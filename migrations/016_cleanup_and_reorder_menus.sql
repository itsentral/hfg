-- ============================================================================
-- MIGRATION 016: PEMBERSIHAN MENU, PENATAAN URUTAN & SINKRONISASI PERMISSIONS
-- Tanggal: 13-11-2026
-- Database Target: db_hfg_dev (Local & Live)
-- ============================================================================

-- 1. PERBAIKAN TITLE MENU (MENGHILANGKAN WHITESPACE / TYPO)
UPDATE `menus` SET `title` = 'Master Sound' WHERE `id` = 493;
UPDATE `menus` SET `title` = 'Asset COA' WHERE `id` = 501;
UPDATE `menus` SET `title` = 'Pricelist' WHERE `id` = 530;
UPDATE `menus` SET `title` = 'Finish Good Warehouse' WHERE `id` = 542;

-- 2. SINKRONISASI NAMA PERMISSION AGAR SAMA PERSIS DENGAN KODE CONTROLLER
UPDATE `permissions` SET `nm_permission` = 'Users.View' WHERE `nm_permission` = 'user.View' AND `id_menu` = 3;
UPDATE `permissions` SET `nm_permission` = 'Users.Add' WHERE `nm_permission` = 'user.Add' AND `id_menu` = 3;
UPDATE `permissions` SET `nm_permission` = 'Users.Manage' WHERE `nm_permission` = 'user.Manage' AND `id_menu` = 3;
UPDATE `permissions` SET `nm_permission` = 'Users.Delete' WHERE `nm_permission` = 'user.Delete' AND `id_menu` = 3;
UPDATE `permissions` SET `nm_permission` = 'Asset_coa.View' WHERE `nm_permission` = 'Asset_coa_.View' AND `id_menu` = 501;
UPDATE `permissions` SET `nm_permission` = 'Asset_coa.Add' WHERE `nm_permission` = 'Asset_coa_.Add' AND `id_menu` = 501;
UPDATE `permissions` SET `nm_permission` = 'Asset_coa.Manage' WHERE `nm_permission` = 'Asset_coa_.Manage' AND `id_menu` = 501;
UPDATE `permissions` SET `nm_permission` = 'Asset_coa.Delete' WHERE `nm_permission` = 'Asset_coa_.Delete' AND `id_menu` = 501;
UPDATE `permissions` SET `nm_permission` = 'Pricelist.View' WHERE `nm_permission` = 'Pricelist_.View' AND `id_menu` = 530;
UPDATE `permissions` SET `nm_permission` = 'Pricelist.Add' WHERE `nm_permission` = 'Pricelist_.Add' AND `id_menu` = 530;
UPDATE `permissions` SET `nm_permission` = 'Pricelist.Manage' WHERE `nm_permission` = 'Pricelist_.Manage' AND `id_menu` = 530;
UPDATE `permissions` SET `nm_permission` = 'Pricelist.Delete' WHERE `nm_permission` = 'Pricelist_.Delete' AND `id_menu` = 530;
UPDATE `permissions` SET `nm_permission` = 'Master_sound.View' WHERE `nm_permission` = 'Master__Sound.View' AND `id_menu` = 493;
UPDATE `permissions` SET `nm_permission` = 'Master_sound.Add' WHERE `nm_permission` = 'Master__Sound.Add' AND `id_menu` = 493;
UPDATE `permissions` SET `nm_permission` = 'Master_sound.Manage' WHERE `nm_permission` = 'Master__Sound.Manage' AND `id_menu` = 493;
UPDATE `permissions` SET `nm_permission` = 'Master_sound.Delete' WHERE `nm_permission` = 'Master__Sound.Delete' AND `id_menu` = 493;
UPDATE `permissions` SET `nm_permission` = 'Asset_depreciation.View' WHERE `nm_permission` = 'Depreciation.View' AND `id_menu` = 502;
UPDATE `permissions` SET `nm_permission` = 'Asset_depreciation.Add' WHERE `nm_permission` = 'Depreciation.Add' AND `id_menu` = 502;
UPDATE `permissions` SET `nm_permission` = 'Asset_depreciation.Manage' WHERE `nm_permission` = 'Depreciation.Manage' AND `id_menu` = 502;
UPDATE `permissions` SET `nm_permission` = 'Asset_depreciation.Delete' WHERE `nm_permission` = 'Depreciation.Delete' AND `id_menu` = 502;
UPDATE `permissions` SET `nm_permission` = 'Report_Monitoring_PO.View' WHERE `nm_permission` = 'Report_Monitoring.View' AND `id_menu` = 515;
UPDATE `permissions` SET `nm_permission` = 'Report_Monitoring_PO.Add' WHERE `nm_permission` = 'Report_Monitoring.Add' AND `id_menu` = 515;
UPDATE `permissions` SET `nm_permission` = 'Report_Monitoring_PO.Manage' WHERE `nm_permission` = 'Report_Monitoring.Manage' AND `id_menu` = 515;
UPDATE `permissions` SET `nm_permission` = 'Report_Monitoring_PO.Delete' WHERE `nm_permission` = 'Report_Monitoring.Delete' AND `id_menu` = 515;
UPDATE `permissions` SET `nm_permission` = 'Rate_Bahan_Pendukung_FOH.View' WHERE `nm_permission` = 'Rate_Bahan_Pendukung_&_FOH.View' AND `id_menu` = 523;
UPDATE `permissions` SET `nm_permission` = 'Rate_Bahan_Pendukung_FOH.Add' WHERE `nm_permission` = 'Rate_Bahan_Pendukung_&_FOH.Add' AND `id_menu` = 523;
UPDATE `permissions` SET `nm_permission` = 'Rate_Bahan_Pendukung_FOH.Manage' WHERE `nm_permission` = 'Rate_Bahan_Pendukung_&_FOH.Manage' AND `id_menu` = 523;
UPDATE `permissions` SET `nm_permission` = 'Rate_Bahan_Pendukung_FOH.Delete' WHERE `nm_permission` = 'Rate_Bahan_Pendukung_&_FOH.Delete' AND `id_menu` = 523;
UPDATE `permissions` SET `nm_permission` = 'Rate_Generals.View' WHERE `nm_permission` = 'General_Rate.View' AND `id_menu` = 524;
UPDATE `permissions` SET `nm_permission` = 'Rate_Generals.Add' WHERE `nm_permission` = 'General_Rate.Add' AND `id_menu` = 524;
UPDATE `permissions` SET `nm_permission` = 'Rate_Generals.Manage' WHERE `nm_permission` = 'General_Rate.Manage' AND `id_menu` = 524;
UPDATE `permissions` SET `nm_permission` = 'Rate_Generals.Delete' WHERE `nm_permission` = 'General_Rate.Delete' AND `id_menu` = 524;
UPDATE `permissions` SET `nm_permission` = 'Rate_Negotiation_Allowance.View' WHERE `nm_permission` = 'Negotiation_Allowance.View' AND `id_menu` = 525;
UPDATE `permissions` SET `nm_permission` = 'Rate_Negotiation_Allowance.Add' WHERE `nm_permission` = 'Negotiation_Allowance.Add' AND `id_menu` = 525;
UPDATE `permissions` SET `nm_permission` = 'Rate_Negotiation_Allowance.Manage' WHERE `nm_permission` = 'Negotiation_Allowance.Manage' AND `id_menu` = 525;
UPDATE `permissions` SET `nm_permission` = 'Rate_Negotiation_Allowance.Delete' WHERE `nm_permission` = 'Negotiation_Allowance.Delete' AND `id_menu` = 525;
UPDATE `permissions` SET `nm_permission` = 'History_Pricelist.View' WHERE `nm_permission` = 'History_Price_List.View' AND `id_menu` = 534;
UPDATE `permissions` SET `nm_permission` = 'History_Pricelist.Add' WHERE `nm_permission` = 'History_Price_List.Add' AND `id_menu` = 534;
UPDATE `permissions` SET `nm_permission` = 'History_Pricelist.Manage' WHERE `nm_permission` = 'History_Price_List.Manage' AND `id_menu` = 534;
UPDATE `permissions` SET `nm_permission` = 'History_Pricelist.Delete' WHERE `nm_permission` = 'History_Price_List.Delete' AND `id_menu` = 534;
UPDATE `permissions` SET `nm_permission` = 'FG_Stock.View' WHERE `nm_permission` = 'Stock_Finish_Good.View' AND `id_menu` = 543;
UPDATE `permissions` SET `nm_permission` = 'FG_Stock.Add' WHERE `nm_permission` = 'Stock_Finish_Good.Add' AND `id_menu` = 543;
UPDATE `permissions` SET `nm_permission` = 'FG_Stock.Manage' WHERE `nm_permission` = 'Stock_Finish_Good.Manage' AND `id_menu` = 543;
UPDATE `permissions` SET `nm_permission` = 'FG_Stock.Delete' WHERE `nm_permission` = 'Stock_Finish_Good.Delete' AND `id_menu` = 543;
UPDATE `permissions` SET `nm_permission` = 'Daftar_Claim_Material.View' WHERE `nm_permission` = 'Daftar_Klaim_Material.View' AND `id_menu` = 545;
UPDATE `permissions` SET `nm_permission` = 'Daftar_Claim_Material.Add' WHERE `nm_permission` = 'Daftar_Klaim_Material.Add' AND `id_menu` = 545;
UPDATE `permissions` SET `nm_permission` = 'Daftar_Claim_Material.Manage' WHERE `nm_permission` = 'Daftar_Klaim_Material.Manage' AND `id_menu` = 545;
UPDATE `permissions` SET `nm_permission` = 'Daftar_Claim_Material.Delete' WHERE `nm_permission` = 'Daftar_Klaim_Material.Delete' AND `id_menu` = 545;

-- 3. TAMBAHKAN PERMISSION UNTUK MENU AKTIF YANG BELUM MEMILIKI PERMISSION LENGKAP
-- Menu 265 (Master Budget)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Master_Budget.View', 'View', 265, 'Master Budget'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Master_Budget.View' AND `id_menu` = 265);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Master_Budget.Add', 'Add', 265, 'Master Budget'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Master_Budget.Add' AND `id_menu` = 265);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Master_Budget.Manage', 'Manage', 265, 'Master Budget'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Master_Budget.Manage' AND `id_menu` = 265);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Master_Budget.Delete', 'Delete', 265, 'Master Budget'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Master_Budget.Delete' AND `id_menu` = 265);

-- Menu 491 (Report)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Report.View', 'View', 491, 'Report'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Report.View' AND `id_menu` = 491);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Report.Add', 'Add', 491, 'Report'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Report.Add' AND `id_menu` = 491);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Report.Manage', 'Manage', 491, 'Report'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Report.Manage' AND `id_menu` = 491);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Report.Delete', 'Delete', 491, 'Report'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Report.Delete' AND `id_menu` = 491);

-- Menu 2 (Settings) - Lengkapi Add, Manage, Delete
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Settings.Add', 'Add', 2, 'Settings'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Settings.Add' AND `id_menu` = 2);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Settings.Manage', 'Manage', 2, 'Settings'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Settings.Manage' AND `id_menu` = 2);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Settings.Delete', 'Delete', 2, 'Settings'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Settings.Delete' AND `id_menu` = 2);

-- Menu 478 (Production)
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Production.View', 'View', 478, 'Production'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Production.View' AND `id_menu` = 478);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Production.Add', 'Add', 478, 'Production'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Production.Add' AND `id_menu` = 478);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Production.Manage', 'Manage', 478, 'Production'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Production.Manage' AND `id_menu` = 478);
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`)
SELECT 'Production.Delete', 'Delete', 478, 'Production'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Production.Delete' AND `id_menu` = 478);

-- 4. PERBAIKAN MAPPING menus.permission_id PADA MENU AKTIF
UPDATE `menus` SET `permission_id` = 658 WHERE `id` = 2; -- Settings -> Settings.View
UPDATE `menus` SET `permission_id` = 352 WHERE `id` = 25; -- Payment -> Payment.View
UPDATE `menus` SET `permission_id` = 384 WHERE `id` = 143; -- Master Material -> Master_Inventory.View
UPDATE `menus` SET `permission_id` = 815 WHERE `id` = 241; -- Master Barang Stok -> Master_Indirect.View
UPDATE `menus` SET `permission_id` = 2024 WHERE `id` = 535; -- Price Reference -> Price_Ref_Raw_Material.View
UPDATE `menus` m 
JOIN `permissions` p ON p.id_menu = m.id AND p.nm_permission = 'Master_Budget.View'
SET m.permission_id = p.id_permission
WHERE m.id = 265;
UPDATE `menus` m 
JOIN `permissions` p ON p.id_menu = m.id AND p.nm_permission = 'Report.View'
SET m.permission_id = p.id_permission
WHERE m.id = 491;
UPDATE `menus` m 
JOIN `permissions` p ON p.id_menu = m.id AND p.nm_permission = 'Production.View'
SET m.permission_id = p.id_permission
WHERE m.id = 478;

-- 5. PEMBERSIHAN ORPHAN DATA (RELASI USER & GROUP PERMISSION DARI MENU YANG DIHAPUS / DEAD RECORDS)
DELETE FROM `user_permissions` WHERE `id_permission` IN (SELECT `id_permission` FROM `permissions` WHERE `id_menu` IN (1,21,26,31,32,35,37,52,82,86,88,120,125,126,129,130,131,132,133,134,135,139,150,154,155,156,158,159,162,163,164,167,168,169,170,172,173,174,175,177,179,180,181,183,184,185,187,190,193,194,195,196,197,198,199,200,201,202,203,204,206,208,209,213,214,215,216,219,220,221,225,230,231,232,233,234,235,236,237,238,244,246,247,248,249,250,253,254,255,256,257,259,266,268,269,272,274,277,280,281,284,285,292,294,296,297,298,300,301,303,306,308,309,310,311,312,314,315,316,317,318,319,320,321,323,327,328,329,330,331,332,333,334,335,336,337,338,339,340,342,344,348,349,350,356,360,362,363,364,366,367,368,369,370,371,375,376,377,378,380,382,383,385,386,387,390,391,392,393,395,396,397,399,400,408,409,411,412,414,415,416,417,418,419,420,421,422,423,424,425,426,427,428,429,430,431,432,433,435,436,438,439,440,445,447,448,449,450,451,452,453,454,455,456,457,458,459,460,461,462,463,464,465,467,468,469,481,509,517));
DELETE FROM `group_permissions` WHERE `id_permission` IN (SELECT `id_permission` FROM `permissions` WHERE `id_menu` IN (1,21,26,31,32,35,37,52,82,86,88,120,125,126,129,130,131,132,133,134,135,139,150,154,155,156,158,159,162,163,164,167,168,169,170,172,173,174,175,177,179,180,181,183,184,185,187,190,193,194,195,196,197,198,199,200,201,202,203,204,206,208,209,213,214,215,216,219,220,221,225,230,231,232,233,234,235,236,237,238,244,246,247,248,249,250,253,254,255,256,257,259,266,268,269,272,274,277,280,281,284,285,292,294,296,297,298,300,301,303,306,308,309,310,311,312,314,315,316,317,318,319,320,321,323,327,328,329,330,331,332,333,334,335,336,337,338,339,340,342,344,348,349,350,356,360,362,363,364,366,367,368,369,370,371,375,376,377,378,380,382,383,385,386,387,390,391,392,393,395,396,397,399,400,408,409,411,412,414,415,416,417,418,419,420,421,422,423,424,425,426,427,428,429,430,431,432,433,435,436,438,439,440,445,447,448,449,450,451,452,453,454,455,456,457,458,459,460,461,462,463,464,465,467,468,469,481,509,517));
DELETE FROM `permissions` WHERE `id_menu` IN (1,21,26,31,32,35,37,52,82,86,88,120,125,126,129,130,131,132,133,134,135,139,150,154,155,156,158,159,162,163,164,167,168,169,170,172,173,174,175,177,179,180,181,183,184,185,187,190,193,194,195,196,197,198,199,200,201,202,203,204,206,208,209,213,214,215,216,219,220,221,225,230,231,232,233,234,235,236,237,238,244,246,247,248,249,250,253,254,255,256,257,259,266,268,269,272,274,277,280,281,284,285,292,294,296,297,298,300,301,303,306,308,309,310,311,312,314,315,316,317,318,319,320,321,323,327,328,329,330,331,332,333,334,335,336,337,338,339,340,342,344,348,349,350,356,360,362,363,364,366,367,368,369,370,371,375,376,377,378,380,382,383,385,386,387,390,391,392,393,395,396,397,399,400,408,409,411,412,414,415,416,417,418,419,420,421,422,423,424,425,426,427,428,429,430,431,432,433,435,436,438,439,440,445,447,448,449,450,451,452,453,454,455,456,457,458,459,460,461,462,463,464,465,467,468,469,481,509,517);
-- Bersihkan juga orphan permission yang id_menu tidak ada di tabel menus
DELETE FROM `user_permissions` WHERE `id_permission` IN (SELECT `id_permission` FROM `permissions` WHERE `id_menu` NOT IN (SELECT `id` FROM `menus`));
DELETE FROM `group_permissions` WHERE `id_permission` IN (SELECT `id_permission` FROM `permissions` WHERE `id_menu` NOT IN (SELECT `id` FROM `menus`));
DELETE FROM `permissions` WHERE `id_menu` NOT IN (SELECT `id` FROM `menus`);

-- 6. HAPUS MENU TIDAK AKTIF, DEAD LINK & HEADER KOSONG (TOTAL 229 MENU)
DELETE FROM `menus` WHERE `id` IN (1,21,26,31,32,35,37,52,82,86,88,120,125,126,129,130,131,132,133,134,135,139,150,154,155,156,158,159,162,163,164,167,168,169,170,172,173,174,175,177,179,180,181,183,184,185,187,190,193,194,195,196,197,198,199,200,201,202,203,204,206,208,209,213,214,215,216,219,220,221,225,230,231,232,233,234,235,236,237,238,244,246,247,248,249,250,253,254,255,256,257,259,266,268,269,272,274,277,280,281,284,285,292,294,296,297,298,300,301,303,306,308,309,310,311,312,314,315,316,317,318,319,320,321,323,327,328,329,330,331,332,333,334,335,336,337,338,339,340,342,344,348,349,350,356,360,362,363,364,366,367,368,369,370,371,375,376,377,378,380,382,383,385,386,387,390,391,392,393,395,396,397,399,400,408,409,411,412,414,415,416,417,418,419,420,421,422,423,424,425,426,427,428,429,430,431,432,433,435,436,438,439,440,445,447,448,449,450,451,452,453,454,455,456,457,458,459,460,461,462,463,464,465,467,468,469,481,509,517);

-- 7. PENATAAN ULANG URUTAN (ORDER) MENU LEVEL 1 (SIDEBAR)
UPDATE `menus` SET `order` = 1 WHERE `id` = 518;
UPDATE `menus` SET `order` = 10 WHERE `id` = 13;
UPDATE `menus` SET `order` = 11 WHERE `id` = 165;
UPDATE `menus` SET `order` = 12 WHERE `id` = 143;
UPDATE `menus` SET `order` = 13 WHERE `id` = 473;
UPDATE `menus` SET `order` = 14 WHERE `id` = 503;
UPDATE `menus` SET `order` = 20 WHERE `id` = 519;
UPDATE `menus` SET `order` = 21 WHERE `id` = 539;
UPDATE `menus` SET `order` = 22 WHERE `id` = 258;
UPDATE `menus` SET `order` = 30 WHERE `id` = 504;
UPDATE `menus` SET `order` = 31 WHERE `id` = 324;
UPDATE `menus` SET `order` = 32 WHERE `id` = 322;
UPDATE `menus` SET `order` = 33 WHERE `id` = 161;
UPDATE `menus` SET `order` = 34 WHERE `id` = 25;
UPDATE `menus` SET `order` = 40 WHERE `id` = 188;
UPDATE `menus` SET `order` = 41 WHERE `id` = 352;
UPDATE `menus` SET `order` = 42 WHERE `id` = 478;
UPDATE `menus` SET `order` = 43 WHERE `id` = 542;
UPDATE `menus` SET `order` = 50 WHERE `id` = 544;
UPDATE `menus` SET `order` = 60 WHERE `id` = 505;
UPDATE `menus` SET `order` = 61 WHERE `id` = 295;
UPDATE `menus` SET `order` = 62 WHERE `id` = 483;
UPDATE `menus` SET `order` = 70 WHERE `id` = 491;
UPDATE `menus` SET `order` = 99 WHERE `id` = 2;


-- 8. PERAPIAN URUTAN SUBMENU LEVEL 2 & LEVEL 3
UPDATE `menus` SET `order` = 1 WHERE `id` = 3; -- Parent 2: Users
UPDATE `menus` SET `order` = 2 WHERE `id` = 14; -- Parent 2: Menus
UPDATE `menus` SET `order` = 3 WHERE `id` = 94; -- Parent 2: Permissions
UPDATE `menus` SET `order` = 1 WHERE `id` = 222; -- Parent 13: Master Unit Measurement
UPDATE `menus` SET `order` = 2 WHERE `id` = 223; -- Parent 13: Master Packing
UPDATE `menus` SET `order` = 3 WHERE `id` = 147; -- Parent 13: Master Coating
UPDATE `menus` SET `order` = 4 WHERE `id` = 148; -- Parent 13: Master Tensile
UPDATE `menus` SET `order` = 9 WHERE `id` = 242; -- Parent 13: Price From Supplier
UPDATE `menus` SET `order` = 10 WHERE `id` = 361; -- Parent 13: TOP
UPDATE `menus` SET `order` = 11 WHERE `id` = 540; -- Parent 13: Master Shift
UPDATE `menus` SET `order` = 12 WHERE `id` = 471; -- Parent 13: Master Forwarding Cost
UPDATE `menus` SET `order` = 13 WHERE `id` = 34; -- Parent 13: HS Code
UPDATE `menus` SET `order` = 14 WHERE `id` = 493; -- Parent 13: Master  Sound
UPDATE `menus` SET `order` = 15 WHERE `id` = 494; -- Parent 13: Master Rate Labor
UPDATE `menus` SET `order` = 16 WHERE `id` = 495; -- Parent 13: Master Rate Process Product
UPDATE `menus` SET `order` = 17 WHERE `id` = 499; -- Parent 13: Master Definisi
UPDATE `menus` SET `order` = 18 WHERE `id` = 500; -- Parent 13: Master Jurnal Mapping
UPDATE `menus` SET `order` = 19 WHERE `id` = 516; -- Parent 13: Master Currency
UPDATE `menus` SET `order` = 4 WHERE `id` = 351; -- Parent 161: Approval PO
UPDATE `menus` SET `order` = 7 WHERE `id` = 405; -- Parent 161: Closed PO
UPDATE `menus` SET `order` = 2 WHERE `id` = 501; -- Parent 165: Asset coa 
UPDATE `menus` SET `order` = 3 WHERE `id` = 176; -- Parent 165: Asset Category
UPDATE `menus` SET `order` = 1 WHERE `id` = 226; -- Parent 143: Jenis Logam
UPDATE `menus` SET `order` = 2 WHERE `id` = 227; -- Parent 143: Slitted/Mother Coil
UPDATE `menus` SET `order` = 3 WHERE `id` = 228; -- Parent 143: Boron/Non Boron
UPDATE `menus` SET `order` = 4 WHERE `id` = 229; -- Parent 143: Material
UPDATE `menus` SET `order` = 2 WHERE `id` = 245; -- Parent 242: Approval Material Price
UPDATE `menus` SET `order` = 2 WHERE `id` = 299; -- Parent 258: Master Petty Cash
UPDATE `menus` SET `order` = 3 WHERE `id` = 263; -- Parent 258: Budget Stock
UPDATE `menus` SET `order` = 4 WHERE `id` = 261; -- Parent 258: Budget Non Rutin
UPDATE `menus` SET `order` = 5 WHERE `id` = 262; -- Parent 258: Budget Pembayaran Periodik
UPDATE `menus` SET `order` = 6 WHERE `id` = 264; -- Parent 258: Budget Umum
UPDATE `menus` SET `order` = 7 WHERE `id` = 260; -- Parent 258: Budget Expense
UPDATE `menus` SET `order` = 1 WHERE `id` = 278; -- Parent 295: Approval Transportasi
UPDATE `menus` SET `order` = 2 WHERE `id` = 271; -- Parent 295: Approval Kasbon
UPDATE `menus` SET `order` = 3 WHERE `id` = 270; -- Parent 295: Approval Expense
UPDATE `menus` SET `order` = 4 WHERE `id` = 273; -- Parent 295: Approval Pengajuan Pembayaran Rutin
UPDATE `menus` SET `order` = 5 WHERE `id` = 394; -- Parent 295: Approval Pengembalian Expense
UPDATE `menus` SET `order` = 6 WHERE `id` = 446; -- Parent 295: Approval Mutasi
UPDATE `menus` SET `order` = 7 WHERE `id` = 510; -- Parent 295: Approval Expense Petty Cash
UPDATE `menus` SET `order` = 2 WHERE `id` = 506; -- Parent 282: Transaksi Petty Cash
UPDATE `menus` SET `order` = 3 WHERE `id` = 507; -- Parent 282: Pelaporan Petty Cash
UPDATE `menus` SET `order` = 2 WHERE `id` = 286; -- Parent 505: Input Transportasi
UPDATE `menus` SET `order` = 3 WHERE `id` = 288; -- Parent 505: Pengajuan Transportasi
UPDATE `menus` SET `order` = 4 WHERE `id` = 287; -- Parent 505: List Transportasi
UPDATE `menus` SET `order` = 5 WHERE `id` = 282; -- Parent 505: Expense
UPDATE `menus` SET `order` = 6 WHERE `id` = 354; -- Parent 505: Pengembalian Expense
UPDATE `menus` SET `order` = 7 WHERE `id` = 444; -- Parent 505: Mutasi Bank
UPDATE `menus` SET `order` = 2 WHERE `id` = 290; -- Parent 283: Kasbon List
UPDATE `menus` SET `order` = 1 WHERE `id` = 304; -- Parent 188: Gudang Coil
UPDATE `menus` SET `order` = 1 WHERE `id` = 305; -- Parent 304: Stock Coil
UPDATE `menus` SET `order` = 2 WHERE `id` = 472; -- Parent 304: Stock Value
UPDATE `menus` SET `order` = 1 WHERE `id` = 325; -- Parent 324: PR Material
UPDATE `menus` SET `order` = 1 WHERE `id` = 374; -- Parent 352: Incoming Stok
UPDATE `menus` SET `order` = 2 WHERE `id` = 373; -- Parent 352: Outgoing Stock
UPDATE `menus` SET `order` = 3 WHERE `id` = 353; -- Parent 352: Stock
UPDATE `menus` SET `order` = 4 WHERE `id` = 365; -- Parent 352: Adjustment Stock
UPDATE `menus` SET `order` = 5 WHERE `id` = 401; -- Parent 352: Costbook Stock
UPDATE `menus` SET `order` = 1 WHERE `id` = 359; -- Parent 275: Approval PR Stock Management
UPDATE `menus` SET `order` = 2 WHERE `id` = 381; -- Parent 358: Approval PR Depart Management
UPDATE `menus` SET `order` = 1 WHERE `id` = 413; -- Parent 410: Approval PR Asset Management
UPDATE `menus` SET `order` = 1 WHERE `id` = 508; -- Parent 444: Transaksi Bank
UPDATE `menus` SET `order` = 2 WHERE `id` = 442; -- Parent 444: Request Mutasi
UPDATE `menus` SET `order` = 3 WHERE `id` = 480; -- Parent 478: Production Report
UPDATE `menus` SET `order` = 5 WHERE `id` = 541; -- Parent 478: Unpack Coil
UPDATE `menus` SET `order` = 2 WHERE `id` = 538; -- Parent 539: Master Standard Material
UPDATE `menus` SET `order` = 3 WHERE `id` = 527; -- Parent 539: Flow Process
UPDATE `menus` SET `order` = 1 WHERE `id` = 515; -- Parent 518: Monitoring Open PO Supplier
UPDATE `menus` SET `order` = 1 WHERE `id` = 528; -- Parent 537: Simulasi Costing
UPDATE `menus` SET `order` = 2 WHERE `id` = 529; -- Parent 537: Notifikasi Bottom Price
UPDATE `menus` SET `order` = 3 WHERE `id` = 530; -- Parent 537: Pricelist 
UPDATE `menus` SET `order` = 4 WHERE `id` = 531; -- Parent 537: Approval Pricelist
UPDATE `menus` SET `order` = 5 WHERE `id` = 532; -- Parent 537: Product Catalog
UPDATE `menus` SET `order` = 6 WHERE `id` = 533; -- Parent 537: History Costing
UPDATE `menus` SET `order` = 7 WHERE `id` = 534; -- Parent 537: History Price List

-- SELESAI MIGRATION 016 --
