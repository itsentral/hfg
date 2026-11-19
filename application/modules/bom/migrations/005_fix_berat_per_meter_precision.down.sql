-- Rollback migrasi 005. Kembali ke DECIMAL(10,2) NULL (presisi 2 desimal; nilai 4+ desimal akan dibulatkan).
-- Nilai NULL asli dapat dipulihkan dari bak_ms_bom_detail_berat_per_meter bila tabel itu masih ada.
ALTER TABLE ms_bom_detail MODIFY berat_per_meter DECIMAL(10,2) NULL DEFAULT NULL;
UPDATE ms_bom_detail d JOIN bak_ms_bom_detail_berat_per_meter b ON b.id = d.id
   SET d.berat_per_meter = b.berat_per_meter WHERE b.berat_per_meter IS NULL;
