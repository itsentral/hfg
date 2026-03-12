<?php
$ENABLE_ADD     = has_permission('Incoming.Add');
$ENABLE_MANAGE  = has_permission('Incoming.Manage');
$ENABLE_VIEW    = has_permission('Incoming.View');
$ENABLE_DELETE  = has_permission('Incoming.Delete');
?>

<div class="card">
    <div class="card-header">
        <?php if ($ENABLE_ADD) : ?>
            <a href="<?= base_url('incoming/add') ?>" class="btn btn-md btn-success add">
                <i class="fa fa-plus me-1"></i> Incoming
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="tblIncoming">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Incoming</th>
                        <th>No PR</th>
                        <th>Supplier</th>
                        <th>Total Material</th>
                        <th>Incoming Date</th>
                        <th>Receiver</th>
                        <th>Option</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalView">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="head_title"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="view">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        DataTables();

        $(document).on('click', '.detailIncoming', function(e) {
            e.preventDefault();

            $("#head_title").html("<b>Detail Incoming</b>");
            $.ajax({
                type: 'POST',
                url: base_url + active_controller + 'view/' + $(this).data('kode_trans'),
                success: function(data) {
                    $("#ModalView").modal('show');
                    $("#view").html(data);

                },
                error: function() {
                    swal({
                        title: "Error Message !",
                        text: 'Connection Timed Out ...',
                        type: "warning",
                        timer: 5000,
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                }
            });
        });
    });

    function DataTables() {
        var dataTable = $('#tblIncoming').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "fixedHeader": true,
            "autoWidth": false,
            "destroy": true,
            "searching": true,
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
                url: siteurl + active_controller + 'data_side_incoming',
                type: "post",
                // data: function(d) {
                //     d.costcenter = costcenter,
                //         d.product = product
                // },
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