<?php
$ENABLE_MANAGE = has_permission('History_Costing.Manage');
$ENABLE_VIEW   = has_permission('History_Costing.View');
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<style>
    input.flatpickr-input.form-control { background-color: #fff !important; }
    .filter-select { min-width: 240px; }
    .filter-select-wide { min-width: 320px; }
</style>

<div class="container-fluid mt-3">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fa fa-history text-primary me-2"></i>History Costing</h5>
                <small class="text-muted">Catatan riwayat perubahan Bottom Price dan komponen biaya produk (Hanya Baca / Audit Trail).</small>
            </div>
            <?php if ($ENABLE_MANAGE): ?>
                <a href="<?= base_url('history_costing/export_excel?' . http_build_query($filters)) ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel-o me-1"></i> Ekspor Excel (CSV)
                </a>
            <?php endif; ?>
        </div>

        <!-- Filter Bar -->
        <div class="card-body border-bottom bg-light py-3">
            <form method="GET" action="<?= base_url('history_costing') ?>" class="row gy-2 gx-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label fw-bold mb-1"><i class="fa fa-filter text-muted me-1"></i>Kategori</label>
                    <select name="category" class="form-select form-select-sm select2-filter filter-select" data-placeholder="-- Semua Kategori --">
                        <option value="">-- Semua Kategori --</option>
                        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                            <option value="<?= html_escape($cat->code_lv2) ?>" <?= ($filters['code_lv2'] == $cat->code_lv2) ? 'selected' : '' ?>>
                                <?= html_escape($cat->nama) ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="col-auto">
                    <label class="form-label fw-bold mb-1">Batch Update</label>
                    <select name="batch_id" class="form-select form-select-sm select2-filter filter-select-wide" data-placeholder="-- Semua Batch --">
                        <option value="">-- Semua Batch --</option>
                        <?php if (!empty($batches)): foreach ($batches as $b): ?>
                            <option value="<?= html_escape($b->batch_id) ?>" <?= ($filters['batch_id'] == $b->batch_id) ? 'selected' : '' ?>>
                                <?= html_escape($b->batch_id) ?> (<?= date('d/m/Y H:i', strtotime($b->changed_at)) ?> - <?= $b->total_items ?> item)
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="col-auto">
                    <label class="form-label fw-bold mb-1">Tanggal</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" name="start_date" class="form-control form-control-sm flatpickr-date" placeholder="Dari tanggal" autocomplete="off" value="<?= html_escape($filters['start_date']) ?>">
                        <span>s/d</span>
                        <input type="text" name="end_date" class="form-control form-control-sm flatpickr-date" placeholder="Sampai tanggal" autocomplete="off" value="<?= html_escape($filters['end_date']) ?>">
                    </div>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search me-1"></i> Terapkan
                    </button>
                    <?php if (!empty($filters['code_lv2']) || !empty($filters['batch_id']) || !empty($filters['start_date']) || !empty($filters['end_date'])): ?>
                        <a href="<?= base_url('history_costing') ?>" class="btn btn-outline-secondary btn-sm ms-1">
                            <i class="fa fa-refresh me-1"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Main Table -->
        <div class="card-body">
            <div class="table-responsive">
                <table id="tbl-history-costing" class="table table-bordered table-striped table-hover align-middle" style="font-size:12px; width:100%;">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:4%;" class="text-center">No</th>
                            <th style="width:12%;" class="text-center">Waktu Perubahan</th>
                            <!-- FR-73: Nama produk di depan -->
                            <th style="width:20%;">Nama Produk</th>
                            <th style="width:10%;" class="text-center">Varian (Uniq ID)</th>
                            <th style="width:12%;">Kategori</th>
                            <th style="width:10%;" class="text-end">Bottom Lama (/m)</th>
                            <th style="width:10%;" class="text-end">Bottom Baru (/m)</th>
                            <th style="width:10%;" class="text-end">Selisih (Delta)</th>
                            <th style="width:10%;" class="text-end">Price List Baru (/m)</th>
                            <th style="width:8%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($list)): $no = 1; foreach ($list as $r):
                            $delta = (float)$r->bottom_price_baru_meter - (float)$r->bottom_price_lama_meter;
                            $pct = ($r->bottom_price_lama_meter > 0) ? ($delta / (float)$r->bottom_price_lama_meter) * 100 : 0;
                        ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center">
                                    <div class="fw-bold"><?= date('d/m/Y', strtotime($r->changed_at)) ?></div>
                                    <div class="text-muted" style="font-size:10px;"><?= date('H:i:s', strtotime($r->changed_at)) ?></div>
                                    <div class="text-muted" style="font-size:10px;">By: <?= html_escape($r->changed_by_name ? $r->changed_by_name : '-') ?></div>
                                </td>
                                <td>
                                    <span class="fw-bold"><?= html_escape($r->nm_product ? $r->nm_product : $r->code_lv4) ?></span>
                                    <div class="text-muted" style="font-size:10px;">Kode: <?= html_escape($r->code_lv4) ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary" style="font-size:11px;"><?= html_escape($r->no_uniq_id) ?></span>
                                </td>
                                <td><?= html_escape($r->nm_category ? $r->nm_category : '-') ?></td>
                                <td class="text-end">
                                    Rp <?= number_format((float)$r->bottom_price_lama_meter, 2) ?>
                                </td>
                                <td class="text-end fw-bold text-danger bg-danger-subtle">
                                    Rp <?= number_format((float)$r->bottom_price_baru_meter, 2) ?>
                                </td>
                                <td class="text-end fw-semibold <?= ($delta >= 0) ? 'text-danger' : 'text-success' ?>">
                                    <?= ($delta >= 0) ? '+' : '' ?>Rp <?= number_format($delta, 2) ?>
                                    <div style="font-size:10px;"><?= ($pct >= 0) ? '+' : '' ?><?= number_format($pct, 2) ?>%</div>
                                </td>
                                <td class="text-end fw-bold text-success bg-success-subtle">
                                    Rp <?= number_format((float)$r->price_list_baru_meter, 2) ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-info btn-sm text-white btn-view-snapshot" data-id="<?= $r->id ?>">
                                        <i class="fas fa-chart-pie me-1"></i> Rincian
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rincian Snapshot Komponen Biaya -->
<div class="modal fade" id="modal-snapshot" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white">
                <h5 class="modal-title fw-bold"><i class="fa fa-pie-chart me-2"></i>Snapshot Komponen Biaya (Lama vs Baru)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow: auto;">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0" style="font-size:12px;">
                            <tr><td style="width:120px;" class="fw-bold">Produk:</td><td id="snap-product">-</td></tr>
                            <tr><td class="fw-bold">Varian (Uniq ID):</td><td id="snap-uniq">-</td></tr>
                            <tr><td class="fw-bold">Batch ID:</td><td id="snap-batch">-</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0" style="font-size:12px;">
                            <tr><td style="width:120px;" class="fw-bold">Waktu Perubahan:</td><td id="snap-time">-</td></tr>
                            <tr><td class="fw-bold">Sumber Perubahan:</td><td id="snap-source">-</td></tr>
                            <tr><td class="fw-bold">Diperbarui Oleh:</td><td id="snap-user">-</td></tr>
                        </table>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="font-size:12px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30%;">Komponen Biaya (Rp/Meter)</th>
                                <th style="width:22%;" class="text-end">Nilai Lama</th>
                                <th style="width:22%;" class="text-end">Nilai Baru</th>
                                <th style="width:26%;" class="text-end">Perubahan (Selisih)</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-snapshot-body">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
$(document).ready(function() {
    // Select2 dengan fitur pencarian untuk filter
    $('.select2-filter').each(function() {
        $(this).select2({
            theme: 'bootstrap-5',
            width: 'style',
            allowClear: true,
            placeholder: $(this).data('placeholder')
        });
    });

    // Flatpickr untuk filter tanggal (nilai dikirim tetap Y-m-d)
    flatpickr('.flatpickr-date', {
        locale: 'id',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        allowInput: false
    });

    // Inisialisasi DataTables tanpa baris colspan manual
    $('#tbl-history-costing').DataTable({
        responsive: true,
        ordering: true,
        pageLength: 25,
        order: [[1, 'desc']], // Urutkan waktu perubahan terbaru
        columnDefs: [
            { orderable: false, targets: [0, 9] }
        ],
        language: {
            search: "Cari Produk / Varian / Batch:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada data riwayat costing",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ riwayat",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)"
        }
    });

    // Handle View Snapshot Detail
    $(document).on('click', '.btn-view-snapshot', function() {
        var id = $(this).data('id');

        $.ajax({
            url: "<?= base_url('history_costing/detail_snapshot') ?>",
            type: "GET",
            dataType: "JSON",
            data: { id: id },
            success: function(res) {
                if (res.status == 1) {
                    var r = res.row;
                    var s = res.snapshot;

                    $('#snap-product').text((r.nm_product ? r.nm_product : r.code_lv4) + ' (' + r.code_lv4 + ')');
                    $('#snap-uniq').html('<span class="badge bg-primary"></span>').find('span').text(r.no_uniq_id);
                    $('#snap-batch').text(r.batch_id);
                    $('#snap-time').text(r.changed_at);
                    $('#snap-source').text(r.change_source || '-');
                    $('#snap-user').text(r.changed_by_name || '-');

                    var oldVal = s ? s.old : null;
                    var newVal = s ? s.now : null;

                    var components = [
                        { label: "1. Biaya Material (Raw Material + Packing)", key: "material", bold: false },
                        { label: "2. Biaya Proses Total (DL + IDL + Depr + Listrik)", key: "proses", bold: false },
                        { label: "3. Manufacturing Support (BPK + FOH + Helper)", key: "support", bold: false },
                        { label: "4. Other Cost Total (5 Elemen)", key: "other", bold: false },
                        { label: "5. HPP (COGS)", key: "hpp", bold: true, color: "#2980b9" },
                        { label: "6. Selling Price: Bottom Price", key: "bottom", bold: true, color: "#c0392b" },
                        { label: "7. Selling Price: Price List", key: "priceList", bold: true, color: "#27ae60" }
                    ];

                    var fmt = {minimumFractionDigits: 2, maximumFractionDigits: 2};
                    var html = '';
                    components.forEach(function(comp) {
                        var vOld = oldVal ? parseFloat(oldVal[comp.key] || 0) : 0;
                        var vNew = newVal ? parseFloat(newVal[comp.key] || 0) : 0;
                        var diff = vNew - vOld;
                        var pct = (vOld > 0) ? (diff / vOld) * 100 : 0;

                        var rowStyle = comp.bold ? 'font-weight:bold; background:#fcfcfc;' : '';
                        var colorStyle = comp.color ? 'color:' + comp.color + ';' : '';

                        html += '<tr style="' + rowStyle + '">';
                        html += '<td style="' + colorStyle + '">' + comp.label + '</td>';
                        html += '<td class="text-end">Rp ' + vOld.toLocaleString('id-ID', fmt) + '</td>';
                        html += '<td class="text-end" style="' + colorStyle + '">Rp ' + vNew.toLocaleString('id-ID', fmt) + '</td>';

                        var diffColor = diff > 0 ? '#e74c3c' : (diff < 0 ? '#27ae60' : '#7f8c8d');
                        var sign = diff > 0 ? '+' : '';
                        html += '<td class="text-end" style="color:' + diffColor + '; font-weight:600;">' + sign + 'Rp ' + diff.toLocaleString('id-ID', fmt) + ' (' + sign + pct.toFixed(2) + '%)</td>';
                        html += '</tr>';
                    });

                    $('#tbl-snapshot-body').html(html);
                    $('#modal-snapshot').modal('show');
                } else {
                    Swal.fire('Error', res.pesan, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal memuat snapshot komponen biaya', 'error');
            }
        });
    });
});
</script>
