<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card">
    <div class="card-body">

        <!-- Tab Gudang -->
        <ul class="nav nav-tabs mb-3" id="tabStockCoil" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-pusat-coil-tab"
                    data-bs-toggle="tab" href="#tab-pusat-coil" role="tab">
                    <i class="fa fa-warehouse"></i> Gudang Pusat
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-penjualan-coil-tab"
                    data-bs-toggle="tab" href="#tab-penjualan-coil" role="tab">
                    <i class="fa fa-store"></i> Gudang Penjualan
                </a>
            </li>
        </ul>

        <div class="tab-content" id="tabStockCoilContent">

            <!-- TAB PUSAT -->
            <div class="tab-pane fade show active" id="tab-pusat-coil" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-stock-pusat"
                        class="table table-bordered table-striped table-hover">
                        <thead class="bg-blue">
                            <tr>
                                <th width="4%">No</th>
                                <th>Nama Material (Lv.4)</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Kode Internal</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- TAB PENJUALAN -->
            <div class="tab-pane fade" id="tab-penjualan-coil" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-stock-penjualan"
                        class="table table-bordered table-striped table-hover">
                        <thead class="bg-green">
                            <tr>
                                <th width="4%">No</th>
                                <th>Nama Material (Lv.4)</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Kode Internal</th>
                                <th class="text-right">Nett Weight (Kg)</th>
                                <th class="text-right">Gross Weight (Kg)</th>
                                <th class="text-right">Length (M)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

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
                data: 2,
                className: 'text-center'
            }, // No. Coil
            {
                data: 3,
                className: 'text-center'
            }, // Kode Internal
            {
                data: 4,
                className: 'text-end'
            }, // Nett Weight
            {
                data: 5,
                className: 'text-end'
            }, // Gross Weight
            {
                data: 6,
                className: 'text-end'
            }, // Length
        ];

        var dtOptions = function(endpoint, badgeId) {
            return {
                processing: true,
                serverSide: true,
                destroy: true,
                autoWidth: false,
                responsive: true,
                sPaginationType: 'simple_numbers',
                iDisplayLength: 25,
                aLengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                ajax: {
                    url: siteurl + 'warehouse/' + endpoint,
                    type: 'POST',
                    cache: false,
                },
                columns: colDef,
                order: [
                    [1, 'asc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Memuat data...',
                    zeroRecords: 'Tidak ada data coil di gudang ini.',
                    emptyTable: 'Tidak ada data.',
                }
            };
        };

       var dtPusat = $('#table-stock-pusat').DataTable(
    dtOptions('data_side_stock_pusat')
);

var dtPenjualan = null;
document.getElementById('tab-penjualan-coil-tab')
    .addEventListener('shown.bs.tab', function () {
        if (!dtPenjualan) {
            dtPenjualan = $('#table-stock-penjualan').DataTable(
                dtOptions('data_side_stock_penjualan')
            );
        }
    });

    });
</script>