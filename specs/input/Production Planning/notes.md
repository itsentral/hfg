# 2026-11-19 — Proses Subkont dari Production Planning

Sumber: pesan user (chat), 2026-11-19 s.d. 2026-11-20, atas tiga materi klien di folder ini:
- `Prod.Plan To Be D02-PTHFG.png` (BPMN To Be Production Planning: lane PPIC / Top Management; proses lain: Sales, Stok Finish Good,
  Stok Material, Delivery, Proses Subkont, Warehouse Material, Produksi).
- `Subkont To Be D002-PTHFG.png` (BPMN To Be L3-Proses Subkont: lane PPIC / Vendor).
- `3. Rev.2 Konsep & Simulasi Laporan Slitting Subkont.xlsx` (5 sheet: Flow, Prod. Plan, Surat Jalan ke Vendor (Rev2),
  Laporan Subkont (Material Slit), Incoming&Laporan Slit Vendor; berisi konsep input + simulasi angka + gambar contoh SPK Material,
  PO jasa slit (Accurate) dan Delivery Order vendor).

Permintaan: pelajari flow dan konsep; jangan langsung membuat mockup Subkont; lengkapi dokumen (ERD, Use Case, FR/NFR) lebih dulu.
Urutan kerja yang diminta: (1) mockup modul `fg_stock` (selesai, `ui-mockup/fg-stock.html`), (2) pelajari gambar flow dan konsep lalu
tanya klarifikasi, (3) lengkapi dokumen.

## Intisari alur (dari BPMN dan workbook)

1. **Production Planning (PPIC):** hitung total kebutuhan produksi dan jual material (dari SO, Stok FG, Stok Material, Plan Delivery,
   Forecast Top Management) → "Apakah perlu proses Subkont?" → **Perlu:** Buat SPK Subkont (Material Slit / Tambah Pasir untuk
   Finish Good) → ke Proses Subkont. **Tidak perlu:** Tentukan Tujuan Material → Produksi (Buat SPK Produksi → Request Material untuk
   Produksi ke Warehouse Material) atau Penjualan (Buat SPM / Surat Perintah Muat → Request Material untuk Pengiriman).
2. **Proses Subkont:** SPK Subkont → PPIC Request Material/FG untuk Subkont ke Warehouse Material / Warehouse Finish Good →
   PPIC Buat Surat Jalan Kirim ke Vendor (jurnal Db Material In Supplier / Cr Inventory) → Vendor proses slitting → Vendor login portal
   dan input data LHP (Laporan Hasil... Subkont) → Vendor kirim material hasil slitting dengan surat jalan → PPIC Incoming
   Material/Finish Good → cek material yang diterima vs data LHP → Sesuai? **Ya:** Konfirmasi Hasil Pengecekan Incoming (stok material
   slit dan stok FG + pasir masuk; jurnal Db Inventory, Db Scrap, Db Selisih Net Weight, Cr Material In Supplier, Cr Unbill) → Update
   Nilai Costbook Material. **Tidak:** Klarifikasi ke Supplier → temuan ketidaksesuaian kembali ke portal vendor.
3. Konsep angka (sheet "Laporan Subkont" dan "Incoming"): Nett Weight Before 3.798 kg, Nett Weight Actual hasil slitting 3.778 kg dibagi
   rata ke 8 roll baby coil (472,25 kg), Scrap 17 kg, selisih 3 kg (0,079%); biaya jasa 270,27/kg × 3.778 = 1.021.080,06; costbook
   gudang 10.000/kg; Nilai Material Gudang + Biaya Proses = 38.801.080,06; jurnal incoming seimbang di 39.001.080,06.
4. Sheet "Laporan Subkont" juga memuat jurnal COGM dan tutup bulan penuh `#REF!` (salinan konsep Laporan Produksi) — **diabaikan**
   atas keputusan user (jawaban 14).

## Pertanyaan agen dan jawaban user (2026-11-20)

Kutipan apa adanya; nomor = nomor pertanyaan agen.

> 1. Perluan dari dua modul itu
> 2. Ya, sisanya dicatat sebagai flow saja
> 3. Setuju
> 4. Berlaku sama untuk material dan FG. Gudang juga boleh siapkan sebagian.
> 5. Betul
> 6. PO tidak perlu dihubungkan dengan SPK Subkont. Biarkan itu sebagai PO jasa subkont terpisah. Nanti nilai biaya proses dimasukkan pada saat input Laporan Subkont.
> 7. Setuju
> 8. Satu SPK boleh dikirim dari beberap surat jalan. Untuk SPK, tidak melihat mau dikirim ke vendor mana. Penentuan kirim ke vendor mananya nanti saat pembuatan surat jalan saja.
> 9. Portal nanti dibentuk seperti E-Proc pada aplikasi yang sudah umum di dunia industri. Sehingga, vendor mendaftarkan diri mereka atau membuat akun dulu untuk bisa akses. Vendor tidak boleh melihat costbook dan nilai. Setelah "Close Laporan", apabila ada yang perlu diperbaiki kembali ajukan perbaikan ke user di HFG agar dibuka akses untuk edit.
> 10. Input tambahan material lainnya dan input total kilogramnya. Hasilnya menjadi kode FG Baru. Biaya jasanya per Kg.
> 11. Jika tidak sesuai, LHP dikonfirmasi kembali ke vendor.
> 12. Dibagi rata seperti unpack coil dan masuk ke gudang produksi 2. Rumus Costbook yang kamu bilang sudah benar.
> 13. Betul. Untuk Kode kamu random dulu saja. Karena penetapan kode akun nanti oleh saya sebagai Business Analyst yang akan discuss dengan client.
> 14. Diabaikan saja.
> 15. Boleh ditambah notes.md di Folder Production Planning tersebut.

Pemetaan ke pertanyaan agen (untuk keterlacakan): 1 = SPK Subkont di modul `production_planning`, Surat Jalan Subkont di `delivery_fg`
(perluasan, bukan modul baru); 2 = ruang lingkup spesifikasi hanya SPK Subkont, langkah lain Prod.Plan (hitung kebutuhan, tentukan
tujuan, SPM, SPK Produksi) hanya dicatat sebagai flow; 3 = status SPK Subkont Draft → Requested → Dikirim Sebagian → Dikirim Penuh →
Incoming → Closed (+ Cancelled); 4 = Tampungan Siap Kirim berlaku untuk material dan FG, gudang boleh menyiapkan sebagian; 5 = qty
material per Pack, FG per pcs, sumber stok pack gudang PRO dan `fg_stock` KW 1; 6 = PO jasa tidak dihubungkan ke SPK; 7 = jurnal Surat
Jalan FG: Db Material In Supplier / Cr Inventory Barang Jadi; 8 = beberapa Surat Jalan per SPK, vendor dipilih saat Surat Jalan;
9 = portal gaya e-procurement dengan pendaftaran mandiri; 10 = SPK Subkont FG: input material tambahan + total kg, hasil = kode FG baru,
biaya jasa per Kg; 11 = ketidaksesuaian → LHP dikonfirmasi kembali ke vendor; 12 = baby coil dibagi rata, masuk Gudang Produksi 2,
rumus costbook baby coil disetujui; 13 = scrap 100% ke Inventory Scrap, selisih ke akun selisih, kode akun sementara; 14 = jurnal
COGM/tutup bulan di sheet 4 diabaikan; 15 = izin menambah file ini.

Yang belum dijawab user (tercatat sebagai open question OQ-S di `docs/fr-nfr.md`): toleransi dan dasar perbandingan "Sesuai?"
(pertanyaan 11 hanya dijawab bagian "jika tidak sesuai"), siapa yang menginput biaya jasa (PPIC HFG vs vendor), pasokan material tambahan
(pasir) pada SPK Subkont FG, jurnal incoming untuk SPK FG, pembuatan kode FG baru, dan verifikasi akun vendor.

Extracted: FR-99–FR-125, NFR-18–NFR-21, OQ-S1–OQ-S10 → `docs/fr-nfr.md`; UC-17–UC-27, UC-N37–UC-N38, PG-37–PG-49 →
`docs/workbook.xlsx`; entitas Subkont → `docs/diagrams/erd-revisi.drawio` halaman "Subkont ERD"; FEAT-12 → `pm/tracker.yaml`.

## Jawaban open question OQ-S1–OQ-S10 (2026-11-20, menyusul)

Kutipan apa adanya:

> S1 : Sesuai usulan
> S2 : Sesuai Usulan
> S3 : Menggunakan pasokan pasir dari vendor. Jadi, HFG tidak ada kirim pasir.
> S4 : Sesuaikan saja dengan yang ada, bedanya Debitnya ke Finish Good.
> S5 : Dibuat otomatis dengan kode = "Nama Produk/Kode Internal Material/No.Urut Subkont" Otomatis saja sesuai usulan kamu. Costbook berdasarkan total kg hasil per produk.
> S6 : Perlu persetujuan HFG dan terhubung dengan master supplier. Master Supplier kamu bisa mockup mengikuti modul supplier
> S7 : Setuju dengan usulan kamu
> S8 : Tidak ada batas waktu klarifikasi. Temuan ditutup oleh pihak HFG bagian PPIC nya. Incoming boleh dicatat sebagian.
> S9 : Iya betul.
> S10 : Pelunasannya nanti berdasarkan PO Jasa saja. Karena bisa saja jasa tersebut menggunakan informasi "1 lot jasa subkont" Jadi tidak terdefinisikan per Kg nya berapa.

Pemetaan (usulan agen yang disetujui ditulis di `docs/fr-nfr.md`): S1 = cek vs LHP memakai timbang HFG per pack, toleransi ± 0,7%;
S2 = PPIC HFG menginput biaya jasa di Incoming; S3 = pasir dipasok vendor, HFG tidak mengirim pasir (tidak ada item pasir di SPK dan
Surat Jalan); S4 = jurnal incoming SPK FG sama dengan Material tetapi Debit ke Inventory Finish Good; S5 = kode FG baru otomatis
`Nama Produk/Kode Internal Material/No. Urut Subkont`, costbook FG baru dihitung atas total kg hasil per produk; S6 = akun vendor butuh
persetujuan HFG dan terhubung ke master supplier, dan **mockup Master Supplier mengikuti modul `supplier` yang sudah ada**; S7 = satu laporan
per Surat Jalan per material; S8 = tanpa batas waktu klarifikasi, temuan ditutup PPIC, Incoming boleh sebagian; S9 = kode akun tetap
sementara sampai BA menetapkan; S10 = pelunasan Unbill berdasarkan PO Jasa (bukan per Kg, karena jasa bisa "1 lot").

Dua turunan baru dari jawaban ini (diajukan sebagai OQ-S11 dan OQ-S12 di `docs/fr-nfr.md`, dengan usulan agen): (S11) karena jasa bisa 1 lot,
biaya jasa boleh diinput sebagai total Lot selain Price/Kg; (S12) aktor persetujuan akun vendor dan arti "Kode Internal Material" pada kode FG baru.

Extracted: OQ-S1–OQ-S10 ditandai RESOLVED; FR-113, FR-116, FR-118, FR-119, FR-120, FR-122, FR-123 direvisi; FR-125 (persetujuan akun vendor +
Master Supplier) dan UC-N39, PG-50, PG-51 ditambahkan.

## Jawaban OQ-S11 dan OQ-S12 (2026-11-20, menyusul)

Kutipan apa adanya:

> S11 : Setuju
> S12 : Betul, Purchasing yang menyetujui. Betul Kode internal berarti kode internal material dasar produk.

Pemetaan: S11 = biaya jasa boleh diinput sebagai Lot selain Price/Kg (Jenis Tarif), dan costbook FG baru = (nilai FG kirim + biaya jasa) ÷ qty hasil
per produk; S12 = **Purchasing** menyetujui akun vendor (menautkan ke Master Supplier), dan "Kode Internal Material" pada kode FG baru = kode internal
material dasar produk (contoh `DEBDA0319-T1`). Seluruh OQ-S1–OQ-S12 kini terjawab.

## Keputusan tambahan: vendor pada Surat Jalan (2026-11-20, setelah baca ulang dokumen)

Pertanyaan agen: apakah Surat Jalan hanya boleh memilih supplier yang akun portalnya Aktif? Kutipan jawaban user apa adanya:

> Surat Jalan memilih vendor dari Master Supplier yang sudah ada saja. Karena sama saja ujung-ujungnya apa yang ada di Master Supplier, itu yang bisa dipilih.

Pemetaan: pilihan vendor pada Surat Jalan = seluruh Master Supplier, **tanpa syarat** akun portal Aktif.

## Perubahan Portal Vendor (2026-11-20, menyusul)

Kutipan user: "Tidak jadi seperti E-Proc. Vendor tidak perlu daftar akun dan diverifikasi ke HFG. Cukup HFG lihat dari daftar master vendor, lalu kasih link portal vendornya." Jawaban atas pertanyaan agen (cara masuk lewat link): link unik per vendor (token), tanpa password. Menggantikan jawaban 9 (portal e-proc, daftar mandiri) dan OQ-S6/OQ-S12(a) (persetujuan akun oleh Purchasing).
