<div class="modal-detail-wrapper">
    <input type="hidden" name="kode_trans" id="kode_trans" value="<?= $getData[0]['kode_trans']; ?>">

    <!-- Ringkasan Transaksi Header Card -->
    <div class="card border border-light-subtle shadow-none bg-light bg-opacity-50 mb-3">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-6 col-12">
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted text-uppercase fw-semibold" style="width: 140px; font-size: 0.8rem;">No Transaksi</span>
                        <span class="fw-bold text-primary font-monospace">: <?= $getData[0]['kode_trans']; ?></span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted text-uppercase fw-semibold" style="width: 140px; font-size: 0.8rem;">Nomor PO / Kasbon</span>
                        <span class="fw-bold text-dark">: <?= strtoupper($no_po); ?></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-muted text-uppercase fw-semibold" style="width: 140px; font-size: 0.8rem;">Warehouse</span>
                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">: <?= strtoupper(get_name('warehouse', 'nm_gudang', 'id', $getData[0]['id_gudang_ke'])); ?></span>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted text-uppercase fw-semibold" style="width: 140px; font-size: 0.8rem;">Tanggal Incoming</span>
                        <span class="fw-semibold text-dark">: <?= tgl_indo($getData[0]['tanggal']); ?></span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted text-uppercase fw-semibold" style="width: 140px; font-size: 0.8rem;">PIC Penerima</span>
                        <span class="fw-semibold text-dark">: <?= !empty($getData[0]['pic']) ? strtoupper($getData[0]['pic']) : '-'; ?></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-muted text-uppercase fw-semibold" style="width: 140px; font-size: 0.8rem;">Catatan</span>
                        <span class="text-dark fst-italic">: <?= !empty($getData[0]['note']) ? $getData[0]['note'] : '-'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Barang -->
    <div class="table-responsive border rounded mb-3">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center" width="5%">#</th>
                    <th width="15%">Kode Barang</th>
                    <th>Nama Barang Stok</th>
                    <th class="text-center" width="12%">Qty Masuk</th>
                    <th class="text-center" width="10%">Satuan</th>
                    <th width="28%">Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($getDataDetail)) :
                    $No = 0;
                    $totalQty = 0;
                    foreach ($getDataDetail as $key => $value) :
                        $No++;
                        $id_material   = $value['id_material'];
                        $nm_material    = (!empty($GET_MATERIAL[$id_material]['stock_name'])) ? $GET_MATERIAL[$id_material]['stock_name'] : ((!empty($GET_MATERIAL[$id_material]['nama'])) ? $GET_MATERIAL[$id_material]['nama'] : '-');
                        $code_material  = (!empty($GET_MATERIAL[$id_material]['id_stock'])) ? $GET_MATERIAL[$id_material]['id_stock'] : ((!empty($GET_MATERIAL[$id_material]['code'])) ? $GET_MATERIAL[$id_material]['code'] : '-');

                        $this->db->select('a.*, b.code as satuan, c.code as satuan_packing');
                        $this->db->from('accessories a');
                        $this->db->join('ms_satuan b', 'b.id = a.id_unit', 'left');
                        $this->db->join('ms_satuan c', 'c.id = a.id_unit_gudang', 'left');
                        $this->db->where('a.id', $id_material);
                        $get_stock = $this->db->get()->row();

                        $satuan_nama = (!empty($get_stock->satuan)) ? $get_stock->satuan : '';
                        $qty_in      = (float)$value['qty_oke'];
                        $totalQty   += $qty_in;
                ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $No; ?></td>
                            <td class="fw-semibold text-primary font-monospace"><?= strtoupper($code_material); ?></td>
                            <td class="fw-bold text-dark"><?= strtoupper($nm_material); ?></td>
                            <td class="text-center fw-bold text-success fs-6"><?= number_format($qty_in, 2); ?></td>
                            <td class="text-center text-muted fw-semibold"><?= strtoupper($satuan_nama); ?></td>
                            <td class="text-muted"><?= !empty($value['keterangan']) ? $value['keterangan'] : '-'; ?></td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">TOTAL QTY MASUK :</td>
                        <td class="text-center text-primary fs-6"><?= number_format($totalQty, 2); ?></td>
                        <td colspan="2"></td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fa fa-info-circle me-1"></i> Tidak ada rincian barang pada transaksi ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between align-items-center pt-2">
        <a href="<?= base_url('incoming_stok/print_incoming_stok/' . $getData[0]['kode_trans']); ?>" target="_blank" class="btn btn-outline-primary shadow-sm">
            <i class="fa fa-print me-1"></i> Cetak Bukti Incoming
        </a>
    </div>
</div>
