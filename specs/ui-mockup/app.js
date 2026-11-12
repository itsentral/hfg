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
    claimIn: {},          // isian Produksi untuk baris Claim Material (problem/alasan/bukti/coil), kunci = key baris (FR-98)
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
  // G1 (FR-95): coil Hold yang sudah di-claim tidak tersedia di Unpack/WIP; yang diputuskan Lanjut Produksi tersedia lagi di sumber Hold
  // (lengkap dengan catatan untuk Produksi). Dibaca dari data Claim (window.CL; mockClaim.data).
  function claimHeld() {
    const out = { held: new Set(), released: [] };
    if (!window.CL) return out;
    window.CL.state.claims.forEach((c) => {
      if (c.asal !== "Hold Coil" || !c.babyCoil) return;
      out.held.add(c.babyCoil);
      const d = c.decisions.find((x) => x.aksi === "Lanjut Produksi");
      if (d) out.released.push({ code: c.babyCoil, claim: c.id, catatan: d.ket || "" });
    });
    return out;
  }
  function coilMasterByCode(code) {
    const all = M.COILS;
    for (const k of Object.keys(all)) { const f = all[k].find((c) => c.code === code); if (f) return Object.assign({}, f); }
    return null;
  }
  function releasedCoils() {
    const have = new Set(M.COILS.hold.map((c) => c.code));
    return claimHeld().released.filter((r) => !have.has(r.code)).map((r) => { const d = coilMasterByCode(r.code); return d ? Object.assign(d, { claim: r.claim, catatan: r.catatan }) : null; }).filter(Boolean);
  }
  // Catatan material di-hold untuk SPK ini: claim dari Hold & Claim yang belum diputuskan Purchasing (FR-94; keputusan user 2026-10-30)
  function updateHoldNote() {
    const el = $("#spkHoldNote"); if (!el) return;
    const hm = window.CL ? CL.state.claims.filter((c) => c.spk === spk.no && !c.decisions.length) : [];
    el.hidden = !hm.length;
    el.innerHTML = hm.length ? "&#9888; Catatan: ada material yang di-hold dari SPK ini (menunggu keputusan Purchasing): " + hm.map((c) => `<strong>${c.coil}</strong> ${c.material} &middot; ${M.fmtNum(c.kg)} Kg (${c.asal}, ${c.id})`).join("; ") + "." : "";
  }
  function updateReleaseNote() {
    updateHoldNote();
    const el = $("#releaseNote"); if (!el) return;
    const rc = releasedCoils();
    el.hidden = !rc.length;
    el.innerHTML = rc.length ? "Coil Release dari Claim tersedia di Add Coil From Hold: " + rc.map((r) => `<strong>${r.code}</strong> (${r.claim}${r.catatan ? " — Catatan untuk Produksi: " + r.catatan : ""})`).join("; ") : "";
  }
  function babyCoilOptions(source, selectedCode) {
    const usedElsewhere = new Set(state.coils.filter((c) => c.code && c.code !== selectedCode).map((c) => c.code));
    const cbMaterials = new Set(M.costbook.materials());   // material yang ada di costbook
    const ch = claimHeld();
    const base = source === "hold" ? M.COILS.hold.concat(releasedCoils()) : M.COILS[source].filter((c) => !ch.held.has(c.code) || c.code === selectedCode);
    const opts = base
      .filter((c) => !usedElsewhere.has(c.code))
      // hanya material yang ada di costbook; kecualikan yang sedang terpilih agar nilai tak hilang
      .filter((c) => cbMaterials.has(c.material) || c.code === selectedCode)
      .map((c) => `<option value="${c.code}" ${c.code === selectedCode ? "selected" : ""}>${c.code} · ${c.material}${c.claim ? " · Release " + c.claim : ""}</option>`)
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
    updateReleaseNote();
  }

  // Saat user memilih baby coil, tarik data material/berat/meter (FR-08)
  function pickCoil(id, code) {
    const row = state.coils.find((c) => c.id === id);
    if (!row) return;
    if (!code) {
      Object.assign(row, { code: "", material: "", nett: null, gross: null, meter: null, kulit: null, clamp: null });
    } else {
      const data = M.coilByCode(row.source, code) || (row.source === "hold" ? coilMasterByCode(code) : null);
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
     GATE: Save tidak pernah dikunci Hold Coil; Submit dikunci selama claim Hold Coil belum diputuskan (FR-17/FR-18/NFR-06 revisi FR-98)
     ============================================================ */
  function updateGate() {
    const pend = holdPending(), hasClaim = claimRows().length > 0;
    $("#btnSave").disabled = pend.length > 0;
    $("#holdNote").hidden = !state.hold.length;
    $("#gateMsg").textContent = pend.length
      ? "Submit dikunci: claim Hold Coil " + pend.map((c) => c.id).join(", ") + " menunggu keputusan Purchasing. Save tetap aktif."
      : (hasClaim ? "Save menyimpan laporan sekaligus mengirim claim ke Claim Material." : "Save dan Submit aktif.");
    renderClaims();
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

  // Varian BOM produk pada SPK ini (dipilih saat Add SPK); "" bila produk tanpa varian / Stok Bebas.
  const bomOf = (productId) => {
    const sp = spk.products.find((x) => x.productId === productId);
    return sp && sp.bomUniq ? sp.bomUniq : "";
  };
  // Stok Bebas mengikuti varian BOM yang dipilih SPK: varian produk yang sama bila ada di SPK, kalau tidak varian SPK pertama.
  const bomOfBebas = (productId) => {
    const own = bomOf(productId);
    if (own) return own;
    const sp = spk.products.find((x) => x.bomUniq);
    return sp ? sp.bomUniq : "";
  };
  // Rate Rp/Kg Hasil Costing dibekukan saat laporan pertama kali dibuat (FR-49, NFR-09): bila laporan tersimpan
  // sudah memuat rate varian ini, nilai itu dipakai lagi; jika belum, diambil dari Hasil Costing terkini.
  const prevReport = M.report.get(spk.no);
  const frozenCache = {};
  const frozenRate = (uniq) => {
    if (!uniq) return null;
    if (!frozenCache[uniq]) {
      const prev = prevReport && prevReport.rateByBom && prevReport.rateByBom[uniq];
      frozenCache[uniq] = prev || (M.bomRateKg ? M.bomRateKg(uniq) : null);
    }
    return frozenCache[uniq];
  };

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
          bomUniq: f.kind === "bebas" ? bomOfBebas(f.productId) : bomOf(f.productId),
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
        bomUniq: bomOfBebas(r.productId),
      };
    };

    const kw1 = state.fg.reduce((acc, f) => acc.concat(fgRows(f)), []);   // KW1 + Stok Bebas, dipecah per sumber
    const kw2Internal = state.kw2.internal.map(kw2Row);
    const kw2Supplier = state.kw2.supplier.map(kw2Row);
    // Rate Rp/Kg Hasil Costing per varian BOM yang dipakai laporan ini (dibekukan).
    const rateByBom = {};
    kw1.concat(kw2Internal, kw2Supplier).forEach((r) => {
      if (r.bomUniq && !rateByBom[r.bomUniq]) { const rt = frozenRate(r.bomUniq); if (rt) rateByBom[r.bomUniq] = rt; }
    });

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
      rateByBom,
      selisih,
      form: captureForm(),
      coils: state.coils.filter((c) => c.code).map((c) => ({ source: c.source, material: c.material, nett: num(c.nett) })),
      savedAt: new Date().toISOString(),
    };
  }

  // Isian mentah form (header, scrap/reject, baris coil/FG/KW2/Sisa/Hold) agar Lihat Laporan memuat input terakhir.
  const FORM_FIELDS = ["tglProduksi", "mesin", "helper", "setter", "startTime", "finishTime", "scrapTong", "scrapWrapping", "scrapPisau",
    "rejProdInt", "rejMatInt", "rejKetInt", "rejProdSup", "rejMatSup", "rejKetSup"];
  function captureForm() {
    const fields = {};
    FORM_FIELDS.forEach((id) => { const el = $("#" + id); if (el) fields[id] = el.value; });
    return { fields, seq, state: JSON.parse(JSON.stringify(state)) };
  }
  function restoreForm(form) {
    if (!form || !form.state) return false;
    ["coils", "fg", "sisa", "hold", "confirmations"].forEach((k) => { if (Array.isArray(form.state[k])) state[k] = form.state[k]; });
    if (form.state.claimIn && typeof form.state.claimIn === "object") state.claimIn = form.state.claimIn;
    if (form.state.kw2) state.kw2 = { internal: form.state.kw2.internal || [], supplier: form.state.kw2.supplier || [] };
    if (form.seq > seq) seq = form.seq;
    Object.keys(form.fields || {}).forEach((id) => { const el = $("#" + id); if (el) el.value = form.fields[id]; });
    return true;
  }

  function persistReport() {
    try { M.report.save(spk.no, buildReport()); } catch (e) { /* ignore in mockup */ }
  }

  /* ============================================================
     CLAIM MATERIAL MENYATU DI LAPORAN PRODUKSI (FR-98, merevisi FR-17/FR-75/FR-94/NFR-06)
     Tidak ada lagi popup Hold & Claim. Baris Hold Coil, KW 2 (Supplier), dan Reject (Supplier) otomatis muncul di bagian "Claim Material"
     pada form; Save menyimpan laporan SEKALIGUS membuat/memperbarui claim (satu input, atomik: validasi dulu, baru simpan keduanya).
     Submit dikunci selama ada claim Hold Coil laporan ini yang belum diputuskan Purchasing. Supplier / No. PO / No. Coil Packing List
     ditarik dari database lewat asal coil (CL.ORIGIN = data mockup).
     ============================================================ */
  const CLM = window.CL;
  const hcCoil = (id) => state.coils.find((c) => c.id === id) || null;
  const hcInfo = (r) => {
    const c = hcCoil(r.coilId); const o = c && c.code ? CLM.ORIGIN[c.code] : null;
    return { c, o, material: c ? c.material : "", gudang: c ? c.source : "unpack",
      hargaKg: o ? o.hargaBeli : 0, costbookKg: c ? M.costbookFor(c.material, c.source) : 0, priceUnitPo: o ? o.priceUnitPo : 0 };
  };
  const HC_MAX = 20 * 1024 * 1024;   // batas ukuran bukti per berkas: 20 MB (OQ-C11, keputusan user 2026-10-29); format bebas, termasuk video
  const hcKb = (n) => (n >= 1048576 ? (n / 1048576).toFixed(1) + " MB" : Math.max(1, Math.round(n / 1024)) + " KB");
  const escH = (s) => String(s == null ? "" : s).replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));
  // claim Hold Coil laporan ini yang belum diputuskan Purchasing -> mengunci Submit (FR-18, NFR-06)
  const holdPending = () => CLM.state.claims.filter((c) => c.spk === spk.no && c.asal === "Hold Coil" && !c.decisions.length);

  // Baris claim dari form (urutan: Hold, KW 2 Supplier, Reject Supplier); isian Production (problem/alasan/bukti) disimpan di state.claimIn[key]
  function claimRows() {
    const rows = [];
    const push = (key, o) => {
      const s = state.claimIn[key] || {};
      const ex = rows.find((r) => r.key === key);
      if (ex) { ex.kg += o.kg; return; }                                  // dua baris form untuk coil yang sama dijumlahkan
      rows.push(Object.assign({}, o, { key, problem: s.problem != null ? s.problem : o.problem, alasan: s.alasan != null ? s.alasan : o.alasan,
        bukti: s.bukti || [], coilId: o.fixed ? o.coilId : (s.coilId || o.coilId) }));
    };
    state.hold.forEach((h) => { if (h.coil && num(h.berat) > 0) push("Hold Coil:" + h.coil, { asal: "Hold Coil", coilId: h.coil, fixed: true, kg: num(h.berat), problem: "", alasan: "" }); });
    const kw2 = {};
    state.kw2.supplier.forEach((r) => {
      if (!(num(r.total) > 0)) return;
      const k = r.coil || "";
      kw2[k] = kw2[k] || { kg: 0, ket: [] };
      kw2[k].kg += num(r.total);
      if (r.ket) kw2[k].ket.push(r.ket);
    });
    Object.keys(kw2).forEach((k) => push("KW 2 Supplier:" + k, { asal: "KW 2 Supplier", coilId: k, fixed: true, kg: kw2[k].kg, problem: "KW 2", alasan: [...new Set(kw2[k].ket)].join("; ") }));
    // OQ-C9(a): coil asal Reject terisi otomatis hanya bila SPK memakai tepat satu baby coil; selain itu wajib dipilih eksplisit.
    const withCode = state.coils.filter((c) => c.code);
    const first = withCode.length === 1 ? withCode[0] : null;
    [["rejProdSup", "Reject Product"], ["rejMatSup", "Reject Material"]].forEach(([id, p]) => {
      const kg = num($("#" + id).value);
      if (kg > 0) push("Reject Supplier:" + p, { asal: "Reject Supplier", coilId: first ? first.id : "", fixed: false, kg, problem: p, alasan: $("#rejKetSup").value.trim() });
    });
    return rows;
  }
  // Susun baris claim siap kirim + daftar kesalahan (validasi FR-94: baby coil asal, supplier/PO, Problem, Alasan, bukti)
  function claimPayload(rows) {
    const err = [], items = [];
    rows.forEach((r, i) => {
      const x = hcInfo(r), n = `Baris claim ${i + 1} (${r.asal})`;
      if (!x.c) err.push(`${n}: baby coil asal belum dipilih${r.fixed ? " di form" : ""}.`);
      else if (!x.o) err.push(`${n}: supplier/PO untuk ${x.c.code} belum ada di database Incoming.`);
      if (!r.problem.trim() || !r.alasan.trim()) err.push(`${n}: Problem dan Alasan Produksi wajib diisi.`);
      if (!r.bukti.length) err.push(`${n}: bukti dokumen pendukung wajib diunggah (minimal satu berkas).`);
      if (x.c && x.o) items.push({ key: r.key, asal: r.asal, supplier: x.o.supplier, material: x.material, coil: x.o.packing, po: x.o.po, kg: r.kg, hargaKg: x.hargaKg,
        costbookKg: x.costbookKg, priceUnitPo: x.priceUnitPo, problem: r.problem.trim(), alasan: r.alasan.trim(), babyCoil: x.c.code, gudang: x.gudang, bukti: r.bukti.map((b) => Object.assign({}, b)) });
    });
    return { err, items };
  }

  let claimSig = "", claimCache = [];
  function renderClaims(force) {
    const box = $("#claimBody"); if (!box) return;
    const rows = claimRows();
    const exOf = (r) => { const x = hcInfo(r); return CLM.A.cari(spk.no, { key: r.key, asal: r.asal, babyCoil: x.c ? x.c.code : "", problem: r.problem.trim() }); };
    const sig = JSON.stringify(rows.map((r) => { const e = exOf(r); return [r.key, r.coilId, r.kg, r.bukti.length, e ? e.id + e.status : ""]; }));
    if (!force && sig === claimSig) return;
    claimSig = sig; claimCache = rows;
    $("#claimCount").textContent = rows.length ? rows.length + " baris" : "tidak ada";
    if (!rows.length) { box.innerHTML = '<p class="note" style="margin:0">Tidak ada Hold Coil, KW 2 (Supplier), atau Reject (Supplier) pada laporan ini, jadi tidak ada claim yang dikirim.</p>'; return; }
    const coilSel = (r, i) => r.fixed
      ? (hcCoil(r.coilId) ? hcCoil(r.coilId).code : '<span class="bad">— belum dipilih di form —</span>')
      : `<select data-cl="${i}" data-f="coilId"><option value="">— Baby Coil —</option>${state.coils.filter((c) => c.code).map((c) => `<option value="${c.id}"${c.id === r.coilId ? " selected" : ""}>${c.code}</option>`).join("")}</select>`;
    box.innerHTML = `<div class="table-wrap"><table class="tbl hc-tbl"><thead><tr>
        <th>Asal</th><th>Baby Coil</th><th>Nama Material</th><th>Nama Supplier</th><th>No. PO</th><th>No. Coil Packing List</th><th class="num">Berat (Kg)</th><th class="num">Harga Beli/Kg</th><th class="num">Costbook/Kg</th><th class="num">Price/Unit PO</th><th>Problem</th><th>Alasan Produksi</th><th>Bukti Pendukung</th><th>Status Claim</th></tr></thead><tbody>
      ${rows.map((r, i) => { const x = hcInfo(r), e = exOf(r), lock = !!e && e.status !== "Baru"; return `<tr>
        <td>${r.asal}</td><td>${coilSel(r, i)}</td><td>${x.material || "—"}</td><td>${x.o ? x.o.supplier : "—"}</td><td>${x.o ? x.o.po : "—"}</td><td>${x.o ? x.o.packing : "—"}</td>
        <td class="num">${M.fmtNum(r.kg)}</td><td class="num">${M.fmtNum(x.hargaKg)}</td><td class="num">${M.fmtNum(x.costbookKg)}</td><td class="num">${M.fmtNum(x.priceUnitPo)}</td>
        <td><input type="text" data-cl="${i}" data-f="problem" value="${escH(r.problem)}" placeholder="mis. Karat"${lock ? " disabled" : ""} /></td>
        <td><textarea data-cl="${i}" data-f="alasan" placeholder="Alasan / keterangan dari Produksi"${lock ? " disabled" : ""}>${escH(r.alasan)}</textarea></td>
        <td class="hc-bukti">${r.bukti.map((b, j) => `<div class="hc-file">📎 ${escH(b.nama)} <small>(${hcKb(b.ukuran)})</small>${lock ? "" : ` <a href="#" data-cl-del="${i}:${j}">hapus</a>`}</div>`).join("")}<input type="file" multiple data-cl="${i}" data-f="bukti" /></td>
        <td>${e ? `<strong>${e.id}</strong><br><span class="${lock ? "bad-txt" : ""}">${e.status}${lock ? " — terkunci" : " — dapat diedit"}</span>` : '<span class="muted">Dibuat saat Save</span>'}</td></tr>`; }).join("")}
      </tbody></table></div>`;
  }
  function claimErr(msg) { const el = $("#claimErr"); el.textContent = msg || ""; el.hidden = !msg; if (msg) el.scrollIntoView({ block: "center", behavior: "smooth" }); }
  function claimIn(i) { const r = claimCache[i]; return state.claimIn[r.key] || (state.claimIn[r.key] = {}); }
  $("#claimBody").addEventListener("input", (e) => {
    const t = e.target; if (t.dataset.cl == null || t.type === "file" || t.tagName === "SELECT") return;
    claimIn(+t.dataset.cl)[t.dataset.f] = t.value;
  });
  $("#claimBody").addEventListener("change", (e) => {
    const t = e.target; if (t.dataset.cl == null) return;
    const i = +t.dataset.cl;
    if (t.dataset.f === "coilId") { claimIn(i).coilId = t.value; renderClaims(true); return; }
    if (t.dataset.f !== "bukti") return;
    const all = [...t.files], ok = all.filter((f) => f.size <= HC_MAX), big = all.filter((f) => f.size > HC_MAX), tgt = claimIn(i);
    Promise.all(ok.map(async (f) => ({ nama: f.name, ukuran: f.size, tipe: f.type || "", fid: await CLM.files.put(f) }))).then((list) => {
      tgt.bukti = (tgt.bukti || []).concat(list); renderClaims(true);
      if (big.length) claimErr("Berkas melebihi batas " + (HC_MAX / 1048576) + " MB per berkas dan tidak diunggah: " + big.map((f) => f.name + " (" + hcKb(f.size) + ")").join(", ") + ".");
    });
  });
  $("#claimBody").addEventListener("click", (e) => {
    const d = e.target.closest ? e.target.closest("[data-cl-del]") : null; if (!d) return;
    e.preventDefault(); const [i, j] = d.dataset.clDel.split(":").map(Number); claimIn(i).bukti = (claimIn(i).bukti || []).filter((_, k) => k !== j); renderClaims(true);
  });

  // dialog umum (konfirmasi / ringkasan), memakai #hcModal
  function askDlg(title, html, okText, cancelText) {
    return new Promise((resolve) => {
      const m = $("#hcModal"); m.hidden = false;
      $("#hcBox").innerHTML = `<h3 class="modal__title">${title}</h3><div class="modal__body">${html}</div><div class="modal__actions" style="margin-top:16px">${cancelText ? `<button type="button" class="btn btn--ghost" data-dlg="0">${cancelText}</button>` : ""}<button type="button" class="btn btn--primary" data-dlg="1">${okText || "OK"}</button></div>`;
      m.querySelectorAll("[data-dlg]").forEach((b) => b.addEventListener("click", () => { m.hidden = true; $("#hcBox").innerHTML = ""; resolve(b.dataset.dlg === "1"); }));
    });
  }
  function refreshDocStatus() {
    const done = M.spkStatus.get(spk.no) === "Done"; if (done) return;
    const n = CLM.state.claims.filter((c) => c.spk === spk.no && c.babyCoil).length;
    $("#docStatus").textContent = "Draft (tersimpan)" + (n ? " · " + n + " claim" : "");
  }

  /* Save = simpan laporan + kirim claim (FR-17 revisi, FR-98). Validasi dulu seluruhnya; bila ada yang gagal, tidak ada yang tersimpan (atomik). */
  async function saveReport(quiet) {
    const rows = claimRows(), p = claimPayload(rows);
    if (p.err.length) { claimErr(p.err.join(" ")); toast("Save dibatalkan: isian Claim Material belum lengkap. Laporan belum tersimpan.", "warn"); return false; }
    const meta = { spk: spk.no, tgl: $("#tglProduksi").value || new Date().toISOString().slice(0, 10) };
    const kunci = CLM.A.cekKunci(meta, p.items);
    if (kunci.length) { claimErr(kunci.join(" ")); toast("Save dibatalkan: ada baris claim yang terkunci. Laporan belum tersimpan.", "warn"); return false; }
    claimErr("");
    const hapus = CLM.A.akanDibatalkan(meta, p.items);
    if (hapus.length) {
      const ok = await askDlg("Batalkan claim?", `Baris sumber claim berikut sudah dihapus dari laporan dan claim-nya masih berstatus <strong>Baru</strong>, sehingga akan <strong>dibatalkan</strong>:<ul>${hapus.map((c) => `<li>${c.id} — ${c.asal}, ${c.babyCoil}, ${M.fmtNum(c.kg)} Kg</li>`).join("")}</ul>`, "Ya, simpan dan batalkan claim", "Kembali");
      if (!ok) return false;
    }
    persistReport();
    const res = CLM.A.sinkronDariProduksi(meta, p.items);
    updateHoldNote(); updateGate(); renderClaims(true); refreshDocStatus();
    const bits = [];
    if (res.made.length) bits.push(`${res.made.length} claim dibuat (${res.made.join(", ")})`);
    if (res.updated.length) bits.push(`${res.updated.length} claim diperbarui (${res.updated.join(", ")})`);
    if (res.kept.length) bits.push(`${res.kept.length} claim terkunci tidak berubah (${res.kept.join(", ")})`);
    if (res.removed.length) bits.push(`${res.removed.length} claim dibatalkan (${res.removed.join(", ")})`);
    if (quiet) return true;
    const pend = holdPending();
    await askDlg("Laporan tersimpan", `<p>Laporan Produksi <strong>${spk.no}</strong> tersimpan sebagai <strong>Draft</strong>.</p>${bits.length ? `<p>${bits.join("; ")}.</p>` : "<p>Tidak ada claim yang dikirim.</p>"}${pend.length ? `<p class="note note--warn">SPK tetap terbuka (SPK Sedang Di-Hold). <strong>Submit</strong> terkunci sampai claim Hold Coil ${pend.map((c) => c.id).join(", ")} diputuskan Purchasing; produksi tidak perlu input ulang.</p>` : ""}${res.made.length ? '<p><a href="claim-material.html">Buka Daftar Claim Material</a></p>' : ""}`, "Tutup");
    return true;
  }
  $("#btnDraft").addEventListener("click", () => { saveReport(false); });

  $("#prodForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    // FR-98: Submit lebih dulu menyimpan laporan + claim (sama dengan Save), lalu ditolak bila claim Hold Coil belum diputuskan Purchasing (FR-18, NFR-06).
    if (!(await saveReport(true))) return;
    const pend = holdPending();
    if (pend.length) { toast("Submit ditolak: claim Hold Coil " + pend.map((c) => c.id).join(", ") + " belum diputuskan Purchasing. Laporan dan claim sudah tersimpan.", "warn"); return; }
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

    // Isian disimpan lebih dulu sebagai draft, agar tidak hilang bila konfirmasi selisih dibatalkan.
    persistReport();
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
    // Bila laporan SPK ini pernah disimpan, muat isian terakhir; jika belum, seed dari SPK.
    const saved = M.report.get(spk.no);
    if (saved && saved.form && restoreForm(saved.form)) {
      const done = M.spkStatus.get(spk.no) === "Done";
      $("#docStatus").textContent = done ? "Submitted" : "Draft (tersimpan)";
      if (done) $("#docStatus").className = "badge badge--role";
    } else {
      seedCoil("unpack", "BC-U-1001");
      seedFgFromSpk();
    }
    renderCoils();
    renderFg();

    renderKw2("internal");
    renderKw2("supplier");
    renderLines("sisa");
    renderLines("hold");
    renderAudit();
    recalc();
    renderClaims(true);
    refreshDocStatus();
  }

  // Helper seed: tambah baris coil yang langsung terpilih
  function seedCoil(source, code) {
    if (claimHeld().held.has(code)) return;   // coil yang sedang ditahan claim tidak di-seed (FR-95)
    const data = M.coilByCode(source, code);
    state.coils.push({ id: uid(), source, ...(data || { code: "", material: "", nett: null, gross: null, meter: null, kulit: null, clamp: null }) });
  }

  document.addEventListener("DOMContentLoaded", init);
})();
