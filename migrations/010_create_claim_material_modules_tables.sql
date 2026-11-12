-- ==============================================================================
-- Migrasi: Modul Claim Material (FEAT-11)
-- File: migrations/010_create_claim_material_modules_tables.sql
-- Tanggal: 10-11-2026
-- ==============================================================================

-- 1. Tabel stok_gudang_slitting (Menampung material hasil keputusan Jual Material)
CREATE TABLE IF NOT EXISTS `stok_gudang_slitting` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_claim_material_decision` INT NOT NULL,
  `id_material` VARCHAR(50) NOT NULL,
  `berat_kg` DECIMAL(18,3) NOT NULL DEFAULT 0.000,
  `nilai_material` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Berat x Harga Beli/Kg',
  `nilai_material_kw` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Diakui sebagai persediaan inventory',
  `nilai_biaya_scrap` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Kerugian / Beban Scrap',
  `tgl_masuk` DATE DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_by` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_slit_dec` (`id_claim_material_decision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Gudang Slitting: material hasil Jual Material';

-- 2. Tabel debit_note (Draf dan dokumen Debit Note Supplier)
CREATE TABLE IF NOT EXISTS `debit_note` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `no_debit_note` VARCHAR(50) NOT NULL COMMENT 'Otomatis: DN-YYMM-XXXX',
  `tgl_terbit` DATE DEFAULT NULL,
  `id_suplier` VARCHAR(50) NOT NULL,
  `id_kurs` INT NOT NULL DEFAULT 64 COMMENT 'mata_uang.id_kurs (default 64=IDR)',
  `kurs` DECIMAL(18,2) NOT NULL DEFAULT 1.00,
  `total_nilai` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai dalam mata uang PO',
  `total_nilai_idr` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Total Nilai x Kurs',
  `jalur` VARCHAR(30) NOT NULL COMMENT 'Kompensasi / Retur',
  `asal` VARCHAR(30) NOT NULL COMMENT 'Hold Coil / KW 2 (Supplier) / Reject (Supplier)',
  `status_dokumen` VARCHAR(40) NOT NULL DEFAULT 'DN Belum Terbit' COMMENT 'DN Belum Terbit / Sudah Terbit / Sudah Alokasi ke Hutang / Belum Terima Uang / Sudah Terima Uang',
  `id_claim_material` INT NOT NULL,
  `id_claim_material_decision` INT NOT NULL,
  `id_surat_jalan_retur` INT DEFAULT NULL,
  `tgl_penagihan` DATE DEFAULT NULL,
  `tgl_jatuh_tempo_tagihan` DATE DEFAULT NULL,
  `catatan_tagihan` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_by` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dn_no` (`no_debit_note`),
  KEY `idx_dn_suplier` (`id_suplier`),
  KEY `idx_dn_kurs` (`id_kurs`),
  KEY `idx_dn_claim` (`id_claim_material`),
  KEY `idx_dn_dec` (`id_claim_material_decision`),
  KEY `idx_dn_sj` (`id_surat_jalan_retur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Debit Note supplier (FEAT-11)';

-- 3. Setup Menu & Permissions untuk Claim Material
-- Tambahkan permission
INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Claim_Material.View', 0, 'Claim Material', 'View Claim Material', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Claim_Material.View');

INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Claim_Material.Manage', 0, 'Claim Material', 'Manage Claim Material', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Claim_Material.Manage');

SET @view_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Claim_Material.View' LIMIT 1);
SET @manage_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Claim_Material.Manage' LIMIT 1);

-- Berikan permission ke group Admin (id_group = 1)
INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`) VALUES
(1, @view_perm_id),
(1, @manage_perm_id);

-- Tambahkan Menu Induk: Claim Material
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
SELECT 'Claim Material', '#', 'ti ti-alert-triangle', '_self', 1, 0, @view_perm_id, 1, 95
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `title` = 'Claim Material' AND `parent_id` = 0);

SET @parent_claim_id = (SELECT `id` FROM `menus` WHERE `title` = 'Claim Material' AND `parent_id` = 0 LIMIT 1);

-- Update id_menu di permissions
UPDATE `permissions` SET `id_menu` = @parent_claim_id WHERE `id_permission` IN (@view_perm_id, @manage_perm_id);

-- Tambahkan Sub-menu 1: Material On Hold
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
SELECT 'Material On Hold', 'claim_material/on_hold', 'ti ti-hand-stop', '_self', 1, @parent_claim_id, @view_perm_id, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `link` = 'claim_material/on_hold');

-- Tambahkan Sub-menu 2: Daftar Claim Material
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
SELECT 'Daftar Claim Material', 'claim_material', 'ti ti-mail', '_self', 1, @parent_claim_id, @view_perm_id, 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `link` = 'claim_material');

-- Tambahkan Sub-menu 3: Material Dijual (Gudang Slitting)
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
SELECT 'Material Dijual (Gudang Slitting)', 'claim_material/slitting', 'ti ti-box', '_self', 1, @parent_claim_id, @view_perm_id, 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `link` = 'claim_material/slitting');

-- Tambahkan Sub-menu 4: Report Biaya Kompensasi
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
SELECT 'Report Biaya Kompensasi', 'claim_material/report_biaya', 'ti ti-chart-bar', '_self', 1, @parent_claim_id, @view_perm_id, 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `link` = 'claim_material/report_biaya');

