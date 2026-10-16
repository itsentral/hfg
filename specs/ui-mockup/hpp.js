/* ============================================================
   Laporan HPP — per SPK (read-only). Vanilla JS.
   Sumber data: snapshot hasil Input Produksi (window.MOCK.report per SPK).
   Struktur kolom & rumus mengikuti sheet "Konsep yang akan dipakai" baris 112–154:
     - Tabel 1  : FG KW1 & Stok Bebas (kolom F..W, termasuk Size)
     - Tabel 2  : FG KW2 Internal & Supplier (komponen penuh + Inventory 80% + Loss 20%)
     - Tabel 3  : Scrap (Inventory 40% / Beban FOH 60%)
     - Tabel 4-6: Sisa Coil / Hold Coil / Selisih Berat (Inventory full)
   ============================================================ */
(() => {
  "use strict";

  const M = window.MOCK;
  const spk = M ? M.spkByNo(M.store.spkNo) : null;

  const $ = (s, r = document) => r.querySelector(s);
  const rp = (n) => "Rp " + Math.round(Number(n) || 0).toLocaleString("id-ID");
  const kg = (n) => (Number(n) || 0).toLocaleString("id-ID", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const td = (v, cls = "") => `<td class="${cls}">${v}</td>`;
  const tdn = (v, cls = "") => `<td class="num rp ${cls}">${v}</td>`;

  const RATE = M ? M.RATE : { manPower: 400, foh: 300, bahanPendukung: 500, consumables: 200, koordinasi: 200, scrapPct: 0.02 };
  const cbRate = (mat, g) => (M ? M.costbookFor(mat, g) : 0);

  /* ---------- Snapshot hasil produksi untuk SPK ini ---------- */
  const snap = spk && M.report ? M.report.get(spk.no) : null;

  const EMPTY = { kw1: [], kw2Internal: [], kw2Supplier: [], scrap: [], sisa: [], hold: [], selisih: [] };
  const data = snap || Object.assign({ meta: null }, EMPTY);

  /* ---------- Komponen biaya/pcs (Excel baris 116/123/129) ---------- */
  function costPerPcs(row) {
    const cb = cbRate(row.material, row.gudang);
    const material = row.beratPcs * cb;
    const manPower = row.beratPcs * RATE.manPower;
    const foh = row.beratPcs * RATE.foh;
    const bahan = row.beratPcs * RATE.bahanPendukung;
    const cons = row.beratPcs * RATE.consumables;
    const koor = row.beratPcs * RATE.koordinasi;
    const scrap = material * RATE.scrapPct;            // 2% dari value material
    const fgPerPcs = material + manPower + foh + bahan + cons + koor + scrap;
    return { cb, material, manPower, foh, bahan, cons, koor, scrap, fgPerPcs };
  }

  /* ============================================================
     TABEL 1 — HPP KW1 & Stok Bebas (kolom F..W + Size)
     ============================================================ */
  function renderKw1() {
    const body = $("#kw1Table tbody");
    const foot = $("#kw1Table tfoot");
    const rows = data.kw1 || [];
    const t = { material: 0, mp: 0, foh: 0, bahan: 0, cons: 0, koor: 0, scrap: 0, total: 0 };

    body.innerHTML = rows.length ? rows.map((r, i) => {
      const c = costPerPcs(r);
      const q = r.qty || 0;
      const tMaterial = c.material * q, tMp = c.manPower * q, tFoh = c.foh * q,
            tBahan = c.bahan * q, tCons = c.cons * q, tKoor = c.koor * q, tScrap = c.scrap * q;
      const fgValueTotal = c.fgPerPcs * q;
      t.material += tMaterial; t.mp += tMp; t.foh += tFoh; t.bahan += tBahan;
      t.cons += tCons; t.koor += tKoor; t.scrap += tScrap; t.total += fgValueTotal;
      return `<tr>
        ${td(i + 1)}${td(r.produk + (r.kind === "Bebas" ? ' <span class="src-tag src-tag--wip">Bebas</span>' : ""))}
        ${tdn(kg(r.totalWeight))}${tdn(kg(r.beratPcs))}${tdn(kg(r.size))}${tdn(q)}${tdn(rp(c.cb))}
        ${tdn(rp(c.material))}${tdn(rp(c.manPower))}${tdn(rp(c.foh))}${tdn(rp(c.bahan))}
        ${tdn(rp(c.cons))}${tdn(rp(c.koor))}${tdn(rp(c.scrap))}${tdn(rp(c.fgPerPcs))}
        ${tdn(rp(tMaterial), "grp")}${tdn(rp(tMp), "grp")}${tdn(rp(tFoh), "grp")}${tdn(rp(tBahan), "grp")}
        ${tdn(rp(tCons), "grp")}${tdn(rp(tKoor), "grp")}${tdn(rp(tScrap), "grp")}${tdn(rp(fgValueTotal), "grp grp--total")}
      </tr>`;
    }).join("") : `<tr><td class="empty" colspan="23">Tidak ada data KW 1 / Stok Bebas.</td></tr>`;

    foot.innerHTML = rows.length ? `<tr>
      <td class="lbl" colspan="15">Total Biaya Finish Good (Rp) →</td>
      ${tdn(rp(t.material), "grp")}${tdn(rp(t.mp), "grp")}${tdn(rp(t.foh), "grp")}${tdn(rp(t.bahan), "grp")}
      ${tdn(rp(t.cons), "grp")}${tdn(rp(t.koor), "grp")}${tdn(rp(t.scrap), "grp")}${tdn(rp(t.total), "grp grp--total")}
    </tr>` : "";
    return t;
  }

  /* ============================================================
     TABEL 2 — HPP KW2 (komponen penuh + Inventory 80% / Loss 20%)
     ============================================================ */
  function renderKw2(rows, tableId) {
    rows = rows || [];
    const body = $(`#${tableId} tbody`);
    const foot = $(`#${tableId} tfoot`);
    let sumInv = 0, sumLoss = 0;
    const t = { material: 0, mp: 0, foh: 0, bahan: 0, cons: 0, koor: 0, scrap: 0, total: 0 };

    body.innerHTML = rows.length ? rows.map((r, i) => {
      const c = costPerPcs(r);
      const q = r.qty || 0;
      const tMaterial = c.material * q, tMp = c.manPower * q, tFoh = c.foh * q,
            tBahan = c.bahan * q, tCons = c.cons * q, tKoor = c.koor * q, tScrap = c.scrap * q;
      const fgValueTotal = c.fgPerPcs * q;
      const inv = fgValueTotal * 0.8;   // Excel kolom X
      const loss = fgValueTotal * 0.2;  // Excel kolom Y
      t.material += tMaterial; t.mp += tMp; t.foh += tFoh; t.bahan += tBahan;
      t.cons += tCons; t.koor += tKoor; t.scrap += tScrap; t.total += fgValueTotal;
      sumInv += inv; sumLoss += loss;
      return `<tr>
        ${td(i + 1)}${td(r.produk)}
        ${tdn(kg(r.totalWeight))}${tdn(kg(r.beratPcs))}${tdn(kg(r.size))}${tdn(q)}${tdn(rp(c.cb))}
        ${tdn(rp(c.material))}${tdn(rp(c.manPower))}${tdn(rp(c.foh))}${tdn(rp(c.bahan))}
        ${tdn(rp(c.cons))}${tdn(rp(c.koor))}${tdn(rp(c.scrap))}${tdn(rp(c.fgPerPcs))}
        ${tdn(rp(tMaterial), "grp")}${tdn(rp(tMp), "grp")}${tdn(rp(tFoh), "grp")}${tdn(rp(tBahan), "grp")}
        ${tdn(rp(tCons), "grp")}${tdn(rp(tKoor), "grp")}${tdn(rp(tScrap), "grp")}${tdn(rp(fgValueTotal), "grp grp--total")}
        ${tdn(rp(inv), "grp")}${tdn(rp(loss), "grp")}
      </tr>`;
    }).join("") : `<tr><td class="empty" colspan="25">Tidak ada data.</td></tr>`;

    foot.innerHTML = rows.length ? `<tr>
      <td class="lbl" colspan="15">Total Biaya Finish Good (Rp) →</td>
      ${tdn(rp(t.material), "grp")}${tdn(rp(t.mp), "grp")}${tdn(rp(t.foh), "grp")}${tdn(rp(t.bahan), "grp")}
      ${tdn(rp(t.cons), "grp")}${tdn(rp(t.koor), "grp")}${tdn(rp(t.scrap), "grp")}${tdn(rp(t.total), "grp grp--total")}
      ${tdn(rp(sumInv), "grp")}${tdn(rp(sumLoss), "grp")}
    </tr>` : "";
    return { t: t, total: t.total, inv: sumInv, loss: sumLoss };
  }

  /* ============================================================
     TABEL 3 — Scrap (Inventory 40% / Beban FOH 60%)
     ============================================================ */
  function renderScrap() {
    const body = $("#scrapTable tbody");
    const foot = $("#scrapTable tfoot");
    const rows = data.scrap || [];
    let sumNilai = 0, sumInv = 0, sumFoh = 0;

    body.innerHTML = rows.length ? rows.map((r, i) => {
      const cb = cbRate(r.material, r.gudang);
      const nilai = r.weight * cb;
      const inv = nilai * 0.4;
      const foh = nilai * 0.6;
      sumNilai += nilai; sumInv += inv; sumFoh += foh;
      return `<tr>
        ${td(i + 1)}${td(r.item)}
        ${tdn(kg(r.weight))}${tdn(rp(cb))}${tdn(rp(nilai))}
        ${tdn(rp(inv), "grp")}${tdn(rp(foh), "grp")}
      </tr>`;
    }).join("") : `<tr><td class="empty" colspan="7">Tidak ada data scrap.</td></tr>`;

    foot.innerHTML = rows.length ? `<tr>
      <td class="lbl" colspan="4">Total →</td>
      ${tdn(rp(sumNilai))}${tdn(rp(sumInv), "grp")}${tdn(rp(sumFoh), "grp")}
    </tr>` : "";
    return { nilai: sumNilai, inv: sumInv, foh: sumFoh };
  }

  /* ============================================================
     TABEL 4-6 — Sisa / Hold / Selisih (Inventory full)
     ============================================================ */
  function renderFullValue(rows, tableId) {
    rows = rows || [];
    const body = $(`#${tableId} tbody`);
    const foot = $(`#${tableId} tfoot`);
    let total = 0;
    body.innerHTML = rows.length ? rows.map((r, i) => {
      const cb = cbRate(r.material, r.gudang);
      const val = r.weight * cb;
      total += val;
      return `<tr>
        ${td(i + 1)}${td(r.item)}
        ${tdn(kg(r.weight))}${tdn(rp(cb))}${tdn(rp(val), "grp--total")}
      </tr>`;
    }).join("") : `<tr><td class="empty" colspan="5">— tidak ada —</td></tr>`;
    foot.innerHTML = rows.length ? `<tr><td class="lbl" colspan="4">Total →</td>${tdn(rp(total), "grp--total")}</tr>` : "";
    return total;
  }

  /* ============================================================
     TOTAL & JURNAL (ringkasan nilai — jurnal Favorable/UnFavorable di luar lingkup)
     ============================================================ */
  /* ============================================================
     JURNAL PRODUKSI (GL Interface) — konsep "Tampilan Jurnal Produksi" baris 160–189
     ============================================================ */
  function renderJournal(t1, kw2i, kw2s, scrap, sisaVal, holdVal, selisihVal) {
    // Komponen KW1 + KW2 (Internal + Supplier) digabung.
    const k2 = kw2i.t, k3 = kw2s.t;
    const comp = (key) => (t1[key] || 0) + (k2[key] || 0) + (k3[key] || 0);
    const material = comp("material");
    const manPower = comp("mp");
    const foh = comp("foh");
    const bahan = comp("bahan");
    const cons = comp("cons");
    const koor = comp("koor");
    const scrapPc = comp("scrap");
    const kw2Loss = kw2i.loss + kw2s.loss;
    const fgTotal = t1.total + kw2i.total + kw2s.total;           // Total FG KW1 & KW2
    const cogmTotal = material + manPower + foh + bahan + cons + koor + scrapPc; // Material..Scrap

    // Nilai material coil per gudang asal (Nett × Costbook material gudang asal)
    const coils = data.coils || [];
    const coilVal = (pred) => coils.filter(pred).reduce((s, c) => s + (c.nett || 0) * cbRate(c.material, c.source), 0);
    const matUnpackHold = coilVal((c) => c.source === "unpack" || c.source === "hold");
    const matWip = coilVal((c) => c.source === "wip");

    // Baris jurnal: [COA, Nama, Formula, Debit, Kredit]
    const G = (label) => ({ group: label });
    const R = (coa, nama, formula, debit, kredit) => ({ coa, nama, formula, debit: debit || 0, kredit: kredit || 0 });

    const rows = [
      G("COGM"),
      R("5101-01-01", "COGM MATERIAL", "Material KW 1 & KW 2", material, 0),
      R("5101-02-01", "COGM LABOUR COST PC", "Labour cost FG KW1 & KW2", manPower, 0),
      R("5101-05-01", "COGM FACTORY OVERHEAD PC", "Factory Overhead FG KW1 & KW2", foh, 0),
      R("5101-03-01", "COGM BAHAN PENDUKUNG KHUSUS PC", "Bahan Pendukung Khusus FG KW1 & KW2", bahan, 0),
      R("5101-04-01", "COGM CONSUMABLE PC", "Consumables FG KW1 & KW2", cons, 0),
      R("5101-06-01", "COGM BIAYA KOORDINASI PC", "Biaya Koordinasi FG KW1 & KW2", koor, 0),
      R("5101-07-01", "COGM BIAYA SCRAP PC", "Biaya Scrap FG KW1 & KW2", scrapPc, 0),
      R("", "COGM Loss KW 2", "Total Biaya Loss KW 2 (20%) — internal & supplier", 0, kw2Loss),

      G("Inventory"),
      R("1105-03-01", "PERSEDIAN BARANG WASTE", "40% dari Scrap (semua kategori)", scrap.inv, 0),
      R("1105-05-02", "WIP MANUFACTURING", "Sisa Material dikembalikan ke WIP Manufacturing", sisaVal, 0),
      R("1105-05-02", "WIP MANUFACTURING", "Material yang dipakai produksi (keluar dari WIP)", 0, matWip),
      R("1105-01-01", "PERSEDIAN BAHAN BAKU PRODUKSI", "Nett Weight Material Unpack/Hold × Costbook", 0, matUnpackHold),

      G("Biaya Aktual Based On Standard Costing (diCrossing)"),
      R("5205-01-01", "BIAYA SCRAP", "60% dari Scrap (semua kategori)", scrap.foh, 0),
      R("", "Biaya KW 2 (20%)", "Total Biaya Loss KW 2 (20%) — internal & supplier", kw2Loss, 0),

      G("Biaya Loss/Profit Akibat Selisih Material"),
      R("7201-01-03", "B. SELISIH STOCK AUDIT", "Selisih timbang berat tercatat vs aktual" + (selisihVal < 0 ? " (favorable)" : ""),
        selisihVal >= 0 ? selisihVal : 0,
        selisihVal < 0 ? -selisihVal : 0),

      G("Hutang yang akan diCrossing di akhir bulan"),
      R("2107-01-01", "HUTANG LABOUR COST PC", "Man Power KW 1 & KW 2", 0, manPower),
      R("2107-01-04", "HUTANG FACTORY OVERHEAD PC", "FOH KW 1 & KW 2", 0, foh),
      R("2107-01-02", "HUTANG BAHAN PENDUKUNG PC", "Bahan Pendukung Khusus KW 1 & KW 2", 0, bahan),
      R("2107-01-03", "HUTANG CONSOMABLE PC", "Consumables KW 1 & KW 2", 0, cons),
      R("2107-01-05", "HUTANG BIAYA KOORDINASI PC", "Biaya Koordinasi KW 1 & KW 2", 0, koor),
      R("2107-01-06", "HUTANG BIAYA SCRAP PC", "Biaya Scrap KW 1 & KW 2", 0, scrapPc),

      G("Cek Balancing HPP vs COGM"),
      R("1106-03-01", "INVENTORY FINISHED GOODS", "Total Nilai Finish Good KW 1 & KW 2", fgTotal, 0),
      R("5103-01-01", "COST OF GOOD MANUFACTURING", "Total Komponen COGM (Material s.d. Scrap)", 0, cogmTotal),
    ];

    const body = $("#journalTable tbody");
    let totDebit = 0, totKredit = 0;
    body.innerHTML = rows.map((r) => {
      if (r.group) {
        return `<tr class="jgroup"><td colspan="5"><strong>${r.group}</strong></td></tr>`;
      }
      totDebit += r.debit; totKredit += r.kredit;
      return `<tr>
        ${td(r.coa || "—")}${td(r.nama)}${td(`<small>${r.formula}</small>`)}
        ${tdn(r.debit ? rp(r.debit) : "—")}${tdn(r.kredit ? rp(r.kredit) : "—")}
      </tr>`;
    }).join("");

    // Keseimbangan utama: total Debit = total Kredit seluruh voucher.
    const balanced = Math.round(totDebit) === Math.round(totKredit);
    // Cek sekunder (konsep Excel): Inventory Finished Goods (FG) = Total Komponen COGM.
    const cekBalanced = Math.round(fgTotal) === Math.round(cogmTotal);
    $("#journalTable tfoot").innerHTML = `<tr>
      <td class="lbl ${cekBalanced ? "balanced" : "unbalanced"}" colspan="3">Cek Balancing HPP vs COGM (FG = COGM) ${cekBalanced ? "SEIMBANG ✓" : "TIDAK SEIMBANG"}</td>
      ${tdn(rp(fgTotal))}${tdn(rp(cogmTotal))}
    </tr>
    <tr>
      <td class="lbl ${balanced ? "balanced" : "unbalanced"}" colspan="3">Total Debit / Kredit ${balanced ? "SEIMBANG ✓" : "TIDAK SEIMBANG"}</td>
      ${tdn(rp(totDebit))}${tdn(rp(totKredit))}
    </tr>`;

    const diff = Math.round(totDebit - totKredit);
    $("#grandTotal").textContent = balanced ? "Jurnal Seimbang ✓" : ("Selisih " + rp(Math.abs(diff)));
    $("#balanceLine").classList.toggle("bad", !balanced);
  }

  /* ============================================================
     TOAST + INIT
     ============================================================ */
  let toastTimer = null;
  function toast(msg, kind = "") {
    const t = $("#toast"); t.textContent = msg;
    t.className = "toast" + (kind ? " toast--" + kind : ""); t.hidden = false;
    clearTimeout(toastTimer); toastTimer = setTimeout(() => (t.hidden = true), 2600);
  }

  function renderMeta() {
    const m = data.meta || {};
    $("#mSpk").textContent = m.spk || (spk ? spk.no : "—");
    $("#mTgl").textContent = m.tgl || (spk ? spk.tgl : "—");
    $("#mMesin").textContent = m.mesin || "—";
    $("#mReport").textContent = m.report || "—";
  }

  function init() {
    if (!spk) { window.location.replace("beranda.html"); return; }
    renderMeta();

    const t1 = renderKw1();
    const kw2i = renderKw2(data.kw2Internal, "kw2InternalTable");
    const kw2s = renderKw2(data.kw2Supplier, "kw2SupplierTable");
    const scrap = renderScrap();
    const sisaVal = renderFullValue(data.sisa, "sisaTable");
    const holdVal = renderFullValue(data.hold, "holdTable");
    const selisihVal = renderFullValue(data.selisih, "selisihTable");
    renderJournal(t1, kw2i, kw2s, scrap, sisaVal, holdVal, selisihVal);

    if (!snap) {
      toast("Belum ada hasil Laporan Produksi tersimpan untuk SPK ini. Isi & Save di Input Produksi.", "warn");
    }

    $("#btnPost").addEventListener("click", () => {
      toast("Jurnal Produksi diposting ke GL Interface ✓", "ok");
    });
  }

  document.addEventListener("DOMContentLoaded", init);
})();
