<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Fg_warehouse_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // SECTION 1: Pure Calculation Functions
    // =========================================================================

    /**
     * Hitung berat referensi FG (rata-rata tertimbang)
     * Berat_Referensi = total_berat / qty_stok
     * Hindari division by zero — return 0 jika qty_stok = 0
     *
     * @param float $total_berat Total berat stok FG dalam kg
     * @param float $qty_stok    Total qty stok FG
     * @return float
     */
    public function calculate_berat_referensi($total_berat, $qty_stok)
    {
        $qty_stok    = (float) $qty_stok;
        $total_berat = (float) $total_berat;

        if ($qty_stok <= 0) {
            return 0.0;
        }

        return round($total_berat / $qty_stok, 4);
    }

    // =========================================================================
    // SECTION 2: Stok Methods
    // =========================================================================

    /**
     * Ambil stok terkini dari fg_stock
     *
     * @param string $produk_fg
     * @return object|null
     */
    public function get_stok($produk_fg)
    {
        return $this->db->get_where('fg_stock', ['produk_fg' => $produk_fg])->row();
    }

    /**
     * Ambil semua stok FG
     *
     * @return array
     */
    public function get_all_stok()
    {
        return $this->db->order_by('produk_fg', 'ASC')->get('fg_stock')->result();
    }

    /**
     * Ambil riwayat mutasi stok dari fg_stock_ledger
     *
     * @param string $produk_fg
     * @param string $tgl_dari   Format Y-m-d
     * @param string $tgl_sampai Format Y-m-d
     * @return array
     */
    public function get_kartu_stok($produk_fg, $tgl_dari = null, $tgl_sampai = null)
    {
        $this->db->where('produk_fg', $produk_fg);

        if (!empty($tgl_dari)) {
            $this->db->where('tgl_transaksi >=', $tgl_dari);
        }
        if (!empty($tgl_sampai)) {
            $this->db->where('tgl_transaksi <=', $tgl_sampai);
        }

        return $this->db->order_by('tgl_transaksi', 'ASC')
            ->order_by('id', 'ASC')
            ->get('fg_stock_ledger')->result();
    }

    /**
     * Tambah stok FG (mutasi IN)
     * Dalam transaksi:
     * 1. Upsert fg_stock (tambah qty + berat)
     * 2. Catat fg_stock_ledger jenis IN dengan saldo terkini
     * 3. Hitung ulang berat_referensi
     * 4. Simpan riwayat ke ms_fg_weight_history
     *
     * @param string $produk_fg
     * @param string $nm_produk_fg
     * @param float  $qty
     * @param float  $berat
     * @param string $no_referensi
     * @param int    $user_id
     * @return array ['success' => bool, 'message' => string]
     */
    public function update_stok_in($produk_fg, $nm_produk_fg, $qty, $berat, $no_referensi, $user_id)
    {
        $qty   = (float) $qty;
        $berat = (float) $berat;
        $now   = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        $this->db->trans_start();

        // Ambil stok saat ini
        $stok = $this->get_stok($produk_fg);

        if ($stok) {
            $new_qty    = (float) $stok->qty_stok + $qty;
            $new_berat  = (float) $stok->total_berat + $berat;
            $berat_ref  = $this->calculate_berat_referensi($new_berat, $new_qty);

            $this->db->update('fg_stock', [
                'nm_produk_fg'    => $nm_produk_fg,
                'qty_stok'        => $new_qty,
                'total_berat'     => $new_berat,
                'berat_referensi' => $berat_ref,
                'last_update'     => $now,
            ], ['produk_fg' => $produk_fg]);
        } else {
            $new_qty   = $qty;
            $new_berat = $berat;
            $berat_ref = $this->calculate_berat_referensi($new_berat, $new_qty);

            $this->db->insert('fg_stock', [
                'produk_fg'       => $produk_fg,
                'nm_produk_fg'    => $nm_produk_fg,
                'qty_stok'        => $new_qty,
                'total_berat'     => $new_berat,
                'berat_referensi' => $berat_ref,
                'last_update'     => $now,
            ]);
        }

        // Catat ledger IN
        $this->db->insert('fg_stock_ledger', [
            'produk_fg'     => $produk_fg,
            'tgl_transaksi' => $today,
            'no_referensi'  => $no_referensi,
            'jenis_mutasi'  => 'IN',
            'qty_in'        => $qty,
            'qty_out'       => 0,
            'berat_in'      => $berat,
            'berat_out'     => 0,
            'qty_saldo'     => $new_qty,
            'berat_saldo'   => $new_berat,
            'keterangan'    => 'Penerimaan FG dari ' . $no_referensi,
            'created_at'    => $now,
        ]);

        // Simpan riwayat berat referensi
        $this->db->insert('ms_fg_weight_history', [
            'produk_fg'        => $produk_fg,
            'berat_referensi'  => $berat_ref,
            'total_qty_stok'   => $new_qty,
            'total_berat_stok' => $new_berat,
            'effective_date'   => $now,
            'created_by'       => $user_id,
        ]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal update stok IN untuk ' . $produk_fg];
        }

        return ['success' => true, 'message' => 'Stok IN berhasil diupdate'];
    }

    /**
     * Kurangi stok FG (mutasi OUT)
     * Dalam transaksi:
     * 1. Kurangi qty + berat di fg_stock
     * 2. Catat fg_stock_ledger jenis OUT
     * 3. Hitung ulang berat_referensi
     *
     * @param string $produk_fg
     * @param float  $qty
     * @param float  $berat
     * @param string $no_referensi
     * @param int    $user_id
     * @return array ['success' => bool, 'message' => string]
     */
    public function update_stok_out($produk_fg, $qty, $berat, $no_referensi, $user_id)
    {
        $qty   = (float) $qty;
        $berat = (float) $berat;
        $now   = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        $stok = $this->get_stok($produk_fg);
        if (!$stok) {
            return ['success' => false, 'message' => 'Stok produk ' . $produk_fg . ' tidak ditemukan'];
        }

        $this->db->trans_start();

        $new_qty   = max(0, (float) $stok->qty_stok - $qty);
        $new_berat = max(0, (float) $stok->total_berat - $berat);
        $berat_ref = $this->calculate_berat_referensi($new_berat, $new_qty);

        $this->db->update('fg_stock', [
            'qty_stok'        => $new_qty,
            'total_berat'     => $new_berat,
            'berat_referensi' => $berat_ref,
            'last_update'     => $now,
        ], ['produk_fg' => $produk_fg]);

        // Catat ledger OUT
        $this->db->insert('fg_stock_ledger', [
            'produk_fg'     => $produk_fg,
            'tgl_transaksi' => $today,
            'no_referensi'  => $no_referensi,
            'jenis_mutasi'  => 'OUT',
            'qty_in'        => 0,
            'qty_out'       => $qty,
            'berat_in'      => 0,
            'berat_out'     => $berat,
            'qty_saldo'     => $new_qty,
            'berat_saldo'   => $new_berat,
            'keterangan'    => 'Pengeluaran FG untuk ' . $no_referensi,
            'created_at'    => $now,
        ]);

        // Simpan riwayat berat referensi
        $this->db->insert('ms_fg_weight_history', [
            'produk_fg'        => $produk_fg,
            'berat_referensi'  => $berat_ref,
            'total_qty_stok'   => $new_qty,
            'total_berat_stok' => $new_berat,
            'effective_date'   => $now,
            'created_by'       => $user_id,
        ]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal update stok OUT untuk ' . $produk_fg];
        }

        return ['success' => true, 'message' => 'Stok OUT berhasil diupdate'];
    }

    // =========================================================================
    // SECTION 3: Receipt Methods
    // =========================================================================

    /**
     * Ambil satu FG Receipt beserta info user
     *
     * @param string $fg_receipt_no
     * @return object|null
     */
    public function get_receipt($fg_receipt_no)
    {
        return $this->db
            ->select('r.*, u.nm_lengkap AS nama_created_by, p.nm_lengkap AS nama_posted_by')
            ->from('tr_fg_receipt r')
            ->join('users u', 'u.id_user = r.created_by', 'left')
            ->join('users p', 'p.id_user = r.posted_by', 'left')
            ->where('r.fg_receipt_no', $fg_receipt_no)
            ->get()->row();
    }

    /**
     * Query DataTables server-side untuk list FG Receipt
     *
     * @param array $params $_REQUEST dari DataTables
     * @return array ['total' => int, 'filtered' => int, 'data' => array]
     */
    public function get_receipt_list_for_datatable($params)
    {
        $search    = isset($params['search']['value']) ? $params['search']['value'] : '';
        $start     = isset($params['start']) ? (int) $params['start'] : 0;
        $length    = isset($params['length']) ? (int) $params['length'] : 10;
        $order_col = isset($params['order'][0]['column']) ? (int) $params['order'][0]['column'] : 0;
        $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';

        $cols     = ['r.fg_receipt_no', 'r.created_at', 'r.report_no', 'r.spk_no', 'r.produk_fg', 'r.status'];
        $order_by = isset($cols[$order_col]) ? $cols[$order_col] : 'r.created_at';

        $base_sql = "FROM tr_fg_receipt r
                     LEFT JOIN users u ON u.id_user = r.created_by
                     WHERE 1=1";

        if (!empty($search)) {
            $esc = $this->db->escape_like_str($search);
            $base_sql .= " AND (r.fg_receipt_no LIKE '%{$esc}%'
                            OR r.report_no LIKE '%{$esc}%'
                            OR r.spk_no LIKE '%{$esc}%'
                            OR r.produk_fg LIKE '%{$esc}%'
                            OR r.nm_produk_fg LIKE '%{$esc}%'
                            OR r.status LIKE '%{$esc}%')";
        }

        $total    = $this->db->query("SELECT COUNT(*) AS cnt {$base_sql}")->row()->cnt;
        $filtered = $total;
        $query    = $this->db->query(
            "SELECT r.*, u.nm_lengkap AS nama_created_by {$base_sql}
             ORDER BY {$order_by} {$order_dir}
             LIMIT {$start},{$length}"
        );

        return ['total' => $total, 'filtered' => $filtered, 'data' => $query->result()];
    }

    /**
     * Posting FG Receipt
     * Dalam transaksi:
     * 1. Update status ke 'Posted'
     * 2. Panggil update_stok_in() untuk FG
     * 3. Panggil update_stok_in() untuk KW2 Internal (jika ada)
     * 4. Panggil update_stok_in() untuk KW2 Supplier (jika ada)
     * 5. Update posted_by dan posted_at
     *
     * @param string $fg_receipt_no
     * @param int    $user_id
     * @return array ['success' => bool, 'message' => string]
     */
    public function post_receipt($fg_receipt_no, $user_id)
    {
        $receipt = $this->get_receipt($fg_receipt_no);
        if (!$receipt) {
            return ['success' => false, 'message' => 'FG Receipt tidak ditemukan'];
        }
        if ($receipt->status !== 'Draft') {
            return ['success' => false, 'message' => 'FG Receipt hanya bisa diposting dari status Draft'];
        }

        $now = date('Y-m-d H:i:s');

        // Update status dulu
        $this->db->update('tr_fg_receipt', [
            'status'    => 'Posted',
            'posted_by' => $user_id,
            'posted_at' => $now,
        ], ['fg_receipt_no' => $fg_receipt_no]);

        // Update stok FG
        if ((float) $receipt->fg_qty > 0) {
            $result = $this->update_stok_in(
                $receipt->produk_fg,
                $receipt->nm_produk_fg,
                (float) $receipt->fg_qty,
                (float) $receipt->fg_kg,
                $fg_receipt_no,
                $user_id
            );
            if (!$result['success']) {
                // Rollback status
                $this->db->update('tr_fg_receipt', [
                    'status'    => 'Draft',
                    'posted_by' => null,
                    'posted_at' => null,
                ], ['fg_receipt_no' => $fg_receipt_no]);
                return ['success' => false, 'message' => 'Gagal update stok FG: ' . $result['message']];
            }
        }

        // Update stok KW2 Internal (kode produk: produk_fg + '-KW2INT')
        if ((float) $receipt->kw2_internal_qty > 0) {
            $kw2_int_kode = $receipt->produk_fg . '-KW2INT';
            $kw2_int_nama = ($receipt->nm_produk_fg ? $receipt->nm_produk_fg : $receipt->produk_fg) . ' KW2 Internal';
            $this->update_stok_in(
                $kw2_int_kode,
                $kw2_int_nama,
                (float) $receipt->kw2_internal_qty,
                (float) $receipt->kw2_internal_kg,
                $fg_receipt_no,
                $user_id
            );
        }

        // Update stok KW2 Supplier (kode produk: produk_fg + '-KW2SUP')
        if ((float) $receipt->kw2_supplier_qty > 0) {
            $kw2_sup_kode = $receipt->produk_fg . '-KW2SUP';
            $kw2_sup_nama = ($receipt->nm_produk_fg ? $receipt->nm_produk_fg : $receipt->produk_fg) . ' KW2 Supplier';
            $this->update_stok_in(
                $kw2_sup_kode,
                $kw2_sup_nama,
                (float) $receipt->kw2_supplier_qty,
                (float) $receipt->kw2_supplier_kg,
                $fg_receipt_no,
                $user_id
            );
        }

        return ['success' => true, 'message' => 'FG Receipt ' . $fg_receipt_no . ' berhasil diposting'];
    }

    /**
     * Cancel FG Receipt
     * Dalam transaksi:
     * 1. Update status ke 'Cancelled'
     * 2. Panggil update_stok_out() untuk reverse stok FG
     * 3. Reverse stok KW2 jika ada
     *
     * @param string $fg_receipt_no
     * @param int    $user_id
     * @return array ['success' => bool, 'message' => string]
     */
    public function cancel_receipt($fg_receipt_no, $user_id)
    {
        $receipt = $this->get_receipt($fg_receipt_no);
        if (!$receipt) {
            return ['success' => false, 'message' => 'FG Receipt tidak ditemukan'];
        }
        if ($receipt->status !== 'Posted') {
            return ['success' => false, 'message' => 'Hanya FG Receipt berstatus Posted yang bisa di-cancel'];
        }

        // Update status
        $this->db->update('tr_fg_receipt', [
            'status' => 'Cancelled',
        ], ['fg_receipt_no' => $fg_receipt_no]);

        // Reverse stok FG
        if ((float) $receipt->fg_qty > 0) {
            $this->update_stok_out(
                $receipt->produk_fg,
                (float) $receipt->fg_qty,
                (float) $receipt->fg_kg,
                'CANCEL-' . $fg_receipt_no,
                $user_id
            );
        }

        // Reverse stok KW2 Internal
        if ((float) $receipt->kw2_internal_qty > 0) {
            $kw2_int_kode = $receipt->produk_fg . '-KW2INT';
            $this->update_stok_out(
                $kw2_int_kode,
                (float) $receipt->kw2_internal_qty,
                (float) $receipt->kw2_internal_kg,
                'CANCEL-' . $fg_receipt_no,
                $user_id
            );
        }

        // Reverse stok KW2 Supplier
        if ((float) $receipt->kw2_supplier_qty > 0) {
            $kw2_sup_kode = $receipt->produk_fg . '-KW2SUP';
            $this->update_stok_out(
                $kw2_sup_kode,
                (float) $receipt->kw2_supplier_qty,
                (float) $receipt->kw2_supplier_kg,
                'CANCEL-' . $fg_receipt_no,
                $user_id
            );
        }

        return ['success' => true, 'message' => 'FG Receipt ' . $fg_receipt_no . ' berhasil di-cancel dan stok telah di-reverse'];
    }
}
