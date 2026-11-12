-- Rollback price_per_unit_po pada claim_material
ALTER TABLE `claim_material` DROP COLUMN `price_per_unit_po`;
