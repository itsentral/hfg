<?php
$ENABLE_ADD     = has_permission('Outgoing_Stok_ATK.Add');
$ENABLE_MANAGE  = has_permission('Outgoing_Stok_ATK.Manage');
$ENABLE_VIEW    = has_permission('Outgoing_Stok_ATK.View');
$ENABLE_DELETE  = has_permission('Outgoing_Stok_ATK.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" style="display: none;" role="alert"></div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="row align-items-center g-3">
            <div class="col-md-7 col-12">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="fa fa-boxes-packing fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title fw-bold text-dark mb-0">Outgoing Stok ATK / Household</h5>
                        <small class="text-muted">Riwayat permintaan dan pengeluaran barang stok ATK &amp; perlengkapan household</small>
                    </div>
                </div>
            </div>
            <div class="col-md-5 col-12 text-md-end">
                <?php if ($ENABLE_ADD) : ?>
                    <a class="btn btn-sm btn-primary shadow-sm px-3" href="<?= base_url('outgoing_stok_atk/request') ?>" title="Tambah Outgoing">
                        <i class="fa fa-plus-circle me-1"></i> Tambah Outgoing
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example2" class="table table-striped table-hover align-middle border mb-0" style="width: 100%;">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th class="text-center" width="40">#</th>
                        <th class="text-center" width="140">No Transaksi</th>
                        <th class="text-center" width="110">Tanggal</th>
                        <th>Departemen</th>
                        <th class="text-center" width="90">Qty Unit</th>
                        <th width="140">PIC</th>
                        <th width="130">Pembuat</th>
                        <th class="text-center" width="140">Waktu Dibuat</th>
                        <th class="text-center no-sort" width="90">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal View Detail -->
<div class="modal fade" id="ModalView2" tabindex="-1" aria-hidden="true" style="overflow-y: auto;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fw-bold" id="head_title2">Detail Request Outgoing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="view2">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Memuat rincian...</p>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>

<script type="text/javascript">
    var datatables;
    $(document).ready(function() {
        DataTables2();
    });

    function DataTables2() {
        if ($.fn.DataTable.isDataTable('#example2')) {
            $('#example2').DataTable().destroy();
        }

        datatables = $('#example2').DataTable({
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
                url: siteurl + active_controller + 'server_side_request_produksi',
                type: "POST",
                cache: false,
                error: function() {
                    $("#example2 tbody").html('<tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data ditemukan di server</td></tr>');
                }
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari no trans / departemen...",
                lengthMenu: "_MENU_ data per halaman",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                emptyTable: "Tidak ada data outgoing stok",
                zeroRecords: "Tidak ada data yang cocok",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
            }
        });
    }

    $(document).on('click', '.detail', function(e) {
        e.preventDefault();
        var kode_trans = $(this).data('kode_trans');
        var tanda = $(this).data('tanda');
        var title = (tanda == 'detail') ? 'DETAIL REQUEST OUTGOING: ' + kode_trans : 'EDIT REQUEST OUTGOING: ' + kode_trans;

        $("#head_title2").html("<i class='fa fa-eye me-1'></i> <b>" + title + "</b>");
        $("#view2").html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2 mb-0">Memuat detail...</p></div>');
        $("#ModalView2").modal('show');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'modal_request_edit/' + kode_trans + '/' + tanda,
            success: function(data) {
                $("#view2").html(data);
            },
            error: function() {
                $("#view2").html('<div class="alert alert-danger text-center">Gagal memuat detail transaksi. Silakan coba kembali.</div>');
            }
        });
    });
</script>
