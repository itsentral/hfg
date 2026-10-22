<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold"><i class="fa fa-sitemap text-primary me-2"></i> Detail BOM</h5>
        <div class="d-flex gap-2">
            <a href="<?= base_url('bom/edit/' . $bom->id) ?>" class="btn btn-warning btn-sm">
                <i class="fa fa-edit me-1"></i> Edit
            </a>
            <a href="<?= base_url('bom') ?>" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="max-width: 600px;">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td width="160" class="fw-bold text-muted">Kode Produk</td>
                    <td>: <strong class="fs-6 text-dark"><?= htmlspecialchars($bom->id_produk) ?></strong></td>
                </tr>
                <tr>
                    <td class="fw-bold text-muted">Nama Produk</td>
                    <td>: <?= htmlspecialchars($bom->nm_produk ?: '-') ?></td>
                </tr>
                <tr>
                    <td class="fw-bold text-muted">Trade Name</td>
                    <td>: <?= htmlspecialchars(!empty($bom->trade_name) ? $bom->trade_name : ($bom->nm_produk ?: '-')) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold text-muted">Latest Update</td>
                    <td>: <?= !empty($bom->updated_at) ? date('d/m/Y H:i', strtotime($bom->updated_at)) : (!empty($bom->created_at) ? date('d/m/Y H:i', strtotime($bom->created_at)) : '-') ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Card Tabel: Daftar Varian Tebal Sesuai Mockup BA -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-secondary">
            <i class="fa fa-list me-2"></i> Daftar Varian Tebal (<?= count($details) ?> item)
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th width="160">No. Uniq ID</th>
                        <th width="180">Kode Material</th>
                        <th>Nama Material</th>
                        <th class="text-end" width="130">Varian Tebal</th>
                        <th class="text-end" width="130">Lebar Coil</th>
                        <th class="text-end" width="180">Berat/Meter (kg/m)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($details)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada varian material</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($details as $i => $d): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td>
                                <span class="badge bg-primary fs-6"><?= htmlspecialchars(!empty($d->no_uniq_id) ? $d->no_uniq_id : 'Type 001.' . ($i + 1)) ?></span>
                            </td>
                            <td><code><?= htmlspecialchars($d->id_material) ?></code></td>
                            <td><?= htmlspecialchars($d->nm_material ?: '-') ?></td>
                            <td class="text-end"><?= number_format(isset($d->thickness) ? (float)$d->thickness : 0, 3, '.', '') ?></td>
                            <td class="text-end"><?= number_format(isset($d->width) ? (float)$d->width : 0, 2, '.', '') ?></td>
                            <td class="text-end fw-bold text-success"><?= number_format(isset($d->berat_per_meter) ? (float)$d->berat_per_meter : 0, 4, '.', '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
