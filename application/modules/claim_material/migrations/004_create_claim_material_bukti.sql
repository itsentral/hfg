-- Migration: claim_material_bukti (FEAT-11 Claim Material, FR-94)
-- Nama tabel/kolom mengikuti specs/docs/diagrams/erd-revisi.drawio. Jalankan manual (tidak otomatis).
-- Butuh tabel `claim_material` (001) sudah ada.
-- Bukti dokumen pendukung dari Produksi saat Hold & Claim; tidak boleh dihapus selama claim belum selesai.

CREATE TABLE `claim_material_bukti` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_claim_material` INT NOT NULL,
  `file_original_name` VARCHAR(255) NOT NULL COMMENT 'nama berkas asli yang diunggah',
  `file_hash_name` VARCHAR(255) NOT NULL COMMENT 'nama berkas tersimpan di server',
  `created_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_bukti_claim` (`id_claim_material`),
  CONSTRAINT `fk_bukti_claim` FOREIGN KEY (`id_claim_material`) REFERENCES `claim_material` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Bukti pendukung claim dari Produksi (FEAT-11)';
