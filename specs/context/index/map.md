# Map — what owns what

Traceability index: a use case or feature, and the artifacts — and later, the
code — that realise it. Agents load this to navigate without re-reading the
whole repository; humans use it for tickets and onboarding. Update it when
ownership changes, not merely because a file exists.

> **NOT MACHINE-PARSED, by design.** This is an index of pointers — closer to a
> class diagram in text than to a config file. It is read, never loaded: no
> tooling parses it, and `note:` values are prose that may contain colons,
> quotes, or anything else that helps a reader. The `key: value` shape below is
> for human and agent legibility only. The file is `.md`, not `.yaml`, precisely
> so that nothing — reader or tool — expects it to parse. Do not "fix" it into
> strict YAML, and do not build tooling that parses it.
>
> (Renamed from `map.yaml`. The extension was the only signal a reader got
> before this warning, and it promised the wrong thing.)

**This file is the prose layer** — ownership, use cases, and the "why" that no
scanner can produce. It does not hold the inventory. `context/index/README.md`
explains the three index files and what each one answers.

---

## Use cases → artifacts

One entry per use case or feature worth tracing, naming where its behaviour is
specified and what implements it. Only the lines that are true get written — a
UC with no diagram simply has no `bpmn:` line.

```
uc: UC-01
  name: {the workbook's own wording, in the client's language}
  bpmn: docs/diagrams/{file}.drawio → page "{Level 2 — ...}"
  entities: [{Entity}, {Entity}]
  state: docs/diagrams/{file}.drawio, when this UC drives a lifecycle
  code: ~
  note: {what a reader would otherwise have to reconstruct — a rule not visible
        in the diagram, an exception, the decision that shaped it}
```

```
uc: UC-01
  name: Catat Laporan Unpacking Coil
  user: Operator
  entities: [tr_unpack_coil_header, warehouse_pack, baby_coil, material, gudang_produksi_2]
  code: ~
  note: folds the automatic "Masuk ke Stok Gudang Produksi 2" service task (BPMN
        gear icon) into this UC's sub-flow — that gear task has no own row.
  note: workbook User was hand-edited by the client from "Operator" to "Admin
        Produksi" on 2026-09-21, then reverted back to "Operator" on 2026-09-22
        after cross-checking against this UC's own Deskripsi, FR-04's text, and
        the entity Owner — all three agreed on Operator, so the hand-edit was
        treated as a data-entry slip, not a deliberate reassignment. Modul
        reassigned from Manufacturing/Production to a new per-screen module
        "Unpack" — see the Modul-restructuring note below (still current).
  note: entity names updated 2026-09-22 to snake_case per the client's revised
        ERD (`input/Rev.1 erd.drawio produksi.drawio`) — see the Entities
        sheet in `docs/workbook.xlsx` for the full old-name → new-name map.

uc: UC-02
  name: Catat Laporan Ketidaksesuaian Material
  user: Admin Produksi
  entities: [laporan_ketidaksesuaian_material, baby_coil, material]
  code: ~
  note: precondition "Cek kondisi coil setelah unpacking" is a hand-icon Manual
        task (physical inspection) and is excluded, not folded — it has zero
        system involvement, only its "Tidak sesuai" outcome leads here.
  note: workbook User was hand-edited by the client from "Operator" to "Admin
        Produksi"; Modul reassigned to "Report Produksi" (see Modul
        restructuring note below). The Deskripsi text in the workbook still
        narrates the old "Operator" actor for the excluded manual precondition
        — that's the BPMN lane, not this UC's own actor, so left as-is.

uc: UC-03
  name: Release Kembali Material Coil untuk Produksi
  entities: [material_coil, laporan_ketidaksesuaian_material]
  code: ~
  note: separate UC from UC-02 despite being the same FR-06 — actor changes
        (Operator → Claim Material, an out-of-scope external process → PPIC),
        which is an actor-boundary split regardless of route.

uc: UC-04
  name: Input Jumlah Finish Good KW 1
  entities: [production_report_items, baby_coil, product_lvl_4]
  code: ~

uc: UC-05
  name: Input Jumlah Finish Good KW 2
  entities: [production_report_items, product_lvl_4]
  code: ~
  note: FR-11 describes KW2 Internal and KW2 Supplier as two input flows, but
        the BPMN has one User task box for both — they fold into this single
        UC's Deskripsi as two sub-flows, same pattern as FR-09's two weight
        methods on UC-04. FR-26 (2026-09-22) confirms this from the client's
        revised ERD: `report_produksi` carried two fully parallel field groups
        (Internal/Supplier), not a shared set with a type flag. Superseded
        2026-09-24 (Rev.2, FR-34): KW rows are now `production_report_items`
        rows told apart by `category_type` (values deferred).

uc: UC-06
  name: Input Total Reject Product
  entities: [production_report_scraps]
  code: ~
  note: no single FR names this box explicitly; content is inferred from the
        BPMN label plus FR-21's "Reject Produk Internal/Supplier" costing
        category that consumes this qty. FR-27 (2026-09-22) grounds this
        further: the client's ERD shows 7 named scrap categories, each with
        its own Qty/Costbook/Inventory/Biaya columns on `report_produksi`
        (capture side) and `hpp` (valuation side) — see UC-11 note. Superseded
        2026-09-24 (Rev.2, FR-35): scrap is one row per scrap in
        `production_report_scraps` (`scrap_type`, values deferred); the
        7-category × 4-column layout no longer exists.

uc: UC-07
  name: Input Total Reject Material Plat
  entities: [production_report_scraps]
  code: ~

uc: UC-08
  name: Input Reject Potongan Pisau
  entities: [production_report_scraps, production_reports]
  code: ~
  note: also carries the Tong Coil and Wrapping qty fields (FR-14a, mock-up
        "Konsep yang akan dipakai" rows 89-90) — those have no BPMN box of
        their own and are folded here as same-screen fields, not new UCs.

uc: UC-09
  name: Verifikasi Selisih Berat Produksi
  entities: [production_reports]
  code: ~
  note: BPMN box is "Cek kembali ke lapangan dan verifikasi laporan"; the
        Summary computation (FR-15) and the "Output Berat Produksi" gateway
        that triggers this UC are automatic sub-flow, folded in, not their own
        UC.

uc: UC-10
  name: Submit Laporan Produksi
  entities: [production_reports, stok_gudang_wip, production_plan, asset, employee]
  code: ~
  note: folds two automatic sub-flows: Stok Gudang WIP update, and sending
        Report Produksi + Sisa Coil to Warehouse Finish Good (out-of-scope
        external process). Save is gated by FR-17/NFR-06 (no Hold Coil open).
  note: 2026-09-22 — `production_plan` (FR-25, `id_tr_spk_detail` FK, external
        read-only), `asset` (FR-29, `id_asset`, machine used for this report),
        and `employee` (FR-30, `id_employee`, who submitted — supersedes the
        earlier Staff/Role-enum assumption) are now recorded at this UC's
        point of finalizing the report — this is the UC judged to own the
        `report_produksi` header record, matching NFR-02's traceability
        wording. Grounded in the client's revised ERD, see `docs/fr-nfr.md`.
  note: 2026-09-24 — Rev.2 renamed the header to `production_reports`, and
        FR-36 replaced `id_asset`/`id_employee` with `id_asset_machine` and
        `employee_helper`/`employee_setter` (still referencing the `asset` and
        `employee` masters; helper/setter stay string-typed).

uc: UC-11
  name: Posting Jurnal Produksi ke GL Interface
  entities: [jurnal_produksi, gl_interface, production_report_items, production_reports]
  code: ~
  note: this UC's Modul is "Report HPP"; the three entities it creates
        (Jurnal Produksi, GL Interface, HPP) were resolved 2026-09-21 to the
        same Modul, per client decision — "Finance/GL" is retired from the
        Modules sheet (nothing pointed at it any more).
  note: 2026-09-22 — `hpp` absorbed the FR-27 (7-category Scrap valuation) and
        FR-28 (per-KW-variant × per-pcs/Total cost breakdown: Material, Man
        Power, FOH, Bahan Pendukung, Consumable, Coordination, Scrap, FG Cost)
        detail from the client's revised ERD; no new UC was needed since this
        one already owned `hpp`. The old standalone `Costbook Material` entity
        (added 2026-09-21) was retired the same pass — those values are now
        inline columns on `report_produksi`/`hpp`, not a separate lookup.
  note: 2026-09-24 — Rev.2 replaced `hpp`: cost components now live on
        `production_report_items` (FR-34) and the total on
        `production_reports.total_hpp_keseluruhan` (FR-32). The 80/20 and 40/60
        splits have no Rev.2 column yet (open). `jurnal_produksi.HPP_Id` will
        read from a future Report HPP (not yet drawn).

uc: UC-N01
  name: Add Coil dari Multi Sumber Gudang
  entities: [baby_coil, tr_unpack_coil_header, stok_gudang_wip, gudang_material_hold, material, production_report_materials]
  code: ~
  note: covers all three BPMN-absent sources (Unpack/WIP/Hold, FR-08) as one
        UC, not three — same actor, same sitting, admission test 5 does not
        split it (Route 2, no BPMN box to anchor granularity here).

uc: UC-N02
  name: Catat Stok Bebas
  entities: [production_report_items, product_lvl_4, baby_coil]
  code: ~
  note: structurally identical input flow to UC-04 (FG KW1) but a distinct
        actor goal — product outside the running SPK — so kept as its own UC
        per FR-10.

uc: UC-N03
  name: Catat Sisa Coil
  entities: [warehouse_stock, baby_coil]
  code: ~
  note: 2026-09-24 — Sisa Coil and Hold Coil will live in a future
        `warehouse_stock` table, told apart only by warehouse id (not yet
        drawn on the ERD).

uc: UC-N04
  name: Catat Hold Coil
  entities: [warehouse_stock, baby_coil]
  code: ~
  note: an open Hold Coil on the session blocks Submit Laporan Produksi
        (UC-10) per FR-17/NFR-06 until Hold & Claim resolves it.

uc: UC-N05
  name: Kelola Master Produk
  user: Engineering
  entities: [product_lvl_4]
  code: ui-mockup/pc-produk.html (mockup only, localStorage; no backend)
  note: Product Costing UCs UC-N05..UC-N14 added 2026-10-07 from FR-37..FR-45 plus
        FR-46/FR-47 (`docs/fr-nfr.md`); actors given by the user the same day. All 11
        Product Costing open questions were answered 2026-10-07 and are recorded in
        `input/2026-10-07-product-costing-jawaban/`. Master Produk keeps Tebal and
        Panjang; berat per pcs is not stored on the product, it is Berat/Meter of the
        chosen BOM variant x Panjang (so `berat_standard` is derived, not a product column).

uc: UC-N06
  name: Kelola Master Material
  user: Engineering
  entities: [material]
  code: ui-mockup/pc-material.html (mockup only, localStorage; no backend)
  note: adds Berat/Meter (kg/m) to the material master. `material` moved from module
        Unpack to the new Master Material module on 2026-10-07 because this UC
        creates the record (single-owner rule).

uc: UC-N07
  name: Kelola Kurs
  user: Accounting
  entities: [master_kurs]
  code: ui-mockup/pc-kurs.html (mockup only, localStorage; no backend)

uc: UC-N08
  name: Kelola Price Reference
  user: Finance
  entities: [history_purchase, price_reference_header, price_reference_detail, material, master_kurs]
  code: ui-mockup/pc-price-reference.html (mockup only, localStorage; no backend)
  note: owner is Finance (Rev.1 BPMN lane, workbook, FR-52); only the Max of the purchase history is used
        (user 2026-10-19) - the earlier Purchasing owner and the Harga Terakhir/Rata-rata choice are superseded.
        history_purchase data itself still originates from Purchasing/PO.

uc: UC-N09
  name: Kelola BOM Produk
  user: Engineering
  entities: [bom_header, bom_detail, product_lvl_4, material]
  code: ui-mockup/pc-bom.html (mockup only, localStorage; no backend)
  note: one BOM variant per material thickness, each with its own No. Uniq ID.

uc: UC-N10
  name: Kelola Flow Process & Cycletime
  user: Engineering
  entities: [flow_process_header, flow_process_detail]
  code: ui-mockup/pc-flow.html (mockup only, localStorage; no backend)
  note: keyed by Product Category (Level 2) — user decided 2026-10-07, resolving the
        conflict between the Excel and the client ERD (which used product level 4).

uc: UC-N11
  name: Kelola Man Power Rate
  user: Accounting
  entities: [man_power_rate]
  code: ui-mockup/pc-man-power-rate.html (mockup only, localStorage; no backend)

uc: UC-N12
  name: Kelola Rate Bahan Pendukung Khusus & FOH
  user: Accounting
  entities: [rate_bahan_pendukung_foh]
  code: ui-mockup/pc-bpk-foh.html (mockup only, localStorage; no backend)
  note: keyed by Product Category (Level 2), same decision as UC-N10.

uc: UC-N13
  name: Kelola General Rate
  user: Accounting
  entities: [rate_generals]
  code: ui-mockup/pc-general-rate.html (mockup only, localStorage; no backend)

uc: UC-N14
  name: Lihat Simulasi Costing & Pricelist
  user: Accounting, Sales, Engineering
  entities: [product_costing_header, product_costing_detail, material_cost, man_power_cost]
  code: ui-mockup/pc-simulasi.html (mockup only, localStorage; no backend)
  note: three actors use the screen independently (no handoff), so it is one UC.
        FR-48/FR-49 (rates entering production-report HPP, frozen at report creation)
        are NOT a UC of their own — no actor; they fold into UC-10 / UC-11. Reads
        every master above; full list is in the workbook row.
```

**Note — Product Costing mockup revised to Excel concept (user, 2026-10-08, FEAT-01):** the
Product Costing mockup (`ui-mockup/pc-data.js`, `pc-pages.js`, `pc.css`) was revised to match
`input/Mockup Lengkap dari Master Product sampai Product Costing.xlsx` (concept carried as
embedded images per sheet; the Flow sheet is cell-text only). Changes are mockup-only (localStorage,
no backend, no schema change): (1) Master Produk gained a list (Kode, Code Master, Status) + a
"Detil Input" card with the concept's attribute fields (Product Retail, Product Code, Trade Name,
Packing Unit/Conversion, Unit Measurement, Max/Min Stock, MOQ, Upload MSDS, Dimensi P/L/T,
Berat/unit, CBM) — these are mockup form fields, NOT confirmed `product_lvl_4` columns; the ERD is
unchanged. Lebar/Tebal/Panjang kept (Panjang drives Berat per Pcs). (2) Master Material gained the
concept detail fields (HS Code, Alias, Coating, Color, Hardness, Tensile, No. Accurate, Packing
Unit/Conversion, Unit Measurement, Max/Min Stock, Upload MSDS, Alternative Supplier, Status);
**Berat/Meter was kept** though the concept form omits it, because Material Cost = Price Reference
Rp/Kg × Berat/Meter and Berat per Pcs = Berat/Meter × Panjang both depend on it (confirmed with
user as Q1). (3) BOM now shows a front list per product (Kode Produk, Nama Produk, Jumlah Material,
Dibuat) and a "Daftar Material" detail with a Qty-per-material column; the one-variant-per-material
Uniq-ID model was kept because the Simulasi screen renders one column per thickness variant (Q2 —
Qty added as a field, Material Cost still driven by the variant's material). (4) Flow Process & CT
gained a front list and the detail columns were reordered to the concept (Cost Centre/Area, Process
Name, Cycletime, Man Power, Total Man Hour, Machine). (5)/(6) Price Reference and Rate BP/FOH
already matched the concept — verified, unchanged. (7) General Rate: the Other Cost group element
names are now editable (e.g. "Transport" → "Logistik") and the edited label flows into Simulasi
(the calc engine reads the live label via `genLabel`, replacing the old hardcoded `OTHER` constant
with `OTHER_KEYS`). (8) Simulasi gained a front "Daftar Product Costing" list (Product Category,
Product Name, Varian Material, Uniq ID, Harga/Meter, Harga/Kg, Last Updated, View/Ajukan Pricelist)
and the per-variant breakdown is now shown below as "Detil Isi dari Costing (Costing Category
Product)" information. Verified: HPP (19.733,47 / 20.831,85), Price List (21.756,15 / 22.967,12)
and Berat per Pcs (8,04 / 8,64) still match the concept images exactly; all 10 pages render without
JS errors (two Node harnesses + headless-Chrome screenshots). The underlying UC rows (UC-N05,
UC-N06, UC-N09, UC-N10, UC-N13, UC-N14) and the ERD are unchanged by this revision.

**Correction (user, 2026-10-08, same FEAT-01):** the first cut put the "Detil Input" as a second
card on the same page (above/below the list). The user clarified that "Tampilan Depan" means the
header/list only, and the detail (Add/View/Edit, and Simulasi's "Detil Isi dari Costing") must be a
separate window. A modal mechanism was added to `pc-shell.js` (`openModal`/`closeModal`/
`renderModal`, host `#pcModal`, close via overlay/×/Escape, `view` mode disables inputs). Master
Produk, Master Material, BOM, Flow Process & CT, and Simulasi are now list-only pages whose rows
carry View/Edit (plus Add / Buat BOM Baru / Add CT), each opening the detail in a modal. Kurs,
Price Reference, Man Power Rate, Rate BP/FOH, and General Rate stay single-screen (they are not
list→detail master screens). Costing numbers and the ERD remain unchanged.

**Correction 2 (user, 2026-10-08, same FEAT-01):** Price Reference and Rate BP/FOH were wrongly
left single-screen above — their sheets ("Price Reference Mat", "Rate BP dan FOH") also split
Tampilan Depan vs Detil. The Price Reference front is the image13 list ("Price Reference » Raw
Materials": Material Code, Material Master, Price Ref IDR/USD, Expired, Status, download) — image14/15
turned out to be our own old mockup screenshots, not the target. Both are now list→modal: Price
Reference list opens a View/Edit modal (History Pembelian + keputusan pemakaian); Rate BP/FOH list
(`No | Product Category | Rate BPK | Rate FOH | Action`) opens an Add/View/Edit modal. Added
`priceMeta` (expired+status) + `priceMetaOf()` to `pc-data.js`, and a `kode` (Material Code) field on
`material`. The genuinely single-screen set is now only Kurs, Man Power Rate, and General Rate.
Costing numbers and ERD still unchanged.

**Enhancement (user, 2026-10-08, same FEAT-01):** Product Master and Material Name are now
auto-generated composites that remain editable. `pc-data.js` gained `productName()` (Type +
Category + Jenis + Tebal + Lebar + Panjang), `materialName()` (Metal + Slit + Boron +
Tebal-Lebar-Coating-Tensile-, matching the Excel example `GALVALUM MOTHER BORON 0.14-1219-AZ70-
G550-`), `applyProductName`/`applyMaterialName`, and a per-record `nameManual` flag. In the detail
modal the source fields carry `struct` so editing one regenerates the name; typing in the name
field sets `nameManual=true` (regeneration stops); a "Generate ulang" link clears the flag and
recomposes. Seed names are preserved on load (regeneration only fires on a field change).

**Note — Product Costing in scope (user, 2026-10-07):** reverses the 2026-09-21 decision that
Product Costing is a separate module that Modul Produksi only reads. Master data is built and
filled through this system's UI. Two new modules, `Master Material` and `Product Costing`,
carry the UCs above. NFR-09 supersedes NFR-07(a): production reports keep the rates in force
when they were created. `docs/diagrams/erd.drawio` page "Product Costing ERD" (added 2026-10-07, derived from
`input/ERD Product Costing-Production Report.jpg`) now draws these entities; Flow Process Header
and the Bahan Pendukung/FOH rate are keyed by Product Category, not product level 4. Page 1 still
shows the reduced Produksi view of `material`/`product_lvl_4` (it also still groups `material`
under Unpack) — drift to fix when the Unpack layout is reworked. `man_power_rate_component` is our
inference from FR-43, not in the client ERD. Per-pcs cost in the production report follows the
earlier mock-up, not this work.

**Note — Modul restructuring (client hand-edit, 2026-09-21):** the client returned an edited
copy of the workbook that regroups `Modul` from the earlier ERP-module grouping
(`Manufacturing/Production`, one module for the whole production flow) into a per-screen/report
grouping: `Unpack` (UC-01), `Report Produksi` (UC-02–UC-10, UC-N01–UC-N04), `Report HPP` (UC-11),
plus two entity-only modules `Gudang Produksi 2` and `Master Product`. `Manufacturing/Production`
no longer has any UC or entity pointing at it and was removed from the Modules sheet per the
"don't keep a module nothing lands in" rule. `Finance/GL` was retired the same way on
2026-09-21, after the client resolved the UC-11 contradiction (below) by moving its three
entities — Jurnal Produksi, GL Interface, HPP — to `Report HPP` instead of moving UC-11 back.

**Note — Gudang Material Hold reinterpretation (client hand-edit, 2026-09-21):** the client's
edit sets the `Gudang Material Hold` entity's `Modul` to the literal string
`Gudang Produksi 2 "Status HOLD"` rather than a real Modules-sheet lookup value. This signals a
new understanding: **Hold Coil is a status on Gudang Produksi 2 stock, not a separate physical
warehouse/entity.** Recorded verbatim in the Entities sheet per the client's instruction, not
paraphrased — but it does not resolve to any row in the Modules sheet, so it reads as an open
question rather than a settled module assignment. Flag for client confirmation: is
`Gudang Material Hold` still a distinct entity at all, or should it be folded into
`Gudang Produksi 2` with a status field, in which case UC-N01's `Entity/Objek Terkait` list and
this whole row may need to be retired rather than just re-mapped.

**Note — Entity consolidation and snake_case rename (client hand-edit, 2026-09-22):** the client
returned their own revised ERD (`input/Rev.1 erd.drawio produksi.drawio`) and three decisions
followed from it: (1) FR-01 reopened as a read-only FK linkage, not a UC — see below; (2) the ~9
separate "Report Produksi" line-item entities (`Laporan Produksi`, `Finish Good KW1`/`KW2`,
`Komponen Scrap`, `Sisa Coil`, `Hold Coil`, `Stok Bebas`, `Summary Produksi`, plus the
short-lived standalone `Costbook Material` added 2026-09-21) collapsed into one wide table,
`report_produksi`, matching the client's own consolidated design rather than staying normalized;
(3) all entity names moved from this project's earlier `PascalCase_Underscore` convention to
`snake_case`, using the client file's literal table names wherever it named that exact concept
(e.g. `Laporan Unpacking Coil` → `tr_unpack_coil_header`, `Pack` → `warehouse_pack`, `Master
Product` → `product_lvl_4`, `Staff` → `employee`), and this project's own transliteration where
it didn't. Three new entities were added: `asset` (FR-29, Master Asset), `employee` (FR-30,
replaces the `Staff`/`Role` 4-value-enum idea with open `department`/`sub_department` strings),
and `production_plan` (FR-25, external/read-only, bundles `tr_spk_material_header`/`detail`).
Full old-name → new-name mapping lives in `docs/workbook.xlsx`'s Entities sheet, not duplicated
here. `docs/diagrams/erd.drawio` and `docs/fr-nfr.md` (FR-25–FR-30, Re-Confirm section) were
updated in the same pass — this file, the workbook, the FR/NFR doc, and the ERD should now all
agree; if a future session finds one disagreeing with another, that's drift to fix, not a
three-way vote.

**Note — Rev.2 replaces `report_produksi`/`hpp` (client, confirmed 2026-09-24):** the client's
second ERD revision (`input/Rev 2 Production Report.drawio`) restructures Report Produksi as one
header + three details: `production_reports`, `production_report_materials`,
`production_report_items`, `production_report_scraps`. The user confirmed it **replaces** the Rev.1
`report_produksi` and `hpp` entities, so `docs/diagrams/erd.drawio` and `docs/fr-nfr.md`
(FR-31–FR-36, which mark FR-26–FR-30 as superseded) were updated in the same pass. The workbook
Entities sheet and `Entity/Objek Terkait` cells were reconciled the same day. Any class diagram
naming `report_produksi` or `hpp` was checked 2026-09-24: none exists for this
project (only framework examples), and the UC block above was updated to Rev.2 names.

Follow-up decisions (user, 2026-09-24): `id_asset_machine` and `employee_helper`/`employee_setter`
reference the `asset` and `employee` master tables (the `employee` edge is back on the ERD;
`employee_helper`/`employee_setter` stay string-typed, confirmed with the IT team); the
± 0,7% tolerance stands; Sisa Coil and Hold Coil will live in a future `warehouse_stock` table,
told apart only by warehouse id (not yet drawn); `category_type`/`scrap_type` values are
deliberately deferred; `jurnal_produksi.HPP_Id` will read from a future Report HPP (not yet drawn,
so the FK row stays without a target on the ERD). The `baby_coil` (`id_warehouse_stock_coil`) edge
stays dropped — Rev.2 has no such column.

Not yet a UC in this workbook (deliberately excluded, see `docs/fr-nfr.md` decomposition
status and the reasoning below):

- **FR-02 (Warehouse Material lane)** — "Other Process" in the BPMN, i.e. an external module
  (Warehouse) out of Modul Produksi's scope; no User task box belongs to this module for it.
- **FR-01 (Production Planning issuing SPK) — reopened 2026-09-22, still excluded as a UC, but
  no longer a clean "not our concern".** The client's own revised ERD (`input/Rev.1 erd.drawio
  produksi.drawio`) models `production_plan` (`tr_spk_material_header`/`tr_spk_material_detail`)
  as a table `report_produksi` FKs against directly (`id_tr_spk_detail`). Re-checked against the
  source: nothing in it shows Modul Produksi creating or editing SPK rows, only referencing them
  — so FR-01 itself (SPK issuance) is still someone else's UC, and `production_plan` is drawn on
  the ERD as a greyed-out external/read-only entity, not a module this project owns. What changed
  is that the FK linkage itself is now a stated fact (`docs/fr-nfr.md` FR-25, grounding NFR-02's
  traceability requirement) instead of an assumed non-relationship. See `docs/fr-nfr.md`'s
  Re-Confirm section (2026-09-22) for the full resolution.
- **FR-03, FR-05, FR-07 (Setting Mesin, Cek kondisi coil, Coil pasang di mesin)** — Manual
  tasks (hand icon), zero system involvement, admission test 4 fails.
- **FR-19–FR-23 (HPP/costing calculations)** — no nameable actor triggers them; they are
  automatic derivations consumed by UC-11's posting, not use cases (admission test 1 fails).
  They stay documented as computation rules in `docs/fr-nfr.md`.
- **NFR-01–NFR-08** — quality constraints, not actor flows; see *Where NFRs go* in
  `workbook-conventions.md`.

## Diagrams → what they own

Which diagram is authoritative for which part of the model, so that two diagrams
never quietly disagree about the same thing.

```
file: docs/diagrams/{file}.drawio
  pages: [{page name}, {page name}]
  owns: {the process / entities / lifecycle this is the source of truth for}
  derived_from: input/{YYYY-MM-DD-slug}/ or docs/workbook.xlsx
  note: {drift risk, known gaps, who confirmed it and when}
```

_Empty._

## Entities → where the rules live

The workbook's Entities sheet is the deduped list. This section is only for
entities carrying a rule that sheet has no column for — a lifecycle, an
invariant, a naming exception.

_Empty._
