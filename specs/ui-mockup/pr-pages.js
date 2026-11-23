/* ============================================================
   pr-pages.js — Halaman Laporan Produksi (mockup), dibuat dari modul aplikasi production_report
   (index.php, form.php, view_detail.php, view_hpp.php; controller + model). Hanya dibaca, tidak diubah.
   Empat halaman: daftar (index), input (create), lihat (view), hpp. Pembantu umum: wh-core.js (WHUI).
   ============================================================ */
(() => {
  "use strict";
  const U = window.WHUI, W = U.W;
  const { $, esc, num, fdate, btn, tbl, card, stat, sel, toast, dialog, warn, done } = U;
  const P = {};
  const APP = "Laporan Produksi";
  const qp = new URLSearchParams(location.search);
  const rpf = (n) => "Rp " + Number(n || 0).toLocaleString("id-ID", { maximumFractionDigits: 0 });
  const n2 = (n) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const n4 = (n) => Number(n || 0).toLocaleString("id-ID", { minimumFractionDigits: 4, maximumFractionDigits: 4 });
  const nz = (v) => (Number(v) ? v : "");                       // kolom kosong bila 0 (seperti value="" di form.php)
  const USER = { name: "Agus H.", role: "Produksi" };
  const TOL = W.TOLERANCE;
  const base = { app: APP, mod: "production_report", role: "Produksi" };

  /* ======================= 1. Daftar SPK (production_report/index) ======================= */
  const stBadge = (r) => ({ Done: '<span class="pr-pill pr-pill--done">&#10003; Selesai</span>', "On Hold": `<span class="pr-pill pr-pill--hold">&#10074;&#10074; SPK Sedang Di-Hold (${esc(r.hold_claim_no || "")})</span>`, Draft: '<span class="pr-pill pr-pill--draft">&#9998; Draft</span>' }[r.report_status] || '<span class="pr-pill pr-pill--wait">&#9719; Menunggu</span>');
  P.daftar = Object.assign({}, base, {
    title: "SPK Menunggu Produksi", ui: { tab: "open", q: "" },
    hint: "Pilih SPK untuk meninjau data tarikan lalu menginput laporan hasil produksi aktual.",
    render() {
      const u = U.ui, all = W.spkList(""), list = W.spkList(u.q);
      const open = (r) => r.report_status !== "Done", nOpen = all.filter(open).length, nDone = all.length - nOpen;
      const rows = list.filter((r) => (u.tab === "open" ? open(r) : u.tab === "closed" ? !open(r) : true));
      const act = (r) => {
        if (r.report_status === "Done" && r.report_id) return `<a class="ap-btn ap-btn--sm ap-btn--secondary" href="pr-lihat.html?id=${r.report_id}" title="Lihat Detail Laporan">Laporan</a> <a class="ap-btn ap-btn--sm ap-btn--primary" href="pr-hpp.html?id=${r.report_id}" title="Lihat Perhitungan HPP & Jurnal">HPP</a> ${btn("success", "export", r.report_id, "Export")}`;
        if (r.report_status === "Draft" || r.report_status === "On Hold") return `<a class="ap-btn ap-btn--sm ap-btn--warning" href="pr-input.html?spk=${encodeURIComponent(r.spk_no)}">Lanjutkan Draft</a>` + (r.report_status === "On Hold" ? ` ${btn("light", "decide", r.hold_claim_no, "Simulasi: Purchasing memutuskan", ' title="Hanya untuk mockup: menandai claim Hold Coil sudah diputuskan di modul Claim Material"')}` : "");
        return `<a class="ap-btn ap-btn--sm ap-btn--primary" href="pr-input.html?spk=${encodeURIComponent(r.spk_no)}">Buat Laporan &rarr;</a>`;
      };
      return `<div class="cl-sumrow">${stat(nOpen, "SPK Menunggu")}${stat(nDone, "SPK Selesai (Done)")}${stat(num(all.reduce((a, r) => a + r.total_target_qty, 0), 0), "Target Qty")}</div>` +
        card("Daftar Surat Perintah Kerja", `<p class="pr-hint">Setiap SPK memuat daftar produk Finish Good KW 1 yang siap dilaporkan hasil produksinya.</p>
          <div class="cl-tools"><input class="ap-in" data-u="q" placeholder="Cari SPK No / Nama Produk FG..." value="${esc(u.q)}"><button type="button" class="ap-btn ap-btn--sm ap-btn--secondary" data-act="refresh">Refresh Data</button></div>` +
          U.tabs([["open", `Open / Dalam Proses <span class="wh-tabs-count">${nOpen}</span>`], ["closed", `Selesai (Closed) <span class="wh-tabs-count">${nDone}</span>`], ["all", `Semua SPK <span class="wh-tabs-count">${all.length}</span>`]], u.tab) +
          tbl(["SPK No", "Tgl SPK", "Nama Produk FG", "Target Qty", "Catatan", "Aksi"],
            rows.map((r) => `<tr><td><b>${esc(r.spk_no)}</b><div>${stBadge(r)}</div></td><td>${fdate(r.tgl_spk)}</td>
              <td>${r.produk_fg_summary.split(", ").map((p) => `<div>&#9632; ${esc(p)}</div>`).join("")}</td><td class="ap-r"><span class="pr-chipq">${num(r.total_target_qty, 0)} pcs</span></td>
              <td>${r.catatan ? `<small class="ap-hint">${esc(r.catatan)}</small>` : "-"}</td><td class="ap-act ap-act-nowrap">${act(r)}</td></tr>`).join(""), "Tidak ada SPK pada tab ini.")) +
        '<p class="note">Hanya SPK berstatus Released (atau yang sudah punya laporan) yang tampil; ubah status SPK menjadi Released di Request List. Tombol Simulasi di baris On Hold hanya ada di mockup: di aplikasi keputusan Purchasing diambil di modul Claim Material.</p>';
    },
    act: {
      refresh() { U.render(); toast("Data dimuat ulang.", "ok"); },
      export() { toast("Export Report Excel (mockup, tidak membuat berkas nyata)."); },
      decide(d) { W.decide(d.id); U.render(); toast(`Claim ${d.id} ditandai sudah diputuskan Purchasing. Laporan tetap harus bebas baris Hold Coil sebelum Submit.`, "ok"); },
    },
  });

  /* ======================= 2. Input Laporan Produksi (production_report/create) ======================= */
  let R = null, SPK = null, FATAL = null, ST = null;
  const maxC = () => R.coils.length;
  const sel1 = (f, v, opts, blank, o = {}) => `<select class="ap-sel ap-in" data-f="${f}"${o.multiple ? " multiple size=\"3\"" : ""}${o.dis ? " disabled" : ""}>${blank != null ? `<option value="">${esc(blank)}</option>` : ""}${opts.map((x) => `<option value="${esc(x[0])}"${(Array.isArray(v) ? v.includes(String(x[0])) : String(x[0]) === String(v)) ? " selected" : ""}>${esc(x[1])}</option>`).join("")}</select>`;
  const field = (label, html, req, hint) => `<div class="pr-f"><label class="ap-lbl">${label}${req ? ' <span class="ap-req">*</span>' : ""}</label>${html}${hint ? `<div class="ap-hint">${hint}</div>` : ""}</div>`;
  const nin = (f, v, ro) => U.inp(f, nz(v), { num: 1, ro });
  const coilOpt = (usedCodes) => R.coils.filter((c) => c.code && !usedCodes.includes(c.code)).map((c) => [c.code, c.code + " · " + c.material]);
  const BL = "— Pilih Baby Coil Terpakai —";
  const cnt = (n) => `<span class="pr-cnt">${n} / ${maxC()} Baris</span>`;
  const sec = (n, title, hint, body, head = "", id = "") => `<section class="ap-card ap-card--shadow"${id ? ` id="${id}"` : ""}><div class="ap-card__head ap-card__head--bar"><div><h5><span class="pr-num">${n}</span>${title}</h5><div class="pr-hint">${hint}</div></div>${head}</div><div class="ap-card__body">${body}</div></section>`;
  const subh = (title, count, button) => `<div class="pr-sub-h"><h6>${title}${count != null ? cnt(count) : ""}</h6>${button || ""}</div>`;
  const delb = (act, id, extra = "") => `<button type="button" class="ap-btn ap-btn--sm ap-btn--danger" data-act="${act}" data-id="${esc(id)}"${extra} title="Hapus Baris">&#10005;</button>`;
  const isConfirmed = (f) => f.confirmedAt != null && f.confirmedAt === f.selisih && Math.abs(f.selisih) > TOL;
  const metaCard = () => `<div class="pr-meta"><div><span>SPK No</span><b>${esc(SPK.spk_no)}</b></div><div><span>Tgl SPK</span><b>${fdate(SPK.tgl_spk)}</b></div><div><span>Target Qty</span><b style="color:#2563eb">${num(SPK.products.reduce((a, p) => a + p.target_qty, 0), 0)} pcs</b></div><div><span>Catatan</span><b style="font-weight:400;color:#64748b">${esc(SPK.catatan || "-")}</b></div></div>`;

  function fgCard(f) {
    const bebas = f.kind === "bebas", p1 = f.method === 1;
    let head;
    if (bebas) head = sel1(`fg|${f.id}|productId`, f.productId, W.PRODUCTS.map((p) => [p.id, `${p.nama} (${p.weight} kg/pcs)`]), "— Pilih Master Produk —");
    else head = (SPK.products.length > 1 ? sel1(`fg|${f.id}|productId`, f.productId, SPK.products.map((p) => [p.id_product_lvl_4, `${p.nm_produk_fg} (Target: ${p.target_qty} pcs)`]), "— Pilih Produk dari SPK —") : `<span class="pr-fg__name">${esc(f.productName || "Produk SPK")}</span>`) + (f.targetQty != null ? ` <span class="pr-chip">Target SPK: ${f.targetQty} pcs</span>` : "");
    return `<div class="pr-fg" data-fgid="${f.id}"><div class="pr-fg__h"><div>${head} <span class="pr-conf" data-confirm="${f.id}"${isConfirmed(f) ? "" : " hidden"}>&#10003; Selisih Dikonfirmasi</span></div>${delb("fg-del", f.id)}</div>
      <div class="pr-method"><label><input type="radio" name="m-${f.id}" value="1" data-f="fg|${f.id}|method"${p1 ? " checked" : ""}> Metode 1: Qty + Berat Total</label><label><input type="radio" name="m-${f.id}" value="2" data-f="fg|${f.id}|method"${!p1 ? " checked" : ""}> Metode 2: Qty + Berat/Pcs</label></div>
      <div class="pr-grid4">${field("Source Material Coil", sel1(`fg|${f.id}|coil`, f.coil, coilOpt(R.fg.filter((x) => x.id !== f.id && x.kind === f.kind && x.coil).map((x) => x.coil)), BL))}
        ${field("Qty", nin(`fg|${f.id}|qty`, f.qty))}${field(p1 ? "Berat Total (Kg)" : "Berat Per Pcs (Kg)", nin(`fg|${f.id}|input`, p1 ? f.total : f.perPcs))}
        ${field(p1 ? "Berat Per Pcs (Kg)" : "Berat Total (Kg)", `<input class="ap-in ap-in--num ap-in--ro" readonly data-fgd="${f.id}" value="">`)}</div>
      <div class="pr-metrics"><div><label>Berat Standard</label><input class="ap-in ap-in--num ap-in--ro" readonly data-fgs="${f.id}"></div><div><label>Percentage Selisih</label><span class="pr-sel ok" data-fgp="${f.id}">0,00 %</span></div>
        <div><label>Panjang Meter</label><input class="ap-in ap-in--num ap-in--ro" readonly data-fgl="${f.id}"></div><div><label>Berat Per Meter Aktual</label><input class="ap-in ap-in--num ap-in--ro" readonly data-fgm="${f.id}"></div>
        <div><label>Berat Per Meter Standard</label><input class="ap-in ap-in--num ap-in--ro" readonly data-fgsm="${f.id}"></div><div><label>Percentage Selisih Meter</label><span class="pr-sel ok" data-fgpm="${f.id}">0,00 %</span></div></div></div>`;
  }
  const kw2Table = (t) => {
    const ini = t === "supplier" ? "S" : "I", rows = R.kw2[t], key = "kw2" + ini;
    return tbl(["No", "Product Lvl 4 Id", "Source Material Coil", "Size Meter", "Qty", "Berat Total (Kg)", "Berat Per Pcs (Kg)", "Nama Product Custom", "Keterangan", ""],
      rows.map((r, i) => { const p = W.prodBy(r.productId); return `<tr><td>${i + 1}</td><td>${sel1(`${key}|${r.id}|productId`, r.productId, W.PRODUCTS.map((x) => [x.id, `${x.nama} (${x.weight} kg/pcs)`]), "— Pilih Master Produk —")}</td>
        <td>${sel1(`${key}|${r.id}|coil`, r.coil, coilOpt([]), BL)}</td><td>${nin(`${key}|${r.id}|size`, r.size)}</td><td>${nin(`${key}|${r.id}|qty`, r.qty)}</td><td>${nin(`${key}|${r.id}|total`, r.total)}</td>
        <td class="ap-r" data-kw2p="${r.id}">${n2(r.qty > 0 ? r.total / r.qty : 0)}</td><td><span class="ap-label ap-label--secondary">${p ? esc(p.nama + "-KW2-" + ini + "-" + String(i + 1).padStart(2, "0")) : "—"}</span></td>
        <td><input class="ap-in" data-f="${key}|${r.id}|ket" placeholder="Keterangan..." value="${esc(r.ket)}"></td><td>${delb("kw2-del", r.id, ` data-t="${t}"`)}</td></tr>`; }).join(""), t === "supplier" ? "Belum ada KW 2 Supplier." : "Belum ada KW 2 Internal.");
  };
  const lineTable = (k) => tbl(["Target Coil Code", "Berat Total (Kg)", ""],
    R[k].map((r) => `<tr><td>${sel1(`${k}|${r.id}|coil`, r.coil, coilOpt(R[k].filter((x) => x.id !== r.id && x.coil).map((x) => x.coil)), BL)}</td><td>${nin(`${k}|${r.id}|berat`, r.berat)}</td><td>${delb("line-del", r.id, ` data-k="${k}"`)}</td></tr>`).join(""), k === "sisa" ? "Tidak ada sisa coil." : "Tidak ada hold coil.");

  function claimBody() {
    const rows = W.claimRows(R);
    if (!rows.length) return `<tr><td class="ap-empty" colspan="14">Tidak ada Hold Coil, KW 2 (Supplier), atau Reject (Supplier) pada laporan ini.</td></tr>`;
    const act = R.coils.filter((c) => c.code);
    return rows.map((r) => {
      const m = W.claimMeta(R, r.coilCode) || {}, dis = r.lock ? " disabled" : "";
      const lab = { "Hold Coil": "danger", "KW 2 (Supplier)": "warning" }[r.asal] || "dark";
      const coilCell = r.fixed ? `<b>${esc(r.coilCode || "—")}</b>` : `<select class="ap-sel ap-in" data-f="cl|${esc(r.key)}|coilCode"${dis}><option value="">-- Pilih Coil --</option>${act.map((c) => `<option${c.code === r.coilCode ? " selected" : ""}>${esc(c.code)}</option>`).join("")}</select>`;
      return `<tr><td><span class="ap-label ap-label--${lab}">${esc(r.asal)}</span></td><td>${coilCell}</td><td>${esc(m.nm_material || "—")}</td><td>${esc(m.nm_supplier || "—")}</td><td>${esc(m.no_po || "—")}</td><td>${esc(m.no_coil_packing_list || r.coilCode || "—")}</td>
        <td class="ap-r">${n2(r.berat_kg)}</td><td class="ap-r">${n2(m.harga_beli_per_kg || 0)}</td><td class="ap-r">${n2(m.costbook_per_kg || 0)}</td><td class="ap-r">${n2(m.price_per_unit_po || 0)}</td>
        <td><input class="ap-in" data-f="cl|${esc(r.key)}|problem" value="${esc(r.problem)}" placeholder="mis. Cacat / Karat"${dis}></td>
        <td><textarea class="ap-in" rows="2" data-f="cl|${esc(r.key)}|alasan" placeholder="Alasan dari produksi..."${dis}>${esc(r.alasan)}</textarea></td>
        <td>${r.bukti.length ? r.bukti.map((b, i) => `<span class="pr-chip">&#128206; ${esc(b.file_original_name)}${b.file_size ? ` <small>(${Math.round(b.file_size / 1024)} KB)</small>` : ""}${r.lock ? "" : `<a data-act="cl-delfile" data-key="${esc(r.key)}" data-i="${i}" title="Hapus berkas">&times;</a>`}</span>`).join("") : '<span class="pr-need">Wajib lampirkan bukti</span>'}
          ${r.lock ? "" : `<div><label class="ap-btn ap-btn--sm ap-btn--secondary" style="cursor:pointer">Upload Bukti<input type="file" multiple accept="image/*,application/pdf,video/*" data-f="cl|${esc(r.key)}|file" style="display:none"></label></div>`}</td>
        <td><span class="ap-label ap-label--${r.lock ? "danger" : "secondary"}">${esc(r.status)}</span></td></tr>`;
    }).join("");
  }
  const auditRows = () => R.confirmations.map((c, i) => `<tr><td>${i + 1}</td><td>${esc(c.label)}</td><td class="ap-r wh-diff-bad">${n2(c.selisih)} %</td><td>${esc(c.statement)}</td><td><b>${esc(c.user)}</b> <span class="ap-label ap-label--light">${esc(c.role)}</span></td><td>${esc(c.tsLabel)}</td></tr>`).join("");

  function load() {
    FATAL = null; SPK = W.spkOf(qp.get("spk") || ""); R = null;
    if (!qp.get("spk")) { FATAL = ["danger", "Nomor SPK tidak ditemukan."]; return; }
    if (!SPK) { FATAL = ["danger", `Data SPK ${qp.get("spk")} tidak valid atau tidak ditemukan.`]; return; }
    if (SPK.status !== "Released") { FATAL = ["warning", `SPK ${SPK.spk_no} masih berstatus "${SPK.status}". Ubah status SPK menjadi Released di modul Request List terlebih dahulu.`]; return; }
    const ex = W.reportOf(SPK.spk_no);
    if (ex && !ex.status_draft) { FATAL = ["warning", `SPK ${SPK.spk_no} sudah memiliki laporan final. Lihat di menu Laporan atau HPP pada daftar.`]; return; }
    R = ex ? W.deep(ex) : W.emptyReport(SPK.spk_no);
    ST = W.state; R.fg.forEach((f) => (f.method = f.method || 1));
  }
  P.input = Object.assign({}, base, {
    title: "Input Laporan Produksi", hint: "Isi hasil produksi aktual per SPK: sumber baby coil, Finish Good KW 1 / Stok Bebas / KW 2, scrap, sisa & hold coil, claim material, lalu bandingkan dengan Packing List.",
    render() {
      if (!R || ST !== W.state) load();
      if (FATAL) return `<div class="pr-${FATAL[0] === "danger" ? "warn" : "warn"}">${esc(FATAL[1])}</div><p><a class="ap-btn ap-btn--secondary" href="pr-daftar.html">Kembali ke Daftar</a></p>`;
      const h = R, s = R.scrap, mach = W.MACHINES.map((m) => [m.id, `${m.nm_asset} (${m.kd_asset})`]);
      const scrapIn = (k, label) => field(label, nin(`scrap||${k}`, s[k]));
      const kw1 = R.fg.filter((f) => f.kind === "kw1"), beb = R.fg.filter((f) => f.kind === "bebas");
      return `<div class="pr-top"><div class="pr-sub">SPK: <b>${esc(SPK.spk_no)}</b> &bull; Tgl SPK: ${fdate(SPK.tgl_spk)}</div><div><span class="ap-label ap-label--${h.status_draft ? "secondary" : "success"}" id="docStatus">${h.status_draft ? "Draft" : "Submitted (Done)"}</span> <a class="ap-btn ap-btn--sm ap-btn--secondary" href="pr-daftar.html">Kembali ke Daftar</a></div></div>` +
        sec(1, "Informasi Laporan", "Data SPK &amp; jadwal produksi (read-only info SPK). Lengkapi data mesin dan operator di bawah.", metaCard() +
          `<div class="pr-grid4">${field("Tgl Produksi", `<input class="ap-in" type="date" data-f="hdr||tgl_produksi" value="${esc(h.tgl_produksi)}">`, 1)}${field("Id Asset Machine", sel1("hdr||id_asset_machine", h.id_asset_machine, mach, "-- Pilih Mesin --"), 1)}
          ${field("Employee Helper", sel1("hdr||helpers", h.helpers, W.HELPERS.map((x) => [x, x]), null, { multiple: 1 }), 1, "Bisa pilih lebih dari 1 helper (tahan Ctrl)")}${field("Employee Setter", sel1("hdr||setter", h.setter, W.SETTERS.map((x) => [x, x]), "-- Pilih Setter --"), 1)}
          ${field("Start Time", `<input class="ap-in" type="time" data-f="hdr||start_time" value="${esc(h.start_time)}">`, 1)}${field("Finished Time", `<input class="ap-in" type="time" data-f="hdr||finished_time" value="${esc(h.finished_time)}">`, 1)}</div>`) +
        sec(2, "Add Coil (Sumber Material)", "Pilih baby coil dari Gudang Unpack, WIP, atau Hold. Tarik data berat &amp; meter otomatis.",
          tbl(["Source Warehouse", "Coil Code", "Material Name", "Nett Weight Packing (Kg)", "Gross Weight (Kg)", "Total Meter", "Berat Kulit (Kg)", "Berat Clamp (Kg)", ""],
            h.coils.map((c) => { const used = h.coils.filter((x) => x.id !== c.id && x.code).map((x) => x.code), av = W.availableCoils(c.source).filter((x) => !used.includes(x.no_coil));
              const opts = av.map((x) => [x.no_coil, x.no_coil + " · " + x.nm_material]); if (c.code && !av.some((x) => x.no_coil === c.code)) opts.push([c.code, c.code + " · " + c.material]);
              const has = !!c.code, f = (v, d) => (has ? num(v, d) : "—");
              return `<tr><td><span class="pr-src pr-src--${c.source}">${W.SRC_LABEL[c.source]}</span></td><td>${sel1(`coil|${c.id}|pick`, c.code, opts, "— Pilih Baby Coil —")}</td><td>${has ? esc(c.material) : "—"}</td><td class="ap-r">${f(c.nett)}</td><td class="ap-r">${f(c.gross)}</td><td class="ap-r">${has ? c.meter : "—"}</td><td class="ap-r">${f(c.kulit)}</td><td class="ap-r">${f(c.clamp)}</td><td>${delb("coil-del", c.id)}</td></tr>`; }).join(""),
            "Belum ada coil ditambahkan. Klik salah satu tombol di atas untuk menambah baby coil."),
          `<span><button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="coil-add" data-src="unpack">+ Coil From Unpack</button> <button type="button" class="ap-btn ap-btn--sm ap-btn--warning" data-act="coil-add" data-src="wip">+ Coil From WIP</button> <button type="button" class="ap-btn ap-btn--sm ap-btn--danger" data-act="coil-add" data-src="hold">+ Coil From Hold</button></span>`) +
        sec(3, "Finish Good KW 1 &amp; Stok Bebas", "KW 1 terisi dari SPK. Stok Bebas untuk sisa produksi di luar target SPK. Toleransi selisih aktual vs standar &plusmn; 0.7%.",
          subh("Produk KW 1 (dari SPK)", kw1.length, '<button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="fg-add" data-kind="kw1">+ Add Produk KW 1</button>') + (kw1.length ? kw1.map(fgCard).join("") : '<p class="ap-empty" style="text-align:center">Belum ada baris Produk KW 1 ditambahkan. Tambahkan baris material coil terlebih dahulu.</p>') +
          subh("Stok Bebas", beb.length, '<button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="fg-add" data-kind="bebas">+ Add Stok Bebas</button>') + (beb.length ? beb.map(fgCard).join("") : '<p class="ap-empty" style="text-align:center">Belum ada Stok Bebas ditambahkan. Tambahkan baris material coil terlebih dahulu.</p>'),
          '<span class="pr-hint">Maksimal baris mengikuti jumlah material coil</span>') +
        sec(4, "Finish Good KW 2", "Dipisah KW 2 Internal dan Supplier. Nama otomatis di-generate: {Produk}-KW2-{I/S}-01, ...",
          subh("KW 2 — Internal", null, '<button type="button" class="ap-btn ap-btn--sm ap-btn--secondary" data-act="kw2-add" data-t="internal">+ Add KW 2 Internal</button>') + kw2Table("internal") +
          subh("KW 2 — Supplier", null, '<button type="button" class="ap-btn ap-btn--sm ap-btn--secondary" data-act="kw2-add" data-t="supplier">+ Add KW 2 Supplier</button>') + kw2Table("supplier")) +
        sec(5, "Sisa Coil &amp; Hold Coil", "Sisa Coil masuk Gudang WIP. Baris Hold Coil otomatis masuk ke seksi Claim Material (FR-98).",
          subh("Sisa Coil", h.sisa.length, '<button type="button" class="ap-btn ap-btn--sm ap-btn--secondary" data-act="line-add" data-k="sisa">+ Add Sisa</button>') + lineTable("sisa") +
          subh("Hold Coil", h.hold.length, '<button type="button" class="ap-btn ap-btn--sm ap-btn--danger" data-act="line-add" data-k="hold">+ Add Hold</button>') + lineTable("hold") +
          (h.hold.length ? '<div class="pr-warn">Ada Hold Coil — baris klaim otomatis dibuat di <b>Card 7. Claim Material</b>. Anda dapat menyimpan laporan sebagai <b>Draft</b>, namun <b>Submit</b> dikunci sampai Purchasing memutuskan klaim tersebut (FR-18, FR-98).</div>' : "")) +
        sec(6, "Komponen Scrap", "Input berat scrap produksi (Tong, Wrapping, Potongan Pisau, serta Reject Internal &amp; Supplier).",
          `<div class="pr-grid3">${scrapIn("tong", "Tong Coil (kg)")}${scrapIn("wrapping", "Wrapping (kg)")}${scrapIn("pisau", "Potongan Pisau (kg)")}</div>` + subh("Reject Produksi &amp; Material") +
          tbl(["Kelompok", "Reject Product (kg)", "Reject Material Plat (kg)", "Keterangan Reject"], [["Internal", "Int"], ["Supplier", "Sup"]].map(([n, k]) => `<tr><td><b>${n}</b></td><td>${nin("scrap||rejProd" + k, s["rejProd" + k])}</td><td>${nin("scrap||rejMat" + k, s["rejMat" + k])}</td><td><input class="ap-in" data-f="scrap||rejKet${k}" placeholder="Catatan reject ${n.toLowerCase()}..." value="${esc(s["rejKet" + k])}"></td></tr>`).join("")) +
          `<div class="wh-total">Total Inventory Scrap: <strong id="scrapTotal">0,00 kg</strong></div>`) +
        sec(7, `Claim Material / Ajukan Claim <span class="pr-cnt" id="claimCount">0 Baris</span>`, "Baris Hold Coil, KW 2 (Supplier), dan Reject (Supplier) otomatis terdeteksi dan muncul di sini. Lengkapi Problem, Alasan Produksi, dan upload bukti berkas (maks. 5 MB/berkas).",
          `<div class="ap-tablewrap"><table class="ap-table ap-table--striped"><thead><tr>${["Asal", "Id Baby Coil", "Id Material", "Id Suplier", "No. PO", "No. Coil Packing List", "Berat (Kg)", "Harga Beli/Kg", "Costbook/Kg", "Price Per Unit PO", "Problem <span class='ap-req'>*</span>", "Alasan Produksi <span class='ap-req'>*</span>", "Bukti Pendukung <span class='ap-req'>*</span> (Maks 5MB)", "Status Claim"].map((x) => `<th>${x}</th>`).join("")}</tr></thead><tbody id="claimTbody"></tbody></table></div>
          <div class="pr-info"><b>Bukti Dokumen Pendukung Wajib:</b> Lampirkan minimal 1 berkas per baris claim (foto coil defect, video masalah, atau berita acara QC; maksimal 5 MB per berkas). Berkas akan disimpan bersama tiket claim ke Purchasing.</div>`, "", "cardClaim") +
        sec(8, "Summary — Packing List vs Aktual", "Total Net Weight Produksi vs Net Weight Packing List. Toleransi &plusmn; 0.7%.",
          `<div class="pr-sum">${[["Summary Finish Good", "sumFG"], ["Summary KW2", "sumKW2"], ["Summary Scrap Total", "sumScrap"], ["Summary Sisa Coil", "sumSisa"], ["Summary Hold Coil", "sumHold"], ["Summary Nett Weight Produksi", "sumNetProd", "acc"], ["Summary Nett Weight Packing List", "sumNetPack"], ["Summary Selisih", "sumSelisihKg"], ["Summary Selisih Percentage", "sumSelisihPct", "res"]].map((x) => `<div class="${x[2] || ""}"${x[2] === "res" ? ' id="sumBox"' : ""}><span>${x[0]}</span><b id="${x[1]}">0,00 kg</b></div>`).join("")}</div><p class="note" id="tolNote"></p>`) +
        `<section class="ap-card ap-card--shadow" id="auditCard"${h.confirmations.length ? "" : " hidden"}><div class="ap-card__head"><h5><span class="pr-num" style="background:#dc3545">9</span>Jejak Konfirmasi Selisih</h5><div class="pr-hint">Daftar konfirmasi selisih di luar toleransi &plusmn; 0,7% beserta identitas dan waktu konfirmasi.</div></div><div class="ap-card__body">${tbl(["No", "Objek", "% Selisih", "Pernyataan", "Dikonfirmasi Oleh", "Waktu"], auditRows(), "")}</div></section>` +
        `<div class="pr-actions"><span class="ap-hint" id="gateMsg"></span><span><button type="button" class="ap-btn ap-btn--secondary" data-act="save" data-d="1">Save Draft</button> <button type="button" class="ap-btn ap-btn--primary" data-act="save" data-d="0">Simpan &amp; Submit Laporan</button></span></div>`;
    },
    after() { if (R && !FATAL) refresh(); },
    act: {},
  });

  // Perbarui nilai turunan tanpa merender ulang (supaya kursor input tidak hilang)
  function refresh() {
    const r = W.recalc(R, SPK), set = (id, v) => { const e = $("#" + id); if (e) e.textContent = v; };
    R.fg.forEach((f) => {
      const q = (a) => $(`[${a}="${f.id}"]`), p1 = f.method === 1;
      const v = (a, t) => { const e = q(a); if (e) e.value = t; };
      v("data-fgd", n2(p1 ? f.perPcs : f.total)); v("data-fgs", f.std ? n2(f.std) : "-"); v("data-fgl", f.panjang ? n2(f.panjang) : "-"); v("data-fgm", f.beratPerMeterAktual ? n4(f.beratPerMeterAktual) : "-"); v("data-fgsm", f.stdPerMeter ? n4(f.stdPerMeter) : "-");
      const b = (a, x) => { const e = q(a); if (e) { e.textContent = n2(x) + " %"; e.className = "pr-sel " + (Math.abs(x) > TOL ? "bad" : "ok"); } };
      b("data-fgp", f.selisih); b("data-fgpm", f.selisihMeter);
      const c = q("data-confirm"); if (c) c.hidden = !isConfirmed(f);
    });
    ["internal", "supplier"].forEach((t) => R.kw2[t].forEach((k) => { const e = $(`[data-kw2p="${k.id}"]`); if (e) e.textContent = n2(k.qty > 0 ? k.total / k.qty : 0); }));
    set("sumFG", n2(r.fgTotal) + " kg"); set("sumKW2", n2(r.kw2Total) + " kg"); set("sumScrap", n2(r.scrap) + " kg"); set("sumSisa", n2(r.sisaTotal) + " kg"); set("sumHold", n2(r.holdTotal) + " kg");
    set("sumNetProd", n2(r.netProd) + " kg"); set("sumNetPack", n2(r.netPack) + " kg"); set("sumSelisihKg", n2(r.selisihKg) + " kg"); set("sumSelisihPct", n2(r.selisihPct) + " %"); set("scrapTotal", n2(r.scrap) + " kg");
    const bx = $("#sumBox"); if (bx) bx.className = "res" + (r.over ? " bad" : "");
    set("tolNote", r.over ? `Selisih ${n2(r.selisihPct)}% melebihi toleransi ± ${TOL}%. Perlu konfirmasi audit.` : `Toleransi ± ${TOL}% terhadap Net Weight Produksi.`);
    const g = $("#gateMsg"); if (g) g.textContent = R.hold.length ? "Ada Hold Coil — Simpan sebagai Draft. Submit menunggu keputusan Purchasing." : "Simpan Draft atau Submit Laporan.";
    const cb = $("#claimTbody"); if (cb) { cb.innerHTML = claimBody(); set("claimCount", W.claimRows(R).length + " Baris"); }
    return r;
  }

  const clearCoilRefs = (code) => { if (!code) return; R.fg.forEach((f) => { if (f.coil === code) f.coil = ""; }); ["internal", "supplier"].forEach((t) => R.kw2[t].forEach((k) => { if (k.coil === code) k.coil = ""; })); ["sisa", "hold"].forEach((k) => R[k].forEach((x) => { if (x.coil === code) x.coil = ""; })); };
  const stop = (title, text, html) => dialog({ icon: "warning", title, text, html });
  const need = (txt) => stop("Perhatian", txt);
  const findIn = (kind, id) => (kind === "fg" ? R.fg : kind === "kw2I" ? R.kw2.internal : kind === "kw2S" ? R.kw2.supplier : R[kind] || []).find((x) => x.id === id);

  Object.assign(P.input.act, {
    "coil-add"(d) { R.coils.push({ id: W.uid(), source: d.src, id_warehouse_stock_coil: null, code: "", material: "", nett: null, gross: null, meter: null, kulit: null, clamp: null }); if (!R.fg.some((f) => f.kind === "kw1") && SPK.products.length) addFg("kw1"); U.render(); },
    "coil-del"(d) { const c = R.coils.find((x) => x.id === d.id); clearCoilRefs(c && c.code); R.coils = R.coils.filter((x) => x.id !== d.id); U.render(); },
    "fg-add"(d) { addFg(d.kind, true); },
    "fg-del"(d) { R.fg = R.fg.filter((x) => x.id !== d.id); U.render(); },
    "kw2-add"(d) { R.kw2[d.t].push(W.kw2Row("", "", 0, 0, 0, "")); U.render(); },
    "kw2-del"(d) { R.kw2[d.t] = R.kw2[d.t].filter((x) => x.id !== d.id); U.render(); },
    "line-add"(d) {
      if (!maxC()) return need("Silakan tambahkan Baby Coil pada Sumber Material (Section 2) terlebih dahulu.");
      const label = d.k === "sisa" ? "Sisa Coil" : "Hold Coil";
      if (R[d.k].length >= maxC()) return need(`Jumlah baris ${label} (${R[d.k].length}) tidak boleh melebihi jumlah material coil (${maxC()} baris).`);
      R[d.k].push({ id: W.uid(), coil: "", berat: 0 }); U.render();
    },
    "line-del"(d) { R[d.k] = R[d.k].filter((x) => x.id !== d.id); U.render(); },
    "cl-delfile"(d) { const e = R.claimIn[d.key] || (R.claimIn[d.key] = {}); const row = W.claimRows(R).find((x) => x.key === d.key); e.bukti = (e.bukti || (row ? row.bukti : [])).slice(); e.bukti.splice(+d.i, 1); refresh(); },
    save(d) { submit(d.d === "1"); },
  });
  function addFg(kind, warnIfNone) {
    if (!maxC()) return need("Silakan tambahkan Baby Coil pada Sumber Material (Section 2) terlebih dahulu.");
    const cur = R.fg.filter((f) => f.kind === kind).length, label = kind === "kw1" ? "Produk KW 1" : "Stok Bebas";
    if (cur >= maxC()) return need(`Jumlah baris ${label} (${cur}) tidak boleh melebihi jumlah material coil (${maxC()} baris).`);
    const sp = kind === "kw1" ? SPK.products[0] : null;
    R.fg.push({ id: W.uid(), kind, productId: sp ? sp.id_product_lvl_4 : "", productName: sp ? sp.nm_produk_fg : "", targetQty: sp ? sp.target_qty : null, method: 1, qty: 0, total: 0, perPcs: 0, std: sp ? sp.weight_standard : 0, selisih: 0, coil: "" });
    U.render();
  }
  P.input.onField = (el, type) => {
    const [kind, id, key] = el.dataset.f.split("|"), v = U.isNumEl(el) ? (U.pv(el) || 0) : el.value;
    if (kind === "hdr") { R[key] = key === "helpers" ? Array.from(el.selectedOptions).map((o) => o.value) : (key === "id_asset_machine" ? v : v); return; }
    if (kind === "scrap") { R.scrap[key] = U.isNumEl(el) ? v : el.value; refresh(); return; }
    if (kind === "coil") {
      const row = R.coils.find((x) => x.id === id); if (!row || type === "input") return;
      const old = row.code; if (old === v) return;
      if (!v) Object.assign(row, { id_warehouse_stock_coil: null, code: "", material: "", nett: null, gross: null, meter: null, kulit: null, clamp: null });
      else { const c = W.coilByCode(v); Object.assign(row, { id_warehouse_stock_coil: c.id, code: c.no_coil, material: c.nm_material, nett: c.net_weight, gross: c.gross_weight, meter: c.total_meter, kulit: c.berat_kulit, clamp: c.berat_clamp }); }
      clearCoilRefs(old); U.render(); return;
    }
    if (kind === "cl") {
      const e = R.claimIn[id] || (R.claimIn[id] = {});
      if (key === "file") {
        const files = Array.from(el.files || []); if (!files.length) return;
        const b = (e.bukti || (W.claimRows(R).find((x) => x.key === id) || { bukti: [] }).bukti).slice();
        files.forEach((f) => { if (f.size > 5 * 1024 * 1024) dialog({ icon: "warning", title: "File Terlalu Besar", text: `Berkas "${f.name}" melebihi batas maksimal 5 MB dan tidak diunggah.` }); else b.push({ file_original_name: f.name, file_size: f.size }); });
        e.bukti = b; el.value = ""; refresh(); return;
      }
      e[key] = el.value; if (key === "coilCode") refresh(); return;
    }
    const row = findIn(kind, id); if (!row) return;
    if (kind === "fg") {
      if (key === "method") { row.method = +el.value; U.render(); return; }
      if (key === "productId") { row.productId = v; computeFgReset(row); U.render(); return; }
      if (key === "coil") { row.coil = v; U.render(); return; }
      if (key === "qty") row.qty = v; else if (key === "input") { if (row.method === 1) row.total = v; else row.perPcs = v; }
      refresh(); return;
    }
    if (kind === "kw2I" || kind === "kw2S") {
      if (key === "productId" || key === "coil") { row[key] = v; U.render(); return; }
      row[key] = key === "ket" ? el.value : v; refresh(); return;
    }
    if (kind === "sisa" || kind === "hold") { if (key === "coil") { row.coil = v; U.render(); return; } row.berat = v; refresh(); }
  };
  const computeFgReset = (f) => { f.confirmedAt = null; };

  /* Simpan Draft / Submit (form.php: submitReport) */
  async function submit(isDraft) {
    const r = refresh();
    if (!isDraft && R.hold.length > 0) return stop("Submit Laporan Dikunci", "Laporan memuat material Hold Coil yang menunggu keputusan Purchasing (FR-18, FR-98). Silakan simpan laporan ini sebagai Draft dengan menekan tombol Save Draft.");
    const errs = [];
    W.claimRows(R).forEach((c, i) => {
      const n = `Baris ${i + 1} (${c.asal})`;
      if (!c.coilCode) errs.push(n + ": Baby coil asal belum dipilih.");
      if (!String(c.problem || "").trim()) errs.push(n + ": Problem wajib diisi.");
      if (!String(c.alasan || "").trim()) errs.push(n + ": Alasan Produksi wajib diisi.");
      if (!c.bukti.length) errs.push(n + ": Bukti dokumen pendukung wajib diunggah minimal 1 berkas.");
    });
    if (errs.length) { await stop("Isian Claim Material Belum Lengkap", "", `<ul style="text-align:left;font-size:13px">${errs.map((e) => `<li>${esc(e)}</li>`).join("")}</ul>`); $("#cardClaim").scrollIntoView({ behavior: "smooth" }); return; }
    if (!isDraft) {
      const bad = (m) => { dialog({ icon: "warning", title: "Validasi Gagal", text: m }); return true; };
      if (!R.tgl_produksi) return bad("Tanggal Produksi wajib diisi.");
      if (!R.id_asset_machine) return bad("Mesin produksi wajib dipilih.");
      if (!R.helpers.length) return bad("Employee Helper wajib dipilih minimal 1 orang.");
      if (!R.setter) return bad("Employee Setter wajib dipilih.");
      if (!R.start_time) return bad("Start Time wajib diisi.");
      if (!R.finished_time) return bad("Finished Time wajib diisi.");
      if (!R.coils.length) return bad("Sumber Material (Add Coil) wajib ditambahkan minimal 1 baris.");
      for (let i = 0; i < R.coils.length; i++) if (!R.coils[i].code) return bad(`Baris ke-${i + 1} pada Sumber Material belum memilih Baby Coil.`);
      const kw1 = R.fg.filter((f) => f.kind === "kw1");
      if (!kw1.length) return bad("Produk KW 1 (dari SPK) wajib diisi minimal 1 baris.");
      for (let i = 0; i < kw1.length; i++) {
        if (!kw1[i].productId) return bad(`Baris ke-${i + 1} Produk KW 1 belum memilih Produk.`);
        if (!kw1[i].coil) return bad(`Baris ke-${i + 1} Produk KW 1 belum memilih Source Material Coil.`);
        if (!(kw1[i].qty > 0)) return bad(`Qty pada baris ke-${i + 1} Produk KW 1 harus lebih besar dari 0.`);
        if (!(kw1[i].total > 0)) return bad(`Berat Total pada baris ke-${i + 1} Produk KW 1 harus lebih besar dari 0.`);
      }
      const queue = [];
      R.fg.forEach((f) => { if (f.std > 0 && Math.abs(f.selisih) > TOL && !R.confirmations.some((c) => c.ref === f.id && c.selisih === f.selisih)) queue.push({ scope: "fg", ref: f.id, label: `${f.productName} (${f.kind === "bebas" ? "Stok Bebas" : "KW 1"})`, selisih: f.selisih, msg: `Produk "${f.productName}" memiliki selisih aktual ${n2(f.selisih)}% terhadap berat standar (${n2(f.std)} kg/pcs). Toleransi maksimum ± ${TOL}%.` }); });
      if (r.over && !R.confirmations.some((c) => c.ref === "summary" && Math.abs(c.selisih - r.selisihPct) < 0.01)) queue.push({ scope: "summary", ref: "summary", label: "Total Net Produksi vs Packing List", selisih: r.selisihPct, msg: `Total Net Weight Produksi (${n2(r.netProd)} kg) memiliki selisih ${n2(r.selisihPct)}% terhadap Net Packing List (${n2(r.netPack)} kg). Melebihi toleransi ± ${TOL}%.` });
      for (const it of queue) {
        const ok = await dialog({ icon: "warning", title: "Konfirmasi Selisih Di Luar Toleransi", text: it.msg, html: `<p style="font-size:13px">Konfirmasi dicatat atas nama: <b>${esc(USER.name)}</b></p>`, ok: "Sudah Sesuai Aktual", cancel: "Perbaiki Data" });
        if (!ok) return dialog({ icon: "warning", title: "Perhatian", text: "Penyimpanan dibatalkan. Silakan sesuaikan data aktual." });
        R.confirmations = R.confirmations.filter((c) => c.ref !== it.ref);
        R.confirmations.push({ id: W.uid(), scope: it.scope, ref: it.ref, label: it.label, selisih: it.selisih, statement: "Sudah sesuai aktual", user: USER.name, role: USER.role, tsLabel: new Date().toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" }) });
        if (it.scope === "fg") { const f = R.fg.find((x) => x.id === it.ref); if (f) f.confirmedAt = f.selisih; }
      }
    }
    const res = W.saveReport(R, isDraft);
    if (res.status !== 1) return stop("Gagal", res.message);
    await done(res.message + (isDraft ? " Disimpan sebagai Draft." : " Laporan final: stok & HPP dibukukan (lihat menu HPP)."));
    location.href = "pr-daftar.html";
  }

  /* ======================= 3. Detail Laporan (production_report/view) ======================= */
  const repOrFail = () => { const r = W.reportById(qp.get("id")); return r || null; };
  const noRep = '<div class="pr-warn">Laporan produksi tidak ditemukan.</div><p><a class="ap-btn ap-btn--secondary" href="pr-daftar.html">Kembali ke Daftar</a></p>';
  const CAT = { kw_1: "KW 1", stok_bebas: "STOK BEBAS", kw_2_internal: "KW 2 INTERNAL", kw_2_supplier: "KW 2 SUPPLIER" };
  P.lihat = Object.assign({}, base, {
    title: "Detail Laporan Produksi", hint: "Hasil laporan produksi per SPK (baca-saja): bahan baku, hasil produksi, scrap/sisa/hold, claim material, dan rekonsiliasi berat.",
    render() {
      const rep = repOrFail(); if (!rep) return noRep;
      const spk = W.spkOf(rep.spk_no), r = W.recalc(W.deep(rep), spk), fin = !rep.status_draft, mach = W.MACHINES.find((m) => String(m.id) === String(rep.id_asset_machine));
      const dd = W.deep(rep); const calc = W.computeHpp(dd, spk), s = rep.scrap, stdSel = (v) => (Math.abs(v) > TOL ? "wh-diff-bad" : "wh-diff-ok");
      const its = calc.items.map((i) => { const f = rep.fg.find((x) => x.coil === i.source_material_coil && x.productName === i.nama_product_custom && (i.category_type === "kw_1" || i.category_type === "stok_bebas")); const kw1 = i.category_type === "kw_1" || i.category_type === "stok_bebas";
        const fx = kw1 ? (dd.fg.find((x) => x.coil === i.source_material_coil && x.productName === i.nama_product_custom) || {}) : {}; return Object.assign({}, i, { std: fx.std || 0, selisih: fx.selisih || 0, bpm: fx.beratPerMeterAktual || 0, spm: fx.stdPerMeter || 0, selm: fx.selisihMeter || 0 }); });
      return `<div class="pr-top"><div class="pr-sub">Nomor SPK: <b>${esc(rep.spk_no)}</b> &bull; Tgl Produksi: ${fdate(rep.tgl_produksi)}</div><div><span class="ap-label ap-label--${fin ? "success" : "warning"}">${fin ? "Submitted (Done)" : "Draft"}</span>
        ${fin ? `<a class="ap-btn ap-btn--sm ap-btn--primary" href="pr-hpp.html?id=${rep.id}">Laporan HPP</a> ${btn("success", "export", rep.id, "Export Report")}` : ""} <a class="ap-btn ap-btn--sm ap-btn--secondary" href="pr-daftar.html">Kembali ke Daftar</a></div></div>` +
        card("Informasi Laporan &amp; Operator", `<div class="pr-meta"><div><span>Id Asset Machine</span><b>${mach ? esc(mach.nm_asset + " (" + mach.kd_asset + ")") : "-"}</b></div><div><span>Employee Helper</span><b>${esc(rep.helpers.join(", ") || "-")}</b></div><div><span>Employee Setter</span><b>${esc(rep.setter || "-")}</b></div><div><span>Start Time / Finished Time</span><b>${esc(rep.start_time || "-")} s/d ${esc(rep.finished_time || "-")}</b></div></div>`) +
        card("Baby Coil Terpakai (Bahan Baku)", tbl(["Source Warehouse", "Coil Code", "Material Name", "Nett Weight Packing (Kg)", "Gross Weight (Kg)", "Total Meter", "Berat Kulit (Kg)", "Berat Clamp (Kg)"], rep.coils.map((c) => `<tr><td><span class="ap-label ap-label--light">${c.source.toUpperCase()}</span></td><td><b>${esc(c.code)}</b></td><td>${esc(c.material)}</td><td class="ap-r">${n2(c.nett)}</td><td class="ap-r">${n2(c.gross)}</td><td class="ap-r">${n2(c.meter)}</td><td class="ap-r">${n2(c.kulit)}</td><td class="ap-r">${n2(c.clamp)}</td></tr>`).join(""), "Tidak ada data material coil.")) +
        card("Hasil Produksi (KW 1, Stok Bebas, KW 2)", tbl(["Category Type", "Nama Product Custom", "Source Material Coil", "Qty", "Berat Total (Kg)", "Berat Per Pcs (Kg)", "Berat Standard (Kg)", "Percentage Selisih", "Berat Per Meter Aktual", "Berat Per Meter Standard", "Percentage Selisih Meter", "Keterangan"],
          its.map((i) => `<tr><td><span class="ap-label ap-label--${i.category_type === "kw_1" ? "info" : i.category_type === "stok_bebas" ? "warning" : "secondary"}">${CAT[i.category_type]}</span></td><td><b>${esc(i.nama_product_custom)}</b></td><td>${esc(i.source_material_coil)}</td><td class="ap-r">${num(i.qty, 0)}</td><td class="ap-r"><b>${n2(i.berat_total)}</b></td><td class="ap-r">${n2(i.berat_per_pcs)}</td><td class="ap-r">${n2(i.std)}</td><td class="ap-r ${stdSel(i.selisih)}">${n2(i.selisih)} %</td><td class="ap-r">${i.bpm > 0 ? n4(i.bpm) : "-"}</td><td class="ap-r">${i.spm > 0 ? n4(i.spm) : "-"}</td><td class="ap-r ${i.spm > 0 ? stdSel(i.selm) : ""}">${i.spm > 0 ? n2(i.selm) + " %" : "-"}</td><td>${esc(i.keterangan || "-")}</td></tr>`).join(""), "Tidak ada data hasil produksi.")) +
        (calc.scraps.length ? card("Komponen Scrap, Sisa, &amp; Hold Coil", tbl(["Scrap Type", "Target Coil Code", "Berat Total (Kg)", "Keterangan"], calc.scraps.map((x) => `<tr><td><span class="ap-label ap-label--light">${esc(W.NAMA_SCRAP[x.scrap_type])}</span></td><td>${esc(x.target_coil_code || "-")}</td><td class="ap-r"><b>${n2(x.berat_total)}</b></td><td>${esc(x.keterangan || "-")}</td></tr>`).join(""))) : "") +
        (rep.claims.length ? card("Claim Material Yang Diajukan (FR-94)", tbl(["No. Claim", "Asal", "No. Coil Packing List", "Id Material", "Id Suplier", "No. PO", "Berat (Kg)", "Problem", "Alasan Produksi", "Bukti Pendukung", "Status Claim"], rep.claims.map((c) => `<tr><td><b>${esc(c.no_claim)}</b></td><td><span class="ap-label ap-label--${{ "Hold Coil": "danger", "KW 2 (Supplier)": "warning" }[c.asal] || "dark"}">${esc(c.asal)}</span></td><td>${esc(c.no_coil_packing_list || "-")}</td><td>${esc(c.id_material || "-")}</td><td>${esc(c.id_suplier || "-")}</td><td>${esc(c.no_po || "-")}</td><td class="ap-r"><b>${n2(c.berat_kg)}</b></td><td>${esc(c.problem || "-")}</td><td>${esc(c.alasan_produksi || "-")}</td><td>${(c.bukti || []).map((b) => `<div><span class="pr-chip">&#128206; ${esc(b.file_original_name)}</span></div>`).join("") || "-"}</td><td><span class="ap-label ap-label--info">${esc(c.status_claim)}</span></td></tr>`).join(""), ""), `<span class="ap-label ap-label--warning">${rep.claims.length} Claim</span>`) : "") +
        card("Ringkasan &amp; Rekonsiliasi Berat", `<div class="pr-sum" style="grid-template-columns:repeat(4,minmax(0,1fr))"><div class="acc"><span>Summary Nett Weight Produksi</span><b>${n2(r.netProd)} kg</b></div><div><span>Summary Nett Weight Packing List</span><b>${n2(r.netPack)} kg</b></div><div><span>Summary Selisih</span><b>${n2(r.selisihKg)} kg</b></div><div class="${Math.abs(r.selisihPct) > TOL ? "bad" : ""}"><span>Summary Selisih Percentage</span><b style="color:${Math.abs(r.selisihPct) > TOL ? "#b91c1c" : "#15803d"}">${n2(r.selisihPct)} %</b></div></div>`);
    },
    act: { export() { toast("Export Report Excel (mockup, tidak membuat berkas nyata)."); } },
  });

  /* ======================= 4. Laporan HPP (production_report/hpp) ======================= */
  const HC = ["material", "mp", "foh", "bahan", "cons", "koor", "scrap"], HN = ["Material", "Main Power", "FOH", "Bahan Pendukung", "Consumables", "Coordinator", "Scrap"], HPCS = HN.map((x) => x + " Cost/Pcs"), HTOT = HN.map((x) => x + " Cost Total");
  P.hpp = Object.assign({}, base, {
    title: "Laporan Harga Pokok Produksi (HPP)", hint: "Rincian Komponen Biaya Produksi (Read-Only).",
    render() {
      const rep = repOrFail(); if (!rep) return noRep;
      const spk = W.spkOf(rep.spk_no), hp = rep.hpp || W.computeHpp(W.deep(rep), spk), mach = W.MACHINES.find((m) => String(m.id) === String(rep.id_asset_machine));
      const kw1 = hp.items.filter((i) => i.category_type === "kw_1" || i.category_type === "stok_bebas"), kwi = hp.items.filter((i) => i.category_type === "kw_2_internal"), kws = hp.items.filter((i) => i.category_type === "kw_2_supplier");
      const waste = hp.scraps.filter((s) => s.scrap_type !== "sisa_coil" && s.scrap_type !== "hold_coil"), sisa = hp.scraps.filter((s) => s.scrap_type === "sisa_coil"), hold = hp.scraps.filter((s) => s.scrap_type === "hold_coil");
      const prodCell = (i) => `<b>${esc(i.nama_product_custom)}</b>${i.category_type === "stok_bebas" ? ' <span class="ap-label ap-label--warning">Bebas</span>' : ""}<br><span class="pr-tag">${esc(i.no_uniq_id)}</span>`;
      const tot = (k, list) => list.reduce((a, i) => a + i.tot[k], 0);
      const kw1Table = `<div class="ap-tablewrap"><table class="ap-table ap-table--striped pr-hpp"><thead><tr><th rowspan="2">No</th><th rowspan="2">Produk<br><small>/ Varian BOM (no_uniq_id)</small></th><th rowspan="2">Source Material Coil</th><th rowspan="2">Berat Total (Kg)</th><th rowspan="2">Berat Per Pcs</th><th rowspan="2">Qty</th><th rowspan="2">Costbook Rate</th><th colspan="8" class="grp">Cost Per Pcs (Rp)</th><th colspan="8" class="grp-t">Cost Total (Rp)</th></tr>
        <tr>${HPCS.map((x) => `<th>${x}</th>`).join("")}<th>Value/Pcs</th>${HTOT.map((x) => `<th>${x}</th>`).join("")}<th class="grp-t">Total HPP Item</th></tr></thead><tbody>${kw1.length ? kw1.map((i, n) => `<tr><td>${n + 1}</td><td>${prodCell(i)}</td><td><small>${esc(i.source_material_coil)}</small></td><td class="ap-r">${n2(i.berat_total)}</td><td class="ap-r">${n2(i.berat_per_pcs)}</td><td class="ap-r"><b>${num(i.qty, 0)}</b></td><td class="ap-r">${rpf(i.costbook_rate)}</td>
          ${HC.map((k) => `<td class="ap-r">${rpf(i.per[k])}</td>`).join("")}<td class="ap-r"><b>${rpf(HC.reduce((a, k) => a + i.per[k], 0))}</b></td>${HC.map((k) => `<td class="ap-r">${rpf(i.tot[k])}</td>`).join("")}<td class="ap-r grp-t">${rpf(i.total_hpp_item)}</td></tr>`).join("") : '<tr><td class="ap-empty" colspan="23">Tidak ada data KW 1 / Stok Bebas.</td></tr>'}</tbody>
        ${kw1.length ? `<tfoot><tr class="wh-foot"><td colspan="15" class="ap-r">Total Biaya Finish Good (Rp) &rarr;</td>${HC.map((k) => `<td class="ap-r">${rpf(tot(k, kw1))}</td>`).join("")}<td class="ap-r grp-t">${rpf(kw1.reduce((a, i) => a + i.total_hpp_item, 0))}</td></tr></tfoot>` : ""}</table></div>`;
      const kw2Table2 = (list, empty) => `<div class="ap-tablewrap"><table class="ap-table ap-table--striped pr-hpp"><thead><tr><th rowspan="2">No</th><th rowspan="2">Produk KW 2<br><small>/ Varian BOM (no_uniq_id)</small></th><th rowspan="2">Source Material Coil</th><th rowspan="2">Berat Total (Kg)</th><th rowspan="2">Qty</th><th rowspan="2">Costbook Rate</th><th colspan="7" class="grp">Cost Total (Rp)</th><th rowspan="2" class="grp-t">Total HPP Item</th><th rowspan="2" class="pr-pos">Inventory Value 80</th><th rowspan="2" class="pr-neg">Loss Cost 20</th></tr><tr>${HTOT.map((x) => `<th>${x}</th>`).join("")}</tr></thead><tbody>${list.length ? list.map((i, n) => `<tr><td>${n + 1}</td><td>${prodCell(i)}</td><td><small>${esc(i.source_material_coil)}</small></td><td class="ap-r">${n2(i.berat_total)}</td><td class="ap-r">${num(i.qty, 0)}</td><td class="ap-r">${rpf(i.costbook_rate)}</td>${HC.map((k) => `<td class="ap-r">${rpf(i.tot[k])}</td>`).join("")}<td class="ap-r grp-t">${rpf(i.total_hpp_item)}</td><td class="ap-r pr-pos">${rpf(i.inventory_value_80)}</td><td class="ap-r pr-neg">${rpf(i.loss_cost_20)}</td></tr>`).join("") : `<tr><td class="ap-empty" colspan="16">${empty}</td></tr>`}</tbody></table></div>`;
      const small = (title, list, totLabel, totVal, cls) => card(title, tbl(["Target Coil Code", "Berat Total (Kg)", "Total Nilai"], list.map((s) => `<tr><td><small>${esc(s.target_coil_code)}</small></td><td class="ap-r">${n2(s.berat_total)}</td><td class="ap-r ${cls}">${rpf(s.total_nilai)}</td></tr>`).join(""), "— Tidak ada —", `<tr class="wh-foot"><td colspan="2" class="ap-r">${totLabel}</td><td class="ap-r ${cls}">${rpf(totVal)}</td></tr>`));
      const sel = hp.summary.selisihKg;
      return `<div class="pr-top"><div></div><div><a class="ap-btn ap-btn--sm ap-btn--secondary" href="pr-lihat.html?id=${rep.id}">Lihat Laporan Produksi</a> <a class="ap-btn ap-btn--sm ap-btn--secondary" href="pr-daftar.html">Kembali ke Daftar</a></div></div>
        <div class="pr-meta"><div><span>SPK No</span><b style="color:#2563eb">${esc(rep.spk_no)}</b></div><div><span>Tgl Produksi</span><b>${fdate(rep.tgl_produksi)}</b></div><div><span>Id Asset Machine</span><b>${mach ? esc(mach.nm_asset) : "-"}</b></div><div><span>Total COGM</span><b style="color:#15803d">${rpf(hp.total_cogm)}</b></div><div><span>Total Inventory FG</span><b style="color:#2563eb">${rpf(hp.total_inventory_fg)}</b></div><div><span>Status</span><b><span class="ap-label ap-label--${rep.status_draft ? "warning" : "success"}">${rep.status_draft ? "Draft" : "Final"}</span></b></div></div>` +
        card('<span class="pr-num">1</span>HPP — Finish Good KW 1 &amp; Stok Bebas', `<div class="pr-info" style="margin:0 0 10px"><b>Rate HPP dari Hasil Costing:</b> Komposisi biaya (Man Power, FOH, Bahan Pendukung Khusus, Consumables, Biaya Koordinasi, dan Scrap) dihitung menggunakan <b>Rp/Kg dari Hasil Costing Varian BOM</b> yang dipilih SPK, dan <b>dibekukan</b> saat laporan disimpan (FR-48, FR-49). Material = Berat/Pcs × Costbook coil.</div>${kw1Table}`) +
        card('<span class="pr-num">2</span>HPP — Finish Good KW 2', `<h6>KW 2 — Internal <span class="ap-label ap-label--info">Inventory 80% &bull; Biaya Loss 20%</span></h6>${kw2Table2(kwi, "Tidak ada KW 2 Internal.")}<h6 style="margin-top:14px">KW 2 — Supplier <span class="ap-label ap-label--info">Inventory 80% &bull; Biaya Loss 20%</span></h6>${kw2Table2(kws, "Tidak ada KW 2 Supplier.")}`) +
        card('<span class="pr-num">3</span>Komponen Scrap Waste — Inventory Value (40%) &bull; Beban FOH (60%)', tbl(["No", "Scrap Type", "Target Coil Code", "Berat Total (Kg)", "Costbook Rate", "Total Nilai", "Inventory Value 40", "FOH Burden 60"], waste.map((s, n) => `<tr><td>${n + 1}</td><td><b>${esc(W.NAMA_SCRAP[s.scrap_type])}</b></td><td>${esc(s.target_coil_code || "-")}</td><td class="ap-r">${n2(s.berat_total)}</td><td class="ap-r">${rpf(s.costbook_rate)}</td><td class="ap-r"><b>${rpf(s.total_nilai)}</b></td><td class="ap-r pr-pos">${rpf(s.inventory_value_40)}</td><td class="ap-r pr-neg">${rpf(s.foh_burden_60)}</td></tr>`).join(""), "Tidak ada komponen scrap.",
          `<tr class="wh-foot"><td colspan="5" class="ap-r">Total Scrap &rarr;</td><td class="ap-r">${rpf(hp.total_inventory_scrap + hp.total_beban_foh_scrap)}</td><td class="ap-r pr-pos">${rpf(hp.total_inventory_scrap)}</td><td class="ap-r pr-neg">${rpf(hp.total_beban_foh_scrap)}</td></tr>`)) +
        `<div class="pr-two">${small('<span class="pr-num">4</span>Sisa Coil → WIP (100%)', sisa, "Total WIP:", hp.total_inventory_sisa, "pr-pos")}${small('<span class="pr-num">5</span>Hold Coil → Hold (100%)', hold, "Total Hold:", hp.total_inventory_hold, "")}
        ${card('<span class="pr-num">6</span>Selisih Berat Audit Material', `<div style="display:flex;justify-content:space-between;margin-bottom:8px"><span class="ap-hint">Selisih Aktual vs Pack:</span><b>${n2(sel)} kg (${n2(hp.summary.selisihPct)}%)</b></div><div style="display:flex;justify-content:space-between;margin-bottom:8px"><span class="ap-hint">Status Audit:</span><span class="ap-label ap-label--${sel < 0 ? "success" : "danger"}">${sel < 0 ? "Favorable" : "Unfavorable"}</span></div><div class="pr-sum" style="grid-template-columns:1fr"><div><span>Nilai Rupiah Selisih Material</span><b class="${hp.selisih_weight_value < 0 ? "pr-pos" : "pr-neg"}">${rpf(hp.selisih_weight_value)}</b></div></div>`)}</div>
        <p class="note">Aturan hitung (Production_report_model): 6 komponen non-material = Berat/Pcs × Rp/Kg Hasil Costing varian BOM; KW 2 → 80% persediaan / 20% biaya loss; scrap waste → 40% persediaan / 60% beban FOH; sisa coil → WIP 100%; hold coil → Hold 100%; nilai selisih material = selisih kg × costbook coil. Jurnal GL (template JV016 via Master Jurnal Mapping) diposting sekali saat Submit; tampilan jurnal tidak ada di halaman ini pada aplikasi.</p>`;
    },
  });

  U.init(P);
})();
