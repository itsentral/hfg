<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .card {
        border-radius: 0.75rem;
    }

    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row align-items-center g-3">
                    <div class="col-md-6 col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="fa fa-boxes-stacked fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-0">Stok Gudang Barang</h5>
                                <small class="text-muted">Monitoring ketersediaan stok fisik barang, satuan packing, dan mutasi kartu stok</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12 text-md-end">
                        <button type="button" class="btn btn-success shadow-sm" id="download_excel">
                            <i class="fa fa-file-excel me-1"></i> Download Rekap Excel
                        </button>
                    </div>
                </div>

                <!-- Filter Controls Section -->
                <div class="row g-3 align-items-end mt-2 pt-3 border-top">
                    <div class="col-md-4 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-warehouse text-primary me-1"></i> Pilih Gudang</label>
                        <select name="id_warehouse" id="id_warehouse" class="form-select select2">
                            <?php foreach ($warehouse as $key => $value) : ?>
                                <option value="<?= $value['id']; ?>">
                                    <?= strtoupper($value['nm_gudang']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-tags text-primary me-1"></i> Kategori Barang</label>
                        <select name="id_category" id="id_category" class="form-select select2">
                            <option value="0">SEMUA KATEGORI</option>
                            <?php foreach ($category as $key => $value) : ?>
                                <option value="<?= $value['id']; ?>">
                                    <?= strtoupper($value['nm_category']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-calendar text-primary me-1"></i> Tanggal Stok (Histori)</label>
                        <div class="input-group">
                            <input type="text" name="date_filter" id="date_filter" class="form-control text-center" placeholder="Stok Saat Ini (Klik untuk histori tanggal)">
                            <button class="btn btn-outline-secondary" type="button" id="btn_clear_date" title="Reset ke Stok Saat Ini">
                                <i class="fa fa-undo"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="example1" class="table table-hover table-striped align-middle border rounded" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="4%">#</th>
                                <th width="14%">Kategori</th>
                                <th class="text-center" width="12%">Kode Stok</th>
                                <th>Nama Barang</th>
                                <th width="13%">Warehouse</th>
                                <th class="text-center no-sort" width="9%">Unit Packing</th>
                                <th class="text-end no-sort" width="10%">Stok Pack</th>
                                <th class="text-end no-sort" width="9%">Konversi</th>
                                <th class="text-end no-sort" width="10%">Stok Unit</th>
                                <th class="text-center no-sort" width="9%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal View History Mutasi -->
<div class="modal fade" id="ModalView" tabindex="-1" aria-labelledby="head_title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="head_title">
                    <i class="fa fa-history text-primary"></i> Riwayat Mutasi Kartu Stok
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="view">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Memuat data histori mutasi...</p>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-2">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        if ($('.select2').length) {
            $('.select2').select2({
                width: '100%'
            });
        }

        if (typeof flatpickr !== 'undefined') {
            flatpickr('#date_filter', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd F Y',
                locale: 'id',
                maxDate: 'today',
                allowInput: true,
                onChange: function(selectedDates, dateStr, instance) {
                    reloadTable();
                }
            });
        }

        $('#btn_clear_date').on('click', function() {
            var fp = document.querySelector("#date_filter")._flatpickr;
            if (fp) {
                fp.clear();
            } else {
                $('#date_filter').val('');
            }
            reloadTable();
        });

        reloadTable();

        $(document).on('change', '#id_category, #id_warehouse', function(e) {
            e.preventDefault();
            reloadTable();
        });

        function reloadTable() {
            var date_filter = $('#date_filter').val();
            var id_category = $('#id_category').val();
            var id_warehouse = $('#id_warehouse').val();
            DataTables(date_filter, id_category, id_warehouse);
        }

        // Handler tombol Riwayat Mutasi
        $(document).on('click', '.hist', function(e) {
            e.preventDefault();
            var gudang = $(this).data('gudang');
            var material = $(this).data('material');

            $("#head_title").html('<i class="fa fa-history text-primary me-2"></i><b>RIWAYAT MUTASI KARTU STOK</b>');
            $("#view").html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2">Memuat riwayat transaksi mutasi...</p></div>');

            var modalEl = document.getElementById('ModalView');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalObj.show();
            } else {
                $('#ModalView').modal('show');
            }

            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'modal_history',
                data: {
                    "gudang": gudang,
                    "material": material
                },
                success: function(data) {
                    $("#view").html(data);
                },
                error: function() {
                    $("#view").html('<div class="alert alert-danger mb-0"><i class="fa fa-exclamation-triangle me-1"></i> Gagal mengambil data histori mutasi dari server.</div>');
                }
            });
        });

        $(document).on('click', '.hist_excel', function(e) {
            e.preventDefault();
            var id_warehouse = $(this).data('gudang');
            var id_barang = $(this).data('material');

            var Links = siteurl + active_controller + 'download_excel_history/' + id_warehouse + '/' + id_barang;
            window.open(Links, '_blank');
        });

        $(document).on('click', '#download_excel', function(e) {
            e.preventDefault();
            var id_warehouse = $("#id_warehouse").val();
            var id_category = $("#id_category").val();
            var date_filter = $("#date_filter").val() || '0';

            var Links = siteurl + active_controller + 'download_excel/' + id_warehouse + '/' + id_category + '/' + encodeURIComponent(date_filter);
            window.open(Links, '_blank');
        });
    });

    function DataTables(date_filter = null, id_category = null, id_warehouse = null) {
        if ($.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().destroy();
        }

        $('#example1').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            autoWidth: false,
            responsive: true,
            order: [
                [1, "asc"]
            ],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            ajax: {
                url: siteurl + active_controller + 'data_side_stock',
                type: "POST",
                data: function(d) {
                    d.date_filter = date_filter;
                    d.id_category = id_category;
                    d.id_warehouse = id_warehouse;
                },
                cache: false,
                error: function() {
                    $("#example1 tbody").html('<tr><td colspan="10" class="text-center text-muted py-4"><i class="fa fa-info-circle me-1"></i> Tidak ada data stok ditemukan</td></tr>');
                }
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari nama barang / kode...",
                lengthMenu: "_MENU_ data per halaman",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                emptyTable: "Data stok tidak tersedia",
                zeroRecords: "Tidak ada data yang cocok",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data stok...'
            }
        });
    }
</script>