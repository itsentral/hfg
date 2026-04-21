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
        <h5 class="mb-0">Daftar SPK Produksi</h5>
        <div class="d-flex gap-2">
            <a href="<?= base_url('production_issue/scan_issue') ?>" class="btn btn-warning btn-sm">
                <i class="fa fa-barcode"></i> Scan Issue Material
            </a>
            <a href="<?= base_url('production_issue/monitoring_coil') ?>" class="btn btn-info btn-sm">
                <i class="fa fa-map-marker"></i> Monitoring Coil
            </a>
            <a href="<?= base_url('production_issue/add') ?>" class="btn btn-success btn-sm">
                <i class="fa fa-plus"></i> Buat SPK
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbl-spk" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th class="text-center">No SPK</th>
                        <th class="text-center">Tgl SPK</th>
                        <th class="text-center">No Plan</th>
                        <th class="text-center">Produk FG</th>
                        <th class="text-center">Target Qty</th>
                        <th class="text-center">Status</th>
                        <th class="text-center no-sort" width="130">Aksi</th>
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
$(document).ready(function () {
    $('#tbl-spk').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        autoWidth: false,
        destroy: true,
        responsive: true,
        aaSorting: [[1, 'desc']],
        columnDefs: [{ targets: 'no-sort', orderable: false }],
        sPaginationType: 'simple_numbers',
        iDisplayLength: 10,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: siteurl + 'production_issue/data_side_spk',
            type: 'POST',
            cache: false
        }
    });

    $(document).on('click', '.btn-release-spk', function () {
        var spk_no = $(this).data('spk');
        swal({
            title: 'Release SPK?',
            text: 'SPK akan diubah ke status Released dan siap untuk scan issue material.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Release',
            cancelButtonText: 'Batal'
        }, function (isConfirm) {
            if (isConfirm) {
                $.post(siteurl + 'production_issue/process_release_spk/' + spk_no, function (res) {
                    if (res.success) {
                        swal('Berhasil!', 'SPK berhasil di-release.', 'success');
                        $('#tbl-spk').DataTable().ajax.reload();
                    } else {
                        swal('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                    }
                }, 'json');
            }
        });
    });
});
</script>
