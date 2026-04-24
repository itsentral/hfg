<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-sitemap"></i> Daftar BOM (Bill of Material)</h5>
        <a href="<?= base_url('bom/add') ?>" class="btn btn-success btn-sm">
            <i class="fa fa-plus"></i> Buat BOM Baru
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbl-bom" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Jumlah Material</th>
                        <th class="text-center">Dibuat</th>
                        <th class="text-center no-sort" width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function () {
    $('#tbl-bom').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        autoWidth: false,
        destroy: true,
        responsive: true,
        aaSorting: [[4, 'desc']],
        columnDefs: [{ targets: 'no-sort', orderable: false }],
        sPaginationType: 'simple_numbers',
        iDisplayLength: 10,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: siteurl + 'bom/data_side_bom',
            type: 'POST',
            cache: false
        }
    });

    // Delete BOM
    $(document).on('click', '.btn-delete-bom', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus BOM?',
            text: 'BOM dan semua detail material akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: siteurl + 'bom/delete_bom',
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function (res) {
                    if (res.success) {
                        Swal.fire({ title: 'Berhasil!', text: 'BOM berhasil dihapus.', icon: 'success', timer: 1500, showConfirmButton: false })
                            .then(function () { $('#tbl-bom').DataTable().ajax.reload(); });
                    } else {
                        Swal.fire({ title: 'Gagal!', text: 'Terjadi kesalahan.', icon: 'error', confirmButtonText: 'OK' });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ title: 'Error!', text: 'Request gagal: ' + xhr.status, icon: 'error', confirmButtonText: 'OK' });
                }
            });
        });
    });
});
</script>
