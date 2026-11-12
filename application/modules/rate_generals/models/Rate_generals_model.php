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

        $other_labels = [];
        if (!empty($r->other_cost_labels)) {
            $decoded = json_decode($r->other_cost_labels, true);
            if (is_array($decoded)) {
                $other_labels = $decoded;
            }
        }

        return [
            [
                'key'               => 'rate_consumables',
                'group'             => 'Manufacturing Support',
                'label'             => 'Consumables',
                'basis'             => 'Rp/Kg Material',
                'unit'              => 'rp_kg',
                'required'          => true,
                'is_label_editable' => false,
                'value'             => (float) $r->rate_consumables,
                'updated'           => $r->latest_update,
                'by'                => $r->updated_by_name ?: '-',
            ],
            [
                'key'               => 'rate_scrap',
                'group'             => 'Manufacturing Support',
                'label'             => 'Scrap',
                'basis'             => '% terhadap Material Cost',
                'unit'              => 'pct',
                'required'          => true,
                'is_label_editable' => false,
                'value'             => (float) $r->rate_scrap,
                'updated'           => $r->latest_update,
                'by'                => $r->updated_by_name ?: '-',
            ],
            [
                'key'               => 'rate_biaya_koordinasi',
                'group'             => 'Manufacturing Support',
                'label'             => 'Biaya Koordinasi',
                'basis'             => 'Rp/Kg Material',
                'unit'              => 'rp_kg',
                'required'          => true,
                'is_label_editable' => false,
                'value'             => (float) $r->rate_biaya_koordinasi,
                'updated'           => $r->latest_update,
                'by'                => $r->updated_by_name ?: '-',
            ],
            [
                'key'               => 'rate_other_cost_sales_marketing',
                'group'             => 'Other Cost',
                'label'             => !empty($other_labels['rate_other_cost_sales_marketing']) ? $other_labels['rate_other_cost_sales_marketing'] : 'Sales & Marketing',
                'basis'             => '% × HPP',
                'unit'              => 'pct',
                'required'          => false,
                'is_label_editable' => true,
                'value'             => (float) $r->rate_other_cost_sales_marketing,
                'updated'           => $r->latest_update,
                'by'                => $r->updated_by_name ?: '-',
            ],
            [
                'key'               => 'rate_other_cost_transport',
                'group'             => 'Other Cost',
                'label'             => !empty($other_labels['rate_other_cost_transport']) ? $other_labels['rate_other_cost_transport'] : 'Transport',
                'basis'             => '% × HPP',
                'unit'              => 'pct',
                'required'          => false,
                'is_label_editable' => true,
                'value'             => (float) $r->rate_other_cost_transport,
                'updated'           => $r->latest_update,
                'by'                => $r->updated_by_name ?: '-',
            ],
            [
                'key'               => 'rate_other_cost_finance_admin',
                'group'             => 'Other Cost',
                'label'             => !empty($other_labels['rate_other_cost_finance_admin']) ? $other_labels['rate_other_cost_finance_admin'] : 'Finance & Admin',
                'basis'             => '% × HPP',
                'unit'              => 'pct',
                'required'          => false,
                'is_label_editable' => true,
                'value'             => (float) $r->rate_other_cost_finance_admin,
                'updated'           => $r->latest_update,
                'by'                => $r->updated_by_name ?: '-',
            ],
            [
                'key'               => 'rate_other_cost_management_fee',
                'group'             => 'Other Cost',
                'label'             => !empty($other_labels['rate_other_cost_management_fee']) ? $other_labels['rate_other_cost_management_fee'] : 'Management Fee',
                'basis'             => '% × HPP',
                'unit'              => 'pct',
                'required'          => false,
                'is_label_editable' => true,
                'value'             => (float) $r->rate_other_cost_management_fee,
                'updated'           => $r->latest_update,
                'by'                => $r->updated_by_name ?: '-',
            ],
            [
                'key'               => 'rate_other_cost_interest',
                'group'             => 'Other Cost',
                'label'             => !empty($other_labels['rate_other_cost_interest']) ? $other_labels['rate_other_cost_interest'] : 'Interest',
                'basis'             => '% × HPP',
                'unit'              => 'pct',
                'required'          => false,
                'is_label_editable' => true,
                'value'             => (float) $r->rate_other_cost_interest,
                'updated'           => $r->latest_update,
                'by'                => $r->updated_by_name ?: '-',
            ],
            [
                'key'               => 'rate_other_cost_profit',
                'group'             => 'Other Cost',
                'label'             => !empty($other_labels['rate_other_cost_profit']) ? $other_labels['rate_other_cost_profit'] : 'Profit',
                'basis'             => '% × HPP',
                'unit'              => 'pct',
                'required'          => false,
                'is_label_editable' => true,
                'value'             => (float) $r->rate_other_cost_profit,
                'updated'           => $r->latest_update,
                'by'                => $r->updated_by_name ?: '-',
            ],
        ];
    }

    /** Update satu elemen rate general (beserta label kustom jika Other Cost) */
    public function update_element($key, $value, $id_user = null, $new_label = null)
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

        $update_data = [
            $key            => (float) $value,
            'latest_update' => date('Y-m-d H:i:s'),
            'updated_by'    => $id_user,
        ];

        // Khusus grup Other Cost, label dapat disesuaikan
        $other_cost_keys = [
            'rate_other_cost_sales_marketing',
            'rate_other_cost_transport',
            'rate_other_cost_finance_admin',
            'rate_other_cost_management_fee',
            'rate_other_cost_interest',
            'rate_other_cost_profit',
        ];

        if (in_array($key, $other_cost_keys) && $new_label !== null && trim($new_label) !== '') {
            $row = $this->db->select('other_cost_labels')->where('id', 1)->get('rate_generals')->row();
            $labels = [];
            if ($row && !empty($row->other_cost_labels)) {
                $decoded = json_decode($row->other_cost_labels, true);
                if (is_array($decoded)) {
                    $labels = $decoded;
                }
            }
            $labels[$key] = trim($new_label);
            $update_data['other_cost_labels'] = json_encode($labels);
        }

        return $this->db->where('id', 1)->update('rate_generals', $update_data);
    }

    /** Update seluruh elemen sekaligus */
    public function update_all($data, $id_user = null)
    {
        $data['latest_update'] = date('Y-m-d H:i:s');
        $data['updated_by']    = $id_user;
        return $this->db->where('id', 1)->update('rate_generals', $data);
    }
}
