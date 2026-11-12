/* ============================================================
   wh-pages2.js (bagian B) — SPK Material, Request List (SPK Coil), Stock From Warehouse, Unpack Coil.
   Acuan modul aplikasi: spk_material, request_list, stock_from_warehouse, unpack_coil (hanya dibaca).
   Status SPK dan alur: Material Requested -> Material On Load -> Material Confirmed -> Released | Cancelled.
   ============================================================ */
(() => {
  "use strict";
  const U = window.WHUI, W = U.W, S = () => W.state, P = window.WHP;
  const { $, esc, num, fdate, lab, btn, tbl, card, stat, kv, row, inp, sel, txt, isNumEl, pv, tabs, toast, warn, done, confirmThen, openModal, closeModal, printDoc, ppHead, ppMeta, ppSign } = U;
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
    printDoc("SPK Material", ppHead("SURAT PERINTAH KERJA", s.spk_no) + ppMeta([["Tgl SPK", fdate(s.tgl_spk)], ["Due Date", fdate(s.due_date)], ["Shift Names", esc(s.shift_names.join(", "))], ["Catatan", esc(s.catatan || "-")], ["Status", esc(s.status)]]) +
      `<table class="pp-tbl"><thead><tr><th>No</th><th>Id Produk FG</th><th>Nama Produk FG</th><th class="pp-r">Target Qty</th><th class="pp-r">Berat Per Unit (Kg)</th><th class="pp-r">Total Weight (Kg)</th></tr></thead><tbody>${s.detail.map((d, i) => `<tr><td>${i + 1}</td><td>${esc(d.id_produk_fg)}</td><td>${esc(d.nm_produk_fg)}</td><td class="pp-r">${num(d.target_qty, 0)}</td><td class="pp-r">${num(d.berat_per_unit, 4)}</td><td class="pp-r">${num(d.total_weight)}</td></tr>`).join("")}</tbody></table>` +
      `<table class="pp-tbl"><thead><tr><th>Nama Material</th><th class="pp-r">Qty Needed (Kg)</th></tr></thead><tbody>${needsOf(s).map((n) => `<tr><td>${esc(mn(n.mid))}</td><td class="pp-r">${num(n.need)}</td></tr>`).join("")}</tbody></table>` + ppSign(["Dibuat (PPIC)", "Disetujui", "Diterima (Produksi)"]));
  }
  P.spk = {
    title: "SPK Material", mod: "spk_material", role: "PPIC / Produksi", ui: { mode: "list", id: null },
    hint: "Surat Perintah Kerja material: produk FG, target qty, kebutuhan material dari BOM, dan status SPK.",
    render() {
      const u = U.ui;
      if (u.mode === "form") return formView();
      if (u.mode === "view") { const s = SPK(u.id), edt = ["Material Requested", "Material Confirmed"].includes(s.status);
        return card("View SPK — " + esc(s.spk_no), spkHeaderKv(s) + '<div class="wh-sec">Daftar Produk</div>' + prodTable(s) + '<div class="wh-sec">Kebutuhan Material (BOM)</div>' + tbl(["Nama Material", "Qty Needed (Kg)"], needsOf(s).map((n) => `<tr><td>${esc(mn(n.mid))}</td><td class="ap-r">${num(n.need)}</td></tr>`).join("")) +
          `<div class="cl-tools" style="margin-top:12px"><button type="button" class="ap-btn ap-btn--outline-warning" data-act="back">&#8592; Kembali</button>${edt ? btn("warning", "edit", s.spk_no, "Edit") : ""}${btn("secondary", "pdf", s.spk_no, "Print PDF")}${s.status === "Material Confirmed" ? btn("success", "setst", s.spk_no, "Release", ' data-st="Released"') : ""}${edt ? btn("danger", "setst", s.spk_no, "Cancel SPK", ' data-st="Cancelled"') : ""}</div>`); }
      const l = S().spk.slice().sort((a, b) => b.tgl_spk.localeCompare(a.tgl_spk) || b.spk_no.localeCompare(a.spk_no));
      return `<div class="cl-sumrow">${stat(l.filter((s) => s.status === "Material Requested").length, "Material Requested")}${stat(l.filter((s) => s.status === "Material Confirmed").length, "Material Confirmed")}${stat(l.filter((s) => s.status === "Released").length, "Released")}${stat(l.filter((s) => s.status === "Cancelled").length, "Cancelled")}</div>` +
        card("Daftar SPK Material", `<div class="cl-tools"><span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="add">+ Add SPK</button></span></div>` +
          tbl(["No", "SPK No", "Tgl SPK", "Shift Names", "Status", "Jumlah Produk", "Actions"], l.map((s, i) => `<tr><td>${i + 1}</td><td>${esc(s.spk_no)}</td><td>${fdate(s.tgl_spk)}</td><td>${esc(s.shift_names.join(", "))}</td><td>${lab(s.status)}</td><td class="ap-r">${s.detail.length}</td><td class="ap-act ap-act-nowrap">${btn("info", "view", s.spk_no, "View")}${btn("secondary", "pdf", s.spk_no, "PDF")}</td></tr>`).join(""))) +
        '<p class="note">Status berikutnya (Material On Load, Material Confirmed) berubah lewat menu Request List saat pack dipilih dan dikonfirmasi. SPK berstatus Material Requested / Material Confirmed masih boleh di-edit (rule is_editable di halaman View).</p>';
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
      setst(d, b) { const s = SPK(d.id), st = b.dataset.st; confirmThen({ title: st === "Released" ? "Release SPK?" : "Cancel SPK?", text: "Status SPK " + s.spk_no + " berubah dari " + s.status + " menjadi " + st + ". Status akhir tidak dapat diubah lagi." + (st === "Cancelled" && s.spkc ? " SPK Coil yang sudah dibuat tidak ikut berubah posisi stoknya." : ""), ok: "Ya, " + (st === "Released" ? "Release" : "Cancel") }, () => { s.status = st; W.save(); U.render(); toast("Status SPK menjadi " + st + ".", "ok"); }); },
      add() { ff = { id: null, tgl_spk: W.today(), due_date: "", shifts: [], catatan: "", detail: [newDetail()] }; U.ui.mode = "form"; U.render(); },
      edit(d) { const s = SPK(d.id); ff = { id: s.spk_no, tgl_spk: s.tgl_spk, due_date: s.due_date || "", shifts: s.shift_names.slice(), catatan: s.catatan, detail: s.detail.map((x) => ({ id_produk_fg: x.id_produk_fg, target_qty: x.target_qty })) }; U.ui.mode = "form"; U.render(); },
      addrow() { ff.detail.push(newDetail()); U.render(); },
      delrow(d) { if (ff.detail.length === 1) { warn("Minimal satu produk."); return; } ff.detail.splice(+d.id, 1); U.render(); },
      bom(d) { bomModal(ff.detail[+d.id]); },
      save() {
        if (!ff.tgl_spk) { warn("Tanggal SPK wajib diisi."); return; }
        if (!ff.shifts.length) { warn("Shift wajib dipilih (minimal satu)."); return; }
        const det = ff.detail.map(dOf);
        if (det.some((x) => !x || x.target_qty < 1)) { warn("Setiap baris produk wajib memilih Produk dan Target Qty minimal 1."); return; }
        if (new Set(det.map((x) => x.id_produk_fg)).size !== det.length) { warn("Produk yang sama tidak boleh dipilih dua kali dalam satu SPK."); return; }
        if (ff.id) { const s = SPK(ff.id); Object.assign(s, { tgl_spk: ff.tgl_spk, due_date: ff.due_date, shift_names: ff.shifts, catatan: ff.catatan, detail: det }); }
        else S().spk.push({ spk_no: W.nextNo("spk"), tgl_spk: ff.tgl_spk, due_date: ff.due_date, shift_names: ff.shifts, catatan: ff.catatan, status: "Material Requested", detail: det, spkc: null });
        W.save(); U.ui.mode = "list"; U.render(); done(ff.id ? "SPK berhasil diperbarui." : "SPK berhasil dibuat dengan status Material Requested.");
      },
    },
  };
  function formView() {
    const rows = ff.detail.map((x, i) => { const d = dOf(x);
      return `<tr><td>${i + 1}</td><td><select class="ap-sel ap-in" data-f="prod" data-i="${i}"><option value="">-- Pilih Produk --</option>${W.PRODUK.filter((p) => p.aktif || p.id === x.id_produk_fg).map((p) => `<option value="${p.id}"${x.id_produk_fg === p.id ? " selected" : ""}>${esc(p.id + " — " + p.nm)}</option>`).join("")}</select></td>
        <td><input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="qty" data-i="${i}" value="${esc(NumFmt.fmt(x.target_qty))}" placeholder="0"></td><td class="ap-r">${d ? num(d.berat_per_unit, 4) : "-"}</td><td class="ap-r" id="tw${i}">${d ? num(d.total_weight) : "0,00"}</td>
        <td class="ap-act ap-act-nowrap">${btn("info", "bom", i, "Detail Material (BOM)")}${btn("danger", "delrow", i, "Hapus")}</td></tr>`; }).join("");
    return card(ff.id ? "Edit SPK — " + esc(ff.id) : "Add SPK Material", `<div class="wh-grid2">${row("Tgl SPK", `<input class="ap-in" type="date" data-f="tgl_spk" value="${esc(ff.tgl_spk)}">`, 1)}${row("Due Date", `<input class="ap-in" type="date" data-f="due_date" value="${esc(ff.due_date)}"><div class="ap-hint">Tanggal batas penyelesaian (opsional).</div>`)}</div>` +
      row("Shift Names", W.SHIFT.map((s) => `<label style="display:inline-block;margin-right:16px"><input type="checkbox" data-f="shift" value="${esc(s)}"${ff.shifts.includes(s) ? " checked" : ""}> ${esc(s)}</label>`).join(""), 1) +
      row("Catatan", txt("catatan", ff.catatan, { ph: "Catatan tambahan (opsional)", rows: 2 }).replace("<textarea", "<textarea").replace('data-f="catatan"', 'data-f="catatan"')) +
      '<div class="wh-sec">Daftar Produk</div><p class="ap-hint" style="margin:0 0 6px">Produk diambil dari <a class="ap-link" href="pc-produk.html">Product Costing &gt; Master Produk</a> (hanya produk Aktif yang sudah punya BOM material gudang).</p>' + tbl(["No", "Produk FG", "Target Qty", "Berat Per Unit (Kg)", "Total Weight (Kg)", "Action"], rows) +
      `<div class="cl-tools" style="margin-top:10px"><button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="addrow">+ Tambah Produk</button><span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--outline-warning" data-act="cancel">Batal</button> <button type="button" class="ap-btn ap-btn--success" data-act="save">${ff.id ? "Update SPK" : "Create SPK"}</button></span></div>`);
  }

  /* ======================= 6. Request List — SPK Coil (request_list) ======================= */
  const LISTED = ["Material Requested", "Material On Load", "Material Confirmed"];
  function viewSpkc(s) {
    const pk = s.spkc ? s.spkc.packs.map(packInfo).filter(Boolean) : [];
    openModal({ title: "View SPK Coil Detail — " + s.spk_no, size: "xl", hdr: "info",
      foot: s.status === "Material On Load" ? btn("danger", "delspkc", s.spk_no, "Hapus SPK Coil") : "",
      act: {
        rmpack(d) { confirmThen({ title: "Hapus pack dari SPK Coil?", text: "Pack " + d.id + " dikeluarkan dari SPK Coil " + s.spkc.no + (s.spkc.packs.length === 1 ? ". Ini pack terakhir, SPK Coil ikut dihapus dan status kembali Material Requested." : "."), ok: "Ya, Hapus" }, () => {
          s.spkc.packs = s.spkc.packs.filter((c) => c !== d.id); s.spkc.scanned = s.spkc.scanned.filter((c) => c !== d.id);
          if (!s.spkc.packs.length) { s.spkc = null; s.status = "Material Requested"; W.save(); closeModal(); U.render(); toast("SPK Coil dihapus; status kembali Material Requested.", "ok"); return; }
          W.save(); U.drawModal(); U.render(); toast("Pack dihapus dari SPK Coil.", "ok"); }); },
        delspkc() { confirmThen({ title: "Hapus SPK Coil?", text: "SPK Coil " + s.spkc.no + " dihapus dan status SPK kembali Material Requested.", ok: "Ya, Hapus" }, () => { s.spkc = null; s.status = "Material Requested"; W.save(); closeModal(); U.render(); toast("SPK Coil dihapus; status kembali Material Requested.", "ok"); }); },
      }, body: () => spkHeaderKv(s) + (s.spkc ? kv([["No. SPK Coil", esc(s.spkc.no)], ["Tgl SPK Coil", fdate(s.spkc.date)], ["Total Berat Dibutuhkan", num(needsOf(s).reduce((a, n) => a + n.need, 0)) + " Kg"]]) : '<p class="note">Belum ada SPK Coil: pack belum dipilih (Manage Request Pack).</p>') +
      s.detail.map((d) => `<div class="wh-prod"><div class="wh-prod__h"><span>${esc(d.nm_produk_fg)}</span><span>Target ${num(d.target_qty, 0)} pcs</span></div><div class="wh-prod__b">${bomOf(d).map((b) => {
        const mine = pk.filter((p) => p.mats.includes(b.mid));
        return `<div class="wh-need${mine.length ? " is-ok" : ""}"><b>${esc(mn(b.mid))}</b> — kebutuhan ${num(b.need)} Kg, terpilih ${num(mine.reduce((a, p) => a + p.nw, 0))} Kg</div>` + (mine.length ? tbl(["Pack Code", "Roll", "Net Weight Total", "Gross Weight Total", "Status Scan"].concat(s.status === "Material On Load" ? ["Aksi"] : []), mine.map((p) => `<tr><td>${pill(p.code)}</td><td class="ap-r">${p.roll}</td><td class="ap-r">${num(p.nw)}</td><td class="ap-r">${num(p.gw)}</td><td>${lab(s.spkc.scanned.includes(p.code) ? "Scanned" : "Belum Scan")}</td>${s.status === "Material On Load" ? `<td>${btn("danger", "rmpack", p.code, "Hapus Pack", ` data-spk="${s.spk_no}"`)}</td>` : ""}</tr>`).join("")) : "");
      }).join("")}</div></div>`).join("") });
  }
  function manageModal(s) {
    const f = { sel: new Set(s.spkc ? s.spkc.packs : []) };
    const packTbl = (list, titleTxt, mid) => `<h6 style="margin:10px 0 4px">${titleTxt}</h6>` + tbl(["", "Pack Code", "Roll", "Net Weight Total", "Gross Weight Total", "Detail", "Status"],
      list.map((p) => { const used = inOtherSpkc(p.code, s.spk_no);
        return `<tr class="${f.sel.has(p.code) ? "wh-sel" : ""}"><td><input type="checkbox" data-f="pk" value="${esc(p.code)}"${f.sel.has(p.code) ? " checked" : ""}${used ? " disabled" : ""}></td><td>${pill(p.code)}</td><td class="ap-r">${p.roll}</td><td class="ap-r">${num(p.nw)}</td><td class="ap-r">${num(p.gw)}</td><td>${p.code.startsWith("WIP-") ? "-" : btn("light", "pdet", p.code, "Detail")}</td><td>${used ? lab("Dipilih di SPK Coil") : lab("Available")}</td></tr>`; }).join(""), "Tidak ada pack tersedia.");
    openModal({
      title: "Manage Request Pack — " + s.spk_no, size: "xl", ok: "Simpan SPK Coil", okCls: "success",
      body: () => `<p class="note" style="margin-top:0">Pilih pack dari Warehouse WIP dan Warehouse Production sesuai kebutuhan material. ${s.spkc ? "SPK Coil <b>" + esc(s.spkc.no) + "</b> sudah ada; menyimpan akan mengganti pilihan pack (hasil scan pack yang tetap dipilih dipertahankan)." : "Menyimpan akan membuat SPK Coil baru dan status SPK menjadi Material On Load."}</p>` +
        s.detail.map((d) => `<div class="wh-prod"><div class="wh-prod__h"><span>${esc(d.nm_produk_fg)}</span><span>Target ${num(d.target_qty, 0)} pcs</span></div><div class="wh-prod__b">${bomOf(d).map((b) =>
          `<div class="wh-need" data-calc="need" data-arg="${b.mid}|${b.need}"></div>` + packTbl(wipPacks().filter((p) => p.mats.includes(b.mid)), "Warehouse WIP", b.mid) + packTbl(W.packs("PRO").filter((p) => p.mats.includes(b.mid)), "Warehouse Production", b.mid)).join("")}</div></div>`).join(""),
      calc: { need: (arg) => { const [mid, need] = arg.split("|"), got = [...f.sel].map(packInfo).filter((p) => p && p.mats.includes(mid)).reduce((a, p) => a + p.nw, 0), sel = Number(need);
        return `<b>${esc(mn(mid))}</b> — kebutuhan ${num(sel)} Kg, terpilih ${num(got)} Kg ${got >= sel ? '<span class="ap-label ap-label--success">Cukup</span>' : `<span class="ap-label ap-label--warning">Kurang ${num(sel - got)} Kg</span>`}`; } },
      onField(el) { if (el.dataset.f !== "pk") return; if (el.checked) f.sel.add(el.value); else f.sel.delete(el.value); U.drawModal(); },
      act: { pdet(d) { const p = packInfo(d.id); openModal({ title: "Pack Detail — " + d.id, size: "lg", hdr: "info", body: () => tbl(["No", "Nama Material", "No. Coil", "Kode Internal", "Net Weight", "Gross Weight", "Length"], p.coils.map((c, i) => `<tr><td>${i + 1}</td><td>${esc(mn(c.mat))}</td><td>${esc(c.no_coil)}</td><td>${esc(c.kode_internal)}</td><td class="ap-r">${num(c.net_weight)}</td><td class="ap-r">${num(c.gross_weight)}</td><td class="ap-r">${num(c.length)}</td></tr>`).join("")) }); } },
      onOk() {
        if (!f.sel.size) { warn("Pilih minimal satu pack."); return; }
        const packs = [...f.sel];
        if (s.spkc) { s.spkc.packs = packs; s.spkc.scanned = s.spkc.scanned.filter((c) => packs.includes(c)); } else s.spkc = { no: W.nextNo("spkc"), date: W.today(), packs, scanned: [] };
        s.status = "Material On Load"; W.save(); closeModal(); U.render(); done("SPK Coil " + s.spkc.no + " tersimpan. Status SPK: Material On Load.");
      },
    });
  }
  function printPengambilan(s) {
    const pk = s.spkc.packs.map(packInfo).filter(Boolean);
    printDoc("SPK Pengambilan Material", ppHead("SPK PENGAMBILAN MATERIAL", s.spkc.no) + ppMeta([["No. SPK", esc(s.spk_no)], ["Tgl SPK Coil", fdate(s.spkc.date)], ["Shift Names", esc(s.shift_names.join(", "))], ["Status", esc(s.status)]]) +
      `<table class="pp-tbl"><thead><tr><th>Material Code</th><th>Nama Material</th><th class="pp-r">Kebutuhan (Qty)</th><th>Unit</th></tr></thead><tbody>${needsOf(s).map((n) => `<tr><td>${esc(n.mid)}</td><td>${esc(mn(n.mid))}</td><td class="pp-r">${num(n.need)}</td><td>KG</td></tr>`).join("")}</tbody></table>` +
      `<table class="pp-tbl"><thead><tr><th>No</th><th>Pack Code</th><th>Nama Material</th><th class="pp-r">Roll</th><th class="pp-r">Net Weight Total</th><th class="pp-r">Gross Weight Total</th><th>Gudang Asal</th></tr></thead><tbody>${pk.map((p, i) => `<tr><td>${i + 1}</td><td>${esc(p.code)}</td><td>${esc(p.mats.map(mn).join(", "))}</td><td class="pp-r">${p.roll}</td><td class="pp-r">${num(p.nw)}</td><td class="pp-r">${num(p.gw)}</td><td>${esc(p.gudang)}</td></tr>`).join("")}</tbody></table>` + ppSign(["Dibuat (PPIC)", "Diambil oleh (Gudang)", "Diterima (Produksi)"]));
  }
  function confirmModal(pre) {
    const cf = { no: pre || "" };
    const pend = () => S().spk.filter((s) => s.status === "Material On Load" && s.spkc);
    const cur = () => pend().find((s) => s.spkc.no === cf.no);
    const allScanned = () => { const s = cur(); return !!s && s.spkc.packs.every((c) => s.spkc.scanned.includes(c)); };
    const doScan = (code) => {
      const s = cur(); if (!s) return;
      const c = (code || "").trim().toUpperCase(); if (!c) return;
      if (!s.spkc.packs.includes(c)) { warn(`Pack "${c}" tidak ada di SPK Coil ${s.spkc.no}.`); return; }
      if (s.spkc.scanned.includes(c)) { toast(`Pack ${c} sudah di-scan.`, "warn"); return; }
      s.spkc.scanned.push(c); W.save(); U.drawModal(); toast(`Pack ${c} berhasil di-scan.`, "ok");
    };
    openModal({
      title: "Confirm SPK Coil", size: "lg", ok: "Confirm SPK Coil", okCls: "success", okDisabled: () => !allScanned(),
      body: () => { const s = cur(), pk = s ? s.spkc.packs.map(packInfo).filter(Boolean) : [];
        return `<div class="ap-row"><div class="c3"><label class="ap-lbl">Pilih Nomor SPK Coil (Request ID)</label></div><div class="c9" style="grid-column: span 9"><select class="ap-sel ap-in" data-f="no"><option value="">-- Pilih SPK Coil --</option>${pend().map((x) => `<option value="${x.spkc.no}"${x.spkc.no === cf.no ? " selected" : ""}>${x.spkc.no} (${fdate(x.spkc.date)})</option>`).join("")}</select>${pend().length ? "" : '<div class="ap-hint">Tidak ada SPK Coil pending.</div>'}</div></div>` +
          (s ? `<div class="wh-scanbox"><b>Scan QR Code Coil</b><br><input class="ap-in" id="scanIn" data-enter="scan" placeholder="Scan/Ketik kode pack di sini (Enter)" autocomplete="off"> <button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="scan">Scan</button></div>` +
            tbl(["No", "Pack Code", "Roll", "Nama Material", "Status Scan"], pk.map((p, i) => `<tr><td>${i + 1}</td><td>${pill(p.code)}</td><td class="ap-r">${p.roll}</td><td>${esc(p.mats.map(mn).join(", "))}</td><td>${lab(s.spkc.scanned.includes(p.code) ? "Scanned" : "Belum Scan")} ${s.spkc.scanned.includes(p.code) ? "" : btn("light", "scanrow", p.code, "Simulasi scan")}</td></tr>`).join("")) +
            `<p class="note">${s.spkc.scanned.length}/${s.spkc.packs.length} pack ter-scan. Tombol Confirm aktif setelah semua pack ter-scan. Tombol "Simulasi scan" hanya untuk mockup (di aplikasi dipindai dengan scanner QR).</p>` : "");
      },
      onField(el) { cf.no = el.value; U.drawModal(); },
      after(h) { const i = h.querySelector("#scanIn"); if (i) i.focus(); },
      act: { scan(d, b) { const i = $("#scanIn"); doScan(i ? i.value : ""); }, scanrow: (d) => doScan(d.id) },
      onOk() {
        const s = cur(); if (!allScanned()) { warn("Semua coil harus di-scan terlebih dahulu"); return; }
        const sum = {};
        s.spkc.packs.forEach((code) => { const p = packInfo(code); p.mats.forEach((mid) => { const k = mid + "|" + p.gudang, o = sum[k] || (sum[k] = { mid, gudang: p.gudang, n: 0, qty: 0 }); if (p.coils.length) p.coils.filter((c) => c.mat === mid).forEach((c) => { o.n++; o.qty += c.net_weight; }); else { o.n++; o.qty += p.nw; } });
          if (p.wip) p.wip.kd = "PRT"; else S().coils.filter((c) => c.pack === code).forEach((c) => { c.gudang = "PRT"; }); });
        Object.values(sum).filter((o) => o.gudang === "PRO" || o.gudang === "SLI").forEach((o) => W.addTrx(o.mid, W.today(), s.spkc.no, "OUT", o.n, o.qty));
        s.status = "Material Confirmed"; W.save(); closeModal(); U.render();
        done("SPK Coil " + s.spkc.no + " berhasil dikonfirmasi.", `<p class="note">Pack dipindah ke gudang PRT (WRH Production 2) tanpa mengubah nilai stok. Ringkasan transaksi (kode_trans ${esc(s.spkc.no)}):</p>` + tbl(["Nama Material", "Gudang Asal", "Jumlah Coil", "Qty Transaksi (Kg)"], Object.values(sum).map((o) => `<tr><td>${esc(mn(o.mid))}</td><td>${o.gudang}</td><td class="ap-r">${o.n}</td><td class="ap-r">${num(o.qty)}</td></tr>`).join("")));
      },
    });
  }
  P.request = {
    title: "Request List", mod: "request_list", role: "Gudang / PPIC", ui: {},
    hint: "Permintaan material dari SPK: pilih pack (SPK Coil), cetak SPK Pengambilan, scan QR saat pengambilan, lalu konfirmasi.",
    render() {
      const l = S().spk.filter((s) => LISTED.includes(s.status)).sort((a, b) => b.tgl_spk.localeCompare(a.tgl_spk) || b.spk_no.localeCompare(a.spk_no));
      return `<div class="cl-sumrow">${LISTED.map((st) => stat(l.filter((s) => s.status === st).length, st)).join("")}</div>` +
        card("Daftar Request SPK", tbl(["No", "SPK No", "Tgl SPK", "Shift Names", "Status Coil", "Jumlah Produk", "Aksi"], l.map((s, i) => {
          const it = [mi("view", s.spk_no, "View")];
          if (["Material Requested", "Material On Load"].includes(s.status)) it.push(mi("manage", s.spk_no, "Manage Request Pack"));
          if (s.status !== "Material Requested") it.push(mi("print", s.spk_no, "Print SPK Pengambilan"));
          if (s.status === "Material On Load") it.push(mi("x", "", "-"), mi("confirm", s.spkc.no, "Confirm SPK Coil"), mi("close", s.spk_no, "Close SPK"));
          return `<tr><td>${i + 1}</td><td>${esc(s.spk_no)}</td><td>${fdate(s.tgl_spk)}</td><td>${esc(s.shift_names.join(", "))}</td><td>${lab(s.status)}</td><td class="ap-r">${s.detail.length}</td><td>${menu(it)}</td></tr>`; }).join(""), "Tidak ada request."), "") +
        `<p class="note">Alur status: <b>Material Requested</b> (SPK dibuat) → Manage Request Pack → <b>Material On Load</b> (SPK Coil dibuat, bisa dicetak) → scan semua pack + Confirm SPK Coil → <b>Material Confirmed</b> (pack pindah ke gudang PRT, lanjut ke menu Unpack Coil). Close SPK = menutup manual menjadi Material Confirmed tanpa scan.</p>`;
    },
    act: {
      view: (d) => viewSpkc(SPK(d.id)), manage: (d) => manageModal(SPK(d.id)), print: (d) => { const s = SPK(d.id); if (!s.spkc) { warn("SPK Coil belum dibuat (Manage Request Pack dulu)."); return; } printPengambilan(s); },
      confirm: (d) => confirmModal(d.id),
      close(d) { confirmThen({ title: "Close SPK?", text: "SPK " + d.id + " ditutup manual dan statusnya menjadi Material Confirmed (tanpa scan).", ok: "Ya, Close SPK" }, () => { SPK(d.id).status = "Material Confirmed"; W.save(); U.render(); toast("SPK ditutup. Status: Material Confirmed.", "ok"); }); },
    },
  };

  /* ======================= 7. Stock From Warehouse (stock_from_warehouse) ======================= */
  const SFWT = { prt: ["PRT", "WRH. Production 2", "Coil yang sudah dikonfirmasi lewat Request List dan berada di gudang PRT (menunggu Unpack Coil)."], wip: ["WIP", "WIP (Coil Remains)", "Sisa coil hasil produksi yang masih bisa dipakai lagi (dipilih di Manage Request Pack)."], hld: ["HLD", "On Hold", "Material ditahan dari Laporan Produksi; keputusan lanjutannya di modul Claim Material."] };
  const sfwTable = (rows, extra) => tbl(["No", "Nama Material", "No. Coil", "Kode Internal"].concat(extra ? ["Gudang Asal"] : [], ["Net Weight (Kg)", "Gross Weight (Kg)", "Length (M)"], extra ? ["Kode Trans"] : []),
    rows.map((x, i) => `<tr><td>${i + 1}</td><td>${esc(mn(x.mat))}</td><td>${esc(x.no_coil)}</td><td>${esc(x.kode_internal)}</td>${extra ? `<td>${esc(x.gudang_asal)}</td>` : ""}<td class="ap-r">${num(x.net_weight)}</td><td class="ap-r">${num(x.gross_weight)}</td><td class="ap-r">${num(x.length)}</td>${extra ? `<td>${esc(x.kode_trans)}</td>` : ""}</tr>`).join(""), "Tidak ada data.",
    `<tr class="wh-foot"><td colspan="${extra ? 5 : 4}" class="ap-r">Total</td><td class="ap-r">${num(sumNet(rows))}</td><td class="ap-r">${num(rows.reduce((a, x) => a + x.gross_weight, 0))}</td><td colspan="${extra ? 2 : 1}"></td></tr>`);
  const sfwAll = () => S().sfw.concat(S().coils.filter((c) => c.gudang === "PRT").map((c) => { const sp = S().spk.find((x) => x.spkc && x.spkc.packs.includes(c.pack)); return { kd: "PRT", mat: c.mat, no_coil: c.no_coil, kode_internal: c.kode_internal, net_weight: c.net_weight, gross_weight: c.gross_weight, length: c.length, gudang_asal: "PRO", kode_trans: sp ? sp.spkc.no : "-" }; }));
  P.sfw = {
    title: "Stock From Warehouse", mod: "stock_from_warehouse", role: "Gudang / Produksi", ui: { tab: "prt", q: "", hdate: "", hsrc: "", hshown: false },
    hint: "Stok coil yang sudah keluar dari gudang utama: WRH. Production 2 (PRT), WIP (coil sisa), On Hold, dan posisi per tanggal.",
    render() {
      const u = U.ui, nav = tabs([["prt", "WRH. Production 2"], ["wip", "WIP (Coil Remains)"], ["hld", "On Hold"], ["hist", "History Per Days"]], u.tab);
      const stats = `<div class="cl-sumrow">${["prt", "wip", "hld"].map((k) => stat(sfwAll().filter((x) => x.kd === SFWT[k][0]).length + " coil / " + num(sumNet(sfwAll().filter((x) => x.kd === SFWT[k][0])), 0) + " Kg", SFWT[k][1])).join("")}</div>`;
      if (u.tab === "hist") {
        const rows = u.hshown ? sfwAll().filter((x) => !u.hsrc || x.kd === u.hsrc) : [];
        return stats + nav + card("History Per Days", `<div class="cl-tools"><label>As of Date : <input class="ap-in" type="date" data-u="hdate" value="${esc(u.hdate)}"></label><label>Sumber : <select class="ap-sel ap-in" data-u="hsrc"><option value="">-- Semua Sumber --</option><option value="PRT"${u.hsrc === "PRT" ? " selected" : ""}>Production 2 (PRT)</option><option value="WIP"${u.hsrc === "WIP" ? " selected" : ""}>WIP</option><option value="HLD"${u.hsrc === "HLD" ? " selected" : ""}>On Hold (HLD)</option></select></label>
          <button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="show">Show</button><button type="button" class="ap-btn ap-btn--sm ap-btn--secondary" data-act="reset">Reset</button>${u.hshown ? '<button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="xl">Excel</button>' : ""}</div>` + (u.hshown ? sfwTable(rows, true) : '<div class="ap-empty">Pilih tanggal lalu klik Show.</div>')) + '<p class="note">Mockup: posisi per tanggal memakai data stok saat ini.</p>';
      }
      const t = SFWT[u.tab], q = (u.q || "").toLowerCase(), rows = sfwAll().filter((x) => x.kd === t[0] && (!q || (mn(x.mat) + x.no_coil + x.kode_internal).toLowerCase().includes(q)));
      return stats + nav + card(t[1], `<div class="cl-tools"><input class="ap-in" data-u="q" placeholder="Cari material / no. coil..." value="${esc(u.q)}"><span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="xl">Excel</button></span></div><div class="ap-hint" style="margin-bottom:8px">${t[2]}</div>` + sfwTable(rows, false));
    },
    act: {
      xl: () => excel("Stock From Warehouse"),
      show() { if (!U.ui.hdate) { warn("Pilih tanggal terlebih dahulu."); return; } U.ui.hshown = true; U.render(); },
      reset() { Object.assign(U.ui, { hdate: "", hsrc: "", hshown: false }); U.render(); },
    },
  };

  /* ======================= 8. Unpack Coil (unpack_coil) ======================= */
  let uf = null;                                              // state form unpack
  const sumB = (m, k) => (m.babies || []).reduce((a, b) => a + (Number(b[k]) || 0), 0);
  const mkBabies = (m, n) => { const cur = m.babies || []; const per = n ? W.round2(m.net_weight_pl / n) : 0, perG = n ? W.round2(m.gross_weight_pl / n) : 0; const out = []; for (let i = 0; i < n; i++) out.push(cur[i] || { babycoil_code: m.kode_internal + "-BC." + (i + 1), no_coil: m.no_coil, net_weight_actual: per, gross_weight_actual: perG, net_weight_pl: per, gross_weight_pl: perG }); return out; };
  const eligiblePacks = () => W.packs("PRT").filter((p) => !S().unpack.some((u) => u.pack_code === p.code && u.status !== "Cancelled"));
  const totalsOf = (f) => ({ nwP: f.mats.reduce((a, m) => a + m.net_weight_pl, 0), gwP: f.mats.reduce((a, m) => a + m.gross_weight_pl, 0) });
  function babyModal(i) {
    const m = uf.mats[i];
    openModal({
      title: "Detail Baby Coil — " + mn(m.id_material) + " (" + m.no_coil + ")", size: "lg", ok: "Selesai", hdr: "primary",
      body: () => tbl(["No", "No. Coil", "Babycoil", "Net Weight Per Roll (Actual)", "Gross Weight Per Roll (Actual)", "Net Weight Per Roll (PL)", "Gross Weight Per Roll (PL)", "Hapus"],
        m.babies.map((b, k) => `<tr><td>${k + 1}</td><td>${esc(m.no_coil)}</td><td>${esc(b.babycoil_code)}</td><td><input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="bn" data-k="${k}" value="${esc(NumFmt.fmt(b.net_weight_actual))}"></td><td><input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="bg" data-k="${k}" value="${esc(NumFmt.fmt(b.gross_weight_actual))}"></td><td class="ap-r">${num(b.net_weight_pl)}</td><td class="ap-r">${num(b.gross_weight_pl)}</td><td>${btn("danger", "bdel", k, "&#10005;")}</td></tr>`).join(""), "Jumlah coil roll belum diisi."),
      onField(el) { const b = m.babies[+el.dataset.k]; if (!b) return; const v = pv(el) || 0; if (el.dataset.f === "bn") b.net_weight_actual = v; else b.gross_weight_actual = v; },
      act: { bdel(d) { m.babies.splice(+d.id, 1); m.jumlah_coil_roll = m.babies.length; U.drawModal(); } },
      onOk() { m.jumlah_coil_roll = m.babies.length; m.net_weight_actual = W.round2(sumB(m, "net_weight_actual")); m.gross_weight_actual = W.round2(sumB(m, "gross_weight_actual")); closeModal(); U.render(); },
      onClose() { m.jumlah_coil_roll = m.babies.length; m.net_weight_actual = W.round2(sumB(m, "net_weight_actual")); m.gross_weight_actual = W.round2(sumB(m, "gross_weight_actual")); U.render(); },
    });
  }
  function unpackForm() {
    const t = totalsOf(uf), edit = !!uf.unpack_no;
    const matRows = uf.mats.map((m, i) => `<tr><td>${i + 1}</td><td>${pill(uf.pack)}</td><td>${esc(mn(m.id_material))}</td><td>${esc(m.no_coil)}</td><td><input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="roll" data-i="${i}" value="${esc(NumFmt.fmt(m.jumlah_coil_roll))}"></td>
      <td class="ap-r">${num(m.net_weight_actual)}</td><td class="ap-r">${num(m.gross_weight_actual)}</td><td class="ap-r">${num(m.net_weight_pl)}</td><td class="ap-r">${num(m.gross_weight_pl)}</td><td>${btn("info", "baby", i, "View Detail")}</td></tr>`).join("");
    return card(edit ? "Edit Report Unpack — " + esc(uf.unpack_no) : "Add Report Unpack", row("Pack (Material Confirmed)", edit ? `${pill(uf.pack)} <span class="ap-hint">pack tidak dapat diganti saat edit</span>` : sel("pack", uf.pack, eligiblePacks().map((p) => [p.code, p.code + " — " + p.mats.map(mn).join(", ")]), { blank: "Cari & pilih pack...", struct: 1 }).replace('data-f="pack"', 'data-f="pack"'), 1) +
      (uf.pack ? `<h6 style="margin:14px 0 6px">Ringkasan Pack</h6><div class="ap-tablewrap"><table class="ap-table ap-table--bordered"><thead><tr><th rowspan="2">Kulit</th><th rowspan="2">Clamp / Ring</th><th colspan="2">Actual Weight</th><th colspan="2">Packing List</th><th colspan="2">Selisih Actual vs PL</th></tr><tr><th>Net Weight</th><th>Gross Weight</th><th>Net Weight</th><th>Gross Weight</th><th>Net</th><th>Gross</th></tr></thead><tbody><tr>
          <td><input class="ap-in" data-f="catatan_kulit" value="${esc(uf.catatan_kulit)}" placeholder="Catatan kulit"></td><td><input class="ap-in" data-f="catatan_clamp_ring" value="${esc(uf.catatan_clamp_ring)}" placeholder="Catatan clamp / ring"></td>
          <td><input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="nwA" value="${esc(NumFmt.fmt(uf.net_weight_actual))}"></td><td><input class="ap-in ap-in--num" inputmode="decimal" data-numfmt="1" data-f="gwA" value="${esc(NumFmt.fmt(uf.gross_weight_actual))}"></td>
          <td class="ap-r">${num(t.nwP)}</td><td class="ap-r">${num(t.gwP)}</td><td class="ap-r" id="selN">${num(uf.net_weight_actual - t.nwP)}</td><td class="ap-r" id="selG">${num(uf.gross_weight_actual - t.gwP)}</td></tr></tbody></table></div>
        <h6 style="margin:14px 0 6px">Material dalam Pack</h6>` + tbl(["No", "Pack Code", "Nama Material", "No. Coil", "Jumlah Coil (Roll Baby Coil)", "Net Weight Total Actual", "Gross Weight Total Actual", "Net Weight Total PL", "Gross Weight Total PL", "Aksi"], matRows) +
        row("Catatan", txt("catatan", uf.catatan, { ph: "Opsional", rows: 2 })) : '<p class="note">Pilih pack berstatus Material Confirmed (berada di gudang PRT) untuk memulai.</p>') +
      `<div class="cl-tools" style="margin-top:10px"><button type="button" class="ap-btn ap-btn--outline-warning" data-act="cancel">&#8592; Kembali</button>${uf.pack ? '<button type="button" class="ap-btn ap-btn--success" data-act="save">Save</button>' : ""}</div>`);
  }
  const UNPACK_LABEL = { Draft: "Draft", Confirmed: "Confirmed", Cancelled: "Cancelled", Lock: "Lock" };
  function valueSplit(u, m, b) {
    const harga = W.matBy(m.id_material).harga_beli, nilai = m.net_weight_pl * harga, tot = sumB(m, "net_weight_actual") || 1, tv = nilai * (b.net_weight_actual / tot);
    return { costbook: W.round2(b.net_weight_actual ? tv / b.net_weight_actual : 0), total: W.round2(tv) };
  }
  P.unpack = {
    title: "Unpack Coil", mod: "unpack_coil", role: "Gudang / Produksi", ui: { mode: "list", id: null },
    hint: "Report Unpack: 1 report = 1 pack berstatus Material Confirmed. Input berat actual pack, jumlah baby coil per material, lalu berat per baby coil.",
    render() {
      const u = U.ui;
      if (u.mode === "form") return unpackForm();
      if (u.mode === "view") { const r = S().unpack.find((x) => x.unpack_no === u.id), edt = !["Cancelled", "Lock"].includes(r.status);
        return card("View Report Unpack — " + esc(r.unpack_no), kv([["Unpack No", esc(r.unpack_no)], ["Tgl Unpack", fdate(r.tgl_unpack) + " " + r.tgl_unpack.slice(11)], ["Pack Code", pill(r.pack_code)], ["Status", lab(r.status)], ["Catatan Kulit", esc(r.catatan_kulit || "-")], ["Catatan Clamp Ring", esc(r.catatan_clamp_ring || "-")], ["Net Weight Actual / PL", num(r.net_weight_actual) + " / " + num(r.net_weight_pl) + " Kg"], ["Gross Weight Actual / PL", num(r.gross_weight_actual) + " / " + num(r.gross_weight_pl) + " Kg"], ["Catatan", esc(r.catatan || "-")]]) +
          r.mats.map((m) => `<div class="wh-sec">${esc(mn(m.id_material))} — No. Coil ${esc(m.no_coil)}</div>` + tbl(["No", "Babycoil", "Net Weight Per Roll (Actual)", "Gross Weight Per Roll (Actual)", "Net Weight Per Roll (PL)", "Gross Weight Per Roll (PL)", "Costbook", "Total Nilai"],
            m.babies.map((b, k) => { const v = valueSplit(r, m, b); return `<tr><td>${k + 1}</td><td>${esc(b.babycoil_code)}</td><td class="ap-r">${num(b.net_weight_actual)}</td><td class="ap-r">${num(b.gross_weight_actual)}</td><td class="ap-r">${num(b.net_weight_pl)}</td><td class="ap-r">${num(b.gross_weight_pl)}</td><td class="ap-r">${num(v.costbook)}</td><td class="ap-r">${num(v.total)}</td></tr>`; }).join(""), "",
            `<tr class="wh-foot"><td colspan="2" class="ap-r">Total</td><td class="ap-r">${num(sumB(m, "net_weight_actual"))}</td><td class="ap-r">${num(sumB(m, "gross_weight_actual"))}</td><td class="ap-r">${num(sumB(m, "net_weight_pl"))}</td><td class="ap-r">${num(sumB(m, "gross_weight_pl"))}</td><td></td><td class="ap-r">${num(m.babies.reduce((a, b) => a + valueSplit(r, m, b).total, 0))}</td></tr>`)).join("") +
          `<p class="note">Costbook per baby coil = (Net Weight PL coil induk × Harga Beli/Kg) dibagi proporsional terhadap berat actual baby coil. Rumus pembagian dan sumber harga perlu dikonfirmasi ke programmer.</p><div class="cl-tools"><button type="button" class="ap-btn ap-btn--outline-warning" data-act="back">&#8592; Kembali</button>${edt ? btn("warning", "edit", r.unpack_no, "Edit") : ""}</div>`); }
      const l = S().unpack.slice().sort((a, b) => b.tgl_unpack.localeCompare(a.tgl_unpack));
      return `<div class="cl-sumrow">${stat(l.filter((r) => r.status === "Confirmed").length, "Confirmed")}${stat(l.filter((r) => r.status === "Lock").length, "Lock")}${stat(eligiblePacks().length, "Pack siap di-unpack")}</div>` +
        card("Daftar Report Unpack", `<div class="cl-tools"><span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--sm ap-btn--success" data-act="add"${eligiblePacks().length ? "" : " disabled"}>+ Add Report Unpack</button></span></div>` +
          tbl(["No", "Unpack No", "Pack Code", "Jumlah Material", "Jumlah Coil Roll", "Net Weight Actual", "Tgl Unpack", "Status", "Aksi"], l.map((r, i) => {
            const it = [mi("view", r.unpack_no, "View")]; if (!["Cancelled", "Lock"].includes(r.status)) it.push(mi("edit", r.unpack_no, "Edit"), mi("x", "", "-"), mi("lock", r.unpack_no, "Lock"), mi("del", r.unpack_no, "Delete"));
            return `<tr><td>${i + 1}</td><td>${esc(r.unpack_no)}</td><td>${pill(r.pack_code)}</td><td class="ap-r">${r.mats.length}</td><td class="ap-r">${r.mats.reduce((a, m) => a + m.babies.length, 0)}</td><td class="ap-r">${num(r.net_weight_actual)}</td><td>${fdate(r.tgl_unpack)}</td><td>${lab(UNPACK_LABEL[r.status] || r.status)}</td><td>${menu(it)}</td></tr>`; }).join(""))) +
        '<p class="note">Report bisa di-edit ulang selama belum Lock: baby coil ditulis ulang beserta costbook proporsional dan history dicatat ulang. Setelah Lock, report tidak dapat diedit atau dihapus. Status Lock dipakai kode tetapi belum ada di ENUM migrasi (Draft / Confirmed / Cancelled) — perlu disamakan.</p>';
    },
    onField(el) {
      const k = el.dataset.f;
      if (k === "pack") {
        uf.pack = el.value;
        const p = W.packs().find((x) => x.code === uf.pack);
        uf.mats = p ? p.coils.map((c) => ({ id_material: c.mat, no_coil: c.no_coil, kode_internal: c.kode_internal, jumlah_coil_roll: 0, net_weight_actual: 0, gross_weight_actual: 0, net_weight_pl: c.net_weight, gross_weight_pl: c.gross_weight, babies: [] })) : [];
        const t = totalsOf(uf); uf.net_weight_actual = t.nwP; uf.gross_weight_actual = t.gwP; U.render(); return;
      }
      if (k === "roll") { const m = uf.mats[+el.dataset.i], n = Math.max(0, Math.min(40, Math.floor(pv(el) || 0))); m.jumlah_coil_roll = n; m.babies = mkBabies(m, n); m.net_weight_actual = W.round2(sumB(m, "net_weight_actual")); m.gross_weight_actual = W.round2(sumB(m, "gross_weight_actual")); { const keep = el.selectionStart; U.render(); const n2 = document.querySelector(`[data-f="roll"][data-i="${el.dataset.i}"]`); if (n2) { n2.focus(); try { n2.setSelectionRange(keep, keep); } catch (e) { /* abaikan */ } } } return; }
      if (k === "nwA" || k === "gwA") { if (k === "nwA") uf.net_weight_actual = pv(el) || 0; else uf.gross_weight_actual = pv(el) || 0; const t = totalsOf(uf); $("#selN").textContent = num(uf.net_weight_actual - t.nwP); $("#selG").textContent = num(uf.gross_weight_actual - t.gwP); return; }
      uf[k] = el.value;
    },
    act: {
      back() { U.ui.mode = "list"; U.render(); }, cancel() { U.ui.mode = "list"; U.render(); },
      view(d) { U.ui.mode = "view"; U.ui.id = d.id; U.render(); },
      add() { uf = { unpack_no: null, pack: "", catatan_kulit: "", catatan_clamp_ring: "", net_weight_actual: 0, gross_weight_actual: 0, catatan: "", mats: [] }; U.ui.mode = "form"; U.render(); },
      edit(d) { const r = JSON.parse(JSON.stringify(S().unpack.find((x) => x.unpack_no === d.id))); uf = { unpack_no: r.unpack_no, pack: r.pack_code, catatan_kulit: r.catatan_kulit, catatan_clamp_ring: r.catatan_clamp_ring, net_weight_actual: r.net_weight_actual, gross_weight_actual: r.gross_weight_actual, catatan: r.catatan, mats: r.mats, _r: r.unpack_no }; U.ui.mode = "form"; U.render(); },
      baby(d) { const m = uf.mats[+d.id]; if (!(m.jumlah_coil_roll > 0)) { warn("Isi Jumlah Coil (Roll Baby Coil) terlebih dahulu."); return; } babyModal(+d.id); },
      del(d) { confirmThen({ title: "Hapus report?", text: "Report " + d.id + " dihapus; baby coil dibuang dari stok dan coil induk dikembalikan.", ok: "Ya, Hapus" }, () => { const r = S().unpack.find((x) => x.unpack_no === d.id); if (r.status === "Lock") { warn("Report unpack sudah di-Lock dan tidak dapat dihapus."); return; } W.unpackRevert(r); S().unpack = S().unpack.filter((x) => x !== r); W.save(); U.render(); toast("Report unpack berhasil dihapus.", "ok"); }); },
      lock(d) { confirmThen({ title: "Lock report?", text: "Report " + d.id + " tidak dapat diedit atau dihapus setelah di-Lock.", ok: "Ya, Lock" }, () => { S().unpack.find((x) => x.unpack_no === d.id).status = "Lock"; W.save(); U.render(); toast("Report unpack berhasil di-lock.", "ok"); }); },
      save() {
        if (!uf.pack) { warn("Pack wajib dipilih."); return; }
        const bad = uf.mats.find((m) => !(m.jumlah_coil_roll > 0) || !m.babies.length);
        if (!uf.mats.length || bad) { warn(bad ? `Detail baby coil untuk material ${mn(bad.id_material)} belum diisi. Silakan klik "View Detail" sebelum Save.` : "Minimal 1 material harus diisi."); return; }
        const old = uf.unpack_no ? S().unpack.find((x) => x.unpack_no === uf.unpack_no) : null; if (old) W.unpackRevert(old);
        const t = totalsOf(uf), rec = { unpack_no: uf.unpack_no || W.nextNo("unpack"), tgl_unpack: W.today() + " 10:00", pack_code: uf.pack, catatan_kulit: uf.catatan_kulit, catatan_clamp_ring: uf.catatan_clamp_ring, net_weight_actual: uf.net_weight_actual, gross_weight_actual: uf.gross_weight_actual, net_weight_pl: t.nwP, gross_weight_pl: t.gwP, catatan: uf.catatan, status: "Confirmed", mats: uf.mats };
        const i = S().unpack.findIndex((x) => x.unpack_no === rec.unpack_no); if (i >= 0) { rec.tgl_unpack = S().unpack[i].tgl_unpack; S().unpack[i] = rec; } else S().unpack.push(rec);
        W.unpackApply(rec); W.save(); U.ui.mode = "list"; U.render(); done("Report unpack berhasil disimpan: " + rec.unpack_no + ". Baby coil masuk ke stok gudang PRT menggantikan coil induk.");
      },
    },
  };

  U.init(P);
})();
