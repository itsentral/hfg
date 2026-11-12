DELETE gp FROM `group_permissions` gp JOIN `permissions` p ON p.`id_permission` = gp.`id_permission` WHERE p.`nm_permission` IN ('Debit_Note.View', 'Debit_Note.Manage');
DELETE FROM `permissions` WHERE `nm_permission` IN ('Debit_Note.View', 'Debit_Note.Manage');
DELETE FROM `menus` WHERE `link` = 'debit_note';
