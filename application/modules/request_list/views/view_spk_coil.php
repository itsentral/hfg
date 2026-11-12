<?php
// Status badge classes
$status_badges = [
    'Material Requested' => 'bg-warning text-dark',
    'Material On Load'   => 'bg-info text-dark',
    'Material Confirmed' => 'bg-primary',
    'Released'           => 'bg-success',
    'Cancelled'          => 'bg-danger',
];
$badge_class = isset($status_badges[$spk['status']]) ? $status_badges[$spk['status']] : 'bg-secondary';

$total_qty    = 0;
$total_weight = 0;
foreach ($details as $d) {
    $total_qty    += (int) $d['target_qty'];
    $total_weight += (float) $d['total_weight'];
}

$total_scan_qty_kg = 0;
if (!empty($scan_pack_history)) {
    foreach ($scan_pack_history as $h) {
        $total_scan_qty_kg += (float) $h['qty_trans'];
    }
}
?>

<!-- Card Detail SPK Material -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-file-text-o"></i> Detail SPK Material</h5>
        <div>
            <a href="<?= site_url('request_list') ?>" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td width="35%" class="fw-bold">No. SPK</td>
                        <td>: <span class="badge bg-dark fs-6"><?= htmlspecialchars($spk['spk_no']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Tanggal SPK</td>
                        <td>: <?= date('d M Y', strtotime($spk['tgl_spk'])) ?></td>
                    </tr>
                    <?php if (!empty($spk['due_date'])): ?>
                        <tr>
                            <td class="fw-bold">Due Date</td>
                            <td>: <?= date('d M Y', strtotime($spk['due_date'])) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="fw-bold">Shift</td>
                        <td>: <?= htmlspecialchars($spk['shift_names']) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td width="35%" class="fw-bold">Status</td>
                        <td>: <span class="badge rounded-pill <?= $badge_class ?>"><?= htmlspecialchars($spk['status']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Dibuat Oleh</td>
                        <td>: <?= htmlspecialchars(!empty($spk['created_by_name']) ? $spk['created_by_name'] : $spk['created_by']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Catatan</td>
                        <td>: <?= nl2br(htmlspecialchars($spk['catatan'] ?: '-')) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Card Detail Produk -->
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-cubes"></i> Detail Produk (<?= count($details) ?> item)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="8%" class="text-center">Urut</th>
                        <th>Nama Produk</th>
                        <th width="18%" class="text-end">Target Qty (pcs)</th>
                        <th width="20%" class="text-end">Total Weight (Kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($details)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Belum ada detail produk.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($details as $d): ?>
                            <tr>
                                <td class="text-center"><?= (int) $d['urut'] ?></td>
                                <td>
                                    <?= htmlspecialchars($d['nm_produk_fg']) ?>
                                    <?php if (!empty($d['thickness']) && (float) $d['thickness'] > 0): ?>
                                        <span class="badge bg-info text-dark ms-1">Varian <?= number_format((float) $d['thickness'], 2) ?> mm</span>
                                    <?php elseif (!empty($d['no_uniq_id'])): ?>
                                        <span class="badge bg-light text-secondary border ms-1"><?= htmlspecialchars($d['no_uniq_id']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= number_format((int) $d['target_qty'], 0, ',', '.') ?></td>
                                <td class="text-end"><?= number_format((float) $d['total_weight'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end"><?= number_format($total_qty, 0, ',', '.') ?> pcs</td>
                        <td class="text-end"><?= number_format($total_weight, 2, ',', '.') ?> Kg</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Card Riwayat Scan Pack (Read-Only) -->
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fa fa-qrcode text-primary me-1"></i>
            Riwayat Scan Pack ke WRH. Production 2 (<?= count($scan_pack_history) ?> coil)
        </h6>
        <span class="badge bg-light text-dark border">Read-Only</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="13%">No. Pack</th>
                        <th>Material</th>
                        <th width="14%">No. Coil</th>
                        <th width="16%">Kode Internal</th>
                        <th width="12%" class="text-end">Net Weight (Kg)</th>
                        <th width="15%">Waktu Scan</th>
                        <th width="12%">Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($scan_pack_history)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">
                                Belum ada Pack yang di-scan ke WRH. Production 2 dengan No. Transaksi SPK ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($scan_pack_history as $idx => $h): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars(!empty($h['pack_code']) ? $h['pack_code'] : '-') ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($h['nm_material'] ?: '-') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($h['id_material']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($h['no_coil'] ?: '-') ?></td>
                                <td><code><?= htmlspecialchars($h['kode_internal'] ?: '-') ?></code></td>
                                <td class="text-end fw-semibold"><?= number_format((float) $h['qty_trans'], 2, ',', '.') ?></td>
                                <td><?= !empty($h['created_at']) ? date('d M Y H:i', strtotime($h['created_at'])) : '-' ?></td>
                                <td><?= htmlspecialchars($h['scanned_by_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($scan_pack_history)): ?>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end">Total Masuk WRH. Production 2</td>
                            <td class="text-end"><?= number_format($total_scan_qty_kg, 2, ',', '.') ?> Kg</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Card Print PDF -->
<div class="card mt-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            <i class="fa fa-info-circle me-1"></i> Dokumen SPK dapat dicetak untuk acuan persiapan material &amp; produksi.
        </span>
        <a href="<?= site_url('spk_material/print_pdf/' . rawurlencode($spk['spk_no'])) ?>" target="_blank" class="btn btn-secondary">
            <i class="fa fa-print me-1"></i> Print PDF
        </a>
    </div>
</div>
