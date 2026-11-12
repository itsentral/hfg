<?php
    $ENABLE_ADD     = has_permission('Master_Unit.Add');
    $ENABLE_MANAGE  = has_permission('Master_Unit.Manage');
    $ENABLE_VIEW    = has_permission('Master_Unit.View');
    $ENABLE_DELETE  = has_permission('Master_Unit.Delete');
?>

<div id='alert_edit' class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <span class="alert-content"></span>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fa fa-balance-scale me-2"></i>Master Unit
        </h5>
        <div>
            <?php if($ENABLE_ADD) : ?>
                <button type='button' class="btn btn-success btn-sm" id="add" title="Add">
                    <i class="fa fa-plus me-1"></i> Add Unit
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-primary text-center">
                    <tr>
                        <th width="5%">#</th>
                        <th>Unit</th>
                        <th>Last By</th>
                        <th>Last Date</th>
                        <th width="15%" class="no-sort text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="dialog-popup-label" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="dialog-popup-label">
                    <i class="fa fa-list-alt me-2"></i><span id="head_title">Detail Data</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
        </div>
    </div>
</div>

<!-- page script -->
<script type="text/javascript">
    $(document).on('click', '#add', function(){
        $("#head_title").html("Tambah Unit");
        $.ajax({
            type:'POST',
            url: siteurl + 'material/add_unit',
            success:function(data){
                $("#ModalView").html(data);
                $("#dialog-popup").modal('show');
            }
        });
    });

    $(document).on('click', '.edit', function(){
        $("#head_title").html("Edit Unit");
        var id = $(this).data('id');
        $.ajax({
            type:'POST',
            url: siteurl + 'material/add_unit/' + id,
            success:function(data){
                $("#ModalView").html(data);
                $("#dialog-popup").modal('show');
            }
        });
    });

    // DELETE DATA
    $(document).on('click', '.delete', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        swal({
            title: "Anda Yakin?",
            text: "Data Inventory akan di hapus.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
            closeOnConfirm: false
        },
        function(){
            $.ajax({
                type:'POST',
                url: siteurl + 'material/hapus_unit',
                dataType : "json",
                data:{'id':id},
                success:function(result){
                    if(result.status == '1'){
                        swal({
                            title: "Sukses",
                            text : "Data berhasil dihapus.",
                            type : "success"
                        },
                        function (){
                            window.location.reload(true);
                        });
                    } else {
                        swal({
                            title : "Error",
                            text  : "Data error. Gagal hapus data",
                            type  : "error"
                        });
                    }
                },
                error : function(){
                    swal({
                        title : "Error",
                        text  : "Data error. Gagal request Ajax",
                        type  : "error"
                    });
                }
            });
        });
    });

    $(document).on('click', '#save', function(e){
        e.preventDefault();
        var data = $('#data_form').serialize();

        swal({
            title: "Anda Yakin?",
            text: "Data akan di simpan.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya, Simpan!",
            cancelButtonText: "Batal",
            closeOnConfirm: false
        },
        function(){
            $.ajax({
                type:'POST',
                url: siteurl + 'material/add_unit',
                dataType : "json",
                data:data,
                success:function(result){
                    if(result.status == '1'){
                        swal({
                            title: "Sukses",
                            text : "Data Inventory berhasil disimpan.",
                            type : "success"
                        },
                        function (){
                            window.location.reload(true);
                        });
                    } else {
                        swal({
                            title : "Error",
                            text  : "Data error. Gagal insert data",
                            type  : "error"
                        });
                    }
                },
                error : function(){
                    swal({
                        title : "Error",
                        text  : "Data error. Gagal request Ajax",
                        type  : "error"
                    });
                }
            });
        });
    });

    $(function() {
        DataTables();
    });

    function DataTables(){
        var dataTable = $('#example1').DataTable({
            "scrollY": "500px",
            "scrollCollapse" : true,
            "processing" : true,
            "serverSide": true,
            "stateSave" : true,
            "bAutoWidth": true,
            "destroy": true,
            "responsive": true,
            "oLanguage": {
                "sSearch": "<b>Search : </b>",
                "sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
                "sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
                "sInfoFiltered": "(filtered from _MAX_ total entries)",
                "sZeroRecords": "No matching records found",
                "sEmptyTable": "No data available in table",
                "sLoadingRecords": "Please wait - loading...",
                "oPaginate": {
                    "sPrevious": "Prev",
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
                url : siteurl + 'material/data_side_unit',
                type: "post",
                cache: false,
                error: function(){
                    $(".my-grid-error").html("");
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#my-grid_processing").css("display","none");
                }
            }
        });
    }
</script>