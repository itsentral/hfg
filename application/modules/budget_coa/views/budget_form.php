<?php
$is_edit = ($type == 'edit');
$tahun_val = isset($tahun) ? $tahun : date("Y");
if (!empty($data)) {
    foreach ($data as $r) {
        if (!empty($r->tahun)) {
            $tahun_val = $r->tahun;
            break;
        }
    }
}
?>
<style>
    .table-budget-container {
        max-height: 580px;
        overflow-y: auto;
        overflow-x: auto;
    }
    .table-budget thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background-color: #f1f5f9 !important;
        box-shadow: inset 0 -1px 0 #cbd5e1;
    }
</style>

<div class="card border-0 shadow-sm" id="card-form-budget">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="<?= ($is_edit ? 'bg-warning' : 'bg-success') ?> bg-opacity-10 text-<?= ($is_edit ? 'warning' : 'success') ?> p-2 rounded-3">
                    <i class="fa <?= ($is_edit ? 'fa-edit' : 'fa-plus-circle') ?> fs-4"></i>
                </div>
                <div>
                    <h5 class="card-title fw-bold text-dark mb-0">
                        <?= ($is_edit ? 'Edit Data Budget COA' : 'Tambah Data Budget COA Baru') ?>
                    </h5>
                    <small class="text-muted">
                        <?= ($is_edit ? 'Perbarui alokasi budget dan parameter COA untuk tahun terpilih' : 'Alokasikan budget tahunan dan sesuaikan parameter per COA') ?>
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge <?= ($is_edit ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-success-subtle text-success border border-success-subtle') ?> px-2 py-1">
                    <i class="fa <?= ($is_edit ? 'fa-pencil-alt' : 'fa-plus') ?> me-1"></i>
                    Mode <?= ($is_edit ? 'Edit' : 'Tambah') ?>
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancel()" title="Tutup Formulir">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form')) ?>
    <div class="card-body p-4">
        <input type="hidden" id="type" name="type" value="<?= $type ?>">

        <!-- Header Info Form -->
        <div class="row g-3 mb-4 align-items-center bg-light p-3 rounded-3 border">
            <div class="col-md-4 col-sm-6">
                <label class="form-label fw-bold text-dark small mb-1">
                    Tahun Anggaran <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="fa fa-calendar-alt"></i></span>
                    <input type="number" class="form-control fw-bold font-monospace" id="tahunform" name="tahun" value="<?= $tahun_val ?>" placeholder="YYYY" required min="2000" max="2099" maxlength="4" <?= ($is_edit ? 'readonly' : '') ?>>
                </div>
                <small class="text-muted">Format: 4 digit tahun (contoh: <?= date('Y') ?>)</small>
            </div>
            <div class="col-md-5 col-sm-6">
                <label class="form-label fw-bold text-dark small mb-1">Cari Akun COA Cepat</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="fa fa-search"></i></span>
                    <input type="text" id="quick_filter_coa" class="form-control" placeholder="Ketik kode / nama COA untuk memfilter...">
                </div>
                <small class="text-muted">Menyaring baris COA pada tabel di bawah</small>
            </div>
            <div class="col-md-3 col-sm-12 text-md-end">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle p-2">
                    <i class="fa fa-list-ol me-1"></i> Total <strong id="total_baris_coa"><?= count($data ?? []) ?></strong> Akun COA
                </span>
            </div>
        </div>

        <!-- Table Form -->
        <div class="table-budget-container border rounded-3 mb-3">
            <table class="table table-hover table-bordered table-budget align-middle mb-0" id="table_input_budget">
                <thead>
                    <tr class="small text-uppercase fw-semibold text-secondary">
                        <th width="240">Akun COA</th>
                        <th width="180">Penanggung Jawab</th>
                        <th width="160">Kategori</th>
                        <th width="180">Definisi</th>
                        <th width="180">Formulasi Budget</th>
                        <th width="180" class="text-end">Referensi Budget/Tahun</th>
                    </tr>
                </thead>
                <tbody id="tbody_budget">
                    <?php
                    $i = 0;
                    if (!empty($data)) {
                        foreach ($data as $record) {
                            $i++;
                    ?>
                            <tr class="row-coa-item">
                                <td>
                                    <input type="hidden" name="id[]" value="<?= (isset($record->id) ? $record->id : '') ?>">
                                    <input type="hidden" name="coa[]" value="<?= $record->no_perkiraan ?>">
                                    <div class="fw-bold font-monospace text-primary coa-number"><?= $record->no_perkiraan ?></div>
                                    <div class="text-secondary small coa-name"><?= $record->nama_perkiraan ?></div>
                                </td>
                                <td>
                                    <?php
                                    $datadept[0] = '- Pilih Departemen -';
                                    echo form_dropdown('divisi[]', $datadept, set_value('divisi', isset($record->divisi) ? $record->divisi : '0'), array('id' => 'divisi' . $i, 'class' => 'form-select form-select-sm'));
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    echo form_dropdown('kategori[]', $datakategori, set_value('kategori', isset($record->kategori) ? $record->kategori : '0'), array('id' => 'kategori' . $i, 'class' => 'form-select form-select-sm'));
                                    ?>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" id="definisi_<?= $i ?>" name="definisi[]" value="<?= (isset($record->definisi) ? htmlspecialchars($record->definisi) : '') ?>" placeholder="Definisi anggaran">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" id="info_<?= $i ?>" name="info[]" value="<?= (isset($record->info) ? htmlspecialchars($record->info) : '') ?>" placeholder="Formulasi budget">
                                </td>
                                <td>
                                    <input type="hidden" id="finance_bulan<?= $i ?>" name="finance_bulan[]" value="<?= (isset($record->finance_bulan) ? $record->finance_bulan : 0) ?>">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">Rp</span>
                                        <input type="text" class="form-control form-control-sm text-end fw-semibold font-monospace divide" id="finance_tahun<?= $i ?>" name="finance_tahun[]" value="<?= (isset($record->finance_tahun) ? number_format($record->finance_tahun, 0, ',', '.') : 0) ?>" placeholder="0">
                                    </div>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fa fa-info-circle me-1"></i> Tidak ada data COA level 5 yang ditemukan.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
            <div class="text-muted small">
                <i class="fa fa-info-circle text-primary me-1"></i> Pastikan seluruh akun COA telah dialokasikan penanggung jawab dan nilai referensinya dengan benar.
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary px-3" onclick="cancel()">
                    <i class="fa fa-arrow-left me-1"></i> Batal
                </button>
                <button type="submit" name="save" class="btn btn-primary px-4 shadow-sm" id="btn_submit_budget">
                    <i class="fa fa-save me-1"></i> Simpan Data Budget
                </button>
            </div>
        </div>
    </div>
    <?= form_close() ?>
</div>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
        if ($.fn.divide) {
            $(".divide").divide({
                delimiter: '.',
                divideFor: 'all'
            });
        }

        // Quick filter for COA list
        $("#quick_filter_coa").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tbody_budget tr.row-coa-item").filter(function() {
                var coaText = $(this).find(".coa-number").text().toLowerCase() + " " + $(this).find(".coa-name").text().toLowerCase();
                $(this).toggle(coaText.indexOf(value) > -1);
            });
        });
    });

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();

        var tahun = $("#tahunform").val();
        if (!tahun || tahun.length < 4) {
            Swal.fire({
                icon: "warning",
                title: "Perhatian",
                text: "Silakan masukkan tahun anggaran dengan benar (4 digit angka)!",
                confirmButtonText: "OK"
            });
            return false;
        }

        Swal.fire({
            title: "Simpan Data Budget?",
            text: "Pastikan seluruh alokasi data budget tahun " + tahun + " sudah benar.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#0d6efd",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "<i class='fa fa-save me-1'></i> Ya, Simpan",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                var $btn = $("#btn_submit_budget");
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menyimpan data budget...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                var formdata = $("#frm_data").serialize();
                $.ajax({
                    url: siteurl + "budget_coa/save_data",
                    dataType: "json",
                    type: 'POST',
                    data: formdata,
                    success: function(msg) {
                        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Data Budget');
                        if (msg['save'] == true || msg['save'] == '1') {
                            Swal.fire({
                                icon: "success",
                                title: "Sukses!",
                                text: "Data budget berhasil disimpan.",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = siteurl + 'budget_coa?tahun=' + tahun;
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: "Data budget gagal disimpan.",
                                confirmButtonText: "Tutup"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Data Budget');
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
    });

    function cancel() {
        $("#form-data").hide().empty();
        $("#card-list, #card-laporan, .box").show();
    }
</script>
