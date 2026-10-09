<form action="#" method="POST" id="form_adjustment" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="no_po" id="no_po" value="<?= html_escape($no_po); ?>">
    <input type="hidden" name="id_dept" id="id_dept" value="<?= html_escape($id_dept); ?>">
    <input type="hidden" name="id_costcenter" id="id_costcenter" value="<?= html_escape($id_costcenter); ?>">
    <input type="hidden" name="tanggal" id="tanggal" value="<?= html_escape($tanggal_trans); ?>">
    <input type="hidden" name="pic" id="pic" value="<?= html_escape($pic); ?>">
    <input type="hidden" name="note" id="note" value="<?= html_escape($note); ?>">
    <input type="hidden" name="adjustment" id="adjustment" value="IN">

    <div class="row g-3 mb-3 bg-light p-3 rounded-3 border">
        <div class="col-md-4 col-12">
            <label class="form-label fw-bold small text-secondary">Nomor PO / NON-PO</label>
            <div class="form-control-plaintext fw-bold text-primary"><?= html_escape($no_po); ?></div>
        </div>
        <div class="col-md-4 col-12">
            <label class="form-label fw-bold small text-secondary">PIC Penerima</label>
            <div class="form-control-plaintext"><?= strtoupper(html_escape($pic)); ?></div>
        </div>
        <div class="col-md-4 col-12">
            <label class="form-label fw-bold small text-secondary">Upload Dokumen (BA/Surat Jalan/Foto)</label>
            <input type="file" name="upload_doc" class="form-control form-control-sm">
        </div>
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-bordered table-striped table-hover align-middle mb-0" width="100%">
            <thead class="table-primary text-center">
                <tr>
                    <th width="4%">#</th>
                    <th width="15%">Departemen</th>
                    <th>Nama Barang / Asset</th>
                    <th width="8%">Qty Order</th>
                    <th width="8%">Satuan</th>
                    <th width="10%">Sisa Belum Kirim</th>
                    <th width="12%">Qty Diterima</th>
                    <th width="15%">Catatan</th>
                    <th width="15%">Pemeriksa</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($result)) {
                    $No = 0;
                    $cek_type = substr($no_po, 0, 3);
                    foreach ($result as $val => $valx) {
                        $No++;

                        if ($cek_type == 'NPO') {
                            $qty = isset($valx['qty_rev']) ? (float)$valx['qty_rev'] : 0;
                            $satuan = 'UNIT';
                            $dept = function_exists('get_name') ? get_name('department', 'nm_dept', 'id', $valx['id_dept']) : '';
                            $no_pox = isset($valx['no_non_po']) ? $valx['no_non_po'] : $no_po;
                        } else {
                            $qty = isset($valx['qty_po']) ? (float)$valx['qty_po'] : 0;
                            $satuan = 'UNIT';
                            $dept = '-';
                            $no_pox = isset($valx['no_po']) ? $valx['no_po'] : $no_po;
                        }

                        $qty_in_current = isset($valx['qty_in']) ? (float)$valx['qty_in'] : 0;
                        $Qty_kurang = $qty - $qty_in_current;
                        if ($Qty_kurang < 0) $Qty_kurang = 0;
                ?>
                        <tr>
                            <td class="text-center">
                                <?= $No; ?>
                                <input type="hidden" name="addInMat[<?= $No; ?>][no_po]" value="<?= html_escape($no_pox); ?>">
                                <input type="hidden" name="addInMat[<?= $No; ?>][nm_barang]" value="<?= html_escape($valx['nm_barang']); ?>">
                                <input type="hidden" name="addInMat[<?= $No; ?>][spec]" value="<?= html_escape($valx['id_barang']); ?>">
                                <input type="hidden" name="addInMat[<?= $No; ?>][id]" value="<?= html_escape($valx['id']); ?>">
                                <input type="hidden" name="addInMat[<?= $No; ?>][qty_rev]" value="<?= $qty; ?>">
                            </td>
                            <td><?= strtoupper($dept); ?></td>
                            <td class="fw-bold"><?= strtoupper(html_escape($valx['nm_barang'])); ?></td>
                            <td class="text-center"><?= number_format($qty, 2); ?></td>
                            <td class="text-center"><?= strtoupper($satuan); ?></td>
                            <td class="text-center text-danger fw-bold"><?= number_format($Qty_kurang, 2); ?></td>
                            <td>
                                <input type="number" step="any" min="0" max="<?= $Qty_kurang; ?>" name="addInMat[<?= $No; ?>][qty_in]" class="form-control form-control-sm text-center fw-bold" placeholder="0" value="<?= $Qty_kurang; ?>">
                            </td>
                            <td>
                                <input type="text" name="addInMat[<?= $No; ?>][keterangan]" class="form-control form-control-sm" placeholder="Catatan kondisi...">
                            </td>
                            <td>
                                <input type="text" name="addInMat[<?= $No; ?>][pemeriksa]" class="form-control form-control-sm" placeholder="Nama pemeriksa...">
                            </td>
                        </tr>
                <?php
                    }
                } else {
                ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-info-circle me-1"></i> Tidak ada barang yang menunggu penerimaan atau seluruh barang sudah diterima lengkap.
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
        <?php if (!empty($result)) : ?>
            <button type="button" class="btn btn-success px-4 shadow-sm" id="saveINMaterial">
                <i class="fa fa-save me-1"></i> Simpan Penerimaan
            </button>
        <?php endif; ?>
    </div>
</form>

<script>
    if (typeof swal !== 'undefined' && swal.close) {
        swal.close();
    }
</script>
