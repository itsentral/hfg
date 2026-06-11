<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pengajuan_mutasi extends Admin_Controller
{
    protected $viewPermission   = 'Pengajuan_mutasi.View';
    protected $addPermission    = 'Pengajuan_mutasi.Add';
    protected $managePermission = 'Pengajuan_mutasi.Manage';
    protected $deletePermission = 'Pengajuan_mutasi.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pengajuan_mutasi/pengajuan_mutasi_model');
        $this->template->title('Pengajuan Mutasi');
        $this->template->page_icon('fa fa-exchange-alt');

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->username = $this->auth->nama();
        $this->datetime = date('Y-m-d H:i:s');
    }

    // ---------------------------------------------------------------
    // INDEX
    // ---------------------------------------------------------------

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Pengajuan Mutasi');
        $this->template->render('index');
    }

    // ---------------------------------------------------------------
    // RENDER PARTIAL TABLE PER TAB (AJAX)
    // ---------------------------------------------------------------

    public function render_open()
    {
        $this->auth->restrict($this->viewPermission);
        $data['list'] = $this->pengajuan_mutasi_model->get_list([0, 1, 6]);
        $this->template->render('table/open_mutation', $data);
    }

    public function render_close()
    {
        $this->auth->restrict($this->viewPermission);
        $data['list'] = $this->pengajuan_mutasi_model->get_list([2, 4]);
        $this->template->render('table/close_mutation', $data);
    }

    public function render_cancel()
    {
        $this->auth->restrict($this->viewPermission);
        $data['list'] = $this->pengajuan_mutasi_model->get_list([3, 5]);
        $this->template->render('table/cancel_mutation', $data);
    }

    // ---------------------------------------------------------------
    // FORM (ADD / EDIT / VIEW)
    // ---------------------------------------------------------------

    public function form($mode = 'add', $id = null)
    {
        if ($mode === 'add') {
            $this->auth->restrict($this->addPermission);
        } else {
            $this->auth->restrict($this->viewPermission);
        }

        $data['mode']       = $mode;
        $data['id']         = $id;
        $data['warehouses'] = $this->pengajuan_mutasi_model->get_all_warehouse();
        $data['mutation']  = null;

        if ($id && in_array($mode, ['edit', 'view'])) {
            $mutation = $this->pengajuan_mutasi_model->get_detail($id);

            if (!$mutation) {
                $this->session->set_flashdata('error', 'Data mutasi tidak ditemukan.');
                redirect(site_url('pengajuan_mutasi'));
            }

            if ($mode === 'edit' && !in_array($mutation['status'], [0, 6])) {
                $this->session->set_flashdata('error', 'Data ini tidak dapat diedit karena status tidak valid.');
                redirect(site_url('pengajuan_mutasi'));
            }

            $data['mutation'] = $mutation;
        }

        $this->template->title(ucfirst($mode) . ' Pengajuan Mutasi');
        $this->template->render('form', $data);
    }

    // ---------------------------------------------------------------
    // AJAX — GET MATERIAL BY GUDANG
    // ---------------------------------------------------------------

    public function get_material()
    {
        $id_gudang = $this->input->get('id_gudang');

        if (!$id_gudang) {
            return $this->_json(['status' => 0, 'message' => 'id_gudang wajib diisi']);
        }

        $materials = $this->pengajuan_mutasi_model->get_material_by_gudang($id_gudang);

        return $this->_json(['status' => 1, 'data' => $materials]);
    }

    // ---------------------------------------------------------------
    // AJAX — GET COIL BY WAREHOUSE STOCK
    // ---------------------------------------------------------------

    public function get_coil()
    {
        $code_lv4  = $this->input->get('code_lv4');

        if (!$code_lv4) {
            return $this->_json(['status' => 0, 'message' => 'code_lv4 wajib diisi']);
        }

        $coils = $this->pengajuan_mutasi_model->get_coil_by_material($code_lv4);

        return $this->_json(['status' => 1, 'data' => $coils]);
    }

    // ---------------------------------------------------------------
    // SAVE (ADD)
    // ---------------------------------------------------------------

    public function save()
    {
        $this->auth->restrict($this->addPermission);

        $post = $this->input->post();

        // Validasi no berita acara
        if (empty($post['no_berita_acara'])) {
            return $this->_json(['status' => 0, 'message' => 'No. Berita Acara wajib diisi.']);
        }

        // Validasi gudang
        if (empty($post['id_gudang_from']) || empty($post['id_gudang_to'])) {
            return $this->_json(['status' => 0, 'message' => 'Gudang asal dan tujuan wajib dipilih.']);
        }

        if ($post['id_gudang_from'] == $post['id_gudang_to']) {
            return $this->_json(['status' => 0, 'message' => 'Gudang asal dan tujuan tidak boleh sama.']);
        }

        // Validasi detail (dikirim sebagai JSON string dalam form field)
        $details_raw = json_decode($post['details_json'] ?? '', true);
        if (empty($details_raw)) {
            return $this->_json(['status' => 0, 'message' => 'Minimal satu material harus dipilih.']);
        }

        // Ambil info gudang
        $gudang_from = $this->_get_gudang($post['id_gudang_from']);
        $gudang_to   = $this->_get_gudang($post['id_gudang_to']);

        if (!$gudang_from || !$gudang_to) {
            return $this->_json(['status' => 0, 'message' => 'Data gudang tidak valid.']);
        }

        // Handle upload file (optional)
        $file_name_original = null;
        $file_name_hash     = null;

        if (!empty($_FILES['berita_acara_file']['name'])) {
            $upload_result = $this->_upload_berita_acara();
            if ($upload_result['status'] === false) {
                return $this->_json(['status' => 0, 'message' => $upload_result['message']]);
            }
            $file_name_original = $upload_result['original_name'];
            $file_name_hash     = $upload_result['hash_name'];
        }

        $mutation_number = $this->pengajuan_mutasi_model->generate_mutation_number();

        $header = [
            'mutation_number'    => $mutation_number,
            'mutation_date'      => date('Y-m-d'),
            'no_berita_acara'    => $post['no_berita_acara'],
            'file_name_original' => $file_name_original,
            'file_name_hash'     => $file_name_hash,
            'id_gudang_from'     => $post['id_gudang_from'],
            'kd_gudang_from'     => $gudang_from['kd_gudang'],
            'nm_gudang_from'     => $gudang_from['nm_gudang'],
            'id_gudang_to'       => $post['id_gudang_to'],
            'kd_gudang_to'       => $gudang_to['kd_gudang'],
            'nm_gudang_to'       => $gudang_to['nm_gudang'],
            'description'        => $post['description'],
            'status'             => 0,
            'create_by'          => $this->username,
            'create_date'        => $this->datetime,
        ];

        $details = $this->_parse_details($details_raw);
        $result  = $this->pengajuan_mutasi_model->save_mutation($header, $details);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Data berhasil disimpan.', 'id' => $result]);
        }

        // Rollback file jika DB gagal
        if ($file_name_hash) {
            @unlink(FCPATH . 'uploads/berita_acara_mutasi/' . $file_name_hash);
        }

        return $this->_json(['status' => 0, 'message' => 'Gagal menyimpan data.']);
    }

    // ---------------------------------------------------------------
    // UPDATE (EDIT)
    // ---------------------------------------------------------------

    public function update($id)
    {
        $this->auth->restrict($this->managePermission);

        $post = $this->input->post();

        $mutation = $this->pengajuan_mutasi_model->get_detail($id);
        if (!$mutation || !in_array($mutation['status'], [0, 6])) {
            return $this->_json([
                'status' => 0,
                'message' => 'Data tidak dapat diubah.'
            ]);
        }

        if (empty($post['no_berita_acara'])) {
            return $this->_json(['status' => 0, 'message' => 'No. Berita Acara wajib diisi.']);
        }

        if (empty($post['id_gudang_from']) || empty($post['id_gudang_to'])) {
            return $this->_json(['status' => 0, 'message' => 'Gudang asal dan tujuan wajib dipilih.']);
        }

        if ($post['id_gudang_from'] == $post['id_gudang_to']) {
            return $this->_json(['status' => 0, 'message' => 'Gudang asal dan tujuan tidak boleh sama.']);
        }

        $details_raw = json_decode($post['details_json'] ?? '', true);
        if (empty($details_raw)) {
            return $this->_json(['status' => 0, 'message' => 'Minimal satu material harus dipilih.']);
        }

        $gudang_from = $this->_get_gudang($post['id_gudang_from']);
        $gudang_to   = $this->_get_gudang($post['id_gudang_to']);

        // Handle upload file baru (optional — jika tidak upload, pakai file lama)
        $file_name_original = $mutation['file_name_original'];
        $file_name_hash     = $mutation['file_name_hash'];
        $old_hash           = $mutation['file_name_hash'];

        if (!empty($_FILES['berita_acara_file']['name'])) {
            $upload_result = $this->_upload_berita_acara();
            if ($upload_result['status'] === false) {
                return $this->_json(['status' => 0, 'message' => $upload_result['message']]);
            }
            $file_name_original = $upload_result['original_name'];
            $file_name_hash     = $upload_result['hash_name'];

            // Hapus file lama
            if ($old_hash) {
                @unlink(FCPATH . 'uploads/berita_acara_mutasi/' . $old_hash);
            }
        }

        $header = [
            'no_berita_acara'    => $post['no_berita_acara'],
            'file_name_original' => $file_name_original,
            'file_name_hash'     => $file_name_hash,
            'id_gudang_from'     => $post['id_gudang_from'],
            'kd_gudang_from'     => $gudang_from['kd_gudang'],
            'nm_gudang_from'     => $gudang_from['nm_gudang'],
            'id_gudang_to'       => $post['id_gudang_to'],
            'kd_gudang_to'       => $gudang_to['kd_gudang'],
            'nm_gudang_to'       => $gudang_to['nm_gudang'],
            'description'        => $post['description'],
            'update_by'          => $this->username,
            'update_date'        => $this->datetime,
        ];

        $details = $this->_parse_details($details_raw);
        $result  = $this->pengajuan_mutasi_model->update_mutation($id, $header, $details);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Data berhasil diperbarui.']);
        }

        return $this->_json(['status' => 0, 'message' => 'Gagal memperbarui data.']);
    }

    // ---------------------------------------------------------------
    // AJUKAN (status 0 → 1)
    // ---------------------------------------------------------------

    public function submit($id)
    {
        $this->auth->restrict($this->managePermission);

        $result = $this->pengajuan_mutasi_model->submit_mutation($id, $this->username);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Mutasi berhasil diajukan.']);
        }

        return $this->_json(['status' => 0, 'message' => 'Gagal mengajukan mutasi.']);
    }

    // ---------------------------------------------------------------
    // CANCEL (status 0 → 5)
    // ---------------------------------------------------------------

    public function cancel($id)
    {
        $this->auth->restrict($this->managePermission);
        $reject_reason = $this->input->post('reject_reason');
        if (empty(trim($reject_reason))) {
            return $this->_json(['status' => 0, 'message' => 'Alasan pembatalan wajib diisi.']);
        }

        $result = $this->pengajuan_mutasi_model->cancel_mutation($id, $this->username, $reject_reason);

        if ($result) {
            return $this->_json(['status' => 1, 'message' => 'Mutasi berhasil dibatalkan.']);
        }

        return $this->_json(['status' => 0, 'message' => 'Gagal membatalkan mutasi atau status data sudah berubah.']);
    }

    // ---------------------------------------------------------------
    // HELPERS PRIVATE
    // ---------------------------------------------------------------

    private function _upload_berita_acara()
    {
        $upload_path = FCPATH . 'uploads/berita_acara_mutasi/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $file      = $_FILES['berita_acara_file'];
        $allowed   = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            return ['status' => false, 'message' => 'Format file tidak didukung. Gunakan PDF, JPG, atau PNG.'];
        }

        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            return ['status' => false, 'message' => 'Ukuran file maksimal 5MB.'];
        }

        $original_name = $file['name'];
        $hash_name     = md5(uniqid(rand(), true)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $upload_path . $hash_name)) {
            return ['status' => false, 'message' => 'Gagal menyimpan file.'];
        }

        return [
            'status'        => true,
            'original_name' => $original_name,
            'hash_name'     => $hash_name,
        ];
    }

    private function _get_gudang($id)
    {
        return $this->db->select('id, kd_gudang, nm_gudang')
            ->from('warehouse')
            ->where('id', $id)
            ->get()->row_array();
    }

    private function _parse_details($details_raw)
    {
        $details = [];

        foreach ($details_raw as $d) {
            $coils = [];

            if (!empty($d['coils'])) {
                foreach ($d['coils'] as $c) {
                    $coils[] = [
                        'id_warehouse_stock_coil' => $c['id'],
                        'no_coil'                 => $c['no_coil'],
                        'no_ipp'                  => $c['no_ipp'],
                        'no_po'                   => $c['no_po'],
                        'no_ros'                  => $c['no_ros'],
                        'gross_weight'            => $c['gross_weight'],
                        'net_weight'              => $c['net_weight'],
                        'length'                  => $c['length'],
                        'harga_beli'              => $c['harga_beli'],
                        'kode_internal'           => $c['kode_internal'],
                    ];
                }
            }


            // Disesuaikan agar hanya menyimpan code_lv4 demi efisiensi tabel detail
            $details[] = [
                'id_warehouse_stock' => $d['id_warehouse_stock'],
                'id_material'        => $d['id_material'],
                'nm_material'        => $d['nm_material'],
                'trade_name'         => $d['trade_name'],
                'code_lv4'           => $d['code_lv4'],
                'id_unit'            => $d['id_unit'],
                'qty'                => !empty($coils) ? 0 : ($d['qty'] ?? 0),
                'harga_beli'         => $d['harga_beli'] ?? 0,
                'coils'              => $coils,
            ];
        }

        return $details;
    }

    private function _json($data)
    {
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
