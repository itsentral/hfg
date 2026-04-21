<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Filter Laporan Berat FG</h5>
    </div>
    <div class="card-body">
        <form method="get" action="<?= base_url('production_dashboard/laporan_berat_fg') ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="tgl_dari" class="form-control" value="<?= htmlspecialchars($filter['tgl_dari']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="tgl_sampai" class="form-control" value="<?= htmlspecialchars($filter['tgl_sampai']) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-search"></i> Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Laporan Berat Referensi FG per Produk</h5>
        <a href="<?= base_url('production_dashboard') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm" id="tbl-berat-fg">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">No</th>
                        <th>Kode Produk FG</th>
                        <th class="text-end">Qty Stok Saat Ini</th>
                        <th class="text-end">Total Berat Stok (kg)</th>
                        <th class="text-end">Berat Referensi Terkini (kg/pcs)</th>
                        <th class="text-end">Berat Referensi Historis (kg/pcs)</th>
                        <th class="text-end">Qty Stok Historis</th>
                        <th class="text-end">Total Berat Historis (kg)</th>
                        <th class="text-center">Effective Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_laporan)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">Tidak ada data untuk filter yang dipilih</td>
                        </tr>
                    <?php else: ?>
                        <?php
                            $prev_produk = null;
                            $no = 0;
                        ?>
                        <?php foreach ($data_laporan as $row): ?>
                            <?php
                                $is_new_produk = ($row->produk_fg !== $prev_produk);
                                if ($is_new_produk) {
                                    $no++;
                                    $prev_produk = $row->produk_fg;
                                }
                                $row_class = $is_new_produk ? 'table-light fw-semibold' : '';
                            ?>
                            <tr class="<?= $row_class ?>">
                                <td class="text-center"><?= $is_new_produk ? $no : '' ?></td>
                                <td><?= $is_new_produk ? htmlspecialchars($row->produk_fg) : '' ?></td>
                                <td class="text-end"><?= $is_new_produk ? number_format((float) $row->qty_stok, 2) : '' ?></td>
                                <td class="text-end"><?= $is_new_produk ? number_format((float) $row->total_berat, 3) : '' ?></td>
                                <td class="text-end text-primary fw-bold">
                                    <?= $is_new_produk ? number_format((float) $row->berat_referensi_terkini, 4) : '' ?>
                                </td>
                                <td class="text-end">
                                    <?= $row->berat_referensi_historis !== null ? number_format((float) $row->berat_referensi_historis, 4) : '-' ?>
                                </td>
                                <td class="text-end">
                                    <?= $row->qty_stok_historis !== null ? number_format((float) $row->qty_stok_historis, 2) : '-' ?>
                                </td>
                                <td class="text-end">
                                    <?= $row->total_berat_historis !== null ? number_format((float) $row->total_berat_historis, 3) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?= $row->effective_date ? date('d/m/Y H:i', strtotime($row->effective_date)) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($data_laporan)): ?>
            <div class="text-muted small mt-2">Total: <?= count($data_laporan) ?> record</div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
$(document).ready(function () {
    $('#tbl-berat-fg').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        autoWidth: false,
        responsive: true,
        iDisplayLength: 25,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        sPaginationType: 'simple_numbers',
        columnDefs: [{ targets: 0, orderable: false }],
    });
});
</script>
