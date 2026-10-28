<?php defined('BASEPATH') or exit('No direct script access allowed');
$ENABLE_MANAGE = has_permission('Man_Power_Rate.Manage');
?>

<style>
.card-section {
    border-radius: 12px;
    transition: all 0.2s ease;
    overflow: hidden;
}
.card-collapse-btn {
    border-radius: 8px;
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.card-collapse-btn i {
    transition: transform 0.25s ease;
}
.card-collapse-btn.collapsed i {
    transform: rotate(180deg);
}
.shadow-xs {
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.dataTables_wrapper {
    padding: 4px 0;
}
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 12px;
}
.dataTables_wrapper .dataTables_filter input {
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    padding: 4px 10px;
    margin-left: 6px;
    font-size: 0.875rem;
}
.dataTables_wrapper .dataTables_length select {
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    padding: 3px 8px;
    font-size: 0.875rem;
}
.dataTables_wrapper .dataTables_info {
    font-size: 0.85rem;
    color: #64748b;
    padding-top: 8px;
}
.dataTables_wrapper .dataTables_paginate {
    padding-top: 6px;
}
.badge-circle {
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
</style>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-xs border-0" role="alert">
        <i class="fa fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-xs border-0" role="alert">
        <i class="fa fa-exclamation-triangle me-1"></i> <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- 1. MODERN INFORMATION & KPI HERO SECTION                                   -->
<!-- ========================================================================= -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border-radius: 12px;">
    <div class="card-body p-4">
        <!-- Top Title & Global Collapse Bar -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 52px; height: 52px;">
                    <i class="fa fa-users-cog fa-2x"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Man Power Rate &amp; Direct Labor Operator</h4>
                    <p class="text-muted mb-0 small">
                        Pusat pengelolaan tarif tenaga kerja operator per jam (Rate 1/3 Operator) berbasis rincian gaji, BPJS, biaya lain, dan jam kerja standar.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-white bg-white text-secondary border shadow-xs" id="btn-expand-all">
                    <i class="fa fa-expand me-1 text-primary"></i> Buka Semua Card
                </button>
                <button type="button" class="btn btn-sm btn-white bg-white text-secondary border shadow-xs" id="btn-collapse-all">
                    <i class="fa fa-compress me-1 text-muted"></i> Tutup Semua Card
                </button>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="row g-3">
            <!-- Metric 1: Total Biaya Direct Labor -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-xs h-100 bg-white" style="border-radius: 10px;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold">Total Direct Labor / Bln</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2">
                                <i class="fa fa-calendar-alt me-1"></i>Bulan
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Rp <?= number_format($rate->total_direct_labor_per_bulan, 0, ',', '.') ?></h4>
                        <div class="small text-muted text-truncate">Salary + BPJS + Lain-Lain</div>
                    </div>
                </div>
            </div>

            <!-- Metric 2: Jam Kerja Standar & Porsi -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-xs h-100 bg-white" style="border-radius: 10px;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold">Jam Kerja Standar</span>
                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2">
                                <i class="fa fa-clock me-1"></i>Standar
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-1"><?= number_format($rate->jam_kerja_per_bulan, 0) ?> <span class="fs-6 fw-normal text-muted">Jam / Bln</span></h4>
                        <div class="small text-muted">Porsi Operator: <strong>1 / <?= round(1 / ((float)$rate->porsi_operator ?: 0.3333)) ?></strong></div>
                    </div>
                </div>
            </div>

            <!-- Metric 3: Rate 1/3 Operator (Aktif) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-xs h-100 bg-white border-start border-primary border-4" style="border-radius: 10px;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-primary small fw-bold">Rate 1/3 Operator (Aktif)</span>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">
                                <i class="fa fa-check me-1"></i>CEIL
                            </span>
                        </div>
                        <h4 class="fw-bold text-primary mb-1">Rp <?= number_format($rate->rate_1_3_operator, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted">/ Jam</span></h4>
                        <div class="small text-muted">Dibulatkan ke Rupiah penuh</div>
                    </div>
                </div>
            </div>

            <!-- Metric 4: Faktor Indirect Labor -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-xs h-100 bg-white" style="border-radius: 10px;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold">Faktor Indirect Labor</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2">
                                <i class="fa fa-percent me-1"></i>Global
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-1"><?= number_format($faktor, 2) ?>x</h4>
                        <div class="small text-muted">
                            <a href="<?= base_url('faktor_indirect_labor') ?>" class="text-primary text-decoration-none fw-semibold">Kelola Faktor &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formula Callout Banner -->
        <div class="mt-3 p-3 bg-white rounded-3 border border-light-subtle d-flex flex-wrap align-items-center justify-content-between gap-2 shadow-xs">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="badge bg-primary px-3 py-2 text-uppercase"><i class="fa fa-calculator me-1"></i> Formula MPC</span>
                <span class="text-secondary small fw-medium">
                    <code>Man Power Cost (Rp/m) = (Total Man Hour &times; Rate 1/3 Operator + Rate Borongan Helper Rp/m) &times; Faktor Indirect Labor</code>
                </span>
            </div>
            <span class="badge bg-light text-muted border px-2 py-1 small">
                <i class="fa fa-info-circle me-1 text-info"></i> Terhubung langsung ke Simulasi Costing &amp; Pricelist
            </span>
        </div>
    </div>
</div>

<?php
$groups = [
    'salary'    => ['id' => 'salary', 'no' => '1', 'title' => 'Salary Direct Man Power', 'sub' => $rate->salary_direct_man_power, 'items' => $components['salary'], 'desc' => 'Gaji pokok dan tunjangan langsung operator.', 'badge_cls' => 'bg-primary bg-opacity-10 text-primary border-primary border-opacity-25'],
    'bpjs'      => ['id' => 'bpjs', 'no' => '2', 'title' => 'BPJS (Input Manual)', 'sub' => $rate->bpjs, 'items' => $components['bpjs'], 'desc' => 'Nilai rupiah tiap komponen diinput manual; persentase hanya keterangan.', 'badge_cls' => 'bg-info bg-opacity-10 text-info border-info border-opacity-25'],
    'lain_lain' => ['id' => 'lain-lain', 'no' => '3', 'title' => 'Biaya Lain-Lain', 'sub' => $rate->biaya_lain_lain, 'items' => $components['lain_lain'], 'desc' => 'Biaya pendukung per bulan (seragam, pelatihan, dll.).', 'badge_cls' => 'bg-secondary bg-opacity-10 text-secondary border-secondary border-opacity-25'],
];
?>

<!-- ========================================================================= -->
<!-- 2. CARD 1, 2, 3: KOMPONEN BIAYA (SALARY, BPJS, BIAYA LAIN-LAIN)           -->
<!-- ========================================================================= -->
<?php foreach ($groups as $gKey => $g): ?>
<div class="card shadow-sm border-0 mb-4 card-section">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <span class="badge badge-circle bg-primary text-white fs-6">
                <?= $g['no'] ?>
            </span>
            <div>
                <h5 class="mb-0 fw-bold text-dark"><?= $g['no'] ?>. <?= $g['title'] ?></h5>
                <small class="text-muted"><?= $g['desc'] ?></small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge <?= $g['badge_cls'] ?> border px-3 py-2 fs-6">
                Subtotal: <strong>Rp <?= number_format($g['sub'], 0, ',', '.') ?></strong>
            </span>
            <?php if ($ENABLE_MANAGE): ?>
                <button type="button" class="btn btn-primary btn-sm btn-add-comp shadow-xs" data-kelompok="<?= $gKey ?>" data-title="<?= $g['title'] ?>">
                    <i class="fa fa-plus me-1"></i> Add Komponen
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-outline-secondary card-collapse-btn" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $g['id'] ?>" aria-expanded="true" title="Buka / Tutup Card">
                <i class="fa fa-chevron-up"></i>
            </button>
        </div>
    </div>
    <div id="collapse-<?= $g['id'] ?>" class="collapse show card-collapse-body">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0 tbl-komponen" id="tbl-<?= $g['id'] ?>" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center no-sort" width="50">#</th>
                            <th>Item Komponen</th>
                            <th class="text-end" width="220">Nilai (Rp)</th>
                            <th>Keterangan</th>
                            <?php if ($ENABLE_MANAGE): ?>
                                <th class="text-center no-sort" width="120">Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($g['items'])): ?>
                            <?php foreach ($g['items'] as $idx => $item): ?>
                                <tr>
                                    <td class="text-center"><?= $idx + 1 ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($item->nama_komponen) ?></td>
                                    <td class="text-end fw-bold text-success">Rp <?= number_format($item->nilai, 0, ',', '.') ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($item->keterangan ?: '—') ?></td>
                                    <?php if ($ENABLE_MANAGE): ?>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-comp"
                                                data-id="<?= $item->id ?>"
                                                data-kelompok="<?= $gKey ?>"
                                                data-nama="<?= htmlspecialchars($item->nama_komponen) ?>"
                                                data-nilai="<?= (float)$item->nilai ?>"
                                                data-ket="<?= htmlspecialchars($item->keterangan ?: '') ?>"
                                                title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-del-comp ms-1"
                                                data-id="<?= $item->id ?>"
                                                data-nama="<?= htmlspecialchars($item->nama_komponen) ?>"
                                                title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- ========================================================================= -->
<!-- 3. CARD 4: PARAMETER & RATE 1/3 OPERATOR                                   -->
<!-- ========================================================================= -->
<div class="card shadow-sm border-0 mb-4 card-section">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <span class="badge badge-circle bg-primary text-white fs-6">
                4
            </span>
            <div>
                <h5 class="mb-0 fw-bold text-dark">4. Parameter &amp; Rate 1/3 Operator</h5>
                <small class="text-muted">Konstanta jam kerja, porsi operator, dan pembulatan ke atas ke Rupiah penuh (CEIL).</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="width: 220px;">
                <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                <input type="text" id="search-param" class="form-control border-start-0" placeholder="Cari parameter...">
            </div>
            <?php if ($ENABLE_MANAGE): ?>
                <button type="button" class="btn btn-primary btn-sm shadow-xs" data-bs-toggle="modal" data-bs-target="#modal-param">
                    <i class="fa fa-edit me-1"></i> Edit Parameter
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-outline-secondary card-collapse-btn" data-bs-toggle="collapse" data-bs-target="#collapse-param" aria-expanded="true" title="Buka / Tutup Card">
                <i class="fa fa-chevron-up"></i>
            </button>
        </div>
    </div>
    <div id="collapse-param" class="collapse show card-collapse-body">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="tbl-parameter">
                    <thead class="table-light">
                        <tr>
                            <th width="35%">Komponen / Parameter</th>
                            <th width="25%" class="text-end">Nilai</th>
                            <th>Keterangan / Aturan Kalkulasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Jam kerja per bulan <span class="badge bg-light text-dark border ms-1">parameter</span></td>
                            <td class="text-end fw-bold fs-6"><?= number_format($rate->jam_kerja_per_bulan, 0) ?> jam</td>
                            <td class="text-muted">Pembagi Total Biaya Direct Labor Operator per bulan</td>
                        </tr>
                        <tr>
                            <td>Porsi Operator <span class="badge bg-light text-dark border ms-1">parameter</span></td>
                            <td class="text-end fw-bold fs-6">1 / <?= round(1 / ((float)$rate->porsi_operator ?: 0.3333)) ?></td>
                            <td class="text-muted">Operator dianggap hanya 1/<?= round(1 / ((float)$rate->porsi_operator ?: 0.3333)) ?> bekerja langsung pada produk</td>
                        </tr>
                        <tr>
                            <td><strong>Faktor Indirect Labor</strong> <span class="badge bg-info text-dark ms-1">master global</span></td>
                            <td class="text-end fw-bold fs-6 text-primary"><?= number_format($faktor, 2) ?></td>
                            <td class="text-muted">Master: menu <a href="<?= base_url('faktor_indirect_labor') ?>" class="fw-bold text-decoration-none">Faktor Indirect Labor</a></td>
                        </tr>
                        <tr class="table-light">
                            <td><strong>Total Biaya Direct Labor Operator / Bulan</strong></td>
                            <td class="text-end fw-bold fs-6 text-dark">Rp <?= number_format($rate->total_direct_labor_per_bulan, 0, ',', '.') ?></td>
                            <td class="text-muted">Salary + BPJS + Biaya Lain-Lain</td>
                        </tr>
                        <tr>
                            <td>Rate Direct Labor Operator (Rp/jam)</td>
                            <td class="text-end fw-bold text-secondary">Rp <?= number_format($rate->rate_direct_labor, 2, ',', '.') ?></td>
                            <td class="text-muted">Total &divide; jam kerja per bulan (belum dibulatkan)</td>
                        </tr>
                        <tr>
                            <td><strong>Rate 1/3 Operator (Rp/jam)</strong></td>
                            <td class="text-end fw-bold fs-6 text-dark">Rp <?= number_format($rate->rate_1_3_operator, 0, ',', '.') ?></td>
                            <td class="text-muted"><strong>CEIL(Rate Direct Labor &times; 1/<?= round(1 / ((float)$rate->porsi_operator ?: 0.3333)) ?>)</strong> &mdash; dibulatkan ke atas ke Rupiah penuh sesudah dikali 1/3. Satu-satunya angka yang dibulatkan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 4. CARD 5: CONTOH MAN POWER COST PER PRODUCT CATEGORY                      -->
<!-- ========================================================================= -->
<div class="card shadow-sm border-0 mb-4 card-section">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <span class="badge badge-circle bg-primary text-white fs-6">
                5
            </span>
            <div>
                <h5 class="mb-0 fw-bold text-dark">5. Contoh: Man Power Cost per Product Category</h5>
                <small class="text-muted">
                    Formula: <code>(Total Man Hour &times; Rate 1/3 Operator + Rate Borongan Helper Rp/m) &times; Faktor Indirect Labor</code>
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-secondary card-collapse-btn" data-bs-toggle="collapse" data-bs-target="#collapse-sim" aria-expanded="true" title="Buka / Tutup Card">
                <i class="fa fa-chevron-up"></i>
            </button>
        </div>
    </div>
    <div id="collapse-sim" class="collapse show card-collapse-body">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-simulation" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center no-sort" width="50">#</th>
                            <th>Product Category</th>
                            <th class="text-end" width="170">Total Man Hour (jam/m)</th>
                            <th class="text-end" width="180">Bagian Operator (Rp/m)</th>
                            <th class="text-end" width="180">Borongan Helper (Rp/m)</th>
                            <th class="text-end" width="200">Jumlah sebelum Faktor (Rp/m)</th>
                            <th class="text-end" width="140">&times; Faktor Indirect</th>
                            <th class="text-end" width="200">Man Power Cost (Rp/m)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($simulation)): ?>
                            <?php foreach ($simulation as $idx => $sim): ?>
                                <tr>
                                    <td class="text-center"><?= $idx + 1 ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars(strtoupper($sim->nama)) ?></td>
                                    <td class="text-end">
                                        <?= $sim->has_flow ? number_format($sim->tmh, 6, '.', '') : '<span class="badge bg-warning text-dark">Flow belum ada</span>' ?>
                                    </td>
                                    <td class="text-end">
                                        <?= $sim->has_flow ? 'Rp ' . number_format($sim->op_cost, 2, ',', '.') : '—' ?>
                                    </td>
                                    <td class="text-end">
                                        <?= $sim->has_bor ? 'Rp ' . number_format($sim->bor, 2, ',', '.') : '<span class="badge bg-warning text-dark">Rate belum ada</span>' ?>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        <?= $sim->subtotal !== null ? 'Rp ' . number_format($sim->subtotal, 2, ',', '.') : '—' ?>
                                    </td>
                                    <td class="text-end text-primary fw-bold">
                                        <?= number_format($sim->faktor, 2) ?>
                                    </td>
                                    <td class="text-end fw-bold text-success fs-6">
                                        <?= $sim->total_mpc !== null ? 'Rp ' . number_format($sim->total_mpc, 2, ',', '.') : '—' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL ADD / EDIT KOMPONEN                                                 -->
<!-- ========================================================================= -->
<div class="modal fade" id="modal-comp" tabindex="-1" aria-labelledby="modalCompLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <form action="<?= base_url('man_power_rate/save_component') ?>" method="POST" autocomplete="off">
                <input type="hidden" name="id" id="comp_id" value="">
                <input type="hidden" name="kelompok" id="comp_kelompok" value="salary">

                <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title text-white" id="modalCompLabel"><i class="fa fa-cubes me-2"></i>Komponen Biaya</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Item Komponen <span class="text-danger">*</span></label>
                        <input type="text" name="nama_komponen" id="comp_nama" class="form-control" required placeholder="Contoh: Gaji Pokok, Tunjangan Kehadiran, dll.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nilai Rupiah (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="0" name="nilai" id="comp_nilai" class="form-control" required placeholder="0">
                        <div class="form-text text-muted">Nilai biaya per bulan dalam Rupiah penuh.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <input type="text" name="keterangan" id="comp_ket" class="form-control" placeholder="Contoh: Iuran bulanan, tunjangan makan, dll.">
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i> Simpan Komponen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL EDIT PARAMETER                                                      -->
<!-- ========================================================================= -->
<div class="modal fade" id="modal-param" tabindex="-1" aria-labelledby="modalParamLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <form action="<?= base_url('man_power_rate/save_parameters') ?>" method="POST" autocomplete="off">
                <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title text-white" id="modalParamLabel"><i class="fa fa-sliders-h me-2"></i>Edit Parameter Man Power</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jam Kerja per Bulan <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" min="1" name="jam_kerja_per_bulan" class="form-control" required
                            value="<?= number_format($rate->jam_kerja_per_bulan, 2, '.', '') ?>">
                        <div class="form-text text-muted">Standar parameter jam kerja operasional (default 173 jam).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Porsi Operator: 1 / n (n =) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" min="0.1" name="porsi_operator_den" class="form-control" required
                            value="<?= round(1 / ((float)$rate->porsi_operator ?: 0.3333)) ?>">
                        <div class="form-text text-muted">Operator dianggap hanya 1/n bekerja langsung pada produk (contoh n = 3 untuk porsi 1/3).</div>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i> Simpan Parameter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function() {
    // -------------------------------------------------------------------------
    // 1. Inisialisasi DataTables pada Tabel Komponen (Salary, BPJS, Lain-Lain)
    // -------------------------------------------------------------------------
    $('.tbl-komponen').each(function() {
        $(this).DataTable({
            responsive: true,
            paging: false,
            info: true,
            searching: true,
            language: {
                search: "",
                searchPlaceholder: "Cari item komponen...",
                emptyTable: "Belum ada rincian komponen pada kelompok ini",
                info: "Menampilkan <strong>_TOTAL_</strong> komponen",
                infoEmpty: "0 komponen",
                infoFiltered: "(difilter dari _MAX_ total)"
            },
            columnDefs: [
                { targets: 'no-sort', orderable: false }
            ]
        });
    });

    // -------------------------------------------------------------------------
    // 2. Inisialisasi DataTables pada Tabel Simulasi Man Power Cost
    // -------------------------------------------------------------------------
    $('#tbl-simulation').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "",
            searchPlaceholder: "Cari kategori produk...",
            emptyTable: "Belum ada data kategori produk",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ s/d _END_ dari <strong>_TOTAL_</strong> kategori",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(difilter dari _MAX_ total kategori)",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "&rarr;",
                previous: "&larr;"
            }
        },
        columnDefs: [
            { targets: 'no-sort', orderable: false }
        ]
    });

    // -------------------------------------------------------------------------
    // 3. Live Search Filter pada Tabel Parameter (Card 4)
    // -------------------------------------------------------------------------
    $('#search-param').on('keyup', function() {
        var val = $(this).val().toLowerCase();
        $('#tbl-parameter tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    // -------------------------------------------------------------------------
    // 4. Global Expand & Collapse All Cards
    // -------------------------------------------------------------------------
    $('#btn-expand-all').on('click', function() {
        $('.card-collapse-body').collapse('show');
        $('.card-collapse-btn').removeClass('collapsed').attr('aria-expanded', 'true');
        $('.card-collapse-btn i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    });

    $('#btn-collapse-all').on('click', function() {
        $('.card-collapse-body').collapse('hide');
        $('.card-collapse-btn').addClass('collapsed').attr('aria-expanded', 'false');
        $('.card-collapse-btn i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    // Sinkronisasi ikon chevron saat card individual di-collapse/expand
    $('.card-collapse-body').on('shown.bs.collapse', function () {
        var id = $(this).attr('id');
        var btn = $('[data-bs-target="#' + id + '"]');
        btn.removeClass('collapsed');
        btn.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    });

    $('.card-collapse-body').on('hidden.bs.collapse', function () {
        var id = $(this).attr('id');
        var btn = $('[data-bs-target="#' + id + '"]');
        btn.addClass('collapsed');
        btn.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    // -------------------------------------------------------------------------
    // 5. Action Modal Add / Edit Komponen
    // -------------------------------------------------------------------------
    $('.btn-add-comp').on('click', function(e) {
        e.stopPropagation();
        var kel = $(this).data('kelompok');
        var title = $(this).data('title');
        $('#comp_id').val('');
        $('#comp_kelompok').val(kel);
        $('#comp_nama').val('');
        $('#comp_nilai').val('');
        $('#comp_ket').val('');
        $('#modalCompLabel').html('<i class="fa fa-plus-circle me-2"></i>Add Komponen — ' + title);
        $('#modal-comp').modal('show');
    });

    $('.btn-edit-comp').on('click', function(e) {
        e.stopPropagation();
        var id = $(this).data('id');
        var kel = $(this).data('kelompok');
        var nama = $(this).data('nama');
        var nilai = $(this).data('nilai');
        var ket = $(this).data('ket');

        $('#comp_id').val(id);
        $('#comp_kelompok').val(kel);
        $('#comp_nama').val(nama);
        $('#comp_nilai').val(nilai);
        $('#comp_ket').val(ket);
        $('#modalCompLabel').html('<i class="fa fa-edit me-2"></i>Edit Komponen — ' + nama);
        $('#modal-comp').modal('show');
    });

    // -------------------------------------------------------------------------
    // 6. Action Delete Komponen via SweetAlert2
    // -------------------------------------------------------------------------
    $('.btn-del-comp').on('click', function(e) {
        e.stopPropagation();
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        Swal.fire({
            title: 'Hapus Komponen?',
            text: 'Komponen "' + nama + '" akan dihapus dan Rate 1/3 Operator akan dihitung ulang otomatis.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-trash me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(res) {
            if (!res.isConfirmed) return;
            $.ajax({
                url: '<?= base_url('man_power_rate/delete_component') ?>',
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(resp) {
                    if (resp.status == 1) {
                        Swal.fire({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1500, showConfirmButton: false })
                            .then(function() { window.location.reload(); });
                    } else {
                        Swal.fire({ title: 'Gagal!', text: resp.pesan, icon: 'error' });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ title: 'Error!', text: 'Request error: ' + xhr.status, icon: 'error' });
                }
            });
        });
    });
});
</script>
