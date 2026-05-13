<!-- Bootstrap 5 CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<div class="card">
    <div class="card-body">

        <div class="row mb-3">
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
                                <th colspan="6" class="text-end">
                                    <strong>GRAND TOTAL PUSAT</strong>
                                </th>
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
                                <th colspan="6" class="text-end">
                                    <strong>GRAND TOTAL PENJUALAN</strong>
                                </th>
                                <th class="text-end" id="grand-total-penjualan">—</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <tbody></tbody>
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
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>No. Coil</th>
                                <th>Gudang</th>
                                <th>Keterangan</th>
                                <th class="text-end">Qty Masuk</th>
                                <th class="text-end">Harga Beli</th>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {

        var colDef = [{
                data: 0,
                width: '4%'
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
                columns: colDef,
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
            var kdGudang = document.getElementById('tab-sv-pusat').classList.contains('active') ? 'PUS' : 'PEN';
            window.location.href = siteurl + 'warehouse/export_excel_stock_value?kd_gudang=' + kdGudang;
        });

    });

    // ── Modal History ─────────────────────────────────────────────────────────
    function showHistory(id_material, nm_material, id_gudang) {
        $('#modal-title-material').text(nm_material);
        $('#tbody-history').html(
            '<tr><td colspan="13" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>'
        );
        new bootstrap.Modal(document.getElementById('modal-history')).show();

        $.post(siteurl + 'warehouse/get_history_material', {
            id_material: id_material,
            id_gudang: id_gudang
        }, function(data) {
            if (!data.length) {
                $('#tbody-history').html('<tr><td colspan="13" class="text-center">Tidak ada history</td></tr>');
                return;
            }
            var html = '';
            $.each(data, function(i, row) {
                html += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td>' + (row.update_date || '-') + '</td>' +
                    '<td>' + (row.no_ipp || '-') + '</td>' +
                    '<td>' + (row.no_coil || '-') + '</td>' +
                    '<td>' + (row.kd_gudang || '-') + '</td>' +
                    '<td>' + (row.ket || '-') + '</td>' +
                    '<td class="text-end">' + fmtNum(row.jumlah_mat) + '</td>' +
                    '<td class="text-end">' + fmtNum(row.harga_beli) + '</td>' +
                    '<td class="text-end">' + fmtNum(row.total_harga) + '</td>' +
                    '<td class="text-end">' + fmtNum(row.saldo_awal) + '</td>' +
                    '<td class="text-end">' + fmtNum(row.saldo_akhir) + '</td>' +
                    '<td class="text-end">' + fmtNum(row.harga_lama) + '</td>' +
                    '<td class="text-end">' + fmtNum(row.harga_baru) + '</td>' +
                    '</tr>';
            });
            $('#tbody-history').html(html);
        }, 'json');
    }

    // ── Modal Detail Coil ─────────────────────────────────────────────────────
    function showDetailCoil(id_material, nm_material, id_gudang) {
        $('#coil-title-material').text(nm_material);
        $('#tbody-detail-coil').html(
            '<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>'
        );
        new bootstrap.Modal(document.getElementById('modal-detail-coil')).show();

        $.post(siteurl + 'warehouse/get_detail_coil', {
            id_material: id_material,
            id_gudang: id_gudang
        }, function(data) {
            if (!data.length) {
                $('#tbody-detail-coil').html('<tr><td colspan="6" class="text-center">Tidak ada data coil</td></tr>');
                return;
            }
            var html = '';
            $.each(data, function(i, row) {
                html += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td>' + (row.no_coil || '-') + '</td>' +
                    '<td>' + (row.kode_internal || '-') + '</td>' +
                    '<td class="text-end">' + fmtDec(row.net_weight) + '</td>' +
                    '<td class="text-end">' + fmtDec(row.gross_weight) + '</td>' +
                    '<td class="text-end">' + fmtDec(row.length) + '</td>' +
                    '</tr>';
            });
            $('#tbody-detail-coil').html(html);
        }, 'json');
    }

    function fmtNum(val) {
        if (val === null || val === undefined || val === '') return '-';
        return Number(val).toLocaleString('id-ID');
    }

    function fmtDec(val) {
        if (val === null || val === undefined || val === '') return '-';
        return Number(val).toLocaleString('id-ID', {
            minimumFractionDigits: 3,
            maximumFractionDigits: 3
        });
    }
</script>