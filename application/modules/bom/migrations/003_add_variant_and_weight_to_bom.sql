-- ==============================================================================
-- Migrasi 003: Dukungan Multi-Varian BOM & Berat per Meter (Tahap 1 Product Costing)
-- Referensi : FR-41, FR-50, FR-51, FR-63 (docs/fr-nfr.md) & erd-revisi.drawio
-- ==============================================================================

-- 1. Tabel Master Standard Material (global 1 baris, toleransi, berat jenis, coating)
CREATE TABLE IF NOT EXISTS master_standard_material (
    id INT NOT NULL AUTO_INCREMENT,
    standar_toleransi DECIMAL(14,6) NOT NULL DEFAULT 0.000000 COMMENT 'toleransi tebal (mm)',
    standar_berat_jenis DECIMAL(14,6) NOT NULL DEFAULT 7.850000 COMMENT 'berat jenis baja (kg/dm3 atau g/cm3)',
    coating DECIMAL(14,6) NOT NULL DEFAULT 0.000000 COMMENT 'nilai coating',
    latest_update DATETIME DEFAULT NULL,
    updated_by INT DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO master_standard_material (id, standar_toleransi, standar_berat_jenis, coating, latest_update)
VALUES (1, 0.000000, 7.850000, 0.000000, NOW());

-- 2. Modifikasi ms_bom_header: tambah varian_type & is_default, ubah unique constraint
ALTER TABLE ms_bom_header
    ADD COLUMN varian_type VARCHAR(50) NOT NULL DEFAULT 'Type 001.1' COMMENT 'Varian tebal / No Uniq ID' AFTER nm_produk,
    ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = default BOM untuk SPK/Produksi, 0 = varian tambahan' AFTER status;

-- Lepas unique key produk lama
ALTER TABLE ms_bom_header DROP INDEX uk_produk;

-- Pasang composite unique key per produk, varian, dan status delete
ALTER TABLE ms_bom_header ADD UNIQUE KEY uk_produk_varian (id_produk, varian_type, is_delete);

-- 3. Modifikasi ms_bom_detail: tambah berat_per_meter
ALTER TABLE ms_bom_detail
    ADD COLUMN berat_per_meter DECIMAL(14,6) NOT NULL DEFAULT 0.000000 COMMENT 'kg/m hasil hitung formula FR-51' AFTER qty;
