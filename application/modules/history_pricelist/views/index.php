<?php
$ENABLE_MANAGE = has_permission('History_Pricelist.Manage');
$ENABLE_VIEW   = has_permission('History_Pricelist.View');
$has_filters   = !empty($filters['code_lv2']) || !empty($filters['no_pricelist'])
    || !empty($filters['start_date']) || !empty($filters['end_date']);
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .history-pricelist-page .page-card { border: 0; border-radius: 12px; overflow: hidden; }
    .history-pricelist-page .page-subtitle { max-width: 720px; line-height: 1.5; }
    .history-pricelist-page .filter-panel {
        background: #f8fafc;
        border: 1px solid #e7ebf0;
        border-radius: 10px;
    }
    .history-pricelist-page .form-label {
        color: #344054;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .history-pricelist-page .form-control,
    .history-pricelist-page .form-select,
    .history-pricelist-page .select2-container .select2-selection--single {
        min-height: 38px;
        border-color: #d0d5dd;
        border-radius: 7px;
    }
    .history-pricelist-page .select2-container { width: 100% !important; }
    .history-pricelist-page .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
        color: #344054;
    }
    .history-pricelist-page .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 5px;
    }
    .history-pricelist-page .select2-container--default.select2-container--focus .select2-selection--single,
    .history-pricelist-page .select2-container--default.select2-container--open .select2-selection--single,
    .history-pricelist-page .form-control:focus {
        border-color: #4680ff;
        box-shadow: 0 0 0 0.15rem rgba(70, 128, 255, 0.15);
    }
    .history-pricelist-page .flatpickr-input[readonly] { background: #fff; cursor: pointer; }
    .history-pricelist-page .input-group .flatpickr-input {
        min-width: 0;
        width: 1%;
        flex: 1 1 auto;
        font-size: 12px;
    }
    .history-pricelist-page .input-group-text {
        min-width: 40px;
        justify-content: center;
        padding-left: 10px;
        padding-right: 10px;
    }
    .history-pricelist-page .table thead th {
        background: #344054;
        border-color: #475467;
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .02em;
        padding: 12px 10px;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }
    .history-pricelist-page .table tbody td {
        border-color: #eaecf0;
        padding: 11px 10px;
        vertical-align: middle;
    }
    .history-pricelist-page .price-new {
        background: #f0fdf4 !important;
        color: #15803d;
        font-weight: 700;
    }
    .history-pricelist-page .product-code,
    .history-pricelist-page .approval-meta {
        color: #667085;
        font-size: 10px;
        margin-top: 2px;
    }
    .history-pricelist-page .filter-actions { min-height: 38px; }
    @media (max-width: 767.98px) {
        .history-pricelist-page .header-actions,
        .history-pricelist-page .filter-actions { width: 100%; }
        .history-pricelist-page .header-actions .btn,
        .history-pricelist-page .filter-actions .btn { flex: 1 1 auto; }
    }
</style>

<div class="container-fluid py-3 history-pricelist-page">
    <div class="card page-card shadow-sm">
        <div class="card-header bg-white border-bottom px-3 px-md-4 py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width:36px;height:36px;">
                            <i class="fa fa-clock"></i>
                        </span>
                        <h5 class="mb-0 fw-bold">History Price List</h5>
                    </div>
                    <div class="text-muted small page-subtitle">
                        Approval history for Price List documents. This page is read-only and serves as an audit trail.
                    </div>
                </div>

                <?php if ($ENABLE_MANAGE): ?>
                    <div class="d-flex header-actions">
                        <a href="<?= base_url('history_pricelist/export_excel?' . http_build_query($filters)) ?>" class="btn btn-success btn-sm px-3">
                            <i class="fa fa-file-excel-o me-1"></i> Export CSV
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body px-3 px-md-4 pt-3 pb-0">
            <div class="filter-panel p-3">
                <form method="GET" action="<?= base_url('history_pricelist') ?>" class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="filter-category" class="form-label">
                            <i class="fa fa-filter text-muted me-1"></i>Category
                        </label>
                        <select id="filter-category" name="category" class="form-select select2-filter" data-placeholder="All Categories">
                            <option value=""></option>
                            <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                                <option value="<?= html_escape($cat->code_lv2) ?>" <?= ($filters['code_lv2'] == $cat->code_lv2) ? 'selected' : '' ?>>
                                    <?= html_escape($cat->nama) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="filter-pricelist" class="form-label">Pricelist Document</label>
                        <select id="filter-pricelist" name="no_pricelist" class="form-select select2-filter" data-placeholder="All Documents">
                            <option value=""></option>
                            <?php if (!empty($pricelists)): foreach ($pricelists as $pl): ?>
                                <option value="<?= html_escape($pl->no_pricelist) ?>" <?= ($filters['no_pricelist'] == $pl->no_pricelist) ? 'selected' : '' ?>>
                                    <?= html_escape($pl->no_pricelist) ?> — <?= date('d/m/Y', strtotime((string) $pl->event_at)) ?> (<?= (int) $pl->total_items ?> items)
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-2">
                        <label for="filter-start-date" class="form-label">Start Date</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                            <input type="text" id="filter-start-date" name="start_date" class="form-control flatpickr-date" placeholder="Start date" autocomplete="off" value="<?= html_escape($filters['start_date']) ?>">
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-2">
                        <label for="filter-end-date" class="form-label">End Date</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                            <input type="text" id="filter-end-date" name="end_date" class="form-control flatpickr-date" placeholder="End date" autocomplete="off" value="<?= html_escape($filters['end_date']) ?>">
                        </div>
                    </div>

                    <div class="col-12 col-xl-2">
                        <div class="d-flex align-items-center gap-2 filter-actions">
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fa fa-search me-1"></i> Apply
                            </button>
                            <?php if ($has_filters): ?>
                                <a href="<?= base_url('history_pricelist') ?>" class="btn btn-outline-secondary btn-sm px-3">
                                    <i class="fa fa-refresh me-1"></i> Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body px-3 px-md-4 pt-4">
            <div class="table-responsive">
                <table id="tbl-history-pricelist" class="table table-bordered table-hover align-middle w-100" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Approval Time</th>
                            <th>Product Name</th>
                            <th class="text-center">Variant (Uniq ID)</th>
                            <th>Category</th>
                            <th class="text-end">Old Price List (/m)</th>
                            <th class="text-end">New Price List (/m)</th>
                            <th class="text-end">Change</th>
                            <th class="text-end">Bottom Price (/m)</th>
                            <th class="text-center">Extra Margin</th>
                            <th class="text-center">Pricelist No.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($list)): $no = 1; foreach ($list as $r):
                            $delta = (float) $r->price_list_baru_meter - (float) $r->price_list_lama_meter;
                            $pct = ((float) $r->price_list_lama_meter > 0)
                                ? ($delta / (float) $r->price_list_lama_meter) * 100 : 0;
                            $delta_class = $delta >= 0 ? 'text-success' : 'text-danger';
                            $event_time = strtotime((string) $r->event_at);
                        ?>
                            <tr>
                                <td class="text-center text-muted fw-semibold"><?= $no++ ?></td>
                                <td class="text-center" data-order="<?= (int) $event_time ?>">
                                    <div class="fw-semibold text-dark"><?= date('d M Y', $event_time) ?></div>
                                    <div class="approval-meta"><?= date('H:i', $event_time) ?> · <?= html_escape($r->event_by_name ?: '-') ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= html_escape($r->nm_product ?: $r->code_lv4) ?></div>
                                    <div class="product-code">Code: <?= html_escape($r->code_lv4) ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= html_escape($r->no_uniq_id) ?></span>
                                </td>
                                <td><?= html_escape($r->nm_category ?: '-') ?></td>
                                <td class="text-end text-nowrap">Rp <?= number_format((float) $r->price_list_lama_meter, 2) ?></td>
                                <td class="text-end text-nowrap price-new">
                                    Rp <?= number_format((float) $r->price_list_baru_meter, 2) ?>
                                    <div class="product-code">Rp <?= number_format((float) $r->price_list_baru_kg, 2) ?>/kg</div>
                                </td>
                                <td class="text-end text-nowrap fw-semibold <?= $delta_class ?>">
                                    <?= $delta >= 0 ? '+' : '' ?>Rp <?= number_format($delta, 2) ?>
                                    <div class="product-code <?= $delta_class ?>"><?= $pct >= 0 ? '+' : '' ?><?= number_format($pct, 2) ?>%</div>
                                </td>
                                <td class="text-end text-nowrap">Rp <?= number_format((float) $r->bottom_price_meter, 2) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border"><?= number_format((float) $r->rate_negotiation_allowance * 100, 1) ?>%</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><?= html_escape($r->no_pricelist) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2-filter').each(function() {
            $(this).select2({
                width: '100%',
                allowClear: true,
                placeholder: $(this).data('placeholder')
            });
        });
    }

    if (typeof flatpickr !== 'undefined') {
        flatpickr('.flatpickr-date', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            allowInput: true,
            disableMobile: true
        });
    }

    $('#tbl-history-pricelist').DataTable({
        responsive: true,
        ordering: true,
        pageLength: 25,
        order: [[1, 'desc']],
        autoWidth: false,
        columnDefs: [
            { orderable: false, targets: [0, 9, 10] }
        ],
        language: {
            search: 'Search product, variant, or Pricelist No.:',
            zeroRecords: 'No Price List approval history found',
            info: 'Showing _START_ to _END_ of _TOTAL_ history entries',
            infoEmpty: 'Showing 0 history entries',
            infoFiltered: '(filtered from _MAX_ total entries)'
        }
    });
});
</script>
