<?php
$ENABLE_VIEW   = has_permission('PR_Asset.View');
$ENABLE_ADD    = has_permission('PR_Asset.Add');
$ENABLE_MANAGE = has_permission('PR_Asset.Manage');
$ENABLE_DELETE = has_permission('PR_Asset.Delete');
?>

<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="card-title fw-bold mb-0 text-primary">
                    <i class="fa fa-check-square-o me-2"></i><?= isset($title) ? $title : 'Approval PR Asset by Management'; ?>
                </h5>
                <small class="text-muted">Persetujuan Purchase Request Asset untuk level Management</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary px-3 py-2 rounded-pill">
                    <i class="fa fa-shield me-1"></i>Management Review
                </span>
                <?php if ($ENABLE_ADD && (!isset($tanda) || $tanda == '')): ?>
                    <a href="<?= site_url('pr_asset/add_pr'); ?>" class="btn btn-sm btn-success">
                        <i class="fa fa-plus me-1"></i> Add PR
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body p-4">
            <input type="hidden" id="tanda" value="<?= isset($tanda) ? htmlspecialchars($tanda) : 'approval_management'; ?>">

            <!-- Status Filter Tabs -->
            <ul class="nav nav-tabs mb-4" id="prStatusTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold status-tab" data-status="N" type="button">
                        <i class="fa fa-clock-o me-1 text-warning"></i> Open / Waiting Approval
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold status-tab" data-status="Y" type="button">
                        <i class="fa fa-check-circle me-1 text-success"></i> Approved
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold status-tab" data-status="D" type="button">
                        <i class="fa fa-times-circle me-1 text-danger"></i> Rejected
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold status-tab" data-status="ALL" type="button">
                        <i class="fa fa-list me-1 text-primary"></i> All Status
                    </button>
                </li>
            </ul>

            <div class="table-responsive">
                <table class="table table-hover table-striped border align-middle w-100" id="my-grid">
                    <thead class="table-primary text-center">
                        <tr>
                            <th width="4%">#</th>
                            <th width="14%">No PR</th>
                            <th width="12%">Tanggal PR</th>
                            <th>Nama Barang</th>
                            <th width="12%">PR By</th>
                            <th width="12%">PR Date</th>
                            <th width="14%">Status</th>
                            <th width="12%" class="no-sort">Option</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal View Detail PR Asset -->
<div class="modal fade" id="ModalView2" tabindex="-1" aria-labelledby="head_title2" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="head_title2">
                    <i class="fa fa-info-circle me-2"></i>Detail PR Asset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="view2">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var currentStatus = 'N';

    $(document).ready(function() {
        var tanda = $('#tanda').val() || 'approval_management';
        DataTables(tanda, currentStatus);

        // Status Tabs Click
        $(document).on('click', '.status-tab', function(e) {
            e.preventDefault();
            $('.status-tab').removeClass('active');
            $(this).addClass('active');
            currentStatus = $(this).data('status');
            DataTables(tanda, currentStatus);
        });

        // Expand / Collapse Child Row
        $(document).on('click', '.look_hide', function(e) {
            e.preventDefault();
            var idOfParent = $(this).data('id');
            $('.child-' + idOfParent).toggle('slow');
        });

        // Modal View Detail PR
        $(document).on('click', '.view_modal_pr', function(e) {
            e.preventDefault();
            var no_pr = $(this).data('no_pr');
            $('#head_title2').html('<i class="fa fa-file-text-o me-2"></i>Detail PR Asset [' + no_pr + ']');
            $('#view2').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            
            var modalEl = document.getElementById('ModalView2');
            var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();

            $.ajax({
                type: 'POST',
                url: base_url + active_controller + 'view_pr/' + encodeURIComponent(no_pr),
                success: function(data) {
                    $('#view2').html(data);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat detail PR Asset. Silakan coba kembali.'
                    });
                }
            });
        });

        // Print PR
        $(document).on('click', '.print_pr', function(e) {
            e.preventDefault();
            var no_pr = $(this).data('no_pr');
            var link = base_url + active_controller + 'print_pr_asset/' + encodeURIComponent(no_pr);
            window.open(link, '_blank');
        });

        // Approve / Reject Action
        $(document).on('click', '.approve', function(e) {
            e.preventDefault();
            var nomor = $(this).data('id');
            var tipe_approve = $(this).data('tipe_approve') || 'approval_management';
            var no_pr = ($('#no_pr_' + nomor).val() || '').trim();
            var action = ($('#action_' + nomor).val() || 'Y').trim();
            var reason = ($('#reason_' + nomor).val() || '').trim();

            if (action === 'D' && reason === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Alasan penolakan (Reason Reject) wajib diisi!'
                });
                return false;
            }

            var actionLabel = (action === 'Y') ? 'Approve' : 'Reject';
            var confirmBtnColor = (action === 'Y') ? '#198754' : '#dc3545';

            Swal.fire({
                title: 'Konfirmasi ' + actionLabel + '?',
                text: 'Apakah Anda yakin ingin memproses ' + actionLabel + ' untuk No. PR ' + no_pr + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmBtnColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-check me-1"></i> Ya, Proses!',
                cancelButtonText: '<i class="fa fa-times me-1"></i> Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses data...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: base_url + active_controller + 'approve_pr',
                        type: 'POST',
                        data: {
                            no_pr: no_pr,
                            action: action,
                            reason: reason,
                            tipe_approve: tipe_approve
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.status == 1) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.pesan,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                DataTables(tanda, currentStatus);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: data.pesan || 'Proses gagal dilakukan.'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan sistem saat memproses approval. Silakan coba kembali.'
                            });
                        }
                    });
                }
            });
        });
    });

    function DataTables(tanda, status_filter) {
        if (!status_filter) status_filter = 'N';
        if (!tanda) tanda = 'approval_management';

        $('#my-grid').DataTable({
            serverSide: true,
            stateSave: true,
            bAutoWidth: true,
            destroy: true,
            processing: true,
            responsive: true,
            aaSorting: [[1, 'desc']],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            iDisplayLength: 10,
            aLengthMenu: [
                [10, 20, 50, 100],
                [10, 20, 50, 100]
            ],
            ajax: {
                url: base_url + active_controller + 'server_side_pr_asset',
                type: 'POST',
                data: function(d) {
                    d.tanda = tanda;
                    d.status_filter = status_filter;
                },
                cache: false,
                error: function() {
                    $('#my-grid').append('<tbody class="my-grid-error"><tr><th colspan="8" class="text-center text-danger py-3">Tidak ada data ditemukan pada server</th></tr></tbody>');
                }
            }
        });
    }
</script>
