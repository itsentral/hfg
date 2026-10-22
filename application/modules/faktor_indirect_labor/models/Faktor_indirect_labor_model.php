<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Faktor_indirect_labor_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /** Ambil data faktor indirect labor (global 1 baris) */
    public function get_faktor()
    {
        $row = $this->db->get_where('faktor_indirect_labor', ['id' => 1])->row();
        if (!$row) {
            $this->db->insert('faktor_indirect_labor', [
                'id'                    => 1,
                'faktor_indirect_labor' => 1.5000,
                'latest_update'         => date('Y-m-d H:i:s'),
            ]);
            $row = $this->db->get_where('faktor_indirect_labor', ['id' => 1])->row();
        }
        return $row;
    }

    /** Update faktor indirect labor */
    public function save_faktor($nilai, $id_user = null)
    {
        $data = [
            'faktor_indirect_labor' => (float) $nilai,
            'latest_update'         => date('Y-m-d H:i:s'),
            'updated_by'            => $id_user,
        ];
        return $this->db->where('id', 1)->update('faktor_indirect_labor', $data);
    }

    /** Ambil data contoh dampak per kategori produk */
    public function get_impact_simulation()
    {
        $cats = $this->db->select('code_lv2, nama')
            ->from('product_lvl_2')
            ->where('deleted_date', null)
            ->where('category', 'product')
            ->order_by('nama', 'ASC')
            ->get()->result();

        $flows = [];
        $q_flow = $this->db->get('flow_process_header')->result();
        foreach ($q_flow as $f) {
            $flows[$f->code_lv2] = (float) $f->total_man_hour;
        }

        $borongans = [];
        $q_bor = $this->db->get('rate_borongan_helper')->result();
        foreach ($q_bor as $b) {
            $borongans[$b->code_lv2] = (float) $b->rate_borongan_helper;
        }

        $mpr_row = $this->db->get_where('man_power_rate', ['id' => 1])->row();
        $rate_op = $mpr_row ? (float) $mpr_row->rate_1_3_operator : 9827.00;
        $faktor  = (float) $this->get_faktor()->faktor_indirect_labor;

        $out = [];
        foreach ($cats as $c) {
            $tmh = isset($flows[$c->code_lv2]) ? $flows[$c->code_lv2] : 0.0;
            $bor = isset($borongans[$c->code_lv2]) ? $borongans[$c->code_lv2] : null;

            $has_data = isset($flows[$c->code_lv2]) && isset($borongans[$c->code_lv2]);
            $subtotal = $has_data ? (($tmh * $rate_op) + $bor) : null;
            $total    = $subtotal !== null ? ($subtotal * $faktor) : null;

            $out[] = (object) [
                'code_lv2'  => $c->code_lv2,
                'nama'      => $c->nama,
                'has_data'  => $has_data,
                'subtotal'  => $subtotal,
                'faktor'    => $faktor,
                'total_mpc' => $total,
            ];
        }
        return $out;
    }
}
