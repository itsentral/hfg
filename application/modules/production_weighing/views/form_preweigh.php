<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Form Timbang Awal Coil</h5>
        <a href="<?= base_url('production_weighing') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="form-preweigh" action="<?= base_url('production_weighing/save_preweigh') ?>" method="POST">

            <!-- Scan Barcode Coil -->
            <div class="card mb-3 border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fa fa-barcode"></i> Scan Barcode Coil</h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No Coil <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="input-no-coil" class="form-control form-control-lg"
                                    placeholder="Scan atau ketik no coil..." autofocus>
                                <button type="button" class="btn btn-primary" id="btn-cari-coil">
                                    <i class="fa fa-search"></i> Cari
                                </button>
                            </div>
                            <input type="hidden" name="no_coil" id="no_coil">
                            <input type="hidden" name="spk_no" id="spk_no">
                        </div>
                        <div class="col-md-6">
                            <div id="coil-info-box" class="alert alert-info d-none mb-0">
                                <strong>SPK:</strong> <span id="info-spk-no">-</span> &nbsp;|&nbsp;
                                <strong>Produk:</strong> <span id="info-produk">-</span>
                            </div>
                            <div id="coil-error-box" class="alert alert-danger d-none mb-0">
                                <i class="fa fa-exclamation-triangle"></i> <span id="coil-error-msg"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Packing List -->
            <div id="section-pl" class="d-none">
                <div class="card mb-3 border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fa fa-file-text"></i> Data Packing List Supplier</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Gross Weight PL (kg)</label>
                                <input type="number" step="0.001" name="gross_pl" id="gross_pl"
                                    class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Net Weight PL (kg)</label>
                                <input type="number" step="0.001" name="net_pl" id="net_pl"
                                    class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Komponen Berat -->
                <div class="card mb-3 border-warning">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0"><i class="fa fa-balance-scale"></i> Input Komponen Berat Timbang</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Gross Aktual (kg) <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" name="gross_actual" id="gross_actual"
                                    class="form-control calc-input" placeholder="0.000" required>
                                <small class="text-muted">Berat kotor aktual timbangan</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Berat Kulit (kg)</label>
                                <input type="number" step="0.001" name="berat_kulit" id="berat_kulit"
                                    class="form-control" placeholder="0.000" value="0">
                                <small class="text-muted">Packaging luar (tidak masuk net)</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Berat Clamp/Ring (kg)</label>
                                <input type="number" step="0.001" name="berat_clamp_ring" id="berat_clamp_ring"
                                    class="form-control" placeholder="0.000" value="0">
                                <small class="text-muted">Clamp/ring (tidak masuk net)</small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Berat Coil + Tong (kg) <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" name="berat_coil_tong" id="berat_coil_tong"
                                    class="form-control calc-input" placeholder="0.000" required>
                                <small class="text-muted">Masuk perhitungan net</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Berat Cover Wrapping (kg) <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" name="berat_cover_wrapping" id="berat_cover_wrapping"
                                    class="form-control calc-input" placeholder="0.000" required>
                                <small class="text-muted">Masuk perhitungan net</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Net Weight Timbang Awal (kg)</label>
                                <input type="text" id="net_timbang_awal_display" class="form-control fw-bold bg-light"
                                    readonly placeholder="Auto-hitung">
                                <small class="text-muted">= Coil+Tong + Cover Wrapping</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hasil Perbandingan Real-time -->
                <div class="card mb-3" id="card-selisih">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fa fa-exchange"></i> Perbandingan vs Packing List</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted small">Selisih Gross</div>
                                    <div class="fs-5 fw-bold" id="display-selisih-gross">-</div>
                                    <div class="text-muted small">kg</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted small">Selisih Net</div>
                                    <div class="fs-5 fw-bold" id="display-selisih-net">-</div>
                                    <div class="text-muted small">kg</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted small">Deviasi %</div>
                                    <div class="fs-5 fw-bold" id="display-selisih-pct">-</div>
                                    <div class="text-muted small">dari net PL</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <div class="text-muted small">Status</div>
                                    <div class="fs-5 fw-bold" id="display-status">-</div>
                                </div>
                            </div>
                        </div>

                        <!-- Warning Exception -->
                        <div id="warning-exception" class="alert alert-danger mt-3 d-none">
                            <i class="fa fa-exclamation-triangle fa-lg"></i>
                            <strong>PERHATIAN!</strong> Selisih berat melebihi toleransi yang ditetapkan.
                            Record akan ditandai sebagai <strong>Exception</strong> dan notifikasi akan dikirim ke Supervisor/QC.
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg" id="btn-simpan">
                        <i class="fa fa-save"></i> Simpan Timbang Awal
                    </button>
                    <a href="<?= base_url('production_weighing') ?>" class="btn btn-secondary btn-lg">Batal</a>
                </div>
            </div><!-- /section-pl -->

        </form>
    </div>
</div>

<script>
var TOLERANSI_PCT = 0.05; // default, bisa di-override dari server

$(document).ready(function () {

    // Trigger cari coil saat Enter di input
    $('#input-no-coil').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btn-cari-coil').trigger('click');
        }
    });

    // Cari info coil via AJAX
    $('#btn-cari-coil').on('click', function () {
        var no_coil = $.trim($('#input-no-coil').val());
        if (!no_coil) return;

        $('#coil-info-box').addClass('d-none');
        $('#coil-error-box').addClass('d-none');
        $('#section-pl').addClass('d-none');

        $.get(siteurl + 'production_weighing/get_coil_info', { no_coil: no_coil }, function (res) {
            if (res.status !== 'ok') {
                $('#coil-error-msg').text(res.message);
                $('#coil-error-box').removeClass('d-none');
                return;
            }

            var coil = res.coil_data;
            var pl   = res.pl_data;

            // Isi hidden fields
            $('#no_coil').val(no_coil);
            $('#spk_no').val(coil.spk_no);

            // Tampilkan info coil
            $('#info-spk-no').text(coil.spk_no);
            $('#info-produk').text(coil.nm_produk_fg || '-');
            $('#coil-info-box').removeClass('d-none');

            // Isi data PL
            if (pl) {
                $('#gross_pl').val(parseFloat(pl.gross_pl || 0).toFixed(3));
                $('#net_pl').val(parseFloat(pl.net_pl || 0).toFixed(3));
            } else {
                $('#gross_pl').val('0.000');
                $('#net_pl').val('0.000');
            }

            $('#section-pl').removeClass('d-none');
            $('#gross_actual').focus();
        }, 'json').fail(function () {
            $('#coil-error-msg').text('Gagal menghubungi server, silakan coba lagi.');
            $('#coil-error-box').removeClass('d-none');
        });
    });

    // Auto-hitung net weight dan selisih secara real-time
    $(document).on('input', '.calc-input, #gross_actual', function () {
        recalcSelisih();
    });

    function recalcSelisih() {
        var berat_coil_tong      = parseFloat($('#berat_coil_tong').val()) || 0;
        var berat_cover_wrapping = parseFloat($('#berat_cover_wrapping').val()) || 0;
        var gross_actual         = parseFloat($('#gross_actual').val()) || 0;
        var gross_pl             = parseFloat($('#gross_pl').val()) || 0;
        var net_pl               = parseFloat($('#net_pl').val()) || 0;

        var net_timbang = berat_coil_tong + berat_cover_wrapping;
        $('#net_timbang_awal_display').val(net_timbang.toFixed(3));

        var selisih_gross = gross_actual - gross_pl;
        var selisih_net   = net_timbang - net_pl;
        var selisih_pct   = (net_pl > 0) ? Math.abs(selisih_net) / net_pl : 0;
        var is_exception  = selisih_pct > TOLERANSI_PCT;

        // Tampilkan selisih gross
        var $sg = $('#display-selisih-gross');
        $sg.text(selisih_gross.toFixed(3));
        $sg.removeClass('text-success text-danger text-muted');
        $sg.addClass(selisih_gross < 0 ? 'text-danger' : (selisih_gross > 0 ? 'text-warning' : 'text-success'));

        // Tampilkan selisih net
        var $sn = $('#display-selisih-net');
        $sn.text(selisih_net.toFixed(3));
        $sn.removeClass('text-success text-danger text-muted');
        $sn.addClass(is_exception ? 'text-danger' : 'text-success');

        // Tampilkan pct
        var $sp = $('#display-selisih-pct');
        $sp.text((selisih_pct * 100).toFixed(2) + '%');
        $sp.removeClass('text-success text-danger text-muted');
        $sp.addClass(is_exception ? 'text-danger fw-bold' : 'text-success');

        // Status
        var $st = $('#display-status');
        if (net_timbang === 0) {
            $st.html('<span class="badge bg-secondary">-</span>');
        } else if (is_exception) {
            $st.html('<span class="badge bg-danger">Exception</span>');
            $('#warning-exception').removeClass('d-none');
        } else {
            $st.html('<span class="badge bg-success">Normal</span>');
            $('#warning-exception').addClass('d-none');
        }
    }
});
</script>
