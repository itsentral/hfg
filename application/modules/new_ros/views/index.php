<?php
$ENABLE_ADD    = has_permission('New_ROS.Add');
$ENABLE_MANAGE = has_permission('New_ROS.Manage');
$ENABLE_VIEW   = has_permission('New_ROS.View');
$ENABLE_DELETE = has_permission('New_ROS.Delete');
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="card">
    <div class="card-header">
        <?php if ($ENABLE_ADD) : ?>
            <a class="btn btn-success btn-md" href="<?= base_url('new_ros/add') ?>" title="Add"><i class="fa fa-plus"></i> Add New ROS</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table id="tbl_new_ros" class="table table-bordered table-striped" width="100%">
            <thead>
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th class="text-center">Nomor ROS</th>
                    <th class="text-center">Nomor PO</th>
                    <th class="text-center">Supplier</th>
                    <th class="text-center">Nilai PIB (Rp)</th>
                    <th class="text-center" width="8%">Status</th>
                    <th class="text-center" width="15%">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    var table = $('#tbl_new_ros').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: siteurl + 'new_ros/data_side',
            type: 'POST'
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6 }
        ],
        order: [[1, 'desc']],
        pageLength: 25
    });

    // Delete
    $(document).on('click', '.del_ros', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus ROS?',
            text: 'Data ROS ' + id + ' akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(siteurl + 'new_ros/delete', { id: id }, function(res) {
                    var resp = JSON.parse(res);
                    if (resp.status == 1) {
                        Swal.fire('Terhapus!', 'Data ROS berhasil dihapus.', 'success');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Gagal!', 'Gagal menghapus data.', 'error');
                    }
                });
            }
        });
    });
});
</script>
