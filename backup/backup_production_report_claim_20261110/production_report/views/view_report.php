<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <?= $this->session->flashdata('warning') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
$status_color = [
    'Draft'        => 'secondary',
    'Submitted'    => 'primary',
    'Approved'     => 'success',
    'Rejected'     => 'danger',
    'Posted to FG' => 'dark',
];
$badge_color = isset($status_color[$report->status]) ? $status_color[$report->status] : 'secondary';
?>

<!-- Header Info -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Laporan Produksi: <?= $report->report_no ?></h5>
        <span class="badge bg-<?= $badge_color ?> fs-6"><?= $report->status ?></span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%">No Laporan</td>
                        <td><strong><?= $report->report_no ?></strong></td>
                    </tr>
                    <tr>
                        <td>No SPK</td>
                        <td><?= $report->spk_no ?></td>
                    </tr>
                    <tr>
                        <td>No Coil</td>
                        <td><?= $report->no_coil ?></td>
                    </tr>
                    <tr>
                        <td>Produk FG</td>
                        <td><?= isset($report->nm_produk_fg) ? $report->nm_produk_fg : '-' ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%">Dibuat Oleh</td>
                        <td><?= isset($report->nama_created_by) ? $report->nama_created_by : '-' ?></td>
                    </tr>
                    <tr>
                        <td>Tgl Buat</td>
                        <td><?= $report->created_at ?></td>
                    </tr>
                    <?php if ($report->approved_by): ?>
                    <tr>
                        <td>Disetujui Oleh</td>
                        <td><?= isset($report->nama_approved_by) ? $report->nama_approved_by : '-' ?></td>
                    </tr>
                    <tr>
                        <td>Tgl Approve</td>
                        <td><?= $report->approved_at ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($report->override_fg): ?>
                    <tr>
                        <td>Override FG</td>
                        <td><span class="badge bg-warning text-dark">Ya</span> — <?= $report->override_alasan ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Hasil Produksi & Kalkulasi -->
<?php if ($result): ?>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Rincian Hasil Produksi</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th class="text-end">Berat (kg)</th>
                            <th class="text-end">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Reject Supplier</td>
                            <td class="text-end"><?= number_format($result->reject_supplier, 3) ?></td>
                            <td class="text-end">—</td>
                        </tr>
                        <tr>
                            <td>Waste Potong</td>
                            <td class="text-end"><?= number_format($result->waste_potong, 3) ?></td>
                            <td class="text-end">—</td>
                        </tr>
                        <tr>
                            <td>NG Internal</td>
                            <td class="text-end"><?= number_format($result->ng_internal, 3) ?></td>
                            <td class="text-end">—</td>
                        </tr>
                        <tr>
                            <td>NG Supplier</td>
                            <td class="text-end"><?= number_format($result->ng_supplier, 3) ?></td>
                            <td class="text-end">—</td>
                        </tr>
                        <tr>
                            <td>Plat BS</td>
                            <td class="text-end"><?= number_format($result->plat_bs, 3) ?></td>
                            <td class="text-end">—</td>
                        </tr>
                        <tr class="table-success">
                            <td><strong>FG</strong></td>
                            <td class="text-end"><strong><?= number_format($result->fg_kg, 3) ?></strong></td>
                            <td class="text-end"><strong><?= number_format($result->fg_qty, 2) ?></strong></td>
                        </tr>
                        <tr>
                            <td>KW2 Internal</td>
                            <td class="text-end"><?= number_format($result->kw2_internal_kg, 3) ?></td>
                            <td class="text-end"><?= number_format($result->kw2_internal_qty, 2) ?></td>
                        </tr>
                        <tr>
                            <td>KW2 Supplier</td>
                            <td class="text-end"><?= number_format($result->kw2_supplier_kg, 3) ?></td>
                            <td class="text-end"><?= number_format($result->kw2_supplier_qty, 2) ?></td>
                        </tr>
                        <tr>
                            <td>Tong Coil</td>
                            <td class="text-end"><?= number_format($result->tong_coil, 3) ?></td>
                            <td class="text-end">—</td>
                        </tr>
                        <tr>
                            <td>Cover Wrapping</td>
                            <td class="text-end"><?= number_format($report->berat_cover_wrapping, 3) ?></td>
                            <td class="text-end">—</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td><strong>Total Berat Coil</strong></td>
                            <td class="text-end"><strong><?= number_format($result->total_berat_coil, 3) ?></strong></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><strong>Net Hasil Produksi</strong></td>
                            <td class="text-end"><strong><?= number_format($result->net_hasil_produksi, 3) ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <!-- Yield Breakdown -->
        <?php if (!empty($yield)): ?>
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Yield per Kategori</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th class="text-end">Yield (%)</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $yield_map = [
                            'yield_fg'           => 'FG',
                            'yield_kw2_internal' => 'KW2 Internal',
                            'yield_kw2_supplier' => 'KW2 Supplier',
                            'yield_reject'       => 'Reject Supplier',
                            'yield_waste'        => 'Waste Potong',
                            'yield_ng_internal'  => 'NG Internal',
                            'yield_ng_supplier'  => 'NG Supplier',
                            'yield_plat_bs'      => 'Plat BS',
                        ];
                        $yield_colors = [
                            'yield_fg'           => 'success',
                            'yield_kw2_internal' => 'info',
                            'yield_kw2_supplier' => 'info',
                            'yield_reject'       => 'danger',
                            'yield_waste'        => 'warning',
                            'yield_ng_internal'  => 'danger',
                            'yield_ng_supplier'  => 'danger',
                            'yield_plat_bs'      => 'secondary',
                        ];
                        foreach ($yield_map as $key => $label):
                            $pct = isset($yield[$key]) ? $yield[$key] : 0;
                        ?>
                        <tr>
                            <td><?= $label ?></td>
                            <td class="text-end"><?= number_format($pct, 2) ?>%</td>
                            <td>
                                <div class="progress" style="height:12px;">
                                    <div class="progress-bar bg-<?= $yield_colors[$key] ?>"
                                         style="width:<?= min($pct, 100) ?>%"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Validasi Berat FG -->
        <div class="card <?= ($deviasi && $deviasi['is_exception']) ? 'border-danger' : '' ?>">
            <div class="card-header <?= ($deviasi && $deviasi['is_exception']) ? 'bg-danger text-white' : '' ?>">
                <h6 class="mb-0">Validasi Berat Satuan FG</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="50%">Berat Satuan Aktual</td>
                        <td class="text-end fw-bold">
                            <?= $result->fg_qty > 0 ? number_format($result->berat_satuan_fg, 4) . ' kg' : '—' ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Berat Standar</td>
                        <td class="text-end">
                            <?= $berat_standar > 0 ? number_format($berat_standar, 4) . ' kg' : '—' ?>
                        </td>
                    </tr>
                    <?php if ($deviasi): ?>
                    <tr>
                        <td>Deviasi</td>
                        <td class="text-end fw-bold <?= $deviasi['is_exception'] ? 'text-danger' : 'text-success' ?>">
                            <?= number_format($deviasi['deviasi_pct'] * 100, 2) ?>%
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>

                <?php if ($deviasi && $deviasi['is_exception']): ?>
                    <?php if ($report->override_fg): ?>
                        <div class="alert alert-warning mt-2 mb-0">
                            <i class="fa fa-check-circle"></i>
                            <strong>Override disetujui.</strong> Alasan: <?= $report->override_alasan ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger mt-2 mb-0">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>Deviasi melebihi toleransi!</strong> Diperlukan konfirmasi QC sebelum posting.
                        </div>
                    <?php endif; ?>
                <?php elseif ($deviasi): ?>
                    <div class="alert alert-success mt-2 mb-0">
                        <i class="fa fa-check-circle"></i> Berat FG dalam toleransi.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tombol Aksi -->
<div class="card">
    <div class="card-body d-flex flex-wrap gap-2">
        <a href="<?= base_url('production_report') ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>

        <?php if ($report->status === 'Draft'): ?>
            <a href="<?= base_url('production_report/edit/' . $report->report_no) ?>" class="btn btn-warning">
                <i class="fa fa-edit"></i> Edit
            </a>
            <button type="button" class="btn btn-primary" onclick="doSubmit()">
                <i class="fa fa-paper-plane"></i> Submit
            </button>
        <?php endif; ?>

        <?php if ($report->status === 'Submitted'): ?>
            <button type="button" class="btn btn-success" onclick="doApprove()">
                <i class="fa fa-check"></i> Approve
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalReject">
                <i class="fa fa-times"></i> Reject
            </button>
        <?php endif; ?>

        <?php if ($report->status === 'Approved'): ?>
            <?php if ($deviasi && $deviasi['is_exception'] && !$report->override_fg): ?>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalOverride">
                    <i class="fa fa-exclamation-triangle"></i> Override Deviasi FG
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-dark" onclick="doPost()">
                    <i class="fa fa-upload"></i> Post ke FG
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Alasan Reject <span class="text-danger">*</span></label>
                <textarea id="alasan_reject" class="form-control" rows="3" placeholder="Masukkan alasan reject..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="doReject()">Reject</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Override Deviasi FG -->
<div class="modal fade" id="modalOverride" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Konfirmasi Override Deviasi Berat FG</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    Deviasi berat FG melebihi toleransi. Dengan mengisi alasan dan mengkonfirmasi,
                    Anda menyetujui bahwa laporan ini dapat diposting meskipun ada deviasi.
                </div>
                <label class="form-label">Alasan Override <span class="text-danger">*</span></label>
                <textarea id="alasan_override" class="form-control" rows="3" placeholder="Masukkan alasan override..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" onclick="doOverride()">Konfirmasi Override</button>
            </div>
        </div>
    </div>
</div>

<script>
var reportNo = '<?= $report->report_no ?>';

function doSubmit() {
    if (!confirm('Submit laporan ini untuk review?')) return;
    $.post(siteurl + 'production_report/process_submit/' + reportNo, {}, function (res) {
        if (res.success) {
            location.reload();
        } else {
            alert('Gagal: ' + res.message);
        }
    }, 'json');
}

function doApprove() {
    if (!confirm('Approve laporan ini?')) return;
    $.post(siteurl + 'production_report/process_approve/' + reportNo, {}, function (res) {
        if (res.success) {
            location.reload();
        } else {
            alert('Gagal: ' + res.message);
        }
    }, 'json');
}

function doReject() {
    var alasan = $('#alasan_reject').val().trim();
    if (!alasan) { alert('Alasan reject wajib diisi'); return; }
    $.post(siteurl + 'production_report/process_reject/' + reportNo, { alasan: alasan }, function (res) {
        if (res.success) {
            location.reload();
        } else {
            alert('Gagal: ' + res.message);
        }
    }, 'json');
}

function doPost() {
    if (!confirm('Post laporan ini ke FG? Tindakan ini tidak dapat dibatalkan.')) return;
    $.post(siteurl + 'production_report/process_post/' + reportNo, {}, function (res) {
        if (res.success) {
            alert(res.message);
            location.reload();
        } else if (res.need_override) {
            alert(res.message);
            $('#modalOverride').modal('show');
        } else {
            alert('Gagal: ' + res.message);
        }
    }, 'json');
}

function doOverride() {
    var alasan = $('#alasan_override').val().trim();
    if (!alasan) { alert('Alasan override wajib diisi'); return; }
    $.post(siteurl + 'production_report/process_override_fg/' + reportNo, { alasan: alasan }, function (res) {
        if (res.success) {
            $('#modalOverride').modal('hide');
            location.reload();
        } else {
            alert('Gagal: ' + res.message);
        }
    }, 'json');
}
</script>
