<?php
define('BASEPATH', 'TRUE');
define('ENVIRONMENT', 'development');

// Bootstrap CI database connection and test model query
require __DIR__ . '/../application/config/database.php';
$db_cfg = $db['default'];

try {
    $mysqli = new mysqli('localhost', 'heru', '@Sentral123', 'db_hfg_dev');
} catch (Exception $e) {
    $mysqli = new mysqli($db_cfg['hostname'], $db_cfg['username'], $db_cfg['password'], $db_cfg['database']);
}

// Test exact query from line 188
$sql = "SELECT DISTINCT cm.id_suplier, COALESCE(s.nama, ros.nm_supplier, cm.id_suplier) AS nama_supplier FROM claim_material cm LEFT JOIN new_supplier s ON s.id = cm.id_suplier OR s.kode_supplier = cm.id_suplier LEFT JOIN tr_ros_header ros ON ros.no_po = cm.no_po ORDER BY nama_supplier ASC";

$res = $mysqli->query($sql);
if (!$res) {
    echo "SQL ERROR: " . $mysqli->error . "\n";
} else {
    echo "SUCCESS: Query executed successfully! Rows: " . $res->num_rows . "\n";
}

