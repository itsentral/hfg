/* ============================================================
   pc-shell.js — Kerangka bersama halaman Product Costing: isi halaman (sidebar + top bar dari
   berry-shell.js), binding input (data-path), aksi (data-act), simpan/reset, toast.
   Tiap halaman: <body data-page="..."> dan pc-pages.js mendaftarkan isinya.
   ============================================================ */
(() => {
  "use strict";
  const PC = window.PC;

  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));

  // Urutan menu mengikuti alur BPMN To Be (Engineering → Finance → Costing → Accounting → Sales → Management).
  // Kode UC = "Rev.1 Use Case Product Costing" (UC-01..UC-19).
  const MENU = [
    { group: "Master Data", items: [
      { id: "produk", file: "pc-produk.html", label: "Master Produk", role: "Engineering", uc: "UC-01" },
      { id: "material", file: "pc-material.html", label: "Master Material", role: "Engineering", uc: "UC-02" },
      { id: "standard", file: "pc-standard.html", label: "Master Standard Material", role: "Engineering", uc: "UC-03" },
      { id: "bom", file: "pc-bom.html", label: "BOM Produk", role: "Engineering", uc: "UC-04" },
      { id: "flow", file: "pc-flow.html", label: "Flow Process & Cycletime", role: "Engineering", uc: "UC-05" },
      { id: "kurs", file: "pc-kurs.html", label: "Kurs", role: "Finance", uc: "UC-06" },
      { id: "price", file: "pc-price-reference.html", label: "Price Reference", role: "Finance", uc: "UC-06" },
    ] },
    { group: "Rate", items: [
      { id: "mpr", file: "pc-man-power-rate.html", label: "Man Power Rate (Operator)", role: "Costing", uc: "UC-07" },
      { id: "borongan", file: "pc-borongan.html", label: "Rate Borongan Helper", role: "Costing", uc: "UC-07" },
      { id: "faktor", file: "pc-faktor-indirect.html", label: "Faktor Indirect Labor", role: "Costing", uc: "UC-07" },
      { id: "bpkfoh", file: "pc-bpk-foh.html", label: "Rate Bahan Pendukung & FOH", role: "Costing", uc: "UC-07" },
      { id: "general", file: "pc-general-rate.html", label: "General Rate", role: "Costing", uc: "UC-08, UC-09" },
      { id: "nego", file: "pc-negotiation.html", label: "Negotiation Allowance", role: "Sales Manager", uc: "UC-10" },
    ] },
    { group: "Hasil & Pricelist", items: [
      { id: "bpnotif", file: "pc-notifikasi-bottom-price.html", label: "Notifikasi Bottom Price", role: "Sales", uc: "UC-N18 (usulan)" },
      { id: "simulasi", file: "pc-simulasi.html", label: "Simulasi Costing", role: "Accounting / Sales / Engineering", uc: "UC-11 s.d. UC-16" },
      { id: "pricelist", file: "pc-pricelist.html", label: "Pricelist", role: "Sales", uc: "UC-16, UC-17" },
      { id: "approval", file: "pc-approval.html", label: "Approval Pricelist", role: "Management", uc: "UC-19" },
      { id: "egapproval", file: "pc-approval-extra-margin.html", label: "Approval Extra Margin", role: "GM", uc: "UC-10" },
      { id: "catalog", file: "pc-catalog.html", label: "Product Catalog", role: "Sales", uc: "UC-18" },
      { id: "hcosting", file: "pc-history-costing.html", label: "History Costing", role: "Costing / Accounting / Sales / Management (lihat saja)", uc: "UC-N19 (usulan)" },
      { id: "hpricelist", file: "pc-history-pricelist.html", label: "History Price List", role: "Costing / Accounting / Sales / Management (lihat saja)", uc: "UC-N20 (usulan)" },
    ] },
  ];
  const flat = MENU.reduce((a, g) => a.concat(g.items), []);
  const USERS = { Engineering: "Eka R.", Accounting: "Andri P.", Finance: "Andri P.", Costing: "Citra D.", Sales: "Sari W.", "Sales Manager": "Sari W.", GM: "Gilang M.", Management: "Bima H." };

  const getPath = (o, p) => p.split(".").reduce((x, k) => (x == null ? x : x[k]), o);
  const setPath = (o, p, v) => {
    const ks = p.split("."); const last = ks.pop();
    const t = ks.reduce((x, k) => x[k], o); t[last] = v;
  };

  // Ikon garis kecil (pengganti Font Awesome / Tabler yang dipakai aplikasi); tanpa dependensi.
  const ICO = {
    plus: '<path d="M12 5v14M5 12h14"/>',
    edit: '<path d="M4 20h4L19 9l-4-4L4 16v4z"/><path d="M13 7l4 4"/>',
    trash: '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
    eye: '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    excel: '<path d="M6 3h9l4 4v14H6z"/><path d="M9 10l5 7M14 10l-5 7"/>',
    save: '<path d="M5 4h12l3 3v13H5z"/><path d="M8 4v6h8V4M8 20v-6h8v6"/>',
    x: '<path d="M6 6l12 12M18 6L6 18"/>',
    back: '<path d="M19 12H5M11 6l-6 6 6 6"/>',
    sitemap: '<rect x="9" y="3" width="6" height="5"/><rect x="3" y="16" width="6" height="5"/><rect x="15" y="16" width="6" height="5"/><path d="M12 8v4M6 16v-4h12v4"/>',
    list: '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
  };
  const ico = (n) => `<svg class="ap-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICO[n] || ""}</svg>`;

  /* ---- helper pembangun HTML yang dipakai pc-pages.js ---- */
  const H = {
    esc,
    ico,
    in(path, val, o = {}) {
      return `<input class="pc-in ${o.num ? "pc-in--num" : ""} ${o.cls || ""}" type="${o.date ? "date" : o.num ? "number" : "text"}"${o.step ? ` step="${o.step}"` : ""}${o.min != null ? ` min="${o.min}"` : ""} data-path="${path}" data-t="${o.t || (o.num ? "num" : "text")}"${o.struct ? " data-struct" : ""} value="${esc(val)}">`;
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

  /* ---- Dialog gaya SweetAlert (konfirmasi / peringatan / sukses) ---- */
  let dlgDone = null;
  function dialog(cfg) {
    return new Promise((resolve) => {
      const host = $("#pcDialog");
      if (!host) { resolve(true); return; }
      const sym = { warning: "!", success: "&#10003;", error: "&#10005;" }[cfg.icon || "warning"];
      const done = (v) => { host.hidden = true; host.innerHTML = ""; dlgDone = null; resolve(v); };
      dlgDone = done;
      host.hidden = false;
      host.innerHTML = `<div class="ap-dlg__overlay"></div>
        <div class="ap-dlg" role="alertdialog" aria-modal="true">
          <div class="ap-dlg__icon ap-dlg__icon--${cfg.icon || "warning"}">${sym}</div>
          <h3>${esc(cfg.title || "")}</h3>
          ${cfg.text ? `<p>${esc(cfg.text)}</p>` : ""}
          <div class="ap-dlg__btns">
            ${cfg.cancelText ? `<button type="button" class="ap-btn ap-btn--secondary" data-dlg="0">${esc(cfg.cancelText)}</button>` : ""}
            ${cfg.timer ? "" : `<button type="button" class="ap-btn ${cfg.danger ? "ap-btn--danger" : "ap-btn--primary"}" data-dlg="1">${esc(cfg.confirmText || "OK")}</button>`}
          </div>
        </div>`;
      host.querySelectorAll("[data-dlg]").forEach((b) => b.addEventListener("click", () => done(b.dataset.dlg === "1")));
      if (cfg.timer) setTimeout(() => { if (dlgDone === done) done(true); }, cfg.timer);
    });
  }

  // Simpan modal gaya aplikasi: validasi → "Are you sure?" → simpan → "Success".
  function appSave() {
    const m = modal;
    const err = m.validate ? m.validate() : null;
    if (err) { dialog({ icon: "warning", title: "Error Message!", text: err }); return; }
    dialog({ icon: "warning", title: m.confirmTitle || "Are you sure?", text: m.confirmText || "Process this data", confirmText: m.confirmBtn || "Ya, Simpan!", cancelText: "Batal" }).then((ok) => {
      if (!ok || modal !== m) return;
      if (m.onSave && m.onSave() === false) return;
      if (page.noSave) PC.save(); else markDirty(true);
      modal = null; render();
      dialog({ icon: "success", title: "Success", text: m.doneMsg || "Data berhasil disimpan." });
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
    if (modal.app) {
      host.hidden = false;
      host.innerHTML = `<div class="modal__overlay" data-act="mclose"></div>
        <div class="ap-modal ap-modal--${modal.size || "lg"} ${modal.cls || ""}" role="dialog" aria-modal="true">
          <div class="ap-modal__head"><h4>${esc(modal.title || "")}</h4><button type="button" class="ap-modal__x" data-act="mclose" aria-label="Close">&#10005;</button></div>
          <div class="ap-modal__body">${modal.bodyFn ? modal.bodyFn() : ""}</div>
          <div class="ap-modal__foot">
            ${modal.hideSave ? "" : `<button type="button" class="ap-btn ap-btn--primary" data-act="msave">${ico("save")} ${esc(modal.saveLabel || "Save")}</button>`}
            <button type="button" class="ap-btn ap-btn--dark" data-act="mclose">${ico("x")} ${esc(modal.cancelLabel || "Cancel")}</button>
          </div>
        </div>`;
      refreshCalc();
      if (modal.after) modal.after();
      return;
    }
    const ro = modal.mode === "view";
    const body = `<div class="modal__overlay" data-act="mclose"></div>
      <div class="modal__box ${modal.size ? "modal__box--" + modal.size : ""}" role="dialog" aria-modal="true">
        <div class="modal__head"><h2>${esc(modal.title || "")}</h2><button type="button" class="modal__x" data-act="mclose" aria-label="Tutup">&#10005;</button></div>
        <div class="modal__body ${ro ? "is-view" : ""}">${modal.bodyFn ? modal.bodyFn() : ""}</div>
        <div class="modal__foot">
          <button type="button" class="btn btn--ghost" data-act="mclose">${ro ? "Tutup" : "Batal"}</button>
          ${ro ? "" : `<button type="button" class="btn btn--primary" data-act="msave">${esc(modal.saveLabel || "Simpan")}</button>`}
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
    else if (t.dataset.t === "pctnull") v = v === "" ? null : parseFloat(v) / 100;
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
    if (act === "msave" && modal && modal.app) { appSave(); return; }
    if (act === "msave") {
      // onSave boleh mengembalikan false (validasi gagal) → modal tetap terbuka.
      if (modal && modal.onSave && modal.onSave() === false) return;
      const doneMsg = (modal && modal.doneMsg) || "Tersimpan.";
      if (!page.noSave) markDirty(true); else PC.save();
      modal = null; render(); toast(doneMsg, "ok"); return;
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
    PC.setActor(role + " · " + (USERS[role] || ""));   // dipakai pencatat History Costing / History Price List
    document.title = cur.label + " — Product Costing";
    document.body.innerHTML = `
      <main class="page pc-main">
          <div class="page__header">
            <div><h1>${page.title || esc(cur.label)}</h1><p class="muted">${page.hint || ""}</p><p class="muted"><span class="badge badge--note">${cur.uc}</span> Diisi oleh: <strong>${esc(cur.role)}</strong></p></div>
            <div class="page__header-status"><span class="badge badge--done" id="saveStatus">Tersimpan</span></div>
          </div>
          <div id="pcBody"></div>
      </main>
      <div class="actionbar">
        <div class="actionbar__note"><span class="muted">Angka contoh mockup, bukan angka acuan klien. Pembulatan: hanya Rate 1/3 Operator dibulatkan ke atas ke Rupiah penuh; komponen lain 2 desimal tanpa pembulatan. Perubahan disimpan ke penyimpanan lokal browser (mockup).</span></div>
        <div class="actionbar__btns">
          <button type="button" class="btn btn--ghost" id="btnExport" title="Unduh seluruh data Product Costing sebagai file cadangan">Simpan ke File</button>
          <button type="button" class="btn btn--ghost" id="btnImport" title="Muat data dari file cadangan">Muat dari File</button>
          <input type="file" id="fileImport" accept=".json,application/json" hidden>
          <button type="button" class="btn btn--ghost" id="btnReset">Reset ke Data Contoh</button>
          ${page.noSave ? "" : `<button type="button" class="btn btn--primary" id="btnSave">Simpan</button>`}
        </div>
      </div>
      <div class="toast" id="toast" hidden></div>
      <div class="pc-modal" id="pcModal" hidden></div>
      <div class="pc-modal pc-modal--dlg" id="pcDialog" hidden></div>`;
    // Kerangka Berry (sidebar + top bar + kartu judul/breadcrumb) — lihat berry-shell.js
    const gaps = PC.gapReport().length;
    window.BERRY.mount({ user: { name: USERS[role] || "", role: cur.role }, badges: Object.assign(gaps ? { "pc-simulasi.html": gaps, "pc-pricelist.html": gaps } : {}, PC.negoPending().length ? { "pc-approval-extra-margin.html": { n: PC.negoPending().length, title: PC.negoPending().length + " pengajuan Extra Margin menunggu keputusan GM", tone: "info" } } : {}) });
    render();
    document.addEventListener("input", (e) => { if (e.target.tagName !== "SELECT") onInput(e); });
    document.addEventListener("change", onInput);
    document.addEventListener("click", onClick);
    const sv = $("#btnSave");
    if (sv) sv.addEventListener("click", () => { PC.save(); markDirty(false); toast("Tersimpan di penyimpanan lokal.", "ok"); });
    // Simpan/muat data ke file; peringatan bila penyimpanan browser tidak bisa dipakai.
    $("#btnExport").addEventListener("click", () => {
      PC.save();
      const blob = new Blob([PC.exportData()], { type: "application/json" });
      const a = document.createElement("a");
      a.href = URL.createObjectURL(blob);
      a.download = "pc-data-tersimpan-" + PC.today() + ".json";
      document.body.appendChild(a); a.click(); a.remove();
      setTimeout(() => URL.revokeObjectURL(a.href), 1000);
      toast("Data diunduh sebagai file cadangan.", "ok");
    });
    $("#btnImport").addEventListener("click", () => $("#fileImport").click());
    $("#fileImport").addEventListener("change", (e) => {
      const f = e.target.files && e.target.files[0]; if (!f) return;
      const rd = new FileReader();
      rd.onload = () => {
        const r = PC.importData(String(rd.result));
        if (r.ok) { markDirty(false); render(); }
        toast(r.msg, r.ok ? "ok" : "warn");
        e.target.value = "";
      };
      rd.readAsText(f);
    });
    if (!PC.storageOk()) {
      const b = $("#saveStatus");
      if (b) { b.textContent = "Penyimpanan browser tidak tersedia"; b.className = "badge badge--draft"; }
      toast("Penyimpanan browser tidak tersedia (jendela privat atau diblokir): data akan hilang saat halaman di-refresh. Gunakan \"Simpan ke File\".", "warn");
    }
    $("#btnReset").addEventListener("click", () => {
      PC.reset(); markDirty(false); render(); toast("Seluruh data Product Costing dikembalikan ke data contoh.", "warn");
    });
    window.addEventListener("beforeunload", () => { /* mockup: tanpa dialog */ });
    document.addEventListener("keydown", (e) => {
      if (e.key !== "Escape") return;
      if (dlgDone) { dlgDone(false); return; }
      if (modal) closeModal();
    });
  }

  window.PC_SHELL = { H, MENU, render, refreshCalc, toast, markDirty, getPath, openModal, closeModal, dialog };
  document.addEventListener("DOMContentLoaded", () => setTimeout(init, 0));
})();
