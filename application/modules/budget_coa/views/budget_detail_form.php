<?php
$tahun_val = isset($tahun) ? $tahun : date("Y");
$grand_total = 0;
$grand_bulan = array_fill(1, 12, 0);

if (!empty($data)) {
    foreach ($data as $record) {
        if (!empty($record->tahun)) {
            $tahun_val = $record->tahun;
        }
        $grand_total += (isset($record->total) ? (float)$record->total : 0);
        for ($bln = 1; $bln <= 12; $bln++) {
            $grand_bulan[$bln] += (isset($record->{"bulan_" . $bln}) ? (float)$record->{"bulan_" . $bln} : 0);
        }
    }
}
?>
<style>
    .table-detail-container {
        max-height: 580px;
        overflow-y: auto;
        overflow-x: auto;
    }
    .table-detail thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background-color: #f1f5f9 !important;
        box-shadow: inset 0 -1px 0 #cbd5e1;
    }
    .table-detail tfoot th {
        position: sticky;
        bottom: 0;
        z-index: 4;
        background-color: #f8fafc !important;
        box-shadow: inset 0 1px 0 #cbd5e1;
    }
</style>

<div class="card border-0 shadow-sm" id="card-detail-budget">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-info bg-opacity-10 text-info p-2 rounded-3">
                    <i class="fa fa-calendar-check fs-4"></i>
                </div>
                <div>
                    <h5 class="card-title fw-bold text-dark mb-0">
                        Detail Alokasi Budget Tahunan (<?= $tahun_val ?>)
                    </h5>
                    <small class="text-muted">Rincian alokasi budget per akun perkiraan untuk 12 periode bulan</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    <i class="fa fa-calendar me-1"></i> Tahun: <strong><?= $tahun_val ?></strong>
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancel()" title="Tutup Detail">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Quick search & Summary Cards -->
        <div class="row g-3 mb-4 align-items-center">
            <div class="col-md-4 col-sm-6">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fa fa-search"></i></span>
                    <input type="text" id="quick_filter_detail" class="form-control" placeholder="Cari kode / nama COA...">
                </div>
            </div>
            <div class="col-md-8 col-sm-6 text-md-end">
                <div class="d-inline-flex align-items-center gap-3">
                    <div class="text-end">
                        <small class="text-muted d-block">Total Alokasi Setahun</small>
                        <span class="fs-5 fw-bold text-primary font-monospace">
                            Rp <?= number_format($grand_total, 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Breakdown -->
        <div class="table-detail-container border rounded-3 mb-4">
            <table class="table table-hover table-bordered table-detail align-middle mb-0" id="table_detail_data">
                <thead>
                    <tr class="small text-uppercase fw-semibold text-secondary text-nowrap">
                        <th width="50" class="text-center">#</th>
                        <th width="220">Akun COA</th>
                        <th width="150">Penanggung Jawab</th>
                        <th width="130">Kategori</th>
                        <th width="140" class="text-end bg-primary-subtle text-primary">Budget Tahun</th>
                        <?php
                        $bulan_labels = array(
                            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                        );
                        for ($bln = 1; $bln <= 12; $bln++) {
                            echo '<th width="110" class="text-end text-nowrap">' . $bulan_labels[$bln] . '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody id="tbody_detail">
                    <?php
                    $i = 0;
                    if (!empty($data)) {
                        foreach ($data as $record) {
                            $i++;
                    ?>
                            <tr class="row-detail-item">
                                <td class="text-center text-muted small"><?= $i ?></td>
                                <td>
                                    <div class="fw-bold font-monospace text-primary detail-coa-number"><?= $record->no_perkiraan ?></div>
                                    <div class="text-secondary small detail-coa-name"><?= $record->nama_perkiraan ?></div>
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
                                <td class="text-end fw-bold font-monospace text-primary">
                                    <?= isset($record->total) ? number_format($record->total, 0, ',', '.') : '0' ?>
                                </td>
                                <?php
                                for ($bln = 1; $bln <= 12; $bln++) {
                                    $val = isset($record->{"bulan_" . $bln}) ? (float)$record->{"bulan_" . $bln} : 0;
                                    echo '<td class="text-end font-monospace ' . ($val > 0 ? 'text-dark' : 'text-muted') . ' text-nowrap">';
                                    echo number_format($val, 0, ',', '.');
                                    echo '</td>';
                                }
                                ?>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="17" class="text-center py-4 text-muted">
                                Tidak ada data budget ditemukan.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold text-nowrap">
                        <th colspan="4" class="text-end">TOTAL KESELURUHAN:</th>
                        <th class="text-end font-monospace text-primary bg-primary-subtle">
                            <?= number_format($grand_total, 0, ',', '.') ?>
                        </th>
                        <?php
                        for ($bln = 1; $bln <= 12; $bln++) {
                            echo '<th class="text-end font-monospace text-dark">';
                            echo number_format($grand_bulan[$bln], 0, ',', '.');
                            echo '</th>';
                        }
                        ?>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-end pt-3 border-top">
            <button type="button" class="btn btn-outline-secondary px-3" onclick="cancel()">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#quick_filter_detail").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tbody_detail tr.row-detail-item").filter(function() {
                var coaText = $(this).find(".detail-coa-number").text().toLowerCase() + " " + $(this).find(".detail-coa-name").text().toLowerCase();
                $(this).toggle(coaText.indexOf(value) > -1);
            });
        });
    });

    function cancel() {
        $("#form-data").hide().empty();
        $("#card-list, #card-laporan, .box").show();
    }
</script>
