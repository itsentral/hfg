<?php
$ENABLE_ADD     = has_permission('Incoming_Departemen.Add');
$ENABLE_VIEW    = has_permission('Incoming_Departemen.View');
$ENABLE_MANAGE  = has_permission('Incoming_Departemen.Manage');
$ENABLE_DELETE  = has_permission('Incoming_Departemen.Delete');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .form-label {
        font-size: 0.875rem;
        margin-bottom: 0.35rem;
    }
    .select2-container {
        width: 100% !important;
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
        color: #495057;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .flatpickr-input[readonly] {
        background-color: #fff !important;
        cursor: pointer;
    }
</style>

<div class="row mb-3">
    <div class="col-12">
        <!-- Form Tambah Incoming (Collapsible / Default Tertutup) -->
        <?php if ($ENABLE_ADD) : ?>
        <div class="collapse mb-4" id="collapseFormIncoming">
            <div class="card border border-primary border-opacity-25 shadow-sm">
                <div class="card-header bg-primary bg-opacity-10 py-3 d-flex justify-content-between align-items-center border-bottom border-primary border-opacity-10">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white p-2 rounded-3 shadow-xs">
                            <i class="fa fa-file-circle-plus fs-5"></i>
                        </div>
                        <div>
                            <h6 class="card-title fw-bold text-dark mb-0">Incoming Department Receipt Input Form</h6>
                            <small class="text-muted">Select a supplier and PO / Non-PO document to process the goods receipt</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-toggle="collapse" data-bs-target="#collapseFormIncoming" aria-label="Close" title="Close Form">
                        <i class="fa fa-times me-1"></i> Close
                    </button>
                </div>
                <div class="card-body p-4 bg-white">
                    <form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete="off">
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
                                            <label class="form-label fw-bold text-dark">Supplier <span class="text-danger">*</span></label>
                                            <select id="supplier" name="supplier" class="form-select select2 list_supplier w-100">
                                                <option value="">-- Select Supplier --</option>
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
                                            <label class="form-label fw-bold text-dark">Transaction Date <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" name="tanggal_trans" id="tanggal_trans" class="form-control flatpickr-input bg-white" style="height: 38px;" placeholder="Select date..." value="<?= date('Y-m-d'); ?>">
                                                <span class="input-group-text bg-light text-muted"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            <small class="text-muted">Date the goods / services were physically received</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-dark">Receiving PIC Name <span class="text-danger">*</span></label>
                                            <input type="text" id="pic" name="pic" class="form-control" style="height: 38px;" placeholder="Example: Budi (Warehouse / User)">
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-bold text-dark">Notes</label>
                                            <textarea id="note" name="note" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                                        </div>

                                        <div class="alert alert-info py-2 small mb-4">
                                            <i class="fa fa-circle-info me-1"></i> Centang minimal satu dokumen PO di tabel sebelah kiri, lalu klik tombol di bawah untuk memeriksa dan menginput qty barang yang diterima.
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                            <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="collapse" data-bs-target="#collapseFormIncoming">
                                                Cancel
                                            </button>
                                            <button type="button" class="btn btn-success px-4 py-2 shadow-sm fw-bold" id="modalDetail">
                                                <i class="fa fa-arrow-right me-1"></i> Lanjut Proses Barang Incoming
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Riwayat Penerimaan Departemen (Index Table Utama) -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row align-items-center g-3">
                    <div class="col-md-7 col-12">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="fa fa-boxes-packing fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-0">Incoming Department</h5>
                                <small class="text-muted">Department Non-Material Goods &amp; Services Receipt History (PO &amp; Non-PO)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-12 text-md-end">
                        <?php if ($ENABLE_ADD) : ?>
                            <button type="button" class="btn btn-sm btn-primary shadow-sm px-3" data-bs-toggle="collapse" data-bs-target="#collapseFormIncoming" aria-expanded="false" aria-controls="collapseFormIncoming" id="btnToggleForm">
                                <i class="fa fa-plus-circle me-1" id="iconToggle"></i> <span id="textToggle">Add Incoming</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle border mb-0" id="my-grid" style="width: 100%;">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th class="text-center" width="40">#</th>
                                <th class="text-center" width="140">Transaction No.</th>
                                <th class="text-center" width="120">Tgl Incoming</th>
                                <th class="text-center" width="140">No PO / Non-PO</th>
                                <th class="text-center" width="100">Sum Qty</th>
                                <th class="text-center" width="120">PIC</th>
                                <th class="text-center" width="130">Receiver</th>
                                <th>Supplier</th>
                                <th class="text-center" width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($list_incoming)) :
                                $no = 1;
                                foreach ($list_incoming as $item) :
                                    $supp_name = '-';
                                    if (!empty($item['id_supplier']) && isset($list_arr_supplier[(string) ($item['id_supplier'] ?? '')]['nama'])) {
                                        $supp_name = $list_arr_supplier[(string) ($item['id_supplier'] ?? '')]['nama'];
                                    }

                                    $print = '<a href="' . base_url('incoming_departemen/print_incoming_dept/' . $item['kode_trans']) . '" target="_blank" class="btn btn-sm btn-warning text-white" title="Print Receipt Letter"><i class="fa fa-print"></i></a>';
                                    $view = '<button type="button" class="btn btn-sm btn-info text-white detailAjust" title="Lihat Detail Penerimaan" data-kode_trans="' . $item['kode_trans'] . '"><i class="fa fa-eye"></i></button>';
                            ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td class="text-center fw-semibold text-primary"><?= $item['kode_trans']; ?></td>
                                        <td class="text-center"><?= !empty($item['incoming_date']) ? date('d M Y', strtotime((string) $item['incoming_date'])) : '-'; ?></td>
                                        <td class="text-center"><?= $item['no_po']; ?></td>
                                        <td class="text-center fw-bold"><?= number_format((float) $item['sum_qty']); ?></td>
                                        <td class="text-center"><?= ucfirst((string) $item['pic']); ?></td>
                                        <td class="text-center"><?= $item['receiver']; ?></td>
                                        <td><?= $supp_name; ?></td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-1">
                                                <?= $view; ?>
                                                <?= $print; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal View Detail / Form Rincian Barang Incoming -->
<div class="modal fade" id="ModalView2" tabindex="-1" aria-hidden="true" style="overflow-y: auto;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fw-bold" id="head_title2">Incoming Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="view2">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Loading details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
    var active_controller = 'incoming_departemen/';

    function initFormControls() {
        if ($('.list_supplier').length && $.fn.select2) {
            $('.list_supplier').select2({
                width: '100%',
                dropdownParent: $('#collapseFormIncoming')
            });
        }
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#tanggal_trans', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                defaultDate: '<?= date("Y-m-d"); ?>',
                allowInput: false
            });
        }
    }

    $(document).ready(function() {
        if ($('#my-grid').length) {
            $('#my-grid').DataTable({
                order: [],
                pageLength: 10
            });
        }

        initFormControls();

        // Toggle text, icon & fix dropdown width when collapse opens/closes
        var collapseEl = document.getElementById('collapseFormIncoming');
        if (collapseEl) {
            collapseEl.addEventListener('show.bs.collapse', function () {
                $('#btnToggleForm').removeClass('btn-primary').addClass('btn-secondary');
                $('#iconToggle').removeClass('fa-plus-circle').addClass('fa-chevron-up');
                $('#textToggle').text('Close Form');
            });
            collapseEl.addEventListener('shown.bs.collapse', function () {
                initFormControls();
                collapseEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            collapseEl.addEventListener('hide.bs.collapse', function () {
                $('#btnToggleForm').removeClass('btn-secondary').addClass('btn-primary');
                $('#iconToggle').removeClass('fa-chevron-up').addClass('fa-plus-circle');
                $('#textToggle').text('Add Incoming');
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

    $(document).on('click', '.detailAjust', function(e) {
        e.preventDefault();
        var kode_trans = $(this).data('kode_trans');
        $("#head_title2").html("<i class='fa fa-eye me-1'></i> <b>DETAIL INCOMING: " + kode_trans + "</b>");
        $("#view2").html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2 mb-0">Loading details...</p></div>');
        $("#ModalView2").modal('show');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'modal_detail/' + encodeURIComponent(kode_trans),
            success: function(data) {
                $("#view2").html(data);
            },
            error: function() {
                $("#view2").html('<div class="alert alert-danger text-center">Failed to load incoming detail. Please try again.</div>');
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
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#6c757d",
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
