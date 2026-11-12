# 2026-10-27 — Proses Claim Material (Kompensasi / Retur)

Sumber: pesan user (chat), 2026-10-27, atas dua materi klien di `input/Claim Process/`:
- `To Be_Claim Coil_D003-PTHFG.png` (BPMN To Be D003: lane Purchasing / PPIC / Produksi).
- `4. Rev.2 Mockup Claim Material (based on Concept).xlsx` (wireframe + simulasi jurnal; 13 sheet).

Permintaan: lanjut ke Proses Mockup untuk Proses Claim. Pemicu: input Laporan Produksi bagian KW 2 (Supplier),
Hold Coil, dan Reject Supplier masuk ke Proses Claim.

## Jawaban atas pertanyaan terbuka (2026-10-27)

Kutipan apa adanya:

> 1. Dua-duanya nanti terpakai karena akan mengurangi biaya KW 2 yang terjurnal di HPP dan Mengurangi Biaya Scrap.
> 2. Untuk jalur retur, ketika buat Debit Note tidak perlu jurnal dari in transit lagi. Debit note hanya menjurnal rute dari jalur Kompensasi.
> 3. Jurnal yang benar sesuai workbook Db Bank / Cr Debit Note.
> 4. Nilai material dari Gudang Bahan Persediaan Produksi dipecah menjadi 2 : Material KW (material dari gudang bahan persediaan produksi diakui sebagai inventory) dan Biaya Scrap (biaya kerugian dari nilai material dari gudang bahan persediaan produksi). Nanti akan ada pembagian bobot nilainya antara yang masuk ke Material KW dan ke Biaya Scrap.
> 5. Satu coil bisa dibuat di dua jalur. Karena bisa saja coil tetap dikembalikan dan juga dikompensasikan contohnya bisa dilihat dari sheet "Simulasi Claim Kombinasi jalur" pada poin 6.
> 6. Itu hanya contoh kasus dari HFG. Tidak perlu pakai field persen, lebih baik langsung input nilainya saja langsung.
> 7. Setuju. (PPN 11% untuk kompensasi/DN, 12% untuk PO, seperti workbook.)
> 8. Iya, betul. (Finance/Accounting melakukan Alokasi DN ke Pembayaran dan Konfirmasi Penerimaan Uang.)

Pemetaan ke pertanyaan: 1 = akun kredit DN Kompensasi; 2 = jurnal DN Retur ganda; 3 = jurnal Penerimaan Uang; 4 = jurnal Jual
Material; 5 = coil satu jalur atau dua; 6 = rumus nilai kompensasi; 7 = pajak; 8 = pemisahan lane.

## Intisari alur (dari BPMN dan workbook)

1. Produksi → Purchasing: Data Hold Coil, Reject Material, KW 2 Supplier, Selisih Coil. Purchasing kirim email dokumen claim; supplier konfirmasi; diskusi sampai ada kesepakatan penanganan.
2. Purchasing memilih skenario per baris claim: **Retur** dan/atau **Kompensasi** (satu coil boleh di dua jalur).
3. Kompensasi: (a) tetapkan perlakuan coil — Lanjut Produksi (catatan khusus) atau Jual Material (Gudang Slitting); (b) tetapkan Nilai Kompensasi (input nilai langsung, bukan persen) → Report Biaya Kompensasi vs Biaya Scrap.
4. Retur: PPIC buat SPK Re-Pack → Produksi pindahkan material ke gudang → PPIC buat Surat Jalan → pengiriman → Confirm Surat Jalan.
5. Terbitkan Debit Note (List Debit Note) → dipakai sebagai pengurang hutang di Pembayaran PO (Alokasi DN), atau ditagih langsung ke supplier (Penerimaan Uang).

## Jurnal yang dikonfirmasi

| Titik | Debit | Kredit |
|---|---|---|
| Debit Note Kompensasi terbit | Debit Note (Kompensasi) | Biaya KW 2 Supplier dan/atau Biaya Scrap |
| Debit Note Retur terbit | — (tidak ada jurnal) | — |
| Surat Jalan Retur dibuat | In Transit | Inventory |
| Surat Jalan Retur dikonfirmasi diterima supplier | Debit Note (Retur) | In Transit |
| Jual Material | Material KW (inventory) + Biaya Scrap | Gudang Bahan Persediaan Produksi |
| Pembayaran PO dengan Debit Note | Hutang | Bank + Debit Note (Kompensasi) + Debit Note (Retur) |
| Penerimaan Uang dari Supplier | Bank | Debit Note |

Extracted: FR-75–FR-92 dan OQ-C1–OQ-C5 → `docs/fr-nfr.md`; FEAT-11 → `pm/tracker.yaml`.

## Jawaban OQ-C1–C5 (2026-10-27, menyusul)

Sumber: pesan user (chat). Kutipan apa adanya:

> - OQ-Q1 : Purchasing
> - OQ-C3 : Perlu
> - OQ-C4 : Iya betul
> - OQ-C5 : Betul
> - OQ-C2 : Boleh

"OQ-Q1" dibaca sebagai OQ-C1. "Boleh" pada OQ-C2 dibaca sebagai setuju bahwa master email boleh ditunda (usulan agen: "Boleh ditunda").
Extracted: OQ-C1–C5 ditandai RESOLVED/DITUNDA di `docs/fr-nfr.md`; FR-06 disesuaikan (Purchasing).

## Jawaban atas daftar pertanyaan terbuka (2026-10-27, sesi lanjutan)

Sumber: pesan user (chat). Kutipan apa adanya:

> 1. Untuk COA nanti akan ditentukan pada saat sudah buat Master Jurnalnya khusus untuk proses Claim nanti. Posisi terposting ke GL sesuai dengan proses dari flow. Misalnya ketika proses Buat Surat Jalan ke Supplier, otomatis jurnal ke GL sesuai dengan Master Jurnal pada proses tersebut.
> 2. Menjadi baris tambahan di alur pembayaran yang ada
> 3. Boleh
> 4. Boleh
> 5. Tidak perlu release secara manual, otomatis saja
> 6. Benar
> 7. Ditolak saja dulu, Harusnya sama.
> 8. Betul
> 9. Boleh edit sebelum terbit. Yang berhak hanya yang memiliki akses.

Pemetaan: 1 = OQ-C6; 2 = OQ-C7; 3 = master email ditunda (OQ-C2); 4 = aktor UC-N34/PG-27/UC-N35 dari mockup; 5 = Release coil (FR-06);
6 = DN Retur hanya setelah Surat Jalan; 7 = nominal penerimaan beda dari DN; 8 = nilai claim dalam mata uang PO; 9 = Edit Debit Note.
Extracted: FR-06, FR-85, FR-88, FR-90 direvisi dan FR-93 baru di `docs/fr-nfr.md`; OQ-C6, OQ-C7 RESOLVED; OQ-C8 baru (titik penempelan alokasi DN).

## Jawaban OQ-C8 (2026-10-27)

> Saya setuju dengan pilihan kamu untuk OQ-C8, lanjut popup Hold & Claim

Pilihan yang disetujui: (a) Alokasi DN dipilih saat Request Payment; (b) DN sebagai baris detail negatif di `payment_approve_details` plus tabel
penghubung `alokasi_debit_note`. Extracted: OQ-C8 RESOLVED dan FR-88 direvisi di `docs/fr-nfr.md`.

## Jawaban OQ-C9 (2026-10-27)

> a. Saran kamu apa?
> b. Betul lewat tombol ini

(b): KW 2 / Reject Supplier tanpa Hold Coil dikirim lewat tombol Hold & Claim. (a): user meminta saran; usulan agen dicatat di OQ-C9 `docs/fr-nfr.md`
(belum disetujui). Extracted: OQ-C9 (b) RESOLVED, FR-94 dilengkapi.

## Jawaban OQ-C9(a) (2026-10-27)

> Setuju, lanjutkan

Disetujui usulan agen: baby coil asal Reject Supplier dipilih di popup Hold & Claim, otomatis hanya bila SPK memakai satu baby coil, selain itu wajib dipilih.
Extracted: OQ-C9 RESOLVED di `docs/fr-nfr.md`; popup `ui-mockup/index.html` + `app.js` disesuaikan dan diuji.

## Jawaban telaah alur mockup (2026-10-28)

> G1 : Sambungkan saja
> G6 : Suggest kamu seperti apa?
> G3 : Gudang slitting hanya daftar baca saja untuk material yang dijual
> G4 : Saya setuju dengan usulan kamu
> G5 : Saran kamu seperti apa?

Pemetaan (dokumen telaah `.artifacts/claim_material/alur_mockup_claim_material_28-10-2026.md`): G1 = Release coil tersambung ke Laporan Produksi;
G3 = halaman Gudang Slitting; G4 = kolom Progres turunan; G5 = cakupan Report Biaya; G6 = SPK Re-Pack gabungan atau satu per claim.
G6 dan G5: user meminta saran agen (belum diputuskan). Extracted: FR-95, FR-96, FR-97 dan OQ-C10 di `docs/fr-nfr.md`; UC-N36, PG-36 di workbook.

## Jawaban G5, G6, OQ-C10 (2026-10-28)

> OQ-C10 benar. Saya setuju dengan saran kamu di G6 dan G5. Silahkan lanjutkan ke tahap berikutnya.

Disetujui: G6 = gabungkan per supplier saat Create SPK (satu SPK = satu SJ; DN Retur tetap per keputusan); G5 = Report Biaya memuat baris Debit Note dan
Jual Material, tanpa Retur dan Lanjut Produksi; OQ-C10 = coil Hold yang di-claim tidak dapat dipilih dari Unpack/WIP. "Tahap berikutnya" dibaca sebagai
rencana implementasi CI3 (Handover ke Dev, AGENTS.md). Extracted: FR-83, FR-84, FR-87 direvisi; OQ-C10 RESOLVED di `docs/fr-nfr.md`.

## Jawaban D0-D7 rencana implementasi CI3 (2026-10-28)

> D0 : Setuju / D2 : Setuju / D4 : Setuju / D5 : Semua tipe. Untuk sisanya : D1 : Betul / D3 : Betul / D6 : Betul / D7 : Betul, itu nanti saja karena lebih baik ada standard kalimat emailnya juga.

Disetujui: D0 skema asli diambil via SHOW CREATE TABLE; D1 catatan ERD "baby_coil = warehouse_stock_coil"; D2 coil dipindah ke Hold saat claim terkirim, status baru saat Lanjut Produksi (hold_released);
D3 harga_beli_per_kg dari costbook; D4 Master Jurnal Claim tanpa fallback (kode kosong = jurnal ditandai gagal + log, transaksi bisnis tetap tersimpan); D5 alokasi DN untuk semua tipe invoice PO;
D6 verifikasi via skrip SQL + walkthrough di database uji, tulis testing.md; D7 email: tidak ada pengiriman sungguhan dulu, ditunda sampai ada standar kalimat email (terkait OQ-C2).

## Jawaban poin matang desain (2026-10-28)

> 1. Debit Note tidak perlu ada Approval dulu untuk memangkas waktu. 2. Hak Akses nanti diatur dari permision di programnya. Untuk saat ini biarkan saja terbuka karena hanya untuk mockup. 3. Nanti dulu saja. 4. Dibuatkan sebagai tampilan output di depan layar saja. Tidak perlu dibuat tabel. 5. Boleh

Extracted: Debit Note tanpa approval; hak akses terbuka di mockup (permission saat implementasi); template email ditunda (OQ-C2); jurnal ditampilkan di layar (tautan Lihat Jurnal), bukan tabel/laporan; uji alur penuh mockup disetujui. Ruang lingkup: fokus desain/mockup, kode aplikasi oleh programmer kemudian.

## Bukti dokumen pendukung Hold & Claim (2026-10-29)

> Oh iya, saat proses claim dari produksi, Ketika klik "Hold & Claim" Wajib upload bukti dokumen pendukung. Agar bukti dari produksi bisa tersimpan sampai proses claim selesai.

Extracted: popup Hold & Claim mewajibkan minimal satu berkas bukti per baris; tersimpan bersama claim sampai selesai (FR-94, tabel baru `claim_material_bukti`). Asumsi agen (belum dikonfirmasi): bukti per baris claim, boleh beberapa berkas, tidak dapat dihapus setelah terkirim; format/ukuran = OQ-C11 terbuka.

## Email dengan bukti dan Process Decision beberapa claim (2026-10-29)

> bukti sebaiknya ikut terlihat di surat email. / Belum ada [format & ukuran], biarkan dulu seperti sekarang. / ... nomor 5 dan 6 ... dari satu laporan SPK yang sama, tapi ini jadinya malah dipecah antara KW 2 dengan Hold. Agar bisa dikombinasikan, sebaiknya dari header sudah bisa ceklis dari beberapa nomor lalu Process Decisionnya.

Extracted: bukti pendukung ikut dilampirkan di email (FR-77); OQ-C11 tetap terbuka. Process Decision dapat dilakukan untuk beberapa claim terpilih sekaligus (centang per baris + centang semua di header; FR-78). Asumsi agen (belum dikonfirmasi): claim terpilih harus satu supplier; satu kesepakatan bersama; keputusan, Debit Note, dan SPK tetap per claim (tidak digabung menjadi satu dokumen).

## Unduh dan pratinjau bukti (2026-10-29)

> Sudah bisa upload dokumen, tetapi di menu daftar claim tidak bisa download buktinya. Cuma ada bukti tersimpan saja. Tolong dibuat bisa didownload, bahkan bisa muncul langsung gambaran atau file yang diupload agar tidak repot download juga.

Extracted: bukti di Daftar Claim dapat diunduh dan dipratinjau langsung (gambar, PDF); lampiran email dapat diunduh (FR-94, FR-77). Mockup menyimpan isi berkas di IndexedDB browser (bukan server); data contoh menampilkan berkas contoh yang dibuat otomatis.

## Sumber nilai Retur (2026-10-29)

> 1. Setuju. Sebaiknya retur mengambil dari Costbook. Bukan dari harga beli/kg. 2. Sesuai costbook di gudangnya saja. Jangan pedulikan sudah termasuk PPn atau tidak.

Extracted: Nilai Retur = Berat x Costbook/Kg gudang asal; tidak dibagi 1,11 (FR-84, FR-92 diperbarui). Costbook = Costbook Material per material x gudang asal (NFR-07, FR-19..23), bukan harga beli. Kolom claim_material.harga_beli_per_kg menyimpan nilai Costbook/Kg; rename costbook_per_kg diusulkan agen (belum dikonfirmasi). Sumber di database: tr_cost_book.costbook (kosong di hfg_dev).

## Sumber harga kembali ke harga_beli + aturan penamaan (2026-10-29)

> Tetap ikuti field harga_beli dari warehouse_stock_coil. Dan aturannya juga ditambah nama di ERD, tabel, kode dan juga tampilan dengan di header seperti kolom header itu juga sama ya.

Extracted: sumber Harga Beli/Kg = warehouse_stock_coil.harga_beli (mengganti jawaban costbook di atas); header "Harga Beli/Kg" kembali (sama dengan kolom harga_beli_per_kg). Aturan baru: nama di ERD, tabel database, kode, dan header kolom tampilan harus sama. Belum dikonfirmasi: pembagi 1,11 tetap dihilangkan setelah sumber kembali ke harga beli.

## Retur = costbook, harga_beli diisi, audit header (2026-10-29)

> 1. Nilai Retur = Berat x Costbook material dari gudang tersebut. 2. Database asli sudah terisi. Begini saja, kamu boleh tambahkan harga_beli di hfg_dev untuk menyesuaikan dengan material yang sedang kita coba dulu. 3. Audit saja dan langsung eksekusi sesuai aturan itu.

Extracted: Nilai Retur = Berat x Costbook/Kg gudang asal (kolom baru claim_material.costbook_per_kg); harga_beli_per_kg (dari warehouse_stock_coil.harga_beli) tetap untuk Nilai Material. Izin: isi harga_beli di hfg_dev (dilakukan 268 coil ber-PO, dari harga per Kg ROS). Audit header tampilan terhadap nama kolom ERD dan perbaikannya dieksekusi. Asumsi agen: Nilai Material (Jual Material, biaya) tetap memakai harga_beli; pembagi 1,11 tetap dihilangkan.

## Print dokumen dan selisih Costbook vs Price/Unit PO (2026-10-29)

> 1. Saat Print SPK Repack boleh kamu kreasikan contoh Print Outnya ya. Yang penting sudah ada logo HFG-nya. 2. Untuk Print Surat Jalan juga kamu bisa kreasikan sendiri ya. 3. Print Debit Note juga kamu kreasikan. 4. Ketika Create Debit Note, ada beberapa isu seperti: Supplier ingin ditagihnya berdasarkan nilai Price/Unit saat PO. Tapi, ketika claim kita mengikuti costbook. Sehingga, terjadi perbedaan atau selisih. Kalau kita fix pakai costbook, yang ada supplier tidak terima. Tetapi, apabila pakai Price/Unit akan terjadi perbedaan di Neraca akhir Laporan keuangan gudang dengan aktual di ERP. Jadi, solusinya: ketika Material diclaim menggunakan Price/Unit saat PO, jurnalnya adalah (Db) Debit Note, (Cr) Nilai Material Retur, (Db/Cr) selisih costbook vs Price/unit PO. "(Db/Cr)" ini artinya tergantung dari situasi angkanya negatif atau positif.

Extracted: (1-3) tiga dokumen cetak berlogo HFG (SPK Re-Pack, Surat Jalan Retur, Debit Note) dikreasikan agen (FR-83/84/85). (4) Nilai Debit Note Retur = Berat x Price/Unit PO; nilai material keluar tetap Berat x Costbook/Kg; jurnal Db Debit Note / Cr Nilai Material Retur / Db atau Cr Selisih Costbook vs Price/Unit PO. Kolom baru claim_material.price_per_unit_po (sumber dt_trans_po.hargasatuan). Asumsi agen (belum dikonfirmasi): jurnal ini terjadi saat Confirm Surat Jalan (menggantikan Db Debit Note / Cr In Transit); "Nilai Material Retur" = akun In Transit; selisih kredit bila Price/Unit PO > Costbook. OQ-C12: kurs untuk PO non-IDR dan akun COA selisih.

## Jawaban OQ-C12 dan konfirmasi asumsi jurnal selisih (2026-10-29)

> 1. Iya betul. 2. Betul. 3. OQ-C12: - Kurs pada saat claim. Jadi, pada saat create Debit Note sudah tinggal tarik kurs yang sudah diinput. - Iya, betul. Sementara pakai kalimat itu dulu saja untuk menampilkan di mockup. - Betul, boleh juga nanti dibuat dengan kasus USD. Query ALTER sudah dijalankan.

Extracted: jurnal selisih terjadi saat Confirm Surat Jalan, "Nilai Material Retur" = In Transit, selisih Kredit bila Price/Unit PO > Costbook (dikonfirmasi). OQ-C12 RESOLVED: kurs = kurs saat claim, diinput di Process Decision, Create Debit Note menarik kurs; label akun "Selisih Costbook vs Price/Unit PO" sementara; kasus USD contoh dibuat kemudian. Query 006 (price_per_unit_po) sudah dijalankan user di hfg_dev dan diverifikasi agen. Asumsi agen: kurs diinput per claim di modal Process Decision (bukan di popup Hold & Claim, karena Produksi tidak menetapkan kurs).

## Jawaban OQ-C11 (2026-10-29)

> 1. Format bebas, bahkan bisa upload video juga. 2. Maksimal di 20mb saja dulu.

Extracted: OQ-C11 RESOLVED: format bukti bebas termasuk video; batas 20 MB (agen mengasumsikan per berkas; "dulu" = bisa berubah). Mockup: popup menolak berkas > 20 MB dengan pesan; video dipratinjau dengan pemutar di Daftar Claim.

## Separator angka dan status SPK saat Hold & Claim (2026-10-30)

> 1. Nilai Debit Note pada saat input Process Decision tolong format angka ditambah separator untuk menunjukkan ribuan dan seterusnya. Cek juga nilai seperti harga di menu lain untuk diberi separator. 2. Ketika SPK sedang Hold&Claim status di SPK nya juga berubah menampilkan status SPK sedang dihold.

Extracted: (1) input angka berformat ribuan (titik) dan desimal (koma) pada semua halaman Claim serta Costbook dan Rate (harga); berat/kuantitas di Laporan Produksi, Material, dan Target Qty Beranda tidak diubah (bukan harga). (2) SPK produksi menampilkan "SPK Sedang Di-Hold" di Daftar SPK (Beranda) selama claim dari Hold & Claim belum diputuskan. Asumsi agen: status kembali normal setelah semua claim SPK itu diputuskan (bukan menunggu coil dirilis).

## Konfirmasi status SPK + catatan material di-hold (2026-10-30)

> Betul, tetapi perlu dikasih catatan atau note bahwa ada material yang dihold.

Extracted: asumsi "status SPK kembali normal setelah claim diputuskan" dibenarkan. Ditambah catatan "ada material yang di-hold" pada Daftar SPK (Beranda) dan Laporan Produksi selama claim belum diputuskan, berisi material, no. coil, berat, asal, dan no. claim.
