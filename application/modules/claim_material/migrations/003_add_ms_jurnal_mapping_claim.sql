-- Migration: baris Master Jurnal untuk menu Claim Material (FR-93)
-- kode_master_jurnal sengaja KOSONG: diisi akuntansi setelah Master Jurnal Claim dibuat.
-- Tanpa fallback: bila kosong, jurnal ditandai gagal dan dicatat di log (keputusan user 2026-10-28, D4).
INSERT INTO `ms_jurnal_mapping` (`menu`, `action`, `kode_master_jurnal`, `keterangan`) VALUES
('Claim Material', 'jual_material',       '', 'Jual Material: Db Material KW + Db Biaya Scrap / Cr Gudang Bahan Persediaan Produksi'),
('Claim Material', 'terbit_dn_kompensasi', '', 'Terbit DN Kompensasi: Db Debit Note (Kompensasi) / Cr Biaya KW 2 Supplier atau Biaya Scrap'),
('Claim Material', 'create_sj_retur',     '', 'Create Surat Jalan Retur: Db In Transit / Cr Inventory'),
('Claim Material', 'confirm_sj_retur',    '', 'Confirm Surat Jalan Retur: Db Debit Note (Retur) / Cr In Transit'),
('Claim Material', 'bayar_po_dn',         '', 'Pembayaran PO dengan DN: Db Hutang / Cr Bank, Cr DN Kompensasi, Cr DN Retur (+admin)'),
('Claim Material', 'terima_uang',         '', 'Penerimaan uang: Db Bank / Cr Debit Note');
