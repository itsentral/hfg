# History Costing, History Price List, dan tampilan nama produk (permintaan user)

- **Source:** Rama Ramon (pemilik proyek, mewakili klien)
- **Date:** 2026-10-21
- **Channel:** permintaan tertulis di sesi kerja (tanpa file)
- **Contents:** tidak ada file; hanya permintaan di bawah. Terkait intake
  `input/2026-10-21-bottom-price-change-notification/` (notifikasi Bottom Price) dan
  `input/2026-10-21-sales-extra-margin-approval/` (persetujuan Extra Margin).

## Permintaan (kata per kata)

> "Karena setiap kali ada perubahan rate yang mempengaruhi bottom price, maka perubahan tersebut harus disimpan di History
> Costing, termasuk juga apabila ada perubahan pricelist akan disimpan di History Price list.
> Oh iya, tambahan juga untuk menu Price list dan product catalog tolong munculkan juga nama produknya. Jangan nomor
> pricelist yang dimunculkan didepan."

## Tafsiran (dibuat tim pengembang, belum dikonfirmasi user)

- **History Costing:** setiap perubahan master/rate/input yang membuat Bottom Price suatu Produk × varian BOM berubah disimpan
  sebagai catatan riwayat (waktu, pengguna, sumber perubahan dengan nilai lama dan baru, Bottom Price lama dan baru,
  snapshot komponen). Hanya baca. Terpisah dari notifikasi Bottom Price (FR-68..FR-70): notifikasi untuk Sales meninjau,
  riwayat untuk audit.
- **History Price List:** setiap kejadian pada Pricelist (diajukan, disetujui, ditolak, dihitung ulang, dirilis ke Product Catalog)
  disimpan per Produk × varian BOM dengan Price List lama dan baru. Hanya baca.
- **Tampilan:** pada daftar Pricelist dan Product Catalog, nama produk (dengan varian/Uniq ID) tampil di depan; nomor
  pricelist/katalog menjadi kolom sekunder.
- Semua butir yang diasumsikan tercatat sebagai open question 34–42 di `docs/fr-nfr.md`.

## Extracted

- FR-71 s.d. FR-73, NFR-17, open question 34–42 -> `docs/fr-nfr.md`
- Istilah "History Costing", "History Price List" -> `context/index/vocabulary.md`
- UC-N19, UC-N20, PG-25, PG-26 -> `docs/workbook.xlsx`; entitas `history_costing`, `history_pricelist` -> `docs/diagrams/erd.drawio`
- FEAT-10 -> `pm/tracker.yaml`
