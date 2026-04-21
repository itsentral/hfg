<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<!-- Filter Form -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Kartu Stok FG</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= base_url('fg_warehouse/kartu_stok') ?>" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Produk FG <span class="text-danger">*</span></label>
                <select name="produk_fg" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($produk_list as $p): ?>
                        <option value="<?= $p->produk_fg ?>"
                            <?= ($produk_fg === $p->produk_fg) ? 'selected' : '' ?>>
                            <?= $p->produk_fg ?> — <?= $p->nm_produk_fg ? $p->nm_produk_fg : '-' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Dari</label>
                <input type="date" name="tgl_dari" class="form-control"
                       value="<?= $tgl_dari ? $tgl_dari : '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Sampai</label>
                <input type="date" name="tgl_sampai" class="form-control"
                       value="<?= $tgl_sampai ? $tgl_sampai : '' ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($produk_fg)): ?>

<!-- Info Stok Terkini -->
<?php if ($stok_info): ?>
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card text-center border-primary">
            <div class="card-body py-2">
                <div class="text-muted small">Qty Stok</div>
                <div class="fs-4 fw-bold text-primary"><?= number_format((float) $stok_info->qty_stok, 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-info">
            <div class="card-body py-2">
                <div class="text-muted small">Total Berat (kg)</div>
                <div class="fs-4 fw-bold text-info"><?= number_format((float) $stok_info->total_berat, 3) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-success">
            <div class="card-body py-2">
                <div class="text-muted small">Berat Referensi (kg/pcs)</div>
                <div class="fs-4 fw-bold text-success"><?= number_format((float) $stok_info->berat_referensi, 4) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body py-2">
                <div class="text-muted small">Last Update</div>
                <div class="fw-bold"><?= $stok_info->last_update ? $stok_info->last_update : '-' ?></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Mutasi -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            Riwayat Mutasi: <strong><?= $produk_fg ?></strong>
            <?php if ($tgl_dari || $tgl_sampai): ?>
                <small class="text-muted">
                    (<?= $tgl_dari ? $tgl_dari : '...' ?> s/d <?= $tgl_sampai ? $tgl_sampai : '...' ?>)
                </small>
            <?php endif; ?>
        </h6>
        <a href="<?= base_url('fg_warehouse/stok_fg') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-boxes"></i> Semua Stok
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($ledger)): ?>
            <div class="p-3 text-muted">Tidak ada data mutasi untuk filter yang dipilih.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th class="text-center">Tgl Transaksi</th>
                        <th>No Referensi</th>
                        <th class="text-center">Jenis</th>
                        <th class="text-end">Qty IN</th>
                        <th class="text-end">Qty OUT</th>
                        <th class="text-end">Berat IN (kg)</th>
                        <th class="text-end">Berat OUT (kg)</th>
                        <th class="text-end">Saldo Qty</th>
                        <th class="text-end">Saldo Berat (kg)</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ledger as $i => $row): ?>
                    <tr class="<?= $row->jenis_mutasi === 'IN' ? 'table-success' : 'table-danger' ?>">
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td class="text-center"><?= $row->tgl_transaksi ?></td>
                        <td><?= $row->no_referensi ?></td>
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
                        <td class="text-end">
                            <?= $row->jenis_mutasi === 'IN' ? number_format((float) $row->berat_in, 3) : '—' ?>
                        </td>
                        <td class="text-end">
                            <?= $row->jenis_mutasi === 'OUT' ? number_format((float) $row->berat_out, 3) : '—' ?>
                        </td>
                        <td class="text-end fw-bold"><?= number_format((float) $row->qty_saldo, 2) ?></td>
                        <td class="text-end fw-bold"><?= number_format((float) $row->berat_saldo, 3) ?></td>
                        <td><small><?= $row->keterangan ? $row->keterangan : '-' ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<div class="alert alert-info">
    <i class="fa fa-info-circle"></i> Pilih produk FG untuk melihat kartu stok.
</div>
<?php endif; ?>
