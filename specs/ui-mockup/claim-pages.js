/* ============================================================
   claim-pages.js — Halaman Modul Claim Material (FEAT-11). Satu file untuk semua halaman claim-*.html;
   <body data-cpage="..."> memilih halaman. Kerangka sidebar/top bar: berry-shell.js (auto-mount).
   Acuan: BPMN D003 + workbook Rev.2 (input/Claim Process/), FR-75..FR-94 di docs/fr-nfr.md.
   ============================================================ */
(() => {
  "use strict";
  const CL = window.CL;
  const S = () => CL.state;
  const $ = (s, r = document) => r.querySelector(s);
  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));
  const num = (n, d = 2) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: d, maximumFractionDigits: d });
  const rp = (n) => "Rp " + num(n);
  const MON = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
  const fdate = (iso) => { if (!iso) return "-"; const [y, m, d] = iso.split("-"); return `${d} ${MON[+m - 1]} ${y}`; };
  const LAB = { success: ["SJ CONFIRMED", "COMPLETED", "Sudah Terima Uang", "Dapat Dipakai (Release)", "Terposting"],
    warning: ["Hold", "WAITING", "WAITING SJ", "Belum Terbit", "Baru", "Belum ditagih"],
    info: ["ON PROGRESS", "NOT CONFIRMED", "Email Terkirim", "Belum Terima Uang", "Sudah Terbit", "Sudah ditagih", "Kompensasi", "Retur", "Kompensasi + Retur", "Sudah Alokasi ke Hutang"] };
  const lab = (s) => { const k = Object.keys(LAB).find((x) => LAB[x].includes(s)) || "secondary"; return `<span class="ap-label ap-label--${k}">${esc(s || "-")}</span>`; };
  const btn = (cls, act, id, label, extra = "") => `<button type="button" class="ap-btn ap-btn--sm ap-btn--${cls}" data-act="${act}" data-id="${esc(id)}"${extra}>${label}</button>`;
  const tbl = (heads, rows, empty) => `<div class="ap-tablewrap"><table class="ap-table ap-table--striped ap-table--hover"><thead><tr>${heads.map((h) => `<th>${h}</th>`).join("")}</tr></thead><tbody>${rows || `<tr><td class="ap-empty" colspan="${heads.length}">${empty || "Belum ada data."}</td></tr>`}</tbody></table></div>`;
  const card = (title, body, head = "") => `<section class="ap-card ap-card--shadow"><div class="ap-card__head ap-card__head--bar"><h5>${title}</h5>${head}</div><div class="ap-card__body">${body}</div></section>`;
  const stat = (v, k) => `<div class="stat"><div class="stat__v">${v}</div><div class="stat__k">${k}</div></div>`;
  const row = (label, field, req) => `<div class="ap-row"><div class="c3"><label class="ap-lbl">${label}${req ? ' <span class="ap-req">*</span>' : ""}</label></div><div class="c9" style="grid-column: span 9">${field}</div></div>`;
  const inp = (f, v, o = {}) => `<input class="ap-in ${o.num ? "ap-in--num" : ""} ${o.ro ? "ap-in--ro" : ""}" type="${o.num ? "text" : (o.type || "text")}"${o.num ? ' inputmode="decimal" data-numfmt="1"' : ""} data-f="${f}" ${o.di != null ? `data-di="${o.di}"` : ""} value="${esc(o.num ? NumFmt.fmt(v) : v)}"${o.ro ? " readonly" : ""}${o.ph ? ` placeholder="${esc(o.ph)}"` : ""}>`;
  const sel = (f, v, opts, o = {}) => `<select class="ap-sel ap-in" data-f="${f}" ${o.di != null ? `data-di="${o.di}"` : ""}${o.struct ? " data-struct" : ""}>${opts.map((x) => `<option value="${esc(x)}"${x === v ? " selected" : ""}>${esc(x)}</option>`).join("")}</select>`;
  const txt = (f, v, o = {}) => `<textarea class="ap-in" data-f="${f}" ${o.di != null ? `data-di="${o.di}"` : ""} placeholder="${esc(o.ph || "")}">${esc(v)}</textarea>`;
  const isNumEl = (el) => el.type === "number" || el.dataset.numfmt === "1";
  const pv = (el) => parseFloat(el.dataset.numfmt === "1" ? NumFmt.norm(el.value) : el.value);
  const kb = (n) => (n >= 1048576 ? (n / 1048576).toFixed(1) + " MB" : Math.max(1, Math.round(n / 1024)) + " KB");
  /* ---- bukti pendukung: pratinjau + unduh (berkas tersimpan di IndexedDB browser; data contoh dibuat saat dibuka) ---- */
  const mkPdf = (t) => { const s = `BT /F1 20 Tf 50 760 Td (${t.replace(/[()\\]/g, " ")}) Tj ET`; const objs = ["<< /Type /Catalog /Pages 2 0 R >>", "<< /Type /Pages /Kids [3 0 R] /Count 1 >>", "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>", `<< /Length ${s.length} >>\nstream\n${s}\nendstream`, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>"];
    let out = "%PDF-1.4\n"; const off = []; objs.forEach((o, i) => { off.push(out.length); out += `${i + 1} 0 obj\n${o}\nendobj\n`; }); const x = out.length;
    out += `xref\n0 ${objs.length + 1}\n0000000000 65535 f \n` + off.map((o) => String(o).padStart(10, "0") + " 00000 n \n").join("") + `trailer\n<< /Size ${objs.length + 1} /Root 1 0 R >>\nstartxref\n${x}\n%%EOF`;
    return new Blob([out], { type: "application/pdf" }); };
  const sampleBlob = (b) => new Promise((res) => {
    if (/\.pdf$/i.test(b.nama)) { res(mkPdf("CONTOH BUKTI - " + b.nama)); return; }
    const cv = document.createElement("canvas"); cv.width = 640; cv.height = 400; const g = cv.getContext("2d");
    const gr = g.createLinearGradient(0, 0, 640, 400); gr.addColorStop(0, "#cbd5e1"); gr.addColorStop(1, "#94a3b8"); g.fillStyle = gr; g.fillRect(0, 0, 640, 400);
    g.fillStyle = "#334155"; g.font = "bold 30px sans-serif"; g.fillText("CONTOH BUKTI (data contoh)", 40, 180); g.font = "20px sans-serif"; g.fillText(b.nama, 40, 225);
    cv.toBlob((x) => res(x), "image/jpeg", 0.85);
  });
  const buktiBlob = async (b) => { if (b.fid) { const x = await CL.files.get(b.fid); if (x) return { blob: x, sample: false }; } return { blob: await sampleBlob(b), sample: true }; };
  const buktiHtml = (c, preview) => ((c.bukti || []).length ? c.bukti.map((b, i) => `<div class="cl-bk" data-bk="${esc(c.id)}:${i}" data-pv="${preview ? 1 : 0}">📎 ${esc(b.nama)} <span class="ap-muted">(${kb(b.ukuran)})</span> <span class="ap-muted">memuat...</span></div>`).join("") : "-");
  async function fillBukti(root) {
    for (const el of root.querySelectorAll("[data-bk]")) {
      const [cid, i] = el.dataset.bk.split(":"), c = CL.claim(cid), b = c && c.bukti[+i]; if (!b) continue;
      const { blob, sample } = await buktiBlob(b), pdf = /\.pdf$/i.test(b.nama) || /pdf/.test(blob.type), img = /^image\//.test(blob.type) || /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(b.nama), vid = /^video\//.test(blob.type) || /\.(mp4|webm|mov|ogv|m4v)$/i.test(b.nama);
      const url = URL.createObjectURL(pdf && blob.type !== "application/pdf" ? new Blob([blob], { type: "application/pdf" }) : blob), pv = el.dataset.pv === "1";
      el.innerHTML = `📎 <b>${esc(b.nama)}</b> <span class="ap-muted">(${kb(b.ukuran)}${sample ? ", contoh" : ""})</span> &middot; <a class="ap-link" href="${url}" download="${esc(b.nama)}">Unduh</a> &middot; <a class="ap-link" href="${url}" target="_blank" rel="noopener">Buka di tab baru</a>`
        + (pv ? (img ? `<img class="cl-bk__img" src="${url}" alt="${esc(b.nama)}">` : vid ? `<video class="cl-bk__vid" src="${url}" controls preload="metadata"></video>` : pdf ? `<iframe class="cl-bk__pdf" src="${url}" title="${esc(b.nama)}"></iframe>` : '<div class="ap-hint">Pratinjau tidak tersedia untuk jenis berkas ini; gunakan Unduh.</div>') : "");
    }
  }
  /* ---- dokumen cetak (SPK Re-Pack, Surat Jalan, Debit Note): pratinjau layar + tombol Cetak ---- */
  const LOGO = "../../assets/images/iconhfg.png";
  function printDoc(title, inner) {
    const h = $("#pcPrint"); h.hidden = false;
    h.innerHTML = `<div class="pp-bar"><span>Pratinjau Cetak — ${esc(title)}</span><span><button type="button" class="ap-btn ap-btn--primary" id="ppDo">Cetak</button> <button type="button" class="ap-btn ap-btn--dark" id="ppX">Tutup</button></span></div><div class="pp-sheet">${inner}</div>`;
    $("#ppDo").onclick = () => window.print();
    $("#ppX").onclick = () => { h.hidden = true; h.innerHTML = ""; };
  }
  const ppHead = (title, no) => `<div class="pp-head"><img class="pp-logo" src="${LOGO}" alt="HFG"><div class="pp-co"><b>PT HFG</b><div>Dokumen contoh (mockup)</div></div><div class="pp-title"><h2>${esc(title)}</h2><div>No. ${esc(no || "-")}</div></div></div>`;
  const ppMeta = (pairs) => `<table class="pp-meta">${pairs.map((p) => `<tr><td class="pp-k">${p[0]}</td><td>:</td><td>${p[1]}</td></tr>`).join("")}</table>`;
  const ppSign = (names) => `<div class="pp-sign">${names.map((n) => `<div><div>${esc(n)}</div><div class="ln">( ................................ )</div><div class="pp-tgl">Tanggal: ........................</div></div>`).join("")}</div>`;
  const ppItems = (items, withTotal) => { const tot = items.reduce((t, i) => t + i.kg, 0);
    return `<table class="pp-tbl"><thead><tr><th>No</th><th>Nama Material</th><th>No. Coil Packing List</th><th>No. PO</th><th class="pp-r">Berat (Kg)</th><th>Problem</th></tr></thead><tbody>${items.map((i, n) => `<tr><td>${n + 1}</td><td>${esc(i.material)}</td><td>${esc(i.coil)}</td><td>${esc(i.po)}</td><td class="pp-r">${num(i.kg, 0)}</td><td>${esc(i.problem)}</td></tr>`).join("")}</tbody>${withTotal ? `<tfoot><tr><td colspan="4" class="pp-r"><b>Total</b></td><td class="pp-r"><b>${num(tot, 0)}</b></td><td></td></tr></tfoot>` : ""}</table>`; };
  const kv = (pairs) => `<dl class="cl-kv">${pairs.map((p) => `<dt>${p[0]}</dt><dd>${p[1]}</dd>`).join("")}</dl>`;
  const jr = (j) => {
    const db = j.lines.reduce((a, l) => a + l[1], 0), cr = j.lines.reduce((a, l) => a + l[2], 0), ok = Math.abs(db - cr) < 0.005;
    return `<div class="cl-jr"><h6>${esc(j.title)}</h6><table class="ap-table ap-table--bordered ap-table--sm"><thead><tr><th>COA</th><th class="ap-r">Debit</th><th class="ap-r">Kredit</th></tr></thead><tbody>${j.lines.map((l) => `<tr><td>${esc(l[0])}</td><td class="ap-r">${l[1] ? num(l[1]) : ""}</td><td class="ap-r">${l[2] ? num(l[2]) : ""}</td></tr>`).join("")}</tbody><tfoot><tr><td>Total ${ok ? '<span class="cl-ok">&#10003; seimbang</span>' : '<span class="cl-bad">tidak seimbang</span>'}</td><td class="ap-r">${num(db)}</td><td class="ap-r">${num(cr)}</td></tr></tfoot></table></div>`;
  };
  const itemsTbl = (items) => tbl(["No", "Nama Material", "No. Coil Packing List", "No. PO", "Berat (Kg)", "Problem"],
    items.map((i, n) => `<tr><td>${n + 1}</td><td>${esc(i.material)}</td><td>${esc(i.coil)}</td><td>${esc(i.po)}</td><td class="ap-r">${num(i.kg, 0)}</td><td>${esc(i.problem)}</td></tr>`).join(""));

  /* ---------- host: toast, modal, dialog ---------- */
  let toastT = null;
  function toast(msg, kind) { const t = $("#toast"); t.textContent = msg; t.className = "toast" + (kind ? " toast--" + kind : ""); t.hidden = false; clearTimeout(toastT); toastT = setTimeout(() => (t.hidden = true), 3200); }
  let modal = null;
  function openModal(cfg) { modal = cfg; drawModal(); }
  function closeModal() { modal = null; drawModal(); }
  function drawModal() {
    const h = $("#pcModal");
    const keep = h.querySelector(".ap-modal__body");
    const top = keep ? keep.scrollTop : 0;
    if (!modal) { h.hidden = true; h.innerHTML = ""; return; }
    h.hidden = false;
    h.innerHTML = `<div class="modal__overlay" data-act="mclose"></div>
      <div class="ap-modal ap-modal--${modal.size || "lg"} ap-modal--hdr-primary" role="dialog" aria-modal="true">
        <div class="ap-modal__head"><h4>${esc(modal.title)}</h4><button type="button" class="ap-modal__x" data-act="mclose" aria-label="Close">&#10005;</button></div>
        <div class="ap-modal__body">${modal.body()}</div>
        <div class="ap-modal__foot">${modal.ok ? `<button type="button" class="ap-btn ap-btn--primary" data-act="mok">${esc(modal.ok)}</button>` : ""}<button type="button" class="ap-btn ap-btn--dark" data-act="mclose">${modal.ok ? "Cancel" : "Tutup"}</button></div>
      </div>`;
    h.querySelector(".ap-modal__body").scrollTop = top;
    calc();
    if (modal.after) modal.after(h);
  }
  function calc() { document.querySelectorAll("#pcModal [data-calc]").forEach((el) => { const f = modal && modal.calc && modal.calc[el.dataset.calc]; if (f) el.innerHTML = f(el.dataset.arg); }); }
  function dialog(cfg) {
    return new Promise((resolve) => {
      const h = $("#pcDialog"); h.hidden = false;
      const sym = { warning: "!", success: "&#10003;", error: "&#10005;" }[cfg.icon || "warning"];
      const done = (v) => { h.hidden = true; h.innerHTML = ""; resolve(v); };
      h.innerHTML = `<div class="ap-dlg__overlay"></div><div class="ap-dlg" role="alertdialog"><div class="ap-dlg__icon ap-dlg__icon--${cfg.icon || "warning"}">${sym}</div><h3>${esc(cfg.title)}</h3>${cfg.text ? `<p>${esc(cfg.text)}</p>` : ""}<div class="ap-dlg__btns">${cfg.cancel ? `<button type="button" class="ap-btn ap-btn--secondary" data-d="0">${esc(cfg.cancel)}</button>` : ""}<button type="button" class="ap-btn ap-btn--primary" data-d="1">${esc(cfg.ok || "OK")}</button></div></div>`;
      h.querySelectorAll("[data-d]").forEach((b) => b.addEventListener("click", () => done(b.dataset.d === "1")));
    });
  }
  const warn = (text) => dialog({ icon: "warning", title: "Error Message!", text });
  const confirmThen = (cfg, fn) => dialog(Object.assign({ icon: "warning", title: "Are you sure?", ok: "Ya, Proses!", cancel: "Batal" }, cfg)).then((ok) => { if (ok) fn(); });
  const done = (text) => dialog({ icon: "success", title: "Success", text });

  const progHtml = (c) => {
    const p = CL.progres(c);
    if (!p.length) return '<span class="ap-hint">Belum diputuskan</span>';
    return p.map((x) => `<div class="cl-prog ${x.done ? "is-done" : ""}">${x.done ? "&#10003;" : "&#9675;"} <b>${esc(x.aksi)}</b>: ${esc(x.text)}</div>`).join("") + `<div class="ap-hint">${p.filter((x) => x.done).length}/${p.length} selesai</div>`;
  };
  const ASSUME = { oqc3: "Pemetaan akun kredit (dikonfirmasi user 2026-10-27, OQ-C3): asal <b>KW 2 Supplier</b> → Biaya KW 2 Supplier; asal <b>Hold Coil / Reject Supplier</b> → Biaya Scrap." };
  const P = {};   // definisi halaman: { title, hint, role, fr, head(), render(), act:{} }

  /* ======================= 1. Material On Hold (FR-75) ======================= */
  P.onhold = {
    title: "Gudang Coil — Material On Hold", role: "Purchasing / PPIC", fr: "FR-75",
    hint: "Tampungan material berstatus HOLD dari Laporan Produksi; tidak dijurnal sampai claim diputuskan.",
    render() {
      const rows = S().claims.filter((c) => c.asal === "Hold Coil");
      const hold = rows.filter((c) => CL.holdStatus(c) === "Hold");
      return `<div class="cl-sumrow">${stat(hold.length, "Coil berstatus Hold")}${stat(num(hold.reduce((a, c) => a + c.kg, 0), 0) + " Kg", "Total berat Hold")}${stat(rows.length - hold.length, "Sudah diputuskan (claim)")}</div>
        <div class="cl-tabs"><a class="is-off" data-act="stock">Stock</a><a class="is-on">Material On Hold</a></div>
        ${card("Material On Hold", tbl(["No", "Nama Supplier", "Nama Material", "No. Coil Packing List", "No. PO", "Berat (Kg)", "Tgl Hold", "Alasan Produksi", "Status Material", "Action"],
          rows.map((c, i) => `<tr><td>${i + 1}</td><td>${esc(c.supplier)}</td><td>${esc(c.material)}</td><td>${esc(c.coil)}</td><td>${esc(c.po)}</td><td class="ap-r">${num(c.kg, 0)}</td><td>${fdate(c.tglProduksi)}</td><td>${esc(c.alasan)}</td><td>${lab(CL.holdStatus(c))}</td><td class="ap-act"><a class="ap-btn ap-btn--sm ap-btn--info" href="claim-material.html">Lihat Claim</a></td></tr>`).join(""), "Tidak ada material On Hold."))}
        <p class="note">Tab <b>Stock</b> adalah tampilan existing Warehouse &gt; Gudang Coil &gt; Stock (tidak diubah); tab <b>Material On Hold</b> ditambahkan sesuai workbook sheet "Tampungan Hold". Status berubah otomatis mengikuti keputusan di Daftar Claim Material.</p>`;
    },
    act: { stock: () => toast("Tab Stock = tampilan Stock Gudang Coil yang sudah ada di aplikasi (tidak dimockup ulang).") },
  };

  /* ======================= 1b. Material Dijual — Gudang Slitting (FR-96) ======================= */
  P.slitting = {
    title: "Material Dijual — Gudang Slitting", role: "Purchasing / PPIC", fr: "FR-96",
    hint: "Daftar baca material hasil claim yang diputuskan Jual Material (Inventory Material Khusus Dijual).",
    render() {
      const l = S().jual, k = (x) => S().claims.find((c) => c.id === x.claim);
      const sum = (f) => l.reduce((a, x) => a + f(x), 0);
      return `<div class="cl-sumrow">${stat(l.length, "Baris material")}${stat(num(sum((x) => x.kg), 0) + " Kg", "Total berat")}${stat(rp(sum((x) => x.nilai)), "Total nilai material")}</div>
        ${card("Material Khusus Dijual (Gudang Slitting)", tbl(["No", "Tgl Masuk", "No. Claim", "Nama Supplier", "Nama Material", "No. Coil Packing List", "Berat (Kg)", "Nilai Material (Rp)", "Nilai Material KW (Rp)", "Nilai Biaya Scrap (Rp)"],
          l.map((x, i) => `<tr><td>${i + 1}</td><td>${fdate(x.tgl)}</td><td>${esc(x.claim)}</td><td>${esc(k(x) ? k(x).supplier : "-")}</td><td>${esc(x.material)}</td><td>${esc(x.coil)}</td><td class="ap-r">${num(x.kg, 0)}</td><td class="ap-r">${num(x.nilai)}</td><td class="ap-r">${num(x.materialKw)}</td><td class="ap-r">${num(CL.round2(x.nilai - x.materialKw))}</td></tr>`).join(""),
          "Belum ada material dijual. Baris muncul otomatis saat Process Decision memilih Kompensasi → Jual Material."))}
        <p class="note">Halaman baca saja. Nilai Material = Berat × Harga Beli/Kg; Material KW diakui sebagai inventory, sisanya Biaya Scrap (FR-81). Pengelolaan stok selanjutnya milik modul gudang yang ada.</p>`;
    },
    act: {},
  };

  /* ======================= 2. Daftar Claim Material (FR-76..FR-82) ======================= */
  const flt = { sup: "", asal: "" };
  const selClaims = new Set();   // claim terpilih untuk Process Decision bersama
  let visUndecided = [];
  const emailOf = (n) => "claim@" + n.toLowerCase().replace(/^pt\s+/, "").replace(/[^a-z0-9]+/g, "") + ".example";
  P.claim = {
    title: "Daftar Claim Material", role: "Purchasing", fr: "FR-76–FR-82",
    hint: "Baris KW 2 Supplier, Hold Coil, dan Reject Supplier dari Laporan Produksi. Kirim email ke supplier, lalu tetapkan Process Decision (Kompensasi / Retur).",
    render() {
      const all = S().claims, list = all.filter((c) => (!flt.sup || c.supplier === flt.sup) && (!flt.asal || c.asal === flt.asal));
      const sups = [...new Set(all.map((c) => c.supplier))];
      visUndecided = list.filter((c) => !c.decisions.length).map((c) => c.id);
      [...selClaims].forEach((id) => { if (!all.some((c) => c.id === id && !c.decisions.length)) selClaims.delete(id); });
      const allSel = visUndecided.length > 0 && visUndecided.every((id) => selClaims.has(id));
      return `<div class="cl-sumrow">${stat(all.filter((c) => c.status === "Baru").length, "Belum diemail")}${stat(all.filter((c) => !c.decisions.length).length, "Menunggu Process Decision")}${stat(all.filter((c) => c.decisions.length).length, "Sudah diputuskan")}</div>
        ${card("Claim ke Supplier", `<div class="cl-tools"><label>Supplier : <select class="ap-sel ap-in" data-flt="sup"><option value="">(Semua supplier)</option>${sups.map((s) => `<option${s === flt.sup ? " selected" : ""}>${esc(s)}</option>`).join("")}</select></label>
          <label>Asal : <select class="ap-sel ap-in" data-flt="asal"><option value="">(Semua)</option>${["KW 2 Supplier", "Hold Coil", "Reject Supplier"].map((s) => `<option${s === flt.asal ? " selected" : ""}>${s}</option>`).join("")}</select></label>
            <button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="decideSel"${selClaims.size ? "" : " disabled"}>Process Decision (Terpilih: ${selClaims.size})</button></div>
          <div class="ap-hint" style="margin-bottom:8px">Centang beberapa claim satu supplier (mis. KW 2 dan Hold dari SPK yang sama), lalu Process Decision (Terpilih) untuk memutuskan bersamaan dengan satu kesepakatan.</div>
          ${tbl([`<input type="checkbox" data-sel-all title="Pilih semua yang belum diputuskan"${allSel ? " checked" : ""}${visUndecided.length ? "" : " disabled"}>`, "No", "Nama Supplier", "Nama Material", "No. Coil Packing List", "No. PO", "Berat (Kg)", "Problem", "Asal", "Status Claim", "Progres", "Action"],
            list.map((c, i) => `<tr><td>${c.decisions.length ? "" : `<input type="checkbox" data-sel="${esc(c.id)}"${selClaims.has(c.id) ? " checked" : ""}>`}</td><td>${i + 1}</td><td>${esc(c.supplier)}</td><td>${esc(c.material)}</td><td>${esc(c.coil)}</td><td>${esc(c.po)}</td><td class="ap-r">${num(c.kg, 0)}</td><td>${esc(c.problem)}</td><td>${esc(c.asal)}</td><td>${lab(c.status)}</td><td>${progHtml(c)}</td>
              <td class="ap-act">${btn("info", "email", c.id, "Email")}${btn(c.decisions.length ? "light" : "primary", "decide", c.id, c.decisions.length ? "View Decision" : "Process Decision")}<br><a class="ap-link" data-act="alasan" data-id="${c.id}">View Alasan Hold dari Produksi${(c.bukti || []).length ? ` (📎${c.bukti.length})` : ""}</a>${c.decisions.length ? `<br><a class="ap-link" data-act="jurnal" data-id="${c.id}">Lihat Jurnal</a>` : ""}</td></tr>`).join(""), "Tidak ada claim.")}`)}
        <p class="note">Purchasing memproses claim (BPMN D003; OQ-C1 dikonfirmasi). Status Claim (OQ-C5, dikonfirmasi): Baru → Email Terkirim → Kompensasi / Retur / Kompensasi + Retur.</p>`;
    },
    act: {
      jurnal(d) {
        const c = CL.claim(d.id);
        openModal({ title: "Jurnal Claim — " + c.id, size: "lg", body: () => {
          const ev = CL.jurnalOf(c.id), n = ev.filter((e) => e.posted).length;
          return kv([["Nama Supplier", esc(c.supplier)], ["Nama Material", esc(c.material + " — " + c.coil)], ["Asal", esc(c.asal)], ["Peristiwa terjurnal", n + " dari " + ev.length]])
            + (ev.length ? ev.map((e, i) => `<div class="cl-jev"><div class="cl-jev__h"><b>${i + 1}. ${esc(e.ev)}</b> <span class="ap-muted">${esc(e.ref)}</span> ${lab(e.posted ? "Terposting" : "Belum terjadi")}</div>${e.note ? `<p class="note">${esc(e.note)}</p>` : ""}${jr(e.j)}</div>`).join("") : '<p class="note">Belum ada peristiwa yang menghasilkan jurnal.</p>')
            + '<p class="note">Jurnal "Belum terjadi" adalah proyeksi dari nilai saat ini; baru terposting ke GL saat peristiwanya dilakukan. Debit Note Retur tidak menghasilkan jurnal saat terbit. COA mengikuti Master Jurnal Claim (dibuat kemudian).</p>';
        } });
      },
      alasan(d) {
        const c = CL.claim(d.id);
        openModal({ title: "Alasan Produksi — " + c.id, size: "lg", body: () => kv([["Laporan Produksi (SPK)", esc(c.spk)], ["Tgl Produksi", fdate(c.tglProduksi)], ["Asal", esc(c.asal)], ["Problem", esc(c.problem)], ["Berat (Kg)", num(c.kg, 0)], ["Alasan Produksi", esc(c.alasan)], ["Bukti Pendukung", buktiHtml(c, true)]]) + '<p class="note">Bukti diunggah Produksi saat Hold &amp; Claim dan tetap tersimpan bersama claim sampai claim selesai; tidak dapat dihapus (FR-94). Gambar, video, dan PDF tampil langsung, semua berkas dapat diunduh. Di mockup berkas disimpan di browser ini (IndexedDB), bukan di server.</p>', after: fillBukti });
      },
      email(d) {
        const c = CL.claim(d.id);
        const f = { to: emailOf(c.supplier), cc: "", subject: `Claim Material ${c.material} — ${c.coil} (${c.po})`,
          body: `Kepada ${c.supplier},\n\nKami menemukan ketidaksesuaian pada material berikut:\n- Material: ${c.material}\n- No. Coil Packing List: ${c.coil}\n- No. PO: ${c.po}\n- Berat: ${num(c.kg, 0)} Kg\n- Problem: ${c.problem} (${c.asal})\n- Keterangan: ${c.alasan}${(c.bukti || []).length ? `\n- Bukti pendukung dari Produksi: terlampir (${c.bukti.length} berkas)` : ""}\n\nMohon konfirmasi dan kesepakatan penanganan (Kompensasi / Retur).\n\nTerima kasih,\nPurchasing PT HFG` };
        openModal({ title: "Email Claim ke Supplier — " + c.id, size: "lg", ok: "Kirim Email",
          body: () => row("Kepada", inp("to", f.to), 1) + row("Cc", inp("cc", f.cc)) + row("Subject", inp("subject", f.subject), 1) + row("Isi Email", txt("body", f.body).replace("<textarea", '<textarea rows="11"'), 1)
            + row("Lampiran", `<span class="ap-badge ap-badge--light">Dokumen_Claim_${esc(c.id)}.pdf</span> <span class="ap-hint">Laporan Ketidaksesuaian Material dari Produksi</span>${(c.bukti || []).length ? buktiHtml(c, false) + '<div class="ap-hint">Bukti pendukung dari Produksi otomatis dilampirkan.</div>' : ""}`)
            + `<p class="cl-assume">OQ-C2 (ditunda): alamat tujuan nanti dari master setting email per supplier; sementara input manual.</p>`,
          onField: (el) => { f[el.dataset.f] = el.value; }, after: fillBukti,
          onOk() { if (!f.to.trim()) { warn("Alamat tujuan wajib diisi."); return; } closeModal(); A().kirimEmail(c.id); render(); toast("Email claim terkirim ke " + f.to + " (mockup, tidak mengirim email nyata).", "ok"); } });
      },
      decide(d) { decisionModal([CL.claim(d.id)]); },
      decideSel() {
        const cs = S().claims.filter((c) => selClaims.has(c.id));
        if (!cs.length) { warn("Centang minimal satu claim terlebih dahulu."); return; }
        const dec = cs.find((c) => c.decisions.length); if (dec) { warn("Claim " + dec.id + " sudah diputuskan."); return; }
        if (new Set(cs.map((c) => c.supplier)).size > 1) { warn("Claim yang dipilih harus dari satu supplier, karena kesepakatan dibuat per supplier."); return; }
        decisionModal(cs);
      },
    },
  };
  const A = () => CL.A;

  const AKSI = { Kompensasi: ["Debit Note", "Lanjut Produksi", "Jual Material"], Retur: ["Retur ke Supplier"] };
  const state_dn = (c) => S().dn.find((d) => d.claim === c.id);
  function decisionModal(cs) {
    const multi = cs.length > 1, c0 = cs[0], view = !multi && c0.decisions.length > 0;
    const defRow = (c) => (c.asal === "KW 2 Supplier" ? { jalur: "Kompensasi", aksi: "Debit Note", kg: c.kg, nilai: 0, ket: "", materialKw: 0 } : { jalur: "Retur", aksi: "Retur ke Supplier", kg: c.kg, nilai: 0, ket: "", materialKw: 0 });
    const dd = { kes: view ? (c0.kesepakatan || "") : "", items: cs.map((c) => { const dnx = state_dn(c); return { c, rows: view ? c.decisions.map((x) => Object.assign({}, x)) : [defRow(c)], kurs: view && dnx ? dnx.kurs : "" }; }) };
    const curOf = (c) => CL.SUPPLIERS[c.supplier].cur, kursOf = (ci) => (curOf(dd.items[ci].c) === "IDR" ? 1 : Number(dd.items[ci].kurs) || 0);
    const dis = view ? " disabled" : "";
    const at = (a) => { const [ci, ri] = String(a).split(":").map(Number); return { ci, ri, c: dd.items[ci].c, r: dd.items[ci].rows[ri] }; };
    const total = (ci) => dd.items[ci].rows.reduce((a, r) => a + (Number(r.kg) || 0), 0);
    const rowHtml = (ci, r, i) => {
      const c = dd.items[ci].c, di = ci + ":" + i, n = dd.items[ci].rows.length;
      let x = "";
      if (r.aksi === "Debit Note") x = row("Nilai Debit Note (Rp)", inp("nilai", r.nilai || "", { num: 1, step: "0.01", di, ph: "Input nilai langsung, tanpa PPN" }) + `<div class="ap-hint">Nilai langsung (bukan persen), tanpa PPN. → Otomatis masuk List Debit Note.</div>`, 1)
        + `<div data-calc="jdn" data-arg="${di}"></div>`;
      if (r.aksi === "Lanjut Produksi") x = row("Keterangan", txt("ket", r.ket, { di, ph: "Catatan khusus untuk Produksi" }), 1) + `<div class="ap-hint">Keterangan = catatan untuk Produksi (tampil di Laporan Produksi). → Status coil otomatis berubah dari "Hold" menjadi dapat dipakai.</div>`;
      if (r.aksi === "Jual Material") x = row("Keterangan", txt("ket", r.ket, { di }), 0)
        + row("Nilai Material", `<span data-calc="nilaiMat" data-arg="${di}"></span> <span class="ap-hint">(Berat × Harga Beli/Kg Rp ${num(c.hargaKg, 0)})</span>`)
        + row("Nilai Material KW (Rp)", inp("materialKw", r.materialKw || "", { num: 1, step: "0.01", di }) + `<div class="ap-hint">Sisanya otomatis menjadi Biaya Scrap (OQ-C4, dikonfirmasi).</div>`, 1)
        + `<div data-calc="jjual" data-arg="${di}"></div><div class="ap-hint">→ Otomatis masuk Inventory Material Khusus Dijual (Gudang Slitting).</div>`;
      if (r.aksi === "Retur ke Supplier") x = row("Keterangan", txt("ket", r.ket, { di }), 0) + row("Nilai Retur", `<span data-calc="nilaiRetur" data-arg="${di}"></span> <span class="ap-hint">(Berat × Costbook/Kg gudang asal Rp ${num(c.costbookKg != null ? c.costbookKg : c.hargaKg, 0)}; nilai material yang keluar dari gudang)</span>`) + row("Nilai Debit Note (Rp)", `<span data-calc="nilaiDnRetur" data-arg="${di}"></span> <span class="ap-hint">(Berat × Price/Unit PO Rp ${num(c.priceUnitPo != null ? c.priceUnitPo : c.hargaKg, 0)}; yang ditagihkan ke supplier)</span>`) + `<div data-calc="jret" data-arg="${di}"></div><div class="ap-hint">→ Otomatis masuk List SPK Re-Pack dan Surat Jalan Retur. Debit Note Retur tidak menjurnal saat terbit; jurnal ada di Confirm Surat Jalan (selisih Costbook vs Price/Unit PO ikut dijurnal).</div>`;
      return `<div class="cl-dec"><div class="cl-dec__head"><span>Jalur ${i + 1}</span>${!view && n > 1 ? `<button type="button" class="ap-btn ap-btn--sm ap-btn--danger" data-act="decDel" data-id="${di}">Hapus</button>` : ""}</div>
        ${row("Jalur", sel("jalur", r.jalur, Object.keys(AKSI), { di, struct: 1 }).replace("<select", "<select" + dis), 1)}
        ${row("Aksi", sel("aksi", r.aksi, AKSI[r.jalur], { di, struct: 1 }).replace("<select", "<select" + dis), 1)}
        ${row("Berat (Kg)", inp("kg", r.kg, { num: 1, step: "any", di }), 1)}${x}</div>`;
    };
    const detail = () => (multi
      ? tbl(["No", "No. Claim", "Asal", "Nama Material", "No. Coil Packing List", "No. PO", "Berat (Kg)", "Problem"],
          cs.map((c, n) => `<tr><td>${n + 1}</td><td>${esc(c.id)}</td><td>${esc(c.asal)}</td><td>${esc(c.material)}</td><td>${esc(c.coil)}</td><td>${esc(c.po)}</td><td class="ap-r">${num(c.kg, 0)}</td><td>${esc(c.problem)}</td></tr>`).join(""))
        + `<div class="ap-hint" style="margin:6px 0 12px">Supplier: ${esc(c0.supplier)} · ${cs.length} claim diputuskan bersama; kesepakatan di bawah berlaku untuk semuanya. Nilai, SPK, dan Debit Note tetap dibuat per claim.</div>`
      : itemsTbl([c0]) + `<div class="ap-hint" style="margin:6px 0 12px">Asal: ${esc(c0.asal)} · Supplier: ${esc(c0.supplier)} · Email terkirim: ${fdate(c0.emailDate)}</div>`);
    const kursBlock = (ci) => { const c = dd.items[ci].c, cur = curOf(c); if (cur === "IDR") return ""; return `<div class="cl-kurs">${row("Mata Uang", inp("cur", cur, { ro: 1 }) + '<div class="ap-hint">Mata uang PO supplier.</div>')}${row("Kurs", inp("kurs", dd.items[ci].kurs, { num: 1, step: "any", ph: "Kurs saat claim" }).replace("<input", `<input data-ci="${ci}"${dis}`) + '<div class="ap-hint">Kurs saat claim, diinput di sini. Create Debit Note nanti menarik kurs ini (tidak diinput ulang) dan jurnal Confirm Surat Jalan memakainya.</div>', 1)}</div>`; };
    const section = (ci) => { const c = dd.items[ci].c; return `${multi ? `<h6 class="cl-decgrp">${esc(c.id)} — ${esc(c.asal)} — ${esc(c.material)} (${esc(c.coil)}) · ${num(c.kg, 0)} Kg</h6>` : ""}${kursBlock(ci)}${dd.items[ci].rows.map((r, i) => rowHtml(ci, r, i)).join("")}
        ${view ? "" : `<button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="decAdd" data-id="${ci}">+ Tambah Jalur${multi ? " (" + esc(c.id) + ")" : ""}</button>`}
        <p style="margin-top:10px">Total Berat dialokasikan${multi ? " (" + esc(c.id) + ")" : ""}: <span class="cl-sum ${total(ci) > c.kg ? "is-bad" : ""}" data-ci="${ci}">${num(total(ci), 0)} / ${num(c.kg, 0)} Kg</span>${ci === 0 ? ` <span class="ap-hint">Satu coil boleh dibuat di dua jalur (mis. KW 2 dikompensasi, Hold diretur).</span>` : ""}</p>`; };
    openModal({ title: multi ? `Process Decision — ${cs.length} Claim (${c0.supplier})` : (view ? "View Decision — " : "Process Decision — ") + c0.id, size: "xl", ok: view ? "" : "Confirm",
      body: () => `<h6>Detail Claim</h6>${detail()}
        ${row("Kesepakatan Supplier", txt("kes", dd.kes, {}).replace("<textarea", "<textarea" + dis), 0)}
        ${view ? `<h6>Progres</h6>${progHtml(c0)}` : ""}<h6>Decision</h6>${cs.map((c, ci) => section(ci)).join("")}
        <p class="cl-assume">${ASSUME.oqc3}</p>`,
      calc: {
        jdn: (a) => { const { c, r, ci } = at(a); return jr(CL.J.dnKompensasi({ asal: c.asal, nilai: Number(r.nilai) || 0, kurs: kursOf(ci) })); },
        nilaiMat: (a) => { const { c, r } = at(a); return rp(CL.nilaiMaterial(c, Number(r.kg) || 0)); },
        nilaiRetur: (a) => { const { c, r } = at(a); return rp(CL.nilaiRetur(c, Number(r.kg) || 0)); },
        nilaiDnRetur: (a) => { const { c, r, ci } = at(a), n = CL.nilaiDnRetur(c, Number(r.kg) || 0); return curOf(c) === "IDR" ? rp(n) : `${curOf(c)} ${num(n)} <span class="ap-hint">(= ${rp(CL.round2(n * kursOf(ci)))} dengan kurs ${num(kursOf(ci))})</span>`; },
        jret: (a) => { const { c, r, ci } = at(a), kg = Number(r.kg) || 0; return jr(CL.J.sjConfirm(CL.nilaiRetur(c, kg), CL.round2(CL.nilaiDnRetur(c, kg) * kursOf(ci)))); },
        jjual: (a) => { const { c, r } = at(a), n = CL.nilaiMaterial(c, Number(r.kg) || 0), kw = Math.min(Number(r.materialKw) || 0, n); return jr(CL.J.jualMaterial(n, kw)); },
      },
      onField(el) {
        if (el.dataset.f === "kes") { dd.kes = el.value; return; }
        if (el.dataset.f === "kurs" && el.dataset.ci != null) { dd.items[+el.dataset.ci].kurs = el.value === "" ? "" : pv(el); calc(); return; }
        if (el.dataset.di == null) return;
        const { r } = at(el.dataset.di); if (!r) return;
        r[el.dataset.f] = isNumEl(el) ? (el.value === "" ? "" : pv(el)) : el.value;
        if (el.dataset.f === "jalur") r.aksi = AKSI[r.jalur][0];
        if ("struct" in el.dataset) drawModal();
        else { calc(); dd.items.forEach((it, ci) => { const sm = $(`#pcModal .cl-sum[data-ci="${ci}"]`); if (sm) { sm.textContent = `${num(total(ci), 0)} / ${num(it.c.kg, 0)} Kg`; sm.classList.toggle("is-bad", total(ci) > it.c.kg); } }); }
      },
      act: {
        decAdd(x) { const ci = +x.id, c = dd.items[ci].c; dd.items[ci].rows.push({ jalur: "Retur", aksi: "Retur ke Supplier", kg: Math.max(c.kg - total(ci), 0), nilai: 0, ket: "", materialKw: 0 }); drawModal(); },
        decDel(x) { const a = at(x.id); dd.items[a.ci].rows.splice(a.ri, 1); drawModal(); },
      },
      onOk() {
        for (let ci = 0; ci < dd.items.length; ci++) {
          const { c, rows } = dd.items[ci], p = multi ? c.id + ": " : "";
          if (curOf(c) !== "IDR" && !(Number(dd.items[ci].kurs) > 0)) { warn(p + "Kurs wajib diisi untuk mata uang " + curOf(c) + "."); return; }
          if (total(ci) <= 0) { warn(p + "Berat tiap jalur wajib diisi."); return; }
          if (rows.some((r) => !(Number(r.kg) > 0))) { warn(p + "Berat (Kg) tiap jalur harus lebih dari 0."); return; }
          if (total(ci) > c.kg + 0.0001) { warn(`${p}Total berat jalur (${num(total(ci), 0)} Kg) melebihi berat claim (${num(c.kg, 0)} Kg).`); return; }
          if (rows.find((r) => r.aksi === "Debit Note" && !(Number(r.nilai) > 0))) { warn(p + "Nilai Debit Note wajib diisi (lebih dari 0)."); return; }
          if (rows.find((r) => r.aksi === "Jual Material" && (Number(r.materialKw) < 0 || Number(r.materialKw) > CL.nilaiMaterial(c, Number(r.kg))))) { warn(p + "Nilai Material KW tidak boleh melebihi nilai material."); return; }
        }
        confirmThen({ text: (multi ? cs.length + " claim akan diputuskan bersamaan. " : "") + "Keputusan claim akan diproses (Debit Note / SPK Re-Pack / Jual Material) dan tidak dapat diubah dari halaman ini." }, () => {
          dd.items.forEach(({ c, rows, kurs }) => A().putuskan(c.id, dd.kes, rows.map((r) => ({ jalur: r.jalur, aksi: r.aksi, kg: Number(r.kg), nilai: Number(r.nilai) || 0, ket: r.ket || "", materialKw: Number(r.materialKw) || 0 })), kurs));
          cs.forEach((c) => selClaims.delete(c.id));
          closeModal(); render(); done(multi ? `Keputusan ${cs.length} claim tersimpan: ${cs.map((c) => c.id).join(", ")}.` : "Keputusan claim tersimpan: " + CL.jalurOf(c0) + ".");
        });
      },
    });
  }

  /* ======================= 3. SPK Re-Pack (FR-83) ======================= */
  const spkNoNext = () => "SPK-RP-" + CL.today().slice(2, 4) + CL.today().slice(5, 7) + String(S().seq.spk + 1).padStart(2, "0");
  const matList = (items) => items.map((i, n) => `${n + 1}. ${esc(i.material)} (${esc(i.coil)})`).join("<br>");
  P.spk = {
    title: "SPK Re-Pack", role: "PPIC (Produksi memindahkan material)", fr: "FR-83",
    hint: "Perintah kerja re-pack material yang akan diretur ke supplier; Produksi memindahkan material dari Produksi ke Gudang.",
    render() {
      const l = S().spk;
      return `<div class="cl-sumrow">${stat(l.filter((x) => x.status === "WAITING").length, "WAITING")}${stat(l.filter((x) => x.status === "ON PROGRESS").length, "ON PROGRESS")}${stat(l.filter((x) => x.status === "COMPLETED").length, "COMPLETED")}</div>
        ${card("List SPK Re-Pack", tbl(["No. SPK Re-Pack", "Tgl Perintah Kerja", "Nama Supplier", "Material Claim", "Status SPK", "Action"],
          l.map((s) => `<tr><td>${esc(s.no || "-")}</td><td>${fdate(s.tgl)}</td><td>${esc(s.supplier)}</td><td>${matList(s.items)}</td><td>${lab(s.status)}</td><td class="ap-act">${s.status === "WAITING" ? btn("primary", "create", s.id, "Create SPK") : ""}${s.status === "ON PROGRESS" ? btn("success", "confirm", s.id, "Confirm SPK") : ""}${s.status !== "WAITING" ? btn("info", "view", s.id, "View") + btn("secondary", "print", s.id, "Print") : ""}</td></tr>`).join(""),
          "Belum ada SPK Re-Pack. Baris muncul otomatis saat Process Decision memilih Retur ke Supplier."))}
        <p class="note">Create SPK menggabungkan baris Retur satu supplier dalam satu SPK (G6). Create SPK = membuat perintah re-pack ke Produksi; Confirm SPK = material sudah di-re-pack (status COMPLETED, lalu muncul di Surat Jalan Retur sebagai WAITING SJ). Tidak ada jurnal pada SPK Re-Pack.</p>`;
    },
    act: {
      create(d) {
        const s = S().spk.find((x) => x.id === d.id), f = { tgl: CL.today() };
        const lines = S().spk.filter((x) => x.status === "WAITING" && x.supplier === s.supplier), on = {};
        lines.forEach((x) => { on[x.id] = true; });
        openModal({ title: "Create SPK Re-Pack — " + s.supplier, size: "xl", ok: "Create SPK",
          body: () => row("No. SPK Re-Pack", inp("no", spkNoNext(), { ro: 1 }) + '<div class="ap-hint">(Auto Generate)</div>') + row("Tgl Perintah Kerja", inp("tgl", f.tgl, { type: "date" }), 1) + row("Nama Supplier", inp("s", s.supplier, { ro: 1 }))
            + "<h6>Material claim yang masuk SPK ini</h6>"
            + tbl(["", "No. Claim", "Nama Material", "No. Coil Packing List", "No. PO", "Berat (Kg)", "Problem"], lines.map((x) => { const i = x.items[0]; return `<tr><td><input type="checkbox" data-f="chk" data-id="${esc(x.id)}"${on[x.id] ? " checked" : ""}></td><td>${esc(i.claim)}</td><td>${esc(i.material)}</td><td>${esc(i.coil)}</td><td>${esc(i.po)}</td><td class="ap-r">${num(i.kg, 0)}</td><td>${esc(i.problem)}</td></tr>`; }).join(""))
            + '<p class="ap-hint">Semua baris Retur WAITING dari supplier yang sama tercentang awalnya dan digabung dalam satu SPK (satu SPK menjadi satu Surat Jalan). Hapus centang untuk membuat SPK terpisah.</p>',
          onField(el) { if (el.dataset.f === "tgl") f.tgl = el.value; else if (el.dataset.f === "chk") on[el.dataset.id] = el.checked; },
          onOk() {
            const ids = lines.filter((x) => on[x.id]).map((x) => x.id);
            if (!ids.length) { warn("Pilih minimal satu baris material."); return; }
            if (!f.tgl) { warn("Tgl Perintah Kerja wajib diisi."); return; }
            confirmThen({ text: `Buat SPK Re-Pack berisi ${ids.length} baris material dan kirim perintah ke Produksi?` }, () => { A().createSpkGroup(ids, f.tgl); closeModal(); render(); done("SPK Re-Pack dibuat."); });
          } });
      },
      confirm(d) { confirmThen({ title: "Confirm SPK Re-Pack?", text: "Material sudah di-re-pack dan dipindahkan dari Produksi ke Gudang." }, () => { A().confirmSpk(d.id); render(); done("SPK Re-Pack COMPLETED. Surat Jalan Retur dapat dibuat."); }); },
      view(d) { const s = S().spk.find((x) => x.id === d.id); openModal({ title: "View SPK Re-Pack", body: () => kv([["No. SPK Re-Pack", esc(s.no)], ["Tgl Perintah Kerja", fdate(s.tgl)], ["Nama Supplier", esc(s.supplier)], ["Status", lab(s.status)]]) + itemsTbl(s.items) }); },
      print(d) {
        const s = S().spk.find((x) => x.id === d.id);
        printDoc("SPK Re-Pack " + s.no, ppHead("SURAT PERINTAH KERJA RE-PACK", s.no)
          + ppMeta([["No. SPK Re-Pack", esc(s.no)], ["Tgl Perintah Kerja", fdate(s.tgl)], ["Nama Supplier", esc(s.supplier)], ["Status SPK", esc(s.status)]])
          + '<p>Mohon Produksi melakukan <b>re-pack</b> material berikut dan memindahkannya dari Produksi ke Gudang untuk diretur ke supplier.</p>'
          + ppItems(s.items, true)
          + '<p class="pp-note">Catatan: pastikan nomor coil, berat, dan kondisi pengemasan sesuai sebelum material dipindahkan.</p>'
          + ppSign(["Dibuat oleh (PPIC)", "Diterima oleh (Produksi)", "Mengetahui"]));
      },
    },
  };

  /* ======================= 4. Surat Jalan Retur (FR-84) ======================= */
  P.sj = {
    title: "Surat Jalan Retur", role: "PPIC", fr: "FR-84",
    hint: "Surat Jalan dibuat dari SPK Re-Pack yang COMPLETED, dikirim ke supplier, lalu dikonfirmasi saat diterima supplier.",
    render() {
      const l = S().sj, pend = S().spk.filter((x) => x.status === "ON PROGRESS").length;
      return `<div class="cl-sumrow">${stat(l.filter((x) => x.status === "WAITING SJ").length, "WAITING SJ")}${stat(l.filter((x) => x.status === "NOT CONFIRMED").length, "NOT CONFIRMED")}${stat(l.filter((x) => x.status === "SJ CONFIRMED").length, "SJ CONFIRMED")}</div>
        ${card("List Surat Jalan Retur", tbl(["No. Surat Jalan", "Tgl Surat Jalan", "Nama Supplier", "Material Claim", "Status Surat Jalan", "Action"],
          l.map((j) => { const s = S().spk.find((x) => x.id === j.spk); return `<tr><td>${esc(j.no || "-")}</td><td>${fdate(j.tgl)}</td><td>${esc(j.supplier)}</td><td>${matList(s.items)}</td><td>${lab(j.status)}</td><td class="ap-act">${j.status === "WAITING SJ" ? btn("primary", "create", j.id, "Create SJ") : ""}${j.status === "NOT CONFIRMED" ? btn("success", "confirm", j.id, "Confirm SJ") : ""}${j.status !== "WAITING SJ" ? btn("info", "view", j.id, "View") + btn("secondary", "print", j.id, "Print") : ""}</td></tr>`; }).join(""),
          "Belum ada Surat Jalan. Muncul (WAITING SJ) setelah SPK Re-Pack dikonfirmasi COMPLETED." + (pend ? ` ${pend} SPK masih ON PROGRESS.` : "")))}
        <p class="note">Jurnal Create SJ: Db In Transit / Cr Inventory. Jurnal Confirm SJ: Db Debit Note (Retur, Berat × Price/Unit PO) / Cr Nilai Material Retur (In Transit, Berat × Costbook/Kg gudang asal) / Db atau Cr Selisih Costbook vs Price/Unit PO (FR-84; keputusan user 2026-10-29).</p>`;
    },
    act: {
      create(d) {
        const j = S().sj.find((x) => x.id === d.id), s = S().spk.find((x) => x.id === j.spk), f = { tgl: CL.today(), driver: "", nopol: "" };
        openModal({ title: "Create Surat Jalan Retur", size: "lg", ok: "Create SJ",
          body: () => row("No. Surat Jalan", inp("n", "SJ-" + String(S().seq.sj + 1).padStart(2, "0"), { ro: 1 }) + '<div class="ap-hint">(Auto Generate)</div>') + row("Tgl Surat Jalan", inp("tgl", f.tgl, { type: "date" }), 1) + row("Alamat", inp("a", j.alamat, { ro: 1 }))
            + row("Driver", inp("driver", f.driver, { ph: "Nama driver" }), 1) + row("No. Polisi Truk", inp("nopol", f.nopol, { ph: "mis. B 1234 HFG" }), 1) + itemsTbl(s.items) + jr(CL.J.sjCreate(j.nilai)),
          onField: (el) => { f[el.dataset.f] = el.value; },
          onOk() { if (!f.tgl || !f.driver.trim() || !f.nopol.trim()) { warn("Tgl, Driver, dan No. Polisi Truk wajib diisi."); return; } confirmThen({ text: "Buat Surat Jalan dan jurnalkan In Transit?" }, () => { A().createSj(j.id, f); closeModal(); render(); done("Surat Jalan dibuat; jurnal In Transit tercatat."); }); } });
      },
      confirm(d) { const j = S().sj.find((x) => x.id === d.id); confirmThen({ title: "Confirm Surat Jalan?", text: `Surat Jalan ${j.no} diterima supplier. Jurnal: Db Debit Note (Retur) ${rp(CL.sjDn(S().spk.find((x) => x.id === j.spk)))} / Cr Nilai Material Retur ${rp(j.nilai)} / selisih Costbook vs Price/Unit PO ${rp(CL.round2(CL.sjDn(S().spk.find((x) => x.id === j.spk)) - j.nilai))}.` }, () => { A().confirmSj(j.id); render(); done("Surat Jalan dikonfirmasi; Debit Note Retur dapat dibuat."); }); },
      view(d) {
        const j = S().sj.find((x) => x.id === d.id), s = S().spk.find((x) => x.id === j.spk);
        openModal({ title: "View Surat Jalan Retur — " + j.no, size: "lg", body: () => kv([["No. Surat Jalan", esc(j.no)], ["Tgl Surat Jalan", fdate(j.tgl)], ["Alamat", esc(j.alamat)], ["Driver", esc(j.driver)], ["No. Polisi Truk", esc(j.nopol)], ["Status", lab(j.status)]]) + itemsTbl(s.items) + jr(CL.J.sjCreate(j.nilai)) + (j.status === "SJ CONFIRMED" ? jr(CL.J.sjConfirm(j.nilai, CL.sjDn(s))) : "") });
      },
      print(d) {
        const j = S().sj.find((x) => x.id === d.id), s = S().spk.find((x) => x.id === j.spk);
        printDoc("Surat Jalan " + j.no, ppHead("SURAT JALAN RETUR", j.no)
          + ppMeta([["No. Surat Jalan", esc(j.no)], ["Tgl Surat Jalan", fdate(j.tgl)], ["Nama Supplier", esc(j.supplier)], ["Alamat", esc(j.alamat)], ["Driver", esc(j.driver)], ["No. Polisi Truk", esc(j.nopol)], ["No. SPK Re-Pack", esc(s.no)]])
          + '<p>Mohon diterima barang retur berikut:</p>' + ppItems(s.items, true)
          + '<p class="pp-note">Barang diterima dalam keadaan baik dan lengkap sesuai daftar di atas.</p>'
          + ppSign(["Dibuat oleh (PPIC)", "Driver", "Diterima oleh (Supplier)"]));
      },
    },
  };

  /* ======================= 5. Debit Note (FR-85, FR-86) ======================= */
  const dnClaim = (d) => CL.claim(d.claim);
  const dnItems = (d) => { const c = dnClaim(d); return c ? [{ material: c.material, coil: c.coil, po: c.po, kg: (c.decisions.find((x) => x.id === d.dec) || { kg: c.kg }).kg, problem: c.problem }] : []; };
  const nilaiLabel = (d) => `${d.cur === "IDR" ? "Rp" : d.cur} ${num(d.nilai)}`;
  P.dn = {
    title: "Debit Note", role: "Purchasing", fr: "FR-85, FR-86",
    hint: "List Debit Note dari jalur Kompensasi dan Retur. Debit Note Retur menarik detail Surat Jalan; hanya Debit Note Kompensasi yang menjurnal saat terbit.",
    render() {
      const l = S().dn;
      return `<div class="cl-sumrow">${stat(l.filter((d) => d.status === "Belum Terbit").length, "Belum Terbit")}${stat(l.filter((d) => d.status === "Sudah Terbit").length, "Sudah Terbit (siap alokasi / tagih)")}${stat(l.length, "Total Debit Note")}</div>
        ${card("List Debit Note", tbl(["No. Debit Note", "Tgl Terbit", "Nama Supplier", "Mata Uang", "Total Nilai", "Jalur", "Status Dokumen", "Action"],
          l.map((d) => `<tr><td>${esc(d.no)}</td><td>${fdate(d.tgl)}</td><td>${esc(d.supplier)}</td><td>${esc(d.cur)}</td><td class="ap-r">${num(d.nilai)}</td><td>${lab(d.jalur)}</td><td>${lab(d.status)}</td>
            <td class="ap-act">${btn("info", "view", d.no, "View")}${btn("secondary", "print", d.no, "Print")}${d.status === "Belum Terbit" ? btn("warning", "create", d.no, "Edit") + btn("primary", "create", d.no, "Create") : ""}</td></tr>`).join("")))}
        <p class="note">${ASSUME.oqc3} Status Dokumen mengikuti workbook; transisi: Belum Terbit → Sudah Terbit (Create) → Sudah Alokasi ke Hutang (Pembayaran PO) atau Belum Terima Uang (Tagihan) → Sudah Terima Uang.</p>`;
    },
    act: {
      view(d) {
        const x = S().dn.find((y) => y.no === d.id);
        openModal({ title: "View Debit Note — " + x.no, size: "lg", body: () => kv([["Jalur", esc(x.jalur)], ["Nama Supplier", esc(x.supplier)], ["Tgl Terbit", fdate(x.tgl)], ["Status", lab(x.status)], ["Total Nilai", esc(nilaiLabel(x))], ["Kurs", num(x.kurs)], ["Total Nilai (IDR)", rp(CL.dnIDR(x))], ["No. Surat Jalan", esc(x.sj || "-")]]) + itemsTbl(dnItems(x))
          + (x.jalur === "Kompensasi" ? (x.status !== "Belum Terbit" ? jr(CL.J.dnKompensasi(x)) : '<p class="note">Jurnal tercatat saat Debit Note diterbitkan.</p>') : '<p class="cl-assume">Debit Note Retur tidak membuat jurnal saat terbit; jurnal Db Debit Note (Retur) / Cr Nilai Material Retur (In Transit) / Db atau Cr Selisih Costbook vs Price/Unit PO terjadi saat Confirm Surat Jalan.</p>') });
      },
      print(d) {
        const x = S().dn.find((y) => y.no === d.id), c = dnClaim(x), it = dnItems(x)[0], retur = x.jalur === "Retur", cur = x.cur === "IDR" ? "Rp" : x.cur;
        const price = c ? (c.priceUnitPo != null ? c.priceUnitPo : c.hargaKg) : 0;
        const rincian = retur && it
          ? `<table class="pp-tbl"><thead><tr><th>No</th><th>Nama Material</th><th>No. Coil Packing List</th><th>No. PO</th><th class="pp-r">Berat (Kg)</th><th class="pp-r">Price/Unit PO</th><th class="pp-r">Total Nilai</th></tr></thead><tbody><tr><td>1</td><td>${esc(it.material)}</td><td>${esc(it.coil)}</td><td>${esc(it.po)}</td><td class="pp-r">${num(it.kg, 0)}</td><td class="pp-r">${num(price)}</td><td class="pp-r">${num(x.nilai)}</td></tr></tbody></table>`
          : `<table class="pp-tbl"><thead><tr><th>No</th><th>Keterangan</th><th class="pp-r">Total Nilai</th></tr></thead><tbody><tr><td>1</td><td>Kompensasi ${esc(x.asal)}${it ? " — " + esc(it.material) + " (" + esc(it.coil) + "), " + num(it.kg, 0) + " Kg" : ""}</td><td class="pp-r">${num(x.nilai)}</td></tr></tbody></table>`;
        printDoc("Debit Note " + x.no, (x.status === "Belum Terbit" ? '<div class="pp-wm">BELUM TERBIT</div>' : "") + ppHead("DEBIT NOTE", x.no)
          + ppMeta([["No. Debit Note", esc(x.no)], ["Tgl Terbit", fdate(x.tgl)], ["Nama Supplier", esc(x.supplier)], ["Jalur", esc(x.jalur)], ["Mata Uang", esc(x.cur)], ["Kurs", num(x.kurs)], ["No. Claim", esc(x.claim || "-")], ["No. Surat Jalan", esc(x.sj || "-")]])
          + "<p>Dengan ini kami debet rekening Saudara atas claim material berikut:</p>" + rincian
          + `<table class="pp-sum"><tr><td>Total Nilai</td><td class="pp-r"><b>${cur} ${num(x.nilai)}</b></td></tr><tr><td>Total Nilai (IDR)</td><td class="pp-r"><b>Rp ${num(CL.dnIDR(x))}</b></td></tr></table>`
          + ppSign(["Dibuat oleh (Purchasing)", "Diterima oleh (Supplier)"]));
      },
      create(d) {
        const x = S().dn.find((y) => y.no === d.id), supCur = CL.SUPPLIERS[x.supplier].cur, f = { cur: x.cur === "IDR" ? supCur : x.cur, kurs: x.kurs || 1 };
        const retur = x.jalur === "Retur";
        openModal({ title: "Create Debit Note — " + x.no, size: "lg", ok: "Create Debit Note",
          body: () => row("Jalur", inp("j", x.jalur, { ro: 1 })) + row("Nama Supplier", inp("s", x.supplier, { ro: 1 })) + itemsTbl(dnItems(x))
            + (retur ? row("No. Surat Jalan", inp("sj", x.sj || "(belum ada)", { ro: 1 }) + `<div class="ap-hint">Jika Retur, otomatis tarik detail Surat Jalan.</div>`) : "")
            + row("Total Nilai", inp("n", num(x.nilai), { ro: 1 }) + (retur ? `<div class="ap-hint">(Berat × Price/Unit PO, bukan costbook; selisihnya dijurnal saat Confirm Surat Jalan)</div>` : `<div class="ap-hint">(Tarik nilai dari modul claim)</div>`)) + row("Mata Uang", inp("cur", f.cur, { ro: 1 }))
            + row("Kurs", inp("kurs", f.cur === "IDR" ? 1 : f.kurs, { num: 1, step: "any", ro: 1 }) + `<div class="ap-hint">(Ditarik dari kurs yang diinput saat Process Decision; tidak diinput ulang)</div>`) + row("Total Nilai (IDR)", `<b data-calc="idr"></b> <span class="ap-hint">(Nanti masuk ke menu Penerimaan Uang dari Supplier)</span>`)
            + (retur ? '<p class="cl-assume">Debit Note Retur tidak membuat jurnal saat terbit (jawaban user 2026-10-27).</p>' : jr(CL.J.dnKompensasi({ asal: x.asal, nilai: x.nilai, kurs: f.cur === "IDR" ? 1 : f.kurs })) + `<p class="cl-assume">${ASSUME.oqc3}</p>`),
          calc: { idr: () => rp(x.nilai * (f.cur === "IDR" ? 1 : Number(f.kurs) || 0)) },
          onField(el) { f[el.dataset.f] = isNumEl(el) ? pv(el) || 0 : el.value; if (el.dataset.f === "cur") { if (f.cur === "IDR") f.kurs = 1; drawModal(); } else calc(); },
          onOk() {
            if (retur && !x.sj) { warn("Surat Jalan Retur belum dibuat. Debit Note Retur menarik detail Surat Jalan."); return; }
            if (f.cur !== "IDR" && !(Number(f.kurs) > 0)) { warn("Kurs belum diinput pada Process Decision claim."); return; }
            confirmThen({ text: "Terbitkan Debit Note " + x.no + "?" }, () => { A().createDn(x.no, { cur: f.cur, kurs: f.cur === "IDR" ? 1 : Number(f.kurs) }); closeModal(); render(); done("Debit Note " + x.no + " terbit." + (retur ? "" : " Jurnal Kompensasi tercatat.")); });
          } });
      },
    },
  };

  /* ======================= 6. Report Biaya Kompensasi vs Biaya Scrap (FR-87) ======================= */
  P.report = {
    title: "Report Biaya Kompensasi vs Biaya Scrap", role: "Purchasing / Accounting", fr: "FR-87",
    hint: "Beban yang tetap ditanggung HFG setelah kompensasi (Debit Note) dan setelah Jual Material (Biaya Scrap yang diakui).",
    render() {
      const rows = [];
      S().claims.forEach((c) => c.decisions.forEach((d) => {
        const biaya = CL.nilaiMaterial(c, d.kg);
        if (d.aksi === "Debit Note") rows.push({ c, d, jenis: "Debit Note", biaya, komp: d.nilai, kw: null, beban: biaya - d.nilai, akun: c.asal === "KW 2 Supplier" ? "Biaya KW 2 Supplier" : "Biaya Scrap" });
        if (d.aksi === "Jual Material") rows.push({ c, d, jenis: "Jual Material", biaya, komp: null, kw: d.materialKw, beban: CL.round2(biaya - d.materialKw), akun: "Biaya Scrap" });
      }));
      const sum = (f) => rows.reduce((a, r) => a + (f(r) || 0), 0);
      return card("Report Biaya Kompensasi vs Biaya Scrap atas Material di-Claim", tbl(["Jenis", "No. Claim", "Nama Supplier", "Nama Material", "No. Coil Packing List", "Asal", "Berat (Kg)", "Biaya Material di-Claim (Rp)", "Nilai Debit Note (Rp)", "Nilai Material KW (Rp)", "Beban Ditanggung HFG (Rp)", "Akun Beban"],
        rows.map((r) => `<tr><td>${lab(r.jenis === "Debit Note" ? "Sudah Terbit" : "Hold").replace(/>[^<]*</, ">" + r.jenis + "<")}</td><td>${esc(r.c.id)}</td><td>${esc(r.c.supplier)}</td><td>${esc(r.c.material)}</td><td>${esc(r.c.coil)}</td><td>${esc(r.c.asal)}</td><td class="ap-r">${num(r.d.kg, 0)}</td><td class="ap-r">${num(r.biaya)}</td><td class="ap-r">${r.komp == null ? "-" : num(r.komp)}</td><td class="ap-r">${r.kw == null ? "-" : num(r.kw)}</td><td class="ap-r">${num(r.beban)}</td><td>${esc(r.akun)}</td></tr>`).join("")
          + (rows.length ? `<tr class="ap-grp"><td colspan="7"><b>Total</b></td><td class="ap-r"><b>${num(sum((r) => r.biaya))}</b></td><td class="ap-r"><b>${num(sum((r) => r.komp))}</b></td><td class="ap-r"><b>${num(sum((r) => r.kw))}</b></td><td class="ap-r"><b>${num(sum((r) => r.beban))}</b></td><td></td></tr>` : ""),
        "Belum ada claim berjalur Kompensasi — Debit Note atau Jual Material."))
        + `<p class="note">Beban Ditanggung HFG: untuk Debit Note = biaya material − nilai kompensasi (tanpa PPN); untuk Jual Material = Biaya Scrap yang diakui (nilai material − Material KW). Retur dan Lanjut Produksi tidak ditampilkan karena tidak mengakui kompensasi atau kerugian (keputusan user 2026-10-28, G5). Biaya material = Berat × Harga Beli/Kg.</p>`;
    },
    act: {},
  };

  /* ======================= 7. Request Payment — Alokasi Debit Note (FR-88, OQ-C7/C8) ======================= */
  const detRows = (b) => [["Tagihan PO (nilai invoice)", b.nilai], ["PPN", b.ppn]].concat(b.dns.map((no) => { const x = S().dn.find((y) => y.no === no); return ["Debit Note " + no + " (" + x.jalur + ")", -CL.dnIDR(x)]; }));
  const detTbl = (b) => tbl(["Baris detail (payment_approve_details)", "Total (IDR)"], detRows(b).map((r) => `<tr><td>${esc(r[0])}</td><td class="ap-r ${r[1] < 0 ? "cl-bad" : ""}">${num(r[1])}</td></tr>`).join("") + `<tr class="ap-grp"><td><b>Jumlah dibayar (Bank)</b></td><td class="ap-r"><b>${num(b.totalBayar)}</b></td></tr>`);
  const stLab = (st) => `<span class="ap-label ap-label--${st === "Dibayar" ? "success" : "info"}">${esc(st)}</span>`;
  P.bayar = {
    title: "Request Payment PO — Alokasi Debit Note", role: "Accounting / Finance", fr: "FR-88",
    hint: "Bagian Alokasi Debit Note adalah tambahan pada alur pembayaran yang ada (request_payment → payment_approve): dipilih saat Request Payment, tampil sebagai baris negatif saat pembayaran.",
    head: () => `<button type="button" class="btn btn--primary" data-act="new">+ Buat Request Payment PO</button>`,
    render() {
      const l = S().bayar;
      return card("Request Payment PO dengan Debit Note", tbl(["No. Request", "Tgl", "Supplier", "No. PO", "Nilai PO + PPN", "DN Kompensasi", "DN Retur", "Jumlah Diajukan (bersih)", "Status", "Action"],
        l.map((b) => `<tr><td>${esc(b.no)}</td><td>${fdate(b.tgl)}</td><td>${esc(b.supplier)}</td><td>${esc(b.po)}</td><td class="ap-r">${num(b.nilai + b.ppn)}</td><td class="ap-r">${num(b.dnKomp)}</td><td class="ap-r">${num(b.dnRetur)}</td><td class="ap-r">${num(b.totalBayar)}</td><td>${stLab(b.status)}</td><td class="ap-act">${b.status === "Request Payment" ? btn("success", "bayar", b.no, "Proses Pembayaran") : ""}${btn("info", "view", b.no, "View")}</td></tr>`).join(""), "Belum ada Request Payment. Klik Buat Request Payment PO."))
        + `<p class="note">Rumus: Jumlah = Nilai PO − DN Kompensasi − DN Retur + PPN (PPN = Nilai × 11/12 × 12%, FR-92). Approve checker dan management mengikuti alur Request Payment yang sudah ada dan tidak dimockup di sini. Jurnal baru terposting saat Proses Pembayaran (FR-93).</p>`;
    },
    act: {
      view(d) { const b = S().bayar.find((x) => x.no === d.id); openModal({ title: "View Request Payment — " + b.no, size: "lg", body: () => kv([["Nama Supplier", esc(b.supplier)], ["No. PO", esc(b.po)], ["Status", esc(b.status)], ["Debit Note", esc(b.dns.join(", ") || "-")]]) + detTbl(b) + (b.status === "Dibayar" ? jr(CL.J.bayar(b)) : '<p class="note">Jurnal terposting saat Proses Pembayaran.</p>') }); },
      bayar(d) {
        const b = S().bayar.find((x) => x.no === d.id), f = { admin: 0, tgl: CL.today() };
        openModal({ title: "Proses Pembayaran (payment_approve) — " + b.no, size: "lg", ok: "Bayar",
          body: () => row("Tgl Pembayaran", inp("tgl", f.tgl, { type: "date" }), 1) + row("Biaya Admin (Rp)", inp("admin", f.admin, { num: 1, step: "0.01" })) + detTbl(b) + '<div data-calc="jr"></div>',
          calc: { jr: () => jr(CL.J.bayar(Object.assign({}, b, { admin: Number(f.admin) || 0 }))) },
          onField(el) { f[el.dataset.f] = isNumEl(el) ? pv(el) || 0 : el.value; calc(); },
          onOk() { if (!f.tgl) { warn("Tgl Pembayaran wajib diisi."); return; } confirmThen({ text: "Proses pembayaran dan posting jurnal ke GL?" }, () => { A().prosesBayar(b.no, Number(f.admin) || 0, f.tgl); closeModal(); render(); done("Pembayaran diproses; jurnal terposting."); }); } });
      },
      new() {
        const avail = (sup) => S().dn.filter((d) => d.supplier === sup && d.status === "Sudah Terbit" && d.cur === "IDR");
        const sups = [...new Set(S().dn.filter((d) => d.status === "Sudah Terbit" && d.cur === "IDR").map((d) => d.supplier))];
        if (!sups.length) { warn("Belum ada Debit Note berstatus Sudah Terbit (mata uang IDR) untuk dialokasikan."); return; }
        const f = { supplier: sups[0], po: "PO-008 (Tukar Coil)", nilai: CL.round2(3870 * 16936.94), dns: [], pick: "" };
        const ppn = () => CL.round2(f.nilai * (11 / 12) * 0.12);
        const sums = () => { const dns = f.dns.map((no) => S().dn.find((x) => x.no === no)); const k = dns.filter((x) => x.jalur === "Kompensasi").reduce((a, x) => a + CL.dnIDR(x), 0), r = dns.filter((x) => x.jalur === "Retur").reduce((a, x) => a + CL.dnIDR(x), 0);
          const b = { supplier: f.supplier, po: f.po, nilai: Number(f.nilai) || 0, ppn: ppn(), admin: 0, dnKomp: k, dnRetur: r, dns: f.dns }; b.totalBayar = CL.round2(b.nilai - k - r + b.ppn); return b; };
        openModal({ title: "Buat Request Payment PO", size: "xl", ok: "Simpan Request",
          body: () => { const a = avail(f.supplier).filter((x) => !f.dns.includes(x.no));
            return row("Nama Supplier", sel("supplier", f.supplier, sups, { struct: 1 }), 1) + row("No. PO", inp("po", f.po), 1) + row("Nilai PO / Invoice (Rp)", inp("nilai", f.nilai, { num: 1, step: "0.01" }), 1)
            + row("PPN", `<b data-calc="ppn"></b>`) + `<h6>Alokasi Debit Note <span class="ap-badge">bagian tambahan</span></h6>` + row("Alokasi Debit Note", `<div class="ap-gap">${a.length ? `<select class="ap-sel ap-in" data-f="pick" style="width:auto;min-width:260px"><option value="">(Pilih Debit Note)</option>${a.map((x) => `<option value="${esc(x.no)}">${esc(x.no)} — ${esc(x.jalur)} — ${num(CL.dnIDR(x))}</option>`).join("")}</select><button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="add">Add Debit Note</button>` : '<span class="ap-hint">Tidak ada Debit Note tersedia untuk supplier ini.</span>'}</div>`)
            + tbl(["No. Debit Note", "Jalur", "Nilai Alokasi (IDR)", ""], f.dns.map((no, i) => { const x = S().dn.find((y) => y.no === no); return `<tr><td>${esc(no)}</td><td>${lab(x.jalur)}</td><td class="ap-r">${num(CL.dnIDR(x))}</td><td><button type="button" class="ap-btn ap-btn--sm ap-btn--danger" data-act="rm" data-id="${i}">Hapus</button></td></tr>`; }).join(""), "Belum ada Debit Note dialokasikan.")
            + row("Jumlah Diajukan (bersih)", `<b data-calc="tot"></b>`); },
          calc: { ppn: () => rp(ppn()), tot: () => { const b = sums(); return `<span class="${b.totalBayar < 0 ? "cl-bad" : ""}">${rp(b.totalBayar)}</span>`; } },
          onField(el) { const k = el.dataset.f; if (k === "pick") { f.pick = el.value; return; } f[k] = isNumEl(el) ? (el.value === "" ? 0 : pv(el)) : el.value; if (k === "supplier") { f.dns = []; drawModal(); } else calc(); },
          act: { add() { if (!f.pick) { warn("Pilih Debit Note terlebih dahulu."); return; } f.dns.push(f.pick); f.pick = ""; drawModal(); }, rm(x) { f.dns.splice(+x.id, 1); drawModal(); } },
          onOk() { const b = sums(); if (!f.dns.length) { warn("Alokasikan minimal satu Debit Note."); return; } if (b.totalBayar < 0) { warn("Jumlah negatif: nilai Debit Note melebihi nilai PO."); return; }
            confirmThen({ text: "Simpan Request Payment " + f.po + " dengan alokasi Debit Note?" }, () => { A().requestPayment(b); closeModal(); render(); done("Request Payment tersimpan; Debit Note berstatus Sudah Alokasi ke Hutang."); }); } });
      },
    },
  };

  /* ======================= 8. Tagihan Debit Note Langsung (FR-89) ======================= */
  const tagStat = (d) => (d.status === "Sudah Terbit" ? "Belum ditagih" : "Sudah ditagih");
  P.tagihan = {
    title: "Penagihan Debit Note ke Supplier", role: "Accounting / Finance", fr: "FR-89",
    hint: "Debit Note yang ditagihkan langsung ke supplier (bukan sebagai pengurang PO).",
    render() {
      const l = S().dn.filter((d) => ["Sudah Terbit", "Belum Terima Uang", "Sudah Terima Uang"].includes(d.status));
      return card("Daftar Debit Note Siap Ditagih", tbl(["No. Debit Note", "Nama Supplier", "Jalur", "Total Nilai (IDR)", "Status", "Action"],
        l.map((d) => `<tr><td>${esc(d.no)}</td><td>${esc(d.supplier)}</td><td>${lab(d.jalur)}</td><td class="ap-r">${num(CL.dnIDR(d))}</td><td>${lab(tagStat(d))}</td><td class="ap-act">${d.status === "Sudah Terbit" ? btn("primary", "new", d.no, "Buat Penagihan") : "-"}</td></tr>`).join(""), "Belum ada Debit Note yang terbit."))
        + `<p class="note">Debit Note yang dialokasikan ke PO (Pembayaran PO dengan DN) tidak tampil di sini. Setelah penagihan diterbitkan, baris otomatis muncul di Penerimaan Uang dari Supplier.</p>`;
    },
    act: {
      new(d) {
        const x = S().dn.find((y) => y.no === d.id), f = { due: "", note: "" };
        openModal({ title: `Buat Penagihan — ${x.no} (${x.supplier})`, ok: "Terbitkan Tagihan",
          body: () => kv([["Jalur", esc(x.jalur)], ["Tgl Terbit", fdate(x.tgl)], ["Total Nilai", rp(CL.dnIDR(x))]]) + `<p class="cl-assume">Debit note ini tidak dialokasikan ke PO manapun. Tagihan dikirim langsung ke supplier dan otomatis muncul di menu Penerimaan Uang dari Supplier setelah diterbitkan.</p>`
            + row("Tgl Jatuh Tempo Tagihan", inp("due", f.due, { type: "date" }), 1) + row("Catatan Tagihan (opsional)", txt("note", f.note)),
          onField: (el) => { f[el.dataset.f] = el.value; },
          onOk() { if (!f.due) { warn("Tanggal jatuh tempo wajib diisi."); return; } confirmThen({ text: "Terbitkan tagihan " + x.no + " ke supplier?" }, () => { A().buatTagihan(x.no, f); closeModal(); render(); done("Tagihan diterbitkan; DN masuk Penerimaan Uang dari Supplier."); }); } });
      },
    },
  };

  /* ======================= 9. Penerimaan Uang dari Supplier (FR-90) ======================= */
  const fp = { q: "", jalur: "", status: "", tgl: "" };
  const REK = ["BCA - 001 302 5567 (Rek. Operasional)", "Mandiri - 123 000 4567 (Rek. Valas)"];
  P.terima = {
    title: "Penerimaan Uang dari Supplier", role: "Accounting / Finance", fr: "FR-90",
    hint: "Daftar debit note yang menunggu atau sudah diterima pembayarannya.",
    render() {
      const all = S().dn.filter((d) => d.status === "Belum Terima Uang" || d.status === "Sudah Terima Uang"), bel = all.filter((d) => d.status === "Belum Terima Uang");
      const sum = (a) => a.reduce((t, d) => t + CL.dnIDR(d), 0);
      const mon = CL.today().slice(0, 7), diterima = all.filter((d) => d.terima && d.terima.tgl.slice(0, 7) === mon);
      const l = all.filter((d) => (!fp.jalur || d.jalur === fp.jalur) && (!fp.status || d.status === fp.status) && (!fp.tgl || d.tgl === fp.tgl) && (!fp.q || (d.no + d.supplier).toLowerCase().includes(fp.q.toLowerCase())));
      return `<div class="cl-sumrow">${stat(rp(sum(bel)), "Total belum diterima")}${stat(rp(sum(bel.filter((d) => d.jalur === "Retur"))), "Jalur retur")}${stat(rp(sum(bel.filter((d) => d.jalur === "Kompensasi"))), "Jalur kompensasi")}${stat(rp(diterima.reduce((t, d) => t + d.terima.nominal, 0)), "Diterima bulan ini")}</div>
        ${card("Daftar Debit Note", `<div class="cl-tools"><input class="ap-in" data-fp="q" placeholder="Cari no. debit note / supplier..." value="${esc(fp.q)}"><select class="ap-sel ap-in" data-fp="jalur"><option value="">Semua jalur</option><option${fp.jalur === "Kompensasi" ? " selected" : ""}>Kompensasi</option><option${fp.jalur === "Retur" ? " selected" : ""}>Retur</option></select>
          <select class="ap-sel ap-in" data-fp="status"><option value="">Semua status</option><option${fp.status === "Belum Terima Uang" ? " selected" : ""}>Belum Terima Uang</option><option${fp.status === "Sudah Terima Uang" ? " selected" : ""}>Sudah Terima Uang</option></select>
          <label>Tgl terbit : <input class="ap-in" type="date" data-fp="tgl" value="${esc(fp.tgl)}"></label></div>
          ${tbl(["No. Debit Note", "Tgl Terbit", "Nama Supplier", "Jalur", "Total Nilai (IDR)", "Status", "Aksi"], l.map((d) => `<tr><td>${esc(d.no)}</td><td>${fdate(d.tgl)}</td><td>${esc(d.supplier)}</td><td>${lab(d.jalur)}</td><td class="ap-r">${num(CL.dnIDR(d))}</td><td>${lab(d.status)}</td><td class="ap-act">${d.status === "Belum Terima Uang" ? btn("primary", "terima", d.no, "Konfirmasi terima") : "-"}</td></tr>`).join(""), "Tidak ada debit note.")}`)}`;
    },
    act: {
      terima(d) {
        const x = S().dn.find((y) => y.no === d.id), c = dnClaim(x), nom = CL.dnIDR(x), f = { tgl: CL.today(), rek: REK[0], nominal: nom, note: "" };
        openModal({ title: `Konfirmasi Penerimaan Uang — ${x.no}`, size: "lg", ok: "Konfirmasi & Posting Jurnal",
          body: () => `<h6>Referensi Proses (${esc(x.jalur)})</h6>` + kv([["No. Claim", esc(x.claim || "-")], ["Nama Supplier", esc(x.supplier)], ["Surat jalan", esc(x.sj || "-")], ["Material", c ? esc(c.material + " — " + c.coil) : "-"], ["Tgl Terbit", fdate(x.tgl)], ["Total Nilai", rp(nom)]])
            + "<h6>Detail Penerimaan</h6>" + row("Tgl Diterima", inp("tgl", f.tgl, { type: "date" }), 1) + row("Rekening Penerima", sel("rek", f.rek, REK), 1) + row("Nominal Diterima (Rp)", inp("nominal", f.nominal, { num: 1, step: "0.01" }) + '<div data-calc="selisih"></div>', 1)
            + row("Bukti Transfer", '<input type="file" class="ap-in">') + row("Catatan (opsional)", txt("note", f.note)) + '<div data-calc="jr"></div>',
          calc: { selisih: () => (Math.abs(f.nominal - nom) > 0.005 ? `<div class="ap-hint cl-bad">Nominal berbeda ${rp(f.nominal - nom)} dari debit note — harus sama, konfirmasi akan ditolak.</div>` : ""), jr: () => jr(CL.J.terima(Number(f.nominal) || 0)) },
          onField: (el) => { f[el.dataset.f] = isNumEl(el) ? pv(el) || 0 : el.value; calc(); },
          onOk() { if (!f.tgl || !(f.nominal > 0)) { warn("Tanggal dan nominal diterima wajib diisi."); return; } if (Math.abs(f.nominal - nom) > 0.005) { warn(`Nominal diterima (${rp(f.nominal)}) harus sama dengan nominal Debit Note (${rp(nom)}).`); return; } confirmThen({ text: "Posting jurnal Db Bank / Cr Debit Note?" }, () => { A().terimaUang(x.no, { tgl: f.tgl, rek: f.rek, nominal: Number(f.nominal), note: f.note }); closeModal(); render(); done("Penerimaan uang dikonfirmasi dan jurnal diposting."); }); } });
      },
    },
  };

  /* ---------- kerangka halaman ---------- */
  const USERS = { Purchasing: "Rina P.", PPIC: "Dedi K.", Accounting: "Andri P." };
  let page = null;
  function render() { $("#clBody").innerHTML = page.render(); }
  function init() {
    page = P[document.body.dataset.cpage];
    if (!page) { document.body.textContent = "Halaman tidak dikenal"; return; }
    const role = page.role.split(/[ /(]/)[0];
    document.body.dataset.user = USERS[role] || "Rina P.";
    document.body.dataset.role = page.role;
    document.title = page.title + " — Claim Material";
    document.body.insertAdjacentHTML("afterbegin", `<main class="page"><div class="page__header"><div><h1>${esc(page.title)}</h1><p class="muted">${esc(page.hint)}</p><p class="muted"><span class="badge badge--note">${page.fr}</span> Diisi oleh: <strong>${esc(page.role)}</strong></p></div><div class="page__header-status">${page.head ? page.head() : ""}</div></div><div id="clBody"></div></main>
      <div class="actionbar"><div class="actionbar__note"><span class="muted">Mockup Claim Material (FEAT-11): angka contoh dari workbook Rev.2; data tersimpan di localStorage browser. Nama tabel/kolom mengikuti ERD (grup Claim Material).</span></div><div class="actionbar__btns"><button type="button" class="btn btn--ghost" id="btnReset">Reset ke Data Contoh</button></div></div>
      <div class="toast" id="toast" hidden></div><div class="pc-modal" id="pcModal" hidden></div><div class="pc-modal pc-modal--dlg" id="pcDialog" hidden></div><div class="pc-print" id="pcPrint" hidden></div>`);
    render();
    document.addEventListener("click", (e) => {
      const b = e.target.closest("[data-act]"); if (!b) return;
      const a = b.dataset.act;
      if (a === "mclose") { closeModal(); return; }
      if (a === "mok") { if (modal && modal.onOk) modal.onOk(); return; }
      if (modal && modal.act && modal.act[a] && b.closest("#pcModal")) { modal.act[a](b.dataset); return; }
      if (page.act[a]) page.act[a](b.dataset);
    });
    const onField = (e) => {
      const t = e.target;
      if (t.dataset.flt) { flt[t.dataset.flt] = t.value; render(); return; }
      if (t.dataset.fp) { fp[t.dataset.fp] = t.value; render(); const n = $(`[data-fp="${t.dataset.fp}"]`); if (n && n.tagName === "INPUT" && n.type === "text") { n.focus(); n.setSelectionRange(n.value.length, n.value.length); } return; }
      if (modal && modal.onField && t.closest("#pcModal") && t.dataset.f !== undefined) {
        if (e.type === "input" && t.tagName === "SELECT") return;
        if (e.type === "change" && t.tagName !== "SELECT" && t.type !== "date") { modal.onField(t, "change"); return; }
        modal.onField(t, e.type);
      }
    };
    document.addEventListener("change", (e) => {
      const t = e.target; if (!t.dataset) return;
      if (t.dataset.selAll !== undefined) { visUndecided.forEach((id) => (t.checked ? selClaims.add(id) : selClaims.delete(id))); render(); }
      else if (t.dataset.sel !== undefined) { if (t.checked) selClaims.add(t.dataset.sel); else selClaims.delete(t.dataset.sel); render(); }
    });
    document.addEventListener("input", onField);
    document.addEventListener("change", (e) => { if (e.target.tagName === "SELECT" || e.target.type === "date") onField(e); });
    document.addEventListener("keydown", (e) => { if (e.key === "Escape" && !$("#pcDialog").hidden) return; if (e.key === "Escape" && !$("#pcPrint").hidden) { $("#pcPrint").hidden = true; $("#pcPrint").innerHTML = ""; return; } if (e.key === "Escape" && modal) closeModal(); });
    $("#btnReset").addEventListener("click", () => { CL.reset(); render(); toast("Data dikembalikan ke data contoh.", "warn"); });
    if (!CL.storageOk()) toast("Penyimpanan browser tidak tersedia: data hilang saat refresh.", "warn");
  }
  init();
})();
