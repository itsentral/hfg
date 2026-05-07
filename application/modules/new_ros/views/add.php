<?php
$ENABLE_ADD    = has_permission('New_ROS.Add');
$ENABLE_MANAGE = has_permission('New_ROS.Manage');

$is_edit   = ($mode == 'edit');
$id_ros    = $is_edit ? $header['id'] : 'New';
$no_po_val = $is_edit ? $header['no_po'] : '';

// Header values
$h = [
    'id_supplier'        => $is_edit ? $header['id_supplier'] : '',
    'nilai_po_usd'       => $is_edit ? $header['nilai_po_usd'] : 0,
    'kurs_pib'           => $is_edit ? $header['kurs_pib'] : 0,
    'nilai_po_pib_rp'    => $is_edit ? $header['nilai_po_pib_rp'] : 0,
    'total_kg_kotor_pib' => $is_edit ? $header['total_kg_kotor_pib'] : 0,
    'total_kg_bersih_pib' => $is_edit ? $header['total_kg_bersih_pib'] : 0,
    'cost_bm'            => $is_edit ? $header['cost_bm'] : 0,
    'cost_bm_kite'       => $is_edit ? $header['cost_bm_kite'] : 0,
    'cost_bmt'           => $is_edit ? $header['cost_bmt'] : 0,
    'cost_cukai'         => $is_edit ? $header['cost_cukai'] : 0,
    'cost_ppn'           => $is_edit ? $header['cost_ppn'] : 0,
    'cost_ppnbm'         => $is_edit ? $header['cost_ppnbm'] : 0,
    'cost_pph_import'    => $is_edit ? $header['cost_pph_import'] : 0,
    'biaya_ls'           => $is_edit ? $header['biaya_ls'] : 0,
    'ppn_ls'             => $is_edit ? $header['ppn_ls'] : 0,
    'pph_ls'             => $is_edit ? $header['pph_ls'] : 0,
    'insurance'          => $is_edit ? $header['insurance'] : 0,
];

$others_data    = $is_edit ? $others : [];
$materials_data = $is_edit ? $materials : [];

// List PO untuk edit mode (sudah di-filter by supplier di controller)
$list_po_data = isset($list_po) ? $list_po : [];
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .section-title {
        background: #f8f9fa;
        padding: 10px 15px;
        border-left: 4px solid #0d6efd;
        margin: 20px 0 15px;
        font-weight: bold;
    }

    .section-title.pib {
        border-left-color: #0d6efd;
    }

    .section-title.ls {
        border-left-color: #198754;
    }

    .section-title.insurance {
        border-left-color: #ffc107;
    }

    .section-title.others {
        border-left-color: #dc3545;
    }

    .section-title.data-po {
        border-left-color: #6f42c1;
    }

    .auto_num {
        text-align: right;
    }

    .table-calc th {
        font-size: 12px;
        white-space: nowrap;
    }

    .table-calc td {
        font-size: 12px;
    }

    .table-calc input {
        font-size: 12px;
    }

    .readonly-field {
        background-color: #e9ecef !important;
    }

    .summary-row td {
        font-weight: bold;
        background-color: #f0f8ff;
    }

    .selisih-row td {
        font-size: 11px;
        color: #6c757d;
    }
</style>

<div class="card">
    <form id="frm-new-ros" method="post">
        <input type="hidden" name="id_ros" id="id_ros" value="<?= $id_ros ?>">
        <input type="hidden" name="no_po" id="no_po" value="<?= $no_po_val ?>">
        <input type="hidden" name="no_surat" id="no_surat" value="<?= $is_edit ? $header['no_surat'] : '' ?>">

        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0 fw-bold">Form New ROS - Kalkulasi Biaya Import Material</h5>
                <span class="text-muted small">(*) wajib diisi</span>
            </div>
            <hr class="mt-2">

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- HEADER: Supplier → PO Selection                        -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>No. ROS</label>
                    <input type="text" class="form-control form-control-sm" value="<?= $id_ros ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label>Supplier <span class="text-danger">*</span></label>
                    <select name="id_supplier" id="id_supplier" class="form-control form-control-sm select2" required style="width:100%">
                        <option value="">-- Pilih Supplier --</option>
                        <?php foreach ($list_supplier as $s) : ?>
                            <option value="<?= $s['kode_supplier'] ?>" <?= ($h['id_supplier'] == $s['kode_supplier']) ? 'selected' : '' ?>><?= $s['nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label>No. PO <span class="text-danger">*</span></label>
                    <select id="select_po" class="form-control form-control-sm select2" required style="width:100%">
                        <option value="">-- Pilih Supplier dulu --</option>
                        <?php if ($is_edit) : ?>
                            <?php foreach ($list_po_data as $po) : ?>
                                <option value="<?= $po['no_po'] ?>" <?= ($no_po_val == $po['no_po']) ? 'selected' : '' ?>><?= $po['no_surat'] ?: $po['no_po'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- TAHAP 1: DATA PIB                                      -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <div class="section-title pib"><i class="fas fa-file-invoice"></i> Tahap 1 — Data PIB</div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Nilai PO (U$) <small class="text-muted">CIF / C&F</small></label>
                    <input type="text" name="nilai_po_usd" id="nilai_po_usd" class="form-control form-control-sm auto_num" value="<?= $h['nilai_po_usd'] ?>">
                </div>
                <div class="col-md-4">
                    <label>Kurs PIB <span class="text-danger">*</span></label>
                    <input type="text" name="kurs_pib" id="kurs_pib" class="form-control form-control-sm auto_num" value="<?= $h['kurs_pib'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Nilai PO PIB (Rp)</label>
                    <input type="text" name="nilai_po_pib_rp" id="nilai_po_pib_rp" class="form-control form-control-sm auto_num readonly-field" value="<?= $h['nilai_po_pib_rp'] ?>">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Total KG Kotor PIB</label>
                    <input type="text" name="total_kg_kotor_pib" id="total_kg_kotor_pib" class="form-control form-control-sm auto_num" value="<?= $h['total_kg_kotor_pib'] ?>">
                </div>
                <div class="col-md-4">
                    <label>Total KG Bersih PIB <span class="text-danger">*</span></label>
                    <input type="text" name="total_kg_bersih_pib" id="total_kg_bersih_pib" class="form-control form-control-sm auto_num" value="<?= $h['total_kg_bersih_pib'] ?>" required>
                </div>
            </div>

            <!-- F&C Estimation -->
            <h6 class="fw-bold mt-3">F&C Estimation</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th class="text-center">Item Pembiayaan</th>
                                <th class="text-center" width="40%">Cost (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">1</td>
                                <td>BM</td>
                                <td><input type="text" name="cost_bm" id="cost_bm" class="form-control form-control-sm auto_num fc-cost" value="<?= $h['cost_bm'] ?>"></td>
                            </tr>
                            <tr>
                                <td class="text-center">2</td>
                                <td>BM Kite</td>
                                <td><input type="text" name="cost_bm_kite" id="cost_bm_kite" class="form-control form-control-sm auto_num fc-cost" value="<?= $h['cost_bm_kite'] ?>"></td>
                            </tr>
                            <tr>
                                <td class="text-center">3</td>
                                <td>BMT</td>
                                <td><input type="text" name="cost_bmt" id="cost_bmt" class="form-control form-control-sm auto_num fc-cost" value="<?= $h['cost_bmt'] ?>"></td>
                            </tr>
                            <tr>
                                <td class="text-center">4</td>
                                <td>Cukai</td>
                                <td><input type="text" name="cost_cukai" id="cost_cukai" class="form-control form-control-sm auto_num fc-cost" value="<?= $h['cost_cukai'] ?>"></td>
                            </tr>
                            <tr>
                                <td class="text-center">5</td>
                                <td>PPN</td>
                                <td><input type="text" name="cost_ppn" id="cost_ppn" class="form-control form-control-sm auto_num fc-cost" value="<?= $h['cost_ppn'] ?>"></td>
                            </tr>
                            <tr>
                                <td class="text-center">6</td>
                                <td>PPnBM</td>
                                <td><input type="text" name="cost_ppnbm" id="cost_ppnbm" class="form-control form-control-sm auto_num fc-cost" value="<?= $h['cost_ppnbm'] ?>"></td>
                            </tr>
                            <tr>
                                <td class="text-center">7</td>
                                <td>PPH Import</td>
                                <td><input type="text" name="cost_pph_import" id="cost_pph_import" class="form-control form-control-sm auto_num fc-cost" value="<?= $h['cost_pph_import'] ?>"></td>
                            </tr>
                            <tr class="table-secondary">
                                <td colspan="2" class="text-center fw-bold">TOTAL</td>
                                <td class="text-end fw-bold" id="total_fc">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- TAHAP 2: BIAYA LS (Surveyor)                           -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <div class="section-title ls"><i class="fas fa-search-dollar"></i> Tahap 2 — Biaya LS (Surveyor)</div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Biaya LS</label>
                    <input type="text" name="biaya_ls" id="biaya_ls" class="form-control form-control-sm auto_num" value="<?= $h['biaya_ls'] ?>">
                </div>
                <div class="col-md-3">
                    <label>PPN LS</label>
                    <input type="text" name="ppn_ls" id="ppn_ls" class="form-control form-control-sm auto_num" value="<?= $h['ppn_ls'] ?>">
                </div>
                <div class="col-md-3">
                    <label>PPH LS</label>
                    <input type="text" name="pph_ls" id="pph_ls" class="form-control form-control-sm auto_num" value="<?= $h['pph_ls'] ?>">
                </div>
            </div>

            <!-- Tabel Prorate LS -->
            <h6 class="fw-bold">Perhitungan Prorate LS</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" id="tbl_prorate_ls">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Nama Material</th>
                            <th class="text-center" width="12%">Net Weight (Kg)</th>
                            <th class="text-center" width="10%">LS (Ya/Tidak)</th>
                            <th class="text-center" width="12%">KG LS</th>
                            <th class="text-center" width="15%">Prorate LS (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="prorate_ls_body">
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary">
                            <td colspan="3" class="text-end fw-bold">Total KG LS</td>
                            <td class="text-end fw-bold" id="total_kg_ls">0</td>
                            <td class="text-end fw-bold" id="total_prorate_ls">0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- TAHAP 3: INSURANCE                                     -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <div class="section-title insurance"><i class="fas fa-shield-alt"></i> Tahap 3 — Insurance (jika ada)</div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Nilai Insurance</label>
                    <input type="text" name="insurance" id="insurance" class="form-control form-control-sm auto_num" value="<?= $h['insurance'] ?>">
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- TAHAP 4: OTHERS COST                                   -->
            <!-- ═══════════════════════════════════════════════════════ -->
            <div class="section-title others"><i class="fas fa-coins"></i> Tahap 4 — Biaya Lain-lain (jika ada)</div>
            <div class="row mb-3">
                <div class="col-md-8">
                    <table class="table table-bordered table-sm" id="tbl_others">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th class="text-center">Keterangan</th>
                                <th class="text-center" width="30%">Nilai (Rp)</th>
                                <th class="text-center" width="8%">Act</th>
                            </tr>
                        </thead>
                        <tbody id="others_body">
                            <?php if (!empty($others_data)) : ?>
                                <?php foreach ($others_data as $idx => $ot) : ?>
                                    <tr>
                                        <td class="text-center others-no"><?= $idx + 1 ?></td>
                                        <td><input type="text" name="others_keterangan[]" class="form-control form-control-sm" value="<?= $ot['keterangan'] ?>"></td>
                                        <td><input type="text" name="others_nilai[]" class="form-control form-control-sm auto_num others-nilai" value="<?= $ot['nilai'] ?>"></td>
                                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove-others"><i class="fa fa-trash"></i></button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td><input type="text" id="new_others_ket" class="form-control form-control-sm" placeholder="Keterangan biaya"></td>
                                <td><input type="text" id="new_others_nilai" class="form-control form-control-sm auto_num" placeholder="0"></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-success" id="btn_add_others"><i class="fa fa-plus"></i></button></td>
                            </tr>
                            <tr class="table-secondary">
                                <td colspan="2" class="text-end fw-bold">Total Biaya Lain</td>
                                <td class="text-end fw-bold" id="total_others">0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- DATA PO & KALKULASI                                        -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div class="card-body">
            <div class="section-title data-po"><i class="fas fa-calculator"></i> Data PO & Kalkulasi Nilai Inventory</div>

            <div class="mb-2">
                <button type="button" class="btn btn-primary btn-sm" id="btn_load_po"><i class="fas fa-sync-alt"></i> Load / Refresh Data PO</button>
                <button type="button" class="btn btn-info btn-sm" id="btn_recalculate"><i class="fas fa-calculator"></i> Hitung Ulang</button>
                <button type="button" class="btn btn-secondary btn-sm" id="btn_download_template"><i class="fas fa-download"></i> Download Template Excel</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm table-calc" id="tbl_data_po">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="3%">No</th>
                            <th class="text-center" style="min-width:150px">Nama di PO</th>
                            <th class="text-center" style="min-width:150px">Nama di ERP</th>
                            <th class="text-center" style="min-width:150px">Nama PO (Alias)</th>
                            <th class="text-center" width="8%">Kg Unit</th>
                            <th class="text-center" width="8%">Unit Price (U$)</th>
                            <th class="text-center" width="8%">Total Value (U$)</th>
                            <th class="text-center" style="min-width:120px">Total Value (Rp)</th>
                            <th class="text-center" width="5%">BM %</th>
                            <th class="text-center" style="min-width:110px">BM (Rp)</th>
                            <th class="text-center" style="min-width:110px">Prorate LS</th>
                            <th class="text-center" style="min-width:110px">Forwarding Cost</th>
                            <th class="text-center" style="min-width:110px">Pro Rate Insurance</th>
                            <th class="text-center" style="min-width:110px">Pro Rate Biaya Lain</th>
                            <th class="text-center" style="min-width:130px">Total Nilai Inventory</th>
                            <th class="text-center" style="min-width:100px">Cost Book</th>
                            <th class="text-center" style="min-width:130px">Kode Internal</th>
                        </tr>
                    </thead>
                    <tbody id="data_po_body">
                    </tbody>
                    <tfoot>
                        <tr class="summary-row">
                            <td colspan="6" class="text-end">Total PO</td>
                            <td class="text-end" id="sum_total_value_usd">0</td>
                            <td class="text-end" id="sum_total_value_rp">0</td>
                            <td></td>
                            <td class="text-end" id="sum_bm_rp">0</td>
                            <td class="text-end" id="sum_prorate_ls">0</td>
                            <td class="text-end" id="sum_forwarding">0</td>
                            <td class="text-end" id="sum_insurance">0</td>
                            <td class="text-end" id="sum_others">0</td>
                            <td class="text-end" id="sum_total_inventory">0</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="summary-row" style="background-color:#e8f5e9;">
                            <td colspan="6" class="text-end">Nilai PIB (Rp)</td>
                            <td class="text-end" id="foot_nilai_pib_usd">-</td>
                            <td class="text-end" id="foot_nilai_pib_rp">0</td>
                            <td></td>
                            <td class="text-end" id="foot_bm_pib">0</td>
                            <td class="text-end" id="foot_ls_pib">0</td>
                            <td></td>
                            <td class="text-end" id="foot_insurance_pib">0</td>
                            <td class="text-end" id="foot_others_pib">0</td>
                            <td colspan="3"></td>
                        </tr>
                        <tr class="selisih-row">
                            <td colspan="6" class="text-end">Selisih</td>
                            <td class="text-end" id="selisih_usd">0</td>
                            <td class="text-end" id="selisih_rp">0</td>
                            <td></td>
                            <td class="text-end" id="selisih_bm">0</td>
                            <td class="text-end" id="selisih_ls">0</td>
                            <td></td>
                            <td class="text-end" id="selisih_insurance">0</td>
                            <td class="text-end" id="selisih_others">0</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- TAHAP 5: UPLOAD PACKING LIST                               -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div class="card-body">
            <div class="section-title" style="border-left-color:#17a2b8;"><i class="fas fa-file-excel"></i> Tahap 5 — Upload Packing List (Data Coil)</div>
            <p class="text-muted small">Upload file Excel packing list untuk merinci coil per material. Format: Coil No | Actual Size | Nama Sesuai PO | Coil Number | N.W. (Kg) | G.W. (Kg) | Length (M)</p>

            <div class="row mb-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="file" id="file_packing_list" class="form-control form-control-sm" accept=".xlsx,.xls">
                        <button type="button" class="btn btn-sm btn-primary" id="btn_upload_pl"><i class="fas fa-upload"></i> Upload</button>
                    </div>
                    <small class="text-muted">Format: .xlsx / .xls, max 10MB</small>
                </div>
                <div class="col-md-3">
                    <span class="badge bg-info" id="coil_count_badge" style="font-size:13px; display:none;">
                        <i class="fas fa-check-circle"></i> <span id="coil_count_text">0</span> coil uploaded
                    </span>
                </div>
            </div>

            <!-- Tabel hasil upload coil -->
            <div class="table-responsive" id="coil_result_wrapper" style="display:none;">
                <table class="table table-bordered table-sm" id="tbl_coils">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="4%">No</th>
                            <th class="text-center">Material (Alias)</th>
                            <th class="text-center">No. Coil</th>
                            <th class="text-center">Kode Internal</th>
                            <th class="text-center">N.W. (Kg)</th>
                            <th class="text-center">G.W. (Kg)</th>
                            <th class="text-center">Length (M)</th>
                        </tr>
                    </thead>
                    <tbody id="coil_result_body"></tbody>
                </table>
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="<?= base_url('new_ros') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="button" class="btn btn-success" id="btn_save"><i class="fas fa-save"></i> Simpan</button>
            <?php if ($is_edit) : ?>
                <!-- <button type="button" class="btn btn-warning" id="btn_print_qr"><i class="fas fa-qrcode"></i> Print QR Code</button> -->
                <button type="button" class="btn btn-info" id="btn_finalize"><i class="fas fa-check-double"></i> Selesai & Kirim ke Incoming</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Modal Print QR -->
<!-- <div class="modal fade" id="modalPrintQR" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Coil — Print QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_body_qr"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-print-qr-action"><i class="fas fa-print"></i> Print Selected</button>
            </div>
        </div>
    </div>
</div> -->

<!-- Modal Review Upload Packing List -->
<div class="modal fade" id="modalReviewUpload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-search"></i> Review Data Packing List</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_body_review" style="max-height:70vh; overflow-y:auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" id="btn_cancel_upload" data-bs-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                <button type="button" class="btn btn-success btn-sm" id="btn_confirm_upload"><i class="fas fa-check"></i> Konfirmasi & Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Select2 CDN (memastikan tersedia sebelum script kita) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- autoNumeric -->
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    // ═══════════════════════════════════════════════════════════════
    // GLOBAL STATE (di luar ready agar bisa diakses semua function)
    // ═══════════════════════════════════════════════════════════════
    var materialsData = <?= json_encode($materials_data) ?>;
    var FORWARDING_RATE = <?= isset($forwarding_rate) ? $forwarding_rate : 0 ?>;

    $(document).ready(function() {

        // ═══════════════════════════════════════════════════════════════
        // INIT Select2 & autoNumeric
        // ═══════════════════════════════════════════════════════════════
        $('#id_supplier').select2({
            placeholder: '-- Pilih Supplier --',
            allowClear: true,
            width: '100%'
        });
        $('#select_po').select2({
            placeholder: '-- Pilih Supplier dulu --',
            allowClear: true,
            width: '100%'
        });
        $('.auto_num').autoNumeric('init');

        // ═══════════════════════════════════════════════════════════════
        // UTILITY: Format & Parse Number
        // ═══════════════════════════════════════════════════════════════
        function formatNum(val, dec) {
            dec = (dec !== undefined) ? dec : 2;
            var n = parseFloat(val) || 0;
            return n.toLocaleString('en-US', {
                minimumFractionDigits: dec,
                maximumFractionDigits: dec
            });
        }

        function getNum(str) {
            if (typeof str === 'number') return str;
            if (!str) return 0;
            return parseFloat(String(str).replace(/,/g, '')) || 0;
        }

        function initAutoNum() {
            $('.auto_num').each(function() {
                if (!$(this).data('autoNumeric')) {
                    $(this).autoNumeric('init');
                }
            });
        }

        // ═══════════════════════════════════════════════════════════════
        // SUPPLIER → PO: Pilih supplier dulu, baru PO muncul
        // ═══════════════════════════════════════════════════════════════
        $('#id_supplier').on('change', function() {
            var id_supplier = $(this).val();
            var $selectPo = $('#select_po');

            // Reset PO dropdown
            $selectPo.empty().append('<option value="">-- Pilih PO --</option>');
            $('#no_po').val('');

            if (!id_supplier) {
                $selectPo.empty().append('<option value="">-- Pilih Supplier dulu --</option>');
                $selectPo.trigger('change');
                return;
            }

            // AJAX: ambil PO berdasarkan supplier
            $.ajax({
                url: siteurl + 'new_ros/get_po_by_supplier',
                type: 'POST',
                data: {
                    id_supplier: id_supplier,
                    exclude_ros: '<?= $is_edit ? $id_ros : '' ?>'
                },
                dataType: 'json',
                success: function(res) {
                    $selectPo.empty().append('<option value="">-- Pilih PO --</option>');
                    if (res.status == 1 && res.data.length > 0) {
                        $.each(res.data, function(i, po) {
                            var label = po.no_surat ? po.no_surat : po.no_po;
                            $selectPo.append('<option value="' + po.no_po + '">' + label + '</option>');
                        });
                    }
                    $selectPo.trigger('change');
                }
            });
        });

        // PO selection → set hidden no_po DAN no_surat
        $('#select_po').on('change', function() {
            var no_po = $(this).val();
            var no_surat = $(this).find('option:selected').text(); // Mengambil label/teks option

            $('#no_po').val(no_po);

            // Jika tidak ada nomor surat (kosong), default ke no_po
            if (no_po === "") {
                $('#no_surat').val("");
            } else {
                // Membersihkan string jika label mengandung format "NoSurat (NoPO)"
                var clean_surat = no_surat.split(' (')[0];
                $('#no_surat').val(clean_surat);
            }
        });

        // ═══════════════════════════════════════════════════════════════
        // TAHAP 1: F&C Total
        // ═══════════════════════════════════════════════════════════════
        function calcFCTotal() {
            var total = 0;
            $('.fc-cost').each(function() {
                var v = $(this).data('autoNumeric') ? getNum($(this).autoNumeric('get')) : getNum($(this).val());
                total += v;
            });
            $('#total_fc').text(formatNum(total, 0));
        }
        $(document).on('keyup blur', '.fc-cost', calcFCTotal);
        calcFCTotal();

        // ═══════════════════════════════════════════════════════════════
        // TAHAP 2: Prorate LS Calculation
        // ═══════════════════════════════════════════════════════════════
        function getAutoVal(selector) {
            var $el = $(selector);
            if ($el.data('autoNumeric')) {
                return parseFloat($el.autoNumeric('get')) || 0;
            }
            return getNum($el.val());
        }

        function calcProrateLS() {
            var biaya_ls = getAutoVal('#biaya_ls');
            var total_kg_ls = 0;

            // First pass: total KG LS
            $('#prorate_ls_body tr').each(function() {
                var ls_flag = $(this).find('.ls-select').val();
                var net_weight = parseFloat($(this).data('kg')) || 0;
                if (ls_flag === 'YA') {
                    total_kg_ls += net_weight;
                }
            });

            // Second pass: prorate
            var total_prorate = 0;
            $('#prorate_ls_body tr').each(function() {
                var ls_flag = $(this).find('.ls-select').val();
                var net_weight = parseFloat($(this).data('kg')) || 0;
                var kg_ls = 0,
                    prorate = 0;

                if (ls_flag === 'YA') {
                    kg_ls = net_weight;
                    if (total_kg_ls > 0) {
                        prorate = biaya_ls * (kg_ls / total_kg_ls);
                    }
                }

                $(this).find('.ls-kg').text(formatNum(kg_ls, 4));
                $(this).find('.ls-prorate').text(formatNum(prorate, 0));
                total_prorate += prorate;
            });

            $('#total_kg_ls').text(formatNum(total_kg_ls, 4));
            $('#total_prorate_ls').text(formatNum(total_prorate, 0));
        }

        $(document).on('change', '.ls-select', function() {
            var idx = $(this).data('idx');
            if (materialsData[idx]) {
                materialsData[idx].ls_flag = $(this).val();
            }
            calcProrateLS();
            recalculate();
        });
        $(document).on('keyup blur', '#biaya_ls', function() {
            calcProrateLS();
            recalculate();
        });

        // ═══════════════════════════════════════════════════════════════
        // TAHAP 4: Others Cost
        // ═══════════════════════════════════════════════════════════════
        function calcOthersTotal() {
            var total = 0;
            $('.others-nilai').each(function() {
                var v = $(this).data('autoNumeric') ? getNum($(this).autoNumeric('get')) : getNum($(this).val());
                total += v;
            });
            $('#total_others').text(formatNum(total, 0));
        }

        function renumberOthers() {
            var no = 1;
            $('#others_body tr').each(function() {
                $(this).find('.others-no').text(no++);
            });
        }

        $(document).on('click', '#btn_add_others', function() {
            var ket = $.trim($('#new_others_ket').val());
            var nilaiRaw = $('#new_others_nilai').data('autoNumeric') ?
                $('#new_others_nilai').autoNumeric('get') :
                $('#new_others_nilai').val();
            var nilai = parseFloat(String(nilaiRaw).replace(/,/g, '')) || 0;

            if (!ket && nilai <= 0) {
                Swal.fire('Perhatian', 'Isi keterangan atau nilai biaya.', 'warning');
                return;
            }

            var no = $('#others_body tr').length + 1;
            var row = '<tr>' +
                '<td class="text-center others-no">' + no + '</td>' +
                '<td><input type="text" name="others_keterangan[]" class="form-control form-control-sm" value="' + ket + '"></td>' +
                '<td><input type="text" name="others_nilai[]" class="form-control form-control-sm auto_num others-nilai" value="' + nilai + '"></td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove-others"><i class="fa fa-trash"></i></button></td>' +
                '</tr>';
            $('#others_body').append(row);

            // Reset input fields
            $('#new_others_ket').val('');
            if ($('#new_others_nilai').data('autoNumeric')) {
                $('#new_others_nilai').autoNumeric('set', '');
            } else {
                $('#new_others_nilai').val('');
            }

            initAutoNum();
            calcOthersTotal();
            recalculate();
        });

        $(document).on('click', '.btn-remove-others', function() {
            $(this).closest('tr').remove();
            renumberOthers();
            calcOthersTotal();
            recalculate();
        });

        $(document).on('keyup blur', '.others-nilai', function() {
            calcOthersTotal();
            recalculate();
        });
        calcOthersTotal();

        // ═══════════════════════════════════════════════════════════════
        // LOAD PO MATERIALS
        // ═══════════════════════════════════════════════════════════════
        $('#btn_load_po').on('click', function() {
            var no_po = $('#no_po').val();
            var kurs = getAutoVal('#kurs_pib');

            if (!no_po) {
                Swal.fire('Perhatian', 'Pilih No. PO terlebih dahulu.', 'warning');
                return;
            }
            if (!kurs) {
                Swal.fire('Perhatian', 'Isi Kurs PIB terlebih dahulu.', 'warning');
                return;
            }

            $.ajax({
                url: siteurl + 'new_ros/get_po_materials',
                type: 'POST',
                data: {
                    no_po: no_po,
                    kurs_pib: kurs
                },
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Loading...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(res) {
                    Swal.close();
                    if (res.status == 1 && res.data.length > 0) {
                        materialsData = [];
                        $.each(res.data, function(i, m) {
                            materialsData.push({
                                id_po_detail: m.id_po_detail,
                                id_barang: m.idmaterial,
                                nm_barang: m.nm_barang,
                                nm_erp: m.nm_erp,
                                nm_alias: m.nm_alias,
                                kg_unit: m.kg_unit,
                                unit_price_usd: m.unit_price_usd,
                                total_value_usd: m.total_value_usd,
                                bm_persen: m.bm_persen,
                                ls_flag: 'YA',
                                coils: []
                            });
                        });
                        renderProrateLS();
                        renderDataPO();
                        calcProrateLS();
                        recalculate();
                    } else {
                        Swal.fire('Info', 'Tidak ada material ditemukan untuk PO ini.', 'info');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Gagal memuat data PO.', 'error');
                }
            });
        });

        // ═══════════════════════════════════════════════════════════════
        // RENDER PRORATE LS TABLE
        // ═══════════════════════════════════════════════════════════════
        function renderProrateLS() {
            var html = '';
            $.each(materialsData, function(i, m) {
                var ls = m.ls_flag || 'YA';
                html += '<tr data-idx="' + i + '" data-kg="' + m.kg_unit + '">';
                html += '<td>' + (m.nm_alias || m.nm_barang) + '</td>';
                html += '<td class="text-end">' + formatNum(m.kg_unit, 4) + '</td>';
                html += '<td class="text-center"><select class="form-control form-control-sm ls-select" data-idx="' + i + '">';
                html += '<option value="YA"' + (ls === 'YA' ? ' selected' : '') + '>YA</option>';
                html += '<option value="TIDAK"' + (ls === 'TIDAK' ? ' selected' : '') + '>TIDAK</option>';
                html += '</select></td>';
                html += '<td class="text-end ls-kg">0</td>';
                html += '<td class="text-end ls-prorate">0</td>';
                html += '</tr>';
            });
            $('#prorate_ls_body').html(html);
        }

        // ═══════════════════════════════════════════════════════════════
        // RENDER DATA PO TABLE (with coil child rows)
        // ═══════════════════════════════════════════════════════════════
        function renderDataPO() {
            var html = '';
            $.each(materialsData, function(i, m) {
                // Material row
                html += '<tr data-idx="' + i + '" class="table-light">';
                html += '<td class="text-center">' + (i + 1) + '</td>';
                html += '<td><input type="hidden" name="mat[' + i + '][id_po_detail]" value="' + m.id_po_detail + '">';
                html += '<input type="hidden" name="mat[' + i + '][id_barang]" value="' + m.id_barang + '">';
                html += '<input type="text" name="mat[' + i + '][nm_barang]" class="form-control form-control-sm" value="' + (m.nm_barang || '') + '"></td>';
                html += '<td><input type="text" name="mat[' + i + '][nm_erp]" class="form-control form-control-sm" value="' + (m.nm_erp || '') + '" readonly></td>';
                html += '<td><input type="text" name="mat[' + i + '][nm_alias]" class="form-control form-control-sm" value="' + (m.nm_alias || '') + '" readonly></td>';
                html += '<td class="text-end mat-kg">' + formatNum(m.kg_unit, 4) + '<input type="hidden" name="mat[' + i + '][kg_unit]" value="' + m.kg_unit + '"></td>';
                html += '<td class="text-end mat-price">' + formatNum(m.unit_price_usd, 6) + '<input type="hidden" name="mat[' + i + '][unit_price_usd]" value="' + m.unit_price_usd + '"></td>';
                html += '<td class="text-end mat-total-usd">' + formatNum(m.total_value_usd, 4) + '<input type="hidden" name="mat[' + i + '][total_value_usd]" value="' + m.total_value_usd + '"></td>';
                html += '<td class="text-end mat-total-rp">0</td>';
                html += '<td class="text-end mat-bm-persen">' + formatNum(m.bm_persen, 0) + '%<input type="hidden" name="mat[' + i + '][bm_persen]" value="' + m.bm_persen + '"></td>';
                html += '<td class="text-end mat-bm-rp">0</td>';
                html += '<td class="text-end mat-prorate-ls">0</td>';
                html += '<td class="text-end mat-forwarding">0</td>';
                html += '<td class="text-end mat-insurance">0</td>';
                html += '<td class="text-end mat-others">0</td>';
                html += '<td class="text-end mat-total-inv fw-bold">0</td>';
                html += '<td class="text-end mat-cost-book fw-bold">0</td>';
                html += '<td class="text-center mat-kode-internal"></td>';
                html += '<input type="hidden" name="mat[' + i + '][ls_flag]" class="mat-ls-flag" value="' + (m.ls_flag || 'YA') + '">';
                html += '</tr>';

                // Coil child rows
                if (m.coils && m.coils.length > 0) {
                    $.each(m.coils, function(j, coil) {
                        html += '<tr class="coil-row" style="background-color:#f9f9f9; font-size:11px;">';
                        html += '<td></td>';
                        html += '<td colspan="3" class="ps-4"><i class="fas fa-level-up-alt fa-rotate-90 text-muted me-1"></i>';
                        html += '<span class="text-primary">' + coil.no_coil + '</span>';
                        html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][no_coil]" value="' + coil.no_coil + '">';
                        html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][berat_bersih]" value="' + coil.berat_bersih + '">';
                        html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][berat_kotor]" value="' + coil.berat_kotor + '">';
                        html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][panjang]" value="' + coil.panjang + '">';
                        html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][kode_internal]" value="' + coil.kode_internal + '">';
                        html += '</td>';
                        html += '<td class="text-end">' + formatNum(coil.berat_bersih, 2) + '</td>'; // NW as kg
                        html += '<td colspan="2"></td>';
                        html += '<td class="text-end">' + formatNum(coil.berat_kotor, 2) + '</td>'; // GW
                        html += '<td colspan="4"></td>';
                        html += '<td class="text-end"><small>' + formatNum(coil.panjang, 2) + ' M</small></td>';
                        html += '<td colspan="2"></td>';
                        html += '<td class="text-center"><small><b>' + coil.kode_internal + '</b></small></td>';
                        html += '</tr>';
                    });
                }
            });
            $('#data_po_body').html(html);
        }

        // ═══════════════════════════════════════════════════════════════
        // RECALCULATE ALL
        // ═══════════════════════════════════════════════════════════════
        function recalculate() {
            var kurs = getAutoVal('#kurs_pib');
            var biaya_ls = getAutoVal('#biaya_ls');
            var insurance = getAutoVal('#insurance');
            var total_kg_bersih = getAutoVal('#total_kg_bersih_pib');

            // Total others
            var total_others = 0;
            $('.others-nilai').each(function() {
                var v = $(this).data('autoNumeric') ? getNum($(this).autoNumeric('get')) : getNum($(this).val());
                total_others += v;
            });

            // Total KG LS
            var total_kg_ls = 0;
            $.each(materialsData, function(i, m) {
                if (m.ls_flag === 'YA') total_kg_ls += parseFloat(m.kg_unit) || 0;
            });

            // Sums
            var sum_total_usd = 0,
                sum_total_rp = 0,
                sum_bm = 0,
                sum_ls = 0;
            var sum_fwd = 0,
                sum_ins = 0,
                sum_oth = 0,
                sum_inv = 0;

            $('#data_po_body tr').each(function() {
                var idx = $(this).data('idx');
                var m = materialsData[idx];
                if (!m) return;

                var kg = parseFloat(m.kg_unit) || 0;
                var total_usd = parseFloat(m.total_value_usd) || 0;
                var total_rp = total_usd * kurs;
                var bm_persen = parseFloat(m.bm_persen) || 0;
                var bm_rp = total_rp * (bm_persen / 100);

                var prorate_ls = 0;
                if (m.ls_flag === 'YA' && total_kg_ls > 0) {
                    prorate_ls = biaya_ls * (kg / total_kg_ls);
                }

                var forwarding = FORWARDING_RATE * kg;

                var pro_ins = 0;
                if (total_kg_bersih > 0) pro_ins = insurance * (kg / total_kg_bersih);

                var pro_oth = 0;
                if (total_kg_bersih > 0) pro_oth = total_others * (kg / total_kg_bersih);

                var total_inv = total_rp + bm_rp + prorate_ls + forwarding + pro_ins + pro_oth;
                var cost_book = (kg > 0) ? total_inv / kg : 0;

                $(this).find('.mat-total-rp').text(formatNum(total_rp, 0));
                $(this).find('.mat-bm-rp').text(formatNum(bm_rp, 0));
                $(this).find('.mat-prorate-ls').text(formatNum(prorate_ls, 0));
                $(this).find('.mat-forwarding').text(formatNum(forwarding, 0));
                $(this).find('.mat-insurance').text(formatNum(pro_ins, 0));
                $(this).find('.mat-others').text(formatNum(pro_oth, 0));
                $(this).find('.mat-total-inv').text(formatNum(total_inv, 0));
                $(this).find('.mat-cost-book').text(formatNum(cost_book, 0));
                $(this).find('.mat-ls-flag').val(m.ls_flag);

                sum_total_usd += total_usd;
                sum_total_rp += total_rp;
                sum_bm += bm_rp;
                sum_ls += prorate_ls;
                sum_fwd += forwarding;
                sum_ins += pro_ins;
                sum_oth += pro_oth;
                sum_inv += total_inv;
            });

            // Footer sums
            $('#sum_total_value_usd').text(formatNum(sum_total_usd, 4));
            $('#sum_total_value_rp').text(formatNum(sum_total_rp, 0));
            $('#sum_bm_rp').text(formatNum(sum_bm, 0));
            $('#sum_prorate_ls').text(formatNum(sum_ls, 0));
            $('#sum_forwarding').text(formatNum(sum_fwd, 0));
            $('#sum_insurance').text(formatNum(sum_ins, 0));
            $('#sum_others').text(formatNum(sum_oth, 0));
            $('#sum_total_inventory').text(formatNum(sum_inv, 0));

            // PIB row
            var nilai_pib_rp = getAutoVal('#nilai_po_pib_rp');
            var nilai_pib_usd = getAutoVal('#nilai_po_usd');
            var bm_pib = getAutoVal('#cost_bm');
            var ls_pib = biaya_ls;
            var ins_pib = insurance;
            var oth_pib = total_others;

            $('#foot_nilai_pib_usd').text(formatNum(nilai_pib_usd, 4));
            $('#foot_nilai_pib_rp').text(formatNum(nilai_pib_rp, 0));
            $('#foot_bm_pib').text(formatNum(bm_pib, 0));
            $('#foot_ls_pib').text(formatNum(ls_pib, 0));
            $('#foot_insurance_pib').text(formatNum(ins_pib, 0));
            $('#foot_others_pib').text(formatNum(oth_pib, 0));

            // Selisih
            $('#selisih_usd').text(formatNum(sum_total_usd - nilai_pib_usd, 4));
            $('#selisih_rp').text(formatNum(sum_total_rp - nilai_pib_rp, 0));
            $('#selisih_bm').text(formatNum(sum_bm - bm_pib, 2));
            $('#selisih_ls').text(formatNum(sum_ls - ls_pib, 0));
            $('#selisih_insurance').text(formatNum(sum_ins - ins_pib, 0));
            $('#selisih_others').text(formatNum(sum_oth - oth_pib, 0));
        }

        $('#btn_recalculate').on('click', function() {
            calcProrateLS();
            recalculate();
            Swal.fire({
                icon: 'success',
                title: 'Selesai',
                text: 'Kalkulasi telah diperbarui.',
                timer: 1500,
                showConfirmButton: false
            });
        });

        // Download Template Excel
        $('#btn_download_template').on('click', function() {
            if (!materialsData || materialsData.length === 0) {
                Swal.fire('Perhatian', 'Load data PO terlebih dahulu.', 'warning');
                return;
            }

            // Kumpulkan nama lain (nm_alias) dari semua material
            var names = [];
            $.each(materialsData, function(i, m) {
                names.push(m.nm_alias || m.nm_barang || '');
            });

            // Submit via hidden form (POST)
            var form = $('<form>', {
                method: 'POST',
                action: siteurl + 'new_ros/download_template'
            });
            form.append($('<input>', {
                type: 'hidden',
                name: 'materials',
                value: JSON.stringify(names)
            }));
            form.appendTo('body').submit().remove();
        });

        // Auto-recalculate on key field changes
        $(document).on('keyup blur', '#kurs_pib, #insurance, #total_kg_bersih_pib', function() {
            calcProrateLS();
            recalculate();
        });

        // ═══════════════════════════════════════════════════════════════
        // SAVE
        // ═══════════════════════════════════════════════════════════════
        $('#btn_save').on('click', function() {
            var supplier = $('#id_supplier').val();
            var no_po = $('#no_po').val();
            var kurs = getAutoVal('#kurs_pib');

            if (!supplier) {
                Swal.fire('Perhatian', 'Pilih Supplier terlebih dahulu.', 'warning');
                return;
            }
            if (!no_po) {
                Swal.fire('Perhatian', 'Pilih No. PO terlebih dahulu.', 'warning');
                return;
            }
            if (!kurs) {
                Swal.fire('Perhatian', 'Isi Kurs PIB terlebih dahulu.', 'warning');
                return;
            }
            if ($('#data_po_body tr').length === 0) {
                Swal.fire('Perhatian', 'Load data PO terlebih dahulu.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Simpan ROS?',
                text: 'Data akan disimpan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Collect autoNumeric values properly
                    var formData = new FormData(document.getElementById('frm-new-ros'));

                    // Override autoNumeric fields with raw values
                    $('.auto_num').each(function() {
                        var name = $(this).attr('name');
                        if (name && $(this).data('autoNumeric')) {
                            formData.set(name, $(this).autoNumeric('get'));
                        }
                    });

                    $.ajax({
                        url: siteurl + 'new_ros/save',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Menyimpan...',
                                allowOutsideClick: false,
                                didOpen: function() {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(res) {
                            Swal.close();
                            if (res.status == 1) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.msg,
                                    confirmButtonText: 'OK'
                                }).then(function() {
                                    window.location.href = baseurl + 'new_ros';
                                });
                            } else {
                                Swal.fire('Gagal', res.msg || 'Terjadi kesalahan.', 'error');
                            }
                        },
                        error: function() {
                            Swal.close();
                            Swal.fire('Error', 'Gagal menyimpan data.', 'error');
                        }
                    });
                }
            });
        });

        // ═══════════════════════════════════════════════════════════════
        // INIT: If edit mode, render existing data
        // ═══════════════════════════════════════════════════════════════
        if (materialsData && materialsData.length > 0) {
            renderProrateLS();
            renderDataPO();
            calcProrateLS();
            recalculate();
        }

        // Load coil data if edit mode
        <?php if ($is_edit) : ?>
            loadCoilData();
        <?php endif; ?>

        // ═══════════════════════════════════════════════════════════════
        // UPLOAD PACKING LIST → Parse → Review Modal → Add to Data PO
        // ═══════════════════════════════════════════════════════════════
        $('#btn_upload_pl').on('click', function() {
            var fileInput = document.getElementById('file_packing_list');
            if (!fileInput.files || fileInput.files.length === 0) {
                Swal.fire('Perhatian', 'Pilih file Excel packing list.', 'warning');
                return;
            }

            if (!materialsData || materialsData.length === 0) {
                Swal.fire('Perhatian', 'Load data PO terlebih dahulu sebelum upload packing list.', 'warning');
                return;
            }

            // Hitung existing coil count
            var existingCoilCount = 0;
            $.each(materialsData, function(i, m) {
                if (m.coils) existingCoilCount += m.coils.length;
            });

            var formData = new FormData();
            formData.append('file_packing_list', fileInput.files[0]);
            formData.append('id_supplier', $('#id_supplier').val());
            formData.append('existing_coil_count', existingCoilCount);

            $.ajax({
                url: siteurl + 'new_ros/parse_packing_list',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Uploading & Parsing...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(res) {
                    Swal.close();
                    if (res.status == 1 && res.coils.length > 0) {
                        showUploadReview(res);
                    } else if (res.status == 1 && res.coils.length === 0) {
                        Swal.fire('Info', 'Tidak ada data coil yang terbaca dari file.', 'info');
                    } else {
                        Swal.fire('Gagal', res.msg, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Gagal upload file.', 'error');
                }
            });
        });

        var parsedCoils = []; // Simpan hasil parse sementara

        function showUploadReview(res) {
            parsedCoils = res.coils;

            // Matching di client-side berdasarkan nama_sesuai_po vs materialsData
            var html = '<p class="mb-2"><small class="text-muted">' + res.msg + '</small></p>';
            html += '<div class="table-responsive"><table class="table table-bordered table-sm" style="font-size:11px;">';
            html += '<thead class="table-light"><tr>';
            html += '<th class="text-center">No</th>';
            html += '<th class="text-center">No. Coil</th>';
            html += '<th class="text-center">Nama Sesuai PO</th>';
            html += '<th class="text-center">Match Material</th>';
            html += '<th class="text-center">Kode Internal</th>';
            html += '<th class="text-center">N.W. (Kg)</th>';
            html += '<th class="text-center">G.W. (Kg)</th>';
            html += '<th class="text-center">Length (M)</th>';
            html += '<th class="text-center">BPM</th>';
            html += '<th class="text-center">Status</th>';
            html += '</tr></thead><tbody>';

            var matchCount = 0;
            $.each(parsedCoils, function(i, coil) {
                // Match di client
                var matched = findMaterialMatch(coil.nama_sesuai_po);
                coil._matched_idx = matched.idx;
                coil._matched_name = matched.name;

                var statusBadge = matched.idx !== null ?
                    '<span class="badge bg-success">Matched</span>' :
                    '<span class="badge bg-danger">Not Match</span>';
                var rowClass = matched.idx !== null ? '' : 'table-warning';
                if (matched.idx !== null) matchCount++;

                html += '<tr class="' + rowClass + '">';
                html += '<td class="text-center">' + (i + 1) + '</td>';
                html += '<td class="text-center">' + coil.no_coil + '</td>';
                html += '<td>' + coil.nama_sesuai_po + '</td>';
                html += '<td>' + (matched.name || '<span class="text-danger">-</span>') + '</td>';
                html += '<td class="text-center">' + coil.kode_internal + '</td>';
                html += '<td class="text-end">' + formatNum(coil.berat_bersih, 2) + '</td>';
                html += '<td class="text-end">' + formatNum(coil.berat_kotor, 2) + '</td>';
                html += '<td class="text-end">' + formatNum(coil.panjang, 2) + '</td>';
                html += '<td class="text-end">' + formatNum(coil.bpm, 2) + '</td>';
                html += '<td class="text-center">' + statusBadge + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            html += '<p class="text-muted small mt-2"><i class="fas fa-info-circle"></i> ' + matchCount + ' matched, ' + (parsedCoils.length - matchCount) + ' not match. Hanya yang <b>Matched</b> akan ditambahkan ke Data PO.</p>';

            $('#modal_body_review').html(html);
            $('#modalReviewUpload').modal('show');
        }

        // Cari material yang cocok berdasarkan nama lain (nm_alias) sebagai key utama
        function findMaterialMatch(namaPo) {
            if (!namaPo) return {
                idx: null,
                name: ''
            };
            var lower = namaPo.toLowerCase().trim();
            for (var i = 0; i < materialsData.length; i++) {
                var m = materialsData[i];
                // Prioritas: nm_alias (nama lain) sebagai key utama
                if (m.nm_alias && m.nm_alias.toLowerCase().trim() === lower) {
                    return {
                        idx: i,
                        name: m.nm_alias
                    };
                }
            }
            // Fallback: cek nm_barang dan nm_erp
            for (var i = 0; i < materialsData.length; i++) {
                var m = materialsData[i];
                if ((m.nm_barang && m.nm_barang.toLowerCase().trim() === lower) ||
                    (m.nm_erp && m.nm_erp.toLowerCase().trim() === lower)) {
                    return {
                        idx: i,
                        name: m.nm_alias || m.nm_barang
                    };
                }
            }
            return {
                idx: null,
                name: ''
            };
        }

        // Konfirmasi upload → tambahkan coil ke materialsData dan render ulang
        $(document).on('click', '#btn_confirm_upload', function() {
            var added = 0;
            $.each(parsedCoils, function(i, coil) {
                if (coil._matched_idx !== null) {
                    var idx = coil._matched_idx;
                    if (!materialsData[idx].coils) materialsData[idx].coils = [];
                    materialsData[idx].coils.push({
                        no_coil: coil.no_coil,
                        berat_bersih: coil.berat_bersih,
                        berat_kotor: coil.berat_kotor,
                        panjang: coil.panjang,
                        kode_internal: coil.kode_internal,
                        bpm: coil.bpm
                    });
                    added++;
                }
            });

            $('#modalReviewUpload').modal('hide');
            parsedCoils = [];

            if (added > 0) {
                renderDataPO(); // Re-render tabel Data PO dengan coil rows
                recalculate();
                Swal.fire('Berhasil', added + ' coil berhasil ditambahkan ke Data PO.', 'success');
                $('#coil_count_badge').show();
                var totalCoils = 0;
                $.each(materialsData, function(i, m) {
                    if (m.coils) totalCoils += m.coils.length;
                });
                $('#coil_count_text').text(totalCoils);
            } else {
                Swal.fire('Info', 'Tidak ada coil yang match.', 'info');
            }
        });

        // Batal upload
        $(document).on('click', '#btn_cancel_upload', function() {
            parsedCoils = [];
            $('#modalReviewUpload').modal('hide');
        });

        function loadCoilData() {
            var id_ros = $('#id_ros').val();
            if (!id_ros || id_ros === 'New') return;

            $.post(siteurl + 'new_ros/get_coils_data', {
                id_ros: id_ros
            }, function(res) {
                var resp = (typeof res === 'string') ? JSON.parse(res) : res;
                if (resp.status == 1 && resp.data.length > 0) {
                    // Assign coils ke materialsData berdasarkan nm_alias match
                    // Reset coils dulu
                    $.each(materialsData, function(i, m) {
                        m.coils = [];
                    });

                    $.each(resp.data, function(i, c) {
                        // Cari material yang cocok
                        var matched = findMaterialMatch(c.nm_alias || c.nm_barang);
                        if (matched.idx !== null) {
                            if (!materialsData[matched.idx].coils) materialsData[matched.idx].coils = [];
                            materialsData[matched.idx].coils.push({
                                no_coil: c.no_coil,
                                berat_bersih: parseFloat(c.berat_bersih) || 0,
                                berat_kotor: parseFloat(c.berat_kotor) || 0,
                                panjang: parseFloat(c.panjang) || 0,
                                kode_internal: c.kode_internal,
                                bpm: 0
                            });
                        }
                    });

                    renderDataPO();
                    recalculate();

                    var totalCoils = 0;
                    $.each(materialsData, function(i, m) {
                        if (m.coils) totalCoils += m.coils.length;
                    });
                    $('#coil_count_badge').show();
                    $('#coil_count_text').text(totalCoils);
                }
            }, 'json');
        }

        // ═══════════════════════════════════════════════════════════════
        // PRINT QR CODE
        // ═══════════════════════════════════════════════════════════════
        // $('#btn_print_qr').on('click', function() {
        //     var id_ros = $('#id_ros').val();
        //     $.ajax({
        //         url: siteurl + 'new_ros/get_coil_list',
        //         type: 'POST',
        //         data: {
        //             id_ros: id_ros
        //         },
        //         success: function(html) {
        //             $('#modal_body_qr').html(html);
        //             $('#modalPrintQR').modal('show');
        //         }
        //     });
        // });

        $(document).on('click', '#check_all_modal', function() {
            $('.check_item_modal').prop('checked', this.checked);
        });

        // $(document).on('click', '#btn-print-qr-action', function() {
        //     var ids = [];
        //     $('.check_item_modal:checked').each(function() {
        //         ids.push($(this).val());
        //     });
        //     if (ids.length === 0) {
        //         Swal.fire('Perhatian', 'Pilih minimal 1 coil untuk print QR.', 'warning');
        //         return;
        //     }
        //     window.open(siteurl + 'new_ros/print_qr/' + ids.join('-'), '_blank');
        // });

        // ═══════════════════════════════════════════════════════════════
        // FINALIZE → Kirim ke Incoming
        // ═══════════════════════════════════════════════════════════════
        $('#btn_finalize').on('click', function() {
            var id_ros = $('#id_ros').val();
            Swal.fire({
                title: 'Finalize ROS?',
                text: 'Setelah finalize, data tidak bisa diubah dan akan masuk ke proses Incoming.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Selesai',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.post(siteurl + 'new_ros/finalize', {
                        id_ros: id_ros
                    }, function(res) {
                        var resp = (typeof res === 'string') ? JSON.parse(res) : res;
                        if (resp.status == 1) {
                            Swal.fire('Berhasil', resp.msg, 'success').then(function() {
                                window.location.href = baseurl + 'new_ros';
                            });
                        } else {
                            Swal.fire('Gagal', resp.msg, 'error');
                        }
                    });
                }
            });
        });

    }); // end $(document).ready
</script>