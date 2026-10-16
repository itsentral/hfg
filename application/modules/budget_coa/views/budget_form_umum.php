<?php
$dept_name = isset($datadept[$dataset['divisi']]) ? $datadept[$dataset['divisi']] : $dataset['divisi'];
?>
<style>
    .table-umum-container {
        max-height: 580px;
        overflow-y: auto;
    }
    .table-umum thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background-color: #f1f5f9 !important;
        box-shadow: inset 0 -1px 0 #cbd5e1;
    }
    .month-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 6px;
    }
</style>

<div class="card border-0 shadow-sm" id="card-form-umum">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                    <i class="fa fa-building fs-4"></i>
                </div>
                <div>
                    <h5 class="card-title fw-bold text-dark mb-0">
                        Input Detail Budget Umum
                    </h5>
                    <small class="text-muted">
                        Departemen: <strong><?= $dept_name ?></strong>, Tahun: <strong><?= $dataset['tahun'] ?></strong>
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    UMUM
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancel()" title="Tutup Formulir">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form')) ?>
    <input type="hidden" id="type" name="type" value="edit">
    <input type="hidden" id="tahun" name="tahun" value="<?= $dataset['tahun'] ?>">
    <input type="hidden" id="divisi" name="divisi" value="<?= $dataset['divisi'] ?>">
    <input type="hidden" id="kategori" name="kategori" value="<?= $dataset['kategori'] ?>">

    <div class="card-body p-4">
        <!-- Info Strip & Quick Filter -->
        <div class="row g-3 mb-4 align-items-center bg-light p-3 rounded-3 border">
            <div class="col-md-3 col-sm-6">
                <small class="text-muted d-block fw-semibold">Tahun Anggaran</small>
                <div class="fw-bold font-monospace fs-6 text-dark mt-1">
                    <i class="fa fa-calendar me-1 text-primary"></i> <?= $dataset['tahun'] ?>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <small class="text-muted d-block fw-semibold">Penanggung Jawab / Departemen</small>
                <div class="fw-bold fs-6 text-dark mt-1">
                    <i class="fa fa-building me-1 text-secondary"></i> <?= $dept_name ?>
                </div>
            </div>
            <div class="col-md-5 col-sm-12">
                <small class="text-muted d-block fw-semibold">Cari Akun COA Cepat</small>
                <div class="input-group input-group-sm mt-1">
                    <span class="input-group-text bg-white text-muted"><i class="fa fa-search"></i></span>
                    <input type="text" id="quick_filter_coa" class="form-control" placeholder="Ketik kode / nama COA...">
                </div>
            </div>
        </div>

        <!-- Table Form -->
        <div class="table-umum-container border rounded-3 mb-3">
            <table class="table table-hover table-bordered table-umum align-middle mb-0" id="table_input_umum">
                <thead>
                    <tr class="small text-uppercase fw-semibold text-secondary text-nowrap">
                        <th width="40" class="text-center">#</th>
                        <th width="240">Akun COA</th>
                        <th width="200">Jenis Alokasi</th>
                        <th>Parameter Nilai / Alokasi Bulan</th>
                        <th width="260">Variabel Acuan COA</th>
                    </tr>
                </thead>
                <tbody id="tbody_umum">
                    <?php
                    $i = 0;
                    if (!empty($data)) {
                        foreach ($data as $record) {
                            $i++;
                            $is_bulanan = ($record->jenis == 'FIX COST BULANAN');
                            $is_variable = ($record->jenis == 'VARIABLE');
                    ?>
                            <tr class="row-umum-item" id="row_umum_<?= $i ?>">
                                <td class="text-center text-muted small"><?= $i ?></td>
                                <td>
                                    <input type="hidden" name="id[]" value="<?= (isset($record->id) ? $record->id : '') ?>">
                                    <input type="hidden" name="coa[]" value="<?= $record->no_perkiraan ?>">
                                    <div class="fw-bold font-monospace text-primary coa-number"><?= $record->no_perkiraan ?></div>
                                    <div class="text-secondary small coa-name"><?= $record->nama_perkiraan ?></div>
                                </td>
                                <td>
                                    <?php
                                    echo form_dropdown(
                                        'jenis[]',
                                        $datajenis,
                                        set_value('jenis', isset($record->jenis) ? $record->jenis : '0'),
                                        array(
                                            'id' => 'jenis' . $i,
                                            'class' => 'form-select form-select-sm select-jenis',
                                            'data-row' => $i,
                                            'onchange' => 'cekvariabel(' . $i . ')'
                                        )
                                    );
                                    ?>
                                </td>
                                <td>
                                    <!-- Single Nilai Input (Untuk Fix Cost Tahunan & Variable) -->
                                    <div class="input-group input-group-sm <?= ($is_bulanan ? 'd-none' : '') ?>" id="divilai<?= $i ?>" style="max-width: 280px;">
                                        <span class="input-group-text bg-light">Rp / %</span>
                                        <input type="text" id="nilai<?= $i ?>" name="nilai[]" value="<?= (isset($record->nilai) ? number_format($record->nilai, 0, ',', '.') : 0) ?>" class="form-control form-control-sm text-end font-monospace divide">
                                        <span class="input-group-text bg-light <?= (!$is_variable ? 'd-none' : '') ?>" id="persen<?= $i ?>">%</span>
                                    </div>

                                    <!-- 12 Months Breakdown (Untuk Fix Cost Bulanan) -->
                                    <div id="tdbulan<?= $i ?>" class="<?= (!$is_bulanan ? 'd-none' : '') ?> p-2 bg-light rounded-2 border">
                                        <div class="month-grid mb-2">
                                            <?php
                                            $bulan_labels = array(
                                                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                                                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                                                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                                            );
                                            for ($bln = 1; $bln <= 12; $bln++) {
                                                $b_val = isset($record->{"bulan_" . $bln}) ? (float)$record->{"bulan_" . $bln} : 0;
                                            ?>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text px-1 py-0 small bg-white text-muted" style="font-size: 11px;"><?= $bulan_labels[$bln] ?></span>
                                                    <input type="text" class="form-control form-control-sm text-end font-monospace divide col-bulan-item" id="bulan_<?= $bln ?>_<?= $i ?>" name="bulan_<?= $bln ?>[]" value="<?= number_format($b_val, 0, ',', '.') ?>" data-row="<?= $i ?>" onblur="cektotalrow(<?= $i ?>)" placeholder="0">
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between pt-1 border-top">
                                            <span class="small fw-semibold text-secondary">Total Setahun:</span>
                                            <div class="input-group input-group-sm" style="width: 180px;">
                                                <span class="input-group-text bg-white small">Rp</span>
                                                <input type="text" class="form-control form-control-sm text-end fw-bold font-monospace text-primary bg-white divide" id="total<?= $i ?>" name="total[]" value="<?= number_format(isset($record->total) ? $record->total : 0, 0, ',', '.') ?>" readonly tabindex="-1">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div id="row<?= $i ?>" class="<?= (!$is_variable ? 'd-none' : '') ?>">
                                        <?php
                                        $datacoa[0] = '- Pilih Akun Acuan -';
                                        echo form_dropdown(
                                            'variabel_coa[]',
                                            $datacoa,
                                            set_value('variabel_coa', isset($record->variabel_coa) ? $record->variabel_coa : '0'),
                                            array('id' => 'variabel_coa' . $i, 'class' => 'form-select form-select-sm select2')
                                        );
                                        ?>
                                    </div>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Tidak ada akun perkiraan budget umum yang ditemukan.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
            <div class="text-muted small">
                <i class="fa fa-info-circle text-primary me-1"></i> Pilih jenis alokasi (*Fix Cost Bulanan*, *Fix Cost Tahunan*, atau *Variable*) kemudian tentukan nilai parameternya.
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary px-3" onclick="cancel()">
                    <i class="fa fa-arrow-left me-1"></i> Batal
                </button>
                <button type="submit" name="save" class="btn btn-primary px-4 shadow-sm" id="btn_submit_umum">
                    <i class="fa fa-save me-1"></i> Simpan Budget Umum
                </button>
            </div>
        </div>
    </div>
    <?= form_close() ?>
</div>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
    function parseNum(val) {
        if (!val) return 0;
        var s = String(val).replace(/\./g, '').replace(/,/g, '');
        var n = parseFloat(s);
        return isNaN(n) ? 0 : n;
    }

    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function cektotalrow(row) {
        var sum = 0;
        for (var i = 1; i <= 12; i++) {
            sum += parseNum($("#bulan_" + i + "_" + row).val());
        }
        $("#total" + row).val(formatNumber(sum));
    }

    function cekvariabel(idrow) {
        var variabel = $("#jenis" + idrow).val();
        if (variabel == 'VARIABLE') {
            $("#row" + idrow).removeClass("d-none");
            $("#persen" + idrow).removeClass("d-none");
        } else {
            $("#row" + idrow).addClass("d-none");
            $("#persen" + idrow).addClass("d-none");
            $("#variabel_coa" + idrow).val('0').trigger('change');
        }

        if (variabel == 'FIX COST BULANAN') {
            $("#tdbulan" + idrow).removeClass("d-none");
            $("#divilai" + idrow).addClass("d-none");
            $("#nilai" + idrow).val(0);
        } else {
            $("#tdbulan" + idrow).addClass("d-none");
            $("#divilai" + idrow).removeClass("d-none");
        }
    }

    $(document).ready(function() {
        if ($.fn.divide) {
            $(".divide").divide({
                delimiter: '.',
                divideFor: 'all'
            });
        }

        $('.select2').select2({ width: '100%' });

        // Quick filter for COA
        $("#quick_filter_coa").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tbody_umum tr.row-umum-item").filter(function() {
                var coaText = $(this).find(".coa-number").text().toLowerCase() + " " + $(this).find(".coa-name").text().toLowerCase();
                $(this).toggle(coaText.indexOf(value) > -1);
            });
        });
    });

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: "Simpan Budget Umum?",
            text: "Pastikan seluruh parameter jenis biaya dan nilai alokasi sudah benar.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#0d6efd",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "<i class='fa fa-save me-1'></i> Ya, Simpan",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                var $btn = $("#btn_submit_umum");
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menyimpan data budget umum...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                var formdata = $("#frm_data").serialize();
                $.ajax({
                    url: siteurl + "budget_coa/save_data_umum",
                    dataType: "json",
                    type: 'POST',
                    data: formdata,
                    success: function(msg) {
                        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Budget Umum');
                        if (msg['save'] == true || msg['save'] == '1') {
                            Swal.fire({
                                icon: "success",
                                title: "Sukses!",
                                text: "Data budget umum berhasil disimpan.",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: "Data budget umum gagal disimpan.",
                                confirmButtonText: "Tutup"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Budget Umum');
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
        $("#card-list, .box").show();
    }
</script>
