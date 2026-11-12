-- Migration: alokasi_debit_note (FEAT-11 Claim Material)
-- Nama tabel/kolom mengikuti specs/docs/diagrams/erd-revisi.drawio. Jalankan manual (tidak otomatis).

CREATE TABLE `alokasi_debit_note` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_request_payment` BIGINT UNSIGNED NOT NULL COMMENT 'request_payment.id',
  `id_debit_note` INT NOT NULL,
  `id_payment_approve_details` INT DEFAULT NULL COMMENT 'baris detail negatif saat pembayaran',
  `nilai_alokasi` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_ald_rp` (`id_request_payment`),
  KEY `idx_ald_dn` (`id_debit_note`),
  KEY `idx_ald_pad` (`id_payment_approve_details`),
  CONSTRAINT `fk_ald_dn` FOREIGN KEY (`id_debit_note`) REFERENCES `debit_note` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Alokasi Debit Note ke Request Payment (butuh modul debit_note lebih dulu)';
