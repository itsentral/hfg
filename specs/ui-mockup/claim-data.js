/* ============================================================
   claim-data.js — Data contoh + aturan hitung/jurnal Modul Claim Material (FEAT-11, FR-75..FR-94).
   Sumber: input/Claim Process/ (BPMN D003 + workbook Rev.2) dan jawaban user 2026-10-27.
   Mockup saja: disimpan di localStorage "mockClaim.data"; nama tabel/kolom mengikuti ERD (grup Claim Material).
   ============================================================ */
(() => {
  "use strict";
  const KEY = "mockClaim.data";
  const PPN = 1.11;                                 // FR-92 (tidak dipakai untuk Retur sejak 2026-10-29; dipertahankan untuk acuan)
  const round2 = (n) => Math.round((Number(n) + Number.EPSILON) * 100) / 100;
  const today = () => new Date().toISOString().slice(0, 10);

  const SUPPLIERS = {
    "PT Baja Semesta Mandiri": { alamat: "Jl. Industri Raya No. 12, Cikarang, Bekasi", cur: "IDR" },
    "PT Zibo Internasional": { alamat: "Zibo Industrial Park, Shandong, China", cur: "USD" },
    "YINGLI INTERNATIONAL": { alamat: "Yingli Steel Zone, Tianjin, China", cur: "USD" },
    "PT Baja Lokal Sejahtera": { alamat: "Jl. Kawasan Industri Jababeka II, Bekasi", cur: "IDR" },
  };

  const seed = () => ({
    seq: { claim: 3, spk: 0, sj: 0, dn: 3, bayar: 0, dec: 1 },
    // Baris claim dari Laporan Produksi (FR-75). asal: "KW 2 Supplier" | "Hold Coil" | "Reject Supplier".
    claims: [
      { id: "CLM-2610-001", supplier: "PT Baja Semesta Mandiri", material: "Galvalum 025", coil: "BSM-C001", po: "PO-001", kg: 681, hargaKg: 18800, costbookKg: 18200, priceUnitPo: 19000,
        problem: "KW 2", asal: "KW 2 Supplier", tglProduksi: "2026-10-19", spk: "SPK-2610-007", alasan: "KW 2 akibat white rust pada sisi luar coil setelah unpack; 681 Kg tidak lolos QC.",
        bukti: [{ nama: "foto_coil_BSM-C001_whiterust.jpg", ukuran: 482304 }, { nama: "berita_acara_QC_SPK-2610-007.pdf", ukuran: 215040 }],
        status: "Email Terkirim", emailDate: "2026-10-20", kesepakatan: "", decisions: [] },
      { id: "CLM-2610-002", supplier: "PT Baja Semesta Mandiri", material: "Galvalum 025", coil: "BSM-C002", po: "PO-001", kg: 3125, hargaKg: 18800, costbookKg: 18200, priceUnitPo: 19000,
        problem: "Karat", asal: "Hold Coil", tglProduksi: "2026-10-19", spk: "SPK-2610-007", alasan: "Karat merata di permukaan coil; operator menahan coil (Hold) sebelum dipasang di mesin.",
        bukti: [{ nama: "foto_karat_BSM-C002.jpg", ukuran: 733184 }],
        status: "Email Terkirim", emailDate: "2026-10-20", kesepakatan: "", decisions: [] },
      { id: "CLM-2610-003", supplier: "PT Baja Lokal Sejahtera", material: "Galvalum 020", coil: "BL-C114", po: "PO-007", kg: 1200, hargaKg: 17500, costbookKg: 17000, priceUnitPo: 16800,
        problem: "Reject Material", asal: "Reject Supplier", tglProduksi: "2026-10-21", spk: "SPK-2610-011", alasan: "Tepi coil bergelombang (edge wave) sehingga plat ditolak QC.",
        bukti: [{ nama: "foto_edge_wave_BL-C114.jpg", ukuran: 598016 }],
        status: "Baru", emailDate: "", kesepakatan: "", decisions: [] },
      { id: "CLM-2610-000", supplier: "PT Baja Lokal Sejahtera", material: "Galvalum 014", coil: "BL-C021", po: "PO-005", kg: 800, hargaKg: 16000, costbookKg: 15500, priceUnitPo: 15200,
        problem: "Selisih Timbang", asal: "Hold Coil", tglProduksi: "2026-10-12", spk: "SPK-2610-003", alasan: "Berat aktual lebih ringan 800 Kg dari packing list.",
        bukti: [{ nama: "foto_timbangan_BL-C021.jpg", ukuran: 401408 }],
        status: "Kompensasi", emailDate: "2026-10-13", kesepakatan: "Supplier setuju potongan nilai.",
        decisions: [{ id: "D-0001", jalur: "Kompensasi", aksi: "Debit Note", kg: 800, nilai: 9600000, ket: "" }] },
    ],
    spk: [],       // SPK Re-Pack (FR-83): { id, no, tgl, supplier, decs:[...], items:[{claim, dec, ...}], status: WAITING|ON PROGRESS|COMPLETED } — satu SPK memuat beberapa baris satu supplier (G6)
    sj: [],        // Surat Jalan Retur (FR-84): { id, spk, no, tgl, supplier, alamat, driver, nopol, status: WAITING SJ|NOT CONFIRMED|SJ CONFIRMED, nilai }
    jual: [],      // Inventory Material Khusus Dijual — Gudang Slitting (FR-81)
    dn: [
      { no: "DN-2610-001", tgl: "2026-10-14", supplier: "PT Baja Lokal Sejahtera", cur: "IDR", kurs: 1, nilai: 9600000, jalur: "Kompensasi", asal: "Hold Coil",
        status: "Sudah Terbit", claim: "CLM-2610-000", dec: "D-0001", sj: "", tagihan: null, terima: null, alokasi: "" },
      { no: "DN-2610-002", tgl: "2026-10-09", supplier: "PT Zibo Internasional", cur: "USD", kurs: 16300, nilai: 2637.4, jalur: "Kompensasi", asal: "KW 2 Supplier",
        status: "Belum Terima Uang", claim: "CLM-2609-014", dec: "", sj: "", tagihan: { tgl: "2026-10-10", due: "2026-10-30", note: "" }, terima: null, alokasi: "" },
      { no: "DN-2610-003", tgl: "2026-10-05", supplier: "PT Baja Lokal Sejahtera", cur: "IDR", kurs: 1, nilai: 25500000, jalur: "Kompensasi", asal: "Reject Supplier",
        status: "Sudah Terima Uang", claim: "CLM-2609-009", dec: "", sj: "", tagihan: { tgl: "2026-10-06", due: "2026-10-20", note: "" },
        terima: { tgl: "2026-10-16", rek: "BCA - 001 302 5567 (Rek. Operasional)", nominal: 25500000, note: "" }, alokasi: "" },
    ],
    bayar: [],     // Pembayaran PO dengan Debit Note (FR-88)
  });

  // Asal coil -> supplier, No. PO, No. Coil Packing List. DATA MOCKUP: aslinya ditarik dari database Incoming / Packing List
  // (baby coil -> penerimaan material -> PO); dipakai popup Hold & Claim di Laporan Produksi (FR-75).
  const ORIGIN = {
    "BC-U-1001": { supplier: "PT Baja Semesta Mandiri", po: "PO-001", packing: "BSM-C001", hargaBeli: 18800, priceUnitPo: 19000 },
    "BC-U-1002": { supplier: "PT Baja Semesta Mandiri", po: "PO-001", packing: "BSM-C002", hargaBeli: 18800, priceUnitPo: 19000 },
    "BC-U-1003": { supplier: "PT Baja Lokal Sejahtera", po: "PO-005", packing: "BL-C021", hargaBeli: 16000, priceUnitPo: 15200 },
    "BC-W-2005": { supplier: "PT Baja Semesta Mandiri", po: "PO-001", packing: "BSM-C003", hargaBeli: 18800, priceUnitPo: 19000 },
    "BC-W-2006": { supplier: "PT Baja Lokal Sejahtera", po: "PO-007", packing: "BL-C114", hargaBeli: 17500, priceUnitPo: 16800 },
    "BC-H-3009": { supplier: "PT Baja Lokal Sejahtera", po: "PO-005", packing: "BL-C022", hargaBeli: 16000, priceUnitPo: 15200 },
  };

  let state = null;
  let stored = true;
  function load() {
    try { const raw = localStorage.getItem(KEY); if (raw) return JSON.parse(raw); } catch (e) { stored = false; }
    return seed();
  }
  const save = () => { try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) { stored = false; } };
  /* ---------- penyimpanan berkas bukti (mockup: IndexedDB browser; jatuh ke memori sesi bila tidak tersedia) ---------- */
  const FILES = (() => {
    const mem = new Map(); let dbp = null;
    const open = () => dbp || (dbp = new Promise((res) => { try { const rq = indexedDB.open("mockClaimFiles", 1); rq.onupgradeneeded = () => rq.result.createObjectStore("f"); rq.onsuccess = () => res(rq.result); rq.onerror = () => res(null); setTimeout(() => res(null), 4000); } catch (e) { res(null); } }));
    const tx = (mode, fn) => open().then((db) => (db ? new Promise((res) => { try { const t = db.transaction("f", mode), r = fn(t.objectStore("f")); t.oncomplete = () => res(r && r.result !== undefined ? r.result : true); t.onerror = () => res(null); } catch (e) { res(null); } }) : null));
    return {
      async put(file) { const id = "F-" + Date.now().toString(36) + "-" + Math.random().toString(36).slice(2, 7); mem.set(id, file); await tx("readwrite", (s) => s.put(file, id)); return id; },
      async get(id) { if (mem.has(id)) return mem.get(id); const b = await tx("readonly", (s) => s.get(id)); return b instanceof Blob ? b : null; },
      async clear() { mem.clear(); await tx("readwrite", (s) => s.clear()); },
    };
  })();
  const reset = () => { state = seed(); save(); FILES.clear(); };
  state = load();

  const pad = (n, w = 3) => String(n).padStart(w, "0");
  const next = (k, fmt) => { state.seq[k] += 1; return fmt(state.seq[k]); };
  const ym = () => today().slice(2, 4) + today().slice(5, 7);

  /* ---------- hitung ---------- */
  const nilaiMaterial = (c, kg) => round2((kg == null ? c.kg : kg) * c.hargaKg);          // nilai material = Kg x Harga Beli/Kg (warehouse_stock_coil.harga_beli; field hargaKg)
  const nilaiDnRetur = (c, kg) => round2((kg == null ? c.kg : kg) * (c.priceUnitPo != null ? c.priceUnitPo : (c.costbookKg != null ? c.costbookKg : c.hargaKg)));   // Debit Note Retur = Kg x Price/Unit PO (price_per_unit_po; keputusan user 2026-10-29)
  const nilaiRetur = (c, kg) => round2((kg == null ? c.kg : kg) * (c.costbookKg != null ? c.costbookKg : c.hargaKg));                                          // Retur = Kg x Costbook/Kg gudang asal (costbook_per_kg); tidak dibagi 1,11 (keputusan user 2026-10-29)
  const dnIDR = (d) => round2(d.nilai * (d.kurs || 1));
  const claim = (id) => state.claims.find((c) => c.id === id);
  const jalurOf = (c) => {
    const j = [...new Set(c.decisions.map((d) => d.jalur))];
    return j.length ? j.join(" + ") : "";
  };
  // Status material pada tab Material On Hold (FR-75, FR-80, FR-81, FR-82)
  function holdStatus(c) {
    if (!c.decisions.length) return "Hold";
    const a = c.decisions.map((d) => d.aksi);
    const out = [];
    if (a.includes("Lanjut Produksi")) out.push("Dapat Dipakai (Release)");
    if (a.includes("Jual Material")) out.push("Dijual (Gudang Slitting)");
    if (a.includes("Retur ke Supplier")) out.push("Dalam Retur");
    if (a.includes("Debit Note") && !out.length) out.push("Dikompensasi");
    return out.join(", ");
  }

  /* ---------- jurnal (FR-81, FR-84, FR-86, FR-88, FR-90) ---------- */
  const J = {
    // Debit Note Kompensasi terbit: kredit mengurangi biaya KW 2 (asal KW 2 Supplier) atau Biaya Scrap (asal Hold/Reject) — OQ-C3.
    dnKompensasi: (d) => ({ title: "Jurnal saat Debit Note Kompensasi terbit", lines: [
      ["Debit Note (Kompensasi)", dnIDR(d), 0],
      [d.asal === "KW 2 Supplier" ? "Biaya KW 2 Supplier" : "Biaya Scrap", 0, dnIDR(d)],
    ] }),
    sjCreate: (v) => ({ title: "Jurnal saat Surat Jalan Retur dibuat", lines: [["In Transit", v, 0], ["Inventory", 0, v]] }),
    // Confirm SJ: Db Debit Note (Price/Unit PO) / Cr Nilai Material Retur (costbook, In Transit) / Db atau Cr Selisih Costbook vs Price/Unit PO (keputusan user 2026-10-29)
    sjConfirm: (v, dn) => { const d = dn == null ? v : dn, sel = round2(d - v);
      return { title: "Jurnal saat Surat Jalan dikonfirmasi diterima Supplier", lines: [["Debit Note (Retur)", d, 0], ["Nilai Material Retur (In Transit)", 0, v]].concat(sel > 0 ? [["Selisih Costbook vs Price/Unit PO", 0, sel]] : sel < 0 ? [["Selisih Costbook vs Price/Unit PO", -sel, 0]] : []) }; },
    jualMaterial: (nilai, kw) => ({ title: "Jurnal Jual Material", lines: [
      ["Material KW (Inventory)", kw, 0], ["Biaya Scrap", round2(nilai - kw), 0], ["Gudang Bahan Persediaan Produksi", 0, nilai],
    ] }),
    terima: (v) => ({ title: "Jurnal Penerimaan Uang dari Supplier", lines: [["Bank", v, 0], ["Debit Note", 0, v]] }),
    bayar: (b) => ({ title: "Jurnal Pembayaran PO dengan Debit Note", lines: [
      ["Hutang", b.nilai + b.ppn, 0], ["Bank", 0, b.totalBayar], ["Bank Admin", 0, b.admin], ["Debit Note (Kompensasi)", 0, b.dnKomp],
      ["Debit Note (Retur)", 0, b.dnRetur], ["Admin", b.admin, 0],
    ].filter((l) => l[1] || l[2]) }),
  };

  /* ---------- aksi bisnis (mengubah state) ---------- */
  const A = {
    kirimEmail(id) { const c = claim(id); c.status = "Email Terkirim"; c.emailDate = today(); save(); },
    // decisions: [{ jalur, aksi, kg, nilai?, ket?, materialKw? }]
    // kurs: diinput saat claim diproses (Process Decision) untuk supplier non-IDR; Create Debit Note hanya menarik kurs ini (OQ-C12, user 2026-10-29).
    putuskan(id, kesepakatan, decisions, kurs) {
      const c = claim(id);
      c.kesepakatan = kesepakatan;
      const kr = SUPPLIERS[c.supplier].cur === "IDR" ? 1 : (Number(kurs) > 0 ? Number(kurs) : 1);
      c.decisions = decisions.map((d) => Object.assign({}, d, { id: next("dec", (n) => "D-" + pad(n, 4)) }));   // urut, bukan dari jam (ID tidak boleh bentrok)
      c.status = jalurOf(c);
      c.decisions.forEach((d) => {
        if (d.aksi === "Debit Note") {
          state.dn.push({ no: next("dn", (n) => "DN-" + ym() + "-" + pad(n)), tgl: "", supplier: c.supplier, cur: SUPPLIERS[c.supplier].cur, kurs: kr, nilai: d.nilai,
            jalur: "Kompensasi", asal: c.asal, status: "Belum Terbit", claim: c.id, dec: d.id, sj: "", tagihan: null, terima: null, alokasi: "" });
        } else if (d.aksi === "Jual Material") {
          state.jual.push({ claim: c.id, dec: d.id, tgl: today(), material: c.material, coil: c.coil, kg: d.kg, nilai: nilaiMaterial(c, d.kg), materialKw: d.materialKw });
        } else if (d.aksi === "Retur ke Supplier") {
          state.spk.push({ id: "SP-" + d.id, no: "", tgl: "", supplier: c.supplier, decs: [d.id], status: "WAITING",
            items: [{ claim: c.id, dec: d.id, material: c.material, coil: c.coil, po: c.po, kg: d.kg, problem: c.problem }] });
          state.dn.push({ no: next("dn", (n) => "DN-" + ym() + "-" + pad(n)), tgl: "", supplier: c.supplier, cur: SUPPLIERS[c.supplier].cur, kurs: kr, nilai: nilaiDnRetur(c, d.kg),
            jalur: "Retur", asal: c.asal, status: "Belum Terbit", claim: c.id, dec: d.id, sj: "", tagihan: null, terima: null, alokasi: "" });
        }
      });
      save();
    },
    // Baris claim dari tombol Hold & Claim di Laporan Produksi (FR-75). rows: [{ asal, supplier, material, coil (No. Coil Packing List),
    // po, kg, hargaKg, costbookKg, priceUnitPo, problem, alasan, babyCoil, gudang }]. Baris yang sama (SPK + coil + asal + problem) tidak dibuat dua kali.
    terimaDariProduksi(meta, rows) {
      const made = [], skipped = [];
      rows.forEach((r) => {
        const dup = state.claims.find((c) => c.spk === meta.spk && c.coil === r.coil && c.asal === r.asal && c.problem === r.problem);
        if (dup) { skipped.push(dup.id); return; }
        const c = { id: next("claim", (n) => "CLM-" + ym() + "-" + pad(n)), supplier: r.supplier, material: r.material, coil: r.coil, po: r.po, kg: r.kg,
          hargaKg: r.hargaKg, costbookKg: r.costbookKg, priceUnitPo: r.priceUnitPo, problem: r.problem, asal: r.asal, tglProduksi: meta.tgl, spk: meta.spk, alasan: r.alasan, babyCoil: r.babyCoil, gudangAsal: r.gudang,
          bukti: (r.bukti || []).map((b) => Object.assign({}, b)), status: "Baru", emailDate: "", kesepakatan: "", decisions: [] };
        state.claims.push(c); made.push(c.id);
      });
      save();
      return { made, skipped };
    },
    // ---- Laporan Produksi + claim sekaligus (FR-98) ----
    // Claim yang lahir dari Laporan Produksi punya babyCoil dan srcKey (kunci baris sumber di form). Aturan edit (usulan FR-98 butir f):
    // status Baru = ikut tersinkron; status lain = terkunci (hanya bukti yang boleh ditambah).
    cari(spkNo, r) { return state.claims.find((c) => c.spk === spkNo && c.babyCoil && (c.srcKey ? c.srcKey === r.key : (c.babyCoil === r.babyCoil && c.asal === r.asal && c.problem === r.problem))) || null; },
    cekKunci(meta, items) {
      const err = [];
      state.claims.filter((c) => c.spk === meta.spk && c.babyCoil && c.status !== "Baru").forEach((c) => {
        const r = items.find((x) => (c.srcKey ? c.srcKey === x.key : (c.babyCoil === x.babyCoil && c.asal === x.asal && c.problem === x.problem)));
        if (!r) err.push("Claim " + c.id + " (" + c.asal + ", " + c.babyCoil + ") berstatus " + c.status + ": baris sumbernya tidak boleh dihapus di laporan produksi; koreksi lewat Purchasing di Claim Material.");
        else if (round2(r.kg) !== round2(c.kg) || r.babyCoil !== c.babyCoil) err.push("Claim " + c.id + " (" + c.asal + ", " + c.babyCoil + ") berstatus " + c.status + ": berat/coil terkunci pada " + c.kg + " Kg; koreksi lewat Purchasing di Claim Material.");
      });
      return err;
    },
    akanDibatalkan(meta, items) {
      return state.claims.filter((c) => c.spk === meta.spk && c.babyCoil && c.status === "Baru" && !items.some((x) => (c.srcKey ? c.srcKey === x.key : (c.babyCoil === x.babyCoil && c.asal === x.asal && c.problem === x.problem))));
    },
    sinkronDariProduksi(meta, items) {
      const made = [], updated = [], kept = [], removed = [];
      const sig = (c) => JSON.stringify([c.kg, c.problem, c.alasan, c.supplier, c.po, c.coil, (c.bukti || []).map((b) => b.fid || b.nama)]);
      items.forEach((r) => {
        const c = A.cari(meta.spk, r);
        if (!c) {
          const n = { id: next("claim", (k) => "CLM-" + ym() + "-" + pad(k)), supplier: r.supplier, material: r.material, coil: r.coil, po: r.po, kg: r.kg,
            hargaKg: r.hargaKg, costbookKg: r.costbookKg, priceUnitPo: r.priceUnitPo, problem: r.problem, asal: r.asal, tglProduksi: meta.tgl, spk: meta.spk, alasan: r.alasan,
            babyCoil: r.babyCoil, gudangAsal: r.gudang, srcKey: r.key, bukti: (r.bukti || []).map((b) => Object.assign({}, b)), status: "Baru", emailDate: "", kesepakatan: "", decisions: [] };
          state.claims.push(n); made.push(n.id);
        } else if (c.status === "Baru") {
          const before = sig(c);
          Object.assign(c, { supplier: r.supplier, material: r.material, coil: r.coil, po: r.po, kg: r.kg, hargaKg: r.hargaKg, costbookKg: r.costbookKg, priceUnitPo: r.priceUnitPo,
            problem: r.problem, alasan: r.alasan, tglProduksi: meta.tgl, srcKey: r.key, bukti: (r.bukti || []).map((b) => Object.assign({}, b)) });
          if (sig(c) !== before) updated.push(c.id);
        } else {
          const have = new Set((c.bukti || []).map((b) => b.fid || b.nama));
          (r.bukti || []).forEach((b) => { if (!have.has(b.fid || b.nama)) (c.bukti = c.bukti || []).push(Object.assign({}, b)); });
          kept.push(c.id);
        }
      });
      A.akanDibatalkan(meta, items).forEach((c) => { state.claims.splice(state.claims.indexOf(c), 1); removed.push(c.id); });
      save();
      return { made, updated, kept, removed };
    },
    // Create SPK: menggabungkan baris Retur WAITING satu supplier menjadi satu SPK (G6, usulan disetujui user 2026-10-28).
    createSpkGroup(ids, tgl) {
      const rows = ids.map((id) => state.spk.find((x) => x.id === id));
      const s = rows[0];
      rows.slice(1).forEach((o) => { s.items = s.items.concat(o.items); s.decs = decsOf(s).concat(decsOf(o)); state.spk.splice(state.spk.indexOf(o), 1); });
      s.no = next("spk", (n) => "SPK-RP-" + ym() + pad(n, 2)); s.tgl = tgl; s.status = "ON PROGRESS"; save();
      return s;
    },
    confirmSpk(spkId) {
      const s = state.spk.find((x) => x.id === spkId); s.status = "COMPLETED";
      state.sj.push({ id: "SJ-" + spkId, spk: spkId, no: "", tgl: "", supplier: s.supplier, alamat: SUPPLIERS[s.supplier].alamat, driver: "", nopol: "", status: "WAITING SJ", nilai: sjNilai(s) });
      save();
    },
    createSj(sjId, f) {
      const j = state.sj.find((x) => x.id === sjId);
      Object.assign(j, f, { no: next("sj", (n) => "SJ-" + pad(n, 2)), status: "NOT CONFIRMED" });
      const s = state.spk.find((x) => x.id === j.spk);
      state.dn.filter((d) => decsOf(s).includes(d.dec) && d.jalur === "Retur").forEach((d) => { d.sj = j.no; });
      save();
    },
    confirmSj(sjId) { state.sj.find((x) => x.id === sjId).status = "SJ CONFIRMED"; save(); },
    createDn(no, f) { const d = state.dn.find((x) => x.no === no); Object.assign(d, f, { tgl: d.tgl || today(), status: "Sudah Terbit" }); save(); },
    buatTagihan(no, f) { const d = state.dn.find((x) => x.no === no); d.tagihan = Object.assign({ tgl: today() }, f); d.status = "Belum Terima Uang"; save(); },
    terimaUang(no, f) { const d = state.dn.find((x) => x.no === no); d.terima = f; d.status = "Sudah Terima Uang"; save(); },
    // Request Payment dengan Alokasi DN (OQ-C8a): DN langsung berstatus Sudah Alokasi ke Hutang; jurnal baru terposting saat pembayaran final.
    requestPayment(b) {
      b.no = next("bayar", (n) => "RP-DN-" + ym() + "-" + pad(n, 2)); b.tgl = today(); b.status = "Request Payment"; b.admin = 0;
      b.dns.forEach((no) => { const d = state.dn.find((x) => x.no === no); d.status = "Sudah Alokasi ke Hutang"; d.alokasi = b.no; });
      state.bayar.push(b); save();
    },
    // Pembayaran final (payment_approve): DN menjadi baris detail negatif, jurnal terposting (FR-93).
    prosesBayar(no, admin, tgl) { const b = state.bayar.find((x) => x.no === no); b.admin = admin; b.tglBayar = tgl; b.status = "Dibayar"; save(); },
  };
  const decsOf = (s) => s.decs || (s.dec ? [s.dec] : []);
  // Progres turunan per jalur (G4): dibaca dari dokumen yang sudah ada, bukan status baru pada claim.
  const dnFinal = (d) => !!d && (d.status === "Sudah Terima Uang" || (d.status === "Sudah Alokasi ke Hutang" && (state.bayar.find((b) => b.no === d.alokasi) || {}).status === "Dibayar"));
  const dnText = (d) => (!d ? "DN belum dibuat" : d.status === "Sudah Alokasi ke Hutang" ? "DN dialokasikan ke hutang (" + ((state.bayar.find((b) => b.no === d.alokasi) || {}).status || "-") + ")" : "DN " + d.status);
  function progres(c) {
    return c.decisions.map((d) => {
      if (d.aksi === "Debit Note") { const dn = state.dn.find((x) => x.dec === d.id && x.jalur === "Kompensasi"); return { aksi: d.aksi, text: dnText(dn), done: dnFinal(dn) }; }
      if (d.aksi === "Lanjut Produksi") return { aksi: d.aksi, text: "Coil dirilis ke Produksi", done: true };
      if (d.aksi === "Jual Material") return { aksi: d.aksi, text: "Masuk Gudang Slitting", done: true };
      const sp = state.spk.find((x) => decsOf(x).includes(d.id)), sj = sp && state.sj.find((x) => x.spk === sp.id), dn = state.dn.find((x) => x.dec === d.id && x.jalur === "Retur");
      const t = ["SPK " + (sp ? sp.status : "-")].concat(sj ? ["SJ " + sj.status] : [], [dnText(dn)]).join(" → ");
      return { aksi: d.aksi, text: t, done: !!(sj && sj.status === "SJ CONFIRMED" && dnFinal(dn)) };
    });
  }
  function sjDn(s) { const ids = decsOf(s); return round2(state.dn.filter((d) => d.jalur === "Retur" && ids.includes(d.dec)).reduce((t, d) => t + dnIDR(d), 0)); }
  function sjNilai(s) { return round2(s.items.reduce((t, i) => t + nilaiRetur(claim(i.claim), i.kg), 0)); }

  // Jurnal gabungan satu claim, urut proses (tampilan di layar, FR-93). posted=false berarti proyeksi: peristiwanya belum terjadi.
  function jurnalOf(claimId) {
    const c = claim(claimId), ev = [], bayarDone = {};
    c.decisions.forEach((d) => {
      if (d.aksi === "Jual Material") {
        const j = state.jual.find((x) => x.dec === d.id);
        if (j) ev.push({ ev: "Process Decision: Jual Material", ref: d.id, posted: true, j: J.jualMaterial(j.nilai, j.materialKw) });
      } else if (d.aksi === "Debit Note") {
        const dn = state.dn.find((x) => x.dec === d.id && x.jalur === "Kompensasi");
        if (dn) ev.push({ ev: "Debit Note Kompensasi terbit", ref: dn.no, posted: dn.status !== "Belum Terbit", j: J.dnKompensasi(dn) });
      } else if (d.aksi === "Retur ke Supplier") {
        const sp = state.spk.find((x) => decsOf(x).includes(d.id)), sj = sp && state.sj.find((x) => x.spk === sp.id);
        if (sp) {
          const v = sj ? sj.nilai : sjNilai(sp), ref = sj && sj.no ? sj.no : (sp.no || "SPK belum dibuat");
          ev.push({ ev: "Surat Jalan Retur dibuat", ref, posted: !!sj && sj.status !== "WAITING SJ", j: J.sjCreate(v), note: sp.items.length > 1 ? "Surat jalan ini memuat " + sp.items.length + " claim satu supplier; nilai = jumlah semuanya." : "" });
          ev.push({ ev: "Surat Jalan Retur dikonfirmasi supplier", ref, posted: !!sj && sj.status === "SJ CONFIRMED", j: J.sjConfirm(v, sjDn(sp)) });
        }
      }
    });
    state.dn.filter((x) => x.claim === claimId).forEach((dn) => {
      const b = dn.alokasi && state.bayar.find((x) => x.no === dn.alokasi);
      if (b && !bayarDone[b.no]) {
        bayarDone[b.no] = 1;
        ev.push({ ev: "Pembayaran PO dengan Debit Note", ref: b.no, posted: b.status === "Dibayar", j: J.bayar(b), note: b.dns.length > 1 ? "Request Payment ini memotong " + b.dns.length + " Debit Note sekaligus, jurnal di bawah mencakup semuanya." : "" });
      }
      if (dn.terima) ev.push({ ev: "Penerimaan uang dari supplier", ref: dn.no, posted: true, j: J.terima(dn.terima.nominal) });
      else if (dn.tagihan) ev.push({ ev: "Penerimaan uang dari supplier", ref: dn.no, posted: false, j: J.terima(dnIDR(dn)) });
    });
    return ev;
  }

  window.CL = { files: FILES, get state() { return state; }, SUPPLIERS, ORIGIN, PPN, round2, today, save, reset, storageOk: () => stored, claim, jalurOf, holdStatus,
    nilaiMaterial, nilaiRetur, nilaiDnRetur, sjDn, dnIDR, progres, jurnalOf, J, A };
})();
