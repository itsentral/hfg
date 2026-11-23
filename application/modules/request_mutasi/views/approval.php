<?php
$record = !empty($data) ? $data[0] : null;
$kd_mutasi  = $record ? $record->kd_mutasi : '';
$keterangan = $record ? $record->keterangan : '';
$dari       = $record ? $record->bank_asal : '';
$darinama   = $record ? $record->nama_bank_asal : '';
$ke         = $record ? $record->bank_tujuan : '';
$kenama     = $record ? $record->nama_bank_tujuan : '';
$nilai      = $record ? $record->nilai_request : 0;
$tanggal    = $record ? $record->tgl_request : '';
$matauang   = $record ? $record->mata_uang : 'IDR';
?>

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-check-circle me-2"></i>Review Approval Request Mutasi
            </h5>
            <small class="text-muted">Document Number: <span class="fw-bold text-dark"><?= htmlspecialchars((string) $kd_mutasi) ?></span></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= site_url('request_mutasi/approval_mutasi') ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <form method="post" id="frm_data">
            <input type="hidden" name="no_request" id="no_request" value="<?= htmlspecialchars((string) $kd_mutasi) ?>">

            <!-- Summary Info -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-muted small fw-semibold text-uppercase">No Request</span>
                        <h5 class="fw-bold text-primary mb-0 mt-1"><?= htmlspecialchars((string) $kd_mutasi) ?></h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-muted small fw-semibold text-uppercase">Request Date</span>
                        <h6 class="fw-bold text-dark mb-0 mt-1"><i class="fa fa-calendar me-1 text-muted"></i><?= date('d/m/Y', strtotime((string) $tanggal)) ?></h6>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-muted small fw-semibold text-uppercase">Currency</span>
                        <h6 class="fw-bold text-dark mb-0 mt-1"><?= htmlspecialchars((string) $matauang) ?></h6>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-muted small fw-semibold text-uppercase">Request Value</span>
                        <h5 class="fw-bold text-success mb-0 mt-1">Rp <?= number_format((float) $nilai, 0, ',', '.') ?></h5>
                    </div>
                </div>
            </div>

            <div class="card border border-light-subtle rounded-3 bg-light p-3 mb-4">
                <h6 class="fw-bold text-dark mb-2"><i class="fa fa-info-circle me-1 text-primary"></i>Description / Purpose:</h6>
                <p class="mb-0 text-dark"><?= nl2br(htmlspecialchars((string) $keterangan)) ?></p>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped align-middle w-100">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="15%">Request Date</th>
                            <th>Source Bank (Fund Source)</th>
                            <th>Destination Bank (Recipient)</th>
                            <th width="20%" class="text-end">Transfer Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center fw-medium"><?= date('d/m/Y', strtotime((string) $tanggal)) ?></td>
                            <td><i class="fa fa-bank me-1 text-muted"></i><strong><?= htmlspecialchars((string) $dari) ?></strong> - <?= htmlspecialchars((string) $darinama) ?></td>
                            <td><i class="fa fa-bank me-1 text-muted"></i><strong><?= htmlspecialchars((string) $ke) ?></strong> - <?= htmlspecialchars((string) $kenama) ?></td>
                            <td class="text-end fw-bold text-success fs-6">Rp <?= number_format((float) $nilai, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Decision Box -->
            <div class="card border border-primary-subtle rounded-3 p-3 bg-white shadow-sm">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Approval Decision</h6>
                        <small class="text-muted">Approve this mutation request so the cashier/finance can process its realization.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-danger px-4 reject" data-bs-toggle="modal" data-bs-target="#dialog-popup">
                            <i class="fa fa-times me-1"></i> Reject
                        </button>
                        <button type="button" class="btn btn-success px-4 shadow-sm approve">
                            <i class="fa fa-check me-1"></i> Approve
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="modalRejectLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold fs-6" id="modalRejectLabel">
                    <i class="fa fa-ban me-2"></i>Penolakan Request Mutasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="frm-reject">
                <input type="hidden" name="no_request" value="<?= htmlspecialchars((string) $kd_mutasi) ?>">
                <div class="modal-body p-4">
                    <label for="alasan" class="form-label fw-semibold text-muted small">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="alasan" id="alasan" rows="4" placeholder="Tuliskan alasan penolakan secara jelas..." required></textarea>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm px-3 reject_request">
                        <i class="fa fa-ban me-1"></i> Tolak Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Approve
        $(document).on('click', '.approve', function() {
            Swal.fire({
                title: "Approve Mutation Request?",
                text: "Mutation request <?= htmlspecialchars((string) $kd_mutasi) ?> will be approved for realization.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#198754",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-check me-1'></i> Yes, Approve!",
                cancelButtonText: "Cancel"
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Memproses...",
                        text: "Approving mutation request",
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: base_url + "request_mutasi/save_approval",
                        dataType: "json",
                        type: 'POST',
                        data: $("#frm_data").serialize(),
                        success: function(data) {
                            if (data.status == 1) {
                                Swal.fire({
                                    title: "Success!",
                                    text: data.pesan || "Mutation request approved successfully.",
                                    icon: "success"
                                }).then(function() {
                                    window.location.href = base_url + 'request_mutasi/approval_mutasi';
                                });
                            } else {
                                Swal.fire("Failed!", data.pesan || "Failed to approve the mutation request.", "error");
                            }
                        },
                        error: function() {
                            Swal.fire("Error!", "A system error occurred.", "error");
                        }
                    });
                }
            });
        });

        // Reject
        $(document).on('click', '.reject_request', function() {
            var alasan = $('#alasan').val();
            if (!alasan || !$.trim(alasan)) {
                Swal.fire("Reason Is Empty!", "Please fill in the rejection reason first.", "warning");
                $('#alasan').focus();
                return;
            }

            Swal.fire({
                title: "Reject Mutation Request?",
                text: "This request will be rejected with the entered reason.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-ban me-1'></i> Yes, Reject!",
                cancelButtonText: "Cancel"
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Memproses...",
                        text: "Rejecting mutation request",
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: base_url + "request_mutasi/reject_mutasi",
                        dataType: "json",
                        type: 'POST',
                        data: $("#frm-reject").serialize(),
                        success: function(data) {
                            if (data.status == 1) {
                                Swal.fire({
                                    title: "Success!",
                                    text: data.pesan || "Mutation request rejected successfully.",
                                    icon: "success"
                                }).then(function() {
                                    window.location.href = base_url + 'request_mutasi/approval_mutasi';
                                });
                            } else {
                                Swal.fire("Failed!", data.pesan || "Failed to reject the mutation request.", "error");
                            }
                        },
                        error: function() {
                            Swal.fire("Error!", "A system error occurred saat menolak request.", "error");
                        }
                    });
                }
            });
        });
    });
</script>
