<?php
$edit_mode = (isset($mode) && $mode === 'edit');
$header_id = ($edit_mode && isset($pencatatan->header->id)) ? $pencatatan->header->id : '';
$no_pencatatan = ($edit_mode && isset($pencatatan->header->no_pencatatan)) ? $pencatatan->header->no_pencatatan : 'Auto-generated';
$tanggal = ($edit_mode && isset($pencatatan->header->tanggal)) ? $pencatatan->header->tanggal : date('Y-m-d');
$company = ($edit_mode && isset($pencatatan->header->company)) ? $pencatatan->header->company : '';
$request_by = ($edit_mode && isset($pencatatan->header->request_by)) ? $pencatatan->header->request_by : '';
$keterangan = ($edit_mode && isset($pencatatan->header->keterangan)) ? $pencatatan->header->keterangan : '';

$budget = isset($budget_info->budget) ? (float)$budget_info->budget : 0;
$budget_terpakai = isset($budget_info->budget_terpakai) ? (float)$budget_info->budget_terpakai : 0;
$sisa_budget = isset($budget_info->sisa_budget) ? (float)$budget_info->sisa_budget : 0;
?>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Select2 CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

<!-- KPI Stat Cards (Modern Bootstrap 5) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 5px solid #16a34a !important;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Budget Master</span>
                    <h4 class="fw-bold mb-0 text-success mt-1" id="budget-display">Rp <?= number_format($budget, 0, ',', '.') ?></h4>
                    <small class="text-muted" style="font-size: 11px;">Alokasi kas kecil aktif</small>
                </div>
                <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-success" style="width: 48px; height: 48px; font-size: 20px;">
                    <i class="fa fa-money"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #d97706 !important;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Budget Terpakai</span>
                    <h4 class="fw-bold mb-0 text-warning mt-1" id="budget-terpakai-display">Rp <?= number_format($budget_terpakai, 0, ',', '.') ?></h4>
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
                    <h4 class="fw-bold mb-0 text-primary mt-1" id="sisa-budget-display">Rp <?= number_format($sisa_budget, 0, ',', '.') ?></h4>
                    <small class="text-muted" style="font-size: 11px;" id="sisa-budget-status">Sisa saldo aman</small>
                </div>
                <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-primary" style="width: 48px; height: 48px; font-size: 20px;">
                    <i class="fa fa-balance-scale"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Pencatatan Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-pencil-square-o me-2"></i><?= $edit_mode ? 'Edit' : 'Tambah' ?> Pencatatan Petty Cash
            </h5>
            <small class="text-muted">Isi informasi pengeluaran kas kecil beserta bukti transaksinya</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= site_url('expense_petty_cash') ?>" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke List
            </a>
            <button type="button" class="btn btn-primary btn-sm px-4 shadow-sm" id="btn-save">
                <i class="fa fa-save me-1"></i> Simpan Pencatatan
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        <?= form_open_multipart('expense_petty_cash/save', array('id' => 'frm_pencatatan', 'name' => 'frm_pencatatan', 'role' => 'form')) ?>
        <input type="hidden" id="pencatatan_id" name="id" value="<?= $header_id ?>">
        <input type="hidden" id="petty_cash_id" name="petty_cash_id" value="<?= $petty_cash_id ?>">

        <!-- Header Fields -->
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-3">
                <label for="no_pencatatan" class="form-label fw-semibold text-muted small">No Pencatatan</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-barcode"></i></span>
                    <input type="text" class="form-control bg-light fw-bold text-primary border-start-0" id="no_pencatatan" name="no_pencatatan" value="<?= $no_pencatatan ?>" readonly>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="tanggal" class="form-label fw-semibold text-muted small">Tanggal <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-calendar"></i></span>
                    <input type="text" class="form-control flatpickr-date border-start-0" id="tanggal" name="tanggal" value="<?= $tanggal ?>" placeholder="Pilih tanggal..." required>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="company" class="form-label fw-semibold text-muted small">Company <span class="text-danger">*</span></label>
                <select class="form-select" id="company" name="company" required>
                    <option value="">-- Pilih Company --</option>
                    <option value="STM" <?= ($company === 'STM') ? 'selected' : '' ?>>STM</option>
                    <option value="VUCA" <?= ($company === 'VUCA') ? 'selected' : '' ?>>VUCA</option>
                    <option value="SUSTAIN" <?= ($company === 'SUSTAIN') ? 'selected' : '' ?>>SUSTAIN</option>
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="request_by" class="form-label fw-semibold text-muted small">Request By <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control border-start-0" id="request_by" name="request_by" value="<?= $request_by ?>" placeholder="Nama pemohon..." maxlength="100" required>
                </div>
            </div>
            <div class="col-12">
                <label for="keterangan" class="form-label fw-semibold text-muted small">Keterangan / Keperluan</label>
                <textarea class="form-control" id="keterangan" name="keterangan" rows="2" placeholder="Catatan atau keterangan opsional..."><?= $keterangan ?></textarea>
            </div>
        </div>

        <hr class="my-4 text-muted opacity-25">

        <!-- Detail Table Section -->
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa fa-list-ul text-primary me-2"></i>Detail Item Pengeluaran
                </h6>
                <small class="text-muted">Tambahkan rincian item transaksi beserta bukti pendukung</small>
            </div>
            <button type="button" class="btn btn-outline-success btn-sm px-3 shadow-sm" id="btn-add-row">
                <i class="fa fa-plus me-1"></i> Tambah Item
            </button>
        </div>

        <div class="table-responsive mb-3">
            <table id="detail-table" class="table table-bordered table-hover align-middle w-100">
                <thead class="table-light text-center">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th width="240">COA Pengeluaran</th>
                        <th width="180">Deskripsi Pengeluaran</th>
                        <th width="160">Spesifikasi</th>
                        <th width="85" class="text-center">Jumlah</th>
                        <th width="130" class="text-end">Nominal</th>
                        <th width="140" class="text-end">Total</th>
                        <th width="180">Evidence (Bukti)</th>
                        <th width="50" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="detail-table-body">
                    <?php if ($edit_mode && !empty($pencatatan->details)) : ?>
                        <?php foreach ($pencatatan->details as $idx => $detail) : ?>
                            <tr data-row="<?= $idx ?>">
                                <td class="text-center row-number fw-semibold text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <select name="details[<?= $idx ?>][coa_code]" class="form-select form-select-sm coa-select select2" required style="width: 100%;">
                                        <option value="">-- Pilih COA --</option>
                                        <?php foreach ($coa_list as $coa) : ?>
                                            <option value="<?= $coa->coa_code ?>" data-pengeluaran="<?= htmlspecialchars($coa->jenis_pengeluaran) ?>" <?= ($coa->coa_code === $detail->coa_code) ? 'selected' : '' ?>>
                                                <?= $coa->coa_code ?> - <?= $coa->coa_nama ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="details[<?= $idx ?>][pengeluaran]" class="form-control form-control-sm pengeluaran-input" value="<?= htmlspecialchars($detail->pengeluaran) ?>" placeholder="Pengeluaran..." required>
                                </td>
                                <td>
                                    <input type="text" name="details[<?= $idx ?>][spesifikasi]" class="form-control form-control-sm" value="<?= htmlspecialchars($detail->spesifikasi) ?>" placeholder="Spesifikasi...">
                                </td>
                                <td>
                                    <input type="number" name="details[<?= $idx ?>][jumlah]" class="form-control form-control-sm text-center jumlah-input" value="<?= $detail->jumlah ?>" min="1" max="9999" required>
                                </td>
                                <td>
                                    <input type="text" name="details[<?= $idx ?>][nominal]" class="form-control form-control-sm text-end nominal-input" value="<?= number_format($detail->nominal, 0, ',', '.') ?>" placeholder="0" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm text-end total-display bg-light fw-semibold" value="<?= number_format($detail->total, 0, ',', '.') ?>" readonly>
                                    <input type="hidden" name="details[<?= $idx ?>][total]" class="total-hidden" value="<?= $detail->total ?>">
                                </td>
                                <td>
                                    <div class="evidence-container" data-row="<?= $idx ?>">
                                        <div class="evidence-list mb-1">
                                            <?php if (isset($pencatatan->evidences[$detail->id]) && !empty($pencatatan->evidences[$detail->id])) : ?>
                                                <?php foreach ($pencatatan->evidences[$detail->id] as $evKey => $evidence) : ?>
                                                    <div class="evidence-item badge bg-light text-dark border d-inline-flex align-items-center me-1 mb-1 p-1 px-2" data-encrypted="<?= htmlspecialchars($evidence->encrypted_name) ?>">
                                                        <i class="fa fa-file me-1 text-primary"></i>
                                                        <span class="evidence-name text-truncate" style="max-width: 90px;" title="<?= htmlspecialchars($evidence->original_name) ?>"><?= htmlspecialchars($evidence->original_name) ?></span>
                                                        <a href="javascript:void(0)" class="text-danger ms-1 btn-remove-evidence" title="Hapus"><i class="fa fa-times"></i></a>
                                                        <input type="hidden" name="evidences[<?= $idx ?>][<?= $evKey ?>][original_name]" value="<?= htmlspecialchars($evidence->original_name) ?>">
                                                        <input type="hidden" name="evidences[<?= $idx ?>][<?= $evKey ?>][encrypted_name]" value="<?= htmlspecialchars($evidence->encrypted_name) ?>">
                                                        <input type="hidden" name="evidences[<?= $idx ?>][<?= $evKey ?>][file_type]" value="<?= htmlspecialchars($evidence->file_type) ?>">
                                                        <input type="hidden" name="evidences[<?= $idx ?>][<?= $evKey ?>][file_size]" value="<?= htmlspecialchars($evidence->file_size) ?>">
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" class="evidence-file-input" style="display:none;" accept=".png,.jpg,.jpeg,.pdf,.xlsx,.xls" multiple>
                                        <button type="button" class="btn btn-outline-secondary btn-sm btn-trigger-upload py-0 px-2" style="font-size: 11px;">
                                            <i class="fa fa-upload me-1"></i> Upload
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Hapus Baris"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <!-- Default empty row -->
                        <tr data-row="0">
                            <td class="text-center row-number fw-semibold text-muted">1</td>
                            <td>
                                <select name="details[0][coa_code]" class="form-select form-select-sm coa-select select2" required style="width: 100%;">
                                    <option value="">-- Pilih COA --</option>
                                    <?php foreach ($coa_list as $coa) : ?>
                                        <option value="<?= $coa->coa_code ?>" data-pengeluaran="<?= htmlspecialchars($coa->jenis_pengeluaran) ?>">
                                            <?= $coa->coa_code ?> - <?= $coa->coa_nama ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="details[0][pengeluaran]" class="form-control form-control-sm pengeluaran-input" value="" placeholder="Pengeluaran..." required>
                            </td>
                            <td>
                                <input type="text" name="details[0][spesifikasi]" class="form-control form-control-sm" value="" placeholder="Spesifikasi...">
                            </td>
                            <td>
                                <input type="number" name="details[0][jumlah]" class="form-control form-control-sm text-center jumlah-input" value="1" min="1" max="9999" required>
                            </td>
                            <td>
                                <input type="text" name="details[0][nominal]" class="form-control form-control-sm text-end nominal-input" value="" placeholder="0" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end total-display bg-light fw-semibold" value="0" readonly>
                                <input type="hidden" name="details[0][total]" class="total-hidden" value="0">
                            </td>
                            <td>
                                <div class="evidence-container" data-row="0">
                                    <div class="evidence-list mb-1"></div>
                                    <input type="file" class="evidence-file-input" style="display:none;" accept=".png,.jpg,.jpeg,.pdf,.xlsx,.xls" multiple>
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-trigger-upload py-0 px-2" style="font-size: 11px;">
                                        <i class="fa fa-upload me-1"></i> Upload
                                    </button>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Hapus Baris"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="6" class="text-end fw-bold py-2">Grand Total Pengeluaran:</th>
                        <th class="text-end fw-bold text-primary py-2 fs-6" id="grand-total-display">Rp 0</th>
                        <th colspan="2"></th>
                    </tr>
                    <tr class="bg-light">
                        <th colspan="6" class="text-end fw-semibold text-muted py-2">Alokasi Budget:</th>
                        <th class="text-end fw-semibold text-muted py-2" id="budget-table-display">Rp <?= number_format($budget, 0, ',', '.') ?></th>
                        <th colspan="2"></th>
                    </tr>
                    <tr class="bg-light">
                        <th colspan="6" class="text-end fw-bold py-2">Sisa Budget Tersisa:</th>
                        <th class="text-end fw-bold py-2 text-success" id="sisa-budget-table-display">Rp <?= number_format($sisa_budget, 0, ',', '.') ?></th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Warning Callout if Overbudget -->
        <div class="alert alert-warning border-0 shadow-sm rounded-3 d-flex align-items-center mb-4" id="budget-warning-container" style="display: none !important;">
            <div class="me-3 fs-3 text-warning"><i class="fa fa-exclamation-triangle"></i></div>
            <div>
                <h6 class="alert-heading fw-bold mb-1">Peringatan: Melebihi Sisa Budget!</h6>
                <p class="mb-0 small">Total pengeluaran yang diinput melebihi sisa anggaran kas kecil yang tersedia. Anda tetap dapat menyimpan pencatatan ini, namun perhatikan saldo fisik kas kecil.</p>
            </div>
        </div>

        <hr class="my-4 text-muted opacity-25">

        <!-- Live Simulasi Jurnal Preview -->
        <div class="card border border-light-subtle rounded-3 shadow-none bg-light mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-book text-primary me-2"></i>Simulasi Jurnal Transaksi (Live Preview)
                    </h6>
                    <small class="text-muted"><i class="fa fa-info-circle me-1"></i>Pratinjau ayat jurnal otomatis saat disimpan</small>
                </div>

                <!-- Jurnal STM -->
                <div id="jurnal-stm-section">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary px-2 py-1"><i class="fa fa-building me-1"></i> Jurnal Pencatatan STM</span>
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
                            <tbody id="jurnal-stm-body">
                                <tr><td colspan="6" class="text-center text-muted py-2">Belum ada data</td></tr>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end">Balancing:</td>
                                    <td class="text-end" id="jurnal-stm-total-debit">Rp 0</td>
                                    <td class="text-end" id="jurnal-stm-total-kredit">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Jurnal Inter-Company VUCA / SUSTAIN -->
                <div id="jurnal-intercompany-section" style="display: none;">
                    <div class="d-flex align-items-center gap-2 mb-2 mt-3">
                        <span class="badge bg-warning text-dark px-2 py-1"><i class="fa fa-building me-1"></i> Sisi <span class="jurnal-company-name">Company</span></span>
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
                            <tbody id="jurnal-company-body">
                                <tr><td colspan="6" class="text-center text-muted py-2">Belum ada data</td></tr>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end">Balancing:</td>
                                    <td class="text-end" id="jurnal-company-total-debit">Rp 0</td>
                                    <td class="text-end" id="jurnal-company-total-kredit">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2">
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
                            <tbody id="jurnal-stm-side-body">
                                <tr><td colspan="6" class="text-center text-muted py-2">Belum ada data</td></tr>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end">Balancing:</td>
                                    <td class="text-end" id="jurnal-stm-side-total-debit">Rp 0</td>
                                    <td class="text-end" id="jurnal-stm-side-total-kredit">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-2 pt-2">
            <a href="<?= site_url('expense_petty_cash') ?>" class="btn btn-secondary px-3">
                <i class="fa fa-times me-1"></i> Batal
            </a>
            <button type="button" class="btn btn-primary px-4 shadow-sm" id="btn-save-bottom">
                <i class="fa fa-save me-1"></i> Simpan Pencatatan
            </button>
        </div>
        <?= form_close() ?>
    </div>
</div>

<!-- Template COA Options -->
<script type="text/template" id="coa-options-template">
    <option value="">-- Pilih COA --</option>
    <?php foreach ($coa_list as $coa) : ?>
        <option value="<?= $coa->coa_code ?>" data-pengeluaran="<?= htmlspecialchars($coa->jenis_pengeluaran) ?>">
            <?= $coa->coa_code ?> - <?= $coa->coa_nama ?>
        </option>
    <?php endforeach; ?>
</script>

<!-- Select2 JS -->
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script type="text/javascript">
    var FORM_DATA = {
        mode: '<?= $mode ?>',
        petty_cash_id: <?= json_encode($petty_cash_id) ?>,
        budget_info: <?= json_encode($budget_info) ?>,
        coa_list: <?= json_encode($coa_list) ?>,
        base_url: '<?= site_url('expense_petty_cash/') ?>'
    };

    var coaMap = {};
    if (FORM_DATA.coa_list && FORM_DATA.coa_list.length > 0) {
        $.each(FORM_DATA.coa_list, function(i, item) {
            coaMap[item.coa_code] = item;
        });
    }

    var initialBudget = <?= $budget ?>;
    var initialTerpakai = <?= $budget_terpakai ?>;
    var initialSisa = <?= $sisa_budget ?>;

    function formatRupiah(num) {
        return (num || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function cleanNumber(str) {
        if (!str) return 0;
        return parseFloat(str.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
    }

    $(document).ready(function() {
        // Flatpickr initialization
        flatpickr('#tanggal', {
            dateFormat: 'Y-m-d',
            locale: 'id',
            allowInput: true
        });

        // Initialize Select2 on existing COA selects
        $('.coa-select').select2({
            placeholder: '-- Pilih COA --',
            allowClear: true,
            width: '100%'
        });

        // Add Row Handler
        $('#btn-add-row').on('click', function() {
            var rowCount = $('#detail-table-body tr').length;
            var nextIndex = rowCount;
            // Find max index
            $('#detail-table-body tr').each(function() {
                var r = parseInt($(this).data('row'));
                if (!isNaN(r) && r >= nextIndex) nextIndex = r + 1;
            });

            var coaOptions = $('#coa-options-template').html();

            var newRow = '<tr data-row="' + nextIndex + '">';
            newRow += '  <td class="text-center row-number fw-semibold text-muted">' + (rowCount + 1) + '</td>';
            newRow += '  <td>';
            newRow += '    <select name="details[' + nextIndex + '][coa_code]" class="form-select form-select-sm coa-select select2" required style="width: 100%;">';
            newRow += coaOptions;
            newRow += '    </select>';
            newRow += '  </td>';
            newRow += '  <td><input type="text" name="details[' + nextIndex + '][pengeluaran]" class="form-control form-control-sm pengeluaran-input" placeholder="Pengeluaran..." required></td>';
            newRow += '  <td><input type="text" name="details[' + nextIndex + '][spesifikasi]" class="form-control form-control-sm" placeholder="Spesifikasi..."></td>';
            newRow += '  <td><input type="number" name="details[' + nextIndex + '][jumlah]" class="form-control form-control-sm text-center jumlah-input" value="1" min="1" max="9999" required></td>';
            newRow += '  <td><input type="text" name="details[' + nextIndex + '][nominal]" class="form-control form-control-sm text-end nominal-input" placeholder="0" required></td>';
            newRow += '  <td>';
            newRow += '    <input type="text" class="form-control form-control-sm text-end total-display bg-light fw-semibold" value="0" readonly>';
            newRow += '    <input type="hidden" name="details[' + nextIndex + '][total]" class="total-hidden" value="0">';
            newRow += '  </td>';
            newRow += '  <td>';
            newRow += '    <div class="evidence-container" data-row="' + nextIndex + '">';
            newRow += '      <div class="evidence-list mb-1"></div>';
            newRow += '      <input type="file" class="evidence-file-input" style="display:none;" accept=".png,.jpg,.jpeg,.pdf,.xlsx,.xls" multiple>';
            newRow += '      <button type="button" class="btn btn-outline-secondary btn-sm btn-trigger-upload py-0 px-2" style="font-size: 11px;">';
            newRow += '        <i class="fa fa-upload me-1"></i> Upload';
            newRow += '      </button>';
            newRow += '    </div>';
            newRow += '  </td>';
            newRow += '  <td class="text-center">';
            newRow += '    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Hapus Baris"><i class="fa fa-trash"></i></button>';
            newRow += '  </td>';
            newRow += '</tr>';

            $('#detail-table-body').append(newRow);

            var $row = $('#detail-table-body tr').last();
            $row.find('.coa-select').select2({
                placeholder: '-- Pilih COA --',
                allowClear: true,
                width: '100%'
            });

            renumberRows();
            recalcAll();
        });

        // Remove Row
        $('#detail-table').on('click', '.btn-remove-row', function() {
            if ($('#detail-table-body tr').length <= 1) {
                Swal.fire('Info', 'Minimal harus ada 1 baris item pengeluaran.', 'info');
                return;
            }
            $(this).closest('tr').remove();
            renumberRows();
            recalcAll();
        });

        function renumberRows() {
            $('#detail-table-body tr').each(function(i) {
                $(this).find('.row-number').text(i + 1);
            });
        }

        // COA Change -> Auto set Pengeluaran input
        $('#detail-table').on('change', '.coa-select', function() {
            var pengeluaran = $(this).find('option:selected').data('pengeluaran');
            var $row = $(this).closest('tr');
            if (pengeluaran && !$row.find('.pengeluaran-input').val()) {
                $row.find('.pengeluaran-input').val(pengeluaran);
            }
            recalcAll();
        });

        // Realtime calculation on Jumlah & Nominal
        $('#detail-table').on('input keyup change', '.jumlah-input, .nominal-input', function() {
            var $row = $(this).closest('tr');
            var isNominal = $(this).hasClass('nominal-input');
            if (isNominal) {
                var raw = cleanNumber($(this).val());
                if (raw > 0) {
                    var curPos = this.selectionStart;
                    $(this).val(formatRupiah(raw));
                }
            }

            var jumlah = parseInt($row.find('.jumlah-input').val()) || 0;
            var nominal = cleanNumber($row.find('.nominal-input').val());
            var subtotal = jumlah * nominal;

            $row.find('.total-display').val(formatRupiah(subtotal));
            $row.find('.total-hidden').val(subtotal);

            recalcAll();
        });

        // Company Change
        $('#company').on('change', function() {
            updateJournalPreview();
        });

        // Trigger Upload File
        $('#detail-table').on('click', '.btn-trigger-upload', function() {
            $(this).siblings('.evidence-file-input').click();
        });

        // Handle File Selection & AJAX Upload
        $('#detail-table').on('change', '.evidence-file-input', function() {
            var files = this.files;
            if (!files || files.length === 0) return;

            var $container = $(this).closest('.evidence-container');
            var rowIndex = $container.data('row');
            var $list = $container.find('.evidence-list');

            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var formData = new FormData();
                formData.append('evidence_file', file);

                (function(f) {
                    var loadingId = 'loading-' + Math.random().toString(36).substring(7);
                    $list.append('<div id="' + loadingId + '" class="badge bg-light text-muted border me-1 mb-1 p-1"><i class="fa fa-spinner fa-spin me-1"></i> Mengupload...</div>');

                    $.ajax({
                        url: FORM_DATA.base_url + 'upload_evidence',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(resp) {
                            $('#' + loadingId).remove();
                            if (resp.status && resp.data) {
                                var evKey = $list.find('.evidence-item').length;
                                var evHtml = '<div class="evidence-item badge bg-light text-dark border d-inline-flex align-items-center me-1 mb-1 p-1 px-2" data-encrypted="' + resp.data.encrypted_name + '">';
                                evHtml += '  <i class="fa fa-file me-1 text-primary"></i>';
                                evHtml += '  <span class="evidence-name text-truncate" style="max-width: 90px;" title="' + $('<div>').text(resp.data.original_name).html() + '">' + $('<div>').text(resp.data.original_name).html() + '</span>';
                                evHtml += '  <a href="javascript:void(0)" class="text-danger ms-1 btn-remove-evidence" title="Hapus"><i class="fa fa-times"></i></a>';
                                evHtml += '  <input type="hidden" name="evidences[' + rowIndex + '][' + evKey + '][original_name]" value="' + resp.data.original_name + '">';
                                evHtml += '  <input type="hidden" name="evidences[' + rowIndex + '][' + evKey + '][encrypted_name]" value="' + resp.data.encrypted_name + '">';
                                evHtml += '  <input type="hidden" name="evidences[' + rowIndex + '][' + evKey + '][file_type]" value="' + resp.data.file_type + '">';
                                evHtml += '  <input type="hidden" name="evidences[' + rowIndex + '][' + evKey + '][file_size]" value="' + resp.data.file_size + '">';
                                evHtml += '</div>';
                                $list.append(evHtml);
                            } else {
                                Swal.fire('Upload Gagal', resp.message || 'Gagal mengupload file.', 'error');
                            }
                        },
                        error: function() {
                            $('#' + loadingId).remove();
                            Swal.fire('Error', 'Terjadi kesalahan saat upload bukti.', 'error');
                        }
                    });
                })(file);
            }

            $(this).val('');
        });

        // Remove Evidence Item
        $('#detail-table').on('click', '.btn-remove-evidence', function() {
            $(this).closest('.evidence-item').remove();
        });

        function recalcAll() {
            var grandTotal = 0;
            $('#detail-table-body tr').each(function() {
                var total = cleanNumber($(this).find('.total-hidden').val());
                grandTotal += total;
            });

            $('#grand-total-display').text('Rp ' + formatRupiah(grandTotal));

            var sisaBudgetNow = initialSisa - grandTotal;
            $('#sisa-budget-display').text('Rp ' + formatRupiah(sisaBudgetNow));
            $('#sisa-budget-table-display').text('Rp ' + formatRupiah(sisaBudgetNow));

            if (sisaBudgetNow < 0) {
                $('#sisa-budget-display').removeClass('text-primary').addClass('text-danger');
                $('#sisa-budget-table-display').removeClass('text-success').addClass('text-danger');
                $('#sisa-budget-status').text('Budget overlimit!').addClass('text-danger');
                $('#budget-warning-container').show();
            } else {
                $('#sisa-budget-display').removeClass('text-danger').addClass('text-primary');
                $('#sisa-budget-table-display').removeClass('text-danger').addClass('text-success');
                $('#sisa-budget-status').text('Sisa saldo aman').removeClass('text-danger');
                $('#budget-warning-container').hide();
            }

            updateJournalPreview();
        }

        function updateJournalPreview() {
            var comp = $('#company').val();
            var tgl = $('#tanggal').val() || '-';
            var parts = tgl.split('-');
            var tglFmt = (parts.length === 3) ? parts[2] + '/' + parts[1] + '/' + parts[0] : tgl;

            var grandTotal = 0;
            var rows = [];

            $('#detail-table-body tr').each(function() {
                var coaCode = $(this).find('.coa-select').val();
                var subtotal = cleanNumber($(this).find('.total-hidden').val());
                if (coaCode && subtotal > 0) {
                    var coaItem = coaMap[coaCode];
                    var coaNama = coaItem ? coaItem.coa_nama : coaCode;
                    rows.push({
                        coaCode: coaCode,
                        coaNama: coaNama,
                        total: subtotal
                    });
                    grandTotal += subtotal;
                }
            });

            if (comp === 'STM') {
                $('#jurnal-stm-section').show();
                $('#jurnal-intercompany-section').hide();

                var html = '';
                if (rows.length > 0) {
                    $.each(rows, function(i, r) {
                        html += '<tr>';
                        html += '  <td class="text-center">' + tglFmt + '</td>';
                        html += '  <td>' + r.coaCode + '</td>';
                        html += '  <td>' + $('<div>').text(r.coaNama).html() + '</td>';
                        html += '  <td class="text-center">STM</td>';
                        html += '  <td class="text-end">Rp ' + formatRupiah(r.total) + '</td>';
                        html += '  <td class="text-end text-muted">-</td>';
                        html += '</tr>';
                    });
                    html += '<tr class="table-warning">';
                    html += '  <td class="text-center">' + tglFmt + '</td>';
                    html += '  <td>1101-01-02</td>';
                    html += '  <td>Kas Kecil</td>';
                    html += '  <td class="text-center">STM</td>';
                    html += '  <td class="text-end text-muted">-</td>';
                    html += '  <td class="text-end">Rp ' + formatRupiah(grandTotal) + '</td>';
                    html += '</tr>';
                } else {
                    html = '<tr><td colspan="6" class="text-center text-muted py-2">Belum ada item transaksi valid</td></tr>';
                }
                $('#jurnal-stm-body').html(html);
                $('#jurnal-stm-total-debit').text('Rp ' + formatRupiah(grandTotal));
                $('#jurnal-stm-total-kredit').text('Rp ' + formatRupiah(grandTotal));

            } else if (comp === 'VUCA' || comp === 'SUSTAIN') {
                $('#jurnal-stm-section').hide();
                $('#jurnal-intercompany-section').show();
                $('.jurnal-company-name').text(comp);

                var coaHutang = (comp === 'VUCA') ? '2103-01-01' : '2103-01-02';
                var coaPiutang = (comp === 'VUCA') ? '1103-01-01' : '1103-01-02';

                // Sisi Company
                var htmlComp = '';
                if (rows.length > 0) {
                    $.each(rows, function(i, r) {
                        htmlComp += '<tr>';
                        htmlComp += '  <td class="text-center">' + tglFmt + '</td>';
                        htmlComp += '  <td>' + r.coaCode + '</td>';
                        htmlComp += '  <td>' + $('<div>').text(r.coaNama).html() + '</td>';
                        htmlComp += '  <td class="text-center">' + comp + '</td>';
                        htmlComp += '  <td class="text-end">Rp ' + formatRupiah(r.total) + '</td>';
                        htmlComp += '  <td class="text-end text-muted">-</td>';
                        htmlComp += '</tr>';
                    });
                    htmlComp += '<tr class="table-warning">';
                    htmlComp += '  <td class="text-center">' + tglFmt + '</td>';
                    htmlComp += '  <td>' + coaHutang + '</td>';
                    htmlComp += '  <td>Hutang ke STM</td>';
                    htmlComp += '  <td class="text-center">' + comp + '</td>';
                    htmlComp += '  <td class="text-end text-muted">-</td>';
                    htmlComp += '  <td class="text-end">Rp ' + formatRupiah(grandTotal) + '</td>';
                    htmlComp += '</tr>';
                } else {
                    htmlComp = '<tr><td colspan="6" class="text-center text-muted py-2">Belum ada item transaksi valid</td></tr>';
                }
                $('#jurnal-company-body').html(htmlComp);
                $('#jurnal-company-total-debit').text('Rp ' + formatRupiah(grandTotal));
                $('#jurnal-company-total-kredit').text('Rp ' + formatRupiah(grandTotal));

                // Sisi STM
                var htmlStm = '';
                if (rows.length > 0) {
                    htmlStm += '<tr>';
                    htmlStm += '  <td class="text-center">' + tglFmt + '</td>';
                    htmlStm += '  <td>' + coaPiutang + '</td>';
                    htmlStm += '  <td>Piutang ' + comp + '</td>';
                    htmlStm += '  <td class="text-center">STM</td>';
                    htmlStm += '  <td class="text-end">Rp ' + formatRupiah(grandTotal) + '</td>';
                    htmlStm += '  <td class="text-end text-muted">-</td>';
                    htmlStm += '</tr>';
                    htmlStm += '<tr class="table-warning">';
                    htmlStm += '  <td class="text-center">' + tglFmt + '</td>';
                    htmlStm += '  <td>1101-01-02</td>';
                    htmlStm += '  <td>Kas Kecil</td>';
                    htmlStm += '  <td class="text-center">STM</td>';
                    htmlStm += '  <td class="text-end text-muted">-</td>';
                    htmlStm += '  <td class="text-end">Rp ' + formatRupiah(grandTotal) + '</td>';
                    htmlStm += '</tr>';
                } else {
                    htmlStm = '<tr><td colspan="6" class="text-center text-muted py-2">Belum ada item transaksi valid</td></tr>';
                }
                $('#jurnal-stm-side-body').html(htmlStm);
                $('#jurnal-stm-side-total-debit').text('Rp ' + formatRupiah(grandTotal));
                $('#jurnal-stm-side-total-kredit').text('Rp ' + formatRupiah(grandTotal));
            }
        }

        // Save Click
        $('#btn-save, #btn-save-bottom').on('click', function(e) {
            e.preventDefault();

            // Client Validation
            var company = $('#company').val();
            if (!company) {
                Swal.fire('Validasi', 'Company wajib dipilih.', 'warning');
                $('#company').focus();
                return;
            }

            var requestBy = $('#request_by').val();
            if (!requestBy || !$.trim(requestBy)) {
                Swal.fire('Validasi', 'Request By wajib diisi.', 'warning');
                $('#request_by').focus();
                return;
            }

            var rowErrors = [];
            $('#detail-table-body tr').each(function(i) {
                var rowNum = i + 1;
                var coa = $(this).find('.coa-select').val();
                var pengeluaran = $(this).find('.pengeluaran-input').val();
                var jumlah = parseInt($(this).find('.jumlah-input').val()) || 0;
                var nominal = cleanNumber($(this).find('.nominal-input').val());
                var evidenceCount = $(this).find('.evidence-item').length;

                if (!coa) rowErrors.push('Baris ' + rowNum + ': COA belum dipilih.');
                if (!pengeluaran) rowErrors.push('Baris ' + rowNum + ': Deskripsi pengeluaran wajib diisi.');
                if (jumlah <= 0) rowErrors.push('Baris ' + rowNum + ': Jumlah minimal 1.');
                if (nominal <= 0) rowErrors.push('Baris ' + rowNum + ': Nominal harus lebih besar dari 0.');
                if (evidenceCount === 0) rowErrors.push('Baris ' + rowNum + ': Wajib mengupload minimal 1 bukti/evidence.');
            });

            if (rowErrors.length > 0) {
                Swal.fire({
                    title: 'Validasi Belum Lengkap',
                    html: '<div class="text-start small"><ul><li>' + rowErrors.join('</li><li>') + '</li></ul></div>',
                    icon: 'warning'
                });
                return;
            }

            // Confirm & Submit
            Swal.fire({
                title: 'Simpan Pencatatan?',
                text: 'Pastikan data pengeluaran dan lampiran bukti sudah sesuai.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-save me-1"></i> Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Sedang memproses pencatatan kas kecil',
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    var formData = new FormData($('#frm_pencatatan')[0]);

                    $.ajax({
                        url: FORM_DATA.base_url + 'save',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.status) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: resp.message + (resp.warning ? ' (' + resp.warning + ')' : ''),
                                    icon: resp.warning ? 'warning' : 'success'
                                }).then(function() {
                                    window.location.href = FORM_DATA.base_url;
                                });
                            } else {
                                var msg = resp.message || 'Gagal menyimpan data.';
                                if (resp.errors) {
                                    msg += '<br><small>' + Object.values(resp.errors).join('<br>') + '</small>';
                                }
                                Swal.fire('Gagal!', msg, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem saat menyimpan data.', 'error');
                        }
                    });
                }
            });
        });

        // Initial Calculation
        recalcAll();
    });
</script>
