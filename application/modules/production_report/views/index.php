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

    <!-- ag-Grid Container -->
    <div id="spkGrid" class="ag-theme-alpine"></div>
  </div>
</div>

<!-- ag-Grid Community Script -->
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community@31.3.2/dist/ag-grid-community.min.js"></script>
<!-- Driver.js Script -->
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>

<script>
  $(document).ready(function() {
    let gridApi = null;

    const columnDefs = [{
        headerName: "No. SPK",
        field: "spk_no",
        width: 180,
        pinned: 'left',
        cellRenderer: function(params) {
          return '<strong>' + (params.value || '-') + '</strong>';
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
        headerName: "Status Laporan",
        field: "report_status",
        width: 160,
        cellRenderer: function(params) {
          const st = params.data.report_status;
          if (st === 'Done') {
            return '<span class="badge-done"><i class="fa fa-check-circle me-1"></i>Selesai</span>';
          } else if (st === 'Draft') {
            return '<span class="badge-draft"><i class="fa fa-pencil me-1"></i>Draft Tersimpan</span>';
          } else {
            return '<span class="badge-menunggu"><i class="fa fa-clock-o me-1"></i>Belum Dikerjakan</span>';
          }
        }
      },
      {
        headerName: "Aksi",
        field: "action",
        width: 170,
        pinned: 'right',
        cellRenderer: function(params) {
          const spkNo = params.data.spk_no;
          const reportStatus = params.data.report_status;
          const reportId = params.data.report_id;

          if (reportStatus === 'Done' && reportId) {
            return '<a href="<?= site_url('production_report/view/') ?>' + reportId + '" class="btn btn-sm btn-outline-secondary py-1 px-2">' +
              '<i class="fa fa-eye me-1"></i>Lihat Laporan</a>';
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
      rowHeight: 48,
      headerHeight: 42,
      defaultColDef: {
        sortable: true,
        filter: true,
        resizable: true
      }
    };

    const gridDiv = document.querySelector('#spkGrid');
    gridApi = agGrid.createGrid(gridDiv, gridOptions);

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
            gridApi.setGridOption('rowData', res.data || []);
            if (res.stats) {
              $('#statMenunggu').text(res.stats.waiting_count || 0);
              $('#statDone').text(res.stats.done_count || 0);
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

    $('#btnRefreshGrid').on('click', function() {
      loadData();
    });

    $('#quickSearch').on('input keyup', function() {
      if (gridApi) {
        gridApi.setGridOption('quickFilterText', $(this).val());
      }
    });
    // ============================================================
    // DRIVER.JS: PANDUAN FITUR INTERAKTIF TABEL (ag-Grid Tour)
    // ============================================================
    $('#btnStartTour').on('click', function(e) {
      e.preventDefault();
      if (typeof window.driver === 'undefined') {
        alert('Library panduan sedang dimuat, silakan coba sesaat lagi.');
        return;
      }

      const driverObj = window.driver.js.driver({
        showProgress: true,
        animate: true,
        allowClose: true,
        overlayColor: 'rgba(15, 23, 42, 0.65)',
        nextBtnText: 'Lanjut &rarr;',
        prevBtnText: '&larr; Kembali',
        doneBtnText: 'Selesai &times;',
        steps: [{
            element: '#spkGrid',
            popover: {
              title: '✨ Selamat Datang di Tabel Modern!',
              description: 'Tabel ini dilengkapi fitur interaktif canggih: Anda dapat menggeser urutan kolom, menyortir data, mengatur ukuran lebar kolom, hingga menyaring data dengan instan.',
              side: 'top',
              align: 'start'
            }
          },
          {
            element: '#spkGrid .ag-header-cell[col-id="spk_no"]',
            popover: {
              title: '📌 Pinned Column (Kolom Tetap)',
              description: 'Kolom <b>No. SPK</b> disematkan (pinned) di sisi kiri agar tetap terlihat meskipun Anda menggulir tabel ke kanan.',
              side: 'bottom',
              align: 'start'
            }
          },
          {
            element: '#spkGrid .ag-header-cell[col-id="tgl_spk"]',
            popover: {
              title: '↕️ Sorting Data (Urutkan Kolom)',
              description: 'Klik judul kolom (header) untuk mengurutkan data dari terkecil ke terbesar (A-Z) atau sebaliknya (Z-A). Klik sekali lagi untuk kembali ke urutan semula.',
              side: 'bottom',
              align: 'start'
            }
          },
          {
            element: '#spkGrid .ag-header-cell[col-id="produk_fg_summary"]',
            popover: {
              title: '↔️ Geser Posisi & Atur Lebar Kolom',
              description: '<b>• Geser Posisi:</b> Klik dan tahan (drag & drop) judul kolom untuk memindahkan posisinya.<br><b>• Ubah Lebar:</b> Arahkan kursor ke garis tepi kanan judul kolom hingga kursor berubah bentuk, lalu geser ke kiri/kanan.',
              side: 'bottom',
              align: 'start'
            }
          },
          {
            element: '#quickSearch',
            popover: {
              title: '🔍 Pencarian Cepat (Instant Filter)',
              description: 'Ketik kata kunci apa saja (Nomor SPK, nama produk, status, dsb) di sini untuk menyaring baris data secara langsung di seluruh kolom tabel.',
              side: 'bottom',
              align: 'end'
            }
          },
          {
            element: '#spkGrid .ag-header-cell[col-id="action"]',
            popover: {
              title: '⚡ Kolom Aksi Cepat',
              description: 'Kolom aksi dipin di sebelah kanan. Klik tombol <b>Buat Laporan</b> untuk mulai menginput laporan produksi dari SPK terkait, atau <b>Lanjutkan Draft</b> / <b>Lihat Laporan</b>.',
              side: 'left',
              align: 'center'
            }
          },
          {
            element: '#spkGrid .ag-paging-panel',
            popover: {
              title: '📄 Pagination & Jumlah Baris',
              description: 'Di bagian bawah tabel, Anda dapat berpindah halaman serta memilih berapa banyak baris yang ingin ditampilkan per halaman (10, 15, 25, 50).',
              side: 'top',
              align: 'center'
            }
          }
        ]
      });

      driverObj.drive();
    });
  });
</script>