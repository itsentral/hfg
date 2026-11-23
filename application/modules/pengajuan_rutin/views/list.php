<?php
$ENABLE_ADD     = has_permission('Pengajuan_Pembayaran_Rutin.Add');
$ENABLE_MANAGE  = has_permission('Pengajuan_Pembayaran_Rutin.Manage');
$ENABLE_VIEW    = has_permission('Pengajuan_Pembayaran_Rutin.View');
$ENABLE_DELETE  = has_permission('Pengajuan_Pembayaran_Rutin.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" style="display: none;" role="alert"></div>

<!-- Main Card List -->
<div class="card border-0 shadow-sm mb-4" id="card-list">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="row align-items-center g-3">
            <div class="col-md-7 col-12">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="fa fa-calendar-days fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title fw-bold text-dark mb-0">Periodic (Routine) Payment Request</h5>
                        <small class="text-muted">Manage department periodic / routine operational payment requests</small>
                    </div>
                </div>
            </div>
            <div class="col-md-5 col-12 text-md-end">
                <?php if ($ENABLE_ADD) : ?>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-success dropdown-toggle shadow-sm px-3" type="button" id="dropdownNew" data-bs-toggle="dropdown" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-plus-circle me-1"></i> Tambah Pengajuan Baru
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" aria-labelledby="dropdownNew" style="max-height: 350px; overflow-y: auto;">
                            <li class="dropdown-header fw-bold text-primary small text-uppercase px-3">
                                <i class="fa fa-building me-1"></i> Pilih Departemen
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <?php if (!empty($datdept)) : ?>
                                <?php foreach ($datdept as $key => $val) : ?>
                                    <li>
                                        <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="new_data('<?= $key ?>')">
                                            <i class="fa fa-arrow-right text-muted small"></i>
                                            <span><?= htmlspecialchars((string) $val) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <li><span class="dropdown-item text-muted small">No departments</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-striped table-hover align-middle border mb-0" style="width: 100%;">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th class="text-center" width="40">#</th>
                        <th>Department</th>
                        <th class="text-center" width="160">Document No.</th>
                        <th class="text-end" width="130">Nominal</th>
                        <th class="text-center" width="120">Date</th>
                        <th class="text-center" width="140">Status</th>
                        <th>Reject Description</th>
                        <th class="text-center" width="110">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Dynamic Form Container for Add / Edit / View -->
<div id="form-data" style="display: none;"></div>

<!-- DataTables & Libraries -->
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script type="text/javascript">
    var url_add = "";
    var url_add_def = siteurl + 'pengajuan_rutin/create/';
    var url_edit = siteurl + 'pengajuan_rutin/edit/';
    var url_delete = siteurl + 'pengajuan_rutin/hapus_data/';
    var url_view = siteurl + 'pengajuan_rutin/view/';
    var datatables;

    $(document).ready(function() {
        initDatatables();
    });

    function initDatatables() {
        if ($.fn.DataTable.isDataTable('#mytabledata')) {
            $('#mytabledata').DataTable().destroy();
        }

        datatables = $('#mytabledata').DataTable({
            serverSide: true,
            processing: true,
            paging: true,
            stateSave: true,
            destroy: true,
            ajax: {
                type: 'POST',
                url: siteurl + active_controller + 'get_pengajuan_periodik',
                cache: false,
                dataType: 'json'
            },
            columns: [
                { data: 'no', className: 'text-center' },
                { data: 'department' },
                { data: 'nomor', className: 'text-center fw-semibold text-primary' },
                { data: 'nominal', className: 'text-end fw-bold' },
                { data: 'tanggal', className: 'text-center' },
                { data: 'status', className: 'text-center' },
                { data: 'keterangan_reject' },
                { data: 'action', className: 'text-center', orderable: false, searchable: false }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search document no / department...",
                lengthMenu: "_MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Menampilkan 0 data",
                emptyTable: "No periodic payment requests",
                zeroRecords: "No matching data",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading data...'
            }
        });
    }

    function list_view() {
        $('#form-data').hide().empty();
        $('#card-list, .box').fadeIn(200);
        if (datatables) {
            datatables.ajax.reload(null, false);
        }
    }

    function cancel() {
        list_view();
    }

    function new_data(key) {
        url_add = url_add_def + key;
        data_add();
    }

    function data_add() {
        $('#card-list, .box').hide();
        $('#form-data').show().html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Loading new request form...</div></div>');
        $('#form-data').load(url_add, function(response, status, xhr) {
            if (status == "error") {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to load form: ' + xhr.status + ' ' + xhr.statusText });
                } else {
                    alert('Failed to load form: ' + xhr.status + ' ' + xhr.statusText);
                }
                list_view();
            }
        });
    }

    function data_edit(id) {
        if (id != "") {
            $('#card-list, .box').hide();
            $('#form-data').show().html('<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div><div class="mt-2 text-muted">Loading edit form...</div></div>');
            $('#form-data').load(url_edit + id, function(response, status, xhr) {
                if (status == "error") {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to load form: ' + xhr.status + ' ' + xhr.statusText });
                    } else {
                        alert('Failed to load form: ' + xhr.status + ' ' + xhr.statusText);
                    }
                    list_view();
                }
            });
        }
    }

    function data_view(id) {
        if (id != "") {
            $('#card-list, .box').hide();
            $('#form-data').show().html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div><div class="mt-2 text-muted">Loading data details...</div></div>');
            $('#form-data').load(url_view + id, function(response, status, xhr) {
                if (status == "error") {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to load details: ' + xhr.status + ' ' + xhr.statusText });
                    } else {
                        alert('Failed to load details: ' + xhr.status + ' ' + xhr.statusText);
                    }
                    list_view();
                }
            });
        }
    }

    function data_delete(id) {
        var doDelete = function() {
            $.ajax({
                url: url_delete + id,
                dataType: "json",
                type: 'POST',
                success: function(msg) {
                    if (msg['delete'] == '1') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Data deleted successfully', timer: 1500, showConfirmButton: false });
                        } else {
                            swal({ title: 'Deleted!', text: 'Data deleted successfully', type: 'success', timer: 1500, showConfirmButton: false });
                        }
                        if (datatables) {
                            datatables.ajax.reload(null, false);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Failed!', text: 'Failed to delete data' });
                        } else {
                            swal({ title: 'Failed!', text: 'Failed to delete data', type: 'error' });
                        }
                    }
                },
                error: function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to execute deletion' });
                    } else {
                        swal({ title: 'Error!', text: 'Failed to execute deletion', type: 'error' });
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: "Are you sure?",
                text: "This periodic request data will be deleted permanently!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, Delete!",
                cancelButtonText: "Cancel"
            }).then(function(result) {
                if (result.isConfirmed) {
                    doDelete();
                }
            });
        } else {
            swal({
                title: "Are you sure?",
                text: "The Data Will Be Deleted!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Delete!",
                cancelButtonText: "Cancel",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    doDelete();
                }
            });
        }
    }
</script>
