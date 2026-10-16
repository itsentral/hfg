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

  // Opsi baby coil per sumber; sembunyikan yang sudah dipakai baris lain (dedup),
  // dan batasi ke material yang terdaftar di Master Costbook saja.
  function babyCoilOptions(source, selectedCode) {
    const usedElsewhere = new Set(state.coils.filter((c) => c.code && c.code !== selectedCode).map((c) => c.code));
    const cbMaterials = new Set(M.costbook.materials());   // material yang ada di costbook
    const opts = M.COILS[source]
      .filter((c) => !usedElsewhere.has(c.code))
      // hanya material yang ada di costbook; kecualikan yang sedang terpilih agar nilai tak hilang
      .filter((c) => cbMaterials.has(c.material) || c.code === selectedCode)
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
     Satu produk (kartu) dapat memakai >1 sumber material/coil (sources[]).
     ============================================================ */
  function newSource() {
    return { id: uid(), coil: "", method: 1, qty: 0, total: 0, perPcs: 0, selisih: 0, confirmedAt: null };
  }
  function seedFgFromSpk() {
    spk.products.forEach((sp) => {
      const p = productById(sp.productId);
      state.fg.push({
        id: uid(), kind: "kw1",
        productId: sp.productId, productName: p ? p.name : sp.productId,
        targetQty: sp.targetQty,
        std: p ? p.std : 0,
        sources: [newSource()],
      });
    });
  }

  function addBebas() {
    state.fg.push({
      id: uid(), kind: "bebas", productId: "", productName: "",
      std: 0, sources: [newSource()],
    });
    renderFg();
    recalc();
  }

  function addFgSource(fid) {
    const f = state.fg.find((x) => x.id === fid);
    if (!f) return;
    f.sources.push(newSource());
    renderFg();
    recalc();
  }
  function delFgSource(fid, sid) {
    const f = state.fg.find((x) => x.id === fid);
    if (!f) return;
    f.sources = f.sources.filter((s) => s.id !== sid);
    if (!f.sources.length) f.sources.push(newSource()); // selalu ada minimal satu
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
    const productHead = isBebas
      ? `<select data-fg-product="${f.id}">${productOptions()}</select>`
      : `<span class="fg-prod-name">${f.productName}</span>` +
        (f.targetQty != null ? `<span class="chip">Target ${f.targetQty} pcs</span>` : "");
    const delBtn = isBebas ? `<button type="button" class="btn-icon" data-del-fg="${f.id}" title="Hapus produk">✕</button>` : "";
    const multi = f.sources.length > 1;
    const sourcesHtml = f.sources.map((s, i) => fgSourceRow(f, s, i, multi)).join("");
    return `
    <div class="fg-card" data-id="${f.id}">
      <div class="fg-card__head">
        <span class="tag ${isBebas ? "tag--bebas" : ""}">${isBebas ? "Stok Bebas" : "KW 1"}</span>
        ${productHead}
        ${delBtn}
      </div>
      <div class="fg-card__body">
        ${sourcesHtml}
        <div class="btn-row">
          <button type="button" class="btn btn--tiny" data-add-fg-source="${f.id}">+ Tambah Material / Coil</button>
        </div>
      </div>
    </div>`;
  }

  // Satu baris sumber material/coil untuk sebuah produk FG.
  function fgSourceRow(f, s, idx, multi) {
    const p1 = s.method === 1;
    const key = `${f.id}:${s.id}`;
    const confirmed = s.confirmedAt != null && s.confirmedAt === s.selisih && Math.abs(s.selisih) > TOLERANCE;
    const srcDel = multi ? `<button type="button" class="btn-icon" data-del-fg-source="${key}" title="Hapus sumber">✕</button>` : "";
    return `
    <div class="fg-source" data-key="${key}">
      <div class="fg-source__bar">
        <span class="fg-source__no">Sumber ${idx + 1}</span>
        <div class="method-toggle">
          <label><input type="radio" name="m-${key}" value="1" ${p1 ? "checked" : ""} data-fg-method="${key}"><span>Qty + Berat Total</span></label>
          <label><input type="radio" name="m-${key}" value="2" ${!p1 ? "checked" : ""} data-fg-method="${key}"><span>Qty + Berat/Pcs</span></label>
        </div>
        <span class="confirm-badge" data-fg-confirm="${key}" ${confirmed ? "" : "hidden"}>✓ Selisih dikonfirmasi</span>
        ${srcDel}
      </div>
      <div class="fg-row">
        <label class="field">
          <span class="field__label">Baby Coil Sumber</span>
          <select data-fg-coil="${key}">${coilOptions()}</select>
        </label>
        <label class="field">
          <span class="field__label">Qty (pcs)</span>
          <input type="number" min="0" step="1" data-fg-qty="${key}" value="${s.qty || ""}" />
        </label>
        <label class="field">
          <span class="field__label">${p1 ? "Berat Total (kg)" : "Berat/Pcs (kg)"}</span>
          <input type="number" min="0" step="0.01" data-fg-input="${key}" value="${p1 ? (s.total || "") : (s.perPcs || "")}" />
        </label>
        <label class="field">
          <span class="field__label">${p1 ? "Berat/Pcs (kg)" : "Berat Total (kg)"}</span>
          <input type="text" class="calc" readonly data-fg-derived="${key}" value="${p1 ? fmtNum(s.perPcs) : fmtNum(s.total)}" />
        </label>
        <label class="field">
          <span class="field__label">Berat Standard/Pcs</span>
          <input type="text" class="calc" readonly data-fg-std="${key}" value="${f.std ? fmtNum(f.std) : ""}" />
        </label>
        <label class="field">
          <span class="field__label">% Selisih</span>
          <input type="text" class="calc selisih-cell ${Math.abs(s.selisih) > TOLERANCE ? "bad" : "ok"}" readonly data-fg-selisih="${key}" value="${fmtNum(s.selisih)} %" />
        </label>
      </div>
    </div>`;
  }

  // Hitung ulang seluruh sumber di sebuah kartu FG (std produk dipakai semua sumber).
  function computeFg(f) {
    const p = productById(f.productId);
    f.std = p ? p.std : 0;
    f.sources.forEach((s) => {
      if (s.method === 1) s.perPcs = s.qty > 0 ? s.total / s.qty : 0;
      else s.total = s.qty * s.perPcs;
      s.selisih = f.std > 0 ? ((s.perPcs - f.std) / f.std) * 100 : 0;
    });
  }

  // Total berat & qty seluruh sumber sebuah produk
  function fgCardTotal(f) { return f.sources.reduce((a, s) => a + (s.total || 0), 0); }

  function restoreFg() {
    state.fg.forEach((f) => {
      const ps = $(`[data-fg-product="${f.id}"]`); if (ps) ps.value = f.productId || "";
      f.sources.forEach((s) => {
        const cs = $(`[data-fg-coil="${f.id}:${s.id}"]`); if (cs) cs.value = s.coil || "";
      });
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
    state.kw2[type].push({ id: uid(), productId: "", coil: "", size: 0, qty: 0, total: 0 });
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
        <td><select data-kw2-coil="${r.id}">${coilOptions()}</select></td>
        <td class="num"><input type="number" min="0" step="0.01" data-kw2-size="${r.id}" value="${r.size || ""}" /></td>
        <td class="num"><input type="number" min="0" step="1" data-kw2-qty="${r.id}" value="${r.qty || ""}" /></td>
        <td class="num"><input type="number" min="0" step="0.01" data-kw2-total="${r.id}" value="${r.total || ""}" /></td>
        <td class="num cell-auto">${fmtNum(perPcs)}</td>
        <td class="cell-auto">${nama}</td>
        <td><input type="text" data-kw2-ket="${r.id}" placeholder="Free text" value="${r.ket || ""}" /></td>
        <td><button type="button" class="btn-icon" data-del-kw2="${r.id}" data-type="${type}" title="Hapus">✕</button></td>
      </tr>`;
    }).join("");
    rows.forEach((r) => {
      const s = tbody.querySelector(`[data-kw2-product="${r.id}"]`); if (s) s.value = r.productId;
      const cs = tbody.querySelector(`[data-kw2-coil="${r.id}"]`); if (cs) cs.value = r.coil || "";
    });
  }
  function restoreKw2(ty) {
    state.kw2[ty].forEach((r) => {
      const s = $(`[data-kw2-product="${r.id}"]`); if (s) s.value = r.productId;
      const cs = $(`[data-kw2-coil="${r.id}"]`); if (cs) cs.value = r.coil || "";
    });
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
    const fgTotal = state.fg.filter((f) => f.kind === "kw1").reduce((s, f) => s + fgCardTotal(f), 0);
    const bebasTotal = state.fg.filter((f) => f.kind === "bebas").reduce((s, f) => s + fgCardTotal(f), 0);
    const kw2Total =
      state.kw2.internal.reduce((s, r) => s + num(r.total), 0) +
      state.kw2.supplier.reduce((s, r) => s + num(r.total), 0);
    const scrap = scrapTotal();
    const sisaTotal = state.sisa.reduce((s, r) => s + num(r.berat), 0);
    const holdTotal = state.hold.reduce((s, r) => s + num(r.berat), 0);

    const netProd = fgTotal + bebasTotal + kw2Total + scrap + sisaTotal + holdTotal;
    const netPack = state.coils.reduce((s, c) => s + num(c.nett), 0);
    const selisihKg = netPack - netProd;   // Net Weight Packing List - Net Weight Produksi
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
      const s = fgSource(ctx.ref);
      if (s) s.confirmedAt = s.selisih;
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
    if (t.dataset.addFgSource) addFgSource(t.dataset.addFgSource);
    if (t.dataset.delFgSource) { const [fid, sid] = t.dataset.delFgSource.split(":"); delFgSource(fid, sid); }
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

    // FG product (hanya Stok Bebas yang punya select) — level kartu
    if (t.dataset.fgProduct != null) {
      const f = state.fg.find((x) => x.id === t.dataset.fgProduct);
      if (f) { f.productId = t.value; const p = productById(t.value); f.productName = p ? p.name : ""; touched = true; }
    }
    // Sumber material/coil — key "fid:sid"
    if (t.dataset.fgCoil != null) {
      const s = fgSource(t.dataset.fgCoil);
      if (s) s.coil = t.value;
    }
    if (t.dataset.fgMethod != null && isChange) {
      const s = fgSource(t.dataset.fgMethod);
      if (s) { s.method = parseInt(t.value, 10); renderFg(); recalc(); return; }
    }
    if (t.dataset.fgQty != null) {
      const s = fgSource(t.dataset.fgQty);
      if (s) { s.qty = num(t.value); touched = true; }
    }
    if (t.dataset.fgInput != null) {
      const s = fgSource(t.dataset.fgInput);
      if (s) { if (s.method === 1) s.total = num(t.value); else s.perPcs = num(t.value); touched = true; }
    }

    ["internal", "supplier"].forEach((ty) => {
      if (t.dataset.kw2Product != null) { const r = find(ty, t.dataset.kw2Product); if (r) { r.productId = t.value; touched = true; renderKw2(ty); restoreKw2(ty); } }
      if (t.dataset.kw2Coil != null) { const r = find(ty, t.dataset.kw2Coil); if (r) r.coil = t.value; }
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

  // Resolusi sumber FG dari key "fid:sid"
  function fgSource(key) {
    const [fid, sid] = String(key).split(":");
    const f = state.fg.find((x) => x.id === fid);
    return f ? f.sources.find((s) => s.id === sid) : null;
  }

  function updateFgDerived() {
    state.fg.forEach((f) => {
      f.sources.forEach((s) => {
        const key = `${f.id}:${s.id}`;
        const der = $(`[data-fg-derived="${key}"]`);
        const std = $(`[data-fg-std="${key}"]`);
        const sel = $(`[data-fg-selisih="${key}"]`);
        if (der) der.value = s.method === 1 ? fmtNum(s.perPcs) : fmtNum(s.total);
        if (std) std.value = f.std ? fmtNum(f.std) : "";
        if (sel) {
          sel.value = fmtNum(s.selisih) + " %";
          sel.classList.toggle("bad", Math.abs(s.selisih) > TOLERANCE);
          sel.classList.toggle("ok", Math.abs(s.selisih) <= TOLERANCE);
        }
        const badge = $(`[data-fg-confirm="${key}"]`);
        if (badge) badge.hidden = !(s.confirmedAt != null && s.confirmedAt === s.selisih && Math.abs(s.selisih) > TOLERANCE);
      });
    });
  }

  function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

  /* ============================================================
     SNAPSHOT UNTUK HPP (FR-19..FR-23) — bentuk data per SPK
     ============================================================ */
  // Resolusi coil terpilih (id baris coil) -> {material, gudang(source)}.
  function coilInfo(coilId) {
    const c = state.coils.find((x) => x.id === coilId);
    return c ? { material: c.material || "", gudang: c.source || "unpack" } : { material: "", gudang: "unpack" };
  }
  // Material & gudang "utama" untuk item yang tak terikat coil (scrap/selisih):
  // pakai coil pertama yang terpilih, default gudang unpack (sesuai Excel).
  function primaryInfo() {
    const c = state.coils.find((x) => x.code);
    return { material: c ? c.material : "", gudang: c ? c.source : "unpack" };
  }

  function buildReport() {
    const prim = primaryInfo();

    // Satu produk dengan >1 sumber -> dipecah menjadi beberapa sub-baris HPP,
    // satu per sumber (material/gudang bisa berbeda per sumber).
    const fgRows = (f) => {
      const p = productById(f.productId);
      const base = f.productName || (f.kind === "bebas" ? "Stok Bebas" : "Produk");
      const multi = f.sources.length > 1;
      return f.sources.map((s) => {
        const info = coilInfo(s.coil);
        const label = multi && info.gudang ? `${base} (${SRC_LABEL[info.gudang] || info.gudang})` : base;
        return {
          produk: label,
          kind: f.kind === "bebas" ? "Bebas" : "KW1",
          totalWeight: s.total || 0,
          beratPcs: s.perPcs || 0,
          size: p ? p.size : 0,
          qty: s.qty || 0,
          material: info.material,
          gudang: info.gudang,
        };
      });
    };
    const kw2Row = (r) => {
      const info = coilInfo(r.coil);
      const p = productById(r.productId);
      const qty = num(r.qty), total = num(r.total);
      return {
        produk: p ? p.name : "Produk KW2",
        totalWeight: total,
        beratPcs: qty > 0 ? total / qty : 0,
        size: num(r.size) || (p ? p.size : 0),
        qty: qty,
        material: info.material,
        gudang: info.gudang,
      };
    };

    const kw1 = state.fg.reduce((acc, f) => acc.concat(fgRows(f)), []);   // KW1 + Stok Bebas, dipecah per sumber
    const kw2Internal = state.kw2.internal.map(kw2Row);
    const kw2Supplier = state.kw2.supplier.map(kw2Row);

    // Scrap 7 kategori (Excel baris 135-141) — costbook dari Gudang Produksi 2 (unpack)
    const scrapVal = (id) => num($("#" + id).value);
    const scrap = [
      { item: "Reject Produk Internal", weight: scrapVal("rejProdInt") },
      { item: "Reject Material Internal", weight: scrapVal("rejMatInt") },
      { item: "Reject Produk Supplier", weight: scrapVal("rejProdSup") },
      { item: "Reject Material Supplier", weight: scrapVal("rejMatSup") },
      { item: "Tong Coil", weight: scrapVal("scrapTong") },
      { item: "Wrapping", weight: scrapVal("scrapWrapping") },
      { item: "Potongan Pisau", weight: scrapVal("scrapPisau") },
    ].map((s) => ({ ...s, material: prim.material, gudang: "unpack" }));

    const lineRow = (r) => {
      const info = coilInfo(r.coil);
      const c = state.coils.find((x) => x.id === r.coil);
      return {
        item: c && c.code ? `${c.code} · ${info.material}` : "(Baby Coil)",
        weight: num(r.berat),
        material: info.material,
        gudang: info.gudang,
      };
    };
    const sisa = state.sisa.map(lineRow);
    const hold = state.hold.map(lineRow);

    // ==== Selisih Berat per (material, gudang) — Hold digabung ke Unpack ====
    // bucket: "unpack" (termasuk hold) atau "wip". WIP dipakai duluan sampai habis;
    // KW2 & Scrap dibebankan ke Unpack. Scrap dialokasikan proporsional atas
    // Nett Packing List tiap material Unpack. Sisa Coil = faktor pengurang.
    const bucketOf = (g) => (g === "wip" ? "wip" : "unpack");   // hold -> unpack
    const selMap = {};   // key "material|bucket" -> {material, bucket, gudangAsli, packing, kw1, kw2, sisa, scrap}
    const selKey = (mat, b) => `${mat}|${b}`;
    const ensure = (mat, b, gudangAsli) => {
      const k = selKey(mat, b);
      if (!selMap[k]) selMap[k] = { material: mat, bucket: b, gudang: gudangAsli || b, packing: 0, kw1: 0, kw2: 0, sisa: 0, scrap: 0 };
      return selMap[k];
    };

    // Nett Packing List per material×bucket (dari coil yang di-Add)
    state.coils.filter((c) => c.code).forEach((c) => {
      const b = bucketOf(c.source);
      ensure(c.material, b, c.source).packing += num(c.nett);
    });
    // Berat KW1 (per sumber) terpakai per material×bucket
    state.fg.filter((f) => f.kind !== "bebasSkip").forEach((f) => {
      f.sources.forEach((s) => {
        const info = coilInfo(s.coil);
        if (!info.material) return;
        ensure(info.material, bucketOf(info.gudang), info.gudang).kw1 += (s.total || 0);
      });
    });
    // Berat KW2 (Internal+Supplier) terpakai per material×bucket
    [].concat(state.kw2.internal, state.kw2.supplier).forEach((r) => {
      const info = coilInfo(r.coil);
      if (!info.material) return;
      ensure(info.material, bucketOf(info.gudang), info.gudang).kw2 += num(r.total);
    });
    // Sisa Coil (faktor pengurang) per material×bucket
    state.sisa.forEach((r) => {
      const info = coilInfo(r.coil);
      if (!info.material) return;
      ensure(info.material, bucketOf(info.gudang), info.gudang).sisa += num(r.berat);
    });
    // Scrap total -> alokasi proporsional atas Nett Packing material bucket Unpack
    const scrapTotAll = scrap.reduce((s, r) => s + r.weight, 0);
    const unpackKeys = Object.values(selMap).filter((e) => e.bucket === "unpack");
    const unpackPackingTot = unpackKeys.reduce((s, e) => s + e.packing, 0);
    if (scrapTotAll > 0 && unpackPackingTot > 0) {
      unpackKeys.forEach((e) => { e.scrap += scrapTotAll * (e.packing / unpackPackingTot); });
    } else if (scrapTotAll > 0 && unpackKeys.length) {
      unpackKeys[0].scrap += scrapTotAll;   // fallback: bebankan ke material unpack pertama
    }

    // Selisih(kg) = Packing - KW1 - KW2 - Scrap - Sisa ; nilai = kg × costbook material
    const selisih = Object.values(selMap).map((e) => {
      const kgSel = e.packing - e.kw1 - e.kw2 - e.scrap - e.sisa;
      return {
        item: `Selisih Berat Material dari ${SRC_LABEL[e.bucket] || e.bucket}` + (e.material ? ` — ${e.material}` : ""),
        weight: kgSel,
        material: e.material,
        gudang: e.gudang,
      };
    }).filter((r) => Math.abs(r.weight) > 1e-9 || r.material);

    // Total selisih kg (untuk summary lama, tetap = netPack - netProd)
    const fgTotal = state.fg.filter((f) => f.kind === "kw1").reduce((s, f) => s + fgCardTotal(f), 0);
    const bebasTotal = state.fg.filter((f) => f.kind === "bebas").reduce((s, f) => s + fgCardTotal(f), 0);
    const kw2Total =
      state.kw2.internal.reduce((s, r) => s + num(r.total), 0) +
      state.kw2.supplier.reduce((s, r) => s + num(r.total), 0);
    const scrapTot = scrapTotAll;
    const sisaTot = sisa.reduce((s, r) => s + r.weight, 0);
    const holdTot = hold.reduce((s, r) => s + r.weight, 0);
    const netProd = fgTotal + bebasTotal + kw2Total + scrapTot + sisaTot + holdTot;
    const netPack = state.coils.reduce((s, c) => s + num(c.nett), 0);
    const selisihKg = netPack - netProd;   // Net Weight Packing List - Net Weight Produksi

    return {
      spkNo: spk.no,
      meta: {
        spk: spk.no,
        tgl: $("#tglProduksi").value || spk.tgl,
        mesin: $("#mesin").value || "",
        report: "RP-" + spk.no.replace(/[^0-9]/g, "").slice(-8),
      },
      kw1, kw2Internal, kw2Supplier, scrap, sisa, hold,
      selisih,
      coils: state.coils.filter((c) => c.code).map((c) => ({ source: c.source, material: c.material, nett: num(c.nett) })),
      savedAt: new Date().toISOString(),
    };
  }

  function persistReport() {
    try { M.report.save(spk.no, buildReport()); } catch (e) { /* ignore in mockup */ }
  }

  /* ============================================================
     ACTIONS: Draft / Hold&Claim / Save
     ============================================================ */
  $("#btnDraft").addEventListener("click", () => {
    persistReport();
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
      const multi = f.sources.length > 1;
      f.sources.forEach((s, idx) => {
        if (f.std > 0 && Math.abs(s.selisih) > TOLERANCE) {
          const label = productLabel(f) + (multi ? ` — Sumber ${idx + 1}` : "");
          items.push({ scope: "fg", ref: `${f.id}:${s.id}`, label, selisih: s.selisih, statement: "Sudah sesuai aktual" });
        }
      });
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
      persistReport();
      M.spkStatus.markDone(spk.no);
      return;
    }
    $("#docStatus").textContent = "Submitted";
    $("#docStatus").className = "badge badge--role";
    persistReport();
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
