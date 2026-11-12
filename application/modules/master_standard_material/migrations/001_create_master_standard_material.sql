-- =====================================================
-- Migrasi 001: Tabel Master Standard Material
-- Modul   : master_standard_material (FR-50, UC-N15, PG-08)
-- ERD     : halaman "Product Costing ERD", entitas master_standard_material
-- =====================================================
-- Master GLOBAL: satu baris (id = 1) berisi nilai standar yang dipakai menghitung
-- Berat/Meter di BOM:
--   ((Tebal Material - Standar Toleransi) x Lebar Coil x Standar Berat Jenis) + Coating
-- Satuan tidak disimpan/ditampilkan karena klien tidak menginformasikannya.
--
-- Jalankan di database UJI lebih dulu. Skrip aman dijalankan ulang (IF NOT EXISTS / WHERE NOT EXISTS).
-- Rollback: 001_create_master_standard_material.down.sql
-- =====================================================

CREATE TABLE IF NOT EXISTS master_standard_material (
    id                  INT NOT NULL AUTO_INCREMENT,
    standar_toleransi   DECIMAL(14,6) NOT NULL DEFAULT 0,
    standar_berat_jenis DECIMAL(14,6) NOT NULL DEFAULT 0,
    coating             DECIMAL(14,6) NOT NULL DEFAULT 0,
    latest_update       DATETIME DEFAULT NULL,
    updated_by          INT DEFAULT NULL COMMENT 'id_user yang terakhir mengubah',
    PRIMARY KEY (id)
);

-- Baris global awal (bernilai 0) bila belum ada
INSERT INTO master_standard_material (id, standar_toleransi, standar_berat_jenis, coating, latest_update, updated_by)
SELECT 1, 0, 0, 0, NULL, NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM master_standard_material WHERE id = 1);
