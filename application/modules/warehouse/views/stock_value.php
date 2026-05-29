<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    #modal-history .modal-body table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        overflow: visible !important;
    }

    #modal-history .modal-body thead {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    #modal-history .modal-body thead th {
        background-color: #f8f9fa !important;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.2);
        border-bottom: 2px solid #dee2e6;
    }

    #modal-detail-coil-trx {
        z-index: 1060;
    }

    .modal-backdrop:nth-of-type(2) {
        z-index: 1055;
    }

    /* Blur modal history ketika modal transaksi terbuka */
    #modal-history.blur-background .modal-content {
        filter: blur(3px);
        transition: filter 0.2s ease;
    }

    #modal-history .modal-content {
        transition: filter 0.2s ease;
    }
</style>
<div class="card">
    <div class="card-body">

        <!-- ── Filter material (global, hanya untuk tab live) ─────────── -->
        <div class="row mb-3 g-2 align-items-end" id="bar-filter-live">
            <div class="col-md-4">
                <input type="text" id="filter_material" class="form-control"
                    placeholder="Cari kode / nama material...">
            </div>
            <div class="col-md-8 text-end">
                <button class="btn btn-primary" id="btn-filter">
                    <i class="fa fa-search"></i> Filter
                </button>
                <button class="btn btn-secondary" id="btn-reset">
                    <i class="fa fa-refresh"></i> Reset
                </button>
                <button class="btn btn-success" id="btn-excel">
                    <i class="fa fa-file-excel-o"></i> Download Excel
                </button>
            </div>
        </div>

        <!-- ── Tab ───────────────────────────────────────────────────── -->
        <ul class="nav nav-tabs mb-3" id="tabStockValue" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-sv-pusat-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-sv-pusat"
                    type="button" role="tab">
                    <i class="fa fa-warehouse"></i> Gudang Pusat
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-sv-penjualan-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-sv-penjualan"
                    type="button" role="tab">
                    <i class="fa fa-store"></i> Gudang Penjualan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-sv-history-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-sv-history"
                    type="button" role="tab">
                    <i class="fa fa-history"></i> History Per Days
                </button>
            </li>
        </ul>

        <div class="tab-content" id="tabStockValueContent">

            <!-- TAB PUSAT -->
            <div class="tab-pane fade show active" id="tab-sv-pusat" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-sv-pusat" class="table table-bordered table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th width="4%">No</th>
                                <th>Kode Material</th>
                                <th>Nama Material</th>
                                <th class="text-center">Jml Coil</th>
                                <th class="text-end">Qty Stock (Kg)</th>
                                <th class="text-end">Harga Beli (Avg)</th>
                                <th class="text-end">Total Nilai</th>
                                <th width="8%" class="text-center no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end"><strong>GRAND TOTAL PUSAT</strong></th>
                                <th class="text-end" id="grand-total-pusat">—</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB PENJUALAN -->
            <div class="tab-pane fade" id="tab-sv-penjualan" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-sv-penjualan" class="table table-bordered table-striped table-hover">
                        <thead class="table-success">
                            <tr>
                                <th width="4%">No</th>
                                <th>Kode Material</th>
                                <th>Nama Material</th>
                                <th class="text-center">Jml Coil</th>
                                <th class="text-end">Qty Stock (Kg)</th>
                                <th class="text-end">Harga Beli (Avg)</th>
                                <th class="text-end">Total Nilai</th>
                                <th width="8%" class="text-center no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end"><strong>GRAND TOTAL PENJUALAN</strong></th>
                                <th class="text-end" id="grand-total-penjualan">—</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB HISTORY PER DAYS -->
            <div class="tab-pane fade" id="tab-sv-history" role="tabpanel">

                <!-- Filter date range -->
                <div class="row mb-3 g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <i class="fa fa-search"></i> Cari Material
                        </label>
                        <input type="text" id="sv_hist_material" class="form-control form-control-sm"
                            placeholder="Kode / nama material...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <i class="fa fa-calendar"></i> Tanggal Dari
                        </label>
                        <input type="text" id="sv_date_from" class="form-control form-control-sm"
                            placeholder="dd/mm/yyyy" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <i class="fa fa-calendar"></i> Tanggal Sampai
                        </label>
                        <input type="text" id="sv_date_to" class="form-control form-control-sm"
                            placeholder="dd/mm/yyyy" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <i class="fa fa-filter"></i> Gudang
                        </label>
                        <select id="sv_hist_gudang" class="form-select form-select-sm">
                            <option value="">-- Semua --</option>
                            <option value="PUS">Gudang Pusat</option>
                            <option value="PEN">Gudang Penjualan</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-primary btn-sm" id="btn-filter-svh">
                            <i class="fa fa-search"></i> Tampilkan
                        </button>
                        <button class="btn btn-secondary btn-sm" id="btn-reset-svh">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>
                </div>

                <!-- Grand total history -->
                <div class="row mb-2">
                    <div class="col text-end">
                        <span style="font-size:13px;">Grand Total:</span>
                        <strong id="grand-total-history" class="ms-2" style="font-size:14px;">—</strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table-sv-history" class="table table-bordered table-striped table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th width="4%">No</th>
                                <th>Kode Material</th>
                                <th>Nama Material</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-end">Qty Stock (Kg)</th>
                                <th class="text-end">Harga Beli (Avg)</th>
                                <th class="text-end">Total Nilai</th>
                                <th width="8%" class="text-center no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fa fa-info-circle"></i>
                                    Pilih rentang tanggal lalu klik <strong>Tampilkan</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal History -->
<div class="modal fade" id="modal-history" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-history"></i>
                    History Stok — <span id="modal-title-material"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 80vh; overflow-y: auto;">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>No. Transaksi</th>
                            <th class="text-center">Jml Coil</th> <!-- BARU -->
                            <th class="text-end">Qty Awal (Kg)</th>
                            <th class="text-end">Qty Transaksi (Kg)</th>
                            <th class="text-end">Qty Akhir (Kg)</th>
                            <th class="text-end">Costbook (Avg)</th>
                            <th class="text-end">Total Harga</th>
                            <th class="text-end">Saldo Awal</th>
                            <th class="text-end">Saldo Akhir</th>
                            <th class="text-end">Harga Lama</th>
                            <th class="text-end">Harga Baru (Avg)</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-history">
                        <tr>
                            <td colspan="13" class="text-center">Pilih material untuk melihat history</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Coil -->
<div class="modal fade" id="modal-detail-coil" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fa fa-list"></i>
                    Detail Coil — <span id="coil-title-material"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>No. Coil</th>
                                <th>Kode Internal</th>
                                <th class="text-end">Net Weight (Kg)</th>
                                <th class="text-end">Gross Weight (Kg)</th>
                                <th class="text-end">Panjang (m)</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-detail-coil">
                            <tr>
                                <td colspan="6" class="text-center">—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Coil Per Transaksi (drill-down dari modal history) -->
<div class="modal fade" id="modal-detail-coil-trx" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fa fa-list"></i>
                    Detail Coil Transaksi — <span id="coil-trx-title"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>No. Coil</th>
                                <th>Kode Internal</th>
                                <th class="text-end">Net Weight (Kg)</th>
                                <th class="text-end">Gross Weight (Kg)</th>
                                <th class="text-end">Panjang (m)</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-detail-coil-trx">
                            <tr>
                                <td colspan="7" class="text-center">—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
    $(document).ready(function() {

        // ── Kolom live ────────────────────────────────────────────────────────
        var colDefLive = [{
                data: 0,
                width: '4%'
            },
            {
                data: 1
            }, {
                data: 2
            },
            {
                data: 3,
                className: 'text-center'
            },
            {
                data: 4,
                className: 'text-end'
            },
            {
                data: 5,
                className: 'text-end'
            },
            {
                data: 6,
                className: 'text-end'
            },
            {
                data: 7,
                className: 'text-center',
                orderable: false
            },
        ];

        // ── Kolom history per days ────────────────────────────────────────────
        var colDefHistory = [{
                data: 0,
                width: '4%'
            },
            {
                data: 1
            }, {
                data: 2
            },
            {
                data: 3,
                className: 'text-center'
            },
            {
                data: 4,
                className: 'text-end'
            },
            {
                data: 5,
                className: 'text-end'
            },
            {
                data: 6,
                className: 'text-end'
            },
            {
                data: 7,
                className: 'text-center',
                orderable: false
            },
        ];

        // ── Helper dd/mm/yyyy → yyyy-mm-dd ────────────────────────────────────
        function getYmd(dmy) {
            if (!dmy) return '';
            var p = dmy.split('/');
            return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : '';
        }

        // ── DataTables live ───────────────────────────────────────────────────
        function dtOptions(endpoint, grandTotalId, kdGudang) {
            return {
                processing: true,
                serverSide: true,
                destroy: true,
                autoWidth: false,
                responsive: true,
                pagingType: 'simple_numbers',
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }],
                ajax: {
                    url: siteurl + 'warehouse/' + endpoint,
                    type: 'POST',
                    data: function(d) {
                        d.filter_material = $('#filter_material').val();
                    },
                    cache: false,
                },
                columns: colDefLive,
                order: [
                    [2, 'asc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Memuat data...',
                    zeroRecords: 'Tidak ada data stock.',
                    emptyTable: 'Tidak ada data.',
                },
                drawCallback: function() {
                    $.post(siteurl + 'warehouse/get_grand_total_stock_value', {
                        kd_gudang: kdGudang,
                        filter_material: $('#filter_material').val(),
                    }, function(res) {
                        $('#' + grandTotalId).html('<strong>' + res.total + '</strong>');
                    }, 'json');
                }
            };
        }

        var dtPusat = $('#table-sv-pusat').DataTable(
            dtOptions('data_side_stock_value_pusat', 'grand-total-pusat', 'PUS')
        );

        var dtPenjualan = null;
        document.getElementById('tab-sv-penjualan-tab')
            .addEventListener('shown.bs.tab', function() {
                if (!dtPenjualan) {
                    dtPenjualan = $('#table-sv-penjualan').DataTable(
                        dtOptions('data_side_stock_value_penjualan', 'grand-total-penjualan', 'PEN')
                    );
                }
            });

        $('#btn-filter').on('click', function() {
            dtPusat.ajax.reload();
            if (dtPenjualan) dtPenjualan.ajax.reload();
        });

        $('#btn-reset').on('click', function() {
            $('#filter_material').val('');
            dtPusat.ajax.reload();
            if (dtPenjualan) dtPenjualan.ajax.reload();
        });

        $('#btn-excel').on('click', function() {
            var kd = document.getElementById('tab-sv-pusat').classList.contains('active') ? 'PUS' : 'PEN';
            window.location.href = siteurl + 'warehouse/export_excel_stock_value?kd_gudang=' + kd;
        });

        // ── Flatpickr untuk tab History Per Days ─────────────────────────────
        var fpFrom = flatpickr('#sv_date_from', {
            locale: 'id',
            dateFormat: 'd/m/Y',
            onChange: function(sel) {
                fpTo.set('minDate', sel[0] || null);
            }
        });
        var fpTo = flatpickr('#sv_date_to', {
            locale: 'id',
            dateFormat: 'd/m/Y',
            onChange: function(sel) {
                fpFrom.set('maxDate', sel[0] || null);
            }
        });

        // ── DataTable History Per Days (lazy) ─────────────────────────────────
        var dtHistory = null;

        function buildHistoryDt() {
            if (dtHistory) dtHistory.destroy();

            var kdGudang = $('#sv_hist_gudang').val();

            // Pilih endpoint berdasarkan gudang
            var endpoint = 'data_side_stock_value_perday';
            if (kdGudang === 'PUS') endpoint = 'data_side_stock_value_perday_pusat';
            if (kdGudang === 'PEN') endpoint = 'data_side_stock_value_perday_penjualan';

            dtHistory = $('#table-sv-history').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                autoWidth: false,
                responsive: true,
                pagingType: 'simple_numbers',
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }],
                ajax: {
                    url: siteurl + 'warehouse/' + endpoint,
                    type: 'POST',
                    data: function(d) {
                        d.filter_material = $('#sv_hist_material').val();
                        d.date_from = getYmd($('#sv_date_from').val());
                        d.date_to = getYmd($('#sv_date_to').val());
                        d.kd_gudang = kdGudang;
                    },
                    cache: false,
                },
                columns: colDefHistory,
                order: [
                    [3, 'desc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Memuat data...',
                    zeroRecords: 'Tidak ada data untuk rentang tanggal ini.',
                    emptyTable: 'Tidak ada data.',
                },
                drawCallback: function() {
                    $.post(siteurl + 'warehouse/get_grand_total_stock_value_perday', {
                        kd_gudang: kdGudang,
                        filter_material: $('#sv_hist_material').val(),
                        date_from: getYmd($('#sv_date_from').val()),
                        date_to: getYmd($('#sv_date_to').val()),
                    }, function(res) {
                        $('#grand-total-history').text(res.total);
                    }, 'json');
                }
            });
        }

        $('#btn-filter-svh').on('click', function() {
            buildHistoryDt();
        });

        $('#btn-reset-svh').on('click', function() {
            fpFrom.clear();
            fpTo.clear();
            fpFrom.set('maxDate', null);
            fpTo.set('minDate', null);
            $('#sv_hist_material').val('');
            $('#sv_hist_gudang').val('');
            $('#grand-total-history').text('—');
            if (dtHistory) {
                dtHistory.destroy();
                dtHistory = null;
                $('#table-sv-history tbody').html(
                    '<tr><td colspan="8" class="text-center text-muted py-4">' +
                    '<i class="fa fa-info-circle"></i> ' +
                    'Pilih rentang tanggal lalu klik <strong>Tampilkan</strong>' +
                    '</td></tr>'
                );
            }
        });

    });

    // ── Modal History ──────────────────────────────────────────────────────────
    // function showHistory(id_material, nm_material, id_gudang) {
    //     $('#modal-title-material').text(nm_material);
    //     $('#tbody-history').html('<tr><td colspan="13" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
    //     new bootstrap.Modal(document.getElementById('modal-history')).show();
    //     $.post(siteurl + 'warehouse/get_history_material', {
    //         id_material,
    //         id_gudang
    //     }, function(data) {
    //         if (!data.length) {
    //             $('#tbody-history').html('<tr><td colspan="13" class="text-center">Tidak ada history</td></tr>');
    //             return;
    //         }
    //         var html = '';
    //         $.each(data, function(i, r) {
    //             html += '<tr>' +
    //                 '<td class="text-center">' + (i + 1) + '</td>' +
    //                 '<td>' + (r.update_date || '-') + '</td><td>' + (r.no_ipp || '-') + '</td>' +
    //                 '<td>' + (r.no_coil || '-') + '</td><td>' + (r.kd_gudang || '-') + '</td>' +
    //                 '<td>' + (r.ket || '-') + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.jumlah_mat) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.harga_beli) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.total_harga) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.saldo_awal) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.saldo_akhir) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.harga_lama) + '</td>' +
    //                 '<td class="text-end">' + fmtNum(r.harga_baru) + '</td>' +
    //                 '</tr>';
    //         });
    //         $('#tbody-history').html(html);
    //     }, 'json');
    // }

    function showHistory(id_material, nm_material, id_gudang) {
        $('#modal-title-material').text(nm_material);
        $('#tbody-history').html('<tr><td colspan="14" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
        new bootstrap.Modal(document.getElementById('modal-history')).show();

        $.post(siteurl + 'warehouse/get_history_summary', {
            id_material,
            id_gudang
        }, function(data) {
            if (!data.length) {
                $('#tbody-history').html('<tr><td colspan="14" class="text-center">Tidak ada history</td></tr>');
                return;
            }
            var html = '';
            $.each(data, function(i, r) {
                html += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td>' + (r.tanggal || '-') + '</td>' +
                    '<td>' + (r.no_ipp || '-') + '</td>' +
                    // Jumlah coil — klik → drill-down
                    '<td class="text-center">' +
                    '<a href="javascript:void(0)" class="badge bg-primary" ' +
                    'data-no-ipp="' + r.no_ipp + '" ' +
                    'data-id-material="' + r.id_material + '" ' +
                    'data-id-gudang="' + r.id_gudang + '" ' +
                    'data-nm-material="' + r.nm_material + '" ' +
                    'onclick="showSummaryDetailCoil(this)">' +
                    r.jumlah_coil + ' coil</a>' +
                    '</td>' +
                    '<td class="text-end">' + fmtDec(r.qty_awal) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.qty_transaksi) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.qty_akhir) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.costbook) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.total_harga) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.saldo_awal) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.saldo_akhir) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.harga_lama) + '</td>' +
                    '<td class="text-end">' + fmtNum(r.costbook) + '</td>' + // harga baru = costbook
                    '</tr>';
            });
            $('#tbody-history').html(html);
        }, 'json');
    }

    function showSummaryDetailCoil(el) {
        var no_ipp = el.dataset.noIpp;
        var id_material = el.dataset.idMaterial;
        var id_gudang = el.dataset.idGudang;
        var nm_material = el.dataset.nmMaterial;

        $('#coil-trx-title').text(nm_material + ' — ' + no_ipp);
        $('#tbody-detail-coil-trx').html(
            '<tr><td colspan="7" class="text-center">' +
            '<i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>'
        );

        // Buka modal baru DI ATAS modal history — tidak perlu tutup modal history
        new bootstrap.Modal(document.getElementById('modal-detail-coil-trx')).show();

        $.post(siteurl + 'warehouse/get_summary_detail_coil', {
            no_ipp: no_ipp,
            id_material: id_material,
            id_gudang: id_gudang
        }, function(data) {
            if (!data.length) {
                $('#tbody-detail-coil-trx').html(
                    '<tr><td colspan="7" class="text-center">Tidak ada data coil</td></tr>'
                );
                return;
            }
            var html = '';
            $.each(data, function(i, r) {
                html += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td>' + (r.no_coil || '-') + '</td>' +
                    '<td>' + (r.kode_internal || '-') + '</td>' +
                    '<td class="text-end">' + fmtDec(r.net_weight) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.gross_weight) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.length) + '</td>' +
                    '</tr>';
            });
            $('#tbody-detail-coil-trx').html(html);
        }, 'json');
    }

    function showDetailCoil(id_material, nm_material, id_gudang) {
        $('#coil-title-material').text(nm_material);
        $('#tbody-detail-coil').html('<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
        new bootstrap.Modal(document.getElementById('modal-detail-coil')).show();
        $.post(siteurl + 'warehouse/get_detail_coil', {
            id_material,
            id_gudang
        }, function(data) {
            if (!data.length) {
                $('#tbody-detail-coil').html('<tr><td colspan="6" class="text-center">Tidak ada data coil</td></tr>');
                return;
            }
            var html = '';
            $.each(data, function(i, r) {
                html += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td>' + (r.no_coil || '-') + '</td><td>' + (r.kode_internal || '-') + '</td>' +
                    '<td class="text-end">' + fmtDec(r.net_weight) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.gross_weight) + '</td>' +
                    '<td class="text-end">' + fmtDec(r.length) + '</td>' +
                    '</tr>';
            });
            $('#tbody-detail-coil').html(html);
        }, 'json');
    }

    function fmtNum(val) {
        if (val == null || val === '') return '-';
        return Number(val).toLocaleString('id-ID');
    }

    function fmtDec(val) {
        if (val == null || val === '') return '-';
        return Number(val).toLocaleString('id-ID', {
            minimumFractionDigits: 3,
            maximumFractionDigits: 3
        });
    }

    // Blur modal history saat modal transaksi muncul
    document.getElementById('modal-detail-coil-trx').addEventListener('show.bs.modal', function() {
        document.getElementById('modal-history').classList.add('blur-background');
    });

    // Hapus blur saat modal transaksi ditutup
    document.getElementById('modal-detail-coil-trx').addEventListener('hidden.bs.modal', function() {
        document.getElementById('modal-history').classList.remove('blur-background');
    });

    document.getElementById('modal-history').addEventListener('shown.bs.modal', function() {
        // Paksa hilangkan overflow hidden pada tabel
        const table = this.querySelector('table');
        if (table) {
            table.style.setProperty('overflow', 'visible', 'important');
            table.style.setProperty('overflow-x', 'visible', 'important');
            table.style.setProperty('overflow-y', 'visible', 'important');
            table.style.setProperty('border-collapse', 'separate', 'important');
            table.style.setProperty('border-spacing', '0', 'important');
        }
    });
</script>