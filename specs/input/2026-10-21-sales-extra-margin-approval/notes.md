# BPMN To Be Sales D01 — persetujuan perubahan rate Extra Margin

- **Source:** Rama Ramon (pemilik proyek, mewakili klien)
- **Date:** 2026-10-21
- **Channel:** file dikirim ke `input/` (belum di dalam folder intake) + penjelasan tertulis di sesi kerja
- **Contents:**
  - `input/BPM To Be Sales D01-PTHFG.png` — BPMN To Be proses Sales (lane Staf Sales, Sales Manager, GM) dengan
    sub-proses Costing, Delivery, dan Production Plan di lane Other Process.

## Isi flow (dibaca dari gambar)

1. Customer mengajukan permintaan/PO → Staf Sales **Konsultasi kebutuhan customer** → **Cek Existing Product**.
2. Gateway **Sudah ada di Product Catalog?** — *Belum ada*: **Ajukan Produk baru** (ke proses Costing, "Pengajuan Harga
   Produk Baru"); *Sudah ada*: lanjut ke lane Sales Manager.
3. **Sales Manager** — gateway **Perlu Perubahan Margin?**: *Tidak ada Update* → **Buat Quotation**; *Perlu* →
   **Update dan Ajukan Rate Extra Margin baru**.
4. **GM** — gateway **Approval Extra Margin**: *Approved* → **Buat Quotation** (rate dipakai); *Reject* → kembali ke
   **Update dan Ajukan Rate Extra Margin baru** (Sales Manager memperbaiki, mengajukan ulang).
5. Catatan pada flow, "Poin penilaian Cust. Baru": 1) toko sewa/sendiri, 2) lama berdiri, 3) armada, 4) asset,
   5) jumlah cabang; sumber data Master Customer, Stok Finish Good, dan Stok Material untuk dijual dipakai saat Buat Quotation.
6. Setelah Quotation: gateway **Negosiasi?** — **Diskon <5%**: Approval langsung oleh Sales; **5%–10%** (tergantung aturan
   company): Approval Quotation oleh Sales Manager; **>10%**: Approval Quotation oleh GM → **Buat Sales Order (SO)** → **Buat SPM**
   (Surat Perintah Muat untuk Ready Stok) ke Delivery dan **Kirim SO ke Production Plan**.

## Keputusan user (2026-10-21)

- "Jika Sales mengubah rate extra margin, maka perlu approval ke management atau General Manager": penyetuju = **GM**,
  pengaju = **Sales Manager** (sesuai lane BPMN).
- Referensi spesifikasi, workbook, dan ERD disesuaikan; mockup dibuat dengan template yang ada.

## Batas scope

- Persetujuan **diskon pada Quotation** (<5% / 5–10% / >10%), pembuatan SO, SPM, dan pengiriman ke Production Plan adalah
  modul Sales/Quotation dan **di luar scope Product Costing**; hanya dicatat di sini.

## Supersedes

- `input/2026-10-19-product-costing-rev1/` keputusan OQ 16 / FR-55 / UC-10: "Sales menetapkan rate Negotiation Allowance
  per Product Category" digantikan oleh pengajuan Sales Manager yang berlaku setelah Approved GM (FR-67).

## Extracted

- FR-67, revisi FR-55, peta perubahan, open question 20–24 -> `docs/fr-nfr.md`
- ERD `rate_negotiation_allowance_request` -> `docs/diagrams/erd.drawio` halaman "Product Costing ERD"
- UC-12 (diubah), UC-16 (baru), PG-16 (diubah), PG-23 (baru), entitas baru -> `docs/workbook.xlsx`
- Istilah "Extra Margin" -> `context/index/vocabulary.md`
- Mockup -> `ui-mockup/pc-approval-extra-margin.html` (dikerjakan terpisah)
