<?php
$ENABLE_ADD     = has_permission('Budget_Non_Rutin.Add');
$ENABLE_MANAGE  = has_permission('Budget_Non_Rutin.Manage');
$ENABLE_VIEW    = has_permission('Budget_Non_Rutin.View');
$ENABLE_DELETE  = has_permission('Budget_Non_Rutin.Delete');
?>

<style>
    .select2-container--default .select2-results__message {
        padding: 12px 16px !important;
        text-align: center !important;
        color: #6c757d !important;
        background-color: #f8f9fa !important;
        border-radius: 6px !important;
        margin: 6px !important;
        font-size: 13px !important;
        border: 1px dashed #dee2e6 !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border-color: #dee2e6 !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>
<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<!-- Main Card List -->
<div class="card border-0 shadow-sm mb-4" id="card-list">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="row align-items-center g-3">
            <div class="col-md-7 col-12">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="fa fa-building fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title fw-bold text-dark mb-0">Daftar Budget Umum</h5>
                        <small class="text-muted">Kelola alokasi detail budget kategori Umum per departemen dan penanggung jawab</small>
                    </div>
                </div>
            </div>
            <div class="col-md-5 col-12 text-md-end">
                <div class="d-inline-flex flex-wrap align-items-center gap-2">
                    <?php if ($ENABLE_MANAGE) : ?>
                        <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" onclick="data_proses()" title="Proses Perhitungan Budget Umum">
                            <i class="fa fa-cogs me-1"></i> Proses Budget
                        </button>
                    <?php endif; ?>
                    <?php if ($ENABLE_ADD) : ?>
                        <button type="button" class="btn btn-sm btn-success shadow-sm" onclick="showmodal()">
                            <i class="fa fa-plus-circle me-1"></i> Tambah Budget Umum
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-striped table-hover align-middle border mb-0" style="width: 100%;">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th class="text-center" width="40">#</th>
                        <th>Tanggal Dibuat</th>
                        <th class="text-center" width="80">Tahun</th>
                        <th>Penanggung Jawab</th>
                        <th>Kategori</th>
                        <th class="text-end">Referensi</th>
                        <th class="text-end">Total Alokasi</th>
                        <th class="text-center" width="70">Revisi</th>
                        <th class="text-center" width="120">Status</th>
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
                                    <?= !empty($record->created_on_dept) ? date('d M Y H:i', strtotime($record->created_on_dept)) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary border"><?= $record->tahun ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($record->nm_dept)) : ?>
                                        <span class="badge bg-light text-dark border"><?= $record->nm_dept ?></span>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle"><?= $record->kategori ?></span>
                                </td>
                                <td class="text-end font-monospace">
                                    <?= number_format($record->finance_tahun, 0, ',', '.') ?>
                                </td>
                                <td class="text-end font-monospace fw-bold text-primary">
                                    <?= number_format($record->total, 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border"><?= $record->revisi ?></span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    if ($record->status == '3') {
                                        echo '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa fa-check-circle me-1"></i> Approved</span>';
                                    } elseif ($record->status == '2') {
                                        echo '<span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="fa fa-hourglass-half me-1"></i> Menunggu Approval</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary-subtle text-secondary border">Draft</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if ($record->status == '3' && $ENABLE_MANAGE) : ?>
                                            <button type="button" class="btn btn-outline-danger" title="Revisi Budget" onclick="revisi_data('<?= $record->tahun ?>','<?= $record->kategori ?>','<?= $record->divisi ?>','<?= $record->revisi ?>')">
                                                <i class="fa fa-undo"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($record->status == '2' && $ENABLE_MANAGE) : ?>
                                            <button type="button" class="btn btn-outline-success" title="Approve Budget" onclick="approve_data('<?= $record->tahun ?>','<?= $record->kategori ?>','<?= $record->divisi ?>')">
                                                <i class="fa fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" title="Edit Alokasi" onclick="edit_data('<?= $record->tahun ?>','<?= $record->kategori ?>','<?= $record->divisi ?>')">
                                                <i class="fa fa-pencil-alt"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($ENABLE_VIEW) : ?>
                                            <button type="button" class="btn btn-outline-info" title="Cetak PDF" onclick="print_data('<?= $record->tahun ?>','<?= $record->kategori ?>','<?= $record->divisi ?>')">
                                                <i class="fa fa-print"></i>
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

<!-- Modal Pilih Parameter Budget Umum -->
<div class="modal fade" tabindex="-1" id="mymodal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-2">
                        <i class="fa fa-filter"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-dark fs-6 mb-0">Pilih Parameter Budget Umum</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="frmbudget" name="frmbudget" method="post" target="_blank" action="<?= base_url('budget_coa/print_budget_umum') ?>">
                    <input type="hidden" id="fkategori" name="fkategori" value="UMUM">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Penanggung Jawab / Departemen <span class="text-danger">*</span></label>
                            <?php
                            $datadept[0] = '- Pilih Departemen -';
                            echo form_dropdown('fdivisi', $datadept, '', array('id' => 'fdivisi', 'class' => 'form-select select2-modal', 'required' => 'required'));
                            ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Tahun Anggaran <span class="text-danger">*</span></label>
                            <input type="number" id="ftahun" name="ftahun" class="form-control" value="<?= date('Y') ?>" min="2000" max="2099" maxlength="4" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm" onclick="add_data()">
                    <i class="fa fa-arrow-right me-1"></i> Lanjut
                </button>
            </div>
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
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
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

    $(function() {
        $('#mymodal').on('shown.bs.modal', function() {
            if ($.fn.select2) {
                $('.select2-modal').select2({
                    dropdownParent: $('#mymodal'),
                    width: '100%',
                    language: {
                        noResults: function() {
                            return '<div class="py-2 px-3 text-center text-muted"><i class="fa fa-info-circle text-warning me-1"></i> Data tidak ditemukan</div>';
                        }
                    },
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });
            }
        });
    });

    function showmodal() {
        $("#fdivisi").val("0").trigger('change');
        $("#ftahun").val("<?= date('Y') ?>");
        var modalEl = document.getElementById('mymodal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function add_data() {
        var divisi = $("#fdivisi").val();
        var tahun = $("#ftahun").val();
        if (!divisi || divisi == '0') {
            Swal.fire({
                icon: "warning",
                title: "Perhatian",
                text: "Silakan pilih Penanggung Jawab / Departemen terlebih dahulu!",
                confirmButtonText: "OK"
            });
            return false;
        }

        var modalEl = document.getElementById('mymodal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();

        var url = 'budget_coa/create_budget_umum';
        $("#card-list, .box").hide();
        $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat formulir alokasi budget umum...</p></div>');
        $("#form-data").load(siteurl + url, {
            kategori: $("#fkategori").val(),
            tahun: tahun,
            divisi: divisi
        });
    }

    function edit_data(tahun, kategori, divisi) {
        var url = 'budget_coa/create_budget_umum/';
        $("#card-list, .box").hide();
        $("#form-data").show().html('<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div><p class="mt-2 text-muted fw-semibold">Memuat data alokasi edit...</p></div>');
        $("#form-data").load(siteurl + url, {
            kategori: kategori,
            tahun: tahun,
            divisi: divisi
        });
    }

    function revisi_data(tahun, kategori, divisi, revisi) {
        Swal.fire({
            title: "Revisi Data Budget?",
            text: "Status budget umum akan dikembalikan ke revisi untuk disesuaikan kembali.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "<i class='fa fa-undo me-1'></i> Ya, Revisi",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang merevisi data...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                $.ajax({
                    url: siteurl + "budget_coa/revisi_data_umum",
                    dataType: "json",
                    type: 'POST',
                    data: {
                        tahun: tahun,
                        kategori: kategori,
                        divisi: divisi,
                        revisi: revisi
                    },
                    success: function(msg) {
                        if (msg['save'] == '1') {
                            Swal.fire({
                                icon: "success",
                                title: "Sukses!",
                                text: "Data berhasil direvisi.",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: "Data gagal direvisi.",
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

    function approve_data(tahun, kategori, divisi) {
        Swal.fire({
            title: "Approve Data Budget?",
            text: "Data alokasi budget umum akan disetujui (Approved).",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#198754",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "<i class='fa fa-check me-1'></i> Ya, Approve",
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
                    url: siteurl + "budget_coa/approve_data_umum",
                    dataType: "json",
                    type: 'POST',
                    data: {
                        tahun: tahun,
                        kategori: kategori,
                        divisi: divisi
                    },
                    success: function(msg) {
                        if (msg['save'] == '1') {
                            Swal.fire({
                                icon: "success",
                                title: "Disetujui!",
                                text: "Data budget berhasil disetujui.",
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
                            text: "Gagal memproses request AJAX: " + error,
                            confirmButtonText: "Tutup"
                        });
                    }
                });
            }
        });
    }

    function print_data(tahun, kategori, divisi) {
        $("#fkategori").val(kategori);
        $("#ftahun").val(tahun);
        $("#fdivisi").val(divisi);
        $("#frmbudget").submit();
    }

    function data_proses() {
        Swal.fire({
            title: "Proses Budget Umum?",
            text: "Sistem akan mengkalkulasi ulang seluruh alokasi biaya fix dan variabel untuk tahun berjalan.",
            icon: "info",
            showCancelButton: true,
            confirmButtonColor: "#0d6efd",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "<i class='fa fa-cogs me-1'></i> Ya, Proses!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menjalankan kalkulasi budget umum...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                $.ajax({
                    dataType: "json",
                    url: siteurl + 'budget_coa/proses_budget_umum',
                    type: 'POST',
                    success: function(msg) {
                        if (msg['save'] == '1') {
                            Swal.fire({
                                icon: "success",
                                title: "Sukses!",
                                text: "Data budget umum berhasil diproses.",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: "Data gagal diproses.",
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
