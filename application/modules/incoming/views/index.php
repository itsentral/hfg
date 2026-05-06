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
                <a class="nav-link" id="tab-draft-tab" data-bs-toggle="tab" href="#tab-draft" role="tab">
                    <i class="fa fa-clock"></i> Draft
                    <span class="badge bg-info ms-1" id="draft-count"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-close-tab" data-bs-toggle="tab" href="#tab-close" role="tab">
                    <i class="fa fa-check-circle"></i> Close
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

            <!-- TAB DRAFT -->
            <div class="tab-pane fade" id="tab-draft" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <table id="table-incoming-draft" class="table table-bordered table-striped dt-responsive" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. ROS</th>
                                    <th>No. PO / Surat</th>
                                    <th>Supplier</th>
                                    <th>Tgl Draft</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB CLOSE (BARU) -->
            <div class="tab-pane fade" id="tab-close" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <table id="table-incoming-close" class="table table-bordered table-striped dt-responsive" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. ROS</th>
                                    <th>No. PO / Surat</th>
                                    <th>Supplier</th>
                                    <th>Tgl Finalize</th>
                                    <th>Kode Incoming</th>
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

<!-- SweetAlert2 JS -->
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
            columns: [{
                    data: 0
                }, {
                    data: 1
                }, {
                    data: 2
                }, {
                    data: 3
                },
                {
                    data: 4
                }, {
                    data: 5
                }, {
                    data: 6,
                    orderable: false
                }
            ],
            order: [
                [1, 'desc']
            ]
        });

        // DataTable Draft
        var tableDraft = $('#table-incoming-draft').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'data_side_draft',
                type: 'POST',
                dataSrc: function(json) {
                    // Update badge count
                    $('#draft-count').text(json.recordsTotal > 0 ? json.recordsTotal : '');
                    return json.data;
                }
            },
            columns: [{
                    data: 0
                }, {
                    data: 1
                }, {
                    data: 2
                }, {
                    data: 3
                },
                {
                    data: 4
                }, {
                    data: 5
                }, {
                    data: 6
                }, {
                    data: 7,
                    orderable: false
                }
            ],
            order: [
                [4, 'desc']
            ]
        });

        // DataTable Close
        var tableClose = $('#table-incoming-close').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: siteurl + active_controller + 'data_side_close',
                type: 'POST'
            },
            columns: [{
                    data: 0
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
                    data: 5
                },
                {
                    data: 6
                },
                {
                    data: 7,
                    orderable: false
                }
            ],
            order: [
                [4, 'desc']
            ]
        });

        $('#tab-draft-tab').one('shown.bs.tab', function() {
            tableDraft.ajax.reload();
        });

        $('#tab-close-tab').one('shown.bs.tab', function() {
            tableClose.ajax.reload();
        });

        // Tombol Finalize
        $(document).on('click', '.btn-finalize', function() {
            var no_ros = $(this).data('id');

            Swal.fire({
                title: 'Finalize Incoming?',
                html: '<b>ROS: ' + no_ros + '</b><br>Proses stok dan jurnal akuntansi akan dijalankan. Tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Finalize!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                // Form kecil untuk upload file & tanggal saat finalize
                Swal.fire({
                    title: 'Konfirmasi Tanggal Incoming',
                    html: `<div class="text-start">
                    <div class="mb-2">
                        <label class="form-label fw-bold">Tanggal Incoming</label>
                        <input type="date" id="swal-tanggal" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Finalize Sekarang',
                    cancelButtonText: 'Batal',
                    preConfirm: function() {
                        var tgl = document.getElementById('swal-tanggal').value;
                        if (!tgl) {
                            Swal.showValidationMessage('Tanggal wajib diisi!');
                        }
                        return {
                            tanggal: tgl
                        };
                    }
                }).then(function(res2) {
                    if (!res2.isConfirmed) return;

                    $.ajax({
                        url: siteurl + active_controller + 'finalize',
                        type: 'POST',
                        data: {
                            no_ros: no_ros,
                            tanggal: res2.value.tanggal
                        },
                        dataType: 'json',
                        success: function(r) {
                            if (r.status == 1) {
                                Swal.fire({
                                        title: 'Berhasil!',
                                        text: r.pesan,
                                        icon: 'success',
                                        timer: 1800,
                                        showConfirmButton: false
                                    })
                                    .then(function() {
                                        tableDraft.ajax.reload();
                                        tableOpen.ajax.reload();
                                    });
                            } else if (r.status == 2) {
                                Swal.fire({
                                        title: 'Perhatian',
                                        text: r.pesan,
                                        icon: 'warning'
                                    })
                                    .then(function() {
                                        tableDraft.ajax.reload();
                                        tableOpen.ajax.reload();
                                    });
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: r.pesan,
                                    icon: 'error'
                                });
                            }
                        }
                    });
                });
            });
        });
    });
</script>