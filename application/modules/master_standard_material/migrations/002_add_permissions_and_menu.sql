-- =====================================================
-- Migrasi 002: Permission dan menu Master Standard Material
-- Modul   : master_standard_material (FR-50, UC-N15, PG-08)
-- Pola    : modules/spk_material/migrations/002_add_permissions_and_menu.sql
-- =====================================================
-- Skrip aman dijalankan ulang (setiap INSERT diberi pengecekan WHERE NOT EXISTS).
-- Rollback: 002_add_permissions_and_menu.down.sql
--
-- CATATAN:
--  * Sesuaikan parent_id dengan menu induk "Master Data" (atau sejenisnya) dan `order` sesuai hierarki
--    menu yang berlaku. Nilai bawaan di bawah (parent_id = 0, order = 99) hanya penampung awal.
--  * Skrip ini TIDAK menetapkan permission ke group/role. Penetapan dilakukan sendiri di program
--    (User Management > Roles).
-- =====================================================

-- STEP 1: Menu
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
SELECT 'Master Standard Material', 'master_standard_material', 'fa fa-sliders', '_self', 1, 0, 0, 1, 99
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `link` = 'master_standard_material');

SET @menu_id = (SELECT `id` FROM `menus` WHERE `link` = 'master_standard_material' ORDER BY `id` LIMIT 1);

-- STEP 2: Permission
INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Master_Standard_Material.View', @menu_id, 'Master Standard Material', 'View', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Master_Standard_Material.View');

INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Master_Standard_Material.Add', @menu_id, 'Master Standard Material', 'Add', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Master_Standard_Material.Add');

INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Master_Standard_Material.Manage', @menu_id, 'Master Standard Material', 'Manage', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Master_Standard_Material.Manage');

-- STEP 3: Menu memakai permission .View
SET @view_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Master_Standard_Material.View' LIMIT 1);

UPDATE `menus` SET `permission_id` = @view_perm_id WHERE `id` = @menu_id;

-- =====================================================
-- Permission yang tersedia:
--   - Master_Standard_Material.View   : melihat halaman Master Standard Material
--   - Master_Standard_Material.Add    : membuat baris global bila belum ada
--   - Master_Standard_Material.Manage : mengubah nilai standar
-- Controller memakai $this->auth->restrict() pada index() (View) dan save() (Add bila baris belum ada,
-- Manage bila sudah ada).
-- =====================================================
