<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Man_power_rate_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /** Ambil header Man Power Rate (id=1) */
    public function get_rate()
    {
        $row = $this->db->get_where('man_power_rate', ['id' => 1])->row();
        if (!$row) {
            $this->db->insert('man_power_rate', [
                'id'                           => 1,
                'salary_direct_man_power'      => 4500000.00,
                'bpjs'                         => 300000.00,
                'biaya_lain_lain'              => 300000.00,
                'total_direct_labor_per_bulan' => 5100000.00,
                'jam_kerja_per_bulan'          => 173.00,
                'rate_direct_labor'            => 29479.7688,
                'porsi_operator'               => 0.3333,
                'rate_1_3_operator'            => 9827.00,
                'latest_update'                => date('Y-m-d H:i:s'),
            ]);
            $row = $this->db->get_where('man_power_rate', ['id' => 1])->row();
        }
        return $row;
    }

    /** Ambil semua komponen, dikelompokkan berdasarkan kelompok */
    public function get_components()
    {
        $rows = $this->db->order_by('id', 'ASC')->get_where('man_power_rate_component', ['id_man_power_rate' => 1])->result();
        $out = [
            'salary'    => [],
            'bpjs'      => [],
            'lain_lain' => [],
        ];
        foreach ($rows as $r) {
            if (isset($out[$r->kelompok])) {
                $out[$r->kelompok][] = $r;
            }
        }
        return $out;
    }

    /** Ambil 1 komponen by id */
    public function get_component_by_id($id)
    {
        return $this->db->get_where('man_power_rate_component', ['id' => $id])->row();
    }

    /** Ambil nilai faktor indirect labor dari master */
    public function get_faktor_indirect()
    {
        $row = $this->db->get_where('faktor_indirect_labor', ['id' => 1])->row();
        return $row ? (float) $row->faktor_indirect_labor : 1.5000;
    }

    /** Simpan atau update komponen dan hitung ulang total */
    public function save_component($data, $id_user = null)
    {
        $this->db->trans_start();

        if (!empty($data['id'])) {
            $this->db->where('id', $data['id'])->update('man_power_rate_component', [
                'nama_komponen' => $data['nama_komponen'],
                'nilai'         => (float) $data['nilai'],
                'keterangan'    => isset($data['keterangan']) ? $data['keterangan'] : null,
            ]);
        } else {
            $this->db->insert('man_power_rate_component', [
                'id_man_power_rate' => 1,
                'kelompok'          => $data['kelompok'],
                'nama_komponen'     => $data['nama_komponen'],
                'nilai'             => (float) $data['nilai'],
                'keterangan'        => isset($data['keterangan']) ? $data['keterangan'] : null,
            ]);
        }

        $this->recalculate($id_user);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /** Hapus komponen dan hitung ulang */
    public function delete_component($id, $id_user = null)
    {
        $this->db->trans_start();
        $this->db->where('id', $id)->delete('man_power_rate_component');
        $this->recalculate($id_user);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /** Update parameter jam kerja & porsi operator lalu hitung ulang */
    public function save_parameters($jam_kerja, $porsi_den, $id_user = null)
    {
        $this->db->trans_start();

        $jam   = (float) $jam_kerja > 0 ? (float) $jam_kerja : 173.00;
        $porsi = (float) $porsi_den > 0 ? round(1 / (float) $porsi_den, 4) : 0.3333;

        $this->db->where('id', 1)->update('man_power_rate', [
            'jam_kerja_per_bulan' => $jam,
            'porsi_operator'      => $porsi,
        ]);

        $this->recalculate($id_user);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Hitung ulang seluruh subtotal, rate direct labor, dan Rate 1/3 Operator (CEIL)
     */
    public function recalculate($id_user = null)
    {
        $sum_salary = (float) $this->db->select_sum('nilai')
            ->where('id_man_power_rate', 1)->where('kelompok', 'salary')
            ->get('man_power_rate_component')->row()->nilai;

        $sum_bpjs = (float) $this->db->select_sum('nilai')
            ->where('id_man_power_rate', 1)->where('kelompok', 'bpjs')
            ->get('man_power_rate_component')->row()->nilai;

        $sum_lain = (float) $this->db->select_sum('nilai')
            ->where('id_man_power_rate', 1)->where('kelompok', 'lain_lain')
            ->get('man_power_rate_component')->row()->nilai;

        $total = $sum_salary + $sum_bpjs + $sum_lain;

        $header = $this->db->get_where('man_power_rate', ['id' => 1])->row();
        $jam    = ($header && (float)$header->jam_kerja_per_bulan > 0) ? (float)$header->jam_kerja_per_bulan : 173.00;
        $porsi  = ($header && (float)$header->porsi_operator > 0) ? (float)$header->porsi_operator : 0.3333;

        $rate_dl  = round($total / $jam, 4);
        // Formula spesifikasi: CEIL(rate_direct_labor * 1/3) dibulatkan ke atas ke Rupiah penuh sesudah dikali 1/3
        $rate_1_3 = ceil($rate_dl * $porsi);

        $update = [
            'salary_direct_man_power'      => $sum_salary,
            'bpjs'                         => $sum_bpjs,
            'biaya_lain_lain'              => $sum_lain,
            'total_direct_labor_per_bulan' => $total,
            'rate_direct_labor'            => $rate_dl,
            'rate_1_3_operator'            => $rate_1_3,
            'latest_update'                => date('Y-m-d H:i:s'),
        ];
        if ($id_user) {
            $update['updated_by'] = $id_user;
        }

        $this->db->where('id', 1)->update('man_power_rate', $update);
    }

    /** Ambil data contoh simulasi per product category */
    public function get_simulation_data()
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

        $mpr    = $this->get_rate();
        $faktor = $this->get_faktor_indirect();

        $out = [];
        foreach ($cats as $c) {
            $has_flow = isset($flows[$c->code_lv2]);
            $tmh      = $has_flow ? $flows[$c->code_lv2] : 0.0;
            $has_bor  = isset($borongans[$c->code_lv2]);
            $bor      = $has_bor ? $borongans[$c->code_lv2] : 0.0;

            $op_cost   = $tmh * (float) $mpr->rate_1_3_operator;
            $subtotal  = ($has_flow && $has_bor) ? ($op_cost + $bor) : null;
            $total_mpc = $subtotal !== null ? ($subtotal * $faktor) : null;

            $out[] = (object) [
                'code_lv2'   => $c->code_lv2,
                'nama'       => $c->nama,
                'has_flow'   => $has_flow,
                'tmh'        => $tmh,
                'op_cost'    => $op_cost,
                'has_bor'    => $has_bor,
                'bor'        => $bor,
                'subtotal'   => $subtotal,
                'faktor'     => $faktor,
                'total_mpc'  => $total_mpc,
            ];
        }

        return $out;
    }
}
