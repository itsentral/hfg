---
id: FEAT-12
type: feature
epic: subkont
status: planning
url:
source: input/Production Planning/Prod.Plan To Be D02-PTHFG.png; input/Production Planning/Subkont To Be D002-PTHFG.png; input/Production Planning/3. Rev.2 Konsep & Simulasi Laporan Slitting Subkont.xlsx; input/Production Planning/notes.md (jawaban user 2026-11-20)
uc: [UC-17, UC-18, UC-19, UC-20, UC-21, UC-22, UC-23, UC-24, UC-25, UC-26, UC-27, UC-N37, UC-N38, UC-N39]
depends_on: []
blocks: []
---

Proses Subkont dimulai dari Production Planning: SPK Subkont (Material Slit / FG Tambah Pasir), Tampungan Siap Kirim, Surat Jalan ke
Vendor, Portal Vendor (LHP), Incoming, jurnal, dan update costbook. Tahap ini: **dokumen dulu** (FR/NFR, ERD, Use Case), mockup menyusul.

## Context

BPMN To Be Prod.Plan (D02) dan Subkont (D002), workbook Rev.2 (5 sheet) dan 15 jawaban user tercatat di
`input/Production Planning/notes.md`. Modul aplikasi terkait yang sudah ada: `production_planning` (tr_production_plan, tr_spk_plan_*),
`delivery_fg` (tr_delivery_order), `fg_warehouse` / `fg_stock`, `warehouse_stock_coil`, `purchase_order`, `incoming_*`. Portal vendor belum ada.

## Current State

2026-11-20: `docs/fr-nfr.md` bagian "Proses Subkont" (FR-99–FR-125, NFR-18–NFR-21, OQ-S1–OQ-S12 (S1–S10 dijawab user 2026-11-20)); `erd-revisi.drawio` halaman 3 "Subkont ERD"
(14 entitas baru + 4 ringkasan); `workbook.xlsx` UC-17–UC-27 (UC BPMN), UC-N37–N39, PG-37–PG-51, modul Subkont dan Portal Vendor, entitas baru.
Mockup Subkont tahap 1-5 (lengkap sampai Incoming, Laporan Nilai Slitting, Saldo Material In Supplier; lihat `.artifacts/mockup_subkont/walkthrough_mockup_subkont_20-11-2026.md`) selesai 2026-11-20: `ui-mockup/sk-spk.html`, `sk-tampungan.html`, `sk-surat-jalan.html`, `sk-portal.html`, `sk-master-supplier.html`, `sk-akun-vendor.html`, `sk-laporan-vendor.html`. Mockup `fg_stock` (prasyarat yang diminta user) selesai: `ui-mockup/fg-stock.html`, `fg-kartu-stok.html`.

## Scope

Termasuk: spesifikasi dokumen di atas. Di luar cakupan sekarang: mockup Subkont, kode CI3, SQL migrasi, langkah Prod.Plan selain SPK Subkont
(hitung kebutuhan, tentukan tujuan, SPM, SPK Produksi; hanya dicatat sebagai flow), BPMN drawio baru (gambar klien dipakai apa adanya).
Urutan berikutnya: user menjawab OQ-S1–OQ-S10 dan menyetujui dokumen → mockup Subkont (SPK, Tampungan, Surat Jalan, Portal Vendor, Persetujuan Akun Vendor, Master Supplier meniru modul supplier, Incoming) →
SQL migrasi (hanya ditulis, tidak dieksekusi) → plan/task CI3.

## Verification

- [x] Hitungan contoh workbook cocok dengan FR-120/FR-122/FR-123: 3.778 x 270,27 = 1.021.080,06; 38.801.080,06; jurnal Db = Kr = 39.001.080,06; costbook baru 10.270,27/kg.
- [x] ERD: XML valid, tanpa komentar XML, dirender dengan draw.io CLI dan dilihat (halaman "Subkont ERD").
- [x] Workbook dibuka ulang dengan openpyxl: baris baru terbaca (Modules 14, UC BPMN 28, UC Non-BPMN 39, Entities 76, Pages 50).
- [x] OQ-S1–OQ-S10 dijawab user (2026-11-20); OQ-S9 = kode akun tetap sementara sampai BA menetapkan.
- [x] OQ-S11 dan OQ-S12 dijawab user 2026-11-20 (Setuju). Keputusan akun vendor di OQ-S12 diganti 2026-11-20: tanpa akun/persetujuan; HFG memberi link portal unik dari Master Supplier.
