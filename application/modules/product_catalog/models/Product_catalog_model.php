<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_catalog_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil daftar item katalog produk dengan filter kategori, ID katalog rilis, atau status
     * Menampilkan nama produk di awal query (FR-73)
     */
    public function get_catalog_items($category = null, $catalog_id = null, $status = 'Active')
    {
        $sql = "
            SELECT 
                d.*,
                h.no_catalog,
                h.status AS catalog_status,
                h.released_at,
                h.released_by,
                u_rel.nm_lengkap AS released_by_name,
                pl.no_pricelist,
                p.nama AS nm_product,
                p.code_lv1,
                cat.nama AS nm_category
            FROM product_catalog_detail d
            JOIN product_catalog_header h ON h.id = d.id_product_catalog_header
            LEFT JOIN pricelist_header pl ON pl.id = h.id_pricelist_header
            LEFT JOIN product_lvl_4 p ON p.code_lv4 = d.code_lv4
            LEFT JOIN product_lvl_2 cat ON cat.code_lv2 = d.code_lv2
            LEFT JOIN users u_rel ON u_rel.id_user = h.released_by
            WHERE 1=1
        ";

        if (!empty($status)) {
            $sql .= " AND h.status = " . $this->db->escape($status);
        }

        if (!empty($catalog_id)) {
            $sql .= " AND h.id = " . (int)$catalog_id;
        }

        if (!empty($category)) {
            $sql .= " AND (d.code_lv2 = " . $this->db->escape($category) . " OR cat.nama = " . $this->db->escape($category) . ")";
        }

        $sql .= " ORDER BY cat.nama ASC, p.nama ASC, d.no_uniq_id ASC";

        return $this->db->query($sql)->result();
    }

    /**
     * Ambil daftar rilis Product Catalog (header)
     */
    public function get_catalog_headers()
    {
        $sql = "
            SELECT 
                h.*,
                u.nm_lengkap AS released_by_name,
                pl.no_pricelist,
                COUNT(d.id) AS total_items
            FROM product_catalog_header h
            LEFT JOIN users u ON u.id_user = h.released_by
            LEFT JOIN pricelist_header pl ON pl.id = h.id_pricelist_header
            LEFT JOIN product_catalog_detail d ON d.id_product_catalog_header = h.id
            GROUP BY h.id
            ORDER BY h.id DESC
        ";
        return $this->db->query($sql)->result();
    }

    /**
     * Ambil satu rilis catalog beserta seluruh detail produknya
     */
    public function get_catalog_by_id($id)
    {
        $header = $this->db->select('h.*, u.nm_lengkap AS released_by_name, pl.no_pricelist')
            ->from('product_catalog_header h')
            ->join('users u', 'u.id_user = h.released_by', 'left')
            ->join('pricelist_header pl', 'pl.id = h.id_pricelist_header', 'left')
            ->where('h.id', (int)$id)
            ->get()->row();

        if (!$header) return null;

        $sql_items = "
            SELECT 
                d.*,
                p.nama AS nm_product,
                cat.nama AS nm_category
            FROM product_catalog_detail d
            LEFT JOIN product_lvl_4 p ON p.code_lv4 = d.code_lv4
            LEFT JOIN product_lvl_2 cat ON cat.code_lv2 = d.code_lv2
            WHERE d.id_product_catalog_header = ?
            ORDER BY cat.nama ASC, p.nama ASC, d.no_uniq_id ASC
        ";
        $details = $this->db->query($sql_items, [(int)$id])->result();

        return (object)[
            'header'  => $header,
            'details' => $details,
        ];
    }

    /**
     * Ambil daftar kategori produk untuk dropdown filter
     */
    public function get_categories()
    {
        return $this->db->select('code_lv2, nama')
            ->from('product_lvl_2')
            ->where('deleted_date', null)
            ->where('category', 'product')
            ->order_by('nama', 'ASC')
            ->get()->result();
    }

    /**
     * Ambil daftar Pricelist yang berstatus 'Approved' untuk rilis katalog baru
     */
    public function get_approved_pricelists()
    {
        $sql = "
            SELECT 
                h.*,
                u.nm_lengkap AS decided_by_name,
                COUNT(d.id) AS total_items
            FROM pricelist_header h
            LEFT JOIN users u ON u.id_user = h.decided_by
            LEFT JOIN pricelist_detail d ON d.id_pricelist_header = h.id
            WHERE h.status = 'Approved'
            GROUP BY h.id
            ORDER BY h.id DESC
        ";
        return $this->db->query($sql)->result();
    }

    /**
     * Rilis Product Catalog dari Pricelist yang sudah Approved
     */
    public function release_catalog_from_pricelist($id_pricelist_header, $user_id)
    {
        $pl = $this->db->where('id', (int)$id_pricelist_header)
            ->where('status', 'Approved')
            ->get('pricelist_header')->row();

        if (!$pl) return false;

        $details = $this->db->where('id_pricelist_header', (int)$id_pricelist_header)->get('pricelist_detail')->result();
        if (empty($details)) return false;

        $this->db->trans_begin();

        $now = date('Y-m-d H:i:s');
        $year = date('Y');
        $cat_count = $this->db->like('no_catalog', 'CAT-' . $year)->count_all_results('product_catalog_header') + 1;
        $no_cat = 'CAT-' . $year . '-' . str_pad($cat_count, 4, '0', STR_PAD_LEFT);

        // Arsipkan katalog aktif sebelumnya yang memuat produk serupa jika diinginkan,
        // namun sesuai FR-58 & NFR sistem mencatat setiap rilis sebagai snapshot baru
        $this->db->insert('product_catalog_header', [
            'id_pricelist_header' => $id_pricelist_header,
            'no_catalog'          => $no_cat,
            'status'              => 'Active',
            'released_by'         => $user_id,
            'released_at'         => $now,
        ]);
        $cat_id = $this->db->insert_id();

        foreach ($details as $d) {
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
        return $no_cat;
    }
}
