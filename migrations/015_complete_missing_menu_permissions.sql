-- ========================================================================
-- Migration: 015_complete_missing_menu_permissions.sql
-- Melengkapi dan memperbaiki permission View, Add, Manage, Delete untuk menu:
-- 1. Mutasi Bank (Menu 444)
-- 2. Expense Submenu (Menu 279)
-- 3. Expense Header Folder (Menu 282)
-- 4. Stok Finish Good (Menu 542)
-- 5. Claim Material Folder (Menu 543)
-- 6. Daftar Claim Material (Menu 545)
-- 7. Material Dijual (Gudang Slitting) (Menu 546)
-- 8. Report Biaya Kompensasi (Menu 547)
-- ========================================================================

-- STEP 1: HAPUS SEMUA DUPLIKAT PERMISSION DI SERVER/DB JIKA ADA
DELETE gp FROM `group_permissions` gp
INNER JOIN `permissions` p ON p.`id_permission` = gp.`id_permission`
WHERE p.`id_permission` IN (
    SELECT `id_permission` FROM (
        SELECT p1.`id_permission`
        FROM `permissions` p1
        INNER JOIN `permissions` p2 
            ON p1.`nm_permission` = p2.`nm_permission` 
            AND p1.`id_menu` = p2.`id_menu` 
            AND p1.`id_permission` > p2.`id_permission`
    ) t
);

DELETE up FROM `user_permissions` up
INNER JOIN `permissions` p ON p.`id_permission` = up.`id_permission`
WHERE p.`id_permission` IN (
    SELECT `id_permission` FROM (
        SELECT p1.`id_permission`
        FROM `permissions` p1
        INNER JOIN `permissions` p2 
            ON p1.`nm_permission` = p2.`nm_permission` 
            AND p1.`id_menu` = p2.`id_menu` 
            AND p1.`id_permission` > p2.`id_permission`
    ) t
);

DELETE FROM `permissions` 
WHERE `id_permission` IN (
    SELECT `id_permission` FROM (
        SELECT p1.`id_permission`
        FROM `permissions` p1
        INNER JOIN `permissions` p2 
            ON p1.`nm_permission` = p2.`nm_permission` 
            AND p1.`id_menu` = p2.`id_menu` 
            AND p1.`id_permission` > p2.`id_permission`
    ) t
);

-- STEP 2: FIX MAPPING MUTASI BANK (Menu ID: 444)
UPDATE `permissions` 
SET `id_menu` = 444, `nm_menu` = 'Mutasi Bank' 
WHERE `id_permission` IN (1678, 1679, 1680, 1681);

UPDATE `menus` 
SET `permission_id` = 1678 
WHERE `id` = 444;

-- STEP 3: FIX MAPPING EXPENSE SUBMENU (Menu ID: 279)
UPDATE `permissions` 
SET `id_menu` = 279, `nm_menu` = 'Expense' 
WHERE `id_permission` IN (955, 956, 957, 958);

UPDATE `menus` 
SET `permission_id` = 955 
WHERE `id` = 279;

-- STEP 4: PERMISSIONS EXPENSE HEADER MENU (Menu ID: 282)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Menu_Expense.View', 'View', 282, 'Expense', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Menu_Expense.View' AND `id_menu` = 282);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Menu_Expense.Add', 'Add', 282, 'Expense', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Menu_Expense.Add' AND `id_menu` = 282);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Menu_Expense.Manage', 'Manage', 282, 'Expense', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Menu_Expense.Manage' AND `id_menu` = 282);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Menu_Expense.Delete', 'Delete', 282, 'Expense', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Menu_Expense.Delete' AND `id_menu` = 282);

UPDATE `menus` 
SET `permission_id` = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Menu_Expense.View' LIMIT 1)
WHERE `id` = 282;

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 282;

-- STEP 5: PERMISSIONS STOK FINISH GOOD (Menu ID: 542)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'FG_Stock.Add', 'Add', 542, 'Stok Finish Good', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'FG_Stock.Add' AND `id_menu` = 542);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'FG_Stock.Manage', 'Manage', 542, 'Stok Finish Good', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'FG_Stock.Manage' AND `id_menu` = 542);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'FG_Stock.Delete', 'Delete', 542, 'Stok Finish Good', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'FG_Stock.Delete' AND `id_menu` = 542);

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 542;

-- STEP 6: PERMISSIONS CLAIM MATERIAL FOLDER (Menu ID: 543)
UPDATE `permissions` 
SET `nm_permission` = 'Claim_Material.Add', `ket` = 'Add' 
WHERE `id_permission` = 2054;

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Claim_Material.Manage', 'Manage', 543, 'Claim Material', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Claim_Material.Manage' AND `id_menu` = 543);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Claim_Material.Delete', 'Delete', 543, 'Claim Material', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Claim_Material.Delete' AND `id_menu` = 543);

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 543;

-- STEP 7: PERMISSIONS DAFTAR CLAIM MATERIAL (Menu ID: 545)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Daftar_Claim_Material.View', 'View', 545, 'Daftar Claim Material', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Daftar_Claim_Material.View' AND `id_menu` = 545);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Daftar_Claim_Material.Add', 'Add', 545, 'Daftar Claim Material', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Daftar_Claim_Material.Add' AND `id_menu` = 545);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Daftar_Claim_Material.Manage', 'Manage', 545, 'Daftar Claim Material', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Daftar_Claim_Material.Manage' AND `id_menu` = 545);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Daftar_Claim_Material.Delete', 'Delete', 545, 'Daftar Claim Material', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Daftar_Claim_Material.Delete' AND `id_menu` = 545);

UPDATE `menus` 
SET `permission_id` = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Daftar_Claim_Material.View' LIMIT 1)
WHERE `id` = 545;

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 545;

-- STEP 8: PERMISSIONS MATERIAL DIJUAL GUDANG SLITTING (Menu ID: 546)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Material_Dijual_Slitting.View', 'View', 546, 'Material Dijual (Gudang Slitting)', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Material_Dijual_Slitting.View' AND `id_menu` = 546);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Material_Dijual_Slitting.Add', 'Add', 546, 'Material Dijual (Gudang Slitting)', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Material_Dijual_Slitting.Add' AND `id_menu` = 546);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Material_Dijual_Slitting.Manage', 'Manage', 546, 'Material Dijual (Gudang Slitting)', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Material_Dijual_Slitting.Manage' AND `id_menu` = 546);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Material_Dijual_Slitting.Delete', 'Delete', 546, 'Material Dijual (Gudang Slitting)', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Material_Dijual_Slitting.Delete' AND `id_menu` = 546);

UPDATE `menus` 
SET `permission_id` = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Material_Dijual_Slitting.View' LIMIT 1)
WHERE `id` = 546;

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 546;

-- STEP 9: PERMISSIONS REPORT BIAYA KOMPENSASI (Menu ID: 547)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Report_Biaya_Kompensasi.View', 'View', 547, 'Report Biaya Kompensasi', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Report_Biaya_Kompensasi.View' AND `id_menu` = 547);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Report_Biaya_Kompensasi.Add', 'Add', 547, 'Report Biaya Kompensasi', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Report_Biaya_Kompensasi.Add' AND `id_menu` = 547);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Report_Biaya_Kompensasi.Manage', 'Manage', 547, 'Report Biaya Kompensasi', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Report_Biaya_Kompensasi.Manage' AND `id_menu` = 547);

INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`)
SELECT 'Report_Biaya_Kompensasi.Delete', 'Delete', 547, 'Report Biaya Kompensasi', NOW(), 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Report_Biaya_Kompensasi.Delete' AND `id_menu` = 547);

UPDATE `menus` 
SET `permission_id` = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Report_Biaya_Kompensasi.View' LIMIT 1)
WHERE `id` = 547;

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, `id_permission` FROM `permissions` WHERE `id_menu` = 547;
