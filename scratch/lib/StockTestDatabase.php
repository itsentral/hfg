<?php
/** SQLite memory-only CI3 adapter. It never reads application database configuration. */
class StockTestResult
{
    private $rows;
    public function __construct(array $rows) { $this->rows = $rows; }
    public function row_array() { return $this->rows[0] ?? []; }
    public function result_array() { return $this->rows; }
    public function row() { return isset($this->rows[0]) ? (object)$this->rows[0] : null; }
    public function result() { return array_map(function ($r) { return (object)$r; }, $this->rows); }
}

class StockTestDatabase
{
    public $pdo;
    private $where = [];
    private $params = [];
    private $selection = '*';
    private $from_table;
    private $ordering = '';
    private $limit_sql = '';
    private $failed = false;
    public function __construct()
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $schemas = [
            'warehouse' => 'id INTEGER PRIMARY KEY, kd_gudang TEXT, nm_gudang TEXT, status TEXT',
            'warehouse_stock' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, code_lv4 TEXT, code_incoming TEXT, nm_material TEXT, trade_name TEXT, id_gudang INTEGER, kd_gudang TEXT, begining NUMERIC DEFAULT 0, incoming NUMERIC DEFAULT 0, outgoing NUMERIC DEFAULT 0, qty_stock NUMERIC DEFAULT 0, qty_booking NUMERIC DEFAULT 0, qty_free NUMERIC DEFAULT 0, use_qty_free NUMERIC DEFAULT 0, harga_beli NUMERIC, total_nilai NUMERIC, update_by INTEGER, update_date TEXT',
            'warehouse_stock_coil' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, id_material TEXT, nm_material TEXT, trade_name TEXT, id_gudang INTEGER, kd_gudang TEXT, no_coil TEXT, kode_internal TEXT, id_pack INTEGER, parent_coil_id INTEGER, is_baby_coil INTEGER DEFAULT 0, no_ipp TEXT, no_po TEXT, no_ros TEXT, net_weight NUMERIC, gross_weight NUMERIC, kulit NUMERIC, clamp_ring NUMERIC, length NUMERIC, qty_roll INTEGER, qty NUMERIC, harga_beli NUMERIC, total_nilai NUMERIC, status INTEGER, status_proses TEXT, created_by INTEGER, created_on TEXT',
            'warehouse_stock_transaction_summary' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, kode_trans TEXT, id_pack INTEGER, pack_code TEXT, id_material TEXT, nm_material TEXT, id_gudang INTEGER, kd_gudang TEXT, tanggal TEXT, jumlah_coil INTEGER, qty_awal NUMERIC, qty_transaksi NUMERIC, qty_akhir NUMERIC, costbook NUMERIC, total_harga NUMERIC, saldo_awal NUMERIC, saldo_akhir NUMERIC, harga_lama NUMERIC, created_by INTEGER, created_at TEXT, production_report_id INTEGER',
            'warehouse_stock_transaction_detail' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, id_summary INTEGER, id_coil INTEGER, kode_trans TEXT, id_pack INTEGER, pack_code TEXT, id_material TEXT, nm_material TEXT, id_gudang INTEGER, kd_gudang TEXT, no_coil TEXT, parent_coil_id INTEGER, parent_no_coil TEXT, kode_internal TEXT, gross_weight NUMERIC, net_weight NUMERIC, length NUMERIC, price_per_coil NUMERIC, cost_book NUMERIC, status_qc TEXT, to_status TEXT, created_at TEXT',
            'warehouse_history' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, id_summary INTEGER, id_material TEXT, nm_material TEXT, no_coil TEXT, id_gudang INTEGER, kd_gudang TEXT, id_gudang_dari INTEGER, kd_gudang_dari TEXT, id_gudang_ke INTEGER, kd_gudang_ke TEXT, qty_stock_awal NUMERIC, qty_stock_akhir NUMERIC, no_ipp TEXT, jumlah_mat NUMERIC, ket TEXT, harga_beli NUMERIC, total_harga NUMERIC, saldo_awal NUMERIC, saldo_akhir NUMERIC, harga_baru NUMERIC, harga_lama NUMERIC, update_by INTEGER, update_date TEXT',
            'warehouse_stock_per_day' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, id_material TEXT, nm_material TEXT, id_gudang INTEGER, kd_gudang TEXT, qty_stock NUMERIC, qty_booking NUMERIC, qty_free NUMERIC, harga_beli NUMERIC, total_nilai NUMERIC, hist_date TEXT, hist_by INTEGER',
            'warehouse_coil_per_day' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, id_material TEXT, nm_material TEXT, id_gudang INTEGER, kd_gudang TEXT, no_coil TEXT, kode_internal TEXT, id_pack INTEGER, parent_coil_id INTEGER, is_baby_coil INTEGER, gross_weight NUMERIC, net_weight NUMERIC, length NUMERIC, qty_roll INTEGER, harga_beli NUMERIC, total_nilai NUMERIC, status TEXT, hist_date TEXT, hist_by INTEGER',
            'warehouse_coil_kartu_stok' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, id_coil INTEGER, no_trs TEXT, id_material TEXT, nm_material TEXT, no_coil TEXT, kode_internal TEXT, status TEXT, dari TEXT, ke TEXT, qty_trans NUMERIC, qty_awal NUMERIC, qty_akhir NUMERIC, production_report_id INTEGER, keterangan TEXT, created_by INTEGER, created_at TEXT',
            'warehouse_pack' => 'id INTEGER PRIMARY KEY, pack_code TEXT, id_gudang INTEGER, kd_gudang TEXT, status INTEGER',
            'fg_stock' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, produk_fg TEXT UNIQUE, nm_produk_fg TEXT, kategori_stok TEXT, sub_kategori TEXT, id_material TEXT, qty_stok NUMERIC, total_berat NUMERIC, total_nilai NUMERIC, costbook NUMERIC, berat_referensi NUMERIC, last_update TEXT',
            'fg_stock_ledger' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, production_report_id INTEGER, produk_fg TEXT, tgl_transaksi TEXT, no_referensi TEXT, spk_no TEXT, category_type TEXT, kategori_stok TEXT, sub_kategori TEXT, id_material TEXT, jenis_mutasi TEXT, qty_in NUMERIC, qty_out NUMERIC, berat_in NUMERIC, berat_out NUMERIC, qty_saldo NUMERIC, berat_saldo NUMERIC, costbook NUMERIC, total_nilai NUMERIC, saldo_nilai NUMERIC, keterangan TEXT, created_by INTEGER, created_at TEXT',
            'claim_material' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, id_baby_coil INTEGER, id_gudang_material_hold INTEGER, no_coil_packing_list TEXT, status_claim TEXT, production_report_id INTEGER',
            'tr_unpack_coil_baby' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, id_coil_baru INTEGER, net_weight_pl NUMERIC, gross_weight_pl NUMERIC, is_delete INTEGER DEFAULT 0',
            'production_reports' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, id_tr_spk_detail INTEGER, status_draft INTEGER, tgl_produksi TEXT, created_at TEXT',
            'tr_spk_material_header' => 'spk_no TEXT PRIMARY KEY, status TEXT, created_at TEXT',
            'tr_spk_material_detail' => 'id INTEGER PRIMARY KEY, spk_no TEXT',
            'tr_spk_scan_log' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, spk_no TEXT, no_coil TEXT, scan_time TEXT, scan_user INTEGER, status_scan TEXT, keterangan TEXT',
            'production_report_materials' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, production_report_id INTEGER',
            'production_report_items' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, production_report_id INTEGER',
            'production_report_scraps' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, production_report_id INTEGER',
            'rate_generals' => 'id INTEGER PRIMARY KEY, rate_consumables NUMERIC, rate_biaya_koordinasi NUMERIC, rate_scrap NUMERIC',
            'ms_jurnal_mapping' => 'id INTEGER PRIMARY KEY, menu TEXT, action TEXT, kode_master_jurnal TEXT',
            'product_lvl_4' => 'id INTEGER PRIMARY KEY, code_lv4 TEXT, nama TEXT, weight NUMERIC',
        ];
        foreach ($schemas as $table => $cols) $this->pdo->exec('CREATE TABLE ' . $table . ' (' . $cols . ')');
        foreach ([1 => 'PRO', 2 => 'SLI', 3 => 'PRT', 4 => 'WIP', 5 => 'HLD'] as $id => $code) {
            $this->insert('warehouse', ['id' => $id, 'kd_gudang' => $code, 'nm_gudang' => $code, 'status' => 'Y']);
        }
    }
    public function query($sql, $args = [])
    {
        if (strpos($sql,'information_schema.COLUMNS') !== false) {
            return new StockTestResult([['numeric_scale'=>3]]);
        }
        $sql = preg_replace('/\s+FOR UPDATE\b/i', '', $sql);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return new StockTestResult($stmt->columnCount() ? $stmt->fetchAll(PDO::FETCH_ASSOC) : []);
    }
    public function where($key, $value) { $this->where[] = $key . ' = ?'; $this->params[] = $value; return $this; }
    public function or_where($key, $value) { $this->where[] = 'OR ' . $key . ' = ?'; $this->params[] = $value; return $this; }
    public function group_start() { $this->where[] = '('; return $this; }
    public function group_end() { $this->where[] = ')'; return $this; }
    public function where_in($key, $values) { $this->where[] = $key . ' IN (' . implode(',', array_fill(0, count($values), '?')) . ')'; $this->params = array_merge($this->params, $values); return $this; }
    public function or_where_in($key, $values) { $this->where[] = 'OR ' . $key . ' IN (' . implode(',', array_fill(0, count($values), '?')) . ')'; $this->params = array_merge($this->params, $values); return $this; }
    public function select($fields, $escape = true) { $this->selection = $fields; return $this; }
    public function from($table) { $this->from_table = $table; return $this; }
    public function order_by($key, $dir) { $this->ordering = ' ORDER BY ' . $key . ' ' . $dir; return $this; }
    public function limit($count) { $this->limit_sql = ' LIMIT ' . (int)$count; return $this; }
    private function predicate() {
        $sql = ''; $separator = false;
        foreach ($this->where as $part) {
            if ($part === ')') { $sql .= ')'; $separator = true; continue; }
            $or = strpos($part,'OR ') === 0;
            if ($separator) $sql .= $or ? ' OR ' : ' AND ';
            $sql .= $or ? substr($part,3) : $part;
            $separator = $part !== '(';
        }
        return $sql === '' ? '' : ' WHERE ' . $sql;
    }
    private function reset() { $this->where = []; $this->params = []; $this->selection = '*'; $this->from_table = null; $this->ordering = ''; $this->limit_sql = ''; }
    public function get($table = null) { $sql = 'SELECT ' . $this->selection . ' FROM ' . ($table ?: $this->from_table) . $this->predicate() . $this->ordering . $this->limit_sql; $params = $this->params; $this->reset(); return $this->query($sql, $params); }
    public function get_where($table, $where) { foreach ($where as $key => $value) $this->where($key, $value); return $this->get($table); }
    public function update($table, $data) { $sql = 'UPDATE ' . $table . ' SET ' . implode(',', array_map(function ($k) { return $k . ' = ?'; }, array_keys($data))) . $this->predicate(); $params = array_merge(array_values($data), $this->params); $this->reset(); $this->query($sql, $params); return true; }
    public function delete($table) { $sql = 'DELETE FROM ' . $table . $this->predicate(); $params = $this->params; $this->reset(); $this->query($sql, $params); return true; }
    public function insert($table, $data) { $this->query('INSERT INTO ' . $table . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ')', array_values($data)); return true; }
    public function insert_batch($table, $rows) {
        // Derived report fields are not the subject of this stock test. All stock/ledger schemas above stay strict.
        if (in_array($table,['production_report_materials','production_report_items','production_report_scraps'],true)) {
            $fields=$this->list_fields($table);
            foreach ($rows as $row) foreach (array_keys($row) as $key) if (!in_array($key,$fields,true)) {
                $this->pdo->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $key);
                $fields[]=$key;
            }
        }
        foreach ($rows as $row) $this->insert($table, $row); return true;
    }
    public function insert_id() { return (int)$this->pdo->lastInsertId(); }
    public function count_all_results($table) { return count($this->get($table)->result_array()); }
    public function list_fields($table) { return array_column($this->query('PRAGMA table_info(' . $table . ')')->result_array(), 'name'); }
    public function field_exists($field, $table) { return in_array($field, $this->list_fields($table), true); }
    public function table_exists($table) { return (bool)$this->query("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$table])->row_array(); }
    public function trans_start() { $this->failed = false; $this->pdo->beginTransaction(); }
    public function trans_begin() { $this->trans_start(); }
    public function trans_complete() { if ($this->pdo->inTransaction()) $this->pdo->commit(); }
    public function trans_commit() { $this->trans_complete(); }
    public function trans_rollback() { $this->failed = true; if ($this->pdo->inTransaction()) $this->pdo->rollBack(); }
    public function trans_status() { return !$this->failed; }
    public function rows($table) { return $this->query('SELECT * FROM ' . $table . ' ORDER BY rowid')->result_array(); }
    public function escape_like_str($value) { return str_replace(['!','%','_'],['!!','!%','!_'],$value); }
    public function escape($value) { return $this->pdo->quote((string)$value); }
    public function __get($name) {
        if ($name !== 'tables') throw new RuntimeException('Unknown property: ' . $name);
        $result = [];
        foreach ($this->query("SELECT name FROM sqlite_master WHERE type='table' AND name <> 'sqlite_sequence' ORDER BY name")->result_array() as $table) {
            $result[$table['name']] = $this->rows($table['name']);
        }
        return $result;
    }
}

class StockTestLoader { public function helper($names) {} }
