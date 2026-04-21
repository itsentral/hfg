<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Filter Laporan Selisih Delivery</h5>
    </div>
    <div class="card-body">
        <form method="get" action="<?= base_url('production_dashboard/laporan_delivery_discrepancy') ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Delivery Dari</label>
                    <input type="date" name="tgl_dari" class="form-control" value="<?= htmlspecialchars($filter['tgl_dari']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Delivery Sampai</label>
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
        <h5 class="mb-0">Laporan Selisih Berat Delivery (Estimasi vs Aktual)</h5>
        <a href="<?= base_url('production_dashboard') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm" id="tbl-discrepancy">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">No</th>
                        <th>No DO</th>
                        <th>Customer</th>
                        <th class="text-center">Tgl Delivery</th>
                        <th class="text-end">Estimasi Berat (kg)</th>
                        <th class="text-end">Berat Aktual (kg)</th>
                        <th class="text-end">Selisih (kg)</th>
                        <th class="text-end">Selisih (%)</th>
                        <th class="text-center">Tgl Timbang</th>
                        <th class="text-center">Status DO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_laporan)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">Tidak ada data untuk filter yang dipilih</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data_laporan as $i => $row): ?>
                            <?php
                                $selisih_pct = (float) $row->selisih_pct;
                                $is_over = ($selisih_pct > 0.03);
                                $row_class = $is_over ? 'table-warning' : '';
                                $badge_map = [
                                    'Draft'              => 'secondary',
                                    'Waiting Approval'   => 'warning',
                                    'Approved Exception' => 'success',
                                    'Shipped'            => 'dark',
                                    'Cancelled'          => 'danger',
                                ];
                                $badge_color = isset($badge_map[$row->status]) ? $badge_map[$row->status] : 'secondary';
                            ?>
                            <tr class="<?= $row_class ?>">
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= base_url('delivery_fg/view/' . $row->do_no) ?>">
                                        <?= htmlspecialchars($row->do_no) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($row->customer) ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($row->tgl_delivery)) ?></td>
                                <td class="text-end"><?= number_format((float) $row->total_estimasi_berat, 3) ?></td>
                                <td class="text-end"><?= $row->berat_aktual !== null ? number_format((float) $row->berat_aktual, 3) : '-' ?></td>
                                <td class="text-end <?= ((float) $row->selisih_kg < 0) ? 'text-danger' : 'text-success' ?>">
                                    <?= $row->selisih_kg !== null ? number_format((float) $row->selisih_kg, 3) : '-' ?>
                                </td>
                                <td class="text-end <?= $is_over ? 'text-danger fw-bold' : '' ?>">
                                    <?= $row->selisih_pct !== null ? number_format($selisih_pct * 100, 2) . '%' : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?= $row->tgl_timbang ? date('d/m/Y H:i', strtotime($row->tgl_timbang)) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $badge_color ?>"><?= $row->status ?></span>
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
    $('#tbl-discrepancy').DataTable({
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
