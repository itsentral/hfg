/* ============================================================
   data.js — Master data bersama (dummy) untuk seluruh halaman mockup.
   Diekspos sebagai window.MOCK. Dipakai oleh beranda, index (input), hpp.
   State lintas-halaman (SPK terpilih + coil yang di-add) disimpan di localStorage.
   ============================================================ */
(function () {
  "use strict";

  /* ---------- Master Product + berat standar/pcs (FR-09) ---------- */
  const PRODUCTS = [
    { id: "P01", name: "Spandek 0.30 x 1000", std: 24.0, size: 2.4 },
    { id: "P02", name: "Bondek 0.75 x 1000", std: 18.5, size: 2.4 },
    { id: "P03", name: "Hollow 40 x 40", std: 31.2, size: 3.0 },
    { id: "P04", name: "Reng 0.45", std: 120.0, size: 100 },
    { id: "P05", name: "Spandek 0.35 x 1000", std: 34.6, size: 3.0 },
  ];
  const productById = (id) => PRODUCTS.find((p) => p.id === id) || null;

  /* ---------- SPK + daftar produk FG (FR-01/FR-25: tr_spk_material_detail.id_produk_fg) ---------- */
  // Tiap SPK punya daftar produk FG KW1 yang harus dilaporkan produksinya.
  const SPKS = [
    {
      no: "SPK-2026-0912", tgl: "2026-09-24", customer: "PT Baja Sejahtera",
      material: "Coil Galvanis 1.0mm", status: "Menunggu Produksi",
      products: [
        { productId: "P01", targetQty: 100 },
        { productId: "P05", targetQty: 40 },
      ],
    },
    {
      no: "SPK-2026-0913", tgl: "2026-09-24", customer: "CV Logam Jaya",
      material: "Coil Galvanis 0.8mm", status: "Menunggu Produksi",
      products: [
        { productId: "P02", targetQty: 150 },
      ],
    },
    {
      no: "SPK-2026-0914", tgl: "2026-09-25", customer: "PT Konstruksi Prima",
      material: "Coil Cold Rolled 1.2mm", status: "Menunggu Produksi",
      products: [
        { productId: "P03", targetQty: 80 },
        { productId: "P02", targetQty: 60 },
        { productId: "P05", targetQty: 30 },
      ],
    },
  ];
  const USER_SPK_KEY = "mockProduksi.userSpk";
  const userSpk = {
    all() {
      try { return JSON.parse(localStorage.getItem(USER_SPK_KEY)) || []; } catch (e) { return []; }
    },
    add(spk) {
      const list = userSpk.all();
      list.push(spk);
      localStorage.setItem(USER_SPK_KEY, JSON.stringify(list));
    },
    reset() { localStorage.removeItem(USER_SPK_KEY); },
  };
  // Daftar SPK gabungan: bawaan + buatan user (user di atas / paling baru).
  const allSpks = () => userSpk.all().concat(SPKS);
  const spkByNo = (no) => allSpks().find((s) => s.no === no) || null;

  /* ---------- Baby coil per sumber gudang (FR-08) ---------- */
  /* ---------- Master Material (baby coil) per gudang (FR-08) ----------
     Seed default; halaman Master Material dapat meng-override & menyimpan ke
     localStorage (key mockProduksi.material). Sumber tunggal daftar baby coil
     untuk Laporan Produksi (Add Coil) dan daftar jenis material untuk Costbook. */
  const COILS_DEFAULT = {
    unpack: [
      { code: "BC-U-1001", material: "Coil Galvanis 1.0mm", nett: 2450.5, gross: 2480.0, meter: 980, kulit: 12.0, clamp: 5.5 },
      { code: "BC-U-1002", material: "Coil Galvanis 1.0mm", nett: 2380.0, gross: 2410.0, meter: 952, kulit: 11.5, clamp: 5.0 },
      { code: "BC-U-1003", material: "Coil Galvanis 0.8mm", nett: 1950.0, gross: 1975.0, meter: 1200, kulit: 10.0, clamp: 4.5 },
    ],
    wip: [
      { code: "BC-W-2005", material: "Coil Galvanis 1.0mm", nett: 620.0, gross: 628.0, meter: 248, kulit: null, clamp: null },
      { code: "BC-W-2006", material: "Coil Cold Rolled 1.2mm", nett: 810.0, gross: 820.0, meter: 270, kulit: null, clamp: null },
    ],
    hold: [
      { code: "BC-H-3009", material: "Coil Galvanis 0.8mm", nett: 430.0, gross: 436.0, meter: 265, kulit: null, clamp: null },
    ],
  };
  const SRC_LABEL = { unpack: "Unpack", wip: "WIP", hold: "Hold" };
  const MATERIAL_SOURCES = [
    { key: "unpack", label: "Gudang Produksi 2 (Unpack)" },
    { key: "wip", label: "Gudang WIP" },
    { key: "hold", label: "Gudang Material Hold" },
  ];
  const COIL_FIELDS = ["code", "material", "nett", "gross", "meter", "kulit", "clamp"];

  const MATERIAL_KEY = "mockProduksi.material";
  const material = {
    // Seluruh master material per gudang. Data tersimpan meng-override seed default.
    all() {
      let saved = null;
      try { saved = JSON.parse(localStorage.getItem(MATERIAL_KEY)); } catch (e) { saved = null; }
      const base = saved && typeof saved === "object" ? saved : COILS_DEFAULT;
      const out = {};
      MATERIAL_SOURCES.forEach((s) => { out[s.key] = Array.isArray(base[s.key]) ? base[s.key] : []; });
      return out;
    },
    bySource(source) { return material.all()[source] || []; },
    // Daftar jenis material unik (untuk Costbook), terurut kemunculan.
    materialNames() {
      const seen = new Set();
      const names = [];
      const tbl = material.all();
      MATERIAL_SOURCES.forEach((s) => (tbl[s.key] || []).forEach((c) => {
        const m = (c.material || "").trim();
        if (m && !seen.has(m)) { seen.add(m); names.push(m); }
      }));
      return names;
    },
    save(table) { localStorage.setItem(MATERIAL_KEY, JSON.stringify(table || {})); },
    reset() { localStorage.removeItem(MATERIAL_KEY); },
  };
  const coilByCode = (source, code) => material.bySource(source).find((c) => c.code === code) || null;

  /* ---------- Master Rate Product Costing (NFR-07) ----------
     Rp/kg untuk komponen biaya, dan Scrap sebagai persentase dari value material.
     RATE_DEFAULT = seed; halaman Master Rate dapat meng-override & simpan ke
     localStorage (key mockProduksi.rate). Dibaca HPP untuk komponen biaya/pcs. */
  const RATE_DEFAULT = { manPower: 400, foh: 300, bahanPendukung: 500, consumables: 200, koordinasi: 200, scrapPct: 0.02 };
  // Metadata field untuk UI: key, label, satuan, tipe.
  const RATE_FIELDS = [
    { key: "manPower", label: "Man Power", unit: "Rp/kg", type: "rp" },
    { key: "foh", label: "FOH", unit: "Rp/kg", type: "rp" },
    { key: "bahanPendukung", label: "Bahan Pendukung Khusus", unit: "Rp/kg", type: "rp" },
    { key: "consumables", label: "Consumables", unit: "Rp/kg", type: "rp" },
    { key: "koordinasi", label: "Biaya Koordinasi", unit: "Rp/kg", type: "rp" },
    { key: "scrapPct", label: "Scrap, Tong Coil, Wrapping", unit: "% dari value material", type: "pct" },
  ];
  const RATE_KEY = "mockProduksi.rate";
  const rateStore = {
    // Rate efektif: default di-override oleh yang tersimpan.
    all() {
      let saved = null;
      try { saved = JSON.parse(localStorage.getItem(RATE_KEY)); } catch (e) { saved = null; }
      const out = Object.assign({}, RATE_DEFAULT);
      if (saved && typeof saved === "object") {
        RATE_FIELDS.forEach((f) => { if (saved[f.key] != null) out[f.key] = saved[f.key]; });
      }
      return out;
    },
    save(obj) { localStorage.setItem(RATE_KEY, JSON.stringify(obj || {})); },
    reset() { localStorage.removeItem(RATE_KEY); },
  };

  /* ---------- Master Costbook — nilai/kg PER MATERIAL × PER GUDANG (NFR-07b) ----------
     Daftar jenis material diturunkan dari Master Material (material.materialNames()).
     Costbook hanya menyimpan NILAI/kg per material×gudang (key mockProduksi.costbook).
     Gudang: unpack (Gudang Produksi 2), wip (Gudang WIP), hold (Gudang Material Hold). */
  const COSTBOOK_WAREHOUSES = MATERIAL_SOURCES;
  // Seed default nilai/kg (Rp) untuk material bawaan. null = belum ditetapkan.
  const COSTBOOK_DEFAULT = {
    "Coil Galvanis 1.0mm":     { unpack: 14500, wip: 14200, hold: 13800 },
    "Coil Galvanis 0.8mm":     { unpack: 14300, wip: 14000, hold: 13600 },
    "Coil Cold Rolled 1.2mm":  { unpack: 15200, wip: 14900, hold: 14400 },
  };

  // Beberapa kode gudang lama memakai "prod2" untuk Unpack — petakan agar lookup tetap jalan.
  const GUDANG_ALIAS = { prod2: "unpack", produksi2: "unpack" };
  const normGudang = (g) => GUDANG_ALIAS[g] || g;

  const COSTBOOK_KEY = "mockProduksi.costbook";
  const costbook = {
    // Daftar material = jenis material yang ada di Master Material (sumber tunggal).
    materials() { return material.materialNames(); },
    // Nilai/kg tersimpan per material×gudang (override seed default).
    savedRates() {
      let saved = null;
      try { saved = JSON.parse(localStorage.getItem(COSTBOOK_KEY)); } catch (e) { saved = null; }
      return saved && typeof saved === "object" ? saved : {};
    },
    // Tabel lengkap: satu baris per jenis material dari Master Material, nilai/kg
    // diambil dari tersimpan → seed default → null.
    all() {
      const saved = costbook.savedRates();
      const merged = {};
      costbook.materials().forEach((mat) => {
        const ov = saved[mat] || {};
        const def = COSTBOOK_DEFAULT[mat] || {};
        merged[mat] = {};
        COSTBOOK_WAREHOUSES.forEach((w) => {
          merged[mat][w.key] = ov[w.key] != null ? ov[w.key] : (def[w.key] != null ? def[w.key] : null);
        });
      });
      return merged;
    },
    // Nilai/kg untuk satu material di satu gudang; menerima alias gudang lama.
    rate(matName, gudang) {
      const g = normGudang(gudang);
      const row = costbook.all()[matName];
      const v = row ? row[g] : null;
      return v != null ? v : 0;
    },
    save(table) { localStorage.setItem(COSTBOOK_KEY, JSON.stringify(table || {})); },
    reset() { localStorage.removeItem(COSTBOOK_KEY); },
  };

  /* ---------- Pilihan mesin / helper / setter (master) ---------- */
  const MACHINES = ["Slitting Line 01", "Slitting Line 02", "Cut To Length 01"];
  const HELPERS = ["Agus Pranoto", "Dedi Kurnia", "Rahmat Hidayat"];
  const SETTERS = ["Joko Susilo", "Bambang W."];

  /* ---------- User aktif (dummy; di sistem nyata dari sesi login) ---------- */
  const CURRENT_USER = { name: "Budi S.", role: "Admin Produksi" };

  /* ---------- Store lintas-halaman ---------- */
  const KEY = "mockProduksi.session";
  const store = {
    load() {
      try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { return {}; }
    },
    save(obj) {
      const cur = store.load();
      localStorage.setItem(KEY, JSON.stringify(Object.assign(cur, obj)));
    },
    clear() { localStorage.removeItem(KEY); },
    get spkNo() { return store.load().spkNo || null; },
    set spkNo(no) { store.save({ spkNo: no }); },
    get coils() { return store.load().coils || []; },       // [{source, code}]
    set coils(arr) { store.save({ coils: arr }); },
  };

  // Status penyelesaian SPK — key TERPISAH supaya tidak ikut terhapus saat store.clear()
  const STATUS_KEY = "mockProduksi.spkStatus";
  const spkStatus = {
    all() { try { return JSON.parse(localStorage.getItem(STATUS_KEY)) || {}; } catch (e) { return {}; } },
    get(no) { return spkStatus.all()[no] || null; },     // null | "Done"
    markDone(no) {
      const m = spkStatus.all(); m[no] = "Done";
      localStorage.setItem(STATUS_KEY, JSON.stringify(m));
    },
    reset() { localStorage.removeItem(STATUS_KEY); },
  };

  // Snapshot hasil Laporan Produksi PER SPK — sumber data HPP (FR-19..FR-23).
  // Ditulis saat Save/Submit di Input Produksi; dibaca halaman HPP.
  const REPORT_KEY = "mockProduksi.report";
  const report = {
    all() { try { return JSON.parse(localStorage.getItem(REPORT_KEY)) || {}; } catch (e) { return {}; } },
    get(spkNo) { return report.all()[spkNo] || null; },
    save(spkNo, snapshot) {
      const m = report.all();
      m[spkNo] = snapshot;
      localStorage.setItem(REPORT_KEY, JSON.stringify(m));
    },
    reset() { localStorage.removeItem(REPORT_KEY); },
  };

  window.MOCK = {
    PRODUCTS, productById,
    SPKS, spkByNo, allSpks, userSpk,
    SRC_LABEL, coilByCode,
    material, MATERIAL_SOURCES, COIL_FIELDS,
    rateStore, RATE_FIELDS,
    COSTBOOK_WAREHOUSES,
    costbook,
    costbookFor: (matName, gudang) => costbook.rate(matName, gudang),
    MACHINES, HELPERS, SETTERS,
    CURRENT_USER,
    store,
    spkStatus,
    report,
    TOLERANCE: 0.7,
    // format helpers dipakai lintas halaman
    fmtNum: (n) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
    fmtKg: (n) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " kg",
    rp: (n) => "Rp " + Math.round(Number(n || 0)).toLocaleString("id-ID"),
  };
  // COILS selalu merefleksikan Master Material terkini (dibaca oleh Laporan Produksi).
  Object.defineProperty(window.MOCK, "COILS", { get() { return material.all(); }, enumerable: true });
  // RATE selalu merefleksikan Master Rate terkini (dibaca oleh HPP).
  Object.defineProperty(window.MOCK, "RATE", { get() { return rateStore.all(); }, enumerable: true });
})();
