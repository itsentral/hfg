<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-exchange me-2"></i>Form Transaksi Bank Langsung
            </h5>
            <small class="text-muted">Catat mutasi penerimaan atau pengeluaran kas/bank langsung beserta penjurnalannya</small>
        </div>
        <div>
            <a href="<?= site_url('request_mutasi/index_transaksi') ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke List Transaksi
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <form method="post" id="frm_data">
            <div class="row g-3 mb-3">
                <div class="col-md-6 col-lg-3">
                    <label for="no_request" class="form-label fw-semibold text-muted small">No Transaksi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-barcode"></i></span>
                        <input type="text" name="no_request" id="no_request" placeholder="Automatic (Auto)" class="form-control bg-light fw-bold text-primary border-start-0" readonly>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="jenis_transaksi" class="form-label fw-semibold text-muted small">Jenis Transaksi <span class="text-danger">*</span></label>
                    <select class="form-select select2" name="jenis_transaksi" id="jenis_transaksi" required style="width: 100%;">
                        <option value="terima">Penerimaan (BUM)</option>
                        <option value="keluar">Pengeluaran (BUK)</option>
                    </select>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="tgl_request" class="form-label fw-semibold text-muted small">Tanggal Transaksi <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="tgl_request" id="tgl_request" class="form-control flatpickr-date border-start-0" value="<?= date('Y-m-d') ?>" placeholder="Pilih tanggal..." required>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="matauang" class="form-label fw-semibold text-muted small">Mata Uang <span class="text-danger">*</span></label>
                    <?php
                    echo form_dropdown('matauang', $matauang, '', array('id' => 'matauang', 'required' => 'required', 'class' => 'form-select select2', 'style' => 'width: 100%;'));
                    ?>
                </div>

                <div class="col-md-6">
                    <label for="dari" class="form-label fw-semibold text-muted small">COA Asal <span class="text-danger">*</span></label>
                    <?php
                    echo form_dropdown('dari', $datacoa, '', array('id' => 'dari', 'required' => 'required', 'class' => 'form-select select2', 'style' => 'width: 100%;'));
                    ?>
                </div>

                <div class="col-md-6">
                    <label for="ke" class="form-label fw-semibold text-muted small">COA Tujuan <span class="text-danger">*</span></label>
                    <?php
                    echo form_dropdown('ke', $datbank, '', array('id' => 'ke', 'required' => 'required', 'class' => 'form-select select2', 'style' => 'width: 100%;'));
                    ?>
                </div>

                <div class="col-md-4">
                    <label for="nilai" class="form-label fw-semibold text-muted small">Nilai Valas / Nominal <span class="text-danger">*</span></label>
                    <input type="text" name="nilai" class="form-control text-end fw-bold divide" id="nilai" onblur="total()" placeholder="0" required>
                </div>

                <div class="col-md-4">
                    <label for="kurs" class="form-label fw-semibold text-muted small">Kurs <span class="text-danger">*</span></label>
                    <input type="text" name="kurs" class="form-control text-end divide" id="kurs" value="1" onblur="total()" required>
                </div>

                <div class="col-md-4">
                    <label for="transaksi" class="form-label fw-semibold text-muted small">Total IDR Transaksi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold">Rp</span>
                        <input type="text" name="transaksi" class="form-control text-end fw-bold text-success bg-light" id="transaksi" placeholder="0" readonly disabled>
                    </div>
                </div>

                <div class="col-12">
                    <label for="keterangan" class="form-label fw-semibold text-muted small">Keterangan Transaksi <span class="text-danger">*</span></label>
                    <textarea name="keterangan" class="form-control" id="keterangan" rows="2" placeholder="Keterangan transaksi bank..." required></textarea>
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
                    <i class="fa fa-save me-1"></i> Simpan Transaksi Bank
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
                '#tgl_request': 'Tanggal tidak boleh kosong!',
                '#dari': 'COA Asal tidak boleh kosong!',
                '#ke': 'COA Tujuan tidak boleh kosong!',
                '#nilai': 'Nilai tidak boleh kosong!',
                '#transaksi': 'Nilai transaksi tidak boleh kosong!',
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

            Swal.fire({
                title: "Simpan Transaksi Bank?",
                text: "Sistem akan otomatis mencatat mutasi dan ayat jurnal pembukuan.",
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
                        text: "Sedang memproses transaksi dan ayat jurnal",
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    $('#kurs, #transaksi').prop('disabled', false);
                    var formData = $("#frm_data").serialize();
                    checkKurs();
                    $('#transaksi').prop('disabled', true);

                    $.ajax({
                        url: base_url + "request_mutasi/save_transaksi",
                        dataType: "json",
                        type: "POST",
                        data: formData,
                        success: function(data) {
                            if (data.status == 1) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: data.pesan + " (" + (data.nomor || '') + ")",
                                    icon: "success"
                                }).then(function() {
                                    window.location.href = base_url + 'request_mutasi/index_transaksi';
                                });
                            } else {
                                Swal.fire({
                                    title: "Gagal!",
                                    text: data.pesan || "Gagal menyimpan transaksi bank.",
                                    icon: "error"
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: "Error!",
                                text: "Terjadi kesalahan sistem saat memproses transaksi.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });
    });

    function checkKurs() {
        var mu = ($('#matauang').val() || '').toString().trim().toUpperCase();
        var muText = ($('#matauang option:selected').text() || '').toString().trim().toUpperCase();

        if (mu === 'IDR' || mu === 'RUPIAH' || muText.indexOf('IDR') !== -1 || muText.indexOf('RUPIAH') !== -1 || mu === '' || mu === '0') {
            $('#kurs').val('1').prop('disabled', true).addClass('bg-light');
        } else {
            $('#kurs').prop('disabled', false).removeClass('bg-light');
        }
        total();
    }

    function total() {
        var nilaiStr = ($("#nilai").val() || "").toString().replace(/,/g, '');
        var kursStr = ($("#kurs").val() || "1").toString().replace(/,/g, '');
        var nilai = parseFloat(nilaiStr) || 0;
        var kurs = parseFloat(kursStr) || 1;
        var totalVal = Math.round(nilai * kurs);

        if (totalVal > 0) {
            $("#transaksi").val(totalVal.toLocaleString('en-US'));
        } else if (nilai === 0) {
            $("#transaksi").val('0');
        }
        fn_terbilang();
    }

    var terbilangTimeout = null;

    function fn_terbilang() {
        var nilai = $("#transaksi").val() || "";
        var matauang = $("#matauang").val() || "IDR";

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
    $('#nilai, #kurs').on('input keyup change blur', function() {
        total();
    });

    $("#transaksi").on("input keyup change blur", function() {
        clearTimeout(terbilangTimeout);
        terbilangTimeout = setTimeout(fn_terbilang, 300);
    });

    $("#matauang").on("change", function() {
        checkKurs();
        fn_terbilang();
    });

    // Inisialisasi status kurs saat pertama kali halaman dimuat
    checkKurs();
    if ($("#transaksi").val()) {
        fn_terbilang();
    }
</script>
