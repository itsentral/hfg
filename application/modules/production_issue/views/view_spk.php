<?php defined('BASEPATH') || exit('No direct script access allowed');
$status_map = [
    'Draft'      => 'secondary',
    'Released'   => 'primary',
    'In Process' => 'warning',
    'Submitted'  => 'info',
    'Closed'     => 'success',
    'Cancelled'  => 'danger',
];
$color = isset($status_map[$spk->status]) ? $status_map[$spk->status] : 'secondary';
$pct   = $total_coil > 0 ? round(($scanned_coil / $total_coil) * 100) : 0;
?>

<!-- Header SPK -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail SPK: <?= htmlspecialchars($spk->spk_no) ?></h5>
        <div class="d-flex gap-2">
            <?php if ($spk->status === 'Draft'): ?>
                <button class="btn btn-success btn-sm btn-release-spk" data-spk="<?= $spk->spk_no ?>">
                    <i class="fa fa-check"></i> Release SPK
                </button>
            <?php endif; ?>
            <?php if ($spk->status === 'Released'): ?>
                <a href="<?= base_url('production_issue/scan_issue?spk_no=' . $spk->spk_no) ?>"
                   class="btn btn-warning btn-sm">
                    <i class="fa fa-barcode"></i> Scan Issue Material
                </a>
            <?php endif; ?>
            <a href="<?= base_url('production_issue') ?>" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="160" class="fw-semibold">No SPK</td>
                        <td>: <?= htmlspecialchars($spk->spk_no) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">No Plan</td>
                        <td>: <a href="<?= base_url('production_planning/view/' . $spk->plan_no) ?>">
                            <?= htmlspecialchars($spk->plan_no) ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Produk FG</td>
                        <td>: <?= htmlspecialchars($spk->nm_produk_fg ?: $spk->produk_fg) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Tanggal SPK</td>
                        <td>: <?= date('d/m/Y', strtotime($spk->tgl_spk)) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="160" class="fw-semibold">Target Qty</td>
                        <td>: <?= number_format($spk->target_qty, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Due Date</td>
                        <td>: <?= $spk->due_date ? date('d/m/Y', strtotime($spk->due_date)) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Status</td>
                        <td>: <span class="badge bg-<?= $color ?>"><?= $spk->status ?></span></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Progress Scan</td>
                        <td>: <?= $scanned_coil ?> / <?= $total_coil ?> coil</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mt-2">
            <div class="d-flex justify-content-between mb-1">
                <small class="text-muted">Progress Issue Material</small>
                <small class="fw-bold"><?= $pct ?>%</small>
            </div>
            <div class="progress" style="height: 12px;">
                <div class="progress-bar bg-<?= $pct >= 100 ? 'success' : 'warning' ?>"
                     style="width: <?= $pct ?>%;" role="progressbar">
                </div>
            </div>
        </div>

        <?php if ($spk->catatan): ?>
            <div class="mt-2">
                <strong>Catatan:</strong> <?= nl2br(htmlspecialchars($spk->catatan)) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Coil -->
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">Detail Coil SPK</h6>
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
                        <th class="text-end">Berat Bersih (kg)</th>
                        <th class="text-center">Status Scan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($details)): ?>
                        <?php
                        // Buat map coil yang sudah di-scan sukses dari scan_status detail
                        $scanned_map = [];
                        foreach ($details as $d_check) {
                            if ($d_check->scan_status === 'scanned') {
                                $scanned_map[$d_check->no_coil] = true;
                            }
                        }
                        ?>
                        <?php foreach ($details as $i => $d): ?>
                            <?php $is_scanned = isset($scanned_map[$d->no_coil]); ?>
                            <tr class="<?= $is_scanned ? 'table-success' : '' ?>">
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= base_url('production_issue/histori_coil/' . urlencode($d->no_coil)) ?>">
                                        <?= htmlspecialchars($d->no_coil) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($d->no_ros ?: '-') ?></td>
                                <td><?= htmlspecialchars($d->nm_material ?: '-') ?></td>
                                <td class="text-end"><?= number_format($d->net_weight, 3) ?></td>
                                <td class="text-center">
                                    <?php if ($is_scanned): ?>
                                        <span class="badge bg-success"><i class="fa fa-check"></i> Issued</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Belum Scan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Tidak ada coil</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Log Scan -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Log Scan Barcode</h6>
        <span class="badge bg-secondary"><?= count($logs) ?> entri</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>No Coil</th>
                        <th class="text-center">Waktu Scan</th>
                        <th>Operator</th>
                        <th class="text-center">Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $i => $log): ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($log->no_coil) ?></td>
                                <td class="text-center"><?= date('d/m/Y H:i:s', strtotime($log->scan_time)) ?></td>
                                <td><?= htmlspecialchars($log->nama_user ?: 'User #' . $log->scan_user) ?></td>
                                <td class="text-center">
                                    <?php if ($log->status_scan === 'success'): ?>
                                        <span class="badge bg-success">Sukses</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log->keterangan ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada log scan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.btn-release-spk', function () {
    var spk_no = $(this).data('spk');
    swal({
        title: 'Release SPK?',
        text: 'SPK akan diubah ke status Released dan siap untuk scan issue material.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Ya, Release',
        cancelButtonText: 'Batal'
    }, function (isConfirm) {
        if (isConfirm) {
            $.post(siteurl + 'production_issue/process_release_spk/' + spk_no, function (res) {
                if (res.success) {
                    swal('Berhasil!', 'SPK berhasil di-release.', 'success');
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    swal('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                }
            }, 'json');
        }
    });
});
</script>
