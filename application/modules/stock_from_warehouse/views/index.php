<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="card">
    <div class="card-body">

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-3" id="tabStockFromWarehouse" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-transit-tab"
                    data-bs-toggle="tab" href="#tab-transit" role="tab">
                    <i class="fa fa-truck-loading"></i> WRH. Production 2
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-wip-tab"
                    data-bs-toggle="tab" href="#tab-wip" role="tab">
                    <i class="fa fa-cogs"></i> WIP (Coil Remains)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-on-hold-tab"
                    data-bs-toggle="tab" href="#tab-on-hold" role="tab">
                    <i class="fa fa-pause-circle"></i> On Hold
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-history-tab"
                    data-bs-toggle="tab" href="#tab-history" role="tab">
                    <i class="fa fa-history"></i> History Per Days
                </a>
            </li>
        </ul>

        <div class="tab-content" id="tabStockFromWarehouseContent">

            <!-- TAB: Production Transit -->
            <div class="tab-pane fade show active" id="tab-transit" role="tabpanel">
                <div class="mb-3">
                    <button class="btn btn-success btn-sm" id="btn-excel-transit">
                        <i class="fa fa-file-excel-o"></i> Download Excel
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="table-stock-transit"
                        class="table table-bordered table-striped table-hover">
                        <thead class="bg-blue">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Name</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Internal Code</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="14%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: WIP -->
            <div class="tab-pane fade" id="tab-wip" role="tabpanel">
                <div class="mb-3">
                    <button class="btn btn-success btn-sm" id="btn-excel-wip">
                        <i class="fa fa-file-excel-o"></i> Download Excel
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="table-stock-wip"
                        class="table table-bordered table-striped table-hover">
                        <thead class="bg-green">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Name</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Internal Code</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: On Hold -->
            <div class="tab-pane fade" id="tab-on-hold" role="tabpanel">
                <div class="mb-3">
                    <button class="btn btn-success btn-sm" id="btn-excel-on-hold">
                        <i class="fa fa-file-excel-o"></i> Download Excel
                    </button>
                </div>
                <div class="table-responsive">
                    <table id="table-stock-on-hold"
                        class="table table-bordered table-striped table-hover">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th width="4%">No</th>
                                <th>Nama Supplier</th>
                                <th>Nama Material</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">No. PO</th>
                                <th class="text-end">Berat (Kg)</th>
                                <th>Alasan / Problem</th>
                                <th class="text-center">Status Material</th>
                                <th class="text-center" width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: History Per Days -->
            <div class="tab-pane fade" id="tab-history" role="tabpanel">

                <!-- Filter -->
                <div class="row mb-3 g-2 align-items-end">
                    <!-- Input Tanggal -->
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <i class="fa fa-calendar"></i> Tanggal
                        </label>
                        <input type="text" id="hist_date" class="form-control form-control-sm"
                            placeholder="dd/mm/yyyy" readonly>
                    </div>

                    <!-- Dropdown Sumber -->
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <i class="fa fa-filter"></i> Sumber
                        </label>
                        <select id="hist_source" class="form-select form-select-sm">
                            <option value="">-- Semua Sumber --</option>
                            <option value="PRT">Production 2 (PRT)</option>
                            <option value="WIP">WIP</option>
                            <option value="HLD">On Hold (HLD)</option>
                        </select>
                    </div>

                    <!-- Group Tombol Sejajar -->
                    <div class="col-md-6">
                        <div class="d-flex gap-1">
                            <button class="btn btn-primary btn-sm" id="btn-filter-hist">
                                <i class="fa fa-search"></i> Show
                            </button>
                            <button class="btn btn-secondary btn-sm" id="btn-reset-hist">
                                <i class="fa fa-refresh"></i> Reset
                            </button>
                            <button class="btn btn-success btn-sm d-none" id="btn-excel-hist">
                                <i class="fa fa-file-excel-o"></i> Download Excel
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table-history"
                        class="table table-bordered table-striped table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th width="4%">No</th>
                                <th>Material Name</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Internal Code</th>
                                <th class="text-center">Gudang Asal</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                                <th class="text-center">Kode Trans</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fa fa-info-circle"></i>
                                    Pilih tanggal lalu klik <strong>Show</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Kartu Pemakaian Material -->
<div class="modal fade" id="modal-kartu-pemakaian" tabindex="-1" aria-labelledby="modalKartuPemakaianLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalKartuPemakaianLabel">
                    <i class="fa fa-history me-1"></i> Kartu Pemakaian Material
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3 p-2 bg-light border rounded small">
                    <div class="col-md-4">
                        <span class="text-muted">Material:</span><br>
                        <strong id="kp-info-material">-</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted">No. Coil:</span><br>
                        <strong id="kp-info-nocoil">-</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted">Kode Internal:</span><br>
                        <strong id="kp-info-kode">-</strong>
                    </div>
                    <div class="col-md-2">
                        <span class="text-muted">Berat Awal (Net):</span><br>
                        <strong id="kp-info-net">-</strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table-kartu-pemakaian" class="table table-bordered table-striped table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>no. Trs</th>
                                <th>Material</th>
                                <th class="text-center">kode internal</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">dari</th>
                                <th class="text-center">ke</th>
                                <th class="text-end">qty trans</th>
                                <th class="text-end">Qty Awal</th>
                                <th class="text-end">Qty Akhir</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-kartu-pemakaian">
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        var BASE_URL = siteurl + 'stock_from_warehouse';

        // ── Column definitions — Transit & WIP (7 columns) ──
        var colDef7 = [{
                data: 0,
                width: '4%'
            },
            {
                data: 1
            },
            {
                data: 2,
                className: 'text-center'
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
        ];

        // ── Column definitions — History (9 columns) ──
        var colDefHistory = [{
                data: 0,
                width: '4%'
            },
            {
                data: 1
            },
            {
                data: 2,
                className: 'text-center'
            },
            {
                data: 3,
                className: 'text-center'
            },
            {
                data: 4,
                className: 'text-center'
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
                className: 'text-end'
            },
            {
                data: 8,
                className: 'text-center'
            },
        ];

        // ── Column definitions — Transit / WRH Production 2 (9 columns) ──
        var colDefTransit = [{
                data: 0,
                width: '4%',
                className: 'text-center',
                orderable: false
            },
            {
                data: 1
            },
            {
                data: 2,
                className: 'text-center'
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
                className: 'text-center'
            },
            {
                data: 8,
                width: '14%',
                className: 'text-center',
                orderable: false
            }
        ];

        // ── Tab Transit (active by default) ──
        var dtTransit = $('#table-stock-transit').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            autoWidth: false,
            sPaginationType: 'simple_numbers',
            iDisplayLength: 25,
            aLengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            ajax: {
                url: BASE_URL + '/data_side_transit',
                type: 'POST',
                cache: false
            },
            columns: colDefTransit,
            order: [
                [1, 'asc']
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                zeroRecords: 'No coil data available.',
                emptyTable: 'No data available.',
            }
        });

        // ── Modal Kartu Pemakaian Material ──
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatKartuQty(val) {
            var num = parseFloat(val);
            if (isNaN(num)) return '0';
            if (Math.abs(num) < 0.0005) num = 0;
            return num.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 3
            });
        }

        function openModalKartuPemakaian() {
            var modalEl = document.getElementById('modal-kartu-pemakaian');
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else {
                $('#modal-kartu-pemakaian').modal('show');
            }
        }

        $(document).on('click', '.btn-kartu-pemakaian', function() {
            var idCoil = $(this).data('id');
            if (!idCoil) return;

            $('#kp-info-material').text('-');
            $('#kp-info-nocoil').text('-');
            $('#kp-info-kode').text('-');
            $('#kp-info-net').text('-');
            $('#tbody-kartu-pemakaian').html(
                '<tr><td colspan="9" class="text-center text-muted py-4">' +
                '<i class="fa fa-spinner fa-spin me-1"></i> Memuat kartu pemakaian material...</td></tr>'
            );
            openModalKartuPemakaian();

            $.ajax({
                url: BASE_URL + '/ajax_kartu_pemakaian/' + encodeURIComponent(idCoil),
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (!res || parseInt(res.status, 10) !== 1) {
                        $('#tbody-kartu-pemakaian').html(
                            '<tr><td colspan="9" class="text-center text-danger py-4">' +
                            escapeHtml((res && res.message) ? res.message : 'Gagal memuat data.') +
                            '</td></tr>'
                        );
                        return;
                    }

                    var c = res.coil || {};
                    $('#kp-info-material').text(c.nm_material || c.id_material || '-');
                    $('#kp-info-nocoil').text(c.no_coil || '-');
                    $('#kp-info-kode').text(c.kode_internal || '-');
                    $('#kp-info-net').text(formatKartuQty(c.net_weight || 0) + ' Kg');

                    var rows = Array.isArray(res.rows) ? res.rows : [];
                    if (rows.length === 0) {
                        $('#tbody-kartu-pemakaian').html(
                            '<tr><td colspan="9" class="text-center text-muted py-4">' +
                            '<i class="fa fa-info-circle me-1"></i> Belum ada riwayat kartu pemakaian material untuk coil ini.' +
                            '</td></tr>'
                        );
                        return;
                    }

                    var html = '';
                    rows.forEach(function(r) {
                        var st = (r.status || '').toLowerCase();
                        var stBadge = st === 'in' ?
                            '<span class="badge bg-success">in</span>' :
                            '<span class="badge bg-danger">out</span>';
                        var qtyAkhir = parseFloat(r.qty_akhir || 0);
                        var akhirClass = qtyAkhir < 0 ? 'text-end fw-bold text-danger' : 'text-end fw-bold';

                        html += '<tr>' +
                            '<td><code>' + escapeHtml(r.no_trs || '-') + '</code></td>' +
                            '<td>' + escapeHtml(r.nm_material || c.nm_material || '-') + '</td>' +
                            '<td class="text-center">' + escapeHtml(r.kode_internal || c.kode_internal || '-') + '</td>' +
                            '<td class="text-center">' + stBadge + '</td>' +
                            '<td class="text-center">' + escapeHtml(r.dari || '-') + '</td>' +
                            '<td class="text-center">' + escapeHtml(r.ke || '-') + '</td>' +
                            '<td class="text-end">' + formatKartuQty(r.qty_trans) + '</td>' +
                            '<td class="text-end">' + formatKartuQty(r.qty_awal) + '</td>' +
                            '<td class="' + akhirClass + '">' + formatKartuQty(qtyAkhir) + '</td>' +
                            '</tr>';
                    });
                    $('#tbody-kartu-pemakaian').html(html);
                },
                error: function() {
                    $('#tbody-kartu-pemakaian').html(
                        '<tr><td colspan="9" class="text-center text-danger py-4">' +
                        'Terjadi kesalahan koneksi saat memuat Kartu Pemakaian Material.' +
                        '</td></tr>'
                    );
                }
            });
        });

        // ── Tab WIP (lazy load) ──
        var dtWip = null;
        document.getElementById('tab-wip-tab')
            .addEventListener('shown.bs.tab', function() {
                if (!dtWip) {
                    dtWip = $('#table-stock-wip').DataTable({
                        processing: true,
                        serverSide: true,
                        destroy: true,
                        autoWidth: false,
                        sPaginationType: 'simple_numbers',
                        iDisplayLength: 25,
                        aLengthMenu: [
                            [10, 25, 50, 100],
                            [10, 25, 50, 100]
                        ],
                        ajax: {
                            url: BASE_URL + '/data_side_wip',
                            type: 'POST',
                            cache: false
                        },
                        columns: colDef7,
                        order: [
                            [1, 'asc']
                        ],
                        language: {
                            processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                            zeroRecords: 'No coil data available.',
                            emptyTable: 'No data available.',
                        }
                    });
                }
            });

        // ── Tab On Hold (lazy load) ──
        var colDefOnHold = [{
                data: 0,
                width: '4%',
                className: 'text-center'
            },
            {
                data: 1
            },
            {
                data: 2
            },
            {
                data: 3,
                className: 'text-center'
            },
            {
                data: 4,
                className: 'text-center'
            },
            {
                data: 5,
                className: 'text-end'
            },
            {
                data: 6
            },
            {
                data: 7,
                className: 'text-center'
            },
            {
                data: 8,
                className: 'text-center',
                orderable: false
            }
        ];

        var dtOnHold = null;
        document.getElementById('tab-on-hold-tab')
            .addEventListener('shown.bs.tab', function() {
                if (!dtOnHold) {
                    dtOnHold = $('#table-stock-on-hold').DataTable({
                        processing: true,
                        serverSide: true,
                        destroy: true,
                        autoWidth: false,
                        sPaginationType: 'simple_numbers',
                        iDisplayLength: 25,
                        aLengthMenu: [
                            [10, 25, 50, 100],
                            [10, 25, 50, 100]
                        ],
                        ajax: {
                            url: BASE_URL + '/data_side_on_hold',
                            type: 'POST',
                            cache: false
                        },
                        columns: colDefOnHold,
                        order: [
                            [1, 'asc']
                        ],
                        language: {
                            processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                            zeroRecords: 'No coil data available.',
                            emptyTable: 'No data available.',
                        }
                    });
                }
            });

        // ── Flatpickr ──
        var fpHist = flatpickr('#hist_date', {
            locale: 'id',
            dateFormat: 'd/m/Y',
        });

        // ── Helper dd/mm/yyyy → yyyy-mm-dd ──
        function getYmd(dmy) {
            if (!dmy) return '';
            var p = dmy.split('/');
            return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : '';
        }

        // ── Tab History Per Days ──
        var dtHistory = null;

        function buildHistoryDt() {
            if (dtHistory) dtHistory.destroy();

            dtHistory = $('#table-history').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                autoWidth: false,
                sPaginationType: 'simple_numbers',
                iDisplayLength: 25,
                aLengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                ajax: {
                    url: BASE_URL + '/data_side_history',
                    type: 'POST',
                    data: function(d) {
                        d.date_filter = getYmd($('#hist_date').val());
                        d.kd_gudang = $('#hist_source').val();
                    },
                    cache: false,
                },
                columns: colDefHistory,
                order: [
                    [1, 'asc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Loading data...',
                    zeroRecords: 'Tidak ada data untuk tanggal ini.',
                    emptyTable: 'Tidak ada data.',
                }
            });
        }

        $('#btn-filter-hist').on('click', function() {
            if (!$('#hist_date').val()) {
                Swal.fire('Perhatian', 'Pilih tanggal terlebih dahulu.', 'warning');
                return;
            }
            buildHistoryDt();
            $('#btn-excel-hist').removeClass('d-none');
        });

        $('#btn-reset-hist').on('click', function() {
            fpHist.clear();
            $('#hist_source').val('');
            $('#btn-excel-hist').addClass('d-none');
            if (dtHistory) {
                dtHistory.destroy();
                dtHistory = null;
                $('#table-history tbody').html(
                    '<tr><td colspan="9" class="text-center text-muted py-4">' +
                    '<i class="fa fa-info-circle"></i> ' +
                    'Pilih tanggal lalu klik <strong>Show</strong>' +
                    '</td></tr>'
                );
            }
        });

        $('#btn-excel-hist').on('click', function() {
            var params = new URLSearchParams({
                date_filter: getYmd($('#hist_date').val()),
                kd_gudang: $('#hist_source').val(),
            });
            downloadWithLoading(BASE_URL + '/export_excel_history?' + params.toString());
        });

        // ── Download Excel with loading — Transit ──
        $('#btn-excel-transit').on('click', function() {
            downloadWithLoading(BASE_URL + '/export_excel_transit');
        });

        // ── Download Excel with loading — WIP ──
        $('#btn-excel-wip').on('click', function() {
            downloadWithLoading(BASE_URL + '/export_excel_wip');
        });

        // ── Download Excel with loading — On Hold ──
        $('#btn-excel-on-hold').on('click', function() {
            downloadWithLoading(BASE_URL + '/export_excel_on_hold');
        });

        // ── Helper: Download file with loading overlay ──
        function downloadWithLoading(url) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang menyiapkan file Excel, mohon tunggu.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.responseType = 'blob';

            xhr.onload = function() {
                Swal.close();
                if (xhr.status === 200) {
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    var filename = 'export.xls';
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        var match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        if (match && match[1]) {
                            filename = match[1].replace(/['"]/g, '');
                        }
                    }
                    var blob = xhr.response;
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(link.href);
                } else {
                    Swal.fire('Error', 'Gagal mengunduh file.', 'error');
                }
            };

            xhr.onerror = function() {
                Swal.close();
                Swal.fire('Error', 'Terjadi kesalahan saat mengunduh file.', 'error');
            };

            xhr.send();
        }

        // ── Auto switch tab dari URL hash (misal #tab-on-hold) ──
        if (window.location.hash) {
            var activeTabLink = document.querySelector('a[href="' + window.location.hash + '"]');
            if (activeTabLink) {
                var tabTrigger = new bootstrap.Tab(activeTabLink);
                tabTrigger.show();
            }
        }

    });
</script>