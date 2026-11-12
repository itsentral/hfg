-- Migration 004: Tambah kolom no_uniq_id pada ms_bom_detail
-- Sesuai Mockup UC-04 (pc_bom.html) di mana No. Uniq ID (Type xxx.x) ada di setiap baris varian material
ALTER TABLE ms_bom_detail 
ADD COLUMN no_uniq_id VARCHAR(50) NULL AFTER id_bom;

-- Backfill data existing
UPDATE ms_bom_detail 
SET no_uniq_id = CONCAT('Type ', LPAD(id_bom, 3, '0'), '.', IF(urut > 0, urut, id)) 
WHERE no_uniq_id IS NULL;
