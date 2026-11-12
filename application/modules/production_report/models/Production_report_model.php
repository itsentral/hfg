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
        $this->db->where("(
            h.status = 'Released'
            OR EXISTS (
                SELECT 1
                FROM production_reports pr_ex
                JOIN tr_spk_material_detail d_ex ON d_ex.id = pr_ex.id_tr_spk_detail
                WHERE d_ex.spk_no = h.spk_no
            )
        )", null, false);

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
            COALESCE(NULLIF(p.panjang, 0), NULLIF(p.length, 0), 0) AS panjang,
            p.length,
            p.panjang AS panjang_m,
            p.wide,
            p.tebal
        ');
        $this->db->from('tr_spk_material_detail d');
        $this->db->join('product_lvl_4 p', 'p.code_lv4 = d.id_produk_fg', 'left');
        $this->db->where('d.spk_no', $spk_no);
        $this->db->order_by('d.urut', 'ASC');

        $res = $this->db->get()->result_array();
        foreach ($res as &$row) {
            // Ambil varian pertama dan berat_per_meter dari ms_bom_detail untuk produk ini
            $b = $this->db->select('d.no_uniq_id, d.berat_per_meter')
                ->from('ms_bom_header h')
                ->join('ms_bom_detail d', 'd.id_bom = h.id AND d.is_delete = 0')
                ->where('h.id_produk', $row['id_produk_fg'])
                ->where('h.is_delete', 0)
                ->order_by('d.urut', 'ASC')
                ->limit(1)
                ->get()->row();
            if ($b && !empty($b->no_uniq_id)) {
                $row['no_uniq_id']    = !empty($row['no_uniq_id']) ? $row['no_uniq_id'] : $b->no_uniq_id;
                $row['std_per_meter'] = (float)($b->berat_per_meter ?? 0);
            } else {
                if (empty($row['no_uniq_id'])) {
                    $row['no_uniq_id'] = 'Type 001.1';
                }
                $row['std_per_meter'] = 0;
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
        $sql = "SELECT p.id, p.code_lv4, p.nama, p.weight, p.length, p.panjang,
                       COALESCE(NULLIF(p.panjang, 0), NULLIF(p.length, 0), 0) AS panjang_produk,
                       p.wide, p.tebal,
                       (SELECT d.berat_per_meter 
                        FROM ms_bom_header h 
                        JOIN ms_bom_detail d ON d.id_bom = h.id AND d.is_delete = 0 
                        WHERE h.id_produk = p.code_lv4 AND h.is_delete = 0 
                        ORDER BY d.urut ASC LIMIT 1) AS std_per_meter
                FROM product_lvl_4 p
                WHERE p.status = 1 AND p.deleted_date IS NULL
                ORDER BY p.nama ASC
                LIMIT " . (int)$limit;
        $products = $this->db->query($sql)->result_array();
        foreach ($products as &$prod) {
            $prod['panjang']       = (float)($prod['panjang_produk'] ?? 0);
            $prod['std_per_meter'] = (float)($prod['std_per_meter'] ?? 0);
        }
        return $products;
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

    public function save_production_report($header_data, $materials, $items, $scraps, $claims = [], $existing_report_id = null, $user_id = null)
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

        // 2. Petakan Costbook (harga_beli) per Coil dari warehouse_stock_coil (fallback warehouse_stock)
        $coil_cb_map = [];
        $coil_ids    = [];
        $coil_codes  = [];
        if (!empty($materials)) {
            foreach ($materials as $m) {
                if (!empty($m['id_unpack_baby_coil'])) $coil_ids[]   = (int)$m['id_unpack_baby_coil'];
                if (!empty($m['coil_code']))            $coil_codes[] = trim($m['coil_code']);
            }
        }

        $coils_db = [];
        if (!empty($coil_ids) || !empty($coil_codes)) {
            $this->db->select('id, no_coil, id_material, nm_material, net_weight, harga_beli');
            $this->db->from('warehouse_stock_coil');
            $this->db->group_start();
            if (!empty($coil_ids)) {
                $this->db->where_in('id', $coil_ids);
            }
            if (!empty($coil_codes)) {
                if (!empty($coil_ids)) {
                    $this->db->or_where_in('no_coil', $coil_codes);
                } else {
                    $this->db->where_in('no_coil', $coil_codes);
                }
            }
            $this->db->group_end();
            $coils_db = $this->db->get()->result_array();
        }

        foreach ($coils_db as $cd) {
            $cb = (float)$cd['harga_beli'];
            // Jika harga_beli di warehouse_stock_coil masih 0, tarik dari master warehouse_stock
            if ($cb <= 0 && !empty($cd['id_material'])) {
                $master_stock = $this->db->select('harga_beli')
                    ->where('code_lv4', $cd['id_material'])
                    ->where('harga_beli >', 0)
                    ->order_by('id', 'ASC')
                    ->limit(1)
                    ->get('warehouse_stock')
                    ->row();
                if ($master_stock && (float)$master_stock->harga_beli > 0) {
                    $cb = (float)$master_stock->harga_beli;
                    // Sinkronkan kembali ke warehouse_stock_coil agar data coil tidak 0 lagi
                    $this->db->where('id', $cd['id'])->update('warehouse_stock_coil', [
                        'harga_beli'  => $cb,
                        'total_nilai' => round((float)$cd['net_weight'] * $cb, 2)
                    ]);
                }
            }

            if (!empty($cd['no_coil'])) {
                $coil_cb_map[$cd['no_coil']]       = $cb;
                $coil_cb_map[trim($cd['no_coil'])] = $cb;
            }
            $coil_cb_map[(string)$cd['id']] = $cb;
        }

        // Tentukan fallback costbook: cari yang > 0 terlebih dahulu
        $default_cb = 0;
        foreach ($coil_cb_map as $val) {
            if ($val > 0) {
                $default_cb = $val;
                break;
            }
        }

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

        // 6. Update Header Data dengan nilai HPP & Field Jurnal Siap Pakai (GL Interface)
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

        // --- Field Jurnal Siap Pakai untuk Master Jurnal (GL Interface) ---
        // Semua field jurnal di-round seragam ke 2 desimal agar seimbang (Debit = Kredit)
        // A. Kelompok COGM
        $header_data['cogm_material']            = round($total_cogm_material, 2);
        $header_data['cogm_labor_cost_pc']       = round($total_cogm_mp, 2);
        $header_data['cogm_foh_pc']              = round($total_cogm_foh, 2);
        $header_data['cogm_bpp_pc']              = round($total_cogm_bahan, 2);
        $header_data['cogm_consumable_pc']       = round($total_cogm_cons, 2);
        $header_data['cogm_biaya_koordinasi_pc'] = round($total_cogm_koor, 2);
        $header_data['cogm_biaya_scrap_pc']      = round($total_cogm_scrap, 2);
        $header_data['cogm_loss_kw2_pc']         = round($sum_loss_kw2, 2);

        // B. Kelompok Persediaan (Inventory)
        $val_mat_wip = 0;
        $val_mat_unpack_hold = 0;
        if (!empty($materials) && is_array($materials)) {
            foreach ($materials as $m) {
                $c_code = !empty($m['coil_code']) ? trim($m['coil_code']) : '';
                $cb = isset($coil_cb_map[$c_code]) && $coil_cb_map[$c_code] > 0 ? $coil_cb_map[$c_code] : $default_cb;
                $val = (float)($m['nett_weight_packing'] ?? 0) * $cb;
                if (!empty($m['source_warehouse']) && $m['source_warehouse'] === 'wip') {
                    $val_mat_wip += $val;
                } else {
                    $val_mat_unpack_hold += $val;
                }
            }
        }
        $header_data['persediaan_waste']         = round($sum_inv_scrap, 2);
        $header_data['journal_inventory_sisa']   = round($sum_inv_sisa, 2); // Khusus Jurnal (WIP Manufacturing Sisa)
        $header_data['wip_manufactur']           = round($val_mat_wip, 2);
        $header_data['persediaan_bahan_baku']    = round($val_mat_unpack_hold, 2);

        // C. Kelompok Biaya Aktual
        $header_data['biaya_scrap_60']           = round($sum_foh_scrap, 2);
        $header_data['biaya_kw2_20']             = round($sum_loss_kw2, 2);

        // D. Kelompok Selisih Audit Material (Khusus Jurnal)
        $header_data['journal_selisih_audit']    = round($selisih_weight_val, 2);

        // E. Kelompok Cek Balancing HPP vs COGM (Khusus Jurnal)
        $header_data['journal_inventory_fg']     = round($sum_inv_fg, 2);
        $header_data['journal_total_cogm']        = round($total_cogm_all, 2);

        // F. Kelompok Hutang Crossing Akhir Bulan
        $tot_crossing = round($total_cogm_mp + $total_cogm_foh + $total_cogm_bahan + $total_cogm_cons + $total_cogm_koor + $total_cogm_scrap, 2);
        $header_data['hutang_labor_cost_pc']       = round($total_cogm_mp, 2);
        $header_data['hutang_foh_pc']              = round($total_cogm_foh, 2);
        $header_data['hutang_bpp_pc']              = round($total_cogm_bahan, 2);
        $header_data['hutang_consumable_pc']       = round($total_cogm_cons, 2);
        $header_data['hutang_biaya_koordinasi_pc'] = round($total_cogm_koor, 2);
        $header_data['hutang_biaya_scrap_pc']      = round($total_cogm_scrap, 2);

        $header_data['hutang_crossing_labor']      = round($total_cogm_mp, 2);
        $header_data['hutang_crossing_foh']        = round($total_cogm_foh, 2);
        $header_data['hutang_crossing_bpp']        = round($total_cogm_bahan, 2);
        $header_data['hutang_crossing_consumable'] = round($total_cogm_cons, 2);
        $header_data['hutang_crossing_koordinasi'] = round($total_cogm_koor, 2);
        $header_data['hutang_crossing_scrap']      = round($total_cogm_scrap, 2);
        $header_data['total_hutang_crossing']      = $tot_crossing;

        // Filter kolom agar hanya field yang benar-benar ada di tabel database yang dikirim ke query
        $table_fields = $this->db->list_fields('production_reports');
        $header_data = array_intersect_key($header_data, array_flip($table_fields));

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
            }
            $this->db->insert_batch('production_report_items', $items);
        }

        if (!empty($scraps) && is_array($scraps)) {
            foreach ($scraps as &$sc) {
                $sc['production_report_id'] = $report_id;
            }
            $this->db->insert_batch('production_report_scraps', $scraps);
        }

        // 7b. Simpan Claim Material & Bukti Dokumen (FR-94, FR-98)
        $existing_claims = [];
        if (!empty($existing_report_id)) {
            $ex_rows = $this->db->where('production_report_id', $report_id)->get('claim_material')->result_array();
            foreach ($ex_rows as $er) {
                $key = $er['asal'] . ':' . trim($er['no_coil_packing_list']);
                $existing_claims[$key] = $er;
            }
        }

        $current_keys = [];
        if (!empty($claims) && is_array($claims)) {
            foreach ($claims as $cl) {
                $asal       = !empty($cl['asal']) ? $cl['asal'] : 'Hold Coil';
                $no_coil_pl = !empty($cl['no_coil_packing_list']) ? trim($cl['no_coil_packing_list']) : '';
                $key        = $asal . ':' . $no_coil_pl;
                $current_keys[] = $key;

                $claim_data = [
                    'production_report_id' => $report_id,
                    'id_baby_coil'         => !empty($cl['id_baby_coil']) ? (int)$cl['id_baby_coil'] : null,
                    'id_gudang_material_hold' => null,
                    'id_suplier'           => !empty($cl['id_suplier']) ? $cl['id_suplier'] : '-',
                    'id_material'          => !empty($cl['id_material']) ? $cl['id_material'] : '-',
                    'no_po'                => !empty($cl['no_po']) ? $cl['no_po'] : '-',
                    'no_coil_packing_list' => $no_coil_pl,
                    'asal'                 => $asal,
                    'problem'              => !empty($cl['problem']) ? trim($cl['problem']) : '',
                    'alasan_produksi'      => !empty($cl['alasan_produksi']) ? trim($cl['alasan_produksi']) : '',
                    'berat_kg'             => !empty($cl['berat_kg']) ? (float)$cl['berat_kg'] : 0,
                    'harga_beli_per_kg'    => !empty($cl['harga_beli_per_kg']) ? (float)$cl['harga_beli_per_kg'] : 0,
                    'costbook_per_kg'      => !empty($cl['costbook_per_kg']) ? (float)$cl['costbook_per_kg'] : 0,
                    'price_per_unit_po'    => !empty($cl['price_per_unit_po']) ? (float)$cl['price_per_unit_po'] : 0,
                    'tgl_produksi'         => !empty($header_data['tgl_produksi']) ? $header_data['tgl_produksi'] : date('Y-m-d'),
                    'id_gudang_asal'       => !empty($cl['id_gudang_asal']) ? $cl['id_gudang_asal'] : null,
                    'kd_gudang_asal'       => !empty($cl['kd_gudang_asal']) ? $cl['kd_gudang_asal'] : null,
                ];

                if (isset($existing_claims[$key])) {
                    $cl_id = $existing_claims[$key]['id'];
                    $curr_status = $existing_claims[$key]['status_claim'];
                    // Hanya perbarui data inti jika status masih 'Claim Baru' atau 'Baru'
                    if (in_array($curr_status, ['Claim Baru', 'Baru'])) {
                        $this->db->where('id', $cl_id)->update('claim_material', $claim_data);
                    }
                } else {
                    $claim_data['no_claim']     = $this->generate_claim_no();
                    $claim_data['status_claim'] = 'Claim Baru';
                    $claim_data['created_at']   = date('Y-m-d H:i:s');
                    $claim_data['created_by']   = $user_id ? (string)$user_id : '1';
                    $this->db->insert('claim_material', $claim_data);
                    $cl_id = $this->db->insert_id();
                }

                // Simpan berkas bukti dokumen pendukung (FR-94)
                if (!empty($cl['bukti']) && is_array($cl['bukti'])) {
                    foreach ($cl['bukti'] as $b) {
                        if (empty($b['file_hash_name'])) continue;
                        $b_exists = $this->db->where('id_claim_material', $cl_id)
                            ->where('file_hash_name', $b['file_hash_name'])
                            ->count_all_results('claim_material_bukti');
                        if ($b_exists == 0) {
                            $this->db->insert('claim_material_bukti', [
                                'id_claim_material'  => $cl_id,
                                'file_original_name' => !empty($b['file_original_name']) ? $b['file_original_name'] : $b['file_hash_name'],
                                'file_hash_name'     => $b['file_hash_name'],
                                'file_size'          => !empty($b['file_size']) ? (int)$b['file_size'] : 0,
                                'file_type'          => !empty($b['file_type']) ? $b['file_type'] : null,
                                'created_at'         => date('Y-m-d H:i:s'),
                                'created_by'         => $user_id ? (string)$user_id : '1'
                            ]);
                        }
                    }
                }

                // Bila asal Hold Coil, update status_proses di warehouse_stock_coil menjadi 'on_hold' (FR-95)
                if ($asal === 'Hold Coil' && !empty($claim_data['id_baby_coil'])) {
                    $this->db->where('id', $claim_data['id_baby_coil'])->update('warehouse_stock_coil', [
                        'status_proses' => 'on_hold'
                    ]);
                }
            }
        }

        // Hapus claim yang statusnya masih 'Claim Baru' bila baris sumbernya telah dihapus dari form (FR-98 butir f)
        if (!empty($existing_claims)) {
            foreach ($existing_claims as $k => $old_cl) {
                if (!in_array($k, $current_keys) && in_array($old_cl['status_claim'], ['Claim Baru', 'Baru'])) {
                    $this->db->where('id', $old_cl['id'])->delete('claim_material');
                }
            }
        }

        // 8. Jika status_draft == 0 (Final / Submitted):
        // a. Catat pengeluaran stok material ke history (status OUT)
        // b. Catat penerimaan produk Finish Good ke stok FG (fg_stock & fg_stock_ledger) (status IN)
        // c. Catat penerimaan Tong Coil & Scrap ke stok FG/Waste (fg_stock & fg_stock_ledger) (status IN)
        // d. Catat Sisa Coil ke Gudang WIP & Hold Coil ke Gudang Material Hold (warehouse_stock_coil) (status IN)
        // e. Catat rincian pemakaian per coil ke Kartu Pemakaian Material (warehouse_coil_kartu_stok)
        if (isset($header_data['status_draft']) && (int)$header_data['status_draft'] === 0) {
            $this->process_production_stock_out($report_id, $header_data, $materials, $user_id);
            $this->process_production_fg_stock_in($report_id, $header_data, $items, $user_id);
            $this->process_production_scrap_fg_stock_in($report_id, $header_data, $scraps, $materials, $user_id);
            $this->process_production_sisa_hold_coil_in($report_id, $header_data, $scraps, $materials, $user_id);
            $this->process_production_coil_kartu_stok($report_id, $header_data, $materials, $items, $scraps, $user_id);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        return $report_id;
    }

    /**
     * Catat pengeluaran stok material ke history (status OUT) dan potong stok buku agregat.
     * Aturan:
     * - Hanya mengurangi nilai transaksi (qty_stock dan total_nilai berkurang).
     * - JANGAN mengubah nilai costbook / harga_beli (harga satuan per kg tetap).
     * - Status transaksi OUT (qty_akhir < qty_awal).
     * - Idempoten per kode_trans (SPK).
     */
    public function process_production_stock_out($report_id, $header_data, $materials, $user_id = null)
    {
        if (empty($materials) || !is_array($materials)) {
            return;
        }

        // 1. Ambil nomor SPK sebagai kode transaksi utama
        $spk_no = 'PR-' . str_pad($report_id, 4, '0', STR_PAD_LEFT);
        if (!empty($header_data['id_tr_spk_detail'])) {
            $spk_d = $this->db->select('spk_no')->where('id', $header_data['id_tr_spk_detail'])->get('tr_spk_material_detail')->row();
            if ($spk_d && !empty($spk_d->spk_no)) {
                $spk_no = $spk_d->spk_no;
            }
        }

        $kode_trans   = $spk_no;
        $tgl_produksi = !empty($header_data['tgl_produksi']) ? $header_data['tgl_produksi'] : date('Y-m-d');
        $now          = date('Y-m-d H:i:s');
        $user_id      = $user_id ?: ($this->auth ? $this->auth->user_id() : 1);

        // 2. Cek apakah kode_trans ini sudah pernah dicatat di transaction summary (idempoten)
        $already = $this->db->get_where('warehouse_stock_transaction_summary', [
            'kode_trans' => $kode_trans,
        ])->row();

        if ($already) {
            return;
        }

        // 3. Kumpulkan data pemakaian per material & gudang
        $material_groups = [];

        foreach ($materials as $m) {
            $coil_code = !empty($m['coil_code']) ? trim($m['coil_code']) : '';
            $coil_id   = !empty($m['id_unpack_baby_coil']) ? (int)$m['id_unpack_baby_coil'] : null;

            // Lookup coil info dari warehouse_stock_coil
            $coil = null;
            if ($coil_id) {
                $coil = $this->db->where('id', $coil_id)->get('warehouse_stock_coil')->row_array();
            }
            if (!$coil && $coil_code) {
                $coil = $this->db->where('no_coil', $coil_code)->order_by('id', 'DESC')->get('warehouse_stock_coil')->row_array();
            }

            if (!$coil) {
                continue;
            }

            $id_mat = $coil['id_material'];
            $nm_mat = !empty($coil['nm_material']) ? $coil['nm_material'] : (!empty($m['material_name']) ? $m['material_name'] : $id_mat);
            $net_kg = !empty($m['nett_weight_packing']) && (float)$m['nett_weight_packing'] > 0
                ? (float)$m['nett_weight_packing']
                : (float)$coil['net_weight'];

            if (!isset($material_groups[$id_mat])) {
                $material_groups[$id_mat] = [
                    'id_material'      => $id_mat,
                    'nm_material'      => $nm_mat,
                    'total_qty'        => 0,
                    'coil_count'       => 0,
                    'source_id_gudang' => $coil['id_gudang'],
                    'source_kd_gudang' => $coil['kd_gudang'],
                    'coils'            => [],
                    'coil_ids'         => [],
                    'coil_codes'       => [],
                ];
            }

            $material_groups[$id_mat]['total_qty'] += $net_kg;
            $material_groups[$id_mat]['coil_count']++;
            $material_groups[$id_mat]['coils'][] = array_merge($coil, [
                'net_weight_used' => $net_kg,
            ]);
            $material_groups[$id_mat]['coil_ids'][] = $coil['id'];
            $material_groups[$id_mat]['coil_codes'][] = $coil['no_coil'];
        }

        // 4. Eksekusi pemotongan stok & pencatatan history per material
        foreach ($material_groups as $id_mat => $grp) {
            $qty_pakai = (float)$grp['total_qty'];
            if ($qty_pakai <= 0) continue;

            // Cari baris warehouse_stock gudang sumber
            // Prioritas: Gudang PRO / PUS / SLI
            $stock = null;
            if (!empty($grp['source_kd_gudang']) && in_array($grp['source_kd_gudang'], ['PRO', 'SLI', 'PUS'])) {
                $stock = $this->db->query(
                    "SELECT * FROM warehouse_stock WHERE code_lv4 = ? AND kd_gudang = ? LIMIT 1 FOR UPDATE",
                    [$id_mat, $grp['source_kd_gudang']]
                )->row_array();
            }
            if (!$stock) {
                // Default ke gudang PRO (id_gudang = 1)
                $stock = $this->db->query(
                    "SELECT * FROM warehouse_stock WHERE code_lv4 = ? AND (id_gudang = 1 OR kd_gudang = 'PRO') LIMIT 1 FOR UPDATE",
                    [$id_mat]
                )->row_array();
            }
            if (!$stock) {
                // Fallback baris pertama apapun gudangnya untuk material ini
                $stock = $this->db->query(
                    "SELECT * FROM warehouse_stock WHERE code_lv4 = ? LIMIT 1 FOR UPDATE",
                    [$id_mat]
                )->row_array();
            }

            if (!$stock) {
                continue;
            }

            $qty_awal   = (float)$stock['qty_stock'];
            $saldo_awal = (float)$stock['total_nilai'];
            $costbook   = (float)$stock['harga_beli']; // Nilai costbook / harga beli lama

            // Hitung nilai transaksi pengeluaran (OUT)
            $total_harga = round($qty_pakai * $costbook, 2);
            $qty_akhir   = $qty_awal - $qty_pakai;
            $saldo_akhir = $saldo_awal - $total_harga;
            $harga_lama  = $costbook;

            // UPDATE WAREHOUSE_STOCK:
            // Hanya mengurangi nilai transaksi (qty_stock & total_nilai)
            // JANGAN MENGUBAH harga_beli / costbook!
            $this->db->where('id', $stock['id'])->update('warehouse_stock', [
                'qty_stock'   => $qty_akhir,
                'total_nilai' => $saldo_akhir,
                // harga_beli TETAP tidak diubah
                'update_by'   => $user_id,
                'update_date' => $now,
            ]);

            // INSERT WAREHOUSE_STOCK_TRANSACTION_SUMMARY (Buku Mutasi / History Stok)
            // Karena qty_akhir < qty_awal, maka di UI otomatis berstatus OUT
            $this->db->insert('warehouse_stock_transaction_summary', [
                'kode_trans'    => $kode_trans,
                'id_material'   => $id_mat,
                'nm_material'   => $grp['nm_material'],
                'id_gudang'     => $stock['id_gudang'],
                'kd_gudang'     => $stock['kd_gudang'],
                'tanggal'       => $tgl_produksi,
                'jumlah_coil'   => $grp['coil_count'],
                'qty_awal'      => $qty_awal,
                'qty_transaksi' => $qty_pakai,
                'qty_akhir'     => $qty_akhir,
                'costbook'      => $costbook,       // Tetap sama
                'total_harga'   => $total_harga,    // Nilai transaksi pengurang
                'saldo_awal'    => $saldo_awal,
                'saldo_akhir'   => $saldo_akhir,
                'harga_lama'    => $harga_lama,
                'created_by'    => $user_id,
                'created_at'    => $now,
            ]);

            // INSERT WAREHOUSE_HISTORY
            $this->db->insert('warehouse_history', [
                'id_material'     => $id_mat,
                'nm_material'     => $grp['nm_material'],
                'id_gudang'       => $stock['id_gudang'],
                'kd_gudang'       => $stock['kd_gudang'],
                'id_gudang_dari'  => $stock['id_gudang'],
                'kd_gudang_dari'  => $stock['kd_gudang'],
                'id_gudang_ke'    => 3,
                'kd_gudang_ke'    => 'PRT',
                'qty_stock_awal'  => $qty_awal,
                'qty_stock_akhir' => $qty_akhir,
                'no_ipp'          => $kode_trans,
                'jumlah_mat'      => $qty_pakai,
                'ket'             => 'Pemakaian Produksi ' . $kode_trans,
                'no_coil'         => implode(', ', $grp['coil_codes']),
                'harga_beli'      => $costbook,
                'total_harga'     => $total_harga,
                'saldo_awal'      => $saldo_awal,
                'saldo_akhir'     => $saldo_akhir,
                'harga_baru'      => $costbook,
                'harga_lama'      => $harga_lama,
                'update_by'       => $user_id,
                'update_date'     => $now,
            ]);

            // INSERT WAREHOUSE_STOCK_TRANSACTION_DETAIL (Per Roll Coil)
            foreach ($grp['coils'] as $c) {
                $c_net = (float)$c['net_weight_used'];
                $this->db->insert('warehouse_stock_transaction_detail', [
                    'kode_trans'     => $kode_trans,
                    'id_material'    => $id_mat,
                    'nm_material'    => $grp['nm_material'],
                    'id_gudang'      => $stock['id_gudang'],
                    'kd_gudang'      => $stock['kd_gudang'],
                    'no_coil'        => $c['no_coil'],
                    'parent_no_coil' => !empty($c['parent_coil_id']) ? $c['parent_coil_id'] : null,
                    'kode_internal'  => !empty($c['kode_internal']) ? $c['kode_internal'] : null,
                    'gross_weight'   => !empty($c['gross_weight']) ? (float)$c['gross_weight'] : 0,
                    'net_weight'     => $c_net,
                    'length'         => !empty($c['length']) ? (float)$c['length'] : 0,
                    'price_per_coil' => round($c_net * $costbook, 2),
                    'cost_book'      => $costbook,
                    'status_qc'      => 'OUT',
                    'to_status'      => 'consumed',
                    'created_at'     => $now,
                ]);
            }

            // UPDATE STATUS COIL DI WAREHOUSE_STOCK_COIL (Menjadi consumed)
            if (!empty($grp['coil_ids'])) {
                $this->db->where_in('id', $grp['coil_ids'])->update('warehouse_stock_coil', [
                    'status'        => 0,
                    'status_proses' => 'consumed',
                    'no_ipp'        => $kode_trans,
                ]);
            }
        }
    }

    /**
     * Catat penerimaan produk Finish Good (KW 1, Stok Bebas, KW 2) ke tabel stok FG:
     * - fg_stock: saldo persediaan dengan metode Moving Average Costbook.
     * - fg_stock_ledger: kartu stok riwayat mutasi IN per SPK.
     *
     * @param int   $report_id
     * @param array $header_data
     * @param array $items
     * @param int   $user_id
     */
    public function process_production_fg_stock_in($report_id, $header_data, $items, $user_id = null)
    {
        if (empty($items) || !is_array($items)) {
            return;
        }

        // 1. Tentukan nomor SPK dan nomor referensi
        $spk_no = 'PR-' . str_pad($report_id, 4, '0', STR_PAD_LEFT);
        if (!empty($header_data['id_tr_spk_detail'])) {
            $spk_d = $this->db->select('spk_no')->where('id', $header_data['id_tr_spk_detail'])->get('tr_spk_material_detail')->row();
            if ($spk_d && !empty($spk_d->spk_no)) {
                $spk_no = $spk_d->spk_no;
            }
        }

        $no_referensi = $spk_no;
        $tgl_produksi = !empty($header_data['tgl_produksi']) ? $header_data['tgl_produksi'] : date('Y-m-d');
        $now          = date('Y-m-d H:i:s');
        $user_id      = $user_id ?: ($this->auth ? $this->auth->user_id() : 1);

        // 2. Cek apakah mutasi untuk nomor referensi ini sudah pernah dicatat di fg_stock_ledger (idempoten)
        $already = $this->db->get_where('fg_stock_ledger', [
            'no_referensi' => $no_referensi,
            'jenis_mutasi' => 'IN'
        ])->row();

        if ($already) {
            return;
        }

        // 3. Pre-fetch master product_lvl_4 untuk lookup code_lv4 dan nama produk
        $prod_ids = [];
        foreach ($items as $it) {
            if (!empty($it['product_lvl_4_id'])) {
                $prod_ids[] = (int)$it['product_lvl_4_id'];
            }
        }
        $prod_map = [];
        if (!empty($prod_ids)) {
            $prods = $this->db->select('id, code_lv4, nama, weight')
                ->where_in('id', array_unique($prod_ids))
                ->get('product_lvl_4')
                ->result_array();
            foreach ($prods as $p) {
                $prod_map[$p['id']] = $p;
            }
        }

        // 4. Proses setiap item produk hasil produksi
        foreach ($items as $it) {
            $qty = isset($it['qty']) ? (float)$it['qty'] : 0;
            $berat_total = isset($it['berat_total']) ? (float)$it['berat_total'] : 0;
            if ($qty <= 0 && $berat_total <= 0) {
                continue;
            }

            $pid = !empty($it['product_lvl_4_id']) ? (int)$it['product_lvl_4_id'] : 0;
            $master_prod = isset($prod_map[$pid]) ? $prod_map[$pid] : null;

            // Tentukan kode produk dasar dan nama produk
            $base_code = $master_prod && !empty($master_prod['code_lv4']) ? $master_prod['code_lv4'] : ('P4-' . $pid);
            $base_name = !empty($it['nama_product_custom']) ? $it['nama_product_custom'] : ($master_prod ? $master_prod['nama'] : 'Product ' . $base_code);

            $cat = !empty($it['category_type']) ? strtolower($it['category_type']) : 'kw_1';

            // Pembedaan kode & nama produk berdasarkan grade (KW 1 vs KW 2)
            $produk_fg     = $base_code;
            $nm_produk     = $base_name;
            $kategori_stok = 'KW1';
            $sub_kategori  = 'KW 1';

            if ($cat === 'kw_2_internal') {
                $produk_fg     = $base_code . '-KW2INT';
                $nm_produk     = $base_name . ' (KW2 Internal)';
                $kategori_stok = 'KW2';
                $sub_kategori  = 'Internal';
            } elseif ($cat === 'kw_2_supplier') {
                $produk_fg     = $base_code . '-KW2SUP';
                $nm_produk     = $base_name . ' (KW2 Supplier)';
                $kategori_stok = 'KW2';
                $sub_kategori  = 'Supplier';
            } elseif ($cat === 'stok_bebas') {
                $produk_fg     = $base_code;
                $nm_produk     = $base_name;
                $kategori_stok = 'KW1';
                $sub_kategori  = 'Stok Bebas';
            }

            // Tentukan Total Nilai & Costbook sesuai FR-19 & FR-20:
            // KW 1 / Stok Bebas: 100% total_hpp_item
            // KW 2: 80% (inventory_value_80), 20% adalah beban rugi loss akuntansi
            $total_hpp_item = isset($it['total_hpp_item']) ? (float)$it['total_hpp_item'] : 0;
            if ($cat === 'kw_2_internal' || $cat === 'kw_2_supplier') {
                $total_nilai_in = isset($it['inventory_value_80']) && (float)$it['inventory_value_80'] > 0
                    ? (float)$it['inventory_value_80']
                    : round($total_hpp_item * 0.80, 2);
            } else {
                $total_nilai_in = $total_hpp_item;
            }

            $costbook_in = $qty > 0 ? round($total_nilai_in / $qty, 2) : 0;

            // 5. Update Saldo fg_stock (Moving Average Costbook & Berat Referensi)
            $stok = $this->db->get_where('fg_stock', ['produk_fg' => $produk_fg])->row();

            if ($stok) {
                $old_qty   = (float)$stok->qty_stok;
                $old_berat = (float)$stok->total_berat;
                $old_nilai = (float)($stok->total_nilai ?? 0);

                $new_qty   = $old_qty + $qty;
                $new_berat = $old_berat + $berat_total;
                $new_nilai = $old_nilai + $total_nilai_in;

                $new_cb    = $new_qty > 0 ? round($new_nilai / $new_qty, 2) : $costbook_in;
                $new_b_ref = $new_qty > 0 ? round($new_berat / $new_qty, 4) : ($qty > 0 ? round($berat_total / $qty, 4) : 0);

                $this->db->where('produk_fg', $produk_fg)->update('fg_stock', [
                    'nm_produk_fg'    => $nm_produk,
                    'kategori_stok'   => $kategori_stok,
                    'sub_kategori'    => $sub_kategori,
                    'qty_stok'        => $new_qty,
                    'total_berat'     => $new_berat,
                    'berat_referensi' => $new_b_ref,
                    'costbook'        => $new_cb,
                    'total_nilai'     => $new_nilai,
                    'last_update'     => $now
                ]);
            } else {
                $new_qty   = $qty;
                $new_berat = $berat_total;
                $new_nilai = $total_nilai_in;
                $new_cb    = $costbook_in;
                $new_b_ref = $qty > 0 ? round($berat_total / $qty, 4) : 0;

                $this->db->insert('fg_stock', [
                    'produk_fg'       => $produk_fg,
                    'nm_produk_fg'    => $nm_produk,
                    'kategori_stok'   => $kategori_stok,
                    'sub_kategori'    => $sub_kategori,
                    'qty_stok'        => $new_qty,
                    'total_berat'     => $new_berat,
                    'berat_referensi' => $new_b_ref,
                    'costbook'        => $new_cb,
                    'total_nilai'     => $new_nilai,
                    'last_update'     => $now
                ]);
            }

            // 6. Catat Mutasi IN di fg_stock_ledger (Kartu Stok)
            $this->db->insert('fg_stock_ledger', [
                'produk_fg'     => $produk_fg,
                'tgl_transaksi' => $tgl_produksi,
                'no_referensi'  => $no_referensi,
                'spk_no'        => $spk_no,
                'category_type' => $cat,
                'kategori_stok' => $kategori_stok,
                'sub_kategori'  => $sub_kategori,
                'jenis_mutasi'  => 'IN',
                'qty_in'        => $qty,
                'qty_out'       => 0,
                'berat_in'      => $berat_total,
                'berat_out'     => 0,
                'qty_saldo'     => $new_qty,
                'berat_saldo'   => $new_berat,
                'costbook'      => $costbook_in,
                'total_nilai'   => $total_nilai_in,
                'saldo_nilai'   => $new_nilai,
                'keterangan'    => 'Hasil Produksi ' . $spk_no . ' (' . strtoupper(str_replace('_', ' ', $cat)) . ')',
                'created_by'    => $user_id,
                'created_at'    => $now
            ]);
        }
    }

    /**
     * Catat Tong Coil dan Komponen Scrap (Wrapping, Potongan Pisau, Reject Internal/Supplier)
     * ke fg_stock dan fg_stock_ledger (FR-21, FR-27):
     * - Nilai persediaan waste masuk ke stok adalah 40% dari (Berat x Costbook Material)
     * - 60% lainnya adalah beban rugi FOH Scrap (biaya)
     *
     * @param int   $report_id
     * @param array $header_data
     * @param array $scraps
     * @param array $materials
     * @param int   $user_id
     */
    public function process_production_scrap_fg_stock_in($report_id, $header_data, $scraps, $materials = [], $user_id = null)
    {
        if (empty($scraps) || !is_array($scraps)) {
            return;
        }

        $spk_no = 'PR-' . str_pad($report_id, 4, '0', STR_PAD_LEFT);
        if (!empty($header_data['id_tr_spk_detail'])) {
            $spk_d = $this->db->select('spk_no')->where('id', $header_data['id_tr_spk_detail'])->get('tr_spk_material_detail')->row();
            if ($spk_d && !empty($spk_d->spk_no)) {
                $spk_no = $spk_d->spk_no;
            }
        }

        $no_referensi = $spk_no;
        $tgl_produksi = !empty($header_data['tgl_produksi']) ? $header_data['tgl_produksi'] : date('Y-m-d');
        $now          = date('Y-m-d H:i:s');
        $user_id      = $user_id ?: ($this->auth ? $this->auth->user_id() : 1);

        // Pre-fetch material info from materials
        $mat_info = null;
        if (!empty($materials) && is_array($materials)) {
            foreach ($materials as $m) {
                if (!empty($m['id_material'])) {
                    $mat_info = [
                        'id_material' => $m['id_material'],
                        'nm_material' => !empty($m['nm_material']) ? $m['nm_material'] : $m['id_material'],
                        'costbook'    => !empty($m['costbook_rate']) ? (float)$m['costbook_rate'] : (!empty($m['harga_beli']) ? (float)$m['harga_beli'] : 0),
                    ];
                    break;
                }
            }
        }

        foreach ($scraps as $sc) {
            $scrap_type = !empty($sc['scrap_type']) ? $sc['scrap_type'] : (!empty($sc['jenis_scrap']) ? $sc['jenis_scrap'] : '');
            // Sisa coil & hold coil ditangani oleh process_production_sisa_hold_coil_in()
            if (in_array($scrap_type, ['sisa_coil', 'hold_coil']) || empty($scrap_type)) {
                continue;
            }

            $berat_kg = !empty($sc['berat_total']) ? (float)$sc['berat_total'] : (!empty($sc['berat']) ? (float)$sc['berat'] : 0);
            if ($berat_kg <= 0) {
                continue;
            }

            // Resolve material specific to scrap row if target_coil_code is present
            $curr_mat_id   = $mat_info ? $mat_info['id_material'] : 'MAT-GENERAL';
            $curr_mat_name = $mat_info ? $mat_info['nm_material'] : 'Material Plat';
            $curr_cb_rate  = !empty($sc['costbook_rate']) ? (float)$sc['costbook_rate'] : ($mat_info ? $mat_info['costbook'] : 0);

            if (!empty($sc['target_coil_code'])) {
                $c_row = $this->db->select('id_material, nm_material, harga_beli')
                    ->where('no_coil', trim($sc['target_coil_code']))
                    ->order_by('id', 'DESC')->limit(1)->get('warehouse_stock_coil')->row_array();
                if ($c_row) {
                    $curr_mat_id   = $c_row['id_material'];
                    $curr_mat_name = $c_row['nm_material'];
                    if ($curr_cb_rate <= 0 && (float)$c_row['harga_beli'] > 0) {
                        $curr_cb_rate = (float)$c_row['harga_beli'];
                    }
                }
            }

            // FR-21 & FR-27: Nilai persediaan waste masuk ke stok adalah 40% (Inventory Value Waste)
            $total_nilai_in = isset($sc['inventory_value_40']) && (float)$sc['inventory_value_40'] > 0
                ? (float)$sc['inventory_value_40']
                : round($berat_kg * $curr_cb_rate * 0.40, 2);

            $costbook_in = $berat_kg > 0 ? round($total_nilai_in / $berat_kg, 2) : 0;

            if ($scrap_type === 'tong_coil') {
                $kategori_stok = 'TONG_COIL';
                $sub_kategori  = 'Tong Coil';
                $produk_fg     = 'TC-' . $curr_mat_id;
                $nm_produk     = 'Tong Coil - ' . $curr_mat_name;
                $qty_unit      = !empty($sc['qty']) ? (float)$sc['qty'] : 1;
            } else {
                $kategori_stok = 'SCRAP';
                $sub_kategori  = ucwords(str_replace('_', ' ', $scrap_type));
                $produk_fg     = 'SCRAP-' . strtoupper($scrap_type) . '-' . $curr_mat_id;
                $nm_produk     = 'Scrap ' . $sub_kategori . ' - ' . $curr_mat_name;
                $qty_unit      = 0;
            }

            // Update Saldo fg_stock
            $stok = $this->db->get_where('fg_stock', ['produk_fg' => $produk_fg])->row();

            if ($stok) {
                $old_qty   = (float)$stok->qty_stok;
                $old_berat = (float)$stok->total_berat;
                $old_nilai = (float)($stok->total_nilai ?? 0);

                $new_qty   = $old_qty + $qty_unit;
                $new_berat = $old_berat + $berat_kg;
                $new_nilai = $old_nilai + $total_nilai_in;
                $new_cb    = $new_berat > 0 ? round($new_nilai / $new_berat, 2) : $costbook_in;
                $new_b_ref = $new_qty > 0 ? round($new_berat / $new_qty, 4) : 0;

                $this->db->where('produk_fg', $produk_fg)->update('fg_stock', [
                    'nm_produk_fg'    => $nm_produk,
                    'kategori_stok'   => $kategori_stok,
                    'sub_kategori'    => $sub_kategori,
                    'id_material'     => $curr_mat_id,
                    'qty_stok'        => $new_qty,
                    'total_berat'     => $new_berat,
                    'berat_referensi' => $new_b_ref,
                    'costbook'        => $new_cb,
                    'total_nilai'     => $new_nilai,
                    'last_update'     => $now
                ]);
            } else {
                $new_qty   = $qty_unit;
                $new_berat = $berat_kg;
                $new_nilai = $total_nilai_in;
                $new_cb    = $costbook_in;
                $new_b_ref = $new_qty > 0 ? round($new_berat / $new_qty, 4) : 0;

                $this->db->insert('fg_stock', [
                    'produk_fg'       => $produk_fg,
                    'nm_produk_fg'    => $nm_produk,
                    'kategori_stok'   => $kategori_stok,
                    'sub_kategori'    => $sub_kategori,
                    'id_material'     => $curr_mat_id,
                    'qty_stok'        => $new_qty,
                    'total_berat'     => $new_berat,
                    'berat_referensi' => $new_b_ref,
                    'costbook'        => $new_cb,
                    'total_nilai'     => $new_nilai,
                    'last_update'     => $now
                ]);
            }

            // Catat Mutasi IN di fg_stock_ledger
            $this->db->insert('fg_stock_ledger', [
                'produk_fg'     => $produk_fg,
                'tgl_transaksi' => $tgl_produksi,
                'no_referensi'  => $no_referensi,
                'spk_no'        => $spk_no,
                'category_type' => $scrap_type,
                'kategori_stok' => $kategori_stok,
                'sub_kategori'  => $sub_kategori,
                'id_material'   => $curr_mat_id,
                'jenis_mutasi'  => 'IN',
                'qty_in'        => $qty_unit,
                'qty_out'       => 0,
                'berat_in'      => $berat_kg,
                'berat_out'     => 0,
                'qty_saldo'     => $new_qty,
                'berat_saldo'   => $new_berat,
                'costbook'      => $costbook_in,
                'total_nilai'   => $total_nilai_in,
                'saldo_nilai'   => $new_nilai,
                'keterangan'    => 'Hasil Produksi ' . $spk_no . ' (' . $sub_kategori . ')',
                'created_by'    => $user_id,
                'created_at'    => $now
            ]);
        }
    }

    /**
     * Bersihkan suffix WIP sebelumnya (-BDP.xx atau legacy -SISA) agar penomoran BDP melanjutkan coil induk.
     *
     * @param string $code
     * @return string
     */
    private function _strip_wip_coil_suffix($code)
    {
        $trimmed = trim((string) $code);
        if ($trimmed === '') {
            return '';
        }
        $stripped = preg_replace('/-(?:BDP\.\d+|SISA)$/i', '', $trimmed);
        return $stripped !== '' ? $stripped : $trimmed;
    }

    /**
     * Generate pasangan no_coil & kode_internal berformat <base>-BDP.xx (2 digit count per coil induk).
     * Bila coil berasal dari WIP (-BDP.01), counter melanjutkan seri coil induknya (-BDP.02, dst).
     *
     * @param array $parent_coil
     * @return array{no_coil:string,kode_internal:string}
     */
    private function _generate_next_wip_bdp_codes($parent_coil)
    {
        $raw_no_coil       = trim((string) ($parent_coil['no_coil'] ?? ''));
        $raw_kode_internal = trim((string) ($parent_coil['kode_internal'] ?? ''));

        $base_no_coil       = $this->_strip_wip_coil_suffix($raw_no_coil);
        $has_kode_internal  = ($raw_kode_internal !== '');
        $base_kode_internal = $has_kode_internal ? $this->_strip_wip_coil_suffix($raw_kode_internal) : $base_no_coil;

        $prefix_no_coil       = $base_no_coil . '-BDP.';
        $prefix_kode_internal = $base_kode_internal . '-BDP.';

        $like_no_coil = $this->db->escape_like_str($prefix_no_coil) . '%';
        $like_kode    = $this->db->escape_like_str($prefix_kode_internal) . '%';

        $rows = $this->db->query(
            "SELECT no_coil, kode_internal
             FROM warehouse_stock_coil
             WHERE no_coil LIKE ? ESCAPE '!' OR kode_internal LIKE ? ESCAPE '!'
             FOR UPDATE",
            [$like_no_coil, $like_kode]
        )->result_array();

        $max_seq = 0;
        $re_no   = '/^' . preg_quote($prefix_no_coil, '/') . '(\d+)$/i';
        $re_kode = '/^' . preg_quote($prefix_kode_internal, '/') . '(\d+)$/i';

        foreach ($rows as $r) {
            if (!empty($r['no_coil']) && preg_match($re_no, trim((string) $r['no_coil']), $m)) {
                $max_seq = max($max_seq, (int) $m[1]);
            }
            if (!empty($r['kode_internal']) && preg_match($re_kode, trim((string) $r['kode_internal']), $m)) {
                $max_seq = max($max_seq, (int) $m[1]);
            }
        }

        $seq_str = str_pad((string) ($max_seq + 1), 2, '0', STR_PAD_LEFT);

        return [
            'no_coil'       => $prefix_no_coil . $seq_str,
            'kode_internal' => $has_kode_internal ? ($prefix_kode_internal . $seq_str) : ($prefix_no_coil . $seq_str),
        ];
    }

    /**
     * Catat Sisa Coil ke Gudang WIP dan Hold Coil ke Gudang Material Hold (HLD):
     * - warehouse_stock_coil: record coil aktif baru dengan suffix -BDP.xx (WIP, 2 digit per coil induk) atau -HOLD (HLD).
     * - warehouse_stock: update persediaan fisik & total nilai Gudang WIP (harga_beli per kg TETAP).
     * - warehouse_history: catat riwayat audit mutasi masuk (IN).
     *
     * @param int   $report_id
     * @param array $header_data
     * @param array $scraps
     * @param array $materials
     * @param int   $user_id
     */
    public function process_production_sisa_hold_coil_in($report_id, $header_data, $scraps, $materials = [], $user_id = null)
    {
        if (empty($scraps) || !is_array($scraps)) {
            return;
        }

        // 1. Tentukan nomor SPK dan kode transaksi referensi
        $spk_no = 'PR-' . str_pad($report_id, 4, '0', STR_PAD_LEFT);
        if (!empty($header_data['id_tr_spk_detail'])) {
            $spk_d = $this->db->select('spk_no')->where('id', $header_data['id_tr_spk_detail'])->get('tr_spk_material_detail')->row();
            if ($spk_d && !empty($spk_d->spk_no)) {
                $spk_no = $spk_d->spk_no;
            }
        }

        $kode_trans   = $spk_no;
        $tgl_produksi = !empty($header_data['tgl_produksi']) ? $header_data['tgl_produksi'] : date('Y-m-d');
        $now          = date('Y-m-d H:i:s');
        $user_id      = $user_id ?: ($this->auth ? $this->auth->user_id() : 1);

        // Peta lookup id_unpack_baby_coil dari $materials agar parent_coil tepat
        $mat_coil_id_map = [];
        if (!empty($materials) && is_array($materials)) {
            foreach ($materials as $m) {
                $mc_id   = !empty($m['id_unpack_baby_coil']) ? (int)$m['id_unpack_baby_coil'] : 0;
                $mc_code = !empty($m['coil_code']) ? strtoupper(trim((string)$m['coil_code'])) : '';
                if ($mc_id > 0 && $mc_code !== '' && !isset($mat_coil_id_map[$mc_code])) {
                    $mat_coil_id_map[$mc_code] = $mc_id;
                }
            }
        }

        $seen_count_by_key    = [];
        $existing_count_cache = [];

        foreach ($scraps as $sc) {
            $scrap_type = !empty($sc['scrap_type']) ? $sc['scrap_type'] : (!empty($sc['jenis_scrap']) ? $sc['jenis_scrap'] : '');
            if (!in_array($scrap_type, ['sisa_coil', 'hold_coil'])) {
                continue;
            }

            $coil_code = !empty($sc['target_coil_code']) ? trim($sc['target_coil_code']) : (!empty($sc['kode_baby_coil']) ? trim($sc['kode_baby_coil']) : '');
            $berat_kg  = !empty($sc['berat_total']) ? (float)$sc['berat_total'] : (!empty($sc['berat']) ? (float)$sc['berat'] : 0);

            if ($berat_kg <= 0 || empty($coil_code)) {
                continue;
            }

            // Lookup coil induk dari warehouse_stock_coil (prioritaskan id_unpack_baby_coil dari material yang dipilih)
            $parent_coil = null;
            $coil_key    = strtoupper($coil_code);
            if (isset($mat_coil_id_map[$coil_key])) {
                $parent_coil = $this->db->where('id', $mat_coil_id_map[$coil_key])->get('warehouse_stock_coil')->row_array();
            }
            if (!$parent_coil) {
                $parent_coil = $this->db->where('no_coil', $coil_code)->order_by('id', 'DESC')->get('warehouse_stock_coil')->row_array();
            }
            if (!$parent_coil) {
                $parent_coil = $this->db->where('kode_internal', $coil_code)->order_by('id', 'DESC')->get('warehouse_stock_coil')->row_array();
            }

            if (!$parent_coil) {
                continue;
            }

            $id_mat     = $parent_coil['id_material'];
            $harga_beli = (float)$parent_coil['harga_beli']; // Harga beli / costbook per kg TETAP SAMA
            if ($harga_beli <= 0 && !empty($sc['costbook_rate'])) {
                $harga_beli = (float)$sc['costbook_rate'];
            }
            if ($harga_beli <= 0) {
                $ws = $this->db->select('harga_beli')->where('code_lv4', $id_mat)->where('harga_beli >', 0)->limit(1)->get('warehouse_stock')->row();
                if ($ws && (float)$ws->harga_beli > 0) {
                    $harga_beli = (float)$ws->harga_beli;
                }
            }

            $total_nilai = round($berat_kg * $harga_beli, 2);

            if ($scrap_type === 'sisa_coil') {
                $target_id_wh  = 3;
                $target_kd_wh  = 'WIP';
                $status_proses = 'wip';
                $ket_history   = 'Sisa Coil Produksi Masuk Gudang WIP dari ' . $kode_trans;
            } else {
                // hold_coil
                $target_id_wh  = 5;
                $target_kd_wh  = 'HLD';
                $status_proses = 'on_hold';
                $ket_history   = 'Hold Coil Produksi Masuk Gudang Hold dari ' . $kode_trans;
            }

            // Cek idempoten per (no_ipp, kd_gudang, parent_coil_id, urutan kemunculan) agar save ulang tidak menggandakan BDP.xx
            $occ_key = $scrap_type . ':' . (int)$parent_coil['id'];
            $occ_idx = isset($seen_count_by_key[$occ_key]) ? $seen_count_by_key[$occ_key] : 0;
            $seen_count_by_key[$occ_key] = $occ_idx + 1;

            if (!isset($existing_count_cache[$occ_key])) {
                $existing_count_cache[$occ_key] = (int)$this->db
                    ->where('no_ipp', $kode_trans)
                    ->where('kd_gudang', $target_kd_wh)
                    ->where('parent_coil_id', (int)$parent_coil['id'])
                    ->count_all_results('warehouse_stock_coil');
            }

            $exist = ($occ_idx < $existing_count_cache[$occ_key]);

            if (!$exist) {
                if ($scrap_type === 'sisa_coil') {
                    $bdp_codes         = $this->_generate_next_wip_bdp_codes($parent_coil);
                    $new_no_coil       = $bdp_codes['no_coil'];
                    $new_kode_internal = $bdp_codes['kode_internal'];
                } else {
                    $new_no_coil       = $parent_coil['no_coil'] . '-HOLD';
                    $new_kode_internal = !empty($parent_coil['kode_internal']) ? $parent_coil['kode_internal'] . '-HOLD' : $new_no_coil;
                }
                // 1. INSERT KE warehouse_stock_coil
                $this->db->insert('warehouse_stock_coil', [
                    'id_material'    => $id_mat,
                    'id_pack'        => $parent_coil['id_pack'],
                    'nm_material'    => $parent_coil['nm_material'],
                    'trade_name'     => $parent_coil['trade_name'],
                    'parent_coil_id' => $parent_coil['id'],
                    'is_baby_coil'   => 1,
                    'kode_internal'  => $new_kode_internal,
                    'id_gudang'      => $target_id_wh,
                    'kd_gudang'      => $target_kd_wh,
                    'no_coil'        => $new_no_coil,
                    'no_ipp'         => $kode_trans,
                    'no_po'          => $parent_coil['no_po'],
                    'no_ros'         => $parent_coil['no_ros'],
                    'gross_weight'   => $berat_kg,
                    'net_weight'     => $berat_kg,
                    'length'         => 0,
                    'qty_roll'       => 1,
                    'qty'            => $berat_kg,
                    'harga_beli'     => $harga_beli,
                    'total_nilai'    => $total_nilai,
                    'status'         => 1,
                    'status_proses'  => $status_proses,
                    'created_by'     => $user_id,
                    'created_on'     => $now,
                ]);
                $new_inserted_coil_id = $this->db->insert_id();

                // Hubungkan ID fisik coil gudang hold ke baris claim_material (FR-95, FR-98)
                if ($scrap_type === 'hold_coil' && $new_inserted_coil_id) {
                    $this->db->where('production_report_id', $report_id)
                        ->group_start()
                            ->where('no_coil_packing_list', $parent_coil['no_coil'])
                            ->or_where('no_coil_packing_list', $new_no_coil)
                        ->group_end()
                        ->update('claim_material', [
                            'id_gudang_material_hold' => $new_inserted_coil_id,
                            'id_baby_coil'            => $new_inserted_coil_id
                        ]);
                }

                // 2. UPDATE BUKU STOK AGREGAT DI warehouse_stock KHUSUS GUDANG WIP
                $qty_awal_wip   = 0;
                $saldo_awal_wip = 0;
                $qty_akhir_wip  = $berat_kg;
                $saldo_akhir_wip= $total_nilai;

                if ($scrap_type === 'sisa_coil') {
                    $stock_wip = $this->db->query(
                        "SELECT * FROM warehouse_stock WHERE code_lv4 = ? AND (kd_gudang = 'WIP' OR id_gudang = 3) LIMIT 1 FOR UPDATE",
                        [$id_mat]
                    )->row_array();

                    if ($stock_wip) {
                        $qty_awal_wip   = (float)$stock_wip['qty_stock'];
                        $saldo_awal_wip = (float)$stock_wip['total_nilai'];
                        $qty_akhir_wip  = $qty_awal_wip + $berat_kg;
                        $saldo_akhir_wip= $saldo_awal_wip + $total_nilai;

                        $this->db->where('id', $stock_wip['id'])->update('warehouse_stock', [
                            'qty_stock'   => $qty_akhir_wip,
                            'total_nilai' => $saldo_akhir_wip,
                            'incoming'    => (float)$stock_wip['incoming'] + $berat_kg,
                            'update_by'   => $user_id,
                            'update_date' => $now,
                        ]);
                    } else {
                        // Jika baris warehouse_stock untuk WIP belum ada, buat baris baru
                        $this->db->insert('warehouse_stock', [
                            'code_lv4'     => $id_mat,
                            'nm_material'  => $parent_coil['nm_material'],
                            'trade_name'   => $parent_coil['trade_name'],
                            'id_gudang'    => 3,
                            'kd_gudang'    => 'WIP',
                            'qty_stock'    => $berat_kg,
                            'incoming'     => $berat_kg,
                            'harga_beli'   => $harga_beli,
                            'total_nilai'  => $total_nilai,
                            'update_by'    => $user_id,
                            'update_date'  => $now,
                        ]);
                    }
                }

                // 3. INSERT warehouse_history (Audit Mutasi Masuk IN)
                $this->db->insert('warehouse_history', [
                    'id_material'     => $id_mat,
                    'nm_material'     => $parent_coil['nm_material'],
                    'id_gudang'       => $target_id_wh,
                    'kd_gudang'       => $target_kd_wh,
                    'id_gudang_dari'  => 1, // Area Produksi
                    'kd_gudang_dari'  => 'PRO',
                    'id_gudang_ke'    => $target_id_wh,
                    'kd_gudang_ke'    => $target_kd_wh,
                    'qty_stock_awal'  => $qty_awal_wip,
                    'qty_stock_akhir' => $qty_akhir_wip,
                    'no_ipp'          => $kode_trans,
                    'jumlah_mat'      => $berat_kg,
                    'ket'             => $ket_history,
                    'no_coil'         => $new_no_coil,
                    'harga_beli'      => $harga_beli,
                    'total_harga'     => $total_nilai,
                    'saldo_awal'      => $saldo_awal_wip,
                    'saldo_akhir'     => $saldo_akhir_wip,
                    'harga_baru'      => $harga_beli,
                    'harga_lama'      => $harga_beli,
                    'update_by'       => $user_id,
                    'update_date'     => $now,
                ]);
            }
        }
    }

    /**
     * Ambil nomor SPK Induk dari header Production Report.
     */
    private function _resolve_report_parent_spk($report_id, $header_data)
    {
        $spk_no = 'PR-' . str_pad((int) $report_id, 4, '0', STR_PAD_LEFT);
        if (!empty($header_data['id_tr_spk_detail'])) {
            $spk_d = $this->db->select('spk_no')
                ->where('id', (int) $header_data['id_tr_spk_detail'])
                ->get('tr_spk_material_detail')
                ->row();
            if ($spk_d && !empty($spk_d->spk_no)) {
                $spk_no = $spk_d->spk_no;
            }
        }
        $parent = kartu_stok_extract_parent_spk($spk_no);
        return $parent !== '' ? $parent : $spk_no;
    }

    /**
     * Distribusikan berat secara proporsional berdasarkan bobot net masing-masing coil.
     * Sisa pembulatan ditempatkan pada coil terakhir agar total tepat sama.
     */
    private function _allocate_weight_proportionally(array &$buckets, $field, $total_weight, array $coil_weights)
    {
        $total_weight = round((float) $total_weight, 3);
        if ($total_weight <= 0 || empty($coil_weights)) {
            return;
        }

        $coil_ids    = array_keys($coil_weights);
        $count       = count($coil_ids);
        $sum_weights = array_sum($coil_weights);
        $allocated   = 0.0;

        foreach ($coil_ids as $idx => $cid) {
            if ($idx === $count - 1) {
                $share = max(0.0, round($total_weight - $allocated, 3));
            } else {
                $ratio = $sum_weights > 0 ? ($coil_weights[$cid] / $sum_weights) : (1 / $count);
                $share = round($total_weight * $ratio, 3);
                $allocated += $share;
            }
            $buckets[$cid][$field] = round($buckets[$cid][$field] + $share, 3);
        }
    }

    /**
     * Catat mutasi Kartu Pemakaian Material per Coil (warehouse_coil_kartu_stok)
     * saat Production Report disubmit final (status_draft = 0):
     * - out ke 'consume' (KW 1 + Stok Bebas + KW 2)
     * - out ke 'WIP'     (Sisa Coil)
     * - out ke 'hold'    (Hold Coil)
     * - out ke 'Scrap'   (alokasi proporsional Tong Coil, Wrapping, Potongan Pisau, Reject)
     * Idempoten per production_report_id.
     */
    public function process_production_coil_kartu_stok($report_id, $header_data, $materials, $items = [], $scraps = [], $user_id = null, $created_at = null)
    {
        $this->load->helper('coil_kartu_stok');
        if (!$this->db->table_exists('warehouse_coil_kartu_stok')) {
            return;
        }
        if (empty($materials) || !is_array($materials)) {
            return;
        }

        $report_id = (int) $report_id;
        $already   = $this->db->where('production_report_id', $report_id)
            ->limit(1)
            ->get('warehouse_coil_kartu_stok')
            ->row();
        if ($already) {
            return;
        }

        $no_trs  = $this->_resolve_report_parent_spk($report_id, $header_data);
        $user_id = $user_id ?: ($this->auth ? $this->auth->user_id() : '1');
        $now     = !empty($created_at) ? $created_at : date('Y-m-d H:i:s');

        // 1. Kumpulkan coil unik dari materials + peta alias kode coil -> id_coil
        $coils        = [];
        $coil_weights = [];
        $buckets      = [];
        $alias_map    = [];

        foreach ($materials as $m) {
            $coil_code = !empty($m['coil_code']) ? trim($m['coil_code']) : '';
            $coil_id   = !empty($m['id_unpack_baby_coil']) ? (int) $m['id_unpack_baby_coil'] : 0;

            $coil = null;
            if ($coil_id > 0) {
                $coil = $this->db->where('id', $coil_id)->get('warehouse_stock_coil')->row_array();
            }
            if (!$coil && $coil_code !== '') {
                $coil = $this->db->where('no_coil', $coil_code)->order_by('id', 'DESC')->get('warehouse_stock_coil')->row_array();
                if (!$coil) {
                    $coil = $this->db->where('kode_internal', $coil_code)->order_by('id', 'DESC')->get('warehouse_stock_coil')->row_array();
                }
            }
            if (!$coil) {
                continue;
            }

            $cid = (int) $coil['id'];
            $w   = !empty($m['nett_weight_packing']) && (float) $m['nett_weight_packing'] > 0
                ? (float) $m['nett_weight_packing']
                : (float) $coil['net_weight'];

            if (!isset($coils[$cid])) {
                $coils[$cid]        = $coil;
                $coil_weights[$cid] = $w;
                $buckets[$cid]      = [
                    'consume' => 0.0,
                    'WIP'     => 0.0,
                    'hold'    => 0.0,
                    'Scrap'   => 0.0,
                ];
            } else {
                $coil_weights[$cid] += $w;
            }

            foreach ([$coil['no_coil'], $coil['kode_internal'], $coil_code] as $alias) {
                $k = strtoupper(trim((string) $alias));
                if ($k !== '') {
                    $alias_map[$k] = $cid;
                }
            }
        }

        if (empty($coils)) {
            return;
        }

        // 2. Akumulasi pemakaian 'consume' dari items (KW 1, Stok Bebas, KW 2)
        $unassigned_consume = 0.0;
        if (!empty($items) && is_array($items)) {
            foreach ($items as $it) {
                $berat = isset($it['berat_total']) ? (float) $it['berat_total'] : 0.0;
                if ($berat <= 0) {
                    continue;
                }
                $src_key = isset($it['source_material_coil']) ? strtoupper(trim((string) $it['source_material_coil'])) : '';
                if ($src_key !== '' && isset($alias_map[$src_key])) {
                    $buckets[$alias_map[$src_key]]['consume'] += $berat;
                } else {
                    $unassigned_consume += $berat;
                }
            }
        }
        $this->_allocate_weight_proportionally($buckets, 'consume', $unassigned_consume, $coil_weights);

        // 3. Akumulasi WIP (sisa_coil), Hold (hold_coil), dan Scrap Global (opsi B: proporsional)
        $unassigned_wip     = 0.0;
        $unassigned_hold    = 0.0;
        $total_global_scrap = 0.0;

        if (!empty($scraps) && is_array($scraps)) {
            foreach ($scraps as $sc) {
                $scrap_type = !empty($sc['scrap_type']) ? $sc['scrap_type'] : (!empty($sc['jenis_scrap']) ? $sc['jenis_scrap'] : '');
                $berat      = !empty($sc['berat_total']) ? (float) $sc['berat_total'] : (!empty($sc['berat']) ? (float) $sc['berat'] : 0.0);
                if ($berat <= 0 || $scrap_type === '') {
                    continue;
                }

                $target_raw = !empty($sc['target_coil_code']) ? $sc['target_coil_code'] : (!empty($sc['kode_baby_coil']) ? $sc['kode_baby_coil'] : '');
                $target_key = strtoupper(trim((string) $target_raw));

                if ($scrap_type === 'sisa_coil') {
                    if ($target_key !== '' && isset($alias_map[$target_key])) {
                        $buckets[$alias_map[$target_key]]['WIP'] += $berat;
                    } else {
                        $unassigned_wip += $berat;
                    }
                } elseif ($scrap_type === 'hold_coil') {
                    if ($target_key !== '' && isset($alias_map[$target_key])) {
                        $buckets[$alias_map[$target_key]]['hold'] += $berat;
                    } else {
                        $unassigned_hold += $berat;
                    }
                } else {
                    $total_global_scrap += $berat;
                }
            }
        }

        $this->_allocate_weight_proportionally($buckets, 'WIP', $unassigned_wip, $coil_weights);
        $this->_allocate_weight_proportionally($buckets, 'hold', $unassigned_hold, $coil_weights);
        $this->_allocate_weight_proportionally($buckets, 'Scrap', $total_global_scrap, $coil_weights);

        // 4. Catat ke warehouse_coil_kartu_stok per coil secara berurutan
        $destinations = ['consume', 'WIP', 'hold', 'Scrap'];
        foreach ($coils as $cid => $coil) {
            $b       = $buckets[$cid];
            $has_out = ($b['consume'] > 0 || $b['WIP'] > 0 || $b['hold'] > 0 || $b['Scrap'] > 0);
            if (!$has_out) {
                continue;
            }

            // Pastikan coil memiliki baris IN pembuka jika belum pernah masuk ke PRT lewat Request List
            $last = kartu_stok_get_last_balance($cid);
            if (!$last['exists']) {
                $opening_qty = round((float) $coil_weights[$cid], 3);
                kartu_stok_record_entry([
                    'id_coil'              => $cid,
                    'no_trs'               => $no_trs,
                    'id_material'          => $coil['id_material'],
                    'nm_material'          => $coil['nm_material'],
                    'no_coil'              => $coil['no_coil'],
                    'kode_internal'        => $coil['kode_internal'],
                    'status'               => 'in',
                    'dari'                 => kartu_stok_map_gudang_label($coil['kd_gudang']),
                    'ke'                   => 'prod. 2',
                    'qty_trans'            => $opening_qty,
                    'qty_awal'             => 0,
                    'production_report_id' => $report_id,
                    'keterangan'           => 'Stok awal coil dipakai produksi (' . $no_trs . ')',
                    'created_by'           => $user_id,
                    'created_at'           => $now,
                ]);
            }

            foreach ($destinations as $dest) {
                $qty_out = round((float) $b[$dest], 3);
                if ($qty_out <= 0) {
                    continue;
                }
                kartu_stok_record_entry([
                    'id_coil'              => $cid,
                    'no_trs'               => $no_trs,
                    'id_material'          => $coil['id_material'],
                    'nm_material'          => $coil['nm_material'],
                    'no_coil'              => $coil['no_coil'],
                    'kode_internal'        => $coil['kode_internal'],
                    'status'               => 'out',
                    'dari'                 => 'prod. 2',
                    'ke'                   => $dest,
                    'qty_trans'            => $qty_out,
                    'production_report_id' => $report_id,
                    'keterangan'           => 'Pemakaian produksi -> ' . $dest . ' (' . $no_trs . ')',
                    'created_by'           => $user_id,
                    'created_at'           => $now,
                ]);
            }
        }
    }

    public function get_report_full($report_id)
    {
        $header = $this->db->where('id', $report_id)->get('production_reports')->row_array();
        if (!$header) return null;

        $materials = $this->db->where('production_report_id', $report_id)->get('production_report_materials')->result_array();
        $items = $this->db->where('production_report_id', $report_id)->get('production_report_items')->result_array();
        $scraps = $this->db->where('production_report_id', $report_id)->get('production_report_scraps')->result_array();

        // Fallback dinamis jika ada item lama yang costbook_rate-nya masih 0
        $coil_cb_map = [];
        foreach ($materials as $m) {
            $c_code = !empty($m['coil_code']) ? trim($m['coil_code']) : '';
            $c_id   = !empty($m['id_unpack_baby_coil']) ? (int)$m['id_unpack_baby_coil'] : 0;
            $coil_db = null;
            if ($c_id) {
                $coil_db = $this->db->where('id', $c_id)->get('warehouse_stock_coil')->row_array();
            }
            if (!$coil_db && $c_code) {
                $coil_db = $this->db->where('no_coil', $c_code)->order_by('id', 'DESC')->get('warehouse_stock_coil')->row_array();
            }
            if ($coil_db) {
                $cb = (float)$coil_db['harga_beli'];
                if ($cb <= 0 && !empty($coil_db['id_material'])) {
                    $mst = $this->db->select('harga_beli')->where('code_lv4', $coil_db['id_material'])->where('harga_beli >', 0)->get('warehouse_stock')->row();
                    if ($mst && (float)$mst->harga_beli > 0) {
                        $cb = (float)$mst->harga_beli;
                    }
                }
                if ($c_code) {
                    $coil_cb_map[$c_code] = $cb;
                }
                if ($c_id) {
                    $coil_cb_map[(string)$c_id] = $cb;
                }
            }
        }
        $fallback_cb = 0;
        foreach ($coil_cb_map as $val) {
            if ($val > 0) {
                $fallback_cb = $val;
                break;
            }
        }

        foreach ($items as &$it) {
            if (empty($it['costbook_rate']) || (float)$it['costbook_rate'] <= 0) {
                $c_code = !empty($it['source_material_coil']) ? trim($it['source_material_coil']) : '';
                $cb = isset($coil_cb_map[$c_code]) && $coil_cb_map[$c_code] > 0 ? $coil_cb_map[$c_code] : $fallback_cb;
                $it['costbook_rate'] = $cb;
                if ((empty($it['material_cost_per_pcs']) || (float)$it['material_cost_per_pcs'] <= 0) && $cb > 0) {
                    $berat_pcs = (float)$it['berat_per_pcs'];
                    $qty = (int)$it['qty'];
                    $it['material_cost_per_pcs'] = $berat_pcs * $cb;
                    $it['material_cost_total']   = $it['material_cost_per_pcs'] * $qty;
                }
            }
        }
        unset($it);

        foreach ($scraps as &$sc) {
            if (empty($sc['costbook_rate']) || (float)$sc['costbook_rate'] <= 0) {
                $c_code = !empty($sc['target_coil_code']) ? trim($sc['target_coil_code']) : '';
                $cb = isset($coil_cb_map[$c_code]) && $coil_cb_map[$c_code] > 0 ? $coil_cb_map[$c_code] : $fallback_cb;
                $sc['costbook_rate'] = $cb;
                if ((empty($sc['total_nilai']) || (float)$sc['total_nilai'] <= 0) && $cb > 0) {
                    $berat = (float)$sc['berat_total'];
                    $sc['total_nilai'] = $berat * $cb;
                }
            }
        }
        unset($sc);

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
            'claims'     => $this->get_report_claims($report_id),
        ];
    }

    /**
     * Ambil data claim material dan bukti pendukung untuk production report terkait (FR-94)
     */
    public function get_report_claims($report_id)
    {
        $claims = $this->db->where('production_report_id', $report_id)->get('claim_material')->result_array();
        if (empty($claims)) {
            return [];
        }
        foreach ($claims as &$cl) {
            $cl['bukti'] = $this->db->where('id_claim_material', $cl['id'])->get('claim_material_bukti')->result_array();
            $cl['decisions'] = $this->db->where('id_claim_material', $cl['id'])->get('claim_material_decision')->result_array();
        }
        return $claims;
    }

    /**
     * Cek apakah ada claim Hold Coil dari laporan ini yang belum diputuskan oleh Purchasing (FR-18, NFR-06)
     */
    public function has_pending_hold_claim($report_id)
    {
        $hold_claims = $this->db->where('production_report_id', $report_id)
            ->where('asal', 'Hold Coil')
            ->get('claim_material')
            ->result_array();

        if (empty($hold_claims)) {
            return false;
        }

        foreach ($hold_claims as $hc) {
            $dec = $this->db->where('id_claim_material', $hc['id'])->count_all_results('claim_material_decision');
            if ($dec == 0) {
                return $hc['no_claim'];
            }
        }

        return false;
    }

    /**
     * Generate Nomor Claim Otomatis format CLM-YYMM-XXXX
     */
    public function generate_claim_no()
    {
        $prefix = 'CLM-' . date('ym') . '-';
        $last = $this->db->like('no_claim', $prefix, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('claim_material')
            ->row();
        $seq = 1;
        if ($last && !empty($last->no_claim)) {
            $parts = explode('-', $last->no_claim);
            if (isset($parts[2])) {
                $seq = (int)$parts[2] + 1;
            }
        }
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Ambil informasi lengkap baby coil untuk data awal baris claim (FR-94)
     */
    public function get_coil_claim_info($coil_id = null, $coil_code = null)
    {
        $coil = null;
        if (!empty($coil_id)) {
            $coil = $this->db->where('id', $coil_id)->get('warehouse_stock_coil')->row_array();
        }
        if (!$coil && !empty($coil_code)) {
            $coil = $this->db->where('no_coil', $coil_code)->order_by('id', 'DESC')->get('warehouse_stock_coil')->row_array();
            if (!$coil) {
                $coil = $this->db->where('kode_internal', $coil_code)->order_by('id', 'DESC')->get('warehouse_stock_coil')->row_array();
            }
        }

        if (!$coil) {
            return null;
        }

        // Jika no_po kosong pada baby coil, fallback ke parent_coil bila ada
        if (empty($coil['no_po']) && !empty($coil['parent_coil_id'])) {
            $p_coil = $this->db->where('id', $coil['parent_coil_id'])->get('warehouse_stock_coil')->row_array();
            if ($p_coil) {
                if (empty($coil['no_po'])) $coil['no_po'] = $p_coil['no_po'];
                if (empty($coil['no_ros'])) $coil['no_ros'] = $p_coil['no_ros'];
                if (empty($coil['harga_beli']) || (float)$coil['harga_beli'] <= 0) $coil['harga_beli'] = $p_coil['harga_beli'];
            }
        }

        $supplier_id = null;
        $supplier_name = null;

        // Cari supplier dari tr_ros_header
        if (!empty($coil['no_po'])) {
            $ros = $this->db->select('id_supplier, nm_supplier')
                ->where('no_po', $coil['no_po'])
                ->order_by('id', 'DESC')
                ->get('tr_ros_header')
                ->row_array();
            if ($ros) {
                $supplier_id = $ros['id_supplier'];
                $supplier_name = $ros['nm_supplier'];
            }
        }

        // Fallback cari supplier dari tr_purchase_order
        if (empty($supplier_name) && !empty($coil['no_po'])) {
            $po = $this->db->select('po.id_suplier, s.nama AS nm_supplier')
                ->from('tr_purchase_order po')
                ->join('new_supplier s', 's.id_suplier = po.id_suplier', 'left')
                ->where('po.no_po', $coil['no_po'])
                ->get()
                ->row_array();
            if ($po) {
                $supplier_id = $po['id_suplier'];
                $supplier_name = $po['nm_supplier'];
            }
        }

        // Cari costbook dari tr_cost_book
        $costbook = (float)$coil['harga_beli'];
        if (!empty($coil['id_material'])) {
            $this->db->where('id_material', $coil['id_material']);
            if (!empty($coil['id_gudang'])) {
                $this->db->where('id_gudang', $coil['id_gudang']);
            }
            $cb_row = $this->db->get('tr_cost_book')->row_array();
            if ($cb_row && (float)$cb_row['costbook'] > 0) {
                $costbook = (float)$cb_row['costbook'];
            }
        }

        // Cari price_per_unit_po dari dt_trans_po
        $price_per_unit_po = 0;
        if (!empty($coil['no_po'])) {
            $dt_po = $this->db->select('hargasatuan')
                ->where('no_po', $coil['no_po']);
            if (!empty($coil['id_material'])) {
                $dt_po->where('idmaterial', $coil['id_material']);
            }
            $po_item = $dt_po->order_by('id', 'DESC')->get('dt_trans_po')->row_array();
            if ($po_item && (float)$po_item['hargasatuan'] > 0) {
                $price_per_unit_po = (float)$po_item['hargasatuan'];
            }
        }

        return [
            'id_baby_coil'         => (int)$coil['id'],
            'id_material'          => $coil['id_material'],
            'nm_material'          => $coil['nm_material'],
            'trade_name'           => $coil['trade_name'],
            'kode_internal'        => $coil['kode_internal'],
            'no_coil_packing_list' => $coil['no_coil'],
            'no_po'                => !empty($coil['no_po']) ? $coil['no_po'] : '-',
            'id_suplier'           => !empty($supplier_id) ? $supplier_id : '-',
            'nm_supplier'          => !empty($supplier_name) ? $supplier_name : '-',
            'harga_beli_per_kg'    => (float)$coil['harga_beli'],
            'costbook_per_kg'      => (float)$costbook,
            'price_per_unit_po'    => (float)$price_per_unit_po,
            'id_gudang_asal'       => $coil['id_gudang'],
            'kd_gudang_asal'       => $coil['kd_gudang'],
        ];
    }

    /**
     * Siapkan dataset lengkap Export Report Excel SPK untuk laporan berstatus Done.
     *
     * @param int $report_id
     * @return array|null
     */
    public function get_report_export_data($report_id)
    {
        $report = $this->get_report_full($report_id);
        if (!$report) {
            return null;
        }

        $spk_no = !empty($report['spk_header']['spk_no'])
            ? $report['spk_header']['spk_no']
            : (!empty($report['spk_detail']['spk_no']) ? $report['spk_detail']['spk_no'] : 'PR-' . str_pad((int) $report_id, 4, '0', STR_PAD_LEFT));

        $coil_ctx  = $this->_build_export_coil_context($report['materials']);
        $prod_meta = $this->_get_export_product_meta($report['items']);

        $unassigned = [
            'kw1_kg'                   => 0.0,
            'kw1_meter'                => 0.0,
            'kw2_kg'                   => 0.0,
            'kw2_meter'                => 0.0,
            'wip_kg'                   => 0.0,
            'tong_coil'                => 0.0,
            'wrapping'                 => 0.0,
            'potongan_pisau'           => 0.0,
            'reject_product_internal'  => 0.0,
            'reject_product_supplier'  => 0.0,
            'reject_material_internal' => 0.0,
            'reject_material_supplier' => 0.0,
        ];

        $section2 = [];
        $section3 = [];
        $fallback_mat = !empty($coil_ctx['rows'][0]['material']) ? $coil_ctx['rows'][0]['material'] : '-';

        foreach ($report['items'] as $it) {
            $cat         = !empty($it['category_type']) ? strtolower(trim($it['category_type'])) : 'kw_1';
            $pid         = !empty($it['product_lvl_4_id']) ? (int) $it['product_lvl_4_id'] : 0;
            $uniq        = !empty($it['no_uniq_id']) ? trim($it['no_uniq_id']) : '';
            $meta        = isset($prod_meta[$pid]) ? $prod_meta[$pid] : null;
            $qty         = (float) ($it['qty'] ?? 0);
            $berat_total = (float) ($it['berat_total'] ?? 0);
            $berat_pcs   = (float) ($it['berat_per_pcs'] ?? 0);
            if ($berat_pcs <= 0 && $qty > 0) {
                $berat_pcs = $berat_total / $qty;
            }

            $prod_name = !empty($it['nama_product_custom'])
                ? $it['nama_product_custom']
                : (!empty($it['nama_produk']) ? $it['nama_produk'] : ($meta ? $meta['nama'] : '-'));
            $coil_code = !empty($it['source_material_coil']) ? trim($it['source_material_coil']) : '';
            $mat_name  = $this->_resolve_coil_material_name($coil_code, $coil_ctx['material_by_alias'], $fallback_mat);

            if ($cat === 'kw_1' || $cat === 'stok_bebas') {
                $length_m   = $this->_resolve_item_length_m($it, $meta, $prod_name);
                $item_meter = ($qty > 0 && $length_m > 0) ? round($qty * $length_m, 3) : 0.0;

                $this->_accumulate_export_metric($coil_ctx, $unassigned, $coil_code, 'kw1_kg', $berat_total);
                $this->_accumulate_export_metric($coil_ctx, $unassigned, $coil_code, 'kw1_meter', $item_meter);

                $std_pcs = (float) ($it['berat_standard'] ?? 0);
                if ($std_pcs <= 0 && $meta) {
                    $std_pcs = (float) $meta['weight'];
                }
                $selisih_pcs_ratio = $std_pcs > 0 ? (($berat_pcs - $std_pcs) / $std_pcs) : 0.0;

                $akt_kg_m = (float) ($it['berat_per_meter_aktual'] ?? 0);
                if ($akt_kg_m <= 0 && $length_m > 0 && $berat_pcs > 0) {
                    $akt_kg_m = $berat_pcs / $length_m;
                }

                $std_kg_m = (float) ($it['berat_per_meter_standard'] ?? 0);
                if ($std_kg_m <= 0 && $meta) {
                    $std_kg_m = ($uniq !== '' && isset($meta['bom_std_map'][$uniq]))
                        ? $meta['bom_std_map'][$uniq]
                        : $meta['bom_std_default'];
                }
                $selisih_m_ratio = ($std_kg_m > 0 && $akt_kg_m > 0) ? (($akt_kg_m - $std_kg_m) / $std_kg_m) : 0.0;

                $section2[] = [
                    'produk'              => $prod_name,
                    'qty'                 => $qty,
                    'material'            => $mat_name,
                    'total_kg'            => $berat_total,
                    'berat_per_pcs'       => $berat_pcs,
                    'std_pcs'             => $std_pcs,
                    'selisih_pcs_ratio'   => $selisih_pcs_ratio,
                    'aktual_kg_meter'     => $akt_kg_m,
                    'std_kg_meter'        => $std_kg_m,
                    'selisih_meter_ratio' => $selisih_m_ratio,
                ];
            } elseif ($cat === 'kw_2_internal' || $cat === 'kw_2_supplier') {
                $size_m    = (float) ($it['size_meter'] ?? 0);
                $kw2_meter = ($qty > 0 && $size_m > 0) ? round($qty * $size_m, 3) : round($size_m, 3);

                $this->_accumulate_export_metric($coil_ctx, $unassigned, $coil_code, 'kw2_kg', $berat_total);
                $this->_accumulate_export_metric($coil_ctx, $unassigned, $coil_code, 'kw2_meter', $kw2_meter);

                $section3[] = [
                    'produk'        => $prod_name,
                    'qty'           => $qty,
                    'material'      => $mat_name,
                    'total_kg'      => $berat_total,
                    'berat_per_pcs' => $berat_pcs,
                    'panjang'       => $size_m,
                    'keterangan'    => !empty($it['keterangan']) ? $it['keterangan'] : '-',
                ];
            }
        }

        $this->_apply_export_scraps($coil_ctx, $unassigned, $report['scraps'], $report['claims']);

        foreach ($unassigned as $field => $val) {
            if ($val > 0 && !empty($coil_ctx['rows'])) {
                $this->_allocate_weight_proportionally($coil_ctx['rows'], $field, $val, $coil_ctx['weights']);
            }
        }

        // WIP Meter dihitung proporsional dari rasio berat WIP terhadap Nett Weight Packing List coil
        foreach ($coil_ctx['rows'] as &$row) {
            $row['wip_meter'] = ($row['pl_nett'] > 0 && $row['pl_meter'] > 0 && $row['wip_kg'] > 0)
                ? round(($row['wip_kg'] / $row['pl_nett']) * $row['pl_meter'], 3)
                : 0.0;
        }
        unset($row);

        return [
            'report_id' => (int) $report_id,
            'spk_no'    => $spk_no,
            'header'    => $report['header'],
            'section1'  => array_values($coil_ctx['rows']),
            'section2'  => $section2,
            'section3'  => $section3,
        ];
    }

    /**
     * Siapkan baris dasar Bagian 1 (Pemakaian Material) & indeks alias coil.
     */
    private function _build_export_coil_context($materials)
    {
        $rows              = [];
        $weights           = [];
        $alias_map         = [];
        $material_by_alias = [];

        if (empty($materials) || !is_array($materials)) {
            return compact('rows', 'weights', 'alias_map', 'material_by_alias');
        }

        foreach ($materials as $idx => $m) {
            $coil_id   = !empty($m['id_unpack_baby_coil']) ? (int) $m['id_unpack_baby_coil'] : 0;
            $coil_code = !empty($m['coil_code']) ? trim($m['coil_code']) : '';

            $coil_db = null;
            if ($coil_id > 0) {
                $coil_db = $this->db->where('id', $coil_id)->get('warehouse_stock_coil')->row_array();
            }
            if (!$coil_db && $coil_code !== '') {
                $coil_db = $this->db->where('no_coil', $coil_code)->order_by('id', 'DESC')->get('warehouse_stock_coil')->row_array();
                if (!$coil_db) {
                    $coil_db = $this->db->where('kode_internal', $coil_code)->order_by('id', 'DESC')->get('warehouse_stock_coil')->row_array();
                }
            }

            $mat_name      = !empty($m['material_name']) ? trim($m['material_name']) : (!empty($coil_db['nm_material']) ? trim($coil_db['nm_material']) : '-');
            $kode_internal = !empty($coil_db['kode_internal']) ? trim($coil_db['kode_internal']) : ($coil_code !== '' ? $coil_code : '-');
            $pl_nett       = (float) ($m['nett_weight_packing'] ?? 0);
            $pl_gross      = (float) ($m['gross_weight'] ?? 0);
            $pl_meter      = (float) ($m['total_meter'] ?? 0);
            if ($pl_meter <= 0 && !empty($coil_db['length'])) {
                $pl_meter = (float) $coil_db['length'];
            }

            $act_nett = $this->_lookup_unpack_actual_nett($coil_id, $kode_internal, $coil_code);

            $rows[$idx] = [
                'material'                 => $mat_name,
                'kode_internal'            => $kode_internal,
                'pl_nett'                  => $pl_nett,
                'pl_gross'                 => $pl_gross,
                'pl_meter'                 => $pl_meter,
                'out_nett'                 => $pl_nett,
                'out_meter'                => $pl_meter,
                'act_timbang_nett'         => $act_nett,
                'kw1_kg'                   => 0.0,
                'kw1_meter'                => 0.0,
                'kw2_kg'                   => 0.0,
                'kw2_meter'                => 0.0,
                'wip_kg'                   => 0.0,
                'wip_meter'                => 0.0,
                'kulit'                    => (float) ($m['berat_kulit'] ?? 0),
                'clamp'                    => (float) ($m['berat_clamp'] ?? 0),
                'tong_coil'                => 0.0,
                'wrapping'                 => 0.0,
                'potongan_pisau'           => 0.0,
                'reject_product_internal'  => 0.0,
                'reject_product_supplier'  => 0.0,
                'reject_material_internal' => 0.0,
                'reject_material_supplier' => 0.0,
            ];
            $weights[$idx] = $pl_nett;

            $aliases = [$coil_code];
            if ($coil_db) {
                $aliases[] = $coil_db['no_coil'] ?? '';
                $aliases[] = $coil_db['kode_internal'] ?? '';
            }
            foreach ($aliases as $al) {
                $k = strtoupper(trim((string) $al));
                if ($k !== '' && $k !== '-') {
                    $alias_map[$k]         = $idx;
                    $material_by_alias[$k] = $mat_name;
                }
            }
        }

        return compact('rows', 'weights', 'alias_map', 'material_by_alias');
    }

    /**
     * Ambil actual timbang nett (net_weight_actual) dari data Unpack Coil.
     */
    private function _lookup_unpack_actual_nett($coil_id, $kode_internal, $coil_code)
    {
        if (!$this->db->table_exists('tr_unpack_coil_baby')) {
            return 0.0;
        }

        if ($coil_id > 0) {
            $row = $this->db->select('net_weight_actual')
                ->where('id_coil_baru', (int) $coil_id)
                ->where('is_delete', 0)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get('tr_unpack_coil_baby')
                ->row_array();
            if ($row) {
                return (float) $row['net_weight_actual'];
            }
        }

        $candidates = array_values(array_unique(array_filter([$kode_internal, $coil_code], function ($v) {
            return $v !== '' && $v !== '-';
        })));
        if (empty($candidates)) {
            return 0.0;
        }

        $this->db->select('net_weight_actual')->from('tr_unpack_coil_baby')->where('is_delete', 0);
        $this->db->group_start();
        $this->db->where_in('babycoil_code', $candidates);
        $this->db->or_where_in('no_coil', $candidates);
        $this->db->group_end();
        $row = $this->db->order_by('id', 'DESC')->limit(1)->get()->row_array();

        return $row ? (float) $row['net_weight_actual'] : 0.0;
    }

    /**
     * Pre-fetch metadata master product_lvl_4 dan Std Kg/Meter dari ms_bom_detail.
     */
    private function _get_export_product_meta($items)
    {
        $meta = [];
        if (empty($items) || !is_array($items)) {
            return $meta;
        }

        $pids = [];
        foreach ($items as $it) {
            if (!empty($it['product_lvl_4_id'])) {
                $pids[] = (int) $it['product_lvl_4_id'];
            }
        }
        $pids = array_values(array_unique($pids));
        if (empty($pids)) {
            return $meta;
        }

        $prods = $this->db->select('id, code_lv4, nama, weight, length, panjang')
            ->where_in('id', $pids)
            ->get('product_lvl_4')
            ->result_array();

        $code_to_pid = [];
        foreach ($prods as $p) {
            $pid = (int) $p['id'];
            $meta[$pid] = [
                'code_lv4'        => $p['code_lv4'],
                'nama'            => $p['nama'],
                'weight'          => (float) $p['weight'],
                'length'          => (float) $p['length'],
                'panjang'         => (float) $p['panjang'],
                'bom_std_map'     => [],
                'bom_std_default' => 0.0,
            ];
            if (!empty($p['code_lv4'])) {
                $code_to_pid[$p['code_lv4']] = $pid;
            }
        }

        if (!empty($code_to_pid)) {
            $boms = $this->db->select('h.id_produk, d.no_uniq_id, d.berat_per_meter')
                ->from('ms_bom_header h')
                ->join('ms_bom_detail d', 'd.id_bom = h.id AND d.is_delete = 0')
                ->where_in('h.id_produk', array_keys($code_to_pid))
                ->where('h.is_delete', 0)
                ->order_by('d.urut', 'ASC')
                ->get()
                ->result_array();
            foreach ($boms as $b) {
                $pid = isset($code_to_pid[$b['id_produk']]) ? $code_to_pid[$b['id_produk']] : 0;
                if (!$pid) {
                    continue;
                }
                $val = (float) ($b['berat_per_meter'] ?? 0);
                if (!empty($b['no_uniq_id'])) {
                    $meta[$pid]['bom_std_map'][$b['no_uniq_id']] = $val;
                }
                if ($meta[$pid]['bom_std_default'] <= 0 && $val > 0) {
                    $meta[$pid]['bom_std_default'] = $val;
                }
            }
        }

        return $meta;
    }

    /**
     * Resolve panjang produk (meter): panjang_meter -> product_lvl_4 -> parsing nama produk.
     */
    private function _resolve_item_length_m($it, $meta, $prod_name)
    {
        if (!empty($it['panjang_meter']) && (float) $it['panjang_meter'] > 0) {
            return (float) $it['panjang_meter'];
        }
        if ($meta) {
            if (!empty($meta['panjang']) && (float) $meta['panjang'] > 0) {
                return (float) $meta['panjang'];
            }
            if (!empty($meta['length']) && (float) $meta['length'] > 0) {
                return (float) $meta['length'];
            }
        }
        if (preg_match('/[x×]\s*(\d+(?:[.,]\d+)?)\s*(?:mtr|meter|m)\b/i', (string) $prod_name, $m)) {
            return (float) str_replace(',', '.', $m[1]);
        }
        if (preg_match('/\b(\d+(?:[.,]\d+)?)\s*(?:mtr|meter)\b/i', (string) $prod_name, $m)) {
            return (float) str_replace(',', '.', $m[1]);
        }
        return 0.0;
    }

    /**
     * Resolve nama material dari kode coil item.
     */
    private function _resolve_coil_material_name($coil_code, array $material_by_alias, $fallback)
    {
        $k = strtoupper(trim((string) $coil_code));
        if ($k !== '' && isset($material_by_alias[$k])) {
            return $material_by_alias[$k];
        }
        return $fallback;
    }

    /**
     * Tambah nilai metrik ke baris coil yang cocok atau ke pool proporsional bila belum terikat coil.
     */
    private function _accumulate_export_metric(array &$coil_ctx, array &$unassigned, $coil_code, $field, $amount)
    {
        $amount = (float) $amount;
        if ($amount <= 0) {
            return;
        }
        $k = strtoupper(trim((string) $coil_code));
        if ($k !== '' && isset($coil_ctx['alias_map'][$k])) {
            $idx = $coil_ctx['alias_map'][$k];
            $coil_ctx['rows'][$idx][$field] += $amount;
        } else {
            $unassigned[$field] += $amount;
        }
    }

    /**
     * Petakan WIP (sisa_coil) dan 7 komponen scrap ke coil terkait atau pool proporsional.
     */
    private function _apply_export_scraps(array &$coil_ctx, array &$unassigned, $scraps, $claims = [])
    {
        if (empty($scraps) || !is_array($scraps)) {
            return;
        }

        $scrap_field_map = [
            'sisa_coil'                => 'wip_kg',
            'tong_coil'                => 'tong_coil',
            'wrapping'                 => 'wrapping',
            'potongan_pisau'           => 'potongan_pisau',
            'reject_product_internal'  => 'reject_product_internal',
            'reject_internal'          => 'reject_product_internal',
            'reject_material_internal' => 'reject_material_internal',
            'reject_product_supplier'  => 'reject_product_supplier',
            'reject_material_supplier' => 'reject_material_supplier',
        ];

        // Cek rincian Reject Product vs Material dari claim_material bila data lama menyimpan reject_supplier tergabung
        $claim_rej_prod = 0.0;
        $claim_rej_mat  = 0.0;
        if (!empty($claims) && is_array($claims)) {
            foreach ($claims as $cl) {
                if (($cl['asal'] ?? '') === 'Reject (Supplier)') {
                    $prob = strtolower((string) ($cl['problem'] ?? ''));
                    if (strpos($prob, 'material') !== false) {
                        $claim_rej_mat += (float) ($cl['berat_kg'] ?? 0);
                    } else {
                        $claim_rej_prod += (float) ($cl['berat_kg'] ?? 0);
                    }
                }
            }
        }

        foreach ($scraps as $sc) {
            $st    = !empty($sc['scrap_type']) ? strtolower(trim($sc['scrap_type'])) : '';
            $berat = (float) ($sc['berat_total'] ?? 0);
            if ($berat <= 0 || $st === '') {
                continue;
            }
            $target = !empty($sc['target_coil_code']) ? trim($sc['target_coil_code']) : '';

            if ($st === 'reject_supplier') {
                if ($claim_rej_mat > 0 || $claim_rej_prod > 0) {
                    $mat_part  = min($claim_rej_mat, $berat);
                    $prod_part = max(0.0, $berat - $mat_part);
                    $this->_accumulate_export_metric($coil_ctx, $unassigned, $target, 'reject_product_supplier', $prod_part);
                    $this->_accumulate_export_metric($coil_ctx, $unassigned, $target, 'reject_material_supplier', $mat_part);
                } else {
                    $this->_accumulate_export_metric($coil_ctx, $unassigned, $target, 'reject_product_supplier', $berat);
                }
                continue;
            }

            if (isset($scrap_field_map[$st])) {
                $this->_accumulate_export_metric($coil_ctx, $unassigned, $target, $scrap_field_map[$st], $berat);
            }
        }
    }
}
