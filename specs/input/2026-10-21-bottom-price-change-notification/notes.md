# Dashboard notifikasi perubahan Bottom Price (permintaan user)

- **Source:** Rama Ramon (pemilik proyek, mewakili klien)
- **Date:** 2026-10-21
- **Channel:** permintaan tertulis di sesi kerja (tanpa file)
- **Contents:** tidak ada file; hanya permintaan di bawah. Terkait `input/BPM To Be Sales D01-PTHFG.png`
  (intake `input/2026-10-21-sales-extra-margin-approval/`), gateway Sales Manager "Perlu Perubahan Margin?".

## Permintaan (kata per kata)

> "Oke, sekarang saya butuh dashboard notifikasi apabila ada perubahan bottom price dari Product Costing. Fungsinya adalah
> memberikan notifikasi atau alert ke Sales bahwa ada perubahan bottom price. Sehingga, sales harus memikirkan apakah harga
> jual dengan costing baru mempengaruhi harga pasar saat ini dengan menggunakan Rate Extra Margin sebelumnya. Itu juga membuat
> sales harus memutuskan apakah mau mengajukan rate extra margin baru atau tidak perlu diubah."

## Tafsiran (dibuat tim pengembang, belum dikonfirmasi user)

- Pemicu: Bottom Price per Produk × varian BOM berubah dibanding baseline, apa pun penyebabnya.
- Isi: Bottom Price lama → baru, harga jual saat ini vs Price List baru bila Rate Extra Margin lama dipertahankan.
- Keputusan Sales (Sales Manager menurut BPMN): **Ajukan Rate Extra Margin Baru** (masuk persetujuan GM, FR-67) atau
  **Tidak Perlu Diubah**.
- Semua butir yang diasumsikan tercatat sebagai open question 25–32 di `docs/fr-nfr.md`.

## Extracted

- FR-68 s.d. FR-70, NFR-16, open question 25–32 -> `docs/fr-nfr.md`
- Istilah "Notifikasi Perubahan Bottom Price", "Harga jual saat ini" -> `context/index/vocabulary.md`
- UC-N18 dan PG-24 -> `docs/workbook.xlsx`; entitas `bottom_price_notification` -> `docs/diagrams/erd.drawio`
- FEAT-09 -> `pm/tracker.yaml`

## Jawaban user 2026-10-21 (OQ 48)

- **OQ 48:** "Iya, betul. Ketika tekan Update Costing." → notifikasi Bottom Price juga dipicu saat tombol **Update Costing** ditekan
  (satu pemicu dengan History Costing): hasil costing terbaru dibandingkan dengan baseline (Bottom Price saat terakhir ditinjau Sales);
  bila berbeda ≥ Rp0,01 dibuat notifikasi Belum Ditinjau per varian BOM. Menggantikan asumsi "setiap kali costing dihitung ulang" (OQ 29).
