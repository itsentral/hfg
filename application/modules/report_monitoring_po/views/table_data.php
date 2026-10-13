<!-- Table Card Monitoring PO -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="table_monitoring_po" class="table table-striped table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 45px;">#</th>
                        <th style="width: 145px;">No. PO</th>
                        <th style="width: 100px;">Tanggal PO</th>
                        <th style="width: 175px;">Supplier</th>
                        <th style="width: 170px;">No. PR & Tipe PR</th>
                        <th style="min-width: 250px;">Item / Spek Material</th>
                        <th class="text-end" style="width: 130px;">Total Qty PO (Kg)</th>
                        <th class="text-end" style="width: 130px;">Total Diterima (Kg)</th>
                        <th class="text-end" style="width: 130px;">Sisa Qty Belum Dikirim (Kg)</th>
                        <th class="text-center" style="width: 140px;">% Progress & Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($results)) : ?>
                        <?php $numb = 0;
                        foreach ($results as $record) : $numb++; ?>
                            <tr>
                                <td class="text-center text-muted"><?= $numb; ?></td>
                                <td>
                                    <!-- No. PO klikable untuk melihat detail -->
                                    <a href="javascript:void(0)"
                                        class="btn-detail text-primary fw-bold text-decoration-none d-inline-flex align-items-center gap-1"
                                        data-id="<?= $record->no_po; ?>"
                                        data-surat="<?= htmlspecialchars($record->no_surat ?? $record->no_po, ENT_QUOTES, 'UTF-8'); ?>"
                                        title="Klik untuk melihat detail riwayat kedatangan PO">
                                        <span><?= htmlspecialchars($record->no_surat ?? $record->no_po, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-secondary small">
                                        <?= !empty($record->tanggal) ? date('d M Y', strtotime($record->tanggal)) : '-'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark small">
                                        <?= htmlspecialchars($record->nm_supplier ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Kolom No. PR & Tipe PR disatukan -->
                                    <?php if (!empty($record->pr_arr)) : ?>
                                        <div class="d-flex flex-column gap-1">
                                            <?php foreach ($record->pr_arr as $pr) : ?>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark font-monospace small">
                                                        <i class="ti ti-clipboard-list text-primary me-1"></i><?= htmlspecialchars($pr['no_pr'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                    <span class="badge bg-light text-secondary text-uppercase border" style="font-size: 10px; width: fit-content;">
                                                        <?= htmlspecialchars($pr['tipe_pr'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($record->materials_arr)) : ?>
                                        <div class="d-flex flex-column gap-1">
                                            <?php foreach ($record->materials_arr as $mat) : ?>
                                                <span class="badge bg-light text-dark text-start text-wrap fw-normal border" style="font-size: 11px; line-height: 1.4;">
                                                    <i class="ti ti-box text-secondary me-1"></i><?= htmlspecialchars($mat, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    <?= number_format($record->total_qty_po, 2); ?>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    <?= number_format($record->total_diterima, 2); ?>
                                </td>
                                <td class="text-end fw-semibold <?= ($record->sisa_qty < 0) ? 'text-danger' : (($record->sisa_qty == 0) ? 'text-muted' : 'text-primary'); ?>">
                                    <?= number_format($record->sisa_qty, 2); ?>
                                </td>
                                <td class="text-center">
                                    <!-- Progress & Status ditaruh 1 kolom -->
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="small fw-bold <?= ($record->progress_pct >= 100) ? 'text-success' : 'text-primary'; ?>">
                                            <?= $record->progress_pct; ?>%
                                        </span>
                                        <div class="progress w-100" style="height: 6px; max-width: 90px;">
                                            <div class="progress-bar <?= ($record->progress_pct > 100) ? 'bg-danger' : (($record->progress_pct == 100) ? 'bg-success' : 'bg-primary'); ?>"
                                                role="progressbar"
                                                style="width: <?= min($record->progress_pct, 100); ?>%"></div>
                                        </div>
                                        <div class="mt-1">
                                            <?= $record->status_badge; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>