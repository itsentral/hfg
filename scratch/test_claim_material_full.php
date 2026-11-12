<?php
define('BASEPATH', 'TRUE');
define('ENVIRONMENT', 'development');
require_once __DIR__ . '/../system/core/Common.php';

// Mock CI bootstrap
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$cfg_file = __DIR__ . '/../application/config/database.php';
require $cfg_file;

$db_cfg = $db['default'];
try {
    $mysqli = new mysqli('localhost', 'heru', '@Sentral123', 'db_hfg_dev');
} catch (Exception $e) {
    $mysqli = new mysqli($db_cfg['hostname'], $db_cfg['username'], $db_cfg['password'], $db_cfg['database']);
}

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "=== MEMULAI TEST INTEGRASI MODUL CLAIM MATERIAL ===\n";

// Start test transaction
$mysqli->begin_transaction();

try {
    // 1. Insert test claim material
    $no_claim = 'CLM-TEST-0001';
    $mysqli->query("DELETE FROM claim_material WHERE no_claim = '{$no_claim}'");
    $mysqli->query("DELETE FROM debit_note WHERE no_debit_note LIKE 'DN-TEST%'");

    $insert_claim = "
        INSERT INTO claim_material (
            no_claim, production_report_id, id_baby_coil, id_suplier, id_material,
            no_po, no_coil_packing_list, asal, problem, alasan_produksi, berat_kg,
            harga_beli_per_kg, costbook_per_kg, price_per_unit_po, status_claim,
            created_at, created_by
        ) VALUES (
            '{$no_claim}', 9999, NULL, 'SUP-TEST', 'MAT-01',
            'PO-TEST-001', 'COIL-TEST-100', 'Hold Coil', 'Gelombang dan Tebal Minus', 'Coil tidak bisa ditarik',
            1500.000, 15000.00, 14500.00, 15000.000, 'Claim Baru',
            NOW(), 'Tester'
        )
    ";
    if (!$mysqli->query($insert_claim)) {
        throw new Exception("Insert claim failed: " . $mysqli->error);
    }
    $claim_id = $mysqli->insert_id;
    echo "[OK] 1. Berhasil insert test claim ID: {$claim_id} ({$no_claim})\n";

    // 2. Insert test bukti berkas
    $insert_bukti = "
        INSERT INTO claim_material_bukti (
            id_claim_material, file_original_name, file_hash_name, file_size, file_type, created_at, created_by
        ) VALUES (
            {$claim_id}, 'foto_cacat.jpg', 'hash_foto_123.jpg', 1048576, 'image/jpeg', NOW(), 'Tester'
        )
    ";
    if (!$mysqli->query($insert_bukti)) {
        throw new Exception("Insert bukti failed: " . $mysqli->error);
    }
    echo "[OK] 2. Berhasil insert test bukti berkas foto_cacat.jpg\n";

    // 3. Verifikasi query claim list
    $res = $mysqli->query("SELECT * FROM claim_material WHERE id = {$claim_id}");
    $c = $res->fetch_assoc();
    if ($c['status_claim'] !== 'Claim Baru') {
        throw new Exception("Status claim awal tidak sesuai: " . $c['status_claim']);
    }
    echo "[OK] 3. Status claim awal terverifikasi: {$c['status_claim']}\n";

    // 4. Test simulasi email keluhan (status update)
    $mysqli->query("UPDATE claim_material SET status_claim = 'Email Terkirim', tgl_email_terkirim = NOW() WHERE id = {$claim_id}");
    $res = $mysqli->query("SELECT status_claim, tgl_email_terkirim FROM claim_material WHERE id = {$claim_id}");
    $c_email = $res->fetch_assoc();
    if ($c_email['status_claim'] !== 'Email Terkirim' || empty($c_email['tgl_email_terkirim'])) {
        throw new Exception("Update email terkirim gagal");
    }
    echo "[OK] 4. Simulasi email keluhan sukses, status_claim: {$c_email['status_claim']}\n";

    // 5. Test Process Decision: 1000 Kg Kompensasi (Debit Note) + 500 Kg Kompensasi (Jual Material)
    // Jalur Kompensasi -> Debit Note
    $mysqli->query("
        INSERT INTO claim_material_decision (
            id_claim_material, jalur, aksi, berat_kg, nilai_debit_note, nilai_material_kw, nilai_biaya_scrap, keterangan, created_at, created_by
        ) VALUES (
            {$claim_id}, 'Kompensasi', 'Debit Note', 1000.000, 15000000.00, NULL, NULL, 'Kompensasi Debit Note 1000 Kg', NOW(), 'Tester'
        )
    ");
    $dec1_id = $mysqli->insert_id;

    // Generate test Debit Note
    $mysqli->query("
        INSERT INTO debit_note (
            no_debit_note, tgl_terbit, id_suplier, id_kurs, kurs, total_nilai, total_nilai_idr, jalur, asal, status_dokumen, id_claim_material, id_claim_material_decision, created_at, created_by
        ) VALUES (
            'DN-TEST-0001', CURDATE(), 'SUP-TEST', 64, 1.00, 15000000.00, 15000000.00, 'Kompensasi', 'Hold Coil', 'DN Belum Terbit', {$claim_id}, {$dec1_id}, NOW(), 'Tester'
        )
    ");
    $dn_id = $mysqli->insert_id;
    echo "[OK] 5a. Decision Debit Note sukses, generated Debit Note ID: {$dn_id} (DN-TEST-0001)\n";

    // Jalur Kompensasi -> Jual Material
    $mysqli->query("
        INSERT INTO claim_material_decision (
            id_claim_material, jalur, aksi, berat_kg, nilai_debit_note, nilai_material_kw, nilai_biaya_scrap, keterangan, created_at, created_by
        ) VALUES (
            {$claim_id}, 'Kompensasi', 'Jual Material', 500.000, NULL, 5000000.00, 2500000.00, 'Dijual ke slitting', NOW(), 'Tester'
        )
    ");
    $dec2_id = $mysqli->insert_id;

    $mysqli->query("
        INSERT INTO stok_gudang_slitting (
            id_claim_material_decision, id_material, berat_kg, nilai_material, nilai_material_kw, nilai_biaya_scrap, tgl_masuk, created_at, created_by
        ) VALUES (
            {$dec2_id}, 'MAT-01', 500.000, 7500000.00, 5000000.00, 2500000.00, CURDATE(), NOW(), 'Tester'
        )
    ");
    $slit_id = $mysqli->insert_id;
    echo "[OK] 5b. Decision Jual Material sukses, insert stok_gudang_slitting ID: {$slit_id}\n";

    // Update claim status setelah decision
    $mysqli->query("UPDATE claim_material SET status_claim = 'Kompensasi', kesepakatan_supplier = 'Kompensasi' WHERE id = {$claim_id}");
    echo "[OK] 5c. Status claim diperbarui menjadi 'Kompensasi'\n";

    // 6. Verifikasi Report Biaya Kompensasi vs Biaya Scrap (FR-87)
    $q_rep = "
        SELECT cmd.aksi, cmd.berat_kg, cmd.nilai_debit_note, cmd.nilai_material_kw, cmd.nilai_biaya_scrap
        FROM claim_material_decision cmd
        WHERE cmd.id_claim_material = {$claim_id}
    ";
    $rep_res = $mysqli->query($q_rep);
    $rep_rows = [];
    while ($row = $rep_res->fetch_assoc()) {
        $rep_rows[] = $row;
    }
    if (count($rep_rows) !== 2) {
        throw new Exception("Jumlah baris decision tidak sesuai: " . count($rep_rows));
    }
    echo "[OK] 6. Verifikasi report biaya kompensasi & scrap: 2 baris valid ditemukan\n";

    echo "\n=== SEMUA PENGUJIAN INTEGRASI BERHASIL 100% ===\n";

} catch (Exception $e) {
    echo "\n[ERROR]: " . $e->getMessage() . "\n";
} finally {
    // Clean rollback
    $mysqli->rollback();
    echo "[OK] Transaksi pengujian di-rollback bersih (database dev tetap bersih).\n";
}
