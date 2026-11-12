<?php
$record = !empty($result) ? $result[0] : null;
$kd_mutasi  = $record ? $record->kd_mutasi : '';
$keterangan = $record ? $record->keterangan : '';
$dari       = $record ? $record->bank_asal : '';
$darinama   = $record ? $record->nama_bank_asal : '';
$ke         = $record ? $record->bank_tujuan : '';
$kenama     = $record ? $record->nama_bank_tujuan : '';
$nilai      = $record ? $record->nilai_request : 0;
$tanggal    = $record ? $record->tgl_request : date('Y-m-d');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-check-circle-o me-2"></i>Form Realisasi Mutasi Bank
            </h5>
            <small class="text-muted">Eksekusi pencatatan aktual transfer bank untuk request <span class="fw-bold text-dark"><?= htmlspecialchars($kd_mutasi) ?></span></small>
        </div>
        <div>
            <a href="<?= site_url('request_mutasi/approval_mutasi') ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <form method="post" id="frm_data">
            <input type="hidden" name="no_request" id="no_request" value="<?= htmlspecialchars($kd_mutasi) ?>">
            <input type="hidden" name="tgl_request" id="tgl_request" value="<?= htmlspecialchars($tanggal) ?>">
            <input type="hidden" name="dari" id="dari" value="<?= htmlspecialchars($dari) ?>">
            <input type="hidden" name="ke" id="ke" value="<?= htmlspecialchars($ke) ?>">

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold">No Request</label>
                    <div class="fw-bold fs-6 text-primary"><?= htmlspecialchars($kd_mutasi) ?></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold">Tanggal Request</label>
                    <div class="fw-semibold text-dark"><i class="fa fa-calendar me-1 text-muted"></i><?= date('d/m/Y', strtotime($tanggal)) ?></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold">Bank Asal</label>
                    <div class="fw-semibold text-dark"><i class="fa fa-bank me-1 text-muted"></i><?= htmlspecialchars($darinama ?: $dari) ?></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold">Bank Tujuan</label>
                    <div class="fw-semibold text-dark"><i class="fa fa-bank me-1 text-muted"></i><?= htmlspecialchars($kenama ?: $ke) ?></div>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="tgl" class="form-label fw-semibold text-muted small">Tanggal Mutasi Aktual <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="tgl" id="tgl" class="form-control flatpickr-date border-start-0" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="nilai" class="form-label fw-semibold text-muted small">Nilai Request (Valas/IDR)</label>
                    <input type="text" name="nilai" class="form-control text-end bg-light fw-bold" id="nilai" value="<?= number_format($nilai, 0, ',', '.') ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="kurs" class="form-label fw-semibold text-muted small">Kurs</label>
                    <input type="text" name="kurs" class="form-control text-end divide" id="kurs" value="1" onblur="total()">
                </div>

                <div class="col-md-4">
                    <label for="rupiah" class="form-label fw-semibold text-muted small">Nilai Aktual IDR <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold">Rp</span>
                        <input type="text" name="rupiah" class="form-control text-end fw-bold text-success divide" id="rupiah" value="<?= number_format($nilai, 0, ',', '.') ?>" required>
                    </div>
                </div>

                <div class="col-md-8">
                    <label for="keterangan" class="form-label fw-semibold text-muted small">Keterangan Tambahan</label>
                    <textarea name="keterangan" class="form-control" id="keterangan" rows="2"><?= htmlspecialchars($keterangan) ?></textarea>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <div class="d-flex align-items-center justify-content-end gap-2">
                <a class="btn btn-secondary px-3" onclick="history.back(); return false;">
                    <i class="fa fa-times me-1"></i> Batal
                </a>
                <button type="submit" name="save" class="btn btn-success px-4 shadow-sm" id="submit">
                    <i class="fa fa-save me-1"></i> Simpan Realisasi Mutasi
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var url_save = siteurl + 'request_mutasi/save_mutasi/';

    $(document).ready(function() {
        if ($.fn.divide) {
            $('.divide').divide();
        }

        flatpickr('#tgl', {
            dateFormat: 'Y-m-d',
            locale: 'id',
            allowInput: true
        });

        $('#frm_data').on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: "Konfirmasi Realisasi Mutasi",
                text: "Sistem akan menyimpan mutasi aktual dan membuat jurnal BUM.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#198754",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-save me-1'></i> Ya, Simpan!",
                cancelButtonText: "Batal"
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Menyimpan...",
                        text: "Sedang memproses realisasi mutasi",
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: url_save,
                        type: 'POST',
                        data: $('#frm_data').serialize(),
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: res.pesan + ' No: ' + (res.nomor || ''),
                                    icon: "success"
                                }).then(function() {
                                    window.location.href = base_url + 'request_mutasi/mutasi';
                                });
                            } else {
                                Swal.fire("Gagal!", res.pesan || "Gagal menyimpan mutasi.", "error");
                            }
                        },
                        error: function() {
                            Swal.fire("Error!", "Terjadi kesalahan sistem, silakan coba lagi.", "error");
                        }
                    });
                }
            });
        });
    });

    function total() {
        var nilai = parseFloat($('#nilai').val().replace(/\./g, '').replace(/,/g, '')) || 0;
        var kurs = parseFloat($('#kurs').val().replace(/\./g, '').replace(/,/g, '')) || 1;
        var hasil = nilai * kurs;
        $('#rupiah').val(hasil.toLocaleString('id-ID'));
    }
</script>
