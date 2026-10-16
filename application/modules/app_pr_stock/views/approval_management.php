<?php
$ENABLE_ADD     = has_permission('Approval_PR_Stock_Management.Add');
$ENABLE_MANAGE  = has_permission('Approval_PR_Stock_Management.Manage');
$ENABLE_VIEW    = has_permission('Approval_PR_Stock_Management.View');
$ENABLE_DELETE  = has_permission('Approval_PR_Stock_Management.Delete');
?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-check-circle me-2"></i>Approval PR Stock - Management
            </h5>
            <small class="text-muted">Daftar pengajuan PR stok material untuk persetujuan level Management</small>
        </div>
        <div>
            <span class="badge bg-primary px-3 py-2 rounded-pill">
                <i class="fa fa-clock-o me-1"></i>Waiting Approval
            </span>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Filter Bar -->
        <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
            <div class="col-md-4 col-lg-3">
                <label for="product" class="form-label fw-semibold text-muted small">Product Type</label>
                <select name="product" id="product" class="form-select select2">
                    <option value="0">Semua Product Type</option>
                    <?php
                    foreach (get_list_inventory_lv1('product') as $val => $valx) {
                        echo "<option value='" . htmlspecialchars($valx['code_lv1']) . "'>" . strtoupper(htmlspecialchars($valx['nama'])) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4 col-lg-3" style="display: none;">
                <label for="costcenter" class="form-label fw-semibold text-muted small">Costcenter</label>
                <select name="costcenter" id="costcenter" class="form-select select2">
                    <option value="0">Semua Costcenter</option>
                    <?php
                    foreach (get_costcenter() as $val => $valx) {
                        echo "<option value='" . htmlspecialchars($valx['id_costcenter']) . "'>" . strtoupper(htmlspecialchars($valx['nama_costcenter'])) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- DataTable -->
        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-light text-center">
                    <tr>
                        <th width="4%" class="text-center">#</th>
                        <th width="18%">Asal Permintaan</th>
                        <th width="12%" class="text-center">No. Req / No SO</th>
                        <th width="12%" class="text-center">No. PR</th>
                        <th>Untuk Kebutuhan</th>
                        <th width="12%">Request By</th>
                        <th width="10%" class="text-center">Request Date</th>
                        <th width="11%" class="text-center">Status</th>
                        <th width="8%" class="text-center no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="dialogPopupLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="dialogPopupLabel"><i class="fa fa-info-circle me-2"></i>Detail PR Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="ModalView">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables & Select2 & SweetAlert2 -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        var product = $("#product").val();
        var costcenter = $("#costcenter").val();
        DataTables(costcenter, product);

        $(document).on('change', '#costcenter', function() {
            var costcenter = $("#costcenter").val();
            var product = $("#product").val();
            DataTables(costcenter, product);
        });

        $(document).on('change', '#product', function() {
            var costcenter = $("#costcenter").val();
            var product = $("#product").val();
            DataTables(costcenter, product);
        });

        // Detail Modal Click
        $(document).on('click', '.detail', function() {
            var so_number = $(this).data('so_number');
            $("#dialogPopupLabel").html('<i class="fa fa-info-circle me-2"></i>Detail PR Stock - ' + so_number);
            $("#ModalView").html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

            var modalEl = document.getElementById('dialog-popup');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalObj.show();
            } else {
                $('#dialog-popup').modal('show');
            }

            $.ajax({
                type: 'POST',
                url: base_url + active_controller + 'detail',
                data: {
                    'so_number': so_number,
                },
                success: function(data) {
                    $("#ModalView").html(data);
                },
                error: function() {
                    $("#ModalView").html('<div class="alert alert-danger mb-0">Gagal memuat detail data. Silakan coba lagi.</div>');
                }
            });
        });

        // Process Booking
        $(document).on('click', '.booking', function(e) {
            e.preventDefault();
            var so_number = $(this).data('so_number');

            Swal.fire({
                title: "Anda Yakin?",
                text: "Process Booking Material & PR untuk " + so_number + "!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#0d6efd",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-check me-1'></i> Ya, Proses!",
                cancelButtonText: "Batal"
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Memproses...",
                        text: "Sedang memproses booking material",
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    $.ajax({
                        type: 'POST',
                        url: base_url + active_controller + 'process_booking',
                        dataType: "json",
                        data: {
                            'so_number': so_number
                        },
                        success: function(res) {
                            if (res.status == '1') {
                                Swal.fire({
                                    title: "Sukses!",
                                    text: res.pesan,
                                    icon: "success"
                                }).then(function() {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: "Gagal!",
                                    text: res.pesan || "Terjadi kesalahan saat proses booking.",
                                    icon: "error"
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: "Error!",
                                text: "Terjadi kesalahan sistem saat menghubungi server.",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });
    });

    function DataTables(costcenter = null, product = null) {
        $('#example1').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            fixedHeader: true,
            autoWidth: false,
            destroy: true,
            searching: true,
            responsive: true,
            order: [[1, "desc"]],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            iDisplayLength: 10,
            aLengthMenu: [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            ajax: {
                url: siteurl + active_controller + 'data_side_approval_pr_material',
                type: "POST",
                data: function(d) {
                    d.tanda = 'management';
                },
                cache: false,
                error: function() {
                    $(".my-grid-error").html("");
                    $("#example1 tbody").html('<tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data ditemukan</td></tr>');
                }
            }
        });
    }
</script>
