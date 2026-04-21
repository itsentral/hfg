<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_issue_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // -------------------------------------------------------------------------
    // SPK Generation & Save
    // -------------------------------------------------------------------------

    /**
     * Generate nomor SPK: SPK-YYYYMM-XXXX
     */
    public function generate_spk_no()
    {
        $prefix = 'SPK-' . date('Ym') . '-';
        $row = $this->db->query(
            "SELECT MAX(spk_no) AS max_no FROM tr_spk_production WHERE spk_no LIKE '{$prefix}%'"
        )->row();
        $last = $row ? (int) substr($row->max_no, -4) : 0;
        return $prefix . sprintf('%04d', $last + 1);
    }

    /**
     * Simpan SPK Draft + detail coil dalam satu transaksi
     *
     * @param array $data   Header SPK (plan_no, produk_fg, nm_produk_fg, target_qty, tgl_spk, due_date, catatan, created_by)
     * @param array $coils  Array coil: [['no_coil','id_material','nm_material','no_ros','net_weight'], ...]
     * @return string|false spk_no jika berhasil, false jika gagal
     */
    public function save_spk($data, $coils = [])
    {
        $this->db->trans_start();

        $spk_no = $this->generate_spk_no();
        $this->db->insert('tr_spk_production', array_merge($data, [
            'spk_no'     => $spk_no,
            'status'     => 'Draft',
            'created_at' => date('Y-m-d H:i:s'),
        ]));

        foreach ($coils as $coil) {
            if (!empty($coil['no_coil'])) {
                $this->db->insert('tr_spk_material_detail', [
                    'spk_no'      => $spk_no,
                    'no_coil'     => $coil['no_coil'],
                    'id_material' => isset($coil['id_material']) ? $coil['id_material'] : null,
                    'nm_material' => isset($coil['nm_material']) ? $coil['nm_material'] : null,
                    'no_ros'      => isset($coil['no_ros']) ? $coil['no_ros'] : null,
                    'net_weight'  => isset($coil['net_weight']) ? $coil['net_weight'] : null,
                    'scan_status' => 'pending',
                ]);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $spk_no : false;
    }

    // -------------------------------------------------------------------------
    // SPK Getters
    // -------------------------------------------------------------------------

    /**
     * Ambil satu SPK beserta data plan
     */
    public function get_spk($spk_no)
    {
        return $this->db->select('s.*, p.tgl_plan, p.target_berat AS plan_target_berat')
            ->from('tr_spk_production s')
            ->join('tr_production_plan p', 'p.plan_no = s.plan_no', 'left')
            ->where('s.spk_no', $spk_no)
            ->get()->row();
    }

    /**
     * Ambil detail coil SPK
     */
    public function get_spk_details($spk_no)
    {
        return $this->db->get_where('tr_spk_material_detail', ['spk_no' => $spk_no])->result();
    }

    /**
     * Ambil log scan per SPK
     */
    public function get_spk_scan_log($spk_no)
    {
        return $this->db->select('l.*, u.name AS nama_user')
            ->from('tr_spk_scan_log l')
            ->join('users u', 'u.id = l.scan_user', 'left')
            ->where('l.spk_no', $spk_no)
            ->order_by('l.scan_time', 'DESC')
            ->get()->result();
    }

    // -------------------------------------------------------------------------
    // SPK Status Transitions
    // -------------------------------------------------------------------------

    /**
     * Ubah status SPK ke Released
     */
    public function release_spk($spk_no)
    {
        $spk = $this->get_spk($spk_no);
        if (!$spk) {
            return ['success' => false, 'message' => 'SPK tidak ditemukan'];
        }
        if ($spk->status !== 'Draft') {
            return ['success' => false, 'message' => 'SPK tidak dalam status Draft (status saat ini: ' . $spk->status . ')'];
        }

        $this->db->where('spk_no', $spk_no)->update('tr_spk_production', [
            'status'     => 'Released',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'spk_no' => $spk_no];
    }

    /**
     * Cek apakah semua coil sudah scan_status='scanned', jika ya ubah status SPK ke In Process
     */
    public function check_and_set_in_process($spk_no)
    {
        $total_coil = $this->db->where('spk_no', $spk_no)
            ->count_all_results('tr_spk_material_detail');

        $scanned = $this->db->where('spk_no', $spk_no)
            ->where('scan_status', 'scanned')
            ->count_all_results('tr_spk_material_detail');

        if ($total_coil > 0 && $scanned >= $total_coil) {
            $this->db->where('spk_no', $spk_no)->update('tr_spk_production', [
                'status'     => 'In Process',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return true;
        }
        return false;
    }

    // -------------------------------------------------------------------------
    // Scan Validation & Processing
    // -------------------------------------------------------------------------

    /**
     * Validasi scan barcode coil untuk issue material
     *
     * Cek:
     * 1. SPK ada dan berstatus Released
     * 2. Coil terdaftar dalam detail SPK
     * 3. Coil belum di-scan (scan_status = pending)
     *
     * @return array ['valid' => bool, 'message' => string, 'coil_data' => object|null]
     */
    public function validate_scan($no_coil, $spk_no)
    {
        // 1. Cek SPK Released
        $spk = $this->get_spk($spk_no);
        if (!$spk) {
            return ['valid' => false, 'message' => 'SPK ' . $spk_no . ' tidak ditemukan'];
        }
        if ($spk->status !== 'Released') {
            return ['valid' => false, 'message' => 'SPK ' . $spk_no . ' tidak dalam status Released (status saat ini: ' . $spk->status . ')'];
        }

        // 2. Cek coil terdaftar di detail SPK
        $coil_detail = $this->db->get_where('tr_spk_material_detail', [
            'spk_no'  => $spk_no,
            'no_coil' => $no_coil,
        ])->row();

        if (!$coil_detail) {
            return ['valid' => false, 'message' => 'Coil ' . $no_coil . ' tidak terdaftar dalam SPK ' . $spk_no];
        }

        // 3. Cek belum di-scan
        if ($coil_detail->scan_status === 'scanned') {
            return ['valid' => false, 'message' => 'Coil ' . $no_coil . ' sudah pernah di-scan dan di-issue pada SPK ini'];
        }

        return ['valid' => true, 'message' => 'OK', 'coil_data' => $coil_detail];
    }

    /**
     * Proses scan barcode coil (dalam transaksi):
     * 1. Catat tr_stock_move_prod (mutasi lokasi gudang material → gudang produksi)
     * 2. Update scan_status di tr_spk_material_detail ke 'scanned'
     * 3. Catat tr_spk_scan_log status=success
     * 4. Update tr_production_plan_coil_alloc status=issued
     * 5. Cek apakah semua coil ter-scan → set In Process
     *
     * Jika validasi gagal, tetap catat scan_log dengan status=rejected
     * TIDAK update warehouse_stock qty (hanya mutasi lokasi)
     *
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function process_scan($no_coil, $spk_no, $user_id)
    {
        $validation = $this->validate_scan($no_coil, $spk_no);

        if (!$validation['valid']) {
            // Catat scan gagal
            $this->db->insert('tr_spk_scan_log', [
                'spk_no'      => $spk_no,
                'no_coil'     => $no_coil,
                'scan_time'   => date('Y-m-d H:i:s'),
                'scan_user'   => $user_id,
                'status_scan' => 'rejected',
                'keterangan'  => $validation['message'],
            ]);
            return ['success' => false, 'message' => $validation['message'], 'data' => []];
        }

        $coil_detail = $validation['coil_data'];
        $now         = date('Y-m-d H:i:s');

        $this->db->trans_start();

        // Ambil gudang asal dari warehouse_stock berdasarkan id_material
        $from_gudang    = null;
        $nm_from_gudang = null;
        if (!empty($coil_detail->id_material)) {
            $ws = $this->db->select('ws.id_gudang, w.nm_gudang')
                ->from('warehouse_stock ws')
                ->join('warehouse w', 'w.id = ws.id_gudang', 'left')
                ->where('ws.id_material', $coil_detail->id_material)
                ->limit(1)
                ->get()->row();
            if ($ws) {
                $from_gudang    = $ws->id_gudang;
                $nm_from_gudang = $ws->nm_gudang;
            }
        }

        // Ambil gudang produksi dari config param atau cari dari tabel warehouse
        $id_gudang_produksi = get_param('id_gudang_produksi');
        $nm_to_gudang       = null;
        if ($id_gudang_produksi) {
            $wh = $this->db->get_where('warehouse', ['id' => $id_gudang_produksi])->row();
            $nm_to_gudang = $wh ? $wh->nm_gudang : null;
        } else {
            // Fallback: cari gudang dengan nama mengandung 'produksi'
            $wh = $this->db->like('nm_gudang', 'produksi')->limit(1)->get('warehouse')->row();
            if ($wh) {
                $id_gudang_produksi = $wh->id;
                $nm_to_gudang       = $wh->nm_gudang;
            }
        }

        // 1. Catat mutasi lokasi (TANPA jurnal akuntansi, TANPA update qty warehouse_stock)
        $this->db->insert('tr_stock_move_prod', [
            'spk_no'         => $spk_no,
            'no_coil'        => $no_coil,
            'id_material'    => $coil_detail->id_material,
            'from_gudang'    => $from_gudang,
            'nm_from_gudang' => $nm_from_gudang,
            'to_gudang'      => $id_gudang_produksi,
            'nm_to_gudang'   => $nm_to_gudang,
            'move_time'      => $now,
            'move_user'      => $user_id,
        ]);

        // 2. Update scan_status di tr_spk_material_detail ke 'scanned'
        $this->db->where(['spk_no' => $spk_no, 'no_coil' => $no_coil])
            ->update('tr_spk_material_detail', ['scan_status' => 'scanned']);

        // 3. Catat scan log sukses
        $this->db->insert('tr_spk_scan_log', [
            'spk_no'      => $spk_no,
            'no_coil'     => $no_coil,
            'scan_time'   => $now,
            'scan_user'   => $user_id,
            'status_scan' => 'success',
            'keterangan'  => 'Issue material berhasil ke ' . ($nm_to_gudang ?: 'gudang produksi'),
        ]);

        // 4. Update status alokasi coil ke issued
        $this->db->where('no_coil', $no_coil)
            ->update('tr_production_plan_coil_alloc', ['status_alloc' => 'issued']);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal memproses scan, silakan coba lagi', 'data' => []];
        }

        // 5. Cek apakah semua coil sudah ter-scan
        $all_scanned = $this->check_and_set_in_process($spk_no);

        // Hitung progress
        $total   = $this->db->where('spk_no', $spk_no)->count_all_results('tr_spk_material_detail');
        $scanned = $this->db->where('spk_no', $spk_no)->where('scan_status', 'scanned')->count_all_results('tr_spk_material_detail');

        return [
            'success' => true,
            'message' => 'Coil ' . $no_coil . ' berhasil di-issue ke ' . ($nm_to_gudang ?: 'area produksi'),
            'data'    => [
                'no_coil'     => $no_coil,
                'spk_no'      => $spk_no,
                'all_scanned' => $all_scanned,
                'progress'    => ['scanned' => $scanned, 'total' => $total],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Coil History & Monitoring
    // -------------------------------------------------------------------------

    /**
     * Riwayat mutasi coil dari tr_stock_move_prod
     */
    public function get_coil_history($no_coil)
    {
        return $this->db->select('m.*, s.nm_produk_fg, s.status AS status_spk, u.name AS nama_user,
                wf.nm_gudang AS nm_from_gudang_ref, wt.nm_gudang AS nm_to_gudang_ref')
            ->from('tr_stock_move_prod m')
            ->join('tr_spk_production s', 's.spk_no = m.spk_no', 'left')
            ->join('users u', 'u.id = m.move_user', 'left')
            ->join('warehouse wf', 'wf.id = m.from_gudang', 'left')
            ->join('warehouse wt', 'wt.id = m.to_gudang', 'left')
            ->where('m.no_coil', $no_coil)
            ->order_by('m.move_time', 'ASC')
            ->get()->result();
    }

    /**
     * Daftar coil yang sedang di area produksi (status issued di alloc)
     */
    public function get_coil_in_production()
    {
        $sql = "
            SELECT
                a.no_coil,
                a.plan_no,
                a.status_alloc,
                s.spk_no,
                s.nm_produk_fg,
                s.status AS status_spk,
                m.from_gudang,
                m.nm_from_gudang,
                m.to_gudang,
                m.nm_to_gudang,
                m.move_time,
                d.nm_material,
                d.net_weight
            FROM tr_production_plan_coil_alloc a
            LEFT JOIN tr_spk_production s ON s.plan_no = a.plan_no
            LEFT JOIN tr_spk_material_detail d ON d.spk_no = s.spk_no AND d.no_coil = a.no_coil
            LEFT JOIN tr_stock_move_prod m ON m.no_coil = a.no_coil AND m.spk_no = s.spk_no
            WHERE a.status_alloc = 'issued'
            ORDER BY m.move_time DESC
        ";
        return $this->db->query($sql)->result();
    }

    // -------------------------------------------------------------------------
    // DataTables
    // -------------------------------------------------------------------------

    /**
     * Query DataTables server-side untuk list SPK
     */
    public function get_list_for_datatable($params)
    {
        $search    = isset($params['search']['value']) ? $params['search']['value'] : '';
        $start     = isset($params['start']) ? (int) $params['start'] : 0;
        $length    = isset($params['length']) ? (int) $params['length'] : 10;
        $order_col = isset($params['order'][0]['column']) ? (int) $params['order'][0]['column'] : 0;
        $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';

        $cols     = ['s.spk_no', 's.tgl_spk', 's.plan_no', 's.nm_produk_fg', 's.target_qty', 's.status', 's.created_at'];
        $order_by = isset($cols[$order_col]) ? $cols[$order_col] : 's.created_at';

        $base_sql = "FROM tr_spk_production s WHERE 1=1";
        if (!empty($search)) {
            $esc = $this->db->escape_like_str($search);
            $base_sql .= " AND (s.spk_no LIKE '%{$esc}%' OR s.plan_no LIKE '%{$esc}%' OR s.nm_produk_fg LIKE '%{$esc}%' OR s.status LIKE '%{$esc}%')";
        }

        $total    = $this->db->query("SELECT COUNT(*) AS cnt {$base_sql}")->row()->cnt;
        $filtered = $total;
        $query    = $this->db->query("SELECT s.* {$base_sql} ORDER BY {$order_by} {$order_dir} LIMIT {$start},{$length}");

        return ['total' => $total, 'filtered' => $filtered, 'data' => $query->result()];
    }
}
