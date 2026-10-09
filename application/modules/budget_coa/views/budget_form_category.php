<?php
$dept_name = isset($datadept[$dataset['divisi']]) ? $datadept[$dataset['divisi']] : $dataset['divisi'];
?>
<style>
    .table-category-container {
        max-height: 580px;
        overflow-y: auto;
        overflow-x: auto;
    }
    .table-category thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background-color: #f1f5f9 !important;
        box-shadow: inset 0 -1px 0 #cbd5e1;
    }
    .table-category tfoot th {
        position: sticky;
        bottom: 0;
        z-index: 4;
        background-color: #f8fafc !important;
        box-shadow: inset 0 1px 0 #cbd5e1;
    }
</style>

<div class="card border-0 shadow-sm" id="card-form-category">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                    <i class="fa fa-calendar-alt fs-4"></i>
                </div>
                <div>
                    <h5 class="card-title fw-bold text-dark mb-0">
                        Input Detail Budget <?= $dataset['kategori'] ?>
                    </h5>
                    <small class="text-muted">
                        Alokasi bulanan budget untuk Departemen: <strong><?= $dept_name ?></strong>, Tahun: <strong><?= $dataset['tahun'] ?></strong>
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    <?= $dataset['kategori'] ?>
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
        <div class="table-category-container border rounded-3 mb-3">
            <table class="table table-hover table-bordered table-category align-middle mb-0" id="table_input_category">
                <thead>
                    <tr class="small text-uppercase fw-semibold text-secondary text-nowrap">
                        <th width="40" class="text-center">#</th>
                        <th width="220">Akun COA</th>
                        <th width="140" class="text-end">Referensi</th>
                        <th width="140" class="text-end bg-primary-subtle text-primary">Total Alokasi</th>
                        <?php
                        $bulan_labels = array(
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        );
                        for ($bln = 1; $bln <= 12; $bln++) {
                            echo '<th width="120" class="text-end text-nowrap">' . $bulan_labels[$bln] . '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody id="tbody_category">
                    <?php
                    $i = 0;
                    $grand_ref = 0;
                    $grand_tot = 0;
                    $grand_bln = array_fill(1, 12, 0);

                    if (!empty($data)) {
                        foreach ($data as $record) {
                            $i++;
                            $ref_val = isset($record->finance_tahun) ? (float)$record->finance_tahun : 0;
                            $tot_val = isset($record->total) ? (float)$record->total : 0;
                            $grand_ref += $ref_val;
                            $grand_tot += $tot_val;
                    ?>
                            <tr class="row-category-item" id="row_coa_<?= $i ?>">
                                <td class="text-center text-muted small"><?= $i ?></td>
                                <td>
                                    <input type="hidden" name="id[]" value="<?= (isset($record->id) ? $record->id : '') ?>">
                                    <input type="hidden" name="coa[]" value="<?= $record->no_perkiraan ?>">
                                    <div class="fw-bold font-monospace text-primary coa-number"><?= $record->no_perkiraan ?></div>
                                    <div class="text-secondary small coa-name"><?= $record->nama_perkiraan ?></div>
                                </td>
                                <td class="text-end font-monospace">
                                    <input type="hidden" id="finance_tahun_<?= $i ?>" value="<?= $ref_val ?>">
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <?= number_format($ref_val, 0, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <input type="text" id="total_<?= $i ?>" name="total[]" value="<?= number_format($tot_val, 0, ',', '.') ?>" class="form-control form-control-sm text-end fw-bold font-monospace text-primary bg-light" readonly tabindex="-1">
                                </td>
                                <?php
                                for ($bln = 1; $bln <= 12; $bln++) {
                                    $b_val = isset($record->{"bulan_" . $bln}) ? (float)$record->{"bulan_" . $bln} : 0;
                                    $grand_bln[$bln] += $b_val;
                                    echo '<td>';
                                    echo '<input type="text" class="form-control form-control-sm text-end font-monospace divide col-bulan bulan-' . $bln . '" data-row="' . $i . '" data-month="' . $bln . '" id="bulan_' . $bln . '_' . $i . '" name="bulan_' . $bln . '[]" value="' . number_format($b_val, 0, ',', '.') . '" placeholder="0">';
                                    echo '</td>';
                                }
                                ?>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="16" class="text-center py-4 text-muted">
                                Tidak ada data budget ditemukan untuk parameter ini.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold text-nowrap">
                        <th colspan="2" class="text-end">TOTAL KESELURUHAN:</th>
                        <th class="text-end font-monospace" id="grand_ref_display">
                            <?= number_format($grand_ref, 0, ',', '.') ?>
                        </th>
                        <th class="text-end font-monospace text-primary bg-primary-subtle" id="grand_tot_display">
                            <?= number_format($grand_tot, 0, ',', '.') ?>
                        </th>
                        <?php
                        for ($bln = 1; $bln <= 12; $bln++) {
                            echo '<th class="text-end font-monospace" id="grand_bln_' . $bln . '_display">';
                            echo number_format($grand_bln[$bln], 0, ',', '.');
                            echo '</th>';
                        }
                        ?>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
            <div class="text-muted small">
                <i class="fa fa-info-circle text-primary me-1"></i> Nilai alokasi per bulan akan otomatis mengakumulasi Total Alokasi baris COA dan Grand Total.
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary px-3" onclick="cancel()">
                    <i class="fa fa-arrow-left me-1"></i> Batal
                </button>
                <button type="submit" name="save" class="btn btn-primary px-4 shadow-sm" id="btn_submit_category">
                    <i class="fa fa-save me-1"></i> Simpan Alokasi Budget
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

    function calculateRow(row) {
        var sum = 0;
        for (var b = 1; b <= 12; b++) {
            var val = parseNum($("#bulan_" + b + "_" + row).val());
            sum += val;
        }
        $("#total_" + row).val(formatNumber(sum));

        var ref = parseFloat($("#finance_tahun_" + row).val()) || 0;
        if (ref > 0 && sum > ref) {
            $("#total_" + row).addClass("text-danger").removeClass("text-primary");
            $("#row_coa_" + row).addClass("table-warning");
        } else {
            $("#total_" + row).removeClass("text-danger").addClass("text-primary");
            $("#row_coa_" + row).removeClass("table-warning");
        }
        recalcGrandTotals();
    }

    function recalcGrandTotals() {
        var grandTot = 0;
        var grandBln = {};
        for (var b = 1; b <= 12; b++) {
            grandBln[b] = 0;
        }

        $("#tbody_category tr.row-category-item").each(function() {
            var r = $(this).attr("id").replace("row_coa_", "");
            var rTot = parseNum($("#total_" + r).val());
            grandTot += rTot;
            for (var b = 1; b <= 12; b++) {
                grandBln[b] += parseNum($("#bulan_" + b + "_" + r).val());
            }
        });

        $("#grand_tot_display").text(formatNumber(grandTot));
        for (var b = 1; b <= 12; b++) {
            $("#grand_bln_" + b + "_display").text(formatNumber(grandBln[b]));
        }
    }

    $(document).ready(function() {
        if ($.fn.divide) {
            $(".divide").divide({
                delimiter: '.',
                divideFor: 'all'
            });
        }

        // Realtime calculation on month input
        $(document).on("input blur change", ".col-bulan", function() {
            var row = $(this).data("row");
            calculateRow(row);
        });

        // Quick filter for COA
        $("#quick_filter_coa").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tbody_category tr.row-category-item").filter(function() {
                var coaText = $(this).find(".coa-number").text().toLowerCase() + " " + $(this).find(".coa-name").text().toLowerCase();
                $(this).toggle(coaText.indexOf(value) > -1);
            });
        });
    });

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();

        // Check if any row exceeds reference
        var exceeds = false;
        $("#tbody_category tr.row-category-item").each(function() {
            var r = $(this).attr("id").replace("row_coa_", "");
            var sum = parseNum($("#total_" + r).val());
            var ref = parseFloat($("#finance_tahun_" + r).val()) || 0;
            if (ref > 0 && sum > ref) {
                exceeds = true;
            }
        });

        var confirmText = "Pastikan seluruh alokasi bulanan sudah sesuai.";
        if (exceeds) {
            confirmText = "Perhatian: Ada baris alokasi budget yang melebihi nilai referensi tahunan! Tetap simpan?";
        }

        Swal.fire({
            title: "Simpan Alokasi Budget?",
            text: confirmText,
            icon: exceeds ? "warning" : "question",
            showCancelButton: true,
            confirmButtonColor: exceeds ? "#f59e0b" : "#0d6efd",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "<i class='fa fa-save me-1'></i> Ya, Simpan",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                var $btn = $("#btn_submit_category");
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menyimpan alokasi budget...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                var formdata = $("#frm_data").serialize();
                $.ajax({
                    url: siteurl + "budget_coa/save_data_category",
                    dataType: "json",
                    type: 'POST',
                    data: formdata,
                    success: function(msg) {
                        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Alokasi Budget');
                        if (msg['save'] == true || msg['save'] == '1') {
                            Swal.fire({
                                icon: "success",
                                title: "Sukses!",
                                text: "Data alokasi budget berhasil disimpan.",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: "Data alokasi budget gagal disimpan.",
                                confirmButtonText: "Tutup"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Alokasi Budget');
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
