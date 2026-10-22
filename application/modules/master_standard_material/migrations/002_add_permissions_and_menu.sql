-- =====================================================
-- Migrasi: Permission dan menu Master Standard Material
-- Modul   : master_standard_material (FR-50, FR-63, UC-N15)
-- =====================================================

-- STEP 1: Menu di bawah Master Material (parent_id = 143)
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
SELECT 'Master Standard Material', 'master_standard_material', 'fa fa-cogs', '_self', 1, 143, 0, 1, 20
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `link` = 'master_standard_material');

SET @menu_id = (SELECT `id` FROM `menus` WHERE `link` = 'master_standard_material' ORDER BY `id` LIMIT 1);

-- STEP 2: Permission
INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Master_Standard_Material.View', @menu_id, 'Master Standard Material', 'View', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Master_Standard_Material.View');

INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Master_Standard_Material.Manage', @menu_id, 'Master Standard Material', 'Manage', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Master_Standard_Material.Manage');

-- STEP 3: Link menu ke permission View
SET @view_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Master_Standard_Material.View' LIMIT 1);

UPDATE `menus` SET `permission_id` = @view_perm_id WHERE `id` = @menu_id;

-- Berikan permission ini otomatis ke group Administrator (id_group = 1)
INSERT INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, @view_perm_id FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `group_permissions` WHERE `id_group` = 1 AND `id_permission` = @view_perm_id);

SET @manage_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Master_Standard_Material.Manage' LIMIT 1);

INSERT INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, @manage_perm_id FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `group_permissions` WHERE `id_group` = 1 AND `id_permission` = @manage_perm_id);
