<?php
$ENABLE_MANAGE = has_permission('History_Costing.Manage');
$ENABLE_VIEW   = has_permission('History_Costing.View');
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="box-title"><i class="fa fa-history text-primary"></i> History Costing</h3>
                <p class="text-muted mb-0" style="font-size:12px;">Catatan riwayat perubahan Bottom Price dan komponen biaya produk (Hanya Baca / Audit Trail).</p>
            </div>
            <div class="col-md-6 text-right">
                <?php if ($ENABLE_MANAGE): ?>
                    <a href="<?= base_url('history_costing/export_excel?' . http_build_query($filters)) ?>" class="btn btn-success btn-sm">
                        <i class="fa fa-file-excel-o"></i> Ekspor Excel (CSV)
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="box-body" style="background:#f8f9fc; border-bottom:1px solid #e3e6f0; padding:12px 15px;">
        <form method="GET" action="<?= base_url('history_costing') ?>" class="form-inline">
            <div class="form-group" style="margin-right:15px;">
                <label style="margin-right:6px; font-weight:600;"><i class="fa fa-filter text-muted"></i> Kategori:</label>
                <select name="category" class="form-control input-sm select2" style="min-width:180px;">
                    <option value="">-- Semua Kategori --</option>
                    <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                        <option value="<?= html_escape($cat->code_lv2) ?>" <?= ($filters['code_lv2'] == $cat->code_lv2) ? 'selected' : '' ?>>
                            <?= html_escape($cat->nama) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin-right:15px;">
                <label style="margin-right:6px; font-weight:600;">Batch Update:</label>
                <select name="batch_id" class="form-control input-sm" style="min-width:180px;">
                    <option value="">-- Semua Batch --</option>
                    <?php if (!empty($batches)): foreach ($batches as $b): ?>
                        <option value="<?= html_escape($b->batch_id) ?>" <?= ($filters['batch_id'] == $b->batch_id) ? 'selected' : '' ?>>
                            <?= html_escape($b->batch_id) ?> (<?= date('d/m/Y H:i', strtotime($b->changed_at)) ?> - <?= $b->total_items ?> item)
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin-right:15px;">
                <label style="margin-right:6px; font-weight:600;">Tanggal:</label>
                <input type="date" name="start_date" class="form-control input-sm" value="<?= html_escape($filters['start_date']) ?>">
                <span style="margin:0 4px;">s/d</span>
                <input type="date" name="end_date" class="form-control input-sm" value="<?= html_escape($filters['end_date']) ?>">
            </div>

            <button type="submit" class="btn btn-info btn-sm">
                <i class="fa fa-search"></i> Terapkan
            </button>
            <?php if (!empty($filters['code_lv2']) || !empty($filters['batch_id']) || !empty($filters['start_date']) || !empty($filters['end_date'])): ?>
                <a href="<?= base_url('history_costing') ?>" class="btn btn-default btn-sm" style="margin-left:5px;">
                    <i class="fa fa-refresh"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Main Table -->
    <div class="box-body">
        <div class="table-responsive">
            <table id="tbl-history-costing" class="table table-bordered table-striped table-hover" style="font-size:12px; width:100%;">
                <thead style="background:#2c3e50; color:#fff;">
                    <tr>
                        <th style="width:4%; text-align:center;">No</th>
                        <th style="width:12%; text-align:center;">Waktu Perubahan</th>
                        <!-- FR-73: Nama produk di depan -->
                        <th style="width:20%;">Nama Produk</th>
                        <th style="width:10%; text-align:center;">Varian (Uniq ID)</th>
                        <th style="width:12%;">Kategori</th>
                        <th style="width:10%; text-align:right;">Bottom Lama (/m)</th>
                        <th style="width:10%; text-align:right; background:#c0392b;">Bottom Baru (/m)</th>
                        <th style="width:10%; text-align:right;">Selisih (Delta)</th>
                        <th style="width:10%; text-align:right; background:#27ae60;">Price List Baru (/m)</th>
                        <th style="width:8%; text-align:center;">Aksi</th>
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
                                <div><strong><?= date('d/m/Y', strtotime($r->changed_at)) ?></strong></div>
                                <div class="text-muted" style="font-size:10px;"><?= date('H:i:s', strtotime($r->changed_at)) ?></div>
                                <div class="text-muted" style="font-size:10px;">By: <?= html_escape($r->changed_by_name ? $r->changed_by_name : '-') ?></div>
                            </td>
                            <td>
                                <strong><?= html_escape($r->nm_product ? $r->nm_product : $r->code_lv4) ?></strong>
                                <div class="text-muted" style="font-size:10px;">Kode: <?= html_escape($r->code_lv4) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="label label-primary" style="font-size:11px;"><?= html_escape($r->no_uniq_id) ?></span>
                            </td>
                            <td><?= html_escape($r->nm_category ? $r->nm_category : '-') ?></td>
                            <td class="text-right">
                                Rp <?= number_format((float)$r->bottom_price_lama_meter, 2) ?>
                            </td>
                            <td class="text-right" style="font-weight:700; color:#c0392b; background:#fdf2f2;">
                                Rp <?= number_format((float)$r->bottom_price_baru_meter, 2) ?>
                            </td>
                            <td class="text-right" style="font-weight:600; color:<?= ($delta >= 0) ? '#e74c3c' : '#27ae60' ?>;">
                                <?= ($delta >= 0) ? '+' : '' ?>Rp <?= number_format($delta, 2) ?>
                                <div style="font-size:10px;"><?= ($pct >= 0) ? '+' : '' ?><?= number_format($pct, 2) ?>%</div>
                            </td>
                            <td class="text-right" style="font-weight:700; color:#27ae60; background:#f4fbf7;">
                                Rp <?= number_format((float)$r->price_list_baru_meter, 2) ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-info btn-xs btn-view-snapshot" data-id="<?= $r->id ?>">
                                    <i class="fa fa-pie-chart"></i> Rincian
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Rincian Snapshot Komponen Biaya -->
<div class="modal fade" id="modal-snapshot" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#2c3e50; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-pie-chart"></i> Snapshot Komponen Biaya (Lama vs Baru)</h4>
            </div>
            <div class="modal-body">
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-md-6">
                        <table class="table table-condensed table-borderless" style="margin:0; font-size:12px;">
                            <tr><td style="width:120px; font-weight:600;">Produk:</td><td id="snap-product">-</td></tr>
                            <tr><td style="font-weight:600;">Varian (Uniq ID):</td><td id="snap-uniq">-</td></tr>
                            <tr><td style="font-weight:600;">Batch ID:</td><td id="snap-batch">-</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-condensed table-borderless" style="margin:0; font-size:12px;">
                            <tr><td style="width:120px; font-weight:600;">Waktu Perubahan:</td><td id="snap-time">-</td></tr>
                            <tr><td style="font-weight:600;">Sumber Perubahan:</td><td id="snap-source">-</td></tr>
                            <tr><td style="font-weight:600;">Diperbarui Oleh:</td><td id="snap-user">-</td></tr>
                        </table>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="font-size:12px;">
                        <thead style="background:#f4f6f9;">
                            <tr>
                                <th style="width:30%;">Komponen Biaya (Rp/Meter)</th>
                                <th style="width:22%; text-align:right;">Nilai Lama</th>
                                <th style="width:22%; text-align:right;">Nilai Baru</th>
                                <th style="width:26%; text-align:right;">Perubahan (Selisih)</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-snapshot-body">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
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
                    $('#snap-uniq').html('<span class="label label-primary">' + r.no_uniq_id + '</span>');
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
                        html += '<td class="text-right">Rp ' + vOld.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                        html += '<td class="text-right" style="' + colorStyle + '">Rp ' + vNew.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                        
                        var diffColor = diff > 0 ? '#e74c3c' : (diff < 0 ? '#27ae60' : '#7f8c8d');
                        var sign = diff > 0 ? '+' : '';
                        html += '<td class="text-right" style="color:' + diffColor + '; font-weight:600;">' + sign + 'Rp ' + diff.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + sign + pct.toFixed(2) + '%)</td>';
                        html += '</tr>';
                    });

                    $('#tbl-snapshot-body').html(html);
                    $('#modal-snapshot').modal('show');
                } else {
                    swal("Error", res.pesan, "error");
                }
            },
            error: function() {
                swal("Error", "Gagal memuat snapshot komponen biaya", "error");
            }
        });
    });
});
</script>
