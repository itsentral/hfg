<?php
$ENABLE_MANAGE = has_permission('Approval_Pricelist.Manage');
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
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

        <div class="row align-items-center g-2">
            <div class="col-md-4">
                <label class="form-label text-muted small mb-1 fw-bold">Filter Status Pengajuan</label>
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="" <?= empty($status_filter) ? 'selected' : '' ?>>-- SEMUA STATUS --</option>
                    <option value="Menunggu Approval" <?= ($status_filter === 'Menunggu Approval') ? 'selected' : '' ?>>Menunggu Approval</option>
                    <option value="Approved" <?= ($status_filter === 'Approved') ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= ($status_filter === 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-approval-pl" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="40">#</th>
                        <th>Produk (Varian Utama)</th>
                        <th width="140">No. Pricelist</th>
                        <th class="text-center" width="90">Jumlah Baris</th>
                        <th width="170">Diajukan Oleh / Waktu</th>
                        <th class="text-center" width="160">Status</th>
                        <th width="180">Diputuskan Oleh / Waktu</th>
                        <th>Alasan Reject</th>
                        <th class="text-center no-sort" width="160">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($list)): ?>
                        <?php foreach ($list as $idx => $r): ?>
                            <?php 
                                $is_pending  = ($r->status === 'Menunggu Approval');
                                $is_approved = ($r->status === 'Approved');
                                $is_rejected = ($r->status === 'Rejected');

                                $prods = array_filter(array_map('trim', explode(',', $r->product_names)));
                                $first_prod = !empty($prods) ? $prods[0] : '(Belum ada produk)';
                                $more_count = count($prods) - 1;
                            ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark fs-6"><?= htmlspecialchars(strtoupper($first_prod)) ?></strong>
                                    <?php if ($more_count > 0): ?>
                                        <span class="badge bg-secondary ms-1">+<?= $more_count ?> produk</span>
                                    <?php endif; ?>
                                    <div class="text-muted small mt-1">
                                        Varian: <?= htmlspecialchars($r->uniq_ids ?: '-') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-bold"><?= htmlspecialchars($r->no_pricelist) ?></span>
                                </td>
                                <td class="text-center fw-bold">
                                    <?= $r->total_items ?> varian
                                </td>
                                <td>
                                    <?php if (!empty($r->submitted_at)): ?>
                                        <strong><?= htmlspecialchars($r->submitted_by_name ?: 'Sales') ?></strong>
                                        <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($r->submitted_at)) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_pending): ?>
                                        <span class="badge bg-warning text-dark border border-warning px-2 py-1">
                                            <i class="fa fa-clock-o me-1"></i> Menunggu Approval
                                        </span>
                                    <?php elseif ($is_approved): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="fa fa-check-circle me-1"></i> Approved
                                        </span>
                                    <?php elseif ($is_rejected): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                            <i class="fa fa-times-circle me-1"></i> Rejected
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-2 py-1">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r->decided_at)): ?>
                                        <strong><?= htmlspecialchars($r->decided_by_name ?: 'Management') ?></strong>
                                        <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($r->decided_at)) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r->reject_reason)): ?>
                                        <span class="text-danger small"><i class="fa fa-info-circle me-1"></i> <?= htmlspecialchars($r->reject_reason) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_pending && $ENABLE_MANAGE): ?>
                                        <button type="button" class="btn btn-sm btn-primary btn-decide-pl shadow-sm"
                                            data-id="<?= $r->id ?>"
                                            data-no="<?= $r->no_pricelist ?>"
                                            title="Periksa & Putuskan">
                                            <i class="fa fa-gavel me-1"></i> Putuskan
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-view-app-pl"
                                            data-id="<?= $r->id ?>"
                                            title="View Detail Dokumen">
                                            <i class="fa fa-eye"></i> View
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
                <div class="modal-body p-4" id="app-doc-body">
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
$(document).ready(function() {
    $('#tbl-approval-pl').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [{ targets: 'no-sort', orderable: false }]
    });

    // Filter status
    $('#filter-status').on('change', function() {
        var s = $(this).val();
        window.location.href = '<?= base_url('pricelist/approval') ?>' + (s ? '?status=' + encodeURIComponent(s) : '');
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
            url: '<?= base_url('pricelist/view_doc') ?>',
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
                var fn = function(v) { return Number(v || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

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
                            <thead class="table-primary text-dark">
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
        var id       = $('#app_pl_id').val();
        var decision = $('input[name="decision"]:checked').val();
        var reason   = $('#app_reject_reason').val();

        if (decision === 'Reject' && !reason.trim()) {
            Swal.fire({ title: 'Validasi', text: 'Alasan penolakan (Reject) wajib diisi.', icon: 'warning' });
            return;
        }

        var isApprove = (decision === 'Approve');
        var actionUrl = isApprove ? '<?= base_url('pricelist/approve') ?>' : '<?= base_url('pricelist/reject') ?>';

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
                        Swal.fire({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1800, showConfirmButton: false })
                            .then(function() { window.location.reload(); });
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
