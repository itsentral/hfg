-- Migration: surat_jalan_retur (FEAT-11 Claim Material)
-- Nama tabel/kolom mengikuti specs/docs/diagrams/erd-revisi.drawio. Jalankan manual (tidak otomatis).

CREATE TABLE `surat_jalan_retur` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `no_surat_jalan` VARCHAR(50) NOT NULL COMMENT 'otomatis',
  `id_spk_repack` INT NOT NULL COMMENT 'satu SPK = satu Surat Jalan',
  `tgl_surat_jalan` DATE DEFAULT NULL,
  `id_suplier` VARCHAR(50) NOT NULL,
  `alamat` TEXT,
  `driver` VARCHAR(100) DEFAULT NULL,
  `no_polisi_truk` VARCHAR(20) DEFAULT NULL,
  `nilai_retur` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'jumlah nilai semua baris SPK, tanpa PPN',
  `status_surat_jalan` VARCHAR(20) NOT NULL DEFAULT 'WAITING SJ' COMMENT 'WAITING SJ / NOT CONFIRMED / SJ CONFIRMED',
  `confirmed_at` DATETIME DEFAULT NULL,
  `confirmed_by` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sj_no` (`no_surat_jalan`),
  UNIQUE KEY `uq_sj_spk` (`id_spk_repack`),
  KEY `idx_sj_suplier` (`id_suplier`),
  CONSTRAINT `fk_sj_spk` FOREIGN KEY (`id_spk_repack`) REFERENCES `spk_repack` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Surat Jalan Retur ke supplier (butuh modul spk_repack lebih dulu)';
