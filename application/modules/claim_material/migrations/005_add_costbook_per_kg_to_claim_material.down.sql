-- Rollback costbook_per_kg pada claim_material
ALTER TABLE `claim_material` DROP COLUMN `costbook_per_kg`;
