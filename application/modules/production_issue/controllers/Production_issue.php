<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_issue extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Production_issue/Production_issue_model');
        $this->load->helper('config_param');
        $this->template->title('Production Issue');
        $this->template->page_icon('fa fa-barcode');
    }

    // -------------------------------------------------------------------------
    // Pages
    // -------------------------------------------------------------------------

    /**
     * List SPK
     */
    public function index()
    {
        $this->template->render('index');
    }

    /**
     * Form buat SPK dari plan
     */
    public function add($plan_no = null)
    {
        $plan    = null;
        $details = [];

        if ($plan_no) {
            $this->load->model('Production_planning/Production_planning_model');
            $plan    = $this->Production_planning_model->get_plan($plan_no);
            $details = $this->Production_planning_model->get_plan_details($plan_no);

            if (!$plan || $plan->status !== 'Released') {
                $this->session->set_flashdata('error', 'Plan tidak ditemukan atau belum Released');
                redirect('production_issue');
            }
        }

        $this->template->set(['plan' => $plan, 'details' => $details]);
        $this->template->render('form_spk');
    }

    /**
     * Detail SPK + log scan
     */
    public function view($spk_no)
    {
        $spk     = $this->Production_issue_model->get_spk($spk_no);
        $details = $this->Production_issue_model->get_spk_details($spk_no);
        $logs    = $this->Production_issue_model->get_spk_scan_log($spk_no);

        if (!$spk) {
            $this->session->set_flashdata('error', 'SPK tidak ditemukan');
            redirect('production_issue');
        }

        // Hitung progress scan dari scan_status di detail coil
        $total_coil   = count($details);
        $scanned_coil = 0;
        foreach ($details as $d) {
            if ($d->scan_status === 'scanned') {
                $scanned_coil++;
            }
        }

        $this->template->set([
            'spk'          => $spk,
            'details'      => $details,
            'logs'         => $logs,
            'total_coil'   => $total_coil,
            'scanned_coil' => $scanned_coil,
        ]);
        $this->template->render('view_spk');
    }

    /**
     * Halaman monitoring coil in production
     */
    public function monitoring_coil()
    {
        $coils = $this->Production_issue_model->get_coil_in_production();
        $this->template->set(['coils' => $coils]);
        $this->template->render('monitoring_coil');
    }

    /**
     * Riwayat mutasi coil
     */
    public function histori_coil($no_coil = null)
    {
        if (!$no_coil) {
            $no_coil = $this->input->get('no_coil');
        }

        $history = [];
        if ($no_coil) {
            $history = $this->Production_issue_model->get_coil_history($no_coil);
        }

        $this->template->set(['no_coil' => $no_coil, 'history' => $history]);
        $this->template->render('histori_coil');
    }

    /**
     * Halaman scan barcode issue material
     */
    public function scan_issue()
    {
        // Ambil daftar SPK Released untuk dropdown
        $spk_list = $this->db->where('status', 'Released')
            ->order_by('spk_no', 'DESC')
            ->get('tr_spk_production')->result();

        $this->template->set(['spk_list' => $spk_list]);
        $this->template->render('scan_issue');
    }

    // -------------------------------------------------------------------------
    // JSON Endpoints
    // -------------------------------------------------------------------------

    /**
     * AJAX: info SPK + detail coil untuk halaman scan
     */
    public function get_spk_info()
    {
        $spk_no = $this->input->get('spk_no');
        $spk    = $this->Production_issue_model->get_spk($spk_no);
        if (!$spk) {
            echo json_encode(['status' => 'error', 'message' => 'SPK tidak ditemukan']);
            return;
        }
        $details = $this->Production_issue_model->get_spk_details($spk_no);

        $scanned_coil = 0;
        foreach ($details as $d) {
            if ($d->scan_status === 'scanned') {
                $scanned_coil++;
            }
        }

        $spk->total_coil   = count($details);
        $spk->scanned_coil = $scanned_coil;

        echo json_encode(['status' => 'ok', 'spk' => $spk, 'details' => $details]);
    }

    /**
     * AJAX: log scan hari ini untuk SPK tertentu
     */
    public function get_scan_log_today()
    {
        $spk_no = $this->input->get('spk_no');
        $today  = date('Y-m-d');

        $logs = $this->db->select('l.no_coil, l.spk_no, l.status_scan, l.keterangan,
                DATE_FORMAT(l.scan_time, "%H:%i:%s") AS scan_time')
            ->from('tr_spk_scan_log l')
            ->where('l.spk_no', $spk_no)
            ->where('DATE(l.scan_time)', $today)
            ->order_by('l.scan_time', 'DESC')
            ->get()->result();

        echo json_encode(['status' => 'ok', 'data' => $logs]);
    }

    /**
     * DataTables server-side endpoint
     */
    public function data_side_spk()
    {
        $result = $this->Production_issue_model->get_list_for_datatable($_REQUEST);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $status_badge = $this->_status_badge($row->status);
            $btn_view     = '<a href="' . base_url('production_issue/view/' . $row->spk_no) . '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>';
            $btn_release  = $row->status === 'Draft'
                ? '<button class="btn btn-sm btn-success ms-1 btn-release-spk" data-spk="' . $row->spk_no . '"><i class="fa fa-check"></i></button>'
                : '';
            $btn_scan     = $row->status === 'Released'
                ? '<a href="' . base_url('production_issue/scan_issue?spk_no=' . $row->spk_no) . '" class="btn btn-sm btn-warning ms-1"><i class="fa fa-barcode"></i></a>'
                : '';

            $data[] = [
                $i + 1 + (int) $_REQUEST['start'],
                $row->spk_no,
                $row->tgl_spk,
                $row->plan_no,
                $row->nm_produk_fg,
                number_format($row->target_qty, 2),
                $status_badge,
                $btn_view . $btn_release . $btn_scan,
            ];
        }

        echo json_encode([
            'draw'            => (int) $_REQUEST['draw'],
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST Actions
    // -------------------------------------------------------------------------

    /**
     * POST: simpan SPK baru
     */
    public function save_spk()
    {
        $data = [
            'plan_no'      => $this->input->post('plan_no'),
            'produk_fg'    => $this->input->post('produk_fg'),
            'nm_produk_fg' => $this->input->post('nm_produk_fg'),
            'target_qty'   => $this->input->post('target_qty'),
            'tgl_spk'      => $this->input->post('tgl_spk'),
            'due_date'     => $this->input->post('due_date') ?: null,
            'catatan'      => $this->input->post('catatan'),
            'created_by'   => $this->auth->user_id(),
        ];

        $coils_raw = $this->input->post('coil') ?: [];
        $coils     = [];
        foreach ($coils_raw as $c) {
            if (!empty($c['no_coil'])) {
                $coils[] = [
                    'no_coil'     => $c['no_coil'],
                    'id_material' => isset($c['id_material']) ? $c['id_material'] : null,
                    'nm_material' => isset($c['nm_material']) ? $c['nm_material'] : null,
                    'no_ros'      => isset($c['no_ros']) ? $c['no_ros'] : null,
                    'net_weight'  => isset($c['net_weight']) ? $c['net_weight'] : null,
                ];
            }
        }

        $result = $this->Production_issue_model->save_spk($data, $coils);

        if ($result) {
            $this->session->set_flashdata('success', 'SPK ' . $result . ' berhasil dibuat');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan SPK');
        }
        redirect('production_issue');
    }

    /**
     * POST: release SPK, return JSON
     */
    public function process_release_spk($spk_no)
    {
        $result = $this->Production_issue_model->release_spk($spk_no);
        echo json_encode($result);
    }

    /**
     * POST: validasi + proses scan barcode, return JSON
     */
    public function process_scan()
    {
        $no_coil = trim($this->input->post('no_coil'));
        $spk_no  = trim($this->input->post('spk_no'));
        $user_id = $this->auth->user_id();

        if (empty($no_coil) || empty($spk_no)) {
            echo json_encode([
                'success' => false,
                'message' => 'No coil dan No SPK wajib diisi',
                'data'    => [],
            ]);
            return;
        }

        $result = $this->Production_issue_model->process_scan($no_coil, $spk_no, $user_id);
        echo json_encode($result);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function _status_badge($status)
    {
        $map = [
            'Draft'      => 'secondary',
            'Released'   => 'primary',
            'In Process' => 'warning',
            'Submitted'  => 'info',
            'Closed'     => 'success',
            'Cancelled'  => 'danger',
        ];
        $color = isset($map[$status]) ? $map[$status] : 'secondary';
        return "<span class='badge bg-{$color}'>{$status}</span>";
    }
}
