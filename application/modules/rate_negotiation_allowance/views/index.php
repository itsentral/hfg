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
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
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

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Product Category</label>
                            <input type="text" id="req_category_name" class="form-control bg-light fw-bold" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">Rate Aktif Saat Ini</label>
                            <div class="input-group">
                                <input type="text" id="req_active_rate" class="form-control bg-light text-end fw-bold" readonly>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Rate Baru Diajukan (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" name="rate_negotiation_allowance" id="req_new_rate" class="form-control text-end fs-6 fw-bold text-primary" required placeholder="0.00">
                                <span class="input-group-text fw-bold">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Impact Section -->
                    <div class="card border mb-3">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fa fa-line-chart text-warning me-1"></i> Detail Impact Simulasi Harga :
                            </h6>
                            <span class="badge bg-secondary" id="req_impact_count">0 Produk</span>
                        </div>
                        <div class="card-body p-0">
                            <div id="req_impact_loading" class="text-center py-4 text-muted" style="display:none;">
                                <i class="fa fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
                                <div>Memuat simulasi impact produk...</div>
                            </div>
                            <div class="table-responsive" id="req_impact_wrapper" style="max-height: 280px; overflow-y: auto;">
                                <table class="table table-sm table-bordered table-hover align-middle mb-0" id="req_tbl_impact">
                                    <thead class="table-light sticky-top">
                                        <tr class="text-center text-nowrap">
                                            <th width="40">No</th>
                                            <th>Category Produk</th>
                                            <th>Produk</th>
                                            <th class="text-end">Bottom Price</th>
                                            <th class="text-end">Pricelist Saat Ini</th>
                                            <th class="text-center">Pengajuan Ext. Margin</th>
                                            <th class="text-end" style="background-color: #fff3cd; color: #856404;">Pricelist/Meter</th>
                                            <th class="text-end">Selisih</th>
                                        </tr>
                                    </thead>
                                    <tbody id="req_impact_tbody"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light py-2 text-muted small">
                            <i class="fa fa-lightbulb-o text-warning me-1"></i> Nilai <strong>Pricelist/Meter</strong> dan <strong>Selisih</strong> langsung terhitung otomatis saat persentase di atas diketik.
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

        var currentImpactItems = [];

        function formatMoney(n) {
            return Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function escHtml(str) {
            return $('<div>').text(str || '').html();
        }

        function renderImpactTable(items, tbodyId, countId, customReqPct) {
            var $tbody = $('#' + tbodyId);
            $tbody.empty();
            if (!items || items.length === 0) {
                $tbody.html('<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data produk / costing untuk kategori ini.</td></tr>');
                if (countId) $('#' + countId).text('0 Produk');
                return;
            }
            if (countId) $('#' + countId).text(items.length + ' Produk');

            var reqPct = (customReqPct !== undefined && customReqPct !== null && !isNaN(customReqPct)) ? Number(customReqPct) : 0;

            items.forEach(function(it, idx) {
                var pct = reqPct;
                var bottom = Number(it.bottom_price || 0);
                var curPl = Number(it.current_pricelist || 0);
                var newPl = bottom * (1 + pct / 100);
                var diffRp = newPl - curPl;

                var diffBadge = '';
                if (diffRp > 0) {
                    diffBadge = '<span class="text-success fw-bold">+' + formatMoney(diffRp) + '</span>';
                } else if (diffRp < 0) {
                    diffBadge = '<span class="text-danger fw-bold">' + formatMoney(diffRp) + '</span>';
                } else {
                    diffBadge = '<span class="text-muted">Rp 0,00</span>';
                }

                var varianBadge = it.no_uniq_id ? '<span class="badge bg-secondary ms-1">' + escHtml(it.no_uniq_id) + '</span>' : '';

                var tr = '<tr>' +
                    '<td class="text-center">' + (idx + 1) + '</td>' +
                    '<td class="fw-semibold">' + escHtml(it.nm_category || '-') + '</td>' +
                    '<td>' + escHtml(it.nm_product || '-') + varianBadge + '</td>' +
                    '<td class="text-end">Rp ' + formatMoney(bottom) + '</td>' +
                    '<td class="text-end text-muted">Rp ' + formatMoney(curPl) + '</td>' +
                    '<td class="text-center"><span class="badge bg-primary px-2 py-1">' + pct.toFixed(2) + '%</span></td>' +
                    '<td class="text-end fw-bold" style="background-color: #fff3cd; color: #856404;">Rp ' + formatMoney(newPl) + '</td>' +
                    '<td class="text-end">' + diffBadge + '</td>' +
                    '</tr>';
                $tbody.append(tr);
            });
        }

        // Live recalculate saat rate diajukan diubah oleh user
        $('#req_new_rate').on('input change', function() {
            var val = parseFloat($(this).val());
            if (isNaN(val) || val < 0) val = 0;
            renderImpactTable(currentImpactItems, 'req_impact_tbody', 'req_impact_count', val);
        });

        // Click Ajukan Perubahan / Ajukan Ulang
        $(document).on('click', '.btn-request-nego', function() {
            var code = $(this).data('code');
            var nama = $(this).data('nama');
            var active = $(this).data('active');
            var req = $(this).data('req');
            var reason = $(this).data('reason');

            var initialRate = req !== '' ? Number(req).toFixed(2) : (active !== '' ? Number(active).toFixed(2) : '0.00');

            $('#req_code_lv2').val(code);
            $('#req_category_name').val(nama);
            $('#req_active_rate').val(active !== '' ? Number(active).toFixed(2) : '—');
            $('#req_new_rate').val(initialRate);

            if (reason) {
                $('#txt-reason').text(reason);
                $('#alert-reason').removeClass('d-none');
            } else {
                $('#alert-reason').addClass('d-none');
            }

            $('#modalRequestLabel').text('Ajukan Extra Margin — ' + nama);

            // Load Detail Impact
            currentImpactItems = [];
            $('#req_impact_tbody').empty();
            $('#req_impact_loading').show();
            $('#req_impact_wrapper').hide();
            $('#req_impact_count').text('Memuat...');

            $.ajax({
                url: '<?= base_url('rate_negotiation_allowance/get_detail_impact') ?>',
                type: 'GET',
                dataType: 'json',
                data: {
                    code_lv2: code,
                    rate_pct: initialRate
                },
                success: function(resp) {
                    $('#req_impact_loading').hide();
                    $('#req_impact_wrapper').show();
                    if (resp.status == 1) {
                        currentImpactItems = resp.items || [];
                        var curVal = parseFloat($('#req_new_rate').val()) || 0;
                        renderImpactTable(currentImpactItems, 'req_impact_tbody', 'req_impact_count', curVal);
                    } else {
                        $('#req_impact_tbody').html('<tr><td colspan="8" class="text-center text-danger py-3">' + (resp.pesan || 'Gagal memuat impact.') + '</td></tr>');
                        $('#req_impact_count').text('0 Produk');
                    }
                },
                error: function() {
                    $('#req_impact_loading').hide();
                    $('#req_impact_wrapper').show();
                    $('#req_impact_tbody').html('<tr><td colspan="8" class="text-center text-danger py-3">Terjadi kesalahan jaringan saat memuat impact.</td></tr>');
                    $('#req_impact_count').text('0 Produk');
                }
            });

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