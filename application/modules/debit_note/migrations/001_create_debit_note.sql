-- Migration: debit_note (FEAT-11 Claim Material)
-- Nama tabel/kolom mengikuti specs/docs/diagrams/erd-revisi.drawio. Jalankan manual (tidak otomatis).

CREATE TABLE `debit_note` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `no_debit_note` VARCHAR(50) NOT NULL COMMENT 'otomatis',
  `tgl_terbit` DATE DEFAULT NULL,
  `id_suplier` VARCHAR(50) NOT NULL,
  `id_kurs` INT NOT NULL COMMENT 'mata_uang.id_kurs',
  `kurs` DECIMAL(18,2) NOT NULL DEFAULT 1.00,
  `total_nilai` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'dalam mata uang PO',
  `total_nilai_idr` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `jalur` VARCHAR(30) NOT NULL COMMENT 'Kompensasi / Retur',
  `asal` VARCHAR(30) NOT NULL COMMENT 'dari claim_material.asal (menentukan akun kredit)',
  `status_dokumen` VARCHAR(40) NOT NULL DEFAULT 'DN Belum Terbit',
  `id_claim_material` INT NOT NULL,
  `id_claim_material_decision` INT NOT NULL,
  `id_surat_jalan_retur` INT DEFAULT NULL,
  `tgl_penagihan` DATE DEFAULT NULL,
  `tgl_jatuh_tempo_tagihan` DATE DEFAULT NULL,
  `catatan_tagihan` TEXT,
  `created_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dn_no` (`no_debit_note`),
  KEY `idx_dn_suplier` (`id_suplier`),
  KEY `idx_dn_kurs` (`id_kurs`),
  KEY `idx_dn_claim` (`id_claim_material`),
  KEY `idx_dn_dec` (`id_claim_material_decision`),
  KEY `idx_dn_sj` (`id_surat_jalan_retur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Debit Note supplier (FEAT-11)';
