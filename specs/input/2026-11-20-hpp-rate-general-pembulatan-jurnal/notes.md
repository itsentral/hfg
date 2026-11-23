# 2026-11-20 — HPP: Consumable, Koordinasi, Scrap dari General Rate; field jurnal JV016 tanpa desimal

Sumber: pesan user (chat), 2026-11-20. Kutipan inti, apa adanya:

> di modul production_report
>
> Untuk Report HPP "Komponen Biaya/Pcs(Rp)" bagian biaya Consumable, Koordinasi dan Scrap menggunakan rumus :
> 1. Consumable = Modul rate_generals bagian Consumables * Berat/Pcs
> 2. Koordinasi = Modul rate_generals bagian Biaya Koordinas * Berat/Pcs
> 3. Scrap = Modul rate_generals bagian Scrap %Scrap * Biaya Material/Pcs
>
> dan untuk penyimpanan di production_reports untuk field field yang digunakan untuk jurnal JV016 tolong valuenya di round, jadi tanpa angka dibelakang koma

Konfirmasi persetujuan (pesan user berikutnya, 2026-11-20):

> agar rumus konek dengan yang ada di specs, tolong ubah juga yang di specs tadi saya sudah mendapatkan persetujuan

Pemetaan:
- Consumable/Pcs = `rate_generals.rate_consumables` (Rp/Kg) × `berat_per_pcs`.
- Koordinasi/Pcs = `rate_generals.rate_biaya_koordinasi` (Rp/Kg) × `berat_per_pcs`.
- Scrap/Pcs = `rate_generals.rate_scrap` (%Scrap; disimpan sebagai pecahan, 0,02 = 2%) × Biaya Material/Pcs.
- Man Power, FOH, Bahan Pendukung Khusus tidak disebut: tetap Rp/Kg Hasil Costing varian BOM (FR-48 2026-10-26).
- "Field yang digunakan untuk jurnal JV016" = kolom `production_reports` yang dirujuk `master_oto_jurnal_detail.field`
  untuk kode jurnal JV016 (lewat `ms_jurnal_mapping` menu `production_report`, action `save_production_report`).

Extracted: FR-19, FR-24, FR-48, FR-49, NFR-09 -> `docs/fr-nfr.md`; hint & rumus `ui-mockup/hpp.html` / `ui-mockup/hpp.js`.
