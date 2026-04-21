<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <?= isset($report) ? 'Edit Laporan Produksi: ' . $report->report_no : 'Form Laporan Produksi Baru' ?>
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('production_report/save_report') ?>" id="form-report">

            <?php if (isset($report)): ?>
                <input type="hidden" name="report_no" value="<?= $report->report_no ?>">
            <?php endif; ?>

            <!-- SPK & Coil -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">No SPK <span class="text-danger">*</span></label>
                <div class="col-sm-4">
                    <select name="spk_no" id="spk_no" class="form-control" required>
                        <option value="">-- Pilih SPK --</option>
                        <?php if (isset($spk_list) && !empty($spk_list)): ?>
                            <?php foreach ($spk_list as $s): ?>
                                <option value="<?= $s->spk_no ?>"
                                    data-produk="<?= isset($s->produk_fg) ? $s->produk_fg : '' ?>"
                                    data-nm="<?= isset($s->nm_produk_fg) ? $s->nm_produk_fg : '' ?>"
                                    <?= (isset($report) && $report->spk_no === $s->spk_no) || (isset($spk) && $spk && $spk->spk_no === $s->spk_no) ? 'selected' : '' ?>>
                                    <?= $s->spk_no ?> — <?= isset($s->nm_produk_fg) ? $s->nm_produk_fg : '' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">No Coil <span class="text-danger">*</span></label>
                <div class="col-sm-4">
                    <input type="text" name="no_coil" id="no_coil" class="form-control"
                           value="<?= isset($report) ? $report->no_coil : '' ?>" required>
                </div>
            </div>

            <input type="hidden" name="id_produk_fg" id="id_produk_fg" value="<?= isset($report) && isset($report->produk_fg) ? $report->produk_fg : '' ?>">

            <hr>
            <h6 class="mb-3 text-primary">Hasil Produksi</h6>

            <!-- Reject Supplier -->
            <div class="row mb-2 align-items-center">
                <label class="col-sm-3 col-form-label">Reject Supplier (kg)</label>
                <div class="col-sm-3">
                    <input type="number" step="0.001" min="0" name="reject_supplier" id="reject_supplier"
                           class="form-control calc-input"
                           value="<?= isset($result) ? $result->reject_supplier : '0' ?>">
                </div>
            </div>

            <!-- Waste Potong -->
            <div class="row mb-2 align-items-center">
                <label class="col-sm-3 col-form-label">Waste Potong (kg)</label>
                <div class="col-sm-3">
                    <input type="number" step="0.001" min="0" name="waste_potong" id="waste_potong"
                           class="form-control calc-input"
                           value="<?= isset($result) ? $result->waste_potong : '0' ?>">
                </div>
            </div>

            <!-- NG Internal -->
            <div class="row mb-2 align-items-center">
                <label class="col-sm-3 col-form-label">NG Internal (kg)</label>
                <div class="col-sm-3">
                    <input type="number" step="0.001" min="0" name="ng_internal" id="ng_internal"
                           class="form-control calc-input"
                           value="<?= isset($result) ? $result->ng_internal : '0' ?>">
                </div>
            </div>

            <!-- NG Supplier -->
            <div class="row mb-2 align-items-center">
                <label class="col-sm-3 col-form-label">NG Supplier (kg)</label>
                <div class="col-sm-3">
                    <input type="number" step="0.001" min="0" name="ng_supplier" id="ng_supplier"
                           class="form-control calc-input"
                           value="<?= isset($result) ? $result->ng_supplier : '0' ?>">
                </div>
            </div>

            <!-- Plat BS -->
            <div class="row mb-2 align-items-center">
                <label class="col-sm-3 col-form-label">Plat BS (kg)</label>
                <div class="col-sm-3">
                    <input type="number" step="0.001" min="0" name="plat_bs" id="plat_bs"
                           class="form-control calc-input"
                           value="<?= isset($result) ? $result->plat_bs : '0' ?>">
                </div>
            </div>

            <!-- FG -->
            <div class="row mb-2 align-items-center">
                <label class="col-sm-3 col-form-label">FG (kg)</label>
                <div class="col-sm-2">
                    <input type="number" step="0.001" min="0" name="fg_kg" id="fg_kg"
                           class="form-control calc-input"
                           value="<?= isset($result) ? $result->fg_kg : '0' ?>">
                </div>
                <label class="col-sm-1 col-form-label text-center">Qty</label>
                <div class="col-sm-2">
                    <input type="number" step="0.01" min="0" name="fg_qty" id="fg_qty"
                           class="form-control calc-input"
                           value="<?= isset($result) ? $result->fg_qty : '0' ?>">
                </div>
            </div>

            <!-- KW2 Internal -->
            <div class="row mb-2 align-items-center">
                <label class="col-sm-3 col-form-label">KW2 Internal (kg)</label>
                <div class="col-sm-2">
                    <input type="number" step="0.001" min="0" name="kw2_internal_kg" id="kw2_internal_kg"
                           class="form-control calc-input"
                           value="<?= isset($result) ? $result->kw2_internal_kg : '0' ?>">
                </div>
                <label class="col-sm-1 col-form-label text-center">Qty</label>
                <div class="col-sm-2">
                    <input type="number" step="0.01" min="0" name="kw2_internal_qty"
                           class="form-control"
                           value="<?= isset($result) ? $result->kw2_internal_qty : '0' ?>">
                </div>
            </div>

            <!-- KW2 Supplier -->
            <div class="row mb-2 align-items-center">
                <label class="col-sm-3 col-form-label">KW2 Supplier (kg)</label>
                <div class="col-sm-2">
                    <input type="number" step="0.001" min="0" name="kw2_supplier_kg" id="kw2_supplier_kg"
                           class="form-control calc-input"
                           value="<?= isset($result) ? $result->kw2_supplier_kg : '0' ?>">
                </div>
                <label class="col-sm-1 col-form-label text-center">Qty</label>
                <div class="col-sm-2">
                    <input type="number" step="0.01" min="0" name="kw2_supplier_qty"
                           class="form-control"
                           value="<?= isset($result) ? $result->kw2_supplier_qty : '0' ?>">
                </div>
            </div>

            <!-- Tong Coil -->
            <div class="row mb-2 align-items-center">
                <label class="col-sm-3 col-form-label">Berat Tong Coil (kg)</label>
                <div class="col-sm-3">
                    <input type="number" step="0.001" min="0" name="tong_coil" id="tong_coil"
                           class="form-control"
                           value="<?= isset($result) ? $result->tong_coil : '0' ?>">
                </div>
            </div>

            <hr>

            <!-- Summary Kalkulasi Otomatis -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-secondary">Kalkulasi Otomatis</h6>
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Total Berat Coil</td>
                                    <td class="text-end fw-bold">
                                        <span id="display_total_berat">0.000</span> kg
                                    </td>
                                </tr>
                                <tr>
                                    <td>Net Hasil Produksi</td>
                                    <td class="text-end fw-bold">
                                        <span id="display_net_hasil">0.000</span> kg
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Deviasi Berat FG -->
                <div class="col-md-6">
                    <div class="card" id="card-deviasi">
                        <div class="card-body">
                            <h6 class="card-title text-secondary">Validasi Berat FG</h6>
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Berat Satuan Aktual</td>
                                    <td class="text-end"><span id="display_berat_satuan">—</span></td>
                                </tr>
                                <tr>
                                    <td>Berat Standar</td>
                                    <td class="text-end"><span id="display_berat_standar">—</span></td>
                                </tr>
                                <tr>
                                    <td>Deviasi</td>
                                    <td class="text-end fw-bold"><span id="display_deviasi">—</span></td>
                                </tr>
                            </table>
                            <div id="alert-deviasi" class="alert alert-danger mt-2 mb-0 d-none" role="alert">
                                <i class="fa fa-exclamation-triangle"></i>
                                <strong>Deviasi melebihi toleransi!</strong> Diperlukan konfirmasi QC sebelum posting.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan Laporan
                </button>
                <a href="<?= base_url('production_report') ?>" class="btn btn-secondary ms-2">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>

        </form>
    </div>
</div>

<script>
var beratStandarFG = 0;
var toleransiDeviasi = 0.05;

// Ambil berat standar saat SPK dipilih (via AJAX ke endpoint get_spk_info)
$('#spk_no').on('change', function () {
    var spkNo = $(this).val();
    if (!spkNo) {
        beratStandarFG = 0;
        updateDeviasi();
        return;
    }
    $.get(siteurl + 'production_report/get_spk_info/' + spkNo, function (res) {
        if (res && res.berat_standar) {
            beratStandarFG = parseFloat(res.berat_standar) || 0;
        } else {
            beratStandarFG = 0;
        }
        $('#display_berat_standar').text(beratStandarFG > 0 ? beratStandarFG.toFixed(4) + ' kg' : '—');
        updateDeviasi();
    }, 'json').fail(function () {
        beratStandarFG = 0;
        updateDeviasi();
    });
});

// Hitung ulang saat ada perubahan input
$(document).on('input change', '.calc-input', function () {
    hitungKalkulasi();
});

function getVal(id) {
    return parseFloat($('#' + id).val()) || 0;
}

function hitungKalkulasi() {
    var reject_supplier  = getVal('reject_supplier');
    var waste_potong     = getVal('waste_potong');
    var ng_internal      = getVal('ng_internal');
    var ng_supplier      = getVal('ng_supplier');
    var plat_bs          = getVal('plat_bs');
    var fg_kg            = getVal('fg_kg');
    var fg_qty           = getVal('fg_qty');
    var kw2_internal_kg  = getVal('kw2_internal_kg');
    var kw2_supplier_kg  = getVal('kw2_supplier_kg');
    var tong_coil        = getVal('tong_coil');

    // Total Berat Coil = 8 komponen
    var totalBerat = reject_supplier + waste_potong + ng_internal + ng_supplier
                   + plat_bs + fg_kg + kw2_internal_kg + kw2_supplier_kg;

    // Net Hasil Produksi = Total + tong_coil + berat_cover_wrapping (cover wrapping dari server)
    var coverWrapping = parseFloat($('#cover_wrapping_display').data('value')) || 0;
    var netHasil = totalBerat + tong_coil + coverWrapping;

    $('#display_total_berat').text(totalBerat.toFixed(3));
    $('#display_net_hasil').text(netHasil.toFixed(3));

    // Berat satuan FG
    var beratSatuan = (fg_qty > 0) ? (fg_kg / fg_qty) : 0;
    $('#display_berat_satuan').text(fg_qty > 0 ? beratSatuan.toFixed(4) + ' kg' : '—');

    updateDeviasi(beratSatuan);
}

function updateDeviasi(beratSatuan) {
    if (typeof beratSatuan === 'undefined') {
        var fg_kg  = getVal('fg_kg');
        var fg_qty = getVal('fg_qty');
        beratSatuan = (fg_qty > 0) ? (fg_kg / fg_qty) : 0;
    }

    if (beratStandarFG <= 0 || beratSatuan <= 0) {
        $('#display_deviasi').text('—').removeClass('text-danger text-success');
        $('#alert-deviasi').addClass('d-none');
        return;
    }

    var deviasi = Math.abs(beratSatuan - beratStandarFG) / beratStandarFG;
    var pct = (deviasi * 100).toFixed(2);

    $('#display_deviasi').text(pct + '%');

    if (deviasi > toleransiDeviasi) {
        $('#display_deviasi').addClass('text-danger').removeClass('text-success');
        $('#alert-deviasi').removeClass('d-none');
        $('#card-deviasi').addClass('border-danger');
    } else {
        $('#display_deviasi').addClass('text-success').removeClass('text-danger');
        $('#alert-deviasi').addClass('d-none');
        $('#card-deviasi').removeClass('border-danger');
    }
}

// Init saat halaman load (mode edit)
$(document).ready(function () {
    hitungKalkulasi();
    // Trigger SPK change jika sudah ada nilai
    if ($('#spk_no').val()) {
        $('#spk_no').trigger('change');
    }
});
</script>
