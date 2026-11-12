<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rate_bahan_pendukung_foh extends Admin_Controller
{
    protected $viewPermission   = 'Rate_Bahan_Pendukung_FOH.View';
    protected $managePermission = 'Rate_Bahan_Pendukung_FOH.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('rate_bahan_pendukung_foh/Rate_bahan_pendukung_foh_model', 'rbpf_model');
        $this->template->title('Rate Bahan Pendukung Khusus & FOH');
        $this->template->page_icon('fa fa-cubes');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $list      = $this->rbpf_model->get_list();
        $available = $this->rbpf_model->get_available_categories();

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
        $bpk      = (float) $this->input->post('rate_pendukung_khusus');
        $foh      = (float) $this->input->post('rate_foh');

        if (empty($id) && empty($code_lv2)) {
            $this->session->set_flashdata('error', 'Product Category wajib dipilih');
            redirect('rate_bahan_pendukung_foh');
        }

        if ($bpk < 0 || $foh < 0) {
            $this->session->set_flashdata('error', 'Nilai Rate tidak boleh negatif');
            redirect('rate_bahan_pendukung_foh');
        }

        $data = [
            'id'                    => $id,
            'code_lv2'              => $code_lv2,
            'rate_pendukung_khusus' => $bpk,
            'rate_foh'              => $foh,
        ];

        $old = !empty($id) ? $this->rbpf_model->get_rate_by_id($id) : null;
        $save = $this->rbpf_model->save_rate($data, $this->auth->user_id());

        if ($save) {
            $action = !empty($id) ? 'Edit' : 'Add';
            $cat_name = $old ? $old->nm_category : $code_lv2;
            $log = [
                'before' => $old ? (array)$old : null,
                'after'  => $data,
            ];
            write_log('Rate BPK & FOH', $action, $action . ' Rate BPK & FOH ' . $cat_name . ' (BPK: Rp ' . number_format($bpk, 2) . '/kg, FOH: Rp ' . number_format($foh, 2) . '/kg)', $log, null, 1);
            $this->session->set_flashdata('success', 'Rate Bahan Pendukung & FOH berhasil disimpan');
        } else {
            write_log('Rate BPK & FOH', 'Save', 'Gagal simpan Rate BPK & FOH', $data, null, 0);
            $this->session->set_flashdata('error', 'Gagal menyimpan Rate Bahan Pendukung & FOH');
        }

        redirect('rate_bahan_pendukung_foh');
    }

    public function delete()
    {
        $this->auth->restrict($this->managePermission);

        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid']);
            return;
        }

        $old = $this->rbpf_model->get_rate_by_id($id);
        $del = $this->rbpf_model->delete_rate($id);

        if ($del) {
            write_log('Rate BPK & FOH', 'Delete', 'Hapus Rate BPK & FOH ' . ($old ? $old->nm_category : $id), (array)$old, null, 1);
            echo json_encode(['status' => 1, 'pesan' => 'Rate berhasil dihapus']);
        } else {
            write_log('Rate BPK & FOH', 'Delete', 'Gagal hapus Rate ID ' . $id, [], null, 0);
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menghapus rate']);
        }
    }
}
