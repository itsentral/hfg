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
        $this->template->set($data);
        $this->template->title('Master Forwarding Cost');
        $this->template->render('index');
    }

    public function save()
    {
        $this->auth->restrict($this->managePermission);

        $id         = $this->input->post('id');
        $tipe       = strtolower($this->input->post('tipe') ?? '');
        $value_cost = $this->input->post('value_cost');
        $remark     = $this->input->post('remark');

        // Jika mode edit dan tipe kosong (karena disabled di form), pertahankan tipe aslinya dari DB
        if (!empty($id)) {
            $existingRow = $this->Master_forwarding_cost_model->get_data($id);
            if (!empty($existingRow)) {
                $tipe = $existingRow->tipe;
            }
        }
        if (empty($tipe)) {
            $tipe = 'import';
        }

        if (!in_array($tipe, ['import', 'lokal'])) {
            echo json_encode(['status' => 'error', 'message' => 'Pilihan tipe tidak valid (harus Import atau Lokal)!']);
            return;
        }

        // Cek duplikasi tipe
        if ($this->Master_forwarding_cost_model->check_existing_tipe($tipe, $id)) {
            echo json_encode(['status' => 'error', 'message' => 'Tarif Forwarding Cost untuk tipe ' . strtoupper($tipe) . ' sudah ada! Silakan edit data yang sudah ada.']);
            return;
        }

        $data = [
            'tipe'        => $tipe,
            'value_cost'  => $value_cost,
            'remark'      => $remark,
            'update_by'   => $this->auth->nama(),
            'update_date' => $this->datetime
        ];

        $oldData = ($id) ? $this->Master_forwarding_cost_model->get_data($id) : null;
        if ($id) {
            // Update
            $result = $this->Master_forwarding_cost_model->save_data($data, $id);
            $msg = 'Update Forwarding Cost ' . ucfirst($tipe) . ' berhasil';
        } else {
            $data['create_by']   = $this->auth->nama();
            $data['create_date'] = $this->datetime;
            $data['is_delete']   = '0';
            $result = $this->Master_forwarding_cost_model->save_data($data);
            $msg = 'Tambah Forwarding Cost ' . ucfirst($tipe) . ' berhasil';
        }

        $logPayload = ($id && !empty($oldData)) ? ['old' => $oldData, 'new' => $data] : $data;

        if ($result) {
            write_log('Master Forwarding Cost', $id ? 'Edit' : 'Add', $msg, $logPayload, null, 1);
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } else {
            write_log('Master Forwarding Cost', $id ? 'Edit' : 'Add', 'Gagal menyimpan Forwarding Cost', $logPayload, null, 0);
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data']);
        }
    }

    public function delete($id)
    {
        $this->auth->restrict($this->deletePermission);

        $result = $this->Master_forwarding_cost_model->delete_data($id, $this->auth->nama());

        if ($result) {
            write_log('Master Forwarding Cost', 'Delete', 'Hapus Forwarding Cost ID: ' . $id, ['id' => $id], null, 1);
            echo json_encode(['status' => 'success', 'message' => 'Hapus Forwarding Cost berhasil']);
        } else {
            write_log('Master Forwarding Cost', 'Delete', 'Gagal menghapus Forwarding Cost ID: ' . $id, ['id' => $id], null, 0);
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data']);
        }
    }
}
