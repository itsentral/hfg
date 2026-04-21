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
<?php if ($this->session->flashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <?= $this->session->flashdata('warning') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Delivery Order</h5>
        <a href="<?= base_url('delivery_fg/add') ?>" class="btn btn-success btn-sm">
            <i class="fa fa-plus"></i> Buat DO Baru
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbl-do" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th class="text-center">No DO</th>
                        <th class="text-center">Customer</th>
                        <th class="text-center">Tgl Delivery</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Tgl Buat</th>
                        <th class="text-center no-sort" width="100">Aksi</th>
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
    $('#tbl-do').DataTable({
        processing: true,
        serverSide: true,
        stateSave: true,
        autoWidth: false,
        destroy: true,
        responsive: true,
        aaSorting: [[5, 'desc']],
        columnDefs: [{ targets: 'no-sort', orderable: false }],
        sPaginationType: 'simple_numbers',
        iDisplayLength: 10,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: siteurl + 'delivery_fg/data_side_do',
            type: 'POST',
            cache: false
        }
    });
});
</script>
