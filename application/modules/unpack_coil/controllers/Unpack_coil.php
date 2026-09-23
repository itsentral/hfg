<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Unpack_coil extends Admin_Controller
{
    protected $viewPermission   = 'Unpack_Coil.View';
    protected $addPermission    = 'Unpack_Coil.Add';
    protected $managePermission = 'Unpack_Coil.Manage';

    protected $id_user;
    protected $username;
    protected $datetime;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('unpack_coil/Unpack_coil_model');
        $this->template->title('Unpack Coil');
        $this->template->page_icon('fa fa-box-open');

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
        $this->template->render('index');
    }

    public function data_side()
    {
        $this->auth->restrict($this->viewPermission);

        $search_param = $this->input->get_post('search', TRUE);
        $search = (is_array($search_param) && isset($search_param['value'])) ? $search_param['value'] : '';
        $start  = (int) $this->input->get_post('start', TRUE);
        $length = (int) $this->input->get_post('length', TRUE);
        $draw   = (int) $this->input->get_post('draw', TRUE);

        $order_col = isset($_REQUEST['order'][0]['column']) ? (int) $_REQUEST['order'][0]['column'] : 1;
        $order_dir = isset($_REQUEST['order'][0]['dir']) ? $_REQUEST['order'][0]['dir'] : 'desc';
        $order_dir = in_array(strtolower($order_dir), array('asc', 'desc')) ? strtolower($order_dir) : 'desc';

        $col_map = array(
            1 => 'h.unpack_no',
            2 => 'h.pack_code',
            3 => 'material_count',
            4 => 'roll_count',
            5 => 'h.net_weight_actual',
            6 => 'h.tgl_unpack',
            7 => 'h.status',
        );
        $order_by = isset($col_map[$order_col]) ? $col_map[$order_col] : 'h.created_at';

        $rows      = $this->Unpack_coil_model->get_unpack_list($search, $start, $length, $order_by, $order_dir);
        $totalData = $this->Unpack_coil_model->count_unpack_filtered($search);

        $data = array();
        $no   = $start + 1;

        foreach ($rows as $row) {
            switch ($row['status']) {
                case 'Lock':
                    $badge = 'bg-success';
                    break;
                case 'Draft':
                    $badge = 'bg-warning text-dark';
                    break;
                case 'Cancelled':
                    $badge = 'bg-danger';
                    break;
                default:
                    $badge = 'bg-secondary';
                    break;
            }
            $status_html = "<span class='badge rounded-pill " . $badge . "'>" . $row['status'] . "</span>";

            $dropdown_items = array();
            $dropdown_items[] = '<a class="dropdown-item" href="' . site_url('unpack_coil/view/' . rawurlencode($row['unpack_no'])) . '"><i class="fa fa-eye me-2 text-info"></i> View</a>';

            if (!in_array($row['status'], array('Cancelled', 'Lock')) && has_permission($this->addPermission)) {
                $dropdown_items[] = '<a class="dropdown-item" href="' . site_url('unpack_coil/edit/' . rawurlencode($row['unpack_no'])) . '"><i class="fa fa-edit me-2 text-warning"></i> Edit</a>';
            }
            if (!in_array($row['status'], array('Cancelled', 'Lock')) && has_permission($this->managePermission)) {
                $dropdown_items[] = '<div class="dropdown-divider"></div>';
                $dropdown_items[] = '<a class="dropdown-item btn-lock-unpack" href="javascript:void(0)" data-unpack="' . htmlspecialchars($row['unpack_no']) . '"><i class="fa fa-lock me-2 text-danger"></i> Lock</a>';
            }

            $aksi = '<div class="dropdown text-center">'
                . '<button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false">'
                . '<i class="fa fa-ellipsis-v"></i></button>'
                . '<ul class="dropdown-menu dropdown-menu-end">'
                . '<li>' . implode('</li><li>', $dropdown_items) . '</li>'
                . '</ul></div>';

            $data[] = array(
                "<div class='text-center'>" . $no . "</div>",
                htmlspecialchars($row['unpack_no']),
                "<span class='badge bg-primary'>" . htmlspecialchars($row['pack_code']) . "</span>",
                "<div class='text-center'>" . (int) $row['material_count'] . "</div>",
                "<div class='text-center'>" . (int) $row['roll_count'] . "</div>",
                "<div class='text-end'>" . number_format((float) $row['net_weight_actual'], 2) . "</div>",
                "<div class='text-center'>" . date('d/m/Y', strtotime($row['tgl_unpack'])) . "</div>",
                "<div class='text-center'>" . $status_html . "</div>",
                $aksi,
            );
            $no++;
        }

        echo json_encode(array(
            'draw'            => $draw,
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ));
    }

    // ---------------------------------------------------------------
    // ADD / EDIT (form)
    // ---------------------------------------------------------------

    public function add()
    {
        $this->auth->restrict($this->addPermission);
        $data['mode']      = 'add';
        $data['unpack_no'] = '';
        $data['existing']  = null;
        $this->template->render('form_add', $data);
    }

    public function edit($unpack_no = null)
    {
        $this->auth->restrict($this->addPermission);

        if (!$unpack_no) {
            $this->session->set_flashdata('error', 'Nomor unpack tidak valid.');
            redirect('unpack_coil');
        }

        $header = $this->Unpack_coil_model->get_header($unpack_no);
        if (!$header) {
            $this->session->set_flashdata('error', 'Report unpack tidak ditemukan.');
            redirect('unpack_coil');
        }

        $materials = $this->Unpack_coil_model->get_materials_by_unpack($unpack_no);
        foreach ($materials as &$m) {
            $m['babies'] = $this->Unpack_coil_model->get_babies_by_material($m['id']);
        }
        unset($m);

        $data['mode']      = 'edit';
        $data['unpack_no'] = $unpack_no;
        $data['existing']  = array('header' => $header, 'materials' => $materials);
        $this->template->render('form_add', $data);
    }

    // ---------------------------------------------------------------
    // AJAX: select2 pack Material Confirmed
    // ---------------------------------------------------------------

    public function get_confirmed_packs()
    {
        $this->auth->restrict($this->viewPermission);

        $search = $this->input->get('q', TRUE);
        $rows = $this->Unpack_coil_model->get_confirmed_packs($search ?: '');

        $results = array();
        foreach ($rows as $r) {
            $results[] = array(
                'id'   => $r['id_pack'],
                'text' => $r['pack_code'] . ' (' . (int) $r['material_count'] . ' material, ' . (int) $r['coil_count'] . ' coil)',
                'data' => $r,
            );
        }

        return $this->_json(array('status' => 1, 'results' => $results));
    }

    // ---------------------------------------------------------------
    // AJAX: material dalam pack (level 2)
    // ---------------------------------------------------------------

    public function get_pack_materials($id_pack = null)
    {
        $this->auth->restrict($this->viewPermission);
        if (!$id_pack) {
            return $this->_json(array('status' => 0, 'message' => 'ID pack tidak valid.'));
        }
        $materials = $this->Unpack_coil_model->get_materials_in_pack($id_pack);
        return $this->_json(array('status' => 1, 'data' => $materials));
    }

    public function get_material_baby($id_pack = null, $id_material = null, $id_gudang = null)
    {
        $this->auth->restrict($this->viewPermission);
        if (!$id_pack || !$id_material || !$id_gudang) {
            return $this->_json(array('status' => 0, 'message' => 'Parameter tidak lengkap.'));
        }

        $rows = $this->Unpack_coil_model->get_coils_by_material($id_pack, $id_material, $id_gudang);
        $babies = array();

        foreach ($rows as $r) {
            $qty = max(1, (int) $r['qty_roll']);

            if ($qty > 1) {
                // Baris DB masih agregat (belum diexplode per roll) -> pecah rata & generate kode turunan
                $nw_avg = $r['net_weight_pl'] / $qty;
                $gw_avg = $r['gross_weight_pl'] / $qty;
                for ($i = 1; $i <= $qty; $i++) {
                    $babies[] = array(
                        'no_coil'         => $r['no_coil'] . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'babycoil_code'   => $r['kode_internal'] . '-BC.' . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'net_weight_pl'   => round($nw_avg, 4),
                        'gross_weight_pl' => round($gw_avg, 4),
                    );
                }
            } else {
                // Sudah 1 baris = 1 roll fisik di DB -> tinggal diteruskan
                $babies[] = array(
                    'no_coil'         => $r['no_coil'],
                    'babycoil_code'   => $r['kode_internal'],
                    'net_weight_pl'   => $r['net_weight_pl'],
                    'gross_weight_pl' => $r['gross_weight_pl'],
                );
            }
        }

        return $this->_json(array('status' => 1, 'babies' => $babies));
    }

    // ---------------------------------------------------------------
    // AJAX: baby coil default untuk 1 coil induk (modal detail)
    // ---------------------------------------------------------------

    // public function get_material_baby($id_coil_induk = null)
    // {
    //     $this->auth->restrict($this->viewPermission);

    //     if (!$id_coil_induk) {
    //         return $this->_json(array('status' => 0, 'message' => 'ID coil tidak valid.'));
    //     }

    //     $induk = $this->Unpack_coil_model->get_coil_induk($id_coil_induk);
    //     if (!$induk) {
    //         return $this->_json(array('status' => 0, 'message' => 'Coil induk tidak ditemukan.'));
    //     }

    //     $babies = $this->Unpack_coil_model->get_existing_baby_coils($id_coil_induk);

    //     return $this->_json(array('status' => 1, 'induk' => $induk, 'babies' => $babies));
    // }

    // ---------------------------------------------------------------
    // SAVE (add) & UPDATE (edit) — logika sama
    // ---------------------------------------------------------------

    public function save()
    {
        $this->auth->restrict($this->addPermission);
        return $this->_process_save('add');
    }

    public function update()
    {
        $this->auth->restrict($this->addPermission);
        return $this->_process_save('edit');
    }

    private function _process_save($mode)
    {
        $unpack_no  = $this->input->post('unpack_no', TRUE);
        $id_pack    = (int) $this->input->post('id_pack', TRUE);
        $pack_code  = $this->input->post('pack_code', TRUE);
        $request_id = (int) $this->input->post('request_id', TRUE);
        $materials  = $this->input->post('materials'); // [{id_material, id_gudang, jumlah_coil, babies:[...]}]

        $pack_net_actual   = (float) $this->input->post('pack_net_actual', TRUE);
        $pack_gross_actual = (float) $this->input->post('pack_gross_actual', TRUE);
        $pack_net_pl       = (float) $this->input->post('pack_net_pl', TRUE);
        $pack_gross_pl     = (float) $this->input->post('pack_gross_pl', TRUE);
        $catatan_kulit     = $this->input->post('catatan_kulit', TRUE);
        $catatan_clamp     = $this->input->post('catatan_clamp_ring', TRUE);
        $catatan           = $this->input->post('catatan', TRUE);

        if (!$id_pack || empty($pack_code)) {
            return $this->_json(array('status' => 0, 'message' => 'Pack wajib dipilih.'));
        }
        if (empty($materials) || !is_array($materials)) {
            return $this->_json(array('status' => 0, 'message' => 'Minimal 1 material harus diisi.'));
        }

        if ($mode == 'edit') {
            if (empty($unpack_no)) {
                return $this->_json(array('status' => 0, 'message' => 'Nomor unpack tidak valid.'));
            }
            $existing = $this->Unpack_coil_model->get_header($unpack_no);
            if (!$existing) {
                return $this->_json(array('status' => 0, 'message' => 'Report unpack tidak ditemukan.'));
            }
        }

        $this->db->trans_begin();

        if ($mode == 'add') {
            $unpack_no = $this->Unpack_coil_model->generate_unpack_no();
            $this->Unpack_coil_model->insert_header(array(
                'unpack_no'           => $unpack_no,
                'tgl_unpack'          => $this->datetime,
                'id_pack'             => $id_pack,
                'pack_code'           => $pack_code,
                'request_id'          => $request_id ?: null,
                'catatan_kulit'       => $catatan_kulit,
                'catatan_clamp_ring'  => $catatan_clamp,
                'net_weight_actual'   => $pack_net_actual,
                'gross_weight_actual' => $pack_gross_actual,
                'net_weight_pl'       => $pack_net_pl,
                'gross_weight_pl'     => $pack_gross_pl,
                'catatan'             => $catatan,
                'status'              => 'Draft',
                'created_by'          => $this->id_user,
                'created_at'          => $this->datetime,
            ));
        } else {
            $this->Unpack_coil_model->update_header($unpack_no, array(
                'catatan_kulit'       => $catatan_kulit,
                'catatan_clamp_ring'  => $catatan_clamp,
                'net_weight_actual'   => $pack_net_actual,
                'gross_weight_actual' => $pack_gross_actual,
                'net_weight_pl'       => $pack_net_pl,
                'gross_weight_pl'     => $pack_gross_pl,
                'catatan'             => $catatan,
                'updated_by'          => $this->id_user,
            ));
            $this->Unpack_coil_model->restore_induk_by_unpack($unpack_no);
            $this->Unpack_coil_model->purge_details($unpack_no);
            $this->Unpack_coil_model->delete_baby_stock_coils_by_unpack($unpack_no);
        }

        foreach ($materials as $m) {
            $id_material = isset($m['id_material']) ? trim($m['id_material']) : '';
            $id_gudang   = isset($m['id_gudang']) ? $m['id_gudang'] : null;
            if ($id_material === '' || $id_gudang === null) {
                $this->db->trans_rollback();
                return $this->_json(array('status' => 0, 'message' => 'Data material tidak lengkap (id_material/id_gudang kosong).'));
            }

            $babies = isset($m['babies']) && is_array($m['babies']) ? $m['babies'] : array();
            $roll   = count($babies);
            if ($roll <= 0) {
                $this->db->trans_rollback();
                return $this->_json(array('status' => 0, 'message' => 'Detail baby coil untuk material ' . $id_material . ' belum diisi. Silakan klik "View Detail" sebelum Save.'));
            }

            // Baris fisik asli grup ini (masih in_transit, di pack ini)
            $real_coils = $this->Unpack_coil_model->get_coils_by_material($id_pack, $id_material, $id_gudang);
            if (empty($real_coils)) {
                $this->db->trans_rollback();
                return $this->_json(array('status' => 0, 'message' => 'Data coil untuk material ' . $id_material . ' tidak ditemukan saat proses simpan.'));
            }
            $rep = $real_coils[0]; // representatif: nama, gudang, panjang, dll

            // Total nilai grup asli (info untuk ledger summary). Tidak dipakai untuk
            // menghitung ulang harga; harga baby mewarisi harga induk apa adanya.
            $total_nilai_group = 0;
            foreach ($real_coils as $rc) {
                $nilai = (float) (isset($rc['total_nilai']) ? $rc['total_nilai'] : 0);
                if ($nilai <= 0) {
                    $nilai = (float) (isset($rc['harga_beli']) ? $rc['harga_beli'] : 0) * (float) $rc['net_weight_pl'];
                }
                $total_nilai_group += $nilai;
            }

            // Agregat dari input babies (jumlah_coil FINAL, boleh beda dari count($real_coils))
            $sum_net_actual = 0;
            $sum_gross_actual = 0;
            $sum_net_pl = 0;
            $sum_gross_pl = 0;
            foreach ($babies as $b) {
                $sum_net_actual   += (float) (isset($b['net_weight_actual']) ? $b['net_weight_actual'] : 0);
                $sum_gross_actual += (float) (isset($b['gross_weight_actual']) ? $b['gross_weight_actual'] : 0);
                $sum_net_pl       += (float) (isset($b['net_weight_pl']) ? $b['net_weight_pl'] : 0);
                $sum_gross_pl     += (float) (isset($b['gross_weight_pl']) ? $b['gross_weight_pl'] : 0);
            }

            $id_unpack_material = $this->Unpack_coil_model->insert_material(array(
                'unpack_no'           => $unpack_no,
                'id_material'         => $id_material,
                'id_gudang'           => $id_gudang,
                'nm_material'         => $rep['nm_material'],
                'kode_internal'       => $rep['kode_internal'],
                'kd_gudang'           => $rep['kd_gudang'],
                'no_coil'             => $rep['no_coil'],
                'jumlah_coil_roll'    => $roll,
                'net_weight_actual'   => $sum_net_actual,
                'gross_weight_actual' => $sum_gross_actual,
                'net_weight_pl'       => $sum_net_pl,
                'gross_weight_pl'     => $sum_gross_pl,
                'is_delete'           => 0,
            ));

            // Harga baby coil = harga_beli induk APA ADANYA (tidak dihitung ulang).
            // Unpack hanya memecah coil; nilai/harga per kg tidak berubah.
            $cb_per_kg = (float) (isset($rep['harga_beli']) ? $rep['harga_beli'] : 0);

            foreach ($babies as $idx => $b) {
                $babycoil_code = isset($b['babycoil_code']) ? $b['babycoil_code'] : ($id_material . '-BC.' . ($idx + 1));
                $no_coil_baby  = isset($b['no_coil']) ? $b['no_coil'] : $rep['no_coil'];
                $net_actual    = (float) (isset($b['net_weight_actual']) ? $b['net_weight_actual'] : 0);
                $gross_actual  = (float) (isset($b['gross_weight_actual']) ? $b['gross_weight_actual'] : 0);
                $net_pl        = (float) (isset($b['net_weight_pl']) ? $b['net_weight_pl'] : 0);
                $gross_pl      = (float) (isset($b['gross_weight_pl']) ? $b['gross_weight_pl'] : 0);
                $nilai_baby    = $net_actual * $cb_per_kg;

                // parent_coil_id cuma valid kalau grup = persis 1 baris asli & jumlahnya tidak di-adjust
                $induk_ref = $rep;
                $induk_ref['id'] = (count($real_coils) === 1 && $roll === (int) $real_coils[0]['qty_roll'])
                    ? $rep['id']
                    : null;

                $id_coil_baru = $this->Unpack_coil_model->insert_baby_stock_coil(
                    $induk_ref,
                    $babycoil_code,
                    $no_coil_baby,
                    $net_actual,
                    $gross_actual,
                    $cb_per_kg,
                    $unpack_no
                );

                $this->Unpack_coil_model->insert_baby(array(
                    'id_unpack_material'  => $id_unpack_material,
                    'babycoil_code'       => $babycoil_code,
                    'no_coil'             => $no_coil_baby,
                    'net_weight_actual'   => $net_actual,
                    'gross_weight_actual' => $gross_actual,
                    'net_weight_pl'       => $net_pl,
                    'gross_weight_pl'     => $gross_pl,
                    'costbook'            => $cb_per_kg,
                    'total_nilai'         => $nilai_baby,
                    'id_coil_baru'        => $id_coil_baru,
                    'is_delete'           => 0,
                ));

                $this->Unpack_coil_model->insert_transaction_detail(array(
                    'kode_trans'     => $unpack_no,
                    'id_material'    => $id_material,
                    'nm_material'    => $rep['nm_material'],
                    'id_gudang'      => $id_gudang,
                    'kd_gudang'      => $rep['kd_gudang'],
                    'no_coil'        => $no_coil_baby,
                    'parent_no_coil' => $rep['no_coil'],
                    'kode_internal'  => $babycoil_code,
                    'gross_weight'   => $gross_actual,
                    'net_weight'     => $net_actual,
                    'length'         => isset($rep['length']) ? $rep['length'] : 0,
                    'price_per_coil' => $cb_per_kg,
                    'cost_book'      => $cb_per_kg,
                    'status_qc'      => 'IN',
                    'to_status'      => 'unpacked',
                    'created_at'     => $this->datetime,
                ));
            }

            // Nonaktifkan SEMUA baris fisik asli grup ini
            foreach ($real_coils as $rc) {
                $this->Unpack_coil_model->mark_induk_unpacked($rc['id'], $unpack_no);
            }

            // ================================================================
            // TIDAK ADA perubahan nilai di warehouse_stock (agregat).
            // Unpack hanya memecah coil induk menjadi baby coil; net total tetap
            // dan harga per kg diwariskan apa adanya dari induk. Karena itu:
            // - warehouse_stock TIDAK di-update (qty_stock/total_nilai/harga_beli tetap)
            // - qty & saldo di ledger diambil apa adanya (tidak digerakkan): awal = akhir
            // ================================================================
            $harga_induk = $cb_per_kg; // harga_beli induk (rep)

            $stock = $this->Unpack_coil_model->get_stock($id_material, $rep['kd_gudang']);
            $qty_now   = $stock ? (float) $stock['qty_stock'] : 0;
            $saldo_now = $stock ? (float) $stock['total_nilai'] : 0;

            $this->Unpack_coil_model->insert_warehouse_history(array(
                'id_material'     => $id_material,
                'nm_material'     => $rep['nm_material'],
                'id_gudang'       => $id_gudang,
                'kd_gudang'       => $rep['kd_gudang'],
                'id_gudang_dari'  => $id_gudang,
                'kd_gudang_dari'  => $rep['kd_gudang'],
                'id_gudang_ke'    => $id_gudang,
                'kd_gudang_ke'    => $rep['kd_gudang'],
                'qty_stock_awal'  => $qty_now,
                'qty_stock_akhir' => $qty_now, // tidak berubah
                'no_ipp'          => $unpack_no,
                'jumlah_mat'      => $sum_net_actual,
                'ket'             => 'Unpack material ' . $id_material . ' menjadi ' . $roll . ' baby coil (' . $unpack_no . ')',
                'no_coil'         => $rep['no_coil'],
                'harga_beli'      => $harga_induk,
                'total_harga'     => $sum_net_actual * $harga_induk, // info nilai coil yang dipecah
                'saldo_awal'      => $saldo_now,
                'saldo_akhir'     => $saldo_now, // tidak berubah
                'harga_baru'      => $harga_induk,
                'harga_lama'      => $harga_induk,
                'update_by'       => $this->id_user,
                'update_date'     => $this->datetime,
            ));

            $this->Unpack_coil_model->insert_transaction_summary(array(
                'kode_trans'    => $unpack_no,
                'id_material'   => $id_material,
                'nm_material'   => $rep['nm_material'],
                'id_gudang'     => $id_gudang,
                'kd_gudang'     => $rep['kd_gudang'],
                'tanggal'       => date('Y-m-d'),
                'jumlah_coil'   => $roll,
                'qty_awal'      => $qty_now,
                'qty_transaksi' => $sum_net_actual,
                'qty_akhir'     => $qty_now, // tidak berubah
                'costbook'      => $harga_induk,
                'total_harga'   => $total_nilai_group,
                'saldo_awal'    => $saldo_now,
                'saldo_akhir'   => $saldo_now, // tidak berubah
                'harga_lama'    => $harga_induk,
                'created_by'    => $this->id_user,
                'created_at'    => $this->datetime,
            ));
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->_json(array('status' => 0, 'message' => 'Gagal menyimpan report unpack. Silakan coba lagi.'));
        }

        $this->db->trans_commit();
        return $this->_json(array('status' => 1, 'message' => 'Report unpack berhasil disimpan.', 'unpack_no' => $unpack_no));
    }

    // ---------------------------------------------------------------
    // VIEW DETAIL
    // ---------------------------------------------------------------

    public function view($unpack_no = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$unpack_no) {
            $this->session->set_flashdata('error', 'Nomor unpack tidak valid.');
            redirect('unpack_coil');
        }

        $header = $this->Unpack_coil_model->get_header($unpack_no);
        if (!$header) {
            $this->session->set_flashdata('error', 'Report unpack tidak ditemukan.');
            redirect('unpack_coil');
        }

        $materials = $this->Unpack_coil_model->get_materials_by_unpack($unpack_no);
        foreach ($materials as &$m) {
            $m['babies'] = $this->Unpack_coil_model->get_babies_by_material($m['id']);
        }
        unset($m);

        $data['header']    = $header;
        $data['materials'] = $materials;
        $this->template->render('view', $data);
    }

    // ---------------------------------------------------------------
    // DELETE (soft)
    // ---------------------------------------------------------------

    public function delete()
    {
        $this->auth->restrict($this->managePermission);

        $unpack_no = $this->input->post('unpack_no', TRUE);
        if (empty($unpack_no)) {
            return $this->_json(array('status' => 0, 'message' => 'Nomor unpack tidak valid.'));
        }

        $header = $this->Unpack_coil_model->get_header($unpack_no);
        if (!$header) {
            return $this->_json(array('status' => 0, 'message' => 'Report unpack tidak ditemukan.'));
        }

        $this->Unpack_coil_model->delete_req($unpack_no, $this->id_user);

        return $this->_json(array('status' => 1, 'message' => 'Report unpack berhasil dihapus.'));
    }


    public function lock()
    {
        $this->auth->restrict($this->managePermission);

        $unpack_no = $this->input->post('unpack_no', TRUE);
        if (empty($unpack_no)) {
            return $this->_json(array('status' => 0, 'message' => 'Nomor unpack tidak valid.'));
        }

        $header = $this->Unpack_coil_model->get_header($unpack_no);
        if (!$header) {
            return $this->_json(array('status' => 0, 'message' => 'Report unpack tidak ditemukan.'));
        }

        if ($header['status'] == 'Lock') {
            return $this->_json(array('status' => 0, 'message' => 'Report unpack ini sudah dalam status Lock.'));
        }

        $this->Unpack_coil_model->lock_req($unpack_no, $this->id_user);

        return $this->_json(array('status' => 1, 'message' => 'Report unpack berhasil di-lock.'));
    }


    // ---------------------------------------------------------------
    // HELPER: JSON output
    // ---------------------------------------------------------------

    private function _json($data)
    {
        if (ob_get_length()) ob_clean();
        return $this->output->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
