<?php
    $ENABLE_ADD     = has_permission('Product_Category.Add');
    $ENABLE_MANAGE  = has_permission('Product_Category.Manage');
    $ENABLE_VIEW    = has_permission('Product_Category.View');
    $ENABLE_DELETE  = has_permission('Product_Category.Delete');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <?php if ($ENABLE_ADD) : ?>
            <a class="btn btn-success add" href="javascript:void(0)" title="Add">
                <i class="fa fa-plus me-1"></i>Add
            </a>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example1" class="table table-striped table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product Type</th>
                        <th>Product Category</th>
                        <th>Category Code</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($result)) : ?>
                        <?php $numb = 0; foreach ($result as $record) : $numb++;
                            $product_type = (!empty($get_level_1[$record->code_lv1]['nama'])) ? $get_level_1[$record->code_lv1]['nama'] : '';
                        ?>
                            <tr>
                                <td><?= $numb; ?></td>
                                <td><?= strtoupper($product_type); ?></td>
                                <td><?= strtoupper($record->nama); ?></td>
                                <td><?= strtoupper($record->code); ?></td>
                                <td>
                                    <?php if ($record->status == '1') : ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Non Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <?php if ($ENABLE_MANAGE) : ?>
                                            <a class="btn-icon btn-icon-edit add"
                                                href="javascript:void(0)"
                                                title="Edit"
                                                data-id="<?= $record->id; ?>">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($ENABLE_DELETE) : ?>
                                            <a class="btn-icon btn-icon-delete delete"
                                                href="javascript:void(0)"
                                                title="Delete"
                                                data-id="<?= $record->id; ?>">
                                                <i class="ti ti-trash"></i>
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

<!-- Modal -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="data_form" autocomplete="off">
                <div class="modal-header">
                    <h4 class="modal-title" id="head_title">Product Category</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="ModalView">
                    <!-- ajax content -->
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
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

<script type="text/javascript">
    $(document).ready(function () {

        $('#example1').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: { previous: "Prev", next: "Next" }
            }
        });

        // ADD / EDIT
        $(document).on('click', '.add', function () {
            var id = ($(this).data('id') === undefined) ? '' : $(this).data('id');
            let title = (id === '') ? 'Add' : 'Edit';
            $("#head_title").html(`${title} Product Category`);

            $.ajax({
                type: 'POST',
                url: base_url + active_controller + 'add/' + id,
                success: function (data) {
                    $("#dialog-popup").modal('show');
                    $("#ModalView").html(data);
                }
            });
        });

        // SUBMIT
        $(document).on('submit', '#data_form', function (e) {
            e.preventDefault();
            var data = $('#data_form').serialize();
            var code_lv1 = $('#code_lv1').val();

            if (code_lv1 == '0') {
                swal({ title: "Error Message!", text: 'Product type not selected...', type: "warning" });
                return false;
            }

            swal({
                title: "Are you sure?", text: "Process this data", type: "warning",
                showCancelButton: true, confirmButtonClass: "btn-info",
                confirmButtonText: "Ya, Simpan!", cancelButtonText: "Batal", closeOnConfirm: false
            }, function () {
                $.ajax({
                    type: 'POST', url: base_url + active_controller + 'add', dataType: "json", data: data,
                    success: function (res) {
                        if (res.status == '1') {
                            swal({ title: "Success", text: res.pesan, type: "success" }, function () { window.location.reload(true); });
                        } else {
                            swal({ title: "Error", text: res.pesan, type: "error" });
                        }
                    },
                    error: function () { swal({ title: "Error", text: "Error Process!", type: "error" }); }
                });
            });
        });

        // DELETE
        $(document).on('click', '.delete', function (e) {
            e.preventDefault();
            var id = $(this).data('id');

            swal({
                title: "Are you sure?", text: "Delete this data", type: "warning",
                showCancelButton: true, confirmButtonClass: "btn-info",
                confirmButtonText: "Ya, Hapus!", cancelButtonText: "Batal", closeOnConfirm: false
            }, function () {
                $.ajax({
                    type: 'POST', url: base_url + active_controller + 'delete', dataType: "json", data: { id: id },
                    success: function (res) {
                        if (res.status == '1') {
                            swal({ title: "Success", text: res.pesan, type: "success" }, function () { window.location.reload(true); });
                        } else {
                            swal({ title: "Error", text: res.pesan, type: "error" });
                        }
                    },
                    error: function () { swal({ title: "Error", text: "Error Process!", type: "error" }); }
                });
            });
        });

    });
</script>