<?php
$id_dept = [];
if (!empty($result)) {
    foreach ($result as $val => $valx) {
        if (!empty($valx['id_dept']) && isset($list_departments[$valx['id_dept']]['id_dept'])) {
            $id_dept[] = $list_departments[$valx['id_dept']]['id_dept'];
        }
    }
}
$id_dept = array_unique($id_dept);
$id_dept = implode(',', $id_dept);
?>
<form action="#" method="POST" id="form_adjustment" enctype="multipart/form-data" autocomplete="off">
    <div class="p-2">
        <input type="hidden" name="no_po" id="no_po" value="<?= implode(',', (array)$no_po); ?>">
        <input type="hidden" name="tanggal" id="tanggal" value="<?= $tanggal_trans; ?>">
        <input type="hidden" name="id_dept" id="id_dept" value="<?= $id_dept; ?>">
        <input type="hidden" name="pic" id="pic" value="<?= $pic; ?>">
        <input type="hidden" name="note" id="note" value="<?= $note; ?>">
        <input type="hidden" name="adjustment" id="adjustment" value="IN">

        <div class="row align-items-center mb-3">
            <label class="col-sm-2 col-form-label fw-bold">Upload Dokumen</label>
            <div class="col-sm-5">
                <input type="file" name="upload_doc" class="form-control form-control-sm">
                <small class="text-muted">Opsional (Surat Jalan / Bukti Terima)</small>
            </div>
        </div>

        <div class="table-responsive border rounded mb-3">
            <table class="table table-sm table-striped table-hover align-middle mb-0" id="table-incoming-items" width="100%">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th class="text-center" width="40">#</th>
                        <th width="160">Departemen</th>
                        <th>Nama Barang / Jasa</th>
                        <th>Spec / Requirement</th>
                        <th class="text-center" width="100">Qty PO</th>
                        <th class="text-center" width="110">Belum Dikirim</th>
                        <th class="text-center" width="120">Qty Diterima <span class="text-danger">*</span></th>
                        <th width="180">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($result)) :
                        $No = 0;
                        foreach ($result as $val => $valx) :
                            $No++;

                            $incoming_qty = 0;
                            $this->db->select('IF(SUM(a.qty_oke) IS NULL, 0, SUM(a.qty_oke)) as qty_incoming');
                            $this->db->from('warehouse_adjustment_detail a');
                            $this->db->join('warehouse_adjustment b', 'b.kode_trans = a.kode_trans');
                            $this->db->where('a.id_material', $valx['id']);
                            $this->db->where('b.no_ipp', $valx['no_po']);
                            $get_incoming_qty = $this->db->get()->row_array();
                            if (!empty($get_incoming_qty)) {
                                $incoming_qty = $get_incoming_qty['qty_incoming'];
                            }

                            $nm_dept = (!empty($valx['id_dept']) && !empty($list_departments[$valx['id_dept']]['nm_dept']))
                                ? strtoupper($list_departments[$valx['id_dept']]['nm_dept'])
                                : '-';

                            $Qty_kurang = ($valx['qty'] - $incoming_qty);
                            if ($Qty_kurang < 0) {
                                $Qty_kurang = 0;
                            }

                            $readonly = ($Qty_kurang <= 0) ? 'readonly' : '';
                    ?>
                            <tr>
                                <td class="text-center">
                                    <?= $No; ?>
                                    <input type="hidden" name="addInMat[<?= $No; ?>][no_po]" value="<?= $valx['no_po']; ?>">
                                    <input type="hidden" name="addInMat[<?= $No; ?>][nm_barang]" value="<?= htmlspecialchars($valx['namamaterial']); ?>">
                                    <input type="hidden" name="addInMat[<?= $No; ?>][spec]" value="<?= htmlspecialchars($valx['spec']); ?>">
                                    <input type="hidden" name="addInMat[<?= $No; ?>][id]" value="<?= $valx['id']; ?>">
                                    <input type="hidden" name="addInMat[<?= $No; ?>][qty_rev]" value="<?= $valx['qty']; ?>">
                                    <input type="hidden" name="addInMat[<?= $No; ?>][tipe_po]" value="<?= $valx['tipe_po']; ?>">
                                    <input type="hidden" class="max_qty_<?= $valx['id']; ?>" value="<?= $Qty_kurang; ?>">
                                </td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-dark"><?= $nm_dept; ?></span></td>
                                <td class="fw-semibold"><?= strtoupper($valx['namamaterial']); ?></td>
                                <td><small class="text-muted"><?= strtoupper($valx['spec']); ?></small></td>
                                <td class="text-center"><?= number_format($valx['qty'], 2); ?></td>
                                <td class="text-center belumDiterima fw-bold <?= ($Qty_kurang > 0) ? 'text-primary' : 'text-muted'; ?>">
                                    <?= number_format($Qty_kurang, 2); ?>
                                </td>
                                <td class="text-center">
                                    <input type="text"
                                           name="addInMat[<?= $No; ?>][qty_in]"
                                           data-no="<?= $No; ?>"
                                           class="form-control form-control-sm text-center maskM qtyDiterima"
                                           data-id="<?= $valx['id']; ?>"
                                           placeholder="0"
                                           value="<?= ($Qty_kurang > 0) ? $Qty_kurang : 0; ?>"
                                           <?= $readonly; ?>>
                                </td>
                                <td>
                                    <input type="text"
                                           name="addInMat[<?= $No; ?>][keterangan]"
                                           data-no="<?= $No; ?>"
                                           class="form-control form-control-sm"
                                           placeholder="Keterangan..."
                                           <?= $readonly; ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Data aktual belum tersedia atau sudah diterima penuh.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-2">
            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-sm btn-success px-4 shadow-sm" id="saveINMaterial">
                <i class="fa fa-save me-1"></i> Simpan Penerimaan
            </button>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        if (typeof swal !== 'undefined' && swal.close) {
            swal.close();
        }
        if (typeof Swal !== 'undefined' && Swal.close) {
            Swal.close();
        }
        $('.maskM').autoNumeric('init', {
            mDec: '2',
            aPad: false
        });
    });

    $(document).on('keyup change', '.qtyDiterima', function() {
        var tr = $(this).closest('tr');
        var belumText = tr.find('.belumDiterima').text().replace(/,/g, '').trim();
        var belumDiterima = parseFloat(belumText) || 0;
        var valText = $(this).val().replace(/,/g, '').trim();
        var qtyDiterima = parseFloat(valText) || 0;

        if (qtyDiterima > belumDiterima) {
            $(this).val(belumDiterima);
        }
    });
</script>
