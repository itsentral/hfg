-- ==============================================================================
-- Rollback Migrasi 003: Kembalikan BOM ke single-variant
-- ==============================================================================

ALTER TABLE ms_bom_detail
    DROP COLUMN erat_per_meter;

ALTER TABLE ms_bom_header
    DROP INDEX uk_produk_varian,
    ADD UNIQUE KEY uk_produk (id_produk),
    DROP COLUMN is_default,
    DROP COLUMN arian_type;

DROP TABLE IF EXISTS master_standard_material;
