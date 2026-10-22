<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pricelist_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /** Ambil daftar seluruh Pricelist dengan ringkasan produk dan jumlah varian */
    public function get_pricelists($status = null)
    {
        $sql = "
            SELECT 
                h.*,
                u_sub.nm_lengkap AS submitted_by_name,
                u_dec.nm_lengkap AS decided_by_name,
                COUNT(d.id) AS total_items,
                GROUP_CONCAT(DISTINCT p.nama ORDER BY p.nama SEPARATOR ', ') AS product_names,
                GROUP_CONCAT(DISTINCT d.no_uniq_id ORDER BY d.no_uniq_id SEPARATOR ', ') AS uniq_ids
            FROM pricelist_header h
            LEFT JOIN pricelist_detail d ON d.id_pricelist_header = h.id
            LEFT JOIN product_lvl_4 p ON p.code_lv4 = d.code_lv4
            LEFT JOIN users u_sub ON u_sub.id_user = h.submitted_by
            LEFT JOIN users u_dec ON u_dec.id_user = h.decided_by
        ";

        if (!empty($status)) {
            $sql .= " WHERE h.status = " . $this->db->escape($status);
        }

        $sql .= " GROUP BY h.id ORDER BY (CASE WHEN h.status = 'Menunggu Approval' THEN 0 WHEN h.status = 'Draft' THEN 1 ELSE 2 END) ASC, h.id DESC";

        return $this->db->query($sql)->result();
    }

    /** Ambil satu Pricelist beserta seluruh baris detail variannya */
    public function get_pricelist_by_id($id)
    {
        $header = $this->db->select('h.*, u_sub.nm_lengkap AS submitted_by_name, u_dec.nm_lengkap AS decided_by_name, u_crt.nm_lengkap AS created_by_name')
            ->from('pricelist_header h')
            ->join('users u_sub', 'u_sub.id_user = h.submitted_by', 'left')
            ->join('users u_dec', 'u_dec.id_user = h.decided_by', 'left')
            ->join('users u_crt', 'u_crt.id_user = h.created_by', 'left')
            ->where('h.id', $id)
            ->get()->row();

        if (!$header) return null;

        $sql_details = "
            SELECT 
                d.*,
                p.nama AS nm_product,
                cat.nama AS nm_category
            FROM pricelist_detail d
            LEFT JOIN product_lvl_4 p ON p.code_lv4 = d.code_lv4
            LEFT JOIN product_lvl_2 cat ON cat.code_lv2 = d.code_lv2
            WHERE d.id_pricelist_header = ?
            ORDER BY cat.nama ASC, p.nama ASC, d.id ASC
        ";
        $details = $this->db->query($sql_details, [$id])->result();

        return (object)[
            'header'  => $header,
            'details' => $details,
        ];
    }

    /** Ajukan Draft Pricelist ke Management */
    public function submit_to_management($id, $user_id)
    {
        $pl = $this->get_pricelist_by_id($id);
        if (!$pl || $pl->header->status !== 'Draft') {
            return false;
        }

        if (empty($pl->details)) {
            return false;
        }

        return $this->db->where('id', $id)->update('pricelist_header', [
            'status'        => 'Menunggu Approval',
            'submitted_by'  => $user_id,
            'submitted_at'  => date('Y-m-d H:i:s'),
            'reject_reason' => null,
        ]);
    }

    /** Setujui Pricelist (Approved oleh GM/Management) */
    public function approve_pricelist($id, $user_id)
    {
        $pl = $this->get_pricelist_by_id($id);
        if (!$pl || $pl->header->status !== 'Menunggu Approval') {
            return false;
        }

        $this->db->trans_begin();
        $now = date('Y-m-d H:i:s');

        // 1. Update status header pricelist
        $this->db->where('id', $id)->update('pricelist_header', [
            'status'        => 'Approved',
            'decided_by'    => $user_id,
            'decided_at'    => $now,
            'reject_reason' => null,
        ]);

        // 2. Buat atau update Product Catalog (Snapshot harga aktif resmi)
        $year = date('Y');
        $cat_count = $this->db->like('no_catalog', 'CAT-' . $year)->count_all_results('product_catalog_header') + 1;
        $no_cat = 'CAT-' . $year . '-' . str_pad($cat_count, 4, '0', STR_PAD_LEFT);

        $this->db->insert('product_catalog_header', [
            'id_pricelist_header' => $id,
            'no_catalog'          => $no_cat,
            'status'              => 'Active',
            'released_by'         => $user_id,
            'released_at'         => $now,
        ]);
        $cat_id = $this->db->insert_id();

        // 3. Catat ke History Pricelist & rilis ke Product Catalog Detail
        foreach ($pl->details as $d) {
            // Ambil harga lama dari history pricelist terakhir varian ini
            $last_h = $this->db->where('code_lv4', $d->code_lv4)
                ->where('no_uniq_id', $d->no_uniq_id)
                ->order_by('id', 'DESC')
                ->get('history_pricelist')
                ->row();

            $pl_lama_m = $last_h ? (float)$last_h->price_list_baru_meter : 0.0;
            $pl_lama_kg = $last_h ? (float)$last_h->price_list_baru_kg : 0.0;

            // Catat history
            $this->db->insert('history_pricelist', [
                'id_pricelist_header'       => $id,
                'no_pricelist'              => $pl->header->no_pricelist,
                'code_lv4'                  => $d->code_lv4,
                'no_uniq_id'                => $d->no_uniq_id,
                'code_lv2'                  => $d->code_lv2,
                'event'                     => 'Disetujui',
                'event_at'                  => $now,
                'event_by'                  => $user_id,
                'price_list_lama_meter'     => $pl_lama_m,
                'price_list_lama_kg'        => $pl_lama_kg,
                'price_list_baru_meter'     => (float)$d->price_list_meter,
                'price_list_baru_kg'        => (float)$d->price_list_kg,
                'bottom_price_meter'        => (float)$d->bottom_price_meter,
                'bottom_price_kg'           => (float)$d->bottom_price_kg,
                'rate_negotiation_allowance'=> (float)$d->rate_negotiation_allowance,
                'catatan'                   => 'Pricelist ' . $pl->header->no_pricelist . ' disetujui Management',
            ]);

            // Masukkan ke Catalog Detail
            $this->db->insert('product_catalog_detail', [
                'id_product_catalog_header' => $cat_id,
                'id_product_costing_detail' => $d->id_product_costing_detail,
                'code_lv4'                  => $d->code_lv4,
                'no_uniq_id'                => $d->no_uniq_id,
                'code_lv2'                  => $d->code_lv2,
                'ukuran_product'            => $d->ukuran_product,
                'berat_per_meter'           => $d->berat_per_meter,
                'price_list_meter'          => $d->price_list_meter,
                'price_list_kg'             => $d->price_list_kg,
                'price_list_pcs'            => $d->price_list_pcs,
            ]);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    /** Tolak Pricelist (Rejected dengan alasan wajib) */
    public function reject_pricelist($id, $reason, $user_id)
    {
        $pl = $this->get_pricelist_by_id($id);
        if (!$pl || $pl->header->status !== 'Menunggu Approval') {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update('pricelist_header', [
            'status'        => 'Rejected',
            'reject_reason' => trim($reason),
            'decided_by'    => $user_id,
            'decided_at'    => $now,
        ]);
    }

    /** Hapus Baris Detail Varian dari Draft */
    public function delete_detail($detail_id)
    {
        return $this->db->where('id', $detail_id)->delete('pricelist_detail');
    }

    /** Hapus Seluruh Dokumen Draft Pricelist */
    public function delete_draft($id)
    {
        $pl = $this->get_pricelist_by_id($id);
        if (!$pl || $pl->header->status !== 'Draft') {
            return false;
        }

        $this->db->where('id_pricelist_header', $id)->delete('pricelist_detail');
        return $this->db->where('id', $id)->delete('pricelist_header');
    }
}
