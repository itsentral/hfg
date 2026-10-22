<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bottom_price_notification_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /** Ambil daftar notifikasi perubahan Bottom Price dengan join info produk */
    public function get_list($status_filter = null)
    {
        $sql = "
            SELECT 
                n.*,
                p.nama AS nm_product,
                cat.nama AS nm_category,
                u.nm_lengkap AS reviewer_name,
                req.status AS req_status,
                req.rate_negotiation_allowance AS requested_rate
            FROM bottom_price_notification n
            LEFT JOIN product_lvl_4 p ON p.code_lv4 = n.code_lv4
            LEFT JOIN product_lvl_2 cat ON cat.code_lv2 = n.code_lv2
            LEFT JOIN users u ON u.id_user = n.reviewed_by
            LEFT JOIN rate_negotiation_allowance_request req ON req.id = n.id_rate_negotiation_allowance_request
        ";

        if (!empty($status_filter)) {
            $sql .= " WHERE n.status = " . $this->db->escape($status_filter);
        }

        $sql .= " ORDER BY (CASE WHEN n.status = 'Belum Ditinjau' THEN 0 ELSE 1 END) ASC, n.id DESC";

        return $this->db->query($sql)->result();
    }

    /** Hitung ringkasan status notifikasi */
    public function get_counts()
    {
        $sql = "
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'Belum Ditinjau' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'Diajukan' THEN 1 ELSE 0 END) AS requested,
                SUM(CASE WHEN status = 'Tidak Perlu Diubah' THEN 1 ELSE 0 END) AS kept
            FROM bottom_price_notification
        ";
        $row = $this->db->query($sql)->row();
        return [
            'total'     => $row ? (int)$row->total : 0,
            'pending'   => $row ? (int)$row->pending : 0,
            'requested' => $row ? (int)$row->requested : 0,
            'kept'      => $row ? (int)$row->kept : 0,
        ];
    }

    /** Putuskan tetap pakai rate aktif (Keep Rate Aktif / Tidak Perlu Diubah) */
    public function keep_rate($id, $catatan, $user_id)
    {
        return $this->db->where('id', $id)->update('bottom_price_notification', [
            'status'      => 'Tidak Perlu Diubah',
            'reviewed_by' => $user_id,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'catatan'     => trim($catatan),
        ]);
    }

    /** Tautkan notifikasi dengan pengajuan Extra Margin baru */
    public function link_request($id, $req_id, $user_id)
    {
        return $this->db->where('id', $id)->update('bottom_price_notification', [
            'status'                              => 'Diajukan',
            'id_rate_negotiation_allowance_request'=> $req_id,
            'reviewed_by'                         => $user_id,
            'reviewed_at'                         => date('Y-m-d H:i:s'),
        ]);
    }
}
