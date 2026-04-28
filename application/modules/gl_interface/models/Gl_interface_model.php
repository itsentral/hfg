<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Gl_interface_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * DataTable server-side
     */
    public function get_datatable($search = '', $jenis_transaksi = '', $status = '', $start = 0, $length = 25)
    {
        // Total tanpa filter
        $total = $this->db->count_all('gl_interface');

        // Base query
        $this->db->from('gl_interface');

        if (!empty($jenis_transaksi)) {
            $this->db->where('jenis_transaksi', $jenis_transaksi);
        }
        if (!empty($status)) {
            $this->db->where('status', $status);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('nomor', $search);
            $this->db->or_like('keterangan', $search);
            $this->db->or_like('jenis_transaksi', $search);
            $this->db->or_like('memo', $search);
            $this->db->group_end();
        }

        $filtered = $this->db->count_all_results('', false);

        $this->db->order_by('id', 'DESC');
        $this->db->limit($length, $start);
        $rows = $this->db->get()->result_array();

        // Hitung total debet/kredit per row
        foreach ($rows as &$row) {
            $totals = $this->db->select('SUM(debet) as total_debet, SUM(kredit) as total_kredit')
                ->where('id_gl_interface', $row['id'])
                ->get('gl_interface_detail')
                ->row();
            $row['total_debet']  = $totals ? (float) $totals->total_debet : 0;
            $row['total_kredit'] = $totals ? (float) $totals->total_kredit : 0;
            $row['memo_decoded'] = !empty($row['memo']) ? json_decode($row['memo'], true) : [];
        }
        unset($row);

        return [
            'total'    => $total,
            'filtered' => $filtered,
            'data'     => $rows,
        ];
    }

    public function get_header($id)
    {
        return $this->db->get_where('gl_interface', ['id' => $id])->row_array();
    }

    public function get_details($id)
    {
        return $this->db->select('a.*, b.nama as nama_coa')
            ->from('gl_interface_detail a')
            ->join(DBACC . '.coa_master b', 'a.no_perkiraan = b.no_perkiraan', 'left')
            ->where('a.id_gl_interface', $id)
            ->get()
            ->result_array();
    }

    /**
     * Ambil daftar jenis_transaksi unik untuk dropdown filter
     */
    public function get_jenis_transaksi_list()
    {
        return $this->db->distinct()
            ->select('jenis_transaksi')
            ->order_by('jenis_transaksi', 'ASC')
            ->get('gl_interface')
            ->result_array();
    }
}
