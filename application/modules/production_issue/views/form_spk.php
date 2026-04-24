<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Buat SPK Produksi</h5>
        <a href="<?= base_url('production_issue') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <?php if (!$plan): ?>
            <!-- Form pilih plan jika belum ada plan_no -->
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                Pilih Production Plan yang sudah Released untuk membuat SPK.
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">No Plan <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="input-plan-no" class="form-control" placeholder="Masukkan No Plan...">
                        <button type="button" class="btn btn-primary" id="btn-load-plan">
                            <i class="fa fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </div>
            <div id="plan-info" style="display:none;"></div>
        <?php else: ?>
            <!-- Info plan sudah dipilih -->
            <div class="alert alert-success mb-3">
                <strong>Plan:</strong> <?= htmlspecialchars($plan->plan_no) ?> —
                <?= htmlspecialchars($plan->nm_produk_fg ?: $plan->id_produk_fg) ?>
                | Target: <?= number_format($plan->target_qty, 2) ?> pcs
            </div>
        <?php endif; ?>

        <form id="form-spk" action="<?= base_url('production_issue/save_spk') ?>" method="POST">
            <input type="hidden" name="plan_no" id="field-plan-no" value="<?= $plan ? htmlspecialchars($plan->plan_no) : '' ?>">
            <input type="hidden" name="produk_fg" id="field-produk-fg" value="<?= $plan ? htmlspecialchars($plan->id_produk_fg) : '' ?>">
            <input type="hidden" name="nm_produk_fg" id="field-nm-produk-fg" value="<?= $plan ? htmlspecialchars($plan->nm_produk_fg) : '' ?>">

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal SPK <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_spk" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control"
                        value="<?= $plan && $plan->due_date ? $plan->due_date : '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Target Qty <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="target_qty" id="field-target-qty" class="form-control"
                        value="<?= $plan ? $plan->target_qty : '' ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="1"></textarea>
                </div>
            </div>

            <!-- Tabel Coil dari Plan -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Coil yang Akan Di-issue</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="50">No</th>
                                    <th>No Coil</th>
                                    <th>No ROS</th>
                                    <th>Material</th>
                                    <th class="text-end">Berat Bersih (kg)</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-coil-spk">
                                <?php if (!empty($details)): ?>
                                    <?php foreach ($details as $i => $d): ?>
                                        <tr>
                                            <td class="text-center"><?= $i + 1 ?></td>
                                            <td>
                                                <?= htmlspecialchars($d->no_coil) ?>
                                                <input type="hidden" name="coil[<?= $i ?>][no_coil]" value="<?= htmlspecialchars($d->no_coil) ?>">
                                                <input type="hidden" name="coil[<?= $i ?>][id_material]" value="<?= htmlspecialchars($d->id_material) ?>">
                                                <input type="hidden" name="coil[<?= $i ?>][nm_material]" value="<?= htmlspecialchars($d->nm_material) ?>">
                                                <input type="hidden" name="coil[<?= $i ?>][no_ros]" value="<?= htmlspecialchars($d->no_ros) ?>">
                                                <input type="hidden" name="coil[<?= $i ?>][net_weight]" value="<?= $d->net_weight_coil ?>">
                                            </td>
                                            <td><?= htmlspecialchars($d->no_ros ?: '-') ?></td>
                                            <td><?= htmlspecialchars($d->nm_material ?: '-') ?></td>
                                            <td class="text-end"><?= number_format($d->net_weight_coil, 3) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr id="row-empty-coil">
                                        <td colspan="5" class="text-center text-muted py-3">
                                            <?= $plan ? 'Tidak ada coil pada plan ini' : 'Pilih plan terlebih dahulu' ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="btn-save-spk"
                    <?= !$plan ? 'disabled' : '' ?>>
                    <i class="fa fa-save"></i> Simpan SPK
                </button>
                <a href="<?= base_url('production_issue') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    <?php if (!$plan): ?>
    // Cari plan
    $('#btn-load-plan').on('click', function () {
        var plan_no = $('#input-plan-no').val().trim();
        if (!plan_no) { alert('Masukkan No Plan terlebih dahulu'); return; }

        $.get(siteurl + 'production_planning/get_plan_info', { plan_no: plan_no }, function (res) {
            if (res.status === 'ok' && res.plan) {
                var p = res.plan;
                $('#field-plan-no').val(p.plan_no);
                $('#field-produk-fg').val(p.id_produk_fg);
                $('#field-nm-produk-fg').val(p.nm_produk_fg);
                $('#field-target-qty').val(p.target_qty);

                var html = '<div class="alert alert-success"><strong>Plan:</strong> ' + p.plan_no
                         + ' — ' + (p.nm_produk_fg || p.id_produk_fg)
                         + ' | Target: ' + parseFloat(p.target_qty).toFixed(2) + ' pcs</div>';
                $('#plan-info').html(html).show();

                // Render coil
                var coilHtml = '';
                if (res.details && res.details.length > 0) {
                    $.each(res.details, function (i, d) {
                        coilHtml += '<tr>';
                        coilHtml += '<td class="text-center">' + (i + 1) + '</td>';
                        coilHtml += '<td>' + d.no_coil;
                        coilHtml += '<input type="hidden" name="coil[' + i + '][no_coil]" value="' + d.no_coil + '">';
                        coilHtml += '<input type="hidden" name="coil[' + i + '][id_material]" value="' + (d.id_material || '') + '">';
                        coilHtml += '<input type="hidden" name="coil[' + i + '][nm_material]" value="' + (d.nm_material || '') + '">';
                        coilHtml += '<input type="hidden" name="coil[' + i + '][no_ros]" value="' + (d.no_ros || '') + '">';
                        coilHtml += '<input type="hidden" name="coil[' + i + '][net_weight]" value="' + (d.net_weight_coil || 0) + '">';
                        coilHtml += '</td>';
                        coilHtml += '<td>' + (d.no_ros || '-') + '</td>';
                        coilHtml += '<td>' + (d.nm_material || '-') + '</td>';
                        coilHtml += '<td class="text-end">' + parseFloat(d.net_weight_coil || 0).toFixed(3) + '</td>';
                        coilHtml += '</tr>';
                    });
                } else {
                    coilHtml = '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada coil pada plan ini</td></tr>';
                }
                $('#tbody-coil-spk').html(coilHtml);
                $('#btn-save-spk').prop('disabled', false);
            } else {
                alert(res.message || 'Plan tidak ditemukan atau belum Released');
            }
        }, 'json');
    });
    <?php endif; ?>
});
</script>
