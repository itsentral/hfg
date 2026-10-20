/* ============================================================
   pc-pages.js — Isi menu Product Costing (Rev.1).
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

  /* ============================================================
     Gaya "seperti aplikasi" untuk Master Produk, Master Material, dan BOM:
     card + toolbar + filter, tabel ala DataTables (Show/Search/Showing/Pagination),
     modal Bootstrap (Save/Cancel), dialog ala SweetAlert (Are you sure? / Success).
     Acuan: application/modules/{product_master,material_master,bom}/views. Hanya TATA LETAK
     yang ditiru; isi field mengikuti Rev.1 (FR-37, FR-38, FR-41, FR-51).
     ============================================================ */
  const ico = H.ico;
  const dlg = (c) => window.PC_SHELL.dialog(c);
  const U = () => S().ui;
  const up = (v) => String(v == null ? "" : v).toUpperCase();
  const uniqList = (arr) => arr.filter((v, i) => v && arr.indexOf(v) === i);
  const clone = (o) => JSON.parse(JSON.stringify(o));
  // opsi <select>: placeholder + daftar (nilai aktif selalu ikut supaya tidak hilang)
  const selOpts = (first, list, cur) => [{ v: "", l: first }].concat((cur && !list.includes(cur) ? list.concat(cur) : list).map((v) => ({ v, l: up(v) })));

  // ---- kontrol DataTables (Show N entries / Search / Showing x to y / Previous 1 2 Next) ----
  const dtSt = (k) => { const d = U().dt; return (d[k] = d[k] || { q: "", n: 10, p: 1 }); };
  function dtView(k, rows, textFn) {
    const st = dtSt(k);
    const q = String(st.q || "").trim().toLowerCase();
    const fl = q ? rows.filter((r) => textFn(r).toLowerCase().includes(q)) : rows;
    const n = Number(st.n) || 10;
    const pages = Math.max(1, Math.ceil(fl.length / n));
    if (st.p > pages) st.p = pages;
    if (st.p < 1) st.p = 1;
    const from = fl.length ? (st.p - 1) * n : 0;
    return { st, rows: fl.slice(from, from + n), total: fl.length, all: rows.length, from, to: Math.min(from + n, fl.length), pages };
  }
  const dtTop = (k, st) => `<div class="ap-dt__top">
    <label>Show <select class="ap-sel ap-sel--sm" data-dtlen="${k}">${[10, 25, 50, 100].map((n) => `<option${Number(st.n) === n ? " selected" : ""}>${n}</option>`).join("")}</select> entries</label>
    <label>Search: <input type="search" class="ap-in ap-in--sm" data-live="${k}" value="${H.esc(st.q)}"></label></div>`;
  const dtBottom = (k, v) => {
    const b = (label, p, dis, on) => `<button type="button" data-act="dtp" data-k="${k}" data-p="${p}"${on ? ' class="is-on"' : ""}${dis ? " disabled" : ""}>${label}</button>`;
    let nums = "";
    for (let i = 1; i <= v.pages; i++) nums += b(i, i, false, i === v.st.p);
    return `<div class="ap-dt__bot"><span>Showing ${v.total ? v.from + 1 : 0} to ${v.to} of ${v.total} entries${v.total !== v.all ? ` (filtered from ${v.all} total entries)` : ""}</span>
      <div class="ap-pg">${b("Previous", v.st.p - 1, v.st.p <= 1)}${nums}${b("Next", v.st.p + 1, v.st.p >= v.pages)}</div></div>`;
  };
  const DT_ACT = { dtp(d) { dtSt(d.k).p = Number(d.p); } };
  document.addEventListener("input", (e) => {
    const t = e.target;
    if (!t.dataset || !t.dataset.live) return;
    const k = t.dataset.live, st = dtSt(k);
    st.q = t.value; st.p = 1;
    window.PC_SHELL.render();
    const el = document.querySelector(`[data-live="${k}"]`);
    if (el) { el.focus(); try { el.setSelectionRange(el.value.length, el.value.length); } catch (_) { /* abaikan */ } }
  });
  document.addEventListener("change", (e) => {
    const t = e.target;
    if (!t.dataset || !t.dataset.dtlen) return;
    const st = dtSt(t.dataset.dtlen);
    st.n = Number(t.value); st.p = 1;
    window.PC_SHELL.render();
  });

  const apTable = (cls, head, body) => `<div class="ap-tablewrap"><table class="ap-table ${cls}"><thead><tr>${head.map((h) => (typeof h === "string" ? `<th>${h}</th>` : `<th class="${h.c || ""}"${h.w ? ` style="width:${h.w}"` : ""}>${h.t}</th>`)).join("")}</tr></thead><tbody>${body}</tbody></table></div>`;
  const statusLabel = (st) => `<label class="ap-label ap-label--${st === "Aktif" ? "success" : "danger"}">${st === "Aktif" ? "Aktif" : "Non Aktif"}</label>`;
  const editOnly = (i) => `<button type="button" class="ap-btn ap-btn--primary ap-btn--sm" title="Edit" data-act="edit" data-i="${i}">${ico("edit")}</button>`;
  const editDel = (i) => `<button type="button" class="ap-btn ap-btn--primary ap-btn--sm" title="Edit" data-act="edit" data-i="${i}">${ico("edit")}</button> <button type="button" class="ap-btn ap-btn--danger ap-btn--sm" title="Delete" data-act="delrow" data-i="${i}">${ico("trash")}</button>`;
  // baris form: label 2 kolom + isian (grid 12 kolom seperti col-md-2 / col-md-4 / col-md-10)
  const lb = (t, req) => `<div class="c2"><label class="ap-lbl">${t}${req ? ' <span class="ap-req">*</span>' : ""}</label></div>`;
  const fld = (cls, html) => `<div class="${cls}">${html}</div>`;
  const apRow = (...cells) => `<div class="ap-row">${cells.join("")}</div>`;
  const apIn = (path, val, o = {}) => H.in(path, val, Object.assign({ cls: "ap-in" }, o));
  const apSel = (path, val, opts, o = {}) => H.sel(path, val, opts, Object.assign({ cls: "ap-sel" }, o));
  const unitList = (cur, base) => selOpts("Select Unit", base, cur);
  const PACK_UNITS = ["Roll", "Pcs", "Box", "Pack", "Kg", "Meter"];
  const UOM_UNITS = ["Meter", "Kg", "Pcs", "Roll", "Lembar"];
  const msdsField = (msds) => `<div class="ap-filebtn"><button type="button" class="ap-btn ap-btn--outline-warning" data-act="msds">Choose file</button><span class="ap-hint">${H.esc(msds || "No file chosen")}</span></div>`;
  const statusRadios = (path, val) => `<div class="ap-radios">${["Aktif", "Nonaktif"].map((v) => `<label><input type="radio" name="${path}" data-path="${path}" data-t="text" value="${v}"${val === v ? " checked" : ""}> ${v === "Aktif" ? "Aktif" : "Non-Aktif"}</label>`).join("")}</div>`;

  /* ---------- 1. Master Produk (UC-01, FR-37) — layout seperti modul product_master ---------- */
  // Master Level 1–3 dikelola modul Product Type / Category / Jenis; di mockup berupa daftar contoh.
  const LV_EXTRA = [["Atap", "Spandek", "Polos"], ["Atap", "Spandek", "Warna"], ["Atap", "BONDECK", "Polos"], ["Atap", "Genteng Metal", "Polos"], ["Rangka", "Hollow", "Galvanis"], ["Baja Ringan", "CANAL C", "C75.70 B"]];
  const lvTree = () => {
    const t = [];
    const add = (a, b, c) => { if (a && b && c && !t.some((x) => x[0] === a && x[1] === b && x[2] === c)) t.push([a, b, c]); };
    S().products.forEach((p) => add(p.type, p.category, p.jenis));
    LV_EXTRA.forEach((x) => add(x[0], x[1], x[2]));
    return t;
  };
  const newProduct = () => ({ id: PC.uid("PR"), kode: "", status: "Aktif", type: "", category: "", jenis: "", name: "", nameManual: false, codeMaster: "", lebar: 0, tebal: 0, panjang: 6, retail: "", productCode: "", tradeName: "", packUnit: "Roll", conversion: 1, uom: "Meter", maxStock: 0, minStock: 0, moq: 0, msds: "", dimP: 0, dimT: 0, beratUnit: 0, cbm: 0 });
  const produkForm = (i) => {
    const p = S().products[i]; if (!p) return "";
    const tree = lvTree();
    const types = uniqList(tree.map((x) => x[0]));
    const cats = uniqList(tree.filter((x) => x[0] === p.type).map((x) => x[1]));
    const jens = uniqList(tree.filter((x) => x[0] === p.type && x[1] === p.category).map((x) => x[2]));
    const isEdit = !!p.kode || !!p._edit;
    return `
      ${apRow(lb("Product Type", 1), fld("c10", apSel(`products.${i}.type`, p.type, selOpts("Select Product Type", types, p.type), { struct: 1 })))}
      ${apRow(lb("Product Category", 1), fld("c10", apSel(`products.${i}.category`, p.category, selOpts("Select Product Category", cats, p.category), { struct: 1 })))}
      ${apRow(lb("Product Jenis", 1), fld("c10", apSel(`products.${i}.jenis`, p.jenis, selOpts(p.category ? "Select Product Jenis" : "List Empty", jens, p.jenis), { struct: 1 })))}
      <hr class="ap-hr">
      ${apRow(lb("Product Master", 1), fld("c10", apIn(`products.${i}.name`, p.name) + `<div class="ap-hint">Otomatis: Product Type + Category + Jenis + Tebal + Lebar + Panjang${p.nameManual ? " (diedit manual)" : ""} · <a data-act="regenName" data-i="${i}" href="#">Generate ulang</a></div>`))}
      ${apRow(lb("Panjang (m)"), fld("c4", apIn(`products.${i}.panjang`, p.panjang, { num: 1, step: 0.5, struct: 1 })), lb("Tebal (mm)"), fld("c4", apIn(`products.${i}.tebal`, p.tebal, { num: 1, step: 0.01, struct: 1 })))}
      ${apRow(lb("Lebar (mm)"), fld("c4", apIn(`products.${i}.lebar`, p.lebar, { num: 1, step: 1, struct: 1 }) + `<div class="ap-hint">Lebar tetap per produk (FR-37); kolom <code>wide</code>, satuan mm; juga dipakai menghitung CBM.</div>`), "<div class=\"c6\"></div>")}
      ${apRow(lb("Product Retail"), fld("c10", apIn(`products.${i}.retail`, p.retail)))}
      <hr class="ap-hr">
      ${apRow(lb("Product Code"), fld("c4", apIn(`products.${i}.productCode`, p.productCode) + `<div class="ap-hint"><a data-act="updcode" href="#">Update Code Program</a></div>`), lb("Trade Name"), fld("c4", apIn(`products.${i}.tradeName`, p.tradeName)))}
      ${apRow(lb("Packing Unit / Conversion"), fld("c2", apSel(`products.${i}.packUnit`, p.packUnit, unitList(p.packUnit, PACK_UNITS))), fld("c2", apIn(`products.${i}.conversion`, p.conversion, { num: 1, step: 1 })), lb("Unit Measurement"), fld("c2", apSel(`products.${i}.uom`, p.uom, unitList(p.uom, UOM_UNITS))))}
      ${apRow(lb("Maximum Stock"), fld("c4", apIn(`products.${i}.maxStock`, p.maxStock, { num: 1, step: 1 })), lb("Minimum Stock"), fld("c4", apIn(`products.${i}.minStock`, p.minStock, { num: 1, step: 1 })))}
      ${apRow(lb("Upload MSDS"), fld("c4", msdsField(p.msds)), lb("MOQ"), fld("c4", apIn(`products.${i}.moq`, p.moq, { num: 1, step: 1 })))}
      <hr class="ap-hr">
      ${apRow(lb("Dimensi (P, T)"), fld("c3", apIn(`products.${i}.dimP`, p.dimP, { num: 1, step: 0.01 })), fld("c3", apIn(`products.${i}.dimT`, p.dimT, { num: 1, step: 0.01 })), `<div class="c6"><div class="ap-hint">Dimensi kemasan (P, T) dalam meter; lebar = field Lebar di atas (kolom wide).</div></div>`)}
      ${apRow(lb("Berat/unit"), fld("c4", apIn(`products.${i}.beratUnit`, p.beratUnit, { num: 1, step: 0.01 })))}
      ${apRow(lb("CBM"), fld("c4", `<div class="ap-in ap-in--ro">${H.calc("cbm", i)}</div>`))}
      ${isEdit ? apRow(lb("Status"), fld("c10", statusRadios(`products.${i}.status`, p.status))) : ""}`;
  };
  const openProduk = (isNew, i) => {
    const snap = isNew ? null : clone(S().products[i]);
    if (!isNew) S().products[i]._edit = true;
    openModal({
      app: true, mode: isNew ? "add" : "edit", title: (isNew ? "Add" : "Edit") + " Product Master",
      bodyFn: () => produkForm(i),
      validate() {
        const p = S().products[i];
        if (!p.type) return "Product type not selected...";
        if (!p.category) return "Product category not selected...";
        if (!p.jenis) return "Product jenis not selected...";
        if (!(p.name || "").trim()) return "Product master name is required...";
        return null;
      },
      onSave() { delete S().products[i]._edit; if (isNew && !S().products[i].kode) S().products[i].kode = "P" + String(Date.now()).slice(-9); },
      doneMsg: "Data Product Master berhasil disimpan.",
      onClose() { if (isNew) S().products.splice(i, 1); else S().products[i] = snap; },
    });
  };
  pages.produk = {
    title: "Master Produk",
    hint: "Daftar produk dengan hierarki Level 1 Product Type · Level 2 Product Category (kunci rate Flow Process, Bahan Pendukung/FOH, Borongan, dan Negotiation Allowance) · Level 3 Product Jenis · Level 4 Product Master. Tata letak mengikuti modul Master Product aplikasi.",
    noSave: true,
    render() {
      const f1 = U().pf, tree = lvTree();
      const fl = S().products.map((p, i) => ({ p, i })).filter(({ p }) => (!f1.t || p.type === f1.t) && (!f1.c || p.category === f1.c) && (!f1.j || p.jenis === f1.j));
      const v = dtView("produk", fl, ({ p }) => [p.kode, p.type, p.category, p.jenis, p.name, p.codeMaster, p.status === "Aktif" ? "aktif" : "non aktif"].join(" "));
      const rows = v.rows.map(({ p, i }, n) => `<tr>
        <td>${v.from + n + 1}</td>
        <td class="ap-c">${H.esc(up(p.kode))}</td>
        <td>${H.esc(up(p.type))}</td><td>${H.esc(up(p.category))}</td><td>${H.esc(up(p.jenis))}</td>
        <td>${H.esc(up(p.name))}</td><td>${H.esc(up(p.codeMaster))}</td>
        <td>${statusLabel(p.status)}</td>
        <td class="ap-c">${editDel(i)}</td></tr>`).join("") || `<tr><td class="ap-empty" colspan="9">No data available in table</td></tr>`;
      return `<div class="ap-card ap-card--shadow">
        <div class="ap-card__head">
          <div class="ap-gap" style="margin-bottom:12px">
            <button type="button" class="ap-btn ap-btn--success" data-act="add">${ico("plus")} Add</button>
            <button type="button" class="ap-btn ap-btn--info" data-act="excel">${ico("excel")} Excel</button>
          </div>
          <div class="ap-filters" style="margin-top:0">
            ${apSel("ui.pf.t", f1.t, selOpts("ALL PRODUCT TYPE", uniqList(tree.map((x) => x[0])), f1.t), { struct: 1 })}
            ${apSel("ui.pf.c", f1.c, selOpts("ALL PRODUCT CATEGORY", uniqList(tree.map((x) => x[1])), f1.c), { struct: 1 })}
            ${apSel("ui.pf.j", f1.j, selOpts("ALL PRODUCT JENIS", uniqList(tree.map((x) => x[2])), f1.j), { struct: 1 })}
          </div>
        </div>
        <div class="ap-card__body">${dtTop("produk", v.st)}
          ${apTable("ap-table--striped ap-table--hover", ["#", { t: "Kode", c: "ap-c" }, "Product Type", "Product Category", "Product Jenis", "Product Master", "Code Master", "Status", { t: "Action", c: "ap-c", w: "90px" }], rows)}
          ${dtBottom("produk", v)}</div></div>`;
    },
    onChange(path) {
      const m = /^products\.(\d+)\.(\w+)$/.exec(path);
      if (!m) return;
      const p = S().products[m[1]], field = m[2];
      if (!p) return;
      if (field === "type") { p.category = ""; p.jenis = ""; }            // cascade Level 1 → 2 → 3 seperti aplikasi
      else if (field === "category") { p.jenis = ""; }
      if (field === "name") { p.nameManual = (p.name || "").trim() !== ""; }
      else if (["type", "category", "jenis", "tebal", "lebar", "panjang"].includes(field)) PC.applyProductName(p);
    },
    calc: { cbm: (i) => { const p = S().products[Number(i)]; return p ? f(p.dimP * ((Number(p.lebar) || 0) / 1000) * p.dimT, 3) : ""; } },
    act: Object.assign({
      add() { S().products.push(newProduct()); openProduk(true, S().products.length - 1); },
      edit(d) { openProduk(false, Number(d.i)); },
      delrow(d) {
        const i = Number(d.i), p = S().products[i];
        if (!p) return;
        if (S().boms.some((b) => b.productId === p.id)) { dlg({ icon: "error", title: "Error", text: "Produk masih dipakai BOM; hapus BOM-nya terlebih dahulu." }); return; }
        dlg({ icon: "warning", title: "Are you sure?", text: "Delete this data", confirmText: "Ya, Hapus!", cancelText: "Batal", danger: true }).then((ok) => {
          if (!ok) return;
          S().products.splice(i, 1); PC.save(); window.PC_SHELL.render();
          dlg({ icon: "success", title: "Success", text: "Data berhasil dihapus." });
        });
      },
      regenName(d) { const p = S().products[Number(d.i)]; if (p) { p.nameManual = false; PC.applyProductName(p); window.PC_SHELL.render(); } },
      excel() { window.PC_SHELL.toast("Export Excel — contoh mockup (tidak menghasilkan file).", "ok"); },
      updcode() { window.PC_SHELL.toast("Update Code Program — contoh mockup.", "ok"); },
      msds() { window.PC_SHELL.toast("Upload MSDS — contoh mockup (tanpa berkas).", "ok"); },
    }, DT_ACT),
  };

  /* ---------- 2. Master Material (UC-02, FR-38) — layout seperti modul material_master ---------- */
  const MAT_EXTRA = { metal: ["Galvalum", "Galvanis", "PPGI"], slit: ["Mother", "Slitted"], boron: ["Boron", "Non Boron"], coating: ["AZ70", "AZ100", "AZ150", "Z120"], tensile: ["G550", "G300", "G350"] };
  const matList = (key, field) => uniqList(S().materials.map((m) => m[field]).concat(MAT_EXTRA[key]));
  const newMaterial = () => ({ id: PC.uid("MT"), kode: "", status: "Aktif", metal: "", slit: "", boron: "", name: "", nameManual: false, hsCode: "", alias: "", width: 1219, coating: "", thickness: 0, color: "", hardness: "", tensile: "", noAccurate: "", packUnit: "Kg", conversion: 1, uom: "Kg", maxStock: 0, minStock: 0, msds: "", altSupplier: "" });
  const materialForm = (i) => {
    const m = S().materials[i]; if (!m) return "";
    const isEdit = !!m.kode || !!m._edit;
    return `
      ${apRow(lb("Metal Type", 1), fld("c10", apSel(`materials.${i}.metal`, m.metal, selOpts("Select Metal Type", matList("metal", "metal"), m.metal), { struct: 1 })))}
      ${apRow(lb("Slithed / Non Slithed", 1), fld("c10", apSel(`materials.${i}.slit`, m.slit, selOpts("Select Slithed / Non Slithed", matList("slit", "slit"), m.slit), { struct: 1 })))}
      ${apRow(lb("Boron / Non Boron", 1), fld("c10", apSel(`materials.${i}.boron`, m.boron, selOpts("Select Boron / Non Boron", matList("boron", "boron"), m.boron), { struct: 1 })))}
      ${apRow(lb("Material Name", 1), fld("c10", apIn(`materials.${i}.name`, m.name) + `<div class="ap-hint">Otomatis: Metal + Slitted/Non + Boron/Non + Tebal-Lebar-Coating-Tensile${m.nameManual ? " (diedit manual)" : ""} · <a data-act="regenName" data-i="${i}" href="#">Generate ulang</a></div>`))}
      <hr class="ap-hr">
      ${apRow(lb("HS Code", 1), fld("c4", apIn(`materials.${i}.hsCode`, m.hsCode)), lb("Alias Name"), fld("c4", apIn(`materials.${i}.alias`, m.alias)))}
      ${apRow(lb("Width (Lebar Coil)", 1), fld("c4", apIn(`materials.${i}.width`, m.width, { num: 1, step: 1, struct: 1 })), lb("Coating", 1), fld("c4", apSel(`materials.${i}.coating`, m.coating, selOpts("Select Coating", matList("coating", "coating"), m.coating), { struct: 1 })))}
      ${apRow(lb("Thickness (Tebal)", 1), fld("c4", apIn(`materials.${i}.thickness`, m.thickness, { num: 1, step: 0.01, struct: 1 })), lb("Color"), fld("c4", apIn(`materials.${i}.color`, m.color)))}
      ${apRow(lb("Hardness", 1), fld("c4", apIn(`materials.${i}.hardness`, m.hardness)), lb("Tensile", 1), fld("c4", apSel(`materials.${i}.tensile`, m.tensile, selOpts("Select Tensile", matList("tensile", "tensile"), m.tensile), { struct: 1 })))}
      ${apRow(lb("No. Accurate", 1), fld("c4", apIn(`materials.${i}.noAccurate`, m.noAccurate)), "<div class=\"c6\"></div>")}
      ${apRow(lb("Packing Unit / Conversion"), fld("c2", apSel(`materials.${i}.packUnit`, m.packUnit, unitList(m.packUnit, PACK_UNITS))), fld("c2", apIn(`materials.${i}.conversion`, m.conversion, { num: 1, step: 1 })), lb("Unit Measurement"), fld("c2", apSel(`materials.${i}.uom`, m.uom, unitList(m.uom, UOM_UNITS))))}
      ${apRow(lb("Maximum Stock", 1), fld("c4", apIn(`materials.${i}.maxStock`, m.maxStock, { num: 1, step: 1 })), lb("Minimum Stock", 1), fld("c4", apIn(`materials.${i}.minStock`, m.minStock, { num: 1, step: 1 })))}
      ${apRow(lb("Upload MSDS"), fld("c10", msdsField(m.msds)))}
      ${apRow(lb("Alternative Supplier"), fld("c10", apIn(`materials.${i}.altSupplier`, m.altSupplier)))}
      ${isEdit ? apRow(lb("Status"), fld("c10", statusRadios(`materials.${i}.status`, m.status))) : ""}`;
  };
  const openMaterial = (isNew, i) => {
    const snap = isNew ? null : clone(S().materials[i]);
    if (!isNew) S().materials[i]._edit = true;
    openModal({
      app: true, mode: isNew ? "add" : "edit", title: "Material Master",
      bodyFn: () => materialForm(i),
      validate() {
        const m = S().materials[i];
        if (!m.metal) return "Metal type not selected...";
        if (!m.slit) return "Slithed / Non Slithed not selected...";
        if (!m.boron) return "Boron / Non Boron not selected...";
        if (!(m.name || "").trim()) return "Material name is required...";
        if (!(Number(m.width) > 0)) return "Width is required...";
        if (!(Number(m.thickness) > 0)) return "Thickness is required...";
        return null;
      },
      onSave() { delete S().materials[i]._edit; if (isNew && !S().materials[i].kode) S().materials[i].kode = "MT" + String(Date.now()).slice(-8); },
      doneMsg: "Data Material Master berhasil disimpan.",
      onClose() { if (isNew) S().materials.splice(i, 1); else S().materials[i] = snap; },
    });
  };
  pages.material = {
    title: "Master Material",
    hint: "Daftar material (Metal Type · Slitted/Mother Coil · Boron/Non Boron). Tebal dan Lebar Coil dipakai BOM; Berat/Meter tidak disimpan di sini, tetapi dihitung otomatis di BOM per varian tebal. Tata letak mengikuti modul Master Material aplikasi.",
    noSave: true,
    render() {
      const f1 = U().mf;
      const fl = S().materials.map((m, i) => ({ m, i })).filter(({ m }) => (!f1.m || m.metal === f1.m) && (!f1.s || m.slit === f1.s) && (!f1.b || m.boron === f1.b));
      const v = dtView("material", fl, ({ m }) => [m.metal, m.slit, m.boron, m.name, m.status === "Aktif" ? "aktif" : "non aktif"].join(" "));
      const rows = v.rows.map(({ m, i }, n) => `<tr>
        <td>${v.from + n + 1}</td>
        <td>${H.esc(up(m.metal))}</td><td>${H.esc(up(m.slit))}</td><td>${H.esc(up(m.boron))}</td>
        <td>${H.esc(up(m.name))}</td>
        <td>${statusLabel(m.status)}</td>
        <td class="ap-c">${editDel(i)}</td></tr>`).join("") || `<tr><td class="ap-empty" colspan="7">No data available in table</td></tr>`;
      return `<div class="ap-card ap-card--shadow">
        <div class="ap-card__head ap-card__head--bar">
          <div class="ap-filters ap-filters--inline">
            ${apSel("ui.mf.m", f1.m, selOpts("METAL TYPE", matList("metal", "metal"), f1.m), { struct: 1 })}
            ${apSel("ui.mf.s", f1.s, selOpts("SLITHED/MOTHER COIL", matList("slit", "slit"), f1.s), { struct: 1 })}
            ${apSel("ui.mf.b", f1.b, selOpts("BORON/NON BORON", matList("boron", "boron"), f1.b), { struct: 1 })}
          </div>
          <div class="ap-gap">
            <button type="button" class="ap-btn ap-btn--success" data-act="add">${ico("plus")} Add</button>
            <button type="button" class="ap-btn ap-btn--info" data-act="excel">${ico("excel")} Excel</button>
          </div>
        </div>
        <div class="ap-card__body">${dtTop("material", v.st)}
          ${apTable("ap-table--striped ap-table--hover", ["#", "Metal Type", "Slitted / Mother Coil", "Boron / Non Boron", "Material Name", "Status", { t: "Action", c: "ap-c", w: "90px" }], rows)}
          ${dtBottom("material", v)}</div></div>`;
    },
    onChange(path) {
      const mm = /^materials\.(\d+)\.(\w+)$/.exec(path);
      if (!mm) return;
      const m = S().materials[mm[1]], field = mm[2];
      if (!m) return;
      if (field === "name") { m.nameManual = (m.name || "").trim() !== ""; }
      else if (["metal", "slit", "boron", "thickness", "width", "coating", "tensile"].includes(field)) PC.applyMaterialName(m);
    },
    act: Object.assign({
      add() { S().materials.push(newMaterial()); openMaterial(true, S().materials.length - 1); },
      edit(d) { openMaterial(false, Number(d.i)); },
      delrow(d) {
        const i = Number(d.i), m = S().materials[i];
        if (!m) return;
        if (S().boms.some((b) => b.materialId === m.id)) { dlg({ icon: "error", title: "Error", text: "Material masih dipakai BOM; ganti material di BOM terlebih dahulu." }); return; }
        dlg({ icon: "warning", title: "Are you sure?", text: "Delete this data", confirmText: "Ya, Hapus!", cancelText: "Batal", danger: true }).then((ok) => {
          if (!ok) return;
          S().materials.splice(i, 1); PC.save(); window.PC_SHELL.render();
          dlg({ icon: "success", title: "Success", text: "Data berhasil dihapus." });
        });
      },
      regenName(d) { const m = S().materials[Number(d.i)]; if (m) { m.nameManual = false; PC.applyMaterialName(m); window.PC_SHELL.render(); } },
      excel() { window.PC_SHELL.toast("Export Excel — contoh mockup (tidak menghasilkan file).", "ok"); },
      msds() { window.PC_SHELL.toast("Upload MSDS — contoh mockup (tanpa berkas).", "ok"); },
    }, DT_ACT),
  };

  /* ============================================================
     Pola bersama layar Master Data & Rate (Standard, Kurs, Price Reference, Flow, Man Power,
     Borongan, BPK/FOH, General Rate, Negotiation, Simulasi, Pricelist, Approval, Catalog).
     Hanya TATA LETAK yang ditiru dari modul aplikasi (mata_uang, price_ref_raw_material,
     master_rate_labor, master_coating, master_forwarding_cost, purchase_request, approval_po,
     closed_po); isi field mengikuti Rev.1. Semua layar: simpan lewat modal + dialog konfirmasi.
     ============================================================ */
  const ro = (v) => `<div class="ap-in ap-in--ro">${H.esc(v == null || v === "" ? "—" : v)}</div>`;
  const hasV = PC.hasVal;
  const pctShow = (v) => (hasV(v) ? f(pctVal(v)) + " %" : '<span class="ap-label ap-label--warning">belum diisi</span>');
  const rpShow = (v, unit) => (hasV(v) ? "Rp " + f(v) + (unit ? " / " + unit : "") : '<span class="ap-label ap-label--warning">belum diisi</span>');
  // isian angka: kosong = null (bukan 0) supaya kelengkapan rate bisa dideteksi
  const numIn = (path, val, o = {}) => apIn(path, val == null ? "" : val, Object.assign({ num: 1, t: "numnull", step: 1, min: 0 }, o));
  const pctIn = (path, val, o = {}) => apIn(path, val == null ? "" : pctVal(val), Object.assign({ num: 1, t: "pctnull", step: 0.1, min: 0 }, o));
  const nonNeg = (v) => v == null || v === "" || Number(v) >= 0;
  const productCats = () => uniqList(S().products.map((p) => p.category).filter(Boolean));
  const catOpts = (cur) => selOpts("Select Product Category", productCats(), cur);

  // kepala kartu seperti modul mata_uang: judul + keterangan + lencana total + tombol
  const apHead = (title, sub, badge, btns) => `<div class="ap-card__head ap-card__head--bar"><div><h5>${title}</h5>${sub ? `<div class="ap-hint">${sub}</div>` : ""}</div>
    <div class="ap-gap">${badge ? `<span class="ap-badge ap-badge--light">${badge}</span>` : ""}${btns || ""}</div></div>`;
  const btnAdd = (label) => `<button type="button" class="ap-btn ap-btn--primary" data-act="add">${ico("plus")} ${label || "Add"}</button>`;
  const iconBtn = (act, title, svg, cls, attrs) => `<button type="button" class="ap-btn ap-btn--${cls} ap-btn--sm" title="${title}" data-act="${act}" ${attrs || ""}>${ico(svg)}</button>`;
  const viewEdit = (attrs, withEdit = true) => iconBtn("view", "View", "eye", "info", attrs) + (withEdit ? " " + iconBtn("edit", "Edit", "edit", "primary", attrs) : "");

  // peringatan kelengkapan: pesan + menu tempat mengisinya (tautan)
  const gapItem = (g) => `<li>${H.esc(g.text)} Isi di menu <a class="ap-link" href="${g.file}">${H.esc(g.label)}</a>.</li>`;
  const warnBox = (title, items) => `<div class="ap-warn" role="alert"><strong>&#9888; ${title}</strong><ul>${items}</ul></div>`;
  const uniqGaps = (list) => { const seen = {}; const out = []; list.forEach((g) => { if (!seen[g.text]) { seen[g.text] = 1; out.push(g); } }); return out; };
  const gapsLabel = (c) => (c.lengkap
    ? `<span class="ap-label ap-label--success">Lengkap</span>`
    : `<span class="ap-label ap-label--warning" title="${H.esc(c.kekurangan.map((g) => g.text).join(" "))}">&#9888; ${c.kekurangan.length} kekurangan</span>`);
  // peringatan khusus satu kunci menu (dipakai di halaman master terkait)
  const gapBanner = (keys, title) => {
    const all = [];
    S().boms.forEach((b) => { const c = PC.costing(b); if (c) c.kekurangan.filter((g) => keys.includes(g.key)).forEach((g) => all.push(g)); });
    const u = uniqGaps(all);
    return u.length ? warnBox(title || "Data belum lengkap — memengaruhi Simulasi Costing dan Pricelist", u.map((g) => `<li>${H.esc(g.text)}</li>`).join("")) : "";
  };
  const confirmDel = (cfg) => dlg({ icon: "warning", title: "Are you sure?", text: cfg.text || "Delete this data", confirmText: "Ya, Hapus!", cancelText: "Batal", danger: true });
  const doneDel = () => dlg({ icon: "success", title: "Success", text: "Data berhasil dihapus." });

  // Fabrik layar master sederhana: kepala kartu + tabel DataTables + modal Add/Edit + hapus (konfirmasi).
  function masterPage(cfg) {
    const rows = () => S()[cfg.key];
    const open = (isNew, i) => {
      const snap = isNew ? null : clone(rows()[i]);
      openModal({
        app: true, mode: isNew ? "add" : "edit", title: (isNew ? "Add " : "Edit ") + cfg.modalTitle, cls: cfg.modalCls || "", size: cfg.size || "lg",
        bodyFn: () => cfg.form(i, isNew),
        validate() { return cfg.validate(rows()[i], i); },
        onSave() { const r = rows()[i]; if (cfg.beforeSave) cfg.beforeSave(r); if (cfg.stamp) { r.updated = PC.today(); r.by = cfg.stamp; } },
        doneMsg: cfg.doneMsg || "Data berhasil disimpan.",
        onClose() { if (isNew) rows().splice(i, 1); else rows()[i] = snap; },
      });
    };
    return {
      title: cfg.title, hint: cfg.hint, noSave: true,
      render() {
        const v = dtView(cfg.key, rows().map((r, i) => ({ r, i })), ({ r }) => cfg.search(r));
        const body = v.rows.map(({ r, i }, n) => `<tr><td class="ap-c">${v.from + n + 1}</td>${cfg.cols.map((c) => `<td class="${c.c || ""}">${c.fn(r, i)}</td>`).join("")}
          <td class="ap-c">${cfg.noDel ? editOnly(i) : editDel(i)}</td></tr>`).join("") || `<tr><td class="ap-empty" colspan="${cfg.cols.length + 2}">No data available in table</td></tr>`;
        return (cfg.banner ? cfg.banner() : "") + `<div class="ap-card ap-card--shadow">${apHead(cfg.headTitle, cfg.headSub, `Total: ${rows().length} ${cfg.unit}`, cfg.noAdd ? "" : btnAdd(cfg.addLabel))}
          <div class="ap-card__body">${dtTop(cfg.key, v.st)}
            ${apTable("ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }].concat(cfg.cols.map((c) => ({ t: c.h, c: c.c }))).concat([{ t: "Action", c: "ap-c", w: "90px" }]), body)}
            ${dtBottom(cfg.key, v)}</div></div>` + (cfg.after ? cfg.after() : "");
      },
      calc: cfg.calc || {},
      onChange: cfg.onChange,
      act: Object.assign({
        add() { rows().push(cfg.blank()); open(true, rows().length - 1); },
        edit(d) { open(false, Number(d.i)); },
        delrow(d) {
          const i = Number(d.i), r = rows()[i];
          if (!r) return;
          const used = cfg.delCheck ? cfg.delCheck(r) : null;
          if (used) { dlg({ icon: "error", title: "Error", text: used }); return; }
          confirmDel({}).then((ok) => { if (!ok) return; rows().splice(i, 1); PC.save(); window.PC_SHELL.render(); doneDel(); });
        },
      }, DT_ACT),
    };
  }
  const stampCols = (r) => `<td>${H.esc(r.updated || "—")}</td><td>${H.esc(r.by || "—")}</td>`;

  /* ---------- 3. Master Standard Material (UC-03, FR-50) — pola form satu baris (cf. master_coating form add) ---------- */
  // Global: SATU set nilai untuk seluruh material. Tanpa satuan (klien belum menginformasikan).
  // Isian bekerja pada salinan (draft); nilai baru berlaku setelah Save + konfirmasi.
  const stdDraft = () => { if (!U().stdDraft) U().stdDraft = { toleransi: S().standard.toleransi, bj: S().standard.bj, coating: S().standard.coating }; return U().stdDraft; };
  pages.standard = {
    title: "Master Standard Material",
    noSave: true,
    hint: "Satu set nilai standar yang berlaku untuk seluruh material (global). Dipakai BOM untuk menghitung Berat/Meter otomatis. Satuan tidak ditampilkan karena belum diinformasikan klien.",
    render() {
      const st = S().standard, d = stdDraft();
      const changed = ["toleransi", "bj", "coating"].some((k) => String(d[k]) !== String(st[k]));
      const bad = S().materials.filter((m) => !(PC.bpmOf(m) > 0));
      const ex = S().materials.map((m, i) => `<tr><td class="ap-c">${i + 1}</td><td>${H.esc(up(m.name))}</td><td class="ap-r">${f(m.thickness)}</td><td class="ap-r">${f(m.width)}</td><td class="ap-r"><strong>${f(PC.bpmOf(m))}</strong></td></tr>`).join("");
      return (bad.length ? warnBox("Berat/Meter ≤ 0 — Simulasi Costing tidak lengkap", bad.map((m) => `<li>Berat/Meter material ${H.esc(m.name)} ≤ 0; periksa nilai standar di bawah serta Tebal dan Lebar Coil di <a class="ap-link" href="pc-material.html">Master Material</a>.</li>`).join("")) : "")
        + `<div class="ap-card ap-card--shadow">
          ${apHead("Standar Material (Global)", "Perubahan nilai langsung mengubah Berat/Meter semua varian BOM (tabel contoh di bawah).", "", "")}
          <div class="ap-card__body">
            ${apRow(lb("Standar Toleransi", 1), fld("c4", numIn("ui.stdDraft.toleransi", d.toleransi, { step: 0.01 })), "<div class=\"c6\"></div>")}
            ${apRow(lb("Standar Berat Jenis Material Baja", 1), fld("c4", numIn("ui.stdDraft.bj", d.bj, { step: 0.01 })), "<div class=\"c6\"></div>")}
            ${apRow(lb("Coating", 1), fld("c4", numIn("ui.stdDraft.coating", d.coating, { step: 0.01 })), "<div class=\"c6\"></div>")}
            <hr class="ap-hr">
            ${apRow(lb("Last Updated"), fld("c4", ro(st.updated)), lb("Updated By"), fld("c4", ro(st.by)))}
            <div class="ap-hint">Berat/Meter = ((Tebal Material − Standar Toleransi) × Lebar Coil × Standar Berat Jenis) + Coating</div>
            <div class="ap-gap" style="margin-top:14px">
              <button type="button" class="ap-btn ap-btn--primary" data-act="stdsave">${ico("save")} Save</button>
              <button type="button" class="ap-btn ap-btn--secondary" data-act="stdreset"${changed ? "" : " disabled"}>${ico("x")} Batal Ubah</button>
              ${changed ? '<span class="ap-label ap-label--warning">Perubahan belum disimpan</span>' : ""}
            </div>
          </div></div>`
        + `<div class="ap-card ap-card--shadow">${apHead("Contoh dampak: Berat/Meter per material", "Hanya baca. Tebal dan Lebar Coil dari Master Material.", "", "")}
          <div class="ap-card__body">${apTable("ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "Material", { t: "Tebal", c: "ap-r" }, { t: "Lebar Coil", c: "ap-r" }, { t: "Berat/Meter (kg/m)", c: "ap-r" }], ex)}</div></div>`;
    },
    act: {
      stdreset() { U().stdDraft = null; },
      stdsave() {
        const d = stdDraft();
        const names = { toleransi: "Standar Toleransi", bj: "Standar Berat Jenis", coating: "Coating" };
        for (const k of ["toleransi", "bj", "coating"]) {
          if (!hasV(d[k])) { dlg({ icon: "warning", title: "Error Message!", text: names[k] + " wajib diisi." }); return; }
          if (Number(d[k]) < 0) { dlg({ icon: "warning", title: "Error Message!", text: names[k] + " tidak boleh negatif." }); return; }
        }
        if (!(Number(d.bj) > 0)) { dlg({ icon: "warning", title: "Error Message!", text: "Standar Berat Jenis harus lebih besar dari 0." }); return; }
        dlg({ icon: "warning", title: "Are you sure?", text: "Nilai ini mengubah Berat/Meter semua varian BOM.", confirmText: "Ya, Simpan!", cancelText: "Batal" }).then((ok) => {
          if (!ok) return;
          Object.assign(S().standard, { toleransi: Number(d.toleransi), bj: Number(d.bj), coating: Number(d.coating), updated: PC.today(), by: "Engineering" });
          U().stdDraft = null; PC.save(); window.PC_SHELL.render();
          dlg({ icon: "success", title: "Success", text: "Master Standard Material berhasil disimpan." });
        });
      },
    },
  };

  /* ---------- 4. Kurs (UC-06, FR-39) — layout seperti modul mata_uang (kepala bertotal, modal berkepala biru) ---------- */
  pages.kurs = masterPage({
    key: "kurs", title: "Kurs", unit: "Mata Uang", modalTitle: "Kurs", modalCls: "ap-modal--hdr-primary", size: "lg", addLabel: "Tambah Mata Uang", stamp: "Finance",
    hint: "Finance memperbarui Kurs; Kurs mengubah Price Reference dari USD ke Rupiah. Last Updated dan Updated By terisi otomatis saat kurs diubah.",
    headTitle: "Master Kurs", headSub: "Kelola nilai tukar mata uang terhadap Rupiah (dipakai Price Reference)",
    banner: () => (PC.kursOf("USD") > 0 ? "" : warnBox("Kurs USD belum diisi — Material Cost dihitung 0", `<li>Isi Kurs USD pada daftar di bawah.</li>`)),
    cols: [
      { h: "Currency", fn: (r) => `<span class="ap-badge ap-badge--blue">${H.esc(up(r.currency) || "—")}</span>` },
      { h: "Kurs (Rp)", c: "ap-r", fn: (r) => rpShow(r.kurs) },
      { h: "Last Updated", fn: (r) => H.esc(r.updated || "—") },
      { h: "Updated By", fn: (r) => H.esc(r.by || "—") },
    ],
    search: (r) => [r.currency, r.kurs, r.updated, r.by].join(" "),
    blank: () => ({ id: PC.uid("K"), currency: "", kurs: null, updated: PC.today(), by: "Finance" }),
    form: (i) => { const k = S().kurs[i]; return `
      ${apRow(lb("Currency", 1), fld("c10", apIn(`kurs.${i}.currency`, k.currency) + `<div class="ap-hint">Kode mata uang, mis. USD.</div>`))}
      ${apRow(lb("Kurs (Rp)", 1), fld("c10", numIn(`kurs.${i}.kurs`, k.kurs, { step: 1 }) + `<div class="ap-hint">Nilai tukar 1 satuan mata uang dalam Rupiah.</div>`))}
      ${apRow(lb("Last Updated"), fld("c4", ro(k.updated)), lb("Updated By"), fld("c4", ro(k.by)))}`; },
    validate: (r, i) => {
      if (!(r.currency || "").trim()) return "Currency wajib diisi.";
      if (S().kurs.some((x, j) => j !== i && up(x.currency).trim() === up(r.currency).trim())) return "Currency " + up(r.currency).trim() + " sudah ada.";
      if (!hasV(r.kurs)) return "Kurs wajib diisi.";
      if (Number(r.kurs) <= 0) return "Kurs harus lebih besar dari 0.";
      return null;
    },
    beforeSave: (r) => { r.currency = up(r.currency).trim(); },
    delCheck: (r) => (up(r.currency) === "USD" ? "Kurs USD dipakai Price Reference; tidak dapat dihapus." : null),
  });

  /* ---------- 5. Price Reference (UC-06, FR-52) — layout seperti modul price_ref_raw_material ---------- */
  // Hanya nilai MAX History Pembelian yang dipakai; terakhir/rata-rata hanya informasi.
  const priceBody = (mid, view) => {
    const m = PC.materialById(mid); if (!m) return "";
    if (!S().history[mid]) S().history[mid] = [null, null, null, null, null, null];
    if (!S().priceMeta[mid]) S().priceMeta[mid] = { expired: "", status: "Aktif" };
    const meta = PC.priceMetaOf(mid);
    const histRow = `<tr><td>${H.esc(up(m.name))}</td>
      ${PC.MONTHS.map((_, i) => `<td class="ap-r">${H.in(`history.${mid}.${i}`, S().history[mid][i] == null ? "" : S().history[mid][i], { num: 1, step: 0.001, min: 0, t: "numnull", cls: "ap-in" })}</td>`).join("")}
      <td class="ap-r">${H.calc("lat", mid)}</td><td class="ap-r">${H.calc("avg", mid)}</td><td class="ap-r"><strong>${H.calc("max", mid)}</strong></td></tr>`;
    const refRow = `<tr><td>${H.esc(up(m.name))}</td><td class="ap-r"><strong>${H.calc("max", mid)}</strong></td><td class="ap-r">${H.calc("kurs")}</td><td class="ap-r"><strong>${H.calc("idr", mid)}</strong></td></tr>`;
    return `${meta.label === "Not Set" ? '<div class="ap-alert ap-alert--danger">Price Reference belum ada: isi minimal satu harga pada History Pembelian.</div>' : ""}
      ${apRow(lb("Material ID"), fld("c4", ro(m.kode)), lb("Status"), fld("c4", `<div class="ap-in ap-in--ro">${priceStatusLabel(meta.label)}</div>`))}
      ${apRow(lb("Material Master"), fld("c10", ro(up(m.name))))}
      ${apRow(lb("Berlaku s/d (Expired)"), fld("c4", view ? ro(meta.expired) : H.in(`priceMeta.${mid}.expired`, (S().priceMeta[mid] || {}).expired || "", { cls: "ap-in", date: 1 })), "<div class=\"c6\"></div>")}
      <hr class="ap-hr">
      <h6>History Pembelian (USD/kg)</h6>
      <div class="ap-hint" style="margin-bottom:8px">Isi harga per bulan dari History Pembelian; bulan kosong dilewati. Yang dipakai costing adalah harga <strong>tertinggi (Max)</strong>; kolom Terakhir dan Rata-rata hanya informasi.</div>
      ${apTable("ap-table--bordered ap-table--sm", ["Material Name", ...PC.MONTHS.map((x) => ({ t: x, c: "ap-r" })), { t: "Terakhir (info)", c: "ap-r" }, { t: "Rata-rata (info)", c: "ap-r" }, { t: "Max (dipakai)", c: "ap-r" }], histRow)}
      <h6 style="margin-top:14px">Price Reference</h6>
      <div class="ap-hint" style="margin-bottom:8px">Price Reference (Rp/Kg) = Max (USD/Kg) × Kurs USD.</div>
      ${apTable("ap-table--bordered ap-table--sm", ["Material Name", { t: "Price Reference Max (USD/Kg)", c: "ap-r" }, { t: "Kurs USD (Rp)", c: "ap-r" }, { t: "Price Reference (Rp/Kg)", c: "ap-r" }], refRow)}`;
  };
  const priceStatusLabel = (l) => `<label class="ap-label ap-label--${l === "Oke" ? "success" : l === "Expired" ? "danger" : "warning"}">${l}</label>`;
  const openPrice = (mode, mid) => {
    const snapH = clone(S().history[mid] || []), snapM = clone(S().priceMeta[mid] || {});
    const view = mode === "view";
    openModal({
      app: true, mode, title: (view ? "View" : "Edit") + " Price Reference — " + up((PC.materialById(mid) || {}).name), size: "xl",
      hideSave: view, cancelLabel: view ? "Close" : "Cancel",
      bodyFn: () => priceBody(mid, view),
      after() { if (view) document.querySelectorAll("#pcModal input").forEach((el) => el.setAttribute("disabled", "")); },
      validate() { return S().history[mid].some((v) => v !== null && Number(v) < 0) ? "Harga History Pembelian tidak boleh negatif." : null; },
      doneMsg: "Price Reference berhasil disimpan.",
      onClose() { S().history[mid] = snapH; S().priceMeta[mid] = snapM; },
    });
  };
  pages.price = {
    title: "Price Reference",
    noSave: true,
    hint: "Finance memperbarui Price Reference USD/Kg dari History Pembelian (nilai tertinggi), lalu sistem menghitung Rp/Kg dengan Kurs. Tampilan depan: daftar material; View/Edit membuka jendela History Pembelian.",
    render() {
      S().materials.forEach((m) => {
        if (!S().history[m.id]) S().history[m.id] = [null, null, null, null, null, null];
        if (!S().priceMeta[m.id]) S().priceMeta[m.id] = { expired: "", status: "Aktif" };
      });
      const v = dtView("price", S().materials.map((m, i) => ({ m, i })), ({ m }) => [m.kode, m.name].join(" "));
      const rows = v.rows.map(({ m }, n) => {
        const pr = PC.priceRef(m.id), meta = PC.priceMetaOf(m.id);
        return `<tr><td class="ap-c">${v.from + n + 1}</td><td>${H.esc(up(m.kode || "—"))}</td><td>${H.esc(up(m.name))}</td>
          <td class="ap-r">${PC.hasHistory(m.id) ? f(pr.idr) : "—"}</td><td class="ap-r">${PC.hasHistory(m.id) ? f(pr.usd, 2) : "—"}</td>
          <td class="ap-c">${H.esc(meta.expired || "—")}</td><td>${priceStatusLabel(meta.label)}</td>
          <td class="ap-c">${viewEdit(`data-m="${m.id}"`)}</td></tr>`;
      }).join("") || `<tr><td class="ap-empty" colspan="8">No data available in table</td></tr>`;
      return gapBanner(["price", "kurs"], "Price Reference belum lengkap — memengaruhi Simulasi Costing dan Pricelist")
        + `<div class="ap-card ap-card--shadow"><div class="ap-card__body">${dtTop("price", v.st)}
          ${apTable("ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "Material Code", "Material Master", { t: "Price Ref IDR (Rp/Kg)", c: "ap-r" }, { t: "Price Ref USD (Max)", c: "ap-r" }, { t: "Expired", c: "ap-c" }, "Status", { t: "Action", c: "ap-c", w: "100px" }], rows)}
          ${dtBottom("price", v)}</div></div>`;
    },
    calc: {
      lat: (id) => f(PC.priceRef(id).latest, 3),
      avg: (id) => f(PC.priceRef(id).avg, 4),
      max: (id) => f(PC.priceRef(id).max, 4),
      idr: (id) => f(PC.priceRef(id).idr),
      kurs: () => f(PC.kursOf("USD")),
    },
    act: Object.assign({
      view(d) { openPrice("view", d.m); },
      edit(d) { openPrice("edit", d.m); },
    }, DT_ACT),
  };

  const matLabel = (m) => `${m.kode} — ${m.name}`;
  /* ---------- 6. BOM Produk per Varian Tebal (UC-04, FR-41, FR-51) — layout seperti modul bom ---------- */
  // Seperti aplikasi: daftar BOM (satu baris per produk), halaman form terpisah (Buat BOM Baru / Edit BOM),
  // dan halaman Detail BOM. Beda dari aplikasi karena Rev.1: baris detail = VARIAN TEBAL (satu material per
  // varian, No. Uniq ID, Berat/Meter terhitung otomatis) — tanpa kolom Qty.
  const BV = () => U().bomv;
  const bomOf = (pid) => S().boms.filter((b) => b.productId === pid);
  const bomProducts = () => { const out = []; S().boms.forEach((b) => { if (!out.includes(b.productId)) out.push(b.productId); }); return out; };
  const prodLabel = (p) => `${p.kode} — ${p.name}` + ([p.type, p.category, p.jenis].filter(Boolean).length ? ` (${[p.type, p.category, p.jenis].filter(Boolean).join(" / ")})` : "");
  const prodNo = (pid) => String(S().products.findIndex((p) => p.id === pid) + 1).padStart(3, "0");
  // No. Uniq ID berikutnya: nomor urut terbesar (draft + BOM tersimpan produk itu) + 1, supaya tidak pernah dipakai ulang.
  const nextUniq = (pid, rows) => {
    const seqOf = (u) => { const m = /\.(\d+)$/.exec(u || ""); return m ? Number(m[1]) : 0; };
    const mx = Math.max(0, ...rows.map((r) => seqOf(r.uniq)), ...bomOf(pid).map((b) => seqOf(b.uniq)));
    return `Type ${prodNo(pid)}.${mx + 1}`;
  };
  const bomLatest = (pid) => bomOf(pid).map((b) => b.updated || "").sort().pop() || "—";
  const bomHead = (title, right) => `<div class="ap-card__head ap-card__head--bar"><h5>${ico("sitemap")} ${title}</h5>${right}</div>`;
  const bomKeyVal = (k, v) => `<tr><td class="fw" style="width:160px;font-weight:600">${k}</td><td>: ${v}</td></tr>`;

  function bomList() {
    const flash = U().flash; U().flash = null;
    const groups = bomProducts().map((pid) => ({ pid, p: PC.productById(pid), n: bomOf(pid).length }));
    const v = dtView("bom", groups, (g) => [g.p && g.p.kode, g.p && g.p.name].join(" "));
    const rows = v.rows.map((g, n) => `<tr>
      <td class="ap-c">${v.from + n + 1}</td>
      <td>${H.esc((g.p && g.p.kode) || "—")}</td>
      <td>${H.esc((g.p && g.p.name) || "—")}</td>
      <td class="ap-c">${g.n}</td>
      <td class="ap-c">${H.esc(bomLatest(g.pid))}</td>
      <td class="ap-c"><button type="button" class="ap-btn ap-btn--info ap-btn--sm" title="View" data-act="bomview" data-p="${g.pid}">${ico("eye")}</button>
        <button type="button" class="ap-btn ap-btn--warning ap-btn--sm" title="Edit" data-act="bomedit" data-p="${g.pid}">${ico("edit")}</button>
        <button type="button" class="ap-btn ap-btn--danger ap-btn--sm" title="Delete" data-act="bomdel" data-p="${g.pid}">${ico("trash")}</button></td></tr>`).join("") || `<tr><td class="ap-empty" colspan="6">No data available in table</td></tr>`;
    return (flash ? `<div class="ap-alert ap-alert--success"><span>${H.esc(flash)}</span><button type="button" data-act="bomflash" aria-label="Close">&#10005;</button></div>` : "")
      + `<div class="ap-card">${bomHead("Daftar BOM (Bill of Material)", `<button type="button" class="ap-btn ap-btn--success ap-btn--sm" data-act="bomadd">${ico("plus")} Buat BOM Baru</button>`)}
        <div class="ap-card__body">${dtTop("bom", v.st)}
          ${apTable("ap-table--bordered ap-table--striped ap-table--hover", [{ t: "No", c: "ap-c", w: "50px" }, "Kode Produk", "Nama Produk", { t: "Jumlah Varian", c: "ap-c" }, { t: "Latest Update", c: "ap-c" }, { t: "Aksi", c: "ap-c", w: "140px" }], rows)}
          ${dtBottom("bom", v)}</div></div>`;
  }

  function bomForm() {
    const d = U().bomDraft;
    if (!d) return bomList();
    const p = PC.productById(d.productId);
    const free = S().products.filter((x) => !bomOf(x.id).length || x.id === d.productId);
    const prodOpts = d.isNew ? [{ v: "", l: "-- Pilih Produk --" }].concat(free.map((x) => ({ v: x.id, l: prodLabel(x) })))
      : [{ v: d.productId, l: p ? prodLabel(p) : d.productId }];
    const used = (i) => d.rows.filter((_, j) => j !== i).map((r) => r.materialId);
    const rows = d.rows.map((r, i) => {
      const mat = PC.materialById(r.materialId);
      const mopts = [{ v: "", l: "-- Pilih Material --" }].concat(S().materials.filter((m) => m.id === r.materialId || !used(i).includes(m.id)).map((m) => ({ v: m.id, l: matLabel(m) })));
      return `<tr>
        <td class="ap-c">${i + 1}</td>
        <td>${H.esc(r.uniq)}</td>
        <td>${apSel(`ui.bomDraft.rows.${i}.materialId`, r.materialId, mopts, { struct: 1 })}</td>
        <td class="ap-r">${mat ? f(mat.thickness) : "—"}</td>
        <td class="ap-r">${mat ? f(mat.width, 0) : "—"}</td>
        <td class="ap-r"><strong>${mat ? f(PC.bpmOf(mat), 4) : "—"}</strong></td>
        <td class="ap-c"><button type="button" class="ap-btn ap-btn--danger ap-btn--sm" data-act="bomrowdel" data-i="${i}" title="Hapus">${ico("trash")}</button></td></tr>`;
    }).join("") || `<tr><td class="ap-empty" colspan="7">Belum ada varian. Klik Tambah Varian.</td></tr>`;
    const st = S().standard;
    return `<div class="ap-card">${bomHead(d.isNew ? "Buat BOM Baru" : "Edit BOM: " + H.esc(p ? p.name : ""), `<button type="button" class="ap-btn ap-btn--secondary ap-btn--sm" data-act="bomback">${ico("back")} Kembali</button>`)}
      <div class="ap-card__body">
        <div class="ap-row" style="grid-template-columns:2fr 1fr;margin-bottom:14px">
          <div><label class="ap-lbl" style="font-weight:600">Produk FG <span class="ap-req">*</span></label>${apSel("ui.bomDraft.productId", d.productId, prodOpts, { struct: 1 })}</div>
          <div><label class="ap-lbl" style="font-weight:600">Trade Name Produk</label><div class="ap-in ap-in--ro">${H.esc(p ? (p.tradeName || p.name) : "Otomatis dari master")}</div></div>
        </div>
        <div class="ap-card ap-card--primary" style="margin-bottom:0">
          <div class="ap-card__head ap-card__head--bar ap-card__head--primary"><h4>${ico("list")} Daftar Varian Tebal (Material)</h4>
            <button type="button" class="ap-btn ap-btn--light ap-btn--sm" data-act="bomrowadd">${ico("plus")} Tambah Varian</button></div>
          <div class="ap-card__body ap-card__body--flush">
            ${apTable("ap-table--bordered ap-table--sm", [{ t: "No", c: "ap-c", w: "40px" }, "No. Uniq ID", { t: "Material *", w: "34%" }, { t: "Varian Tebal", c: "ap-r" }, { t: "Lebar Coil", c: "ap-r" }, { t: "Berat/Meter (kg/m)", c: "ap-r" }, { t: "Hapus", c: "ap-c", w: "60px" }], rows)}
          </div>
        </div>
        <p class="ap-hint" style="margin-top:10px">Berat/Meter (otomatis, read-only) = ((Tebal Material − Standar Toleransi) × Lebar Coil × Standar Berat Jenis) + Coating · nilai standar dari Master Standard Material (Toleransi ${f(st.toleransi)}, Berat Jenis ${f(st.bj)}, Coating ${f(st.coating)}). Material yang sudah dipakai varian lain pada produk ini tidak muncul di pilihan (tidak boleh dobel).</p>
        <div class="ap-gap" style="margin-top:14px">
          <button type="button" class="ap-btn ap-btn--primary" data-act="bomsave">${ico("save")} Simpan BOM</button>
          <button type="button" class="ap-btn ap-btn--secondary" data-act="bomback">Batal</button>
        </div></div></div>`;
  }

  function bomView() {
    const p = PC.productById(BV().productId), rows = bomOf(BV().productId);
    const body = rows.map((b, i) => {
      const m = PC.materialById(b.materialId);
      return `<tr><td class="ap-c">${i + 1}</td><td><span class="ap-badge">${H.esc(b.uniq)}</span></td><td><code>${H.esc(m ? m.kode : "—")}</code></td><td>${H.esc(m ? m.name : "—")}</td>
        <td class="ap-r">${m ? f(m.thickness) : "—"}</td><td class="ap-r">${m ? f(m.width, 0) : "—"}</td><td class="ap-r"><strong>${m ? f(PC.bpmOf(m), 4) : "—"}</strong></td></tr>`;
    }).join("") || `<tr><td class="ap-empty" colspan="7">Belum ada varian</td></tr>`;
    return `<div class="ap-card" style="margin-bottom:14px">${bomHead("Detail BOM", `<div class="ap-gap">
        <button type="button" class="ap-btn ap-btn--warning ap-btn--sm" data-act="bomedit" data-p="${BV().productId}">${ico("edit")} Edit</button>
        <button type="button" class="ap-btn ap-btn--secondary ap-btn--sm" data-act="bomback">${ico("back")} Kembali</button></div>`)}
      <div class="ap-card__body"><table class="ap-table" style="width:auto;min-width:420px"><tbody>
        ${bomKeyVal("Kode Produk", `<strong>${H.esc(p ? p.kode : "—")}</strong>`)}
        ${bomKeyVal("Nama Produk", H.esc(p ? p.name || "-" : "—"))}
        ${bomKeyVal("Latest Update", H.esc(bomLatest(BV().productId)))}
      </tbody></table></div></div>
      <div class="ap-card">${`<div class="ap-card__head ap-card__head--bar"><h6>Daftar Varian Tebal (${rows.length} item)</h6></div>`}
        <div class="ap-card__body ap-card__body--flush">${apTable("ap-table--bordered ap-table--sm ap-table--hover", [{ t: "No", c: "ap-c", w: "50px" }, "No. Uniq ID", "Kode Material", "Nama Material", { t: "Varian Tebal", c: "ap-r" }, { t: "Lebar Coil", c: "ap-r" }, { t: "Berat/Meter (kg/m)", c: "ap-r" }], body)}</div></div>`;
  }

  const bomErr = (text) => dlg({ icon: "warning", title: "Error Message!", text });
  pages.bom = {
    title: "BOM Produk",
    hint: "Engineering menyusun BOM per Varian Tebal dengan memilih material dari Master Material; Berat/Meter dihitung otomatis. Tata letak mengikuti modul BOM aplikasi: daftar, halaman form terpisah, dan halaman detail.",
    noSave: true,
    render() {
      const bv = BV();
      if (bv.mode === "form" && U().bomDraft) return bomForm();
      if (bv.mode === "view" && PC.productById(bv.productId)) return bomView();
      bv.mode = "list";
      return bomList();
    },
    onChange(path) {
      const d = U().bomDraft;
      if (!d) return;
      if (path === "ui.bomDraft.productId") d.rows.forEach((r, i) => { r.uniq = `Type ${prodNo(d.productId)}.${i + 1}`; });
    },
    act: Object.assign({
      bomadd() {
        U().bomDraft = { productId: "", isNew: true, rows: [{ id: PC.uid("B"), uniq: "—", materialId: "" }] };
        BV().mode = "form";
      },
      bomedit(d) {
        const pid = d.p || BV().productId;
        U().bomDraft = { productId: pid, isNew: false, rows: bomOf(pid).map((b) => ({ id: b.id, uniq: b.uniq, materialId: b.materialId })) };
        BV().mode = "form";
      },
      bomview(d) { BV().mode = "view"; BV().productId = d.p; },
      bomback() { U().bomDraft = null; BV().mode = "list"; },
      bomflash() { U().flash = null; },
      bomrowadd() {
        const d = U().bomDraft;
        d.rows.push({ id: PC.uid("B"), uniq: d.productId ? nextUniq(d.productId, d.rows) : "—", materialId: "" });
      },
      bomrowdel(d) { U().bomDraft.rows.splice(Number(d.i), 1); },
      bomsave() {
        const d = U().bomDraft;
        if (!PC.productById(d.productId)) { bomErr("Produk FG belum dipilih..."); return; }
        if (!d.rows.length) { bomErr("Tambahkan minimal satu varian material..."); return; }
        if (d.rows.some((r) => !r.materialId)) { bomErr("Ada varian yang materialnya belum dipilih..."); return; }
        if (new Set(d.rows.map((r) => r.materialId)).size !== d.rows.length) { bomErr("Material yang sama tidak boleh dipakai dua kali pada produk ini..."); return; }
        const old = bomOf(d.productId);
        const saved = d.rows.map((r, i) => {
          const o = old.find((x) => x.id === r.id);
          const uniq = r.uniq && r.uniq !== "—" ? r.uniq : `Type ${prodNo(d.productId)}.${i + 1}`;
          return { id: r.id, uniq, productId: d.productId, materialId: r.materialId, qty: 1, updated: o && o.materialId === r.materialId ? o.updated : PC.today(), by: "Engineering" };
        });
        S().boms = S().boms.filter((b) => b.productId !== d.productId).concat(saved);
        U().flash = "BOM berhasil disimpan.";
        U().bomDraft = null; BV().mode = "list";
      },
      bomdel(d) {
        const pid = d.p, ids = bomOf(pid).map((b) => b.id);
        if (S().pricelists.some((pl) => pl.bomIds.some((id) => ids.includes(id)))) { dlg({ icon: "error", title: "Gagal!", text: "BOM masih dipakai Pricelist." }); return; }
        dlg({ icon: "warning", title: "Hapus BOM?", text: "BOM dan semua varian akan dihapus.", confirmText: "Ya, Hapus", cancelText: "Batal", danger: true }).then((ok) => {
          if (!ok) return;
          S().boms = S().boms.filter((b) => b.productId !== pid);
          PC.save(); window.PC_SHELL.render();
          dlg({ icon: "success", title: "Berhasil!", text: "BOM berhasil dihapus.", timer: 1500 });
        });
      },
    }, DT_ACT),
  };

  /* ---------- 7. Flow Process & Cycletime (UC-05, FR-42) — layout seperti master_rate_labor (daftar bertabel + form spreadsheet) ---------- */
  const flowCats = () => uniqList(S().flow.map((x) => x.category).filter(Boolean));
  const flowForm = (cat, view) => {
    const idx = S().flow.map((x, i) => i).filter((i) => S().flow[i].category === cat);
    const rows = idx.map((i, n) => { const x = S().flow[i]; return `<tr><td class="ap-c">${n + 1}</td>
      <td>${H.in(`flow.${i}.area`, x.area, { cls: "ap-in" })}</td><td>${H.in(`flow.${i}.process`, x.process, { cls: "ap-in" })}</td>
      <td class="ap-r">${H.in(`flow.${i}.ct`, Number((x.ct || 0).toFixed(6)), { num: 1, step: 0.0001, min: 0, cls: "ap-in" })}</td>
      <td class="ap-r">${H.in(`flow.${i}.mp`, x.mp, { num: 1, step: 1, min: 0, cls: "ap-in" })}</td>
      <td class="ap-r"><strong>${H.calc("mh", i)}</strong></td>
      <td>${H.in(`flow.${i}.machine`, x.machine, { cls: "ap-in" })}</td>
      <td class="ap-c">${view ? "" : H.del("flow", i)}</td></tr>`; }).join("") || `<tr><td class="ap-empty" colspan="8">Belum ada proses.</td></tr>`;
    return `${apRow(lb("Product Category"), fld("c10", ro(up(cat))))}
      ${apTable("ap-table--bordered ap-table--sm", [{ t: "#", c: "ap-c", w: "40px" }, "Cost Centre / Area", "Process Name", { t: "Cycletime (mnt)", c: "ap-r" }, { t: "Man Power", c: "ap-r" }, { t: "Total Man Hour", c: "ap-r" }, "Machine", { t: "", w: "44px" }], rows)}
      <div class="ap-row" style="margin-top:10px"><div class="c8"><strong>Total Man Hour — ${H.esc(up(cat))} (jam/meter)</strong></div><div class="c4 ap-r"><strong>${H.calc("tmh", cat)}</strong></div></div>
      <div class="ap-hint">Cost Centre dari Master Cost Centre · Machine dari Master Mesin · Total Man Hour = Cycletime × Man Power ÷ 60. Contoh Excel: mesin 24 m/menit → cycletime 1 ÷ 24 = 0,0417 mnt; 1 orang → 0,000694 jam/meter.</div>
      ${view ? "" : `<div class="ap-gap" style="margin-top:12px"><button type="button" class="ap-btn ap-btn--success ap-btn--sm" data-act="addproc" data-c="${H.esc(cat)}">${ico("plus")} Tambah Proses</button></div>`}`;
  };
  const openFlow = (mode, cat) => {
    const snap = clone(S().flow), view = mode === "view";
    openModal({
      app: true, mode, title: (view ? "View" : "Edit") + " Flow Process & Cycletime — " + up(cat), size: "xl",
      hideSave: view, cancelLabel: view ? "Close" : "Cancel",
      bodyFn: () => flowForm(cat, view),
      after() { if (view) document.querySelectorAll("#pcModal input").forEach((el) => el.setAttribute("disabled", "")); },
      validate() {
        const fs = S().flow.filter((x) => x.category === cat);
        if (!fs.length) return "Flow Process belum berisi proses.";
        for (const x of fs) {
          if (!(x.process || "").trim()) return "Process Name wajib diisi pada setiap baris.";
          if (Number(x.ct) < 0 || Number(x.mp) < 0) return "Cycletime dan Man Power tidak boleh negatif.";
        }
        return null;
      },
      onSave() { S().flow.filter((x) => x.category === cat).forEach((x) => { x.updated = PC.today(); x.by = "Engineering"; }); },
      doneMsg: "Flow Process berhasil disimpan.",
      onClose() { S().flow = snap; },
    });
  };
  pages.flow = {
    title: "Flow Process &amp; Cycletime",
    noSave: true,
    hint: "Engineering menyusun urutan proses dan cycle time per Product Category; hasilnya Total Man Hour per kategori. Tampilan depan: daftar Cycletime per Product Category; Add CT / View / Edit membuka jendela berisi flow process.",
    render() {
      const cats = flowCats();
      const v = dtView("flow", cats.map((c) => ({ c })), ({ c }) => c);
      const front = v.rows.map(({ c }, n) => {
        const fs = S().flow.filter((x) => x.category === c);
        const lastUpd = fs.map((x) => x.updated).filter(Boolean).sort().slice(-1)[0] || "—";
        const by = (fs.slice(-1)[0] || {}).by || "—";
        return `<tr><td class="ap-c">${v.from + n + 1}</td><td>${H.esc(up(c))}</td>
          <td class="ap-r">${f(fs.reduce((t, x) => t + (Number(x.ct) || 0), 0), 4)}</td><td class="ap-r">${f(PC.totalManHour(c), 6)}</td>
          <td>${H.esc(lastUpd)}</td><td>${H.esc(by)}</td><td class="ap-c">${viewEdit(`data-c="${H.esc(c)}"`)}</td></tr>`;
      }).join("") || `<tr><td class="ap-empty" colspan="7">No data available in table</td></tr>`;
      const missing = productCats().filter((c) => !flowCats().includes(c));
      return (missing.length ? warnBox("Flow Process belum ada — Simulasi Costing tidak lengkap", missing.map((c) => `<li>Product Category ${H.esc(up(c))} belum punya Flow Process (Total Man Hour = 0). Klik Add CT.</li>`).join("")) : "")
        + gapBanner(["flow"], "Flow Process memengaruhi Simulasi Costing dan Pricelist")
        + `<div class="ap-card ap-card--shadow">${apHead("Daftar Cycletime per Product", "Tiap Product Category punya satu flow process", `Total: ${cats.length} Kategori`, btnAdd("Add CT"))}
          <div class="ap-card__body">${dtTop("flow", v.st)}
            ${apTable("ap-table--bordered ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "Product (Category)", { t: "Cycletime (mnt)", c: "ap-r" }, { t: "Total Man Hour", c: "ap-r" }, "Last Update", "Update By", { t: "Action", c: "ap-c", w: "100px" }], front)}
            ${dtBottom("flow", v)}</div></div>`;
    },
    calc: {
      mh: (i) => f(PC.manHour(S().flow[i] || { ct: 0, mp: 0 }), 6),
      tmh: (c) => f(PC.totalManHour(c), 6),
    },
    act: Object.assign({
      view(d) { openFlow("view", d.c); },
      edit(d) { openFlow("edit", d.c); },
      addproc(d) { S().flow.push({ id: PC.uid("F"), category: d.c, area: "", process: "", machine: "", ct: 0, mp: 1, updated: PC.today(), by: "Engineering" }); },
      add() {
        U().newFlowCat = "";
        openModal({
          app: true, mode: "add", title: "Add Cycletime (CT)", size: "lg",
          bodyFn: () => apRow(lb("Product Category", 1), fld("c10", apSel("ui.newFlowCat", U().newFlowCat, selOpts("Select Product Category", productCats().filter((c) => !flowCats().includes(c)), U().newFlowCat))
            + `<div class="ap-hint">Hanya Product Category yang belum punya Flow Process. Setelah disimpan, klik Edit untuk mengisi prosesnya.</div>`)),
          validate() { return U().newFlowCat ? null : "Product category not selected..."; },
          onSave() { S().flow.push({ id: PC.uid("F"), category: U().newFlowCat, area: "", process: "", machine: "", ct: 0, mp: 1, updated: PC.today(), by: "Engineering" }); },
          doneMsg: "Product Category ditambahkan; klik Edit untuk mengisi prosesnya.",
        });
      },
    }, DT_ACT),
  };

  /* ---------- 8. Man Power Rate (Operator) (UC-07, FR-53) — layout seperti master_rate_labor (tabel bordered Item / Rate / Remark / Action) ---------- */
  const MPR_GROUPS = [["salary", "Salary Direct Man Power"], ["bpjs", "BPJS (input manual)"], ["lain", "Biaya Lain-Lain"]];
  const mprTitle = (k) => (MPR_GROUPS.find((g) => g[0] === k) || [])[1] || "";
  const openMprComp = (key, isNew, i) => {
    const snap = isNew ? null : clone(S().mpr[key][i]);
    openModal({
      app: true, mode: isNew ? "add" : "edit", title: (isNew ? "Add" : "Edit") + " Komponen — " + mprTitle(key), size: "lg",
      bodyFn: () => { const x = S().mpr[key][i]; return `
        ${apRow(lb("Item", 1), fld("c10", apIn(`mpr.${key}.${i}.n`, x.n)))}
        ${apRow(lb("Nilai (Rp)", 1), fld("c10", numIn(`mpr.${key}.${i}.v`, x.v, { step: 0.01 })))}
        ${apRow(lb("Keterangan"), fld("c10", apIn(`mpr.${key}.${i}.k`, x.k)))}`; },
      validate() {
        const x = S().mpr[key][i];
        if (!(x.n || "").trim()) return "Item wajib diisi.";
        if (!hasV(x.v)) return "Nilai wajib diisi.";
        if (Number(x.v) < 0) return "Nilai tidak boleh negatif.";
        return null;
      },
      doneMsg: "Komponen berhasil disimpan.",
      onClose() { if (isNew) S().mpr[key].splice(i, 1); else S().mpr[key][i] = snap; },
    });
  };
  const mprGroupCard = (key, title, n) => {
    const rows = S().mpr[key].map((x, i) => `<tr><td class="ap-c">${i + 1}</td><td>${H.esc(x.n)}</td><td class="ap-r">${rpShow(x.v)}</td><td>${H.esc(x.k)}</td>
      <td class="ap-c"><button type="button" class="ap-btn ap-btn--primary ap-btn--sm" title="Edit" data-act="mprcedit" data-g="${key}" data-i="${i}">${ico("edit")}</button>
        <button type="button" class="ap-btn ap-btn--danger ap-btn--sm" title="Delete" data-act="mprcdel" data-g="${key}" data-i="${i}">${ico("trash")}</button></td></tr>`).join("")
      || `<tr><td class="ap-empty" colspan="5">Belum ada komponen.</td></tr>`;
    return `<div class="ap-card ap-card--shadow">${apHead(`${n}. ${title}`, key === "bpjs" ? "Nilai rupiah tiap komponen diinput manual; persentase hanya keterangan." : "", `Subtotal: Rp ${f(PC.sum(S().mpr[key], (x) => x.v))}`,
      `<button type="button" class="ap-btn ap-btn--primary" data-act="mprcadd" data-g="${key}">${ico("plus")} Add Komponen</button>`)}
      <div class="ap-card__body">${apTable("ap-table--bordered ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "Item", { t: "Nilai (Rp)", c: "ap-r" }, "Keterangan", { t: "Action", c: "ap-c", w: "100px" }], rows)}</div></div>`;
  };
  const WARN_LBL = (t) => `<span class="ap-label ap-label--warning">${t}</span>`;
  pages.mpr = {
    title: "Man Power Rate (Operator)",
    noSave: true,
    hint: "Rate 1/3 Operator (Rp/jam) berasal dari rincian gaji/BPJS/biaya lain Operator. Rate Borongan Helper per Product Category ada di menu terpisah (Rate Borongan Helper). Man Power Cost = (Total Man Hour × Rate 1/3 Operator + Rate Borongan Helper Rp/Meter) × Faktor Indirect Labor. Faktor Indirect Labor adalah master tersendiri (menu Faktor Indirect Labor).",
    render() {
      const cats = productCats();
      const ex = cats.map((c, i) => `<tr><td class="ap-c">${i + 1}</td><td>${H.esc(up(c))}</td>
        <td class="ap-r">${f(PC.totalManHour(c), 6)}</td><td class="ap-r">${H.calc("mpop", c)}</td><td class="ap-r">${H.calc("mpbor", c)}</td>
        <td class="ap-r">${H.calc("mpsum", c)}</td><td class="ap-r">${H.calc("mpfk", c)}</td>
        <td class="ap-r"><strong>${H.calc("mpc", c)}</strong></td></tr>`).join("");
      const m = S().mpr;
      return gapBanner(["mpr"], "Parameter Man Power belum lengkap — Simulasi Costing tidak lengkap")
        + MPR_GROUPS.map((g, n) => mprGroupCard(g[0], g[1], n + 1)).join("")
        + `<div class="ap-card ap-card--shadow">${apHead("4. Parameter &amp; Rate 1/3 Operator", "", "", `<button type="button" class="ap-btn ap-btn--primary" data-act="mprparam">${ico("edit")} Edit Parameter</button>`)}
          <div class="ap-card__body">
            <div class="ap-hint" style="margin-bottom:8px">${H.calc("jamnote")}</div>
            ${apTable("ap-table--bordered", ["Komponen", { t: "Nilai", c: "ap-r" }, "Keterangan"], `
              <tr><td>Jam kerja per bulan <span class="ap-hint">(parameter)</span></td><td class="ap-r"><strong>${f(m.jam, 0)}</strong></td><td>Pembagi Total Biaya Direct Labor</td></tr>
              <tr><td>Porsi Operator <span class="ap-hint">(parameter)</span></td><td class="ap-r"><strong>1/${f(m.porsiDen, 0)}</strong></td><td>Operator dianggap hanya ${"1/" + f(m.porsiDen, 0)} bekerja langsung pada produk</td></tr>
              <tr><td><strong>Faktor Indirect Labor</strong> <span class="ap-hint">(master)</span></td><td class="ap-r"><strong>${f(PC.faktorOf(), 2)}</strong></td><td>Master: lihat menu <a class="ap-link" href="pc-faktor-indirect.html">Faktor Indirect Labor</a>. Mengalikan jumlah (Total Man Hour × Rate 1/3 Operator + Rate Borongan Helper Rp/Meter) pada Man Power Cost</td></tr>
              <tr><td>Total Biaya Direct Labor Operator / Bulan</td><td class="ap-r"><strong>${H.calc("total")}</strong></td><td>Salary + BPJS + Biaya Lain-Lain</td></tr>
              <tr><td>Rate Direct Labor Operator (Rp/jam)</td><td class="ap-r"><strong>${H.calc("rdl")}</strong></td><td>Total ÷ jam kerja per bulan (belum dibulatkan)</td></tr>
              <tr><td><strong>Rate 1/3 Operator (Rp/jam)</strong></td><td class="ap-r"><strong>${H.calc("rop")}</strong></td><td>ROUNDUP(Rate Direct Labor × 1/${f(m.porsiDen, 0)}) — dibulatkan ke atas ke Rupiah penuh sesudah dikali 1/${f(m.porsiDen, 0)}. Satu-satunya angka yang dibulatkan; komponen lain 2 desimal tanpa pembulatan.</td></tr>`)}
          </div></div>`
        + `<div class="ap-card ap-card--shadow">${apHead("5. Contoh: Man Power Cost per Product Category", "Man Power Cost (Rp/Meter) = (Total Man Hour × Rate 1/3 Operator + Rate Borongan Helper Rp/Meter) × Faktor Indirect Labor", "", "")}
          <div class="ap-card__body">${apTable("ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "Product Category", { t: "Total Man Hour (jam/meter)", c: "ap-r" }, { t: "Bagian Operator (Rp/Meter)", c: "ap-r" }, { t: "Borongan Helper (Rp/Meter)", c: "ap-r" }, { t: "Jumlah sebelum Faktor (Rp/Meter)", c: "ap-r" }, { t: "× Faktor Indirect Labor", c: "ap-r" }, { t: "Man Power Cost (Rp/Meter)", c: "ap-r" }], ex)}
            <div class="ap-hint">Rate Borongan Helper diisi di menu <a class="ap-link" href="pc-borongan.html">Rate Borongan Helper</a>. Faktor Indirect Labor (master di menu <a class="ap-link" href="pc-faktor-indirect.html">Faktor Indirect Labor</a>) mengalikan jumlah bagian Operator dan Borongan. Angka contoh mockup, bukan angka acuan klien.</div></div></div>`;
    },
    calc: {
      total: () => f(PC.manPowerRate().total),
      rdl: () => f(PC.manPowerRate().rateDL),
      rop: () => f(PC.manPowerRate().rateOp, 0),
      jamnote: () => `Konstanta yang digunakan: <strong>${f(S().mpr.jam, 0)} jam kerja per bulan</strong>, porsi Operator <strong>1/${f(S().mpr.porsiDen, 0)}</strong>. Faktor Indirect Labor <strong>${f(PC.faktorOf(), 2)}</strong> diambil dari master (menu <a class="ap-link" href="pc-faktor-indirect.html">Faktor Indirect Labor</a>). Jam kerja dan porsi adalah parameter, bukan angka tetap.`,
      mpop: (c) => (PC.totalManHour(c) > 0 ? f(PC.totalManHour(c) * PC.manPowerRate().rateOp) : WARN_LBL("Flow Process belum ada")),
      mpbor: (c) => { const b = PC.boronganRow(c); if (!b || !hasV(b.v)) return WARN_LBL("Rate belum ada"); return f(PC.boronganPerMeter(c)); },
      mpsum: (c) => { const b = PC.boronganRow(c); if (!b || !hasV(b.v) || !(PC.totalManHour(c) > 0)) return "—"; return f(PC.totalManHour(c) * PC.manPowerRate().rateOp + PC.boronganPerMeter(c)); },
      mpfk: () => f(PC.manPowerRate().faktor, 2),
      mpc: (c) => { const b = PC.boronganRow(c), mp = PC.manPowerRate(); if (!b || !hasV(b.v) || !(PC.totalManHour(c) > 0)) return "—"; return f((PC.totalManHour(c) * mp.rateOp + PC.boronganPerMeter(c)) * mp.faktor); },
    },
    act: {
      mprcadd(d) { S().mpr[d.g].push({ n: "", v: null, k: "per bulan" }); openMprComp(d.g, true, S().mpr[d.g].length - 1); },
      mprcedit(d) { openMprComp(d.g, false, Number(d.i)); },
      mprcdel(d) {
        const key = d.g, i = Number(d.i);
        confirmDel({}).then((ok) => { if (!ok) return; S().mpr[key].splice(i, 1); PC.save(); window.PC_SHELL.render(); doneDel(); });
      },
      mprparam() {
        const snap = clone(S().mpr); const keep = { jam: snap.jam, porsiDen: snap.porsiDen };
        openModal({
          app: true, mode: "edit", title: "Edit Parameter Man Power", size: "lg",
          bodyFn: () => `${apRow(lb("Jam kerja per bulan", 1), fld("c10", numIn("mpr.jam", S().mpr.jam, { step: 1 }) + `<div class="ap-hint">Parameter (contoh 173), bukan konstanta tetap.</div>`))}
            ${apRow(lb("Porsi Operator = 1 / n (n =)", 1), fld("c10", numIn("mpr.porsiDen", S().mpr.porsiDen, { step: 1 }) + `<div class="ap-hint">Operator dianggap hanya 1/n bekerja langsung pada produk (contoh n = 3).</div>`))}`,
          validate() {
            const m = S().mpr;
            if (!(Number(m.jam) > 0)) return "Jam kerja per bulan harus lebih besar dari 0.";
            if (!(Number(m.porsiDen) > 0)) return "Porsi Operator (n) harus lebih besar dari 0.";
            return null;
          },
          doneMsg: "Parameter berhasil disimpan.",
          onClose() { Object.assign(S().mpr, keep); },
        });
      },
    },
  };

  /* ---------- 8b. Rate Borongan Helper (UC-07, FR-53) — menu terpisah; layout seperti master_rate_labor ---------- */
  pages.borongan = masterPage({
    key: "borongan", title: "Rate Borongan Helper", unit: "Rate", modalTitle: "Rate Borongan Helper", size: "lg", addLabel: "Add Rate Borongan", stamp: "Costing",
    hint: "Costing menetapkan Rate Borongan Helper per Product Category dalam Rp/Meter. Bagian Operator (Rate 1/3 Operator) ada di menu Man Power Rate (Operator); keduanya dijumlahkan lalu dikali Faktor Indirect Labor di Product Costing.",
    headTitle: "Rate Borongan Helper", headSub: "Rate Borongan Helper per Product Category dalam Rp/Meter; dijumlahkan dengan bagian Operator lalu dikali Faktor Indirect Labor",
    banner: () => { const miss = productCats().filter((c) => !S().borongan.some((b) => b.category === c));
      return (miss.length ? warnBox("Rate Borongan Helper belum ada — Simulasi Costing tidak lengkap", miss.map((c) => `<li>Product Category ${H.esc(up(c))} belum punya Rate Borongan Helper. Klik Add Rate Borongan.</li>`).join("")) : "") + gapBanner(["borongan"], "Borongan memengaruhi Simulasi Costing dan Pricelist"); },
    cols: [
      { h: "Product Category", fn: (r) => H.esc(up(r.category) || "—") },
      { h: "Rate Borongan Helper (Rp/Meter)", c: "ap-r", fn: (r) => (hasV(r.v) ? `<strong>${rpShow(r.v)}</strong>` : WARN_LBL("belum diisi")) },
      { h: "Last Update", fn: (r) => H.esc(r.updated || "—") },
      { h: "Updated By", fn: (r) => H.esc(r.by || "—") },
    ],
    search: (r) => [r.category, r.v].join(" "),
    blank: () => ({ id: PC.uid("BH"), category: "", v: null }),
    form: (i) => { const b = S().borongan[i]; return `
      ${apRow(lb("Product Category", 1), fld("c10", apSel(`borongan.${i}.category`, b.category, catOpts(b.category), { struct: 1 })))}
      ${apRow(lb("Rate Borongan (Rp/Meter)", 1), fld("c10", numIn(`borongan.${i}.v`, b.v, { step: 1 }) + `<div class="ap-hint">Dalam Rp/Meter (tanpa satuan lain, tanpa konversi).</div>`))}`; },
    validate: (r, i) => {
      if (!r.category) return "Product category not selected...";
      if (S().borongan.some((x, j) => j !== i && x.category === r.category)) return "Product Category " + up(r.category) + " sudah punya Rate Borongan Helper.";
      if (!hasV(r.v)) return "Rate Borongan wajib diisi.";
      if (Number(r.v) < 0) return "Nilai tidak boleh negatif.";
      return null;
    },
  });

  /* ---------- 9. Rate Bahan Pendukung Khusus & FOH (UC-07, FR-53) — layout seperti master_forwarding_cost (Value Cost Rp/Kg) ---------- */
  pages.bpkfoh = masterPage({
    key: "bpkfoh", title: "Rate Bahan Pendukung Khusus &amp; FOH", unit: "Rate", modalTitle: "Rate Bahan Pendukung & FOH", size: "lg", addLabel: "Add Rate", stamp: "Costing",
    hint: "Costing menetapkan rate per Product Category dalam Rp/Kg. Saat costing, Rp/Meter = Rate Rp/Kg × Berat/Meter. Perubahan rate tidak mengubah laporan produksi yang sudah dibuat (rate dibekukan saat laporan dibuat).",
    headTitle: "Rate Bahan Pendukung Khusus &amp; FOH", headSub: "Rate per Product Category (Rp/Kg)",
    banner: () => { const miss = productCats().filter((c) => !S().bpkfoh.some((r) => r.category === c));
      return (miss.length ? warnBox("Rate Bahan Pendukung & FOH belum ada — Simulasi Costing tidak lengkap", miss.map((c) => `<li>Product Category ${H.esc(up(c))} belum punya rate. Klik Add Rate.</li>`).join("")) : "") + gapBanner(["bpkfoh"], "Rate memengaruhi Simulasi Costing dan Pricelist"); },
    cols: [
      { h: "Product Category", fn: (r) => H.esc(up(r.category) || "—") },
      { h: "Rate Bahan Pendukung Khusus (Rp/Kg)", c: "ap-r", fn: (r) => rpShow(r.bpk) },
      { h: "Rate FOH (Rp/Kg)", c: "ap-r", fn: (r) => rpShow(r.foh) },
      { h: "Last Update", fn: (r) => H.esc(r.updated || "—") },
      { h: "Updated By", fn: (r) => H.esc(r.by || "—") },
    ],
    search: (r) => [r.category, r.bpk, r.foh].join(" "),
    blank: () => ({ id: PC.uid("R"), category: "", bpk: null, foh: null }),
    form: (i) => { const r = S().bpkfoh[i]; return `
      ${apRow(lb("Product Category", 1), fld("c10", apSel(`bpkfoh.${i}.category`, r.category, catOpts(r.category))))}
      ${apRow(lb("Rate Bahan Pendukung Khusus (Rp/Kg)", 1), fld("c10", numIn(`bpkfoh.${i}.bpk`, r.bpk, { step: 1 })))}
      ${apRow(lb("Rate FOH (Rp/Kg)", 1), fld("c10", numIn(`bpkfoh.${i}.foh`, r.foh, { step: 1 })))}
      <div class="ap-hint">Rp/Meter = Rate (Rp/Kg) × Berat/Meter (BOM yang dipilih).</div>`; },
    validate: (r, i) => {
      if (!r.category) return "Product category not selected...";
      if (S().bpkfoh.some((x, j) => j !== i && x.category === r.category)) return "Product Category " + up(r.category) + " sudah punya rate.";
      if (!hasV(r.bpk) || !hasV(r.foh)) return "Rate Bahan Pendukung Khusus dan Rate FOH wajib diisi.";
      if (Number(r.bpk) < 0 || Number(r.foh) < 0) return "Rate tidak boleh negatif.";
      return null;
    },
  });

  /* ---------- 10. General Rate (UC-08, UC-09, FR-54, FR-55) — layout seperti index master_rate_labor (Item / Rate / Remark / Update) ---------- */
  const GEN_REQUIRED = ["cons", "scrap", "koord"]; // menentukan kelengkapan costing; Other Cost kosong = 0 (FR-45)
  const genShow = (g) => (g.u === "pct" ? pctShow(g.v) : rpShow(g.v, "Kg"));
  pages.general = {
    title: "General Rate",
    noSave: true,
    hint: "Berlaku untuk semua produk: Manufacturing Support (Consumable, Scrap, Biaya Koordinasi) dan enam elemen Other Cost. Setiap baris punya satuan sendiri. Negotiation Allowance ada di menu tersendiri (per Product Category). Perubahan rate tidak mengubah laporan produksi yang sudah dibuat.",
    render() {
      const v = dtView("general", S().general.map((g, i) => ({ g, i })), ({ g }) => [g.group, g.label, g.basis].join(" "));
      const rows = v.rows.map(({ g, i }, n) => `<tr><td class="ap-c">${v.from + n + 1}</td><td>${H.esc(g.group)}</td><td class="fw">${H.esc(g.label)}</td><td>${H.esc(g.basis)}</td>
        <td class="ap-r">${genShow(g)}</td><td>${H.esc(g.by || "—")}</td><td>${H.esc(g.updated || "—")}</td>
        <td class="ap-c">${iconBtn("edit", "Edit", "edit", "primary", `data-i="${i}"`)}</td></tr>`).join("") || `<tr><td class="ap-empty" colspan="8">No data available in table</td></tr>`;
      return gapBanner(["general"], "General Rate belum lengkap — Simulasi Costing tidak lengkap")
        + `<div class="ap-card ap-card--shadow">${apHead("Rate Umum", "Manufacturing Support dan Other Cost (berlaku semua produk)", `Total: ${S().general.length} Rate`, "")}
          <div class="ap-card__body">${dtTop("general", v.st)}
            ${apTable("ap-table--bordered ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "Kelompok", "Element Costing", "Rumus / Dasar", { t: "Rate / Tarif", c: "ap-r" }, "Update By", "Update Date", { t: "Action", c: "ap-c", w: "80px" }], rows)}
            ${dtBottom("general", v)}</div></div>`;
    },
    act: Object.assign({
      edit(d) {
        const i = Number(d.i), snap = clone(S().general[i]);
        openModal({
          app: true, mode: "edit", title: "Edit Rate — " + S().general[i].label, size: "lg",
          bodyFn: () => { const g = S().general[i], pct = g.u === "pct"; return `
            ${apRow(lb("Element Costing"), fld("c10", ro(g.label)))}
            ${apRow(lb("Rumus / Dasar"), fld("c10", ro(g.basis)))}
            ${apRow(lb(pct ? "Nilai (%)" : "Nilai (Rp/Kg)", 1), fld("c10", (pct ? pctIn(`general.${i}.v`, g.v) : numIn(`general.${i}.v`, g.v, { step: 1 }))
              + (GEN_REQUIRED.includes(g.key) ? "" : `<div class="ap-hint">Kosong dianggap 0 untuk elemen Other Cost.</div>`)))}`; },
          validate() {
            const g = S().general[i];
            if (GEN_REQUIRED.includes(g.key) && !hasV(g.v)) return "Nilai wajib diisi.";
            if (!nonNeg(g.v)) return "Nilai tidak boleh negatif.";
            return null;
          },
          onSave() { const g = S().general[i]; if (!GEN_REQUIRED.includes(g.key) && !hasV(g.v)) g.v = 0; g.updated = PC.today(); g.by = "Costing"; },
          doneMsg: "Rate berhasil disimpan.",
          onClose() { S().general[i] = snap; },
        });
      },
    }, DT_ACT),
  };

  /* ---------- 11. Negotiation Allowance per Product Category (UC-10, FR-55) — pola master sederhana (master_coating) ---------- */
  pages.nego = masterPage({
    key: "nego", title: "Negotiation Allowance (Extra Margin)", unit: "Rate", modalTitle: "Negotiation Allowance", size: "lg", addLabel: "Add", stamp: "Sales",
    hint: "Sales menetapkan persentase per Product Category. Negotiation Allowance = % × Bottom Price; Price List = Bottom Price + Negotiation Allowance.",
    headTitle: "Negotiation Allowance (Extra Margin)", headSub: "Persentase per Product Category (× Bottom Price)",
    banner: () => { const miss = productCats().filter((c) => !S().nego.some((n) => n.category === c));
      const empty = S().nego.filter((n) => !hasV(n.v));
      const items = miss.map((c) => `<li>Product Category ${H.esc(up(c))} belum punya Negotiation Allowance. Klik Add.</li>`).concat(empty.map((n) => `<li>Negotiation Allowance kategori ${H.esc(up(n.category))} belum diisi nilainya. Klik Edit.</li>`));
      return items.length ? warnBox("Negotiation Allowance belum lengkap — Pricelist tidak bisa diajukan", items.join("")) : ""; },
    cols: [
      { h: "Product Category", fn: (r) => H.esc(up(r.category) || "—") },
      { h: "Negotiation Allowance (% × Bottom Price)", c: "ap-r", fn: (r) => pctShow(r.v) },
      { h: "Last Update", fn: (r) => H.esc(r.updated || "—") },
      { h: "Updated By", fn: (r) => H.esc(r.by || "—") },
    ],
    search: (r) => [r.category, r.v].join(" "),
    blank: () => ({ id: PC.uid("N"), category: "", v: null }),
    form: (i) => { const r = S().nego[i]; return `
      ${apRow(lb("Product Category", 1), fld("c10", apSel(`nego.${i}.category`, r.category, catOpts(r.category))))}
      ${apRow(lb("Negotiation Allowance (%)", 1), fld("c10", pctIn(`nego.${i}.v`, r.v) + `<div class="ap-hint">Negotiation Allowance = % × Bottom Price.</div>`))}`; },
    validate: (r, i) => {
      if (!r.category) return "Product category not selected...";
      if (S().nego.some((x, j) => j !== i && x.category === r.category)) return "Product Category " + up(r.category) + " sudah punya Negotiation Allowance.";
      if (!hasV(r.v)) return "Negotiation Allowance wajib diisi.";
      if (Number(r.v) < 0) return "Nilai tidak boleh negatif.";
      return null;
    },
  });

  /* ---------- 11b. Faktor Indirect Labor (UC-07, FR-53) — master global satu nilai (bisa berubah) ---------- */
  pages.faktor = masterPage({
    key: "faktor", title: "Faktor Indirect Labor", unit: "Faktor", modalTitle: "Faktor Indirect Labor", size: "lg", stamp: "Costing", noAdd: true, noDel: true,
    hint: "Costing menetapkan Faktor Indirect Labor sebagai master global (satu nilai untuk semua produk) karena nilainya dapat berubah. Faktor mengalikan (Total Man Hour × Rate 1/3 Operator + Rate Borongan Helper Rp/Meter) pada Man Power Cost. Perubahan tidak mengubah Pricelist yang sudah diajukan (snapshot) maupun laporan produksi yang sudah dibuat.",
    headTitle: "Faktor Indirect Labor", headSub: "Pengali Man Power Cost (global, satu nilai)",
    banner: () => (PC.faktorOf() > 0 ? "" : warnBox("Faktor Indirect Labor belum diisi — Man Power Cost tidak lengkap", `<li>Klik Edit lalu isi nilai Faktor Indirect Labor.</li>`)),
    cols: [
      { h: "Faktor Indirect Labor", c: "ap-r", fn: (r) => (hasV(r.v) ? `<strong>${f(r.v, 2)}</strong>` : WARN_LBL("belum diisi")) },
      { h: "Keterangan", fn: () => "× (Total Man Hour × Rate 1/3 Operator + Rate Borongan Helper Rp/Meter)" },
      { h: "Last Update", fn: (r) => H.esc(r.updated || "—") },
      { h: "Updated By", fn: (r) => H.esc(r.by || "—") },
    ],
    search: (r) => ["faktor indirect labor", r.v].join(" "),
    blank: () => ({ id: PC.uid("FK"), v: null }),
    form: (i) => { const r = S().faktor[i]; return `
      ${apRow(lb("Faktor Indirect Labor", 1), fld("c10", numIn(`faktor.${i}.v`, r.v, { step: 0.01 }) + `<div class="ap-hint">Pengali Man Power Cost; berlaku untuk semua produk. Nilai harus lebih besar dari 0.</div>`))}`; },
    validate: (r) => {
      if (!hasV(r.v)) return "Faktor Indirect Labor wajib diisi.";
      if (!(Number(r.v) > 0)) return "Faktor Indirect Labor harus lebih besar dari 0.";
      return null;
    },
    after: () => {
      const fk = PC.faktorOf(), mp = PC.manPowerRate();
      const ex = productCats().map((c, n) => {
        const b = PC.boronganRow(c);
        const ok = b && hasV(b.v) && PC.totalManHour(c) > 0;
        const sum = ok ? PC.totalManHour(c) * mp.rateOp + PC.boronganPerMeter(c) : null;
        return `<tr><td class="ap-c">${n + 1}</td><td>${H.esc(up(c))}</td><td class="ap-r">${sum == null ? WARN_LBL("Data belum lengkap") : f(sum)}</td><td class="ap-r">${fk > 0 ? f(fk, 2) : WARN_LBL("belum diisi")}</td><td class="ap-r"><strong>${sum == null || !(fk > 0) ? "—" : f(sum * fk)}</strong></td></tr>`;
      }).join("");
      return `<div class="ap-card ap-card--shadow">${apHead("Contoh dampak: Man Power Cost per Product Category", "Man Power Cost (Rp/Meter) = jumlah sebelum Faktor × Faktor Indirect Labor. Hanya baca; angka contoh mockup, bukan angka acuan klien.", "", "")}
        <div class="ap-card__body">${apTable("ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "Product Category", { t: "Jumlah sebelum Faktor (Rp/Meter)", c: "ap-r" }, { t: "× Faktor Indirect Labor", c: "ap-r" }, { t: "Man Power Cost (Rp/Meter)", c: "ap-r" }], ex)}</div></div>`;
    },
  });

  /* ---------- 12. Simulasi Costing & Report Costing Detail/Produk (UC-11..UC-16) — layout seperti daftar-detail costbook/HPP ---------- */
  // Detil breakdown (modal). focusUniq != null → sorot kolom varian itu. Semua komponen dalam dua satuan.
  function costingDetail(focusUniq) {
    const cs = S().boms.map((b) => PC.costing(b)).filter(Boolean);
    if (!cs.length) return `<p class="ap-empty">Belum ada BOM.</p>`;
    const hot = (c) => (focusUniq && c.bom.uniq === focusUniq ? " pc-colhot" : "");
    const n = cs.length * 2 + 1;
    const dual = (label, fm, fk, extra = "") => `<tr class="${extra}"><td style="text-align:left">${label}</td>${cs.map((c) => `<td class="num${hot(c)}">${fm(c)}</td><td class="num${hot(c)}">${fk(c)}</td>`).join("")}</tr>`;
    const one = (label, fn, extra = "") => `<tr class="${extra}"><td style="text-align:left">${label}</td>${cs.map((c) => `<td class="num${hot(c)}" colspan="2">${fn(c)}</td>`).join("")}</tr>`;
    const txt = (label, fn) => `<tr><td style="text-align:left">${label}</td>${cs.map((c) => `<td class="${hot(c)}" colspan="2">${fn(c)}</td>`).join("")}</tr>`;
    const L = (key, extra = "") => dual(LBL[key], (c) => f(c.lines[key]), (c) => f(c.linesKg[key]), extra);
    const sec = (t) => `<tr class="pc-sub"><td colspan="${n}" style="text-align:left">${t}</td></tr>`;
    const LBL = {
      material: "Material Cost", manPower: "Man Power Cost", bpk: "Bahan Pendukung Khusus", foh: "FOH", proses: "Biaya Proses",
      cons: "Consumables", scrap: "Scrap", koord: "Biaya Koordinasi", support: "Manufacturing Support", hpp: "HPP",
      bottom: "Bottom Price (HPP + Other Cost)", nego: "Negotiation Allowance", priceList: "Price List",
    };
    const gaps = uniqGaps([].concat(...cs.map((c) => c.kekurangan)));
    const body = [
      sec("Dasar perhitungan"),
      txt("Produk", (c) => H.esc(c.product.name)),
      txt("Product Category", (c) => H.esc(c.product.category)),
      txt("Material (varian BOM)", (c) => H.esc(c.material.name)),
      txt("Kelengkapan data", (c) => gapsLabel(c)),
      one("Price Reference Max (USD/Kg)", (c) => f(c.pr.usd, 4)),
      one("Kurs USD (Rp)", (c) => f(c.kurs)),
      one("Price Reference (Rp/Kg)", (c) => f(c.pr.idr)),
      one("Berat/Meter (kg/m, dari BOM)", (c) => f(c.bpm)),
      one("Total Man Hour (jam/meter)", (c) => f(c.totalManHour, 6)),
      one("Rate 1/3 Operator (Rp/jam, dibulatkan ke atas)", (c) => f(c.rateOp, 0)),
      one("Rate Borongan Helper (Rp/Meter)", (c) => f(c.boronganM)),
      one("Faktor Indirect Labor", (c) => f(c.faktor, 2)),
      sec("Material Cost"),
      L("material", "pc-sub"),
      sec("Biaya Proses"),
      dual("Man Power Cost = (Total Man Hour × Rate 1/3 Operator + Borongan) × Faktor", (c) => f(c.lines.manPower), (c) => f(c.linesKg.manPower)),
      dual("Bahan Pendukung Khusus (Rate Rp/Kg × Berat/Meter)", (c) => f(c.lines.bpk), (c) => f(c.linesKg.bpk)),
      dual("FOH (Rate Rp/Kg × Berat/Meter)", (c) => f(c.lines.foh), (c) => f(c.linesKg.foh)),
      L("proses", "pc-sub"),
      sec("Manufacturing Support"),
      dual("Consumables (Rate Rp/Kg × Berat/Meter)", (c) => f(c.lines.cons), (c) => f(c.linesKg.cons)),
      dual(`Scrap (${pctVal(PC.gen("scrap"))}% Material Cost)`, (c) => f(c.lines.scrap), (c) => f(c.linesKg.scrap)),
      dual("Biaya Koordinasi (Rate Rp/Kg × Berat/Meter)", (c) => f(c.lines.koord), (c) => f(c.linesKg.koord)),
      L("support", "pc-sub"),
      L("hpp", "pc-total"),
      sec("Other Cost (% × HPP)"),
      ...PC.OTHER_KEYS.map((key, i) => dual(`${H.esc(cs[0].lines.others[i].l)} (${pctVal(PC.gen(key))}%)`, (c) => f(c.lines.others[i].v), (c) => f(c.linesKg.others[i].v))),
      L("bottom", "pc-bottom"),
      dual(`Negotiation Allowance (% × Bottom Price)`, (c) => `${f(c.lines.nego)} <span class="muted">(${pctVal(c.negoPct)}%)</span>`, (c) => f(c.linesKg.nego)),
      L("priceList", "pc-total"),
      sec("Pricelist Produk"),
      one("Ukuran Produk (meter)", (c) => f(c.ukuran)),
      one("Berat per Pcs (kg) = Berat/Meter × Ukuran", (c) => f(c.beratPcs)),
      one("Pricelist Produk per pcs (= Ukuran × Price List/Meter)", (c) => `<strong>${f(c.pricelistProduk)}</strong>`),
      sec("Pembanding"),
      one("Pricelist HFG saat ini (Rp/Meter, contoh)", () => H.in("hfg", S().hfg, { num: 1, step: 100, struct: 1 })),
      one("Selisih Price List/Meter vs HFG", (c) => { const d = c.lines.priceList - (Number(S().hfg) || 0); const p = S().hfg ? (d / S().hfg) * 100 : 0; return `<span class="${d >= 0 ? "pc-up" : "pc-down"}">${d >= 0 ? "+" : ""}${f(d)} (${f(p, 1)}%)</span>`; }),
    ].join("");
    return `${gaps.length ? warnBox("Data belum lengkap — angka di bawah belum bisa diandalkan", gaps.map(gapItem).join("")) : ""}
      <div class="table-wrap"><table class="tbl" style="min-width:${340 + cs.length * 300}px"><thead>
        <tr><th rowspan="2">Komponen</th>${cs.map((c) => `<th class="num${hot(c)}" colspan="2">${H.esc(c.bom.uniq)}<br><span class="muted">tebal ${f(c.material.thickness)} mm</span></th>`).join("")}</tr>
        <tr>${cs.map((c) => `<th class="num${hot(c)}">Rp/Meter</th><th class="num${hot(c)}">Rp/Kg</th>`).join("")}</tr>
      </thead><tbody>${body}</tbody></table></div>
      <p class="pc-formula">HPP = Material Cost + Biaya Proses + Manufacturing Support · Other Cost = % × HPP · Bottom Price = HPP + seluruh Other Cost · Price List = Bottom Price + (Negotiation Allowance % × Bottom Price) · Rp/Kg = Rp/Meter ÷ Berat/Meter. Pembulatan: hanya Rate 1/3 Operator dibulatkan ke atas ke Rupiah penuh; komponen lain 2 desimal tanpa pembulatan. Angka contoh mockup, bukan angka acuan klien.</p>`;
  }
  const openCostingDetail = (uniq) => openModal({
    app: true, mode: "view", title: "Report Costing Detail/Produk" + (uniq ? " — " + uniq : ""), size: "xl", hideSave: true, cancelLabel: "Close",
    bodyFn: () => costingDetail(uniq),
  });
  pages.simulasi = {
    title: "Simulasi Costing",
    noSave: true,
    hint: "Hasil perhitungan otomatis per Produk × varian BOM: Material Cost → Biaya Proses → Manufacturing Support → HPP → Bottom Price → Price List, masing-masing dalam Rp/Meter dan Rp/Kg. Hanya baca; hasil dapat dimasukkan ke Draft Pricelist. Baris yang datanya belum lengkap diberi tanda peringatan.",
    render() {
      U().sf = U().sf || { c: "" };
      const all = S().boms.map((b) => PC.costing(b)).filter(Boolean);
      const cs = all.filter((c) => !U().sf.c || c.product.category === U().sf.c);
      const v = dtView("simulasi", cs.map((c) => ({ c })), ({ c }) => [c.product.category, c.product.name, c.material.name, c.bom.uniq].join(" "));
      const rows = v.rows.map(({ c }, n) => `<tr><td class="ap-c">${v.from + n + 1}</td><td>${H.esc(up(c.product.category))}</td><td>${H.esc(up(c.product.name))}</td><td>${H.esc(up(c.material.name))}</td><td>${H.esc(c.bom.uniq)}</td>
        <td class="ap-r">${f(c.bottomMeter)}</td><td class="ap-r"><strong>${f(c.hargaMeter)}</strong></td><td class="ap-r"><strong>${f(c.hargaKg)}</strong></td>
        <td>${gapsLabel(c)}</td><td>${H.esc(c.lastUpdated)}</td>
        <td class="ap-c ap-act">${iconBtn("view", "Report Costing Detail", "eye", "info", `data-u="${H.esc(c.bom.uniq)}"`)} <button type="button" class="ap-btn ap-btn--success ap-btn--sm" title="Masukkan ke Draft Pricelist" data-act="addDraft" data-u="${H.esc(c.bom.uniq)}">${ico("plus")} Draft</button></td></tr>`).join("")
        || `<tr><td class="ap-empty" colspan="11">No data available in table</td></tr>`;
      const gr = PC.gapReport();
      const warn = gr.length ? warnBox(`${gr.length} dari ${all.length} varian BOM datanya belum lengkap — harga belum bisa diandalkan`, uniqGaps([].concat(...gr.map((x) => x.gaps))).map(gapItem).join("")) : "";
      return warn + `<div class="ap-card ap-card--shadow">
        <div class="ap-card__head">
          <div class="ap-gap" style="margin-bottom:12px; justify-content:space-between">
            <div class="ap-gap"><button type="button" class="ap-btn ap-btn--primary" data-act="update">${ico("save")} Update Costing</button>
              <button type="button" class="ap-btn ap-btn--info" data-act="excel">${ico("excel")} Excel</button></div>
            <div class="ap-gap"><label class="ap-lbl">Range Date</label> ${apIn("costRange", S().costRange || PC.today(), { cls: "ap-in--sm", date: 1 })}</div>
          </div>
          <div class="ap-filters" style="margin-top:0">${apSel("ui.sf.c", U().sf.c, selOpts("ALL PRODUCT CATEGORY", productCats(), U().sf.c), { struct: 1 })}</div>
        </div>
        <div class="ap-card__body">${dtTop("simulasi", v.st)}
          ${apTable("ap-table--striped ap-table--hover ap-table--sm ap-table--fit", [{ t: "#", c: "ap-c", w: "40px" }, "Product Category", "Product Name", "Varian Material", "Uniq ID", { t: "Bottom Price/Meter", c: "ap-r" }, { t: "Price List/Meter", c: "ap-r" }, { t: "Price List/Kg", c: "ap-r" }, "Data", "Last Updated", { t: "Action", c: "ap-c", w: "140px" }], rows)}
          ${dtBottom("simulasi", v)}
          <div class="ap-hint">Pembulatan: hanya Rate 1/3 Operator dibulatkan ke atas ke Rupiah penuh; komponen lain 2 desimal tanpa pembulatan.</div></div></div>`;
    },
    act: Object.assign({
      view(d) { openCostingDetail(d.u); },
      addDraft(d) {
        const bom = S().boms.find((b) => b.uniq === d.u);
        if (!bom) return;
        let pl = S().pricelists.find((x) => x.status === "Draft");
        if (!pl) {
          pl = { id: PC.uid("PL"), no: PC.nextNo(S().pricelists, "PL"), status: "Draft", bomIds: [], createdAt: PC.today(), submittedAt: "", decidedBy: "", decidedAt: "", rejectReason: "" };
          S().pricelists.push(pl);
        }
        if (pl.bomIds.includes(bom.id)) { window.PC_SHELL.toast(`${d.u} sudah ada di Draft ${pl.no}.`, "warn"); return; }
        pl.bomIds.push(bom.id);
        PC.save();
        window.PC_SHELL.toast(`${d.u} dimasukkan ke Draft Pricelist ${pl.no}.`, "ok");
      },
      update() { S().boms.forEach((b) => { b.updated = PC.today(); }); PC.save(); window.PC_SHELL.toast("Costing diperbarui (Last Updated = hari ini).", "ok"); },
      excel() { window.PC_SHELL.toast("Download Excel — contoh mockup (tidak menghasilkan file).", "ok"); },
    }, DT_ACT),
  };

  /* ---------- 13. Pricelist (UC-16, UC-17, FR-58) — daftar + filter status seperti purchase_request, dokumen seperti View PR ---------- */
  const STATUS_LBL = { "Draft": "secondary", "Menunggu Approval": "warning", "Approved": "success", "Rejected": "danger" };
  const pill = (st) => `<label class="ap-label ap-label--${STATUS_LBL[st] || "secondary"}">${H.esc(st)}</label>`;
  const plTable = (rows, del) => apTable("ap-table--bordered ap-table--striped ap-table--sm",
    ["Produk", "Varian BOM", "Product Category", { t: "Bottom Price/Meter", c: "ap-r" }, { t: "Bottom Price/Kg", c: "ap-r" }, { t: "Neg. Allowance", c: "ap-r" }, { t: "Price List/Meter", c: "ap-r" }, { t: "Price List/Kg", c: "ap-r" }, "Data"].concat(del ? [{ t: "", w: "44px" }] : []),
    rows.map((r, k) => `<tr><td>${H.esc(up(r.product))}</td><td>${H.esc(r.uniq)}</td><td>${H.esc(up(r.category))}</td>
      <td class="ap-r">${f(r.bottomM)}</td><td class="ap-r">${f(r.bottomKg)}</td><td class="ap-r">${f(pctVal(r.negoPct))}%</td>
      <td class="ap-r"><strong>${f(r.plM)}</strong></td><td class="ap-r"><strong>${f(r.plKg)}</strong></td>
      <td>${r.gaps && r.gaps.length ? `<span class="ap-label ap-label--warning" title="${H.esc(r.gaps.join(" "))}">&#9888; ${r.gaps.length} kekurangan</span>` : '<span class="ap-label ap-label--success">Lengkap</span>'}</td>${del ? `<td class="ap-c">${del(k)}</td>` : ""}</tr>`).join("")
    || `<tr><td class="ap-empty" colspan="${del ? 10 : 9}">Belum ada baris.</td></tr>`);
  const reasonText = (pl) => (!pl.rejectReason ? "—" : (pl.status === "Rejected" ? "" : "Sebelumnya: ") + H.esc(pl.rejectReason));
  // Pricelist tidak boleh diajukan bila ada baris dengan kekurangan data (keputusan desain mockup, 2026-10-20).
  const submitBlock = (pl) => {
    const rows = PC.plRows(pl);
    if (!rows.length) return "Draft belum berisi Produk × varian BOM.";
    const bad = rows.filter((r) => r.gaps && r.gaps.length);
    if (!bad.length) return null;
    return `Tidak dapat diajukan: ${bad.length} baris belum lengkap datanya. ` + bad.slice(0, 3).map((r) => `${r.uniq}: ${r.gaps[0]}`).join(" | ") + (bad.length > 3 ? " | …" : "") + " Lengkapi data di menu terkait (lihat peringatan di Simulasi Costing), lalu ajukan lagi.";
  };
  const doSubmit = (pl) => {
    PC.snapPricelist(pl);
    pl.status = "Menunggu Approval"; pl.submittedAt = PC.today(); pl.decidedBy = ""; pl.decidedAt = "";
  };
  const plDoc = (i, view) => {
    const pl = S().pricelists[i]; if (!pl) return "";
    const draft = pl.status === "Draft";
    const rows = PC.plRows(pl);
    const avail = S().boms.filter((b) => !pl.bomIds.includes(b.id) && PC.costing(b));
    const bad = rows.filter((r) => r.gaps && r.gaps.length);
    return `${draft && bad.length ? warnBox(`${bad.length} baris belum lengkap — Pricelist tidak dapat diajukan`, uniqGaps([].concat(...bad.map((r) => r.gapItems || r.gaps.map((t) => ({ text: t, file: "pc-simulasi.html", label: "Simulasi Costing" }))))).map(gapItem).join("")) : ""}
      ${apRow(lb("No. Pricelist"), fld("c4", ro(pl.no)), lb("Status"), fld("c4", `<div class="ap-in ap-in--ro">${pill(pl.status)}</div>`))}
      ${apRow(lb("Dibuat"), fld("c4", ro(pl.createdAt)), lb("Diajukan"), fld("c4", ro(pl.submittedAt)))}
      ${pl.decidedAt ? apRow(lb("Keputusan"), fld("c4", ro(pl.decidedBy + " · " + pl.decidedAt)), lb("Alasan Reject"), fld("c4", ro(pl.rejectReason))) : (pl.rejectReason ? apRow(lb("Alasan Reject sebelumnya"), fld("c10", ro(pl.rejectReason))) : "")}
      <hr class="ap-hr"><h6>Produk × Varian BOM</h6>
      ${plTable(rows, draft && !view ? (k) => H.del(`pricelists.${i}.bomIds`, k) : null)}
      ${draft && !view ? `<div class="ap-gap" style="margin-top:12px">
          <select id="plAddSel" class="ap-sel ap-sel--sm"><option value="">— Tambah Varian BOM —</option>${avail.map((b) => `<option value="${b.id}">${H.esc(b.uniq)}</option>`).join("")}</select>
          <button type="button" class="ap-btn ap-btn--success ap-btn--sm" data-act="addrow" data-i="${i}">${ico("plus")} Tambah</button></div>
        <div class="ap-hint">Harga dihitung dari Simulasi Costing terkini dan dibekukan saat diajukan ke Management. Draft tersimpan otomatis.</div>`
        : `<div class="ap-hint">Harga dibekukan saat pricelist diajukan.</div>`}`;
  };
  const openPl = (mode, i) => {
    const pl = S().pricelists[i];
    const canSubmit = mode === "edit" && pl && pl.status === "Draft";
    openModal({
      app: true, mode, title: (mode === "view" ? "View" : "Edit") + " Pricelist — " + ((pl || {}).no || ""), size: "xl",
      hideSave: !canSubmit, saveLabel: "Ajukan ke Management", cancelLabel: "Close",
      confirmTitle: "Ajukan ke Management?", confirmText: "Harga dibekukan dan tidak bisa diubah sampai diputuskan.", confirmBtn: "Ya, Ajukan!",
      bodyFn: () => plDoc(i, mode === "view"),
      validate() { return submitBlock(S().pricelists[i]); },
      onSave() { doSubmit(S().pricelists[i]); },
      doneMsg: "Pricelist diajukan ke Management.",
    });
  };
  pages.pricelist = {
    title: "Pricelist",
    noSave: true, // alur lintas peran: setiap aksi langsung tersimpan
    hint: "Sales menyusun Draft Pricelist per Produk × varian BOM dari hasil Simulasi Costing, lalu mengajukannya ke Management. Pricelist yang ditolak dikembalikan ke Draft untuk dihitung ulang. Pricelist dengan data costing yang belum lengkap tidak dapat diajukan.",
    render() {
      U().plf = U().plf || "";
      const all = S().pricelists.map((pl, i) => ({ pl, i })).filter((x) => !U().plf || x.pl.status === U().plf);
      const v = dtView("pricelist", all, ({ pl }) => [pl.no, pl.status, pl.rejectReason].join(" "));
      const rows = v.rows.map(({ pl, i }, n) => {
        const draft = pl.status === "Draft", rej = pl.status === "Rejected";
        const bad = PC.plRows(pl).filter((r) => r.gaps && r.gaps.length).length;
        return `<tr><td class="ap-c">${v.from + n + 1}</td><td>${H.esc(pl.no)}</td><td>${H.esc(pl.createdAt)}</td><td class="ap-r">${PC.plRows(pl).length}</td><td>${pill(pl.status)}</td>
          <td>${H.esc(pl.submittedAt || "—")}</td><td>${pl.decidedAt ? H.esc(pl.decidedBy + " · " + pl.decidedAt) : "—"}</td><td>${reasonText(pl)}</td>
          <td>${bad ? `<span class="ap-label ap-label--warning">&#9888; ${bad} baris belum lengkap</span>` : '<span class="ap-label ap-label--success">Lengkap</span>'}</td>
          <td class="ap-c">${iconBtn(draft ? "edit" : "view", draft ? "Buka Draft" : "View", draft ? "edit" : "eye", draft ? "primary" : "info", `data-i="${i}"`)}
            ${draft ? `<button type="button" class="ap-btn ap-btn--success ap-btn--sm" data-act="ajukan" data-i="${i}">Ajukan</button> ` + iconBtn("delpl", "Delete", "trash", "danger", `data-i="${i}"`) : ""}
            ${rej ? `<button type="button" class="ap-btn ap-btn--warning ap-btn--sm" data-act="susun" data-i="${i}">Hitung Ulang</button>` : ""}</td></tr>`;
      }).join("") || `<tr><td class="ap-empty" colspan="10">No data available in table</td></tr>`;
      return gapBanner(["flow", "bpkfoh", "borongan", "nego", "price", "kurs", "standard", "general", "mpr", "faktor"], "Ada data costing yang belum lengkap — Pricelist terkait tidak dapat diajukan")
        + `<div class="ap-card ap-card--shadow">
        <div class="ap-card__head ap-card__head--bar">
          <div style="min-width:220px">${apSel("ui.plf", U().plf, [{ v: "", l: "- Filter Status -" }, "Draft", "Menunggu Approval", "Approved", "Rejected"].map((x) => (typeof x === "string" ? { v: x, l: x } : x)), { struct: 1 })}</div>
          <button type="button" class="ap-btn ap-btn--primary" data-act="newDraft">${ico("plus")} Buat Draft Pricelist</button>
        </div>
        <div class="ap-card__body">${dtTop("pricelist", v.st)}
          ${apTable("ap-table--bordered ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "No. Pricelist", "Dibuat", { t: "Jumlah Item", c: "ap-r" }, "Status", "Diajukan", "Keputusan Management", "Alasan Reject", "Data", { t: "Option", c: "ap-c", w: "230px" }], rows)}
          ${dtBottom("pricelist", v)}</div></div>`;
    },
    act: Object.assign({
      newDraft() {
        let i = S().pricelists.findIndex((x) => x.status === "Draft");
        if (i < 0) {
          S().pricelists.push({ id: PC.uid("PL"), no: PC.nextNo(S().pricelists, "PL"), status: "Draft", bomIds: S().boms.filter((b) => PC.costing(b)).map((b) => b.id), createdAt: PC.today(), submittedAt: "", decidedBy: "", decidedAt: "", rejectReason: "" });
          i = S().pricelists.length - 1;
        } else window.PC_SHELL.toast("Masih ada Draft Pricelist; dibuka untuk dilanjutkan.", "warn");
        PC.save();
        openPl("edit", i);
      },
      edit(d) { openPl("edit", Number(d.i)); },
      view(d) { openPl("view", Number(d.i)); },
      delpl(d) {
        const i = Number(d.i);
        confirmDel({}).then((ok) => { if (!ok) return; S().pricelists.splice(i, 1); PC.save(); window.PC_SHELL.render(); doneDel(); });
      },
      addrow(d) {
        const sel = document.getElementById("plAddSel"), pl = S().pricelists[Number(d.i)];
        if (sel && sel.value && pl && !pl.bomIds.includes(sel.value)) pl.bomIds.push(sel.value);
      },
      ajukan(d) {
        const i = Number(d.i), pl = S().pricelists[i];
        if (!pl) return;
        const err = submitBlock(pl);
        if (err) { dlg({ icon: "error", title: "Pricelist tidak dapat diajukan", text: err }); return; }
        dlg({ icon: "warning", title: "Ajukan ke Management?", text: "Harga dibekukan dan tidak bisa diubah sampai diputuskan.", confirmText: "Ya, Ajukan!", cancelText: "Batal" }).then((ok) => {
          if (!ok) return;
          doSubmit(pl); PC.save(); window.PC_SHELL.render();
          dlg({ icon: "success", title: "Success", text: `Pricelist ${pl.no} diajukan ke Management.` });
        });
      },
      susun(d) {
        const pl = S().pricelists[Number(d.i)];
        if (!pl) return;
        pl.status = "Draft"; delete pl.snap; pl.submittedAt = ""; pl.decidedBy = ""; pl.decidedAt = "";
        window.PC_SHELL.toast(`Pricelist ${pl.no} kembali ke Draft; hitung ulang lalu ajukan lagi.`, "ok");
      },
    }, DT_ACT),
  };

  /* ---------- 14. Approval Pricelist — Management (UC-19, FR-58) — antrean seperti approval_po ---------- */
  const approvalForm = (i) => {
    const pl = S().pricelists[i]; if (!pl) return "";
    const rej = S().ui.decision === "Reject";
    return `${apRow(lb("No. Pricelist"), fld("c4", ro(pl.no)), lb("Diajukan"), fld("c4", ro(pl.submittedAt)))}
      <hr class="ap-hr"><h6>Draft Pricelist (harga saat diajukan)</h6>
      ${plTable(PC.plRows(pl), null)}
      <hr class="ap-hr"><h6>Keputusan</h6>
      ${apRow(lb("Keputusan", 1), fld("c10", `<div class="ap-radios">${["Approve", "Reject"].map((v) => `<label><input type="radio" name="ui.decision" data-path="ui.decision" data-t="text" data-struct value="${v}"${S().ui.decision === v ? " checked" : ""}> ${v}</label>`).join("")}</div>`))}
      ${apRow(lb("Alasan Reject", rej), fld("c10", `<textarea class="pc-in ap-in" rows="3" data-path="ui.reason" data-t="text"${rej ? "" : ' placeholder="Hanya diisi bila Reject"'}>${H.esc(S().ui.reason)}</textarea>${rej ? '<div class="ap-hint">Alasan Reject wajib diisi dan disimpan.</div>' : ""}`))}`;
  };
  const openApproval = (i) => {
    S().ui.decision = "Approve"; S().ui.reason = "";
    openModal({
      app: true, mode: "edit", title: "Periksa Draft Pricelist — " + ((S().pricelists[i] || {}).no || ""), size: "xl",
      saveLabel: "Simpan Keputusan", confirmTitle: "Simpan keputusan?", confirmText: "Keputusan Management tidak dapat diubah setelah disimpan.", confirmBtn: "Ya, Simpan!",
      doneMsg: "Keputusan Management tersimpan.",
      bodyFn: () => approvalForm(i),
      validate() {
        const ui = S().ui;
        if (ui.decision === "Reject" && !String(ui.reason || "").trim()) return "Alasan Reject wajib diisi.";
        return null;
      },
      onSave() {
        const pl = S().pricelists[i], ui = S().ui;
        pl.status = ui.decision === "Approve" ? "Approved" : "Rejected";
        pl.decidedBy = "Management"; pl.decidedAt = PC.today();
        pl.rejectReason = ui.decision === "Reject" ? String(ui.reason).trim() : "";
        return true;
      },
    });
  };
  pages.approval = {
    title: "Approval Pricelist",
    noSave: true, // alur lintas peran: setiap aksi langsung tersimpan
    hint: "Management memeriksa Draft Pricelist yang diajukan Sales, lalu memutuskan Approve atau Reject. Reject wajib disertai alasan dan mengembalikan pricelist ke Sales untuk dihitung ulang.",
    render() {
      const order = { "Menunggu Approval": 0, "Approved": 1, "Rejected": 1 };
      const items = S().pricelists.map((pl, i) => ({ pl, i })).filter((x) => x.pl.status !== "Draft").sort((a, b) => order[a.pl.status] - order[b.pl.status]);
      const v = dtView("approval", items, ({ pl }) => [pl.no, pl.status, pl.rejectReason].join(" "));
      const rows = v.rows.map(({ pl, i }, k) => `<tr><td class="ap-c">${v.from + k + 1}</td><td>${H.esc(pl.no)}</td><td>${H.esc(pl.submittedAt || "—")}</td>
        <td class="ap-r">${PC.plRows(pl).length}</td><td>${pill(pl.status)}</td><td>${pl.decidedAt ? H.esc(pl.decidedBy + " · " + pl.decidedAt) : "—"}</td><td>${reasonText(pl)}</td>
        <td class="ap-c">${pl.status === "Menunggu Approval"
          ? `<button type="button" class="ap-btn ap-btn--primary ap-btn--sm" data-act="periksa" data-i="${i}">Approve / Reject</button>`
          : iconBtn("view", "View", "eye", "info", `data-i="${i}"`)}</td></tr>`).join("") || `<tr><td class="ap-empty" colspan="8">No data available in table</td></tr>`;
      const pending = items.filter((x) => x.pl.status === "Menunggu Approval").length;
      return `<div class="ap-card ap-card--shadow">${apHead("Daftar Pricelist yang Diajukan", pending ? `${pending} pricelist menunggu keputusan.` : "Tidak ada pricelist yang menunggu keputusan.", `Total: ${items.length} Pricelist`, "")}
        <div class="ap-card__body">${dtTop("approval", v.st)}
          ${apTable("ap-table--bordered ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "No. Pricelist", "Diajukan", { t: "Jumlah Item", c: "ap-r" }, "Status", "Keputusan", "Alasan Reject", { t: "Action", c: "ap-c", w: "160px" }], rows)}
          ${dtBottom("approval", v)}</div></div>`;
    },
    act: Object.assign({
      periksa(d) { openApproval(Number(d.i)); },
      view(d) { openPl("view", Number(d.i)); },
    }, DT_ACT),
  };

  /* ---------- 15. Product Catalog (UC-18, FR-58) — daftar dokumen seperti closed_po ---------- */
  const openCatalog = (i) => {
    const ct = S().catalogs[i]; if (!ct) return;
    const pl = S().pricelists.find((x) => x.id === ct.pricelistId) || {};
    openModal({
      app: true, mode: "view", title: "View Product Catalog — " + ct.no, size: "xl", hideSave: true, cancelLabel: "Close",
      bodyFn: () => `${apRow(lb("No. Katalog"), fld("c4", ro(ct.no)), lb("Asal Pricelist"), fld("c4", ro(pl.no || "—")))}
        ${apRow(lb("Dirilis Oleh"), fld("c4", ro(ct.releasedBy)), lb("Dirilis Pada"), fld("c4", ro(ct.releasedAt)))}
        <hr class="ap-hr"><h6>Harga yang disetujui (snapshot)</h6>${plTable(ct.rows || [], null)}`,
    });
  };
  pages.catalog = {
    title: "Product Catalog",
    noSave: true, // alur lintas peran: setiap aksi langsung tersimpan
    hint: "Setelah Pricelist disetujui Management, Sales merilis Product Catalog berisi harga yang disetujui. Katalog disimpan sebagai data (snapshot harga) dan diteruskan ke proses Sales. Satu pricelist Approved boleh dirilis berkali-kali; setiap rilis membuat katalog baru.",
    render() {
      const ready = S().pricelists.map((pl, i) => ({ pl, i })).filter((x) => x.pl.status === "Approved");
      const rrows = ready.map(({ pl, i }, k) => `<tr><td class="ap-c">${k + 1}</td><td>${H.esc(pl.no)}</td><td class="ap-r">${PC.plRows(pl).length}</td><td>${H.esc(pl.decidedAt || "—")}</td><td class="ap-r">${S().catalogs.filter((c) => c.pricelistId === pl.id).length}</td>
        <td class="ap-c"><button type="button" class="ap-btn ap-btn--success ap-btn--sm" data-act="rilis" data-i="${i}">${ico("plus")} Rilis Product Catalog</button></td></tr>`).join("")
        || `<tr><td class="ap-empty" colspan="6">Belum ada pricelist Approved.</td></tr>`;
      const v = dtView("catalog", S().catalogs.map((ct, i) => ({ ct, i })), ({ ct }) => [ct.no, ct.releasedBy, ct.releasedAt].join(" "));
      const crows = v.rows.map(({ ct, i }, n) => {
        const pl = S().pricelists.find((x) => x.id === ct.pricelistId) || {};
        return `<tr><td class="ap-c">${v.from + n + 1}</td><td>${H.esc(ct.no)}</td><td>${H.esc(pl.no || "—")}</td><td class="ap-r">${(ct.rows || []).length}</td>
          <td>${H.esc(ct.releasedBy)}</td><td>${H.esc(ct.releasedAt)}</td><td class="ap-c">${iconBtn("view", "View", "eye", "info", `data-i="${i}"`)}</td></tr>`;
      }).join("") || `<tr><td class="ap-empty" colspan="7">No data available in table</td></tr>`;
      return `<div class="ap-card ap-card--shadow">${apHead("Pricelist Approved — siap dirilis", "Hanya pricelist berstatus Approved yang dapat dirilis; satu pricelist boleh dirilis berkali-kali dan setiap rilis membuat katalog baru.", "", "")}
          <div class="ap-card__body">${apTable("ap-table--bordered ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "No. Pricelist", { t: "Jumlah Item", c: "ap-r" }, "Disetujui", { t: "Sudah Dirilis (kali)", c: "ap-r" }, { t: "Option", c: "ap-c", w: "210px" }], rrows)}</div></div>
        <div class="ap-card ap-card--shadow">${apHead("Daftar Product Catalog", "Snapshot harga yang disetujui, disimpan sebagai data", `Total: ${S().catalogs.length} Katalog`, "")}
          <div class="ap-card__body">${dtTop("catalog", v.st)}
            ${apTable("ap-table--bordered ap-table--striped ap-table--hover", [{ t: "#", c: "ap-c", w: "50px" }, "No. Katalog", "Asal Pricelist", { t: "Jumlah Produk", c: "ap-r" }, "Dirilis Oleh", "Dirilis Pada", { t: "Action", c: "ap-c", w: "90px" }], crows)}
            ${dtBottom("catalog", v)}</div></div>`;
    },
    act: Object.assign({
      rilis(d) {
        const pl = S().pricelists[Number(d.i)];
        if (!pl || pl.status !== "Approved") return;
        const now = new Date();
        S().catalogs.push({ id: PC.uid("CT"), no: PC.nextNo(S().catalogs, "KAT"), pricelistId: pl.id, releasedBy: "Sales", releasedAt: PC.today() + " " + now.toTimeString().slice(0, 5), rows: (pl.snap || []).map((r) => Object.assign({}, r)) });
        window.PC_SHELL.toast("Product Catalog dirilis dan diteruskan ke proses Sales.", "ok");
      },
      view(d) { openCatalog(Number(d.i)); },
    }, DT_ACT),
  };
})();
