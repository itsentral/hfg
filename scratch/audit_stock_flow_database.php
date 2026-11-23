<?php
mysqli_report(MYSQLI_REPORT_OFF);
define('BASEPATH', dirname(__DIR__) . '/system/');
define('ENVIRONMENT', 'development');
include dirname(__DIR__) . '/application/config/' . ENVIRONMENT . '/database.php';

$config = $db['default'];
$mysqli = @new mysqli(
    $config['hostname'],
    $config['username'],
    $config['password'],
    $config['database'],
    (int) ($config['port'] ?: 3306)
);
if ($mysqli->connect_errno) {
    fwrite(STDERR, 'DB_CONNECT_FAILED: ' . $mysqli->connect_error . PHP_EOL);
    exit(2);
}
$mysqli->set_charset('utf8');

$checks = [
    'warehouse_master' => "SELECT COUNT(*) n FROM warehouse WHERE (id=3 AND kd_gudang='PRT' AND status='Y') OR (id=4 AND kd_gudang='WIP' AND status='Y') OR (id=5 AND kd_gudang='HLD' AND status='Y')",
    'required_columns' => "SELECT COUNT(*) n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND ((TABLE_NAME='warehouse_stock_transaction_summary' AND COLUMN_NAME='production_report_id') OR (TABLE_NAME='warehouse_stock_transaction_detail' AND COLUMN_NAME IN ('id_summary','id_coil')) OR (TABLE_NAME='warehouse_history' AND COLUMN_NAME='id_summary') OR (TABLE_NAME='fg_stock_ledger' AND COLUMN_NAME='production_report_id'))",
    'precision_columns' => "SELECT COUNT(*) n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND ((TABLE_NAME='warehouse_stock' AND COLUMN_NAME IN ('begining','incoming','outgoing','qty_stock','qty_booking','qty_free','use_qty_free') AND NUMERIC_SCALE=3) OR (TABLE_NAME='warehouse_stock_per_day' AND COLUMN_NAME IN ('qty_stock','qty_booking','qty_rusak','qty_free') AND NUMERIC_SCALE=3))",
    'identity_mismatch_stock' => 'SELECT COUNT(*) n FROM warehouse_stock s LEFT JOIN warehouse w ON w.id=s.id_gudang WHERE w.id IS NULL OR w.kd_gudang<>s.kd_gudang',
    'identity_mismatch_coil' => 'SELECT COUNT(*) n FROM warehouse_stock_coil c LEFT JOIN warehouse w ON w.id=c.id_gudang WHERE w.id IS NULL OR w.kd_gudang<>c.kd_gudang',
    'duplicate_balance_identity' => 'SELECT COUNT(*) n FROM (SELECT 1 FROM warehouse_stock GROUP BY code_lv4,id_gudang,kd_gudang HAVING COUNT(*)>1) x',
    'negative_balance' => 'SELECT COUNT(*) n FROM warehouse_stock WHERE qty_stock<0 OR total_nilai<0 OR qty_free<0',
    'scan_without_summary' => "SELECT COUNT(*) n FROM (SELECT d.kode_trans,d.id_material,d.id_gudang,d.status_qc FROM warehouse_stock_transaction_detail d WHERE d.to_status='in_transit' AND d.status_qc IN ('IN','OUT') AND NOT EXISTS (SELECT 1 FROM warehouse_stock_transaction_summary s WHERE s.kode_trans=d.kode_trans AND s.id_material=d.id_material AND s.id_gudang=d.id_gudang AND ((d.status_qc='OUT' AND s.qty_akhir<s.qty_awal) OR (d.status_qc='IN' AND s.qty_akhir>s.qty_awal))) GROUP BY d.kode_trans,d.id_material,d.id_gudang,d.status_qc) x",
    'active_unpack_duplicate' => 'SELECT COUNT(*) n FROM (SELECT id_coil_baru FROM tr_unpack_coil_baby WHERE is_delete=0 GROUP BY id_coil_baru HAVING COUNT(*)>1) x',
    'orphan_book_balance' => 'SELECT COUNT(*) n FROM warehouse_stock s WHERE s.qty_stock<>0 AND NOT EXISTS (SELECT 1 FROM warehouse_stock_coil c WHERE c.status=1 AND c.id_material=s.code_lv4 AND c.id_gudang=s.id_gudang AND c.kd_gudang=s.kd_gudang)',
    'costbook_difference' => 'SELECT COUNT(*) n FROM warehouse_stock WHERE qty_stock>0 AND ABS(total_nilai-qty_stock*harga_beli)>ABS(qty_stock)*0.005+0.01',
    'duplicate_daily_snapshot' => 'SELECT COUNT(*) n FROM (SELECT 1 FROM warehouse_stock_per_day GROUP BY id_material,id_gudang,DATE(hist_date) HAVING COUNT(*)>1) x',
    'non_transactional_stock_tables' => "SELECT COUNT(*) n FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('warehouse_stock','warehouse_stock_per_day','warehouse_stock_coil','warehouse_pack','warehouse_stock_transaction_summary','warehouse_stock_transaction_detail','warehouse_history','warehouse_coil_kartu_stok','production_reports','fg_stock','fg_stock_ledger') AND ENGINE<>'InnoDB'",
    'warehouse_stock_unique_identity' => "SELECT COUNT(*) n FROM (SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='warehouse_stock' AND NON_UNIQUE=0 GROUP BY INDEX_NAME HAVING GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX)= 'code_lv4,id_gudang,kd_gudang') x",
];

foreach ($checks as $name => $sql) {
    $result = $mysqli->query($sql);
    if (!$result) {
        echo $name . '=ERROR:' . $mysqli->error . PHP_EOL;
        continue;
    }
    echo $name . '=' . $result->fetch_assoc()['n'] . PHP_EOL;
}
$mysqli->close();
