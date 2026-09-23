<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title fw-bold mb-0 text-primary">
                <i class="fa fa-plus-circle me-2"></i><?= $title; ?>
            </h5>
            <a href="<?= site_url('pr_asset'); ?>" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Back to PR List
            </a>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Select asset planning item below to request a Purchase Request (PR).</p>
            <div class="table-responsive">
                <table class="table table-hover table-striped border align-middle w-100" id="my-grid">
                    <thead class="table-primary text-center">
                        <tr>
                            <th width="5%">#</th>
                            <th>Nama Barang</th>
                            <th width="15%">Department</th>
                            <th width="15%">Costcenter</th>
                            <th width="8%">Qty</th>
                            <th width="13%">Created By</th>
                            <th width="13%">Created Date</th>
                            <th width="8%">Option</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        DataTables();

        $(document).on('click', '.look_hide', function() {
            var idOfParent = $(this).data('id');
            $('.child-' + idOfParent).toggle('slow');
        });

        $(document).on('click', '.add_pr', function() {
            var nomor = $(this).data('id');
            var qty_rev = $('#qty_rev_' + nomor).val().split(",").join("");
            var nil_pr = $('#nil_pr_' + nomor).val().split(",").join("");
            var tgl_butuh = $('#tgl_butuh_' + nomor).val();
            var code_plan = $('#code_plan_' + nomor).val();

            if (qty_rev == '' || qty_rev == '0') {
                swal({
                    title: "Error Message!",
                    text: 'Qty is empty, please input first ...',
                    type: "warning"
                });
                return false;
            }

            if (nil_pr == '' || nil_pr == '0') {
                swal({
                    title: "Error Message!",
                    text: 'Nilai PR is empty, please input first ...',
                    type: "warning"
                });
                return false;
            }

            if (tgl_butuh == '') {
                swal({
                    title: "Error Message!",
                    text: 'Date dibutuhkan is empty, please input first ...',
                    type: "warning"
                });
                return false;
            }

            swal({
                title: "Are you sure?",
                text: "Process PR request for this item?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Create PR!",
                cancelButtonText: "Cancel",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: base_url + active_controller + '/add_pr',
                        type: "POST",
                        data: {
                            "code_plan": code_plan,
                            "qty_rev": qty_rev,
                            "nil_pr": nil_pr,
                            "tgl_butuh": tgl_butuh
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Success!",
                                    text: data.pesan,
                                    type: "success",
                                    timer: 3000
                                });
                                window.location.href = base_url + 'pr_asset';
                            } else {
                                swal({
                                    title: "Failed!",
                                    text: data.pesan,
                                    type: "warning"
                                });
                            }
                        },
                        error: function() {
                            swal({
                                title: "Error!",
                                text: 'An error occurred during process.',
                                type: "error"
                            });
                        }
                    });
                }
            });
        });
    });

    function DataTables() {
        $('#my-grid').DataTable({
            "serverSide": true,
            "stateSave": true,
            "bAutoWidth": true,
            "destroy": true,
            "processing": true,
            "responsive": true,
            "aaSorting": [[1, "asc"]],
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
                url: base_url + active_controller + '/server_side_add_pr_asset',
                type: "post",
                cache: false,
                error: function() {
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="8" class="text-center text-danger">No data found in the server</th></tr></tbody>');
                }
            }
        });
    }
</script>
