/* ============================================================
   costbook.js — Master Costbook: nilai/kg per material × gudang.
   Daftar jenis material BERSUMBER dari Master Material (read-only di sini).
   Halaman ini hanya mengisi nilai/kg. Disimpan ke window.MOCK.costbook, dipakai HPP.
   ============================================================ */
(() => {
  "use strict";

  const M = window.MOCK;
  const $ = (s, r = document) => r.querySelector(s);
  const num = (v) => { const n = parseFloat(v); return isNaN(n) ? null : n; };

  const WAREHOUSES = M.COSTBOOK_WAREHOUSES;   // [{key,label}]

  // Working copy: { material: { gudangKey: nilai|null } }
  let table = M.costbook.all();
  const materials = () => M.costbook.materials();   // dari Master Material

  function markDirty(state) {
    const badge = $("#saveStatus");
    if (state) { badge.textContent = "Perubahan belum disimpan"; badge.className = "badge badge--draft"; }
    else { badge.textContent = "Tersimpan"; badge.className = "badge badge--done"; }
  }

  function renderHead() {
    $("#cbHeadRow").innerHTML =
      `<th>Material</th>` +
      WAREHOUSES.map((w) => `<th class="num">${w.label}<br /><small>Rp/kg</small></th>`).join("");
  }

  function renderBody() {
    const body = $("#costbookTable tbody");
    const mats = materials();
    if (!mats.length) {
      body.innerHTML = `<tr><td colspan="${WAREHOUSES.length + 1}" class="empty">Belum ada material di Master Material. Tambahkan lebih dulu di halaman Material.</td></tr>`;
      return;
    }
    body.innerHTML = mats.map((mat) => {
      const cells = WAREHOUSES.map((w) => {
        const v = table[mat] ? table[mat][w.key] : null;
        return `<td class="num">
          <input type="number" min="0" step="1" class="cb-in"
                 data-material="${encodeURIComponent(mat)}" data-gudang="${w.key}"
                 value="${v != null ? v : ""}" placeholder="—" />
        </td>`;
      }).join("");
      return `<tr><td><strong>${mat}</strong></td>${cells}</tr>`;
    }).join("");
  }

  function onInput(e) {
    const t = e.target;
    if (!t.classList || !t.classList.contains("cb-in")) return;
    const mat = decodeURIComponent(t.dataset.material);
    const g = t.dataset.gudang;
    if (!table[mat]) table[mat] = {};
    table[mat][g] = num(t.value);
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
    // Simpan hanya material yang masih ada di master (bersihkan yatim).
    const mats = new Set(materials());
    const clean = {};
    Object.keys(table).forEach((mat) => { if (mats.has(mat)) clean[mat] = table[mat]; });
    M.costbook.save(clean);
    table = M.costbook.all();
    renderBody();
    markDirty(false);
    toast("Master Costbook tersimpan. Dipakai pada perhitungan HPP.", "ok");
  }

  function reset() {
    M.costbook.reset();
    table = M.costbook.all();
    renderBody();
    markDirty(false);
    toast("Costbook dikembalikan ke nilai default.", "warn");
  }

  function init() {
    renderHead();
    renderBody();
    markDirty(false);
    $("#saveStatus").textContent = "Siap diedit";
    document.addEventListener("input", onInput);
    $("#btnSave").addEventListener("click", save);
    $("#btnReset").addEventListener("click", reset);
  }

  document.addEventListener("DOMContentLoaded", init);
})();
