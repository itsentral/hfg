<?php defined('BASEPATH') || exit('No direct script access allowed');

$status_map = [
    'Draft'     => 'secondary',
    'Confirmed' => 'success',
    'Exception' => 'danger',
];
$status_color = isset($status_map[$preweigh->status]) ? $status_map[$preweigh->status] : 'secondary';
?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Timbang Awal Coil</h5>
        <div class="d-flex gap-2">
            <a href="<?= base_url('production_weighing/perbandingan/' . $preweigh->spk_no) ?>"
               class="btn btn-secondary btn-sm">
                <i class="fa fa-bar-chart"></i> Perbandingan SPK
            </a>
            <a href="<?= base_url('production_weighing') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="180" class="fw-semibold">No Timbang</td>
                        <td>: <strong><?= htmlspecialchars($preweigh->preweigh_no) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">No SPK</td>
                        <td>: <?= htmlspecialchars($preweigh->spk_no) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">No Coil</td>
                        <td>: <?= htmlspecialchars($preweigh->no_coil) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Operator</td>
                        <td>: <?= htmlspecialchars($preweigh->nama_user ?: '-') ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Tanggal Timbang</td>
                        <td>: <?= date('d/m/Y H:i', strtotime($preweigh->created_at)) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Status</td>
                        <td>: <span class="badge bg-<?= $status_color ?> fs-6"><?= $preweigh->status ?></span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Komponen Berat -->
<?php if ($components): ?>
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-balance-scale"></i> Komponen Berat Timbang</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Komponen</th>
                            <th class="text-end">Berat (kg)</th>
                            <th class="text-center">Masuk Net?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Berat Kulit (packaging luar)</td>
                            <td class="text-end"><?= number_format($components->berat_kulit, 3) ?></td>
                            <td class="text-center"><span class="badge bg-secondary">Tidak</span></td>
                        </tr>
                        <tr>
                            <td>Berat Clamp / Ring</td>
                            <td class="text-end"><?= number_format($components->berat_clamp_ring, 3) ?></td>
                            <td class="text-center"><span class="badge bg-secondary">Tidak</span></td>
                        </tr>
                        <tr>
                            <td>Berat Coil + Tong</td>
                            <td class="text-end"><?= number_format($components->berat_coil_tong, 3) ?></td>
                            <td class="text-center"><span class="badge bg-success">Ya</span></td>
                        </tr>
                        <tr>
                            <td>Berat Cover Wrapping</td>
                            <td class="text-end"><?= number_format($components->berat_cover_wrapping, 3) ?></td>
                            <td class="text-center"><span class="badge bg-success">Ya</span></td>
                        </tr>
                        <tr class="table-warning fw-bold">
                            <td>Net Weight Timbang Awal</td>
                            <td class="text-end"><?= number_format($components->net_timbang_awal, 3) ?></td>
                            <td class="text-center">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Perbandingan PL vs Aktual -->
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-exchange"></i> Perbandingan Packing List vs Timbang Aktual</h6>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-2">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small mb-1">Gross PL</div>
                    <div class="fs-5 fw-bold"><?= number_format($preweigh->gross_pl, 3) ?></div>
                    <div class="text-muted small">kg</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small mb-1">Gross Aktual</div>
                    <div class="fs-5 fw-bold"><?= number_format($preweigh->gross_actual, 3) ?></div>
                    <div class="text-muted small">kg</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small mb-1">Selisih Gross</div>
                    <?php $sg_color = $preweigh->selisih_gross < 0 ? 'text-danger' : ($preweigh->selisih_gross > 0 ? 'text-warning' : 'text-success'); ?>
                    <div class="fs-5 fw-bold <?= $sg_color ?>"><?= number_format($preweigh->selisih_gross, 3) ?></div>
                    <div class="text-muted small">kg</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small mb-1">Net PL</div>
                    <div class="fs-5 fw-bold"><?= number_format($preweigh->net_pl, 3) ?></div>
                    <div class="text-muted small">kg</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small mb-1">Net Timbang Awal</div>
                    <div class="fs-5 fw-bold"><?= $components ? number_format($components->net_timbang_awal, 3) : '-' ?></div>
                    <div class="text-muted small">kg</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="border rounded p-3 h-100 <?= $preweigh->status === 'Exception' ? 'border-danger bg-danger bg-opacity-10' : '' ?>">
                    <div class="text-muted small mb-1">Selisih Net / Deviasi</div>
                    <?php $sn_color = $preweigh->status === 'Exception' ? 'text-danger' : 'text-success'; ?>
                    <?php $selisih_net = $components ? $components->selisih_net : 0; ?>
                    <?php $selisih_net_pct = $components ? $components->selisih_net_pct : 0; ?>
                    <div class="fs-5 fw-bold <?= $sn_color ?>"><?= number_format($selisih_net, 3) ?> kg</div>
                    <div class="fw-bold <?= $sn_color ?>"><?= number_format($selisih_net_pct * 100, 2) ?>%</div>
                </div>
            </div>
        </div>

        <?php if ($preweigh->status === 'Exception'): ?>
            <div class="alert alert-danger mt-3">
                <i class="fa fa-exclamation-triangle fa-lg"></i>
                <strong>Exception!</strong> Selisih berat melebihi toleransi yang ditetapkan.
                Notifikasi telah dikirim ke Supervisor/QC untuk tindak lanjut.
            </div>
        <?php endif; ?>
    </div>
</div>
