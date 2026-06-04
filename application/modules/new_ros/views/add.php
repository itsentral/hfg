<?php
$ENABLE_ADD    = has_permission('New_ROS.Add');
$ENABLE_MANAGE = has_permission('New_ROS.Manage');

$is_edit   = ($mode == 'edit');
$id_ros    = $is_edit ? $header['id'] : 'New';
$no_po_val = $is_edit ? $header['no_po'] : '';

// Header values
$h = [
    'id_supplier'         => $is_edit ? $header['id_supplier'] : '',
    'nilai_po_usd'        => $is_edit ? $header['nilai_po_usd'] : 0,
    'kurs_pib'            => $is_edit ? $header['kurs_pib'] : 0,
    'nilai_po_pib_rp'     => $is_edit ? $header['nilai_po_pib_rp'] : 0,
    'total_kg_kotor_pib'  => $is_edit ? $header['total_kg_kotor_pib'] : 0,
    'total_kg_bersih_pib' => $is_edit ? $header['total_kg_bersih_pib'] : 0,
    'cost_bm'             => $is_edit ? $header['cost_bm'] : 0,
    'cost_bm_kite'        => $is_edit ? $header['cost_bm_kite'] : 0,
    'cost_bmt'            => $is_edit ? $header['cost_bmt'] : 0,
    'cost_cukai'          => $is_edit ? $header['cost_cukai'] : 0,
    'cost_ppn'            => $is_edit ? $header['cost_ppn'] : 0,
    'cost_ppnbm'          => $is_edit ? $header['cost_ppnbm'] : 0,
    'cost_pph_import'     => $is_edit ? $header['cost_pph_import'] : 0,
    'biaya_ls'            => $is_edit ? $header['biaya_ls'] : 0,
    'ppn_ls'              => $is_edit ? $header['ppn_ls'] : 0,
    'pph_ls'              => $is_edit ? $header['pph_ls'] : 0,
    'insurance'           => $is_edit ? $header['insurance'] : 0,
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

    .section-title.coil-sec {
        border-left-color: #17a2b8;
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

    /* Loading overlay untuk tabel PO */
    #po_loading_overlay {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 10;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }

    #po_section_wrapper {
        position: relative;
    }

    #tbl_coils td,
    #tbl_coils th {
        border: 2px solid #000 !important;
    }
</style>

<div class="card">
    <form id="frm-new-ros" method="post">
        <input type="hidden" name="id_ros" id="id_ros" value="<?= $id_ros ?>">
        <input type="hidden" name="no_po" id="no_po" value="<?= $no_po_val ?>">
        <input type="hidden" name="no_surat" id="no_surat" value="<?= $is_edit ? $header['no_surat'] : '' ?>">

        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0 fw-bold">Form New ROS — Kalkulasi Biaya Import Material</h5>
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
                    <div class="d-flex align-items-center gap-1">
                        <select id="select_po" class="form-control form-control-sm select2" required style="width:100%">
                            <option value="">-- Pilih Supplier dulu --</option>
                            <?php if ($is_edit) : ?>
                                <?php foreach ($list_po_data as $po) : ?>
                                    <option value="<?= $po['no_po'] ?>" <?= ($no_po_val == $po['no_po']) ? 'selected' : '' ?>>
                                        <?= $po['no_surat'] ?: $po['no_po'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div id="po_spinner" class="spinner-border spinner-border-sm text-primary ms-1" style="display:none;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
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
                    <small class="text-muted"><i class="fas fa-info-circle"></i> Isi kurs lalu klik <b>Hitung Ulang</b> untuk update nilai Rp.</small>
                </div>
                <div class="col-md-4">
                    <label>Nilai PO PIB (Rp)</label>
                    <input type="text" name="nilai_po_pib_rp" id="nilai_po_pib_rp"
                        class="form-control form-control-sm auto_num readonly-field"
                        value="<?= $h['nilai_po_pib_rp'] ?>" readonly tabindex="-1">
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
                    <tbody id="prorate_ls_body"></tbody>
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
        <div class="card-body" id="po_section_wrapper">
            <div id="po_loading_overlay" style="display:none; position:absolute; top:0; left:0; right:0; bottom:0;
                 background:rgba(255,255,255,0.85); z-index:10; align-items:center; justify-content:center; border-radius:4px;">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" style="width:2.5rem;height:2.5rem;"></div>
                    <div class="fw-bold text-primary">Memuat data PO...</div>
                </div>
            </div>

            <div class="section-title data-po"><i class="fas fa-calculator"></i> Data PO &amp; Kalkulasi Nilai Inventory</div>

            <div class="mb-2">
                <button type="button" class="btn btn-info btn-sm" id="btn_recalculate"><i class="fas fa-calculator"></i> Hitung Ulang</button>
                <button type="button" class="btn btn-secondary btn-sm" id="btn_download_template"><i class="fas fa-download"></i> Download Template Excel</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm table-calc" id="tbl_data_po">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="3%">No</th>
                            <th class="text-center" style="min-width:150px">Nama di PO</th>
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
                        </tr>
                    </thead>
                    <tbody id="data_po_body">
                        <tr id="tr_empty_po">
                            <td colspan="16" class="text-center text-muted py-3">
                                <i class="fas fa-info-circle me-1"></i>Pilih Supplier dan No. PO untuk memuat data material.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="summary-row">
                            <td colspan="3" class="text-end fw-bold">Total PO</td>
                            <td class="text-end fw-bold" id="sum_kg_unit">0</td>
                            <td></td>
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
                        </tr>
                        <tr class="summary-row" style="background-color:#e8f5e9;">
                            <td colspan="5" class="text-end">Nilai PIB (Rp)</td>
                            <td class="text-end" id="foot_nilai_pib_usd">-</td>
                            <td class="text-end" id="foot_nilai_pib_rp">0</td>
                            <td></td>
                            <td class="text-end" id="foot_bm_pib">0</td>
                            <td class="text-end" id="foot_ls_pib">0</td>
                            <td></td>
                            <td class="text-end" id="foot_insurance_pib">0</td>
                            <td class="text-end" id="foot_others_pib">0</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr class="selisih-row">
                            <td colspan="5" class="text-end">Selisih</td>
                            <td class="text-end" id="selisih_usd">0</td>
                            <td class="text-end" id="selisih_rp">0</td>
                            <td></td>
                            <td class="text-end" id="selisih_bm">0</td>
                            <td class="text-end" id="selisih_ls">0</td>
                            <td></td>
                            <td class="text-end" id="selisih_insurance">0</td>
                            <td class="text-end" id="selisih_others">0</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- TAHAP 5: UPLOAD PACKING LIST                               -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div class="card-body">
            <div class="section-title coil-sec"><i class="fas fa-file-excel"></i> Tahap 5 — Upload Packing List (Data Coil)</div>
            <p class="text-muted small">Upload file Excel packing list untuk merinci coil per material. Format: Coil No | Nama Lain/Alias | Nama Asli | N.W. (Kg) | G.W. (Kg) | Length (M) | BPM</p>

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

            <!-- ── Tabel Coil (terpisah dari Data PO) ── -->
            <div id="coil_section" style="<?= ($is_edit && !empty($materials_data)) ? '' : 'display:none;' ?>">
                <h6 class="fw-bold mb-2"><i class="fas fa-list text-info me-1"></i> Detail Coil per Material</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl_coils">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="4%">No</th>
                                <th class="text-center">Nama Asli</th>
                                <th class="text-center">Nama Alias</th>
                                <th class="text-center">No. Coil</th>
                                <th class="text-center">Kode Internal</th>
                                <th class="text-center">N.W. (Kg)</th>
                                <th class="text-center">G.W. (Kg)</th>
                                <th class="text-center">Length (M)</th>
                                <th class="text-center">BPM</th>
                            </tr>
                        </thead>
                        <tbody id="coil_result_body">
                            <tr id="tr_empty_coil">
                                <td colspan="8" class="text-center text-muted py-2">Belum ada data coil.</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <td colspan="3" class="text-end fw-bold">Total Coil</td>
                                <td class="text-end fw-bold" id="total_coil_count">0</td>
                                <td></td>
                                <td class="text-end fw-bold" id="total_nw">0</td>
                                <td class="text-end fw-bold" id="total_gw">0</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="<?= base_url('new_ros') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="button" class="btn btn-success" id="btn_save"><i class="fas fa-save"></i> Simpan</button>
            <?php if ($is_edit) : ?>
                <!-- <button type="button" class="btn btn-info" id="btn_finalize"><i class="fas fa-check-double"></i> Selesai &amp; Kirim ke Incoming</button> -->
            <?php endif; ?>
        </div>
    </form>
</div>

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
                <button type="button" class="btn btn-success btn-sm" id="btn_confirm_upload"><i class="fas fa-check"></i> Konfirmasi &amp; Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- autoNumeric -->
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    // ═══════════════════════════════════════════════════════════════
    // GLOBAL STATE
    // ═══════════════════════════════════════════════════════════════
    var materialsData = <?= json_encode($materials_data) ?>;
    var FORWARDING_RATE = <?= isset($forwarding_rate) ? $forwarding_rate : 0 ?>;
    var poLoadingActive = false;

    $(document).ready(function() {
        var isInit = true;

        // ── Init Select2 & autoNumeric ──
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

        // ── Utilities ──
        function formatNum(val, dec) {
            dec = (dec !== undefined) ? dec : 2;
            return (parseFloat(val) || 0).toLocaleString('en-US', {
                minimumFractionDigits: dec,
                maximumFractionDigits: dec
            });
        }

        function getNum(str) {
            if (typeof str === 'number') return str;
            return parseFloat(String(str || '').replace(/,/g, '')) || 0;
        }

        function getAutoVal(selector) {
            var $el = $(selector);
            if ($el.data('autoNumeric')) return parseFloat($el.autoNumeric('get')) || 0;
            return getNum($el.val());
        }

        function initAutoNum() {
            $('.auto_num').each(function() {
                if (!$(this).data('autoNumeric')) $(this).autoNumeric('init');
            });
        }

        // ═══════════════════════════════════════════════════════════
        // Nilai PO PIB (Rp) = Nilai PO USD × Kurs PIB (auto)
        // ═══════════════════════════════════════════════════════════
        function calcNilaiPibRp() {
            var usd = getAutoVal('#nilai_po_usd');
            var kurs = getAutoVal('#kurs_pib');
            var hasil = usd * kurs;
            // Set ke field readonly via autoNumeric jika sudah init
            var $field = $('#nilai_po_pib_rp');
            if ($field.data('autoNumeric')) {
                $field.autoNumeric('set', hasil.toFixed(2));
            } else {
                $field.val(formatNum(hasil, 2));
            }
            recalculate();
        }
        $(document).on('keyup blur change', '#nilai_po_usd, #kurs_pib', function() {
            calcNilaiPibRp();
        });

        // ═══════════════════════════════════════════════════════════
        // SUPPLIER → Populate PO dropdown
        // ═══════════════════════════════════════════════════════════
        $('#id_supplier').on('change', function() {
            if (isInit) return;
            var id_supplier = $(this).val();
            var $selectPo = $('#select_po');

            $selectPo.empty().append('<option value="">-- Pilih PO --</option>').trigger('change');
            $('#no_po').val('');
            $('#data_po_body').html('<tr id="tr_empty_po"><td colspan="16" class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>Pilih Supplier dan No. PO untuk memuat data material.</td></tr>');
            materialsData = [];
            renderProrateLS();
            recalculate();

            if (!id_supplier) {
                $selectPo.empty().append('<option value="">-- Pilih Supplier dulu --</option>').trigger('change');
                return;
            }

            $('#po_spinner').show();
            $.ajax({
                url: siteurl + 'new_ros/get_po_by_supplier',
                type: 'POST',
                data: {
                    id_supplier: id_supplier,
                    exclude_ros: '<?= $is_edit ? $id_ros : '' ?>'
                },
                dataType: 'json',
                success: function(res) {
                    $('#po_spinner').hide();
                    $selectPo.empty().append('<option value="">-- Pilih PO --</option>');
                    if (res.status == 1 && res.data.length > 0) {
                        $.each(res.data, function(i, po) {
                            var label = po.no_surat ? po.no_surat : po.no_po;
                            $selectPo.append('<option value="' + po.no_po + '">' + label + '</option>');
                        });
                    }
                    $selectPo.trigger('change');
                },
                error: function() {
                    $('#po_spinner').hide();
                }
            });
        });

        // ── PO selection → set hidden fields + auto-load materials ──
        $('#select_po').on('change', function() {
            if (isInit) return;
            var no_po = $(this).val();
            var no_surat = $(this).find('option:selected').text();

            $('#no_po').val(no_po);
            if (!no_po) {
                $('#no_surat').val('');
                materialsData = [];
                renderProrateLS();
                renderDataPO();
                recalculate();
                return;
            }
            var clean_surat = no_surat.split(' (')[0];
            $('#no_surat').val(clean_surat);

            loadPOMaterials(no_po);
        });

        // ── Load PO Materials (dipanggil otomatis saat PO dipilih) ──
        function loadPOMaterials(no_po) {
            var kurs = getAutoVal('#kurs_pib'); // boleh 0, kalkulasi Rp akan 0 dulu

            // Tampilkan overlay loading
            $('#po_loading_overlay').css('display', 'flex');

            $.ajax({
                url: siteurl + 'new_ros/get_po_materials',
                type: 'POST',
                data: {
                    no_po: no_po,
                    kurs_pib: kurs
                },
                dataType: 'json',
                success: function(res) {
                    $('#po_loading_overlay').hide();
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
                        materialsData = [];
                        renderProrateLS();
                        renderDataPO();
                    }
                },
                error: function() {
                    $('#po_loading_overlay').hide();
                    Swal.fire('Error', 'Gagal memuat data PO.', 'error');
                }
            });
        }

        // Tombol Hitung Ulang tetap tersedia
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

        // ═══════════════════════════════════════════════════════════
        // TAHAP 1: F&C Total
        // ═══════════════════════════════════════════════════════════
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

        // ═══════════════════════════════════════════════════════════
        // TAHAP 2: Prorate LS
        // ═══════════════════════════════════════════════════════════
        function calcProrateLS() {
            var biaya_ls = getAutoVal('#biaya_ls');
            var total_kg_ls = 0;

            $('#prorate_ls_body tr').each(function() {
                var idx = $(this).data('idx');
                if (materialsData[idx] === undefined) return;
                var net_weight = getAutoVal($(this).find('.ls-net-weight'));
                if ($(this).find('.ls-select').val() === 'YA') {
                    total_kg_ls += net_weight;
                }
            });

            var total_prorate = 0;
            $('#prorate_ls_body tr').each(function() {
                var idx = $(this).data('idx');
                if (materialsData[idx] === undefined) return;
                var ls_flag = $(this).find('.ls-select').val();
                var net_weight = getAutoVal($(this).find('.ls-net-weight'));
                var kg_ls = 0,
                    prorate = 0;
                if (ls_flag === 'YA') {
                    kg_ls = net_weight;
                    if (total_kg_ls > 0) prorate = biaya_ls * (kg_ls / total_kg_ls);
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
            if (materialsData[idx]) materialsData[idx].ls_flag = $(this).val();
            calcProrateLS();
            recalculate();
        });
        $(document).on('keyup blur change', '.ls-net-weight', function() {
            var idx = $(this).data('idx');
            var val = getAutoVal($(this));
            if (materialsData[idx]) {
                materialsData[idx].kg_unit = val;
            }
            calcProrateLS();
            recalculate();
        });
        $(document).on('keyup blur', '#biaya_ls', function() {
            calcProrateLS();
            recalculate();
        });

        // ═══════════════════════════════════════════════════════════
        // TAHAP 4: Others
        // ═══════════════════════════════════════════════════════════
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
            var nilai = parseFloat(String($('#new_others_nilai').data('autoNumeric') ? $('#new_others_nilai').autoNumeric('get') : $('#new_others_nilai').val()).replace(/,/g, '')) || 0;
            if (!ket && nilai <= 0) {
                Swal.fire('Perhatian', 'Isi keterangan atau nilai biaya.', 'warning');
                return;
            }
            var no = $('#others_body tr').length + 1;
            $('#others_body').append(
                '<tr><td class="text-center others-no">' + no + '</td>' +
                '<td><input type="text" name="others_keterangan[]" class="form-control form-control-sm" value="' + ket + '"></td>' +
                '<td><input type="text" name="others_nilai[]" class="form-control form-control-sm auto_num others-nilai" value="' + nilai + '"></td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove-others"><i class="fa fa-trash"></i></button></td></tr>'
            );
            $('#new_others_ket').val('');
            $('#new_others_nilai').data('autoNumeric') ? $('#new_others_nilai').autoNumeric('set', '') : $('#new_others_nilai').val('');
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

        // ═══════════════════════════════════════════════════════════
        // RENDER PRORATE LS TABLE
        // ═══════════════════════════════════════════════════════════
        function renderProrateLS() {
            var html = '';
            $.each(materialsData, function(i, m) {
                var ls = m.ls_flag || 'YA';
                html += '<tr data-idx="' + i + '">';
                html += '<td>' + (m.nm_alias || m.nm_barang) + '</td>';
                html += '<td><input type="text" class="form-control form-control-sm text-end auto_num ls-net-weight" data-idx="' + i + '" value="' + m.kg_unit + '"></td>';
                html += '<td class="text-center"><select class="form-control form-control-sm ls-select" data-idx="' + i + '">';
                html += '<option value="YA"' + (ls === 'YA' ? ' selected' : '') + '>YA</option>';
                html += '<option value="TIDAK"' + (ls === 'TIDAK' ? ' selected' : '') + '>TIDAK</option>';
                html += '</select></td>';
                html += '<td class="text-end ls-kg">0</td>';
                html += '<td class="text-end ls-prorate">0</td>';
                html += '</tr>';
            });
            $('#prorate_ls_body').html(html || '<tr><td colspan="5" class="text-center text-muted">Belum ada material.</td></tr>');
            initAutoNum();
        }

        // ═══════════════════════════════════════════════════════════
        // RENDER DATA PO TABLE (tanpa coil rows)
        // ═══════════════════════════════════════════════════════════
        function renderDataPO() {
            if (!materialsData || materialsData.length === 0) {
                $('#data_po_body').html('<tr id="tr_empty_po"><td colspan="16" class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>Pilih Supplier dan No. PO untuk memuat data material.</td></tr>');
                return;
            }
            var html = '';
            $.each(materialsData, function(i, m) {
                html += '<tr data-idx="' + i + '">';
                html += '<td class="text-center">' + (i + 1) + '</td>';
                html += '<td>' +
                    '<input type="hidden" name="mat[' + i + '][id_po_detail]" value="' + m.id_po_detail + '">' +
                    '<input type="hidden" name="mat[' + i + '][id_barang]" value="' + m.id_barang + '">' +
                    '<input type="hidden" name="mat[' + i + '][nm_erp]" value="' + (m.nm_erp || '') + '">' +
                    '<input type="text" name="mat[' + i + '][nm_barang]" class="form-control form-control-sm readonly-field" value="' + (m.nm_barang || '') + '" readonly></td>';
                html += '<td><input type="text" name="mat[' + i + '][nm_alias]" class="form-control form-control-sm readonly-field" value="' + (m.nm_alias || '') + '" readonly></td>';
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
                html += '<input type="hidden" name="mat[' + i + '][ls_flag]" class="mat-ls-flag" value="' + (m.ls_flag || 'YA') + '">';
                html += '</tr>';
            });
            $('#data_po_body').html(html);
        }

        // ═══════════════════════════════════════════════════════════
        // RENDER TABEL COIL (terpisah)
        // ═══════════════════════════════════════════════════════════
        function renderCoilTable() {
            var html = '';
            var no = 1;
            var total = 0;
            var total_nw = 0;
            var total_gw = 0;
            var hasCoil = false;

            $.each(materialsData, function(i, m) {
                if (!m.coils || m.coils.length === 0) return;
                hasCoil = true;

                var rowspan = m.coils.length;
                var nm_asli = m.nm_barang || m.nm_erp || '';
                var nm_alias = m.nm_alias || m.nm_barang || '';

                var total_inv = parseFloat(m.total_nilai_inventory) || 0;
                var jumlah_coil = m.coils.length;
                var price_per_coil = (jumlah_coil > 0) ? total_inv / jumlah_coil : 0;

                $.each(m.coils, function(j, coil) {
                    total_nw += parseFloat(coil.berat_bersih) || 0;
                    total_gw += parseFloat(coil.berat_kotor) || 0;

                    html += '<tr>';
                    if (j === 0) {
                        html += '<td class="text-center align-middle" rowspan="' + rowspan + '">' + no + '</td>';
                        html += '<td class="align-middle" rowspan="' + rowspan + '">' + nm_asli + '</td>';
                        html += '<td class="align-middle" rowspan="' + rowspan + '">' + nm_alias + '</td>';
                    }
                    html += '<td class="text-center">' + coil.no_coil + '</td>';
                    html += '<td class="text-center"><small><b>' + (coil.kode_internal || '') + '</b></small></td>';
                    html += '<td class="text-end">' + formatNum(coil.berat_bersih, 2) + '</td>';
                    html += '<td class="text-end">' + formatNum(coil.berat_kotor, 2) + '</td>';
                    html += '<td class="text-end">' + formatNum(coil.panjang, 2) + '</td>';
                    html += '<td class="text-end">' + formatNum(coil.bpm || 0, 2) + '</td>';

                    html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][no_coil]"       value="' + coil.no_coil + '">';
                    html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][berat_bersih]"  value="' + coil.berat_bersih + '">';
                    html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][berat_kotor]"   value="' + coil.berat_kotor + '">';
                    html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][panjang]"       value="' + coil.panjang + '">';
                    html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][bpm]" value="' + (coil.bpm || 0) + '">';
                    html += '<input type="hidden" name="mat[' + i + '][coil][' + j + '][kode_internal]" value="' + (coil.kode_internal || '') + '">';

                    html += '</tr>';
                    total++;
                });

                no++;
            });

            if (!hasCoil) {
                $('#coil_result_body').html('<tr id="tr_empty_coil"><td colspan="8" class="text-center text-muted py-2">Belum ada data coil.</td></tr>');
                $('#total_coil_count').text('0');
                $('#total_nw').text('0');
                $('#total_gw').text('0');
                return;
            }

            $('#coil_result_body').html(html);
            $('#total_coil_count').text(total);
            $('#total_nw').text(formatNum(total_nw, 2));
            $('#total_gw').text(formatNum(total_gw, 2));
            $('#coil_section').show();
            $('#coil_count_badge').show();
            $('#coil_count_text').text(total);
        }

        // ═══════════════════════════════════════════════════════════
        // RECALCULATE ALL
        // ═══════════════════════════════════════════════════════════
        function recalculate() {
            var kurs = getAutoVal('#kurs_pib');
            var biaya_ls = getAutoVal('#biaya_ls');
            var insurance = getAutoVal('#insurance');
            var total_kg_bersih = getAutoVal('#total_kg_bersih_pib');

            var total_others = 0;
            $('.others-nilai').each(function() {
                var v = $(this).data('autoNumeric') ? getNum($(this).autoNumeric('get')) : getNum($(this).val());
                total_others += v;
            });

            var total_kg_ls = 0;
            $.each(materialsData, function(i, m) {
                if (m.ls_flag === 'YA') total_kg_ls += parseFloat(m.kg_unit) || 0;
            });

            var sum_total_usd = 0,
                sum_total_rp = 0,
                sum_bm = 0,
                sum_ls = 0;
            var sum_fwd = 0,
                sum_ins = 0,
                sum_oth = 0,
                sum_inv = 0;
            var sum_kg_unit = 0;

            $('#data_po_body tr').each(function() {
                var idx = $(this).data('idx');
                var m = materialsData[idx];
                if (m === undefined || m === null) return;

                var kg = parseFloat(m.kg_unit) || 0;
                var unit_price = parseFloat(m.unit_price_usd) || 0;
                var total_usd = kg * unit_price;

                var total_rp = total_usd * kurs;
                var bm_persen = parseFloat(m.bm_persen) || 0;
                var bm_rp = total_rp * (bm_persen / 100);

                // Prorate LS
                var prorate_ls = 0;
                if (m.ls_flag === 'YA' && total_kg_ls > 0) {
                    prorate_ls = biaya_ls * (kg / total_kg_ls);
                }

                // Forwarding
                var forwarding = FORWARDING_RATE * kg;

                // Prorate Insurance & Others (basis: total_kg_bersih)
                var pro_ins = (total_kg_bersih > 0) ? insurance * (kg / total_kg_bersih) : 0;
                var pro_oth = (total_kg_bersih > 0) ? total_others * (kg / total_kg_bersih) : 0;

                // Total Inventory & Cost Book
                var total_inv = total_rp + bm_rp + prorate_ls + forwarding + pro_ins + pro_oth;
                var cost_book = (kg > 0) ? total_inv / kg : 0;

                // ── Update DOM ──
                $(this).find('.mat-kg').html(
                    formatNum(kg, 4) +
                    '<input type="hidden" name="mat[' + idx + '][kg_unit]" value="' + kg + '">'
                );
                $(this).find('.mat-total-usd').html(
                    formatNum(total_usd, 4) +
                    '<input type="hidden" name="mat[' + idx + '][total_value_usd]" value="' + total_usd + '">'
                );
                $(this).find('.mat-total-rp').text(formatNum(total_rp, 0));
                $(this).find('.mat-bm-rp').text(formatNum(bm_rp, 0));
                $(this).find('.mat-prorate-ls').text(formatNum(prorate_ls, 0));
                $(this).find('.mat-forwarding').text(formatNum(forwarding, 0));
                $(this).find('.mat-insurance').text(formatNum(pro_ins, 0));
                $(this).find('.mat-others').text(formatNum(pro_oth, 0));
                $(this).find('.mat-total-inv').text(formatNum(total_inv, 0));
                $(this).find('.mat-cost-book').text(formatNum(cost_book, 0));
                $(this).find('.mat-ls-flag').val(m.ls_flag);

                // ── Akumulasi footer ──
                sum_kg_unit += kg;
                sum_total_usd += total_usd;
                sum_total_rp += total_rp;
                sum_bm += bm_rp;
                sum_ls += prorate_ls;
                sum_fwd += forwarding;
                sum_ins += pro_ins;
                sum_oth += pro_oth;
                sum_inv += total_inv;
            });

            // ── Footer summary row ──
            $('#sum_kg_unit').text(formatNum(sum_kg_unit, 4));
            $('#sum_total_value_usd').text(formatNum(sum_total_usd, 4));
            $('#sum_total_value_rp').text(formatNum(sum_total_rp, 0));
            $('#sum_bm_rp').text(formatNum(sum_bm, 0));
            $('#sum_prorate_ls').text(formatNum(sum_ls, 0));
            $('#sum_forwarding').text(formatNum(sum_fwd, 0));
            $('#sum_insurance').text(formatNum(sum_ins, 0));
            $('#sum_others').text(formatNum(sum_oth, 0));
            $('#sum_total_inventory').text(formatNum(sum_inv, 0));

            // ── Baris Nilai PIB ──
            var nilai_pib_rp = getAutoVal('#nilai_po_pib_rp');
            var nilai_pib_usd = getAutoVal('#nilai_po_usd');
            var bm_pib = getAutoVal('#cost_bm');

            $('#foot_nilai_pib_usd').text(formatNum(nilai_pib_usd, 4));
            $('#foot_nilai_pib_rp').text(formatNum(nilai_pib_rp, 0));
            $('#foot_bm_pib').text(formatNum(bm_pib, 0));
            $('#foot_ls_pib').text(formatNum(biaya_ls, 0));
            $('#foot_insurance_pib').text(formatNum(insurance, 0));
            $('#foot_others_pib').text(formatNum(total_others, 0));

            // ── Baris Selisih ──
            $('#selisih_usd').text(formatNum(sum_total_usd - nilai_pib_usd, 4));
            $('#selisih_rp').text(formatNum(sum_total_rp - nilai_pib_rp, 0));
            $('#selisih_bm').text(formatNum(sum_bm - bm_pib, 2));
            $('#selisih_ls').text(formatNum(sum_ls - biaya_ls, 0));
            $('#selisih_insurance').text(formatNum(sum_ins - insurance, 0));
            $('#selisih_others').text(formatNum(sum_oth - total_others, 0));
        }

        $(document).on('keyup blur', '#insurance, #total_kg_bersih_pib', function() {
            calcProrateLS();
            recalculate();
        });

        // ═══════════════════════════════════════════════════════════
        // DOWNLOAD TEMPLATE EXCEL (dengan dialog pilih jumlah coil)
        // ═══════════════════════════════════════════════════════════
        $('#btn_download_template').on('click', function() {
            if (!materialsData || materialsData.length === 0) {
                Swal.fire('Perhatian', 'Pilih PO terlebih dahulu untuk memuat data material.', 'warning');
                return;
            }

            // Bangun form dialog untuk input jumlah coil per material
            var inputsHtml = '<div style="text-align:left;">';
            inputsHtml += '<p class="mb-2 text-muted small">Tentukan jumlah baris coil yang akan disiapkan per material di template Excel.</p>';
            inputsHtml += '<table class="table table-sm table-bordered" style="font-size:13px;">';
            inputsHtml += '<thead class="table-light"><tr><th>Nama Alias</th><th>Nama Barang</th><th class="text-center" width="100px">Jumlah Coil</th></tr></thead><tbody>';
            $.each(materialsData, function(i, m) {
                var nm_alias = m.nm_alias || '-';
                var nm_barang = m.nm_barang || m.nm_erp || '-';
                inputsHtml += '<tr>' +
                    '<td>' + nm_alias + '</td>' +
                    '<td>' + nm_barang + '</td>' +
                    '<td><input type="number" id="coil_count_' + i + '" class="form-control form-control-sm text-center" value="1" min="1" max="500" style="width:80px;margin:auto;"></td>' +
                    '</tr>';
            });
            inputsHtml += '</tbody></table></div>';

            Swal.fire({
                title: '<i class="fas fa-file-excel text-success me-1"></i> Jumlah Coil per Material',
                html: inputsHtml,
                width: '600px',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-download"></i> Download',
                cancelButtonText: 'Batal',
                preConfirm: function() {
                    var coilCounts = [];
                    $.each(materialsData, function(i, m) {
                        var val = parseInt($('#coil_count_' + i).val()) || 1;
                        if (val < 1) val = 1;
                        coilCounts.push({
                            nm_alias: m.nm_alias || m.nm_barang || '',
                            nm_barang: m.nm_barang || m.nm_alias || '',
                            count: val
                        });
                    });
                    return coilCounts;
                }
            }).then(function(result) {
                if (result.isConfirmed && result.value) {
                    // Submit via hidden form POST
                    var form = $('<form>', {
                        method: 'POST',
                        action: siteurl + 'new_ros/download_template'
                    });
                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'materials_coil',
                        value: JSON.stringify(result.value)
                    }));
                    form.appendTo('body').submit().remove();
                }
            });
        });

        // ═══════════════════════════════════════════════════════════
        // UPLOAD PACKING LIST → Parse → Review Modal
        // ═══════════════════════════════════════════════════════════
        $('#btn_upload_pl').on('click', function() {
            var fileInput = document.getElementById('file_packing_list');
            if (!fileInput.files || fileInput.files.length === 0) {
                Swal.fire('Perhatian', 'Pilih file Excel packing list.', 'warning');
                return;
            }
            if (!materialsData || materialsData.length === 0) {
                Swal.fire('Perhatian', 'Pilih PO terlebih dahulu sebelum upload packing list.', 'warning');
                return;
            }

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
                    } else if (res.status == 1) {
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

        var parsedCoils = [];

        function showUploadReview(res) {
            parsedCoils = res.coils;
            var totalExcelNW = 0;
            var matchCount = 0;

            $.each(parsedCoils, function(i, coil) {
                var matched = findMaterialMatch(coil.nama_alias);
                coil._matched_idx = matched.idx;
                coil._matched_name = matched.name;

                if (matched.idx === null && coil.nm_barang) {
                    matched = findMaterialMatch(coil.nm_barang);
                    coil._matched_idx = matched.idx;
                    coil._matched_name = matched.name;
                }

                if (matched.idx !== null) matchCount++;
                totalExcelNW += parseFloat(coil.berat_bersih) || 0;
            });

            var totalPoKg = 0;
            $.each(materialsData, function(i, m) {
                totalPoKg += parseFloat(m.kg_unit) || 0;
            });

            var isMatched = Math.abs(totalExcelNW - totalPoKg) < 0.01;
            var alertClass = isMatched ? 'alert-success' : 'alert-danger';
            var matchStatusText = isMatched ?
                '<span class="badge bg-success" style="font-size: 13px;"><i class="fas fa-check-circle"></i> Cocok (Match)</span>' :
                '<span class="badge bg-danger" style="font-size: 13px;"><i class="fas fa-times-circle"></i> Tidak Cocok</span>';

            var summaryHtml = '<div class="alert ' + alertClass + ' p-3 mb-3 d-flex justify-content-between align-items-center" style="font-size:14px; border-radius:6px;">' +
                '<div>' +
                '<strong>Total Net Weight Excel:</strong> <span class="fw-bold">' + formatNum(totalExcelNW, 2) + ' Kg</span>' +
                '&nbsp;&nbsp;&nbsp;&nbsp;' +
                '<strong>Total Kg Unit PO:</strong> <span class="fw-bold">' + formatNum(totalPoKg, 4) + ' Kg</span>' +
                '</div>' +
                '<div>' + matchStatusText + '</div>' +
                '</div>';

            if (!isMatched) {
                summaryHtml += '<div class="alert alert-warning p-2 mb-3" style="font-size:12px;"><i class="fas fa-exclamation-triangle"></i> Peringatan: Total Net Weight Excel harus sama dengan Total Kg Unit PO agar dapat disimpan. Silakan sesuaikan kolom Net Weight di tabel Prorate LS atau periksa kembali file Excel Anda.</div>';
            }

            var html = summaryHtml;
            html += '<p class="mb-2"><small class="text-muted">' + res.msg + '</small></p>';
            html += '<div class="table-responsive"><table class="table table-bordered table-sm" style="font-size:11px;">';
            html += '<thead class="table-light"><tr>' +
                '<th>No</th><th>No. Coil</th><th>Nama Alias</th><th>Nama Asli</th>' +
                '<th>Match Material</th><th>Kode Internal</th>' +
                '<th>N.W.</th><th>G.W.</th><th>Length</th><th>Status</th>' +
                '</tr></thead><tbody>';

            $.each(parsedCoils, function(i, coil) {
                var matchedName = coil._matched_name || '';
                var statusBadge = coil._matched_idx !== null ?
                    '<span class="badge bg-success">Matched</span>' :
                    '<span class="badge bg-danger">Not Match</span>';

                html += '<tr class="' + (coil._matched_idx !== null ? '' : 'table-warning') + '">' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td>' + coil.no_coil + '</td>' +
                    '<td>' + coil.nama_alias + '</td>' +
                    '<td>' + (coil.nm_barang || '-') + '</td>' +
                    '<td>' + (matchedName || '<span class="text-danger">-</span>') + '</td>' +
                    '<td><small>' + coil.kode_internal + '</small></td>' +
                    '<td class="text-end">' + formatNum(coil.berat_bersih, 2) + '</td>' +
                    '<td class="text-end">' + formatNum(coil.berat_kotor, 2) + '</td>' +
                    '<td class="text-end">' + formatNum(coil.panjang, 2) + '</td>' +
                    '<td class="text-center">' + statusBadge + '</td>' +
                    '</tr>';
            });

            html += '</tbody>';
            html += '<tfoot><tr class="table-secondary">';
            html += '<td colspan="6" class="text-end fw-bold">Total Net Weight Excel</td>';
            html += '<td class="text-end fw-bold">' + formatNum(totalExcelNW, 2) + '</td>';
            html += '<td colspan="3"></td>';
            html += '</tr></tfoot>';
            html += '</table></div>';

            html += '<p class="text-muted small mt-2"><i class="fas fa-info-circle"></i> ' +
                matchCount + ' matched, ' + (parsedCoils.length - matchCount) + ' not match.</p>';

            $('#modal_body_review').html(html);
            $('#btn_confirm_upload').prop('disabled', !isMatched);
            $('#modalReviewUpload').modal('show');
        }

        function findMaterialMatch(namaPo) {
            if (!namaPo) return {
                idx: null,
                name: ''
            };
            var lower = namaPo.toLowerCase().trim();
            for (var i = 0; i < materialsData.length; i++) {
                var m = materialsData[i];
                if (m.nm_alias && m.nm_alias.toLowerCase().trim() === lower) return {
                    idx: i,
                    name: m.nm_alias
                };
            }
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

        $(document).on('click', '#btn_confirm_upload', function() {
            var added = 0;
            var affectedIdx = {};
            $.each(parsedCoils, function(i, coil) {
                if (coil._matched_idx !== null) {
                    affectedIdx[coil._matched_idx] = true;
                }
            });

            $.each(affectedIdx, function(idx) {
                materialsData[idx].coils = [];
            });

            // Isi ulang dengan coil dari file upload
            $.each(parsedCoils, function(i, coil) {
                if (coil._matched_idx !== null) {
                    var idx = coil._matched_idx;
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
                renderCoilTable();
                recalculate();
                Swal.fire('Berhasil', added + ' coil berhasil ditambahkan.', 'success');
            } else {
                Swal.fire('Info', 'Tidak ada coil yang match.', 'info');
            }
        });

        $(document).on('click', '#btn_cancel_upload', function() {
            parsedCoils = [];
            $('#modalReviewUpload').modal('hide');
        });

        // ═══════════════════════════════════════════════════════════
        // SAVE
        // ═══════════════════════════════════════════════════════════
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
            if ($('#data_po_body tr[data-idx]').length === 0) {
                Swal.fire('Perhatian', 'Belum ada data material dari PO.', 'warning');
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
                if (!result.isConfirmed) return;

                var formData = new FormData(document.getElementById('frm-new-ros'));
                $('.auto_num').each(function() {
                    var name = $(this).attr('name');
                    if (name && $(this).data('autoNumeric')) formData.set(name, $(this).autoNumeric('get'));
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
                                })
                                .then(function() {
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
            });
        });

        // ═══════════════════════════════════════════════════════════
        // INIT: Edit mode — render data yang sudah ada
        // ═══════════════════════════════════════════════════════════
        if (materialsData && materialsData.length > 0) {
            renderProrateLS();
            renderDataPO();
            calcProrateLS();
            recalculate();
            <?php if ($is_edit) : ?>
                loadCoilDataEdit();
            <?php endif; ?>
        }
        isInit = false;

        // Load coil dari DB pada edit mode
        function loadCoilDataEdit() {
            var id_ros = $('#id_ros').val();
            if (!id_ros || id_ros === 'New') return;

            $.post(siteurl + 'new_ros/get_coils_data', {
                id_ros: id_ros
            }, function(res) {
                var resp = (typeof res === 'string') ? JSON.parse(res) : res;
                if (resp.status == 1 && resp.data.length > 0) {
                    $.each(materialsData, function(i, m) {
                        m.coils = [];
                    });
                    $.each(resp.data, function(i, c) {
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
                    renderCoilTable();
                }
            }, 'json');
        }

        // ═══════════════════════════════════════════════════════════
        // FINALIZE
        // ═══════════════════════════════════════════════════════════
        // $('#btn_finalize').on('click', function() {
        //     var id_ros = $('#id_ros').val();
        //     Swal.fire({
        //         title: 'Finalize ROS?',
        //         text: 'Setelah finalize, data tidak bisa diubah dan akan masuk ke proses Incoming.',
        //         icon: 'question',
        //         showCancelButton: true,
        //         confirmButtonText: 'Ya, Selesai',
        //         cancelButtonText: 'Batal'
        //     }).then(function(result) {
        //         if (!result.isConfirmed) return;
        //         $.post(siteurl + 'new_ros/finalize', {
        //             id_ros: id_ros
        //         }, function(res) {
        //             var resp = (typeof res === 'string') ? JSON.parse(res) : res;
        //             if (resp.status == 1) {
        //                 Swal.fire('Berhasil', resp.msg, 'success').then(function() {
        //                     window.location.href = baseurl + 'new_ros';
        //                 });
        //             } else {
        //                 Swal.fire('Gagal', resp.msg, 'error');
        //             }
        //         });
        //     });
        // });

    }); // end ready
</script>