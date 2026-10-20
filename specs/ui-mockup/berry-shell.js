/* ============================================================
   berry-shell.js — Kerangka tema Berry (sidebar + top bar + kartu judul/breadcrumb) untuk
   SEMUA mockup (Modul Produksi dan Product Costing). Meniru themes/admin/views/header.php dan
   application/libraries/Menu_generator.php. Sidebar/top bar dirender di Shadow DOM dengan CSS Berry
   asli (jalur relatif ../../assets/berry/…) supaya tidak bentrok dengan styles.css / pc.css.
   Ketergantungan: folder specs/ harus tetap berada di dalam repo (assets/ ada dua tingkat di atas).
   Pemakaian: halaman Produksi → auto-mount (body[data-user], [data-role]); halaman pc-* →
   pc-shell.js memanggil BERRY.mount({ user, badges }) setelah membangun isi.
   ============================================================ */
(() => {
  "use strict";

  const A = "../../assets/";
  const CSS = [
    A + "berry/css/custom-theme.css",
    A + "berry/css/style.css",
    A + "berry/css/custom.css",
    A + "berry/css/style-preset.css",
    A + "berry/fonts/tabler-icons.min.css",
    "berry-vars.css",
    "berry-shell-shadow.css",
  ];
  const BRAND = "PT HFG";                                   // nm_perusahaan aplikasi tidak tersedia di mockup

  // Menu mengikuti struktur Menu_generator: Dashboard, grup level-2, dan level-3 (Product Costing).
  const PRODUKSI = [
    ["beranda.html", "Daftar SPK", "ti-clipboard-list"],
    ["index.html", "Input Produksi", "ti-forms"],
    ["material.html", "Material", "ti-stack-2"],
    ["costbook.html", "Costbook", "ti-coin"],
    ["rate.html", "Rate", "ti-percentage"],
    ["hpp.html", "HPP", "ti-report-analytics"],
  ];
  const COSTING = [
    { group: "Master Data", icon: "ti-database", items: [
      ["pc-produk.html", "Master Produk", "ti-box"],
      ["pc-material.html", "Master Material", "ti-stack"],
      ["pc-standard.html", "Master Standard Material", "ti-adjustments"],
      ["pc-bom.html", "BOM Produk", "ti-list-check"],
      ["pc-flow.html", "Flow Process & Cycletime", "ti-route"],
      ["pc-kurs.html", "Kurs", "ti-currency-dollar"],
      ["pc-price-reference.html", "Price Reference", "ti-tag"],
    ] },
    { group: "Rate", icon: "ti-percentage", items: [
      ["pc-man-power-rate.html", "Man Power Rate (Operator)", "ti-users"],
      ["pc-borongan.html", "Rate Borongan Helper", "ti-user-plus"],
      ["pc-faktor-indirect.html", "Faktor Indirect Labor", "ti-math-symbols"],
      ["pc-bpk-foh.html", "Rate Bahan Pendukung & FOH", "ti-flame"],
      ["pc-general-rate.html", "General Rate", "ti-settings"],
      ["pc-negotiation.html", "Negotiation Allowance", "ti-discount-2"],
    ] },
    { group: "Hasil & Pricelist", icon: "ti-report-money", items: [
      ["pc-simulasi.html", "Simulasi Costing", "ti-calculator"],
      ["pc-pricelist.html", "Pricelist", "ti-file-invoice"],
      ["pc-approval.html", "Approval Pricelist", "ti-checkbox"],
      ["pc-catalog.html", "Product Catalog", "ti-book"],
    ] },
  ];

  const esc = (s) => String(s == null ? "" : s).replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));
  const chevron = '<svg class="feather feather-chevron-right" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';
  const here = () => (location.pathname.split("/").pop() || "index.html").toLowerCase();

  let mounted = false;

  function build(opts) {
    const cur = here();
    const badges = opts.badges || {};
    const leaf = (it, extra = "") => `<li class="pc-item${it[0] === cur ? " active" : ""}"${extra}>
        <a class="pc-link" href="${it[0]}"><span class="pc-micon"><i class="ti ${it[2]}"></i></span><span class="pc-mtext">${esc(it[1])}</span>${badges[it[0]] ? `<span class="berry-badge" title="${badges[it[0]]} varian BOM belum lengkap datanya">&#9888; ${badges[it[0]]}</span>` : ""}</a></li>`;
    const parent = (icon, title, inner, open, nested = false) => `<li class="pc-item pc-hasmenu${open ? " active pc-trigger" : ""}">
        <a href="#!" class="pc-link"><span class="pc-micon"><i class="ti ${icon}"></i></span><span class="pc-mtext">${esc(title)}</span><span class="pc-arrow">${chevron}</span></a>
        <ul class="pc-submenu">${inner}</ul></li>`;

    const prodOpen = PRODUKSI.some((x) => x[0] === cur);
    const pcOpen = (g) => g.items.some((x) => x[0] === cur);
    const menu = `<ul class="pc-navbar">
      <li class="pc-item"><a href="beranda.html" class="pc-link"><span class="pc-micon"><i class="ti ti-dashboard"></i></span><span class="pc-mtext">Dashboard</span></a></li>
      ${parent("ti-building-factory", "Produksi", PRODUKSI.map((x) => leaf(x)).join(""), prodOpen)}
      ${parent("ti-calculator", "Product Costing", COSTING.map((g) => parent(g.icon, g.group, g.items.map((x) => leaf(x)).join(""), pcOpen(g))).join(""), COSTING.some(pcOpen))}
    </ul>`;

    const u = opts.user || {};
    return `
      ${CSS.map((h) => `<link rel="stylesheet" href="${h}">`).join("")}
      <div class="berry-root" data-pc-preset="preset-1" data-pc-sidebar-theme="light" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light" data-bs-theme="light">
        <nav class="pc-sidebar" id="berrySidebar">
          <div class="navbar-wrapper">
            <div class="m-header">
              <a href="beranda.html" class="b-brand text-decoration-none"><img src="${A}images/iconhfg.png" alt="logo"><span class="b-title">${esc(BRAND)}</span></a>
            </div>
            <div class="navbar-content">
              <div class="pc-sidebar-search"><div class="position-relative">
                <i class="ti ti-search pc-search-icon"></i>
                <input type="text" class="form-control" id="berrySearch" placeholder="Cari menu / sub menu... (Ctrl+K)" autocomplete="off" spellcheck="false">
                <button type="button" class="btn pc-search-clear" id="berrySearchClear" style="display:none" title="Bersihkan pencarian"><i class="ti ti-x"></i></button>
              </div></div>
              ${menu}
            </div>
          </div>
        </nav>
        <div class="berry-overlay" id="berryOverlay"></div>
        <header class="pc-header">
          <div class="header-wrapper px-4">
            <div class="me-auto"><ul class="list-unstyled mb-0 d-flex align-items-center">
              <li class="pc-h-item d-lg-none"><a href="#" class="pc-head-link ms-0" id="berryMobile" title="Menu"><i class="ti ti-menu-2"></i></a></li>
              <li class="pc-h-item d-none d-lg-inline-flex"><a href="#" class="pc-head-link ms-0" id="berryHide" title="Tampilkan / sembunyikan menu"><i class="ti ti-menu-2"></i></a></li>
            </ul></div>
            <div class="ms-auto"><ul class="list-unstyled">
              <li class="dropdown pc-h-item">
                <a class="pc-head-link head-link-secondary dropdown-toggle arrow-none me-0" data-berry-dd href="#" role="button" aria-expanded="false"><i class="ti ti-bell"></i></a>
                <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
                  <div class="dropdown-header"><a href="#!" class="link-primary float-end text-decoration-underline">Mark as all read</a><h5 class="mb-0">All Notification <span class="badge bg-warning rounded-pill ms-1">01</span></h5></div>
                  <div class="dropdown-header px-0 text-wrap"><div class="list-group list-group-flush w-100">
                    <div class="list-group-item list-group-item-action"><div class="d-flex">
                      <div class="flex-shrink-0"><div class="user-avtar bg-light-success"><i class="ti ti-building-store"></i></div></div>
                      <div class="flex-grow-1 ms-2"><span class="float-end text-muted small">3 min ago</span><h6 class="mb-1">System Info</h6><p class="text-body fs-6 mb-1">Mockup UI/UX — data contoh, bukan data klien.</p><span class="badge rounded-pill bg-light-danger">Unread</span></div>
                    </div></div>
                  </div></div>
                  <div class="dropdown-divider"></div><div class="text-center py-2"><a href="#!" class="link-primary">View all</a></div>
                </div>
              </li>
              <li class="dropdown pc-h-item header-user-profile">
                <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0" data-berry-dd href="#" role="button" aria-expanded="false">
                  <img src="${A}images/male-def.png" alt="user-image" class="user-avtar"><span><i class="ti ti-settings"></i></span>
                </a>
                <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                  <div class="dropdown-header">
                    <h4 class="mb-0"><span id="berryGreet">Hello</span>, <span class="small text-muted" id="berryUserName">${esc(u.name || "")}</span></h4>
                    <p class="text-muted mb-2 d-flex align-items-center gap-2"><i class="ti ti-calendar"></i><span id="berryClock">--</span></p>
                    <p class="text-muted mb-2 berry-role" id="berryUserRole">${esc(u.role || "")}</p>
                    <hr>
                    <a href="#" class="dropdown-item"><i class="ti ti-user"></i> <span>Profile</span></a>
                    <a href="#" class="dropdown-item"><i class="ti ti-settings"></i> <span>Account Settings</span></a>
                    <a href="#" class="dropdown-item text-danger"><i class="ti ti-logout"></i> <span>Logout</span></a>
                  </div>
                </div>
              </li>
            </ul></div>
          </div>
        </header>
      </div>`;
  }

  // Jejak breadcrumb: Home / Grup / (Subgrup) / Halaman — dari model menu yang sama.
  function crumbs() {
    const cur = here();
    const p = PRODUKSI.find((x) => x[0] === cur);
    if (p) return ["Produksi", p[1]];
    for (const g of COSTING) { const it = g.items.find((x) => x[0] === cur); if (it) return ["Product Costing", g.group, it[1]]; }
    return [];
  }

  function mount(opts = {}) {
    if (mounted) return;
    mounted = true;
    const body = document.body;
    body.classList.add("berry");
    const user = opts.user || { name: body.dataset.user || "", role: body.dataset.role || "" };

    // judul halaman → kartu judul (h1 asli disembunyikan, isi lain tetap)
    const main = document.querySelector("main.page");
    const h1 = main && main.querySelector(".page__header h1, h1");
    const title = opts.title || (h1 ? h1.textContent.trim() : document.title);
    if (h1) h1.classList.add("berry-h1-moved");
    const cr = crumbs();
    const trail = ['<li><a href="beranda.html"><i class="ti ti-home"></i> Home</a></li>']
      .concat(cr.map((c, i) => (i === cr.length - 1 ? `<li class="active" aria-current="page">${esc(c)}</li>` : `<li>${esc(c)}</li>`))).join("");
    const head = document.createElement("div");
    head.className = "berry-pagehead";
    head.innerHTML = `<div><h3>${esc(title)}</h3></div><nav aria-label="breadcrumb"><ol class="berry-crumb">${trail}</ol></nav>`;

    // ruang isi
    const wrap = document.createElement("div");
    wrap.className = "berry-main";
    if (main) { main.parentNode.insertBefore(wrap, main); wrap.appendChild(head); wrap.appendChild(main); }
    else { body.insertBefore(wrap, body.firstChild); wrap.appendChild(head); }

    // elemen penampung nama pengguna (app.js mengisinya lewat .topbar__user-name)
    const holder = document.createElement("span");
    holder.className = "topbar__user-name";
    holder.hidden = true;
    holder.textContent = user.name || "";
    body.appendChild(holder);

    // shadow host (sidebar + top bar)
    const host = document.createElement("div");
    host.id = "berry-host";
    host.style.visibility = "hidden";
    body.insertBefore(host, body.firstChild);
    const root = host.attachShadow({ mode: "open" });
    root.innerHTML = build(Object.assign({}, opts, { user }));
    const links = [...root.querySelectorAll("link")];
    const reveal = () => { host.style.visibility = "visible"; };
    Promise.all(links.map((l) => new Promise((ok) => { l.addEventListener("load", ok); l.addEventListener("error", ok); })))
      .then(reveal);
    setTimeout(reveal, 2500);

    wire(root, host, holder);
  }

  function wire(root, host, holder) {
    const $ = (s) => root.querySelector(s);
    const sidebar = $("#berrySidebar"), overlay = $("#berryOverlay");

    // nama pengguna mengikuti .topbar__user-name (diisi app.js)
    new MutationObserver(() => { $("#berryUserName").textContent = holder.textContent; })
      .observe(holder, { childList: true, characterData: true, subtree: true });

    // sapaan + waktu
    const tick = () => {
      const d = new Date(), h = d.getHours();
      $("#berryGreet").textContent = h < 11 ? "Selamat pagi" : h < 15 ? "Selamat siang" : h < 19 ? "Selamat sore" : "Selamat malam";
      $("#berryClock").textContent = d.toLocaleString("id-ID");
    };
    tick(); setInterval(tick, 30000);

    // dropdown
    const closeDD = () => root.querySelectorAll(".dropdown-menu.show").forEach((m) => {
      m.classList.remove("show");
      const t = m.parentElement.querySelector("[data-berry-dd]"); if (t) { t.setAttribute("aria-expanded", "false"); t.classList.remove("show"); }
    });
    root.addEventListener("click", (e) => {
      const tg = e.target.closest("[data-berry-dd]");
      if (tg) {
        e.preventDefault();
        const menu = tg.parentElement.querySelector(".dropdown-menu"), open = menu.classList.contains("show");
        closeDD();
        if (!open) { menu.classList.add("show"); menu.setAttribute("data-bs-popper", "static"); tg.setAttribute("aria-expanded", "true"); tg.classList.add("show"); }
        return;
      }
      if (e.target.closest(".dropdown-menu")) { const a = e.target.closest("a"); if (a && a.getAttribute("href") === "#") e.preventDefault(); return; }

      // submenu (akordeon per tingkat, seperti script.js Berry)
      const lk = e.target.closest(".pc-hasmenu > .pc-link");
      if (lk) {
        e.preventDefault();
        const li = lk.parentElement, open = li.classList.contains("pc-trigger");
        [...li.parentElement.children].forEach((sib) => { if (sib !== li) sib.classList.remove("pc-trigger"); });
        li.classList.toggle("pc-trigger", !open);
        return;
      }
      if (e.target.closest("#berryHide")) { e.preventDefault(); const c = sidebar.classList.toggle("pc-sidebar-hide"); document.body.classList.toggle("berry-collapsed", c); return; }
      if (e.target.closest("#berryMobile")) { e.preventDefault(); sidebar.classList.toggle("mob-sidebar-active"); overlay.classList.toggle("show"); return; }
      if (e.target.closest("#berrySearchClear")) { const i = $("#berrySearch"); i.value = ""; filter(""); i.focus(); }
    });
    overlay.addEventListener("click", () => { sidebar.classList.remove("mob-sidebar-active"); overlay.classList.remove("show"); });
    document.addEventListener("click", (e) => { if (!e.composedPath().includes(host)) closeDD(); });

    // pencarian menu (Ctrl+K), tanda sorot seperti aplikasi
    const marks = () => root.querySelectorAll(".pc-mtext").forEach((m) => { if (m.dataset.o != null) { m.textContent = m.dataset.o; } });
    const filter = (q) => {
      q = q.trim().toLowerCase();
      $("#berrySearchClear").style.display = q ? "" : "none";
      marks();
      const lis = [...root.querySelectorAll(".pc-navbar li")];
      if (!q) { lis.forEach((li) => { li.style.display = ""; }); return; }
      const leaves = lis.filter((li) => !li.classList.contains("pc-hasmenu"));
      leaves.forEach((li) => {
        const t = li.querySelector(".pc-mtext"), txt = t.textContent, hit = txt.toLowerCase().indexOf(q);
        li.style.display = hit > -1 ? "" : "none";
        if (hit > -1) { t.dataset.o = txt; t.innerHTML = `${esc(txt.slice(0, hit))}<mark class="pc-search-highlight">${esc(txt.slice(hit, hit + q.length))}</mark>${esc(txt.slice(hit + q.length))}`; }
      });
      lis.filter((li) => li.classList.contains("pc-hasmenu")).reverse().forEach((li) => {
        const any = [...li.querySelectorAll(":scope > .pc-submenu > li")].some((c) => c.style.display !== "none");
        li.style.display = any ? "" : "none";
        if (any) li.classList.add("pc-trigger");
      });
    };
    $("#berrySearch").addEventListener("input", (e) => filter(e.target.value));
    document.addEventListener("keydown", (e) => { if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "k") { e.preventDefault(); $("#berrySearch").focus(); } });
  }

  window.BERRY = { mount, menu: { PRODUKSI, COSTING } };
  // halaman Produksi (statis) → auto-mount; halaman pc-* memanggil mount() sendiri dari pc-shell.js
  document.addEventListener("DOMContentLoaded", () => { if (!document.body.dataset.page && !document.body.dataset.berryManual) mount(); });
})();
