<?php
$ENABLE_MANAGE = has_permission('Rate_Generals.Manage');
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1 text-primary fw-bold">
                <i class="fa fa-sliders me-2"></i>General Rate (Rate Umum)
            </h5>
            <small class="text-muted">
                Berlaku untuk semua produk: Manufacturing Support (Consumables, Scrap, Biaya Koordinasi) dan enam elemen Other Cost.
            </small>
        </div>
        <div>
            <span class="badge bg-light text-dark border px-3 py-2 me-2">
                Total: <strong><?= count($elements) ?></strong> Elemen Costing
            </span>
            <?php if ($ENABLE_MANAGE): ?>
                <button type="button" class="btn btn-outline-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-batch-edit">
                    <i class="fa fa-pencil-square-o me-1"></i> Edit Semua Komponen
                </button>
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
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-general-rate" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th width="180">Kelompok</th>
                        <th>Element Costing</th>
                        <th>Rumus / Dasar</th>
                        <th class="text-end" width="180">Rate / Tarif</th>
                        <th width="150">Update By</th>
                        <th class="text-center" width="150">Update Date</th>
                        <?php if ($ENABLE_MANAGE): ?>
                            <th class="text-center no-sort" width="90">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($elements as $idx => $el): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <?php if ($el['group'] === 'Manufacturing Support'): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">
                                        <i class="fa fa-cogs me-1"></i> Manufacturing Support
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1">
                                        <i class="fa fa-pie-chart me-1"></i> Other Cost
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($el['label']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($el['basis']) ?></td>
                            <td class="text-end fw-bold fs-6">
                                <?php if ($el['unit'] === 'pct'): ?>
                                    <span class="text-success"><?= number_format($el['value'] * 100, 2, ',', '.') ?> %</span>
                                <?php else: ?>
                                    <span class="text-primary">Rp <?= number_format($el['value'], 2, ',', '.') ?> / Kg</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($el['by']) ?></td>
                            <td class="text-center text-muted">
                                <?= !empty($el['updated']) ? date('d/m/Y H:i', strtotime($el['updated'])) : '-' ?>
                            </td>
                            <?php if ($ENABLE_MANAGE): ?>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary btn-edit-single shadow-sm"
                                        data-key="<?= $el['key'] ?>"
                                        data-label="<?= htmlspecialchars($el['label']) ?>"
                                        data-basis="<?= htmlspecialchars($el['basis']) ?>"
                                        data-unit="<?= $el['unit'] ?>"
                                        data-val="<?= $el['unit'] === 'pct' ? (float)($el['value'] * 100) : (float)$el['value'] ?>"
                                        data-req="<?= $el['required'] ? '1' : '0' ?>"
                                        title="Edit Rate">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Single Element -->
<div class="modal fade" id="modal-single-edit" tabindex="-1" aria-labelledby="modalSingleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form id="form-single-edit" autocomplete="off">
                <input type="hidden" name="key" id="edit_key">
                <input type="hidden" name="unit" id="edit_unit">
                <input type="hidden" name="label" id="edit_label">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalSingleLabel">Edit Rate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Element Costing</label>
                        <input type="text" id="disp_label" class="form-control bg-light fw-bold" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Rumus / Dasar</label>
                        <input type="text" id="disp_basis" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" id="lbl_edit_val">Nilai Tarif <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" id="prefix_val">Rp</span>
                            <input type="number" step="0.0001" min="0" name="value" id="edit_val" class="form-control text-end fs-6 fw-bold" required placeholder="0.00">
                            <span class="input-group-text" id="suffix_val">/ Kg</span>
                        </div>
                        <div class="form-text text-muted" id="hint_edit_val">
                            Untuk elemen Other Cost, nilai kosong dianggap 0.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4" id="btn-save-single">
                        <i class="fa fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Batch Edit All Elements -->
<div class="modal fade" id="modal-batch-edit" tabindex="-1" aria-labelledby="modalBatchLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="<?= base_url('rate_generals/save_all') ?>" method="POST" autocomplete="off">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalBatchLabel">
                        <i class="fa fa-sliders me-2"></i>Edit Seluruh Komponen General Rate
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary-subtle text-primary fw-bold">
                            <i class="fa fa-cogs me-1"></i> Manufacturing Support
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Consumables (Rp/Kg)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" step="0.0001" min="0" name="rate_consumables" class="form-control text-end" value="<?= (float)$rate_row->rate_consumables ?>" required>
                                    </div>
                                    <div class="form-text text-muted">Dasar: Rp/Kg material</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Scrap (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" name="rate_scrap" class="form-control text-end" value="<?= (float)($rate_row->rate_scrap * 100) ?>" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text text-muted">Dasar: % Material Cost</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Biaya Koordinasi (Rp/Kg)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" step="0.0001" min="0" name="rate_biaya_koordinasi" class="form-control text-end" value="<?= (float)$rate_row->rate_biaya_koordinasi ?>" required>
                                    </div>
                                    <div class="form-text text-muted">Dasar: Rp/Kg material</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-warning">
                        <div class="card-header bg-warning-subtle text-dark fw-bold">
                            <i class="fa fa-pie-chart me-1"></i> Other Cost (% terhadap HPP)
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Sales &amp; Marketing</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" name="rate_other_cost_sales_marketing" class="form-control text-end" value="<?= (float)($rate_row->rate_other_cost_sales_marketing * 100) ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Transport</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" name="rate_other_cost_transport" class="form-control text-end" value="<?= (float)($rate_row->rate_other_cost_transport * 100) ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Finance &amp; Admin</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" name="rate_other_cost_finance_admin" class="form-control text-end" value="<?= (float)($rate_row->rate_other_cost_finance_admin * 100) ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Management Fee</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" name="rate_other_cost_management_fee" class="form-control text-end" value="<?= (float)($rate_row->rate_other_cost_management_fee * 100) ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Interest</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" name="rate_other_cost_interest" class="form-control text-end" value="<?= (float)($rate_row->rate_other_cost_interest * 100) ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Profit</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" name="rate_other_cost_profit" class="form-control text-end" value="<?= (float)($rate_row->rate_other_cost_profit * 100) ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-save me-1"></i> Simpan Seluruh Rate
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
    $('#tbl-general-rate').DataTable({
        responsive: true,
        paging: false,
        searching: false,
        info: false,
        columnDefs: [{ targets: 'no-sort', orderable: false }]
    });

    // Single Edit Click
    $(document).on('click', '.btn-edit-single', function() {
        var key   = $(this).data('key');
        var label = $(this).data('label');
        var basis = $(this).data('basis');
        var unit  = $(this).data('unit');
        var val   = $(this).data('val');

        $('#edit_key').val(key);
        $('#edit_label').val(label);
        $('#edit_unit').val(unit);
        $('#disp_label').val(label);
        $('#disp_basis').val(basis);
        $('#edit_val').val(val);

        if (unit === 'pct') {
            $('#prefix_val').addClass('d-none');
            $('#suffix_val').removeClass('d-none').text('%');
            $('#lbl_edit_val').html('Nilai Persentase (%) <span class="text-danger">*</span>');
            $('#hint_edit_val').text('Persentase dihitung terhadap HPP (atau terhadap Material Cost untuk Scrap).');
        } else {
            $('#prefix_val').removeClass('d-none').text('Rp');
            $('#suffix_val').removeClass('d-none').text('/ Kg');
            $('#lbl_edit_val').html('Nilai Tarif (Rp/Kg) <span class="text-danger">*</span>');
            $('#hint_edit_val').text('Tarif per kilogram material dalam Rupiah.');
        }

        $('#modalSingleLabel').text('Edit Rate — ' + label);
        $('#modal-single-edit').modal('show');
    });

    // Single Edit Submit
    $('#form-single-edit').on('submit', function(e) {
        e.preventDefault();
        var key   = $('#edit_key').val();
        var label = $('#edit_label').val();
        var unit  = $('#edit_unit').val();
        var val   = $('#edit_val').val();

        if (val === '' || isNaN(val) || Number(val) < 0) {
            Swal.fire({ title: 'Validasi', text: 'Nilai tarif tidak valid atau bernilai negatif', icon: 'warning' });
            return;
        }

        $('#btn-save-single').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

        $.ajax({
            url: '<?= base_url('rate_generals/save_element') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                key: key,
                label: label,
                unit: unit,
                value: val
            },
            success: function(resp) {
                $('#btn-save-single').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Perubahan');
                if (resp.status == 1) {
                    $('#modal-single-edit').modal('hide');
                    Swal.fire({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1500, showConfirmButton: false })
                        .then(function() { window.location.reload(); });
                } else {
                    Swal.fire({ title: 'Gagal!', text: resp.pesan, icon: 'error' });
                }
            },
            error: function(xhr) {
                $('#btn-save-single').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Perubahan');
                Swal.fire({ title: 'Error!', text: 'Request error: ' + xhr.status, icon: 'error' });
            }
        });
    });
});
</script>
