---
id: FEAT-03
type: feature
epic: product-costing-rev1
status: planned
url:
source: UC-N05, UC-N06, UC-N15 (workbook PG-06, PG-07, PG-08)
uc: [UC-N05, UC-N06, UC-N15]
depends_on: [FEAT-02]
blocks: [FEAT-04]
---

Master Produk (tambah Lebar), Master Material (tanpa Berat/Meter, validasi, log), dan Master Standard Material
(baru, global) di aplikasi CI3 dengan memperluas modul `product_master` dan `material_master`.

## Context

Hasil Tahap 0 (FEAT-02, arsip) dan riset kode 20-10-2026. Rencana lengkap, temuan, keputusan D0–D7, langkah, dan
verifikasi: `.artifacts/master_data_product_costing/plan_master_data_product_costing_20-10-2026.md` (root repo;
folder ini diabaikan git).

## Scope

Lihat plan di atas, bagian 2 dan 3. Di luar scope: BOM (FEAT-04), rate, perbaikan kualitas kode lama.

## Verification

- [x] User menjawab D1–D7 (20-10-2026); D0 (hasil SHOW CREATE TABLE) menunggu dari user
- [ ] Migrasi (ALTER/CREATE/permission) diuji dan bisa di-rollback di database uji
- [ ] Regresi BOM/Laporan Produksi/SPK/Price Reference lolos

> OPEN DECISION: D0 saja. Jangan mengubah tabel yang sudah ada (product_lvl_4, new_inventory_4) sebelum hasil SHOW CREATE TABLE diterima; Master Standard Material (tabel baru) boleh jalan lebih dulu. Plan: `plan_master_data_product_costing_20-10-2026_v2.md`.
