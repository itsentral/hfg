<?php
$ENABLE_ADD     = has_permission('Outgoing_Stok_ATK.Add');
$ENABLE_MANAGE  = has_permission('Outgoing_Stok_ATK.Manage');
$ENABLE_VIEW    = has_permission('Outgoing_Stok_ATK.View');
$ENABLE_DELETE  = has_permission('Outgoing_Stok_ATK.Delete');
?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row align-items-center g-3">
                    <div class="col-md-7 col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="fa fa-boxes-packing fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-0">Outgoing Stok ATK & Household</h5>
                                <small class="text-muted">Kelola riwayat dan transaksi pengeluaran stok ATK / kebutuhan rumah tangga kantor</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-12 text-md-end">
                        <?php if ($ENABLE_ADD) : ?>
                            <a class="btn btn-primary shadow-sm" href="<?= base_url('outgoing_stok_atk/request') ?>">
                                <i class="fa fa-plus me-1"></i> Tambah Outgoing
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="example2" class="table table-hover table-striped align-middle border rounded" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="4%">#</th>
                                <th class="text-center" width="13%">No Transaksi</th>
                                <th class="text-center" width="10%">Tanggal</th>
                                <th class="text-start">Departemen</th>
                                <th class="text-center" width="9%">Qty Unit</th>
                                <th class="text-start" width="12%">PIC Request</th>
                                <th class="text-start" width="11%">Dibuat Oleh</th>
                                <th class="text-center" width="12%">Waktu Input</th>
                                <th class="text-center no-sort" width="9%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal View Detail -->
<div class="modal fade" id="ModalView2" tabindex="-1" aria-labelledby="head_title2" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="head_title2">
                    <i class="fa fa-file-lines text-primary"></i> Detail Transaksi Outgoing
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="view2">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Memuat data transaksi...</p>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-2">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        DataTables2();

        // Handler tombol Detail Modal
        $(document).on('click', '.detail', function(e) {
            e.preventDefault();
            var kode_trans = $(this).data('kode_trans');
            var tanda = $(this).data('tanda');
            var title = (tanda == 'detail') ? 'DETAIL TRANSAKSI OUTGOING' : 'EDIT TRANSAKSI OUTGOING';

            $("#head_title2").html('<i class="fa fa-boxes-packing text-primary me-2"></i><b>' + title + '</b>: <span class="text-primary">' + kode_trans + '</span>');
            $("#view2").html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2">Memuat data transaksi...</p></div>');

            var modalEl = document.getElementById('ModalView2');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalObj.show();
            } else {
                $('#ModalView2').modal('show');
            }

            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'modal_request_edit/' + encodeURIComponent(kode_trans) + '/' + tanda,
                success: function(data) {
                    $("#view2").html(data);
                },
                error: function() {
                    $("#view2").html('<div class="alert alert-danger mb-0"><i class="fa fa-exclamation-triangle me-1"></i> Gagal mengambil data detail transaksi dari server.</div>');
                }
            });
        });
    });

    function DataTables2() {
        if ($.fn.DataTable.isDataTable('#example2')) {
            $('#example2').DataTable().destroy();
        }

        $('#example2').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            autoWidth: false,
            responsive: true,
            order: [[1, "desc"]],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            ajax: {
                url: siteurl + active_controller + 'data_side_request_material',
                type: "POST",
                cache: false,
                error: function() {
                    $("#example2 tbody").html('<tr><td colspan="9" class="text-center text-muted py-4"><i class="fa fa-info-circle me-1"></i> Tidak ada data outgoing ditemukan</td></tr>');
                }
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari nomor transaksi / departemen...",
                lengthMenu: "_MENU_ data per halaman",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                emptyTable: "Belum ada transaksi outgoing",
                zeroRecords: "Tidak ada data yang cocok",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
            }
        });
    }
</script>
