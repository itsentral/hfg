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

<!-- Modal Konfirmasi Finalize -->
<div class="modal fade" id="modalFinalize" tabindex="-1" aria-labelledby="modalFinalizeLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalFinalizeLabel">
                    <i class="fa fa-check-circle"></i> Konfirmasi Finalize Incoming — ROS: <span id="modal-no-ros"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Info Header -->
                <div class="row mb-3" id="modal-header-info">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="35%" class="fw-bold">Supplier</td>
                                <td>: <span id="modal-supplier">-</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">No. PO</td>
                                <td>: <span id="modal-no-po">-</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">No. ROS</td>
                                <td>: <span id="modal-no-ros-info">-</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="fw-bold">Tanggal Incoming</label>
                            <input type="date" id="modal-tanggal" class="form-control"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Tabel Detail Coil -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="modal-table-coil">
                        <thead>
                            <tr>
                                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="3%">No</th>
                                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="20%">Material</th>
                                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="6%">Unit</th>
                                <th class="text-center" rowspan="2" style="vertical-align:middle;" width="8%">Qty PO</th>
                                <th class="text-center" colspan="4" style="background-color:#69c79d !important;">Data ROS (Packing List)</th>
                                <th class="text-center" rowspan="2" style="vertical-align:middle; background-color:#f3b44e !important;" width="8%">Status QC</th>
                                <th class="text-center" rowspan="2" style="vertical-align:middle; background-color:#c8e6c9 !important;" width="10%">Gudang Tujuan</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="background-color:#69c79d !important;">No. Coil</th>
                                <th class="text-center" style="background-color:#69c79d !important;">Berat Kotor</th>
                                <th class="text-center" style="background-color:#69c79d !important;">Berat Bersih</th>
                                <th class="text-center" style="background-color:#69c79d !important;">Panjang</th>
                            </tr>
                        </thead>
                        <tbody id="modal-body-coil">
                            <tr>
                                <td colspan="10" class="text-center">
                                    <i class="fa fa-spinner fa-spin"></i> Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-success" id="btn-confirm-finalize">
                    <i class="fa fa-check-circle"></i> Ya, Finalize Sekarang!
                </button>
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
        /* ── Tombol Finalize → buka modal preview ── */
        $(document).on('click', '.btn-finalize', function() {
            var no_ros = $(this).data('id');

            // Reset modal
            $('#modal-no-ros').text(no_ros);
            $('#modal-no-ros-info').text(no_ros);
            $('#modal-supplier').text('-');
            $('#modal-no-po').text('-');
            $('#modal-tanggal').val(new Date().toISOString().split('T')[0]);
            $('#modal-body-coil').html(
                '<tr><td colspan="10" class="text-center">' +
                '<i class="fa fa-spinner fa-spin"></i> Memuat data...</td></tr>'
            );
            $('#btn-confirm-finalize').data('ros', no_ros);

            // Tampilkan modal
            var modal = new bootstrap.Modal(document.getElementById('modalFinalize'));
            modal.show();

            // Load preview data coil
            $.ajax({
                url: siteurl + active_controller + 'get_draft_preview',
                type: 'POST',
                data: {
                    no_ros: no_ros
                },
                dataType: 'json',
                success: function(res) {
                    if (!res || res.status === 0) {
                        $('#modal-body-coil').html(
                            '<tr><td colspan="10" class="text-center text-danger">' +
                            (res.pesan || 'Gagal memuat data.') + '</td></tr>'
                        );
                        return;
                    }

                    // Isi header info
                    $('#modal-supplier').text(res.header.nm_supplier || '-');
                    $('#modal-no-po').text(res.header.no_po || '-');

                    // Render tabel coil
                    var html = '';
                    var grouped = {};
                    res.coils.forEach(function(c) {
                        var key = c.id_material;
                        if (!grouped[key]) grouped[key] = [];
                        grouped[key].push(c);
                    });

                    var no = 1;
                    Object.keys(grouped).forEach(function(key) {
                        var rows = grouped[key];
                        rows.forEach(function(row, idx) {
                            var qcBadge = row.status_qc === 'OK' ?
                                '<span class="badge bg-success">OK</span>' :
                                '<span class="badge bg-danger">REJECT</span>';

                            html += '<tr>';
                            if (idx === 0) {
                                html +=
                                    '<td class="text-center" rowspan="' + rows.length + '" style="vertical-align:middle;">' + no + '</td>' +
                                    '<td rowspan="' + rows.length + '" style="vertical-align:middle;">' +
                                    '<b>' + row.nm_material + '</b><br>' +
                                    '<small class="text-muted">' + row.id_material + '</small>' +
                                    '</td>' +
                                    '<td class="text-center" rowspan="' + rows.length + '" style="vertical-align:middle;">Kg</td>' +
                                    '<td class="text-end" rowspan="' + rows.length + '" style="vertical-align:middle;">' +
                                    parseFloat(row.qty_po || 0).toLocaleString('id-ID') +
                                    '</td>';
                            }
                            html +=
                                '<td class="text-center bg-light">' + row.no_coil + '</td>' +
                                '<td class="text-end bg-light">' + parseFloat(row.berat_kotor || 0).toLocaleString('id-ID') + '</td>' +
                                '<td class="text-end bg-light">' + parseFloat(row.berat_bersih || 0).toLocaleString('id-ID') + '</td>' +
                                '<td class="text-end bg-light">' + parseFloat(row.panjang || 0).toLocaleString('id-ID') + '</td>' +
                                '<td class="text-center">' + qcBadge + '</td>' +
                                '<td class="text-center">' + (row.kd_gudang_ke || '-') + '</td>' +
                                '</tr>';
                        });
                        no++;
                    });

                    $('#modal-body-coil').html(html);
                },
                error: function() {
                    $('#modal-body-coil').html(
                        '<tr><td colspan="10" class="text-center text-danger">Gagal koneksi ke server.</td></tr>'
                    );
                }
            });
        });

        /* ── Tombol konfirmasi di dalam modal ── */
        // $(document).on('click', '#btn-confirm-finalize', function() {
        //     var no_ros = $(this).data('ros');
        //     var tanggal = $('#modal-tanggal').val();

        //     if (!tanggal) {
        //         Swal.fire({
        //             title: 'Peringatan',
        //             text: 'Tanggal incoming wajib diisi!',
        //             icon: 'warning'
        //         });
        //         return;
        //     }

        //     // Tutup modal lalu proses
        //     bootstrap.Modal.getInstance(document.getElementById('modalFinalize')).hide();

        //     Swal.fire({
        //         title: 'Proses Finalize?',
        //         html: '<b>ROS: ' + no_ros + '</b><br>Stok dan jurnal akuntansi akan diproses. Tidak dapat dibatalkan!',
        //         icon: 'warning',
        //         showCancelButton: true,
        //         confirmButtonText: 'Ya, Finalize!',
        //         cancelButtonText: 'Batal',
        //         confirmButtonColor: '#28a745'
        //     }).then(function(result) {
        //         if (!result.isConfirmed) return;

        //         $.ajax({
        //             url: siteurl + active_controller + 'finalize',
        //             type: 'POST',
        //             data: {
        //                 no_ros: no_ros,
        //                 tanggal: tanggal
        //             },
        //             dataType: 'json',
        //             success: function(r) {
        //                 if (r.status == 1) {
        //                     Swal.fire({
        //                         title: 'Berhasil!',
        //                         text: r.pesan,
        //                         icon: 'success',
        //                         timer: 1800,
        //                         showConfirmButton: false
        //                     }).then(function() {
        //                         tableDraft.ajax.reload();
        //                         tableOpen.ajax.reload();
        //                         tableClose.ajax.reload();
        //                     });
        //                 } else if (r.status == 2) {
        //                     Swal.fire({
        //                             title: 'Perhatian',
        //                             text: r.pesan,
        //                             icon: 'warning'
        //                         })
        //                         .then(function() {
        //                             tableDraft.ajax.reload();
        //                             tableClose.ajax.reload();
        //                         });
        //                 } else {
        //                     Swal.fire({
        //                         title: 'Gagal',
        //                         text: r.pesan,
        //                         icon: 'error'
        //                     });
        //                 }
        //             }
        //         });
        //     });
        // });

        $(document).on('click', '#btn-confirm-finalize', function() {
            var no_ros = $(this).data('ros');
            var tanggal = $('#modal-tanggal').val();

            if (!tanggal) {
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Tanggal incoming wajib diisi!',
                    icon: 'warning'
                });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('modalFinalize')).hide();

            Swal.fire({
                title: 'Proses Finalize?',
                html: '<b>ROS: ' + no_ros + '</b><br>Stok dan jurnal akuntansi akan diproses. Tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Finalize!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: siteurl + active_controller + 'finalize',
                    type: 'POST',
                    data: {
                        no_ros: no_ros,
                        tanggal: tanggal
                    },
                    success: function(r) {
                        Swal.close();
                        if (r.status == 1) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: r.pesan,
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(function() {
                                tableDraft.ajax.reload();
                                tableOpen.ajax.reload();
                                tableClose.ajax.reload();
                            });
                        } else if (r.status == 2) {
                            Swal.fire({
                                    title: 'Perhatian',
                                    text: r.pesan,
                                    icon: 'warning'
                                })
                                .then(function() {
                                    tableDraft.ajax.reload();
                                    tableClose.ajax.reload();
                                });
                        } else if (r.status == 3) {
                            // COA tidak ditemukan di master
                            Swal.fire({
                                title: 'Master COA Tidak Lengkap!',
                                html: '<div class="text-start">' +
                                    '<p>Proses <b>Finalize</b> dibatalkan karena nomor COA berikut belum terdaftar di Master COA:</p>' +
                                    '<div class="alert alert-danger fw-bold">' + r.pesan.replace(/:\s*/, ':<br><code>').replace(/$/, '</code>') + '</div>' +
                                    '<p class="mb-0 text-muted small">Silakan tambahkan nomor COA tersebut di menu <b>Master COA</b> terlebih dahulu, lalu ulangi proses ini.</p>' +
                                    '</div>',
                                icon: 'error',
                                confirmButtonText: 'Mengerti',
                                confirmButtonColor: '#dc3545',
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal',
                                text: r.pesan,
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Gagal memproses finalize.', 'error');
                    }
                });
            });
        });
    });
</script>