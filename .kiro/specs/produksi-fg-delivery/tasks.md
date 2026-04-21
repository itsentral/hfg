# Rencana Implementasi: Produksi, FG, dan Delivery

## Overview

Implementasi modul end-to-end alur produksi PT HFG menggunakan CodeIgniter 3 HMVC dengan MySQL.
Setiap task mencakup DDL/migrasi database, model, controller, dan view sesuai konvensi MVC ketat.
Semua parameter toleransi disimpan di `ms_config_param`, self-approval dicegah di semua approval,
dan operasi multi-tabel menggunakan transaksi database.

---

## Phase 1: Production Planning, SPK, Issue Material, Timbang Awal

- [x] 1. Setup infrastruktur dasar dan konfigurasi parameter
  - Buat tabel `ms_config_param` dengan DDL sesuai design
  - Insert data awal: `toleransi_timbang_pct = 0.05`, `toleransi_deviasi_fg_pct = 0.05`
  - Buat tabel `ms_notification` untuk sistem notifikasi in-app
  - Buat helper `Config_param_helper.php` dengan fungsi `get_param($key)` untuk membaca nilai dari `ms_config_param`
  - _Requirements: 3.5, 7.6_

- [x] 2. Modul `production_planning` — Production Plan dan Alokasi Coil
  - [x] 2.1 Buat DDL tabel `tr_production_plan`, `tr_production_plan_detail`, `tr_production_plan_coil_alloc`
    - Pastikan foreign key dan index sesuai design
    - _Requirements: 1.2, 1.3_
  - [x] 2.2 Buat `Production_planning_model.php` dengan method:
    - `save_plan($data)` — simpan plan baru, status default `Draft`
    - `get_plan($plan_no)` — ambil satu plan
    - `get_coil_available()` — return coil status `available` di gudang material
    - `release_plan($plan_no)` — ubah status ke `Released`, auto-generate SPK Draft (dalam transaksi)
    - `cancel_plan($plan_no)` — ubah status ke `Cancelled`
    - `check_and_close_plan($plan_no)` — cek semua SPK Closed, ubah plan ke `Closed`
    - `get_list_for_datatable($params)` — query untuk DataTables server-side
    - _Requirements: 1.2, 1.3, 1.4, 1.5, 1.6_
  - [x] 2.3 Buat `Production_planning.php` controller dengan method:
    - `index()`, `add()`, `edit($plan_no)`, `view($plan_no)`, `monitoring()`
    - `data_side_plan()` — JSON endpoint DataTables
    - `get_coil_available()` — JSON endpoint AJAX
    - `save_plan()` — POST: validasi input, panggil model, redirect
    - `process_release($plan_no)` — POST: release plan, return JSON
    - `process_cancel($plan_no)` — POST: cancel plan, return JSON
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.7_
  - [x] 2.4 Buat views: `index.php` (list + DataTables), `form.php` (add/edit dengan AJAX coil), `view.php` (detail plan + coil teralokasi), `monitoring.php` (monitoring semua plan)
    - _Requirements: 1.1, 1.7_
  - [ ]* 2.5 Tulis property test untuk alokasi coil ke Production Plan
    - **Property 1: Alokasi Coil ke Production Plan**
    - **Validates: Requirements 1.2**
  - [ ]* 2.6 Tulis property test untuk status awal Production Plan
    - **Property 2: Status Awal Production Plan adalah Draft**
    - **Validates: Requirements 1.3**
  - [ ]* 2.7 Tulis property test untuk release plan menghasilkan SPK Draft
    - **Property 3: Release Plan Menghasilkan SPK Draft**
    - **Validates: Requirements 1.4, 2.1**
  - [ ]* 2.8 Tulis property test untuk plan closed ketika semua SPK closed
    - **Property 4: Plan Closed Ketika Semua SPK Closed**
    - **Validates: Requirements 1.6**

- [x] 3. Modul `production_issue` — SPK dan Issue Material via Scan Barcode
  - [x] 3.1 Buat DDL tabel `tr_spk_production`, `tr_spk_material_detail`, `tr_spk_scan_log`, `tr_stock_move_prod`
    - _Requirements: 2.1, 2.5_
  - [x] 3.2 Buat `Production_issue_model.php` dengan method:
    - `save_spk($data)` — simpan SPK Draft
    - `get_spk($spk_no)` — ambil satu SPK
    - `release_spk($spk_no)` — ubah status ke `Released`
    - `validate_scan($barcode, $spk_no)` — validasi barcode: cek SPK Released + coil available
    - `process_scan($barcode, $spk_no, $user_id)` — catat mutasi lokasi ke `tr_stock_move_prod`, update status coil, catat `tr_spk_scan_log`, cek apakah semua coil ter-scan (dalam transaksi)
    - `check_and_set_in_process($spk_no)` — ubah status SPK ke `In Process` jika semua coil ter-scan
    - `get_coil_history($barcode)` — riwayat mutasi coil
    - `get_list_for_datatable($params)` — query DataTables
    - _Requirements: 2.2, 2.3, 2.4, 2.5, 2.6, 2.7_
  - [x] 3.3 Buat `Production_issue.php` controller dengan method:
    - `index()`, `add($plan_no)`, `view($spk_no)`, `monitoring_coil()`, `histori_coil($barcode)`
    - `data_side_spk()` — JSON endpoint DataTables
    - `save_spk()` — POST: simpan SPK
    - `process_release_spk($spk_no)` — POST: release SPK, return JSON
    - `scan_issue()` — halaman scan barcode
    - `process_scan()` — POST: validasi + proses scan, return JSON dengan pesan error deskriptif
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.8, 2.9_
  - [x] 3.4 Buat views: `index.php`, `form_spk.php`, `view_spk.php`, `scan_issue.php` (UI scan barcode real-time), `monitoring_coil.php`, `histori_coil.php`
    - _Requirements: 2.8, 2.9_
  - [ ]* 3.5 Tulis property test untuk validasi scan barcode issue material
    - **Property 5: Validasi Scan Barcode Issue Material**
    - **Validates: Requirements 2.3, 2.4**
  - [ ]* 3.6 Tulis property test untuk mutasi lokasi tanpa jurnal akuntansi
    - **Property 6: Mutasi Lokasi Tanpa Jurnal Akuntansi**
    - **Validates: Requirements 2.5**
  - [ ]* 3.7 Tulis property test untuk SPK menjadi In Process setelah semua coil ter-scan
    - **Property 7: SPK Menjadi In Process Setelah Semua Coil Ter-scan**
    - **Validates: Requirements 2.6**
  - [ ]* 3.8 Tulis property test untuk uniqueness barcode coil
    - **Property 8: Uniqueness Barcode Coil**
    - **Validates: Requirements 2.7**

- [x] 4. Modul `production_weighing` — Timbang Awal Coil
  - [x] 4.1 Buat DDL tabel `tr_coil_preweigh` dan `tr_coil_preweigh_component`
    - Pastikan kolom `net_timbang_awal` sebagai generated column: `berat_coil_tong + berat_cover_wrapping`
    - _Requirements: 3.3, 3.6_
  - [x] 4.2 Buat `Production_weighing_model.php` dengan method:
    - `validate_coil_for_preweigh($barcode)` — validasi coil ada di SPK In Process dan berlokasi di gudang produksi
    - `calculate_net_weight($data)` — hitung `net_timbang_awal = berat_coil_tong + berat_cover_wrapping`
    - `calculate_selisih($net_timbang, $net_pl)` — hitung selisih dan persentase deviasi
    - `save_preweigh($data)` — simpan ke `tr_coil_preweigh` + `tr_coil_preweigh_component` (dalam transaksi), cek toleransi dari `ms_config_param`, set status Exception jika perlu
    - `send_notification_exception($preweigh_no)` — insert notifikasi ke `ms_notification` untuk QC/Supervisor
    - `get_perbandingan_spk($spk_no)` — data perbandingan timbang awal vs packing list per SPK
    - `get_list_for_datatable($params)` — query DataTables
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_
  - [x] 4.3 Buat `Production_weighing.php` controller dengan method:
    - `index()`, `add()`, `view($preweigh_no)`, `perbandingan($spk_no)`
    - `data_side_preweigh()` — JSON endpoint DataTables
    - `save_preweigh()` — POST: validasi input, hitung net weight, cek toleransi, simpan
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.7_
  - [x] 4.4 Buat views: `index.php`, `form_preweigh.php` (scan barcode + input komponen berat), `view_preweigh.php` (detail + selisih), `perbandingan.php` (tabel perbandingan per SPK)
    - _Requirements: 3.7_
  - [ ]* 4.5 Tulis property test untuk rumus Net Weight timbang awal
    - **Property 9: Rumus Net Weight Timbang Awal**
    - **Validates: Requirements 3.3**
  - [ ]* 4.6 Tulis property test untuk deteksi exception timbang awal
    - **Property 10: Deteksi Exception Timbang Awal**
    - **Validates: Requirements 3.4, 3.5**
  - [ ]* 4.7 Tulis property test untuk persistensi data timbang awal
    - **Property 11: Persistensi Data Timbang Awal**
    - **Validates: Requirements 3.6**

- [ ] 5. Checkpoint Phase 1 — Pastikan semua test lulus, tanyakan kepada user jika ada pertanyaan.

---

## Phase 2: Laporan Produksi, Validasi Berat FG, Supplier Performance Feed

- [x] 6. Modul `production_report` — Laporan Produksi dan Validasi Berat FG
  - [x] 6.1 Buat DDL tabel `tr_production_report`, `tr_production_report_result`, `tr_supplier_perf_feed`
    - _Requirements: 4.1, 4.9, 9.1_
  - [x] 6.2 Buat `Production_report_model.php` dengan method kalkulasi:
    - `calculate_total_berat($data)` — hitung `Total_Berat_Coil` sesuai rumus requirements 4.2
    - `calculate_net_hasil($total_berat, $tong_coil, $cover_wrapping)` — hitung `Net_Hasil_Produksi`
    - `calculate_yield($data, $total_berat)` — hitung persentase yield per kategori
    - `calculate_berat_satuan_fg($fg_kg, $fg_qty)` — hitung berat satuan aktual FG
    - `check_deviasi_fg($berat_satuan_aktual, $berat_acuan)` — cek deviasi vs berat acuan standar, baca toleransi dari `ms_config_param`
    - _Requirements: 4.2, 4.3, 4.4, 5.1, 5.2, 5.3_
  - [x] 6.3 Lanjutkan `Production_report_model.php` dengan method transaksi:
    - `save_report($data)` — simpan laporan + result (dalam transaksi)
    - `submit_report($report_no)` — ubah status ke `Submitted`, kirim notifikasi
    - `approve_report($report_no, $approver_id)` — ubah status ke `Approved`
    - `reject_report($report_no, $approver_id, $alasan)` — ubah status ke `Rejected`
    - `override_fg($report_no, $alasan)` — simpan konfirmasi override deviasi berat FG
    - `post_report($report_no)` — posting: ubah status ke `Posted to FG`, update SPK ke `Submitted`, auto-create FG Receipt Draft, insert `tr_supplier_perf_feed` (semua dalam satu transaksi)
    - `get_list_for_datatable($params)` — query DataTables
    - _Requirements: 4.5, 4.6, 4.7, 4.8, 4.9, 5.4_
  - [x] 6.4 Buat `Production_report.php` controller dengan method:
    - `index()`, `add($spk_no)`, `edit($report_no)`, `view($report_no)`
    - `data_side_report()` — JSON endpoint DataTables
    - `save_report()` — POST: validasi, hitung total berat, cek deviasi FG, simpan
    - `process_submit($report_no)` — POST: submit laporan, return JSON
    - `process_approve($report_no)` — POST: approve (QC/Supervisor), return JSON
    - `process_reject($report_no)` — POST: reject + alasan, return JSON
    - `process_post($report_no)` — POST: posting ke FG, return JSON
    - `process_override_fg($report_no)` — POST: konfirmasi override deviasi, return JSON
    - _Requirements: 4.1, 4.5, 4.6, 4.7, 4.8, 5.3, 5.4_
  - [x] 6.5 Buat views: `index.php`, `form_report.php` (input semua kategori hasil produksi), `view_report.php` (detail + yield breakdown + deviasi FG), modal konfirmasi override deviasi
    - _Requirements: 4.1, 4.4, 5.3_
  - [ ]* 6.6 Tulis property test untuk rumus Total Berat Coil laporan produksi
    - **Property 12: Rumus Total Berat Coil Laporan Produksi**
    - **Validates: Requirements 4.2**
  - [ ]* 6.7 Tulis property test untuk rumus Net Hasil Produksi
    - **Property 13: Rumus Net Hasil Produksi**
    - **Validates: Requirements 4.3**
  - [ ]* 6.8 Tulis property test untuk yield tidak melebihi 100%
    - **Property 14: Yield Tidak Melebihi 100%**
    - **Validates: Requirements 4.4**
  - [ ]* 6.9 Tulis property test untuk state machine laporan produksi
    - **Property 15: State Machine Laporan Produksi**
    - **Validates: Requirements 4.5, 4.6, 4.7, 4.8**
  - [ ]* 6.10 Tulis property test untuk posting laporan mencatat supplier performance feed
    - **Property 16: Posting Laporan Mencatat Supplier Performance Feed**
    - **Validates: Requirements 4.9, 9.1**
  - [ ]* 6.11 Tulis property test untuk validasi dan penanganan deviasi berat FG
    - **Property 17: Validasi dan Penanganan Deviasi Berat FG**
    - **Validates: Requirements 5.1, 5.2, 5.3, 5.4**
  - [ ]* 6.12 Tulis property test untuk auto-create FG Receipt saat laporan diposting
    - **Property 18: Auto-create FG Receipt Saat Laporan Diposting**
    - **Validates: Requirements 6.1**

- [ ] 7. Checkpoint Phase 2 — Pastikan semua test lulus, tanyakan kepada user jika ada pertanyaan.

---

## Phase 3: FG Receipt, Berat Referensi FG, Stok FG

- [ ] 8. Modul `fg_warehouse` — Penerimaan FG, Stok, dan Berat Referensi
  - [x] 8.1 Buat DDL tabel `tr_fg_receipt`, `tr_fg_receipt_detail`, `fg_stock`, `fg_stock_ledger`, `ms_fg_weight_history`
    - _Requirements: 6.1, 6.2, 6.3, 6.4_
  - [x] 8.2 Buat `Fg_warehouse_model.php` dengan method kalkulasi dan stok:
    - `calculate_berat_referensi($produk_fg)` — hitung rata-rata tertimbang: `total_berat / qty_stok` dari `fg_stock`
    - `update_stok_in($produk_fg, $qty, $berat, $no_referensi)` — tambah stok, catat ledger IN, hitung ulang berat referensi, simpan riwayat ke `ms_fg_weight_history` (dalam transaksi)
    - `update_stok_out($produk_fg, $qty, $berat, $no_referensi)` — kurangi stok, catat ledger OUT, hitung ulang berat referensi (dalam transaksi)
    - `get_stok($produk_fg)` — ambil stok terkini
    - `get_kartu_stok($produk_fg, $tgl_dari, $tgl_sampai)` — riwayat mutasi stok
    - _Requirements: 6.2, 6.3, 6.4, 6.7_
  - [x] 8.3 Lanjutkan `Fg_warehouse_model.php` dengan method receipt:
    - `get_receipt($fg_receipt_no)` — ambil satu receipt
    - `post_receipt($fg_receipt_no, $user_id)` — posting receipt: update stok IN + ledger + berat referensi (dalam transaksi)
    - `cancel_receipt($fg_receipt_no, $user_id)` — cancel: reverse stok OUT + ledger (dalam transaksi)
    - `get_list_for_datatable($params)` — query DataTables
    - _Requirements: 6.2, 6.3, 6.4, 6.5_
  - [x] 8.4 Buat `Fg_warehouse.php` controller dengan method:
    - `index()`, `view($fg_receipt_no)`, `stok_fg()`, `kartu_stok($produk_fg)`
    - `data_side_receipt()` — JSON endpoint DataTables
    - `process_post_receipt($fg_receipt_no)` — POST: posting receipt, return JSON
    - `process_cancel_receipt($fg_receipt_no)` — POST: cancel receipt, return JSON
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7_
  - [x] 8.5 Buat views: `index.php` (list receipt), `view_receipt.php` (detail receipt), `stok_fg.php` (stok terkini + berat referensi per produk), `kartu_stok.php` (riwayat mutasi dengan filter tanggal)
    - _Requirements: 6.6, 6.7_
  - [ ]* 8.6 Tulis property test untuk posting FG Receipt memperbarui stok dan ledger
    - **Property 19: Posting FG Receipt Memperbarui Stok dan Ledger**
    - **Validates: Requirements 6.2, 6.3**
  - [ ]* 8.7 Tulis property test untuk rumus berat referensi FG (rata-rata tertimbang)
    - **Property 20: Rumus Berat Referensi FG**
    - **Validates: Requirements 6.4**
  - [ ]* 8.8 Tulis property test untuk cancel FG Receipt membalik stok (round-trip)
    - **Property 21: Cancel FG Receipt Membalik Stok (Round-trip)**
    - **Validates: Requirements 6.5**

- [ ] 9. Checkpoint Phase 3 — Pastikan semua test lulus, tanyakan kepada user jika ada pertanyaan.

---

## Phase 4: Delivery Order, Timbang Aktual, Approval Exception, Surat Jalan

- [x] 10. Modul `delivery_fg` — Delivery Order dan Timbang Aktual
  - [x] 10.1 Buat DDL tabel `tr_delivery_order`, `tr_delivery_detail`, `tr_delivery_weight_log`, `tr_delivery_approval`
    - _Requirements: 7.1, 7.9, 8.8_
  - [x] 10.2 Buat `Delivery_fg_model.php` dengan method kalkulasi dan simpan DO:
    - `calculate_estimasi_berat($qty_kirim, $berat_referensi)` — hitung `qty_kirim × berat_referensi`
    - `calculate_selisih_timbang($berat_aktual, $estimasi_berat)` — hitung selisih_kg dan selisih_pct
    - `validate_stok($produk_fg, $qty_kirim)` — cek qty_kirim tidak melebihi stok available
    - `save_do($data)` — simpan DO Draft + detail (dalam transaksi), validasi stok
    - `save_timbang($do_no, $berat_aktual, $user_id)` — simpan ke `tr_delivery_weight_log`, hitung selisih, baca toleransi dari `ms_config_param`, ubah status DO (Waiting Approval atau Approved Exception)
    - `get_do($do_no)` — ambil satu DO
    - `get_list_for_datatable($params)` — query DataTables
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 7.9_
  - [x] 10.3 Lanjutkan `Delivery_fg_model.php` dengan method approval dan shipping:
    - `approve_do($do_no, $approver_id, $alasan)` — cek self-approval, ubah status ke `Approved Exception`, catat ke `tr_delivery_approval`
    - `reject_do($do_no, $approver_id, $alasan)` — ubah status ke `Draft`, catat ke `tr_delivery_approval`
    - `ship_do($do_no, $user_id)` — ubah status ke `Shipped`, kurangi stok FG via `fg_warehouse_model` (dalam transaksi)
    - `cancel_do($do_no, $user_id)` — ubah status ke `Cancelled`, bebaskan reservasi stok
    - `send_notification_approval($do_no)` — insert notifikasi ke `ms_notification` untuk Manager
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.6, 8.7, 8.8_
  - [x] 10.4 Buat `Delivery_fg.php` controller dengan method:
    - `index()`, `add()`, `edit($do_no)`, `view($do_no)`
    - `data_side_do()` — JSON endpoint DataTables
    - `save_do()` — POST: validasi stok, hitung estimasi berat, simpan DO
    - `save_timbang($do_no)` — POST: simpan berat aktual, hitung selisih, cek toleransi
    - `process_approve($do_no)` — POST: cek self-approval, approve DO, return JSON
    - `process_reject($do_no)` — POST: reject DO + alasan, return JSON
    - `process_ship($do_no)` — POST: konfirmasi shipped, kurangi stok, return JSON
    - `process_cancel($do_no)` — POST: cancel DO, return JSON
    - `cetak_surat_jalan($do_no)` — GET: render PDF/print surat jalan (hanya jika status Approved Exception atau Shipped)
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 7.8, 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7_
  - [x] 10.5 Buat views: `index.php`, `form_do.php` (pilih produk FG + qty + estimasi berat otomatis), `view_do.php` (detail DO + log timbang + riwayat approval), `form_timbang.php`, `surat_jalan.php` (template cetak)
    - _Requirements: 7.1, 7.5, 8.5_
  - [ ]* 10.6 Tulis property test untuk rumus estimasi berat delivery
    - **Property 22: Rumus Estimasi Berat Delivery**
    - **Validates: Requirements 7.2**
  - [ ]* 10.7 Tulis property test untuk validasi stok saat buat Delivery Order
    - **Property 23: Validasi Stok Saat Buat Delivery Order**
    - **Validates: Requirements 7.3**
  - [ ]* 10.8 Tulis property test untuk kontrol status DO berdasarkan selisih berat
    - **Property 24: Kontrol Status DO Berdasarkan Selisih Berat**
    - **Validates: Requirements 7.5, 7.6, 7.7, 7.8**
  - [ ]* 10.9 Tulis property test untuk pencatatan timbang aktual delivery
    - **Property 25: Pencatatan Timbang Aktual Delivery**
    - **Validates: Requirements 7.9**
  - [ ]* 10.10 Tulis property test untuk pencegahan self-approval
    - **Property 26: Pencegahan Self-Approval**
    - **Validates: Requirements 8.4**
  - [ ]* 10.11 Tulis property test untuk shipped mengurangi stok FG
    - **Property 27: Shipped Mengurangi Stok FG**
    - **Validates: Requirements 8.6**
  - [ ]* 10.12 Tulis property test untuk cancel DO membebaskan stok
    - **Property 28: Cancel DO Membebaskan Stok**
    - **Validates: Requirements 8.7**
  - [ ]* 10.13 Tulis property test untuk pencatatan riwayat approval
    - **Property 29: Pencatatan Riwayat Approval**
    - **Validates: Requirements 8.8**

- [ ] 11. Checkpoint Phase 4 — Pastikan semua test lulus, tanyakan kepada user jika ada pertanyaan.

---

## Phase 5: Dashboard Analitis, Supplier Performance, Integrasi Final

- [x] 12. Modul `supplier_performance` — Feed dan Summary Kinerja Supplier
  - [x] 12.1 Buat `Supplier_performance_model.php` dengan method:
    - `get_feed_datatable($params)` — query feed per coil per supplier untuk DataTables
    - `get_summary($supplier_id, $tgl_dari, $tgl_sampai)` — agregasi: SUM reject, NG, KW2, AVG selisih net per supplier per periode
    - `get_summary_datatable($params)` — query summary untuk DataTables
    - `get_dashboard_data($periode)` — data perbandingan kinerja antar supplier untuk chart
    - _Requirements: 9.2, 9.3, 9.4_
  - [x] 12.2 Buat `Supplier_performance.php` controller dengan method:
    - `index()` — halaman summary periodik dengan filter supplier & periode
    - `feed_coil()` — halaman feed per coil
    - `dashboard()` — halaman dashboard perbandingan antar supplier
    - `data_side_feed()` — JSON endpoint DataTables feed
    - `data_side_summary()` — JSON endpoint DataTables summary
    - _Requirements: 9.2, 9.3, 9.4_
  - [x] 12.3 Buat views: `index.php` (summary periodik + filter), `feed_coil.php` (detail per coil), `dashboard.php` (chart perbandingan antar supplier)
    - _Requirements: 9.2, 9.3, 9.4_

- [x] 13. Modul `production_dashboard` — Dashboard Analitis dan Laporan Manajerial
  - [x] 13.1 Buat `Production_dashboard_model.php` dengan method:
    - `get_laporan_timbang_awal($spk_no, $tgl_dari, $tgl_sampai)` — perbandingan net weight timbang awal vs packing list per coil per SPK
    - `get_laporan_hasil_produksi($spk_no)` — total input, breakdown output per kategori, yield per SPK
    - `get_laporan_delivery_discrepancy($tgl_dari, $tgl_sampai)` — selisih estimasi vs aktual per DO
    - `get_laporan_berat_fg($tgl_dari, $tgl_sampai)` — berat acuan standar vs aktual FG per periode
    - `get_dashboard_summary()` — ringkasan produksi, stok FG, delivery untuk dashboard utama
    - _Requirements: 10.1, 10.2, 10.3, 10.4_
  - [x] 13.2 Buat `Production_dashboard.php` controller dengan method:
    - `index()` — dashboard utama dengan ringkasan data
    - `laporan_timbang_awal()` — laporan perbandingan timbang awal
    - `laporan_hasil_produksi()` — laporan hasil produksi per SPK
    - `laporan_delivery_discrepancy()` — laporan selisih berat delivery
    - `laporan_berat_fg()` — laporan berat standar vs aktual FG
    - _Requirements: 10.1, 10.2, 10.3, 10.4_
  - [x] 13.3 Buat views: `index.php` (dashboard dengan chart ringkasan), `laporan_timbang_awal.php`, `laporan_hasil_produksi.php`, `laporan_delivery_discrepancy.php`, `laporan_berat_fg.php`
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

- [x] 14. Helper Barcode Coil dan Parser
  - [x] 14.1 Buat `Coil_barcode_helper.php` dengan fungsi:
    - `coil_barcode_parse($barcode_string)` — parse string barcode menjadi array `[kode_coil, nomor_heat, kode_supplier]`, return error deskriptif jika format tidak sesuai
    - `coil_barcode_format($coil_identity)` — format objek identitas coil kembali ke string barcode standar
    - _Requirements: 11.1, 11.2, 11.3_
  - [ ]* 14.2 Tulis property test untuk round-trip parse barcode coil
    - **Property 30: Round-trip Parse Barcode Coil**
    - **Validates: Requirements 11.1, 11.3, 11.4**
  - [ ]* 14.3 Tulis property test untuk penolakan barcode tidak valid
    - **Property 31: Penolakan Barcode Tidak Valid**
    - **Validates: Requirements 11.2**

- [x] 15. Integrasi Final dan Wiring Antar Modul
  - [x] 15.1 Verifikasi alur end-to-end: pastikan `process_release` di `production_planning` memanggil pembuatan SPK di `production_issue_model`
    - _Requirements: 1.4, 2.1_
  - [x] 15.2 Verifikasi alur posting laporan produksi: pastikan `post_report` di `production_report_model` memanggil `fg_warehouse_model` untuk auto-create FG Receipt Draft dan insert `tr_supplier_perf_feed`
    - _Requirements: 4.8, 4.9, 6.1_
  - [x] 15.3 Verifikasi alur shipped delivery: pastikan `ship_do` di `delivery_fg_model` memanggil `fg_warehouse_model->update_stok_out()` dalam satu transaksi
    - _Requirements: 8.6_
  - [x] 15.4 Verifikasi alur check_and_close_plan: pastikan saat SPK terakhir di-close, `production_planning_model->check_and_close_plan()` dipanggil dan status plan berubah ke `Closed`
    - _Requirements: 1.6_
  - [ ]* 15.5 Tulis integration test untuk alur lengkap posting laporan produksi (mock database)
    - Test: submit → approve → post → FG Receipt Draft terbuat → supplier_perf_feed tercatat
    - _Requirements: 4.5, 4.6, 4.8, 4.9, 6.1_
  - [ ]* 15.6 Tulis integration test untuk alur approval exception delivery
    - Test: buat DO → input timbang melebihi toleransi → Waiting Approval → approve (bukan self) → Approved Exception → cetak surat jalan → shipped → stok berkurang
    - _Requirements: 7.6, 8.2, 8.4, 8.5, 8.6_

- [ ] 16. Checkpoint Final — Pastikan semua test lulus, tanyakan kepada user jika ada pertanyaan.

---

## Catatan Implementasi

- Task bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Setiap task mereferensikan requirements spesifik untuk traceability
- Semua operasi multi-tabel wajib menggunakan `$this->db->trans_start()` / `$this->db->trans_complete()`
- Nilai toleransi selalu dibaca dari `ms_config_param` via helper, tidak hardcoded
- Self-approval dicegah di semua controller approval dengan pengecekan `created_by == session user_id`
- Property test menggunakan 100 iterasi per property sesuai design
