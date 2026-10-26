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
        const bom = p.bomUniq ? ` <span class="chip" title="no_uniq_id">${p.bomUniq}</span>` : "";
        return `${prod ? prod.name : p.productId}${bom} <span class="chip">${p.targetQty} pcs</span>`;
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
      const locked = M.spkLocked(spk.no);
      const varBtn = locked
        ? `<span class="muted" style="font-size:12px">Terkunci (laporan sudah dibuat)</span>`
        : `<button type="button" class="btn btn--ghost btn--tiny" data-edit-bom="${spk.no}">Ubah Varian BOM</button>`;
      return `
      <tr class="${done ? "row--done" : ""}">
        <td><strong>${spk.no}</strong></td>
        <td>${spk.tgl}</td>
        <td>${spk.customer}</td>
        <td>${spk.material || "—"}</td>
        <td class="prod-cell">${productSummary(spk)}</td>
        <td>${statusBadge}</td>
        <td>${actionBtn}<div style="margin-top:4px">${varBtn}</div></td>
      </tr>`;
    }).join("");
  }

  /* ============================================================
     ADD SPK — modal & form
     ============================================================ */
  let seq = 0;
  const uid = () => `sp${++seq}`;
  let draftProducts = [];   // [{ id, productId, bomUniq, qty }]

  const productOptions = (selected) =>
    `<option value="">— Pilih Produk —</option>` +
    M.PRODUCTS.map((p) => `<option value="${p.id}" ${p.id === selected ? "selected" : ""}>${p.name}${M.productReady(p.id) ? "" : " — belum ada Hasil Costing"}</option>`).join("");

  const bomHtml = (id, productId, selected, attr) => {
    if (!productId) return `<select data-${attr}="${id}" disabled><option value="">— Pilih produk dulu —</option></select>`;
    const opts = M.bomOptions(productId);
    if (!M.productReady(productId)) return `<select data-${attr}="${id}" disabled><option value="">— Belum ada Hasil Costing —</option></select>
        <div class="reject-note" style="font-size:12px;color:#b91c1c">✕ Ditolak: produk belum punya varian BOM dengan Hasil Costing lengkap di Product Costing. SPK tidak dapat dibuat.</div>`;
    return `<select data-${attr}="${id}"><option value="">— Pilih Varian BOM (no_uniq_id) —</option>` +
      opts.map((o) => `<option value="${o.uniq}" ${o.uniq === selected ? "selected" : ""} ${M.bomReady(o.uniq) ? "" : "disabled"}>${o.uniq}${o.materialName ? " — " + o.materialName : ""}${M.bomReady(o.uniq) ? "" : " (costing belum lengkap)"}</option>`).join("") + "</select>";
  };
  const bomSelect = (r) => bomHtml(r.id, r.productId, r.bomUniq, "sp-bom");

  function renderDraftProducts() {
    const tbody = $("#spkProductTable tbody");
    const foot = tbody.nextElementSibling;
    foot.style.display = draftProducts.length ? "none" : "";
    tbody.innerHTML = draftProducts.map((r) => `
      <tr data-id="${r.id}">
        <td><select data-sp-product="${r.id}">${productOptions(r.productId)}</select></td>
        <td>${bomSelect(r)}</td>
        <td class="num"><input type="number" min="0" step="1" data-sp-qty="${r.id}" value="${r.qty || ""}" placeholder="0" /></td>
        <td><button type="button" class="btn-icon" data-sp-del="${r.id}" title="Hapus">✕</button></td>
      </tr>`).join("");
    refreshSave();
  }

  // Tombol Simpan diblokir bila ada baris produk tanpa Hasil Costing.
  function refreshSave() {
    const blocked = draftProducts.some((r) => r.productId && !M.productReady(r.productId));
    const b = $("#spkSave");
    if (b) { b.disabled = blocked; b.title = blocked ? "Ada produk tanpa Hasil Costing: SPK ditolak" : ""; }
  }

  function addDraftProduct() {
    draftProducts.push({ id: uid(), productId: "", bomUniq: "", qty: 0 });
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

  /* ---------- Ubah Varian BOM (no_uniq_id) SPK yang belum punya laporan produksi ---------- */
  let editNo = null;
  function openEdit(no) {
    const spk = spkList().find((s) => s.no === no);
    if (!spk || M.spkLocked(no)) return;
    editNo = no;
    $("#spkEditNo").textContent = no;
    $("#spkEditError").hidden = true;
    $("#spkEditBody").innerHTML = spk.products.map((p, i) => {
      const pr = M.productById(p.productId);
      return `<tr><td>${pr ? pr.name : p.productId}</td><td>${bomHtml(i, p.productId, p.bomUniq, "ed-bom")}</td></tr>`;
    }).join("");
    $("#spkEditModal").hidden = false;
  }
  function saveEdit() {
    const spk = spkList().find((s) => s.no === editNo);
    const sels = [...document.querySelectorAll("#spkEditBody [data-ed-bom]")];
    const uniqs = sels.map((el) => el.value);
    const err = $("#spkEditError");
    if (spk.products.some((p, i) => M.bomOptions(p.productId).length && !uniqs[i])) { err.textContent = "⚠ Pilih Varian BOM (no_uniq_id) untuk setiap produk."; err.className = "note note--warn"; err.hidden = false; return; }
    if (!M.setSpkBoms(editNo, uniqs.map((u, i) => u || spk.products[i].bomUniq || ""))) { err.textContent = "⚠ SPK terkunci: laporan produksi sudah dibuat."; err.className = "note note--warn"; err.hidden = false; return; }
    $("#spkEditModal").hidden = true;
    renderTable();
    toast("Varian BOM SPK " + editNo + " diperbarui.", "ok");
  }

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
      .map((r) => ({ productId: r.productId, bomUniq: r.bomUniq || "", targetQty: Number(r.qty) }));
    if (!products.length) return showError("Pilih minimal satu produk dengan target qty > 0.");
    const bad = products.find((p) => !M.productReady(p.productId));
    if (bad) { const pr = M.productById(bad.productId); return showError("SPK ditolak: produk " + (pr ? pr.name : bad.productId) + " belum punya varian BOM dengan Hasil Costing lengkap (bila dibiarkan, biaya masuk Laporan Keuangan dan perlu adjustment)."); }
    const noBom = products.find((p) => !p.bomUniq || !M.bomReady(p.bomUniq));
    if (noBom) { const pr = M.productById(noBom.productId); return showError("Pilih Varian BOM (no_uniq_id) untuk produk " + (pr ? pr.name : noBom.productId) + "."); }

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
    if (t.dataset.editBom) openEdit(t.dataset.editBom);
    if (t.id === "spkEditCancel") $("#spkEditModal").hidden = true;
    if (t.id === "spkEditSave") saveEdit();
    if (t.id === "spkAddProduct") addDraftProduct();
    if (t.dataset.spDel) { draftProducts = draftProducts.filter((r) => r.id !== t.dataset.spDel); renderDraftProducts(); }
  });

  document.addEventListener("input", (e) => {
    const t = e.target;
    if (!t.dataset) return;
    if (t.dataset.spProduct != null) { const r = draftProducts.find((x) => x.id === t.dataset.spProduct); if (r) { r.productId = t.value; const o = M.bomOptions(t.value); r.bomUniq = o.length === 1 ? o[0].uniq : ""; renderDraftProducts(); } }
    if (t.dataset.spBom != null) { const r = draftProducts.find((x) => x.id === t.dataset.spBom); if (r) r.bomUniq = t.value; }
    if (t.dataset.spQty != null) { const r = draftProducts.find((x) => x.id === t.dataset.spQty); if (r) r.qty = parseInt(t.value, 10) || 0; }
  });

  document.addEventListener("DOMContentLoaded", () => {
    renderStats();
    renderTable();
  });
})();
