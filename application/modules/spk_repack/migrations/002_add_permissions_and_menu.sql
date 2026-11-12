-- =====================================================
-- Migration: Permissions & Menu untuk SPK Re-Pack
-- Module: spk_repack
-- =====================================================
-- Jalankan manual. Sesuaikan parent_id (grup menu Claim) dan `order`.
-- Penetapan permission ke group selain group 1 dilakukan lewat User Management.
-- =====================================================

INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
VALUES ('SPK Re-Pack', 'spk_repack', 'fa fa-box', '_self', 1, 0, 0, 1, 99);

SET @menu_id = LAST_INSERT_ID();

INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`) VALUES
('SPK_Repack.View',   @menu_id, 'SPK Re-Pack', 'View',   NOW()),
('SPK_Repack.Manage', @menu_id, 'SPK Re-Pack', 'Manage', NOW());

SET @view_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'SPK_Repack.View' LIMIT 1);
SET @manage_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'SPK_Repack.Manage' LIMIT 1);

UPDATE `menus` SET `permission_id` = @view_perm_id WHERE `id` = @menu_id;

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`) VALUES
(1, @view_perm_id),
(1, @manage_perm_id);
