<?php
$ENABLE_ADD     = has_permission('Approval Expense Management.Add');
$ENABLE_MANAGE  = has_permission('Approval Expense Management.Manage');
$ENABLE_VIEW    = has_permission('Approval Expense Management.View');
$ENABLE_DELETE  = has_permission('Approval Expense Management.Delete');
?>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<div class="card shadow-sm border-0 mb-4" id="card-list">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-sm bg-light-primary text-primary me-2 rounded-2 d-flex align-items-center justify-content-center p-2">
                <i class="fa fa-user-check fs-5"></i>
            </div>
            <div>
                <h5 class="card-title mb-0 fw-bold text-dark">Persetujuan Management Pengajuan Expense</h5>
                <small class="text-muted">Daftar pengajuan expense yang menunggu persetujuan level Management</small>
            </div>
        </div>
        <div>
            <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm">
                <i class="fa fa-clock me-1"></i>Menunggu Approval Management
            </span>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-hover table-striped align-middle border w-100">
                <thead class="table-light text-center">
                    <tr>
                        <th width="4%" class="text-center">#</th>
                        <th class="text-center">No Dokumen</th>
                        <th class="text-center">Tanggal</th>
                        <th>Nama Pemohon</th>
                        <th>Keterangan</th>
                        <th class="text-end">Total Nominal</th>
                        <th class="text-center" width="18%">Status</th>
                        <th class="text-center no-sort" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Dynamic Form Container for View & Approval -->
<div id="form-data" style="display: none;"></div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var url_view     = siteurl + 'expense/view/';
    var url_approval = siteurl + 'expense/approval/';
    var datatables;

    $(document).ready(function() {
        initDataTable();

        // SPA Form View Click
        $(document).on('click', '#mytabledata .view', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (id) {
                data_view(id);
            } else {
                window.location.href = $(this).attr('href');
            }
        });

        // SPA Form Approve Click
        $(document).on('click', '#mytabledata .approve', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (id) {
                data_approve(id);
            } else {
                window.location.href = $(this).attr('href');
            }
        });
    });

    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#mytabledata')) {
            $('#mytabledata').DataTable().destroy();
        }

        datatables = $('#mytabledata').DataTable({
            serverSide: true,
            processing: true,
            stateSave: true,
            paging: true,
            order: [[1, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari no dokumen, pemohon, keterangan...",
                lengthMenu: "_MENU_ data per halaman",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                emptyTable: "Tidak ada data pengajuan expense yang menunggu persetujuan management",
                zeroRecords: "Tidak ada data yang cocok",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Sedang memuat data...'
            },
            ajax: {
                type: 'POST',
                url: siteurl + active_controller + 'get_dat_approval_expense_manage',
                cache: false,
                dataType: 'json',
                error: function(xhr, status, error) {
                    console.error("DataTable AJAX error: " + status + ": " + error);
                }
            },
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            columns: [
                { data: 'no', className: 'text-center' },
                { data: 'no_doc', className: 'text-center' },
                { data: 'tgl_doc', className: 'text-center' },
                { data: 'nmuser', className: 'fw-medium' },
                { data: 'informasi', className: '' },
                { data: 'nominal', className: 'text-end' },
                { data: 'status', className: 'text-center' },
                { data: 'action', className: 'text-center', orderable: false, searchable: false }
            ]
        });
    }

    function list_view() {
        $('#form-data').hide().empty();
        $('#card-list, .box').fadeIn(200);
        if (datatables) {
            datatables.ajax.reload(null, false);
        }
    }

    function data_view(id) {
        if (id != "") {
            $('#card-list, .box').hide();
            $('#form-data').show().html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div><div class="mt-2 text-muted">Memuat rincian dokumen expense...</div></div>');
            $('#form-data').load(url_view + id, function(response, status, xhr) {
                if (status == "error") {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Gagal memuat detail data: " + xhr.status + " " + xhr.statusText
                    });
                    list_view();
                }
            });
        }
    }

    function data_approve(id) {
        if (id != "") {
            $('#card-list, .box').hide();
            $('#form-data').show().html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Memuat form persetujuan management...</div></div>');
            $('#form-data').load(url_approval + id, function(response, status, xhr) {
                if (status == "error") {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Gagal memuat form approval: " + xhr.status + " " + xhr.statusText
                    });
                    list_view();
                }
            });
        }
    }
</script>
