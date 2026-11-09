-- =====================================================
-- Migration: Permissions & Menu untuk Claim Material
-- Module: claim_material
-- =====================================================
-- Jalankan manual. Sesuaikan parent_id (grup menu Claim) dan `order`.
-- Penetapan permission ke group selain group 1 dilakukan lewat User Management.
-- =====================================================

INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`)
VALUES ('Claim Material', 'claim_material', 'fa fa-clipboard-check', '_self', 1, 0, 0, 1, 99);

SET @menu_id = LAST_INSERT_ID();

INSERT INTO `permissions` (`nm_permission`, `id_menu`, `nm_menu`, `ket`, `created_on`) VALUES
('Claim_Material.View',   @menu_id, 'Claim Material', 'View',   NOW()),
('Claim_Material.Manage', @menu_id, 'Claim Material', 'Manage', NOW());

SET @view_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Claim_Material.View' LIMIT 1);
SET @manage_perm_id = (SELECT `id_permission` FROM `permissions` WHERE `nm_permission` = 'Claim_Material.Manage' LIMIT 1);

UPDATE `menus` SET `permission_id` = @view_perm_id WHERE `id` = @menu_id;

INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`) VALUES
(1, @view_perm_id),
(1, @manage_perm_id);
