<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">

<style>
    .card-report {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .btn-download-excel {
        border-radius: 6px;
        height: 35px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
    }

    .currency-badge {
        display: inline-block;
        background-color: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        padding: 5px 10px;
        font-family: monospace;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
        line-height: 1.2;
        white-space: nowrap;
    }

    /* Table styling */
    .table-header-po {
        font-size: 13.5px;
    }

    .table-header-po thead th {
        vertical-align: middle;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        background-color: #f1f5f9;
        color: #334155;
    }

    .btn-detail-po {
        cursor: pointer;
        text-decoration: none;
        font-weight: 600;
        color: #1e40af;
        transition: color 0.15s ease-in-out;
    }

    .btn-detail-po:hover {
        color: #2563eb;
        text-decoration: underline;
    }

    /* DataTables UI styling tweak */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 4px 10px;
        font-size: 13px;
        margin-left: 6px;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 4px 8px;
        font-size: 13px;
        margin: 0 4px;
    }
</style>

<div class="card card-report">
    <div class="card-body p-4">
        <!-- Action Bar: Tombol Download Excel -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fa fa-credit-card text-primary me-2"></i>Report Payment PO
                </h5>
                <small class="text-muted">Monitoring status pembayaran dan outstanding per Purchase Order</small>
            </div>
            <div>
                <a href="<?= site_url('report_payment_po/download_excel'); ?>" class="btn btn-success btn-sm btn-download-excel shadow-sm" id="btn-download">
                    <i class="fa fa-file-excel-o"></i> Download Excel
                </a>
            </div>
        </div>

        <!-- Tabel Utama Header Report Payment PO dengan DataTables -->
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered table-header-po align-middle w-100 mb-0" id="table_header_payment_po">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th style="width: 170px;">No. PO</th>
                        <th>Supplier</th>
                        <th class="text-center" style="width: 100px;">Currency</th>
                        <th class="text-end" style="width: 150px;">Nilai PO</th>
                        <th class="text-end" style="width: 150px;">PO Terbayar</th>
                        <th class="text-end" style="width: 150px;">Outstanding PO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($headers)) : ?>
                        <?php $no = 0;
                        foreach ($headers as $row) : $no++; ?>
                            <tr>
                                <td class="text-center text-muted"><?= $no; ?></td>
                                <td>
                                    <!-- No. PO klikable membuka Modal Detail -->
                                    <a href="javascript:void(0)"
                                        class="btn-detail-po"
                                        data-no-po="<?= htmlspecialchars($row->no_po, ENT_QUOTES, 'UTF-8'); ?>"
                                        title="Klik untuk melihat rincian transaksi pembayaran"
                                        style="text-decoration: underline;">
                                        <i class="fa fa-file-text-o me-1"></i><?= htmlspecialchars($row->no_po, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($row->nm_supplier ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge currency-badge">
                                        <?= htmlspecialchars($row->currency ?? 'IDR', ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    <?= number_format($row->nilai_po, 2); ?>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    <?= number_format($row->po_terbayar, 2); ?>
                                </td>
                                <td class="text-end fw-bold <?= ($row->outstanding_po > 0) ? 'text-danger' : 'text-primary'; ?>">
                                    <?= number_format($row->outstanding_po, 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Dialog XL untuk Detail Transaksi Payment PO -->
<div class="modal fade" id="modal_detail_payment" tabindex="-1" aria-labelledby="modalPaymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title text-white fw-bold" id="modalPaymentLabel">
                    <i class="fa fa-file-text-o me-2"></i>Detail Transaksi Payment PO
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modal_detail_content" style="max-height: 75vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memuat rincian transaksi pembayaran...</p>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTable pada Tabel Utama
        if ($('#table_header_payment_po').length) {
            if ($.fn.DataTable.isDataTable('#table_header_payment_po')) {
                $('#table_header_payment_po').DataTable().destroy();
            }

            $('#table_header_payment_po').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [1, 'asc']
                ],
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                    searchable: false
                }],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ PO",
                    infoEmpty: "Menampilkan 0 data",
                    zeroRecords: "Tidak ada data PO yang sesuai",
                    paginate: {
                        previous: "Prev",
                        next: "Next"
                    }
                }
            });
        }

        // Tombol Download Excel konfirmasi
        $('#btn-download').on('click', function() {
            Swal.fire({
                title: 'Downloading...',
                text: 'Harap tunggu, sistem sedang memproses laporan Excel Anda.',
                icon: 'info',
                showConfirmButton: false,
                timer: 3000
            });
        });

        // Klik No. PO untuk buka Modal Detail Transaksi
        $(document).on('click', '.btn-detail-po', function(e) {
            e.preventDefault();
            var no_po = $(this).data('no-po');

            $('#modalPaymentLabel').html('<i class="fa fa-file-text-o me-2"></i>Detail Payment PO: <span class="badge bg-white text-primary ms-2">' + no_po + '</span>');
            $('#modal_detail_payment').modal('show');
            $('#modal_detail_content').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat rincian transaksi pembayaran...</p></div>');

            $.ajax({
                type: 'POST',
                url: "<?= site_url('report_payment_po/detail'); ?>",
                data: {
                    no_po: no_po
                },
                success: function(html) {
                    $('#modal_detail_content').html(html);
                },
                error: function() {
                    $('#modal_detail_content').html('<div class="alert alert-danger mb-0">Gagal memuat detail transaksi payment PO! Silakan coba lagi.</div>');
                }
            });
        });
    });
</script>