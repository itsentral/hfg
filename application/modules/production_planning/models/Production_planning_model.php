<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_planning_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate nomor plan: PP-YYYYMM-XXXX
     */
    public function generate_plan_no()
    {
        $prefix = 'PP-' . date('Ym') . '-';
        $row = $this->db->query(
            "SELECT MAX(plan_no) AS max_no FROM tr_production_plan WHERE plan_no LIKE '{$prefix}%'"
        )->row();
        $last = $row ? (int) substr($row->max_no, -4) : 0;
        return $prefix . sprintf('%04d', $last + 1);
    }

    /**
     * Ambil daftar produk FG dari master new_inventory_4
     * Digunakan untuk dropdown di form production planning
     */
    public function get_produk_fg_list()
    {
        return $this->db
            ->select('code_lv4, nama')
            ->from('new_inventory_4')
            ->order_by('nama', 'ASC')
            ->get()->result();
    }

    /**
     * Simpan plan baru, status default Draft
     */
    public function save_plan($data, $details = [])
    {
        $this->db->trans_start();

        $plan_no = $this->generate_plan_no();
        $this->db->insert('tr_production_plan', array_merge($data, [
            'plan_no'    => $plan_no,
            'status'     => 'Draft',
            'created_at' => date('Y-m-d H:i:s'),
        ]));

        foreach ($details as $detail) {
            $this->db->insert('tr_production_plan_detail', array_merge($detail, ['plan_no' => $plan_no]));
            $this->db->insert('tr_production_plan_coil_alloc', [
                'plan_no'      => $plan_no,
                'no_coil'      => $detail['no_coil'],
                'status_alloc' => 'allocated',
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $plan_no : false;
    }

    /**
     * Update plan yang masih Draft
     */
    public function update_plan($plan_no, $data, $details = [])
    {
        $this->db->trans_start();

        $this->db->where('plan_no', $plan_no)->update('tr_production_plan', $data);
        $this->db->where('plan_no', $plan_no)->delete('tr_production_plan_detail');
        $this->db->where('plan_no', $plan_no)->delete('tr_production_plan_coil_alloc');

        foreach ($details as $detail) {
            $this->db->insert('tr_production_plan_detail', array_merge($detail, ['plan_no' => $plan_no]));
            $this->db->insert('tr_production_plan_coil_alloc', [
                'plan_no'      => $plan_no,
                'no_coil'      => $detail['no_coil'],
                'status_alloc' => 'allocated',
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Ambil satu plan beserta detail
     */
    public function get_plan($plan_no)
    {
        return $this->db->get_where('tr_production_plan', ['plan_no' => $plan_no])->row();
    }

    public function get_plan_details($plan_no)
    {
        return $this->db->get_where('tr_production_plan_detail', ['plan_no' => $plan_no])->result();
    }

    public function get_coil_alloc($plan_no)
    {
        return $this->db->get_where('tr_production_plan_coil_alloc', ['plan_no' => $plan_no])->result();
    }

    /**
     * Ambil coil available di gudang material (status belum dialokasikan ke plan aktif)
     * Coil dari tr_ros_detail yang belum ada di tr_production_plan_coil_alloc dengan status allocated/issued
     */
    public function get_coil_available($id_produk_fg = null)
    {
        $sql = "
            SELECT
                d.no_coil,
                d.id_barang AS id_material,
                d.nm_barang AS nm_material,
                d.berat_kotor,
                d.berat_bersih,
                d.no_ros,
                ws.qty_stock,
                w.nm_gudang
            FROM tr_ros_detail d
            LEFT JOIN warehouse_stock ws ON ws.id_material = d.id_barang
            LEFT JOIN warehouse w ON w.id = ws.id_gudang
            LEFT JOIN tr_production_plan_coil_alloc alloc
                ON alloc.no_coil COLLATE utf8mb4_general_ci = d.no_coil COLLATE utf8mb4_general_ci
                AND alloc.status_alloc IN ('allocated','issued')
            WHERE alloc.id IS NULL
              AND d.no_coil IS NOT NULL
              AND d.no_coil != ''
              AND ws.qty_stock > 0
            ORDER BY d.no_coil ASC
        ";
        return $this->db->query($sql)->result();
    }

    /**
     * Release plan: ubah status ke Released
     * Plan yang sudah Released tidak bisa diedit
     */
    public function release_plan($plan_no)
    {
        $plan = $this->get_plan($plan_no);
        if (!$plan || $plan->status !== 'Draft') {
            return ['success' => false, 'message' => 'Plan tidak ditemukan atau status bukan Draft'];
        }

        $details = $this->get_plan_details($plan_no);
        if (empty($details)) {
            return ['success' => false, 'message' => 'Plan tidak dapat di-release karena belum ada coil yang dialokasikan'];
        }

        $this->db->trans_start();

        // Ubah status plan ke Released
        $this->db->where('plan_no', $plan_no)->update('tr_production_plan', [
            'status'     => 'Released',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Auto-generate Draft SPK dari plan ini (Requirements 1.4)
        $spk_no = $this->_generate_spk_from_plan($plan, $details);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal me-release plan'];
        }

        return ['success' => true, 'plan_no' => $plan_no, 'spk_no' => $spk_no];
    }

    /**
     * Generate Draft SPK dari plan yang di-release
     * Dipanggil dalam transaksi release_plan
     */
    private function _generate_spk_from_plan($plan, $details)
    {
        $prefix = 'SPK-' . date('Ym') . '-';
        $row = $this->db->query(
            "SELECT MAX(spk_no) AS max_no FROM tr_spk_production WHERE spk_no LIKE '{$prefix}%'"
        )->row();
        $last   = ($row && $row->max_no) ? (int) substr($row->max_no, -4) : 0;
        $spk_no = $prefix . sprintf('%04d', $last + 1);

        $this->db->insert('tr_spk_production', [
            'spk_no'       => $spk_no,
            'plan_no'      => $plan->plan_no,
            'produk_fg'    => $plan->id_produk_fg,
            'nm_produk_fg' => $plan->nm_produk_fg,
            'target_qty'   => $plan->target_qty,
            'tgl_spk'      => date('Y-m-d'),
            'due_date'     => $plan->due_date,
            'status'       => 'Draft',
            'created_by'   => $plan->created_by,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        // Salin detail coil dari plan ke SPK
        foreach ($details as $d) {
            $this->db->insert('tr_spk_material_detail', [
                'spk_no'      => $spk_no,
                'no_coil'     => $d->no_coil,
                'id_material' => $d->id_material,
                'nm_material' => $d->nm_material,
                'no_ros'      => $d->no_ros,
                'net_weight'  => $d->net_weight_coil,
                'scan_status' => 'pending',
            ]);
        }

        return $spk_no;
    }

    /**
     * Cancel plan: hanya bisa jika belum ada SPK aktif
     */
    public function cancel_plan($plan_no)
    {
        $plan = $this->get_plan($plan_no);
        if (!$plan || !in_array($plan->status, ['Draft', 'Released'])) {
            return ['success' => false, 'message' => 'Plan tidak dapat dibatalkan'];
        }

        // Cek apakah sudah ada SPK
        $spk_count = $this->db->where('plan_no', $plan_no)
            ->where_not_in('status', ['Cancelled'])
            ->count_all_results('tr_spk_production');

        if ($spk_count > 0) {
            return ['success' => false, 'message' => 'Plan tidak dapat dibatalkan karena sudah ada SPK aktif'];
        }

        $this->db->trans_start();
        $this->db->where('plan_no', $plan_no)->update('tr_production_plan', ['status' => 'Cancelled']);
        $this->db->where('plan_no', $plan_no)->update('tr_production_plan_coil_alloc', ['status_alloc' => 'cancelled']);
        $this->db->trans_complete();

        return ['success' => $this->db->trans_status()];
    }

    /**
     * Cek apakah semua SPK sudah Closed, jika ya ubah plan ke Closed
     */
    public function check_and_close_plan($plan_no)
    {
        $total = $this->db->where('plan_no', $plan_no)
            ->where_not_in('status', ['Cancelled'])
            ->count_all_results('tr_spk_production');

        $closed = $this->db->where('plan_no', $plan_no)
            ->where('status', 'Closed')
            ->count_all_results('tr_spk_production');

        if ($total > 0 && $total === $closed) {
            $this->db->where('plan_no', $plan_no)->update('tr_production_plan', ['status' => 'Closed']);
            return true;
        }
        return false;
    }

    /**
     * Query untuk DataTables server-side
     */
    public function get_list_for_datatable($params)
    {
        $search    = isset($params['search']['value']) ? $params['search']['value'] : '';
        $start     = isset($params['start']) ? (int) $params['start'] : 0;
        $length    = isset($params['length']) ? (int) $params['length'] : 10;
        $order_col = isset($params['order'][0]['column']) ? (int) $params['order'][0]['column'] : 0;
        $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';

        $cols     = ['p.plan_no', 'p.tgl_plan', 'p.nm_produk_fg', 'p.target_qty', 'p.status', 'p.created_at'];
        $order_by = isset($cols[$order_col]) ? $cols[$order_col] : 'p.created_at';

        $base_sql = "FROM tr_production_plan p WHERE 1=1";
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $base_sql .= " AND (p.plan_no LIKE '%{$s}%' OR p.nm_produk_fg LIKE '%{$s}%' OR p.status LIKE '%{$s}%')";
        }

        $total    = $this->db->query("SELECT COUNT(*) AS cnt {$base_sql}")->row()->cnt;
        $filtered = $total;
        $query    = $this->db->query("SELECT p.* {$base_sql} ORDER BY {$order_by} {$order_dir} LIMIT {$start},{$length}");

        return ['total' => $total, 'filtered' => $filtered, 'data' => $query->result()];
    }
}
