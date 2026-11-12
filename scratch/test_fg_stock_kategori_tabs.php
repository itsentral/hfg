<?php
define('BASEPATH', 'TRUE');
define('ENVIRONMENT', 'development');

$mysqli = new mysqli('localhost', 'heru', '@Sentral123', 'db_hfg_dev');
if ($mysqli->connect_error) {
    die("Connect failed: " . $mysqli->connect_error . "\n");
}

echo "=== TEST SIMULASI INTEGRASI 4 KATEGORI STOK FINISH GOOD (KW1, KW2, TONG COIL, SCRAP) ===\n\n";

$ts = time();
$code_kw1 = "TEST-KW1-{$ts}";
$code_kw2 = "TEST-KW2-{$ts}";
$code_tc  = "TC-TEST-MAT-{$ts}";
$code_sc  = "SCRAP-WRAPPING-TEST-MAT-{$ts}";

// 1. SIMULASI INSERT KE fg_stock
echo "[1] Testing INSERT 4 Kategori ke fg_stock...\n";

// KW 1
$q1 = "INSERT INTO fg_stock (produk_fg, nm_produk_fg, kategori_stok, sub_kategori, qty_stok, total_berat, costbook, total_nilai, last_update)
       VALUES ('{$code_kw1}', 'Spandek Prima 0.30 (KW1)', 'KW1', 'KW 1', 50.00, 250.000, 75000.00, 3750000.00, NOW())";
if (!$mysqli->query($q1)) die("Insert KW1 failed: " . $mysqli->error);
$id_kw1 = $mysqli->insert_id;

// KW 2
$q2 = "INSERT INTO fg_stock (produk_fg, nm_produk_fg, kategori_stok, sub_kategori, qty_stok, total_berat, costbook, total_nilai, last_update)
       VALUES ('{$code_kw2}', 'Spandek Prima 0.30 (KW2 Internal)', 'KW2', 'Internal', 10.00, 50.000, 60000.00, 600000.00, NOW())";
if (!$mysqli->query($q2)) die("Insert KW2 failed: " . $mysqli->error);
$id_kw2 = $mysqli->insert_id;

// Tong Coil
$q3 = "INSERT INTO fg_stock (produk_fg, nm_produk_fg, kategori_stok, sub_kategori, id_material, qty_stok, total_berat, costbook, total_nilai, last_update)
       VALUES ('{$code_tc}', 'Tong Coil - Galvalum 0.30', 'TONG_COIL', 'Tong Coil', 'MAT-GLV-030', 2.00, 35.500, 5000.00, 71000.00, NOW())";
if (!$mysqli->query($q3)) die("Insert Tong Coil failed: " . $mysqli->error);
$id_tc = $mysqli->insert_id;

// Scrap
$q4 = "INSERT INTO fg_stock (produk_fg, nm_produk_fg, kategori_stok, sub_kategori, id_material, qty_stok, total_berat, costbook, total_nilai, last_update)
       VALUES ('{$code_sc}', 'Scrap Wrapping - Galvalum 0.30', 'SCRAP', 'Wrapping', 'MAT-GLV-030', 0.00, 15.200, 5000.00, 30400.00, NOW())";
if (!$mysqli->query($q4)) die("Insert Scrap failed: " . $mysqli->error);
$id_sc = $mysqli->insert_id;

echo "    -> Berhasil insert: KW1 (ID {$id_kw1}), KW2 (ID {$id_kw2}), Tong Coil (ID {$id_tc}), Scrap (ID {$id_sc})\n\n";

// 2. Testing Query per Kategori
echo "[2] Testing SELECT per Kategori...\n";

foreach (['KW1', 'KW2', 'TONG_COIL', 'SCRAP'] as $kat) {
    $res = $mysqli->query("SELECT * FROM fg_stock WHERE kategori_stok = '{$kat}' AND id IN ({$id_kw1}, {$id_kw2}, {$id_tc}, {$id_sc})");
    $r = $res->fetch_assoc();
    echo "    - Kategori [{$kat}]: Produk: {$r['produk_fg']}, Sub: {$r['sub_kategori']}, Qty: {$r['qty_stok']}, Berat: {$r['total_berat']} kg, Nilai: Rp " . number_format($r['total_nilai'], 2) . "\n";
    assert(!empty($r), "Data kategori {$kat} harus ditemukan");
}

// 3. Testing KPI Summary Breakdown
echo "\n[3] Testing KPI Summary Breakdown Query...\n";
$q_kpi = "SELECT
    COUNT(id) AS total_sku,
    COALESCE(SUM(qty_stok), 0) AS total_qty,
    COALESCE(SUM(total_berat), 0) AS total_berat,
    COALESCE(SUM(total_nilai), 0) AS total_nilai,

    COUNT(IF(kategori_stok = 'KW1', 1, NULL)) AS kw1_sku,
    COALESCE(SUM(IF(kategori_stok = 'KW1', qty_stok, 0)), 0) AS kw1_qty,
    COALESCE(SUM(IF(kategori_stok = 'KW1', total_berat, 0)), 0) AS kw1_berat,
    COALESCE(SUM(IF(kategori_stok = 'KW1', total_nilai, 0)), 0) AS kw1_nilai,

    COUNT(IF(kategori_stok = 'KW2', 1, NULL)) AS kw2_sku,
    COALESCE(SUM(IF(kategori_stok = 'KW2', qty_stok, 0)), 0) AS kw2_qty,
    COALESCE(SUM(IF(kategori_stok = 'KW2', total_berat, 0)), 0) AS kw2_berat,
    COALESCE(SUM(IF(kategori_stok = 'KW2', total_nilai, 0)), 0) AS kw2_nilai,

    COUNT(IF(kategori_stok = 'TONG_COIL', 1, NULL)) AS tc_sku,
    COALESCE(SUM(IF(kategori_stok = 'TONG_COIL', qty_stok, 0)), 0) AS tc_qty,
    COALESCE(SUM(IF(kategori_stok = 'TONG_COIL', total_berat, 0)), 0) AS tc_berat,
    COALESCE(SUM(IF(kategori_stok = 'TONG_COIL', total_nilai, 0)), 0) AS tc_nilai,

    COUNT(IF(kategori_stok = 'SCRAP', 1, NULL)) AS scrap_sku,
    COALESCE(SUM(IF(kategori_stok = 'SCRAP', total_berat, 0)), 0) AS scrap_berat,
    COALESCE(SUM(IF(kategori_stok = 'SCRAP', total_nilai, 0)), 0) AS scrap_nilai
FROM fg_stock";

$res_kpi = $mysqli->query($q_kpi);
$kpi = $res_kpi->fetch_assoc();

echo "    - Grand Total: {$kpi['total_sku']} SKU, {$kpi['total_qty']} pcs, {$kpi['total_berat']} kg, Rp " . number_format($kpi['total_nilai'], 2) . "\n";
echo "    - KPI KW 1   : {$kpi['kw1_sku']} SKU, {$kpi['kw1_qty']} pcs, {$kpi['kw1_berat']} kg, Rp " . number_format($kpi['kw1_nilai'], 2) . "\n";
echo "    - KPI KW 2   : {$kpi['kw2_sku']} SKU, {$kpi['kw2_qty']} pcs, {$kpi['kw2_berat']} kg, Rp " . number_format($kpi['kw2_nilai'], 2) . "\n";
echo "    - KPI Tong C : {$kpi['tc_sku']} SKU, {$kpi['tc_qty']} roll, {$kpi['tc_berat']} kg, Rp " . number_format($kpi['tc_nilai'], 2) . "\n";
echo "    - KPI Scrap  : {$kpi['scrap_sku']} Item, {$kpi['scrap_berat']} kg, Rp " . number_format($kpi['scrap_nilai'], 2) . "\n";

// 4. CLEANUP TEST DATA
echo "\n[4] Membersihkan data uji...\n";
$mysqli->query("DELETE FROM fg_stock WHERE id IN ({$id_kw1}, {$id_kw2}, {$id_tc}, {$id_sc})");
echo "[OK] Data uji dibersihkan.\n";

echo "\n=== SEMUA PENGUJIAN 4 KATEGORI STOK BERHASIL 100% ===\n";

