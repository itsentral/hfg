# Jawaban open question Product Costing

- **Source:** Rama Ramon (pemilik proyek, mewakili klien)
- **Date:** 2026-10-07
- **Channel:** percakapan sesi kerja (jawaban atas open question 1-11 di `docs/fr-nfr.md`)
- **Contents:** tidak ada file; hanya jawaban lisan/tertulis di bawah. Dua file terkait diterima
  hari yang sama dan masih berada di `input/` (belum di dalam folder intake):
  `ERD Product Costing-Production Report.jpg` dan `Rev.1 Ide Costing Kg konversi ke Meter.xlsx`.

## Jawaban (ringkas, mengikuti isi pesan)

1. Kunci Flow Process dan Rate Bahan Pendukung/FOH: **per kategori produk** (Level 2).
2. Material Cost memakai Berat/Meter material utuh tanpa faktor lebar: **benar**.
3. Master Produk: **Tebal dan Panjang tetap ada**. Berat per Pcs = Berat/Meter × Panjang produk;
   Berat/Meter berasal dari **varian BOM** produk (contoh: Produk 1 + material A = 1 kg/m,
   Produk 1 + material B = 1,3 kg/m, panjang 6 m → 6 × berat/meter tiap varian). Produksi
   mengambil produk dengan varian BOM yang dipilih.
4. Biaya per pcs di Laporan Produksi mengikuti mock up Laporan Produksi sebelumnya; fokus saat ini
   hanya Product Costing.
5. Rate Scrap untuk HPP Produk KW 1 dan KW 2 diambil dari Master Rate Generals "Scrap". Pembagian
   40/60 tetap ada (user: sudah sesuai pada `ui-mockup/index.html`).
6. Kolom jam (`start_time`/`finished_time`) tidak ikut dihitung; Man Power memakai standar rate
   Product Costing.
7. BPJS **input manual**. Konstanta 173 jam dijadikan parameter; tampilkan keterangan konstanta
   yang dipakai.
8. Width **914 pada baris contoh Master Material adalah salah ketik; seharusnya 1219.**
9. "Pricelist HFG saat ini 27.400" = **Rp/Meter**, hanya contoh untuk membandingkan hasil
   pricelist klien dengan konsep mock up.
10. Aktor: Engineering (Master Produk, Master Material, BOM, Flow Process & Cycletime), Accounting
    (Kurs, Man Power Rate, Rate Bahan Pendukung/FOH, General Rate), Purchasing (Price Reference),
    Accounting+Sales+Engineering (Simulasi Costing & Pricelist).
11. Setuju: tiap baris General Rate punya satuan/jenis sendiri; Biaya Koordinasi tetap Rp/Kg,
    sisanya persentase.

## Supersedes

- `Rev.1 Ide Costing Kg konversi ke Meter.xlsx`, sheet **Simulasi Program**, sel F11 (Width
  `914`) pada baris `GALVALUM MOTHER BORON 0.14-1219-AZ70-G550-`: nilai yang benar **1219**. File
  sumber tidak diedit (append-only).
- Sheet **Ide** E9–E11 ("Tebal tidak perlu di master produk"): diganti jawaban 3 di atas — Tebal
  dan Panjang tetap ada di Master Produk.

## Extracted

- Open question 1-11 -> `docs/fr-nfr.md` (FR-37, FR-38, FR-43, FR-45, FR-46, FR-47, FR-48)
- UC-N05 s.d. UC-N14 -> `docs/workbook.xlsx`
