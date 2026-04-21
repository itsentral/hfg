<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_weighing extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Production_weighing/Production_weighing_model');
        $this->template->title('Timbang Awal Coil');
        $this->template->page_icon('fa fa-balance-scale');
    }

    // -------------------------------------------------------------------------
    // Pages
    // -------------------------------------------------------------------------

    /** List semua record timbang awal */
    public function index()
    {
        $this->template->render('index');
    }

    /** Form timbang awal (scan barcode coil) */
    public function add()
    {
        $this->template->render('form_preweigh');
    }

    /** Detail timbang awal + selisih */
    public function view($preweigh_no)
    {
        $preweigh   = $this->Production_weighing_model->get_preweigh($preweigh_no);
        $components = $this->Production_weighing_model->get_preweigh_component($preweigh_no);

        if (!$preweigh) {
            $this->session->set_flashdata('error', 'Data timbang awal tidak ditemukan');
            redirect('production_weighing');
        }

        $this->template->set(['preweigh' => $preweigh, 'components' => $components]);
        $this->template->render('view_preweigh');
    }

    /** Halaman perbandingan timbang awal per SPK */
    public function perbandingan($spk_no)
    {
        $rows = $this->Production_weighing_model->get_perbandingan_spk($spk_no);

        $this->template->set(['spk_no' => $spk_no, 'rows' => $rows]);
        $this->template->render('perbandingan');
    }

    // -------------------------------------------------------------------------
    // AJAX / JSON Endpoints
    // -------------------------------------------------------------------------

    /** DataTables server-side endpoint */
    public function data_side_preweigh()
    {
        $result = $this->Production_weighing_model->get_list_for_datatable($_REQUEST);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $status_badge = $this->_status_badge($row->status);
            $selisih_pct  = number_format((isset($row->selisih_net_pct) ? $row->selisih_net_pct : 0) * 100, 2) . '%';
            $btn_view     = '<a href="' . base_url('production_weighing/view/' . $row->preweigh_no) . '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>';
            $btn_perband  = '<a href="' . base_url('production_weighing/perbandingan/' . $row->spk_no) . '" class="btn btn-sm btn-secondary ms-1" title="Perbandingan SPK"><i class="fa fa-bar-chart"></i></a>';

            $data[] = [
                $i + 1 + (int) $_REQUEST['start'],
                $row->preweigh_no,
                date('d/m/Y H:i', strtotime($row->created_at)),
                htmlspecialchars($row->spk_no),
                htmlspecialchars($row->no_coil),
                number_format($row->net_pl, 3),
                $selisih_pct,
                $status_badge,
                $btn_view . $btn_perband,
            ];
        }

        echo json_encode([
            'draw'            => (int) $_REQUEST['draw'],
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }

    /**
     * AJAX GET: ambil info coil + packing list data berdasarkan no_coil
     * Response: { status, message, coil_data, pl_data }
     */
    public function get_coil_info()
    {
        $no_coil = $this->input->get('no_coil');

        if (empty($no_coil)) {
            echo json_encode(['status' => 'error', 'message' => 'No coil tidak boleh kosong']);
            return;
        }

        $validation = $this->Production_weighing_model->validate_coil_for_preweigh($no_coil);

        if (!$validation['valid']) {
            echo json_encode(['status' => 'error', 'message' => $validation['message']]);
            return;
        }

        $pl_data = $this->Production_weighing_model->get_packing_list_data($no_coil);

        echo json_encode([
            'status'    => 'ok',
            'message'   => 'OK',
            'coil_data' => $validation['coil_data'],
            'pl_data'   => $pl_data,
        ]);
    }

    /**
     * POST: validasi input, hitung net weight, cek toleransi, simpan
     */
    public function save_preweigh()
    {
        $no_coil = $this->input->post('no_coil');
        $spk_no  = $this->input->post('spk_no');

        if (empty($no_coil) || empty($spk_no)) {
            $this->session->set_flashdata('error', 'No coil dan SPK wajib diisi');
            redirect('production_weighing/add');
            return;
        }

        // Validasi ulang coil
        $validation = $this->Production_weighing_model->validate_coil_for_preweigh($no_coil);
        if (!$validation['valid']) {
            $this->session->set_flashdata('error', $validation['message']);
            redirect('production_weighing/add');
            return;
        }

        $data = [
            'spk_no'       => $spk_no,
            'no_coil'      => $no_coil,
            'gross_actual' => (float) $this->input->post('gross_actual'),
            'gross_pl'     => (float) $this->input->post('gross_pl'),
            'net_pl'       => (float) $this->input->post('net_pl'),
            'created_by'   => $this->auth->user_id(),
        ];

        $components = [
            'berat_kulit'          => (float) $this->input->post('berat_kulit'),
            'berat_clamp_ring'     => (float) $this->input->post('berat_clamp_ring'),
            'berat_coil_tong'      => (float) $this->input->post('berat_coil_tong'),
            'berat_cover_wrapping' => (float) $this->input->post('berat_cover_wrapping'),
        ];

        $result = $this->Production_weighing_model->save_preweigh($data, $components);

        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
            redirect('production_weighing/view/' . $result['preweigh_no']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
            redirect('production_weighing/add');
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function _status_badge($status)
    {
        $map = [
            'Draft'     => 'secondary',
            'Confirmed' => 'success',
            'Exception' => 'danger',
        ];
        $color = isset($map[$status]) ? $map[$status] : 'secondary';
        return "<span class='badge bg-{$color}'>{$status}</span>";
    }
}
