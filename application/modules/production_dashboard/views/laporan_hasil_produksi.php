<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Filter Laporan Hasil Produksi</h5>
    </div>
    <div class="card-body">
        <form method="get" action="<?= base_url('production_dashboard/laporan_hasil_produksi') ?>">
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
        <h5 class="mb-0">Laporan Hasil Produksi — Breakdown Yield per Kategori</h5>
        <a href="<?= base_url('production_dashboard') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm" id="tbl-hasil-produksi">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" rowspan="2">No</th>
                        <th rowspan="2">No Laporan</th>
                        <th rowspan="2">No SPK</th>
                        <th rowspan="2">Barcode Coil</th>
                        <th class="text-end" rowspan="2">Total Berat Coil (kg)</th>
                        <th class="text-center" colspan="2">FG</th>
                        <th class="text-center" colspan="2">KW2 Internal</th>
                        <th class="text-center" colspan="2">KW2 Supplier</th>
                        <th class="text-end" rowspan="2">Reject (kg)</th>
                        <th class="text-end" rowspan="2">NG Supplier (kg)</th>
                        <th class="text-end" rowspan="2">NG Internal (kg)</th>
                        <th class="text-end" rowspan="2">Waste (kg)</th>
                        <th class="text-end" rowspan="2">Plat BS (kg)</th>
                        <th class="text-center" rowspan="2">Status</th>
                    </tr>
                    <tr>
                        <th class="text-end">kg</th>
                        <th class="text-end">Yield%</th>
                        <th class="text-end">kg</th>
                        <th class="text-end">Yield%</th>
                        <th class="text-end">kg</th>
                        <th class="text-end">Yield%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_laporan)): ?>
                        <tr>
                            <td colspan="17" class="text-center text-muted py-3">Tidak ada data untuk filter yang dipilih</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data_laporan as $i => $row): ?>
                            <?php
                                $badge_map = [
                                    'Draft'        => 'secondary',
                                    'Submitted'    => 'primary',
                                    'Approved'     => 'success',
                                    'Rejected'     => 'danger',
                                    'Posted to FG' => 'dark',
                                ];
                                $badge_color = isset($badge_map[$row->status]) ? $badge_map[$row->status] : 'secondary';
                            ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($row->report_no) ?></td>
                                <td><?= htmlspecialchars($row->spk_no) ?></td>
                                <td><?= htmlspecialchars($row->barcode_coil) ?></td>
                                <td class="text-end"><?= number_format((float) $row->total_berat_coil, 3) ?></td>
                                <td class="text-end"><?= number_format((float) $row->fg_kg, 3) ?></td>
                                <td class="text-end text-success fw-bold"><?= number_format((float) $row->yield_fg_pct, 2) ?>%</td>
                                <td class="text-end"><?= number_format((float) $row->kw2_internal_kg, 3) ?></td>
                                <td class="text-end"><?= number_format((float) $row->yield_kw2_internal_pct, 2) ?>%</td>
                                <td class="text-end"><?= number_format((float) $row->kw2_supplier_kg, 3) ?></td>
                                <td class="text-end"><?= number_format((float) $row->yield_kw2_supplier_pct, 2) ?>%</td>
                                <td class="text-end text-danger"><?= number_format((float) $row->reject_supplier, 3) ?></td>
                                <td class="text-end text-warning"><?= number_format((float) $row->ng_supplier, 3) ?></td>
                                <td class="text-end"><?= number_format((float) $row->ng_internal, 3) ?></td>
                                <td class="text-end"><?= number_format((float) $row->waste_potong, 3) ?></td>
                                <td class="text-end"><?= number_format((float) $row->plat_bs, 3) ?></td>
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
    $('#tbl-hasil-produksi').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        autoWidth: false,
        responsive: false,
        scrollX: true,
        iDisplayLength: 25,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        sPaginationType: 'simple_numbers',
        columnDefs: [{ targets: 0, orderable: false }],
    });
});
</script>
