<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Master_forwarding_cost extends Admin_Controller
{
    protected $viewPermission   = 'Master_forwarding_cost.View';
    protected $addPermission    = 'Master_forwarding_cost.Add';
    protected $managePermission = 'Master_forwarding_cost.Manage';
    protected $deletePermission = 'Master_forwarding_cost.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Master_forwarding_cost/Master_forwarding_cost_model');
        $this->template->title('Manage Forwarding Cost');
        $this->template->page_icon('fa fa-building-o');
        date_default_timezone_set('Asia/Bangkok');
        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data['forwarding_cost'] = $this->Master_forwarding_cost_model->get_data();

        history("View Master Forwarding Cost");
        $this->template->set($data);
        $this->template->title('Master Forwarding Cost');
        $this->template->render('index');
    }

    public function save()
    {
        $this->auth->restrict($this->managePermission);

        $id = $this->input->post('id');
        $value_cost = $this->input->post('value_cost');
        $remark = $this->input->post('remark');

        $data = [
            'value_cost'  => $value_cost,
            'remark'      => $remark,
            'update_by'   => $this->auth->nama(),
            'update_date' => $this->datetime
        ];

        if ($id) {
            // Update
            $result = $this->Master_forwarding_cost_model->save_data($data, $id);
            $msg = 'Update Forwarding Cost berhasil';
        } else {
            // Cek apakah sudah ada data aktif sebelum insert
            $existing = $this->Master_forwarding_cost_model->get_data();
            if (!empty($existing)) {
                echo json_encode(['status' => 'error', 'message' => 'Hanya boleh ada 1 data Forwarding Cost! Silakan edit data yang sudah ada.']);
                return;
            }

            $data['create_by']   = $this->auth->nama();
            $data['create_date'] = $this->datetime;
            $data['is_delete']   = '0';
            $result = $this->Master_forwarding_cost_model->save_data($data);
            $msg = 'Tambah Forwarding Cost berhasil';
        }

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data']);
        }
    }

    public function delete($id)
    {
        $this->auth->restrict($this->deletePermission);

        $result = $this->Master_forwarding_cost_model->delete_data($id, $this->auth->nama());

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Hapus Forwarding Cost berhasil']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data']);
        }
    }
}
