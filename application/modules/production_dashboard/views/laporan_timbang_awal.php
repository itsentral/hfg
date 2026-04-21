<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Filter Laporan Timbang Awal</h5>
    </div>
    <div class="card-body">
        <form method="get" action="<?= base_url('production_dashboard/laporan_timbang_awal') ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">No SPK</label>
                    <select name="spk_no" class="form-select">
                        <option value="">-- Semua SPK --</option>
                        <?php foreach ($spk_list as $spk): ?>
                            <option value="<?= htmlspecialchars($spk->spk_no) ?>"
                                <?= ($filter['spk_no'] == $spk->spk_no) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($spk->spk_no) ?> — <?= htmlspecialchars($spk->produk_fg) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="tgl_dari" class="form-control" value="<?= htmlspecialchars($filter['tgl_dari']) ?>">
                </div>
                <div class="col-md-3">
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
        <h5 class="mb-0">Perbandingan Timbang Awal vs Packing List</h5>
        <a href="<?= base_url('production_dashboard') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm" id="tbl-timbang-awal">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">No</th>
                        <th>No Preweigh</th>
                        <th>No SPK</th>
                        <th>Barcode Coil</th>
                        <th class="text-end">Gross Aktual (kg)</th>
                        <th class="text-end">Gross PL (kg)</th>
                        <th class="text-end">Net PL (kg)</th>
                        <th class="text-end">Net Timbang (kg)</th>
                        <th class="text-end">Selisih Net (kg)</th>
                        <th class="text-end">Selisih (%)</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Tgl Buat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_laporan)): ?>
                        <tr>
                            <td colspan="12" class="text-center text-muted py-3">Tidak ada data untuk filter yang dipilih</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data_laporan as $i => $row): ?>
                            <?php
                                $selisih_pct = (float) $row->selisih_pct;
                                $is_exception = ($row->status === 'Exception');
                                $row_class = $is_exception ? 'table-danger' : '';
                            ?>
                            <tr class="<?= $row_class ?>">
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($row->preweigh_no) ?></td>
                                <td><?= htmlspecialchars($row->spk_no) ?></td>
                                <td><?= htmlspecialchars($row->barcode_coil) ?></td>
                                <td class="text-end"><?= number_format((float) $row->gross_actual, 3) ?></td>
                                <td class="text-end"><?= number_format((float) $row->gross_pl, 3) ?></td>
                                <td class="text-end"><?= number_format((float) $row->net_pl, 3) ?></td>
                                <td class="text-end"><?= number_format((float) $row->net_timbang_awal, 3) ?></td>
                                <td class="text-end <?= ((float) $row->selisih_net < 0) ? 'text-danger' : 'text-success' ?>">
                                    <?= number_format((float) $row->selisih_net, 3) ?>
                                </td>
                                <td class="text-end <?= ($selisih_pct > 0.05) ? 'text-danger fw-bold' : '' ?>">
                                    <?= number_format($selisih_pct * 100, 2) ?>%
                                </td>
                                <td class="text-center">
                                    <?php
                                        $badge_map = ['Draft' => 'secondary', 'Confirmed' => 'success', 'Exception' => 'danger'];
                                        $badge_color = isset($badge_map[$row->status]) ? $badge_map[$row->status] : 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badge_color ?>"><?= $row->status ?></span>
                                </td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($row->created_at)) ?></td>
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
    $('#tbl-timbang-awal').DataTable({
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
