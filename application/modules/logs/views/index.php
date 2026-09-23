<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Bootstrap 5 CSS & Flatpickr & Select2 & ag-Grid Community -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.3.2/styles/ag-grid.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.3.2/styles/ag-theme-alpine.min.css">

<style>
    .log-container {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .filter-card {
        border: none;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        margin-bottom: 20px;
    }

    .filter-label {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 5px;
        display: block;
    }

    .grid-card {
        border: none;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        padding: 20px;
    }

    #myGrid {
        width: 100%;
        height: 620px;
        border-radius: 8px;
        overflow: hidden;
    }

    .badge-status-success {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.76rem;
    }

    .badge-status-failed {
        background-color: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.76rem;
    }

    .badge-module {
        background-color: #e3f2fd;
        color: #1565c0;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.78rem;
    }

    .badge-action {
        background-color: #f3e5f5;
        color: #6a1b9a;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.78rem;
    }

    .badge-tech {
        background-color: #f5f5f5;
        color: #616161;
        font-size: 0.72rem;
        padding: 2px 6px;
        border-radius: 4px;
        margin-right: 4px;
    }

    /* Skeleton Loader */
    .skeleton-box {
        display: inline-block;
        height: 14px;
        width: 100%;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.4s infinite;
        border-radius: 4px;
    }

    @keyframes skeleton-loading {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    /* Code Viewer in Modal */
    .code-viewer {
        background: #1e1e2f;
        color: #a9b7c6;
        padding: 14px;
        border-radius: 8px;
        font-family: 'Consolas', 'Monaco', 'Courier New', Courier, monospace;
        font-size: 0.85rem;
        max-height: 280px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-break: break-all;
    }

    /* Select2 Tweaks */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 31px;
        padding: 2px 6px;
        font-size: 0.875rem;
    }
</style>

<div class="log-container p-3">

    <!-- Filter Card: Compact Dates + Module + Status + Terapkan Filter & Reset -->
    <div class="card filter-card">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="filter-label"><i class="fa fa-calendar me-1"></i>Dari</label>
                    <input type="text" id="filter_start_date" class="form-control form-control-sm flatpickr-input" placeholder="Mulai" value="<?= $start_date; ?>">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="filter-label"><i class="fa fa-calendar me-1"></i>Sampai</label>
                    <input type="text" id="filter_end_date" class="form-control form-control-sm flatpickr-input" placeholder="Sampai" value="<?= $end_date; ?>">
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-label"><i class="fa fa-cubes me-1"></i>Modul</label>
                    <select id="filter_module" class="form-select form-select-sm select2-module">
                        <option value="">-- Semua Modul --</option>
                        <?php foreach ($modules as $m): ?>
                            <option value="<?= htmlspecialchars($m); ?>"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $m))); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-6">
                    <label class="filter-label"><i class="fa fa-flag me-1"></i>Status</label>
                    <select id="filter_status" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        <option value="SUCCESS">SUCCESS</option>
                        <option value="FAILED">FAILED</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-12 d-flex gap-2">
                    <button type="button" id="btn_apply_filter" class="btn btn-sm btn-primary px-3 shadow-sm flex-fill">
                        <i class="fa fa-filter me-1"></i>Terapkan Filter
                    </button>
                    <button type="button" id="btn_reset_filter" class="btn btn-sm btn-outline-secondary px-3" title="Reset Filter">
                        <i class="fa fa-refresh me-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Card -->
    <div class="card grid-card">
        <!-- Action Toolbar Above Table: Search Bar & Export CSV -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <!-- Left: Search Bar (Live Debounced) -->
            <div class="d-flex align-items-center" style="max-width: 380px; width: 100%;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 p-0"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" id="filter_search" class="form-control border-start-0" placeholder="search pesan, user, IP, query...">
                    <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" id="btn_clear_search" title="Bersihkan Pencarian" style="display: none;">
                        <i class="fa fa-times text-muted"></i>
                    </button>
                </div>
            </div>

            <!-- Right: Export CSV Button -->
            <div>
                <button type="button" id="btn_export_csv" class="btn btn-sm btn-success px-3 shadow-sm">
                    <i class="fa fa-file-excel-o me-1"></i>Export CSV (Lengkap)
                </button>
            </div>
        </div>

        <!-- ag-Grid Container -->
        <div id="myGrid" class="ag-theme-alpine"></div>
    </div>
</div>

<!-- Modal Detail Log -->
<div class="modal fade" id="modalLogDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3 border-0">
                <h5 class="modal-title fw-bold text-dark" id="modalDetailTitle">
                    <i class="fa fa-info-circle text-primary me-2"></i>Detail Activity Log
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalDetailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Memuat rincian log...</p>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery (Required for Select2 if not loaded), Bootstrap 5, Select2, Flatpickr & ag-Grid -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community@31.3.2/dist/ag-grid-community.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 with search for Module dropdown
        if (window.jQuery && jQuery.fn.select2) {
            $('.select2-module').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Cari & Pilih Modul --',
                allowClear: true,
                width: '100%'
            });
        }

        // 1. Initialize Flatpickr
        flatpickr("#filter_start_date", {
            dateFormat: "Y-m-d",
            defaultDate: "<?= $start_date; ?>",
            allowInput: true
        });
        flatpickr("#filter_end_date", {
            dateFormat: "Y-m-d",
            defaultDate: "<?= $end_date; ?>",
            allowInput: true
        });

        const defaultDate = "<?= $start_date; ?>";

        // 2. ag-Grid Column Definitions
        const columnDefs = [{
                headerName: "No",
                field: "no",
                width: 75,
                sortable: false,
                cellRenderer: function(params) {
                    if (!params.data) return '<span class="skeleton-box" style="width: 50%;"></span>';
                    return '<span class="text-muted fw-bold">' + (params.node.rowIndex + 1) + '</span>';
                }
            },
            {
                headerName: "Waktu",
                field: "created_at",
                width: 160,
                sortable: true,
                cellRenderer: function(params) {
                    if (!params.data) return '<span class="skeleton-box" style="width: 80%;"></span>';
                    return '<i class="fa fa-clock-o text-muted me-1"></i><small class="text-dark">' + params.value + '</small>';
                }
            },
            {
                headerName: "User",
                field: "user_name",
                width: 140,
                sortable: true,
                cellRenderer: function(params) {
                    if (!params.data) return '<span class="skeleton-box" style="width: 70%;"></span>';
                    return '<i class="fa fa-user text-secondary me-1"></i><span class="fw-semibold">' + (params.value || 'System') + '</span>';
                }
            },
            {
                headerName: "Modul",
                field: "module",
                width: 160,
                sortable: true,
                cellRenderer: function(params) {
                    if (!params.data) return '<span class="skeleton-box" style="width: 60%;"></span>';
                    return '<span class="badge-module">' + (params.value ? params.value.toUpperCase() : '-') + '</span>';
                }
            },
            {
                headerName: "Aksi",
                field: "action",
                width: 140,
                sortable: true,
                cellRenderer: function(params) {
                    if (!params.data) return '<span class="skeleton-box" style="width: 60%;"></span>';
                    return '<span class="badge-action">' + (params.value ? params.value.toUpperCase() : '-') + '</span>';
                }
            },
            {
                headerName: "Status",
                field: "status",
                width: 110,
                sortable: true,
                cellRenderer: function(params) {
                    if (!params.data) return '<span class="skeleton-box" style="width: 70%;"></span>';
                    if (params.value === 'SUCCESS') {
                        return '<span class="badge-status-success"><i class="fa fa-check me-1"></i>SUCCESS</span>';
                    } else {
                        return '<span class="badge-status-failed"><i class="fa fa-times me-1"></i>FAILED</span>';
                    }
                }
            },
            {
                headerName: "Pesan / Ringkasan",
                field: "message",
                minWidth: 260,
                flex: 1,
                sortable: false,
                cellRenderer: function(params) {
                    if (!params.data) return '<span class="skeleton-box" style="width: 90%;"></span>';
                    return '<span title="' + (params.value || '') + '">' + (params.value || '-') + '</span>';
                }
            },
            {
                headerName: "Klien & IP",
                field: "ip_address",
                width: 170,
                sortable: false,
                cellRenderer: function(params) {
                    if (!params.data) return '<span class="skeleton-box" style="width: 80%;"></span>';
                    return '<span class="badge-tech">' + (params.data.ip_address || '-') + '</span>' +
                        (params.data.platform ? '<span class="badge-tech">' + params.data.platform + '</span>' : '');
                }
            },
            {
                headerName: "Aksi",
                field: "actions",
                width: 110,
                sortable: false,
                pinned: "right",
                cellClass: "d-flex justify-content-center align-items-center",
                headerClass: "text-center",
                cellRenderer: function(params) {
                    if (!params.data) return '<span class="skeleton-box" style="width: 60px;"></span>';
                    return '<button class="btn btn-xs btn-outline-primary py-0 px-2 btn-view-detail" data-id="' + params.data.id + '"><i class="fa fa-eye me-1"></i>Detail</button>';
                }
            }
        ];

        // Template keterangan saat data kosong
        const emptyStateHtml = `
        <div class="py-5 text-center">
            <div class="mb-3">
                <i class="fa fa-folder-open-o fa-3x text-muted opacity-50"></i>
            </div>
            <h6 class="fw-bold text-secondary mb-1">Tidak Ada Data Log Ditemukan</h6>
            <p class="text-muted small mb-0">Tidak ada riwayat aktivitas log yang sesuai dengan rentang tanggal atau kriteria filter yang dipilih.</p>
        </div>
    `;

        // 3. Helper to create a fresh ag-Grid Infinite Datasource
        function createDataSource() {
            return {
                rowCount: undefined,
                getRows: function(params) {
                    const sortModel = params.sortModel && params.sortModel.length > 0 ? params.sortModel[0] : null;

                    const formData = new FormData();
                    formData.append('startRow', params.startRow);
                    formData.append('endRow', params.endRow);
                    formData.append('start_date', document.getElementById('filter_start_date').value);
                    formData.append('end_date', document.getElementById('filter_end_date').value);
                    formData.append('module', document.getElementById('filter_module').value);
                    formData.append('status', document.getElementById('filter_status').value);
                    formData.append('search', document.getElementById('filter_search').value.trim());

                    if (sortModel) {
                        formData.append('sortCol', sortModel.colId);
                        formData.append('sortDir', sortModel.sort);
                    }

                    fetch('<?= site_url("logs/get_data"); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            params.successCallback(data.rows, data.totalCount);
                            // Tampilkan overlay jika data kosong
                            if (data.totalCount === 0 || (!data.rows || data.rows.length === 0)) {
                                gridApi.showNoRowsOverlay();
                            } else {
                                gridApi.hideOverlay();
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching logs:', error);
                            params.failCallback();
                        });
                }
            };
        }

        // 4. ag-Grid Options (Infinite Scroll Model)
        const gridOptions = {
            columnDefs: columnDefs,
            rowModelType: 'infinite',
            cacheBlockSize: 50,
            maxBlocksInCache: 10,
            infiniteInitialRowCount: 50,
            datasource: createDataSource(),
            rowHeight: 46,
            headerHeight: 42,
            defaultColDef: {
                resizable: true,
                sortable: true
            },
            overlayNoRowsTemplate: emptyStateHtml
        };

        // 5. Initialize ag-Grid
        const gridDiv = document.querySelector('#myGrid');
        const gridApi = agGrid.createGrid(gridDiv, gridOptions);

        // Refresh function using createDataSource()
        function refreshGrid() {
            gridApi.setGridOption('datasource', createDataSource());
        }

        // Debounce Helper Function
        function debounce(func, delay) {
            let timer;
            return function(...args) {
                clearTimeout(timer);
                timer = setTimeout(() => func.apply(this, args), delay);
            };
        }

        // 6. Filter & Live Search Events
        document.getElementById('btn_apply_filter').addEventListener('click', function() {
            refreshGrid();
        });

        const searchInput = document.getElementById('filter_search');
        const clearSearchBtn = document.getElementById('btn_clear_search');

        // Live Search with 450ms debounce
        const handleLiveSearch = debounce(function() {
            refreshGrid();
        }, 450);

        searchInput.addEventListener('input', function() {
            if (clearSearchBtn) {
                clearSearchBtn.style.display = this.value.trim() ? 'block' : 'none';
            }
            handleLiveSearch();
        });

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                this.style.display = 'none';
                refreshGrid();
            });
        }

        // 7. Reset Filter Event
        document.getElementById('btn_reset_filter').addEventListener('click', function() {
            document.getElementById('filter_start_date').value = defaultDate;
            document.getElementById('filter_end_date').value = defaultDate;
            document.getElementById('filter_status').value = "";
            document.getElementById('filter_search').value = "";
            const clearBtn = document.getElementById('btn_clear_search');
            if (clearBtn) clearBtn.style.display = 'none';

            // Reset Select2 module
            if (window.jQuery && jQuery.fn.select2) {
                $('.select2-module').val('').trigger('change');
            } else {
                document.getElementById('filter_module').value = "";
            }

            // Re-sync flatpickr instances
            if (document.getElementById('filter_start_date')._flatpickr) {
                document.getElementById('filter_start_date')._flatpickr.setDate(defaultDate);
            }
            if (document.getElementById('filter_end_date')._flatpickr) {
                document.getElementById('filter_end_date')._flatpickr.setDate(defaultDate);
            }

            refreshGrid();
        });

        // 8. Full Export to CSV Event
        document.getElementById('btn_export_csv').addEventListener('click', function() {
            const params = new URLSearchParams({
                start_date: document.getElementById('filter_start_date').value,
                end_date: document.getElementById('filter_end_date').value,
                module: document.getElementById('filter_module').value,
                status: document.getElementById('filter_status').value,
                search: document.getElementById('filter_search').value.trim()
            });
            window.location.href = '<?= site_url("logs/export_csv"); ?>?' + params.toString();
        });

        // 9. View Detail Modal
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-view-detail');
            if (btn) {
                const id = btn.getAttribute('data-id');
                const modalEl = document.getElementById('modalLogDetail');
                const modal = new bootstrap.Modal(modalEl);
                const modalBody = document.getElementById('modalDetailBody');

                modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Memuat rincian log #${id}...</p>
                </div>
            `;
                modal.show();

                fetch('<?= site_url("logs/detail"); ?>/' + id)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 1) {
                            const d = res.data;
                            const statusBadge = (d.status === 'SUCCESS') ?
                                '<span class="badge-status-success">SUCCESS</span>' :
                                '<span class="badge-status-failed">FAILED</span>';

                            let html = `
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="text-muted small">ID & Waktu:</div>
                                        <div class="fw-bold">#${d.id} - ${d.created_at}</div>
                                        <div class="text-muted small mt-2">Pengguna:</div>
                                        <div class="fw-bold">${d.user_name}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="text-muted small">Modul / Aksi / Status:</div>
                                        <div class="mt-1">
                                            <span class="badge-module me-1">${d.module}</span>
                                            <span class="badge-action me-1">${d.action}</span>
                                            ${statusBadge}
                                        </div>
                                        <div class="text-muted small mt-2">IP & Platform:</div>
                                        <div><code>${d.ip_address}</code> - ${d.platform || '-'}</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="fw-bold small text-muted">Pesan Log:</label>
                                    <div class="alert alert-secondary py-2 px-3 mb-2">${d.message || '-'}</div>
                                </div>
                        `;

                            if (d.query_sql) {
                                html += `
                                <div class="col-12">
                                    <label class="fw-bold small text-muted"><i class="fa fa-database me-1"></i>Executed Query (SQL):</label>
                                    <pre class="code-viewer"><code>${escapeHtml(d.query_sql)}</code></pre>
                                </div>
                            `;
                            }

                            if (d.data_json) {
                                html += `
                                <div class="col-12">
                                    <label class="fw-bold small text-muted"><i class="fa fa-code me-1"></i>Payload Data (JSON):</label>
                                    <pre class="code-viewer"><code>${escapeHtml(d.data_json)}</code></pre>
                                </div>
                            `;
                            }

                            html += '</div>';
                            modalBody.innerHTML = html;
                        } else {
                            modalBody.innerHTML = `<div class="alert alert-danger">${res.msg || 'Gagal memuat detail.'}</div>`;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        modalBody.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan jaringan saat memuat detail log.</div>`;
                    });
            }
        });

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }
    });
</script>