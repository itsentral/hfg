<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row align-items-center g-3">
                    <div class="col-md-7 col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="fa fa-file-text-o fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-0">Detail Purchase Request (PR) Stok</h5>
                                <small class="text-muted">No. PR: <span class="badge bg-primary bg-opacity-10 text-primary fw-bold"><?= htmlspecialchars($header[0]['no_pr'] ?? '-') ?></span> &bull; No. SO: <?= htmlspecialchars($header[0]['so_number'] ?? '-') ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-12 text-md-end d-flex gap-2 justify-content-md-end">
                        <a href="<?= base_url('request_pr_stok') ?>" class="btn btn-secondary shadow-sm">
                            <i class="fa fa-arrow-left me-1"></i> Kembali
                        </a>
                        <a href="<?= base_url('request_pr_stok/PrintH2/' . ($header[0]['so_number'] ?? '')) ?>" class="btn btn-success shadow-sm" target="_blank">
                            <i class="fa fa-print me-1"></i> Cetak PR
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if (!empty($header[0]) && (($header[0]['sts_reject1'] == '1' || $header[0]['sts_reject2'] == '1' || $header[0]['sts_reject3'] == '1') && $header[0]['rejected'] == 1)) : ?>
                    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-3 mb-4" role="alert">
                        <i class="fa fa-ban fs-3 text-danger mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1">Pengajuan PR Ditolak!</h6>
                            <p class="mb-0 text-dark">
                                <?= !empty($header[0]['reject_reason3']) ? htmlspecialchars($header[0]['reject_reason3']) : (!empty($header[0]['reject_reason2']) ? htmlspecialchars($header[0]['reject_reason2']) : (!empty($header[0]['reject_reason1']) ? htmlspecialchars($header[0]['reject_reason1']) : 'Pengajuan PR ditolak oleh pihak Management / Approval.')); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- PR Information Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-md-7 col-12">
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr class="border-bottom">
                                        <th class="bg-light text-muted py-2 px-3" style="width: 35%;">No. Request / SO</th>
                                        <td class="py-2 px-3 fw-bold text-dark"><?= htmlspecialchars($header[0]['so_number'] ?? '-') ?></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="bg-light text-muted py-2 px-3">No. Dokumen PR</th>
                                        <td class="py-2 px-3 fw-bold text-danger"><?= htmlspecialchars($header[0]['no_pr'] ?? '-') ?></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="bg-light text-muted py-2 px-3">Customer / Keperluan</th>
                                        <td class="py-2 px-3"><?= htmlspecialchars($header[0]['nm_customer'] ?? 'Pengisian Stok Internal') ?></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="bg-light text-muted py-2 px-3">Tgl Dibutuhkan</th>
                                        <td class="py-2 px-3"><?= !empty($header[0]['tgl_dibutuhkan']) ? date('d F Y', strtotime($header[0]['tgl_dibutuhkan'])) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-muted py-2 px-3">Tingkat Urgensi</th>
                                        <td class="py-2 px-3">
                                            <?php if (($header[0]['tingkat_pr'] ?? 1) == 2) : ?>
                                                <span class="badge bg-danger">Urgent</span>
                                            <?php else : ?>
                                                <span class="badge bg-info text-dark">Normal</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-5 col-12">
                        <div class="d-flex flex-column gap-3">
                            <div class="card border-0 bg-info bg-opacity-10 rounded-3 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase">Nilai Budget</div>
                                        <div class="fs-4 fw-bold text-info mt-1">
                                            Rp <?= number_format($header[0]['nilai_budget'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-white bg-opacity-75 rounded-circle text-info">
                                        <i class="fa fa-money fs-3"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 bg-success bg-opacity-10 rounded-3 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase">Nilai Total Pengajuan</div>
                                        <div class="fs-4 fw-bold text-success mt-1">
                                            Rp <?= number_format($header[0]['nilai_pengajuan'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-white bg-opacity-75 rounded-circle text-success">
                                        <i class="fa fa-calculator fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Table -->
                <h6 class="fw-bold text-dark mb-3"><i class="fa fa-list text-primary me-1"></i> Rincian Barang Permintaan (PR)</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-striped border-top mb-0" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th class="text-start">Nama Barang & No. Akun COA</th>
                                <th class="text-end" style="width: 90px;">Min Stock</th>
                                <th class="text-end" style="width: 90px;">Max Stock</th>
                                <th class="text-end" style="width: 90px;">Min Order</th>
                                <th class="text-end" style="width: 110px;">Qty PR (Pack)</th>
                                <th class="text-center" style="width: 80px;">Satuan</th>
                                <th class="text-end" style="width: 100px;">Qty Total</th>
                                <th class="text-center" style="width: 80px;">Unit</th>
                                <th class="text-end" style="width: 110px;">Harga Acuan</th>
                                <th class="text-end" style="width: 120px;">Total Estimasi</th>
                                <th class="text-center" style="width: 120px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_all_item = 0;
                            if (!empty($detail)) {
                                foreach ($detail as $key => $value) {
                                    $key++;
                                    $propose = $value['propose_purchase'] ?? 0;

                                    $this->db->select('a.*, b.code as satuan, c.code as satuan_pack');
                                    $this->db->from('accessories a');
                                    $this->db->join('ms_satuan b', 'b.id = a.id_unit', 'left');
                                    $this->db->join('ms_satuan c', 'c.id = a.id_unit_gudang', 'left');
                                    $this->db->where('a.id', $value['id_material']);
                                    $get_stock = $this->db->get()->row();

                                    $konversi = (!empty($get_stock) && $get_stock->konversi > 0) ? $get_stock->konversi : 1;
                                    $min_order = (!empty($get_stock) && !empty($get_stock->min_order)) ? $get_stock->min_order : 0;
                                    $satuan_pack = !empty($get_stock->satuan_pack) ? strtoupper($get_stock->satuan_pack) : '-';
                                    $satuan_unit = !empty($get_stock->satuan) ? strtoupper($get_stock->satuan) : '-';

                                    $subtotal = ($value['price_ref'] ?? 0) * $propose;
                                    $total_all_item += $subtotal;

                                    echo "<tr>";
                                    echo "<td class='text-center'>" . $key . "</td>";
                                    echo "<td class='text-start fw-semibold'>" . htmlspecialchars($value['stock_name'] ?? '') . " <span class='text-muted small fw-normal'>(" . htmlspecialchars($value['no_coa'] ?? '-') . " - " . htmlspecialchars(strtoupper($value['nm_coa'] ?? '-')) . ")</span></td>";
                                    echo "<td class='text-end'>" . number_format($value['min_stok'] ?? 0, 2) . "</td>";
                                    echo "<td class='text-end'>" . number_format($value['max_stok'] ?? 0, 2) . "</td>";
                                    echo "<td class='text-end'>" . number_format($min_order, 2) . "</td>";
                                    echo "<td class='text-end fw-bold text-primary'>" . number_format($propose / $konversi, 2) . "</td>";
                                    echo "<td class='text-center'><span class='badge bg-light text-dark border'>" . $satuan_pack . "</span></td>";
                                    echo "<td class='text-end fw-bold'>" . number_format($propose, 2) . "</td>";
                                    echo "<td class='text-center'><span class='badge bg-light text-dark border'>" . $satuan_unit . "</span></td>";
                                    echo "<td class='text-end'>Rp " . number_format($value['price_ref'] ?? 0, 2) . "</td>";
                                    echo "<td class='text-end fw-bold text-dark'>Rp " . number_format($subtotal, 2) . "</td>";

                                    $status_badge = "<span class='badge bg-secondary'>Draft</span>";
                                    if (($value['status_app'] ?? '') == 'N') {
                                        $status_badge = "<span class='badge bg-warning text-dark'>Menunggu Review</span>";
                                    } elseif (($value['status_app'] ?? '') == 'Y') {
                                        $status_badge = "<span class='badge bg-success'>Disetujui</span>";
                                    } elseif (($value['status_app'] ?? '') == 'D') {
                                        $status_badge = "<span class='badge bg-danger'>Ditolak</span>";
                                    }

                                    echo "<td class='text-center'>" . $status_badge . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='12' class='text-center py-4 text-muted'>Tidak ada rincian item dalam PR ini</td></tr>";
                            }
                            ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="10" class="text-end fw-bold py-3 fs-6">Total Nilai Item:</th>
                                <th class="text-end fw-bold py-3 fs-6 text-success">Rp <?= number_format($total_all_item, 2) ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                    <a href="<?= base_url('request_pr_stok') ?>" class="btn btn-secondary shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                    <a href="<?= base_url('request_pr_stok/PrintH2/' . ($header[0]['so_number'] ?? '')) ?>" class="btn btn-success shadow-sm" target="_blank">
                        <i class="fa fa-print me-1"></i> Cetak Dokumen PR
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
