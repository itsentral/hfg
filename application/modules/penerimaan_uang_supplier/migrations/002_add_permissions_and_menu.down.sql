DELETE gp FROM `group_permissions` gp JOIN `permissions` p ON p.`id_permission` = gp.`id_permission` WHERE p.`nm_permission` IN ('Penerimaan_Uang_Supplier.View', 'Penerimaan_Uang_Supplier.Manage');
DELETE FROM `permissions` WHERE `nm_permission` IN ('Penerimaan_Uang_Supplier.View', 'Penerimaan_Uang_Supplier.Manage');
DELETE FROM `menus` WHERE `link` = 'penerimaan_uang_supplier';
