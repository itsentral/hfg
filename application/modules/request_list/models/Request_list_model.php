<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Request_list_model extends BF_Model
{
    private $stock_move_error = null;
    private $stock_move_changes = [];

    public function get_stock_move_error() { return $this->stock_move_error; }
    public function get_stock_move_changes() { return $this->stock_move_changes; }

    public function __construct()
    {
        parent::__construct();
    }

    // ---------------------------------------------------------------
    // SPK LIST & DATATABLES
    // ---------------------------------------------------------------

    /**
     * Get daftar SPK Material dengan filter status untuk DataTables server-side
     * Filter status: 'waiting' (default), 'released', atau 'all'
     * Include subquery detail_count untuk jumlah produk
     *
     * @param string $search        Search keyword (spk_no)
     * @param int    $start         Offset untuk pagination
     * @param int    $length        Limit rows
     * @param string $order_by      Kolom untuk ORDER BY
     * @param string $order_dir     ASC atau DESC
     * @param string $status_filter Filter status tab ('waiting', 'released', 'all')
     * @return array Array of SPK Material rows
     */
    public function get_spk_list($search, $start, $length, $order_by, $order_dir, $status_filter = 'waiting')
    {
        $this->db->select("
            h.*,
            (SELECT COUNT(*) FROM tr_spk_material_detail d WHERE d.spk_no = h.spk_no) as detail_count,
            (SELECT COUNT(DISTINCT COALESCE(wsc.id_pack, ks.id_coil))
             FROM warehouse_coil_kartu_stok ks
             LEFT JOIN warehouse_stock_coil wsc ON wsc.id = ks.id_coil
             WHERE ks.no_trs = h.spk_no AND ks.status = 'in' AND ks.ke = 'prod. 2') as scanned_pack_count,
            (SELECT pr.status_draft
             FROM production_reports pr
             JOIN tr_spk_material_detail d2 ON d2.id = pr.id_tr_spk_detail
             WHERE d2.spk_no = h.spk_no AND pr.created_at >= h.created_at
             ORDER BY pr.id DESC LIMIT 1) as pr_status_draft
        ", false);
        $this->db->from('tr_spk_material_header h');

        if ($status_filter === 'released') {
            $this->db->where('h.status', 'Released');
            $this->db->where("NOT EXISTS (
                SELECT 1 FROM production_reports pr
                JOIN tr_spk_material_detail d2 ON d2.id = pr.id_tr_spk_detail
                WHERE d2.spk_no = h.spk_no AND pr.status_draft = 0 AND pr.created_at >= h.created_at
            )", null, false);
        } elseif ($status_filter === 'closed') {
            $this->db->where('h.status', 'Released');
            $this->db->where("EXISTS (
                SELECT 1 FROM production_reports pr
                JOIN tr_spk_material_detail d2 ON d2.id = pr.id_tr_spk_detail
                WHERE d2.spk_no = h.spk_no AND pr.status_draft = 0 AND pr.created_at >= h.created_at
            )", null, false);
        } elseif ($status_filter === 'all') {
            $this->db->where_in('h.status', array('Material Requested', 'Material On Load', 'Material Confirmed', 'Released'));
        } else {
            // Waiting: belum dirilis
            $this->db->where_in('h.status', array('Material Requested', 'Material On Load', 'Material Confirmed'));
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('h.spk_no', $search);
            $this->db->or_like('h.shift_names', $search);
            $this->db->or_like('h.status', $search);
            $this->db->group_end();
        }

        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);

        return $this->db->get()->result_array();
    }

    /**
     * Count total SPK Material filtered untuk DataTables recordsFiltered
     *
     * @param string $search        Search keyword
     * @param string $status_filter Filter status tab ('waiting', 'released', 'closed', 'all')
     * @return int Total count
     */
    public function count_spk_filtered($search, $status_filter = 'waiting')
    {
        $this->db->from('tr_spk_material_header h');

        if ($status_filter === 'released') {
            $this->db->where('h.status', 'Released');
            $this->db->where("NOT EXISTS (
                SELECT 1 FROM production_reports pr
                JOIN tr_spk_material_detail d2 ON d2.id = pr.id_tr_spk_detail
                WHERE d2.spk_no = h.spk_no AND pr.status_draft = 0 AND pr.created_at >= h.created_at
            )", null, false);
        } elseif ($status_filter === 'closed') {
            $this->db->where('h.status', 'Released');
            $this->db->where("EXISTS (
                SELECT 1 FROM production_reports pr
                JOIN tr_spk_material_detail d2 ON d2.id = pr.id_tr_spk_detail
                WHERE d2.spk_no = h.spk_no AND pr.status_draft = 0 AND pr.created_at >= h.created_at
            )", null, false);
        } elseif ($status_filter === 'all') {
            $this->db->where_in('h.status', array('Material Requested', 'Material On Load', 'Material Confirmed', 'Released'));
        } else {
            // Waiting: belum dirilis
            $this->db->where_in('h.status', array('Material Requested', 'Material On Load', 'Material Confirmed'));
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('h.spk_no', $search);
            $this->db->or_like('h.shift_names', $search);
            $this->db->or_like('h.status', $search);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    /**
     * Hitung jumlah SPK per tab status: waiting, released, closed, all (sinkron dengan SPK Material)
     *
     * @return array
     */
    public function get_status_counts()
    {
        $sql = "SELECT
                    SUM(CASE WHEN h.status IN ('Material Requested', 'Material On Load', 'Material Confirmed') THEN 1 ELSE 0 END) as waiting_count,
                    SUM(CASE WHEN h.status = 'Released' AND NOT EXISTS (
                        SELECT 1 FROM production_reports pr
                        JOIN tr_spk_material_detail d2 ON d2.id = pr.id_tr_spk_detail
                        WHERE d2.spk_no = h.spk_no AND pr.status_draft = 0 AND pr.created_at >= h.created_at
                    ) THEN 1 ELSE 0 END) as released_count,
                    SUM(CASE WHEN h.status = 'Released' AND EXISTS (
                        SELECT 1 FROM production_reports pr
                        JOIN tr_spk_material_detail d2 ON d2.id = pr.id_tr_spk_detail
                        WHERE d2.spk_no = h.spk_no AND pr.status_draft = 0 AND pr.created_at >= h.created_at
                    ) THEN 1 ELSE 0 END) as closed_count,
                    SUM(CASE WHEN h.status IN ('Material Requested', 'Material On Load', 'Material Confirmed', 'Released') THEN 1 ELSE 0 END) as all_count
                FROM tr_spk_material_header h";
        $row = $this->db->query($sql)->row_array();

        return array(
            'waiting'  => (int) (isset($row['waiting_count']) ? $row['waiting_count'] : 0),
            'released' => (int) (isset($row['released_count']) ? $row['released_count'] : 0),
            'closed'   => (int) (isset($row['closed_count']) ? $row['closed_count'] : 0),
            'all'      => (int) (isset($row['all_count']) ? $row['all_count'] : 0),
        );
    }

    // ---------------------------------------------------------------
    // SCAN NO. PACK -> PILIH SPK -> PRT + RELEASE SPK (ALUR BARU)
    // ---------------------------------------------------------------

    /**
     * Daftar SPK aktif untuk popup pilihan setelah scan No. Pack:
     * semua SPK selain Cancelled yang belum berstatus selesai (Done) di Production Report.
     *
     * @return array
     */
    public function get_scannable_spk_list()
    {
        $sql = "
            SELECT
                h.spk_no,
                h.tgl_spk,
                h.status,
                h.shift_names,
                (SELECT GROUP_CONCAT(CONCAT(d.nm_produk_fg, ' (', d.target_qty, ' pcs)') ORDER BY d.urut ASC SEPARATOR ', ')
                 FROM tr_spk_material_detail d
                 WHERE d.spk_no = h.spk_no) AS produk_summary
            FROM tr_spk_material_header h
            WHERE h.status != 'Cancelled'
              AND NOT EXISTS (
                  SELECT 1
                  FROM production_reports pr
                  JOIN tr_spk_material_detail d2 ON d2.id = pr.id_tr_spk_detail
                  WHERE d2.spk_no = h.spk_no
                    AND pr.status_draft = 0
                    AND pr.created_at >= h.created_at
              )
            ORDER BY h.tgl_spk DESC, h.spk_no DESC
        ";
        return $this->db->query($sql)->result_array();
    }

    /**
     * Validasi No. Pack sebelum menampilkan popup pemilihan No. SPK.
     * Aturan:
     * - Tidak mengikat material pack ke BOM SPK tertentu.
     * - Hanya menerima pack aktif dari gudang PRO (bukan WIP / SLI / PRT).
     *
     * @param string $pack_code
     * @return array
     */
    public function check_pack_for_scan($pack_code)
    {
        $pack_code = trim((string) $pack_code);
        if ($pack_code === '') {
            return ['status' => false, 'message' => 'Pack Number is required.'];
        }

        $pack = $this->db->query(
            "SELECT * FROM warehouse_pack WHERE UPPER(TRIM(pack_code)) = UPPER(?) AND status = 1 LIMIT 1",
            [$pack_code]
        )->row_array();

        if (!$pack) {
            return ['status' => false, 'message' => 'No. Pack "' . $pack_code . '" not found or inactive.'];
        }

        $pack_kd = strtoupper(trim((string) $pack['kd_gudang']));
        if ($pack_kd === 'PRT') {
            return ['status' => false, 'message' => 'Pack ' . $pack['pack_code'] . ' is already in warehouse WRH Production 2 (PRT).'];
        }
        if ($pack_kd !== 'PRO') {
            return ['status' => false, 'message' => 'Pack ' . $pack['pack_code'] . ' is in warehouse ' . ($pack_kd ?: '-') . '. Scan hanya menerima pack dari gudang PRO (WRH Production 1).'];
        }

        $coils = $this->db->query(
            "SELECT id, id_material, nm_material, no_coil, kode_internal, id_gudang, kd_gudang,
                    net_weight, gross_weight, status, status_proses, is_baby_coil
             FROM warehouse_stock_coil
             WHERE id_pack = ? AND status = 1
             ORDER BY id ASC",
            [(int) $pack['id']]
        )->result_array();

        if (empty($coils)) {
            return ['status' => false, 'message' => 'Pack ' . $pack['pack_code'] . ' has no active coils (empty / already used).'];
        }

        $eligible_coils = [];
        $total_net      = 0.0;
        $total_gross    = 0.0;

        foreach ($coils as $c) {
            $coil_kd = strtoupper(trim((string) $c['kd_gudang']));
            $sp      = trim((string) $c['status_proses']);

            if ($coil_kd === 'PRT' || $sp === 'in_transit') {
                return ['status' => false, 'message' => 'Pack ' . $pack['pack_code'] . ' already has coils in WRH Production 2 (PRT).'];
            }
            if ($coil_kd !== 'PRO') {
                return ['status' => false, 'message' => 'Coil ' . $c['no_coil'] . ' in pack ' . $pack['pack_code'] . ' is in warehouse ' . ($coil_kd ?: '-') . ' (scan hanya menerima PRO).'];
            }
            if (in_array($sp, ['unpacked', 'consumed', 'wip', 'on_hold', 'finish_good'], true)) {
                return ['status' => false, 'message' => 'Coil ' . $c['no_coil'] . ' in pack ' . $pack['pack_code'] . ' berstatus "' . $sp . '" and cannot be moved.'];
            }

            $total_net   += (float) $c['net_weight'];
            $total_gross += (float) $c['gross_weight'];
            $eligible_coils[] = $c;
        }

        $spk_list = $this->get_scannable_spk_list();
        if (empty($spk_list)) {
            return ['status' => false, 'message' => 'There is no active SPK to select at this time.'];
        }

        return [
            'status' => true,
            'pack'   => [
                'id'                 => (int) $pack['id'],
                'pack_code'          => $pack['pack_code'],
                'ref_number'         => $pack['ref_number'],
                'no_po'              => $pack['no_po'],
                'kd_gudang'          => $pack['kd_gudang'],
                'coil_count'         => count($eligible_coils ?? []),
                'total_net_weight'   => round($total_net, 3),
                'total_gross_weight' => round($total_gross, 3),
            ],
            'coils'    => $eligible_coils,
            'spk_list' => $spk_list,
        ];
    }

    /**
     * Konfirmasi pemindahan seluruh coil aktif dalam pack dari PRO ke PRT
     * dengan no_trs Kartu Pemakaian Material = $spk_no terpilih.
     * Catatan: Tidak mengubah tr_spk_material_header.status.
     *
     * @param string $pack_code
     * @param string $spk_no
     * @param int    $created_by
     * @return array
     */
    public function confirm_scan_pack_to_prt($pack_code, $spk_no, $created_by)
    {
        $this->stock_move_error = null;
        $this->stock_move_changes = [];
        $pack_code = trim((string) $pack_code);
        $spk_no    = trim((string) $spk_no);

        if ($pack_code === '' || $spk_no === '') {
            return ['status' => false, 'message' => 'Pack No. and SPK No. are required.'];
        }

        $now = date('Y-m-d H:i:s');
        $this->load->helper('coil_kartu_stok');

        $this->db->trans_begin();

        $spk = $this->db->query(
            "SELECT * FROM tr_spk_material_header WHERE spk_no = ? LIMIT 1 FOR UPDATE",
            [$spk_no]
        )->row_array();

        if (!$spk) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'No. SPK "' . $spk_no . '" tidak ditemukan.'];
        }
        if ($spk['status'] === 'Cancelled') {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'SPK ' . $spk_no . ' has status Cancelled and cannot be selected.'];
        }

        $done_pr = $this->db->query(
            "SELECT pr.id
             FROM production_reports pr
             JOIN tr_spk_material_detail d ON d.id = pr.id_tr_spk_detail
             JOIN tr_spk_material_header h ON h.spk_no = d.spk_no
             WHERE d.spk_no = ? AND pr.status_draft = 0 AND pr.created_at >= h.created_at
             LIMIT 1",
            [$spk_no]
        )->row_array();

        if ($done_pr) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'SPK ' . $spk_no . ' is already finished (Done) in the Production Report.'];
        }

        $pack = $this->db->query(
            "SELECT * FROM warehouse_pack WHERE UPPER(TRIM(pack_code)) = UPPER(?) AND status = 1 LIMIT 1 FOR UPDATE",
            [$pack_code]
        )->row_array();

        if (!$pack) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'No. Pack "' . $pack_code . '" not found or inactive.'];
        }

        $pack_kd = strtoupper(trim((string) $pack['kd_gudang']));
        if ($pack_kd === 'PRT') {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Pack ' . $pack['pack_code'] . ' is already in warehouse WRH Production 2 (PRT).'];
        }
        if ($pack_kd !== 'PRO') {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Pack ' . $pack['pack_code'] . ' is in warehouse ' . ($pack_kd ?: '-') . ' (hanya menerima PRO).'];
        }

        $coils = $this->db->query(
            "SELECT * FROM warehouse_stock_coil WHERE id_pack = ? AND status = 1 ORDER BY id ASC FOR UPDATE",
            [(int) $pack['id']]
        )->result_array();

        if (empty($coils)) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Pack ' . $pack['pack_code'] . ' has no active coils.'];
        }

        foreach ($coils as $c) {
            $coil_kd = strtoupper(trim((string) $c['kd_gudang']));
            $sp      = trim((string) $c['status_proses']);
            if ($coil_kd !== 'PRO' || in_array($sp, ['in_transit', 'unpacked', 'consumed', 'wip', 'on_hold', 'finish_good'], true)) {
                $this->db->trans_rollback();
                return ['status' => false, 'message' => 'Coil ' . $c['no_coil'] . ' in pack ' . $pack['pack_code'] . ' is not in a ready state in the PRO warehouse.'];
            }
        }

        $old_pack_snapshot = $pack;
        $moved_coils       = [];
        $total_weight      = 0.0;

        foreach ($coils as $c) {
            $res = $this->_move_coil_to_prt((int) $c['id'], $spk_no, (int) $created_by);
            if ($res === false) {
                $this->db->trans_rollback();
                return ['status' => false, 'message' => $this->stock_move_error ?: 'Coil ' . $c['no_coil'] . ' failed to be moved to PRT.'];
            }

            $moved_coils[] = $c['no_coil'];
            $total_weight += (float) $c['net_weight'];

            $this->db->insert('tr_spk_scan_log', [
                'spk_no'      => $spk_no,
                'no_coil'     => !empty($c['no_coil']) ? $c['no_coil'] : $pack['pack_code'],
                'scan_time'   => $now,
                'scan_user'   => (int) $created_by,
                'status_scan' => 'success',
                'keterangan'  => 'Scan Pack ' . $pack['pack_code'] . ' (' . $c['kode_internal'] . ') PRO -> PRT | No. Trs: ' . $spk_no,
            ]);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Failed to save the pack transfer transaction to PRT.'];
        }

        $this->db->trans_commit();

        $new_pack_snapshot = array_merge($old_pack_snapshot ?? [], [
            'id_gudang' => $res['destination_id'],
            'kd_gudang' => 'PRT',
        ]);

        return [
            'status'      => true,
            'message'     => 'Pack ' . $pack['pack_code'] . ' (' . count($moved_coils ?? []) . ' coil, ' . number_format((float) $total_weight, 2, '.', ',') . ' Kg) berhasil dipindahkan ke WRH Production 2 (PRT) untuk transaksi ' . $spk_no . '.',
            'pack_code'   => $pack['pack_code'],
            'spk_no'      => $spk_no,
            'coil_count'  => count($moved_coils ?? []),
            'total_weight'=> round($total_weight, 3),
            'log_payload' => [
                'old' => $old_pack_snapshot,
                'new' => $new_pack_snapshot,
                'detail_changes' => $this->stock_move_changes,
            ],
        ];
    }

    /**
     * Release SPK secara eksplisit melalui tombol per baris SPK di Request List.
     * Menjadi syarat pembuatan Production Report.
     *
     * @param string $spk_no
     * @param int    $user_id
     * @return array
     */
    public function release_spk($spk_no, $user_id)
    {
        $spk_no = trim((string) $spk_no);
        if ($spk_no === '') {
            return ['status' => false, 'message' => 'Invalid SPK number.'];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->trans_begin();

        $old = $this->db->query(
            "SELECT * FROM tr_spk_material_header WHERE spk_no = ? LIMIT 1 FOR UPDATE",
            [$spk_no]
        )->row_array();

        if (!$old) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'SPK "' . $spk_no . '" tidak ditemukan.'];
        }

        if ($old['status'] === 'Released') {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'SPK ' . $spk_no . ' already has status Released.'];
        }

        if ($old['status'] === 'Cancelled') {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'SPK ' . $spk_no . ' has status Cancelled and cannot be released.'];
        }

        $update_data = [
            'status'     => 'Released',
            'updated_by' => (int) $user_id,
            'updated_at' => $now,
        ];

        $this->db->where('spk_no', $spk_no)->update('tr_spk_material_header', $update_data);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Failed to change SPK status to Released.'];
        }

        $this->db->trans_commit();

        return [
            'status'      => true,
            'message'     => 'SPK ' . $spk_no . ' released successfully.',
            'log_payload' => [
                'old' => $old,
                'new' => array_merge($old ?? [], $update_data ?? []),
            ],
            'counts'      => $this->get_status_counts(),
        ];
    }

    /**
     * Ambil detail SPK + produk sesuai format spk_material/view untuk halaman view_spk_coil.
     *
     * @param string $spk_no
     * @return array|null
     */
    public function get_spk_detail_for_view($spk_no)
    {
        $header = $this->db
            ->select('h.*, COALESCE(u.nm_lengkap, u.username, h.created_by) AS created_by_name')
            ->from('tr_spk_material_header h')
            ->join('users u', 'u.id_user = h.created_by', 'left')
            ->where('h.spk_no', $spk_no)
            ->get()
            ->row_array();

        if (!$header) {
            return null;
        }

        $sql = "
            SELECT
                d.*,
                MAX(COALESCE(m.thickness, 0)) AS thickness,
                MAX(COALESCE(bd.berat_per_meter, 0)) AS berat_per_meter
            FROM tr_spk_material_detail d
            LEFT JOIN ms_bom_header bh ON bh.id_produk = d.id_produk_fg AND bh.is_delete = 0
            LEFT JOIN ms_bom_detail bd ON bd.id_bom = bh.id AND (bd.no_uniq_id = d.no_uniq_id OR d.no_uniq_id IS NULL) AND bd.is_delete = 0
            LEFT JOIN new_inventory_4 m ON (m.code_lv4 = bd.id_material OR m.id = bd.id_material)
            WHERE d.spk_no = ?
            GROUP BY d.id
            ORDER BY d.urut ASC
        ";
        $details = $this->db->query($sql, [$spk_no])->result_array();

        return [
            'spk'     => $header,
            'details' => $details,
        ];
    }

    /**
     * Ambil riwayat read-only Pack / Coil yang pernah di-scan ke PRT dengan no_trs = $spk_no.
     *
     * @param string $spk_no
     * @return array
     */
    public function get_spk_scan_pack_history($spk_no)
    {
        $rows = $this->db->query(
            "SELECT
                ks.id,
                ks.no_trs,
                ks.id_coil,
                ks.id_material,
                ks.nm_material,
                ks.no_coil,
                ks.kode_internal,
                ks.dari,
                ks.ke,
                ks.qty_trans,
                ks.keterangan,
                ks.created_by,
                ks.created_at,
                wp.pack_code,
                wsc.kd_gudang AS current_kd_gudang,
                wsc.status_proses
             FROM warehouse_coil_kartu_stok ks
             LEFT JOIN warehouse_stock_coil wsc ON wsc.id = ks.id_coil
             LEFT JOIN warehouse_pack wp ON wp.id = wsc.id_pack
             WHERE ks.no_trs = ?
               AND ks.status = 'in'
               AND ks.ke = 'prod. 2'
             ORDER BY ks.created_at DESC, ks.id DESC",
            [$spk_no]
        )->result_array();

        if (empty($rows)) {
            return [];
        }

        // Map numeric user IDs cleanly without risking mixed collation/int cast issues
        $user_ids = [];
        foreach ($rows as $r) {
            if (!empty($r['created_by']) && ctype_digit((string) $r['created_by'])) {
                $user_ids[] = (int) $r['created_by'];
            }
        }
        $user_map = [];
        if (!empty($user_ids)) {
            $users = $this->db
                ->select('id_user, nm_lengkap, username')
                ->where_in('id_user', array_values(array_unique($user_ids ?? [])))
                ->get('users')
                ->result_array();
            foreach ($users as $u) {
                $user_map[(string) $u['id_user']] = !empty($u['nm_lengkap']) ? $u['nm_lengkap'] : $u['username'];
            }
        }

        foreach ($rows as &$r) {
            $uid = (string) ($r['created_by'] ?? '');
            $r['scanned_by_name'] = isset($user_map[$uid]) ? $user_map[$uid] : ($uid !== '' ? $uid : '-');
        }
        unset($r);

        return $rows;
    }

    // ---------------------------------------------------------------
    // SPK DETAIL & BOM
    // ---------------------------------------------------------------

    /**
     * Get SPK header + detail produk + BOM materials per produk
     * JOIN tr_spk_material_detail untuk produk, lalu get BOM via ms_bom_header + ms_bom_detail
     *
     * @param string $spk_no Nomor SPK Material
     * @return array|null Array dengan 'header' dan 'products' (each with 'materials')
     */
    public function get_spk_with_details($spk_no)
    {
        // Get header
        $header = $this->db->where('spk_no', $spk_no)->get('tr_spk_material_header')->row_array();

        if (!$header) {
            return null;
        }

        // Get detail produk
        $products = $this->db
            ->where('spk_no', $spk_no)
            ->order_by('urut', 'ASC')
            ->get('tr_spk_material_detail')
            ->result_array();

        // Calculate sum from detail
        $sum_qty = 0;
        $sum_weight = 0;
        foreach ($products as $p) {
            // Check possible column names for qty
            if (isset($p['target_qty'])) $sum_qty += (float)$p['target_qty'];

            // Check possible column names for weight
            if (isset($p['weight'])) $sum_weight += (float)$p['weight'];
            elseif (isset($p['total_weight'])) $sum_weight += (float)$p['total_weight'];
        }

        $header['target_qty'] = $sum_qty;
        $header['total_weight'] = $sum_weight;

        // Get BOM materials per produk
        foreach ($products as &$product) {
            $sql = "SELECT bd.id_material, bd.nm_material, bd.qty, bd.id_unit, bd.nm_unit
                    FROM ms_bom_header bh
                    JOIN ms_bom_detail bd ON bd.id_bom = bh.id
                    WHERE bh.id_produk = ? AND bh.is_delete = 0 AND bd.is_delete = 0
                    ORDER BY bd.nm_material ASC";

            $materials = $this->db->query($sql, [$product['id_produk_fg']])->result_array();

            foreach ($materials as &$mat) {
                // Stock Produksi (Gudang 1)
                $prod_row = $this->db->select('qty_stock')
                    ->where('id_material', $mat['id_material'])
                    ->where('id_gudang', 1)
                    ->get('warehouse_stock')
                    ->row();
                $mat['stock_produksi'] = $prod_row ? (float) $prod_row->qty_stock : 0;

                // Stock WIP (dari warehouse_stock_coil dengan kd_gudang = 'WIP')
                $wip_row = $this->db->select_sum('net_weight', 'qty')
                    ->where('id_material', $mat['id_material'])
                    ->where('kd_gudang', 'WIP')
                    ->where('status', 1)
                    ->get('warehouse_stock_coil')
                    ->row();
                $mat['stock_wip'] = $wip_row ? (float) $wip_row->qty : 0;
            }

            $product['materials'] = $materials;
        }

        return [
            'header'   => $header,
            'products' => $products
        ];
    }

    // ---------------------------------------------------------------
    // COIL AVAILABILITY
    // ---------------------------------------------------------------

    /**
     * Get available coils dari warehouse_stock_coil
     * Filter: id_material, id_gudang IN (1, 3), status = 'active'
     * Exclude: coil yang sudah dipilih di SPK Coil sebelumnya untuk SPK yang sama (non-Rejected)
     *
     * @param string $id_material ID material
     * @param string $spk_no      Nomor SPK Material (untuk exclude already selected)
     * @return array Array of available coil rows
     */
    public function get_available_coils($id_material, $spk_no)
    {
        $sql_pro = "SELECT c.id, c.id_material, c.nm_material, c.kode_internal, c.no_coil, c.net_weight, c.id_gudang, c.kd_gudang, 1 as source_type,
                        (SELECT wrh.spk_coil_no 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 1 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as assigned_spkc,
                        (SELECT wrcd.scan_status 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 1 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as scan_status,
                        (SELECT wrcd.request_id 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 1 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as assigned_request_id
                    FROM warehouse_stock_coil c
                    WHERE c.id_material = ?
                    AND c.kd_gudang = 'PRO'
                    AND c.status = 1
                    AND c.status_proses = 'in_warehouse'";

        $sql_wip = "SELECT c.id, c.id_material, c.nm_material, c.kode_internal, c.no_coil, c.net_weight, c.id_gudang, c.kd_gudang, 4 as source_type,
                        (SELECT wrh.spk_coil_no 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 4 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as assigned_spkc,
                        (SELECT wrcd.scan_status 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 4 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as scan_status,
                        (SELECT wrcd.request_id 
                            FROM tr_warehouse_request_coil_detail wrcd
                            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                            WHERE wrcd.id_coil = c.id AND wrcd.id_gudang_sumber = 4 AND wrh.status != 'Rejected' AND wrh.status != 'Cancelled'
                            ORDER BY wrcd.id DESC LIMIT 1) as assigned_request_id
                    FROM warehouse_stock_coil c
                    WHERE c.id_material = ?
                    AND c.kd_gudang = 'WIP'
                    AND c.status = 1
                    AND c.status_proses = 'wip'";

        $pro_coils = $this->db->query($sql_pro, [$id_material])->result_array();
        $wip_coils = $this->db->query($sql_wip, [$id_material])->result_array();

        return array_merge($pro_coils ?? [], $wip_coils ?? []);
    }

    // ---------------------------------------------------------------
    // PACK-BASED AVAILABILITY
    // ---------------------------------------------------------------

    /**
     * Get available packs that contain a specific material
     * Exclude packs yang sudah fully assigned ke SPK Coil aktif (non-Rejected/Cancelled)
     *
     * @param string $id_material ID material
     * @param string $spk_no      SPK number (untuk context)
     * @return array Array of pack rows dengan roll_count, total_nw, total_gw, assigned info
     */
    public function get_available_packs_by_material($id_material, $spk_no)
    {
        $sql = "
            SELECT
                wp.id AS id_pack,
                wp.pack_code,
                wp.kd_gudang,
                GROUP_CONCAT(DISTINCT wsc.kode_internal ORDER BY wsc.kode_internal ASC SEPARATOR ', ') AS kode_internal,
                COUNT(wsc.id) AS roll_count,
                SUM(wsc.net_weight) AS total_nw,
                SUM(wsc.gross_weight) AS total_gw,
                (
                    SELECT GROUP_CONCAT(DISTINCT wrh.spk_coil_no SEPARATOR ', ')
                    FROM tr_warehouse_request_coil_detail wrcd
                    JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                    WHERE wrcd.pack_code = wp.pack_code COLLATE utf8mb4_general_ci
                      AND wrh.status NOT IN ('Rejected', 'Cancelled')
                    LIMIT 1
                ) AS assigned_spkc,
                (
                    SELECT MAX(wrcd2.scan_status)
                    FROM tr_warehouse_request_coil_detail wrcd2
                    JOIN tr_warehouse_request_header wrh2 ON wrh2.id = wrcd2.request_id
                    WHERE wrcd2.pack_code = wp.pack_code COLLATE utf8mb4_general_ci
                      AND wrh2.status NOT IN ('Rejected', 'Cancelled')
                ) AS scan_status,
                (
                    SELECT wrcd3.request_id
                    FROM tr_warehouse_request_coil_detail wrcd3
                    JOIN tr_warehouse_request_header wrh3 ON wrh3.id = wrcd3.request_id
                    WHERE wrcd3.pack_code = wp.pack_code COLLATE utf8mb4_general_ci
                      AND wrh3.status NOT IN ('Rejected', 'Cancelled')
                    LIMIT 1
                ) AS assigned_request_id
            FROM warehouse_pack wp
            JOIN warehouse_stock_coil wsc ON wsc.id_pack = wp.id
                AND wsc.id_material = ?
                AND wsc.status = 1
                AND (wsc.is_baby_coil = 1 OR (wsc.is_baby_coil = 0 AND wsc.qty_roll <= 1))
            WHERE wp.status = 1
            GROUP BY wp.id
            ORDER BY wp.pack_code ASC
        ";

        return $this->db->query($sql, [$id_material])->result_array();
    }

    /**
     * Get all coils inside a specific pack for a given material
     *
     * @param int    $id_pack     ID pack
     * @param string $id_material ID material (optional filter, null = all materials in pack)
     * @return array Array of coil rows
     */
    public function get_coils_in_pack($id_pack, $id_material = null)
    {
        $where_material = '';
        $params = [$id_pack];
        if ($id_material) {
            $where_material = ' AND wsc.id_material = ?';
            $params[] = $id_material;
        }

        $sql = "
            SELECT wsc.id, wsc.id_material, wsc.nm_material, wsc.trade_name,
                   wsc.no_coil, wsc.kode_internal, wsc.net_weight, wsc.gross_weight,
                   wsc.length, wsc.id_gudang, wsc.kd_gudang, wsc.id_pack,
                   wsc.is_baby_coil, wsc.qty_roll,
                   wp.pack_code
            FROM warehouse_stock_coil wsc
            JOIN warehouse_pack wp ON wp.id = wsc.id_pack
            WHERE wsc.id_pack = ?
              AND wsc.status = 1
              AND (wsc.is_baby_coil = 1 OR (wsc.is_baby_coil = 0 AND wsc.qty_roll <= 1))
              {$where_material}
            ORDER BY wsc.nm_material ASC, wsc.no_coil ASC
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Get total coil count per material per gudang
     *
     * @param string $id_material ID material
     * @param int    $id_gudang   ID gudang (1=Gudang Coil, 3=WIP)
     * @return int Count of available coils
     */
    public function get_coil_count_by_gudang($id_material, $id_gudang)
    {
        $this->db->where('id_material', $id_material);
        $this->db->where('id_gudang', $id_gudang);
        $this->db->where('status', 1);

        return $this->db->count_all_results('warehouse_stock_coil');
    }

    /**
     * Check single coil availability
     * SELECT dari warehouse_stock_coil WHERE id=? AND status=1
     *
     * @param int $id_coil ID coil
     * @return array|null Coil row atau null jika tidak tersedia
     */
    public function check_coil_available($id_coil, $id_gudang_sumber = 0)
    {
        // Semua coil (PRO, WIP) sekarang ada di tabel warehouse_stock_coil
        return $this->db
            ->where('id', $id_coil)
            ->where('status', 1)
            ->get('warehouse_stock_coil')
            ->row_array();
    }

    // ---------------------------------------------------------------
    // SPK COIL NUMBER GENERATION
    // ---------------------------------------------------------------

    /**
     * Generate nomor SPK Coil format [spk_no]/TRS-001
     * Query last counter dari tr_warehouse_request_header per SPK Material
     *
     * @param string $spk_no Nomor SPK Material
     * @return string Nomor SPK Coil baru (e.g. SPK-001/TRS-001)
     */
    public function generate_spk_coil_no($spk_no)
    {
        $prefix = $spk_no . '/TRS-';

        // #3: FOR UPDATE lock untuk prevent race condition nomor urut
        $sql = "SELECT spk_coil_no FROM tr_warehouse_request_header
                WHERE spk_no = ? AND spk_coil_no LIKE ?
                ORDER BY spk_coil_no DESC
                LIMIT 1
                FOR UPDATE";

        $last = $this->db->query($sql, array($spk_no, $prefix . '%'))->row();

        $next = 1;
        if ($last) {
            $parts = explode('/TRS-', (string) $last->spk_coil_no);
            if (isset($parts[1])) {
                $next = (int) $parts[1] + 1;
            }
        }

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    // ---------------------------------------------------------------
    // INSERT & UPDATE OPERATIONS
    // ---------------------------------------------------------------

    /**
     * Insert header warehouse request ke tr_warehouse_request_header
     *
     * @param array $data Associative array kolom header
     * @return int Insert ID
     */
    public function insert_request_header($data)
    {
        $this->db->insert('tr_warehouse_request_header', $data);
        return $this->db->insert_id();
    }

    /**
     * Insert batch coil details ke tr_warehouse_request_coil_detail
     *
     * @param array $details Array of associative arrays untuk setiap coil
     * @return bool Insert batch result
     */
    public function insert_coil_details($details)
    {
        return $this->db->insert_batch('tr_warehouse_request_coil_detail', $details);
    }

    /**
     * Update status SPK Material di tr_spk_material_header
     *
     * @param string $spk_no Nomor SPK
     * @param array  $data   Array berisi status, updated_by, updated_at
     * @return bool Update result
     */
    public function update_spk_material_status($spk_no, $data)
    {
        $this->db->where('spk_no', $spk_no);
        return $this->db->update('tr_spk_material_header', $data);
    }

    // ---------------------------------------------------------------
    // EXISTING COIL SELECTIONS
    // ---------------------------------------------------------------

    /**
     * Get coil IDs yang sudah dipilih untuk SPK Material tertentu
     * Dari tr_warehouse_request_coil_detail JOIN header WHERE spk_no AND status != 'Rejected'
     *
     * @param string $spk_no Nomor SPK Material
     * @return array Array of id_coil values
     */
    public function get_selected_coil_ids($spk_no)
    {
        $this->db->select('wrcd.id_coil');
        $this->db->from('tr_warehouse_request_coil_detail wrcd');
        $this->db->join('tr_warehouse_request_header wrh', 'wrh.id = wrcd.request_id');
        $this->db->where('wrh.spk_no', $spk_no);
        $this->db->where('wrh.status !=', 'Rejected');

        $result = $this->db->get()->result_array();

        return array_column($result ?? [], 'id_coil');
    }

    // ---------------------------------------------------------------
    // READ OPERATIONS
    // ---------------------------------------------------------------

    /**
     * Get saved coils by SPK number
     *
     * @param string $spk_no Nomor SPK Material
     * @return array Array of coil details with request header info
     */
    public function get_saved_coils_by_spk($spk_no)
    {
        $this->db->select('wrcd.*, wrh.spk_coil_no, wrh.status as request_status');
        $this->db->from('tr_warehouse_request_coil_detail wrcd');
        $this->db->join('tr_warehouse_request_header wrh', 'wrh.id = wrcd.request_id');
        $this->db->where('wrh.spk_no', $spk_no);
        $this->db->where('wrh.status !=', 'Rejected');
        return $this->db->get()->result_array();
    }

    /**
     * Get single warehouse request header by ID
     *
     * @param int $request_id ID request
     * @return array|null Request row atau null
     */
    public function get_request_by_id($request_id)
    {
        return $this->db
            ->where('id', $request_id)
            ->get('tr_warehouse_request_header')
            ->row_array();
    }

    /**
     * Get request header by ID with FOR UPDATE lock (untuk double-confirm prevention)
     *
     * @param int $request_id
     * @return array|null
     */
    public function get_request_by_id_locked($request_id)
    {
        return $this->db->query(
            "SELECT * FROM tr_warehouse_request_header WHERE id = ? LIMIT 1 FOR UPDATE",
            array($request_id)
        )->row_array();
    }

    /**
     * Get coil details by request_id
     *
     * @param int $request_id ID request header
     * @return array Array of coil detail rows
     */
    public function get_coil_details($request_id)
    {
        return $this->db
            ->select('d.*, c.net_weight')
            ->from('tr_warehouse_request_coil_detail d')
            ->join('warehouse_stock_coil c', 'c.id = d.id_coil', 'left')
            ->where('d.request_id', $request_id)
            ->get()
            ->result_array();
    }

    // ---------------------------------------------------------------
    // SPK COIL STATUS
    // ---------------------------------------------------------------

    /**
     * Count SPK Coil (non-Rejected) untuk SPK Material tertentu
     *
     * @param string $spk_no Nomor SPK Material
     * @return int Jumlah SPK Coil
     */
    public function get_spk_coil_count($spk_no)
    {
        $this->db->where('spk_no', $spk_no);
        $this->db->where('status !=', 'Rejected');

        return $this->db->count_all_results('tr_warehouse_request_header');
    }

    /**
     * Check apakah masih ada material dari BOM yang belum fully covered oleh coil selections
     * Membandingkan material BOM list vs coil yang sudah dipilih (grouped by id_material)
     *
     * @param string $spk_no Nomor SPK Material
     * @return bool True jika masih ada material yang belum terpenuhi
     */
    public function has_unfulfilled_material($spk_no)
    {
        // Get semua material dari BOM untuk SPK ini
        $sql_bom = "SELECT DISTINCT bd.id_material
                    FROM tr_spk_material_detail sd
                    JOIN ms_bom_header bh ON bh.id_produk = sd.id_produk_fg AND bh.is_delete = 0
                    JOIN ms_bom_detail bd ON bd.id_bom = bh.id AND bd.is_delete = 0
                    WHERE sd.spk_no = ?";

        $bom_materials = $this->db->query($sql_bom, [$spk_no])->result_array();

        if (empty($bom_materials)) {
            return false;
        }

        // Get material IDs yang sudah punya coil selection (non-Rejected)
        $sql_selected = "SELECT DISTINCT wrcd.id_material
                         FROM tr_warehouse_request_coil_detail wrcd
                         JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
                         WHERE wrh.spk_no = ? AND wrh.status != 'Rejected'";

        $selected_materials = $this->db->query($sql_selected, [$spk_no])->result_array();
        $selected_ids = array_column($selected_materials ?? [], 'id_material');

        // Check if any BOM material belum ada di selections
        foreach ($bom_materials as $material) {
            if (!in_array($material['id_material'], $selected_ids ?? [])) {
                return true;
            }
        }

        return false;
    }

    // ---------------------------------------------------------------
    // SCAN & CONFIRM SPK COIL OPERATIONS (Migrated from Confirm_spk_coil_model)
    // ---------------------------------------------------------------

    /**
     * Get pending SPK Coils (request headers) by SPK Material
     */
    public function get_pending_spkc_by_spk($spk_no)
    {
        $this->db->where('spk_no', $spk_no);
        $this->db->where('status', 'Material On Load');
        return $this->db->get('tr_warehouse_request_header')->result_array();
    }

    /**
     * Get ALL SPK Coils (request headers) by SPK Material — untuk print (hanya yang confirmed)
     */
    public function get_all_spkc_by_spk($spk_no)
    {
        $this->db->where('spk_no', $spk_no);
        $this->db->where('status', 'Material Confirmed');
        $this->db->order_by('created_at', 'ASC');
        return $this->db->get('tr_warehouse_request_header')->result_array();
    }

    public function update_scan_status($detail_id, $data)
    {
        $this->db->where('id', $detail_id);
        return $this->db->update('tr_warehouse_request_coil_detail', $data);
    }

    public function find_coil_by_kode_internal($request_id, $kode_internal, $nm_gudang)
    {
        $this->db->select('d.*, w.kd_gudang, w.nm_gudang');
        $this->db->from('tr_warehouse_request_coil_detail d');
        $this->db->join('warehouse w', 'w.id = d.id_gudang_sumber', 'left');
        $this->db->where('d.request_id', $request_id);
        $this->db->where('d.kode_internal', $kode_internal);
        $this->db->where('LOWER(w.nm_gudang)', strtolower((string) $nm_gudang));

        return $this->db->get()->row_array();
    }

    public function all_coils_scanned($request_id)
    {
        $this->db->where('request_id', $request_id);
        $this->db->where('scan_status', 0);
        $count = $this->db->count_all_results('tr_warehouse_request_coil_detail');

        return $count == 0;
    }

    /**
     * Konfirmasi material: transfer coil PRO beserta companion pack dan saldo bukunya ke PRT.
     * Qty/nilai OUT dan IN sama; nilai sumber memakai costbook PRO, tujuan moving average.
     * WIP/Hold tetap dipakai langsung dari gudang asal. Caller wajib rollback jika ada error.
     * @return array Ringkasan coil yang ditransfer; kosong jika sudah diproses atau sumber WIP/Hold.
     */
    public function reduce_coil_stock($id_coil, $kode_trans, $created_by)
    {
        $results = [];

        // Proses coil utama
        $main = $this->_move_coil_to_prt((int) $id_coil, $kode_trans, $created_by);
        if ($main === false) {
            return $results; // coil tidak valid / sudah diproses
        }
        $results[] = $main;

        // Proses seluruh coil companion dalam pack yang sama (jika ada id_pack)
        if (!empty($main['id_pack'])) {
            $companions = $this->db->query(
                "SELECT id FROM warehouse_stock_coil
                 WHERE id_pack = ? AND status = 1 AND id != ?
                   AND (status_proses IS NULL OR status_proses != 'in_transit')",
                [$main['id_pack'], (int) $id_coil]
            )->result_array();

            foreach ($companions as $c) {
                $res = $this->_move_coil_to_prt((int) $c['id'], $kode_trans, $created_by);
                if ($res !== false) {
                    $results[] = $res;
                }
            }
        }

        return $results;
    }

    /**
     * Pindahkan satu coil PRO ke PRT bersama saldo, ledger, snapshot dan kartu pemakaian.
     * Idempoten: coil yang sudah in_transit akan di-skip (return false).
     *
     * @return array|false Ringkasan per coil, atau false jika di-skip/invalid.
     */
    private function _move_coil_to_prt($id_coil, $kode_trans, $created_by)
    {
        $this->load->helper(['material_stock', 'coil_kartu_stok']);
        try {
            $coil = $this->db->query(
                'SELECT * FROM warehouse_stock_coil WHERE id = ? AND status = 1 LIMIT 1 FOR UPDATE',
                [$id_coil]
            )->row_array();
            if (!$coil || ($coil['kd_gudang'] === 'PRT' && $coil['status_proses'] === 'in_transit')) {
                return false;
            }
            // WIP/Hold dipakai langsung dari gudang asal, bukan ditransfer ulang ke PRT.
            if (in_array($coil['kd_gudang'], ['WIP', 'HLD'], true)) {
                return false;
            }
            if ($coil['kd_gudang'] !== 'PRO') {
                throw new RuntimeException('Transfer scan only accepts coils from PRO.');
            }
            if (in_array($coil['status_proses'], ['in_transit','unpacked','consumed','wip','on_hold','finish_good'], true)) {
                throw new RuntimeException('The source coil status in PRO is not ready to be moved.');
            }
            $source = material_stock_warehouse($this->db, 'PRO');
            $destination = material_stock_warehouse($this->db, 'PRT');
            if ((string)$coil['id_gudang'] !== (string)$source['id']) {
                throw new RuntimeException('The coil source warehouse identity does not match the PRO master.');
            }
            $pack = !empty($coil['id_pack'])
                ? $this->db->get_where('warehouse_pack', ['id' => $coil['id_pack']])->row_array() : [];
            $now = date('Y-m-d H:i:s');
            $qty = round((float)$coil['net_weight'], 3);
            $context = [
                'kode_trans' => $kode_trans, 'user' => $created_by, 'now' => $now,
                'source' => $source, 'destination' => $destination, 'to_status' => 'in_transit',
                'description' => 'Scan Pack PRO -> PRT (' . $kode_trans . ')',
                'pack_code' => $pack['pack_code'] ?? null,
            ];
            $out = material_stock_post($this->db, $coil, $source, $qty, 'OUT', $context, $this->stock_move_changes);
            $context['rate'] = $out['rate'];
            $context['value'] = $out['value'];
            $context['material_meta'] = $out['old'];
            $in = material_stock_post($this->db, $coil, $destination, $qty, 'IN', $context, $this->stock_move_changes);
            $update = ['id_gudang' => $destination['id'], 'kd_gudang' => 'PRT', 'status_proses' => 'in_transit',
                'harga_beli' => $out['rate'], 'total_nilai' => $out['value']];
            material_stock_write($this->db->where('id', $id_coil)->update('warehouse_stock_coil', $update));
            if (!empty($coil['id_pack'])) {
                material_stock_write($this->db->where('id', $coil['id_pack'])->update('warehouse_pack', [
                    'id_gudang' => $destination['id'], 'kd_gudang' => 'PRT',
                ]));
            }
            foreach ($update as $field => $value) {
                $this->stock_move_changes['Coil ' . $id_coil . ' - ' . $field] = [
                    'old' => $coil[$field] ?? null, 'new' => $value,
                ];
            }
            material_stock_coil_snapshot($this->db, $coil, 'OUT', $created_by, $now);
            material_stock_coil_snapshot($this->db, array_merge($coil, $update), 'IN', $created_by, $now);
            if (!$this->db->table_exists('warehouse_coil_kartu_stok')) {
                throw new RuntimeException('The Material Usage Card table is not available yet.');
            }
            material_stock_write(kartu_stok_record_entry([
                'id_coil' => (int)$id_coil, 'no_trs' => kartu_stok_extract_parent_spk($kode_trans),
                'id_material' => $coil['id_material'], 'nm_material' => $coil['nm_material'],
                'no_coil' => $coil['no_coil'], 'kode_internal' => $coil['kode_internal'],
                'status' => 'in', 'dari' => 'Produksi 1', 'ke' => 'prod. 2', 'qty_trans' => $qty,
                'keterangan' => $context['description'], 'created_by' => $created_by, 'created_at' => $now,
            ]));
            return ['id_material' => $coil['id_material'], 'id_pack' => $coil['id_pack'],
                'net_weight' => $qty, 'total_nilai' => $out['value'], 'destination_id' => $destination['id']];
        } catch (RuntimeException $e) {
            $this->stock_move_error = $e->getMessage();
            return false;
        }
    }

    public function insert_transaction_summary($data)
    {
        return $this->db->insert('warehouse_stock_transaction_summary', $data);
    }

    public function get_coil_source_data($id_coil)
    {
        $this->db->where('id', $id_coil);
        return $this->db->get('warehouse_stock_coil')->row_array();
    }

    public function update_request_status($request_id, $data)
    {
        $this->db->where('id', $request_id);
        return $this->db->update('tr_warehouse_request_header', $data);
    }

    public function remove_coil_from_spkc($request_id, $id_coil)
    {
        $this->db->where('request_id', $request_id);
        $this->db->where('id_coil', $id_coil);
        return $this->db->delete('tr_warehouse_request_coil_detail');
    }

    public function check_and_cancel_empty_spkc($request_id)
    {
        $this->db->where('request_id', $request_id);
        $count = $this->db->count_all_results('tr_warehouse_request_coil_detail');
        if ($count == 0) {
            $this->db->where('id', $request_id);
            $this->db->update('tr_warehouse_request_header', array('status' => 'Cancelled'));
        }
    }

    /**
     * Get saved SPK Coils (headers + details) for a given SPK Material
     *
     * @param string $spk_no
     * @return array Array of SPK Coil headers with 'coils' key containing details
     */
    public function get_saved_spk_coils_grouped($spk_no)
    {
        $headers = $this->db
            ->where('spk_no', $spk_no)
            ->where('status !=', 'Cancelled')
            ->where('status !=', 'Rejected')
            ->order_by('id', 'ASC')
            ->get('tr_warehouse_request_header')
            ->result_array();

        if (empty($headers)) {
            return array();
        }

        foreach ($headers as &$header) {
            $this->db->select('wrcd.*, w.nm_gudang');
            $this->db->from('tr_warehouse_request_coil_detail wrcd');
            $this->db->join('warehouse w', 'w.id = wrcd.id_gudang_sumber', 'left');
            $this->db->where('wrcd.request_id', $header['id']);
            $header['coils'] = $this->db->get()->result_array();
        }

        return $headers;
    }

    /**
     * Delete SPK Coil header & details by request ID, and update SPK Material status if empty
     *
     * @param int $request_id
     * @return array Array containing status (bool) and message (string)
     */
    public function delete_spk_coil_by_id($request_id)
    {
        $header = $this->db->where('id', $request_id)->get('tr_warehouse_request_header')->row_array();
        if (!$header) {
            return array('status' => false, 'message' => 'SPK Coil not found.');
        }

        if (in_array($header['status'], array('Material Confirmed', 'Confirmed', 'Released'))) {
            return array('status' => false, 'message' => 'SPK Coil cannot be deleted because its status is already ' . $header['status']);
        }

        // Cek apakah ada coil non-WIP yang sudah discan manual (coil WIP auto-scan boleh dihapus)
        $scanned_count = $this->db
            ->where('request_id', $request_id)
            ->where('scan_status', 1)
            ->where('id_gudang_sumber !=', 4)
            ->count_all_results('tr_warehouse_request_coil_detail');

        if ($scanned_count > 0) {
            return array('status' => false, 'message' => 'SPK Coil cannot be deleted because it contains scanned (non-WIP) coils.');
        }

        $spk_no = $header['spk_no'];

        $this->db->trans_start();

        // Delete coil details
        $this->db->where('request_id', $request_id)->delete('tr_warehouse_request_coil_detail');

        // Delete request header
        $this->db->where('id', $request_id)->delete('tr_warehouse_request_header');

        // Check remaining active SPK Coils for this spk_no
        $remaining_count = $this->db
            ->where('spk_no', $spk_no)
            ->where('status !=', 'Cancelled')
            ->where('status !=', 'Rejected')
            ->count_all_results('tr_warehouse_request_header');

        if ($remaining_count == 0) {
            $this->db->where('spk_no', $spk_no)->update('tr_spk_material_header', array(
                'status' => 'Material Requested'
            ));
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return array('status' => false, 'message' => 'Failed to delete SPK Coil.');
        }

        return array('status' => true, 'message' => 'SPK Coil ' . $header['spk_coil_no'] . ' berhasil dihapus.', 'spk_no' => $spk_no);
    }

    /**
     * Delete 1 coil item from SPK Coil detail by detail ID
     *
     * @param int $detail_id
     * @return array Array containing status (bool) and message (string)
     */
    public function delete_spk_coil_detail_item($detail_id)
    {
        $detail = $this->db->where('id', $detail_id)->get('tr_warehouse_request_coil_detail')->row_array();
        if (!$detail) {
            return array('status' => false, 'message' => 'Coil details not found.');
        }

        $request_id = $detail['request_id'];
        $header     = $this->db->where('id', $request_id)->get('tr_warehouse_request_header')->row_array();

        if (!$header) {
            return array('status' => false, 'message' => 'SPK Coil not found.');
        }

        if (in_array($header['status'], array('Material Confirmed', 'Confirmed', 'Released'))) {
            return array('status' => false, 'message' => 'The item cannot be deleted because the SPK Coil status is already ' . $header['status']);
        }

        if (isset($detail['scan_status']) && $detail['scan_status'] == 1) {
            // Coil dari WIP (id_gudang_sumber=4) boleh dihapus meskipun scan_status=1 (auto-scan)
            // Hanya coil non-WIP yang sudah discan manual yang tidak bisa dihapus
            $is_wip = (isset($detail['id_gudang_sumber']) && $detail['id_gudang_sumber'] == 4);
            if (!$is_wip) {
                return array('status' => false, 'message' => 'Coil ' . (!empty($detail['no_coil']) ? $detail['no_coil'] : $detail['kode_internal']) . ' has been scanned and cannot be deleted.');
            }
        }

        $spk_no = $header['spk_no'];
        $no_coil = !empty($detail['no_coil']) ? $detail['no_coil'] : $detail['kode_internal'];

        $this->db->trans_start();

        // Delete single detail item
        $this->db->where('id', $detail_id)->delete('tr_warehouse_request_coil_detail');

        // Check if SPK Coil has 0 items remaining -> cancel SPK Coil
        $this->check_and_cancel_empty_spkc($request_id);

        // Check remaining active SPK Coils for this spk_no
        $remaining_count = $this->db
            ->where('spk_no', $spk_no)
            ->where('status !=', 'Cancelled')
            ->where('status !=', 'Rejected')
            ->count_all_results('tr_warehouse_request_header');

        if ($remaining_count == 0) {
            $this->db->where('spk_no', $spk_no)->update('tr_spk_material_header', array(
                'status' => 'Material Requested'
            ));
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return array('status' => false, 'message' => 'Failed to delete coil item.');
        }

        return array('status' => true, 'message' => 'Coil ' . $no_coil . ' removed successfully from SPK Coil.', 'spk_no' => $spk_no);
    }

    /**
     * Add batch coils to an existing SPK Coil request_id
     *
     * @param int $request_id
     * @param array $coils Array of coil records to insert
     * @return array Array containing status (bool) and message (string)
     */
    public function add_coils_to_spkc($request_id, $coils)
    {
        $header = $this->db->where('id', $request_id)->get('tr_warehouse_request_header')->row_array();
        if (!$header) {
            return array('status' => false, 'message' => 'SPK Coil not found.');
        }

        if (in_array($header['status'], array('Material Confirmed', 'Confirmed', 'Released', 'Cancelled', 'Rejected'))) {
            return array('status' => false, 'message' => 'Cannot add coils to an SPK Coil with status: ' . $header['status']);
        }

        if (empty($coils) || !is_array($coils)) {
            return array('status' => false, 'message' => 'At least 1 coil must be selected.');
        }

        $this->db->trans_start();

        $detail_records = array();
        foreach ($coils as $coil) {
            $assigned_req_id = isset($coil['assigned_request_id']) ? $coil['assigned_request_id'] : '';
            if (!empty($assigned_req_id) && $assigned_req_id != $request_id) {
                $this->remove_coil_from_spkc($assigned_req_id, $coil['id_coil']);
                $this->check_and_cancel_empty_spkc($assigned_req_id);
            }

            $id_gudang_sumber_val = isset($coil['id_gudang_sumber']) ? (int) $coil['id_gudang_sumber'] : 0;

            $detail_records[] = array(
                'request_id'       => $request_id,
                'id_coil'          => isset($coil['id_coil']) ? $coil['id_coil'] : 0,
                'id_material'      => isset($coil['id_material']) ? $coil['id_material'] : '',
                'nm_material'      => isset($coil['nm_material']) ? $coil['nm_material'] : '',
                'kode_internal'    => isset($coil['kode_internal']) ? $coil['kode_internal'] : '',
                'no_coil'          => isset($coil['no_coil']) ? $coil['no_coil'] : '',
                'id_gudang_sumber' => $id_gudang_sumber_val,
                // Coil dari WIP (id_gudang_sumber=4) auto scan, tidak perlu scan manual
                'scan_status'      => ($id_gudang_sumber_val == 4) ? 1 : 0,
            );
        }

        if (!empty($detail_records)) {
            $this->insert_coil_details($detail_records);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return array('status' => false, 'message' => 'Failed to add coils to SPK Coil.');
        }

        return array('status' => true, 'message' => count($detail_records ?? []) . ' coils added successfully to ' . $header['spk_coil_no'] . '.', 'spk_no' => $header['spk_no']);
    }

    /**
     * Ambil daftar material yang dibutuhkan SPK dari formula BOM produk
     */
    public function get_materials_by_spk($spk_no)
    {
        $sql = "
            SELECT DISTINCT bd.id_material, m.nama as nm_material
            FROM tr_spk_material_detail d
            JOIN ms_bom_header bh ON bh.id_produk = d.id_produk_fg AND bh.is_delete = 0
            JOIN ms_bom_detail bd ON bd.id_bom = bh.id AND bd.is_delete = 0
            LEFT JOIN new_inventory_4 m ON (m.code_lv4 = bd.id_material OR m.id = bd.id_material)
            WHERE d.spk_no = ?
        ";
        return $this->db->query($sql, [$spk_no])->result_array();
    }

    /**
     * Cek status pack di gudang untuk material-material SPK
     */
    public function check_material_packs($materials_needed)
    {
        if (empty($materials_needed)) return ['unpacked_packs' => [], 'raw_packs' => []];

        $mat_ids = array_column($materials_needed ?? [], 'id_material');
        if (empty($mat_ids)) return ['unpacked_packs' => [], 'raw_packs' => []];

        $this->db->select('wsc.id_pack, wp.pack_code, wsc.id_material, wsc.status_proses, COUNT(wsc.id) as coil_count');
        $this->db->from('warehouse_stock_coil wsc');
        $this->db->join('warehouse_pack wp', 'wp.id = wsc.id_pack', 'left');
        $this->db->where_in('wsc.id_material', $mat_ids);
        $this->db->where('wsc.id_pack IS NOT NULL', null, false);
        $this->db->group_by('wsc.id_pack, wp.pack_code, wsc.id_material, wsc.status_proses');
        $rows = $this->db->get()->result_array();

        $unpacked_packs = [];
        $raw_packs = [];
        foreach ($rows as $r) {
            $pcode = !empty($r['pack_code']) ? $r['pack_code'] : ('Pack #' . $r['id_pack']);
            if ($r['status_proses'] === 'unpacked') {
                $unpacked_packs[] = $pcode;
            } else {
                $raw_packs[] = $pcode;
            }
        }

        return [
            'unpacked_packs' => array_values(array_unique(array_filter($unpacked_packs ?? []))),
            'raw_packs'      => array_values(array_unique(array_filter($raw_packs ?? []))),
        ];
    }
}
