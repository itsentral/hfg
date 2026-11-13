-- ========================================================================
-- Migration: 015_complete_missing_menu_permissions.sql
-- Melengkapi dan memperbaiki permission View, Add, Manage, Delete untuk menu:
-- 1. Mutasi Bank (Menu 444)
-- 2. Expense (Menu 279)
-- 3. Stok Finish Good (Menu 542)
-- 4. Claim Material (Menu 543)
-- 5. Daftar Claim Material (Menu 545)
-- 6. Material Dijual (Gudang Slitting) (Menu 546)
-- 7. Report Biaya Kompensasi (Menu 547)
-- ========================================================================

-- 1. FIX MAPPING ID_MENU: MUTASI BANK (Menu ID: 444)
UPDATE `permissions` 
SET `id_menu` = 444, `nm_menu` = 'Mutasi Bank' 
WHERE `id_permission` IN (1678, 1679, 1680, 1681);

UPDATE `menus` 
SET `permission_id` = 1678 
WHERE `id` = 444;

-- 2. FIX MAPPING ID_MENU: EXPENSE (Menu ID: 279)
UPDATE `permissions` 
SET `id_menu` = 279, `nm_menu` = 'Expense' 
WHERE `id_permission` IN (955, 956, 957, 958);

UPDATE `menus` 
SET `permission_id` = 955 
WHERE `id` = 279;

-- 3. COMPLETE PERMISSIONS: STOK FINISH GOOD (Menu ID: 542)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`) VALUES
('FG_Stock.Add',    'Add',    542, 'Stok Finish Good', NOW(), 1),
('FG_Stock.Manage', 'Manage', 542, 'Stok Finish Good', NOW(), 1),
('FG_Stock.Delete', 'Delete', 542, 'Stok Finish Good', NOW(), 1);

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 542;

-- 4. COMPLETE PERMISSIONS: CLAIM MATERIAL (Menu ID: 543)
UPDATE `permissions` 
SET `nm_permission` = 'Claim_Material.Add', `ket` = 'Add' 
WHERE `id_permission` = 2054;

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`) VALUES
('Claim_Material.Manage', 'Manage', 543, 'Claim Material', NOW(), 1),
('Claim_Material.Delete', 'Delete', 543, 'Claim Material', NOW(), 1);

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 543;

-- 5. PERMISSIONS: DAFTAR CLAIM MATERIAL (Menu ID: 545)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`) VALUES
('Daftar_Claim_Material.View',   'View',   545, 'Daftar Claim Material', NOW(), 1),
('Daftar_Claim_Material.Add',    'Add',    545, 'Daftar Claim Material', NOW(), 1),
('Daftar_Claim_Material.Manage', 'Manage', 545, 'Daftar Claim Material', NOW(), 1),
('Daftar_Claim_Material.Delete', 'Delete', 545, 'Daftar Claim Material', NOW(), 1);

UPDATE `menus` 
SET `permission_id` = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Daftar_Claim_Material.View' LIMIT 1)
WHERE `id` = 545;

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 545;

-- 6. PERMISSIONS: MATERIAL DIJUAL GUDANG SLITTING (Menu ID: 546)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`) VALUES
('Material_Dijual_Slitting.View',   'View',   546, 'Material Dijual (Gudang Slitting)', NOW(), 1),
('Material_Dijual_Slitting.Add',    'Add',    546, 'Material Dijual (Gudang Slitting)', NOW(), 1),
('Material_Dijual_Slitting.Manage', 'Manage', 546, 'Material Dijual (Gudang Slitting)', NOW(), 1),
('Material_Dijual_Slitting.Delete', 'Delete', 546, 'Material Dijual (Gudang Slitting)', NOW(), 1);

UPDATE `menus` 
SET `permission_id` = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Material_Dijual_Slitting.View' LIMIT 1)
WHERE `id` = 546;

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 546;

-- 7. PERMISSIONS: REPORT BIAYA KOMPENSASI (Menu ID: 547)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`) VALUES
('Report_Biaya_Kompensasi.View',   'View',   547, 'Report Biaya Kompensasi', NOW(), 1),
('Report_Biaya_Kompensasi.Add',    'Add',    547, 'Report Biaya Kompensasi', NOW(), 1),
('Report_Biaya_Kompensasi.Manage', 'Manage', 547, 'Report Biaya Kompensasi', NOW(), 1),
('Report_Biaya_Kompensasi.Delete', 'Delete', 547, 'Report Biaya Kompensasi', NOW(), 1);

UPDATE `menus` 
SET `permission_id` = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Report_Biaya_Kompensasi.View' LIMIT 1)
WHERE `id` = 547;

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 547;
