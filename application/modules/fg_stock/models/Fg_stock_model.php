<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Fg_stock_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil daftar stok FG berdasarkan kategori ('KW1', 'KW2', 'TONG_COIL', 'SCRAP')
     *
     * @param string|null $kategori
     * @return array
     */
    public function get_stok_by_kategori($kategori = null)
    {
        $this->db->select('s.*, COALESCE(NULLIF(s.nm_produk_fg, ""), p4.nama, s.produk_fg) AS nm_produk_fg, m.nama AS nm_material_plat');
        $this->db->from('fg_stock s');
        $this->db->join('product_lvl_4 p4', 'p4.code_lv4 = s.produk_fg', 'left');
        $this->db->join('new_inventory_4 m', 'm.code_lv4 = s.id_material OR m.id = s.id_material', 'left');

        if (!empty($kategori)) {
            $this->db->where('s.kategori_stok', $kategori);
        }

        if (in_array($kategori, ['TONG_COIL', 'SCRAP'])) {
            $this->db->order_by('s.sub_kategori', 'ASC');
            $this->db->order_by('s.nm_produk_fg', 'ASC');
        } else {
            $this->db->order_by('s.produk_fg', 'ASC');
        }

        return $this->db->get()->result();
    }

    /**
     * Ambil semua daftar stok FG (kompatibilitas)
     *
     * @return array
     */
    public function get_all_stok()
    {
        return $this->get_stok_by_kategori(null);
    }

    /**
     * Ambil data ringkasan KPI untuk header stok FG (agregat per kategori)
     *
     * @return object
     */
    public function get_kpi_summary()
    {
        $this->db->select("
            COUNT(id) AS total_sku,
            COALESCE(SUM(qty_stok), 0) AS total_qty,
            COALESCE(SUM(total_berat), 0) AS total_berat,
            COALESCE(SUM(total_nilai), 0) AS total_nilai,

            COUNT(IF(kategori_stok = 'KW1', 1, NULL)) AS kw1_sku,
            COALESCE(SUM(IF(kategori_stok = 'KW1', qty_stok, 0)), 0) AS kw1_qty,
            COALESCE(SUM(IF(kategori_stok = 'KW1', total_berat, 0)), 0) AS kw1_berat,
            COALESCE(SUM(IF(kategori_stok = 'KW1', total_nilai, 0)), 0) AS kw1_nilai,

            COUNT(IF(kategori_stok = 'KW2', 1, NULL)) AS kw2_sku,
            COALESCE(SUM(IF(kategori_stok = 'KW2', qty_stok, 0)), 0) AS kw2_qty,
            COALESCE(SUM(IF(kategori_stok = 'KW2', total_berat, 0)), 0) AS kw2_berat,
            COALESCE(SUM(IF(kategori_stok = 'KW2', total_nilai, 0)), 0) AS kw2_nilai,

            COUNT(IF(kategori_stok = 'TONG_COIL', 1, NULL)) AS tc_sku,
            COALESCE(SUM(IF(kategori_stok = 'TONG_COIL', qty_stok, 0)), 0) AS tc_qty,
            COALESCE(SUM(IF(kategori_stok = 'TONG_COIL', total_berat, 0)), 0) AS tc_berat,
            COALESCE(SUM(IF(kategori_stok = 'TONG_COIL', total_nilai, 0)), 0) AS tc_nilai,

            COUNT(IF(kategori_stok = 'SCRAP', 1, NULL)) AS scrap_sku,
            COALESCE(SUM(IF(kategori_stok = 'SCRAP', total_berat, 0)), 0) AS scrap_berat,
            COALESCE(SUM(IF(kategori_stok = 'SCRAP', total_nilai, 0)), 0) AS scrap_nilai
        ");
        return $this->db->get('fg_stock')->row();
    }

    /**
     * Ambil stok berdasarkan kode produk FG
     *
     * @param string $produk_fg
     * @return object|null
     */
    public function get_stok_by_kode($produk_fg)
    {
        $this->db->select('s.*, COALESCE(NULLIF(s.nm_produk_fg, ""), p4.nama, s.produk_fg) AS nm_produk_fg, m.nama AS nm_material_plat');
        $this->db->from('fg_stock s');
        $this->db->join('product_lvl_4 p4', 'p4.code_lv4 = s.produk_fg', 'left');
        $this->db->join('new_inventory_4 m', 'm.code_lv4 = s.id_material OR m.id = s.id_material', 'left');
        $this->db->where('s.produk_fg', $produk_fg);
        return $this->db->get()->row();
    }

    /**
     * Ambil ringkasan agregat stok untuk kategori tertentu (atau semua kategori)
     *
     * @param string|null $kategori
     * @return object
     */
    public function get_category_summary($kategori = null)
    {
        $this->db->select("
            COUNT(id) AS total_sku,
            COALESCE(SUM(qty_stok), 0) AS total_qty,
            COALESCE(SUM(total_berat), 0) AS total_berat,
            COALESCE(SUM(total_nilai), 0) AS total_nilai
        ");
        $this->db->from('fg_stock');
        if (!empty($kategori) && $kategori !== 'ALL') {
            $this->db->where('kategori_stok', $kategori);
        }
        return $this->db->get()->row();
    }

    /**
     * Ambil riwayat kartu stok (ledger) berdasarkan kategori stok, rentang tanggal, dan produk opsional
     *
     * @param string|null $kategori   'KW1', 'KW2', 'TONG_COIL', 'SCRAP', atau 'ALL'
     * @param string|null $tgl_dari   Format Y-m-d
     * @param string|null $tgl_sampai Format Y-m-d
     * @param string|null $produk_fg  Kode produk spesifik (opsional)
     * @return array
     */
    public function get_history_ledger($kategori = null, $tgl_dari = null, $tgl_sampai = null, $produk_fg = null)
    {
        $this->db->select('l.*, COALESCE(NULLIF(s.nm_produk_fg, ""), p4.nama, l.produk_fg) AS nm_produk_fg');
        $this->db->from('fg_stock_ledger l');
        $this->db->join('fg_stock s', 's.produk_fg = l.produk_fg', 'left');
        $this->db->join('product_lvl_4 p4', 'p4.code_lv4 = l.produk_fg', 'left');

        if (!empty($kategori) && $kategori !== 'ALL') {
            $this->db->where('l.kategori_stok', $kategori);
        }

        if (!empty($produk_fg)) {
            $this->db->where('l.produk_fg', $produk_fg);
        }

        if (!empty($tgl_dari)) {
            $this->db->where('l.tgl_transaksi >=', $tgl_dari);
        }
        if (!empty($tgl_sampai)) {
            $this->db->where('l.tgl_transaksi <=', $tgl_sampai);
        }

        return $this->db->order_by('l.tgl_transaksi', 'DESC')
            ->order_by('l.id', 'DESC')
            ->get()->result();
    }

    /**
     * Ambil daftar produk yang ada di stok untuk dropdown riwayat
     *
     * @return array
     */
    public function get_product_dropdown()
    {
        $this->db->select('s.produk_fg, s.kategori_stok, s.sub_kategori, COALESCE(NULLIF(s.nm_produk_fg, ""), p4.nama, s.produk_fg) AS nm_produk_fg');
        $this->db->from('fg_stock s');
        $this->db->join('product_lvl_4 p4', 'p4.code_lv4 = s.produk_fg', 'left');
        $this->db->order_by('s.kategori_stok', 'ASC');
        $this->db->order_by('s.produk_fg', 'ASC');
        return $this->db->get()->result();
    }
}
