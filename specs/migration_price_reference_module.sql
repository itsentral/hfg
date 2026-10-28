-- ==============================================================================
-- Migrasi Modul Khusus: Price Reference (Product Costing UC-06 / FR-52)
-- Berdasarkan Spesifikasi ERD: specs/docs/diagrams/erd-revisi.drawio (tab: Product Costing ERD)
-- Entitas: price_reference_header (c_PRH), price_reference_detail (c_PRD), history_purchase (c_HP)
-- Menggunakan Collation: utf8mb4_general_ci (sesuai database db_hfg_dev)
-- ==============================================================================

-- 1. Tabel: price_reference_header (ID: c_PRH pada ERD)
CREATE TABLE IF NOT EXISTS `price_reference_header` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_material` VARCHAR(50) NOT NULL COMMENT 'FK new_inventory_4 (id / code_lv4)',
  `id_kurs` INT DEFAULT NULL COMMENT 'FK mata_uang.id_kurs',
  `price_reference_type` VARCHAR(50) NOT NULL DEFAULT 'MAX' COMMENT 'Tipe acuan: MAX (UC-06 / FR-52)',
  `latest_update` DATETIME NOT NULL,
  `updated_by` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_prh_mat` (`id_material`),
  KEY `idx_prh_kurs` (`id_kurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tabel: price_reference_detail (ID: c_PRD pada ERD)
CREATE TABLE IF NOT EXISTS `price_reference_detail` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_price_reference_header` INT NOT NULL COMMENT 'FK price_reference_header.id (K6)',
  `id_material` VARCHAR(50) NOT NULL COMMENT 'FK new_inventory_4',
  `max_price_reference` DECIMAL(14,6) NOT NULL DEFAULT '0.000000' COMMENT 'USD/Kg Max (dipakai costing)',
  `newest_price_reference` DECIMAL(14,6) NOT NULL DEFAULT '0.000000' COMMENT 'USD/Kg Terakhir (info)',
  `newest_latest_update` DATETIME DEFAULT NULL,
  `average_price_reference` DECIMAL(14,6) NOT NULL DEFAULT '0.000000' COMMENT 'USD/Kg Rata-rata (info)',
  `average_latest_update` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_prd_header` (`id_price_reference_header`),
  KEY `idx_prd_mat` (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Tabel: history_purchase (ID: c_HP pada ERD)
CREATE TABLE IF NOT EXISTS `history_purchase` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_purchase_order` VARCHAR(50) DEFAULT NULL COMMENT 'FK / No PO (nullable jika manual)',
  `id_material` VARCHAR(50) NOT NULL COMMENT 'FK new_inventory_4',
  `price_unit` DECIMAL(14,6) NOT NULL DEFAULT '0.000000' COMMENT 'Harga USD/Kg',
  `period_month` VARCHAR(20) NOT NULL COMMENT 'Bulan / Periode (1 s.d. 12 atau YYYY-MM)',
  `created_at` DATETIME NOT NULL,
  `created_by` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_hp_mat` (`id_material`),
  KEY `idx_hp_po` (`id_purchase_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Konversi jika tabel sebelumnya sudah dibuat dengan collation lain
ALTER TABLE `price_reference_header` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `price_reference_detail` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `history_purchase` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 4. Daftarkan permission Price_Reference jika belum ada
INSERT INTO `permissions` (`id_permission`, `nm_permission`, `ket`, `nm_menu`) VALUES
(1983, 'Price_Reference.View', 'View Price Reference', 'Price Reference'),
(1984, 'Price_Reference.Manage', 'Manage Price Reference', 'Price Reference')
ON DUPLICATE KEY UPDATE `nm_permission` = VALUES(`nm_permission`);

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`) VALUES
(1, 1983), (1, 1984);

-- 5. Arahkan menu ID 532 (Price Reference) di bawah Parent 120 (Product Costing) ke controller 'price_reference'
UPDATE `menus`
SET `link` = 'price_reference',
    `permission_id` = 1983
WHERE `id` = 532 OR (`title` = 'Price Reference' AND `parent_id` = 120);
