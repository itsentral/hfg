-- ==============================================================================
-- Migration: 012_fg_stock_kategori_tabs.sql
-- Keterangan: Penambahan kolom kategori_stok, sub_kategori, dan id_material
--             pada tabel fg_stock dan fg_stock_ledger untuk membedakan
--             KW 1, KW 2, Tong Coil, dan Scrap.
-- Tanggal: 11-11-2026
-- ==============================================================================

-- 1. Penambahan Kolom pada fg_stock
ALTER TABLE `fg_stock`
ADD COLUMN `kategori_stok` ENUM('KW1', 'KW2', 'TONG_COIL', 'SCRAP') NOT NULL DEFAULT 'KW1' AFTER `nm_produk_fg`,
ADD COLUMN `sub_kategori` VARCHAR(50) NULL AFTER `kategori_stok`,
ADD COLUMN `id_material` VARCHAR(50) NULL AFTER `sub_kategori`,
ADD INDEX `idx_fg_stock_kategori` (`kategori_stok`);

-- 2. Penambahan Kolom pada fg_stock_ledger
ALTER TABLE `fg_stock_ledger`
ADD COLUMN `kategori_stok` ENUM('KW1', 'KW2', 'TONG_COIL', 'SCRAP') NOT NULL DEFAULT 'KW1' AFTER `category_type`,
ADD COLUMN `sub_kategori` VARCHAR(50) NULL AFTER `kategori_stok`,
ADD COLUMN `id_material` VARCHAR(50) NULL AFTER `sub_kategori`,
ADD INDEX `idx_fg_ledger_kategori` (`kategori_stok`);

-- 3. Normalisasi Data Existing
UPDATE `fg_stock`
SET `kategori_stok` = 'KW2',
    `sub_kategori` = CASE 
        WHEN `produk_fg` LIKE '%-KW2INT%' THEN 'Internal' 
        WHEN `produk_fg` LIKE '%-KW2SUP%' THEN 'Supplier' 
        ELSE 'KW 2' 
    END
WHERE `produk_fg` LIKE '%-KW2%';

UPDATE `fg_stock_ledger`
SET `kategori_stok` = 'KW2',
    `sub_kategori` = CASE 
        WHEN `category_type` = 'kw_2_internal' THEN 'Internal' 
        WHEN `category_type` = 'kw_2_supplier' THEN 'Supplier' 
        ELSE 'KW 2' 
    END
WHERE `category_type` IN ('kw_2_internal', 'kw_2_supplier') OR `produk_fg` LIKE '%-KW2%';

