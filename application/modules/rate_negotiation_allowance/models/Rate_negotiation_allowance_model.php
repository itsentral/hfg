<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rate_negotiation_allowance_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /** Ambil daftar seluruh kategori produk dengan rate aktif dan status pengajuan terakhir */
    public function get_list_with_requests()
    {
        $sql = "
            SELECT 
                p.code_lv2,
                p.nama AS nm_category,
                r.id AS rate_id,
                r.rate_negotiation_allowance AS active_rate,
                r.latest_update,
                u_act.nm_lengkap AS updated_by_name,
                req.id AS req_id,
                req.rate_negotiation_allowance AS requested_rate,
                req.status AS req_status,
                req.submitted_at,
                req.reject_reason,
                u_sub.nm_lengkap AS submitted_by_name
            FROM product_lvl_2 p
            LEFT JOIN rate_negotiation_allowance r ON r.code_lv2 = p.code_lv2
            LEFT JOIN users u_act ON u_act.id_user = r.updated_by
            LEFT JOIN (
                SELECT r1.*
                FROM rate_negotiation_allowance_request r1
                INNER JOIN (
                    SELECT code_lv2, MAX(id) AS max_id
                    FROM rate_negotiation_allowance_request
                    GROUP BY code_lv2
                ) r2 ON r1.id = r2.max_id
            ) req ON req.code_lv2 = p.code_lv2
            LEFT JOIN users u_sub ON u_sub.id_user = req.submitted_by
            WHERE p.deleted_date IS NULL
              AND p.category = 'product'
            ORDER BY p.nama ASC
        ";

        return $this->db->query($sql)->result();
    }

    /** Ambil 1 kategori & rate aktif by code_lv2 */
    public function get_by_code($code_lv2)
    {
        $sql = "
            SELECT 
                p.code_lv2,
                p.nama AS nm_category,
                r.id AS rate_id,
                r.rate_negotiation_allowance AS active_rate,
                r.latest_update,
                u.nm_lengkap AS updated_by_name
            FROM product_lvl_2 p
            LEFT JOIN rate_negotiation_allowance r ON r.code_lv2 = p.code_lv2
            LEFT JOIN users u ON u.id_user = r.updated_by
            WHERE p.code_lv2 = ?
        ";
        return $this->db->query($sql, [$code_lv2])->row();
    }

    /** Ajukan rate baru (insert ke rate_negotiation_allowance_request) */
    public function submit_request($code_lv2, $requested_rate, $id_user)
    {
        return $this->db->insert('rate_negotiation_allowance_request', [
            'code_lv2'                   => $code_lv2,
            'rate_negotiation_allowance' => (float) $requested_rate,
            'status'                     => 'Menunggu Approval GM',
            'submitted_by'               => $id_user,
            'submitted_at'               => date('Y-m-d H:i:s'),
        ]);
    }

    /** Ambil daftar pengajuan approval GM dengan filter status */
    public function get_requests($status = null)
    {
        $sql = "
            SELECT 
                req.*,
                p.nama AS nm_category,
                act.rate_negotiation_allowance AS active_rate,
                u_sub.nm_lengkap AS submitted_by_name,
                u_dec.nm_lengkap AS decided_by_name
            FROM rate_negotiation_allowance_request req
            LEFT JOIN product_lvl_2 p ON p.code_lv2 = req.code_lv2
            LEFT JOIN rate_negotiation_allowance act ON act.code_lv2 = req.code_lv2
            LEFT JOIN users u_sub ON u_sub.id_user = req.submitted_by
            LEFT JOIN users u_dec ON u_dec.id_user = req.decided_by
        ";

        if (!empty($status)) {
            $sql .= " WHERE req.status = " . $this->db->escape($status);
        }

        $sql .= " ORDER BY (CASE WHEN req.status = 'Menunggu Approval GM' THEN 0 ELSE 1 END) ASC, req.id DESC";

        return $this->db->query($sql)->result();
    }

    /** Ambil 1 request by ID */
    public function get_request_by_id($id)
    {
        $sql = "
            SELECT 
                req.*,
                p.nama AS nm_category,
                act.rate_negotiation_allowance AS active_rate,
                u_sub.nm_lengkap AS submitted_by_name,
                u_dec.nm_lengkap AS decided_by_name
            FROM rate_negotiation_allowance_request req
            LEFT JOIN product_lvl_2 p ON p.code_lv2 = req.code_lv2
            LEFT JOIN rate_negotiation_allowance act ON act.code_lv2 = req.code_lv2
            LEFT JOIN users u_sub ON u_sub.id_user = req.submitted_by
            LEFT JOIN users u_dec ON u_dec.id_user = req.decided_by
            WHERE req.id = ?
        ";
        return $this->db->query($sql, [$id])->row();
    }

    /** Hitung pengajuan yang menunggu approval GM */
    public function count_pending_requests()
    {
        return (int) $this->db->where('status', 'Menunggu Approval GM')
            ->count_all_results('rate_negotiation_allowance_request');
    }

    /** Setujui pengajuan (GM Approve) */
    public function approve_request($id, $id_user)
    {
        $req = $this->get_request_by_id($id);
        if (!$req || $req->status !== 'Menunggu Approval GM') {
            return false;
        }

        $this->db->trans_begin();
        $now = date('Y-m-d H:i:s');

        // 1. Update status request
        $this->db->where('id', $id)->update('rate_negotiation_allowance_request', [
            'status'        => 'Approved',
            'decided_by'    => $id_user,
            'decided_at'    => $now,
            'reject_reason' => null,
        ]);

        // 2. Update atau insert rate aktif
        $exists = $this->db->where('code_lv2', $req->code_lv2)->get('rate_negotiation_allowance')->row();
        if ($exists) {
            $this->db->where('code_lv2', $req->code_lv2)->update('rate_negotiation_allowance', [
                'rate_negotiation_allowance' => (float) $req->rate_negotiation_allowance,
                'latest_update'              => $now,
                'updated_by'                 => $id_user,
            ]);
        } else {
            $this->db->insert('rate_negotiation_allowance', [
                'code_lv2'                   => $req->code_lv2,
                'rate_negotiation_allowance' => (float) $req->rate_negotiation_allowance,
                'latest_update'              => $now,
                'updated_by'                 => $id_user,
            ]);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    /** Tolak pengajuan (GM Reject) */
    public function reject_request($id, $reject_reason, $id_user)
    {
        $req = $this->get_request_by_id($id);
        if (!$req || $req->status !== 'Menunggu Approval GM') {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update('rate_negotiation_allowance_request', [
            'status'        => 'Rejected',
            'reject_reason' => trim($reject_reason),
            'decided_by'    => $id_user,
            'decided_at'    => $now,
        ]);
    }
}
