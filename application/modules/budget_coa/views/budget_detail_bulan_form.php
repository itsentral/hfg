<?php
$bulan_names = array(
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
);
$nama_bulan = isset($bulan_names[(int)$bulan]) ? $bulan_names[(int)$bulan] : 'Bulan ' . $bulan;

$tot_bulan = 0;
$tot_ytd = 0;
$tot_tahun = 0;

if (!empty($data)) {
    foreach ($data as $record) {
        $tot_bulan += (isset($record->{"bulan_" . $bulan}) ? (float)$record->{"bulan_" . $bulan} : 0);
        $ytd = 0;
        for ($bln = 1; $bln <= $bulan; $bln++) {
            $ytd += (isset($record->{"bulan_" . $bln}) ? (float)$record->{"bulan_" . $bln} : 0);
        }
        $tot_ytd += $ytd;
        $tot_tahun += (isset($record->total) ? (float)$record->total : 0);
    }
}
?>
<style>
    .table-bulan-container {
        max-height: 580px;
        overflow-y: auto;
    }
    .table-bulan thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background-color: #f1f5f9 !important;
        box-shadow: inset 0 -1px 0 #cbd5e1;
    }
    .table-bulan tfoot th {
        position: sticky;
        bottom: 0;
        z-index: 4;
        background-color: #f8fafc !important;
        box-shadow: inset 0 1px 0 #cbd5e1;
    }
</style>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                    <i class="fa fa-file-invoice-dollar fs-4"></i>
                </div>
                <div>
                    <h5 class="card-title fw-bold text-dark mb-0">
                        Laporan Realisasi & Alokasi Budget COA
                    </h5>
                    <small class="text-muted">
                        Periode: <strong><?= $nama_bulan ?> <?= $tahun ?></strong> (Termasuk Perbandingan Year To Date / YTD)
                    </small>
                </div>
            </div>
            <div>
                <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('budget_coa?tahun=' . $tahun) ?>">
                    <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar Budget
                </a>
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-sm-6">
                <div class="card bg-light border p-3 rounded-3">
                    <small class="text-muted d-block fw-semibold">Alokasi Bulan <?= $nama_bulan ?></small>
                    <div class="fs-5 fw-bold text-dark font-monospace mt-1">
                        Rp <?= number_format($tot_bulan, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card bg-primary-subtle border-primary-subtle p-3 rounded-3">
                    <small class="text-primary d-block fw-semibold">Year To Date (YTD) s/d <?= $nama_bulan ?></small>
                    <div class="fs-5 fw-bold text-primary font-monospace mt-1">
                        Rp <?= number_format($tot_ytd, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="card bg-success-subtle border-success-subtle p-3 rounded-3">
                    <small class="text-success d-block fw-semibold">Total Alokasi Setahun (<?= $tahun ?>)</small>
                    <div class="fs-5 fw-bold text-success font-monospace mt-1">
                        Rp <?= number_format($tot_tahun, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3 align-items-center">
            <div class="col-md-4 col-sm-6">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fa fa-search"></i></span>
                    <input type="text" id="quick_filter_bulan" class="form-control" placeholder="Cari kode / nama COA...">
                </div>
            </div>
        </div>

        <div class="table-bulan-container border rounded-3 mb-4">
            <table class="table table-hover table-bordered table-bulan align-middle mb-0" id="table_laporan_bulan">
                <thead>
                    <tr class="small text-uppercase fw-semibold text-secondary text-nowrap">
                        <th width="50" class="text-center">#</th>
                        <th width="160">No Perkiraan</th>
                        <th>Nama Perkiraan</th>
                        <th width="180" class="text-end">Budget Bulan <?= $nama_bulan ?></th>
                        <th width="200" class="text-end bg-primary-subtle text-primary">Year To Date (YTD)</th>
                        <th width="200" class="text-end bg-success-subtle text-success">Budget Tahun <?= $tahun ?></th>
                    </tr>
                </thead>
                <tbody id="tbody_bulan">
                    <?php
                    $i = 0;
                    if (!empty($data)) {
                        foreach ($data as $record) {
                            $i++;
                            $b_val = isset($record->{"bulan_" . $bulan}) ? (float)$record->{"bulan_" . $bulan} : 0;
                            $ytd_val = 0;
                            for ($bln = 1; $bln <= $bulan; $bln++) {
                                $ytd_val += isset($record->{"bulan_" . $bln}) ? (float)$record->{"bulan_" . $bln} : 0;
                            }
                            $tot_val = isset($record->total) ? (float)$record->total : 0;
                    ?>
                            <tr class="row-bulan-item">
                                <td class="text-center text-muted small"><?= $i ?></td>
                                <td class="fw-bold font-monospace text-primary bulan-coa-number">
                                    <?= $record->no_perkiraan ?>
                                </td>
                                <td class="bulan-coa-name">
                                    <?= $record->nama_perkiraan ?>
                                </td>
                                <td class="text-end font-monospace <?= ($b_val > 0 ? 'text-dark' : 'text-muted') ?>">
                                    <?= number_format($b_val, 0, ',', '.') ?>
                                </td>
                                <td class="text-end font-monospace fw-bold text-primary">
                                    <?= number_format($ytd_val, 0, ',', '.') ?>
                                </td>
                                <td class="text-end font-monospace fw-bold text-success">
                                    <?= number_format($tot_val, 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Tidak ada data budget ditemukan.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold text-nowrap">
                        <th colspan="3" class="text-end">TOTAL KESELURUHAN:</th>
                        <th class="text-end font-monospace text-dark">
                            <?= number_format($tot_bulan, 0, ',', '.') ?>
                        </th>
                        <th class="text-end font-monospace text-primary bg-primary-subtle">
                            <?= number_format($tot_ytd, 0, ',', '.') ?>
                        </th>
                        <th class="text-end font-monospace text-success bg-success-subtle">
                            <?= number_format($tot_tahun, 0, ',', '.') ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-end pt-3 border-top">
            <a class="btn btn-outline-secondary px-3" href="<?= base_url('budget_coa?tahun=' . $tahun) ?>">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar Budget
            </a>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#quick_filter_bulan").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tbody_bulan tr.row-bulan-item").filter(function() {
                var coaText = $(this).find(".bulan-coa-number").text().toLowerCase() + " " + $(this).find(".bulan-coa-name").text().toLowerCase();
                $(this).toggle(coaText.indexOf(value) > -1);
            });
        });
    });
</script>
