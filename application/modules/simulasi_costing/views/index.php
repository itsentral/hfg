<style>
    .select2-container--default .select2-selection--single {
        height: 34px !important;
        padding: 3px 10px !important;
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        padding-left: 0 !important;
        font-size: 0.875rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
        right: 8px !important;
    }

    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
</style>
<?php
$ENABLE_MANAGE = has_permission('Simulasi_Costing.Manage');
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1 text-primary fw-bold">
                    <i class="fa fa-calculator me-2"></i>Simulasi Costing
                </h5>
                <small class="text-muted">
                    Perhitungan otomatis per Produk &times; Varian BOM: Material Cost &rarr; Biaya Proses &rarr; Support &rarr; HPP &rarr; Bottom Price &rarr; Price List.
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if ($ENABLE_MANAGE): ?>
                    <button type="button" class="btn btn-primary btn-sm shadow-sm btn-update-costing">
                        <i class="fa fa-refresh me-1"></i> Update Costing
                    </button>
                <?php endif; ?>
                <a href="<?= base_url('pricelist') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="fa fa-list-alt me-1"></i> Open Pricelist
                </a>
            </div>
        </div>

        <div class="row align-items-center g-2">
            <div class="col-md-4">
                <label class="form-label text-muted small mb-1 fw-bold">Filter Product Category</label>
                <select id="filter-category" class="form-select form-select-sm select2 chosen-select" style="width: 100%;">
                    <option value="">-- SEMUA PRODUCT CATEGORY --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars((string) $cat->code_lv2) ?>" <?= ($cat_filter === $cat->code_lv2) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(strtoupper((string) $cat->nama)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8 text-md-end pt-3 pt-md-0">
                <span class="badge bg-light text-dark border px-3 py-2">
                    Total: <strong><?= count($costings ?? []) ?></strong> Varian BOM
                </span>
                <?php if ($incomplete_count > 0): ?>
                    <span class="badge bg-warning text-dark border border-warning px-3 py-2 ms-2">
                        <i class="fa fa-exclamation-triangle me-1"></i> <?= $incomplete_count ?> Data Belum Lengkap
                    </span>
                <?php else: ?>
                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 ms-2">
                        <i class="fa fa-check-circle me-1"></i> Seluruh Parameter Lengkap
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-body p-3">
        <?php if ($incomplete_count > 0): ?>
            <div class="alert alert-warning py-2 mb-3 small" role="alert">
                <i class="fa fa-info-circle me-1"></i>
                <strong>Notice:</strong> Some variants have incomplete parameters (marked yellow). Complete the related master data so the costing figures are accurate.
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-simulasi" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="40">#</th>
                        <th>Product Category</th>
                        <th>Product Name</th>
                        <th>Varian Material</th>
                        <th width="110">Uniq ID</th>
                        <th class="text-end" width="130">Bottom Price/m</th>
                        <th class="text-end" width="130">Price List/m</th>
                        <th class="text-end" width="130">Price List/kg</th>
                        <th class="text-center" width="110">Data</th>
                        <th class="text-center" width="130">Last Updated</th>
                        <th class="text-center no-sort" width="140">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($costings)): ?>
                        <?php foreach ($costings as $idx => $c): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><?= htmlspecialchars(strtoupper((string) $c->nm_category)) ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars(strtoupper((string) $c->nm_product)) ?></td>
                                <td><?= htmlspecialchars((string) $c->nm_material) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars((string) $c->no_uniq_id) ?></span></td>
                                <td class="text-end fw-bold">
                                    Rp <?= number_format((float) $c->bottom_meter, 2, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold text-primary fs-6">
                                    Rp <?= number_format((float) $c->pricelist_meter, 2, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold text-success fs-6">
                                    Rp <?= number_format((float) $c->pricelist_kg, 2, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($c->is_lengkap): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">Lengkap</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark border border-warning px-2 py-1" data-bs-toggle="tooltip" title="<?= htmlspecialchars(implode(', ', $c->gaps ?? [])) ?>">
                                            &#9888; <?= count($c->gaps ?? []) ?> Kekurangan
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-muted small">
                                    <?= (!empty($c->last_updated) && strtotime((string) $c->last_updated) > 0) ? date('d/m/Y H:i', strtotime((string) $c->last_updated)) : '-' ?>
                                </td>
                                <td class="text-center text-nowrap">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-view-detail shadow-xs"
                                            data-bom-detail-id="<?= $c->bom_detail_id ?>"
                                            data-uniq="<?= htmlspecialchars((string) $c->no_uniq_id) ?>"
                                            data-product="<?= htmlspecialchars((string) $c->nm_product) ?>"
                                            title="View Costing Detail Report"
                                            style="width: 80px;">
                                            <i class="fa fa-eye me-1"></i> Detail
                                        </button>

                                        <?php if ($ENABLE_MANAGE): ?>
                                            <?php if ($c->is_lengkap): ?>
                                                <button type="button" class="btn btn-sm btn-success btn-add-draft shadow-xs"
                                                    data-bom-detail-id="<?= $c->bom_detail_id ?>"
                                                    data-uniq="<?= htmlspecialchars((string) $c->no_uniq_id) ?>"
                                                    data-product="<?= htmlspecialchars((string) $c->nm_product) ?>"
                                                    data-category="<?= htmlspecialchars((string) $c->nm_category) ?>"
                                                    data-price-m="Rp <?= number_format((float) $c->pricelist_meter, 2, ',', '.') ?>"
                                                    data-price-kg="Rp <?= number_format((float) $c->pricelist_kg, 2, ',', '.') ?>"
                                                    title="Submit this variant to the Draft Pricelist"
                                                    style="width: 80px;">
                                                    <i class="fa fa-plus-circle me-1"></i> Draft
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary opacity-50 shadow-xs" disabled
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Cannot be submitted: <?= htmlspecialchars(implode(', ', $c->gaps ?? [])) ?>"
                                                    style="width: 80px;">
                                                    <i class="fa fa-lock me-1"></i> Draft
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3 text-muted small">
            <i class="fa fa-info-circle me-1"></i>
            <strong>Notes:</strong> The <strong>Update Costing</strong> button records all costing changes in <em>History Costing</em> and triggers price change notifications for Sales.
        </div>
    </div>
</div>

<!-- Modal Report Costing Detail (2 Satuan: Rp/Meter & Rp/Kg) -->
<div class="modal fade" id="modal-detail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalDetailLabel">
                    <i class="fa fa-file-text-o me-2"></i>Report Costing Detail / Produk
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detail-body-content">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">Loading costing details...</div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tbl-simulasi').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                },
                {
                    targets: [0, 5, 6, 7, 8, 9, 10],
                    searchable: false
                }
            ],
            language: {
                search: "Search Data:",
                searchPlaceholder: "Product / Material / Variant Name..."
            }
        });

        // Tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(el) {
            return new bootstrap.Tooltip(el);
        });

        // Inisialisasi Select2 pada Filter Product Category
        if ($.fn.select2) {
            $('#filter-category').select2({
                width: '100%',
                placeholder: '-- SEMUA PRODUCT CATEGORY --',
                allowClear: true
            });
        }

        // Filter Category
        $('#filter-category').on('change', function() {
            var c = $(this).val();
            window.location.href = '<?= base_url('simulasi_costing') ?>' + (c ? '?category=' + encodeURIComponent(c) : '');
        });

        // Update Costing Click
        $('.btn-update-costing').on('click', function() {
            var btn = $(this);
            Swal.fire({
                title: 'Jalankan Update Costing?',
                text: 'Master changes since the previous update will be recorded in History Costing and trigger a Bottom Price Notification for Sales.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, Update Now!',
                cancelButtonText: 'Cancel'
            }).then(function(res) {
                if (!res.isConfirmed) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Memperbarui...');

                $.ajax({
                    url: '<?= base_url('simulasi_costing/update_costing') ?>',
                    type: 'POST',
                    dataType: 'json',
                    success: function(resp) {
                        btn.prop('disabled', false).html('<i class="fa fa-refresh me-1"></i> Update Costing');
                        if (resp.status == 1) {
                            Swal.fire({
                                title: 'Success!',
                                text: resp.pesan,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(function() {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Failed!',
                                text: resp.pesan,
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-refresh me-1"></i> Update Costing');
                        Swal.fire({
                            title: 'Error!',
                            text: 'Request error: ' + xhr.status,
                            icon: 'error'
                        });
                    }
                });
            });
        });

        // Add Draft Click dengan Alert Konfirmasi
        $(document).on('click', '.btn-add-draft', function() {
            var bomDetailId = $(this).data('bom-detail-id');
            var uniq = $(this).data('uniq');
            var prod = $(this).data('product') || '';
            var cat = $(this).data('category') || '';
            var plM = $(this).data('price-m') || '';
            var plKg = $(this).data('price-kg') || '';
            var btn = $(this);

            Swal.fire({
                title: 'Submit to Draft Pricelist?',
                html: '<div class="text-start">' +
                    '<p class="mb-2">You are about to add the following variants to the document <strong>Draft Pricelist</strong> active:</p>' +
                    '<div class="p-3 bg-light rounded border small mb-2">' +
                    '<div><strong>Produk:</strong> ' + prod + '</div>' +
                    '<div><strong>Category:</strong> ' + cat + '</div>' +
                    '<div><strong>Uniq ID:</strong> <span class="badge bg-secondary">' + (uniq || '-') + '</span></div>' +
                    '<div><strong>Price List/m:</strong> <span class="text-primary fw-bold">' + plM + '</span></div>' +
                    '<div><strong>Price List/kg:</strong> <span class="text-success fw-bold">' + plKg + '</span></div>' +
                    '</div>' +
                    '<small class="text-muted"><i class="fa fa-info-circle me-1"></i> This variant will be added to the Draft Pricelist and can be reviewed before it is submitted to Management.</small>' +
                    '</div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-plus-circle me-1"></i> Yes, Add to Draft',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Memproses...');

                $.ajax({
                    url: '<?= base_url('simulasi_costing/add_draft') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        bom_detail_id: bomDetailId,
                        no_uniq_id: uniq
                    },
                    success: function(resp) {
                        btn.prop('disabled', false).html('<i class="fa fa-plus-circle me-1"></i> + Draft');
                        if (resp.status == 1) {
                            Swal.fire({
                                title: 'Added to Draft Successfully!',
                                text: resp.pesan,
                                icon: 'success',
                                confirmButtonText: '<i class="fa fa-list-alt me-1"></i> Open Pricelist',
                                showCancelButton: true,
                                cancelButtonText: 'Stay in Simulation'
                            }).then(function(nav) {
                                if (nav.isConfirmed) {
                                    window.location.href = '<?= base_url('pricelist') ?>';
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Attention',
                                text: resp.pesan,
                                icon: 'warning'
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-plus-circle me-1"></i> + Draft');
                        Swal.fire({
                            title: 'Error!',
                            text: 'Request error: ' + xhr.status,
                            icon: 'error'
                        });
                    }
                });
            });
        });

        // View Detail Modal
        $(document).on('click', '.btn-view-detail', function() {
            var bomDetailId = $(this).data('bom-detail-id');
            var uniq = $(this).data('uniq');
            var prod = $(this).data('product') || '';
            var titleLabel = 'Report Costing Detail';
            if (prod) {
                titleLabel += ' — ' + prod;
            } else if (uniq) {
                titleLabel += ' — Varian ' + uniq;
            }
            $('#modalDetailLabel').html('<i class="fa fa-file-text-o me-2"></i>' + titleLabel);
            $('#modal-detail').modal('show');
            $('#detail-body-content').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Loading costing details...</div></div>');

            $.ajax({
                url: '<?= base_url('simulasi_costing/detail_json') ?>',
                type: 'GET',
                dataType: 'json',
                data: {
                    bom_detail_id: bomDetailId,
                    no_uniq_id: uniq
                },
                success: function(resp) {
                    if (resp.status != 1) {
                        $('#detail-body-content').html('<div class="alert alert-danger">' + resp.pesan + '</div>');
                        return;
                    }
                    var d = resp.data;
                    var fn = function(v) {
                        return Number(v || 0).toLocaleString('id-ID', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    };

                    var lbl_sales = (d.other_labels && d.other_labels.sales) ? d.other_labels.sales : 'Sales & Marketing';
                    var lbl_trans = (d.other_labels && d.other_labels.trans) ? d.other_labels.trans : 'Transport';
                    var lbl_fa = (d.other_labels && d.other_labels.fa) ? d.other_labels.fa : 'Finance & Admin';
                    var lbl_mgmt = (d.other_labels && d.other_labels.mgmt) ? d.other_labels.mgmt : 'Management Fee';
                    var lbl_int = (d.other_labels && d.other_labels.int) ? d.other_labels.int : 'Interest';
                    var lbl_prof = (d.other_labels && d.other_labels.prof) ? d.other_labels.prof : 'Profit';

                    var html = `
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Produk Finished Goods</small>
                                    <strong class="fs-6">${d.nm_product}</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Product Category</small>
                                    <strong>${d.nm_category}</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Varian BOM / Uniq ID</small>
                                    <span class="badge bg-primary fs-6">${d.no_uniq_id}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Material Coil</small>
                                    <span>${d.nm_material}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Weight per Meter</small>
                                    <strong>${Number(d.berat_per_meter).toFixed(4)} kg/m</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Product Length Size</small>
                                    <strong>${d.ukuran} meter</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle mb-0">
                            <thead class="text-dark">
                                <tr>
                                    <th>Komponen Costing</th>
                                    <th class="text-end" width="220">Rp / Meter</th>
                                    <th class="text-end" width="220">Rp / Kg</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-light fw-bold"><td colspan="3"><i class="fa fa-cube me-1"></i> 1. Material Cost</td></tr>
                                <tr>
                                    <td>Material Cost (Price Ref USD ${d.pr_usd} &times; Rate Rp ${fn(d.kurs_usd)} &times; Weight/m)</td>
                                    <td class="text-end fw-bold">Rp ${fn(d.material_meter)}</td>
                                    <td class="text-end fw-bold">Rp ${fn(d.material_kg)}</td>
                                </tr>

                                <tr class="table-light fw-bold"><td colspan="3"><i class="fa fa-cogs me-1"></i> 2. Biaya Proses</td></tr>
                                <tr>
                                    <td>Man Power Cost: (TMH ${Number(d.total_man_hour).toFixed(6)} jam &times; Rate 1/3 Op Rp ${fn(d.rate_1_3_op)} + Borongan Rp ${fn(d.rate_borongan)}) &times; Faktor ${d.faktor_indirect}</td>
                                    <td class="text-end">Rp ${fn(d.man_power_meter)}</td>
                                    <td class="text-end">Rp ${fn(d.man_power_kg)}</td>
                                </tr>
                                <tr>
                                    <td>Special Supporting Material (BPK)</td>
                                    <td class="text-end">Rp ${fn(d.bpk_meter)}</td>
                                    <td class="text-end">Rp ${fn(d.bpk_kg)}</td>
                                </tr>
                                <tr>
                                    <td>Factory Overhead (FOH)</td>
                                    <td class="text-end">Rp ${fn(d.foh_meter)}</td>
                                    <td class="text-end">Rp ${fn(d.foh_kg)}</td>
                                </tr>
                                <tr class="fw-bold bg-light">
                                    <td>Process Cost Subtotal</td>
                                    <td class="text-end text-primary">Rp ${fn(d.proses_meter)}</td>
                                    <td class="text-end text-primary">Rp ${fn(d.proses_kg)}</td>
                                </tr>

                                <tr class="table-light fw-bold"><td colspan="3"><i class="fa fa-wrench me-1"></i> 3. Manufacturing Support</td></tr>
                                <tr>
                                    <td>Consumables</td>
                                    <td class="text-end">Rp ${fn(d.cons_meter)}</td>
                                    <td class="text-end">Rp ${fn(d.cons_kg)}</td>
                                </tr>
                                <tr>
                                    <td>Scrap</td>
                                    <td class="text-end">Rp ${fn(d.scrap_meter)}</td>
                                    <td class="text-end">Rp ${fn(d.scrap_kg)}</td>
                                </tr>
                                <tr>
                                    <td>Coordination Cost</td>
                                    <td class="text-end">Rp ${fn(d.koord_meter)}</td>
                                    <td class="text-end">Rp ${fn(d.koord_kg)}</td>
                                </tr>
                                <tr class="fw-bold bg-light">
                                    <td>Subtotal Manufacturing Support</td>
                                    <td class="text-end text-primary">Rp ${fn(d.support_meter)}</td>
                                    <td class="text-end text-primary">Rp ${fn(d.support_kg)}</td>
                                </tr>

                                <tr class="table-secondary fw-bold fs-6">
                                    <td>TOTAL HPP (Material + Process Cost + Support)</td>
                                    <td class="text-end">Rp ${fn(d.hpp_meter)}</td>
                                    <td class="text-end">Rp ${fn(d.hpp_kg)}</td>
                                </tr>

                                <tr class="table-light fw-bold"><td colspan="3"><i class="fa fa-pie-chart me-1"></i> 4. Other Cost (% &times; HPP)</td></tr>
                                <tr><td>${lbl_sales}</td><td class="text-end">Rp ${fn(d.other_sales_meter)}</td><td class="text-end">Rp ${fn(d.other_sales_kg)}</td></tr>
                                <tr><td>${lbl_trans}</td><td class="text-end">Rp ${fn(d.other_trans_meter)}</td><td class="text-end">Rp ${fn(d.other_trans_kg)}</td></tr>
                                <tr><td>${lbl_fa}</td><td class="text-end">Rp ${fn(d.other_fa_meter)}</td><td class="text-end">Rp ${fn(d.other_fa_kg)}</td></tr>
                                <tr><td>${lbl_mgmt}</td><td class="text-end">Rp ${fn(d.other_mgmt_meter)}</td><td class="text-end">Rp ${fn(d.other_mgmt_kg)}</td></tr>
                                <tr><td>${lbl_int}</td><td class="text-end">Rp ${fn(d.other_int_meter)}</td><td class="text-end">Rp ${fn(d.other_int_kg)}</td></tr>
                                <tr><td>${lbl_prof}</td><td class="text-end">Rp ${fn(d.other_prof_meter)}</td><td class="text-end">Rp ${fn(d.other_prof_kg)}</td></tr>
                                <tr class="fw-bold bg-light">
                                    <td>Total Other Cost</td>
                                    <td class="text-end text-primary">Rp ${fn(d.other_total_meter)}</td>
                                    <td class="text-end text-primary">Rp ${fn(d.other_total_kg)}</td>
                                </tr>

                                <tr class="table-warning fw-bold fs-6">
                                    <td>BOTTOM PRICE (HPP + Other Cost)</td>
                                    <td class="text-end text-dark">Rp ${fn(d.bottom_meter)}</td>
                                    <td class="text-end text-dark">Rp ${fn(d.bottom_kg)}</td>
                                </tr>
                                <tr>
                                    <td>Negotiation Allowance (Extra Margin ${(d.nego_pct * 100).toFixed(2)}% &times; Bottom Price)</td>
                                    <td class="text-end fw-bold text-muted">Rp ${fn(d.nego_meter)}</td>
                                    <td class="text-end fw-bold text-muted">Rp ${fn(d.nego_kg)}</td>
                                </tr>

                                <tr class="table-success fw-bold fs-5">
                                    <td>PRICE LIST (Bottom Price + Extra Margin)</td>
                                    <td class="text-end text-success">Rp ${fn(d.pricelist_meter)}</td>
                                    <td class="text-end text-success">Rp ${fn(d.pricelist_kg)}</td>
                                </tr>
                                <tr class="table-info fw-bold">
                                    <td>PRODUCT PRICE LIST (${d.ukuran} m &times; Price List/m)</td>
                                    <td colspan="2" class="text-end text-primary fs-5">Rp ${fn(d.pricelist_pcs)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
                    $('#detail-body-content').html(html);
                },
                error: function(xhr) {
                    $('#detail-body-content').html('<div class="alert alert-danger">Failed to load costing details (Status: ' + xhr.status + ')</div>');
                }
            });
        });
    });
</script>