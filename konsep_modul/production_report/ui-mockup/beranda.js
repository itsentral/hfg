/* ============================================================
   beranda.js — Landing: daftar SPK menunggu produksi.
   ============================================================ */
(() => {
  "use strict";
  const M = window.MOCK;
  const $ = (s, r = document) => r.querySelector(s);

  function productSummary(spk) {
    return spk.products
      .map((p) => {
        const prod = M.productById(p.productId);
        return `${prod ? prod.name : p.productId} <span class="chip">${p.targetQty} pcs</span>`;
      })
      .join(" ");
  }

  function renderStats() {
    const totalSpk = M.SPKS.length;
    const doneCount = M.SPKS.filter((spk) => M.spkStatus.get(spk.no) === "Done").length;
    const menunggu = totalSpk - doneCount;
    const totalQty = M.SPKS.reduce((s, spk) => s + spk.products.reduce((a, p) => a + p.targetQty, 0), 0);
    $("#statRow").innerHTML = `
      <div class="stat"><span class="stat__v">${menunggu}</span><span class="stat__k">SPK Menunggu</span></div>
      <div class="stat"><span class="stat__v">${doneCount}</span><span class="stat__k">SPK Selesai (Done)</span></div>
      <div class="stat"><span class="stat__v">${totalQty.toLocaleString("id-ID")}</span><span class="stat__k">Target Qty (pcs)</span></div>`;
  }

  function renderTable() {
    const body = $("#spkTable tbody");
    body.innerHTML = M.SPKS.map((spk) => {
      const done = M.spkStatus.get(spk.no) === "Done";
      const statusBadge = done
        ? `<span class="badge badge--done">Done</span>`
        : `<span class="badge badge--draft">${spk.status}</span>`;
      const actionBtn = done
        ? `<button type="button" class="btn btn--ghost btn--tiny" data-open="${spk.no}">Lihat Laporan</button>`
        : `<button type="button" class="btn btn--primary btn--tiny" data-open="${spk.no}">Buat Laporan →</button>`;
      return `
      <tr class="${done ? "row--done" : ""}">
        <td><strong>${spk.no}</strong></td>
        <td>${spk.tgl}</td>
        <td>${spk.customer}</td>
        <td>${spk.material}</td>
        <td class="prod-cell">${productSummary(spk)}</td>
        <td>${statusBadge}</td>
        <td>${actionBtn}</td>
      </tr>`;
    }).join("");
  }

  document.addEventListener("click", (e) => {
    const no = e.target.dataset.open;
    if (!no) return;
    // set SPK terpilih & reset coil sesi sebelumnya
    M.store.clear();
    M.store.spkNo = no;
    M.store.coils = [];
    window.location.href = "index.html";
  });

  document.addEventListener("DOMContentLoaded", () => {
    renderStats();
    renderTable();
  });
})();
