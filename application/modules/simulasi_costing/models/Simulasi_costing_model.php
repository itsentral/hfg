<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Simulasi_costing_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /** Ambil Kurs USD aktif */
    public function get_kurs_usd()
    {
        $row = $this->db->select('kurs')->where('kode', 'USD')->get('mata_uang')->row();
        return ($row && (float)$row->kurs > 0) ? (float)$row->kurs : 17863.0;
    }

    /** Ambil Parameter MPR (Man Power Rate 1/3 Operator) */
    public function get_man_power_rate()
    {
        $comp = $this->db->select_sum('nilai')->get('man_power_rate_component')->row();
        $total_gaji = ($comp && (float)$comp->nilai > 0) ? (float)$comp->nilai : 5100000.0;

        $mpr = $this->db->where('id', 1)->get('man_power_rate')->row();
        $jam_kerja = ($mpr && (float)$mpr->jam_kerja_per_bulan > 0) ? (float)$mpr->jam_kerja_per_bulan : 173.0;
        $rate_dl = $total_gaji / $jam_kerja;
        $rate_1_3_op = ($mpr && (float)$mpr->rate_1_3_operator > 0) ? (float)$mpr->rate_1_3_operator : ceil($rate_dl / 3);
        $porsi_den = 3;

        return [
            'total_gaji'  => $total_gaji,
            'jam_kerja'   => $jam_kerja,
            'porsi_den'   => $porsi_den,
            'rate_dl'     => $rate_dl,
            'rate_1_3_op' => (float)$rate_1_3_op,
        ];
    }

    /** Ambil Faktor Indirect Labor aktif */
    public function get_faktor_indirect()
    {
        $row = $this->db->where('id', 1)->get('faktor_indirect_labor')->row();
        return $row ? (float)$row->faktor_indirect_labor : 1.5;
    }

    /** Ambil General Rate (9 elemen) */
    public function get_general_rate()
    {
        $row = $this->db->where('id', 1)->get('rate_generals')->row();
        if (!$row) {
            return (object)[
                'rate_consumables'                => 100.0,
                'rate_scrap'                      => 0.02,
                'rate_biaya_koordinasi'           => 50.0,
                'rate_other_cost_sales_marketing' => 0.02,
                'rate_other_cost_transport'       => 0.015,
                'rate_other_cost_finance_admin'   => 0.01,
                'rate_other_cost_management_fee'  => 0.02,
                'rate_other_cost_interest'        => 0.005,
                'rate_other_cost_profit'          => 0.05,
            ];
        }
        return $row;
    }

    /** Ambil Rate Borongan Helper per kategori */
    public function get_borongan_rates()
    {
        $rows = $this->db->get('rate_borongan_helper')->result();
        $res = [];
        foreach ($rows as $r) {
            $res[$r->code_lv2] = (float)$r->rate_borongan_helper;
        }
        return $res;
    }

    /** Ambil Rate BPK & FOH per kategori */
    public function get_bpk_foh_rates()
    {
        $rows = $this->db->get('rate_bahan_pendukung_foh')->result();
        $res = [];
        foreach ($rows as $r) {
            $res[$r->code_lv2] = [
                'bpk' => (float)$r->rate_pendukung_khusus,
                'foh' => (float)$r->rate_foh,
            ];
        }
        return $res;
    }

    /** Ambil Rate Extra Margin Aktif per kategori */
    public function get_nego_rates()
    {
        $rows = $this->db->get('rate_negotiation_allowance')->result();
        $res = [];
        foreach ($rows as $r) {
            $res[$r->code_lv2] = (float)$r->rate_negotiation_allowance;
        }
        return $res;
    }

    /** Ambil Total Man Hour per Product Category dari Flow Process */
    public function get_flow_man_hours()
    {
        $sql = "
            SELECT h.code_lv2, SUM((d.cycletime * d.man_power) / 60) AS total_man_hour
            FROM flow_process_header h
            JOIN flow_process_detail d ON d.id_flow_process_header = h.id
            GROUP BY h.code_lv2
        ";
        $rows = $this->db->query($sql)->result();
        $res = [];
        foreach ($rows as $r) {
            $res[$r->code_lv2] = (float)$r->total_man_hour;
        }
        return $res;
    }

    /** Ambil Price Reference Max (USD/Kg) per Material dari Master Price Reference (new_inventory_4) & PO History (dt_trans_po) */
    public function get_price_reference_max()
    {
        $res = [];

        // 1. Ambil dari History PO dt_trans_po (nilai MAX USD)
        $sql_po = "
            SELECT idmaterial, MAX(hargasatuan) AS max_usd
            FROM dt_trans_po
            WHERE hargasatuan > 0
            GROUP BY idmaterial
        ";
        $rows_po = $this->db->query($sql_po)->result();
        foreach ($rows_po as $r) {
            if (!empty($r->idmaterial)) {
                $res[$r->idmaterial] = (float)$r->max_usd;
            }
        }

        // 2. Ambil dari Master Material new_inventory_4 (Price Reference resmi yang disetujui Finance)
        // Prioritas utama: jika Finance sudah menentukan/approve price_ref_use_usd > 0
        $sql_mat = "
            SELECT id, code_lv4, price_ref_use_usd, price_ref_usd, price_ref_high_usd
            FROM new_inventory_4
            WHERE category = 'material' AND deleted_date IS NULL
        ";
        $rows_mat = $this->db->query($sql_mat)->result();
        foreach ($rows_mat as $m) {
            $ref_usd = (float)($m->price_ref_use_usd ?: ($m->price_ref_usd ?: $m->price_ref_high_usd));
            if ($ref_usd > 0) {
                if (!empty($m->code_lv4)) {
                    $res[$m->code_lv4] = $ref_usd;
                }
                $res[$m->id] = $ref_usd;
            }
        }

        return $res;
    }

    /** Ambil seluruh varian BOM yang aktif */
    public function get_all_boms($category_filter = null)
    {
        $sql = "
            SELECT 
                h.id AS bom_id,
                h.id_produk AS code_lv4,
                h.nm_produk,
                d.id AS bom_detail_id,
                d.no_uniq_id,
                d.id_material,
                d.nm_material,
                d.berat_per_meter,
                p.code_lv2,
                p.code_lv1,
                p.nama AS nm_product,
                cat.nama AS nm_category,
                p.panjang,
                p.length,
                p.wide
            FROM ms_bom_header h
            JOIN ms_bom_detail d ON d.id_bom = h.id AND d.is_delete = 0
            LEFT JOIN product_lvl_4 p ON p.code_lv4 = h.id_produk
            LEFT JOIN product_lvl_2 cat ON cat.code_lv2 = p.code_lv2
            WHERE h.is_delete = 0
              AND h.status = 1
        ";

        if (!empty($category_filter)) {
            $sql .= " AND p.code_lv2 = " . $this->db->escape($category_filter);
        }

        $sql .= " ORDER BY cat.nama ASC, p.nama ASC, d.id ASC";

        return $this->db->query($sql)->result();
    }

    /** Ambil daftar kategori produk untuk filter */
    public function get_categories()
    {
        return $this->db->select('code_lv2, nama')
            ->from('product_lvl_2')
            ->where('deleted_date', null)
            ->where('category', 'product')
            ->order_by('nama', 'ASC')
            ->get()->result();
    }

    /** Hitung Costing Lengkap untuk seluruh varian atau 1 varian */
    public function calculate_all_costing($category_filter = null)
    {
        $boms         = $this->get_all_boms($category_filter);
        $kurs_usd     = $this->get_kurs_usd();
        $mpr          = $this->get_man_power_rate();
        $faktor_ind   = $this->get_faktor_indirect();
        $gen          = $this->get_general_rate();
        $borong_map   = $this->get_borongan_rates();
        $bpk_foh_map  = $this->get_bpk_foh_rates();
        $nego_map     = $this->get_nego_rates();
        $man_hour_map = $this->get_flow_man_hours();
        $pr_max_map   = $this->get_price_reference_max();

        $results = [];

        foreach ($boms as $b) {
            $c = $this->calculate_single_bom(
                $b, $kurs_usd, $mpr, $faktor_ind, $gen, 
                $borong_map, $bpk_foh_map, $nego_map, $man_hour_map, $pr_max_map
            );
            $results[] = $c;
        }

        return $results;
    }

    /** Mesin Hitung 1 Varian BOM */
    public function calculate_single_bom($b, $kurs_usd, $mpr, $faktor_ind, $gen, $borong_map, $bpk_foh_map, $nego_map, $man_hour_map, $pr_max_map)
    {
        $cat_code = $b->code_lv2;
        $mat_id   = $b->id_material;

        // 1. Price Reference & Kurs
        $pr_usd = isset($pr_max_map[$mat_id]) ? (float)$pr_max_map[$mat_id] : 0.6800; // default jika belum ada history
        $pr_idr = $pr_usd * $kurs_usd;

        // 2. Berat per Meter & Ukuran
        $bpm    = (float)$b->berat_per_meter;
        $ukuran = (float)($b->panjang ?: ($b->length ?: 1.0));
        if ($ukuran <= 0) $ukuran = 1.0;

        // 3. Cek Kekurangan Data
        $gaps = [];
        if ($bpm <= 0) $gaps[] = 'Berat/Meter ≤ 0';
        if (!isset($man_hour_map[$cat_code]) || $man_hour_map[$cat_code] <= 0) $gaps[] = 'Flow Process / Cycletime belum ada';
        if (!isset($bpk_foh_map[$cat_code])) $gaps[] = 'Rate BPK & FOH belum diset';
        if (!isset($borong_map[$cat_code])) $gaps[] = 'Rate Borongan Helper belum diset';
        if (!isset($nego_map[$cat_code])) $gaps[] = 'Negotiation Allowance belum diset';
        if ($pr_usd <= 0) $gaps[] = 'Price Reference belum ada';

        // 4. Material Cost
        $material_meter = $pr_idr * $bpm;
        $material_kg    = $pr_idr;

        // 5. Biaya Proses
        $mh          = isset($man_hour_map[$cat_code]) ? (float)$man_hour_map[$cat_code] : 0.0;
        $rate_op     = (float)$mpr['rate_1_3_op'];
        $borong      = isset($borong_map[$cat_code]) ? (float)$borong_map[$cat_code] : 0.0;
        $man_power_m = (($mh * $rate_op) + $borong) * $faktor_ind;

        $bpk_rate    = isset($bpk_foh_map[$cat_code]) ? (float)$bpk_foh_map[$cat_code]['bpk'] : 0.0;
        $foh_rate    = isset($bpk_foh_map[$cat_code]) ? (float)$bpk_foh_map[$cat_code]['foh'] : 0.0;
        $bpk_m       = $bpk_rate * $bpm;
        $foh_m       = $foh_rate * $bpm;
        $proses_m    = $man_power_m + $bpk_m + $foh_m;

        // 6. Manufacturing Support
        $cons_rate   = (float)$gen->rate_consumables;
        $scrap_rate  = (float)$gen->rate_scrap;
        $koord_rate  = (float)$gen->rate_biaya_koordinasi;

        $cons_m      = $cons_rate * $bpm;
        $scrap_m     = $material_meter * $scrap_rate;
        $koord_m     = $koord_rate * $bpm;
        $support_m   = $cons_m + $scrap_m + $koord_m;

        // 7. HPP
        $hpp_m       = $material_meter + $proses_m + $support_m;

        // 8. Other Cost (% x HPP)
        $o_sales     = $hpp_m * (float)$gen->rate_other_cost_sales_marketing;
        $o_trans     = $hpp_m * (float)$gen->rate_other_cost_transport;
        $o_fa        = $hpp_m * (float)$gen->rate_other_cost_finance_admin;
        $o_mgmt      = $hpp_m * (float)$gen->rate_other_cost_management_fee;
        $o_int       = $hpp_m * (float)$gen->rate_other_cost_interest;
        $o_prof      = $hpp_m * (float)$gen->rate_other_cost_profit;
        $other_tot_m = $o_sales + $o_trans + $o_fa + $o_mgmt + $o_int + $o_prof;

        // 9. Bottom Price
        $bottom_m    = $hpp_m + $other_tot_m;

        // 10. Negotiation Allowance (Extra Margin) & Price List
        $nego_pct    = isset($nego_map[$cat_code]) ? (float)$nego_map[$cat_code] : 0.03;
        $nego_m      = $bottom_m * $nego_pct;
        $pricelist_m = $bottom_m + $nego_m;
        $pricelist_pcs = $pricelist_m * $ukuran;

        // Konversi per Kg = Nilai Meter / bpm
        $div_kg = function($val) use ($bpm) {
            return ($bpm > 0) ? ($val / $bpm) : 0.0;
        };

        return (object)[
            'bom_id'          => $b->bom_id,
            'bom_detail_id'   => $b->bom_detail_id,
            'code_lv4'        => $b->code_lv4,
            'nm_product'      => $b->nm_product ?: $b->nm_produk,
            'code_lv2'        => $b->code_lv2,
            'nm_category'     => $b->nm_category ?: $b->code_lv2,
            'id_material'     => $b->id_material,
            'nm_material'     => $b->nm_material,
            'no_uniq_id'      => $b->no_uniq_id,
            'berat_per_meter' => $bpm,
            'ukuran'          => $ukuran,
            'pr_usd'          => $pr_usd,
            'kurs_usd'        => $kurs_usd,
            'pr_idr'          => $pr_idr,
            'total_man_hour'  => $mh,
            'rate_1_3_op'     => $rate_op,
            'rate_borongan'   => $borong,
            'faktor_indirect' => $faktor_ind,
            'nego_pct'        => $nego_pct,
            'gaps'            => $gaps,
            'is_lengkap'      => empty($gaps),

            // Nilai Meter
            'material_meter'   => $material_meter,
            'man_power_meter'  => $man_power_m,
            'bpk_meter'        => $bpk_m,
            'foh_meter'        => $foh_m,
            'proses_meter'     => $proses_m,
            'cons_meter'       => $cons_m,
            'scrap_meter'      => $scrap_m,
            'koord_meter'      => $koord_m,
            'support_meter'    => $support_m,
            'hpp_meter'        => $hpp_m,
            'other_sales_meter'=> $o_sales,
            'other_trans_meter'=> $o_trans,
            'other_fa_meter'   => $o_fa,
            'other_mgmt_meter' => $o_mgmt,
            'other_int_meter'  => $o_int,
            'other_prof_meter' => $o_prof,
            'other_total_meter'=> $other_tot_m,
            'bottom_meter'     => $bottom_m,
            'nego_meter'       => $nego_m,
            'pricelist_meter'  => $pricelist_m,
            'pricelist_pcs'    => $pricelist_pcs,

            // Nilai Kg
            'material_kg'      => $material_kg,
            'man_power_kg'     => $div_kg($man_power_m),
            'bpk_kg'           => $div_kg($bpk_m),
            'foh_kg'           => $div_kg($foh_m),
            'proses_kg'        => $div_kg($proses_m),
            'cons_kg'          => $div_kg($cons_m),
            'scrap_kg'         => $div_kg($scrap_m),
            'koord_kg'         => $div_kg($koord_m),
            'support_kg'       => $div_kg($support_m),
            'hpp_kg'           => $div_kg($hpp_m),
            'other_sales_kg'   => $div_kg($o_sales),
            'other_trans_kg'   => $div_kg($o_trans),
            'other_fa_kg'      => $div_kg($o_fa),
            'other_mgmt_kg'    => $div_kg($o_mgmt),
            'other_int_kg'     => $div_kg($o_int),
            'other_prof_kg'    => $div_kg($o_prof),
            'other_total_kg'   => $div_kg($other_tot_m),
            'bottom_kg'        => $div_kg($bottom_m),
            'nego_kg'          => $div_kg($nego_m),
            'pricelist_kg'     => $div_kg($pricelist_m),

            'last_updated'     => date('Y-m-d H:i:s'),
        ];
    }

    /** Jalankan Update Costing: Catat ke History Costing & Memicu Notifikasi Bottom Price */
    public function execute_update_costing($user_id)
    {
        $costings = $this->calculate_all_costing();
        $batch_id = 'BATCH-' . date('YmdHis');
        $now      = date('Y-m-d H:i:s');

        $entries_count = 0;
        $notif_count   = 0;

        foreach ($costings as $c) {
            // Ambil snapshot terakhir dari product_costing_detail
            $old = $this->db->where('code_lv4', $c->code_lv4)
                ->where('no_uniq_id', $c->no_uniq_id)
                ->get('product_costing_detail')
                ->row();

            $is_new = empty($old);
            $bottom_lama = $is_new ? 0.0 : (float)$old->selling_price_bottom_price_meter;
            $bottom_baru = (float)$c->bottom_meter;

            // Cek apakah ada perubahan nilai (selisih >= 0.01)
            $diff = abs($bottom_baru - $bottom_lama);

            if ($is_new || $diff >= 0.01) {
                // 1. Simpan ke history_costing
                $snapshot_data = [
                    'old' => $is_new ? null : [
                        'material'  => (float)$old->material_cost_meter,
                        'proses'    => (float)$old->biaya_proses_total_meter,
                        'support'   => (float)$old->manufacturing_support_total_meter,
                        'other'     => (float)$old->other_cost_total_meter,
                        'hpp'       => (float)$old->hpp_meter,
                        'bottom'    => (float)$old->selling_price_bottom_price_meter,
                        'priceList' => (float)$old->selling_price_price_list_meter,
                    ],
                    'now' => [
                        'material'  => $c->material_meter,
                        'proses'    => $c->proses_meter,
                        'support'   => $c->support_meter,
                        'other'     => $c->other_total_meter,
                        'hpp'       => $c->hpp_meter,
                        'bottom'    => $c->bottom_meter,
                        'priceList' => $c->pricelist_meter,
                    ],
                ];

                $this->db->insert('history_costing', [
                    'batch_id'                   => $batch_id,
                    'code_lv4'                   => $c->code_lv4,
                    'no_uniq_id'                 => $c->no_uniq_id,
                    'code_lv2'                   => $c->code_lv2,
                    'changed_at'                 => $now,
                    'changed_by'                 => $user_id,
                    'change_source'              => 'Update Costing Terkini',
                    'change_old_value'           => 'Bottom Price: Rp ' . number_format($bottom_lama, 2),
                    'change_new_value'           => 'Bottom Price: Rp ' . number_format($bottom_baru, 2),
                    'bottom_price_lama_meter'    => $bottom_lama,
                    'bottom_price_lama_kg'       => $is_new ? 0.0 : (float)$old->selling_price_bottom_price_kg,
                    'bottom_price_baru_meter'    => $bottom_baru,
                    'bottom_price_baru_kg'       => $c->bottom_kg,
                    'price_list_lama_meter'      => $is_new ? 0.0 : (float)$old->selling_price_price_list_meter,
                    'price_list_baru_meter'      => $c->pricelist_meter,
                    'rate_negotiation_allowance' => $c->nego_pct,
                    'detail_komponen'            => json_encode($snapshot_data),
                ]);
                $entries_count++;

                // 2. Buat notifikasi Bottom Price jika bukan data pertama dan ada pergeseran
                if (!$is_new && $diff >= 0.01) {
                    $this->db->insert('bottom_price_notification', [
                        'code_lv4'                   => $c->code_lv4,
                        'no_uniq_id'                 => $c->no_uniq_id,
                        'code_lv2'                   => $c->code_lv2,
                        'bottom_price_lama_meter'    => $bottom_lama,
                        'bottom_price_lama_kg'       => (float)$old->selling_price_bottom_price_kg,
                        'bottom_price_baru_meter'    => $bottom_baru,
                        'bottom_price_baru_kg'       => $c->bottom_kg,
                        'rate_negotiation_allowance' => $c->nego_pct,
                        'price_list_saat_ini_meter'  => (float)$old->selling_price_price_list_meter,
                        'price_list_baru_meter'      => $c->pricelist_meter,
                        'status'                     => 'Belum Ditinjau',
                        'detected_at'                => $now,
                    ]);
                    $notif_count++;
                }

                // 3. Simpan / Perbarui Snapshot di product_costing_detail
                $detail_payload = [
                    'code_lv4'                                  => $c->code_lv4,
                    'no_uniq_id'                                => $c->no_uniq_id,
                    'code_lv2'                                  => $c->code_lv2,
                    'ukuran_product'                            => $c->ukuran,
                    'berat_per_meter'                           => $c->berat_per_meter,
                    'material_cost_meter'                       => $c->material_meter,
                    'material_cost_kg'                          => $c->material_kg,
                    'biaya_proses_man_power_meter'              => $c->man_power_meter,
                    'biaya_proses_man_power_kg'                 => $c->man_power_kg,
                    'biaya_proses_bahan_pendukung_khusus_meter' => $c->bpk_meter,
                    'biaya_proses_bahan_pendukung_khusus_kg'    => $c->bpk_kg,
                    'biaya_proses_foh_meter'                    => $c->foh_meter,
                    'biaya_proses_foh_kg'                       => $c->foh_kg,
                    'biaya_proses_total_meter'                  => $c->proses_meter,
                    'biaya_proses_total_kg'                     => $c->proses_kg,
                    'manufacturing_support_consumables_meter'   => $c->cons_meter,
                    'manufacturing_support_consumables_kg'      => $c->cons_kg,
                    'manufacturing_support_biaya_koordinasi_meter'=> $c->koord_meter,
                    'manufacturing_support_biaya_koordinasi_kg' => $c->koord_kg,
                    'manufacturing_support_scrap_meter'         => $c->scrap_meter,
                    'manufacturing_support_scrap_kg'            => $c->scrap_kg,
                    'manufacturing_support_total_meter'         => $c->support_meter,
                    'manufacturing_support_total_kg'            => $c->support_kg,
                    'hpp_meter'                                 => $c->hpp_meter,
                    'hpp_kg'                                    => $c->hpp_kg,
                    'other_cost_sales_marketing_meter'          => $c->other_sales_meter,
                    'other_cost_sales_marketing_kg'             => $c->other_sales_kg,
                    'other_cost_transport_meter'                => $c->other_trans_meter,
                    'other_cost_transport_kg'                   => $c->other_trans_kg,
                    'other_cost_finance_admin_meter'            => $c->other_fa_meter,
                    'other_cost_finance_admin_kg'               => $c->other_fa_kg,
                    'other_cost_management_fee_meter'           => $c->other_mgmt_meter,
                    'other_cost_management_fee_kg'              => $c->other_mgmt_kg,
                    'other_cost_interest_meter'                 => $c->other_int_meter,
                    'other_cost_interest_kg'                    => $c->other_int_kg,
                    'other_cost_profit_meter'                   => $c->other_prof_meter,
                    'other_cost_profit_kg'                      => $c->other_prof_kg,
                    'other_cost_total_meter'                    => $c->other_total_meter,
                    'other_cost_total_kg'                       => $c->other_total_kg,
                    'selling_price_bottom_price_meter'          => $c->bottom_meter,
                    'selling_price_bottom_price_kg'             => $c->bottom_kg,
                    'selling_price_negotiation_allowance_meter' => $c->nego_meter,
                    'selling_price_negotiation_allowance_kg'    => $c->nego_kg,
                    'selling_price_price_list_meter'            => $c->pricelist_meter,
                    'selling_price_price_list_kg'               => $c->pricelist_kg,
                    'selling_price_price_list_pcs'              => $c->pricelist_pcs,
                    'rate_negotiation_allowance'                => $c->nego_pct,
                    'latest_update'                             => $now,
                    'updated_by'                                => $user_id,
                ];

                if ($is_new) {
                    $this->db->insert('product_costing_detail', $detail_payload);
                } else {
                    $this->db->where('id', $old->id)->update('product_costing_detail', $detail_payload);
                }

                // 4. Update Header per Product
                $header = $this->db->where('code_lv4', $c->code_lv4)->get('product_costing_header')->row();
                $header_payload = [
                    'code_lv4'              => $c->code_lv4,
                    'ukuran_product'        => $c->ukuran,
                    'material_cost'         => $c->material_meter,
                    'biaya_proses'          => $c->proses_meter,
                    'manufacturing_support' => $c->support_meter,
                    'hpp'                   => $c->hpp_meter,
                    'other_cost'            => $c->other_total_meter,
                    'selling_price'         => $c->pricelist_meter,
                    'latest_update'         => $now,
                    'updated_by'            => $user_id,
                ];
                if ($header) {
                    $this->db->where('id', $header->id)->update('product_costing_header', $header_payload);
                } else {
                    $this->db->insert('product_costing_header', $header_payload);
                }
            }
        }

        return [
            'entries' => $entries_count,
            'notif'   => $notif_count,
        ];
    }

    /** Masukkan varian ke Draft Pricelist */
    public function add_to_draft_pricelist($no_uniq_id, $user_id)
    {
        // Cari varian costing
        $costings = $this->calculate_all_costing();
        $target = null;
        foreach ($costings as $c) {
            if ($c->no_uniq_id === $no_uniq_id) {
                $target = $c;
                break;
            }
        }

        if (!$target) {
            return ['status' => 0, 'pesan' => 'Varian BOM tidak ditemukan'];
        }

        if (!$target->is_lengkap) {
            $gaps_text = implode(', ', $target->gaps);
            return [
                'status' => 0,
                'pesan'  => "Tidak dapat mengajukan ke Draft Pricelist karena parameter belum lengkap: {$gaps_text}."
            ];
        }

        // Cari draft pricelist aktif atau buat baru
        $pl = $this->db->where('status', 'Draft')->order_by('id', 'DESC')->get('pricelist_header')->row();
        if (!$pl) {
            $year = date('Y');
            $count = $this->db->like('no_pricelist', 'PL-' . $year)->count_all_results('pricelist_header') + 1;
            $no_pl = 'PL-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $this->db->insert('pricelist_header', [
                'no_pricelist' => $no_pl,
                'status'       => 'Draft',
                'created_by'   => $user_id,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            $pl_id = $this->db->insert_id();
            $pl_no = $no_pl;
        } else {
            $pl_id = $pl->id;
            $pl_no = $pl->no_pricelist;
        }

        // Cek apakah varian sudah ada di draft ini
        $exists = $this->db->where('id_pricelist_header', $pl_id)
            ->where('no_uniq_id', $no_uniq_id)
            ->get('pricelist_detail')
            ->row();

        if ($exists) {
            return ['status' => 0, 'pesan' => "Varian {$no_uniq_id} sudah ada di Draft Pricelist {$pl_no}."];
        }

        // Ambil snapshot id_product_costing_detail jika ada
        $pcd = $this->db->where('code_lv4', $target->code_lv4)
            ->where('no_uniq_id', $no_uniq_id)
            ->get('product_costing_detail')
            ->row();

        $this->db->insert('pricelist_detail', [
            'id_pricelist_header'       => $pl_id,
            'id_product_costing_detail' => $pcd ? $pcd->id : null,
            'code_lv4'                  => $target->code_lv4,
            'no_uniq_id'                => $target->no_uniq_id,
            'code_lv2'                  => $target->code_lv2,
            'ukuran_product'            => $target->ukuran,
            'berat_per_meter'           => $target->berat_per_meter,
            'bottom_price_meter'        => $target->bottom_meter,
            'bottom_price_kg'           => $target->bottom_kg,
            'rate_negotiation_allowance'=> $target->nego_pct,
            'price_list_meter'          => $target->pricelist_meter,
            'price_list_kg'             => $target->pricelist_kg,
            'price_list_pcs'            => $target->pricelist_pcs,
        ]);

        return [
            'status' => 1,
            'pesan'  => "Varian {$no_uniq_id} berhasil dimasukkan ke Draft Pricelist {$pl_no}.",
            'no_pl'  => $pl_no,
        ];
    }
}
