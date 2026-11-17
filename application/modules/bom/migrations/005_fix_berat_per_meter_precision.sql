-- ==============================================================================
-- Migrasi 005: Samakan tipe ms_bom_detail.berat_per_meter dengan ERD/migrasi 003
-- Referensi : FR-51, erd-revisi.drawio -> berat_per_meter (kg/m, terhitung, DECIMAL(14,6))
-- Latar     : sebagian database memiliki kolom DECIMAL(10,2) NULL sehingga nilai dibulatkan
--             2 desimal dan banyak baris NULL. Berat/Meter kini boleh diedit manual (4+ desimal).
-- Aman dijalankan ulang. Baris NULL menjadi 0 (= belum dihitung; dihitung otomatis saat BOM disimpan).
-- ==============================================================================
CREATE TABLE IF NOT EXISTS bak_ms_bom_detail_berat_per_meter AS
    SELECT id, berat_per_meter FROM ms_bom_detail;

UPDATE ms_bom_detail SET berat_per_meter = 0 WHERE berat_per_meter IS NULL;

ALTER TABLE ms_bom_detail
    MODIFY berat_per_meter DECIMAL(14,6) NOT NULL DEFAULT 0.000000 COMMENT 'kg/m hasil hitung formula FR-51';
