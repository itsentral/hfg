<?php
$ENABLE_ADD     = has_permission('Product_Catalog.Add');
$ENABLE_MANAGE  = has_permission('Product_Catalog.Manage');
$ENABLE_VIEW    = has_permission('Product_Catalog.View');
$ENABLE_DELETE  = has_permission('Product_Catalog.Delete');

// Parameter query string untuk ekspor dan cetak
$export_params = http_build_query(array_filter([
    'category'   => $filter_cat,
    'catalog_id' => $filter_cid,
    'status'     => $filter_stat,
    'date_from'  => $filter_date_from,
    'date_to'    => $filter_date_to,
], function ($v) { return $v !== null && $v !== ''; }));
?>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .select2-container--default .select2-selection--single {
        height: 34px !important;
        border: 1px solid #d2d6de !important;
        border-radius: 4px !important;
        padding: 2px 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        font-size: 12px;
        color: #444;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
    }
    .flatpickr-input {
        background-color: #fff !important;
        cursor: pointer;
    }
    .catalog-stat-card {
        background: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
    }
    .catalog-stat-card .icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
        font-size: 20px;
    }
    .catalog-stat-card .stat-title {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        color: #858796;
        margin-bottom: 2px;
    }
    .catalog-stat-card .stat-value {
        font-size: 18px;
        font-weight: 700;
        color: #2e384d;
        line-height: 1.2;
    }
</style>

<!-- Stat Summary Header -->
<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="catalog-stat-card">
            <div class="icon-wrap" style="background:#e8f4fd; color:#1a73e8;">
                <i class="fa fa-book"></i>
            </div>
            <div>
                <div class="stat-title">Katalog Aktif</div>
                <div class="stat-value"><?= !empty($stats->active_catalog_no) ? html_escape($stats->active_catalog_no) : '-' ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="catalog-stat-card">
            <div class="icon-wrap" style="background:#e6f4ea; color:#137333;">
                <i class="fa fa-cubes"></i>
            </div>
            <div>
                <div class="stat-title">Item Terfilter</div>
                <div class="stat-value"><?= number_format(count($items)) ?> <span style="font-size:12px; font-weight:normal; color:#666;">Produk</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="catalog-stat-card">
            <div class="icon-wrap" style="background:#fef7e0; color:#b06000;">
                <i class="fa fa-tags"></i>
            </div>
            <div>
                <div class="stat-title">Kategori Produk</div>
                <div class="stat-value"><?= !empty($stats->total_categories) ? $stats->total_categories : count($categories) ?> <span style="font-size:12px; font-weight:normal; color:#666;">Level 2</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="catalog-stat-card">
            <div class="icon-wrap" style="background:#f3e8fd; color:#7627bb;">
                <i class="fa fa-history"></i>
            </div>
            <div>
                <div class="stat-title">Total Versi Rilis</div>
                <div class="stat-value"><?= !empty($stats->total_releases) ? $stats->total_releases : count($headers) ?> <span style="font-size:12px; font-weight:normal; color:#666;">Snapshot</span></div>
            </div>
        </div>
    </div>
</div>

<div class="box box-primary" style="border-radius:6px; box-shadow:0 1px 4px rgba(0,0,0,0.06);">
    <div class="box-header with-border" style="padding:15px;">
        <div class="row align-items-center">
            <div class="col-md-6 col-sm-12">
                <h3 class="box-title" style="font-size:16px; font-weight:700;">
                    <i class="fa fa-book text-primary" style="margin-right:6px;"></i> Product Catalog Resmi <?= html_escape(get_company_code()) ?>
                </h3>
                <p class="text-muted mb-0" style="font-size:12px; margin-top:3px;">
                    Daftar harga resmi katalog snapshot dari dokumen Pricelist yang telah disetujui (FR-73: Nama produk di depan).
                </p>
            </div>
            <div class="col-md-6 col-sm-12 text-right" style="margin-top:6px;">
                <a href="<?= base_url('product_catalog/print_catalog?' . $export_params) ?>" target="_blank" class="btn btn-default btn-sm" style="border-radius:4px;">
                    <i class="fa fa-print"></i> Cetak Katalog
                </a>
                <?php if ($ENABLE_MANAGE): ?>
                    <a href="<?= base_url('product_catalog/export_excel?' . $export_params) ?>" class="btn btn-success btn-sm" style="border-radius:4px; margin-left:4px;">
                        <i class="fa fa-file-excel-o"></i> Ekspor Excel (CSV)
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-release-catalog" style="border-radius:4px; margin-left:4px;">
                        <i class="fa fa-plus-circle"></i> Rilis Catalog Baru
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter Bar Menggunakan Select2 & Flatpickr -->
    <div class="box-body" style="background:#f8f9fc; border-bottom:1px solid #e3e6f0; padding:15px;">
        <form method="GET" action="<?= base_url('product_catalog') ?>" id="form-filter">
            <div class="row">
                <!-- Select2: Kategori Produk -->
                <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom:10px;">
                    <label style="font-size:11px; font-weight:600; color:#4e73df; margin-bottom:4px;">
                        <i class="fa fa-filter"></i> Kategori Produk (Level 2)
                    </label>
                    <select name="category" id="filter_category" class="form-control select2" data-placeholder="-- Semua Kategori --" style="width:100%;">
                        <option value="">-- Semua Kategori --</option>
                        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                            <option value="<?= html_escape($cat->code_lv2) ?>" <?= ($filter_cat == $cat->code_lv2) ? 'selected' : '' ?>>
                                <?= html_escape($cat->nama) ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <!-- Select2: Rilis Versi Katalog -->
                <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom:10px;">
                    <label style="font-size:11px; font-weight:600; color:#4e73df; margin-bottom:4px;">
                        <i class="fa fa-history"></i> Versi Rilis Katalog
                    </label>
                    <select name="catalog_id" id="filter_catalog_id" class="form-control select2" data-placeholder="-- Semua Rilis --" style="width:100%;">
                        <option value="">-- Semua Rilis --</option>
                        <?php if (!empty($headers)): foreach ($headers as $h): ?>
                            <option value="<?= $h->id ?>" <?= ($filter_cid == $h->id) ? 'selected' : '' ?>>
                                <?= html_escape($h->no_catalog) ?> (<?= date('d/m/Y', strtotime((string) $h->released_at)) ?> - <?= $h->total_items ?> item)
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <!-- Select2: Status Katalog -->
                <div class="col-md-2 col-sm-6 col-xs-12" style="margin-bottom:10px;">
                    <label style="font-size:11px; font-weight:600; color:#4e73df; margin-bottom:4px;">
                        <i class="fa fa-info-circle"></i> Status Katalog
                    </label>
                    <select name="status" id="filter_status" class="form-control select2" data-placeholder="-- Pilih Status --" style="width:100%;">
                        <option value="Active" <?= ($filter_stat == 'Active') ? 'selected' : '' ?>>Active (Katalog Aktif)</option>
                        <option value="Archived" <?= ($filter_stat == 'Archived') ? 'selected' : '' ?>>Archived (Arsip)</option>
                        <option value="" <?= ($filter_stat === '') ? 'selected' : '' ?>>Semua Status</option>
                    </select>
                </div>

                <!-- Flatpickr: Rentang Tanggal Rilis -->
                <div class="col-md-2 col-sm-6 col-xs-12" style="margin-bottom:10px;">
                    <label style="font-size:11px; font-weight:600; color:#4e73df; margin-bottom:4px;">
                        <i class="fa fa-calendar"></i> Tanggal Rilis Dari
                    </label>
                    <div class="input-group">
                        <input type="text" name="date_from" id="filter_date_from" class="form-control flatpickr-date" value="<?= html_escape($filter_date_from) ?>" placeholder="Tgl Dari" autocomplete="off" style="font-size:12px; height:34px;">
                        <span class="input-group-addon" style="background:#fff; border-color:#d2d6de;"><i class="fa fa-calendar text-muted"></i></span>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6 col-xs-12" style="margin-bottom:10px;">
                    <label style="font-size:11px; font-weight:600; color:#4e73df; margin-bottom:4px;">
                        <i class="fa fa-calendar"></i> Tanggal Rilis Sampai
                    </label>
                    <div class="input-group">
                        <input type="text" name="date_to" id="filter_date_to" class="form-control flatpickr-date" value="<?= html_escape($filter_date_to) ?>" placeholder="Tgl Sampai" autocomplete="off" style="font-size:12px; height:34px;">
                        <span class="input-group-addon" style="background:#fff; border-color:#d2d6de;"><i class="fa fa-calendar text-muted"></i></span>
                    </div>
                </div>
            </div>

            <!-- Tombol Filter & Reset -->
            <div class="row" style="margin-top:4px;">
                <div class="col-xs-12 text-right">
                    <button type="submit" class="btn btn-primary btn-sm" style="border-radius:4px; padding:6px 16px;">
                        <i class="fa fa-filter"></i> Terapkan Filter
                    </button>
                    <?php if (!empty($filter_cat) || !empty($filter_cid) || $filter_stat !== 'Active' || !empty($filter_date_from) || !empty($filter_date_to)): ?>
                        <a href="<?= base_url('product_catalog') ?>" class="btn btn-default btn-sm" style="border-radius:4px; margin-left:4px;">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Main Table (FR-73: Nama Produk di Depan) -->
    <div class="box-body" style="padding:15px;">
        <div class="table-responsive">
            <table id="tbl-catalog" class="table table-bordered table-striped table-hover" style="font-size:12px; width:100%;">
                <thead style="background:#2c3e50; color:#fff;">
                    <tr>
                        <th style="width:4%; text-align:center;">No</th>
                        <!-- FR-73: Nama produk di depan -->
                        <th style="width:22%;">Product Name</th>
                        <th style="width:10%; text-align:center;">Varian (Uniq ID)</th>
                        <th style="width:12%;">Product Category</th>
                        <th style="width:7%; text-align:center;">Size (m)</th>
                        <th style="width:8%; text-align:right;">Weight/m (Kg)</th>
                        <th style="width:10%; text-align:right; background:#27ae60;">Price List / m</th>
                        <th style="width:10%; text-align:right;">Price List / Kg</th>
                        <th style="width:10%; text-align:right;">Price List / Pcs</th>
                        <th style="width:7%; text-align:center;">No Catalog</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): $no = 1; foreach ($items as $item): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <strong style="color:#2c3e50; font-size:12.5px;"><?= html_escape($item->nm_product ? $item->nm_product : $item->code_lv4) ?></strong>
                                <div class="text-muted" style="font-size:10.5px; margin-top:2px;">
                                    <span class="label label-default" style="font-size:9.5px; padding:2px 5px;"><?= html_escape($item->code_lv4) ?></span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="label label-primary" style="font-size:11px; padding:3px 8px; border-radius:3px;">
                                    <?= html_escape($item->no_uniq_id) ?>
                                </span>
                            </td>
                            <td><?= html_escape($item->nm_category ? $item->nm_category : '-') ?></td>
                            <td class="text-center"><?= number_format((float)$item->ukuran_product, 2) ?></td>
                            <td class="text-right"><?= number_format((float)$item->berat_per_meter, 4) ?></td>
                            <td class="text-right" style="font-weight:700; color:#1e7e34; background:#f4fbf7;">
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
                                <div class="text-muted" style="font-size:9.5px; margin-top:2px;">
                                    <?= date('d/m/Y', strtotime((string) $item->released_at)) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="10" class="text-center" style="padding:25px; color:#888;">
                                <i class="fa fa-info-circle fa-2x" style="color:#ccc; display:block; margin-bottom:8px;"></i>
                                Tidak ada data katalog produk yang sesuai dengan filter yang dipilih.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Rilis Catalog Baru (Sales/Admin) -->
<?php if ($ENABLE_MANAGE): ?>
<div class="modal fade" id="modal-release-catalog" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:6px; overflow:hidden;">
            <div class="modal-header" style="background:#2c3e50; color:#fff; padding:15px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:0.8;">&times;</button>
                <h4 class="modal-title" style="font-weight:700;">
                    <i class="fa fa-rocket" style="margin-right:6px;"></i> Rilis Product Catalog Baru
                </h4>
            </div>
            <div class="modal-body" style="padding:20px;">
                <div class="alert alert-info" style="border-radius:4px; font-size:12px;">
                    <i class="fa fa-info-circle" style="margin-right:4px;"></i>
                    Pilih dokumen <strong>Pricelist</strong> yang telah berstatus <strong>Approved</strong> oleh Management untuk dijadikan rilis snapshot Product Catalog resmi aktif.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="font-size:12px;">
                        <thead style="background:#f4f6f9;">
                            <tr>
                                <th style="width:5%; text-align:center;">No</th>
                                <th>No Pricelist</th>
                                <th>Approval Date</th>
                                <th>Approved By</th>
                                <th style="text-align:center;">Jumlah Varian</th>
                                <th style="width:16%; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($approved_pls)): $n=1; foreach ($approved_pls as $apl): ?>
                                <tr>
                                    <td class="text-center"><?= $n++ ?></td>
                                    <td><strong style="color:#1a73e8;"><?= html_escape($apl->no_pricelist) ?></strong></td>
                                    <td><?= $apl->decided_at ? date('d/m/Y H:i', strtotime((string) $apl->decided_at)) : '-' ?></td>
                                    <td><?= html_escape($apl->decided_by_name ? $apl->decided_by_name : '-') ?></td>
                                    <td class="text-center"><span class="badge bg-green"><?= $apl->total_items ?> item</span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-success btn-xs btn-do-release" data-id="<?= $apl->id ?>" data-no="<?= html_escape($apl->no_pricelist) ?>" style="border-radius:3px; padding:4px 8px;">
                                            <i class="fa fa-check"></i> Rilis Catalog
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center" style="padding:15px; color:#888;">
                                        Belum ada dokumen Pricelist yang berstatus Approved untuk dirilis.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="padding:12px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
$(document).ready(function() {
    // 1. Inisialisasi Select2 pada seluruh filter dropdown
    if ($.fn.select2) {
        $('.select2').select2({
            width: '100%',
            allowClear: true
        });
    }

    // 2. Inisialisasi Flatpickr pada input rentang tanggal rilis
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.flatpickr-date', {
            locale: 'id',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true
        });
    }

    // 3. Inisialisasi DataTables (FR-73: Order by Nama Produk)
    var tblCatalog = $('#tbl-catalog').DataTable({
        responsive: true,
        ordering: true,
        pageLength: 25,
        order: [[1, 'asc']], // Order by Product Name
        columnDefs: [
            { orderable: false, targets: [0, 9] }
        ],
        language: {
            search: "Cari Produk / Varian:",
            lengthMenu: "Tampilkan _MENU_ baris",
            zeroRecords: "Tidak ada produk dalam katalog",
            info: "Menampilkan _START_ sampai _END_ dari total _TOTAL_ produk",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)"
        }
    });

    // 4. Handle Rilis Catalog Baru dengan SweetAlert
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
        }, function(isConfirm) {
            if (!isConfirm) {
                return;
            }
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
                    swal("Error", "Terjadi kesalahan komunikasi dengan server", "error");
                }
            });
        });
    });
});
</script>
