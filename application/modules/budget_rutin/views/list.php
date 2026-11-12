<?php
$ENABLE_ADD     = has_permission('Budget_Rutin.Add');
$ENABLE_MANAGE  = has_permission('Budget_Rutin.Manage');
$ENABLE_VIEW    = has_permission('Budget_Rutin.View');
$ENABLE_DELETE  = has_permission('Budget_Rutin.Delete');
?>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<!-- Main Card List -->
<div class="card border-0 shadow-sm mb-4" id="card-list">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="row align-items-center g-3">
            <div class="col-md-7 col-12">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="fa fa-boxes-stacked fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title fw-bold text-dark mb-0">Budget Stock (Rutin)</h5>
                        <small class="text-muted">Kelola data perencanaan budget stock rutin per warehouse / gudang</small>
                    </div>
                </div>
            </div>
            <div class="col-md-5 col-12 text-md-end">
                <div class="d-inline-flex align-items-center gap-2">
                    <?php if ($ENABLE_VIEW) : ?>
                        <a class="btn btn-sm btn-outline-primary shadow-sm" href="<?= base_url('budget_rutin/kompilasi') ?>" title="Kompilasi Budget">
                            <i class="fa fa-layer-group me-1"></i> Kompilasi Budget
                        </a>
                    <?php endif; ?>
                    <?php if ($ENABLE_ADD) : ?>
                        <button type="button" class="btn btn-sm btn-success shadow-sm" onclick="add_data()">
                            <i class="fa fa-plus-circle me-1"></i> Tambah Budget
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example1" class="table table-striped table-hover align-middle border mb-0" style="width: 100%;">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th width="150">Tanggal Dibuat</th>
                        <th>Warehouse</th>
                        <th class="text-center" width="80">Revisi</th>
                        <th class="text-center" width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($results)) {
                        $numb = 0;
                        foreach ($results as $record) {
                            $numb++; ?>
                            <tr>
                                <td class="text-center text-muted small"><?= $numb ?></td>
                                <td class="small text-nowrap">
                                    <i class="fa fa-calendar-alt text-muted me-1"></i>
                                    <?= date('d M Y', strtotime($record->tanggal)) ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= strtoupper($record->nm_dept) ?></div>
                                    <div class="text-muted small font-monospace"><?= $record->code_budget ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary border">Rev <?= $record->rev ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if ($ENABLE_VIEW) : ?>
                                            <button type="button" class="btn btn-outline-info" title="Lihat Detail" onclick="view_data('<?= $record->code_budget ?>')">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($ENABLE_MANAGE) : ?>
                                            <button type="button" class="btn btn-outline-warning" title="Edit Budget" onclick="edit_data('<?= $record->code_budget ?>')">
                                                <i class="fa fa-pencil-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($ENABLE_DELETE) : ?>
                                            <button type="button" class="btn btn-outline-danger" title="Hapus Budget" onclick="delete_data('<?= $record->code_budget ?>')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Container for Form Loaded via AJAX -->
<div id="form-data" style="display: none;"></div>

<!-- DataTables & Plugins -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    $(function() {
        $("#example1").DataTable({
            paging: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-2"lfB>rt<"d-flex flex-wrap justify-content-between align-items-center mt-2"ip>',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel me-1 text-success"></i> Export Excel',
                    className: 'btn btn-sm btn-outline-success',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                }
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang cocok",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Sebelum"
                }
            }
        });
    });

    function add_data() {
        var url = 'budget_rutin/create/';
        $("#card-list, .box").hide();
        $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-success" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat formulir tambah budget stock...</p></div>');
        $("#form-data").load(siteurl + url);
    }

    function edit_data(id) {
        if (id != "") {
            var url = 'budget_rutin/edit/' + id;
            $("#card-list, .box").hide();
            $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat formulir edit budget stock...</p></div>');
            $("#form-data").load(siteurl + url);
        }
    }

    function view_data(id) {
        if (id != "") {
            var url = 'budget_rutin/view/' + id;
            $("#card-list, .box").hide();
            $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat detail budget stock...</p></div>');
            $("#form-data").load(siteurl + url);
        }
    }

    function delete_data(id) {
        Swal.fire({
            title: "Anda Yakin?",
            text: "Data budget stock " + id + " akan dihapus secara permanen!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "<i class='fa fa-trash me-1'></i> Ya, Hapus!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menghapus data...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                $.ajax({
                    url: siteurl + 'budget_rutin/hapus_data/' + id,
                    dataType: "json",
                    type: 'POST',
                    success: function(msg) {
                        if (msg['delete'] == '1' || msg['delete'] == true) {
                            Swal.fire({
                                icon: "success",
                                title: "Terhapus!",
                                text: "Data budget stock berhasil dihapus.",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: "Data gagal dihapus.",
                                confirmButtonText: "Tutup"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: "Gagal memproses request AJAX: " + error,
                            confirmButtonText: "Tutup"
                        });
                    }
                });
            }
        });
    }

    function cancel() {
        $("#form-data").hide().empty();
        $("#card-list, .box").show();
    }
</script>
