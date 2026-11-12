<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rate_borongan_helper extends Admin_Controller
{
    protected $viewPermission   = 'Rate_Borongan_Helper.View';
    protected $managePermission = 'Rate_Borongan_Helper.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('rate_borongan_helper/Rate_borongan_helper_model', 'rbh_model');
        $this->template->title('Rate Borongan Helper');
        $this->template->page_icon('fa fa-handshake-o');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $list      = $this->rbh_model->get_list();
        $available = $this->rbh_model->get_available_categories();

        $this->template->set([
            'list'      => $list,
            'available' => $available,
        ]);
        $this->template->render('index');
    }

    public function save()
    {
        $this->auth->restrict($this->managePermission);

        $id       = $this->input->post('id');
        $code_lv2 = trim($this->input->post('code_lv2'));
        $rate     = (float) $this->input->post('rate_borongan_helper');

        if (empty($id) && empty($code_lv2)) {
            $this->session->set_flashdata('error', 'Product Category wajib dipilih');
            redirect('rate_borongan_helper');
        }

        if ($rate < 0) {
            $this->session->set_flashdata('error', 'Rate Borongan Helper tidak boleh negatif');
            redirect('rate_borongan_helper');
        }

        $data = [
            'id'                   => $id,
            'code_lv2'             => $code_lv2,
            'rate_borongan_helper' => $rate,
        ];

        $old = !empty($id) ? $this->rbh_model->get_rate_by_id($id) : null;
        $save = $this->rbh_model->save_rate($data, $this->auth->user_id());

        if ($save) {
            $action = !empty($id) ? 'Edit' : 'Add';
            $cat_name = $old ? $old->nm_category : $code_lv2;
            $log = [
                'before' => $old ? (array)$old : null,
                'after'  => $data,
            ];
            write_log('Rate Borongan Helper', $action, $action . ' Rate Borongan ' . $cat_name . ' (Rp ' . number_format($rate, 2, ',', '.') . '/m)', $log, null, 1);
            $this->session->set_flashdata('success', 'Rate Borongan Helper berhasil disimpan');
        } else {
            write_log('Rate Borongan Helper', 'Save', 'Gagal simpan Rate Borongan Helper', $data, null, 0);
            $this->session->set_flashdata('error', 'Gagal menyimpan Rate Borongan Helper');
        }

        redirect('rate_borongan_helper');
    }

    public function delete()
    {
        $this->auth->restrict($this->managePermission);

        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid']);
            return;
        }

        $old = $this->rbh_model->get_rate_by_id($id);
        $del = $this->rbh_model->delete_rate($id);

        if ($del) {
            write_log('Rate Borongan Helper', 'Delete', 'Hapus Rate Borongan ' . ($old ? $old->nm_category : $id), (array)$old, null, 1);
            echo json_encode(['status' => 1, 'pesan' => 'Rate Borongan Helper berhasil dihapus']);
        } else {
            write_log('Rate Borongan Helper', 'Delete', 'Gagal hapus Rate Borongan ID ' . $id, [], null, 0);
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menghapus rate']);
        }
    }
}
