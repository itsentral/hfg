<?php
$ENABLE_ADD = isset($addPermission) ? $addPermission : false;
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card shadow-sm border-0 mb-4" id="card-list">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-sm text-primary me-2 rounded-2 d-flex align-items-center justify-content-center p-2" style="width: 40px; height: 40px; background-color: rgba(13, 110, 253, 0.1);">
                <i class="fa fa-money fs-5"></i>
            </div>
            <div>
                <h5 class="card-title mb-0 fw-bold text-dark">Master Petty Cash</h5>
                <small class="text-muted">Kelola master data petty cash dan alokasi anggaran COA</small>
            </div>
        </div>
        <div>
            <?php if ($ENABLE_ADD) : ?>
                <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm rounded-2" title="Tambah Master Petty Cash" onclick="data_add()">
                    <i class="fa fa-plus me-1"></i> Tambah Master Petty Cash
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-hover table-striped align-middle border w-100">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Nama Petty Cash</th>
                        <th>Keterangan</th>
                        <th width="180" class="text-end">Total Budget</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal CRUD -->
<div class="modal fade" id="modal-crud" tabindex="-1" role="dialog" aria-labelledby="modal-crud-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-2 p-2" id="modal-icon-wrapper" style="width: 36px; height: 36px; background-color: rgba(13, 110, 253, 0.1);">
                        <i class="fa fa-money text-primary" id="modal-crud-icon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modal-crud-label">Master Petty Cash</h5>
                        <small class="text-muted" id="modal-crud-sub">Form master petty cash</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modal-crud-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat...</span>
                    </div>
                    <p class="mt-2 text-muted fw-semibold">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        DataTables();
    });

    /**
     * Initialize server-side DataTable
     */
    function DataTables() {
        $('#mytabledata').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            stateSave: true,
            ajax: {
                url: siteurl + 'petty_cash_master/get_data',
                type: 'POST'
            },
            columns: [{
                    data: 'no',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'nama',
                    render: function(data, type, row) {
                        return '<div class="fw-bold text-dark">' + (data ? data : '-') + '</div>';
                    }
                },
                {
                    data: 'keterangan',
                    render: function(data, type, row) {
                        return '<span class="text-secondary">' + (data ? data : '-') + '</span>';
                    }
                },
                {
                    data: 'total_budget',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-6 font-monospace">Rp ' + data + '</span>';
                    }
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [1, 'asc']
            ]
        });
    }

    /**
     * Open modal and load create form via AJAX
     */
    function data_add() {
        $('#modal-icon-wrapper').css('background-color', 'rgba(13, 110, 253, 0.1)');
        $('#modal-crud-icon').attr('class', 'fa fa-plus text-primary');
        $('#modal-crud-label').text('Tambah Master Petty Cash');
        $('#modal-crud-sub').text('Tambahkan master petty cash baru dan tentukan alokasi COA');
        $('#modal-crud-body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat formulir...</p></div>');
        $('#modal-crud').modal('show');

        $.ajax({
            type: 'GET',
            url: siteurl + 'petty_cash_master/create',
            success: function(response) {
                $('#modal-crud-body').html(response);
            },
            error: function() {
                $('#modal-crud').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal menghubungi server',
                    timer: 3000
                });
            }
        });
    }

    /**
     * Open modal and load edit form via AJAX
     * @param {int} id - Master petty cash ID
     */
    function data_edit(id) {
        $('#modal-icon-wrapper').css('background-color', 'rgba(255, 193, 7, 0.15)');
        $('#modal-crud-icon').attr('class', 'fa fa-pencil text-warning');
        $('#modal-crud-label').text('Edit Master Petty Cash');
        $('#modal-crud-sub').text('Perbarui informasi master petty cash dan rincian alokasi COA');
        $('#modal-crud-body').html('<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat data...</p></div>');
        $('#modal-crud').modal('show');

        $.ajax({
            type: 'GET',
            url: siteurl + 'petty_cash_master/edit/' + id,
            success: function(response) {
                $('#modal-crud-body').html(response);
            },
            error: function() {
                $('#modal-crud').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal menghubungi server',
                    timer: 3000
                });
            }
        });
    }

    /**
     * Open modal and load view (read-only) form via AJAX
     * @param {int} id - Master petty cash ID
     */
    function data_view(id) {
        $('#modal-icon-wrapper').css('background-color', 'rgba(13, 202, 240, 0.15)');
        $('#modal-crud-icon').attr('class', 'fa fa-eye text-info');
        $('#modal-crud-label').text('Detail Master Petty Cash');
        $('#modal-crud-sub').text('Informasi rincian master petty cash dan daftar alokasi COA');
        $('#modal-crud-body').html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat rincian...</p></div>');
        $('#modal-crud').modal('show');

        $.ajax({
            type: 'GET',
            url: siteurl + 'petty_cash_master/view/' + id,
            success: function(response) {
                $('#modal-crud-body').html(response);
            },
            error: function() {
                $('#modal-crud').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal menghubungi server',
                    timer: 3000
                });
            }
        });
    }

    /**
     * Delete master petty cash with SweetAlert2 confirmation
     * @param {int} id - Master petty cash ID
     */
    function data_delete(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-trash me-1"></i> Ya, Hapus!',
            cancelButtonText: '<i class="fa fa-times me-1"></i> Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: siteurl + 'petty_cash_master/delete/' + id,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == '1') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.msg,
                                timer: 2500,
                                showConfirmButton: false
                            }).then(function() {
                                DataTables();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Gagal!',
                                text: response.msg,
                                timer: 3000
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Gagal menghubungi server',
                            timer: 3000
                        });
                    }
                });
            }
        });
    }
</script>