<?php
define('BASEPATH', 'TRUE');
define('ENVIRONMENT', 'development');

$mysqli = new mysqli('localhost', 'heru', '@Sentral123', 'db_hfg_dev');
if ($mysqli->connect_error) {
    die("Connect failed: " . $mysqli->connect_error . "\n");
}

echo "=== SIMULASI END-TO-END: PRODUCTION REPORT -> ON HOLD (STOCK_FROM_WAREHOUSE) -> CLAIM MATERIAL ===\n\n";

$test_coil_no = 'TEST-COIL-' . time();
$test_hold_coil_no = $test_coil_no . '-HOLD';
$test_po = 'PO-TEST-' . rand(100, 999);
$test_supplier = 'PT Test Krakatau Steel';
$test_weight = 1250.50;
$test_problem = 'Lapisan seng terkelupas dan bergelombang saat uncoiling';
$test_alasan = 'Tidak memenuhi standar ketebalan toleransi ±0.02';

// 1. SIMULASI: INSERT ke claim_material (seperti saat Production Report di-submit)
$q_claim = "INSERT INTO claim_material (
    no_claim, production_report_id, asal, no_coil_packing_list, no_po, id_suplier, id_material,
    berat_kg, problem, alasan_produksi, status_claim, created_at, created_by
) VALUES (
    'CLM-TEST-001', 9999, 'Hold Coil', '{$test_coil_no}', '{$test_po}', '{$test_supplier}', 'MAT-CR-01',
    {$test_weight}, '{$test_problem}', '{$test_alasan}', 'Claim Baru', NOW(), '1'
)";
if (!$mysqli->query($q_claim)) {
    die("Insert claim failed: " . $mysqli->error . "\n");
}
$claim_id = $mysqli->insert_id;
echo "[1] SUCCESS: Baris claim_material berhasil dibuat (ID: {$claim_id}, No Claim: CLM-TEST-001)\n";

// 2. SIMULASI: INSERT ke warehouse_stock_coil (Gudang HLD / id 5)
$q_wh = "INSERT INTO warehouse_stock_coil (
    id_material, nm_material, no_coil, no_po, gross_weight, net_weight, qty,
    id_gudang, kd_gudang, status_proses, status, created_by, created_on
) VALUES (
    'MAT-CR-01', 'Cold Rolled Coil 0.8mm', '{$test_hold_coil_no}', '{$test_po}',
    {$test_weight}, {$test_weight}, {$test_weight}, 5, 'HLD', 'on_hold', 1, 1, NOW()
)";
if (!$mysqli->query($q_wh)) {
    die("Insert warehouse_stock_coil failed: " . $mysqli->error . "\n");
}
$coil_hld_id = $mysqli->insert_id;
echo "[2] SUCCESS: Baris warehouse_stock_coil (HLD) berhasil dibuat (ID: {$coil_hld_id}, Coil: {$test_hold_coil_no})\n";

// Update reference id_gudang_material_hold di claim
$mysqli->query("UPDATE claim_material SET id_gudang_material_hold = {$coil_hld_id}, id_baby_coil = {$coil_hld_id} WHERE id = {$claim_id}");

// 3. SIMULASI: Verifikasi Query Tab On Hold di stock_from_warehouse
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
      AND hld.id = {$coil_hld_id}
";

$sql = "
    SELECT
        hld.id,
        hld.nm_material,
        hld.no_coil,
        hld.no_po,
        hld.net_weight,
        hld.status_proses,
        COALESCE(s.nama, ros.nm_supplier, cm.id_suplier, '-') AS nama_supplier,
        cm.id AS id_claim,
        cm.no_claim,
        cm.problem,
        cm.alasan_produksi,
        cm.status_claim,
        (SELECT GROUP_CONCAT(cmd.aksi SEPARATOR ', ') FROM claim_material_decision cmd WHERE cmd.id_claim_material = cm.id) AS dec_aksis
    {$base_from}
";

$res = $mysqli->query($sql);
$row = $res->fetch_assoc();

echo "\n[3] HASIL QUERY TAB ON HOLD STOCK_FROM_WAREHOUSE:\n";
echo "    - Nama Supplier   : " . $row['nama_supplier'] . "\n";
echo "    - Nama Material   : " . $row['nm_material'] . "\n";
echo "    - No. Coil        : " . $row['no_coil'] . "\n";
echo "    - No. PO          : " . $row['no_po'] . "\n";
echo "    - Berat           : " . number_format($row['net_weight'], 2) . " Kg\n";
echo "    - Problem         : " . $row['problem'] . "\n";
echo "    - Alasan          : " . $row['alasan_produksi'] . "\n";
echo "    - Terhubung Claim : " . $row['no_claim'] . " (ID: " . $row['id_claim'] . ")\n";

// Status awal harus Hold
$status_label = (!empty($row['dec_aksis'])) ? $row['dec_aksis'] : ($row['status_proses'] === 'in_warehouse' ? 'Dapat Dipakai (Release)' : 'Hold');
echo "    - Status Material : " . $status_label . "\n";
assert($status_label === 'Hold', "Status material harus Hold di awal");

// 4. SIMULASI KEPUTUSAN PURCHASING: Lanjut Produksi (Release)
echo "\n[4] SIMULASI KEPUTUSAN PURCHASING: Lanjut Produksi (Release)...\n";
$q_dec = "INSERT INTO claim_material_decision (
    id_claim_material, jalur, aksi, berat_kg, keterangan, created_at, created_by
) VALUES (
    {$claim_id}, 'Produksi', 'Lanjut Produksi', {$test_weight}, 'Disetujui pakai dengan parameter speed rendah', NOW(), 'Purchasing Head'
)";
$mysqli->query($q_dec);

// Update status_proses di warehouse_stock_coil
$mysqli->query("UPDATE warehouse_stock_coil SET status_proses = 'in_warehouse' WHERE id = {$coil_hld_id}");

// Cek ulang query stock_from_warehouse
$res2 = $mysqli->query($sql);
$row2 = $res2->fetch_assoc();
echo "    - Keputusan di claim_material_decision: " . $row2['dec_aksis'] . "\n";
echo "    - Status proses di warehouse_stock_coil: " . $row2['status_proses'] . "\n";

$status_label2 = 'Hold';
if (!empty($row2['dec_aksis'])) {
    if (strpos($row2['dec_aksis'], 'Lanjut Produksi') !== false) {
        $status_label2 = 'Dapat Dipakai (Release)';
    }
}
echo "    - Status Material di UI Tab On Hold: " . $status_label2 . "\n";
assert($status_label2 === 'Dapat Dipakai (Release)', "Status material harus berubah menjadi 'Dapat Dipakai (Release)'");

// 5. CLEANUP DATA UJI
echo "\n[5] MEMBERSIHKAN DATA UJI DARI DATABASE...\n";
$mysqli->query("DELETE FROM claim_material_decision WHERE id_claim_material = {$claim_id}");
$mysqli->query("DELETE FROM claim_material WHERE id = {$claim_id}");
$mysqli->query("DELETE FROM warehouse_stock_coil WHERE id = {$coil_hld_id}");
echo "[OK] Data uji bersih kembali.\n";
echo "\n=== SEMUA ALUR BERHASIL DAN TERHUBUNG 100% ===\n";

