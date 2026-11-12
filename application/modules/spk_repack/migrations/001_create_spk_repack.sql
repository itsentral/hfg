-- Migration: spk_repack (FEAT-11 Claim Material)
-- Nama tabel/kolom mengikuti specs/docs/diagrams/erd-revisi.drawio. Jalankan manual (tidak otomatis).

CREATE TABLE `spk_repack` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `no_spk_repack` VARCHAR(50) NOT NULL COMMENT 'otomatis',
  `tgl_perintah_kerja` DATE DEFAULT NULL,
  `id_suplier` VARCHAR(50) NOT NULL,
  `status_spk` VARCHAR(20) NOT NULL DEFAULT 'WAITING' COMMENT 'WAITING / ON PROGRESS / COMPLETED',
  `confirmed_at` DATETIME DEFAULT NULL,
  `confirmed_by` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_spk_no` (`no_spk_repack`),
  KEY `idx_spk_suplier` (`id_suplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='SPK Re-Pack, satu SPK memuat beberapa material satu supplier';

CREATE TABLE `spk_repack_detail` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_spk_repack` INT NOT NULL,
  `id_claim_material_decision` INT NOT NULL,
  `berat_kg` DECIMAL(18,3) NOT NULL DEFAULT 0.000,
  PRIMARY KEY (`id`),
  KEY `idx_spkd_spk` (`id_spk_repack`),
  KEY `idx_spkd_dec` (`id_claim_material_decision`),
  CONSTRAINT `fk_spkd_spk` FOREIGN KEY (`id_spk_repack`) REFERENCES `spk_repack` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Catatan: butuh tabel `claim_material_decision` (modul claim_material) sudah ada untuk relasi logis id_claim_material_decision.
