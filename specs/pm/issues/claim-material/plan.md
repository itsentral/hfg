---
id: FEAT-11
type: feature
epic: claim-material
status: in_progress
url:
source: input/Claim Process/To Be_Claim Coil_D003-PTHFG.png; input/Claim Process/4. Rev.2 Mockup Claim Material (based on Concept).xlsx; input/2026-10-27-claim-material/notes.md
uc: []
depends_on: []
blocks: []
---

Claim Material: baris KW 2 Supplier, Hold Coil, dan Reject Supplier dari Laporan Produksi diproses Purchasing (Email, Process
Decision Kompensasi/Retur) sampai Debit Note, Pembayaran PO dengan DN, atau Penerimaan Uang.

## Context

BPMN D003 dan workbook Rev.2 (13 sheet) dari klien; jawaban user atas 8 pertanyaan 2026-10-27 tercatat di
`input/2026-10-27-claim-material/notes.md`. Spesifikasi: `docs/fr-nfr.md` FR-75–FR-94, OQ-C1–OQ-C9. Rencana rinci dan urutan
tahap: `.artifacts/claim_material/plan_claim_material_27-10-2026.md`.

## Current State

Per 2026-10-28: mockup Claim final (`ui-mockup/claim-*.html`, popup Hold & Claim, tautan Lihat Jurnal), FR-75–FR-97, ERD 9 entitas claim,
workbook UC-N21–N36. Ruang lingkup saat ini = desain konsep dan mockup; kode aplikasi dikerjakan programmer kemudian. Bahan handover: 9 tabel
sudah dibuat di `hfg_dev` (SQL di `application/modules/*/migrations/`), rencana dan task di `.artifacts/claim_material/`.

## Scope

Tahap 1 (mockup, dikerjakan): halaman `ui-mockup/claim-*.html` dan menu "Claim Material" di `berry-shell.js`.
Tahap 2: tombol/popup Hold & Claim di Laporan Produksi; Tahap 3: ERD usulan di `erd-revisi.drawio` + workbook UC;
Tahap 4: modul CI3. Di luar cakupan: modul Pembayaran PO dan Penerimaan Uang yang sudah ada di aplikasi (hanya penambahan
Alokasi DN), Email SMTP nyata.

## Verification

- [x] Mockup dibuka di browser, tiap halaman tanpa error JS (11 halaman, Chrome headless, 2026-10-28); alur Claim → SPK → SJ → DN → Alokasi/Penerimaan berjalan di data contoh (uji logika flow2/3/4 lolos)
- [x] Angka simulasi workbook: Debit Note Kompensasi 2.306.810,81 cocok. Nilai Retur sengaja berbeda dari workbook sejak 2026-10-29 (keputusan user: Berat × Costbook/Kg gudang asal, tanpa ÷ 1,11): data contoh 3.125 Kg × 18.200 = 56.875.000 (workbook lama 52.927.927,93)
- [ ] Jurnal tiap titik sama dengan tabel di `input/2026-10-27-claim-material/notes.md`

> OPEN DECISION: OQ-C1–OQ-C5 di `docs/fr-nfr.md` memakai asumsi mockup; konfirmasikan sebelum ERD/kode.
