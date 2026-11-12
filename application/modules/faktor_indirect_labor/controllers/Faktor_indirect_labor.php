<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Faktor_indirect_labor extends Admin_Controller
{
    protected $viewPermission   = 'Faktor_Indirect_Labor.View';
    protected $managePermission = 'Faktor_Indirect_Labor.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('faktor_indirect_labor/Faktor_indirect_labor_model', 'fil_model');
        $this->template->title('Faktor Indirect Labor');
        $this->template->page_icon('fa fa-percent');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $faktor     = $this->fil_model->get_faktor();
        $simulation = $this->fil_model->get_impact_simulation();

        $this->template->set([
            'faktor'     => $faktor,
            'simulation' => $simulation,
        ]);
        $this->template->render('index');
    }

    public function save()
    {
        $this->auth->restrict($this->managePermission);

        $nilai = (float) $this->input->post('faktor_indirect_labor');

        if ($nilai <= 0) {
            $this->session->set_flashdata('error', 'Faktor Indirect Labor harus lebih besar dari 0');
            redirect('faktor_indirect_labor');
        }

        $old = $this->fil_model->get_faktor();
        $save = $this->fil_model->save_faktor($nilai, $this->auth->user_id());

        if ($save) {
            $log = [
                'before' => ['faktor_indirect_labor' => (float)$old->faktor_indirect_labor],
                'after'  => ['faktor_indirect_labor' => $nilai],
            ];
            write_log('Faktor Indirect Labor', 'Edit', 'Update Faktor Indirect Labor menjadi ' . $nilai, $log, null, 1);
            $this->session->set_flashdata('success', 'Faktor Indirect Labor berhasil disimpan');
        } else {
            write_log('Faktor Indirect Labor', 'Edit', 'Gagal update Faktor Indirect Labor', [], null, 0);
            $this->session->set_flashdata('error', 'Gagal menyimpan Faktor Indirect Labor');
        }

        redirect('faktor_indirect_labor');
    }
}
