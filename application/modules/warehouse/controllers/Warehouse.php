<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Master Warehouse
 */
$status = array();
class Warehouse extends Admin_Controller
{
    //Permission
    protected $viewPermission       = 'Warehouse.View';
    protected $addPermission        = 'Warehouse.Add';
    protected $managePermission     = 'Warehouse.Manage';
    protected $deletePermission     = 'Warehouse.Delete';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Warehouse/Warehouse_model', 'All/All_model'));
        $this->template->title('Gudang');
        $this->template->page_icon('fa fa-dollar');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Material Stock');
        $this->template->page_icon('fa fa-cubes');
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

        $list_gudang = $this->db->query("SELECT id, nm_gudang, kd_gudang FROM warehouse WHERE status = 'Y' ORDER BY urut ASC")->result_array();

        $this->template->set(['list_gudang' => $list_gudang]);
        $this->template->render('stock_value');
    }

    public function data_side_stock_value()
    {
        $this->Warehouse_model->get_json_stock_value();
    }

    public function get_grand_total_stock_value()
    {
        $id_gudang       = $this->input->post('id_gudang');
        $filter_material = $this->input->post('filter_material');

        $this->db->select('SUM(ws.total_nilai) as grand_total');
        $this->db->from('warehouse_stock ws');
        $this->db->join('new_inventory_4 ni', 'ni.code_lv4 = ws.id_material', 'left');
        if (!empty($id_gudang)) {
            $this->db->where('ws.id_gudang', $id_gudang);
        }
        if (!empty($filter_material)) {
            $this->db->group_start();
            $this->db->like('ws.nm_material', $filter_material);
            $this->db->or_like('ws.id_material', $filter_material);
            $this->db->group_end();
        }
        $row = $this->db->get()->row();
        $total = $row ? (int) round($row->grand_total) : 0;

        echo json_encode(['total' => number_format($total, 0, ',', '.')]);
    }

    public function export_excel_stock_value()
    {
        $id_gudang = $this->input->get('id_gudang');

        // Ambil data — filter hanya berdasarkan gudang
        $this->db->select('ws.id_material, ws.nm_material, ws.id_gudang, ws.kd_gudang, ws.qty_stock, ws.harga_beli, ws.total_nilai, w.nm_gudang');
        $this->db->from('warehouse_stock ws');
        $this->db->join('warehouse w', 'w.id = ws.id_gudang', 'left');
        $this->db->where('ws.qty_stock >', 0);
        if (!empty($id_gudang)) {
            $this->db->where('ws.id_gudang', $id_gudang);
        }
        $this->db->order_by('ws.nm_material', 'ASC');
        $data = $this->db->get()->result_array();

        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');

        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Stock Value');

        // Judul
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'STOCK VALUE REPORT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'Per Tanggal: ' . date('d F Y'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Header kolom baris 4
        $headers = ['No', 'Kode Material', 'Nama Material', 'Gudang', 'Qty Stock', 'Harga Beli (Avg)', 'Total Nilai'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        foreach ($headers as $i => $h) {
            $cell = $cols[$i] . '4';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E79');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        $sheet->getRowDimension(4)->setRowHeight(20);

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(45);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);

        // Data rows
        $row         = 5;
        $grand_total = 0;
        $fmt_number  = '#,##0';

        foreach ($data as $no => $d) {
            $harga_beli  = (int) round((float) $d['harga_beli']);
            $total_nilai = (int) round((float) $d['total_nilai']);
            $qty_stock   = (float) $d['qty_stock'];

            $sheet->setCellValueExplicit('A' . $row, $no + 1,        PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $row, $d['id_material'],  PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, $d['nm_material'],  PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, $d['nm_gudang'] . ' (' . $d['kd_gudang'] . ')', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, $qty_stock,     PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F' . $row, $harga_beli,    PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G' . $row, $total_nilai,   PHPExcel_Cell_DataType::TYPE_NUMERIC);

            // Border semua kolom
            $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

            // Format angka
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode($fmt_number);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode($fmt_number);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode($fmt_number);

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $grand_total += $total_nilai;
            $row++;
        }

        // Grand total row
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValueExplicit('A' . $row, 'GRAND TOTAL', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('G' . $row, $grand_total,  PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');
        $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode($fmt_number);

        // Output
        $filename = 'Stock_Value_' . date('Ymd_His') . '.xls';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

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

        $data = $this->db->get()->result_array();
        echo json_encode($data);
    }

    // SERVER SIDE
    public function data_side_warehouse_stock()
    {
        $this->Warehouse_model->get_json_warehouse_stock();
    }

    public function data_side_kartu_stok()
    {
        $this->Warehouse_model->get_json_kartu_stok();
    }
}
