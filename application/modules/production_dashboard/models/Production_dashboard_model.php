<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // SECTION 1: Laporan Timbang Awal
    // =========================================================================

    /**
     * Perbandingan net weight timbang awal vs packing list per coil per SPK
     * Join tr_coil_preweigh + tr_coil_preweigh_component
     *
     * @param string|null $spk_no
     * @param string|null $tgl_dari
     * @param string|null $tgl_sampai
     * @return array
     */
    public function get_laporan_timbang_awal($spk_no = null, $tgl_dari = null, $tgl_sampai = null)
    {
        $this->db->select([
            'p.preweigh_no',
            'p.spk_no',
            'p.barcode_coil',
            'p.gross_actual',
            'p.gross_pl',
            'p.net_pl',
            'p.status',
            'p.created_at',
            'c.berat_kulit',
            'c.berat_clamp_ring',
            'c.berat_coil_tong',
            'c.berat_cover_wrapping',
            'c.net_timbang_awal',
            '(c.net_timbang_awal - p.net_pl) AS selisih_net',
            'CASE WHEN p.net_pl > 0 THEN ABS(c.net_timbang_awal - p.net_pl) / p.net_pl ELSE 0 END AS selisih_pct',
        ]);
        $this->db->from('tr_coil_preweigh p');
        $this->db->join('tr_coil_preweigh_component c', 'c.preweigh_no = p.preweigh_no', 'left');

        if (!empty($spk_no)) {
            $this->db->where('p.spk_no', $spk_no);
        }
        if (!empty($tgl_dari)) {
            $this->db->where('DATE(p.created_at) >=', $tgl_dari);
        }
        if (!empty($tgl_sampai)) {
            $this->db->where('DATE(p.created_at) <=', $tgl_sampai);
        }

        $this->db->order_by('p.spk_no', 'ASC');
        $this->db->order_by('p.created_at', 'ASC');

        return $this->db->get()->result();
    }

    // =========================================================================
    // SECTION 2: Laporan Hasil Produksi
    // =========================================================================

    /**
     * Total input, breakdown output per kategori, yield per SPK
     * Join tr_production_report + tr_production_report_result
     *
     * @param string|null $spk_no
     * @param string|null $tgl_dari
     * @param string|null $tgl_sampai
     * @return array
     */
    public function get_laporan_hasil_produksi($spk_no = null, $tgl_dari = null, $tgl_sampai = null)
    {
        $this->db->select([
            'r.report_no',
            'r.spk_no',
            'r.barcode_coil',
            'r.status',
            'r.created_at',
            'res.reject_supplier',
            'res.waste_potong',
            'res.ng_internal',
            'res.ng_supplier',
            'res.plat_bs',
            'res.fg_kg',
            'res.fg_qty',
            'res.kw2_internal_kg',
            'res.kw2_internal_qty',
            'res.kw2_supplier_kg',
            'res.kw2_supplier_qty',
            'res.tong_coil',
            'res.total_berat_coil',
            'res.berat_satuan_fg',
            // Yield per kategori (persen)
            'CASE WHEN res.total_berat_coil > 0 THEN ROUND(res.fg_kg / res.total_berat_coil * 100, 2) ELSE 0 END AS yield_fg_pct',
            'CASE WHEN res.total_berat_coil > 0 THEN ROUND(res.reject_supplier / res.total_berat_coil * 100, 2) ELSE 0 END AS yield_reject_pct',
            'CASE WHEN res.total_berat_coil > 0 THEN ROUND(res.ng_supplier / res.total_berat_coil * 100, 2) ELSE 0 END AS yield_ng_supplier_pct',
            'CASE WHEN res.total_berat_coil > 0 THEN ROUND(res.ng_internal / res.total_berat_coil * 100, 2) ELSE 0 END AS yield_ng_internal_pct',
            'CASE WHEN res.total_berat_coil > 0 THEN ROUND(res.kw2_supplier_kg / res.total_berat_coil * 100, 2) ELSE 0 END AS yield_kw2_supplier_pct',
            'CASE WHEN res.total_berat_coil > 0 THEN ROUND(res.kw2_internal_kg / res.total_berat_coil * 100, 2) ELSE 0 END AS yield_kw2_internal_pct',
            'CASE WHEN res.total_berat_coil > 0 THEN ROUND(res.waste_potong / res.total_berat_coil * 100, 2) ELSE 0 END AS yield_waste_pct',
            'CASE WHEN res.total_berat_coil > 0 THEN ROUND(res.plat_bs / res.total_berat_coil * 100, 2) ELSE 0 END AS yield_plat_bs_pct',
        ]);
        $this->db->from('tr_production_report r');
        $this->db->join('tr_production_report_result res', 'res.report_no = r.report_no', 'left');

        if (!empty($spk_no)) {
            $this->db->where('r.spk_no', $spk_no);
        }
        if (!empty($tgl_dari)) {
            $this->db->where('DATE(r.created_at) >=', $tgl_dari);
        }
        if (!empty($tgl_sampai)) {
            $this->db->where('DATE(r.created_at) <=', $tgl_sampai);
        }

        $this->db->order_by('r.spk_no', 'ASC');
        $this->db->order_by('r.created_at', 'ASC');

        return $this->db->get()->result();
    }

    // =========================================================================
    // SECTION 3: Laporan Delivery Discrepancy
    // =========================================================================

    /**
     * Selisih estimasi vs aktual per DO
     * Join tr_delivery_order + tr_delivery_detail + tr_delivery_weight_log
     *
     * @param string|null $tgl_dari
     * @param string|null $tgl_sampai
     * @return array
     */
    public function get_laporan_delivery_discrepancy($tgl_dari = null, $tgl_sampai = null)
    {
        $sql = "SELECT
                    d.do_no,
                    d.customer,
                    d.tgl_delivery,
                    d.status,
                    SUM(dd.estimasi_berat) AS total_estimasi_berat,
                    wl.berat_aktual,
                    wl.selisih_kg,
                    wl.selisih_pct,
                    wl.tgl_timbang
                FROM tr_delivery_order d
                LEFT JOIN tr_delivery_detail dd ON dd.do_no = d.do_no
                LEFT JOIN (
                    SELECT do_no, berat_aktual, selisih_kg, selisih_pct, tgl_timbang
                    FROM tr_delivery_weight_log
                    WHERE id IN (
                        SELECT MAX(id) FROM tr_delivery_weight_log GROUP BY do_no
                    )
                ) wl ON wl.do_no = d.do_no
                WHERE 1=1";

        $binds = [];

        if (!empty($tgl_dari)) {
            $sql .= " AND d.tgl_delivery >= ?";
            $binds[] = $tgl_dari;
        }
        if (!empty($tgl_sampai)) {
            $sql .= " AND d.tgl_delivery <= ?";
            $binds[] = $tgl_sampai;
        }

        $sql .= " GROUP BY d.do_no, d.customer, d.tgl_delivery, d.status,
                           wl.berat_aktual, wl.selisih_kg, wl.selisih_pct, wl.tgl_timbang
                  ORDER BY d.tgl_delivery DESC";

        return $this->db->query($sql, $binds)->result();
    }

    // =========================================================================
    // SECTION 4: Laporan Berat FG
    // =========================================================================

    /**
     * Berat acuan standar vs aktual FG per periode
     * Join fg_stock + ms_fg_weight_history
     *
     * @param string|null $tgl_dari
     * @param string|null $tgl_sampai
     * @return array
     */
    public function get_laporan_berat_fg($tgl_dari = null, $tgl_sampai = null)
    {
        $sql = "SELECT
                    s.produk_fg,
                    s.qty_stok,
                    s.total_berat,
                    s.berat_referensi AS berat_referensi_terkini,
                    s.last_update,
                    h.berat_referensi AS berat_referensi_historis,
                    h.total_qty_stok AS qty_stok_historis,
                    h.total_berat_stok AS total_berat_historis,
                    h.effective_date
                FROM fg_stock s
                LEFT JOIN ms_fg_weight_history h ON h.produk_fg = s.produk_fg
                WHERE 1=1";

        $binds = [];

        if (!empty($tgl_dari)) {
            $sql .= " AND DATE(h.effective_date) >= ?";
            $binds[] = $tgl_dari;
        }
        if (!empty($tgl_sampai)) {
            $sql .= " AND DATE(h.effective_date) <= ?";
            $binds[] = $tgl_sampai;
        }

        $sql .= " ORDER BY s.produk_fg ASC, h.effective_date DESC";

        return $this->db->query($sql, $binds)->result();
    }

    // =========================================================================
    // SECTION 5: Dashboard Summary
    // =========================================================================

    /**
     * Ringkasan untuk dashboard utama:
     * - Jumlah plan aktif (status Released/In Process)
     * - Jumlah SPK In Process
     * - Total stok FG (qty dan berat)
     * - Jumlah DO pending approval
     * - Jumlah laporan produksi menunggu review
     *
     * @return array
     */
    public function get_dashboard_summary()
    {
        // Plan aktif
        $plan_aktif = (int) $this->db
            ->where_in('status', ['Released'])
            ->count_all_results('tr_production_plan');

        // SPK In Process
        $spk_in_process = (int) $this->db
            ->where('status', 'In Process')
            ->count_all_results('tr_spk_production');

        // Total stok FG
        $stok_row = $this->db->query(
            "SELECT COALESCE(SUM(qty_stok), 0) AS total_qty, COALESCE(SUM(total_berat), 0) AS total_berat FROM fg_stock"
        )->row();
        $total_stok_qty   = $stok_row ? (float) $stok_row->total_qty : 0;
        $total_stok_berat = $stok_row ? (float) $stok_row->total_berat : 0;

        // DO pending approval
        $do_pending = (int) $this->db
            ->where('status', 'Waiting Approval')
            ->count_all_results('tr_delivery_order');

        // Laporan produksi menunggu review (status Submitted)
        $laporan_pending = (int) $this->db
            ->where('status', 'Submitted')
            ->count_all_results('tr_production_report');

        return [
            'plan_aktif'        => $plan_aktif,
            'spk_in_process'    => $spk_in_process,
            'total_stok_qty'    => $total_stok_qty,
            'total_stok_berat'  => $total_stok_berat,
            'do_pending'        => $do_pending,
            'laporan_pending'   => $laporan_pending,
        ];
    }

    // =========================================================================
    // SECTION 6: Helper — Daftar SPK untuk dropdown filter
    // =========================================================================

    /**
     * Ambil daftar SPK untuk dropdown filter laporan
     *
     * @return array
     */
    public function get_spk_list()
    {
        return $this->db
            ->select('spk_no, produk_fg, status')
            ->order_by('spk_no', 'DESC')
            ->get('tr_spk_production')
            ->result();
    }
}
