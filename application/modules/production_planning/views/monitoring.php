<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Monitoring Production Plan</h5>
        <a href="<?= base_url('production_planning/add') ?>" class="btn btn-success btn-sm">
            <i class="fa fa-plus"></i> Buat Plan Baru
        </a>
    </div>
    <div class="card-body">
        <!-- Filter Status -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Filter Status</label>
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="Draft">Draft</option>
                    <option value="Released">Released</option>
                    <option value="Closed">Closed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" id="filter-tgl-dari" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" id="filter-tgl-sampai" class="form-control form-control-sm">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary btn-sm me-2" id="btn-filter">
                    <i class="fa fa-search"></i> Filter
                </button>
                <button class="btn btn-secondary btn-sm" id="btn-reset-filter">
                    <i class="fa fa-undo"></i> Reset
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-3" id="summary-cards">
            <div class="col-md-3">
                <div class="card bg-secondary text-white text-center py-2">
                    <div class="fs-4 fw-bold" id="cnt-draft">-</div>
                    <div class="small">Draft</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white text-center py-2">
                    <div class="fs-4 fw-bold" id="cnt-released">-</div>
                    <div class="small">Released</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white text-center py-2">
                    <div class="fs-4 fw-bold" id="cnt-closed">-</div>
                    <div class="small">Closed</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white text-center py-2">
                    <div class="fs-4 fw-bold" id="cnt-cancelled">-</div>
                    <div class="small">Cancelled</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tbl-monitoring" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th class="text-center">No Plan</th>
                        <th class="text-center">Tgl Plan</th>
                        <th class="text-center">Produk FG</th>
                        <th class="text-center">Target Qty</th>
                        <th class="text-center">Status</th>
                        <th class="text-center no-sort" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/sweetalert/dist/sweetalert2.min.js'); ?>"></script>

<script>
var dtMonitoring;

function loadSummary() {
    // Hitung summary dari data yang sudah ada di tabel
    var counts = { Draft: 0, Released: 0, Closed: 0, Cancelled: 0 };
    $('#tbl-monitoring tbody tr').each(function () {
        var badge = $(this).find('.badge').text().trim();
        if (counts.hasOwnProperty(badge)) counts[badge]++;
    });
    $('#cnt-draft').text(counts.Draft);
    $('#cnt-released').text(counts.Released);
    $('#cnt-closed').text(counts.Closed);
    $('#cnt-cancelled').text(counts.Cancelled);
}

$(document).ready(function () {
    dtMonitoring = $('#tbl-monitoring').DataTable({
        processing: true,
        serverSide: true,
        stateSave: false,
        autoWidth: false,
        destroy: true,
        responsive: true,
        aaSorting: [[1, 'desc']],
        columnDefs: [{ targets: 'no-sort', orderable: false }],
        sPaginationType: 'simple_numbers',
        iDisplayLength: 25,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: siteurl + 'production_planning/data_side_plan',
            type: 'POST',
            data: function (d) {
                d.filter_status   = $('#filter-status').val();
                d.filter_tgl_dari = $('#filter-tgl-dari').val();
                d.filter_tgl_sampai = $('#filter-tgl-sampai').val();
            },
            cache: false
        },
        drawCallback: function () {
            loadSummary();
        }
    });

    $('#btn-filter').on('click', function () {
        dtMonitoring.ajax.reload();
    });

    $('#btn-reset-filter').on('click', function () {
        $('#filter-status').val('');
        $('#filter-tgl-dari').val('');
        $('#filter-tgl-sampai').val('');
        dtMonitoring.ajax.reload();
    });

    // Click pada badge status untuk filter cepat
    $(document).on('click', '#summary-cards .card', function () {
        var status = $(this).find('.small').text().trim();
        $('#filter-status').val(status);
        dtMonitoring.ajax.reload();
    });

    // Release plan
    $(document).on('click', '.btn-release', function () {
        var plan_no = $(this).data('plan');
        Swal.fire({
            title: 'Release Plan?',
            text: 'Plan akan diubah ke status Released.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Release',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: siteurl + 'production_planning/process_release/' + plan_no,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({ title: 'Berhasil!', text: 'Plan berhasil di-release.', icon: 'success', timer: 1500, showConfirmButton: false })
                            .then(function(){ dtMonitoring.ajax.reload(); });
                    } else {
                        Swal.fire({ title: 'Gagal!', text: res.message || 'Terjadi kesalahan.', icon: 'error', confirmButtonText: 'OK' });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ title: 'Error!', text: 'Request gagal: ' + xhr.status, icon: 'error', confirmButtonText: 'OK' });
                }
            });
        });
    });

    // Cancel plan
    $(document).on('click', '.btn-cancel', function () {
        var plan_no = $(this).data('plan');
        Swal.fire({
            title: 'Batalkan Plan?',
            text: 'Plan akan dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tidak'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: siteurl + 'production_planning/process_cancel/' + plan_no,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({ title: 'Berhasil!', text: 'Plan berhasil dibatalkan.', icon: 'success', timer: 1500, showConfirmButton: false })
                            .then(function(){ dtMonitoring.ajax.reload(); });
                    } else {
                        Swal.fire({ title: 'Gagal!', text: res.message || 'Terjadi kesalahan.', icon: 'error', confirmButtonText: 'OK' });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ title: 'Error!', text: 'Request gagal: ' + xhr.status, icon: 'error', confirmButtonText: 'OK' });
                }
            });
        });
    });
});
</script>
