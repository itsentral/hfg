<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<!-- Bootstrap 5, FontAwesome, ag-Grid -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.3.2/styles/ag-grid.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.3.2/styles/ag-theme-alpine.min.css">
<!-- Driver.js (Interactive Feature Tour) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css" />

<style>
  :root {
    --pr-primary: #1e3a8a;
    --pr-primary-dark: #172554;
    --pr-primary-soft: #eff6ff;
    --pr-accent: #2563eb;
    --pr-text: #0f172a;
    --pr-muted: #64748b;
    --pr-border: #e2e8f0;
    --pr-ok: #15803d;
    --pr-ok-soft: #dcfce7;
    --pr-warn: #b45309;
    --pr-warn-soft: #fef3c7;
    --pr-draft: #475569;
    --pr-draft-soft: #f1f5f9;
  }

  .pr-page-header {
    background: #fff;
    border-radius: 12px;
    padding: 22px 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    border: 1px solid var(--pr-border);
  }

  .pr-page-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: var(--pr-text);
    margin-bottom: 4px;
  }

  .pr-page-header p {
    color: var(--pr-muted);
    font-size: 13.5px;
    margin-bottom: 0;
  }

  .pr-stats-row {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }

  .pr-stat-card {
    flex: 1;
    min-width: 180px;
    background: #fff;
    border: 1px solid var(--pr-border);
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .pr-stat-card .val {
    font-size: 28px;
    font-weight: 800;
    color: var(--pr-primary);
    font-variant-numeric: tabular-nums;
  }

  .pr-stat-card .lbl {
    font-size: 12.5px;
    color: var(--pr-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .pr-table-card {
    background: #fff;
    border: 1px solid var(--pr-border);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
  }

  .pr-table-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 12px;
  }

  .pr-table-head h2 {
    font-size: 17px;
    font-weight: 700;
    margin: 0;
    color: var(--pr-text);
  }

  .pr-table-head p {
    font-size: 13px;
    color: var(--pr-muted);
    margin: 2px 0 0;
  }

  #spkGrid {
    width: 100%;
    height: 520px;
    border-radius: 8px;
    overflow: hidden;
  }

  /* Izinkan seleksi & copy teks pada cell ag-Grid */
  .ag-theme-alpine .ag-cell {
    user-select: text !important;
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
    cursor: text;
  }

  /* Biarkan kursor tombol/link tetap pointer */
  .ag-theme-alpine .ag-cell a,
  .ag-theme-alpine .ag-cell button {
    cursor: pointer;
  }

  .badge-menunggu {
    background-color: var(--pr-warn-soft);
    color: var(--pr-warn);
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.76rem;
    border: 1px solid #fde68a;
    display: inline-block;
  }

  .badge-done {
    background-color: var(--pr-ok-soft);
    color: var(--pr-ok);
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.76rem;
    border: 1px solid #bbf7d0;
    display: inline-block;
  }

  .badge-draft {
    background-color: var(--pr-draft-soft);
    color: var(--pr-draft);
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.76rem;
    border: 1px solid #cbd5e1;
    display: inline-block;
  }

  .badge-sub-status {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 10.5px;
    font-weight: 600;
    line-height: 1;
    padding: 2px 7px;
    border-radius: 4px;
    margin-top: 3px;
  }

  .badge-sub-menunggu {
    background-color: var(--pr-warn-soft);
    color: var(--pr-warn);
    border: 1px solid #fde68a;
  }

  .badge-sub-done {
    background-color: var(--pr-ok-soft);
    color: var(--pr-ok);
    border: 1px solid #bbf7d0;
  }

  .badge-sub-draft {
    background-color: var(--pr-draft-soft);
    color: var(--pr-draft);
    border: 1px solid #cbd5e1;
  }

  .badge-chip {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
    background: var(--pr-primary-soft);
    color: var(--pr-accent);
    margin-left: 4px;
  }

  .nav-tabs-pr {
    border-bottom: 2px solid var(--pr-border);
    margin-bottom: 16px;
    gap: 8px;
  }

  .nav-tabs-pr .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: var(--pr-muted);
    font-weight: 600;
    font-size: 14px;
    padding: 10px 16px;
    border-radius: 0;
    background: transparent;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .nav-tabs-pr .nav-link:hover {
    color: var(--pr-accent);
    background: var(--pr-primary-soft);
    border-radius: 8px 8px 0 0;
  }

  .nav-tabs-pr .nav-link.active {
    color: var(--pr-primary);
    border-bottom-color: var(--pr-primary);
    background: transparent;
  }

  .tab-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
  }

  .tab-badge-open {
    background: var(--pr-warn-soft);
    color: var(--pr-warn);
    border: 1px solid #fde68a;
  }

  .tab-badge-closed {
    background: var(--pr-ok-soft);
    color: var(--pr-ok);
    border: 1px solid #bbf7d0;
  }

  /* Tour Demo Visual Animation */
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

    0%,
    15% {
      transform: translateX(0);
    }

    50%,
    65% {
      transform: translateX(28px);
    }

    100% {
      transform: translateX(0);
    }
  }

  @keyframes slideCursor {

    0%,
    15% {
      transform: translateX(0) scale(1);
    }

    20% {
      transform: scale(0.85);
    }

    50%,
    65% {
      transform: translateX(28px) scale(0.85);
    }

    70% {
      transform: scale(1);
    }

    100% {
      transform: translateX(0);
    }
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

    0%,
    100% {
      transform: scale(1);
      box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4);
    }

    50% {
      transform: scale(0.9);
      box-shadow: 0 0 0 6px rgba(99, 102, 241, 0);
    }
  }

  .tour-demo-text {
    font-size: 12px;
    color: #475569;
    line-height: 1.35;
  }

  .tab-badge-all {
    background: var(--pr-primary-soft);
    color: var(--pr-accent);
    border: 1px solid #bfdbfe;
  }
</style>

<div class="container-fluid px-3 py-2">
  <div class="pr-page-header d-flex justify-content-between align-items-center">
    <div>
      <h1>SPK Menunggu Produksi</h1>
      <p>Pilih SPK untuk meninjau data tarikan lalu menginput laporan hasil produksi aktual.</p>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-primary btn-sm" id="btnStartTour" title="Panduan Fitur Interaktif Tabel ag-Grid">
        <i class="fa fa-question-circle me-1"></i> Panduan Fitur Tabel
      </button>
      <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRefreshGrid">
        <i class="fa fa-refresh me-1"></i> Refresh Data
      </button>
    </div>
  </div>

  <div class="pr-stats-row">
    <div class="pr-stat-card">
      <span class="val" id="statMenunggu">0</span>
      <span class="lbl">SPK Menunggu</span>
    </div>
    <div class="pr-stat-card">
      <span class="val" id="statDone">0</span>
      <span class="lbl">SPK Selesai (Done)</span>
    </div>
    <div class="pr-stat-card">
      <span class="val" id="statTotalQty">0</span>
      <span class="lbl">Total Target Qty (pcs)</span>
    </div>
  </div>

  <div class="pr-table-card">
    <div class="pr-table-head">
      <div>
        <h2>Daftar Surat Perintah Kerja</h2>
        <p>Setiap SPK memuat daftar produk Finish Good KW 1 yang siap dilaporkan hasil produksinya.</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div class="input-group input-group-sm" style="max-width: 280px;">
          <span class="input-group-text bg-light border-end-0"><i class="fa fa-search text-muted"></i></span>
          <input type="text" class="form-control border-start-0 ps-0" id="quickSearch" placeholder="Cari No. SPK / Produk...">
        </div>
      </div>
    </div>

    <!-- Navigation Tabs: Open vs Closed -->
    <ul class="nav nav-tabs nav-tabs-pr px-3 pt-2 mb-2" id="prTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tabOpen" data-filter="open" type="button" role="tab">
          <i class="fa fa-clock-o text-warning me-1"></i> Open / Dalam Proses
          <span class="tab-badge tab-badge-open" id="badgeTabOpen">0</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tabClosed" data-filter="closed" type="button" role="tab">
          <i class="fa fa-check-circle text-success me-1"></i> Selesai (Closed)
          <span class="tab-badge tab-badge-closed" id="badgeTabClosed">0</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tabAll" data-filter="all" type="button" role="tab">
          <i class="fa fa-list text-primary me-1"></i> Semua SPK
          <span class="tab-badge tab-badge-all" id="badgeTabAll">0</span>
        </button>
      </li>
    </ul>

    <!-- ag-Grid Container -->
    <div id="spkGrid" class="ag-theme-alpine"></div>
  </div>
</div>

<!-- ag-Grid Community Script -->
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community@31.3.2/dist/ag-grid-community.min.js"></script>
<!-- Driver.js Script -->
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
<!-- Universal ag-Grid Tour Helper -->
<script src="<?= base_url('assets/js/ag_grid_tour.js'); ?>"></script>

<script>
  $(document).ready(function() {
    let gridApi = null;

    const columnDefs = [{
        headerName: "No. SPK",
        field: "spk_no",
        width: 200,
        pinned: 'left',
        cellRenderer: function(params) {
          const spk = params.value || '-';
          const st = params.data.report_status;
          let badgeHtml = '';
          if (st === 'Done') {
            badgeHtml = '<span class="badge-sub-status badge-sub-done"><i class="fa fa-check-circle"></i> Selesai</span>';
          } else if (st === 'Draft') {
            badgeHtml = '<span class="badge-sub-status badge-sub-draft"><i class="fa fa-pencil"></i> Draft</span>';
          } else {
            badgeHtml = '<span class="badge-sub-status badge-sub-menunggu"><i class="fa fa-clock-o"></i> Menunggu</span>';
          }
          return '<div class="d-flex flex-column justify-content-center" style="height: 100%; line-height: 1.25;">' +
            '<span class="fw-bold text-dark">' + spk + '</span>' +
            '<div>' + badgeHtml + '</div>' +
            '</div>';
        }
      },
      {
        headerName: "Tanggal SPK",
        field: "tgl_spk",
        width: 130
      },
      {
        headerName: "Produk FG (KW 1)",
        field: "produk_fg_summary",
        flex: 1,
        minWidth: 320,
        cellRenderer: function(params) {
          if (!params.value) return '<span class="text-muted">-</span>';
          const parts = params.value.split(', ');
          return parts.map(function(p) {
            return '<span class="me-2"><i class="fa fa-cube text-primary me-1"></i>' + p + '</span>';
          }).join(' ');
        }
      },
      {
        headerName: "Target Total",
        field: "total_target_qty",
        width: 140,
        type: 'rightAligned',
        cellRenderer: function(params) {
          return '<span class="badge-chip">' + Number(params.value || 0).toLocaleString('id-ID') + ' pcs</span>';
        }
      },
      {
        headerName: "Catatan SPK",
        field: "catatan",
        width: 180,
        cellRenderer: function(params) {
          return params.value ? '<small class="text-muted">' + params.value + '</small>' : '<span class="text-muted">-</span>';
        }
      },
      {
        headerName: "Aksi",
        field: "action",
        width: 195,
        pinned: 'right',
        cellRenderer: function(params) {
          const spkNo = params.data.spk_no;
          const reportStatus = params.data.report_status;
          const reportId = params.data.report_id;

          if (reportStatus === 'Done' && reportId) {
            return '<div class="d-flex gap-1">' +
              '<a href="<?= site_url('production_report/view/') ?>' + reportId + '" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Lihat Detail Laporan">' +
                '<i class="fa fa-eye me-1"></i>Laporan</a>' +
              '<a href="<?= site_url('production_report/hpp/') ?>' + reportId + '" class="btn btn-sm btn-outline-primary py-1 px-2" title="Lihat Perhitungan HPP & Jurnal">' +
                '<i class="fa fa-calculator me-1"></i>HPP</a>' +
              '</div>';
          } else if (reportStatus === 'Draft' && spkNo) {
            return '<a href="<?= site_url('production_report/create/') ?>' + encodeURIComponent(spkNo) + '" class="btn btn-sm btn-warning py-1 px-2 text-dark">' +
              '<i class="fa fa-edit me-1"></i>Lanjutkan Draft</a>';
          } else {
            return '<a href="<?= site_url('production_report/create/') ?>' + encodeURIComponent(spkNo) + '" class="btn btn-sm btn-primary py-1 px-2">' +
              'Buat Laporan <i class="fa fa-arrow-right ms-1"></i></a>';
          }
        }
      }
    ];

    const gridOptions = {
      columnDefs: columnDefs,
      rowData: [],
      enableCellTextSelection: true,
      ensureDomOrder: true,
      pagination: true,
      paginationPageSize: 15,
      paginationPageSizeSelector: [10, 15, 25, 50],
      animateRows: true,
      rowHeight: 52,
      headerHeight: 42,
      defaultColDef: {
        sortable: true,
        filter: true,
        resizable: true
      }
    };

    let allRowData = [];
    let currentTab = 'open'; // default: 'open'

    const gridDiv = document.querySelector('#spkGrid');
    gridApi = agGrid.createGrid(gridDiv, gridOptions);

    function applyTabFilter() {
      if (!gridApi) return;
      let filtered = [];
      if (currentTab === 'open') {
        filtered = allRowData.filter(function(row) {
          return row.report_status !== 'Done';
        });
      } else if (currentTab === 'closed') {
        filtered = allRowData.filter(function(row) {
          return row.report_status === 'Done';
        });
      } else {
        filtered = allRowData;
      }
      gridApi.setGridOption('rowData', filtered);
    }

    function loadData() {
      if (gridApi) {
        gridApi.showLoadingOverlay();
      }

      $.ajax({
        url: "<?= site_url('production_report/ajax_spk_list'); ?>",
        type: "GET",
        dataType: "json",
        success: function(res) {
          if (res.status === 1) {
            allRowData = res.data || [];

            let openCount = 0;
            let closedCount = 0;
            allRowData.forEach(function(r) {
              if (r.report_status === 'Done') {
                closedCount++;
              } else {
                openCount++;
              }
            });

            $('#badgeTabOpen').text(openCount);
            $('#badgeTabClosed').text(closedCount);
            $('#badgeTabAll').text(allRowData.length);

            applyTabFilter();

            if (res.stats) {
              $('#statMenunggu').text(res.stats.waiting_count || openCount);
              $('#statDone').text(res.stats.done_count || closedCount);
              $('#statTotalQty').text(Number(res.stats.total_target_qty || 0).toLocaleString('id-ID'));
            }
          }
        },
        error: function(xhr, status, err) {
          console.error("Gagal mengambil data SPK:", err);
        }
      });
    }

    loadData();

    $('#prTabs button').on('click', function(e) {
      e.preventDefault();
      $('#prTabs button').removeClass('active');
      $(this).addClass('active');
      currentTab = $(this).data('filter');
      applyTabFilter();
    });

    $('#btnRefreshGrid').on('click', function() {
      loadData();
    });

    $('#quickSearch').on('input keyup', function() {
      if (gridApi) {
        gridApi.setGridOption('quickFilterText', $(this).val());
      }
    });
    // ============================================================
    // UNIVERSAL PANDUAN FITUR TABEL (ag_grid_tour.js)
    // ============================================================
    $('#btnStartTour').on('click', function(e) {
      e.preventDefault();
      startAgGridTour({
        gridElement: '#spkGrid',
        tabsElement: '#prTabs',
        tabsTitle: 'Tab Status SPK',
        tabsDesc: 'Data dipisahkan berdasarkan status pengerjaan: <b>Open</b> (SPK baru & draft) dan <b>Selesai</b>. Tab <b>Semua SPK</b> tersedia jika ingin melihat seluruh data tanpa filter.',
        pinnedColId: 'spk_no',
        pinnedTitle: 'Kolom No. SPK & Status',
        pinnedDesc: 'Kolom ini disematkan (pinned) di sisi kiri agar tetap terlihat saat tabel digeser ke kanan. Status pengerjaan juga tertera langsung di bawah nomor SPK.',
        sortColId: 'tgl_spk',
        dragColId: 'produk_fg_summary',
        searchElement: '#quickSearch',
        actionColId: 'action',
        actionTitle: 'Aksi Laporan',
        actionDesc: 'Gunakan tombol <b>Buat Laporan</b> untuk memulai input, <b>Lanjutkan Draft</b> untuk melanjutkan pengisian, atau <b>Lihat Laporan</b> untuk meninjau hasil produksi yang sudah selesai.'
      });
    });
  });
</script>