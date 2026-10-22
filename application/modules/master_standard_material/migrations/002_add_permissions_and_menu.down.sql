-- =====================================================
-- Rollback migrasi 002: hapus permission dan menu Master Standard Material
-- PERINGATAN: bila permission sudah ditetapkan ke group/role, hapus dulu penetapannya
-- (baris pada group_permissions / user_permissions yang merujuk id_permission di bawah) melalui
-- User Management, agar tidak tertinggal referensi yatim.
-- =====================================================

DELETE FROM `permissions`
WHERE `nm_permission` IN (
    'Master_Standard_Material.View',
    'Master_Standard_Material.Add',
    'Master_Standard_Material.Manage'
);

DELETE FROM `menus` WHERE `link` = 'master_standard_material';
