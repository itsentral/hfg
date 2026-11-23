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
                            <h5 class="card-title fw-bold text-dark mb-0">Add Incoming Receipt</h5>
                            <small class="text-muted">Select a supplier and PO / Non-PO document to process the goods receipt</small>
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
                                    <option value="">- Select Supplier -</option>
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
                                            <th>Description</th>
                                            <th class="text-center" width="60">Select</th>
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
                                <label class="form-label fw-bold">Transaction Date <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="tanggal_trans" id="tanggal_trans" class="form-control form-control-sm flatpickr-date bg-white" placeholder="Select date..." value="<?= date('Y-m-d'); ?>" readonly>
                                    <span class="input-group-text bg-white"><i class="fa fa-calendar-alt text-primary"></i></span>
                                </div>
                                <small class="text-muted">Date the goods / services were physically received</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Receiving PIC Name <span class="text-danger">*</span></label>
                                <input type="text" id="pic" name="pic" class="form-control form-control-sm" placeholder="Example: Budi (Warehouse / User)">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Notes</label>
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
                    <h5 class="modal-title fw-bold" id="head_title2">Incoming Receipt Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3" id="view2">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 mb-0">Loading item details...</p>
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
            $('.list_po').html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fa fa-info-circle me-1"></i> Please select a supplier first.</td></tr>');
            return;
        }

        $('.list_po').html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin me-1"></i> Loading PO list...</td></tr>');

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
                    $('.list_po').html('<tr><td colspan="6" class="text-center text-muted py-4">There is no PO / Non-PO waiting for incoming for this supplier.</td></tr>');
                }
            },
            error: function() {
                $('.list_po').html('<tr><td colspan="6" class="text-center text-danger py-4">Failed to load PO data. Please try again.</td></tr>');
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
                Swal.fire({ title: 'Attention!', text: 'Please check at least one PO / Non-PO in the table!', icon: 'warning' });
            } else {
                swal({ title: 'Attention!', text: 'Please check at least one PO / Non-PO in the table!', type: 'warning' });
            }
            return false;
        }

        if (!pic || pic.trim() === '') {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ title: 'Attention!', text: 'Receiving PIC name is required!', icon: 'warning' });
            } else {
                swal({ title: 'Attention!', text: 'Receiving PIC name is required!', type: 'warning' });
            }
            $('#pic').focus();
            return false;
        }

        $("#head_title2").html("<i class='fa fa-boxes-packing me-1'></i> <b>Incoming Receipt Item Details</b>");
        $("#view2").html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2 mb-0">Loading item details...</p></div>');
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
                $("#view2").html('<div class="alert alert-danger text-center">Failed to load the incoming form. Please try again.</div>');
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
                Swal.fire({ title: 'Warning!', text: 'Make sure all Received Qty inputs are filled in!', icon: 'warning' });
            } else {
                swal({ title: 'Warning!', text: 'Make sure all Received Qty inputs are filled in!', type: 'warning' });
            }
            return false;
        }

        if (sts_not_exc !== 1) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ title: 'Warning!', text: 'Received Qty cannot exceed the remaining unreceived Qty!', icon: 'warning' });
            } else {
                swal({ title: 'Warning!', text: 'Received Qty cannot exceed the remaining unreceived Qty!', type: 'warning' });
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
                    btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Receipt');
                    if (data.status == 1) {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({ title: "Success!", text: data.pesan, icon: "success", timer: 3000 }).then(function() {
                                window.location.href = siteurl + active_controller;
                            });
                        } else {
                            swal({ title: "Success!", text: data.pesan, type: "success", timer: 3000 }, function() {
                                window.location.href = siteurl + active_controller;
                            });
                        }
                    } else {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({ title: "Failed!", text: data.pesan, icon: "warning" });
                        } else {
                            swal({ title: "Failed!", text: data.pesan, type: "warning" });
                        }
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Receipt');
                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire({ title: "Error!", text: 'A system error occurred while processing the data.', icon: "error" });
                    } else {
                        swal({ title: "Error!", text: 'A system error occurred while processing the data.', type: "error" });
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title: "Confirm Save",
                text: "Are you sure this incoming receipt data is correct?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, Save!",
                cancelButtonText: "Cancel"
            }).then(function(result) {
                if (result.isConfirmed) {
                    doSubmit();
                }
            });
        } else {
            swal({
                title: "Confirm Save",
                text: "Are you sure this incoming receipt data is correct?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Save!",
                cancelButtonText: "Cancel",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    doSubmit();
                }
            });
        }
    });
</script>
