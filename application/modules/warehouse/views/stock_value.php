<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <select id="filter_gudang" class="form-control select2">
                    <option value="">-- Semua Gudang --</option>
                    <?php foreach ($list_gudang as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= $g['nm_gudang'] ?> (<?= $g['kd_gudang'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" id="filter_material" class="form-control" placeholder="Cari material...">
            </div>
            <div class="col-md-4 text-right">
                <button class="btn btn-primary" id="btn-filter"><i class="fa fa-search"></i> Filter</button>
                <button class="btn btn-default" id="btn-reset"><i class="fa fa-refresh"></i> Reset</button>
                <button class="btn btn-success" id="btn-excel"><i class="fa fa-file-excel-o"></i> Download Excel</button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="table-stock-value" class="table table-bordered table-striped table-hover">
                <thead class="bg-blue">
                    <tr>
                        <th width="4%">No</th>
                        <th>Kode Material</th>
                        <th>Nama Material</th>
                        <th>Gudang</th>
                        <th class="text-right">Qty Stock</th>
                        <th class="text-right">Harga Beli (Avg)</th>
                        <th class="text-right">Total Nilai</th>
                        <th width="8%" class="text-center no-sort">Aksi</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-right"><strong>GRAND TOTAL</strong></th>
                        <th class="text-right" id="grand-total">0</th>
                        <th></th>
                    </tr>
                </tfoot>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal History -->
<div class="modal fade" id="modal-history" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <h4 class="modal-title"><i class="fa fa-history"></i> History Stok — <span id="modal-title-material"></span></h4>
                <button type="button" class="close" onclick="$('#modal-history').modal('hide')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-history" class="table table-bordered table-striped table-sm">
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
                            <tr><td colspan="13" class="text-center">Pilih material untuk melihat history</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="$('#modal-history').modal('hide')">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
$(document).ready(function() {
    $('.select2').select2();
    loadTable();

    $('#btn-filter').on('click', function() { loadTable(); });
    $('#btn-reset').on('click', function() {
        $('#filter_gudang').val('').trigger('change');
        $('#filter_material').val('');
        loadTable();
    });

    $('#btn-excel').on('click', function() {
        var params = new URLSearchParams({
            id_gudang: $('#filter_gudang').val()
        });
        window.location.href = siteurl + 'warehouse/export_excel_stock_value?' + params.toString();
    });
});

var dtStockValue = null;

function loadTable() {
    if (dtStockValue) {
        dtStockValue.destroy();
    }

    dtStockValue = $('#table-stock-value').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        autoWidth: false,
        responsive: true,
        sPaginationType: 'simple_numbers',
        iDisplayLength: 25,
        aLengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        columnDefs: [{ targets: 'no-sort', orderable: false }],
        ajax: {
            url: siteurl + 'warehouse/data_side_stock_value',
            type: 'POST',
            data: function(d) {
                d.id_gudang      = $('#filter_gudang').val();
                d.filter_material = $('#filter_material').val();
            },
            cache: false
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4, className: 'text-right' },
            { data: 5, className: 'text-right' },
            { data: 6, className: 'text-right' },
            { data: 7, className: 'text-center' }
        ],
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();
            // Hitung grand total dari semua halaman via AJAX terpisah
            $.post(siteurl + 'warehouse/get_grand_total_stock_value', {
                id_gudang: $('#filter_gudang').val(),
                filter_material: $('#filter_material').val()
            }, function(res) {
                $('#grand-total').html('<strong>' + res.total + '</strong>');
            }, 'json');
        }
    });
}

function showHistory(id_material, nm_material, id_gudang) {
    $('#modal-title-material').text(nm_material);
    $('#tbody-history').html('<tr><td colspan="13" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
    $('#modal-history').modal('show');

    $.post(siteurl + 'warehouse/get_history_material', {
        id_material: id_material,
        id_gudang: id_gudang
    }, function(data) {
        if (data.length === 0) {
            $('#tbody-history').html('<tr><td colspan="13" class="text-center">Tidak ada history</td></tr>');
            return;
        }
        var html = '';
        $.each(data, function(i, row) {
            html += '<tr>' +
                '<td class="text-center">' + (i + 1) + '</td>' +
                '<td>' + row.update_date + '</td>' +
                '<td>' + (row.no_ipp || '-') + '</td>' +
                '<td>' + (row.no_coil || '-') + '</td>' +
                '<td>' + (row.kd_gudang || '-') + '</td>' +
                '<td>' + (row.ket || '-') + '</td>' +
                '<td class="text-right">' + formatNum(row.jumlah_mat) + '</td>' +
                '<td class="text-right">' + formatNum(row.harga_beli) + '</td>' +
                '<td class="text-right">' + formatNum(row.total_harga) + '</td>' +
                '<td class="text-right">' + formatNum(row.saldo_awal) + '</td>' +
                '<td class="text-right">' + formatNum(row.saldo_akhir) + '</td>' +
                '<td class="text-right">' + formatNum(row.harga_lama) + '</td>' +
                '<td class="text-right">' + formatNum(row.harga_baru) + '</td>' +
                '</tr>';
        });
        $('#tbody-history').html(html);
    }, 'json');
}

function formatNum(val) {
    if (!val && val !== 0) return '-';
    return Number(val).toLocaleString('id-ID');
}
</script>
