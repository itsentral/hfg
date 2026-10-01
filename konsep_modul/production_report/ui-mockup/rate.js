/* ============================================================
   rate.js — Master Rate Product Costing.
   Menampilkan & mengedit rate komponen biaya HPP (Rp/kg) + Scrap (%).
   Sumber & tujuan simpan: window.MOCK.rateStore (localStorage). Dibaca HPP.
   Catatan tampilan: Scrap disimpan sebagai fraksi (0.02) tetapi ditampilkan
   sebagai persen (2) agar mudah dibaca.
   ============================================================ */
(() => {
  "use strict";

  const M = window.MOCK;
  const $ = (s, r = document) => r.querySelector(s);
  const num = (v) => { const n = parseFloat(v); return isNaN(n) ? 0 : n; };

  const FIELDS = M.RATE_FIELDS;      // [{key,label,unit,type}]
  let values = M.rateStore.all();    // { key: nilai }

  function markDirty(state) {
    const badge = $("#saveStatus");
    if (state) { badge.textContent = "Perubahan belum disimpan"; badge.className = "badge badge--draft"; }
    else { badge.textContent = "Tersimpan"; badge.className = "badge badge--done"; }
  }

  // Nilai yang ditampilkan di input: pct disimpan sebagai fraksi -> tampil ×100
  const toDisplay = (f, v) => (f.type === "pct" ? Number(v) * 100 : Number(v));
  // Kebalikannya saat menyimpan
  const fromDisplay = (f, v) => (f.type === "pct" ? num(v) / 100 : num(v));

  function renderBody() {
    const body = $("#rateTable tbody");
    body.innerHTML = FIELDS.map((f) => {
      const disp = toDisplay(f, values[f.key]);
      const step = f.type === "pct" ? "0.1" : "1";
      return `<tr>
        <td><strong>${f.label}</strong></td>
        <td class="num"><input type="number" min="0" step="${step}" class="rate-in" data-key="${f.key}" value="${disp}" /></td>
        <td>${f.unit}</td>
      </tr>`;
    }).join("");
  }

  function onInput(e) {
    const t = e.target;
    if (!t.classList || !t.classList.contains("rate-in")) return;
    const f = FIELDS.find((x) => x.key === t.dataset.key);
    if (!f) return;
    values[f.key] = fromDisplay(f, t.value);
    markDirty(true);
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

  function save() {
    M.rateStore.save(values);
    values = M.rateStore.all();
    renderBody();
    markDirty(false);
    toast("Master Rate tersimpan. Dipakai pada perhitungan HPP.", "ok");
  }

  function reset() {
    M.rateStore.reset();
    values = M.rateStore.all();
    renderBody();
    markDirty(false);
    toast("Rate dikembalikan ke nilai default.", "warn");
  }

  function init() {
    renderBody();
    markDirty(false);
    $("#saveStatus").textContent = "Siap diedit";
    document.addEventListener("input", onInput);
    $("#btnSave").addEventListener("click", save);
    $("#btnReset").addEventListener("click", reset);
  }

  document.addEventListener("DOMContentLoaded", init);
})();
