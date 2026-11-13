<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    input.flatpickr-input.form-control, input.flatpickr-input[readonly] { background-color: #fff !important; }
</style>

<!-- Filter Card Berdasarkan Kategori Stok -->
<div class="card shadow-sm mb-3">
    <div class="card-header py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold">
                <i class="fa fa-filter me-2"></i> Filter Riwayat Mutasi Finish Good
            </h5>
            <a href="<?= base_url('fg_stock') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Rekap Stok
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= base_url('fg_stock/history') ?>" class="row g-3 align-items-end">
            <!-- Filter Kategori Stok (Utama) -->
            <div class="col-md-3">
                <label class="form-label fw-bold">Kategori Stok <span class="text-danger">*</span></label>
                <select name="kategori" id="select_kategori" class="form-select">
                    <option value="ALL" <?= ($kategori === 'ALL' || empty($kategori)) ? 'selected' : '' ?>>-- Semua Kategori --</option>
                    <option value="KW1" <?= ($kategori === 'KW1') ? 'selected' : '' ?>>1. Finish Good KW 1</option>
                    <option value="KW2" <?= ($kategori === 'KW2') ? 'selected' : '' ?>>2. Finish Good KW 2</option>
                    <option value="TONG_COIL" <?= ($kategori === 'TONG_COIL') ? 'selected' : '' ?>>3. Tong Coil</option>
                    <option value="SCRAP" <?= ($kategori === 'SCRAP') ? 'selected' : '' ?>>4. Scrap / Waste</option>
                </select>
            </div>

            <!-- Filter Produk (Opsional) -->
            <div class="col-md-3">
                <label class="form-label fw-bold">Pilih Produk <small class="text-muted fw-normal">(Opsional)</small></label>
                <select name="produk_fg" id="select_produk_fg" class="form-control select2">
                    <option value="">-- Semua Produk --</option>
                    <?php if (!empty($produk_dropdown)): ?>
                        <?php foreach ($produk_dropdown as $p): ?>
                            <option value="<?= htmlspecialchars($p->produk_fg) ?>"
                                data-kategori="<?= htmlspecialchars($p->kategori_stok) ?>"
                                <?= ($produk_fg === $p->produk_fg) ? 'selected' : '' ?>>
                                [<?= htmlspecialchars(isset($p->kategori_stok) ? $p->kategori_stok : 'KW1') ?>] <?= htmlspecialchars($p->produk_fg) ?> — <?= htmlspecialchars($p->nm_produk_fg ? $p->nm_produk_fg : '-') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Tanggal Dari -->
            <div class="col-md-2">
                <label class="form-label fw-bold">Tanggal Dari</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                    <input type="text" name="tgl_dari" id="tgl_dari" class="form-control flatpickr-date bg-white"
                        placeholder="Pilih Tanggal" autocomplete="off"
                        value="<?= htmlspecialchars($tgl_dari ? $tgl_dari : '') ?>">
                </div>
            </div>

            <!-- Tanggal Sampai -->
            <div class="col-md-2">
                <label class="form-label fw-bold">Tanggal Sampai</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                    <input type="text" name="tgl_sampai" id="tgl_sampai" class="form-control flatpickr-date bg-white"
                        placeholder="Pilih Tanggal" autocomplete="off"
                        value="<?= htmlspecialchars($tgl_sampai ? $tgl_sampai : '') ?>">
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fa fa-search me-1"></i> Filter
                </button>
                <a href="<?= base_url('fg_stock/history') ?>" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="fa fa-refresh"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Info Ringkasan Kategori -->
<div class="row mb-3 g-2">
    <div class="col-md-12">
        <div class="alert alert-light border border-primary d-flex justify-content-between align-items-center mb-0 py-2">
            <div>
                <h5 class="text-primary fw-bold mb-0">
                    <i class="fa fa-layer-group me-1"></i>
                    <?php
                        $cat_names = [
                            'ALL'       => 'Semua Kategori (KW 1, KW 2, Tong Coil, Scrap)',
                            'KW1'       => '1. Finish Good KW 1',
                            'KW2'       => '2. Finish Good KW 2',
                            'TONG_COIL' => '3. Tong Coil',
                            'SCRAP'     => '4. Scrap / Waste'
                        ];
                        echo isset($cat_names[$kategori]) ? $cat_names[$kategori] : 'Semua Kategori';
                    ?>
                    <?php if (!empty($produk_fg)): ?>
                        <span class="badge bg-secondary fs-6 ms-2">Produk: <?= htmlspecialchars($produk_fg) ?></span>
                    <?php endif; ?>
                </h5>
            </div>
            <div>
                <a href="<?= base_url('fg_stock/export_excel_history/' . urlencode($kategori) . '?produk_fg=' . urlencode($produk_fg) . '&tgl_dari=' . urlencode($tgl_dari) . '&tgl_sampai=' . urlencode($tgl_sampai)) ?>"
                    class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel me-1"></i> Export Excel Riwayat
                </a>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards Sesuai Kategori Terpilih -->
    <div class="col-md-3">
        <div class="card text-center border-primary shadow-sm h-100">
            <div class="card-body py-2">
                <div class="text-muted small text-uppercase fw-bold">Total Item / SKU</div>
                <div class="fs-4 fw-bold text-primary"><?= number_format((int) (isset($summary_kategori->total_sku) ? $summary_kategori->total_sku : 0)) ?> <small class="fs-6 text-muted">SKU</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-success shadow-sm h-100">
            <div class="card-body py-2">
                <div class="text-muted small text-uppercase fw-bold">Total Qty Tersedia</div>
                <div class="fs-4 fw-bold text-success"><?= number_format((float) (isset($summary_kategori->total_qty) ? $summary_kategori->total_qty : 0), 2) ?> <small class="fs-6 text-muted">pcs/roll</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-info shadow-sm h-100">
            <div class="card-body py-2">
                <div class="text-muted small text-uppercase fw-bold">Total Berat Stok</div>
                <div class="fs-4 fw-bold text-info"><?= number_format((float) (isset($summary_kategori->total_berat) ? $summary_kategori->total_berat : 0), 3) ?> <small class="fs-6 text-muted">kg</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-warning shadow-sm h-100">
            <div class="card-body py-2">
                <div class="text-muted small text-uppercase fw-bold">Total Nilai Persediaan</div>
                <div class="fs-5 fw-bold text-dark">Rp <?= number_format((float) (isset($summary_kategori->total_nilai) ? $summary_kategori->total_nilai : 0), 2) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Mutasi Kartu Stok -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="fa fa-history me-1"></i> Riwayat Mutasi Kartu Stok
            <?php if (!empty($tgl_dari) || !empty($tgl_sampai)): ?>
                <span class="badge bg-secondary ms-2">
                    Periode: <?= $tgl_dari ? date('d-m-Y', strtotime($tgl_dari)) : 'Awal' ?> s/d <?= $tgl_sampai ? date('d-m-Y', strtotime($tgl_sampai)) : 'Sekarang' ?>
                </span>
            <?php endif; ?>
        </h6>
        <span class="badge bg-primary fs-6"><?= count($ledger) ?> Catatan Transaksi</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($ledger)): ?>
            <div class="p-4 text-center text-muted">
                <i class="fa fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                Belum ada riwayat mutasi untuk kategori/produk ini pada periode yang dipilih.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover align-middle mb-0" id="tbl-ledger">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="40">No</th>
                            <th class="text-center" width="90">Tanggal</th>
                            <th>Kode & Nama Produk</th>
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
                                <td class="text-center"><?= date('d-m-Y', strtotime($row->tgl_transaksi)) ?></td>
                                <td>
                                    <strong class="text-primary"><?= htmlspecialchars($row->produk_fg) ?></strong>
                                    <small class="d-block text-muted"><?= htmlspecialchars($row->nm_produk_fg ? $row->nm_produk_fg : '-') ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row->no_referensi) ?></strong>
                                </td>
                                <td>
                                    <?= $row->spk_no ? '<span class="badge bg-light text-dark border">' . htmlspecialchars($row->spk_no) . '</span>' : '-' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= htmlspecialchars($row->kategori_stok ? $row->kategori_stok : ($row->category_type ?: '-')) ?></span>
                                    <?php if (!empty($row->sub_kategori)): ?>
                                        <small class="d-block text-muted"><?= htmlspecialchars($row->sub_kategori) ?></small>
                                    <?php endif; ?>
                                </td>
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
                                <td class="text-end fw-bold">
                                    <?= number_format((float) $row->qty_saldo, 2) ?>
                                </td>
                                <td class="text-end">
                                    <?= $row->jenis_mutasi === 'IN' ? number_format((float) $row->berat_in, 3) : '—' ?>
                                </td>
                                <td class="text-end">
                                    <?= $row->jenis_mutasi === 'OUT' ? number_format((float) $row->berat_out, 3) : '—' ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <?= number_format((float) $row->berat_saldo, 3) ?>
                                </td>
                                <td class="text-end">
                                    <?= (float) $row->costbook > 0 ? number_format((float) $row->costbook, 2) : '—' ?>
                                </td>
                                <td class="text-end">
                                    <?= (float) $row->total_nilai > 0 ? number_format((float) $row->total_nilai, 2) : '—' ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    <?= (float) $row->saldo_nilai > 0 ? number_format((float) $row->saldo_nilai, 2) : '—' ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($row->keterangan ? $row->keterangan : '-') ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi Flatpickr Tanggal
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#tgl_dari', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                allowInput: false
            });
            flatpickr('#tgl_sampai', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                allowInput: false
            });
        }

        if ($.fn.select2) {
            $('#select_produk_fg').select2({
                placeholder: "-- Semua Produk --",
                allowClear: true,
                width: '100%'
            });
        }

        // Sinkronisasi dropdown produk saat kategori diubah
        $('#select_kategori').on('change', function() {
            var selectedCat = $(this).val();
            $('#select_produk_fg option').each(function() {
                var optCat = $(this).data('kategori');
                if (!optCat || selectedCat === 'ALL' || optCat === selectedCat) {
                    $(this).prop('disabled', false);
                } else {
                    $(this).prop('disabled', true);
                }
            });

            // Jika produk yang sedang terpilih tidak cocok dengan kategori baru, reset pilihan produk
            var currentVal = $('#select_produk_fg').val();
            if (currentVal) {
                var curOpt = $('#select_produk_fg').find('option[value="' + currentVal + '"]');
                if (curOpt.prop('disabled')) {
                    $('#select_produk_fg').val('').trigger('change');
                }
            }

            if ($.fn.select2) {
                $('#select_produk_fg').select2({
                    placeholder: "-- Semua Produk --",
                    allowClear: true,
                    width: '100%'
                });
            }
        });
    });
</script>
