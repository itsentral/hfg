<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_monitoring_po_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil data report monitoring open PO supplier dengan kalkulasi progress & status,
     * serta relasi ke table material_planning_base_on_produksi untuk No PR dan Tipe PR.
     */
    public function get_monitoring_data($filter_status = null, $filter_supplier = null, $tgl_awal = null, $tgl_akhir = null)
    {
        $this->db->select("
            p.no_po,
            p.no_surat,
            p.tanggal,
            p.id_suplier,
            COALESCE(s.nama, p.id_suplier) as nm_supplier,
            COALESCE(SUM(dpo.qty), 0) as total_qty_po,
            GROUP_CONCAT(DISTINCT dpo.namamaterial ORDER BY dpo.id ASC SEPARATOR '||') as materials_list,
            COUNT(DISTINCT dpo.idmaterial) as count_materials,
            GROUP_CONCAT(DISTINCT IF(mp.no_pr IS NOT NULL, CONCAT_WS(':::', mp.no_pr, COALESCE(mp.category, '-')), NULL) ORDER BY mp.no_pr ASC SEPARATOR '||') as pr_list,
            COALESCE((
                SELECT SUM(d.berat_bersih)
                FROM tr_incoming_header h
                JOIN tr_incoming_detail d ON h.kode_trans = d.kode_trans
                WHERE h.no_po = p.no_po
            ), 0) as total_diterima
        ");
        $this->db->from("tr_purchase_order p");
        $this->db->join("new_supplier s", "p.id_suplier = s.kode_supplier", "left");
        $this->db->join("dt_trans_po dpo", "p.no_po = dpo.no_po", "left");
        $this->db->join("material_planning_base_on_produksi mp", "dpo.idpr = mp.id", "left");
        
        // Filter supplier
        if (!empty($filter_supplier)) {
            $this->db->where("p.id_suplier", $filter_supplier);
        }

        // Filter tanggal PO
        if (!empty($tgl_awal)) {
            $this->db->where("p.tanggal >=", $tgl_awal);
        }
        if (!empty($tgl_akhir)) {
            $this->db->where("p.tanggal <=", $tgl_akhir);
        }

        $this->db->where("p.status !=", '0');
        $this->db->group_by("p.no_po");
        $this->db->order_by("p.tanggal", "DESC");
        $this->db->order_by("p.no_po", "DESC");

        $query = $this->db->get();
        $results = $query->result();

        $data = [];
        foreach ($results as $row) {
            $qty_po       = (float) $row->total_qty_po;
            $qty_diterima = (float) $row->total_diterima;
            $sisa_qty     = $qty_po - $qty_diterima;

            // Hitung persentase progress
            if ($qty_po > 0) {
                $progress_pct = round(($qty_diterima / $qty_po) * 100, 2);
            } else {
                $progress_pct = ($qty_diterima > 0) ? 100 : 0;
            }

            // Tentukan status kriteria
            if ($qty_diterima == 0) {
                $status_code  = 'belum_mulai';
                $status_label = 'Belum Mulai';
                $status_badge = '<span class="badge bg-light-secondary text-secondary fw-semibold px-2 py-1"><i class="ti ti-clock me-1"></i>Belum Mulai</span>';
                $status_desc  = 'Belum ada penerimaan sama sekali';
            } elseif ($qty_diterima > 0 && $qty_diterima < $qty_po) {
                $status_code  = 'partial';
                $status_label = 'Partial';
                $status_badge = '<span class="badge bg-light-warning text-warning fw-semibold px-2 py-1"><i class="ti ti-hourglass-empty me-1"></i>Partial</span>';
                $status_desc  = 'Sudah ada penerimaan, tapi belum mencapai total Qty PO';
            } elseif ($qty_diterima == $qty_po) {
                $status_code  = 'selesai';
                $status_label = 'Selesai';
                $status_badge = '<span class="badge bg-light-success text-success fw-semibold px-2 py-1"><i class="ti ti-check me-1"></i>Selesai</span>';
                $status_desc  = 'Total diterima = Total Qty PO';
            } else {
                $status_code  = 'over_delivery';
                $status_label = 'Over-delivery';
                $status_badge = '<span class="badge bg-light-danger text-danger fw-semibold px-2 py-1" title="Total diterima melebihi Qty PO — perlu klarifikasi ke supplier"><i class="ti ti-alert-triangle me-1"></i>Over-delivery</span>';
                $status_desc  = 'Total diterima melebihi Qty PO — perlu klarifikasi ke supplier';
            }

            // Filter status jika dipilih
            if (!empty($filter_status) && $filter_status !== 'all') {
                if ($status_code !== $filter_status) {
                    continue;
                }
            }

            // Pisahkan material list
            $materials_arr = [];
            if (!empty($row->materials_list)) {
                $materials_arr = explode('||', $row->materials_list);
            }

            // Pisahkan PR list (No PR & Tipe PR)
            $pr_arr = [];
            $pr_strings = [];
            if (!empty($row->pr_list)) {
                $pairs = explode('||', $row->pr_list);
                foreach ($pairs as $p) {
                    $parts = explode(':::', $p);
                    $no_pr   = trim($parts[0] ?? '');
                    $tipe_pr = trim($parts[1] ?? '-');
                    if (!empty($no_pr)) {
                        $pr_arr[] = [
                            'no_pr'   => $no_pr,
                            'tipe_pr' => $tipe_pr,
                        ];
                        $pr_strings[] = $no_pr . ' (' . $tipe_pr . ')';
                    }
                }
            }

            $row->total_qty_po   = $qty_po;
            $row->total_diterima = $qty_diterima;
            $row->sisa_qty       = $sisa_qty;
            $row->progress_pct   = $progress_pct;
            $row->status_code    = $status_code;
            $row->status_label   = $status_label;
            $row->status_badge   = $status_badge;
            $row->status_desc    = $status_desc;
            $row->materials_arr  = $materials_arr;
            $row->pr_arr         = $pr_arr;
            $row->pr_text        = !empty($pr_strings) ? implode(", ", $pr_strings) : '-';

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Ambil data ringkasan counter (Total PO, Qty PO, Diterima, Sisa, Over-delivery)
     */
    public function get_summary_counters()
    {
        $all = $this->get_monitoring_data();

        $summary = [
            'total_po'             => count($all),
            'total_qty_po'         => 0,
            'total_diterima'       => 0,
            'total_sisa'           => 0,
            'count_belum_mulai'    => 0,
            'count_partial'        => 0,
            'count_selesai'        => 0,
            'count_over_delivery'  => 0,
        ];

        foreach ($all as $item) {
            $summary['total_qty_po']   += $item->total_qty_po;
            $summary['total_diterima'] += $item->total_diterima;
            if ($item->sisa_qty > 0) {
                $summary['total_sisa'] += $item->sisa_qty;
            }

            if ($item->status_code === 'belum_mulai')        $summary['count_belum_mulai']++;
            elseif ($item->status_code === 'partial')       $summary['count_partial']++;
            elseif ($item->status_code === 'selesai')       $summary['count_selesai']++;
            elseif ($item->status_code === 'over_delivery') $summary['count_over_delivery']++;
        }

        return $summary;
    }

    /**
     * Ambil detail data per No PO, termasuk relasi PR dan riwayat kedatangan kronologis (oldest to newest)
     */
    public function get_po_detail($no_po)
    {
        // 1. PO Header
        $this->db->select("
            p.no_po,
            p.no_surat,
            p.tanggal,
            p.id_suplier,
            COALESCE(s.nama, p.id_suplier) as nm_supplier,
            p.status as po_status
        ");
        $this->db->from("tr_purchase_order p");
        $this->db->join("new_supplier s", "p.id_suplier = s.kode_supplier", "left");
        $this->db->where("p.no_po", $no_po);
        $header = $this->db->get()->row();

        if (!$header) {
            return null;
        }

        // 2. Material & PR ordered in PO
        $this->db->select("
            dpo.id,
            dpo.no_po,
            dpo.idmaterial,
            dpo.namamaterial,
            dpo.qty,
            dpo.idpr,
            mp.no_pr,
            mp.category as tipe_pr
        ");
        $this->db->from("dt_trans_po dpo");
        $this->db->join("material_planning_base_on_produksi mp", "dpo.idpr = mp.id", "left");
        $this->db->where("dpo.no_po", $no_po);
        $this->db->order_by("dpo.id", "ASC");
        $po_materials = $this->db->get()->result();

        $total_qty_po = 0;
        $pr_list_arr = [];
        foreach ($po_materials as $pm) {
            $total_qty_po += (float) $pm->qty;
            if (!empty($pm->no_pr)) {
                $pr_list_arr[$pm->no_pr] = $pm->no_pr . ' (' . ($pm->tipe_pr ?? '-') . ')';
            }
        }

        // 3. Incoming arrivals history (diurutkan kronologis: tanggal ASC, id ASC)
        $this->db->select("
            h.id as incoming_id,
            h.kode_trans,
            h.no_po,
            h.no_surat,
            h.tanggal,
            d.id_material,
            d.nm_material,
            SUM(d.berat_bersih) as qty_diterima
        ");
        $this->db->from("tr_incoming_header h");
        $this->db->join("tr_incoming_detail d", "h.kode_trans = d.kode_trans");
        $this->db->where("h.no_po", $no_po);
        $this->db->group_by("h.kode_trans");
        $this->db->group_by("d.nm_material");
        $this->db->order_by("h.tanggal", "ASC");
        $this->db->order_by("h.id", "ASC");
        $incoming_query = $this->db->get()->result();

        $incoming_history = [];
        $kumulatif = 0;
        $total_diterima = 0;

        foreach ($incoming_query as $inc) {
            $qty_terima = (float) $inc->qty_diterima;
            $kumulatif += $qty_terima;
            $sisa_setelah_ini = $total_qty_po - $kumulatif;

            $inc->qty_diterima            = $qty_terima;
            $inc->kumulatif_diterima      = $kumulatif;
            $inc->sisa_setelah_kedatangan = $sisa_setelah_ini;

            $incoming_history[] = $inc;
            $total_diterima += $qty_terima;
        }

        // Ringkasan metrik
        $sisa_qty = $total_qty_po - $total_diterima;
        $progress = ($total_qty_po > 0) ? round(($total_diterima / $total_qty_po) * 100, 2) : 0;

        if ($total_diterima == 0) {
            $status_code  = 'belum_mulai';
            $status_label = 'Belum Mulai';
            $status_badge = '<span class="badge bg-light-secondary text-secondary fw-semibold px-2 py-1"><i class="ti ti-clock me-1"></i>Belum Mulai</span>';
        } elseif ($total_diterima > 0 && $total_diterima < $total_qty_po) {
            $status_code  = 'partial';
            $status_label = 'Partial';
            $status_badge = '<span class="badge bg-light-warning text-warning fw-semibold px-2 py-1"><i class="ti ti-hourglass-empty me-1"></i>Partial</span>';
        } elseif ($total_diterima == $total_qty_po) {
            $status_code  = 'selesai';
            $status_label = 'Selesai';
            $status_badge = '<span class="badge bg-light-success text-success fw-semibold px-2 py-1"><i class="ti ti-check me-1"></i>Selesai</span>';
        } else {
            $status_code  = 'over_delivery';
            $status_label = 'Over-delivery';
            $status_badge = '<span class="badge bg-light-danger text-danger fw-semibold px-2 py-1"><i class="ti ti-alert-triangle me-1"></i>Over-delivery</span>';
        }

        return [
            'header'            => $header,
            'po_materials'      => $po_materials,
            'pr_summary'        => !empty($pr_list_arr) ? implode(', ', $pr_list_arr) : '-',
            'incoming_history'  => $incoming_history,
            'total_qty_po'      => $total_qty_po,
            'total_diterima'    => $total_diterima,
            'sisa_qty'          => $sisa_qty,
            'progress'          => $progress,
            'status_code'       => $status_code,
            'status_label'      => $status_label,
            'status_badge'      => $status_badge,
        ];
    }

    /**
     * Ambil daftar supplier untuk dropdown filter
     */
    public function get_suppliers()
    {
        $this->db->select("s.kode_supplier, s.nama");
        $this->db->from("new_supplier s");
        $this->db->order_by("s.nama", "ASC");
        return $this->db->get()->result();
    }
}
