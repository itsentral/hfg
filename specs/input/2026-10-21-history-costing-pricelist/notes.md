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

## Jawaban user 2026-10-21 (pertanyaan terbuka 34–42)

**History Costing**
- Pemicu: "Apabila ada update costing terbaru".
- Masa simpan riwayat 1 tahun; bila sudah 1 tahun, dimasukkan ke backup data.
- User yang diberikan akses nanti untuk melihat.
- Pricelist draft tidak ikut tercatat.
- Persetujuan Extra Margin tidak masuk riwayat History Costing.
- Bisa diekspor ke Excel.
- Tidak terpisah dari Activity Log aplikasi.

**History Price List**
- Pemicu: "Apabila ada update Pricelist terbaru yang sudah disetujui".
- Masa simpan riwayat 1 tahun; bila sudah 1 tahun, dimasukkan ke backup data.
- User yang diberikan akses saja yang bisa melihat.
- Pricelist draft tidak ikut tercatat.
- Persetujuan Extra Margin tidak masuk riwayat History Pricelist.
- Bisa ekspor ke Excel.
- Tidak terpisah dari Activity Log aplikasi.

Tidak dijawab: granularitas History Costing (OQ 41) dan format nama produk (OQ 42).

Supersedes: asumsi sebelumnya bahwa perubahan Extra Margin yang disetujui GM dicatat di History Costing (OQ 38),
riwayat dicatat untuk semua kejadian Pricelist (Diajukan/Ditolak/Dihitung Ulang/Dirilis), masa simpan tanpa batas,
pembaca tetap (Costing/Accounting/Sales/Management), dan catatan bisnis terpisah dari Activity Log.
