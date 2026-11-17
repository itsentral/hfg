-- ==============================================================================
-- Migrasi 009: Mapping template jurnal Laporan Produksi (JV016) untuk posting ke gl_interface
-- Dipakai Production_report::save() saat submit final (status_draft = 0).
-- Template JV016 dikelola di aplikasi jurnal (master_oto_jurnal_header / master_oto_jurnal_detail).
-- Aman dijalankan ulang.
-- ==============================================================================
INSERT INTO ms_jurnal_mapping (menu, action, kode_master_jurnal, keterangan, updated_by, updated_on)
SELECT 'production_report', 'save_production_report', 'JV016', 'Jurnal Laporan Produksi', 'system', NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM ms_jurnal_mapping WHERE menu = 'production_report' AND action = 'save_production_report'
);
