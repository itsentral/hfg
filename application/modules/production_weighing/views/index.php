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
        <h5 class="mb-0">Daftar Timbang Awal Coil</h5>
        <a href="<?= base_url('production_weighing/add') ?>" class="btn btn-success btn-sm">
            <i class="fa fa-plus"></i> Timbang Awal Baru
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbl-preweigh" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th class="text-center">No Timbang</th>
                        <th class="text-center">Tgl Timbang</th>
                        <th class="text-center">No SPK</th>
                        <th class="text-center">No Coil</th>
                        <th class="text-center">Net PL (kg)</th>
                        <th class="text-center">Selisih %</th>
                        <th class="text-center">Status</th>
                        <th class="text-center no-sort" width="120">Aksi</th>
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
    $('#tbl-preweigh').DataTable({
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
            url: siteurl + 'production_weighing/data_side_preweigh',
            type: 'POST',
            cache: false
        }
    });
});
</script>
