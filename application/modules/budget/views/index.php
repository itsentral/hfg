<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fa fa-calculator me-2"></i><?= $title ?>
        </h5>
        <?php if ($this->auth->has_permission('Budget.Add')): ?>
            <a href="<?= site_url('budget/add_asset') ?>" class="btn btn-sm btn-success">
                <i class="fa fa-plus me-1"></i> Add Budget Asset
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <input type="hidden" id="tanda" value="<?= $tanda ?>">

        <!-- Status Filter Tabs -->
        <ul class="nav nav-tabs mb-3" id="budgetStatusTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold status-tab" data-status="N" type="button">
                    <i class="fa fa-clock-o me-1 text-warning"></i> Open / Waiting Approval
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold status-tab" data-status="Y" type="button">
                    <i class="fa fa-check-circle me-1 text-success"></i> Approved
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold status-tab" data-status="D" type="button">
                    <i class="fa fa-times-circle me-1 text-danger"></i> Rejected
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold status-tab" data-status="ALL" type="button">
                    <i class="fa fa-list me-1 text-primary"></i> All Status
                </button>
            </li>
        </ul>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle w-100" id="my-grid">
                <thead class="table-primary text-center">
                    <tr>
                        <th width="4%">#</th>
                        <th>Category / COA</th>
                        <th>Department</th>
                        <th>Costcenter</th>
                        <th>Nama Asset</th>
                        <th width="5%">Qty</th>
                        <th width="10%" class="text-end">Budget</th>
                        <th width="10%" class="text-end">Sisa Budget PR</th>
                        <th width="10%" class="text-end">Sisa Budget PO</th>
                        <th width="10%">Planning</th>
                        <th width="10%">Status</th>
                        <th width="10%" class="no-sort">Option</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    var currentStatus = 'N';

    $(document).ready(function() {
        DataTables($('#tanda').val(), currentStatus);
    });

    $(document).on('click', '.status-tab', function(e) {
        e.preventDefault();
        $('.status-tab').removeClass('active');
        $(this).addClass('active');
        currentStatus = $(this).data('status');
        DataTables($('#tanda').val(), currentStatus);
    });

    $(document).on('click', '.hapus', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        swal({
            title: "Are you sure?",
            text: "Delete budget planning data: " + id,
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, Delete!",
            closeOnConfirm: true
        }, function(isConfirm) {
            if (isConfirm) {
                if (typeof loading_spinner === 'function') loading_spinner();
                $.ajax({
                    url: base_url + active_controller + '/hapus_asset/' + id,
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        if (data.status == 1) {
                            swal("Deleted!", data.pesan, "success");
                            DataTables($('#tanda').val(), currentStatus);
                        } else {
                            swal("Failed!", data.pesan, "warning");
                        }
                    },
                    error: function() {
                        swal("Error!", "An error occurred during deletion", "error");
                    }
                });
            }
        });
    });

    function DataTables(tanda = null, status_filter = 'N') {
        $('#my-grid').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "responsive": true,
            "aaSorting": [[1, "desc"]],
            "columnDefs": [
                { "targets": 'no-sort', "orderable": false },
                { className: 'text-end', targets: [6, 7, 8] }
            ],
            "iDisplayLength": 10,
            "aLengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
            "ajax": {
                url: base_url + active_controller + '/server_side_asset',
                type: "post",
                data: function(d) {
                    d.tanda = tanda;
                    d.status_filter = status_filter;
                }
            }
        });
    }
</script>
