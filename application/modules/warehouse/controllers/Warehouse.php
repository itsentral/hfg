<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * Controller for Master Warehouse
 */
class Warehouse extends Admin_Controller
{
    protected $viewPermission   = 'Warehouse.View';
    protected $addPermission    = 'Warehouse.Add';
    protected $managePermission = 'Warehouse.Manage';
    protected $deletePermission = 'Warehouse.Delete';

    // Mapping kode gudang per tab — sesuaikan dengan data di tabel warehouse
    const GUDANG_PUSAT    = 'PUS';
    const GUDANG_PENJUALAN = 'PEN';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Warehouse/Warehouse_model', 'All/All_model']);
        $this->template->title('Gudang');
        $this->template->page_icon('fa fa-cubes');
        date_default_timezone_set('Asia/Bangkok');
    }

    // ── PAGES ──────────────────────────────────────────────────────────────

    public function index()
    {
        $this->template->title('Material Stock (Per Coil)');
        $this->template->render('index');
    }

    public function kartu_stok()
    {
        $this->template->title('Kartu Stok');
        $this->template->page_icon('fa fa-file');
        $this->template->render('kartu_stok');
    }

    public function stock_value()
    {
        $this->template->title('Stock Value');
        $this->template->page_icon('fa fa-dollar');

        $list_gudang = $this->db->query("
            SELECT id, nm_gudang, kd_gudang
            FROM warehouse
            WHERE status = 'Y'
            ORDER BY urut ASC
        ")->result_array();

        $this->template->set(['list_gudang' => $list_gudang]);
        $this->template->render('stock_value');
    }

    // ── SERVER SIDE — STOCK PER COIL (index) ──────────────────────────────
    // Tab Pusat

    public function data_side_stock_pusat()
    {
        $this->Warehouse_model->get_json_warehouse_stock(self::GUDANG_PUSAT);
    }

    // Tab Penjualan
    public function data_side_stock_penjualan()
    {
        $this->Warehouse_model->get_json_warehouse_stock(self::GUDANG_PENJUALAN);
    }

    // ── SERVER SIDE — STOCK VALUE PER MATERIAL ────────────────────────────
    // Tab Pusat

    public function data_side_stock_value_pusat()
    {
        $this->Warehouse_model->get_json_stock_value(self::GUDANG_PUSAT);
    }

    // Tab Penjualan
    public function data_side_stock_value_penjualan()
    {
        $this->Warehouse_model->get_json_stock_value(self::GUDANG_PENJUALAN);
    }

    // Backward-compat (semua gudang)
    public function data_side_stock_value()
    {
        $this->Warehouse_model->get_json_stock_value('');
    }

    // ── GRAND TOTAL (footer DataTables) ───────────────────────────────────

    public function get_grand_total_stock_value()
    {
        $kd_gudang       = $this->input->post('kd_gudang')       ?? '';
        $id_gudang       = $this->input->post('id_gudang')       ?? '';
        $filter_material = $this->input->post('filter_material') ?? '';

        $total = $this->Warehouse_model->get_grand_total_stock_value($kd_gudang, $id_gudang, $filter_material);
        echo json_encode(['total' => number_format($total, 0, ',', '.')]);
    }

    // ── HISTORY MATERIAL ──────────────────────────────────────────────────

    public function get_history_material()
    {
        $id_material = $this->input->post('id_material');
        $id_gudang   = $this->input->post('id_gudang');

        $this->db->select('wh.*, w.nm_gudang, w.kd_gudang');
        $this->db->from('warehouse_history wh');
        $this->db->join('warehouse w', 'w.id = wh.id_gudang', 'left');
        $this->db->where('wh.id_material', $id_material);
        if (!empty($id_gudang)) {
            $this->db->where('wh.id_gudang', $id_gudang);
        }
        $this->db->order_by('wh.update_date', 'ASC');

        echo json_encode($this->db->get()->result_array());
    }

    // ── EXPORT EXCEL ──────────────────────────────────────────────────────

    public function export_excel_stock_value()
    {
        $id_gudang  = $this->input->get('id_gudang');
        $kd_gudang  = $this->input->get('kd_gudang');

        $this->db->select('
            ws.code_lv4 AS id_material,
            ws.nm_material,
            ws.id_gudang,
            ws.kd_gudang,
            ws.qty_stock,
            ws.harga_beli,
            ws.total_nilai,
            w.nm_gudang,
            COUNT(wsc.id) AS jumlah_coil
        ');
        $this->db->from('warehouse_stock ws');
        $this->db->join('warehouse w',              'w.id = ws.id_gudang',          'left');
        $this->db->join('warehouse_stock_coil wsc', 'wsc.id_material = ws.code_lv4', 'left');
        $this->db->where('ws.qty_stock >', 0);
        if (!empty($kd_gudang)) {
            $this->db->where('ws.kd_gudang', $kd_gudang);
        }
        if (!empty($id_gudang)) {
            $this->db->where('ws.id_gudang', $id_gudang);
        }
        $this->db->group_by('ws.code_lv4, ws.id_gudang');
        $this->db->order_by('ws.nm_material', 'ASC');
        $data = $this->db->get()->result_array();

        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');

        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Stock Value');

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'STOCK VALUE REPORT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Per Tanggal: ' . date('d F Y'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $headers = ['No', 'Kode Material', 'Nama Material', 'Gudang', 'Jumlah Coil', 'Qty Stock', 'Harga Beli (Avg)', 'Total Nilai'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        foreach ($headers as $i => $h) {
            $cell = $cols[$i] . '4';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E79');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        $sheet->getRowDimension(4)->setRowHeight(20);

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(45);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);

        $row         = 5;
        $grand_total = 0;
        $fmt_number  = '#,##0';

        foreach ($data as $no => $d) {
            $harga_beli  = (int) round((float) $d['harga_beli']);
            $total_nilai = (int) round((float) $d['total_nilai']);
            $qty_stock   = (float) $d['qty_stock'];
            $jml_coil    = (int) $d['jumlah_coil'];

            $sheet->setCellValueExplicit('A' . $row, $no + 1,               PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $row, $d['id_material'],      PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, $d['nm_material'],      PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, $d['nm_gudang'] . ' (' . $d['kd_gudang'] . ')', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, $jml_coil,              PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F' . $row, $qty_stock,             PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G' . $row, $harga_beli,            PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $row, $total_nilai,           PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode($fmt_number);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode($fmt_number);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode($fmt_number);
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode($fmt_number);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $grand_total += $total_nilai;
            $row++;
        }

        // Grand total row
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValueExplicit('A' . $row, 'GRAND TOTAL',  PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('H' . $row, $grand_total,   PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');
        $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode($fmt_number);

        $filename  = 'Stock_Value_' . date('Ymd_His') . '.xls';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    // ── KARTU STOK ────────────────────────────────────────────────────────

    public function data_side_kartu_stok()
    {
        $this->Warehouse_model->get_json_kartu_stok();
    }

    // Backward-compat
    public function data_side_warehouse_stock()
    {
        $this->Warehouse_model->get_json_warehouse_stock('');
    }
}