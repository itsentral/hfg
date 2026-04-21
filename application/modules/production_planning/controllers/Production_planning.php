<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_planning extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Production_planning/Production_planning_model');
        $this->template->title('Production Planning');
        $this->template->page_icon('fa fa-industry');
    }

    public function index()
    {
        $this->template->render('index');
    }

    public function add()
    {
        $produk_list = $this->Production_planning_model->get_produk_fg_list();
        $this->template->set(['produk_list' => $produk_list]);
        $this->template->render('form');
    }

    public function edit($plan_no)
    {
        $plan    = $this->Production_planning_model->get_plan($plan_no);
        $details = $this->Production_planning_model->get_plan_details($plan_no);

        if (!$plan || $plan->status !== 'Draft') {
            redirect('production_planning');
        }

        $produk_list = $this->Production_planning_model->get_produk_fg_list();
        $this->template->set(['plan' => $plan, 'details' => $details, 'produk_list' => $produk_list]);
        $this->template->render('form');
    }

    public function view($plan_no)
    {
        $plan    = $this->Production_planning_model->get_plan($plan_no);
        $details = $this->Production_planning_model->get_plan_details($plan_no);
        $alloc   = $this->Production_planning_model->get_coil_alloc($plan_no);

        if (!$plan) {
            redirect('production_planning');
        }

        $this->template->set(['plan' => $plan, 'details' => $details, 'alloc' => $alloc]);
        $this->template->render('view');
    }

    public function monitoring()
    {
        $this->template->render('monitoring');
    }

    /** DataTables server-side endpoint */
    public function data_side_plan()
    {
        $result = $this->Production_planning_model->get_list_for_datatable($_REQUEST);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $status_badge = $this->_status_badge($row->status);
            $btn_view     = '<a href="' . base_url('production_planning/view/' . $row->plan_no) . '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>';
            $btn_edit     = $row->status === 'Draft'
                ? '<a href="' . base_url('production_planning/edit/' . $row->plan_no) . '" class="btn btn-sm btn-warning ms-1"><i class="fa fa-edit"></i></a>'
                : '';
            $btn_release  = $row->status === 'Draft'
                ? '<button class="btn btn-sm btn-success ms-1 btn-release" data-plan="' . $row->plan_no . '"><i class="fa fa-check"></i></button>'
                : '';
            $btn_cancel   = in_array($row->status, ['Draft', 'Released'])
                ? '<button class="btn btn-sm btn-danger ms-1 btn-cancel" data-plan="' . $row->plan_no . '"><i class="fa fa-times"></i></button>'
                : '';

            $data[] = [
                $i + 1 + (int) $_REQUEST['start'],
                $row->plan_no,
                $row->tgl_plan,
                $row->nm_produk_fg,
                number_format($row->target_qty, 2),
                $status_badge,
                $btn_view . $btn_edit . $btn_release . $btn_cancel,
            ];
        }

        echo json_encode([
            'draw'            => (int) $_REQUEST['draw'],
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }

    /** AJAX: ambil coil available */
    public function get_coil_available()
    {
        $id_produk_fg = $this->input->get('id_produk_fg');
        $coils        = $this->Production_planning_model->get_coil_available($id_produk_fg);
        echo json_encode(['status' => 'ok', 'data' => $coils]);
    }

    /** POST: simpan plan baru atau update */
    public function save_plan()
    {
        $plan_no = $this->input->post('plan_no');
        $data    = [
            'tgl_plan'     => $this->input->post('tgl_plan'),
            'id_produk_fg' => $this->input->post('id_produk_fg'),
            'nm_produk_fg' => $this->input->post('nm_produk_fg'),
            'target_qty'   => $this->input->post('target_qty'),
            'target_berat' => $this->input->post('target_berat'),
            'due_date'     => $this->input->post('due_date'),
            'catatan'      => $this->input->post('catatan'),
            'created_by'   => $this->auth->user_id(),
        ];

        $details_raw = $this->input->post('detail') ?: [];
        $details     = [];
        foreach ($details_raw as $d) {
            if (!empty($d['no_coil'])) {
                $details[] = [
                    'id_material'     => $d['id_material'],
                    'nm_material'     => $d['nm_material'],
                    'no_coil'         => $d['no_coil'],
                    'no_ros'          => $d['no_ros'],
                    'net_weight_coil' => $d['net_weight_coil'],
                    'estimasi_fg'     => $d['estimasi_fg'],
                ];
            }
        }

        if ($plan_no) {
            $result = $this->Production_planning_model->update_plan($plan_no, $data, $details);
        } else {
            $result = $this->Production_planning_model->save_plan($data, $details);
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Production Plan berhasil disimpan');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan Production Plan');
        }
        redirect('production_planning');
    }

    /** POST: release plan */
    public function process_release($plan_no)
    {
        $result = $this->Production_planning_model->release_plan($plan_no);
        echo json_encode($result);
    }

    /** POST: cancel plan */
    public function process_cancel($plan_no)
    {
        $result = $this->Production_planning_model->cancel_plan($plan_no);
        echo json_encode($result);
    }

    private function _status_badge($status)
    {
        $map = [
            'Draft'     => 'secondary',
            'Released'  => 'primary',
            'Closed'    => 'success',
            'Cancelled' => 'danger',
        ];
        $color = isset($map[$status]) ? $map[$status] : 'secondary';
        return "<span class='badge bg-{$color}'>{$status}</span>";
    }
}
