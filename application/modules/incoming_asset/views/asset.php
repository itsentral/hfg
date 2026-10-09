<?php
$ENABLE_ADD     = has_permission('Incoming_Asset.Add');
$ENABLE_VIEW    = has_permission('Incoming_Asset.View');
$ENABLE_MANAGE  = has_permission('Incoming_Asset.Manage');
$ENABLE_DELETE  = has_permission('Incoming_Asset.Delete');
?>

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
    .chosen-container {
        width: 100% !important;
    }
    .chosen-container-single .chosen-single {
        height: 38px !important;
        line-height: 36px !important;
        border-radius: 0.375rem !important;
        border: 1px solid #ced4da !important;
        background: #fff !important;
        box-shadow: none !important;
        padding-left: 12px !important;
    }
    .chosen-container-single .chosen-single div {
        top: 6px !important;
    }
    .flatpickr-input[readonly] {
        background-color: #fff !important;
        cursor: pointer;
    }
</style>

<div class="row mb-3">
    <div class="col-12">
        <!-- Form Penerimaan Asset (Collapsible / Default Closed) -->
        <div class="collapse mb-4" id="collapseFormIncoming">
            <div class="card border border-primary border-opacity-25 shadow-sm">
                <div class="card-header bg-primary bg-opacity-10 py-3 d-flex justify-content-between align-items-center border-bottom border-primary border-opacity-10">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white p-2 rounded-3 shadow-xs">
                            <i class="fa fa-plus-circle fs-5"></i>
                        </div>
                        <div>
                            <h6 class="card-title fw-bold text-dark mb-0">Form Input Penerimaan Asset</h6>
                            <small class="text-muted">Input penerimaan barang asset dari PO atau Non-PO</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-toggle="collapse" data-bs-target="#collapseFormIncoming" aria-label="Close" title="Tutup Form">
                        <i class="fa fa-times me-1"></i> Tutup
                    </button>
                </div>
                <div class="card-body p-4 bg-white">
                    <form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" id="tandax" name="tandax">
                        <div class="row g-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-dark">Nomor PO / NON-PO <span class="text-danger">*</span></label>
                                <select id="no_ipp" name="no_ipp" class="form-select select2 chosen-select" style="width: 100% !important;">
                                    <option value="0">Pilih Nomor PO / NON-PO</option>
                                    <?php
                                    if (!empty($no_po)) {
                                        foreach ($no_po as $val => $valx) {
                                            $nm_supplier = (!empty($valx['nm_supplier'])) ? ' - ' . $valx['nm_supplier'] : '';
                                            echo "<option value='" . $valx['no_po'] . "'>" . strtoupper($valx['ket'] . ' - ' . $valx['no_po'] . $nm_supplier) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-dark">PIC Penerima <span class="text-danger">*</span></label>
                                <input type="text" id="pic" name="pic" class="form-control" style="height: 38px;" placeholder="Nama PIC Penerima">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-dark">Department <span class="text-danger">*</span></label>
                                <select name="id_dept" id="id_dept" class="form-select select2 chosen-select" style="width: 100% !important;">
                                    <option value="0">Pilih Department</option>
                                    <?php
                                    if (!empty($list_dept)) {
                                        foreach ($list_dept as $item_dept) {
                                            echo "<option value='" . $item_dept['id'] . "'>" . strtoupper($item_dept['nm_dept']) . "</option>";
                                        }
                                    } elseif (function_exists('get_list_dept')) {
                                        foreach (get_list_dept() as $val => $valx) {
                                            echo "<option value='" . $valx['id'] . "'>" . strtoupper($valx['nm_dept']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-dark">Cost Center</label>
                                <select name="id_costcenter" id="id_costcenter" class="form-select select2 chosen-select" style="width: 100% !important;">
                                    <option value="0">Pilih Cost Center</option>
                                    <?php
                                    if (!empty($list_costcenter)) {
                                        foreach ($list_costcenter as $item_cc) {
                                            echo "<option value='" . $item_cc['id_costcenter'] . "'>" . strtoupper($item_cc['nm_costcenter']) . "</option>";
                                        }
                                    } elseif (function_exists('get_list_costcenter')) {
                                        foreach (get_list_costcenter() as $val => $valx) {
                                            echo "<option value='" . $valx['id_costcenter'] . "'>" . strtoupper($valx['nm_costcenter']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-dark">Tanggal Transaksi</label>
                                <div class="input-group">
                                    <input type="text" name="tanggal_trans" id="tanggal_trans" class="form-control flatpickr-input" style="height: 38px;" placeholder="Pilih Tanggal Transaksi" value="<?= date('Y-m-d'); ?>">
                                    <span class="input-group-text bg-light text-muted"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-dark">Catatan / Note</label>
                                <textarea id="note" name="note" class="form-control" rows="2" placeholder="Catatan transaksi..."></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-toggle="collapse" data-bs-target="#collapseFormIncoming">
                                Batal
                            </button>
                            <button type="button" class="btn btn-primary px-4 shadow-sm" id="modalDetail">
                                <i class="fa fa-arrow-right me-1"></i> Proses Penerimaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card Riwayat Penerimaan Asset (Index Table Utama) -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row align-items-center g-3">
                    <div class="col-md-5 col-12">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="fa fa-cubes fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-0">Incoming Asset</h5>
                                <small class="text-muted">Riwayat Penerimaan Barang &amp; Asset (PO &amp; Non-PO)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7 col-12">
                        <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2">
                            <div style="min-width: 170px;">
                                <select id="filter_no_po" name="filter_no_po" class="form-select form-select-sm">
                                    <option value="0">Semua Nomor PO</option>
                                    <?php
                                    if (!empty($list_po)) {
                                        foreach ($list_po as $val => $valx) {
                                            echo "<option value='" . $valx['no_ipp'] . "'>" . strtoupper($valx['no_ipp']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div style="min-width: 160px;">
                                <select id="filter_gudang" name="filter_gudang" class="form-select form-select-sm">
                                    <option value="0">Semua Dept</option>
                                    <?php
                                    if (!empty($data_gudang)) {
                                        foreach ($data_gudang as $val => $valx) {
                                            $dept_lbl = !empty($valx['kd_gudang_ke']) ? $valx['kd_gudang_ke'] : $valx['id_gudang_ke'];
                                            echo "<option value='" . $valx['id_gudang_ke'] . "'>" . strtoupper($dept_lbl) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php if ($ENABLE_ADD) : ?>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm px-3" data-bs-toggle="collapse" data-bs-target="#collapseFormIncoming" aria-expanded="false" aria-controls="collapseFormIncoming" id="btnToggleForm">
                                    <i class="fa fa-plus-circle me-1" id="iconToggle"></i> <span id="textToggle">Tambah Incoming</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle border mb-0" id="my-grid" style="width: 100%;">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th class="text-center" width="40">#</th>
                                <th class="text-center" width="130">No Trans</th>
                                <th class="text-center" width="110">Incoming Date</th>
                                <th class="text-center" width="140">No PO/Non-PO</th>
                                <th>Department</th>
                                <th>PIC</th>
                                <th>Note</th>
                                <th>Receiver</th>
                                <th class="text-center" width="140">Created Date</th>
                                <th class="text-center" width="80">Doc</th>
                                <th class="text-center" width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal View -->
<div class="modal fade" id="ModalView2" tabindex="-1" aria-labelledby="head_title2" aria-hidden="true" style="overflow-y: auto;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="head_title2">DETAIL INCOMING ASSET</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="view2">
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
    var active_controller = 'incoming_asset';
    var fpTanggal = null;

    function initFlatpickr() {
        if (typeof flatpickr !== 'undefined') {
            fpTanggal = flatpickr('#tanggal_trans', {
                locale: 'id',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                defaultDate: '<?= date('Y-m-d'); ?>',
                allowInput: false
            });
        }
    }

    function initFormSelects() {
        if ($.fn.select2) {
            $('#collapseFormIncoming .select2').select2({
                width: '100%',
                dropdownParent: $('#collapseFormIncoming')
            });
        }
        if ($.fn.chosen) {
            $('#collapseFormIncoming .chosen-select').chosen({
                width: '100%'
            }).trigger('chosen:updated');
        }
    }

    function showModalDialog(title, content) {
        $("#head_title2").html(title);
        $("#view2").html(content);
        var modalEl = document.getElementById('ModalView2');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalObj.show();
        } else {
            $("#ModalView2").modal('show');
        }
    }

    $(document).ready(function() {
        initFlatpickr();

        if ($.fn.maskMoney) {
            $('.maskM').maskMoney();
        }
        var no_po = $('#filter_no_po').val();
        var gudang = $('#filter_gudang').val();
        loadDataTables(no_po, gudang);

        $(document).on('change', '#filter_gudang, #filter_no_po', function(e) {
            e.preventDefault();
            var no_po = $('#filter_no_po').val();
            var gudang = $('#filter_gudang').val();
            loadDataTables(no_po, gudang);
        });

        // Toggle text, icon & fix dropdown width when collapse opens/closes
        var collapseEl = document.getElementById('collapseFormIncoming');
        if (collapseEl) {
            collapseEl.addEventListener('show.bs.collapse', function () {
                $('#btnToggleForm').removeClass('btn-primary').addClass('btn-secondary');
                $('#iconToggle').removeClass('fa-plus-circle').addClass('fa-chevron-up');
                $('#textToggle').text('Tutup Form');
            });
            collapseEl.addEventListener('shown.bs.collapse', function () {
                initFormSelects();
                collapseEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            collapseEl.addEventListener('hide.bs.collapse', function () {
                $('#btnToggleForm').removeClass('btn-secondary').addClass('btn-primary');
                $('#iconToggle').removeClass('fa-chevron-up').addClass('fa-plus-circle');
                $('#textToggle').text('Tambah Incoming');
            });
        }
    });

    $(document).on('click', '.detailAjust', function(e) {
        e.preventDefault();
        var kode_trans = $(this).data('kode_trans');
        if (typeof loading_spinner === 'function') {
            loading_spinner();
        }
        $.ajax({
            type: 'POST',
            url: base_url + active_controller + '/modal_detail/' + encodeURIComponent(kode_trans),
            success: function(data) {
                showModalDialog("<b>DETAIL INCOMING ASSET</b>", data);
            },
            error: function() {
                if (typeof swal === 'function') {
                    swal({
                        title: "Error Message !",
                        text: 'Connection Timed Out ...',
                        type: "warning",
                        timer: 5000
                    });
                } else {
                    alert('Connection Timed Out');
                }
            }
        });
    });

    $(document).on('click', '#modalDetail', function(e) {
        e.preventDefault();
        var no_ipp = $('#no_ipp').val();
        var pic = $('#pic').val();
        var note = $('#note').val();
        var id_dept = $('#id_dept').val();
        var id_costcenter = $('#id_costcenter').val();
        var tanggal_trans = $('#tanggal_trans').val();

        if (no_ipp == '0' || no_ipp == '') {
            if (typeof swal === 'function') {
                swal({
                    title: "Peringatan!",
                    text: 'Silahkan pilih Nomor PO / NON-PO terlebih dahulu.',
                    type: "warning"
                });
            } else {
                alert('Silahkan pilih Nomor PO / NON-PO terlebih dahulu.');
            }
            return false;
        }

        if (pic.trim() == '') {
            if (typeof swal === 'function') {
                swal({
                    title: "Peringatan!",
                    text: 'PIC Penerima belum diisi.',
                    type: "warning"
                });
            } else {
                alert('PIC Penerima belum diisi.');
            }
            return false;
        }

        if (typeof loading_spinner === 'function') {
            loading_spinner();
        }

        $.ajax({
            type: 'POST',
            url: base_url + active_controller + '/modal_incoming_asset',
            data: {
                "id_dept": id_dept,
                "id_costcenter": id_costcenter,
                "no_po": no_ipp,
                "pic": pic,
                "note": note,
                "tanggal_trans": tanggal_trans
            },
            success: function(data) {
                showModalDialog("<b>PENERIMAAN BARANG ASSET</b>", data);
            },
            error: function() {
                if (typeof swal === 'function') {
                    swal({
                        title: "Error Message !",
                        text: 'Connection Timed Out ...',
                        type: "warning",
                        timer: 5000
                    });
                } else {
                    alert('Connection Timed Out');
                }
            }
        });
    });

    $(document).on('click', '#saveINMaterial', function(e) {
        e.preventDefault();
        var doSubmit = function() {
            if (typeof loading_spinner === 'function') {
                loading_spinner();
            }
            var formData = new FormData($('#form_adjustment')[0]);
            $.ajax({
                url: base_url + active_controller + '/process_incoming_asset',
                type: "POST",
                data: formData,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.status == 1) {
                        if (typeof swal === 'function') {
                            swal({
                                title: "Save Success!",
                                text: data.pesan,
                                type: "success",
                                timer: 3000
                            }, function() {
                                window.location.href = base_url + active_controller;
                            });
                        } else {
                            alert(data.pesan);
                            window.location.href = base_url + active_controller;
                        }
                    } else {
                        if (typeof swal === 'function') {
                            swal({
                                title: "Save Failed!",
                                text: data.pesan,
                                type: "warning",
                                timer: 5000
                            });
                        } else {
                            alert(data.pesan);
                        }
                    }
                },
                error: function() {
                    if (typeof swal === 'function') {
                        swal({
                            title: "Error Message !",
                            text: 'Terjadi kesalahan sistem saat menyimpan data.',
                            type: "warning",
                            timer: 5000
                        });
                    } else {
                        alert('Terjadi kesalahan sistem.');
                    }
                }
            });
        };

        if (typeof swal === 'function') {
            swal({
                title: "Apakah Anda Yakin?",
                text: "Data penerimaan asset akan diproses dan stok akan disesuaikan!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Ya, Proses!",
                cancelButtonText: "Batal",
                closeOnConfirm: false,
                closeOnCancel: true
            }, function(isConfirm) {
                if (isConfirm) {
                    doSubmit();
                }
            });
        } else {
            if (confirm("Apakah Anda yakin ingin memproses penerimaan asset ini?")) {
                doSubmit();
            }
        }
    });

    function loadDataTables(no_po = null, gudang = null) {
        $('#my-grid').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "bAutoWidth": false,
            "destroy": true,
            "responsive": true,
            "order": [[1, "desc"]],
            "columnDefs": [
                {
                    "targets": [0, 9, 10],
                    "orderable": false
                }
            ],
            "sPaginationType": "simple_numbers",
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100],
                [10, 20, 50, 100]
            ],
            "ajax": {
                url: base_url + active_controller + '/server_side_incoming_asset',
                type: "post",
                data: function(d) {
                    d.no_po = no_po;
                    d.gudang = gudang;
                },
                cache: false,
                error: function() {
                    $("#my-grid tbody").html('<tr><td colspan="11" class="text-center text-muted py-3">Tidak ada data ditemukan</td></tr>');
                    $("#my-grid_processing").css("display", "none");
                }
            }
        });
    }
</script>
