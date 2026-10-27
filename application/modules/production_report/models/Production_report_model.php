<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_report_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_spk_list($search = null)
    {
        $this->db->select('
            h.spk_no,
            h.tgl_spk,
            h.status,
            h.catatan,
            COALESCE(SUM(d.target_qty), 0) AS total_target_qty,
            COUNT(d.id) AS total_items,
            GROUP_CONCAT(CONCAT(d.nm_produk_fg, " (", d.target_qty, " pcs)") SEPARATOR ", ") AS produk_fg_summary,
            (SELECT pr.id FROM production_reports pr 
             JOIN tr_spk_material_detail d2 ON d2.id = pr.id_tr_spk_detail 
             WHERE d2.spk_no = h.spk_no ORDER BY pr.id DESC LIMIT 1) AS report_id,
            (SELECT pr.status_draft FROM production_reports pr 
             JOIN tr_spk_material_detail d2 ON d2.id = pr.id_tr_spk_detail 
             WHERE d2.spk_no = h.spk_no ORDER BY pr.id DESC LIMIT 1) AS report_status_draft
        ', false);
        $this->db->from('tr_spk_material_header h');
        $this->db->join('tr_spk_material_detail d', 'd.spk_no = h.spk_no', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('h.spk_no', $search);
            $this->db->or_like('d.nm_produk_fg', $search);
            $this->db->group_end();
        }

        $this->db->group_by('h.spk_no');
        $this->db->order_by('h.tgl_spk', 'DESC');
        $this->db->order_by('h.spk_no', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_spk_header($spk_no)
    {
        $header = $this->db->where('spk_no', $spk_no)->get('tr_spk_material_header')->row_array();
        if ($header) {
            $first_detail = $this->db->where('spk_no', $spk_no)->order_by('urut', 'ASC')->get('tr_spk_material_detail')->row_array();
            $header['primary_spk_detail_id'] = $first_detail ? $first_detail['id'] : null;
        }
        return $header;
    }

    public function get_draft_by_spk($spk_no)
    {
        $draft = $this->db->select('pr.id')
            ->from('production_reports pr')
            ->join('tr_spk_material_detail d', 'd.id = pr.id_tr_spk_detail')
            ->where('d.spk_no', $spk_no)
            ->where('pr.status_draft', 1)
            ->order_by('pr.id', 'DESC')
            ->limit(1)
            ->get()->row_array();

        if ($draft) {
            return $this->get_report_full($draft['id']);
        }
        return null;
    }

    public function get_spk_products($spk_no)
    {
        $this->db->select('
            d.id AS id_tr_spk_detail,
            d.spk_no,
            d.urut,
            d.id_produk_fg,
            d.nm_produk_fg,
            COALESCE(d.no_uniq_id, "") AS no_uniq_id,
            d.target_qty,
            d.berat_per_unit,
            d.total_weight,
            COALESCE(p.id, d.id_produk_fg) AS id_product_lvl_4,
            p.id AS product_lvl_4_id,
            COALESCE(p.weight, d.berat_per_unit, 0) AS weight_standard,
            p.weight AS berat_standar,
            p.length,
            p.wide,
            p.tebal
        ');
        $this->db->from('tr_spk_material_detail d');
        $this->db->join('product_lvl_4 p', 'p.code_lv4 = d.id_produk_fg', 'left');
        $this->db->where('d.spk_no', $spk_no);
        $this->db->order_by('d.urut', 'ASC');

        $res = $this->db->get()->result_array();
        foreach ($res as &$row) {
            if (empty($row['no_uniq_id'])) {
                // Fallback: ambil varian pertama dari ms_bom_detail untuk produk ini
                $b = $this->db->select('d.no_uniq_id')
                    ->from('ms_bom_header h')
                    ->join('ms_bom_detail d', 'd.id_bom = h.id AND d.is_delete = 0')
                    ->where('h.id_produk', $row['id_produk_fg'])
                    ->where('h.is_delete', 0)
                    ->order_by('d.urut', 'ASC')
                    ->limit(1)
                    ->get()->row();
                if ($b && !empty($b->no_uniq_id)) {
                    $row['no_uniq_id'] = $b->no_uniq_id;
                } else {
                    $row['no_uniq_id'] = 'Type 001.1';
                }
            }
        }
        return $res;
    }

    public function get_machines()
    {
        return $this->db->select('id, kd_asset, nm_asset, nm_category')
            ->from('asset')
            ->where('deleted', 'N')
            ->where('category', '4')
            ->order_by('nm_asset', 'ASC')
            ->get()->result_array();
    }

    public function get_employees()
    {
        return $this->db->select('id, nik, nm_karyawan, position, department')
            ->from('employee')
            ->where('status', 'Y')
            ->where('deleted', 'N')
            ->order_by('nm_karyawan', 'ASC')
            ->get()->result_array();
    }

    public function get_all_products($limit = 300)
    {
        return $this->db->select('id, code_lv4, nama, weight, length, wide, tebal')
            ->from('product_lvl_4')
            ->where('status', 1)
            ->where('deleted_date IS NULL')
            ->order_by('nama', 'ASC')
            ->limit($limit)
            ->get()->result_array();
    }

    public function get_cost_rates()
    {
        $rates = $this->db->get('ms_production_cost_rate')->result_array();
        $map = [
            'man_power'       => 400.00,
            'foh'             => 300.00,
            'bahan_pendukung' => 500.00,
            'consumables'     => 200.00,
            'koordinasi'      => 200.00,
            'scrap_pct'       => 0.0200,
        ];
        foreach ($rates as $r) {
            $map[$r['rate_key']] = (float)$r['rate_value'];
        }
        return $map;
    }

    /**
     * Ambil rate Rp/Kg Hasil Costing untuk produk FG dan varian BOM tertentu.
     * Sesuai FR-48 & FR-74: Mengambil 6 komponen dari product_costing_detail (Update Costing terakhir).
     */
    public function get_costing_rates_for_variant($code_lv4_or_id, $no_uniq_id = null)
    {
        $code_lv4 = $code_lv4_or_id;
        if (is_numeric($code_lv4_or_id)) {
            $p = $this->db->select('code_lv4')->where('id', $code_lv4_or_id)->get('product_lvl_4')->row();
            if ($p && !empty($p->code_lv4)) {
                $code_lv4 = $p->code_lv4;
            }
        }

        if (empty($no_uniq_id)) {
            $bom = $this->db->select('d.no_uniq_id')
                ->from('ms_bom_header h')
                ->join('ms_bom_detail d', 'd.id_bom = h.id AND d.is_delete = 0')
                ->where('h.id_produk', $code_lv4)
                ->where('h.is_delete', 0)
                ->order_by('d.urut', 'ASC')
                ->limit(1)
                ->get()->row();
            if ($bom && !empty($bom->no_uniq_id)) {
                $no_uniq_id = $bom->no_uniq_id;
            }
        }

        $costing = null;
        if (!empty($code_lv4)) {
            $this->db->select('
                code_lv4,
                no_uniq_id,
                biaya_proses_man_power_kg,
                biaya_proses_foh_kg,
                biaya_proses_bahan_pendukung_khusus_kg,
                manufacturing_support_consumables_kg,
                manufacturing_support_biaya_koordinasi_kg,
                manufacturing_support_scrap_kg
            ');
            $this->db->from('product_costing_detail');
            $this->db->where('code_lv4', $code_lv4);
            if (!empty($no_uniq_id)) {
                $this->db->where('no_uniq_id', $no_uniq_id);
            }
            $this->db->order_by('id', 'DESC');
            $this->db->limit(1);
            $costing = $this->db->get()->row();
        }

        if ($costing) {
            return [
                'man_power'       => (float)$costing->biaya_proses_man_power_kg,
                'foh'             => (float)$costing->biaya_proses_foh_kg,
                'bahan_pendukung' => (float)$costing->biaya_proses_bahan_pendukung_khusus_kg,
                'consumables'     => (float)$costing->manufacturing_support_consumables_kg,
                'koordinasi'      => (float)$costing->manufacturing_support_biaya_koordinasi_kg,
                'scrap'           => (float)$costing->manufacturing_support_scrap_kg,
                'no_uniq_id'      => $costing->no_uniq_id,
                'source'          => 'costing_detail',
            ];
        }

        // Fallback jika belum pernah di-Update Costing untuk varian ini
        $legacy = $this->get_cost_rates();
        return [
            'man_power'       => (float)($legacy['man_power'] ?? 400.0),
            'foh'             => (float)($legacy['foh'] ?? 300.0),
            'bahan_pendukung' => (float)($legacy['bahan_pendukung'] ?? 500.0),
            'consumables'     => (float)($legacy['consumables'] ?? 200.0),
            'koordinasi'      => (float)($legacy['koordinasi'] ?? 200.0),
            'scrap'           => 100.0,
            'no_uniq_id'      => $no_uniq_id ?: 'Type 001.1',
            'source'          => 'fallback',
        ];
    }

    public function get_coils_by_source($source)
    {
        $this->db->select('
            c.id,
            c.id_material,
            c.nm_material,
            c.trade_name,
            c.kode_internal,
            c.kd_gudang,
            c.no_coil,
            c.gross_weight,
            c.net_weight,
            c.kulit AS berat_kulit,
            c.clamp_ring AS berat_clamp,
            c.net_weight AS weight,
            c.harga_beli,
            c.length AS total_meter,
            c.status_proses
        ');
        $this->db->from('warehouse_stock_coil c');

        if ($source === 'unpack') {
            $this->db->group_start();
            $this->db->where('c.status_proses', 'unpacked');
            $this->db->or_where_in('c.kd_gudang', ['PRT', 'PRO']);
            $this->db->group_end();
        } elseif ($source === 'wip') {
            $this->db->group_start();
            $this->db->where('c.status_proses', 'wip');
            $this->db->or_where('c.Kd_gudang', 'WIP');
            $this->db->group_end();
        } elseif ($source === 'hold') {
            $this->db->group_start();
            $this->db->where('c.status_proses', 'on_hold');
            $this->db->or_where('c.kd_gudang', 'HLD');
            $this->db->group_end();
        }

        $this->db->where('c.status', 1);
        $this->db->order_by('c.id', 'DESC');
        $this->db->limit(100);

        return $this->db->get()->result_array();
    }

    public function get_coil_extra_info($no_coil)
    {
        $res = [
            'berat_kulit' => 0.00,
            'berat_clamp' => 0.00
        ];

        $weigh = $this->db->select('c.berat_kulit, c.berat_clamp_ring')
            ->from('tr_coil_preweigh p')
            ->join('tr_coil_preweigh_component c', 'c.preweigh_no = p.preweigh_no', 'left')
            ->where('p.no_coil', $no_coil)
            ->order_by('p.created_at', 'DESC')
            ->limit(1)
            ->get()->row_array();

        if ($weigh) {
            $res['berat_kulit'] = (float) $weigh['berat_kulit'];
            $res['berat_clamp'] = (float) $weigh['berat_clamp_ring'];
        }

        return $res;
    }

    public function save_production_report($header_data, $materials, $items, $scraps, $existing_report_id = null)
    {
        $this->db->trans_start();

        // 1. Tentukan fallback Varian BOM dari SPK untuk Stok Bebas & KW2 (FR-48, FR-74)
        $spk_prod_fallback = null;
        $spk_uniq_fallback = null;
        if (!empty($header_data['id_tr_spk_detail'])) {
            $spk_d = $this->db->select('id_produk_fg, no_uniq_id')
                ->where('id', $header_data['id_tr_spk_detail'])
                ->get('tr_spk_material_detail')
                ->row();
            if ($spk_d) {
                $spk_prod_fallback = $spk_d->id_produk_fg;
                $spk_uniq_fallback = $spk_d->no_uniq_id;
            }
        }

        // 2. Petakan Costbook (harga_beli) per Coil
        $coil_cb_map = [];
        $coil_codes = [];
        if (!empty($materials)) {
            foreach ($materials as $m) {
                if (!empty($m['coil_code'])) $coil_codes[] = $m['coil_code'];
            }
        }
        if (!empty($coil_codes)) {
            $coils_db = $this->db->select('no_coil, harga_beli')
                ->where_in('no_coil', $coil_codes)
                ->get('warehouse_stock_coil')
                ->result_array();
            foreach ($coils_db as $cd) {
                $coil_cb_map[$cd['no_coil']] = (float)$cd['harga_beli'];
            }
        }

        // Fallback costbook pertama jika ada item/scrap tanpa coil spesifik
        $default_cb = count($coil_cb_map) ? reset($coil_cb_map) : 0;

        // 3. Kalkulasi HPP Items (KW1, Stok Bebas, KW2) memakai Rp/Kg Hasil Costing varian BOM
        $total_cogm_material = 0;
        $total_cogm_mp       = 0;
        $total_cogm_foh      = 0;
        $total_cogm_bahan    = 0;
        $total_cogm_cons     = 0;
        $total_cogm_koor     = 0;
        $total_cogm_scrap    = 0;
        $total_fg_all        = 0;
        $sum_inv_fg          = 0;
        $sum_loss_kw2        = 0;

        if (!empty($items) && is_array($items)) {
            foreach ($items as &$it) {
                $coil_code = !empty($it['source_material_coil']) ? $it['source_material_coil'] : '';
                $cb = isset($coil_cb_map[$coil_code]) ? $coil_cb_map[$coil_code] : $default_cb;
                $it['costbook_rate'] = $cb;

                $berat_pcs = (float)$it['berat_per_pcs'];
                $qty       = (int)$it['qty'];

                // Tentukan produk & varian BOM untuk item ini
                $prod_id = !empty($it['product_lvl_4_id']) ? $it['product_lvl_4_id'] : $spk_prod_fallback;
                $uniq    = !empty($it['no_uniq_id']) ? $it['no_uniq_id'] : $spk_uniq_fallback;

                // Ambil rate Hasil Costing varian BOM terpilih (FR-48)
                $c_rates = $this->get_costing_rates_for_variant($prod_id, $uniq);
                $it['no_uniq_id'] = $c_rates['no_uniq_id'];

                $r_mp    = (float)$c_rates['man_power'];
                $r_foh   = (float)$c_rates['foh'];
                $r_bahan = (float)$c_rates['bahan_pendukung'];
                $r_cons  = (float)$c_rates['consumables'];
                $r_koor  = (float)$c_rates['koordinasi'];
                $r_scrap = (float)$c_rates['scrap'];

                // Biaya per pcs:
                // Material = Berat/Pcs * Costbook
                // 6 komponen lainnya = Berat/Pcs * Rp/Kg Hasil Costing varian BOM
                $c_mat   = $berat_pcs * $cb;
                $c_mp    = $berat_pcs * $r_mp;
                $c_foh   = $berat_pcs * $r_foh;
                $c_bahan = $berat_pcs * $r_bahan;
                $c_cons  = $berat_pcs * $r_cons;
                $c_koor  = $berat_pcs * $r_koor;
                $c_scrap = $berat_pcs * $r_scrap; // Diperbaiki: Berat/Pcs * Rp/Kg Scrap
                $c_fg_pcs = $c_mat + $c_mp + $c_foh + $c_bahan + $c_cons + $c_koor + $c_scrap;

                $it['material_cost_per_pcs']        = $c_mat;
                $it['main_power_cost_per_pcs']      = $c_mp;
                $it['foh_cost_per_pcs']             = $c_foh;
                $it['bahan_pendukung_cost_per_pcs'] = $c_bahan;
                $it['consumables_cost_per_pcs']     = $c_cons;
                $it['coordinator_cost_per_pcs']     = $c_koor;
                $it['scrap_cost_per_pcs']           = $c_scrap;

                // Total biaya item
                $t_mat   = $c_mat * $qty;
                $t_mp    = $c_mp * $qty;
                $t_foh   = $c_foh * $qty;
                $t_bahan = $c_bahan * $qty;
                $t_cons  = $c_cons * $qty;
                $t_koor  = $c_koor * $qty;
                $t_scrap = $c_scrap * $qty;
                $t_fg    = $c_fg_pcs * $qty;

                $it['material_cost_total']        = $t_mat;
                $it['main_power_cost_total']      = $t_mp;
                $it['foh_cost_total']             = $t_foh;
                $it['bahan_pendukung_cost_total'] = $t_bahan;
                $it['consumables_cost_total']     = $t_cons;
                $it['coordinator_cost_total']     = $t_koor;
                $it['scrap_cost_total']           = $t_scrap;
                $it['total_hpp_item']             = $t_fg;

                $total_cogm_material += $t_mat;
                $total_cogm_mp       += $t_mp;
                $total_cogm_foh      += $t_foh;
                $total_cogm_bahan    += $t_bahan;
                $total_cogm_cons     += $t_cons;
                $total_cogm_koor     += $t_koor;
                $total_cogm_scrap    += $t_scrap;
                $total_fg_all        += $t_fg;

                // Logika Inventory KW1 vs KW2
                $cat = $it['category_type'];
                if ($cat === 'kw_2_internal' || $cat === 'kw_2_supplier') {
                    $inv80  = $t_fg * 0.8;
                    $loss20 = $t_fg * 0.2;
                    $it['inventory_value_80'] = $inv80;
                    $it['loss_cost_20']       = $loss20;
                    $sum_inv_fg   += $inv80;
                    $sum_loss_kw2 += $loss20;
                } else {
                    $it['inventory_value_80'] = $t_fg;
                    $it['loss_cost_20']       = 0;
                    $sum_inv_fg += $t_fg;
                }
            }
        }

        // 4. Kalkulasi Scrap, Sisa Coil, dan Hold Coil
        $sum_inv_scrap = 0;
        $sum_foh_scrap = 0;
        $sum_inv_sisa  = 0;
        $sum_inv_hold  = 0;

        if (!empty($scraps) && is_array($scraps)) {
            foreach ($scraps as &$sc) {
                $coil_code = !empty($sc['target_coil_code']) ? $sc['target_coil_code'] : '';
                $cb = isset($coil_cb_map[$coil_code]) ? $coil_cb_map[$coil_code] : $default_cb;
                $sc['costbook_rate'] = $cb;

                $berat = (float)$sc['berat_total'];
                $nilai = $berat * $cb;
                $sc['total_nilai'] = $nilai;

                $st = $sc['scrap_type'];
                if ($st === 'sisa_coil') {
                    $sc['inventory_value_40'] = $nilai; // 100% ke WIP
                    $sc['foh_burden_60']      = 0;
                    $sum_inv_sisa += $nilai;
                } elseif ($st === 'hold_coil') {
                    $sc['inventory_value_40'] = $nilai; // 100% ke Hold
                    $sc['foh_burden_60']      = 0;
                    $sum_inv_hold += $nilai;
                } else {
                    // Murni scrap waste: 40% persediaan waste, 60% beban FOH
                    $inv40 = $nilai * 0.4;
                    $foh60 = $nilai * 0.6;
                    $sc['inventory_value_40'] = $inv40;
                    $sc['foh_burden_60']      = $foh60;
                    $sum_inv_scrap += $inv40;
                    $sum_foh_scrap += $foh60;
                }
            }
        }

        // 5. Selisih Berat Audit Material
        $selisih_kg = isset($header_data['summary_selisih']) ? (float)$header_data['summary_selisih'] : 0;
        $selisih_weight_val = $selisih_kg * $default_cb;

        // 6. Update Header Data dengan nilai HPP agregat
        $total_cogm_all = $total_cogm_material + $total_cogm_mp + $total_cogm_foh + $total_cogm_bahan + $total_cogm_cons + $total_cogm_koor + $total_cogm_scrap;
        $header_data['total_hpp_keseluruhan']  = $total_fg_all;
        $header_data['total_cogm']             = $total_cogm_all;
        $header_data['total_inventory_fg']     = $sum_inv_fg;
        $header_data['total_loss_kw2']         = $sum_loss_kw2;
        $header_data['total_inventory_scrap']  = $sum_inv_scrap;
        $header_data['total_beban_foh_scrap']  = $sum_foh_scrap;
        $header_data['total_inventory_sisa']   = $sum_inv_sisa;
        $header_data['total_inventory_hold']   = $sum_inv_hold;
        $header_data['selisih_weight_value']   = $selisih_weight_val;

        // 7. Simpan Header & Detail
        if (!empty($existing_report_id)) {
            $report_id = $existing_report_id;
            $this->db->where('id', $report_id)->update('production_reports', $header_data);

            $this->db->where('production_report_id', $report_id)->delete('production_report_materials');
            $this->db->where('production_report_id', $report_id)->delete('production_report_items');
            $this->db->where('production_report_id', $report_id)->delete('production_report_scraps');
        } else {
            $this->db->insert('production_reports', $header_data);
            $report_id = $this->db->insert_id();
        }

        if (!empty($materials) && is_array($materials)) {
            foreach ($materials as &$m) {
                $m['production_report_id'] = $report_id;
            }
            $this->db->insert_batch('production_report_materials', $materials);
        }

        if (!empty($items) && is_array($items)) {
            foreach ($items as &$it) {
                $it['production_report_id'] = $report_id;
                unset($it['costbook_rate'], $it['inventory_value_80'], $it['loss_cost_20']);
            }
            $this->db->insert_batch('production_report_items', $items);
        }

        if (!empty($scraps) && is_array($scraps)) {
            foreach ($scraps as &$sc) {
                $sc['production_report_id'] = $report_id;
            }
            $this->db->insert_batch('production_report_scraps', $scraps);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        return $report_id;
    }

    public function get_report_full($report_id)
    {
        $header = $this->db->where('id', $report_id)->get('production_reports')->row_array();
        if (!$header) return null;

        $materials = $this->db->where('production_report_id', $report_id)->get('production_report_materials')->result_array();
        $items = $this->db->where('production_report_id', $report_id)->get('production_report_items')->result_array();
        $scraps = $this->db->where('production_report_id', $report_id)->get('production_report_scraps')->result_array();

        $spk_detail = $this->db->where('id', $header['id_tr_spk_detail'])->get('tr_spk_material_detail')->row_array();
        $spk_header = $spk_detail ? $this->get_spk_header($spk_detail['spk_no']) : null;
        $mesin = $this->db->where('id', $header['id_asset_machine'])->get('asset')->row_array();

        return [
            'header'     => $header,
            'spk_header' => $spk_header,
            'spk_detail' => $spk_detail,
            'mesin'      => $mesin,
            'materials'  => $materials,
            'items'      => $items,
            'scraps'     => $scraps,
        ];
    }
}
