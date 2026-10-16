/* ============================================================
   pc-data.js — Data & mesin hitung Product Costing (mockup).
   Seed diambil dari input/Rev.1 Ide Costing Kg konversi ke Meter.xlsx,
   sheet "Simulasi Program" (nilai contoh). Diekspos sebagai window.PC.
   State disimpan di localStorage (key mockPC.v1); tidak ada backend.
   Rumus mengikuti docs/fr-nfr.md FR-40..FR-46.
   ============================================================ */
(function () {
  "use strict";

  const KEY = "mockPC.v1";
  const MONTHS = ["Januari", "Februari", "Maret", "April", "Mei", "Juni"];

  const seed = () => ({
    // 1. Master Produk (FR-37) — hierarki 4 level + atribut master mengikuti
    //    sheet "Master Product". Berat per pcs tidak disimpan; diturunkan dari
    //    Berat/Meter varian BOM × Panjang.
    products: [
      {
        id: "PR1", kode: "P425000429", status: "Aktif",
        type: "Atap", category: "Spandek", jenis: "Polos",
        name: "HARAPAN SPANDEK POLOS ECO 0.25 X 6 M", codeMaster: "SM1020302012",
        lebar: 750, tebal: 0.25, panjang: 6, retail: "",
        productCode: "", tradeName: "", packUnit: "Roll", conversion: 1, uom: "Meter",
        maxStock: 0, minStock: 0, moq: 0, msds: "",
        dimP: 6, dimL: 0.75, dimT: 0.025, beratUnit: 0, cbm: 0,
      },
    ],
    // 2. Master Material (FR-38) — atribut mengikuti sheet "Master Material".
    //    Berat/Meter (bpm) dipertahankan: load-bearing untuk Material Cost dan
    //    Berat per Pcs (tidak tampil di form concept, dikonfirmasi ke user).
    //    Width 1219 (koreksi salah ketik 914).
    materials: [
      {
        id: "MT1", kode: "1GLMB014AZ70", status: "Aktif", metal: "Galvalum", slit: "Mother", boron: "Boron",
        name: "GALVALUM MOTHER BORON 0.14-1219-AZ70-G550-", hsCode: "7210.49.00",
        alias: "", width: 1219, coating: "AZ70", thickness: 0.14, color: "",
        hardness: "", tensile: "G550", noAccurate: "", packUnit: "Kg", conversion: 1,
        uom: "Kg", maxStock: 0, minStock: 0, msds: "", altSupplier: "", bpm: 1.34,
      },
      {
        id: "MT2", kode: "1GLMB015AZ70", status: "Aktif", metal: "Galvalum", slit: "Mother", boron: "Boron",
        name: "GALVALUM MOTHER BORON 0.15-1219-AZ70-G550-", hsCode: "7210.49.00",
        alias: "", width: 1219, coating: "AZ70", thickness: 0.15, color: "",
        hardness: "", tensile: "G550", noAccurate: "", packUnit: "Kg", conversion: 1,
        uom: "Kg", maxStock: 0, minStock: 0, msds: "", altSupplier: "", bpm: 1.44,
      },
    ],
    // Master Kurs (FR-39)
    kurs: [{ id: "K1", currency: "USD", kurs: 17863, updated: "2026-10-06", by: "Accounting" }],
    // 3. Price Reference (FR-40): history harga USD/kg per bulan (null = kosong)
    history: {
      MT1: [0.8, null, 0.81, null, null, 0.737],
      MT2: [0.79, null, 0.8, null, null, 0.725],
    },
    priceUse: { MT1: "terakhir", MT2: "terakhir" }, // terakhir | rata
    // Metadata Price Reference per material untuk Tampilan Depan (list): tanggal
    // berlaku s/d (expired) + status. Sheet "Price Reference Mat" tampilan depan.
    priceMeta: {
      MT1: { expired: "2025-05-18", status: "Aktif" },
      MT2: { expired: "2025-05-18", status: "Aktif" },
    },
    // 4. BOM (FR-41): satu baris per varian (Uniq ID). qty = jumlah material
    //    per produk (sheet "BOM" Daftar Material); Material Cost mockup memakai
    //    material varian (dikonfirmasi ke user).
    boms: [
      { id: "B1", uniq: "Type 001.1", productId: "PR1", materialId: "MT1", qty: 1, updated: "2026-10-06", by: "Engineering" },
      { id: "B2", uniq: "Type 001.2", productId: "PR1", materialId: "MT2", qty: 1, updated: "2026-10-06", by: "Engineering" },
    ],
    // 5. Flow Process & Cycletime per Product Category (FR-42); ct = menit per meter
    flow: [
      { id: "F1", category: "Spandek", area: "Spandek Area", process: "Cutting Spandek", machine: "Spandek", ct: 1 / 24, mp: 1, updated: "2026-09-01", by: "admin" },
    ],
    // 6. Man Power Rate (FR-43)
    mpr: {
      salary: [
        { n: "Gaji Pokok", v: 4828884, k: "per bulan" },
        { n: "Tunjangan Keluarga", v: 15000, k: "per bulan" },
        { n: "Tunjangan Jabatan", v: 100000, k: "per bulan" },
        { n: "THR", v: 402407, k: "per bulan" },
        { n: "Cuti", v: 193155.36, k: "per bulan" },
        { n: "Uang Makan", v: 200000, k: "per 25 hari" },
        { n: "Transport", v: 225000, k: "per 25 hari" },
      ],
      bpjs: [
        { n: "Jaminan Kecelakaan Kerja", v: 40671.35, k: "0,89% per bulan" },
        { n: "Jaminan Kematian", v: 13709.45, k: "0,3% per bulan" },
        { n: "Jaminan Hari Tua", v: 169083.16, k: "3,7% per bulan" },
        { n: "Jaminan Pensiun", v: 91396.3, k: "2% per bulan" },
        { n: "Jaminan Kesehatan", v: 182792.6, k: "4% per bulan" },
      ],
      lain: [
        { n: "Seragam (2 pc/thn)", v: 29333.33, k: "Prorate/bln" },
        { n: "Sepatu (1 psg/tahun)", v: 26250, k: "Prorate/bln" },
        { n: "Helm (1 pcs/tahun)", v: 10000, k: "Prorate/bln" },
        { n: "Sarung Tangan (1 psg/2 hari)", v: 14300, k: "Per bulan" },
        { n: "Masker (1 pc/hari x 25 hari)", v: 10000, k: "Per bulan" },
      ],
      jam: 173,
      faktor: 1.5,
    },
    // 7. Rate Bahan Pendukung Khusus & FOH per Product Category, Rp/meter (FR-44)
    bpkfoh: [
      { id: "R1", category: "Spandek", bpk: 150, foh: 400 },
      { id: "R2", category: "BONDECK", bpk: 150, foh: 400 },
    ],
    // 8. General Rate (FR-45): u = pct (pecahan) | rpkg
    general: [
      { key: "cons", group: "Manufacturing Support", label: "Consumables (Umum)", basis: "% x Material Cost", u: "pct", v: 0.05 },
      { key: "scrap", group: "Manufacturing Support", label: "Scrap", basis: "% x Material Cost", u: "pct", v: 0.02 },
      { key: "koord", group: "Manufacturing Support", label: "Biaya Koordinasi", basis: "Rp/Kg x Berat/Meter", u: "rpkg", v: 200 },
      { key: "o_sales", group: "Other Cost", label: "Sales, Marketing", basis: "% x HPP", u: "pct", v: 0 },
      { key: "o_transport", group: "Other Cost", label: "Transport", basis: "% x HPP", u: "pct", v: 0 },
      { key: "o_finance", group: "Other Cost", label: "Finance, Admin", basis: "% x HPP", u: "pct", v: 0 },
      { key: "o_mgmt", group: "Other Cost", label: "Management Fee", basis: "% x HPP", u: "pct", v: 0 },
      { key: "o_interest", group: "Other Cost", label: "Interest", basis: "% x HPP", u: "pct", v: 0 },
      { key: "o_profit", group: "Other Cost", label: "Profit", basis: "% x HPP", u: "pct", v: 0.05 },
      { key: "nego", group: "Selling Price", label: "Negotiation Allowance (Extra Margin)", basis: "% x Bottom Price", u: "pct", v: 0.05 },
    ],
    hfg: 27400, // pembanding "Pricelist HFG saat ini", Rp/Meter — hanya contoh
    costRange: "", // Range Date filter pada Tampilan Depan Product Costing (contoh)
  });

  let state = load();
  function load() {
    try {
      const s = JSON.parse(localStorage.getItem(KEY));
      if (s && typeof s === "object") return Object.assign(seed(), s);
    } catch (e) { /* abaikan */ }
    return seed();
  }
  const save = () => { try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) { /* abaikan */ } };
  const reset = () => { try { localStorage.removeItem(KEY); } catch (e) { /* abaikan */ } state = seed(); };
  const reload = () => { state = load(); };

  const sum = (a, f) => a.reduce((t, x) => t + (Number(f ? f(x) : x) || 0), 0);
  const uid = (p) => p + Date.now().toString(36) + Math.floor(Math.random() * 1e3).toString(36);
  const today = () => new Date().toISOString().slice(0, 10);

  const productById = (id) => state.products.find((p) => p.id === id) || null;
  const materialById = (id) => state.materials.find((m) => m.id === id) || null;
  const kursOf = (cur) => { const k = state.kurs.find((x) => x.currency === cur); return k ? Number(k.kurs) : 0; };
  const categories = () => {
    const out = [];
    state.products.concat(state.flow, state.bpkfoh).forEach((x) => { if (x.category && !out.includes(x.category)) out.push(x.category); });
    return out;
  };

  /* ---- Auto-generate nama gabungan (bisa dioverride manual) ----
     Product Master = Type + Category + Jenis + Tebal + Lebar + Panjang.
     Material Name  = Metal + Slit + Boron + Tebal-Lebar-Coating-Tensile- (format Excel). */
  const nz = (v) => (v == null ? "" : String(v).trim());
  function productName(p) {
    if (!p) return "";
    const words = [nz(p.type), nz(p.category), nz(p.jenis)].filter(Boolean).join(" ").toUpperCase();
    const dims = [nz(p.tebal), nz(p.lebar), nz(p.panjang)].filter((x) => x !== "");
    const dimStr = dims.length ? dims.join(" X ") + " M" : "";
    return [words, dimStr].filter(Boolean).join(" ").trim();
  }
  function materialName(m) {
    if (!m) return "";
    const words = [nz(m.metal), nz(m.slit), nz(m.boron)].filter(Boolean).join(" ").toUpperCase();
    const spec = [nz(m.thickness), nz(m.width), nz(m.coating), nz(m.tensile)].join("-") + "-";
    return [words, spec].filter((x) => x && x !== "----").join(" ").trim();
  }
  // Terapkan auto-generate bila record belum di-override manual (nameManual != true).
  const applyProductName = (p) => { if (p && !p.nameManual) p.name = productName(p); return p; };
  const applyMaterialName = (m) => { if (m && !m.nameManual) m.name = materialName(m); return m; };

  /* ---- Price Reference (FR-40) ---- */
  function history(mid) { return state.history[mid] || [null, null, null, null, null, null]; }
  function priceRef(mid) {
    const h = history(mid).filter((v) => v !== null && v !== "" && !isNaN(v)).map(Number);
    const latest = h.length ? h[h.length - 1] : 0;
    const avg = h.length ? sum(h) / h.length : 0;
    const use = state.priceUse[mid] || "terakhir";
    const usd = use === "terakhir" ? latest : avg;
    return { latest, avg, use, usd, idr: usd * kursOf("USD") };
  }
  // Metadata list Price Reference per material (expired + status efektif).
  function priceMetaOf(mid) {
    const m = (state.priceMeta && state.priceMeta[mid]) || { expired: "", status: "Aktif" };
    let status = m.status || "Aktif";
    if (m.expired) { const exp = new Date(m.expired); if (!isNaN(exp) && exp < new Date()) status = "Expired"; }
    return { expired: m.expired || "", status };
  }

  /* ---- Man Power Rate (FR-43) ---- */
  function manPowerRate() {
    const m = state.mpr;
    const salary = sum(m.salary, (x) => x.v), bpjs = sum(m.bpjs, (x) => x.v), lain = sum(m.lain, (x) => x.v);
    const total = salary + bpjs + lain;
    const jam = Number(m.jam) || 1;
    const rateDL = Math.ceil(total / jam - 1e-9);
    return { salary, bpjs, lain, total, jam, rateDL, faktor: Number(m.faktor) || 0, rateMP: rateDL * (Number(m.faktor) || 0) };
  }

  /* ---- Flow Process (FR-42) ---- */
  const manHour = (f) => ((Number(f.ct) || 0) * (Number(f.mp) || 0)) / 60; // jam per meter
  const totalManHour = (cat) => sum(state.flow.filter((f) => f.category === cat), manHour);

  const rateOf = (cat) => state.bpkfoh.find((r) => r.category === cat) || { bpk: 0, foh: 0 };
  const gen = (key) => { const g = state.general.find((x) => x.key === key); return g ? Number(g.v) || 0 : 0; };

  /* ---- Kalkulasi costing per varian BOM (FR-46); semua Rp/meter ---- */
  const OTHER_KEYS = ["o_sales", "o_transport", "o_finance", "o_mgmt", "o_interest", "o_profit"];
  const genLabel = (key) => { const g = state.general.find((x) => x.key === key); return g ? g.label : key; };
  function costing(bom) {
    const p = productById(bom.productId), mat = materialById(bom.materialId);
    if (!p || !mat) return null;
    const pr = priceRef(mat.id), bpm = Number(mat.bpm) || 0;
    const rt = rateOf(p.category), mh = totalManHour(p.category), mp = manPowerRate();
    const material = pr.idr * bpm;
    const manPower = mh * mp.rateMP;
    const cons = material * gen("cons"), scrap = material * gen("scrap"), koord = gen("koord") * bpm;
    const hpp = material + manPower + (Number(rt.bpk) || 0) + (Number(rt.foh) || 0) + cons + scrap + koord;
    const others = OTHER_KEYS.map((k) => ({ k, l: "Other Cost — " + genLabel(k), pct: gen(k), v: hpp * gen(k) }));
    const bottom = hpp + sum(others, (o) => o.v);
    const nego = bottom * gen("nego");
    const priceList = bottom + nego;
    const ukuran = Number(p.panjang) || 0;
    const priceListKg = bpm ? priceList / bpm : 0; // Rp/Kg (Price List per meter ÷ berat/meter)
    return {
      bom, product: p, material: mat, pr, bpm, kurs: kursOf("USD"), totalManHour: mh, rateMP: mp.rateMP,
      beratPcs: bpm * ukuran, ukuran,
      lines: { material, manPower, bpk: Number(rt.bpk) || 0, foh: Number(rt.foh) || 0, cons, scrap, koord, hpp, others, bottom, nego, priceList },
      pricelistProduk: ukuran * priceList,
      hargaMeter: priceList, hargaKg: priceListKg,
      lastUpdated: bom.updated || today(),
    };
  }

  const fmt = (n, d = 2) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: d, maximumFractionDigits: d });
  const rp = (n, d = 2) => "Rp " + fmt(n, d);

  window.PC = {
    get state() { return state; },
    MONTHS, OTHER_KEYS, genLabel, save, reset, reload, uid, today, sum,
    productById, materialById, kursOf, categories,
    productName, materialName, applyProductName, applyMaterialName,
    history, priceRef, priceMetaOf, manPowerRate, manHour, totalManHour, rateOf, gen, costing,
    fmt, rp,
  };
})();
