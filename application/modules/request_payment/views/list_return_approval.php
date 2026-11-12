<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-check-circle me-2"></i>Daftar Approval Pengembalian Expense
            </h5>
            <small class="text-muted">Persetujuan transfer dana pengembalian expense yang diserahkan karyawan</small>
        </div>
        <div>
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                <i class="fa fa-clock-o me-1"></i>Antrian Waiting Approval
            </span>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%" class="text-center">No.</th>
                        <th width="25%" class="text-center">No Dokumen</th>
                        <th width="25%" class="text-end">Nilai Pengembalian</th>
                        <th width="25%" class="text-center">Status</th>
                        <th width="20%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (!empty($data_pengembalian)) {
                        foreach ($data_pengembalian as $item) {
                            $status = '<span class="badge bg-info text-dark px-2 py-1"><i class="fa fa-clock-o me-1"></i>Waiting Approval</span>';
                            if ($item->status == 2) {
                                $status = '<span class="badge bg-danger px-2 py-1"><i class="fa fa-times-circle me-1"></i>Rejected</span>';
                            }

                            echo '<tr>';
                            echo '<td class="text-center text-muted fw-semibold">' . $no . '</td>';
                            echo '<td class="text-center fw-bold text-primary">' . htmlspecialchars($item->no_doc) . '</td>';
                            echo '<td class="text-end fw-semibold">Rp ' . number_format($item->transfer_jumlah, 0, ',', '.') . '</td>';
                            echo '<td class="text-center">' . $status . '</td>';
                            echo '<td class="text-center text-nowrap">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-success shadow-sm approval px-3" data-id="' . $item->id . '" title="Setujui"><i class="fa fa-check me-1"></i> Approve</button>
                                        <button type="button" class="btn btn-outline-danger shadow-sm reject px-3" data-id="' . $item->id . '" title="Tolak"><i class="fa fa-times me-1"></i> Reject</button>
                                    </div>
                                </td>';
                            echo '</tr>';

                            $no++;
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal View Expense (Bootstrap 5 Compatible) -->
<div class="modal fade" id="Mymodal" tabindex="-1" aria-labelledby="modalExpenseApproveLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold fs-6" id="modalExpenseApproveLabel">
                    <i class="fa fa-file-text-o me-2"></i>View Expense
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="listexpense">
                <div class="text-center py-5">
                    <i class="fa fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                    <p class="text-muted">Memuat data expense...</p>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" data-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#mytabledata").DataTable({
            order: [[0, 'asc']],
            pageLength: 10,
            responsive: true
        });
    });

    $(document).on('click', '.approval', function() {
        var id = $(this).data('id');

        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title: "Anda Yakin?",
                text: "Pengembalian expense akan di-approve!",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#198754",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-check me-1'></i> Ya, Approve!",
                cancelButtonText: "Batal"
            }).then(function(result) {
                if (result.isConfirmed) {
                    processApproval(id);
                }
            });
        } else if (typeof swal !== 'undefined') {
            swal({
                title: "Anda Yakin?",
                text: "Pengembalian expense akan di-approve!",
                type: "info",
                showCancelButton: true,
                confirmButtonText: "Ya, Approve!",
                cancelButtonText: "Tidak!",
                closeOnConfirm: false,
                closeOnCancel: true
            }, function(isConfirm) {
                if (isConfirm) {
                    processApproval(id);
                }
            });
        } else {
            if (confirm("Pengembalian expense akan di-approve. Lanjutkan?")) {
                processApproval(id);
            }
        }
    });

    function processApproval(id) {
        $.ajax({
            url: siteurl + active_controller + 'approve_pengembalian_expense',
            dataType: "json",
            type: 'POST',
            data: { 'id': id },
            cache: false,
            success: function(msg) {
                if (msg.status == '1') {
                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire({
                            title: "Sukses!",
                            text: "Data Pengembalian berhasil di-approve",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() { window.location.reload(); });
                    } else if (typeof swal !== 'undefined') {
                        swal({
                            title: "Sukses!",
                            text: "Data Pengembalian berhasil di approve",
                            type: "success",
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(function() { window.location.reload(); }, 1500);
                    } else {
                        alert("Data Pengembalian berhasil di-approve");
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire("Gagal!", "Data Pengembalian gagal di-approve!", "error");
                    } else if (typeof swal !== 'undefined') {
                        swal("Gagal!", "Data Pengembalian gagal Di Approve !", "error");
                    } else {
                        alert("Data Pengembalian gagal di-approve");
                    }
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire("Gagal!", "Ajax Data Gagal Di Proses", "error");
                } else if (typeof swal !== 'undefined') {
                    swal("Gagal!", "Ajax Data Gagal Di Proses", "error");
                } else {
                    alert("Ajax Data Gagal Di Proses");
                }
            }
        });
    }

    $(document).on('click', '.reject', function() {
        var id = $(this).data('id');

        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title: "Anda Yakin?",
                text: "Pengembalian expense akan di-reject!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-times me-1'></i> Ya, Reject!",
                cancelButtonText: "Batal"
            }).then(function(result) {
                if (result.isConfirmed) {
                    processReject(id);
                }
            });
        } else if (typeof swal !== 'undefined') {
            swal({
                title: "Anda Yakin?",
                text: "Pengembalian expense akan di reject !",
                type: "info",
                showCancelButton: true,
                confirmButtonText: "Ya, Reject!",
                cancelButtonText: "Tidak!",
                closeOnConfirm: false,
                closeOnCancel: true
            }, function(isConfirm) {
                if (isConfirm) {
                    processReject(id);
                }
            });
        } else {
            if (confirm("Pengembalian expense akan di-reject. Lanjutkan?")) {
                processReject(id);
            }
        }
    });

    function processReject(id) {
        $.ajax({
            url: siteurl + active_controller + 'reject_pengembalian_expense',
            dataType: "json",
            type: 'POST',
            data: { 'id': id },
            cache: false,
            success: function(msg) {
                if (msg.status == '1') {
                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire({
                            title: "Sukses!",
                            text: "Data Pengembalian berhasil di-reject",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() { window.location.reload(); });
                    } else if (typeof swal !== 'undefined') {
                        swal({
                            title: "Sukses!",
                            text: "Data Pengembalian berhasil di reject",
                            type: "success",
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(function() { window.location.reload(); }, 1500);
                    } else {
                        alert("Data Pengembalian berhasil di-reject");
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire("Gagal!", "Data Pengembalian gagal di-reject!", "error");
                    } else if (typeof swal !== 'undefined') {
                        swal("Gagal!", "Data Pengembalian gagal Di Reject !", "error");
                    } else {
                        alert("Data Pengembalian gagal di-reject");
                    }
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire("Gagal!", "Ajax Data Gagal Di Proses", "error");
                } else if (typeof swal !== 'undefined') {
                    swal("Gagal!", "Ajax Data Gagal Di Proses", "error");
                } else {
                    alert("Ajax Data Gagal Di Proses");
                }
            }
        });
    }
</script>
