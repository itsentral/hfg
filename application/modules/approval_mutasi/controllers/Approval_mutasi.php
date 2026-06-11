<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Approval_mutasi extends Admin_Controller
{
    protected $viewPermission   = 'Approval_mutasi.View';
    protected $addPermission    = 'Approval_mutasi.Add';
    protected $managePermission = 'Approval_mutasi.Manage';
    protected $deletePermission = 'Approval_mutasi.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Approval_mutasi/Approval_mutasi_model');
        $this->template->title('Approval Mutasi');
        $this->template->page_icon('fa fa-exchange-alt');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->username = $this->auth->nama();
        $this->datetime = date('Y-m-d H:i:s');
    }

    // ---------------------------------------------------------------
    // INDEX
    // ---------------------------------------------------------------
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Approval Mutasi');
        $this->template->render('index');
    }

    // ---------------------------------------------------------------
    // RENDER TABLE DATA (HANYA STATUS = 1)
    // ---------------------------------------------------------------
    public function render_open()
    {
        $this->auth->restrict($this->viewPermission);

        // Diubah menjadi [1] saja agar memuat data status = 1 (Waiting Approval)
        $data['list'] = $this->Approval_mutasi_model->get_list([1]);

        $this->template->render('table/open_mutation', $data);
    }

    // ---------------------------------------------------------------
    // FORM (VIEW / APPROVAL MODE)
    // ---------------------------------------------------------------
    public function form($mode = 'view', $id = null)
    {
        $this->auth->restrict($this->viewPermission);

        $data['mode']       = $mode;
        $data['id']         = $id;
        $data['warehouses'] = $this->Approval_mutasi_model->get_all_warehouse();
        $data['mutation']   = null;

        if ($id) {
            $mutation = $this->Approval_mutasi_model->get_detail($id);

            if (!$mutation) {
                set_flashdata('error', 'Data mutasi tidak ditemukan.');
                redirect(site_url('Approval_mutasi'));
            }

            $data['mutation'] = $mutation;
        }

        $this->template->title(ucfirst($mode) . ' Approval Mutasi');
        $this->template->render('form', $data);
    }

    // ---------------------------------------------------------------
    // AJAX POST — SUBMIT APPROVAL DECISION
    // ---------------------------------------------------------------
    public function submit_approval()
    {
        $this->auth->restrict($this->managePermission);

        $id     = $this->input->post('id');
        $action = $this->input->post('action'); 
        $reason = $this->input->post('reason');

        if (!$id || !$action) {
            return $this->_json(['status' => 0, 'message' => 'Parameter pengenal tidak valid.']);
        }

        $mutation = $this->Approval_mutasi_model->get_detail($id);
        if (!$mutation) {
            return $this->_json(['status' => 0, 'message' => 'Data mutasi tidak ditemukan.']);
        }

        if ($mutation['status'] != 1) {
            return $this->_json(['status' => 0, 'message' => 'Status pengajuan ini sudah berubah, tidak dapat diproses lagi.']);
        }

        // Jalankan mapping status baru
        // status 2 = Approved, status 6 = Butuh Revisi
        if ($action === 'approve') {
            $new_status = 2;
            $msg_success = 'Pengajuan mutasi berhasil disetujui (Approved).';
        } elseif ($action === 'revisi') {
            $new_status = 6;
            $msg_success = 'Status pengajuan berhasil diubah menjadi butuh Revisi.';
        } else {
            return $this->_json(['status' => 0, 'message' => 'Tindakan aksi ilegal atau tidak dikenal.']);
        }

        $update = $this->Approval_mutasi_model->update_approval_status($id, [
            'status'        => $new_status,
            'reject_reason' => (!empty($reason)) ? $reason : null,
            'approved_by'   => $this->username,
            'approved_date' => $this->datetime
        ]);

        if ($update) {
            return $this->_json(['status' => 1, 'message' => $msg_success]);
        } else {
            return $this->_json(['status' => 0, 'message' => 'Gagal memperbarui status data ke database.']);
        }
    }

    private function _json($data)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
