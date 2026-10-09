<?php
$ENABLE_ADD     = has_permission('Pembayaran_Periodik.Add');
$ENABLE_MANAGE  = has_permission('Pembayaran_Periodik.Manage');
$ENABLE_VIEW    = has_permission('Pembayaran_Periodik.View');
$ENABLE_DELETE  = has_permission('Pembayaran_Periodik.Delete');
?>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
    .dept-dropdown-menu {
        padding: 0;
        min-width: 320px;
        border-radius: 8px;
        overflow: hidden;
    }

    .dept-search-wrap {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .dept-search-icon {
        color: #6c757d;
        margin-right: 8px;
        font-size: 13px;
    }

    .dept-search-input {
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 13px;
        width: 100%;
        outline: none;
        background: #fff;
    }

    .dept-search-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }

    .dept-header {
        padding: 8px 14px;
        font-size: 11px;
        font-weight: 700;
        color: #6c757d;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        background: #f1f3f5;
        border-bottom: 1px solid #dee2e6;
        cursor: default;
    }

    #dept_list {
        max-height: 260px;
        overflow-y: auto;
    }

    .dept-item>a {
        display: block;
        padding: 9px 16px;
        font-size: 13px;
        color: #212529;
        text-decoration: none;
        transition: background 0.15s;
    }

    .dept-item>a:hover {
        background: #e7f1ff;
        color: #0d6efd;
    }
</style>

<div class="card shadow-sm border-0 mb-4" id="card-list">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-sm text-primary me-2 rounded-2 d-flex align-items-center justify-content-center p-2" style="width: 40px; height: 40px; background-color: rgba(13, 110, 253, 0.1);">
                <i class="fa fa-calendar-check fs-5"></i>
            </div>
            <div>
                <h5 class="card-title mb-0 fw-bold text-dark">Master Pembayaran Periodik</h5>
                <small class="text-muted">Kelola master anggaran rutin & jadwal pembayaran periodik per departemen</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($ENABLE_MANAGE) : ?>
                <button type="button" class="btn btn-outline-info btn-sm rounded-2 shadow-sm" title="Proses Budget ke Master" onclick="data_proses()">
                    <i class="fa fa-refresh me-1"></i> Proses Budget
                </button>
            <?php endif; ?>

            <?php if ($ENABLE_ADD) : ?>
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm rounded-2 shadow-sm dropdown-toggle" type="button" id="dropdownNewDept" data-bs-toggle="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-plus me-1"></i> Tambah Budget Baru
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dept-dropdown-menu shadow border-0" aria-labelledby="dropdownNewDept">
                        <li class="dept-search-wrap">
                            <span class="dept-search-icon"><i class="fa fa-search"></i></span>
                            <input type="text" id="dept_search" class="dept-search-input" placeholder="Cari departemen..." autocomplete="off">
                        </li>
                        <li class="dept-header">
                            <i class="fa fa-university me-1"></i> PILIH DEPARTEMEN
                        </li>
                        <div id="dept_list">
                            <?php
                            if (!empty($datdept)) {
                                foreach ($datdept as $key => $val) {
                                    if (empty($key) || empty($val)) continue;
                                    echo '<li class="dept-item"><a href="javascript:void(0)" onclick="new_data(\'' . $key . '\')"><i class="fa fa-building text-primary me-2"></i>' . htmlspecialchars($val) . '</a></li>';
                                }
                            }
                            ?>
                            <li class="dept-no-result" style="display:none;">
                                <span class="text-muted p-3 d-block"><i class="fa fa-info-circle me-1"></i> Tidak ada departemen</span>
                            </li>
                        </div>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-hover table-striped align-middle border w-100">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>Penanggung Jawab / Departemen</th>
                        <th width="160" class="text-center">Jumlah Post Biaya</th>
                        <th width="180" class="text-end">Total Estimasi Nilai</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($results)) {
                        $numb = 0;
                        foreach ($results as $record) {
                            $numb++;
                            // Safely resolve department name:
                            $dept_name = '';
                            if (isset($datdept[$record->departement]) && !empty($datdept[$record->departement])) {
                                $dept_name = $datdept[$record->departement];
                            } elseif (isset($all_dept[$record->departement]) && !empty($all_dept[$record->departement])) {
                                $dept_name = $all_dept[$record->departement];
                            } else {
                                $dept_name = 'Departemen #' . $record->departement;
                            }
                    ?>
                            <tr>
                                <td class="text-center fw-semibold text-muted"><?= $numb; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded-circle bg-primary-subtle text-primary p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fa fa-building"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark"><?= strtoupper(htmlspecialchars($dept_name)) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <i class="fa fa-list me-1"></i><?= isset($record->total_item) ? $record->total_item : '-' ?> Post
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold text-dark font-monospace">
                                        <?= isset($record->total_nilai) ? 'Rp ' . number_format($record->total_nilai, 0, ',', '.') : '-' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
                                        <?php if ($ENABLE_VIEW) : ?>
                                            <button type="button" class="btn btn-outline-info btn-sm view" title="Lihat Detail Budget" onclick="data_view('<?= $record->departement ?>')">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($ENABLE_MANAGE) : ?>
                                            <button type="button" class="btn btn-outline-warning btn-sm edit" title="Edit Master Budget" onclick="data_edit('<?= $record->departement ?>')">
                                                <i class="fa fa-pen"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                    <?php
                        }
                    }  ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="form-data" style="display:none;"></div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var url_add_def = siteurl + 'budget_periodik/create/';
    var url_edit = siteurl + 'budget_periodik/edit/';
    var url_delete = siteurl + 'budget_periodik/hapus_data/';
    var url_view = siteurl + 'budget_periodik/view/';

    $(document).ready(function() {
        $('#mytabledata').DataTable({
            order: [
                [0, 'asc']
            ]
        });
    });

    function new_data(key) {
        if (key != '') {
            $("#card-list").hide();
            $(".box").hide();
            $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat formulir...</p></div>');
            $("#form-data").load(url_add_def + key);
        }
    }

    function data_edit(id) {
        if (id != '') {
            $("#card-list").hide();
            $(".box").hide();
            $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat data edit...</p></div>');
            $("#form-data").load(url_edit + id);
        }
    }

    function data_view(id) {
        if (id != '') {
            $("#card-list").hide();
            $(".box").hide();
            $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat rincian...</p></div>');
            $("#form-data").load(url_view + id);
        }
    }

    function close_form() {
        $("#form-data").hide().html('');
        $("#card-list").show();
        $(".box").show();
    }

    function data_proses() {
        Swal.fire({
            title: 'Proses Budget Periodik?',
            text: 'Data anggaran periodik akan diproses dan diakumulasikan ke Master Budget tahun berjalan!',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-check me-1"></i> Ya, Proses!',
            cancelButtonText: '<i class="fa fa-times me-1"></i> Batal',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    dataType: "json",
                    url: siteurl + 'budget_periodik/proses_budget_periodik',
                    type: 'POST',
                    success: function(msg) {
                        if (msg['save'] == '1') {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Data Budget Periodik Berhasil Diproses',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: 'Data Gagal Diproses',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal menghubungi server',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }

    // Search filter dropdown departemen
    $(document).on('keyup', '#dept_search', function() {
        var keyword = $(this).val().toLowerCase();
        var found = 0;
        $('#dept_list .dept-item').each(function() {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(keyword) > -1) {
                $(this).show();
                found++;
            } else {
                $(this).hide();
            }
        });
        $('.dept-no-result').toggle(found === 0);
    });

    $(document).on('click', '#dept_search', function(e) {
        e.stopPropagation();
    });

    $(document).on('hidden.bs.dropdown', '.dropdown', function() {
        $('#dept_search').val('');
        $('#dept_list .dept-item').show();
        $('.dept-no-result').hide();
    });

    $(document).on('shown.bs.dropdown', '.dropdown', function() {
        $('#dept_search').focus();
    });
</script>