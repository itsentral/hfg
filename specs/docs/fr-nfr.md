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

## Re-Confirm

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
