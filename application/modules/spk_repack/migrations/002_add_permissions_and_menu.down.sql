DELETE gp FROM `group_permissions` gp JOIN `permissions` p ON p.`id_permission` = gp.`id_permission` WHERE p.`nm_permission` IN ('SPK_Repack.View', 'SPK_Repack.Manage');
DELETE FROM `permissions` WHERE `nm_permission` IN ('SPK_Repack.View', 'SPK_Repack.Manage');
DELETE FROM `menus` WHERE `link` = 'spk_repack';
