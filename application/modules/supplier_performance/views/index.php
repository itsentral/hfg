<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Filter Summary Kinerja Supplier</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Supplier</label>
                <select id="filter-supplier" class="form-select">
                    <option value="">-- Semua Supplier --</option>
                    <?php foreach ($supplier_list as $sup): ?>
                        <option value="<?= $sup->id_supplier ?>"><?= htmlspecialchars($sup->nm_supplier) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Dari</label>
                <input type="date" id="filter-tgl-dari" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Sampai</label>
                <input type="date" id="filter-tgl-sampai" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button id="btn-filter" class="btn btn-primary w-100">
                    <i class="fa fa-search"></i> Tampilkan
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Summary Kinerja Supplier per Periode</h5>
        <div>
            <a href="<?= base_url('supplier_performance/feed_coil') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-list"></i> Detail per Coil
            </a>
            <a href="<?= base_url('supplier_performance/dashboard') ?>" class="btn btn-sm btn-outline-info ms-1">
                <i class="fa fa-chart-bar"></i> Dashboard
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbl-summary" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Nama Supplier</th>
                        <th class="text-center">Jml Coil</th>
                        <th class="text-end">Total Reject (kg)</th>
                        <th class="text-end">Total NG (kg)</th>
                        <th class="text-end">Total KW2 (kg)</th>
                        <th class="text-end">Total Defect (kg)</th>
                        <th class="text-end">Avg Selisih Net (kg)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
var tblSummary;

$(document).ready(function () {
    tblSummary = $('#tbl-summary').DataTable({
        processing: true,
        serverSide: true,
        stateSave: false,
        autoWidth: false,
        destroy: true,
        responsive: true,
        aaSorting: [[7, 'desc']],
        columnDefs: [
            { targets: [0, 2], className: 'text-center' },
            { targets: [3, 4, 5, 6, 7], className: 'text-end' },
        ],
        sPaginationType: 'simple_numbers',
        iDisplayLength: 25,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: siteurl + 'supplier_performance/data_side_summary',
            type: 'POST',
            cache: false,
            data: function (d) {
                d.id_supplier  = $('#filter-supplier').val();
                d.tgl_dari     = $('#filter-tgl-dari').val();
                d.tgl_sampai   = $('#filter-tgl-sampai').val();
            }
        }
    });

    $('#btn-filter').on('click', function () {
        tblSummary.ajax.reload();
    });
});
</script>
