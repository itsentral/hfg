<?php
$ENABLE_ADD    = has_permission('Unpack_Coil.Add');
$ENABLE_MANAGE = has_permission('Unpack_Coil.Manage');
?>

<style>
    .swal2-container {
        z-index: 999999 !important;
    }

    .swal2-popup {
        z-index: 1000000 !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    .dropdown-menu {
        z-index: 9999 !important;
        position: fixed !important;
    }

    .card,
    .card-body {
        overflow: visible !important;
    }

    #table-unpack tbody td {
        overflow: visible !important;
    }

    .dataTables_wrapper {
        overflow: visible !important;
    }

    #table-unpack {
        margin-bottom: 60px !important;
    }
</style>

<div class="card">
    <div class="card-body">
        <?php if ($ENABLE_ADD): ?>
            <div class="d-flex justify-content-end mb-3">
                <a href="<?= site_url('unpack_coil/add') ?>" class="btn btn-success">
                    <i class="fa fa-plus me-1"></i> Add Report Unpack
                </a>
            </div>
        <?php endif; ?>

        <table id="table-unpack" class="table table-bordered table-striped" width="100%">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Unpack No</th>
                    <th>Pack</th>
                    <th width="9%">Jumlah Material</th>
                    <th width="9%">Jumlah Coil (Roll)</th>
                    <th width="13%">Nett Weight Total</th>
                    <th width="10%">Tgl Unpack</th>
                    <th width="9%">Status</th>
                    <th width="8%">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        const BASE_URL = siteurl + active_controller;

        var tableUnpack = $('#table-unpack').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + '/data_side',
                type: 'GET'
            },
            columns: [{
                    data: 0,
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 1,
                    className: 'text-nowrap'
                },
                {
                    data: 2
                },
                {
                    data: 3,
                    className: 'text-center'
                },
                {
                    data: 4,
                    className: 'text-center'
                },
                {
                    data: 5,
                    className: 'text-end'
                },
                {
                    data: 6,
                    className: 'text-center'
                },
                {
                    data: 7,
                    className: 'text-center'
                },
                {
                    data: 8,
                    orderable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [1, 'desc']
            ]
        });

        // Fix dropdown position di dalam DataTable
        $(document).on('show.bs.dropdown', '#table-unpack .dropdown', function() {
            var $btn = $(this).find('[data-bs-toggle="dropdown"]');
            var $menu = $(this).find('.dropdown-menu');
            var btnRect = $btn[0].getBoundingClientRect();
            $menu.css({
                position: 'fixed',
                top: btnRect.bottom + 'px',
                left: (btnRect.right - $menu.outerWidth()) + 'px',
                transform: 'none'
            });
        });

        // Lock report unpack
        $(document).on('click', '.btn-lock-unpack', function() {
            var unpackNo = $(this).data('unpack');

            Swal.fire({
                icon: 'warning',
                title: 'Lock Report Unpack?',
                text: 'Report ' + unpackNo + ' akan dikunci dan tidak bisa diedit lagi. Lanjutkan?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lock',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: BASE_URL + '/lock',
                    type: 'POST',
                    data: {
                        unpack_no: unpackNo
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            tableUnpack.ajax.reload(null, false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan jaringan.'
                        });
                    }
                });
            });
        });

        // Delete report unpack
        $(document).on('click', '.btn-delete-unpack', function() {
            var unpackNo = $(this).data('unpack');

            Swal.fire({
                icon: 'warning',
                title: 'Hapus Report Unpack?',
                text: 'Report ' + unpackNo + ' akan dihapus. Lanjutkan?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: BASE_URL + '/delete',
                    type: 'POST',
                    data: {
                        unpack_no: unpackNo
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            tableUnpack.ajax.reload(null, false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan jaringan.'
                        });
                    }
                });
            });
        });
    });
</script>