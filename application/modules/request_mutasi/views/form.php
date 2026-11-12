<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

<input type="hidden" id="id" name="id" value="<?php echo (isset($data->id) ? $data->id : ''); ?>">

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-random me-2"></i>Form Pembuatan Request Mutasi
            </h5>
            <small class="text-muted">Buat pengajuan transfer / pemindahan saldo antar rekening bank</small>
        </div>
        <div>
            <a href="<?= site_url('request_mutasi') ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke List
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <form method="post" id="frm_data">
            <div class="row g-3 mb-3">
                <div class="col-md-6 col-lg-3">
                    <label for="no_request" class="form-label fw-semibold text-muted small">No Request</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-barcode"></i></span>
                        <input type="text" name="no_request" id="no_request" placeholder="Automatic (Auto)" class="form-control bg-light fw-bold text-primary border-start-0" readonly>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="tgl_request" class="form-label fw-semibold text-muted small">Tanggal Request <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="tgl_request" id="tgl_request" class="form-control flatpickr-date border-start-0" value="<?= date('Y-m-d') ?>" placeholder="Pilih tanggal..." required>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="dari" class="form-label fw-semibold text-muted small">Bank Asal (Sumber) <span class="text-danger">*</span></label>
                    <?php
                    echo form_dropdown('dari', $datbank, '', array('id' => 'dari', 'required' => 'required', 'class' => 'form-select select2', 'style' => 'width: 100%;'));
                    ?>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="ke" class="form-label fw-semibold text-muted small">Bank Tujuan <span class="text-danger">*</span></label>
                    <?php
                    echo form_dropdown('ke', $datbank, '', array('id' => 'ke', 'required' => 'required', 'class' => 'form-select select2', 'style' => 'width: 100%;'));
                    ?>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="matauang" class="form-label fw-semibold text-muted small">Mata Uang <span class="text-danger">*</span></label>
                    <?php
                    echo form_dropdown('matauang', $matauang, '', array('id' => 'matauang', 'required' => 'required', 'class' => 'form-select select2', 'style' => 'width: 100%;'));
                    ?>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="nilai" class="form-label fw-semibold text-muted small">Nilai Transfer <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold">Rp</span>
                        <input type="text" name="nilai" class="form-control text-end fw-bold divide" id="nilai" placeholder="0" onblur="fn_terbilang()" required>
                    </div>
                </div>

                <div class="col-md-12 col-lg-6">
                    <label for="keterangan" class="form-label fw-semibold text-muted small">Keterangan / Keperluan <span class="text-danger">*</span></label>
                    <textarea name="keterangan" class="form-control" id="keterangan" rows="2" placeholder="Tuliskan keterangan keperluan mutasi bank..." required></textarea>
                </div>

                <div class="col-12">
                    <label for="terbilang" class="form-label fw-semibold text-muted small">Terbilang</label>
                    <input type="text" name="terbilang" id="terbilang" placeholder="Terbilang otomatis..." class="form-control bg-light text-uppercase fw-semibold" readonly>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <div class="d-flex align-items-center justify-content-end gap-2">
                <a class="btn btn-secondary px-3" onclick="history.back(); return false;">
                    <i class="fa fa-times me-1"></i> Batal
                </a>
                <button type="submit" name="save" class="btn btn-primary px-4 shadow-sm" id="submit">
                    <i class="fa fa-save me-1"></i> Simpan Request Mutasi
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var url_save = siteurl + 'request_mutasi/save/';

    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        if ($.fn.divide) {
            $('.divide').divide();
        }

        flatpickr('#tgl_request', {
            dateFormat: 'Y-m-d',
            locale: 'id',
            allowInput: true
        });

        $('#frm_data').on('submit', function(e) {
            e.preventDefault();

            let errors = {
                '#tgl_request': 'Tanggal Tidak Boleh Kosong!',
                '#matauang': 'Mata uang tidak boleh kosong!',
                '#dari': 'Bank Asal tidak boleh kosong!',
                '#ke': 'Bank Tujuan tidak boleh kosong!',
                '#nilai': 'Nilai tidak boleh kosong!',
                '#keterangan': 'Keterangan tidak boleh kosong!',
            };

            for (let field in errors) {
                if ($(field).val() === "" || $(field).val() === "0" || $(field).val() === null) {
                    Swal.fire({
                        title: errors[field],
                        text: "Silakan lengkapi data dengan benar!",
                        icon: "warning"
                    });
                    $(field).focus();
                    return;
                }
            }

            if ($('#dari').val() === $('#ke').val()) {
                Swal.fire({
                    title: "Bank Asal dan Bank Tujuan Sama!",
                    text: "Bank tujuan harus berbeda dengan bank asal.",
                    icon: "warning"
                });
                return;
            }

            Swal.fire({
                title: "Simpan Request Mutasi?",
                text: "Pastikan data bank dan nilai transfer sudah sesuai.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#0d6efd",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-save me-1'></i> Ya, Simpan!",
                cancelButtonText: "Batal"
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Menyimpan...",
                        text: "Sedang memproses request mutasi",
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: base_url + "request_mutasi/save",
                        dataType: "json",
                        type: "POST",
                        data: $("#frm_data").serialize(),
                        success: function(data) {
                            if (data.status == 1) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: data.pesan + " (" + (data.nomor || '') + ")",
                                    icon: "success"
                                }).then(function() {
                                    window.location.href = base_url + 'request_mutasi';
                                });
                            } else {
                                Swal.fire({
                                    title: "Gagal!",
                                    text: data.pesan || "Gagal menyimpan request mutasi.",
                                    icon: "error"
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: "Error!",
                                text: "Terjadi kesalahan sistem saat menyimpan data.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });
    });

        var terbilangTimeout = null;

    function fn_terbilang() {
        var nilai = $("#nilai").val() || "";
        var matauang = $("#matauang").val() || "IDR";

        // Bersihkan koma dan format angka
        var cleanNilai = nilai.replace(/,/g, "").trim();

        if (!cleanNilai || parseFloat(cleanNilai) <= 0) {
            $("#terbilang").val("");
            return;
        }

        var ajaxUrl = (typeof siteurl !== 'undefined' ? siteurl : base_url) + "request_mutasi/terbilang";

        $.ajax({
            type: "GET",
            url: ajaxUrl,
            data: {
                nilai: cleanNilai,
                matauang: matauang
            },
            success: function(response) {
                $("#terbilang").val(response);
            },
            error: function() {
                console.warn("Gagal mengambil teks terbilang");
            }
        });
    }

    // Event listeners
    $("#nilai").on("input keyup change blur", function() {
        clearTimeout(terbilangTimeout);
        terbilangTimeout = setTimeout(fn_terbilang, 300);
    });

    $("#matauang").on("change", function() {
        fn_terbilang();
    });

    // Jalankan saat load jika ada nilai
    if ($("#nilai").val()) {
        fn_terbilang();
    }
</script>
