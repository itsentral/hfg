---
id: FEAT-04
type: feature
epic: product-costing-rev1
status: in_progress
url:
source: UC-N07, UC-N08, UC-N09, UC-N10 (workbook PG-09..PG-12)
uc: [UC-N07, UC-N08, UC-N09, UC-N10]
depends_on: [FEAT-03]
blocks: [FEAT-06]
---

BOM per Varian Tebal, Flow Process & Cycletime, Kurs, dan Price Reference, dipecah menjadi sub-tahap 04a–04d.

## Context

Riset kode 20-10-2026 dan plan lengkap (temuan, sub-tahap, keputusan K1–K11, risiko) ada di
`.artifacts/bom_flow_kurs_price_reference/plan_bom_flow_kurs_price_reference_20-10-2026.md`
(root repo; folder ini diabaikan git).

## Current State

- **04b Flow Process:** kode ditulis (modul `flow_process`, `php -l` bersih), belum diuji di database; menunggu
  migrasi di database uji (walkthrough di `.artifacts/bom_flow_kurs_price_reference/`).
- **04a Kurs, 04c Price Reference, 04d BOM varian:** belum dikerjakan; menunggu D0 diperluas dan keputusan K2, K4.

## Scope

04a–04d seperti di plan. Di luar scope: mengubah `ms_bom_*` (dipakai SPK), master mesin, kolom `price_ref*` lama.

## Verification

- [x] 04b: `php -l` bersih dan pembacaan kode
- [ ] 04b: migrasi dan uji manual di database UJI
- [ ] 04a, 04c, 04d

> OPEN DECISION: D0 diperluas (`SHOW CREATE TABLE` bom_header, bom_detail, master_kurs, ms_bom_*, tr_purchase_order,
> dt_trans_po, jumlah BOM aktif), K2 (nama tabel BOM baru), K4 (sumber History Pembelian). Jangan menulis
> migrasi 04a/04c/04d sebelum terjawab.
