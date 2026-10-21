# Vocabulary — business language to our names

Hand-maintained, and it has to be: this file covers the gap no scanner can
close — **the words the business uses are not the words in the artifacts.**
Somebody told "the client wants the kartu stok screen changed" has to know which
diagram page, which entity, and later which class that is, and grepping the
business term with a space in it finds none of them.

Read this before searching for anything by its business name. Terms are listed
in **the client's language first**, because that is what tickets, elicitation
notes and users actually say — see the domain-language rule in
`context/general-rules.md`.

## Traps — where the obvious guess is wrong

The highest-value section. Fill it with the terms that have already caused a
wrong search, not with everything.

| You hear | It is | Not |
|---|---|---|
| _{business term}_ | _{the artifact and/or code name, with the file it lives in}_ | _{the plausible guess that returns nothing}_ |
| _Costbook Material_ | the **average value per material, per source warehouse** (Gudang Produksi 2 / WIP / Hold — each its own number), read by UC-11's HPP/jurnal inputs; see `docs/fr-nfr.md` "Konfirmasi klien" | _Product Costing_ — that is a **separate module**'s (in scope since 2026-10-07, `docs/fr-nfr.md` FR-37–FR-58) standard rate per product (Man Power, FOH, etc.), read by production as a dependency, never the same number as Costbook Material even when a source sheet's row literally says "Costbook Material dari Gudang Produksi 2" for a Hold/WIP-sourced coil (that literal wording is wrong per client confirmation — the rule is "gudang asal", not always Produksi 2) |
| _Gudang Material Hold_ | (client edit, 2026-09-21, unresolved) most likely a **status flag on Gudang Produksi 2 stock**, not a separate physical warehouse — `docs/workbook.xlsx` Entities sheet now carries its `Modul` as the literal string `Gudang Produksi 2 "Status HOLD"` rather than a real module, and `context/index/map.md`'s Entities section flags this as open | a **third warehouse alongside Gudang Produksi 2 and Gudang WIP** — that was the pre-edit assumption (`UC-N01`'s "Add Coil from Hold" source), now superseded pending client confirmation |

Four trap shapes worth watching for, all of which produce a confident wrong
answer rather than an empty result:

- **The name was translated once and not again.** The entity is `Regulation`,
  the table is `regulasi`, and no class is called `Regulasi`.
- **The singular and the plural are different concepts.** One is the type, the
  other is the physical instance, and both exist.
- **One word means three things.** The same term names a model, a screen, and a
  schedule, and only context distinguishes them.
- **It is not an entity at all.** The word everyone says is a column on
  something else, or a derived view, and searching for a table by that name
  finds nothing because there is nothing to find.

## By area

Group by the same areas the workbook's Modules sheet uses, so the vocabulary,
the ERD, and the class diagram cannot disagree about what a module is.

### Manufacturing/Production

- **Baby Coil** — the individual roll-level unit inside a **Pack** (`No. Uniq Pack`); a Pack
  contains a `Jumlah Baby Coil dalam Pack` count of these. Don't conflate the two: `docs/workbook.xlsx`
  Entities sheet lists both as separate entities (UC-01), and every downstream UC (Add Coil,
  FG KW1/KW2, Sisa Coil, Hold Coil) selects a **Baby Coil**, never a Pack.
- **KW1 / KW2 / Stok Bebas** — three different Finish Good outcomes of the same production
  session, each its own UC: KW1 (`UC-04`, first-quality product against the running SPK), KW2
  (`UC-05`, second-quality/reject-grade product, split Internal vs. Supplier), Stok Bebas
  (`UC-N02`, product made from leftover coil **outside** the running SPK — not a KW grade at
  all). "KW2" is not a defect flag on KW1's row; it is a fully separate input flow.
- **Sisa Coil vs. Hold Coil** — both are "leftover" but not the same: Sisa Coil (`UC-N03`) is
  the remaining weight of a baby coil after normal production, feeds Stok Gudang WIP for reuse
  next session; Hold Coil (`UC-N04`) is coil set aside pending Claim/inspection and **blocks**
  Submit Laporan Produksi (`UC-10`) until resolved (FR-17/NFR-06) — Sisa Coil never blocks Save.

### Product Costing

Sumber: sheet *Istilah* di `input/Rev.1 Use Case Product Costing.xlsx` (2026-10-19). Kolom
"Catatan" menyebut keputusan user atau nama ERD; arti sudah disesuaikan dengan
`docs/fr-nfr.md` "Revisi Rev.1" bila ada perbedaan dari sheet.

| Istilah | Artinya | Catatan |
|---|---|---|
| BOM (Bill of Materials) | Daftar bahan beserta jumlahnya yang dibutuhkan untuk membuat satu produk. | ERD `BOM Header` / `BOM Detail`; satu produk punya banyak BOM, tidak boleh memakai material yang sama dua kali (FR-51). |
| Varian Tebal | Jenis produk yang dibedakan berdasarkan ketebalannya. | Kolom `Varian type` pada BOM Header (keputusan user 2026-10-19). |
| Berat/Meter | Berat produk untuk setiap satu meter. | **Dihitung di BOM**: `((Tebal − Toleransi) × Lebar Coil × Berat Jenis) + Coating` (FR-51); bukan lagi atribut Master Material. |
| Flow Process & Cycle Time | Urutan proses produksi dan lama pengerjaan tiap proses. | ERD `Flow Process Header/Detail`; dikunci per Product Category (L2). |
| Man Hour | Jam kerja tenaga kerja yang dibutuhkan untuk membuat produk. | Total Man Hour per Kategori Produk; Rate Man Hour = Rate Man Power; Man Power Cost = Total Man Hour × Rate Man Power. |
| FOH (Factory Overhead) | Biaya pabrik yang tidak langsung ke produk, misalnya listrik dan perawatan mesin. | Rate per Kategori Produk dalam **Rp/Kg**; Rp/Meter = Rate × Berat/Meter (user 2026-10-19). |
| Bahan Pendukung Khusus | Bahan tambahan di luar bahan baku utama yang dibutuhkan untuk kategori produk tertentu. | Rate Rp/Kg per Kategori Produk; termasuk Biaya Proses (bukan Manufacturing Support). |
| Faktor Indirect Labor | Pengali biaya tenaga kerja tidak langsung pada Man Power Cost. | **Master tersendiri** (global, satu nilai), dikelola Costing, karena dapat berubah; bukan per Product Category dan hanya pengali di Man Power Cost (user 2026-10-20); mengalikan (Total Man Hour × Rate 1/3 Operator + Borongan Rp/Meter). |
| Consumable (Umum) | Bahan habis pakai yang dipakai secara umum di produksi. | Rate Rp/Kg, berlaku semua produk; termasuk Manufacturing Support. |
| Scrap | Sisa atau buangan bahan produksi. Di alur ini dihitung sebagai persen dari Material Cost. | Rate Scrap (%); beda dari `production_report_scraps` (scrap aktual). |
| Biaya Koordinasi | Biaya untuk mengoordinasikan produksi. Di alur ini dihitung memakai Berat/Meter. | Rate Rp/Kg × Berat/Meter; kolom ERD `coordinator_cost_*`. |
| Manufacturing Support | Gabungan biaya penunjang produksi: Consumable, Scrap, dan Biaya Koordinasi. | Bukan Biaya Proses (UC-13 langkah 4 salah ketik, user 2026-10-19). |
| Kurs | Nilai tukar USD ke Rupiah. | ERD `Master Kurs`. |
| Price Reference Rp/Kg | Harga acuan material dalam Rupiah per Kg, dihitung dari harga USD/Kg dan kurs. | USD/Kg = **Max** History Pembelian (hanya Max dipakai, user 2026-10-19). |
| Material Cost | Biaya bahan baku untuk membuat satu produk. | Rp/Kg = Price Reference; Rp/Meter = Price Reference × Berat/Meter. |
| HPP (Harga Pokok Produksi) | Total biaya membuat produk: Material Cost + Biaya Proses + Manufacturing Support. | Jangan tertukar dengan `total_hpp_*` di Laporan Produksi (HPP aktual dengan Costbook Material). |
| Other Cost | Biaya di luar produksi yang ikut dihitung. | **Enam elemen terbaru (UC-09):** Sales & Marketing, Transport, Finance & Admin, Management Fee, Interest, Profit. Daftar empat elemen di sheet *Istilah* usang. Masing-masing % × HPP. |
| Bottom Price | Harga batas bawah, yaitu HPP ditambah Other Cost. | `HPP + Σ (Rate Other Cost × HPP)` (user 2026-10-19). |
| Negotiation Allowance (Extra Margin) | Tambahan margin sebagai ruang untuk negosiasi harga dengan pelanggan. | **Persentase per Product Category** × Bottom Price; Price List = Bottom Price + allowance. Rate aktif hanya berubah setelah pengajuan Sales Manager **disetujui GM** (FR-67, BPMN Sales D01, user 2026-10-21). |
| Extra Margin | Nama lain **Negotiation Allowance** di BPMN Sales D01 ("Update dan Ajukan Rate Extra Margin baru", gateway "Approval Extra Margin"). | Perubahan rate diajukan **Sales Manager**, diputuskan **GM** (Approved / Reject); Reject kembali ke Sales Manager. Berbeda dari **diskon Quotation** (<5% Sales, 5–10% Sales Manager, >10% GM) yang di luar scope Product Costing. |
| Notifikasi Perubahan Bottom Price | Peringatan di dashboard ke Sales bahwa Bottom Price suatu Produk × varian BOM berubah karena costing baru, supaya Sales Manager menilai apakah Rate Extra Margin lama masih sesuai harga pasar dan memutuskan **Ajukan Rate Extra Margin Baru** atau **Tidak Perlu Diubah**. | Usulan (FR-68–FR-70, UC-N18, entitas ERD `bottom_price_notification`); rincian diasumsikan, open question 25–32. Sistem tidak mengubah rate/Pricelist otomatis (NFR-16). |
| Harga jual saat ini | Harga jual yang sedang berlaku sebagai pembanding pada notifikasi Bottom Price. | Diasumsikan = Price List pada Product Catalog terakhir dirilis; bila belum ada, Price List baseline (OQ 27). |
| Price List / Pricelist | Daftar harga jual produk. Di alur ini dihitung dari Bottom Price dan Negotiation Allowance. | Harus disetujui Management sebelum menjadi Product Catalog. |
| Product Catalog | Katalog produk berisi harga resmi yang sudah disetujui Management. | Dirilis Sales setelah Approve (UC-18); **disimpan sebagai data** (snapshot harga yang disetujui, user 2026-10-19). |

Istilah yang **tidak ada di sheet *Istilah*** tetapi dipakai di Use Case/BPMN: **Rate Man Power**
(= Rate Man Hour di BPMN, per Product Category) = `(Rate 1/3 Operator + Rate Borongan Helper)
× Faktor Indirect Labor` (per meter: `(Total Man Hour × Rate 1/3 Operator + Borongan Rp/Meter) × Faktor`, FR-56); **Rate 1/3 Operator** = (Rate Direct
Labor Operator ÷ 173 jam) × 1/3 (dibulatkan ke atas ke Rupiah penuh sesudah dikali 1/3), karena Operator dianggap hanya 1/3 bekerja langsung pada produk;
**Rate Borongan Helper** dari master Borongan per Product Category, hanya dalam **Rp/Meter** (tanpa Rp/Pcs dan tanpa konversi, user 2026-10-20);
**Master Standard Material** (Standar Toleransi, Berat Jenis, Coating; UC-03; global, satuan tidak
ditampilkan).

_Add a term the first time someone has to ask what it maps to._
