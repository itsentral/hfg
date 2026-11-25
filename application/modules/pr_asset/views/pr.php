<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-end">
            <div>
                <?php if ($this->auth->has_permission('PR_Asset.Add')): ?>
                    <a href="<?= site_url('pr_asset/add_pr'); ?>" class="btn btn-success btn-sm">
                        <i class="fa fa-plus me-1"></i> Add PR Asset
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <!-- Status Filter Tabs -->
            <ul class="nav nav-tabs mb-3" id="prStatusTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold status-tab" data-status="N" type="button">
                        <i class="fa fa-clock me-1 text-warning"></i> Open / Waiting Approval
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
                <table class="table table-hover table-striped border align-middle w-100" id="my-grid">
                    <thead class="table-primary text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">No PR</th>
                            <th width="15%">PR Date</th>
                            <th>Item Name</th>
                            <th width="15%">PR By</th>
                            <th width="15%">PR Date</th>
                            <th width="15%">Status</th>
                            <th width="10%">Option</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal View PR Detail Asset -->
<div class="modal fade" id="ModalView2" tabindex="-1" aria-labelledby="head_title2" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="head_title2">
                    <i class="fa fa-info-circle me-2"></i>Detail PR Asset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="view2">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Closing PR Asset -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="modalClosePrAssetLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold" id="modalClosePrAssetLabel"><i class="fa fa-times-circle me-1"></i> Closing PR Asset</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post" id="frm-close-pr">
                <div class="modal-body p-4" id="ModalView">
                    <div class="text-center text-muted py-3">Loading data...</div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-check me-1"></i> Close PR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var currentStatus = 'N';

    $(document).ready(function() {
        DataTables(currentStatus);
    });

    $(document).on('click', '.status-tab', function(e) {
        e.preventDefault();
        $('.status-tab').removeClass('active');
        $(this).addClass('active');
        currentStatus = $(this).data('status');
        DataTables(currentStatus);
    });

    // Toggle row detail di dalam tabel
    $(document).on('click', '.look_hide', function(e) {
        e.preventDefault();
        var idOfParent = $(this).data('id');
        $('.child-' + idOfParent).toggle('slow');
    });

    // Modal View PR Detail
    $(document).on('click', '.view_modal_pr', function(e) {
        e.preventDefault();
        var no_pr = $(this).data('no_pr');
        $('#head_title2').html('<i class="fa fa-file-text-o me-2"></i>PR Asset Detail [' + no_pr + ']');
        $('#view2').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        var modalEl = document.getElementById('ModalView2');
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();

        var ctrl = (typeof active_controller !== 'undefined' && active_controller) ? active_controller : 'pr_asset/';
        if (!ctrl.endsWith('/')) ctrl += '/';
        var urlAjax = base_url + ctrl + 'view_pr/' + encodeURIComponent(no_pr);

        $.ajax({
            type: 'POST',
            url: urlAjax,
            success: function(data) {
                $('#view2').html(data);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load PR Asset detail. Please try again.'
                });
            }
        });
    });

    function DataTables(status_filter) {
        if (!status_filter) status_filter = 'N';
        $('#my-grid').DataTable({
            "serverSide": true,
            "stateSave": true,
            "bAutoWidth": true,
            "destroy": true,
            "processing": true,
            "responsive": true,
            "aaSorting": [
                [1, "desc"]
            ],
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false,
            }],
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100],
                [10, 20, 50, 100]
            ],
            "ajax": {
                url: base_url + active_controller + '/server_side_pr_asset',
                type: "post",
                data: function(d) {
                    d.status_filter = status_filter;
                },
                cache: false,
                error: function() {
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="8" class="text-center text-danger">No data found in the server</th></tr></tbody>');
                }
            }
        });
    }

    $(document).on('click', '.close_pr_modal', function() {
        var no_pr = $(this).data('no_pr');
        var ctrl = (typeof active_controller !== 'undefined' && active_controller) ? active_controller : 'pr_asset/';
        if (!ctrl.endsWith('/')) ctrl += '/';

        $.ajax({
            type: 'POST',
            url: siteurl + ctrl + 'close_pr_modal',
            data: { 'no_pr': no_pr },
            cache: false,
            success: function(result) {
                $('#ModalView').html(result);
                var modalEl = document.getElementById('dialog-popup');
                var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
            },
            error: function() {
                Swal.fire('Error!', 'Failed to load Close PR modal.', 'error');
            }
        });
    });

    $(document).on('submit', '#frm-close-pr', function(e) {
        e.preventDefault();
        var ctrl = (typeof active_controller !== 'undefined' && active_controller) ? active_controller : 'pr_asset/';
        if (!ctrl.endsWith('/')) ctrl += '/';
        var data = new FormData(this);

        $.ajax({
            type: 'POST',
            url: siteurl + ctrl + 'close_pr',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status == 1) {
                    var modalEl = document.getElementById('dialog-popup');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    Swal.fire('Success!', res.msg || 'PR Asset closed.', 'success').then(function() {
                        DataTables(currentStatus);
                    });
                } else {
                    Swal.fire('Failed!', res.msg || 'Failed to close PR Asset.', 'warning');
                }
            },
            error: function() {
                Swal.fire('Error!', 'A system error occurred.', 'error');
            }
        });
    });
</script>