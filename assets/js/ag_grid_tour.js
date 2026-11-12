/**
 * ag_grid_tour.js - Universal Tour & Guidance Helper for ag-Grid in CodeIgniter
 * Author: Antigravity IDE
 * Description: Menyediakan panduan interaktif modular berbasis Driver.js untuk tabel ag-Grid
 *              dengan animasi gesture visual dan konfigurasi yang fleksibel.
 */

(function (window, $) {
  'use strict';

  // Inject CSS Styles for visual animations if not already present
  function ensureTourStyles() {
    if (document.getElementById('ag-grid-tour-styles')) return;

    var css = `
      .tour-demo-box {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
      }
      .tour-demo-track {
        width: 65px;
        height: 30px;
        background: #e2e8f0;
        border-radius: 6px;
        position: relative;
        overflow: hidden;
        flex-shrink: 0;
      }
      .tour-demo-col {
        width: 28px;
        height: 100%;
        background: #3b82f6;
        border-radius: 4px;
        position: absolute;
        top: 0;
        left: 4px;
        animation: slideCol 2.4s infinite ease-in-out;
      }
      .tour-demo-cursor {
        position: absolute;
        top: 6px;
        left: 8px;
        font-size: 13px;
        animation: slideCursor 2.4s infinite ease-in-out;
        pointer-events: none;
      }
      @keyframes slideCol {
        0%, 15% { transform: translateX(0); }
        50%, 65% { transform: translateX(28px); }
        100% { transform: translateX(0); }
      }
      @keyframes slideCursor {
        0%, 15% { transform: translateX(0) scale(1); }
        20% { transform: scale(0.85); }
        50%, 65% { transform: translateX(28px) scale(0.85); }
        70% { transform: scale(1); }
        100% { transform: translateX(0); }
      }
      .tour-click-demo {
        width: 32px;
        height: 32px;
        background: #e0e7ff;
        color: #4338ca;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
        animation: clickPulse 1.8s infinite ease-in-out;
      }
      @keyframes clickPulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
        50% { transform: scale(0.9); box-shadow: 0 0 0 6px rgba(99, 102, 241, 0); }
      }
      .tour-demo-text {
        font-size: 12px;
        color: #475569;
        line-height: 1.35;
      }
    `;

    var style = document.createElement('style');
    style.id = 'ag-grid-tour-styles';
    style.type = 'text/css';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);
  }

  /**
   * Fungsi Global: startAgGridTour(options)
   * 
   * @param {Object} options Konfigurasi tour:
   *   - gridElement: selector container ag-grid (default: '#spkGrid' atau '.ag-theme-alpine')
   *   - tabsElement: selector nav-tabs jika ada (opsional)
   *   - tabsTitle: judul tab step
   *   - tabsDesc: deskripsi tab step
   *   - searchElement: selector input search (default: '#quickSearch')
   *   - searchDesc: deskripsi fitur search
   *   - actionColId: col-id kolom aksi (default: 'action')
   *   - actionTitle: judul step aksi
   *   - actionDesc: deskripsi step aksi
   *   - customSteps: Array step Driver.js tambahan (opsional)
   */
  window.startAgGridTour = function (options) {
    options = options || {};

    if (typeof window.driver === 'undefined' || typeof window.driver.js === 'undefined') {
      alert('Library panduan (Driver.js) sedang dimuat, silakan coba sesaat lagi.');
      return;
    }

    ensureTourStyles();

    var gridSelector = options.gridElement || '#spkGrid';
    if (!$(gridSelector).length) {
      gridSelector = '.ag-theme-alpine:first';
    }

    var steps = [];

    // 1. Tab Bar Step (Jika ada)
    if (options.tabsElement && $(options.tabsElement).is(':visible')) {
      steps.push({
        element: options.tabsElement,
        popover: {
          title: options.tabsTitle || 'Tab Status & Kategori',
          description: options.tabsDesc || 'Data dipisahkan berdasarkan tab untuk mempermudah monitoring status dan penyaringan pekerjaan.',
          side: 'bottom',
          align: 'start'
        }
      });
    }

    // 2. Pinned Column Step (Jika ada)
    var pinnedCell = options.pinnedColId
      ? gridSelector + ' .ag-header-cell[col-id="' + options.pinnedColId + '"]'
      : gridSelector + ' .ag-pinned-left-header .ag-header-cell:first';

    if ($(pinnedCell).length) {
      steps.push({
        element: pinnedCell,
        popover: {
          title: options.pinnedTitle || 'Kolom Tetap (Pinned)',
          description: options.pinnedDesc || 'Kolom ini disematkan di sisi kiri agar nomor acuan tetap terlihat saat tabel digeser ke kanan.',
          side: 'bottom',
          align: 'start'
        }
      });
    }

    // 3. Sorting Step
    var sortCell = options.sortColId
      ? gridSelector + ' .ag-header-cell[col-id="' + options.sortColId + '"]'
      : gridSelector + ' .ag-header-cell:not(.ag-header-cell-moving):eq(1)';

    if ($(sortCell).length) {
      steps.push({
        element: sortCell,
        popover: {
          title: 'Urutkan Data (Sorting)',
          description: 'Klik judul kolom mana saja untuk mengurutkan data (A-Z atau angka terbesar/terkecil). Klik sekali lagi untuk membalik urutan.<div class="tour-demo-box"><div class="tour-click-demo"><i class="fa fa-mouse-pointer"></i></div><div class="tour-demo-text"><b>Cukup 1 klik:</b> Klik header kolom untuk langsung menyortir baris data.</div></div>',
          side: 'bottom',
          align: 'start'
        }
      });
    }

    // 4. Drag Column & Resize Step
    var dragCell = options.dragColId
      ? gridSelector + ' .ag-header-cell[col-id="' + options.dragColId + '"]'
      : gridSelector + ' .ag-header-cell:not(.ag-header-cell-moving):eq(2)';

    if ($(dragCell).length) {
      steps.push({
        element: dragCell,
        popover: {
          title: 'Atur Posisi & Lebar Kolom',
          description: 'Anda bisa menyesuaikan tampilan tabel sesuai kenyamanan kerja:<div class="tour-demo-box"><div class="tour-demo-track"><div class="tour-demo-col"></div><span class="tour-demo-cursor">👆</span></div><div class="tour-demo-text"><b>• Geser Kolom:</b> Klik & tahan judul kolom lalu seret ke kiri/kanan.<br><b>• Ubah Lebar:</b> Tarik garis pembatas antar kolom di baris header.</div></div>',
          side: 'bottom',
          align: 'start'
        }
      });
    }

    // 5. Quick Search Step
    var searchSelector = options.searchElement || '#quickSearch';
    if ($(searchSelector).is(':visible')) {
      steps.push({
        element: searchSelector,
        popover: {
          title: 'Pencarian Cepat',
          description: options.searchDesc || 'Ketik kata kunci apa saja (nomor referensi, nama produk, status, dsb) untuk memfilter tabel seketika.',
          side: 'bottom',
          align: 'end'
        }
      });
    }

    // 6. Action Column Step
    var actionColId = options.actionColId || 'action';
    var actionCell = gridSelector + ' .ag-header-cell[col-id="' + actionColId + '"]';
    if ($(actionCell).length) {
      steps.push({
        element: actionCell,
        popover: {
          title: options.actionTitle || 'Aksi & Tindakan',
          description: options.actionDesc || 'Gunakan tombol pada kolom aksi ini untuk memproses data, melihat detail, atau mengubah status data.',
          side: 'left',
          align: 'center'
        }
      });
    }

    // 7. Pagination Step
    var pagingPanel = gridSelector + ' .ag-paging-panel';
    if ($(pagingPanel).length) {
      steps.push({
        element: pagingPanel,
        popover: {
          title: 'Navigasi Halaman',
          description: 'Atur jumlah baris per halaman serta navigasi antar halaman dari panel kontrol di bagian bawah tabel ini.',
          side: 'top',
          align: 'center'
        }
      });
    }

    // 8. Custom Steps tambahan jika di-passing dari luar
    if (options.customSteps && Array.isArray(options.customSteps)) {
      steps = steps.concat(options.customSteps);
    }

    // Jalankan Driver.js
    var driverObj = window.driver.js.driver({
      showProgress: true,
      animate: true,
      allowClose: true,
      overlayColor: 'rgba(15, 23, 42, 0.6)',
      nextBtnText: 'Lanjut &rarr;',
      prevBtnText: '&larr; Kembali',
      doneBtnText: 'Mengerti',
      steps: steps
    });

    driverObj.drive();
  };

})(window, jQuery);
