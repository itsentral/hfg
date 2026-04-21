<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-map-marker"></i> Monitoring Coil in Production</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh">
                <i class="fa fa-refresh"></i> Refresh
            </button>
            <a href="<?= base_url('production_issue') ?>" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info py-2 mb-3">
            <i class="fa fa-info-circle"></i>
            Halaman ini menampilkan coil yang sedang berada di area produksi (status <strong>issued</strong>).
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm" id="tbl-coil-prod">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>No Coil</th>
                        <th>No SPK</th>
                        <th>Produk FG</th>
                        <th>Material</th>
                        <th class="text-end">Berat Bersih (kg)</th>
                        <th>Lokasi Asal</th>
                        <th>Lokasi Tujuan</th>
                        <th class="text-center">Waktu Mutasi</th>
                        <th class="text-center">Status SPK</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($coils)): ?>
                        <?php foreach ($coils as $i => $c): ?>
                            <?php
                            $status_map = [
                                'Draft'      => 'secondary',
                                'Released'   => 'primary',
                                'In Process' => 'warning',
                                'Submitted'  => 'info',
                                'Closed'     => 'success',
                                'Cancelled'  => 'danger',
                            ];
                            $color = isset($status_map[$c->status_spk]) ? $status_map[$c->status_spk] : 'secondary';
                            ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= base_url('production_issue/histori_coil/' . urlencode($c->no_coil)) ?>">
                                        <strong><?= htmlspecialchars($c->no_coil) ?></strong>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= base_url('production_issue/view/' . $c->spk_no) ?>">
                                        <?= htmlspecialchars($c->spk_no ?: '-') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($c->nm_produk_fg ?: '-') ?></td>
                                <td><?= htmlspecialchars($c->nm_material ?: '-') ?></td>
                                <td class="text-end"><?= $c->net_weight ? number_format($c->net_weight, 3) : '-' ?></td>
                                <td><?= htmlspecialchars($c->from_gudang ?: '-') ?></td>
                                <td><?= htmlspecialchars($c->to_gudang ?: '-') ?></td>
                                <td class="text-center">
                                    <?= $c->move_time ? date('d/m/Y H:i', strtotime($c->move_time)) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $color ?>"><?= htmlspecialchars($c->status_spk ?: '-') ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('production_issue/histori_coil/' . urlencode($c->no_coil)) ?>"
                                       class="btn btn-xs btn-info" title="Lihat Histori">
                                        <i class="fa fa-history"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                Tidak ada coil di area produksi saat ini
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#btn-refresh').on('click', function () {
        location.reload();
    });

    // Optional: DataTables untuk sorting/filter
    <?php if (!empty($coils)): ?>
    $('#tbl-coil-prod').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 25,
        order: [[8, 'desc']], // Sort by waktu mutasi desc
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ coil',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        }
    });
    <?php endif; ?>
});
</script>
