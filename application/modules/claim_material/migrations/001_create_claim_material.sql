-- Migration: claim_material (FEAT-11 Claim Material)
-- Nama tabel/kolom mengikuti specs/docs/diagrams/erd-revisi.drawio. Jalankan manual (tidak otomatis).

CREATE TABLE `claim_material` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `no_claim` VARCHAR(50) NOT NULL COMMENT 'otomatis',
  `production_report_id` INT NOT NULL COMMENT 'production_reports.id',
  `id_baby_coil` INT DEFAULT NULL COMMENT 'warehouse_stock_coil.id (baby_coil di ERD = warehouse_stock_coil)',
  `id_gudang_material_hold` INT DEFAULT NULL,
  `id_suplier` VARCHAR(50) NOT NULL COMMENT 'kode supplier (SUP...), via tr_ros_header.id_supplier',
  `id_material` VARCHAR(50) NOT NULL,
  `no_po` VARCHAR(255) NOT NULL COMMENT 'tr_purchase_order.no_po, via tr_ros_header.no_po',
  `no_coil_packing_list` VARCHAR(100) NOT NULL COMMENT 'warehouse_stock_coil.no_coil',
  `asal` VARCHAR(30) NOT NULL COMMENT 'Hold Coil / KW 2 (Supplier) / Reject (Supplier)',
  `problem` TEXT,
  `alasan_produksi` TEXT,
  `berat_kg` DECIMAL(18,3) NOT NULL DEFAULT 0.000,
  `harga_beli_per_kg` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'warehouse_stock_coil.harga_beli',
  `tgl_produksi` DATE DEFAULT NULL,
  `status_claim` VARCHAR(30) NOT NULL DEFAULT 'Claim Baru',
  `tgl_email_terkirim` DATETIME DEFAULT NULL,
  `kesepakatan_supplier` VARCHAR(50) DEFAULT NULL COMMENT 'Kompensasi / Retur / Kompensasi + Retur',
  `created_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT NULL,
  `id_gudang_asal` VARCHAR(50) DEFAULT NULL COMMENT 'gudang coil sebelum dipindah ke Hold (dipakai saat Release)',
  `kd_gudang_asal` VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_claim_no_claim` (`no_claim`),
  KEY `idx_claim_prod_report` (`production_report_id`),
  KEY `idx_claim_coil` (`no_coil_packing_list`),
  KEY `idx_claim_suplier` (`id_suplier`),
  KEY `idx_claim_po` (`no_po`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Claim material (FEAT-11)';

CREATE TABLE `claim_material_decision` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_claim_material` INT NOT NULL,
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
  CONSTRAINT `fk_dec_claim` FOREIGN KEY (`id_claim_material`) REFERENCES `claim_material` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Process Decision claim (bisa dua jalur per claim)';

CREATE TABLE `stok_gudang_slitting` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_claim_material_decision` INT NOT NULL,
  `id_material` VARCHAR(50) NOT NULL,
  `berat_kg` DECIMAL(18,3) NOT NULL DEFAULT 0.000,
  `nilai_material` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `nilai_material_kw` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `nilai_biaya_scrap` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `tgl_masuk` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_slit_dec` (`id_claim_material_decision`),
  CONSTRAINT `fk_slit_dec` FOREIGN KEY (`id_claim_material_decision`) REFERENCES `claim_material_decision` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Gudang Slitting: material hasil Jual Material (daftar baca)';
