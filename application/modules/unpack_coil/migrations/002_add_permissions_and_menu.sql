-- =====================================================
-- Migration: Add Permissions & Menu for Unpack Coil
-- Module: unpack_coil
-- =====================================================
-- Jalankan query ini untuk menambahkan permission dan menu ke sistem.
-- Sesuaikan parent_id dengan hierarki menu yang berlaku (mis. grup Production/Warehouse),
-- dan `order` agar posisi menu sesuai keinginan.
-- =====================================================

-- =====================================================
-- STEP 1: Insert Menu Entry
-- =====================================================
INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
VALUES ('Unpack Coil', 'unpack_coil', 'fa fa-box-open', '_self', 1, 0, 0, 1, 99);

SET @menu_id = LAST_INSERT_ID();

-- =====================================================
-- STEP 2: Insert Permission Entries
-- =====================================================
INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`) VALUES
('Unpack_Coil.View',   @menu_id, 'Unpack Coil', 'View',   NOW()),
('Unpack_Coil.Add',    @menu_id, 'Unpack Coil', 'Add',    NOW()),
('Unpack_Coil.Manage', @menu_id, 'Unpack Coil', 'Manage', NOW());

-- =====================================================
-- STEP 3: Update menu permission_id dengan permission .View
-- =====================================================
SET @view_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Unpack_Coil.View' LIMIT 1);

UPDATE `menus` SET `permission_id` = @view_perm_id WHERE `id` = @menu_id;

-- =====================================================
-- NOTE:
-- Setelah menjalankan migration ini, admin harus assign permission
-- ke role yang sesuai melalui menu User Management > Roles.
--
-- Permission yang tersedia:
--   - Unpack_Coil.View   : Lihat daftar Report Unpack Coil
--   - Unpack_Coil.Add    : Buat Report Unpack Coil baru
--   - Unpack_Coil.Manage : Kelola Report Unpack Coil (delete, dll)
--
-- Controller menggunakan $this->auth->restrict() di setiap method:
--   - index(), data_side(), get_confirmed_packs(), get_pack_baby_detail(), view() -> Unpack_Coil.View
--   - add(), save() -> Unpack_Coil.Add
--   - delete() -> Unpack_Coil.Manage
-- =====================================================
