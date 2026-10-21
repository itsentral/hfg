# Functional Requirements & Non-Functional Requirements — Modul Produksi

Materialized because the client handed over two concrete sources this FR/NFR set decomposes
into UCs from (`workbook-conventions.md` → _Route 1 / Route 2_): a confirmed To-Be BPMN and a
mock-up sheet with explicit input/database/formula concepts per column.

## Sumber

- `input/Produksi To Be D06-PTHFG (To Be Produksi).png` — BPMN To-Be proses Produksi (Route 1)
- `input/19. Rev.10 Mock Up (Tambahan jurnal Favorable dan UnFavorable by Pak Andri).xlsx`,
  sheet **"Konsep yang akan dipakai"** — konsep field input / tarik-database / formula per
  laporan (Route 2, dekomposisi field-level)
- `input/Rev.1 erd.drawio produksi.drawio` — ERD hasil revisi klien sendiri (bukan turunan
  praktisi); ground truth field-level untuk konsolidasi `Report_Produksi`, dan sumber untuk
  beberapa fakta baru: linkage SPK/Production Plan (`tr_spk_material_header`,
  `tr_spk_material_detail`, `id_tr_spk_detail`), split field KW 2 Internal/Supplier, breakdown
  Scrap 7 kategori × 4 sub-field di tabel `HPP`, breakdown biaya HPP per varian KW × per-pcs/
  Total, dan dua dimensi baru per laporan produksi: mesin/asset (`asset`, `id_asset`) dan
  employee/department (`employee`, `id_employee`).

- `input/ERD Product Costing-Production Report.jpg` — ERD klien untuk Product Costing (Material
  Cost, Man Power Cost, Rate Bahan Pendukung Khusus & FOH, Rate Generals) dan relasinya ke
  `production_report_items` (2026-10-07)
- `input/Rev.1 Ide Costing Kg konversi ke Meter.xlsx` — sheet **Ide**, **Simulasi Program**
  (contoh input master produk/material/rate), **Simulasi Costing** (output kalkulasi), serta
  sheet **Simulasi ide (copy)per kg** (versi lama, per Kg; hanya sebagai pembanding)
  (2026-10-07)
- Keputusan user 2026-10-07 (percakapan, belum ada dokumen): lihat "Keputusan 2026-10-07" di
  bagian Product Costing di bawah.

## Re-Confirm

- **Kepemilikan Product Costing — REVERSAL 2026-10-07.** Entri 2026-09-21 di bawah menyatakan
  Product Costing modul terpisah yang hanya dibaca. User kini memutuskan **master data Product
  Costing dibangun dan diisi lewat UI di sistem ini** (Master Produk, Master Material, Price
  Reference, BOM, Flow Process, Man Power Rate, Rate Bahan Pendukung Khusus & FOH, Rate
  Generals). Konsekuensi: FR-37–FR-49 di bawah ada di scope; teks NFR-07(a) tidak lagi berlaku
  seperti tertulis (lihat NFR-09). Entri 2026-09-21 tidak dihapus supaya jejaknya terlihat.

- **Ambang toleransi selisih berat — RESOLVED 2026-09-21.** Kedua sumber sempat berbeda: BPMN
  (swimlane Admin Produksi, gateway "Output Berat Produksi") menyebut ± 0,5%, sheet "Konsep yang
  akan dipakai" (baris 46/49/59/62) menyebut ± 0,7%. Klien mengonfirmasi **± 0,7%** sebagai
  angka yang dipakai. Seluruh FR/NFR di bawah sudah memakai 0,7%. `input/Produksi To Be D06-PTHFG (To Be Produksi).png` sendiri masih menampilkan 0,5% pada gambar — itu tidak diedit
  (append-only, `input/README.md`), tapi diagram tersebut sekarang **stale** terhadap requirement
  ini; jika/ketika BPMN ini diperbarui atau diturunkan ke `docs/diagrams/`, angkanya harus
  disesuaikan jadi 0,7%.
- **Costbook Material vs. Product Costing — RESOLVED 2026-09-21.** Dua istilah ini sempat
  tercampur di sheet (baris 135–154 selalu menulis "Costbook Material dari Gudang Produksi 2"
  bahkan untuk Sisa Coil/Hold Coil yang bukan berasal dari gudang itu) dan belum dibedakan di
  draft FR/NFR sebelumnya. Klien mengonfirmasi keduanya adalah dua sumber angka yang berbeda:
  - **Costbook Material** — posisi **nilai rata-rata material** yang sedang berada di gudang,
    dihitung **per gudang asal**: Gudang Produksi 2, Gudang WIP, atau Gudang Material Hold masing-
    masing punya nilai rata-ratanya sendiri. Dipakai di FR-19, FR-21, FR-22, FR-23 (Biaya
    Material/Pcs, nilai Komponen Scrap, Sisa Coil, Hold Coil, Selisih Berat) — dan harus diambil
    dari gudang tempat baby coil bersangkutan sebenarnya berasal (hasil FR-08: Unpack/WIP/Hold),
    bukan selalu "Gudang Produksi 2" seperti tertulis literal di sheet. FR-19/FR-21/FR-22/FR-23
    di bawah sudah disesuaikan.
  - **Product Costing** — **standard rate per produk** yang awalnya ditetapkan untuk menentukan
    harga jual produk (bukan nilai gudang). Dipakai sebagai sumber rate Man Power, FOH, Bahan
    Pendukung Khusus, Consumables, dan Biaya Koordinasi di FR-19/FR-20 dan NFR-07. Modul/sumber
    data "Product Costing" tetap dianggap ada di luar scope Modul Produksi ini (dependency,
    bukan bagian yang dibangun di sini).

- **Granularitas Costbook Material — RESOLVED 2026-09-21.** Costbook Material di-key oleh
  **material + gudang sumber yang dipakai** — bukan per lot/pack. Artinya satu material yang
  sama punya satu nilai rata-rata per gudang (Produksi 2, WIP, Hold masing-masing satu angka
  per material), dan FR-19/FR-21/FR-22/FR-23 membaca nilai itu berdasarkan material dari baby
  coil yang dipilih dan gudang asalnya — tidak butuh field lot pada entitas Costbook Material.
- **Kepemilikan Product Costing — RESOLVED 2026-09-21.** Product Costing adalah **modul
  terpisah** dari Modul Produksi — bukan sesuatu yang dibangun di dalam scope FR/NFR ini.
  Modul Produksi hanya **membaca** standard rate-nya (FR-19, FR-20, NFR-07); definisi,
  pemeliharaan rate, dan struktur data Product Costing sendiri adalah dependency eksternal
  terhadap dokumen ini.

- **FR-01 (scope Production Plan/SPK) — RESOLVED 2026-09-22, REVERSAL.** FR-01 sebelumnya
  dikeluarkan dari scope Modul Produksi (`context/index/map.md`, entri 2026-09-21: "belongs to
  another module — Planning/Warehouse"). Klien menyerahkan ERD revisi sendiri,
  `input/Rev.1 erd.drawio produksi.drawio`, dan mengonfirmasi lewat keputusan langsung bahwa ERD
  ini menggantikan sejumlah keputusan scope sebelumnya — termasuk FR-01.
  - **Bukti di sumber.** ERD tersebut menempatkan `tr_spk_material_header` (PK `spk_no`, kolom
    `tgl_spk`) dan `tr_spk_material_detail` (PK `id`, FK `spk_no`, kolom `id_produk_fg`) di dalam
    kotak modul terpisah berlabel **"Production Plan"** — bukan di dalam kotak "Report Produksi".
    `Report_Produksi` sendiri punya baris FK `id_tr_spk_detail` langsung di blok header-nya,
    sejajar dengan FK `id_unpack_baby_coil`, `id_warehouse_stock_coil`, `product_lvl_4`,
    `id_employee`, `id_asset`.
  - **Kesimpulan (bukan tebakan).** Tidak ada field atau baris di kotak "Production Plan" pada
    file ini yang menunjukkan Modul Produksi men-**create**/mengedit `tr_spk_material_header`
    atau `tr_spk_material_detail` — kedua tabel itu hanya PK/FK, tanpa form/alur input yang
    tergambar di dalam modul ini. Yang berubah scope adalah: `Report_Produksi` sekarang secara
    eksplisit **membaca/mereferensikan** satu baris SPK spesifik lewat FK `id_tr_spk_detail`.
    Karena itu FR-01 (Production Planning menerbitkan SPK) **tetap** proses di luar Modul
    Produksi — teksnya di bawah tidak diubah — tapi **reversal-nya** berarti: linkage ke SPK
    (`id_tr_spk_detail`) kini bagian in-scope dari data model Modul Produksi, dan memberi dasar
    field-level untuk NFR-02 (traceability ke SPK sumber) yang sebelumnya hanya prosa. Lihat
    FR-25 (baru) untuk fakta field-level ini.
- **KW 2 Internal/Supplier, Scrap 7-kategori, breakdown HPP granular, dimensi mesin & employee —
  RESOLVED 2026-09-22.** `input/Rev.1 erd.drawio produksi.drawio` mengonfirmasi field-level baru
  yang belum tercatat di FR/NFR sebelumnya (lihat FR-26 s.d. FR-30 baru di bawah): split field KW
  2 Internal/Supplier di `Report_Produksi`, breakdown 7 kategori Scrap × 4 sub-field (Qty,
  Costbook, Inventory, Biaya) di tabel `HPP`, breakdown biaya HPP per varian KW (KW1/KW2-Internal/
  KW2-Supplier) × per-pcs/Total, dimensi mesin (`asset`/`id_asset`, kategori "machine"), dan
  dimensi employee/department (`employee`/`id_employee`) — yang menggantikan asumsi role tertutup
  Operator/PPIC/Admin Produksi/Accounting untuk dimensi per-laporan ini; `department`/
  `sub_department` di sumber adalah kolom string terbuka, bukan set 4 nilai tertutup.

### Open questions (belum diresolusi, dicatat apa adanya)

- **Duplikasi baris "Qty/Costbook/Total Sisa Coil" di tabel `HPP`.** Sumber menampilkan dua
  kelompok baris berlabel identik "Qty Sisa Coil" / "Costbook Sisa Coil" / "Total Sisa Coil" —
  satu tepat setelah kelompok Hold Coil (`Qty Hold Coil`/`Costbook Hold Coil`/`Total Hold Coil`),
  dan satu lagi tepat sesudahnya dengan label yang sama persis. Tidak ada penanda di sumber yang
  membedakan kelompok kedua sebagai sesuatu yang lain (mis. valuasi Selisih Berat per FR-23) —
  tidak ditebak di sini; kemungkinan salah label/duplikasi pada sisi klien. Perlu konfirmasi
  klien sebelum di-decompose ke workbook.
- **`asset.nm_asset` dan `asset.nm_category` ditandai FK di sumber**, tapi tidak ada tabel master
  yang digambar sebagai target FK tersebut di file ini. Tidak diasumsikan menunjuk ke tabel
  tertentu — dicatat sebagai field yang levelnya belum jelas (lookup vs. FK sungguhan).
- **FR-14 (capture Komponen Scrap) kini terlihat kurang granular dibanding field yang ada di
  `Report_Produksi` pada sumber ini.** FR-14 saat ini hanya menyebut Wrapping/Potongan Pisau (Qty
  saja) dan "Reject Material Plat" (Qty + Keterangan). Sumber baru menunjukkan field capture yang
  lebih banyak: `Baby Coil Sisa`/`Berat Baby Coil Sisa`, `Baby Coil Hold`/`Berat Baby Coil Hold`,
  `Tong Coil`, `Wrapping`, `Potongan Pisau`, `Reject Product (Internal)`, `Reject Material
  (Internal)`, `Keterangan Reject (Internal)`, `Reject Product (Supplier)`, `Reject Material
  (Supplier)`, `Keterangan Reject (Supplier)`. Tugas ini secara eksplisit hanya diminta mengoreksi
  sisi **valuasi HPP** (FR-21, lihat FR-27 baru); FR-14 (sisi capture) **tidak diubah** di sini
  supaya tidak menimpa teks settled di luar scope yang diminta — dicatat sebagai gap yang perlu
  keputusan terpisah, bukan diperbaiki secara sepihak.

Tidak ada lagi item terbuka dari sisi definisi field selain yang dicatat eksplisit di atas.
Dokumen ini siap di-decompose ke `docs/workbook.xlsx` setelah dua open question pertama
diklarifikasi klien.

## FR — Functional Requirements

Dikelompokkan mengikuti urutan proses pada BPMN (lane: PPIC, Operator, Admin Produksi,
Accounting) dan diperkaya dengan detail field-level dari sheet "Konsep yang akan dipakai".
Setiap FR nantinya di-decompose jadi satu atau lebih UC (Route 1/2) saat masuk
`docs/workbook.xlsx` — belum dilakukan di sini, ini masih layer FR.

### Persiapan SPK & material

- **FR-01** — Production Planning menerbitkan SPK Produksi beserta alokasi Material ke
  Warehouse Material. _(BPMN: Production Planning → Warehouse Material)_
- **FR-02** — Warehouse Material meneruskan material dari Gudang Produksi 1 (Gudang Coil
  Utama) sebagai stok masuk ke Gudang Produksi 2 (khusus area Produksi); PPIC menerima SPK
  Produksi secara paralel. _(BPMN: Warehouse Material → Masuk ke Stok Gudang Produksi 2 / PPIC)_
- **FR-03** — Operator melakukan Setting Mesin sampai status "Mesin sudah siap" sebelum
  laporan unpacking bisa dimulai.

### Unpacking & pengecekan coil

- **FR-04** — Operator mencatat Laporan Unpacking Coil dari stok Gudang Produksi 2, menghasilkan
  Jumlah Roll Coil dalam Pack. Field per pack: No. Uniq Pack, Nama Material (tarik dari
  database), Jumlah Baby Coil dalam Pack, Net/Gross Weight per Roll berdasarkan berat **aktual**
  dan berdasarkan berat **Packing List** — dua pasang angka disimpan terpisah untuk perbandingan
  selisih nanti. _(sheet, baris 5–9, Laporan Pack §1)_
- **FR-05** — Operator mencatat Cek Kondisi Coil setelah unpacking; hasilnya menentukan
  percabangan **Kondisi Coil**: _Tidak sesuai_ → FR-06, atau _Ok, diproduksi sebagian/seluruhnya_
  → FR-07.
- **FR-06** — Jika kondisi coil tidak sesuai, Operator menghasilkan Laporan Ketidaksesuaian
  Material yang dikirim ke Claim Material (PPIC); setelah proses klaim, PPIC melakukan Release
  kembali Material Coil untuk Produksi dengan konfirmasi kelanjutan material coil ke Operator.
- **FR-07** — Operator memasang coil di mesin & memulai produksi, termasuk kemungkinan memakai
  Sisa Coil (dari Stok Gudang WIP) sebagai input tambahan.

### Add Coil (tiga sumber)

- **FR-08** — Sistem memungkinkan Add Coil dari tiga sumber gudang ke satu sesi produksi (Add
  Coil From Unpack / From WIP / From Hold). Untuk setiap sumber: user memilih Baby Coil, dan
  sistem otomatis menampilkan Nama Material, Nett/Gross Weight (Packing List), Total Meter
  (hanya sumber Unpack juga menampilkan Kulit dan Clamp/Ring dari laporan unpack terkait) —
  seluruhnya tarik-database dari laporan asal (FR-04, FR-06, atau data Gudang Material Hold),
  bukan input manual. _(sheet, baris 30–36)_

### Input hasil produksi — FG KW1 & Stok Bebas

- **FR-09** — Admin Produksi menginput Detail Produksi untuk Finish Good KW 1 per baby coil yang
  sudah di-add (bisa >1 baby coil per produk), dengan dua pilihan metode input berat: **Pilihan
  1** (Qty × Berat Standard/pcs dari database) atau **Pilihan 2** (input Kg langsung, sistem
  hitung Berat/Pcs = Total Berat ÷ Qty). Kedua pilihan menghasilkan %Selisih =
  (Berat/pcs − Berat Standard/pcs) ÷ Berat Standard/pcs, dan sistem menampilkan popup
  notifikasi cek ulang bila selisih melebihi **± 0,7%**. _(sheet, baris 39–49)_
  _Diubah 2026-10-07: **Berat Standard/pcs** tidak lagi kolom di database produk, melainkan hasil
  perhitungan = Berat/Meter varian BOM yang dipilih × Panjang produk (FR-37, FR-46); nilainya
  tersimpan ke `berat_standard` pada item laporan. FR-10 mengikuti._
- **FR-10** — Admin Produksi dapat mencatat Stok Bebas untuk sisa coil yang dijadikan produk di
  luar SPK (bukan hasil SPK yang sedang berjalan), dengan alur input yang identik dengan FR-09
  (pilihan Master Product, baby coil sumber, dua pilihan input berat, %Selisih, popup toleransi).
  _(sheet, baris 52–62)_

### Input hasil produksi — FG KW2

- **FR-11** — Admin Produksi menginput Detail Produksi untuk Finish Good KW 2, dipisah antara
  **KW 2 (Internal)** dan **KW 2 (Supplier)** sebagai dua alur input terpisah dengan struktur
  sama: pilih Master Product, lalu per baris input Size, Qty KW 2, Berat Total; sistem hitung
  Berat/Pcs = Berat Total ÷ Qty. Nama KW 2 di-generate otomatis dengan format
  `{Master Product}-KW 2- {01, 02, …}` (formula `=$C$67&"-"&"KW 2"&"- 01"`, urut per baris), dan
  Keterangan adalah free text untuk mencatat penyebab (mis. "akibat white rust").
  _(sheet, baris 65–76)_

### Sisa Coil, Hold Coil & Komponen Scrap

- **FR-12** — Admin Produksi mencatat Sisa Coil: pilih baby coil dari yang sudah di-add (FR-08)
  dan input berat sisa satu baby coil tersebut.
- **FR-13** — Admin Produksi mencatat Total Berat Hold Coil: pilih baby coil (bisa lebih dari
  satu) dan input berat yang di-hold per baby coil.
- **FR-14** — Admin Produksi mencatat Komponen Scrap dalam dua kelompok: (a) Wrapping dan
  Potongan Pisau, masing-masing hanya input Qty; (b) Reject Material Plat, dengan Qty dan
  Keterangan free text. Sistem menjumlahkan Total Semua Scrap dari seluruh komponen.
  _(sheet, baris 88–97)_

### Summary, approval & submit

- **FR-15** — Sistem menghasilkan Summary Packing List vs Aktual Total Produksi: menjumlahkan
  Net Weight Produksi dari seluruh output (FG KW1, KW2, Stok Bebas, Scrap, Sisa Coil, Hold
  Coil) dan membandingkannya dengan Net Weight Packing List (dari data Laporan Unpack, FR-04);
  menghitung Selisih (kg) dan Selisih (%) = Selisih ÷ Net Weight Produksi, dan menampilkan
  popup notifikasi cek ulang bila selisih melebihi **± 0,7%**. _(sheet, baris 100–102)_
- **FR-16** — Bila selisih di luar toleransi, Admin Produksi harus melakukan "Cek kembali ke
  lapangan dan verifikasi laporan" dan mendapat status Approved Toleransi sebelum lanjut ke
  submit. _(BPMN: gateway "Output Berat Produksi" → Cek kembali ke lapangan)_
- **FR-17** — Tombol Save pada laporan produksi hanya aktif apabila tidak ada Hold Coil
  tercatat pada sesi tersebut; bila ada Hold Coil, alur harus melalui Hold & Claim.
  _(sheet, baris 106–109)_
- **FR-18** — Admin Produksi men-submit Laporan Produksi. Submit memperbarui Stok Gudang WIP
  (untuk Sisa Coil) dan mengirim Report Produksi serta Sisa Coil ke Warehouse Finish Good.

### HPP & posting jurnal

- **FR-19** — Sistem menghitung HPP Finish Good KW 1 dan Stok Bebas per produk: Biaya
  Material/Pcs = Berat/Pcs × Costbook Material (nilai rata-rata material pada gudang asal baby
  coil yang sebenarnya dipakai — Gudang Produksi 2 / Gudang WIP / Gudang Material Hold, sesuai
  FR-08); Man Power, FOH, Bahan Pendukung Khusus, Consumables, Biaya Koordinasi masing-masing =
  Berat/Pcs × standard rate produk terkait dari Product Costing; Finish Good Value/Pcs = jumlah
  seluruh komponen biaya di atas; Total per komponen = nilai per pcs × Qty FG.
  _(sheet, baris 115–118)_
- **FR-20** — Sistem menghitung HPP Finish Good KW 2 (Internal dan Supplier terpisah) dengan
  komponen biaya yang sama seperti FR-19, ditambah pembagian Finish Good Value Total menjadi
  Inventory (80%) dan Biaya Loss KW 2 (20%). _(sheet, baris 120–129)_
- **FR-21** — Sistem menghitung nilai Komponen Scrap per jenis (Reject Produk Internal/Supplier,
  Reject Material Internal/Supplier, Tong Coil, Wrapping, Potongan Pisau): Total Weight ×
  Costbook Material dari gudang asal material tersebut, dibagi Inventory Value (40%) dan Beban
  FOH Scrap/Tong Coil/Wrapping (60%). _(sheet, baris 132–142)_
- **FR-22** — Sistem menghitung nilai inventory Sisa Coil dan Hold Coil masing-masing sebagai
  Total Weight × Costbook Material dari gudang asal baby coil bersangkutan (full, tanpa
  pembagian persentase). _(sheet, baris 144–150)_
- **FR-23** — Sistem menghitung nilai Selisih Berat (mengambil angka dari Summary §"Selisih Kg",
  FR-15) sebagai Total Weight × Costbook Material. _(sheet, baris 152–154)_
- **FR-24** — Accounting melakukan Posting Jurnal Produksi ke GL Interface berdasarkan Nilai
  Material dan HPP hasil FR-19 s.d. FR-23, mencakup jurnal: KW1, KW2, Scrap, Inventory, Hutang
  Biaya Proses, Hutang Scrap, dan Hutang Koordinasi. _(BPMN: Posting Jurnal Produksi di GL
  Interface; sheet judul "Jurnal:")_

### Field-level baru dari ERD revisi klien (Rev.1) — 2026-09-22

Seluruh FR di bawah bersumber dari `input/Rev.1 erd.drawio produksi.drawio` (lihat Re-Confirm di
atas). Ditambahkan sebagai item baru, bukan menimpa FR-01–FR-24, supaya jejak "apa berubah kapan"
tetap terlihat.

- **FR-25** — Setiap `Report_Produksi` tertaut ke satu baris SPK spesifik lewat FK
  `id_tr_spk_detail`, yang merujuk ke `tr_spk_material_detail` (PK `id`, FK `spk_no`, kolom
  `id_produk_fg`) di bawah `tr_spk_material_header` (PK `spk_no`, kolom `tgl_spk`) pada modul
  "Production Plan" (di luar Modul Produksi). Field-field SPK ini **dibaca** (reference) oleh
  Modul Produksi, tidak dibuat/diedit di sini — penerbitan SPK tetap FR-01 (proses Production
  Planning). Fakta ini menggantikan pengecualian scope sebelumnya (lihat Re-Confirm, FR-01
  reversal) dan menjadi dasar field-level untuk NFR-02.
- **FR-26** — Input Detail Produksi KW 2 (FR-11) dikonfirmasi sebagai dua kelompok field yang
  identik strukturnya dan sepenuhnya terpisah pada `Report_Produksi`: **KW 2 (Internal)** —
  `Product Asal KW 2 (Internal)`, `Size Product KW 2 (Internal)`, `Qty Product KW 2 (Internal)`,
  `Berat Total Product KW 2 (Internal)`, `Berat/pcs Product KW 2 (Internal)`, `Nama Product KW 2
  (Internal)`, `Keterangan Product KW 2 (Internal)` — dan **KW 2 (Supplier)** dengan tujuh field
  yang sama persis, bersuffix "(Supplier)".
- **FR-27** — Tabel `HPP` menyimpan valuasi Komponen Scrap (FR-21) dalam 7 kategori, masing-
  masing dengan 4 sub-field `Scrap Qty <kategori>`, `Scrap Costbook <kategori>`, `Scrap Inventory
  <kategori>`, `Scrap Biaya <kategori>`: **Reject Product Internal**, **Reject Material
  Internal**, **Reject Product Supplier**, **Reject Material Supplier**, **Tong Coil**,
  **Wrapping**, **Potongan Pisau**. Ini menggantikan penyebutan "Reject Produk Internal/Supplier"
  yang lebih umum di FR-21 dengan daftar kategori dan nama field eksak; nilai `Inventory` dan
  `Biaya` per kategori adalah hasil pembagian 40%/60% yang sudah dijelaskan di FR-21 (Total
  Weight × Costbook Material), kini tersimpan sebagai kolom terpisah alih-alih dihitung on-the-fly.
- **FR-28** — Tabel `HPP` mengonfirmasi breakdown biaya per varian KW × per-pcs/Total sesuai
  FR-19/FR-20, dengan kolom eksak sebagai berikut, diulang identik untuk tiga varian **KW 1**,
  **KW 2 (Internal)**, dan **KW 2 (Supplier)**: header varian (`Product`, `Total Weight`,
  `Berat/Pcs`, `Costbook`); per-pcs (`Material Cost per pcs`, `Man Power Cost per pcs`, `FOH Cost
  per pcs`, `Bahan Pendukung Cost per pcs`, `Consumable Cost per pcs`, `Coordination Cost per
  pcs`, `Scrap Cost per pcs`, `FG Cost per pcs`); Total (komponen yang sama dengan suffix
  `... Cost Total`). Untuk kedua varian KW 2 (Internal dan Supplier), ditambah dua kolom
  `Invetory Cost Total` [sic, ejaan sumber] dan `Biaya Cost Total` — field-level untuk pembagian
  Inventory (80%) / Biaya Loss KW 2 (20%) di FR-20.
- **FR-29** — `Report_Produksi` mencatat dimensi mesin/asset per laporan lewat FK `id_asset`
  (label sumber: "category : machine"), merujuk ke tabel `asset` (PK `id`, kolom `nm_asset`,
  `nm_category` — keduanya ditandai FK di sumber, lihat open question) di bawah grup "Master
  Asset". Field ini berada di blok header `Report_Produksi` (sejajar dengan `id_employee`,
  `id_tr_spk_detail`, dst.), bukan diulang per baris KW1/KW2/Scrap — dimensi mesin dicatat satu
  kali per laporan produksi, bukan per item produksi. Dimensi ini baru, belum ada di FR/NFR
  sebelumnya.
- **FR-30** — `Report_Produksi` mencatat dimensi employee/department per laporan lewat FK
  `id_employee`, merujuk ke tabel `employee` (PK `id`, kolom `department`, `sub_department (roll
  karyawan)`) di bawah grup "Master Employee". Sama seperti FR-29, field ini di blok header
  `Report_Produksi`, dicatat satu kali per laporan. `department` dan `sub_department` adalah
  kolom string terbuka di sumber ini — **bukan** set nilai tertutup — sehingga menggantikan
  asumsi role tertutup Operator/PPIC/Admin Produksi/Accounting (`Staff.Role` di
  `docs/diagrams/erd.drawio` saat ini) untuk dimensi per-laporan ini. Dimensi ini baru, belum ada
  di FR/NFR sebelumnya.

### Struktur header–detail dari ERD revisi ke-2 (Rev.2) — 2026-09-24

Sumber: `input/Rev 2 Production Report.drawio`. Klien menyerahkan revisi ke-2 khusus tabel header
dan detail Report Produksi; **keputusan 2026-09-24: Rev.2 menggantikan** struktur Report
Produksi/HPP di Rev.1. FR-25–FR-30 tidak dihapus (jejak "apa berubah kapan" tetap terlihat) —
bagian yang tergantikan ditandai di bawah. Ditambahkan sebagai item baru.

- **FR-31** — Report Produksi disimpan sebagai **satu header + tiga detail** (relasi 1-ke-banyak
  lewat FK `production_report_id`): `production_reports` (header), `production_report_materials`
  (material/coil yang dipakai), `production_report_items` (hasil produksi), dan
  `production_report_scraps` (scrap).
- **FR-32** — Header `production_reports` memuat: `id_tr_spk_detail`, `tgl_produksi`,
  `id_asset_machine`, `employee_helper`, `employee_setter`, `start_time`, `finished_time`, blok
  ringkasan `summary_finish_good`, `summary_kw2`, `summary_scrap_total`, `summary_sisa_coil`,
  `summary_nett_weight_produksi`, `summary_nett_weight_packing_list`, `summary_selisih`,
  `summary_selisih_percentage`, serta `total_hpp_keseluruhan`, `status_draft`, `created_at`,
  `updated_at`.
- **FR-33** — `production_report_materials` mencatat coil yang dipakai per laporan:
  `id_unpack_baby_coil`, `coil_code`, `material_name`, `nett_weight_packing`, `gross_weight`,
  `total_meter`.
- **FR-34** — `production_report_items` mencatat satu baris per item hasil produksi, dibedakan
  oleh `category_type` (bukan kolom melebar per KW). Kolom: `product_lvl_4_id`,
  `source_material_coil`, `qty`, `berat_total`, `berat_per_pcs`, `berat_standard`,
  `percentage_selisih`, `nama_product_custom`, `keterangan`, plus tujuh komponen biaya
  (`material`, `main_power`, `foh`, `bahan_pendukung`, `consumables`, `coordinator`, `scrap`)
  masing-masing sebagai `*_cost_per_pcs` dan `*_cost_total`, serta `total_hpp_item`.
  **Menggantikan** FR-26 (kolom KW 2 Internal/Supplier melebar) dan FR-28 (breakdown biaya per
  varian KW pada tabel `HPP`).
- **FR-35** — `production_report_scraps` mencatat satu baris per scrap: `scrap_type`,
  `target_coil_code`, `qty`, `berat_total`, `berat_material_plat`, `keterangan`. **Menggantikan**
  FR-27 (7 kategori × 4 sub-field pada `HPP`).
- **FR-36** — Dimensi mesin dan employee pada header berubah bentuk: `id_asset_machine` dan
  `employee_helper`/`employee_setter`, yang (per keputusan 2026-09-24) merujuk tabel master
  `asset` dan `employee`. **Menggantikan** FK `id_asset` (FR-29) dan `id_employee` (FR-30) pada
  header; tabel master keduanya tetap seperti FR-29/FR-30. Tipe `employee_helper` dan
  `employee_setter` **tetap string** (dikonfirmasi tim IT klien, 2026-09-24) — sengaja tidak
  diubah menjadi integer FK.

**Keputusan klien 2026-09-24 (atas open question Rev.2):**

- **`id_asset_machine` dan `employee_helper`/`employee_setter` merujuk tabel master `asset` dan
  `employee`.** Ini mengubah FR-36: bukan lagi kolom bebas, dan dimensi asset/employee di
  header tetap tertaut ke master (FR-29/FR-30 tetap relevan untuk tabel masternya). Nama kolom
  tetap seperti di Rev.2.
- **Toleransi selisih berat tetap ± 0,7%** (NFR-01 tidak berubah).
- **Sisa Coil dan Hold Coil akan masuk tabel `warehouse_stock`** (belum digambar di ERD),
  dibedakan hanya oleh id warehouse-nya. Di header Rev.2 tetap hanya `summary_sisa_coil`.
- **`category_type` dan `scrap_type` dibiarkan dulu** — nilainya belum ditetapkan.
- **`jurnal_produksi.HPP_Id` akan mengambil data dari Report HPP** (tabel/modul mendatang, belum
  digambar). Rev.2 tidak lagi memuat tabel `hpp`.

**Open questions yang masih terbuka (dicatat apa adanya, tidak ditebak):**

- **Nilai `category_type` dan `scrap_type`** — sengaja ditunda. Rev.1 menyebut KW1, KW2
  (Internal/Supplier), dan 7 kategori scrap; apakah nilai itu dipakai apa adanya belum diputuskan.
- **Pemisahan Inventory 80% / Biaya 20% KW 2 (FR-20) dan 40%/60% scrap (FR-21)** tidak punya
  kolom di Rev.2 — perlu konfirmasi apakah dihitung on-the-fly atau ada kolom yang belum digambar.
- **Struktur `warehouse_stock` dan Report HPP** belum digambar — dicatat sebagai rencana, bukan
  desain.
- **Nama komponen biaya berbeda** (`main_power`, `consumables`, `coordinator`) dari FR-28
  (Man Power, Consumable, Coordination) — dianggap penamaan ulang komponen yang sama, belum
  dikonfirmasi.

### Product Costing — master data, kalkulasi, dan pemakaian rate di HPP — 2026-10-07

Sumber: `input/ERD Product Costing-Production Report.jpg` (struktur tabel) dan
`input/Rev.1 Ide Costing Kg konversi ke Meter.xlsx` (contoh input & rumus). Angka dan rumus di
bawah adalah yang tertulis di sheet **Simulasi Program** / **Simulasi Costing**; nilai (mis. 5%,
2%, Rp200/Kg, faktor 1,5) adalah **contoh isian master**, bukan konstanta — lihat NFR-12.
Aktor setiap master ditetapkan user 2026-10-07 (open question 10) dan tercatat di UC-N05–UC-N14.

> **Direvisi 2026-10-19:** beberapa rumus dan aktor di bawah digantikan oleh BPMN/Use Case/ERD
> Rev.1 — baca bagian "Revisi Rev.1" (sebelum "Open questions Product Costing") terlebih dahulu.

**Keputusan user 2026-10-07:**

- Master data Product Costing **dibangun dan diisi lewat UI** (membalik keputusan 2026-09-21).
- Rate Consumables dan Rate Scrap adalah **persentase dari Material Cost**; hasilnya dinyatakan
  dalam Rp/meter dan Rp/Kg.
- Man Power Cost = **Total Man Hour × Rate Man Power**, tidak ada faktor lain.
- Kolom "Row 3" pada ERD `Rate Bahan Pendukung Khusus & FOH` adalah **Rate FOH**.
- Laporan produksi yang sudah dibuat **tetap memakai rate saat laporan dibuat**; perubahan rate
  tidak mengubah laporan lama.
- (Jawaban open question 2, 3, 5–9, 11 — 2026-10-07, lihat `input/2026-10-07-product-costing-jawaban/`.)
- Contoh bentuk input dan output ada pada `input/Rev.1 Ide Costing Kg konversi ke Meter.xlsx`.

**Master data**

- **FR-37** — Master Produk menyimpan hierarki 4 level (Level 1 Product Type, Level 2 Product
  Category, Level 3 Product Jenis, Level 4 Product Master/nama produk) dan **Lebar** (tetap per
  produk, karena satu lebar bisa memakai ketebalan material berbeda). **Tebal** dan **Panjang (meter)**
  tetap disimpan di Master Produk (keputusan user 2026-10-07, open question 3; menggantikan
  catatan sheet Ide E9–E11). **Berat per Pcs tidak disimpan di produk**: = Berat/Meter varian
  BOM × Panjang produk (FR-46). Contoh user: Produk 1 panjang 6 m; material A 1 kg/m → 6 kg,
  material B 1,3 kg/m → 7,8 kg.
- **FR-38** — Master Material menyimpan Metal Type, Slitted/Non Slit, Boron/Non Boron, Material
  Name, Width, Thickness, dan **Berat/Meter (kg/m)**. Berat/Meter ditambahkan ke master karena
  dibutuhkan untuk konversi biaya dari Rp/Kg ke Rp/meter (catatan sheet Simulasi Program, I10),
  dan dipakai per varian BOM untuk menghitung Berat per Pcs. Contoh baris di sheet menulis Width
  914 untuk material "…-1219-…"; itu salah ketik, nilai yang benar **1219**
  (`input/2026-10-07-product-costing-jawaban/`).
- **FR-39** — Master Kurs menyimpan Currency, Kurs, Last Updated, dan Updated By. Kurs dipakai
  mengubah Price Reference dari USD ke Rupiah.
- **FR-40** — Price Reference per material: (a) **History Pembelian** — harga/unit (USD/kg) per
  bulan; (b) sistem menurunkan **Harga Pembelian Terakhir** (harga bulan terisi paling baru) dan
  **Harga Pembelian Rata-rata** (rata-rata bulan yang terisi); (c) per material, user memilih
  **Keputusan Pemakaian Price Reference** — "Harga Terakhir" atau rata-rata — dan pilihan itu
  dapat diubah; (d) **Final Price Reference (USD)** = harga sesuai pilihan; **Final Price
  Reference (IDR/kg)** = Final USD × Kurs.
- **FR-41** — BOM Produk: satu produk punya satu atau lebih **varian BOM**, dibedakan oleh
  ketebalan material (Varian Thickness diambil dari Master Material). Setiap varian punya **No.
  Uniq ID** sendiri (mis. `Type 001.1`, `Type 001.2`), Latest Update, dan Update By; aksi View,
  Edit, Delete. Satu produk boleh memakai beberapa varian material sekaligus.
- **FR-42** — Flow Process & Cycletime: per proses menyimpan Costcentre/Area, Process Name,
  Machine, **Cycletime (menit per meter)**, Man Power (jumlah orang), dan **Man Hour = Cycletime
  × Man Power ÷ 60** (jam per meter). **Total Man Hour** = jumlah Man Hour seluruh proses pada
  flow. Contoh sheet: Cutting Spandek, mesin 24 m/menit → cycletime 1 ÷ 24 = 0,0417 menit/meter,
  1 man power → 0,000694 jam/meter. **Flow dikunci per Product Category (Level 2)**, bukan per
  produk Level 4 (keputusan user 2026-10-07; open question 1).
- **FR-43** — Man Power Rate dihitung dari tiga kelompok komponen per bulan: **Salary Direct Man
  Power** (Gaji Pokok, Tunjangan Keluarga, Tunjangan Jabatan, THR, Cuti, Uang Makan, Transport),
  **BPJS** (Jaminan Kecelakaan Kerja 0,89%, Kematian 0,3%, Hari Tua 3,7%, Pensiun 2%, Kesehatan
  4%) — **nilai rupiah tiap komponen BPJS diinput manual** (persentase hanya keterangan) —
  dan **Biaya Lain-Lain** (Seragam, Sepatu, Helm, Sarung Tangan, Masker — dihitung prorate
  per bulan). Lalu: **Total Biaya Direct Labor/Bulan** = jumlah tiga kelompok; **Rate Direct
  Labor** = pembulatan ke atas (Total ÷ **jam kerja per bulan**, parameter yang di contoh bernilai
  173 jam; layar menampilkan keterangan konstanta yang dipakai); **Rate Man Power (Rp/jam)** = Rate Direct Labor
  × **Faktor Indirect Labor**. Contoh sheet: 6.551.982,55 → 37.873 → × 1,5 = 56.809,5 per jam.
  Komponen dapat ditambah/dihapus per kelompok (tombol Add/Delete di sheet).
- **FR-44** — Rate Bahan Pendukung Khusus dan **Rate FOH** disimpan per Product Category dalam
  **Rp/meter** (contoh sheet: BONDECK 150 dan 400 — "contoh saja"). **Kunci rate adalah
  Product Category (Level 2)**, bukan Level 4 (keputusan user 2026-10-07; open question 1).
- **FR-45** — General Rate (berlaku semua produk): **Consumables** (% × Material Cost),
  **Scrap** (% × Material Cost), **Biaya Koordinasi** (Rp/Kg × Berat/Meter), **Other Cost** — Sales
  & Marketing, Transport, Finance & Admin, Management Fee, Interest, Profit (masing-masing % ×
  HPP) — dan **Negotiation Allowance** (% × Bottom Price). Setiap baris punya **jenis/satuan** sendiri di master: Biaya Koordinasi
  = **Rp/Kg**, baris lainnya **persentase**. Contoh nilai di sheet: Consumables 5%,
  Scrap 2%, Koordinasi Rp200/Kg, Profit 5%, Negotiation Allowance 5%, lima Other Cost lain kosong
  (= 0). ERD menyediakan 6 slot `Other Cost` dan satu `Negotiation Allowance`.

**Kalkulasi Product Costing (per meter, per varian BOM)**

- **FR-46** — Sistem menghitung, untuk setiap kombinasi Produk × varian BOM, dalam Rp/meter:
  - Price Reference (Rp/Kg) = Final Price Reference IDR material varian tersebut (FR-40)
  - **Material Cost** = Price Reference (Rp/Kg) × Berat/Meter material (FR-38)
  - **Man Power Cost** = Total Man Hour (FR-42) × Rate Man Power (FR-43)
  - **Bahan Pendukung Khusus**, **FOH** = rate FR-44
  - **Consumables** = % × Material Cost; **Scrap** = % × Material Cost;
    **Biaya Koordinasi** = Rp/Kg × Berat/Meter (FR-45)
  - **HPP** = Material + Man Power + Bahan Pendukung + FOH + Consumables + Scrap + Koordinasi
  - **Other Cost** (6 komponen) = % × HPP; **Bottom Price** = HPP + seluruh Other Cost
  - **Negotiation Allowance** = % × Bottom Price; **Price List/Meter** = Bottom Price +
    Negotiation Allowance
  - Setiap nilai juga ditampilkan dalam **Rp/Kg** = nilai Rp/meter ÷ Berat/Meter.
  - **Berat per Pcs** varian BOM = Berat/Meter material varian × Panjang produk (FR-37).
  - Material Cost memakai Berat/Meter material utuh tanpa faktor lebar produk (dikonfirmasi user
    2026-10-07, open question 2).
- **FR-47** — Output Simulasi Costing menampilkan satu baris per Produk × varian BOM (No. Uniq
  ID, Price Reference USD/Kg, Kurs, Rp/Kg, Berat/Meter, seluruh komponen FR-46 dalam Rp/meter dan
  Rp/Kg), ditambah **Ukuran Produk (meter)** yang diambil otomatis dari Panjang Master Produk (FR-37; keputusan user 2026-10-07), **Pricelist Produk/Meter**
  (= Ukuran × Price List/Meter) dan **Pricelist Produk/Kg**, sebagai pembanding terhadap "Pricelist
  HFG saat ini" (contoh 27.400 **Rp/Meter**; hanya contoh pembanding hasil hitung pricelist klien
  dengan konsep mock up, user 2026-10-07). Struktur yang sama dikonfirmasi ERD: `Product Costing Header/Detail`.

**Pemakaian rate di HPP Laporan Produksi**

- **FR-48** — HPP per item pada `production_report_items` memakai enam rate dari Product
  Costing: **Man Power** (Total Man Hour × Rate Man Power), **Bahan Pendukung Khusus**, **FOH**,
  **Consumables** (% × Material Cost), **Biaya Koordinasi** (Rp/Kg × Berat/Meter) dan **Scrap**
  (% × Material Cost). Untuk HPP Produk **KW 1 dan KW 2**, Rate Scrap diambil dari Master Rate
  Generals "Scrap"; pembagian 40%/60% pada FR-21 tetap berlaku. Man Power memakai standar rate
  Product Costing; `start_time`/`finished_time` laporan tidak ikut dihitung.
  Rate dicari lewat **Product Category (Level 2)** dari produk (`product_lvl_4_id`) item
  tersebut. Ini mengisi kolom
  `main_power_cost_*`, `bahan_pendukung_cost_*`, `foh_cost_*`, `consumables_cost_*`,
  `coordinator_cost_*`, `scrap_cost_*`. _Cara menghitung biaya per pcs mengikuti mock up Laporan Produksi
  sebelumnya (FR-19/FR-20), bukan didefinisikan ulang di sini (keputusan user 2026-10-07;
  open question 4). Fokus saat ini hanya Product Costing; FR-48 hanya menetapkan **dari mana
  rate-nya datang**, bukan rumus per pcs._
- **FR-49** — Rate yang dipakai FR-48 **dibekukan saat laporan produksi dibuat**: perubahan Rate
  Man Power, Rate Bahan Pendukung/FOH, atau General Rate sesudahnya tidak mengubah angka biaya
  pada laporan yang sudah ada (keputusan user 2026-10-07). Mekanisme penyimpanan (salinan nilai
  ke baris item vs. rate ber-versi yang ditunjuk FK) belum dipilih — NFR-09.

### Revisi Rev.1 — Use Case, BPMN To Be, dan ERD Product Costing — 2026-10-19

Sumber: `input/2026-10-19-product-costing-rev1/notes.md`, yang mencatat tiga file referensi utama
(`input/Costing To Be D05-PTHFG.png`, `input/Rev.1 Use Case Product Costing.xlsx` UC-01–UC-19,
`input/ERD Product Costing-Production Report.jpg`) dan jawaban user 2026-10-19. **Ketiganya
dipakai sebagai referensi utama; jika berbeda dari FR-37–FR-49 di atas, bagian ini yang berlaku**,
kecuali butir yang ditandai "menunggu open question" (belum boleh diimplementasi sebagai final).
Teks FR-37–FR-49 di atas tidak dihapus supaya riwayat keputusan 2026-10-07 tetap terbaca.

**Keputusan user 2026-10-19:**

- Bottom Price = HPP + seluruh Other Cost (Σ Rate Other Cost × HPP); bukan perkalian.
- Daftar Other Cost mengikuti **UC-09 (enam elemen)**: Sales & Marketing, Transport, Finance &
  Admin, Management Fee, Interest, Profit. Daftar empat elemen di sheet *Istilah* dan BPMN usang.
- Negotiation Allowance (Extra Margin) berupa **persentase**.
- UC-13 langkah 4 berjudul "Biaya Proses": salah ketik, maksudnya Biaya Manufacturing Support.
- ERD Rev.1 adalah referensi awal; entitas yang kurang dilengkapi tim pengembang (FR-50, FR-58).
- `Varian type` pada BOM Header = **Varian Tebal**.
- Price Reference hanya memakai nilai **Max** dari History Pembelian.

**Peta perubahan terhadap FR-37–FR-49**

| Topik | Versi 2026-10-07 | Rev.1 (berlaku) | Status |
|---|---|---|---|
| Berat/Meter | Atribut Master Material (FR-38) | Dihitung di BOM per varian tebal (FR-51) | Berlaku; Master Standard global (OQ 15) |
| Price Reference | Pilih Harga Terakhir / Rata-rata, per material (FR-40) | Hanya Max History Pembelian × Kurs (FR-52) | Berlaku (user) |
| Aktor Kurs & Price Reference | Accounting / Purchasing | Finance (UC-06) | Berlaku; role detail saat RBAC |
| Aktor rate | Accounting | Costing (UC-07, 08, 09), Sales (UC-10) | Berlaku; role detail saat RBAC |
| Consumables | % × Material Cost (FR-45) | Rate Rp/Kg × Berat/Meter (FR-54) | Berlaku (UC-08 menyebut Rp/Kg eksplisit) |
| Rate FOH & Bahan Pendukung Khusus | Rp/meter langsung (FR-44) | **Rp/Kg**; Rp/Meter = Rate × Berat/Meter (UC-12) | Berlaku (user, OQ 14) |
| Man Power Cost | Total Man Hour × Rate Man Power (FR-46) | Total Man Hour × Rate (UC-12 1.1 salah salin); bagian Borongan Rp/Meter → FR-56 | Berlaku (user, OQ 19) |
| Man Power Rate | Komponen gaji/BPJS/lain-lain ÷ 173 jam × Faktor Indirect (FR-43) | (Rate 1/3 Operator + Rate Borongan Helper per kategori) × Faktor Indirect; rincian FR-43 hanya untuk Operator (FR-53) | Berlaku (user, OQ 13, 18); Borongan hanya Rp/Meter (user 2026-10-20) |
| Satuan hasil | Rp/meter, lalu Rp/Kg (FR-46) | Setiap komponen dalam **dua satuan**: Rp/Kg dan Rp/Meter (FR-56) | Berlaku |
| Other Cost | Enam elemen, % × HPP (FR-45) | Sama (UC-09, UC-15) | Konsisten |
| Lebar produk | Tetap per produk (FR-37) | Memakai kolom `wide` yang sudah ada, mm (FR-64) | Berlaku (user, D1, 2026-10-20) |
| Faktor Indirect Labor | Parameter pada Man Power Rate (FR-43) | Master tersendiri, global, dikelola Costing (FR-53) | Berlaku (user 2026-10-20) |
| Negotiation Allowance | % global × Bottom Price (FR-45) | % **per Product Category** (FR-55) | Berlaku (user, OQ 16) |
| Perubahan rate Negotiation Allowance | Ditetapkan langsung oleh Sales (FR-55) | Diajukan Sales Manager, **berlaku setelah Approved GM**; Reject kembali ke Sales Manager (FR-67; BPMN To Be Sales D01) | Berlaku (user 2026-10-21); alasan Reject diasumsikan wajib (OQ 20) |
| Alur Pricelist | Tidak ada | Draft → Approval Management → Product Catalog (FR-58), per Produk × varian BOM, Product Catalog disimpan sebagai data | Berlaku (user, OQ 17) |
| Kunci Flow & Rate FOH/BPK | Product Category (L2) | BPMN/UC: per Kategori Produk (ERD masih L4) | Konsisten dengan keputusan 2026-10-07; ERD digambar L2 |

**Master data tambahan**

- **FR-50** (UC-03) — **Master Standard Material** menyimpan Standar Toleransi, Standar Berat Jenis
  Material Baja, dan Coating; dikelola Engineering dan dipakai FR-51. Master baru (belum ada di
  ERD referensi). Nilainya **berlaku global** (satu set untuk semua material; user 2026-10-19).
  **Satuan tidak ditampilkan** di UI karena klien tidak menginformasikannya; angka dipakai apa
  adanya dan konversi satuan pada formula FR-51 dikalibrasi dengan contoh 0,14 mm × lebar 1219 ×
  7,85 ≈ 1,34 kg/m (cek hasil dengan klien saat uji).
- **FR-51** (UC-04) — **BOM per Varian Tebal**: Engineering memilih material dari Master Material
  untuk setiap varian ketebalan. **Berat/Meter dihitung otomatis** =
  `((Tebal Material − Standar Toleransi) × Lebar Coil × Standar Berat Jenis) + Coating`
  (FR-50). Satu produk boleh punya beberapa BOM dengan material berbeda, tetapi **tidak boleh
  membuat BOM dengan material yang sama dua kali** untuk produk yang sama (unik per
  produk + material). Prasyarat: Master Produk (UC-01), Master Material (UC-02), Master Standard
  (UC-03). Menggantikan atribut Berat/Meter pada Master Material (FR-38); kolom Tebal dan Lebar Coil
  tetap di Master Material. Sebagai cek silang, contoh lama 0,14 mm × 1,219 m × 7,85 ≈ 1,34 kg/m.
- **FR-52** (UC-06) — **Price Reference**: Finance memperbarui Kurs, lalu Price Reference USD/Kg
  per material, **diacu dari History Pembelian dengan mengambil nilai tertinggi (Max)**. Sistem
  menghitung Price Reference Rp/Kg = USD/Kg × Kurs. Nilai rata-rata dan terakhir pada ERD
  tidak dipakai costing dan tidak ada pilihan pemakaian. Menggantikan FR-40 butir (b)–(d).

**Master rate**

- **FR-53** (UC-07) — Costing menetapkan rate biaya proses:
  - **Man Power Rate (per Product Category)** terdiri dari dua bagian, lalu dikali **Faktor
    Indirect Labor** (UC-07):
    - **Rate 1/3 Operator (Rp/jam)** = Rate Direct Labor **Operator** (FR-43: rincian gaji/BPJS/
      biaya lain ÷ jam kerja per bulan, contoh 173 jam) × **porsi operator 1/3**. Alasan user
      2026-10-19: Operator dianggap hanya 1/3 bekerja langsung pada produk. Porsi disimpan sebagai
      parameter (NFR-12). **Pembulatan:** Rate 1/3 Operator dibulatkan ke atas ke Rupiah penuh
      **setelah** dikali 1/3 (user 2026-10-19), menggantikan pembulatan Rate Direct Labor di FR-43.
    - **Rate Borongan Helper** diambil dari **master Borongan per Product Category** (master baru),
      **hanya dalam Rp/Meter** (user 2026-10-20: standar selalu berawal dari Rp/Meter; tidak ada
      Rp/Pcs dan tidak ada konversi dengan Panjang Produk).
    - **Faktor Indirect Labor** adalah **master tersendiri** (global, satu nilai) yang dikelola Costing
      karena nilainya dapat berubah (user 2026-10-20); **bukan per Product Category** dan hanya dipakai sebagai pengali pada Man Power Cost; bukan lagi parameter pada Man Power Rate. Nilai
      mengalikan jumlah (Total Man Hour × Rate 1/3 Operator + Rate Borongan Helper Rp/Meter) (FR-56).
      Perubahan nilai tidak mengubah Pricelist yang sudah diajukan dan laporan produksi yang sudah dibuat
      (FR-49, NFR-09).
    - Rincian gaji/BPJS/jam kerja FR-43 hanya berlaku untuk bagian Operator; "Rate Man Power
      (Rp/jam)" tunggal pada FR-43 tidak lagi dipakai langsung (lihat FR-56).
  - **Rate FOH** dan **Rate Bahan Pendukung Khusus** per Product Category dalam **Rp/Kg**
    (menggantikan Rp/meter di FR-44); Rp/Meter dihitung `Rate Rp/Kg × Berat/Meter` (FR-56).
- **FR-54** (UC-08) — Costing menetapkan **Rate Consumable (Rp/Kg)**, **Rate Scrap (% dari Material
  Cost)**, dan **Rate Biaya Koordinasi (Rp/Kg)**; berlaku semua produk.
- **FR-55** (UC-09, UC-10) — Rate Other Cost (% × HPP) untuk enam elemen UC-09 ditetapkan Costing;
  **Rate Negotiation Allowance (Extra Margin, %)** ditetapkan Sales **per Product Category**
  (user 2026-10-19; bukan lagi bagian Rate Generals). Rate Other Cost berlaku semua produk.
  **Direvisi 2026-10-21 (FR-67):** rate Negotiation Allowance tidak lagi ditetapkan langsung oleh Sales; perubahannya
  diajukan **Sales Manager** dan baru berlaku setelah **disetujui GM**.

**Kalkulasi (UC-11 s.d. UC-16)** — `BM` = Berat/Meter varian BOM yang dipilih (FR-51). Setiap
komponen dihitung dalam **Rp/Meter dan Rp/Kg**; sistem mengambil rate Product Category dari produk.

- **FR-56** —
  - **Material Cost** (UC-11): Rp/Kg = Price Reference Rp/Kg material BOM (FR-52); Rp/Meter =
    Price Reference × BM.
  - **Biaya Proses** (UC-12): Man Power, Bahan Pendukung Khusus, FOH. Bahan Pendukung dan FOH:
    Rp/Meter = Rate (Rp/Kg) × BM; Rp/Kg = Rp/Meter ÷ BM. **Man Power** (disetujui user
    2026-10-19, OQ 19): Rp/Meter = `(Total Man Hour × Rate 1/3 Operator + Rate Borongan Helper Rp/Meter) ×
    Faktor Indirect Labor` (Rate Borongan Helper sudah Rp/Meter, FR-53); Rp/Kg = Rp/Meter
    ÷ BM. Butir 1.1 UC-12 (Rate × Berat/Meter) salah salin (user 2026-10-19). Biaya Proses = jumlah
    ketiganya, per satuan.
  - **Manufacturing Support** (UC-13): Consumables Rp/Meter = Rate × BM, Rp/Kg = Rate (Rp/Kg);
    **Scrap** = Rate Scrap × Material Cost (Rp/Meter dan Rp/Kg masing-masing); **Biaya
    Koordinasi** Rp/Meter = Rate × BM, Rp/Kg = Rate. Manufacturing Support = jumlah ketiganya.
  - **HPP** (UC-14) = Material Cost + Biaya Proses + Manufacturing Support, per satuan.
- **FR-57** — **Bottom Price** (UC-15): untuk tiap elemen Other Cost, Other Cost = Rate × HPP
  (per satuan); `Bottom Price = HPP + Σ Other Cost`. **Price List** (UC-16) = Bottom Price +
  (Rate Negotiation Allowance % × Bottom Price), per satuan. Sales menerima Bottom Price, lalu
  menjalankan perhitungan; hasilnya dicetak sebagai **Report Costing Detail/Produk**.

**Alur Pricelist (UC-16 s.d. UC-19)**

- **FR-58** — Sales menyusun **Draft Pricelist** dari hasil FR-57 dan mengirimkannya ke Management
  (UC-17). Management memeriksa dan memutuskan **Approve** atau **Reject** (UC-19). **Reject**
  mengembalikan pricelist ke perhitungan ulang (UC-16) lalu diajukan lagi; **Approve** memungkinkan
  Sales **merilis Product Catalog** berisi harga yang disetujui (UC-18), yang diteruskan ke proses
  Sales di luar alur ini. Hanya pricelist yang sudah Approve yang boleh dirilis; satu pricelist Approved boleh dirilis **berkali-kali** (user 2026-10-20) dan setiap rilis membuat Product Catalog (snapshot) baru (relasi pricelist → katalog 1 ke 0..N). Status yang
  tersirat pada BPMN: Draft, Menunggu Approval, Approved, Rejected; tidak ada status lain
  yang ditambahkan. Keputusan user 2026-10-19: pricelist **per Produk × varian BOM** (Rp/Meter dan
  Rp/Kg); Management **wajib mengisi alasan Reject** yang disimpan; **Product Catalog disimpan
  sebagai data** (snapshot harga yang disetujui, dengan pelepas dan waktu rilis). Versi dan periode
  berlaku tidak diminta klien.

**Aktor Rev.1 (menurut lane BPMN)**: Engineering (UC-01–05), Finance (UC-06), Costing (UC-07–09),
Sales (UC-10, 16–18), Management (UC-19); perhitungan UC-11–15 dijalankan sistem dengan pemilik
proses Accounting (asumsi di sheet Use Case: ikon roda gigi = otomatis).

### Fungsi tambahan hasil review mockup — 2026-10-20

Sumber: review mockup `ui-mockup/pc-*` oleh user dan klien (simulasi disetujui klien 2026-10-20). Melengkapi FR-50–FR-58; tidak
menimpa teks sebelumnya. Fitur yang hanya ada di mockup (Simpan ke File / Muat dari File, kerangka tema) bukan requirement.
**FR-53 sudah memuat Faktor Indirect Labor sebagai master global** dan **Rate Borongan Helper hanya Rp/Meter**; tidak diulang di sini.

- **FR-59** (UC-N14, UC-13) — **Peringatan kelengkapan data costing.** Sistem menandai setiap varian BOM yang datanya belum
  lengkap, menyebut apa yang kurang, dan menunjuk menu tempat mengisinya. Kekurangan yang dideteksi: Flow Process kategori
  belum ada atau Total Man Hour 0; Rate BPK/FOH kategori belum ada; Rate Borongan Helper kategori belum ada atau kosong;
  Negotiation Allowance kategori belum ada atau kosong; Price Reference kosong (History Pembelian tanpa nilai); Berat/Meter ≤ 0
  (periksa Master Standard, tebal, lebar coil); Kurs USD belum ada; General Rate (Consumables, Scrap, Biaya Koordinasi) belum
  diisi; Faktor Indirect Labor belum diisi atau ≤ 0; jam kerja per bulan atau porsi Operator tidak valid. Rate yang belum ada
  tidak boleh dihitung diam-diam sebagai 0. Elemen Other Cost yang kosong tetap dianggap 0 (FR-45). **Draft Pricelist boleh
  disimpan, tetapi tidak boleh diajukan ke Management (UC-13) selama ada baris yang tidak lengkap.**
- **FR-60** (semua perhitungan) — **Pembulatan.** Semua nilai rupiah dihitung dan ditampilkan 2 desimal tanpa pembulatan,
  **kecuali Rate 1/3 Operator** yang dibulatkan ke atas ke Rupiah penuh sesudah dikali 1/3 (FR-53). Menggantikan `{TBC}`
  aturan pembulatan komponen lain pada NFR-11.
- **FR-61** (UC-N05, UC-N06) — **Produk atau material yang sudah dipakai BOM tidak boleh dihapus** (periksa `ms_bom_header.id_produk`
  / `ms_bom_detail.id_material`); sistem menolak dengan pesan. Pemakaian oleh transaksi lain (PR/PO/gudang) belum diperiksa
  (keputusan D5, 2026-10-20).
- **FR-62** (UC-N05, UC-N06) — **Nama otomatis dengan override manual.** Nama Level 4 produk disusun dari Product Type + Category +
  Jenis + Tebal × Lebar × Panjang; Material Name dari Metal Type + Slitted/Mother + Boron + Tebal-Lebar Coil-Coating-Tensile.
  Pengguna boleh menimpanya secara manual; nama yang sudah diedit manual tidak ditimpa lagi oleh penyusunan otomatis, dan
  tersedia aksi "generate ulang" (keputusan D4).
- **FR-63** (UC-N15) — **Master Standard Material** (melengkapi FR-50): satu baris global (upsert, id tetap), tanpa label satuan
  di UI, tiga nilai desimal berpresisi `DECIMAL(14,6)`; setiap perubahan nilai dicatat before/after karena memengaruhi
  Berat/Meter semua BOM (keputusan D6).
- **FR-64** (UC-N05; **direvisi FR-37**) — **Lebar produk memakai kolom `wide` yang sudah ada** (satuan mm), bukan kolom `lebar`
  baru (keputusan D1, 2026-10-20). Kolom itu juga dipakai menghitung CBM (panjang × lebar × tinggi kemasan) dan dibaca
  modul lain, jadi bentuk dan satuannya tidak boleh diubah. Isi `wide` diperiksa setelah `SHOW CREATE TABLE` (D0) diterima.
- **FR-65** (UC-N08, UC-N14) — **Tanggal dipilih lewat pemilih tanggal**, bukan teks bebas: Expired Price Reference dan filter
  Range Date Simulasi Costing (format `YYYY-MM-DD`).
- **FR-66** (UC-N05; data master, bukan aturan) — Contoh pilihan master level produk yang ditambahkan user: Product Type
  **Baja Ringan** → Product Category **CANAL C** → Product Jenis **C75.70 B**.
- **FR-67** (UC-12, UC-16; **merevisi FR-55**; sumber BPMN To Be Sales D01, keputusan user 2026-10-21) — **Perubahan rate
  Negotiation Allowance (Extra Margin) perlu persetujuan GM.** Menurut BPMN: setelah produk ada di Product Catalog,
  **Sales Manager** menilai "Perlu perubahan margin?"; bila tidak, Quotation dibuat dengan rate yang berlaku; bila perlu,
  Sales Manager **mengajukan rate Extra Margin baru** (UC-12), lalu **GM** memutuskan **Approved** atau **Reject**
  (UC-16). Approved → rate dipakai; Reject → kembali ke Sales Manager untuk memperbaiki dan mengajukan ulang.
  - Rate Negotiation Allowance **aktif** (% per Product Category, FR-55) **hanya berubah setelah Approved GM**. Sebelum itu
    Simulasi, Pricelist, dan Katalog memakai rate aktif lama; Pricelist yang sudah diajukan tidak berubah (snapshot, FR-58).
  - Pengajuan menyimpan Product Category (`code_lv2`), rate yang diajukan, pengaju dan waktu, pemutus dan waktu, serta alasan
    Reject (ERD: `rate_negotiation_allowance_request`). Status hanya **Menunggu Approval GM**, **Approved**, **Rejected**.
    Approved memperbarui rate aktif beserta Last Update dan Updated By; Reject tidak mengubah rate aktif.
  - **Diasumsikan, mohon konfirmasi (OQ 20): alasan Reject wajib diisi GM** (BPMN tidak menyebut alasan; mengikuti pola
    Pricelist FR-58). Pengajuan diasumsikan satu per Product Category (OQ 22).
  - Poin penilaian customer baru pada BPMN (toko sewa/sendiri, lama berdiri, armada, asset, jumlah cabang) hanya acuan
    keputusan margin dan **tidak diotomasi**.
  - **Di luar scope Product Costing:** persetujuan **diskon pada Quotation** (<5% disetujui Sales langsung, 5–10% Sales
    Manager, >10% GM) beserta pembuatan Sales Order, SPM, dan pengiriman SO ke Production Plan. Itu modul Sales/Quotation
    dan tidak dimodelkan di sini.

### Open questions Product Costing (belum diresolusi, dicatat apa adanya)

1. **RESOLVED 2026-10-07 — per Product Category (Level 2).** Pertanyaan semula: kunci rate
   Product Category atau Level 4? Sheet Simulasi Program menulis Flow Process
   "(Cycletime per Kategori Produk)" dan Rate Bahan Pendukung/FOH per `Product Category`; ERD
   (`Flow Process Header`, `Rate Bahan Pendukung Khusus & FOH`) memakai `id product level 4`.
   Jawaban user: per kategori produk; ERD
   `Flow Process Header` dan `Rate Bahan Pendukung Khusus & FOH` harus memakai Product
   Category, bukan `id product level 4`, saat digambar.
2. **RESOLVED 2026-10-07 — benar.** Jawaban user: Material Cost memang memakai Berat/Meter material utuh, tanpa faktor lebar. Pertanyaan semula: Material Cost per meter memakai berat per meter material utuh, bukan lebar produk.**
   Rumus sheet: Berat/Meter material (mis. 1,34 kg/m untuk coil lebar 1219 mm) × Rp/Kg, tanpa
   memakai Lebar produk (750 mm). Dua produk berbeda lebar dari coil yang sama akan mendapat
   Material Cost identik. Apakah itu benar (coil dipakai utuh/slit penuh) atau perlu faktor
   lebar? Sheet Ide sendiri sempat memakai "Berat per m² × Lebar Efektif".
3. **RESOLVED 2026-10-07 — Tebal dan Panjang tetap ada.** Jawaban user: Berat per Pcs = Berat/Meter varian BOM × Panjang produk; produksi mengambil produk dengan varian BOM yang dipilih (FR-37, FR-46). Pertanyaan semula: Master Produk: Tebal/Panjang/Berat standard.** Sheet Ide: tebal dan berat/unit dihapus dari
   Master Produk; sheet Simulasi Program: Panjang dan Tebal masih ada. Dan bila Berat/unit
   dihapus, dari mana `berat_standard` per pcs (FR-09 dan kolom `berat_standard` di
   `production_report_items`) diambil?
4. **RESOLVED 2026-10-07 — di luar scope Product Costing.** Jawaban user: biaya per pcs sudah
   mengikuti mock up Laporan Produksi sebelumnya; fokus sekarang hanya Product Costing.
   Pertanyaan semula: Rate Product Costing per meter; `production_report_items`
   per pcs. Apakah per pcs = per meter × panjang pcs (panjang dari mana?) dan memakai Berat/Meter
   varian BOM yang sesuai coil yang benar-benar dipakai (`source_material_coil`)?
5. **RESOLVED 2026-10-07 — Rate Scrap dari Master Rate Generals.** Jawaban user: HPP KW 1 dan KW 2 memakai Rate Scrap dari Master Rate Generals; pembagian 40/60 tetap ada. Pertanyaan semula: Scrap: rate standar atau aktual?** Rate Scrap = 2% × Material Cost adalah penyisihan standar;
   `production_report_scraps` mencatat scrap aktual (qty, berat). `scrap_cost_*` pada item
   memakai yang mana? Terkait FR-21/FR-35 dan pembagian 40/60 yang masih terbuka.
6. **RESOLVED 2026-10-07 — jam tidak dihitung.** Jawaban user: `start_time`/`finished_time` tidak dihitung; Man Power memakai standar rate Product Costing. Pertanyaan semula: Man Power standar vs aktual.** Rumus yang diputuskan memakai Total Man Hour dari flow
   (standar). `start_time`/`finished_time` laporan tidak dipakai di perhitungan — dikonfirmasi?
7. **RESOLVED 2026-10-07 — BPJS manual, 173 jam parameter.** Jawaban user: BPJS diinput manual; 173 jam menjadi parameter dan keterangan konstanta ditampilkan (FR-43). Pertanyaan semula: Dasar BPJS.** Nilai BPJS di sheet setara 0,89%/0,3%/3,7%/2%/4% dari ≈ Rp4.570.000, bukan
   dari Gaji Pokok Rp4.828.884. Apakah nilai BPJS diinput manual (seperti sheet) atau dihitung
   sistem dari persentase × dasar tertentu? Konstanta **173 jam** juga perlu jadi parameter.
8. **RESOLVED 2026-10-07 — salah ketik, 1219.** Jawaban user: Width yang benar 1219; sumber tidak diedit, koreksi dicatat di `input/2026-10-07-product-costing-jawaban/`. Pertanyaan semula: Master Material: Width 914 vs nama "…-1219-…"** pada baris contoh yang sama; dan baris
   contoh Berat/Meter 1,34 berasal dari kontrak supplier untuk lebar 1219.
9. **RESOLVED 2026-10-07 — Rp/Meter, hanya contoh.** Jawaban user: 27.400 dalam Rp/Meter, contoh pembanding saja. Pertanyaan semula: Satuan "Pricelist HFG saat ini: 27.400"** (Simulasi Costing, Y9) tidak disebut — per meter,
   per kg, atau per produk? Hasil simulasi 21.756 Rp/meter dan 16.236 Rp/Kg.
10. **RESOLVED 2026-10-07 — aktor tiap master.** Engineering: Master Produk, Master Material,
    BOM, Flow Process & Cycletime. Accounting: Kurs, Man Power Rate, Rate Bahan Pendukung/FOH,
    General Rate. Purchasing: Price Reference. Simulasi Costing & Pricelist: Accounting, Sales,
    dan Engineering. Dicatat di UC-N05–UC-N14.
11. **RESOLVED 2026-10-07 — disetujui.** Jawaban user: tiap baris punya satuan/jenis sendiri; Koordinasi Rp/Kg, lainnya persentase (FR-45). Pertanyaan semula: Header kolom `Rate/Meter` pada General Rate** salah label: isinya persentase dan Rp/Kg.
    Satuan tiap baris perlu menjadi bagian master (kolom satuan/jenis rate).

**Open questions Rev.1 (2026-10-19)** — butir 1–7 pertanyaan sesi ini sudah dijawab user dan
tercatat di "Revisi Rev.1"; berikut yang masih terbuka dari pembacaan Use Case:

12. **RESOLVED 2026-10-19 — Total Man Hour × Rate Man Power; butir 1.1 UC-12 salah salin (FR-56).** Pertanyaan semula: **Rumus Man Power Cost (UC-12 butir 1.1).** Use Case menulis `Rp/Meter = Rate Man Power ×
    Berat/Meter`, sedangkan BPMN (Total Man Hour/Kategori → Perhitungan Man Power Cost), ERD
    (`Man Power Cost`: Total Man Hour, Rate Man Power) dan FR-46 memakai `Total Man Hour × Rate
    Man Power (Rp/jam)`. UC-12 sendiri mensyaratkan UC-05 (Total Man Hour). Dugaan kami: salah
    salin pola Rate × Berat/Meter. Konfirmasi: tetap Total Man Hour × Rate Man Power?
13. **RESOLVED 2026-10-19 — rincian gaji/BPJS/173 jam tetap dipakai hanya untuk Operator; Helper dari master Borongan per Product Category (FR-53).** Pertanyaan semula: **Rumus Man Power Rate (UC-07).** Use Case: `(Rate 1/3 Operator + Rate Borongan Helper per
    kategori produk) × Faktor Indirect Labor`. FR-43/ERD: Salary Direct + BPJS + Biaya Lain ÷ 173
    jam × Faktor Indirect. Apa arti "1/3 Operator" dan "Borongan Helper", dan apakah rumus UC-07
    **menggantikan** rincian gaji/BPJS (FR-43), atau Rate 1/3 Operator berasal dari rincian itu?
14. **RESOLVED 2026-10-19 — Rp/Kg; Rp/Meter = Rate × Berat/Meter (FR-53, FR-56).** Pertanyaan semula: **Satuan Rate FOH dan Rate Bahan Pendukung Khusus.** FR-44 (jawaban 2026-10-07): Rp/meter
    langsung. UC-12: `Rate × Berat/Meter` (berarti rate Rp/Kg). Mana yang berlaku? Jika Rp/Kg,
    nilai contoh lama (150 dan 400 per meter) tidak lagi bisa dipakai.
15. **RESOLVED 2026-10-19 — berlaku global; satuan tidak ditampilkan karena klien tidak menginformasikan (FR-50).** Pertanyaan semula: **Master Standard Material (UC-03).** Apakah Standar Toleransi, Berat Jenis, dan Coating
    berlaku global, per Metal Type, atau per material? Satuan tiap nilai (Tebal dan Lebar dalam
    mm atau m; Berat Jenis g/cm³; Coating dalam kg/m atau lainnya)? Tanpa ini relasi entitas dan
    formula FR-51 belum bisa dipastikan.
16. **RESOLVED 2026-10-19 — per Product Category (FR-55).** Pertanyaan semula: **Cakupan Negotiation Allowance.** ERD menaruhnya di Rate Generals (semua produk), sedangkan
    UC-16 langkah 2 menulis "per Category Product". Global atau per Product Category? Apakah
    Sales boleh mengubahnya per perhitungan pricelist atau hanya memakai nilai master (UC-10)?
17. **RESOLVED 2026-10-19 — per Produk × varian BOM; alasan Reject disimpan; Product Catalog disimpan sebagai data (FR-58).** Pertanyaan semula: **Struktur Pricelist dan Product Catalog.** Apakah pricelist per Produk × varian BOM (per
    Rp/Meter dan Rp/Kg), dan apakah Draft Pricelist perlu menyimpan alasan Reject, versi, dan
    periode berlaku? Apakah Product Catalog disimpan sebagai data (snapshot harga yang disetujui)
    atau cukup status "Released" pada pricelist?
18. **RESOLVED 2026-10-19 — Rate 1/3 Operator = (Rate Direct Labor Operator ÷ 173 jam) × 1/3; Rate Borongan Helper Rp/Meter atau Rp/Pcs, dan Rp/Pcs ÷ Panjang Produk = Rp/Meter (FR-53). **Diubah 2026-10-20: Borongan hanya Rp/Meter, tanpa Rp/Pcs dan tanpa konversi.** Pertanyaan semula: **Arti "1/3 Operator" (UC-07, FR-53).** Kami menyimpannya sebagai
    parameter porsi operator (nilai 1/3) yang mengalikan Rate Direct Labor Operator. Apakah itu
    maksudnya (satu operator dibagi tiga mesin/lini), atau sudah nama baku dari rate lain? Satuan
    Rate Borongan Helper kami asumsikan Rp/jam supaya bisa dijumlahkan dengan rate operator.
19. **RESOLVED 2026-10-19 — rumus disetujui user; pembulatan sesudah dikali 1/3 (FR-53, FR-56).** `Man Power Cost (Rp/Meter) = (Total Man Hour × Rate 1/3 Operator + Rate Borongan Helper Rp/Meter) × Faktor Indirect Labor`; Total Man Hour hanya mengalikan bagian Operator. Rate 1/3 Operator dibulatkan ke atas ke Rupiah penuh **setelah** dikali 1/3 (tidak ada pembulatan lebih dulu pada Rate Direct Labor). Pertanyaan semula: **Rumus akhir Man Power Cost dengan Borongan Rp/Meter.** Angka acuan uji Man Power baru ditetapkan di NFR-11 (simulasi disetujui klien 2026-10-20).

**Open questions Extra Margin (2026-10-21, dari BPMN To Be Sales D01)** — belum diputuskan:

20. **Alasan Reject GM wajib?** BPMN hanya menggambar Reject → kembali ke Sales Manager. Diasumsikan wajib dan disimpan
    (`reject_reason`), seperti Pricelist (FR-58). Benar begitu, atau boleh kosong?
21. **Bolehkah Sales Manager menarik (membatalkan) pengajuan yang masih Menunggu Approval GM?** BPMN tidak menyebut. Bila
    boleh, perlu status tambahan (mis. Dibatalkan); sekarang hanya tiga status.
22. **Satu pengajuan per Product Category atau beberapa kategori sekaligus?** Diasumsikan satu per kategori. Bila banyak
    kategori sekaligus, perlu tabel detail pengajuan.
23. **Apakah rate pertama untuk kategori yang belum punya rate juga lewat approval GM?** BPMN: "Perlu Perubahan Margin?"
    → "Update dan Ajukan Rate Extra Margin baru". Diasumsikan semua pengisian dan perubahan rate lewat approval.
24. **Apakah GM boleh mengubah nilai yang diajukan saat menyetujui, atau hanya Approve/Reject apa adanya?** Diasumsikan
    hanya Approve/Reject apa adanya.

**Temuan review atas sheet Simulasi Costing** (untuk implementer; sheet di `input/` tidak diedit):

- Baris varian 2 (`Type 001.2`, 0,15 mm): kolom G (`Price Reference Rp/Kg`) menunjuk absolut ke
  `'Simulasi Program'!$J$28` (harga 0,14 mm: Rp13.165,03) padahal kolom E di baris yang sama
  benar memakai `I29` (USD 0,725 → Rp12.950,68). Akibatnya Material Cost varian 0,15 ter-lebih
  Rp308,67/meter (≈1,7%). Implementasi harus mengambil price reference **per material varian**.
- Konsumables 5%, Scrap 2%, Koordinasi Rp200, dan Negotiation 5% **ditulis literal** dalam rumus
  (kolom N, O, P, AN) walau nilai itu ada di sel General Rate. Sistem harus membacanya dari
  master (NFR-12).
- Sheet **Simulasi ide (copy)per kg** memakai 56.809,5 (rate per jam) langsung sebagai biaya
  Man Power per meter; sheet Simulasi Program sudah memperbaikinya (× Man Hour = Rp39,45/meter).
  Jangan dipakai sebagai acuan.

## NFR — Non-Functional Requirements

Setiap NFR dicatat dengan fit criterion terukur (Volere). Yang belum bisa diberi angka pasti
ditandai `{TBC}` dan masuk daftar "Perlu konfirmasi klien" di atas — kecuali ambang toleransi,
yang sudah resolved di atas (± 0,7%).

- **NFR-01 (Data quality — toleransi berat).** Sistem memblokir submit Laporan Produksi tanpa
  approval eksplisit ketika %Selisih (FR-09, FR-10, FR-15) melebihi **± 0,7%** — verifikasi:
  input yang melewati ambang tidak bisa submit tanpa melalui FR-16.
- **NFR-02 (Traceability).** Setiap Laporan Produksi yang submit harus tertaut ke SPK sumbernya,
  ke pack asal material (Unpack/WIP/Hold dari FR-04/FR-08), dan menjadi input satu-satunya untuk
  perhitungan HPP (FR-19–FR-23) dan jurnal (FR-24) — verifikasi: tidak ada baris HPP atau jurnal
  tanpa Laporan Produksi asal yang bisa ditelusuri.
- **NFR-03 (Master data, bukan free text).** Seluruh field bertanda "(Select …)" atau
  "(Otomatis muncul …)" pada sheet Konsep (SPK, Mesin, Helper, Setter, Master Product, Baby
  Coil) wajib berasal dari lookup ke data master/tarik-database, bukan input teks bebas —
  verifikasi: field tersebut tidak dapat diisi selain dari daftar pilihan yang disediakan
  sistem.
- **NFR-04 (Auditability).** Setiap input manual (Start/Finished Time produksi, Input Qty/Berat,
  approval toleransi, submit laporan) merekam user dan timestamp — verifikasi: setiap Laporan
  Produksi dapat menampilkan siapa menginput/approve/submit dan kapan.
- **NFR-05 (Role-based access).** Setiap lane BPMN (PPIC, Operator, Admin Produksi, Accounting,
  Warehouse Material/Finish Good) memetakan ke role sistem terpisah; hanya role Accounting dapat
  melakukan Posting Jurnal ke GL (FR-24); hanya role yang berwenang dapat memberi Approved
  Toleransi (FR-16) — verifikasi: user di luar role tersebut tidak melihat/bisa memicu aksi itu.
  Detail role/entity dicatat di `docs/rbac-entities.csv` saat modul ini di-breakdown ke UC.
- **NFR-06 (Hold Coil gate).** Tombol Save pada Laporan Produksi non-aktif selama ada Hold Coil
  yang belum di-release atau di-claim pada sesi tersebut (FR-17) — verifikasi: mencoba submit
  dengan Hold Coil aktif ditolak sistem, bukan hanya diperingatkan di UI.
- **NFR-07 (Integrasi rate biaya & costbook).** (a) Standard rate Man Power, FOH, Bahan
  Pendukung Khusus, Consumables, dan Biaya Koordinasi yang dipakai FR-19/FR-20 diambil real-time
  dari Product Costing, bukan nilai yang disalin/statis. (b) Costbook Material yang dipakai
  FR-19/FR-21/FR-22/FR-23 diambil real-time sebagai nilai rata-rata gudang **saat itu**, dan
  selalu dari gudang asal baby coil yang sebenarnya dipakai (Produksi 2 / WIP / Hold) — bukan
  nilai gudang lain — verifikasi: perubahan rate di Product Costing atau nilai rata-rata di
  gudang terkait langsung memengaruhi perhitungan HPP berikutnya tanpa perlu re-entry manual, dan
  HPP dari baby coil sumber WIP/Hold tidak pernah memakai costbook Gudang Produksi 2.
- **NFR-08 (Responsivitas lookup).** Saat user memilih Baby Coil pada Add Coil (FR-08) atau
  field manapun bertipe "(Select …)"/"(Otomatis muncul …)", data terkait (nama material, berat,
  costbook) tampil tanpa reload halaman — `{TBC}`: ambang waktu respons perlu disepakati dengan
  klien.

### NFR Product Costing — 2026-10-07

- **NFR-09 (Imutabilitas rate pada laporan — mengubah NFR-07a).** Rate Product Costing yang
  dipakai FR-48 dibekukan pada saat laporan produksi dibuat (FR-49). **NFR-07(a) tidak lagi
  berlaku** untuk laporan yang sudah ada; "real-time" hanya berlaku untuk laporan **baru**. Fit
  criterion: setelah Rate Man Power / Rate Bahan Pendukung / Rate FOH / General Rate diubah,
  `total_hpp_item` dan seluruh kolom `*_cost_*` laporan yang dibuat sebelum perubahan **tidak
  berubah satu rupiah pun**, dan setiap baris item dapat menunjukkan rate (nilai atau versi) apa
  yang dipakai. NFR-07(b) (Costbook Material) tidak berubah.
- **NFR-10 (Auditability master costing).** Setiap perubahan pada master Product Costing (Kurs,
  Price Reference & keputusan pemakaiannya, BOM, Flow Process, Man Power Rate, Rate Bahan
  Pendukung/FOH, General Rate) merekam user dan timestamp (`Latest Update`, `Updated By`), dan
  nilai lama tetap dapat ditelusuri. Fit criterion: untuk rate mana pun dapat ditampilkan siapa
  mengubahnya, kapan, dan nilai sebelumnya.
- **NFR-11 (Akurasi kalkulasi).** Angka acuan uji adalah **simulasi yang disetujui klien pada 2026-10-20** (data contoh mockup
  `ui-mockup/pc-data.js`, menggantikan angka lama sheet Simulasi Costing dan angka sementara `{TBC}`). Dengan input yang sama,
  sistem menghasilkan angka berikut dengan selisih ≤ Rp0,01 per komponen. **Input kunci:** Kurs USD Rp17.863; Berat Jenis 7,85,
  Toleransi 0, Coating 0, Lebar Coil 1219; Price Reference = USD/Kg Max History Pembelian (Type 001.1: 0,81; Type 001.2: 0,80);
  Rate 1/3 Operator Rp12.625/jam (Total Direct Labor Operator Rp6.551.982,55/bulan ÷ 173 × 1/3, dibulatkan ke atas);
  Total Man Hour 0,000694 jam/meter (cycletime 1/24 menit/meter × 1 orang ÷ 60); Rate Borongan Helper Rp25/Meter; Faktor
  Indirect Labor 1,5; BPK Rp110/Kg; FOH Rp300/Kg; Consumables Rp300/Kg; Koordinasi Rp200/Kg; Scrap 2%; Other Cost hanya Profit 5%;
  Negotiation Allowance 5%. **Hasil (Rp/Meter):**

  | Komponen | Type 001.1 (0,14 mm) | Type 001.2 (0,15 mm) |
  |---|---:|---:|
  | Berat/Meter (kg/m) | 1,3397 | 1,4354 |
  | Material Cost | 19.383,88 | 20.512,05 |
  | Man Power Cost | 50,65 | 50,65 |
  | Bahan Pendukung Khusus / FOH | 147,36 / 401,90 | 157,89 / 430,61 |
  | Biaya Proses | 599,92 | 639,15 |
  | Consumables / Scrap / Koordinasi | 401,90 / 387,68 / 267,94 | 430,61 / 410,24 / 287,07 |
  | Manufacturing Support | 1.057,52 | 1.127,93 |
  | HPP | 21.041,32 | 22.279,13 |
  | Other Cost (Profit 5%) | 1.052,07 | 1.113,96 |
  | Bottom Price | 22.093,39 | 23.393,08 |
  | Negotiation Allowance (5%) | 1.104,67 | 1.169,65 |
  | **Price List/Meter** | **23.198,06** | **24.562,74** |
  | Price List/Kg | 17.316,11 | 17.112,45 |

  Angka ini data contoh yang disetujui klien sebagai acuan uji; bukan rate produksi. Rate 1/3 Operator dibulatkan ke atas ke
  Rupiah penuh sesudah dikali 1/3 dan komponen lain 2 desimal tanpa pembulatan (FR-53, FR-60). Rumus lama (Rate Man Power
  Rp56.809,5/jam; Price List `Type 001.1` Rp21.756,15; Material Cost Rp17.641,14) tidak berlaku lagi.
- **NFR-12 (Tanpa angka tertanam).** Seluruh persentase, Rp/Kg, faktor, dan konstanta (Faktor
  Indirect Labor, 173 jam, Consumables, Scrap, Koordinasi, Other Cost, Negotiation Allowance)
  dibaca dari master, tidak ditulis di kode. Fit criterion: mengubah nilai di master mengubah
  hasil kalkulasi berikutnya tanpa deploy; pencarian di kode tidak menemukan literal 0,05 /
  0,02 / 200 / 173 / 1,5 sebagai rate bisnis.
- **NFR-13 (Hak akses master costing).** Hanya role yang ditetapkan klien yang dapat mengubah
  master Product Costing; role lain hanya melihat. Aktor per master sudah ditetapkan (open
  question 10: Engineering, Accounting, Purchasing); verifikasi: user di luar role pemilik master
  tidak dapat menyimpan perubahan. Detail role dicatat di `docs/rbac-entities.csv` saat
  di-breakdown.
- **NFR-14 (Traceability costing → laporan).** Setiap baris `production_report_items` dapat
  menunjuk varian BOM (No. Uniq ID) dan set rate yang dipakai menghitung HPP-nya. Fit criterion:
  dari satu baris item dapat dibuka kembali seluruh rumus FR-46/FR-48 beserta input yang sama
  dan hasilnya sama dengan yang tersimpan.

- **NFR-15 (Konsistensi nama ERD dan kode).** Untuk data yang berasal dari database, nama tabel dan kolom di kode, migrasi,
  dan query harus persis sama dengan ERD (`docs/diagrams/erd.drawio`) (user 2026-10-20). Bila berbeda, ERD diperbaiki (jika
  ringkas atau keliru) atau tabelnya; tidak ada alias diam-diam. Label tampilan di UI boleh berbeda; keterangan dalam kurung pada label kolom ERD (mis. `wide (lebar, mm)`) bukan bagian nama kolom. Fit criterion: pada
  walkthrough tiap fitur, setiap kolom yang dipakai dicocokkan dengan ERD dan selisihnya nol. Tabel yang sudah ada di aplikasi
  (`product_lvl_4`, `new_inventory_4`) digambar ringkas sampai `SHOW CREATE TABLE` dikonfirmasi; pemetaannya di
  `.artifacts/master_data_product_costing/pemetaan_nama_kolom_erd_20-10-2026.md` (v2: `…_v2.md`). Penunjuk ke produk di tabel baru memakai
  `code_lv4` dan penunjuk ke Product Category memakai `code_lv2` (user 2026-10-20), mengikuti praktik tabel yang ada (`ms_bom_header.id_produk` = `code_lv4`).

## Status

Draft, diturunkan langsung dari `input/`. Titik ambigu dari pasangan sumber awal (BPMN + sheet
"Konsep yang akan dipakai") sudah dikonfirmasi klien: ambang toleransi selisih berat (± 0,7%),
pembedaan Costbook Material (nilai rata-rata per material per gudang asal) vs. Product Costing
(standard rate produk, modul terpisah yang hanya dibaca dari sini). Sejak 2026-09-22, ERD revisi
klien (`input/Rev.1 erd.drawio produksi.drawio`) menambah FR-25–FR-30 dan membalik scope FR-01
(lihat Re-Confirm) — **tiga item baru masih terbuka** dan tercatat eksplisit di "Open questions"
pada Re-Confirm: duplikasi baris Sisa Coil di tabel `HPP`, target FK `asset.nm_asset`/
`nm_category` yang tidak tergambar, dan gap granularitas FR-14 (capture Scrap) vs. field yang
sudah dikonfirmasi di sisi valuasi (FR-27). Sejak 2026-09-24, Rev.2
(`input/Rev 2 Production Report.drawio`) **menggantikan** struktur Report Produksi/HPP Rev.1:
FR-31–FR-36 ditambahkan dan menandai FR-26–FR-30 yang tergantikan (lihat bagian Rev.2). Bagian
FR-01–FR-24 dan NFR-01–NFR-08 yang tidak disebut di atas tetap seperti draft sebelumnya (tidak
diubah). Decompose ke `docs/workbook.xlsx`
sebaiknya menunggu klarifikasi tiga open question di atas untuk bagian yang mereka pengaruhi (HPP
Scrap Sisa Coil, dimensi asset); bagian lain sudah siap di-decompose.

**Pembaruan 2026-10-07 (Product Costing).** FR-37–FR-49 dan NFR-09–NFR-14 ditambahkan dari
`input/ERD Product Costing-Production Report.jpg` dan `input/Rev.1 Ide Costing Kg konversi ke
Meter.xlsx`; Product Costing kini **in scope** (membalik entri Re-Confirm 2026-09-21) dan NFR-07(a)
diubah oleh NFR-09. Seluruh 11 open question Product Costing sudah dijawab user 2026-10-07 (kunci rate: Product
Category; Tebal/Panjang tetap di Master Produk; Berat per Pcs = Berat/Meter varian BOM × Panjang;
Rate Scrap dari Rate Generals; BPJS manual dan 173 jam parameter; Width 1219; Pricelist HFG
27.400 Rp/Meter; satuan per baris General Rate). FR-37–FR-47 sudah di-decompose ke
UC-N05–UC-N14. Rumus per pcs di Laporan Produksi mengikuti mock up sebelumnya (di luar FR-37–FR-49).

**Pembaruan 2026-10-19 (Product Costing Rev.1).** BPMN To Be, Use Case UC-01–UC-19, dan ERD baru
(`input/2026-10-19-product-costing-rev1/`) menjadi referensi utama. FR-50–FR-58 ditambahkan
("Revisi Rev.1"); FR-38, FR-40, FR-43, FR-44, FR-45, FR-46 sebagian digantikan menurut peta perubahan di
sana. Open question 12–17 dijawab user 2026-10-19 (ronde kedua) dan sudah masuk FR-50–FR-58; yang
**tidak ada open question Product Costing yang masih terbuka** (OQ 18 dan 19 dijawab 2026-10-19);
angka acuan uji ditetapkan di NFR-11 (simulasi disetujui klien 2026-10-20).

**Pembaruan 2026-10-20.** FR-59–FR-66 (fungsi hasil review mockup) dan NFR-15 (nama kolom = ERD) ditambahkan; NFR-11 memakai angka
simulasi yang disetujui klien; Lebar produk memakai kolom `wide` (FR-64). Perubahan lain hari itu: Rate Borongan Helper hanya
Rp/Meter dan Faktor Indirect Labor master global (FR-53).

**Pembaruan 2026-10-21 (BPMN To Be Sales D01).** FR-67 menambah persetujuan GM atas perubahan rate Negotiation Allowance
(pengaju Sales Manager) dan merevisi FR-55. Persetujuan **diskon Quotation** (Sales/Sales Manager/GM) serta SO, SPM, dan
Production Plan di BPMN yang sama **di luar scope Product Costing**. Open question baru 20–24 (alasan Reject, tarik pengajuan,
banyak kategori, rate pertama, GM mengubah nilai) belum diputuskan; asumsi sementara tercantum di FR-67.
