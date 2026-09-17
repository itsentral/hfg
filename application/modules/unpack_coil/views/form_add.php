<style>
    .swal2-container {
        z-index: 999999 !important;
    }

    .swal2-popup {
        z-index: 1000000 !important;
    }

    th.actual-head {
        background-color: #cfe2ff;
    }

    th.pl-head {
        background-color: #fff3cd;
    }

    th.selisih-head {
        background-color: #d1e7dd;
    }

    .table-unpack td,
    .table-unpack th {
        vertical-align: middle;
    }

    .select2-container {
        width: 100% !important;
    }

    /* Untuk Chrome, Safari, Edge, dan Opera */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Untuk Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<?php
$is_edit = ($mode === 'edit');
$ex      = $existing;
?>

<div class="card">
    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Pilih Pack (Material Confirmed)</label>
                <select id="select-pack" class="form-control" <?= $is_edit ? 'disabled' : '' ?>></select>
                <small class="text-muted">1 report unpack = 1 pack.</small>
            </div>
        </div>

        <!-- ===== TAHAP 1: LEVEL PACK ===== -->
        <div id="pack-level-wrapper" style="display:none;">
            <h6 class="fw-bold mb-2">Ringkasan Pack</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm text-center align-middle table-unpack">
                    <thead>
                        <tr>
                            <th rowspan="2">Kulit</th>
                            <th rowspan="2">Clamp / ring</th>
                            <th colspan="2" class="actual-head">Actual Weight</th>
                            <th colspan="2" class="pl-head">Packing List</th>
                            <th colspan="2" class="selisih-head">Selisih Actual vs PL</th>
                        </tr>
                        <tr>
                            <th class="actual-head">Nett Weight</th>
                            <th class="actual-head">Gross Weight</th>
                            <th class="pl-head">Nett Weight</th>
                            <th class="pl-head">Gross Weight</th>
                            <th class="selisih-head">Nett</th>
                            <th class="selisih-head">Gross</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" id="pack-kulit" class="form-control form-control-sm"></td>
                            <td><input type="text" id="pack-clamp" class="form-control form-control-sm"></td>
                            <td><input type="number" step="0.01" id="pack-net-actual" class="form-control form-control-sm text-end" value="0"></td>
                            <td><input type="number" step="0.01" id="pack-gross-actual" class="form-control form-control-sm text-end" value="0"></td>
                            <td class="text-end"><span id="pack-net-pl">0.00</span></td>
                            <td class="text-end"><span id="pack-gross-pl">0.00</span></td>
                            <td class="text-end"><span id="pack-net-selisih">0.00</span></td>
                            <td class="text-end"><span id="pack-gross-selisih">0.00</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ===== TAHAP 2: PER MATERIAL ===== -->
            <h6 class="fw-bold mb-2">Material dalam Pack</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-unpack" id="table-material">
                    <thead class="text-center align-middle">
                        <tr>
                            <th rowspan="2" width="4%">No</th>
                            <th rowspan="2">Pack</th>
                            <th rowspan="2">Material</th>
                            <th rowspan="2">No Coil</th>
                            <th rowspan="2" width="10%">Jumlah Coil (Roll Baby Coil)</th>
                            <th colspan="2" class="actual-head">Actual Weight</th>
                            <th colspan="2" class="pl-head">Packing List</th>
                            <th rowspan="2" width="8%">Aksi</th>
                        </tr>
                        <tr>
                            <th class="actual-head">Net weight Total Actual</th>
                            <th class="actual-head">Gross Weight Total Actual</th>
                            <th class="pl-head">Net weight Total</th>
                            <th class="pl-head">Gross Weight Total</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-material">
                        <tr id="empty-row">
                            <td colspan="10" class="text-center text-muted py-4">Pilih pack untuk memuat material.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-3 mt-3">
            <label class="form-label fw-bold">Catatan</label>
            <textarea id="catatan" class="form-control" rows="2" placeholder="Opsional"></textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="<?= site_url('unpack_coil') ?>" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
            <button type="button" class="btn btn-success" id="btn-save"><i class="fa fa-save me-1"></i> Save</button>
        </div>
    </div>
</div>

<!-- Modal Detail Baby Coil -->
<div class="modal fade" id="modal-detail" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-list"></i> Detail Baby Coil <span id="modal-title"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height:70vh; overflow:auto;" id="modal-body-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center align-middle" id="table-baby">
                        <thead>
                            <tr>
                                <th width="4%">No</th>
                                <th>No Coil</th>
                                <th>Babycoil</th>
                                <th class="actual-head">Net weight per Roll (Actual)</th>
                                <th class="actual-head">Gross Weight per Roll (Actual)</th>
                                <th class="pl-head">Net weight per Roll (PL)</th>
                                <th class="pl-head">Gross Weight per Roll (PL)</th>
                                <th width="6%">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-baby"></tbody>
                        <tfoot>
                            <tr class="fw-bold table-light">
                                <td colspan="3" class="text-end">Total</td>
                                <td id="foot-net-actual">0.00</td>
                                <td id="foot-gross-actual">0.00</td>
                                <td id="foot-net-pl">0.00</td>
                                <td id="foot-gross-pl">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    var MODE = <?= json_encode($mode) ?>;
    var UNPACK_NO = <?= json_encode($unpack_no) ?>;
    var EXISTING = <?= json_encode($ex) ?>;

    $(document).ready(function() {
        const BASE_URL = siteurl + active_controller;

        var currentPack = null; // { id_pack, pack_code, request_id }
        var materials = {}; // key(id_material|id_gudang) -> material obj (with babies[])

        function fmt(n) {
            return (parseFloat(n) || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // ---------------- SELECT2 pack ----------------
        if (MODE === 'add') {
            $('#select-pack').select2({
                placeholder: 'Cari & pilih pack...',
                ajax: {
                    url: BASE_URL + '/get_confirmed_packs',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function(res) {
                        return {
                            results: (res.results || [])
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });

            $('#select-pack').on('select2:select', function(e) {
                var d = e.params.data.data;
                currentPack = {
                    id_pack: d.id_pack,
                    pack_code: d.pack_code,
                    request_id: d.request_id
                };
                loadPackMaterials(d.id_pack);
            });
        }

        // ---------------- Load material dalam pack (grouped id_material + id_gudang) ----------------
        function loadPackMaterials(id_pack) {
            $.ajax({
                url: BASE_URL + '/get_pack_materials/' + id_pack,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status != 1) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message || 'Gagal memuat material.'
                        });
                        return;
                    }
                    materials = {};
                    res.data.forEach(function(m) {
                        var key = m.id_material + '|' + m.id_gudang;
                        materials[key] = {
                            id_material: m.id_material,
                            id_gudang: m.id_gudang,
                            kd_gudang: m.kd_gudang,
                            no_coil: m.no_coil,
                            kode_internal_sample: m.kode_internal_sample,
                            nm_material: m.nm_material,
                            net_weight_pl: parseFloat(m.net_weight_pl) || 0,
                            gross_weight_pl: parseFloat(m.gross_weight_pl) || 0,
                            jumlah_coil: parseInt(m.jumlah_coil) || 0,
                            jumlah_coil_adjusted: null,
                            babies: []
                        };
                    });
                    $('#pack-level-wrapper').show();
                    renderMaterialTable();
                    recalcPackLevel();
                }
            });
        }

        // ---------------- Helper: total coil & actual per-coil (dibagi rata seluruh pack) ----------------
        function getTotalJumlahCoilAll() {
            var total = 0;
            Object.keys(materials).forEach(function(key) {
                var m = materials[key];
                var n = (m.jumlah_coil_adjusted != null) ? m.jumlah_coil_adjusted : m.jumlah_coil;
                total += (parseInt(n) || 0);
            });
            return total;
        }

        function getPerCoilActual() {
            var totalCoil = getTotalJumlahCoilAll();
            var netAct = parseFloat($('#pack-net-actual').val()) || 0;
            var grossAct = parseFloat($('#pack-gross-actual').val()) || 0;
            return {
                net: totalCoil > 0 ? (netAct / totalCoil) : 0,
                gross: totalCoil > 0 ? (grossAct / totalCoil) : 0,
                totalCoil: totalCoil
            };
        }

        // update net/gross actual pada babies yg sudah ada tiap kali actual weight / jumlah coil berubah
        function refreshBabiesActual() {
            var per = getPerCoilActual();
            Object.keys(materials).forEach(function(key) {
                var m = materials[key];
                (m.babies || []).forEach(function(b) {
                    b.net_weight_actual = per.net;
                    b.gross_weight_actual = per.gross;
                });
            });
        }

        // ---------------- Render tabel material (kolom No, Pack, Actual, PL, Aksi digabung/rowspan) ----------------
        function renderMaterialTable() {
            var keys = Object.keys(materials);
            var $tb = $('#tbody-material').empty();

            if (keys.length === 0) {
                $tb.html('<tr id="empty-row"><td colspan="10" class="text-center text-muted py-4">Tidak ada material dalam pack ini.</td></tr>');
                return;
            }

            var netPl = 0,
                grossPl = 0;
            keys.forEach(function(key) {
                netPl += materials[key].net_weight_pl;
                grossPl += materials[key].gross_weight_pl;
            });
            var netActual = parseFloat($('#pack-net-actual').val()) || 0;
            var grossActual = parseFloat($('#pack-gross-actual').val()) || 0;
            var rowspan = keys.length;

            keys.forEach(function(key, i) {
                var m = materials[key];
                var displayCount = (m.jumlah_coil_adjusted != null) ? m.jumlah_coil_adjusted : m.jumlah_coil;

                var tr = '<tr data-key="' + key + '">';

                if (i === 0) {
                    tr += '<td class="text-center" rowspan="' + rowspan + '">1</td>' +
                        '<td rowspan="' + rowspan + '"><span class="badge bg-primary">' + currentPack.pack_code + '</span></td>';
                }

                tr += '<td>' + (m.nm_material || '') + '</td>' +
                    '<td><span title="' + (m.no_coil || '') + '" style="display:inline-block;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + (m.no_coil || '') + '</span></td>' +
                    '<td class="text-center">' +
                    '<input type="number" min="0" class="form-control form-control-sm text-center jumlah-coil-input" ' +
                    'data-key="' + key + '" value="' + displayCount + '" style="width:70px;margin:0 auto;">' +
                    '</td>';

                if (i === 0) {
                    tr += '<td class="text-end" rowspan="' + rowspan + '">' + fmt(netActual) + '</td>' +
                        '<td class="text-end" rowspan="' + rowspan + '">' + fmt(grossActual) + '</td>' +
                        '<td class="text-end" rowspan="' + rowspan + '">' + fmt(netPl) + '</td>' +
                        '<td class="text-end" rowspan="' + rowspan + '">' + fmt(grossPl) + '</td>' +
                        '<td class="text-center" rowspan="' + rowspan + '">' +
                        '<button type="button" class="btn btn-sm btn-info" id="btn-view-detail-all"><i class="fa fa-eye"></i> Detail</button>' +
                        '</td>';
                }

                tr += '</tr>';
                $tb.append(tr);
            });
        }

        // jumlah coil di-adjust manual oleh user
        $(document).on('change', '.jumlah-coil-input', function() {
            var key = $(this).data('key');
            var m = materials[key];
            if (!m) return;

            var n = parseInt($(this).val());
            if (isNaN(n) || n < 0) n = 0;

            m.jumlah_coil_adjusted = n;
            m.babies = []; // reset, akan digenerate ulang saat modal dibuka / saat save
            refreshBabiesWeights();
        });

        // ---------------- Recalc level pack ----------------
        function recalcPackLevel() {
            var netPl = 0,
                grossPl = 0;
            Object.keys(materials).forEach(function(key) {
                netPl += materials[key].net_weight_pl;
                grossPl += materials[key].gross_weight_pl;
            });
            $('#pack-net-pl').text(fmt(netPl));
            $('#pack-gross-pl').text(fmt(grossPl));

            var netAct = parseFloat($('#pack-net-actual').val()) || 0;
            var grossAct = parseFloat($('#pack-gross-actual').val()) || 0;
            $('#pack-net-selisih').text(fmt(netAct - netPl));
            $('#pack-gross-selisih').text(fmt(grossAct - grossPl));
        }

        $('#pack-net-actual, #pack-gross-actual').on('input', function() {
            recalcPackLevel();
            refreshBabiesWeights();
            renderMaterialTable(); // refresh angka Actual Weight yang digabung
        });

        // ---------------- Ambil / generate baby coil per material ----------------
        function loadBabiesForMaterial(m, n) {
            var deferred = $.Deferred();
            var isAdjusted = (m.jumlah_coil_adjusted != null && m.jumlah_coil_adjusted !== m.jumlah_coil);

            if (!isAdjusted) {
                $.ajax({
                    url: BASE_URL + '/get_material_baby/' + currentPack.id_pack + '/' + encodeURIComponent(m.id_material) + '/' + m.id_gudang,
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == 1 && res.babies && res.babies.length > 0) {
                            deferred.resolve(res.babies);
                        } else {
                            deferred.resolve(generatePlaceholderRows(m, n));
                        }
                    },
                    error: function() {
                        deferred.resolve(generatePlaceholderRows(m, n));
                    }
                });
            } else {
                deferred.resolve(generatePlaceholderRows(m, n));
            }
            return deferred.promise();
        }

        function getKodeInternalBase(m) {
            var sample = m.kode_internal_sample || m.no_coil || m.id_material;
            // buang suffix urutan di belakang, contoh "ZBO-LYBB0007-001" -> "ZBO-LYBB0007"
            return String(sample).replace(/[-.]\d{2,4}$/, '');
        }

        function generatePlaceholderRows(m, n) {
            var rows = [];
            var nwAvg = n > 0 ? (m.net_weight_pl / n) : 0;
            var gwAvg = n > 0 ? (m.gross_weight_pl / n) : 0;
            var baseNoCoil = m.no_coil || m.id_material;
            var baseKode = getKodeInternalBase(m);

            for (var i = 1; i <= n; i++) {
                rows.push({
                    no_coil: baseNoCoil,
                    babycoil_code: baseKode + '-' + String(i).padStart(3, '0'), // mengikuti format DB: ZBO-LYBB0007-001
                    net_weight_pl: nwAvg,
                    gross_weight_pl: gwAvg
                });
            }
            return rows;
        }

        // analog getPerCoilActual(), tapi untuk PL
        function getPerCoilPL() {
            var totalCoil = getTotalJumlahCoilAll();
            var netPlTotal = 0,
                grossPlTotal = 0;
            Object.keys(materials).forEach(function(key) {
                netPlTotal += materials[key].net_weight_pl;
                grossPlTotal += materials[key].gross_weight_pl;
            });
            return {
                net: totalCoil > 0 ? (netPlTotal / totalCoil) : 0,
                gross: totalCoil > 0 ? (grossPlTotal / totalCoil) : 0
            };
        }

        // gabungkan refresh actual + PL jadi satu, dipanggil tiap ada perubahan input actual maupun jumlah coil
        function refreshBabiesWeights() {
            var perActual = getPerCoilActual();
            var perPL = getPerCoilPL();
            Object.keys(materials).forEach(function(key) {
                var m = materials[key];
                (m.babies || []).forEach(function(b) {
                    b.net_weight_actual = perActual.net;
                    b.gross_weight_actual = perActual.gross;
                    b.net_weight_pl = perPL.net;
                    b.gross_weight_pl = perPL.gross;
                });
            });
        }

        // ---------------- Pastikan SEMUA material sudah punya babies (dipakai oleh View Detail & Save) ----------------
        function ensureAllBabiesGenerated() {
            var perActual = getPerCoilActual();
            var perPL = getPerCoilPL();
            var keys = Object.keys(materials);

            var promises = keys.map(function(key) {
                var m = materials[key];
                var n = (m.jumlah_coil_adjusted != null) ? m.jumlah_coil_adjusted : m.jumlah_coil;

                if (m.babies && m.babies.length === n && n > 0) {
                    m.babies.forEach(function(b) {
                        b.net_weight_actual = perActual.net;
                        b.gross_weight_actual = perActual.gross;
                        b.net_weight_pl = perPL.net;
                        b.gross_weight_pl = perPL.gross;
                    });
                    return $.Deferred().resolve().promise();
                }

                return loadBabiesForMaterial(m, n).then(function(rawBabies) {
                    m.babies = rawBabies.map(function(b) {
                        return {
                            no_coil: m.no_coil || b.no_coil,
                            babycoil_code: b.babycoil_code,
                            net_weight_pl: perPL.net, // dari total pack, bukan m.net_weight_pl lagi
                            gross_weight_pl: perPL.gross, // idem
                            net_weight_actual: perActual.net,
                            gross_weight_actual: perActual.gross
                        };
                    });
                });
            });

            return $.when.apply($, promises);
        }

        // ---------------- Tombol View Detail (gabungan semua material) ----------------
        $(document).on('click', '#btn-view-detail-all', function() {
            ensureAllBabiesGenerated().then(function() {
                renderCombinedModalBody();
                new bootstrap.Modal(document.getElementById('modal-detail')).show();
            });
        });

        function renderCombinedModalBody() {
            var $body = $('#modal-body-content').empty();

            function numVal(n) {
                return (parseFloat(n) || 0).toFixed(2);
            }

            var keys = Object.keys(materials);
            var sumNetPl = 0,
                sumGrossPl = 0,
                sumNetAct = 0,
                sumGrossAct = 0;

            var html = '<div class="table-responsive"><table class="table table-bordered table-sm text-center align-middle">' +
                '<thead><tr>' +
                '<th width="4%">No</th><th>Material</th><th>No Coil</th><th>Babycoil</th>' +
                '<th class="actual-head">Net weight per Roll (Actual)</th>' +
                '<th class="actual-head">Gross Weight per Roll (Actual)</th>' +
                '<th class="pl-head">Net weight per Roll (PL)</th>' +
                '<th class="pl-head">Gross Weight per Roll (PL)</th>' +
                '</tr></thead><tbody>';

            keys.forEach(function(key, matIdx) {
                var m = materials[key];
                var babies = m.babies || [];
                var rowspan = babies.length;

                babies.forEach(function(b, idx) {
                    sumNetPl += b.net_weight_pl;
                    sumGrossPl += b.gross_weight_pl;
                    sumNetAct += b.net_weight_actual;
                    sumGrossAct += b.gross_weight_actual;

                    html += '<tr>';

                    if (idx === 0) {
                        html += '<td class="text-center" rowspan="' + rowspan + '">' + (matIdx + 1) + '</td>' +
                            '<td rowspan="' + rowspan + '">' + (m.nm_material || '') + '</td>';
                    }

                    html += '<td><input type="text" class="form-control form-control-sm" value="' + (b.no_coil || '') + '" readonly></td>' +
                        '<td><input type="text" class="form-control form-control-sm" value="' + (b.babycoil_code || '') + '" readonly></td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm text-end" value="' + numVal(b.net_weight_actual) + '" disabled></td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm text-end" value="' + numVal(b.gross_weight_actual) + '" disabled></td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm text-end" value="' + numVal(b.net_weight_pl) + '" readonly></td>' +
                        '<td><input type="number" step="0.01" class="form-control form-control-sm text-end" value="' + numVal(b.gross_weight_pl) + '" readonly></td>' +
                        '</tr>';
                });
            });

            html += '</tbody><tfoot><tr class="fw-bold table-light">' +
                '<td colspan="4" class="text-end">Total</td>' +
                '<td>' + fmt(sumNetAct) + '</td>' +
                '<td>' + fmt(sumGrossAct) + '</td>' +
                '<td>' + fmt(sumNetPl) + '</td>' +
                '<td>' + fmt(sumGrossPl) + '</td>' +
                '</tr></tfoot></table></div>';

            $body.append(html);
        }

        // ---------------- SAVE / UPDATE ----------------
        $('#btn-save').on('click', function() {
            if (!currentPack) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih pack dulu'
                });
                return;
            }

            var keys = Object.keys(materials);
            if (keys.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum ada material',
                    text: 'Pack ini tidak memiliki material.'
                });
                return;
            }

            var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spin fa-spinner"></i> Menyimpan...');

            ensureAllBabiesGenerated().then(function() {
                // validasi: pastikan tiap material benar-benar punya babies sebelum submit
                var invalid = keys.filter(function(k) {
                    return !materials[k].babies || materials[k].babies.length === 0;
                });
                if (invalid.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Detail belum lengkap',
                        text: 'Detail baby coil untuk material ' + invalid.join(', ') + ' gagal dimuat. Coba klik "View Detail" secara manual.'
                    });
                    $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
                    return;
                }

                // Tambahan: Alert konfirmasi sebelum data dikirim
                Swal.fire({
                    title: 'Konfirmasi Penyimpanan',
                    text: 'Apakah data yang dimasukkan sudah betul?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        // Jika dibatalkan, kembalikan tombol ke keadaan semula
                        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
                        return;
                    }

                    var matArr = keys.map(function(k) {
                        var m = materials[k];
                        return {
                            id_material: m.id_material,
                            id_gudang: m.id_gudang,
                            jumlah_coil: (m.jumlah_coil_adjusted != null) ? m.jumlah_coil_adjusted : m.jumlah_coil,
                            babies: m.babies
                        };
                    });

                    var payload = {
                        unpack_no: UNPACK_NO,
                        id_pack: currentPack.id_pack,
                        pack_code: currentPack.pack_code,
                        request_id: currentPack.request_id,
                        pack_net_actual: parseFloat($('#pack-net-actual').val()) || 0,
                        pack_gross_actual: parseFloat($('#pack-gross-actual').val()) || 0,
                        pack_net_pl: parseFloat(($('#pack-net-pl').text() || '0').replace(/,/g, '')) || 0,
                        pack_gross_pl: parseFloat(($('#pack-gross-pl').text() || '0').replace(/,/g, '')) || 0,
                        catatan_kulit: $('#pack-kulit').val(),
                        catatan_clamp_ring: $('#pack-clamp').val(),
                        catatan: $('#catatan').val(),
                        materials: matArr
                    };

                    var url = (MODE === 'edit') ? (BASE_URL + '/update') : (BASE_URL + '/save');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: payload,
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: res.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    })
                                    .then(function() {
                                        window.location.href = BASE_URL;
                                    });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: res.message
                                });
                                $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan jaringan.'
                            });
                            $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
                        }
                    });
                });

            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menyiapkan detail baby coil.'
                });
                $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
            });
        });

        // ---------------- MODE EDIT: prefill ----------------
        if (MODE === 'edit' && EXISTING) {
            var h = EXISTING.header;
            currentPack = {
                id_pack: h.id_pack,
                pack_code: h.pack_code,
                request_id: h.request_id
            };

            var opt = new Option(h.pack_code, h.id_pack, true, true);
            $('#select-pack').append(opt).trigger('change');

            $('#pack-kulit').val(h.catatan_kulit || '');
            $('#pack-clamp').val(h.catatan_clamp_ring || '');
            $('#pack-net-actual').val(h.net_weight_actual || 0);
            $('#pack-gross-actual').val(h.gross_weight_actual || 0);
            $('#catatan').val(h.catatan || '');

            materials = {};
            (EXISTING.materials || []).forEach(function(m) {
                var key = m.id_material + '|' + m.id_gudang;
                materials[key] = {
                    id_material: m.id_material,
                    id_gudang: m.id_gudang,
                    kd_gudang: m.kd_gudang,
                    no_coil: m.no_coil,
                    nm_material: m.nm_material,
                    net_weight_pl: parseFloat(m.net_weight_pl) || 0,
                    gross_weight_pl: parseFloat(m.gross_weight_pl) || 0,
                    jumlah_coil: (m.babies || []).length,
                    jumlah_coil_adjusted: null,
                    babies: (m.babies || []).map(function(b) {
                        return {
                            babycoil_code: b.babycoil_code,
                            no_coil: b.no_coil,
                            net_weight_actual: parseFloat(b.net_weight_actual) || 0,
                            gross_weight_actual: parseFloat(b.gross_weight_actual) || 0,
                            net_weight_pl: parseFloat(b.net_weight_pl) || 0,
                            gross_weight_pl: parseFloat(b.gross_weight_pl) || 0
                        };
                    })
                };
            });

            $('#pack-level-wrapper').show();
            renderMaterialTable();
            recalcPackLevel();
        }
    });
</script>