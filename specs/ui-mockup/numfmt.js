/* Pemformat angka untuk kolom input (mockup): pemisah ribuan titik, desimal koma (format Indonesia).
   Dipakai oleh input ber-atribut data-numfmt="1". Nilai yang dibaca program memakai NumFmt.norm() / NumFmt.parse(). */
(function () {
  const fmt = (v) => {
    if (v === "" || v == null || (typeof v === "number" && !isFinite(v))) return "";
    const n = Number(v);
    return isNaN(n) ? String(v) : n.toLocaleString("id-ID", { maximumFractionDigits: 4 });
  };
  // "2.306.810,81" -> "2306810.81" (string berformat titik-desimal, siap parseFloat)
  const norm = (s) => String(s == null ? "" : s).replace(/\./g, "").replace(",", ".");
  const parse = (s) => parseFloat(norm(s));

  // Format langsung saat mengetik; posisi kursor dijaga berdasarkan jumlah digit/koma sebelum kursor.
  function live(t) {
    const v = t.value, pos = t.selectionStart == null ? v.length : t.selectionStart;
    const before = v.slice(0, pos).replace(/[^0-9,]/g, "").length;
    const raw = v.replace(/[^0-9,]/g, "");
    const ci = raw.indexOf(",");
    let ip = ci < 0 ? raw : raw.slice(0, ci);
    const dp = ci < 0 ? null : raw.slice(ci + 1).replace(/,/g, "");
    ip = ip.replace(/^0+(?=\d)/, "");
    const out = ip.replace(/\B(?=(\d{3})+(?!\d))/g, ".") + (dp !== null ? "," + dp : "");
    if (out === v) return;
    t.value = out;
    let c = 0, n = 0;
    while (c < out.length && n < before) { if (/[0-9,]/.test(out[c])) n++; c++; }
    try { t.setSelectionRange(c, c); } catch (e) { /* tipe input tanpa seleksi */ }
  }

  // fase capture: terformat sebelum penangan halaman membaca nilainya
  document.addEventListener("input", (e) => {
    const t = e.target;
    if (t && t.dataset && t.dataset.numfmt === "1") live(t);
  }, true);

  window.NumFmt = { fmt, norm, parse, live };
})();
