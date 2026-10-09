<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .card {
        border-radius: 0.75rem;
    }
    .form-label {
        font-size: 0.875rem;
        margin-bottom: 0.35rem;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-10 col-12">
        <form action="#" method="POST" id="form_proses" autocomplete="off">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7 col-12">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                    <i class="fa fa-sliders fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="card-title fw-bold text-dark mb-0">Tambah Adjustment Stok</h5>
                                    <small class="text-muted">Formulir penyesuaian kuantitas fisik stok barang di gudang</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 col-12 text-md-end">
                            <a href="<?= base_url('adjustment_stock') ?>" class="btn btn-outline-secondary shadow-sm" id="back">
                                <i class="fa fa-arrow-left me-1"></i> Kembali ke Riwayat
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Tipe Adjustment -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-dark">
                                Tipe Adjustment <span class="text-danger">*</span>
                            </label>
                            <select name="adjustment_type" id="adjustment_type" class="form-select select2" required>
                                <option value="0">-- Pilih Tipe Adjustment --</option>
                                <option value="plus">PLUS (Penambahan Stok)</option>
                                <option value="minus">MINUS (Pengurangan Stok)</option>
                                <option value="mutasi">MUTASI (Antar Gudang)</option>
                            </select>
                        </div>

                        <!-- No Berita Acara -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-dark">
                                No Berita Acara (BA) / Referensi <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa fa-file-lines text-muted"></i></span>
                                <input type="text" name="no_ba" id="no_ba" class="form-control" placeholder="Contoh: BA/STOK/2026/001" required>
                            </div>
                        </div>

                        <!-- SECTION: PLUS / MINUS -->
                        <div class="col-md-6 col-12 gudang_plus_min" style="display: none;">
                            <label class="form-label fw-bold text-dark">
                                Gudang Stok <span class="text-danger">*</span>
                            </label>
                            <select name="id_gudang_ke" id="id_gudang_ke" class="form-select select2">
                                <option value="0">-- Pilih Gudang --</option>
                                <?php foreach ($gudang as $val => $valx) : ?>
                                    <option value="<?= $valx['id']; ?>">
                                        <?= strtoupper($valx['nm_gudang']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 col-12 gudang_plus_min" style="display: none;">
                            <label class="form-label fw-bold text-dark">
                                PIC Petugas / Pemohon <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa fa-user text-muted"></i></span>
                                <input type="text" name="pic" id="pic" class="form-control" placeholder="Nama PIC...">
                            </div>
                        </div>

                        <!-- SECTION: MUTASI -->
                        <div class="col-md-4 col-12 gudang_mutasi" style="display: none;">
                            <label class="form-label fw-bold text-dark">
                                Dari Gudang <span class="text-danger">*</span>
                            </label>
                            <select name="id_gudang_dari_m" id="id_gudang_dari_m" class="form-select select2">
                                <option value="0">-- Pilih Gudang Asal --</option>
                                <?php foreach ($gudang as $val => $valx) : ?>
                                    <option value="<?= $valx['id']; ?>">
                                        <?= strtoupper($valx['nm_gudang']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 col-12 gudang_mutasi" style="display: none;">
                            <label class="form-label fw-bold text-dark">
                                Ke Gudang Tujuan <span class="text-danger">*</span>
                            </label>
                            <select name="id_gudang_ke_m" id="id_gudang_ke_m" class="form-select select2">
                                <option value="0">List Empty</option>
                            </select>
                        </div>

                        <div class="col-md-4 col-12 gudang_mutasi" style="display: none;">
                            <label class="form-label fw-bold text-dark">
                                PIC Petugas Mutasi <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa fa-user text-muted"></i></span>
                                <input type="text" name="pic_m" id="pic_m" class="form-control" placeholder="Nama PIC Mutasi...">
                            </div>
                        </div>

                        <!-- Pilihan Barang Stok -->
                        <div class="col-md-8 col-12">
                            <label class="form-label fw-bold text-dark">
                                Nama Barang Stok <span class="text-danger">*</span>
                            </label>
                            <select name="id_material" id="id_material" class="form-select select2" required>
                                <option value="0">Pilih Barang Stok (Pilih Tipe Dahulu)</option>
                            </select>
                        </div>

                        <!-- Quantity Adjustment -->
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold text-dark">
                                Qty Adjustment <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa fa-calculator text-muted"></i></span>
                                <input type="text" name="qty_oke" id="qty_oke" class="form-control text-end autoNumeric2" placeholder="0.00" required>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark">Keterangan / Alasan Penyesuaian</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Masukkan keterangan adjustment (misal: Hasil stock opname fisik gudang, barang rusak, dll)..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top py-3 text-end">
                    <a href="<?= base_url('adjustment_stock') ?>" class="btn btn-outline-secondary me-2">
                        <i class="fa fa-times me-1"></i> Batal
                    </a>
                    <button type="button" class="btn btn-primary px-4 shadow-sm" id="simpan">
                        <i class="fa fa-save me-1"></i> Simpan Adjustment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var getBaseUrl = function() {
            if (typeof siteurl !== 'undefined') return siteurl;
            if (typeof base_url !== 'undefined') return base_url;
            return '<?= base_url() ?>';
        };

        var controllerPath = (typeof active_controller !== 'undefined') ? active_controller : 'adjustment_stock/';
        if (!controllerPath.endsWith('/')) {
            controllerPath += '/';
        }

        function notifyAlert(title, text, type) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: (type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'success')),
                    title: title,
                    text: text
                });
            } else if (typeof swal === 'function') {
                swal({
                    title: title,
                    text: text,
                    type: type
                });
            } else {
                alert(title + ": " + text);
            }
        }

        // Inisialisasi Select2
        if ($('.select2').length) {
            $('.select2').select2({ width: '100%' });
        }

        // Inisialisasi AutoNumeric secara aman
        if (typeof $.fn.autoNumeric === 'function') {
            $(".autoNumeric2").autoNumeric('init', {
                mDec: '2',
                aPad: false
            });
        }

        $(document).on('input', '.autoNumeric2', function() {
            if (typeof $.fn.autoNumeric !== 'function') {
                this.value = this.value.replace(/[^0-9.]/g, '');
            }
        });

        // Toggle tipe adjustment
        $(document).on('change', '#adjustment_type', function(e) {
            var type = $(this).val();

            if (type === 'plus' || type === 'minus') {
                $('.gudang_mutasi').hide();
                $('.gudang_plus_min').fadeIn();

                $.ajax({
                    url: getBaseUrl() + controllerPath + 'list_material',
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    success: function(data) {
                        $("#id_material").html(data.option);
                        if ($('#id_material').data('select2')) {
                            $('#id_material').trigger('change');
                        }
                    },
                    error: function() {
                        notifyAlert("Error Message!", "Gagal memuat daftar barang stok", "warning");
                    }
                });
            } else if (type === 'mutasi') {
                $('.gudang_plus_min').hide();
                $('.gudang_mutasi').fadeIn();
                $("#id_material").html("<option value='0'>Pilih Gudang Asal Terlebih Dahulu</option>").trigger('change');
            } else {
                $('.gudang_mutasi').hide();
                $('.gudang_plus_min').hide();
                $("#id_material").html("<option value='0'>Pilih Tipe Adjustment Dahulu</option>").trigger('change');
            }
        });

        // Trigger jika gudang asal mutasi dipilih
        $(document).on('change', '#id_gudang_dari_m', function(e) {
            var type = $('#adjustment_type').val();
            var gudangVal = $(this).val();

            // Load gudang tujuan
            $.ajax({
                url: getBaseUrl() + controllerPath + 'list_gudang_ke',
                cache: false,
                type: "POST",
                data: {
                    'gudang': gudangVal,
                    'tandax': 'MOVE'
                },
                dataType: "json",
                success: function(data) {
                    $("#id_gudang_ke_m").html(data.option);
                    if ($('#id_gudang_ke_m').data('select2')) {
                        $('#id_gudang_ke_m').trigger('change');
                    }
                },
                error: function() {
                    notifyAlert("Error Message!", "Gagal memuat daftar gudang tujuan", "warning");
                }
            });

            // Load material berdasarkan stok gudang asal jika mutasi
            if (type === 'mutasi') {
                $.ajax({
                    url: getBaseUrl() + controllerPath + 'list_material_stock',
                    cache: false,
                    type: "POST",
                    data: {
                        'gudang': gudangVal
                    },
                    dataType: "json",
                    success: function(data) {
                        $("#id_material").html(data.option);
                        if ($('#id_material').data('select2')) {
                            $('#id_material').trigger('change');
                        }
                    },
                    error: function() {
                        notifyAlert("Error Message!", "Gagal memuat material dari gudang asal", "warning");
                    }
                });
            }
        });

        // Simpan Form Adjustment
        $(document).on('click', '#simpan', function(e) {
            e.preventDefault();

            var adjustment_type = $('#adjustment_type').val();
            var id_material = $('#id_material').val();
            var no_ba = $('#no_ba').val();
            var qty_oke = $('#qty_oke').val();

            var id_gudang_dari_m = $('#id_gudang_dari_m').val();
            var id_gudang_ke_m = $('#id_gudang_ke_m').val();
            var pic_m = $('#pic_m').val();

            var id_gudang_ke = $('#id_gudang_ke').val();
            var pic = $('#pic').val();

            if (!adjustment_type || adjustment_type === '0') {
                notifyAlert("Peringatan!", "Silakan pilih Tipe Adjustment terlebih dahulu!", "warning");
                $('#adjustment_type').focus();
                return false;
            }

            if (adjustment_type === 'mutasi') {
                if (!id_gudang_dari_m || id_gudang_dari_m === '0') {
                    notifyAlert("Peringatan!", "Gudang asal wajib dipilih!", "warning");
                    $('#id_gudang_dari_m').focus();
                    return false;
                }
                if (!id_gudang_ke_m || id_gudang_ke_m === '0') {
                    notifyAlert("Peringatan!", "Gudang tujuan wajib dipilih!", "warning");
                    $('#id_gudang_ke_m').focus();
                    return false;
                }
                if (!pic_m || pic_m.trim() === '') {
                    notifyAlert("Peringatan!", "Nama PIC Mutasi wajib diisi!", "warning");
                    $('#pic_m').focus();
                    return false;
                }
            } else {
                if (!id_gudang_ke || id_gudang_ke === '0') {
                    notifyAlert("Peringatan!", "Gudang stok wajib dipilih!", "warning");
                    $('#id_gudang_ke').focus();
                    return false;
                }
                if (!pic || pic.trim() === '') {
                    notifyAlert("Peringatan!", "Nama PIC Petugas wajib diisi!", "warning");
                    $('#pic').focus();
                    return false;
                }
            }

            if (!no_ba || no_ba.trim() === '') {
                notifyAlert("Peringatan!", "Nomor Berita Acara (BA) / Referensi wajib diisi!", "warning");
                $('#no_ba').focus();
                return false;
            }

            if (!id_material || id_material === '0') {
                notifyAlert("Peringatan!", "Barang stok wajib dipilih!", "warning");
                $('#id_material').focus();
                return false;
            }

            var numQty = qty_oke ? parseFloat(qty_oke.toString().replace(/,/g, '')) : 0;
            if (numQty <= 0) {
                notifyAlert("Peringatan!", "Qty adjustment harus lebih dari 0!", "warning");
                $('#qty_oke').focus();
                return false;
            }

            var doSubmit = function() {
                var btn = $('#simpan');
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

                var formData = new FormData($('#form_proses')[0]);
                var urlTarget = getBaseUrl() + controllerPath + 'add';

                $.ajax({
                    url: urlTarget,
                    type: "POST",
                    data: formData,
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Adjustment');
                        if (data.status == 1) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.pesan,
                                    timer: 3000
                                }).then(function() {
                                    window.location.href = getBaseUrl() + controllerPath;
                                });
                            } else if (typeof swal === 'function') {
                                swal({
                                    title: "Save Success!",
                                    text: data.pesan,
                                    type: "success",
                                    timer: 3000
                                });
                                setTimeout(function() {
                                    window.location.href = getBaseUrl() + controllerPath;
                                }, 1500);
                            } else {
                                alert(data.pesan);
                                window.location.href = getBaseUrl() + controllerPath;
                            }
                        } else {
                            notifyAlert("Simpan Gagal!", data.pesan, "warning");
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Adjustment');
                        notifyAlert("Error!", "Terjadi kesalahan sistem saat memproses transaksi.", "error");
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: "Konfirmasi Adjustment",
                    text: "Apakah Anda yakin ingin memproses penyesuaian stok ini?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Proses!",
                    cancelButtonText: "Batal"
                }).then(function(result) {
                    if (result.isConfirmed) {
                        doSubmit();
                    }
                });
            } else if (typeof swal === 'function') {
                swal({
                    title: "Konfirmasi Adjustment",
                    text: "Apakah Anda yakin ingin memproses penyesuaian stok ini?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Ya, Proses!",
                    cancelButtonText: "Batal",
                    closeOnConfirm: false
                }, function(isConfirm) {
                    if (isConfirm) {
                        doSubmit();
                    }
                });
            } else {
                if (confirm("Apakah Anda yakin ingin memproses penyesuaian stok ini?")) {
                    doSubmit();
                }
            }
        });
    });
</script>
