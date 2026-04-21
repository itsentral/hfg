<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="row">
    <!-- Panel Kiri: Input Scan -->
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fa fa-barcode"></i> Scan Issue Material</h5>
            </div>
            <div class="card-body">
                <!-- Pilih SPK -->
                <div class="mb-3">
                    <label class="form-label fw-bold">No SPK <span class="text-danger">*</span></label>
                    <select id="select-spk" class="form-select">
                        <option value="">-- Pilih SPK --</option>
                        <?php foreach ($spk_list as $s): ?>
                            <option value="<?= htmlspecialchars($s->spk_no) ?>"
                                data-produk="<?= htmlspecialchars($s->nm_produk_fg ?: $s->produk_fg) ?>"
                                <?= (isset($_GET['spk_no']) && $_GET['spk_no'] === $s->spk_no) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s->spk_no) ?> — <?= htmlspecialchars($s->nm_produk_fg ?: $s->produk_fg) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Info SPK terpilih -->
                <div id="spk-info" class="alert alert-info py-2 mb-3" style="display:none;">
                    <div id="spk-info-text"></div>
                    <div class="mt-1">
                        <div class="progress" style="height: 8px;">
                            <div id="scan-progress-bar" class="progress-bar bg-success" style="width: 0%;"></div>
                        </div>
                        <small id="scan-progress-text" class="text-muted">0 / 0 coil ter-scan</small>
                    </div>
                </div>

                <!-- Input Barcode -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Scan / Input No Coil <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <input type="text" id="input-barcode" class="form-control"
                               placeholder="Scan barcode atau ketik no coil..."
                               autocomplete="off" autofocus>
                        <button type="button" class="btn btn-primary" id="btn-scan">
                            <i class="fa fa-check"></i> Issue
                        </button>
                    </div>
                    <small class="text-muted">Tekan Enter atau klik tombol Issue setelah scan</small>
                </div>

                <!-- Hasil Scan -->
                <div id="scan-result" style="display:none;"></div>
            </div>
        </div>

        <!-- Daftar Coil SPK -->
        <div class="card" id="card-coil-list" style="display:none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Coil dalam SPK</h6>
                <span id="badge-coil-count" class="badge bg-secondary">0 coil</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>No Coil</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-coil-list">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Kanan: Log Scan Hari Ini -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fa fa-list"></i> Log Scan Hari Ini</h6>
                <div class="d-flex gap-2 align-items-center">
                    <span id="badge-log-count" class="badge bg-secondary">0 scan</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-refresh-log">
                        <i class="fa fa-refresh"></i>
                    </button>
                    <a href="<?= base_url('production_issue') ?>" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-bordered mb-0" id="tbl-scan-log">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th>No Coil</th>
                                <th>No SPK</th>
                                <th class="text-center">Waktu</th>
                                <th class="text-center">Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-scan-log">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    Pilih SPK untuk melihat log scan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var siteurl_scan = '<?= base_url() ?>';
var currentSpkNo = '<?= isset($_GET['spk_no']) ? htmlspecialchars($_GET['spk_no']) : '' ?>';

$(document).ready(function () {

    // Auto-load jika ada spk_no dari URL
    if (currentSpkNo) {
        $('#select-spk').val(currentSpkNo).trigger('change');
    }

    // Ganti SPK
    $('#select-spk').on('change', function () {
        currentSpkNo = $(this).val();
        if (!currentSpkNo) {
            $('#spk-info').hide();
            $('#card-coil-list').hide();
            resetScanResult();
            return;
        }
        loadSpkInfo(currentSpkNo);
        loadScanLog(currentSpkNo);
        focusBarcode();
    });

    // Scan via Enter
    $('#input-barcode').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            doScan();
        }
    });

    // Scan via tombol
    $('#btn-scan').on('click', function () {
        doScan();
    });

    // Refresh log
    $('#btn-refresh-log').on('click', function () {
        if (currentSpkNo) {
            loadScanLog(currentSpkNo);
            loadSpkInfo(currentSpkNo);
        }
    });

    function doScan() {
        var no_coil = $('#input-barcode').val().trim();
        if (!no_coil) {
            showScanResult(false, 'No coil tidak boleh kosong');
            return;
        }
        if (!currentSpkNo) {
            showScanResult(false, 'Pilih SPK terlebih dahulu');
            return;
        }

        $('#btn-scan').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: siteurl_scan + 'production_issue/process_scan',
            type: 'POST',
            data: { no_coil: no_coil, spk_no: currentSpkNo },
            dataType: 'json',
            success: function (res) {
                showScanResult(res.success, res.message, res.data);
                if (res.success) {
                    loadScanLog(currentSpkNo);
                    loadSpkInfo(currentSpkNo);
                    // Cek apakah semua coil sudah ter-scan
                    if (res.data && res.data.all_scanned) {
                        setTimeout(function () {
                            swal('Selesai!', 'Semua coil sudah di-issue. SPK berubah ke status In Process.', 'success');
                        }, 500);
                    }
                }
                $('#input-barcode').val('');
                focusBarcode();
            },
            error: function () {
                showScanResult(false, 'Terjadi kesalahan koneksi, silakan coba lagi');
                focusBarcode();
            },
            complete: function () {
                $('#btn-scan').prop('disabled', false).html('<i class="fa fa-check"></i> Issue');
            }
        });
    }

    function showScanResult(success, message, data) {
        var cls  = success ? 'alert-success' : 'alert-danger';
        var icon = success ? 'fa-check-circle' : 'fa-times-circle';
        var html = '<div class="alert ' + cls + ' py-2 mb-0">';
        html += '<i class="fa ' + icon + '"></i> <strong>' + (success ? 'Berhasil' : 'Gagal') + ':</strong> ' + message;
        if (success && data && data.progress) {
            html += '<div class="mt-1 small">Progress: ' + data.progress.scanned + ' / ' + data.progress.total + ' coil ter-scan</div>';
        }
        html += '</div>';
        $('#scan-result').html(html).show();
    }

    function resetScanResult() {
        $('#scan-result').hide().html('');
    }

    function loadSpkInfo(spk_no) {
        $.get(siteurl_scan + 'production_issue/get_spk_info', { spk_no: spk_no }, function (res) {
            if (res.status === 'ok') {
                var s = res.spk;
                var pct = s.total_coil > 0 ? Math.round((s.scanned_coil / s.total_coil) * 100) : 0;
                $('#spk-info-text').html(
                    '<strong>' + s.spk_no + '</strong> — ' + (s.nm_produk_fg || s.produk_fg) +
                    ' | Status: <span class="badge bg-primary">' + s.status + '</span>'
                );
                $('#scan-progress-bar').css('width', pct + '%');
                $('#scan-progress-text').text(s.scanned_coil + ' / ' + s.total_coil + ' coil ter-scan (' + pct + '%)');
                $('#spk-info').show();

                // Render daftar coil
                if (res.details && res.details.length > 0) {
                    var html = '';
                    $.each(res.details, function (i, d) {
                        var statusBadge = d.scan_status === 'scanned'
                            ? '<span class="badge bg-success"><i class="fa fa-check"></i> Issued</span>'
                            : '<span class="badge bg-secondary">Pending</span>';
                        var rowClass = d.scan_status === 'scanned' ? 'table-success' : '';
                        html += '<tr class="' + rowClass + '">';
                        html += '<td><small>' + d.no_coil + '</small></td>';
                        html += '<td class="text-center">' + statusBadge + '</td>';
                        html += '</tr>';
                    });
                    $('#tbody-coil-list').html(html);
                    $('#badge-coil-count').text(res.details.length + ' coil');
                    $('#card-coil-list').show();
                }
            }
        }, 'json');
    }

    function loadScanLog(spk_no) {
        $.get(siteurl_scan + 'production_issue/get_scan_log_today', { spk_no: spk_no }, function (res) {
            if (res.status === 'ok') {
                var html = '';
                if (res.data && res.data.length > 0) {
                    $.each(res.data, function (i, row) {
                        var statusBadge = row.status_scan === 'success'
                            ? '<span class="badge bg-success">Sukses</span>'
                            : '<span class="badge bg-danger">Ditolak</span>';
                        var rowClass = row.status_scan === 'success' ? '' : 'table-danger';
                        html += '<tr class="' + rowClass + '">';
                        html += '<td class="text-center">' + (i + 1) + '</td>';
                        html += '<td><small>' + row.no_coil + '</small></td>';
                        html += '<td><small>' + row.spk_no + '</small></td>';
                        html += '<td class="text-center"><small>' + row.scan_time + '</small></td>';
                        html += '<td class="text-center">' + statusBadge + '</td>';
                        html += '<td><small>' + (row.keterangan || '-') + '</small></td>';
                        html += '</tr>';
                    });
                    $('#badge-log-count').text(res.data.length + ' scan');
                } else {
                    html = '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada log scan hari ini</td></tr>';
                    $('#badge-log-count').text('0 scan');
                }
                $('#tbody-scan-log').html(html);
            }
        }, 'json');
    }

    function focusBarcode() {
        setTimeout(function () { $('#input-barcode').focus(); }, 100);
    }
});
</script>
