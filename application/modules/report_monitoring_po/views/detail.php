<div class="row g-3 mb-4">
    <!-- PO Header Summary -->
    <div class="col-md-7">
        <div class="p-3 rounded-3 bg-light border h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-light-primary text-primary fw-bold fs-6">
                    <i class="ti ti-file-text me-1"></i>No. PO: <?= htmlspecialchars($header->no_surat ?? $header->no_po, ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <div>
                    <?= $status_badge; ?>
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-sm-6">
                    <small class="text-muted d-block">Tanggal PO</small>
                    <span class="fw-semibold text-dark"><?= !empty($header->tanggal) ? date('d F Y', strtotime($header->tanggal)) : '-'; ?></span>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">Supplier</small>
                    <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($header->nm_supplier ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="col-12 mt-2">
                    <small class="text-muted d-block">Referensi PR</small>
                    <span class="fw-semibold text-primary font-monospace small"><?= htmlspecialchars($pr_summary ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Card -->
    <div class="col-md-5">
        <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-between">
            <div class="row g-2 text-center">
                <div class="col-4">
                    <small class="text-muted d-block">Total Qty PO</small>
                    <span class="fw-bold text-dark"><?= number_format($total_qty_po, 2); ?></span>
                    <small class="text-xs text-muted d-block">Kg</small>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">Total Diterima</small>
                    <span class="fw-bold text-success"><?= number_format($total_diterima, 2); ?></span>
                    <small class="text-xs text-muted d-block">Kg</small>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">Sisa Belum Dikirim</small>
                    <span class="fw-bold <?= ($sisa_qty < 0) ? 'text-danger' : 'text-primary'; ?>"><?= number_format($sisa_qty, 2); ?></span>
                    <small class="text-xs text-muted d-block">Kg</small>
                </div>
            </div>

            <div class="mt-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-semibold">Progres Pengiriman</small>
                    <span class="fw-bold small text-primary"><?= $progress; ?>%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar <?= ($progress > 100) ? 'bg-danger' : (($progress == 100) ? 'bg-success' : 'bg-primary'); ?>" 
                         role="progressbar" 
                         style="width: <?= min($progress, 100); ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ordered Items in PO -->
<div class="mb-4">
    <h6 class="fw-bold text-dark mb-2">
        <i class="ti ti-boxes text-primary me-2"></i>Daftar Item / Spek Material yang Dipesan
    </h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 40px;">#</th>
                    <th>Item / Spek Material</th>
                    <th style="width: 200px;">No. PR & Tipe PR</th>
                    <th class="text-end" style="width: 160px;">Qty Dipesan (Kg)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($po_materials)) : ?>
                    <?php $no = 0; foreach ($po_materials as $pm) : $no++; ?>
                        <tr>
                            <td class="text-center text-muted"><?= $no; ?></td>
                            <td>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($pm->namamaterial, ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td>
                                <?php if (!empty($pm->no_pr)) : ?>
                                    <span class="fw-bold text-dark font-monospace small d-block"><?= htmlspecialchars($pm->no_pr, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="badge bg-light text-secondary text-uppercase border" style="font-size: 10px;"><?= htmlspecialchars($pm->tipe_pr ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php else : ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold text-dark"><?= number_format($pm->qty, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-2">Tidak ada data item material</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Riwayat Kedatangan Barang (Chronological: Oldest to Newest) -->
<div>
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="fw-bold text-dark mb-0">
            <i class="ti ti-history text-primary me-2"></i>Riwayat Kedatangan Barang (Incoming Deliveries)
        </h6>
        <span class="badge bg-light-info text-info small">
            <i class="ti ti-arrows-sort me-1"></i>Urutan: Paling lama di atas (kronologis)
        </span>
    </div>

    <?php if (!empty($incoming_history)) : ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 45px;">#</th>
                        <th style="width: 140px;">No. PO</th>
                        <th>Item / Spek Material</th>
                        <th style="width: 150px;">No. Incoming</th>
                        <th class="text-center" style="width: 110px;">Tanggal Terima</th>
                        <th class="text-end" style="width: 140px;">Qty Diterima (Kg)</th>
                        <th class="text-end" style="width: 150px;">Kumulatif Diterima (Kg)</th>
                        <th class="text-end" style="width: 160px;">Sisa Qty Setelah Ini (Kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no_inc = 0;
                    foreach ($incoming_history as $row) : 
                        $no_inc++; 
                    ?>
                        <tr>
                            <td class="text-center text-muted"><?= $no_inc; ?></td>
                            <td>
                                <span class="fw-semibold text-dark">
                                    <?= htmlspecialchars($row->no_surat ?? $row->no_po, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-medium text-dark">
                                    <?= htmlspecialchars($row->nm_material, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light-primary text-primary font-monospace fw-bold px-2 py-1">
                                    <i class="ti ti-receipt me-1"></i><?= htmlspecialchars($row->kode_trans, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td class="text-center text-muted">
                                <?= !empty($row->tanggal) ? date('d/m/Y', strtotime($row->tanggal)) : '-'; ?>
                            </td>
                            <td class="text-end fw-bold text-success">
                                +<?= number_format($row->qty_diterima, 2); ?>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                <?= number_format($row->kumulatif_diterima, 2); ?>
                            </td>
                            <td class="text-end fw-bold <?= ($row->sisa_setelah_kedatangan < 0) ? 'text-danger' : (($row->sisa_setelah_kedatangan == 0) ? 'text-muted' : 'text-primary'); ?>">
                                <?= number_format($row->sisa_setelah_kedatangan, 2); ?>
                                <?php if ($row->sisa_setelah_kedatangan < 0) : ?>
                                    <span class="badge bg-danger ms-1 text-xs">Over</span>
                                <?php elseif ($row->sisa_setelah_kedatangan == 0) : ?>
                                    <span class="badge bg-success ms-1 text-xs">Pass</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="5" class="text-end">Total Akhir Diterima:</td>
                        <td class="text-end text-success"><?= number_format($total_diterima, 2); ?> Kg</td>
                        <td class="text-end text-dark"><?= number_format($total_diterima, 2); ?> Kg</td>
                        <td class="text-end <?= ($sisa_qty < 0) ? 'text-danger' : 'text-primary'; ?>">
                            <?= number_format($sisa_qty, 2); ?> Kg
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else : ?>
        <div class="alert alert-secondary d-flex align-items-center py-4 my-2">
            <i class="ti ti-info-circle fs-2 text-secondary me-3"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">Belum Ada Riwayat Penerimaan</h6>
                <p class="mb-0 small text-muted">Belum ada transaksi kedatangan (Incoming) yang tercatat untuk PO ini di sistem. Status PO saat ini adalah <strong>Belum Mulai</strong>.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
