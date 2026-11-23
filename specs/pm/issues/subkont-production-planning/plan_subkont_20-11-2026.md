# Plan Implementasi — Proses Subkont (FEAT-12) — 20-11-2026

Tahap Handover BA → Dev. Sumber kebenaran: `specs/docs/fr-nfr.md` (FR-99–FR-125, NFR-18–NFR-21, OQ-S1–S12 terjawab), ERD `specs/docs/diagrams/erd-revisi.drawio` halaman "Subkont ERD", mockup `specs/ui-mockup/sk-*.html`, walkthrough `.artifacts/mockup_subkont/walkthrough_mockup_subkont_20-11-2026.md`. **Jangan mengarang** alur, rumus, status, atau nama field di luar dokumen itu; bila ada celah, tanya BA.

Dokumen berikutnya: `task_subkont_20-11-2026.md` (centang pekerjaan) → `walkthrough_subkont_<tanggal>.md` (bukti kesesuaian kode vs spesifikasi).

## 1. Ringkasan
Alur: **SPK Subkont → Request ke Gudang → Tampungan Siap Kirim → Surat Jalan (Confirm) → Portal Vendor (LHP) → Incoming → Temuan/Konfirmasi → jurnal + costbook**. Dua jenis: **Material (slitting, satuan Pack)** dan **FG (tambah pasir, satuan pcs)**. Vendor hanya ditentukan saat Surat Jalan; SPK tidak punya vendor dan tidak punya relasi PO.

## 2. Fondasi yang sudah ada (jangan dibuat ulang)
| Kebutuhan | Pakai |
|---|---|
| Master Supplier | modul `supplier` (`new_supplier`: id, nama, address, dll.) |
| SPK / perencanaan produksi | modul `production_planning` (diperluas, bukan diganti) |
| Surat Jalan / delivery | modul `delivery_fg` (diperluas) |
| Stok coil / pack | `warehouse_stock_coil` (kolom `kd_gudang`, `harga_beli`, `no_coil`) |
| Stok FG | `fg_stock` (`produk_fg`, `qty_stok`, `total_berat`) |
| Jurnal | `gl_interface` + `Gl_interface_model::generate_jurnal_dari_template()` + `ms_jurnal_mapping` (pola: `claim_material/migrations/003`) |
| Log audit | `write_log()` modul **"Subkont"**, format before/after (AGENTS.md) |
| Hak akses & menu | tabel `menus`, `permissions`, `group_permissions` (pola: `claim_material/migrations/002`) |
| Pola modul HMVC | `application/modules/claim_material/` (controller + model + views + migrations) |

## 3. Struktur modul usulan
Satu modul baru `application/modules/subkont/` untuk halaman internal HFG (SPK, Tampungan, Surat Jalan, Incoming, Laporan Nilai, Saldo, Link Portal Vendor di Master Supplier, Laporan Vendor). **SPK** dan **Surat Jalan** boleh tetap menjadi menu di `production_planning` / `delivery_fg` bila lebih sesuai dengan keputusan "perluasan dua modul" (FR-99); pilih satu dan konsisten dengan permission FR-124.
Portal Vendor: **aplikasi terpisah secara akses**, bukan `Admin_Controller`. Usulan: modul `vendor_portal` dengan controller dasar sendiri (`Vendor_Controller`), sesi/cookie terpisah dari pengguna HFG, layout sederhana, rute berawalan `portal/`. (Keputusan arsitektur akhir ada pada programmer; syarat mutlak = NFR-18.)

## 4. Urutan pengerjaan (stage)
Tiap stage selesai bila: migrasi/kolom sesuai ERD, halaman sesuai mockup (label = nama kolom ERD), `write_log()` terpasang, walkthrough manual lulus di `hfg_dev`, `task_subkont` dicentang.

### Stage 0 — Prasyarat database (tulis, jalankan manual oleh user)
- Jalankan `application/modules/subkont/migrations/001_create_subkont_tables.sql` di **hfg_dev** (bukan db_hfg_dev). `.down.sql` hanya untuk membatalkan.
- Tulis `002_add_permissions_and_menu.sql` (+down): permission FR-124 (`Spk_Subkont.View/Add/Manage`, `Delivery_Subkont.View/Add/Manage`, `Tampungan_Subkont.View/Manage`, `Subkont_Incoming.View/Manage`, `Vendor_Portal_Link.View/Manage`, plus baca-saja Saldo/Laporan Nilai) dan menu grup **Subkont**.
- Tulis `003_add_ms_jurnal_mapping_subkont.sql` (+down): menu `subkont`, action `confirm_surat_jalan` dan `konfirmasi_incoming`; `kode_master_jurnal` kosong sampai Accounting membuat Master Jurnal (pola Claim Material D4).
- Tulis `004_widen_fg_stock_produk_fg.sql` (+down): kode FG baru bisa > 50 karakter (lihat §6-1). **Perlu konfirmasi BA sebelum dijalankan** karena mengubah tabel yang sudah ada.

### Stage 1 — Link Portal Vendor di Master Supplier (HFG) · FR-113, FR-125
**Keputusan user 2026-11-20: portal TIDAK memakai pendaftaran, akun, login, atau verifikasi (bukan E-Procurement).** Mockup: `sk-master-supplier.html`. Master Supplier **memakai modul `supplier` yang ada** (mockup hanya meniru); yang baru hanya kolom dan aksi **Link Portal Vendor** pada daftar supplier: **Buat Link Portal** (token acak unik, tampilkan link untuk disalin), **Lihat/Salin Link**, **Buat Ulang Link** (link lama langsung mati), **Nonaktifkan Link**; status Belum ada link / Link Aktif / Link Nonaktif dan waktu terakhir dibuka. Tabel `ms_vendor_portal_link`. Aktor: Purchasing. Satu supplier hanya satu link aktif (riwayat tetap tersimpan).

### Stage 2 — SPK Subkont · FR-100–FR-103
Mockup: `sk-spk.html`. Tabel `tr_spk_subkont_header/detail`. Daftar + Add/Edit/Detail, Add Item (stok Material = pack di gudang produksi yang belum di Tampungan; stok FG dari `fg_stock` dikurangi yang ada di Tampungan), **Request Item to Warehouse** (Draft→Requested), **Cancel** (Draft, atau Requested tanpa barang di Tampungan). Penomoran `SPK-Sub-NNN`. Permintaan tidak mengubah stok.

### Stage 3 — Tampungan Siap Kirim · FR-104
Mockup: `sk-tampungan.html`. Tabel `tr_subkont_tampungan`. Tab Permintaan SPK + Isi Tampungan (scan QR/pilih pack untuk Material; qty untuk FG), boleh sebagian, hapus baris hanya saat `Siap Kirim`. **Tidak ada jurnal** (hanya pemisahan fisik; akun tetap Inventory). Satu pack hanya di satu baris aktif (validasi aplikasi).

### Stage 4 — Surat Jalan Subkont · FR-105–FR-112
**Keputusan 2026-11-20: satu Surat Jalan boleh berisi beberapa SPK, boleh campur Material dan FG, tetapi satu vendor.** `spk_no` ada di `tr_subkont_delivery_detail` (bukan di header); header punya `total_net_weight` dan `total_qty_pcs`. Confirm membuat satu jurnal (Db Material In Supplier = total; Cr Persediaan Bahan Baku untuk barang SPK Material dan Cr Persediaan Barang Jadi untuk barang SPK FG), satu baris ledger Kirim per SPK, dan memajukan status tiap SPK. Laporan Subkont (LHP) tetap per (Surat Jalan, SPK, material). Contoh uji: Material 37.980.000 + FG 14.500.000 = Db 52.480.000.

Mockup: `sk-surat-jalan.html`. Tabel `tr_subkont_delivery/_detail`, `subkont_in_supplier_ledger`. Save → Draft (barang `Terkunci di Surat Jalan`, stok belum berkurang, costbook belum diambil). **Confirm** (satu transaksi DB, NFR-19): ambil costbook, hitung nilai, jurnal **Db Material In Supplier / Cr Inventory (Bahan Baku atau Barang Jadi)**, tampungan → `Terkirim`, tulis ledger **Kirim**, status SPK Dikirim Sebagian/Penuh (atau tetap `Incoming` bila sudah ada Incoming), daftar coil muncul di Portal. Vendor dipilih dari **seluruh Master Supplier**. Print: tanpa costbook/nilai (FR-110). Penomoran `DV-SUB-NNN`; No. Surat Jalan teks bebas (unik per vendor).
Sumber costbook: Material = costbook gudang HFG per kg (mockup memakai konstanta; **tentukan sumber nyata bersama BA**, §6-2); FG = costbook per pcs dari `fg_stock`.

### Stage 5 — Portal Vendor · FR-113–FR-117, NFR-18
Mockup: `sk-portal.html?t=<token>`. **Vendor membuka link dari HFG (tanpa pendaftaran, login, atau password)** → Surat Jalan Masuk (Confirmed saja) → Daftar Laporan per material → Input LHP Material (`tr_laporan_subkont`, `_detail`, `_baby`) / FG (`_fg`) → Close Laporan → Ajukan Perbaikan (`tr_laporan_subkont_revisi`, asal "Pengajuan Vendor"). Link tidak valid / dinonaktifkan / sudah digantikan ditolak (halaman "Link Portal Tidak Valid").
Wajib: token dibuat dengan generator acak aman (mis. `random_bytes(16)` → 32 hex) dan unik, tidak ditulis ke log, CSRF, sesi/rute terpisah dari pengguna HFG, setiap query dibatasi `id_vendor` milik link, **tidak ada kolom costbook/nilai/biaya jasa** pada query/respons portal, log aksi vendor dengan identitas vendor pemilik link, `last_access_at` diperbarui saat dibuka. Rumus: baby coil `{kode_internal}-BC.{n}` (n berurut per kode internal), Net Weight per Roll = Nett Weight Actual ÷ total roll; Total Nett Production = Actual + Scrap; Selisih (kg/%) terhadap Before. Selisih > 0,7% **tetap boleh Save/Close dengan peringatan** (keputusan user). Kode FG baru di portal **hanya pratinjau**.

### Stage 6 — Laporan Subkont Vendor (HFG) · FR-117 / UC-25
Mockup: `sk-laporan-vendor.html`. Daftar LHP + View + **Buka Akses Edit** atas pengajuan perbaikan (rev `Diajukan→Dibuka`, laporan `Dibuka Kembali`); saat vendor Close lagi rev menjadi `Ditutup`.

### Stage 7 — Incoming + Temuan + Konfirmasi + Laporan Nilai Slitting · FR-118–FR-123
**Costbook (keputusan 2026-11-20):** costbook dasar Material = Purchase Price (Avg) di Stock Value (modul warehouse, `warehouse_stock_history`); Confirm Surat Jalan = OUT (harga tetap); Konfirmasi Incoming = IN baby coil pada costbook baru, harga rata-rata dihitung ulang. FG: costbook produk asal dari `fg_stock`; Confirm Surat Jalan = OUT di kartu stok; Incoming = IN kode FG baru pada costbook baru. Stage 4 juga mengambil costbook dari sumber ini (bukan konstanta).

**Revisi 2026-11-20: Incoming TANPA input berat** (baby coil masih terbungkus). Cek Material = Nett Weight dari Supplier (LHP: Nett Weight Actual + Total Scrap) vs Nett Weight Before di Packing List, toleransi ±0,7%, dihitung otomatis dan disimpan di `tr_subkont_incoming_laporan` (`nett_weight_packing_list`, `nett_weight_supplier`, `selisih_persen`, `status_cek`); SPK FG tanpa cek berat. Tabel `tr_subkont_incoming_detail` sudah dihapus dari ERD dan migrasi.

Mockup: `sk-incoming.html`, `sk-nilai-slitting.html`. Tabel `tr_subkont_incoming`, `_laporan`, `_detail`. **Satu Incoming = satu vendor, beberapa Laporan Closed.** Cek otomatis Packing List vs LHP (lihat revisi di atas), toleransi **±0,7%**. **Tidak sesuai** → Catat Temuan (rev asal "Temuan Incoming", status Open, laporan `Dibuka Kembali`; laporan keluar dari Incoming). **Tutup Temuan** oleh PPIC (setelah vendor Close ulang) = HFG menerima selisih. **Konfirmasi** per Incoming (satu transaksi DB): baby coil → `warehouse_stock_coil` Gudang Produksi 2 (PRT) dengan `harga_beli` = costbook baru; scrap 100% ke Inventory Scrap; FG baru (No. Urut Subkont resmi dibuat di sini) → `fg_stock` KW 1; jurnal gabungan satu `gl_interface`; ledger **Terima**; costbook baru; status SPK `Incoming`/`Closed`.
Rumus (FR-120/122/123), contoh uji (NFR-20, angka dari mockup/workbook): Material before 6.120, actual 6.098, scrap 14, selisih 8, costbook 14.850, jasa Rp 270,27/kg → Biaya Subkont 1.648.106,46; Db Persediaan Bahan Baku 92.203.406,46 + Inventory Scrap 207.900 + Selisih Net Weight 118.800 = Cr Material In Supplier 90.882.000 + Cr Unbill 1.648.106,46 (total 92.530.106,46, selisih jurnal 0); costbook baru 15.120,27/kg. FG: nilai kirim 14.500.000 + jasa Lot 2.500.000 → Db Persediaan Barang Jadi 17.000.000; costbook FG baru 708.333,33/pcs.
Biaya jasa hanya diinput PPIC HFG (Per Kg atau Lot) dan **tidak pernah** terlihat vendor.

### Stage 8 — Saldo Material In Supplier (Accounting, baca saja) · FR-112 / UC-N38
Mockup: `sk-saldo-supplier.html`. Baca `subkont_in_supplier_ledger`; saldo nol setelah seluruh barang yang dikirim diterima dan dikonfirmasi.

## 5. Lintas stage
- **Audit:** `write_log('Subkont', ...)` untuk Create, Edit, Request, Scan Tampungan, Confirm Surat Jalan, Close Laporan, Buka Akses Edit, Tutup Temuan, Setujui Akun Vendor, Konfirmasi Incoming, Jurnal. Update memakai snapshot `old/new`; header-detail memakai `detail_changes`.
- **Transaksi:** Confirm Surat Jalan dan Konfirmasi Incoming dalam satu `trans_start()/trans_complete()` (stok + jurnal + ledger + status); gagal jurnal = batal seluruhnya, atau ditandai gagal dan dicatat (ikuti pola Claim Material bila BA setuju).
- **Penamaan seragam:** nama tabel/kolom di kode = ERD; header kolom di tampilan = nama kolom (underscore→spasi, kapital awal). Tidak ada alias diam-diam.
- **Format angka:** input harga/nilai berformat ribuan titik dan desimal koma (`numfmt.js` di mockup → padanan di aplikasi).
- **Kode akun jurnal sementara** (OQ-S9): jangan di-hardcode; ambil dari Master Jurnal Mapping.

## 6. Titik yang perlu keputusan BA/programmer sebelum atau saat koding
1. `fg_stock.produk_fg` VARCHAR(50) terlalu pendek untuk kode FG baru (`Nama Produk/Kode Internal/No. Urut`): lebarkan (migrasi 004) atau definisikan ulang format kode.
2. **Sumber costbook Material** saat Confirm Surat Jalan (mockup memakai angka contoh): tentukan (mis. `costbook_stock`/`harga_beli` coil) bersama BA.
3. Identitas pengaju pada `tr_laporan_subkont_revisi` (`diajukan_oleh`, `ditutup_by`): bertipe teks karena bisa akun vendor atau user PPIC.
4. Kode produk FG di laporan tidak ber-FK ke `product_lvl_4` (kolom `code_lv4` tidak unik).
5. Master Jurnal (Accounting) untuk `confirm_surat_jalan` dan `konfirmasi_incoming` belum ada; sampai dibuat, perilaku jurnal kosong mengikuti keputusan Claim Material (D4).
6. Pengiriman email/notifikasi (pengiriman link portal ke vendor, pengajuan perbaikan) **tidak ada di dokumen**; jangan dibuat tanpa keputusan BA.
7. Pelunasan Unbill (Jasa Subkont) memakai alur Receive Invoice/pembayaran PO Jasa yang sudah ada; **PO tidak dihubungkan ke SPK** (FR-99). Tidak ada pekerjaan baru di sini kecuali BA meminta.

## 7. Pengujian (manual, `hfg_dev`)
Belum ada infrastruktur uji PHP. Verifikasi dengan skrip SQL baca-saja dan walkthrough: ulangi skenario mockup (Surat Jalan Material dan FG, LHP dengan selisih besar + peringatan, Incoming Sesuai, Incoming Tidak Sesuai → Temuan → vendor revisi → Tutup Temuan → Konfirmasi, Incoming dua laporan sekaligus) dan cocokkan angka dengan contoh di Stage 7. Cek isolasi vendor (akun A tidak bisa membuka data vendor B lewat ID di URL) dan bahwa respons portal tidak memuat costbook/nilai.

8. **Penyimpanan `token_link`:** mockup menyimpan token apa adanya agar HFG dapat melihat/menyalin link lagi. Programmer menentukan: simpan token apa adanya (link dapat ditampilkan ulang) atau simpan hash dan tampilkan link hanya saat dibuat (lebih aman; bila hilang, Buat Ulang Link). Syarat tetap: acak, unik, panjang, tidak masuk log.
