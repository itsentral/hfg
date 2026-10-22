<?php
defined('BASEPATH') or exit('No direct script access allowed');

class History_costing_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil riwayat costing dengan berbagai filter
     * Nama produk di depan (FR-73)
     */
    public function get_history($filters = [])
    {
        $sql = "
            SELECT 
                h.*,
                p.nama AS nm_product,
                cat.nama AS nm_category,
                u.nm_lengkap AS changed_by_name
            FROM history_costing h
            LEFT JOIN product_lvl_4 p ON p.code_lv4 = h.code_lv4
            LEFT JOIN product_lvl_2 cat ON cat.code_lv2 = h.code_lv2
            LEFT JOIN users u ON u.id_user = h.changed_by
            WHERE 1=1
        ";

        if (!empty($filters['code_lv4'])) {
            $sql .= " AND h.code_lv4 = " . $this->db->escape($filters['code_lv4']);
        }

        if (!empty($filters['code_lv2'])) {
            $sql .= " AND h.code_lv2 = " . $this->db->escape($filters['code_lv2']);
        }

        if (!empty($filters['batch_id'])) {
            $sql .= " AND h.batch_id = " . $this->db->escape($filters['batch_id']);
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(h.changed_at) >= " . $this->db->escape($filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(h.changed_at) <= " . $this->db->escape($filters['end_date']);
        }

        $sql .= " ORDER BY h.id DESC";

        return $this->db->query($sql)->result();
    }

    /**
     * Ambil 1 record riwayat costing beserta detail snapshot JSON
     */
    public function get_history_by_id($id)
    {
        $sql = "
            SELECT 
                h.*,
                p.nama AS nm_product,
                cat.nama AS nm_category,
                u.nm_lengkap AS changed_by_name
            FROM history_costing h
            LEFT JOIN product_lvl_4 p ON p.code_lv4 = h.code_lv4
            LEFT JOIN product_lvl_2 cat ON cat.code_lv2 = h.code_lv2
            LEFT JOIN users u ON u.id_user = h.changed_by
            WHERE h.id = ?
        ";
        return $this->db->query($sql, [(int)$id])->row();
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
     * Ambil daftar batch update costing untuk filter
     */
    public function get_batches()
    {
        return $this->db->select('batch_id, changed_at, COUNT(id) as total_items')
            ->from('history_costing')
            ->group_by('batch_id')
            ->order_by('MAX(id)', 'DESC')
            ->limit(30)
            ->get()->result();
    }
}
