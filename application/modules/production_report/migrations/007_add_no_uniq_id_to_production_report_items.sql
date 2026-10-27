-- ==============================================================================
-- Migrasi: Integrasi Rate Hasil Costing Varian BOM ke HPP Laporan Produksi & BOM
-- Referensi: FR-48, FR-74, NFR-14, NFR-15 & erd-revisi.drawio
-- Tanggal: 2026-10-27
-- ==============================================================================

-- 1. Tambah kolom no_uniq_id pada tr_spk_material_detail (Production Planning eksternal)
ALTER TABLE \	r_spk_material_detail    ADD COLUMN IF NOT EXISTS o_uniq_id\ VARCHAR(50) NULL COMMENT 'Varian BOM (no_uniq_id)' AFTER \id_produk_fg\;

-- 2. Tambah kolom no_uniq_id pada production_report_items
ALTER TABLE \production_report_items    ADD COLUMN IF NOT EXISTS o_uniq_id\ VARCHAR(50) NULL COMMENT 'Varian BOM (no_uniq_id)' AFTER \product_lvl_4_id\;

-- 3. Pastikan presisi berat_per_meter pada ms_bom_detail bertipe DECIMAL(14,6)
ALTER TABLE \ms_bom_detail    MODIFY COLUMN \erat_per_meter\ DECIMAL(14,6) NOT NULL DEFAULT 0.000000 COMMENT 'kg/m hasil hitung formula FR-51';

-- 4. Tambah constraint UNIQUE pada ms_bom_detail untuk (id_bom, no_uniq_id)
UPDATE \ms_bom_detailSET o_uniq_id\ = CONCAT('Type ', LPAD(\id_bom\, 3, '0'), '.', IF(\urut\ > 0, \urut\, \id\))
WHERE o_uniq_id\ IS NULL OR o_uniq_id\ = '';

ALTER TABLE \ms_bom_detail    ADD UNIQUE KEY \uk_bom_detail_uniq\ (\id_bom\, o_uniq_id\);
