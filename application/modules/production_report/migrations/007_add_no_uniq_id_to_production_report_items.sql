-- ==============================================================================
-- Migrasi: Integrasi Rate Hasil Costing Varian BOM ke HPP Laporan Produksi & BOM
-- Referensi: FR-48, FR-74, NFR-14, NFR-15 & erd-revisi.drawio
-- Tanggal: 2026-10-27 (Diperbarui 2026-10-30)
-- ==============================================================================

-- 1. Tambah kolom no_uniq_id pada tr_spk_material_detail (Production Planning eksternal)
ALTER TABLE `tr_spk_material_detail` 
    ADD COLUMN IF NOT EXISTS `no_uniq_id` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'Varian BOM (no_uniq_id)' AFTER `id_produk_fg`;

-- 2. Tambah kolom no_uniq_id pada production_report_items
ALTER TABLE `production_report_items` 
    ADD COLUMN IF NOT EXISTS `no_uniq_id` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'Varian BOM (no_uniq_id)' AFTER `product_lvl_4_id`;

-- 3. Pastikan presisi berat_per_meter pada ms_bom_detail bertipe DECIMAL(14,6)
ALTER TABLE `ms_bom_detail` 
    MODIFY COLUMN `berat_per_meter` DECIMAL(14,6) NOT NULL DEFAULT 0.000000 COMMENT 'kg/m hasil hitung formula FR-51';

-- 4. Inisialisasi default no_uniq_id pada ms_bom_detail jika belum ada
UPDATE `ms_bom_detail` 
SET `no_uniq_id` = CONCAT('Type ', LPAD(`id_bom`, 3, '0'), '.', IF(`urut` > 0, `urut`, `id`))
WHERE `no_uniq_id` IS NULL OR `no_uniq_id` = '';
