<?php
$ENABLE_MANAGE = has_permission('Approval_Pricelist.Manage');

$active_tab = 'pending';
if (!empty($status_filter)) {
    if ($status_filter === 'Menunggu Approval') $active_tab = 'pending';
    elseif ($status_filter === 'Approved') $active_tab = 'approved';
    elseif ($status_filter === 'Rejected') $active_tab = 'rejected';
    elseif ($status_filter === 'Semua') $active_tab = 'all';
}
?>

<style>
    .skeleton {
        border-radius: 4px;
        animation: shimmer 1.5s infinite linear;
        background: linear-gradient(90deg, #f2f2f2 25%, #e0e0e0 50%, #f2f2f2 75%);
        background-size: 200% 100%;
    }
    .skeleton-line {
        height: 20px;
        margin: 8px 0;
        border-radius: 4px;
    }
    .skeleton-line.short { width: 60%; }
    .skeleton-line.medium { width: 80%; }
    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .nav-tabs .nav-link {
        cursor: pointer;
        user-select: none;
        color: #495057;
        font-weight: 500;
        border: 1px solid transparent;
        border-top-left-radius: 0.35rem;
        border-top-right-radius: 0.35rem;
        padding: 0.6rem 1.1rem;
        transition: background-color 0.1s ease, color 0.1s ease;
    }
    .nav-tabs .nav-link * {
        pointer-events: none;
    }
    .nav-tabs .nav-link:hover {
        border-color: #e9ecef #e9ecef #dee2e6;
        color: #0d6efd;
        background-color: #f8f9fa;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd !important;
        background-color: #fff !important;
        border-color: #dee2e6 #dee2e6 #fff !important;
        font-weight: 700;
    }
</style>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1 text-primary fw-bold">
                    <i class="fa fa-check-circle me-2"></i>Approval Pricelist
                </h5>
                <small class="text-muted">
                    Management / GM memeriksa pengajuan dokumen Pricelist dari Sales. Menyetujui akan otomatis merilis harga ke <strong>Product Catalog</strong> dan mencatat riwayat ke <strong>History Price List</strong>.
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('pricelist') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="fa fa-list-alt me-1"></i> Buka Daftar Pricelist
                </a>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <ul class="nav nav-tabs card-header-tabs" id="appStatusTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link app-tab-btn <?= ($active_tab === 'pending') ? 'active' : '' ?>" id="tab-btn-app-pending" data-bs-toggle="tab" data-bs-target="#tab-pane-app-pending" data-tab="pending" type="button" role="tab" aria-controls="tab-pane-app-pending" aria-selected="<?= ($active_tab === 'pending') ? 'true' : 'false' ?>">
                    <i class="fa fa-clock-o me-1 text-warning"></i> Menunggu Approval
                    <span class="badge rounded-pill bg-warning text-dark ms-1" id="app-count-pending"><?= $counts['Menunggu Approval'] ?? 0 ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link app-tab-btn <?= ($active_tab === 'approved') ? 'active' : '' ?>" id="tab-btn-app-approved" data-bs-toggle="tab" data-bs-target="#tab-pane-app-approved" data-tab="approved" type="button" role="tab" aria-controls="tab-pane-app-approved" aria-selected="<?= ($active_tab === 'approved') ? 'true' : 'false' ?>">
                    <i class="fa fa-check-circle me-1 text-success"></i> Approved
                    <span class="badge rounded-pill bg-success ms-1" id="app-count-approved"><?= $counts['Approved'] ?? 0 ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link app-tab-btn <?= ($active_tab === 'rejected') ? 'active' : '' ?>" id="tab-btn-app-rejected" data-bs-toggle="tab" data-bs-target="#tab-pane-app-rejected" data-tab="rejected" type="button" role="tab" aria-controls="tab-pane-app-rejected" aria-selected="<?= ($active_tab === 'rejected') ? 'true' : 'false' ?>">
                    <i class="fa fa-times-circle me-1 text-danger"></i> Rejected
                    <span class="badge rounded-pill bg-danger ms-1" id="app-count-rejected"><?= $counts['Rejected'] ?? 0 ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link app-tab-btn <?= ($active_tab === 'all') ? 'active' : '' ?>" id="tab-btn-app-all" data-bs-toggle="tab" data-bs-target="#tab-pane-app-all" data-tab="all" type="button" role="tab" aria-controls="tab-pane-app-all" aria-selected="<?= ($active_tab === 'all') ? 'true' : 'false' ?>">
                    <i class="fa fa-list me-1 text-primary"></i> Semua Riwayat
                    <span class="badge rounded-pill bg-secondary ms-1" id="app-count-all"><?= $counts['all_approval'] ?? 0 ?></span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-3">
        <div class="tab-content" id="appStatusTabsContent">
            <!-- Wadah Tab Menunggu Approval -->
            <div class="tab-pane <?= ($active_tab === 'pending') ? 'show active' : '' ?>" id="tab-pane-app-pending" role="tabpanel" aria-labelledby="tab-btn-app-pending">
                <div id="skeleton-app-pending" class="p-3">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-app-pending" style="display:none;"></div>
            </div>

            <!-- Wadah Tab Approved -->
            <div class="tab-pane <?= ($active_tab === 'approved') ? 'show active' : '' ?>" id="tab-pane-app-approved" role="tabpanel" aria-labelledby="tab-btn-app-approved">
                <div id="skeleton-app-approved" class="p-3">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-app-approved" style="display:none;"></div>
            </div>

            <!-- Wadah Tab Rejected -->
            <div class="tab-pane <?= ($active_tab === 'rejected') ? 'show active' : '' ?>" id="tab-pane-app-rejected" role="tabpanel" aria-labelledby="tab-btn-app-rejected">
                <div id="skeleton-app-rejected" class="p-3">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-app-rejected" style="display:none;"></div>
            </div>

            <!-- Wadah Tab Semua -->
            <div class="tab-pane <?= ($active_tab === 'all') ? 'show active' : '' ?>" id="tab-pane-app-all" role="tabpanel" aria-labelledby="tab-btn-app-all">
                <div id="skeleton-app-all" class="p-3">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-app-all" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Periksa & Putuskan Dokumen Pricelist -->
<div class="modal fade" id="modal-app-doc" tabindex="-1" aria-labelledby="modalAppDocLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow border-0">
            <form id="form-decision" autocomplete="off">
                <input type="hidden" name="id" id="app_pl_id">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalAppDocLabel">
                        <i class="fa fa-gavel me-2"></i>Periksa Dokumen Pricelist
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="app-doc-body" style="max-height: 65vh; overflow: auto;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">Memuat dokumen pricelist...</div>
                    </div>
                </div>
                <div class="modal-footer bg-light" id="app-doc-footer">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    const BASE_URL = '<?= base_url("pricelist") ?>';

    /** Render tabel approval via AJAX hanya saat tab aktif */
    function loadAppTab(tab) {
        const skeleton = $('#skeleton-app-' + tab);
        const content  = $('#content-app-' + tab);

        if (content.data('loaded')) return;

        skeleton.show();
        content.hide();

        $.get(BASE_URL + '/render_app_tab/' + tab, function(html) {
            skeleton.hide();
            content.html(html).show();
            content.data('loaded', true);
        }).fail(function() {
            skeleton.hide();
            content.html('<div class="alert alert-danger my-3">Gagal memuat data tabel. Silakan refresh halaman.</div>').show();
        });
    }

    /** Reload tab tertentu jika dibutuhkan */
    function reloadAppTab(tab) {
        $('#content-app-' + tab).data('loaded', false);
        loadAppTab(tab);
    }

    $(document).ready(function() {
        // Load tab awal aktif pertama kali saja!
        loadAppTab('<?= $active_tab ?>');

        // Tab switch handler: pindah seketika dan load tabel jika belum pernah dimuat
        $(document).on('click', '.app-tab-btn', function(e) {
            e.preventDefault();
            var tab    = $(this).data('tab');
            var target = $(this).attr('data-bs-target');
            if (!target) return;

            $('.app-tab-btn').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');

            $('#appStatusTabsContent > .tab-pane').removeClass('show active');
            $(target).addClass('show active');

            loadAppTab(tab);
        });

        // Open Modal Periksa & Putuskan / View
        $(document).on('click', '.btn-decide-pl, .btn-view-app-pl', function() {
            var id = $(this).data('id');
            var isDecide = $(this).hasClass('btn-decide-pl');

            $('#app_pl_id').val(id);
            $('#modalAppDocLabel').html('<i class="fa fa-gavel me-2"></i>' + (isDecide ? 'Periksa & Putuskan Pricelist' : 'Detail Dokumen Pricelist'));
            $('#modal-app-doc').modal('show');
            $('#app-doc-body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Memuat dokumen pricelist...</div></div>');

            $.ajax({
                url: BASE_URL + '/view_doc',
                type: 'GET',
                dataType: 'json',
                data: { id: id },
                success: function(resp) {
                    if (resp.status != 1) {
                        $('#app-doc-body').html('<div class="alert alert-danger">' + resp.pesan + '</div>');
                        return;
                    }
                    var h = resp.data.header;
                    var details = resp.data.details || [];
                    var fn = function(v) {
                        return Number(v || 0).toLocaleString('id-ID', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    };

                    $('#modalAppDocLabel').html('<i class="fa fa-gavel me-2"></i>' + (isDecide ? 'Periksa Dokumen' : 'Detail Dokumen') + ' — ' + h.no_pricelist);

                    var badgeClass = h.status === 'Approved' ? 'bg-success' : (h.status === 'Rejected' ? 'bg-danger' : (h.status === 'Menunggu Approval' ? 'bg-warning text-dark' : 'bg-secondary'));

                    var html = `
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <small class="text-muted d-block">No. Pricelist</small>
                                    <strong class="fs-6 text-primary">${h.no_pricelist}</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Status Saat Ini</small>
                                    <span class="badge ${badgeClass} fs-6">${h.status}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Diajukan Oleh</small>
                                    <strong>${h.submitted_by_name || 'Sales'}</strong>
                                    <div class="text-muted small">${h.submitted_at || '—'}</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Jumlah Varian</small>
                                    <strong class="fs-6">${details.length} Varian BOM</strong>
                                </div>
                            </div>
                            ${h.reject_reason ? `
                                <div class="alert alert-danger mt-3 mb-0 py-2">
                                    <div class="fw-bold"><i class="fa fa-times-circle me-1"></i> Alasan Penolakan:</div>
                                    <div>${h.reject_reason}</div>
                                </div>
                            ` : ''}
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-2"><i class="fa fa-cubes me-1"></i> Rincian Produk &times; Varian BOM</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="40" class="text-center">#</th>
                                    <th>Produk Finished Goods</th>
                                    <th>Varian (Uniq ID)</th>
                                    <th>Category</th>
                                    <th class="text-end" width="130">Bottom Price/m</th>
                                    <th class="text-end" width="110">Extra Margin</th>
                                    <th class="text-end" width="140">Price List/m</th>
                                    <th class="text-end" width="140">Price List/kg</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                    details.forEach(function(d, k) {
                        html += `
                        <tr>
                            <td class="text-center">${k + 1}</td>
                            <td class="fw-bold">${d.nm_product}</td>
                            <td><span class="badge bg-light text-dark border">${d.no_uniq_id}</span></td>
                            <td>${d.nm_category}</td>
                            <td class="text-end">Rp ${fn(d.bottom_price_meter)}</td>
                            <td class="text-end fw-bold text-muted">${(Number(d.rate_negotiation_allowance) * 100).toFixed(2)}%</td>
                            <td class="text-end fw-bold text-primary">Rp ${fn(d.price_list_meter)}</td>
                            <td class="text-end fw-bold text-success">Rp ${fn(d.price_list_kg)}</td>
                        </tr>
                    `;
                    });

                    html += `
                            </tbody>
                        </table>
                    </div>
                `;

                    // If in decide mode, render decision controls
                    if (isDecide) {
                        html += `
                        <hr class="my-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa fa-check-circle text-primary me-1"></i> Keputusan Management / GM</h6>
                        <div class="mb-3">
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="decision" id="dec_app_yes" value="Approve" checked>
                                    <label class="form-check-label fw-bold text-success fs-6" for="dec_app_yes">
                                        <i class="fa fa-check-circle me-1"></i> Approve (Setujui Pricelist)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="decision" id="dec_app_no" value="Reject">
                                    <label class="form-check-label fw-bold text-danger fs-6" for="dec_app_no">
                                        <i class="fa fa-times-circle me-1"></i> Reject (Tolak Pricelist)
                                    </label>
                                </div>
                            </div>
                            <div class="form-text text-muted mt-2" id="app-decision-hint">
                                Approve: Harga akan dirilis ke Product Catalog dan menjadi harga aktif resmi perusahaan.
                            </div>
                        </div>

                        <div class="mb-3 d-none" id="group-app-reason">
                            <label class="form-label fw-bold text-danger">Alasan Penolakan (Reject) <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="reject_reason" id="app_reject_reason" rows="3" placeholder="Tuliskan alasan penolakan untuk Sales..."></textarea>
                            <div class="form-text text-danger">Alasan penolakan wajib diisi untuk panduan revisi oleh Sales.</div>
                        </div>
                    `;

                        $('#app-doc-footer').html(`
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4" id="btn-save-decision">
                            <i class="fa fa-save me-1"></i> Simpan Keputusan
                        </button>
                    `);
                    } else {
                        $('#app-doc-footer').html(`<button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>`);
                    }

                    $('#app-doc-body').html(html);
                },
                error: function(xhr) {
                    $('#app-doc-body').html('<div class="alert alert-danger">Gagal memuat dokumen (Status: ' + xhr.status + ')</div>');
                }
            });
        });

        // Decision Radio Change
        $(document).on('change', 'input[name="decision"]', function() {
            if ($(this).val() === 'Reject') {
                $('#group-app-reason').removeClass('d-none');
                $('#app_reject_reason').prop('required', true);
                $('#app-decision-hint').text('Reject: Pengajuan dikembalikan ke Sales dengan alasan penolakan.');
            } else {
                $('#group-app-reason').addClass('d-none');
                $('#app_reject_reason').prop('required', false);
                $('#app-decision-hint').text('Approve: Harga akan dirilis ke Product Catalog dan menjadi harga aktif resmi perusahaan.');
            }
        });

        // Form Decision Submit
        $('#form-decision').on('submit', function(e) {
            e.preventDefault();
            var id = $('#app_pl_id').val();
            var decision = $('input[name="decision"]:checked').val();
            var reason = $('#app_reject_reason').val();

            if (decision === 'Reject' && !reason.trim()) {
                Swal.fire({
                    title: 'Validasi',
                    text: 'Alasan penolakan (Reject) wajib diisi.',
                    icon: 'warning'
                });
                return;
            }

            var isApprove = (decision === 'Approve');
            var actionUrl = isApprove ? (BASE_URL + '/approve') : (BASE_URL + '/reject');

            Swal.fire({
                title: isApprove ? 'Setujui Pricelist Ini?' : 'Tolak Pricelist Ini?',
                text: isApprove ? 'Pricelist akan berstatus Approved dan otomatis dirilis ke Product Catalog.' : 'Pricelist akan ditolak dan dikembalikan ke Sales.',
                icon: isApprove ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: isApprove ? '#28a745' : '#dc3545',
                confirmButtonText: isApprove ? 'Ya, Setujui!' : 'Ya, Tolak!',
                cancelButtonText: 'Batal'
            }).then(function(res) {
                if (!res.isConfirmed) return;

                $('#btn-save-decision').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: id,
                        reject_reason: reason
                    },
                    success: function(resp) {
                        $('#btn-save-decision').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Keputusan');
                        if (resp.status == 1) {
                            $('#modal-app-doc').modal('hide');
                            Swal.fire({
                                title: 'Berhasil!',
                                text: resp.pesan,
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({ title: 'Gagal!', text: resp.pesan, icon: 'error' });
                        }
                    },
                    error: function(xhr) {
                        $('#btn-save-decision').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Keputusan');
                        Swal.fire({ title: 'Error!', text: 'Request error: ' + xhr.status, icon: 'error' });
                    }
                });
            });
        });
    });
</script>
