<div class="card">
    <div class="card-body">
        <table id="table-incoming-open" class="table table-bordered table-striped dt-responsive" width="100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. ROS</th>
                    <th>No. PO / Surat</th>
                    <th>Supplier</th>
                    <th>Kurs PIB</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        var tableOpen = $('#table-incoming-open').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'data_side_all',
                type: 'POST'
            },
            columns: [{
                    data: 0,
                    className: 'text-center'
                },
                {
                    data: 1
                },
                {
                    data: 2
                },
                {
                    data: 3
                },
                {
                    data: 4
                },
                {
                    data: 5,
                    className: 'text-center'
                },
                {
                    data: 6,
                    orderable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [1, 'desc']
            ],
            pageLength: 10
        });

        $(document).on('click', '.btn-submit-draft', function() {
            var no_ros = $(this).data('id');

            Swal.fire({
                title: 'Ajukan Draft?',
                html: 'Data ROS <b>' + no_ros + '</b> akan diajukan ke Finalize Incoming.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ajukan!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: siteurl + active_controller + 'submit_draft',
                    type: 'POST',
                    data: {
                        no_ros: no_ros
                    },
                    dataType: 'json',
                    success: function(r) {
                        if (r.status == 1) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: r.pesan ?? 'Draft berhasil diajukan.',
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(function() {
                                tableOpen.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal',
                                text: r.pesan ?? 'Gagal mengajukan draft.',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'Terjadi kesalahan sistem. Hubungi IT.',
                            icon: 'error'
                        });
                    }
                });
            });
        });

    });
</script>