-- ==============================================================================
-- Migrasi: Hapus Sub-menu Material On Hold di bawah Claim Material
-- File: migrations/011_remove_menu_claim_material_on_hold.sql
-- Tanggal: 11-11-2026
-- Penampung Material On Hold disatukan ke modul stock_from_warehouse (Tab On Hold)
-- ==============================================================================

DELETE FROM `menus` WHERE `link` = 'claim_material/on_hold';

