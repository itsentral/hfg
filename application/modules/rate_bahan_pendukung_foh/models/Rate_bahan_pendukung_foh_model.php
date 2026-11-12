<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rate_bahan_pendukung_foh_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /** Ambil semua data rate BPK & FOH join kategori produk */
    public function get_list()
    {
        return $this->db->select('r.*, p.nama AS nm_category')
            ->from('rate_bahan_pendukung_foh r')
            ->join('product_lvl_2 p', 'p.code_lv2 = r.code_lv2', 'left')
            ->order_by('p.nama', 'ASC')
            ->get()->result();
    }

    /** Ambil 1 baris rate by id */
    public function get_rate_by_id($id)
    {
        return $this->db->select('r.*, p.nama AS nm_category')
            ->from('rate_bahan_pendukung_foh r')
            ->join('product_lvl_2 p', 'p.code_lv2 = r.code_lv2', 'left')
            ->where('r.id', $id)
            ->get()->row();
    }

    /** Ambil daftar kategori produk yang belum diset rate BPK/FOH-nya */
    public function get_available_categories()
    {
        $used = $this->db->select('code_lv2')->get('rate_bahan_pendukung_foh')->result();
        $used_codes = array_map(function($x) { return $x->code_lv2; }, $used);

        $q = $this->db->select('code_lv2, nama')
            ->from('product_lvl_2')
            ->where('deleted_date', null)
            ->where('category', 'product')
            ->order_by('nama', 'ASC');

        if (!empty($used_codes)) {
            $q->where_not_in('code_lv2', $used_codes);
        }
        return $q->get()->result();
    }

    /** Simpan atau update rate BPK & FOH */
    public function save_rate($data, $id_user = null)
    {
        $now = date('Y-m-d H:i:s');
        if (!empty($data['id'])) {
            return $this->db->where('id', $data['id'])->update('rate_bahan_pendukung_foh', [
                'rate_pendukung_khusus' => (float) $data['rate_pendukung_khusus'],
                'rate_foh'              => (float) $data['rate_foh'],
                'latest_update'         => $now,
                'updated_by'            => $id_user,
            ]);
        } else {
            return $this->db->insert('rate_bahan_pendukung_foh', [
                'code_lv2'              => $data['code_lv2'],
                'rate_pendukung_khusus' => (float) $data['rate_pendukung_khusus'],
                'rate_foh'              => (float) $data['rate_foh'],
                'latest_update'         => $now,
                'updated_by'            => $id_user,
            ]);
        }
    }

    /** Hapus rate BPK & FOH */
    public function delete_rate($id)
    {
        return $this->db->where('id', $id)->delete('rate_bahan_pendukung_foh');
    }
}
