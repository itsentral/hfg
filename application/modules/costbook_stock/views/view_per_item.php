<?php
$nm_material = (!empty($results['nm_material'])) ? $results['nm_material'] : '-';
$nm_gudang   = (!empty($results['nm_gudang'])) ? $results['nm_gudang'] : '-';
?>

<div class="card border bg-light bg-opacity-25 mb-3">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-6 col-12">
                <span class="text-muted d-block small">Item Name:</span>
                <span class="fw-bold fs-6 text-dark"><i class="fa fa-box text-primary me-1"></i> <?= strtoupper((string) $nm_material) ?></span>
            </div>
            <div class="col-md-6 col-12 text-md-end">
                <span class="text-muted d-block small">Storage Warehouse:</span>
                <span class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-1 fs-6"><i class="fa fa-warehouse text-primary me-1"></i> <?= strtoupper((string) $nm_gudang) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle table-sm" id="table_detail_trans" style="width:100%; font-size: 0.88rem;">
        <thead class="table-primary text-center">
            <tr>
                <th width="11%">Date</th>
                <th class="text-start">Transaction Type</th>
                <th width="11%" class="text-end">Transaction Qty</th>
                <th width="10%" class="text-end">Qty Akhir</th>
                <th width="12%" class="text-end">In Value</th>
                <th width="12%" class="text-end">CostBook</th>
                <th width="13%" class="text-end">Transaction Value</th>
                <th width="13%" class="text-end">Value Neraca</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($results['list_data'])) :
                foreach ($results['list_data'] as $item) :
                    $badge = 'secondary';
                    if (stripos((string) $item->jenis_transaksi, 'Incoming') !== false || stripos((string) $item->jenis_transaksi, 'plus') !== false) {
                        $badge = 'success';
                    } elseif (stripos((string) $item->jenis_transaksi, 'Outgoing') !== false || stripos((string) $item->jenis_transaksi, 'minus') !== false) {
                        $badge = 'danger';
                    } elseif (stripos((string) $item->jenis_transaksi, 'Saldo') !== false) {
                        $badge = 'primary';
                    }
            ?>
                <tr>
                    <td class="text-center font-monospace"><?= date('d-M-Y', strtotime((string) $item->tgl)) ?></td>
                    <td class="text-start">
                        <span class="badge bg-<?= $badge ?> bg-opacity-10 text-<?= $badge ?> border border-<?= $badge ?> border-opacity-25 px-2 py-1">
                            <?= $item->jenis_transaksi ?>
                        </span>
                    </td>
                    <td class="text-end font-monospace fw-semibold"><?= number_format((float) $item->qty_transaksi, 2) ?></td>
                    <td class="text-end font-monospace"><?= number_format((float) $item->qty, 2) ?></td>
                    <td class="text-end font-monospace">Rp <?= number_format((float) $item->nilai_beli, 2) ?></td>
                    <td class="text-end font-monospace fw-bold text-success">Rp <?= number_format((float) $item->costbook, 2) ?></td>
                    <td class="text-end font-monospace">Rp <?= number_format((float) $item->value_transaksi, 2) ?></td>
                    <td class="text-end font-monospace fw-bold text-primary">Rp <?= number_format((float) $item->value_neraca, 2) ?></td>
                </tr>
            <?php 
                endforeach;
            else :
            ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fa fa-info-circle me-1"></i> Belum ada riwayat transaksi costbook untuk barang ini pada gudang terpilih.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
