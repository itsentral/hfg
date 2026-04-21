<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Supplier_performance_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // SECTION 1: Feed per Coil per Supplier
    // =========================================================================

    /**
     * Query DataTables server-side untuk feed per coil per supplier
     * Join dengan supplier untuk nama supplier, join dengan tr_production_report untuk info laporan
     *
     * @param array $params $_REQUEST dari DataTables
     * @return array ['total' => int, 'filtered' => int, 'data' => array]
     */
    public function get_feed_datatable($params)
    {
        $search    = isset($params['search']['value']) ? trim($params['search']['value']) : '';
        $start     = isset($params['start']) ? (int) $params['start'] : 0;
        $length    = isset($params['length']) ? (int) $params['length'] : 10;
        $order_col = isset($params['order'][0]['column']) ? (int) $params['order'][0]['column'] : 0;
        $order_dir = (isset($params['order'][0]['dir']) && $params['order'][0]['dir'] === 'asc') ? 'ASC' : 'DESC';

        $id_supplier = isset($params['id_supplier']) ? $params['id_supplier'] : '';
        $tgl_dari    = isset($params['tgl_dari']) ? $params['tgl_dari'] : '';
        $tgl_sampai  = isset($params['tgl_sampai']) ? $params['tgl_sampai'] : '';

        $cols = [
            'f.tgl_feed', 'f.report_no', 'f.no_coil', 's.nm_supplier',
            'f.selisih_gross', 'f.selisih_net',
            'f.reject_supplier_kg', 'f.ng_supplier_kg', 'f.kw2_supplier_kg',
        ];
        $order_by = isset($cols[$order_col]) ? $cols[$order_col] : 'f.tgl_feed';

        $base_sql = "FROM tr_supplier_perf_feed f
                     LEFT JOIN supplier s ON s.id = f.id_supplier
                     LEFT JOIN tr_production_report r ON r.report_no = f.report_no
                     WHERE 1=1";

        if (!empty($id_supplier)) {
            $esc_sup = $this->db->escape($id_supplier);
            $base_sql .= " AND f.id_supplier = {$esc_sup}";
        }
        if (!empty($tgl_dari)) {
            $esc_dari = $this->db->escape($tgl_dari);
            $base_sql .= " AND f.tgl_feed >= {$esc_dari}";
        }
        if (!empty($tgl_sampai)) {
            $esc_sampai = $this->db->escape($tgl_sampai);
            $base_sql .= " AND f.tgl_feed <= {$esc_sampai}";
        }
        if (!empty($search)) {
            $esc = $this->db->escape_like_str($search);
            $base_sql .= " AND (f.report_no LIKE '%{$esc}%'
                            OR f.no_coil LIKE '%{$esc}%'
                            OR s.nm_supplier LIKE '%{$esc}%')";
        }

        $total    = (int) $this->db->query("SELECT COUNT(*) AS cnt {$base_sql}")->row()->cnt;
        $filtered = $total;

        $data = $this->db->query(
            "SELECT f.*, s.nm_supplier, r.spk_no
             {$base_sql}
             ORDER BY {$order_by} {$order_dir}
             LIMIT {$start}, {$length}"
        )->result();

        return ['total' => $total, 'filtered' => $filtered, 'data' => $data];
    }

    // =========================================================================
    // SECTION 2: Summary Agregasi per Supplier per Periode
    // =========================================================================

    /**
     * Agregasi per supplier per periode:
     * SUM reject_supplier_kg, SUM ng_supplier_kg, SUM kw2_supplier_kg,
     * AVG selisih_net, COUNT coil
     *
     * @param int|null    $id_supplier
     * @param string|null $tgl_dari
     * @param string|null $tgl_sampai
     * @return array
     */
    public function get_summary($id_supplier = null, $tgl_dari = null, $tgl_sampai = null)
    {
        $this->db->select([
            'f.id_supplier',
            's.nm_supplier',
            'SUM(f.reject_supplier_kg) AS total_reject_kg',
            'SUM(f.ng_supplier_kg) AS total_ng_kg',
            'SUM(f.kw2_supplier_kg) AS total_kw2_kg',
            'AVG(f.selisih_net) AS avg_selisih_net',
            'COUNT(f.no_coil) AS jumlah_coil',
            'SUM(f.reject_supplier_kg + f.ng_supplier_kg + f.kw2_supplier_kg) AS total_defect_kg',
        ]);
        $this->db->from('tr_supplier_perf_feed f');
        $this->db->join('supplier s', 's.id = f.id_supplier', 'left');

        if (!empty($id_supplier)) {
            $this->db->where('f.id_supplier', $id_supplier);
        }
        if (!empty($tgl_dari)) {
            $this->db->where('f.tgl_feed >=', $tgl_dari);
        }
        if (!empty($tgl_sampai)) {
            $this->db->where('f.tgl_feed <=', $tgl_sampai);
        }

        $this->db->group_by('f.id_supplier');
        $this->db->order_by('total_defect_kg', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Query DataTables server-side untuk summary (filter supplier + periode dari $params)
     *
     * @param array $params $_REQUEST dari DataTables
     * @return array ['total' => int, 'filtered' => int, 'data' => array]
     */
    public function get_summary_datatable($params)
    {
        $search    = isset($params['search']['value']) ? trim($params['search']['value']) : '';
        $start     = isset($params['start']) ? (int) $params['start'] : 0;
        $length    = isset($params['length']) ? (int) $params['length'] : 10;
        $order_col = isset($params['order'][0]['column']) ? (int) $params['order'][0]['column'] : 0;
        $order_dir = (isset($params['order'][0]['dir']) && $params['order'][0]['dir'] === 'asc') ? 'ASC' : 'DESC';

        $id_supplier = isset($params['id_supplier']) ? $params['id_supplier'] : '';
        $tgl_dari    = isset($params['tgl_dari']) ? $params['tgl_dari'] : '';
        $tgl_sampai  = isset($params['tgl_sampai']) ? $params['tgl_sampai'] : '';

        $cols = [
            's.nm_supplier', 'total_reject_kg', 'total_ng_kg',
            'total_kw2_kg', 'avg_selisih_net', 'jumlah_coil',
        ];
        $order_by = isset($cols[$order_col]) ? $cols[$order_col] : 'total_defect_kg';

        $where = "WHERE 1=1";
        if (!empty($id_supplier)) {
            $esc_sup = $this->db->escape($id_supplier);
            $where .= " AND f.id_supplier = {$esc_sup}";
        }
        if (!empty($tgl_dari)) {
            $esc_dari = $this->db->escape($tgl_dari);
            $where .= " AND f.tgl_feed >= {$esc_dari}";
        }
        if (!empty($tgl_sampai)) {
            $esc_sampai = $this->db->escape($tgl_sampai);
            $where .= " AND f.tgl_feed <= {$esc_sampai}";
        }
        if (!empty($search)) {
            $esc = $this->db->escape_like_str($search);
            $where .= " AND s.nm_supplier LIKE '%{$esc}%'";
        }

        $sub_sql = "FROM tr_supplier_perf_feed f
                    LEFT JOIN supplier s ON s.id = f.id_supplier
                    {$where}
                    GROUP BY f.id_supplier";

        $total_row = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM (SELECT f.id_supplier {$sub_sql}) AS sub"
        )->row();
        $total    = $total_row ? (int) $total_row->cnt : 0;
        $filtered = $total;

        $data = $this->db->query(
            "SELECT f.id_supplier, s.nm_supplier,
                    SUM(f.reject_supplier_kg) AS total_reject_kg,
                    SUM(f.ng_supplier_kg) AS total_ng_kg,
                    SUM(f.kw2_supplier_kg) AS total_kw2_kg,
                    AVG(f.selisih_net) AS avg_selisih_net,
                    COUNT(f.no_coil) AS jumlah_coil,
                    SUM(f.reject_supplier_kg + f.ng_supplier_kg + f.kw2_supplier_kg) AS total_defect_kg
             {$sub_sql}
             ORDER BY {$order_by} {$order_dir}
             LIMIT {$start}, {$length}"
        )->result();

        return ['total' => $total, 'filtered' => $filtered, 'data' => $data];
    }

    // =========================================================================
    // SECTION 3: Dashboard Data
    // =========================================================================

    /**
     * Data untuk chart dashboard: top 10 supplier berdasarkan total reject+NG+KW2
     * dengan breakdown per kategori
     *
     * @return array
     */
    public function get_dashboard_data()
    {
        $rows = $this->db->query(
            "SELECT f.id_supplier, s.nm_supplier,
                    SUM(f.reject_supplier_kg) AS total_reject_kg,
                    SUM(f.ng_supplier_kg) AS total_ng_kg,
                    SUM(f.kw2_supplier_kg) AS total_kw2_kg,
                    SUM(f.reject_supplier_kg + f.ng_supplier_kg + f.kw2_supplier_kg) AS total_defect_kg,
                    COUNT(f.no_coil) AS jumlah_coil,
                    AVG(f.selisih_net) AS avg_selisih_net
             FROM tr_supplier_perf_feed f
             LEFT JOIN supplier s ON s.id = f.id_supplier
             GROUP BY f.id_supplier
             ORDER BY total_defect_kg DESC
             LIMIT 10"
        )->result();

        $labels        = [];
        $reject_data   = [];
        $ng_data       = [];
        $kw2_data      = [];
        $table_data    = [];

        foreach ($rows as $row) {
            $labels[]      = $row->nm_supplier ?: ('Supplier #' . $row->id_supplier);
            $reject_data[] = (float) $row->total_reject_kg;
            $ng_data[]     = (float) $row->total_ng_kg;
            $kw2_data[]    = (float) $row->total_kw2_kg;
            $table_data[]  = $row;
        }

        return [
            'labels'      => $labels,
            'reject_data' => $reject_data,
            'ng_data'     => $ng_data,
            'kw2_data'    => $kw2_data,
            'table_data'  => $table_data,
        ];
    }

    // =========================================================================
    // SECTION 4: Master Data Helpers
    // =========================================================================

    /**
     * Ambil daftar supplier yang ada di feed untuk dropdown filter
     *
     * @return array
     */
    public function get_supplier_list()
    {
        return $this->db->query(
            "SELECT DISTINCT f.id_supplier, s.nm_supplier
             FROM tr_supplier_perf_feed f
             LEFT JOIN supplier s ON s.id = f.id_supplier
             ORDER BY s.nm_supplier ASC"
        )->result();
    }
}
