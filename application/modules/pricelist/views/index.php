<?php
$ENABLE_ADD     = has_permission('Pricelist.Add');
$ENABLE_MANAGE  = has_permission('Pricelist.Manage');
$ENABLE_DELETE  = has_permission('Pricelist.Delete');

$active_tab = 'all';
if (!empty($status_filter)) {
    if ($status_filter === 'Draft') $active_tab = 'draft';
    elseif ($status_filter === 'Menunggu Approval') $active_tab = 'pending';
    elseif ($status_filter === 'Approved') $active_tab = 'approved';
    elseif ($status_filter === 'Rejected') $active_tab = 'rejected';
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

    .skeleton-line.short {
        width: 60%;
    }

    .skeleton-line.medium {
        width: 80%;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
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
                    <i class="fa fa-list-alt me-2"></i>Daftar Dokumen Pricelist
                </h5>
                <small class="text-muted">
                    Pengajuan dan pengelolaan Pricelist per Produk &times; Varian BOM. Menampilkan nama produk di depan dan status approval Management.
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('simulasi_costing') ?>" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fa fa-calculator me-1"></i> Buka Simulasi Costing (+ Draft)
                </a>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <ul class="nav nav-tabs card-header-tabs" id="plStatusTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link pl-tab-btn <?= ($active_tab === 'all') ? 'active' : '' ?>" id="tab-btn-all" data-bs-toggle="tab" data-bs-target="#tab-pane-all" data-tab="all" type="button" role="tab" aria-controls="tab-pane-all" aria-selected="<?= ($active_tab === 'all') ? 'true' : 'false' ?>">
                    <i class="fa fa-list me-1 text-primary"></i> Semua
                    <span class="badge rounded-pill bg-secondary ms-1" id="badge-count-all"><?= $counts['all'] ?? 0 ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link pl-tab-btn <?= ($active_tab === 'draft') ? 'active' : '' ?>" id="tab-btn-draft" data-bs-toggle="tab" data-bs-target="#tab-pane-draft" data-tab="draft" type="button" role="tab" aria-controls="tab-pane-draft" aria-selected="<?= ($active_tab === 'draft') ? 'true' : 'false' ?>">
                    <i class="fa fa-pencil-square-o me-1 text-secondary"></i> Draft
                    <span class="badge rounded-pill bg-secondary ms-1" id="badge-count-draft"><?= $counts['Draft'] ?? 0 ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link pl-tab-btn <?= ($active_tab === 'pending') ? 'active' : '' ?>" id="tab-btn-pending" data-bs-toggle="tab" data-bs-target="#tab-pane-pending" data-tab="pending" type="button" role="tab" aria-controls="tab-pane-pending" aria-selected="<?= ($active_tab === 'pending') ? 'true' : 'false' ?>">
                    <i class="fa fa-clock-o me-1 text-warning"></i> Menunggu Approval
                    <span class="badge rounded-pill bg-warning text-dark ms-1" id="badge-count-pending"><?= $counts['Menunggu Approval'] ?? 0 ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link pl-tab-btn <?= ($active_tab === 'approved') ? 'active' : '' ?>" id="tab-btn-approved" data-bs-toggle="tab" data-bs-target="#tab-pane-approved" data-tab="approved" type="button" role="tab" aria-controls="tab-pane-approved" aria-selected="<?= ($active_tab === 'approved') ? 'true' : 'false' ?>">
                    <i class="fa fa-check-circle me-1 text-success"></i> Approved
                    <span class="badge rounded-pill bg-success ms-1" id="badge-count-approved"><?= $counts['Approved'] ?? 0 ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link pl-tab-btn <?= ($active_tab === 'rejected') ? 'active' : '' ?>" id="tab-btn-rejected" data-bs-toggle="tab" data-bs-target="#tab-pane-rejected" data-tab="rejected" type="button" role="tab" aria-controls="tab-pane-rejected" aria-selected="<?= ($active_tab === 'rejected') ? 'true' : 'false' ?>">
                    <i class="fa fa-times-circle me-1 text-danger"></i> Rejected
                    <span class="badge rounded-pill bg-danger ms-1" id="badge-count-rejected"><?= $counts['Rejected'] ?? 0 ?></span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-3">
        <div class="tab-content" id="plStatusTabsContent">
            <!-- Wadah Tab Semua -->
            <div class="tab-pane <?= ($active_tab === 'all') ? 'show active' : '' ?>" id="tab-pane-all" role="tabpanel" aria-labelledby="tab-btn-all">
                <div id="skeleton-all" class="p-3">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-all" style="display:none;"></div>
            </div>

            <!-- Wadah Tab Draft -->
            <div class="tab-pane <?= ($active_tab === 'draft') ? 'show active' : '' ?>" id="tab-pane-draft" role="tabpanel" aria-labelledby="tab-btn-draft">
                <div id="skeleton-draft" class="p-3">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-draft" style="display:none;"></div>
            </div>

            <!-- Wadah Tab Menunggu Approval -->
            <div class="tab-pane <?= ($active_tab === 'pending') ? 'show active' : '' ?>" id="tab-pane-pending" role="tabpanel" aria-labelledby="tab-btn-pending">
                <div id="skeleton-pending" class="p-3">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-pending" style="display:none;"></div>
            </div>

            <!-- Wadah Tab Approved -->
            <div class="tab-pane <?= ($active_tab === 'approved') ? 'show active' : '' ?>" id="tab-pane-approved" role="tabpanel" aria-labelledby="tab-btn-approved">
                <div id="skeleton-approved" class="p-3">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-approved" style="display:none;"></div>
            </div>

            <!-- Wadah Tab Rejected -->
            <div class="tab-pane <?= ($active_tab === 'rejected') ? 'show active' : '' ?>" id="tab-pane-rejected" role="tabpanel" aria-labelledby="tab-btn-rejected">
                <div id="skeleton-rejected" class="p-3">
                    <div class="skeleton skeleton-line medium"></div>
                    <div class="skeleton skeleton-line"></div>
                    <div class="skeleton skeleton-line short"></div>
                </div>
                <div id="content-rejected" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal View Dokumen Pricelist -->
<div class="modal fade" id="modal-pl-doc" tabindex="-1" aria-labelledby="modalPlDocLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalPlDocLabel">
                    <i class="fa fa-list-alt me-2"></i>Dokumen Pricelist
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="pl-doc-body" style="max-height: 65vh; overflow: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">Memuat dokumen pricelist...</div>
                </div>
            </div>
            <div class="modal-footer bg-light" id="pl-doc-footer">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    const BASE_URL = '<?= base_url("pricelist") ?>';

    /** Render tabel via AJAX hanya saat tab aktif */
    function loadTab(tab) {
        const skeleton = $('#skeleton-' + tab);
        const content = $('#content-' + tab);

        if (content.data('loaded')) return;

        skeleton.show();
        content.hide();

        $.get(BASE_URL + '/render_tab/' + tab, function(html) {
            skeleton.hide();
            content.html(html).show();
            content.data('loaded', true);
        }).fail(function() {
            skeleton.hide();
            content.html('<div class="alert alert-danger my-3">Gagal memuat data tabel. Silakan refresh halaman.</div>').show();
        });
    }

    /** Reload tab tertentu (misal setelah submit / delete) */
    function reloadTab(tab) {
        $('#content-' + tab).data('loaded', false);
        loadTab(tab);
    }

    $(document).ready(function() {
        // Load tab awal yang sedang aktif saja!
        loadTab('<?= $active_tab ?>');

        // Tab switch handler: pindah seketika dan load tabel jika belum pernah dimuat
        $(document).on('click', '.pl-tab-btn', function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            var target = $(this).attr('data-bs-target');
            if (!target) return;

            $('.pl-tab-btn').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');

            $('#plStatusTabsContent > .tab-pane').removeClass('show active');
            $(target).addClass('show active');

            loadTab(tab);
        });

        // Tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(el) {
            return new bootstrap.Tooltip(el);
        });

        // View Dokumen
        $(document).on('click', '.btn-view-pl', function() {
            var id = $(this).data('id');
            $('#modalPlDocLabel').html('<i class="fa fa-list-alt me-2"></i>Dokumen Pricelist');
            $('#modal-pl-doc').modal('show');
            $('#pl-doc-body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Memuat dokumen pricelist...</div></div>');

            $.ajax({
                url: BASE_URL + '/view_doc',
                type: 'GET',
                dataType: 'json',
                data: {
                    id: id
                },
                success: function(resp) {
                    if (resp.status != 1) {
                        $('#pl-doc-body').html('<div class="alert alert-danger">' + resp.pesan + '</div>');
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

                    $('#modalPlDocLabel').html('<i class="fa fa-list-alt me-2"></i>Dokumen Pricelist — ' + h.no_pricelist);

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
                                    <small class="text-muted d-block">Status</small>
                                    <span class="badge ${badgeClass} fs-6">${h.status}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Dibuat Oleh / Tanggal</small>
                                    <strong>${h.created_by_name || 'Sales'}</strong>
                                    <div class="text-muted small">${h.created_at}</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Diajukan Pada</small>
                                    <strong>${h.submitted_at || '—'}</strong>
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

                    <h6 class="fw-bold text-dark mb-2"><i class="fa fa-cubes me-1"></i> Rincian Produk &times; Varian BOM (${details.length} Baris)</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle mb-0">
                            <thead class="text-dark">
                                <tr>
                                    <th width="40" class="text-center">#</th>
                                    <th>Produk Finished Goods</th>
                                    <th>Varian (Uniq ID)</th>
                                    <th>Category</th>
                                    <th class="text-end" width="130">Bottom Price/m</th>
                                    <th class="text-end" width="110">Extra Margin</th>
                                    <th class="text-end" width="140">Price List/m</th>
                                    <th class="text-end" width="140">Price List/kg</th>
                                    ${h.status === 'Draft' ? '<th width="50" class="text-center">Aksi</th>' : ''}
                                </tr>
                            </thead>
                            <tbody>
                `;

                    if (!details.length) {
                        html += `<tr><td colspan="${h.status === 'Draft' ? 9 : 8}" class="text-center text-muted py-3">Belum ada baris varian. Masukkan varian dari menu Simulasi Costing.</td></tr>`;
                    } else {
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
                                ${h.status === 'Draft' ? `
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger btn-del-item py-0 px-2" data-id="${d.id}" data-header="${h.id}" title="Hapus baris">
                                            &times;
                                        </button>
                                    </td>
                                ` : ''}
                            </tr>
                        `;
                        });
                    }

                    html += `
                            </tbody>
                        </table>
                    </div>
                `;

                    $('#pl-doc-body').html(html);

                    // Footer button
                    if (h.status === 'Draft' && details.length > 0) {
                        $('#pl-doc-footer').html(`
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-success px-4 btn-modal-submit" data-id="${h.id}" data-no="${h.no_pricelist}">
                            <i class="fa fa-paper-plane me-1"></i> Ajukan ke Management
                        </button>
                    `);
                    } else {
                        $('#pl-doc-footer').html(`<button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>`);
                    }
                },
                error: function(xhr) {
                    $('#pl-doc-body').html('<div class="alert alert-danger">Gagal memuat dokumen (Status: ' + xhr.status + ')</div>');
                }
            });
        });

        // Submit Draft Click
        $(document).on('click', '.btn-submit-pl, .btn-modal-submit', function() {
            var id = $(this).data('id');
            var no = $(this).data('no');

            Swal.fire({
                title: 'Ajukan ke Management?',
                text: 'Harga pada dokumen ' + no + ' akan dibekukan dan tidak dapat diubah lagi sampai diputuskan oleh Management.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Ajukan!',
                cancelButtonText: 'Batal'
            }).then(function(res) {
                if (!res.isConfirmed) return;

                $.ajax({
                    url: BASE_URL + '/submit',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: id
                    },
                    success: function(resp) {
                        if (resp.status == 1) {
                            $('#modal-pl-doc').modal('hide');
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
                            Swal.fire({
                                title: 'Gagal!',
                                text: resp.pesan,
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Request error: ' + xhr.status,
                            icon: 'error'
                        });
                    }
                });
            });
        });

        // Delete item from draft
        $(document).on('click', '.btn-del-item', function() {
            var detail_id = $(this).data('id');
            var header_id = $(this).data('header');

            $.ajax({
                url: BASE_URL + '/delete_item',
                type: 'POST',
                dataType: 'json',
                data: {
                    detail_id: detail_id
                },
                success: function(resp) {
                    if (resp.status == 1) {
                        $('.btn-view-pl[data-id="' + header_id + '"]').trigger('click');
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: resp.pesan,
                            icon: 'error'
                        });
                    }
                }
            });
        });

        // Delete whole draft
        $(document).on('click', '.btn-del-pl', function() {
            var id = $(this).data('id');
            var no = $(this).data('no');

            Swal.fire({
                title: 'Hapus Draft?',
                text: 'Draft Pricelist ' + no + ' beserta seluruh barisnya akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus Draft',
                cancelButtonText: 'Batal'
            }).then(function(res) {
                if (!res.isConfirmed) return;

                $.ajax({
                    url: BASE_URL + '/delete',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: id
                    },
                    success: function(resp) {
                        if (resp.status == 1) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: resp.pesan,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: resp.pesan,
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Request error: ' + xhr.status,
                            icon: 'error'
                        });
                    }
                });
            });
        });
    });
</script>