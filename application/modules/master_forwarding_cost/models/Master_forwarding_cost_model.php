<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Master_forwarding_cost_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'master_forwarding_cost';
    }

    public function get_data($id = null, $tipe = null)
    {
        $this->db->where('is_delete', '0');
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get($this->table_name)->row();
        }
        if ($tipe) {
            $this->db->where('tipe', strtolower($tipe));
            return $this->db->get($this->table_name)->row();
        }
        $this->db->order_by('tipe', 'ASC');
        return $this->db->get($this->table_name)->result();
    }

    public function get_active_cost($tipe = 'import')
    {
        $this->db->where('is_delete', '0');
        if (!empty($tipe)) {
            $this->db->where('tipe', strtolower($tipe));
        }
        $this->db->limit(1);
        $result = $this->db->get($this->table_name)->row();
        return $result ? (float) $result->value_cost : 0;
    }

    public function check_existing_tipe($tipe, $exclude_id = null)
    {
        $this->db->where('is_delete', '0');
        $this->db->where('tipe', strtolower($tipe));
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table_name)->num_rows() > 0;
    }

    public function save_data($data, $id = null)
    {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->update($this->table_name, $data);
        } else {
            return $this->db->insert($this->table_name, $data);
        }
    }

    public function delete_data($id, $user_id)
    {
        $data = [
            'is_delete'   => '1',
            'update_by'   => $user_id,
            'update_date' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $id);
        return $this->db->update($this->table_name, $data);
    }

    public function has_active_data()
    {
        $this->db->where('is_delete', '0');
        return $this->db->get($this->table_name)->num_rows() > 0;
    }
}
