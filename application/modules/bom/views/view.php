<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-sitemap"></i> Detail BOM</h5>
        <div class="d-flex gap-2">
            <a href="<?= base_url('bom/edit/' . $bom->id) ?>" class="btn btn-warning btn-sm">
                <i class="fa fa-edit"></i> Edit
            </a>
            <a href="<?= base_url('bom') ?>" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="160" class="fw-semibold">Kode Produk</td>
                        <td>: <strong><?= htmlspecialchars($bom->id_produk) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Nama Produk</td>
                        <td>: <?= htmlspecialchars($bom->nm_produk ?: '-') ?></td>
                    </tr>
                    <?php if ($bom->keterangan): ?>
                    <tr>
                        <td class="fw-semibold">Keterangan</td>
                        <td>: <?= nl2br(htmlspecialchars($bom->keterangan)) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="fw-semibold">Dibuat</td>
                        <td>: <?= date('d/m/Y H:i', strtotime($bom->created_at)) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Material -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Daftar Material (<?= count($details) ?> item)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Kode Material</th>
                        <th>Nama Material</th>
                        <th>Trade Name</th>
                        <th class="text-end">Qty</th>
                        <th>Satuan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($details)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada material</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($details as $i => $d): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td><code><?= htmlspecialchars($d->id_material) ?></code></td>
                            <td><?= htmlspecialchars($d->nm_material ?: '-') ?></td>
                            <td>
                                <?php if ($d->trade_name): ?>
                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($d->trade_name) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= number_format($d->qty, 4) ?></td>
                            <td><?= htmlspecialchars($d->nm_unit ?: $d->id_unit ?: '-') ?></td>
                            <td><?= htmlspecialchars($d->keterangan ?: '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
