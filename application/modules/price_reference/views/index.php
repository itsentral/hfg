<?php
$ENABLE_VIEW = isset($ENABLE_VIEW) ? $ENABLE_VIEW : true;
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1 text-primary"><i class="ti ti-tags me-2"></i>Price Reference (Raw Materials)</h5>
                    <p class="text-muted mb-0 small">
                        Daftar harga acuan material untuk Product Costing. Nilai Price Reference USD diambil dari nilai tertinggi (Max) pada History Pembelian / PO, dan dikonversi ke Rupiah menggunakan Kurs USD aktif.
                    </p>
                </div>
                <div class="mt-2 mt-md-0">
                    <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                        <i class="ti ti-currency-dollar text-success me-1"></i>Kurs USD Aktif: <strong>Rp <?= number_format($usd_kurs, 2, ',', '.') ?></strong>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbl-price-reference" class="table table-striped table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th style="width: 140px;">Material Code</th>
                        <th>Material Master</th>
                        <th class="text-end" style="width: 180px;">Price Ref IDR (Rp/Kg)</th>
                        <th class="text-end" style="width: 180px;">Price Ref USD (Max)</th>
                        <th class="text-center" style="width: 120px;">Status</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($materials)): ?>
                        <?php $no = 1;
                        foreach ($materials as $m): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="fw-bold"><?= htmlspecialchars(strtoupper($m->code ?: $m->code_lv4)) ?></td>
                                <td><?= htmlspecialchars(strtoupper($m->nama)) ?></td>
                                <td class="text-end font-monospace">
                                    <?= ((float)$m->price_ref_use > 0) ? 'Rp ' . number_format($m->price_ref_use, 2, ',', '.') : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-end font-monospace">
                                    <?= ((float)$m->price_ref_use_usd > 0) ? '$ ' . number_format($m->price_ref_use_usd, 4, '.', ',') : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $m->status_badge ?> px-2 py-1"><?= htmlspecialchars($m->status_label) ?></span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info btn-open-modal"
                                        data-id="<?= $m->id ?>"
                                        title="View Detail Price Reference">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Price Reference Detail (History Pembelian & Kalkulasi) -->
<div class="modal fade" id="modal-price-ref" tabindex="-1" aria-labelledby="modalPriceRefLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title text-white" id="modalPriceRefLabel">
                    <i class="ti ti-tags me-1"></i> Detail Price Reference — <span id="modal_mat_nama" class="fw-bold"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" style="max-height: 70vh; overflow: auto;">
                <!-- Alert Not Set jika belum ada harga -->
                <div id="alert_not_set" class="alert alert-warning py-2 mb-3 d-none">
                    <i class="ti ti-alert-triangle me-1"></i> <strong>Perhatian:</strong> Price Reference belum ada untuk material ini.
                </div>

                <!-- Material Info -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body py-3">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small mb-1">Material ID</label>
                                <input type="text" id="m_code" class="form-control form-control-sm bg-white" readonly>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-bold text-muted small mb-1">Material Master</label>
                                <input type="text" id="m_nama" class="form-control form-control-sm bg-white" readonly>
                            </div>
                            <div class="col-md-2 text-md-center">
                                <label class="form-label fw-bold text-muted small mb-1 d-block">Status</label>
                                <span id="m_badge_status" class="badge bg-warning px-3 py-2 fs-6">Not Set</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1. History Pembelian (USD/kg) -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="fw-bold text-dark mb-0">
                            <span class="badge bg-primary rounded-circle me-1">1</span> History Pembelian (USD/kg)
                        </h6>
                        <span class="text-muted small">Nilai tertinggi (Max) otomatis dipakai sebagai acuan costing</span>
                    </div>
                    <p class="text-muted small mb-2">
                        Daftar harga per bulan dari History Pembelian. Yang dipakai untuk Product Costing adalah harga <strong>tertinggi (Max)</strong>; nilai Terakhir dan Rata-rata hanya sebagai informasi pelengkap.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-nowrap mb-0" id="tbl-history-display">
                            <thead class="text-center small table-light">
                                <tr>
                                    <th style="min-width: 200px;">Material Name</th>
                                    <th style="width: 90px;">Januari</th>
                                    <th style="width: 90px;">Februari</th>
                                    <th style="width: 90px;">Maret</th>
                                    <th style="width: 90px;">April</th>
                                    <th style="width: 90px;">Mei</th>
                                    <th style="width: 90px;">Juni</th>
                                    <th style="width: 90px;">Juli</th>
                                    <th style="width: 90px;">Agustus</th>
                                    <th style="width: 90px;">September</th>
                                    <th style="width: 90px;">Oktober</th>
                                    <th style="width: 90px;">November</th>
                                    <th style="width: 90px;">Desember</th>
                                    <th style="width: 110px;">Terakhir (info)</th>
                                    <th style="width: 110px;">Rata-rata (info)</th>
                                    <th class="fw-bold" style="width: 120px;">Max (dipakai)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="cell_mat_name" class="fw-bold small text-truncate" style="max-width: 220px;">-</td>
                                    <?php for ($bln = 1; $bln <= 12; $bln++): ?>
                                        <td class="text-end font-monospace" id="month_<?= $bln ?>">-</td>
                                    <?php endfor; ?>
                                    <td class="text-end font-monospace text-muted bg-light" id="disp_calc_latest">0.0000</td>
                                    <td class="text-end font-monospace text-muted bg-light" id="disp_calc_avg">0.0000</td>
                                    <td class="text-end font-monospace fw-bold text-primary bg-primary bg-opacity-10 fs-6" id="disp_calc_max">0.0000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Price Reference (Perhitungan Kurs ke IDR) -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="fw-bold text-dark mb-0">
                            <span class="badge bg-primary rounded-circle me-1">2</span> Price Reference Calculation
                        </h6>
                        <span class="text-muted small">Rumus: <strong>Price Reference (Rp/Kg) = Max (USD/Kg) × Kurs USD</strong></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-nowrap mb-0">
                            <thead class="table-light text-center small">
                                <tr>
                                    <th>Material Name</th>
                                    <th class="text-end" style="width: 200px;">Price Reference Max (USD/Kg)</th>
                                    <th class="text-end" style="width: 200px;">Kurs USD (Rp)</th>
                                    <th class="text-end table-success fw-bold" style="width: 220px;">Price Reference (Rp/Kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="cell_mat_name_calc" class="fw-bold small">-</td>
                                    <td class="text-end font-monospace fw-bold" id="box_max_usd">$ 0.0000</td>
                                    <td class="text-end font-monospace" id="box_kurs">Rp <?= number_format($usd_kurs, 2, ',', '.') ?></td>
                                    <td class="text-end font-monospace fw-bold text-success bg-success bg-opacity-10 fs-6" id="box_price_idr">Rp 0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Riwayat Pembelian dari PO Sistem (dt_trans_po) -->
                <div class="accordion" id="accPoHistory">
                    <div class="accordion-item border">
                        <h2 class="accordion-header" id="headingPo">
                            <button class="accordion-button collapsed py-2 bg-light text-dark small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePo" aria-expanded="false" aria-controls="collapsePo">
                                <i class="ti ti-history me-2 text-primary"></i> Riwayat Transaksi PO Sistem (<span id="po_count">0</span> PO ditemukan)
                            </button>
                        </h2>
                        <div id="collapsePo" class="accordion-collapse collapse" aria-labelledby="headingPo" data-bs-parent="#accPoHistory">
                            <div class="accordion-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped table-hover mb-0 small" id="tbl_po_list">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No. PO</th>
                                                <th>Tanggal</th>
                                                <th>Supplier</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Harga Satuan (USD/Kg)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="po_rows">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-2">Memuat riwayat PO...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi DataTable Depan
        var dataTable = $('#tbl-price-reference').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [
                [2, 'asc']
            ], // urut nama material
            columnDefs: [{
                targets: [0, 6],
                orderable: false
            }]
        });

        var currentKurs = <?= (float)$usd_kurs ?>;

        // Helper format angka
        function formatUSD(num) {
            return parseFloat(num || 0).toLocaleString('en-US', {
                minimumFractionDigits: 4,
                maximumFractionDigits: 4
            });
        }

        function formatIDR(num) {
            return 'Rp ' + parseFloat(num || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Buka Modal View Detail
        $(document).on('click', '.btn-open-modal', function() {
            var id = $(this).data('id');

            // Reset tampilan modal
            $('#alert_not_set').addClass('d-none');
            $('#po_rows').html('<tr><td colspan="5" class="text-center text-muted py-2"><i class="fas fa-spinner fa-spin me-1"></i> Memuat riwayat...</td></tr>');
            for (var b = 1; b <= 12; b++) {
                $('#month_' + b).text('-');
            }
            $('#disp_calc_latest').text('0.0000');
            $('#disp_calc_avg').text('0.0000');
            $('#disp_calc_max').text('0.0000');
            $('#box_max_usd').text('$ 0.0000');
            $('#box_price_idr').text('Rp 0.00');

            // Ambil Data via AJAX
            $.ajax({
                url: '<?= base_url('price_reference/get_detail/') ?>' + id,
                type: 'GET',
                dataType: 'json',
                success: function(resp) {
                    if (resp.status !== 1) {
                        Swal.fire('Error', resp.pesan || 'Gagal memuat detail', 'error');
                        return;
                    }

                    var mat = resp.material;
                    var hist = resp.history;
                    var po = resp.po_history;
                    var calc = resp.calc;

                    currentKurs = parseFloat(calc.kurs || <?= (float)$usd_kurs ?>);
                    $('#box_kurs').text('Rp ' + currentKurs.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));

                    $('#m_code').val(mat.code);
                    $('#m_nama').val(mat.nama);
                    $('#modal_mat_nama, #cell_mat_name, #cell_mat_name_calc').text(mat.nama);

                    $('#m_badge_status').removeClass('bg-warning bg-success bg-danger')
                        .addClass('bg-' + mat.status_badge).text(mat.status_label);

                    // Populate 12 bulan (Jan-Des)
                    for (var b = 1; b <= 12; b++) {
                        var val = (hist && hist[b] !== undefined && hist[b] !== null) ? parseFloat(hist[b]) : null;
                        if (val !== null && val > 0) {
                            $('#month_' + b).text(formatUSD(val));
                        } else {
                            $('#month_' + b).html('<span class="text-muted">—</span>');
                        }
                    }

                    // Tampilkan kalkulasi info
                    $('#disp_calc_latest').text(formatUSD(calc.latest_usd));
                    $('#disp_calc_avg').text(formatUSD(calc.avg_usd));
                    $('#disp_calc_max').text(formatUSD(calc.max_usd));

                    // Tampilkan kalkulasi final
                    $('#box_max_usd').text('$ ' + formatUSD(calc.max_usd));
                    $('#box_price_idr').text(formatIDR(calc.price_ref_idr));

                    if (parseFloat(calc.max_usd) > 0) {
                        $('#alert_not_set').addClass('d-none');
                    } else {
                        $('#alert_not_set').removeClass('d-none');
                    }

                    // Populate Riwayat PO
                    $('#po_count').text(po.length);
                    if (po.length > 0) {
                        var htmlPo = '';
                        po.forEach(function(item) {
                            var hrgUsd = parseFloat(item.harga_usd || 0);
                            htmlPo += '<tr>' +
                                '<td><strong>' + (item.no_po || '-') + '</strong></td>' +
                                '<td>' + (item.tanggal || '-') + '</td>' +
                                '<td>' + (item.nm_supplier || '-') + '</td>' +
                                '<td class="text-end font-monospace">' + parseFloat(item.qty || 0).toLocaleString('id-ID') + '</td>' +
                                '<td class="text-end font-monospace fw-bold text-success">$ ' + formatUSD(hrgUsd) + '</td>' +
                                '</tr>';
                        });
                        $('#po_rows').html(htmlPo);
                    } else {
                        $('#po_rows').html('<tr><td colspan="5" class="text-center text-muted py-2">Belum ada riwayat PO di sistem untuk material ini.</td></tr>');
                    }

                    $('#modal-price-ref').modal('show');
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Gagal menghubungi server (' + xhr.status + ')', 'error');
                }
            });
        });
    });
</script>
