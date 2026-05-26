<div class="card">
    <div class="card-body">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-3" id="incomingTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-open-tab" data-bs-toggle="tab" href="#tab-open" role="tab">
                    <i class="fa fa-inbox"></i> Open ROS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-saved-tab" data-bs-toggle="tab" href="#tab-saved" role="tab">
                    <i class="fa fa-save"></i> Saved
                    <span class="badge bg-warning ms-1" id="saved-count"></span>
                </a>
            </li>
        </ul>

        <div class="tab-content" id="incomingTabContent">

            <!-- TAB OPEN -->
            <div class="tab-pane fade show active" id="tab-open" role="tabpanel">
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
            </div>

            <!-- TAB SAVED -->
            <div class="tab-pane fade" id="tab-saved" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <table id="table-incoming-saved" class="table table-bordered table-striped dt-responsive" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. ROS</th>
                                    <th>No. PO / Surat</th>
                                    <th>Supplier</th>
                                    <th>Tgl Simpan</th>
                                    <th>Disimpan Oleh</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        // DataTable Open
        var tableOpen = $('#table-incoming-open').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'data_side_incoming',
                type: 'POST'
            },
            columns: [
                { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 },
                { data: 4 }, { data: 5 }, { data: 6, orderable: false }
            ],
            order: [[1, 'desc']]
        });

        // DataTable Saved
        var tableSaved = $('#table-incoming-saved').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'data_side_saved',
                type: 'POST',
                dataSrc: function(json) {
                    $('#saved-count').text(json.recordsTotal > 0 ? json.recordsTotal : '');
                    return json.data;
                }
            },
            columns: [
                { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 },
                { data: 4 }, { data: 5 }, { data: 6 }, { data: 7, orderable: false }
            ],
            order: [[4, 'desc']]
        });

        $('#tab-saved-tab').one('shown.bs.tab', function() {
            tableSaved.ajax.reload();
        });

        /* ── Tombol Ajukan Draft ── */
        $(document).on('click', '.btn-submit-draft', function() {
            var no_ros = $(this).data('id');

            Swal.fire({
                title: 'Ajukan Draft?',
                html: 'Data ROS <b>' + no_ros + '</b> akan diajukan ke Finalize Incoming.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ajukan!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: siteurl + active_controller + 'submit_draft',
                    type: 'POST',
                    data: { no_ros: no_ros },
                    dataType: 'json',
                    success: function(r) {
                        if (r.status == 1) {
                            Swal.fire({
                                title: 'Berhasil!', text: r.pesan, icon: 'success',
                                timer: 1800, showConfirmButton: false
                            }).then(function() {
                                tableSaved.ajax.reload();
                            });
                        } else {
                            Swal.fire({ title: 'Gagal', text: r.pesan, icon: 'error' });
                        }
                    }
                });
            });
        });

    });
</script>
