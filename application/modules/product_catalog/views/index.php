<?php
$ENABLE_ADD     = has_permission('Product_Catalog.Add');
$ENABLE_MANAGE  = has_permission('Product_Catalog.Manage');
$ENABLE_VIEW    = has_permission('Product_Catalog.View');
$ENABLE_DELETE  = has_permission('Product_Catalog.Delete');
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="box-title"><i class="fa fa-book text-primary"></i> Product Catalog Resmi HFG</h3>
                <p class="text-muted mb-0" style="font-size:12px;">Daftar harga katalog resmi hasil rilis Pricelist yang disetujui Management.</p>
            </div>
            <div class="col-md-6 text-right">
                <a href="<?= base_url('product_catalog/print_catalog?category=' . urlencode($filter_cat) . '&catalog_id=' . $filter_cid . '&status=' . $filter_stat) ?>" target="_blank" class="btn btn-default btn-sm">
                    <i class="fa fa-print"></i> Cetak Katalog
                </a>
                <?php if ($ENABLE_MANAGE): ?>
                    <a href="<?= base_url('product_catalog/export_excel?category=' . urlencode($filter_cat) . '&catalog_id=' . $filter_cid . '&status=' . $filter_stat) ?>" class="btn btn-success btn-sm">
                        <i class="fa fa-file-excel-o"></i> Ekspor Excel (CSV)
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-release-catalog">
                        <i class="fa fa-plus-circle"></i> Rilis Catalog Baru
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="box-body" style="background:#f8f9fc; border-bottom:1px solid #e3e6f0; padding:12px 15px;">
        <form method="GET" action="<?= base_url('product_catalog') ?>" class="form-inline">
            <div class="form-group" style="margin-right:15px;">
                <label style="margin-right:6px; font-weight:600;"><i class="fa fa-filter text-muted"></i> Kategori Produk:</label>
                <select name="category" class="form-control input-sm select2" style="min-width:180px;">
                    <option value="">-- Semua Kategori --</option>
                    <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                        <option value="<?= html_escape($cat->code_lv2) ?>" <?= ($filter_cat == $cat->code_lv2) ? 'selected' : '' ?>>
                            <?= html_escape($cat->nama) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin-right:15px;">
                <label style="margin-right:6px; font-weight:600;">Rilis Versi:</label>
                <select name="catalog_id" class="form-control input-sm" style="min-width:180px;">
                    <option value="">-- Semua Rilis --</option>
                    <?php if (!empty($headers)): foreach ($headers as $h): ?>
                        <option value="<?= $h->id ?>" <?= ($filter_cid == $h->id) ? 'selected' : '' ?>>
                            <?= html_escape($h->no_catalog) ?> (<?= date('d/m/Y', strtotime($h->released_at)) ?> - <?= $h->total_items ?> item)
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin-right:15px;">
                <label style="margin-right:6px; font-weight:600;">Status:</label>
                <select name="status" class="form-control input-sm">
                    <option value="Active" <?= ($filter_stat == 'Active') ? 'selected' : '' ?>>Active (Aktif)</option>
                    <option value="Archived" <?= ($filter_stat == 'Archived') ? 'selected' : '' ?>>Archived (Arsip)</option>
                    <option value="" <?= ($filter_stat === '') ? 'selected' : '' ?>>Semua Status</option>
                </select>
            </div>

            <button type="submit" class="btn btn-info btn-sm">
                <i class="fa fa-search"></i> Terapkan
            </button>
            <?php if (!empty($filter_cat) || !empty($filter_cid) || $filter_stat !== 'Active'): ?>
                <a href="<?= base_url('product_catalog') ?>" class="btn btn-default btn-sm" style="margin-left:5px;">
                    <i class="fa fa-refresh"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Main Table -->
    <div class="box-body">
        <div class="table-responsive">
            <table id="tbl-catalog" class="table table-bordered table-striped table-hover" style="font-size:12px; width:100%;">
                <thead style="background:#2c3e50; color:#fff;">
                    <tr>
                        <th style="width:4%; text-align:center;">No</th>
                        <!-- FR-73: Nama produk di depan -->
                        <th style="width:20%;">Nama Produk</th>
                        <th style="width:10%; text-align:center;">Varian (Uniq ID)</th>
                        <th style="width:12%;">Kategori Produk</th>
                        <th style="width:8%; text-align:center;">Ukuran (m)</th>
                        <th style="width:8%; text-align:right;">Berat/m (Kg)</th>
                        <th style="width:10%; text-align:right; background:#27ae60;">Price List / m</th>
                        <th style="width:10%; text-align:right;">Price List / Kg</th>
                        <th style="width:10%; text-align:right;">Price List / Pcs</th>
                        <th style="width:8%; text-align:center;">No Catalog</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): $no = 1; foreach ($items as $item): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <strong><?= html_escape($item->nm_product ? $item->nm_product : $item->code_lv4) ?></strong>
                                <div class="text-muted" style="font-size:10px;">Kode: <?= html_escape($item->code_lv4) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="label label-primary" style="font-size:11px;"><?= html_escape($item->no_uniq_id) ?></span>
                            </td>
                            <td><?= html_escape($item->nm_category ? $item->nm_category : '-') ?></td>
                            <td class="text-center"><?= number_format((float)$item->ukuran_product, 2) ?></td>
                            <td class="text-right"><?= number_format((float)$item->berat_per_meter, 4) ?></td>
                            <td class="text-right" style="font-weight:700; color:#27ae60; background:#f4fbf7;">
                                Rp <?= number_format((float)$item->price_list_meter, 2) ?>
                            </td>
                            <td class="text-right">
                                Rp <?= number_format((float)$item->price_list_kg, 2) ?>
                            </td>
                            <td class="text-right">
                                Rp <?= number_format((float)$item->price_list_pcs, 2) ?>
                            </td>
                            <td class="text-center">
                                <span class="label <?= ($item->catalog_status == 'Active') ? 'label-success' : 'label-default' ?>" style="font-size:10px;">
                                    <?= html_escape($item->no_catalog) ?>
                                </span>
                                <div class="text-muted" style="font-size:9px;"><?= date('d/m/Y', strtotime($item->released_at)) ?></div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Rilis Catalog Baru (Sales/Admin) -->
<?php if ($ENABLE_MANAGE): ?>
<div class="modal fade" id="modal-release-catalog" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#3c8dbc; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-rocket"></i> Rilis Product Catalog Baru</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Pilih dokumen Pricelist yang telah <strong>Disetujui (Approved)</strong> oleh Management untuk dijadikan rilis Product Catalog resmi aktif.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="font-size:12px;">
                        <thead style="background:#f4f6f9;">
                            <tr>
                                <th style="width:5%; text-align:center;">No</th>
                                <th>No Pricelist</th>
                                <th>Tanggal Disetujui</th>
                                <th>Disetujui Oleh</th>
                                <th style="text-align:center;">Jumlah Varian</th>
                                <th style="width:15%; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($approved_pls)): $n=1; foreach ($approved_pls as $apl): ?>
                                <tr>
                                    <td class="text-center"><?= $n++ ?></td>
                                    <td><strong><?= html_escape($apl->no_pricelist) ?></strong></td>
                                    <td><?= $apl->decided_at ? date('d/m/Y H:i', strtotime($apl->decided_at)) : '-' ?></td>
                                    <td><?= html_escape($apl->decided_by_name ? $apl->decided_by_name : '-') ?></td>
                                    <td class="text-center"><span class="badge bg-green"><?= $apl->total_items ?> item</span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-success btn-xs btn-do-release" data-id="<?= $apl->id ?>" data-no="<?= html_escape($apl->no_pricelist) ?>">
                                            <i class="fa fa-check"></i> Rilis Catalog
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
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
<?php endif; ?>

<script>
$(document).ready(function() {
    // Inisialisasi DataTables tanpa baris colspan manual
    var tblCatalog = $('#tbl-catalog').DataTable({
        responsive: true,
        ordering: true,
        pageLength: 25,
        order: [[1, 'asc']], // Order by Nama Produk (FR-73)
        columnDefs: [
            { orderable: false, targets: [0, 9] }
        ],
        language: {
            search: "Cari Produk / Varian:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada produk dalam katalog",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)"
        }
    });

    // Handle Rilis Catalog
    $(document).on('click', '.btn-do-release', function() {
        var id = $(this).data('id');
        var no = $(this).data('no');

        swal({
            title: "Konfirmasi Rilis Catalog",
            text: "Rilis snapshot Product Catalog baru berdasarkan dokumen Pricelist " + no + "?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#27ae60",
            confirmButtonText: "Ya, Rilis Sekarang!",
            cancelButtonText: "Batal",
            closeOnConfirm: false
        }, function() {
            $.ajax({
                url: "<?= base_url('product_catalog/release') ?>",
                type: "POST",
                dataType: "JSON",
                data: { id_pricelist: id },
                success: function(res) {
                    if (res.status == 1) {
                        swal({
                            title: "Berhasil!",
                            text: res.pesan,
                            type: "success"
                        }, function() {
                            location.reload();
                        });
                    } else {
                        swal("Gagal", res.pesan, "error");
                    }
                },
                error: function() {
                    swal("Error", "Terjadi kesalahan jaringan/server", "error");
                }
            });
        });
    });
});
</script>
