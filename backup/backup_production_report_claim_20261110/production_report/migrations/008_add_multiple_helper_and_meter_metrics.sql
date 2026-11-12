-- ==============================================================================
-- Migrasi: Multiple Helper & Metrik Berat/Meter pada Modul Production Report
-- Modul: production_report
-- Tanggal: 10-11-2026
-- ==============================================================================

-- 1. Perlebar kolom employee_helper di production_reports agar dapat menyimpan lebih dari 1 nama helper (dipisahkan koma)
ALTER TABLE `production_reports` 
    MODIFY COLUMN `employee_helper` VARCHAR(255) NULL COMMENT 'Nama helper (dapat lebih dari 1, dipisahkan koma)';

-- 2. Tambahkan kolom metrik per meter pada tabel production_report_items (KW 1 & Stok Bebas)
ALTER TABLE `production_report_items`
    ADD COLUMN `panjang_meter` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Panjang produk dalam meter dari Master Produk' AFTER `size_meter`,
    ADD COLUMN `berat_per_meter_aktual` DECIMAL(10,4) DEFAULT 0.0000 COMMENT 'Aktual berat/meter (berat/pcs aktual / panjang produk)' AFTER `panjang_meter`,
    ADD COLUMN `berat_per_meter_standard` DECIMAL(10,4) DEFAULT 0.0000 COMMENT 'Standar berat/meter dari BOM Material' AFTER `berat_per_meter_aktual`,
    ADD COLUMN `percentage_selisih_meter` DECIMAL(6,2) DEFAULT 0.00 COMMENT '% Selisih berat/meter terhadap standar' AFTER `berat_per_meter_standard`;
