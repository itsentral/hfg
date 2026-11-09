DELETE gp FROM `group_permissions` gp JOIN `permissions` p ON p.`id_permission` = gp.`id_permission` WHERE p.`nm_permission` IN ('Claim_Material.View', 'Claim_Material.Manage');
DELETE FROM `permissions` WHERE `nm_permission` IN ('Claim_Material.View', 'Claim_Material.Manage');
DELETE FROM `menus` WHERE `link` = 'claim_material';
