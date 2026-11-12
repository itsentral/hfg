<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Simulasi_costing extends Admin_Controller
{
    protected $viewPermission   = 'Simulasi_Costing.View';
    protected $managePermission = 'Simulasi_Costing.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('simulasi_costing/Simulasi_costing_model', 'sc_model');
        $this->template->title('Simulasi Costing');
        $this->template->page_icon('fa fa-calculator');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $cat_filter = $this->input->get('category');
        $costings   = $this->sc_model->calculate_all_costing($cat_filter);
        $categories = $this->sc_model->get_categories();

        // Hitung kelengkapan data & varian yang belum diupdate ke snapshot
        $incomplete_count = 0;
        foreach ($costings as $c) {
            if (!$c->is_lengkap) $incomplete_count++;
        }

        $this->template->set([
            'costings'         => $costings,
            'categories'       => $categories,
            'cat_filter'       => $cat_filter,
            'incomplete_count' => $incomplete_count,
        ]);
        $this->template->render('index');
    }

    /** Jalankan Update Costing (AJAX) */
    public function update_costing()
    {
        $this->auth->restrict($this->managePermission);

        $res = $this->sc_model->execute_update_costing($this->auth->user_id());

        write_log('Simulasi Costing', 'Update Costing', 'Update Costing dijalankan (' . $res['entries'] . ' perubahan dicatat, ' . $res['notif'] . ' notifikasi baru)', $res, null, 1);

        $msg_parts = [];
        if ($res['entries'] > 0) $msg_parts[] = $res['entries'] . ' perubahan dicatat di History Costing';
        if ($res['notif'] > 0) $msg_parts[] = $res['notif'] . ' notifikasi Bottom Price baru untuk Sales';

        $text = !empty($msg_parts) ? 'Costing berhasil diperbarui: ' . implode('; ', $msg_parts) . '.' : 'Semua costing sudah mutakhir. Tidak ada perubahan nilai.';

        echo json_encode([
            'status'  => 1,
            'pesan'   => $text,
            'entries' => $res['entries'],
            'notif'   => $res['notif'],
        ]);
    }

    /** Masukkan Varian ke Draft Pricelist (AJAX) */
    public function add_draft()
    {
        $this->auth->restrict($this->managePermission);

        $bom_detail_id = $this->input->post('bom_detail_id');
        $no_uniq_id    = trim($this->input->post('no_uniq_id'));
        if (empty($bom_detail_id) && empty($no_uniq_id)) {
            echo json_encode(['status' => 0, 'pesan' => 'Identifier varian tidak valid']);
            return;
        }

        $res = $this->sc_model->add_to_draft_pricelist($bom_detail_id, $no_uniq_id, $this->auth->user_id());
        if ($res['status'] == 1) {
            $identifier_log = !empty($no_uniq_id) ? "Varian {$no_uniq_id}" : "BOM Detail #{$bom_detail_id}";
            write_log('Simulasi Costing', 'Add Draft', "{$identifier_log} dimasukkan ke Draft Pricelist {$res['no_pl']}", $res, null, 1);
        }

        echo json_encode($res);
    }

    /** Ambil Detail Breakdown Costing untuk Modal (AJAX) */
    public function detail_json()
    {
        $this->auth->restrict($this->viewPermission);

        $bom_detail_id = $this->input->get('bom_detail_id');
        $no_uniq_id    = trim($this->input->get('no_uniq_id'));
        $costings      = $this->sc_model->calculate_all_costing();

        $target = null;
        if (!empty($bom_detail_id)) {
            foreach ($costings as $c) {
                if ($c->bom_detail_id == $bom_detail_id) {
                    $target = $c;
                    break;
                }
            }
        } elseif (!empty($no_uniq_id)) {
            foreach ($costings as $c) {
                if ($c->no_uniq_id === $no_uniq_id) {
                    $target = $c;
                    break;
                }
            }
        }

        if (!$target) {
            echo json_encode(['status' => 0, 'pesan' => 'Data tidak ditemukan']);
            return;
        }

        echo json_encode(['status' => 1, 'data' => $target]);
    }
}
