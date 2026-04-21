<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Production Plan</h5>
        <div class="d-flex gap-2">
            <?php if ($plan->status === 'Draft'): ?>
                <a href="<?= base_url('production_planning/edit/' . $plan->plan_no) ?>" class="btn btn-warning btn-sm">
                    <i class="fa fa-edit"></i> Edit
                </a>
                <button class="btn btn-success btn-sm btn-release" data-plan="<?= $plan->plan_no ?>">
                    <i class="fa fa-check"></i> Release
                </button>
            <?php endif; ?>
            <?php if (in_array($plan->status, ['Draft', 'Released'])): ?>
                <button class="btn btn-danger btn-sm btn-cancel" data-plan="<?= $plan->plan_no ?>">
                    <i class="fa fa-times"></i> Cancel
                </button>
            <?php endif; ?>
            <a href="<?= base_url('production_planning') ?>" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="160" class="fw-semibold">No Plan</td>
                        <td>: <?= htmlspecialchars($plan->plan_no) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Tanggal Plan</td>
                        <td>: <?= date('d/m/Y', strtotime($plan->tgl_plan)) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Produk FG</td>
                        <td>: <?= htmlspecialchars($plan->nm_produk_fg ?: $plan->id_produk_fg) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">ID Produk FG</td>
                        <td>: <?= htmlspecialchars($plan->id_produk_fg) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="160" class="fw-semibold">Target Qty</td>
                        <td>: <?= number_format($plan->target_qty, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Target Berat</td>
                        <td>: <?= $plan->target_berat ? number_format($plan->target_berat, 3) . ' kg' : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Due Date</td>
                        <td>: <?= $plan->due_date ? date('d/m/Y', strtotime($plan->due_date)) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Status</td>
                        <td>:
                            <?php
                            $status_map = ['Draft' => 'secondary', 'Released' => 'primary', 'Closed' => 'success', 'Cancelled' => 'danger'];
                            $color = isset($status_map[$plan->status]) ? $status_map[$plan->status] : 'secondary';
                            ?>
                            <span class="badge bg-<?= $color ?>"><?= $plan->status ?></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php if ($plan->catatan): ?>
            <div class="mt-2">
                <strong>Catatan:</strong> <?= nl2br(htmlspecialchars($plan->catatan)) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Coil -->
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">Detail Coil yang Dialokasikan</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>No Coil</th>
                        <th>No ROS</th>
                        <th>Material</th>
                        <th class="text-end">Net Weight (kg)</th>
                        <th class="text-end">Estimasi FG (kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($details)): ?>
                        <?php $total_net = 0; $total_fg = 0; ?>
                        <?php foreach ($details as $i => $d): ?>
                            <?php $total_net += $d->net_weight_coil; $total_fg += $d->estimasi_fg; ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($d->no_coil) ?></td>
                                <td><?= htmlspecialchars($d->no_ros ?: '-') ?></td>
                                <td><?= htmlspecialchars($d->nm_material ?: '-') ?></td>
                                <td class="text-end"><?= number_format($d->net_weight_coil, 3) ?></td>
                                <td class="text-end"><?= number_format($d->estimasi_fg, 3) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end">Total:</td>
                            <td class="text-end"><?= number_format($total_net, 3) ?></td>
                            <td class="text-end"><?= number_format($total_fg, 3) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada coil yang dialokasikan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Status Alokasi Coil -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Status Alokasi Coil</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>No Coil</th>
                        <th class="text-center">Status Alokasi</th>
                        <th class="text-center">Dibuat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($alloc)): ?>
                        <?php foreach ($alloc as $i => $a): ?>
                            <?php
                            $alloc_map = ['allocated' => 'info', 'issued' => 'warning', 'done' => 'success', 'cancelled' => 'danger'];
                            $alloc_color = isset($alloc_map[$a->status_alloc]) ? $alloc_map[$a->status_alloc] : 'secondary';
                            ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($a->no_coil) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $alloc_color ?>"><?= ucfirst($a->status_alloc) ?></span>
                                </td>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($a->created_at)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Tidak ada data alokasi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.btn-release', function () {
    var plan_no = $(this).data('plan');
    swal({
        title: 'Release Plan?',
        text: 'Plan akan diubah ke status Released.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Ya, Release',
        cancelButtonText: 'Batal'
    }, function (isConfirm) {
        if (isConfirm) {
            $.post(siteurl + 'production_planning/process_release/' + plan_no, function (res) {
                if (res.success) {
                    swal('Berhasil!', 'Plan berhasil di-release.', 'success');
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    swal('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                }
            }, 'json');
        }
    });
});

$(document).on('click', '.btn-cancel', function () {
    var plan_no = $(this).data('plan');
    swal({
        title: 'Batalkan Plan?',
        text: 'Plan akan dibatalkan.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak'
    }, function (isConfirm) {
        if (isConfirm) {
            $.post(siteurl + 'production_planning/process_cancel/' + plan_no, function (res) {
                if (res.success) {
                    swal('Berhasil!', 'Plan berhasil dibatalkan.', 'success');
                    setTimeout(function () { window.location.href = siteurl + 'production_planning'; }, 1500);
                } else {
                    swal('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                }
            }, 'json');
        }
    });
});
</script>
