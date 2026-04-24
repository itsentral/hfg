-- Tambah kolom is_delete ke ms_bom_header dan ms_bom_detail
-- Gunakan soft delete, tidak hard delete

ALTER TABLE ms_bom_header
    ADD COLUMN is_delete TINYINT(1) NOT NULL DEFAULT 0 AFTER status,
    ADD COLUMN deleted_by INT DEFAULT NULL AFTER is_delete,
    ADD COLUMN deleted_at DATETIME DEFAULT NULL AFTER deleted_by,
    ADD INDEX idx_is_delete (is_delete);

ALTER TABLE ms_bom_detail
    ADD COLUMN is_delete TINYINT(1) NOT NULL DEFAULT 0 AFTER keterangan,
    ADD INDEX idx_is_delete (is_delete);

-- Update comment kolom id_produk agar jelas mengacu ke product_lvl_4
ALTER TABLE ms_bom_header
    MODIFY COLUMN id_produk VARCHAR(50) NOT NULL COMMENT 'code_lv4 dari product_lvl_4';

ALTER TABLE ms_bom_detail
    MODIFY COLUMN id_material VARCHAR(50) NOT NULL COMMENT 'code_lv4 dari new_inventory_4 category=material';
