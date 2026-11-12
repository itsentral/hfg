<?php
$edit_mode = (isset($mode) && $mode === 'edit');
$header_id = (isset($data->id)) ? $data->id : '';
?>
<!-- Select2 CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

<?= form_open('petty_cash_master/save', array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form')) ?>
<input type="hidden" id="id" name="id" value="<?= $header_id ?>">

<!-- Header Fields Card -->
<div class="card border-0 bg-light p-3 mb-4 rounded-3">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="nama" class="form-label fw-bold text-dark">
                <i class="fa fa-tag text-primary me-1"></i> Nama Master Petty Cash <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="nama" name="nama" value="<?= isset($data->nama) ? htmlspecialchars($data->nama) : '' ?>" placeholder="Contoh: Petty Cash Operasional HO" required>
        </div>
        <div class="col-md-6">
            <label for="keterangan" class="form-label fw-bold text-dark">
                <i class="fa fa-info-circle text-info me-1"></i> Keterangan <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="keterangan" name="keterangan" value="<?= isset($data->keterangan) ? htmlspecialchars($data->keterangan) : '' ?>" placeholder="Contoh: Kas kecil untuk kebutuhan operasional harian" required>
        </div>
    </div>
</div>

<!-- Detail Table Section -->
<div class="card border mb-3">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="fa fa-list text-primary me-2"></i>
            <h6 class="mb-0 fw-bold text-dark">Daftar Alokasi COA & Nominal Budget</h6>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-2" id="btn-add-row">
            <i class="fa fa-plus me-1"></i> Tambah Baris COA
        </button>
    </div>
    <div class="table-responsive">
        <table id="detail-table" class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th width="40" class="text-center">No</th>
                    <th width="280">Akun COA <span class="text-danger">*</span></th>
                    <th>Jenis Pengeluaran <span class="text-danger">*</span></th>
                    <th width="220" class="text-end">Nominal Budget <span class="text-danger">*</span></th>
                    <th width="60" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($edit_mode && isset($details) && !empty($details)) : ?>
                    <?php foreach ($details as $idx => $detail) : ?>
                        <tr>
                            <td class="text-center row-number fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td>
                                <select name="detail[<?= $idx ?>][coa_code]" class="form-select select2 coa-select" style="width: 100%;">
                                    <option value="">-- Pilih COA --</option>
                                    <?php foreach ($coa_list as $code => $label) : ?>
                                        <option value="<?= $code ?>" <?= ($detail->coa_code == $code) ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="detail[<?= $idx ?>][jenis_pengeluaran]" class="form-control" value="<?= htmlspecialchars($detail->jenis_pengeluaran) ?>" placeholder="Jenis Pengeluaran">
                            </td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted small">Rp</span>
                                    <input type="text" name="detail[<?= $idx ?>][nominal]" class="form-control nominal-input text-end fw-semibold font-monospace" value="<?= number_format($detail->nominal, 0, ',', '.') ?>" placeholder="0">
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Hapus Baris"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <!-- Default empty row for create mode -->
                    <tr>
                        <td class="text-center row-number fw-semibold text-muted">1</td>
                        <td>
                            <select name="detail[0][coa_code]" class="form-select select2 coa-select" style="width: 100%;">
                                <option value="">-- Pilih COA --</option>
                                <?php foreach ($coa_list as $code => $label) : ?>
                                    <option value="<?= $code ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="detail[0][jenis_pengeluaran]" class="form-control" value="" placeholder="Jenis Pengeluaran">
                        </td>
                        <td>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted small">Rp</span>
                                <input type="text" name="detail[0][nominal]" class="form-control nominal-input text-end fw-semibold font-monospace" value="" placeholder="0">
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Hapus Baris"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Total Budget Summary Card -->
<div class="card bg-success-subtle border-success-subtle p-3 mb-4 rounded-3 shadow-sm">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2 text-success">
            <i class="fa fa-calculator fs-4"></i>
            <div>
                <div class="fw-bold fs-6">Total Budget Petty Cash</div>
                <small class="text-muted">Akumulasi seluruh nominal alokasi COA di atas</small>
            </div>
        </div>
        <div class="fs-4 fw-bold text-success font-monospace" id="total-budget-display">Rp 0</div>
    </div>
</div>

<!-- Footer Buttons -->
<div class="d-flex justify-content-end gap-2 pt-2 border-top">
    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal" data-dismiss="modal">
        <i class="fa fa-times me-1"></i> Batal
    </button>
    <button type="button" class="btn btn-primary px-4 shadow-sm" id="btn-save">
        <i class="fa fa-save me-1"></i> Simpan Data
    </button>
</div>
<?= form_close() ?>

<!-- Hidden COA options template for JS -->
<script type="text/template" id="coa-options-template">
    <option value="">-- Pilih COA --</option>
    <?php foreach ($coa_list as $code => $label) : ?>
        <option value="<?= $code ?>"><?= $label ?></option>
    <?php endforeach; ?>
</script>

<!-- Select2 JS -->
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize Select2 on existing dropdowns
        initSelect2();
        calculateTotal();
    });

    function initSelect2() {
        $('.coa-select').select2({
            dropdownParent: $('#modal-crud'),
            width: '100%',
            placeholder: '-- Pilih COA --'
        });
    }

    /**
     * Parse formatted nominal string to integer
     */
    function parseNominal(str) {
        if (!str || str.toString().trim() === '') return 0;
        var cleaned = str.toString().replace(/\./g, '').replace(/,/g, '');
        var num = parseInt(cleaned, 10);
        return isNaN(num) ? 0 : num;
    }

    /**
     * Format integer to Indonesian number format "1.000.000"
     */
    function formatNominal(num) {
        if (!num || num === 0) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Auto-format nominal inputs as user types
    $(document).on('keyup', '.nominal-input', function(e) {
        if (e.which >= 37 && e.which <= 40) return;

        var val = $(this).val();
        var num = parseNominal(val);

        if (num === 0 && val !== '0') {
            $(this).val('');
        } else {
            $(this).val(formatNominal(num));
        }
        calculateTotal();
    });

    /**
     * Calculate total budget from all nominal inputs and update display
     */
    function calculateTotal() {
        var total = 0;
        $('.nominal-input').each(function() {
            total += parseNominal($(this).val());
        });
        $('#total-budget-display').text('Rp ' + formatNominal(total));
    }

    /**
     * Renumber all detail rows sequentially
     */
    function renumberRows() {
        $('#detail-table tbody tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
            $(this).find('[name]').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    name = name.replace(/detail\[\d+\]/, 'detail[' + index + ']');
                    $(this).attr('name', name);
                }
            });
        });
    }

    /**
     * Add a new detail row
     */
    function addDetailRow() {
        var rowCount = $('#detail-table tbody tr').length;
        var coaOptions = $('#coa-options-template').html();

        var newRow = '<tr>' +
            '<td class="text-center row-number fw-semibold text-muted">' + (rowCount + 1) + '</td>' +
            '<td>' +
            '<select name="detail[' + rowCount + '][coa_code]" class="form-select select2 coa-select" style="width: 100%;">' +
            coaOptions +
            '</select>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="detail[' + rowCount + '][jenis_pengeluaran]" class="form-control" value="" placeholder="Jenis Pengeluaran">' +
            '</td>' +
            '<td>' +
            '<div class="input-group">' +
            '<span class="input-group-text bg-light text-muted small">Rp</span>' +
            '<input type="text" name="detail[' + rowCount + '][nominal]" class="form-control nominal-input text-end fw-semibold font-monospace" value="" placeholder="0">' +
            '</div>' +
            '</td>' +
            '<td class="text-center">' +
            '<button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Hapus Baris"><i class="fa fa-trash"></i></button>' +
            '</td>' +
            '</tr>';

        $('#detail-table tbody').append(newRow);
        $('#detail-table tbody tr:last').find('.coa-select').select2({
            dropdownParent: $('#modal-crud'),
            width: '100%',
            placeholder: '-- Pilih COA --'
        });
    }

    /**
     * Remove a detail row
     */
    function removeDetailRow(btn) {
        var rowCount = $('#detail-table tbody tr').length;
        if (rowCount <= 1) {
            Swal.fire({
                title: 'Perhatian',
                text: 'Minimal harus ada 1 baris detail.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
        $(btn).closest('tr').remove();
        renumberRows();
        calculateTotal();
    }

    /**
     * Validate form before submission
     */
    function validateForm() {
        var nama = $('#nama').val().trim();
        var keterangan = $('#keterangan').val().trim();

        if (!nama) {
            return 'Field Nama Master harus diisi';
        }
        if (!keterangan) {
            return 'Field Keterangan harus diisi';
        }

        var rowCount = $('#detail-table tbody tr').length;
        if (rowCount < 1) {
            return 'Detail COA harus diisi minimal 1 baris';
        }

        var rowError = '';
        $('#detail-table tbody tr').each(function(index) {
            var coa = $(this).find('.coa-select').val();
            var jenis = $(this).find('[name$="[jenis_pengeluaran]"]').val().trim();
            var nominalVal = $(this).find('.nominal-input').val();
            var parsed = parseNominal(nominalVal);

            if (!coa) {
                rowError = 'Silakan pilih COA pada baris ke-' + (index + 1);
                return false;
            }
            if (!jenis) {
                rowError = 'Jenis Pengeluaran pada baris ke-' + (index + 1) + ' harus diisi';
                return false;
            }
            if (parsed <= 0) {
                rowError = 'Nominal pada baris ke-' + (index + 1) + ' harus lebih besar dari 0';
                return false;
            }
        });

        if (rowError) {
            return rowError;
        }

        return '';
    }

    /**
     * Save data with SweetAlert2 confirmation and AJAX POST
     */
    function saveData() {
        Swal.fire({
            title: 'Simpan Data?',
            text: 'Apakah Anda yakin ingin menyimpan master petty cash ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-save me-1"></i> Ya, Simpan',
            cancelButtonText: '<i class="fa fa-times me-1"></i> Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                var formData = $('#frm_data').serialize();

                $.ajax({
                    url: siteurl + 'petty_cash_master/save',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 1) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.msg,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(function() {
                                $('#modal-crud').modal('hide');
                                if (typeof DataTables === 'function') {
                                    DataTables();
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.msg,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal menghubungi server',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }

    // ============================================================
    // Event Bindings
    // ============================================================

    // Add row button
    $('#btn-add-row').off('click').on('click', addDetailRow);

    // Remove row button (delegated for dynamic rows)
    $(document).off('click', '.btn-remove-row').on('click', '.btn-remove-row', function() {
        removeDetailRow(this);
    });

    // Real-time total calculation on nominal input
    $(document).off('input', '.nominal-input').on('input', '.nominal-input', calculateTotal);

    // Keypress filter on nominal fields - allow only digits and dots
    $(document).off('keypress', '.nominal-input').on('keypress', '.nominal-input', function(e) {
        var charCode = e.which || e.keyCode;
        if (charCode !== 46 && (charCode < 48 || charCode > 57)) {
            e.preventDefault();
        }
    });

    // Save button click - validate then save
    $('#btn-save').off('click').on('click', function() {
        var error = validateForm();
        if (error) {
            Swal.fire({
                title: 'Validasi Gagal',
                text: error,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
        saveData();
    });
</script>
