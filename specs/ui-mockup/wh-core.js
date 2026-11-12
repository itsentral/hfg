/* ============================================================
   wh-core.js — Pembantu bersama halaman Gudang Coil (mockup): format, tabel, modal bertumpuk,
   dialog, toast, pratinjau cetak, dan kerangka halaman. Halaman sendiri ada di wh-pages.js.
   Sidebar/top bar: berry-shell.js (auto-mount). Pola mengikuti claim-pages.js.
   ============================================================ */
(() => {
  "use strict";
  const W = window.WH;
  const $ = (s, r = document) => r.querySelector(s);
  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));
  const num = (n, d = 2) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: d, maximumFractionDigits: d });
  const rp = (n) => "Rp " + num(n);
  const MON = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
  const fdate = (iso) => { if (!iso) return "-"; const [y, m, d] = String(iso).slice(0, 10).split("-"); return `${d} ${MON[+m - 1]} ${y}`; };
  const LAB = {
    success: ["Material Confirmed", "Confirmed", "Released", "IN", "Available", "Aktif", "Scanned"],
    warning: ["Material Requested", "Draft", "Belum Scan"],
    info: ["Material On Load", "Dipilih di SPK Coil", "Lock"],
    danger: ["Cancelled", "OUT", "Non Aktif"],
  };
  const lab = (s) => { const k = Object.keys(LAB).find((x) => LAB[x].includes(s)) || "secondary"; return `<span class="ap-label ap-label--${k}">${esc(s || "-")}</span>`; };
  const btn = (cls, act, id, label, extra = "") => `<button type="button" class="ap-btn ap-btn--sm ap-btn--${cls}" data-act="${act}" data-id="${esc(id)}"${extra}>${label}</button>`;
  const tbl = (heads, rows, empty, foot) => `<div class="ap-tablewrap"><table class="ap-table ap-table--striped ap-table--hover"><thead><tr>${heads.map((h) => `<th>${h}</th>`).join("")}</tr></thead><tbody>${rows || `<tr><td class="ap-empty" colspan="${heads.length}">${empty || "Belum ada data."}</td></tr>`}</tbody>${foot ? `<tfoot>${foot}</tfoot>` : ""}</table></div>`;
  const card = (title, body, head = "") => `<section class="ap-card ap-card--shadow"><div class="ap-card__head ap-card__head--bar"><h5>${title}</h5>${head}</div><div class="ap-card__body">${body}</div></section>`;
  const stat = (v, k) => `<div class="stat"><div class="stat__v">${v}</div><div class="stat__k">${k}</div></div>`;
  const kv = (pairs) => `<dl class="cl-kv">${pairs.map((p) => `<dt>${p[0]}</dt><dd>${p[1]}</dd>`).join("")}</dl>`;
  const row = (label, field, req) => `<div class="ap-row"><div class="c3"><label class="ap-lbl">${label}${req ? ' <span class="ap-req">*</span>' : ""}</label></div><div class="c9" style="grid-column: span 9">${field}</div></div>`;
  const inp = (f, v, o = {}) => `<input class="ap-in ${o.num ? "ap-in--num" : ""} ${o.ro ? "ap-in--ro" : ""}" type="${o.num ? "text" : (o.type || "text")}"${o.num ? ' inputmode="decimal" data-numfmt="1"' : ""} data-f="${f}" value="${esc(o.num ? NumFmt.fmt(v) : v)}"${o.ro ? " readonly" : ""}${o.ph ? ` placeholder="${esc(o.ph)}"` : ""}${o.cls ? ` data-cls="${o.cls}"` : ""}>`;
  const sel = (f, v, opts, o = {}) => `<select class="ap-sel ap-in" data-f="${f}"${o.struct ? " data-struct" : ""}>${o.blank != null ? `<option value="">${esc(o.blank)}</option>` : ""}${opts.map((x) => { const val = Array.isArray(x) ? x[0] : x, tx = Array.isArray(x) ? x[1] : x; return `<option value="${esc(val)}"${String(val) === String(v) ? " selected" : ""}>${esc(tx)}</option>`; }).join("")}</select>`;
  const txt = (f, v, o = {}) => `<textarea class="ap-in" data-f="${f}" placeholder="${esc(o.ph || "")}"${o.rows ? ` rows="${o.rows}"` : ""}>${esc(v)}</textarea>`;
  const isNumEl = (el) => el.dataset.numfmt === "1";
  const pv = (el) => parseFloat(isNumEl(el) ? NumFmt.norm(el.value) : el.value);
  const tabs = (items, cur, key = "tab") => `<div class="cl-tabs">${items.map((t) => `<a class="${t[0] === cur ? "is-on" : "is-off"}" data-act="tab" data-id="${t[0]}" data-key="${key}">${t[1]}</a>`).join("")}</div>`;

  /* ---------- toast & dialog ---------- */
  let toastT = null;
  function toast(msg, kind) { const t = $("#toast"); t.textContent = msg; t.className = "toast" + (kind ? " toast--" + kind : ""); t.hidden = false; clearTimeout(toastT); toastT = setTimeout(() => (t.hidden = true), 3400); }
  function dialog(cfg) {
    return new Promise((resolve) => {
      const h = $("#pcDialog"); h.hidden = false;
      const sym = { warning: "!", success: "&#10003;", error: "&#10005;" }[cfg.icon || "warning"];
      const fin = (v) => { h.hidden = true; h.innerHTML = ""; resolve(v); };
      h.innerHTML = `<div class="ap-dlg__overlay"></div><div class="ap-dlg" role="alertdialog"><div class="ap-dlg__icon ap-dlg__icon--${cfg.icon || "warning"}">${sym}</div><h3>${esc(cfg.title)}</h3>${cfg.text ? `<p>${esc(cfg.text)}</p>` : ""}${cfg.html || ""}<div class="ap-dlg__btns">${cfg.cancel ? `<button type="button" class="ap-btn ap-btn--secondary" data-d="0">${esc(cfg.cancel)}</button>` : ""}<button type="button" class="ap-btn ap-btn--primary" data-d="1">${esc(cfg.ok || "OK")}</button></div></div>`;
      h.querySelectorAll("[data-d]").forEach((b) => b.addEventListener("click", () => fin(b.dataset.d === "1")));
    });
  }
  const warn = (text) => dialog({ icon: "warning", title: "Warning", text });
  const done = (text, html) => dialog({ icon: "success", title: "Success", text, html });
  const confirmThen = (cfg, fn) => dialog(Object.assign({ icon: "warning", title: "Are you sure?", ok: "Ya, Proses!", cancel: "Batal" }, cfg)).then((ok) => { if (ok) fn(); });

  /* ---------- modal bertumpuk ---------- */
  const stack = [];
  const topModal = () => stack[stack.length - 1] || null;
  function openModal(cfg) { stack.push(cfg); drawModal(); }
  function closeModal() { stack.pop(); drawModal(); }
  function drawModal() {
    const h = $("#pcModal"), modal = topModal();
    const keep = h.querySelector(".ap-modal__body"), top = keep ? keep.scrollTop : 0;
    if (!modal) { h.hidden = true; h.innerHTML = ""; return; }
    h.hidden = false;
    h.innerHTML = `<div class="modal__overlay" data-act="mclose"></div>
      <div class="ap-modal ap-modal--${modal.size || "lg"} ap-modal--hdr-${modal.hdr || "primary"}" role="dialog" aria-modal="true">
        <div class="ap-modal__head"><h4>${esc(modal.title)}</h4><button type="button" class="ap-modal__x" data-act="mclose" aria-label="Close">&#10005;</button></div>
        <div class="ap-modal__body">${modal.body()}</div>
        <div class="ap-modal__foot">${(modal.foot || "")}${modal.ok ? `<button type="button" class="ap-btn ap-btn--${modal.okCls || "primary"}" data-act="mok"${modal.okDisabled && modal.okDisabled() ? " disabled" : ""}>${esc(modal.ok)}</button>` : ""}<button type="button" class="ap-btn ap-btn--dark" data-act="mclose">${modal.ok ? "Cancel" : "Close"}</button></div>
      </div>`;
    h.querySelector(".ap-modal__body").scrollTop = top;
    calc();
    if (modal.after) modal.after(h);
  }
  function calc() {
    const m = topModal(), ctx = m && m.calc;
    document.querySelectorAll("#pcModal [data-calc]").forEach((el) => { const f = ctx && ctx[el.dataset.calc]; if (f) el.innerHTML = f(el.dataset.arg); });
  }

  /* ---------- pratinjau cetak ---------- */
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

  /* ---------- QR contoh (pola deterministik, bukan QR sungguhan) ---------- */
  function qrSvg(text) {
    let h = 2166136261; for (const ch of text) { h ^= ch.charCodeAt(0); h = Math.imul(h, 16777619) >>> 0; }
    const n = 25, cell = 8; let r = "";
    const finder = (x, y) => {
      for (const [cx, cy] of [[0, 0], [n - 7, 0], [0, n - 7]]) {
        const lx = x - cx, ly = y - cy;
        if (lx >= -1 && lx <= 7 && ly >= -1 && ly <= 7) { if (lx < 0 || ly < 0 || lx > 6 || ly > 6) return 0; return lx === 0 || lx === 6 || ly === 0 || ly === 6 || (lx >= 2 && lx <= 4 && ly >= 2 && ly <= 4) ? 1 : 0; }
      }
      return null;
    };
    for (let y = 0; y < n; y++) for (let x = 0; x < n; x++) {
      h = (Math.imul(h, 1103515245) + 12345) >>> 0;
      const f = finder(x, y), on = f == null ? (h >>> 16) & 1 : f;
      if (on) r += `<rect x="${x * cell}" y="${y * cell}" width="${cell}" height="${cell}"/>`;
    }
    return `<svg viewBox="0 0 ${n * cell} ${n * cell}" xmlns="http://www.w3.org/2000/svg" fill="#111">${r}</svg>`;
  }

  /* ---------- kerangka halaman ---------- */
  const USERS = { Gudang: "Budi S.", PPIC: "Dedi K.", Produksi: "Agus H.", Accounting: "Andri P." };
  let page = null, P = {};
  const ui = {};                          // state tampilan per halaman (filter, tab, mode)
  const render = () => { $("#whBody").innerHTML = page.render(); calc(); if (page.after) page.after(); };
  function init(pages) {
    P = pages;
    page = P[document.body.dataset.wpage];
    if (!page) { document.body.textContent = "Halaman tidak dikenal"; return; }
    const role = page.role.split(/[ /(]/)[0];
    document.body.dataset.user = USERS[role] || "Budi S.";
    document.body.dataset.role = page.role;
    document.title = page.title + " — Gudang Coil";
    Object.assign(ui, page.ui || {});
    document.body.insertAdjacentHTML("afterbegin", `<main class="page"><div class="page__header"><div><h1>${esc(page.title)}</h1><p class="muted">${esc(page.hint)}</p><p class="muted"><span class="badge badge--note">${esc(page.mod)}</span> Dipakai oleh: <strong>${esc(page.role)}</strong></p></div></div><div id="whBody"></div></main>
      <div class="actionbar"><div class="actionbar__note"><span class="muted">Mockup Gudang Coil: dibuat dari modul aplikasi yang sudah ada (referensi baca-saja). Angka contoh, bukan data klien; data tersimpan di localStorage browser.</span></div><div class="actionbar__btns"><button type="button" class="btn btn--ghost" id="btnReset">Reset ke Data Contoh</button></div></div>
      <div class="toast" id="toast" hidden></div><div class="pc-modal" id="pcModal" hidden></div><div class="pc-modal pc-modal--dlg" id="pcDialog" hidden></div><div class="pc-print" id="pcPrint" hidden></div>`);
    render();
    document.addEventListener("click", (e) => {
      const mm = document.querySelector(".wh-menu__list:not([hidden])");
      if (mm && !e.target.closest(".wh-menu")) mm.hidden = true;
      const b = e.target.closest("[data-act]"); if (!b) return;
      const a = b.dataset.act, modal = topModal();
      if (a === "menu") { const l = b.parentElement.querySelector(".wh-menu__list"); const was = l.hidden; document.querySelectorAll(".wh-menu__list").forEach((x) => (x.hidden = true)); l.hidden = !was; return; }
      if (a === "mclose") { if (modal && modal.onClose) modal.onClose(); closeModal(); return; }
      if (a === "mok") { if (modal && modal.onOk) modal.onOk(); return; }
      if (a === "tab") { ui[b.dataset.key || "tab"] = b.dataset.id; render(); return; }
      if (modal && modal.act && modal.act[a] && b.closest("#pcModal")) { modal.act[a](b.dataset, b); return; }
      if (page.act && page.act[a]) { document.querySelectorAll(".wh-menu__list").forEach((x) => (x.hidden = true)); page.act[a](b.dataset, b); }
    });
    const onField = (e) => {
      const t = e.target; if (!t.dataset) return;
      if (t.dataset.u) {                                              // filter halaman
        ui[t.dataset.u] = t.value; render();
        const n = $(`[data-u="${t.dataset.u}"]`); if (n && n.tagName === "INPUT" && n.type === "text") { n.focus(); n.setSelectionRange(n.value.length, n.value.length); }
        return;
      }
      const modal = topModal();
      if (t.dataset.f !== undefined) {
        if (t.closest("#pcModal")) {
          if (!modal || !modal.onField) return;
          if (e.type === "input" && t.tagName === "SELECT") return;
          if (e.type === "change" && t.tagName !== "SELECT" && t.type !== "date" && t.type !== "checkbox") { modal.onField(t, "change"); return; }
          modal.onField(t, e.type);
        } else if (page.onField) {
          if (e.type === "input" && t.tagName === "SELECT") return;
          if (e.type === "change" && t.tagName !== "SELECT" && t.type !== "date" && t.type !== "checkbox") { page.onField(t, "change"); return; }
          page.onField(t, e.type);
        }
      }
    };
    document.addEventListener("input", onField);
    document.addEventListener("change", (e) => { const t = e.target; if (t.tagName === "SELECT" || t.type === "date" || t.type === "checkbox") onField(e); });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && e.target.dataset && e.target.dataset.enter) { e.preventDefault(); const m = topModal(); if (m && m.act && m.act[e.target.dataset.enter]) m.act[e.target.dataset.enter]({}, e.target); return; }
      if (e.key !== "Escape") return;
      if (!$("#pcDialog").hidden) return;
      if (!$("#pcPrint").hidden) { $("#pcPrint").hidden = true; $("#pcPrint").innerHTML = ""; return; }
      if (topModal()) closeModal();
    });
    $("#btnReset").addEventListener("click", () => { W.reset(); stack.length = 0; drawModal(); Object.keys(ui).forEach((k) => delete ui[k]); Object.assign(ui, page.ui || {}); render(); toast("Data dikembalikan ke data contoh.", "warn"); });
    if (!W.storageOk()) toast("Penyimpanan browser tidak tersedia: data hilang saat refresh.", "warn");
  }

  window.WHUI = { W, $, esc, num, rp, fdate, lab, btn, tbl, card, stat, kv, row, inp, sel, txt, isNumEl, pv, tabs, toast, dialog, warn, done, confirmThen, openModal, closeModal, drawModal, calc, printDoc, ppHead, ppMeta, ppSign, qrSvg, ui, render, init, topModal };
})();
