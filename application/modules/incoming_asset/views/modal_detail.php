<div class="p-2">
    <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
        <div class="col-md-4 col-12">
            <span class="text-muted small d-block">Nomor PO / Non-PO:</span>
            <span class="fw-bold text-primary fs-6"><?= !empty($no_po) ? html_escape($no_po) : '-'; ?></span>
        </div>
        <div class="col-md-4 col-12">
            <span class="text-muted small d-block">Kode Transaksi:</span>
            <span class="fw-bold text-dark fs-6"><?= html_escape($kode_trans); ?></span>
        </div>
        <div class="col-md-4 col-12">
            <span class="text-muted small d-block">Tanggal Penerimaan:</span>
            <span class="fw-bold text-dark fs-6"><?= html_escape($resv); ?></span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover align-middle mb-0" width="100%">
            <thead class="table-primary text-center">
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Barang / Asset</th>
                    <th width="12%">Qty Diterima</th>
                    <th width="25%">Keterangan</th>
                    <th width="20%">Pemeriksa</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($result)) {
                    $No = 0;
                    foreach ($result as $val => $valx) {
                        $No++;
                        $qty_oke    = number_format($valx['qty_oke'], 2);
                        $keterangan = (!empty($valx['keterangan'])) ? ucfirst($valx['keterangan']) : '-';
                        $pemeriksa  = (!empty($valx['ket_req_po'])) ? ucfirst($valx['ket_req_po']) : ((!empty($valx['ket_req_pro'])) ? ucfirst($valx['ket_req_pro']) : '-');
                        if (isset($tanda) && $tanda == 'check' && isset($checked) && $checked == 'Y') {
                            $qty_oke    = number_format($valx['check_qty_oke'], 2);
                            $keterangan = (!empty($valx['check_keterangan'])) ? ucfirst($valx['check_keterangan']) : '-';
                            $pemeriksa  = (!empty($valx['ket_req_po'])) ? ucfirst($valx['ket_req_po']) : ((!empty($valx['ket_req_pro'])) ? ucfirst($valx['ket_req_pro']) : '-');
                        }
                ?>
                        <tr>
                            <td class="text-center"><?= $No; ?></td>
                            <td class="fw-semibold"><?= strtoupper(html_escape($valx['nm_material'])); ?></td>
                            <td class="text-center fw-bold text-success"><?= $qty_oke; ?></td>
                            <td><?= html_escape($keterangan); ?></td>
                            <td><?= html_escape($pemeriksa); ?></td>
                        </tr>
                <?php
                    }
                } else {
                ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">Tidak ada data detail penerimaan.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    if (typeof swal !== 'undefined' && swal.close) {
        swal.close();
    }
</script>
