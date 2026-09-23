<style>
    th.actual-head { background-color: #cfe2ff; }
    th.pl-head { background-color: #fff3cd; }
    th.selisih-head { background-color: #d1e7dd; }
</style>

<div class="card mb-3">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-3"><strong>Unpack No</strong><br><?= htmlspecialchars($header['unpack_no']) ?></div>
            <div class="col-md-3"><strong>Pack</strong><br><span class="badge bg-primary"><?= htmlspecialchars($header['pack_code']) ?></span></div>
            <div class="col-md-3"><strong>Tanggal Unpack</strong><br><?= date('d/m/Y H:i', strtotime($header['tgl_unpack'])) ?></div>
            <div class="col-md-3"><strong>Status</strong><br>
                <span class="badge <?= $header['status'] == 'Confirmed' ? 'bg-success' : ($header['status'] == 'Cancelled' ? 'bg-danger' : 'bg-secondary') ?>">
                    <?= htmlspecialchars($header['status']) ?>
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3"><strong>Kulit</strong><br><?= htmlspecialchars($header['catatan_kulit'] ?: '-') ?></div>
            <div class="col-md-3"><strong>Clamp / ring</strong><br><?= htmlspecialchars($header['catatan_clamp_ring'] ?: '-') ?></div>
            <div class="col-md-6"><strong>Catatan</strong><br><?= nl2br(htmlspecialchars($header['catatan'] ?: '-')) ?></div>
        </div>
    </div>
</div>

<!-- Ringkasan level pack -->
<div class="card mb-3">
    <div class="card-header bg-light fw-bold">Ringkasan Pack</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center align-middle">
                <thead>
                    <tr>
                        <th colspan="2" class="actual-head">Actual Weight</th>
                        <th colspan="2" class="pl-head">Packing List</th>
                        <th colspan="2" class="selisih-head">Selisih Actual vs PL</th>
                    </tr>
                    <tr>
                        <th class="actual-head">Nett Weight</th>
                        <th class="actual-head">Gross Weight</th>
                        <th class="pl-head">Nett Weight</th>
                        <th class="pl-head">Gross Weight</th>
                        <th class="selisih-head">Nett</th>
                        <th class="selisih-head">Gross</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $net_sel = (float) $header['net_weight_actual'] - (float) $header['net_weight_pl'];
                    $gross_sel = (float) $header['gross_weight_actual'] - (float) $header['gross_weight_pl'];
                    ?>
                    <tr>
                        <td class="text-end"><?= number_format((float) $header['net_weight_actual'], 2) ?></td>
                        <td class="text-end"><?= number_format((float) $header['gross_weight_actual'], 2) ?></td>
                        <td class="text-end"><?= number_format((float) $header['net_weight_pl'], 2) ?></td>
                        <td class="text-end"><?= number_format((float) $header['gross_weight_pl'], 2) ?></td>
                        <td class="text-end"><?= number_format($net_sel, 2) ?></td>
                        <td class="text-end"><?= number_format($gross_sel, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Per material + baby coil -->
<?php foreach ($materials as $i => $m): ?>
    <div class="card mb-3">
        <div class="card-header bg-light">
            <strong>Material #<?= $i + 1 ?>:</strong> <?= htmlspecialchars($m['nm_material']) ?>
            &mdash; <?= htmlspecialchars($m['no_coil']) ?> | <?= htmlspecialchars($m['kode_internal']) ?>
            <span class="float-end">Jumlah Roll: <span class="badge bg-success"><?= (int) $m['jumlah_coil_roll'] ?></span></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center align-middle">
                    <thead>
                        <tr>
                            <th width="4%">No</th>
                            <th>No Coil</th>
                            <th>Babycoil</th>
                            <th class="actual-head">Net weight per Roll (Actual)</th>
                            <th class="actual-head">Gross Weight per Roll (Actual)</th>
                            <th class="pl-head">Net weight per Roll (PL)</th>
                            <th class="pl-head">Gross Weight per Roll (PL)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($m['babies'])): ?>
                            <tr><td colspan="7" class="text-muted">Tidak ada baby coil.</td></tr>
                        <?php else: ?>
                            <?php foreach ($m['babies'] as $j => $b): ?>
                                <tr>
                                    <td><?= $j + 1 ?></td>
                                    <td><?= htmlspecialchars($b['no_coil']) ?></td>
                                    <td><?= htmlspecialchars($b['babycoil_code']) ?></td>
                                    <td class="text-end"><?= number_format((float) $b['net_weight_actual'], 2) ?></td>
                                    <td class="text-end"><?= number_format((float) $b['gross_weight_actual'], 2) ?></td>
                                    <td class="text-end"><?= number_format((float) $b['net_weight_pl'], 2) ?></td>
                                    <td class="text-end"><?= number_format((float) $b['gross_weight_pl'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold table-light">
                            <td colspan="3" class="text-end">Total</td>
                            <td class="text-end"><?= number_format((float) $m['net_weight_actual'], 2) ?></td>
                            <td class="text-end"><?= number_format((float) $m['gross_weight_actual'], 2) ?></td>
                            <td class="text-end"><?= number_format((float) $m['net_weight_pl'], 2) ?></td>
                            <td class="text-end"><?= number_format((float) $m['gross_weight_pl'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<a href="<?= site_url('unpack_coil') ?>" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
