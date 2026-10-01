/* ============================================================
   beranda.js — Landing: daftar SPK menunggu produksi + Add SPK baru.
   ============================================================ */
(() => {
  "use strict";
  const M = window.MOCK;
  const $ = (s, r = document) => r.querySelector(s);

  const spkList = () => (M.allSpks ? M.allSpks() : M.SPKS);

  function productSummary(spk) {
    return spk.products
      .map((p) => {
        const prod = M.productById(p.productId);
        return `${prod ? prod.name : p.productId} <span class="chip">${p.targetQty} pcs</span>`;
      })
      .join(" ");
  }

  function renderStats() {
    const list = spkList();
    const totalSpk = list.length;
    const doneCount = list.filter((spk) => M.spkStatus.get(spk.no) === "Done").length;
    const menunggu = totalSpk - doneCount;
    const totalQty = list.reduce((s, spk) => s + spk.products.reduce((a, p) => a + p.targetQty, 0), 0);
    $("#statRow").innerHTML = `
      <div class="stat"><span class="stat__v">${menunggu}</span><span class="stat__k">SPK Menunggu</span></div>
      <div class="stat"><span class="stat__v">${doneCount}</span><span class="stat__k">SPK Selesai (Done)</span></div>
      <div class="stat"><span class="stat__v">${totalQty.toLocaleString("id-ID")}</span><span class="stat__k">Target Qty (pcs)</span></div>`;
  }

  function renderTable() {
    const body = $("#spkTable tbody");
    body.innerHTML = spkList().map((spk) => {
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
        <td>${spk.material || "—"}</td>
        <td class="prod-cell">${productSummary(spk)}</td>
        <td>${statusBadge}</td>
        <td>${actionBtn}</td>
      </tr>`;
    }).join("");
  }

  /* ============================================================
     ADD SPK — modal & form
     ============================================================ */
  let seq = 0;
  const uid = () => `sp${++seq}`;
  let draftProducts = [];   // [{ id, productId, qty }]

  const productOptions = (selected) =>
    `<option value="">— Pilih Produk —</option>` +
    M.PRODUCTS.map((p) => `<option value="${p.id}" ${p.id === selected ? "selected" : ""}>${p.name}</option>`).join("");

  function renderDraftProducts() {
    const tbody = $("#spkProductTable tbody");
    const foot = tbody.nextElementSibling;
    foot.style.display = draftProducts.length ? "none" : "";
    tbody.innerHTML = draftProducts.map((r) => `
      <tr data-id="${r.id}">
        <td><select data-sp-product="${r.id}">${productOptions(r.productId)}</select></td>
        <td class="num"><input type="number" min="0" step="1" data-sp-qty="${r.id}" value="${r.qty || ""}" placeholder="0" /></td>
        <td><button type="button" class="btn-icon" data-sp-del="${r.id}" title="Hapus">✕</button></td>
      </tr>`).join("");
  }

  function addDraftProduct() {
    draftProducts.push({ id: uid(), productId: "", qty: 0 });
    renderDraftProducts();
  }

  function openModal() {
    draftProducts = [];
    $("#spkNo").value = "";
    $("#spkTgl").valueAsDate = new Date();
    $("#spkCustomer").value = "";
    $("#spkMaterial").value = "";
    $("#spkError").hidden = true;
    addDraftProduct();   // mulai dengan satu baris
    $("#spkModal").hidden = false;
  }
  function closeModal() { $("#spkModal").hidden = true; }

  function showError(msg) {
    const el = $("#spkError");
    el.textContent = "⚠ " + msg;
    el.className = "note note--warn";
    el.hidden = false;
  }

  function saveSpk() {
    const no = $("#spkNo").value.trim();
    const tgl = $("#spkTgl").value;
    const customer = $("#spkCustomer").value.trim();
    const material = $("#spkMaterial").value.trim();

    if (!no) return showError("No. SPK wajib diisi.");
    if (spkList().some((s) => s.no === no)) return showError(`No. SPK "${no}" sudah ada. Gunakan nomor lain.`);
    if (!tgl) return showError("Tgl SPK wajib diisi.");
    if (!customer) return showError("Customer wajib diisi.");

    const products = draftProducts
      .filter((r) => r.productId && r.qty > 0)
      .map((r) => ({ productId: r.productId, targetQty: Number(r.qty) }));
    if (!products.length) return showError("Pilih minimal satu produk dengan target qty > 0.");

    M.userSpk.add({ no, tgl, customer, material, status: "Menunggu Produksi", products });
    closeModal();
    renderStats();
    renderTable();
    toast(`SPK ${no} dibuat dengan ${products.length} produk.`, "ok");
  }

  /* ============================================================
     TOAST
     ============================================================ */
  let toastTimer = null;
  function toast(msg, kind = "") {
    const t = $("#toast");
    t.textContent = msg;
    t.className = "toast" + (kind ? " toast--" + kind : "");
    t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => (t.hidden = true), 2800);
  }

  /* ============================================================
     EVENTS
     ============================================================ */
  document.addEventListener("click", (e) => {
    const t = e.target;
    // Buka laporan SPK
    const no = t.dataset.open;
    if (no) {
      M.store.clear();
      M.store.spkNo = no;
      M.store.coils = [];
      window.location.href = "index.html";
      return;
    }
    if (t.id === "btnAddSpk") openModal();
    if (t.id === "spkCancel") closeModal();
    if (t.id === "spkSave") saveSpk();
    if (t.id === "spkAddProduct") addDraftProduct();
    if (t.dataset.spDel) { draftProducts = draftProducts.filter((r) => r.id !== t.dataset.spDel); renderDraftProducts(); }
  });

  document.addEventListener("input", (e) => {
    const t = e.target;
    if (!t.dataset) return;
    if (t.dataset.spProduct != null) { const r = draftProducts.find((x) => x.id === t.dataset.spProduct); if (r) r.productId = t.value; }
    if (t.dataset.spQty != null) { const r = draftProducts.find((x) => x.id === t.dataset.spQty); if (r) r.qty = parseInt(t.value, 10) || 0; }
  });

  document.addEventListener("DOMContentLoaded", () => {
    renderStats();
    renderTable();
  });
})();
