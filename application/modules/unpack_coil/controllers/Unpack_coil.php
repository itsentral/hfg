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
        $order_dir = in_array(strtolower((string) $order_dir), array('asc', 'desc')) ? strtolower((string) $order_dir) : 'desc';

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
            $dropdown_items[] = '<a class="dropdown-item" href="' . site_url('unpack_coil/view/' . rawurlencode((string) $row['unpack_no'])) . '"><i class="fa fa-eye me-2 text-info"></i> View</a>';

            if (!in_array($row['status'], array('Cancelled', 'Lock')) && has_permission($this->addPermission)) {
                $dropdown_items[] = '<a class="dropdown-item" href="' . site_url('unpack_coil/edit/' . rawurlencode((string) $row['unpack_no'])) . '"><i class="fa fa-edit me-2 text-warning"></i> Edit</a>';
            }
            if (!in_array($row['status'], array('Cancelled', 'Lock')) && has_permission($this->managePermission)) {
                $dropdown_items[] = '<div class="dropdown-divider"></div>';
                $dropdown_items[] = '<a class="dropdown-item btn-lock-unpack" href="javascript:void(0)" data-unpack="' . htmlspecialchars((string) $row['unpack_no']) . '"><i class="fa fa-lock me-2 text-danger"></i> Lock</a>';
            }

            $aksi = '<div class="dropdown text-center">'
                . '<button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false">'
                . '<i class="fa fa-ellipsis-v"></i></button>'
                . '<ul class="dropdown-menu dropdown-menu-end">'
                . '<li>' . implode('</li><li>', $dropdown_items ?? []) . '</li>'
                . '</ul></div>';

            $data[] = array(
                "<div class='text-center'>" . $no . "</div>",
                htmlspecialchars((string) $row['unpack_no']),
                "<span class='badge bg-primary'>" . htmlspecialchars((string) $row['pack_code']) . "</span>",
                "<div class='text-center'>" . (int) $row['material_count'] . "</div>",
                "<div class='text-center'>" . (int) $row['roll_count'] . "</div>",
                "<div class='text-end'>" . number_format((float) $row['net_weight_actual'], 2) . "</div>",
                "<div class='text-center'>" . date('d/m/Y', strtotime((string) $row['tgl_unpack'])) . "</div>",
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
            $this->session->set_flashdata('error', 'Invalid unpack number.');
            redirect('unpack_coil');
        }

        $header = $this->Unpack_coil_model->get_header($unpack_no);
        if (!$header) {
            $this->session->set_flashdata('error', 'Unpack report not found.');
            redirect('unpack_coil');
        }

        if ($header['status'] === 'Lock') {
            $this->session->set_flashdata('error', 'The unpack report is Locked and cannot be edited.');
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
            return $this->_json(array('status' => 0, 'message' => 'Invalid pack ID.'));
        }
        $materials = $this->Unpack_coil_model->get_materials_in_pack($id_pack);
        return $this->_json(array('status' => 1, 'data' => $materials));
    }

    public function get_material_baby($id_pack = null, $id_material = null, $id_gudang = null)
    {
        $this->auth->restrict($this->viewPermission);
        if (!$id_pack || !$id_material || !$id_gudang) {
            return $this->_json(array('status' => 0, 'message' => 'Incomplete parameters.'));
        }

        $rows = $this->Unpack_coil_model->get_coils_by_material($id_pack, $id_material, $id_gudang);
        $babies = array();
        $total_baby_idx = 1;

        foreach ($rows as $r) {
            $qty = max(1, (int) $r['qty_roll']);
            $base_no_coil = trim(preg_replace('/-BC\.\d+$/i', '', $r['no_coil'] ?? ''));
            $base_kode_internal = trim(preg_replace('/-BC\.\d+$/i', '', $r['kode_internal'] ?? ''));

            $nw_avg = $r['net_weight_pl'] / $qty;
            $gw_avg = $r['gross_weight_pl'] / $qty;
            for ($i = 1; $i <= $qty; $i++) {
                $babies[] = array(
                    'no_coil'         => $base_no_coil . '-BC.' . $total_baby_idx,
                    'babycoil_code'   => $base_kode_internal . '-BC.' . $total_baby_idx,
                    'net_weight_pl'   => round($nw_avg, 4),
                    'gross_weight_pl' => round($gw_avg, 4),
                );
                $total_baby_idx++;
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
            return $this->_json(array('status' => 0, 'message' => 'A pack is required.'));
        }
        if (empty($materials) || !is_array($materials)) {
            return $this->_json(array('status' => 0, 'message' => 'At least 1 material must be filled in.'));
        }

        if ($mode == 'edit') {
            if (empty($unpack_no)) {
                return $this->_json(array('status' => 0, 'message' => 'Invalid unpack number.'));
            }
            $existing = $this->Unpack_coil_model->get_header($unpack_no);
            if (!$existing) {
                return $this->_json(array('status' => 0, 'message' => 'Unpack report not found.'));
            }
            if ($existing['status'] === 'Lock') {
                return $this->_json(array('status' => 0, 'message' => 'The unpack report is Locked and cannot be edited.'));
            }
        }

        $this->db->trans_begin();
        try {
        $this->load->helper('material_stock');
        material_stock_require_schema($this->db);
        $old_header = $mode === 'edit' ? $existing : [];
        $old_details = $mode === 'edit' ? $this->Unpack_coil_model->get_materials_by_unpack($unpack_no) : [];
        $old_coils = [];
        if ($mode === 'edit') {
            $old_coils = $this->db->query(
                'SELECT * FROM warehouse_stock_coil WHERE no_ipp = ? AND is_baby_coil = 1 FOR UPDATE', [$unpack_no]
            )->result_array();
            foreach ($old_coils as $old_coil) {
                $used = $this->db->get_where('production_report_materials', ['id_unpack_baby_coil' => $old_coil['id']])->row_array();
                if ($used || (int)$old_coil['status'] !== 1 || !in_array($old_coil['status_proses'], ['unpacked','in_transit'], true)) {
                    throw new RuntimeException('The baby coil has already been used/selected in a production report. Unpack cannot overwrite that transaction source.');
                }
            }
            foreach ($old_details as &$old_detail) {
                $old_detail['babies'] = $this->Unpack_coil_model->get_babies_by_material($old_detail['id']);
            }
            unset($old_detail);
            // Hanya ledger penanganan unpack ini, bukan mutasi scan atau pemakaian produksi.
            $this->db->where('kode_trans', $unpack_no)->delete('warehouse_stock_transaction_detail');
            $this->db->where('kode_trans', $unpack_no)->delete('warehouse_stock_transaction_summary');
            $this->db->where('no_ipp', $unpack_no)->delete('warehouse_history');
        }

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
            $id_material = isset($m['id_material']) ? trim((string) $m['id_material']) : '';
            $id_gudang   = isset($m['id_gudang']) ? $m['id_gudang'] : null;
            if ($id_material === '' || $id_gudang === null) {
                $this->db->trans_rollback();
                return $this->_json(array('status' => 0, 'message' => 'Material data is incomplete (id_material/id_gudang empty).'));
            }

            $babies = isset($m['babies']) && is_array($m['babies']) ? $m['babies'] : array();
            $roll   = count($babies ?? []);
            if ($roll <= 0) {
                $this->db->trans_rollback();
                return $this->_json(array('status' => 0, 'message' => 'Baby coil detail for material ' . $id_material . ' has not been filled in. Please click "View Detail" before Save.'));
            }

            // Baris fisik asli grup ini (masih in_transit, di pack ini)
            $real_coils = $this->Unpack_coil_model->get_coils_by_material($id_pack, $id_material, $id_gudang);
            if (empty($real_coils)) {
                $this->db->trans_rollback();
                return $this->_json(array('status' => 0, 'message' => 'Coil data for material ' . $id_material . ' was not found during the save process.'));
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

            $parent_packing = round(array_sum(array_column($real_coils, 'net_weight_pl')), 3);
            if (round($sum_net_pl, 3) !== $parent_packing) {
                throw new RuntimeException('The baby coil Packing List Total Net Weight must equal the parent coil packing list (' . $parent_packing . ' Kg). Berat aktual tetap dicatat terpisah.');
            }
            $warehouse = material_stock_warehouse($this->db, 'PRT');
            if ((string)$id_gudang !== (string)$warehouse['id']) throw new RuntimeException('The unpack warehouse does not match the PRT master.');
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
            $cb_per_kg = material_stock_costbook($this->db, $rep, true);

            $base_induk_coil = trim(preg_replace('/-BC\.\d+$/i', '', $rep['no_coil'] ?? ''));
            if (strpos((string) $base_induk_coil, ',') !== false) {
                $parts_coil = explode(',', (string) $base_induk_coil);
                $base_induk_coil = trim((string) $parts_coil[0]);
            }
            $base_induk_kode = trim(preg_replace('/-BC\.\d+$/i', '', $rep['kode_internal'] ?? ''));

            foreach ($babies as $idx => $b) {
                $seq = $idx + 1;
                $no_coil_baby = (!empty($b['no_coil']) && strpos((string) $b['no_coil'], ',') === false)
                    ? trim((string) $b['no_coil'])
                    : ($base_induk_coil . '-BC.' . $seq);

                $babycoil_code = (!empty($b['babycoil_code']) && strpos((string) $b['babycoil_code'], ',') === false)
                    ? trim((string) $b['babycoil_code'])
                    : ($base_induk_kode . '-BC.' . $seq);
                $net_actual    = (float) (isset($b['net_weight_actual']) ? $b['net_weight_actual'] : 0);
                $gross_actual  = (float) (isset($b['gross_weight_actual']) ? $b['gross_weight_actual'] : 0);
                $net_pl        = (float) (isset($b['net_weight_pl']) ? $b['net_weight_pl'] : 0);
                $gross_pl      = (float) (isset($b['gross_weight_pl']) ? $b['gross_weight_pl'] : 0);
                $nilai_baby    = round($net_pl * $cb_per_kg, 2);

                // parent_coil_id cuma valid kalau grup = persis 1 baris asli & jumlahnya tidak di-adjust
                $induk_ref = $rep;
                $induk_ref['id'] = (count($real_coils ?? []) === 1 && $roll === (int) $real_coils[0]['qty_roll'])
                    ? $rep['id']
                    : null;

                $kulit_baby      = (float) (isset($b['kulit']) ? $b['kulit'] : 0);
                $clamp_ring_baby = (float) (isset($b['clamp_ring']) ? $b['clamp_ring'] : 0);

                $id_coil_baru = $this->Unpack_coil_model->insert_baby_stock_coil(
                    $induk_ref,
                    $babycoil_code,
                    $no_coil_baby,
                    $net_actual,
                    $gross_actual,
                    $cb_per_kg,
                    $unpack_no,
                    $kulit_baby,
                    $clamp_ring_baby,
                    $net_pl
                );

                $baby_snapshot = $this->db->get_where('warehouse_stock_coil', ['id' => $id_coil_baru])->row_array();
                material_stock_coil_snapshot($this->db, $baby_snapshot, 'IN', $this->id_user, $this->datetime);
                $this->Unpack_coil_model->insert_baby(array(
                    'id_unpack_material'  => $id_unpack_material,
                    'babycoil_code'       => $babycoil_code,
                    'no_coil'             => $no_coil_baby,
                    'net_weight_actual'   => $net_actual,
                    'gross_weight_actual' => $gross_actual,
                    'net_weight_pl'       => $net_pl,
                    'gross_weight_pl'     => $gross_pl,
                    'kulit'               => $kulit_baby,
                    'clamp_ring'          => $clamp_ring_baby,
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

                // Kartu Pemakaian Material per coil (warehouse_coil_kartu_stok) TIDAK dicatat di Unpack.
                // Pencatatan (consume / WIP / hold / Scrap) dilakukan saat Production Report final;
                // baris IN pembuka dibuat otomatis di sana bila coil belum punya saldo.
            }

            // Nonaktifkan SEMUA baris fisik asli grup ini
            foreach ($real_coils as $rc) {
                $parent_snapshot = $this->db->get_where('warehouse_stock_coil', ['id' => $rc['id']])->row_array();
                material_stock_coil_snapshot($this->db, $parent_snapshot, 'OUT', $this->id_user, $this->datetime);
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
                'total_harga'     => 0, // Pemecahan coil tidak menambah nilai persediaan.
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
                'qty_transaksi' => $sum_net_pl,
                'qty_akhir'     => $qty_now, // tidak berubah
                'costbook'      => $harga_induk,
                'total_harga'   => 0,
                'saldo_awal'    => $saldo_now,
                'saldo_akhir'   => $saldo_now, // tidak berubah
                'harga_lama'    => $harga_induk,
                'created_by'    => $this->id_user,
                'created_at'    => $this->datetime,
            ));
            $summary_id = $this->db->insert_id();
            $this->db->where('kode_trans', $unpack_no)->where('id_material', $id_material)
                ->where('id_gudang', $id_gudang)->update('warehouse_stock_transaction_detail', ['id_summary' => $summary_id]);
            $this->db->where('no_ipp', $unpack_no)->where('id_material', $id_material)
                ->where('id_gudang', $id_gudang)->update('warehouse_history', ['id_summary' => $summary_id]);
            $book = material_stock_row($this->db, $id_material, $warehouse);
            if (!$book) throw new RuntimeException('The PRT book balance is not available yet. Reconciliation is required.');
            material_stock_snapshot($this->db, $book, $this->id_user, $this->datetime);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->_json(array('status' => 0, 'message' => 'Failed to save the unpack report. Please try again.'));
        }

        $this->db->trans_commit();
        $new_header = $this->Unpack_coil_model->get_header($unpack_no);
        $new_details = $this->Unpack_coil_model->get_materials_by_unpack($unpack_no);
        $new_coils = $this->db->get_where('warehouse_stock_coil', ['no_ipp' => $unpack_no, 'is_baby_coil' => 1])->result_array();
        write_log('Unpack Coil', $mode === 'edit' ? 'Edit' : 'Create', 'Simpan unpack ' . $unpack_no, [
            'old' => $old_header, 'new' => $new_header,
            'detail_changes' => [
                'Material Unpack' => ['old' => $old_details, 'new' => $new_details],
                'Baby Coil' => ['old' => $old_coils, 'new' => $new_coils],
            ],
        ], null, 1);
        return $this->_json(array('status' => 1, 'message' => 'Unpack report saved successfully.', 'unpack_no' => $unpack_no));
        } catch (RuntimeException $e) {
            $this->db->trans_rollback();
            return $this->_json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    // ---------------------------------------------------------------
    // VIEW DETAIL
    // ---------------------------------------------------------------

    public function view($unpack_no = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$unpack_no) {
            $this->session->set_flashdata('error', 'Invalid unpack number.');
            redirect('unpack_coil');
        }

        $header = $this->Unpack_coil_model->get_header($unpack_no);
        if (!$header) {
            $this->session->set_flashdata('error', 'Unpack report not found.');
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
            return $this->_json(array('status' => 0, 'message' => 'Invalid unpack number.'));
        }

        $header = $this->Unpack_coil_model->get_header($unpack_no);
        if (!$header) {
            return $this->_json(array('status' => 0, 'message' => 'Unpack report not found.'));
        }

        if ($header['status'] === 'Lock') {
            return $this->_json(array('status' => 0, 'message' => 'The unpack report is Locked and cannot be deleted.'));
        }

        $this->Unpack_coil_model->delete_req($unpack_no, $this->id_user);

        return $this->_json(array('status' => 1, 'message' => 'Unpack report deleted successfully.'));
    }


    public function lock()
    {
        $this->auth->restrict($this->managePermission);

        $unpack_no = $this->input->post('unpack_no', TRUE);
        if (empty($unpack_no)) {
            return $this->_json(array('status' => 0, 'message' => 'Invalid unpack number.'));
        }

        $header = $this->Unpack_coil_model->get_header($unpack_no);
        if (!$header) {
            return $this->_json(array('status' => 0, 'message' => 'Unpack report not found.'));
        }

        if ($header['status'] == 'Lock') {
            return $this->_json(array('status' => 0, 'message' => 'This unpack report already has status Lock.'));
        }

        $this->Unpack_coil_model->lock_req($unpack_no, $this->id_user);

        return $this->_json(array('status' => 1, 'message' => 'Unpack report locked successfully.'));
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
