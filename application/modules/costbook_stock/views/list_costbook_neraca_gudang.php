<div class="table-responsive">
    <table id="example1" class="table table-hover table-striped align-middle border rounded" style="width:100%">
        <thead class="table-light">
            <tr>
                <th class="text-center" width="4%">#</th>
                <th class="text-start" width="16%">Transaksi</th>
                <th class="text-center" width="13%">No. Transaksi</th>
                <th class="text-center" width="11%">Tgl Transaksi</th>
                <th class="text-center" width="7%">Tipe</th>
                <th class="text-center" width="12%">Dari Gudang</th>
                <th class="text-center" width="12%">Ke Gudang</th>
                <th class="text-end" width="11%">Value In</th>
                <th class="text-end" width="11%">Value Out</th>
                <th class="text-end" width="13%">Saldo</th>
            </tr>
        </thead>
        <tbody class="list_data">
            <?php 
                $saldo_awal = 0;
                if (!empty($results['list_saldo_awal'])) {
                    foreach($results['list_saldo_awal'] as $item_saldo_awal) {
                        $saldo_awal += $item_saldo_awal->value_neraca;
                    }
                }
            ?>
            <tr class="table-primary fw-bold"> 
                <td class="text-end" colspan="7"><i class="fa fa-arrow-right me-1"></i> SALDO AWAL</td>
                <td class="text-end font-monospace text-primary" colspan="3">Rp <?= number_format($saldo_awal, 2) ?></td>
            </tr>

            <?php
                $ttl_in = 0;
                $ttl_out = 0;
                $no = 1;
                
                if (!empty($results['list_data'])) :
                    foreach($results['list_data'] as $item) :
                        $value_in = 0;
                        $value_out = 0;

                        if($item->tipe_transaksi == 'In') {
                            $value_in = $item->value_transaksi;
                        } else {
                            $value_out = $item->value_transaksi;
                        }

                        $saldo_awal += $value_in;
                        $saldo_awal -= $value_out;
                        $ttl_in += $value_in;
                        $ttl_out += $value_out;

                        $badge_tipe = ($item->tipe_transaksi == 'In') ? 'success' : 'danger';
            ?>
                <tr> 
                    <td class="text-center"><?= $no ?></td>
                    <td class="text-start fw-bold text-dark"><?= $item->jenis_transaksi ?></td>
                    <td class="text-center font-monospace fw-semibold text-primary"><?= $item->no_transaksi ?></td>
                    <td class="text-center font-monospace"><?= date('d-M-Y', strtotime($item->tgl)) ?></td>
                    <td class="text-center">
                        <span class="badge bg-<?= $badge_tipe ?> bg-opacity-10 text-<?= $badge_tipe ?> border border-<?= $badge_tipe ?> border-opacity-25 px-2 py-1">
                            <?= $item->tipe_transaksi ?>
                        </span>
                    </td>
                    <td class="text-center text-muted"><?= (!empty($item->nm_gudang_dari)) ? strtoupper($item->nm_gudang_dari) : '-' ?></td>
                    <td class="text-center text-muted"><?= (!empty($item->nm_gudang_ke)) ? strtoupper($item->nm_gudang_ke) : '-' ?></td>
                    <td class="text-end font-monospace text-success"><?= ($value_in > 0) ? 'Rp ' . number_format($value_in, 2) : '-' ?></td>
                    <td class="text-end font-monospace text-danger"><?= ($value_out > 0) ? 'Rp ' . number_format($value_out, 2) : '-' ?></td>
                    <td class="text-end font-monospace fw-bold text-dark">Rp <?= number_format($saldo_awal, 2) ?></td>
                </tr>
            <?php
                        $no++;
                    endforeach;
                else :
            ?>
                <tr>
                    <td colspan="10" class="text-center py-4 text-muted">
                        <i class="fa fa-info-circle me-1"></i> Tidak ada mutasi transaksi pada periode ini.
                    </td>
                </tr>
            <?php endif; ?>

            <tr class="table-secondary fw-bold"> 
                <td class="text-end" colspan="7"><i class="fa fa-arrow-right me-1"></i> SALDO AKHIR</td>
                <td class="text-end font-monospace text-success">Rp <?= number_format($ttl_in, 2) ?></td>
                <td class="text-end font-monospace text-danger">Rp <?= number_format($ttl_out, 2) ?></td>
                <td class="text-end font-monospace text-primary fs-6">Rp <?= number_format($saldo_awal, 2) ?></td>
            </tr>
        </tbody>
    </table>
</div>
