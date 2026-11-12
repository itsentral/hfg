<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<!-- KPI Summary Cards Breakdown 4 Kategori -->
<div class="row mb-3 g-2">
    <!-- KW 1 -->
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm h-100">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small fw-bold opacity-75">1. Finish Good KW 1</div>
                        <div class="fs-4 fw-bold mt-1"><?= number_format(isset($summary->kw1_qty) ? (float) $summary->kw1_qty : 0, 2) ?> <small class="fs-6 opacity-75">pcs</small></div>
                        <div class="small opacity-75">
                            <i class="fa fa-weight-hanging me-1"></i><?= number_format(isset($summary->kw1_berat) ? (float) $summary->kw1_berat : 0, 2) ?> kg
                            &bull; <?= (int)(isset($summary->kw1_sku) ? $summary->kw1_sku : 0) ?> SKU
                        </div>
                        <div class="small fw-semibold mt-1">Rp <?= number_format(isset($summary->kw1_nilai) ? (float) $summary->kw1_nilai : 0, 2) ?></div>
                    </div>
                    <div class="fs-1 opacity-50"><i class="fa fa-star"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- KW 2 -->
    <div class="col-md-3">
        <div class="card bg-warning text-dark shadow-sm h-100">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small fw-bold text-muted">2. Finish Good KW 2</div>
                        <div class="fs-4 fw-bold mt-1 text-dark"><?= number_format(isset($summary->kw2_qty) ? (float) $summary->kw2_qty : 0, 2) ?> <small class="fs-6 opacity-75">pcs</small></div>
                        <div class="small text-muted">
                            <i class="fa fa-weight-hanging me-1"></i><?= number_format(isset($summary->kw2_berat) ? (float) $summary->kw2_berat : 0, 2) ?> kg
                            &bull; <?= (int)(isset($summary->kw2_sku) ? $summary->kw2_sku : 0) ?> SKU
                        </div>
                        <div class="small fw-bold text-dark mt-1">Rp <?= number_format(isset($summary->kw2_nilai) ? (float) $summary->kw2_nilai : 0, 2) ?></div>
                    </div>
                    <div class="fs-1 opacity-50"><i class="fa fa-cubes"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tong Coil -->
    <div class="col-md-3">
        <div class="card bg-info text-white shadow-sm h-100">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small fw-bold opacity-75">3. Tong Coil</div>
                        <div class="fs-4 fw-bold mt-1"><?= number_format(isset($summary->tc_berat) ? (float) $summary->tc_berat : 0, 2) ?> <small class="fs-6 opacity-75">kg</small></div>
                        <div class="small opacity-75">
                            <i class="fa fa-circle-o me-1"></i><?= number_format(isset($summary->tc_qty) ? (float) $summary->tc_qty : 0) ?> roll/pcs
                            &bull; <?= (int)(isset($summary->tc_sku) ? $summary->tc_sku : 0) ?> SKU
                        </div>
                        <div class="small fw-semibold mt-1">Rp <?= number_format(isset($summary->tc_nilai) ? (float) $summary->tc_nilai : 0, 2) ?></div>
                    </div>
                    <div class="fs-1 opacity-50"><i class="fa fa-circle-o-notch"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scrap / Waste -->
    <div class="col-md-3">
        <div class="card bg-danger text-white shadow-sm h-100">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small fw-bold opacity-75">4. Scrap / Waste</div>
                        <div class="fs-4 fw-bold mt-1"><?= number_format(isset($summary->scrap_berat) ? (float) $summary->scrap_berat : 0, 2) ?> <small class="fs-6 opacity-75">kg</small></div>
                        <div class="small opacity-75">
                            <i class="fa fa-recycle me-1"></i><?= (int)(isset($summary->scrap_sku) ? $summary->scrap_sku : 0) ?> Item
                            &bull; Persediaan 40%
                        </div>
                        <div class="small fw-semibold mt-1">Rp <?= number_format(isset($summary->scrap_nilai) ? (float) $summary->scrap_nilai : 0, 2) ?></div>
                    </div>
                    <div class="fs-1 opacity-50"><i class="fa fa-trash"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Card with Bootstrap 5 Tabs -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-primary fw-bold">
                <i class="fa fa-warehouse me-2"></i> Rekapitulasi Stok Produk & Waste Hasil Produksi
            </h5>
            <div class="d-flex gap-2">
                <a href="<?= base_url('fg_stock/export_excel_stok') ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </a>
                <a href="<?= base_url('fg_stock/history') ?>" class="btn btn-secondary btn-sm">
                    <i class="fa fa-book me-1"></i> Kartu Stok
                </a>
            </div>
        </div>

        <!-- TAB NAVIGATION -->
        <ul class="nav nav-tabs card-header-tabs" id="tabFgStock" role="tablist">
            <li class="nav-item">
                <a class="nav-link active fw-bold" id="tab-kw1-tab" data-bs-toggle="tab" href="#tab-kw1" role="tab">
                    <i class="fa fa-star text-primary me-1"></i> Finish Good KW 1
                    <span class="badge bg-primary ms-1"><?= count($stok_kw1) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold" id="tab-kw2-tab" data-bs-toggle="tab" href="#tab-kw2" role="tab">
                    <i class="fa fa-cubes text-warning me-1"></i> Finish Good KW 2
                    <span class="badge bg-warning text-dark ms-1"><?= count($stok_kw2) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold" id="tab-tong-coil-tab" data-bs-toggle="tab" href="#tab-tong-coil" role="tab">
                    <i class="fa fa-circle-o text-info me-1"></i> Tong Coil
                    <span class="badge bg-info text-dark ms-1"><?= count($stok_tong_coil) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold" id="tab-scrap-tab" data-bs-toggle="tab" href="#tab-scrap" role="tab">
                    <i class="fa fa-trash text-danger me-1"></i> Scrap / Waste
                    <span class="badge bg-danger ms-1"><?= count($stok_scrap) ?></span>
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body pt-3">
        <div class="tab-content" id="tabFgStockContent">

            <!-- ========================================== -->
            <!-- TAB 1: FINISH GOOD KW 1                    -->
            <!-- ========================================== -->
            <div class="tab-pane fade show active" id="tab-kw1" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-kw1">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center" width="40">No</th>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-end">Qty Stok (pcs)</th>
                                <th class="text-end">Total Berat (kg)</th>
                                <th class="text-end">Berat Ref (kg/pcs)</th>
                                <th class="text-end">Costbook (Rp/pcs)</th>
                                <th class="text-end">Total Nilai HPP (Rp)</th>
                                <th class="text-center">Terakhir Update</th>
                                <th class="text-center no-sort" width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stok_kw1 as $i => $s): ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><strong class="text-primary"><?= htmlspecialchars($s->produk_fg) ?></strong></td>
                                <td><?= htmlspecialchars($s->nm_produk_fg ?: '-') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($s->sub_kategori ?: 'KW 1') ?></span>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-success fs-6"><?= number_format((float) $s->qty_stok, 2) ?></span>
                                </td>
                                <td class="text-end fw-semibold"><?= number_format((float) $s->total_berat, 3) ?></td>
                                <td class="text-end">
                                    <?= (float)$s->berat_referensi > 0 ? number_format((float)$s->berat_referensi, 4) : '—' ?>
                                </td>
                                <td class="text-end">Rp <?= number_format((float) $s->costbook, 2) ?></td>
                                <td class="text-end text-success fw-bold">Rp <?= number_format((float) $s->total_nilai, 2) ?></td>
                                <td class="text-center"><small class="text-muted"><?= $s->last_update ? date('d-m-Y H:i', strtotime($s->last_update)) : '-' ?></small></td>
                                <td class="text-center">
                                    <a href="<?= base_url('fg_stock/history/' . urlencode($s->produk_fg)) ?>" class="btn btn-sm btn-outline-info" title="Lihat Kartu Stok">
                                        <i class="fa fa-history me-1"></i> Kartu Stok
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: FINISH GOOD KW 2                    -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-kw2" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-kw2">
                        <thead class="table-warning">
                            <tr>
                                <th class="text-center" width="40">No</th>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th class="text-center">Kategori</th>
                                <th class="text-end">Qty Stok (pcs)</th>
                                <th class="text-end">Total Berat (kg)</th>
                                <th class="text-end">Berat Ref (kg/pcs)</th>
                                <th class="text-end">Costbook (Rp/pcs)</th>
                                <th class="text-end">Nilai Inventory (80%) (Rp)</th>
                                <th class="text-center">Terakhir Update</th>
                                <th class="text-center no-sort" width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stok_kw2 as $i => $s): ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><strong class="text-warning text-dark"><?= htmlspecialchars($s->produk_fg) ?></strong></td>
                                <td><?= htmlspecialchars($s->nm_produk_fg ?: '-') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark border"><?= htmlspecialchars($s->sub_kategori ?: 'KW 2') ?></span>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-warning text-dark fs-6"><?= number_format((float) $s->qty_stok, 2) ?></span>
                                </td>
                                <td class="text-end fw-semibold"><?= number_format((float) $s->total_berat, 3) ?></td>
                                <td class="text-end">
                                    <?= (float)$s->berat_referensi > 0 ? number_format((float)$s->berat_referensi, 4) : '—' ?>
                                </td>
                                <td class="text-end">Rp <?= number_format((float) $s->costbook, 2) ?></td>
                                <td class="text-end text-primary fw-bold">Rp <?= number_format((float) $s->total_nilai, 2) ?></td>
                                <td class="text-center"><small class="text-muted"><?= $s->last_update ? date('d-m-Y H:i', strtotime($s->last_update)) : '-' ?></small></td>
                                <td class="text-center">
                                    <a href="<?= base_url('fg_stock/history/' . urlencode($s->produk_fg)) ?>" class="btn btn-sm btn-outline-info" title="Lihat Kartu Stok">
                                        <i class="fa fa-history me-1"></i> Kartu Stok
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 3: TONG COIL                           -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-tong-coil" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-tong-coil">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center" width="40">No</th>
                                <th>Kode Stok</th>
                                <th>Material Asal</th>
                                <th class="text-end">Qty (Roll/Pcs)</th>
                                <th class="text-end">Total Berat (kg)</th>
                                <th class="text-end">Costbook Mat (Rp/kg)</th>
                                <th class="text-end">Nilai Persediaan 40% (Rp)</th>
                                <th class="text-center">Terakhir Update</th>
                                <th class="text-center no-sort" width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stok_tong_coil as $i => $s): ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><code><?= htmlspecialchars($s->produk_fg) ?></code></td>
                                <td>
                                    <strong><?= htmlspecialchars($s->nm_produk_fg ?: '-') ?></strong>
                                    <?php if (!empty($s->id_material)): ?>
                                        <br><small class="text-muted">Kode Material: <?= htmlspecialchars($s->id_material) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-info text-dark fs-6"><?= number_format((float) $s->qty_stok, 0) ?></span>
                                </td>
                                <td class="text-end fw-bold text-primary"><?= number_format((float) $s->total_berat, 3) ?></td>
                                <td class="text-end">Rp <?= number_format((float) $s->costbook, 2) ?></td>
                                <td class="text-end text-success fw-bold">Rp <?= number_format((float) $s->total_nilai, 2) ?></td>
                                <td class="text-center"><small class="text-muted"><?= $s->last_update ? date('d-m-Y H:i', strtotime($s->last_update)) : '-' ?></small></td>
                                <td class="text-center">
                                    <a href="<?= base_url('fg_stock/history/' . urlencode($s->produk_fg)) ?>" class="btn btn-sm btn-outline-info" title="Lihat Kartu Stok">
                                        <i class="fa fa-history me-1"></i> Kartu Stok
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: SCRAP / WASTE                       -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-scrap" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-scrap">
                        <thead class="table-danger">
                            <tr>
                                <th class="text-center" width="40">No</th>
                                <th>Kode Stok</th>
                                <th class="text-center">Jenis Scrap</th>
                                <th>Material Asal</th>
                                <th class="text-end">Total Berat (kg)</th>
                                <th class="text-end">Costbook Mat (Rp/kg)</th>
                                <th class="text-end">Nilai Persediaan 40% (Rp)</th>
                                <th class="text-center">Terakhir Update</th>
                                <th class="text-center no-sort" width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stok_scrap as $i => $s): ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><code><?= htmlspecialchars($s->produk_fg) ?></code></td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?= htmlspecialchars($s->sub_kategori ?: 'Scrap') ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($s->nm_produk_fg ?: '-') ?></strong>
                                    <?php if (!empty($s->id_material)): ?>
                                        <br><small class="text-muted">Kode Material: <?= htmlspecialchars($s->id_material) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold text-danger"><?= number_format((float) $s->total_berat, 3) ?></td>
                                <td class="text-end">Rp <?= number_format((float) $s->costbook, 2) ?></td>
                                <td class="text-end text-success fw-bold">Rp <?= number_format((float) $s->total_nilai, 2) ?></td>
                                <td class="text-center"><small class="text-muted"><?= $s->last_update ? date('d-m-Y H:i', strtotime($s->last_update)) : '-' ?></small></td>
                                <td class="text-center">
                                    <a href="<?= base_url('fg_stock/history/' . urlencode($s->produk_fg)) ?>" class="btn btn-sm btn-outline-info" title="Lihat Kartu Stok">
                                        <i class="fa fa-history me-1"></i> Kartu Stok
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
$(document).ready(function () {
    var dtConfig = {
        autoWidth: false,
        responsive: true,
        columnDefs: [{ targets: 'no-sort', orderable: false }],
        sPaginationType: 'simple_numbers',
        iDisplayLength: 25,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 data",
            zeroRecords: "Tidak ada data yang cocok",
            paginate: { first: "Awal", last: "Akhir", next: "&raquo;", previous: "&laquo;" }
        }
    };

    if ($('#tbl-kw1').length) $('#tbl-kw1').DataTable(dtConfig);
    if ($('#tbl-kw2').length) $('#tbl-kw2').DataTable(dtConfig);
    if ($('#tbl-tong-coil').length) $('#tbl-tong-coil').DataTable(dtConfig);
    if ($('#tbl-scrap').length) $('#tbl-scrap').DataTable(dtConfig);

    // Auto-switch tab dari URL hash (misal #tab-kw2, #tab-tong-coil, #tab-scrap)
    if (window.location.hash) {
        var activeTab = document.querySelector('a[href="' + window.location.hash + '"]');
        if (activeTab) {
            var tabTrigger = new bootstrap.Tab(activeTab);
            tabTrigger.show();
        }
    }
});
</script>
