<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_monitoring_po extends Admin_Controller
{
    protected $viewPermission = 'Report_Monitoring_PO.View';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('report_monitoring_po/Report_monitoring_po_model', 'monitoring_model');
        $this->template->title('Report Monitoring Open PO Supplier');
        $this->template->page_icon('ti ti-truck-delivery');
        date_default_timezone_set('Asia/Bangkok');
    }

    /**
     * Halaman Utama Report Monitoring PO
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $suppliers = $this->monitoring_model->get_suppliers();

        $data = [
            'suppliers' => $suppliers,
        ];

        $this->template->set($data);
        $this->template->title('Report Monitoring Open PO Supplier');
        $this->template->render('index');
    }

    /**
     * Endpoint AJAX untuk memuat summary cards dan tabel data secara terpisah (JSON)
     */
    public function load_data()
    {
        $this->auth->restrict($this->viewPermission);

        $filter_status   = $this->input->get('status') ?? 'all';
        $filter_supplier = $this->input->get('supplier') ?? '';
        $tgl_awal        = $this->input->get('tgl_awal') ?? '';
        $tgl_akhir       = $this->input->get('tgl_akhir') ?? '';

        $results = $this->monitoring_model->get_monitoring_data($filter_status, $filter_supplier, $tgl_awal, $tgl_akhir);
        $summary = $this->monitoring_model->get_summary_counters();

        $data = [
            'results'         => $results,
            'summary'         => $summary,
            'filter_status'   => $filter_status,
            'filter_supplier' => $filter_supplier,
            'tgl_awal'        => $tgl_awal,
            'tgl_akhir'       => $tgl_akhir,
        ];

        $summary_html = $this->load->view('report_monitoring_po/summary_cards', $data, true);
        $table_html   = $this->load->view('report_monitoring_po/table_data', $data, true);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'       => 'success',
                'summary_html' => $summary_html,
                'table_html'   => $table_html,
            ]));
    }

    /**
     * Modal Detail Riwayat Kedatangan per No PO (AJAX)
     */
    public function detail($no_po = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (empty($no_po)) {
            echo "<div class='alert alert-danger mb-0'>No PO tidak valid!</div>";
            return;
        }

        $detail_data = $this->monitoring_model->get_po_detail($no_po);

        if (!$detail_data) {
            echo "<div class='alert alert-warning mb-0'>Data PO tidak ditemukan!</div>";
            return;
        }

        $this->load->view('report_monitoring_po/detail', $detail_data);
    }

    /**
     * Export Laporan Monitoring Open PO ke File Excel dengan 2 SHEET:
     * Sheet 1: Rekap Monitoring PO (dengan No. PR & Tipe PR)
     * Sheet 2: Detail Riwayat Kedatangan PO (dengan No. PR & Tipe PR)
     */
    public function download_excel()
    {
        $this->auth->restrict($this->viewPermission);

        $filter_status   = $this->input->get('status') ?? 'all';
        $filter_supplier = $this->input->get('supplier') ?? '';
        $tgl_awal        = $this->input->get('tgl_awal') ?? '';
        $tgl_akhir       = $this->input->get('tgl_akhir') ?? '';

        $data = $this->monitoring_model->get_monitoring_data($filter_status, $filter_supplier, $tgl_awal, $tgl_akhir);

        require_once APPPATH . 'libraries/PHPExcel.php';

        $objPHPExcel = new PHPExcel();

        // Style Preset
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap'       => true,
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5DD']],
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '1E40AF'], // Indigo Primary
            ],
        ];

        $detailHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap'       => true,
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5DD']],
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '047857'], // Emerald Green
            ],
        ];

        $borderStyle = [
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
            ],
        ];

        // ========================================================
        // SHEET 1: REKAP MONITORING PO
        // ========================================================
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet1 = $objPHPExcel->getActiveSheet();
        $sheet1->setTitle('Monitoring PO');

        // Judul Sheet 1
        $sheet1->setCellValue('A1', 'REPORT MONITORING OPEN PO SUPPLIER');
        $sheet1->mergeCells('A1:K1');
        $sheet1->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E293B');

        $filterInfo = 'Diekspor pada: ' . date('d/m/Y H:i:s');
        if (!empty($filter_status) && $filter_status !== 'all') {
            $filterInfo .= ' | Status: ' . ucfirst(str_replace('_', ' ', $filter_status));
        }
        $sheet1->setCellValue('A2', $filterInfo);
        $sheet1->mergeCells('A2:K2');
        $sheet1->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('64748B');

        // Header Kolom Sheet 1 (Ditambahkan No. PR & Tipe PR di Kolom E)
        $headers1 = [
            'A4' => 'NO',
            'B4' => 'NO. PO',
            'C4' => 'TANGGAL PO',
            'D4' => 'SUPPLIER',
            'E4' => 'NO. PR & TIPE PR',
            'F4' => 'ITEM / SPEK MATERIAL',
            'G4' => 'TOTAL QTY PO (KG)',
            'H4' => 'TOTAL DITERIMA (KG)',
            'I4' => 'SISA QTY BELUM DIKIRIM (KG)',
            'J4' => '% PROGRESS',
            'K4' => 'STATUS',
        ];

        foreach ($headers1 as $cell => $val) {
            $sheet1->setCellValue($cell, $val);
        }
        $sheet1->getStyle('A4:K4')->applyFromArray($headerStyle);
        $sheet1->getRowDimension(4)->setRowHeight(28);

        // Isi Data Sheet 1
        $rowNum1 = 5;
        $no1 = 1;
        $sumQtyPO = 0;
        $sumDiterima = 0;
        $sumSisa = 0;

        foreach ($data as $item) {
            $materials = !empty($item->materials_arr) ? implode(", ", $item->materials_arr) : '-';
            $pr_text   = $item->pr_text ?? '-';

            $sheet1->setCellValue('A' . $rowNum1, $no1);
            $sheet1->setCellValue('B' . $rowNum1, $item->no_surat ?? $item->no_po);
            $sheet1->setCellValue('C' . $rowNum1, !empty($item->tanggal) ? date('d/m/Y', strtotime($item->tanggal)) : '-');
            $sheet1->setCellValue('D' . $rowNum1, $item->nm_supplier ?? '-');
            $sheet1->setCellValue('E' . $rowNum1, $pr_text);
            $sheet1->setCellValue('F' . $rowNum1, $materials);
            $sheet1->setCellValue('G' . $rowNum1, (float) $item->total_qty_po);
            $sheet1->setCellValue('H' . $rowNum1, (float) $item->total_diterima);
            $sheet1->setCellValue('I' . $rowNum1, (float) $item->sisa_qty);
            $sheet1->setCellValue('J' . $rowNum1, $item->progress_pct . '%');
            $sheet1->setCellValue('K' . $rowNum1, $item->status_label);

            $sheet1->getStyle('A' . $rowNum1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('B' . $rowNum1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet1->getStyle('C' . $rowNum1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('D' . $rowNum1 . ':F' . $rowNum1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet1->getStyle('G' . $rowNum1 . ':I' . $rowNum1)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet1->getStyle('J' . $rowNum1 . ':K' . $rowNum1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet1->getStyle('A' . $rowNum1 . ':K' . $rowNum1)->applyFromArray($borderStyle);

            $sumQtyPO    += (float) $item->total_qty_po;
            $sumDiterima += (float) $item->total_diterima;
            $sumSisa     += (float) $item->sisa_qty;

            $rowNum1++;
            $no1++;
        }

        // Total Row Sheet 1
        $sheet1->setCellValue('A' . $rowNum1, 'TOTAL KESELURUHAN');
        $sheet1->mergeCells('A' . $rowNum1 . ':F' . $rowNum1);
        $sheet1->setCellValue('G' . $rowNum1, $sumQtyPO);
        $sheet1->setCellValue('H' . $rowNum1, $sumDiterima);
        $sheet1->setCellValue('I' . $rowNum1, $sumSisa);
        $sheet1->setCellValue('J' . $rowNum1, ($sumQtyPO > 0) ? round(($sumDiterima / $sumQtyPO) * 100, 2) . '%' : '-');
        $sheet1->setCellValue('K' . $rowNum1, '-');

        $sheet1->getStyle('A' . $rowNum1 . ':K' . $rowNum1)->getFont()->setBold(true);
        $sheet1->getStyle('A' . $rowNum1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle('G' . $rowNum1 . ':I' . $rowNum1)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet1->getStyle('J' . $rowNum1 . ':K' . $rowNum1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A' . $rowNum1 . ':K' . $rowNum1)->applyFromArray($borderStyle);
        $sheet1->getStyle('A' . $rowNum1 . ':K' . $rowNum1)->getFill()->applyFromArray([
            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => ['rgb' => 'F1F5F9'],
        ]);

        foreach (range('A', 'K') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // ========================================================
        // SHEET 2: DETAIL RIWAYAT KEDATANGAN PO
        // ========================================================
        $sheet2 = $objPHPExcel->createSheet(1);
        $sheet2->setTitle('Detail Kedatangan PO');

        // Judul Sheet 2
        $sheet2->setCellValue('A1', 'DETAIL RIWAYAT KEDATANGAN BARANG (INCOMING DELIVERIES)');
        $sheet2->mergeCells('A1:J1');
        $sheet2->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E293B');

        $sheet2->setCellValue('A2', 'Urutan Kedatangan: Paling lama di atas (kronologis per kedatangan)');
        $sheet2->mergeCells('A2:J2');
        $sheet2->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('64748B');

        // Header Kolom Sheet 2 (Ditambahkan No. PR & Tipe PR di Kolom D)
        $headers2 = [
            'A4' => 'NO',
            'B4' => 'NO. PO',
            'C4' => 'SUPPLIER',
            'D4' => 'NO. PR & TIPE PR',
            'E4' => 'ITEM / SPEK MATERIAL',
            'F4' => 'NO. INCOMING',
            'G4' => 'TANGGAL TERIMA',
            'H4' => 'QTY DITERIMA (KG)',
            'I4' => 'KUMULATIF DITERIMA (KG)',
            'J4' => 'SISA QTY SETELAH INI (KG)',
        ];

        foreach ($headers2 as $cell => $val) {
            $sheet2->setCellValue($cell, $val);
        }
        $sheet2->getStyle('A4:J4')->applyFromArray($detailHeaderStyle);
        $sheet2->getRowDimension(4)->setRowHeight(28);

        // Isi Data Sheet 2
        $rowNum2 = 5;
        $no2 = 1;
        $totalAllIncoming = 0;

        foreach ($data as $item) {
            $pr_text = $item->pr_text ?? '-';
            $po_detail = $this->monitoring_model->get_po_detail($item->no_po);
            if (!empty($po_detail['incoming_history'])) {
                foreach ($po_detail['incoming_history'] as $inc) {
                    $sheet2->setCellValue('A' . $rowNum2, $no2);
                    $sheet2->setCellValue('B' . $rowNum2, $item->no_surat ?? $item->no_po);
                    $sheet2->setCellValue('C' . $rowNum2, $item->nm_supplier ?? '-');
                    $sheet2->setCellValue('D' . $rowNum2, $pr_text);
                    $sheet2->setCellValue('E' . $rowNum2, $inc->nm_material ?? '-');
                    $sheet2->setCellValue('F' . $rowNum2, $inc->kode_trans);
                    $sheet2->setCellValue('F' . $rowNum2, $inc->kode_trans);
                    $sheet2->setCellValue('G' . $rowNum2, !empty($inc->tanggal) ? date('d/m/Y', strtotime($inc->tanggal)) : '-');
                    $sheet2->setCellValue('H' . $rowNum2, (float) $inc->qty_diterima);
                    $sheet2->setCellValue('I' . $rowNum2, (float) $inc->kumulatif_diterima);
                    $sheet2->setCellValue('J' . $rowNum2, (float) $inc->sisa_setelah_kedatangan);

                    $sheet2->getStyle('A' . $rowNum2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet2->getStyle('B' . $rowNum2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $sheet2->getStyle('C' . $rowNum2 . ':F' . $rowNum2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $sheet2->getStyle('G' . $rowNum2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet2->getStyle('H' . $rowNum2 . ':J' . $rowNum2)->getNumberFormat()->setFormatCode('#,##0.00');

                    $sheet2->getStyle('A' . $rowNum2 . ':J' . $rowNum2)->applyFromArray($borderStyle);

                    $totalAllIncoming += (float) $inc->qty_diterima;
                    $rowNum2++;
                    $no2++;
                }
            } else {
                // PO yang belum ada penerimaan
                $sheet2->setCellValue('A' . $rowNum2, $no2);
                $sheet2->setCellValue('B' . $rowNum2, $item->no_surat ?? $item->no_po);
                $sheet2->setCellValue('C' . $rowNum2, $item->nm_supplier ?? '-');
                $sheet2->setCellValue('D' . $rowNum2, $pr_text);
                $sheet2->setCellValue('E' . $rowNum2, !empty($item->materials_arr) ? implode(", ", $item->materials_arr) : '-');
                $sheet2->setCellValue('F' . $rowNum2, '-');
                $sheet2->setCellValue('G' . $rowNum2, '-');
                $sheet2->setCellValue('H' . $rowNum2, 0);
                $sheet2->setCellValue('I' . $rowNum2, 0);
                $sheet2->setCellValue('J' . $rowNum2, (float) $item->total_qty_po);

                $sheet2->getStyle('A' . $rowNum2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle('B' . $rowNum2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet2->getStyle('C' . $rowNum2 . ':F' . $rowNum2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet2->getStyle('G' . $rowNum2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle('H' . $rowNum2 . ':J' . $rowNum2)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet2->getStyle('A' . $rowNum2 . ':J' . $rowNum2)->applyFromArray($borderStyle);

                $rowNum2++;
                $no2++;
            }
        }

        // Total Row Sheet 2
        $sheet2->setCellValue('A' . $rowNum2, 'TOTAL DITERIMA KESELURUHAN');
        $sheet2->mergeCells('A' . $rowNum2 . ':G' . $rowNum2);
        $sheet2->setCellValue('H' . $rowNum2, $totalAllIncoming);
        $sheet2->setCellValue('I' . $rowNum2, '-');
        $sheet2->setCellValue('J' . $rowNum2, '-');

        $sheet2->getStyle('A' . $rowNum2 . ':J' . $rowNum2)->getFont()->setBold(true);
        $sheet2->getStyle('A' . $rowNum2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle('H' . $rowNum2)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet2->getStyle('I' . $rowNum2 . ':J' . $rowNum2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle('A' . $rowNum2 . ':J' . $rowNum2)->applyFromArray($borderStyle);
        $sheet2->getStyle('A' . $rowNum2 . ':J' . $rowNum2)->getFill()->applyFromArray([
            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => ['rgb' => 'ECFDF5'],
        ]);

        foreach (range('A', 'J') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // Set Default Sheet Aktif adalah Sheet 1
        $objPHPExcel->setActiveSheetIndex(0);

        // Download Excel
        $filename = "Monitoring_PO_Supplier_" . date('Ymd_His') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
