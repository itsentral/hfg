<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title fw-bold mb-0 text-primary">
                <i class="fa fa-list-alt me-2"></i><?= $title; ?>
            </h5>
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
                <table class="table table-hover table-striped border align-middle w-100" id="my-grid">
                    <thead class="table-primary text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">No PR</th>
                            <th width="15%">Tanggal PR</th>
                            <th>Nama Barang</th>
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

    $(document).on('click', '.look_hide', function(e) {
        e.preventDefault();
        var idOfParent = $(this).data('id');
        $('.child-' + idOfParent).toggle('slow');
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
            "aaSorting": [[1, "desc"]],
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
</script>
