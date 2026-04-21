<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-history"></i> Histori Mutasi Coil</h5>
        <a href="<?= base_url('production_issue/monitoring_coil') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <!-- Form Cari Coil -->
        <form method="GET" action="<?= base_url('production_issue/histori_coil') ?>" class="mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">No Coil</label>
                    <input type="text" name="no_coil" class="form-control"
                           value="<?= htmlspecialchars($no_coil ?: '') ?>"
                           placeholder="Masukkan no coil atau scan barcode...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-search"></i> Cari
                    </button>
                </div>
            </div>
        </form>

        <?php if ($no_coil): ?>
            <div class="alert alert-info py-2 mb-3">
                <i class="fa fa-barcode"></i>
                Menampilkan histori untuk coil: <strong><?= htmlspecialchars($no_coil) ?></strong>
            </div>

            <?php if (!empty($history)): ?>
                <!-- Timeline -->
                <div class="timeline-container mb-4">
                    <?php foreach ($history as $i => $h): ?>
                        <div class="d-flex mb-3">
                            <!-- Ikon timeline -->
                            <div class="flex-shrink-0 me-3 text-center" style="width: 40px;">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                     style="width: 36px; height: 36px; font-size: 14px;">
                                    <?= $i + 1 ?>
                                </div>
                                <?php if ($i < count($history) - 1): ?>
                                    <div style="width: 2px; height: 30px; background: #dee2e6; margin: 4px auto;"></div>
                                <?php endif; ?>
                            </div>
                            <!-- Konten -->
                            <div class="flex-grow-1">
                                <div class="card border-start border-primary border-3">
                                    <div class="card-body py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong class="text-primary">
                                                    <?= htmlspecialchars($h->from_gudang) ?>
                                                    <i class="fa fa-arrow-right mx-1"></i>
                                                    <?= htmlspecialchars($h->to_gudang) ?>
                                                </strong>
                                                <div class="text-muted small mt-1">
                                                    SPK: <a href="<?= base_url('production_issue/view/' . $h->spk_no) ?>">
                                                        <?= htmlspecialchars($h->spk_no) ?>
                                                    </a>
                                                    <?php if ($h->nm_produk_fg): ?>
                                                        — <?= htmlspecialchars($h->nm_produk_fg) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-muted small">
                                                    Operator: <?= htmlspecialchars($h->nama_user ?: 'User #' . $h->move_user) ?>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="text-muted small">
                                                    <?= date('d/m/Y', strtotime($h->move_time)) ?>
                                                </div>
                                                <div class="fw-bold">
                                                    <?= date('H:i:s', strtotime($h->move_time)) ?>
                                                </div>
                                                <?php
                                                $status_map = [
                                                    'Draft'      => 'secondary',
                                                    'Released'   => 'primary',
                                                    'In Process' => 'warning',
                                                    'Submitted'  => 'info',
                                                    'Closed'     => 'success',
                                                    'Cancelled'  => 'danger',
                                                ];
                                                $color = isset($status_map[$h->status_spk]) ? $status_map[$h->status_spk] : 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $color ?> mt-1">
                                                    <?= htmlspecialchars($h->status_spk ?: '-') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tabel Ringkasan -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Tabel Riwayat Mutasi</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="50">No</th>
                                        <th>No SPK</th>
                                        <th>Produk FG</th>
                                        <th>Dari Gudang</th>
                                        <th>Ke Gudang</th>
                                        <th class="text-center">Waktu Mutasi</th>
                                        <th>Operator</th>
                                        <th class="text-center">Status SPK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $i => $h): ?>
                                        <?php
                                        $status_map = [
                                            'Draft'      => 'secondary',
                                            'Released'   => 'primary',
                                            'In Process' => 'warning',
                                            'Submitted'  => 'info',
                                            'Closed'     => 'success',
                                            'Cancelled'  => 'danger',
                                        ];
                                        $color = isset($status_map[$h->status_spk]) ? $status_map[$h->status_spk] : 'secondary';
                                        ?>
                                        <tr>
                                            <td class="text-center"><?= $i + 1 ?></td>
                                            <td>
                                                <a href="<?= base_url('production_issue/view/' . $h->spk_no) ?>">
                                                    <?= htmlspecialchars($h->spk_no) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($h->nm_produk_fg ?: '-') ?></td>
                                            <td><?= htmlspecialchars($h->from_gudang) ?></td>
                                            <td><?= htmlspecialchars($h->to_gudang) ?></td>
                                            <td class="text-center">
                                                <?= date('d/m/Y H:i:s', strtotime($h->move_time)) ?>
                                            </td>
                                            <td><?= htmlspecialchars($h->nama_user ?: 'User #' . $h->move_user) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $color ?>">
                                                    <?= htmlspecialchars($h->status_spk ?: '-') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    Tidak ada riwayat mutasi untuk coil <strong><?= htmlspecialchars($no_coil) ?></strong>.
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="fa fa-search fa-3x mb-3 d-block"></i>
                Masukkan no coil untuk melihat riwayat mutasi
            </div>
        <?php endif; ?>
    </div>
</div>
