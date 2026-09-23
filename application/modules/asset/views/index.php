<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fa fa-cubes me-2"></i><?= $title ?>
        </h5>
        <div class="d-flex align-items-center gap-2">
            <?php if ($this->auth->has_permission('Assets.Add')): ?>
                <a href="<?= site_url('asset/add') ?>" class="btn btn-sm btn-success">
                    <i class="fa fa-plus me-1"></i> Add Asset
                </a>
            <?php endif; ?>
            <a href="<?= site_url('asset/excel_asset') ?>" target="_blank" id="btn-excel" class="btn btn-sm btn-outline-success">
                <i class="fa fa-file-excel me-1"></i> Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="kategory" class="form-label fw-bold">Filter Category</label>
                <select id="kategory" name="kategory" class="form-select form-select-sm">
                    <option value="0">All Category</option>
                    <?php foreach ($kategori as $valx): ?>
                        <option value="<?= $valx['id'] ?>"><?= strtoupper($valx['nm_category']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-primary text-center">
                    <tr>
                        <th width="4%">#</th>
                        <th>Kode Asset</th>
                        <th>Asset Name</th>
                        <th width="10%">Tgl Perolehan</th>
                        <th width="12%">Category</th>
                        <th width="12%">Department</th>
                        <th width="12%" class="text-end">Acquisition</th>
                        <th width="10%">Depreciation</th>
                        <th width="12%" class="text-end">Value</th>
                        <th width="10%" class="no-sort">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Asset -->
<div class="modal fade" id="ModalView" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="head_title">Detail Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="view">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var kategori = $('#kategory').val();
        DataTables(kategori);
    });

    $(document).on('change', '#kategory', function(e) {
        e.preventDefault();
        var kategori = $('#kategory').val();
        DataTables(kategori);
        $('#btn-excel').attr('href', base_url + active_controller + '/excel_asset/' + kategori);
    });

    $(document).on('click', '.detail', function(e) {
        e.preventDefault();
        if (typeof loading_spinner === 'function') loading_spinner();
        $("#head_title").html("<b>DETAIL ASSET</b>");
        $("#view").load(base_url + active_controller + '/modal_view/' + $(this).data('id'), function(){
            var myModal = new bootstrap.Modal(document.getElementById('ModalView'));
            myModal.show();
        });
    });

    $(document).on('click', '.delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        swal({
            title: "Are you sure?",
            text: "You will delete asset data: " + id,
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, Delete it!",
            cancelButtonText: "Cancel",
            closeOnConfirm: true
        }, function(isConfirm) {
            if (isConfirm) {
                if (typeof loading_spinner === 'function') loading_spinner();
                $.ajax({
                    url: base_url + active_controller + '/delete_asset/' + id,
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        if (data.status == 1) {
                            swal("Deleted!", data.pesan, "success");
                            DataTables($('#kategory').val());
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

    function DataTables(kategori = null) {
        $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "responsive": true,
            "aaSorting": [[1, "asc"]],
            "columnDefs": [
                { "targets": 'no-sort', "orderable": false }
            ],
            "iDisplayLength": 10,
            "aLengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
            "ajax": {
                url: base_url + active_controller + '/data_side',
                type: "post",
                data: function(d) {
                    d.kategori = kategori;
                }
            }
        });
    }
</script>
