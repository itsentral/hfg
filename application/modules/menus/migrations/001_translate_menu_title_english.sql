-- ==============================================================================
-- Terjemahan nama menu (menus.title) ke Bahasa Inggris (keputusan user 2026-11-20).
-- Cadangan judul lama disimpan di bak_menus_title_20261120 (id, title) untuk rollback.
-- Hanya baris yang judulnya masih persis sama dengan teks lama yang diubah (aman dijalankan ulang).
-- Rollback: UPDATE menus m JOIN bak_menus_title_20261120 b ON b.id = m.id SET m.title = b.title;
-- ==============================================================================
CREATE TABLE IF NOT EXISTS bak_menus_title_20261120 AS SELECT id, title FROM menus;

UPDATE menus SET title = 'Metal Type'                               WHERE id = 226 AND title = 'Jenis Logam';
UPDATE menus SET title = 'Stock Items'                              WHERE id = 239 AND title = 'Barang Stok';
UPDATE menus SET title = 'Stock Category'                           WHERE id = 240 AND title = 'Category Stok';
UPDATE menus SET title = 'Master Stock Items'                       WHERE id = 241 AND title = 'Master Barang Stok';
UPDATE menus SET title = 'Non-Routine Budget'                       WHERE id = 261 AND title = 'Budget Non Rutin';
UPDATE menus SET title = 'Periodic Payment Budget'                  WHERE id = 262 AND title = 'Budget Pembayaran Periodik';
UPDATE menus SET title = 'General Budget'                           WHERE id = 264 AND title = 'Budget Umum';
UPDATE menus SET title = 'Approval Cash Advance'                    WHERE id = 271 AND title = 'Approval Kasbon';
UPDATE menus SET title = 'Approval Routine Payment Request'         WHERE id = 273 AND title = 'Approval Pengajuan Pembayaran Rutin';
UPDATE menus SET title = 'Approval Transportation'                  WHERE id = 278 AND title = 'Approval Transportasi';
UPDATE menus SET title = 'Cash Advance'                             WHERE id = 283 AND title = 'Kasbon';
UPDATE menus SET title = 'Transportation Input'                     WHERE id = 286 AND title = 'Input Transportasi';
UPDATE menus SET title = 'Transportation List'                      WHERE id = 287 AND title = 'List Transportasi';
UPDATE menus SET title = 'Transportation Request'                   WHERE id = 288 AND title = 'Pengajuan Transportasi';
UPDATE menus SET title = 'Cash Advance Input'                       WHERE id = 289 AND title = 'Input Kasbon';
UPDATE menus SET title = 'Cash Advance List'                        WHERE id = 290 AND title = 'Kasbon List';
UPDATE menus SET title = 'Approval Non-Purchase'                    WHERE id = 295 AND title = 'Approval Non Pembelian';
UPDATE menus SET title = 'Coil Warehouse'                           WHERE id = 304 AND title = 'Gudang Coil';
UPDATE menus SET title = 'PR Stock'                                 WHERE id = 326 AND title = 'PR Stok';
UPDATE menus SET title = 'Purchase Order'                           WHERE id = 346 AND title = 'Purchase Order ';
UPDATE menus SET title = 'Expense Return'                           WHERE id = 354 AND title = 'Pengembalian Expense';
UPDATE menus SET title = 'PR Department'                            WHERE id = 357 AND title = 'PR Departemen';
UPDATE menus SET title = 'Approval PR Department'                   WHERE id = 358 AND title = 'Approval PR Departemen';
UPDATE menus SET title = 'Purchase Method'                          WHERE id = 372 AND title = 'Metode Pembelian';
UPDATE menus SET title = 'Incoming Stock'                           WHERE id = 374 AND title = 'Incoming Stok';
UPDATE menus SET title = 'Approval PR Department Finance'           WHERE id = 379 AND title = 'Approval PR Depart Finance';
UPDATE menus SET title = 'Approval PR Department Management'        WHERE id = 381 AND title = 'Approval PR Depart Management';
UPDATE menus SET title = 'Approval Expense Return'                  WHERE id = 394 AND title = 'Approval Pengembalian Expense';
UPDATE menus SET title = 'Costbook Stock Warehouse Balance'         WHERE id = 403 AND title = 'Costbook Stock Neraca Gudang';
UPDATE menus SET title = 'Incoming Department'                      WHERE id = 434 AND title = 'Incoming Departemen';
UPDATE menus SET title = 'Request Bank Transfer'                    WHERE id = 442 AND title = 'Request Mutasi';
UPDATE menus SET title = 'Bank Transfer'                            WHERE id = 444 AND title = 'Mutasi Bank';
UPDATE menus SET title = 'Approval Bank Transfer'                   WHERE id = 446 AND title = 'Approval Mutasi';
UPDATE menus SET title = 'Product Sub Category'                     WHERE id = 476 AND title = 'Product Jenis';
UPDATE menus SET title = 'Master Definition'                        WHERE id = 499 AND title = 'Master Definisi';
UPDATE menus SET title = 'PR Department, Stock, Asset'              WHERE id = 504 AND title = 'PR Dept, Stok, Asset';
UPDATE menus SET title = 'Non-Purchase Expenditure'                 WHERE id = 505 AND title = 'Pengeluaran Non Pembelian';
UPDATE menus SET title = 'Petty Cash Transaction'                   WHERE id = 506 AND title = 'Transaksi Petty Cash';
UPDATE menus SET title = 'Petty Cash Report'                        WHERE id = 507 AND title = 'Pelaporan Petty Cash';
UPDATE menus SET title = 'Bank Transaction'                         WHERE id = 508 AND title = 'Transaksi Bank';
UPDATE menus SET title = 'Approval Cash Advance Finance'            WHERE id = 511 AND title = 'Approval Kasbon Finance';
UPDATE menus SET title = 'Approval Cash Advance Management'         WHERE id = 512 AND title = 'Approval Kasbon Management';
UPDATE menus SET title = 'Indirect Labor Factor'                    WHERE id = 522 AND title = 'Faktor Indirect Labor';
UPDATE menus SET title = 'Special Supporting Material & FOH Rate'   WHERE id = 523 AND title = 'Rate Bahan Pendukung & FOH';
UPDATE menus SET title = 'Costing Simulation'                       WHERE id = 528 AND title = 'Simulasi Costing';
UPDATE menus SET title = 'Bottom Price Notification'                WHERE id = 529 AND title = 'Notifikasi Bottom Price';
UPDATE menus SET title = 'Material Claim'                           WHERE id = 544 AND title = 'Klaim Material';
UPDATE menus SET title = 'Material Claim List'                      WHERE id = 545 AND title = 'Daftar Klaim Material';
UPDATE menus SET title = 'Slitting Warehouse'                       WHERE id = 546 AND title = 'Gudang Slitting';
UPDATE menus SET title = 'Compensation Cost Report'                 WHERE id = 547 AND title = 'Report Biaya Kompensasi';
