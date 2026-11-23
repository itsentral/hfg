/* ============================================================
   sk-portal.js — Portal Vendor (mockup, aplikasi terpisah): UC-21..UC-25 sisi vendor; FR-113–FR-117, NFR-18.
   Akses (keputusan user 2026-11-20): TANPA pendaftaran, login, atau password. HFG membuat link unik per supplier di Master Supplier (sk-master-supplier.html);
   vendor membuka link `sk-portal.html?t=<token>`. Link tidak valid / dinonaktifkan = akses ditolak.
   Isi: Surat Jalan masuk, Daftar Laporan Subkont, Input Laporan Material (slitting) dan FG (tambah pasir),
   Close Laporan, Ajukan Perbaikan. Vendor TIDAK melihat costbook, nilai, maupun biaya jasa (NFR-18).
   Keputusan user 2026-11-20: selisih > 0,7% tetap boleh Close dengan peringatan; kode FG baru hanya pratinjau (kode resmi saat Konfirmasi Incoming).
   Label field = nama kolom ERD. Data: sk-data.js (links, lap, rev). Pembuka akses edit oleh HFG (UC-25) ada di sisi HFG (tahap berikutnya).
   ============================================================ */
(() => {
  "use strict";
  const U = window.WHUI, W = U.W, P = window.SKP;
  const { esc, num, fdate, btn, tbl, card, kv, toast, dialog, warn, done, openModal, closeModal, pv } = U;
  const S = () => W.state;
  const TOL = 0.7;
  const mat = (jenis) => jenis === W.JENIS[0];
  const ST = { Draft: "warning", Closed: "success", "Dibuka Kembali": "info", "Belum Diinput": "secondary", "Menunggu Persetujuan": "warning", Aktif: "success", Ditolak: "danger", Nonaktif: "secondary" };
  const stl = (s) => `<span class="ap-label ap-label--${ST[s] || "secondary"}">${esc(s)}</span>`;
  const field = (label, html, req, hint) => `<div class="pr-f"><label class="ap-lbl">${label}${req ? ' <span class="ap-req">*</span>' : ""}</label>${html}${hint ? `<div class="ap-hint">${hint}</div>` : ""}</div>`;
  const tx = (f, v, ph, type) => `<input class="ap-in" type="${type || "text"}" data-f="${f}" value="${esc(v)}"${ph ? ` placeholder="${esc(ph)}"` : ""}>`;
  const nm = (f, v, ph) => U.inp(f, v === "" || v == null ? "" : v, { num: 1, ph });
  const tokenNow = () => new URLSearchParams(location.search).get("t") || "";
  const linkNow = () => S().links.find((l) => l.token_link === tokenNow() && l.status_link === "Aktif") || null;
  // konteks vendor dari link (nama_pic dipakai sebagai nama pengaju/penutup di riwayat revisi = nama supplier)
  const acct = () => { const l = linkNow(); if (!l) return null; const v = W.vendorBy(l.id_vendor); return v ? { id_vendor: l.id_vendor, nama_perusahaan: v.nama, nama_pic: v.nama, link: l } : null; };
  const mine = () => { const a = acct(); return a ? S().dlv.filter((d) => d.status === "Confirmed" && String(d.id_vendor) === String(a.id_vendor)) : []; };
  const revOf = (no) => S().rev.filter((r) => r.no_laporan === no);
  const pendingRev = (no) => revOf(no).find((r) => r.status === "Diajukan");
  const now = () => W.today() + " " + new Date().toTimeString().slice(0, 5);
  let lf = null, touched = false;   // state tampilan portal

  /* ---------- baris laporan per material dari Surat Jalan Confirmed (FR-114) ---------- */
  function rows() {
    const out = [];
    mine().forEach((d) => {
      const seen = []; d.detail.forEach((x) => { const k = x.spk_no + "|" + x.kode_barang; if (!seen.includes(k)) seen.push(k); });   // satu baris per (SPK, material) dalam Surat Jalan
      seen.forEach((k) => { const [spk, kode] = k.split("|"), s = W.spkOf(spk), m = mat(s.jenis_subkont), items = d.detail.filter((x) => x.spk_no === spk && x.kode_barang === kode), l = W.lapOf(d.kode_delivery, spk, kode);
        out.push({ d, s, m, kode, items, l, before: m ? items.reduce((a, x) => a + x.net_weight, 0) : items.reduce((a, x) => a + x.qty, 0) }); });
    });
    return out.sort((a, b) => b.d.tgl_kirim.localeCompare(a.d.tgl_kirim));
  }
  const rowBy = (id) => { const [dk, spk, kode] = id.split("|"); return rows().find((x) => x.d.kode_delivery === dk && x.s.spk_no === spk && x.kode === kode); };
  const calcLap = (f) => {
    const roll = f.packs.reduce((a, p) => a + (p.jumlah_coil_roll || 0), 0), before = f.packs.reduce((a, p) => a + p.nett_weight_before, 0);
    const prod = (f.nett_weight_actual || 0) + (f.total_scrap || 0), sel = before - prod;
    return { roll, before, prod, sel, pct: before ? (sel / before) * 100 : 0, perRoll: roll ? (f.nett_weight_actual || 0) / roll : 0 };
  };
  // FG tambah pasir (keputusan user 2026-11-20): Berat Tambahan = berat pasir PER PCS; Berat/Pcs Baru = Berat/Pcs Asal (fg_stock.berat_referensi) + Berat Tambahan;
  // Total Berat (kg) = Qty Pcs Hasil x Berat/Pcs Baru (otomatis, bukan input)
  const refW = (g) => (W.FG.find((x) => x.kode_barang === g.kode_barang_asal) || {}).berat_referensi || 0;
  const pcsBaru = (g) => Math.round((refW(g) + (g.berat_tambahan_kg || 0)) * 10000) / 10000;
  const totFg = (g) => Math.round((g.qty_pcs_hasil || 0) * pcsBaru(g) * 1000) / 1000;
  const fgRefresh = (i) => { const g = lf.fg[i], a = document.getElementById("fgp" + i), b = document.getElementById("fgt" + i); g.total_berat_kg = totFg(g); if (a) a.textContent = num(pcsBaru(g), 4); if (b) b.innerHTML = "<b>" + num(totFg(g)) + "</b>"; };
  const urut = (f, kodeAsal) => { const base = W.FG_BASE[kodeAsal] || "KODE-INTERNAL"; const used = S().lap.reduce((a, l) => a + (l.fg ? l.fg.filter((g) => g.kode_internal === base && l.no_laporan !== f.no_laporan).length : 0), 0); return { base, n: used + 1 }; };
  const fgKode = (f, g) => { const u = urut(f, g.kode_barang_asal); return `${W.nama(g.kode_barang_asal)}/${u.base}/${u.n}`; };

  /* ---------- tampilan ---------- */
  const topBar = (a) => `<div class="ap-card" style="margin-bottom:12px"><div class="ap-card__body" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap"><div><b>${esc(a.nama_perusahaan)}</b><div class="muted" style="font-size:12px">Portal Vendor HFG &bull; akses melalui link dari HFG</div></div></div></div>`;
  function invalidView() {
    return card("Link Portal Tidak Valid", `<div class="wh-need is-bad">Link portal ini tidak valid atau sudah dinonaktifkan oleh HFG.</div><p class="note">Portal Vendor hanya dapat dibuka lewat link yang diberikan HFG. Hubungi HFG (Purchasing) untuk meminta link baru. Untuk contoh: buka <b>Subkont &rarr; Master Supplier</b>, lalu tekan <b>Buat Link Portal</b> atau <b>Buka Portal</b> pada vendor.</p>`);
  }
  function listView(a) {
    const tab = U.ui.tab || "lap", rs = rows(), dl = mine();
    const tabs = `<div class="cl-tools">${[["lap", "Laporan Subkont"], ["sj", "Surat Jalan Masuk"]].map(([k, t]) => `<button type="button" class="ap-btn ap-btn--sm ap-btn--${tab === k ? "primary" : "secondary"}" data-act="tab" data-id="${k}">${t}</button>`).join("")}</div>`;
    if (tab === "sj") {
      return topBar(a) + card("Surat Jalan Masuk dari HFG", tabs + tbl(["Kode Delivery", "No. Surat Jalan", "Tgl Kirim", "No. SPK Subkont", "Jenis Subkont", "Total Qty"], dl.map((d) => { const t = W.dlvTotals(d), jn = [...new Set(d.detail.map((x) => (mat(W.spkOf(x.spk_no).jenis_subkont) ? "Material" : "Finish Good")))].join(" + "); return `<tr><td><b>${esc(d.kode_delivery)}</b></td><td>${esc(d.no_surat_jalan)}</td><td>${fdate(d.tgl_kirim)}</td><td>${W.dlvSpks(d).map(esc).join("<br>")}</td><td>${jn}</td><td class="ap-r">${[t.nw || t.pack ? num(t.nw) + " kg" : "", t.pcs ? num(t.pcs, 0) + " pcs" : ""].filter(Boolean).join("<br>")}</td></tr>`; }).join(""), "Belum ada Surat Jalan yang dikirim HFG kepada Anda.") + '<p class="note">Hanya Surat Jalan yang sudah Confirmed oleh HFG yang tampil. Costbook dan nilai tidak ditampilkan di portal.</p>');
    }
    return topBar(a) + card("Daftar Laporan Subkont", tabs + tbl(["No", "No. Surat Jalan", "No. PO", "Material", "Nett Weight Before", "Nett Weight After", "Total Roll", "Status", "Action"], rs.map((r, i) => {
      const l = r.l, st = l ? l.status : "Belum Diinput", id = r.d.kode_delivery + "|" + r.s.spk_no + "|" + r.kode, pr = l && pendingRev(l.no_laporan);
      const after = l ? (r.m ? num(l.nett_weight_actual) + " kg" : num(l.fg.reduce((x, g) => x + g.total_berat_kg, 0)) + " kg") : "-", roll = l ? (r.m ? num(l.packs.reduce((x, p) => x + p.jumlah_coil_roll, 0), 0) : num(l.fg.reduce((x, g) => x + g.qty_pcs_hasil, 0), 0) + " pcs") : "-";
      return `<tr><td>${i + 1}</td><td>${esc(r.d.no_surat_jalan)}<div class="muted" style="font-size:11px">${esc(r.d.kode_delivery)}</div></td><td>${esc(l ? l.no_po || "-" : "-")}</td><td>${esc(W.nama(r.kode))}<div class="muted" style="font-size:11px">${r.m ? "Slitting" : "Tambah Pasir"} &bull; ${esc(r.s.spk_no)}</div></td><td class="ap-r">${num(r.before, r.m ? 2 : 0)} ${r.m ? "kg" : "pcs"}</td><td class="ap-r">${after}</td><td class="ap-r">${roll}</td><td style="text-align:center">${stl(st)}${pr ? '<div class="muted" style="font-size:11px">Perbaikan diajukan</div>' : ""}</td>
      <td class="ap-act ap-act-nowrap">${!l ? btn("primary", "input", id, "Input Laporan") : btn("info", "viewlap", id, "View Detail")}${l && l.status !== "Closed" ? btn("warning", "editlap", id, "Edit") : ""}${l && l.status !== "Closed" ? btn("success", "closelap", id, "Close Laporan") : ""}${l && l.status === "Closed" && !pr ? btn("secondary", "ajukan", id, "Ajukan Perbaikan") : ""}</td></tr>`; }).join(""), "Belum ada Surat Jalan Confirmed untuk dilaporkan.") + '<p class="note">Daftar per material dari Surat Jalan yang sudah Confirmed. Edit hanya saat laporan Draft atau dibuka kembali oleh HFG; laporan Closed hanya bisa diperbaiki lewat Ajukan Perbaikan (FR-117).</p>');
  }
  const sumHtml = (f) => { const c = calcLap(f); return kv([["Nett Weight Slitting + Scrap = Total Nett Production", `${num(f.nett_weight_actual || 0)} + ${num(f.total_scrap || 0)} = <b>${num(c.prod)} kg</b>`], ["Net Weight Before", num(c.before) + " kg"], ["Selisih (kg)", num(c.sel)], ["Selisih (%)", `<b>${num(c.pct, 3)}%</b>`], ["Net Weight per Roll (dibagi rata)", c.roll ? num(c.perRoll) + " kg" : "-"]]) + (Math.abs(c.pct) > TOL ? `<div class="wh-need is-bad"><b>Peringatan:</b> Selisih ${num(c.pct, 3)}% melebihi toleransi ${TOL}%. Laporan tetap boleh disimpan dan di-Close; HFG akan menilai saat Incoming (FR-119).</div>` : ""); };
  function formView() {
    const f = lf, m = f.m, c = m ? calcLap(f) : null;
    const hdr = `<div class="pr-grid3">${field("No. Surat Jalan", `<div class="ap-in ap-in--ro">${esc(f.sj)}</div>`)}${field("No. Surat Jalan Vendor", tx("no_surat_jalan_vendor", f.no_surat_jalan_vendor, "(Input Free Text)"), 1)}${field("No. PO", tx("no_po", f.no_po, "(Input Free Text) referensi PO jasa"), 1, "Hanya referensi teks; tidak terhubung ke SPK.")}
      ${field("Tgl Laporan", tx("tgl_laporan", f.tgl_laporan, "", "date"), 1)}${m ? field("Nett Weight Actual (kg)", nm("nett_weight_actual", f.nett_weight_actual, "hasil slitting"), 1) + field("Width After (mm)", nm("width_after", f.width_after, ""), 1) + field("Total Scrap (kg)", nm("total_scrap", f.total_scrap, "0")) : ""}</div>`;
    let body;
    if (m) {
      body = `<div class="wh-sec">Detail Pack — ${esc(W.nama(f.kode))}</div>` + tbl(["No", "Pack", "Material", "Kode Internal", "Nett Weight Before (kg)", "Jumlah Coil (Roll Baby Coil)"], f.packs.map((p, i) => `<tr><td>${i + 1}</td><td>${esc(p.pack_code)}</td><td>${esc(W.nama(f.kode))}</td><td><input class="ap-in" data-f="ki|${i}" value="${esc(p.kode_internal)}" placeholder="(Input Free Text)"></td><td class="ap-r">${num(p.nett_weight_before)}</td><td>${U.inp("roll|" + i, p.jumlah_coil_roll || "", { num: 1, ph: "mis. 8" })}</td></tr>`).join("")) +
        `<div class="cl-tools" style="margin-top:8px">${btn("info", "babypv", "", "View Detail Baby Coil")}</div><div class="wh-sec">Summary Packing List vs Aktual</div><div id="lfSum">${sumHtml(f)}</div>`;
    } else {
      body = `<div class="wh-sec">Detail Hasil per Produk (tambah pasir)</div><p class="note">Pasir dipasok vendor; HFG tidak mengirim pasir. Isi qty hasil, nama material tambahan, dan <b>Berat Tambahan = berat pasir per pcs</b>. Berat/Pcs Baru = Berat/Pcs Asal + Berat Tambahan, dan Total Berat (kg) = Qty Pcs Hasil &times; Berat/Pcs Baru (otomatis) (FR-116).</p>` + tbl(["No", "Produk FG Asal (Kode FG)", "Qty Pcs Asal", "Qty Pcs Hasil", "Nama Material Tambahan", "Berat Tambahan (kg/pcs)", "Berat/Pcs Asal (kg)", "Berat/Pcs Baru (kg)", "Total Berat (kg)", "Produk FG Baru (pratinjau)"], f.fg.map((g, i) => `<tr><td>${i + 1}</td><td>${esc(W.nama(g.kode_barang_asal))}<div class="muted" style="font-size:11px">${esc(g.kode_barang_asal)}</div></td><td class="ap-r">${num(g.qty_pcs_asal, 0)}</td><td>${U.inp("hasil|" + i, g.qty_pcs_hasil || "", { num: 1, ph: "pcs" })}</td><td><input class="ap-in" data-f="mt|${i}" value="${esc(g.nama_material_tambahan)}" placeholder="mis. Pasir"></td><td>${U.inp("bt|" + i, g.berat_tambahan_kg || "", { num: 1, ph: "kg per pcs" })}</td><td class="ap-r">${num(refW(g), 4)}</td><td class="ap-r" id="fgp${i}">${num(pcsBaru(g), 4)}</td><td class="ap-r" id="fgt${i}"><b>${num(totFg(g))}</b></td><td style="color:#6c757d">${esc(fgKode(f, g))}</td></tr>`).join("")) + '<p class="note">Kode FG baru bersifat pratinjau; kode resmi (No. Urut Subkont) diberikan saat PPIC Konfirmasi Hasil Incoming.</p>';
    }
    const tm = !f.isNew && S().rev.find((r) => r.no_laporan === f.no_laporan && r.asal === "Temuan Incoming" && r.status === "Open");
    const banner = tm ? `<div class="wh-need is-bad"><b>HFG meminta konfirmasi ulang LHP (Temuan Incoming):</b> ${esc(tm.alasan)}</div>` : "";
    return topBar(acct()) + banner + card((f.isNew ? "Input" : "Edit") + " Laporan Subkont " + (m ? "Material (Slitting)" : "Finish Good") + " — " + esc(f.no_laporan || "baru"), hdr + body + `<div class="cl-tools" style="margin-top:12px;justify-content:flex-end">${btn("secondary", "cancelform", "", "Cancel")}${btn("success", "saveform", "", "Save")}</div>`);
  }
  function viewModal(r) {
    const l = r.l;
    openModal({ title: "Detail Laporan Subkont — " + l.no_laporan, size: "xl", hdr: "info", body: () => `<div class="wh-grid2"><div>${kv([["No. Laporan", esc(l.no_laporan)], ["No. Surat Jalan", esc(r.d.no_surat_jalan)], ["No. Surat Jalan Vendor", esc(l.no_surat_jalan_vendor)], ["No. PO", esc(l.no_po || "-")], ["Tgl Laporan", fdate(l.tgl_laporan)]])}</div><div>${kv([["Status", stl(l.status)], ["Jenis Subkont", r.m ? "Material (Slitting)" : "Finish Good (tambah pasir)"]].concat(r.m ? [["Nett Weight Actual", num(l.nett_weight_actual) + " kg"], ["Width After", num(l.width_after) + " mm"], ["Total Scrap", num(l.total_scrap) + " kg"]] : []))}</div></div>` +
      (r.m ? (() => { const c = calcLap(Object.assign({}, l)); return tbl(["Pack", "Kode Internal", "Nett Weight Before (kg)", "Jumlah Coil (Roll)"], l.packs.map((p) => `<tr><td>${esc(p.pack_code)}</td><td>${esc(p.kode_internal)}</td><td class="ap-r">${num(p.nett_weight_before)}</td><td class="ap-r">${num(p.jumlah_coil_roll, 0)}</td></tr>`).join("")) + kv([["Total Nett Production", num(c.prod) + " kg"], ["Selisih (kg / %)", `${num(c.sel)} / ${num(c.pct, 3)}%`]]) + (Math.abs(c.pct) > TOL ? `<div class="wh-need is-bad">Selisih melebihi toleransi ${TOL}%; HFG menilai saat Incoming.</div>` : ""); })() : tbl(["Produk FG Asal", "Qty Pcs Hasil", "Material Tambahan", "Berat Tambahan (kg/pcs)", "Berat/Pcs Baru (kg)", "Total Berat (kg)", "Produk FG Baru (pratinjau)"], l.fg.map((g) => `<tr><td>${esc(W.nama(g.kode_barang_asal))}</td><td class="ap-r">${num(g.qty_pcs_hasil, 0)}</td><td>${esc(g.nama_material_tambahan || "-")}</td><td class="ap-r">${num(g.berat_tambahan_kg, 4)}</td><td class="ap-r">${num(pcsBaru(g), 4)}</td><td class="ap-r">${num(g.total_berat_kg)}</td><td>${esc(fgKode(l, g))}</td></tr>`).join(""))) +
      (revOf(l.no_laporan).length ? `<div class="wh-sec">Riwayat Perbaikan</div>` + tbl(["Asal", "Alasan", "Status", "Diajukan Oleh", "Diajukan At", "Diputuskan By", "Ditutup At"], revOf(l.no_laporan).map((v) => `<tr><td>${esc(v.asal)}</td><td>${esc(v.alasan)}</td><td>${esc(v.status)}</td><td>${esc(v.diajukan_oleh)}</td><td>${esc(v.diajukan_at)}</td><td>${esc(v.diputuskan_by || "-")}</td><td>${esc(v.ditutup_at || "-")}</td></tr>`).join("")) : "") });
  }
  function babyModal() {
    const list = W.babies(lf).map((b) => `<tr><td>${esc(b.pack_code)}</td><td>${esc(b.babycoil_code)}</td><td class="ap-r">${num(b.net_weight)}</td></tr>`);
    openModal({ title: "Baby Coil Terbentuk", size: "lg", hdr: "info", body: () => tbl(["Pack", "Identitas Baby Coil", "Net Weight per Roll (kg)"], list.join(""), "Isi Jumlah Coil terlebih dahulu.") + '<p class="note">Net Weight per Roll = Nett Weight Actual ÷ total Jumlah Coil (dibagi rata, seperti Unpack Coil).</p>' });
  }

  /* ---------- halaman ---------- */
  P.portal = {
    app: "Portal Vendor", title: "Portal Vendor HFG", mod: "portal_vendor (aplikasi terpisah)", role: "Vendor", ui: { tab: "lap" },
    hint: "Portal vendor subkont (dibuka lewat link dari HFG): lihat Surat Jalan, input Laporan Hasil Produksi (LHP), Close, dan ajukan perbaikan. Tanpa costbook/nilai.",
    render() { const a = acct(); if (!a) return invalidView(); if (!touched) { touched = true; a.link.last_access_at = now(); W.save(); } return U.ui.mode === "form" ? formView() : listView(a); },
    onField(el, type) {
      const [k, i] = String(el.dataset.f).split("|"), n = U.isNumEl(el) ? pv(el) : null;
      if (!lf) return;
      const f = lf, num0 = (v) => (isNaN(v) ? 0 : v);
      if (k === "ki") f.packs[+i].kode_internal = el.value;
      else if (k === "roll") f.packs[+i].jumlah_coil_roll = Math.round(num0(n));
      else if (k === "hasil") { f.fg[+i].qty_pcs_hasil = Math.round(num0(n)); fgRefresh(+i); }
      else if (k === "mt") f.fg[+i].nama_material_tambahan = el.value;
      else if (k === "bt") { f.fg[+i].berat_tambahan_kg = num0(n); fgRefresh(+i); }
      else if (["nett_weight_actual", "width_after", "total_scrap"].includes(k)) f[k] = num0(n);
      else f[k] = el.value;
      const box = f.m && document.getElementById("lfSum"); if (box) box.innerHTML = sumHtml(f);
    },
    act: {
      input(d) {
        const r = rowBy(d.id), dk = r.d.kode_delivery, kode = r.kode;
        lf = { isNew: true, no_laporan: "", kode_delivery: dk, kode, m: r.m, sj: r.d.no_surat_jalan, spk_no: r.s.spk_no, no_surat_jalan_vendor: "", no_po: "", tgl_laporan: W.today(), nett_weight_actual: 0, width_after: 0, total_scrap: 0,
          packs: r.m ? r.items.map((x) => ({ pack_code: x.pack_code, kode_internal: "", nett_weight_before: x.net_weight, jumlah_coil_roll: 0 })) : [],
          fg: r.m ? [] : r.items.map((x) => ({ kode_barang_asal: x.kode_barang, qty_pcs_asal: x.qty, qty_pcs_hasil: 0, nama_material_tambahan: "", berat_tambahan_kg: 0, total_berat_kg: 0, kode_internal: W.FG_BASE[x.kode_barang] || "" })) };
        U.ui.mode = "form"; U.render();
      },
      editlap(d) {
        const r = rowBy(d.id), dk = r.d.kode_delivery, kode = r.kode;
        if (r.l.status === "Closed") { warn("Laporan Closed tidak dapat diedit. Gunakan Ajukan Perbaikan."); return; }
        // kode wajib ikut: tanpanya Save menghapus kode_barang laporan dan laporan hilang dari daftar
        lf = Object.assign(W.deep(r.l), { isNew: false, m: r.m, sj: r.d.no_surat_jalan, kode: r.kode, spk_no: r.s.spk_no });
        U.ui.mode = "form"; U.render();
      },
      viewlap(d) { viewModal(rowBy(d.id)); },
      babypv: () => babyModal(), cancelform() { lf = null; U.ui.mode = "list"; U.render(); },
      saveform() {
        const f = lf, bad = (t) => dialog({ icon: "error", title: "Validasi Gagal", text: t });
        if (!f.no_surat_jalan_vendor.trim()) { bad("No. Surat Jalan Vendor wajib diisi."); return; }
        if (!f.no_po.trim()) { bad("No. PO wajib diisi (referensi PO jasa)."); return; }
        if (!f.tgl_laporan) { bad("Tgl Laporan wajib diisi."); return; }
        if (f.m) {
          if (!(f.nett_weight_actual > 0)) { bad("Nett Weight Actual wajib lebih dari 0."); return; }
          if (!(f.width_after > 0)) { bad("Width After wajib lebih dari 0."); return; }
          if (f.total_scrap < 0) { bad("Total Scrap tidak boleh negatif."); return; }
          if (f.packs.some((p) => !p.kode_internal.trim())) { bad("Kode Internal wajib diisi untuk setiap pack."); return; }
          if (f.packs.some((p) => !(p.jumlah_coil_roll >= 1))) { bad("Jumlah Coil (Roll Baby Coil) wajib minimal 1 untuk setiap pack."); return; }
        } else {
          if (f.fg.some((g) => !(g.qty_pcs_hasil > 0))) { bad("Qty Pcs Hasil wajib lebih dari 0 untuk setiap produk."); return; }
          if (f.fg.some((g) => g.berat_tambahan_kg < 0)) { bad("Berat Tambahan (kg/pcs) tidak boleh negatif."); return; }
          f.fg.forEach((g) => { g.total_berat_kg = totFg(g); });
          if (f.fg.some((g) => g.berat_tambahan_kg > 0 && !g.nama_material_tambahan.trim())) { bad("Nama Material Tambahan wajib diisi bila Berat Tambahan diisi."); return; }
        }
        const a = acct(), rec = { no_laporan: f.no_laporan || W.nextLap(), kode_delivery: f.kode_delivery, kode_barang: f.kode || f.kode_barang, spk_no: f.spk_no, id_vendor: a.id_vendor, no_surat_jalan_vendor: f.no_surat_jalan_vendor.trim(), no_po: f.no_po.trim(), tgl_laporan: f.tgl_laporan, nett_weight_actual: f.nett_weight_actual, width_after: f.width_after, total_scrap: f.total_scrap, status: f.isNew ? "Draft" : f.status, reopened: !!f.reopened, alasan_selisih: "", packs: f.m ? W.deep(f.packs) : [], fg: f.m ? null : W.deep(f.fg) };
        const old = S().lap.findIndex((l) => l.no_laporan === rec.no_laporan);
        if (old >= 0) S().lap[old] = rec; else S().lap.push(rec);
        W.save(); lf = null; U.ui.mode = "list"; U.render();
        const c = rec.packs.length ? calcLap(rec) : null; toast(`Laporan ${rec.no_laporan} disimpan (${rec.status}).` + (c && Math.abs(c.pct) > TOL ? ` Peringatan: selisih ${num(c.pct, 3)}% > ${TOL}%.` : ""), c && Math.abs(c.pct) > TOL ? "warn" : "ok");
      },
      closelap(d) {
        const r = rowBy(d.id), l = r.l;
        const c = r.m ? calcLap(l) : null, w = c && Math.abs(c.pct) > TOL ? ` Perhatian: selisih ${num(c.pct, 3)}% melebihi toleransi ${TOL}%; HFG akan menilai saat Incoming.` : "";
        dialog({ icon: "question", title: "Close Laporan?", text: `Laporan ${l.no_laporan} akan dikunci terhadap edit.${w}`, ok: "Ya, Close", cancel: "Batal" }).then((ok) => {
          if (!ok) return; l.status = "Closed";
          const op = revOf(l.no_laporan).find((v) => v.status === "Dibuka"); if (op) { op.status = "Ditutup"; op.ditutup_by = acct().nama_pic; op.ditutup_at = now(); }
          W.save(); U.render(); toast(`Laporan ${l.no_laporan} Closed.`, "ok");
        });
      },
      ajukan(d) {
        const r = rowBy(d.id), l = r.l; let alasan = "";
        openModal({ title: "Ajukan Perbaikan — " + l.no_laporan, size: "md", hdr: "warning", ok: "Ajukan", okCls: "warning",
          body: () => field("Alasan", `<textarea class="ap-in" data-f="alasan" rows="3" placeholder="(Input Free Text) jelaskan bagian yang perlu diperbaiki">${esc(alasan)}</textarea>`, 1, "Pengajuan diteruskan ke PPIC HFG yang membuka akses edit."),
          onField(el) { alasan = el.value; },
          onOk() { if (!alasan.trim()) { dialog({ icon: "error", title: "Validasi Gagal", text: "Alasan wajib diisi." }); return; }
            S().rev.push({ id: W.nextRev(), no_laporan: l.no_laporan, asal: "Pengajuan Vendor", alasan: alasan.trim(), status: "Diajukan", diajukan_oleh: acct().nama_pic, diajukan_at: now(), diputuskan_by: "", diputuskan_at: "", ditutup_by: "", ditutup_at: "" });
            W.save(); closeModal(); U.render(); toast("Pengajuan perbaikan dikirim ke HFG.", "ok"); } });
      },
    },
  };

  U.init(P);
})();
