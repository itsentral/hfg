-- Migration: penerimaan_uang_supplier (FEAT-11 Claim Material)
-- Nama tabel/kolom mengikuti specs/docs/diagrams/erd-revisi.drawio. Jalankan manual (tidak otomatis).

CREATE TABLE `penerimaan_uang_supplier` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_debit_note` INT NOT NULL,
  `tgl_diterima` DATE NOT NULL,
  `rekening_penerima` VARCHAR(100) NOT NULL,
  `nominal_diterima` DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'harus sama dengan nilai Debit Note',
  `bukti_transfer` VARCHAR(255) DEFAULT NULL,
  `catatan` TEXT,
  `created_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pu_dn` (`id_debit_note`),
  CONSTRAINT `fk_pu_dn` FOREIGN KEY (`id_debit_note`) REFERENCES `debit_note` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Penerimaan uang dari supplier atas Debit Note (butuh modul debit_note lebih dulu)';
