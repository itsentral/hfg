/* ============================================================
   sk-inc.js — Mockup Subkont tahap 5: Incoming dari vendor, Temuan, Konfirmasi Hasil, Laporan Nilai Slitting, Saldo Material In Supplier.
   P.inc   = Incoming Subkont (PPIC): FR-118 (satu Incoming berisi beberapa Laporan Closed milik satu vendor), FR-119 (cek berat timbang vs LHP ±0,7%, Temuan), FR-120 (biaya jasa),
             FR-121 (Konfirmasi: baby coil/FG baru masuk gudang, scrap, jurnal, saldo, costbook, status SPK), FR-122 (jurnal), FR-123 (costbook baru)
   P.nilai = Laporan Nilai Slitting (HFG saja, tidak terlihat vendor)                                                 FR-120
   P.saldo = Saldo Material In Supplier (Accounting, baca saja; UC-N38)                                               FR-112
   Label = nama kolom ERD (tr_subkont_incoming, tr_subkont_incoming_detail, subkont_in_supplier_ledger). Kode akun sementara (OQ-S9).
   Keputusan user 2026-11-20: Incoming tanpa input berat (baby coil masih terbungkus); cek = Nett Weight Supplier (LHP Actual + Scrap) vs Packing List (Before) +/- 0,7%, FG tanpa cek berat; satu Incoming berisi beberapa Laporan; Konfirmasi boleh bila tiap laporan Sesuai atau Temuan-nya Ditutup PPIC (selisih diterima);
   detail FG dicek per produk berdasarkan berat. Asumsi mockup: laporan yang dicatat Temuan otomatis keluar dari Incoming (agar laporan lain tidak tertahan).
   ============================================================ */
(() => {
  "use strict";
  const U = window.WHUI, W = U.W, P = window.SKP;
  const { esc, num, fdate, btn, tbl, card, kv, toast, dialog, warn, done, openModal, closeModal, pv } = U;
  const S = () => W.state;
  const PPIC = "Dedi K.", TOL = 0.7;
  const ST = { Draft: "warning", "Temuan Open": "danger", Confirmed: "success", Sesuai: "success", "Tidak Sesuai": "danger", "Belum Dicek": "secondary", Open: "danger", Ditutup: "success", Kirim: "info", Terima: "success" };
  const stl = (s) => `<span class="ap-label ap-label--${ST[s] || "secondary"}">${esc(s)}</span>`;
  const field = (label, html, req, hint) => `<div class="pr-f"><label class="ap-lbl">${label}${req ? ' <span class="ap-req">*</span>' : ""}</label>${html}${hint ? `<div class="ap-hint">${hint}</div>` : ""}</div>`;
  const tx = (f, v, ph, type) => `<input class="ap-in" type="${type || "text"}" data-f="${f}" value="${esc(v)}"${ph ? ` placeholder="${esc(ph)}"` : ""}>`;
  const now = () => W.today() + " " + new Date().toTimeString().slice(0, 5);
  const r2 = (n) => Math.round(n * 100) / 100;
  const supName = (id) => (W.vendorBy(id) || { nama: "-" }).nama;
  const lapOf = (no) => S().lap.find((l) => l.no_laporan === no);
  const isMat = (l) => !!(l.packs && l.packs.length);
  const COA = { bb: ["Persediaan Bahan Baku", "1105-01-01"], bj: ["Persediaan Barang Jadi", "1106-03-01"], scrap: ["Inventory Scrap", "1105-03-01"], sel: ["Selisih Net Weight", "1936155"], mis: ["Material In Supplier", "9901-01-01"], unbill: ["Unbill (Jasa Subcont)", "9901-02-01"] };

  /* ---------- baris cek & perhitungan ---------- */
  // FR-119 (revisi 2026-11-20): TIDAK ada input berat di Incoming (baby coil masih terbungkus). Yang divalidasi = Nett Weight dari Supplier
  // (LHP: Nett Weight Actual + Total Scrap) vs Nett Weight Before di Packing List (berat kirim HFG), toleransi +/- 0,7%. SPK FG: tanpa cek berat.
  const cekLap = (l) => { if (!isMat(l)) return { m: false, status: "Sesuai" };
    const before = l.packs.reduce((a, p) => a + p.nett_weight_before, 0), supplier = r2((l.nett_weight_actual || 0) + (l.total_scrap || 0)), sel = r2(before - supplier), pct = before ? (Math.abs(sel) / before) * 100 : 0;
    return { m: true, before, actual: l.nett_weight_actual || 0, scrap: l.total_scrap || 0, supplier, sel, pct, status: pct <= TOL ? "Sesuai" : "Tidak Sesuai" }; };
  const statusOf = (it) => cekLap(lapOf(it.no_laporan)).status;
  const cekHtml = (l) => { const c = cekLap(l); return c.m ? kv([["Nett Weight Before (Packing List)", num(c.before) + " kg"], ["Nett Weight dari Supplier (Actual + Scrap)", `${num(c.actual)} + ${num(c.scrap)} = ${num(c.supplier)} kg`], ["Selisih (kg)", num(c.sel)], ["Selisih (%)", `<b>${num(c.pct, 3)}%</b> (toleransi ±${TOL}%)`], ["Status Cek", stl(c.status)]]) : kv([["Cek berat", "SPK FG: tanpa cek berat (pasir menambah berat); qty dan berat hasil mengikuti LHP"], ["Status Cek", stl(c.status)]]); };
  const items = (l) => W.dlvItems(l);
  function calc(f) {
    const l = lapOf(f.no_laporan); if (!l) return null;
    const it = items(l), m = isMat(l), harga = Number(f.harga_jasa_per_kg) || 0, lot = Number(f.nilai_jasa_lot) || 0, kg = m ? l.nett_weight_actual : l.fg.reduce((a, g) => a + g.total_berat_kg, 0);
    const biaya = r2(f.jenis_tarif_jasa === "Per Kg" ? kg * harga : lot);
    if (m) {
      const cb = it[0].costbook || W.costbook(l.kode_barang), before = l.packs.reduce((a, p) => a + p.nett_weight_before, 0), actual = l.nett_weight_actual, scrap = l.total_scrap || 0, sel = r2(before - actual - scrap);
      const nm = r2(actual * cb), nmb = r2(nm + biaya), ns = r2(scrap * cb), nsel = r2(sel * cb), cr = r2(before * cb);
      return { m, cb, before, actual, scrap, sel, kg, biaya, nm, nmb, ns, nsel, cr, baru: actual ? r2(nmb / actual) : 0, lines: [["Db", COA.bb, nmb], ["Db", COA.scrap, ns], ["Db", COA.sel, nsel], ["Cr", COA.mis, cr], ["Cr", COA.unbill, biaya]] };
    }
    const nilaiKirim = r2(it.reduce((a, x) => a + (x.nilai || x.qty * W.costbook(x.kode_barang)), 0)), qty = l.fg.reduce((a, g) => a + g.qty_pcs_hasil, 0);
    return { m, kg, biaya, nilaiKirim, qty, baruPcs: qty ? r2((nilaiKirim + biaya) / qty) : 0, baru: kg ? r2((nilaiKirim + biaya) / kg) : 0, lines: [["Db", COA.bj, r2(nilaiKirim + biaya)], ["Cr", COA.mis, nilaiKirim], ["Cr", COA.unbill, biaya]] };
  }
  const jurnalHtml = (c) => { const L = c.lines.map(([p, a, v]) => (v < 0 ? [p === "Db" ? "Cr" : "Db", a, -v] : [p, a, v])), dbT = L.filter((x) => x[0] === "Db").reduce((a, x) => a + x[2], 0), crT = L.filter((x) => x[0] === "Cr").reduce((a, x) => a + x[2], 0);
    return tbl(["Posisi", "COA", "Kode Akun (sementara)", "Debit", "Kredit"], L.filter((x) => x[2] !== 0).map(([p, a, v]) => `<tr><td>${p}</td><td>${esc(a[0])}</td><td>${esc(a[1])}</td><td class="ap-r">${p === "Db" ? num(v) : ""}</td><td class="ap-r">${p === "Cr" ? num(v) : ""}</td></tr>`).join(""), "", `<tr class="wh-foot"><td colspan="3" class="ap-r">Total / Cek Jurnal (selisih ${num(r2(dbT - crT))})</td><td class="ap-r">${num(r2(dbT))}</td><td class="ap-r">${num(r2(crT))}</td></tr>`); };
  const nilaiHtml = (c) => (c.m ? kv([["Total Weight Slitting", num(c.actual) + " kg"], ["Biaya Subkont", num(c.biaya)], ["Costbook Gudang HFG", num(c.cb) + " /kg"], ["Nilai Material Gudang", num(c.nm)], ["Nilai Material Biaya Proses", num(c.nmb)], ["Total Scrap (kg) / Nilai Scrap", `${num(c.scrap)} kg / ${num(c.ns)}`], ["Selisih (kg) / Nilai Selisih", `${num(c.sel)} kg / ${num(c.nsel)}`], ["Costbook Baru/Kg", `<b>${num(c.baru)}</b>`]])
    : kv([["Nilai FG Kirim", num(c.nilaiKirim)], ["Total Berat Hasil", num(c.kg) + " kg"], ["Biaya Subkont", num(c.biaya)], ["Qty Hasil", num(c.qty, 0) + " pcs"], ["Costbook FG Baru (fg_stock)", `<b>${num(c.baruPcs)} /pcs</b>`], ["Costbook Baru/Kg", num(c.baru)]]));

  /* ---------- Incoming = header + beberapa Laporan (it) ---------- */
  // it = {no_laporan, spk_no, no_po_jasa, jenis_tarif_jasa, harga_jasa_per_kg, nilai_jasa_lot, detail:[baris cek], (setelah Konfirmasi: biaya_subkont, costbook_*, ...)}
  const temuanDitutup = (no) => S().rev.some((r) => r.no_laporan === no && r.asal === "Temuan Incoming" && r.status === "Ditutup");
  const eligible = (it) => statusOf(it) === "Sesuai" || temuanDitutup(it.no_laporan);            // FR-119: Sesuai, atau Temuan Ditutup PPIC = selisih diterima
  const incCek = (inc) => { const a = inc.items.map(statusOf); return !a.length || a.includes("Belum Dicek") ? "Belum Dicek" : a.includes("Tidak Sesuai") ? "Tidak Sesuai" : "Sesuai"; };
  const aggLines = (its) => { const m = {}, ord = []; its.forEach((it) => calc(it).lines.forEach(([p, a, v]) => { const k = a[1]; if (!(k in m)) { m[k] = { a, v: 0 }; ord.push(k); } m[k].v += p === "Db" ? v : -v; }));
    const L = ord.map((k) => [m[k].v >= 0 ? "Db" : "Cr", m[k].a, r2(Math.abs(m[k].v))]); return L.filter((x) => x[0] === "Db").concat(L.filter((x) => x[0] === "Cr")); };

  /* ---------- status SPK setelah Konfirmasi (FR-102, FR-121) ---------- */
  function spkAfter(no) {
    const s = W.spkOf(no), dl = S().dlv.filter((d) => d.status === "Confirmed" && d.detail.some((x) => x.spk_no === no));
    const full = s.detail.every((it) => S().tamp.filter((t) => t.spk_no === no && t.kode_barang === it.kode_barang && t.status === "Terkirim").reduce((a, t) => a + t.qty, 0) >= it.qty_subkont);
    const need = []; dl.forEach((d) => { [...new Set(d.detail.filter((x) => x.spk_no === no).map((x) => x.kode_barang))].forEach((k) => need.push(d.kode_delivery + "|" + k)); });
    const got = []; S().inc.filter((i) => i.status === "Confirmed").forEach((i) => i.items.filter((it) => it.spk_no === no).forEach((it) => { const l = lapOf(it.no_laporan); got.push(l.kode_delivery + "|" + l.kode_barang); }));
    s.status = full && need.every((k) => got.includes(k)) ? "Closed" : "Incoming";
    return s.status;
  }

  /* ---------- Konfirmasi (per Incoming, seluruh Laporan di dalamnya) ---------- */
  function confirmInc(no) {
    const inc = W.incOf(no), its = inc.items, cs = its.map((it) => calc(it)), agg = aggLines(its);
    openModal({ title: "Konfirmasi Hasil Pengecekan Incoming — " + no, size: "xl", hdr: "success", ok: "Konfirmasi", okCls: "success",
      body: () => `<div class="wh-grid2"><div>${kv([["No. Incoming", esc(no)], ["Vendor", esc(supName(inc.id_vendor))], ["No. Surat Jalan Vendor", esc(inc.no_surat_jalan_vendor)]])}</div><div>${kv([["Tgl Incoming", fdate(inc.tgl_incoming)], ["Jumlah Laporan", String(its.length)], ["Status Cek", stl(incCek(inc))]])}</div></div>` +
        its.map((it, i) => `<div class="wh-sec">Laporan Nilai Slitting (HFG saja) — ${esc(it.no_laporan)} / ${esc(it.spk_no)}${temuanDitutup(it.no_laporan) && statusOf(it) !== "Sesuai" ? " (selisih diterima: temuan Ditutup)" : ""}</div>${nilaiHtml(cs[i])}`).join("") +
        `<div class="wh-sec">Jurnal Incoming (gabungan seluruh Laporan, satu gl_interface)</div>${jurnalHtml({ lines: agg })}
        <p class="note">Setelah Konfirmasi: Material = baby coil masuk <b>warehouse_stock_coil</b> Gudang Produksi 2 (PRT) dengan harga_beli = costbook baru, scrap 100% ke Inventory Scrap; FG = kode FG baru (No. Urut Subkont) masuk <b>fg_stock</b> (KW 1); jurnal terbentuk; Saldo Material In Supplier berkurang per Laporan; costbook diperbarui; status SPK maju. Kode akun sementara (OQ-S9).</p>`,
      onOk() {
        its.forEach((it, i) => { const c = cs[i], l = lapOf(it.no_laporan), m = c.m;
          Object.assign(it, m ? { biaya_subkont: c.biaya, costbook_gudang_hfg: c.cb, nilai_material_gudang: c.nm, nilai_material_biaya_proses: c.nmb, total_scrap_kg: c.scrap, nilai_scrap: c.ns, selisih_kg: c.sel, nilai_selisih: c.nsel, costbook_baru_per_kg: c.baru, costbook_baru_per_pcs: null }
            : { biaya_subkont: c.biaya, nilai_fg_kirim: c.nilaiKirim, costbook_baru_per_kg: null, costbook_baru_per_pcs: c.baruPcs, total_berat_kg: c.kg });
          { const ck = cekLap(l); it.status_cek = ck.status; if (ck.m) Object.assign(it, { nett_weight_packing_list: ck.before, nett_weight_supplier: ck.supplier, selisih_persen: r2(ck.pct) }); }
          if (m) {
            W.babies(l).forEach((b) => S().stokPRT.push({ no_coil: b.babycoil_code, kd_gudang: "PRT", net_weight: b.net_weight, harga_beli: c.baru, no_incoming: no, no_laporan: it.no_laporan, kode_barang: l.kode_barang, nama_barang: W.nama(l.kode_barang), spk_no: it.spk_no, tgl: inc.tgl_incoming }));
            if (c.scrap) S().scrapStok.push({ no_incoming: no, no_laporan: it.no_laporan, kategori: "SCRAP", berat_kg: c.scrap, nilai: c.ns, kode_barang: l.kode_barang, nama_barang: W.nama(l.kode_barang), spk_no: it.spk_no, tgl: inc.tgl_incoming });
          } else {
            l.fg.forEach((g) => { const base = W.FG_BASE[g.kode_barang_asal] || "KODE-INTERNAL", n = S().fgBaru.filter((x) => x.base === base).length + 1, kode = `${W.nama(g.kode_barang_asal)}/${base}/${n}`; g.no_urut_subkont = n; g.produk_fg_baru = kode; S().fgBaru.push({ no_incoming: no, no_laporan: it.no_laporan, base, no_urut_subkont: n, kode_fg_baru: kode, qty: g.qty_pcs_hasil, costbook_per_pcs: c.baruPcs, kw: "KW 1", nilai: r2(c.nilaiKirim + c.biaya), berat_kg: g.total_berat_kg, spk_no: it.spk_no, tgl: inc.tgl_incoming }); });
          }
          const prev = S().ledger.filter((x) => x.spk_no === it.spk_no && String(x.id_vendor) === String(inc.id_vendor)).reduce((a, x) => a + (x.jenis_mutasi === "Kirim" ? x.nilai : -x.nilai), 0), cr = m ? c.cr : c.nilaiKirim;
          S().ledger.push({ id: W.nextLed(), kode_delivery: l.kode_delivery, spk_no: it.spk_no, id_vendor: inc.id_vendor, tgl_transaksi: inc.tgl_incoming, jenis_mutasi: "Terima", qty: m ? l.packs.length : items(l).reduce((a, x) => a + x.qty, 0), net_weight: m ? c.before : 0, nilai: cr, saldo_nilai: r2(prev - cr), no_referensi: no });
        });
        inc.jurnal = agg.map((x) => [x[0], x[1][0], x[1][1], x[2]]);
        inc.status = "Confirmed"; inc.confirmed_by = PPIC; inc.confirmed_at = now();
        const sts = [...new Set(its.map((it) => it.spk_no))].map((sn) => `${sn}: ${spkAfter(sn)}`).join(", ");
        W.save(); closeModal(); U.render(); done(`Incoming ${no} dikonfirmasi (${its.length} laporan). Jurnal Incoming terbentuk (Total Rp ${num(r2(agg.filter((x) => x[0] === "Db").reduce((a, x) => a + x[2], 0)))}). Status SPK ${sts}.`);
      } });
  }

  /* ---------- form ---------- */
  let inf = null, addSel = "";
  const newForm = () => ({ no_incoming: null, id_vendor: "", no_surat_jalan_vendor: "", tgl_incoming: W.today(), items: [] });
  const used = (no) => S().inc.some((i) => i.items.some((it) => it.no_laporan === no)) || (inf && inf.items.some((it) => it.no_laporan === no));
  const availLap = (vendor) => S().lap.filter((l) => l.status === "Closed" && String(l.id_vendor) === String(vendor) && !used(l.no_laporan));
  const vendorsWithLap = () => { const ids = [...new Set(S().lap.filter((l) => l.status === "Closed" && !S().inc.some((i) => i.items.some((it) => it.no_laporan === l.no_laporan))).map((l) => String(l.id_vendor)))]; if (inf && inf.id_vendor && !ids.includes(String(inf.id_vendor))) ids.push(String(inf.id_vendor)); return ids; };
  const itemOf = (l) => ({ no_laporan: l.no_laporan, spk_no: l.spk_no, no_po_jasa: l.no_po, jenis_tarif_jasa: "Per Kg", harga_jasa_per_kg: 0, nilai_jasa_lot: 0 });
  const stBox = (it) => { const l = lapOf(it.no_laporan), c = cekLap(l), tm = temuanDitutup(it.no_laporan), st = c.status; return `<div class="wh-need${st === "Sesuai" ? " is-ok" : !tm ? " is-bad" : ""}"><b>Status Cek:</b> ${st}${st === "Tidak Sesuai" ? (tm ? " &bull; temuan Ditutup PPIC: selisih diterima, boleh dikonfirmasi" : ` &bull; selisih ${num(c.pct, 3)}% melebihi ±${TOL}%: catat Temuan (laporan dikembalikan ke vendor dan dikeluarkan dari Incoming ini)`) : ""}</div>`; };
  const jurHtml = () => (inf.items.length ? `<div class="wh-sec">Jurnal Incoming (pratinjau gabungan)</div>${jurnalHtml({ lines: aggLines(inf.items) })}` : "");
  function itemView(it, li) {
    const l = lapOf(it.no_laporan), m = isMat(l), c = calc(it);
    const baby = m ? W.babies(l) : [];
    return `<div class="wh-sec">Laporan ${li + 1}: ${esc(it.no_laporan)} &bull; ${esc(it.spk_no)} &bull; ${esc(W.nama(l.kode_barang))}</div><div class="pr-grid3">
      ${field("No. PO Jasa", tx(`po|${li}`, it.no_po_jasa, "(Input Free Text) referensi PO jasa"), 1, "Hanya referensi teks; tidak terhubung ke SPK.")}
      ${field("Jenis Tarif Jasa", `<select class="ap-sel ap-in" data-f="tarif|${li}"><option${it.jenis_tarif_jasa === "Per Kg" ? " selected" : ""}>Per Kg</option><option${it.jenis_tarif_jasa === "Lot" ? " selected" : ""}>Lot</option></select>`, 1, "Biaya jasa hanya diinput HFG; tidak terlihat vendor.")}
      ${it.jenis_tarif_jasa === "Per Kg" ? field("Harga Jasa/Kg (Rp)", U.inp(`harga|${li}`, it.harga_jasa_per_kg || "", { num: 1, ph: "mis. 270,27" }), 1) : field("Nilai Jasa Lot (Rp)", U.inp(`lot|${li}`, it.nilai_jasa_lot || "", { num: 1, ph: "total jasa" }), 1)}</div>
      <div class="wh-sec">Cek Nett Weight Supplier vs Packing List (otomatis dari LHP, tanpa input berat)</div><div id="ck${li}">${cekHtml(l)}</div><div class="cl-tools">${btn("danger", "temuan", li, "Catat Temuan")}${btn("secondary", "dellap", li, "Hapus Laporan dari Incoming")}</div>` +
      (m ? tbl(["No", "Baby Coil (dari LHP)", "Pack", "Net Weight (kg)"], baby.map((b, n) => `<tr><td>${n + 1}</td><td>${esc(b.babycoil_code)}</td><td>${esc(b.pack_code)}</td><td class="ap-r">${num(b.net_weight)}</td></tr>`).join(""), "") : tbl(["No", "Produk FG Asal", "Qty Pcs Asal", "Qty Pcs Hasil", "Total Berat Hasil (kg)", "Produk FG Baru (pratinjau)"], l.fg.map((g, n) => `<tr><td>${n + 1}</td><td>${esc(W.nama(g.kode_barang_asal))}</td><td class="ap-r">${num(g.qty_pcs_asal, 0)}</td><td class="ap-r">${num(g.qty_pcs_hasil, 0)}</td><td class="ap-r">${num(g.total_berat_kg)}</td><td>${esc(W.nama(g.kode_barang_asal))}/${esc(W.FG_BASE[g.kode_barang_asal] || "KODE-INTERNAL")}/(No. Urut saat Konfirmasi)</td></tr>`).join(""), "")) +
      `<div id="is${li}" style="margin-top:8px">${stBox(it)}</div><div id="ic${li}">${c ? nilaiHtml(c) : ""}</div>`;
  }
  function formView() {
    const f = inf, vopts = vendorsWithLap().map((v) => `<option value="${v}"${String(f.id_vendor) === v ? " selected" : ""}>${esc(supName(v))}</option>`).join(""), av = f.id_vendor ? availLap(f.id_vendor) : [];
    return card((f.no_incoming ? "Edit" : "Add") + " Incoming Subkont" + (f.no_incoming ? " — " + esc(f.no_incoming) : ""), `<div class="pr-grid3">
      ${field("Vendor", `<select class="ap-sel ap-in" data-f="id_vendor"${f.items.length ? " disabled" : ""}><option value="">(Select Vendor yang punya Laporan Subkont Closed)</option>${vopts}</select>`, 1, "Satu Incoming = satu vendor, boleh beberapa Laporan Subkont.")}
      ${field("No. Surat Jalan Vendor", tx("no_surat_jalan_vendor", f.no_surat_jalan_vendor, "(Input Free Text) mis. 2534/KPS-DO/VI/26"), 1)}${field("Tgl Incoming", tx("tgl_incoming", f.tgl_incoming, "", "date"), 1)}</div>
      <div class="cl-tools"><select class="ap-sel ap-in" data-f="addsel" style="max-width:520px"><option value="">(Select Laporan Subkont Closed untuk ditambahkan)</option>${av.map((l) => `<option value="${l.no_laporan}"${addSel === l.no_laporan ? " selected" : ""}>${esc(l.no_laporan)} — ${esc(W.nama(l.kode_barang))} — ${esc(l.spk_no || "")}</option>`).join("")}</select>${btn("primary", "addlap", "", "+ Tambah Laporan")}</div>` +
      (f.items.length ? f.items.map(itemView).join("") : '<p class="note">Pilih vendor lalu tambahkan satu atau beberapa Laporan Subkont yang sudah Close.</p>') +
      `<div id="incJ">${jurHtml()}</div><div class="cl-tools" style="margin-top:12px;justify-content:flex-end">${btn("secondary", "cancelform", "", "Cancel")}${btn("success", "saveform", "", "Save")}</div>`);
  }
  const refresh = () => { inf.items.forEach((it, li) => { const s1 = document.getElementById("is" + li), s2 = document.getElementById("ic" + li), c = calc(it); if (s1) s1.innerHTML = stBox(it); if (s2) s2.innerHTML = c ? nilaiHtml(c) : ""; }); const j = document.getElementById("incJ"); if (j) j.innerHTML = jurHtml(); };

  // validasi costbook: kondisi stok material di Stock Value SEBELUM Subkont, lalu tiap transaksi Subkont (OUT Surat Jalan pada harga rata-rata, IN Incoming pada costbook baru)
  const trailHtml = (kode, noInc) => { const t = W.costTrail(kode); if (!t) return '<p class="note">Validasi costbook: buka menu <b>Gudang Coil &rarr; Stock Value</b> sekali agar harga dasar material tersimpan.</p>';
    return `<div class="wh-sec">Validasi Costbook di Stock Value (${esc(t.mid)})</div>` + tbl(["No. Transaksi", "Jenis", "Tgl", "Qty (kg)", "Harga Transaksi (Rp/kg)", "Nilai Transaksi (Rp)", "Qty Akhir (kg)", "Saldo Nilai Akhir (Rp)", "Costbook / Purchase Price (Avg) Akhir (Rp/kg)"],
      `<tr style="background:#f1f3f5"><td colspan="3"><b>Kondisi sebelum Subkont (Stock Value)</b></td><td class="ap-r">${num(t.awal.q)}</td><td class="ap-r">-</td><td class="ap-r">-</td><td class="ap-r">${num(t.awal.q)}</td><td class="ap-r">${num(t.awal.s)}</td><td class="ap-r"><b>${num(t.awal.h)}</b></td></tr>` +
      t.steps.map((x) => `<tr${x.no === noInc ? ' style="background:#e8f7ee"' : ""}><td>${esc(x.no)}</td><td>${x.st === "OUT" ? "OUT (Confirm Surat Jalan)" : "IN (Konfirmasi Incoming)"}</td><td>${fdate(x.tgl)}</td><td class="ap-r">${num(x.q)}</td><td class="ap-r">${num(x.p)}</td><td class="ap-r">${num(x.nilai)}</td><td class="ap-r">${num(x.q_akhir)}</td><td class="ap-r">${num(x.s_akhir)}</td><td class="ap-r"><b>${num(x.h_akhir)}</b></td></tr>`).join(""), "") +
      '<p class="note">Rumus rata-rata bergerak: OUT mengurangi qty dan nilai pada harga rata-rata berjalan (harga tidak berubah); IN menambah qty dan nilai, lalu harga baru = Saldo Nilai Akhir ÷ Qty Akhir. Angka sama dengan History Stock Value material tersebut.</p>'; };
  /* ---------- detail ---------- */
  function viewModal(no) {
    const i = W.incOf(no), conf = i.status === "Confirmed";
    openModal({ title: "Detail Incoming — " + no, size: "xl", hdr: "info", body: () => `<div class="wh-grid2"><div>${kv([["No. Incoming", esc(no)], ["Vendor", esc(supName(i.id_vendor))], ["No. Surat Jalan Vendor", esc(i.no_surat_jalan_vendor)]])}</div><div>${kv([["Tgl Incoming", fdate(i.tgl_incoming)], ["Status Cek", stl(incCek(i))], ["Status", stl(i.status)]].concat(conf ? [["Confirmed By / At", `${esc(i.confirmed_by)} / ${esc(i.confirmed_at)}`]] : []))}</div></div>` +
      i.items.map((it) => { const l = lapOf(it.no_laporan), m = isMat(l), c = calc(it);
        return `<div class="wh-sec">${esc(it.no_laporan)} &bull; ${esc(it.spk_no)} &bull; No. PO Jasa ${esc(it.no_po_jasa)} &bull; ${esc(it.jenis_tarif_jasa)}</div>` + cekHtml(l) + nilaiHtml(c) + (m ? trailHtml(l.kode_barang, no) : "") +
          (conf ? `<div class="wh-sec">Hasil Konfirmasi ${esc(it.no_laporan)}</div><p class="note">Stok ini juga dapat dilihat di menu <b>Gudang Coil &rarr; Stock From Warehouse</b> (tab WRH. Production 2) untuk baby coil, dan di <b>Produksi &rarr; Stock Finish Good</b> untuk FG baru (KW 1) dan scrap.</p>` + (m ? tbl(["No. Coil (warehouse_stock_coil)", "Kd Gudang", "Net Weight (kg)", "Harga Beli (Rp/kg)"], S().stokPRT.filter((x) => x.no_incoming === no && x.no_laporan === it.no_laporan).map((x) => `<tr><td>${esc(x.no_coil)}</td><td>${x.kd_gudang}</td><td class="ap-r">${num(x.net_weight)}</td><td class="ap-r">${num(x.harga_beli)}</td></tr>`).join("")) + `<p class="note">Scrap ke Inventory Scrap: ${num(c.scrap)} kg senilai Rp ${num(c.ns)}.</p>` : tbl(["Kode FG Baru (fg_stock)", "Qty (pcs)", "Costbook (Rp/pcs)", "KW"], S().fgBaru.filter((x) => x.no_incoming === no && x.no_laporan === it.no_laporan).map((x) => `<tr><td>${esc(x.kode_fg_baru)}</td><td class="ap-r">${num(x.qty, 0)}</td><td class="ap-r">${num(x.costbook_per_pcs)}</td><td>${x.kw}</td></tr>`).join(""))) : ""); }).join("") +
      `<div class="wh-sec">Jurnal Incoming${conf ? "" : " (pratinjau)"}</div>${jurnalHtml({ lines: conf ? i.jurnal.map((x) => [x[0], [x[1], x[2]], x[3]]) : aggLines(i.items) })}` });
  }

  /* ---------- halaman Incoming ---------- */
  P.inc = {
    app: "Subkont", title: "Incoming Subkont", mod: "subkont_incoming", role: "PPIC", ui: { mode: "list", tab: "inc", q: "", st: "" },
    hint: "PPIC mencatat barang hasil subkont dari vendor: satu Incoming boleh berisi beberapa Laporan Subkont (Closed). Berat timbang dicocokkan dengan LHP (±0,7%), biaya jasa diinput per laporan, lalu Konfirmasi.",
    render() {
      const u = U.ui; if (u.mode === "form") return formView();
      const tabs = `<div class="cl-tools">${[["inc", "Incoming"], ["temuan", "Temuan Incoming"]].map(([k, t]) => `<button type="button" class="ap-btn ap-btn--sm ap-btn--${u.tab === k ? "primary" : "secondary"}" data-act="tab" data-id="${k}">${t}${k === "temuan" ? ` (${S().rev.filter((r) => r.asal === "Temuan Incoming" && r.status === "Open").length} Open)` : ""}</button>`).join("")}</div>`;
      if (u.tab === "temuan") {
        const l = S().rev.filter((r) => r.asal === "Temuan Incoming").sort((a, b) => b.id - a.id);
        return card("Temuan Incoming (ketidaksesuaian Incoming vs LHP)", tabs + tbl(["No. Laporan", "Vendor", "Alasan", "Status", "Diajukan Oleh", "Diajukan At", "Ditutup By", "Ditutup At", "Action"], l.map((r) => { const lp = lapOf(r.no_laporan); return `<tr><td><b>${esc(r.no_laporan)}</b><div class="muted" style="font-size:11px">LHP: ${esc(lp.status)}</div></td><td>${esc(supName(lp.id_vendor))}</td><td>${esc(r.alasan)}</td><td>${stl(r.status)}</td><td>${esc(r.diajukan_oleh)}</td><td>${esc(r.diajukan_at)}</td><td>${esc(r.ditutup_by || "-")}</td><td>${esc(r.ditutup_at || "-")}</td><td class="ap-act ap-act-nowrap">${r.status === "Open" ? btn("success", "tutup", r.id, "Tutup Temuan") : ""}</td></tr>`; }).join(""), "Belum ada temuan.") + '<p class="note">Tidak ada batas waktu klarifikasi. Setelah vendor merevisi LHP dan Close kembali, PPIC menutup temuan. Menutup temuan = HFG menerima selisih (FR-119); laporan lalu dapat dimasukkan ke Incoming lagi dan boleh dikonfirmasi walau masih Tidak Sesuai.</p>');
      }
      const ql = (u.q || "").toLowerCase(), l = S().inc.filter((i) => (!u.st || i.status === u.st) && (!ql || (i.no_incoming + " " + i.items.map((x) => x.no_laporan + " " + x.spk_no).join(" ") + " " + supName(i.id_vendor) + " " + i.no_surat_jalan_vendor).toLowerCase().includes(ql))).sort((a, b) => b.no_incoming.localeCompare(a.no_incoming));
      return card("Daftar Incoming Subkont", tabs + `<div class="cl-tools"><input class="ap-in" data-u="q" placeholder="Search No. Incoming / Laporan / Vendor / SPK..." value="${esc(u.q)}"><select class="ap-sel ap-in" data-u="st"><option value="">-- Semua Status --</option>${["Draft", "Confirmed"].map((x) => `<option${u.st === x ? " selected" : ""}>${x}</option>`).join("")}</select><span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="add">+ Incoming</button></span></div>` +
        tbl(["No. Incoming", "Tgl Incoming", "Vendor", "No. Surat Jalan Vendor", "No. Laporan", "No. SPK Subkont", "Status Cek", "Status", "Option"], l.map((i) => { const ok = i.status === "Draft" && i.items.length > 0 && i.items.every(eligible);
          return `<tr><td style="white-space:nowrap"><b>${esc(i.no_incoming)}</b></td><td style="white-space:nowrap">${fdate(i.tgl_incoming)}</td><td>${esc(supName(i.id_vendor))}</td><td>${esc(i.no_surat_jalan_vendor)}</td><td>${i.items.map((x) => esc(x.no_laporan)).join("<br>")}</td><td>${[...new Set(i.items.map((x) => x.spk_no))].map(esc).join("<br>")}</td><td style="text-align:center">${stl(incCek(i))}</td><td style="text-align:center">${stl(i.status)}</td>
          <td class="ap-act ap-act-nowrap">${btn("info", "view", i.no_incoming, "View")}${i.status === "Draft" ? btn("warning", "edit", i.no_incoming, "Edit") : ""}${ok ? btn("success", "confirm", i.no_incoming, "Konfirmasi") : ""}</td></tr>`; }).join(""), "Belum ada Incoming.")) + '<p class="note">Satu Incoming berisi beberapa Laporan Subkont milik satu vendor. Konfirmasi aktif bila setiap laporan Sesuai (±0,7%) atau temuannya sudah Ditutup PPIC. Tidak sesuai: Catat Temuan pada laporan terkait (akses edit LHP dibuka ke vendor, asal "Temuan Incoming") dan laporan itu keluar dari Incoming ini.</p>';
    },
    onField(el, type) {
      const [k, a, b] = String(el.dataset.f).split("|"), f = inf, n = U.isNumEl(el) ? pv(el) : null, it = f && f.items[+a];
      if (k === "id_vendor") { f.id_vendor = el.value; addSel = ""; U.render(); return; }
      if (k === "addsel") { addSel = el.value; return; }
      if (k === "tarif") { it.jenis_tarif_jasa = el.value; U.render(); return; }
      if (k === "harga") { it.harga_jasa_per_kg = isNaN(n) ? 0 : n; refresh(); return; }
      if (k === "lot") { it.nilai_jasa_lot = isNaN(n) ? 0 : n; refresh(); return; }
      if (k === "po") { it.no_po_jasa = el.value; return; }
      f[k] = el.value;
    },
    act: {
      tab(d) { U.ui.tab = d.id; U.render(); },
      add() { inf = newForm(); addSel = ""; U.ui.mode = "form"; U.render(); },
      cancelform() { U.ui.mode = "list"; U.render(); },
      view: (d) => viewModal(d.id),
      edit(d) { inf = W.deep(W.incOf(d.id)); addSel = ""; U.ui.mode = "form"; U.render(); },
      addlap() { if (!inf.id_vendor) { warn("Pilih Vendor terlebih dahulu."); return; } if (!addSel) { warn("Pilih Laporan Subkont yang akan ditambahkan."); return; } inf.items.push(itemOf(lapOf(addSel))); addSel = ""; U.render(); },
      dellap(d) { inf.items.splice(+d.id, 1); U.render(); },
      saveform() {
        const f = inf, bad = (t) => dialog({ icon: "error", title: "Validasi Gagal", text: t });
        if (!f.id_vendor) { bad("Vendor wajib dipilih."); return; }
        if (!f.no_surat_jalan_vendor.trim()) { bad("No. Surat Jalan Vendor wajib diisi."); return; }
        if (!f.tgl_incoming) { bad("Tgl Incoming wajib diisi."); return; }
        if (!f.items.length) { bad("Tambahkan minimal satu Laporan Subkont."); return; }
        for (const it of f.items) {
          if (!it.no_po_jasa.trim()) { bad(`No. PO Jasa wajib diisi (${it.no_laporan}).`); return; }
          if (it.jenis_tarif_jasa === "Per Kg" ? !(it.harga_jasa_per_kg > 0) : !(it.nilai_jasa_lot > 0)) { bad(`${it.jenis_tarif_jasa === "Per Kg" ? "Harga Jasa/Kg" : "Nilai Jasa Lot"} wajib lebih dari 0 (${it.no_laporan}).`); return; }
        }
        const rec = Object.assign(W.deep(f), { no_incoming: f.no_incoming || W.nextInc(), status: "Draft" });
        const old = S().inc.findIndex((x) => x.no_incoming === rec.no_incoming); if (old >= 0) S().inc[old] = rec; else S().inc.push(rec);
        W.save(); U.ui.mode = "list"; U.render(); toast(`Incoming ${rec.no_incoming} disimpan (${rec.items.length} laporan). Status Cek: ${incCek(rec)}.`, incCek(rec) === "Tidak Sesuai" ? "warn" : "ok");
      },
      temuan(d) {
        const li = +d.id, it = inf.items[li], l = lapOf(it.no_laporan), ck = cekLap(l);
        if (ck.status !== "Tidak Sesuai") { warn("Catat Temuan hanya untuk laporan Tidak Sesuai (selisih Packing List vs LHP > ±" + TOL + "%)."); return; }
        let alasan = `Selisih Nett Weight Supplier vs Packing List ${num(ck.pct, 3)}% melebihi ±${TOL}%: Packing List ${num(ck.before)} kg vs LHP (Actual + Scrap) ${num(ck.supplier)} kg. Mohon konfirmasi ulang LHP.`;
        openModal({ title: "Catat Temuan Incoming — " + it.no_laporan, size: "lg", hdr: "danger", ok: "Catat Temuan & Buka Akses Edit", okCls: "danger",
          body: () => `<p>Klarifikasi ke supplier dilakukan di luar sistem. Temuan dicatat (status Open), akses edit ${esc(it.no_laporan)} dibuka kembali untuk vendor (asal "Temuan Incoming"), dan laporan ini dikeluarkan dari Incoming (laporan lain tetap bisa dikonfirmasi).</p>${field("Alasan / Catatan Klarifikasi", `<textarea class="ap-in" data-f="alasan" rows="4">${esc(alasan)}</textarea>`, 1)}`,
          onField(el) { alasan = el.value; },
          onOk() { if (!alasan.trim()) { dialog({ icon: "error", title: "Validasi Gagal", text: "Alasan wajib diisi." }); return; }
            S().rev.push({ id: W.nextRev(), no_laporan: l.no_laporan, asal: "Temuan Incoming", alasan: alasan.trim(), status: "Open", diajukan_oleh: PPIC, diajukan_at: now(), diputuskan_by: PPIC, diputuskan_at: now(), ditutup_by: "", ditutup_at: "" });
            l.status = "Dibuka Kembali"; l.reopened = true; inf.items.splice(li, 1);
            if (inf.no_incoming) { const st = W.incOf(inf.no_incoming); if (st) { st.items = st.items.filter((x) => x.no_laporan !== l.no_laporan); if (!st.items.length) S().inc = S().inc.filter((x) => x !== st); } }
            W.save(); closeModal(); if (!inf.items.length) { U.ui.mode = "list"; } U.render(); toast("Temuan dicatat; LHP dikembalikan ke vendor dan dikeluarkan dari Incoming.", "warn"); } });
      },
      tutup(d) { const r = S().rev.find((x) => x.id === +d.id), l = lapOf(r.no_laporan);
        if (l.status !== "Closed") { warn("LHP belum direvisi dan di-Close ulang oleh vendor; temuan belum dapat ditutup."); return; }
        dialog({ icon: "question", title: "Tutup Temuan?", text: `Temuan ${l.no_laporan} ditutup oleh ${PPIC}. Selisih dianggap diterima HFG.`, ok: "Ya, Tutup", cancel: "Batal" }).then((ok) => { if (!ok) return; r.status = "Ditutup"; r.ditutup_by = PPIC; r.ditutup_at = now(); W.save(); U.render(); toast("Temuan ditutup.", "ok"); }); },
      confirm: (d) => confirmInc(d.id),
    },
  };

  /* ---------- Laporan Nilai Slitting (HFG saja) ---------- */
  P.nilai = {
    app: "Subkont", title: "Laporan Nilai Slitting", mod: "subkont_incoming (laporan nilai)", role: "PPIC", ui: {},
    hint: "Nilai material, biaya jasa, scrap, selisih, dan costbook baru per Incoming yang sudah Confirmed. Hanya untuk HFG; tidak terlihat vendor (NFR-18).",
    render() {
      const l = []; S().inc.filter((i) => i.status === "Confirmed").sort((a, b) => b.no_incoming.localeCompare(a.no_incoming)).forEach((i) => i.items.forEach((it) => l.push([i, it])));
      return card("Laporan Nilai Slitting", tbl(["No. Incoming", "No. Laporan", "Vendor", "No. SPK Subkont", "Jenis Tarif Jasa", "Harga Jasa/Kg", "Nilai Jasa Lot", "Biaya Subkont", "Costbook Gudang HFG", "Nilai Material Gudang", "Nilai Material Biaya Proses", "Total Scrap (kg)", "Nilai Scrap", "Selisih (kg)", "Nilai Selisih", "Costbook Baru/Kg", "Costbook Baru/Pcs"], l.map(([i, it]) => { const m = isMat(lapOf(it.no_laporan));
        return `<tr><td><b>${esc(i.no_incoming)}</b></td><td>${esc(it.no_laporan)}</td><td>${esc(supName(i.id_vendor))}</td><td>${esc(it.spk_no)}</td><td>${esc(it.jenis_tarif_jasa)}</td><td class="ap-r">${it.jenis_tarif_jasa === "Per Kg" ? num(it.harga_jasa_per_kg) : "-"}</td><td class="ap-r">${it.jenis_tarif_jasa === "Lot" ? num(it.nilai_jasa_lot) : "-"}</td><td class="ap-r">${num(it.biaya_subkont)}</td>${m ? `<td class="ap-r">${num(it.costbook_gudang_hfg)}</td><td class="ap-r">${num(it.nilai_material_gudang)}</td><td class="ap-r">${num(it.nilai_material_biaya_proses)}</td><td class="ap-r">${num(it.total_scrap_kg)}</td><td class="ap-r">${num(it.nilai_scrap)}</td><td class="ap-r">${num(it.selisih_kg)}</td><td class="ap-r">${num(it.nilai_selisih)}</td><td class="ap-r"><b>${num(it.costbook_baru_per_kg)}</b></td><td class="ap-r">-</td>` : `<td class="ap-r" colspan="7" style="text-align:center;color:#6c757d">SPK FG: nilai FG kirim ${num(it.nilai_fg_kirim)}</td><td class="ap-r">-</td><td class="ap-r"><b>${num(it.costbook_baru_per_pcs)}</b></td>`}</tr>`; }).join(""), "Belum ada Incoming Confirmed.")) + '<p class="note">Satu baris per Laporan dalam Incoming. Biaya Subkont = Total Weight Slitting × Harga Jasa/Kg (atau Nilai Jasa Lot). Costbook Baru/Kg hanya untuk SPK Material; Costbook Baru/Pcs hanya untuk SPK FG dan diteruskan ke fg_stock. Scrap 100% ke Inventory Scrap.</p>';
    },
  };

  /* ---------- Saldo Material In Supplier (Accounting, baca saja) ---------- */
  P.saldo = {
    app: "Subkont", title: "Saldo Material In Supplier", mod: "subkont_in_supplier_ledger", role: "Accounting", ui: { v: "", s: "" },
    hint: "Ledger (kartu stok model) barang HFG yang sedang berada di vendor per Vendor + No. SPK Subkont + Kode Delivery. Kirim menambah saat Surat Jalan Confirmed, Terima mengurangi saat Konfirmasi Incoming.",
    render() {
      const u = U.ui, led = S().ledger.filter((x) => (!u.v || String(x.id_vendor) === u.v) && (!u.s || x.spk_no === u.s)), grp = {};
      S().ledger.forEach((x) => { const k = x.id_vendor + "|" + x.spk_no; grp[k] = (grp[k] || 0) + (x.jenis_mutasi === "Kirim" ? x.nilai : -x.nilai); });
      const vopts = [...new Set(S().ledger.map((x) => String(x.id_vendor)))], sopts = [...new Set(S().ledger.map((x) => x.spk_no))];
      return card("Ringkasan Saldo per Vendor dan SPK", tbl(["Vendor", "No. SPK Subkont", "Saldo Nilai (Rp)"], Object.keys(grp).map((k) => { const [v, s] = k.split("|"); return `<tr><td>${esc(supName(v))}</td><td>${esc(s)}</td><td class="ap-r"><b>${num(r2(grp[k]))}</b></td></tr>`; }).join(""), "Belum ada mutasi.")) +
        card("Ledger Mutasi", `<div class="cl-tools"><select class="ap-sel ap-in" data-u="v"><option value="">-- Semua Vendor --</option>${vopts.map((v) => `<option value="${v}"${u.v === v ? " selected" : ""}>${esc(supName(v))}</option>`).join("")}</select><select class="ap-sel ap-in" data-u="s"><option value="">-- Semua SPK --</option>${sopts.map((v) => `<option${u.s === v ? " selected" : ""}>${v}</option>`).join("")}</select></div>` +
        tbl(["Tgl Transaksi", "Vendor", "No. SPK Subkont", "Kode Delivery", "Jenis Mutasi", "Qty", "Net Weight (kg)", "Nilai (Rp)", "Saldo Nilai (Rp)", "No. Referensi"], led.sort((a, b) => a.id - b.id).map((x) => `<tr><td style="white-space:nowrap">${fdate(x.tgl_transaksi)}</td><td>${esc(supName(x.id_vendor))}</td><td>${esc(x.spk_no)}</td><td>${esc(x.kode_delivery)}</td><td>${stl(x.jenis_mutasi)}</td><td class="ap-r">${num(x.qty, 0)}</td><td class="ap-r">${x.net_weight ? num(x.net_weight) : "-"}</td><td class="ap-r">${num(x.nilai)}</td><td class="ap-r">${num(x.saldo_nilai)}</td><td>${esc(x.no_referensi)}</td></tr>`).join(""), "Belum ada mutasi.")) + '<p class="note">Baca saja (Accounting). Saldo nol berarti seluruh barang yang dikirim sudah diterima kembali dan dikonfirmasi. Akun Material In Supplier: kode sementara 9901-01-01.</p>';
    },
  };

  U.init(P);
})();
