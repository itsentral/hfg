<?php
$kode_trans = isset($kode_trans) ? $kode_trans : '-';
$no_po      = isset($no_po) ? $no_po : '-';
$resv       = isset($resv) ? $resv : '-';
$pic        = isset($pic) ? $pic : '-';
$note       = isset($note) ? $note : '-';
?>
<div class="p-2">
    <!-- Header Informasi -->
    <div class="card border mb-3 shadow-none bg-light">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-6 col-12">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="fw-bold text-muted" width="130">No Transaksi</td>
                            <td width="10">:</td>
                            <td class="fw-bold text-primary"><?= $kode_trans; ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">No PO / Non-PO</td>
                            <td>:</td>
                            <td class="fw-semibold"><?= $no_po; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 col-12">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="fw-bold text-muted" width="130">Tgl Penerimaan</td>
                            <td width="10">:</td>
                            <td><?= $resv; ?></td>
                        </tr>
                        <?php if (!empty($pic) && $pic != '-') : ?>
                        <tr>
                            <td class="fw-bold text-muted">PIC Penerima</td>
                            <td>:</td>
                            <td><?= ucfirst($pic); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($note) && $note != '-') : ?>
                        <tr>
                            <td class="fw-bold text-muted">Catatan</td>
                            <td>:</td>
                            <td><?= $note; ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Barang -->
    <div class="table-responsive border rounded mb-2">
        <table class="table table-sm table-striped table-hover align-middle mb-0" width="100%">
            <thead class="table-light text-nowrap">
                <tr>
                    <th class="text-center" width="40">#</th>
                    <th>Nama Barang / Jasa</th>
                    <th class="text-center" width="110">Qty Diterima</th>
                    <th width="200">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($result)) :
                    $No = 0;
                    $total_qty = 0;
                    foreach ($result as $val => $valx) :
                        $No++;
                        $qty_oke    = isset($valx['qty_oke']) ? (float)$valx['qty_oke'] : 0;
                        $total_qty += $qty_oke;
                        $keterangan = (!empty($valx['keterangan'])) ? ucfirst($valx['keterangan']) : '-';
                ?>
                        <tr>
                            <td class="text-center"><?= $No; ?></td>
                            <td class="fw-semibold"><?= strtoupper($valx['nm_material']); ?></td>
                            <td class="text-center fw-bold text-primary"><?= number_format($qty_oke, 2); ?></td>
                            <td><?= $keterangan; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-light fw-bold">
                        <td colspan="2" class="text-end">Total Diterima:</td>
                        <td class="text-center text-primary"><?= number_format($total_qty, 2); ?></td>
                        <td></td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">Tidak ada data barang yang diterima.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
