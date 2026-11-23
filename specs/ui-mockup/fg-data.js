/* ============================================================
   fg-data.js — Data contoh Modul Stock Finish Good (mockup). Dipakai fg-stock.html dan fg-kartu-stok.html.
   Acuan modul aplikasi (read-only): application/modules/fg_stock (controller, model, views index + history).
   Tabel: fg_stock (rekap stok) dan fg_stock_ledger (kartu stok). Stok = saldo akhir dari ledger (konsisten).
   Diekspos sebagai window.WH supaya wh-core.js bisa dipakai ulang (halaman ini tidak memuat wh-data.js). Data baca-saja.
   ============================================================ */
(() => {
  "use strict";
  const round = (n, d = 2) => Math.round((Number(n) || 0) * Math.pow(10, d)) / Math.pow(10, d);
  // produk: kode, nama, kategori_stok, sub_kategori, id_material, berat_referensi (kg/pcs)
  const P = {
    "PRD-0101": ["Spandek 0.30 x 1000", "KW1", "KW 1", "", 24.0],
    "PRD-0102": ["Spandek 0.35 x 1000", "KW1", "KW 1", "", 34.6],
    "PRD-0105": ["Reng 0.45", "KW1", "KW 1", "", 4.8],
    "PRD-0101-KW2-I-01": ["Spandek 0.30 x 1000-KW2-I-01", "KW2", "KW 2 Internal", "", 22.5],
    "PRD-0101-KW2-S-01": ["Spandek 0.30 x 1000-KW2-S-01", "KW2", "KW 2 Supplier", "", 22.0],
    "TONG-MTR-001": ["Tong Coil GALVALUME MOTHER COIL NON BORON 0.40-1219-AZ150 - G550 - SILVER", "TONG_COIL", "Tong Coil", "MTR-001", 0],
    "SCR-WRP-MTR-001": ["Wrapping GALVALUME MOTHER COIL NON BORON 0.40-1219-AZ150 - G550 - SILVER", "SCRAP", "Wrapping", "MTR-001", 0],
    "SCR-PSU-MTR-001": ["Potongan Pisau GALVALUME MOTHER COIL NON BORON 0.40-1219-AZ150 - G550 - SILVER", "SCRAP", "Potongan Pisau", "MTR-001", 0],
    "SCR-RPI-MTR-001": ["Reject Product Internal GALVALUME MOTHER COIL NON BORON 0.40-1219-AZ150 - G550 - SILVER", "SCRAP", "Reject Product Internal", "MTR-001", 0],
  };
  // [tgl, produk, jenis, qty, berat, costbook, no_referensi, spk_no, keterangan]
  // costbook: Rp/pcs untuk KW1 & KW2, Rp/kg material untuk Tong Coil & Scrap (nilai persediaan 40%)
  const RAW = [
    ["2026-11-07", "PRD-0101", "IN", 68, 1631.5, 411709, "LP-2611-0001", "SPK-2611-0004", "Hasil produksi KW 1 (Laporan Produksi final)"],
    ["2026-11-07", "PRD-0101-KW2-I-01", "IN", 4, 90, 308880, "LP-2611-0001", "SPK-2611-0004", "Hasil produksi KW 2 Internal (inventory 80%)"],
    ["2026-11-07", "PRD-0101-KW2-S-01", "IN", 2, 44, 301800, "LP-2611-0001", "SPK-2611-0004", "Hasil produksi KW 2 Supplier (inventory 80%)"],
    ["2026-11-07", "TONG-MTR-001", "IN", 1, 18, 15400, "LP-2611-0001", "SPK-2611-0004", "Tong coil hasil produksi (nilai persediaan 40%)"],
    ["2026-11-07", "SCR-WRP-MTR-001", "IN", 0, 6.5, 15400, "LP-2611-0001", "SPK-2611-0004", "Scrap wrapping (nilai persediaan 40%)"],
    ["2026-11-07", "SCR-PSU-MTR-001", "IN", 0, 12.2, 15400, "LP-2611-0001", "SPK-2611-0004", "Scrap potongan pisau (nilai persediaan 40%)"],
    ["2026-11-07", "SCR-RPI-MTR-001", "IN", 0, 3.1, 15400, "LP-2611-0001", "SPK-2611-0004", "Reject product internal (nilai persediaan 40%)"],
    ["2026-11-09", "PRD-0102", "IN", 120, 4152, 580000, "LP-2611-0003", "", "Stok bebas hasil produksi (di luar target SPK)"],
    ["2026-11-10", "PRD-0105", "IN", 400, 1920, 82000, "LP-2611-0004", "SPK-2611-0005", "Hasil produksi KW 1 (Laporan Produksi final)"],
    ["2026-11-12", "PRD-0101", "OUT", 20, 479.8, 411709, "DLV-2611-0003", "", "Pengiriman ke customer (Delivery)"],
    ["2026-11-14", "PRD-0105", "OUT", 150, 720, 82000, "DLV-2611-0005", "", "Pengiriman ke customer (Delivery)"],
    ["2026-11-16", "PRD-0102", "OUT", 40, 1384, 580000, "DLV-2611-0006", "", "Pengiriman ke customer (Delivery)"],
  ];
  const stock = {}, ledger = [];
  RAW.forEach((r, i) => {
    const [tgl, pk, jenis, qty, berat, cb, ref, spk, ket] = r, p = P[pk], tong = p[1] === "TONG_COIL" || p[1] === "SCRAP";
    const s = stock[pk] || (stock[pk] = { produk_fg: pk, nm_produk_fg: p[0], kategori_stok: p[1], sub_kategori: p[2], id_material: p[3], berat_referensi: p[4], qty_stok: 0, total_berat: 0, total_nilai: 0, costbook: cb, last_update: tgl + " 08:00" });
    const nilai = round(tong ? berat * cb * 0.4 : qty * cb);               // tong/scrap: 40% dari berat x costbook material
    const sg = jenis === "IN" ? 1 : -1;
    s.qty_stok = round(s.qty_stok + sg * qty); s.total_berat = round(s.total_berat + sg * berat, 3); s.total_nilai = round(s.total_nilai + sg * nilai); s.costbook = cb; s.last_update = tgl + " " + String(8 + (i % 8)).padStart(2, "0") + ":" + String((i * 7) % 60).padStart(2, "0");
    ledger.push({ id: i + 1, tgl_transaksi: tgl, produk_fg: pk, nm_produk_fg: p[0], no_referensi: ref, spk_no: spk, kategori_stok: p[1], sub_kategori: p[2], jenis_mutasi: jenis,
      qty_in: jenis === "IN" ? qty : 0, qty_out: jenis === "OUT" ? qty : 0, qty_saldo: s.qty_stok, berat_in: jenis === "IN" ? berat : 0, berat_out: jenis === "OUT" ? berat : 0, berat_saldo: s.total_berat,
      costbook: cb, total_nilai: nilai, saldo_nilai: s.total_nilai, keterangan: ket });
  });
  /* ---------- hasil Incoming Subkont (FEAT-12): FG baru (KW 1) dan scrap 100% ke fg_stock; dibaca baca-saja dari data mockup Subkont (sk-*.html) ---------- */
  const skKey = () => { try { let best = -1, k = null; for (let q = 0; q < localStorage.length; q++) { const m = /^mockSK\.data(\d+)$/.exec(localStorage.key(q) || ""); if (m && +m[1] > best) { best = +m[1]; k = localStorage.key(q); } } return k; } catch (e) { return null; } };
  const skRaw = (() => { try { const k = skKey(); return k ? localStorage.getItem(k) : null; } catch (e) { return null; } })();
  const skState = (() => { try { return skRaw ? JSON.parse(skRaw) : null; } catch (e) { return null; } })();
  const addIn = (tgl, pk, nm, kat, sub, qty, berat, nilai, ref, spk, ket) => {
    const cb = kat === "SCRAP" ? (berat ? round(nilai / berat) : 0) : (qty ? round(nilai / qty) : 0);
    const s = stock[pk] || (stock[pk] = { produk_fg: pk, nm_produk_fg: nm, kategori_stok: kat, sub_kategori: sub, id_material: "", berat_referensi: qty ? round(berat / qty, 4) : 0, qty_stok: 0, total_berat: 0, total_nilai: 0, costbook: cb, last_update: tgl + " 08:00" });
    s.qty_stok = round(s.qty_stok + qty); s.total_berat = round(s.total_berat + berat, 3); s.total_nilai = round(s.total_nilai + nilai); s.costbook = cb; s.last_update = tgl + " 12:00";
    ledger.push({ id: ledger.length + 1, tgl_transaksi: tgl, produk_fg: pk, nm_produk_fg: nm, no_referensi: ref, spk_no: spk || "", kategori_stok: kat, sub_kategori: sub, jenis_mutasi: "IN",
      qty_in: qty, qty_out: 0, qty_saldo: s.qty_stok, berat_in: berat, berat_out: 0, berat_saldo: s.total_berat, costbook: cb, total_nilai: nilai, saldo_nilai: s.total_nilai, keterangan: ket });
  };
  // Confirm Surat Jalan Subkont (barang FG dikirim ke vendor) = OUT dari fg_stock pada costbook produk asal; Incoming Subkont FG = IN kode FG baru pada costbook baru
  const addOut = (tgl, pk, qty, nilai, ref, spk, ket) => {
    const s = stock[pk]; if (!s) return;
    const berat = round(qty * (s.berat_referensi || 0), 3), cb = qty ? round(nilai / qty) : s.costbook;
    s.qty_stok = round(s.qty_stok - qty); s.total_berat = round(s.total_berat - berat, 3); s.total_nilai = round(s.total_nilai - nilai); s.last_update = tgl + " 11:00";
    ledger.push({ id: ledger.length + 1, tgl_transaksi: tgl, produk_fg: pk, nm_produk_fg: s.nm_produk_fg, no_referensi: ref, spk_no: spk || "", kategori_stok: s.kategori_stok, sub_kategori: s.sub_kategori, jenis_mutasi: "OUT",
      qty_in: 0, qty_out: qty, qty_saldo: s.qty_stok, berat_in: 0, berat_out: berat, berat_saldo: s.total_berat, costbook: cb, total_nilai: nilai, saldo_nilai: s.total_nilai, keterangan: ket });
  };
  if (skState) {
    (skState.dlv || []).filter((d) => d.status === "Confirmed").forEach((d) => {
      const per = {};
      d.detail.filter((x) => !x.pack_code && stock[x.kode_barang]).forEach((x) => { const o = per[x.kode_barang + "|" + x.spk_no] || (per[x.kode_barang + "|" + x.spk_no] = { pk: x.kode_barang, spk: x.spk_no, qty: 0, nilai: 0 }); o.qty += x.qty; o.nilai += (x.nilai != null ? x.nilai : x.qty * stock[x.kode_barang].costbook); });
      Object.values(per).forEach((o) => addOut(d.tgl_kirim, o.pk, o.qty, round(o.nilai), d.kode_delivery, o.spk, "Dikirim ke vendor Subkont (Confirm Surat Jalan " + d.no_surat_jalan + "); jurnal Db Material In Supplier / Cr Persediaan Barang Jadi"));
    });
  }
  if (skState) {
    const incDate = (no) => ((skState.inc || []).find((i) => i.no_incoming === no) || {}).tgl_incoming || new Date().toISOString().slice(0, 10);
    (skState.fgBaru || []).forEach((x) => {
      const lap = (skState.lap || []).find((l) => l.no_laporan === x.no_laporan) || {}, g = (lap.fg || []).find((z) => z.produk_fg_baru === x.kode_fg_baru) || {};
      addIn(x.tgl || incDate(x.no_incoming), x.kode_fg_baru, x.kode_fg_baru, "KW1", "KW 1", x.qty, x.berat_kg != null ? x.berat_kg : (g.total_berat_kg || 0), x.nilai != null ? x.nilai : round(x.qty * x.costbook_per_pcs), x.no_incoming, x.spk_no || lap.spk_no, "Hasil Subkont (Konfirmasi Incoming " + x.no_incoming + "); kode FG baru dengan No. Urut Subkont " + x.no_urut_subkont);
    });
    (skState.scrapStok || []).forEach((x) => {
      const lap = (skState.lap || []).find((l) => l.no_laporan === x.no_laporan) || {}, kode = x.kode_barang || lap.kode_barang || "MAT", nm = x.nama_barang || kode;
      addIn(x.tgl || incDate(x.no_incoming), "SCR-SUB-" + kode, "Scrap Subkont " + nm, "SCRAP", "Scrap Subkont", 0, x.berat_kg, x.nilai, x.no_incoming, x.spk_no || lap.spk_no, "Scrap hasil slitting Subkont (Konfirmasi Incoming " + x.no_incoming + "; 100% Inventory Scrap)");
    });
  }
  // segarkan bila data Subkont berubah di tab lain
  window.addEventListener("focus", () => { try { const k = skKey(), now = k ? localStorage.getItem(k) : null; if (now !== skRaw) location.reload(); } catch (e) { /* abaikan */ } });
  const KAT = { KW1: "1. Finish Good KW 1", KW2: "2. Finish Good KW 2", TONG_COIL: "3. Tong Coil", SCRAP: "4. Scrap / Waste" };
  const stok = (k) => Object.values(stock).filter((s) => !k || s.kategori_stok === k).sort((a, b) => (["TONG_COIL", "SCRAP"].includes(k) ? (a.sub_kategori + a.nm_produk_fg).localeCompare(b.sub_kategori + b.nm_produk_fg) : a.produk_fg.localeCompare(b.produk_fg)));
  const sum = (list) => ({ sku: list.length, qty: round(list.reduce((a, s) => a + s.qty_stok, 0)), berat: round(list.reduce((a, s) => a + s.total_berat, 0), 3), nilai: round(list.reduce((a, s) => a + s.total_nilai, 0)) });
  window.WH = {
    state: {}, save() {}, storageOk: () => true, reset() {},
    KAT, stok, sum, ledger: () => ledger.slice(),
    produkDropdown: () => Object.values(stock).sort((a, b) => a.kategori_stok.localeCompare(b.kategori_stok) || a.produk_fg.localeCompare(b.produk_fg)),
    history(kat, dari, sampai, produk) {
      return ledger.filter((l) => (!kat || kat === "ALL" || l.kategori_stok === kat) && (!produk || l.produk_fg === produk) && (!dari || l.tgl_transaksi >= dari) && (!sampai || l.tgl_transaksi <= sampai)).sort((a, b) => b.tgl_transaksi.localeCompare(a.tgl_transaksi) || b.id - a.id);
    },
  };
})();
