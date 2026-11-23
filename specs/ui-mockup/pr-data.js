/* ============================================================
   pr-data.js — Data contoh Modul Laporan Produksi (mockup). Dipakai semua halaman pr-*.html.
   Acuan modul aplikasi (read-only): application/modules/production_report (controller, model, views).
   Menggantikan mockup lama Daftar SPK (beranda), Input Produksi (index) dan HPP (hpp).
   Diekspos sebagai window.WH (nama sama dengan wh-data.js supaya wh-core.js bisa dipakai ulang;
   halaman pr-* tidak memuat wh-data.js). State di localStorage (kunci mockPR.data1).
   Angka hanya contoh, bukan data klien.
   ============================================================ */
(() => {
  "use strict";
  const KEY = "mockPR.data1";
  const deep = (o) => JSON.parse(JSON.stringify(o));
  const round2 = (n) => Math.round((Number(n) || 0) * 100) / 100;
  const TOLERANCE = 0.7;                                   // ± 0,7% (form.php: var TOLERANCE)
  const today = () => new Date().toISOString().slice(0, 10);

  /* ---------- Master (get_machines, get_employees, get_all_products) ---------- */
  const MACHINES = [
    { id: 1, kd_asset: "MSN-RF-01", nm_asset: "Roll Forming Spandek 1" },
    { id: 2, kd_asset: "MSN-RF-02", nm_asset: "Roll Forming Bondek" },
    { id: 3, kd_asset: "MSN-HL-01", nm_asset: "Mesin Hollow 1" },
  ];
  const HELPERS = ["Rudi H.", "Eko P.", "Slamet R.", "Tono W."];
  const SETTERS = ["Dedi S.", "Bambang T.", "Agus M."];
  // product_lvl_4: weight = berat standar/pcs, panjang (m), std_per_meter dari ms_bom_detail.berat_per_meter
  const PRODUCTS = [
    { id: 101, code_lv4: "PRD-0101", nama: "Spandek 0.30 x 1000", weight: 24.0, panjang: 2.4, std_per_meter: 10.0, no_uniq_id: "Type 001.1" },
    { id: 102, code_lv4: "PRD-0102", nama: "Spandek 0.35 x 1000", weight: 34.6, panjang: 3.0, std_per_meter: 11.5333, no_uniq_id: "Type 001.1" },
    { id: 103, code_lv4: "PRD-0103", nama: "Bondek 0.75 x 1000", weight: 18.5, panjang: 2.4, std_per_meter: 7.7083, no_uniq_id: "Type 001.1" },
    { id: 104, code_lv4: "PRD-0104", nama: "Hollow 40 x 40", weight: 31.2, panjang: 3.0, std_per_meter: 10.4, no_uniq_id: "Type 001.1" },
    { id: 105, code_lv4: "PRD-0105", nama: "Reng 0.45", weight: 4.8, panjang: 4.0, std_per_meter: 1.2, no_uniq_id: "Type 001.1" },
  ];
  // Rate Rp/Kg Hasil Costing varian BOM (product_costing_detail, Update Costing terakhir) — FR-48
  const RATES = {
    101: { man_power: 410, foh: 310, bahan_pendukung: 520, consumables: 210, koordinasi: 205, scrap: 105 },
    102: { man_power: 395, foh: 300, bahan_pendukung: 505, consumables: 200, koordinasi: 200, scrap: 100 },
    103: { man_power: 420, foh: 320, bahan_pendukung: 540, consumables: 215, koordinasi: 210, scrap: 110 },
    104: { man_power: 450, foh: 340, bahan_pendukung: 560, consumables: 230, koordinasi: 220, scrap: 115 },
    105: { man_power: 380, foh: 290, bahan_pendukung: 480, consumables: 190, koordinasi: 190, scrap: 95 },
  };
  const prodBy = (id) => PRODUCTS.find((p) => String(p.id) === String(id)) || null;

  /* ---------- SPK (tr_spk_material_header / detail). Hanya status Released yang muncul di daftar ---------- */
  const spkD = (no, urut, pid, qty) => { const p = prodBy(pid); return { id_tr_spk_detail: Number(no.slice(-4)) * 10 + urut, urut, id_product_lvl_4: pid, nm_produk_fg: p.nama, no_uniq_id: p.no_uniq_id, target_qty: qty, weight_standard: p.weight, panjang: p.panjang, std_per_meter: p.std_per_meter }; };
  const SPKS = [
    { spk_no: "SPK-2611-0001", tgl_spk: "2026-11-02", status: "Released", catatan: "Prioritas order proyek", products: [spkD("SPK-2611-0001", 1, 101, 100)] },
    { spk_no: "SPK-2611-0002", tgl_spk: "2026-11-03", status: "Released", catatan: "", products: [spkD("SPK-2611-0002", 1, 102, 60), spkD("SPK-2611-0002", 2, 103, 80)] },
    { spk_no: "SPK-2611-0003", tgl_spk: "2026-11-04", status: "Released", catatan: "Coil sempat ditahan QC", products: [spkD("SPK-2611-0003", 1, 104, 40)] },
    { spk_no: "SPK-2611-0004", tgl_spk: "2026-11-01", status: "Released", catatan: "", products: [spkD("SPK-2611-0004", 1, 101, 120)] },
    { spk_no: "SPK-2611-0005", tgl_spk: "2026-11-05", status: "Released", catatan: "Stok toko", products: [spkD("SPK-2611-0005", 1, 105, 200)] },
    { spk_no: "SPK-2611-0006", tgl_spk: "2026-11-06", status: "Material Confirmed", catatan: "Belum Released (ubah di Request List)", products: [spkD("SPK-2611-0006", 1, 101, 50)] },
  ];

  /* ---------- Stok baby coil per sumber (warehouse_stock_coil) ---------- */
  const SUP = ["PT Sumber Baja Perkasa", "PT Nusa Steel Coil", "PT Bluescope Lysaght"];
  const mat = (t, w, c, tn) => `GALVALUME SLITTED COIL NON BORON ${t}-${w}-${c} - ${tn} - SILVER`;
  const coil = (id, no, material, nett, kulit, clamp, meter, harga, sup, po) => ({ id, no_coil: no, nm_material: material, net_weight: nett, gross_weight: round2(nett + kulit + clamp + 15), total_meter: meter, berat_kulit: kulit, berat_clamp: clamp, harga_beli: harga, nm_supplier: sup, no_po: po, price_per_unit_po: harga - 150 });
  const COILS = {
    unpack: [
      coil(501, "BC-2611-001-A", mat("0.30", "1000", "AZ100", "G550"), 1850.0, 12.5, 4.2, 480, 15400, SUP[0], "PO-2610-014"),
      coil(502, "BC-2611-001-B", mat("0.30", "1000", "AZ100", "G550"), 1620.0, 11.0, 4.0, 420, 15400, SUP[0], "PO-2610-014"),
      coil(503, "BC-2611-002-A", mat("0.35", "1000", "AZ100", "G550"), 1480.0, 10.5, 3.8, 360, 16050, SUP[1], "PO-2610-021"),
      coil(504, "BC-2611-002-B", mat("0.35", "1000", "AZ100", "G550"), 1390.0, 10.0, 3.6, 340, 16050, SUP[1], "PO-2610-021"),
      coil(505, "BC-2611-003-A", mat("0.75", "1000", "Z120", "G300"), 1210.0, 9.5, 3.5, 300, 14400, SUP[2], "PO-2610-027"),
      coil(506, "BC-2611-004-A", mat("0.45", "305", "Z80", "G300"), 980.0, 8.0, 3.0, 260, 14550, SUP[2], "PO-2610-030"),
      coil(507, "BC-2611-005-A", mat("0.40", "610", "AZ150", "G550"), 1100.0, 9.0, 3.2, 280, 15400, SUP[0], "PO-2610-033"),
    ],
    wip: [
      coil(601, "BC-2610-009-A-WIP", mat("0.30", "1000", "AZ100", "G550"), 210.5, 0, 0, 55, 15400, SUP[0], "PO-2610-010"),
      coil(602, "BC-2610-011-B-WIP", mat("0.35", "1000", "AZ100", "G550"), 305.0, 0, 0, 80, 16050, SUP[1], "PO-2610-012"),
    ],
    hold: [
      coil(701, "BC-2610-015-H", mat("0.40", "610", "AZ150", "G550"), 420.0, 0, 0, 110, 15400, SUP[0], "PO-2610-018"),
    ],
  };
  const SRC_LABEL = { unpack: "Gudang Unpack", wip: "Gudang WIP", hold: "Gudang Hold" };
  const coilByCode = (code) => { for (const k of Object.keys(COILS)) { const c = COILS[k].concat((state && state.extraCoils && state.extraCoils[k]) || []).find((x) => x.no_coil === code); if (c) return c; } return null; };

  /* ---------- Bentuk laporan (sama dengan state form.php) ---------- */
  let seqUid = 0;
  const uid = () => "r" + Date.now().toString(36) + (++seqUid);
  const emptyReport = (spk_no) => ({
    id: null, spk_no, status_draft: 1, tgl_produksi: today(), id_asset_machine: "", helpers: [], setter: "", start_time: "", finished_time: "",
    coils: [], fg: [], kw2: { internal: [], supplier: [] }, sisa: [], hold: [],
    scrap: { tong: 0, wrapping: 0, pisau: 0, rejProdInt: 0, rejMatInt: 0, rejKetInt: "", rejProdSup: 0, rejMatSup: 0, rejKetSup: "" },
    claimIn: {}, claims: [], confirmations: [], hpp: null, created_at: null,
  });
  const cRow = (source, code) => { const c = coilByCode(code); return { id: uid(), source, id_warehouse_stock_coil: c.id, code: c.no_coil, material: c.nm_material, nett: c.net_weight, gross: c.gross_weight, meter: c.total_meter, kulit: c.berat_kulit, clamp: c.berat_clamp }; };
  const fgRow = (kind, pid, coil, qty, total, spkProd) => { const p = prodBy(pid); return { id: uid(), kind, productId: pid, productName: p.nama, targetQty: spkProd ? spkProd.target_qty : null, method: 1, qty, total, perPcs: 0, std: 0, selisih: 0, coil }; };
  const kw2Row = (pid, coil, size, qty, total, ket) => ({ id: uid(), productId: pid, coil, size, qty, total, ket: ket || "" });

  /* ---------- Hitungan (form.php: computeFg / recalc) ---------- */
  function computeFg(f, spk) {
    const p = prodBy(f.productId);
    if (f.kind === "bebas") { f.std = p ? p.weight : 0; f.panjang = p ? p.panjang : 0; f.stdPerMeter = p ? p.std_per_meter : 0; f.productName = p ? p.nama : ""; }
    else {
      const sp = spk && spk.products.find((x) => String(x.id_product_lvl_4) === String(f.productId));
      if (sp) { f.std = sp.weight_standard; f.targetQty = sp.target_qty; f.productName = sp.nm_produk_fg; f.panjang = sp.panjang; f.stdPerMeter = sp.std_per_meter; }
    }
    f.qty = Number(f.qty) || 0;
    if (f.method === 1) { f.total = Number(f.total) || 0; f.perPcs = f.qty > 0 ? f.total / f.qty : 0; }
    else { f.perPcs = Number(f.perPcs) || 0; f.total = f.qty * f.perPcs; }
    f.selisih = f.std > 0 ? ((f.perPcs - f.std) / f.std) * 100 : 0;
    f.beratPerMeterAktual = f.panjang > 0 ? f.perPcs / f.panjang : 0;
    f.selisihMeter = f.stdPerMeter > 0 ? ((f.beratPerMeterAktual - f.stdPerMeter) / f.stdPerMeter) * 100 : 0;
  }
  function recalc(rep, spk) {
    rep.fg.forEach((f) => computeFg(f, spk));
    const sum = (a, k) => a.reduce((t, r) => t + (Number(r[k]) || 0), 0);
    const s = rep.scrap;
    const fgKw1 = sum(rep.fg.filter((f) => f.kind === "kw1"), "total"), fgBebas = sum(rep.fg.filter((f) => f.kind === "bebas"), "total");
    const kw2Total = sum(rep.kw2.internal, "total") + sum(rep.kw2.supplier, "total");
    const scrap = ["tong", "wrapping", "pisau", "rejProdInt", "rejMatInt", "rejProdSup", "rejMatSup"].reduce((t, k) => t + (Number(s[k]) || 0), 0);
    const sisaTotal = sum(rep.sisa, "berat"), holdTotal = sum(rep.hold, "berat");
    const netProd = fgKw1 + fgBebas + kw2Total + scrap + sisaTotal + holdTotal;
    const netPack = sum(rep.coils, "nett");
    const selisihKg = netProd - netPack, selisihPct = netProd > 0 ? (selisihKg / netProd) * 100 : 0;
    return { fgTotal: fgKw1 + fgBebas, kw2Total, scrap, sisaTotal, holdTotal, netProd, netPack, selisihKg, selisihPct, over: Math.abs(selisihPct) > TOLERANCE && netProd > 0 };
  }

  /* ---------- Baris Claim Material otomatis (form.php: claimRows) ---------- */
  function claimMeta(rep, code) {
    const c = rep.coils.find((x) => x.code === code); if (!c) return null;
    const m = coilByCode(code) || {};
    return { id_baby_coil: c.id_warehouse_stock_coil, nm_material: c.material, nm_supplier: m.nm_supplier || "-", no_po: m.no_po || "-", no_coil_packing_list: code, harga_beli_per_kg: m.harga_beli || 0, costbook_per_kg: m.harga_beli || 0, price_per_unit_po: m.price_per_unit_po || 0 };
  }
  function claimRows(rep) {
    const rows = [], ci = rep.claimIn;
    const saved = (key) => rep.claims.find((c) => c.key === key);
    const base = (key, asal, coilCode, fixed, berat, dProb, dAls, existingDefaults) => {
      const e = ci[key] || {}, sv = saved(key);
      const status = sv ? sv.status_claim : "Dibuat saat Save";
      return { key, asal, coilCode, fixed, berat_kg: berat, problem: e.problem !== undefined ? e.problem : (existingDefaults ? dProb : ""), alasan: e.alasan !== undefined ? e.alasan : (existingDefaults ? dAls : ""), bukti: e.bukti || (sv ? sv.bukti : []) || [], status, lock: !!sv && sv.status_claim !== "Claim Baru" };
    };
    rep.hold.forEach((h) => { const b = Number(h.berat) || 0, c = h.coil ? String(h.coil).trim() : ""; if (c && b > 0) rows.push(base("Hold Coil:" + c, "Hold Coil", c, true, b, "Hold Coil", "Material di-hold pada lini produksi", false)); });
    const grp = {};
    rep.kw2.supplier.forEach((r) => { const t = Number(r.total) || 0, c = r.coil ? String(r.coil).trim() : ""; if (c && t > 0) { const g = grp[c] || (grp[c] = { berat: 0, kets: [] }); g.berat += t; if (r.ket && r.ket.trim()) g.kets.push(r.ket.trim()); } });
    Object.keys(grp).forEach((c) => { const g = grp[c]; rows.push(base("KW 2 (Supplier):" + c, "KW 2 (Supplier)", c, true, g.berat, "KW 2", g.kets.length ? g.kets.join("; ") : "Hasil KW 2 cacat dari supplier", true)); });
    const act = rep.coils.filter((c) => c.code), auto = act.length === 1 ? act[0].code : "";
    const s = rep.scrap;
    [["Reject Product", Number(s.rejProdSup) || 0, "Reject produk cacat dari supplier"], ["Reject Material", Number(s.rejMatSup) || 0, "Reject material plat cacat dari supplier"]].forEach(([nm, b, dAls]) => {
      if (b > 0) { const key = "Reject (Supplier):" + nm, e = ci[key] || {}; const r = base(key, "Reject (Supplier)", e.coilCode || auto, false, b, nm, (s.rejKetSup || "").trim() || dAls, true); rows.push(r); }
    });
    return rows;
  }

  /* ---------- HPP (Production_report_model::save_production_report, langkah 3-6) ---------- */
  const NAMA_SCRAP = { tong_coil: "TONG COIL", wrapping: "WRAPPING", potongan_pisau: "POTONGAN PISAU", reject_product_internal: "REJECT PRODUCT INTERNAL", reject_material_internal: "REJECT MATERIAL INTERNAL", reject_product_supplier: "REJECT PRODUCT SUPPLIER", reject_material_supplier: "REJECT MATERIAL SUPPLIER", sisa_coil: "SISA COIL", hold_coil: "HOLD COIL" };
  function computeHpp(rep, spk) {
    const r = recalc(rep, spk);
    const cbOf = {}; rep.coils.forEach((c) => { const m = coilByCode(c.code); cbOf[c.code] = m ? m.harga_beli : 0; });
    const defaultCb = Object.values(cbOf).find((v) => v > 0) || 0;
    const cb = (code) => (code && cbOf[code] > 0 ? cbOf[code] : defaultCb);
    const items = [];
    const push = (cat, p, coilCode, qty, total, nama, ket) => {
      const pcs = qty > 0 ? total / qty : 0, rt = RATES[p.id] || RATES[101], c = cb(coilCode);
      const per = { material: pcs * c, mp: pcs * rt.man_power, foh: pcs * rt.foh, bahan: pcs * rt.bahan_pendukung, cons: pcs * rt.consumables, koor: pcs * rt.koordinasi, scrap: pcs * rt.scrap };
      const tot = {}; Object.keys(per).forEach((k) => (tot[k] = per[k] * qty));
      const fg = Object.values(tot).reduce((a, b) => a + b, 0), kw2 = cat.startsWith("kw_2");
      items.push({ category_type: cat, nama_product_custom: nama, no_uniq_id: p.no_uniq_id, source_material_coil: coilCode || "-", qty, berat_total: total, berat_per_pcs: pcs, costbook_rate: c, per, tot, total_hpp_item: fg, inventory_value_80: kw2 ? fg * 0.8 : fg, loss_cost_20: kw2 ? fg * 0.2 : 0, keterangan: ket || "" });
    };
    rep.fg.forEach((f) => { if (f.qty > 0 || f.total > 0) { const p = prodBy(f.productId); if (p) push(f.kind === "bebas" ? "stok_bebas" : "kw_1", p, f.coil, f.qty, f.total, p.nama, ""); } });
    ["internal", "supplier"].forEach((t) => rep.kw2[t].forEach((k, i) => { if (k.qty > 0 || k.total > 0) { const p = prodBy(k.productId); if (p) push("kw_2_" + t, p, k.coil, k.qty, k.total, p.nama + "-KW2-" + (t === "supplier" ? "S" : "I") + "-" + String(i + 1).padStart(2, "0"), k.ket); } }));
    const scraps = [];
    const sc = rep.scrap, addS = (type, berat, coilCode, ket) => { if (berat > 0) scraps.push({ scrap_type: type, target_coil_code: coilCode || "", berat_total: berat, keterangan: ket || "" }); };
    addS("tong_coil", Number(sc.tong) || 0); addS("wrapping", Number(sc.wrapping) || 0); addS("potongan_pisau", Number(sc.pisau) || 0);
    addS("reject_product_internal", Number(sc.rejProdInt) || 0, "", sc.rejKetInt); addS("reject_material_internal", Number(sc.rejMatInt) || 0, "", sc.rejKetInt);
    addS("reject_product_supplier", Number(sc.rejProdSup) || 0, "", sc.rejKetSup); addS("reject_material_supplier", Number(sc.rejMatSup) || 0, "", sc.rejKetSup);
    rep.sisa.forEach((x) => addS("sisa_coil", Number(x.berat) || 0, x.coil)); rep.hold.forEach((x) => addS("hold_coil", Number(x.berat) || 0, x.coil));
    let invScrap = 0, fohScrap = 0, invSisa = 0, invHold = 0;
    scraps.forEach((s) => {
      s.costbook_rate = cb(s.target_coil_code); s.total_nilai = s.berat_total * s.costbook_rate;
      if (s.scrap_type === "sisa_coil") { s.inventory_value_40 = s.total_nilai; s.foh_burden_60 = 0; invSisa += s.total_nilai; }
      else if (s.scrap_type === "hold_coil") { s.inventory_value_40 = s.total_nilai; s.foh_burden_60 = 0; invHold += s.total_nilai; }
      else { s.inventory_value_40 = s.total_nilai * 0.4; s.foh_burden_60 = s.total_nilai * 0.6; invScrap += s.inventory_value_40; fohScrap += s.foh_burden_60; }
    });
    const T = { material: 0, mp: 0, foh: 0, bahan: 0, cons: 0, koor: 0, scrap: 0 }; let invFg = 0, lossKw2 = 0, totFg = 0;
    items.forEach((i) => { Object.keys(T).forEach((k) => (T[k] += i.tot[k])); invFg += i.inventory_value_80; lossKw2 += i.loss_cost_20; totFg += i.total_hpp_item; });
    const cogm = Object.values(T).reduce((a, b) => a + b, 0);
    return { items, scraps, total_cogm: round2(cogm), total_hpp_keseluruhan: round2(totFg), total_inventory_fg: round2(invFg), total_loss_kw2: round2(lossKw2), total_inventory_scrap: round2(invScrap), total_beban_foh_scrap: round2(fohScrap), total_inventory_sisa: round2(invSisa), total_inventory_hold: round2(invHold), selisih_weight_value: round2(r.selisihKg * defaultCb), totals: T, summary: r };
  }

  /* ---------- Data awal ---------- */
  function initial() {
    const st = { reports: [], decided: {}, extraCoils: { wip: [], hold: [] }, usedCoils: [], seq: { report: 0, claim: 0 } };
    const spk = (no) => SPKS.find((s) => s.spk_no === no);
    // SPK-0004: laporan final (Done)
    const r1 = emptyReport("SPK-2611-0004"); r1.id = ++st.seq.report; r1.status_draft = 0; r1.tgl_produksi = "2026-11-07"; r1.id_asset_machine = 1; r1.helpers = ["Rudi H.", "Eko P."]; r1.setter = "Dedi S."; r1.start_time = "07:30"; r1.finished_time = "15:45"; r1.created_at = "2026-11-07 16:10";
    r1.coils = [cRow("unpack", "BC-2611-001-A")];
    r1.fg = [fgRow("kw1", 101, "BC-2611-001-A", 68, 1631.5, spk("SPK-2611-0004").products[0])];
    r1.kw2.internal = [kw2Row(101, "BC-2611-001-A", 2.4, 4, 90.0, "Penyok ringan")]; r1.kw2.supplier = [kw2Row(101, "BC-2611-001-A", 2.4, 2, 44.0, "Coating mengelupas")];
    r1.scrap = { tong: 18, wrapping: 6.5, pisau: 12.2, rejProdInt: 3.1, rejMatInt: 0, rejKetInt: "Gores", rejProdSup: 0, rejMatSup: 0, rejKetSup: "" };
    r1.sisa = [{ id: uid(), coil: "BC-2611-001-A", berat: 40 }];
    r1.claims = [{ key: "KW 2 (Supplier):BC-2611-001-A", no_claim: "CLM-2611-0001", asal: "KW 2 (Supplier)", no_coil_packing_list: "BC-2611-001-A", id_material: r1.coils[0].material, id_suplier: SUP[0], no_po: "PO-2610-014", berat_kg: 44, problem: "KW 2", alasan_produksi: "Coating mengelupas", bukti: [{ file_original_name: "foto_kw2_coating.jpg", file_size: 482000 }], status_claim: "Claim Baru" }];
    st.reports.push(r1); st.usedCoils.push("BC-2611-001-A");
    st.extraCoils.wip.push(coil(801, "BC-2611-001-A-WIP", r1.coils[0].material, 40, 0, 0, 10, 15400, SUP[0], "PO-2610-014"));
    // SPK-0002: draft biasa
    const r2 = emptyReport("SPK-2611-0002"); r2.id = ++st.seq.report; r2.tgl_produksi = "2026-11-08"; r2.id_asset_machine = 1; r2.helpers = ["Slamet R."]; r2.setter = "Bambang T."; r2.start_time = "08:00";
    r2.coils = [cRow("unpack", "BC-2611-002-A")]; r2.fg = [fgRow("kw1", 102, "BC-2611-002-A", 20, 692, spk("SPK-2611-0002").products[0])];
    st.reports.push(r2);
    // SPK-0003: draft dengan Hold Coil (claim belum diputuskan Purchasing) => On Hold
    const r3 = emptyReport("SPK-2611-0003"); r3.id = ++st.seq.report; r3.tgl_produksi = "2026-11-09"; r3.id_asset_machine = 3; r3.helpers = ["Tono W."]; r3.setter = "Agus M."; r3.start_time = "07:45";
    r3.coils = [cRow("unpack", "BC-2611-004-A")]; r3.fg = [fgRow("kw1", 104, "BC-2611-004-A", 10, 312, spk("SPK-2611-0003").products[0])];
    r3.hold = [{ id: uid(), coil: "BC-2611-004-A", berat: 50 }];
    r3.claims = [{ key: "Hold Coil:BC-2611-004-A", no_claim: "CLM-2611-0002", asal: "Hold Coil", no_coil_packing_list: "BC-2611-004-A", id_material: r3.coils[0].material, id_suplier: SUP[2], no_po: "PO-2610-030", berat_kg: 50, problem: "Karat putih", alasan_produksi: "Ditemukan karat putih di lapisan dalam coil", bukti: [{ file_original_name: "video_karat.mp4", file_size: 3100000 }], status_claim: "Claim Baru" }];
    st.reports.push(r3); st.seq.claim = 2;
    return st;
  }

  let storageOk = true, state;
  const load = () => { try { const s = JSON.parse(localStorage.getItem(KEY)); if (s && s.reports) return s; } catch (e) { storageOk = false; } return null; };
  const save = () => { try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) { storageOk = false; } };
  state = load() || initial(); save();

  /* ---------- Layanan data ---------- */
  const reportOf = (spk_no) => state.reports.filter((r) => r.spk_no === spk_no).sort((a, b) => b.id - a.id)[0] || null;
  const hasPendingHold = (rep) => { const c = rep && rep.claims.find((x) => x.asal === "Hold Coil" && !state.decided[x.no_claim]); return c ? c.no_claim : false; };
  // ajax_spk_list: status Done / On Hold / Draft / Belum Ada
  function spkList(q) {
    const rows = SPKS.filter((s) => s.status === "Released" || reportOf(s.spk_no)).filter((s) => !q || (s.spk_no + " " + s.products.map((p) => p.nm_produk_fg).join(" ")).toLowerCase().includes(q.toLowerCase()))
      .sort((a, b) => b.tgl_spk.localeCompare(a.tgl_spk) || b.spk_no.localeCompare(a.spk_no));
    return rows.map((s) => {
      const rep = reportOf(s.spk_no); let st = "Belum Ada", hold = null;
      if (rep && !rep.status_draft) st = "Done"; else if (rep) { hold = hasPendingHold(rep); st = hold ? "On Hold" : "Draft"; }
      return { spk_no: s.spk_no, tgl_spk: s.tgl_spk, catatan: s.catatan, total_target_qty: s.products.reduce((a, p) => a + p.target_qty, 0), produk_fg_summary: s.products.map((p) => `${p.nm_produk_fg} (${p.target_qty} pcs)`).join(", "), report_id: rep ? rep.id : null, report_status: st, hold_claim_no: hold };
    });
  }
  const availableCoils = (source) => {
    const used = state.reports.filter((r) => !r.status_draft).flatMap((r) => r.coils.map((c) => c.code));
    return COILS[source].concat(state.extraCoils[source] || []).filter((c) => !used.includes(c.no_coil));
  };
  const nextClaimNo = () => "CLM-2611-" + String(++state.seq.claim).padStart(4, "0");

  // Simpan laporan (production_report/save + model). Mengembalikan {status, message, id}.
  function saveReport(rep, isDraft) {
    const spk = SPKS.find((s) => s.spk_no === rep.spk_no);
    if (!spk || spk.status !== "Released") return { status: 0, message: `SPK ${rep.spk_no} berstatus "${spk ? spk.status : "-"}". Status SPK wajib Released di Request List sebelum membuat/menyimpan Laporan Produksi.` };
    const rows = claimRows(rep);
    const old = rep.id ? state.reports.find((r) => r.id === rep.id) : null;
    if (!isDraft) {
      if (rows.some((c) => c.asal === "Hold Coil")) return { status: 0, message: "Submit ditolak: Laporan memuat material Hold Coil yang harus menunggu keputusan Purchasing (FR-18, FR-98). Silakan simpan sebagai Draft terlebih dahulu." };
      const pend = old && hasPendingHold(old);
      if (pend) return { status: 0, message: `Submit ditolak: Terdapat claim Hold Coil (${pend}) yang belum diputuskan oleh Purchasing (FR-18, FR-98). Silakan tunggu keputusan sebelum men-submit laporan.` };
    }
    // claim: pertahankan yang sudah diproses, buat baru bila belum ada, hapus 'Claim Baru' yang barisnya sudah dihapus
    const keep = [];
    (old ? old.claims : []).forEach((c) => { if (!rows.some((r) => r.key === c.key) && c.status_claim === "Claim Baru") return; keep.push(c); });
    rows.forEach((r) => {
      const m = claimMeta(rep, r.coilCode) || {}, ex = keep.find((c) => c.key === r.key);
      const data = { key: r.key, asal: r.asal, no_coil_packing_list: m.no_coil_packing_list || r.coilCode, id_material: m.nm_material || "", id_suplier: m.nm_supplier || "-", no_po: m.no_po || "-", berat_kg: r.berat_kg, problem: r.problem, alasan_produksi: r.alasan, bukti: r.bukti, harga_beli_per_kg: m.harga_beli_per_kg || 0, costbook_per_kg: m.costbook_per_kg || 0, price_per_unit_po: m.price_per_unit_po || 0 };
      if (ex) { if (ex.status_claim === "Claim Baru") Object.assign(ex, data); } else keep.push(Object.assign(data, { no_claim: nextClaimNo(), status_claim: "Claim Baru" }));
    });
    const out = deep(rep); out.claims = keep; out.status_draft = isDraft ? 1 : 0; out.claimIn = rep.claimIn; out.created_at = old && old.created_at ? old.created_at : today() + " " + new Date().toTimeString().slice(0, 5);
    recalc(out, spk);
    if (!isDraft) {
      out.hpp = computeHpp(out, spk);                    // dibekukan saat final (FR-48, FR-49)
      // efek stok: coil terpakai keluar, Sisa Coil -> Gudang WIP, Hold Coil -> Gudang Hold (100% nilai)
      out.sisa.forEach((x) => { const c = coilByCode(x.coil); if (c && x.berat > 0) state.extraCoils.wip.push(Object.assign({}, c, { id: 900 + state.extraCoils.wip.length + 1, no_coil: x.coil.replace(/-WIP$/, "") + "-WIP", net_weight: Number(x.berat) })); });
    }
    if (old) Object.assign(old, out); else { out.id = ++state.seq.report; state.reports.push(out); }
    save();
    return { status: 1, message: "Laporan produksi berhasil disimpan!", id: (old || out).id };
  }

  window.WH = {
    get state() { return state; }, save, storageOk: () => storageOk, reset() { state = initial(); save(); },
    TOLERANCE, MACHINES, HELPERS, SETTERS, PRODUCTS, RATES, SPKS, COILS, SRC_LABEL, SUP,
    prodBy, coilByCode, uid, today, round2, emptyReport, cRow, fgRow, kw2Row, computeFg, recalc, claimRows, claimMeta, computeHpp, NAMA_SCRAP,
    spkOf: (no) => SPKS.find((s) => s.spk_no === no) || null, reportOf, reportById: (id) => state.reports.find((r) => String(r.id) === String(id)) || null,
    hasPendingHold, spkList, availableCoils, saveReport, deep,
    decide(no_claim) { state.decided[no_claim] = true; save(); },
  };
})();
