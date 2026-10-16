/* ============================================================
   pc-shell.js — Kerangka bersama menu Product Costing: top bar, menu samping,
   binding input (data-path), aksi (data-act), simpan/reset, toast.
   Tiap halaman: <body data-page="..."> dan pc-pages.js mendaftarkan isinya.
   ============================================================ */
(() => {
  "use strict";
  const PC = window.PC;

  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));

  // Urutan menu = nomor di sheet "Simulasi Program" + Simulasi Costing. Kode UC = docs/workbook.xlsx.
  const MENU = [
    { group: "Master Data", items: [
      { id: "produk", file: "pc-produk.html", label: "Master Produk", role: "Engineering", uc: "UC-N05" },
      { id: "material", file: "pc-material.html", label: "Master Material", role: "Engineering", uc: "UC-N06" },
      { id: "kurs", file: "pc-kurs.html", label: "Kurs", role: "Accounting", uc: "UC-N07" },
      { id: "price", file: "pc-price-reference.html", label: "Price Reference", role: "Purchasing", uc: "UC-N08" },
      { id: "bom", file: "pc-bom.html", label: "BOM Produk", role: "Engineering", uc: "UC-N09" },
    ] },
    { group: "Proses & Rate", items: [
      { id: "flow", file: "pc-flow.html", label: "Flow Process & Cycletime", role: "Engineering", uc: "UC-N10" },
      { id: "mpr", file: "pc-man-power-rate.html", label: "Man Power Rate", role: "Accounting", uc: "UC-N11" },
      { id: "bpkfoh", file: "pc-bpk-foh.html", label: "Rate Bahan Pendukung & FOH", role: "Accounting", uc: "UC-N12" },
      { id: "general", file: "pc-general-rate.html", label: "General Rate", role: "Accounting", uc: "UC-N13" },
    ] },
    { group: "Hasil", items: [
      { id: "simulasi", file: "pc-simulasi.html", label: "Simulasi Costing & Pricelist", role: "Accounting / Sales / Engineering", uc: "UC-N14" },
    ] },
  ];
  const flat = MENU.reduce((a, g) => a.concat(g.items), []);
  const USERS = { Engineering: "Eka R.", Accounting: "Andri P.", Purchasing: "Pandu S." };

  const getPath = (o, p) => p.split(".").reduce((x, k) => (x == null ? x : x[k]), o);
  const setPath = (o, p, v) => {
    const ks = p.split("."); const last = ks.pop();
    const t = ks.reduce((x, k) => x[k], o); t[last] = v;
  };

  /* ---- helper pembangun HTML yang dipakai pc-pages.js ---- */
  const H = {
    esc,
    in(path, val, o = {}) {
      return `<input class="pc-in ${o.num ? "pc-in--num" : ""} ${o.cls || ""}" type="${o.num ? "number" : "text"}"${o.step ? ` step="${o.step}"` : ""}${o.min != null ? ` min="${o.min}"` : ""} data-path="${path}" data-t="${o.t || (o.num ? "num" : "text")}"${o.struct ? " data-struct" : ""} value="${esc(val)}">`;
    },
    sel(path, val, opts, o = {}) {
      return `<select class="pc-in ${o.cls || ""}" data-path="${path}" data-t="text"${o.struct ? " data-struct" : ""}>${opts.map((x) => {
        const v = typeof x === "object" ? x.v : x, l = typeof x === "object" ? x.l : x;
        return `<option value="${esc(v)}"${String(v) === String(val) ? " selected" : ""}>${esc(l)}</option>`;
      }).join("")}</select>`;
    },
    calc(key, arg = "") { return `<span class="pc-calc" data-calc="${key}" data-arg="${esc(arg)}"></span>`; },
    del(list, idx) { return `<button type="button" class="btn-icon" title="Hapus baris" data-act="del" data-list="${list}" data-idx="${idx}">&#10005;</button>`; },
    card(num, title, hint, body) {
      return `<section class="card"><div class="card__head"><h2>${num ? `<span class="card__num">${num}</span> ` : ""}${title}</h2>${hint ? `<p class="card__hint">${hint}</p>` : ""}</div><div class="card__body">${body}</div></section>`;
    },
    table(head, rows, o = {}) {
      return `<div class="table-wrap"><table class="tbl ${o.cls || ""}"><thead><tr>${head.map((h) => (typeof h === "string" ? `<th>${h}</th>` : `<th class="${h.c || ""}"${h.w ? ` style="width:${h.w}px"` : ""}>${h.t}</th>`)).join("")}</tr></thead><tbody>${rows || `<tr><td class="empty" colspan="${head.length}">Belum ada data.</td></tr>`}</tbody>${o.foot ? `<tfoot>${o.foot}</tfoot>` : ""}</table></div>`;
    },
  };

  let page = null;
  let dirty = false;
  const $ = (s, r = document) => r.querySelector(s);

  function markDirty(on) {
    dirty = on;
    const b = $("#saveStatus");
    if (!b) return;
    b.textContent = on ? "Perubahan belum disimpan" : "Tersimpan";
    b.className = "badge " + (on ? "badge--draft" : "badge--done");
  }

  let toastTimer = null;
  function toast(msg, kind = "") {
    const t = $("#toast");
    t.textContent = msg;
    t.className = "toast" + (kind ? " toast--" + kind : "");
    t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => (t.hidden = true), 2800);
  }

  function refreshCalc() {
    document.querySelectorAll("[data-calc]").forEach((el) => {
      const fn = page.calc && page.calc[el.dataset.calc];
      if (fn) el.innerHTML = fn(el.dataset.arg);
    });
  }

  /* ---- Modal: Detil Input / View / Edit terpisah dari list ---- */
  // modal = null | { mode: "add"|"edit"|"view", title, bodyFn, size, onSave, onClose }
  let modal = null;
  function openModal(cfg) { modal = cfg; renderModal(); }
  function closeModal() { if (modal && modal.onClose) modal.onClose(); modal = null; renderModal(); }
  function renderModal() {
    const host = $("#pcModal");
    if (!host) return;
    if (!modal) { host.hidden = true; host.innerHTML = ""; return; }
    const ro = modal.mode === "view";
    const body = `<div class="modal__overlay" data-act="mclose"></div>
      <div class="modal__box ${modal.size ? "modal__box--" + modal.size : ""}" role="dialog" aria-modal="true">
        <div class="modal__head"><h2>${esc(modal.title || "")}</h2><button type="button" class="modal__x" data-act="mclose" aria-label="Tutup">&#10005;</button></div>
        <div class="modal__body ${ro ? "is-view" : ""}">${modal.bodyFn ? modal.bodyFn() : ""}</div>
        <div class="modal__foot">
          <button type="button" class="btn btn--ghost" data-act="mclose">${ro ? "Tutup" : "Batal"}</button>
          ${ro ? "" : `<button type="button" class="btn btn--primary" data-act="msave">Simpan</button>`}
        </div>
      </div>`;
    host.hidden = false;
    host.innerHTML = body;
    // read-only pada mode view
    if (ro) host.querySelectorAll("input,select,textarea").forEach((el) => { el.setAttribute("disabled", ""); });
    refreshCalc();
    if (modal.after) modal.after();
  }

  function render() {
    $("#pcBody").innerHTML = page.render();
    if (page.after) page.after();
    renderModal();
    refreshCalc();
  }

  function onInput(e) {
    const t = e.target;
    if (!t.dataset || !t.dataset.path) return;
    const inModal = !!(t.closest && t.closest("#pcModal"));
    let v = t.value;
    if (t.dataset.t === "num") v = v === "" ? 0 : parseFloat(v);
    else if (t.dataset.t === "numnull") v = v === "" ? null : parseFloat(v);
    else if (t.dataset.t === "pct") v = (v === "" ? 0 : parseFloat(v)) / 100;
    setPath(PC.state, t.dataset.path, v);
    if (page.onChange) page.onChange(t.dataset.path, v);
    if (page.noSave) PC.save(); else markDirty(true);
    // struct change: re-render hanya bagian yang relevan (modal sendiri, atau list)
    if (e.type === "change" && "struct" in t.dataset) { if (inModal) renderModal(); else render(); }
    else refreshCalc();
  }

  function onClick(e) {
    const b = e.target.closest("[data-act]");
    if (!b) return;
    const act = b.dataset.act;
    // aksi modal bawaan shell
    if (act === "mclose") { closeModal(); return; }
    if (act === "msave") {
      if (modal && modal.onSave) modal.onSave();
      if (!page.noSave) markDirty(true); else PC.save();
      modal = null; render(); toast("Tersimpan.", "ok"); return;
    }
    if (act === "del") {
      const arr = getPath(PC.state, b.dataset.list);
      arr.splice(Number(b.dataset.idx), 1);
      if (page.onChange) page.onChange(b.dataset.list, null);
    } else if (page.act && page.act[act]) {
      page.act[act](b.dataset);
    } else return;
    if (page.noSave) PC.save(); else markDirty(true);
    render();
  }

  function init() {
    const id = document.body.dataset.page;
    page = (window.PC_PAGES || {})[id];
    const cur = flat.find((x) => x.id === id);
    if (!page || !cur) { document.body.textContent = "Halaman tidak dikenal: " + id; return; }
    const role = cur.role.split(" / ")[0];
    document.title = cur.label + " — Product Costing";
    document.body.innerHTML = `
      <header class="topbar">
        <div class="topbar__brand">
          <span class="topbar__logo">PT</span>
          <div><div class="topbar__title">Modul Produksi</div><div class="topbar__subtitle">Product Costing</div></div>
        </div>
        <nav class="topbar__steps" aria-label="Alur proses">
          <a class="step" href="beranda.html">Daftar SPK</a>
          <a class="step" href="index.html">Input Produksi</a>
          <a class="step" href="material.html">Material</a>
          <a class="step" href="costbook.html">Costbook</a>
          <a class="step" href="rate.html">Rate</a>
          <a class="step" href="hpp.html">HPP</a>
          <span class="step step--active">Product Costing</span>
        </nav>
        <div class="topbar__user"><span class="badge badge--role">${esc(cur.role)}</span><span class="topbar__user-name">${esc(USERS[role] || "")}</span></div>
      </header>
      <div class="pc-layout">
        <aside class="pc-side" aria-label="Menu Product Costing">
          ${MENU.map((g) => `<h3>${g.group}</h3>` + g.items.map((x) => `<a href="${x.file}" class="${x.id === id ? "is-active" : ""}"><span class="pc-no">${flat.indexOf(x) + 1}</span>${x.label}</a>`).join("")).join("")}
        </aside>
        <main class="page pc-main">
          <div class="page__header">
            <div><h1>${page.title || esc(cur.label)}</h1><p class="muted">${page.hint || ""}</p><p class="muted"><span class="badge badge--note">${cur.uc}</span> Diisi oleh: <strong>${esc(cur.role)}</strong></p></div>
            <div class="page__header-status"><span class="badge badge--done" id="saveStatus">Tersimpan</span></div>
          </div>
          <div id="pcBody"></div>
        </main>
      </div>
      <div class="actionbar">
        <div class="actionbar__note"><span class="muted">Data contoh dari Excel "Rev.1 Ide Costing Kg konversi ke Meter". Perubahan disimpan ke penyimpanan lokal browser (mockup).</span></div>
        <div class="actionbar__btns">
          <button type="button" class="btn btn--ghost" id="btnReset">Reset ke Contoh Excel</button>
          ${page.noSave ? "" : `<button type="button" class="btn btn--primary" id="btnSave">Simpan</button>`}
        </div>
      </div>
      <div class="toast" id="toast" hidden></div>
      <div class="pc-modal" id="pcModal" hidden></div>`;
    render();
    document.addEventListener("input", (e) => { if (e.target.tagName !== "SELECT") onInput(e); });
    document.addEventListener("change", onInput);
    document.addEventListener("click", onClick);
    const sv = $("#btnSave");
    if (sv) sv.addEventListener("click", () => { PC.save(); markDirty(false); toast("Tersimpan di penyimpanan lokal.", "ok"); });
    $("#btnReset").addEventListener("click", () => {
      PC.reset(); markDirty(false); render(); toast("Seluruh data Product Costing dikembalikan ke contoh Excel.", "warn");
    });
    window.addEventListener("beforeunload", () => { /* mockup: tanpa dialog */ });
    document.addEventListener("keydown", (e) => { if (e.key === "Escape" && modal) closeModal(); });
  }

  window.PC_SHELL = { H, MENU, render, refreshCalc, toast, markDirty, getPath, openModal, closeModal };
  document.addEventListener("DOMContentLoaded", () => setTimeout(init, 0));
})();
