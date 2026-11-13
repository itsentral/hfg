<?php
$ENABLE_VIEW   = has_permission('Request_List.View');
$ENABLE_MANAGE = has_permission('Request_List.Manage');
?>

<style>
    .swal2-container {
        z-index: 999999 !important;
    }

    .swal2-popup {
        z-index: 1000000 !important;
    }

    .skeleton {
        border-radius: 4px;
        animation: shimmer 1.5s infinite linear;
        background: linear-gradient(90deg, #f2f2f2 25%, #e0e0e0 50%, #f2f2f2 75%);
        background-size: 200% 100%;
    }

    .skeleton-line {
        height: 20px;
        margin: 8px 0;
        border-radius: 4px;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    /* Scanner & tabs compact styling */
    .scan-bar-group {
        width: 100%;
    }

    @media (min-width: 768px) {
        .scan-bar-group {
            max-width: 380px;
        }
    }

    @media (max-width: 767.98px) {
        .scan-bar-group .form-control,
        .scan-bar-group .btn,
        .scan-bar-group .input-group-text {
            min-height: 40px;
            font-size: 16px;
        }

        #modal-scan-pack-spk .select2-container .select2-selection--single,
        #modal-scan-pack-spk .form-select {
            min-height: 40px;
            font-size: 16px;
        }
    }

    #scan-pack-input:focus {
        border-color: #556ee6;
        box-shadow: 0 0 0 0.15rem rgba(85, 110, 230, 0.2);
    }

    #spk-status-tabs .nav-link:not(.active) {
        color: #495057;
    }

    #spk-status-tabs .nav-link:not(.active):hover {
        background-color: #f1f3f5;
    }

    #modal-scan-pack-spk .select2-container .select2-selection--single {
        min-height: 38px;
        display: flex;
        align-items: center;
    }

    #qr-reader {
        border: none !important;
    }

    #qr-reader button {
        background-color: #007bff !important;
        color: #fff !important;
        border: none !important;
        padding: 8px 18px !important;
        border-radius: 6px !important;
        cursor: pointer;
        margin: 6px 4px !important;
    }
</style>

<!-- Skeleton loading -->
<div id="skeleton-content">
    <div class="skeleton">
        <div class="skeleton-line" style="width:60%"></div>
        <div class="skeleton-line" style="width:100%;height:200px"></div>
    </div>
</div>

<!-- Actual content (hidden until loaded) -->
<div id="actual-content" style="display:none">

    <!-- Main Card -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-3 gap-2">
                <!-- Nav Tabs Status Warehouse (Aktivitas Scan Material) -->
                <ul class="nav nav-pills mb-0 order-2 order-md-1 flex-wrap gap-1" id="spk-status-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-1 px-2 px-md-3 small fw-semibold" id="tab-waiting-scan" data-status="waiting_scan" type="button" role="tab">
                            Menunggu Scan <span class="badge bg-warning text-dark ms-1 rounded-pill" id="badge-count-waiting-scan">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-2 px-md-3 small fw-semibold" id="tab-scanned" data-status="scanned" type="button" role="tab">
                            Sudah Scan <span class="badge bg-success ms-1 rounded-pill" id="badge-count-scanned">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-2 px-md-3 small fw-semibold" id="tab-all" data-status="all" type="button" role="tab">
                            Semua SPK <span class="badge bg-secondary ms-1 rounded-pill" id="badge-count-all">0</span>
                        </button>
                    </li>
                </ul>

                <?php if ($ENABLE_MANAGE): ?>
                    <!-- Compact Inline Scan No. Pack Bar (Paling atas di HP/Tablet, Kanan di Desktop) -->
                    <div class="input-group input-group-sm order-1 order-md-2 scan-bar-group">
                        <span class="input-group-text bg-light text-primary"><i class="fa fa-qrcode"></i></span>
                        <input type="text" id="scan-pack-input" class="form-control fw-semibold" placeholder="Scan / Ketik No. Pack..." autocomplete="off" enterkeyhint="search">
                        <button class="btn btn-primary fw-semibold" type="button" id="btn-submit-scan">
                            <i class="fa fa-search me-1"></i> Scan
                        </button>
                        <button class="btn btn-outline-secondary" type="button" id="btn-open-camera" title="Scan dengan Kamera">
                            <i class="fa fa-camera"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($ENABLE_MANAGE): ?>
                <div id="camera-container" class="mb-3 mx-auto ms-md-auto border rounded p-2 bg-light" style="display:none; width:100%; max-width: 360px;">
                    <div id="qr-reader" style="width:100%"></div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2 w-100" id="btn-close-camera">Tutup Kamera</button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table id="table-request-list" class="table table-bordered table-striped align-middle" width="100%">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>SPK No</th>
                            <th>Tanggal SPK</th>
                            <th>Shift</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Jumlah Produk</th>
                            <th width="24%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Popup Pilih No. SPK setelah Scan No. Pack -->
<div class="modal fade" id="modal-scan-pack-spk" tabindex="-1" aria-labelledby="modalScanPackSpkLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalScanPackSpkLabel">
                    <i class="fa fa-box-open me-2"></i> Konfirmasi Scan Pack &amp; Pilih No. SPK
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Info Pack -->
                <div class="alert alert-light border mb-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <small class="text-muted d-block">No. Pack</small>
                            <span class="badge bg-primary fs-6" id="modal-pack-code">-</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Asal &rarr; Tujuan Gudang</small>
                            <strong>PRO</strong> &rarr; <strong class="text-success">PRT (Production 2)</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Ringkasan Isi Pack</small>
                            <strong id="modal-pack-summary">-</strong>
                        </div>
                    </div>
                </div>

                <!-- Daftar Coil dalam Pack -->
                <div class="mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted mb-1">Detail Coil dalam Pack</label>
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-bordered table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="6%" class="text-center">No</th>
                                    <th>Material</th>
                                    <th width="20%">No. Coil</th>
                                    <th width="22%">Kode Internal</th>
                                    <th width="16%" class="text-end">Net Weight (Kg)</th>
                                </tr>
                            </thead>
                            <tbody id="modal-pack-coils-body"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Pilih No. SPK Tujuan -->
                <div class="mb-2">
                    <label for="select-spk-scan" class="form-label fw-bold">
                        Pilih No. SPK Tujuan (Acuan No. Transaksi Kartu Material) <span class="text-danger">*</span>
                    </label>
                    <select id="select-spk-scan" class="form-select" style="width:100%;">
                        <option value="">-- Pilih No. SPK --</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success fw-bold" id="btn-confirm-scan-pack">
                    <i class="fa fa-check-circle me-1"></i> Pindahkan Pack ke PRT
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    $(document).ready(function() {

        const BASE_URL = siteurl + active_controller;
        var currentStatusFilter = 'waiting_scan';
        var scannedPackCode = '';
        var isProcessingScan = false;

        // ---------------------------------------------------------------
        // MASTER SOUND & VIBRATE INTEGRATION (scan_success / scan_error)
        // ---------------------------------------------------------------
        var activeSoundConfig = {};
        var preloadedAudioMap = {};
        var currentAppAudio = null;
        var sharedAudioCtx = null;

        function loadSoundConfig() {
            $.ajax({
                url: siteurl + 'master_sound/get_sound_config',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res && res.status == 1 && res.data) {
                        activeSoundConfig = res.data;
                        $.each(activeSoundConfig, function(code, cfg) {
                            if (cfg && cfg.sound_url) {
                                try {
                                    var audio = new Audio(cfg.sound_url);
                                    audio.preload = 'auto';
                                    preloadedAudioMap[code] = audio;
                                } catch (e) {}
                            }
                        });
                    }
                }
            });
        }
        loadSoundConfig();
        $(document).one('touchstart click', unlockAudioPlayback);

        // Unlock Web Audio & preloaded audio saat ada interaksi user (penting untuk browser tablet/HP)
        function unlockAudioPlayback() {
            try {
                var AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (AudioCtx) {
                    if (!sharedAudioCtx) {
                        sharedAudioCtx = new AudioCtx();
                    }
                    if (sharedAudioCtx.state === 'suspended') {
                        sharedAudioCtx.resume().catch(function() {});
                    }
                }
            } catch (e) {}

            $.each(preloadedAudioMap, function(_, audio) {
                if (audio._unlocked) return;
                audio._unlocked = true;
                try {
                    audio.muted = true;
                    var p = audio.play();
                    if (p && typeof p.then === 'function') {
                        p.then(function() {
                            if (!audio._isPlayingReal) {
                                audio.pause();
                                audio.currentTime = 0;
                            }
                            audio.muted = false;
                        }).catch(function() {
                            audio._unlocked = false;
                            audio.muted = false;
                        });
                    } else {
                        if (!audio._isPlayingReal) {
                            audio.pause();
                            audio.currentTime = 0;
                        }
                        audio.muted = false;
                    }
                } catch (e) {
                    audio._unlocked = false;
                    audio.muted = false;
                }
            });
        }

        function triggerVibrate(level) {
            level = parseInt(level, 10) || 0;
            if (level <= 0 || !('vibrate' in navigator)) return;

            try {
                var duration = level * 100;
                navigator.vibrate([duration, 50, duration]);
            } catch (e) {
                try {
                    navigator.vibrate(level * 100);
                } catch (err) {}
            }
        }

        function playFallbackTone(isError) {
            try {
                var AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (AudioCtx) {
                    if (!sharedAudioCtx) {
                        sharedAudioCtx = new AudioCtx();
                    }
                    if (sharedAudioCtx.state === 'suspended') {
                        sharedAudioCtx.resume().catch(function() {});
                    }

                    var playTone = function(freq, startTime, duration, volume, waveType) {
                        var osc = sharedAudioCtx.createOscillator();
                        var gain = sharedAudioCtx.createGain();
                        osc.connect(gain);
                        gain.connect(sharedAudioCtx.destination);

                        osc.type = waveType || 'sine';
                        osc.frequency.setValueAtTime(freq, startTime);

                        gain.gain.setValueAtTime(0, startTime);
                        gain.gain.linearRampToValueAtTime(volume || 0.4, startTime + 0.02);
                        gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);

                        osc.start(startTime);
                        osc.stop(startTime + duration);
                    };

                    var now = sharedAudioCtx.currentTime;
                    if (isError) {
                        playTone(320, now, 0.14, 0.35, 'sawtooth');
                        playTone(220, now + 0.16, 0.22, 0.35, 'sawtooth');
                    } else {
                        playTone(1046.50, now, 0.15, 0.4, 'sine');        // C6
                        playTone(1318.51, now + 0.15, 0.15, 0.4, 'sine'); // E6
                        playTone(1567.98, now + 0.30, 0.35, 0.4, 'sine'); // G6
                    }
                }
            } catch (e) {}

            if ('vibrate' in navigator) {
                try {
                    navigator.vibrate(isError ? [180, 60, 180] : [50, 30, 50, 30, 100]);
                } catch (e) {}
            }
        }

        function playAppSound(eventCode) {
            eventCode = eventCode || 'scan_success';
            var isError = (eventCode === 'scan_error');
            var cfg = activeSoundConfig && activeSoundConfig[eventCode] ? activeSoundConfig[eventCode] : null;

            try {
                if (currentAppAudio) {
                    currentAppAudio._isPlayingReal = false;
                    currentAppAudio.pause();
                    currentAppAudio.currentTime = 0;
                }

                if (cfg && cfg.sound_url) {
                    var audio = preloadedAudioMap[eventCode] || new Audio(cfg.sound_url);
                    preloadedAudioMap[eventCode] = audio;
                    currentAppAudio = audio;
                    audio._isPlayingReal = true;
                    audio.onended = function() {
                        audio._isPlayingReal = false;
                    };
                    audio.muted = false;
                    audio.currentTime = 0;

                    var playPromise = audio.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(function() {
                            audio._isPlayingReal = false;
                            playFallbackTone(isError);
                        });
                    }

                    triggerVibrate(cfg.vibrate_level || 0);
                    return;
                }
            } catch (e) {}

            playFallbackTone(isError);
        }

        // Initialize DataTables
        var tableRequestList = $('#table-request-list').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + '/data_side',
                type: 'GET',
                data: function(d) {
                    d.status_filter = currentStatusFilter;
                },
                dataSrc: function(json) {
                    if (json.counts) {
                        $('#badge-count-waiting-scan').text(json.counts.waiting_scan || 0);
                        $('#badge-count-scanned').text(json.counts.scanned || 0);
                        $('#badge-count-all').text(json.counts.all || 0);
                    }
                    return json.data;
                }
            },
            columns: [{
                    data: 0,
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 1,
                    className: 'text-nowrap'
                },
                {
                    data: 2
                },
                {
                    data: 3
                },
                {
                    data: 4,
                    className: 'text-center'
                },
                {
                    data: 5,
                    className: 'text-center'
                },
                {
                    data: 6,
                    orderable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [2, 'desc']
            ],
            pageLength: 10
        });

        function isDesktopKeyboardDevice() {
            return window.innerWidth >= 992 && !('ontouchstart' in window && navigator.maxTouchPoints > 0);
        }

        // Tab click event
        $('#spk-status-tabs button').on('click', function(e) {
            e.preventDefault();
            $('#spk-status-tabs button').removeClass('active');
            $(this).addClass('active');
            currentStatusFilter = $(this).data('status');
            tableRequestList.ajax.reload();
        });

        // Skeleton transition
        setTimeout(function() {
            $('#skeleton-content').hide();
            $('#actual-content').fadeIn(200, function() {
                tableRequestList.columns.adjust().draw();
                if (isDesktopKeyboardDevice()) {
                    $('#scan-pack-input').focus();
                }
            });
        }, 300);

        // Helper Bootstrap Modal show/hide (BS5 & jQuery fallback)
        function showPackModal() {
            var el = document.getElementById('modal-scan-pack-spk');
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(el).show();
            } else {
                $(el).modal('show');
            }
        }

        function hidePackModal() {
            var el = document.getElementById('modal-scan-pack-spk');
            if (window.bootstrap && bootstrap.Modal) {
                var instance = bootstrap.Modal.getInstance(el);
                if (instance) instance.hide();
            } else {
                $(el).modal('hide');
            }
        }

        $('#modal-scan-pack-spk').on('hidden.bs.modal', function() {
            scannedPackCode = '';
            if (isDesktopKeyboardDevice()) {
                $('#scan-pack-input').focus();
            }
        });

        // ---------------------------------------------------------------
        // SCAN NO. PACK -> CHECK PACK -> POPUP PILIH SPK -> CONFIRM PRT
        // ---------------------------------------------------------------
        $('#scan-pack-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                unlockAudioPlayback();
                var packCode = $(this).val().trim();
                processScanPack(packCode);
            }
        });

        $('#btn-submit-scan').on('click', function() {
            unlockAudioPlayback();
            var packCode = $('#scan-pack-input').val().trim();
            processScanPack(packCode);
        });

        function processScanPack(packCode) {
            if (!packCode || isProcessingScan) return;
            isProcessingScan = true;
            if (!isDesktopKeyboardDevice()) {
                $('#scan-pack-input').blur();
            }

            Swal.fire({
                title: 'Memeriksa Pack...',
                text: 'Memvalidasi No. Pack di WRH. Production 1 (PRO)...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: BASE_URL + '/check_pack',
                type: 'POST',
                data: {
                    pack_code: packCode
                },
                dataType: 'json',
                success: function(res) {
                    Swal.close();
                    if (res.status == 1) {
                        playAppSound('scan_success');
                        scannedPackCode = res.pack.pack_code;
                        populateScanPackModal(res.pack, res.coils, res.spk_list);
                        showPackModal();
                    } else {
                        playAppSound('scan_error');
                        Swal.fire({
                            icon: 'error',
                            title: 'Scan Pack Ditolak',
                            text: res.message || 'Pack tidak valid.'
                        });
                    }
                },
                error: function() {
                    playAppSound('scan_error');
                    Swal.fire('Error', 'Terjadi kesalahan jaringan saat memeriksa pack.', 'error');
                },
                complete: function() {
                    isProcessingScan = false;
                }
            });
        }

        function populateScanPackModal(pack, coils, spkList) {
            $('#modal-pack-code').text(pack.pack_code);
            $('#modal-pack-summary').text(
                (pack.coil_count || 0) + ' Coil | ' +
                Number(pack.total_net_weight || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' Kg'
            );

            var tbodyHtml = '';
            $.each(coils || [], function(idx, c) {
                tbodyHtml += '<tr>' +
                    '<td class="text-center">' + (idx + 1) + '</td>' +
                    '<td><div class="fw-semibold">' + escHtml(c.nm_material || '-') + '</div><small class="text-muted">' + escHtml(c.id_material || '') + '</small></td>' +
                    '<td>' + escHtml(c.no_coil || '-') + '</td>' +
                    '<td><code>' + escHtml(c.kode_internal || '-') + '</code></td>' +
                    '<td class="text-end">' + Number(c.net_weight || 0).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + '</td>' +
                    '</tr>';
            });
            $('#modal-pack-coils-body').html(tbodyHtml || '<tr><td colspan="5" class="text-center text-muted">Tidak ada coil.</td></tr>');

            var $select = $('#select-spk-scan');
            if ($select.data('select2')) {
                $select.select2('destroy');
            }

            var optsHtml = '<option value="">-- Pilih No. SPK --</option>';
            $.each(spkList || [], function(_, s) {
                var prodText = s.produk_summary ? ' - ' + s.produk_summary : '';
                var label = s.spk_no + ' (' + s.status + ' | ' + s.tgl_spk + ')' + prodText;
                optsHtml += '<option value="' + escAttr(s.spk_no) + '">' + escHtml(label) + '</option>';
            });
            $select.html(optsHtml);

            if ($.fn.select2) {
                $select.select2({
                    dropdownParent: $('#modal-scan-pack-spk'),
                    width: '100%',
                    placeholder: '-- Pilih No. SPK --',
                    allowClear: true
                });
            }
        }

        $('#btn-confirm-scan-pack').on('click', function() {
            unlockAudioPlayback();
            var spkNo = ($('#select-spk-scan').val() || '').trim();
            if (!scannedPackCode) {
                playAppSound('scan_error');
                Swal.fire('Error', 'Data pack tidak ditemukan, silakan scan ulang.', 'error');
                return;
            }
            if (!spkNo) {
                playAppSound('scan_error');
                Swal.fire('Perhatian', 'Silakan pilih No. SPK terlebih dahulu.', 'warning');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Memproses...');

            $.ajax({
                url: BASE_URL + '/confirm_scan_pack',
                type: 'POST',
                data: {
                    pack_code: scannedPackCode,
                    spk_no: spkNo
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1) {
                        playAppSound('scan_success');
                        hidePackModal();
                        $('#scan-pack-input').val('');
                        if (isDesktopKeyboardDevice()) {
                            $('#scan-pack-input').focus();
                        }
                        if (res.counts) {
                            $('#badge-count-waiting-scan').text(res.counts.waiting_scan || 0);
                            $('#badge-count-scanned').text(res.counts.scanned || 0);
                            $('#badge-count-all').text(res.counts.all || 0);
                        }
                        tableRequestList.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Scan',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        playAppSound('scan_error');
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Scan',
                            text: res.message || 'Gagal memproses pemindahan pack.'
                        });
                    }
                },
                error: function() {
                    playAppSound('scan_error');
                    Swal.fire('Error', 'Terjadi kesalahan jaringan saat konfirmasi pack.', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fa fa-check-circle me-1"></i> Pindahkan Pack ke PRT');
                }
            });
        });

        // ---------------------------------------------------------------
        // CAMERA QR SCANNER (SCAN NO. PACK - MOBILE REAR CAMERA READY)
        // ---------------------------------------------------------------
        var html5QrcodeScanner = null;

        $('#btn-open-camera').click(function() {
            unlockAudioPlayback();
            var isSecureOrigin = (window.location.protocol === 'https:' ||
                window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1');

            if (!isSecureOrigin && !navigator.mediaDevices) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Kamera Membutuhkan HTTPS',
                    text: 'Browser HP/tablet memblokir kamera pada koneksi HTTP non-localhost. Gunakan HTTPS atau input manual / scanner Bluetooth.'
                });
                return;
            }

            $('#camera-container').slideDown(200, function() {
                if (!html5QrcodeScanner) {
                    html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", {
                        fps: 10,
                        qrbox: function(viewfinderWidth, viewfinderHeight) {
                            var minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                            var boxSize = Math.max(150, Math.min(250, Math.floor(minEdge * 0.72)));
                            return { width: boxSize, height: boxSize };
                        },
                        aspectRatio: 1.0,
                        rememberLastUsedCamera: true,
                        videoConstraints: {
                            facingMode: { ideal: "environment" }
                        }
                    });
                    html5QrcodeScanner.render(onScanSuccess);
                } else {
                    try {
                        html5QrcodeScanner.resume();
                    } catch (e) {}
                }
            });
        });

        $('#btn-close-camera').click(function() {
            $('#camera-container').slideUp();
            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.pause(true);
                } catch (e) {}
            }
        });

        function onScanSuccess(decodedText) {
            try {
                html5QrcodeScanner.pause(true);
            } catch (e) {}
            $('#camera-container').slideUp();
            $('#scan-pack-input').val(decodedText);
            processScanPack(decodedText);
        }

        function escHtml(str) {
            if (str === null || str === undefined) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(String(str)));
            return div.innerHTML;
        }

        function escAttr(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }
    });
</script>