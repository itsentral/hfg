<?php
$ENABLE_ADD     = has_permission('Master_Indirect.Add');
$ENABLE_MANAGE  = has_permission('Master_Indirect.Manage');
$ENABLE_VIEW    = has_permission('Master_Indirect.View');
$ENABLE_DELETE  = has_permission('Master_Indirect.Delete');
?>

<div id='alert_edit' class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <span class="alert-content"></span>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-end gap-2">
        <div class="d-flex align-items-center gap-2">
            <?php if ($ENABLE_ADD) : ?>
                <a class="btn btn-success btn-sm" href="<?= base_url('accessories/add') ?>" title="Add">
                    <i class="fa fa-plus me-1"></i> Add
                </a>
            <?php endif; ?>
            <a class="btn btn-outline-success btn-sm" href="<?= base_url('accessories/download_excel'); ?>" target='_blank' title="Download Excel">
                <i class="fa fa-file-excel me-1"></i> Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3 g-3">
            <div class="col-md-3">
                <label for="id_category" class="form-label fw-bold">Filter Category</label>
                <select name="id_category" id="id_category" class="form-select select2">
                    <option value="0">ALL CATEGORY</option>
                    <?php
                    foreach ($category as $key => $value) {
                        echo "<option value='" . $value['id'] . "'>" . strtoupper($value['nm_category']) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-primary text-center">
                    <tr>
                        <th width="4%" class="no-sort text-center">#</th>
                        <th>Item Code</th>
                        <th>Stok Name</th>
                        <th>Stok Category</th>
                        <th>Trade Name</th>
                        <th>Brand</th>
                        <th>Spec</th>
                        <th width="8%">Status</th>
                        <th>Last By</th>
                        <th>Last Date</th>
                        <th width="10%" class="no-sort text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="dialog-popup-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
    $(document).on('click', '.detail', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $("#head_title").html("Detail Accessories");
        $.ajax({
            type: 'POST',
            url: "<?= site_url('accessories/detail'); ?>/" + id,
            data: {
                'id': id
            },
            success: function(data) {
                $("#ModalView").html(data);
                $("#dialog-popup").modal('show');
            }
        });
    });

    // DELETE DATA
    $(document).on('click', '.delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        swal({
                title: "Anda Yakin?",
                text: "Data akan di hapus.",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            },
            function() {
                $.ajax({
                    type: 'POST',
                    url: "<?= site_url('accessories/hapus'); ?>",
                    dataType: "json",
                    data: {
                        'id': id
                    },
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                    title: "Sukses",
                                    text: result.pesan,
                                    type: "success"
                                },
                                function() {
                                    window.location.reload(true);
                                });
                        } else {
                            swal({
                                title: "Error",
                                text: result.pesan,
                                type: "error"
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error",
                            text: "Error Process !",
                            type: "error"
                        });
                    }
                });
            });
    });

    $(function() {
        var id_category = $('#id_category').val();
        DataTables(id_category);

        $('.select2').select2({
            width: '100%'
        });
    });

    $(document).on('change', '#id_category', function() {
        var id_category = $('#id_category').val();
        DataTables(id_category);
    });

    function DataTables(id_category = null) {
        var dataTable = $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": false,
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
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            "ajax": {
                url: "<?= site_url('accessories/data_side_accessories'); ?>",
                type: "post",
                data: function(d) {
                    d.id_category = id_category;
                },
                error: function() {
                    $(".my-grid-error").html("");
                    $("#example1").append('<tbody class="my-grid-error"><tr><th colspan="11" class="text-center py-3 text-muted">No data found in the server</th></tr></tbody>');
                    $("#example1_processing").css("display", "none");
                }
            }
        });
    }
</script>