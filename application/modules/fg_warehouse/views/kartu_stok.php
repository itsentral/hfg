<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<!-- Filter Form -->
<div class="card mb-3 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold">
                <i class="fa fa-book me-2"></i> Kartu Stok FG
            </h5>
            <a href="<?= base_url('fg_warehouse/stok_fg') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Stok FG
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= base_url('fg_warehouse/kartu_stok') ?>" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Produk FG <span class="text-danger">*</span></label>
                <select name="produk_fg" class="form-control select2" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($produk_list as $p): ?>
                        <option value="<?= htmlspecialchars($p->produk_fg) ?>"
                            <?= ($produk_fg === $p->produk_fg) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p->produk_fg) ?> — <?= htmlspecialchars($p->nm_produk_fg ? $p->nm_produk_fg : '-') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Tanggal Dari</label>
                <input type="date" name="tgl_dari" class="form-control"
                       value="<?= htmlspecialchars($tgl_dari ? $tgl_dari : '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Tanggal Sampai</label>
                <input type="date" name="tgl_sampai" class="form-control"
                       value="<?= htmlspecialchars($tgl_sampai ? $tgl_sampai : '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search me-1"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($produk_fg)): ?>

<!-- Info Stok Terkini -->
<?php if ($stok_info): ?>
<div class="row mb-3">
    <div class="col-md-12">
        <div class="alert alert-light border border-primary d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="text-primary fw-bold mb-1">
                    <i class="fa fa-cube me-1"></i> [<?= htmlspecialchars($stok_info->produk_fg) ?>] <?= htmlspecialchars($stok_info->nm_produk_fg ? $stok_info->nm_produk_fg : '-') ?>
                </h5>
                <small class="text-muted">
                    Last Update: <?= $stok_info->last_update ? date('d-m-Y H:i:s', strtotime($stok_info->last_update)) : '-' ?>
                </small>
            </div>
            <div>
                <a href="<?= base_url('fg_warehouse/export_excel_kartu_stok/' . urlencode($produk_fg) . '?tgl_dari=' . urlencode($tgl_dari) . '&tgl_sampai=' . urlencode($tgl_sampai)) ?>"
                   class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-primary shadow-sm">
            <div class="card-body py-2">
                <div class="text-muted small fw-bold">Qty Stok</div>
                <div class="fs-4 fw-bold text-primary"><?= number_format((float) $stok_info->qty_stok, 2) ?> <small class="fs-6">pcs</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-info shadow-sm">
            <div class="card-body py-2">
                <div class="text-muted small fw-bold">Total Berat</div>
                <div class="fs-4 fw-bold text-info"><?= number_format((float) $stok_info->total_berat, 3) ?> <small class="fs-6">kg</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-secondary shadow-sm">
            <div class="card-body py-2">
                <div class="text-muted small fw-bold">Berat Ref</div>
                <div class="fs-4 fw-bold text-dark"><?= number_format((float) $stok_info->berat_referensi, 4) ?> <small class="fs-6">kg/pc</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-success shadow-sm">
            <div class="card-body py-2">
                <div class="text-muted small fw-bold">Costbook</div>
                <div class="fs-5 fw-bold text-success">Rp <?= number_format((float) $stok_info->costbook, 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-warning shadow-sm">
            <div class="card-body py-2">
                <div class="text-muted small fw-bold">Total Nilai Stok</div>
                <div class="fs-5 fw-bold text-dark">Rp <?= number_format((float) $stok_info->total_nilai, 2) ?></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Mutasi -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-bold">
            Riwayat Mutasi: <strong><?= htmlspecialchars($produk_fg) ?></strong>
            <?php if ($tgl_dari || $tgl_sampai): ?>
                <small class="text-muted">
                    (<?= $tgl_dari ? $tgl_dari : '...' ?> s/d <?= $tgl_sampai ? $tgl_sampai : '...' ?>)
                </small>
            <?php endif; ?>
        </h6>
        <a href="<?= base_url('fg_warehouse/stok_fg') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-boxes me-1"></i> Semua Stok
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($ledger)): ?>
            <div class="p-3 text-muted">Tidak ada data mutasi untuk filter yang dipilih.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" width="40">No</th>
                        <th class="text-center" width="90">Tanggal</th>
                        <th>No Referensi</th>
                        <th>No SPK</th>
                        <th class="text-center">Kategori</th>
                        <th class="text-center" width="60">Jenis</th>
                        <th class="text-end">Qty IN</th>
                        <th class="text-end">Qty OUT</th>
                        <th class="text-end">Saldo Qty</th>
                        <th class="text-end">Berat IN (kg)</th>
                        <th class="text-end">Berat OUT (kg)</th>
                        <th class="text-end">Saldo Berat (kg)</th>
                        <th class="text-end">Costbook (Rp)</th>
                        <th class="text-end">Total Nilai (Rp)</th>
                        <th class="text-end">Saldo Nilai (Rp)</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ledger as $i => $row): ?>
                    <tr class="<?= $row->jenis_mutasi === 'IN' ? 'table-success bg-opacity-25' : 'table-danger bg-opacity-25' ?>">
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td class="text-center"><?= $row->tgl_transaksi ?></td>
                        <td><?= htmlspecialchars($row->no_referensi) ?></td>
                        <td><?= $row->spk_no ? '<span class="badge bg-light text-dark border">' . htmlspecialchars($row->spk_no) . '</span>' : '-' ?></td>
                        <td class="text-center"><?= $row->category_type ? '<span class="badge bg-secondary">' . htmlspecialchars($row->category_type) . '</span>' : '-' ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $row->jenis_mutasi === 'IN' ? 'success' : 'danger' ?>">
                                <?= $row->jenis_mutasi ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?= $row->jenis_mutasi === 'IN' ? number_format((float) $row->qty_in, 2) : '—' ?>
                        </td>
                        <td class="text-end">
                            <?= $row->jenis_mutasi === 'OUT' ? number_format((float) $row->qty_out, 2) : '—' ?>
                        </td>
                        <td class="text-end fw-bold"><?= number_format((float) $row->qty_saldo, 2) ?></td>
                        <td class="text-end">
                            <?= $row->jenis_mutasi === 'IN' ? number_format((float) $row->berat_in, 3) : '—' ?>
                        </td>
                        <td class="text-end">
                            <?= $row->jenis_mutasi === 'OUT' ? number_format((float) $row->berat_out, 3) : '—' ?>
                        </td>
                        <td class="text-end fw-bold"><?= number_format((float) $row->berat_saldo, 3) ?></td>
                        <td class="text-end"><?= (float) $row->costbook > 0 ? number_format((float) $row->costbook, 2) : '—' ?></td>
                        <td class="text-end"><?= (float) $row->total_nilai > 0 ? number_format((float) $row->total_nilai, 2) : '—' ?></td>
                        <td class="text-end fw-bold text-success"><?= (float) $row->saldo_nilai > 0 ? number_format((float) $row->saldo_nilai, 2) : '—' ?></td>
                        <td><small><?= htmlspecialchars($row->keterangan ? $row->keterangan : '-') ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<div class="alert alert-info shadow-sm">
    <i class="fa fa-info-circle me-1"></i> Pilih produk FG untuk melihat kartu stok.
</div>
<?php endif; ?>
