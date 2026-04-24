<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Bom_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ── Master Produk & Material ─────────────────────────────────────────────

    /**
     * Ambil semua produk dari product_lvl_4 untuk Select2
     * Filter: category=product, deleted_date IS NULL, status=1
     */
    public function get_produk_list()
    {
        return $this->db
            ->select('p4.code_lv4, p4.nama, p4.trade_name,
                      p1.nama AS nm_lv1, p2.nama AS nm_lv2, p3.nama AS nm_lv3')
            ->from('product_lvl_4 p4')
            ->join('product_lvl_1 p1', 'p1.code_lv1 = p4.code_lv1 AND p1.category = "product"', 'left')
            ->join('product_lvl_2 p2', 'p2.code_lv2 = p4.code_lv2 AND p2.category = "product"', 'left')
            ->join('product_lvl_3 p3', 'p3.code_lv3 = p4.code_lv3 AND p3.category = "product"', 'left')
            ->where('p4.category', 'product')
            ->where('p4.deleted_date', null)
            ->where('p4.status', 1)
            ->order_by('p4.nama', 'ASC')
            ->get()->result();
    }

    /**
     * Ambil semua material dari new_inventory_4 untuk Select2
     * Filter: category=material, deleted_date IS NULL
     */
    public function get_material_list()
    {
        return $this->db
            ->select('m4.code_lv4, m4.nama, m4.trade_name, m4.id_unit, u.nama AS nm_unit,
                      m1.nama AS nm_lv1, m2.nama AS nm_lv2, m3.nama AS nm_lv3')
            ->from('new_inventory_4 m4')
            ->join('new_inventory_1 m1', 'm1.code_lv1 = m4.code_lv1 AND m1.category = "material"', 'left')
            ->join('new_inventory_2 m2', 'm2.code_lv2 = m4.code_lv2 AND m2.category = "material"', 'left')
            ->join('new_inventory_3 m3', 'm3.code_lv3 = m4.code_lv3 AND m3.category = "material"', 'left')
            ->join('ms_satuan u', 'u.id = m4.id_unit', 'left')
            ->where('m4.category', 'material')
            ->where('m4.deleted_date', null)
            ->order_by('m4.nama', 'ASC')
            ->get()->result();
    }

    /** Ambil satu material by code_lv4 */
    public function get_material_by_code($code_lv4)
    {
        return $this->db
            ->select('m4.code_lv4, m4.nama, m4.trade_name, m4.id_unit, u.nama AS nm_unit')
            ->from('new_inventory_4 m4')
            ->join('ms_satuan u', 'u.id = m4.id_unit', 'left')
            ->where('m4.code_lv4', $code_lv4)
            ->where('m4.category', 'material')
            ->get()->row();
    }

    // ── BOM Header ───────────────────────────────────────────────────────────

    /** Ambil satu BOM by id (hanya yang belum dihapus) */
    public function get_bom($id)
    {
        return $this->db
            ->where('id', $id)
            ->where('is_delete', 0)
            ->get('ms_bom_header')->row();
    }

    /** Ambil BOM by produk (hanya yang belum dihapus) */
    public function get_bom_by_produk($id_produk)
    {
        return $this->db
            ->where('id_produk', $id_produk)
            ->where('is_delete', 0)
            ->get('ms_bom_header')->row();
    }

    /** Ambil detail BOM (hanya yang belum dihapus) */
    public function get_bom_details($id_bom)
    {
        return $this->db
            ->where('id_bom', $id_bom)
            ->where('is_delete', 0)
            ->order_by('urut', 'ASC')
            ->order_by('id', 'ASC')
            ->get('ms_bom_detail')->result();
    }

    /**
     * Simpan atau update BOM (header + detail) dalam satu transaksi
     * Detail lama di-soft-delete, bukan hard delete
     */
    public function save_bom($header, $details)
    {
        $this->db->trans_start();
        $now = date('Y-m-d H:i:s');

        $existing = $this->get_bom_by_produk($header['id_produk']);

        if ($existing) {
            $id_bom = $existing->id;
            $this->db->update('ms_bom_header', [
                'nm_produk'  => $header['nm_produk'],
                'keterangan' => isset($header['keterangan']) ? $header['keterangan'] : null,
                'updated_at' => $now,
            ], ['id' => $id_bom]);
        } else {
            $this->db->insert('ms_bom_header', [
                'id_produk'  => $header['id_produk'],
                'nm_produk'  => $header['nm_produk'],
                'keterangan' => isset($header['keterangan']) ? $header['keterangan'] : null,
                'status'     => 1,
                'is_delete'  => 0,
                'created_by' => $header['created_by'],
                'created_at' => $now,
            ]);
            $id_bom = $this->db->insert_id();
        }

        // Soft delete detail lama
        $this->db->update('ms_bom_detail', ['is_delete' => 1], ['id_bom' => $id_bom]);

        // Insert detail baru
        foreach ($details as $urut => $d) {
            if (empty($d['id_material'])) continue;
            $this->db->insert('ms_bom_detail', [
                'id_bom'      => $id_bom,
                'id_material' => $d['id_material'],
                'nm_material' => isset($d['nm_material']) ? $d['nm_material'] : null,
                'trade_name'  => isset($d['trade_name']) ? $d['trade_name'] : null,
                'qty'         => isset($d['qty']) ? (float) $d['qty'] : 1,
                'id_unit'     => isset($d['id_unit']) ? $d['id_unit'] : null,
                'nm_unit'     => isset($d['nm_unit']) ? $d['nm_unit'] : null,
                'keterangan'  => isset($d['keterangan']) ? $d['keterangan'] : null,
                'urut'        => $urut + 1,
                'is_delete'   => 0,
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $id_bom : false;
    }

    /**
     * Soft delete BOM header (dan tandai semua detail sebagai deleted)
     */
    public function delete_bom($id, $user_id = null)
    {
        $this->db->trans_start();

        $this->db->update('ms_bom_header', [
            'is_delete'  => 1,
            'deleted_by' => $user_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        $this->db->update('ms_bom_detail', ['is_delete' => 1], ['id_bom' => $id]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ── DataTables ───────────────────────────────────────────────────────────

    public function get_list_for_datatable($params)
    {
        $search    = isset($params['search']['value']) ? $params['search']['value'] : '';
        $start     = isset($params['start']) ? (int) $params['start'] : 0;
        $length    = isset($params['length']) ? (int) $params['length'] : 10;
        $order_col = isset($params['order'][0]['column']) ? (int) $params['order'][0]['column'] : 0;
        $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';

        $cols     = ['h.id', 'h.id_produk', 'h.nm_produk', 'cnt', 'h.created_at'];
        $order_by = isset($cols[$order_col]) ? $cols[$order_col] : 'h.created_at';

        // Filter is_delete = 0 dan hanya hitung detail yang aktif
        $base_sql = "FROM ms_bom_header h
                     LEFT JOIN (
                         SELECT id_bom, COUNT(*) AS cnt
                         FROM ms_bom_detail
                         WHERE is_delete = 0
                         GROUP BY id_bom
                     ) d ON d.id_bom = h.id
                     WHERE h.is_delete = 0";

        if (!empty($search)) {
            $esc = $this->db->escape_like_str($search);
            $base_sql .= " AND (h.id_produk LIKE '%{$esc}%' OR h.nm_produk LIKE '%{$esc}%')";
        }

        $total    = $this->db->query("SELECT COUNT(*) AS cnt {$base_sql}")->row()->cnt;
        $filtered = $total;
        $data     = $this->db->query(
            "SELECT h.*, COALESCE(d.cnt, 0) AS jumlah_material
             {$base_sql}
             ORDER BY {$order_by} {$order_dir}
             LIMIT {$start},{$length}"
        )->result();

        return ['total' => $total, 'filtered' => $filtered, 'data' => $data];
    }
}
