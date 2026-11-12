<?php
define('BASEPATH', 'TRUE');
define('ENVIRONMENT', 'development');

require __DIR__ . '/../application/config/database.php';
$db_cfg = $db['default'];

try {
    $mysqli = new mysqli('localhost', 'heru', '@Sentral123', 'db_hfg_dev');
} catch (Exception $e) {
    $mysqli = new mysqli($db_cfg['hostname'], $db_cfg['username'], $db_cfg['password'], $db_cfg['database']);
}

echo "=== TEST QUERY ON HOLD DI STOCK_FROM_WAREHOUSE ===\n";

$base_from = "
    FROM warehouse_stock_coil hld
    LEFT JOIN claim_material cm ON (
        cm.no_coil_packing_list = hld.no_coil
        OR cm.no_coil_packing_list = REPLACE(hld.no_coil, '-HOLD', '')
        OR cm.id_baby_coil = hld.id
        OR cm.id_baby_coil = hld.parent_coil_id
    )
    LEFT JOIN new_supplier s ON (s.id = cm.id_suplier OR s.kode_supplier = cm.id_suplier)
    LEFT JOIN tr_ros_header ros ON (ros.no_po = hld.no_po OR ros.no_po = cm.no_po)
    WHERE hld.id_gudang = 5
      AND hld.kd_gudang = 'HLD'
      AND hld.status = 1
";

// Test COUNT
$q_cnt = "SELECT COUNT(DISTINCT hld.id) as cnt {$base_from}";
$res_cnt = $mysqli->query($q_cnt);
if (!$res_cnt) {
    die("COUNT ERROR: " . $mysqli->error . "\n");
}
$cnt = $res_cnt->fetch_assoc()['cnt'];
echo "[OK] Count query sukses: {$cnt} baris ditemukan.\n";

// Test SELECT data
$sql = "
    SELECT
        hld.id,
        hld.id_material,
        hld.nm_material,
        hld.trade_name,
        hld.no_coil,
        hld.kode_internal,
        hld.gross_weight,
        hld.net_weight,
        hld.length,
        hld.kd_gudang,
        hld.no_po,
        hld.status_proses,
        COALESCE(s.nama, ros.nm_supplier, cm.id_suplier, '-') AS nama_supplier,
        cm.id AS id_claim,
        cm.no_claim,
        cm.problem,
        cm.alasan_produksi,
        cm.status_claim,
        (SELECT GROUP_CONCAT(cmd.aksi SEPARATOR ', ') FROM claim_material_decision cmd WHERE cmd.id_claim_material = cm.id) AS dec_aksis
    {$base_from}
    GROUP BY hld.id
    ORDER BY hld.nm_material ASC
    LIMIT 0, 10
";

$res = $mysqli->query($sql);
if (!$res) {
    die("SELECT ERROR: " . $mysqli->error . "\n");
}
echo "[OK] SELECT query sukses dijalankan 100% tanpa error!\n";
echo "=== SELESAI PENGUJIAN ===\n";

