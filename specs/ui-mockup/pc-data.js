/* ============================================================
   pc-data.js — Data & mesin hitung Product Costing (mockup).
   Seed = data CONTOH (bukan angka acuan klien). Rev.1 (2026-10-19): BPMN To Be,
   Use Case UC-01..UC-19, ERD Product Costing; rumus mengikuti docs/fr-nfr.md
   "Revisi Rev.1" (FR-50..FR-58). Diekspos sebagai window.PC.
   State disimpan di localStorage (key tetap mockPC.data, versi skema di dalamnya); tidak ada backend.
   ============================================================ */
(function () {
  "use strict";

  // Kunci TETAP. Perubahan bentuk data ke depan ditangani migrasi (SCHEMA naik + langkah di migrate()), bukan
  // dengan mengganti kunci, supaya isian pengguna tidak hilang saat mockup diperbarui.
  const KEY = "mockPC.data";
  const LEGACY_KEYS = ["mockPC.v5"]; // bentuk data sama dengan SCHEMA 5
  const SCHEMA = 11;
  const MONTHS = ["Januari", "Februari", "Maret", "April", "Mei", "Juni"];

  const seed = () => ({
    // 1. Master Produk (UC-01, FR-37) — hierarki 4 level. Berat per pcs tidak disimpan;
    //    diturunkan dari Berat/Meter varian BOM × Panjang.
    products: [
      {
        id: "PR1", kode: "P425000429", status: "Aktif",
        type: "Atap", category: "Spandek", jenis: "Polos",
        name: "HARAPAN SPANDEK POLOS ECO 0.25 X 6 M", codeMaster: "SM1020302012",
        lebar: 750, tebal: 0.25, panjang: 6, retail: "",
        productCode: "", tradeName: "", packUnit: "Roll", conversion: 1, uom: "Meter",
        maxStock: 0, minStock: 0, moq: 0, msds: "",
        dimP: 6, dimT: 0.025, beratUnit: 0, cbm: 0,
      },
      {
        id: "PR2", kode: "P425000430", status: "Aktif",
        type: "Atap", category: "BONDECK", jenis: "Polos",
        name: "ATAP BONDECK POLOS 0.7 X 1000 X 6 M", codeMaster: "BD1020302013",
        lebar: 1000, tebal: 0.7, panjang: 6, retail: "",
        productCode: "", tradeName: "", packUnit: "Roll", conversion: 1, uom: "Meter",
        maxStock: 0, minStock: 0, moq: 0, msds: "",
        dimP: 6, dimT: 0.05, beratUnit: 0, cbm: 0,
      },
    ],
    // 2. Master Material (UC-02) — Tebal (thickness) dan Lebar Coil (width). Berat/Meter
    //    TIDAK lagi atribut master: dihitung di BOM (UC-04) dari Master Standard Material.
    materials: [
      {
        id: "MT1", kode: "1GLMB014AZ70", status: "Aktif", metal: "Galvalum", slit: "Mother", boron: "Boron",
        name: "GALVALUM MOTHER BORON 0.14-1219-AZ70-G550-", hsCode: "7210.49.00",
        alias: "", width: 1219, coating: "AZ70", thickness: 0.14, color: "",
        hardness: "", tensile: "G550", noAccurate: "", packUnit: "Kg", conversion: 1,
        uom: "Kg", maxStock: 0, minStock: 0, msds: "", altSupplier: "",
      },
      {
        id: "MT2", kode: "1GLMB015AZ70", status: "Aktif", metal: "Galvalum", slit: "Mother", boron: "Boron",
        name: "GALVALUM MOTHER BORON 0.15-1219-AZ70-G550-", hsCode: "7210.49.00",
        alias: "", width: 1219, coating: "AZ70", thickness: 0.15, color: "",
        hardness: "", tensile: "G550", noAccurate: "", packUnit: "Kg", conversion: 1,
        uom: "Kg", maxStock: 0, minStock: 0, msds: "", altSupplier: "",
      },
    ],
    // 3. Master Standard Material (UC-03, FR-50): GLOBAL, satu set nilai, satuan tidak ditampilkan.
    standard: { toleransi: 0, bj: 7.85, coating: 0, updated: "2026-10-06", by: "Engineering" },
    // Master Kurs (UC-06)
    kurs: [{ id: "K1", currency: "USD", kurs: 17863, updated: "2026-10-06", by: "Finance" }],
    // 4. Price Reference (UC-06, FR-52): history harga USD/kg per bulan (null = kosong).
    //    Hanya nilai MAX yang dipakai costing.
    history: {
      MT1: [0.8, null, 0.81, null, null, 0.737],
      MT2: [0.79, null, 0.8, null, null, 0.725],
    },
    priceMeta: {
      MT1: { expired: "2027-05-18", status: "Aktif" },
      MT2: { expired: "2027-05-18", status: "Aktif" },
    },
    // 5. BOM per Varian Tebal (UC-04, FR-51): satu baris per varian (Uniq ID).
    boms: [
      { id: "B1", uniq: "Type 001.1", productId: "PR1", materialId: "MT1", qty: 1, updated: "2026-10-06", by: "Engineering" },
      { id: "B2", uniq: "Type 001.2", productId: "PR1", materialId: "MT2", qty: 1, updated: "2026-10-06", by: "Engineering" },
    ],
    // 6. Flow Process & Cycletime per Product Category (UC-05, FR-42); ct = menit per meter
    flow: [
      { id: "F1", category: "Spandek", area: "Spandek Area", process: "Cutting Spandek", machine: "Spandek", ct: 1 / 24, mp: 1, updated: "2026-09-01", by: "admin" },
    ],
    // 7. Man Power Rate (UC-07, FR-53): rincian gaji/BPJS/lain-lain hanya untuk OPERATOR.
    //    Rate 1/3 Operator = ROUNDUP((Total ÷ jam kerja) × (1 / porsiDen)).
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
      porsiDen: 3, // porsi operator = 1 / porsiDen (parameter, "1/3")
    },
    // 7b. Faktor Indirect Labor (UC-07, FR-53): MASTER GLOBAL satu nilai (bisa berubah; user 2026-10-20),
    //     bukan lagi parameter Man Power Rate. Disimpan sebagai daftar satu baris agar memakai pola master.
    faktor: [{ id: "FK1", v: 1.5, updated: "2026-10-06", by: "Costing" }],
    // 8. Master Borongan Helper per Product Category (UC-07, FR-53): HANYA Rp/Meter (user 2026-10-20);
    //    tidak ada satuan lain dan tidak ada konversi.
    borongan: [
      { id: "BH1", category: "Spandek", v: 25 },
      { id: "BH2", category: "BONDECK", v: 30 },
    ],
    // 9. Rate Bahan Pendukung Khusus & FOH per Product Category, Rp/KG (FR-53)
    bpkfoh: [
      { id: "R1", category: "Spandek", bpk: 110, foh: 300 },
      { id: "R2", category: "BONDECK", bpk: 110, foh: 300 },
    ],
    // 10. General Rate (UC-08, UC-09, FR-54/55): u = pct (pecahan) | rpkg
    general: [
      { key: "cons", group: "Manufacturing Support", label: "Consumables (Umum)", basis: "Rp/Kg x Berat/Meter", u: "rpkg", v: 300 },
      { key: "scrap", group: "Manufacturing Support", label: "Scrap", basis: "% x Material Cost", u: "pct", v: 0.02 },
      { key: "koord", group: "Manufacturing Support", label: "Biaya Koordinasi", basis: "Rp/Kg x Berat/Meter", u: "rpkg", v: 200 },
      { key: "o_sales", group: "Other Cost", label: "Sales & Marketing", basis: "% x HPP", u: "pct", v: 0 },
      { key: "o_transport", group: "Other Cost", label: "Transport", basis: "% x HPP", u: "pct", v: 0 },
      { key: "o_finance", group: "Other Cost", label: "Finance & Admin", basis: "% x HPP", u: "pct", v: 0 },
      { key: "o_mgmt", group: "Other Cost", label: "Management Fee", basis: "% x HPP", u: "pct", v: 0 },
      { key: "o_interest", group: "Other Cost", label: "Interest", basis: "% x HPP", u: "pct", v: 0 },
      { key: "o_profit", group: "Other Cost", label: "Profit", basis: "% x HPP", u: "pct", v: 0.05 },
    ],
    // 11. Rate Negotiation Allowance per Product Category (UC-10, FR-55) — ditetapkan Sales
    nego: [
      { id: "N1", category: "Spandek", v: 0.05 },
      { id: "N2", category: "BONDECK", v: 0.05 },
    ],
    // 11b. Pengajuan perubahan Negotiation Allowance (BPMN To Be Sales D01): Sales Manager mengajukan, GM memutuskan.
    //      Rate AKTIF tetap di `nego`; hanya berubah saat GM Approve. Status: Menunggu Approval GM | Approved | Rejected.
    negoReq: [
      { id: "NR1", category: "BONDECK", current: 0.05, requested: 0.03, status: "Rejected", submittedBy: "Sales Manager", submittedAt: "2026-10-15",
        decidedBy: "GM", decidedAt: "2026-10-16", rejectReason: "Contoh alasan: extra margin 3% terlalu rendah untuk kategori BONDECK; mohon ajukan ulang." },
    ],
    // 11c. Notifikasi perubahan Bottom Price (dashboard Sales). bpBase = baseline Bottom Price per varian BOM yang terakhir
    //      ditinjau Sales (null = diisi dari hitungan saat ini oleh normalize(), sehingga tanpa notifikasi palsu);
    //      bpNotif = notifikasi + riwayat keputusan.
    bpBase: null,
    bpNotif: [],
    // 11d. History Costing (hcLog = riwayat perubahan Bottom Price/Price List per varian BOM; hcBase = snapshot terakhir yang dicatat)
    //      dan History Price List (hpLog = kejadian Pricelist/Katalog per Produk × varian BOM; hpSeen = status terakhir yang sudah dicatat).
    hcLog: [], hcBase: null, hcLast: null, // hcLast = waktu Update Costing terakhir (pemicu History Costing)
    hpLog: [], hpSeen: null, hpInit: false, hpSeq: 0,
    hfg: 27400, // pembanding "Pricelist HFG saat ini", Rp/Meter — hanya contoh
    costRange: "",
    // 12. Pricelist (UC-16..UC-19, FR-58): per Produk x varian BOM. Status hanya:
    //     Draft | Menunggu Approval | Approved | Rejected. snap = harga saat diajukan.
    pricelists: [
      { id: "PL1", no: "PL-001", status: "Approved", bomIds: ["B1", "B2"], createdAt: "2026-10-12", submittedAt: "2026-10-12", decidedBy: "Management", decidedAt: "2026-10-13", rejectReason: "" },
      { id: "PL2", no: "PL-002", status: "Rejected", bomIds: ["B1"], createdAt: "2026-10-14", submittedAt: "2026-10-14", decidedBy: "Management", decidedAt: "2026-10-15", rejectReason: "Contoh alasan: margin terlalu tipis untuk kategori Spandek, mohon hitung ulang." },
    ],
    // 13. Product Catalog (UC-18, FR-58): disimpan sebagai data (snapshot harga yang disetujui).
    catalogs: [
      { id: "CT1", no: "KAT-001", pricelistId: "PL1", releasedBy: "Sales", releasedAt: "2026-10-14 09:30" },
    ],
    ui: { decision: "Approve", reason: "" }, // isian sementara modal Approval + filter/halaman daftar + BOM
  });

  let state = load();
  // Langkah migrasi data lama ke SCHEMA terbaru: tambahkan blok `if (v < n) { ...; }` saat bentuk data berubah.
  // Dijalankan pada data tersimpan SEBELUM digabung dengan seed (Object.assign(seed(), migrate(...))).
  //  v6: pengajuan perubahan Negotiation Allowance (negoReq); rate aktif tetap di `nego` (isian lama utuh).
  //  v7: notifikasi perubahan Bottom Price (bpNotif) dan baseline (bpBase = null → diisi normalize() dari hitungan saat ini).
  //  v8: History Costing (hcLog, hcBase) dan History Price List (hpLog, hpSeen, hpInit, hpSeq).
  //  v10: pemicu History Costing = tombol Update Costing (hcLast); pencatatan otomatis per perubahan master dihapus.
  //  v11: pemicu Notifikasi Bottom Price = tombol Update Costing (user 2026-10-21); deteksi otomatis saat simpan/muat dihapus.
  //       Bentuk data TIDAK berubah: bpBase dan bpNotif lama tetap (tanpa notifikasi palsu).
  //  v9: aturan pencatatan riwayat (jawaban user 2026-10-21): History Costing HANYA saat Bottom Price berubah (perubahan Extra
  //      Margin saja tidak dicatat); History Price List HANYA kejadian Disetujui. Entri lama yang dibuat oleh aturan sebelumnya
  //      dibuang; entri lain milik pengguna tidak disentuh.
  function migrate(s, v) {
    s = s || {};
    if (v < 6 && !Array.isArray(s.negoReq)) s.negoReq = [];
    if (v < 7) {
      if (!Array.isArray(s.bpNotif)) s.bpNotif = [];
      if (!("bpBase" in s)) s.bpBase = null;
    }
    if (v < 8) {
      // v8: History Costing dan History Price List. hcBase = null → diisi normalize() dari hitungan saat ini (TANPA entri palsu);
      //     hpInit = false → riwayat Pricelist direkonstruksi sekali dari data pricelist/katalog yang sudah ada.
      if (!Array.isArray(s.hcLog)) s.hcLog = [];
      if (!("hcBase" in s)) s.hcBase = null;
      if (!Array.isArray(s.hpLog)) s.hpLog = [];
      if (!("hpSeen" in s)) s.hpSeen = null;
      if (!("hpInit" in s)) s.hpInit = false;
      if (!("hpSeq" in s)) s.hpSeq = 0;
    }
    if (v < 10) {
      // v10: History Costing dipicu tombol "Update Costing" (jawaban user 2026-10-21). hcBase tetap menjadi snapshot hasil costing
      //      terakhir (sebelumnya disegarkan otomatis tiap simpan = sama dengan hitungan terakhir), sehingga Update Costing pertama
      //      tidak membuat entri palsu. hcLast baru.
      if (!("hcLast" in s)) s.hcLast = null;
    }
    if (v < 9) {
      const c2 = (n) => Math.round((Number(n) || 0) * 100);
      // History Costing: buang entri yang HANYA disebabkan Negotiation Allowance dan Bottom Price-nya tidak berubah (aturan lama).
      if (Array.isArray(s.hcLog)) {
        s.hcLog = s.hcLog.filter((e) => !(c2(e.oldM) === c2(e.newM) && Array.isArray(e.sources) && e.sources.length && e.sources.every((x) => x.g === "Negotiation Allowance")));
      }
      // History Price List: hanya kejadian Disetujui yang dicatat.
      if (Array.isArray(s.hpLog)) s.hpLog = s.hpLog.filter((e) => e.event === "Disetujui");
    }
    return s;
  }
  function load() {
    try {
      const raw = localStorage.getItem(KEY);
      if (raw) {
        const o = JSON.parse(raw);
        if (o && o.state && typeof o.state === "object") return Object.assign(seed(), migrate(o.state, o.v || SCHEMA));
      }
      for (const k of LEGACY_KEYS) {
        const old = JSON.parse(localStorage.getItem(k));
        if (old && typeof old === "object") return Object.assign(seed(), migrate(old, 5));
      }
    } catch (e) { /* abaikan */ }
    return seed();
  }
  let storageFailed = false;
  const save = () => {
    try { bpInit(); } catch (e) { /* inisialisasi baseline tidak boleh mengganggu penyimpanan */ }
    try { hcInit(); hpDetect(); } catch (e) { /* pencatatan riwayat tidak boleh mengganggu penyimpanan */ }
    try { localStorage.setItem(KEY, JSON.stringify({ v: SCHEMA, savedAt: new Date().toISOString(), state })); storageFailed = false; }
    catch (e) { storageFailed = true; }
  };
  // true bila penyimpanan browser dapat ditulis (false di jendela privat / penampil yang memblokir localStorage).
  const storageOk = () => {
    try { localStorage.setItem("mockPC.probe", "1"); localStorage.removeItem("mockPC.probe"); return !storageFailed; }
    catch (e) { return false; }
  };
  const reset = () => { try { localStorage.removeItem(KEY); LEGACY_KEYS.forEach((k) => localStorage.removeItem(k)); } catch (e) { /* abaikan */ } state = seed(); normalize(); };
  const reload = () => { state = load(); normalize(); };
  // Simpan/muat data ke file (cadangan yang tahan refresh, ganti browser, atau reset).
  const exportData = () => JSON.stringify({ app: "pc-mockup", v: SCHEMA, exportedAt: new Date().toISOString(), state }, null, 2);
  function importData(text) {
    let o;
    try { o = JSON.parse(text); } catch (e) { return { ok: false, msg: "File bukan JSON yang valid." }; }
    if (!o || o.app !== "pc-mockup" || !o.state || !Array.isArray(o.state.products) || !Array.isArray(o.state.materials)) {
      return { ok: false, msg: "File ini bukan cadangan data Product Costing." };
    }
    state = Object.assign(seed(), migrate(o.state, o.v || SCHEMA));
    normalize(); save();
    return { ok: true, msg: "Data dimuat dari file." };
  }

  const sum = (a, f) => a.reduce((t, x) => t + (Number(f ? f(x) : x) || 0), 0);
  const uid = (p) => p + Date.now().toString(36) + Math.floor(Math.random() * 1e3).toString(36);
  const today = () => new Date().toISOString().slice(0, 10);

  const productById = (id) => state.products.find((p) => p.id === id) || null;
  const materialById = (id) => state.materials.find((m) => m.id === id) || null;
  const kursOf = (cur) => { const k = state.kurs.find((x) => x.currency === cur); return k ? Number(k.kurs) : 0; };
  const categories = () => {
    const out = [];
    state.products.concat(state.flow, state.bpkfoh, state.borongan, state.nego).forEach((x) => { if (x.category && !out.includes(x.category)) out.push(x.category); });
    return out;
  };

  /* ---- Auto-generate nama gabungan (bisa dioverride manual) ---- */
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
  const applyProductName = (p) => { if (p && !p.nameManual) p.name = productName(p); return p; };
  const applyMaterialName = (m) => { if (m && !m.nameManual) m.name = materialName(m); return m; };

  /* ---- Berat/Meter varian BOM (FR-51) ----
     ((Tebal − Standar Toleransi) × Lebar Coil × Standar Berat Jenis) + Coating.
     Satuan tidak ditampilkan; konversi internal (÷1000) dikalibrasi dengan contoh
     0,14 × 1219 × 7,85 ≈ 1,34 kg/m. */
  function bpmOf(x) {
    const m = typeof x === "string" ? materialById(x) : x;
    if (!m) return 0;
    const s = state.standard;
    const v = ((Number(m.thickness) - (Number(s.toleransi) || 0)) * (Number(m.width) || 0) * (Number(s.bj) || 0)) / 1000 + (Number(s.coating) || 0);
    return v > 0 ? v : 0;
  }

  /* ---- Price Reference (FR-52): hanya MAX History Pembelian × Kurs ---- */
  function history(mid) { return state.history[mid] || [null, null, null, null, null, null]; }
  function priceRef(mid) {
    const h = history(mid).filter((v) => v !== null && v !== "" && !isNaN(v)).map(Number);
    const max = h.length ? Math.max.apply(null, h) : 0;
    const latest = h.length ? h[h.length - 1] : 0; // informasi saja
    const avg = h.length ? sum(h) / h.length : 0;  // informasi saja
    return { max, latest, avg, usd: max, idr: max * kursOf("USD") };
  }
  function priceMetaOf(mid) {
    const m = (state.priceMeta && state.priceMeta[mid]) || { expired: "", status: "Aktif" };
    let status = m.status || "Aktif";
    if (m.expired) { const exp = new Date(m.expired); if (!isNaN(exp) && exp < new Date()) status = "Expired"; }
    // label seperti modul aplikasi: Not Set (History kosong) | Expired | Oke
    const label = !hasHistory(mid) ? "Not Set" : (status === "Expired" ? "Expired" : "Oke");
    return { expired: m.expired || "", status, label };
  }
  const hasVal = (v) => v !== null && v !== undefined && v !== "" && !isNaN(Number(v));
  const hasHistory = (mid) => history(mid).some(hasVal);

  /* ---- Man Power Rate (FR-53): bagian Operator ---- */
  const faktorOf = () => { const r = Array.isArray(state.faktor) ? state.faktor[0] : null; return r ? Number(r.v) || 0 : 0; };
  function manPowerRate() {
    const m = state.mpr;
    const salary = sum(m.salary, (x) => x.v), bpjs = sum(m.bpjs, (x) => x.v), lain = sum(m.lain, (x) => x.v);
    const total = salary + bpjs + lain;
    const jam = Number(m.jam) || 1;
    const den = Number(m.porsiDen) || 1;
    const rateDL = total / jam;                              // Rate Direct Labor Operator (Rp/jam), belum dibulatkan
    const rateOp = Math.ceil(rateDL / den - 1e-9);           // Rate 1/3 Operator: dibulatkan ke atas SESUDAH dikali 1/3
    return { salary, bpjs, lain, total, jam, den, rateDL, rateOp, faktor: faktorOf() };
  }
  // Borongan Helper per Product Category — nilai langsung dalam Rp/Meter (tanpa konversi).
  function boronganRow(cat) { return state.borongan.find((b) => b.category === cat) || null; }
  function boronganPerMeter(cat) {
    const b = boronganRow(cat);
    return b ? Number(b.v) || 0 : 0;
  }

  /* ---- Flow Process (FR-42) ---- */
  const manHour = (f) => ((Number(f.ct) || 0) * (Number(f.mp) || 0)) / 60; // jam per meter
  const totalManHour = (cat) => sum(state.flow.filter((f) => f.category === cat), manHour);

  const rateOf = (cat) => state.bpkfoh.find((r) => r.category === cat) || { bpk: 0, foh: 0 };
  const negoOf = (cat) => { const n = state.nego.find((x) => x.category === cat); return n ? Number(n.v) || 0 : 0; };
  // Pengajuan perubahan Negotiation Allowance: costing hanya memakai rate AKTIF (negoOf); pengajuan menunggu TIDAK dihitung.
  const NEGO_PENDING = "Menunggu Approval GM";
  const negoLatest = (cat) => { const l = state.negoReq.filter((r) => r.category === cat); return l.length ? l[l.length - 1] : null; };
  const negoPending = () => state.negoReq.filter((r) => r.status === NEGO_PENDING);
  const gen = (key) => { const g = state.general.find((x) => x.key === key); return g ? Number(g.v) || 0 : 0; };

  /* ---- Kalkulasi costing per varian BOM (FR-56, FR-57); Rp/meter, Rp/Kg = ÷ Berat/Meter ---- */
  const OTHER_KEYS = ["o_sales", "o_transport", "o_finance", "o_mgmt", "o_interest", "o_profit"];
  const genLabel = (key) => { const g = state.general.find((x) => x.key === key); return g ? g.label : key; };
  /* ---- Kelengkapan data costing: rate yang belum diisi TIDAK boleh diam-diam dianggap 0 ----
     Mengembalikan daftar kekurangan { key, text, file, label }; file/label = menu tempat mengisinya. */
  const LINK = {
    flow: ["pc-flow.html", "Flow Process & Cycletime"], bpkfoh: ["pc-bpk-foh.html", "Rate Bahan Pendukung & FOH"],
    borongan: ["pc-borongan.html", "Rate Borongan Helper"], nego: ["pc-negotiation.html", "Negotiation Allowance"],
    price: ["pc-price-reference.html", "Price Reference"], kurs: ["pc-kurs.html", "Kurs"],
    standard: ["pc-standard.html", "Master Standard Material"], general: ["pc-general-rate.html", "General Rate"],
    mpr: ["pc-man-power-rate.html", "Man Power Rate (Operator)"],
    faktor: ["pc-faktor-indirect.html", "Faktor Indirect Labor"],
    material: ["pc-material.html", "Master Material"],
  };
  function gapsOf(p, mat, bpm, pr, mh) {
    const out = [];
    const add = (key, text) => out.push({ key, text, file: LINK[key][0], label: LINK[key][1] });
    const cat = p.category || "(tanpa kategori)";
    const fl = state.flow.filter((x) => x.category === p.category);
    if (!fl.length) add("flow", `Flow Process kategori ${cat} belum ada.`);
    else if (!(mh > 0)) add("flow", `Total Man Hour kategori ${cat} = 0 (Cycletime/Man Power belum diisi).`);
    const bk = state.bpkfoh.find((r) => r.category === p.category);
    if (!bk) add("bpkfoh", `Rate Bahan Pendukung & FOH kategori ${cat} belum ada.`);
    else if (!hasVal(bk.bpk) || !hasVal(bk.foh)) add("bpkfoh", `Rate Bahan Pendukung/FOH kategori ${cat} belum diisi lengkap.`);
    const br = boronganRow(p.category);
    if (!br) add("borongan", `Rate Borongan Helper kategori ${cat} belum ada.`);
    else if (!hasVal(br.v)) add("borongan", `Rate Borongan Helper kategori ${cat} belum diisi nilainya.`);
    const ng = state.nego.find((n) => n.category === p.category);
    if (!ng || !hasVal(ng.v)) add("nego", `Negotiation Allowance kategori ${cat} belum ada.`);
    if (!hasHistory(mat.id)) add("price", `Price Reference material ${mat.name} belum ada (History Pembelian kosong).`);
    if (!(kursOf("USD") > 0)) add("kurs", "Kurs USD belum diisi.");
    if (!(bpm > 0)) add("standard", `Berat/Meter material ${mat.name} ≤ 0 (periksa Master Standard, Tebal, dan Lebar Coil).`);
    [["cons", "Consumables"], ["scrap", "Scrap"], ["koord", "Biaya Koordinasi"]].forEach(([k, l]) => {
      const g = state.general.find((x) => x.key === k);
      if (!g || !hasVal(g.v)) add("general", `General Rate ${l} belum diisi.`);
    });
    const m = state.mpr;
    if (!(Number(m.jam) > 0)) add("mpr", "Jam kerja per bulan belum diisi.");
    if (!(Number(m.porsiDen) > 0)) add("mpr", "Porsi Operator (1 / n) belum diisi.");
    if (!(faktorOf() > 0)) add("faktor", "Faktor Indirect Labor belum diisi.");
    return out;
  }
  function costing(bom) {
    const p = productById(bom.productId), mat = materialById(bom.materialId);
    if (!p || !mat) return null;
    const pr = priceRef(mat.id), bpm = bpmOf(mat);
    const rt = rateOf(p.category), mh = totalManHour(p.category), mp = manPowerRate();
    const ukuran = Number(p.panjang) || 0;
    const borong = boronganPerMeter(p.category);
    const kekurangan = gapsOf(p, mat, bpm, pr, mh);
    const material = pr.idr * bpm;
    const operatorPart = mh * mp.rateOp;
    const manPower = (operatorPart + borong) * mp.faktor;   // FR-56: (Total Man Hour × Rate 1/3 Operator + Borongan Rp/Meter) × Faktor
    const bpk = (Number(rt.bpk) || 0) * bpm, foh = (Number(rt.foh) || 0) * bpm; // Rate Rp/Kg × Berat/Meter
    const proses = manPower + bpk + foh;
    const cons = gen("cons") * bpm, scrap = material * gen("scrap"), koord = gen("koord") * bpm;
    const support = cons + scrap + koord;
    const hpp = material + proses + support;
    const others = OTHER_KEYS.map((k) => ({ k, l: genLabel(k), pct: gen(k), v: hpp * gen(k) }));
    const bottom = hpp + sum(others, (o) => o.v);
    const negoPct = negoOf(p.category);
    const nego = bottom * negoPct;
    const priceList = bottom + nego;
    const kg = (v) => (bpm ? v / bpm : 0);
    const lines = { material, manPower, bpk, foh, proses, cons, scrap, koord, support, hpp, others, bottom, nego, priceList };
    const linesKg = {};
    Object.keys(lines).forEach((key) => { if (key !== "others") linesKg[key] = kg(lines[key]); });
    linesKg.others = others.map((o) => ({ k: o.k, l: o.l, pct: o.pct, v: kg(o.v) }));
    return {
      bom, product: p, material: mat, pr, bpm, kurs: kursOf("USD"), totalManHour: mh, kekurangan, lengkap: kekurangan.length === 0,
      rateOp: mp.rateOp, faktor: mp.faktor, operatorPart, boronganM: borong, negoPct,
      beratPcs: bpm * ukuran, ukuran, lines, linesKg,
      pricelistProduk: ukuran * priceList,
      hargaMeter: priceList, hargaKg: kg(priceList), bottomMeter: bottom, bottomKg: kg(bottom),
      lastUpdated: bom.updated || today(),
    };
  }

  /* ---- Pricelist & Product Catalog (FR-58): baris = Produk x varian BOM ---- */
  function plRow(bomId) {
    const bom = state.boms.find((b) => b.id === bomId);
    const c = bom ? costing(bom) : null;
    if (!c) return null;
    return {
      bomId, uniq: bom.uniq, product: c.product.name, category: c.product.category, material: c.material.name,
      bottomM: c.bottomMeter, bottomKg: c.bottomKg, negoPct: c.negoPct, plM: c.hargaMeter, plKg: c.hargaKg,
      gaps: c.kekurangan.map((g) => g.text), gapItems: c.kekurangan, lengkap: c.lengkap,
    };
  }
  // Baris yang ditampilkan: Draft = hitungan terkini; selain Draft = snapshot saat diajukan.
  function plRows(pl) {
    if (pl.status === "Draft") return pl.bomIds.map(plRow).filter(Boolean);
    return pl.snap || [];
  }
  function snapPricelist(pl) { pl.snap = pl.bomIds.map(plRow).filter(Boolean); }
  function nextNo(list, prefix) {
    const n = list.reduce((mx, x) => Math.max(mx, parseInt(String(x.no || "").replace(/\D/g, ""), 10) || 0), 0) + 1;
    return prefix + "-" + String(n).padStart(3, "0");
  }
  /* ---- Notifikasi perubahan Bottom Price (dashboard Sales; asumsi menunggu konfirmasi) ----
     Baseline per varian BOM (state.bpBase) = Bottom Price yang terakhir ditinjau Sales. Dihitung ulang HANYA saat tombol
     Update Costing ditekan (bpDetect; keputusan user 2026-10-21) — mengubah master saja tidak membuat notifikasi:
     bila Bottom Price (Rp/Meter) berbeda dari baseline pada presisi 2 desimal (selisih >= Rp0,01; tanpa persentase minimum;
     keputusan user 2026-10-21) dan data costing lengkap → notifikasi "Belum Ditinjau". Peninjau: semua Sales; kanal: dashboard
     saja; tanpa batas waktu tinjau (tidak ada SLA/eskalasi/penanda terlambat).
     Sistem TIDAK mengubah Rate Extra Margin maupun Pricelist; Sales memutuskan: ajukan rate baru (→ Approval GM) atau tidak
     perlu diubah (keduanya menggeser baseline ke Bottom Price baru). Satu notifikasi Belum Ditinjau per varian BOM. */
  const BP_NEW = "Belum Ditinjau", BP_NEGO = "Diajukan Extra Margin Baru", BP_KEEP = "Tidak Perlu Diubah";
  const BP_COMP = [["material", "Material Cost"], ["proses", "Biaya Proses"], ["support", "Manufacturing Support"], ["other", "Other Cost"]];
  const nowStamp = () => today() + " " + new Date().toTimeString().slice(0, 5);
  function bpSnap(c) {
    const L = c.lines;
    return {
      bomId: c.bom.id, uniq: c.bom.uniq, productName: c.product.name, category: c.product.category,
      bottomM: c.bottomMeter, bottomKg: c.bottomKg, material: L.material, proses: L.proses, support: L.support,
      other: sum(L.others, (o) => o.v), negoPct: c.negoPct, plM: c.hargaMeter, plKg: c.hargaKg, at: today(),
    };
  }
  // Harga jual saat ini = Price List pada Product Catalog terakhir dirilis untuk varian BOM itu; bila belum ada, Price List baseline.
  function bpHargaJual(bomId, base) {
    for (let i = state.catalogs.length - 1; i >= 0; i--) {
      const ct = state.catalogs[i], r = (ct.rows || []).find((x) => x.bomId === bomId);
      if (r) return { m: r.plM, kg: r.plKg, src: "Katalog " + ct.no };
    }
    return { m: base.plM, kg: base.plKg, src: "Price List baseline" };
  }
  function bpFields(base, c) {
    const now = bpSnap(c), hj = bpHargaJual(base.bomId, base);
    return {
      bomId: base.bomId, bomUniq: c.bom.uniq, productId: c.product.id, productName: c.product.name, category: c.product.category,
      oldM: base.bottomM, newM: now.bottomM, oldKg: base.bottomKg, newKg: now.bottomKg,
      comps: BP_COMP.map(([k, l]) => ({ k, l, old: base[k], now: now[k], diff: now[k] - base[k] })).filter((x) => Math.abs(x.diff) >= 0.005),
      negoPct: now.negoPct, hargaJualM: hj.m, hargaJualKg: hj.kg, hargaJualSrc: hj.src,
      plNewM: now.bottomM * (1 + now.negoPct), plNewKg: now.bottomKg * (1 + now.negoPct),
    };
  }
  // Inisialisasi baseline saja (varian BOM yang belum punya baseline, mis. data baru/lama); TIDAK membuat atau menghapus
  // notifikasi. Dipanggil saat simpan/muat. true bila ada perubahan state.
  function bpInit() {
    let changed = false;
    if (!state.bpBase || typeof state.bpBase !== "object") { state.bpBase = {}; changed = true; }
    if (!Array.isArray(state.bpNotif)) { state.bpNotif = []; changed = true; }
    state.boms.forEach((b) => {
      if (state.bpBase[b.id]) return;
      const c = costing(b);
      if (c && c.lengkap) { state.bpBase[b.id] = bpSnap(c); changed = true; }
    });
    return changed;
  }
  // Deteksi perubahan Bottom Price terhadap baseline — dijalankan oleh Update Costing. true bila ada perubahan state.
  function bpDetect() {
    let changed = false;
    if (!state.bpBase || typeof state.bpBase !== "object") { state.bpBase = {}; changed = true; }
    if (!Array.isArray(state.bpNotif)) { state.bpNotif = []; changed = true; }
    state.boms.forEach((b) => {
      const c = costing(b);
      if (!c || !c.lengkap) return;                                   // data belum lengkap: Bottom Price belum bisa diandalkan
      const base = state.bpBase[b.id];
      if (!base) { state.bpBase[b.id] = bpSnap(c); changed = true; return; }
      const idx = state.bpNotif.findIndex((n) => n.bomId === b.id && n.status === BP_NEW);
      if (Math.round(c.bottomMeter * 100) === Math.round(base.bottomM * 100)) { // sama pada 2 desimal: notifikasi menunggu tidak diperlukan lagi
        if (idx >= 0) { state.bpNotif.splice(idx, 1); changed = true; }
        return;
      }
      const fresh = bpFields(base, c);
      if (idx >= 0) {                                                 // satu notifikasi Belum Ditinjau per varian: diperbarui, tidak dobel
        const cur = state.bpNotif[idx];
        if (JSON.stringify(Object.assign({}, cur, fresh)) !== JSON.stringify(cur)) { Object.assign(cur, fresh); changed = true; }
      } else {
        state.bpNotif.push(Object.assign({ id: uid("BP"), status: BP_NEW, detectedAt: nowStamp(), reviewedBy: "", reviewedAt: "", note: "", negoReqId: "" }, fresh));
        changed = true;
      }
    });
    return changed;
  }
  // Keputusan Sales: geser baseline varian BOM itu ke Bottom Price saat ini.
  function bpShift(bomId) {
    const b = state.boms.find((x) => x.id === bomId), c = b && costing(b);
    if (c && c.lengkap) { if (!state.bpBase) state.bpBase = {}; state.bpBase[bomId] = bpSnap(c); }
  }
  const bpUnreviewed = () => state.bpNotif.filter((n) => n.status === BP_NEW);

  /* ---- History Costing & History Price List (jawaban user 2026-10-21) ----
     History Costing: dipicu tombol "Update Costing" di Simulasi (trigger utama update costing terbaru, user 2026-10-21). Saat
     ditekan, hasil costing saat ini per Produk × varian BOM dibandingkan dengan snapshot Update Costing sebelumnya (hcBase); bila
     Bottom Price (Rp/Meter) berubah pada presisi 2 desimal dicatat satu entri per varian BOM (waktu, pengguna, SEMUA sumber perubahan
     sejak Update Costing sebelumnya lama → baru, snapshot komponen lama dan baru). Tidak ada pencatatan otomatis tiap perubahan
     master. Perubahan/persetujuan Extra Margin TIDAK dicatat (user 2026-10-21).
     Masa simpan 1 tahun: lebih lama dipindahkan ke backup data (disembunyikan dari tampilan, tidak dihapus). Riwayat TERPISAH dari notifikasi Bottom Price: memakai snapshot sendiri
     (state.hcBase) sehingga tidak bergantung pada status tinjau notifikasi. Hanya baca.
     History Price List: HANYA saat Pricelist disetujui (Approved) — satu entri per Produk × varian BOM dengan Price List lama
     (Approved sebelumnya) → baru; Draft, Diajukan, Ditolak, Dihitung Ulang, Katalog, dan Extra Margin tidak dicatat. Hanya baca. */
  let actorName = "Sistem";
  const setActor = (n) => { actorName = n || "Sistem"; };
  const HC_KIND_FMT = {
    idr: (v) => rp(v), usd: (v) => "USD " + fmt(v, 4), num: (v) => fmt(v, 6), pct: (v) => fmt(Number(v) * 100, 2) + " %",
    frac: (v) => "1/" + fmt(v, 0), text: (v) => String(v),
  };
  const hcFmt = (kind, v) => (v == null || v === "" ? "—" : (HC_KIND_FMT[kind] || HC_KIND_FMT.text)(v));
  function hcDrivers(c) {
    const p = c.product, m = c.material, mp = manPowerRate(), cat = p.category, rt = rateOf(cat), d = {};
    const add = (k, g, l, v, kind) => { d[k] = { g, l, v, kind }; };
    add("kurs", "Kurs", "Kurs USD", kursOf("USD"), "idr");
    add("pr:" + m.id, "Price Reference", "Price Reference Max " + m.name + " (USD/Kg)", c.pr.max, "usd");
    add("bom:" + c.bom.id + ":material", "BOM / Material", "Material varian " + c.bom.uniq, m.name, "text");
    add("mat:" + m.id + ":t", "BOM / Material", "Tebal material " + m.name, Number(m.thickness) || 0, "num");
    add("mat:" + m.id + ":w", "BOM / Material", "Lebar coil material " + m.name, Number(m.width) || 0, "num");
    add("std:tol", "Master Standard", "Master Standard: Standar Toleransi", Number(state.standard.toleransi) || 0, "num");
    add("std:bj", "Master Standard", "Master Standard: Standar Berat Jenis", Number(state.standard.bj) || 0, "num");
    add("std:coat", "Master Standard", "Master Standard: Coating", Number(state.standard.coating) || 0, "num");
    add("mpr:total", "Man Power Rate", "Total Biaya Direct Labor Operator / Bulan", mp.total, "idr");
    add("mpr:jam", "Man Power Rate", "Jam kerja per bulan", mp.jam, "num");
    add("mpr:den", "Man Power Rate", "Porsi Operator", mp.den, "frac");
    add("faktor", "Faktor Indirect Labor", "Faktor Indirect Labor", mp.faktor, "num");
    add("flow:" + cat, "Flow Process", "Total Man Hour " + cat + " (jam/meter)", totalManHour(cat), "num");
    add("bor:" + cat, "Rate Borongan Helper", "Rate Borongan Helper " + cat + " (Rp/Meter)", boronganPerMeter(cat), "idr");
    add("bpk:" + cat, "Rate BPK & FOH", "Rate Bahan Pendukung Khusus " + cat + " (Rp/Kg)", Number(rt.bpk) || 0, "idr");
    add("foh:" + cat, "Rate BPK & FOH", "Rate FOH " + cat + " (Rp/Kg)", Number(rt.foh) || 0, "idr");
    state.general.forEach((g) => add("gen:" + g.key, "General Rate", g.label + (g.u === "rpkg" ? " (Rp/Kg)" : ""), Number(g.v) || 0, g.u === "pct" ? "pct" : "idr"));
    // Negotiation Allowance (Extra Margin) sengaja BUKAN sumber riwayat costing (keputusan user 2026-10-21).
    return d;
  }
  function hcSnap(c) {
    const L = c.lines;
    return {
      uniq: c.bom.uniq, productName: c.product.name, category: c.product.category,
      comps: { material: L.material, proses: L.proses, support: L.support, other: sum(L.others, (o) => o.v), hpp: L.hpp, bottom: L.bottom, priceList: L.priceList },
      bottomM: c.bottomMeter, bottomKg: c.bottomKg, plM: c.hargaMeter, plKg: c.hargaKg, negoPct: c.negoPct, drivers: hcDrivers(c),
    };
  }
  const r2 = (n) => Math.round((Number(n) || 0) * 100);
  const sameVal = (a, b) => (typeof a === "number" && typeof b === "number" ? Math.abs(a - b) <= 1e-9 * Math.max(1, Math.abs(a), Math.abs(b)) : a === b);
  function hcSources(oldD, newD) {
    const out = [];
    Object.keys(newD).forEach((k) => {
      if (!oldD[k] || sameVal(oldD[k].v, newD[k].v)) return;
      out.push({ k, g: newD[k].g, l: newD[k].l, kind: newD[k].kind, old: oldD[k].v, now: newD[k].v });
    });
    return out;
  }
  // Snapshot awal saja (tanpa entri): varian BOM yang belum punya snapshot Update Costing diberi snapshot dari hitungan saat ini.
  // true bila ada perubahan state.
  function hcInit() {
    let changed = false;
    if (!state.hcBase || typeof state.hcBase !== "object") { state.hcBase = {}; changed = true; }
    if (!Array.isArray(state.hcLog)) { state.hcLog = []; changed = true; }
    state.boms.forEach((b) => {
      if (state.hcBase[b.id]) return;
      const c = costing(b);
      if (c && c.lengkap) { state.hcBase[b.id] = hcSnap(c); changed = true; }
    });
    return changed;
  }
  // Update Costing: catat perubahan sejak Update Costing sebelumnya. Mengembalikan { entries, bomIds, at }.
  function hcUpdate() {
    hcInit();
    const batch = uid("HB"), at = nowStamp(), bomIds = [];
    state.boms.forEach((b) => {
      const c = costing(b);
      if (!c || !c.lengkap) return;                                   // data belum lengkap: angka belum bisa diandalkan
      const base = state.hcBase[b.id], now = hcSnap(c);
      if (!base) { state.hcBase[b.id] = now; return; }
      let sources = hcSources(base.drivers || {}, now.drivers);
      if (r2(now.bottomM) === r2(base.bottomM)) { state.hcBase[b.id] = now; return; }   // Bottom Price tidak berubah → tidak dicatat
      if (!sources.length) sources = [{ k: "lain", g: "Lainnya", l: "Perubahan lain (sumber tidak teridentifikasi)", kind: "text", old: null, now: null }];
      state.hcLog.push({
        id: uid("HC"), batch, at, by: actorName, bomId: b.id, uniq: b.uniq, productName: now.productName, category: now.category,
        sources, oldM: base.bottomM, newM: now.bottomM, oldKg: base.bottomKg, newKg: now.bottomKg,
        plOldM: base.plM, plNewM: now.plM, plOldKg: base.plKg, plNewKg: now.plKg, negoPct: now.negoPct,
        old: base.comps, now: now.comps,
      });
      state.hcBase[b.id] = now;
      bomIds.push(b.id);
    });
    state.hcLast = at;
    return { entries: bomIds.length, bomIds, at };
  }
  // Jumlah varian BOM yang hasil costing langsungnya sudah berbeda dari snapshot Update Costing terakhir (belum di-Update Costing).
  function hcPending() {
    let n = 0;
    state.boms.forEach((b) => {
      const base = state.hcBase && state.hcBase[b.id], c = costing(b);
      if (!base || !c || !c.lengkap) return;
      const now = hcSnap(c);
      if (r2(now.bottomM) !== r2(base.bottomM) || hcSources(base.drivers || {}, now.drivers).length) n++;
    });
    return n;
  }

  // Masa simpan riwayat 1 tahun (keputusan user 2026-10-21): entri lebih lama dipindahkan ke backup data — di mockup disembunyikan
  // dari tampilan standar (tidak dihapus dari penyimpanan).
  const histCutoff = () => { const d = new Date(); d.setFullYear(d.getFullYear() - 1); return d.toISOString().slice(0, 10); };
  const histRetained = (at) => String(at || "").slice(0, 10) >= histCutoff();
  const HP_EV = { SUBMIT: "Diajukan", APPROVE: "Disetujui", REJECT: "Ditolak", RECALC: "Dihitung Ulang", RELEASE: "Dirilis ke Product Catalog" };
  function hpLastFor(bomId) {
    for (let i = state.hpLog.length - 1; i >= 0; i--) if (state.hpLog[i].bomId === bomId) return state.hpLog[i];
    return null;
  }
  function hpAdd(event, pl, rows, o) {
    o = o || {};
    (rows || []).forEach((r) => {
      const prev = hpLastFor(r.bomId);
      state.hpLog.push({
        id: uid("HP"), seq: ++state.hpSeq, at: o.at || nowStamp(), by: o.by || actorName, event, plId: pl.id, plNo: pl.no, catNo: o.catNo || "",
        bomId: r.bomId, uniq: r.uniq, product: r.product, category: r.category,
        plOldM: prev ? prev.plNewM : null, plNewM: r.plM, plOldKg: prev ? prev.plNewKg : null, plNewKg: r.plKg,
        bottomM: r.bottomM, bottomKg: r.bottomKg, negoPct: r.negoPct, note: o.note || "", backfill: !!o.backfill,
      });
    });
  }
  // Rekonstruksi SEKALI dari data pricelist/katalog yang sudah ada (tanpa mengarang kejadian di luar data tersimpan).
  function hpBackfill() {
    const ev = [];
    state.pricelists.forEach((pl) => {
      const rows = pl.snap || [];
      if (!rows.length) return;
      if (pl.status === "Approved" && pl.decidedAt) ev.push({ at: pl.decidedAt, event: HP_EV.APPROVE, pl, rows, by: pl.decidedBy || "Management" });
    });
    ev.sort((a, b) => String(a.at).localeCompare(String(b.at)));
    ev.forEach((e) => hpAdd(e.event, e.pl, e.rows, { at: e.at, by: e.by, note: e.note, catNo: e.catNo, backfill: true }));
    state.hpSeen = {};
    state.pricelists.forEach((pl) => { state.hpSeen[pl.id] = pl.status; });
    state.catalogs.forEach((ct) => { state.hpSeen["ct:" + ct.id] = true; });
    state.hpInit = true;
  }
  // true bila ada perubahan state. Kejadian terdeteksi dari perubahan status Pricelist dan katalog baru sejak penyimpanan terakhir.
  function hpDetect() {
    if (!state.hpInit) { hpBackfill(); return true; }
    let changed = false;
    if (!state.hpSeen || typeof state.hpSeen !== "object") { state.hpSeen = {}; changed = true; }
    state.pricelists.forEach((pl) => {
      const seen = state.hpSeen[pl.id] === undefined ? "Draft" : state.hpSeen[pl.id];
      if (seen === pl.status) { if (state.hpSeen[pl.id] === undefined) { state.hpSeen[pl.id] = pl.status; changed = true; } return; }
      state.hpSeen[pl.id] = pl.status; changed = true;
      if (pl.status === "Approved") hpAdd(HP_EV.APPROVE, pl, pl.snap || [], { by: pl.decidedBy || actorName });   // hanya yang disetujui
    });
    return changed;
  }

  function normalize() {
    if (!Array.isArray(state.faktor) || !state.faktor.length) state.faktor = seed().faktor;
    if (!Array.isArray(state.negoReq)) state.negoReq = [];
    if (!state.ui) state.ui = { decision: "Approve", reason: "" };
    const u = state.ui;                                  // keadaan layar daftar (filter, DataTables, BOM)
    u.pf = u.pf || { t: "", c: "", j: "" };
    u.mf = u.mf || { m: "", s: "", b: "" };
    u.dt = u.dt || {};
    u.egf = u.egf || { s: "" };
    u.bpf = u.bpf || { s: "", c: "" };
    u.hcf = u.hcf || { c: "", u: "", g: "", from: "", to: "" };
    u.hpf = u.hpf || { c: "", u: "", e: "", from: "", to: "" };
    u.bomv = u.bomv || { mode: "list", productId: "" };
    if (!("flash" in u)) u.flash = null;
    if (!("bomDraft" in u)) u.bomDraft = null;
    state.pricelists.forEach((pl) => { if (pl.status !== "Draft" && !pl.snap) snapPricelist(pl); });
    state.catalogs.forEach((ct) => {
      const pl = state.pricelists.find((x) => x.id === ct.pricelistId);
      if (!ct.rows && pl) ct.rows = (pl.snap || []).map((r) => Object.assign({}, r));
    });
    if (!Array.isArray(state.hcLog)) state.hcLog = [];
    if (!Array.isArray(state.hpLog)) state.hpLog = [];
    // Riwayat: snapshot awal dari hitungan saat ini (tanpa entri palsu); riwayat Pricelist direkonstruksi sekali dari data yang ada.
    try { hcInit(); hpDetect(); } catch (e) { /* abaikan */ }
    // Deteksi perubahan Bottom Price saat memuat; baseline awal dibuat dari hitungan saat ini (tanpa notifikasi palsu).
    if (typeof bpInit === "function" && bpInit() && ready) { try { save(); } catch (e) { /* abaikan */ } }
  }

  // Ringkasan: varian BOM yang datanya belum lengkap (untuk Simulasi, Pricelist, dll.).
  function gapReport() {
    return state.boms.map((b) => ({ b, c: costing(b) })).filter((x) => x.c && !x.c.lengkap)
      .map((x) => ({ bomId: x.b.id, uniq: x.b.uniq, product: x.c.product.name, gaps: x.c.kekurangan }));
  }
  const fmt = (n, d = 2) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: d, maximumFractionDigits: d });
  const rp = (n, d = 2) => "Rp " + fmt(n, d);

  let ready = false;
  normalize();
  ready = true;
  try { save(); } catch (e) { /* abaikan */ }   // simpan baseline awal / notifikasi hasil deteksi saat memuat

  window.PC = {
    get state() { return state; },
    MONTHS, OTHER_KEYS, genLabel, save, reset, reload, storageOk, exportData, importData, uid, today, sum,
    productById, materialById, kursOf, categories,
    productName, materialName, applyProductName, applyMaterialName,
    bpmOf, history, priceRef, priceMetaOf, manPowerRate, faktorOf, boronganRow, boronganPerMeter,
    manHour, totalManHour, rateOf, negoOf, NEGO_PENDING, negoLatest, negoPending, gen, costing,
    plRow, plRows, snapPricelist, nextNo, normalize,
    fmt, rp, hasVal, hasHistory, gapReport, LINK,
    setActor, actor: () => actorName,
    HC: { fmt: hcFmt, init: hcInit, update: hcUpdate, pending: hcPending, retained: histRetained, cutoff: histCutoff },
    HP: { EV: HP_EV, detect: hpDetect, retained: histRetained, cutoff: histCutoff },
    BP: { NEW: BP_NEW, NEGO: BP_NEGO, KEEP: BP_KEEP, init: bpInit, detect: bpDetect, unreviewed: bpUnreviewed, shift: bpShift, hargaJual: bpHargaJual, nowStamp },
  };
})();
