<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Fg_stock extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fg_stock/Fg_stock_model', 'stock_model');
        $this->template->title('Stok Finish Good');
        $this->template->page_icon('fa fa-cubes');
    }

    /**
     * Halaman Utama: Daftar Seluruh Stok FG dengan 4 Kategori Tab (KW1, KW2, Tong Coil, Scrap)
     */
    public function index()
    {
        $stok_kw1       = $this->stock_model->get_stok_by_kategori('KW1');
        $stok_kw2       = $this->stock_model->get_stok_by_kategori('KW2');
        $stok_tong_coil = $this->stock_model->get_stok_by_kategori('TONG_COIL');
        $stok_scrap     = $this->stock_model->get_stok_by_kategori('SCRAP');
        $summary        = $this->stock_model->get_kpi_summary();

        $this->template->set([
            'stok_kw1'       => $stok_kw1,
            'stok_kw2'       => $stok_kw2,
            'stok_tong_coil' => $stok_tong_coil,
            'stok_scrap'     => $stok_scrap,
            'summary'        => $summary,
        ]);
        $this->template->render('index');
    }

    /**
     * Halaman Riwayat Mutasi / Kartu Stok Produk
     *
     * @param string|null $produk_fg
     */
    public function history($produk_fg = null)
    {
        if (empty($produk_fg)) {
            $produk_fg = $this->input->get('produk_fg');
        }

        $tgl_dari   = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        $stok_info       = null;
        $ledger          = [];
        $produk_dropdown = $this->stock_model->get_product_dropdown();

        if (!empty($produk_fg)) {
            $stok_info = $this->stock_model->get_stok_by_kode($produk_fg);
            $ledger    = $this->stock_model->get_history_ledger($produk_fg, $tgl_dari, $tgl_sampai);
        }

        $this->template->set([
            'produk_fg'       => $produk_fg,
            'tgl_dari'        => $tgl_dari,
            'tgl_sampai'      => $tgl_sampai,
            'stok_info'       => $stok_info,
            'ledger'          => $ledger,
            'produk_dropdown' => $produk_dropdown,
        ]);
        $this->template->render('history');
    }

    /**
     * Alias method untuk history agar fleksibel dipanggil kartu_stok
     *
     * @param string|null $produk_fg
     */
    public function kartu_stok($produk_fg = null)
    {
        $this->history($produk_fg);
    }

    /**
     * Export Excel: Daftar Rekapitulasi Stok Finish Good (Multi-Sheet: KW1, KW2, Tong Coil, Scrap)
     */
    public function export_excel_stok()
    {
        error_reporting(0);
        write_log('FG Stock', 'Export Excel', 'Export Excel Stok Finish Good 4 Kategori', null, null, 1);

        $this->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();

        // Metadata
        $objPHPExcel->getProperties()
            ->setCreator("PT. HFG System")
            ->setTitle("Laporan Stok Finish Good")
            ->setSubject("Stok FG & Waste")
            ->setDescription("Rekapitulasi Stok Produk Finish Good, KW 2, Tong Coil, dan Scrap");

        // Styling definitions
        $styleTitle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT],
        ];

        $styleSubTitle = [
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '595959']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT],
        ];

        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '1F4E78'],
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ];

        $styleData = [
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']],
            ],
            'alignment' => [
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
            'font' => ['size' => 9],
        ];

        $styleTotal = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9E1F2'],
            ],
            'borders' => [
                'top'    => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_DOUBLE, 'color' => ['rgb' => '000000']],
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'BFBFBF']],
            ],
        ];

        $categories = [
            ['sheet_title' => 'Finish Good KW 1', 'kategori' => 'KW1',       'header_color' => '1F4E78'],
            ['sheet_title' => 'Finish Good KW 2', 'kategori' => 'KW2',       'header_color' => 'B25900'],
            ['sheet_title' => 'Tong Coil',        'kategori' => 'TONG_COIL', 'header_color' => '117A8B'],
            ['sheet_title' => 'Scrap & Waste',    'kategori' => 'SCRAP',     'header_color' => 'C82333'],
        ];

        foreach ($categories as $idx => $cat_info) {
            if ($idx === 0) {
                $sheet = $objPHPExcel->getActiveSheet();
            } else {
                $sheet = $objPHPExcel->createSheet($idx);
            }

            $sheet->setTitle($cat_info['sheet_title']);
            $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

            $stok_list = $this->stock_model->get_stok_by_kategori($cat_info['kategori']);

            // Title Block
            $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI STOK: ' . strtoupper($cat_info['sheet_title']));
            $sheet->getStyle('A1')->applyFromArray($styleTitle);

            $sheet->setCellValue('A2', 'Waktu Cetak: ' . date('d-m-Y H:i:s'));
            $sheet->getStyle('A2')->applyFromArray($styleSubTitle);

            // Header Table (Row 4)
            $row = 4;
            $sheetHeaderStyle = $styleHeader;
            $sheetHeaderStyle['fill']['color']['rgb'] = $cat_info['header_color'];

            $headers = [
                'A' => 'No',
                'B' => 'Kode Stok',
                'C' => 'Nama Produk / Material',
                'D' => 'Kategori / Jenis',
                'E' => 'Qty (Pcs/Roll)',
                'F' => 'Total Berat (kg)',
                'G' => 'Costbook (Rp)',
                'H' => 'Total Nilai (Rp)',
                'I' => 'Terakhir Update',
            ];

            foreach ($headers as $col => $text) {
                $sheet->setCellValue($col . $row, $text);
            }
            $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($sheetHeaderStyle);
            $sheet->getRowDimension($row)->setRowHeight(26);

            // Data Rows
            $curr_row = 5;
            $no       = 1;
            $tot_qty  = 0;
            $tot_berat= 0;
            $tot_nilai= 0;

            foreach ($stok_list as $item) {
                $qty_stok   = (float) $item->qty_stok;
                $total_berat= (float) $item->total_berat;
                $costbook   = (float) $item->costbook;
                $total_nilai= (float) $item->total_nilai;

                $tot_qty   += $qty_stok;
                $tot_berat += $total_berat;
                $tot_nilai += $total_nilai;

                $sheet->setCellValueExplicit('A' . $curr_row, $no, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('B' . $curr_row, $item->produk_fg, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $curr_row, $item->nm_produk_fg ?: '-', PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('D' . $curr_row, $item->sub_kategori ?: $item->kategori_stok, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('E' . $curr_row, $qty_stok, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('F' . $curr_row, $total_berat, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('G' . $curr_row, $costbook, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('H' . $curr_row, $total_nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('I' . $curr_row, $item->last_update ?: '-', PHPExcel_Cell_DataType::TYPE_STRING);

                // Alignment & borders
                $sheet->getStyle('A' . $curr_row . ':I' . $curr_row)->applyFromArray($styleData);
                $sheet->getStyle('A' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('C' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('D' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('F' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('G' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('H' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('I' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                // Formats
                $sheet->getStyle('E' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('F' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.000');
                $sheet->getStyle('G' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('H' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');

                $curr_row++;
                $no++;
            }

            // Grand Total Row
            $sheet->mergeCells('A' . $curr_row . ':D' . $curr_row);
            $sheet->setCellValue('A' . $curr_row, 'TOTAL PERSENTASE & NILAI:');
            $sheet->setCellValueExplicit('E' . $curr_row, $tot_qty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F' . $curr_row, $tot_berat, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('G' . $curr_row, '');
            $sheet->setCellValueExplicit('H' . $curr_row, $tot_nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('I' . $curr_row, '');

            $sheet->getStyle('A' . $curr_row . ':I' . $curr_row)->applyFromArray($styleTotal);
            $sheet->getStyle('A' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('E' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('F' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('H' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getRowDimension($curr_row)->setRowHeight(22);

            // Auto width
            foreach (range('A', 'I') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
        }

        // Set active sheet to first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Output stream
        $filename = 'Stok_Finish_Good_' . date('Ymd_His') . '.xls';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Export Excel: Riwayat Kartu Stok (Ledger) Produk Tertentu
     *
     * @param string|null $produk_fg
     */
    public function export_excel_history($produk_fg = null)
    {
        if (empty($produk_fg)) {
            $produk_fg = $this->input->get('produk_fg');
        }

        if (empty($produk_fg)) {
            $this->session->set_flashdata('error', 'Pilih produk FG yang ingin diexport.');
            redirect('fg_stock');
        }

        $tgl_dari   = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        error_reporting(0);
        write_log('FG Stock', 'Export Excel', 'Export Excel Kartu Stok FG: ' . $produk_fg, null, null, 1);

        $stok_info = $this->stock_model->get_stok_by_kode($produk_fg);
        $ledger    = $this->stock_model->get_history_ledger($produk_fg, $tgl_dari, $tgl_sampai);

        $this->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();

        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Kartu Stok FG');

        // Page setup
        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        // Styles
        $styleTitle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT],
        ];

        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap'       => true,
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '1F4E78'],
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ];

        $styleData = [
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']],
            ],
            'alignment' => [
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
            'font' => ['size' => 9],
        ];

        $styleTotal = [
            'font' => ['bold' => true, 'size' => 9],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9E1F2'],
            ],
            'borders' => [
                'top'    => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                'bottom' => ['style' => PHPExcel_Style_Border::BORDER_DOUBLE, 'color' => ['rgb' => '000000']],
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'BFBFBF']],
            ],
        ];

        // Header info
        $sheet->setCellValue('A1', 'KARTU STOK FINISH GOOD (FG)');
        $sheet->getStyle('A1')->applyFromArray($styleTitle);

        $sheet->setCellValue('A2', 'Kode Produk: ' . $produk_fg . ' | Nama Produk: ' . ($stok_info ? $stok_info->nm_produk_fg : '-'));
        $sheet->setCellValue('A3', 'Periode Mutasi: ' . ($tgl_dari ? date('d-m-Y', strtotime($tgl_dari)) : 'Semua') . ' s/d ' . ($tgl_sampai ? date('d-m-Y', strtotime($tgl_sampai)) : 'Semua') . ' | Dicetak: ' . date('d-m-Y H:i:s'));

        // Table Header (Row 5)
        $row = 5;
        $headers = [
            'A' => 'No',
            'B' => 'Tgl Transaksi',
            'C' => 'No Referensi',
            'D' => 'No SPK',
            'E' => 'Kategori / Tipe',
            'F' => 'Jenis',
            'G' => 'Qty IN',
            'H' => 'Qty OUT',
            'I' => 'Saldo Qty',
            'J' => 'Berat IN (kg)',
            'K' => 'Berat OUT (kg)',
            'L' => 'Saldo Berat (kg)',
            'M' => 'Costbook (Rp)',
            'N' => 'Nilai Transaksi (Rp)',
            'O' => 'Saldo Nilai (Rp)',
            'P' => 'Keterangan',
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . $row, $text);
        }
        $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray($styleHeader);
        $sheet->getRowDimension($row)->setRowHeight(28);

        // Data Rows
        $curr_row    = 6;
        $no          = 1;
        $tot_qty_in  = 0;
        $tot_qty_out = 0;
        $tot_brt_in  = 0;
        $tot_brt_out = 0;

        foreach ($ledger as $item) {
            $qty_in   = (float) $item->qty_in;
            $qty_out  = (float) $item->qty_out;
            $berat_in = (float) $item->berat_in;
            $berat_out= (float) $item->berat_out;

            $tot_qty_in  += $qty_in;
            $tot_qty_out += $qty_out;
            $tot_brt_in  += $berat_in;
            $tot_brt_out += $berat_out;

            $sheet->setCellValueExplicit('A' . $curr_row, $no, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B' . $curr_row, $item->tgl_transaksi, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $curr_row, $item->no_referensi, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $curr_row, $item->spk_no ? $item->spk_no : '-', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $curr_row, $item->category_type ? $item->category_type : '-', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $curr_row, $item->jenis_mutasi, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G' . $curr_row, $qty_in > 0 ? $qty_in : 0, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $curr_row, $qty_out > 0 ? $qty_out : 0, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I' . $curr_row, (float) $item->qty_saldo, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J' . $curr_row, $berat_in > 0 ? $berat_in : 0, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('K' . $curr_row, $berat_out > 0 ? $berat_out : 0, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('L' . $curr_row, (float) $item->berat_saldo, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('M' . $curr_row, (float) $item->costbook, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('N' . $curr_row, (float) $item->total_nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('O' . $curr_row, (float) $item->saldo_nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('P' . $curr_row, $item->keterangan ? $item->keterangan : '-', PHPExcel_Cell_DataType::TYPE_STRING);

            // Styling & Alignment
            $sheet->getStyle('A' . $curr_row . ':P' . $curr_row)->applyFromArray($styleData);
            $sheet->getStyle('A' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('E' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            // Number formatting
            $sheet->getStyle('G' . $curr_row . ':I' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $curr_row . ':L' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('M' . $curr_row . ':O' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');

            $curr_row++;
            $no++;
        }

        // Summary Row
        $sheet->mergeCells('A' . $curr_row . ':F' . $curr_row);
        $sheet->setCellValue('A' . $curr_row, 'TOTAL MUTASI:');
        $sheet->setCellValueExplicit('G' . $curr_row, $tot_qty_in, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('H' . $curr_row, $tot_qty_out, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValue('I' . $curr_row, '');
        $sheet->setCellValueExplicit('J' . $curr_row, $tot_brt_in, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('K' . $curr_row, $tot_brt_out, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValue('L' . $curr_row, '');
        $sheet->setCellValue('M' . $curr_row, '');
        $sheet->setCellValue('N' . $curr_row, '');
        $sheet->setCellValue('O' . $curr_row, '');
        $sheet->setCellValue('P' . $curr_row, '');

        $sheet->getStyle('A' . $curr_row . ':P' . $curr_row)->applyFromArray($styleTotal);
        $sheet->getStyle('A' . $curr_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $curr_row . ':H' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('J' . $curr_row . ':K' . $curr_row)->getNumberFormat()->setFormatCode('#,##0.000');

        // Auto width
        foreach (range('A', 'P') as $colID) {
            $sheet->getColumnDimension($colID)->setAutoSize(true);
        }

        // Clean & Output
        $clean_code = preg_replace('/[^A-Za-z0-9_\-]/', '_', $produk_fg);
        $filename   = 'Kartu_Stok_FG_' . $clean_code . '_' . date('Ymd_His') . '.xls';
        $objWriter  = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }
}
