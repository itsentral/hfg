<?php
defined('BASEPATH') or exit('No direct script access allowed');

class History_pricelist_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil daftar riwayat Price List yang disetujui Management
     * Nama produk di depan (FR-73)
     */
    public function get_history($filters = [])
    {
        $sql = "
            SELECT 
                h.*,
                p.nama AS nm_product,
                cat.nama AS nm_category,
                u.nm_lengkap AS event_by_name
            FROM history_pricelist h
            LEFT JOIN product_lvl_4 p ON p.code_lv4 = h.code_lv4
            LEFT JOIN product_lvl_2 cat ON cat.code_lv2 = h.code_lv2
            LEFT JOIN users u ON u.id_user = h.event_by
            WHERE 1=1
        ";

        if (!empty($filters['code_lv4'])) {
            $sql .= " AND h.code_lv4 = " . $this->db->escape($filters['code_lv4']);
        }

        if (!empty($filters['code_lv2'])) {
            $sql .= " AND h.code_lv2 = " . $this->db->escape($filters['code_lv2']);
        }

        if (!empty($filters['no_pricelist'])) {
            $sql .= " AND h.no_pricelist = " . $this->db->escape($filters['no_pricelist']);
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(h.event_at) >= " . $this->db->escape($filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(h.event_at) <= " . $this->db->escape($filters['end_date']);
        }

        $sql .= " ORDER BY h.id DESC";

        return $this->db->query($sql)->result();
    }

    /**
     * Ambil daftar kategori produk untuk filter
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
     * Ambil daftar dokumen Pricelist yang ada di riwayat
     */
    public function get_pricelists()
    {
        return $this->db->select('no_pricelist, event_at, COUNT(id) as total_items')
            ->from('history_pricelist')
            ->group_by('no_pricelist')
            ->order_by('MAX(id)', 'DESC')
            ->get()->result();
    }
}
