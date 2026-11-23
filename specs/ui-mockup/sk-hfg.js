/* ============================================================
   sk-hfg.js — Sisi HFG untuk Portal Vendor (mockup Subkont tahap 4):
   P.sup  = Master Supplier (meniru modul `supplier`, kolom sama dengan tabel new_supplier; Purchasing)            FR-125
   (Link Portal Vendor dikelola dari daftar Master Supplier: Buat / Salin / Buka / Buat Ulang / Nonaktifkan Link)     FR-113, FR-125
   P.lapv = Laporan Subkont Vendor (PPIC: lihat LHP dan Buka Akses Edit atas pengajuan perbaikan vendor)          FR-117, UC-25
   Label = nama kolom ERD. Data: sk-data.js (sup, links, lap, rev). Tidak pernah menampilkan biaya jasa di halaman vendor (NFR-18).
   ============================================================ */
(() => {
  "use strict";
  const U = window.WHUI, W = U.W, P = window.SKP;
  const { esc, num, fdate, btn, tbl, card, kv, toast, dialog, warn, done, openModal, closeModal } = U;
  const S = () => W.state;
  const BUYER = "Dewi (Purchasing)", PPIC = "Dedi K.";
  const ST = { "Menunggu Persetujuan": "warning", Aktif: "success", Ditolak: "danger", Nonaktif: "secondary", "Link Aktif": "success", "Link Nonaktif": "secondary", "Belum ada link": "warning", Draft: "warning", Closed: "success", "Dibuka Kembali": "info", Diajukan: "warning", Dibuka: "info", Ditutup: "success" };
  const stl = (s) => `<span class="ap-label ap-label--${ST[s] || "secondary"}">${esc(s)}</span>`;
  const field = (label, html, req, hint) => `<div class="pr-f"><label class="ap-lbl">${label}${req ? ' <span class="ap-req">*</span>' : ""}</label>${html}${hint ? `<div class="ap-hint">${hint}</div>` : ""}</div>`;
  const now = () => W.today() + " " + new Date().toTimeString().slice(0, 5);
  const COUNTRY = ["Indonesia", "China", "Japan", "Korea", "Singapore", "Malaysia", "Lainnya"];
  const supName = (id) => (W.vendorBy(id) || { nama: "-" }).nama;

  /* ================= Master Supplier ================= */
  const SUPF = ["nama", "id_country", "telp", "telp2", "fax", "email", "contact_person", "email2", "inisial", "contact", "tax_number", "bank_account", "address", "tax_address", "note"];
  let sf = null;
  function supModal(id) {
    const edit = id != null; sf = edit ? Object.assign({}, W.vendorBy(id)) : { nama: "", id_country: "Indonesia", telp: "", telp2: "", fax: "", email: "", contact_person: "", email2: "", inisial: "", contact: "", tax_number: "", id_currency: "IDR", bank_account: "", address: "", tax_address: "", note: "" };
    const t = (k, ph) => `<input class="ap-in" data-f="${k}" value="${esc(sf[k])}" placeholder="${esc(ph || "")}">`, a = (k) => `<textarea class="ap-in" data-f="${k}" rows="2">${esc(sf[k])}</textarea>`;
    openModal({ title: (edit ? "Edit" : "Add") + " Master Supplier", size: "xl", hdr: "primary", ok: "Save", okCls: "success",
      body: () => `<div class="pr-grid3">${field("Supplier Name", t("nama", "Supplier Name"), 1)}${field("Country", `<select class="ap-sel ap-in" data-f="id_country">${COUNTRY.map((c) => `<option${sf.id_country === c ? " selected" : ""}>${c}</option>`).join("")}</select>`)}${field("Inisial", t("inisial"))}
        ${field("Telephone", t("telp"))}${field("Telephone 2", t("telp2"))}${field("Fax", t("fax"))}${field("Email", t("email"))}${field("Email 2", t("email2"))}${field("Contact Person", t("contact_person"))}${field("Contact", t("contact"))}${field("Tax Number", t("tax_number"))}${field("Bank Account", t("bank_account"))}
        ${field("Address", a("address"))}${field("Tax Address", a("tax_address"))}${field("Note", a("note"))}</div><p class="note">Form meniru modul Supplier yang sudah ada (Country, Provinsi/Kota/Kecamatan, Currency disederhanakan di mockup). Supplier yang dipilih di sini menjadi pilihan Vendor Subkont pada Surat Jalan.</p>`,
      onField(el) { sf[el.dataset.f] = el.value; },
      onOk() {
        if (!sf.nama.trim()) { dialog({ icon: "error", title: "Validasi Gagal", text: "Supplier Name wajib diisi." }); return; }
        if (S().sup.some((x) => x.id !== sf.id && x.nama.toLowerCase() === sf.nama.trim().toLowerCase())) { dialog({ icon: "error", title: "Validasi Gagal", text: "Supplier Name sudah ada di Master Supplier." }); return; }
        sf.nama = sf.nama.trim(); sf.last_by = BUYER; sf.last_date = W.today();
        if (edit) Object.assign(W.vendorBy(sf.id), sf); else { sf.id = W.nextSup(); S().sup.push(sf); }
        W.save(); closeModal(); U.render(); toast("Master Supplier " + sf.nama + " disimpan.", "ok");
      } });
  }
  P.sup = {
    app: "Subkont", title: "Master Supplier", mod: "supplier (new_supplier)", role: "Purchasing", ui: { q: "" },
    hint: "Master Supplier mengikuti modul Supplier yang sudah ada. Vendor Subkont pada Surat Jalan dipilih dari master ini; akun Portal Vendor ditautkan ke satu supplier.",
    render() {
      const ql = (U.ui.q || "").toLowerCase(), l = S().sup.filter((x) => !ql || (x.nama + " " + x.email + " " + x.telp).toLowerCase().includes(ql));
      return card("Daftar Supplier", `<div class="cl-tools"><input class="ap-in" data-u="q" placeholder="Search..." value="${esc(U.ui.q)}"><span style="margin-left:auto"><button type="button" class="ap-btn ap-btn--sm ap-btn--primary" data-act="add">+ Add Supplier</button></span></div>` +
        tbl(["#", "Supplier Name", "Country", "Telp", "Fax", "Email", "Last By", "Last Date", "Link Portal Vendor", "Action"], l.map((x, i) => { const st = W.vendorPortal(x.id), lk = W.linkOf(x.id), any = S().links.filter((z) => String(z.id_vendor) === String(x.id)).slice(-1)[0];
        return `<tr><td>${i + 1}</td><td><b>${esc(x.nama)}</b></td><td>${esc(x.id_country)}</td><td>${esc(x.telp || "-")}</td><td>${esc(x.fax || "-")}</td><td>${esc(x.email || "-")}</td><td>${esc(x.last_by)}</td><td style="white-space:nowrap">${fdate(x.last_date)}</td><td style="text-align:center">${stl(st)}${lk ? `<div class="muted" style="font-size:11px">${lk.last_access_at ? "dibuka " + esc(lk.last_access_at) : "belum pernah dibuka"}</div>` : any ? `<div class="muted" style="font-size:11px">nonaktif ${esc(any.nonaktif_at || "")}</div>` : ""}</td>
          <td class="ap-act ap-act-nowrap">${btn("warning", "edit", x.id, "Edit")}${lk ? btn("primary", "lview", x.id, "Link Portal") + btn("secondary", "lreset", x.id, "Buat Ulang") + btn("danger", "loff", x.id, "Nonaktifkan") : btn("success", "lnew", x.id, "Buat Link Portal")}</td></tr>`; }).join(""), "Tidak ada supplier.")) +
        '<p class="note">Kolom "Link Portal Vendor" dan tombol link ditambahkan di mockup: HFG melihat vendor dari Master Supplier lalu memberikan link portal (token unik, tanpa pendaftaran/login/password). Data link disimpan di tabel baru ms_vendor_portal_link, bukan di new_supplier.</p>';
    },
    act: {
      add: () => supModal(null), edit: (d) => supModal(+d.id),
      lnew(d) { makeLink(d.id); }, lview(d) { linkModal(d.id); },
      lreset(d) { dialog({ icon: "warning", title: "Buat ulang link?", text: `Link lama ${supName(d.id)} langsung tidak berlaku dan link baru dibuat. Berikan link baru ke vendor.`, ok: "Ya, Buat Ulang", cancel: "Batal" }).then((ok) => { if (ok) makeLink(d.id); }); },
      loff(d) { dialog({ icon: "warning", title: "Nonaktifkan link?", text: `Vendor ${supName(d.id)} tidak dapat membuka portal sampai dibuatkan link baru.`, ok: "Ya, Nonaktifkan", cancel: "Batal" }).then((ok) => { if (!ok) return; const l = W.linkOf(d.id); l.status_link = "Nonaktif"; l.nonaktif_by = BUYER; l.nonaktif_at = now(); W.save(); U.render(); toast("Link portal dinonaktifkan.", "warn"); }); },
    },
  };

  /* ================= Link Portal Vendor (dikelola dari Master Supplier; menggantikan pendaftaran + persetujuan akun) ================= */
  const linkUrl = (token) => new URL("sk-portal.html?t=" + token, location.href).href;
  function linkModal(id) {
    const l = W.linkOf(id), url = l ? linkUrl(l.token_link) : "";
    openModal({ title: "Link Portal Vendor — " + supName(id), size: "lg", hdr: "info", body: () => `<p>Berikan link ini kepada vendor. Vendor cukup membukanya (tanpa pendaftaran, login, atau password) dan hanya melihat Surat Jalan dan Laporan miliknya.</p>
        <div class="pr-f"><label class="ap-lbl">Link Portal Vendor</label><input class="ap-in" id="lnkUrl" readonly value="${esc(url)}" onclick="this.select()"></div>
        ${kv([["Status Link", stl("Link Aktif")], ["Dibuat Oleh / Tanggal", `${esc(l.created_by)} / ${fdate(l.created_at)}`], ["Terakhir Dibuka Vendor", l.last_access_at ? esc(l.last_access_at) : "Belum pernah"]])}
        <div class="cl-tools" style="margin-top:8px">${btn("primary", "lcopy", "", "Salin Link")}${btn("info", "lopen", "", "Buka Portal (contoh)")}</div>
        <p class="note">Siapa pun yang memegang link dapat membuka data vendor ini. Bila link bocor atau berganti penanggung jawab, tekan <b>Buat Ulang Link</b> (link lama otomatis mati) atau <b>Nonaktifkan Link</b>.</p>`,
      act: { lcopy() { const el = document.getElementById("lnkUrl"); el.select(); let ok = false; try { ok = document.execCommand("copy"); } catch (e) { ok = false; } if (!ok && navigator.clipboard) navigator.clipboard.writeText(url); toast("Link disalin.", "ok"); }, lopen() { window.open(url, "_blank"); } } });
  }
  function makeLink(id) {
    const old = W.linkOf(id);
    if (old) { old.status_link = "Nonaktif"; old.nonaktif_by = BUYER; old.nonaktif_at = now(); }
    S().links.push({ id: W.nextLink(), id_vendor: +id, token_link: W.newToken(), status_link: "Aktif", created_by: BUYER, created_at: W.today(), nonaktif_by: "", nonaktif_at: "", last_access_at: "" });
    W.save(); U.render(); linkModal(id);
  }

  /* ================= Laporan Subkont Vendor (UC-25) ================= */
  const revOf = (no) => S().rev.filter((r) => r.no_laporan === no);
  const jenisOf = (l) => (l.packs && l.packs.length ? "Material" : "Finish Good");
  function viewLap(no) {
    const l = S().lap.find((x) => x.no_laporan === no), d = W.dlvOf(l.kode_delivery), m = jenisOf(l) === "Material";
    openModal({ title: "Detail Laporan Subkont — " + l.no_laporan, size: "xl", hdr: "info", body: () => `<div class="wh-grid2"><div>${kv([["No. Laporan", esc(l.no_laporan)], ["Vendor", esc(supName(l.id_vendor))], ["No. Surat Jalan", esc(d ? d.no_surat_jalan : "-")], ["No. Surat Jalan Vendor", esc(l.no_surat_jalan_vendor)], ["No. PO", esc(l.no_po || "-")]])}</div><div>${kv([["Tgl Laporan", fdate(l.tgl_laporan)], ["Status", stl(l.status)], ["Material / Produk", esc(W.nama(l.kode_barang))]].concat(m ? [["Nett Weight Actual", num(l.nett_weight_actual) + " kg"], ["Total Scrap", num(l.total_scrap) + " kg"], ["Width After", num(l.width_after) + " mm"]] : []))}</div></div>` +
      (m ? tbl(["Pack", "Kode Internal", "Nett Weight Before (kg)", "Jumlah Coil (Roll)"], l.packs.map((p) => `<tr><td>${esc(p.pack_code)}</td><td>${esc(p.kode_internal)}</td><td class="ap-r">${num(p.nett_weight_before)}</td><td class="ap-r">${num(p.jumlah_coil_roll, 0)}</td></tr>`).join("")) : tbl(["Produk FG Asal", "Qty Pcs Hasil", "Material Tambahan", "Berat Tambahan (kg/pcs)", "Berat/Pcs Baru (kg)", "Total Berat (kg)"], l.fg.map((g) => { const ref = (W.FG.find((x) => x.kode_barang === g.kode_barang_asal) || {}).berat_referensi || 0; return `<tr><td>${esc(W.nama(g.kode_barang_asal))}</td><td class="ap-r">${num(g.qty_pcs_hasil, 0)}</td><td>${esc(g.nama_material_tambahan || "-")}</td><td class="ap-r">${num(g.berat_tambahan_kg, 4)}</td><td class="ap-r">${num(Math.round((ref + (g.berat_tambahan_kg || 0)) * 10000) / 10000, 4)}</td><td class="ap-r">${num(g.total_berat_kg)}</td></tr>`; }).join(""))) +
      `<div class="wh-sec">Riwayat Perbaikan (tr_laporan_subkont_revisi)</div>` + tbl(["Asal", "Alasan", "Status", "Diajukan Oleh", "Diajukan At", "Diputuskan By", "Diputuskan At", "Ditutup At"], revOf(l.no_laporan).map((v) => `<tr><td>${esc(v.asal)}</td><td>${esc(v.alasan)}</td><td>${stl(v.status)}</td><td>${esc(v.diajukan_oleh)}</td><td>${esc(v.diajukan_at)}</td><td>${esc(v.diputuskan_by || "-")}</td><td>${esc(v.diputuskan_at || "-")}</td><td>${esc(v.ditutup_at || "-")}</td></tr>`).join(""), "Belum ada pengajuan perbaikan.") });
  }
  P.lapv = {
    app: "Subkont", title: "Laporan Subkont Vendor", mod: "laporan_subkont (sisi HFG)", role: "PPIC", ui: { st: "", q: "" },
    hint: "Laporan Hasil Produksi (LHP) yang diinput vendor lewat Portal. PPIC membuka akses edit atas pengajuan perbaikan vendor; vendor memperbaiki lalu Close lagi (FR-117, UC-25).",
    render() {
      const ql = (U.ui.q || "").toLowerCase(), l = S().lap.filter((x) => (!U.ui.st || x.status === U.ui.st) && (!ql || (x.no_laporan + " " + supName(x.id_vendor) + " " + x.kode_delivery).toLowerCase().includes(ql))).sort((a, b) => b.no_laporan.localeCompare(a.no_laporan));
      const pend = S().rev.filter((r) => r.status === "Diajukan").length;
      return card("Daftar Laporan Subkont", `<div class="cl-tools"><input class="ap-in" data-u="q" placeholder="Search No. Laporan / Vendor / Kode Delivery..." value="${esc(U.ui.q)}"><select class="ap-sel ap-in" data-u="st"><option value="">-- Semua Status --</option>${["Draft", "Closed", "Dibuka Kembali"].map((x) => `<option${U.ui.st === x ? " selected" : ""}>${x}</option>`).join("")}</select>${pend ? `<span class="ap-label ap-label--warning" style="margin-left:auto">${pend} pengajuan perbaikan menunggu</span>` : ""}</div>` +
        tbl(["No. Laporan", "Tgl Laporan", "Vendor", "Kode Delivery", "Jenis", "Material / Produk", "No. PO", "Status", "Pengajuan Perbaikan", "Action"], l.map((x) => { const pr = revOf(x.no_laporan).find((r) => r.status === "Diajukan");
          return `<tr><td><b>${esc(x.no_laporan)}</b></td><td style="white-space:nowrap">${fdate(x.tgl_laporan)}</td><td>${esc(supName(x.id_vendor))}</td><td>${esc(x.kode_delivery)}</td><td>${jenisOf(x)}</td><td>${esc(W.nama(x.kode_barang))}</td><td>${esc(x.no_po || "-")}</td><td style="text-align:center">${stl(x.status)}</td><td>${pr ? `<b>Diajukan</b>: ${esc(pr.alasan)}` : "-"}</td><td class="ap-act ap-act-nowrap">${btn("info", "view", x.no_laporan, "View")}${pr ? btn("warning", "open", x.no_laporan, "Buka Akses Edit") : ""}</td></tr>`; }).join(""), "Belum ada Laporan Subkont dari vendor.")) +
        '<p class="note">Buka Akses Edit hanya tersedia untuk laporan Closed yang punya pengajuan perbaikan. Pembukaan akses (siapa, kapan) tercatat; versi lama laporan tidak hilang. Pembukaan akses dari temuan Incoming ditambahkan pada tahap Incoming.</p>';
    },
    act: {
      view: (d) => viewLap(d.id),
      open(d) {
        const l = S().lap.find((x) => x.no_laporan === d.id), r = revOf(l.no_laporan).find((x) => x.status === "Diajukan"); if (!r) { warn("Tidak ada pengajuan perbaikan."); return; }
        dialog({ icon: "question", title: "Buka akses edit?", text: `Vendor ${supName(l.id_vendor)} dapat mengedit ${l.no_laporan} kembali. Alasan vendor: "${r.alasan}".`, ok: "Ya, Buka Akses", cancel: "Batal" }).then((ok) => {
          if (!ok) return; r.status = "Dibuka"; r.diputuskan_by = PPIC; r.diputuskan_at = now(); l.status = "Dibuka Kembali"; l.reopened = true; W.save(); U.render(); toast(`Akses edit ${l.no_laporan} dibuka untuk vendor.`, "ok");
        });
      },
    },
  };

  U.init(P);
})();
