<?php
$ENABLE_ADD     = has_permission('Adjustment_Stock.Add');
$ENABLE_MANAGE  = has_permission('Adjustment_Stock.Manage');
$ENABLE_VIEW    = has_permission('Adjustment_Stock.View');
$ENABLE_DELETE  = has_permission('Adjustment_Stock.Delete');
?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row align-items-center g-3">
                    <div class="col-md-7 col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="fa fa-sliders fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-0">Warehouse Stock Adjustment</h5>
                                <small class="text-muted">Manage item stock quantity adjustments (physical additions or reductions)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-12 text-md-end">
                        <?php if ($ENABLE_ADD) : ?>
                            <a class="btn btn-primary shadow-sm" href="<?= base_url('adjustment_stock/add') ?>">
                                <i class="fa fa-plus me-1"></i> Tambah Adjustment
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filter Controls Section -->
                <div class="row g-3 align-items-end mt-2 pt-3 border-top">
                    <div class="col-md-4 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-filter text-primary me-1"></i> Adjustment Type</label>
                        <select name="type" id="type" class="form-select select2">
                            <option value="0">ALL ADJUSTMENT TYPES</option>
                            <option value="plus">PLUS (Stock Addition)</option>
                            <option value="minus">MINUS (Stock Reduction)</option>
                        </select>
                    </div>

                    <div class="col-md-8 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-boxes-stacked text-primary me-1"></i> Filter Stock Items</label>
                        <select name="code_lv4" id="code_lv4" class="form-select select2">
                            <option value="0">ALL STOCK ITEMS</option>
                            <?php foreach ($material as $key => $value) : ?>
                                <option value="<?= $value['id']; ?>">
                                    <?= strtoupper((string) $value['stock_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="example1" class="table table-hover table-striped align-middle border rounded" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="4%">#</th>
                                <th class="text-center" width="13%">Transaction No.</th>
                                <th class="text-center" width="9%">Tipe</th>
                                <th class="text-start">Item Name</th>
                                <th class="text-end" width="10%">Qty Adjust</th>
                                <th class="text-start" width="12%">PIC</th>
                                <th class="text-start" width="12%">No BA</th>
                                <th class="text-start" width="15%">Description</th>
                                <th class="text-start" width="10%">User</th>
                                <th class="text-center" width="12%">Waktu Input</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        if ($('.select2').length) {
            $('.select2').select2({ width: '100%' });
        }

        var code_lv4 = $('#code_lv4').val();
        var type = $('#type').val();
        DataTables(type, code_lv4);

        $(document).on('change', '#type, #code_lv4', function() {
            var code_lv4 = $('#code_lv4').val();
            var type = $('#type').val();
            DataTables(type, code_lv4);
        });
    });

    function DataTables(type = null, code_lv4 = null) {
        if ($.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().destroy();
        }

        $('#example1').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            autoWidth: false,
            responsive: true,
            order: [[1, "desc"]],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            ajax: {
                url: siteurl + active_controller + 'data_side_adjustment',
                type: "POST",
                data: function(d) {
                    d.type = type;
                    d.code_lv4 = code_lv4;
                },
                cache: false,
                error: function() {
                    $("#example1 tbody").html('<tr><td colspan="10" class="text-center text-muted py-4"><i class="fa fa-info-circle me-1"></i> No adjustment data found</td></tr>');
                }
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search transaction no / BA no / PIC...",
                lengthMenu: "_MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Menampilkan 0 data",
                emptyTable: "No stock adjustment history yet",
                zeroRecords: "No matching data",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading data...'
            }
        });
    }
</script>
