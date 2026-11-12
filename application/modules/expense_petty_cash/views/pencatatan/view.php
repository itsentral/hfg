<?php
$header = $pencatatan->header;
$details = isset($pencatatan->details) ? $pencatatan->details : [];
$evidences = isset($pencatatan->evidences) ? $pencatatan->evidences : [];

$budget = isset($budget_info->budget) ? (float)$budget_info->budget : 0;
$budget_terpakai = isset($budget_info->budget_terpakai) ? (float)$budget_info->budget_terpakai : 0;
$sisa_budget = isset($budget_info->sisa_budget) ? (float)$budget_info->sisa_budget : 0;

$coa_map = [];
if (!empty($coa_list)) {
    foreach ($coa_list as $coa) {
        $coa_map[$coa->coa_code] = $coa->coa_code . ' - ' . (isset($coa->coa_nama) ? $coa->coa_nama : '');
    }
}

// Status Badges
function get_status_badge($status)
{
    switch ($status) {
        case 'draft':
            return '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fa fa-pencil me-1"></i>Draft</span>';
        case 'waiting approval':
            return '<span class="badge bg-info text-dark px-3 py-2 rounded-pill"><i class="fa fa-clock-o me-1"></i>Waiting Approval</span>';
        case 'approved':
            return '<span class="badge bg-success px-3 py-2 rounded-pill"><i class="fa fa-check-circle me-1"></i>Approved</span>';
        case 'reject':
            return '<span class="badge bg-danger px-3 py-2 rounded-pill"><i class="fa fa-times-circle me-1"></i>Reject</span>';
        default:
            return '<span class="badge bg-secondary px-3 py-2 rounded-pill">' . htmlspecialchars($status) . '</span>';
    }
}

function get_journal_badge($status)
{
    switch ($status) {
        case 'success':
            return '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill"><i class="fa fa-check me-1"></i>Success</span>';
        case 'failed':
            return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill"><i class="fa fa-exclamation-circle me-1"></i>Failed</span>';
        default:
            return '<span class="badge bg-light text-muted border px-3 py-1 rounded-pill"><i class="fa fa-clock-o me-1"></i>Pending</span>';
    }
}
?>

<!-- KPI Stat Cards (Modern Bootstrap 5) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 5px solid #16a34a !important;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Budget Master</span>
                    <h4 class="fw-bold mb-0 text-success mt-1">Rp <?= number_format($budget, 0, ',', '.') ?></h4>
                    <small class="text-muted" style="font-size: 11px;">Alokasi kas kecil</small>
                </div>
                <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-success" style="width: 48px; height: 48px; font-size: 20px;">
                    <i class="fas fa-money-bill-alt"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #d97706 !important;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Budget Terpakai</span>
                    <h4 class="fw-bold mb-0 text-warning mt-1">Rp <?= number_format($budget_terpakai, 0, ',', '.') ?></h4>
                    <small class="text-muted" style="font-size: 11px;">Realisasi periode berjalan</small>
                </div>
                <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-warning" style="width: 48px; height: 48px; font-size: 20px;">
                    <i class="fa fa-credit-card"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 5px solid #2563eb !important;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Sisa Budget Tersedia</span>
                    <h4 class="fw-bold mb-0 text-primary mt-1">Rp <?= number_format($sisa_budget, 0, ',', '.') ?></h4>
                    <small class="text-muted" style="font-size: 11px;">Sisa anggaran saat pencatatan</small>
                </div>
                <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-primary" style="width: 48px; height: 48px; font-size: 20px;">
                    <i class="fa fa-balance-scale"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header Detail Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-file-text-o me-2"></i>Detail Pencatatan Petty Cash
            </h5>
            <small class="text-muted">Nomor Dokumen: <span class="fw-bold text-dark"><?= htmlspecialchars($header->no_pencatatan) ?></span></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= site_url('expense_petty_cash') ?>" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke List
            </a>
            <?php if ($header->status === 'draft' && has_permission('Expense_Petty_Cash.Manage')) : ?>
                <a href="<?= site_url('expense_petty_cash/edit/' . $header->id) ?>" class="btn btn-warning btn-sm px-3 text-dark">
                    <i class="fa fa-pencil me-1"></i> Edit Data
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Header Info Grid -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold mb-1">No Pencatatan</label>
                <div class="fw-bold fs-6 text-primary"><?= htmlspecialchars($header->no_pencatatan) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold mb-1">Tanggal Transaksi</label>
                <div class="fw-semibold text-dark"><i class="fa fa-calendar me-1 text-muted"></i><?= date('d/m/Y', strtotime($header->tanggal)) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold mb-1">Company</label>
                <div>
                    <?php
                    $comp = $header->company;
                    $bCls = ($comp === 'STM') ? 'bg-primary' : (($comp === 'VUCA') ? 'bg-info text-dark' : 'bg-success');
                    ?>
                    <span class="badge <?= $bCls ?> px-2 py-1"><?= htmlspecialchars($comp) ?></span>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold mb-1">Request By</label>
                <div class="fw-semibold text-dark"><i class="fa fa-user me-1 text-muted"></i><?= htmlspecialchars($header->request_by) ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small fw-semibold mb-1">Keterangan / Keperluan</label>
                <div class="text-dark bg-light p-2 rounded small"><?= !empty($header->keterangan) ? nl2br(htmlspecialchars($header->keterangan)) : '<span class="text-muted">Tidak ada keterangan</span>' ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold mb-1">Status Pencatatan</label>
                <div><?= get_status_badge($header->status) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold mb-1">Status Sinkronisasi Jurnal</label>
                <div><?= get_journal_badge($header->journal_status) ?></div>
            </div>
        </div>

        <hr class="my-4 text-muted opacity-25">

        <!-- Detail Items Table -->
        <h6 class="fw-bold mb-3 text-dark">
            <i class="fa fa-list-ul text-primary me-2"></i>Rincian Item Pengeluaran
        </h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-hover align-middle w-100">
                <thead class="table-light text-center">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th width="240">COA Pengeluaran</th>
                        <th>Deskripsi Pengeluaran</th>
                        <th width="180">Spesifikasi</th>
                        <th width="80" class="text-center">Jumlah</th>
                        <th width="130" class="text-end">Nominal</th>
                        <th width="140" class="text-end">Total</th>
                        <th width="200">Lampiran Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($details)) : ?>
                        <?php $no = 1;
                        foreach ($details as $detail) : ?>
                            <tr>
                                <td class="text-center fw-semibold text-muted"><?= $no++ ?></td>
                                <td><?= isset($coa_map[$detail->coa_code]) ? htmlspecialchars($coa_map[$detail->coa_code]) : htmlspecialchars($detail->coa_code) ?></td>
                                <td class="fw-medium"><?= htmlspecialchars($detail->pengeluaran) ?></td>
                                <td><?= htmlspecialchars($detail->spesifikasi) ?: '<span class="text-muted">-</span>' ?></td>
                                <td class="text-center"><?= $detail->jumlah ?></td>
                                <td class="text-end">Rp <?= number_format($detail->nominal, 0, ',', '.') ?></td>
                                <td class="text-end fw-semibold">Rp <?= number_format($detail->total, 0, ',', '.') ?></td>
                                <td>
                                    <?php
                                    $detail_evidences = isset($evidences[$detail->id]) ? $evidences[$detail->id] : [];
                                    if (!empty($detail_evidences)) : ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($detail_evidences as $evidence) : ?>
                                                <a href="<?= base_url('assets/expense_petty_cash/' . $evidence->encrypted_name) ?>" target="_blank" class="badge bg-light text-dark border text-decoration-none p-1 px-2" title="<?= htmlspecialchars($evidence->original_name) ?>">
                                                    <i class="fa fa-paperclip text-primary me-1"></i><?= htmlspecialchars(strlen($evidence->original_name) > 16 ? substr($evidence->original_name, 0, 16) . '...' : $evidence->original_name) ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="text-muted small">Tidak ada bukti</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Tidak ada data rincian pengeluaran</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="6" class="text-end fw-bold py-2">Grand Total Pengeluaran:</th>
                        <th class="text-end fw-bold text-primary py-2 fs-6">Rp <?= number_format($header->grand_total, 0, ',', '.') ?></th>
                        <th></th>
                    </tr>
                    <tr class="bg-light">
                        <th colspan="6" class="text-end fw-semibold text-muted py-2">Budget Master:</th>
                        <th class="text-end fw-semibold text-muted py-2">Rp <?= number_format($budget, 0, ',', '.') ?></th>
                        <th></th>
                    </tr>
                    <tr class="bg-light">
                        <th colspan="6" class="text-end fw-bold py-2">Sisa Budget Tersedia:</th>
                        <th class="text-end fw-bold text-success py-2">Rp <?= number_format($sisa_budget, 0, ',', '.') ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <hr class="my-4 text-muted opacity-25">

        <!-- Jurnal Section -->
        <?php
        $jurnal_company = $header->company;
        $jurnal_tanggal = date('d/m/Y', strtotime($header->tanggal));
        $jurnal_grand_total = (float)$header->grand_total;
        ?>
        <div class="card border border-light-subtle rounded-3 bg-light shadow-none">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3 text-dark">
                    <i class="fa fa-book text-primary me-2"></i>Ayat Jurnal Pencatatan (Tersimpan)
                </h6>

                <?php if ($jurnal_company === 'STM') : ?>
                    <div class="table-responsive bg-white rounded border">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th width="100" class="text-center">Tanggal</th>
                                    <th width="120">COA</th>
                                    <th>Nama Account</th>
                                    <th width="80" class="text-center">Company</th>
                                    <th width="130" class="text-end">Debit</th>
                                    <th width="130" class="text-end">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($details)) : ?>
                                    <?php foreach ($details as $d) :
                                        $coa_nama = isset($coa_map[$d->coa_code]) ? $coa_map[$d->coa_code] : $d->coa_code;
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= $jurnal_tanggal ?></td>
                                            <td><?= htmlspecialchars($d->coa_code) ?></td>
                                            <td><?= htmlspecialchars($coa_nama) ?></td>
                                            <td class="text-center">STM</td>
                                            <td class="text-end">Rp <?= number_format($d->total, 0, ',', '.') ?></td>
                                            <td class="text-end text-muted">-</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-warning">
                                        <td class="text-center"><?= $jurnal_tanggal ?></td>
                                        <td>1101-01-02</td>
                                        <td>Kas Kecil</td>
                                        <td class="text-center">STM</td>
                                        <td class="text-end text-muted">-</td>
                                        <td class="text-end">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end">Balancing:</td>
                                    <td class="text-end">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                                    <td class="text-end">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                <?php elseif ($jurnal_company === 'VUCA' || $jurnal_company === 'SUSTAIN') : ?>
                    <?php
                    $coa_hutang = ($jurnal_company === 'VUCA') ? '2103-01-01' : '2103-01-02';
                    $coa_piutang = ($jurnal_company === 'VUCA') ? '1103-01-01' : '1103-01-02';
                    ?>
                    <!-- Sisi Company -->
                    <div class="mb-2">
                        <span class="badge bg-warning text-dark px-2 py-1"><i class="fa fa-building me-1"></i> Sisi <?= $jurnal_company ?></span>
                    </div>
                    <div class="table-responsive bg-white rounded border mb-3">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th width="100" class="text-center">Tanggal</th>
                                    <th width="120">COA</th>
                                    <th>Nama Account</th>
                                    <th width="80" class="text-center">Company</th>
                                    <th width="130" class="text-end">Debit</th>
                                    <th width="130" class="text-end">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($details)) : ?>
                                    <?php foreach ($details as $d) :
                                        $coa_nama = isset($coa_map[$d->coa_code]) ? $coa_map[$d->coa_code] : $d->coa_code;
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= $jurnal_tanggal ?></td>
                                            <td><?= htmlspecialchars($d->coa_code) ?></td>
                                            <td><?= htmlspecialchars($coa_nama) ?></td>
                                            <td class="text-center"><?= $jurnal_company ?></td>
                                            <td class="text-end">Rp <?= number_format($d->total, 0, ',', '.') ?></td>
                                            <td class="text-end text-muted">-</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-warning">
                                        <td class="text-center"><?= $jurnal_tanggal ?></td>
                                        <td><?= $coa_hutang ?></td>
                                        <td>Hutang ke STM</td>
                                        <td class="text-center"><?= $jurnal_company ?></td>
                                        <td class="text-end text-muted">-</td>
                                        <td class="text-end">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end">Balancing:</td>
                                    <td class="text-end">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                                    <td class="text-end">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Sisi STM -->
                    <div class="mb-2">
                        <span class="badge bg-info text-dark px-2 py-1"><i class="fa fa-building me-1"></i> Sisi STM (Inter-Company)</span>
                    </div>
                    <div class="table-responsive bg-white rounded border">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th width="100" class="text-center">Tanggal</th>
                                    <th width="120">COA</th>
                                    <th>Nama Account</th>
                                    <th width="80" class="text-center">Company</th>
                                    <th width="130" class="text-end">Debit</th>
                                    <th width="130" class="text-end">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center"><?= $jurnal_tanggal ?></td>
                                    <td><?= $coa_piutang ?></td>
                                    <td>Piutang <?= $jurnal_company ?></td>
                                    <td class="text-center">STM</td>
                                    <td class="text-end">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                                <tr class="table-warning">
                                    <td class="text-center"><?= $jurnal_tanggal ?></td>
                                    <td>1101-01-02</td>
                                    <td>Kas Kecil</td>
                                    <td class="text-center">STM</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end">Balancing:</td>
                                    <td class="text-end">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                                    <td class="text-end">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>