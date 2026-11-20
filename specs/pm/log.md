# Workflow Log

Narrative record of what happened and why. **Newest entry at the top.**
Append-only: never edit or delete a past entry — a log that gets corrected in
place is a log nobody can trust as evidence. A superseded entry is corrected by
a new entry that says so.

## What goes here, and what does not

This file records **events**. `context/` records **truth**. The test: if the
sentence would still be worth reading when the issue is long closed, it belongs
in `context/`, not here.

| | Goes to |
|---|---|
| "FIX-12 closed, verified against the failing case" | here |
| "The gateway style attribute is `gwType=`, not `symbol=`" | `context/` — via `context/gotchas.md` |
| "We chose X over Y, and here is what it costs" | `context/index/decisions/` + a row in `decisions.md` |
| "What is open, what is next" | `pm/tracker.yaml` |

A durable fact that lands only here is lost: nothing reads this file at session
start, by design.

## Entry format

One `-` bullet per event, newest first:

```markdown
- **YYYY-MM-DD** `[TAG][ID]` **Headline in bold.** What changed, what it was
  verified against, and anything a future reader would otherwise have to
  reconstruct. Inline `[TAG]`s are fine mid-entry when one event carried
  several kinds of outcome.
```

`ID` is the issue id, or omitted for work that never became an issue.

| Tag | For |
|---|---|
| `[STATUS]` | An issue opened, moved, closed, or was dropped |
| `[DECISION]` | A choice was made — record the alternative that was rejected |
| `[DISCOVERY]` | Something turned out not to be what it looked like |
| `[TODO]` | Something was deliberately deferred, and what would settle it |

---


- **2026-11-20** `[DECISION][FEAT-12]` **Portal Vendor tanpa akun: akses lewat link unik dari Master Supplier.** Keputusan user: tidak jadi seperti E-Procurement; vendor tidak mendaftar dan tidak diverifikasi; HFG melihat vendor di daftar Master Supplier lalu memberikan link portal. Jawaban user atas pertanyaan agen: link unik per vendor (token), tanpa password. Diterapkan: FR-113 dan FR-125 ditulis ulang, FR-124 (permission `Vendor_Portal_Link.View/Manage`), NFR-18 (token acak/unik/tidak di log), OQ-S6/OQ-S12(a) ditandai diganti, UC-22 (Buka Portal lewat Link) dan UC-N39 (Kelola Link Portal Vendor) di workbook beserta PG-43/PG-44/PG-50/PG-51, Entities; ERD: tabel `ms_vendor_portal_account` diganti `ms_vendor_portal_link` (id, id_vendor, token_link, status_link, created_by, created_at, nonaktif_by, nonaktif_at, last_access_at; dirender dan dilihat); SQL 001 (14 tabel, 14/14 nama cocok ERD); mockup: `sk-akun-vendor.html` dihapus, `sk-portal.html?t=<token>` (link tidak valid/dinonaktifkan ditolak; tanpa login/daftar), Master Supplier punya kolom dan aksi Buat Link Portal / Lihat dan Salin / Buat Ulang / Nonaktifkan, petunjuk di Surat Jalan bila vendor belum punya link; data lama dimigrasi otomatis (akun dibuang, link contoh vendor 23). Diuji: buat link -> vendor membuka -> hanya data vendor itu; token salah/tanpa token ditolak; Buat Ulang mematikan link lama; Nonaktifkan menolak akses. Catatan proses: komentar akhir baris tidak dipakai lagi; semua penulisan file dilakukan dengan hasil dihitung dulu.

- **2026-11-20** `[DISCOVERY][FEAT-12]` **Stok Material Subkont disatukan dengan Stock Gudang Coil dan Stock Value.** Laporan user: material pada SPK-Sub-006 (GALVALUM MOTHER NON BORON 0.35-1219-AZ100-G550, = MTR-002) tidak terlihat di stok. Penyebab: pack contoh Subkont (`PCK-2609-xxx`) adalah data terpisah dan tidak ada di daftar stok gudang (`PCK-2610/2611-xxx`). Perbaikan di mockup: (1) 10 pack contoh Subkont ditambahkan sekali ke stok gudang (MTR-002: PCK-2609-001..004 di Production + 099 di Slitting; MTR-001: 011..013; MTR-004: 021..022), tanpa menghapus data lain; (2) pack yang sudah dikirim ke vendor (Surat Jalan Subkont Confirmed) dikeluarkan dari stok gudang dan nilainya tercatat sebagai OUT Subkont di riwayat Stock Value (anchor riwayat disesuaikan agar tidak terhitung dua kali); barang di Tampungan Siap Kirim tetap di stok (FR-104); (3) layar Incoming -> View memuat tabel **Validasi Costbook di Stock Value**: kondisi sebelum Subkont (qty, saldo nilai, harga), lalu tiap transaksi Subkont (OUT harga rata-rata, IN costbook baru) dengan qty, nilai, saldo, dan harga rata-rata akhir. Uji end-to-end (data awal MTR-002: Production 7 roll 31.635 kg @15.120 = Rp 478.321.200; setelah kirim 1 pack 3.798 kg dan Incoming 3.778 kg pada costbook baru 15.390,27: Production 6 roll 27.837 kg @15.148,91 = Rp 421.700.207,67; saldo riwayat 35.315 kg = Production + Slitting + baby coil di PRT). Catatan: pengurangan stok pack hanya terjadi bila pack ada di data gudang mockup; angka dasar bergantung pada data gudang di browser masing-masing.

- **2026-11-20** `[DECISION][FEAT-12]` **Laporan FG tambah pasir: Berat Tambahan = berat pasir PER PCS.** Keputusan user: berat/pcs produk bertambah dari pasir, sehingga Berat/Pcs Baru = Berat/Pcs Asal (`fg_stock.berat_referensi`) + berat pasir/pcs; Total Berat (kg) = Qty Pcs Hasil x Berat/Pcs Baru, dihitung otomatis (vendor tidak lagi menginput total kg). Diterapkan di FR-116, SQL 001 (`berat_tambahan_kg` DECIMAL(12,4) kg/pcs; `total_berat_kg` otomatis), workbook (UC-23, PG-46), dan mockup Portal Vendor (kolom Berat Tambahan (kg/pcs), Berat/Pcs Asal, Berat/Pcs Baru, Total Berat otomatis saat mengetik) serta tampilan laporan di sisi HFG. Nama kolom ERD tidak berubah (`berat_tambahan_kg`, `total_berat_kg`); label tampilan memakai satuan kg/pcs. Diuji: pasir 0,5 kg/pcs pada PRD-0102 (34,6 kg/pcs) -> 35,1 kg/pcs; 24 pcs = 842,4 kg. Catatan: Biaya jasa Per Kg, costbook FG baru, dan berat_referensi di stok FG baru memakai total kg ini.

- **2026-11-20** `[DECISION][FEAT-12]` **Costbook Subkont bersumber dari Stock Value (Material) dan fg_stock (FG), dan Incoming mempengaruhinya.** Keputusan user: costbook dasar semua material berasal dari menu Stock Value; Incoming Subkont Material mempengaruhi costbook di sana dan menghitung costbook baru; Finish Good sama (Incoming Subkont FG mempengaruhi costbook di Gudang Finish Good). Diterapkan di FR-107, FR-120, FR-123, plan Stage 4/7, workbook (UC-21, UC-27) dan mockup: (1) **Stock Value**: riwayat stok material memuat transaksi Subkont (OUT saat Confirm Surat Jalan pada harga rata-rata berjalan, harga tetap; IN baby coil saat Konfirmasi Incoming pada costbook baru) sehingga Purchase Price (Avg) dihitung ulang (rata-rata bergerak); cuplikan harga dasar ditulis ke penyimpanan browser (`mockWH.price`). (2) **Surat Jalan Subkont** mengambil costbook Material dari harga Stock Value (pemetaan material Subkont -> MTR-001/002/004 menurut nama); tanpa cuplikan dipakai nilai contoh. (3) **fg_stock**: Confirm Surat Jalan FG = OUT pada costbook produk asal (stok PRD-0102 80 -> 55 pada uji), Incoming FG = IN kode FG baru pada costbook baru (nilai tepat 17.000.000). Stok FG yang tersedia untuk Subkont kini ikut berkurang setelah barang Terkirim. Diuji end-to-end lintas halaman: harga rata-rata MTR-001 14.827,91 -> 14.869,70 setelah Subkont. Catatan: qty/roll di Stock Value tidak dikurangi karena pack contoh Subkont terpisah dari coil contoh gudang; hanya pengaruh harga yang tersambung. `[DISCOVERY]` mockup Stock Finish Good (fg_stock) sudah ada sejak sebelumnya (Produksi -> Stock Finish Good dan Kartu Stok; 4 tab KW 1, KW 2, Tong Coil, Scrap, Excel, kartu stok); modul `fg_warehouse` (penerimaan FG, stok_fg, kartu_stok) berbeda dan belum dimockup.

- **2026-11-20** `[STATUS][FEAT-12]` **Hasil Incoming Subkont tersambung ke halaman stok mockup.** Atas permintaan user dan setelah keputusan menu: baby coil (Gudang Produksi 2 / PRT) tampil di **Stock From Warehouse, tab WRH. Production 2** (tempat stok PRT dilihat di aplikasi; `Stock Gudang Coil` memang hanya PRO dan SLI) dengan label Baby Coil Subkont, nomor Incoming, dan Kartu Pemakaian (sumber vendor Subkont); FG baru (kode dengan No. Urut Subkont, KW 1, costbook per pcs, nilai) dan scrap Subkont (100%) tampil di **Stock Finish Good** (tab KW 1 dan Scrap) beserta Kartu Stok. Cara kerja: halaman stok membaca data mockup Subkont dari penyimpanan browser secara baca-saja (`wh-pages2.js`, `fg-data.js`) dan menyegarkan diri saat jendela aktif kembali; `sk-inc.js` menyimpan nama material, SPK, tanggal, dan nilai pada hasil Konfirmasi; `sk-data.js` melengkapi hasil lama saat dimuat. Diuji end-to-end (konfirmasi Incoming dua laporan lalu buka kedua halaman stok); nilai FG baru Rp 17.000.000,00. Catatan: stok pack Material yang dikirim ke vendor belum dikurangi dari Stock Gudang Coil di mockup (tidak diminta); di aplikasi sebenarnya perpindahan stok dilakukan transaksi Confirm Surat Jalan.

- **2026-11-20** `[DECISION][FEAT-12]` **Incoming tanpa input berat; cek = Nett Weight Supplier vs Packing List.** Keputusan user: Nett Weight Baby Coil tidak diinput ulang saat Incoming (masih terbungkus); yang divalidasi hanya Nett Weight dari Supplier vs Packing List. Jawaban user atas pertanyaan agen: Material = **LHP (Nett Weight Actual + Total Scrap) vs Nett Weight Before di Packing List, toleransi ±0,7%**, dihitung otomatis; FG = **tanpa cek berat** (pasir menambah berat). Diterapkan: FR-118, FR-119, OQ-S1 (direvisi); ERD (tabel `tr_subkont_incoming_detail` dihapus; `tr_subkont_incoming_laporan` bertambah `nett_weight_packing_list`, `nett_weight_supplier`, `selisih_persen`); SQL migrasi 001 (14 tabel, 14/14 nama cocok ERD); workbook (UC-26/27, Pages PG-47, Entities); mockup Incoming (tanpa input berat, cek otomatis, daftar baby coil hanya informasi, Temuan menyebut selisih Packing List vs LHP). Diuji: dua laporan Sesuai langsung dikonfirmasi (jurnal 109.530.106,46), laporan selisih 4,951% Tidak Sesuai -> Catat Temuan -> vendor revisi -> Tutup Temuan. `[DISCOVERY]` **bug Portal diperbaiki (laporan yang di-Edit hilang dari daftar):** Edit lalu Save di portal menghapus `kode_barang` laporan (field `kode` tidak ikut saat Edit), sehingga laporan kembali tampil 'Belum Diinput' dan tombol Close hilang; ini penyebab Temuan Incoming tidak bisa ditutup. Diperbaiki, dan data yang sudah rusak di browser dipulihkan otomatis saat halaman dimuat (`repair` di sk-data.js). Ditambah sinkronisasi data antar tab (HFG dan Portal dibuka bersamaan) supaya tidak ada status basi. Catatan proses: komentar akhir baris sempat menelan perintah sesudahnya (kedua kalinya); sekarang komentar ditaruh di baris sendiri.

- **2026-11-20** `[STATUS][FEAT-12]` **`docs/workbook.xlsx` disinkronkan dengan keputusan terbaru.** Sheet UC BPMN: UC-20/21 (satu Surat Jalan boleh beberapa SPK, campur Material dan FG, satu vendor; jurnal dipecah Cr Bahan Baku dan Cr Barang Jadi; ledger Kirim per SPK; status tiap SPK), UC-22 (isian pendaftaran bertambah: nama perusahaan, alamat perusahaan, no. telepon), UC-23 (laporan per Surat Jalan per SPK per material), UC-25 (status Dibuka Kembali), UC-26/27 (satu Incoming = satu vendor beberapa Laporan; laporan bertemuan keluar dari Incoming; menutup temuan = selisih diterima; cek FG per produk; jurnal gabungan; costbook_baru_per_kg Material vs costbook_baru_per_pcs FG; entitas `tr_subkont_incoming_laporan`). Sheet Pages: PG-40/PG-41 diperbarui dan kolom Asal PG-37..PG-51 kini menunjuk file mockup `specs/ui-mockup/sk-*.html`. Sheet Entities: baris `tr_subkont_incoming_laporan` disisipkan. Diverifikasi dengan memuat ulang dan membandingkan dengan cadangan: hanya 30 sel yang berubah dan semua baris lama Entities utuh. Cadangan pra-perubahan ada di scratchpad.

- **2026-11-20** `[DECISION][FEAT-12]` **Satu Surat Jalan boleh berisi beberapa SPK (campur Material dan FG, satu vendor).** Keputusan user (agar fleksibel); jawaban atas pertanyaan agen: campur jenis = boleh, vendor = satu per Surat Jalan. Diterapkan: FR-105/106/107/108/109/110/111/114 (spk_no per baris detail; jurnal gabungan Cr Bahan Baku + Cr Barang Jadi; ledger Kirim per SPK; print dengan kolom No. SPK); ERD halaman Subkont (hapus `spk_no`/`jenis_subkont` dari `tr_subkont_delivery`, `total_qty` diganti `total_net_weight` + `total_qty_pcs`, tambah `spk_no` di `tr_subkont_delivery_detail`; dirender dan dilihat); SQL migrasi 001 diperbarui (15/15 nama cocok ERD); mockup Surat Jalan ditulis ulang dan Portal/Incoming disesuaikan (laporan per Surat Jalan + SPK + material); plan Stage 4. Diuji: Surat Jalan dua SPK (Material 37.980.000 + FG 14.500.000 = Db 52.480.000, dua baris ledger Kirim, dua status SPK), scan pack milik SPK yang belum ditambahkan ditolak, baris Portal per SPK, regresi Incoming dua laporan lulus. `[DISCOVERY]` bug dari tahap 5 ditemukan dan diperbaiki: komentar tak sengaja menelan `W.save(); closeModal(); U.render();` di Confirm Surat Jalan (sk-sj.js), sehingga Confirm tidak menyimpan/menutup; kode ditulis ulang. Belum: workbook.xlsx sheet Surat Jalan masih versi satu SPK.

- **2026-11-20** `[STATUS][FEAT-12]` **Tahap Handover: plan dan task implementasi CI3 disusun.** `.artifacts/subkont/plan_subkont_20-11-2026.md` (9 stage: prasyarat DB, Persetujuan Akun Vendor, SPK Subkont, Tampungan, Surat Jalan, Portal Vendor, Laporan Vendor/UC-25, Incoming, Saldo; fondasi modul yang dipakai ulang, syarat NFR-18, contoh angka uji, titik keputusan) dan `task_subkont_20-11-2026.md` (daftar centang). Titik terbuka untuk BA/programmer: pelebaran `fg_stock.produk_fg` (migrasi 004 belum ditulis), sumber costbook Material saat Confirm Surat Jalan, Master Jurnal untuk `confirm_surat_jalan` dan `konfirmasi_incoming`, tidak ada email/notifikasi di dokumen. Seluruh pekerjaan BA untuk FEAT-12 (dokumen, mockup, ERD, SQL migrasi, plan) selesai; status siap untuk dikerjakan programmer.

- **2026-11-20** `[STATUS][FEAT-12]` **SQL migrasi Subkont ditulis (tidak dieksekusi).** `application/modules/subkont/migrations/001_create_subkont_tables.sql` + `.down.sql`: 15 tabel baru sesuai ERD (urutan mengikuti FK; FK melingkar tampungan <-> delivery_detail ditambah lewat ALTER; CHECK pada `tr_subkont_incoming_detail` agar tepat satu dari baby/FG terisi; UNIQUE `no_laporan` pada `tr_subkont_incoming_laporan`). Tipe FK dicocokkan dengan tabel yang ada di `hfg_dev` (baca saja): `new_supplier.id`, `warehouse_stock_coil.id`, `gl_interface.id` semuanya INT InnoDB. Nama tabel/kolom dicek otomatis terhadap ERD: 15/15 cocok. `[DISCOVERY]` (1) ERD `ms_vendor_portal_account` kurang kolom isian pendaftaran: ditambah `nama_perusahaan`, `alamat_perusahaan`, `no_telepon` di ERD, migrasi, dan FR-113 (layout ERD digeser, dirender dan dilihat; cadangan pra-perubahan di scratchpad). (2) `fg_stock.produk_fg` hanya VARCHAR(50) sedangkan kode FG baru `Nama Produk/Kode Internal/No. Urut` bisa lebih panjang: perlu pelebaran saat implementasi (di luar migrasi, dicatat di header SQL). (3) Kolom kode FG di laporan tidak diberi FK ke `product_lvl_4` karena `code_lv4` tidak unik/terindeks. (4) `diajukan_oleh` dan `ditutup_by` pada revisi bertipe VARCHAR karena dapat berisi akun vendor atau user PPIC. Belum: rencana implementasi CI3 (`.artifacts/subkont/plan_subkont.md`), permission/menu, Master Jurnal Mapping.

- **2026-11-20** `[DECISION][FEAT-12]` **Keputusan user atas asumsi mockup Incoming (4 butir) diterapkan.** (1) Menutup temuan oleh PPIC = HFG menerima selisih; Konfirmasi boleh bila tiap laporan Sesuai atau temuannya Ditutup (FR-119). (2) Detail cek FG per produk berdasarkan berat; ERD `tr_subkont_incoming_detail` diberi `id_laporan_subkont_fg` (disamping `id_laporan_subkont_baby`). (3) **Satu Incoming berisi beberapa Laporan** milik satu vendor: ERD dipecah menjadi `tr_subkont_incoming` (header: vendor, No. Surat Jalan Vendor, tgl, status, id_gl_interface), **`tr_subkont_incoming_laporan`** (baru: no_laporan, spk_no, no_po_jasa, tarif, biaya, costbook, status_cek per laporan) dan `tr_subkont_incoming_detail`; FR-118/119/121/123 diperbarui; mockup Incoming dibangun ulang (tambah beberapa laporan, cek per laporan, jurnal gabungan; Laporan Nilai Slitting satu baris per laporan). Asumsi mockup: laporan yang dicatat Temuan otomatis keluar dari Incoming agar laporan lain tidak tertahan. (4) Usulan agen disetujui: `costbook_baru_per_kg` hanya untuk Material dan kolom baru `costbook_baru_per_pcs` hanya untuk FG (diteruskan ke fg_stock). `[DISCOVERY]` insiden: skrip Python mengosongkan `docs/fr-nfr.md` (open('w') sebelum isi dihitung); dipulihkan dari git HEAD + memutar ulang perintah edit dari transkrip (1573 baris, +213 dari HEAD, diverifikasi dengan nomor baris FR yang tercatat); cadangan pra-Incoming ERD dan pelajaran dicatat di `context/gotchas.md`.

- **2026-11-20** `[STATUS][FEAT-12]` **Mockup Subkont tahap 5 selesai: Incoming.** `ui-mockup/sk-inc.js`; `sk-incoming.html`, `sk-nilai-slitting.html`, `sk-saldo-supplier.html` (KEY `mockSK.data5`; menu Subkont diperluas). Isi (FR-112, FR-118–FR-123): Incoming per Laporan Closed (No. Surat Jalan Vendor, No. PO Jasa, Tgl Incoming, Jenis Tarif Per Kg/Lot), cek berat timbang vs LHP per baby coil (±0,7%), Catat Temuan (akses edit LHP dibuka ke vendor, asal Temuan Incoming) dan Tutup Temuan oleh PPIC, Konfirmasi Hasil (baby coil ke Gudang Produksi 2 PRT dengan harga_beli = costbook baru, scrap 100% Inventory Scrap, FG baru dengan No. Urut Subkont ke fg_stock KW 1, jurnal Material: Db Persediaan Bahan Baku/Inventory Scrap/Selisih Net Weight, Cr Material In Supplier/Unbill, jurnal FG, ledger Terima, status SPK Incoming/Closed), Laporan Nilai Slitting (HFG saja) dan Saldo Material In Supplier (Accounting, baca saja). Diuji: jurnal seimbang (Material 92.530.106,46; FG 17.000.000), saldo kembali 0, jalur Temuan sampai Konfirmasi. `[DISCOVERY]` beberapa asumsi mockup di luar FR/ERD dicatat di `.artifacts/mockup_subkont/walkthrough_mockup_subkont_20-11-2026.md` untuk ditetapkan BA: gap ERD detail Incoming untuk FG (hanya FK baby coil), Konfirmasi setelah Temuan Ditutup, kolom costbook FG, Incoming satu per laporan. Seluruh mockup Subkont (tahap 1–5) selesai. Berikutnya: keputusan BA atas asumsi, rapikan ERD/FR, SQL migrasi (hanya ditulis, tidak dieksekusi), plan/task implementasi CI3.

- **2026-11-20** `[STATUS][FEAT-12]` **Mockup Subkont tahap 4 selesai: sisi HFG untuk Portal Vendor.** Tiga halaman baru (`ui-mockup/sk-hfg.js`; `sk-master-supplier.html`, `sk-akun-vendor.html`, `sk-laporan-vendor.html`; menu grup Subkont diperluas, KEY data `mockSK.data4`): (1) **Master Supplier** (Purchasing) meniru modul `supplier` (kolom Supplier Name, Country, Telp, Fax, Email, Last By, Last Date; form dengan kolom tabel `new_supplier`); vendor Surat Jalan kini diambil dari data ini; (2) **Persetujuan Akun Vendor** (Purchasing, FR-125): Tinjau, Setujui dengan menautkan ke satu Master Supplier yang ada (hanya yang belum tertaut ke akun lain) atau menambah supplier baru dari data pendaftaran, Tolak, Nonaktifkan/Aktifkan, tercatat Approved By/At; (3) **Laporan Subkont Vendor** (PPIC, UC-25, FR-117): daftar LHP, View, dan **Buka Akses Edit** atas pengajuan perbaikan (rev status Diajukan→Dibuka→Ditutup saat vendor Close lagi; laporan Closed→Dibuka Kembali→Closed). Diuji di Chrome headless: validasi, persetujuan lalu akun bisa login, siklus ajukan→buka→edit→close. `[DISCOVERY]` stub ERD `new_supplier` memakai kolom `alamat` padahal tabel/modul asli memakai `address`; **ERD halaman Subkont diperbaiki menjadi `address`** (satu label, XML valid, tanpa komentar); render ulang ERD belum dilakukan karena hanya mengganti teks satu baris. Kolom "Portal Vendor" di daftar Master Supplier adalah petunjuk mockup, bukan kolom tabel. Berikutnya: tahap 5 Incoming (Material/FG dari vendor), cek hasil vs LHP (±0,7%), temuan + pembukaan akses dari Temuan Incoming, Konfirmasi Hasil, Laporan Nilai Slitting, Saldo Material In Supplier.

- **2026-11-20** `[STATUS][FEAT-12]` **Mockup Subkont tahap 3 selesai: Portal Vendor.** `ui-mockup/sk-portal.html` + `sk-portal.js` (aplikasi terpisah tanpa sidebar HFG; data akun/laporan/revisi di `sk-data.js`, KEY `mockSK.data3`), mengikuti FR-113–FR-117, NFR-18. Isi: daftar akun mandiri (status Menunggu Persetujuan), login (ditolak bila belum disetujui), tab Surat Jalan Masuk (hanya Confirmed, tanpa costbook/nilai), Daftar Laporan Subkont per material (No. PO, Nett Weight Before/After, Total Roll, Action Input/View Detail/Edit/Close/Ajukan Perbaikan), form Material (Nett Weight Actual, Width After, Total Scrap, Kode Internal, Jumlah Coil, baby coil `{Kode Internal}-BC.n`, Net Weight per Roll dibagi rata, Summary Selisih kg/%) dan form FG (qty hasil, material tambahan, berat tambahan, total berat, pratinjau kode FG baru), Close Laporan, Ajukan Perbaikan (alasan wajib, tercatat di `tr_laporan_subkont_revisi`). `[DECISION]` user 2026-11-20: selisih > 0,7% tetap boleh Save/Close dengan peringatan (HFG menilai saat Incoming, FR-119); kode FG baru hanya pratinjau di portal, nomor urut resmi baru diberikan saat PPIC Konfirmasi Hasil Incoming. Diuji di Chrome headless (login/daftar/validasi, Material dengan selisih 4,951%, FG, Close, ajukan perbaikan, tidak ada kebocoran costbook). Belum: sisi HFG untuk Persetujuan Akun (Purchasing, FR-125) dan membuka akses edit (UC-25), lalu Incoming dan Saldo Material In Supplier.

- **2026-11-20** `[STATUS][FEAT-12]` **Mockup Subkont tahap 2 selesai: Surat Jalan Subkont.** `ui-mockup/sk-surat-jalan.html` + `sk-sj.js` (data tambahan di `sk-data.js`: master vendor, costbook, Surat Jalan dan ledger contoh; KEY `mockSK.data2`), mengikuti FR-105–FR-112, UC-20, UC-21 dan workbook sheet "Surat Jalan ke Vendor (Rev2)". Isi: daftar (Kode Delivery, No. Surat Jalan, Vendor, Tgl Kirim, SPK, Jenis, Total Qty kg/pcs, Status, Option Edit/Print/Confirm hanya Draft), form Add/Edit (vendor dari seluruh Master Supplier, scan QR atau pilih dari Stok Siap Kirim, ringkasan Diminta/Sudah Dikirim/Dipilih/Sisa), Save = Draft + barang Terkunci, Confirm (costbook diambil saat Confirm, jurnal Db Material In Supplier / Cr Inventory Bahan Baku atau Barang Jadi, barang Terkirim, saldo ledger, status SPK Dikirim Sebagian/Penuh), Print tanpa costbook, varian FG. Diuji di Chrome headless: validasi, scan ditolak (kosong, tidak di Tampungan, SPK lain, sudah di Surat Jalan lain), edit Draft melepas kunci, Confirm Material dan FG. `[DISCOVERY]` bug ditemukan saat uji dan diperbaiki: item yang dihapus dari Surat Jalan Draft yang sedang diedit tidak bisa dipilih lagi sebelum Save karena kuncinya belum lepas. Tombol View ditambahkan di mockup (tidak ada di sheet) untuk melihat detail dan jurnal. Supplier tanpa akun portal aktif tetap bisa dipilih (keputusan user) dengan petunjuk bahwa vendor belum bisa mengisi LHP. Berikutnya: tahap 3 Portal Vendor (registrasi, daftar laporan, input LHP Material/FG, Close, pengajuan perbaikan), lalu Persetujuan Akun + Master Supplier, Incoming, Saldo Material In Supplier.

- **2026-11-20** `[STATUS][FEAT-12]` **Mockup Subkont tahap 1 selesai: SPK Subkont dan Tampungan Siap Kirim.** `ui-mockup/sk-spk.html`, `sk-tampungan.html` (+ `sk-data.js`, `sk-pages.js`, grup menu "Subkont" di `berry-shell.js`), mengikuti FR-99–FR-104, UC-17–UC-19, UC-N37 dan sheet "Prod. Plan" / "Surat Jalan" bagian 3. Diuji di Chrome headless: validasi form (Jenis wajib, minimal 1 item, qty ≤ stok), Request Item to Warehouse (Draft → Requested), Cancel hanya Draft atau Requested tanpa Tampungan, scan Tampungan (Pack Code kosong / tidak ditemukan / gudang SLI / sudah di Tampungan / bukan item SPK / qty terpenuhi), Hapus baris Siap Kirim, dan Tampungan FG (qty > sisa ditolak). `[DISCOVERY]` mockup tidak mengubah status SPK ke Dikirim Sebagian/Penuh (itu pekerjaan Surat Jalan, tahap 2); baris seed dengan status Terkunci / Terkirim hanya contoh. Berikutnya: tahap 2 Surat Jalan Subkont (daftar, form, confirm, print), lalu Portal Vendor, Persetujuan Akun + Master Supplier, Incoming.

- **2026-11-20** `[DECISION][FEAT-12]` **Vendor pada Surat Jalan = seluruh Master Supplier.** Jawaban user atas celah hasil baca ulang: yang bisa dipilih adalah apa pun yang ada di Master Supplier, tanpa syarat akun portal Aktif. FR-106 dan UC-20 diperbarui; kutipan di `input/Production Planning/notes.md`. Alternatif yang ditolak: membatasi pilihan ke supplier ber-akun portal Aktif. Menutup butir "belum jelas" pada entri sebelumnya.

- **2026-11-20** `[DISCOVERY][FEAT-12]` **Baca ulang dokumen Subkont untuk mencari kalimat bertentangan; 12 butir dirapikan.** Bertentangan: (1) No. PO diinput vendor (FR-114/115) vs PPIC (FR-120) → dua field, `no_po` referensi vendor dan `no_po_jasa` input PPIC; (2) Cancel hanya Draft (FR-103, UC-18) vs Draft/Requested tanpa barang di Tampungan (FR-102) → disamakan ke yang kedua; (3) penautan akun ke master supplier oleh vendor (UC-22) vs oleh HFG (FR-125) → oleh HFG; (4) "master vendor" (FR-106, UC-20) vs Master Supplier (FR-125) → Master Supplier; (5) biaya jasa FG hanya per Kg (FR-116/120) vs Lot (OQ-S11) → Lot berlaku juga untuk FG; (6) status SPK Closed vs Incoming sebagian (FR-102, NFR-19, UC-27) → Closed bila Dikirim Penuh dan semua barang terkirim terkonfirmasi, saldo 0 setelah seluruh Incoming; (7) peran Purchasing belum ada di daftar peran; (8) UC-25 belum memuat penutupan temuan oleh PPIC (S8). Celah: costbook FG memakai qty hasil per produk tetapi tidak ada kolom/input → `qty_pcs_asal` + `qty_pcs_hasil` di `tr_laporan_subkont_fg` dan input vendor di FR-116/UC-23. Salah hitung: plan menulis 5 tabel ringkasan, ERD punya 4. Vocabulary Unbill masih menyebut OQ-S10 terbuka. ERD dirender ulang dan dilihat. Belum jelas (tidak diubah, perlu keputusan): apakah Surat Jalan hanya boleh memilih supplier yang akun portalnya Aktif.

- **2026-11-20** `[DECISION][FEAT-12]` **OQ-S11 dan OQ-S12 dijawab; seluruh OQ-S1–S12 terjawab.** S11 "Setuju" (Jenis Tarif Per Kg / Lot; costbook FG = (nilai FG kirim + biaya jasa) ÷ qty hasil per produk); S12 "Betul" (Purchasing menyetujui akun vendor; Kode Internal = kode internal material dasar produk). FR-116, FR-120, FR-123, FR-125, UC-N39 dan PG-50 diperbarui dari status usulan. Dokumen Subkont lengkap; langkah berikutnya mockup Subkont (termasuk Master Supplier mengikuti modul `supplier`). Menggantikan butir terbuka pada dua entri sebelumnya.

- **2026-11-20** `[DECISION][FEAT-12]` **Jawaban OQ-S1–OQ-S10 masuk dokumen.** Cek vs LHP = timbang HFG per pack, toleransi ± 0,7% (FR-119); PPIC HFG menginput biaya jasa di Incoming (FR-120); pasir dipasok vendor (FR-116); jurnal incoming SPK FG = pola Material dengan Debit ke Inventory Finish Good (FR-122); kode FG baru otomatis `Nama Produk/Kode Internal Material/No. Urut Subkont` dan costbook atas total kg hasil per produk (FR-116, FR-123); akun vendor perlu persetujuan HFG + tertaut `new_supplier`, Master Supplier dimockup mengikuti modul `supplier` (FR-125, UC-N39, PG-50, PG-51); Incoming boleh sebagian, temuan ditutup PPIC tanpa batas waktu (FR-118, FR-119); pelunasan Unbill per PO Jasa karena jasa bisa "1 lot" (FR-122). `[DISCOVERY]` jawaban S10 membuat Price/Kg tidak selalu ada, jadi FR-120 menambah Jenis Tarif Per Kg / Lot dan ERD `tr_subkont_incoming` menambah `jenis_tarif_jasa`, `nilai_jasa_lot` (usulan agen, OQ-S11); OQ-S12 mencatat aktor persetujuan akun dan arti Kode Internal Material. ERD diperbarui (ms_vendor_portal_account: approved_by/at; revisi: ditutup_by/at; fg: nama_material_tambahan, no_urut_subkont), dirender ulang dan dilihat. Menggantikan butir terbuka pada entri sebelumnya.

- **2026-11-20** `[STATUS][FEAT-12]` **Proses Subkont: dokumen dilengkapi sebelum mockup.** Sumber `input/Production Planning/` (BPMN D02 + D002, workbook Rev.2 5 sheet, `notes.md` baru berisi 15 jawaban user). Ditulis: FR-99–FR-124, NFR-18–NFR-21, OQ-S1–OQ-S10 (`docs/fr-nfr.md`); ERD halaman "Subkont ERD" (14 entitas baru, di-render dengan draw.io CLI dan dilihat; tata letak diperbaiki sekali karena garis menabrak tabel); workbook UC-17–UC-27, UC-N37–N38, PG-37–PG-49. `[DECISION]` SPK Subkont tanpa vendor dan tanpa PO (vendor dipilih saat Surat Jalan; PO jasa terpisah); baby coil hasil slitting masuk Gudang Produksi 2 dan costbook baru = (nilai material + biaya jasa) / berat slitting; jurnal COGM/tutup bulan di sheet 4 diabaikan (`#REF!`); kode akun sementara. `[DISCOVERY]` Python di PATH adalah stub Microsoft Store; Python 3.14 + openpyxl ada di `AppData/Local/Python/bin/python3.exe`. Prasyarat user selesai: mockup `fg_stock` (`ui-mockup/fg-stock.html`, `fg-kartu-stok.html`). Mockup Subkont belum dibuat; menunggu jawaban OQ-S.

- **2026-10-28** `[STATUS][FEAT-11]` **Handover ke Dev: rencana implementasi CI3 disusun** (`.artifacts/claim_material/plan_claim_material_28-10-2026.md` + `task_...`). `[DISCOVERY]` dari
  pembacaan kode nyata: (T1) tombol Hold & Claim di `production_report` menerima `isHoldClaim` tetapi tidak memakainya, jadi belum ada backend Claim; (T2) tidak ada kode yang memindahkan
  coil ke Hold (`status_proses='on_hold'` / `kd_gudang='HLD'` hanya dibaca); (T3) stok coil nyata = `warehouse_stock_coil`, bukan `baby_coil` di ERD; (T4) tab "On Hold" sudah ada di
  `stock_from_warehouse` (UC-N35 = memperluas, bukan halaman baru); (T5) Master Jurnal nyata = `ms_jurnal_mapping` + `Gl_interface_model->generate_jurnal_dari_template`; (T6) alur pembayaran
  `request_payment` -> `payment_approve`(+details); (T9) tidak ada infrastruktur uji PHP. `[TODO]` keputusan D0-D7 di plan menunggu user (skema asli, status Hold/Release, harga beli, Master
  Jurnal tanpa fallback, tipe Request Payment, uji, email).
- **2026-10-28** `[DISCOVERY][FEAT-11]` **Skema asli dibaca dari hfg_dev (D0).** `warehouse_stock_coil` memakai `status_proses` enum (termasuk `on_hold`) dan gudang HLD (`id_gudang`=5); kolom `kulit`, `clamp_ring`, `status_proses` ditambahkan ke hfg_dev saja (izin user) karena tidak ada migrasi di repo yang menambahkannya. Rantai coil→PO terverifikasi: `warehouse_stock_coil.no_ros` = `tr_ros_header.id` → `tr_ros_header.no_po`/`id_supplier`/`nm_supplier` → `tr_purchase_order` (268 coil punya rantai lengkap). Tabel kurs = `mata_uang` (`id_kurs`). Release = coil kembali ke gudang asal (butuh kolom gudang asal di `claim_material`). Aturan user: tabel baru cukup ditulis query-nya, tidak dieksekusi.
- **2026-10-30** `[DECISION][FEAT-11]` **Catatan "ada material yang di-hold".** Asumsi status SPK kembali normal setelah claim diputuskan dikonfirmasi user. Mockup: Daftar SPK (Beranda) dan Laporan Produksi menampilkan catatan material di-hold (material, no. coil, berat, asal, no. claim) selama claim belum diputuskan; hilang setelah diputuskan. FR-94 dan workbook UC-N21 diperbarui. `[DISCOVERY]` id #holdNote ternyata sudah dipakai peringatan "Ada Hold Coil - Save dikunci" di index.html; elemen baru memakai #spkHoldNote. Uji Chrome: tampil saat claim menunggu, hilang setelah diputuskan.
- **2026-10-30** `[DECISION][FEAT-11]` **Separator angka + status SPK saat Hold & Claim.** (1) Pemformat bersama `ui-mockup/numfmt.js`: input angka diformat saat diketik (ribuan titik, desimal koma), nilai tersimpan angka murni; dipakai di semua halaman Claim, Costbook, dan Rate. Berat/kuantitas Produksi tidak diubah. (2) Daftar SPK (Beranda) menampilkan badge "SPK Sedang Di-Hold" + nomor claim menunggu keputusan, kartu ringkasan baru; kembali normal bila semua claim SPK diputuskan; "Done" menang. FR-76, FR-94, workbook UC-N21 diperbarui. Uji Chrome: ketik 2306810,81 -> 2.306.810,81 tersimpan 2306810.81; Costbook, Rate, Request Payment berformat; SPK-2026-0912 hold lalu normal. Asumsi agen: status normal kembali setelah claim diputuskan.
- **2026-10-29** `[DECISION][FEAT-11]` **OQ-C11 RESOLVED: bukti format bebas (termasuk video), maksimal 20 MB per berkas.** Mockup: popup Hold & Claim menolak berkas > 20 MB (pesan, berkas lain tetap diproses); Daftar Claim memutar video langsung (pemutar) selain gambar dan PDF. FR-94, workbook UC-N21 diperbarui. Uji real-time Chrome: 21 MB ditolak, tepat 20 MB (jenis .bin) diterima, video+gambar tersimpan dan tampil. Asumsi agen: batas per berkas.
- **2026-10-29** `[DECISION][FEAT-11]` **OQ-C12 RESOLVED: kurs saat claim.** Kurs diinput di Process Decision (supplier non-IDR, wajib), disimpan di Debit Note yang dibuat; Create Debit Note menarik kurs (mata uang dan kurs hanya baca); jurnal Confirm SJ mengonversi Price/Unit PO dengan kurs itu. Akun selisih sementara berlabel "Selisih Costbook vs Price/Unit PO". Asumsi jurnal selisih dikonfirmasi user. Query 006 (price_per_unit_po) sudah dijalankan dan diverifikasi: 10 tabel cocok ERD. `[DISCOVERY]` uji tampilan menemukan kurs tidak terkirim dari modal ke A.putuskan (DN tersimpan kurs 1) padahal uji logika lolos karena memanggil fungsi langsung; diperbaiki. Uji: flow2/3/4/5 + flow6 (kasus USD, 10 pemeriksaan) lolos.
- **2026-10-29** `[DECISION][FEAT-11]` **Tiga dokumen cetak + jurnal selisih Costbook vs Price/Unit PO.** (1-3) Print SPK Re-Pack, Surat Jalan Retur, Debit Note: pratinjau layar berlogo HFG + tombol Cetak (CSS print), DN Belum Terbit bertanda air. (4) Debit Note Retur = Berat x Price/Unit PO; nilai material = Berat x Costbook; jurnal Confirm SJ: Db Debit Note (Retur) / Cr Nilai Material Retur (In Transit) / Db atau Cr Selisih Costbook vs Price/Unit PO. Kolom baru claim_material.price_per_unit_po (ERD, FR-83/84/85/92/94/95, workbook UC-N26/N28/N30; mockup: popup, modal keputusan, jurnal; SQL 006 belum dieksekusi, kolom belum ada di hfg_dev). Sumber di database: dt_trans_po.hargasatuan (PO P2600001 USD 0,586/Kg). OQ-C12 (kurs PO non-IDR, akun COA selisih) terbuka. Uji: flow2/3/4 + flow5 (13 pemeriksaan selisih kredit/debit/nol) lolos; tampilan 3 dokumen cetak dan modal keputusan dicek di Chrome.
- **2026-10-29** `[DECISION][FEAT-11]` **Retur = Berat x Costbook material gudang; harga_beli diisi di hfg_dev; audit header dieksekusi.** (1) Nilai Retur = Berat x Costbook/Kg gudang asal (tanpa ÷ 1,11); kolom baru `claim_material.costbook_per_kg` (ERD, FR-84/92/94/95, workbook, mockup: popup menampilkan Harga Beli/Kg dan Costbook/Kg, Retur memakai Costbook, Nilai Material/Jual Material tetap memakai Harga Beli). SQL: `application/modules/claim_material/migrations/005_add_costbook_per_kg_to_claim_material.sql` (+down), belum dieksekusi, kolom belum ada di hfg_dev. (2) Izin user: `warehouse_stock_coil.harga_beli` di hfg_dev diisi untuk 268 coil ber-PO (dari price_per_coil / berat_bersih di tr_ros_material_coil; sebelumnya 0); kembalikan dengan UPDATE ... SET harga_beli = 0 untuk coil dengan no_ros di tr_ros_header. (3) Audit header sesuai aturan penamaan seragam: header tabel/label di claim-pages.js, popup app.js, FR, dan workbook disamakan dengan nama kolom ERD (mis. No. PO Pembelian -> No. PO, Berat Coil (Kg) -> Berat (Kg), Tindakan -> Aksi, Total Debit Note -> Total Nilai, Tgl Terbit SPK/SJ -> Tgl Perintah Kerja/Tgl Surat Jalan). Dikecualikan: kolom turunan dan hasil gabungan tabel lain (lihat FR-76). Uji: flow2/3/4 lolos (12/13/14), 11 halaman 0 error JS, unggah dan pratinjau bukti lintas halaman lolos.
- **2026-10-29** `[DECISION][FEAT-11]` **Sumber harga kembali ke warehouse_stock_coil.harga_beli; label kembali Harga Beli/Kg; aturan penamaan baru.** Keputusan user (membatalkan costbook di entri sebelumnya). Mockup: header/label "Harga Beli/Kg" dipulihkan (= kolom harga_beli_per_kg); Nilai Retur tetap Berat x Harga Beli/Kg tanpa dibagi 1,11 (menunggu konfirmasi). FR-84/92/94/95, workbook UC-N28/N30 diperbarui. `[RULE]` nama di ERD, tabel database, kode, dan header kolom tampilan harus sama (ditambahkan ke AGENTS.md). Catatan data: harga_beli = 0 untuk 268 coil ber-PO di hfg_dev.
- **2026-10-29** `[DECISION][FEAT-11]` **Nilai Retur memakai Costbook, bukan harga beli, dan tidak dibagi 1,11.** Keputusan user. Mockup: nilaiRetur = Berat x Costbook/Kg (label Harga Beli/Kg diganti Costbook/Kg); contoh 3.125 Kg x 18.800 = 58.750.000 (sebelumnya 52.927.927,93); uji flow2 diperbarui. FR-84, FR-92, FR-94, FR-95, workbook UC-N28/N30 diperbarui. `[DISCOVERY]` catatan agen sebelumnya (harga dari warehouse_stock_coil.harga_beli) keliru: kosong untuk 268 coil ber-PO; tr_cost_book kosong di hfg_dev. Terbuka: rename kolom harga_beli_per_kg menjadi costbook_per_kg (ERD, tabel, SQL).
- **2026-10-29** `[DECISION][FEAT-11]` **Bukti dapat diunduh dan dipratinjau.** Mockup: berkas yang diunggah di popup Hold & Claim disimpan nyata di browser (IndexedDB, `CL.files`); View Alasan menampilkan tiap bukti dengan pratinjau langsung (gambar, PDF) + Unduh + Buka di tab baru; lampiran email bisa diunduh; data contoh menampilkan berkas contoh. FR-94 diperbarui; workbook UC-N21. `[DISCOVERY]` sisa karakter NUL di claim-pages.js/app.js (dari perbaikan ikon klip) menyebabkan grep menganggap file biner; dibersihkan. Uji: unggah PNG+PDF nyata lalu buka di halaman Daftar Claim (Chrome, profil sama): gambar 320x200 tampil, PDF di iframe, isi berkas unduhan 6342 byte sama dengan aslinya. Catatan uji: IndexedDB tidak berjalan pada mode waktu virtual headless, hanya pada waktu nyata.
- **2026-10-29** `[DECISION][FEAT-11]` **Bukti ikut email + Process Decision beberapa claim sekaligus.** Mockup: lampiran email memuat bukti dari Produksi; Daftar Claim punya centang per baris dan centang semua di header + tombol Process Decision (Terpilih); satu modal memuat semua claim terpilih dengan satu kesepakatan bersama dan jalur per claim (uji Chrome: KW 2 + Hold satu SPK -> DN Kompensasi 2.306.810,81 dan DN Retur 52.927.927,93 cocok simulasi workbook; beda supplier ditolak). FR-77 dan FR-78 diperbarui; workbook UC-N22/N23. Asumsi agen: satu supplier, satu kesepakatan, keputusan dan DN tetap per claim. OQ-C11 tetap terbuka.
- **2026-10-29** `[DECISION][FEAT-11]` **Bukti dokumen pendukung wajib pada Hold & Claim.** Permintaan user: bukti dari Produksi harus tersimpan sampai claim selesai. Mockup: kolom "Bukti Pendukung *" di popup (unggah beberapa berkas, hapus sebelum kirim, kirim ditolak tanpa bukti), bukti tampil di View Alasan (nama + ukuran) dan penanda jumlah di tautan; seed claim diberi bukti contoh. FR-94 diperluas; OQ-C11 (format/ukuran, terbuka). ERD: entitas baru `claim_material_bukti` + relasi ke `claim_material`; workbook: entitas baru, UC-N21 diperbarui. SQL: `application/modules/claim_material/migrations/004_create_claim_material_bukti.sql` (belum dieksekusi, tabel belum ada di hfg_dev). Asumsi agen: bukti per baris claim, tidak dapat dihapus setelah terkirim.
- **2026-10-28** `[DECISION][FEAT-11]` **Poin matang desain dijawab.** Debit Note tanpa approval; hak akses terbuka di mockup; template email ditunda (OQ-C2); jurnal ditampilkan di layar. Mockup: tautan **Lihat Jurnal** di Daftar Claim (modal jurnal semua peristiwa claim, Terposting/Belum terjadi, seimbang) memakai `CL.jurnalOf`. FR-93 diperbarui. Uji: flow4 (14 pemeriksaan jurnal) + flow2 (12) + flow3 lolos; tampilan modal dicek di Chrome.
- **2026-10-28** `[DECISION][FEAT-11]` **Ruang lingkup dipersempit: fokus desain konsep dan mockup; kode aplikasi (S2-S9) dikerjakan programmer kemudian.** Keputusan user. Yang sudah ada (9 tabel di hfg_dev, file SQL migrasi 001-003, rencana `plan_claim_material_28-10-2026.md`, `task_...`) menjadi bahan handover ke programmer, bukan pekerjaan agen berikutnya.
- **2026-10-28** `[STATUS][FEAT-11]` **S0 selesai, S1 (file SQL migrasi) ditulis, belum dieksekusi.** ERD: `claim_material` + `id_gudang_asal`, `kd_gudang_asal`; entitas `baby_coil` diberi catatan "=warehouse_stock_coil" (D1). FR-95 dipertegas (Release kembali ke gudang asal; sumber Supplier/PO/harga). `context/coder-only/testing.md` dibuat. File migrasi di `application/modules/{claim_material,spk_repack,surat_jalan_retur,debit_note,penerimaan_uang_supplier,request_payment}/migrations/` (9 tabel, kolom dicek identik dengan ERD, izin View/Manage, 6 baris `ms_jurnal_mapping` kode kosong). Aturan user: tabel/kolom baru hanya ditulis queri-nya.
- **2026-10-28** `[DECISION][FEAT-11]` **Keputusan D0-D7 rencana CI3 dijawab user.** D2 coil ke Hold (gudang HLD) saat claim terkirim, saat Lanjut Produksi kembali ke gudang asal (direvisi 2026-10-28, bukan status baru); D4 Master Jurnal tanpa fallback; D5 alokasi DN semua tipe invoice PO; D1/D3/D6 sesuai usulan; D7 email ditunda sampai ada standar kalimat. D0 terpenuhi (database uji `hfg_dev`, lihat entri Skema asli).
- **2026-10-28** `[DECISION][FEAT-11]` **G5, G6, OQ-C10 diputuskan dan dibangun; mockup Claim final.** G6: Create SPK menggabungkan baris Retur satu supplier (satu SPK = satu SJ,
  nilai SJ = jumlah baris, DN Retur tetap per keputusan); G5: Report Biaya memuat baris Debit Note dan Jual Material beserta total beban, tanpa Retur/Lanjut Produksi;
  OQ-C10 benar. `[DISCOVERY]` ID keputusan di mockup dibentuk dari jam sehingga bentrok bila beberapa keputusan dibuat dalam milidetik yang sama (DN salah tertaut ke SJ); diganti
  penghitung berurutan (ditemukan oleh tes, bukan klik manual; di implementasi CI3 memakai PK auto-increment). Tes logika 12 + 13 pemeriksaan lolos; modal Create SPK dan report diuji di browser.
  Tidak ada pertanyaan terbuka untuk mockup. Tahap berikutnya: rencana implementasi CI3.
- **2026-10-28** `[DECISION][FEAT-11]` **Telaah alur mockup: G1, G3, G4 diputuskan dan dibangun; G5, G6 menunggu.** G1: coil Lanjut Produksi tersedia lagi di Add Coil
  From Hold beserta catatan untuk Produksi (FR-95; diuji dengan SPK berbeda; seed coil yang ditahan dilewati); G3: halaman Material Dijual (Gudang Slitting)
  baca saja (FR-96, UC-N36, PG-36); G4: kolom Progres turunan di Daftar Claim tanpa status baru (FR-97). `[TODO]` OQ-C10 (pembatasan coil Hold di Unpack/WIP,
  perluasan agen) terbuka; G5 (cakupan Report Biaya) dan G6 (SPK Re-Pack gabungan) menunggu persetujuan atas saran agen.
- **2026-10-27** `[DECISION][FEAT-11]` **OQ-C9 terjawab; tidak ada pertanyaan terbuka tersisa untuk spek dan mockup Claim.** KW 2 / Reject Supplier
  tanpa Hold Coil dikirim lewat tombol Hold & Claim; coil asal Reject dipilih di popup (otomatis hanya bila SPK memakai satu baby coil).
  Popup diubah dan diuji untuk satu dan dua coil (dua coil: kosong, validasi menolak, lalu claim mengikuti supplier/PO coil yang dipilih).
  Terbuka hanya OQ-C2 (master email, ditunda) dan Master Jurnal Claim (dibuat kemudian).
- **2026-10-27** `[STATUS][FEAT-11]` **OQ-C8 disetujui; popup Hold & Claim dibangun.** Alokasi DN dipilih saat Request Payment dan tampil sebagai baris
  detail negatif saat pembayaran final; `pembayaran_po_debit_note` dihapus dari ERD dan workbook, `alokasi_debit_note` kini menunjuk
  `id_request_payment` dan `id_payment_approve_details`; halaman mockup diubah menjadi Request Payment + Proses Pembayaran (diuji). Popup Hold & Claim
  di `index.html` (FR-94, diuji end-to-end: validasi, 3 claim terbentuk dengan supplier/PO/coil dan harga costbook, muncul di Daftar Claim).
  `[TODO]` OQ-C9 terbuka: baby coil asal Reject Supplier dipilih di popup, dan pemicu KW 2/Reject Supplier tanpa Hold Coil.
- **2026-10-27** `[DECISION][FEAT-11]` **OQ-C6, OQ-C7 dan sembilan asumsi dijawab.** COA lewat Master Jurnal khusus Claim dengan posting otomatis
  ke GL per peristiwa (FR-93, baru); Alokasi DN = baris tambahan di alur pembayaran yang ada (FR-88); Release coil otomatis (FR-06); DN Retur
  hanya setelah Surat Jalan; nominal penerimaan berbeda ditolak (mockup diubah dan diuji); Edit DN hanya sebelum terbit dan hanya yang berhak
  akses; aktor dari mockup dan master email ditunda diterima. `[DISCOVERY]` alur pembayaran nyata = `request_payment` -> `payment_approve`
  (+ `payment_approve_details`) dan jurnal memakai `master_oto_jurnal_detail` / `ms_jurnal_mapping`. `[TODO]` OQ-C8 (tahap dan bentuk
  penempelan Alokasi DN) terbuka; ERD `pembayaran_po_debit_note`/`alokasi_debit_note` dan halaman Pembayaran PO dengan DN menunggu OQ-C8.
- **2026-10-27** `[DECISION][FEAT-11]` **Keputusan penamaan dan UC Material On Hold.** FK mata uang pada `debit_note` = `id_kurs` (menggantikan
  `id_mata_uang` di entri bawah; sudah diubah di ERD); edge `mata_uang` tidak digambar (halaman 2); owner entitas dipakai seperti workbook;
  tab Material On Hold digabung ke Stok Gudang Coil dan dinamai UC-N35 (workbook, PG-27 menunjuk UC-N35; FR-75 merujuknya).
- **2026-10-27** `[STATUS][FEAT-11]` **ERD dan workbook Claim Material disinkronkan.** `erd-revisi.drawio` halaman 1: grup "Claim Material"
  dengan 10 entitas dan 16 edge (diff +360/−0; 50 entitas lama tidak berubah; kolom 10 entitas dicocokkan satu per satu dengan
  `.artifacts/claim_material/erd_claim_material_27-10-2026.md`; XML valid, 0 komentar, render dilihat). `docs/workbook.xlsx`: UC-N21–N34,
  PG-27–PG-35, 10 entitas (ERD = True), dibandingkan dengan backup (baris lama identik, tanpa Kode ganda). `[DISCOVERY]` PK nyata
  `mata_uang` = `id_kurs` (Mata_uang_model), sedangkan kolom FK rancangan `debit_note.id_mata_uang`; `mata_uang` ada di halaman 2 sehingga
  edge-nya tidak digambar (baris FK saja). Keduanya menunggu keputusan user. Menunggu juga: UC untuk tab Material On Hold (PG-27 tanpa UC).
- **2026-10-27** `[DECISION][FEAT-11]` **OQ-C1–C5 dijawab; ERD claim dirancang.** Claim diproses Purchasing (FR-06 disesuaikan); asal baris
  claim wajib disimpan karena menentukan akun kredit DN Kompensasi (Biaya KW 2 Supplier vs Biaya Scrap); Jual Material = input Rp Material
  KW, sisanya Biaya Scrap; status claim/DN dikonfirmasi; master email ditunda (OQ-C2). OQ-C6 (pemetaan COA/gl_interface) dan OQ-C7 (kaitan ke
  pembayaran PO yang ada) baru dan terbuka. Rancangan 10 tabel di `.artifacts/claim_material/erd_claim_material_27-10-2026.md`; penggambaran
  ke `erd-revisi.drawio` dan workbook didelegasikan, hasilnya diverifikasi terpisah.
- **2026-10-27** `[STATUS][FEAT-11]` **Claim Material: spek disinkronkan, mockup dimulai.** Membaca BPMN D003 dan workbook Rev.2
  di `input/Claim Process/`; delapan pertanyaan dijawab user (jurnal DN Kompensasi/Retur, Jual Material, satu coil dua jalur,
  nilai kompensasi input langsung, PPN, pemisahan lane) dan dicatat di `input/2026-10-27-claim-material/notes.md`. FR-75–FR-92
  dan OQ-C1–C5 ditambahkan ke `docs/fr-nfr.md`; FEAT-11 + capsule dibuat; plan di `.artifacts/claim_material/`. ERD belum
  diubah (tabel claim masih usulan).
- **2026-10-26** `[DECISION][FEAT-04]` **`product_costing_detail` memakai `no_uniq_id`.** Keputusan user: kolom `id_bom (varian tebal)` pada `product_costing_detail` di `erd-revisi.drawio` diganti `no_uniq_id (varian BOM)`; relasi ke BOM kini dari `ms_bom_detail` (kunci pasangan `code_lv4` + `no_uniq_id`). Menutup `[TODO]` temuan agent ERD hari yang sama.

- **2026-10-26** `[DECISION][FEAT-04][FEAT-08]` **Koreksi: kolom varian BOM = `ms_bom_detail.no_uniq_id` (menggantikan `varian_type`).** Definisi user: satu varian = satu baris material pada BOM produk (produk dengan satu material = satu varian; produk dengan beberapa material berbeda ketebalan = beberapa varian); satu header `ms_bom_header` per produk (UNIQUE `id_produk` tetap); kunci varian = pasangan `id_produk` + `no_uniq_id` ("kuncinya pasangan produk dan no_uniq_id jadi aman"). `ms_bom_header.varian_type` dan `is_default` hanya kompatibilitas, tidak dipakai SPK/laporan. SPK (`tr_spk_material_detail`), `production_report_items`, notifikasi Bottom Price, History Costing dan History Price List memakai `no_uniq_id`; label tampilan "Varian BOM (no_uniq_id)". ERD `erd-revisi.drawio`, FR-41/FR-74/NFR-14/NFR-15/OQ 55, workbook (UC BPMN F3, UC Non-BPMN F10, Pages J5), vocabulary disamakan; keputusan `varian_type` sehari yang sama ditandai digantikan. Temuan DB uji: migrasi 003 salah arah (melepas `uk_produk` dan memasang `uk_produk_varian`; header harus tetap satu per produk), `berat_per_meter` di DB uji `DECIMAL(10,2)` (membulatkan 1,3397 menjadi 1,34) harus `DECIMAL(14,6)`. `[TODO]` skrip koreksi DB (unik `id_bom + no_uniq_id`, presisi `berat_per_meter`), kode `application/modules/bom` sudah satu header per produk; label mockup `varian_type` perlu diganti `no_uniq_id`; `product_costing_detail.id_bom (varian tebal)` perlu dipetakan ke `no_uniq_id`.

- **2026-10-26** `[DECISION][FEAT-04]` **Tabel BOM = `ms_bom_header` dan `ms_bom_detail`; ERD resmi = `erd-revisi.drawio`.** Keputusan user: BOM Product Costing memakai tabel yang sudah ada (`ms_bom_header`, `ms_bom_detail`), bukan tabel baru; usulan `product_bom_header/product_bom_detail` dan K2 gugur. Workbook (UC-N07, Entities) disamakan; `erd.drawio` dibiarkan sebagai arsip. `[TODO]` `ms_bom_header` punya `UNIQUE(id_produk)` dan dipakai SPK/Request List/Production Planning; varian tebal per produk (`varian_type`) butuh perubahan skema (lepas unique, tambah `varian_type`, unik gabungan `id_produk + varian_type`) setelah D0 dan keputusan dampak ke modul lain.

- **2026-10-26** `[DECISION][FEAT-08]` **Kolom varian BOM = `varian_type`; ERD yang diperbarui = `erd-revisi.drawio`.** Keputusan user: nama kolom varian di ERD dan kode memakai `varian_type` (nama ERD klien pada `ms_bom_header`), menggantikan usulan `uniq_id`; nama field ERD harus sama dengan nama pada kolom tampilan program (label "Varian BOM (varian_type)"). OQ 52 dijawab: Hasil Costing = Update Costing terakhir saat laporan dibuat. Stok Bebas dan KW 2 mengikuti varian SPK. `erd-revisi.drawio` diperbarui (kolom `varian_type` di `tr_spk_material_detail` dan `production_report_items`, tampilan ringkas `ms_bom_header` + dua relasi di halaman Produksi, catatan); `erd.drawio` belum disamakan. `[TODO]` mockup memakai label Varian BOM (varian_type); samakan `erd.drawio` atau tetapkan satu ERD utama.

- **2026-10-26** `[DECISION][FEAT-08]` **OQ 50, 51, 53, 54, 55 terjawab.** SPK ditolak (tidak bisa disimpan) bila produk belum punya varian BOM atau
  Hasil Costing varian belum lengkap (alasan: sudah tercatat di Laporan Keuangan, adjustment/input ulang repot); varian per produk; varian boleh diubah
  selama SPK belum diinput laporan produksinya; SPK milik modul Production Planning (eksternal), mockup Add SPK harus selaras ERD; `rate.html`
  dipensiunkan. Menggantikan asumsi "HPP komponen Rp 0 ditandai". FR-74 dan fr-nfr OQ disesuaikan. OQ 52 dijawab user: Hasil Costing terakhir yang di-Update Costing; tidak ada OQ terbuka. `[TODO]` mockup: Add SPK menolak
  simpan tanpa varian/costing lengkap, selaraskan label ERD, rate.html ditandai pensiun.

- **2026-10-26** `[DECISION][FEAT-08]` **HPP memakai Rp/Kg Hasil Costing varian BOM; Add SPK memilih varian BOM.** Permintaan user: enam komponen
  biaya HPP (Man Power, FOH, Bahan Pendukung Khusus, Consumables, Biaya Koordinasi, Scrap) tidak lagi dari `rate.html`, tetapi Rp/Kg Hasil
  Costing varian BOM yang dipakai. Ditolak: tetap per Product Category dari Master Rate lama. FR-48 direvisi, FR-49/NFR-14 disesuaikan, FR-74
  baru, OQ 50–55 (asumsi), ERD `uniq_id` (diusulkan; kemudian diganti `varian_type`) pada `production_report_items` dan `tr_spk_material_detail`, intake
  `input/2026-10-26-hpp-rate-dari-costing/`. `[TODO]` mockup hpp.js/beranda.js; kepemilikan kolom di SPK eksternal (OQ 55).

- **2026-10-21** `[DECISION][FEAT-09/FEAT-10]` **OQ 48 dan 49 terjawab.** Notifikasi Bottom Price (FR-68) dipicu saat Update Costing ditekan (satu pemicu
  dengan History Costing; menggantikan "setiap kali costing dihitung ulang", OQ 29 direvisi); hak akses melihat data backup History =
  Manage (sama dengan ekspor). FR-68, FR-71/72, NFR-17, UC-N18/N19/N20, PG-24/25/26, vocabulary (Update Costing) disesuaikan.
  `[TODO]` mockup: notifikasi Bottom Price saat ini masih otomatis; harus mengikuti tombol Update Costing.

- **2026-10-21** `[DECISION][FEAT-10]` **Jawaban user OQ 43–47 History.** Tombol Update Costing di Simulasi = pemicu utama History Costing;
  rilis Product Catalog tidak dicatat (Approval Pricelist yang menetapkan Pricelist terbaru); ekspor Excel tidak mencakup backup (backup
  diakses pemilik hak akses); pencatatan lewat write_log() disetujui (tabel khusus hanya bila perlu); hak akses History Costing dan
  History Price List terpisah, View dan Manage, ekspor perlu Manage. OQ 41, 42 tetap terbuka (asumsi); OQ 48 (Notifikasi Bottom Price
  mengikuti Update Costing?) dan OQ 49 (hak akses backup) baru. fr-nfr, ERD (catatan), workbook UC-N19/N20 dan PG-25/26 disesuaikan.

- **2026-10-21** `[DECISION][FEAT-10]` **Jawaban user untuk History Costing dan History Price List (OQ 34–40).** History Costing dicatat saat costing diperbarui dan hasilnya berubah; History Price List hanya saat Pricelist Disetujui. Draft dan persetujuan Extra Margin tidak dicatat (menggantikan asumsi OQ 38). Masa simpan 1 tahun lalu dipindahkan ke backup data; hanya pengguna dengan hak akses yang melihat; ekspor Excel; tidak terpisah dari Activity Log (`write_log`). OQ 41, 42 tidak dijawab; OQ 43–47 baru (aksi Update Costing, rilis katalog, akses backup, sumber data `logs` vs tabel khusus, hak akses lihat/ekspor). Mockup harus mengikuti: History Costing tanpa entri Negotiation Allowance; History Price List hanya saat Disetujui; ekspor Excel; 12 bulan terakhir.

- **2026-10-21** `[DECISION][FEAT-10]` **History Costing, History Price List, dan nama produk di depan (usulan).** Permintaan user:
  perubahan rate yang memengaruhi Bottom Price disimpan di History Costing; perubahan Pricelist di History Price List; daftar
  Pricelist dan Product Catalog menampilkan nama produk, bukan nomor Pricelist, di depan. FR-71–FR-73, NFR-17, open question 34–42
  (diasumsikan), UC-N19/UC-N20, PG-25/PG-26, entitas `history_costing` dan `history_pricelist`; FR-47/FR-58 diberi catatan revisi tampilan.
  FEAT-10 (open) bergantung FEAT-06 dan FEAT-07.

- **2026-10-21** `[DECISION][FEAT-09]` **Open question 25–33 seluruhnya terjawab.** Baseline = Bottom Price saat terakhir ditinjau;
  deteksi setiap costing dihitung ulang; satu keputusan per notifikasi dan pengajuan per kategori; produk/BOM baru tidak memicu
  notifikasi; "Tidak Perlu Diubah" cukup dengan catatan opsional. OQ 33: pengajuan rate Extra Margin hanya Sales Manager (FR-67
  tetap), semua Sales dapat meninjau dan memilih "Tidak Perlu Diubah"; hak akses diatur per pengguna di program (tidak dikunci ke
  pengguna tertentu). FR-68..70, UC-N18, PG-24, dan catatan mockup disesuaikan.

- **2026-10-21** `[DECISION][FEAT-09]` **Jawaban user untuk notifikasi Bottom Price:** ambang = perubahan sekecil apa pun (≥ Rp0,01);
  pembanding harga = Price List Product Catalog terakhir; peninjau = semua Sales; kanal hanya dashboard (tanpa email/WhatsApp);
  tanpa batas waktu tinjau. OQ 25, 27, 28 terjawab di fr-nfr; UC-N18 dan PG-24 diubah ke aktor Sales. OQ 33 baru: konsistensi
  pengaju Extra Margin (BPMN: Sales Manager) dengan keputusan "semua Sales".

- **2026-10-21** `[DECISION][FEAT-09]` **Fitur dashboard notifikasi perubahan Bottom Price diusulkan dan dicatat di referensi.** Permintaan user:
  alert ke Sales bila Bottom Price berubah, supaya Sales menilai harga jual dengan Rate Extra Margin lama dan memutuskan mengajukan rate
  baru (FR-67) atau tidak. Dicatat: intake `input/2026-10-21-bottom-price-change-notification/`, FR-68–FR-70, NFR-16, open question
  25–32 (ambang, baseline, "harga jual saat ini", penerima/kanal, saat deteksi, granularitas, produk baru, "Tidak Perlu Diubah"),
  UC-N18 (Sales Manager) dan PG-24 di workbook, entitas ERD `bottom_price_notification`, istilah vocabulary, baris tracker FEAT-09
  (open, tanpa capsule). Semua rincian **diasumsikan, menunggu konfirmasi**. `[TODO]` mockup `pc-notifikasi-bottom-price.html`;
  jawaban OQ 25–32 sebelum plan.

- **2026-10-21** `[DECISION][FEAT-05]` **Perubahan rate Negotiation Allowance (Extra Margin) perlu approval GM** (BPMN To Be Sales D01 +
  keputusan user). Pengaju Sales Manager, penyetuju GM; rate aktif hanya berubah setelah Approved; Reject kembali ke Sales Manager;
  Pricelist yang sudah diajukan tidak berubah (snapshot). Diubah: `docs/fr-nfr.md` (FR-67, revisi FR-55, peta perubahan, OQ 20–24),
  ERD (entitas `rate_negotiation_allowance_request` di grup Rates), workbook (UC-12 diubah ke Sales Manager, UC-16 baru untuk GM,
  PG-16 diubah, PG-23 baru, entitas baru), vocabulary (Extra Margin), intake `input/2026-10-21-sales-extra-margin-approval/`.
  `[DECISION]` persetujuan diskon Quotation (<5% / 5–10% / >10%), SO, SPM, Production Plan dicatat di luar scope Product Costing.
  `[TODO]` mockup `pc-approval-extra-margin.html` dan pengajuan di `pc-negotiation.html` (dikerjakan terpisah); OQ 20 (alasan Reject
  wajib: diasumsikan) dan OQ 21–24 menunggu keputusan user.

- **2026-10-20** `[STATUS][FEAT-04]` **FEAT-04 dipecah; 04b Flow Process ditulis.** Riset kode: tabel lama `bom_header`/`bom_detail` dipakai
  json_helper dan modul supplier (bentrok dengan nama ERD), `ms_bom_*` punya UNIQUE(id_produk) dan dipakai SPK/Request List/
  Production Planning (tidak diubah), `master_kurs` dibaca modul PO/harga supplier tanpa filter mata uang, `price_ref_high` bukan Max
  riwayat, `history_purchase` belum ada. ERD: arah FK Flow dan Price Reference diperbaiki. Modul baru `flow_process` (tabel
  `flow_process_header/detail`, migrasi + rollback, log, validasi; `php -l` bersih; belum diuji di database).
  `[TODO]` D0 diperluas, keputusan K2 (nama tabel BOM baru) dan K4 (sumber History Pembelian); user menjalankan migrasi 04b di database uji.

- **2026-10-20** `[DECISION][FEAT-04]` **Perbaikan ERD K6 + koreksi pemilik UC-N08.** `flow_process_detail.id_flow_process_header` (FK baru) menggantikan
  `flow_process_header.id_flow_process_detail` (arah header 1 — N detail; header UNIQUE per `code_lv2`);
  `price_reference_detail.id_price_reference_header` menggantikan `price_reference_header.id_price_reference_detail`
  dan `id_history_purchase` dihapus dari detail (history_purchase dicari per `id_material`, relasi putus-putus tanpa FK);
  catatan bentrok nama `bom_header`/`bom_detail` (tabel lama di `json_helper`/`supplier`) ditambahkan menunggu D0 (K1/K2).
  `context/index/map.md`: UC-N08 dimiliki Finance, hanya Max (menggantikan Purchasing + Harga Terakhir/Rata-rata).
  Pemetaan v3 di `.artifacts/master_data_product_costing/pemetaan_nama_kolom_erd_20-10-2026_v3.md`. `[TODO]` UC-N07 di
  `map.md` masih tertulis Accounting (workbook: Finance); keputusan pertanyaan 5-8 di pemetaan v3.

- **2026-10-20** `[DECISION][FEAT-04]` **Penunjuk ke produk di tabel baru = `code_lv4`; ke Product Category = `code_lv2`** (user:
  "pakai code_lvl4"; nama kolom nyata `code_lv4`). `code_lv2` unik global (generator `P2`+yy+6 digit). ERD halaman Product
  Costing diselaraskan: `bom_header`, `product_costing_header/detail` → `code_lv4`; `flow_process_header`,
  `rate_bahan_pendukung_foh`, `rate_borongan_helper`, `rate_negotiation_allowance` → `code_lv2`; entitas `product_lvl_4` →
  `code_lv1..3`; `material` = `new_inventory_4` (`id`, `nama`, `code_lv1..3`). Alternatif ditolak: `id_product_lvl_*` (tidak
  sama dengan praktik tabel yang ada). Pemetaan v2: `.artifacts/master_data_product_costing/pemetaan_nama_kolom_erd_20-10-2026_v2.md`.
  `[TODO]` putuskan BOM varian: tabel baru bernama sesuai ERD vs ubah `ms_bom_*` (UNIQUE(id_produk)); D0 menunggu.

- **2026-10-20** `[STATUS][FEAT-03]` **Kode FEAT-03 ditulis, menunggu uji database.** Modul baru `master_standard_material`
  (tabel kolom persis ERD, migrasi + rollback, permission/menu tanpa penetapan role, `restrict` di index dan save, log
  before/after); `product_master` dan `material_master` diperluas (validasi server, blokir hapus bila dipakai BOM aktif,
  log `array_merge`, nama otomatis dengan override). `php -l` bersih; SQL tidak dijalankan (tidak ada akses database).
  Aturan baru user dicatat (NFR-15, `general-rules.md`, `AGENTS.md`): nama kolom di program harus sama dengan ERD; tabel lama
  memakai nama nyata sehingga ERD yang menyesuaikan (`wide`; `code_lv*`; `new_inventory_4`) setelah D0. FR-59–FR-66 dan
  angka acuan NFR-11 (simulasi disetujui klien) dicatat. `[TODO]` jalankan migrasi di database uji lalu ikuti walkthrough;
  kirim hasil `SHOW CREATE TABLE`; putuskan penunjuk ke produk di tabel baru (`id_product_lvl_4` vs `code_lv4`).

- **2026-10-20** `[STATUS]` **Master level produk (mockup) ditambah Product Type "Baja Ringan" → Category "CANAL C" → Jenis
  "C75.70 B"** (`pc-pages.js` `LV_EXTRA`) atas permintaan user; hanya pilihan master, belum ada produk contoh.
  Diuji: tampil di filter daftar dan form tambah (cascade Type→Category→Jenis), nama otomatis terbentuk.

- **2026-10-20** `[STATUS]` **Sinkronisasi dokumen poin A.** Aturan baru: nama tabel/kolom di kode = ERD (NFR-15; `AGENTS.md`,
  `context/general-rules.md`). `docs/fr-nfr.md`: FR-59–FR-66 (peringatan kelengkapan + blokir ajukan Pricelist, pembulatan, blokir
  hapus bila dipakai BOM, nama otomatis, Master Standard presisi, Lebar = `wide` [D1, direvisi FR-37], pemilih tanggal, contoh master
  Baja Ringan) dan NFR-11 diganti angka acuan simulasi yang disetujui klien (Type 001.1 Price List 23.198,06; 001.2 24.562,74
  Rp/Meter). ERD: `lebar` → `wide`. Workbook: UC-13/N05/N06/N08/N14/N15 dan PG-06 diperbarui. Mockup pc-produk: satu field Lebar,
  CBM memakai `lebar ÷ 1000`, `dimL` dihapus (tanpa ganti kunci penyimpanan). Pemetaan nama kolom ERD↔kode:
  `.artifacts/master_data_product_costing/pemetaan_nama_kolom_erd_20-10-2026.md`. Konsistensi ERD (26 entitas) = Entities = UC =
  Pages = mockup = menu: nol selisih. `[TODO]` keputusan: penunjuk ke produk di tabel baru (`id_product_lvl_N` vs `code_lvN`),
  nama entitas `material`/`bom_header` vs `new_inventory_4`/`ms_bom_header`; D0.

- **2026-10-20** `[STATUS]` **Penyimpanan data mockup dibuat tahan refresh dan tahan pembaruan.** Kunci penyimpanan kini tetap
  (`mockPC.data`, versi skema di dalam data + fungsi `migrate()`), tidak lagi dinaikkan tiap perubahan bentuk data (v3→v5
  sebelumnya mereset isian user); data lama di `mockPC.v5` otomatis diadopsi. Tambahan: tombol "Simpan ke File" / "Muat
  dari File" (cadangan JSON), dan peringatan bila penyimpanan browser tidak tersedia. Diuji: adopsi kunci lama, bertahan
  setelah refresh, ekspor→impor, penolakan file salah, peringatan saat `setItem` diblokir; 23 halaman × 2 lebar tanpa masalah.
  `[TODO]` perubahan bentuk data berikutnya wajib lewat `SCHEMA` + `migrate()`, bukan ganti kunci.

- **2026-10-20** `[DECISION][FEAT-05]` **Faktor Indirect Labor dijadikan master tersendiri** (user; nilainya bisa berubah),
  global satu nilai, tidak per Product Category, hanya pengali di Man Power Cost (dikonfirmasi user). Diubah: FR-53, peta
  perubahan, vocabulary, workbook (UC-N17 / PG-22, entitas `faktor_indirect_labor`; UC-N11/PG-13 tanpa faktor), ERD
  (entitas baru; `man_power_cost` kolom `rate_1_3_operator` + FK faktor), mockup (`pc-faktor-indirect.html`, menu Rate,
  kunci penyimpanan `mockPC.v5`, peringatan "Faktor belum diisi"). Diverifikasi: 23 halaman × 2 lebar tanpa masalah;
  faktor 1,5 → Man Power 50,651 / Price List 23.198,06; faktor 2 → 67,535 / 23.216,67; snapshot PL-001 tetap.

- **2026-10-20** `[STATUS]` **Faktor Indirect Labor dibuat terlihat di mockup Man Power Rate (Operator):** bagian 4 kini memuat baris
  parameter Jam kerja per bulan, Porsi Operator, dan Faktor Indirect Labor (sebelumnya hanya di kalimat kecil dan modal
  Edit Parameter); bagian 5 memuat kolom "Jumlah sebelum Faktor" dan "× Faktor Indirect Labor" sebelum Man Power Cost.
  Angka tidak berubah (Type 001.1 Man Power 50,651).

- **2026-10-20** `[DECISION][FEAT-05]` **Rate Borongan Helper hanya Rp/Meter, tanpa Rp/Pcs dan tanpa konversi** (user; standar
  tetap berawal dari Rp/Meter). Menggantikan entri 2026-10-19 (Rp/Meter atau Rp/Pcs ÷ Panjang Produk). Diubah di
  `docs/fr-nfr.md` (FR-53, FR-56, peta perubahan, catatan OQ 18), vocabulary, tracker, intake notes, workbook
  (UC-N16, PG-21), ERD (kolom `satuan_borongan` dihapus). Mockup dikerjakan terpisah (seed Spandek 150 Rp/Pcs
  ÷ 6 m → 25 Rp/Meter agar angka costing tidak berubah; mockup selesai dan diverifikasi: 22 halaman tanpa error JS,
  angka Type 001.1 tidak bergeser, sisa "Rp/Pcs" di file pc* nol). `[TODO]` label `rate_man_power (Rp/jam, terhitung)` di
  `man_power_cost` pada ERD tidak lagi menggambarkan rumus (tidak ada satu Rate Man Power Rp/jam); ganti
  nama kolom saat poin "samakan ERD".

- **2026-10-20** `[STATUS]` **Kerangka tema Berry (sidebar, top bar, breadcrumb) dipasang di 22 mockup** (16 `pc-*` + 6
  Produksi) atas permintaan user; isi dan rumus tidak berubah. File baru `ui-mockup/berry-shell.js`,
  `berry-shell.css`, `berry-shell-shadow.css`, `berry-vars.css`; kerangka dirender di Shadow DOM agar CSS Berry
  (Bootstrap 5) tidak bentrok dengan `styles.css`/`pc.css`. Memuat aset lewat jalur relatif `../../assets/berry/`.
  Diverifikasi: 22 halaman × 2 lebar tanpa error JS atau aset gagal, tanpa halaman meluap, isi Produksi identik
  dengan baseline, angka Type 001.1 tidak bergeser. `[TODO]` `berry-vars.css` salinan (buat ulang bila Berry
  diperbarui); layar < 820px belum diuji; mockup bergantung pada `specs/` tetap di dalam repo.

- **2026-10-20** `[STATUS]` **Semua layar `pc-*` ditata mengikuti layout modul aplikasi + peringatan kelengkapan.**
  13 layar sisa dirapikan (acuan: mata_uang, price_ref_raw_material, master_rate_labor, purchase_request,
  approval_po, dst.); mesin hitung menandai varian BOM yang rate/datanya belum lengkap (Flow, BPK/FOH, Borongan,
  Negotiation, Price Reference, Panjang, Berat/Meter, Kurs, General Rate); Pricelist tidak bisa diajukan bila
  ada baris tidak lengkap; semua rupiah 2 desimal, hanya Rate 1/3 Operator dibulatkan. Diverifikasi: 16 halaman
  tanpa error JS, angka Type 001.1 tidak bergeser, peringatan muncul saat rate dihapus. `[TODO]` kolom Action
  Simulasi terpotong di lebar 1500px; belum diuji di layar sempit. Selesai 2026-10-20: akar masalah = kolom grid
  `1fr` melebar mengikuti tabel (halaman lebih lebar dari layar di 1280px); diperbaiki `minmax(0,1fr)` + judul kolom
  boleh membungkus; tabel Simulasi dipadatkan. Dipindai 16 halaman di 1500/1440/1280px: tidak ada halaman atau tabel
  yang meluap. Belum diuji di bawah 1280px / layar sempit.

- **2026-10-20** `[DECISION][FEAT-03]` **Fokus digeser ke tampilan dan fungsi (rumus) di mockup `specs/`.** Kode
  aplikasi (`application/`) dan migrasi FEAT-03 ditunda; D0 tidak lagi mendesak. `[DISCOVERY]` mesin hitung
  mockup (`pc-data.js`) dibaca penuh dan dihitung ulang independen untuk Type 001.1: sesuai FR-51–57
  (Berat/Meter 1,3397; Rate 1/3 Operator 12.625; Man Power 50,65; HPP 21.041,32; Bottom 22.093,39;
  Price List 23.198,06 Rp/Meter).

- **2026-10-20** `[DECISION][FEAT-03]` **Keputusan D1–D7 dijawab user; FEAT-03 planned.** Lebar produk memakai
  ulang kolom `wide` (alternatif ditolak: kolom `lebar` baru, sehingga tidak ada ALTER pada `product_lvl_4`;
  konsekuensi: `wide` juga dipakai menghitung CBM dan dibaca production_report). Level 1–3 tetap terpisah;
  `weight` dibiarkan; nama otomatis; blokir hapus cek BOM dulu; Master Standard satu baris `DECIMAL(14,6)`;
  permission ditetapkan ke group oleh user di program. D0 (SHOW CREATE TABLE) menunggu dari user.
  `[TODO]` mulai dari Master Standard Material; periksa isi `wide` saat D0 diterima.

- **2026-10-20** `[STATUS][FEAT-02]` **FEAT-02 ditutup (done).** User menyetujui peta UC→halaman di workbook dan
  menetapkan rilis Product Catalog boleh berkali-kali (FR-58, UC-15, mockup `pc-catalog`: satu pricelist
  Approved dirilis dua kali → dua katalog, diuji di Chrome headless). Mockup PG-01/02/03 dibatalkan karena
  sudah ada di program (tiga file `unpack*` yang sempat dibuat dihapus); layar pc lain dibiarkan. Workbook
  PG-01..03 dicatat "sudah ada di program". `[DISCOVERY]` riset kode FEAT-03: skema `product_lvl_4` /
  `new_inventory_4` tidak ada di repo, Master Material tidak punya Berat/Meter, `lebar` produk belum ada
  sebagai kolom, `ms_coating` ≠ Master Standard; `ms_bom_header` UNIQUE(id_produk) menghalangi banyak
  varian BOM (FEAT-04). Plan FEAT-03 menyusul di `.artifacts/`.

- **2026-10-20** `[DECISION][FEAT-02]` **Keputusan mockup user.** Layar pc lain (Flow, Kurs, Price Reference,
  Man Power, BPK/FOH, General Rate, Negotiation, Simulasi, Pricelist, Approval, Catalog) dibiarkan dulu;
  butir "Master Produk" kedua = Master Material. Riset kode FEAT-03 (modul CI3 yang ada vs ERD) dimulai.

- **2026-10-20** `[DECISION][FEAT-02]` **UC-N11 dipecah; mockup tiga layar mengikuti layout modul aplikasi.**
  Rate Borongan Helper menjadi UC-N16 / PG-21 / menu sendiri (`pc-borongan.html`); UC-N11 hanya Operator
  (PG-13). `pc-produk`, `pc-material`, `pc-bom` ditata seperti modul `product_master`, `material_master`,
  `bom` (isi tetap Rev.1; aplikasi tidak diubah, 16 halaman termuat tanpa error JS). Modul Produksi,
  `rate.html`, dan `pc-standard` sengaja tidak diubah. `[TODO]` user memutuskan layar pc lain (Flow, Kurs,
  Price Reference, BPK/FOH, General Rate, Negotiation, Simulasi, Pricelist, Approval, Catalog): pakai ulang /
  perluas / baru; pesan butir 5 terpotong.

- **2026-10-20** `[DECISION][FEAT-02]` **Standar `Halaman` + sheet `Pages` diadopsi dari `input/workbook.xlsx` (referensi OFC).**
  Konvensi (`context/document-writer-only/workbook-conventions.md`) dan agent workbook diperbarui; workbook
  kini punya kolom `Halaman` di kedua sheet UC dan sheet `Pages` (PG-01..PG-20, 30 UC; dicek dua arah, tanpa
  Kode ganda). `[DISCOVERY]` aplikasi sudah punya modul CI3 yang tumpang tindih dengan Product Costing
  (bom, product_master, material_master, price_ref_raw_material, master_rate_labor, mata_uang, master_coating);
  `[TODO]` user menentukan tiap mockup .html dipakai ulang / diperluas / dibuat baru, dan memutuskan usulan
  pecah/gabung UC (Report Costing Detail, UC-02/03 tanpa mockup, mockup tanpa UC: material, costbook, rate).

- **2026-10-19** `[STATUS][FEAT-02]` **Workbook dan mockup Product Costing disinkronkan ke Rev.1.**
  Workbook: UC-N05–N14 direvisi di tempat, UC-N15 dan UC-12–15 baru, 43 entitas (dimuat ulang dan
  dicek). Mockup: 15 halaman `pc-*.html` (5 baru: standard, negotiation, pricelist, approval, catalog);
  semua termuat tanpa error JS, alur Pricelist dijalankan end-to-end, angka Type 001.1 cocok dengan hitungan
  tangan. `[TODO]` ERD menggambar pricelist→catalog 1–0..N sedangkan mockup 1–1 (satu rilis per
  pricelist): samakan setelah user memutuskan; angka acuan NFR-11 Man Power baru dari klien.

- **2026-10-19** `[DECISION][FEAT-02]` **Open question 19 disetujui user.** Rumus Man Power Cost usulan
  diterima; Rate 1/3 Operator dibulatkan ke atas sesudah dikali 1/3. Angka acuan NFR-11 Man Power perlu
  dihitung ulang (`[TODO]`).

- **2026-10-19** `[DECISION][FEAT-02]` **Open question 18 dijawab; muncul open question 19.** Rate 1/3
  Operator = (Rate Direct Labor Operator ÷ 173 jam) × 1/3; Rate Borongan Helper Rp/Meter atau Rp/Pcs
  (Rp/Pcs ÷ Panjang Produk = Rp/Meter). Karena Borongan bukan Rp/jam, rumus Man Power Cost diusulkan
  `(Total Man Hour × Rate 1/3 Operator + Borongan Rp/Meter) × Faktor Indirect` dan menunggu
  konfirmasi (OQ 19); angka acuan NFR-11 Man Power ikut usang. ERD: `rate_borongan_helper` mendapat
  `satuan_borongan`.

- **2026-10-19** `[DECISION][FEAT-02]` **Open question 12–17 dijawab user (ronde 2).** Man Power Cost =
  Total Man Hour × Rate (UC-12 1.1 salah salin); Man Power Rate = (Rate 1/3 Operator dari rincian
  gaji/BPJS + Borongan Helper per Product Category) × Faktor Indirect; Rate FOH/Bahan Pendukung Rp/Kg;
  Master Standard global tanpa satuan; Negotiation Allowance per Product Category; Pricelist per Produk ×
  varian BOM dengan alasan Reject, Product Catalog disimpan sebagai data. ERD halaman Product Costing
  diperbarui (render dicek); `[TODO]` OQ 18 (arti "1/3"), workbook, dan mockup `pc-*.html`.

- **2026-10-19** `[STATUS][FEAT-02]` **Tahap 0 Product Costing Rev.1: spesifikasi diselaraskan.**
  BPMN To Be, Use Case UC-01–UC-19, dan ERD baru dijadikan referensi utama (intake
  `input/2026-10-19-product-costing-rev1/`). `docs/fr-nfr.md` mendapat bagian "Revisi Rev.1"
  (FR-50–FR-58, peta perubahan) dan open question 12–17; 20 istilah masuk `vocabulary.md`;
  FEAT-03–FEAT-08 dibuka sebagai backlog per tahap. `[DISCOVERY]` Use Case bertabrakan dengan
  FR-37–FR-49 (2026-10-07) pada rumus Man Power Cost/Rate, satuan FOH/Bahan Pendukung, Berat/Meter,
  dan Price Reference; yang jelas dari jawaban user diadopsi, sisanya dibiarkan terbuka.
  `[DECISION]` Daftar Other Cost = enam elemen UC-09 (bukan empat di sheet *Istilah*/BPMN);
  Price List = Bottom Price + % Negotiation Allowance × Bottom Price. `[TODO]` Workbook
  UC-N05–UC-N14 dan mockup `pc-*.html` diselaraskan setelah open question 12–17 dijawab.

- **2026-10-08** `[STATUS][FEAT-01]` **BOM: Berat/Meter di Daftar Material + material anti-double.**
  Dua permintaan user pada modal BOM: (1) tabel Daftar Material menampilkan kolom Berat/Meter
  (kg/m) read-only di sebelah Qty, terisi dari material terpilih dan ikut berubah saat material
  diganti; (2) material yang sudah dipakai BOM lain tidak boleh dipilih ulang. Dropdown material di
  `bomForm` kini difilter: mengecualikan `materialId` yang dipakai BOM lain, tetap menyertakan
  material milik baris itu sendiri, plus opsi "— Pilih Material —". Diverifikasi harness Node: BOM
  pemakai MT1 tidak menawarkan MT2 (dan sebaliknya), BOM baru kosong mengecualikan MT1 & MT2 yang
  sudah terpakai; screenshot modal menampilkan kolom Berat/Meter = 1,34.

- **2026-10-08** `[STATUS][FEAT-01]` **Product Master & Material Name auto-generate (bisa override).**
  Umpan balik user: kedua nama bukan sekadar input bebas — otomatis tersusun dari gabungan field,
  tetap bisa diedit. Ditambahkan `productName()`/`materialName()` + `applyProductName()`/
  `applyMaterialName()` + flag `nameManual` di `pc-data.js`. Product Master = Type + Category +
  Jenis + Tebal + Lebar + Panjang (mis. "ATAP SPANDEK POLOS 0.25 X 750 X 6 M"); Material Name =
  Metal + Slit + Boron + Tebal-Lebar-Coating-Tensile- (mis. "GALVALUM MOTHER BORON 0.14-1219-AZ70-
  G550-", persis format Excel). Di form modal, field sumber ber-`struct` sehingga mengubahnya
  menyusun ulang nama; mengetik di field nama menyetel `nameManual=true` (regen berhenti); link
  "Generate ulang" mengembalikan ke mode otomatis. Diverifikasi harness Node: generator cocok
  dengan format konsep, auto-overwrite saat tidak manual, preserve saat manual, regen saat field
  sumber berubah, manual bertahan setelah itu. Nama seed tetap utuh saat load (regen hanya saat
  ada perubahan).

- **2026-10-08** `[STATUS][FEAT-01]` **Price Reference & Rate BP/FOH juga dipisah list↔modal (koreksi ke-2).**
  Umpan balik user: sheet "Price Reference Mat" dan "Rate BP dan FOH" juga punya pembagian Tampilan
  Depan vs Detil. Pemeriksaan ulang gambar konsep menunjukkan `[DISCOVERY]` acuan Tampilan Depan
  Price Reference adalah image13 ("Price Reference » Raw Materials": Material Code, Material Master,
  Price Ref IDR, Price Ref USD, Expired, Status, Action-download) — image14/15 ternyata screenshot
  mockup lama kami (berbadge UC-N08), bukan acuan. Price Reference diubah jadi list-only + modal
  View/Edit berisi History Pembelian & keputusan pemakaian; ditambah `priceMeta` (expired+status)
  dan `priceMetaOf()` di `pc-data.js`, plus `kode` (Material Code) pada material. Rate BP/FOH diubah
  jadi list (`No | Product Category | Rate BPK | Rate FOH | Action`) + Add Rate / View / Edit modal,
  sesuai teks sel sheet. Diverifikasi: price list 4849→2025 chars, bpkfoh 1554 chars, modal
  price/bpkfoh terbuka berisi form, header konsep lengkap, costing tetap (HPP 19.733,47) — harness
  Node + screenshot headless Chrome. Catatan sebelumnya yang menyebut keduanya "sudah sesuai,
  unchanged" dengan ini dikoreksi.

- **2026-10-08** `[STATUS][FEAT-01]` **Detil Input dipisah ke modal (koreksi).** Umpan balik user:
  "Tampilan Depan" = header/daftar saja; "Detil Input" = isi saat Add/View/Edit dan tidak boleh
  disatukan sebagai kartu atas/bawah di satu halaman. Ditambahkan mekanisme modal di `pc-shell.js`
  (`openModal`/`closeModal`/`renderModal`, host `#pcModal`, tutup via overlay/×/Escape, mode `view`
  mendisable input), lalu Master Produk, Master Material, BOM, Flow Process & CT, dan Simulasi
  diubah jadi list-only dengan aksi View/Edit (+ Add/Buat Baru) yang membuka detilnya di modal
  terpisah. Simulasi: tombol View membuka "Detil Isi dari Costing (Costing Category Product)" di
  modal (kolom varian yang dipilih disorot). Diverifikasi: 10 halaman render sebagai list (ukuran
  render turun drastis, mis. produk 7807→1473 chars), 8 alur modal (produk/material/bom/flow/
  simulasi add/edit/view) terbuka berisi form, angka costing tetap (HPP 19.733,47; Price List
  21.756,15) — harness Node + screenshot headless Chrome (list header-only + modal "Edit Product
  Master" tampil sebagai overlay di atas list yang diredupkan).

- **2026-10-08** `[STATUS][FEAT-01]` **Product Costing mockup revised to the Excel concept.**
  Revised `ui-mockup/pc-data.js`, `pc-pages.js`, `pc.css` so all 8 requested menus match
  `input/Mockup Lengkap dari Master Product sampai Product Costing.xlsx`: Master Produk list +
  Detil Input, Master Material concept fields (Berat/Meter kept — `[DECISION]` Q1, it drives
  Material Cost and Berat per Pcs), BOM front list + Daftar Material with Qty (`[DECISION]` Q2,
  variant/Uniq-ID model kept because Simulasi renders one column per thickness variant), Flow
  front list + reordered detail columns, Price Reference & Rate BP/FOH verified already matching,
  General Rate Other Cost labels made editable and propagated into Simulasi (hardcoded `OTHER`
  constant replaced by `OTHER_KEYS` + live `genLabel`), and Simulasi front "Daftar Product
  Costing" list + per-variant "Detil Isi dari Costing" breakdown shown as information. Verified:
  HPP (19.733,47 / 20.831,85), Price List (21.756,15 / 22.967,12) and Berat per Pcs (8,04 / 8,64)
  still match the concept images exactly; all 10 pages render with no JS errors via two Node
  harnesses (string render + full `pc-shell` init pipeline, both ALL PASSED) and headless-Chrome
  screenshots of produk/material/bom/flow/general/simulasi. Mockup-only (localStorage, no backend):
  ERD and UC rows unchanged — the added form fields are mockup fields, not confirmed schema. Full
  change note added to `context/index/map.md` under the Product Costing section.

_Nothing before this._
