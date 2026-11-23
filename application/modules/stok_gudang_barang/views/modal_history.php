<div class="modal-history-wrapper">
    <!-- Header Ringkasan Card -->
    <div class="card border border-light-subtle shadow-none bg-light bg-opacity-50 mb-3">
        <div class="card-body p-3">
            <div class="row align-items-center g-2">
                <div class="col-md-7 col-12">
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem;">Stock Item Name</span>
                    <h5 class="fw-bold text-primary mb-0">
                        <?= strtoupper((string) get_name('accessories', 'stock_name', 'id', $material)); ?>
                    </h5>
                    <small class="text-muted font-monospace">
                        Kode: <?= strtoupper((string) get_name('accessories', 'id_stock', 'id', $material)); ?>
                    </small>
                </div>
                <div class="col-md-5 col-12 text-md-end">
                    <span class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.75rem;">Selected Warehouse</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 fs-6">
                        <i class="fa fa-warehouse me-1"></i> <?= strtoupper((string) get_name('warehouse', 'nm_gudang', 'id', $gudang)); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Mutasi -->
    <div class="table-responsive border rounded" style="max-height: 480px; overflow-y: auto;">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-light sticky-top">
                <tr>
                    <th class="text-center" width="4%">#</th>
                    <th class="text-center" width="13%">Mutation Time</th>
                    <th class="text-start" width="12%">By (User)</th>
                    <th class="text-start" width="10%">From Warehouse</th>
                    <th class="text-start" width="10%">To Warehouse</th>
                    <th class="text-end" width="9%">Mutation Qty</th>
                    <th class="text-end" width="9%">Opening Stock</th>
                    <th class="text-end" width="9%">Closing Stock</th>
                    <th class="text-center" width="11%">Transaction No.</th>
                    <th class="text-start">Description</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($data)) :
                    $no = 0;
                    foreach ($data as $val => $valx) :
                        $no++;
                        $dari = strtoupper((string) $valx['kd_gudang_dari']);
                        $ke   = strtoupper((string) $valx['kd_gudang_ke']);
                        $username = get_name('users', 'nm_lengkap', 'id_user', $valx['update_by']);
                        $qty_trans = (float)$valx['jumlah_mat'];
                ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $no; ?></td>
                            <td class="text-center text-muted font-monospace" style="font-size: 0.85rem;">
                                <?= date('d M Y H:i', strtotime((string) $valx['update_date'])); ?>
                            </td>
                            <td class="fw-semibold text-dark"><?= !empty($username) ? strtoupper((string) $username) : '-'; ?></td>
                            <td><span class="badge bg-light text-dark border"><?= !empty($dari) ? $dari : '-'; ?></span></td>
                            <td><span class="badge bg-light text-dark border"><?= !empty($ke) ? $ke : '-'; ?></span></td>
                            <td class="text-end fw-bold <?= ($qty_trans >= 0) ? 'text-primary' : 'text-danger'; ?>">
                                <?= number_format((float) $qty_trans, 2); ?>
                            </td>
                            <td class="text-end text-muted font-monospace"><?= number_format((float) $valx['qty_stock_awal'], 2); ?></td>
                            <td class="text-end fw-bold text-success font-monospace"><?= number_format((float) $valx['qty_stock_akhir'], 2); ?></td>
                            <td class="text-center text-primary font-monospace fw-semibold" style="font-size: 0.85rem;">
                                <?= !empty($valx['no_ipp']) ? $valx['no_ipp'] : '-'; ?>
                            </td>
                            <td class="text-muted" style="font-size: 0.85rem;"><?= !empty($valx['ket']) ? strtolower((string) $valx['ket']) : '-'; ?></td>
                        </tr>
                    <?php
                    endforeach;
                else :
                    ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fa fa-info-circle me-1"></i> Tidak ada data riwayat mutasi stok untuk barang ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
