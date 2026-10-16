# Workflow Log

Narrative record of what happened and why. **Newest entry at the top.**
Append-only: never edit or delete a past entry — a log that gets corrected in
place is a log nobody can trust as evidence. A superseded entry is corrected by
a new entry that says so.

## What goes here, and what does not

This file records **events**. `context/` records **truth**. The test: if the
sentence would still be worth reading when the issue is long closed, it belongs
in `context/`, not here.

| | Goes to |
|---|---|
| "FIX-12 closed, verified against the failing case" | here |
| "The gateway style attribute is `gwType=`, not `symbol=`" | `context/` — via `context/gotchas.md` |
| "We chose X over Y, and here is what it costs" | `context/index/decisions/` + a row in `decisions.md` |
| "What is open, what is next" | `pm/tracker.yaml` |

A durable fact that lands only here is lost: nothing reads this file at session
start, by design.

## Entry format

One `-` bullet per event, newest first:

```markdown
- **YYYY-MM-DD** `[TAG][ID]` **Headline in bold.** What changed, what it was
  verified against, and anything a future reader would otherwise have to
  reconstruct. Inline `[TAG]`s are fine mid-entry when one event carried
  several kinds of outcome.
```

`ID` is the issue id, or omitted for work that never became an issue.

| Tag | For |
|---|---|
| `[STATUS]` | An issue opened, moved, closed, or was dropped |
| `[DECISION]` | A choice was made — record the alternative that was rejected |
| `[DISCOVERY]` | Something turned out not to be what it looked like |
| `[TODO]` | Something was deliberately deferred, and what would settle it |

---

- **2026-10-08** `[STATUS][FEAT-01]` **BOM: Berat/Meter di Daftar Material + material anti-double.**
  Dua permintaan user pada modal BOM: (1) tabel Daftar Material menampilkan kolom Berat/Meter
  (kg/m) read-only di sebelah Qty, terisi dari material terpilih dan ikut berubah saat material
  diganti; (2) material yang sudah dipakai BOM lain tidak boleh dipilih ulang. Dropdown material di
  `bomForm` kini difilter: mengecualikan `materialId` yang dipakai BOM lain, tetap menyertakan
  material milik baris itu sendiri, plus opsi "— Pilih Material —". Diverifikasi harness Node: BOM
  pemakai MT1 tidak menawarkan MT2 (dan sebaliknya), BOM baru kosong mengecualikan MT1 & MT2 yang
  sudah terpakai; screenshot modal menampilkan kolom Berat/Meter = 1,34.

- **2026-10-08** `[STATUS][FEAT-01]` **Product Master & Material Name auto-generate (bisa override).**
  Umpan balik user: kedua nama bukan sekadar input bebas — otomatis tersusun dari gabungan field,
  tetap bisa diedit. Ditambahkan `productName()`/`materialName()` + `applyProductName()`/
  `applyMaterialName()` + flag `nameManual` di `pc-data.js`. Product Master = Type + Category +
  Jenis + Tebal + Lebar + Panjang (mis. "ATAP SPANDEK POLOS 0.25 X 750 X 6 M"); Material Name =
  Metal + Slit + Boron + Tebal-Lebar-Coating-Tensile- (mis. "GALVALUM MOTHER BORON 0.14-1219-AZ70-
  G550-", persis format Excel). Di form modal, field sumber ber-`struct` sehingga mengubahnya
  menyusun ulang nama; mengetik di field nama menyetel `nameManual=true` (regen berhenti); link
  "Generate ulang" mengembalikan ke mode otomatis. Diverifikasi harness Node: generator cocok
  dengan format konsep, auto-overwrite saat tidak manual, preserve saat manual, regen saat field
  sumber berubah, manual bertahan setelah itu. Nama seed tetap utuh saat load (regen hanya saat
  ada perubahan).

- **2026-10-08** `[STATUS][FEAT-01]` **Price Reference & Rate BP/FOH juga dipisah list↔modal (koreksi ke-2).**
  Umpan balik user: sheet "Price Reference Mat" dan "Rate BP dan FOH" juga punya pembagian Tampilan
  Depan vs Detil. Pemeriksaan ulang gambar konsep menunjukkan `[DISCOVERY]` acuan Tampilan Depan
  Price Reference adalah image13 ("Price Reference » Raw Materials": Material Code, Material Master,
  Price Ref IDR, Price Ref USD, Expired, Status, Action-download) — image14/15 ternyata screenshot
  mockup lama kami (berbadge UC-N08), bukan acuan. Price Reference diubah jadi list-only + modal
  View/Edit berisi History Pembelian & keputusan pemakaian; ditambah `priceMeta` (expired+status)
  dan `priceMetaOf()` di `pc-data.js`, plus `kode` (Material Code) pada material. Rate BP/FOH diubah
  jadi list (`No | Product Category | Rate BPK | Rate FOH | Action`) + Add Rate / View / Edit modal,
  sesuai teks sel sheet. Diverifikasi: price list 4849→2025 chars, bpkfoh 1554 chars, modal
  price/bpkfoh terbuka berisi form, header konsep lengkap, costing tetap (HPP 19.733,47) — harness
  Node + screenshot headless Chrome. Catatan sebelumnya yang menyebut keduanya "sudah sesuai,
  unchanged" dengan ini dikoreksi.

- **2026-10-08** `[STATUS][FEAT-01]` **Detil Input dipisah ke modal (koreksi).** Umpan balik user:
  "Tampilan Depan" = header/daftar saja; "Detil Input" = isi saat Add/View/Edit dan tidak boleh
  disatukan sebagai kartu atas/bawah di satu halaman. Ditambahkan mekanisme modal di `pc-shell.js`
  (`openModal`/`closeModal`/`renderModal`, host `#pcModal`, tutup via overlay/×/Escape, mode `view`
  mendisable input), lalu Master Produk, Master Material, BOM, Flow Process & CT, dan Simulasi
  diubah jadi list-only dengan aksi View/Edit (+ Add/Buat Baru) yang membuka detilnya di modal
  terpisah. Simulasi: tombol View membuka "Detil Isi dari Costing (Costing Category Product)" di
  modal (kolom varian yang dipilih disorot). Diverifikasi: 10 halaman render sebagai list (ukuran
  render turun drastis, mis. produk 7807→1473 chars), 8 alur modal (produk/material/bom/flow/
  simulasi add/edit/view) terbuka berisi form, angka costing tetap (HPP 19.733,47; Price List
  21.756,15) — harness Node + screenshot headless Chrome (list header-only + modal "Edit Product
  Master" tampil sebagai overlay di atas list yang diredupkan).

- **2026-10-08** `[STATUS][FEAT-01]` **Product Costing mockup revised to the Excel concept.**
  Revised `ui-mockup/pc-data.js`, `pc-pages.js`, `pc.css` so all 8 requested menus match
  `input/Mockup Lengkap dari Master Product sampai Product Costing.xlsx`: Master Produk list +
  Detil Input, Master Material concept fields (Berat/Meter kept — `[DECISION]` Q1, it drives
  Material Cost and Berat per Pcs), BOM front list + Daftar Material with Qty (`[DECISION]` Q2,
  variant/Uniq-ID model kept because Simulasi renders one column per thickness variant), Flow
  front list + reordered detail columns, Price Reference & Rate BP/FOH verified already matching,
  General Rate Other Cost labels made editable and propagated into Simulasi (hardcoded `OTHER`
  constant replaced by `OTHER_KEYS` + live `genLabel`), and Simulasi front "Daftar Product
  Costing" list + per-variant "Detil Isi dari Costing" breakdown shown as information. Verified:
  HPP (19.733,47 / 20.831,85), Price List (21.756,15 / 22.967,12) and Berat per Pcs (8,04 / 8,64)
  still match the concept images exactly; all 10 pages render with no JS errors via two Node
  harnesses (string render + full `pc-shell` init pipeline, both ALL PASSED) and headless-Chrome
  screenshots of produk/material/bom/flow/general/simulasi. Mockup-only (localStorage, no backend):
  ERD and UC rows unchanged — the added form fields are mockup fields, not confirmed schema. Full
  change note added to `context/index/map.md` under the Product Costing section.

_Nothing before this._
