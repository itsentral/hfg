<?php
$ENABLE_ADD     = has_permission('Mata_uang.Add');
$ENABLE_MANAGE  = has_permission('Mata_uang.Manage');
$ENABLE_VIEW    = has_permission('Mata_uang.View');
$ENABLE_DELETE  = has_permission('Mata_uang.Delete');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <div>
            <h5 class="mb-0 text-dark fw-bold">
                <i class="ti ti-coins text-primary me-2"></i>Master Currency
            </h5>
            <small class="text-muted">Manage country currency data and currency codes</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light-primary text-primary fw-semibold px-3 py-2">
                Total: <?= !empty($results) ? count($results ?? []) : 0; ?> Mata Uang
            </span>
            <?php if ($ENABLE_ADD) : ?>
                <a class="btn btn-primary add" href="javascript:void(0)" title="Add Currency">
                    <i class="ti ti-plus me-1"></i> Add Currency
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="table_mata_uang" class="table table-striped table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="width: 100px;">Code</th>
                        <th>Country</th>
                        <th>Currency Name</th>
                        <th class="text-end" style="width: 160px;">Kurs (Rp)</th>
                        <th class="text-center" style="width: 120px;">Status</th>
                        <th class="text-center" style="width: 110px;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($results)) : ?>
                        <?php $numb = 0;
                        foreach ($results as $record) : $numb++; ?>
                            <tr>
                                <td class="text-center text-muted"><?= $numb; ?></td>
                                <td>
                                    <span class="badge bg-light-primary text-primary fw-bold px-2 py-1 fs-6">
                                        <?= htmlspecialchars($record->kode ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <i class="ti ti-world me-1 text-secondary"></i>
                                        <?= htmlspecialchars($record->negara ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary fw-medium">
                                        <?= htmlspecialchars($record->mata_uang ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if (!empty($record->kurs) && (float)$record->kurs > 0) : ?>
                                        <span class="badge bg-light-success text-success border border-success-subtle fw-bold px-2 py-1 fs-6">
                                            Rp <?= number_format((float)$record->kurs, 2, ',', '.'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="text-muted fst-italic">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <label class="ios-toggle-labeled" title="Click to toggle status">
                                        <input type="checkbox"
                                               class="table-toggle-input"
                                               data-id="<?= $record->id_kurs; ?>"
                                               <?= ($record->activation === 'active') ? 'checked' : ''; ?>>
                                        <span class="toggle-track">
                                            <span class="toggle-text-active">Active</span>
                                            <span class="toggle-text-inactive">Inactive</span>
                                            <span class="toggle-handle"></span>
                                        </span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <?php if ($ENABLE_MANAGE) : ?>
                                            <a class="btn-icon btn-icon-edit add"
                                               href="javascript:void(0)"
                                               title="Edit"
                                               data-id="<?= $record->id_kurs; ?>">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($ENABLE_DELETE) : ?>
                                            <a class="btn-icon btn-icon-delete delete"
                                               href="javascript:void(0)"
                                               title="Delete"
                                               data-id="<?= $record->id_kurs; ?>"
                                               data-name="<?= htmlspecialchars((string) $record->negara, ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="ti ti-trash"></i>
                                            </a>
                                        <?php endif; ?>
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

<!-- Modal Popup Form -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="data_form" autocomplete="off">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white fw-bold" id="head_title">
                        <i class="ti ti-coins me-2"></i>Currency
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4" id="ModalView">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading form...</p>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn_save">
                        <i class="ti ti-device-floppy me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js'); ?>"></script>
<script type="text/javascript">
    // Helper Universal Confirm Dialog untuk SweetAlert 1 & 2
    function customConfirm(title, text, confirmBtnText, onConfirmed) {
        if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e02424',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed || result.value === true) {
                    onConfirmed();
                }
            });
        } else if (typeof swal !== 'undefined') {
            swal({
                title: title,
                text: text,
                type: 'warning',
                showCancelButton: true,
                confirmButtonClass: 'btn-danger',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Cancel',
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm === true) {
                    onConfirmed();
                }
            });
        } else {
            if (confirm(text || title)) {
                onConfirmed();
            }
        }
    }

    $(document).ready(function() {

        // Inisialisasi DataTable
        $('#table_mata_uang').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Menampilkan 0 data",
                zeroRecords: "No matching data",
                paginate: {
                    previous: "Prev",
                    next: "Next"
                }
            }
        });

        // Buka Modal ADD / EDIT
        $(document).on('click', '.add', function() {
            var id = ($(this).data('id') === undefined) ? '' : $(this).data('id');
            let title = (id === '') ? 'Add Currency' : 'Edit Currency';
            $("#head_title").html('<i class="ti ti-coins me-2"></i>' + title);

            $("#dialog-popup").modal('show');
            $("#ModalView").html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading data...</p></div>');

            $.ajax({
                type: 'POST',
                url: "<?= site_url('mata_uang/form'); ?>/" + id,
                success: function(html) {
                    $("#ModalView").html(html);
                    if (typeof $.fn.autoNumeric !== 'undefined') {
                        $('#ModalView .autoNumeric').autoNumeric('init', {
                            aSep: ',',
                            aDec: '.',
                            mDec: '2'
                        });
                    }
                },
                error: function() {
                    $("#ModalView").html('<div class="alert alert-danger">Failed to load the form! Please try again.</div>');
                }
            });
        });

        // SUBMIT FORM
        $(document).on('submit', '#data_form', function(e) {
            e.preventDefault();

            var negaraVal = $('#negara').val();
            if (!negaraVal) {
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                    Swal.fire('Warning', 'Please select a country first!', 'warning');
                } else if (typeof swal !== 'undefined') {
                    swal({ title: "Warning", text: "Please select a country first!", type: "warning" });
                }
                return false;
            }

            var formData = $('#data_form').serialize();

            customConfirm(
                "Confirm Save",
                "Are you sure you want to save this currency data?",
                "Yes, Save!",
                function() {
                    $('#btn_save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                    $.ajax({
                        type: 'POST',
                        url: "<?= site_url('mata_uang/save'); ?>",
                        dataType: "json",
                        data: formData,
                        success: function(res) {
                            $('#btn_save').prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> Save');
                            if (res.status == '1') {
                                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                                    Swal.fire({
                                        title: "Success",
                                        text: res.pesan,
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else if (typeof swal !== 'undefined') {
                                    swal({
                                        title: "Success",
                                        text: res.pesan,
                                        type: "success",
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                }
                                setTimeout(function() {
                                    window.location.reload(true);
                                }, 1500);
                            } else {
                                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                                    Swal.fire("Warning", res.pesan, "warning");
                                } else if (typeof swal !== 'undefined') {
                                    swal({ title: "Warning", text: res.pesan, type: "warning" });
                                }
                            }
                        },
                        error: function() {
                            $('#btn_save').prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> Save');
                            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                                Swal.fire("Error", "An error occurred while processing the data!", "error");
                            } else if (typeof swal !== 'undefined') {
                                swal({ title: "Error", text: "An error occurred while processing the data!", type: "error" });
                            }
                        }
                    });
                }
            );
        });

        // HAPUS DATA
        $(document).on('click', '.delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name') || '';

            customConfirm(
                "Confirm Delete",
                "Are you sure you want to delete the currency for country " + name + "?",
                "Yes, Delete!",
                function() {
                    $.ajax({
                        type: 'POST',
                        url: "<?= site_url('mata_uang/hapus'); ?>",
                        dataType: "json",
                        data: { id: id },
                        success: function(res) {
                            if (res.status == '1') {
                                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                                    Swal.fire({
                                        title: "Success",
                                        text: res.pesan,
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else if (typeof swal !== 'undefined') {
                                    swal({
                                        title: "Success",
                                        text: res.pesan,
                                        type: "success",
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                }
                                setTimeout(function() {
                                    window.location.reload(true);
                                }, 1500);
                            } else {
                                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                                    Swal.fire("Failed", res.pesan, "error");
                                } else if (typeof swal !== 'undefined') {
                                    swal({ title: "Failed", text: res.pesan, type: "error" });
                                }
                            }
                        },
                        error: function() {
                            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                                Swal.fire("Error", "Failed to process data deletion!", "error");
                            } else if (typeof swal !== 'undefined') {
                                swal({ title: "Error", text: "Failed to process data deletion!", type: "error" });
                            }
                        }
                    });
                }
            );
        });

        // TOGGLE STATUS: KLIK PADA SWITCH IPHONE DI TABEL
        $(document).on('change', '.table-toggle-input', function(e) {
            var checkbox = $(this);
            var id = checkbox.data('id');
            var isChecked = checkbox.is(':checked');

            $.ajax({
                type: 'POST',
                url: "<?= site_url('mata_uang/toggle_status'); ?>",
                dataType: "json",
                data: { id: id },
                success: function(res) {
                    if (res.status == '1') {
                        // Pastikan posisi toggle sesuai status database
                        checkbox.prop('checked', res.new_status === 'active');
                    } else {
                        // Kembalikan ke posisi semula jika gagal
                        checkbox.prop('checked', !isChecked);
                        if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                            Swal.fire("Failed", res.pesan, "error");
                        } else if (typeof swal !== 'undefined') {
                            swal({ title: "Failed", text: res.pesan, type: "error" });
                        }
                    }
                },
                error: function() {
                    // Kembalikan ke posisi semula jika koneksi error
                    checkbox.prop('checked', !isChecked);
                    if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                        Swal.fire("Error", "Failed to contact the server", "error");
                    } else if (typeof swal !== 'undefined') {
                        swal({ title: "Error", text: "Failed to contact the server", type: "error" });
                    }
                }
            });
        });

    });
</script>
