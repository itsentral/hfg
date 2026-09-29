/* ============================================================
   data.js — Master data bersama (dummy) untuk seluruh halaman mockup.
   Diekspos sebagai window.MOCK. Dipakai oleh beranda, index (input), hpp.
   State lintas-halaman (SPK terpilih + coil yang di-add) disimpan di localStorage.
   ============================================================ */
(function () {
  "use strict";

  /* ---------- Master Product + berat standar/pcs (FR-09) ---------- */
  const PRODUCTS = [
    { id: "P01", name: "Plat Galvanis 1.0 x 1000", std: 24.0, size: 2.4 },
    { id: "P02", name: "Plat Galvanis 0.8 x 1000", std: 18.5, size: 2.4 },
    { id: "P03", name: "Plat Cold Rolled 1.2 x 1200", std: 31.2, size: 3.0 },
    { id: "P04", name: "Coil Slit 300mm", std: 120.0, size: 100 },
    { id: "P05", name: "Plat Galvanis 1.2 x 1200", std: 34.6, size: 3.0 },
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
  const spkByNo = (no) => SPKS.find((s) => s.no === no) || null;

  /* ---------- Baby coil per sumber gudang (FR-08) ---------- */
  const COILS = {
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
  const coilByCode = (source, code) => (COILS[source] || []).find((c) => c.code === code) || null;

  /* ---------- Rate Product Costing (Rp/kg) & Costbook Material per gudang (NFR-07) ---------- */
  const RATE = { manPower: 400, foh: 300, bahanPendukung: 500, consumables: 200, koordinasi: 200, scrapPct: 0.02 };
  const COSTBOOK = { prod2: 14500, wip: 14200, hold: 13800 };

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

  window.MOCK = {
    PRODUCTS, productById,
    SPKS, spkByNo,
    COILS, SRC_LABEL, coilByCode,
    RATE, COSTBOOK,
    MACHINES, HELPERS, SETTERS,
    CURRENT_USER,
    store,
    spkStatus,
    TOLERANCE: 0.7,
    // format helpers dipakai lintas halaman
    fmtNum: (n) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
    fmtKg: (n) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " kg",
    rp: (n) => "Rp " + Math.round(Number(n || 0)).toLocaleString("id-ID"),
  };
})();
