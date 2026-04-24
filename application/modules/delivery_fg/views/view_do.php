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
    'Draft'              => 'secondary',
    'Waiting Approval'   => 'warning',
    'Approved Exception' => 'success',
    'Shipped'            => 'dark',
    'Cancelled'          => 'danger',
];
$badge_color = isset($status_color[$do->status]) ? $status_color[$do->status] : 'secondary';
?>

<!-- Header Info -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Delivery Order: <?= $do->do_no ?></h5>
        <span class="badge bg-<?= $badge_color ?> fs-6"><?= $do->status ?></span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%">No DO</td>
                        <td><strong><?= $do->do_no ?></strong></td>
                    </tr>
                    <tr>
                        <td>Customer</td>
                        <td><?= htmlspecialchars($do->customer) ?></td>
                    </tr>
                    <tr>
                        <td>Tgl Delivery</td>
                        <td><?= $do->tgl_delivery ?></td>
                    </tr>
                    <?php if ($do->keterangan): ?>
                    <tr>
                        <td>Keterangan</td>
                        <td><?= htmlspecialchars($do->keterangan) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%">Dibuat Oleh</td>
                        <td><?= isset($do->nama_created_by) ? $do->nama_created_by : '-' ?></td>
                    </tr>
                    <tr>
                        <td>Tgl Buat</td>
                        <td><?= $do->created_at ?></td>
                    </tr>
                    <?php if ($do->approved_by): ?>
                    <tr>
                        <td>Disetujui Oleh</td>
                        <td><?= isset($do->nama_approved_by) ? $do->nama_approved_by : '-' ?></td>
                    </tr>
                    <tr>
                        <td>Tgl Approve</td>
                        <td><?= $do->approved_at ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Item DO -->
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Item Produk FG</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produk FG</th>
                        <th class="text-end">Qty Kirim</th>
                        <th class="text-end">Berat Referensi (kg)</th>
                        <th class="text-end">Estimasi Berat (kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $d): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($d->produk_fg) ?>
                            <?php if ($d->nm_produk_fg): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($d->nm_produk_fg) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= number_format($d->qty_kirim, 2) ?></td>
                        <td class="text-end"><?= number_format($d->berat_referensi, 4) ?></td>
                        <td class="text-end"><?= number_format($d->estimasi_berat, 3) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total Estimasi Berat:</td>
                        <td class="text-end fw-bold"><?= number_format($total_estimasi, 3) ?> kg</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Form Timbang Aktual -->
<?php if (in_array($do->status, ['Draft', 'Waiting Approval'])): ?>
<div class="card mb-3 border-primary">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="fa fa-balance-scale"></i> Input Berat Timbang Aktual</h6>
    </div>
    <div class="card-body">
        <form method="post" action="<?= base_url('delivery_fg/save_timbang/' . $do->do_no) ?>">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Berat Aktual (kg) <span class="text-danger">*</span></label>
                    <input type="number" name="berat_aktual" class="form-control" min="0.001" step="0.001"
                           placeholder="Masukkan berat aktual" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Opsional">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-save"></i> Simpan Timbang
                    </button>
                </div>
            </div>
            <div class="mt-2 text-muted small">
                Total estimasi berat: <strong><?= number_format($total_estimasi, 3) ?> kg</strong>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Log Timbang -->
<?php if (!empty($weight_log)): ?>
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Log Timbang Aktual</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tgl Timbang</th>
                        <th>Petugas</th>
                        <th class="text-end">Berat Aktual (kg)</th>
                        <th class="text-end">Selisih (kg)</th>
                        <th class="text-end">Selisih (%)</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weight_log as $log): ?>
                    <?php $is_exception = abs($log->selisih_pct) > 0.03; ?>
                    <tr class="<?= $is_exception ? 'table-warning' : '' ?>">
                        <td><?= $log->tgl_timbang ?></td>
                        <td><?= isset($log->nama_user) ? $log->nama_user : '-' ?></td>
                        <td class="text-end"><?= number_format($log->berat_aktual, 3) ?></td>
                        <td class="text-end <?= $log->selisih_kg < 0 ? 'text-danger' : 'text-success' ?>">
                            <?= ($log->selisih_kg >= 0 ? '+' : '') . number_format($log->selisih_kg, 3) ?>
                        </td>
                        <td class="text-end <?= $is_exception ? 'text-danger fw-bold' : 'text-success' ?>">
                            <?= number_format($log->selisih_pct * 100, 2) ?>%
                        </td>
                        <td><?= htmlspecialchars($log->keterangan ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Riwayat Approval -->
<?php if (!empty($approval_log)): ?>
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Riwayat Approval</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tgl Approval</th>
                        <th>Approver</th>
                        <th>Aksi</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approval_log as $appr): ?>
                    <tr>
                        <td><?= $appr->tgl_approval ?></td>
                        <td><?= isset($appr->nama_approver) ? $appr->nama_approver : '-' ?></td>
                        <td>
                            <span class="badge bg-<?= $appr->action === 'Approved' ? 'success' : 'danger' ?>">
                                <?= $appr->action ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($appr->alasan ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tombol Aksi -->
<div class="card">
    <div class="card-body d-flex flex-wrap gap-2">
        <a href="<?= base_url('delivery_fg') ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>

        <?php if ($do->status === 'Draft'): ?>
            <a href="<?= base_url('delivery_fg/edit/' . $do->do_no) ?>" class="btn btn-warning">
                <i class="fa fa-edit"></i> Edit
            </a>
            <button type="button" class="btn btn-danger" onclick="doCancel()">
                <i class="fa fa-times"></i> Cancel DO
            </button>
        <?php endif; ?>

        <?php if ($do->status === 'Waiting Approval'): ?>
            <button type="button" class="btn btn-success" onclick="doApprove()">
                <i class="fa fa-check"></i> Approve
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalReject">
                <i class="fa fa-times"></i> Reject
            </button>
        <?php endif; ?>

        <?php if ($do->status === 'Approved Exception'): ?>
            <a href="<?= base_url('delivery_fg/cetak_surat_jalan/' . $do->do_no) ?>" class="btn btn-info" target="_blank">
                <i class="fa fa-print"></i> Cetak Surat Jalan
            </a>
            <button type="button" class="btn btn-dark" onclick="doShip()">
                <i class="fa fa-truck"></i> Konfirmasi Shipped
            </button>
            <button type="button" class="btn btn-danger" onclick="doCancel()">
                <i class="fa fa-times"></i> Cancel DO
            </button>
        <?php endif; ?>

        <?php if ($do->status === 'Shipped'): ?>
            <a href="<?= base_url('delivery_fg/cetak_surat_jalan/' . $do->do_no) ?>" class="btn btn-info" target="_blank">
                <i class="fa fa-print"></i> Cetak Surat Jalan
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Delivery Order</h5>
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

<script>
var doNo = '<?= $do->do_no ?>';

function doApprove() {
    Swal.fire({
        title: 'Approve Delivery Order ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Approve',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        $.post(siteurl + 'delivery_fg/process_approve/' + doNo, {}, function (res) {
            if (res.success) {
                Swal.fire({ title: 'Berhasil!', icon: 'success', timer: 1500, showConfirmButton: false })
                    .then(function(){ location.reload(); });
            } else {
                Swal.fire({ title: 'Gagal!', text: res.message, icon: 'error', confirmButtonText: 'OK' });
            }
        }, 'json');
    });
}

function doReject() {
    var alasan = $('#alasan_reject').val().trim();
    if (!alasan) {
        Swal.fire({ title: 'Perhatian', text: 'Alasan reject wajib diisi', icon: 'warning', confirmButtonText: 'OK' });
        return;
    }
    Swal.fire({
        title: 'Reject Delivery Order ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Reject',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        $.post(siteurl + 'delivery_fg/process_reject/' + doNo, { alasan: alasan }, function (res) {
            if (res.success) {
                Swal.fire({ title: 'Berhasil!', icon: 'success', timer: 1500, showConfirmButton: false })
                    .then(function(){ location.reload(); });
            } else {
                Swal.fire({ title: 'Gagal!', text: res.message, icon: 'error', confirmButtonText: 'OK' });
            }
        }, 'json');
    });
}

function doShip() {
    Swal.fire({
        title: 'Konfirmasi Shipped?',
        text: 'Stok FG akan dikurangi dan tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Ship',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        $.post(siteurl + 'delivery_fg/process_ship/' + doNo, {}, function (res) {
            if (res.success) {
                Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false })
                    .then(function(){ location.reload(); });
            } else {
                Swal.fire({ title: 'Gagal!', text: res.message, icon: 'error', confirmButtonText: 'OK' });
            }
        }, 'json');
    });
}

function doCancel() {
    Swal.fire({
        title: 'Batalkan Delivery Order ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        $.post(siteurl + 'delivery_fg/process_cancel/' + doNo, {}, function (res) {
            if (res.success) {
                Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false })
                    .then(function(){ location.reload(); });
            } else {
                Swal.fire({ title: 'Gagal!', text: res.message, icon: 'error', confirmButtonText: 'OK' });
            }
        }, 'json');
    });
}
</script>
