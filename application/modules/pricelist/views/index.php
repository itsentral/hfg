<?php
$ENABLE_ADD     = has_permission('Pricelist.Add');
$ENABLE_MANAGE  = has_permission('Pricelist.Manage');
$ENABLE_DELETE  = has_permission('Pricelist.Delete');
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
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

        <div class="row align-items-center g-2">
            <div class="col-md-4">
                <label class="form-label text-muted small mb-1 fw-bold">Filter Status Dokumen</label>
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="" <?= empty($status_filter) ? 'selected' : '' ?>>-- SEMUA STATUS --</option>
                    <option value="Draft" <?= ($status_filter === 'Draft') ? 'selected' : '' ?>>Draft</option>
                    <option value="Menunggu Approval" <?= ($status_filter === 'Menunggu Approval') ? 'selected' : '' ?>>Menunggu Approval</option>
                    <option value="Approved" <?= ($status_filter === 'Approved') ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= ($status_filter === 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-pricelist" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="40">#</th>
                        <th>Produk (Varian Utama)</th>
                        <th width="140">No. Pricelist</th>
                        <th class="text-center" width="90">Jumlah Baris</th>
                        <th width="150">Dibuat Pada</th>
                        <th class="text-center" width="160">Status</th>
                        <th width="180">Keputusan Management</th>
                        <th class="text-center no-sort" width="160">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($list)): ?>
                        <?php foreach ($list as $idx => $r): ?>
                            <?php 
                                $is_draft    = ($r->status === 'Draft');
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
                                <td class="text-muted small">
                                    <?= date('d/m/Y H:i', strtotime($r->created_at)) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_draft): ?>
                                        <span class="badge bg-secondary px-2 py-1">Draft</span>
                                    <?php elseif ($is_pending): ?>
                                        <span class="badge bg-warning text-dark border border-warning px-2 py-1">
                                            <i class="fa fa-clock-o me-1"></i> Menunggu Approval
                                        </span>
                                    <?php elseif ($is_approved): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="fa fa-check-circle me-1"></i> Approved
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1" data-bs-toggle="tooltip" title="<?= htmlspecialchars($r->reject_reason ?: '') ?>">
                                            <i class="fa fa-times-circle me-1"></i> Rejected
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($is_approved): ?>
                                        <div class="text-success small fw-bold"><i class="fa fa-check me-1"></i> Disetujui</div>
                                        <div class="text-muted small"><?= htmlspecialchars($r->decided_by_name ?: 'GM') ?> · <?= date('d/m/Y H:i', strtotime($r->decided_at)) ?></div>
                                    <?php elseif ($is_rejected): ?>
                                        <div class="text-danger small fw-bold"><i class="fa fa-times me-1"></i> Ditolak</div>
                                        <div class="text-danger small"><?= htmlspecialchars($r->reject_reason ?: '-') ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info text-white btn-view-pl shadow-sm"
                                        data-id="<?= $r->id ?>"
                                        title="View Dokumen Pricelist">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    <?php if ($is_draft && $ENABLE_MANAGE): ?>
                                        <button type="button" class="btn btn-sm btn-success btn-submit-pl shadow-sm ms-1"
                                            data-id="<?= $r->id ?>"
                                            data-no="<?= $r->no_pricelist ?>"
                                            title="Ajukan ke Management">
                                            <i class="fa fa-paper-plane"></i> Ajukan
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($is_draft && $ENABLE_DELETE): ?>
                                        <button type="button" class="btn btn-sm btn-danger btn-del-pl shadow-sm ms-1"
                                            data-id="<?= $r->id ?>"
                                            data-no="<?= $r->no_pricelist ?>"
                                            title="Hapus Draft">
                                            <i class="fa fa-trash"></i>
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
            <div class="modal-body p-4" id="pl-doc-body">
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
$(document).ready(function() {
    $('#tbl-pricelist').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [{ targets: 'no-sort', orderable: false }]
    });

    // Tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });

    // Filter status
    $('#filter-status').on('change', function() {
        var s = $(this).val();
        window.location.href = '<?= base_url('pricelist') ?>' + (s ? '?status=' + encodeURIComponent(s) : '');
    });

    // View Dokumen
    $(document).on('click', '.btn-view-pl', function() {
        var id = $(this).data('id');
        $('#modalPlDocLabel').html('<i class="fa fa-list-alt me-2"></i>Dokumen Pricelist');
        $('#modal-pl-doc').modal('show');
        $('#pl-doc-body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Memuat dokumen pricelist...</div></div>');

        $.ajax({
            url: '<?= base_url('pricelist/view_doc') ?>',
            type: 'GET',
            dataType: 'json',
            data: { id: id },
            success: function(resp) {
                if (resp.status != 1) {
                    $('#pl-doc-body').html('<div class="alert alert-danger">' + resp.pesan + '</div>');
                    return;
                }
                var h = resp.data.header;
                var details = resp.data.details || [];
                var fn = function(v) { return Number(v || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

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
                url: '<?= base_url('pricelist/submit') ?>',
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(resp) {
                    if (resp.status == 1) {
                        $('#modal-pl-doc').modal('hide');
                        Swal.fire({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1800, showConfirmButton: false })
                            .then(function() { window.location.reload(); });
                    } else {
                        Swal.fire({ title: 'Gagal!', text: resp.pesan, icon: 'error' });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ title: 'Error!', text: 'Request error: ' + xhr.status, icon: 'error' });
                }
            });
        });
    });

    // Delete item from draft
    $(document).on('click', '.btn-del-item', function() {
        var detail_id = $(this).data('id');
        var header_id = $(this).data('header');

        $.ajax({
            url: '<?= base_url('pricelist/delete_item') ?>',
            type: 'POST',
            dataType: 'json',
            data: { detail_id: detail_id },
            success: function(resp) {
                if (resp.status == 1) {
                    // Refresh modal
                    $('.btn-view-pl[data-id="' + header_id + '"]').trigger('click');
                } else {
                    Swal.fire({ title: 'Gagal!', text: resp.pesan, icon: 'error' });
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
                url: '<?= base_url('pricelist/delete') ?>',
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(resp) {
                    if (resp.status == 1) {
                        Swal.fire({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1500, showConfirmButton: false })
                            .then(function() { window.location.reload(); });
                    } else {
                        Swal.fire({ title: 'Gagal!', text: resp.pesan, icon: 'error' });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ title: 'Error!', text: 'Request error: ' + xhr.status, icon: 'error' });
                }
            });
        });
    });
});
</script>
