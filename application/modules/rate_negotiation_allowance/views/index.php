<?php
$ENABLE_MANAGE = has_permission('Rate_Negotiation_Allowance.Manage');
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1 text-primary fw-bold">
                <i class="fa fa-tag me-2"></i>Negotiation Allowance (Extra Margin)
            </h5>
            <small class="text-muted">
                Sales Manager mengajukan rate Extra Margin per Product Category. Perubahan baru berlaku efektif setelah disetujui oleh GM.
            </small>
        </div>
        <div class="d-flex align-items-center">
            <?php if ($pending_count > 0): ?>
                <span class="badge bg-warning text-dark border border-warning px-3 py-2 me-2">
                    <i class="fa fa-clock-o me-1"></i> <strong><?= $pending_count ?></strong> Menunggu Approval GM
                </span>
            <?php endif; ?>
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

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-nego" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>Product Category</th>
                        <th class="text-end" width="160">Rate Aktif (%)</th>
                        <th class="text-center" width="180">Status Pengajuan</th>
                        <th class="text-end" width="160">Rate Diajukan (%)</th>
                        <th class="text-center" width="150">Last Update</th>
                        <th width="150">Updated By</th>
                        <?php if ($ENABLE_MANAGE): ?>
                            <th class="text-center no-sort" width="170">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($list)): ?>
                        <?php foreach ($list as $idx => $r): ?>
                            <?php
                            $is_pending = ($r->req_status === 'Menunggu Approval GM');
                            $is_rejected = ($r->req_status === 'Rejected');
                            $is_approved = ($r->req_status === 'Approved');
                            ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?></td>
                                <td class="text-end fw-bold text-success fs-6">
                                    <?= ($r->active_rate !== null) ? number_format($r->active_rate * 100, 2, ',', '.') . ' %' : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_pending): ?>
                                        <span class="badge bg-warning text-dark border border-warning px-2 py-1">
                                            <i class="fa fa-clock-o me-1"></i> Menunggu GM
                                        </span>
                                    <?php elseif ($is_rejected): ?>
                                        <span class="badge bg-danger px-2 py-1" data-bs-toggle="tooltip" title="<?= htmlspecialchars($r->reject_reason ?: 'Tidak ada alasan') ?>">
                                            <i class="fa fa-times-circle me-1"></i> Ditolak GM
                                        </span>
                                    <?php elseif ($is_approved): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="fa fa-check-circle me-1"></i> Approved
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <?php if ($is_pending): ?>
                                        <span class="text-primary fs-6"><?= number_format($r->requested_rate * 100, 2, ',', '.') ?> %</span>
                                    <?php elseif ($is_rejected): ?>
                                        <span class="text-muted text-decoration-line-through"><?= number_format($r->requested_rate * 100, 2, ',', '.') ?> %</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-muted">
                                    <?= !empty($r->latest_update) ? date('d/m/Y H:i', strtotime($r->latest_update)) : '-' ?>
                                </td>
                                <td><?= htmlspecialchars($r->updated_by_name ?: '-') ?></td>
                                <?php if ($ENABLE_MANAGE): ?>
                                    <td class="text-center">
                                        <?php if ($is_pending): ?>
                                            <button type="button" class="btn btn-sm btn-secondary" disabled title="Menunggu keputusan GM">
                                                <i class="fa fa-clock-o me-1"></i> Menunggu GM
                                            </button>
                                        <?php elseif ($is_rejected): ?>
                                            <button type="button" class="btn btn-sm btn-warning btn-request-nego shadow-sm"
                                                data-code="<?= $r->code_lv2 ?>"
                                                data-nama="<?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?>"
                                                data-active="<?= ($r->active_rate !== null) ? (float)($r->active_rate * 100) : '' ?>"
                                                data-req="<?= (float)($r->requested_rate * 100) ?>"
                                                data-reason="<?= htmlspecialchars($r->reject_reason ?: '') ?>"
                                                title="Ajukan Ulang">
                                                <i class="fa fa-refresh me-1"></i> Ajukan Ulang
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-primary btn-request-nego shadow-sm"
                                                data-code="<?= $r->code_lv2 ?>"
                                                data-nama="<?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?>"
                                                data-active="<?= ($r->active_rate !== null) ? (float)($r->active_rate * 100) : '' ?>"
                                                data-req=""
                                                data-reason=""
                                                title="Ajukan Perubahan">
                                                <i class="fa fa-pencil me-1"></i> Ajukan Perubahan
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajukan Perubahan Extra Margin -->
<div class="modal fade" id="modal-request" tabindex="-1" aria-labelledby="modalRequestLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form id="form-request" autocomplete="off">
                <input type="hidden" name="code_lv2" id="req_code_lv2">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalRequestLabel">Ajukan Perubahan Extra Margin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none" id="alert-reason">
                        <div class="fw-bold"><i class="fa fa-info-circle me-1"></i> Alasan Penolakan Sebelumnya:</div>
                        <div id="txt-reason" class="mt-1"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Product Category</label>
                        <input type="text" id="req_category_name" class="form-control bg-light fw-bold" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Rate Extra Margin Aktif Saat Ini</label>
                        <div class="input-group">
                            <input type="text" id="req_active_rate" class="form-control bg-light text-end fw-bold" readonly>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Rate Baru Yang Diajukan (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" name="rate_negotiation_allowance" id="req_new_rate" class="form-control text-end fs-6 fw-bold text-primary" required placeholder="0.00">
                            <span class="input-group-text fw-bold">%</span>
                        </div>
                        <div class="form-text text-muted">
                            Persentase Extra Margin dihitung terhadap Bottom Price saat negosiasi sales.
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded border text-muted small">
                        <i class="fa fa-info-circle text-primary me-1"></i>
                        Rate baru ini akan masuk ke antrean <strong>Approval GM</strong> dan tidak akan mengubah rate aktif sebelum disetujui.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4" id="btn-submit-req">
                        <i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan ke GM
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tbl-nego').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }]
        });

        // Tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(el) {
            return new bootstrap.Tooltip(el);
        });

        // Click Ajukan Perubahan / Ajukan Ulang
        $(document).on('click', '.btn-request-nego', function() {
            var code = $(this).data('code');
            var nama = $(this).data('nama');
            var active = $(this).data('active');
            var req = $(this).data('req');
            var reason = $(this).data('reason');

            $('#req_code_lv2').val(code);
            $('#req_category_name').val(nama);
            $('#req_active_rate').val(active !== '' ? Number(active).toFixed(2) : '—');
            $('#req_new_rate').val(req !== '' ? Number(req).toFixed(2) : (active !== '' ? Number(active).toFixed(2) : ''));

            if (reason) {
                $('#txt-reason').text(reason);
                $('#alert-reason').removeClass('d-none');
            } else {
                $('#alert-reason').addClass('d-none');
            }

            $('#modalRequestLabel').text('Ajukan Extra Margin — ' + nama);
            $('#modal-request').modal('show');
        });

        // Submit Request
        $('#form-request').on('submit', function(e) {
            e.preventDefault();
            var code = $('#req_code_lv2').val();
            var val = $('#req_new_rate').val();

            if (val === '' || isNaN(val) || Number(val) < 0 || Number(val) > 100) {
                Swal.fire({
                    title: 'Validasi',
                    text: 'Rate Extra Margin harus antara 0% dan 100%',
                    icon: 'warning'
                });
                return;
            }

            $('#btn-submit-req').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Mengirim...');

            $.ajax({
                url: '<?= base_url('rate_negotiation_allowance/save_request') ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    code_lv2: code,
                    rate_negotiation_allowance: val
                },
                success: function(resp) {
                    $('#btn-submit-req').prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan ke GM');
                    if (resp.status == 1) {
                        $('#modal-request').modal('hide');
                        Swal.fire({
                                title: 'Berhasil!',
                                text: resp.pesan,
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false
                            })
                            .then(function() {
                                window.location.reload();
                            });
                    } else {
                        Swal.fire({
                            title: 'Perhatian',
                            text: resp.pesan,
                            icon: 'warning'
                        });
                    }
                },
                error: function(xhr) {
                    $('#btn-submit-req').prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan ke GM');
                    Swal.fire({
                        title: 'Error!',
                        text: 'Request error: ' + xhr.status,
                        icon: 'error'
                    });
                }
            });
        });
    });
</script>