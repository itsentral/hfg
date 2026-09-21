<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title fw-bold mb-0 text-primary">
                <i class="fa fa-check-square-o me-2"></i><?= $title; ?>
            </h5>
        </div>
        <div class="card-body">
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
                            <th width="10%">Action</th>
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

        $(document).on('click', '.approve', function() {
            var nomor = $(this).data('id');
            var no_pr = $('#no_pr_' + nomor).val();
            var action = $('#action_' + nomor).val();
            var reason = $('#reason_' + nomor).val();

            if (action == 'D' && (reason == '' || reason == null)) {
                swal({
                    title: "Warning!",
                    text: 'Reason is required when rejecting PR.',
                    type: "warning"
                });
                return false;
            }

            var act_text = (action == 'Y') ? 'Approve' : 'Reject';

            swal({
                title: "Are you sure?",
                text: "Process " + act_text + " for PR " + no_pr + "?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: (action == 'Y') ? "btn-success" : "btn-danger",
                confirmButtonText: "Yes, " + act_text + "!",
                cancelButtonText: "Cancel",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: base_url + active_controller + '/approve_pr',
                        type: "POST",
                        data: {
                            "no_pr": no_pr,
                            "action": action,
                            "reason": reason
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
                                DataTables();
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
                                text: 'An error occurred during approval process.',
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
                url: base_url + active_controller + '/server_side_approval',
                type: "post",
                cache: false,
                error: function() {
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="8" class="text-center text-danger">No data found in the server</th></tr></tbody>');
                }
            }
        });
    }
</script>
