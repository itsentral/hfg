<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Warehouse"
 */

class Warehouse_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Warehouse.Add');
        $this->ENABLE_MANAGE  = has_permission('Warehouse.Manage');
        $this->ENABLE_VIEW    = has_permission('Warehouse.View');
        $this->ENABLE_DELETE  = has_permission('Warehouse.Delete');
    }

    // ── LIST & GET ─────────────────────────────────────────────────────────

    public function GetListWarehouse()
    {
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->order_by('a.id', 'asc');
        $query = $this->db->get();
        return $query->num_rows() ? $query->result() : false;
    }

    public function GetDataWarehouse($id)
    {
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->where('a.id', $id);
        $query = $this->db->get();
        return $query->num_rows() ? $query->row() : false;
    }

    // ── STOCK PER COIL (index) — sumber: warehouse_stock_coil ─────────────
    // $kd_gudang: 'PUS' = Pusat, 'PEN' = Penjualan (sesuaikan dengan data)

    public function get_json_warehouse_stock($kd_gudang = '')
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start']  ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'asc';

        $col_map = [
            1 => 'ni.nama',
            2 => 'wsc.no_coil',
            3 => 'wsc.net_weight',
            4 => 'wsc.gross_weight',
            5 => 'wsc.length',
            6 => 'w.nm_gudang',
        ];
        $order_by = $col_map[$order_col] ?? 'ni.nama';

        // Build base WHERE
        $where_gudang = '';
        if (!empty($kd_gudang)) {
            $kd = $this->db->escape($kd_gudang);
            $where_gudang = " AND ws.kd_gudang = {$kd}";
        }

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (ni.nama LIKE '%{$s}%'
                               OR wsc.no_coil   LIKE '%{$s}%'
                               OR wsc.id_material LIKE '%{$s}%'
                               OR w.nm_gudang   LIKE '%{$s}%')";
        }

        $base_from = "
            FROM warehouse_stock_coil wsc
            JOIN warehouse_stock ws
                ON CONVERT(ws.code_lv4 USING utf8mb4) = CONVERT(wsc.id_material USING utf8mb4)
            JOIN warehouse w
                ON w.id = ws.id_gudang
            LEFT JOIN new_inventory_4 ni
                ON CONVERT(ni.code_lv4 USING utf8mb4) = CONVERT(wsc.id_material USING utf8mb4)
            WHERE 1=1
            {$where_gudang}
            {$where_search}
        ";

        // Hitung total & filtered — pakai raw query agar tidak konflik CI builder
        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_from}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        // Data utama
        $sql = "
            SELECT
                wsc.id,
                wsc.id_material,
                wsc.no_coil,
                wsc.kode_internal,
                wsc.gross_weight,
                wsc.net_weight,
                wsc.length,
                ws.kd_gudang,
                ws.id_gudang,
                ws.harga_beli,
                ni.nama AS nm_barang,
                ni.trade_name,
                w.nm_gudang
            {$base_from}
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['nm_barang'] . '<br><small class="text-muted">' . $row['id_material'] . '</small>',
                "<div class='text-center'>" . $row['no_coil'] . "</div>",
                "<div class='text-center'>1</div>",
                "<div class='text-right'>" . number_format((float) $row['net_weight'],   3, ',', '.') . "</div>",
                "<div class='text-right'>" . number_format((float) $row['gross_weight'], 3, ',', '.') . "</div>",
                "<div class='text-right'>" . number_format((float) $row['length'],       3, ',', '.') . "</div>",
                "<div class='text-center'>" . ($row['nm_gudang'] ?? $row['kd_gudang']) . "</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // ── STOCK VALUE PER MATERIAL — sumber: warehouse_stock ────────────────

    public function get_json_stock_value($kd_gudang = '')
    {
        $requestData     = $_REQUEST;
        $id_gudang       = $_POST['id_gudang']       ?? '';
        $filter_material = $_POST['filter_material'] ?? '';

        $from = $this->_from_stock_value($kd_gudang, $id_gudang, $filter_material);

        // Total & filtered (sama karena filter sudah masuk ke FROM)
        $cnt_row   = $this->db->query("SELECT COUNT(DISTINCT ws.code_lv4, ws.id_gudang) AS cnt {$from}")->row();
        $totalData = $cnt_row ? (int) $cnt_row->cnt : 0;

        // Order
        $col_order = [
            1 => 'ws.code_lv4',
            2 => 'ws.nm_material',
            3 => 'w.nm_gudang',
            4 => 'jumlah_coil',
            5 => 'ws.qty_stock',
            6 => 'ws.harga_beli',
            7 => 'ws.total_nilai',
        ];
        $order_col = (int) ($requestData['order'][0]['column'] ?? 2);
        $order_dir = in_array(strtolower($requestData['order'][0]['dir'] ?? ''), ['asc','desc'])
            ? $requestData['order'][0]['dir'] : 'asc';
        $order_by  = $col_order[$order_col] ?? 'ws.nm_material';

        $start  = (int) ($requestData['start']  ?? 0);
        $length = (int) ($requestData['length'] ?? 25);
        $limit  = ($length != -1) ? "LIMIT {$start}, {$length}" : '';

        $sql = "
            SELECT
                ws.code_lv4    AS id_material,
                ws.nm_material,
                ws.id_gudang,
                ws.kd_gudang,
                ws.qty_stock,
                ws.qty_booking,
                ws.qty_free,
                ws.harga_beli,
                ws.total_nilai,
                w.nm_gudang,
                COUNT(wsc.id)  AS jumlah_coil
            {$from}
            GROUP BY ws.code_lv4, ws.id_gudang
            ORDER BY {$order_by} {$order_dir}
            {$limit}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $btn_history = "<button class='btn btn-xs btn-info'
                onclick=\"showHistory('{$row['id_material']}','{$row['nm_material']}','{$row['id_gudang']}')\">
                <i class='fa fa-history'></i> History
            </button>";

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id_material'],
                $row['nm_material'],
                ($row['nm_gudang'] ?? '-') . ' <span class="text-muted">(' . $row['kd_gudang'] . ')</span>',
                // "<div class='text-center'>" . (int) $row['jumlah_coil'] . "</div>",
                "<div class='text-right'>" . number_format((float) $row['qty_stock'],        3, ',', '.') . "</div>",
                "<div class='text-right'>" . number_format((int) round($row['harga_beli']),  0, ',', '.') . "</div>",
                "<div class='text-right'>" . number_format((int) round($row['total_nilai']), 0, ',', '.') . "</div>",
                "<div class='text-center'>{$btn_history}</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // Bangun klausa WHERE untuk stock value (raw, aman dari collation issue)
    private function _where_stock_value($kd_gudang = '', $id_gudang = '', $filter_material = '')
    {
        $where = " WHERE ws.qty_stock > 0 ";

        if (!empty($kd_gudang)) {
            $kd    = $this->db->escape($kd_gudang);
            $where .= " AND ws.kd_gudang = {$kd} ";
        }
        if (!empty($id_gudang)) {
            $where .= " AND ws.id_gudang = " . (int) $id_gudang . " ";
        }
        if (!empty($filter_material)) {
            $f     = $this->db->escape_like_str($filter_material);
            $where .= " AND (ws.nm_material LIKE '%{$f}%' OR ws.code_lv4 LIKE '%{$f}%') ";
        }
        if (!empty($_POST['search']['value'])) {
            $s     = $this->db->escape_like_str($_POST['search']['value']);
            $where .= " AND (ws.nm_material LIKE '%{$s}%'
                          OR ws.code_lv4    LIKE '%{$s}%'
                          OR w.nm_gudang    LIKE '%{$s}%') ";
        }

        return $where;
    }

    // FROM + JOIN bersama (raw SQL — CONVERT menghindari collation mismatch)
    private function _from_stock_value($kd_gudang = '', $id_gudang = '', $filter_material = '')
    {
        $where = $this->_where_stock_value($kd_gudang, $id_gudang, $filter_material);

        return "
            FROM warehouse_stock ws
            LEFT JOIN warehouse w ON w.id = ws.id_gudang
            LEFT JOIN warehouse_stock_coil wsc
                ON CONVERT(wsc.id_material USING utf8mb4)
                 = CONVERT(ws.code_lv4    USING utf8mb4)
            {$where}
        ";
    }

    public function get_grand_total_stock_value($kd_gudang = '', $id_gudang = '', $filter_material = '')
    {
        $from = $this->_from_stock_value($kd_gudang, $id_gudang, $filter_material);
        $row  = $this->db->query("SELECT SUM(ws.total_nilai) AS grand_total {$from}")->row();
        return $row ? (int) round($row->grand_total) : 0;
    }

    // ── KARTU STOK ────────────────────────────────────────────────────────

    public function get_json_kartu_stok()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_kartu_stok(
            $requestData['search']['value']      ?? '',
            $requestData['order'][0]['column']   ?? null,
            $requestData['order'][0]['dir']      ?? null,
            $requestData['start']                ?? 0,
            $requestData['length']               ?? 10
        );

        $data  = [];
        $urut1 = 1;

        foreach ($fetch['query']->result_array() as $row) {
            $nomor = $urut1 + $requestData['start'];

            $data[] = [
                "<div align='center'>{$nomor}</div>",
                date('d/M/Y', strtotime($row['tgl_transaksi'])),
                $row['no_transaksi'],
                $row['transaksi'],
                $row['code_lv4'],
                $row['nm_material'],
                number_format($row['qty']),
                number_format($row['qty_book']),
                number_format($row['qty_free']),
                number_format($row['qty_transaksi']),
                number_format($row['qty_book_akhir']),
                number_format($row['qty_akhir']),
                number_format($row['qty_book_akhir']),
                number_format($row['qty_free_akhir']),
            ];
            $urut1++;
        }

        echo json_encode([
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => intval($fetch['totalData']),
            'recordsFiltered' => intval($fetch['totalFiltered']),
            'data'            => $data,
        ]);
    }

    public function get_query_json_kartu_stok($like_value = null, $column_order = null, $column_dir = null, $limit_start = 0, $limit_length = 10)
    {
        $columns_order_by = [
            1  => 'ks.tgl_transaksi',
            2  => 'ks.no_transaksi',
            3  => 'ks.transaksi',
            4  => 'ks.code_lv4',
            5  => 'ks.nm_material',
            6  => 'ks.qty',
            7  => 'ks.qty_book',
            8  => 'ks.qty_free',
            9  => 'ks.qty_transaksi',
            10 => 'ks.qty_book_akhir',
            11 => 'ks.qty_akhir',
            12 => 'ks.qty_book_akhir',
            13 => 'ks.qty_free_akhir',
        ];

        // Total semua (tanpa search)
        $this->db->from('kartu_stok ks');
        $this->db->where('ks.deleted', null);
        $totalData = $this->db->count_all_results();

        // Total filtered (dengan search)
        $this->db->from('kartu_stok ks');
        $this->db->where('ks.deleted', null);
        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('ks.code_lv4',    $like_value);
            $this->db->or_like('ks.nm_material',  $like_value);
            $this->db->or_like('ks.no_transaksi', $like_value);
            $this->db->or_like('ks.transaksi',    $like_value);
            $this->db->group_end();
        }
        $totalFiltered = $this->db->count_all_results();

        // Data
        $this->db->select('ks.*');
        $this->db->from('kartu_stok ks');
        $this->db->where('ks.deleted', null);
        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('ks.code_lv4',    $like_value);
            $this->db->or_like('ks.nm_material',  $like_value);
            $this->db->or_like('ks.no_transaksi', $like_value);
            $this->db->or_like('ks.transaksi',    $like_value);
            $this->db->group_end();
        }

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('ks.tgl_transaksi', 'desc');
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        return [
            'totalData'     => $totalData,
            'totalFiltered' => $totalFiltered,
            'query'         => $this->db->get(),
        ];
    }
}