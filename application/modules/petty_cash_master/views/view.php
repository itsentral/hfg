<input type="hidden" id="id" name="id" value="<?= isset($data->id) ? $data->id : '' ?>">

<!-- Header Fields Card -->
<div class="card border-0 bg-light p-3 mb-4 rounded-3">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="text-muted small fw-semibold text-uppercase">Nama Master Petty Cash</div>
            <div class="fs-6 fw-bold text-dark mt-1">
                <i class="fa fa-money text-primary me-2"></i><?= isset($data->nama) ? htmlspecialchars($data->nama) : '-' ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="text-muted small fw-semibold text-uppercase">Keterangan</div>
            <div class="fs-6 text-secondary mt-1">
                <i class="fa fa-info-circle text-info me-2"></i><?= isset($data->keterangan) ? htmlspecialchars($data->keterangan) : '-' ?>
            </div>
        </div>
    </div>
</div>

<!-- Detail Table Card -->
<div class="card border mb-3">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="fa fa-list text-primary me-2"></i>
            <h6 class="mb-0 fw-bold text-dark">Rincian Alokasi COA & Budget</h6>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
            <?= (isset($details) && is_array($details)) ? count($details) : 0 ?> Alokasi Akun
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="detail-table-view">
            <thead class="table-light">
                <tr>
                    <th width="40" class="text-center">No</th>
                    <th width="320">Akun COA</th>
                    <th>Jenis Pengeluaran</th>
                    <th width="200" class="text-end">Nominal Budget</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($details) && is_array($details) && !empty($details)) : ?>
                    <?php $no = 1;
                    foreach ($details as $detail) : 
                        $coa_label = isset($coa_list[$detail->coa_code]) ? $coa_list[$detail->coa_code] : $detail->coa_code;
                    ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $no++ ?></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="badge bg-secondary-subtle text-dark border px-2 py-1 font-monospace align-self-start mb-1"><?= htmlspecialchars($detail->coa_code) ?></span>
                                    <span class="small fw-semibold text-dark"><?= htmlspecialchars($coa_label) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="text-dark fw-medium"><?= htmlspecialchars($detail->jenis_pengeluaran) ?></span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-dark font-monospace">Rp <?= number_format($detail->nominal, 0, ',', '.') ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="fa fa-info-circle me-1"></i> Tidak ada rincian data detail
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Total Budget Summary Card -->
<div class="card bg-success-subtle border-success-subtle p-3 mb-4 rounded-3 shadow-sm">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2 text-success">
            <i class="fa fa-calculator fs-4"></i>
            <div>
                <div class="fw-bold fs-6">Total Budget Petty Cash</div>
                <small class="text-muted">Total pagu anggaran petty cash</small>
            </div>
        </div>
        <div class="fs-4 fw-bold text-success font-monospace">
            Rp <?= isset($data->total_budget) ? number_format($data->total_budget, 0, ',', '.') : '0' ?>
        </div>
    </div>
</div>

<!-- Footer Button -->
<div class="d-flex justify-content-end pt-2 border-top">
    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" data-dismiss="modal">
        <i class="fa fa-times me-1"></i> Tutup
    </button>
</div>
