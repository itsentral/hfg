<?php
$kode_trans = isset($getData[0]['kode_trans']) ? $getData[0]['kode_trans'] : '-';
$tanggal    = isset($getData[0]['tanggal']) ? $getData[0]['tanggal'] : date('Y-m-d');
$pic        = isset($getData[0]['pic']) ? $getData[0]['pic'] : '-';
$nm_dept    = isset($nm_dept) ? $nm_dept : '-';
?>
<div class="p-2">
    <input type="hidden" name="kode_trans" id="kode_trans" value="<?= $kode_trans; ?>">

    <!-- Info Transaksi Card -->
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
                            <td class="fw-bold text-muted">Departemen</td>
                            <td>:</td>
                            <td class="fw-semibold"><?= $nm_dept; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 col-12">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="fw-bold text-muted" width="130">Tgl Outgoing</td>
                            <td width="10">:</td>
                            <td><?= date('d F Y', strtotime($tanggal)); ?></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">PIC Pemohon</td>
                            <td>:</td>
                            <td><?= ucfirst($pic); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Barang -->
    <div class="table-responsive border rounded mb-2">
        <table class="table table-sm table-striped table-hover align-middle mb-0" width="100%">
            <thead class="table-light text-nowrap">
                <tr>
                    <th class="text-center" width="40">#</th>
                    <th width="120">Kode</th>
                    <th>Nama Barang Stok</th>
                    <th class="text-center" width="100">Qty Order</th>
                    <th class="text-center" width="80">Unit</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($getDataDetail)) :
                    $No = 0;
                    $total_qty = 0;
                    foreach ($getDataDetail as $key => $value) :
                        $No++;
                        $id_material    = $value['id_material'];
                        $nm_material    = (!empty($GET_MATERIAL[$id_material]['nama'])) ? $GET_MATERIAL[$id_material]['nama'] : '-';
                        $code_material  = (!empty($GET_MATERIAL[$id_material]['code'])) ? $GET_MATERIAL[$id_material]['code'] : '-';
                        $id_unit        = (!empty($GET_MATERIAL[$id_material]['id_unit'])) ? $GET_MATERIAL[$id_material]['id_unit'] : 0;
                        $unit           = (!empty($GET_SATUAN[$id_unit]['code'])) ? $GET_SATUAN[$id_unit]['code'] : '-';
                        $berat_req      = (float)$value['qty_order'];
                        $total_qty     += $berat_req;
                ?>
                        <tr>
                            <td class="text-center"><?= $No; ?></td>
                            <td class="fw-semibold text-primary"><?= strtoupper($code_material); ?></td>
                            <td class="fw-bold"><?= strtoupper($nm_material); ?></td>
                            <td class="text-center fw-bold text-primary"><?= number_format($berat_req, 2); ?></td>
                            <td class="text-center"><span class="badge bg-light text-dark border"><?= strtoupper($unit); ?></span></td>
                            <td><?= (!empty($value['keterangan'])) ? htmlspecialchars($value['keterangan']) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">Total Barang Dikeluarkan:</td>
                        <td class="text-center text-primary"><?= number_format($total_qty, 2); ?></td>
                        <td colspan="2"></td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">Tidak ada data rincian barang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
