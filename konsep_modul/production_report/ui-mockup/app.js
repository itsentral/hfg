/* ============================================================
   Laporan Produksi — Input Hasil Produksi (vanilla JS)
   Data tarikan (SPK, produk KW1, coil) dari window.MOCK + store.
   Produk KW1 otomatis dari SPK; Stok Bebas manual dari master produk.
   ============================================================ */
(() => {
  "use strict";

  const M = window.MOCK;
  const TOLERANCE = M.TOLERANCE;             // ± 0,7%
  const PRODUCTS = M.PRODUCTS;               // master product [{id,name,std,size}]
  const SRC_LABEL = M.SRC_LABEL;
  const CURRENT_USER = M.CURRENT_USER;

  // SPK terpilih dari store; kalau tidak ada -> kembali ke beranda.
  const spk = M.spkByNo(M.store.spkNo);
  if (!spk) { window.location.replace("beranda.html"); return; }

  /* ---------- State ---------- */
  const state = {
    coils: [],            // read-only, dari store: {id, source, code, material, nett, ...}
    fg: [],               // {id, kind:'kw1'|'bebas', productId, productName, method, qty, total, perPcs, std, selisih}
    kw2: { internal: [], supplier: [] },
    sisa: [],
    hold: [],
    confirmations: [],
  };
  let seq = 0;
  const uid = () => `r${++seq}`;

  /* ---------- Helpers ---------- */
  const $ = (s, r = document) => r.querySelector(s);
  const num = (v) => { const n = parseFloat(v); return isNaN(n) ? 0 : n; };
  const fmtKg = M.fmtKg;
  const fmtNum = M.fmtNum;
  const fmtPct = (n) => fmtNum(n) + " %";
  const productById = (id) => PRODUCTS.find((p) => p.id === id) || null;

  const coilOptions = () =>
    `<option value="">— Baby Coil —</option>` +
    state.coils.filter((c) => c.code).map((c) => `<option value="${c.id}">${c.code} · ${c.material}</option>`).join("");

  // Master product options (untuk Stok Bebas & KW2)
  const productOptions = () =>
    `<option value="">— Select Master Product —</option>` +
    PRODUCTS.map((p) => `<option value="${p.id}">${p.name}</option>`).join("");

  /* ============================================================
     ADD COIL (interaktif — pilih baby coil dulu, FR-08)
     ============================================================ */
  function addCoil(source) {
    state.coils.push({ id: uid(), source, code: "", material: "", nett: null, gross: null, meter: null, kulit: null, clamp: null });
    renderCoils();
    recalc();
    const last = $("#coilTable tbody tr:last-child select");
    if (last) last.focus();
    toast(`Pilih baby coil dari ${SRC_LABEL[source]}.`);
  }

  // Opsi baby coil per sumber; sembunyikan yang sudah dipakai baris lain (dedup)
  function babyCoilOptions(source, selectedCode) {
    const usedElsewhere = new Set(state.coils.filter((c) => c.code && c.code !== selectedCode).map((c) => c.code));
    const opts = M.COILS[source]
      .filter((c) => !usedElsewhere.has(c.code))
      .map((c) => `<option value="${c.code}" ${c.code === selectedCode ? "selected" : ""}>${c.code} · ${c.material}</option>`)
      .join("");
    return `<option value="">— Pilih Baby Coil —</option>${opts}`;
  }

  function renderCoils() {
    const body = $("#coilTable tbody");
    const empty = $("#coilEmpty");
    body.innerHTML = state.coils.map((c) => {
      const chosen = !!c.code;
      return `
      <tr data-id="${c.id}">
        <td><span class="src-tag src-tag--${c.source}">${SRC_LABEL[c.source]}</span></td>
        <td><select data-coil-pick="${c.id}" class="${chosen ? "" : "needs-pick"}">${babyCoilOptions(c.source, c.code)}</select></td>
        <td class="cell-auto">${chosen ? c.material : "—"}</td>
        <td class="num cell-auto">${c.nett != null ? fmtNum(c.nett) : "—"}</td>
        <td class="num cell-auto">${c.gross != null ? fmtNum(c.gross) : "—"}</td>
        <td class="num cell-auto">${c.meter != null ? c.meter : "—"}</td>
        <td class="num cell-auto">${c.kulit != null ? fmtNum(c.kulit) : "—"}</td>
        <td class="num cell-auto">${c.clamp != null ? fmtNum(c.clamp) : "—"}</td>
        <td><button type="button" class="btn-icon" data-del-coil="${c.id}" title="Hapus">✕</button></td>
      </tr>`;
    }).join("");
    empty.parentElement.style.display = state.coils.length ? "none" : "";
  }

  // Saat user memilih baby coil, tarik data material/berat/meter (FR-08)
  function pickCoil(id, code) {
    const row = state.coils.find((c) => c.id === id);
    if (!row) return;
    if (!code) {
      Object.assign(row, { code: "", material: "", nett: null, gross: null, meter: null, kulit: null, clamp: null });
    } else {
      const data = M.coilByCode(row.source, code);
      if (data) Object.assign(row, data);
    }
    renderCoils();
    refreshCoilSelectors(); // perbarui dropdown coil di FG/KW2/Sisa/Hold
    recalc();
  }

  // Perbarui semua dropdown coil (nilai terpilih dipulihkan) setelah daftar coil berubah
  function refreshCoilSelectors() {
    renderFg();
    ["internal", "supplier"].forEach(renderKw2);
    renderLines("sisa");
    renderLines("hold");
  }

  /* ============================================================
     FG KW1 (otomatis dari SPK) & STOK BEBAS (manual)
     ============================================================ */
  function seedFgFromSpk() {
    spk.products.forEach((sp) => {
      const p = productById(sp.productId);
      state.fg.push({
        id: uid(), kind: "kw1",
        productId: sp.productId, productName: p ? p.name : sp.productId,
        targetQty: sp.targetQty,
        method: 1, qty: 0, total: 0, perPcs: 0, std: p ? p.std : 0, selisih: 0,
      });
    });
  }

  function addBebas() {
    state.fg.push({
      id: uid(), kind: "bebas", productId: "", productName: "",
      method: 1, qty: 0, total: 0, perPcs: 0, std: 0, selisih: 0,
    });
    renderFg();
    recalc();
  }

  function renderFg() {
    const kw1 = state.fg.filter((f) => f.kind === "kw1");
    const bebas = state.fg.filter((f) => f.kind === "bebas");
    $("#fgList").innerHTML = kw1.map(fgCard).join("");
    $("#bebasList").innerHTML = bebas.map(fgCard).join("");
    $("#fgEmpty").hidden = kw1.length > 0;
    $("#bebasEmpty").style.display = bebas.length ? "none" : "";
    restoreFg();
  }

  function fgCard(f) {
    const isBebas = f.kind === "bebas";
    const p1 = f.method === 1;
    const confirmed = f.confirmedAt != null && f.confirmedAt === f.selisih && Math.abs(f.selisih) > TOLERANCE;
    // Header produk: KW1 = label tetap dari SPK; Bebas = select master product
    const productHead = isBebas
      ? `<select data-fg-product="${f.id}">${productOptions()}</select>`
      : `<span class="fg-prod-name">${f.productName}</span>` +
        (f.targetQty != null ? `<span class="chip">Target ${f.targetQty} pcs</span>` : "");
    const delBtn = isBebas ? `<button type="button" class="btn-icon" data-del-fg="${f.id}" title="Hapus">✕</button>` : "";
    return `
    <div class="fg-card" data-id="${f.id}">
      <div class="fg-card__head">
        <span class="tag ${isBebas ? "tag--bebas" : ""}">${isBebas ? "Stok Bebas" : "KW 1"}</span>
        ${productHead}
        <span class="confirm-badge" data-fg-confirm="${f.id}" ${confirmed ? "" : "hidden"}>✓ Selisih dikonfirmasi</span>
        ${delBtn}
      </div>
      <div class="fg-card__body">
        <div class="method-toggle">
          <label><input type="radio" name="m-${f.id}" value="1" ${p1 ? "checked" : ""} data-fg-method="${f.id}"><span>Pilihan 1 · Qty + Berat Total</span></label>
          <label><input type="radio" name="m-${f.id}" value="2" ${!p1 ? "checked" : ""} data-fg-method="${f.id}"><span>Pilihan 2 · Qty + Berat/Pcs</span></label>
        </div>
        <div class="fg-row">
          <label class="field">
            <span class="field__label">Baby Coil Sumber</span>
            <select data-fg-coil="${f.id}">${coilOptions()}</select>
          </label>
          <label class="field">
            <span class="field__label">Qty (pcs)</span>
            <input type="number" min="0" step="1" data-fg-qty="${f.id}" value="${f.qty || ""}" />
          </label>
          <label class="field">
            <span class="field__label">${p1 ? "Berat Total (kg)" : "Berat/Pcs (kg)"}</span>
            <input type="number" min="0" step="0.01" data-fg-input="${f.id}" value="${p1 ? (f.total || "") : (f.perPcs || "")}" />
          </label>
          <label class="field">
            <span class="field__label">${p1 ? "Berat/Pcs (kg)" : "Berat Total (kg)"}</span>
            <input type="text" class="calc" readonly data-fg-derived="${f.id}" value="${p1 ? fmtNum(f.perPcs) : fmtNum(f.total)}" />
          </label>
          <label class="field">
            <span class="field__label">Berat Standard/Pcs</span>
            <input type="text" class="calc" readonly data-fg-std="${f.id}" value="${f.std ? fmtNum(f.std) : ""}" />
          </label>
          <label class="field">
            <span class="field__label">% Selisih</span>
            <input type="text" class="calc selisih-cell ${Math.abs(f.selisih) > TOLERANCE ? "bad" : "ok"}" readonly data-fg-selisih="${f.id}" value="${fmtNum(f.selisih)} %" />
          </label>
        </div>
      </div>
    </div>`;
  }

  function computeFg(f) {
    const p = productById(f.productId);
    f.std = p ? p.std : 0;
    if (f.method === 1) f.perPcs = f.qty > 0 ? f.total / f.qty : 0;
    else f.total = f.qty * f.perPcs;
    f.selisih = f.std > 0 ? ((f.perPcs - f.std) / f.std) * 100 : 0;
  }

  function restoreFg() {
    state.fg.forEach((f) => {
      const ps = $(`[data-fg-product="${f.id}"]`); if (ps) ps.value = f.productId || "";
      const cs = $(`[data-fg-coil="${f.id}"]`); if (cs) cs.value = f.coil || "";
    });
  }

  function productLabel(f) {
    const kind = f.kind === "bebas" ? "Stok Bebas" : "KW 1";
    return f.productName ? `${f.productName} (${kind})` : `Produk ${kind} (belum dipilih)`;
  }

  /* ============================================================
     FG KW2 (Internal / Supplier)
     ============================================================ */
  function addKw2(type) {
    state.kw2[type].push({ id: uid(), productId: "", size: 0, qty: 0, total: 0 });
    renderKw2(type);
    recalc();
  }
  function renderKw2(type) {
    const tbody = $(`#kw2${cap(type)}Table tbody`);
    const foot = tbody.nextElementSibling;
    const rows = state.kw2[type];
    foot.style.display = rows.length ? "none" : "";
    tbody.innerHTML = rows.map((r, i) => {
      const p = productById(r.productId);
      const perPcs = r.qty > 0 ? r.total / r.qty : 0;
      const inisial = type === "supplier" ? "S" : "I"; // S = Supplier, I = Internal
      const nama = p ? `${p.name}-KW2-${inisial}-${String(i + 1).padStart(2, "0")}` : "—";
      return `
      <tr data-id="${r.id}" data-type="${type}">
        <td>${i + 1}</td>
        <td><select data-kw2-product="${r.id}">${productOptions()}</select></td>
        <td class="num"><input type="number" min="0" step="0.01" data-kw2-size="${r.id}" value="${r.size || ""}" /></td>
        <td class="num"><input type="number" min="0" step="1" data-kw2-qty="${r.id}" value="${r.qty || ""}" /></td>
        <td class="num"><input type="number" min="0" step="0.01" data-kw2-total="${r.id}" value="${r.total || ""}" /></td>
        <td class="num cell-auto">${fmtNum(perPcs)}</td>
        <td class="cell-auto">${nama}</td>
        <td><input type="text" data-kw2-ket="${r.id}" placeholder="Free text" value="${r.ket || ""}" /></td>
        <td><button type="button" class="btn-icon" data-del-kw2="${r.id}" data-type="${type}" title="Hapus">✕</button></td>
      </tr>`;
    }).join("");
    rows.forEach((r) => { const s = tbody.querySelector(`[data-kw2-product="${r.id}"]`); if (s) s.value = r.productId; });
  }
  function restoreKw2(ty) {
    state.kw2[ty].forEach((r) => { const s = $(`[data-kw2-product="${r.id}"]`); if (s) s.value = r.productId; });
  }

  /* ============================================================
     SISA & HOLD COIL
     ============================================================ */
  function addLine(kind) { state[kind].push({ id: uid(), coil: "", berat: 0 }); renderLines(kind); recalc(); }
  function renderLines(kind) {
    const tbody = $(`#${kind}Table tbody`);
    const foot = tbody.nextElementSibling;
    foot.style.display = state[kind].length ? "none" : "";
    tbody.innerHTML = state[kind].map((r) => `
      <tr data-id="${r.id}" data-kind="${kind}">
        <td><select data-line-coil="${r.id}" data-kind="${kind}">${coilOptions()}</select></td>
        <td class="num"><input type="number" min="0" step="0.01" data-line-berat="${r.id}" data-kind="${kind}" value="${r.berat || ""}" /></td>
        <td><button type="button" class="btn-icon" data-del-line="${r.id}" data-kind="${kind}" title="Hapus">✕</button></td>
      </tr>`).join("");
    state[kind].forEach((r) => { const s = tbody.querySelector(`[data-line-coil="${r.id}"]`); if (s) s.value = r.coil; });
  }

  /* ============================================================
     SCRAP
     ============================================================ */
  function scrapTotal() {
    const ids = ["scrapTong", "scrapWrapping", "scrapPisau", "rejProdInt", "rejMatInt", "rejProdSup", "rejMatSup"];
    return ids.reduce((s, id) => s + num($("#" + id).value), 0);
  }

  /* ============================================================
     RECALC & SUMMARY (FR-15)
     ============================================================ */
  function recalc() {
    state.fg.forEach(computeFg);
    const fgTotal = state.fg.filter((f) => f.kind === "kw1").reduce((s, f) => s + f.total, 0);
    const bebasTotal = state.fg.filter((f) => f.kind === "bebas").reduce((s, f) => s + f.total, 0);
    const kw2Total =
      state.kw2.internal.reduce((s, r) => s + num(r.total), 0) +
      state.kw2.supplier.reduce((s, r) => s + num(r.total), 0);
    const scrap = scrapTotal();
    const sisaTotal = state.sisa.reduce((s, r) => s + num(r.berat), 0);
    const holdTotal = state.hold.reduce((s, r) => s + num(r.berat), 0);

    const netProd = fgTotal + bebasTotal + kw2Total + scrap + sisaTotal + holdTotal;
    const netPack = state.coils.reduce((s, c) => s + num(c.nett), 0);
    const selisihKg = netProd - netPack;
    const selisihPct = netProd > 0 ? (selisihKg / netProd) * 100 : 0;

    $("#sumFG").textContent = fmtKg(fgTotal + bebasTotal);
    $("#sumKW2").textContent = fmtKg(kw2Total);
    $("#sumScrap").textContent = fmtKg(scrap);
    $("#sumSisa").textContent = fmtKg(sisaTotal);
    $("#sumHold").textContent = fmtKg(holdTotal);
    $("#sumNetProd").textContent = fmtKg(netProd);
    $("#sumNetPack").textContent = fmtKg(netPack);
    $("#sumSelisihKg").textContent = fmtKg(selisihKg);
    $("#sumSelisihPct").textContent = fmtPct(selisihPct);
    $("#scrapTotal").textContent = fmtKg(scrap);

    const box = $("#sumSelisihBox");
    const over = Math.abs(selisihPct) > TOLERANCE && netProd > 0;
    box.classList.toggle("bad", over);
    $("#tolNote").textContent = over
      ? `⚠ Selisih ${fmtPct(selisihPct)} melebihi toleransi ± ${TOLERANCE}%. Perlu konfirmasi saat Save.`
      : `Toleransi ± ${TOLERANCE}% terhadap Net Weight Produksi.`;

    updateGate();
    return { selisihPct, over };
  }

  /* ============================================================
     GATE SAVE vs HOLD & CLAIM (FR-17, NFR-06)
     ============================================================ */
  function updateGate() {
    const hasHold = state.hold.length > 0;
    $("#btnSave").disabled = hasHold;
    $("#btnHoldClaim").disabled = !hasHold;
    $("#holdNote").hidden = !hasHold;
    $("#gateMsg").textContent = hasHold
      ? "Ada Hold Coil — Save dikunci. Gunakan Hold & Claim."
      : "Save aktif bila tidak ada Hold Coil.";
  }

  /* ============================================================
     MODAL & TOAST + AUDIT (NFR-04)
     ============================================================ */
  let modalResolve = null;
  function openTolModal(msg, ctx = null) {
    $("#tolModalBody").textContent = msg;
    $("#tolSigner").textContent = `${CURRENT_USER.name} (${CURRENT_USER.role})`;
    $("#tolModal").hidden = false;
    $("#tolModal").dataset.ctx = ctx ? JSON.stringify(ctx) : "";
    return new Promise((res) => (modalResolve = res));
  }
  function closeTolModal(ack) {
    const raw = $("#tolModal").dataset.ctx;
    $("#tolModal").hidden = true;
    if (ack && raw) { try { recordConfirmation(JSON.parse(raw)); } catch (e) { /* ignore */ } }
    if (modalResolve) { modalResolve(ack); modalResolve = null; }
  }
  $("#tolAck").addEventListener("click", () => closeTolModal(true));
  $("#tolCancel").addEventListener("click", () => closeTolModal(false));

  function recordConfirmation(ctx) {
    const now = new Date();
    const entry = {
      id: uid(), scope: ctx.scope, ref: ctx.ref, label: ctx.label,
      selisih: ctx.selisih, statement: ctx.statement || "Sudah sesuai aktual",
      user: CURRENT_USER.name, role: CURRENT_USER.role,
      ts: now.toISOString(),
      tsLabel: now.toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" }),
    };
    state.confirmations = state.confirmations.filter((c) => c.ref !== ctx.ref);
    state.confirmations.push(entry);
    if (ctx.scope === "fg") {
      const f = state.fg.find((x) => x.id === ctx.ref);
      if (f) f.confirmedAt = f.selisih;
      const badge = $(`[data-fg-confirm="${ctx.ref}"]`);
      if (badge) badge.hidden = false;
    }
    renderAudit();
    toast(`Konfirmasi selisih dicatat atas nama ${CURRENT_USER.name}.`, "ok");
  }

  function renderAudit() {
    const card = $("#auditCard");
    const body = $("#auditTable tbody");
    if (!state.confirmations.length) { card.hidden = true; body.innerHTML = ""; return; }
    card.hidden = false;
    body.innerHTML = state.confirmations
      .slice().sort((a, b) => a.ts.localeCompare(b.ts))
      .map((c, i) => `
        <tr>
          <td>${i + 1}</td>
          <td>${c.label}</td>
          <td class="num selisih-cell bad">${fmtNum(c.selisih)} %</td>
          <td>${c.statement}</td>
          <td><strong>${c.user}</strong><br /><small>${c.role}</small></td>
          <td>${c.tsLabel}</td>
        </tr>`).join("");
  }

  let toastTimer = null;
  function toast(msg, kind = "") {
    const t = $("#toast");
    t.textContent = msg;
    t.className = "toast" + (kind ? " toast--" + kind : "");
    t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => (t.hidden = true), 2600);
  }

  /* ============================================================
     EVENT DELEGATION
     ============================================================ */
  document.addEventListener("click", (e) => {
    const t = e.target;
    if (t.dataset.addCoil) addCoil(t.dataset.addCoil);
    if (t.dataset.delCoil) { state.coils = state.coils.filter((c) => c.id !== t.dataset.delCoil); renderCoils(); refreshCoilSelectors(); recalc(); }
    if (t.dataset.addFg === "bebas") addBebas();
    if (t.dataset.addKw2) addKw2(t.dataset.addKw2);
    if (t.dataset.delFg) { state.fg = state.fg.filter((f) => f.id !== t.dataset.delFg); renderFg(); recalc(); }
    if (t.dataset.delKw2) { const ty = t.dataset.type; state.kw2[ty] = state.kw2[ty].filter((r) => r.id !== t.dataset.delKw2); renderKw2(ty); recalc(); }
    if (t.dataset.delLine) { const k = t.dataset.kind; state[k] = state[k].filter((r) => r.id !== t.dataset.delLine); renderLines(k); recalc(); }
  });

  $("#addSisa").addEventListener("click", () => addLine("sisa"));
  $("#addHold").addEventListener("click", () => addLine("hold"));

  document.addEventListener("input", handleFieldChange);
  document.addEventListener("change", handleFieldChange);

  function handleFieldChange(e) {
    const t = e.target;
    if (!t || !t.dataset) return;
    const isChange = e.type === "change";
    let touched = false;

    // Add Coil: pilih baby coil -> tarik data material/berat (FR-08)
    if (t.dataset.coilPick != null) {
      pickCoil(t.dataset.coilPick, t.value);
      return; // pickCoil sudah rerender + recalc
    }

    // FG product (hanya Stok Bebas yang punya select)
    if (t.dataset.fgProduct != null) {
      const f = state.fg.find((x) => x.id === t.dataset.fgProduct);
      if (f) { f.productId = t.value; const p = productById(t.value); f.productName = p ? p.name : ""; touched = true; }
    }
    if (t.dataset.fgCoil != null) {
      const f = state.fg.find((x) => x.id === t.dataset.fgCoil);
      if (f) f.coil = t.value;
    }
    if (t.dataset.fgMethod != null && isChange) {
      const f = state.fg.find((x) => x.id === t.dataset.fgMethod);
      if (f) { f.method = parseInt(t.value, 10); renderFg(); recalc(); return; }
    }
    if (t.dataset.fgQty != null) {
      const f = state.fg.find((x) => x.id === t.dataset.fgQty);
      if (f) { f.qty = num(t.value); touched = true; }
    }
    if (t.dataset.fgInput != null) {
      const f = state.fg.find((x) => x.id === t.dataset.fgInput);
      if (f) { if (f.method === 1) f.total = num(t.value); else f.perPcs = num(t.value); touched = true; }
    }

    ["internal", "supplier"].forEach((ty) => {
      if (t.dataset.kw2Product != null) { const r = find(ty, t.dataset.kw2Product); if (r) { r.productId = t.value; touched = true; renderKw2(ty); restoreKw2(ty); } }
      if (t.dataset.kw2Size != null) { const r = find(ty, t.dataset.kw2Size); if (r) r.size = num(t.value); }
      if (t.dataset.kw2Qty != null) { const r = find(ty, t.dataset.kw2Qty); if (r) { r.qty = num(t.value); touched = true; if (isChange) { renderKw2(ty); restoreKw2(ty); } } }
      if (t.dataset.kw2Total != null) { const r = find(ty, t.dataset.kw2Total); if (r) { r.total = num(t.value); touched = true; if (isChange) { renderKw2(ty); restoreKw2(ty); } } }
      if (t.dataset.kw2Ket != null) { const r = find(ty, t.dataset.kw2Ket); if (r) r.ket = t.value; }
    });

    if (t.dataset.lineCoil != null) { const r = state[t.dataset.kind].find((x) => x.id === t.dataset.lineCoil); if (r) r.coil = t.value; }
    if (t.dataset.lineBerat != null) { const r = state[t.dataset.kind].find((x) => x.id === t.dataset.lineBerat); if (r) { r.berat = num(t.value); touched = true; } }

    if (t.classList.contains("scrap-in")) touched = true;

    if (touched) { recalc(); updateFgDerived(); }
  }

  function find(ty, id) { return state.kw2[ty].find((x) => x.id === id); }

  function updateFgDerived() {
    state.fg.forEach((f) => {
      const der = $(`[data-fg-derived="${f.id}"]`);
      const std = $(`[data-fg-std="${f.id}"]`);
      const sel = $(`[data-fg-selisih="${f.id}"]`);
      if (der) der.value = f.method === 1 ? fmtNum(f.perPcs) : fmtNum(f.total);
      if (std) std.value = f.std ? fmtNum(f.std) : "";
      if (sel) {
        sel.value = fmtNum(f.selisih) + " %";
        sel.classList.toggle("bad", Math.abs(f.selisih) > TOLERANCE);
        sel.classList.toggle("ok", Math.abs(f.selisih) <= TOLERANCE);
      }
      const badge = $(`[data-fg-confirm="${f.id}"]`);
      if (badge) badge.hidden = !(f.confirmedAt != null && f.confirmedAt === f.selisih && Math.abs(f.selisih) > TOLERANCE);
    });
  }

  function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

  /* ============================================================
     ACTIONS: Draft / Hold&Claim / Save
     ============================================================ */
  $("#btnDraft").addEventListener("click", () => {
    $("#docStatus").textContent = "Draft (tersimpan)";
    toast("Draft disimpan. Aktual dapat dicek terlebih dahulu.", "ok");
  });
  $("#btnHoldClaim").addEventListener("click", () => {
    toast("Laporan dikirim ke alur Hold & Claim Material (PPIC).", "warn");
    $("#docStatus").textContent = "Hold & Claim";
  });

  $("#prodForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const { over, selisihPct } = recalc();

    const items = [];
    state.fg.forEach((f) => {
      if (f.std > 0 && Math.abs(f.selisih) > TOLERANCE) {
        items.push({ scope: "fg", ref: f.id, label: productLabel(f), selisih: f.selisih, statement: "Sudah sesuai aktual" });
      }
    });
    if (over) items.push({ scope: "summary", ref: "summary", label: "Total Produksi (Summary)", selisih: selisihPct, statement: "Sudah cek lapangan — sesuai aktual" });

    if (items.length) {
      for (let i = 0; i < items.length; i++) {
        const it = items[i];
        const konfirmasi = await openTolModal(
          `(${i + 1}/${items.length}) ${it.label} memiliki %Selisih ${fmtNum(it.selisih)}% — di luar toleransi ± ${TOLERANCE}%. ` +
          `Jika berat sudah sesuai kondisi aktual di lapangan, konfirmasi untuk mencatat tanggung jawab Anda.`, it);
        if (!konfirmasi) { toast("Submit dibatalkan — silakan perbaiki laporan.", "warn"); return; }
      }
      $("#docStatus").textContent = "Menunggu Approval Toleransi";
      toast(`Laporan disubmit dengan ${items.length} konfirmasi selisih tercatat.`, "warn");
      M.spkStatus.markDone(spk.no);
      return;
    }
    $("#docStatus").textContent = "Submitted";
    $("#docStatus").className = "badge badge--role";
    M.spkStatus.markDone(spk.no);
    toast("Laporan Produksi berhasil disubmit ✓", "ok");
  });

  /* ============================================================
     INIT
     ============================================================ */
  function fillSelect(el, arr, placeholder) {
    el.innerHTML = `<option value="">${placeholder}</option>` + arr.map((v) => `<option>${v}</option>`).join("");
  }

  function renderSpkMeta() {
    $("#spkMeta").innerHTML = `
      <div class="meta"><span class="meta__k">No. SPK</span><span class="meta__v">${spk.no}</span></div>
      <div class="meta"><span class="meta__k">Customer</span><span class="meta__v">${spk.customer}</span></div>
      <div class="meta"><span class="meta__k">Tanggal</span><span class="meta__v">${spk.tgl}</span></div>
      <div class="meta"><span class="meta__k">Produk KW1</span><span class="meta__v">${spk.products.length} produk</span></div>`;
  }

  function init() {
    // header selects dari master
    fillSelect($("#mesin"), M.MACHINES, "— Select Mesin —");
    fillSelect($("#helper"), M.HELPERS, "— Select Helper —");
    fillSelect($("#setter"), M.SETTERS, "— Select Setter —");
    $("#tglProduksi").valueAsDate = new Date();
    $("#startTime").value = "08:00";
    $("#finishTime").value = "16:00";

    const uname = document.querySelector(".topbar__user-name");
    if (uname) uname.textContent = CURRENT_USER.name;

    renderSpkMeta();

    // Seed 1 coil contoh (sudah terpilih) agar mockup langsung hidup; user bisa tambah/hapus.
    seedCoil("unpack", "BC-U-1001");
    renderCoils();

    seedFgFromSpk();
    renderFg();

    renderKw2("internal");
    renderKw2("supplier");
    renderLines("sisa");
    renderLines("hold");
    renderAudit();
    recalc();
  }

  // Helper seed: tambah baris coil yang langsung terpilih
  function seedCoil(source, code) {
    const data = M.coilByCode(source, code);
    state.coils.push({ id: uid(), source, ...(data || { code: "", material: "", nett: null, gross: null, meter: null, kulit: null, clamp: null }) });
  }

  document.addEventListener("DOMContentLoaded", init);
})();
