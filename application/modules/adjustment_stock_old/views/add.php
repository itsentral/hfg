<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .card {
        border-radius: 0.75rem;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-9 col-12">
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
                                    <small class="text-muted">Masukkan data penyesuaian kuantitas fisik stok barang di gudang</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 col-12 text-md-end">
                            <a href="<?= base_url('adjustment_stock') ?>" class="btn btn-outline-secondary shadow-sm">
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
                            <select name="adjustment_type" id="adjustment_type" class="form-select chosen-select" required>
                                <option value="0">-- Pilih Tipe Adjustment --</option>
                                <option value="plus">PLUS (Penambahan Stok)</option>
                                <option value="minus">MINUS (Pengurangan Stok)</option>
                            </select>
                        </div>

                        <!-- Gudang -->
                        <div class="col-md-6 col-12 gudang_plus_min">
                            <label class="form-label fw-bold text-dark">
                                Gudang Stok <span class="text-danger">*</span>
                            </label>
                            <select name="id_gudang_ke" id="id_gudang_ke" class="form-select chosen-select" required>
                                <option value="0">-- Pilih Gudang --</option>
                                <?php foreach ($gudang as $val) : ?>
                                    <option value="<?= $val['id']; ?>">
                                        <?= strtoupper($val['nm_gudang']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Nama PIC -->
                        <div class="col-md-6 col-12 gudang_plus_min">
                            <label class="form-label fw-bold text-dark">
                                PIC Petugas / Pemohon <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa fa-user"></i></span>
                                <input type="text" name="pic" id="pic" class="form-control" placeholder="Nama PIC..." required>
                            </div>
                        </div>

                        <!-- Nomor Berita Acara (BA) -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-dark">
                                No Berita Acara (BA) / Referensi <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa fa-file-lines"></i></span>
                                <input type="text" name="no_ba" id="no_ba" class="form-control" placeholder="Contoh: BA/STOK/2026/001" required>
                            </div>
                        </div>

                        <!-- Pilihan Barang Stok -->
                        <div class="col-md-8 col-12">
                            <label class="form-label fw-bold text-dark">
                                Nama Barang Stok <span class="text-danger">*</span>
                            </label>
                            <select name="id_material" id="id_material" class="form-select chosen-select" required>
                                <option value="0">-- Pilih Barang Stok --</option>
                            </select>
                        </div>

                        <!-- Quantity Adjustment -->
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold text-dark">
                                Qty Adjustment <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa fa-calculator"></i></span>
                                <input type="text" name="qty_oke" id="qty_oke" class="form-control text-center autoNumeric2" placeholder="0.00" required>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark">Keterangan / Alasan Penyesuaian</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Masukkan alasan adjustment stok (contoh: Hasil stock opname fisik gudang, barang rusak, dsb)..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top py-3 text-end">
                    <a href="<?= base_url('adjustment_stock') ?>" class="btn btn-outline-secondary me-2">
                        Batal
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
        if ($('.chosen-select').length) {
            $('.chosen-select').select2({ width: '100%' });
        }

        // Safe AutoNumeric init
        if (typeof $.fn.autoNumeric === 'function') {
            $(".autoNumeric2").autoNumeric('init', {
                mDec: '2',
                aPad: false
            });
        }

        // Fallback filter numeric jika plugin belum siap
        $(document).on('input', '.autoNumeric2', function() {
            if (typeof $.fn.autoNumeric !== 'function') {
                this.value = this.value.replace(/[^0-9.]/g, '');
            }
        });

        // Load list material otomatis
        function loadMaterialList() {
            $.ajax({
                url: siteurl + active_controller + 'list_material',
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
                    console.log('Error loading materials');
                }
            });
        }
        loadMaterialList();

        $(document).on('change', '#adjustment_type', function(e) {
            var type = $(this).val();
            loadMaterialList();
        });

        // Submit form handler
        $(document).on('click', '#simpan', function(e) {
            e.preventDefault();

            var adjustment_type = $('#adjustment_type').val();
            var id_gudang_ke = $('#id_gudang_ke').val();
            var pic = $('#pic').val();
            var id_material = $('#id_material').val();
            var qty_oke = $('#qty_oke').val();
            var no_ba = $('#no_ba').val();

            if (!adjustment_type || adjustment_type == '0') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Tipe adjustment belum dipilih!' });
                } else {
                    alert('Tipe adjustment belum dipilih!');
                }
                $('#adjustment_type').focus();
                return false;
            }

            if (!id_gudang_ke || id_gudang_ke == '0') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Gudang stok belum dipilih!' });
                } else {
                    alert('Gudang stok belum dipilih!');
                }
                $('#id_gudang_ke').focus();
                return false;
            }

            if (!pic || pic.trim() === '') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Nama PIC petugas wajib diisi!' });
                } else {
                    alert('Nama PIC petugas wajib diisi!');
                }
                $('#pic').focus();
                return false;
            }

            if (!id_material || id_material == '0') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Barang stok belum dipilih!' });
                } else {
                    alert('Barang stok belum dipilih!');
                }
                $('#id_material').focus();
                return false;
            }

            var numQty = qty_oke ? parseFloat(qty_oke.toString().replace(/,/g, '')) : 0;
            if (numQty <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Qty adjustment harus lebih dari 0!' });
                } else {
                    alert('Qty adjustment harus lebih dari 0!');
                }
                $('#qty_oke').focus();
                return false;
            }

            if (!no_ba || no_ba.trim() === '') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Nomor Berita Acara (BA) / Referensi wajib diisi!' });
                } else {
                    alert('Nomor Berita Acara (BA) / Referensi wajib diisi!');
                }
                $('#no_ba').focus();
                return false;
            }

            var doSubmit = function() {
                var btn = $('#simpan');
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Memproses...');

                var formData = new FormData($('#form_proses')[0]);
                var baseurl = siteurl + active_controller + 'add';

                $.ajax({
                    url: baseurl,
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
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.pesan, timer: 3000 }).then(() => {
                                    window.location.href = siteurl + active_controller;
                                });
                            } else {
                                alert(data.pesan);
                                window.location.href = siteurl + active_controller;
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'warning', title: 'Gagal!', text: data.pesan });
                            } else {
                                alert(data.pesan);
                            }
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Adjustment');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan sistem saat memproses transaksi.' });
                        } else {
                            alert('Terjadi kesalahan sistem saat memproses transaksi.');
                        }
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
                }).then((result) => {
                    if (result.isConfirmed) {
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
