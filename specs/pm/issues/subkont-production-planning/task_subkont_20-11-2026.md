# Task — Proses Subkont (FEAT-12) — 20-11-2026

Rujukan: `plan_subkont_20-11-2026.md`. Centang saat selesai dan terverifikasi di `hfg_dev`.

## Stage 0 — Database
- [ ] Jalankan `001_create_subkont_tables.sql` di hfg_dev (oleh user) dan cek 15 tabel terbentuk
- [ ] `002_add_permissions_and_menu.sql` (+down)
- [ ] `003_add_ms_jurnal_mapping_subkont.sql` (+down)
- [ ] `004_widen_fg_stock_produk_fg.sql` (+down) — setelah BA setuju (plan §6-1)

## Stage 1 — Link Portal Vendor di Master Supplier (FR-113, FR-125)
- [ ] Kolom Link Portal Vendor di daftar Master Supplier (Belum ada link / Link Aktif / Link Nonaktif, terakhir dibuka)
- [ ] Buat Link Portal (token acak unik), Lihat/Salin Link
- [ ] Buat Ulang Link (link lama mati), Nonaktifkan Link
- [ ] Validasi satu supplier = satu link aktif; catat created_by/at dan nonaktif_by/at
- [ ] write_log "Subkont"

## Stage 2 — SPK Subkont (FR-100–FR-103)
- [ ] Daftar, Add/Edit/Detail, penomoran SPK-Sub-NNN
- [ ] Add Item (stok Material per pack, stok FG) + validasi qty
- [ ] Request Item to Warehouse, Cancel
- [ ] write_log (before/after, detail_changes)

## Stage 3 — Tampungan Siap Kirim (FR-104)
- [ ] Tab Permintaan SPK + Isi Tampungan
- [ ] Scan QR / pilih pack (Material), qty (FG), hapus baris Siap Kirim
- [ ] Validasi satu pack satu baris aktif; boleh sebagian

## Stage 4 — Surat Jalan Subkont (FR-105–FR-112)
- [ ] Daftar + Add/Edit (vendor dari Master Supplier, scan/pilih dari Tampungan, ringkasan Diminta/Dikirim/Dipilih/Sisa)
- [ ] Save Draft (kunci barang)
- [ ] Confirm: costbook, nilai, jurnal Db Material In Supplier / Cr Inventory, tampungan Terkirim, ledger Kirim, status SPK
- [ ] Print tanpa costbook/nilai; varian FG
- [ ] Tentukan sumber costbook Material (plan §6-2)

## Stage 5 — Portal Vendor (FR-113–FR-117, NFR-18)
- [ ] Controller dasar terpisah dari pengguna HFG, rute portal berbasis token
- [ ] Akses lewat link (token valid dan aktif); link tidak valid ditolak; last_access_at diperbarui
- [ ] Surat Jalan Masuk + Daftar Laporan
- [ ] Input LHP Material (baby coil, rumus selisih, peringatan > 0,7%)
- [ ] Input LHP FG (material tambahan, qty/berat hasil, pratinjau kode FG baru)
- [ ] Close Laporan, Ajukan Perbaikan
- [ ] Uji isolasi data antar vendor; pastikan tidak ada costbook/nilai di portal
- [ ] Log aksi vendor

## Stage 6 — Laporan Subkont Vendor (UC-25)
- [ ] Daftar LHP + View
- [ ] Buka Akses Edit atas pengajuan perbaikan; Close ulang menutup revisi

## Stage 7 — Incoming (FR-118–FR-123)
- [ ] Add/Edit Incoming (satu vendor, beberapa Laporan Closed)
- [ ] Cek otomatis Packing List vs LHP ±0,7% (Material; FG tanpa cek berat; tanpa input berat)
- [ ] Biaya jasa Per Kg / Lot per laporan
- [ ] Catat Temuan (laporan keluar dari Incoming, akses edit dibuka) + tab Temuan + Tutup Temuan
- [ ] Konfirmasi: warehouse_stock_coil PRT, scrap, fg_stock KW 1 + No. Urut Subkont, jurnal gabungan, ledger Terima, costbook baru, status SPK
- [ ] Laporan Nilai Slitting (HFG saja)
- [ ] Cocokkan angka uji (plan Stage 7)

## Stage 8 — Saldo Material In Supplier
- [ ] Ringkasan per vendor + SPK dan ledger (baca saja), filter

## Penutup
- [ ] `walkthrough_subkont_<tanggal>.md` (kesesuaian kode vs spesifikasi)
- [ ] Perbarui `specs/pm/tracker.yaml` FEAT-12 dan `specs/pm/log.md`
