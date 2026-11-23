-- 001_create_subkont_tables.down.sql  --  membatalkan 001_create_subkont_tables.sql (FEAT-12 Proses Subkont)
-- PERINGATAN: menghapus 14 tabel Subkont beserta SELURUH datanya. Hanya untuk hfg_dev (BUKAN db_hfg_dev). Tidak dieksekusi oleh agen.
-- Urutan kebalikan dari pembuatan (anak dulu, induk belakangan).

DROP TABLE IF EXISTS tr_subkont_incoming_laporan;
DROP TABLE IF EXISTS tr_subkont_incoming;
DROP TABLE IF EXISTS tr_laporan_subkont_revisi;
DROP TABLE IF EXISTS tr_laporan_subkont_fg;
DROP TABLE IF EXISTS tr_laporan_subkont_baby;
DROP TABLE IF EXISTS tr_laporan_subkont_detail;
DROP TABLE IF EXISTS tr_laporan_subkont;
DROP TABLE IF EXISTS ms_vendor_portal_link;
DROP TABLE IF EXISTS subkont_in_supplier_ledger;

-- FK melingkar tampungan <-> delivery_detail: lepas dulu sebelum tabel dihapus
ALTER TABLE tr_subkont_tampungan DROP FOREIGN KEY fk_tamp_dlvdet;
DROP TABLE IF EXISTS tr_subkont_delivery_detail;
DROP TABLE IF EXISTS tr_subkont_delivery;
DROP TABLE IF EXISTS tr_subkont_tampungan;
DROP TABLE IF EXISTS tr_spk_subkont_detail;
DROP TABLE IF EXISTS tr_spk_subkont_header;
