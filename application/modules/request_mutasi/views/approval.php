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
            <small class="text-muted">Nomor Dokumen: <span class="fw-bold text-dark"><?= htmlspecialchars($kd_mutasi) ?></span></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= site_url('request_mutasi/approval_mutasi') ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <form method="post" id="frm_data">
            <input type="hidden" name="no_request" id="no_request" value="<?= htmlspecialchars($kd_mutasi) ?>">

            <!-- Summary Info -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-muted small fw-semibold text-uppercase">No Request</span>
                        <h5 class="fw-bold text-primary mb-0 mt-1"><?= htmlspecialchars($kd_mutasi) ?></h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-muted small fw-semibold text-uppercase">Tanggal Pengajuan</span>
                        <h6 class="fw-bold text-dark mb-0 mt-1"><i class="fa fa-calendar me-1 text-muted"></i><?= date('d/m/Y', strtotime($tanggal)) ?></h6>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-muted small fw-semibold text-uppercase">Mata Uang</span>
                        <h6 class="fw-bold text-dark mb-0 mt-1"><?= htmlspecialchars($matauang) ?></h6>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-muted small fw-semibold text-uppercase">Nilai Pengajuan</span>
                        <h5 class="fw-bold text-success mb-0 mt-1">Rp <?= number_format($nilai, 0, ',', '.') ?></h5>
                    </div>
                </div>
            </div>

            <div class="card border border-light-subtle rounded-3 bg-light p-3 mb-4">
                <h6 class="fw-bold text-dark mb-2"><i class="fa fa-info-circle me-1 text-primary"></i>Keterangan / Keperluan:</h6>
                <p class="mb-0 text-dark"><?= nl2br(htmlspecialchars($keterangan)) ?></p>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped align-middle w-100">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="15%">Tanggal Request</th>
                            <th>Bank Asal (Sumber Dana)</th>
                            <th>Bank Tujuan (Penerima)</th>
                            <th width="20%" class="text-end">Nilai Transfer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center fw-medium"><?= date('d/m/Y', strtotime($tanggal)) ?></td>
                            <td><i class="fa fa-bank me-1 text-muted"></i><strong><?= htmlspecialchars($dari) ?></strong> - <?= htmlspecialchars($darinama) ?></td>
                            <td><i class="fa fa-bank me-1 text-muted"></i><strong><?= htmlspecialchars($ke) ?></strong> - <?= htmlspecialchars($kenama) ?></td>
                            <td class="text-end fw-bold text-success fs-6">Rp <?= number_format($nilai, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Decision Box -->
            <div class="card border border-primary-subtle rounded-3 p-3 bg-white shadow-sm">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Keputusan Persetujuan (Approval)</h6>
                        <small class="text-muted">Setujui pengajuan mutasi ini agar dapat diproses realisasinya oleh kasir/finance.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-danger px-4 reject" data-bs-toggle="modal" data-bs-target="#dialog-popup">
                            <i class="fa fa-times me-1"></i> Tolak (Reject)
                        </button>
                        <button type="button" class="btn btn-success px-4 shadow-sm approve">
                            <i class="fa fa-check me-1"></i> Setujui (Approve)
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
                <input type="hidden" name="no_request" value="<?= htmlspecialchars($kd_mutasi) ?>">
                <div class="modal-body p-4">
                    <label for="alasan" class="form-label fw-semibold text-muted small">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="alasan" id="alasan" rows="4" placeholder="Tuliskan alasan penolakan secara jelas..." required></textarea>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
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
                title: "Setujui Request Mutasi?",
                text: "Pengajuan mutasi <?= htmlspecialchars($kd_mutasi) ?> akan disetujui untuk proses realisasi.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#198754",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-check me-1'></i> Ya, Setujui!",
                cancelButtonText: "Batal"
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Memproses...",
                        text: "Sedang menyetujui request mutasi",
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
                                    title: "Berhasil!",
                                    text: data.pesan || "Request mutasi berhasil disetujui.",
                                    icon: "success"
                                }).then(function() {
                                    window.location.href = base_url + 'request_mutasi/approval_mutasi';
                                });
                            } else {
                                Swal.fire("Gagal!", data.pesan || "Gagal menyetujui request mutasi.", "error");
                            }
                        },
                        error: function() {
                            Swal.fire("Error!", "Terjadi kesalahan sistem.", "error");
                        }
                    });
                }
            });
        });

        // Reject
        $(document).on('click', '.reject_request', function() {
            var alasan = $('#alasan').val();
            if (!alasan || !$.trim(alasan)) {
                Swal.fire("Alasan Kosong!", "Silakan isi alasan penolakan terlebih dahulu.", "warning");
                $('#alasan').focus();
                return;
            }

            Swal.fire({
                title: "Tolak Request Mutasi?",
                text: "Pengajuan ini akan ditolak dengan alasan yang telah diisi.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-ban me-1'></i> Ya, Tolak!",
                cancelButtonText: "Batal"
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Memproses...",
                        text: "Sedang menolak request mutasi",
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
                                    title: "Berhasil!",
                                    text: data.pesan || "Request mutasi berhasil ditolak.",
                                    icon: "success"
                                }).then(function() {
                                    window.location.href = base_url + 'request_mutasi/approval_mutasi';
                                });
                            } else {
                                Swal.fire("Gagal!", data.pesan || "Gagal menolak request mutasi.", "error");
                            }
                        },
                        error: function() {
                            Swal.fire("Error!", "Terjadi kesalahan sistem saat menolak request.", "error");
                        }
                    });
                }
            });
        });
    });
</script>
