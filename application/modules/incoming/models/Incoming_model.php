<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Incoming_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $ENABLE_ADD     = has_permission('Incoming.Add');
        $ENABLE_MANAGE  = has_permission('Incoming.Manage');
        $ENABLE_VIEW    = has_permission('Incoming.View');
        $ENABLE_DELETE  = has_permission('Incoming.Delete');
    }

    public function generate_id_incoming()
    {
        $tahun  = date('ym');
        $huruf  = "INC";
        $prefix = $huruf . "-" . $tahun . "-";

        $query = $this->db->query("SELECT MAX(kode_trans) AS max_id FROM tr_incoming_check WHERE kode_trans LIKE '$prefix%'");
        $row = $query->row();

        if ($row && $row->max_id != null) {
            $urutan = (int) substr($row->max_id, -6);
        } else {
            $urutan = 0;
        }

        $urutan++;

        return $prefix . sprintf("%06s", $urutan);
    }
}
