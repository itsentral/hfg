<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Fg_warehouse extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Fg_warehouse/Fg_warehouse_model');
        $this->template->title('FG Warehouse');
        $this->template->page_icon('fa fa-warehouse');
    }

    // =========================================================================
    // Pages
    // =========================================================================

    /**
     * List FG Receipt
     */
    public function index()
    {
        $this->template->render('index');
    }

    /**
     * Detail FG Receipt
     *
     * @param string $fg_receipt_no
     */
    public function view($fg_receipt_no)
    {
        $receipt = $this->Fg_warehouse_model->get_receipt($fg_receipt_no);
        if (!$receipt) {
            $this->session->set_flashdata('error', 'FG Receipt tidak ditemukan');
            redirect('fg_warehouse');
        }

        $this->template->set(['receipt' => $receipt]);
        $this->template->render('view_receipt');
    }

    /**
     * Halaman stok FG terkini per produk
     */
    public function stok_fg()
    {
        $stok_list = $this->Fg_warehouse_model->get_all_stok();
        $this->template->set(['stok_list' => $stok_list]);
        $this->template->render('stok_fg');
    }

    /**
     * Halaman kartu stok dengan filter tanggal
     *
     * @param string|null $produk_fg
     */
    public function kartu_stok($produk_fg = null)
    {
        $tgl_dari   = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        // Jika produk_fg dari GET param
        if (empty($produk_fg)) {
            $produk_fg = $this->input->get('produk_fg');
        }

        $ledger      = [];
        $stok_info   = null;
        $produk_list = $this->Fg_warehouse_model->get_all_stok();

        if (!empty($produk_fg)) {
            $stok_info = $this->Fg_warehouse_model->get_stok($produk_fg);
            $ledger    = $this->Fg_warehouse_model->get_kartu_stok($produk_fg, $tgl_dari, $tgl_sampai);
        }

        $this->template->set([
            'produk_fg'   => $produk_fg,
            'tgl_dari'    => $tgl_dari,
            'tgl_sampai'  => $tgl_sampai,
            'ledger'      => $ledger,
            'stok_info'   => $stok_info,
            'produk_list' => $produk_list,
        ]);
        $this->template->render('kartu_stok');
    }

    /**
     * Export Excel Stok FG
     */
    public function export_excel_stok()
    {
        error_reporting(0);
        write_log('FG Warehouse', 'Export Excel', 'Export Excel Stok Finish Good', null, null, 1);
        $stok_list = $this->Fg_warehouse_model->get_all_stok();

        $this->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Stok FG');

        $sheet->setCellValue('A1', 'LAPORAN STOK FINISH GOOD (FG)');
        $sheet->setCellValue('A2', 'Waktu Cetak: ' . date('d-m-Y H:i:s'));

        $row = 4;
        $headers = ['A' => 'No', 'B' => 'Kode Produk', 'C' => 'Nama Produk', 'D' => 'Qty Stok (pcs)', 'E' => 'Total Berat (kg)', 'F' => 'Berat Ref (kg/pcs)', 'G' => 'Costbook (Rp)', 'H' => 'Total Nilai (Rp)', 'I' => 'Last Update'];
        foreach ($headers as $col => $t) {
            $sheet->setCellValue($col . $row, $t);
        }

        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($styleHeader);

        $curr_row = 5;
        $no       = 1;
        $grand_qty = 0; $grand_berat = 0; $grand_nilai = 0;
        foreach ($stok_list as $s) {
            $qty = (float) $s->qty_stok;
            $brt = (float) $s->total_berat;
            $val = (float) $s->total_nilai;
            $grand_qty += $qty; $grand_berat += $brt; $grand_nilai += $val;

            $sheet->setCellValueExplicit('A' . $curr_row, $no++, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $curr_row, $s->produk_fg, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $curr_row, $s->nm_produk_fg ? $s->nm_produk_fg : '-', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $curr_row, $qty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('E' . $curr_row, $brt, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F' . $curr_row, (float) $s->berat_referensi, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G' . $curr_row, (float) $s->costbook, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $curr_row, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I' . $curr_row, $s->last_update ? $s->last_update : '-', PHPExcel_Cell_DataType::TYPE_STRING);

            $sheet->getStyle('D' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('F' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.0000');
            $sheet->getStyle('G' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('H' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
            $curr_row++;
        }

        $sheet->mergeCells('A' . $curr_row . ':C' . $curr_row);
        $sheet->setCellValue('A' . $curr_row, 'TOTAL:');
        $sheet->setCellValueExplicit('D' . $curr_row, $grand_qty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('E' . $curr_row, $grand_berat, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('H' . $curr_row, $grand_nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);

        foreach (range('A', 'I') as $colID) {
            $sheet->getColumnDimension($colID)->setAutoSize(true);
        }

        $filename = 'Stok_FG_' . date('Ymd_His') . '.xls';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Export Excel Kartu Stok FG
     *
     * @param string|null $produk_fg
     */
    public function export_excel_kartu_stok($produk_fg = null)
    {
        if (empty($produk_fg)) {
            $produk_fg = $this->input->get('produk_fg');
        }
        if (empty($produk_fg)) {
            redirect('fg_warehouse/stok_fg');
        }

        $tgl_dari   = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        error_reporting(0);
        write_log('FG Warehouse', 'Export Excel', 'Export Excel Kartu Stok FG: ' . $produk_fg, null, null, 1);
        $stok_info = $this->Fg_warehouse_model->get_stok($produk_fg);
        $ledger    = $this->Fg_warehouse_model->get_kartu_stok($produk_fg, $tgl_dari, $tgl_sampai);

        $this->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Kartu Stok');

        $sheet->setCellValue('A1', 'KARTU STOK FINISH GOOD: ' . $produk_fg);
        $sheet->setCellValue('A2', 'Nama: ' . ($stok_info ? $stok_info->nm_produk_fg : '-'));
        $sheet->setCellValue('A3', 'Periode: ' . ($tgl_dari ? $tgl_dari : 'Awal') . ' s/d ' . ($tgl_sampai ? $tgl_sampai : 'Sekarang'));

        $row = 5;
        $headers = ['A' => 'No', 'B' => 'Tgl', 'C' => 'No Ref', 'D' => 'No SPK', 'E' => 'Kategori', 'F' => 'Jenis', 'G' => 'Qty IN', 'H' => 'Qty OUT', 'I' => 'Saldo Qty', 'J' => 'Brt IN (kg)', 'K' => 'Brt OUT (kg)', 'L' => 'Saldo Brt (kg)', 'M' => 'Costbook', 'N' => 'Nilai Transaksi', 'O' => 'Saldo Nilai', 'P' => 'Keterangan'];
        foreach ($headers as $col => $t) {
            $sheet->setCellValue($col . $row, $t);
        }

        $curr_row = 6;
        $no = 1;
        foreach ($ledger as $item) {
            $sheet->setCellValueExplicit('A' . $curr_row, $no++, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $curr_row, $item->tgl_transaksi, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $curr_row, $item->no_referensi, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $curr_row, $item->spk_no ? $item->spk_no : '-', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $curr_row, $item->category_type ? $item->category_type : '-', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $curr_row, $item->jenis_mutasi, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G' . $curr_row, (float) $item->qty_in, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $curr_row, (float) $item->qty_out, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I' . $curr_row, (float) $item->qty_saldo, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J' . $curr_row, (float) $item->berat_in, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('K' . $curr_row, (float) $item->berat_out, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('L' . $curr_row, (float) $item->berat_saldo, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('M' . $curr_row, (float) $item->costbook, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('N' . $curr_row, (float) $item->total_nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('O' . $curr_row, (float) $item->saldo_nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('P' . $curr_row, $item->keterangan ? $item->keterangan : '-', PHPExcel_Cell_DataType::TYPE_STRING);

            $sheet->getStyle('G' . $curr_row . ':I' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $curr_row . ':L' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('M' . $curr_row . ':O' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
            $curr_row++;
        }

        foreach (range('A', 'P') as $colID) {
            $sheet->getColumnDimension($colID)->setAutoSize(true);
        }

        $clean = preg_replace('/[^A-Za-z0-9_\-]/', '_', $produk_fg);
        $filename = 'Kartu_Stok_' . $clean . '_' . date('Ymd_His') . '.xls';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    // =========================================================================
    // JSON Endpoints
    // =========================================================================

    /**
     * DataTables server-side endpoint untuk list FG Receipt
     */
    public function data_side_receipt()
    {
        $result = $this->Fg_warehouse_model->get_receipt_list_for_datatable($_REQUEST);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $status_badge = $this->_status_badge($row->status);
            $btn_view     = '<a href="' . base_url('fg_warehouse/view/' . $row->fg_receipt_no) . '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>';

            $data[] = [
                $i + 1 + (int) $_REQUEST['start'],
                $row->fg_receipt_no,
                $row->created_at,
                $row->report_no,
                $row->spk_no,
                $row->nm_produk_fg ? $row->nm_produk_fg : ($row->produk_fg ? $row->produk_fg : '-'),
                number_format((float) $row->fg_qty, 2) . ' pcs / ' . number_format((float) $row->fg_kg, 3) . ' kg',
                $status_badge,
                $btn_view,
            ];
        }

        echo json_encode([
            'draw'            => (int) $_REQUEST['draw'],
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }

    // =========================================================================
    // POST Actions
    // =========================================================================

    /**
     * POST: posting FG Receipt, return JSON
     *
     * @param string $fg_receipt_no
     */
    public function process_post_receipt($fg_receipt_no)
    {
        $user_id = $this->auth->user_id();
        $result  = $this->Fg_warehouse_model->post_receipt($fg_receipt_no, $user_id);
        echo json_encode($result);
    }

    /**
     * POST: cancel FG Receipt, return JSON
     *
     * @param string $fg_receipt_no
     */
    public function process_cancel_receipt($fg_receipt_no)
    {
        $user_id = $this->auth->user_id();
        $result  = $this->Fg_warehouse_model->cancel_receipt($fg_receipt_no, $user_id);
        echo json_encode($result);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function _status_badge($status)
    {
        $map = [
            'Draft'     => 'secondary',
            'Posted'    => 'success',
            'Cancelled' => 'danger',
        ];
        $color = isset($map[$status]) ? $map[$status] : 'secondary';
        return "<span class='badge bg-{$color}'>{$status}</span>";
    }
}
