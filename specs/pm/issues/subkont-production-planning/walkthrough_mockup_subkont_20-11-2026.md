# Walkthrough Mockup Subkont (FEAT-12) — 20-11-2026

Mockup statis di `specs/ui-mockup/` (prefix `sk-`). Data contoh di localStorage (KEY `mockSK.data5`), tombol "Reset ke Data Contoh" ada di setiap halaman.

| Tahap | Halaman | File | FR / UC |
|---|---|---|---|
| 1 | SPK Subkont, Tampungan Siap Kirim | sk-spk.html, sk-tampungan.html | FR-100–FR-104 |
| 2 | Surat Jalan Subkont | sk-surat-jalan.html (+ sk-sj.js) | FR-105–FR-112 |
| 3 | Portal Vendor (aplikasi terpisah) | sk-portal.html (+ sk-portal.js) | FR-113–FR-117, NFR-18 |
| 4 | Master Supplier (Link Portal Vendor), Laporan Subkont Vendor (UC-25) | sk-master-supplier.html, sk-laporan-vendor.html (+ sk-hfg.js) | FR-117, FR-125 |
| 5 | Incoming, Temuan, Laporan Nilai Slitting, Saldo Material In Supplier | sk-incoming.html, sk-nilai-slitting.html, sk-saldo-supplier.html (+ sk-inc.js) | FR-112, FR-118–FR-123 |

## Keputusan user yang diterapkan
- Selisih > 0,7% di portal: tetap boleh Save/Close dengan peringatan (HFG menilai saat Incoming).
- Kode FG baru: pratinjau di portal; nomor urut resmi saat Konfirmasi Incoming.
- Vendor Surat Jalan dari seluruh Master Supplier (tanpa syarat link portal aktif).

## Asumsi mockup yang perlu dikonfirmasi BA (tidak ada di FR/ERD)
1. Incoming dibuat **satu per Laporan Subkont Closed** (ERD `tr_subkont_incoming.no_laporan` satu nilai). FR-118 menyebut satu Surat Jalan vendor boleh mencakup beberapa laporan: dibuat beberapa Incoming dengan No. Surat Jalan Vendor yang sama.
2. **ERD gap FG:** `tr_subkont_incoming_detail` hanya punya `id_laporan_subkont_baby` (khusus Material). Untuk SPK FG mockup mencek per produk berdasarkan berat (Total Berat LHP vs diterima); perlu kolom `id_laporan_subkont_fg` atau tabel detail lain.
3. **Konfirmasi saat Tidak Sesuai:** mockup mengizinkan Konfirmasi bila semua baris Sesuai (±0,7%) **atau** Temuan Incoming untuk laporan itu sudah Ditutup PPIC. FR-119 hanya menyebut Temuan ditutup PPIC; apakah penutupan berarti menerima selisih belum tertulis.
4. **Temuan** disimpan di `tr_laporan_subkont_revisi` (asal "Temuan Incoming", status Open → Ditutup); tidak ada tabel temuan terpisah di ERD.
5. **Costbook FG baru** per pcs disimpan di `fg_stock`; kolom `costbook_baru_per_kg` pada incoming di mockup berisi nilai per kg total hasil. Perlu kepastian kolom mana yang dipakai.
6. **Status SPK:** setelah Incoming pertama berstatus Incoming (juga saat masih Dikirim Sebagian); Closed bila Dikirim Penuh dan seluruh Surat Jalan/laporan sudah Konfirmasi.
7. Pembagian berat baby coil: Nett Weight Actual ÷ total roll seluruh pack (dibagi rata); penomoran `{Kode Internal}-BC.n` berurut per Kode Internal.
8. ERD stub `new_supplier.alamat` dikoreksi menjadi `address` (sesuai tabel asli).

## Hasil uji (Chrome headless)
Semua alur diuji: SPK→Tampungan→Surat Jalan (Material & FG)→Portal (daftar, login, input, Close, ajukan perbaikan)→Buka Akses Edit→Incoming Sesuai/Tidak Sesuai→Temuan→Tutup Temuan→Konfirmasi→jurnal seimbang (contoh Material: Db 92.530.106,46 = Cr 92.530.106,46; FG: 17.000.000 = 17.000.000), ledger Kirim/Terima, saldo 0, status SPK.

## Keputusan BA/user atas asumsi di atas (20-11-2026, menyusul)
1. Konfirmasi setelah temuan: **menutup temuan = HFG menerima selisih** (Konfirmasi boleh bila Sesuai atau temuan Ditutup). Diterapkan di FR-119 dan mockup.
2. ERD gap FG: **disetujui**; `tr_subkont_incoming_detail` memakai `id_laporan_subkont_baby` (Material) atau `id_laporan_subkont_fg` (FG).
3. **Satu Incoming berisi beberapa Laporan** (satu vendor). ERD: `tr_subkont_incoming` (header) + `tr_subkont_incoming_laporan` (baru) + `tr_subkont_incoming_detail`. Mockup Incoming dibangun ulang; asumsi tambahan: laporan yang dicatat Temuan otomatis keluar dari Incoming.
4. Costbook baru: `costbook_baru_per_kg` hanya Material; `costbook_baru_per_pcs` hanya FG (diteruskan ke fg_stock). Kolom baru ditambahkan di ERD.

## Perubahan menyusul (20-11-2026): satu Surat Jalan boleh beberapa SPK
Keputusan user: satu Surat Jalan boleh berisi beberapa SPK, boleh campur Material dan FG, satu vendor per Surat Jalan. Dampak yang sudah diterapkan: FR-105–FR-112 dan FR-114; ERD (`spk_no` pindah ke `tr_subkont_delivery_detail`, header `total_net_weight` + `total_qty_pcs`, kolom `spk_no` dan `jenis_subkont` header dihapus); SQL migrasi 001 (belum dieksekusi); mockup Surat Jalan dibangun ulang (tambah beberapa SPK, scan lintas SPK, jurnal gabungan, print dengan kolom No. SPK); Portal Vendor (baris laporan per SPK + material) dan Incoming (status SPK per SPK). **Workbook** (`docs/workbook.xlsx`: UC BPMN, Pages, Entities) sudah disinkronkan pada 20-11-2026. Sheet mentah klien di `specs/input/` tidak diubah (append-only).

## Perubahan menyusul (20-11-2026): Incoming tanpa input berat
Keputusan user: baby coil masih terbungkus, jadi berat tidak diinput ulang di Incoming; yang divalidasi = Nett Weight dari Supplier (LHP Actual + Scrap) vs Packing List (Before), ±0,7%; FG tanpa cek berat. Dampak: FR-118/119, OQ-S1, ERD (tabel detail cek dihapus, tiga kolom snapshot di `tr_subkont_incoming_laporan`), SQL 001 (14 tabel), workbook, mockup Incoming. Bug Portal (Edit LHP menghilangkan `kode_barang`) ditemukan dan diperbaiki pada hari yang sama.

## Perubahan menyusul (20-11-2026): Portal Vendor tanpa akun (bukan E-Procurement)
Keputusan user: vendor tidak mendaftar dan tidak diverifikasi; HFG melihat vendor di Master Supplier lalu memberikan **link portal unik (token)**. Jawaban user atas pertanyaan agen: tanpa password. Dampak: FR-113 dan FR-125 ditulis ulang; OQ-S6/OQ-S12(a) ditandai diganti; NFR-18 (token); ERD: `ms_vendor_portal_account` diganti `ms_vendor_portal_link`; SQL 001 (14 tabel); workbook (UC-22, UC-N39, PG-43, PG-50, Entities); mockup: halaman Persetujuan Akun Vendor dihapus, portal dibuka lewat `sk-portal.html?t=<token>`, Master Supplier punya aksi link. Catatan: nama peran/UC tetap (UC-22 = Buka Portal lewat Link; UC-N39 = Kelola Link Portal).
