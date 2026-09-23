<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fa fa-check-square-o me-2"></i><?= $title ?>
        </h5>
        <a href="<?= site_url('budget') ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Budget List
        </a>
    </div>
    <div class="card-body">
        <input type="hidden" id="tanda" value="approve">
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
    $(document).ready(function() {
        DataTables('approve');
    });

    function DataTables(tanda = null) {
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
                }
            }
        });
    }
</script>
