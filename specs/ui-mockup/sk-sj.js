/* ============================================================
   sk-sj.js — Halaman Subkont tahap 2 (mockup): Surat Jalan Subkont (UC-20, UC-21; PG-40, PG-41, PG-42).
   Acuan dokumen: docs/fr-nfr.md FR-105–FR-112 (daftar, header, detail dari Tampungan, Confirm, jurnal, print, FG, saldo), workbook sheet
   "Surat Jalan ke Vendor (Rev2)" bagian 1–7; keputusan user 2026-11-20: vendor dipilih dari seluruh Master Supplier saat Surat Jalan;
   **satu Surat Jalan boleh berisi beberapa SPK, boleh campur Material dan FG, tetapi satu vendor** (spk_no ada di tiap baris detail).
   Perluasan modul delivery_fg (menu Delivery, tombol + Delivery Subkont). Label kolom = nama kolom ERD "Subkont ERD".
   Pembantu umum: wh-core.js (WHUI). Data: sk-data.js. Memakai halaman bersama window.SKP; U.init dipanggil di akhir file ini.
   ============================================================ */
(() => {
  "use strict";
  const U = window.WHUI, W = U.W, P = window.SKP;
  const { esc, num, fdate, btn, tbl, card, kv, toast, dialog, warn, done, openModal, closeModal, printDoc, ppHead, ppMeta, ppSign } = U;
  const S = () => W.state;
  const USER = "Dedi K.";
  const ST = { Draft: "warning", Confirmed: "success", "Siap Keluar": "info" };
  const stl = (s) => `<span class="ap-label ap-label--${ST[s] || "secondary"}">${esc(s)}</span>`;
  const mat = (jenis) => jenis === W.JENIS[0];
  const q = (jenis, n) => `${num(n, 0)} ${W.SATUAN[jenis]}`;
  const pill = (c) => `<span class="wh-pack">${esc(c)}</span>`;
  const field = (label, html, req, hint) => `<div class="pr-f"><label class="ap-lbl">${label}${req ? ' <span class="ap-req">*</span>' : ""}</label>${html}${hint ? `<div class="ap-hint">${hint}</div>` : ""}</div>`;
  const vname = (id) => (W.vendorBy(id) || { nama: "-" }).nama;
  const isMatRow = (x) => mat(W.spkOf(x.spk_no).jenis_subkont);
  const nilaiOf = (x) => (isMatRow(x) ? x.net_weight : x.qty) * W.costbook(x.kode_barang);
  const jenisLabel = (d) => { const j = [...new Set(d.detail.map((x) => (isMatRow(x) ? "Material" : "Finish Good")))]; return j.length > 1 ? "Material + Finish Good" : j[0] || "-"; };
  const totalsHtml = (d) => { const t = W.dlvTotals(d), a = []; if (t.nw || t.pack) a.push(`${num(t.nw)} kg (${num(t.pack, 0)} Pack)`); if (t.pcs) a.push(`${num(t.pcs, 0)} pcs`); return a.join("<br>") || "-"; };
  const COA = { mis: ["Material In Supplier", "9901-01-01"], bb: ["Inventory (Persediaan Bahan Baku)", "1105-01-01"], bj: ["Inventory (Persediaan Barang Jadi)", "1106-03-01"] };
  const jurnalLines = (rows) => { const m = rows.filter(isMatRow).reduce((a, x) => a + W.round(nilaiOf(x)), 0), f = rows.filter((x) => !isMatRow(x)).reduce((a, x) => a + W.round(nilaiOf(x)), 0), tot = m + f; return { tot: W.round(tot), lines: [["Db", COA.mis, W.round(tot), 0]].concat(m ? [["Cr", COA.bb, 0, W.round(m)]] : [], f ? [["Cr", COA.bj, 0, W.round(f)]] : []) }; };
  const jurnalHtml = (rows) => { const j = jurnalLines(rows); return tbl(["Posisi", "COA", "Kode Akun (sementara)", "Debit", "Kredit"], j.lines.map((r) => `<tr><td>${r[0]}</td><td>${esc(r[1][0])}</td><td>${esc(r[1][1])}</td><td class="ap-r">${r[2] ? num(r[2]) : ""}</td><td class="ap-r">${r[3] ? num(r[3]) : ""}</td></tr>`).join(""), "",
    `<tr class="wh-foot"><td colspan="3" class="ap-r">Total / Cek Jurnal</td><td class="ap-r">${num(j.tot)}</td><td class="ap-r">${num(j.tot)}</td></tr>`) + '<p class="note">Satu jurnal per Surat Jalan: Db Material In Supplier / Cr Inventory (Persediaan Bahan Baku untuk SPK Material, Persediaan Barang Jadi untuk SPK FG). Tampungan Siap Kirim hanya pemisahan fisik, jadi kredit langsung ke Inventory. Kode akun sementara; BA menetapkan bersama klien (OQ-S9). Posting lewat gl_interface + Master Jurnal Mapping (menu subkont, action confirm_surat_jalan).</p>'; };

  /* ---------- ringkasan per SPK ---------- */
  const requestedQty = (s) => s.detail.reduce((a, d) => a + d.qty_subkont, 0);
  const sentQty = (s) => S().tamp.filter((t) => t.spk_no === s.spk_no && t.status === "Terkirim").reduce((a, t) => a + t.qty, 0);
  let fs = null, scanVal = "", addSel = "";                      // state form
  // tersedia untuk form ini: Siap Kirim, atau terkunci oleh Surat Jalan Draft yang sedang diedit (item yang dihapus dari form boleh dipilih lagi)
  const free = (t) => t.status === "Siap Kirim" || (fs && fs.kode_delivery && t.status === "Terkunci di Surat Jalan" && t.kode_delivery === fs.kode_delivery);
  const spkCandidates = () => S().spk.filter((s) => ["Requested", "Dikirim Sebagian", "Incoming"].includes(s.status) && !fs.spks.includes(s.spk_no) && S().tamp.some((t) => t.spk_no === s.spk_no && free(t)));
  const detOf = (t) => ({ id_tamp: t.id, spk_no: t.spk_no, pack_code: t.pack_code, kode_barang: t.kode_barang, qty: t.qty, net_weight: t.net_weight, costbook: null, nilai: null, sumber: "Tampungan", keterangan: "" });
  const summary = (f, spk_no) => { const s = W.spkOf(spk_no), dipilih = f.detail.filter((x) => x.spk_no === spk_no).reduce((a, x) => a + x.qty, 0), dik = sentQty(s), dm = requestedQty(s); return { s, dm, dik, dipilih, sisa: dm - dik - dipilih }; };
  // status SPK setelah Surat Jalan Confirmed (FR-102): Incoming boleh terjadi saat masih Dikirim Sebagian
  const statusAfterSend = (s) => { const full = s.detail.every((it) => S().tamp.filter((t) => t.spk_no === s.spk_no && t.kode_barang === it.kode_barang && t.status === "Terkirim").reduce((a, t) => a + t.qty, 0) >= it.qty_subkont); const hasInc = S().inc.some((x) => x.status === "Confirmed" && x.items.some((it) => it.spk_no === s.spk_no)); return hasInc ? "Incoming" : full ? "Dikirim Penuh" : "Dikirim Sebagian"; };

  /* ---------- print, view, confirm ---------- */
  const printSj = (d) => {
    const v = W.vendorBy(d.id_vendor), spks = W.dlvSpks(d), t = W.dlvTotals(d);
    const rows = d.detail.map((x, i) => { const m = isMatRow(x); return `<tr><td>${i + 1}</td><td>${esc(x.spk_no)}</td><td>${esc(m ? x.pack_code : x.kode_barang)}</td><td>${esc(W.nama(x.kode_barang))}</td><td class="pp-r">${num(x.qty, 0)} ${m ? "Pack" : "pcs"}</td><td class="pp-r">${m ? num(x.net_weight) : "-"}</td><td>${esc(x.keterangan || "-")}</td></tr>`; }).join("");
    const tot = `<tr><td colspan="4" class="pp-r"><b>Total</b></td><td class="pp-r"><b>${[t.pack ? num(t.pack, 0) + " Pack" : "", t.pcs ? num(t.pcs, 0) + " pcs" : ""].filter(Boolean).join(" + ")}</b></td><td class="pp-r"><b>${t.nw ? num(t.nw) : "-"}</b></td><td></td></tr>`;
    printDoc("Surat Jalan Subkont", ppHead("SURAT JALAN SUBKONT", d.no_surat_jalan).replace("Dokumen contoh (mockup)", "PT. HARAPAN FAJAR GEMILANG") + ppMeta([["No. Surat Jalan", esc(d.no_surat_jalan)], ["Tanggal", fdate(d.tgl_kirim)], ["Kepada", esc(v.nama)], ["Alamat", esc(v.address)], ["No. SPK Subkont", esc(spks.join(", "))], ["Proses", esc([...new Set(spks.map((n) => W.spkOf(n).proses_disubkont || "-"))].join(", "))]]) +
      `<table class="pp-tbl"><thead><tr>${["No", "No. SPK", "Pack / Kode FG", "Material / Nama FG", "Qty", "N.W. (kg)", "Keterangan"].map((h) => `<th>${h}</th>`).join("")}</tr></thead><tbody>${rows}${tot}</tbody></table>` +
      ppMeta([["Nopol / Sopir", esc(d.nopol_sopir || "-")]]) + ppSign(["Dibuat (PPIC)", "Diperiksa (Gudang)", "Sopir", "Diterima (Vendor)"]) + '<p style="font-size:10px;color:#666">Costbook dan Nilai tidak dicetak (data internal).</p>');
  };
  const detTable = (d, conf, status) => tbl(["No", "No. SPK Subkont", "Pack Code / Kode FG", "Nama Barang", "Qty", "Net Weight (kg)", "Costbook (Rp/kg atau Rp/pcs)", "Nilai (Rp)", "Sumber", status ? "Status" : "Keterangan"],
    d.detail.map((x, i) => { const m = isMatRow(x), cb = conf ? x.costbook : W.costbook(x.kode_barang), nl = conf ? x.nilai : W.round(nilaiOf(x)); return `<tr><td>${i + 1}</td><td>${esc(x.spk_no)}</td><td>${esc(m ? x.pack_code : x.kode_barang)}</td><td>${esc(W.nama(x.kode_barang))}</td><td class="ap-r">${q(W.spkOf(x.spk_no).jenis_subkont, x.qty)}</td><td class="ap-r">${m ? num(x.net_weight) : "-"}</td><td class="ap-r">${conf || status ? num(cb) : "(diambil saat Confirm)"}</td><td class="ap-r">${conf || status ? num(nl) : "-"}</td><td>${esc(x.sumber)}</td><td>${status ? stl("Siap Keluar") : esc(x.keterangan || "-")}</td></tr>`; }).join(""));
  const viewModal = (kode) => {
    const d = W.dlvOf(kode), v = W.vendorBy(d.id_vendor), conf = d.status === "Confirmed", spks = W.dlvSpks(d);
    openModal({ title: "Detail Surat Jalan Subkont — " + d.kode_delivery, size: "xl", hdr: "info", body: () => `<div class="wh-grid2"><div>${kv([["Kode Delivery", esc(d.kode_delivery)], ["No. Surat Jalan", esc(d.no_surat_jalan)], ["Tgl Kirim", fdate(d.tgl_kirim)], ["No. SPK Subkont", esc(spks.join(", "))], ["Jenis Subkont", esc(jenisLabel(d))]])}</div><div>${kv([["Vendor Subkont", esc(v.nama)], ["Alamat Vendor", esc(v.address)], ["Nopol / Sopir", esc(d.nopol_sopir || "-")], ["Status", stl(d.status)], ["Catatan", esc(d.catatan || "-")]])}</div></div>` +
      detTable(d, conf, false) + (conf ? `<div class="wh-sec">Jurnal (terbentuk saat Confirm ${esc(d.confirmed_at)} oleh ${esc(d.confirmed_by)})</div>` + jurnalHtml(d.detail) : '<p class="note">Surat Jalan berstatus Draft: barang terkunci untuk Surat Jalan ini, stok belum berkurang, jurnal belum terbentuk.</p>') });
  };
  function confirmModal(kode) {
    const d = W.dlvOf(kode), v = W.vendorBy(d.id_vendor), spks = W.dlvSpks(d);
    openModal({
      title: "Confirm Surat Jalan (Material Fix Keluar dari Gudang) — " + d.kode_delivery, size: "xl", hdr: "success", ok: "Confirm Surat Jalan", okCls: "success",
      body: () => `<div class="wh-grid2"><div>${kv([["No. Surat Jalan", esc(d.no_surat_jalan)], ["Tgl Kirim", fdate(d.tgl_kirim)]])}</div><div>${kv([["No. SPK Subkont", esc(spks.join(", "))], ["Vendor Subkont", esc(v.nama)]])}</div></div>` + detTable(d, false, true) +
        `<div class="wh-sec">Jurnal Otomatis saat Confirm</div>${jurnalHtml(d.detail)}
        <p class="note">Setelah Confirm: (1) barang keluar dari Tampungan Siap Kirim (Inventory berkurang); (2) jurnal terbentuk; (3) status Surat Jalan Confirmed (Edit dan Confirm nonaktif, Print aktif); (4) daftar barang muncul di Portal Vendor sebagai dasar input LHP; (5) Saldo Material In Supplier tercatat per Vendor + No. SPK Subkont (satu baris Kirim per SPK); (6) status tiap SPK di dalamnya maju (Dikirim Sebagian/Penuh).</p>`,
      onOk() {
        d.detail.forEach((x) => { x.costbook = W.costbook(x.kode_barang); x.nilai = W.round(nilaiOf(x)); const t = S().tamp.find((y) => y.id === x.id_tamp); if (t) t.status = "Terkirim"; });
        d.status = "Confirmed"; d.confirmed_by = USER; d.confirmed_at = W.today() + " " + new Date().toTimeString().slice(0, 5);
        const msg = [];
        spks.forEach((sn) => { const rows = d.detail.filter((x) => x.spk_no === sn), tot = W.round(rows.reduce((a, x) => a + x.nilai, 0)), prev = S().ledger.filter((l) => l.spk_no === sn && String(l.id_vendor) === String(d.id_vendor)).reduce((a, l) => a + (l.jenis_mutasi === "Kirim" ? l.nilai : -l.nilai), 0), m = isMatRow(rows[0]);
          S().ledger.push({ id: W.nextLed(), kode_delivery: d.kode_delivery, spk_no: sn, id_vendor: d.id_vendor, tgl_transaksi: d.tgl_kirim, jenis_mutasi: "Kirim", qty: rows.reduce((a, x) => a + x.qty, 0), net_weight: m ? rows.reduce((a, x) => a + x.net_weight, 0) : 0, nilai: tot, saldo_nilai: W.round(prev + tot), no_referensi: d.no_surat_jalan });
          const s = W.spkOf(sn); s.status = statusAfterSend(s); msg.push(`${sn}: ${s.status}`); });
        const j = jurnalLines(d.detail); W.save(); closeModal(); U.render();
        done(`Surat Jalan ${d.no_surat_jalan} Confirmed. Jurnal Db Material In Supplier / Cr Inventory sebesar Rp ${num(j.tot)}. Status SPK ${msg.join(", ")}.`);
      },
    });
  }

  /* ---------- form ---------- */
  const newForm = () => ({ kode_delivery: null, no_surat_jalan: "", tgl_kirim: W.today(), id_vendor: "", nopol_sopir: "", catatan: "", detail: [], spks: [] });
  const addRowFrom = (t, sumber) => { fs.detail.push(Object.assign(detOf(t), { sumber })); };
  function scanRule(code) {
    const c = String(code || "").trim();
    if (!c) return { ok: false, msg: "Pack Code wajib diisi." };
    if (!fs.spks.length) return { ok: false, msg: "Tambahkan minimal satu SPK Subkont terlebih dahulu." };
    const t = S().tamp.find((x) => x.pack_code && x.pack_code.toUpperCase() === c.toUpperCase());
    if (!t) return { ok: false, msg: `Pack "${c}" tidak berada di Tampungan Siap Kirim.` };
    if (!fs.spks.includes(t.spk_no)) return { ok: false, msg: `Pack ${t.pack_code} berada di Tampungan untuk ${t.spk_no}, yang belum ditambahkan ke Surat Jalan ini. Tambahkan SPK itu atau pilih pack milik SPK yang ada di Surat Jalan ini.` };
    if (fs.detail.some((x) => x.id_tamp === t.id)) return { ok: false, msg: `Pack ${t.pack_code} sudah ditambahkan pada Surat Jalan ini.` };
    if (!free(t)) return { ok: false, msg: `Pack ${t.pack_code} sudah masuk Surat Jalan lain (${t.kode_delivery || "-"}, status ${t.status}) dan tidak dapat ditambahkan lagi.` };
    return { ok: true, t };
  }
  function pickModal(spk_no) {
    const s = W.spkOf(spk_no), m = mat(s.jenis_subkont);
    openModal({
      title: "Pilih dari Stok Siap Kirim — " + s.spk_no, size: "lg", hdr: "info",
      body: () => { const rows = S().tamp.filter((t) => t.spk_no === spk_no && free(t) && !fs.detail.some((x) => x.id_tamp === t.id));
        return tbl(m ? ["Pack Code", "Nama Barang", "Qty (Pack)", "Net Weight (kg)", "Aksi"] : ["Kode FG", "Nama FG", "Qty (pcs)", "Aksi"], rows.map((t) => `<tr><td>${m ? pill(t.pack_code) : esc(t.kode_barang)}</td><td>${esc(W.nama(t.kode_barang))}</td><td class="ap-r">${num(t.qty, 0)}</td>${m ? `<td class="ap-r">${num(t.net_weight)}</td>` : ""}<td>${btn("success", "pick", t.id, "Pilih")}</td></tr>`).join(""), "Tidak ada barang Siap Kirim yang belum dipilih.") + '<p class="note">Hanya barang di Tampungan Siap Kirim untuk SPK ini yang muncul. Barang yang sudah masuk Surat Jalan lain (Draft/Confirmed) tidak tampil.</p>'; },
      act: { pick(d) { const t = S().tamp.find((x) => x.id === +d.id); if (t) addRowFrom(t, "Tampungan"); U.drawModal(); U.render(); } },
    });
  }
  function spkSection(sn) {
    const sm = summary(fs, sn), s = sm.s, m = mat(s.jenis_subkont), rows = fs.detail.map((x, i) => [x, i]).filter(([x]) => x.spk_no === sn);
    const heads = m ? ["No", "Pack Code", "Nama Barang", "Qty (Pack)", "Net Weight (kg)", "Costbook (Rp/kg)", "Nilai (Rp)", "Sumber", "Keterangan", "Aksi"] : ["No", "Kode FG", "Nama FG", "Qty (pcs)", "Costbook (Rp/pcs)", "Nilai (Rp)", "Sumber", "Keterangan", "Aksi"];
    return `<div class="wh-sec">${esc(sn)} &bull; ${esc(s.jenis_subkont)} &bull; ${esc(s.proses_disubkont || "-")} &bull; Tujuan Incoming: ${esc(W.TUJUAN[s.jenis_subkont])}</div>
      <div class="wh-need${sm.sisa < 0 ? " is-bad" : sm.sisa === 0 ? " is-ok" : ""}"><b>Diminta SPK:</b> ${q(s.jenis_subkont, sm.dm)} &bull; <b>Sudah Dikirim Sebelumnya:</b> ${q(s.jenis_subkont, sm.dik)} &bull; <b>Dipilih Sekarang:</b> ${q(s.jenis_subkont, sm.dipilih)} &bull; <b>Sisa Belum Dikirim:</b> ${q(s.jenis_subkont, sm.sisa)}</div>
      <div class="cl-tools">${btn("secondary", "pickstock", sn, "Pilih dari Stok Siap Kirim")}${btn("danger", "delspk", sn, "Hapus SPK dari Surat Jalan")}</div>` +
      tbl(heads, rows.map(([x, i], n) => `<tr><td>${n + 1}</td><td>${m ? pill(x.pack_code) : esc(x.kode_barang)}</td><td>${esc(W.nama(x.kode_barang))}</td><td class="ap-r">${num(x.qty, 0)}</td>${m ? `<td class="ap-r">${num(x.net_weight)}</td>` : ""}<td class="ap-r" style="color:#6c757d">${num(W.costbook(x.kode_barang))}</td><td class="ap-r" style="color:#6c757d">${num(nilaiOf(x))}</td><td>${esc(x.sumber)}</td><td><input class="ap-in" data-f="ket|${i}" value="${esc(x.keterangan)}" placeholder="(Input Free Text)"></td><td>${btn("danger", "delrow", i, "Hapus")}</td></tr>`).join(""), "Belum ada item. Scan QR atau pilih dari Stok Siap Kirim.");
  }
  function formView() {
    const f = fs, edit = !!f.kode_delivery, v = W.vendorBy(f.id_vendor), cands = spkCandidates(), t = W.dlvTotals(f);
    return card(edit ? "Edit Delivery Subkont — " + esc(f.kode_delivery) : "Add Delivery Subkont", `<div class="wh-sec">Header Surat Jalan</div><div class="pr-grid3">
        ${field("No. Surat Jalan", `<input class="ap-in" data-f="no_surat_jalan" value="${esc(f.no_surat_jalan)}" placeholder="(Input Free Text) mis. SJ/12/11/2026/Sub">`, 1)}${field("Tgl Kirim", `<input class="ap-in" type="date" data-f="tgl_kirim" value="${esc(f.tgl_kirim)}">`, 1)}
        ${field("Vendor Subkont", `<select class="ap-sel ap-in" data-f="id_vendor"><option value="">(Select Vendor dari Master Supplier)</option>${W.VENDORS.map((x) => `<option value="${x.id}"${String(f.id_vendor) === String(x.id) ? " selected" : ""}>${esc(x.nama)}</option>`).join("")}</select>`, 1, v && W.vendorPortal(v.id) !== "Link Aktif" ? `Supplier ini belum punya link Portal Vendor aktif (${esc(W.vendorPortal(v.id))}); buat dan berikan link dari Master Supplier agar vendor dapat melihat Surat Jalan dan mengisi LHP.` : "Satu vendor per Surat Jalan; seluruh SPK di dalamnya dikirim ke vendor ini.")}
        ${field("Alamat Vendor", `<div class="ap-in ap-in--ro">${v ? esc(v.address) : "(Auto dari Master Supplier)"}</div>`)}${field("Nopol / Sopir", `<input class="ap-in" data-f="nopol_sopir" value="${esc(f.nopol_sopir)}" placeholder="(Input Free Text) mis. B 1234 XYZ / Budi">`)}
        ${field("Catatan", `<textarea class="ap-in" data-f="catatan" rows="1" placeholder="(Input Free Text)">${esc(f.catatan)}</textarea>`)}</div>
      <div class="wh-sec">Detail — SPK Subkont dan barang dari Tampungan Siap Kirim</div>
      <div class="cl-tools"><select class="ap-sel ap-in" data-f="addsel" style="max-width:420px"><option value="">(Select SPK Subkont yang punya barang di Tampungan Siap Kirim)</option>${cands.map((s) => `<option value="${s.spk_no}"${addSel === s.spk_no ? " selected" : ""}>${esc(s.spk_no)} — ${esc(s.jenis_subkont)} — ${esc(s.status)}</option>`).join("")}</select>${btn("primary", "addspk", "", "+ Tambah SPK")}
        <span class="wh-scanbox" style="margin:0;padding:6px 10px"><b>(Scan QR)</b> <input class="ap-in" style="display:inline-block;width:220px" data-enter="scan" data-f="scan" placeholder="Pack Code" value="${esc(scanVal)}" autocomplete="off"> <button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="scan">Scan</button></span></div>
      <p class="ap-hint">Satu Surat Jalan boleh berisi beberapa SPK, boleh campur Material dan FG. Scan QR hanya untuk pack Material milik SPK yang sudah ditambahkan.</p>` +
      (f.spks.length ? f.spks.map(spkSection).join("") : '<p class="note">Belum ada SPK. Tambahkan SPK Subkont terlebih dahulu.</p>') +
      (f.detail.length ? kv([["Total Net Weight (kg) / Total Pack", `${num(t.nw)} / ${num(t.pack, 0)}`], ["Total Qty Pcs", num(t.pcs, 0)], ["Perkiraan Nilai (Rp)", num(W.round(f.detail.reduce((a, x) => a + nilaiOf(x), 0)))]]) : "") +
      `<p class="note">Costbook Material diambil dari Stock Value (Purchase Price (Avg) material) dan costbook FG dari fg_stock saat Surat Jalan di-Confirm; angka abu-abu hanya perkiraan (data internal HFG, tidak dicetak dan tidak tampil di Portal Vendor). Save: Surat Jalan berstatus Draft, barang terkunci untuk Surat Jalan ini, stok belum berkurang.</p>
      <div class="cl-tools" style="margin-top:10px;justify-content:flex-end"><button type="button" class="ap-btn ap-btn--secondary" data-act="cancelform">Cancel</button><button type="button" class="ap-btn ap-btn--success" data-act="saveform">Save</button></div>`);
  }

  /* ---------- halaman ---------- */
  P.sj = {
    app: "Subkont", title: "Delivery Subkont (Surat Jalan ke Vendor)", mod: "delivery_fg (Delivery Subkont)", role: "PPIC", ui: { mode: "list", q: "", st: "" },
    hint: "Surat Jalan barang subkont dari Tampungan Siap Kirim ke vendor. Satu Surat Jalan boleh berisi beberapa SPK (Material per Pack dan/atau FG per pcs) untuk satu vendor; satu SPK boleh beberapa Surat Jalan.",
    render() {
      const u = U.ui;
      if (u.mode === "form") return formView();
      const ql = (u.q || "").toLowerCase();
      const l = S().dlv.filter((d) => (!u.st || d.status === u.st) && (!ql || (d.kode_delivery + " " + d.no_surat_jalan + " " + vname(d.id_vendor) + " " + W.dlvSpks(d).join(" ")).toLowerCase().includes(ql))).sort((a, b) => b.tgl_kirim.localeCompare(a.tgl_kirim) || b.kode_delivery.localeCompare(a.kode_delivery));
      return card("Daftar Surat Jalan (menu Delivery)", `<div class="cl-tools"><input class="ap-in" data-u="q" placeholder="Search Kode / No. Surat Jalan / Vendor / SPK..." value="${esc(u.q)}"><select class="ap-sel ap-in" data-u="st"><option value="">-- Semua Status --</option><option${u.st === "Draft" ? " selected" : ""}>Draft</option><option${u.st === "Confirmed" ? " selected" : ""}>Confirmed</option></select>
          <span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="add">+ Delivery Subkont</button></span></div>` +
        tbl(["Kode Delivery", "No. Surat Jalan", "Vendor", "Tgl Kirim", "No. SPK Subkont", "Jenis Subkont", "Total Qty (Net Weight / Pcs)", "Status", "Option"], l.map((d) => { const dr = d.status === "Draft";
          return `<tr><td style="white-space:nowrap"><b>${esc(d.kode_delivery)}</b></td><td>${esc(d.no_surat_jalan)}</td><td>${esc(vname(d.id_vendor))}</td><td style="white-space:nowrap">${fdate(d.tgl_kirim)}</td><td style="white-space:nowrap">${W.dlvSpks(d).map(esc).join("<br>")}</td><td>${jenisLabel(d)}</td><td class="ap-r">${totalsHtml(d)}</td><td style="text-align:center">${stl(d.status)}</td>
            <td class="ap-act ap-act-nowrap">${btn("info", "view", d.kode_delivery, "View")}${dr ? btn("warning", "edit", d.kode_delivery, "Edit") : ""}${btn("secondary", "print", d.kode_delivery, "Print")}${dr ? btn("success", "confirm", d.kode_delivery, "Confirm Surat Jalan") : ""}</td></tr>`; }).join(""), "Belum ada Surat Jalan Subkont.")) +
        '<p class="note">Edit dan Confirm Surat Jalan hanya aktif saat Draft; setelah Confirmed hanya Print (dan View). Total Qty = Net Weight (kg) untuk barang SPK Material dan pcs untuk barang SPK Finish Good (dapat tampil keduanya bila Surat Jalan campur). Tombol View ditambahkan di mockup untuk melihat detail dan jurnal.</p>';
    },
    onField(el) {
      const [k, i] = String(el.dataset.f).split("|");
      if (k === "scan") { scanVal = el.value; return; }
      if (k === "addsel") { addSel = el.value; return; }
      if (k === "ket") { fs.detail[+i].keterangan = el.value; return; }
      if (k === "id_vendor") { fs.id_vendor = el.value; U.render(); return; }
      fs[k] = el.value;
    },
    act: {
      add() { fs = newForm(); scanVal = ""; addSel = ""; U.ui.mode = "form"; U.render(); },
      cancelform() { U.ui.mode = "list"; U.render(); },
      view: (d) => viewModal(d.id), print: (d) => printSj(W.dlvOf(d.id)), confirm: (d) => confirmModal(d.id),
      edit(d) { const x = W.dlvOf(d.id); if (x.status !== "Draft") { warn("Surat Jalan hanya dapat diedit saat berstatus Draft."); return; } fs = W.deep(x); delete fs.status; fs.spks = W.dlvSpks(x); scanVal = ""; addSel = ""; U.ui.mode = "form"; U.render(); },
      addspk() { if (!addSel) { warn("Pilih SPK Subkont yang akan ditambahkan."); return; } fs.spks.push(addSel); addSel = ""; U.render(); },
      delspk(d) { fs.spks = fs.spks.filter((n) => n !== d.id); fs.detail = fs.detail.filter((x) => x.spk_no !== d.id); U.render(); },
      pickstock: (d) => pickModal(d.id),
      delrow(d) { fs.detail.splice(+d.id, 1); U.render(); },
      scan() {
        const r = scanRule(scanVal);
        if (!r.ok) { dialog({ icon: "error", title: "Scan Pack Ditolak", text: r.msg }); return; }
        addRowFrom(r.t, "Scan QR"); scanVal = ""; U.render(); toast(`Pack ${r.t.pack_code} ditambahkan ke Surat Jalan (${r.t.spk_no}).`, "ok");
      },
      saveform() {
        const bad = (m) => dialog({ icon: "error", title: "Validasi Gagal", text: m });
        if (!fs.no_surat_jalan.trim()) { bad("No. Surat Jalan wajib diisi."); return; }
        if (S().dlv.some((d) => d.kode_delivery !== fs.kode_delivery && d.no_surat_jalan.toLowerCase() === fs.no_surat_jalan.trim().toLowerCase() && String(d.id_vendor) === String(fs.id_vendor))) { bad("No. Surat Jalan sudah dipakai untuk vendor ini."); return; }
        if (!fs.tgl_kirim) { bad("Tgl Kirim wajib diisi."); return; }
        if (!fs.id_vendor) { bad("Vendor Subkont wajib dipilih."); return; }
        if (!fs.spks.length) { bad("Tambahkan minimal satu SPK Subkont."); return; }
        const empty = fs.spks.find((n) => !fs.detail.some((x) => x.spk_no === n)); if (empty) { bad(`${empty} belum punya item. Tambahkan item dari Tampungan Siap Kirim atau hapus SPK itu dari Surat Jalan.`); return; }
        for (const n of fs.spks) { const sm = summary(fs, n); if (sm.sisa < 0) { bad(`${n}: item dipilih (${q(sm.s.jenis_subkont, sm.dipilih)}) melebihi sisa SPK (${q(sm.s.jenis_subkont, sm.dm - sm.dik)}).`); return; } }
        const x = fs.kode_delivery ? W.dlvOf(fs.kode_delivery) : null, kode = x ? x.kode_delivery : W.nextDlv();
        if (x) x.detail.forEach((o) => { if (!fs.detail.some((n) => n.id_tamp === o.id_tamp)) { const t = S().tamp.find((y) => y.id === o.id_tamp); if (t) { t.status = "Siap Kirim"; t.kode_delivery = ""; } } });   // item dihapus dari Draft = lepas kunci
        fs.detail.forEach((n) => { const t = S().tamp.find((y) => y.id === n.id_tamp); if (t) { t.status = "Terkunci di Surat Jalan"; t.kode_delivery = kode; } });
        const rec = { kode_delivery: kode, no_surat_jalan: fs.no_surat_jalan.trim(), tgl_kirim: fs.tgl_kirim, id_vendor: Number(fs.id_vendor), nopol_sopir: fs.nopol_sopir, catatan: fs.catatan, status: "Draft", created_by: x ? x.created_by : USER, confirmed_by: "", confirmed_at: "", detail: W.deep(fs.detail) };
        if (x) Object.assign(x, rec); else S().dlv.push(rec);
        W.save(); U.ui.mode = "list"; U.render(); toast(`Surat Jalan ${kode} disimpan berstatus Draft; ${fs.detail.length} item dari ${fs.spks.length} SPK terkunci.`, "ok");
      },
    },
  };

  U.init(P);
})();
