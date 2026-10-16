<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_payment_po extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Purchase_Order.View';
    protected $addPermission    = 'Purchase_Order.Add';
    protected $managePermission = 'Purchase_Order.Manage';
    protected $deletePermission = 'Purchase_Order.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Report_payment_po_model');
        $this->template->title('Report Payment PO');
        $this->template->page_icon('fa fa-file-excel-o');
        date_default_timezone_set('Asia/Bangkok');
    }

    /**
     * Halaman Utama: Menampilkan Tabel Header per No. PO
     * Kolom: No, No. PO, Supplier, Currency, Nilai PO, PO Terbayar, Outstanding PO
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $no_po = $this->input->get('no_po');
        $headers = $this->Report_payment_po_model->get_header_data($no_po);

        $this->template->set('headers', $headers);
        $this->template->set('no_po', $no_po);
        $this->template->title('Report Payment PO');
        $this->template->render('report_payment_po');
    }

    /**
     * Endpoint AJAX untuk memuat modal detail transaksi 18 kolom
     */
    public function detail()
    {
        $this->auth->restrict($this->viewPermission);

        $no_po = $this->input->post('no_po') ?? $this->input->get('no_po');
        if (empty($no_po)) {
            echo "<div class='alert alert-danger mb-0'>Nomor PO tidak valid!</div>";
            return;
        }

        $detail_data = $this->Report_payment_po_model->get_po_detail_data($no_po);

        $this->load->view('report_payment_po/detail_payment_po', $detail_data);
    }

    public function debug_cols()
    {
        echo "<pre>";
        $q = $this->db->query("SHOW COLUMNS FROM payment_approve_details");
        print_r($q->result());
        echo "</pre>";
        exit;
    }

    /**
     * Export Excel dengan 2 SHEET:
     * Sheet 1: Rekap Payment PO (Header per No. PO)
     * Sheet 2: Detail Payment PO (18 kolom detail transaksi)
     */
    public function download_excel()
    {
        $this->auth->restrict($this->viewPermission);

        $no_po = $this->input->post('no_po');
        write_log('Report Payment PO', 'Export Excel', 'Export Excel Report Payment PO' . (!empty($no_po) ? ' No PO: ' . $no_po : ' (All)'), ['no_po' => $no_po], null, 1);

        $headers = $this->Report_payment_po_model->get_header_data($no_po);
        $details = $this->Report_payment_po_model->get_report_data($no_po);

        require_once APPPATH . 'libraries/PHPExcel.php';

        $objPHPExcel = new PHPExcel();

        // Styles
        $headerStyle1 = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5DD']],
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '1E40AF'], // Indigo
            ]
        ];

        $headerStyle2 = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN],
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'E2E8F0'],
            ]
        ];

        $dataStyle = [
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
            ],
        ];

        // ========================================================
        // SHEET 1: REKAP PAYMENT PO
        // ========================================================
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet1 = $objPHPExcel->getActiveSheet();
        $sheet1->setTitle('Rekap Payment PO');

        // Judul Sheet 1
        $sheet1->setCellValue('A1', 'REPORT REKAP PAYMENT PO');
        $sheet1->mergeCells('A1:G1');
        $sheet1->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E293B');

        $sheet1->setCellValue('A2', 'Diekspor pada: ' . date('d/m/Y H:i:s') . (!empty($no_po) ? ' | Filter PO: ' . $no_po : ''));
        $sheet1->mergeCells('A2:G2');
        $sheet1->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('64748B');

        // Headers Sheet 1
        $h1 = [
            'A4' => 'NO',
            'B4' => 'NO. PO',
            'C4' => 'SUPPLIER',
            'D4' => 'CURRENCY',
            'E4' => 'NILAI PO',
            'F4' => 'PO TERBAYAR',
            'G4' => 'OUTSTANDING PO',
        ];

        foreach ($h1 as $cell => $val) {
            $sheet1->setCellValue($cell, $val);
        }
        $sheet1->getStyle('A4:G4')->applyFromArray($headerStyle1);
        $sheet1->getRowDimension(4)->setRowHeight(26);

        // Isi Data Sheet 1
        $row1 = 5;
        $no_idx = 1;
        $total_nilai = 0;
        $total_bayar = 0;
        $total_out   = 0;

        foreach ($headers as $h) {
            $sheet1->setCellValue('A' . $row1, $no_idx);
            $sheet1->setCellValue('B' . $row1, $h->no_po);
            $sheet1->setCellValue('C' . $row1, $h->nm_supplier);
            $sheet1->setCellValue('D' . $row1, $h->currency);
            $sheet1->setCellValue('E' . $row1, (float) $h->nilai_po);
            $sheet1->setCellValue('F' . $row1, (float) $h->po_terbayar);
            $sheet1->setCellValue('G' . $row1, (float) $h->outstanding_po);

            $sheet1->getStyle('A' . $row1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('B' . $row1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet1->getStyle('C' . $row1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet1->getStyle('D' . $row1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('E' . $row1 . ':G' . $row1)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet1->getStyle('A' . $row1 . ':G' . $row1)->applyFromArray($dataStyle);

            $total_nilai += (float) $h->nilai_po;
            $total_bayar += (float) $h->po_terbayar;
            $total_out   += (float) $h->outstanding_po;

            $row1++;
            $no_idx++;
        }

        // Total Row Sheet 1
        $sheet1->setCellValue('A' . $row1, 'TOTAL');
        $sheet1->mergeCells('A' . $row1 . ':D' . $row1);
        $sheet1->setCellValue('E' . $row1, $total_nilai);
        $sheet1->setCellValue('F' . $row1, $total_bayar);
        $sheet1->setCellValue('G' . $row1, $total_out);

        $sheet1->getStyle('A' . $row1 . ':G' . $row1)->getFont()->setBold(true);
        $sheet1->getStyle('A' . $row1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle('E' . $row1 . ':G' . $row1)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet1->getStyle('A' . $row1 . ':G' . $row1)->applyFromArray($dataStyle);
        $sheet1->getStyle('A' . $row1 . ':G' . $row1)->getFill()->applyFromArray([
            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => ['rgb' => 'F1F5F9'],
        ]);

        foreach (range('A', 'G') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // ========================================================
        // SHEET 2: DETAIL PAYMENT PO (18 KOLOM)
        // ========================================================
        $sheet2 = $objPHPExcel->createSheet(1);
        $sheet2->setTitle('Detail Payment PO');

        // Headers Row 1 (Merged Headers)
        $sheet2->setCellValue('A1', 'PO');
        $sheet2->mergeCells('A1:E1');
        $sheet2->setCellValue('F1', 'ROS/Incoming');
        $sheet2->mergeCells('F1:H1');
        $sheet2->setCellValue('I1', 'Receive Invoice');
        $sheet2->mergeCells('I1:L1');
        $sheet2->setCellValue('M1', 'Payment');
        $sheet2->mergeCells('M1:R1');

        // Headers Row 2 (Sub Headers)
        $sheet2->setCellValue('A2', 'No. PO');
        $sheet2->setCellValue('B2', 'Tipe PO');
        $sheet2->setCellValue('C2', 'Value PO');
        $sheet2->setCellValue('D2', 'Tipe TOP');
        $sheet2->setCellValue('E2', 'Value %');
        $sheet2->setCellValue('F2', 'Total Material');
        $sheet2->setCellValue('G2', 'Unbill');
        $sheet2->setCellValue('H2', 'Selisih Kurs');
        $sheet2->setCellValue('I2', 'Receive Invoice');
        $sheet2->setCellValue('J2', 'Kurs');
        $sheet2->setCellValue('K2', 'Value Receive');
        $sheet2->setCellValue('L2', 'Selisih Kurs');
        $sheet2->setCellValue('M2', 'Invoice Pay');
        $sheet2->setCellValue('N2', 'Kurs Pay');
        $sheet2->setCellValue('O2', 'Currency Pay');
        $sheet2->setCellValue('P2', 'Payment IDR');
        $sheet2->setCellValue('Q2', 'Admin');
        $sheet2->setCellValue('R2', 'Selisih Kurs');

        $sheet2->getStyle('A1:R2')->applyFromArray($headerStyle2);

        $row2 = 3;
        $grouped_data = [];
        foreach ($details as $item) {
            $grouped_data[$item['no_po']][] = $item;
        }

        foreach ($grouped_data as $po_no => $items) {
            $is_first = true;
            foreach ($items as $item) {
                if ($item['category'] == 'dp') {
                    $category_label = 'Uang Muka';
                } elseif ($item['category'] == 'import') {
                    $category_label = 'Pelunasan (After ROS)';
                } elseif ($item['category'] == 'local') {
                    $category_label = 'Pelunasan (After Incoming)';
                } else {
                    $category_label = ucfirst($item['category']);
                }

                $po_display = $is_first ? $po_no : '';

                $sheet2->setCellValue('A' . $row2, $po_display);
                $sheet2->setCellValue('B' . $row2, $category_label);
                $sheet2->setCellValue('C' . $row2, $item['value_po']);
                $sheet2->setCellValue('D' . $row2, $item['tipe_top']);
                $sheet2->setCellValue('E' . $row2, $item['value_pct']);
                $sheet2->setCellValue('F' . $row2, $item['total_material']);
                $sheet2->setCellValue('G' . $row2, $item['unbill']);
                $sheet2->setCellValue('H' . $row2, $item['selisih_kurs_receive_1']);
                $sheet2->setCellValue('I' . $row2, $item['receive_invoice_value']);
                $sheet2->setCellValue('J' . $row2, $item['receive_kurs']);
                $sheet2->setCellValue('K' . $row2, $item['value_receive_idr']);
                $sheet2->setCellValue('L' . $row2, $item['selisih_kurs_receive_2']);
                $sheet2->setCellValue('M' . $row2, $item['invoice_pay']);
                $sheet2->setCellValue('N' . $row2, $item['kurs_pay']);
                $sheet2->setCellValue('O' . $row2, $item['currency_pay']);
                $sheet2->setCellValue('P' . $row2, $item['payment_idr']);
                $sheet2->setCellValue('Q' . $row2, $item['admin_bank']);
                $sheet2->setCellValue('R' . $row2, $item['selisih_kurs_admin']);

                $sheet2->getStyle('C' . $row2)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet2->getStyle('F' . $row2 . ':N' . $row2)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet2->getStyle('P' . $row2 . ':R' . $row2)->getNumberFormat()->setFormatCode('#,##0.00');

                $sheet2->getStyle('A' . $row2 . ':R' . $row2)->applyFromArray($dataStyle);

                $is_first = false;
                $row2++;
            }
        }

        foreach (range('A', 'R') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // Set Default Sheet Aktif: Sheet 1
        $objPHPExcel->setActiveSheetIndex(0);

        if (ob_get_length()) {
            ob_clean();
        }

        $filename = "Report_Payment_PO_" . (!empty($no_po) ? str_replace('/', '_', $no_po) : "All") . "_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
