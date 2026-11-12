<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Master_standard_material extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Master_standard_material/Master_standard_material_model', 'std_model');
        $this->template->title('Master Standard Material');
        $this->template->page_icon('fa fa-cogs');
    }

    public function index()
    {
        $standard = $this->std_model->get_standard();
        $sample_materials = $this->std_model->get_sample_materials(10);
        $this->template->set([
            'standard'         => $standard,
            'sample_materials' => $sample_materials,
        ]);
        $this->template->render('index');
    }

    public function save()
    {
        $old = $this->std_model->get_standard();

        $toleransi   = $this->input->post('standar_toleransi');
        $berat_jenis = $this->input->post('standar_berat_jenis');
        $coating     = $this->input->post('coating');

        if ($berat_jenis <= 0) {
            $this->session->set_flashdata('error', 'Standar Berat Jenis harus lebih besar dari 0');
            redirect('master_standard_material');
        }

        $data = [
            'standar_toleransi'   => $toleransi !== null ? (float) $toleransi : 0.0,
            'standar_berat_jenis' => $berat_jenis !== null ? (float) $berat_jenis : 7.85,
            'coating'             => $coating !== null ? (float) $coating : 0.0,
            'updated_by'          => $this->auth->user_id(),
        ];

        $save = $this->std_model->save_standard($data);

        if ($save) {
            $logPayload = [
                'before' => [
                    'standar_toleransi'   => (float) $old->standar_toleransi,
                    'standar_berat_jenis' => (float) $old->standar_berat_jenis,
                    'coating'             => (float) $old->coating,
                ],
                'after'  => [
                    'standar_toleransi'   => (float) $data['standar_toleransi'],
                    'standar_berat_jenis' => (float) $data['standar_berat_jenis'],
                    'coating'             => (float) $data['coating'],
                ],
            ];
            write_log('Master Standard Material', 'Edit', 'Update Master Standard Material', $logPayload, null, 1);
            $this->session->set_flashdata('success', 'Master Standard Material berhasil disimpan');
        } else {
            write_log('Master Standard Material', 'Edit', 'Gagal update Master Standard Material', ['data' => $data], null, 0);
            $this->session->set_flashdata('error', 'Gagal menyimpan Master Standard Material');
        }

        redirect('master_standard_material');
    }
}
