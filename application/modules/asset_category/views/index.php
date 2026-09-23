<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fa fa-tags me-2"></i><?= $title ?>
        </h5>
        <?php if ($this->auth->has_permission('Category.Add') || $this->auth->has_permission('Asset_category.Add')): ?>
            <button type="button" class="btn btn-sm btn-primary" id="add">
                <i class="fa fa-plus me-1"></i> Add Category
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle w-100" id="my-grid">
                <thead class="table-primary text-center">
                    <tr>
                        <th width="5%">#</th>
                        <th>Category Name</th>
                        <th width="15%">Status</th>
                        <th width="15%" class="no-sort">Option</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Bootstrap 5 -->
<div class="modal fade" id="ModalView" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="head_title">Category</h5>
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
    $(document).ready(function(){
        DataTables();
    });

    $(document).on('click', '#add', function(e){
        e.preventDefault();
        if (typeof loading_spinner === 'function') loading_spinner();
        $("#head_title").html("<b>ADD CATEGORY ASSET</b>");
        $("#view").load(base_url + active_controller + '/add_category', function(){
            var myModal = new bootstrap.Modal(document.getElementById('ModalView'));
            myModal.show();
        });
    });

    $(document).on('click', '.edit', function(e){
        e.preventDefault();
        if (typeof loading_spinner === 'function') loading_spinner();
        var id = $(this).data('code');
        $("#head_title").html("<b>EDIT CATEGORY ASSET</b>");
        $("#view").load(base_url + active_controller + '/add_category/' + id, function(){
            var myModal = new bootstrap.Modal(document.getElementById('ModalView'));
            myModal.show();
        });
    });

    $(document).on('click', '#save', function(){
        var nm_category = $("#nm_category").val();
        if(nm_category === ''){
            swal({title:"Warning!", text:'Category Name cannot be empty', type:"warning"});
            return false;
        }

        swal({
            title: "Are you sure?",
            text: "Save this category?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-primary",
            confirmButtonText: "Yes, Save!",
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                if (typeof loading_spinner === 'function') loading_spinner();
                var formData = new FormData($('#form_ct')[0]);
                $.ajax({
                    url: base_url + active_controller + '/add_category',
                    type: "POST",
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(data){
                        if(data.status == 1){
                            swal({
                                title: "Save Success!",
                                text: data.pesan,
                                type: "success",
                                timer: 1500,
                                showConfirmButton: false
                            }, function(){
                                window.location.href = base_url + active_controller;
                            });
                            // Fallback if sweetalert timer finishes
                            setTimeout(function(){
                                window.location.href = base_url + active_controller;
                            }, 1500);
                        } else {
                            swal("Failed!", data.pesan, "warning");
                        }
                    },
                    error: function() {
                        swal("Error!", "An error occurred during save", "error");
                    }
                });
            }
        });
    });

    $(document).on('click', '.delete', function(){
        var code = $(this).data('code');
        swal({
            title: "Are you sure?",
            text: "Delete this category?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, Delete!",
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                if (typeof loading_spinner === 'function') loading_spinner();
                $.ajax({
                    url: base_url + active_controller + '/hapus_category/' + code,
                    type: "POST",
                    dataType: 'json',
                    success: function(data){
                        if(data.status == 1){
                            swal({
                                title: "Delete Success!",
                                text: data.pesan,
                                type: "success",
                                timer: 1500,
                                showConfirmButton: false
                            }, function(){
                                window.location.href = base_url + active_controller;
                            });
                            setTimeout(function(){
                                window.location.href = base_url + active_controller;
                            }, 1500);
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

    function DataTables(){
        $('#my-grid').DataTable({
            "processing" : true,
            "serverSide": true,
            "stateSave" : true,
            "autoWidth": false,
            "destroy": true,
            "responsive": true,
            "aaSorting": [[ 1, "asc" ]],
            "columnDefs": [ {
                "targets": 'no-sort',
                "orderable": false,
            }],
            "iDisplayLength": 10,
            "aLengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
            "ajax":{
                url: base_url + active_controller + '/data_side_category',
                type: "post"
            }
        });
    }
</script>
