/* ============================================================
   sk-pages.js — Halaman Subkont, tahap 1 (mockup): SPK Subkont (UC-17, UC-18, UC-N37) dan Tampungan Siap Kirim (UC-19).
   Acuan dokumen: docs/fr-nfr.md FR-99–FR-104, workbook sheet "Prod. Plan" (konsep input SPK Subkont) dan sheet "Surat Jalan" bagian 3
   (Tampungan Siap Kirim); jawaban user 2026-11-20 (input/Production Planning/notes.md). Label kolom = nama kolom ERD "Subkont ERD".
   Pembantu umum: wh-core.js (WHUI). Data: sk-data.js. Tahap berikutnya (belum dibuat): Surat Jalan, Portal Vendor, Incoming.
   ============================================================ */
(() => {
  "use strict";
  const U = window.WHUI, W = U.W;
  const { $, esc, num, fdate, btn, tbl, card, kv, tabs, toast, dialog, warn, done, confirmThen, openModal, closeModal, pv } = U;
  const P = (window.SKP = window.SKP || {});
  const S = () => W.state;
  const USER = "Budi S.";
  const APP = "Subkont";
  const ST = { Draft: "warning", Requested: "info", "Dikirim Sebagian": "info", "Dikirim Penuh": "success", Incoming: "success", Closed: "dark", Cancelled: "danger", "Siap Kirim": "success", "Terkunci di Surat Jalan": "warning", Terkirim: "secondary" };
  const stl = (s) => `<span class="ap-label ap-label--${ST[s] || "secondary"}">${esc(s)}</span>`;
  const unit = (jenis) => W.SATUAN[jenis];
  const q = (jenis, n) => `${num(n, 0)} ${unit(jenis)}`;
  const pill = (c) => `<span class="wh-pack">${esc(c)}</span>`;
  const field = (label, html, req, hint) => `<div class="pr-f"><label class="ap-lbl">${label}${req ? ' <span class="ap-req">*</span>' : ""}</label>${html}${hint ? `<div class="ap-hint">${hint}</div>` : ""}</div>`;
  const base = { app: APP, mod: "production_planning (SPK Subkont)", role: "PPIC" };

  /* ======================= 1. SPK Subkont (list, view, form) ======================= */
  let ff = null;                                                  // state form SPK
  const newForm = () => ({ spk_no: null, jenis_subkont: "", tgl_spk: W.today(), proses_disubkont: "", catatan: "", detail: [] });
  const itemList = (jenis) => (jenis === W.JENIS[0] ? W.MAT : W.FG);
  const detTable = (s, live) => tbl(["No", "Kode Barang", "Nama Barang", "Stok Saat Ini", "Qty diSubkont", "Qty Siap Kirim", "Qty Terkirim", "Sisa Belum Dikirim", "Keterangan"],
    s.detail.map((d, i) => { const p = W.prog(s, d); return `<tr><td>${i + 1}</td><td>${esc(d.kode_barang)}</td><td>${esc(W.nama(d.kode_barang))}</td><td class="ap-r">${live ? q(s.jenis_subkont, W.stok(s.jenis_subkont, d.kode_barang)) : "-"}</td><td class="ap-r"><b>${q(s.jenis_subkont, p.qty_subkont)}</b></td><td class="ap-r">${q(s.jenis_subkont, p.qty_siap_kirim)}</td><td class="ap-r">${q(s.jenis_subkont, p.qty_terkirim)}</td><td class="ap-r">${q(s.jenis_subkont, p.sisa)}</td><td>${esc(d.keterangan || "-")}</td></tr>`; }).join(""), "Belum ada item.");

  function doRequest(no) {
    const s = W.spkOf(no);
    if (!s.detail.length) { warn("SPK belum memiliki item. Tambahkan minimal 1 item sebelum Request Item to Warehouse."); return; }
    const bad = s.detail.find((d) => d.qty_subkont > W.stok(s.jenis_subkont, d.kode_barang));
    if (bad) { warn(`Stok ${W.nama(bad.kode_barang)} tidak cukup (stok ${q(s.jenis_subkont, W.stok(s.jenis_subkont, bad.kode_barang))}, diminta ${q(s.jenis_subkont, bad.qty_subkont)}). Ubah Qty diSubkont terlebih dahulu.`); return; }
    confirmThen({ icon: "question", title: "Request Item to Warehouse?", text: `${no} dikirim sebagai permintaan ke ${W.TUJUAN[s.jenis_subkont] === "Warehouse Material" ? "Warehouse Material" : "Warehouse Finish Good"}. Setelah berstatus Requested, SPK tidak dapat diedit. Permintaan tidak mengubah stok.`, ok: "Ya, Request", cancel: "Batal" }, () => {
      s.status = "Requested"; W.save(); U.ui.mode = "list"; U.render(); toast(`${no} berstatus Requested; permintaan tampil di ${W.TUJUAN[s.jenis_subkont]}.`, "ok");
    });
  }
  function doCancel(no) {
    const s = W.spkOf(no);
    if (!W.canCancel(s)) { warn(s.status === "Requested" ? `SPK ${no} tidak dapat dibatalkan karena sudah ada barang di Tampungan Siap Kirim.` : `SPK ${no} berstatus ${s.status} dan tidak dapat dibatalkan (hanya Draft atau Requested tanpa barang di Tampungan).`); return; }
    confirmThen({ title: "Cancel SPK Subkont?", text: `${no} akan dibatalkan dan tidak dapat diaktifkan kembali.`, ok: "Ya, Cancel SPK", cancel: "Kembali" }, () => { s.status = "Cancelled"; W.save(); U.ui.mode = "list"; U.render(); toast(`${no} dibatalkan.`, "ok"); });
  }
  const actBtns = (s) => (s.status === "Draft" ? btn("warning", "edit", s.spk_no, "Edit") + btn("primary", "request", s.spk_no, "Request Item to Warehouse") : "") + (W.canCancel(s) ? btn("danger", "cancel", s.spk_no, "Cancel") : "");

  function formView() {
    const f = ff, edit = !!f.spk_no, items = f.jenis_subkont ? true : false;
    const rows = f.detail.map((d, i) => `<tr><td>${i + 1}</td><td>${esc(d.kode_barang)}</td><td>${esc(W.nama(d.kode_barang))}</td><td class="ap-r">${q(f.jenis_subkont, W.stok(f.jenis_subkont, d.kode_barang))}</td>
      <td style="width:160px">${U.inp("qty|" + i, d.qty_subkont ? d.qty_subkont : "", { num: 1 })}<div class="ap-hint">${unit(f.jenis_subkont)}</div></td><td><input class="ap-in" data-f="ket|${i}" value="${esc(d.keterangan)}" placeholder="(Input Free Text)"></td><td>${btn("danger", "delrow", i, "Hapus")}</td></tr>`).join("");
    return card(edit ? "Edit SPK Subkont — " + esc(f.spk_no) : "Create SPK Subkont", `<div class="wh-sec">Header</div><div class="pr-grid3">
        ${field("Jenis Subkont", U.sel("jenis_subkont", f.jenis_subkont, W.JENIS, { blank: "(Select SPK Subkont Material / SPK Subkont FG)" }), 1)}
        ${field("SPK No", `<div class="ap-in ap-in--ro">${edit ? esc(f.spk_no) : "(Auto Generate)"}</div>`)}${field("Tgl SPK", `<input class="ap-in" type="date" data-f="tgl_spk" value="${esc(f.tgl_spk)}">`, 1)}
        ${field("Proses diSubkont", `<input class="ap-in" data-f="proses_disubkont" value="${esc(f.proses_disubkont)}" placeholder="(Input Free Text) mis. Proses Slitting Material">`)}
        ${field("Tujuan Incoming", `<div class="ap-in ap-in--ro">${f.jenis_subkont ? esc(W.TUJUAN[f.jenis_subkont]) : "-"}</div>`, 0, f.jenis_subkont === W.JENIS[0] ? "Jika SPK Subkont Material, otomatis incoming ke Warehouse Material." : f.jenis_subkont ? "Jika SPK Subkont FG, otomatis incoming ke Warehouse Finish Good." : "Otomatis dari Jenis Subkont.")}
        ${field("Catatan", `<textarea class="ap-in" data-f="catatan" rows="1" placeholder="Catatan tambahan (opsional)">${esc(f.catatan)}</textarea>`)}</div>
      <div class="wh-sec">Detail SPK Subkont</div>
      <div class="cl-tools"><button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="additem"${items ? "" : " disabled"}>+ ADD Item (${f.jenis_subkont === W.JENIS[1] ? "FG" : "Material"})</button>
        <span class="ap-hint">${f.jenis_subkont === W.JENIS[0] ? "Jika SPK Subkont Material, Add Material dari Warehouse Material (stok dalam Pack)." : f.jenis_subkont === W.JENIS[1] ? "Jika SPK Subkont FG, Add Finish Good dari Gudang Finish Good (stok KW 1, dalam pcs)." : "Pilih Jenis Subkont terlebih dahulu."}</span></div>` +
      tbl(["No", "Kode Barang", "Nama Barang", "Stok Saat Ini", "Qty diSubkont", "Keterangan", "Aksi"], rows, "Belum ada item. Klik ADD Item.") +
      `<p class="note">Vendor dan No. PO tidak dipilih di SPK: vendor ditentukan saat Surat Jalan dan PO jasa adalah dokumen terpisah (keputusan user 2026-11-20). Qty diSubkont: Pack utuh untuk Material, pcs untuk FG; tidak boleh melebihi Stok Saat Ini.</p>
      <div class="cl-tools" style="margin-top:10px;justify-content:flex-end"><button type="button" class="ap-btn ap-btn--secondary" data-act="cancelform">Cancel</button><button type="button" class="ap-btn ap-btn--success" data-act="saveform">Save (Draft)</button><button type="button" class="ap-btn ap-btn--primary" data-act="saveform" data-req="1">Save &amp; Request Item to Warehouse</button></div>`);
  }
  function addItemModal() {
    const jenis = ff.jenis_subkont, used = ff.detail.map((d) => d.kode_barang);
    openModal({
      title: "ADD Item (" + (jenis === W.JENIS[1] ? "Finish Good" : "Material") + ")", size: "lg", hdr: "info",
      body: () => tbl(["Kode Barang", "Nama Barang", "Stok Saat Ini", "Aksi"], itemList(jenis).filter((m) => !used.includes(m.kode_barang)).map((m) => { const st = W.stok(jenis, m.kode_barang); return `<tr><td>${esc(m.kode_barang)}</td><td>${esc(m.nama_barang)}</td><td class="ap-r">${q(jenis, st)}</td><td>${st > 0 ? btn("success", "pick", m.kode_barang, "Pilih") : '<span class="ap-hint">Stok kosong</span>'}</td></tr>`; }).join(""), "Semua barang sudah dipilih / tidak ada stok.") + '<p class="note">Database stok: Material = pack di gudang PRO yang belum berada di Tampungan; FG = qty stok KW 1 dikurangi yang sudah di Tampungan.</p>',
      act: { pick(d) { ff.detail.push({ kode_barang: d.id, qty_subkont: 0, keterangan: "" }); closeModal(); U.render(); } },
    });
  }
  P.spk = Object.assign({}, base, {
    title: "SPK Subkont", ui: { mode: "list", id: null, q: "", st: "" },
    hint: "Surat Perintah Kerja subkontrak ke vendor: SPK Subkont Material (slitting, per Pack) atau SPK Subkont FG (tambah pasir, per pcs). Vendor ditentukan saat Surat Jalan.",
    render() {
      const u = U.ui;
      if (u.mode === "form") return formView();
      if (u.mode === "view") {
        const s = W.spkOf(u.id), t = W.total(s);
        return card("Detail SPK Subkont — " + esc(s.spk_no), `<div class="wh-grid2"><div>${kv([["SPK No", esc(s.spk_no)], ["Tgl SPK", fdate(s.tgl_spk)], ["Jenis Subkont", esc(s.jenis_subkont)], ["Proses diSubkont", esc(s.proses_disubkont || "-")]])}</div><div>${kv([["Tujuan Incoming", esc(W.TUJUAN[s.jenis_subkont])], ["Status", stl(s.status)], ["Catatan", esc(s.catatan || "-")], ["Created By", esc(s.created_by)]])}</div></div>` +
          '<div class="wh-sec">Detail SPK Subkont</div>' + detTable(s, s.status === "Draft" || s.status === "Requested") +
          `<div class="cl-tools"><span class="ap-hint">Total: diminta ${q(s.jenis_subkont, t.qty_subkont)}, siap kirim ${q(s.jenis_subkont, t.qty_siap_kirim)}, terkirim ${q(s.jenis_subkont, t.qty_terkirim)}.</span></div>` +
          `<div class="cl-tools" style="margin-top:6px"><button type="button" class="ap-btn ap-btn--outline-warning" data-act="back">&#8592; Kembali</button>${actBtns(s)}</div>`);
      }
      const all = S().spk, ql = (u.q || "").toLowerCase();
      const l = all.filter((s) => (!u.st || s.status === u.st) && (!ql || (s.spk_no + " " + s.jenis_subkont + " " + s.proses_disubkont + " " + s.detail.map((d) => W.nama(d.kode_barang)).join(" ")).toLowerCase().includes(ql))).sort((a, b) => b.tgl_spk.localeCompare(a.tgl_spk) || b.spk_no.localeCompare(a.spk_no));
      const stOpt = ["Draft", "Requested", "Dikirim Sebagian", "Dikirim Penuh", "Incoming", "Closed", "Cancelled"];
      return card("Daftar SPK Subkont", `<div class="cl-tools"><input class="ap-in" data-u="q" placeholder="Search SPK No / Jenis / Barang..." value="${esc(u.q)}"><select class="ap-sel ap-in" data-u="st"><option value="">-- Semua Status --</option>${stOpt.map((x) => `<option${u.st === x ? " selected" : ""}>${x}</option>`).join("")}</select>
          <span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="add">+ Create SPK Subkont</button></span></div>` +
        tbl(["No", "SPK No", "Tgl SPK", "Jenis Subkont", "Proses diSubkont", "Status", "Qty diSubkont", "Qty Siap Kirim", "Qty Terkirim", "Sisa Belum Dikirim", "Aksi"],
          l.map((s, i) => { const t = W.total(s); return `<tr><td>${i + 1}</td><td style="white-space:nowrap"><b>${esc(s.spk_no)}</b></td><td style="white-space:nowrap">${fdate(s.tgl_spk)}</td><td>${esc(s.jenis_subkont)}</td><td>${esc(s.proses_disubkont || "-")}</td><td style="text-align:center">${stl(s.status)}</td><td class="ap-r">${q(s.jenis_subkont, t.qty_subkont)}</td><td class="ap-r">${q(s.jenis_subkont, t.qty_siap_kirim)}</td><td class="ap-r">${q(s.jenis_subkont, t.qty_terkirim)}</td><td class="ap-r">${q(s.jenis_subkont, t.sisa)}</td>
            <td class="ap-act ap-act-nowrap">${btn("info", "view", s.spk_no, "View")}${actBtns(s)}</td></tr>`; }).join(""), "Tidak ada SPK Subkont.")) +
        '<p class="note">Status: Draft → Requested (Request Item to Warehouse) → Dikirim Sebagian → Dikirim Penuh → Incoming → Closed; Cancelled hanya dari Draft atau Requested tanpa barang di Tampungan. Edit hanya pada Draft. Status mulai Dikirim Sebagian berubah lewat Surat Jalan (tahap berikutnya, belum di mockup ini). Qty Siap Kirim diisi oleh gudang di menu Tampungan Siap Kirim.</p>';
    },
    onField(el, type) {
      const [k, i] = String(el.dataset.f).split("|");
      if (k === "jenis_subkont") {
        if (el.value === ff.jenis_subkont) return;
        if (ff.detail.length) { dialog({ icon: "warning", title: "Ganti Jenis Subkont?", text: "Item yang sudah ditambahkan akan dihapus karena sumber stoknya berbeda (Pack vs pcs).", ok: "Ya, ganti", cancel: "Batal" }).then((ok) => { if (ok) { ff.jenis_subkont = el.value; ff.detail = []; } U.render(); }); return; }
        ff.jenis_subkont = el.value; U.render(); return;
      }
      if (k === "qty") { ff.detail[+i].qty_subkont = Math.max(0, Math.floor(pv(el) || 0)); return; }
      if (k === "ket") { ff.detail[+i].keterangan = el.value; return; }
      ff[k] = el.value;
    },
    act: {
      back() { U.ui.mode = "list"; U.render(); }, cancelform() { U.ui.mode = "list"; U.render(); },
      view(d) { U.ui.mode = "view"; U.ui.id = d.id; U.render(); },
      add() { ff = newForm(); U.ui.mode = "form"; U.render(); },
      edit(d) { const s = W.spkOf(d.id); if (s.status !== "Draft") { warn("SPK hanya dapat diedit saat berstatus Draft."); return; } ff = { spk_no: s.spk_no, jenis_subkont: s.jenis_subkont, tgl_spk: s.tgl_spk, proses_disubkont: s.proses_disubkont, catatan: s.catatan, detail: W.deep(s.detail) }; U.ui.mode = "form"; U.render(); },
      request: (d) => doRequest(d.id), cancel: (d) => doCancel(d.id),
      additem() { if (!ff.jenis_subkont) { warn("Pilih Jenis Subkont terlebih dahulu."); return; } addItemModal(); },
      delrow(d) { ff.detail.splice(+d.id, 1); U.render(); },
      saveform(d, b) {
        const bad = (m) => dialog({ icon: "error", title: "Validasi Gagal", text: m });
        if (!ff.jenis_subkont) { bad("Jenis Subkont wajib dipilih."); return; }
        if (!ff.tgl_spk) { bad("Tgl SPK wajib diisi."); return; }
        if (!ff.detail.length) { bad("Minimal 1 item harus diisi (ADD Item)."); return; }
        for (let i = 0; i < ff.detail.length; i++) {
          const x = ff.detail[i], st = W.stok(ff.jenis_subkont, x.kode_barang);
          if (!(x.qty_subkont >= 1)) { bad(`Baris ${i + 1}: Qty diSubkont wajib diisi (minimal 1 ${unit(ff.jenis_subkont)}).`); return; }
          if (x.qty_subkont > st) { bad(`Baris ${i + 1}: Qty diSubkont (${q(ff.jenis_subkont, x.qty_subkont)}) melebihi Stok Saat Ini (${q(ff.jenis_subkont, st)}).`); return; }
        }
        const req = b && b.dataset.req === "1";
        const run = () => {
          let s;
          if (ff.spk_no) { s = W.spkOf(ff.spk_no); Object.assign(s, { jenis_subkont: ff.jenis_subkont, tgl_spk: ff.tgl_spk, proses_disubkont: ff.proses_disubkont, catatan: ff.catatan, detail: W.deep(ff.detail) }); }
          else { s = { spk_no: W.nextSpk(), jenis_subkont: ff.jenis_subkont, tgl_spk: ff.tgl_spk, proses_disubkont: ff.proses_disubkont, catatan: ff.catatan, status: "Draft", created_by: USER, detail: W.deep(ff.detail) }; S().spk.push(s); }
          W.save();
          if (req) { U.ui.mode = "list"; U.render(); doRequest(s.spk_no); return; }
          U.ui.mode = "list"; U.render(); toast(`${s.spk_no} disimpan berstatus Draft.`, "ok");
        };
        run();
      },
    },
  });

  /* ======================= 2. Tampungan Siap Kirim ======================= */
  let cur = null, scanVal = "";
  const lockedRow = (t) => t.status !== "Siap Kirim";
  const rowsOfSpk = (no) => W.tampOf(no).slice().sort((a, b) => a.id - b.id);
  function moveRow(s, d, data) {
    const t = Object.assign({ id: W.nextTampId(), spk_no: s.spk_no, kode_barang: d.kode_barang, status: "Siap Kirim", tgl_masuk: W.today(), scan_by: USER }, data);
    S().tamp.push(t); W.save(); return t;
  }
  function packRule(s, pack) {
    const p = W.packBy(pack);
    if (!String(pack || "").trim()) return { ok: false, msg: "Pack Code wajib diisi." };
    if (!p) return { ok: false, msg: `Pack "${String(pack).trim()}" tidak ditemukan.` };
    if (p.kd_gudang !== "PRO") return { ok: false, msg: `Pack ${p.pack_code} berada di gudang ${p.kd_gudang}. Hanya pack dari gudang PRO (Production 1) yang dapat disiapkan.` };
    if (W.inTamp(p.pack_code)) { const t = S().tamp.find((x) => x.pack_code === p.pack_code); return { ok: false, msg: `Pack ${p.pack_code} sudah berada di Tampungan Siap Kirim (${t.spk_no}, status ${t.status}).` }; }
    const d = s.detail.find((x) => x.kode_barang === p.kode_barang);
    if (!d) return { ok: false, msg: `Pack ${p.pack_code} (${W.nama(p.kode_barang)}) bukan item ${s.spk_no}.` };
    const pg = W.prog(s, d);
    if (pg.sisa <= 0) return { ok: false, msg: `Qty ${W.nama(d.kode_barang)} sudah terpenuhi (diminta ${q(s.jenis_subkont, d.qty_subkont)}, siap kirim + terkirim ${q(s.jenis_subkont, pg.qty_siap_kirim + pg.qty_terkirim)}).` };
    return { ok: true, p, d };
  }
  function siapkanModal(no) {
    cur = no; scanVal = "";
    openModal({
      title: "Siapkan Barang di Tampungan Siap Kirim — " + no, size: "xl", hdr: "primary",
      body: () => {
        const s = W.spkOf(no), mat = s.jenis_subkont === W.JENIS[0], t = W.total(s);
        const prog = tbl(["Kode Barang", "Nama Barang", "Qty diSubkont", "Qty Siap Kirim", "Qty Terkirim", "Sisa Belum Dikirim"], s.detail.map((d) => { const p = W.prog(s, d); return `<tr><td>${esc(d.kode_barang)}</td><td>${esc(W.nama(d.kode_barang))}</td><td class="ap-r"><b>${q(s.jenis_subkont, p.qty_subkont)}</b></td><td class="ap-r">${q(s.jenis_subkont, p.qty_siap_kirim)}</td><td class="ap-r">${q(s.jenis_subkont, p.qty_terkirim)}</td><td class="ap-r${p.sisa ? "" : " wh-diff-ok"}">${q(s.jenis_subkont, p.sisa)}</td></tr>`; }).join(""));
        let input;
        if (mat) {
          const av = s.detail.flatMap((d) => W.packsAvail(d.kode_barang).map((p) => ({ p, d })));
          input = `<div class="wh-scanbox"><div style="margin-bottom:6px"><b>Scan QR / ketik Pack Code</b></div><div style="display:flex;gap:6px;justify-content:center"><input class="ap-in" data-enter="scan" data-f="scan" placeholder="mis. PCK-2609-001" value="${esc(scanVal)}" autocomplete="off"><button type="button" class="ap-btn ap-btn--primary" data-act="scan">Scan</button></div></div>
            <div class="pr-sub-h"><h6>Pilih dari Stok (pack di gudang PRO yang sesuai item SPK)</h6></div>` +
            tbl(["Pack Code", "Nama Barang", "Kode Internal", "Net Weight", "Aksi"], av.map(({ p }) => `<tr><td>${pill(p.pack_code)}</td><td>${esc(W.nama(p.kode_barang))}</td><td>${esc(p.kode_internal)}</td><td class="ap-r">${num(p.net_weight)}</td><td>${btn("success", "pickpack", p.pack_code, "Pindahkan ke Tampungan")}</td></tr>`).join(""), "Tidak ada pack tersedia untuk item SPK ini.");
        } else {
          input = tbl(["Kode Barang", "Nama Barang", "Stok Saat Ini", "Sisa Belum Dikirim", "Qty Dipindahkan (pcs)", "Aksi"], s.detail.map((d, i) => { const p = W.prog(s, d), st = W.stok(s.jenis_subkont, d.kode_barang); return `<tr><td>${esc(d.kode_barang)}</td><td>${esc(W.nama(d.kode_barang))}</td><td class="ap-r">${q(s.jenis_subkont, st)}</td><td class="ap-r">${q(s.jenis_subkont, p.sisa)}</td><td style="width:170px">${U.inp("fgq|" + i, "", { num: 1 })}</td><td>${btn("success", "pickfg", i, "Pindahkan ke Tampungan")}</td></tr>`; }).join(""));
        }
        const rows = rowsOfSpk(no);
        const mine = tbl(["No", "Pack Code", "Nama Barang", "Qty", "Net Weight", "Status", "Tgl Masuk", "Scan By", "Aksi"], rows.map((r, i) => `<tr><td>${i + 1}</td><td>${r.pack_code ? pill(r.pack_code) : "-"}</td><td>${esc(W.nama(r.kode_barang))}</td><td class="ap-r">${q(s.jenis_subkont, r.qty)}</td><td class="ap-r">${num(r.net_weight)}</td><td>${stl(r.status)}</td><td>${fdate(r.tgl_masuk)}</td><td>${esc(r.scan_by)}</td><td>${lockedRow(r) ? '<span class="ap-hint">terkunci</span>' : btn("danger", "delrow", r.id, "Hapus")}</td></tr>`).join(""), "Belum ada barang di Tampungan untuk SPK ini.");
        return `<div class="wh-need${t.sisa === 0 ? " is-ok" : ""}"><b>${esc(s.jenis_subkont)}</b> &bull; Tujuan Incoming: ${esc(W.TUJUAN[s.jenis_subkont])} &bull; diminta ${q(s.jenis_subkont, t.qty_subkont)}, siap kirim ${q(s.jenis_subkont, t.qty_siap_kirim)}, terkirim ${q(s.jenis_subkont, t.qty_terkirim)}, sisa ${q(s.jenis_subkont, t.sisa)}</div>${prog}
          <div class="wh-sec">Tambah ke Tampungan (boleh sebagian)</div>${input}<div class="wh-sec">Isi Tampungan SPK ini</div>${mine}<p class="note">Tampungan hanya pemisahan fisik: akun tetap Inventory, tidak ada jurnal. Qty yang disiapkan tidak boleh melebihi qty diminta SPK. Baris yang sudah Terkunci di Surat Jalan atau Terkirim tidak dapat dihapus.</p>`;
      },
      onField(el) { if (el.dataset.f === "scan") scanVal = el.value; },
      act: {
        scan() {
          const s = W.spkOf(cur), r = packRule(s, scanVal);
          if (!r.ok) { dialog({ icon: "error", title: "Scan Pack Ditolak", text: r.msg }); return; }
          moveRow(s, r.d, { pack_code: r.p.pack_code, qty: 1, net_weight: r.p.net_weight });
          scanVal = ""; U.drawModal(); U.render(); toast(`Pack ${r.p.pack_code} dipindahkan ke Tampungan Siap Kirim untuk ${s.spk_no}.`, "ok");
        },
        pickpack(d) {
          const s = W.spkOf(cur), r = packRule(s, d.id);
          if (!r.ok) { dialog({ icon: "error", title: "Tidak dapat dipindahkan", text: r.msg }); return; }
          moveRow(s, r.d, { pack_code: r.p.pack_code, qty: 1, net_weight: r.p.net_weight });
          U.drawModal(); U.render(); toast(`Pack ${r.p.pack_code} dipindahkan ke Tampungan Siap Kirim.`, "ok");
        },
        pickfg(d) {
          const s = W.spkOf(cur), item = s.detail[+d.id], el = document.querySelector(`#pcModal [data-f="fgq|${d.id}"]`), n = Math.floor(el ? pv(el) || 0 : 0);
          const p = W.prog(s, item), st = W.stok(s.jenis_subkont, item.kode_barang);
          if (!(n >= 1)) { dialog({ icon: "warning", title: "Perhatian", text: "Isi Qty Dipindahkan (minimal 1 pcs)." }); return; }
          if (n > p.sisa) { dialog({ icon: "error", title: "Melebihi qty diminta", text: `Qty ${q(s.jenis_subkont, n)} melebihi sisa belum dikirim (${q(s.jenis_subkont, p.sisa)}).` }); return; }
          if (n > st) { dialog({ icon: "error", title: "Stok tidak cukup", text: `Qty ${q(s.jenis_subkont, n)} melebihi Stok Saat Ini (${q(s.jenis_subkont, st)}).` }); return; }
          const berat = (W.FG.find((f) => f.kode_barang === item.kode_barang) || { berat_referensi: 0 }).berat_referensi;
          moveRow(s, item, { pack_code: "", qty: n, net_weight: W.round(n * berat) });
          U.drawModal(); U.render(); toast(`${q(s.jenis_subkont, n)} ${W.nama(item.kode_barang)} dipindahkan ke Tampungan Siap Kirim.`, "ok");
        },
        delrow(d) { const t = S().tamp.find((x) => x.id === +d.id); if (!t || lockedRow(t)) return; S().tamp = S().tamp.filter((x) => x.id !== t.id); W.save(); U.drawModal(); U.render(); toast("Baris dihapus dari Tampungan; barang kembali ke stok gudang.", "ok"); },
      },
    });
  }
  P.tamp = Object.assign({}, base, {
    title: "Tampungan Siap Kirim", mod: "warehouse (Tampungan Siap Kirim)", role: "Gudang / Staf Gudang", ui: { tab: "spk", q: "" },
    hint: "Gudang menyiapkan barang SPK Subkont yang sudah Requested ke Tampungan Siap Kirim (scan QR / pilih), boleh sebagian, untuk Material (Pack) maupun FG (pcs).",
    render() {
      const u = U.ui, ql = (u.q || "").toLowerCase();
      const req = S().spk.filter((s) => ["Requested", "Dikirim Sebagian"].includes(s.status));
      const nav = tabs([["spk", `Permintaan SPK <span class="wh-tabs-count">${req.length}</span>`], ["isi", `Isi Tampungan <span class="wh-tabs-count">${S().tamp.filter((t) => t.status !== "Terkirim").length}</span>`]], u.tab);
      const search = `<div class="cl-tools"><input class="ap-in" data-u="q" placeholder="Search..." value="${esc(u.q)}"></div>`;
      if (u.tab === "isi") {
        const rows = S().tamp.filter((t) => !ql || (t.spk_no + " " + t.pack_code + " " + W.nama(t.kode_barang)).toLowerCase().includes(ql)).sort((a, b) => b.id - a.id);
        return nav + card("Isi Tampungan Siap Kirim", search + tbl(["No", "SPK No", "Jenis Subkont", "Pack Code", "Nama Barang", "Qty", "Net Weight", "Status", "Tgl Masuk", "Scan By", "Aksi"],
          rows.map((r, i) => { const s = W.spkOf(r.spk_no); return `<tr><td>${i + 1}</td><td>${esc(r.spk_no)}</td><td>${esc(s.jenis_subkont)}</td><td>${r.pack_code ? pill(r.pack_code) : "-"}</td><td>${esc(W.nama(r.kode_barang))}</td><td class="ap-r">${q(s.jenis_subkont, r.qty)}</td><td class="ap-r">${num(r.net_weight)}</td><td>${stl(r.status)}</td><td>${fdate(r.tgl_masuk)}</td><td>${esc(r.scan_by)}</td><td>${lockedRow(r) ? '<span class="ap-hint">terkunci</span>' : btn("danger", "del", r.id, "Hapus")}</td></tr>`; }).join(""), "Tampungan kosong.")) +
          '<p class="note">Status baris: Siap Kirim (bisa dihapus), Terkunci di Surat Jalan (sudah masuk Surat Jalan Draft), Terkirim (Surat Jalan Confirmed). Pengunci/pengirim ditangani Surat Jalan (tahap berikutnya).</p>';
      }
      const l = req.filter((s) => !ql || (s.spk_no + " " + s.jenis_subkont).toLowerCase().includes(ql));
      return nav + card("Permintaan SPK Subkont (Requested)", search + tbl(["No", "SPK No", "Tgl SPK", "Jenis Subkont", "Tujuan Incoming", "Status", "Qty diSubkont", "Qty Siap Kirim", "Qty Terkirim", "Sisa Belum Dikirim", "Aksi"],
        l.map((s, i) => { const t = W.total(s); return `<tr><td>${i + 1}</td><td style="white-space:nowrap"><b>${esc(s.spk_no)}</b></td><td style="white-space:nowrap">${fdate(s.tgl_spk)}</td><td>${esc(s.jenis_subkont)}</td><td>${esc(W.TUJUAN[s.jenis_subkont])}</td><td style="text-align:center">${stl(s.status)}</td><td class="ap-r">${q(s.jenis_subkont, t.qty_subkont)}</td><td class="ap-r">${q(s.jenis_subkont, t.qty_siap_kirim)}</td><td class="ap-r">${q(s.jenis_subkont, t.qty_terkirim)}</td><td class="ap-r">${q(s.jenis_subkont, t.sisa)}</td><td class="ap-act ap-act-nowrap">${btn("primary", "siapkan", s.spk_no, "Siapkan")}</td></tr>`; }).join(""), "Tidak ada permintaan SPK Subkont.")) +
        '<p class="note">Hanya SPK berstatus Requested atau Dikirim Sebagian yang tampil. Coba scan Pack Code contoh: PCK-2609-002 (sesuai SPK-Sub-003), PCK-2609-099 (di gudang SLI, ditolak), PCK-2609-011 (material lain, ditolak).</p>';
    },
    act: {
      siapkan: (d) => siapkanModal(d.id),
      del(d) { const t = S().tamp.find((x) => x.id === +d.id); if (!t || lockedRow(t)) return; confirmThen({ title: "Hapus dari Tampungan?", text: "Barang kembali ke stok gudang dan Qty Siap Kirim SPK berkurang.", ok: "Ya, Hapus", cancel: "Batal" }, () => { S().tamp = S().tamp.filter((x) => x.id !== t.id); W.save(); U.render(); toast("Baris dihapus dari Tampungan.", "ok"); }); },
    },
  });


})();
