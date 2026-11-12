/* ============================================================
   wh-pages.js (bagian A) — Material Master, Stock Gudang Coil, Stock Value, Kartu Stok.
   Acuan modul aplikasi: material_master, warehouse (index / stock_value / kartu_stok). Hanya dibaca, tidak diubah.
   Bagian B (SPK Material, Request List, Stock From Warehouse, Unpack Coil) ada di wh-pages2.js.
   ============================================================ */
(() => {
  "use strict";
  const U = window.WHUI, W = U.W, S = () => W.state;
  const { $, esc, num, rp, fdate, lab, btn, tbl, card, stat, kv, row, inp, sel, txt, isNumEl, pv, tabs, toast, warn, done, confirmThen, openModal, closeModal } = U;
  const P = (window.WHP = {});
  const mn = (id) => W.matBy(id).nama || id;
  const matsCell = (ids) => `<div class="wh-mats">${ids.map((id) => `<div><span class="ap-link">&#9679;</span> <b>${esc(mn(id))}</b> <small>(${esc(id)})</small></div>`).join("")}</div>`;
  const pill = (c) => `<span class="wh-pack">${esc(c)}</span>`;
  const search = (key, ph) => `<div class="cl-tools"><input class="ap-in" data-u="${key}" placeholder="${esc(ph)}" value="${esc(U.ui[key] || "")}"></div>`;
  const excel = (what) => toast(`Download Excel "${what}" (mockup, tidak membuat berkas nyata).`);

  /* ======================= 1. Material Master (material_master) ======================= */
  let nmManual = false;
  P.material = {
    title: "Material Master", mod: "material_master", role: "Gudang / PPIC", ui: { lv1: "", lv2: "", lv3: "", q: "" },
    hint: "Master material coil: klasifikasi 3 level (Metal Type, Slitted / Mother Coil, Boron / Non Boron), spesifikasi, satuan, dan batas stok.",
    render() {
      const u = U.ui, l = S().mats.filter((m) => (!u.lv1 || m.lv1 === u.lv1) && (!u.lv2 || m.lv2 === u.lv2) && (!u.lv3 || m.lv3 === u.lv3) && (!u.q || (m.nama + m.id).toLowerCase().includes(u.q.toLowerCase())));
      const opt = (k, a, ph) => `<select class="ap-sel ap-in" data-u="${k}"><option value="">${ph}</option>${a.map((x) => `<option${x === u[k] ? " selected" : ""}>${x}</option>`).join("")}</select>`;
      return `<div class="cl-sumrow">${stat(S().mats.length, "Total material")}${stat(S().mats.filter((m) => m.aktif).length, "Aktif")}${stat(S().mats.filter((m) => !m.aktif).length, "Non aktif")}</div>
        ${card("Daftar Material", `<div class="cl-tools">${opt("lv1", W.LV1, "METAL TYPE")}${opt("lv2", W.LV2, "SLITTED / MOTHER COIL")}${opt("lv3", W.LV3, "BORON / NON BORON")}<input class="ap-in" data-u="q" placeholder="Cari kode / nama material..." value="${esc(u.q)}">
          <span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="add">+ Add</button> <button type="button" class="ap-btn ap-btn--sm ap-btn--info" data-act="excel">Excel</button></span></div>` +
          tbl(["#", "Kode", "Metal Type", "Slitted / Mother Coil", "Boron / Non Boron", "Nama Material", "Status", "Action"],
            l.map((m, i) => `<tr><td>${i + 1}</td><td>${esc(m.id)}</td><td>${esc(m.lv1)}</td><td>${esc(m.lv2)}</td><td>${esc(m.lv3)}</td><td>${esc(m.nama)}</td>
              <td><span class="wh-switch${m.aktif ? " is-on" : ""}" data-act="toggle" data-id="${m.id}" title="Klik untuk ${m.aktif ? "menonaktifkan" : "mengaktifkan"}"></span> ${lab(m.aktif ? "Aktif" : "Non Aktif")}</td>
              <td class="ap-act ap-act-nowrap">${btn("info", "view", m.id, "View")}${btn("warning", "edit", m.id, "Edit")}${btn("danger", "del", m.id, "Delete")}</td></tr>`).join(""), "Tidak ada material."))}
        <p class="note">Nama Material dibentuk otomatis: Metal Type + Slitted/Mother Coil + Boron/Non Boron + Thickness-Width-Coating - Tensile - Warna. Nama boleh diketik manual; klik "Generate ulang" untuk kembali ke nama otomatis (perilaku form Add di aplikasi).</p>`;
    },
    act: {
      add() { form(null, "add"); }, edit(d) { form(d.id, "edit"); }, view(d) { form(d.id, "view"); },
      excel() { excel("Material Master"); },
      toggle(d) { const m = S().mats.find((x) => x.id === d.id); m.aktif = m.aktif ? 0 : 1; W.save(); U.render(); toast(`Status ${m.id} diubah menjadi ${m.aktif ? "Aktif" : "Non Aktif"}.`, "ok"); },
      del(d) { confirmThen({ title: "Hapus material?", text: "Material " + d.id + " akan dihapus dari master." }, () => { S().mats = S().mats.filter((x) => x.id !== d.id); W.save(); U.render(); toast("Material dihapus.", "ok"); }); },
    },
  };
  function form(id, mode) {
    const src = id ? S().mats.find((m) => m.id === id) : null;
    const f = src ? JSON.parse(JSON.stringify(src)) : { id: "", lv1: "", lv2: "", lv3: "", nama: "", hs: "", trade: "", width: "", coating: "", thickness: "", warna: "", hardness: "", tensile: "", no_accurate: "", unit_other: "KG", konversi_other: 1, unit_packing: "", konversi: 0, unit: "KG", max_stok: 0, min_stok: 0, length: 0, wide: 0, high: 0, supplier: "", msds: "", harga_beli: 0, aktif: 1 };
    nmManual = mode === "edit";
    const ro = mode === "view", d = ro ? " disabled" : "";
    const g = (label, field, req) => row(label, field, req);
    const S1 = (k, opts, blank) => sel(k, f[k], opts, { blank }).replace("<select", `<select${d}`);
    const I1 = (k, o = {}) => inp(k, f[k], Object.assign({ ro }, o));
    openModal({
      title: (mode === "add" ? "Add " : mode === "edit" ? "Edit " : "View ") + "Material Master" + (id ? " — " + id : ""), size: "xl", ok: ro ? null : "Save",
      body: () => `<div class="wh-sec">Klasifikasi &amp; Nama</div><div class="wh-grid2">
          ${g("Metal Type", S1("lv1", W.LV1, "Select Metal Type"), 1)}${g("Slitted / Mother Coil", S1("lv2", W.LV2, "Select Slitted / Mother Coil"), 1)}
          ${g("Boron / Non Boron", S1("lv3", W.LV3, "Select Boron / Non Boron"), 1)}${g("HS Code", S1("hs", W.HS, "Select HS Code"), 1)}</div>
        ${row("Nama Material", I1("nama", { ph: "Material Master" }) + (ro ? "" : '<div class="ap-hint">Otomatis dari pilihan di bawah. <a class="ap-link" data-act="regen">Generate ulang</a></div>'), 1)}
        <div class="wh-sec">Spesifikasi</div><div class="wh-grid2">
          ${g("Trade Name", I1("trade", { ph: "Alias" }))}${g("No. Accurate", I1("no_accurate"), 1)}
          ${g("Width", I1("width"), 1)}${g("Thickness", I1("thickness"), 1)}
          ${g("Coating", S1("coating", W.COATING, "Select An Option"), 1)}${g("Tensile", S1("tensile", W.TENSILE, "Select An Option"), 1)}
          ${g("Warna", I1("warna"))}${g("Hardness", I1("hardness"), 1)}</div>
        <div class="wh-sec">Satuan &amp; Stok</div><div class="wh-grid2">
          ${g("Unit Other", S1("unit_other", W.UNIT, "Select An Option"), 1)}${g("Konversi Other", I1("konversi_other", { num: 1 }), 1)}
          ${g("Unit Packing", S1("unit_packing", W.UNIT, "Select An Option"))}${g("Konversi", I1("konversi", { num: 1 }))}
          ${g("Unit", S1("unit", W.UNIT, "Select An Option"))}<div></div>
          ${g("Max Stok", I1("max_stok", { num: 1 }), 1)}${g("Min Stok", I1("min_stok", { num: 1 }), 1)}</div>
        <div class="wh-sec">Dimensi &amp; Lainnya</div><div class="wh-grid2">
          ${g("Length", I1("length", { num: 1 }))}${g("Wide", I1("wide", { num: 1 }))}${g("High", I1("high", { num: 1 }))}${g("CBM", '<span data-calc="cbm"></span>')}
          ${g("Supplier", S1("supplier", W.SUPPLIER, "Select An Option"))}${g("Upload MSDS", ro ? esc(f.msds || "-") : '<input type="file" class="ap-in" accept=".pdf,.jpg,.jpeg,.png"><div class="ap-hint">PDF/JPG/PNG, maks. 2 MB.</div>')}</div>`,
      calc: { cbm: () => `<b>${num((Number(f.length) * Number(f.wide) * Number(f.high)) / 1e6, 4)}</b> <span class="ap-hint">(Length × Wide × High ÷ 1.000.000; asumsi satuan cm — rumus dan satuan perlu dikonfirmasi)</span>` },
      onField(el) {
        const k = el.dataset.f;
        f[k] = isNumEl(el) ? pv(el) || 0 : el.value;
        if (k === "nama") nmManual = true;
        else if (!nmManual && ["lv1", "lv2", "lv3", "thickness", "width", "coating", "tensile", "warna"].includes(k)) { f.nama = W.genNama(f); const n = $('#pcModal [data-f="nama"]'); if (n) n.value = f.nama; }
        U.calc();
      },
      act: { regen() { nmManual = false; f.nama = W.genNama(f); const n = $('#pcModal [data-f="nama"]'); if (n) n.value = f.nama; } },
      onOk() {
        const req = ["lv1", "lv2", "lv3", "nama", "hs", "width", "coating", "thickness", "hardness", "tensile", "no_accurate", "unit_other"];
        if (req.some((k) => !String(f[k]).trim()) || !(f.konversi_other > 0) || !(f.max_stok > 0) || !(f.min_stok >= 0)) { warn("Field bertanda * wajib diisi."); return; }
        if (mode === "add") { f.id = "MTR-" + String(S().mats.length + 1).padStart(3, "0"); S().mats.push(f); } else Object.assign(S().mats.find((m) => m.id === id), f);
        W.save(); closeModal(); U.render(); done("Data material berhasil disimpan.");
      },
    });
  }

  /* ======================= 2. Stock Gudang Coil (warehouse/index) ======================= */
  const packRows = (list) => list.map((p, i) => `<tr><td>${i + 1}</td><td>${pill(p.code)}</td><td>${matsCell(p.mats)}</td><td class="ap-r">${p.roll}</td><td class="ap-r">${num(p.nw)}</td><td class="ap-r">${num(p.nwRoll)}</td><td class="ap-r">${num(p.gw)}</td><td class="ap-r">${num(p.gwRoll)}</td>
    <td class="ap-act ap-act-nowrap">${btn("info", "qr", p.code, "QR")}${btn("light", "pack", p.code, "Detail")}</td></tr>`).join("");
  const PACKH = ["No", "Pack Code", "Nama Material", "Roll", "Net Weight Total", "Net Weight Per Roll", "Gross Weight Total", "Gross Weight Per Roll", "Action"];
  const qfilter = (list, q) => (q ? list.filter((p) => (p.code + " " + p.mats.map(mn).join(" ")).toLowerCase().includes(q.toLowerCase())) : list);
  function packModal(code) {
    const cs = S().coils.filter((c) => c.pack === code);
    openModal({ title: "Pack Detail — " + code, size: "lg", hdr: "info", body: () => tbl(["No", "Nama Material", "No. Coil", "Kode Internal", "Net Weight", "Gross Weight", "Length", "Type"],
      cs.map((c, i) => `<tr><td>${i + 1}</td><td>${esc(mn(c.mat))}</td><td>${esc(c.no_coil)}</td><td>${esc(c.kode_internal)}</td><td class="ap-r">${num(c.net_weight)}</td><td class="ap-r">${num(c.gross_weight)}</td><td class="ap-r">${num(c.length)}</td><td>${c.baby ? '<span class="ap-label ap-label--warning">Baby</span>' : '<span class="ap-label ap-label--secondary">Normal</span>'}</td></tr>`).join(""), "Tidak ada coil.",
      `<tr class="wh-foot"><td colspan="4" class="ap-r">Total</td><td class="ap-r">${num(cs.reduce((a, c) => a + c.net_weight, 0))}</td><td class="ap-r">${num(cs.reduce((a, c) => a + c.gross_weight, 0))}</td><td colspan="2"></td></tr>`) });
  }
  const qrModal = (code) => openModal({ title: "QR Code Pack — " + code, size: "sm", body: () => `<div class="wh-qr">${U.qrSvg(code)}</div><p class="note" style="text-align:center">${esc(code)}<br>Pola QR contoh (bukan QR sungguhan).</p>` });
  P.stockcoil = {
    title: "Stock Gudang Coil", mod: "warehouse", role: "Gudang / PPIC", ui: { tab: "pro", q: "", hdate: "", hgud: "", hshown: false, hq: "" },
    hint: "Stok coil per pack: Production Warehouse, Slitting Warehouse, dan posisi stok per tanggal (History Per Days).",
    render() {
      const u = U.ui, tab = u.tab;
      const nav = tabs([["pro", "Production Warehouse"], ["sli", "Slitting Warehouse"], ["hist", "History Per Days"]], tab);
      if (tab === "hist") {
        const l = u.hshown ? qfilter(W.packs().filter((p) => p.gudang !== "PRT" && (!u.hgud || p.gudang === u.hgud)), u.hq) : [];
        return `${nav}${card("History Per Days", `<div class="cl-tools"><label>As of Date : <input class="ap-in" type="date" data-u="hdate" value="${esc(u.hdate)}"></label><label>Gudang : <select class="ap-sel ap-in" data-u="hgud"><option value="">-- All --</option><option value="PRO"${u.hgud === "PRO" ? " selected" : ""}>Production</option><option value="SLI"${u.hgud === "SLI" ? " selected" : ""}>Slitting</option></select></label>
          <button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="show">Show</button><button type="button" class="ap-btn ap-btn--sm ap-btn--secondary" data-act="reset">Reset</button>${u.hshown ? '<button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="xl">Excel</button>' : ""}
          <input class="ap-in" data-u="hq" placeholder="Filter pack / material..." value="${esc(u.hq)}"></div>` +
          tbl(["No", "Pack Code", "Nama Material", "Roll", "Net Weight Total", "Net Weight Per Roll", "Gross Weight Total", "Gross Weight Per Roll", "Kode Gudang", "Status"],
            l.map((p, i) => `<tr><td>${i + 1}</td><td>${pill(p.code)}</td><td>${matsCell(p.mats)}</td><td class="ap-r">${p.roll}</td><td class="ap-r">${num(p.nw)}</td><td class="ap-r">${num(p.nwRoll)}</td><td class="ap-r">${num(p.gw)}</td><td class="ap-r">${num(p.gwRoll)}</td><td>${p.gudang}</td><td>${lab("IN")}</td></tr>`).join(""),
            u.hshown ? "Tidak ada data pada tanggal ini." : "Pilih tanggal lalu klik Show."))}
          <p class="note">Mockup: posisi per tanggal memakai data stok saat ini (tidak menghitung mundur dari riwayat transaksi). Query sebenarnya: warehouse/get_packs_perday.</p>`;
      }
      const kd = tab === "pro" ? "PRO" : "SLI", l = qfilter(W.packs(kd), u.q);
      return `${nav}${card(tab === "pro" ? "Production Warehouse" : "Slitting Warehouse", search("q", "Search pack / material...") + tbl(PACKH, packRows(l), "No pack data available."))}
        <p class="note">Tombol QR menampilkan kode QR pack untuk label; Detail menampilkan coil di dalam pack (No. Coil, Kode Internal, berat, panjang, Baby/Normal). Stok coil yang pindah gudang lewat Request List dilihat di menu Stock From Warehouse.</p>`;
    },
    act: {
      qr: (d) => qrModal(d.id), pack: (d) => packModal(d.id), xl: () => excel("History Per Days Stock Coil"),
      show() { if (!U.ui.hdate) { warn("Please select a date."); return; } U.ui.hshown = true; U.render(); },
      reset() { Object.assign(U.ui, { hdate: "", hgud: "", hq: "", hshown: false }); U.render(); },
    },
  };

  /* ======================= 3. Stock Value (warehouse/stock_value) ======================= */
  const svRows = (kd) => W.stockValue(kd).sort((a, b) => a.mat.localeCompare(b.mat));
  const histRows = (mid) => W.history(mid);
  function histModal(mid) {
    const h = histRows(mid), m = W.matBy(mid);
    openModal({ title: "History Stok — " + m.nama, size: "xl", body: () => tbl(["No", "Date", "No. Transaksi", "Status", "Coil Count", "Qty Awal (Kg)", "Qty Transaksi (Kg)", "Qty Akhir (Kg)", "Total Harga", "Saldo Awal", "Saldo Akhir", "Harga Lama", "Harga Baru (Avg)", "Detail"],
      h.map((r, i) => `<tr><td>${i + 1}</td><td>${fdate(r.tgl)}</td><td>${esc(r.no)}</td><td>${lab(r.status)}</td><td class="ap-r">${r.coil}</td><td class="ap-r">${num(r.qty_awal)}</td><td class="ap-r">${num(r.qty_trx)}</td><td class="ap-r">${num(r.qty_akhir)}</td><td class="ap-r">${num(r.total_harga)}</td><td class="ap-r">${num(r.saldo_awal)}</td><td class="ap-r">${num(r.saldo_akhir)}</td><td class="ap-r">${num(r.harga_lama)}</td><td class="ap-r">${num(r.harga_baru)}</td><td>${btn("info", "trx", r.no, "Coil", ` data-mat="${mid}" data-n="${r.coil}"`)}</td></tr>`).join(""), "Belum ada transaksi.") +
      '<p class="note">Harga Baru (Avg) = Saldo Akhir ÷ Qty Akhir (rata-rata bergerak) untuk transaksi IN; transaksi OUT keluar pada harga rata-rata berjalan. Baris SALDO-AWAL / PENYESUAIAN dibuat otomatis (mockup) agar saldo akhir sama dengan stok saat ini; transaksi Request List yang dikonfirmasi ikut tercatat.</p>',
      act: { trx(d) {
        const cs = S().coils.filter((c) => c.mat === d.mat).slice(0, +d.n || 3);
        openModal({ title: "Detail Coil Transaksi — " + d.id, size: "lg", hdr: "info", body: () => tbl(["No", "No. Coil", "Kode Internal", "Net Weight (Kg)", "Gross Weight (Kg)", "Length (M)"], cs.map((c, i) => `<tr><td>${i + 1}</td><td>${esc(c.no_coil)}</td><td>${esc(c.kode_internal)}</td><td class="ap-r">${num(c.net_weight)}</td><td class="ap-r">${num(c.gross_weight)}</td><td class="ap-r">${num(c.length)}</td></tr>`).join(""), "Tidak ada coil.") + '<p class="note">Contoh: coil diambil dari stok material yang sama (drill-down modal-detail-coil-trx).</p>' });
      } } });
  }
  function coilModal(mid, kd) {
    const cs = S().coils.filter((c) => c.mat === mid && c.gudang === kd);
    openModal({ title: "Detail Coil — " + W.matBy(mid).nama, size: "lg", hdr: "info", body: () => tbl(["No", "Pack Code", "No. Coil", "Kode Internal", "Net Weight (Kg)", "Gross Weight (Kg)", "Length (M)"], cs.map((c, i) => `<tr><td>${i + 1}</td><td>${pill(c.pack)}</td><td>${esc(c.no_coil)}</td><td>${esc(c.kode_internal)}</td><td class="ap-r">${num(c.net_weight)}</td><td class="ap-r">${num(c.gross_weight)}</td><td class="ap-r">${num(c.length)}</td></tr>`).join(""), "Tidak ada coil.") });
  }
  const svTable = (rows, kd, grandLabel) => tbl(["No", "Material Code", "Nama Material", "Roll", "Qty Stock (Kg)", "Purchase Price (Avg)", "Total Value", "Action"],
    rows.map((r, i) => `<tr><td>${i + 1}</td><td>${esc(r.mat)}</td><td>${esc(mn(r.mat))}</td><td class="ap-r">${r.roll}</td><td class="ap-r">${num(r.qty)}</td><td class="ap-r">${num(r.harga)}</td><td class="ap-r">${num(r.total)}</td><td class="ap-act ap-act-nowrap">${btn("info", "hist", r.mat, "History")}${btn("light", "coil", r.mat, "Coil", ` data-kd="${kd}"`)}</td></tr>`).join(""), "Belum ada stok.",
    `<tr class="wh-foot"><td colspan="6" class="ap-r">${grandLabel}</td><td class="ap-r">${num(rows.reduce((a, r) => a + r.total, 0))}</td><td></td></tr>`);
  P.stockvalue = {
    title: "Stock Value", mod: "warehouse (stock_value)", role: "Gudang / Accounting", ui: { tab: "pro", hmat: "", hdate: "", hgud: "", hshown: false },
    hint: "Nilai persediaan coil per material: Qty Stock × Purchase Price (Avg) per gudang, riwayat transaksi, dan posisi per tanggal.",
    render() {
      const u = U.ui, tab = u.tab, nav = tabs([["pro", "Production Warehouse"], ["sli", "Slitting Warehouse"], ["hist", "History Per Days"]], tab);
      const all = svRows("PRO").concat(svRows("SLI")), tot = all.reduce((a, r) => a + r.total, 0);
      const top = `<div class="cl-sumrow">${stat(rp(tot), "Total nilai persediaan")}${stat(num(all.reduce((a, r) => a + r.qty, 0), 0) + " Kg", "Total Qty Stock")}${stat(all.reduce((a, r) => a + r.roll, 0), "Total roll")}</div>`;
      const xl = '<button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="xl">Download Excel</button>';
      if (tab === "hist") {
        let rows = [];
        if (u.hshown) ["PRO", "SLI"].filter((k) => !u.hgud || k === u.hgud).forEach((k) => svRows(k).forEach((r) => rows.push({ ...r, kd: k })));
        if (u.hmat) rows = rows.filter((r) => (r.mat + mn(r.mat)).toLowerCase().includes(u.hmat.toLowerCase()));
        return `${top}${nav}${card("History Per Days", `<div class="cl-tools"><input class="ap-in" data-u="hmat" placeholder="Search Material: kode / nama..." value="${esc(u.hmat)}"><label>As of Date : <input class="ap-in" type="date" data-u="hdate" value="${esc(u.hdate)}"></label>
          <label>Gudang : <select class="ap-sel ap-in" data-u="hgud"><option value="">-- All --</option><option value="PRO"${u.hgud === "PRO" ? " selected" : ""}>Production Warehouse</option><option value="SLI"${u.hgud === "SLI" ? " selected" : ""}>Slitting Warehouse</option></select></label>
          <button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="show">Show</button><button type="button" class="ap-btn ap-btn--sm ap-btn--secondary" data-act="reset">Reset</button>${u.hshown ? xl : ""}</div>
          <div class="wh-total">Grand Total: <strong>${u.hshown ? num(rows.reduce((a, r) => a + r.total, 0)) : "—"}</strong></div>` +
          tbl(["No", "Material Code", "Nama Material", "Warehouse", "Date", "Coil Count", "Qty Stock (Kg)", "Purchase Price (Avg)", "Total Value", "Action"],
            rows.map((r, i) => `<tr><td>${i + 1}</td><td>${esc(r.mat)}</td><td>${esc(mn(r.mat))}</td><td>${r.kd === "PRO" ? "Production" : "Slitting"}</td><td>${fdate(u.hdate)}</td><td class="ap-r">${r.roll}</td><td class="ap-r">${num(r.qty)}</td><td class="ap-r">${num(r.harga)}</td><td class="ap-r">${num(r.total)}</td><td>${btn("info", "hist", r.mat, "History")}</td></tr>`).join(""), u.hshown ? "Tidak ada data." : "Pilih tanggal lalu klik Show."))}`;
      }
      const kd = tab === "pro" ? "PRO" : "SLI";
      return `${top}${nav}${card(tab === "pro" ? "Production Warehouse" : "Slitting Warehouse", `<div class="cl-tools"><span style="margin-left:auto">${xl}</span></div>` + svTable(svRows(kd), kd, tab === "pro" ? "GRAND TOTAL PRODUKSI" : "GRAND TOTAL SLITTING"))}
        <p class="note">Total Value = Qty Stock (Kg) × Purchase Price (Avg). Purchase Price (Avg) adalah rata-rata bergerak per material (lihat History). Transaksi pindah gudang lewat Request List tidak mengubah nilai (hanya memindahkan posisi coil).</p>`;
    },
    act: {
      hist: (d) => histModal(d.id), coil: (d) => coilModal(d.id, d.kd), xl: () => excel("Stock Value"),
      show() { if (!U.ui.hdate) { warn("Please select a date."); return; } U.ui.hshown = true; U.render(); },
      reset() { Object.assign(U.ui, { hmat: "", hdate: "", hgud: "", hshown: false }); U.render(); },
    },
  };

  /* ======================= 4. Kartu Stok (warehouse/kartu_stok) ======================= */
  const booking = (mid) => S().spk.filter((s) => s.status === "Material On Load" && s.spkc).reduce((a, s) => a + S().coils.filter((c) => s.spkc.packs.includes(c.pack) && c.mat === mid).reduce((t, c) => t + c.net_weight, 0), 0);
  const trxType = (no) => (no.startsWith("GRN") ? "Penerimaan Barang (GRN)" : no.startsWith("SPKC") ? "Pengeluaran SPK Coil" : no.startsWith("UNPK") ? "Hasil Unpack Coil" : "Transaksi");
  P.kartu = {
    title: "Kartu Stok", mod: "warehouse (kartu_stok)", role: "Gudang / PPIC", ui: { mat: "", from: "", to: "" },
    hint: "Mutasi stok per material: saldo awal, transaksi masuk/keluar, saldo akhir, beserta booking (stok yang sudah dialokasikan ke SPK Coil).",
    render() {
      const u = U.ui, mids = u.mat ? [u.mat] : S().mats.map((m) => m.id);
      const rows = []; mids.forEach((mid) => { const h = histRows(mid); h.forEach((r, i) => rows.push({ mid, r, last: i === h.length - 1 })); });
      const l = rows.filter((x) => (!u.from || x.r.tgl >= u.from) && (!u.to || x.r.tgl <= u.to)).sort((a, b) => a.r.tgl.localeCompare(b.r.tgl));
      return card("Filter", `<div class="cl-tools"><label>Material : <select class="ap-sel ap-in" data-u="mat"><option value="">(Semua material)</option>${S().mats.map((m) => `<option value="${m.id}"${u.mat === m.id ? " selected" : ""}>${esc(m.id + " — " + m.nama)}</option>`).join("")}</select></label>
          <label>Dari : <input class="ap-in" type="date" data-u="from" value="${esc(u.from)}"></label><label>Sampai : <input class="ap-in" type="date" data-u="to" value="${esc(u.to)}"></label></div>`) +
        card("Kartu Stok", `<div class="ap-tablewrap"><table class="ap-table ap-table--striped ap-table--bordered"><thead><tr><th rowspan="2">#</th><th rowspan="2">Transaction Date</th><th rowspan="2">Transaction No.</th><th rowspan="2">Transaction Type</th><th rowspan="2">Product ID</th><th rowspan="2">Product</th><th colspan="3">AWAL</th><th colspan="2">TRANSAKSI</th><th colspan="3">AKHIR</th></tr>
          <tr><th>Stock</th><th>Booking</th><th>Free Stock</th><th>In/Out</th><th>Booking</th><th>Stock</th><th>Booking</th><th>Free Stock</th></tr></thead><tbody>${l.length ? l.map((x, i) => { const r = x.r, bk = x.last ? booking(x.mid) : 0, io = r.status === "IN" ? r.qty_trx : -r.qty_trx;
          return `<tr><td>${i + 1}</td><td>${fdate(r.tgl)}</td><td>${esc(r.no)}</td><td>${esc(trxType(r.no))}</td><td>${esc(x.mid)}</td><td>${esc(mn(x.mid))}</td><td class="ap-r">${num(r.qty_awal)}</td><td class="ap-r">${num(0)}</td><td class="ap-r">${num(r.qty_awal)}</td><td class="ap-r">${io > 0 ? "+" : ""}${num(io)}</td><td class="ap-r">${num(0)}</td><td class="ap-r">${num(r.qty_akhir)}</td><td class="ap-r">${num(bk)}</td><td class="ap-r">${num(r.qty_akhir - bk)}</td></tr>`; }).join("") : '<tr><td class="ap-empty" colspan="14">Tidak ada transaksi.</td></tr>'}</tbody></table></div>
          <p class="note">Satuan Kg. Booking pada baris terakhir = berat pack yang sudah dipilih di SPK Coil berstatus Material On Load tetapi belum dikonfirmasi; Free Stock = Stock − Booking. Rumus booking per baris riwayat perlu dikonfirmasi ke programmer (halaman asli memuat data via server-side grid).</p>`);
    },
    act: {},
  };
})();
