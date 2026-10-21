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
