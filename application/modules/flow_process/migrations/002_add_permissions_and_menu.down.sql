-- =====================================================
-- Rollback migrasi 002: hapus permission dan menu Flow Process & Cycletime
-- PERINGATAN: bila permission sudah ditetapkan ke group/role, hapus dulu penetapannya
-- (baris pada group_permissions / user_permissions yang merujuk id_permission di bawah) melalui
-- User Management, agar tidak tertinggal referensi yatim.
-- =====================================================

DELETE FROM `permissions`
WHERE `nm_permission` IN (
    'Flow_Process.View',
    'Flow_Process.Add',
    'Flow_Process.Manage',
    'Flow_Process.Delete'
);

DELETE FROM `menus` WHERE `link` = 'flow_process';
