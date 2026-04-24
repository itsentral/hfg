<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Production Plan</h5>
        <a href="<?= base_url('production_planning/add') ?>" class="btn btn-success btn-sm">
            <i class="fa fa-plus"></i> Buat Plan Baru
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbl-plan" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th class="text-center">No Plan</th>
                        <th class="text-center">Tgl Plan</th>
                        <th class="text-center">Produk FG</th>
                        <th class="text-center">Target Qty</th>
                        <th class="text-center">Status</th>
                        <th class="text-center no-sort" width="160">Aksi</th>
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

<script>
    $(document).ready(function() {
        $('#tbl-plan').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            autoWidth: false,
            destroy: true,
            responsive: true,
            aaSorting: [
                [1, 'desc']
            ],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            sPaginationType: 'simple_numbers',
            iDisplayLength: 10,
            aLengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            ajax: {
                url: siteurl + 'production_planning/data_side_plan',
                type: 'POST',
                cache: false
            }
        });

        // Release plan
        $(document).on('click', '.btn-release', function() {
            var plan_no = $(this).data('plan');

            Swal.fire({
                title: 'Release Plan?',
                text: 'Plan akan diubah ke status Released dan tidak bisa diedit lagi.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Release',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (!result.value) return; // ✅ Fix: ganti dari result.isConfirmed

                $.ajax({
                    url: siteurl + 'production_planning/process_release/' + plan_no,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Plan berhasil di-release.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function() {
                                $('#tbl-plan').DataTable().ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: res.message || 'Terjadi kesalahan.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Request gagal: ' + xhr.status,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });

        // Cancel plan
        $(document).on('click', '.btn-cancel', function() {
            var plan_no = $(this).data('plan');
            Swal.fire({
                title: 'Batalkan Plan?',
                text: 'Plan akan dibatalkan dan tidak dapat diproses lebih lanjut.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tidak'
            }).then(function(result) {
                if (!result.value) return;
                $.ajax({
                    url: siteurl + 'production_planning/process_cancel/' + plan_no,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Plan berhasil dibatalkan.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                })
                                .then(function() {
                                    $('#tbl-plan').DataTable().ajax.reload();
                                });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: res.message || 'Terjadi kesalahan.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Request gagal: ' + xhr.status,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    });
</script>