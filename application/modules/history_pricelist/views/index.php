<?php
$ENABLE_MANAGE = has_permission('History_Pricelist.Manage');
$ENABLE_VIEW   = has_permission('History_Pricelist.View');
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="box-title"><i class="fa fa-clock-o text-primary"></i> History Price List</h3>
                <p class="text-muted mb-0" style="font-size:12px;">Catatan riwayat persetujuan dokumen Price List oleh Management (Hanya Baca / Audit Trail).</p>
            </div>
            <div class="col-md-6 text-right">
                <?php if ($ENABLE_MANAGE): ?>
                    <a href="<?= base_url('history_pricelist/export_excel?' . http_build_query($filters)) ?>" class="btn btn-success btn-sm">
                        <i class="fa fa-file-excel-o"></i> Ekspor Excel (CSV)
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="box-body" style="background:#f8f9fc; border-bottom:1px solid #e3e6f0; padding:12px 15px;">
        <form method="GET" action="<?= base_url('history_pricelist') ?>" class="form-inline">
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
                <label style="margin-right:6px; font-weight:600;">No Pricelist:</label>
                <select name="no_pricelist" class="form-control input-sm" style="min-width:180px;">
                    <option value="">-- Semua Dokumen --</option>
                    <?php if (!empty($pricelists)): foreach ($pricelists as $pl): ?>
                        <option value="<?= html_escape($pl->no_pricelist) ?>" <?= ($filters['no_pricelist'] == $pl->no_pricelist) ? 'selected' : '' ?>>
                            <?= html_escape($pl->no_pricelist) ?> (<?= date('d/m/Y', strtotime($pl->event_at)) ?> - <?= $pl->total_items ?> item)
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
            <?php if (!empty($filters['code_lv2']) || !empty($filters['no_pricelist']) || !empty($filters['start_date']) || !empty($filters['end_date'])): ?>
                <a href="<?= base_url('history_pricelist') ?>" class="btn btn-default btn-sm" style="margin-left:5px;">
                    <i class="fa fa-refresh"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Main Table -->
    <div class="box-body">
        <div class="table-responsive">
            <table id="tbl-history-pricelist" class="table table-bordered table-striped table-hover" style="font-size:12px; width:100%;">
                <thead style="background:#2c3e50; color:#fff;">
                    <tr>
                        <th style="width:4%; text-align:center;">No</th>
                        <th style="width:12%; text-align:center;">Waktu Approval</th>
                        <!-- FR-73: Nama produk di depan -->
                        <th style="width:20%;">Nama Produk</th>
                        <th style="width:10%; text-align:center;">Varian (Uniq ID)</th>
                        <th style="width:10%;">Kategori</th>
                        <th style="width:10%; text-align:right;">Price List Lama (/m)</th>
                        <th style="width:10%; text-align:right; background:#27ae60;">Price List Baru (/m)</th>
                        <th style="width:8%; text-align:right;">Perubahan</th>
                        <th style="width:10%; text-align:right;">Bottom Price (/m)</th>
                        <th style="width:6%; text-align:center;">Extra Margin</th>
                        <th style="width:10%; text-align:center;">No Pricelist</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($list)): $no = 1; foreach ($list as $r): 
                        $delta = (float)$r->price_list_baru_meter - (float)$r->price_list_lama_meter;
                        $pct = ($r->price_list_lama_meter > 0) ? ($delta / (float)$r->price_list_lama_meter) * 100 : 0;
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="text-center">
                                <div><strong><?= date('d/m/Y', strtotime($r->event_at)) ?></strong></div>
                                <div class="text-muted" style="font-size:10px;"><?= date('H:i', strtotime($r->event_at)) ?></div>
                                <div class="text-muted" style="font-size:10px;">By: <?= html_escape($r->event_by_name ? $r->event_by_name : '-') ?></div>
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
                                Rp <?= number_format((float)$r->price_list_lama_meter, 2) ?>
                            </td>
                            <td class="text-right" style="font-weight:700; color:#27ae60; background:#f4fbf7;">
                                Rp <?= number_format((float)$r->price_list_baru_meter, 2) ?>
                                <div class="text-muted" style="font-size:10px;">(Rp <?= number_format((float)$r->price_list_baru_kg, 2) ?>/Kg)</div>
                            </td>
                            <td class="text-right" style="font-weight:600; color:<?= ($delta >= 0) ? '#27ae60' : '#e74c3c' ?>;">
                                <?= ($delta >= 0) ? '+' : '' ?>Rp <?= number_format($delta, 2) ?>
                                <div style="font-size:10px;"><?= ($pct >= 0) ? '+' : '' ?><?= number_format($pct, 2) ?>%</div>
                            </td>
                            <td class="text-right">
                                Rp <?= number_format((float)$r->bottom_price_meter, 2) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-purple" style="font-size:11px;"><?= number_format((float)$r->rate_negotiation_allowance * 100, 1) ?>%</span>
                            </td>
                            <td class="text-center">
                                <span class="label label-success" style="font-size:10px;"><?= html_escape($r->no_pricelist) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inisialisasi DataTables tanpa baris colspan manual
    $('#tbl-history-pricelist').DataTable({
        responsive: true,
        ordering: true,
        pageLength: 25,
        order: [[1, 'desc']], // Urutkan tanggal approval terbaru
        columnDefs: [
            { orderable: false, targets: [0, 9, 10] }
        ],
        language: {
            search: "Cari Produk / Varian / No PL:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada riwayat persetujuan pricelist",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ riwayat",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)"
        }
    });
});
</script>
