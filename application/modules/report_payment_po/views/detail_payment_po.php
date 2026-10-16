<div class="row g-3 mb-4">
    <!-- PO Header Summary -->
    <div class="col-md-7">
        <div class="p-3 rounded-3 bg-light border h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-light-primary text-primary fw-bold fs-6">
                    <i class="fa fa-file-text-o me-1"></i>No. PO: <?= htmlspecialchars($header->no_po ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <span class="badge bg-light-info text-info fw-semibold px-2 py-1">
                    Currency: <?= htmlspecialchars($header->currency ?? 'IDR', ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-12">
                    <small class="text-muted d-block">Supplier</small>
                    <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($header->nm_supplier ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Card -->
    <div class="col-md-5">
        <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-center">
            <div class="row g-2 text-center">
                <div class="col-4">
                    <small class="text-muted d-block">Nilai PO</small>
                    <span class="fw-bold text-dark"><?= number_format($nilai_po, 2); ?></span>
                    <small class="text-xs text-muted d-block"><?= htmlspecialchars($header->currency ?? 'IDR'); ?></small>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">PO Terbayar</small>
                    <span class="fw-bold text-success"><?= number_format($po_terbayar, 2); ?></span>
                    <small class="text-xs text-muted d-block"><?= htmlspecialchars($header->currency ?? 'IDR'); ?></small>
                </div>
                <div class="col-4">
                    <small class="text-muted d-block">Outstanding</small>
                    <span class="fw-bold <?= ($outstanding_po > 0) ? 'text-danger' : 'text-primary'; ?>"><?= number_format($outstanding_po, 2); ?></span>
                    <small class="text-xs text-muted d-block"><?= htmlspecialchars($header->currency ?? 'IDR'); ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 18 Kolom Detail Transaksi Pembayaran PO -->
<div>
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="fw-bold text-dark mb-0">
            <i class="fa fa-list-alt text-primary me-2"></i>Rincian Transaksi Pembayaran (PO, ROS, Receive Invoice, Payment)
        </h6>
        <small class="text-muted"><i class="fa fa-arrows-h me-1"></i>Scroll ke samping untuk melihat seluruh kolom</small>
    </div>

    <div class="table-responsive border rounded" style="max-height: 60vh; overflow-y: auto;">
        <table class="table table-bordered table-sm table-hover align-middle mb-0" style="font-size: 12.5px; white-space: nowrap;">
            <thead class="table-light sticky-top" style="z-index: 10;">
                <tr class="text-center">
                    <th colspan="5" class="bg-primary bg-opacity-10 text-primary border-bottom-0 py-2">PO</th>
                    <th colspan="3" class="bg-success bg-opacity-10 text-success border-bottom-0 py-2">ROS / Incoming</th>
                    <th colspan="4" class="bg-warning bg-opacity-10 text-warning border-bottom-0 py-2">Receive Invoice</th>
                    <th colspan="6" class="bg-danger bg-opacity-10 text-danger border-bottom-0 py-2">Payment</th>
                </tr>
                <tr class="text-center text-uppercase" style="font-size: 11px;">
                    <!-- PO -->
                    <th class="bg-light">No. PO</th>
                    <th class="bg-light">Tipe PO</th>
                    <th class="bg-light text-end">Value PO</th>
                    <th class="bg-light">Tipe TOP</th>
                    <th class="bg-light text-end">Value %</th>
                    <!-- ROS -->
                    <th class="bg-light text-end">Total Material</th>
                    <th class="bg-light text-end">Unbill</th>
                    <th class="bg-light text-end">Selisih Kurs</th>
                    <!-- Receive Invoice -->
                    <th class="bg-light text-end">Receive Invoice</th>
                    <th class="bg-light text-end">Kurs</th>
                    <th class="bg-light text-end">Value Receive</th>
                    <th class="bg-light text-end">Selisih Kurs</th>
                    <!-- Payment -->
                    <th class="bg-light text-end">Invoice Pay</th>
                    <th class="bg-light text-end">Kurs Pay</th>
                    <th class="bg-light text-center">Currency Pay</th>
                    <th class="bg-light text-end">Payment IDR</th>
                    <th class="bg-light text-end">Admin</th>
                    <th class="bg-light text-end">Selisih Kurs</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($details)) : ?>
                    <?php 
                    $is_first = true;
                    $row_count = count($details);
                    foreach ($details as $item) : 
                        if ($item['category'] == 'dp') {
                            $category_label = 'Uang Muka';
                            $category_badge = '<span class="badge bg-warning bg-opacity-25 text-dark fw-semibold">Uang Muka</span>';
                        } elseif ($item['category'] == 'import') {
                            $category_label = 'Pelunasan (After ROS)';
                            $category_badge = '<span class="badge bg-info bg-opacity-25 text-dark fw-semibold">Pelunasan (After ROS)</span>';
                        } elseif ($item['category'] == 'local') {
                            $category_label = 'Pelunasan (After Incoming)';
                            $category_badge = '<span class="badge bg-success bg-opacity-25 text-dark fw-semibold">Pelunasan (After Incoming)</span>';
                        } else {
                            $category_label = ucfirst($item['category']);
                            $category_badge = '<span class="badge bg-secondary bg-opacity-25 text-dark fw-semibold">' . htmlspecialchars($category_label) . '</span>';
                        }

                        $selisih_kurs_receive_1 = abs($item['selisih_kurs_receive_1']);
                        $selisih_kurs_receive_2 = abs($item['selisih_kurs_receive_2']);
                        $selisih_kurs_admin     = abs($item['selisih_kurs_admin']);
                    ?>
                        <tr>
                            <?php if ($is_first) : ?>
                                <td rowspan="<?= $row_count; ?>" class="text-center fw-bold bg-white align-middle">
                                    <span class="badge bg-light-primary text-primary font-monospace px-2 py-1"><?= htmlspecialchars($item['no_po']); ?></span>
                                </td>
                            <?php endif; ?>
                            <td><?= $category_badge; ?></td>
                            <td class="text-end fw-semibold"><?= number_format($item['value_po'], 2); ?></td>
                            <td><?= htmlspecialchars($item['tipe_top'] ?? '-'); ?></td>
                            <td class="text-end"><?= $item['value_pct']; ?></td>
                            <td class="text-end"><?= number_format($item['total_material'], 2); ?></td>
                            <td class="text-end"><?= number_format($item['unbill'], 2); ?></td>
                            <td class="text-end"><?= number_format($selisih_kurs_receive_1, 2); ?></td>
                            <td class="text-end fw-semibold text-primary"><?= number_format($item['receive_invoice_value'], 2); ?></td>
                            <td class="text-end"><?= number_format($item['receive_kurs'], 2); ?></td>
                            <td class="text-end"><?= number_format($item['value_receive_idr'], 2); ?></td>
                            <td class="text-end"><?= number_format($selisih_kurs_receive_2, 2); ?></td>
                            <td class="text-end fw-bold text-success"><?= number_format($item['invoice_pay'], 2); ?></td>
                            <td class="text-end"><?= number_format($item['kurs_pay'], 2); ?></td>
                            <td class="text-center font-monospace small"><?= htmlspecialchars($item['currency_pay'] ?? '-'); ?></td>
                            <td class="text-end fw-semibold"><?= number_format($item['payment_idr'], 2); ?></td>
                            <td class="text-end"><?= number_format($item['admin_bank'], 2); ?></td>
                            <td class="text-end <?= ($selisih_kurs_admin == 0) ? 'text-muted' : 'text-danger fw-semibold'; ?>"><?= number_format($selisih_kurs_admin, 2); ?></td>
                        </tr>
                        <?php $is_first = false; ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="18" class="text-center py-4 text-muted">
                            <i class="fa fa-info-circle fa-2x mb-2 d-block text-secondary"></i>
                            Tidak ada rincian transaksi invoice atau payment untuk PO ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
