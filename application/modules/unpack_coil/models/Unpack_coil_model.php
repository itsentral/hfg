<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Unpack_coil_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ===============================================================
    // LIST & DATATABLES
    // ===============================================================

    public function get_unpack_list($search, $start, $length, $order_by, $order_dir)
    {
        $this->_build_list_query($search);
        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);
        return $this->db->get()->result_array();
    }

    public function count_unpack_filtered($search)
    {
        $this->_build_list_query($search);
        return $this->db->count_all_results();
    }

    private function _build_list_query($search)
    {
        $this->db->select("h.*,
            (SELECT COUNT(*) FROM tr_unpack_coil_material m
                WHERE m.unpack_no = h.unpack_no AND m.is_delete = 0) AS material_count,
            (SELECT COALESCE(SUM(m.jumlah_coil_roll),0) FROM tr_unpack_coil_material m
                WHERE m.unpack_no = h.unpack_no AND m.is_delete = 0) AS roll_count", FALSE);
        $this->db->from('tr_unpack_coil_header h');
        $this->db->where('h.is_delete', 0);

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('h.unpack_no', $search);
            $this->db->or_like('h.pack_code', $search);
            $this->db->group_end();
        }
    }

    // ===============================================================
    // SUMBER PACK: pack yang sudah "Material Confirmed" (untuk select2)
    // 1 baris per pack (unik). Exclude pack yang sudah di-unpack (header aktif).
    // ===============================================================

    public function get_confirmed_packs($search = '')
    {
        $params = array();
        $like = '';
        if (!empty($search)) {
            $like = " AND (wrcd.pack_code LIKE ? OR wrh.spk_coil_no LIKE ?)";
            $s = '%' . $search . '%';
            $params = array($s, $s);
        }

        $sql = "
            SELECT
                wrcd.pack_code,
                wrcd.id_pack,
                MIN(wrh.id)                 AS request_id,
                COUNT(DISTINCT wsc.id)      AS coil_count,
                COUNT(DISTINCT wsc.id_material) AS material_count
            FROM tr_warehouse_request_coil_detail wrcd
            JOIN tr_warehouse_request_header wrh ON wrh.id = wrcd.request_id
            JOIN warehouse_stock_coil wsc ON wsc.id = wrcd.id_coil
            WHERE wrh.status = 'Material Confirmed'
              AND wsc.status = 1
              AND wsc.status_proses = 'in_transit'
              AND NOT EXISTS (
                  SELECT 1 FROM tr_unpack_coil_header uh
                  WHERE uh.id_pack = wrcd.id_pack
                    AND uh.is_delete = 0
              )
              {$like}
            GROUP BY wrcd.pack_code, wrcd.id_pack
            ORDER BY wrcd.pack_code ASC
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Ambil semua material (coil induk) dalam sebuah pack confirmed.
     * 1 baris per coil induk (= 1 material dalam pack).
     * Menyertakan jumlah baby coil default & agregat berat baby coil (bila sudah ada di DB).
     */
    public function get_materials_in_pack($id_pack)
    {
        $sql = "
        SELECT
            wsc.id_material,
            wsc.id_gudang,
            wsc.kd_gudang,
            MAX(wsc.nm_material)  AS nm_material,
            MAX(wsc.trade_name)   AS trade_name,
            COUNT(*)              AS jumlah_coil,
            GROUP_CONCAT(DISTINCT wsc.no_coil ORDER BY wsc.no_coil SEPARATOR ', ') AS no_coil,
            MIN(wsc.kode_internal) AS kode_internal_sample,
            SUM(wsc.net_weight)   AS net_weight_pl,
            SUM(wsc.gross_weight) AS gross_weight_pl
        FROM warehouse_stock_coil wsc
        WHERE wsc.id_pack = ?
          AND wsc.status = 1
          AND wsc.status_proses = 'in_transit'
        GROUP BY wsc.id_material, wsc.id_gudang
        ORDER BY nm_material ASC
        ";
        return $this->db->query($sql, array($id_pack))->result_array();
    }

    // Detail fisik per coil dalam 1 grup material (untuk modal View Detail)
    public function get_coils_by_material($id_pack, $id_material, $id_gudang)
    {
        $sql = "
        SELECT
        wsc.id,
        wsc.id_material,
        wsc.id_pack,
        wsc.nm_material,
        wsc.trade_name,
        wsc.id_gudang,
        wsc.kd_gudang,
        wsc.no_coil,
        wsc.no_po,
        wsc.no_ros,
        wsc.kode_internal,
        wsc.length,
        wsc.qty,
        wsc.net_weight   AS net_weight_pl,
        wsc.gross_weight AS gross_weight_pl,
        wsc.qty_roll,
        wsc.harga_beli,
        wsc.total_nilai
        FROM warehouse_stock_coil wsc
        WHERE wsc.id_pack = ?
        AND wsc.id_material = ?
        AND wsc.id_gudang = ?
        AND wsc.status = 1
        AND wsc.status_proses = 'in_transit'
        ORDER BY wsc.id ASC
        ";
        return $this->db->query($sql, array($id_pack, $id_material, $id_gudang))->result_array();
    }

    public function get_coil_induk($id_coil_induk)
    {
        return $this->db->query(
            "SELECT * FROM warehouse_stock_coil WHERE id = ? LIMIT 1",
            array($id_coil_induk)
        )->row_array();
    }

    /**
     * Baby coil existing untuk 1 coil induk (default saat pertama membuka detail
     * atau saat render mode edit dari warehouse_stock_coil).
     */
    public function get_existing_baby_coils($id_coil_induk)
    {
        return $this->db->query("
            SELECT id, no_coil, kode_internal AS babycoil_code, net_weight, gross_weight, harga_beli, total_nilai
            FROM warehouse_stock_coil
            WHERE parent_coil_id = ? AND status = 1
            ORDER BY id ASC
        ", array($id_coil_induk))->result_array();
    }

    // ===============================================================
    // NOMOR TRANSAKSI
    // ===============================================================

    public function generate_unpack_no()
    {
        $prefix = 'UNPK-' . date('Ym') . '-';

        $sql = "SELECT unpack_no FROM tr_unpack_coil_header
                WHERE unpack_no LIKE ?
                ORDER BY unpack_no DESC
                LIMIT 1
                FOR UPDATE";
        $last = $this->db->query($sql, array($prefix . '%'))->row();

        $next = 1;
        if ($last) {
            $parts = explode('-', $last->unpack_no);
            $next = (int) end($parts) + 1;
        }

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // ===============================================================
    // HEADER / MATERIAL / BABY (tabel modul)
    // ===============================================================

    public function insert_header($data)
    {
        $this->db->insert('tr_unpack_coil_header', $data);
        return $data['unpack_no'];
    }

    public function update_header($unpack_no, $data)
    {
        $this->db->where('unpack_no', $unpack_no)->update('tr_unpack_coil_header', $data);
    }

    public function insert_material($data)
    {
        $this->db->insert('tr_unpack_coil_material', $data);
        return $this->db->insert_id();
    }

    public function insert_baby($data)
    {
        $this->db->insert('tr_unpack_coil_baby', $data);
        return $this->db->insert_id();
    }

    /**
     * Hapus (hard) material & baby milik report untuk keperluan tulis ulang saat edit.
     * Dipanggil di dalam transaksi.
     */
    public function purge_details($unpack_no)
    {
        $mats = $this->db->select('id')->where('unpack_no', $unpack_no)
            ->get('tr_unpack_coil_material')->result_array();
        foreach ($mats as $m) {
            $this->db->where('id_unpack_material', $m['id'])->delete('tr_unpack_coil_baby');
        }
        $this->db->where('unpack_no', $unpack_no)->delete('tr_unpack_coil_material');
    }

    // ===============================================================
    // WAREHOUSE STOCK COIL (baby coil fisik)
    // ===============================================================

    /**
     * Hapus/nonaktifkan baby coil lama dari sebuah coil induk (untuk tulis ulang saat edit).
     * Menghapus baris fisik baby coil supaya jumlah baris konsisten dengan input baru.
     */
    public function delete_baby_stock_coils_by_unpack($unpack_no)
    {
        $this->db->where('no_ipp', $unpack_no)
            ->where('is_baby_coil', 1)
            ->delete('warehouse_stock_coil');
    }

    public function insert_baby_stock_coil($induk, $babycoil_code, $no_coil, $net, $gross, $costbook, $unpack_no)
    {
        $this->db->insert('warehouse_stock_coil', array(
            'id_material'    => $induk['id_material'],
            'no_coil'        => $no_coil,
            'kode_internal'  => $babycoil_code,
            'nm_material'    => $induk['nm_material'],
            'trade_name'     => isset($induk['trade_name']) ? $induk['trade_name'] : null,
            'gross_weight'   => $gross,
            'net_weight'     => $net,
            'length'         => isset($induk['length']) ? $induk['length'] : 0,
            'id_gudang'      => $induk['id_gudang'],
            'kd_gudang'      => $induk['kd_gudang'],
            'id_pack'        => isset($induk['id_pack']) ? $induk['id_pack'] : null,
            'is_baby_coil'   => 1,
            'qty_roll'       => 1,
            'parent_coil_id' => $induk['id'],
            'harga_beli'     => $costbook,
            'total_nilai'    => $net * $costbook,
            'status'         => 1,
            'status_proses'  => 'in_transit',
            'no_ipp'         => $unpack_no,
        ));
        return $this->db->insert_id();
    }

    public function mark_induk_unpacked($id_coil_induk, $unpack_no)
    {
        $this->db->where('id', $id_coil_induk)->update('warehouse_stock_coil', array(
            'status'  => 0,
            'no_ipp'  => $unpack_no,
        ));
    }

    public function restore_induk_by_unpack($unpack_no)
    {
        $this->db->where('no_ipp', $unpack_no)
            ->where('is_baby_coil', 0)
            ->update('warehouse_stock_coil', array(
                'status'        => 1,
                'status_proses' => 'in_transit',
            ));
    }

    /**
     * Kembalikan coil induk ke status semula (dipakai saat edit: sebelum tulis ulang,
     * coil induk mungkin sudah 'unpacked'; kita perlu recalc dari nilai induk).
     */
    public function get_induk_any_status($id_coil_induk)
    {
        return $this->db->query(
            "SELECT * FROM warehouse_stock_coil WHERE id = ? LIMIT 1",
            array($id_coil_induk)
        )->row_array();
    }

    // ===============================================================
    // WAREHOUSE STOCK (agregat) + HISTORY (3 tabel)
    // ===============================================================

    public function get_stock($id_material, $kd_gudang)
    {
        return $this->db->query(
            "SELECT * FROM warehouse_stock WHERE code_lv4 = ? AND kd_gudang = ? LIMIT 1 FOR UPDATE",
            array($id_material, $kd_gudang)
        )->row_array();
    }

    public function update_stock($id_material, $kd_gudang, $qty_stock, $total_nilai, $harga_beli)
    {
        $this->db->where('code_lv4', $id_material)
            ->where('kd_gudang', $kd_gudang)
            ->set('qty_stock', $qty_stock)
            ->set('total_nilai', $total_nilai)
            ->set('harga_beli', $harga_beli)
            ->update('warehouse_stock');
    }

    public function insert_warehouse_history($data)
    {
        $this->db->insert('warehouse_history', $data);
    }

    public function insert_transaction_summary($data)
    {
        $this->db->insert('warehouse_stock_transaction_summary', $data);
    }

    public function insert_transaction_detail($data)
    {
        $this->db->insert('warehouse_stock_transaction_detail', $data);
    }

    // ===============================================================
    // READ (view / edit)
    // ===============================================================

    public function get_header($unpack_no)
    {
        return $this->db->query(
            "SELECT * FROM tr_unpack_coil_header WHERE unpack_no = ? AND is_delete = 0 LIMIT 1",
            array($unpack_no)
        )->row_array();
    }

    public function get_materials_by_unpack($unpack_no)
    {
        return $this->db->query(
            "SELECT * FROM tr_unpack_coil_material WHERE unpack_no = ? AND is_delete = 0 ORDER BY id ASC",
            array($unpack_no)
        )->result_array();
    }

    public function get_babies_by_material($id_unpack_material)
    {
        return $this->db->query(
            "SELECT * FROM tr_unpack_coil_baby WHERE id_unpack_material = ? AND is_delete = 0 ORDER BY id ASC",
            array($id_unpack_material)
        )->result_array();
    }

    /**
     * Net actual total per coil induk yang tersimpan sebelumnya (untuk hitung delta
     * saat EDIT: revert nilai lama sebelum menerapkan yang baru).
     * @return array map id_coil_induk => net_weight_actual
     */
    public function get_prev_material_net_by_group($unpack_no)
    {
        $rows = $this->db->query(
            "SELECT id_material, id_gudang, net_weight_actual FROM tr_unpack_coil_material
         WHERE unpack_no = ? AND is_delete = 0",
            array($unpack_no)
        )->result_array();

        $map = array();
        foreach ($rows as $r) {
            $key = $r['id_material'] . '|' . $r['id_gudang'];
            $map[$key] = (float) $r['net_weight_actual'];
        }
        return $map;
    }

    // ===============================================================
    // DELETE (soft)
    // ===============================================================

    public function delete_req($unpack_no, $updated_by)
    {
        $this->db->where('unpack_no', $unpack_no)->update('tr_unpack_coil_header', array(
            'is_delete'  => 1,
            'status'     => 'Cancelled',
            'updated_by' => $updated_by,
        ));
        $this->db->where('unpack_no', $unpack_no)->update('tr_unpack_coil_material', array('is_delete' => 1));
    }

    public function lock_req($unpack_no, $updated_by)
    {
        $this->db->where('unpack_no', $unpack_no)->update('tr_unpack_coil_header', array(
            'status'     => 'Lock',
            'updated_by' => $updated_by,
        ));
    }
}
