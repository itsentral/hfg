<?php
$ENABLE_ADD     = has_permission('Incoming_Departemen.Add');
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete="off">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row align-items-center g-3">
                <div class="col-md-7 col-12">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                            <i class="fa fa-file-circle-plus fs-4"></i>
                        </div>
                        <div>
                            <h5 class="card-title fw-bold text-dark mb-0">Tambah Penerimaan Incoming</h5>
                            <small class="text-muted">Pilih supplier dan dokumen PO / Non-PO untuk memproses penerimaan barang</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-12 text-md-end">
                    <a href="<?= base_url('incoming_departemen'); ?>" class="btn btn-sm btn-outline-secondary shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-4">
                <!-- Kolom Kiri: Pemilihan Supplier & Daftar PO -->
                <div class="col-lg-7 col-12">
                    <div class="card border shadow-none bg-light h-100">
                        <div class="card-header bg-white py-2 border-bottom">
                            <h6 class="fw-bold mb-0 text-primary">
                                <i class="fa fa-truck-moving me-1"></i> 1. Pilih Supplier &amp; Dokumen PO
                            </h6>
                        </div>
                        <div class="card-body bg-white">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Supplier <span class="text-danger">*</span></label>
                                <select id="supplier" name="supplier" class="form-select form-select-sm list_supplier w-100">
                                    <option value="">- Pilih Supplier -</option>
                                    <option value="NON-PO">NON-PO</option>
                                    <?php if (!empty($list_supplier)) : ?>
                                        <?php foreach ($list_supplier as $item) : ?>
                                            <option value="<?= $item['kode_supplier']; ?>"><?= $item['nama']; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="table-responsive border rounded bg-white">
                                <table class="table table-sm table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light text-nowrap">
                                        <tr>
                                            <th class="text-center" width="40">No</th>
                                            <th class="text-center" width="80">Tipe</th>
                                            <th class="text-center" width="130">No. PO</th>
                                            <th class="text-center" width="130">No. PR</th>
                                            <th>Keterangan</th>
                                            <th class="text-center" width="60">Pilih</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list_po">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fa fa-info-circle me-1"></i> Silakan pilih supplier di atas untuk memuat daftar PO / Non-PO.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Informasi Transaksi & Tombol Proses -->
                <div class="col-lg-5 col-12">
                    <div class="card border shadow-none bg-light h-100">
                        <div class="card-header bg-white py-2 border-bottom">
                            <h6 class="fw-bold mb-0 text-primary">
                                <i class="fa fa-clipboard-list me-1"></i> 2. Informasi Transaksi Penerimaan
                            </h6>
                        </div>
                        <div class="card-body bg-white">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Transaksi <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="tanggal_trans" id="tanggal_trans" class="form-control form-control-sm flatpickr-date bg-white" placeholder="Pilih tanggal..." value="<?= date('Y-m-d'); ?>" readonly>
                                    <span class="input-group-text bg-white"><i class="fa fa-calendar-alt text-primary"></i></span>
                                </div>
                                <small class="text-muted">Tanggal fisik barang / jasa diterima</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama PIC Penerima <span class="text-danger">*</span></label>
                                <input type="text" id="pic" name="pic" class="form-control form-control-sm" placeholder="Contoh: Budi (Gudang / User)">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Catatan / Note</label>
                                <textarea id="note" name="note" class="form-control form-control-sm" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                            </div>

                            <div class="alert alert-info py-2 small mb-4">
                                <i class="fa fa-circle-info me-1"></i> Centang minimal satu dokumen PO di tabel sebelah kiri, lalu klik tombol di bawah untuk memeriksa dan menginput qty barang yang diterima.
                            </div>

                            <div class="d-grid">
                                <button type="button" class="btn btn-success py-2 shadow-sm fw-bold" id="modalDetail">
                                    <i class="fa fa-arrow-right me-1"></i> Lanjut Proses Barang Incoming
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Detail Rincian Barang (modal_incoming) -->
    <div class="modal fade" id="ModalView2" tabindex="-1" aria-hidden="true" style="overflow-y: auto;">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2">
                    <h5 class="modal-title fw-bold" id="head_title2">Form Penerimaan Incoming</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3" id="view2">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 mb-0">Memuat rincian item...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
    $(document).ready(function() {
        if ($('.list_supplier').length) {
            $('.list_supplier').select2({ width: '100%' });
        }

        // Inisialisasi Flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#tanggal_trans', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd F Y',
                locale: 'id',
                defaultDate: '<?= date("Y-m-d"); ?>',
                allowInput: true
            });
        }
    });

    $(document).on('change', '.list_supplier', function() {
        var id_supplier = $(this).val();
        if (!id_supplier) {
            $('.list_po').html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fa fa-info-circle me-1"></i> Silakan pilih supplier terlebih dahulu.</td></tr>');
            return;
        }

        $('.list_po').html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin me-1"></i> Memuat daftar PO...</td></tr>');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'get_list_po_depart',
            data: {
                'id_supplier': id_supplier
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                if (result.hasil && result.hasil.trim() !== '') {
                    $('.list_po').html(result.hasil);
                } else {
                    $('.list_po').html('<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data PO / Non-PO yang menunggu incoming untuk supplier ini.</td></tr>');
                }
            },
            error: function() {
                $('.list_po').html('<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data PO. Silakan coba lagi.</td></tr>');
            }
        });
    });

    $(document).on('click', '#modalDetail', function(e) {
        e.preventDefault();
        var pic = $('#pic').val();
        var note = $('#note').val();
        var tanggal_trans = $('#tanggal_trans').val();

        var check_po = [];
        $('.check_po:checked').each(function() {
            check_po.push($(this).val());
        });

        if (check_po.length < 1) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ title: 'Perhatian!', text: 'Silakan centang minimal satu PO / Non-PO pada tabel!', icon: 'warning' });
            } else {
                swal({ title: 'Perhatian!', text: 'Silakan centang minimal satu PO / Non-PO pada tabel!', type: 'warning' });
            }
            return false;
        }

        if (!pic || pic.trim() === '') {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ title: 'Perhatian!', text: 'Nama PIC penerima wajib diisi!', icon: 'warning' });
            } else {
                swal({ title: 'Perhatian!', text: 'Nama PIC penerima wajib diisi!', type: 'warning' });
            }
            $('#pic').focus();
            return false;
        }

        $("#head_title2").html("<i class='fa fa-boxes-packing me-1'></i> <b>Rincian Barang Penerimaan Incoming</b>");
        $("#view2").html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2 mb-0">Memuat rincian barang...</p></div>');
        $("#ModalView2").modal('show');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'modal_incoming',
            data: {
                "no_po": check_po,
                "pic": pic,
                "note": note,
                "tanggal_trans": tanggal_trans
            },
            success: function(data) {
                $("#view2").html(data);
            },
            error: function() {
                $("#view2").html('<div class="alert alert-danger text-center">Gagal memuat form incoming. Silakan coba lagi.</div>');
            }
        });
    });

    $(document).on('click', '#saveINMaterial', function() {
        var sts_qty = 1;
        var sts_not_exc = 1;

        $('.qtyDiterima').each(function() {
            var id = $(this).data('id');
            var valStr = $(this).val();
            var maxStr = $('.max_qty_' + id).val();
            var value = parseFloat(valStr.toString().replace(/,/g, '')) || 0;
            var max = parseFloat(maxStr.toString().replace(/,/g, '')) || 0;

            if (max > 0 && (valStr === '' || valStr === null)) {
                sts_qty = 0;
            }
            if (value > max) {
                sts_not_exc = 0;
            }
        });

        if (sts_qty !== 1) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ title: 'Peringatan!', text: 'Pastikan seluruh input Qty Diterima telah terisi!', icon: 'warning' });
            } else {
                swal({ title: 'Peringatan!', text: 'Pastikan seluruh input Qty Diterima telah terisi!', type: 'warning' });
            }
            return false;
        }

        if (sts_not_exc !== 1) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ title: 'Peringatan!', text: 'Qty Diterima tidak boleh melebihi sisa Qty yang belum diterima!', icon: 'warning' });
            } else {
                swal({ title: 'Peringatan!', text: 'Qty Diterima tidak boleh melebihi sisa Qty yang belum diterima!', type: 'warning' });
            }
            return false;
        }

        var doSubmit = function() {
            var formData = new FormData($('#form_adjustment')[0]);
            var btn = $('#saveINMaterial');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: siteurl + active_controller + 'process_incoming',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(data) {
                    btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Penerimaan');
                    if (data.status == 1) {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({ title: "Berhasil!", text: data.pesan, icon: "success", timer: 3000 }).then(function() {
                                window.location.href = siteurl + active_controller;
                            });
                        } else {
                            swal({ title: "Berhasil!", text: data.pesan, type: "success", timer: 3000 }, function() {
                                window.location.href = siteurl + active_controller;
                            });
                        }
                    } else {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({ title: "Gagal!", text: data.pesan, icon: "warning" });
                        } else {
                            swal({ title: "Gagal!", text: data.pesan, type: "warning" });
                        }
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Penerimaan');
                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire({ title: "Error!", text: 'Terjadi kesalahan sistem saat memproses data.', icon: "error" });
                    } else {
                        swal({ title: "Error!", text: 'Terjadi kesalahan sistem saat memproses data.', type: "error" });
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title: "Konfirmasi Simpan",
                text: "Apakah Anda yakin data penerimaan incoming ini sudah benar?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Batal"
            }).then(function(result) {
                if (result.isConfirmed) {
                    doSubmit();
                }
            });
        } else {
            swal({
                title: "Konfirmasi Simpan",
                text: "Apakah Anda yakin data penerimaan incoming ini sudah benar?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    doSubmit();
                }
            });
        }
    });
</script>
