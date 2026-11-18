-- ==============================================================================
-- Migrasi 010: Penambahan kolom no_spk pada tabel production_reports
-- Kebutuhan: Referensi nomor SPK langsung untuk posting jurnal dan audit log.
-- ==============================================================================
ALTER TABLE `production_reports` 
ADD COLUMN `no_spk` VARCHAR(50) NULL AFTER `id_tr_spk_detail`,
ADD INDEX `idx_production_reports_no_spk` (`no_spk`);

-- Backfill data existing berdasarkan id_tr_spk_detail
UPDATE `production_reports` `pr`
JOIN `tr_spk_material_detail` `d` ON `d`.`id` = `pr`.`id_tr_spk_detail`
SET `pr`.`no_spk` = `d`.`spk_no`
WHERE `pr`.`no_spk` IS NULL OR `pr`.`no_spk` = '';
