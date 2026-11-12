<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_report extends Admin_Controller
{
    protected $viewPermission   = 'Production_report.View';
    protected $addPermission    = 'Production_report.Add';
    protected $managePermission = 'Production_report.Manage';
    protected $deletePermission = 'Production_report.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            'production_report/Production_report_model' => 'pr_model'
        ]);
        $this->template->title('Laporan Produksi');
        $this->template->page_icon('fa fa-industry');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->render('index');
    }

    public function ajax_spk_list()
    {
        $search = $this->input->get('q', true);
        $data = $this->pr_model->get_spk_list($search);

        $total_spk = count($data);
        $done_count = 0;
        $waiting_count = 0;
        $total_target_qty = 0;

        foreach ($data as &$row) {
            $total_target_qty += (float) $row['total_target_qty'];
            if (!empty($row['report_id']) && $row['report_status_draft'] == 0) {
                $row['report_status'] = 'Done';
                $done_count++;
            } elseif (!empty($row['report_id']) && $row['report_status_draft'] == 1) {
                $pending_hold = $this->pr_model->has_pending_hold_claim($row['report_id']);
                if ($pending_hold) {
                    $row['report_status'] = 'On Hold';
                    $row['hold_claim_no'] = $pending_hold;
                } else {
                    $row['report_status'] = 'Draft';
                }
                $waiting_count++;
            } else {
                $row['report_status'] = 'Belum Ada';
                $waiting_count++;
            }
        }

        echo json_encode([
            'status' => 1,
            'data' => $data,
            'stats' => [
                'total_spk' => $total_spk,
                'waiting_count' => $waiting_count,
                'done_count' => $done_count,
                'total_target_qty' => $total_target_qty
            ]
        ]);
        exit;
    }

    public function create($spk_no = null)
    {
        $this->auth->restrict($this->addPermission);

        if (empty($spk_no)) {
            $this->session->set_flashdata('alert_data', [
                'type' => 'danger',
                'message' => 'Nomor SPK tidak ditemukan.'
            ]);
            redirect('production_report');
            return;
        }

        $spk_header = $this->pr_model->get_spk_header($spk_no);
        if (!$spk_header) {
            $this->session->set_flashdata('alert_data', [
                'type' => 'danger',
                'message' => 'Data SPK ' . htmlspecialchars($spk_no) . ' tidak valid atau tidak ditemukan.'
            ]);
            redirect('production_report');
            return;
        }

        if ($spk_header['status'] !== 'Released') {
            $this->session->set_flashdata('alert_data', [
                'type' => 'warning',
                'message' => 'SPK ' . htmlspecialchars($spk_no) . ' masih berstatus "' . htmlspecialchars($spk_header['status']) . '". Ubah status SPK menjadi Released di modul Request List terlebih dahulu.'
            ]);
            redirect('production_report');
            return;
        }

        $spk_products = $this->pr_model->get_spk_products($spk_no);
        $machines = $this->pr_model->get_machines();
        $employees = $this->pr_model->get_employees();
        $all_products = $this->pr_model->get_all_products();

        $draft_data = $this->pr_model->get_draft_by_spk($spk_no);

        $data = [
            'spk' => $spk_header,
            'spk_products' => $spk_products,
            'machines' => $machines,
            'employees' => $employees,
            'all_products' => $all_products,
            'draft' => $draft_data,
        ];

        $this->template->set($data);
        $this->template->render('form');
    }

    public function ajax_get_coils()
    {
        $source = $this->input->get('source', true);
        if (!in_array($source, ['unpack', 'wip', 'hold'])) {
            echo json_encode(['status' => 0, 'data' => [], 'message' => 'Invalid source']);
            exit;
        }

        $coils = $this->pr_model->get_coils_by_source($source);

        echo json_encode(['status' => 1, 'data' => $coils]);
        exit;
    }

    public function ajax_get_coil_claim_info()
    {
        $coil_id   = $this->input->get('coil_id', true);
        $coil_code = $this->input->get('coil_code', true);

        $info = $this->pr_model->get_coil_claim_info($coil_id, $coil_code);
        if ($info) {
            echo json_encode(['status' => 1, 'data' => $info]);
        } else {
            echo json_encode(['status' => 0, 'data' => null, 'message' => 'Data coil tidak ditemukan']);
        }
        exit;
    }

    public function upload_claim_bukti()
    {
        $upload_path = './uploads/claim_material/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|mp4|webm|m4v';
        $config['max_size']      = 5120; // 5 MB per berkas (instruksi user 2026-11-10)
        $config['encrypt_name']  = true;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file')) {
            echo json_encode([
                'status'  => 0,
                'message' => $this->upload->display_errors('', '')
            ]);
            exit;
        }

        $data = $this->upload->data();
        echo json_encode([
            'status'             => 1,
            'file_original_name' => $data['orig_name'],
            'file_hash_name'     => $data['file_name'],
            'file_size'          => $data['file_size'] * 1024,
            'file_type'          => $data['file_type'],
            'url'                => base_url('uploads/claim_material/' . $data['file_name'])
        ]);
        exit;
    }

    public function save()
    {
        $this->auth->restrict($this->addPermission);

        $json = file_get_contents('php://input');
        $payload = json_decode($json, true);

        if (empty($payload)) {
            $payload = $this->input->post();
        }

        if (empty($payload['spk_no'])) {
            echo json_encode(['status' => 0, 'message' => 'Nomor SPK wajib diisi!']);
            exit;
        }

        $spk_header = $this->pr_model->get_spk_header($payload['spk_no']);
        if (!$spk_header) {
            echo json_encode(['status' => 0, 'message' => 'SPK tidak valid']);
            exit;
        }

        if ($spk_header['status'] !== 'Released') {
            echo json_encode([
                'status'  => 0,
                'message' => 'SPK ' . $payload['spk_no'] . ' berstatus "' . $spk_header['status'] . '". Status SPK wajib Released di Request List sebelum membuat/menyimpan Laporan Produksi.'
            ]);
            exit;
        }

        $id_tr_spk_detail = !empty($payload['id_tr_spk_detail']) ? $payload['id_tr_spk_detail'] : $spk_header['primary_spk_detail_id'];

        $header_data = [
            'id_tr_spk_detail'                  => $id_tr_spk_detail,
            'tgl_produksi'                      => !empty($payload['tgl_produksi']) ? $payload['tgl_produksi'] : date('Y-m-d'),
            'id_asset_machine'                  => !empty($payload['id_asset_machine']) ? $payload['id_asset_machine'] : 0,
            'employee_helper'                   => !empty($payload['employee_helper']) ? (is_array($payload['employee_helper']) ? implode(', ', array_filter(array_map('trim', $payload['employee_helper']))) : $payload['employee_helper']) : null,
            'employee_setter'                   => !empty($payload['employee_setter']) ? $payload['employee_setter'] : null,
            'start_time'                        => !empty($payload['start_time']) ? $payload['start_time'] : null,
            'finished_time'                     => !empty($payload['finished_time']) ? $payload['finished_time'] : null,
            'summary_finish_good'               => !empty($payload['summary_finish_good']) ? (float)$payload['summary_finish_good'] : 0,
            'summary_kw2'                       => !empty($payload['summary_kw_2']) ? (float)$payload['summary_kw_2'] : 0,
            'summary_scrap_total'               => !empty($payload['summary_scrap']) ? (float)$payload['summary_scrap'] : 0,
            'summary_sisa_coil'                 => !empty($payload['summary_sisa_coil']) ? (float)$payload['summary_sisa_coil'] : 0,
            'summary_hold_coil'                 => !empty($payload['summary_hold_coil']) ? (float)$payload['summary_hold_coil'] : 0,
            'summary_nett_weight_produksi'      => !empty($payload['summary_net_produksi']) ? (float)$payload['summary_net_produksi'] : 0,
            'summary_nett_weight_packing_list'  => !empty($payload['summary_net_packing_list']) ? (float)$payload['summary_net_packing_list'] : 0,
            'summary_selisih'                   => !empty($payload['selisih_kg']) ? (float)$payload['selisih_kg'] : 0,
            'summary_selisih_percentage'        => !empty($payload['selisih_persen']) ? (float)$payload['selisih_persen'] : 0,
            'status_draft'                      => isset($payload['status_draft']) ? (int)$payload['status_draft'] : 0,
            'override_confirm_json'             => !empty($payload['confirmations']) ? json_encode($payload['confirmations']) : null,
            'created_at'                        => date('Y-m-d H:i:s'),
        ];

        $materials = [];
        if (!empty($payload['materials']) && is_array($payload['materials'])) {
            foreach ($payload['materials'] as $m) {
                if (empty($m['id_warehouse_stock_coil']) && empty($m['no_coil'])) continue;
                $materials[] = [
                    'source_warehouse'          => !empty($m['source_warehouse']) ? $m['source_warehouse'] : 'unpack',
                    'id_unpack_baby_coil'       => !empty($m['id_warehouse_stock_coil']) ? (int)$m['id_warehouse_stock_coil'] : null,
                    'coil_code'                 => !empty($m['no_coil']) ? $m['no_coil'] : '',
                    'material_name'             => !empty($m['material_name']) ? $m['material_name'] : '',
                    'nett_weight_packing'       => !empty($m['net_weight_packing_list']) ? (float)$m['net_weight_packing_list'] : 0,
                    'gross_weight'              => !empty($m['gross_weight_packing_list']) ? (float)$m['gross_weight_packing_list'] : 0,
                    'total_meter'               => !empty($m['total_meter']) ? (float)$m['total_meter'] : 0,
                    'berat_kulit'               => !empty($m['berat_kulit']) ? (float)$m['berat_kulit'] : 0,
                    'berat_clamp'               => !empty($m['berat_clamp']) ? (float)$m['berat_clamp'] : 0,
                ];
            }
        }

        $items = [];
        if (!empty($payload['items']) && is_array($payload['items'])) {
            foreach ($payload['items'] as $it) {
                if (empty($it['qty']) && empty($it['berat_total'])) continue;
                $items[] = [
                    'category_type'         => !empty($it['kategori']) ? $it['kategori'] : 'kw_1',
                    'product_lvl_4_id'      => !empty($it['id_product_lvl_4']) ? (int)$it['id_product_lvl_4'] : 0,
                    'no_uniq_id'            => !empty($it['no_uniq_id']) ? $it['no_uniq_id'] : null,
                    'nama_product_custom'   => !empty($it['nama_produk']) ? $it['nama_produk'] : null,
                    'source_material_coil'  => !empty($it['kode_baby_coil']) ? $it['kode_baby_coil'] : null,
                    'qty'                   => !empty($it['qty']) ? (int)$it['qty'] : 0,
                    'berat_total'           => !empty($it['berat_total']) ? (float)$it['berat_total'] : 0,
                    'berat_per_pcs'         => !empty($it['berat_per_pcs']) ? (float)$it['berat_per_pcs'] : 0,
                    'berat_standard'            => !empty($it['berat_standard_pcs']) ? (float)$it['berat_standard_pcs'] : 0,
                    'percentage_selisih'        => !empty($it['selisih_persen']) ? (float)$it['selisih_persen'] : 0,
                    'size_meter'                => !empty($it['size_meter']) ? (float)$it['size_meter'] : 0,
                    'panjang_meter'             => !empty($it['panjang_meter']) ? (float)$it['panjang_meter'] : 0,
                    'berat_per_meter_aktual'    => !empty($it['berat_per_meter_aktual']) ? (float)$it['berat_per_meter_aktual'] : 0,
                    'berat_per_meter_standard'  => !empty($it['berat_per_meter_standard']) ? (float)$it['berat_per_meter_standard'] : 0,
                    'percentage_selisih_meter'  => !empty($it['percentage_selisih_meter']) ? (float)$it['percentage_selisih_meter'] : 0,
                    'keterangan'                => !empty($it['keterangan']) ? $it['keterangan'] : null,
                ];
            }
        }

        $scraps = [];
        if (!empty($payload['scraps']) && is_array($payload['scraps'])) {
            foreach ($payload['scraps'] as $sc) {
                if (empty($sc['berat']) && empty($sc['jenis_scrap'])) continue;
                $scraps[] = [
                    'scrap_type'        => $sc['jenis_scrap'],
                    'target_coil_code'  => !empty($sc['kode_baby_coil']) ? $sc['kode_baby_coil'] : null,
                    'berat_total'       => !empty($sc['berat']) ? (float)$sc['berat'] : 0,
                    'keterangan'        => !empty($sc['keterangan']) ? $sc['keterangan'] : null,
                ];
            }
        }

        $claims = [];
        if (!empty($payload['claims']) && is_array($payload['claims'])) {
            foreach ($payload['claims'] as $cl) {
                // Validasi FR-94: Problem, Alasan Produksi, dan minimal 1 Bukti berkas wajib ada
                if (empty(trim($cl['problem'] ?? '')) || empty(trim($cl['alasan_produksi'] ?? ''))) {
                    echo json_encode([
                        'status'  => 0,
                        'message' => 'Problem dan Alasan Produksi wajib diisi untuk setiap baris Claim Material (FR-94)!'
                    ]);
                    exit;
                }
                if (empty($cl['bukti']) || !is_array($cl['bukti']) || count($cl['bukti']) < 1) {
                    echo json_encode([
                        'status'  => 0,
                        'message' => 'Bukti dokumen pendukung (foto/video/QC) wajib diunggah minimal 1 berkas per baris claim (FR-94)!'
                    ]);
                    exit;
                }
                $claims[] = $cl;
            }
        }

        $existing_report_id = !empty($payload['report_id']) ? (int)$payload['report_id'] : null;
        $is_submit          = isset($payload['status_draft']) && (int)$payload['status_draft'] === 0;

        // Pengecekan Submit Lock (FR-18, FR-98)
        if ($is_submit) {
            // 1. Jika pada input saat ini terdapat baris claim Hold Coil, tolak submit
            foreach ($claims as $cl) {
                if (!empty($cl['asal']) && $cl['asal'] === 'Hold Coil') {
                    echo json_encode([
                        'status'  => 0,
                        'message' => 'Submit ditolak: Laporan memuat material Hold Coil yang harus menunggu keputusan Purchasing (FR-18, FR-98). Silakan simpan sebagai Draft terlebih dahulu.'
                    ]);
                    exit;
                }
            }

            // 2. Jika mengedit draft yang sudah ada, cek apakah ada claim Hold Coil yang belum diputuskan
            if ($existing_report_id) {
                $pending_claim_no = $this->pr_model->has_pending_hold_claim($existing_report_id);
                if ($pending_claim_no) {
                    echo json_encode([
                        'status'  => 0,
                        'message' => 'Submit ditolak: Terdapat claim Hold Coil (' . $pending_claim_no . ') yang belum diputuskan oleh Purchasing (FR-18, FR-98). Silakan tunggu keputusan sebelum men-submit laporan.'
                    ]);
                    exit;
                }
            }
        }

        // Ambil snapshot data lama untuk audit log (standar AGENTS.md)
        $old_report = null;
        if ($existing_report_id) {
            $old_report = $this->db->get_where('production_reports', ['id' => $existing_report_id])->row_array();
        }

        $user_id   = $this->auth ? $this->auth->user_id() : 1;
        $report_id = $this->pr_model->save_production_report($header_data, $materials, $items, $scraps, $claims, $existing_report_id, $user_id);

        if ($report_id) {
            // Audit Log write_log (AGENTS.md)
            $new_report = $this->db->get_where('production_reports', ['id' => $report_id])->row_array();
            if ($existing_report_id && !empty($old_report)) {
                write_log('Production Report', 'Edit', 'Update Production Report ID: ' . $report_id . ' (SPK: ' . $payload['spk_no'] . ')', [
                    'old' => $old_report,
                    'new' => $new_report
                ], null, 1);
            } else {
                write_log('Production Report', 'Create', 'Create Production Report ID: ' . $report_id . ' (SPK: ' . $payload['spk_no'] . ')', [
                    'old' => [],
                    'new' => $new_report
                ], null, 1);
            }

            echo json_encode([
                'status'    => 1,
                'message'   => 'Laporan produksi berhasil disimpan!',
                'report_id' => $report_id
            ]);
        } else {
            echo json_encode([
                'status'  => 0,
                'message' => 'Gagal menyimpan laporan produksi. Silakan periksa kembali data Anda.'
            ]);
        }
        exit;
    }

    public function view($id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (empty($id)) {
            redirect('production_report');
            return;
        }

        $data['report'] = $this->pr_model->get_report_full($id);
        if (!$data['report']) {
            $this->session->set_flashdata('alert_data', [
                'type' => 'danger',
                'message' => 'Laporan produksi tidak ditemukan.'
            ]);
            redirect('production_report');
            return;
        }

        $this->template->set($data);
        $this->template->render('view_detail');
    }

    public function hpp($id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (empty($id)) {
            redirect('production_report');
            return;
        }

        $data['report'] = $this->pr_model->get_report_full($id);
        if (!$data['report']) {
            $this->session->set_flashdata('alert_data', [
                'type' => 'danger',
                'message' => 'Laporan produksi tidak ditemukan.'
            ]);
            redirect('production_report');
            return;
        }

        $data['rates'] = $this->pr_model->get_cost_rates();

        $this->template->title('Laporan HPP Produksi');
        $this->template->set($data);
        $this->template->render('view_hpp');
    }

    /**
     * Export Laporan Produksi (status Done / Closed) ke Excel (.xls).
     */
    public function export_excel($id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (empty($id)) {
            redirect('production_report');
            return;
        }

        $export = $this->pr_model->get_report_export_data($id);
        if (!$export || (int) ($export['header']['status_draft'] ?? 1) !== 0) {
            $this->session->set_flashdata('alert_data', [
                'type'    => 'danger',
                'message' => 'Export Report hanya tersedia untuk laporan produksi yang sudah selesai (Done).'
            ]);
            redirect('production_report');
            return;
        }

        error_reporting(0);
        write_log(
            'Production Report',
            'Export Excel',
            'Export Report Excel SPK: ' . $export['spk_no'],
            ['production_report_id' => (int) $id, 'spk_no' => $export['spk_no']],
            null,
            1
        );

        $objPHPExcel = $this->build_spk_export_workbook($export);
        $clean_spk   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $export['spk_no']);
        $filename    = 'Laporan_Produksi_' . $clean_spk . '_' . date('Ymd_His') . '.xls';

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
     * Susun workbook PHPExcel mengikuti template_report_excel_SPK (3 bagian).
     *
     * @param array $export
     * @return PHPExcel
     */
    public function build_spk_export_workbook(array $export)
    {
        $this->load->library('PHPExcel');
        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Report Produksi SPK');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $sheet->setCellValue('B2', 'No SPK :');
        $sheet->setCellValueExplicit('C2', (string) ($export['spk_no'] ?? '-'), PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getStyle('B2:C2')->getFont()->setBold(true)->setSize(11);

        $nextRow = $this->_write_export_section1($sheet, 4, $export['section1']);
        $nextRow = $this->_write_export_section2($sheet, $nextRow + 1, $export['section2']);
        $this->_write_export_section3($sheet, $nextRow + 1, $export['section3']);

        $widths = [
            'A' => 5,
            'B' => 34,
            'C' => 24,
            'D' => 28,
            'E' => 15,
            'F' => 14,
            'G' => 15,
            'H' => 15,
            'I' => 16,
            'J' => 14,
            'K' => 15,
            'L' => 14,
            'M' => 14,
            'N' => 14,
            'O' => 14,
            'P' => 12,
            'Q' => 13,
            'R' => 13,
            'S' => 13,
            'T' => 14,
            'U' => 16,
            'V' => 16,
            'W' => 16,
            'X' => 16,
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        return $objPHPExcel;
    }

    /**
     * Tulis Bagian 1: Pemakaian Material (B..X, 23 kolom).
     */
    private function _write_export_section1($sheet, $startRow, array $rows)
    {
        $sheet->setCellValue('A' . $startRow, 1);
        $sheet->setCellValue('B' . $startRow, 'Pemakaian Material');
        $sheet->getStyle('A' . $startRow . ':B' . $startRow)->getFont()->setBold(true)->setSize(11);

        $grpRow = $startRow + 1;
        $hdrRow = $startRow + 2;

        $groups = [
            ['B', 'F', 'Material In Packing List'],
            ['G', 'H', 'Out Produksi (dari Gudang Produksi 1 ke Gudang Produksi 2)'],
            ['I', 'I', 'Actual Timbang'],
            ['J', 'K', "Hasil Produksi\nKW 1"],
            ['L', 'M', "Hasil Produksi\nKW 2"],
            ['N', 'O', 'WIP (Sisa Material Produksi)'],
            ['P', 'Q', 'Bahan Packing'],
            ['R', 'X', 'Komponen Scrap'],
        ];
        foreach ($groups as $g) {
            if ($g[0] !== $g[1]) {
                $sheet->mergeCells($g[0] . $grpRow . ':' . $g[1] . $grpRow);
            }
            $sheet->setCellValue($g[0] . $grpRow, $g[2]);
        }

        $subHeaders = [
            'B' => 'Material',
            'C' => 'Kode Internal Material',
            'D' => 'Nett Weight',
            'E' => 'Gross Weight',
            'F' => 'Meter',
            'G' => 'Nett Weight',
            'H' => 'Meter',
            'I' => 'Act. Timbang Nett',
            'J' => 'Kg',
            'K' => 'Meter',
            'L' => 'Kg',
            'M' => 'Meter',
            'N' => 'Kg',
            'O' => 'Meter',
            'P' => 'Kulit',
            'Q' => 'Klem/Ring',
            'R' => 'Tong Coil',
            'S' => 'Wrapping',
            'T' => 'Potongan Pisau',
            'U' => 'Reject Product Internal',
            'V' => 'Reject Product Supplier',
            'W' => 'Reject Material Internal',
            'X' => 'Reject Material Supplier',
        ];
        foreach ($subHeaders as $col => $label) {
            $sheet->setCellValue($col . $hdrRow, $label);
        }

        $this->_style_export_header_range($sheet, 'B' . $grpRow . ':X' . $hdrRow);
        $sheet->getRowDimension($grpRow)->setRowHeight(32);
        $sheet->getRowDimension($hdrRow)->setRowHeight(28);

        $curr = $hdrRow + 1;

        $numCols = [
            'D' => 'pl_nett',
            'E' => 'pl_gross',
            'F' => 'pl_meter',
            'G' => 'out_nett',
            'H' => 'out_meter',
            'I' => 'act_timbang_nett',
            'J' => 'kw1_kg',
            'K' => 'kw1_meter',
            'L' => 'kw2_kg',
            'M' => 'kw2_meter',
            'N' => 'wip_kg',
            'O' => 'wip_meter',
            'P' => 'kulit',
            'Q' => 'clamp',
            'R' => 'tong_coil',
            'S' => 'wrapping',
            'T' => 'potongan_pisau',
            'U' => 'reject_product_internal',
            'V' => 'reject_product_supplier',
            'W' => 'reject_material_internal',
            'X' => 'reject_material_supplier',
        ];

        if (empty($rows)) {
            $sheet->mergeCells('B' . $curr . ':X' . $curr);
            $sheet->setCellValue('B' . $curr, 'Tidak ada data pemakaian material.');
            $sheet->getStyle('B' . $curr . ':X' . $curr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->_apply_export_table_borders($sheet, 'B' . $curr . ':X' . $curr);
            return $curr + 1;
        }

        $totals = array_fill_keys(array_keys($numCols), 0.0);
        foreach ($rows as $row) {
            $sheet->setCellValueExplicit('B' . $curr, (string) ($row['material'] ?? '-'), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $curr, (string) ($row['kode_internal'] ?? '-'), PHPExcel_Cell_DataType::TYPE_STRING);
            foreach ($numCols as $col => $key) {
                $val = (float) ($row[$key] ?? 0);
                $totals[$col] += $val;
                $sheet->setCellValueExplicit($col . $curr, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            }
            $sheet->getStyle('D' . $curr . ':X' . $curr)->getNumberFormat()->setFormatCode('#,##0.00');
            $this->_apply_export_table_borders($sheet, 'B' . $curr . ':X' . $curr);
            $curr++;
        }

        $sheet->mergeCells('B' . $curr . ':C' . $curr);
        $sheet->setCellValueExplicit('B' . $curr, 'Total', PHPExcel_Cell_DataType::TYPE_STRING);
        foreach ($totals as $col => $sum) {
            $sheet->setCellValueExplicit($col . $curr, round($sum, 4), PHPExcel_Cell_DataType::TYPE_NUMERIC);
        }
        $sheet->getStyle('D' . $curr . ':X' . $curr)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->_style_export_total_row($sheet, 'B' . $curr . ':X' . $curr);

        return $curr + 1;
    }

    /**
     * Tulis Bagian 2: Finish Good KW 1 (+ Stok Bebas) (B..K, 10 kolom).
     */
    private function _write_export_section2($sheet, $startRow, array $rows)
    {
        $sheet->setCellValue('A' . $startRow, 2);
        $sheet->setCellValue('B' . $startRow, 'Finish Good KW 1');
        $sheet->getStyle('A' . $startRow . ':B' . $startRow)->getFont()->setBold(true)->setSize(11);

        $hdrRow = $startRow + 2;
        $headers = [
            'B' => 'Produk',
            'C' => 'Qty Produksi',
            'D' => 'Material',
            'E' => 'Total Kg Produksi',
            'F' => 'Berat/Pcs',
            'G' => 'Std/pcs',
            'H' => '%Selisih per Pcs',
            'I' => 'Aktual Kg/Meter',
            'J' => 'Std Kg/Meter',
            'K' => '%Selisih per Meter',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $hdrRow, $label);
        }
        $this->_style_export_header_range($sheet, 'B' . $hdrRow . ':K' . $hdrRow);
        $sheet->getRowDimension($hdrRow)->setRowHeight(28);

        $curr = $hdrRow + 1;

        if (empty($rows)) {
            $sheet->mergeCells('B' . $curr . ':K' . $curr);
            $sheet->setCellValue('B' . $curr, 'Tidak ada data Finish Good KW 1.');
            $sheet->getStyle('B' . $curr . ':K' . $curr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->_apply_export_table_borders($sheet, 'B' . $curr . ':K' . $curr);
            return $curr + 1;
        }

        $totQty = 0.0;
        $totKg  = 0.0;
        foreach ($rows as $r) {
            $totQty += (float) $r['qty'];
            $totKg  += (float) $r['total_kg'];
            $sheet->setCellValueExplicit('B' . $curr, (string) $r['produk'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $curr, (float) $r['qty'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('D' . $curr, (string) $r['material'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $curr, (float) $r['total_kg'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F' . $curr, (float) $r['berat_per_pcs'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G' . $curr, (float) $r['std_pcs'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $curr, (float) $r['selisih_pcs_ratio'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I' . $curr, (float) $r['aktual_kg_meter'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J' . $curr, (float) $r['std_kg_meter'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('K' . $curr, (float) $r['selisih_meter_ratio'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle('C' . $curr)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('E' . $curr . ':G' . $curr)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('H' . $curr)->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle('I' . $curr . ':J' . $curr)->getNumberFormat()->setFormatCode('#,##0.0000');
            $sheet->getStyle('K' . $curr)->getNumberFormat()->setFormatCode('0.00%');
            $this->_apply_export_table_borders($sheet, 'B' . $curr . ':K' . $curr);
            $curr++;
        }

        $sheet->setCellValueExplicit('B' . $curr, 'Total', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C' . $curr, $totQty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('E' . $curr, round($totKg, 4), PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('C' . $curr)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E' . $curr)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->_style_export_total_row($sheet, 'B' . $curr . ':K' . $curr);

        return $curr + 1;
    }

    /**
     * Tulis Bagian 3: Finish Good KW 2 (B..H, 7 kolom).
     */
    private function _write_export_section3($sheet, $startRow, array $rows)
    {
        $sheet->setCellValue('A' . $startRow, 3);
        $sheet->setCellValue('B' . $startRow, 'Finish Good KW 2');
        $sheet->getStyle('A' . $startRow . ':B' . $startRow)->getFont()->setBold(true)->setSize(11);

        $hdrRow = $startRow + 2;
        $headers = [
            'B' => 'Produk',
            'C' => 'Qty Produksi',
            'D' => 'Material',
            'E' => 'Total Kg Produksi',
            'F' => 'Berat/Pcs',
            'G' => 'Panjang',
            'H' => 'Keterangan',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $hdrRow, $label);
        }
        $this->_style_export_header_range($sheet, 'B' . $hdrRow . ':H' . $hdrRow);
        $sheet->getRowDimension($hdrRow)->setRowHeight(28);

        $curr = $hdrRow + 1;

        if (empty($rows)) {
            $sheet->mergeCells('B' . $curr . ':H' . $curr);
            $sheet->setCellValue('B' . $curr, 'Tidak ada data Finish Good KW 2.');
            $sheet->getStyle('B' . $curr . ':H' . $curr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->_apply_export_table_borders($sheet, 'B' . $curr . ':H' . $curr);
            return $curr + 1;
        }

        $totQty = 0.0;
        $totKg  = 0.0;
        foreach ($rows as $r) {
            $totQty += (float) $r['qty'];
            $totKg  += (float) $r['total_kg'];
            $sheet->setCellValueExplicit('B' . $curr, (string) $r['produk'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $curr, (float) $r['qty'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('D' . $curr, (string) $r['material'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $curr, (float) $r['total_kg'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F' . $curr, (float) $r['berat_per_pcs'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G' . $curr, (float) $r['panjang'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H' . $curr, (string) $r['keterangan'], PHPExcel_Cell_DataType::TYPE_STRING);

            $sheet->getStyle('C' . $curr)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('E' . $curr . ':G' . $curr)->getNumberFormat()->setFormatCode('#,##0.00');
            $this->_apply_export_table_borders($sheet, 'B' . $curr . ':H' . $curr);
            $curr++;
        }

        $sheet->setCellValueExplicit('B' . $curr, 'Total', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C' . $curr, $totQty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('E' . $curr, round($totKg, 4), PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('C' . $curr)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E' . $curr)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->_style_export_total_row($sheet, 'B' . $curr . ':H' . $curr);

        return $curr + 1;
    }

    private function _style_export_header_range($sheet, $range)
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E79');
        $style->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $this->_apply_export_table_borders($sheet, $range);
    }

    private function _style_export_total_row($sheet, $range)
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true);
        $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EDF2F7');
        $this->_apply_export_table_borders($sheet, $range);
    }

    private function _apply_export_table_borders($sheet, $range)
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
    }
}
