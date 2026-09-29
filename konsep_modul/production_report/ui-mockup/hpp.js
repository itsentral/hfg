/* ============================================================
   Laporan HPP — Mockup UI/UX (vanilla JS, read-only)
   Rumus mengikuti FR-19 s.d. FR-24 / sheet "Konsep yang akan dipakai".
   ============================================================ */
(() => {
  "use strict";

  /* ---------- Konteks laporan (dummy) ---------- */
  const M = window.MOCK || null;
  const _spk = M ? M.spkByNo(M.store.spkNo) : null;
  const META = {
    spk: _spk ? _spk.no : "SPK-2026-0912",
    tgl: _spk ? _spk.tgl : "2026-09-24",
    mesin: "Slitting Line 01",
    report: "RP-2026-00841",
  };

  /* ---------- Rate Product Costing (Rp/kg) — dependency eksternal (FR-19, NFR-07) ---------- */
  const RATE = M ? M.RATE : { manPower: 400, foh: 300, bahanPendukung: 500, consumables: 200, koordinasi: 200, scrapPct: 0.02 };

  /* ---------- Costbook Material per gudang asal (Rp/kg) — FR-19/NFR-07b ---------- */
  const COSTBOOK = M ? M.COSTBOOK : { prod2: 14500, wip: 14200, hold: 13800 };

  /* ---------- Data hasil produksi (dummy, seolah dari Laporan Produksi) ---------- */
  // gudang: costbook material yang dipakai per baby coil sumber
  const KW1 = [
    { produk: "Plat Galvanis 1.0 x 1000", totalWeight: 2410.0, beratPcs: 24.1, qty: 100, gudang: "prod2", kind: "KW1" },
    { produk: "Coil Slit 300mm (Stok Bebas)", totalWeight: 600.0, beratPcs: 120.0, qty: 5, gudang: "wip", kind: "Bebas" },
  ];
  const KW2_INT = [
    { produk: "Plat Galvanis 0.8 x 1000", totalWeight: 90.0, beratPcs: 18.0, qty: 5, gudang: "prod2" },
  ];
  const KW2_SUP = [
    { produk: "Plat Galvanis 0.8 x 1000", totalWeight: 55.0, beratPcs: 18.3, qty: 3, gudang: "prod2" },
  ];
  // Scrap 7 kategori (FR-21/FR-27): total weight + gudang costbook
  const SCRAP = [
    { item: "Reject Produk Internal", weight: 12.0, gudang: "prod2" },
    { item: "Reject Material Internal", weight: 8.5, gudang: "prod2" },
    { item: "Reject Produk Supplier", weight: 6.0, gudang: "prod2" },
    { item: "Reject Material Supplier", weight: 4.0, gudang: "prod2" },
    { item: "Tong Coil", weight: 10.0, gudang: "prod2" },
    { item: "Wrapping", weight: 5.0, gudang: "prod2" },
    { item: "Potongan Pisau", weight: 15.0, gudang: "prod2" },
  ];
  const SISA = [{ item: "BC-W-2005 · Coil Galvanis 1.0mm", weight: 40.0, gudang: "wip" }];
  const HOLD = [];
  const SELISIH = [{ item: "Selisih Berat (Summary)", weight: 26.5, gudang: "prod2" }];

  /* ---------- Helpers ---------- */
  const $ = (s, r = document) => r.querySelector(s);
  const cb = (g) => COSTBOOK[g] || 0;
  const rp = (n) => "Rp " + Math.round(n).toLocaleString("id-ID");
  const kg = (n) => n.toLocaleString("id-ID", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const td = (v, cls = "") => `<td class="${cls}">${v}</td>`;
  const tdn = (v, cls = "") => `<td class="num rp ${cls}">${v}</td>`;

  /* ---------- Komponen biaya/pcs (FR-19) ---------- */
  function costPerPcs(row) {
    const material = row.beratPcs * cb(row.gudang);
    const manPower = row.beratPcs * RATE.manPower;
    const foh = row.beratPcs * RATE.foh;
    const bahan = row.beratPcs * RATE.bahanPendukung;
    const cons = row.beratPcs * RATE.consumables;
    const koor = row.beratPcs * RATE.koordinasi;
    const scrap = material * RATE.scrapPct; // 2% dari value material
    const fgPerPcs = material + manPower + foh + bahan + cons + koor + scrap;
    return { material, manPower, foh, bahan, cons, koor, scrap, fgPerPcs };
  }

  /* ============================================================
     TABEL 1 — HPP KW1 & Stok Bebas
     ============================================================ */
  function renderKw1() {
    const body = $("#kw1Table tbody");
    const foot = $("#kw1Table tfoot");
    let sumTotal = 0;
    const compTotals = { material: 0, mp: 0, foh: 0, bahan: 0, cons: 0, koor: 0, scrap: 0 };

    body.innerHTML = KW1.map((r, i) => {
      const c = costPerPcs(r);
      const fgValueTotal = c.fgPerPcs * r.qty;
      sumTotal += fgValueTotal;
      compTotals.material += c.material * r.qty;
      compTotals.mp += c.manPower * r.qty;
      compTotals.foh += c.foh * r.qty;
      compTotals.bahan += c.bahan * r.qty;
      compTotals.cons += c.cons * r.qty;
      compTotals.koor += c.koor * r.qty;
      compTotals.scrap += c.scrap * r.qty;
      return `<tr>
        ${td(i + 1)}${td(r.produk + (r.kind === "Bebas" ? ' <span class="src-tag src-tag--wip">Bebas</span>' : ""))}
        ${tdn(kg(r.totalWeight))}${tdn(kg(r.beratPcs))}${tdn(r.qty)}${tdn(rp(cb(r.gudang)))}
        ${tdn(rp(c.material))}${tdn(rp(c.manPower))}${tdn(rp(c.foh))}${tdn(rp(c.bahan))}
        ${tdn(rp(c.cons))}${tdn(rp(c.koor))}${tdn(rp(c.scrap))}${tdn(rp(c.fgPerPcs))}
        ${tdn(rp(fgValueTotal), "grp--total")}
      </tr>`;
    }).join("");

    foot.innerHTML = `<tr>
      <td class="lbl" colspan="6">Total Biaya (Rp) →</td>
      ${tdn(rp(compTotals.material))}${tdn(rp(compTotals.mp))}${tdn(rp(compTotals.foh))}${tdn(rp(compTotals.bahan))}
      ${tdn(rp(compTotals.cons))}${tdn(rp(compTotals.koor))}${tdn(rp(compTotals.scrap))}${tdn("")}
      ${tdn(rp(sumTotal), "grp--total")}
    </tr>`;
    return sumTotal;
  }

  /* ============================================================
     TABEL 2 — HPP KW2 (Internal/Supplier) + Inventory 80% / Loss 20%
     ============================================================ */
  function renderKw2(rows, tableId) {
    const body = $(`#${tableId} tbody`);
    const foot = $(`#${tableId} tfoot`);
    let sumTotal = 0, sumInv = 0, sumLoss = 0;

    body.innerHTML = rows.map((r, i) => {
      const c = costPerPcs(r);
      const fgValueTotal = c.fgPerPcs * r.qty;
      const inv = fgValueTotal * 0.8;   // FR-20
      const loss = fgValueTotal * 0.2;
      sumTotal += fgValueTotal; sumInv += inv; sumLoss += loss;
      return `<tr>
        ${td(i + 1)}${td(r.produk)}
        ${tdn(kg(r.totalWeight))}${tdn(kg(r.beratPcs))}${tdn(r.qty)}
        ${tdn(rp(c.fgPerPcs))}${tdn(rp(fgValueTotal), "grp--total")}
        ${tdn(rp(inv), "grp")}${tdn(rp(loss), "grp")}
      </tr>`;
    }).join("");

    foot.innerHTML = rows.length ? `<tr>
      <td class="lbl" colspan="6">Total →</td>
      ${tdn(rp(sumTotal), "grp--total")}${tdn(rp(sumInv), "grp")}${tdn(rp(sumLoss), "grp")}
    </tr>` : `<tr><td class="empty" colspan="9">Tidak ada data.</td></tr>`;
    return { total: sumTotal, inv: sumInv, loss: sumLoss };
  }

  /* ============================================================
     TABEL 3 — Scrap (40% Inventory / 60% Beban FOH)
     ============================================================ */
  function renderScrap() {
    const body = $("#scrapTable tbody");
    const foot = $("#scrapTable tfoot");
    let sumNilai = 0, sumInv = 0, sumFoh = 0;

    body.innerHTML = SCRAP.map((r, i) => {
      const nilai = r.weight * cb(r.gudang);
      const inv = nilai * 0.4;  // FR-21
      const foh = nilai * 0.6;
      sumNilai += nilai; sumInv += inv; sumFoh += foh;
      return `<tr>
        ${td(i + 1)}${td(r.item)}
        ${tdn(kg(r.weight))}${tdn(rp(cb(r.gudang)))}${tdn(rp(nilai))}
        ${tdn(rp(inv), "grp")}${tdn(rp(foh), "grp")}
      </tr>`;
    }).join("");

    foot.innerHTML = `<tr>
      <td class="lbl" colspan="4">Total →</td>
      ${tdn(rp(sumNilai))}${tdn(rp(sumInv), "grp")}${tdn(rp(sumFoh), "grp")}
    </tr>`;
    return { nilai: sumNilai, inv: sumInv, foh: sumFoh };
  }

  /* ============================================================
     TABEL 4 — Sisa / Hold / Selisih (full value)
     ============================================================ */
  function renderOthers() {
    const body = $("#othersTable tbody");
    const foot = $("#othersTable tfoot");
    const groups = [
      { label: "Sisa Coil", rows: SISA },
      { label: "Hold Coil", rows: HOLD },
      { label: "Selisih Berat", rows: SELISIH },
    ];
    let total = 0, html = "";
    let totSisa = 0, totHold = 0, totSelisih = 0;
    groups.forEach((g) => {
      if (!g.rows.length) {
        html += `<tr>${td(g.label)}<td class="empty" colspan="4">— tidak ada —</td></tr>`;
        return;
      }
      g.rows.forEach((r) => {
        const val = r.weight * cb(r.gudang);
        total += val;
        if (g.label === "Sisa Coil") totSisa += val;
        else if (g.label === "Hold Coil") totHold += val;
        else totSelisih += val;
        html += `<tr>
          ${td(g.label)}${td(r.item)}
          ${tdn(kg(r.weight))}${tdn(rp(cb(r.gudang)))}${tdn(rp(val), "grp--total")}
        </tr>`;
      });
    });
    body.innerHTML = html;
    foot.innerHTML = `<tr><td class="lbl" colspan="4">Total →</td>${tdn(rp(total), "grp--total")}</tr>`;
    return { total, sisa: totSisa, hold: totHold, selisih: totSelisih };
  }

  /* ============================================================
     TOTAL & JURNAL (FR-24)
     ============================================================ */
  function renderJournal(kw1Total, kw2i, kw2s, scrap, others) {
    const kw2Inv = kw2i.inv + kw2s.inv;
    const kw2Loss = kw2i.loss + kw2s.loss;

    // Ringkasan nilai
    const summary = [
      ["Finish Good KW 1 & Stok Bebas", kw1Total],
      ["KW 2 — Inventory (80%)", kw2Inv],
      ["KW 2 — Biaya Loss (20%)", kw2Loss],
      ["Scrap — Inventory (40%)", scrap.inv],
      ["Scrap — Beban FOH (60%)", scrap.foh],
      ["Sisa Coil", others.sisa],
      ["Hold Coil", others.hold],
      ["Selisih Berat", others.selisih],
    ];
    const grand = summary.reduce((s, [, v]) => s + v, 0);

    $("#jSummary").innerHTML =
      summary.map(([k, v]) => `<li><span>${k}</span><span>${rp(v)}</span></li>`).join("") +
      `<li class="total"><span>Total HPP Keseluruhan</span><span>${rp(grand)}</span></li>`;

    // Preview jurnal (debit/kredit seimbang) — kelompok dari BPMN Accounting note
    const jrn = [
      ["Persediaan Finish Good (KW1)", kw1Total, 0],
      ["Persediaan Finish Good (KW2 - 80%)", kw2Inv, 0],
      ["Beban Loss KW2 (20%)", kw2Loss, 0],
      ["Persediaan Scrap (40%)", scrap.inv, 0],
      ["Beban FOH Scrap (60%)", scrap.foh, 0],
      ["Persediaan WIP - Sisa/Hold Coil", others.sisa + others.hold, 0],
      ["Selisih Pemakaian Material", others.selisih, 0],
      ["Hutang Biaya Proses / Koordinasi", 0, grand],
    ];
    const totDebit = jrn.reduce((s, r) => s + r[1], 0);
    const totKredit = jrn.reduce((s, r) => s + r[2], 0);
    const jbody = $("#journalTable tbody");
    const jfoot = $("#journalTable tfoot");
    jbody.innerHTML = jrn.map((r) =>
      `<tr>${td(r[0])}${tdn(r[1] ? rp(r[1]) : "—")}${tdn(r[2] ? rp(r[2]) : "—")}</tr>`).join("");
    const balanced = Math.round(totDebit) === Math.round(totKredit);
    jfoot.innerHTML = `<tr>
      <td class="lbl ${balanced ? "balanced" : "unbalanced"}">${balanced ? "SEIMBANG ✓" : "TIDAK SEIMBANG"}</td>
      ${tdn(rp(totDebit))}${tdn(rp(totKredit))}
    </tr>`;

    $("#grandTotal").textContent = rp(grand);
    return grand;
  }

  /* ============================================================
     INIT
     ============================================================ */
  let toastTimer = null;
  function toast(msg, kind = "") {
    const t = $("#toast"); t.textContent = msg;
    t.className = "toast" + (kind ? " toast--" + kind : ""); t.hidden = false;
    clearTimeout(toastTimer); toastTimer = setTimeout(() => (t.hidden = true), 2600);
  }

  function init() {
    $("#mSpk").textContent = META.spk;
    $("#mTgl").textContent = META.tgl;
    $("#mMesin").textContent = META.mesin;
    $("#mReport").textContent = META.report;

    const kw1Total = renderKw1();
    const kw2i = renderKw2(KW2_INT, "kw2InternalTable");
    const kw2s = renderKw2(KW2_SUP, "kw2SupplierTable");
    const scrap = renderScrap();
    const others = renderOthers();
    renderJournal(kw1Total, kw2i, kw2s, scrap, others);

    $("#btnPost").addEventListener("click", () => {
      toast("Jurnal Produksi diposting ke GL Interface ✓", "ok");
    });
  }

  document.addEventListener("DOMContentLoaded", init);
})();
