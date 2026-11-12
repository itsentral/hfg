-- =====================================================
-- Migrasi 002: Permission dan menu Flow Process & Cycletime
-- Modul   : flow_process (FR-42, UC-N10, PG-10)
-- Pola    : modules/master_standard_material/migrations/002_add_permissions_and_menu.sql
-- =====================================================
-- Skrip aman dijalankan ulang (setiap INSERT diberi pengecekan WHERE NOT EXISTS).
-- Rollback: 002_add_permissions_and_menu.down.sql
--
-- CATATAN:
--  * Sesuaikan parent_id dengan menu induk (mis. "Master Data") dan `order` sesuai hierarki menu
--    yang berlaku. Nilai bawaan (parent_id = 0, order = 99) hanya penampung awal.
--  * Skrip ini TIDAK menetapkan permission ke group/role. Penetapan dilakukan sendiri di program
--    (User Management > Roles).
-- =====================================================

-- STEP 1: Menu
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
SELECT 'Flow Process & Cycletime', 'flow_process', 'fa fa-sitemap', '_self', 1, 0, 0, 1, 99
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `link` = 'flow_process');

SET @menu_id = (SELECT `id` FROM `menus` WHERE `link` = 'flow_process' ORDER BY `id` LIMIT 1);

-- STEP 2: Permission
INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Flow_Process.View', @menu_id, 'Flow Process & Cycletime', 'View', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Flow_Process.View');

INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Flow_Process.Add', @menu_id, 'Flow Process & Cycletime', 'Add', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Flow_Process.Add');

INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Flow_Process.Manage', @menu_id, 'Flow Process & Cycletime', 'Manage', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Flow_Process.Manage');

INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`)
SELECT 'Flow_Process.Delete', @menu_id, 'Flow Process & Cycletime', 'Delete', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Flow_Process.Delete');

-- STEP 3: Menu memakai permission .View
SET @view_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Flow_Process.View' LIMIT 1);

UPDATE `menus` SET `permission_id` = @view_perm_id WHERE `id` = @menu_id;

-- =====================================================
-- Permission yang tersedia:
--   - Flow_Process.View   : melihat daftar dan detail flow process
--   - Flow_Process.Add    : menambah flow process untuk Product Category yang belum punya
--   - Flow_Process.Manage : mengubah proses dan nilai cycletime / man power
--   - Flow_Process.Delete : menghapus flow process
-- Controller memakai $this->auth->restrict() pada index() dan form (View; Add/Manage untuk form tambah/ubah),
-- save() (Add bila baru, Manage bila ubah), dan delete() (Delete).
-- =====================================================
