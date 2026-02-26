<?php
$ENABLE_ADD     = has_permission('Hscode.Add');
$ENABLE_MANAGE  = has_permission('Hscode.Manage');
$ENABLE_VIEW    = has_permission('Hscode.View');
$ENABLE_DELETE  = has_permission('Hscode.Delete');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <?php if ($ENABLE_VIEW) : ?>
            <a class="btn btn-success btn-md" href="<?= base_url('hscode/add') ?>">
                <i class="fa fa-plus me-1"></i> Add
            </a>
        <?php endif; ?>

    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="mytab" class="table table-striped table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Local Code</th>
                        <th>Description</th>
                        <th>Origin Code</th>
                        <th>Origin</th>
                        <th>Type</th>
                        <!-- <th>Status</th> -->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="data_form" autocomplete="off">
                <div class="modal-header">
                    <h4 class="modal-title">Form HS Code</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="ModalView">
                    <!-- Form content here -->
                </div>
                <div class="modal-footer">

                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        DataTables(status)

        // Delete action
        $(document).on('click', '.delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            swal({
                title: "Are you sure?",
                text: "Data will be deleted!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-info",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: false
            }, function() {
                $.ajax({
                    type: 'POST',
                    url: siteurl + active_controller + '/delete',
                    dataType: 'json',
                    data: {
                        'id': id
                    },
                    success: function(data) {
                        if (data.status == '1') {
                            swal({
                                title: "Success",
                                text: data.pesan,
                                type: "success"
                            }, function() {
                                window.location.reload(true);
                            });
                        } else {
                            swal({
                                title: "Error",
                                text: data.pesan,
                                type: "error"
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error",
                            text: "Error processing!",
                            type: "error"
                        });
                    }
                });
            });
        });

    });

    function DataTables(status = null) {
        var dataTable = $('#mytab').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "responsive": true,
            "aaSorting": [
                [1, "asc"]
            ],
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false,
            }],
            "sPaginationType": "simple_numbers",
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            "ajax": {
                url: base_url + active_controller + '/data_side_hscode',
                type: "post",
                data: function(d) {
                    d.status = 1
                },
                cache: false,
                error: function() {
                    $(".my-grid-error").html("");
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#my-grid_processing").css("display", "none");
                }
            }
        });
    }
</script>