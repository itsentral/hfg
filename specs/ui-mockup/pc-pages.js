/* ============================================================
   pc-pages.js — Isi 10 menu Product Costing.
   Urutan & kode UC mengikuti pc-shell.js (MENU). Rumus: pc-data.js.
   ============================================================ */
(() => {
  "use strict";
  const PC = window.PC;
  const { H } = window.PC_SHELL;
  const S = () => PC.state;
  const f = PC.fmt;
  const pctVal = (v) => Number((Number(v) * 100).toFixed(6));
  // Field read-only untuk nilai "otomatis dari master".
  const roInput = (val) => `<input class="pc-in pc-in--ro" type="text" value="${H.esc(val)}" readonly tabindex="-1">`;

  const pages = {};
  window.PC_PAGES = pages;

  const THEAD_ACT = { t: "", w: 44 };

  // Baris form dua kolom (label + field) untuk Detil Input modal-style card.
  const fRow = (l1, f1, l2, f2) =>
    `<div class="pc-form__row">
      <div class="field"><label class="field__label">${l1}</label>${f1}</div>
      ${l2 != null ? `<div class="field"><label class="field__label">${l2}</label>${f2}</div>` : "<div></div>"}
    </div>`;

  // Shortcut buka modal lewat shell.
  const openModal = (cfg) => window.PC_SHELL.openModal(cfg);
  // Tombol aksi baris list standar: View / Edit / Delete.
  const rowActions = (list, i) =>
    `<button type="button" class="btn-icon btn-icon--view" title="View" data-act="view" data-i="${i}">&#128065;</button>`
    + `<button type="button" class="btn-icon btn-icon--edit" title="Edit" data-act="edit" data-i="${i}">&#9998;</button>`
    + H.del(list, i);

  /* ---------- 1. Master Produk (UC-N05, FR-37) ---------- */
  // Form Detil Input (dipakai modal add/edit/view). i = index produk.
  const produkForm = (i) => {
    const p = S().products[i]; if (!p) return "";
    const nameField = `${H.in(`products.${i}.name`, p.name, { cls: "pc-in--w" })}`
      + `<div class="pc-genhint">Otomatis: Product Type + Category + Jenis + Tebal + Lebar + Panjang${p.nameManual ? ' <span class="pc-genhint--manual">(diedit manual)</span>' : ""} · <a class="pc-link" data-act="regenName" data-i="${i}" href="#">Generate ulang</a></div>`;
    return `<div class="pc-form">
      <div class="pc-form__sep">Hierarki Produk</div>
      ${fRow("Product Type (L1) *", H.in(`products.${i}.type`, p.type, { struct: 1 }), "Product Category (L2) *", H.in(`products.${i}.category`, p.category, { struct: 1 }))}
      ${fRow("Product Jenis (L3) *", H.in(`products.${i}.jenis`, p.jenis, { struct: 1 }), "Status", H.sel(`products.${i}.status`, p.status, [{ v: "Aktif", l: "Aktif" }, { v: "Nonaktif", l: "Nonaktif" }]))}
      ${fRow("Product Master (L4) *", nameField, "Kode", H.in(`products.${i}.kode`, p.kode))}
      <div class="pc-form__sep">Kode &amp; Satuan</div>
      ${fRow("Product Code", H.in(`products.${i}.productCode`, p.productCode) + ` <a class="pc-link" data-act="updcode" href="#">Update Code Program</a>`, "Code Master", H.in(`products.${i}.codeMaster`, p.codeMaster))}
      ${fRow("Trade Name", H.in(`products.${i}.tradeName`, p.tradeName), "Product Retail", H.in(`products.${i}.retail`, p.retail))}
      ${fRow("Packing Unit", H.in(`products.${i}.packUnit`, p.packUnit), "Conversion", H.in(`products.${i}.conversion`, p.conversion, { num: 1, step: 1 }))}
      ${fRow("Unit Measurement", H.in(`products.${i}.uom`, p.uom), "MOQ", H.in(`products.${i}.moq`, p.moq, { num: 1, step: 1 }))}
      ${fRow("Maximum Stock", H.in(`products.${i}.maxStock`, p.maxStock, { num: 1, step: 1 }), "Minimum Stock", H.in(`products.${i}.minStock`, p.minStock, { num: 1, step: 1 }))}
      ${fRow("Upload MSDS", H.in(`products.${i}.msds`, p.msds, { cls: "pc-in--w" }), null, "")}
      <div class="pc-form__sep">Dimensi &amp; Berat</div>
      ${fRow("Lebar (mm)", H.in(`products.${i}.lebar`, p.lebar, { num: 1, step: 1, struct: 1 }), "Tebal (mm)", H.in(`products.${i}.tebal`, p.tebal, { num: 1, step: 0.01, struct: 1 }))}
      ${fRow("Panjang (m)", H.in(`products.${i}.panjang`, p.panjang, { num: 1, step: 0.5, struct: 1 }), "Berat/unit (kg)", H.in(`products.${i}.beratUnit`, p.beratUnit, { num: 1, step: 0.01 }))}
      ${fRow("Dimensi P (m)", H.in(`products.${i}.dimP`, p.dimP, { num: 1, step: 0.01 }), "Dimensi L (m)", H.in(`products.${i}.dimL`, p.dimL, { num: 1, step: 0.01 }))}
      ${fRow("Dimensi T (m)", H.in(`products.${i}.dimT`, p.dimT, { num: 1, step: 0.01 }), "CBM", H.in(`products.${i}.cbm`, p.cbm, { num: 1, step: 0.01 }))}
    </div>`;
  };
  const openProduk = (mode, i) => openModal({
    mode, title: (mode === "add" ? "Add" : mode === "edit" ? "Edit" : "View") + " Product Master",
    size: "lg", bodyFn: () => produkForm(i),
    onClose() { if (mode === "add" && !S().products[i].name && !S().products[i].kode) S().products.splice(i, 1); },
  });
  pages.produk = {
    title: "Master Produk",
    hint: "Tampilan Depan: daftar produk (header). Detil Input muncul sebagai jendela saat Add / View / Edit.",
    render() {
      const rows = S().products.map((p, i) => `<tr>
        <td>${i + 1}</td>
        <td>${H.esc(p.kode)}</td>
        <td>${H.esc(p.type)}</td>
        <td>${H.esc(p.category)}</td>
        <td>${H.esc(p.jenis)}</td>
        <td>${H.esc(p.name)}</td>
        <td>${H.esc(p.codeMaster)}</td>
        <td><span class="pc-pill ${p.status === "Aktif" ? "pc-pill--on" : "pc-pill--off"}">${H.esc(p.status || "—")}</span></td>
        <td class="pc-actions">${rowActions("products", i)}</td></tr>`).join("");
      return H.card(1, "Daftar Produk", "Level 1 Product Type · Level 2 Product Category (kunci rate Flow Process & Bahan Pendukung/FOH) · Level 3 Product Jenis · Level 4 Product Master.",
        `<div class="btn-row" style="margin-bottom:12px"><button type="button" class="btn btn--primary" data-act="add">+ Add</button> <button type="button" class="btn btn--ghost" data-act="excel">Excel</button></div>`
        + H.table(["#", "Kode", "Product Type", "Product Category", "Product Jenis", "Product Master", "Code Master", "Status", { t: "Action", w: 120 }], rows));
    },
    onChange(path) {
      const m = /^products\.(\d+)\.(\w+)$/.exec(path);
      if (!m) return;
      const p = S().products[m[1]], field = m[2];
      if (!p) return;
      if (field === "name") { p.nameManual = (p.name || "").trim() !== ""; }          // ketik nama → manual
      else if (["type", "category", "jenis", "tebal", "lebar", "panjang"].includes(field)) PC.applyProductName(p); // field sumber → regen (bila belum manual)
    },
    act: {
      add() {
        S().products.push({ id: PC.uid("PR"), kode: "", status: "Aktif", type: "", category: "", jenis: "", name: "", nameManual: false, codeMaster: "", lebar: 0, tebal: 0, panjang: 6, retail: "", productCode: "", tradeName: "", packUnit: "Roll", conversion: 1, uom: "Meter", maxStock: 0, minStock: 0, moq: 0, msds: "", dimP: 0, dimL: 0, dimT: 0, beratUnit: 0, cbm: 0 });
        openProduk("add", S().products.length - 1);
      },
      edit(d) { openProduk("edit", Number(d.i)); },
      view(d) { openProduk("view", Number(d.i)); },
      regenName(d) { const p = S().products[Number(d.i)]; if (p) { p.nameManual = false; PC.applyProductName(p); window.PC_SHELL.render(); } },
      excel() { window.PC_SHELL.toast("Export Excel — contoh mockup (tidak menghasilkan file).", "ok"); },
      updcode() { window.PC_SHELL.toast("Update Code Program — contoh mockup.", "ok"); },
    },
  };

  /* ---------- 2. Master Material (UC-N06, FR-38) ---------- */
  const materialForm = (i) => {
    const m = S().materials[i]; if (!m) return "";
    const nameField = `${H.in(`materials.${i}.name`, m.name, { cls: "pc-in--w" })}`
      + `<div class="pc-genhint">Otomatis: Metal + Slitted/Non + Boron/Non + Tebal-Lebar-Coating-Tensile${m.nameManual ? ' <span class="pc-genhint--manual">(diedit manual)</span>' : ""} · <a class="pc-link" data-act="regenName" data-i="${i}" href="#">Generate ulang</a></div>`;
    return `<div class="pc-form">
      <div class="pc-form__sep">Klasifikasi</div>
      ${fRow("Metal Type *", H.in(`materials.${i}.metal`, m.metal, { struct: 1 }), "Status", H.sel(`materials.${i}.status`, m.status, [{ v: "Aktif", l: "Aktif" }, { v: "Nonaktif", l: "Nonaktif" }]))}
      ${fRow("Slitted / Mother Coil *", H.in(`materials.${i}.slit`, m.slit, { struct: 1 }), "Boron / Non Boron *", H.in(`materials.${i}.boron`, m.boron, { struct: 1 }))}
      ${fRow("Material Name *", nameField, "Material Code", H.in(`materials.${i}.kode`, m.kode))}
      <div class="pc-form__sep">Spesifikasi</div>
      ${fRow("HS Code", H.in(`materials.${i}.hsCode`, m.hsCode), "Alias Name", H.in(`materials.${i}.alias`, m.alias))}
      ${fRow("Width (mm)", H.in(`materials.${i}.width`, m.width, { num: 1, step: 1, struct: 1 }), "Coating", H.in(`materials.${i}.coating`, m.coating, { struct: 1 }))}
      ${fRow("Thickness (mm)", H.in(`materials.${i}.thickness`, m.thickness, { num: 1, step: 0.01, struct: 1 }), "Color", H.in(`materials.${i}.color`, m.color))}
      ${fRow("Hardness", H.in(`materials.${i}.hardness`, m.hardness), "Tensile", H.in(`materials.${i}.tensile`, m.tensile, { struct: 1 }))}
      ${fRow("No. Accurate", H.in(`materials.${i}.noAccurate`, m.noAccurate), "Berat/Meter (kg/m)", H.in(`materials.${i}.bpm`, m.bpm, { num: 1, step: 0.01 }))}
      <div class="pc-form__sep">Packing &amp; Stok</div>
      ${fRow("Packing Unit", H.in(`materials.${i}.packUnit`, m.packUnit), "Conversion", H.in(`materials.${i}.conversion`, m.conversion, { num: 1, step: 1 }))}
      ${fRow("Unit Measurement", H.in(`materials.${i}.uom`, m.uom), "Upload MSDS", H.in(`materials.${i}.msds`, m.msds, { cls: "pc-in--w" }))}
      ${fRow("Maximum Stock", H.in(`materials.${i}.maxStock`, m.maxStock, { num: 1, step: 1 }), "Minimum Stock", H.in(`materials.${i}.minStock`, m.minStock, { num: 1, step: 1 }))}
      ${fRow("Alternative Supplier", H.in(`materials.${i}.altSupplier`, m.altSupplier, { cls: "pc-in--w" }), null, "")}
    </div>`;
  };
  const openMaterial = (mode, i) => openModal({
    mode, title: (mode === "add" ? "Add" : mode === "edit" ? "Edit" : "View") + " Material Master",
    size: "lg", bodyFn: () => materialForm(i),
    onClose() { if (mode === "add" && !S().materials[i].name) S().materials.splice(i, 1); },
  });
  pages.material = {
    title: "Master Material",
    hint: "Tampilan Depan: daftar material (header). Detil Input muncul sebagai jendela saat Add / View / Edit. Berat/Meter (kg/m) dipakai per varian BOM untuk Material Cost dan Berat per Pcs.",
    render() {
      const rows = S().materials.map((m, i) => `<tr>
        <td>${i + 1}</td>
        <td>${H.esc(m.metal)}</td>
        <td>${H.esc(m.slit)}</td>
        <td>${H.esc(m.boron)}</td>
        <td>${H.esc(m.name)}</td>
        <td><span class="pc-pill ${m.status === "Aktif" ? "pc-pill--on" : "pc-pill--off"}">${H.esc(m.status || "—")}</span></td>
        <td class="pc-actions">${rowActions("materials", i)}</td></tr>`).join("");
      return H.card(1, "Daftar Material", "Metal Type · Slitted/Mother Coil · Boron/Non Boron.",
        `<div class="btn-row" style="margin-bottom:12px"><button type="button" class="btn btn--primary" data-act="add">+ Add</button> <button type="button" class="btn btn--ghost" data-act="excel">Excel</button></div>`
        + H.table(["#", "Metal Type", "Slitted / Mother Coil", "Boron / Non Boron", "Material Name", "Status", { t: "Action", w: 120 }], rows));
    },
    onChange(path) {
      const mm = /^materials\.(\d+)\.(\w+)$/.exec(path);
      if (!mm) return;
      const m = S().materials[mm[1]], field = mm[2];
      if (!m) return;
      if (field === "name") { m.nameManual = (m.name || "").trim() !== ""; }
      else if (["metal", "slit", "boron", "thickness", "width", "coating", "tensile"].includes(field)) PC.applyMaterialName(m);
    },
    act: {
      add() {
        S().materials.push({ id: PC.uid("MT"), kode: "", status: "Aktif", metal: "", slit: "", boron: "", name: "", nameManual: false, hsCode: "", alias: "", width: 1219, coating: "", thickness: 0, color: "", hardness: "", tensile: "", noAccurate: "", packUnit: "Kg", conversion: 1, uom: "Kg", maxStock: 0, minStock: 0, msds: "", altSupplier: "", bpm: 0 });
        openMaterial("add", S().materials.length - 1);
      },
      edit(d) { openMaterial("edit", Number(d.i)); },
      view(d) { openMaterial("view", Number(d.i)); },
      regenName(d) { const m = S().materials[Number(d.i)]; if (m) { m.nameManual = false; PC.applyMaterialName(m); window.PC_SHELL.render(); } },
      excel() { window.PC_SHELL.toast("Export Excel — contoh mockup (tidak menghasilkan file).", "ok"); },
    },
  };

  /* ---------- 3. Kurs (UC-N07, FR-39) ---------- */
  pages.kurs = {
    title: "Kurs",
    hint: "Kurs mengubah Price Reference dari USD ke Rupiah. Last Updated dan Updated By terisi otomatis saat kurs diubah.",
    render() {
      const rows = S().kurs.map((k, i) => `<tr>
        <td>${i + 1}</td>
        <td>${H.in(`kurs.${i}.currency`, k.currency)}</td>
        <td class="num">${H.in(`kurs.${i}.kurs`, k.kurs, { num: 1, step: 1 })}</td>
        <td>${H.calc("upd", i)}</td><td>${H.calc("by", i)}</td><td>${H.del("kurs", i)}</td></tr>`).join("");
      return H.card(1, "Kurs Update", "",
        H.table(["No", "Currency", { t: "Kurs (Rp)", c: "num" }, "Last Updated", "Updated By", THEAD_ACT], rows)
        + `<div class="btn-row" style="margin-top:12px"><button type="button" class="btn btn--soft" data-act="add">+ Tambah Currency</button></div>`);
    },
    onChange(path) {
      const m = /^kurs\.(\d+)\./.exec(path);
      if (m && S().kurs[m[1]]) { S().kurs[m[1]].updated = PC.today(); S().kurs[m[1]].by = "Accounting"; }
    },
    calc: { upd: (i) => (S().kurs[i] || {}).updated || "", by: (i) => (S().kurs[i] || {}).by || "" },
    act: { add() { S().kurs.push({ id: PC.uid("K"), currency: "", kurs: 0, updated: PC.today(), by: "Accounting" }); } },
  };

  /* ---------- 4. Price Reference (UC-N08, FR-40) ---------- */
  // Detil (modal) Price Reference per material: History Pembelian + keputusan pemakaian.
  const priceForm = (mid) => {
    const m = PC.materialById(mid); if (!m) return "";
    if (!S().history[mid]) S().history[mid] = [null, null, null, null, null, null];
    if (!S().priceUse[mid]) S().priceUse[mid] = "terakhir";
    const histRow = `<tr><td>${H.esc(m.name)}</td>
      ${PC.MONTHS.map((_, i) => `<td class="num">${H.in(`history.${mid}.${i}`, S().history[mid][i] == null ? "" : S().history[mid][i], { num: 1, step: 0.001, t: "numnull" })}</td>`).join("")}
      <td class="num"><strong>${H.calc("lat", mid)}</strong></td><td class="num"><strong>${H.calc("avg", mid)}</strong></td></tr>`;
    const refRow = `<tr><td>${H.esc(m.name)}</td>
      <td class="num">${H.calc("lat", mid)}</td><td class="num">${H.calc("avg", mid)}</td>
      <td>${H.sel(`priceUse.${mid}`, S().priceUse[mid], [{ v: "terakhir", l: "Harga Terakhir" }, { v: "rata", l: "Rata-rata" }], { cls: "pc-sel-inline", struct: 1 })}</td>
      <td class="num"><strong>${H.calc("usd", mid)}</strong></td><td class="num">${H.calc("kurs")}</td><td class="num"><strong>${H.calc("idr", mid)}</strong></td></tr>`;
    return `<div class="pc-form">
      <div class="pc-form__row"><div class="field"><label class="field__label">Berlaku s/d (Expired)</label>${H.in(`priceMeta.${mid}.expired`, (S().priceMeta[mid] || {}).expired || "")}</div><div></div></div>
      <div class="pc-form__sep">1 · History Pembelian (USD/kg)</div>
      <p class="muted" style="margin:0 0 8px">Isi harga per bulan; bulan kosong dilewati. Harga Terakhir = bulan terisi paling baru; Rata-rata = rata-rata bulan terisi.</p>
      ${H.table(["Material Name", ...PC.MONTHS.map((x) => ({ t: x, c: "num" })), { t: "Latest Price", c: "num" }, { t: "Average Price", c: "num" }], histRow)}
      <div class="pc-form__sep">2 · Price Reference</div>
      <p class="muted" style="margin:0 0 8px">Keputusan pemakaian dapat diubah per material. Final Price Reference (Rp/Kg) = Final USD × Kurs USD.</p>
      ${H.table(["Material Name", { t: "Harga Terakhir (USD)", c: "num" }, { t: "Harga Rata-rata (USD)", c: "num" }, "Keputusan Pemakaian", { t: "Final (USD/Kg)", c: "num" }, { t: "Kurs USD", c: "num" }, { t: "Final (Rp/Kg)", c: "num" }], refRow)}
    </div>`;
  };
  const openPrice = (mode, mid) => openModal({
    mode, title: (mode === "view" ? "View" : "Edit") + " Price Reference — " + ((PC.materialById(mid) || {}).name || ""),
    size: "lg", bodyFn: () => priceForm(mid),
  });
  pages.price = {
    title: "Price Reference",
    hint: "Tampilan Depan: daftar material dengan Price Reference IDR/USD, Expired & Status (header). View/Edit membuka jendela History Pembelian + keputusan pemakaian.",
    render() {
      S().materials.forEach((m) => {
        if (!S().history[m.id]) S().history[m.id] = [null, null, null, null, null, null];
        if (!S().priceUse[m.id]) S().priceUse[m.id] = "terakhir";
        if (!S().priceMeta[m.id]) S().priceMeta[m.id] = { expired: "", status: "Aktif" };
      });
      const rows = S().materials.map((m, i) => {
        const pr = PC.priceRef(m.id), meta = PC.priceMetaOf(m.id);
        return `<tr>
          <td>${i + 1}</td>
          <td>${H.esc(m.kode || "—")}</td>
          <td>${H.esc(m.name)}</td>
          <td class="num">${f(pr.idr, 0)}</td>
          <td class="num">${f(pr.usd, 2)}</td>
          <td>${H.esc(meta.expired || "—")}</td>
          <td><span class="pc-pill ${meta.status === "Expired" ? "pc-pill--off" : "pc-pill--on"}">${H.esc(meta.status)}</span></td>
          <td class="pc-actions">
            <button type="button" class="btn-icon btn-icon--view" title="View" data-act="view" data-m="${m.id}">&#128065;</button>
            <button type="button" class="btn-icon btn-icon--edit" title="Edit" data-act="edit" data-m="${m.id}">&#9998;</button>
            <button type="button" class="btn-icon" title="Download" data-act="dl" data-m="${m.id}">&#8681;</button>
          </td></tr>`;
      }).join("");
      return H.card(1, "Price Reference &raquo; Raw Materials", "Price Ref dihitung dari History Pembelian × Kurs; buka View/Edit untuk mengubah history & keputusan pemakaian.",
        H.table(["#", "Material Code", "Material Master", { t: "Price Ref IDR", c: "num" }, { t: "Price Ref USD", c: "num" }, "Expired", "Status", { t: "Action", w: 130 }], rows));
    },
    onChange(path) {
      // perubahan expired → status efektif ikut berubah (dihitung di priceMetaOf saat render list)
    },
    calc: {
      lat: (id) => f(PC.priceRef(id).latest, 3),
      avg: (id) => f(PC.priceRef(id).avg, 4),
      usd: (id) => f(PC.priceRef(id).usd, 4),
      idr: (id) => f(PC.priceRef(id).idr, 2),
      kurs: () => f(PC.kursOf("USD"), 0),
    },
    act: {
      view(d) { openPrice("view", d.m); },
      edit(d) { openPrice("edit", d.m); },
      dl() { window.PC_SHELL.toast("Download Price Reference — contoh mockup.", "ok"); },
    },
  };

  /* ---------- 5. BOM Produk (UC-N09, FR-41) ---------- */
  const bomForm = (i) => {
    const b = S().boms[i]; if (!b) return "";
    const prods = S().products.map((p) => ({ v: p.id, l: p.name }));
    // Material yang sudah dipakai BOM lain tidak boleh dipilih ulang (cegah double);
    // material milik baris ini sendiri tetap tersedia.
    const used = new Set(S().boms.filter((x, j) => j !== i && x.materialId).map((x) => x.materialId));
    const avail = S().materials.filter((m) => m.id === b.materialId || !used.has(m.id));
    const mats = [{ v: "", l: "— Pilih Material —" }].concat(avail.map((m) => ({ v: m.id, l: m.name })));
    const p = PC.productById(b.productId), mat = PC.materialById(b.materialId);
    return `<div class="pc-form">
      <div class="pc-form__row">
        <div class="field"><label class="field__label">No. Uniq ID</label>${H.in(`boms.${i}.uniq`, b.uniq)}</div>
        <div class="field"><label class="field__label">Produk FG *</label>${H.sel(`boms.${i}.productId`, b.productId, prods, { cls: "pc-in--w", struct: 1 })}</div>
      </div>
      <div class="pc-form__row">
        <div class="field"><label class="field__label">Trade Name Produk</label>${roInput((p && (p.tradeName || p.name)) || "Otomatis dari master")}</div>
        <div></div>
      </div>
      <div class="pc-form__sep">Daftar Material</div>
      ${H.table(["No", "Material *", "Trade Name", { t: "Qty", c: "num", w: 90 }, { t: "Berat/Meter (kg/m)", c: "num", w: 150 }],
        `<tr><td>1</td>
          <td>${H.sel(`boms.${i}.materialId`, b.materialId, mats, { cls: "pc-in--m", struct: 1 })}</td>
          <td>${roInput((mat && (mat.alias || mat.name)) || "Otomatis")}</td>
          <td class="num">${H.in(`boms.${i}.qty`, b.qty == null ? 1 : b.qty, { num: 1, step: 1, min: 0 })}</td>
          <td class="num">${roInput(mat ? f(mat.bpm) : "—")}</td></tr>`)}
      <p class="muted" style="margin-top:10px">Varian Thickness (dari material): <strong>${mat ? f(mat.thickness) + " mm" : "—"}</strong></p>
    </div>`;
  };
  const openBom = (mode, i) => openModal({
    mode, title: (mode === "add" ? "Buat BOM Baru" : mode === "edit" ? "Edit BOM" : "View BOM"),
    size: "lg", bodyFn: () => bomForm(i),
    onSave() { const b = S().boms[i]; if (b) { b.updated = PC.today(); b.by = "Engineering"; } },
    onClose() { if (mode === "add") { const b = S().boms[i]; if (b && !b.materialId) S().boms.splice(i, 1); } },
  });
  pages.bom = {
    title: "BOM Produk",
    hint: "Tampilan Depan: daftar BOM (header). Buat BOM Baru / View / Edit membuka jendela berisi Produk FG & Daftar Material. Satu produk dapat punya beberapa varian BOM dibedakan ketebalan material.",
    render() {
      const rows = S().boms.map((b, i) => {
        const p = PC.productById(b.productId), mat = PC.materialById(b.materialId);
        return `<tr>
          <td>${i + 1}</td>
          <td>${H.esc((p && p.kode) || "—")}</td>
          <td>${H.esc((p && p.name) || "—")}</td>
          <td>${H.esc((mat && mat.name) || "—")}</td>
          <td>${H.esc(b.uniq)}</td>
          <td class="num">${mat ? 1 : 0}</td>
          <td>${H.esc(b.updated || "—")}</td>
          <td class="pc-actions">${rowActions("boms", i)}</td></tr>`;
      }).join("");
      return H.card(1, "Daftar BOM (Bill of Material)", "",
        `<div class="btn-row" style="margin-bottom:12px"><button type="button" class="btn btn--primary" data-act="add">+ Buat BOM Baru</button></div>`
        + H.table(["No", "Kode Produk", "Base Product", "Base Material", "No. Uniq ID", { t: "Jumlah Material", c: "num" }, "Dibuat", { t: "Aksi", w: 120 }], rows));
    },
    act: {
      add() {
        const p = S().products[0];
        if (!p) { window.PC_SHELL.toast("Tambahkan produk dulu di Master Produk.", "warn"); return; }
        const pn = String(S().products.indexOf(p) + 1).padStart(3, "0");
        const seq = S().boms.filter((b) => b.productId === p.id).length + 1;
        S().boms.push({ id: PC.uid("B"), uniq: `Type ${pn}.${seq}`, productId: p.id, materialId: "", qty: 1, updated: PC.today(), by: "Engineering" });
        openBom("add", S().boms.length - 1);
      },
      edit(d) { openBom("edit", Number(d.i)); },
      view(d) { openBom("view", Number(d.i)); },
    },
  };

  /* ---------- 6. Flow Process & Cycletime (UC-N10, FR-42) ---------- */
  pages.flow = {
    title: "Flow Process &amp; Cycletime",
    hint: "Tampilan Depan: daftar Cycletime per Product Category (header). Add CT / View / Edit membuka jendela berisi flow process (Cost Centre, Process Name, Cycletime, Man Power, Total Man Hour, Machine).",
    render() {
      const cats = PC.categories();
      const front = cats.map((c, i) => {
        const fs = S().flow.filter((x) => x.category === c);
        const lastUpd = fs.map((x) => x.updated).filter(Boolean).sort().slice(-1)[0] || "—";
        const by = (fs.slice(-1)[0] || {}).by || "—";
        return `<tr><td>${i + 1}</td><td>${H.esc(c)}</td>
          <td class="num">${f(fs.reduce((t, x) => t + (Number(x.ct) || 0), 0), 4)}</td>
          <td class="num">${f(PC.totalManHour(c), 6)}</td>
          <td>${H.esc(lastUpd)}</td><td>${H.esc(by)}</td>
          <td class="pc-actions">
            <button type="button" class="btn-icon btn-icon--view" title="View" data-act="view" data-c="${H.esc(c)}">&#128065;</button>
            <button type="button" class="btn-icon btn-icon--edit" title="Edit" data-act="edit" data-c="${H.esc(c)}">&#9998;</button>
          </td></tr>`;
      }).join("");
      return H.card(1, "Daftar Cycletime per Product", "",
        `<div class="btn-row" style="margin-bottom:12px"><button type="button" class="btn btn--primary" data-act="addcat">+ Add CT</button></div>`
        + H.table(["No", "Product (Category)", { t: "Cycletime (mnt)", c: "num" }, { t: "Total Man Hour", c: "num" }, "Last Update", "Update By", { t: "Action", w: 120 }], front));
    },
    onChange(path) {
      const m = /^flow\.(\d+)\./.exec(path);
      if (m && S().flow[m[1]]) { S().flow[m[1]].updated = PC.today(); S().flow[m[1]].by = "admin"; }
    },
    calc: {
      mh: (i) => f(PC.manHour(S().flow[i] || { ct: 0, mp: 0 }), 6),
      tmh: (c) => f(PC.totalManHour(c), 6),
    },
    act: {
      edit(d) { openFlow("edit", d.c); },
      view(d) { openFlow("view", d.c); },
      add(d) { S().flow.push({ id: PC.uid("F"), category: d.c, area: "", process: "", machine: "", ct: 0, mp: 1, updated: PC.today(), by: "admin" }); },
      addcat() {
        const c = prompt("Product Category baru untuk CT:", "");
        if (!c) return;
        if (!S().flow.some((x) => x.category === c)) S().flow.push({ id: PC.uid("F"), category: c, area: "", process: "", machine: "", ct: 0, mp: 1, updated: PC.today(), by: "admin" });
        openFlow("edit", c);
      },
    },
  };
  // Detil flow process (modal) untuk satu Product Category.
  function flowForm(cat) {
    const idx = S().flow.map((x, i) => i).filter((i) => S().flow[i].category === cat);
    const rows = idx.map((i) => { const x = S().flow[i]; return `<tr>
      <td>${H.in(`flow.${i}.area`, x.area)}</td><td>${H.in(`flow.${i}.process`, x.process)}</td>
      <td class="num">${H.in(`flow.${i}.ct`, Number((x.ct || 0).toFixed(6)), { num: 1, step: 0.0001 })}</td>
      <td class="num">${H.in(`flow.${i}.mp`, x.mp, { num: 1, step: 1, min: 0 })}</td>
      <td class="num"><strong>${H.calc("mh", i)}</strong></td>
      <td>${H.in(`flow.${i}.machine`, x.machine)}</td><td>${H.del("flow", i)}</td></tr>`; }).join("");
    const foot = `<tr class="pc-total"><td colspan="4" style="text-align:right">Total Man Hour — ${H.esc(cat)} (jam/meter)</td><td class="num">${H.calc("tmh", cat)}</td><td></td><td></td></tr>`;
    return `<div class="pc-form">
      <div class="pc-form__row"><div class="field"><label class="field__label">Product (Product Category)</label>${roInput(cat)}</div><div></div></div>
      ${H.table(["Cost Centre / Area", "Process Name", { t: "Cycletime (mnt)", c: "num" }, { t: "Man Power", c: "num" }, { t: "Total Man Hour", c: "num" }, "Machine", THEAD_ACT], rows, { foot })}
      <p class="pc-formula">Cost Centre dari Master Cost Centre · Machine dari Master Mesin · Total Man Hour = Cycletime × Man Power ÷ 60. Contoh Excel: mesin 24 m/menit → cycletime 1 ÷ 24 = 0,0417 mnt; 1 orang → 0,000694 jam/meter.</p>
      <div class="btn-row" style="margin-top:12px"><button type="button" class="btn btn--soft" data-act="add" data-c="${H.esc(cat)}">+ Tambah Proses</button></div>`;
  }
  const openFlow = (mode, cat) => openModal({
    mode, title: (mode === "view" ? "View" : "Edit") + " Flow Process & Cycletime — " + cat,
    size: "lg", bodyFn: () => flowForm(cat),
    onSave() { S().flow.filter((x) => x.category === cat).forEach((x) => { x.updated = PC.today(); x.by = "admin"; }); },
  });

  /* ---------- 7. Man Power Rate (UC-N11, FR-43) ---------- */
  const mprGroup = (key, title) => {
    const rows = S().mpr[key].map((x, i) => `<tr>
      <td>${H.in(`mpr.${key}.${i}.n`, x.n, { cls: "pc-in--w" })}</td>
      <td class="num">${H.in(`mpr.${key}.${i}.v`, x.v, { num: 1, step: 0.01 })}</td>
      <td>${H.in(`mpr.${key}.${i}.k`, x.k)}</td><td>${H.del(`mpr.${key}`, i)}</td></tr>`).join("");
    const foot = `<tr class="pc-sub"><td>Subtotal ${title}</td><td class="num">${H.calc("sub", key)}</td><td></td><td></td></tr>`;
    return H.table([title, { t: "Nilai (Rp)", c: "num" }, "Keterangan", THEAD_ACT], rows, { foot })
      + `<div class="btn-row" style="margin:10px 0 18px"><button type="button" class="btn btn--soft" data-act="add" data-g="${key}">+ Tambah komponen ${title}</button></div>`;
  };
  pages.mpr = {
    title: "Man Power Rate",
    hint: "Total biaya direct labor per bulan → Rate Direct Labor (dibulatkan ke atas) → × Faktor Indirect Labor = Rate Man Power per jam. Nilai BPJS diinput manual.",
    render() {
      const cats = PC.categories();
      const ex = cats.map((c) => `<tr><td>${H.esc(c)}</td><td class="num">${f(PC.totalManHour(c), 6)}</td><td class="num">${H.calc("mpc", c)}</td></tr>`).join("");
      return H.card(1, "Komponen Biaya per Bulan", "", mprGroup("salary", "Salary Direct Man Power") + mprGroup("bpjs", "BPJS (input manual)") + mprGroup("lain", "Biaya Lain-Lain"))
        + H.card("", "Parameter & Hasil", "",
          `<div class="pc-kv" style="margin-bottom:14px">
            <div class="field"><label class="field__label">Jam kerja per bulan (parameter)</label>${H.in("mpr.jam", S().mpr.jam, { num: 1, step: 1, min: 1 })}</div>
            <div class="field"><label class="field__label">Faktor Indirect Labor</label>${H.in("mpr.faktor", S().mpr.faktor, { num: 1, step: 0.1, min: 0 })}</div>
          </div>
          <p class="note">${H.calc("jamnote")}</p>
          <div class="table-wrap" style="margin-top:12px"><table class="tbl"><tbody>
            <tr><td>Total Biaya Direct Labor / Bulan</td><td class="num"><strong>${H.calc("total")}</strong></td><td class="muted">Salary + BPJS + Biaya Lain-Lain</td></tr>
            <tr><td>Rate Direct Labor (Rp/jam)</td><td class="num"><strong>${H.calc("rdl")}</strong></td><td class="muted">ROUNDUP(Total ÷ jam kerja per bulan)</td></tr>
            <tr class="pc-total"><td>Rate Man Power (Rp/jam)</td><td class="num">${H.calc("rmp")}</td><td class="muted">Rate Direct Labor × Faktor Indirect Labor</td></tr>
          </tbody></table></div>`)
        + H.card("", "Contoh: Man Power Cost per Product Category", "Man Power Cost (Rp/meter) = Total Man Hour kategori × Rate Man Power.",
          H.table(["Product Category", { t: "Total Man Hour (jam/meter)", c: "num" }, { t: "Man Power Cost (Rp/meter)", c: "num" }], ex));
    },
    calc: {
      sub: (k) => f(PC.sum(S().mpr[k], (x) => x.v)),
      total: () => f(PC.manPowerRate().total),
      rdl: () => f(PC.manPowerRate().rateDL, 0),
      rmp: () => f(PC.manPowerRate().rateMP, 2),
      jamnote: () => `Konstanta yang digunakan: <strong>${f(S().mpr.jam, 0)} jam kerja per bulan</strong> dan Faktor Indirect Labor <strong>${f(S().mpr.faktor, 2)}</strong>. Keduanya adalah parameter, bukan angka tetap.`,
      mpc: (c) => f(PC.totalManHour(c) * PC.manPowerRate().rateMP, 2),
    },
    act: { add(d) { S().mpr[d.g].push({ n: "", v: 0, k: "per bulan" }); } },
  };

  /* ---------- 8. Rate Bahan Pendukung Khusus & FOH (UC-N12, FR-44) ---------- */
  const bpkfohForm = (i) => {
    const r = S().bpkfoh[i]; if (!r) return "";
    return `<div class="pc-form">
      ${fRow("Product Category *", H.in(`bpkfoh.${i}.category`, r.category), null, "")}
      ${fRow("Rate Bahan Pendukung Khusus (Rp/meter)", H.in(`bpkfoh.${i}.bpk`, r.bpk, { num: 1, step: 1 }), "Rate FOH (Rp/meter)", H.in(`bpkfoh.${i}.foh`, r.foh, { num: 1, step: 1 }))}
      <p class="muted" style="margin-top:10px">Perubahan rate tidak mengubah laporan produksi yang sudah dibuat (rate dibekukan saat laporan dibuat).</p>
    </div>`;
  };
  const openBpkfoh = (mode, i) => openModal({
    mode, title: (mode === "add" ? "Add Rate" : mode === "edit" ? "Edit Rate" : "View Rate") + " Bahan Pendukung & FOH",
    bodyFn: () => bpkfohForm(i),
    onClose() { if (mode === "add" && !S().bpkfoh[i].category) S().bpkfoh.splice(i, 1); },
  });
  pages.bpkfoh = {
    title: "Rate Bahan Pendukung Khusus &amp; FOH",
    hint: "Tampilan Depan: daftar rate per Product Category (header). Add Rate / View / Edit membuka jendela isian. Rate dalam Rp/meter.",
    render() {
      const rows = S().bpkfoh.map((r, i) => `<tr>
        <td>${i + 1}</td><td>${H.esc(r.category)}</td>
        <td class="num">${f(r.bpk, 0)}</td>
        <td class="num">${f(r.foh, 0)}</td>
        <td class="pc-actions">${rowActions("bpkfoh", i)}</td></tr>`).join("");
      return H.card(1, "Rate per Product Category", "",
        `<div class="btn-row" style="margin-bottom:12px"><button type="button" class="btn btn--primary" data-act="add">+ Add Rate</button></div>`
        + H.table(["No", "Product Category", { t: "Rate Bahan Pendukung Khusus (Rp/meter)", c: "num" }, { t: "Rate FOH (Rp/meter)", c: "num" }, { t: "Action", w: 120 }], rows));
    },
    act: {
      add() { S().bpkfoh.push({ id: PC.uid("R"), category: "", bpk: 0, foh: 0 }); openBpkfoh("add", S().bpkfoh.length - 1); },
      edit(d) { openBpkfoh("edit", Number(d.i)); },
      view(d) { openBpkfoh("view", Number(d.i)); },
    },
  };

  /* ---------- 9. General Rate (UC-N13, FR-45) ---------- */
  pages.general = {
    title: "General Rate",
    hint: "Berlaku untuk semua produk. Setiap baris punya satuan sendiri. Nama elemen pada grup <strong>Other Cost</strong> dapat diubah (mis. \"Transport\" → \"Logistik\").",
    render() {
      let last = "";
      const rows = S().general.map((g, i) => {
        const head = g.group !== last ? `<tr class="pc-sub"><td colspan="4">${H.esc(g.group)}</td></tr>` : "";
        last = g.group;
        const isPct = g.u === "pct";
        const editable = g.group === "Other Cost"; // hanya Other Cost yang namanya bisa diedit
        const labelCell = editable
          ? H.in(`general.${i}.label`, g.label, { cls: "pc-in--w" })
          : H.esc(g.label);
        return head + `<tr><td>${labelCell}</td><td class="muted">${H.esc(g.basis)}</td>
          <td class="num">${H.in(`general.${i}.v`, isPct ? pctVal(g.v) : g.v, { num: 1, step: isPct ? 0.1 : 1, min: 0, t: isPct ? "pct" : "num" })}</td>
          <td>${isPct ? "%" : "Rp/Kg"}</td></tr>`;
      }).join("");
      return H.card(1, "Rate Umum", "Perubahan rate tidak mengubah laporan produksi yang sudah dibuat (rate dibekukan saat laporan dibuat). Nama elemen Other Cost yang diubah ikut muncul di Simulasi Costing.",
        H.table(["Element Costing", "Rumus / Dasar", { t: "Nilai", c: "num" }, "Satuan"], rows));
    },
  };

  /* ---------- 10. Simulasi Costing & Pricelist (UC-N14, FR-46/FR-47) ---------- */
  let unit = "m";
  // Detil breakdown (modal). focusUniq != null → sorot kolom varian itu; tampil semua kolom.
  function costingDetail(focusUniq) {
    const cs = S().boms.map((b) => PC.costing(b)).filter(Boolean);
    if (!cs.length) return `<p class="empty">Belum ada BOM.</p>`;
    const k = (c) => (unit === "m" ? 1 : 1 / (c.bpm || 1));
    const hot = (c) => (focusUniq && c.bom.uniq === focusUniq ? " pc-colhot" : "");
    const cols = (fn, cls = "") => cs.map((c) => `<td class="num ${cls}${hot(c)}">${fn(c)}</td>`).join("");
    const row = (label, fn, cls = "", extra = "") => `<tr class="${extra}"><td>${label}</td>${cols(fn, cls)}</tr>`;
    const L = (key) => (c) => f(c.lines[key] * k(c));
    const u = unit === "m" ? "Rp/meter" : "Rp/Kg";
    const sec = (t) => `<tr class="pc-sub"><td colspan="${cs.length + 1}">${t}</td></tr>`;
    const body = [
      sec("Dasar perhitungan"),
      `<tr><td>Produk</td>${cs.map((c) => `<td class="${hot(c)}">${H.esc(c.product.name)}</td>`).join("")}</tr>`,
      `<tr><td>Product Category</td>${cs.map((c) => `<td class="${hot(c)}">${H.esc(c.product.category)}</td>`).join("")}</tr>`,
      `<tr><td>Material (varian BOM)</td>${cs.map((c) => `<td class="${hot(c)}">${H.esc(c.material.name)}</td>`).join("")}</tr>`,
      row("Price Reference (USD/Kg) — sesuai keputusan pemakaian", (c) => f(c.pr.usd, 4)),
      row("Kurs USD", (c) => f(c.kurs, 0)),
      row("Price Reference (Rp/Kg)", (c) => f(c.pr.idr)),
      row("Berat/Meter (kg/m)", (c) => f(c.bpm)),
      row("Total Man Hour (jam/meter)", (c) => f(c.totalManHour, 6)),
      row("Rate Man Power (Rp/jam)", (c) => f(c.rateMP)),
      sec(`Biaya (${u})`),
      row("Material Cost", L("material")),
      row("Man Power Cost", L("manPower")),
      row("Bahan Pendukung Khusus", L("bpk")),
      row("FOH", L("foh")),
      row(`Consumables (${pctVal(PC.gen("cons"))}% Material Cost)`, L("cons")),
      row(`Scrap (${pctVal(PC.gen("scrap"))}% Material Cost)`, L("scrap")),
      row(`Biaya Koordinasi (Rp${f(PC.gen("koord"), 0)}/Kg)`, L("koord")),
      row("HPP", L("hpp"), "", "pc-sub"),
      ...PC.OTHER_KEYS.map((key, i) => row(`${H.esc(cs[0] ? cs[0].lines.others[i].l : key)} (${pctVal(PC.gen(key))}% HPP)`, (c) => f(c.lines.others[i].v * k(c)))),
      row("Bottom Price", L("bottom"), "", "pc-bottom"),
      row(`Negotiation Allowance (${pctVal(PC.gen("nego"))}% Bottom Price)`, L("nego")),
      row(`Price List (${u})`, L("priceList"), "", "pc-total"),
      sec("Pricelist Produk"),
      row("Ukuran Produk (meter)", (c) => f(c.ukuran)),
      row("Berat per Pcs (kg) = Berat/Meter × Ukuran", (c) => f(c.beratPcs)),
      row("Pricelist Produk/Meter (= Ukuran × Price List/Meter)", (c) => f(c.pricelistProduk), "pc-calc--strong"),
      row("Pricelist Produk/Kg (= Ukuran × Berat/Meter × Price List/Kg)", (c) => f(c.ukuran * c.bpm * (c.lines.priceList / c.bpm)), "pc-calc--strong"),
      sec("Pembanding"),
      row("Pricelist HFG saat ini (Rp/Meter, contoh)", () => H.in("hfg", S().hfg, { num: 1, step: 100, struct: 1 })),
      row("Selisih Price List/Meter vs HFG", (c) => { const d = c.lines.priceList - (Number(S().hfg) || 0); const p = S().hfg ? (d / S().hfg) * 100 : 0; return `<span class="${d >= 0 ? "pc-up" : "pc-down"}">${d >= 0 ? "+" : ""}${f(d)} (${f(p, 1)}%)</span>`; }),
    ].join("");
    return `<div class="pc-toolbar"><span class="field__label">Tampilkan biaya dalam</span>
        <div class="pc-seg"><button type="button" data-act="unit" data-u="m" class="${unit === "m" ? "on" : ""}">Rp/meter</button><button type="button" data-act="unit" data-u="kg" class="${unit === "kg" ? "on" : ""}">Rp/Kg</button></div></div>
      <div class="table-wrap"><table class="tbl" style="min-width:${300 + cs.length * 240}px"><thead><tr><th>Komponen</th>${cs.map((c) => `<th class="num${hot(c)}">${H.esc(c.bom.uniq)}<br><span class="muted">tebal ${f(c.material.thickness)} mm</span></th>`).join("")}</tr></thead><tbody>${body}</tbody></table></div>
      <p class="pc-formula">HPP = Material + Man Power + Bahan Pendukung + FOH + Consumables + Scrap + Koordinasi · Other Cost = % × HPP · Bottom Price = HPP + Other Cost · Price List = Bottom Price + Negotiation Allowance</p>`;
  }
  const openCostingDetail = (uniq) => openModal({
    mode: "view", title: "Detil Isi dari Costing (Costing Category Product)" + (uniq ? " — " + uniq : ""),
    size: "xl", bodyFn: () => costingDetail(uniq),
  });
  pages.simulasi = {
    title: "Simulasi Costing &amp; Pricelist",
    noSave: true,
    hint: "Tampilan Depan: daftar hasil costing per varian (header). Tombol View membuka jendela berisi detil isi costing category product. Semua angka dihitung dari menu 1–9; hanya baca.",
    render() {
      const cs = S().boms.map((b) => PC.costing(b)).filter(Boolean);
      const front = cs.map((c, i) => `<tr>
        <td>${i + 1}</td>
        <td>${H.esc(c.product.category)}</td>
        <td>${H.esc(c.product.name)}</td>
        <td>${H.esc(c.material.name)}</td>
        <td>${H.esc(c.bom.uniq)}</td>
        <td class="num"><strong>${f(c.hargaMeter)}</strong></td>
        <td class="num"><strong>${f(c.hargaKg)}</strong></td>
        <td>${H.esc(c.lastUpdated)}</td>
        <td class="pc-actions"><a class="pc-link" data-act="view" data-u="${H.esc(c.bom.uniq)}" href="#">View</a> · <a class="pc-link" data-act="ajukan" data-u="${H.esc(c.bom.uniq)}" href="#">Ajukan Pricelist</a></td></tr>`).join("");
      return H.card(1, "Daftar Product Costing", "Harga/Meter &amp; Harga/Kg sesuai perhitungan; Last Updated otomatis dari tanggal update costing.",
        `<div class="pc-toolbar" style="justify-content:space-between">
           <div><button type="button" class="btn btn--primary" data-act="update">Update Costing</button> <button type="button" class="btn btn--ghost" data-act="excel">Download Excel</button></div>
           <div><span class="field__label">Range Date</span> ${H.in("costRange", S().costRange || PC.today())}</div>
         </div>`
        + H.table(["No", "Product Category", "Product Name", "Varian Material", "Uniq ID", { t: "Harga/Meter", c: "num" }, { t: "Harga/Kg", c: "num" }, "Last Updated", { t: "Action", w: 160 }], front));
    },
    act: {
      unit(d) { unit = d.u; },
      view(d) { openCostingDetail(d.u); },
      ajukan(d) { window.PC_SHELL.toast(`Ajukan Pricelist untuk ${d.u} — contoh mockup.`, "ok"); },
      update() { S().boms.forEach((b) => { b.updated = PC.today(); }); PC.save(); window.PC_SHELL.toast("Costing diperbarui (Last Updated = hari ini).", "ok"); },
      excel() { window.PC_SHELL.toast("Download Excel — contoh mockup (tidak menghasilkan file).", "ok"); },
    },
  };
})();
