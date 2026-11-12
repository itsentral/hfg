/* ============================================================
   wh-data.js — Data contoh Modul Gudang Coil (mockup). Dipakai semua halaman wh-*.html.
   Acuan modul aplikasi (read-only): warehouse, material_master, request_list, spk_material,
   stock_from_warehouse, unpack_coil. Kolom/tabel mengikuti migrasi + model di application/modules.
   Data hanya contoh (bukan data klien); state disimpan di localStorage browser (kunci mockWH.data).
   ============================================================ */
(() => {
  "use strict";
  const KEY = "mockWH.data4";
  const deep = (o) => JSON.parse(JSON.stringify(o));

  /* ---------- Master Material (material_master) ---------- */
  const genNama = (m) => [m.lv1, m.lv2, m.lv3].filter(Boolean).join(" ") + " " + m.thickness + "-" + m.width + "-" + m.coating + " - " + m.tensile + " - " + m.warna;
  const LV1 = ["GALVALUME", "GALVANIZED", "ALUMINIUM"];
  const LV2 = ["MOTHER COIL", "SLITTED COIL"];
  const LV3 = ["NON BORON", "BORON"];
  const COATING = ["AZ150", "AZ100", "Z120", "Z80"];
  const TENSILE = ["G550", "G300", "G350"];
  const UNIT = ["KG", "PCS", "METER", "COIL", "PACK"];
  const SUPPLIER = ["PT Sumber Baja Perkasa", "PT Bluescope Lysaght", "PT Nusa Steel Coil", "PT Alu Makmur"];
  const HS = ["7210.61.11", "7210.49.11", "7606.12.00"];
  const mat = (id, lv1, lv2, lv3, thickness, width, coating, tensile, warna, hs, hardness, price, aktif) => ({
    id, lv1, lv2, lv3, thickness, width, coating, tensile, warna, hs, trade: lv1.slice(0, 2) + " " + coating + " " + tensile, hardness,
    no_accurate: "ACC-" + id.slice(-3), unit_other: "KG", konversi_other: 1, unit_packing: "COIL", konversi: 1, unit: "KG",
    max_stok: 60000, min_stok: 10000, supplier: SUPPLIER[0], length: "", wide: "", high: "", msds: "", harga_beli: price, aktif: aktif == null ? 1 : aktif,
  });
  const MATS = [
    mat("MTR-001", "GALVALUME", "MOTHER COIL", "NON BORON", "0.40", "1219", "AZ150", "G550", "SILVER", HS[0], "85", 14850),
    mat("MTR-002", "GALVALUME", "MOTHER COIL", "NON BORON", "0.35", "1219", "AZ100", "G550", "SILVER", HS[0], "85", 15120),
    mat("MTR-003", "GALVALUME", "SLITTED COIL", "NON BORON", "0.40", "305", "AZ150", "G550", "SILVER", HS[0], "85", 15400),
    mat("MTR-004", "GALVANIZED", "MOTHER COIL", "BORON", "0.50", "1219", "Z120", "G300", "NATURAL", HS[1], "70", 13980),
    mat("MTR-005", "GALVANIZED", "SLITTED COIL", "NON BORON", "0.45", "610", "Z80", "G300", "NATURAL", HS[1], "70", 14210),
    mat("MTR-006", "ALUMINIUM", "MOTHER COIL", "NON BORON", "0.60", "1000", "AZ100", "G350", "MILL FINISH", HS[2], "24", 42300, 0),
  ];
  MATS.push(
    mat("MTR-007", "GALVALUME", "MOTHER COIL", "NON BORON", "0.30", "1219", "AZ100", "G550", "SILVER", HS[0], "85", 15800),
    mat("MTR-008", "GALVALUME", "SLITTED COIL", "NON BORON", "0.35", "610", "AZ100", "G550", "SILVER", HS[0], "85", 16050),
    mat("MTR-009", "GALVANIZED", "MOTHER COIL", "NON BORON", "0.60", "1219", "Z120", "G350", "NATURAL", HS[1], "70", 14400),
    mat("MTR-010", "GALVANIZED", "SLITTED COIL", "BORON", "0.55", "305", "Z80", "G300", "NATURAL", HS[1], "70", 14550),
    mat("MTR-011", "GALVALUME", "MOTHER COIL", "BORON", "0.45", "1000", "AZ150", "G550", "BIRU", HS[0], "85", 15200),
    mat("MTR-012", "ALUMINIUM", "SLITTED COIL", "NON BORON", "0.50", "1000", "AZ100", "G300", "MILL FINISH", HS[2], "24", 41800, 0));
  MATS.forEach((m, i) => { m.supplier = SUPPLIER[i % SUPPLIER.length]; m.max_stok = 40000 + (i % 4) * 10000; m.min_stok = 5000 + (i % 3) * 2500; });
  MATS.forEach((m) => { m.nama = genNama(m); });
  const matBy = (id) => MATS.find((m) => m.id === id);

  /* ---------- Pack & coil (warehouse_pack, warehouse_stock_coil) ---------- */
  // [pack_code, kd_gudang, id_material, jumlah coil, net weight dasar, is_baby_coil]
  const PACKSPEC = [
    ["PCK-2611-001", "PRO", "MTR-001", 3, 6120, 0],
    ["PCK-2611-002", "PRO", "MTR-001", 2, 5980, 0],
    ["PCK-2611-003", "PRO", "MTR-002", 3, 5410, 0],
    ["PCK-2611-004", "PRT", "MTR-004", 2, 7020, 0],
    ["PCK-2611-005", "PRO", "MTR-003", 4, 1850, 1],
    ["PCK-2611-006", "SLI", "MTR-003", 4, 1620, 1],
    ["PCK-2611-007", "SLI", "MTR-005", 3, 1480, 1],
    ["PCK-2611-008", "PRT", "MTR-001", 2, 6240, 0],
    ["PCK-2611-009", "PRO", "MTR-004", 2, 6900, 0],
    ["PCK-2611-010", "PRO", "MTR-001", 2, 6050, 0],
    ["PCK-2611-011", "PRO", "MTR-007", 3, 5300, 0],
    ["PCK-2611-012", "PRO", "MTR-009", 2, 6600, 0],
    ["PCK-2611-013", "SLI", "MTR-008", 4, 1550, 1],
    ["PCK-2611-014", "SLI", "MTR-010", 3, 1380, 1],
    ["PCK-2611-015", "PRO", "MTR-011", 2, 5750, 0],
    ["PCK-2610-011", "PRT", "MTR-001", 2, 6180, 0],
    ["PCK-2610-012", "PRT", "MTR-009", 2, 6500, 0],
    ["PCK-2610-013", "PRT", "MTR-007", 2, 5400, 0],
  ];
  const mkCoils = () => {
    const coils = []; let n = 0;
    PACKSPEC.forEach((p, pi) => {
      for (let i = 0; i < p[3]; i++) {
        n++;
        const nw = p[4] + ((n * 37) % 90), gw = nw + 40 + (n % 5) * 3;
        coils.push({ id: n, pack: p[0], gudang: p[1], mat: p[2], no_coil: "5A" + String(1234 + pi * 7 + i).padStart(7, "0"), kode_internal: "DEBDA03" + String(19 + pi) + "-T" + (i + 1) + (p[5] ? "-BC." + (i + 1) : ""), net_weight: nw, gross_weight: gw, length: p[5] ? 1400 + n * 11 : 1800 + n * 13, baby: p[5] });
      }
    });
    return coils;
  };

  /* ---------- SPK Material (tr_spk_material_header/detail) + SPK Coil (tr_warehouse_request_*) ---------- */
  const SHIFT = ["Shift 1 (07.00-15.00)", "Shift 2 (15.00-23.00)", "Shift 3 (23.00-07.00)"];
  const PRODUK = [
    { id: "P426000001", nm: "ATAP SPANDEK POLOS 0.4 X 1000 X 6 M", berat: 3.2, bom: [["MTR-001", 1.00]] },
    { id: "P426000002", nm: "ATAP GENTENG METAL POLOS 0.35 X 800 X 1.2 M", berat: 2.4, bom: [["MTR-002", 1.00]] },
    { id: "P426000003", nm: "BAJA RINGAN RENG R35.45 0.45 X 35 X 6 M", berat: 1.9, bom: [["MTR-003", 1.00]] },
    { id: "P426000004", nm: "RANGKA HOLLOW GALVANIS 1.2 X 40 X 6 M", berat: 4.6, bom: [["MTR-004", 1.00], ["MTR-005", 0.20]] },
    { id: "P426000005", nm: "ATAP SPANDEK WARNA 0.45 X 1000 X 6 M", berat: 3.4, bom: [["MTR-011", 1.00]] },
    { id: "P426000006", nm: "BAJA RINGAN CANAL C C75.70 B 0.75 X 75 X 6 M", berat: 2.2, bom: [["MTR-009", 1.00]] },
  ];
  // Master Produk = Product Costing > Master Produk (pc-data.js, kunci mockPC.data). Nama / status / berat diambil dari sana bila dimuat;
  // PRODUK di atas hanya melengkapi BOM material gudang per produk (belum ada di spesifikasi Master Produk).
  const pcProd = (kode) => { try { return window.PC && window.PC.state.products.find((x) => x.kode === kode) || null; } catch (e) { return null; } };
  const prodBy = (id) => { const p = PRODUK.find((x) => x.id === id); if (!p) return null; const m = pcProd(id); return Object.assign({}, p, m ? { nm: m.name, berat: Number(m.beratUnit) > 0 ? Number(m.beratUnit) : p.berat, aktif: m.status === "Aktif" } : { aktif: true }); };
  const spkDet = (id, qty) => { const p = prodBy(id); return { id_produk_fg: id, nm_produk_fg: p.nm, target_qty: qty, berat_per_unit: p.berat, total_weight: Math.round(qty * p.berat * 100) / 100 }; };
  const SPK = [
    { spk_no: "SPK-202611-0001", tgl_spk: "2026-11-02", due_date: "2026-11-07", shift_names: [SHIFT[0]], catatan: "Prioritas order proyek", status: "Material Requested", detail: [spkDet("P426000001", 1800), spkDet("P426000002", 900)], spkc: null },
    { spk_no: "SPK-202611-0002", tgl_spk: "2026-11-03", due_date: "2026-11-08", shift_names: [SHIFT[0], SHIFT[1]], catatan: "", status: "Material On Load", detail: [spkDet("P426000003", 2500)], spkc: { no: "SPKC-202611-0001", date: "2026-11-03", packs: ["PCK-2611-005"], scanned: [] } },
    { spk_no: "SPK-202611-0003", tgl_spk: "2026-11-01", due_date: "2026-11-05", shift_names: [SHIFT[1]], catatan: "Stok minus, kejar kirim", status: "Material Confirmed", detail: [spkDet("P426000004", 1200)], spkc: { no: "SPKC-202610-0007", date: "2026-11-01", packs: ["PCK-2611-004"], scanned: ["PCK-2611-004"] } },
    { spk_no: "SPK-202610-0019", tgl_spk: "2026-10-28", due_date: "2026-11-02", shift_names: [SHIFT[0]], catatan: "", status: "Released", detail: [spkDet("P426000001", 1500)], spkc: { no: "SPKC-202610-0005", date: "2026-10-28", packs: ["PCK-2611-008"], scanned: ["PCK-2611-008"] } },
    { spk_no: "SPK-202611-0004", tgl_spk: "2026-11-04", due_date: "2026-11-09", shift_names: [SHIFT[0], SHIFT[2]], catatan: "Order toko bangunan", status: "Material Requested", detail: [spkDet("P426000002", 600), spkDet("P426000003", 800)], spkc: null },
    { spk_no: "SPK-202611-0005", tgl_spk: "2026-11-04", due_date: "2026-11-09", shift_names: [SHIFT[1]], catatan: "Warna biru", status: "Material On Load", detail: [spkDet("P426000005", 1000)], spkc: { no: "SPKC-202611-0002", date: "2026-11-04", packs: ["PCK-2611-015"], scanned: [] } },
    { spk_no: "SPK-202611-0006", tgl_spk: "2026-11-05", due_date: "2026-11-10", shift_names: [SHIFT[0]], catatan: "", status: "Material Requested", detail: [spkDet("P426000006", 1200)], spkc: null },
    { spk_no: "SPK-202611-0007", tgl_spk: "2026-11-05", due_date: "2026-11-10", shift_names: [SHIFT[0], SHIFT[1]], catatan: "Scan baru sebagian", status: "Material On Load", detail: [spkDet("P426000001", 1800)], spkc: { no: "SPKC-202611-0003", date: "2026-11-05", packs: ["PCK-2611-010", "PCK-2611-001"], scanned: ["PCK-2611-010"] } },
    { spk_no: "SPK-202610-0017", tgl_spk: "2026-10-24", due_date: "2026-10-29", shift_names: [SHIFT[1]], catatan: "", status: "Released", detail: [spkDet("P426000004", 900)], spkc: null },
    { spk_no: "SPK-202610-0011", tgl_spk: "2026-10-15", due_date: "2026-10-20", shift_names: [SHIFT[0]], catatan: "Dibatalkan pelanggan", status: "Cancelled", detail: [spkDet("P426000001", 500)], spkc: null },
    { spk_no: "SPK-202610-0014", tgl_spk: "2026-10-20", due_date: "2026-10-25", shift_names: [SHIFT[2]], catatan: "Dibatalkan, order batal", status: "Cancelled", detail: [spkDet("P426000002", 700)], spkc: null },
  ];

  /* ---------- Stock From Warehouse (PRT / WIP / HLD) ---------- */
  const sfw = (kd, mid, nc, ki, nw, len, asal, kode) => ({ kd, mat: mid, no_coil: nc, kode_internal: ki, net_weight: nw, gross_weight: nw + 55, length: len, gudang_asal: asal, kode_trans: kode });
  const SFW = [
    sfw("WIP", "MTR-001", "5A0001203", "DEBDA0319-T3-R", 1240, 420, "PRT", "RPT-202610-0003"),
    sfw("WIP", "MTR-002", "5A0002210", "DEBDA0320-T1-R", 980, 335, "PRT", "RPT-202610-0004"),
    sfw("HLD", "MTR-003", "5A0003305", "DEBDA0323-T2-BC.2", 1612, 1411, "PRT", "RPT-202610-0006"),
    sfw("WIP", "MTR-007", "5A0007301", "DEBDA0327-T1-R", 1120, 380, "PRT", "RPT-202611-0001"),
    sfw("WIP", "MTR-009", "5A0009102", "DEBDA0328-T2-R", 860, 300, "PRT", "RPT-202611-0002"),
    sfw("WIP", "MTR-004", "5A0004190", "DEBDA0322-T3-R", 1010, 340, "PRT", "RPT-202611-0003"),
    sfw("HLD", "MTR-008", "5A0008210", "DEBDA0329-T1-BC.1", 1540, 1380, "PRT", "RPT-202611-0004"),
    sfw("HLD", "MTR-010", "5A0010220", "DEBDA0330-T2-BC.2", 1360, 1210, "PRT", "RPT-202611-0005"),
    sfw("HLD", "MTR-002", "5A0002215", "DEBDA0320-T4-R", 760, 260, "PRT", "RPT-202610-0009"),
    sfw("HLD", "MTR-005", "5A0005120", "DEBDA0325-T1-BC.1", 1470, 1390, "PRT", "RPT-202610-0008"),
  ];
  const SFW_HIST = [
    { tgl: "2026-11-02", kd: "PRT" }, { tgl: "2026-11-03", kd: "WIP" }, { tgl: "2026-11-04", kd: "HLD" },
  ];

  /* ---------- Report Unpack (tr_unpack_coil_header/material/baby) ---------- */
  const bc = (kodeInt, n, nwP) => ({ babycoil_code: kodeInt + "-BC." + n, no_coil: "", net_weight_actual: nwP, gross_weight_actual: nwP + 30, net_weight_pl: nwP, gross_weight_pl: nwP + 30 });
  const UNPACK = [
    { unpack_no: "UNPK-202611-0001", tgl_unpack: "2026-11-04 09:12", pack_code: "PCK-2611-004", catatan_kulit: "Kulit 12 kg", catatan_clamp_ring: "Clamp 4 pcs", net_weight_actual: 14040, gross_weight_actual: 14185, net_weight_pl: 14051, gross_weight_pl: 14190, catatan: "", status: "Confirmed",
      mats: [{ id_material: "MTR-004", no_coil: "5A0004101", kode_internal: "DEBDA0322-T1", jumlah_coil_roll: 2, net_weight_actual: 7030, gross_weight_actual: 7090, net_weight_pl: 7040, gross_weight_pl: 7095, babies: [bc("DEBDA0322-T1", 1, 3515), bc("DEBDA0322-T1", 2, 3515)] },
             { id_material: "MTR-004", no_coil: "5A0004102", kode_internal: "DEBDA0322-T2", jumlah_coil_roll: 2, net_weight_actual: 7010, gross_weight_actual: 7095, net_weight_pl: 7011, gross_weight_pl: 7095, babies: [bc("DEBDA0322-T2", 1, 3505), bc("DEBDA0322-T2", 2, 3505)] }] },
    { unpack_no: "UNPK-202610-0003", tgl_unpack: "2026-10-30 14:40", pack_code: "PCK-2610-011", catatan_kulit: "", catatan_clamp_ring: "", net_weight_actual: 6150, gross_weight_actual: 6210, net_weight_pl: 6155, gross_weight_pl: 6215, catatan: "Dibatalkan, salah pilih pack", status: "Cancelled",
      mats: [{ id_material: "MTR-001", no_coil: "5A0001201", kode_internal: "DEBDA0319-T1", jumlah_coil_roll: 1, net_weight_actual: 6150, gross_weight_actual: 6210, net_weight_pl: 6155, gross_weight_pl: 6215, babies: [bc("DEBDA0319-T1", 1, 6150)] }] },
  ];

  /* ---------- Riwayat transaksi stok per material (warehouse_stock_history) ---------- */
  const TRX = {
    "MTR-007": [["2026-10-27", "GRN-202610-0041", "IN", 5, 26500, 15800]],
    "MTR-008": [["2026-10-29", "UNPK-202610-0006", "IN", 8, 6200, 16050]],
    "MTR-009": [["2026-10-23", "GRN-202610-0036", "IN", 4, 26000, 14400], ["2026-11-03", "SPKC-202611-0001", "OUT", 1, 6500, null]],
    "MTR-010": [["2026-10-30", "UNPK-202610-0007", "IN", 5, 4140, 14550]],
    "MTR-011": [["2026-10-31", "GRN-202610-0044", "IN", 2, 11500, 15200]],
    "MTR-012": [["2026-10-19", "GRN-202610-0028", "IN", 3, 6000, 41800]],
    "MTR-001": [["2026-10-20", "GRN-202610-0031", "IN", 4, 24800, 14700], ["2026-10-24", "GRN-202610-0038", "IN", 3, 18400, 14980], ["2026-10-28", "SPKC-202610-0005", "OUT", 2, 12345, null], ["2026-11-02", "GRN-202611-0004", "IN", 2, 12090, 14850]],
    "MTR-002": [["2026-10-22", "GRN-202610-0034", "IN", 3, 16230, 15120], ["2026-10-30", "SPKC-202610-0009", "OUT", 1, 5410, null]],
    "MTR-003": [["2026-10-25", "UNPK-202610-0002", "IN", 8, 12950, 15400], ["2026-11-01", "SPKC-202611-0002", "OUT", 2, 3300, null]],
    "MTR-004": [["2026-10-21", "GRN-202610-0032", "IN", 3, 21000, 13980], ["2026-11-01", "SPKC-202610-0007", "OUT", 2, 14051, null]],
    "MTR-005": [["2026-10-26", "UNPK-202610-0004", "IN", 6, 8880, 14210]],
    "MTR-006": [["2026-10-18", "GRN-202610-0027", "IN", 2, 9000, 42300]],
  };

  const W2 = (n) => Math.round(n * 100) / 100;
  const mkU = (no, tgl, pack, status, coils, note) => {
    const cs = coils.filter((c) => c.pack === pack), mats = cs.map((c) => { const h = W2(c.net_weight / 2 - 3), g = W2(c.gross_weight / 2 - 3);
      return { id_material: c.mat, no_coil: c.no_coil, kode_internal: c.kode_internal, jumlah_coil_roll: 2, net_weight_actual: h * 2, gross_weight_actual: g * 2, net_weight_pl: c.net_weight, gross_weight_pl: c.gross_weight,
        babies: [1, 2].map((k) => ({ babycoil_code: c.kode_internal + "-BC." + k, no_coil: c.no_coil, net_weight_actual: h, gross_weight_actual: g, net_weight_pl: W2(c.net_weight / 2), gross_weight_pl: W2(c.gross_weight / 2) })) }; });
    const sum = (k) => mats.reduce((a, m) => a + m[k], 0);
    return { unpack_no: no, tgl_unpack: tgl, pack_code: pack, catatan_kulit: "Kulit 10 kg", catatan_clamp_ring: "Clamp 6 pcs", net_weight_actual: sum("net_weight_actual"), gross_weight_actual: sum("gross_weight_actual"), net_weight_pl: sum("net_weight_pl"), gross_weight_pl: sum("gross_weight_pl"), catatan: note, status, mats };
  };
  const initial0 = () => { const coils = mkCoils(); return { mats: deep(MATS), coils, spk: deep(SPK), sfw: deep(SFW), unpack: deep(UNPACK).concat([mkU("UNPK-202610-0005", "2026-10-29 11:05", "PCK-2610-012", "Lock", coils, "Sudah di-lock Accounting"), mkU("UNPK-202611-0002", "2026-11-05 15:20", "PCK-2610-013", "Confirmed", coils, "")]), trx: {}, seq: { spk: 7, spkc: 3, unpack: 2 } }; };
  let state;
  const initial = () => { const st = initial0(); state = st; st.unpack.filter((u) => u.status === "Confirmed" || u.status === "Lock").forEach((u) => unpackApply(u)); return st; };
  let storageOk = true;
  const load = () => { try { const s = JSON.parse(localStorage.getItem(KEY)); if (s && s.mats && s.seq && s.trx) return s; } catch (e) { storageOk = false; } return initial(); };
  const save = () => { try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) { storageOk = false; } };
  state = load();
  if (!state) state = initial();

  const round2 = (n) => Math.round(Number(n || 0) * 100) / 100;
  const fm = (id) => (matBy(id) || state.mats.find((m) => m.id === id) || {});
  const today = () => "2026-11-06";

  // posisi stok baby coil hasil unpack: coil induk diganti baby coil (gudang tetap), edit/hapus mengembalikan coil induk
  function unpackApply(rec) {
    const pc = state.coils.filter((c) => c.pack === rec.pack_code && !c.baby_of);
    rec.mats.forEach((m, i) => {
      const par = pc[i]; if (!par) return;
      m.parent = JSON.parse(JSON.stringify(par));
      m.id_material = par.mat; m.no_coil = par.no_coil; m.kode_internal = par.kode_internal;
      state.coils = state.coils.filter((c) => c.id !== par.id);
      (m.babies || []).forEach((b, k) => { b.id_coil_baru = 9000 + state.coils.length + k; state.coils.push({ id: b.id_coil_baru, pack: par.pack, gudang: par.gudang, mat: par.mat, no_coil: par.no_coil, kode_internal: b.babycoil_code, net_weight: b.net_weight_actual, gross_weight: b.gross_weight_actual, length: Math.round(par.length / Math.max(1, m.babies.length)), baby: 1, baby_of: rec.unpack_no }); });
    });
  }
  function unpackRevert(rec) {
    state.coils = state.coils.filter((c) => c.baby_of !== rec.unpack_no);
    rec.mats.forEach((m) => { if (m.parent) { state.coils.push(m.parent); delete m.parent; } });
    state.coils.sort((x, y) => x.id - y.id);
  }
  const curQty = (mid) => state.coils.filter((c) => c.mat === mid && (c.gudang === "PRO" || c.gudang === "SLI")).reduce((a, c) => a + c.net_weight, 0);
  const curRoll = (mid) => state.coils.filter((c) => c.mat === mid && (c.gudang === "PRO" || c.gudang === "SLI")).length;
  const run = (list) => {
    const rows = []; let qty = 0, saldo = 0, harga = 0;
    list.forEach((t) => {
      const [tgl, no, st, cc, q, p] = t, hLama = harga, qAwal = qty, sAwal = saldo; let tot;
      if (st === "IN") { tot = round2(q * p); qty += q; saldo = round2(saldo + tot); harga = qty ? round2(saldo / qty) : 0; }
      else { tot = round2(q * harga); qty -= q; saldo = round2(saldo - tot); }
      rows.push({ tgl, no, status: st, coil: cc, qty_awal: qAwal, qty_trx: q, qty_akhir: qty, total_harga: tot, saldo_awal: sAwal, saldo_akhir: saldo, harga_lama: hLama, harga_baru: harga });
    });
    return rows;
  };
  // riwayat stok satu material (rata-rata bergerak); baris SALDO-AWAL / PENYESUAIAN dibuat agar saldo akhir = stok saat ini
  function history(mid) {
    const list = (TRX[mid] || []).concat((state.trx || {})[mid] || []).map((t, i) => ({ t, i })).sort((a, b) => a.t[0].localeCompare(b.t[0]) || a.i - b.i).map((x) => x.t);
    const fin = run(list), last = fin.length ? fin[fin.length - 1].qty_akhir : 0, delta = curQty(mid) - last;
    if (delta > 0.004) list.unshift(["2026-10-01", "SALDO-AWAL", "IN", curRoll(mid), delta, fm(mid).harga_beli]);
    else if (delta < -0.004) list.push([today(), "PENYESUAIAN", "OUT", 0, -delta, null]);
    return run(list);
  }
  const avgHarga = (mid) => { const h = history(mid); return h.length && h[h.length - 1].harga_baru ? h[h.length - 1].harga_baru : fm(mid).harga_beli; };
  function addTrx(mid, tgl, no, st, coil, qty) { ((state.trx[mid]) || (state.trx[mid] = [])).push([tgl, no, st, coil, qty, null]); }
  // ringkasan stok per gudang (stock_value): per material, harga = rata-rata bergerak dari riwayat
  function stockValue(kd) {
    const out = {};
    state.coils.filter((c) => c.gudang === kd).forEach((c) => {
      const o = out[c.mat] || (out[c.mat] = { mat: c.mat, roll: 0, qty: 0 });
      o.roll++; o.qty += c.net_weight;
    });
    return Object.values(out).map((o) => { const h = avgHarga(o.mat); return { ...o, harga: h, total: round2(o.qty * h) }; });
  }

  window.WH = {
    get state() { return state; }, save, storageOk: () => storageOk,
    reset() { state = initial(); save(); },
    LV1, LV2, LV3, COATING, TENSILE, UNIT, SUPPLIER, HS, SHIFT, get PRODUK() { return PRODUK.map((p) => prodBy(p.id)); }, prodBy, genNama, matBy: fm, today, round2, stockValue, history, unpackApply, unpackRevert, addTrx,
    nextNo(kind) {
      const s = state.seq, ym = "202611";
      if (kind === "spk") return "SPK-" + ym + "-" + String(++s.spk).padStart(4, "0");
      if (kind === "spkc") return "SPKC-" + ym + "-" + String(++s.spkc).padStart(4, "0");
      return "UNPK-" + ym + "-" + String(++s.unpack).padStart(4, "0");
    },
    packs(kd) {
      const map = {};
      state.coils.filter((c) => !kd || c.gudang === kd).forEach((c) => { (map[c.pack] || (map[c.pack] = { code: c.pack, gudang: c.gudang, coils: [] })).coils.push(c); });
      return Object.values(map).map((p) => {
        const nw = p.coils.reduce((a, c) => a + c.net_weight, 0), gw = p.coils.reduce((a, c) => a + c.gross_weight, 0);
        return { ...p, roll: p.coils.length, nw, gw, nwRoll: nw / p.coils.length, gwRoll: gw / p.coils.length, mats: [...new Set(p.coils.map((c) => c.mat))] };
      });
    },
    SFW_HIST,
  };
})();
