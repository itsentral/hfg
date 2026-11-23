/* ============================================================
   wh-pages2.js (bagian B) — SPK Material, Request List (SPK Coil), Stock From Warehouse, Unpack Coil.
   Acuan modul aplikasi: spk_material, request_list, stock_from_warehouse, unpack_coil (hanya dibaca).
   Status SPK dan alur: Material Requested -> Material On Load -> Material Confirmed -> Released | Cancelled.
   ============================================================ */
(() => {
  "use strict";
  const U = window.WHUI, W = U.W, S = () => W.state, P = window.WHP;
  const { $, esc, num, fdate, lab, btn, tbl, card, stat, kv, row, inp, sel, txt, isNumEl, pv, tabs, toast, warn, dialog, done, confirmThen, openModal, closeModal, printDoc, ppHead, ppMeta, ppSign } = U;
  const mn = (id) => W.matBy(id).nama || id;
  const pill = (c) => `<span class="wh-pack${c.startsWith("WIP-") ? " wh-pack--prt" : ""}">${esc(c)}</span>`;
  const excel = (what) => toast(`Download Excel "${what}" (mockup, tidak membuat berkas nyata).`);
  const menu = (items) => `<div class="wh-menu"><button type="button" class="ap-btn ap-btn--sm ap-btn--light" data-act="menu" style="border:1px solid #dee2e6">&#8942;</button><div class="wh-menu__list" hidden>${items.join("")}</div></div>`;
  const mi = (act, id, label) => (label === "-" ? "<hr>" : `<a data-act="${act}" data-id="${esc(id)}">${label}</a>`);
  const sumNet = (a) => a.reduce((t, c) => t + c.net_weight, 0);
  const SPK = (no) => S().spk.find((s) => s.spk_no === no);

  /* paket bantu: info pack (coil biasa atau "pack WIP" = satu coil sisa) */
  function packInfo(code) {
    if (code.startsWith("WIP-")) {
      const x = S().sfw.find((s) => s.kd !== "HLD" && "WIP-" + s.kode_internal === code); if (!x) return null;
      return { code, gudang: "WIP", roll: 1, nw: x.net_weight, gw: x.gross_weight, mats: [x.mat], coils: [], wip: x };
    }
    return W.packs().find((p) => p.code === code) || null;
  }
  const wipPacks = () => S().sfw.filter((s) => s.kd === "WIP").map((x) => packInfo("WIP-" + x.kode_internal));
  const inOtherSpkc = (code, self) => S().spk.some((s) => s.spk_no !== self && s.spkc && ["Material On Load", "Material Confirmed"].includes(s.status) && s.spkc.packs.includes(code));
  const bomOf = (d) => W.prodBy(d.id_produk_fg).bom.map(([mid, ratio]) => ({ mid, need: W.round2(d.target_qty * d.berat_per_unit * ratio) }));
  const needsOf = (spk) => { const m = {}; spk.detail.forEach((d) => bomOf(d).forEach((b) => { m[b.mid] = (m[b.mid] || 0) + b.need; })); return Object.entries(m).map(([mid, need]) => ({ mid, need: W.round2(need) })); };
  const stokWip = (mid) => sumNet(S().sfw.filter((s) => s.kd === "WIP" && s.mat === mid));
  const stokPro = (mid) => sumNet(S().coils.filter((c) => c.gudang === "PRO" && c.mat === mid));
  const packAvail = (mid) => W.packs("PRO").filter((p) => p.mats.includes(mid)).length;
  const spkHeaderKv = (s) => kv([["SPK No", esc(s.spk_no)], ["Tgl SPK", fdate(s.tgl_spk)], ["Due Date", fdate(s.due_date)], ["Shift Names", esc(s.shift_names.join(", "))], ["Catatan", esc(s.catatan || "-")], ["Status", lab(s.status)]]);
  const prodTable = (s) => tbl(["No", "Id Produk FG", "Nama Produk FG", "Target Qty", "Berat Per Unit (Kg)", "Total Weight (Kg)"], s.detail.map((d, i) => `<tr><td>${i + 1}</td><td>${esc(d.id_produk_fg)}</td><td>${esc(d.nm_produk_fg)}</td><td class="ap-r">${num(d.target_qty, 0)}</td><td class="ap-r">${num(d.berat_per_unit, 4)}</td><td class="ap-r">${num(d.total_weight)}</td></tr>`).join(""), "",
    `<tr class="wh-foot"><td colspan="5" class="ap-r">Total</td><td class="ap-r">${num(s.detail.reduce((a, d) => a + d.total_weight, 0))}</td></tr>`);

  /* ======================= 5. SPK Material (spk_material) ======================= */
  let ff = null;                                              // state form SPK
  const newDetail = () => ({ id_produk_fg: "", target_qty: 0 });
  const dOf = (x) => { const p = W.prodBy(x.id_produk_fg); return p ? { id_produk_fg: p.id, nm_produk_fg: p.nm, target_qty: Number(x.target_qty) || 0, berat_per_unit: p.berat, total_weight: W.round2((Number(x.target_qty) || 0) * p.berat) } : null; };
  function bomModal(x) {
    const d = dOf(x); if (!d) { warn("Pilih produk terlebih dahulu."); return; }
    openModal({ title: "Detail Material (BOM) — " + d.nm_produk_fg, size: "lg", hdr: "info", body: () => `<p>Target Qty <b>${num(d.target_qty, 0)}</b> × Berat Per Unit <b>${num(d.berat_per_unit, 4)} Kg</b></p>` + tbl(["Nama Material", "Berat Dibutuhkan", "Stok WIP", "Stok Gudang Produksi", "Pack Available"],
      bomOf(d).map((b) => { const cukup = stokWip(b.mid) + stokPro(b.mid) >= b.need; return `<tr><td>${esc(mn(b.mid))}</td><td class="ap-r">${num(b.need)} Kg</td><td class="ap-r">${num(stokWip(b.mid))} Kg</td><td class="ap-r">${num(stokPro(b.mid))} Kg ${cukup ? "" : '<span class="ap-label ap-label--danger">Kurang</span>'}</td><td class="ap-r">${packAvail(b.mid)}</td></tr>`; }).join(""), "BOM belum tersedia.") + '<p class="note">Stok dibaca dari gudang: WIP (coil sisa) dan Production Warehouse. Berat Dibutuhkan = Target Qty × Berat Per Unit × rasio BOM material.</p>' });
  }
  function printSpk(s) {
    const blank = (n, cols) => Array.from({ length: n }, (_, i) => `<tr><td>${cols[0] === "#" ? i + 1 : "&nbsp;"}</td>${cols.slice(1).map(() => "<td>&nbsp;</td>").join("")}</tr>`).join("");
    const tb = (heads, body) => `<table class="pp-tbl"><thead><tr>${heads.map((h) => `<th>${h}</th>`).join("")}</tr></thead><tbody>${body}</tbody></table>`;
    const sec = (t) => `<div style="background:#e9ecef;font-weight:700;padding:3px 8px;margin:10px 0 4px;font-size:12px">${t}</div>`;
    const brk = '<div style="page-break-before:always"></div>';
    const p1 = ppHead("SPK PRODUKSI & MATERIAL", s.spk_no) + `<div style="float:right;text-align:center;font-size:11px">${U.qrSvg(s.spk_no).replace("<svg", '<svg style="width:90px;height:90px"')}<br>${esc(s.spk_no)}</div>` +
      ppMeta([["No. SPK", `<b>${esc(s.spk_no)}</b>`], ["Tanggal SPK", fdate(s.tgl_spk)], ["Due Date", fdate(s.due_date)], ["Shift", esc(s.shift_names.join(", ") || "-")], ["Status", `<b>${esc(s.status.toUpperCase())}</b>`], ["Catatan", esc(s.catatan || "-")]]) +
      s.detail.map((d, i) => `${sec(`${i + 1}. ${esc(d.nm_produk_fg)}`)}<table class="pp-meta"><tr><td class="pp-k">Target Qty</td><td><b>${num(d.target_qty, 0)} Pcs</b></td><td class="pp-k">Total Weight</td><td><b>${num(d.total_weight)} Kg</b></td></tr></table>`).join("") +
      `<p style="font-size:10px;color:#718096;font-style:italic">Dicetak pada: ${fdate(W.today())}</p>` + ppSign(["Dibuat oleh, Budi S."]);
    const p2 = brk + '<h3 style="text-align:center">FORM SURAT PERINTAH KERJA (SPK) &amp; LAPORAN PRODUKSI HARIAN</h3>' +
      ppMeta([["No. SPK", `<b>${esc(s.spk_no)}</b>`], ["Setter Name", "........................"], ["No. Dokumen", "........................"], ["Tgl", "........................"], ["Helper Name", "........................"], ["Revisi", "........................"], ["Mesin", "........................"], ["Jam Mulai s/d Selesai", "........................"], ["Tgl Efektif", "........................"]]) +
      sec("1. PEMAKAIAN MATERIAL (BABY COIL - DARI UNPACK / WIP / HOLD)") + tb(["No", "Sumber Coil<br>(Unpack/WIP/Hold)", "No. Pack", "Nama Material", "No. Baby Coil", "Berat Bersih (Net Kg)", "Kondisi / Keterangan"], blank(5, ["#", 1, 1, 1, 1, 1, 1])) +
      sec("2. HASIL PRODUKSI (FINISH GOOD KW 1) - SPK &amp; STOK BEBAS") + tb(["No", "Jenis Produksi", "Nama Produk", "Asal Baby Coil", "Qty (Pcs)", "Berat Total (Kg)", "Berat / Pcs (Kg) (Opsional)"], blank(5, ["#", 1, 1, 1, 1, 1, 1])) +
      sec("3. HASIL PRODUKSI NON-STANDARD (KW 2)") + tb(["No", "Kategori (Internal / Supplier)", "Nama KW 2", "Size (m)", "Qty KW 2", "Berat Total (Kg)", "Keterangan (Contoh: White Rust)"], blank(3, ["#", 1, 1, 1, 1, 1, 1])) +
      sec("4. SISA COIL &amp; HOLD COIL") + tb(["Kategori", "No. Baby Coil", "Berat (Kg)"], blank(4, [1, 1, 1]) + '<tr><td colspan="3" style="font-style:italic;font-size:10px">*Tuliskan keterangan detail di baliknya jika perlu</td></tr>') +
      sec("5. KOMPONEN SCRAP &amp; REJECT") + tb(["Kategori Scrap", "Berat (Kg)"], ["Tong Coil", "Wrapping", "Potongan Pisau", "Reject Produk (Internal)", "Reject Produk (Supplier)", "Reject Material (Internal)", "Reject Material (Supplier)"].map((x) => `<tr><td>${x}</td><td>&nbsp;</td></tr>`).join("")) +
      `<div class="pp-sign">${["Dibuat Oleh (Operator / Helper)", "Diperiksa Oleh (Leader / SPV Produksi)", "Diketahui Oleh (QC / Admin)"].map((n) => `<div><div>${n}</div><div class="ln">(_________________________)</div></div>`).join("")}</div>`;
    const SOP = [["HEADER & IDENTITAS", "No. SPK, Tgl Produksi, Mesin", "Diisi sesuai dengan Surat Perintah Kerja (SPK) yang diterima dari PPIC / Admin. Harus jelas dan tidak boleh dikosongkan."], ["HEADER & IDENTITAS", "Nama Setter & Helper, Jam", "Diisi dengan nama petugas aktual yang menjalankan mesin. Jam mulai diisi saat mesin setup/running, jam selesai diisi saat batch produksi selesai."], ["1. PEMAKAIAN MATERIAL", "Sumber Coil (Unpack/WIP/Hold)", "Tulis atau beri tanda centang asal material: Unpack, WIP, atau Hold."], ["1. PEMAKAIAN MATERIAL", "No. Pack & No. Baby Coil", "WAJIB diisi sesuai label/barcode fisik pada material (traceability di ERP)."], ["1. PEMAKAIAN MATERIAL", "Berat Bersih (Net Kg)", "Diisi berat aktual hasil timbang material saat akan dimasukkan ke mesin."], ["2. HASIL PRODUKSI (KW 1)", "Jenis Produksi", "'Sesuai SPK' untuk memenuhi SPK; 'Stok Bebas' bila sisa material dijadikan produk lain di luar SPK."], ["2. HASIL PRODUKSI (KW 1)", "Qty (Pcs) & Berat Total (Kg)", "Hitung jumlah pcs, lalu timbang berat total. ERP menghitung persentase selisih terhadap standar."], ["3. HASIL PRODUKSI (KW 2)", "Kategori (Internal / Supplier)", "Internal: cacat akibat proses pabrik. Supplier: cacat bawaan bahan baku."], ["3. HASIL PRODUKSI (KW 2)", "Keterangan", "Wajib menuliskan alasan KW 2, mis. 'White Rust', 'Penyok'."], ["4. SISA COIL & HOLD COIL", "No. Baby Coil & Berat (Kg)", "Timbang material sisa / Hold dan tulis No. Baby Coil asalnya."], ["5. KOMPONEN SCRAP / REJECT", "Tong, Wrapping, Pisau, Reject", "Kumpulkan dan timbang semua scrap/reject di akhir shift."], ["6. APPROVAL / PENGESAHAN", "Tanda Tangan", "Ditandatangani Operator, diverifikasi Leader/SPV, divalidasi QC/Admin."]];
    const p3 = brk + '<h3 style="text-align:center">INSTRUKSI KERJA (SOP) PENGISIAN FORM SPK &amp; LAPORAN PRODUKSI</h3><p style="font-size:11px">Tujuan: Memastikan Operator/Helper dapat mengisi form produksi dengan akurat sehingga data valid saat diinput Admin ke Sistem ERP. (Teks SOP diringkas di mockup.)</p>' +
      tb(["No", "Bagian Form", "Item / Kolom", "Instruksi Pengisian (Cara Pengisian &amp; Keterangan)"], SOP.map((r, i) => `<tr><td>${i + 1}</td><td>${esc(r[0])}</td><td>${esc(r[1])}</td><td>${esc(r[2])}</td></tr>`).join(""));
    printDoc("SPK Material", p1 + p2 + p3);
  }

  /* Aturan tab dan badge mengikuti spk_material (Spk_material_model::get_spk_list_filtered / get_status_counts) */
  const WAIT3 = ["Material Requested", "Material On Load", "Material Confirmed"];
  const inTab = (s, tab) => (tab === "waiting" ? WAIT3.includes(s.status) : tab === "released" ? s.status === "Released" && s.pr !== "final" : tab === "closed" ? s.status === "Released" && s.pr === "final" : WAIT3.concat("Released").includes(s.status));
  const prBadge = (s) => (s.pr === "final" ? '<div><span class="ap-label ap-label--dark" title="Laporan Produksi Selesai (Final)">PR Closed</span></div>' : s.pr === "draft" ? '<div><span class="ap-label ap-label--warning" title="Laporan Produksi Draft (Sedang Berjalan)">PR In Progress</span></div>' : "");
  const dlg = (text) => dialog({ icon: "warning", title: "Perhatian", text });
  const field2 = (label, html, req) => `<div class="pr-f"><label class="ap-lbl">${label}${req ? ' <span class="ap-req">*</span>' : ""}</label>${html}</div>`;
  P.spk = {
    title: "SPK Material", mod: "spk_material", role: "PPIC / Produksi", ui: { mode: "list", id: null, tab: "waiting", q: "" },
    hint: "Surat Perintah Kerja material: buat SPK (produk FG + target qty), pantau status, Release ke Warehouse, dan cetak SPK.",
    render() {
      const u = U.ui;
      if (u.mode === "form") return formView();
      if (u.mode === "view") {
        const s = SPK(u.id);
        return card("Detail SPK Material", `<div class="wh-grid2"><div>${kv([["SPK No", esc(s.spk_no)], ["Tgl SPK", fdate(s.tgl_spk)], ["Due Date", fdate(s.due_date)], ["Shift Names", esc(s.shift_names.join(", ") || "-")], ["Status", lab(s.status)]])}</div><div>${kv([["Catatan", esc(s.catatan || "-")], ["Created By", "Budi S."], ["Created At", fdate(s.tgl_spk) + " 08:00"]])}</div></div>` +
          '<div class="wh-sec">Detail Produk</div>' + tbl(["Urut", "Nama Produk FG", "Target Qty", "Total Weight (Kg)"], s.detail.map((d, i) => `<tr><td>${i + 1}</td><td>${esc(d.nm_produk_fg)}</td><td class="ap-r">${num(d.target_qty, 0)}</td><td class="ap-r">${num(d.total_weight)}</td></tr>`).join(""), "Tidak ada detail produk.") +
          `<div class="cl-tools" style="margin-top:12px"><button type="button" class="ap-btn ap-btn--outline-warning" data-act="back">&#8592; Kembali</button>${btn("secondary", "pdf", s.spk_no, "Print PDF")}</div>`);
      }
      const all = S().spk, cnt = (t) => all.filter((s) => inTab(s, t)).length, q = (u.q || "").toLowerCase();
      const l = all.filter((s) => inTab(s, u.tab) && (!q || (s.spk_no + " " + s.shift_names.join(", ") + " " + s.status).toLowerCase().includes(q))).sort((a, b) => b.tgl_spk.localeCompare(a.tgl_spk) || b.spk_no.localeCompare(a.spk_no));
      const tabBtn = (id, label) => `<a class="${u.tab === id ? "is-on" : "is-off"}" data-act="tab" data-id="${id}" data-key="tab">${label} <span class="wh-tabs-count">${cnt(id)}</span></a>`;
      return card("Daftar SPK Material", `<div class="cl-tabs">${tabBtn("waiting", "Menunggu Rilis")}${tabBtn("released", "Released")}${tabBtn("closed", "Closed Produksi")}${tabBtn("all", "Semua SPK")}</div>
          <div class="cl-tools"><input class="ap-in" data-u="q" placeholder="Search..." value="${esc(u.q)}"><span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="add">+ Create SPK</button></span></div>` +
        tbl(["No", "SPK No", "Tgl SPK", "Shift Names", "Status", "Jumlah Produk", "Actions"], l.map((s, i) => `<tr><td>${i + 1}</td><td>${esc(s.spk_no)}</td><td>${fdate(s.tgl_spk)}</td><td>${esc(s.shift_names.join(", "))}</td><td style="text-align:center">${lab(s.status)}${prBadge(s)}</td><td style="text-align:center">${s.detail.length}</td>
          <td class="ap-act ap-act-nowrap">${btn("info", "view", s.spk_no, "View")}${btn("secondary", "pdf", s.spk_no, "Print PDF")}${WAIT3.includes(s.status) ? btn("success", "release", s.spk_no, "Release") : ""}</td></tr>`).join(""), "No data available in table")) +
        '<p class="note">Tab: Menunggu Rilis = Material Requested / On Load / Confirmed; Released = sudah Released tanpa laporan produksi final; Closed Produksi = Released dan laporan produksi sudah final. Status Material On Load dan Material Confirmed berubah lewat Request List. Release bisa dilakukan dari status mana pun di tab Menunggu Rilis (Spk_material_model::release_spk). Badge PR berasal dari modul Laporan Produksi.</p>';
    },
    onField(el) {
      const k = el.dataset.f, i = +el.dataset.i;
      if (k === "shift") ff.shifts = [...document.querySelectorAll('#whBody [data-f="shift"]')].filter((c) => c.checked).map((c) => c.value);
      else if (k === "prod") { ff.detail[i].id_produk_fg = el.value; U.render(); }
      else if (k === "qty") { ff.detail[i].target_qty = pv(el) || 0; const d = dOf(ff.detail[i]), c = $("#tw" + i); if (c) c.textContent = d ? num(d.total_weight) : "0,00"; }
      else ff[k] = el.value;
    },
    act: {
      back() { U.ui.mode = "list"; U.render(); }, cancel() { U.ui.mode = "list"; U.render(); },
      view(d) { U.ui.mode = "view"; U.ui.id = d.id; U.render(); },
      pdf: (d) => printSpk(SPK(d.id)),
      release(d) {
        confirmThen({ icon: "question", title: "Release SPK " + d.id + "?", text: "Setelah berstatus Released, SPK ini akan diteruskan ke Warehouse (Request List) dan dapat dibuat Laporan Produksi.", ok: "Ya, Release SPK", cancel: "Batal" }, () => {
          const s = SPK(d.id); s.status = "Released"; W.save(); U.render(); done("SPK " + s.spk_no + " berhasil di-Release.");
        });
      },
      add() { ff = { id: null, tgl_spk: W.today(), due_date: "", shifts: [], catatan: "", detail: [newDetail()] }; U.ui.mode = "form"; U.render(); },
      addrow() { if (ff.detail.length >= 100) { dlg("Maksimal 100 baris produk dalam satu SPK."); return; } ff.detail.push(newDetail()); U.render(); },
      delrow(d) { if (ff.detail.length === 1) { dlg("Minimal harus ada 1 baris produk."); return; } ff.detail.splice(+d.id, 1); U.render(); },
      save() {
        const bad = (m) => dialog({ icon: "error", title: "Validasi Gagal", text: "Periksa form dan lengkapi data yang diperlukan. (" + m + ")" });
        if (!ff.tgl_spk) { bad("Tanggal SPK wajib diisi"); return; }
        if (!ff.shifts.length) { bad("Shift wajib dipilih minimal satu"); return; }
        const det = ff.detail.map(dOf);
        for (let i = 0; i < det.length; i++) {
          if (!det[i]) { bad(`Baris ${i + 1}: Produk harus dipilih`); return; }
          if (det[i].target_qty < 1 || det[i].target_qty > 999999) { bad(`Baris ${i + 1}: Target Qty harus antara 1 - 999.999`); return; }
        }
        if (new Set(det.map((x) => x.id_produk_fg)).size !== det.length) { bad("Produk ini sudah dipilih di baris lain"); return; }
        confirmThen({ icon: "question", title: "Konfirmasi", text: "Apakah Anda yakin ingin membuat SPK ini?", ok: "Ya", cancel: "Batal" }, () => {
          S().spk.push({ spk_no: W.nextNo("spk"), tgl_spk: ff.tgl_spk, due_date: ff.due_date, shift_names: ff.shifts, catatan: ff.catatan, status: "Material Requested", detail: det, spkc: null, pr: null });
          W.save(); U.ui.mode = "list"; U.ui.tab = "waiting"; U.render(); toast("SPK berhasil dibuat.", "ok");
        });
      },
    },
  };
  function formView() {
    const rows = ff.detail.map((x, i) => { const d = dOf(x);
      return `<div class="pr-fg"><div class="pr-fg__h"><b>Produk ${i + 1}</b>${btn("danger", "delrow", i, "&#10005;", ' title="Hapus baris"')}</div><div class="pr-grid4" style="grid-template-columns:5fr 2fr 2fr 3fr">
        <div class="pr-f"><label class="ap-lbl">Nama Produk FG <span class="ap-req">*</span></label><select class="ap-sel ap-in" data-f="prod" data-i="${i}"><option value="">-- Pilih Produk --</option>${W.PRODUK.filter((p) => p.aktif || p.id === x.id_produk_fg).map((p) => `<option value="${p.id}"${x.id_produk_fg === p.id ? " selected" : ""}>${esc(p.nm)}</option>`).join("")}</select></div>
        <div class="pr-f"><label class="ap-lbl">Target Qty <span class="ap-req">*</span></label><input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="qty" data-i="${i}" value="${esc(x.target_qty ? NumFmt.fmt(x.target_qty) : "")}" placeholder="0"></div>
        <div class="pr-f"><label class="ap-lbl">Berat Per Unit (Kg)</label><div class="ap-in ap-in--ro ap-in--num">${d ? num(d.berat_per_unit) : "0,00"}</div></div>
        <div class="pr-f"><label class="ap-lbl">Total Weight (Kg)</label><div class="ap-in ap-in--ro ap-in--num" id="tw${i}">${d ? num(d.total_weight) : "0,00"}</div></div></div></div>`; }).join("");
    return card("Create SPK Material", `<div class="pr-grid4">${field2("Tgl SPK", `<input class="ap-in" type="date" data-f="tgl_spk" value="${esc(ff.tgl_spk)}">`, 1)}${field2("Due Date", `<input class="ap-in" type="date" data-f="due_date" value="${esc(ff.due_date)}">`)}
      ${field2("Shift Names", W.SHIFT.map((s) => `<label style="display:block;font-weight:400"><input type="checkbox" data-f="shift" value="${esc(s)}"${ff.shifts.includes(s) ? " checked" : ""}> ${esc(s)}</label>`).join(""), 1)}${field2("Catatan", txt("catatan", ff.catatan, { ph: "Catatan tambahan (opsional)", rows: 2 }))}</div>
      <div class="pr-sub-h" style="margin-top:14px"><h6>Daftar Produk</h6><button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="addrow">+ Add More Product</button></div>${rows}
      <div class="cl-tools" style="margin-top:10px;justify-content:flex-end"><button type="button" class="ap-btn ap-btn--secondary" data-act="cancel">&#8592; Kembali</button><button type="button" class="ap-btn ap-btn--primary" data-act="save">Create SPK</button></div>`);
  }

  /* ======================= 6. Request List (request_list) ======================= */
  // Alur aplikasi: scan / ketik No. Pack di daftar SPK -> popup pilih No. SPK -> pack pindah gudang PRO -> PRT.
  // Status SPK tidak berubah oleh scan (Request_list_model::confirm_scan_pack_to_prt). Release dilakukan di SPK Material.
  const scanCount = (s) => new Set(S().scanlog.filter((x) => x.spk_no === s.spk_no).map((x) => x.pack_code)).size;
  const sfx = (n) => (n ? ` <span class="ap-label ap-label--light" style="color:#0d6efd;border-color:#0d6efd" title="Jumlah pack sudah di-scan ke PRT">${n} Pack PRT</span>` : "");
  const RLBADGE = { "Material Requested": "warning", "Material On Load": "info", "Material Confirmed": "success", Released: "success", Cancelled: "danger" };
  const rlStatus = (s) => `<span class="ap-label ap-label--${RLBADGE[s.status] || "dark"}">${esc(s.status)}</span>${sfx(scanCount(s))}${prBadge(s)}`;
  const rlProdukSummary = (s) => s.detail.map((d) => d.nm_produk_fg + " (" + d.target_qty + " pcs)").join(", ");
  function scanModal(chk) {
    const coils = chk.coils, tn = coils.reduce((a, c) => a + c.net_weight, 0), st = { spk: "" };
    openModal({
      title: "Konfirmasi Scan Pack & Pilih No. SPK", size: "lg", ok: "Pindahkan Pack ke PRT", okCls: "success",
      body: () => `<div class="wh-grid3" style="margin-bottom:10px"><div><small class="ap-hint">Pack Code</small><div><span class="wh-pack">${esc(chk.pack)}</span></div></div><div><small class="ap-hint">Asal &rarr; Tujuan Gudang</small><div><b>PRO</b> &rarr; <b style="color:#198754">PRT (Production 2)</b></div></div><div><small class="ap-hint">Ringkasan Isi Pack</small><div><b>${coils.length} Coil | ${num(tn)} Kg</b></div></div></div>
        <div class="ap-lbl" style="font-weight:700;font-size:12px;text-transform:uppercase;color:#6c757d">Detail Coil dalam Pack</div>` +
        tbl(["No", "Nama Material", "No. Coil", "Kode Internal", "Net Weight (Kg)"], coils.map((c, i) => `<tr><td>${i + 1}</td><td><b>${esc(mn(c.mat))}</b><br><small class="ap-hint">${esc(c.mat)}</small></td><td>${esc(c.no_coil)}</td><td><code>${esc(c.kode_internal)}</code></td><td class="ap-r">${num(c.net_weight)}</td></tr>`).join(""), "Tidak ada coil.") +
        `<div class="pr-f" style="margin-top:10px"><label class="ap-lbl"><b>Pilih No. SPK Tujuan (Acuan No. Transaksi Kartu Material)</b> <span class="ap-req">*</span></label>
        <select class="ap-sel ap-in" data-f="spk"><option value="">-- Pilih No. SPK --</option>${W.scannableSpk().sort((a, b) => b.tgl_spk.localeCompare(a.tgl_spk) || b.spk_no.localeCompare(a.spk_no)).map((s) => `<option value="${esc(s.spk_no)}"${st.spk === s.spk_no ? " selected" : ""}>${esc(s.spk_no + " (" + s.status + " | " + s.tgl_spk + ") - " + rlProdukSummary(s))}</option>`).join("")}</select></div>`,
      onField(el) { st.spk = el.value; },
      onOk() {
        if (!st.spk) { dialog({ icon: "warning", title: "Perhatian", text: "Silakan pilih No. SPK terlebih dahulu." }); return; }
        const r = W.scanMove(chk.pack, st.spk);
        if (!r.ok) { dialog({ icon: "error", title: "Gagal Scan", text: r.message }); return; }
        closeModal(); U.ui.scan = ""; U.render(); toast(r.message, "ok");
      },
    });
  }
  function rlView(s) {
    const hist = S().scanlog.filter((x) => x.spk_no === s.spk_no), tn = hist.reduce((a, h) => a + h.net_weight, 0);
    return card("Detail SPK Material", `<div class="wh-grid2"><div>${kv([["No. SPK", `<span class="wh-pack" style="background:#212529">${esc(s.spk_no)}</span>`], ["Tanggal SPK", fdate(s.tgl_spk)], ["Due Date", fdate(s.due_date)], ["Shift", esc(s.shift_names.join(", ") || "-")]])}</div><div>${kv([["Status", lab(s.status)], ["Dibuat Oleh", "Budi S."], ["Catatan", esc(s.catatan || "-")]])}</div></div>`,
      `<a class="ap-btn ap-btn--sm ap-btn--secondary" data-act="back">&#8592; Kembali</a>`) +
      card(`Detail Produk (${s.detail.length} item)`, tbl(["Urut", "Nama Produk FG", "Target Qty", "Total Weight (Kg)"], s.detail.map((d, i) => `<tr><td>${i + 1}</td><td>${esc(d.nm_produk_fg)}</td><td class="ap-r">${num(d.target_qty, 0)}</td><td class="ap-r">${num(d.total_weight)}</td></tr>`).join(""), "Belum ada detail produk.")) +
      card(`Riwayat Scan Pack ke WRH. Production 2 (${hist.length} coil)`, tbl(["No", "Pack Code", "Nama Material", "No. Coil", "Kode Internal", "Net Weight (Kg)", "Waktu Scan", "Oleh"],
        hist.map((h, i) => `<tr><td>${i + 1}</td><td>${pill(h.pack_code)}</td><td>${esc(mn(h.mat))}<br><small class="ap-hint">${esc(h.mat)}</small></td><td>${esc(h.no_coil)}</td><td><code>${esc(h.kode_internal)}</code></td><td class="ap-r">${num(h.net_weight)}</td><td>${esc(h.waktu)}</td><td>${esc(h.oleh)}</td></tr>`).join(""), "Belum ada pack yang di-scan ke WRH. Production 2 untuk SPK ini.",
        hist.length ? `<tr class="wh-foot"><td colspan="5" class="ap-r">Total</td><td class="ap-r">${num(tn)}</td><td colspan="2"></td></tr>` : ""), '<span class="ap-label ap-label--light">Read-Only</span>') +
      `<div class="cl-tools"><a class="ap-btn ap-btn--secondary" data-act="pdf" data-id="${esc(s.spk_no)}">Print SPK</a></div>`;
  }
  P.request = {
    title: "Request List", mod: "request_list", role: "Gudang / PPIC", ui: { mode: "list", id: null, tab: "waiting", q: "", scan: "" },
    hint: "Daftar SPK untuk gudang: scan No. Pack dari WRH Production 1 (PRO), pilih SPK tujuan, lalu pack dipindahkan ke WRH Production 2 (PRT).",
    render() {
      const u = U.ui;
      if (u.mode === "view") return rlView(SPK(u.id));
      const all = S().spk, cnt = (t) => all.filter((s) => inTab(s, t)).length, q = (u.q || "").toLowerCase();
      const l = all.filter((s) => inTab(s, u.tab) && (!q || (s.spk_no + " " + s.shift_names.join(", ") + " " + s.status).toLowerCase().includes(q))).sort((a, b) => b.tgl_spk.localeCompare(a.tgl_spk) || b.spk_no.localeCompare(a.spk_no));
      const tabBtn = (id, label) => `<a class="${u.tab === id ? "is-on" : "is-off"}" data-act="tab" data-id="${id}" data-key="tab">${label} <span class="wh-tabs-count">${cnt(id)}</span></a>`;
      return card("Request List", `<div class="cl-tools" style="justify-content:space-between;border-bottom:1px solid #dee2e6;padding-bottom:8px"><span class="cl-tabs" style="border:0;margin:0">${tabBtn("waiting", "Menunggu Rilis")}${tabBtn("released", "Released")}${tabBtn("closed", "Closed Produksi")}${tabBtn("all", "Semua SPK")}</span>
          <span style="display:flex;gap:4px"><input class="ap-in" data-enter="scan" data-f="scan" style="min-width:240px;font-weight:600" placeholder="Scan / Ketik No. Pack..." value="${esc(u.scan)}" autocomplete="off"><button type="button" class="ap-btn ap-btn--primary" data-act="scan">Scan</button><button type="button" class="ap-btn ap-btn--secondary" data-act="camera" title="Scan dengan Kamera">&#128247;</button></span></div>
          <div class="cl-tools"><input class="ap-in" data-u="q" placeholder="Search..." value="${esc(u.q)}"></div>` +
        tbl(["No", "SPK No", "Tgl SPK", "Shift Names", "Status", "Jumlah Produk", "Aksi"], l.map((s, i) => `<tr><td>${i + 1}</td><td>${esc(s.spk_no)}</td><td>${fdate(s.tgl_spk)}</td><td>${esc(s.shift_names.join(", "))}</td><td style="text-align:center">${rlStatus(s)}</td><td style="text-align:center">${s.detail.length}</td>
          <td class="ap-act ap-act-nowrap">${btn("info", "view", s.spk_no, "View Detail")}${btn("secondary", "pdf", s.spk_no, "Print SPK")}</td></tr>`).join(""), "No data available in table")) +
        '<p class="note">Scan hanya menerima pack aktif dari gudang PRO; pack di PRT / gudang lain ditolak. SPK tujuan = SPK selain Cancelled yang belum punya Laporan Produksi final; scan tidak mengubah status SPK. Pack yang sudah di PRT bisa di-Unpack lewat menu Unpack Coil. Mockup tidak memakai kamera sungguhan; ketik No. Pack lalu Enter / Scan (mis. PCK-2611-001, PCK-2611-010 dari PRO; PCK-2611-004 sudah di PRT; PCK-2611-006 di SLI).</p>';
    },
    onField(el) { if (el.dataset.f === "scan") U.ui.scan = el.value; },
    act: {
      back() { U.ui.mode = "list"; U.render(); }, view(d) { U.ui.mode = "view"; U.ui.id = d.id; U.render(); }, pdf: (d) => printSpk(SPK(d.id)),
      camera() { dialog({ icon: "warning", title: "Akses Kamera Membutuhkan HTTPS", text: "Browser HP/tablet memblokir kamera pada koneksi HTTP non-localhost. Gunakan HTTPS atau input manual / scanner Bluetooth." }); },
      scan() {
        const code = (U.ui.scan || "").trim(); if (!code) return;
        const r = W.scanCheck(code);
        if (!r.ok) { dialog({ icon: "error", title: "Scan Pack Ditolak", text: r.message }); return; }
        scanModal(r);
      },
    },
  };

  /* ======================= 7. Stock From Warehouse (stock_from_warehouse) ======================= */
  // Mengikuti Stock_from_warehouse_model: 4 tab (WRH. Production 2, WIP (Coil Remains), On Hold, History Per Days),
  // aksi "Kartu Pemakaian" per coil di tab Production 2, tab On Hold berbasis Claim Material, tanpa kartu statistik.
  const n3 = (n) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: 3, maximumFractionDigits: 3 });
  const SFWTAB = [["prt", "WRH. Production 2"], ["wip", "WIP (Coil Remains)"], ["hld", "On Hold"], ["hist", "History Per Days"]];
  // info claim contoh untuk coil On Hold (claim_material + claim_material_decision)
  const HOLDINFO = {
    "5A0003305": { no_po: "PO-2610-018", no_claim: "CLM-2611-0002", problem: "Karat putih", alasan: "Ditemukan karat putih di lapisan dalam coil", st: "Hold" },
    "5A0008210": { no_po: "PO-2610-029", no_claim: "CLM-2611-0003", problem: "Coating mengelupas", alasan: "Hasil KW 2 cacat dari supplier", st: "Dalam Retur" },
    "5A0010220": { no_po: "PO-2610-033", no_claim: "CLM-2611-0005", problem: "Gelombang", alasan: "Lebar tidak rata", st: "Dapat Dipakai (Release)" },
    "5A0002215": { no_po: "PO-2610-016", no_claim: "CLM-2610-0009", problem: "Penyok", alasan: "Rusak saat bongkar", st: "Dijual (Gudang Slitting)" },
    "5A0005120": { no_po: "PO-2610-024", no_claim: "CLM-2610-0008", problem: "Karat", alasan: "Material lembab", st: "Dikompensasi" },
  };
  const HOLD_CLS = { Hold: "danger", "Dapat Dipakai (Release)": "success", "Dijual (Gudang Slitting)": "info", "Dalam Retur": "warning", Dikompensasi: "primary" };
  // ---- hasil Incoming Subkont (FEAT-12): baby coil masuk warehouse_stock_coil Gudang Produksi 2 (PRT); dibaca baca-saja dari data mockup Subkont (sk-*.html)
  const skState = () => { try { let best = -1, k = null; for (let q = 0; q < localStorage.length; q++) { const m = /^mockSK\.data(\d+)$/.exec(localStorage.key(q) || ""); if (m && +m[1] > best) { best = +m[1]; k = localStorage.key(q); } } return k ? JSON.parse(localStorage.getItem(k)) : null; } catch (e) { return null; } };
  const sfwSub = () => { const st = skState(); if (!st || !st.stokPRT) return []; return st.stokPRT.map((c, q) => { const lap = (st.lap || []).find((l) => l.no_laporan === c.no_laporan) || {}, kode = c.kode_barang || lap.kode_barang || "-";
    return { id: "SK-" + q, kd: "PRT", sk: true, mat: kode, mat_name: c.nama_barang || kode, no_coil: c.no_coil, kode_internal: String(c.no_coil).split("-BC.")[0], net_weight: c.net_weight, gross_weight: null, length: null, pack: "", baby: true, no_incoming: c.no_incoming, no_laporan: c.no_laporan, spk_no: c.spk_no || lap.spk_no || "", harga_beli: c.harga_beli }; }); };
  const nz = (v) => (v == null ? "-" : n3(v));
  const sfwPrt = () => S().coils.filter((c) => c.gudang === "PRT").map((c) => ({ id: c.id, kd: "PRT", mat: c.mat, no_coil: c.no_coil, kode_internal: c.kode_internal, net_weight: c.net_weight, gross_weight: c.gross_weight, length: c.length, pack: c.pack, baby: !!c.baby_of, gudang_asal: "PRO" })).concat(sfwSub());
  const sfwAll = () => sfwPrt().concat(S().sfw);
  const trxOf = (x) => { if (x.kd !== "PRT") return x.kode_trans || "-"; const l = S().scanlog.find((s) => s.pack_code === x.pack); return l ? l.spk_no : "-"; };
  const matCell = (x) => (x.sk ? `${esc(x.mat_name)}<br><small class="ap-hint">${esc(x.mat)}</small><br><small style="color:#fd7e14">Hasil Subkont &bull; ${esc(x.no_incoming)}</small>` : `${esc(mn(x.mat))}<br><small class="ap-hint">${esc(x.mat)}</small><br><small style="color:#0aa2c0">${esc(W.matBy(x.mat).trade || "")}</small>`);
  const search3 = (q) => `<input class="ap-in" data-u="q" placeholder="Search..." value="${esc(q)}">`;
  const sfwFilter = (rows, q) => (q ? rows.filter((x) => ((x.sk ? x.mat_name : mn(x.mat)) + " " + x.mat + " " + x.no_coil + " " + x.kode_internal).toLowerCase().includes(q.toLowerCase())) : rows);
  function kartuModal(x) {
    const l = S().scanlog.find((s) => s.pack_code === x.pack), rows = [];
    if (l) rows.push({ no: l.spk_no, st: "in", dari: "prod. 1", ke: "prod. 2", qty: x.net_weight, awal: 0, akhir: x.net_weight });
    if (x.sk) rows.push({ no: x.no_incoming, st: "in", dari: "vendor (Subkont)", ke: "prod. 2", qty: x.net_weight, awal: 0, akhir: x.net_weight });
    openModal({ title: "Kartu Pemakaian Material", size: "xl", body: () => `<div class="wh-grid3" style="padding:8px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;margin-bottom:10px;grid-template-columns:2fr 1.5fr 1.5fr 1fr"><div><small class="ap-hint">Nama Material:</small><br><b>${esc(x.sk ? x.mat_name : mn(x.mat))}</b></div><div><small class="ap-hint">No. Coil:</small><br><b>${esc(x.no_coil)}</b></div><div><small class="ap-hint">Kode Internal:</small><br><b>${esc(x.kode_internal)}</b></div><div><small class="ap-hint">Berat Awal (Net):</small><br><b>${n3(x.net_weight)}</b></div></div>` +
      tbl(["No. Trs", "Nama Material", "Kode Internal", "Status", "Dari", "Ke", "Qty Trans", "Qty Awal", "Qty Akhir"], rows.map((r) => `<tr><td>${esc(r.no)}</td><td>${esc(x.sk ? x.mat_name : mn(x.mat))}</td><td>${esc(x.kode_internal)}</td><td><span class="ap-label ap-label--${r.st === "in" ? "success" : "danger"}">${r.st}</span></td><td>${r.dari}</td><td>${r.ke}</td><td class="ap-r">${n3(r.qty)}</td><td class="ap-r">${n3(r.awal)}</td><td class="ap-r">${n3(r.akhir)}</td></tr>`).join(""), "Belum ada riwayat pemakaian untuk coil ini."), foot: "" });
  }
  P.sfw = {
    title: "Stock From Warehouse", mod: "stock_from_warehouse", role: "Gudang / Produksi", ui: { tab: "prt", q: "", hdate: "", hsrc: "", hshown: false },
    hint: "Stok coil yang sudah keluar dari gudang utama: WRH. Production 2 (PRT), WIP (coil sisa), On Hold, dan posisi per tanggal.",
    render() {
      const u = U.ui, nav = tabs(SFWTAB, u.tab), xl = (what) => `<button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="xl" data-what="${what}">Excel</button>`;
      if (u.tab === "hist") {
        const rows = u.hshown ? sfwFilter(sfwAll().filter((x) => !u.hsrc || x.kd === u.hsrc), u.q) : [];
        const badge = (kd) => `<span class="ap-label ap-label--${{ PRT: "info", WIP: "warning", HLD: "danger" }[kd]}">${kd}</span>`;
        return nav + card("History Per Days", `<div class="cl-tools"><label>Tanggal : <input class="ap-in" type="date" data-u="hdate" value="${esc(u.hdate)}"></label><label>Sumber : <select class="ap-sel ap-in" data-u="hsrc"><option value="">-- Semua Sumber --</option><option value="PRT"${u.hsrc === "PRT" ? " selected" : ""}>Production 2 (PRT)</option><option value="WIP"${u.hsrc === "WIP" ? " selected" : ""}>WIP</option><option value="HLD"${u.hsrc === "HLD" ? " selected" : ""}>On Hold (HLD)</option></select></label>
          <button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="show">Show</button><button type="button" class="ap-btn ap-btn--sm ap-btn--secondary" data-act="reset">Reset</button>${u.hshown ? '<button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="xl" data-what="History">Download Excel</button>' : ""}${u.hshown ? search3(u.q) : ""}</div>` +
          tbl(["No", "Nama Material", "No. Coil", "Kode Internal", "Gudang Asal", "Net Weight (Kg)", "Gross Weight (Kg)", "Length (M)", "Kode Trans"], rows.map((x, i) => `<tr><td>${i + 1}</td><td>${matCell(x)}</td><td>${esc(x.no_coil)}</td><td>${esc(x.kode_internal)}</td><td style="text-align:center">${badge(x.kd)}</td><td class="ap-r">${n3(x.net_weight)}</td><td class="ap-r">${nz(x.gross_weight)}</td><td class="ap-r">${nz(x.length)}</td><td><small>${esc(trxOf(x))}</small></td></tr>`).join(""), u.hshown ? "Tidak ada data." : "Pilih tanggal lalu klik Show.")) + '<p class="note">Mockup: posisi per tanggal memakai data stok saat ini (aplikasi: transaksi warehouse_stock_transaction_detail dengan tanggal ≤ tanggal pilihan).</p>';
      }
      if (u.tab === "hld") {
        const rows = sfwFilter(S().sfw.filter((x) => x.kd === "HLD"), u.q);
        return nav + card("On Hold", `<div class="cl-tools">${search3(u.q)}<span style="margin-left:auto">${xl("On Hold")}</span></div>` +
          tbl(["No", "Nama Supplier", "Nama Material", "No. Coil", "No. PO", "Berat (Kg)", "Alasan / Problem", "Status Material", "Action"], rows.map((x, i) => { const h = HOLDINFO[x.no_coil] || { no_po: "-", no_claim: "", problem: "", alasan: "", st: "Hold" };
            return `<tr><td>${i + 1}</td><td>${esc(W.matBy(x.mat).supplier || "-")}</td><td>${esc(mn(x.mat))}</td><td style="text-align:center">${esc(x.no_coil)}${h.no_claim ? `<br><span class="ap-label ap-label--light">${esc(h.no_claim)}</span>` : ""}</td><td style="text-align:center">${esc(h.no_po)}</td><td class="ap-r">${n3(x.net_weight)}</td>
              <td>${h.problem ? `<b style="color:#dc3545">${esc(h.problem)}</b><br><small class="ap-hint">${esc(h.alasan)}</small>` : "-"}</td><td style="text-align:center"><span class="ap-label ap-label--${HOLD_CLS[h.st]}">${esc(h.st)}</span></td><td style="text-align:center"><a class="ap-btn ap-btn--sm ap-btn--light" style="border:1px solid #0d6efd;color:#0d6efd" href="claim-material.html" title="${h.no_claim ? "Buka Detail Claim di Modul Claim Material" : "Cari Claim berdasarkan No. Coil"}">${h.no_claim ? "Lihat Claim" : "Cari Claim"}</a></td></tr>`; }).join(""), "No data available in table")) +
          '<p class="note">Status Material mengikuti keputusan claim (UC-N35): Hold, Dapat Dipakai (Release), Dijual (Gudang Slitting), Dalam Retur, Dikompensasi. Keputusan diambil di modul Claim Material.</p>';
      }
      const kd = u.tab === "prt" ? "PRT" : "WIP", rows = sfwFilter(sfwAll().filter((x) => x.kd === kd), u.q), isP = kd === "PRT";
      return nav + card(isP ? "WRH. Production 2" : "WIP (Coil Remains)", `<div class="cl-tools">${search3(u.q)}<span style="margin-left:auto">${xl(isP ? "Transit" : "WIP")}</span></div>` +
        tbl(["No", "Nama Material", "No. Coil", "Kode Internal", "Net Weight (Kg)", "Gross Weight (Kg)", "Length (M)"].concat(isP ? ["Status", "Action"] : []),
          rows.map((x, i) => `<tr><td>${i + 1}</td><td>${matCell(x)}</td><td style="text-align:center">${esc(x.no_coil)}</td><td style="text-align:center">${esc(x.kode_internal)}</td><td class="ap-r">${n3(x.net_weight)}</td><td class="ap-r">${nz(x.gross_weight)}</td><td class="ap-r">${nz(x.length)}</td>` +
            (isP ? `<td style="text-align:center"><span class="ap-label ap-label--${x.sk ? "warning" : x.baby ? "info" : "primary"}">${x.sk ? "Baby Coil Subkont" : x.baby ? "Unpacked" : "In Transit (Prod. 2)"}</span></td><td style="text-align:center">${btn("light", "kartu", x.id, "Kartu Pemakaian", ' style="border:1px solid #0d6efd;color:#0d6efd" title="Lihat Kartu Pemakaian Material"')}</td>` : "") + "</tr>").join(""), "No data available in table")) +
        (isP ? '<p class="note">Coil masuk ke sini lewat Request List (scan pack PRO &rarr; PRT). Coil hasil Unpack Coil berstatus Unpacked. <b>Baby Coil Subkont</b> = hasil Incoming Subkont (Konfirmasi Hasil Incoming); gross weight dan length tidak dicatat dari vendor. Kartu Pemakaian menampilkan riwayat transaksi coil (no. transaksi = No. SPK).</p>' : '<p class="note">Sisa coil hasil Laporan Produksi yang masuk Gudang WIP; bisa dipilih lagi sebagai sumber "Coil From WIP" di Laporan Produksi.</p>');
    },
    act: {
      xl: (d) => excel("Stock From Warehouse - " + (d.what || "")),
      kartu(d) { const x = sfwAll().find((c) => String(c.id) === String(d.id)); if (x) kartuModal(x); },
      show() { if (!U.ui.hdate) { warn("Pilih tanggal terlebih dahulu."); return; } U.ui.hshown = true; U.render(); },
      reset() { Object.assign(U.ui, { hdate: "", hsrc: "", hshown: false, q: "" }); U.render(); },
    },
  };

  /* ======================= 8. Unpack Coil (unpack_coil) ======================= */
  // Mengikuti unpack_coil/views (index, form_add, view) + controller: status baru = Draft, aksi daftar View / Edit / Lock,
  // berat Actual & kulit/clamp diinput di level PACK lalu dibagi rata ke seluruh baby coil (Actual per coil = Actual pack / total roll),
  // Detail baby coil bersifat baca-saja.
  let uf = null;                                              // state form unpack
  const eligiblePacks = () => W.packs("PRT").filter((p) => !S().unpack.some((u) => u.pack_code === p.code && u.status !== "Cancelled"));
  const isMotherMat = (mid) => (W.matBy(mid).lv2 || "") === "MOTHER COIL";
  const groupsOfPack = (code) => { const g = {}; (W.packs().find((p) => p.code === code) || { coils: [] }).coils.filter((c) => !c.baby_of).forEach((c) => { const x = g[c.mat] || (g[c.mat] = { id_material: c.mat, no_coils: [], kodes: [], net_pl: 0, gross_pl: 0 }); x.no_coils.push(c.no_coil); x.kodes.push(c.kode_internal); x.net_pl += c.net_weight; x.gross_pl += c.gross_weight; });
    return Object.values(g).map((x) => Object.assign(x, { mother: isMotherMat(x.id_material), jumlah: isMotherMat(x.id_material) ? 1 : x.no_coils.length })); };
  const groupsOfRec = (r) => r.mats.map((m) => ({ id_material: m.id_material, no_coils: String(m.no_coil).split(",").map((s) => s.trim()), kodes: String(m.kode_internal).split(",").map((s) => s.trim()), net_pl: m.net_weight_pl, gross_pl: m.gross_weight_pl, mother: isMotherMat(m.id_material), jumlah: m.jumlah_coil_roll }));
  const totalRoll = (f) => f.groups.reduce((a, g) => a + (Number(g.jumlah) || 0), 0);
  const plTot = (f) => ({ net: f.groups.reduce((a, g) => a + g.net_pl, 0), gross: f.groups.reduce((a, g) => a + g.gross_pl, 0) });
  const perCoil = (f) => { const n = totalRoll(f), p = plTot(f), d = (v) => (n > 0 ? v / n : 0); return { net: d(f.net_weight_actual), gross: d(f.gross_weight_actual), kulit: d(Number(f.catatan_kulit) || 0), clamp: d(Number(f.catatan_clamp_ring) || 0), plNet: d(p.net), plGross: d(p.gross) }; };
  const babiesOf = (f, g) => { const pc = perCoil(f), base = g.no_coils[0].replace(/-BC\.\d+$/i, ""), kb = g.kodes[0].replace(/-BC\.\d+$/i, ""); return Array.from({ length: Number(g.jumlah) || 0 }, (_, i) => ({ no_coil: base + "-BC." + (i + 1), babycoil_code: kb + "-BC." + (i + 1), net_weight_actual: pc.net, gross_weight_actual: pc.gross, kulit: pc.kulit, clamp_ring: pc.clamp, net_weight_pl: pc.plNet, gross_weight_pl: pc.plGross })); };
  const sumB = (babies, k) => babies.reduce((a, b) => a + (Number(b[k]) || 0), 0);
  const hdrCls = { a: "background:#cfe2ff", p: "background:#fff3cd", s: "background:#d1e7dd", k: "background:#cff4fc" };
  const th = (t, c, o = "") => `<th style="${hdrCls[c] || ""}"${o}>${t}</th>`;
  const babyTable = (rows, foot) => `<div class="ap-tablewrap"><table class="ap-table ap-table--bordered"><thead><tr><th>No</th>${rows.mat ? "<th>Nama Material</th>" : ""}<th>No Coil</th><th>Babycoil Code</th>${th("Net Weight Per Roll (Actual)", "a")}${th("Gross Weight Per Roll (Actual)", "a")}${th("Kulit (Kg)", "k")}${th("Clamp Ring (Kg)", "k")}${th("Net Weight Per Roll (PL)", "p")}${th("Gross Weight Per Roll (PL)", "p")}</tr></thead><tbody>${rows.html}</tbody><tfoot>${foot}</tfoot></table></div>`;
  const babyRows = (list, withMat) => list.map((x, i) => `<tr><td>${i + 1}</td>${withMat ? `<td>${esc(x.mat)}</td>` : ""}<td>${esc(x.b.no_coil)}</td><td>${esc(x.b.babycoil_code)}</td><td class="ap-r">${num(x.b.net_weight_actual)}</td><td class="ap-r">${num(x.b.gross_weight_actual)}</td><td class="ap-r">${num(x.b.kulit)}</td><td class="ap-r">${num(x.b.clamp_ring)}</td><td class="ap-r">${num(x.b.net_weight_pl)}</td><td class="ap-r">${num(x.b.gross_weight_pl)}</td></tr>`).join("");
  const babyFoot = (bs, cols) => `<tr class="wh-foot"><td colspan="${cols}" class="ap-r">Total</td>${["net_weight_actual", "gross_weight_actual", "kulit", "clamp_ring", "net_weight_pl", "gross_weight_pl"].map((k) => `<td class="ap-r">${num(sumB(bs, k))}</td>`).join("")}</tr>`;
  function detailModal() {
    const items = []; uf.groups.forEach((g) => babiesOf(uf, g).forEach((b) => items.push({ mat: mn(g.id_material), b }))); const all = items.map((x) => x.b);
    openModal({ title: "Detail Baby Coil", size: "xl", body: () => babyTable({ mat: 1, html: babyRows(items, true) || '<tr><td class="ap-empty" colspan="10">Belum ada baby coil.</td></tr>' }, babyFoot(all, 4)) + '<p class="note">Kolom Actual, Kulit, dan Clamp / Ring baca-saja: nilai pack dibagi rata ke seluruh baby coil (jumlah roll semua material); PL = total PL pack dibagi jumlah roll.</p>' });
  }
  const ringkas = (f, ro) => { const p = plTot(f), nA = Number(f.net_weight_actual) || 0, gA = Number(f.gross_weight_actual) || 0;
    return `<div class="ap-tablewrap"><table class="ap-table ap-table--bordered" style="text-align:center"><thead><tr><th rowspan="2">Catatan Kulit</th><th rowspan="2">Catatan Clamp Ring</th>${th("Actual Weight", "a", ' colspan="2"')}${th("Packing List", "p", ' colspan="2"')}${th("Selisih Actual vs PL", "s", ' colspan="2"')}</tr><tr>${th("Net Weight", "a")}${th("Gross Weight", "a")}${th("Net Weight", "p")}${th("Gross Weight", "p")}${th("Net", "s")}${th("Gross", "s")}</tr></thead><tbody><tr>
      <td>${ro ? esc(f.catatan_kulit || "-") : `<input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="catatan_kulit" value="${esc(NumFmt.fmt(Number(f.catatan_kulit) || 0))}">`}</td><td>${ro ? esc(f.catatan_clamp_ring || "-") : `<input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="catatan_clamp_ring" value="${esc(NumFmt.fmt(Number(f.catatan_clamp_ring) || 0))}">`}</td>
      <td>${ro ? num(nA) : `<input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="net_weight_actual" value="${esc(NumFmt.fmt(nA))}">`}</td><td>${ro ? num(gA) : `<input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="gross_weight_actual" value="${esc(NumFmt.fmt(gA))}">`}</td>
      <td class="ap-r">${num(p.net)}</td><td class="ap-r">${num(p.gross)}</td><td class="ap-r" id="selN">${num(nA - p.net)}</td><td class="ap-r" id="selG">${num(gA - p.gross)}</td></tr></tbody></table></div>`; };
  function unpackForm() {
    const edit = !!uf.unpack_no, p = plTot(uf), rs = uf.groups.length;
    const mrows = uf.groups.map((g, i) => `<tr><td>${i + 1}</td><td>${pill(uf.pack)}</td><td>${esc(mn(g.id_material))}</td><td>${g.no_coils.map((c) => `<div><span class="ap-label ap-label--light" style="font-family:monospace">&#9679; ${esc(c)}</span></div>`).join("")}</td>
      <td style="text-align:center"><input class="ap-in ap-in--num" style="width:70px;margin:0 auto"${g.mother ? ' disabled title="Mother Coil otomatis 1 roll"' : ""} inputmode="decimal" data-numfmt="1" data-f="jumlah" data-i="${i}" value="${esc(NumFmt.fmt(g.jumlah))}">${g.mother ? '<div><span class="ap-label ap-label--secondary" style="font-size:10px">Mother (1 Roll)</span></div>' : ""}</td>
      ${i === 0 ? `<td class="ap-r" rowspan="${rs}" id="mA1">${num(uf.net_weight_actual)}</td><td class="ap-r" rowspan="${rs}" id="mA2">${num(uf.gross_weight_actual)}</td><td class="ap-r" rowspan="${rs}">${num(p.net)}</td><td class="ap-r" rowspan="${rs}">${num(p.gross)}</td><td rowspan="${rs}" style="text-align:center">${btn("info", "detail", "x", "Detail")}</td>` : ""}</tr>`).join("");
    return card(edit ? "Edit Report Unpack — " + esc(uf.unpack_no) : "Add Report Unpack", row("Pilih Pack (Material Confirmed)", edit ? `${pill(uf.pack)} <span class="ap-hint">pack tidak dapat diganti saat edit</span>` : sel("pack", uf.pack, eligiblePacks().map((pk) => [pk.code, `${pk.code} (${pk.mats.length} material, ${pk.roll} coil)`]), { blank: "Cari & pilih pack..." })) +
      (uf.pack ? `<h6 style="margin:10px 0 6px;font-weight:700">Ringkasan Pack</h6>${ringkas(uf, false)}<h6 style="margin:14px 0 6px;font-weight:700">Material dalam Pack</h6>
        <div class="ap-tablewrap"><table class="ap-table ap-table--bordered" style="text-align:center"><thead><tr><th rowspan="2">No</th><th rowspan="2">Pack Code</th><th rowspan="2">Nama Material</th><th rowspan="2">No Coil</th><th rowspan="2">Jumlah Coil Roll</th>${th("Actual Weight", "a", ' colspan="2"')}${th("Packing List", "p", ' colspan="2"')}<th rowspan="2">Aksi</th></tr><tr>${th("Net Weight Actual", "a")}${th("Gross Weight Actual", "a")}${th("Net Weight PL", "p")}${th("Gross Weight PL", "p")}</tr></thead>
        <tbody>${mrows || '<tr><td colspan="10" class="ap-empty">Tidak ada material dalam pack ini.</td></tr>'}</tbody></table></div>` +
        row("Catatan", txt("catatan", uf.catatan, { ph: "Opsional", rows: 2 })) : '<div class="ap-empty">Pilih pack untuk memuat material.</div>') +
      `<div class="cl-tools" style="margin-top:10px"><button type="button" class="ap-btn ap-btn--secondary" data-act="cancel">&#8592; Kembali</button><button type="button" class="ap-btn ap-btn--success" data-act="save">Save</button></div>`);
  }
  const UNPACK_BADGE = { Lock: "success", Draft: "warning", Cancelled: "danger" };
  function unpackView(r) {
    const p = { net: r.net_weight_pl, gross: r.gross_weight_pl }, f = { catatan_kulit: r.catatan_kulit, catatan_clamp_ring: r.catatan_clamp_ring, net_weight_actual: r.net_weight_actual, gross_weight_actual: r.gross_weight_actual, groups: groupsOfRec(r) };
    return `<div class="ap-card ap-card--shadow"><div class="ap-card__body"><div class="wh-grid3" style="grid-template-columns:repeat(4,1fr)"><div><b>Unpack No</b><br>${esc(r.unpack_no)}</div><div><b>Pack Code</b><br>${pill(r.pack_code)}</div><div><b>Tgl Unpack</b><br>${esc(r.tgl_unpack.slice(8, 10) + "/" + r.tgl_unpack.slice(5, 7) + "/" + r.tgl_unpack.slice(0, 4) + " " + r.tgl_unpack.slice(11))}</div><div><b>Status</b><br><span class="ap-label ap-label--${r.status === "Confirmed" ? "success" : r.status === "Cancelled" ? "danger" : "secondary"}">${esc(r.status)}</span></div></div>
      <div class="wh-grid3" style="grid-template-columns:1fr 1fr 2fr;margin-top:10px"><div><b>Catatan Kulit</b><br>${esc(r.catatan_kulit || "-")}</div><div><b>Catatan Clamp Ring</b><br>${esc(r.catatan_clamp_ring || "-")}</div><div><b>Catatan</b><br>${esc(r.catatan || "-")}</div></div></div></div>` +
      card("Ringkasan Pack", ringkas(f, true)) + r.mats.map((m, i) => { const items = m.babies.map((b) => ({ b }));
        return card(`Material #${i + 1}: ${esc(mn(m.id_material))} — ${esc(m.no_coil)} | ${esc(m.kode_internal)}`, babyTable({ html: babyRows(items, false) || '<tr><td class="ap-empty" colspan="9">Tidak ada baby coil.</td></tr>' }, babyFoot(m.babies, 3)), `<span>Jumlah Roll: <span class="ap-label ap-label--success">${m.jumlah_coil_roll}</span></span>`); }).join("") +
      `<div class="cl-tools"><button type="button" class="ap-btn ap-btn--secondary" data-act="back">&#8592; Kembali</button></div>`;
  }
  P.unpack = {
    title: "Unpack Coil", mod: "unpack_coil", role: "Gudang / Produksi", ui: { mode: "list", id: null },
    hint: "Report Unpack: 1 report = 1 pack di WRH Production 2 (PRT). Input berat actual pack, jumlah baby coil per material; berat dibagi rata ke baby coil.",
    render() {
      const u = U.ui;
      if (u.mode === "form") return unpackForm();
      if (u.mode === "view") return unpackView(S().unpack.find((x) => x.unpack_no === u.id));
      const l = S().unpack.slice().sort((a, b) => b.tgl_unpack.localeCompare(a.tgl_unpack));
      return card("Daftar Report Unpack", `<div class="cl-tools"><span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--success" data-act="add">+ Add Report Unpack</button></span></div>` +
        tbl(["No", "Unpack No", "Pack Code", "Jumlah Material", "Jumlah Coil Roll", "Net Weight Actual", "Tgl Unpack", "Status", "Aksi"], l.map((r, i) => {
          const it = [mi("view", r.unpack_no, "View")]; if (!["Cancelled", "Lock"].includes(r.status)) it.push(mi("edit", r.unpack_no, "Edit"), mi("x", "", "-"), mi("lock", r.unpack_no, "Lock"));
          return `<tr><td>${i + 1}</td><td>${esc(r.unpack_no)}</td><td>${pill(r.pack_code)}</td><td class="ap-r">${r.mats.length}</td><td class="ap-r">${r.mats.reduce((a, m) => a + m.jumlah_coil_roll, 0)}</td><td class="ap-r">${num(r.net_weight_actual)}</td><td>${fdate(r.tgl_unpack)}</td><td style="text-align:center"><span class="ap-label ap-label--${UNPACK_BADGE[r.status] || "secondary"}">${esc(r.status)}</span></td><td style="text-align:center">${menu(it)}</td></tr>`; }).join(""), "No data available in table")) +
        '<p class="note">Report baru berstatus Draft dan boleh di-Edit; setelah Lock tidak dapat diedit. Pack yang bisa dipilih = pack di gudang PRT (hasil scan di Request List) yang belum punya report aktif. Baby coil hasil Save langsung menggantikan coil induk di stok PRT.</p>';
    },
    onField(el) {
      const k = el.dataset.f;
      if (k === "pack") {
        uf.pack = el.value; uf.groups = uf.pack ? groupsOfPack(uf.pack) : [];
        const p = plTot(uf); uf.net_weight_actual = 0; uf.gross_weight_actual = 0; uf.catatan_kulit = 0; uf.catatan_clamp_ring = 0; U.render(); return;
      }
      if (k === "jumlah") { const g = uf.groups[+el.dataset.i]; if (g.mother) { g.jumlah = 1; return; } g.jumlah = Math.max(0, Math.floor(pv(el) || 0)); return; }
      if (["net_weight_actual", "gross_weight_actual", "catatan_kulit", "catatan_clamp_ring"].includes(k)) { uf[k] = pv(el) || 0; const p = plTot(uf); $("#selN").textContent = num(uf.net_weight_actual - p.net); $("#selG").textContent = num(uf.gross_weight_actual - p.gross); const a = $("#mA1"), b = $("#mA2"); if (a) a.textContent = num(uf.net_weight_actual); if (b) b.textContent = num(uf.gross_weight_actual); return; }
      uf[k] = el.value;
    },
    act: {
      back() { U.ui.mode = "list"; U.render(); }, cancel() { U.ui.mode = "list"; U.render(); },
      view(d) { U.ui.mode = "view"; U.ui.id = d.id; U.render(); },
      add() { uf = { unpack_no: null, pack: "", catatan_kulit: 0, catatan_clamp_ring: 0, net_weight_actual: 0, gross_weight_actual: 0, catatan: "", groups: [] }; U.ui.mode = "form"; U.render(); },
      edit(d) { const r = S().unpack.find((x) => x.unpack_no === d.id); uf = { unpack_no: r.unpack_no, pack: r.pack_code, catatan_kulit: Number(r.catatan_kulit) || 0, catatan_clamp_ring: Number(r.catatan_clamp_ring) || 0, net_weight_actual: r.net_weight_actual, gross_weight_actual: r.gross_weight_actual, catatan: r.catatan || "", groups: groupsOfRec(r) }; U.ui.mode = "form"; U.render(); },
      detail() { if (!uf.groups.length) return; const bad = uf.groups.find((g) => !(g.jumlah > 0)); if (bad) { dialog({ icon: "warning", title: "Detail belum lengkap", text: "Isi Jumlah Coil (Roll Baby Coil) untuk material " + mn(bad.id_material) + " terlebih dahulu." }); return; } detailModal(); },
      lock(d) { confirmThen({ title: "Lock Report Unpack?", text: "Report " + d.id + " akan dikunci dan tidak bisa diedit lagi. Lanjutkan?", ok: "Ya, Lock", cancel: "Batal" }, () => { S().unpack.find((x) => x.unpack_no === d.id).status = "Lock"; W.save(); U.render(); toast("Report unpack berhasil di-lock.", "ok"); }); },
      save() {
        if (!uf.pack) { dialog({ icon: "warning", title: "Pilih pack dulu" }); return; }
        if (!uf.groups.length) { dialog({ icon: "warning", title: "Belum ada material", text: "Pack ini tidak memiliki material." }); return; }
        const bad = uf.groups.find((g) => !(g.jumlah > 0));
        if (bad) { dialog({ icon: "warning", title: "Detail belum lengkap", text: "Detail baby coil untuk material " + bad.id_material + ' gagal dimuat. Coba klik "View Detail" secara manual.' }); return; }
        confirmThen({ icon: "question", title: "Konfirmasi Penyimpanan", text: "Apakah data yang dimasukkan sudah betul?", ok: "Ya, Simpan", cancel: "Batal" }, () => {
          const old = uf.unpack_no ? S().unpack.find((x) => x.unpack_no === uf.unpack_no) : null; if (old) W.unpackRevert(old);
          const p = plTot(uf), mats = uf.groups.map((g) => { const bs = babiesOf(uf, g); return { id_material: g.id_material, no_coil: g.no_coils.join(", "), kode_internal: g.kodes.join(", "), jumlah_coil_roll: bs.length, net_weight_actual: sumB(bs, "net_weight_actual"), gross_weight_actual: sumB(bs, "gross_weight_actual"), net_weight_pl: g.net_pl, gross_weight_pl: g.gross_pl, babies: bs }; });
          const rec = { unpack_no: uf.unpack_no || W.nextNo("unpack"), tgl_unpack: old ? old.tgl_unpack : W.today() + " 10:00", pack_code: uf.pack, catatan_kulit: String(uf.catatan_kulit || ""), catatan_clamp_ring: String(uf.catatan_clamp_ring || ""), net_weight_actual: uf.net_weight_actual, gross_weight_actual: uf.gross_weight_actual, net_weight_pl: p.net, gross_weight_pl: p.gross, catatan: uf.catatan, status: old ? old.status : "Draft", mats };
          const i = S().unpack.findIndex((x) => x.unpack_no === rec.unpack_no); if (i >= 0) S().unpack[i] = rec; else S().unpack.push(rec);
          W.unpackApply(rec); W.save(); U.ui.mode = "list"; U.render(); toast("Report unpack berhasil disimpan.", "ok");
        });
      },
    },
  };

  U.init(P);
  if (document.body && document.body.dataset.wpage === "sfw") window.addEventListener("focus", () => { if (window.WHUI && window.WHUI.render) window.WHUI.render(); });
})();
