<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_report_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // SECTION 1: Pure Calculation Functions (no DB queries)
    // =========================================================================

    /**
     * Hitung Total_Berat_Coil dari 8 komponen
     * Total = reject_supplier + waste_potong + ng_internal + ng_supplier
     *       + plat_bs + fg_kg + kw2_internal_kg + kw2_supplier_kg
     *
     * @param array $data Associative array dengan key komponen berat
     * @return float
     */
    public function calculate_total_berat($data)
    {
        $keys = [
            'reject_supplier', 'waste_potong', 'ng_internal', 'ng_supplier',
            'plat_bs', 'fg_kg', 'kw2_internal_kg', 'kw2_supplier_kg',
        ];
        $total = 0.0;
        foreach ($keys as $k) {
            $total += (float) (isset($data[$k]) ? $data[$k] : 0);
        }
        return $total;
    }

    /**
     * Hitung Net_Hasil_Produksi
     * Net = Total_Berat_Coil + tong_coil + berat_cover_wrapping
     *
     * @param float $total_berat      Total_Berat_Coil
     * @param float $tong_coil        Berat tong coil
     * @param float $cover_wrapping   Berat cover wrapping (dari timbang awal)
     * @return float
     */
    public function calculate_net_hasil($total_berat, $tong_coil, $cover_wrapping)
    {
        return (float) $total_berat + (float) $tong_coil + (float) $cover_wrapping;
    }


    /**
     * Hitung yield per kategori dalam persen
     * Yield_X = X_kg / Total_Berat_Coil * 100
     *
     * @param array $data        Array dengan key fg_kg, kw2_internal_kg, kw2_supplier_kg,
     *                           reject_supplier, waste_potong, ng_internal, ng_supplier, plat_bs
     * @param float $total_berat Total_Berat_Coil
     * @return array Yield per kategori dalam persen
     */
    public function calculate_yield($data, $total_berat)
    {
        $total_berat = (float) $total_berat;
        if ($total_berat <= 0) {
            return [
                'yield_fg'           => 0,
                'yield_kw2_internal' => 0,
                'yield_kw2_supplier' => 0,
                'yield_reject'       => 0,
                'yield_waste'        => 0,
                'yield_ng_internal'  => 0,
                'yield_ng_supplier'  => 0,
                'yield_plat_bs'      => 0,
            ];
        }

        return [
            'yield_fg'           => round((float) (isset($data['fg_kg']) ? $data['fg_kg'] : 0) / $total_berat * 100, 4),
            'yield_kw2_internal' => round((float) (isset($data['kw2_internal_kg']) ? $data['kw2_internal_kg'] : 0) / $total_berat * 100, 4),
            'yield_kw2_supplier' => round((float) (isset($data['kw2_supplier_kg']) ? $data['kw2_supplier_kg'] : 0) / $total_berat * 100, 4),
            'yield_reject'       => round((float) (isset($data['reject_supplier']) ? $data['reject_supplier'] : 0) / $total_berat * 100, 4),
            'yield_waste'        => round((float) (isset($data['waste_potong']) ? $data['waste_potong'] : 0) / $total_berat * 100, 4),
            'yield_ng_internal'  => round((float) (isset($data['ng_internal']) ? $data['ng_internal'] : 0) / $total_berat * 100, 4),
            'yield_ng_supplier'  => round((float) (isset($data['ng_supplier']) ? $data['ng_supplier'] : 0) / $total_berat * 100, 4),
            'yield_plat_bs'      => round((float) (isset($data['plat_bs']) ? $data['plat_bs'] : 0) / $total_berat * 100, 4),
        ];
    }

    /**
     * Hitung berat satuan FG aktual
     * Berat_Satuan_FG = fg_kg / fg_qty
     *
     * @param float $fg_kg  Total berat FG dalam kg
     * @param float $fg_qty Jumlah qty FG
     * @return float Berat satuan, 0 jika qty = 0
     */
    public function calculate_berat_satuan_fg($fg_kg, $fg_qty)
    {
        $fg_qty = (float) $fg_qty;
        if ($fg_qty <= 0) {
            return 0.0;
        }
        return (float) $fg_kg / $fg_qty;
    }

    /**
     * Cek deviasi berat satuan FG terhadap berat standar
     * Deviasi = |berat_satuan_aktual - berat_standar| / berat_standar
     * Jika Deviasi > toleransi_deviasi_fg_pct → is_exception = true
     *
     * @param float $berat_satuan_aktual Berat satuan aktual (fg_kg / fg_qty)
     * @param float $berat_standar       Berat standar dari master produk
     * @return array ['deviasi_pct' => float, 'is_exception' => bool]
     */
    public function check_deviasi_fg($berat_satuan_aktual, $berat_standar)
    {
        $berat_satuan_aktual = (float) $berat_satuan_aktual;
        $berat_standar       = (float) $berat_standar;

        if ($berat_standar <= 0) {
            return ['deviasi_pct' => 0.0, 'is_exception' => false];
        }

        $deviasi_pct  = abs($berat_satuan_aktual - $berat_standar) / $berat_standar;
        $toleransi    = (float) get_param('toleransi_deviasi_fg_pct', 0.05);
        $is_exception = $deviasi_pct > $toleransi;

        return [
            'deviasi_pct'  => $deviasi_pct,
            'is_exception' => $is_exception,
        ];
    }


    // =========================================================================
    // SECTION 2: Numbering & Master Data Helpers
    // =========================================================================

    /**
     * Generate nomor laporan produksi: LP-YYYYMM-XXXX
     *
     * @return string
     */
    public function generate_report_no()
    {
        $prefix = 'LP-' . date('Ym') . '-';
        $row = $this->db->query(
            "SELECT MAX(report_no) AS max_no FROM tr_production_report WHERE report_no LIKE '{$prefix}%'"
        )->row();
        $last = ($row && $row->max_no) ? (int) substr($row->max_no, -4) : 0;
        return $prefix . sprintf('%04d', $last + 1);
    }

    /**
     * Ambil berat standar FG dari master produk
     * Coba dari new_inventory_4 atau ms_material, fallback 0
     *
     * @param mixed $id_produk_fg ID atau kode produk FG
     * @return float
     */
    public function get_berat_standar_fg($id_produk_fg)
    {
        if (empty($id_produk_fg)) {
            return 0.0;
        }

        // Coba dari new_inventory_4 berdasarkan code_lv4 (id produk FG)
        $row = $this->db->select('berat_standar')
            ->from('new_inventory_4')
            ->where('code_lv4', $id_produk_fg)
            ->get()->row();

        if ($row && isset($row->berat_standar) && $row->berat_standar > 0) {
            return (float) $row->berat_standar;
        }

        // Fallback: cari berdasarkan id numerik
        if (is_numeric($id_produk_fg)) {
            $row = $this->db->select('berat_standar')
                ->from('new_inventory_4')
                ->where('id', $id_produk_fg)
                ->get()->row();

            if ($row && isset($row->berat_standar) && $row->berat_standar > 0) {
                return (float) $row->berat_standar;
            }
        }

        return 0.0;
    }

    /**
     * Alias untuk get_berat_standar_fg — ambil berat standar dari new_inventory_4 berdasarkan code_lv4
     *
     * @param mixed $id_produk_fg code_lv4 atau id produk FG
     * @return float
     */
    public function get_berat_standar_produk($id_produk_fg)
    {
        return $this->get_berat_standar_fg($id_produk_fg);
    }

    /**
     * Ambil berat_cover_wrapping dari tr_coil_preweigh_component via join
     *
     * @param string $spk_no
     * @param string $no_coil
     * @return float
     */
    public function get_cover_wrapping_from_preweigh($spk_no, $no_coil)
    {
        $row = $this->db
            ->select('c.berat_cover_wrapping')
            ->from('tr_coil_preweigh p')
            ->join('tr_coil_preweigh_component c', 'c.preweigh_no = p.preweigh_no')
            ->where('p.spk_no', $spk_no)
            ->where('p.no_coil', $no_coil)
            ->order_by('p.created_at', 'DESC')
            ->limit(1)
            ->get()->row();

        return ($row && isset($row->berat_cover_wrapping)) ? (float) $row->berat_cover_wrapping : 0.0;
    }


    // =========================================================================
    // SECTION 3: Transaction Methods
    // =========================================================================

    /**
     * Simpan laporan produksi (header + result) dalam satu transaksi
     * Hitung semua kalkulasi otomatis
     *
     * @param array $data    Header: spk_no, no_coil, id_produk_fg, created_by
     * @param array $results Hasil produksi: reject_supplier, waste_potong, ng_internal, ng_supplier,
     *                       plat_bs, fg_kg, fg_qty, kw2_internal_kg, kw2_internal_qty,
     *                       kw2_supplier_kg, kw2_supplier_qty, tong_coil
     * @return array ['success' => bool, 'message' => string, 'report_no' => string|null, 'deviasi' => array|null]
     */
    public function save_report($data, $results)
    {
        $this->db->trans_start();

        $report_no = $this->generate_report_no();
        $now       = date('Y-m-d H:i:s');

        // Ambil berat_cover_wrapping dari timbang awal
        $berat_cover_wrapping = $this->get_cover_wrapping_from_preweigh(
            $data['spk_no'],
            $data['no_coil']
        );

        // Kalkulasi
        $total_berat       = $this->calculate_total_berat($results);
        $net_hasil         = $this->calculate_net_hasil($total_berat, (float) (isset($results['tong_coil']) ? $results['tong_coil'] : 0), $berat_cover_wrapping);
        $berat_satuan_fg   = $this->calculate_berat_satuan_fg(
            (float) (isset($results['fg_kg']) ? $results['fg_kg'] : 0),
            (float) (isset($results['fg_qty']) ? $results['fg_qty'] : 0)
        );

        // Insert header
        $this->db->insert('tr_production_report', [
            'report_no'            => $report_no,
            'spk_no'               => $data['spk_no'],
            'no_coil'              => $data['no_coil'],
            'berat_cover_wrapping' => $berat_cover_wrapping,
            'status'               => 'Draft',
            'override_fg'          => 0,
            'created_by'           => $data['created_by'],
            'created_at'           => $now,
        ]);

        // Insert result
        $this->db->insert('tr_production_report_result', [
            'report_no'          => $report_no,
            'reject_supplier'    => (float) (isset($results['reject_supplier']) ? $results['reject_supplier'] : 0),
            'waste_potong'       => (float) (isset($results['waste_potong']) ? $results['waste_potong'] : 0),
            'ng_internal'        => (float) (isset($results['ng_internal']) ? $results['ng_internal'] : 0),
            'ng_supplier'        => (float) (isset($results['ng_supplier']) ? $results['ng_supplier'] : 0),
            'plat_bs'            => (float) (isset($results['plat_bs']) ? $results['plat_bs'] : 0),
            'fg_kg'              => (float) (isset($results['fg_kg']) ? $results['fg_kg'] : 0),
            'fg_qty'             => (float) (isset($results['fg_qty']) ? $results['fg_qty'] : 0),
            'kw2_internal_kg'    => (float) (isset($results['kw2_internal_kg']) ? $results['kw2_internal_kg'] : 0),
            'kw2_internal_qty'   => (float) (isset($results['kw2_internal_qty']) ? $results['kw2_internal_qty'] : 0),
            'kw2_supplier_kg'    => (float) (isset($results['kw2_supplier_kg']) ? $results['kw2_supplier_kg'] : 0),
            'kw2_supplier_qty'   => (float) (isset($results['kw2_supplier_qty']) ? $results['kw2_supplier_qty'] : 0),
            'tong_coil'          => (float) (isset($results['tong_coil']) ? $results['tong_coil'] : 0),
            'total_berat_coil'   => $total_berat,
            'net_hasil_produksi' => $net_hasil,
            'berat_satuan_fg'    => $berat_satuan_fg,
        ]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal menyimpan laporan produksi', 'report_no' => null, 'deviasi' => null];
        }

        // Cek deviasi FG (setelah simpan, untuk info ke controller)
        $berat_standar = $this->get_berat_standar_fg(isset($data['id_produk_fg']) ? $data['id_produk_fg'] : null);
        $deviasi       = $this->check_deviasi_fg($berat_satuan_fg, $berat_standar);

        return [
            'success'   => true,
            'message'   => 'Laporan produksi berhasil disimpan',
            'report_no' => $report_no,
            'deviasi'   => $deviasi,
        ];
    }

    /**
     * Ambil satu laporan produksi beserta info user
     *
     * @param string $report_no
     * @return object|null
     */
    public function get_report($report_no)
    {
        return $this->db
            ->select('r.*, u.name AS nama_created_by, a.name AS nama_approved_by, s.nm_produk_fg, s.produk_fg')
            ->from('tr_production_report r')
            ->join('users u', 'u.id = r.created_by', 'left')
            ->join('users a', 'a.id = r.approved_by', 'left')
            ->join('tr_spk_production s', 's.spk_no = r.spk_no', 'left')
            ->where('r.report_no', $report_no)
            ->get()->row();
    }

    /**
     * Ambil hasil produksi untuk satu laporan
     *
     * @param string $report_no
     * @return object|null
     */
    public function get_report_result($report_no)
    {
        return $this->db->get_where('tr_production_report_result', ['report_no' => $report_no])->row();
    }


    /**
     * Submit laporan: ubah status ke Submitted, kirim notifikasi ke supervisor
     *
     * @param string $report_no
     * @param int    $user_id
     * @return array ['success' => bool, 'message' => string]
     */
    public function submit_report($report_no, $user_id)
    {
        $report = $this->get_report($report_no);
        if (!$report) {
            return ['success' => false, 'message' => 'Laporan tidak ditemukan'];
        }
        if ($report->status !== 'Draft') {
            return ['success' => false, 'message' => 'Laporan hanya bisa di-submit dari status Draft'];
        }

        $this->db->update('tr_production_report', [
            'status'     => 'Submitted',
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['report_no' => $report_no]);

        // Kirim notifikasi ke supervisor
        $this->_send_notification_approval($report_no, $report->spk_no);

        return ['success' => true, 'message' => 'Laporan berhasil di-submit'];
    }

    /**
     * Approve laporan: cek self-approval, ubah status ke Approved
     *
     * @param string $report_no
     * @param int    $approver_id
     * @return array ['success' => bool, 'message' => string]
     */
    public function approve_report($report_no, $approver_id)
    {
        $report = $this->get_report($report_no);
        if (!$report) {
            return ['success' => false, 'message' => 'Laporan tidak ditemukan'];
        }
        if ($report->status !== 'Submitted') {
            return ['success' => false, 'message' => 'Laporan hanya bisa di-approve dari status Submitted'];
        }

        // Cegah self-approval
        if ((int) $approver_id === (int) $report->created_by) {
            return ['success' => false, 'message' => 'Self-approval tidak diizinkan. Approver tidak boleh sama dengan pembuat laporan'];
        }

        $this->db->update('tr_production_report', [
            'status'      => 'Approved',
            'approved_by' => $approver_id,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ], ['report_no' => $report_no]);

        return ['success' => true, 'message' => 'Laporan berhasil di-approve'];
    }

    /**
     * Reject laporan: ubah status ke Rejected
     *
     * @param string $report_no
     * @param int    $approver_id
     * @param string $alasan
     * @return array ['success' => bool, 'message' => string]
     */
    public function reject_report($report_no, $approver_id, $alasan)
    {
        $report = $this->get_report($report_no);
        if (!$report) {
            return ['success' => false, 'message' => 'Laporan tidak ditemukan'];
        }
        if ($report->status !== 'Submitted') {
            return ['success' => false, 'message' => 'Laporan hanya bisa di-reject dari status Submitted'];
        }

        $this->db->update('tr_production_report', [
            'status'         => 'Rejected',
            'override_alasan' => $alasan,
            'approved_by'    => $approver_id,
            'updated_at'     => date('Y-m-d H:i:s'),
        ], ['report_no' => $report_no]);

        return ['success' => true, 'message' => 'Laporan berhasil di-reject'];
    }

    /**
     * Override deviasi berat FG: simpan konfirmasi override
     *
     * @param string $report_no
     * @param string $alasan
     * @param int    $user_id
     * @return array ['success' => bool, 'message' => string]
     */
    public function override_fg($report_no, $alasan, $user_id)
    {
        $report = $this->get_report($report_no);
        if (!$report) {
            return ['success' => false, 'message' => 'Laporan tidak ditemukan'];
        }

        $this->db->update('tr_production_report', [
            'override_fg'     => 1,
            'override_alasan' => $alasan,
            'updated_at'      => date('Y-m-d H:i:s'),
        ], ['report_no' => $report_no]);

        return ['success' => true, 'message' => 'Override deviasi berat FG berhasil dicatat'];
    }


    /**
     * Posting laporan produksi ke FG
     * Dalam satu transaksi:
     * 1. Ubah status laporan ke 'Posted to FG'
     * 2. Update SPK ke 'Submitted'
     * 3. Auto-create FG Receipt Draft di tr_fg_receipt
     * 4. Insert tr_supplier_perf_feed
     *
     * @param string $report_no
     * @param int    $user_id
     * @return array ['success' => bool, 'message' => string]
     */
    public function post_report($report_no, $user_id)
    {
        $report = $this->get_report($report_no);
        if (!$report) {
            return ['success' => false, 'message' => 'Laporan tidak ditemukan'];
        }
        if ($report->status !== 'Approved') {
            return ['success' => false, 'message' => 'Laporan hanya bisa diposting dari status Approved'];
        }

        $result = $this->get_report_result($report_no);
        $now    = date('Y-m-d H:i:s');
        $today  = date('Y-m-d');

        $this->db->trans_start();

        // 1. Update status laporan
        $this->db->update('tr_production_report', [
            'status'     => 'Posted to FG',
            'updated_at' => $now,
        ], ['report_no' => $report_no]);

        // 2. Update SPK ke Submitted
        $this->db->update('tr_spk_production', [
            'status'     => 'Submitted',
            'updated_at' => $now,
        ], ['spk_no' => $report->spk_no]);

        // 3. Auto-create FG Receipt Draft
        $fg_receipt_no = $this->_generate_fg_receipt_no();
        $this->db->insert('tr_fg_receipt', [
            'fg_receipt_no' => $fg_receipt_no,
            'report_no'     => $report_no,
            'spk_no'        => $report->spk_no,
            'no_coil'       => $report->no_coil,
            'produk_fg'     => $report->produk_fg,
            'fg_kg'         => $result ? (float) $result->fg_kg : 0,
            'fg_qty'        => $result ? (float) $result->fg_qty : 0,
            'kw2_internal_kg'  => $result ? (float) $result->kw2_internal_kg : 0,
            'kw2_internal_qty' => $result ? (float) $result->kw2_internal_qty : 0,
            'kw2_supplier_kg'  => $result ? (float) $result->kw2_supplier_kg : 0,
            'kw2_supplier_qty' => $result ? (float) $result->kw2_supplier_qty : 0,
            'status'        => 'Draft',
            'created_by'    => $user_id,
            'created_at'    => $now,
        ]);

        // 4. Insert supplier performance feed
        // Ambil data selisih dari timbang awal
        $preweigh = $this->db
            ->select('p.selisih_gross, p.selisih_net, s.id_supplier')
            ->from('tr_coil_preweigh p')
            ->join('tr_ros_detail rd', 'rd.no_coil = p.no_coil', 'left')
            ->join('tr_ros r', 'r.no_ros = rd.no_ros', 'left')
            ->join('supplier s', 's.id = r.id_supplier', 'left')
            ->where('p.spk_no', $report->spk_no)
            ->where('p.no_coil', $report->no_coil)
            ->order_by('p.created_at', 'DESC')
            ->limit(1)
            ->get()->row();

        $this->db->insert('tr_supplier_perf_feed', [
            'report_no'         => $report_no,
            'no_coil'           => $report->no_coil,
            'id_supplier'       => $preweigh ? $preweigh->id_supplier : null,
            'selisih_gross'     => $preweigh ? (float) $preweigh->selisih_gross : 0,
            'selisih_net'       => $preweigh ? (float) $preweigh->selisih_net : 0,
            'reject_supplier_kg' => $result ? (float) $result->reject_supplier : 0,
            'ng_supplier_kg'    => $result ? (float) $result->ng_supplier : 0,
            'kw2_supplier_kg'   => $result ? (float) $result->kw2_supplier_kg : 0,
            'tgl_feed'          => $today,
        ]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal memposting laporan produksi'];
        }

        // Cek apakah semua SPK terkait plan sudah Closed/Submitted → close plan (Requirements 1.6, 15.4)
        $spk = $this->db->get_where('tr_spk_production', ['spk_no' => $report->spk_no])->row();
        if ($spk && !empty($spk->plan_no)) {
            $this->load->model('Production_planning/Production_planning_model');
            $this->Production_planning_model->check_and_close_plan($spk->plan_no);
        }

        return ['success' => true, 'message' => 'Laporan berhasil diposting ke FG. FG Receipt Draft ' . $fg_receipt_no . ' telah dibuat'];
    }

    /**
     * Query DataTables server-side untuk list laporan produksi
     *
     * @param array $params $_REQUEST dari DataTables
     * @return array ['total' => int, 'filtered' => int, 'data' => array]
     */
    public function get_list_for_datatable($params)
    {
        $search    = isset($params['search']['value']) ? $params['search']['value'] : '';
        $start     = isset($params['start']) ? (int) $params['start'] : 0;
        $length    = isset($params['length']) ? (int) $params['length'] : 10;
        $order_col = isset($params['order'][0]['column']) ? (int) $params['order'][0]['column'] : 0;
        $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';

        $cols     = ['r.report_no', 'r.created_at', 'r.spk_no', 'r.no_coil', 'r.status'];
        $order_by = isset($cols[$order_col]) ? $cols[$order_col] : 'r.created_at';

        $base_sql = "FROM tr_production_report r
                     LEFT JOIN users u ON u.id = r.created_by
                     LEFT JOIN tr_spk_production s ON s.spk_no = r.spk_no
                     WHERE 1=1";

        if (!empty($search)) {
            $esc = $this->db->escape_like_str($search);
            $base_sql .= " AND (r.report_no LIKE '%{$esc}%' OR r.spk_no LIKE '%{$esc}%' OR r.no_coil LIKE '%{$esc}%' OR r.status LIKE '%{$esc}%')";
        }

        $total    = $this->db->query("SELECT COUNT(*) AS cnt {$base_sql}")->row()->cnt;
        $filtered = $total;
        $query    = $this->db->query(
            "SELECT r.*, u.name AS nama_created_by, s.nm_produk_fg {$base_sql} ORDER BY {$order_by} {$order_dir} LIMIT {$start},{$length}"
        );

        return ['total' => $total, 'filtered' => $filtered, 'data' => $query->result()];
    }


    // =========================================================================
    // SECTION 4: Private Helpers
    // =========================================================================

    /**
     * Generate nomor FG Receipt: FGR-YYYYMM-XXXX
     *
     * @return string
     */
    private function _generate_fg_receipt_no()
    {
        $prefix = 'FGR-' . date('Ym') . '-';
        $row = $this->db->query(
            "SELECT MAX(fg_receipt_no) AS max_no FROM tr_fg_receipt WHERE fg_receipt_no LIKE '{$prefix}%'"
        )->row();
        $last = ($row && $row->max_no) ? (int) substr($row->max_no, -4) : 0;
        return $prefix . sprintf('%04d', $last + 1);
    }

    /**
     * Kirim notifikasi approval ke supervisor/QC
     *
     * @param string $report_no
     * @param string $spk_no
     */
    private function _send_notification_approval($report_no, $spk_no)
    {
        $judul = 'Laporan Produksi Menunggu Approval: ' . $report_no;
        $pesan = 'Laporan produksi ' . $report_no . ' untuk SPK ' . $spk_no . ' telah di-submit dan menunggu approval.';

        $supervisor_ids_raw = get_param('supervisor_user_ids', '');
        $supervisor_ids     = array_filter(array_map('trim', explode(',', $supervisor_ids_raw)));

        if (!empty($supervisor_ids)) {
            foreach ($supervisor_ids as $uid) {
                if (is_numeric($uid)) {
                    send_notification((int) $uid, $judul, $pesan, $report_no, 'production_report');
                }
            }
        } else {
            // Fallback: notifikasi ke semua user aktif
            $users = $this->db->select('id')->from('users')->where('status', 1)->get()->result();
            foreach ($users as $u) {
                send_notification($u->id, $judul, $pesan, $report_no, 'production_report');
            }
        }
    }
}
