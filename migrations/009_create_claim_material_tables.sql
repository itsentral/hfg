-- ==============================================================================
-- Migration: 009_create_claim_material_tables.sql
-- Fitur: Claim Material & Bukti Dokumen (FEAT-11, FR-75, FR-94, FR-98)
-- Jalankan pada server production database: db_hfg_dev / middle_live
-- ==============================================================================

-- 1. Tabel Header Claim Material
CREATE TABLE IF NOT EXISTS `claim_material` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `no_claim` VARCHAR(50) NOT NULL COMMENT 'Nomor Claim otomatis (CLM-YYYYMM-XXXX)',
  `production_report_id` INT NOT NULL COMMENT 'FK ke production_reports.id',
  `id_baby_coil` INT DEFAULT NULL COMMENT 'FK ke warehouse_stock_coil.id',
  `id_gudang_material_hold` INT DEFAULT NULL COMMENT 'ID gudang hold',
  `id_suplier` VARCHAR(50) NOT NULL COMMENT 'Kode supplier (SUP...), via tr_ros_header.id_supplier',
  `id_material` VARCHAR(50) NOT NULL COMMENT 'ID Material',
  `no_po` VARCHAR(255) NOT NULL COMMENT 'Nomor PO supplier',
  `no_coil_packing_list` VARCHAR(100) NOT NULL COMMENT 'No. Coil Packing List (warehouse_stock_coil.no_coil)',
  `asal` VARCHAR(30) NOT NULL COMMENT 'Hold Coil / KW 2 (Supplier) / Reject (Supplier)',
  `problem` TEXT COMMENT 'Jenis masalah / cacat',
  `alasan_produksi` TEXT COMMENT 'Alasan detail dari bagian produksi',
  `berat_kg` DECIMAL(18,3) NOT NULL DEFAULT 0.000 COMMENT 'Berat material yang di-claim (Kg)',
  `harga_beli_per_kg` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga beli saat incoming (warehouse_stock_coil.harga_beli)',
  `costbook_per_kg` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Costbook material per Kg gudang asal saat claim dibuat (dasar Nilai Retur)',
  `price_per_unit_po` DECIMAL(18,3) NOT NULL DEFAULT 0.000 COMMENT 'Price/Unit PO per Kg (dt_trans_po.hargasatuan), mata uang PO, saat claim dibuat',
  `tgl_produksi` DATE DEFAULT NULL COMMENT 'Tanggal produksi',
  `status_claim` VARCHAR(30) NOT NULL DEFAULT 'Claim Baru' COMMENT 'Status claim: Claim Baru / Email Terkirim / Kompensasi / Retur / Selesai',
  `tgl_email_terkirim` DATETIME DEFAULT NULL COMMENT 'Waktu email dikirim ke supplier',
  `kesepakatan_supplier` VARCHAR(50) DEFAULT NULL COMMENT 'Kompensasi / Retur / Kompensasi + Retur',
  `created_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT NULL,
  `id_gudang_asal` VARCHAR(50) DEFAULT NULL COMMENT 'Gudang asal coil sebelum hold (untuk release)',
  `kd_gudang_asal` VARCHAR(20) DEFAULT NULL COMMENT 'Kode gudang asal',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_claim_no_claim` (`no_claim`),
  KEY `idx_claim_prod_report` (`production_report_id`),
  KEY `idx_claim_coil` (`no_coil_packing_list`),
  KEY `idx_claim_suplier` (`id_suplier`),
  KEY `idx_claim_po` (`no_po`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Claim Material dari Laporan Produksi (FEAT-11)';

-- 2. Tabel Bukti Dokumen Pendukung Claim
CREATE TABLE IF NOT EXISTS `claim_material_bukti` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_claim_material` INT NOT NULL COMMENT 'FK ke claim_material.id',
  `file_original_name` VARCHAR(255) NOT NULL COMMENT 'Nama berkas asli yang diunggah',
  `file_hash_name` VARCHAR(255) NOT NULL COMMENT 'Nama berkas tersimpan di folder uploads/claim_material/',
  `file_size` INT DEFAULT NULL COMMENT 'Ukuran berkas dalam bytes (maks 5MB = 5242880 bytes)',
  `file_type` VARCHAR(100) DEFAULT NULL COMMENT 'Tipe mime berkas (image/pdf/video)',
  `created_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_bukti_claim` (`id_claim_material`),
  CONSTRAINT `fk_bukti_claim` FOREIGN KEY (`id_claim_material`) REFERENCES `claim_material` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Bukti pendukung claim dari Produksi (FEAT-11)';

-- 3. Tabel Keputusan Purchasing atas Claim (Process Decision)
CREATE TABLE IF NOT EXISTS `claim_material_decision` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_claim_material` INT NOT NULL COMMENT 'FK ke claim_material.id',
  `jalur` VARCHAR(30) NOT NULL COMMENT 'Kompensasi / Retur',
  `aksi` VARCHAR(30) NOT NULL COMMENT 'Debit Note / Jual Material / Retur / Lanjut Produksi',
  `berat_kg` DECIMAL(18,3) NOT NULL DEFAULT 0.000,
  `nilai_debit_note` DECIMAL(18,2) DEFAULT NULL,
  `nilai_material_kw` DECIMAL(18,2) DEFAULT NULL,
  `nilai_biaya_scrap` DECIMAL(18,2) DEFAULT NULL,
  `keterangan` TEXT,
  `created_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dec_claim` (`id_claim_material`),
  CONSTRAINT `fk_dec_claim` FOREIGN KEY (`id_claim_material`) REFERENCES `claim_material` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Process Decision claim (FEAT-11)';

