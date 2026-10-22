<?php
$ENABLE_MANAGE = has_permission('Approval_Extra_Margin.Manage');
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1 text-primary fw-bold">
                <i class="fa fa-check-square-o me-2"></i>Approval Extra Margin (Negotiation Allowance)
            </h5>
            <small class="text-muted">
                GM memeriksa pengajuan perubahan Extra Margin dari Sales Manager. Menyetujui akan langsung memperbarui rate aktif; Menolak wajib disertai alasan.
            </small>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge <?= $pending_count > 0 ? 'bg-warning text-dark border border-warning' : 'bg-light text-muted border' ?> px-3 py-2 me-2">
                <?= $pending_count > 0 ? $pending_count . ' Pengajuan Menunggu Keputusan' : 'Tidak ada pengajuan pending' ?>
            </span>
            <a href="<?= base_url('rate_negotiation_allowance') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa fa-tag me-1"></i> Lihat Rate Aktif
            </a>
        </div>
    </div>
    <div class="card-body p-3">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle me-1"></i> <?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Filter Status Bar -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-3">
                <label class="form-label text-muted small mb-1 fw-bold">Filter Status</label>
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="" <?= empty($status_filter) ? 'selected' : '' ?>>-- SEMUA STATUS --</option>
                    <option value="Menunggu Approval GM" <?= ($status_filter === 'Menunggu Approval GM') ? 'selected' : '' ?>>Menunggu Approval GM</option>
                    <option value="Approved" <?= ($status_filter === 'Approved') ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= ($status_filter === 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-approval" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>Product Category</th>
                        <th class="text-end" width="120">Rate Aktif</th>
                        <th class="text-end" width="130">Rate Diajukan</th>
                        <th class="text-end" width="120">Selisih</th>
                        <th width="180">Diajukan Oleh / Pada</th>
                        <th class="text-center" width="160">Status</th>
                        <th width="180">Diputuskan Oleh / Pada</th>
                        <th>Alasan Reject</th>
                        <th class="text-center no-sort" width="140">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $idx => $r): ?>
                            <?php 
                                $is_pending = ($r->status === 'Menunggu Approval GM');
                                $act_val    = ($r->active_rate !== null) ? (float)($r->active_rate * 100) : null;
                                $req_val    = (float)($r->rate_negotiation_allowance * 100);
                                $diff       = ($act_val !== null) ? ($req_val - $act_val) : null;
                            ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?></td>
                                <td class="text-end">
                                    <?= ($act_val !== null) ? number_format($act_val, 2, ',', '.') . ' %' : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-end fw-bold text-primary fs-6">
                                    <?= number_format($req_val, 2, ',', '.') ?> %
                                </td>
                                <td class="text-end fw-bold">
                                    <?php if ($diff !== null): ?>
                                        <span class="<?= $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted') ?>">
                                            <?= ($diff > 0 ? '+' : '') . number_format($diff, 2, ',', '.') ?> poin %
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">— (pertama)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($r->submitted_by_name ?: 'Sales') ?></strong>
                                    <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($r->submitted_at)) ?></div>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_pending): ?>
                                        <span class="badge bg-warning text-dark border border-warning px-2 py-1">
                                            <i class="fa fa-clock-o me-1"></i> Menunggu GM
                                        </span>
                                    <?php elseif ($r->status === 'Approved'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="fa fa-check-circle me-1"></i> Approved
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                            <i class="fa fa-times-circle me-1"></i> Rejected
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r->decided_at)): ?>
                                        <strong><?= htmlspecialchars($r->decided_by_name ?: 'GM') ?></strong>
                                        <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($r->decided_at)) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r->reject_reason)): ?>
                                        <span class="text-danger small"><i class="fa fa-info-circle me-1"></i> <?= htmlspecialchars($r->reject_reason) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_pending && $ENABLE_MANAGE): ?>
                                        <button type="button" class="btn btn-sm btn-primary btn-decide shadow-sm"
                                            data-id="<?= $r->id ?>"
                                            data-category="<?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?>"
                                            data-active="<?= ($act_val !== null) ? number_format($act_val, 2, ',', '.') . ' %' : '—' ?>"
                                            data-req="<?= number_format($req_val, 2, ',', '.') ?> %"
                                            data-diff="<?= ($diff !== null) ? (($diff > 0 ? '+' : '') . number_format($diff, 2, ',', '.') . ' poin %') : '—' ?>"
                                            data-by="<?= htmlspecialchars($r->submitted_by_name ?: 'Sales') ?>"
                                            data-at="<?= date('d/m/Y H:i', strtotime($r->submitted_at)) ?>"
                                            title="Periksa & Putuskan">
                                            <i class="fa fa-gavel me-1"></i> Keputusan
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-view-decide"
                                            data-category="<?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?>"
                                            data-active="<?= ($act_val !== null) ? number_format($act_val, 2, ',', '.') . ' %' : '—' ?>"
                                            data-req="<?= number_format($req_val, 2, ',', '.') ?> %"
                                            data-diff="<?= ($diff !== null) ? (($diff > 0 ? '+' : '') . number_format($diff, 2, ',', '.') . ' poin %') : '—' ?>"
                                            data-by="<?= htmlspecialchars($r->submitted_by_name ?: 'Sales') ?>"
                                            data-at="<?= date('d/m/Y H:i', strtotime($r->submitted_at)) ?>"
                                            data-status="<?= $r->status ?>"
                                            data-decided-by="<?= htmlspecialchars($r->decided_by_name ?: 'GM') ?>"
                                            data-decided-at="<?= !empty($r->decided_at) ? date('d/m/Y H:i', strtotime($r->decided_at)) : '-' ?>"
                                            data-reason="<?= htmlspecialchars($r->reject_reason ?: '-') ?>"
                                            title="View Detail">
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

<!-- Modal Keputusan GM (Approve / Reject) -->
<div class="modal fade" id="modal-decide" tabindex="-1" aria-labelledby="modalDecideLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form id="form-decide" autocomplete="off">
                <input type="hidden" name="id" id="decide_id">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalDecideLabel">
                        <i class="fa fa-gavel me-2"></i>Periksa Pengajuan Extra Margin
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Ringkasan Pengajuan -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Product Category</label>
                                    <div class="fw-bold fs-6 text-dark" id="dec_cat"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">Diajukan Oleh / Waktu</label>
                                    <div class="fw-bold text-dark" id="dec_by_at"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small mb-1">Rate Extra Margin Aktif</label>
                                    <div class="fw-bold text-dark fs-6" id="dec_active"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small mb-1">Rate Yang Diajukan</label>
                                    <div class="fw-bold text-primary fs-5" id="dec_req"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small mb-1">Selisih Perubahan</label>
                                    <div class="fw-bold text-success fs-6" id="dec_diff"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-3"><i class="fa fa-check-circle text-primary me-1"></i> Keputusan General Manager</h6>

                    <div class="mb-3">
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="decision" id="dec_approve" value="Approve" checked>
                                <label class="form-check-label fw-bold text-success fs-6" for="dec_approve">
                                    <i class="fa fa-check-circle me-1"></i> Approve (Setujui)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="decision" id="dec_reject" value="Reject">
                                <label class="form-check-label fw-bold text-danger fs-6" for="dec_reject">
                                    <i class="fa fa-times-circle me-1"></i> Reject (Tolak)
                                </label>
                            </div>
                        </div>
                        <div class="form-text text-muted mt-2" id="decision-hint">
                            Approve: Rate aktif kategori ini akan otomatis diperbarui menjadi rate diajukan.
                        </div>
                    </div>

                    <div class="mb-3 d-none" id="group-reject-reason">
                        <label class="form-label fw-bold text-danger">Alasan Penolakan (Reject) <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reject_reason" id="dec_reason" rows="3" placeholder="Tuliskan alasan penolakan untuk Sales Manager..."></textarea>
                        <div class="form-text text-danger">Alasan penolakan wajib diisi untuk panduan Sales Manager.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4" id="btn-submit-decision">
                        <i class="fa fa-save me-1"></i> Simpan Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Detail -->
<div class="modal fade" id="modal-view" tabindex="-1" aria-labelledby="modalViewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalViewLabel"><i class="fa fa-info-circle me-2"></i>Detail Pengajuan Extra Margin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-muted small mb-1">Product Category</label>
                    <div class="fw-bold fs-6" id="v_cat"></div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="text-muted small mb-1">Rate Aktif Sebelumnya</label>
                        <div class="fw-bold text-dark" id="v_active"></div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small mb-1">Rate Yang Diajukan</label>
                        <div class="fw-bold text-primary" id="v_req"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small mb-1">Diajukan Oleh / Waktu</label>
                    <div class="fw-bold" id="v_by_at"></div>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="text-muted small mb-1">Status Keputusan</label>
                    <div id="v_status"></div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small mb-1">Diputuskan Oleh / Waktu</label>
                    <div class="fw-bold" id="v_dec_by_at"></div>
                </div>
                <div class="mb-2" id="v_group_reason">
                    <label class="text-muted small mb-1">Alasan Penolakan</label>
                    <div class="alert alert-danger mb-0" id="v_reason"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function() {
    $('#tbl-approval').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [{ targets: 'no-sort', orderable: false }]
    });

    // Filter status change
    $('#filter-status').on('change', function() {
        var st = $(this).val();
        window.location.href = '<?= base_url('rate_negotiation_allowance/approval') ?>' + (st ? '?status=' + encodeURIComponent(st) : '');
    });

    // Radio Decision Change
    $('input[name="decision"]').on('change', function() {
        if ($(this).val() === 'Reject') {
            $('#group-reject-reason').removeClass('d-none');
            $('#dec_reason').prop('required', true);
            $('#decision-hint').text('Reject: Pengajuan ditolak dan kembali ke Sales Manager dengan alasan yang diberikan.');
        } else {
            $('#group-reject-reason').addClass('d-none');
            $('#dec_reason').prop('required', false);
            $('#decision-hint').text('Approve: Rate aktif kategori ini akan otomatis diperbarui menjadi rate diajukan.');
        }
    });

    // Click Decide (GM)
    $(document).on('click', '.btn-decide', function() {
        var id   = $(this).data('id');
        var cat  = $(this).data('category');
        var act  = $(this).data('active');
        var req  = $(this).data('req');
        var diff = $(this).data('diff');
        var by   = $(this).data('by');
        var at   = $(this).data('at');

        $('#decide_id').val(id);
        $('#dec_cat').text(cat);
        $('#dec_by_at').text(by + ' · ' + at);
        $('#dec_active').text(act);
        $('#dec_req').text(req);
        $('#dec_diff').text(diff);

        $('#dec_approve').prop('checked', true).trigger('change');
        $('#dec_reason').val('');
        $('#modalDecideLabel').html('<i class="fa fa-gavel me-2"></i>Periksa Pengajuan Extra Margin — ' + cat);
        $('#modal-decide').modal('show');
    });

    // Submit Decision
    $('#form-decide').on('submit', function(e) {
        e.preventDefault();
        var id       = $('#decide_id').val();
        var decision = $('input[name="decision"]:checked').val();
        var reason   = $('#dec_reason').val();

        if (decision === 'Reject' && !reason.trim()) {
            Swal.fire({ title: 'Validasi', text: 'Alasan penolakan (Reject) wajib diisi.', icon: 'warning' });
            return;
        }

        var isApprove = (decision === 'Approve');
        var actionUrl = isApprove ? '<?= base_url('rate_negotiation_allowance/approve') ?>' : '<?= base_url('rate_negotiation_allowance/reject') ?>';

        Swal.fire({
            title: isApprove ? 'Setujui Pengajuan?' : 'Tolak Pengajuan?',
            text: isApprove ? 'Rate aktif akan diperbarui sesuai nilai yang diajukan.' : 'Pengajuan akan ditolak dan dikembalikan ke Sales Manager.',
            icon: isApprove ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: isApprove ? '#28a745' : '#dc3545',
            confirmButtonText: isApprove ? 'Ya, Setujui' : 'Ya, Tolak',
            cancelButtonText: 'Batal'
        }).then(function(res) {
            if (!res.isConfirmed) return;

            $('#btn-submit-decision').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: actionUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
                    reject_reason: reason
                },
                success: function(resp) {
                    $('#btn-submit-decision').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Keputusan');
                    if (resp.status == 1) {
                        $('#modal-decide').modal('hide');
                        Swal.fire({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1800, showConfirmButton: false })
                            .then(function() { window.location.reload(); });
                    } else {
                        Swal.fire({ title: 'Gagal!', text: resp.pesan, icon: 'error' });
                    }
                },
                error: function(xhr) {
                    $('#btn-submit-decision').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Keputusan');
                    Swal.fire({ title: 'Error!', text: 'Request error: ' + xhr.status, icon: 'error' });
                }
            });
        });
    });

    // View Detail Decided
    $(document).on('click', '.btn-view-decide', function() {
        var cat      = $(this).data('category');
        var act      = $(this).data('active');
        var req      = $(this).data('req');
        var by       = $(this).data('by');
        var at       = $(this).data('at');
        var st       = $(this).data('status');
        var decBy    = $(this).data('decided-by');
        var decAt    = $(this).data('decided-at');
        var reason   = $(this).data('reason');

        $('#v_cat').text(cat);
        $('#v_active').text(act);
        $('#v_req').text(req);
        $('#v_by_at').text(by + ' · ' + at);
        $('#v_dec_by_at').text(decBy + ' · ' + decAt);

        if (st === 'Approved') {
            $('#v_status').html('<span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> Approved</span>');
            $('#v_group_reason').addClass('d-none');
        } else {
            $('#v_status').html('<span class="badge bg-danger"><i class="fa fa-times-circle me-1"></i> Rejected</span>');
            $('#v_reason').text(reason);
            $('#v_group_reason').removeClass('d-none');
        }

        $('#modal-view').modal('show');
    });
});
</script>
