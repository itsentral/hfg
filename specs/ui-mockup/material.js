/* ============================================================
   material.js — Master Material (baby coil per gudang).
   Satu tabel per gudang (Unpack/WIP/Hold). Tiap baris = baby coil:
   Kode, Material, Nett, Gross, Total Meter, Kulit, Clamp/Ring.
   Sumber & tujuan simpan: window.MOCK.material (localStorage).
   Dipakai Laporan Produksi (Add Coil) & Costbook (daftar jenis material).
   ============================================================ */
(() => {
  "use strict";

  const M = window.MOCK;
  const $ = (s, r = document) => r.querySelector(s);
  const num = (v) => { const n = parseFloat(v); return isNaN(n) ? null : n; };

  const SOURCES = M.MATERIAL_SOURCES;   // [{key,label}]

  let seq = 0;
  const uid = () => `c${++seq}`;
  // State: { unpack: [row...], wip: [...], hold: [...] }, row = {id, code, material, nett, gross, meter, kulit, clamp}
  let data = {};

  function loadData() {
    const table = M.material.all();
    data = {};
    SOURCES.forEach((s) => {
      data[s.key] = (table[s.key] || []).map((c) => ({
        id: uid(),
        code: c.code || "",
        material: c.material || "",
        nett: c.nett != null ? c.nett : null,
        gross: c.gross != null ? c.gross : null,
        meter: c.meter != null ? c.meter : null,
        kulit: c.kulit != null ? c.kulit : null,
        clamp: c.clamp != null ? c.clamp : null,
      }));
    });
  }

  function markDirty(state) {
    const badge = $("#saveStatus");
    if (state) { badge.textContent = "Perubahan belum disimpan"; badge.className = "badge badge--draft"; }
    else { badge.textContent = "Tersimpan"; badge.className = "badge badge--done"; }
  }

  function escAttr(s) {
    return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;");
  }

  function rowHtml(src, r) {
    const n = (v) => (v != null ? v : "");
    return `<tr data-id="${r.id}" data-src="${src}">
      <td><input type="text" class="mt-in" data-f="code" data-id="${r.id}" data-src="${src}" value="${escAttr(r.code)}" placeholder="Kode coil" /></td>
      <td><input type="text" class="mt-in" data-f="material" data-id="${r.id}" data-src="${src}" value="${escAttr(r.material)}" placeholder="Nama material" /></td>
      <td class="num"><input type="number" min="0" step="0.01" class="mt-in" data-f="nett" data-id="${r.id}" data-src="${src}" value="${n(r.nett)}" placeholder="—" /></td>
      <td class="num"><input type="number" min="0" step="0.01" class="mt-in" data-f="gross" data-id="${r.id}" data-src="${src}" value="${n(r.gross)}" placeholder="—" /></td>
      <td class="num"><input type="number" min="0" step="1" class="mt-in" data-f="meter" data-id="${r.id}" data-src="${src}" value="${n(r.meter)}" placeholder="—" /></td>
      <td class="num"><input type="number" min="0" step="0.01" class="mt-in" data-f="kulit" data-id="${r.id}" data-src="${src}" value="${n(r.kulit)}" placeholder="—" /></td>
      <td class="num"><input type="number" min="0" step="0.01" class="mt-in" data-f="clamp" data-id="${r.id}" data-src="${src}" value="${n(r.clamp)}" placeholder="—" /></td>
      <td><button type="button" class="btn-icon" data-del="${r.id}" data-src="${src}" title="Hapus">✕</button></td>
    </tr>`;
  }

  function sectionHtml(s) {
    return `
    <section class="card">
      <div class="card__head">
        <h2>${s.label}</h2>
        <p class="card__hint">Baby coil pada gudang ini. Nett &amp; Gross Weight dapat diedit untuk simulasi.</p>
      </div>
      <div class="card__body">
        <div class="table-wrap">
          <table class="tbl" id="mt-${s.key}">
            <thead>
              <tr>
                <th>Kode Coil</th>
                <th>Material</th>
                <th class="num">Nett (kg)</th>
                <th class="num">Gross (kg)</th>
                <th class="num">Total Meter</th>
                <th class="num">Kulit (kg)</th>
                <th class="num">Clamp/Ring (kg)</th>
                <th style="width:44px"></th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot><tr><td colspan="8" class="empty">Belum ada baby coil di gudang ini.</td></tr></tfoot>
          </table>
        </div>
        <div class="btn-row">
          <button type="button" class="btn btn--soft" data-add="${s.key}">+ Tambah Baby Coil</button>
        </div>
      </div>
    </section>`;
  }

  function renderSections() {
    $("#warehouseSections").innerHTML = SOURCES.map(sectionHtml).join("");
    SOURCES.forEach((s) => renderBody(s.key));
  }

  function renderBody(src) {
    const tbody = $(`#mt-${src} tbody`);
    const foot = tbody.nextElementSibling;
    const rows = data[src] || [];
    foot.style.display = rows.length ? "none" : "";
    tbody.innerHTML = rows.map((r) => rowHtml(src, r)).join("");
  }

  /* ---------- Events ---------- */
  function onInput(e) {
    const t = e.target;
    if (!t.classList || !t.classList.contains("mt-in")) return;
    const src = t.dataset.src;
    const r = (data[src] || []).find((x) => x.id === t.dataset.id);
    if (!r) return;
    const f = t.dataset.f;
    if (f === "code" || f === "material") r[f] = t.value;
    else r[f] = num(t.value);
    markDirty(true);
  }

  function onClick(e) {
    const t = e.target;
    if (!t.dataset) return;
    if (t.dataset.add) { addRow(t.dataset.add); }
    else if (t.dataset.del) {
      const src = t.dataset.src;
      data[src] = (data[src] || []).filter((r) => r.id !== t.dataset.del);
      renderBody(src);
      markDirty(true);
    }
  }

  function addRow(src) {
    if (!data[src]) data[src] = [];
    data[src].push({ id: uid(), code: "", material: "", nett: null, gross: null, meter: null, kulit: null, clamp: null });
    renderBody(src);
    markDirty(true);
    const last = $(`#mt-${src} tbody tr:last-child .mt-in`);
    if (last) last.focus();
  }

  /* ---------- Simpan / Reset ---------- */
  function toTable() {
    const table = {};
    let skipped = 0;
    SOURCES.forEach((s) => {
      table[s.key] = (data[s.key] || [])
        .filter((r) => {
          const keep = (r.code || "").trim() || (r.material || "").trim();
          if (!keep) skipped++;
          return keep;
        })
        .map((r) => ({
          code: (r.code || "").trim(),
          material: (r.material || "").trim(),
          nett: r.nett, gross: r.gross, meter: r.meter, kulit: r.kulit, clamp: r.clamp,
        }));
    });
    return { table, skipped };
  }

  let toastTimer = null;
  function toast(msg, kind = "") {
    const t = $("#toast");
    t.textContent = msg;
    t.className = "toast" + (kind ? " toast--" + kind : "");
    t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => (t.hidden = true), 3200);
  }

  function save() {
    const { table, skipped } = toTable();
    M.material.save(table);
    loadData();
    renderSections();
    markDirty(false);
    let msg = "Master Material tersimpan. Dipakai di Laporan Produksi & Costbook.";
    if (skipped) msg += ` ${skipped} baris tanpa kode/material diabaikan.`;
    toast(msg, skipped ? "warn" : "ok");
  }

  function reset() {
    M.material.reset();
    loadData();
    renderSections();
    markDirty(false);
    toast("Master Material dikembalikan ke data default.", "warn");
  }

  function init() {
    loadData();
    renderSections();
    markDirty(false);
    $("#saveStatus").textContent = "Siap diedit";
    document.addEventListener("input", onInput);
    document.addEventListener("click", onClick);
    $("#btnSave").addEventListener("click", save);
    $("#btnReset").addEventListener("click", reset);
  }

  document.addEventListener("DOMContentLoaded", init);
})();
