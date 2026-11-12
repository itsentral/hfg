<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 text-primary fw-bold">
            <i class="fa fa-warehouse me-2"></i> Stok FG Terkini
        </h5>
        <div class="d-flex gap-2">
            <a href="<?= base_url('fg_warehouse/export_excel_stok') ?>" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </a>
            <a href="<?= base_url('fg_warehouse/kartu_stok') ?>" class="btn btn-secondary btn-sm">
                <i class="fa fa-book me-1"></i> Kartu Stok
            </a>
            <a href="<?= base_url('fg_warehouse') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-list me-1"></i> FG Receipt
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($stok_list)): ?>
            <div class="alert alert-info">Belum ada data stok FG.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle" id="tbl-stok">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th class="text-end">Qty Stok (pcs)</th>
                        <th class="text-end">Total Berat (kg)</th>
                        <th class="text-end">Berat Referensi (kg/pcs)</th>
                        <th class="text-end">Costbook (Rp/pcs)</th>
                        <th class="text-end">Total Nilai (Rp)</th>
                        <th class="text-center">Last Update</th>
                        <th class="text-center no-sort" width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stok_list as $i => $stok): ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><strong class="text-primary"><?= htmlspecialchars($stok->produk_fg) ?></strong></td>
                        <td><?= htmlspecialchars($stok->nm_produk_fg ? $stok->nm_produk_fg : '-') ?></td>
                        <td class="text-end">
                            <?php if ((float) $stok->qty_stok <= 0): ?>
                                <span class="badge bg-secondary">0.00</span>
                            <?php else: ?>
                                <span class="badge bg-success fs-6"><?= number_format((float) $stok->qty_stok, 2) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold"><?= number_format((float) $stok->total_berat, 3) ?></td>
                        <td class="text-end">
                            <?php if ((float) $stok->berat_referensi > 0): ?>
                                <span class="badge bg-info text-dark fs-6">
                                    <?= number_format((float) $stok->berat_referensi, 4) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ((float) $stok->costbook > 0): ?>
                                <strong>Rp <?= number_format((float) $stok->costbook, 2) ?></strong>
                            <?php else: ?>
                                <span class="text-muted">Rp 0.00</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-success fw-bold">
                            Rp <?= number_format((float) $stok->total_nilai, 2) ?>
                        </td>
                        <td class="text-center">
                            <small class="text-muted"><?= $stok->last_update ? date('d-m-Y H:i', strtotime($stok->last_update)) : '-' ?></small>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url('fg_warehouse/kartu_stok/' . urlencode($stok->produk_fg)) ?>"
                               class="btn btn-sm btn-info text-white" title="Lihat Kartu Stok">
                                <i class="fa fa-history me-1"></i> Kartu Stok
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
$(document).ready(function () {
    $('#tbl-stok').DataTable({
        autoWidth: false,
        responsive: true,
        columnDefs: [{ targets: 'no-sort', orderable: false }],
        sPaginationType: 'simple_numbers',
        iDisplayLength: 25,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    });
});
</script>
