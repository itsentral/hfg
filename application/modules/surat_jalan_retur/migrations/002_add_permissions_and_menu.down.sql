DELETE gp FROM `group_permissions` gp JOIN `permissions` p ON p.`id_permission` = gp.`id_permission` WHERE p.`nm_permission` IN ('Surat_Jalan_Retur.View', 'Surat_Jalan_Retur.Manage');
DELETE FROM `permissions` WHERE `nm_permission` IN ('Surat_Jalan_Retur.View', 'Surat_Jalan_Retur.Manage');
DELETE FROM `menus` WHERE `link` = 'surat_jalan_retur';
