-- Migration: tambah kolom costbook_per_kg pada claim_material (FEAT-11 Claim Material)
-- Nama kolom mengikuti specs/docs/diagrams/erd-revisi.drawio. Jalankan manual (tidak otomatis).
-- Butuh tabel `claim_material` (001) sudah ada.
-- Dasar Nilai Retur = Berat (Kg) x Costbook/Kg gudang asal (keputusan user 2026-10-29).
-- harga_beli_per_kg tetap ada (dari warehouse_stock_coil.harga_beli) untuk Nilai Material.

ALTER TABLE `claim_material`
  ADD COLUMN `costbook_per_kg` DECIMAL(18,2) NOT NULL DEFAULT 0.00
  COMMENT 'Costbook Material per Kg gudang asal saat claim dibuat (dasar Nilai Retur)'
  AFTER `harga_beli_per_kg`;
