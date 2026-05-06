-- =====================================================
-- Migration: New ROS Module
-- Tabel untuk kalkulasi biaya import material
-- =====================================================

-- 1. Header ROS
CREATE TABLE IF NOT EXISTS `tr_ros_header` (
    `id` VARCHAR(30) NOT NULL,
    `id_supplier` VARCHAR(50) DEFAULT NULL,
    `nm_supplier` VARCHAR(255) DEFAULT NULL,
    `no_po` VARCHAR(50) DEFAULT NULL COMMENT 'Single PO number',
    `no_surat` VARCHAR(100) DEFAULT NULL,

    -- Data PIB
    `nilai_po_usd` DECIMAL(18,4) DEFAULT 0 COMMENT 'Nilai PO (U$) CIF/C&F',
    `kurs_pib` DECIMAL(18,2) DEFAULT 0,
    `nilai_po_pib_rp` DECIMAL(18,2) DEFAULT 0 COMMENT 'Nilai PO PIB (Rp)',
    `total_kg_kotor_pib` DECIMAL(18,4) DEFAULT 0,
    `total_kg_bersih_pib` DECIMAL(18,4) DEFAULT 0,

    -- F&C Estimation (dari PIB)
    `cost_bm` DECIMAL(18,2) DEFAULT 0 COMMENT 'Bea Masuk',
    `cost_bm_kite` DECIMAL(18,2) DEFAULT 0,
    `cost_bmt` DECIMAL(18,2) DEFAULT 0,
    `cost_cukai` DECIMAL(18,2) DEFAULT 0,
    `cost_ppn` DECIMAL(18,2) DEFAULT 0,
    `cost_ppnbm` DECIMAL(18,2) DEFAULT 0,
    `cost_pph_import` DECIMAL(18,2) DEFAULT 0,

    -- Biaya LS (dari Surveyor)
    `biaya_ls` DECIMAL(18,2) DEFAULT 0,
    `ppn_ls` DECIMAL(18,2) DEFAULT 0,
    `pph_ls` DECIMAL(18,2) DEFAULT 0,

    -- Insurance
    `insurance` DECIMAL(18,2) DEFAULT 0,

    -- Status & Audit
    `status` TINYINT(1) DEFAULT 0 COMMENT '0=Draft, 1=Final',
    `file_original_name` VARCHAR(255) DEFAULT NULL COMMENT 'Nama asli file packing list yang diupload',
    `file_hash_name` VARCHAR(255) DEFAULT NULL COMMENT 'Nama file terenkripsi di server',
    `created_by` INT(11) DEFAULT NULL,
    `created_on` DATETIME DEFAULT NULL,
    `modified_by` INT(11) DEFAULT NULL,
    `modified_on` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Others Cost (biaya lain-lain dinamis)
CREATE TABLE IF NOT EXISTS `tr_ros_others` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_ros` VARCHAR(30) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT NULL,
    `nilai` DECIMAL(18,2) DEFAULT 0,
    `created_by` INT(11) DEFAULT NULL,
    `created_on` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ros_others_id_ros` (`id_ros`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Material per PO (1 PO bisa banyak material)
CREATE TABLE IF NOT EXISTS `tr_ros_material` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_ros` VARCHAR(30) NOT NULL,
    `id_po_detail` INT(11) DEFAULT NULL COMMENT 'FK ke dt_trans_po.id',
    `id_barang` VARCHAR(50) DEFAULT NULL,
    `nm_barang` VARCHAR(255) DEFAULT NULL COMMENT 'Nama di PO (sistem)',
    `nm_erp` VARCHAR(255) DEFAULT NULL COMMENT 'Nama di ERP (real)',
    `nm_alias` VARCHAR(255) DEFAULT NULL COMMENT 'Nama PO (alias)',
    `kg_unit` DECIMAL(18,4) DEFAULT 0 COMMENT 'Total Kg Unit',
    `unit_price_usd` DECIMAL(18,6) DEFAULT 0 COMMENT 'Unit Price (U$)',
    `total_value_usd` DECIMAL(18,4) DEFAULT 0 COMMENT 'Total Value (U$)',
    `total_value_rp` DECIMAL(18,2) DEFAULT 0 COMMENT 'Total Value Rp = total_value_usd * kurs',
    `bm_persen` DECIMAL(8,2) DEFAULT 0 COMMENT 'BM % dari HS Code',
    `bm_rp` DECIMAL(18,2) DEFAULT 0 COMMENT 'BM (Rp) = total_value_rp * bm_persen',
    `prorate_ls` DECIMAL(18,2) DEFAULT 0,
    `forwarding_cost` DECIMAL(18,2) DEFAULT 0 COMMENT 'Rp 200 * kg_unit',
    `prorate_insurance` DECIMAL(18,2) DEFAULT 0,
    `prorate_others` DECIMAL(18,2) DEFAULT 0,
    `total_nilai_inventory` DECIMAL(18,2) DEFAULT 0,
    `cost_book` DECIMAL(18,4) DEFAULT 0 COMMENT 'total_nilai_inventory / kg_unit',
    `ls_flag` ENUM('YA','TIDAK') DEFAULT 'YA' COMMENT 'Apakah material ini kena LS',
    `created_by` INT(11) DEFAULT NULL,
    `created_on` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ros_material_id_ros` (`id_ros`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Coil per Material
CREATE TABLE IF NOT EXISTS `tr_ros_material_coil` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_ros_material` INT(11) NOT NULL COMMENT 'FK ke tr_ros_material.id',
    `no_coil` VARCHAR(100) DEFAULT NULL,
    `berat_kotor` DECIMAL(18,4) DEFAULT 0,
    `berat_bersih` DECIMAL(18,4) DEFAULT 0,
    `panjang` DECIMAL(18,4) DEFAULT 0 COMMENT 'Length',
    `kode_internal` VARCHAR(100) DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `created_on` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ros_coil_material` (`id_ros_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel sementara untuk review upload packing list sebelum disimpan
CREATE TABLE IF NOT EXISTS `tr_ros_upload_temp` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_ros` VARCHAR(30) NOT NULL,
    `session_id` VARCHAR(100) NOT NULL COMMENT 'Session user untuk isolasi data',
    `no_coil` VARCHAR(100) DEFAULT NULL,
    `nama_sesuai_po` VARCHAR(255) DEFAULT NULL COMMENT 'Key matching ke material',
    `coil_number` INT(5) DEFAULT 1,
    `berat_bersih` DECIMAL(18,4) DEFAULT 0 COMMENT 'N.W.',
    `berat_kotor` DECIMAL(18,4) DEFAULT 0 COMMENT 'G.W.',
    `panjang` DECIMAL(18,4) DEFAULT 0 COMMENT 'Length',
    `bpm` DECIMAL(10,4) DEFAULT 0,
    `id_ros_material` INT(11) DEFAULT NULL COMMENT 'Matched material ID',
    `kode_internal` VARCHAR(100) DEFAULT NULL COMMENT 'Generated kode internal',
    `is_matched` TINYINT(1) DEFAULT 0 COMMENT '1=matched, 0=not matched',
    `created_on` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_temp_ros` (`id_ros`, `session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
