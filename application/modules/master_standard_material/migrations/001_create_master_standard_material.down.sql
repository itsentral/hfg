-- =====================================================
-- Rollback migrasi 001: hapus tabel Master Standard Material
-- PERINGATAN: menghapus seluruh isi tabel (nilai standar). Jalankan hanya di database uji
-- atau setelah memastikan tidak ada BOM yang bergantung pada nilai ini.
-- =====================================================

DROP TABLE IF EXISTS master_standard_material;
