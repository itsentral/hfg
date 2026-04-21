<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Delivery_fg_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Fg_warehouse/Fg_warehouse_model');
        $this->load->helper('config_param');
    }

    // =========================================================================
    // SECTION 1: Kalkulasi
    // =========================================================================

    /**
     * Generate nomor DO: DO-YYYYMM-XXXX
     *
     * @return string
     */
    public function generate_do_no()
    {
        $prefix = 'DO-' . date('Ym') . '-';
        $last   = $this->db
            ->like('do_no', $prefix, 'after')
            ->order_by('do_no', 'DESC')
            ->limit(1)
            ->get('tr_delivery_order')
            ->row();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last->do_no);
            $seq   = (int) end($parts) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung estimasi berat: qty_kirim × berat_referensi
     *
     * @param float $qty_kirim
     * @param float $berat_referensi
     * @return float
     */
    public function calculate_estimasi_berat($qty_kirim, $berat_referensi)
    {
        return (float) $qty_kirim * (float) $berat_referensi;
    }

    /**
     * Hitung selisih timbang aktual vs estimasi
     *
     * @param float $berat_aktual
     * @param float $estimasi_berat
     * @return array ['selisih_kg' => float, 'selisih_pct' => float]
     */
    public function calculate_selisih_timbang($berat_aktual, $estimasi_berat)
    {
        $berat_aktual   = (float) $berat_aktual;
        $estimasi_berat = (float) $estimasi_berat;

        $selisih_kg  = $berat_aktual - $estimasi_berat;
        $selisih_pct = ($estimasi_berat > 0)
            ? abs($selisih_kg) / $estimasi_berat
            : 0;

        return [
            'selisih_kg'  => $selisih_kg,
            'selisih_pct' => $selisih_pct,
        ];
    }

    /**
     * Validasi stok: cek qty_kirim tidak melebihi qty_stok di fg_stock
     *
     * @param string $produk_fg
     * @param float  $qty_kirim
     * @return array ['valid' => bool, 'message' => string, 'stok' => object|null]
     */
    public function validate_stok($produk_fg, $qty_kirim)
    {
        $stok = $this->db->get_where('fg_stock', ['produk_fg' => $produk_fg])->row();

        if (!$stok) {
            return [
                'valid'   => false,
                'message' => 'Produk FG ' . $produk_fg . ' tidak ditemukan di stok',
                'stok'    => null,
            ];
        }

        if ((float) $qty_kirim > (float) $stok->qty_stok) {
            return [
                'valid'   => false,
                'message' => 'Qty kirim (' . $qty_kirim . ') melebihi stok tersedia (' . $stok->qty_stok . ') untuk ' . $produk_fg,
                'stok'    => $stok,
            ];
        }

        return [
            'valid'   => true,
            'message' => 'Stok tersedia',
            'stok'    => $stok,
        ];
    }

    // =========================================================================
    // SECTION 2: Simpan DO
    // =========================================================================

    /**
     * Simpan DO Draft + detail dalam transaksi, validasi stok tiap item
     *
     * @param array $data   Header DO: customer, tgl_delivery, keterangan, created_by
     * @param array $details Array of: produk_fg, nm_produk_fg, qty_kirim, berat_referensi
     * @return array ['success' => bool, 'message' => string, 'do_no' => string]
     */
    public function save_do($data, $details)
    {
        if (empty($details)) {
            return ['success' => false, 'message' => 'Detail item DO tidak boleh kosong'];
        }

        // Validasi stok semua item sebelum transaksi
        foreach ($details as $item) {
            $check = $this->validate_stok($item['produk_fg'], $item['qty_kirim']);
            if (!$check['valid']) {
                return ['success' => false, 'message' => $check['message']];
            }
        }

        $do_no = $this->generate_do_no();
        $now   = date('Y-m-d H:i:s');

        $this->db->trans_start();

        // Simpan header
        $this->db->insert('tr_delivery_order', [
            'do_no'        => $do_no,
            'customer'     => $data['customer'],
            'tgl_delivery' => $data['tgl_delivery'],
            'keterangan'   => isset($data['keterangan']) ? $data['keterangan'] : null,
            'status'       => 'Draft',
            'created_by'   => $data['created_by'],
            'created_at'   => $now,
        ]);

        // Simpan detail
        foreach ($details as $item) {
            $estimasi = $this->calculate_estimasi_berat($item['qty_kirim'], $item['berat_referensi']);
            $this->db->insert('tr_delivery_detail', [
                'do_no'           => $do_no,
                'produk_fg'       => $item['produk_fg'],
                'nm_produk_fg'    => isset($item['nm_produk_fg']) ? $item['nm_produk_fg'] : null,
                'qty_kirim'       => $item['qty_kirim'],
                'berat_referensi' => $item['berat_referensi'],
                'estimasi_berat'  => $estimasi,
            ]);
        }

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal menyimpan Delivery Order'];
        }

        return ['success' => true, 'message' => 'DO ' . $do_no . ' berhasil dibuat', 'do_no' => $do_no];
    }

    /**
     * Update DO Draft (header + detail)
     *
     * @param string $do_no
     * @param array  $data
     * @param array  $details
     * @return array
     */
    public function update_do($do_no, $data, $details)
    {
        $do = $this->get_do($do_no);
        if (!$do || $do->status !== 'Draft') {
            return ['success' => false, 'message' => 'DO tidak ditemukan atau bukan status Draft'];
        }

        if (empty($details)) {
            return ['success' => false, 'message' => 'Detail item DO tidak boleh kosong'];
        }

        foreach ($details as $item) {
            $check = $this->validate_stok($item['produk_fg'], $item['qty_kirim']);
            if (!$check['valid']) {
                return ['success' => false, 'message' => $check['message']];
            }
        }

        $this->db->trans_start();

        $this->db->update('tr_delivery_order', [
            'customer'     => $data['customer'],
            'tgl_delivery' => $data['tgl_delivery'],
            'keterangan'   => isset($data['keterangan']) ? $data['keterangan'] : null,
        ], ['do_no' => $do_no]);

        // Hapus detail lama, insert baru
        $this->db->delete('tr_delivery_detail', ['do_no' => $do_no]);

        foreach ($details as $item) {
            $estimasi = $this->calculate_estimasi_berat($item['qty_kirim'], $item['berat_referensi']);
            $this->db->insert('tr_delivery_detail', [
                'do_no'           => $do_no,
                'produk_fg'       => $item['produk_fg'],
                'nm_produk_fg'    => isset($item['nm_produk_fg']) ? $item['nm_produk_fg'] : null,
                'qty_kirim'       => $item['qty_kirim'],
                'berat_referensi' => $item['berat_referensi'],
                'estimasi_berat'  => $estimasi,
            ]);
        }

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal mengupdate Delivery Order'];
        }

        return ['success' => true, 'message' => 'DO ' . $do_no . ' berhasil diupdate', 'do_no' => $do_no];
    }

    // =========================================================================
    // SECTION 3: Timbang Aktual
    // =========================================================================

    /**
     * Simpan berat aktual, hitung selisih, ubah status DO
     *
     * @param string $do_no
     * @param float  $berat_aktual
     * @param int    $user_id
     * @param string $keterangan
     * @return array
     */
    public function save_timbang($do_no, $berat_aktual, $user_id, $keterangan = '')
    {
        $do = $this->get_do($do_no);
        if (!$do) {
            return ['success' => false, 'message' => 'DO tidak ditemukan'];
        }
        if (!in_array($do->status, ['Draft', 'Waiting Approval'])) {
            return ['success' => false, 'message' => 'Timbang hanya bisa dilakukan pada status Draft atau Waiting Approval'];
        }

        // Hitung total estimasi berat dari semua detail
        $details = $this->get_do_details($do_no);
        $total_estimasi = 0;
        foreach ($details as $d) {
            $total_estimasi += (float) $d->estimasi_berat;
        }

        $selisih    = $this->calculate_selisih_timbang($berat_aktual, $total_estimasi);
        $toleransi  = (float) get_param('toleransi_selisih_kirim_pct', 0.03);
        $now        = date('Y-m-d H:i:s');

        $new_status = ($selisih['selisih_pct'] > $toleransi)
            ? 'Waiting Approval'
            : 'Approved Exception';

        $this->db->trans_start();

        $this->db->insert('tr_delivery_weight_log', [
            'do_no'        => $do_no,
            'berat_aktual' => $berat_aktual,
            'tgl_timbang'  => $now,
            'user_timbang' => $user_id,
            'selisih_kg'   => $selisih['selisih_kg'],
            'selisih_pct'  => $selisih['selisih_pct'],
            'keterangan'   => $keterangan,
        ]);

        $update_data = ['status' => $new_status];
        if ($new_status === 'Approved Exception') {
            $update_data['approved_at'] = $now;
        }
        $this->db->update('tr_delivery_order', $update_data, ['do_no' => $do_no]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal menyimpan data timbang'];
        }

        if ($new_status === 'Waiting Approval') {
            $this->send_notification_approval($do_no);
        }

        return [
            'success'       => true,
            'message'       => 'Timbang berhasil disimpan. Status DO: ' . $new_status,
            'new_status'    => $new_status,
            'selisih_kg'    => $selisih['selisih_kg'],
            'selisih_pct'   => $selisih['selisih_pct'],
            'toleransi'     => $toleransi,
        ];
    }

    // =========================================================================
    // SECTION 4: Getter Methods
    // =========================================================================

    /**
     * Ambil satu DO beserta info user
     *
     * @param string $do_no
     * @return object|null
     */
    public function get_do($do_no)
    {
        return $this->db
            ->select('d.*, u.nama as nama_created_by, ua.nama as nama_approved_by')
            ->from('tr_delivery_order d')
            ->join('ms_users u', 'u.id = d.created_by', 'left')
            ->join('ms_users ua', 'ua.id = d.approved_by', 'left')
            ->where('d.do_no', $do_no)
            ->get()
            ->row();
    }

    /**
     * Ambil detail item DO
     *
     * @param string $do_no
     * @return array
     */
    public function get_do_details($do_no)
    {
        return $this->db
            ->get_where('tr_delivery_detail', ['do_no' => $do_no])
            ->result();
    }

    /**
     * Ambil log timbang DO
     *
     * @param string $do_no
     * @return array
     */
    public function get_do_weight_log($do_no)
    {
        return $this->db
            ->select('wl.*, u.nama as nama_user')
            ->from('tr_delivery_weight_log wl')
            ->join('ms_users u', 'u.id = wl.user_timbang', 'left')
            ->where('wl.do_no', $do_no)
            ->order_by('wl.tgl_timbang', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Ambil riwayat approval DO
     *
     * @param string $do_no
     * @return array
     */
    public function get_do_approval_log($do_no)
    {
        return $this->db
            ->select('a.*, u.nama as nama_approver')
            ->from('tr_delivery_approval a')
            ->join('ms_users u', 'u.id = a.approver_id', 'left')
            ->where('a.do_no', $do_no)
            ->order_by('a.tgl_approval', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Query DataTables server-side
     *
     * @param array $params $_REQUEST dari DataTables
     * @return array ['data' => array, 'total' => int, 'filtered' => int]
     */
    public function get_list_for_datatable($params)
    {
        $search = isset($params['search']['value']) ? $params['search']['value'] : '';
        $start  = isset($params['start']) ? (int) $params['start'] : 0;
        $length = isset($params['length']) ? (int) $params['length'] : 10;

        $base = $this->db
            ->select('d.do_no, d.customer, d.tgl_delivery, d.status, d.created_at, u.nama as nama_created_by')
            ->from('tr_delivery_order d')
            ->join('ms_users u', 'u.id = d.created_by', 'left');

        if ($search) {
            $base->group_start()
                ->like('d.do_no', $search)
                ->or_like('d.customer', $search)
                ->or_like('d.status', $search)
                ->group_end();
        }

        $total    = $this->db->from('tr_delivery_order')->count_all_results();
        $filtered = clone $this->db;

        // Re-build for filtered count
        $count_query = $this->db
            ->select('COUNT(*) as cnt')
            ->from('tr_delivery_order d')
            ->join('ms_users u', 'u.id = d.created_by', 'left');
        if ($search) {
            $count_query->group_start()
                ->like('d.do_no', $search)
                ->or_like('d.customer', $search)
                ->or_like('d.status', $search)
                ->group_end();
        }
        $filtered_count = $count_query->get()->row()->cnt;

        // Data query
        $data_query = $this->db
            ->select('d.do_no, d.customer, d.tgl_delivery, d.status, d.created_at, u.nama as nama_created_by')
            ->from('tr_delivery_order d')
            ->join('ms_users u', 'u.id = d.created_by', 'left');
        if ($search) {
            $data_query->group_start()
                ->like('d.do_no', $search)
                ->or_like('d.customer', $search)
                ->or_like('d.status', $search)
                ->group_end();
        }
        $data = $data_query
            ->order_by('d.created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->result();

        return [
            'data'     => $data,
            'total'    => $total,
            'filtered' => (int) $filtered_count,
        ];
    }

    // =========================================================================
    // SECTION 5: Approval dan Shipping
    // =========================================================================

    /**
     * Approve DO exception (cek self-approval)
     *
     * @param string $do_no
     * @param int    $approver_id
     * @param string $alasan
     * @return array
     */
    public function approve_do($do_no, $approver_id, $alasan = '')
    {
        $do = $this->get_do($do_no);
        if (!$do) {
            return ['success' => false, 'message' => 'DO tidak ditemukan'];
        }
        if ($do->status !== 'Waiting Approval') {
            return ['success' => false, 'message' => 'DO tidak dalam status Waiting Approval'];
        }
        if ((int) $approver_id === (int) $do->created_by) {
            return ['success' => false, 'message' => 'Self-approval tidak diizinkan. Approver tidak boleh sama dengan pembuat DO'];
        }

        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();

        $this->db->update('tr_delivery_order', [
            'status'      => 'Approved Exception',
            'approved_by' => $approver_id,
            'approved_at' => $now,
        ], ['do_no' => $do_no]);

        $this->db->insert('tr_delivery_approval', [
            'do_no'        => $do_no,
            'approver_id'  => $approver_id,
            'action'       => 'Approved',
            'alasan'       => $alasan,
            'tgl_approval' => $now,
        ]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal menyimpan approval'];
        }

        return ['success' => true, 'message' => 'DO ' . $do_no . ' berhasil diapprove'];
    }

    /**
     * Reject DO, kembalikan ke Draft
     *
     * @param string $do_no
     * @param int    $approver_id
     * @param string $alasan
     * @return array
     */
    public function reject_do($do_no, $approver_id, $alasan)
    {
        $do = $this->get_do($do_no);
        if (!$do) {
            return ['success' => false, 'message' => 'DO tidak ditemukan'];
        }
        if ($do->status !== 'Waiting Approval') {
            return ['success' => false, 'message' => 'DO tidak dalam status Waiting Approval'];
        }
        if (empty($alasan)) {
            return ['success' => false, 'message' => 'Alasan reject wajib diisi'];
        }

        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();

        $this->db->update('tr_delivery_order', [
            'status' => 'Draft',
        ], ['do_no' => $do_no]);

        $this->db->insert('tr_delivery_approval', [
            'do_no'        => $do_no,
            'approver_id'  => $approver_id,
            'action'       => 'Rejected',
            'alasan'       => $alasan,
            'tgl_approval' => $now,
        ]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal menyimpan reject'];
        }

        return ['success' => true, 'message' => 'DO ' . $do_no . ' ditolak dan dikembalikan ke Draft'];
    }

    /**
     * Ship DO: ubah status ke Shipped, kurangi stok FG via Fg_warehouse_model
     *
     * @param string $do_no
     * @param int    $user_id
     * @return array
     */
    public function ship_do($do_no, $user_id)
    {
        $do = $this->get_do($do_no);
        if (!$do) {
            return ['success' => false, 'message' => 'DO tidak ditemukan'];
        }
        if ($do->status !== 'Approved Exception') {
            return ['success' => false, 'message' => 'DO harus berstatus Approved Exception sebelum di-ship'];
        }

        $details = $this->get_do_details($do_no);
        if (empty($details)) {
            return ['success' => false, 'message' => 'Detail DO kosong'];
        }

        $now = date('Y-m-d H:i:s');

        // Validasi stok semua item sebelum transaksi
        foreach ($details as $item) {
            $check = $this->validate_stok($item->produk_fg, $item->qty_kirim);
            if (!$check['valid']) {
                return ['success' => false, 'message' => 'Stok tidak mencukupi: ' . $check['message']];
            }
        }

        $this->db->trans_start();

        $this->db->update('tr_delivery_order', [
            'status' => 'Shipped',
        ], ['do_no' => $do_no]);

        // Kurangi stok FG untuk setiap item dalam satu transaksi
        foreach ($details as $item) {
            $result = $this->Fg_warehouse_model->update_stok_out(
                $item->produk_fg,
                $item->qty_kirim,
                $item->estimasi_berat,
                $do_no,
                $user_id
            );
            if (!$result['success']) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Gagal kurangi stok: ' . $result['message']];
            }
        }

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal mengupdate status DO'];
        }

        return ['success' => true, 'message' => 'DO ' . $do_no . ' berhasil di-ship. Stok FG telah dikurangi'];
    }

    /**
     * Cancel DO (hanya jika belum Shipped)
     *
     * @param string $do_no
     * @param int    $user_id
     * @return array
     */
    public function cancel_do($do_no, $user_id)
    {
        $do = $this->get_do($do_no);
        if (!$do) {
            return ['success' => false, 'message' => 'DO tidak ditemukan'];
        }
        if ($do->status === 'Shipped') {
            return ['success' => false, 'message' => 'DO yang sudah Shipped tidak bisa dibatalkan'];
        }
        if ($do->status === 'Cancelled') {
            return ['success' => false, 'message' => 'DO sudah berstatus Cancelled'];
        }

        $this->db->update('tr_delivery_order', [
            'status' => 'Cancelled',
        ], ['do_no' => $do_no]);

        return ['success' => true, 'message' => 'DO ' . $do_no . ' berhasil dibatalkan'];
    }

    /**
     * Kirim notifikasi approval ke manager dari ms_config_param key manager_user_ids
     *
     * @param string $do_no
     */
    public function send_notification_approval($do_no)
    {
        $manager_ids_raw = get_param('manager_user_ids', '');
        if (empty($manager_ids_raw)) {
            return;
        }

        $manager_ids = array_filter(array_map('trim', explode(',', $manager_ids_raw)));
        if (empty($manager_ids)) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        foreach ($manager_ids as $uid) {
            $this->db->insert('ms_notification', [
                'user_id'      => (int) $uid,
                'judul'        => 'Approval Delivery Order Diperlukan',
                'pesan'        => 'Delivery Order ' . $do_no . ' memerlukan approval karena selisih berat melebihi toleransi.',
                'no_referensi' => $do_no,
                'modul'        => 'delivery_fg',
                'is_read'      => 0,
                'created_at'   => $now,
            ]);
        }
    }
}
