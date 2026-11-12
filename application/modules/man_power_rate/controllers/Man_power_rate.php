<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Man_power_rate extends Admin_Controller
{
    protected $viewPermission   = 'Man_Power_Rate.View';
    protected $managePermission = 'Man_Power_Rate.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('man_power_rate/Man_power_rate_model', 'mpr_model');
        $this->template->title('Man Power Rate (Operator)');
        $this->template->page_icon('fa fa-users');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $rate       = $this->mpr_model->get_rate();
        $components = $this->mpr_model->get_components();
        $faktor     = $this->mpr_model->get_faktor_indirect();
        $simulation = $this->mpr_model->get_simulation_data();

        $this->template->set([
            'rate'       => $rate,
            'components' => $components,
            'faktor'     => $faktor,
            'simulation' => $simulation,
        ]);
        $this->template->render('index');
    }

    /** Simpan komponen (Salary, BPJS, Biaya Lain) */
    public function save_component()
    {
        $this->auth->restrict($this->managePermission);

        $id            = $this->input->post('id');
        $kelompok      = $this->input->post('kelompok');
        $nama_komponen = trim($this->input->post('nama_komponen'));
        $nilai         = (float) $this->input->post('nilai');
        $keterangan    = trim($this->input->post('keterangan'));

        if (empty($nama_komponen)) {
            $this->session->set_flashdata('error', 'Nama Komponen wajib diisi');
            redirect('man_power_rate');
        }

        if ($nilai < 0) {
            $this->session->set_flashdata('error', 'Nilai komponen tidak boleh negatif');
            redirect('man_power_rate');
        }

        $data = [
            'id'            => $id,
            'kelompok'      => $kelompok,
            'nama_komponen' => $nama_komponen,
            'nilai'         => $nilai,
            'keterangan'    => $keterangan,
        ];

        $old = !empty($id) ? $this->mpr_model->get_component_by_id($id) : null;
        $save = $this->mpr_model->save_component($data, $this->auth->user_id());

        if ($save) {
            $action = !empty($id) ? 'Edit' : 'Add';
            $log = [
                'before' => $old ? (array)$old : null,
                'after'  => $data,
            ];
            write_log('Man Power Rate', $action, $action . ' Komponen ' . $nama_komponen . ' (Rp ' . number_format($nilai, 0, ',', '.') . ')', $log, null, 1);
            $this->session->set_flashdata('success', 'Komponen berhasil disimpan');
        } else {
            write_log('Man Power Rate', 'Save', 'Gagal simpan komponen ' . $nama_komponen, $data, null, 0);
            $this->session->set_flashdata('error', 'Gagal menyimpan komponen');
        }

        redirect('man_power_rate');
    }

    /** Hapus komponen */
    public function delete_component()
    {
        $this->auth->restrict($this->managePermission);

        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid']);
            return;
        }

        $old = $this->mpr_model->get_component_by_id($id);
        $del = $this->mpr_model->delete_component($id, $this->auth->user_id());

        if ($del) {
            write_log('Man Power Rate', 'Delete', 'Hapus komponen ' . ($old ? $old->nama_komponen : $id), (array)$old, null, 1);
            echo json_encode(['status' => 1, 'pesan' => 'Komponen berhasil dihapus']);
        } else {
            write_log('Man Power Rate', 'Delete', 'Gagal hapus komponen ID ' . $id, [], null, 0);
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menghapus komponen']);
        }
    }

    /** Simpan parameter (Jam Kerja & Porsi Operator) */
    public function save_parameters()
    {
        $this->auth->restrict($this->managePermission);

        $jam_kerja = (float) $this->input->post('jam_kerja_per_bulan');
        $porsi_den = (float) $this->input->post('porsi_operator_den');

        if ($jam_kerja <= 0 || $porsi_den <= 0) {
            $this->session->set_flashdata('error', 'Jam Kerja dan Porsi Operator harus lebih besar dari 0');
            redirect('man_power_rate');
        }

        $old = $this->mpr_model->get_rate();
        $save = $this->mpr_model->save_parameters($jam_kerja, $porsi_den, $this->auth->user_id());

        if ($save) {
            $log = [
                'before' => [
                    'jam_kerja_per_bulan' => $old->jam_kerja_per_bulan,
                    'porsi_operator'      => $old->porsi_operator,
                    'rate_1_3_operator'   => $old->rate_1_3_operator,
                ],
                'after' => [
                    'jam_kerja_per_bulan' => $jam_kerja,
                    'porsi_operator_den'  => $porsi_den,
                ],
            ];
            write_log('Man Power Rate', 'Edit', 'Update Parameter Man Power Rate (Jam: ' . $jam_kerja . ', Porsi: 1/' . $porsi_den . ')', $log, null, 1);
            $this->session->set_flashdata('success', 'Parameter Man Power Rate berhasil diperbarui');
        } else {
            write_log('Man Power Rate', 'Edit', 'Gagal update Parameter Man Power Rate', [], null, 0);
            $this->session->set_flashdata('error', 'Gagal memperbarui parameter');
        }

        redirect('man_power_rate');
    }
}
