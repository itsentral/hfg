<?php
$ENABLE_ADD     = has_permission('Product_Master.Add');
$ENABLE_MANAGE  = has_permission('Product_Master.Manage');
$ENABLE_VIEW    = has_permission('Product_Master.View');
$ENABLE_DELETE  = has_permission('Product_Master.Delete');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex gap-2">
                <?php if ($ENABLE_ADD) : ?>
                    <a class="btn btn-success add" href="javascript:void(0)" title="Add">
                        <i class="fa fa-plus me-1"></i>Add
                    </a>
                    <a class="btn btn-info" href="<?= base_url('product_master/download_excel'); ?>" target="_blank" title="Download Excel">
                        <i class="fa fa-file-excel-o me-1"></i>Excel
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter Row -->
        <div class="row g-2">
            <div class="col-md-2">
                <select name="level1" id="level1" class="form-select select2">
                    <option value="0">ALL PRODUCT TYPE</option>
                    <?php foreach ($get_level_1 as $value) : ?>
                        <option value="<?= $value['code_lv1']; ?>"><?= strtoupper($value['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="level2" id="level2" class="form-select select2">
                    <option value="0">ALL PRODUCT CATEGORY</option>
                    <?php foreach ($get_level_2 as $value) : ?>
                        <option value="<?= $value['code_lv2']; ?>"><?= strtoupper($value['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select name="level3" id="level3" class="form-select select2">
                    <option value="0">ALL PRODUCT JENIS</option>
                    <?php foreach ($get_level_3 as $value) : ?>
                        <option value="<?= $value['code_lv3']; ?>"><?= strtoupper($value['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example1" class="table table-striped table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Product Type</th>
                        <th>Product Category</th>
                        <th>Product Jenis</th>
                        <th>Product Master</th>
                        <th>Code Master</th>
                        <th>Status</th>
                        <th width="7%">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="data_form_master_product" autocomplete="off">
                <div class="modal-header">
                    <h4 class="modal-title" id="head_title">Product Master</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="ModalView">
                    <!-- ajax content -->
                </div>
                <div class="modal-footer">
                    <button type="button" id="save" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Save
                    </button>
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js'); ?>"></script>

<script type="text/javascript">
    $(document).ready(function() {

        $('.select2').select2({
            width: '100%'
        });

        // Init DataTable
        var level1 = $('#level1').val();
        var level2 = $('#level2').val();
        var level3 = $('#level3').val();
        loadDataTable(level1, level2, level3);

        // Filter change
        $(document).on('change', '#level1, #level2, #level3', function() {
            loadDataTable($('#level1').val(), $('#level2').val(), $('#level3').val());
        });

        // ADD / EDIT
        $(document).on('click', '.add, .edit', function() {
            var id = $(this).data('id') || '';
            let title = (id === '') ? 'Add' : 'Edit';
            $("#head_title").html(`${title} Product Master`);

            $.ajax({
                type: 'POST',
                url: base_url + active_controller + 'add/' + id,
                success: function(data) {
                    $("#dialog-popup").modal('show');
                    $("#ModalView").html(data);
                }
            });
        });

        // Cascade: level1 → level2
        $(document).on('change', '#code_lv1', function() {
            var code_lv1 = $("#code_lv1").val();
            $.ajax({
                url: base_url + active_controller + 'get_list_level1',
                method: "POST",
                data: {
                    code_lv1: code_lv1
                },
                dataType: 'json',
                success: function(data) {
                    $('#code_lv2').html(data.option);
                    $('#code_lv3').html("<option value='0'>List Empty</option>");
                }
            });
        });

        // Cascade: level2 → level3
        $(document).on('change', '#code_lv2', function() {
            $.ajax({
                url: base_url + active_controller + 'get_list_level3',
                method: "POST",
                data: {
                    code_lv1: $("#code_lv1").val(),
                    code_lv2: $("#code_lv2").val()
                },
                dataType: 'json',
                success: function(data) {
                    $('#code_lv3').html(data.option);
                }
            });
        });

        // Cascade: level3 → auto-fill nama & code
        $(document).on('change', '#code_lv3', function() {
            $.ajax({
                url: base_url + active_controller + 'get_list_level4_name',
                method: "POST",
                data: {
                    code_lv1: $("#code_lv1").val(),
                    code_lv2: $("#code_lv2").val(),
                    code_lv3: $("#code_lv3").val()
                },
                dataType: 'json',
                success: function(data) {
                    $('#nama').val(data.nama);
                    $('#code').val(data.code);
                }
            });
        });

        // Update manual code
        $(document).on('click', '#updateManualCode', function() {
            $.ajax({
                url: base_url + active_controller + 'get_list_level4_name',
                method: "POST",
                data: {
                    code_lv1: $("#code_lv1").val(),
                    code_lv2: $("#code_lv2").val(),
                    code_lv3: $("#code_lv3").val()
                },
                dataType: 'json',
                success: function(data) {
                    $('#code').val(data.code);
                }
            });
        });

        // Cubic calc
        $(document).on('keyup', '.getCub', function() {
            get_cub();
        });

        // SAVE
        $(document).on('click', '#save', function(e) {
            e.preventDefault();
            var code_lv1 = $('#code_lv1').val();
            var code_lv2 = $('#code_lv2').val();
            var code_lv3 = $('#code_lv3').val();

            if (code_lv1 == '0') {
                swal({
                    title: "Error Message!",
                    text: 'Product type not selected...',
                    type: "warning"
                });
                return false;
            }
            if (code_lv2 == '0') {
                swal({
                    title: "Error Message!",
                    text: 'Product category not selected...',
                    type: "warning"
                });
                return false;
            }
            if (code_lv3 == '0') {
                swal({
                    title: "Error Message!",
                    text: 'Product jenis not selected...',
                    type: "warning"
                });
                return false;
            }

            swal({
                title: "Are you sure?",
                text: "Process this data",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-info",
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            }, function() {
                var form_data = new FormData($('#data_form_master_product')[0]);
                $.ajax({
                    type: 'POST',
                    url: base_url + active_controller + 'add',
                    dataType: "json",
                    data: form_data,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status == '1') {
                            swal({
                                    title: "Success",
                                    text: res.pesan,
                                    type: "success"
                                },
                                function() {
                                    window.location.reload(true);
                                });
                        } else {
                            swal({
                                title: "Error",
                                text: res.pesan,
                                type: "error"
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error",
                            text: "Error Process!",
                            type: "error"
                        });
                    }
                });
            });
        });

        // DELETE
        $(document).on('click', '.delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');

            swal({
                title: "Are you sure?",
                text: "Delete this data",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-info",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            }, function() {
                $.ajax({
                    type: 'POST',
                    url: base_url + active_controller + 'delete',
                    dataType: "json",
                    data: {
                        id: id
                    },
                    success: function(res) {
                        if (res.status == '1') {
                            swal({
                                    title: "Success",
                                    text: res.pesan,
                                    type: "success"
                                },
                                function() {
                                    window.location.reload(true);
                                });
                        } else {
                            swal({
                                title: "Error",
                                text: res.pesan,
                                type: "error"
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error",
                            text: "Error Process!",
                            type: "error"
                        });
                    }
                });
            });
        });

    });

    function loadDataTable(level1, level2, level3) {
        $('#example1').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            destroy: true,
            responsive: true,
            order: [
                [1, "asc"]
            ],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            pageLength: 10,
            lengthMenu: [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    previous: "Prev",
                    next: "Next"
                }
            },
            ajax: {
                url: base_url + active_controller + 'get_json_product_master',
                type: "POST",
                data: {
                    level1: level1,
                    level2: level2,
                    level3: level3
                },
                cache: false
            }
        });
    }

    function get_cub() {
        var l = getNum($('#length').val().split(",").join(""));
        var w = getNum($('#wide').val().split(",").join(""));
        var h = getNum($('#high').val().split(",").join(""));
        var cub = (l * w * h) / 1000000000;
        $('#cub').val(cub.toFixed(7));
    }
</script>