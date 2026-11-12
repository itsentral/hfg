<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Master_standard_material_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil data Master Standard Material (global 1 baris, id=1)
     */
    public function get_standard()
    {
        $row = $this->db->where('id', 1)->get('master_standard_material')->row();
        if (!$row) {
            $this->db->insert('master_standard_material', [
                'id'                  => 1,
                'standar_toleransi'   => 0.000000,
                'standar_berat_jenis' => 7.850000,
                'coating'             => 0.000000,
                'latest_update'       => date('Y-m-d H:i:s'),
            ]);
            $row = $this->db->where('id', 1)->get('master_standard_material')->row();
        }
        return $row;
    }

    /**
     * Ambil contoh daftar material aktif untuk simulasi dampak berat/meter (limit 10)
     */
    public function get_sample_materials($limit = 10)
    {
        return $this->db
            ->select('m4.code_lv4, m4.nama, m4.thickness, m4.width')
            ->from('new_inventory_4 m4')
            ->where('m4.category', 'material')
            ->where('m4.deleted_date', null)
            ->where('m4.thickness >', 0)
            ->where('m4.width >', 0)
            ->order_by('m4.nama', 'ASC')
            ->limit($limit)
            ->get()->result();
    }

    /**
     * Simpan / update data Master Standard Material (upsert id=1)
     */
    public function save_standard($data)
    {
        $now = date('Y-m-d H:i:s');
        $update = [
            'standar_toleransi'   => (float) $data['standar_toleransi'],
            'standar_berat_jenis' => (float) $data['standar_berat_jenis'],
            'coating'             => (float) $data['coating'],
            'latest_update'       => $now,
            'updated_by'          => isset($data['updated_by']) ? (int) $data['updated_by'] : null,
        ];

        $existing = $this->db->where('id', 1)->get('master_standard_material')->row();
        if ($existing) {
            return $this->db->where('id', 1)->update('master_standard_material', $update);
        } else {
            $update['id'] = 1;
            return $this->db->insert('master_standard_material', $update);
        }
    }
}
