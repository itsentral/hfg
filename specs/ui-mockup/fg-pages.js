/* ============================================================
   fg-pages.js — Halaman Stock Finish Good (mockup), dari modul aplikasi fg_stock (index.php, history.php,
   controller + model). Hanya dibaca. Dua halaman: rekap stok (4 tab kategori) dan kartu stok (riwayat mutasi).
   Pembantu umum: wh-core.js (WHUI). Data: fg-data.js. Label kolom = nama kolom fg_stock / fg_stock_ledger.
   ============================================================ */
(() => {
  "use strict";
  const U = window.WHUI, W = U.W;
  const { $, esc, num, fdate, tbl, card, tabs, toast, dialog } = U;
  const P = {};
  const n2 = (n) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const n3 = (n) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: 3, maximumFractionDigits: 3 });
  const n4 = (n) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: 4, maximumFractionDigits: 4 });
  const rp = (n) => "Rp " + n2(n);
  const dmy = (s) => { const d = String(s).slice(0, 10).split("-"); return d[2] + "-" + d[1] + "-" + d[0]; };
  const upd = (s) => (s ? dmy(s) + " " + String(s).slice(11, 16) : "-");
  const base = { app: "Stock Finish Good", mod: "fg_stock", role: "Gudang / PPIC" };
  const kpi = (title, big, sub, val, cls) => `<div class="ap-card ap-card--shadow" style="flex:1;min-width:210px;margin:0"><div class="ap-card__body" style="padding:10px 14px"><div style="font-size:11.5px;font-weight:700;text-transform:uppercase;color:#6c757d">${title}</div><div style="font-size:24px;font-weight:700;color:${cls}">${big}</div><div class="ap-hint">${sub}</div><div style="font-weight:700">${val}</div></div></div>`;
  const kartuBtn = (pk) => `<a class="ap-btn ap-btn--sm ap-btn--light" style="border:1px solid #0dcaf0;color:#0aa2c0" href="fg-kartu-stok.html?produk=${encodeURIComponent(pk)}" title="Lihat Kartu Stok">Kartu Stok</a>`;
  const lbl = (t, c) => `<span class="ap-label ap-label--${c}">${esc(t)}</span>`;

  /* ======================= 1. Rekap Stok (fg_stock/index) ======================= */
  P.rekap = Object.assign({}, base, {
    title: "Stock Finish Good", ui: { tab: "kw1" },
    hint: "Rekapitulasi stok produk Finish Good (KW 1, KW 2) dan waste hasil produksi (Tong Coil, Scrap).",
    render() {
      const u = U.ui, K = { kw1: W.stok("KW1"), kw2: W.stok("KW2"), tong: W.stok("TONG_COIL"), scrap: W.stok("SCRAP") }, S = { kw1: W.sum(K.kw1), kw2: W.sum(K.kw2), tong: W.sum(K.tong), scrap: W.sum(K.scrap) };
      const cards = `<div class="cl-sumrow">${kpi("1. Finish Good KW 1", n2(S.kw1.qty) + " pcs", n2(S.kw1.berat) + " kg &bull; " + S.kw1.sku + " SKU", rp(S.kw1.nilai), "#0d6efd")}${kpi("2. Finish Good KW 2", n2(S.kw2.qty) + " pcs", n2(S.kw2.berat) + " kg &bull; " + S.kw2.sku + " SKU", rp(S.kw2.nilai), "#b58900")}${kpi("3. Tong Coil", n2(S.tong.berat) + " kg", num(S.tong.qty, 0) + " roll/pcs &bull; " + S.tong.sku + " SKU", rp(S.tong.nilai), "#0aa2c0")}${kpi("4. Scrap / Waste", n2(S.scrap.berat) + " kg", S.scrap.sku + " SKU", rp(S.scrap.nilai), "#dc3545")}</div>`;
      const nav = tabs([["kw1", `Finish Good KW 1 <span class="wh-tabs-count">${K.kw1.length}</span>`], ["kw2", `Finish Good KW 2 <span class="wh-tabs-count">${K.kw2.length}</span>`], ["tong", `Tong Coil <span class="wh-tabs-count">${K.tong.length}</span>`], ["scrap", `Scrap / Waste <span class="wh-tabs-count">${K.scrap.length}</span>`]], u.tab);
      const fgRow = (s, i, kw2) => `<tr><td>${i + 1}</td><td><b style="color:#0d6efd">${esc(s.produk_fg)}</b></td><td>${esc(s.nm_produk_fg || "-")}</td><td style="text-align:center">${lbl(s.sub_kategori || (kw2 ? "KW 2" : "KW 1"), kw2 ? "warning" : "light")}</td><td class="ap-r"><b>${n2(s.qty_stok)}</b></td><td class="ap-r">${n3(s.total_berat)}</td><td class="ap-r">${s.berat_referensi > 0 ? n4(s.berat_referensi) : "—"}</td><td class="ap-r">${rp(s.costbook)}</td><td class="ap-r pr-pos">${rp(s.total_nilai)}</td><td style="text-align:center"><small class="ap-hint">${upd(s.last_update)}</small></td><td style="text-align:center">${kartuBtn(s.produk_fg)}</td></tr>`;
      let body;
      if (u.tab === "kw1") body = tbl(["No", "Produk FG", "Nama Produk FG", "Sub Kategori", "Qty Stok", "Total Berat", "Berat Referensi", "Costbook", "Total Nilai", "Last Update", "Aksi"], K.kw1.map((s, i) => fgRow(s, i, false)).join(""), "Belum ada stok KW 1.");
      else if (u.tab === "kw2") body = tbl(["No", "Produk FG", "Nama Produk FG", "Sub Kategori", "Qty Stok", "Total Berat", "Berat Referensi", "Costbook", "Total Nilai", "Last Update", "Aksi"], K.kw2.map((s, i) => fgRow(s, i, true)).join(""), "Belum ada stok KW 2.") + '<p class="note">Total Nilai KW 2 = nilai inventory 80% (20% sisanya dibebankan sebagai biaya loss KW 2).</p>';
      else if (u.tab === "tong") body = tbl(["No", "Produk FG", "Nama Produk FG", "Qty Stok", "Total Berat", "Costbook", "Total Nilai", "Last Update", "Aksi"], K.tong.map((s, i) => `<tr><td>${i + 1}</td><td><code>${esc(s.produk_fg)}</code></td><td><b>${esc(s.nm_produk_fg || "-")}</b>${s.id_material ? `<br><small class="ap-hint">Kode Material: ${esc(s.id_material)}</small>` : ""}</td><td class="ap-r"><b>${num(s.qty_stok, 0)}</b></td><td class="ap-r pr-pos">${n3(s.total_berat)}</td><td class="ap-r">${rp(s.costbook)}</td><td class="ap-r pr-pos">${rp(s.total_nilai)}</td><td style="text-align:center"><small class="ap-hint">${upd(s.last_update)}</small></td><td style="text-align:center">${kartuBtn(s.produk_fg)}</td></tr>`).join(""), "Belum ada stok Tong Coil.") + '<p class="note">Costbook = Rp/kg material asal; Total Nilai = nilai persediaan 40% (60% dibebankan ke FOH).</p>';
      else body = tbl(["No", "Produk FG", "Sub Kategori", "Nama Produk FG", "Total Berat", "Costbook", "Total Nilai", "Last Update", "Aksi"], K.scrap.map((s, i) => `<tr><td>${i + 1}</td><td><code>${esc(s.produk_fg)}</code></td><td style="text-align:center">${lbl(s.sub_kategori || "Scrap", "danger")}</td><td><b>${esc(s.nm_produk_fg || "-")}</b>${s.id_material ? `<br><small class="ap-hint">Kode Material: ${esc(s.id_material)}</small>` : ""}</td><td class="ap-r"><b style="color:#dc3545">${n3(s.total_berat)}</b></td><td class="ap-r">${rp(s.costbook)}</td><td class="ap-r pr-pos">${rp(s.total_nilai)}</td><td style="text-align:center"><small class="ap-hint">${upd(s.last_update)}</small></td><td style="text-align:center">${kartuBtn(s.produk_fg)}</td></tr>`).join(""), "Belum ada stok Scrap.") + '<p class="note">Costbook = Rp/kg material asal; Total Nilai = nilai persediaan 40% dari scrap/reject.</p>';
      return cards + card("Rekapitulasi Stok Produk & Waste Hasil Produksi", nav + body, `<span><button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="xl">Export Excel</button> <a class="ap-btn ap-btn--sm ap-btn--secondary" href="fg-kartu-stok.html">Kartu Stok</a></span>`) +
        '<p class="note">Stok masuk dari Laporan Produksi final (KW 1, KW 2, Tong Coil, Scrap); stok keluar lewat Delivery. Klik Kartu Stok pada baris untuk melihat riwayat mutasi produk itu.</p>';
    },
    act: { xl() { toast("Export Excel rekap stok (mockup, tidak membuat berkas nyata)."); } },
  });

  /* ======================= 2. Kartu Stok (fg_stock/history) ======================= */
  const qp = new URLSearchParams(location.search);
  P.kartu = Object.assign({}, base, {
    title: "Kartu Stok Finish Good", ui: { kat: "ALL", produk: qp.get("produk") || "", dari: "", sampai: "" },
    hint: "Riwayat mutasi (kartu stok) Finish Good per kategori / produk / periode: IN dari produksi, OUT dari delivery, dengan saldo berjalan.",
    render() {
      const u = U.ui, pk = u.produk, prod = pk ? W.produkDropdown().find((p) => p.produk_fg === pk) : null;
      const kat = pk && prod && u.kat === "ALL" ? "ALL" : u.kat, rows = W.history(kat, u.dari, u.sampai, pk), S = W.sum(W.stok(kat === "ALL" ? null : kat));
      const catName = kat === "ALL" ? "Semua Kategori (KW 1, KW 2, Tong Coil, Scrap)" : W.KAT[kat];
      const dd = W.produkDropdown().filter((p) => kat === "ALL" || p.kategori_stok === kat);
      const filt = `<div class="cl-tools"><label>Kategori Stok <span class="ap-req">*</span> : <select class="ap-sel ap-in" data-u="kat"><option value="ALL"${kat === "ALL" ? " selected" : ""}>-- Semua Kategori --</option>${Object.entries(W.KAT).map(([k, v]) => `<option value="${k}"${kat === k ? " selected" : ""}>${v}</option>`).join("")}</select></label>
        <label>Pilih Produk <small>(Opsional)</small> : <select class="ap-sel ap-in" data-u="produk"><option value="">-- Semua Produk --</option>${dd.map((p) => `<option value="${esc(p.produk_fg)}"${pk === p.produk_fg ? " selected" : ""}>${esc(p.produk_fg + " — " + p.nm_produk_fg)}</option>`).join("")}</select></label>
        <label>Tanggal Dari : <input class="ap-in" type="date" data-u="dari" value="${esc(u.dari)}"></label><label>Tanggal Sampai : <input class="ap-in" type="date" data-u="sampai" value="${esc(u.sampai)}"></label><button type="button" class="ap-btn ap-btn--sm ap-btn--secondary" data-act="reset">Reset Filter</button></div>`;
      const info = `<div class="ap-card ap-card--shadow"><div class="ap-card__body" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap"><b style="color:#0d6efd;font-size:16px">${esc(catName)}${pk ? ` <span class="ap-label ap-label--secondary">Produk: ${esc(pk)}</span>` : ""}</b><button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="xl">Export Excel Riwayat</button></div></div>`;
      const cards = `<div class="cl-sumrow">${[["Total Item / SKU", S.sku + " SKU", "#0d6efd"], ["Total Qty Tersedia", n2(S.qty) + " pcs/roll", "#198754"], ["Total Berat Stok", n3(S.berat) + " kg", "#0aa2c0"], ["Total Nilai Persediaan", rp(S.nilai), "#212529"]].map((c) => `<div class="ap-card ap-card--shadow" style="flex:1;min-width:190px;margin:0;text-align:center"><div class="ap-card__body" style="padding:8px"><div style="font-size:11.5px;font-weight:700;text-transform:uppercase;color:#6c757d">${c[0]}</div><div style="font-size:20px;font-weight:700;color:${c[2]}">${c[1]}</div></div></div>`).join("")}</div>`;
      const dash = (c, v, f) => (c ? f(v) : "—");
      const period = u.dari || u.sampai ? ` <span class="ap-label ap-label--secondary">Periode: ${u.dari ? dmy(u.dari) : "Awal"} s/d ${u.sampai ? dmy(u.sampai) : "Sekarang"}</span>` : "";
      return card("Filter Riwayat Mutasi Finish Good", filt, '<a class="ap-btn ap-btn--sm ap-btn--secondary" href="fg-stock.html">&#8592; Kembali ke Rekap Stok</a>') + info + cards +
        card("Riwayat Mutasi Kartu Stok" + period, tbl(["No", "Tgl Transaksi", "Produk FG", "No Referensi", "SPK No", "Kategori Stok", "Jenis Mutasi", "Qty In", "Qty Out", "Qty Saldo", "Berat In", "Berat Out", "Berat Saldo", "Costbook", "Total Nilai", "Saldo Nilai", "Keterangan"],
          rows.map((r, i) => `<tr style="background:${r.jenis_mutasi === "IN" ? "#e8f5ee" : "#fdecea"}"><td>${i + 1}</td><td>${dmy(r.tgl_transaksi)}</td><td><b style="color:#0d6efd">${esc(r.produk_fg)}</b><br><small class="ap-hint">${esc(r.nm_produk_fg || "-")}</small></td><td><b>${esc(r.no_referensi)}</b></td><td>${r.spk_no ? lbl(r.spk_no, "light") : "-"}</td>
            <td style="text-align:center">${lbl(r.kategori_stok, "secondary")}${r.sub_kategori ? `<br><small class="ap-hint">${esc(r.sub_kategori)}</small>` : ""}</td><td style="text-align:center">${lbl(r.jenis_mutasi, r.jenis_mutasi === "IN" ? "success" : "danger")}</td>
            <td class="ap-r">${dash(r.jenis_mutasi === "IN", r.qty_in, n2)}</td><td class="ap-r">${dash(r.jenis_mutasi === "OUT", r.qty_out, n2)}</td><td class="ap-r"><b>${n2(r.qty_saldo)}</b></td><td class="ap-r">${dash(r.jenis_mutasi === "IN", r.berat_in, n3)}</td><td class="ap-r">${dash(r.jenis_mutasi === "OUT", r.berat_out, n3)}</td><td class="ap-r"><b>${n3(r.berat_saldo)}</b></td>
            <td class="ap-r">${dash(r.costbook > 0, r.costbook, n2)}</td><td class="ap-r">${dash(r.total_nilai > 0, r.total_nilai, n2)}</td><td class="ap-r pr-pos">${dash(r.saldo_nilai > 0, r.saldo_nilai, n2)}</td><td><small class="ap-hint">${esc(r.keterangan || "-")}</small></td></tr>`).join(""), "Tidak ada riwayat mutasi untuk filter ini."),
          `<span class="ap-label ap-label--primary">${rows.length} Catatan Transaksi</span>`);
    },
    act: { reset() { Object.assign(U.ui, { kat: "ALL", produk: "", dari: "", sampai: "" }); U.render(); }, xl() { toast("Export Excel riwayat (mockup, tidak membuat berkas nyata)."); } },
  });

  U.init(P);
})();
