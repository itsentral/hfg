<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mata_uang extends Admin_Controller
{
    // Permission
    protected $viewPermission   = 'Mata_uang.View';
    protected $addPermission    = 'Mata_uang.Add';
    protected $managePermission = 'Mata_uang.Manage';
    protected $deletePermission = 'Mata_uang.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('mata_uang/Mata_uang_model', 'mata_uang_model');
        $this->template->title('Master Mata Uang');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data = $this->mata_uang_model->get_all_mata_uang();

        $this->template->set('results', $data);
        $this->template->title('Master Mata Uang');
        $this->template->render('index');
    }

    /**
     * API untuk mengambil daftar negara dengan status disabled
     * jika sudah terdaftar di database
     */
    public function get_countries_api()
    {
        $current_id = $this->input->get('current_id');
        $data = $this->mata_uang_model->get_countries_with_status($current_id);

        echo json_encode([
            'status' => 1,
            'data'   => $data
        ]);
    }

    /**
     * Tampilkan Modal Form Tambah / Edit
     */
    public function form($id = null)
    {
        if (!empty($id)) {
            $this->auth->restrict($this->managePermission);
            $header = $this->mata_uang_model->get_by_id($id);
            if (!$header) {
                echo "<div class='alert alert-danger'>Data tidak ditemukan!</div>";
                return;
            }
        } else {
            $this->auth->restrict($this->addPermission);
            $header = null;
        }

        $data = [
            'header'     => $header,
            'current_id' => $id,
        ];

        $this->load->view('mata_uang/form', $data);
    }

    /**
     * Simpan data (Tambah / Edit) dengan validasi negara duplikat
     */
    public function save()
    {
        $post = $this->input->post();
        if (!$post) {
            echo json_encode(['status' => 0, 'pesan' => 'Permintaan tidak valid!']);
            return;
        }

        $id        = !empty($post['id_kurs']) ? trim($post['id_kurs']) : null;
        $negara    = trim($post['negara'] ?? '');
        $kode      = strtoupper(trim($post['kode'] ?? ''));
        $mata_uang = trim($post['mata_uang'] ?? '');
        $kurs_raw  = $post['kurs'] ?? 0;
        $kurs      = (float) str_replace(',', '', (string)$kurs_raw);
        $activation= !empty($post['activation']) ? $post['activation'] : 'active';

        // 1. Validasi Input Required
        if (empty($negara)) {
            echo json_encode(['status' => 0, 'pesan' => 'Negara harus dipilih!']);
            return;
        }
        if (empty($kode)) {
            echo json_encode(['status' => 0, 'pesan' => 'Kode mata uang harus diisi!']);
            return;
        }
        if (empty($mata_uang)) {
            echo json_encode(['status' => 0, 'pesan' => 'Nama mata uang harus diisi!']);
            return;
        }

        // 2. Validasi apakah negara sudah terdaftar di mata uang
        $is_duplicate = $this->mata_uang_model->check_country_exists($negara, $id);
        if ($is_duplicate) {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Negara "' . htmlspecialchars($negara) . '" sudah terdaftar dalam data mata uang! Silakan pilih negara lain.'
            ]);
            return;
        }

        $session  = $this->session->userdata('app_session');
        $user_id  = $session['id_user'] ?? 1;

        $payload = [
            'kode'       => $kode,
            'negara'     => $negara,
            'mata_uang'  => $mata_uang,
            'kurs'       => $kurs,
            'activation' => $activation
        ];

        $field_hist = empty($id) ? 'Add' : 'Edit';
        $save_id = $this->mata_uang_model->save_data($payload, $id, $user_id);

        if ($save_id) {
            write_log('Master Mata Uang', $field_hist, $field_hist . ' mata uang: ' . $kode . ' (' . $negara . ')', $payload, null, 1);
            echo json_encode([
                'status' => 1,
                'pesan'  => 'Data mata uang berhasil disimpan!'
            ]);
        } else {
            write_log('Master Mata Uang', $field_hist, 'Gagal ' . strtolower($field_hist) . ' mata uang: ' . $kode, $payload, null, 0);
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Gagal menyimpan data mata uang!'
            ]);
        }
    }

    /**
     * Soft Delete
     */
    public function hapus()
    {
        $this->auth->restrict($this->deletePermission);

        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid!']);
            return;
        }

        $session = $this->session->userdata('app_session');
        $user_id = $session['id_user'] ?? 1;

        $record = $this->mata_uang_model->get_by_id($id);
        $deleted = $this->mata_uang_model->delete_data($id, $user_id);

        if ($deleted) {
            write_log('Master Mata Uang', 'Delete', 'Hapus mata uang ID: ' . $id . ' (' . ($record->kode ?? '') . ')', ['id' => $id], null, 1);
            echo json_encode([
                'status' => 1,
                'pesan'  => 'Data mata uang berhasil dihapus!'
            ]);
        } else {
            write_log('Master Mata Uang', 'Delete', 'Gagal hapus mata uang ID: ' . $id, ['id' => $id], null, 0);
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Gagal menghapus data!'
            ]);
        }
    }

    /**
     * Toggle Status Aktif / Tidak Aktif
     */
    public function toggle_status()
    {
        $this->auth->restrict($this->managePermission);

        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid!']);
            return;
        }

        $session = $this->session->userdata('app_session');
        $user_id = $session['id_user'] ?? 1;

        $new_status = $this->mata_uang_model->toggle_activation($id, $user_id);

        if ($new_status) {
            echo json_encode([
                'status'     => 1,
                'pesan'      => 'Status berhasil diubah menjadi ' . ucfirst($new_status),
                'new_status' => $new_status
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Gagal mengubah status!'
            ]);
        }
    }
}
