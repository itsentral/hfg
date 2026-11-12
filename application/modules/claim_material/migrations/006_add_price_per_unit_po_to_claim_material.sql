-- Migration: tambah kolom price_per_unit_po pada claim_material (FEAT-11 Claim Material)
-- Nama kolom mengikuti specs/docs/diagrams/erd-revisi.drawio. Jalankan manual (tidak otomatis).
-- Butuh kolom costbook_per_kg (005) sudah ada.
-- Price/Unit PO = harga satuan material saat PO (sumber: dt_trans_po.hargasatuan, per Kg, dalam mata uang PO),
-- dasar nilai Debit Note Retur yang ditagihkan ke supplier (keputusan user 2026-10-29).
-- Presisi DECIMAL(18,3) mengikuti dt_trans_po.hargasatuan (double(40,3)).

ALTER TABLE `claim_material`
  ADD COLUMN `price_per_unit_po` DECIMAL(18,3) NOT NULL DEFAULT 0.000
  COMMENT 'Price/Unit PO per Kg (dt_trans_po.hargasatuan), mata uang PO, saat claim dibuat'
  AFTER `costbook_per_kg`;
