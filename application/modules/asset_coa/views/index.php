<?php
DEFINED('BASEPATH') OR exit('No direct script access allowed');
$ENABLE_ADD     = has_permission('Asset_coa.Add');
$ENABLE_MANAGE  = has_permission('Asset_coa.Manage');
$ENABLE_VIEW    = has_permission('Asset_coa.View');
$ENABLE_DELETE  = has_permission('Asset_coa.Delete');
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0 fw-bold"><?php echo $title; ?></h5>
        <div>
<?php if ($ENABLE_ADD) : ?>
            <button type="button" class="btn btn-primary btn-sm" id="add">
                <i class="fa fa-plus me-1"></i> Add CATEGORY ASSET
            </button>
<?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle" id="my-grid" width="100%">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width: 5ppx;">#</th>
                        <th class="text-center">Keterangan</th>
                        <th class="text-center">COA Debet</th>
                        <th class="text-center">COA Kredit</th>
                        <th class="text-center" style="width: 120px;">Option</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Bootstrap 5 -->
<div class="modal fade" id="ModalView" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="head_title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="view">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        DataTables();
    });

    $(document).on('click', '#add', function(e){
        e.preventDefault();
        if (typeof loading_spinner === 'function') loading_spinner();
        $("#head_title").html("<b>ADD CATEGORY ASSET</b>");
        $("#view").load(base_url + active_controller + '/add_asset_coa', function(){
            var myModal = new bootstrap.Modal(document.getElementById('ModalView'));
            myModal.show();
        });
    });

    $(document).on('click', '.edit', function(e){
        e.preventDefault();
        if (typeof loading_spinner === 'function') loading_spinner();
        var id = $(this).data('code');
        $("#head_title").html("<b>EDIT CATEGORY ASSET</b>");
        $("#view").load(base_url + active_controller + '/add_asset_coa/' + id, function(){
            var myModal = new bootstrap.Modal(document.getElementById('ModalView'));
            myModal.show();
        });
    });

    $(document).on('click', '#save', function(){
        var keterangan = $("#keterangan").val();
        if (keterangan === '') {
            swal({title: "Error Message!", text: 'Keterangan Kosong ...', type: "warning"});
            $('#save').prop('disabled', false);
            return false;
        }

        swal({
            title: "Are you sure?",
            text: "Save this data ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, Process it!",
            cancelButtonText: "No, cancel process!",
            closeOnConfirm: true,
            closeOnCancel: false
        }, function(isConfirm) {
            if (isConfirm) {
                if (typeof loading_spinner === 'function') loading_spinner();
                var formData = new FormData($('#form_ct')[0]);
                var baseurl = base_url + active_controller + '/add_asset_coa';
                $.ajax({
                    url: baseurl,
                    type: "POST",
                    data: formData,
                    cache: false,
                   dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(data){
                        if(data.status == 1){
                            swal({
                                 title: "Save Success!",
                                 text: data.pesan,
                                 type: "success",
                                 timer: 3000
                            });
                            window.location.href = base_url + active_controller;
                        } else {
                            swal({
                                 title: "Save Failed!",
                                 text: data.pesan,
                                 type: "warning",
                                 timer: 3000
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error Message !",
                            text: 'An Error Occured During Process. Please try again..',
                            type: "warning"
                        });
                    }
                });
            } else {
                swal("Cancelled", "Data can be process again", "error");
                return false;
            }
        });
    });
    
    $(document).on('click', '.delete', function(){
        var code = $(this).data('code');
        swal({
            title: "Are you sure?",
            text: "Delete this data ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, Process it!",
            cancelButtonText: "No, cancel process!",
            closeOnConfirm: true,
            closeOnCancel: false
        }, function(isConfirm) {
            if (isConfirm) {
                if (typeof loading_spinner === 'function') loading_spinner();
                $.ajax({
                    url: base_url + active_controller + '/hapus_asset_coa/' + code,
                    type: "POST",
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(data){
                        if(data.status == 1){
                            swal({
                                 title: "Save Success!",
                                 text: data.pesan,
                                 type: "success",
                                 timer: 3000
                            });
                            window.location.href = base_url + active_controller;
                        } else {
                            swal({
                                 title: "Save Failed!",
                                 text: data.pesan,
                                 type: "warning",
                                 timer: 3000
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error Message !",
                            text: 'An Error Occured During Process. Please try again..',
                            type: "warning"
                        });
                    }
                });
            } else {
                swal("Cancelled", "Data can be process again", "error");
                return false;
            }
        });
    });

    function DataTables(){
        var dataTable = $('#my-grid').DataTable ({
            "processing" : true,
            "serverSide": true,
            "stateSave" : true,
            "bAutoWidth": true,
            "destroy": true,
            "responsive": true,
            "oLanguage": {
                "sSearch": "<b>Live Search : </b>",
                "sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
                "sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
                "sInfoFiltered": "(filtered from _MAX_ total entries)",
                "sZeroRecords": "No matching records found",
                "sEmptyTable": "No data available in table",
                "sLoadingRecords": "Please wait - loading...",
                "oPaginate": {
                    "sPrevious": "Preview",
                    "sNext": "Next"
                }
            },
            "aaSorting": [[ 1, "asc" ]],
            "columnDefs": [ {
                "targets": 'no-sort',
                "orderable": false,
            }],
            "sPaginationType": "simple_numbers",
            "iDisplayLength": 10,
            "aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
            "ajax":{
                url : base_url + active_controller + '/data_side_asset_coa',
                type: "post",
                cache: false,
                error: function(){
                    $(".my-grid-error").html("");
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="5">No data found in the server</th></tr></tbody>');
                    $("#my-grid_processing").css("display","none");
                }
            }
        });
    }
</script>
