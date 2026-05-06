<?php
$ENABLE_ADD    = has_permission('Incoming.Add');
$ENABLE_MANAGE = has_permission('Incoming.Manage');
$ENABLE_VIEW   = has_permission('Incoming.View');
$ENABLE_DELETE = has_permission('Incoming.Delete');
?>

<div class="card">
    <div class="card-body">
        <table id="tblRosIncoming" class="table table-bordered table-striped" width="100%">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Nomor ROS</th>
                    <th class="text-center">Nomor PO</th>
                    <th class="text-center">Supplier</th>
                    <th class="text-center">Kurs PIB</th>
                    <th class="text-center">ETA Warehouse</th>
                    <th class="text-center">Status</th>
                    <th class="text-center no-sort">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
$(document).ready(function() {
    $('#tblRosIncoming').DataTable({
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
        aLengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
        ajax: {
            url: siteurl + active_controller + 'data_side_incoming',
            type: 'post',
            cache: false
        }
    });
});
</script>
