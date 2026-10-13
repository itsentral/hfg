<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Styling Select2 agar serasi dengan Berry Theme Bootstrap 5 */
    .select2-container--default .select2-selection--single {
        height: 35px !important;
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 33px !important;
        font-size: 0.875rem !important;
        color: #2b3445 !important;
        padding-left: 10px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 33px !important;
    }

    .select2-dropdown {
        border-color: #ced4da !important;
        border-radius: 6px !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08) !important;
    }

    .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
        padding: 5px 8px !important;
    }

    .flatpickr-input[readonly] {
        background-color: #ffffff !important;
        cursor: pointer;
    }

    /* Skeleton Loading Shimmer Effect */
    @keyframes skeleton-shimmer {
        0% {
            background-position: -200px 0;
        }

        100% {
            background-position: calc(200px + 100%) 0;
        }
    }

    .skeleton {
        background: linear-gradient(90deg, #f0f2f5 0px, #e4e7eb 50%, #f0f2f5 100%);
        background-size: 200px 100%;
        animation: skeleton-shimmer 1.5s infinite;
        border-radius: 6px;
        display: inline-block;
    }

    .btn-detail {
        cursor: pointer;
        transition: color 0.15s ease-in-out;
    }

    .btn-detail:hover {
        text-decoration: underline !important;
        color: #1e40af !important;
    }
</style>

<!-- 1. CARD SUMMARY COUNTER (PALING ATAS) -->
<div id="container_summary_cards">
    <!-- Diisi Skeleton Loader atau HTML via AJAX -->
</div>

<!-- 2. CARD FILTER & EXCEL (DI TENGAH) -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form id="form_filter_po" class="row g-2 align-items-end">
            <div class="col-lg-3 col-md-4 col-sm-6">
                <label class="form-label small text-muted mb-1 fw-semibold">Status Progres</label>
                <select name="status" id="filter_status" class="form-select form-select-sm">
                    <option value="all">Semua Status</option>
                    <option value="belum_mulai">Belum Mulai</option>
                    <option value="partial">Partial</option>
                    <option value="selesai">Selesai</option>
                    <option value="over_delivery">Over-delivery</option>
                </select>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <label class="form-label small text-muted mb-1 fw-semibold">Supplier</label>
                <select name="supplier" id="filter_supplier" class="form-select form-select-sm select2">
                    <option value="">Semua Supplier</option>
                    <?php if (!empty($suppliers)) : ?>
                        <?php foreach ($suppliers as $sup) : ?>
                            <option value="<?= $sup->kode_supplier; ?>">
                                <?= htmlspecialchars($sup->nama); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <label class="form-label small text-muted mb-1 fw-semibold">Tgl PO Dari</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="ti ti-calendar"></i></span>
                    <input type="text" name="tgl_awal" id="filter_tgl_awal" class="form-control form-control-sm border-start-0" placeholder="Pilih tanggal...">
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <label class="form-label small text-muted mb-1 fw-semibold">Tgl PO Sampai</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="ti ti-calendar"></i></span>
                    <input type="text" name="tgl_akhir" id="filter_tgl_akhir" class="form-control form-control-sm border-start-0" placeholder="Pilih tanggal...">
                </div>
            </div>

            <!-- Tombol Filter, Refresh, dan Export Excel Berdampingan 1 Baris -->
            <div class="col-lg-2 col-md-8 col-sm-12 d-flex gap-1">
                <button type="submit" id="btn_submit_filter" class="btn btn-sm btn-primary flex-fill" title="Terapkan Filter">
                    <i class="ti ti-filter me-1"></i> Filter
                </button>
                <button type="button" id="btn_reset_filter" class="btn btn-sm btn-outline-secondary" title="Reset Semua Filter & Refresh Data">
                    <i class="ti ti-refresh"></i>
                </button>
                <a href="<?= site_url('report_monitoring_po/download_excel'); ?>" id="btn_export_excel" class="btn btn-sm btn-outline-success" title="Export Multi-Sheet ke Excel">
                    <i class="ti ti-file-spreadsheet"></i> Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- 3. CONTAINER TABLE MONITORING PO (DI BAWAH FILTER) -->
<div id="container_table_po">
    <!-- Diisi Skeleton Table atau HTML via AJAX -->
</div>

<!-- Modal Dialog Detail Riwayat Kedatangan -->
<div class="modal fade" id="modal_detail_po" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title text-white fw-bold" id="modalDetailLabel">
                    <i class="ti ti-truck-delivery me-2"></i>Detail Riwayat Kedatangan PO
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" id="modal_detail_content" style="max-height: 70vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memuat data riwayat kedatangan...</p>
                </div>
            </div>

            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var fp_awal, fp_akhir;

        // Inisialisasi Select2 Supplier
        if ($('.select2').length) {
            $('#filter_supplier').select2({
                width: '100%',
                placeholder: "Semua Supplier",
                allowClear: true
            });
        }

        // Inisialisasi Flatpickr Tanggal
        if (typeof flatpickr !== 'undefined') {
            fp_awal = flatpickr('#filter_tgl_awal', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                locale: 'id',
                allowInput: true
            });

            fp_akhir = flatpickr('#filter_tgl_akhir', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                locale: 'id',
                allowInput: true
            });
        }

        // Template Skeleton Summary Cards
        function getSkeletonSummaryHTML() {
            return `
                <div class="row g-3 mb-4">
                    ${[1, 2, 3, 4].map(() => `
                        <div class="col-md-3 col-sm-6">
                            <div class="card border-0 shadow-sm h-100 bg-white">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="skeleton rounded-circle me-3" style="width: 50px; height: 50px;"></div>
                                    <div class="flex-grow-1">
                                        <div class="skeleton mb-2" style="width: 70%; height: 12px;"></div>
                                        <div class="skeleton mb-1" style="width: 50%; height: 22px;"></div>
                                        <div class="skeleton" style="width: 40%; height: 10px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Template Skeleton Table
        function getSkeletonTableHTML() {
            return `
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="skeleton" style="width: 150px; height: 32px;"></div>
                            <div class="skeleton" style="width: 200px; height: 32px;"></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        ${[45, 140, 100, 160, 150, 240, 120, 120, 120, 130].map(w => `
                                            <th style="width: ${w}px;"><div class="skeleton" style="width: 80%; height: 16px;"></div></th>
                                        `).join('')}
                                    </tr>
                                </thead>
                                <tbody>
                                    ${[1, 2, 3, 4, 5, 6].map(() => `
                                        <tr>
                                            ${[45, 140, 100, 160, 150, 240, 120, 120, 120, 130].map(w => `
                                                <td><div class="skeleton" style="width: 90%; height: 20px;"></div></td>
                                            `).join('')}
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        }

        // Fungsi Load Data Content via AJAX
        function loadData() {
            var status    = $('#filter_status').val() || 'all';
            var supplier  = $('#filter_supplier').val() || '';
            var tgl_awal  = $('#filter_tgl_awal').val() || '';
            var tgl_akhir = $('#filter_tgl_akhir').val() || '';

            // Update Link Download Excel dengan parameter aktif
            var excelUrl = "<?= site_url('report_monitoring_po/download_excel'); ?>?" + 
                           $.param({ status: status, supplier: supplier, tgl_awal: tgl_awal, tgl_akhir: tgl_akhir });
            $('#btn_export_excel').attr('href', excelUrl);

            // Tampilkan Skeleton Loading untuk Summary Cards dan Table
            $('#container_summary_cards').html(getSkeletonSummaryHTML());
            $('#container_table_po').html(getSkeletonTableHTML());

            $.ajax({
                type: 'GET',
                url: "<?= site_url('report_monitoring_po/load_data'); ?>",
                data: {
                    status: status,
                    supplier: supplier,
                    tgl_awal: tgl_awal,
                    tgl_akhir: tgl_akhir
                },
                dataType: 'json',
                success: function(res) {
                    if (res && res.status === 'success') {
                        // Render Summary Cards di paling atas
                        $('#container_summary_cards').html(res.summary_html);

                        // Render Table di bawah Filter
                        $('#container_table_po').html(res.table_html);

                        // Re-inisialisasi DataTable
                        $('#table_monitoring_po').DataTable({
                            responsive: true,
                            pageLength: 10,
                            lengthMenu: [10, 25, 50, 100],
                            language: {
                                search: "Cari:",
                                lengthMenu: "Tampilkan _MENU_ data",
                                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ PO",
                                infoEmpty: "Menampilkan 0 data",
                                zeroRecords: "Tidak ada PO yang sesuai dengan filter",
                                paginate: {
                                    previous: "Prev",
                                    next: "Next"
                                }
                            }
                        });
                    } else {
                        $('#container_table_po').html('<div class="alert alert-warning">Terjadi kendala saat memuat data.</div>');
                    }
                },
                error: function() {
                    $('#container_table_po').html(`
                        <div class="alert alert-danger shadow-sm text-center py-4 my-3">
                            <i class="ti ti-alert-triangle fs-2 d-block mb-2"></i>
                            <strong>Gagal memuat data monitoring PO!</strong>
                            <p class="mb-2 small text-muted">Terjadi kesalahan pada server saat mengambil data.</p>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btn_retry_load">Coba Lagi</button>
                        </div>
                    `);
                    $('#btn_retry_load').on('click', function() {
                        loadData();
                    });
                }
            });
        }

        // Initial Load saat halaman dibuka
        loadData();

        // Submit Filter
        $('#form_filter_po').on('submit', function(e) {
            e.preventDefault();
            loadData();
        });

        // Reset Filter & Refresh Data (Tanpa reload seluruh page)
        $('#btn_reset_filter').on('click', function(e) {
            e.preventDefault();
            
            // Reset Select Status
            $('#filter_status').val('all');

            // Reset Select2 Supplier
            $('#filter_supplier').val('').trigger('change');

            // Reset Flatpickr Tanggal
            if (fp_awal) fp_awal.clear();
            if (fp_akhir) fp_akhir.clear();
            $('#filter_tgl_awal').val('');
            $('#filter_tgl_akhir').val('');

            // Panggil loadData dengan skeleton loading
            loadData();
        });

        // Klik No. PO untuk buka Modal Detail
        $(document).on('click', '.btn-detail', function(e) {
            e.preventDefault();
            var no_po = $(this).data('id');
            var no_surat = $(this).data('surat');

            $('#modalDetailLabel').html('<i class="ti ti-truck-delivery me-2"></i>Detail Kedatangan PO: <span class="badge bg-white text-primary ms-2">' + no_surat + '</span>');
            $('#modal_detail_po').modal('show');
            $('#modal_detail_content').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat data riwayat kedatangan...</p></div>');

            $.ajax({
                type: 'GET',
                url: "<?= site_url('report_monitoring_po/detail'); ?>/" + encodeURIComponent(no_po),
                success: function(html) {
                    $('#modal_detail_content').html(html);
                },
                error: function() {
                    $('#modal_detail_content').html('<div class="alert alert-danger mb-0">Gagal memuat detail riwayat kedatangan PO! Silakan coba lagi.</div>');
                }
            });
        });
    });
</script>
