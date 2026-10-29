-- ==============================================================================
-- SKRIP MIGRASI KONSOLIDASI PRODUKSI: SISTEM PRODUCT COSTING HFG
-- Tanggal: 22 Oktober 2026
-- Database Target: MySQL 5.7 / 8.0 (db_hfg_prod)
-- Deskripsi: Berisi seluruh ALTER TABLE, CREATE TABLE, SEED DATA, dan MENU/PERMISSIONS
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- BAGIAN 1: MODIFIKASI TABEL EKSISTING (ALTER TABLE)
-- Menggunakan stored procedure agar aman dijalankan ulang (idempotent)
-- ------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `AddColIfNotExists`;
DELIMITER $$
CREATE PROCEDURE `AddColIfNotExists`(
    IN in_table VARCHAR(64),
    IN in_col VARCHAR(64),
    IN in_def TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = in_table
          AND COLUMN_NAME = in_col
    ) THEN
        SET @ddl = CONCAT('ALTER TABLE `', in_table, '` ADD COLUMN `', in_col, '` ', in_def);
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END $$
DELIMITER ;

-- 1.1 Tabel ms_bom_header: Varian Tebal, Flag Default, & Soft Delete
CALL AddColIfNotExists('ms_bom_header', 'varian_type', "VARCHAR(50) NOT NULL DEFAULT 'Type 001.1' COMMENT 'Varian tebal / No Uniq ID' AFTER `nm_produk`");
CALL AddColIfNotExists('ms_bom_header', 'is_default', "TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Flag varian default aktif' AFTER `status`");
CALL AddColIfNotExists('ms_bom_header', 'is_delete', "TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_default`");
CALL AddColIfNotExists('ms_bom_header', 'deleted_by', "INT NULL AFTER `is_delete`");
CALL AddColIfNotExists('ms_bom_header', 'deleted_at', "DATETIME NULL AFTER `deleted_by`");

-- 1.2 Tabel ms_bom_detail: No Uniq ID, Berat per Meter, & Soft Delete
CALL AddColIfNotExists('ms_bom_detail', 'no_uniq_id', "VARCHAR(50) NULL COMMENT 'No unik material BOM Type 00X.Y' AFTER `id_bom`");
CALL AddColIfNotExists('ms_bom_detail', 'berat_per_meter', "DECIMAL(14,6) NOT NULL DEFAULT 0.000000 COMMENT 'kg/m hasil formula standar' AFTER `qty`");
CALL AddColIfNotExists('ms_bom_detail', 'is_delete', "TINYINT(1) NOT NULL DEFAULT 0 AFTER `keterangan`");

-- 1.3 Tabel mata_uang: Kolom Nominal Kurs
CALL AddColIfNotExists('mata_uang', 'kurs', "DOUBLE NULL DEFAULT NULL COMMENT 'Nominal kurs terhadap Rupiah (IDR)' AFTER `mata_uang`");

-- Update nilai dasar kurs jika belum terisi
UPDATE `mata_uang` SET `kurs` = 1.00 WHERE `kode` = 'IDR' AND (`kurs` IS NULL OR `kurs` = 0);
UPDATE `mata_uang` SET `kurs` = 17863.00 WHERE `kode` = 'USD' AND (`kurs` IS NULL OR `kurs` = 0);
UPDATE `mata_uang` SET `kurs` = 7000.00 WHERE `kode` = 'RMB' AND (`kurs` IS NULL OR `kurs` = 0);

-- 1.4 Tabel new_inventory_4: Verifikasi Kolom Kurs & Price Reference Material
CALL AddColIfNotExists('new_inventory_4', 'price_ref', "DOUBLE NULL DEFAULT NULL COMMENT 'Lower price IDR'");
CALL AddColIfNotExists('new_inventory_4', 'price_ref_usd', "DOUBLE NULL DEFAULT NULL COMMENT 'Lower price USD'");
CALL AddColIfNotExists('new_inventory_4', 'price_ref_high', "DOUBLE NULL DEFAULT NULL COMMENT 'Higher price IDR'");
CALL AddColIfNotExists('new_inventory_4', 'price_ref_high_usd', "DOUBLE NULL DEFAULT NULL COMMENT 'Higher price USD'");
CALL AddColIfNotExists('new_inventory_4', 'price_ref_use', "DOUBLE NULL DEFAULT NULL COMMENT 'Official Price Ref IDR'");
CALL AddColIfNotExists('new_inventory_4', 'price_ref_use_usd', "DOUBLE NULL DEFAULT NULL COMMENT 'Official Price Ref USD'");
CALL AddColIfNotExists('new_inventory_4', 'kurs', "DOUBLE NULL DEFAULT NULL COMMENT 'Kurs USD yang dipakai saat set price'");

DROP PROCEDURE IF EXISTS `AddColIfNotExists`;


-- ------------------------------------------------------------------------------
-- BAGIAN 2: PEMBUATAN TABEL BARU (20 TABEL BARU)
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- Table: master_standard_material
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `master_standard_material` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `standar_toleransi` decimal(14,6) NOT NULL DEFAULT '0.000000' COMMENT 'toleransi tebal (mm)',\n  `standar_berat_jenis` decimal(14,6) NOT NULL DEFAULT '7.850000' COMMENT 'berat jenis baja (kg/dm3 atau g/cm3)',\n  `coating` decimal(14,6) NOT NULL DEFAULT '0.000000' COMMENT 'nilai coating',\n  `latest_update` datetime DEFAULT NULL,\n  `updated_by` int DEFAULT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: flow_process_header
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `flow_process_header` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `code_lv2` varchar(50) NOT NULL COMMENT 'kode Product Category (product_lvl_2.code_lv2)',\n  `cycletime` decimal(14,6) NOT NULL DEFAULT '0.000000' COMMENT 'jumlah cycletime semua proses, menit/meter',\n  `total_man_hour` decimal(18,8) NOT NULL DEFAULT '0.00000000' COMMENT 'jumlah man hour semua proses, jam/meter',\n  `latest_update` datetime DEFAULT NULL,\n  `updated_by` int DEFAULT NULL COMMENT 'id_user yang terakhir mengubah',\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `uk_flow_process_code_lv2` (`code_lv2`)\n) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: flow_process_detail
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `flow_process_detail` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `id_flow_process_header` int NOT NULL,\n  `costcentre_area` varchar(150) DEFAULT NULL COMMENT 'nama cost centre (ms_costcenter.nama_costcenter)',\n  `process_name` varchar(150) NOT NULL,\n  `machine` varchar(150) DEFAULT NULL COMMENT 'teks bebas',\n  `cycletime` decimal(14,6) NOT NULL DEFAULT '0.000000' COMMENT 'menit/meter',\n  `man_power` int NOT NULL DEFAULT '0' COMMENT 'jumlah orang',\n  `total_man_hour` decimal(18,8) NOT NULL DEFAULT '0.00000000' COMMENT 'cycletime x man_power / 60, jam/meter',\n  PRIMARY KEY (`id`),\n  KEY `idx_flow_process_detail_header` (`id_flow_process_header`),\n  CONSTRAINT `fk_flow_process_detail_header` FOREIGN KEY (`id_flow_process_header`) REFERENCES `flow_process_header` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: man_power_rate
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `man_power_rate` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `salary_direct_man_power` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT 'Subtotal Gaji Pokok Direct Labor',\n  `bpjs` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT 'Input manual BPJS nominal',\n  `biaya_lain_lain` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT 'Subtotal Biaya Lain-lain prorate',\n  `total_direct_labor_per_bulan` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT 'Total = Salary + BPJS + Lain-lain',\n  `jam_kerja_per_bulan` decimal(10,2) NOT NULL DEFAULT '173.00' COMMENT 'Parameter jam kerja (default 173)',\n  `rate_direct_labor` decimal(18,4) NOT NULL DEFAULT '0.0000' COMMENT 'Total / Jam Kerja (belum dibulatkan)',\n  `porsi_operator` decimal(6,4) NOT NULL DEFAULT '0.3333' COMMENT 'Parameter porsi operator (1/3)',\n  `rate_1_3_operator` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT 'CEIL(rate_direct_labor * 1/3) dibulatkan ke atas ke Rupiah penuh',\n  `latest_update` datetime DEFAULT NULL,\n  `updated_by` int DEFAULT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: man_power_rate_component
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `man_power_rate_component` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `id_man_power_rate` int NOT NULL,\n  `kelompok` enum('salary','bpjs','lain_lain') NOT NULL,\n  `nama_komponen` varchar(150) NOT NULL,\n  `nilai` decimal(18,2) NOT NULL DEFAULT '0.00',\n  `keterangan` varchar(255) DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  KEY `idx_mpr_component_header` (`id_man_power_rate`),\n  CONSTRAINT `fk_mpr_component_header` FOREIGN KEY (`id_man_power_rate`) REFERENCES `man_power_rate` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: rate_borongan_helper
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rate_borongan_helper` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `code_lv2` varchar(50) NOT NULL COMMENT 'Product Category',\n  `rate_borongan_helper` decimal(18,4) NOT NULL DEFAULT '0.0000' COMMENT 'Satuan Rp/Meter',\n  `latest_update` datetime DEFAULT NULL,\n  `updated_by` int DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `uk_rbh_code_lv2` (`code_lv2`)\n) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: faktor_indirect_labor
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faktor_indirect_labor` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `faktor_indirect_labor` decimal(6,4) NOT NULL DEFAULT '1.5000' COMMENT 'Pengali Man Power Cost (global, misal 1.50)',\n  `latest_update` datetime DEFAULT NULL,\n  `updated_by` int DEFAULT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: rate_bahan_pendukung_foh
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rate_bahan_pendukung_foh` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `code_lv2` varchar(50) NOT NULL COMMENT 'Product Category',\n  `rate_pendukung_khusus` decimal(18,4) NOT NULL DEFAULT '0.0000' COMMENT 'Satuan Rp/Kg',\n  `rate_foh` decimal(18,4) NOT NULL DEFAULT '0.0000' COMMENT 'Satuan Rp/Kg',\n  `latest_update` datetime DEFAULT NULL,\n  `updated_by` int DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `uk_rbpf_code_lv2` (`code_lv2`)\n) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: rate_generals
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rate_generals` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `rate_consumables` decimal(18,4) NOT NULL DEFAULT '0.0000' COMMENT 'Rp/Kg',\n  `rate_scrap` decimal(6,4) NOT NULL DEFAULT '0.0000' COMMENT '% dari Material Cost',\n  `rate_biaya_koordinasi` decimal(18,4) NOT NULL DEFAULT '0.0000' COMMENT 'Rp/Kg',\n  `rate_other_cost_sales_marketing` decimal(6,4) NOT NULL DEFAULT '0.0000' COMMENT '% × HPP',\n  `rate_other_cost_transport` decimal(6,4) NOT NULL DEFAULT '0.0000' COMMENT '% × HPP',\n  `rate_other_cost_finance_admin` decimal(6,4) NOT NULL DEFAULT '0.0000' COMMENT '% × HPP',\n  `rate_other_cost_management_fee` decimal(6,4) NOT NULL DEFAULT '0.0000' COMMENT '% × HPP',\n  `rate_other_cost_interest` decimal(6,4) NOT NULL DEFAULT '0.0000' COMMENT '% × HPP',\n  `rate_other_cost_profit` decimal(6,4) NOT NULL DEFAULT '0.0000' COMMENT '% × HPP',\n  `latest_update` datetime DEFAULT NULL,\n  `updated_by` int DEFAULT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: rate_negotiation_allowance
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rate_negotiation_allowance` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `code_lv2` varchar(50) NOT NULL COMMENT 'Product Category',\n  `rate_negotiation_allowance` decimal(6,4) NOT NULL DEFAULT '0.0000' COMMENT '% dari Bottom Price',\n  `latest_update` datetime DEFAULT NULL,\n  `updated_by` int DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `uk_rna_code_lv2` (`code_lv2`)\n) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: rate_negotiation_allowance_request
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rate_negotiation_allowance_request` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `code_lv2` varchar(50) NOT NULL COMMENT 'Product Category',\n  `rate_negotiation_allowance` decimal(6,4) NOT NULL COMMENT '% diajukan',\n  `status` enum('Menunggu Approval GM','Approved','Rejected') NOT NULL DEFAULT 'Menunggu Approval GM',\n  `submitted_by` int NOT NULL,\n  `submitted_at` datetime NOT NULL,\n  `decided_by` int DEFAULT NULL,\n  `decided_at` datetime DEFAULT NULL,\n  `reject_reason` text,\n  PRIMARY KEY (`id`),\n  KEY `idx_rna_req_code_lv2` (`code_lv2`),\n  KEY `idx_rna_req_status` (`status`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: product_costing_header
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_costing_header` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `code_lv4` varchar(50) NOT NULL,\n  `ukuran_product` decimal(12,4) NOT NULL DEFAULT '1.0000',\n  `material_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `biaya_proses` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `manufacturing_support` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `hpp` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `selling_price` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `latest_update` datetime DEFAULT NULL,\n  `updated_by` int DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `code_lv4` (`code_lv4`)\n) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: product_costing_detail
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_costing_detail` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `id_product_costing_header` int DEFAULT NULL,\n  `code_lv4` varchar(50) NOT NULL,\n  `no_bom` varchar(50) DEFAULT NULL,\n  `no_uniq_id` varchar(50) NOT NULL,\n  `code_lv2` varchar(50) DEFAULT NULL,\n  `ukuran_product` decimal(12,4) NOT NULL DEFAULT '1.0000',\n  `berat_per_meter` decimal(12,4) NOT NULL DEFAULT '0.0000',\n  `material_cost_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `material_cost_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `biaya_proses_man_power_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `biaya_proses_man_power_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `biaya_proses_bahan_pendukung_khusus_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `biaya_proses_bahan_pendukung_khusus_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `biaya_proses_foh_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `biaya_proses_foh_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `biaya_proses_total_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `biaya_proses_total_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `manufacturing_support_consumables_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `manufacturing_support_consumables_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `manufacturing_support_biaya_koordinasi_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `manufacturing_support_biaya_koordinasi_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `manufacturing_support_scrap_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `manufacturing_support_scrap_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `manufacturing_support_total_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `manufacturing_support_total_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `hpp_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `hpp_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_sales_marketing_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_sales_marketing_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_transport_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_transport_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_finance_admin_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_finance_admin_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_management_fee_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_management_fee_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_interest_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_interest_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_profit_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_profit_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_total_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `other_cost_total_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `selling_price_bottom_price_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `selling_price_bottom_price_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `selling_price_negotiation_allowance_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `selling_price_negotiation_allowance_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `selling_price_price_list_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `selling_price_price_list_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `selling_price_price_list_pcs` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `rate_negotiation_allowance` decimal(6,4) NOT NULL DEFAULT '0.0000',\n  `latest_update` datetime DEFAULT NULL,\n  `updated_by` int DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  KEY `idx_pcd_code_lv4` (`code_lv4`),\n  KEY `idx_pcd_uniq` (`no_uniq_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: bottom_price_notification
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bottom_price_notification` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `code_lv4` varchar(50) NOT NULL,\n  `no_uniq_id` varchar(50) NOT NULL,\n  `code_lv2` varchar(50) DEFAULT NULL,\n  `bottom_price_lama_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `bottom_price_lama_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `bottom_price_baru_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `bottom_price_baru_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `rate_negotiation_allowance` decimal(6,4) NOT NULL DEFAULT '0.0000',\n  `price_list_saat_ini_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `price_list_baru_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `status` enum('Belum Ditinjau','Diajukan','Tidak Perlu Diubah') NOT NULL DEFAULT 'Belum Ditinjau',\n  `detected_at` datetime NOT NULL,\n  `reviewed_by` int DEFAULT NULL,\n  `reviewed_at` datetime DEFAULT NULL,\n  `catatan` text,\n  `id_rate_negotiation_allowance_request` int DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  KEY `idx_bpn_status` (`status`),\n  KEY `idx_bpn_prod` (`code_lv4`,`no_uniq_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: pricelist_header
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pricelist_header` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `no_pricelist` varchar(50) NOT NULL,\n  `status` enum('Draft','Menunggu Approval','Approved','Rejected') NOT NULL DEFAULT 'Draft',\n  `created_by` int DEFAULT NULL,\n  `created_at` datetime NOT NULL,\n  `submitted_by` int DEFAULT NULL,\n  `submitted_at` datetime DEFAULT NULL,\n  `decided_by` int DEFAULT NULL,\n  `decided_at` datetime DEFAULT NULL,\n  `reject_reason` text,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `no_pricelist` (`no_pricelist`),\n  KEY `idx_pl_status` (`status`)\n) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: pricelist_detail
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pricelist_detail` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `id_pricelist_header` int NOT NULL,\n  `id_product_costing_detail` int DEFAULT NULL,\n  `code_lv4` varchar(50) NOT NULL,\n  `no_uniq_id` varchar(50) NOT NULL,\n  `code_lv2` varchar(50) DEFAULT NULL,\n  `ukuran_product` decimal(12,4) NOT NULL DEFAULT '1.0000',\n  `berat_per_meter` decimal(12,4) NOT NULL DEFAULT '0.0000',\n  `bottom_price_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `bottom_price_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `rate_negotiation_allowance` decimal(6,4) NOT NULL DEFAULT '0.0000',\n  `price_list_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `price_list_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `price_list_pcs` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  PRIMARY KEY (`id`),\n  KEY `idx_pld_header` (`id_pricelist_header`),\n  KEY `idx_pld_prod` (`code_lv4`,`no_uniq_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: product_catalog_header
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_catalog_header` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `id_pricelist_header` int NOT NULL,\n  `no_catalog` varchar(50) NOT NULL,\n  `status` enum('Active','Archived') NOT NULL DEFAULT 'Active',\n  `released_by` int DEFAULT NULL,\n  `released_at` datetime NOT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `no_catalog` (`no_catalog`)\n) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: product_catalog_detail
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_catalog_detail` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `id_product_catalog_header` int NOT NULL,\n  `id_product_costing_detail` int DEFAULT NULL,\n  `code_lv4` varchar(50) NOT NULL,\n  `no_uniq_id` varchar(50) NOT NULL,\n  `code_lv2` varchar(50) DEFAULT NULL,\n  `ukuran_product` decimal(12,4) NOT NULL DEFAULT '1.0000',\n  `berat_per_meter` decimal(12,4) NOT NULL DEFAULT '0.0000',\n  `price_list_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `price_list_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `price_list_pcs` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  PRIMARY KEY (`id`),\n  KEY `idx_pcd_header` (`id_product_catalog_header`),\n  KEY `idx_pcd_prod` (`code_lv4`,`no_uniq_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: history_costing
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `history_costing` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `batch_id` varchar(50) NOT NULL,\n  `code_lv4` varchar(50) NOT NULL,\n  `no_uniq_id` varchar(50) NOT NULL,\n  `code_lv2` varchar(50) DEFAULT NULL,\n  `changed_at` datetime NOT NULL,\n  `changed_by` int DEFAULT NULL,\n  `change_source` varchar(255) DEFAULT NULL,\n  `change_old_value` text,\n  `change_new_value` text,\n  `bottom_price_lama_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `bottom_price_lama_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `bottom_price_baru_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `bottom_price_baru_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `price_list_lama_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `price_list_baru_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `rate_negotiation_allowance` decimal(6,4) NOT NULL DEFAULT '0.0000',\n  `detail_komponen` longtext,\n  PRIMARY KEY (`id`),\n  KEY `idx_hc_prod` (`code_lv4`,`no_uniq_id`),\n  KEY `idx_hc_time` (`changed_at`)\n) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------------------------
-- Table: history_pricelist
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `history_pricelist` (\n  `id` int NOT NULL AUTO_INCREMENT,\n  `id_pricelist_header` int NOT NULL,\n  `no_pricelist` varchar(50) NOT NULL,\n  `code_lv4` varchar(50) NOT NULL,\n  `no_uniq_id` varchar(50) NOT NULL,\n  `code_lv2` varchar(50) DEFAULT NULL,\n  `event` varchar(50) NOT NULL DEFAULT 'Disetujui',\n  `event_at` datetime NOT NULL,\n  `event_by` int DEFAULT NULL,\n  `price_list_lama_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `price_list_lama_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `price_list_baru_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `price_list_baru_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `bottom_price_meter` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `bottom_price_kg` decimal(18,4) NOT NULL DEFAULT '0.0000',\n  `rate_negotiation_allowance` decimal(6,4) NOT NULL DEFAULT '0.0000',\n  `catatan` text,\n  PRIMARY KEY (`id`),\n  KEY `idx_hp_time` (`event_at`),\n  KEY `idx_hp_prod` (`code_lv4`,`no_uniq_id`)\n) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



-- ------------------------------------------------------------------------------
-- BAGIAN 3: SEED DATA AWAL (PARAMETER & KONSTANTA GLOBAL)
-- ------------------------------------------------------------------------------

-- 3.1 Master Standard Material
INSERT INTO `master_standard_material` (`id`, `standar_toleransi`, `standar_berat_jenis`, `coating`, `latest_update`)
SELECT 1, 0.000000, 7.850000, 0.000000, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `master_standard_material` WHERE `id` = 1);

-- 3.2 Man Power Rate
INSERT INTO `man_power_rate` (`id`, `salary_direct_man_power`, `bpjs`, `biaya_lain_lain`, `total_direct_labor_per_bulan`, `jam_kerja_per_bulan`, `rate_direct_labor`, `porsi_operator`, `rate_1_3_operator`, `latest_update`)
SELECT 1, 4500000.00, 300000.00, 300000.00, 5100000.00, 173.00, 29479.7688, 0.3333, 9827.00, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `man_power_rate` WHERE `id` = 1);

INSERT INTO `man_power_rate_component` (`id_man_power_rate`, `kelompok`, `nama_komponen`, `nilai`, `keterangan`)
SELECT 1, 'salary', 'Gaji Pokok Operator', 4500000.00, 'Gaji pokok bulanan'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `man_power_rate_component` WHERE `id_man_power_rate` = 1 AND `nama_komponen` = 'Gaji Pokok Operator');

-- 3.3 Faktor Indirect Labor
INSERT INTO `faktor_indirect_labor` (`id`, `faktor_indirect_labor`, `latest_update`)
SELECT 1, 1.5000, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `faktor_indirect_labor` WHERE `id` = 1);

-- 3.4 General Rate (9 Elemen Overhead)
INSERT INTO `rate_generals` (`id`, `rate_consumables`, `rate_scrap`, `rate_biaya_koordinasi`, `rate_other_cost_sales_marketing`, `rate_other_cost_transport`, `rate_other_cost_finance_admin`, `rate_other_cost_management_fee`, `rate_other_cost_interest`, `rate_other_cost_profit`, `latest_update`)
SELECT 1, 100.0000, 0.0200, 50.0000, 0.0200, 0.0150, 0.0100, 0.0200, 0.0050, 0.0500, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `rate_generals` WHERE `id` = 1);


-- ------------------------------------------------------------------------------
-- BAGIAN 4: MENU DAN PERMISSIONS (ADMINISTRASI SISTEM)
-- ------------------------------------------------------------------------------

-- 4.1 Menu Induk Product Costing (ID 120)
INSERT INTO `menus` (`id`, `title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`, `created_on`)
SELECT 120, 'Product Costing', '#', 'ti ti-calculator', NULL, 0, 0, 871, 1, 6, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `id` = 120);

-- 4.2 Permissions Baru (1949 s.d. 1982)
INSERT INTO `permissions` (`id_permission`, `nm_permission`, `ket`, `nm_menu`) VALUES
(1949, 'Master_Standard_Material.View', 'View Master Standard Material', 'Master Standard Material'),
(1950, 'Master_Standard_Material.Manage', 'Manage Master Standard Material', 'Master Standard Material'),
(1951, 'Flow_Process.View', 'View Flow Process', 'Flow Process & Cycletime'),
(1952, 'Flow_Process.Add', 'Add Flow Process', 'Flow Process & Cycletime'),
(1953, 'Flow_Process.Manage', 'Manage Flow Process', 'Flow Process & Cycletime'),
(1954, 'Flow_Process.Delete', 'Delete Flow Process', 'Flow Process & Cycletime'),
(1955, 'Faktor_Indirect_Labor.View', 'View Faktor Indirect Labor', 'Faktor Indirect Labor'),
(1956, 'Faktor_Indirect_Labor.Manage', 'Manage Faktor Indirect Labor', 'Faktor Indirect Labor'),
(1957, 'Rate_Borongan_Helper.View', 'View Rate Borongan Helper', 'Rate Borongan Helper'),
(1958, 'Rate_Borongan_Helper.Manage', 'Manage Rate Borongan Helper', 'Rate Borongan Helper'),
(1959, 'Rate_Bahan_Pendukung_FOH.View', 'View Rate BPK & FOH', 'Rate Bahan Pendukung & FOH'),
(1960, 'Rate_Bahan_Pendukung_FOH.Manage', 'Manage Rate BPK & FOH', 'Rate Bahan Pendukung & FOH'),
(1961, 'Rate_Generals.View', 'View General Rate', 'General Rate'),
(1962, 'Rate_Generals.Manage', 'Manage General Rate', 'General Rate'),
(1963, 'Rate_Negotiation_Allowance.View', 'View Negotiation Allowance', 'Negotiation Allowance'),
(1964, 'Rate_Negotiation_Allowance.Manage', 'Manage Negotiation Allowance', 'Negotiation Allowance'),
(1965, 'Approval_Extra_Margin.View', 'View Approval Extra Margin', 'Approval Extra Margin'),
(1966, 'Approval_Extra_Margin.Manage', 'Manage Approval Extra Margin', 'Approval Extra Margin'),
(1967, 'Bottom_Price_Notification.View', 'View Notifikasi Bottom Price', 'Notifikasi Bottom Price'),
(1968, 'Bottom_Price_Notification.Manage', 'Manage Notifikasi Bottom Price', 'Notifikasi Bottom Price'),
(1969, 'Simulasi_Costing.View', 'View Simulasi Costing', 'Simulasi Costing'),
(1970, 'Simulasi_Costing.Manage', 'Manage Simulasi Costing', 'Simulasi Costing'),
(1971, 'Pricelist.View', 'View Pricelist', 'Pricelist'),
(1972, 'Pricelist.Add', 'Add Pricelist', 'Pricelist'),
(1973, 'Pricelist.Manage', 'Manage Pricelist', 'Pricelist'),
(1974, 'Pricelist.Delete', 'Delete Pricelist', 'Pricelist'),
(1975, 'Approval_Pricelist.View', 'View Approval Pricelist', 'Approval Pricelist'),
(1976, 'Approval_Pricelist.Manage', 'Manage Approval Pricelist', 'Approval Pricelist'),
(1977, 'Product_Catalog.View', 'View Product Catalog', 'Product Catalog'),
(1978, 'Product_Catalog.Manage', 'Manage Product Catalog', 'Product Catalog'),
(1979, 'History_Costing.View', 'View History Costing', 'History Costing'),
(1980, 'History_Costing.Manage', 'Manage History Costing', 'History Costing'),
(1981, 'History_Price_List.View', 'View History Price List', 'History Price List'),
(1982, 'History_Price_List.Manage', 'Manage History Price List', 'History Price List')
ON DUPLICATE KEY UPDATE `nm_permission` = VALUES(`nm_permission`);

-- 4.3 Berikan hak akses Administrator (Group ID 1) ke semua permission baru & Price Reference
INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_permission` BETWEEN 1949 AND 1982;
INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`) VALUES (1, 831), (1, 832), (1, 833), (1, 834);

-- 4.4 Daftarkan 15 Sub-menu di bawah Parent 120 (Product Costing)
INSERT INTO `menus` (`id`, `title`, `link`, `icon`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`, `created_on`) VALUES
(532, 'Price Reference', 'price_reference', 'ti ti-tags', 0, 120, 831, 1, 1, NOW()),
(519, 'Faktor Indirect Labor', 'faktor_indirect_labor', 'ti ti-percentage', 0, 120, 1955, 1, 3, NOW()),
(520, 'Rate Borongan Helper', 'rate_borongan_helper', 'ti ti-users', 0, 120, 1957, 1, 2, NOW()),
(521, 'Rate Bahan Pendukung & FOH', 'rate_bahan_pendukung_foh', 'ti ti-package', 0, 120, 1959, 1, 4, NOW()),
(522, 'General Rate', 'rate_generals', 'ti ti-settings', 0, 120, 1961, 1, 5, NOW()),
(523, 'Negotiation Allowance', 'rate_negotiation_allowance', 'ti ti-tag', 0, 120, 1963, 1, 6, NOW()),
(524, 'Approval Extra Margin', 'rate_negotiation_allowance/approval', 'ti ti-checklist', 0, 120, 1965, 1, 7, NOW()),
(518, 'Flow Process & Cycletime', 'flow_process', 'ti ti-route', 0, 120, 1951, 1, 8, NOW()),
(526, 'Simulasi Costing', 'simulasi_costing', 'ti ti-calculator', 0, 120, 1969, 1, 9, NOW()),
(525, 'Notifikasi Bottom Price', 'bottom_price_notification', 'ti ti-bell-ringing', 0, 120, 1967, 1, 10, NOW()),
(527, 'Pricelist', 'pricelist', 'ti ti-file-text', 0, 120, 1971, 1, 11, NOW()),
(528, 'Approval Pricelist', 'pricelist/approval', 'ti ti-checkup-list', 0, 120, 1975, 1, 12, NOW()),
(529, 'Product Catalog', 'product_catalog', 'ti ti-book', 0, 120, 1977, 1, 13, NOW()),
(530, 'History Costing', 'history_costing', 'ti ti-history', 0, 120, 1979, 1, 14, NOW()),
(531, 'History Price List', 'history_pricelist', 'ti ti-file-analytics', 0, 120, 1981, 1, 15, NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `link` = VALUES(`link`), `parent_id` = VALUES(`parent_id`), `order` = VALUES(`order`);

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- MIGRASI SELESAI
-- ==============================================================================
