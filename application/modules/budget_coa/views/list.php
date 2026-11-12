<?php
$ENABLE_ADD     = has_permission('Budget.Add');
$ENABLE_MANAGE  = has_permission('Budget.Manage');
$ENABLE_VIEW    = has_permission('Budget.View');
$ENABLE_DELETE  = has_permission('Budget.Delete');

$has_data = !empty($results);
$is_draft = false;
if ($has_data) {
    foreach ($results as $record) {
        if (isset($record->status) && $record->status == 0) {
            $is_draft = true;
            break;
        }
    }
}
?>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<!-- Card Master List -->
<div class="card border-0 shadow-sm mb-4" id="card-list">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="row align-items-center g-3">
            <div class="col-md-6 col-12">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="fa fa-calculator fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title fw-bold text-dark mb-0">Master Data Budget COA</h5>
                        <small class="text-muted">Kelola alokasi budget tahunan per akun perkiraan (COA)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12 text-md-end">
                <form method="get" action="" class="d-inline-flex align-items-center flex-wrap gap-2 justify-content-md-end">
                    <div class="input-group input-group-sm" style="width: auto;">
                        <span class="input-group-text bg-light text-muted"><i class="fa fa-calendar-alt"></i></span>
                        <select name="tahun" id="tahun" class="form-select form-select-sm" required>
                            <?php foreach ($listtahun as $val) : ?>
                                <option value="<?= $val ?>" <?= ($val == $tahun ? 'selected' : '') ?>><?= $val ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa fa-filter me-1"></i> Filter
                        </button>
                    </div>
                    <?php if ($ENABLE_ADD) : ?>
                        <button type="button" class="btn btn-sm btn-success shadow-sm" onclick="add_data()">
                            <i class="fa fa-plus-circle me-1"></i> Tambah Budget
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if ($has_data) : ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 mt-3 border-top">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        <i class="fa fa-calendar me-1"></i> Tahun Aktif: <strong><?= $tahun ?></strong>
                    </span>
                    <span class="badge <?= ($is_draft ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-success-subtle text-success border border-success-subtle') ?> px-2 py-1">
                        <i class="fa <?= ($is_draft ? 'fa-hourglass-half' : 'fa-check-circle') ?> me-1"></i>
                        Status: <strong><?= ($is_draft ? 'Draft / Belum Disetujui' : 'Approved / Disetujui') ?></strong>
                    </span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($ENABLE_VIEW) : ?>
                        <button type="button" class="btn btn-sm btn-outline-info" id="btn_detail" onclick="detail_data()">
                            <i class="fa fa-eye me-1"></i> Detail Budget
                        </button>
                    <?php endif; ?>
                    <?php if ($ENABLE_MANAGE) : ?>
                        <button type="button" class="btn btn-sm btn-outline-warning" id="btn_edit" onclick="edit_data()">
                            <i class="fa fa-pencil-alt me-1"></i> Edit Budget
                        </button>
                        <?php if ($is_draft) : ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn_approve" onclick="approve_data()">
                                <i class="fa fa-check me-1"></i> Approve
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($ENABLE_DELETE) : ?>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn_delete" onclick="delete_data()">
                            <i class="fa fa-trash me-1"></i> Hapus Budget
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-striped table-hover align-middle border mb-0" style="width: 100%;">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th width="80">Tahun</th>
                        <th>Akun COA</th>
                        <th>Definisi</th>
                        <th>Penanggung Jawab</th>
                        <th>Kategori</th>
                        <th>Formulasi Budget</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($results)) {
                        $numb = 0;
                        foreach ($results as $record) {
                            $numb++; ?>
                            <tr>
                                <td class="text-center text-muted"><?= $numb ?></td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border"><?= $record->tahun ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold font-monospace text-primary"><?= $record->no_perkiraan ?></div>
                                    <div class="text-secondary small"><?= $record->nama_perkiraan ?></div>
                                </td>
                                <td>
                                    <span class="text-dark"><?= !empty($record->definisi) ? htmlspecialchars($record->definisi) : '-' ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($record->nm_dept)) : ?>
                                        <span class="badge bg-light text-dark border"><?= $record->nm_dept ?></span>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($record->kategori)) : ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle"><?= $record->kategori ?></span>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-dark small"><?= !empty($record->info) ? htmlspecialchars($record->info) : '-' ?></span>
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

<!-- Card Laporan Budget Bulanan -->
<div class="card border-0 shadow-sm" id="card-laporan">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-info bg-opacity-10 text-info p-2 rounded-3">
                <i class="fa fa-chart-line fs-5"></i>
            </div>
            <div>
                <h6 class="card-title fw-bold text-dark mb-0">Laporan Budget Bulanan</h6>
                <small class="text-muted">Pilih bulan dan tahun untuk melihat realisasi Year To Date (YTD) dan alokasi tahunan</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="post" action="<?= base_url('budget_coa/detail_bulan') ?>" class="row g-3 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold text-muted">Bulan</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fa fa-calendar-alt"></i></span>
                    <select name="bulan" id="bulan" class="form-select" required>
                        <?php
                        $bulan_names = array(
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        );
                        $cur_month = date('n');
                        for ($i = 1; $i <= 12; $i++) {
                            echo '<option value="' . $i . '" ' . ($i == $cur_month ? 'selected' : '') . '>' . $bulan_names[$i] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold text-muted">Tahun</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fa fa-calendar"></i></span>
                    <select name="tahun" id="tahun_laporan" class="form-select" required>
                        <?php
                        foreach ($listtahun as $val) {
                            echo '<option value="' . $val . '" ' . ($val == $tahun ? 'selected' : '') . '>' . $val . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2 col-sm-12">
                <button type="submit" class="btn btn-info text-white w-100 shadow-sm">
                    <i class="fa fa-file-invoice me-1"></i> Lihat Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Container for Form / Detail Loaded via AJAX -->
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
        $("#mytabledata").DataTable({
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
                        columns: [0, 1, 2, 3, 4, 5, 6]
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

    function detail_data() {
        var url = 'budget_coa/detail/<?= $tahun ?>';
        $("#card-list, #card-laporan, .box").hide();
        $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat detail budget...</p></div>');
        $("#form-data").load(siteurl + url);
    }

    function add_data() {
        var url = 'budget_coa/create';
        $("#card-list, #card-laporan, .box").hide();
        $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-success" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat formulir tambah budget...</p></div>');
        $("#form-data").load(siteurl + url);
    }

    function edit_data() {
        var url = 'budget_coa/edit/<?= $tahun ?>';
        $("#card-list, #card-laporan, .box").hide();
        $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat formulir edit budget...</p></div>');
        $("#form-data").load(siteurl + url);
    }

    function delete_data() {
        Swal.fire({
            title: "Anda Yakin?",
            text: "Seluruh data budget tahun <?= $tahun ?> akan dihapus secara permanen!",
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
                    url: siteurl + 'budget_coa/hapus_data/<?= $tahun ?>',
                    dataType: "json",
                    type: 'POST',
                    success: function(msg) {
                        if (msg['delete'] == '1') {
                            Swal.fire({
                                icon: "success",
                                title: "Terhapus!",
                                text: "Data budget tahun <?= $tahun ?> berhasil dihapus.",
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
                            text: "Gagal eksekusi request: " + error,
                            confirmButtonText: "Tutup"
                        });
                    }
                });
            }
        });
    }

    function approve_data() {
        Swal.fire({
            title: "Approve Budget?",
            text: "Data budget tahun <?= $tahun ?> akan disetujui (Approved)!",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#0d6efd",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "<i class='fa fa-check me-1'></i> Ya, Approve!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menyetujui budget...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                $.ajax({
                    url: siteurl + 'budget_coa/approve_data/<?= $tahun ?>',
                    dataType: "json",
                    type: 'POST',
                    success: function(msg) {
                        if (msg['delete'] == '1') {
                            Swal.fire({
                                icon: "success",
                                title: "Disetujui!",
                                text: "Data budget tahun <?= $tahun ?> berhasil disetujui.",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: "Data gagal disetujui.",
                                confirmButtonText: "Tutup"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: "Gagal eksekusi request: " + error,
                            confirmButtonText: "Tutup"
                        });
                    }
                });
            }
        });
    }

    function cancel() {
        $("#form-data").hide().empty();
        $("#card-list, #card-laporan, .box").show();
    }
</script>
