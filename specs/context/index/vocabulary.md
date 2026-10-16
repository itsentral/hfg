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
| _Costbook Material_ | the **average value per material, per source warehouse** (Gudang Produksi 2 / WIP / Hold — each its own number), read by UC-11's HPP/jurnal inputs; see `docs/fr-nfr.md` "Konfirmasi klien" | _Product Costing_ — that is a **separate, out-of-scope module**'s standard rate per product (Man Power, FOH, etc.), only read as a dependency, never the same number as Costbook Material even when a source sheet's row literally says "Costbook Material dari Gudang Produksi 2" for a Hold/WIP-sourced coil (that literal wording is wrong per client confirmation — the rule is "gudang asal", not always Produksi 2) |
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

_Add a term the first time someone has to ask what it maps to._
