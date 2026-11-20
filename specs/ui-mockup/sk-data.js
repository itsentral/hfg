/* ============================================================
   sk-data.js — Data contoh Modul Subkont (mockup tahap 1: SPK Subkont + Tampungan Siap Kirim).
   Acuan dokumen: docs/fr-nfr.md FR-99–FR-104 (SPK Subkont, Request, Tampungan), UC-17–UC-19, UC-N37, ERD "Subkont ERD":
   tr_spk_subkont_header/detail, tr_subkont_tampungan. Surat Jalan: tr_subkont_delivery/_detail, subkont_in_supplier_ledger (FR-105–FR-112). Stok sumber: pack gudang PRO (Material, per Pack) dan fg_stock KW 1 (FG, per pcs).
   Diekspos sebagai window.WH (nama sama dengan wh-data.js agar wh-core.js bisa dipakai ulang). State di localStorage (mockSK.data1).
   Angka hanya contoh, bukan data klien.
   ============================================================ */
(() => {
  "use strict";
  const KEY = "mockSK.data7";
  const deep = (o) => JSON.parse(JSON.stringify(o));
  const today = () => "2026-11-12";
  const round = (n, d = 2) => Math.round((Number(n) || 0) * Math.pow(10, d)) / Math.pow(10, d);

  /* ---------- Sumber stok Material: pack di gudang PRO (warehouse_pack + warehouse_stock_coil) ---------- */
  const MAT = [
    { kode_barang: "SBJ-ZGAGBA232-01-004", nama_barang: "GALVALUM MOTHER NON BORON 0.35-1219-AZ100-G550-" },
    { kode_barang: "SBJ-ZGAGBA232-01-006", nama_barang: "GALVALUME MOTHER COIL NON BORON 0.40-1219-AZ150 - G550 - SILVER" },
    { kode_barang: "SBJ-GVBBA118-02-001", nama_barang: "GALVANIZED MOTHER COIL BORON 0.50-1219-Z120 - G300 - NATURAL" },
  ];
  const pk = (pack_code, kode_barang, kode_internal, roll, net_weight) => ({ pack_code, kode_barang, kode_internal, roll, net_weight, kd_gudang: "PRO" });
  const PACKS = [
    pk("PCK-2609-001", MAT[0].kode_barang, "DEBDA0319-T1", 1, 3798), pk("PCK-2609-002", MAT[0].kode_barang, "DEBDA0319-T2", 1, 3812),
    pk("PCK-2609-003", MAT[0].kode_barang, "DEBDA0319-T3", 1, 3888), pk("PCK-2609-004", MAT[0].kode_barang, "DEBDA0319-T4", 1, 3760),
    pk("PCK-2609-011", MAT[1].kode_barang, "DEBDA0322-T1", 1, 6120), pk("PCK-2609-012", MAT[1].kode_barang, "DEBDA0322-T2", 1, 5980),
    pk("PCK-2609-013", MAT[1].kode_barang, "DEBDA0322-T3", 1, 6050), pk("PCK-2609-021", MAT[2].kode_barang, "DEBDA0330-T1", 1, 7020),
    pk("PCK-2609-022", MAT[2].kode_barang, "DEBDA0330-T2", 1, 6900),
    { pack_code: "PCK-2609-099", kode_barang: MAT[0].kode_barang, kode_internal: "DEBDA0319-T9", roll: 1, net_weight: 3700, kd_gudang: "SLI" },   // contoh pack di gudang lain (ditolak scan)
  ];
  /* ---------- Sumber stok FG: fg_stock KW 1 (qty_stok pcs, berat_referensi kg/pcs) ---------- */
  const FG = [
    { kode_barang: "PRD-0101", nama_barang: "Spandek 0.30 x 1000", qty_stok: 48, berat_referensi: 24.0 },
    { kode_barang: "PRD-0102", nama_barang: "Spandek 0.35 x 1000", qty_stok: 80, berat_referensi: 34.6 },
    { kode_barang: "PRD-0105", nama_barang: "Reng 0.45", qty_stok: 250, berat_referensi: 4.8 },
  ];
  /* ---------- Master Supplier (new_supplier; vendor Surat Jalan dipilih dari seluruh Master Supplier, keputusan user 2026-11-20) ---------- */
  // Master Supplier (tabel new_supplier; kolom sama dengan modul supplier yang sudah ada). Dikelola Purchasing; vendor Surat Jalan dipilih dari sini.
  const sup = (id, nama, id_country, telp, fax, email, contact_person, inisial, tax_number, address, last_by, last_date) => ({ id, nama, id_country, telp, telp2: "", fax, email, contact_person, email2: "", inisial, contact: "", tax_number, id_currency: "IDR", bank_account: "", address, tax_address: address, note: "", last_by, last_date });
  const SUPPLIERS = [
    sup(23, "PT KARYA PADU SEJATI", "Indonesia", "021-84593076", "021-84593077", "budi@karyapadu.example", "Budi Hartono", "KPS", "01.234.567.8-001.000", "Jl. Bina Marga No. 29 Rt. 005 Rw. 005, Kel. Ceger, Kec. Cipayung, Jakarta Timur", "Dewi (Purchasing)", "2026-10-21"),
    sup(31, "PT SLIT MAKMUR JAYA", "Indonesia", "021-8900123", "", "info@slitmakmur.example", "Rudi", "SMJ", "02.345.678.9-002.000", "Kawasan Industri Jababeka, Cikarang, Bekasi", "Dewi (Purchasing)", "2026-09-14"),
    sup(44, "CV PASIR WARNA NUSANTARA", "Indonesia", "0251-8231100", "", "cs@pasirwarna.example", "Sari", "PWN", "", "Jl. Raya Narogong Km 12, Bogor", "Dewi (Purchasing)", "2026-08-30"),
    sup(51, "PT BAJA UTAMA SEJAHTERA", "Indonesia", "021-5550123", "021-5550124", "sales@bajautama.example", "Hendra", "BUS", "03.456.789.0-003.000", "Jl. Industri Raya No. 7, Tangerang", "Dewi (Purchasing)", "2026-07-02"),
  ];
  // master costbook (data internal HFG; tidak pernah tampil di Portal Vendor): Material Rp/kg, FG Rp/pcs
  const COSTBOOK = { "SBJ-ZGAGBA232-01-004": 10000, "SBJ-ZGAGBA232-01-006": 14850, "SBJ-GVBBA118-02-001": 13980, "PRD-0101": 411709, "PRD-0102": 580000, "PRD-0105": 82000 };
  const JENIS = ["SPK Subkont Material", "SPK Subkont FG"];
  const TUJUAN = { "SPK Subkont Material": "Warehouse Material", "SPK Subkont FG": "Warehouse Finish Good" };
  const SATUAN = { "SPK Subkont Material": "Pack", "SPK Subkont FG": "pcs" };

  const det = (kode_barang, qty_subkont, keterangan) => ({ kode_barang, qty_subkont, keterangan: keterangan || "" });
  const tp = (spk_no, kode_barang, pack_code, qty, net_weight, status, tgl, by, kode_delivery) => ({ id: 0, spk_no, kode_barang, pack_code: pack_code || "", qty, net_weight, status, tgl_masuk: tgl, scan_by: by, kode_delivery: kode_delivery || "" });
  function initial() {
    const st = {
      spk: [
        { spk_no: "SPK-Sub-001", jenis_subkont: JENIS[0], tgl_spk: "2026-11-09", proses_disubkont: "Proses Slitting Material", catatan: "Slitting lebar 200 mm", status: "Draft", created_by: "Dedi K.", detail: [det(MAT[0].kode_barang, 2, "Pack PCK-2609-001 dan -002 diutamakan")] },
        { spk_no: "SPK-Sub-002", jenis_subkont: JENIS[1], tgl_spk: "2026-11-10", proses_disubkont: "Proses Tambah Pasir (merah)", catatan: "", status: "Requested", created_by: "Dedi K.", detail: [det("PRD-0102", 40, "Order proyek")] },
        { spk_no: "SPK-Sub-003", jenis_subkont: JENIS[0], tgl_spk: "2026-11-10", proses_disubkont: "Proses Slitting Material", catatan: "Gudang siapkan bertahap", status: "Requested", created_by: "Dedi K.", detail: [det(MAT[0].kode_barang, 2, "")] },
        { spk_no: "SPK-Sub-004", jenis_subkont: JENIS[0], tgl_spk: "2026-11-05", proses_disubkont: "Proses Slitting Material", catatan: "", status: "Dikirim Sebagian", created_by: "Dedi K.", detail: [det(MAT[1].kode_barang, 2, "")] },
        { spk_no: "SPK-Sub-005", jenis_subkont: JENIS[1], tgl_spk: "2026-11-03", proses_disubkont: "Proses Tambah Pasir", catatan: "Dibatalkan, order batal", status: "Cancelled", created_by: "Dedi K.", detail: [det("PRD-0105", 100, "")] },
      ],
      tamp: [
        tp("SPK-Sub-003", MAT[0].kode_barang, "PCK-2609-001", 1, 3798, "Siap Kirim", "2026-11-11", "Budi S."),
        tp("SPK-Sub-004", MAT[1].kode_barang, "PCK-2609-011", 1, 6120, "Terkirim", "2026-11-06", "Budi S.", "DV-SUB-001"),
        tp("SPK-Sub-004", MAT[1].kode_barang, "PCK-2609-012", 1, 5980, "Terkunci di Surat Jalan", "2026-11-07", "Budi S.", "DV-SUB-002"),
        tp("SPK-Sub-002", "PRD-0102", "", 25, 865, "Siap Kirim", "2026-11-11", "Budi S."),
      ],
      dlv: [
        { kode_delivery: "DV-SUB-001", no_surat_jalan: "SJ/06/11/2026/Sub", tgl_kirim: "2026-11-06", id_vendor: 23, nopol_sopir: "B 1234 XYZ / Budi", catatan: "", status: "Confirmed", created_by: "Dedi K.", confirmed_by: "Dedi K.", confirmed_at: "2026-11-06 14:10",
          detail: [{ id_tamp: 2, spk_no: "SPK-Sub-004", pack_code: "PCK-2609-011", kode_barang: MAT[1].kode_barang, qty: 1, net_weight: 6120, costbook: 14850, nilai: 90882000, sumber: "Scan QR", keterangan: "" }] },
        { kode_delivery: "DV-SUB-002", no_surat_jalan: "SJ/07/11/2026/Sub", tgl_kirim: "2026-11-07", id_vendor: 23, nopol_sopir: "B 1234 XYZ / Budi", catatan: "Pengiriman kedua", status: "Draft", created_by: "Dedi K.", confirmed_by: "", confirmed_at: "",
          detail: [{ id_tamp: 3, spk_no: "SPK-Sub-004", pack_code: "PCK-2609-012", kode_barang: MAT[1].kode_barang, qty: 1, net_weight: 5980, costbook: null, nilai: null, sumber: "Tampungan", keterangan: "" }] },
      ],
      ledger: [{ id: 1, kode_delivery: "DV-SUB-001", spk_no: "SPK-Sub-004", id_vendor: 23, tgl_transaksi: "2026-11-06", jenis_mutasi: "Kirim", qty: 1, net_weight: 6120, nilai: 90882000, saldo_nilai: 90882000, no_referensi: "SJ/06/11/2026/Sub" }],
      // Portal Vendor (ms_vendor_portal_account, tr_laporan_subkont, tr_laporan_subkont_revisi); kata sandi disimpan polos hanya di mockup
      sup: SUPPLIERS.map((x) => Object.assign({}, x)),
      // link portal vendor (ms_vendor_portal_link): HFG membuat link unik per supplier dari Master Supplier; vendor membuka link tanpa pendaftaran/login/password
      links: [{ id: 1, id_vendor: 23, token_link: "kps7f3a91c4e2b84d0a9c61d5e8b3a7f204", status_link: "Aktif", created_by: "Dewi (Purchasing)", created_at: "2026-10-21", nonaktif_by: "", nonaktif_at: "", last_access_at: "" }],
      lap: [
        { no_laporan: "LHP-Sub-001", kode_delivery: "DV-SUB-001", spk_no: "SPK-Sub-004", kode_barang: MAT[1].kode_barang, id_vendor: 23, no_surat_jalan_vendor: "KPS-DO/2611/041", no_po: "PO-Sub-2611-001", tgl_laporan: "2026-11-08", nett_weight_actual: 6098, width_after: 305, total_scrap: 14, status: "Closed", reopened: false, alasan_selisih: "",
          packs: [{ pack_code: "PCK-2609-011", kode_internal: "DEBDA0322-T1", nett_weight_before: 6120, jumlah_coil_roll: 10 }], fg: null },
      ],
      rev: [],
      inc: [], stokPRT: [], fgBaru: [], scrapStok: [],
      seq: { spk: 5, tamp: 10, dlv: 2, led: 1, lap: 1, link: 1, rev: 0, sup: 51, inc: 0 },
    };
    st.tamp.forEach((t, i) => (t.id = i + 1));
    return st;
  }
  let state, storageOk = true;
  // pemulihan data: laporan yang kode_barang-nya hilang (bug Edit di Portal, diperbaiki 2026-11-20) dilengkapi lagi dari Surat Jalan / LHP-nya
  // migrasi: portal tidak lagi memakai akun/pendaftaran (keputusan user 2026-11-20); akun lama dibuang dan link contoh disediakan bila belum ada
  const repairLinks = (s) => { if (!s.links) s.links = [{ id: 1, id_vendor: 23, token_link: "kps7f3a91c4e2b84d0a9c61d5e8b3a7f204", status_link: "Aktif", created_by: "Dewi (Purchasing)", created_at: "2026-10-21", nonaktif_by: "", nonaktif_at: "", last_access_at: "" }]; s.seq = s.seq || {}; if (!s.seq.link) s.seq.link = s.links.reduce((a, l) => Math.max(a, l.id), 0); delete s.acct; delete s.session; delete s.seq.acct; return s; };
  const repair = (s) => { (s.lap || []).forEach((l) => { if (l.kode_barang) return; const d = (s.dlv || []).find((x) => x.kode_delivery === l.kode_delivery); if (!d) return;
    const hit = (l.packs && l.packs.length) ? d.detail.find((x) => l.packs.some((p) => p.pack_code === x.pack_code)) : d.detail.find((x) => l.fg && l.fg.some((g) => g.kode_barang_asal === x.kode_barang));
    if (hit) { l.kode_barang = hit.kode_barang; if (!l.spk_no) l.spk_no = hit.spk_no; } }); return s; };
  // hasil Konfirmasi Incoming yang dibuat sebelum ada nama/tanggal/SPK dilengkapi dari laporan dan Incoming-nya (dipakai halaman Stock From Warehouse dan FG Stock)
  const repair2 = (s) => { const incDate = (no) => ((s.inc || []).find((i) => i.no_incoming === no) || {}).tgl_incoming || "";
    [s.stokPRT, s.scrapStok].forEach((arr) => (arr || []).forEach((x) => { const l = (s.lap || []).find((z) => z.no_laporan === x.no_laporan) || {}; if (!x.kode_barang) x.kode_barang = l.kode_barang; if (!x.nama_barang) { const m0 = MAT.find((z) => z.kode_barang === x.kode_barang) || FG.find((z) => z.kode_barang === x.kode_barang); x.nama_barang = m0 ? m0.nama_barang : x.kode_barang; } if (!x.spk_no) x.spk_no = l.spk_no; if (!x.tgl) x.tgl = incDate(x.no_incoming); }));
    (s.fgBaru || []).forEach((x) => { const l = (s.lap || []).find((z) => z.no_laporan === x.no_laporan) || {}; if (!x.spk_no) x.spk_no = l.spk_no; if (!x.tgl) x.tgl = incDate(x.no_incoming); if (x.berat_kg == null) { const g = (l.fg || []).find((z) => z.produk_fg_baru === x.kode_fg_baru); x.berat_kg = g ? g.total_berat_kg : 0; } });
    return s; };
  const load = () => { try { const s = JSON.parse(localStorage.getItem(KEY)); if (s && s.spk && s.tamp) return repairLinks(repair2(repair(s))); } catch (e) { storageOk = false; } return null; };
  let lastRaw = null;
  const save = () => { try { lastRaw = JSON.stringify(state); localStorage.setItem(KEY, lastRaw); } catch (e) { storageOk = false; } };
  state = load() || initial(); save();
  // sinkronisasi antar tab/halaman (HFG dan Portal dibuka bersamaan): ambil data terbaru dari penyimpanan bila berubah di tempat lain
  const sync = () => { try { const raw = localStorage.getItem(KEY); if (!raw || raw === lastRaw) return false; const s = load(); if (!s) return false; state = s; lastRaw = raw; return true; } catch (e) { return false; } };
  const resync = () => { if (sync() && window.WHUI && window.WHUI.render) window.WHUI.render(); };
  window.addEventListener("storage", (e) => { if (e.key === KEY) resync(); });
  window.addEventListener("focus", resync);
  document.addEventListener("visibilitychange", () => { if (!document.hidden) resync(); });

  /* ---------- turunan ---------- */
  const matBy = (kode) => MAT.find((m) => m.kode_barang === kode) || FG.find((f) => f.kode_barang === kode) || null;
  const nama = (kode) => (matBy(kode) || {}).nama_barang || kode;
  const spkOf = (no) => state.spk.find((s) => s.spk_no === no) || null;
  const tampOf = (no) => state.tamp.filter((t) => t.spk_no === no);
  const inTamp = (pack) => state.tamp.some((t) => t.pack_code === pack);
  // stok saat ini: Material = pack PRO yang belum di Tampungan manapun; FG = qty_stok - qty di Tampungan (Siap Kirim / Terkunci) semua SPK
  const packsAvail = (kode) => PACKS.filter((p) => p.kode_barang === kode && p.kd_gudang === "PRO" && !inTamp(p.pack_code));
  // fg_stock berkurang saat Confirm Surat Jalan (Terkirim), dan barang Siap Kirim / Terkunci sudah dipisahkan di Tampungan
  const fgInTamp = (kode) => state.tamp.filter((t) => t.kode_barang === kode).reduce((a, t) => a + t.qty, 0);
  const stok = (jenis, kode) => (jenis === JENIS[0] ? packsAvail(kode).length : Math.max(0, (FG.find((f) => f.kode_barang === kode) || { qty_stok: 0 }).qty_stok - fgInTamp(kode)));
  // progres per baris detail
  const prog = (s, d) => {
    const rows = tampOf(s.spk_no).filter((t) => t.kode_barang === d.kode_barang);
    const siap = rows.filter((t) => t.status !== "Terkirim").reduce((a, t) => a + t.qty, 0), kirim = rows.filter((t) => t.status === "Terkirim").reduce((a, t) => a + t.qty, 0);
    return { qty_subkont: d.qty_subkont, qty_siap_kirim: siap, qty_terkirim: kirim, sisa: Math.max(0, d.qty_subkont - siap - kirim) };
  };
  const total = (s) => s.detail.reduce((a, d) => { const p = prog(s, d); a.qty_subkont += p.qty_subkont; a.qty_siap_kirim += p.qty_siap_kirim; a.qty_terkirim += p.qty_terkirim; a.sisa += p.sisa; return a; }, { qty_subkont: 0, qty_siap_kirim: 0, qty_terkirim: 0, sisa: 0 });
  const canCancel = (s) => s.status === "Draft" || (s.status === "Requested" && tampOf(s.spk_no).length === 0);

  /* ---------- Costbook Material dari Stock Value (keputusan user 2026-11-20) ----------
     Costbook dasar = Purchase Price (Avg) material di Stock Value (halaman wh-*; cuplikan harga dasar di localStorage "mockWH.price" = {q, s, h} per material
     TANPA transaksi Subkont). Di atasnya diterapkan transaksi Subkont sendiri (rata-rata bergerak): Surat Jalan Confirmed = OUT pada harga berjalan (harga tidak
     berubah), Incoming Confirmed = IN baby coil pada costbook baru -> harga rata-rata dihitung ulang. Tanpa cuplikan (halaman Stock Value belum pernah dibuka)
     dipakai nilai contoh COSTBOOK. */
  const WHMAP = { "SBJ-ZGAGBA232-01-004": "MTR-002", "SBJ-ZGAGBA232-01-006": "MTR-001", "SBJ-GVBBA118-02-001": "MTR-004" };   // material Subkont -> material Stock Value (nama sama)
  // jejak perhitungan costbook Material (dipakai Surat Jalan dan layar "Validasi Costbook"): kondisi awal dari Stock Value + transaksi Subkont berurutan
  const costTrail = (kode) => {
    const mid = WHMAP[kode]; let sn = null; try { sn = JSON.parse(localStorage.getItem("mockWH.price")); } catch (e) { sn = null; }
    if (!sn || !sn[mid]) return null;
    let q = sn[mid].q, v = sn[mid].s; const tr = [], steps = [];
    state.dlv.filter((d) => d.status === "Confirmed").forEach((d) => { const rows = d.detail.filter((x) => x.pack_code && WHMAP[x.kode_barang] === mid); if (rows.length) tr.push({ tgl: d.tgl_kirim, no: d.kode_delivery, st: "OUT", q: rows.reduce((a, x) => a + (x.net_weight || 0), 0), p: 0 }); });
    state.inc.filter((i) => i.status === "Confirmed").forEach((i) => i.items.forEach((it) => { const l = state.lap.find((z) => z.no_laporan === it.no_laporan) || {}; if (WHMAP[l.kode_barang] === mid && it.costbook_baru_per_kg) tr.push({ tgl: i.tgl_incoming, no: i.no_incoming, st: "IN", q: l.nett_weight_actual || 0, p: it.costbook_baru_per_kg, kode: l.kode_barang }); }));
    tr.map((t, i) => ({ t, i })).sort((a, b) => a.t.tgl.localeCompare(b.t.tgl) || a.i - b.i).forEach(({ t }) => {
      let nilai; if (t.st === "IN") { nilai = Math.round(t.q * t.p * 100) / 100; q += t.q; v += nilai; } else { const h = q ? v / q : 0; nilai = Math.round(t.q * h * 100) / 100; v -= nilai; q -= t.q; }
      steps.push({ tgl: t.tgl, no: t.no, st: t.st, q: t.q, p: t.st === "IN" ? t.p : (q + t.q ? (v + nilai) / (q + t.q) : 0), nilai, q_akhir: Math.round(q * 1000) / 1000, s_akhir: Math.round(v * 100) / 100, h_akhir: q > 0 ? Math.round((v / q) * 100) / 100 : sn[mid].h });
    });
    return { mid, awal: { q: sn[mid].q, s: sn[mid].s, h: sn[mid].h }, steps, harga: steps.length ? steps[steps.length - 1].h_akhir : sn[mid].h };
  };
  const matCost = (kode) => { const t = costTrail(kode); return t ? t.harga : COSTBOOK[kode] || 0; };
  window.WH = {
    get state() { return state; }, get VENDORS() { return state.sup; }, nextSup() { return ++state.seq.sup; }, save, storageOk: () => storageOk, reset() { state = initial(); save(); },
    MAT, FG, PACKS, JENIS, COSTBOOK, TUJUAN, SATUAN, today, round, deep, nama, matBy, spkOf, tampOf, packsAvail, stok, prog, total, canCancel, inTamp,
    nextSpk() { return "SPK-Sub-" + String(++state.seq.spk).padStart(3, "0"); },
    nextTampId() { return ++state.seq.tamp; },
    nextDlv() { return "DV-SUB-" + String(++state.seq.dlv).padStart(3, "0"); }, nextLed() { return ++state.seq.led; },
    vendorBy: (id) => state.sup.find((v) => String(v.id) === String(id)) || null,
    costbook: (kode) => (WHMAP[kode] ? matCost(kode) : COSTBOOK[kode] || 0), costTrail,
    // Portal Vendor
    FG_BASE: { "PRD-0101": "DEBDA0319-T1", "PRD-0102": "DEBDA0319-T2", "PRD-0105": "DEBDA0322-T1" },
    vendorPortal: (id) => { const ls = state.links.filter((x) => String(x.id_vendor) === String(id)); return ls.some((x) => x.status_link === "Aktif") ? "Link Aktif" : ls.length ? "Link Nonaktif" : "Belum ada link"; },
    linkOf: (id) => state.links.find((x) => String(x.id_vendor) === String(id) && x.status_link === "Aktif") || null,
    newToken: () => { const u = new Uint8Array(16); (window.crypto || window.msCrypto).getRandomValues(u); return "v" + Array.from(u).map((x) => x.toString(16).padStart(2, "0")).join(""); },
    nextLink() { return ++state.seq.link; },
    nextInc() { return "INC-Sub-" + String(++state.seq.inc).padStart(3, "0"); },
    // baby coil terbentuk dari LHP: {Kode Internal}-BC.{n}, n berurut per Kode Internal; Net Weight per Roll = Nett Weight Actual / total roll (dibagi rata)
    babies(l) { const tot = l.packs.reduce((a, p) => a + (p.jumlah_coil_roll || 0), 0), per = tot ? l.nett_weight_actual / tot : 0, cnt = {}, out = [];
      l.packs.forEach((p) => { for (let i = 0; i < (p.jumlah_coil_roll || 0); i++) { cnt[p.kode_internal] = (cnt[p.kode_internal] || 0) + 1; out.push({ babycoil_code: p.kode_internal + "-BC." + cnt[p.kode_internal], pack_code: p.pack_code, net_weight: Math.round(per * 100) / 100 }); } } ); return out; },
    incOf: (no) => state.inc.find((x) => x.no_incoming === no) || null,
    incOfLap: (no_laporan) => state.inc.filter((x) => x.no_laporan === no_laporan),
    nextLap() { return "LHP-Sub-" + String(++state.seq.lap).padStart(3, "0"); }, nextRev() { return ++state.seq.rev; },
    // satu Surat Jalan boleh berisi beberapa SPK (keputusan user 2026-11-20): spk_no ada di tiap baris detail; Laporan Subkont per (Surat Jalan, SPK, material)
    lapOf: (kode_delivery, spk_no, kode_barang) => state.lap.find((l) => l.kode_delivery === kode_delivery && l.spk_no === spk_no && l.kode_barang === kode_barang) || null,
    dlvSpks: (d) => [...new Set(d.detail.map((x) => x.spk_no))],
    dlvItems: (l) => { const d = state.dlv.find((x) => x.kode_delivery === l.kode_delivery); return d ? d.detail.filter((x) => x.spk_no === l.spk_no && x.kode_barang === l.kode_barang) : []; },
    dlvTotals: (d) => d.detail.reduce((a, x) => { const sp = state.spk.find((z) => z.spk_no === x.spk_no); if (sp && sp.jenis_subkont === JENIS[0]) { a.nw += x.net_weight || 0; a.pack += x.qty; } else a.pcs += x.qty; return a; }, { nw: 0, pack: 0, pcs: 0 }),
    dlvOf: (kode) => state.dlv.find((d) => d.kode_delivery === kode) || null,
    packBy: (code) => PACKS.find((p) => p.pack_code.toUpperCase() === String(code || "").trim().toUpperCase()) || null,
  };
})();
