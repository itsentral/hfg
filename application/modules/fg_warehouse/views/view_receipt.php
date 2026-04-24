<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $this->session->flashdata('success') ?>
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
    'Draft'     => 'secondary',
    'Posted'    => 'success',
    'Cancelled' => 'danger',
];
$badge_color = isset($status_color[$receipt->status]) ? $status_color[$receipt->status] : 'secondary';
?>

<!-- Header Info -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail FG Receipt: <?= $receipt->fg_receipt_no ?></h5>
        <span class="badge bg-<?= $badge_color ?> fs-6"><?= $receipt->status ?></span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%">No Receipt</td>
                        <td><strong><?= $receipt->fg_receipt_no ?></strong></td>
                    </tr>
                    <tr>
                        <td>No Laporan</td>
                        <td>
                            <a href="<?= base_url('production_report/view/' . $receipt->report_no) ?>">
                                <?= $receipt->report_no ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>No SPK</td>
                        <td><?= $receipt->spk_no ? $receipt->spk_no : '-' ?></td>
                    </tr>
                    <tr>
                        <td>No Coil</td>
                        <td><?= $receipt->no_coil ? $receipt->no_coil : '-' ?></td>
                    </tr>
                    <tr>
                        <td>Produk FG</td>
                        <td><?= $receipt->nm_produk_fg ? $receipt->nm_produk_fg : ($receipt->produk_fg ? $receipt->produk_fg : '-') ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%">Dibuat Oleh</td>
                        <td><?= isset($receipt->nama_created_by) ? $receipt->nama_created_by : '-' ?></td>
                    </tr>
                    <tr>
                        <td>Tgl Buat</td>
                        <td><?= $receipt->created_at ?></td>
                    </tr>
                    <?php if ($receipt->posted_by): ?>
                    <tr>
                        <td>Diposting Oleh</td>
                        <td><?= isset($receipt->nama_posted_by) ? $receipt->nama_posted_by : '-' ?></td>
                    </tr>
                    <tr>
                        <td>Tgl Posting</td>
                        <td><?= $receipt->posted_at ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Rincian FG dan KW2 -->
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">Rincian Penerimaan</h6></div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kategori</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Berat (kg)</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-success">
                    <td><strong>FG (Finished Goods)</strong></td>
                    <td class="text-end"><strong><?= number_format((float) $receipt->fg_qty, 2) ?></strong></td>
                    <td class="text-end"><strong><?= number_format((float) $receipt->fg_kg, 3) ?></strong></td>
                </tr>
                <tr>
                    <td>KW2 Internal</td>
                    <td class="text-end"><?= number_format((float) $receipt->kw2_internal_qty, 2) ?></td>
                    <td class="text-end"><?= number_format((float) $receipt->kw2_internal_kg, 3) ?></td>
                </tr>
                <tr>
                    <td>KW2 Supplier</td>
                    <td class="text-end"><?= number_format((float) $receipt->kw2_supplier_qty, 2) ?></td>
                    <td class="text-end"><?= number_format((float) $receipt->kw2_supplier_kg, 3) ?></td>
                </tr>
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="text-end">
                        <strong>
                            <?= number_format((float) $receipt->fg_qty + (float) $receipt->kw2_internal_qty + (float) $receipt->kw2_supplier_qty, 2) ?>
                        </strong>
                    </td>
                    <td class="text-end">
                        <strong>
                            <?= number_format((float) $receipt->fg_kg + (float) $receipt->kw2_internal_kg + (float) $receipt->kw2_supplier_kg, 3) ?>
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Tombol Aksi -->
<div class="card">
    <div class="card-body d-flex flex-wrap gap-2">
        <a href="<?= base_url('fg_warehouse') ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>

        <?php if ($receipt->status === 'Draft'): ?>
            <button type="button" class="btn btn-success" onclick="doPost()">
                <i class="fa fa-upload"></i> Post Receipt
            </button>
        <?php endif; ?>

        <?php if ($receipt->status === 'Posted'): ?>
            <button type="button" class="btn btn-danger" onclick="doCancel()">
                <i class="fa fa-times"></i> Cancel Receipt
            </button>
        <?php endif; ?>
    </div>
</div>

<script>
var receiptNo = '<?= $receipt->fg_receipt_no ?>';

function doPost() {
    Swal.fire({
        title: 'Post FG Receipt ini?',
        text: 'Stok FG akan diupdate.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Post',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        $.post(siteurl + 'fg_warehouse/process_post_receipt/' + receiptNo, {}, function (res) {
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
        title: 'Cancel FG Receipt ini?',
        text: 'Stok FG akan di-reverse.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Cancel',
        cancelButtonText: 'Tidak'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        $.post(siteurl + 'fg_warehouse/process_cancel_receipt/' + receiptNo, {}, function (res) {
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
