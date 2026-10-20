# Product Costing Rev.1 — flow To Be, Use Case, ERD, dan jawaban klarifikasi

- **Source:** Rama Ramon (pemilik proyek, mewakili klien); Use Case disusun dari diskusi dan simulasi dengan klien
- **Date:** 2026-10-19
- **Channel:** tiga file dikirim ke `input/` (belum di dalam folder intake) + jawaban tertulis di sesi kerja
- **Contents:**
  - `input/Costing To Be D05-PTHFG.png` — BPMN To Be Product Costing (lane Engineering, Finance, Costing (Rate), Accounting, Sales, Management)
  - `input/Rev.1 Use Case Product Costing.xlsx` — sheet *Diagram*, *Spesifikasi Use Case* (UC-01 s.d. UC-19) dan *Istilah* (20 istilah)
  - `input/ERD Product Costing-Production Report.jpg` — ERD konsep Product Costing + Production Report
  - Tidak ada file di folder ini; hanya jawaban di bawah.

Ketiga file di atas ditetapkan user sebagai **referensi utama** program Product Costing.
Jika berbeda dengan `input/2026-10-07-product-costing-jawaban/` atau FR-37–FR-49 versi
2026-10-07, ketiganya yang dipakai; perbedaan dicatat di `docs/fr-nfr.md` (bagian "Revisi Rev.1").

## Jawaban klarifikasi (atas 7 pertanyaan sesi 2026-10-19)

1. **Bottom Price (UC-15):** rumus yang benar = HPP **ditambah** seluruh Other Cost
   (`Bottom Price = HPP + Σ (Rate Other Cost × HPP)`), bukan perkalian. Rumus tertulis
   `HPP × Other Cost` di sheet adalah penulisan yang kurang tepat.
2. **Daftar Other Cost:** pakai daftar **terbaru**, yaitu enam elemen pada UC-09 (Sales & Marketing,
   Transport, Finance & Admin, Management Fee, Interest, Profit). Daftar empat elemen pada sheet
   *Istilah* dan BPMN (Sales, General Admin, Interest, Profit) dianggap usang.
3. **Negotiation Allowance (UC-16):** berupa **persentase**.
4. **UC-13 langkah 4** berjudul "Biaya Proses": salah ketik, maksudnya Biaya Manufacturing Support.
5. **ERD** adalah referensi awal; entitas yang kurang (Master Standard Material, Pricelist, dua
   satuan Rp/Kg & Rp/Meter) dilengkapi oleh tim pengembang.
6. **`Varian type` di BOM Header** = Varian Tebal.
7. **Price Reference:** hanya nilai **Max** dari History Pembelian yang dipakai.

## Supersedes

- `input/2026-10-07-product-costing-jawaban/` jawaban 10 (aktor): aktor berubah mengikuti lane BPMN
  Rev.1 (Finance, Costing, Sales, Management) — lihat `docs/fr-nfr.md` "Revisi Rev.1".
- `docs/fr-nfr.md` FR-40 (pilihan harga terakhir/rata-rata) digantikan: hanya Max.

## Extracted

- FR-50 s.d. FR-58, open question 12–19 -> `docs/fr-nfr.md`
- Istilah (20 istilah) -> `context/index/vocabulary.md`
- Issue capsule -> `pm/tracker.yaml`, `pm/issues/`
- ERD -> `docs/diagrams/erd.drawio` halaman "Product Costing ERD"
- UC-01 s.d. UC-19 (klien) -> `docs/workbook.xlsx` (UC-N05–N15, UC-12–15; disinkronkan 2026-10-19)
- Mockup -> `ui-mockup/pc-*.html` (15 halaman)

## Jawaban klarifikasi ronde 2 (open question 12–17, 2026-10-19)

12. **Man Power Cost:** `Total Man Hour × Rate Man Power`; "Rate Man Power × Berat/Meter" di UC-12 salah salin.
13. **Man Power Rate:** rincian gaji/BPJS/173 jam **tetap dipakai, hanya untuk Operator**; **Helper**
    mengambil dari **master Borongan per Product Category**.
14. **Rate FOH dan Bahan Pendukung:** diganti menjadi **Rp/Kg**; Rp/Meter = Rate Rp/Kg × Berat/Meter.
15. **Master Standard Material:** **berlaku global**; satuannya **disembunyikan** karena klien tidak menginformasikan.
16. **Negotiation Allowance:** **per Product Category**.
17. **Pricelist:** **per Produk per Varian BOM**; **perlu alasan Reject**; **Product Catalog disimpan sebagai data**.

## Jawaban klarifikasi ronde 3 (open question 18, 2026-10-19)

18. **"1/3 Operator":** Rate Operator ÷ 173 jam, lalu **dikalikan 1/3** dari rate tersebut, karena Operator
    dianggap hanya 1/3 bekerja langsung terhadap produk. **Rate Borongan** (Helper) bersatuan **Rp/Meter
    atau Rp/Pcs**; ini memengaruhi Man Power Cost bagian Rp/Meter. Jika Rp/Pcs, dikonversi dulu saat
    masuk Product Costing: **Rp/Pcs ÷ Panjang Produk = Rp/Meter**.

## Jawaban klarifikasi ronde 4 (open question 19, 2026-10-19)

19. **Man Power Cost:** rumus usulan disetujui — `(Total Man Hour × Rate 1/3 Operator + Rate Borongan Helper
    Rp/Meter) × Faktor Indirect Labor`. **Pembulatan rate dilakukan sesudah dikali 1/3.**

## Koreksi 2026-10-20 (Rate Borongan Helper)

- **Rate Borongan Helper hanya Rp/Meter**, tidak perlu konversi; agar standar tetap berawal dari Rp/Meter.
  Menggantikan jawaban ronde 3 (Rp/Meter atau Rp/Pcs, dikonversi Rp/Pcs ÷ Panjang Produk).

## Keputusan 2026-10-20 (Faktor Indirect Labor)

- **Faktor Indirect Labor dijadikan master tersendiri**, karena nilainya bisa berubah (bukan lagi parameter
  di halaman Man Power Rate). Cakupan: **global (satu nilai), tidak per Product Category, hanya faktor pengali pada Man Power Cost** (dikonfirmasi user 2026-10-20).
