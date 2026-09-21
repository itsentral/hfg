<?php
$no_pr      = isset($no_pr) ? $no_pr : '';
$tgl_pr     = isset($result_header['tgl_pr']) ? date('d M Y', strtotime($result_header['tgl_pr'])) : '-';
$created_by = isset($result_header['created_by']) ? $result_header['created_by'] : '-';
$created_date = isset($result_header['created_date']) ? date('d M Y H:i', strtotime($result_header['created_date'])) : '-';
?>
<div class="card border-0 shadow-none">
    <div class="card-body p-2">
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="35%">No PR</th>
                        <td width="5%">:</td>
                        <td><span class="fw-bold text-primary"><?= $no_pr ?></span></td>
                    </tr>
                    <tr>
                        <th>Tanggal PR</th>
                        <td>:</td>
                        <td><?= $tgl_pr ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="35%">Created By</th>
                        <td width="5%">:</td>
                        <td><?= strtoupper($created_by) ?></td>
                    </tr>
                    <tr>
                        <th>Created Date</th>
                        <td>:</td>
                        <td><?= $created_date ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="table-responsive mt-2">
            <table class="table table-bordered table-striped align-middle w-100">
                <thead class="table-primary text-center">
                    <tr>
                        <th width="5%">#</th>
                        <th>Nama Barang / Asset</th>
                        <th width="10%">Qty</th>
                        <th width="18%" class="text-end">Nilai PR</th>
                        <th width="15%">Tgl Dibutuhkan</th>
                        <th width="15%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($result)) {
                        $no = 1;
                        foreach ($result as $row) {
                            $app_status = isset($row['app_status']) ? $row['app_status'] : 'N';
                            if ($app_status == 'Y') {
                                $status = 'APPROVED';
                                $color  = 'success';
                            } else if ($app_status == 'D') {
                                $status = 'REJECTED';
                                $color  = 'danger';
                            } else {
                                $status = 'WAITING APPROVAL';
                                $color  = 'warning';
                            }

                            echo "<tr>";
                            echo "<td class='text-center'>" . $no++ . "</td>";
                            echo "<td>" . strtoupper($row['nm_barang']) . "</td>";
                            echo "<td class='text-center'>" . number_format($row['qty']) . "</td>";
                            echo "<td class='text-end'>" . number_format($row['nilai_pr']) . "</td>";
                            echo "<td class='text-center'>" . (!empty($row['tgl_butuh']) ? date('d M Y', strtotime($row['tgl_butuh'])) : '-') . "</td>";
                            echo "<td class='text-center'><span class='badge bg-" . $color . "'>" . $status . "</span></td>";
                            echo "</tr>";

                            if (!empty($row['app_reason'])) {
                                echo "<tr><td colspan='6' class='text-danger bg-light'><small><b>Reason:</b> " . htmlspecialchars($row['app_reason']) . "</small></td></tr>";
                            }
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-muted'>Tidak ada detail barang.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
