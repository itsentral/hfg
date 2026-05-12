<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card">
    <div class="card-body">

        <!-- Filter baris atas -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="filter_material" class="form-control"
                       placeholder="Cari kode / nama material...">
            </div>
            <div class="col-md-8 text-right">
                <button class="btn btn-primary"  id="btn-filter">
                    <i class="fa fa-search"></i> Filter
                </button>
                <button class="btn btn-default"  id="btn-reset">
                    <i class="fa fa-refresh"></i> Reset
                </button>
                <button class="btn btn-success"  id="btn-excel">
                    <i class="fa fa-file-excel-o"></i> Download Excel
                </button>
            </div>
        </div>

        <!-- Tab Gudang -->
        <ul class="nav nav-tabs mb-3" id="tabStockValue" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-sv-pusat-tab"
                   data-bs-toggle="tab" href="#tab-sv-pusat" role="tab">
                    <i class="fa fa-warehouse"></i> Gudang Pusat
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-sv-penjualan-tab"
                   data-bs-toggle="tab" href="#tab-sv-penjualan" role="tab">
                    <i class="fa fa-store"></i> Gudang Penjualan
                </a>
            </li>
        </ul>

        <div class="tab-content" id="tabStockValueContent">

            <!-- TAB PUSAT -->
            <div class="tab-pane fade show active" id="tab-sv-pusat" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-sv-pusat"
                           class="table table-bordered table-striped table-hover">
                        <thead class="bg-blue">
                            <tr>
                                <th width="4%">No</th>
                                <th>Kode Material</th>
                                <th>Nama Material</th>
                                <th>Gudang</th>
                                <th class="text-center">Jml Coil</th>
                                <th class="text-right">Qty Stock (Kg)</th>
                                <th class="text-right">Harga Beli (Avg)</th>
                                <th class="text-right">Total Nilai</th>
                                <th width="8%" class="text-center no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-right">
                                    <strong>GRAND TOTAL PUSAT</strong>
                                </th>
                                <th class="text-right" id="grand-total-pusat">—</th>
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
                    <table id="table-sv-penjualan"
                           class="table table-bordered table-striped table-hover">
                        <thead class="bg-green">
                            <tr>
                                <th width="4%">No</th>
                                <th>Kode Material</th>
                                <th>Nama Material</th>
                                <th>Gudang</th>
                                <th class="text-center">Jml Coil</th>
                                <th class="text-right">Qty Stock (Kg)</th>
                                <th class="text-right">Harga Beli (Avg)</th>
                                <th class="text-right">Total Nilai</th>
                                <th width="8%" class="text-center no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-right">
                                    <strong>GRAND TOTAL PENJUALAN</strong>
                                </th>
                                <th class="text-right" id="grand-total-penjualan">—</th>
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
<div class="modal fade" id="modal-history" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <h4 class="modal-title">
                    <i class="fa fa-history"></i>
                    History Stok — <span id="modal-title-material"></span>
                </h4>
                <button type="button" class="close"
                        onclick="$('#modal-history').modal('hide')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="bg-gray">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>No. Coil</th>
                                <th>Gudang</th>
                                <th>Keterangan</th>
                                <th class="text-right">Qty Masuk</th>
                                <th class="text-right">Harga Beli</th>
                                <th class="text-right">Total Harga</th>
                                <th class="text-right">Saldo Awal</th>
                                <th class="text-right">Saldo Akhir</th>
                                <th class="text-right">Harga Lama</th>
                                <th class="text-right">Harga Baru (Avg)</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-history">
                            <tr>
                                <td colspan="13" class="text-center">
                                    Pilih material untuk melihat history
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        onclick="$('#modal-history').modal('hide')">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
$(document).ready(function () {

    // ── Kolom DataTable ──────────────────────────────────────────────────
    var colDef = [
        { data: 0, width: '4%' },
        { data: 1 },
        { data: 2 },
        { data: 3 },
        { data: 4, className: 'text-center' },
        { data: 5, className: 'text-right' },
        { data: 6, className: 'text-right' },
        { data: 7, className: 'text-right' },
        { data: 8, className: 'text-center', orderable: false },
    ];

    // ── Builder opsi DataTable ────────────────────────────────────────────
    function dtOptions(endpoint, grandTotalId, kdGudang) {
        return {
            processing   : true,
            serverSide   : true,
            destroy      : true,
            autoWidth    : false,
            responsive   : true,
            sPaginationType: 'simple_numbers',
            iDisplayLength : 25,
            aLengthMenu  : [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            columnDefs   : [{ targets: 'no-sort', orderable: false }],
            ajax: {
                url  : siteurl + 'warehouse/' + endpoint,
                type : 'POST',
                data : function (d) {
                    d.filter_material = $('#filter_material').val();
                },
                cache: false,
            },
            columns : colDef,
            order   : [[2, 'asc']],
            language: {
                processing : '<i class="fa fa-spinner fa-spin fa-fw"></i> Memuat data...',
                zeroRecords: 'Tidak ada data stock.',
                emptyTable : 'Tidak ada data.',
            },
            drawCallback: function () {
                // Update grand total setiap selesai draw
                $.post(siteurl + 'warehouse/get_grand_total_stock_value', {
                    kd_gudang       : kdGudang,
                    filter_material : $('#filter_material').val(),
                }, function (res) {
                    $('#' + grandTotalId).html('<strong>' + res.total + '</strong>');
                }, 'json');
            }
        };
    }

    // ── Init tab Pusat ────────────────────────────────────────────────────
    var dtPusat = $('#table-sv-pusat').DataTable(
        dtOptions('data_side_stock_value_pusat', 'grand-total-pusat', 'PUS')
    );

    // ── Init tab Penjualan (lazy — saat pertama kali dibuka) ──────────────
    var dtPenjualan = null;
    $('#tab-sv-penjualan-tab').one('shown.bs.tab', function () {
        dtPenjualan = $('#table-sv-penjualan').DataTable(
            dtOptions('data_side_stock_value_penjualan', 'grand-total-penjualan', 'PEN')
        );
    });

    // ── Filter & Reset ────────────────────────────────────────────────────
    $('#btn-filter').on('click', function () {
        dtPusat.ajax.reload();
        if (dtPenjualan) dtPenjualan.ajax.reload();
    });

    $('#btn-reset').on('click', function () {
        $('#filter_material').val('');
        dtPusat.ajax.reload();
        if (dtPenjualan) dtPenjualan.ajax.reload();
    });

    // ── Export Excel ──────────────────────────────────────────────────────
    $('#btn-excel').on('click', function () {
        // Deteksi tab aktif → kirim kd_gudang yang sesuai
        var kdGudang = $('#tab-sv-pusat').hasClass('active') ? 'PUS' : 'PEN';
        var params = new URLSearchParams({
            kd_gudang: kdGudang
        });
        window.location.href = siteurl + 'warehouse/export_excel_stock_value?' + params.toString();
    });

});

// ── History modal ─────────────────────────────────────────────────────────
function showHistory(id_material, nm_material, id_gudang) {
    $('#modal-title-material').text(nm_material);
    $('#tbody-history').html(
        '<tr><td colspan="13" class="text-center">' +
        '<i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>'
    );
    $('#modal-history').modal('show');

    $.post(siteurl + 'warehouse/get_history_material', {
        id_material : id_material,
        id_gudang   : id_gudang
    }, function (data) {
        if (!data.length) {
            $('#tbody-history').html(
                '<tr><td colspan="13" class="text-center">Tidak ada history</td></tr>'
            );
            return;
        }
        var html = '';
        $.each(data, function (i, row) {
            html += '<tr>' +
                '<td class="text-center">'  + (i + 1) + '</td>' +
                '<td>'                      + (row.update_date  || '-') + '</td>' +
                '<td>'                      + (row.no_ipp       || '-') + '</td>' +
                '<td>'                      + (row.no_coil      || '-') + '</td>' +
                '<td>'                      + (row.kd_gudang    || '-') + '</td>' +
                '<td>'                      + (row.ket          || '-') + '</td>' +
                '<td class="text-right">'   + fmtNum(row.jumlah_mat)   + '</td>' +
                '<td class="text-right">'   + fmtNum(row.harga_beli)   + '</td>' +
                '<td class="text-right">'   + fmtNum(row.total_harga)  + '</td>' +
                '<td class="text-right">'   + fmtNum(row.saldo_awal)   + '</td>' +
                '<td class="text-right">'   + fmtNum(row.saldo_akhir)  + '</td>' +
                '<td class="text-right">'   + fmtNum(row.harga_lama)   + '</td>' +
                '<td class="text-right">'   + fmtNum(row.harga_baru)   + '</td>' +
                '</tr>';
        });
        $('#tbody-history').html(html);
    }, 'json');
}

function fmtNum(val) {
    if (val === null || val === undefined || val === '') return '-';
    return Number(val).toLocaleString('id-ID');
}
</script>