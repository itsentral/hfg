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
                $row['report_status'] = 'Draft';
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

        $spk_products = $this->pr_model->get_spk_products($spk_no);
        $machines = $this->pr_model->get_machines();
        $employees = $this->pr_model->get_employees();
        $all_products = $this->pr_model->get_all_products();

        $data = [
            'spk' => $spk_header,
            'spk_products' => $spk_products,
            'machines' => $machines,
            'employees' => $employees,
            'all_products' => $all_products,
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

        $id_tr_spk_detail = !empty($payload['id_tr_spk_detail']) ? $payload['id_tr_spk_detail'] : $spk_header['primary_spk_detail_id'];

        $header_data = [
            'id_tr_spk_detail'      => $id_tr_spk_detail,
            'tgl_produksi'          => !empty($payload['tgl_produksi']) ? $payload['tgl_produksi'] : date('Y-m-d'),
            'id_asset_machine'      => !empty($payload['id_asset_machine']) ? $payload['id_asset_machine'] : 0,
            'employee_helper'       => !empty($payload['employee_helper']) ? $payload['employee_helper'] : null,
            'employee_setter'       => !empty($payload['employee_setter']) ? $payload['employee_setter'] : null,
            'start_time'            => !empty($payload['start_time']) ? $payload['start_time'] : null,
            'finished_time'         => !empty($payload['finished_time']) ? $payload['finished_time'] : null,
            'summary_finish_good'   => !empty($payload['summary_finish_good']) ? (float)$payload['summary_finish_good'] : 0,
            'summary_kw_2'          => !empty($payload['summary_kw_2']) ? (float)$payload['summary_kw_2'] : 0,
            'summary_scrap'         => !empty($payload['summary_scrap']) ? (float)$payload['summary_scrap'] : 0,
            'summary_sisa_coil'     => !empty($payload['summary_sisa_coil']) ? (float)$payload['summary_sisa_coil'] : 0,
            'summary_hold_coil'     => !empty($payload['summary_hold_coil']) ? (float)$payload['summary_hold_coil'] : 0,
            'summary_net_produksi'  => !empty($payload['summary_net_produksi']) ? (float)$payload['summary_net_produksi'] : 0,
            'summary_net_packing_list' => !empty($payload['summary_net_packing_list']) ? (float)$payload['summary_net_packing_list'] : 0,
            'selisih_kg'            => !empty($payload['selisih_kg']) ? (float)$payload['selisih_kg'] : 0,
            'selisih_persen'        => !empty($payload['selisih_persen']) ? (float)$payload['selisih_persen'] : 0,
            'status_draft'          => isset($payload['status_draft']) ? (int)$payload['status_draft'] : 0,
            'override_confirm_json' => !empty($payload['confirmations']) ? json_encode($payload['confirmations']) : null,
            'created_by'            => $this->auth->user_id(),
            'created_at'            => date('Y-m-d H:i:s'),
        ];

        $materials = [];
        if (!empty($payload['materials']) && is_array($payload['materials'])) {
            foreach ($payload['materials'] as $m) {
                if (empty($m['id_warehouse_stock_coil']) && empty($m['no_coil'])) continue;
                $materials[] = [
                    'source_warehouse'          => !empty($m['source_warehouse']) ? $m['source_warehouse'] : 'unpack',
                    'id_warehouse_stock_coil'   => !empty($m['id_warehouse_stock_coil']) ? (int)$m['id_warehouse_stock_coil'] : null,
                    'no_coil'                   => !empty($m['no_coil']) ? $m['no_coil'] : '',
                    'material_name'             => !empty($m['material_name']) ? $m['material_name'] : '',
                    'net_weight_packing_list'   => !empty($m['net_weight_packing_list']) ? (float)$m['net_weight_packing_list'] : 0,
                    'gross_weight_packing_list' => !empty($m['gross_weight_packing_list']) ? (float)$m['gross_weight_packing_list'] : 0,
                    'total_meter'               => !empty($m['total_meter']) ? (float)$m['total_meter'] : 0,
                    'berat_kulit'               => !empty($m['berat_kulit']) ? (float)$m['berat_kulit'] : 0,
                    'berat_clamp'               => !empty($m['berat_clamp']) ? (float)$m['berat_clamp'] : 0,
                    'created_by'                => $this->auth->user_id(),
                    'created_at'                => date('Y-m-d H:i:s'),
                ];
            }
        }

        $items = [];
        if (!empty($payload['items']) && is_array($payload['items'])) {
            foreach ($payload['items'] as $it) {
                if (empty($it['qty']) && empty($it['berat_total'])) continue;
                $items[] = [
                    'kategori'          => !empty($it['kategori']) ? $it['kategori'] : 'kw_1',
                    'id_product_lvl_4'  => !empty($it['id_product_lvl_4']) ? (int)$it['id_product_lvl_4'] : 0,
                    'nama_produk'       => !empty($it['nama_produk']) ? $it['nama_produk'] : '',
                    'kode_baby_coil'    => !empty($it['kode_baby_coil']) ? $it['kode_baby_coil'] : null,
                    'metode_input'      => !empty($it['metode_input']) ? (int)$it['metode_input'] : 1,
                    'qty'               => !empty($it['qty']) ? (int)$it['qty'] : 0,
                    'berat_total'       => !empty($it['berat_total']) ? (float)$it['berat_total'] : 0,
                    'berat_per_pcs'     => !empty($it['berat_per_pcs']) ? (float)$it['berat_per_pcs'] : 0,
                    'berat_standard_pcs'=> !empty($it['berat_standard_pcs']) ? (float)$it['berat_standard_pcs'] : 0,
                    'selisih_persen'    => !empty($it['selisih_persen']) ? (float)$it['selisih_persen'] : 0,
                    'size_meter'        => !empty($it['size_meter']) ? (float)$it['size_meter'] : 0,
                    'keterangan'        => !empty($it['keterangan']) ? $it['keterangan'] : null,
                    'created_by'        => $this->auth->user_id(),
                    'created_at'        => date('Y-m-d H:i:s'),
                ];
            }
        }

        $scraps = [];
        if (!empty($payload['scraps']) && is_array($payload['scraps'])) {
            foreach ($payload['scraps'] as $sc) {
                if (empty($sc['berat']) && empty($sc['jenis_scrap'])) continue;
                $scraps[] = [
                    'jenis_scrap'       => $sc['jenis_scrap'],
                    'kode_baby_coil'    => !empty($sc['kode_baby_coil']) ? $sc['kode_baby_coil'] : null,
                    'berat'             => !empty($sc['berat']) ? (float)$sc['berat'] : 0,
                    'keterangan'        => !empty($sc['keterangan']) ? $sc['keterangan'] : null,
                    'created_by'        => $this->auth->user_id(),
                    'created_at'        => date('Y-m-d H:i:s'),
                ];
            }
        }

        $report_id = $this->pr_model->save_production_report($header_data, $materials, $items, $scraps);

        if ($report_id) {
            echo json_encode([
                'status' => 1,
                'message' => 'Laporan produksi berhasil disimpan!',
                'report_id' => $report_id
            ]);
        } else {
            echo json_encode([
                'status' => 0,
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
}
