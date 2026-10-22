<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rate_generals extends Admin_Controller
{
    protected $viewPermission   = 'Rate_Generals.View';
    protected $managePermission = 'Rate_Generals.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('rate_generals/Rate_generals_model', 'rg_model');
        $this->template->title('General Rate');
        $this->template->page_icon('fa fa-sliders');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $elements = $this->rg_model->get_elements();
        $rate_row = $this->rg_model->get_rate();

        $this->template->set([
            'elements' => $elements,
            'rate_row' => $rate_row,
        ]);
        $this->template->render('index');
    }

    /** Simpan satu elemen rate general (AJAX) */
    public function save_element()
    {
        $this->auth->restrict($this->managePermission);

        $key   = trim($this->input->post('key'));
        $val   = (float) $this->input->post('value');
        $unit  = trim($this->input->post('unit'));
        $label = trim($this->input->post('label'));

        if ($val < 0) {
            echo json_encode(['status' => 0, 'pesan' => 'Nilai rate tidak boleh negatif']);
            return;
        }

        // Jika satuan persen (pct), input UI berupa persen (misal 2.00), simpan ke DB sebagai desimal (0.0200)
        $db_val = ($unit === 'pct') ? ($val / 100) : $val;

        $save = $this->rg_model->update_element($key, $db_val, $this->auth->user_id());

        if ($save) {
            $formatted = ($unit === 'pct') ? number_format($val, 2, ',', '.') . ' %' : 'Rp ' . number_format($val, 2, ',', '.') . ' / Kg';
            write_log('General Rate', 'Edit', 'Update General Rate: ' . $label . ' menjadi ' . $formatted, [
                'key'   => $key,
                'value' => $db_val,
            ], null, 1);

            echo json_encode([
                'status' => 1,
                'pesan'  => 'Rate ' . $label . ' berhasil disimpan',
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Gagal menyimpan perubahan rate',
            ]);
        }
    }

    /** Simpan seluruh elemen rate sekaligus (Form POST) */
    public function save_all()
    {
        $this->auth->restrict($this->managePermission);

        $data = [
            'rate_consumables'                => (float) $this->input->post('rate_consumables'),
            'rate_scrap'                      => ((float) $this->input->post('rate_scrap')) / 100,
            'rate_biaya_koordinasi'           => (float) $this->input->post('rate_biaya_koordinasi'),
            'rate_other_cost_sales_marketing' => ((float) $this->input->post('rate_other_cost_sales_marketing')) / 100,
            'rate_other_cost_transport'       => ((float) $this->input->post('rate_other_cost_transport')) / 100,
            'rate_other_cost_finance_admin'   => ((float) $this->input->post('rate_other_cost_finance_admin')) / 100,
            'rate_other_cost_management_fee'  => ((float) $this->input->post('rate_other_cost_management_fee')) / 100,
            'rate_other_cost_interest'        => ((float) $this->input->post('rate_other_cost_interest')) / 100,
            'rate_other_cost_profit'          => ((float) $this->input->post('rate_other_cost_profit')) / 100,
        ];

        // Validasi negatif
        foreach ($data as $k => $v) {
            if ($v < 0) {
                $this->session->set_flashdata('error', 'Nilai rate tidak boleh ada yang bernilai negatif.');
                redirect('rate_generals');
                return;
            }
        }

        $save = $this->rg_model->update_all($data, $this->auth->user_id());

        if ($save) {
            write_log('General Rate', 'Batch Edit', 'Update seluruh komponen General Rate', $data, null, 1);
            $this->session->set_flashdata('success', 'Seluruh komponen General Rate berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui General Rate.');
        }

        redirect('rate_generals');
    }
}
