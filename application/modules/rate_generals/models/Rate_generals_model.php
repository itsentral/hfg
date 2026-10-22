<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rate_generals_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /** Ambil baris data rate generals */
    public function get_rate()
    {
        return $this->db->select('r.*, u.nm_lengkap AS updated_by_name')
            ->from('rate_generals r')
            ->join('users u', 'u.id_user = r.updated_by', 'left')
            ->where('r.id', 1)
            ->get()->row();
    }

    /** Ambil definisi & nilai 9 elemen rate */
    public function get_elements()
    {
        $r = $this->get_rate();
        if (!$r) {
            // Jika belum ada row 1, buat default
            $this->db->insert('rate_generals', [
                'id'                              => 1,
                'rate_consumables'                => 100,
                'rate_scrap'                      => 0.02,
                'rate_biaya_koordinasi'           => 50,
                'rate_other_cost_sales_marketing' => 0.02,
                'rate_other_cost_transport'       => 0.015,
                'rate_other_cost_finance_admin'   => 0.01,
                'rate_other_cost_management_fee'  => 0.02,
                'rate_other_cost_interest'        => 0.005,
                'rate_other_cost_profit'          => 0.05,
                'latest_update'                   => date('Y-m-d H:i:s'),
            ]);
            $r = $this->get_rate();
        }

        return [
            [
                'key'      => 'rate_consumables',
                'group'    => 'Manufacturing Support',
                'label'    => 'Consumables',
                'basis'    => 'Rp/Kg Material',
                'unit'     => 'rp_kg',
                'required' => true,
                'value'    => (float) $r->rate_consumables,
                'updated'  => $r->latest_update,
                'by'       => $r->updated_by_name ?: '-',
            ],
            [
                'key'      => 'rate_scrap',
                'group'    => 'Manufacturing Support',
                'label'    => 'Scrap',
                'basis'    => '% terhadap Material Cost',
                'unit'     => 'pct',
                'required' => true,
                'value'    => (float) $r->rate_scrap,
                'updated'  => $r->latest_update,
                'by'       => $r->updated_by_name ?: '-',
            ],
            [
                'key'      => 'rate_biaya_koordinasi',
                'group'    => 'Manufacturing Support',
                'label'    => 'Biaya Koordinasi',
                'basis'    => 'Rp/Kg Material',
                'unit'     => 'rp_kg',
                'required' => true,
                'value'    => (float) $r->rate_biaya_koordinasi,
                'updated'  => $r->latest_update,
                'by'       => $r->updated_by_name ?: '-',
            ],
            [
                'key'      => 'rate_other_cost_sales_marketing',
                'group'    => 'Other Cost',
                'label'    => 'Sales & Marketing',
                'basis'    => '% × HPP',
                'unit'     => 'pct',
                'required' => false,
                'value'    => (float) $r->rate_other_cost_sales_marketing,
                'updated'  => $r->latest_update,
                'by'       => $r->updated_by_name ?: '-',
            ],
            [
                'key'      => 'rate_other_cost_transport',
                'group'    => 'Other Cost',
                'label'    => 'Transport',
                'basis'    => '% × HPP',
                'unit'     => 'pct',
                'required' => false,
                'value'    => (float) $r->rate_other_cost_transport,
                'updated'  => $r->latest_update,
                'by'       => $r->updated_by_name ?: '-',
            ],
            [
                'key'      => 'rate_other_cost_finance_admin',
                'group'    => 'Other Cost',
                'label'    => 'Finance & Admin',
                'basis'    => '% × HPP',
                'unit'     => 'pct',
                'required' => false,
                'value'    => (float) $r->rate_other_cost_finance_admin,
                'updated'  => $r->latest_update,
                'by'       => $r->updated_by_name ?: '-',
            ],
            [
                'key'      => 'rate_other_cost_management_fee',
                'group'    => 'Other Cost',
                'label'    => 'Management Fee',
                'basis'    => '% × HPP',
                'unit'     => 'pct',
                'required' => false,
                'value'    => (float) $r->rate_other_cost_management_fee,
                'updated'  => $r->latest_update,
                'by'       => $r->updated_by_name ?: '-',
            ],
            [
                'key'      => 'rate_other_cost_interest',
                'group'    => 'Other Cost',
                'label'    => 'Interest',
                'basis'    => '% × HPP',
                'unit'     => 'pct',
                'required' => false,
                'value'    => (float) $r->rate_other_cost_interest,
                'updated'  => $r->latest_update,
                'by'       => $r->updated_by_name ?: '-',
            ],
            [
                'key'      => 'rate_other_cost_profit',
                'group'    => 'Other Cost',
                'label'    => 'Profit',
                'basis'    => '% × HPP',
                'unit'     => 'pct',
                'required' => false,
                'value'    => (float) $r->rate_other_cost_profit,
                'updated'  => $r->latest_update,
                'by'       => $r->updated_by_name ?: '-',
            ],
        ];
    }

    /** Update satu elemen rate general */
    public function update_element($key, $value, $id_user = null)
    {
        $allowed = [
            'rate_consumables',
            'rate_scrap',
            'rate_biaya_koordinasi',
            'rate_other_cost_sales_marketing',
            'rate_other_cost_transport',
            'rate_other_cost_finance_admin',
            'rate_other_cost_management_fee',
            'rate_other_cost_interest',
            'rate_other_cost_profit',
        ];

        if (!in_array($key, $allowed)) {
            return false;
        }

        return $this->db->where('id', 1)->update('rate_generals', [
            $key            => (float) $value,
            'latest_update' => date('Y-m-d H:i:s'),
            'updated_by'    => $id_user,
        ]);
    }

    /** Update seluruh elemen sekaligus */
    public function update_all($data, $id_user = null)
    {
        $data['latest_update'] = date('Y-m-d H:i:s');
        $data['updated_by']    = $id_user;
        return $this->db->where('id', 1)->update('rate_generals', $data);
    }
}
