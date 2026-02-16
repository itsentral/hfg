<?php
$ENABLE_ADD     = has_permission('ROS.Add');
$ENABLE_MANAGE  = has_permission('ROS.Manage');
$ENABLE_VIEW    = has_permission('ROS.View');
$ENABLE_DELETE  = has_permission('ROS.Delete');
?>
<style type="text/css">
    thead input {
        width: 100%;
    }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<div class="card">
    <div class="card-header">
        <?php if ($ENABLE_ADD) : ?>
            <a class="btn btn-success btn-md" href="<?= base_url('ros/add') ?>" title="Add"><i class="fa fa-plus"></i> Add</a>
        <?php endif; ?>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped" width='100%'>
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Nomor ROS</th>
                    <th class="text-center">Nomor PO</th>
                    <th class="text-center">Supplier</th>
                    <th class="text-center">Nomor Pengajuan PIB</th>
                    <th class="text-center">Nilai PIB</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <!-- /.card-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style='width:90%; '>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Default</h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPrintQR" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">List Packing List / Coil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_body_print">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-md" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success btn-md" id="btn-print-action"><i class="fas fa-print"></i> Print Selected</button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        DataTables();
    });

    $(document).on('click', '.view_coil', function() {
        var id_ros = $(this).data('id');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + '/get_coil_list',
            data: {
                id_ros: id_ros
            },
            success: function(html) {
                $('#modal_body_print').html(html);
                $('#modalPrintQR').modal('show');
            }
        });
    });

    $(document).on('click', '#check_all_modal', function() {
        $('.check_item_modal').prop('checked', this.checked);
    });

    // Pastikan jika satu item di-uncheck, header check_all juga uncheck
    $(document).on('click', '.check_item_modal', function() {
        if ($('.check_item_modal:checked').length == $('.check_item_modal').length) {
            $('#check_all_modal').prop('checked', true);
        } else {
            $('#check_all_modal').prop('checked', false);
        }
    });

    $(document).on('click', '#btn-print-action', function() {
        var selected = [];
        $('.check_item_modal:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length > 0) {
            var ids = selected.join('-');
            window.open(siteurl + active_controller + '/print_qr_multi/' + ids, '_blank');
        } else {
            swal('Warning', 'Pilih minimal satu coil untuk dicetak!', 'warning');
        }
    });

    $(document).on('click', '.del_ros', function() {
        var no_ros = $(this).data('no_ros');

        swal({
                title: "Warning !",
                text: "Data will be saved !",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + '/del_ros',
                        data: {
                            'no_ros': no_ros
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(result) {
                            if (result.status == '1') {
                                swal({
                                    title: 'Success !',
                                    text: 'Data was successfully deleted !',
                                    type: 'success',
                                });

                                location.reload();
                            } else {
                                swal({
                                    title: 'Failed !',
                                    text: 'Data was not deleted !',
                                    type: 'error'
                                });
                            }
                        },
                        error: function(result) {
                            swal({
                                title: 'Error !',
                                text: 'Please try again later !',
                                type: 'error'
                            });
                        }
                    });
                }
            });
    });

    $(document).on('click', '.req_payment', function() {
        var no_ros = $(this).data('no_ros');

        swal({
                title: "Warning !",
                text: "Data will be moved to Request Payment !",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Request Payment",
                cancelButtonText: "Cancel",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + '/req_payment_ros',
                        data: {
                            'no_ros': no_ros
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(result) {
                            if (result.status == '1') {
                                swal({
                                    title: 'Success !',
                                    text: 'Data was successfully moved to request payment !',
                                    type: 'success',
                                });

                                location.reload();
                            } else {
                                swal({
                                    title: 'Failed !',
                                    text: 'Data was not moved to request payment !',
                                    type: 'error'
                                });
                            }
                        },
                        error: function(result) {
                            swal({
                                title: 'Error !',
                                text: 'Please try again later !',
                                type: 'error'
                            });
                        }
                    });
                }
            });
    });

    function DataTables(costcenter = null, product = null) {
        var dataTable = $('#example1').DataTable({
            // "scrollX": true,
            // "scrollCollapse" : true,
            // "scrollY": 500,
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "fixedHeader": true,
            "autoWidth": false,
            "destroy": true,
            "searching": true,
            "responsive": true,
            "aaSorting": [
                [1, "desc"]
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
                url: siteurl + active_controller + 'data_side_ros',
                type: "post",
                data: function(d) {
                    d.costcenter = costcenter,
                        d.product = product
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