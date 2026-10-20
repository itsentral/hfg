-- =====================================================
-- Rollback migrasi 001: hapus tabel Flow Process & Cycletime
-- PERINGATAN: menghapus seluruh data flow process. Jalankan hanya di database uji atau setelah
-- memastikan tidak ada perhitungan costing yang bergantung pada data ini.
-- =====================================================

DROP TABLE IF EXISTS flow_process_detail;
DROP TABLE IF EXISTS flow_process_header;
