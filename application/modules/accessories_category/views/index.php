<?php
$ENABLE_ADD     = has_permission('Category_Stok.Add');
$ENABLE_MANAGE  = has_permission('Category_Stok.Manage');
$ENABLE_VIEW    = has_permission('Category_Stok.View');
$ENABLE_DELETE  = has_permission('Category_Stok.Delete');
?>

<div id='alert_edit' class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <span class="alert-content"></span>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-end gap-2">
        <?php if ($ENABLE_ADD) : ?>
            <a class="btn btn-success btn-sm add" href="javascript:void(0)" title="Add Category">
                <i class="fa fa-plus me-1"></i> Add Category
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-primary text-center">
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">ID</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th width="12%" class="no-sort text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($result)): ?>
                        <?php $numb = 0;
                        foreach ($result as $record): $numb++; ?>
                            <tr>
                                <td class="text-center"><?= $numb; ?></td>
                                <td><?= htmlspecialchars(strtoupper($record->id)) ?></td>
                                <td><?= htmlspecialchars(strtoupper($record->nm_category)) ?></td>
                                <td><?= htmlspecialchars(strtoupper($record->description)) ?></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <?php if ($ENABLE_MANAGE) : ?>
                                            <a class="btn btn-primary btn-sm edit" href="javascript:void(0)" title="Edit" data-id="<?= $record->id ?>">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($ENABLE_DELETE) : ?>
                                            <a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id="<?= $record->id ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Dialog -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="dialog-popup-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="dialog-popup-label">
                    <i class="fa fa-tags me-2"></i><span id="head_title">Category Stok</span>
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
    $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $("#head_title").html("Edit Category Stok");
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + '/add/' + id,
            success: function(data) {
                $("#ModalView").html(data);
                $("#dialog-popup").modal('show');
            }
        });
    });

    $(document).on('click', '.add', function(e) {
        e.preventDefault();
        $("#head_title").html("Tambah Category Stok");
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + '/add',
            success: function(data) {
                $("#ModalView").html(data);
                $("#dialog-popup").modal('show');
            }
        });
    });

    $(document).on('submit', '#data_form', function(e) {
        e.preventDefault();
        var data = $('#data_form').serialize();

        swal({
                title: "Anda Yakin?",
                text: "Data akan diproses!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-info",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: false
            },
            function() {
                $.ajax({
                    type: 'POST',
                    url: siteurl + active_controller + '/add',
                    dataType: "json",
                    data: data,
                    success: function(data) {
                        if (data.status == '1') {
                            swal({
                                    title: "Sukses",
                                    text: data.pesan,
                                    type: "success"
                                },
                                function() {
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
                            text: "Error process!",
                            type: "error"
                        });
                    }
                });
            });
    });

    // DELETE DATA
    $(document).on('click', '.delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        swal({
                title: "Anda Yakin?",
                text: "Data akan di hapus!",
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
                    url: siteurl + active_controller + '/delete',
                    dataType: "json",
                    data: {
                        'id': id
                    },
                    success: function(data) {
                        if (data.status == '1') {
                            swal({
                                    title: "Sukses",
                                    text: data.pesan,
                                    type: "success"
                                },
                                function() {
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
                            text: "Error process!",
                            type: "error"
                        });
                    }
                });
            });
    });

    $(function() {
        $('#example1').DataTable({
            responsive: true,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }]
        });
    });
</script>