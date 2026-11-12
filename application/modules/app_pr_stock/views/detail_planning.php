<?php
$pembeda = substr($header[0]['so_number'], 0, 1);
$due_date = (!empty($header[0]['due_date'])) ? date('d F Y', strtotime($header[0]['due_date'])) : '-';
$tgl_dibutuhkan = (!empty($header[0]['tgl_dibutuhkan'])) ? date('d F Y', strtotime($header[0]['tgl_dibutuhkan'])) : '-';
?>

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-info-circle me-2"></i>Detail PR Stock
            </h5>
            <small class="text-muted">Nomor Request / SO: <span class="fw-bold text-dark"><?= htmlspecialchars($header[0]['so_number']) ?></span></small>
        </div>
        <div>
            <button type="button" class="btn btn-outline-secondary btn-sm px-3 shadow-sm" id="back">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="p-3 bg-light rounded-3 border h-100">
                    <span class="text-muted small fw-semibold text-uppercase">No. Request / SO</span>
                    <h6 class="fw-bold text-primary mb-0 mt-1"><?= htmlspecialchars($header[0]['so_number']) ?></h6>
                    <small class="text-muted">Due Date: <?= $due_date ?></small>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-3 bg-light rounded-3 border h-100">
                    <span class="text-muted small fw-semibold text-uppercase">No. PR</span>
                    <h6 class="fw-bold text-dark mb-0 mt-1"><?= htmlspecialchars($header[0]['no_pr']) ?></h6>
                    <small class="text-muted">Dibutuhkan: <?= $tgl_dibutuhkan ?></small>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-3 bg-light rounded-3 border h-100">
                    <span class="text-muted small fw-semibold text-uppercase">Customer</span>
                    <h6 class="fw-bold text-dark mb-0 mt-1"><?= htmlspecialchars($header[0]['nm_customer'] ?: '-') ?></h6>
                    <small class="text-muted">Tingkat: <span class="badge <?= ($header[0]['tingkat_pr'] == 2) ? 'bg-danger' : 'bg-info' ?>"><?= ($header[0]['tingkat_pr'] == 2) ? 'Urgent' : 'Normal' ?></span></small>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-3 bg-light rounded-3 border h-100">
                    <span class="text-muted small fw-semibold text-uppercase">Perbandingan Nilai</span>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted">Budget:</small>
                        <span class="fw-bold text-dark">Rp <?= number_format($header[0]['nilai_budget']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Pengajuan:</small>
                        <span class="fw-bold text-primary">Rp <?= number_format($ttl_pengajuan_pr) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Barang Table -->
        <h6 class="fw-bold text-primary mb-2"><i class="fa fa-list me-1"></i>Rincian Barang</h6>
        <div class="table-responsive mb-4">
            <table class="table table-striped table-bordered table-hover align-middle w-100">
                <thead class="table-light text-center small">
                    <tr>
                        <th width="4%" class="text-center">#</th>
                        <th>Barang</th>
                        <th width="11%" class="text-end">Kebutuhan / Bln</th>
                        <th width="10%" class="text-end">Max</th>
                        <th width="10%" class="text-end">Stock</th>
                        <th width="10%" class="text-end">Propose</th>
                        <th width="12%" class="text-end">Price Reference</th>
                        <th width="14%" class="text-end">Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total_price = 0;
                    if (!empty($detail)) :
                        $numb = 1;
                        foreach ($detail as $key => $value) :
                            $nm_material = htmlspecialchars($value['nm_material']) . ' <br><small class="text-muted fw-semibold">(' . htmlspecialchars($value['no_coa'] . ' - ' . $value['nm_coa']) . ')</small>';
                            $propose = $value['propose_purchase'];

                            $get_kebutuhan = $this->db->get_where('budget_rutin_detail', ['id_barang' => $value['id_material']])->row();
                            $get_stock = $this->db->get_where('warehouse_stock_non_material', ['id_material' => $value['id_material']])->row();

                            $kebutuhan = (!empty($get_kebutuhan)) ? $get_kebutuhan->kebutuhan_month : 0;
                            $stock = (!empty($get_stock)) ? $get_stock->qty_stock : 0;
                            $price_ref = $value['price_ref'];
                            $total_row = ($propose) * $price_ref;
                            $grand_total_price += $total_row;
                    ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $numb++ ?></td>
                            <td><?= $nm_material ?></td>
                            <td class="text-end"><?= number_format($kebutuhan) ?></td>
                            <td class="text-end"><?= number_format($kebutuhan * 1.5) ?></td>
                            <td class="text-end fw-semibold"><?= number_format($stock, 2) ?></td>
                            <td class="text-end fw-bold text-primary"><?= number_format($propose, 2) ?></td>
                            <td class="text-end">Rp <?= number_format($price_ref) ?></td>
                            <td class="text-end fw-bold">Rp <?= number_format($total_row) ?></td>
                        </tr>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="7" class="text-end fw-bold">Total Price Pengajuan:</th>
                        <th class="text-end fw-bold text-primary fs-6">Rp <?= number_format($grand_total_price) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary px-4 shadow-sm" id="back_bottom">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">
    var base_url = '<?= base_url(); ?>';
    var active_controller = '<?= ($this->uri->segment(1)); ?>';

    $(document).ready(function() {
        $(document).on('click', '#back, #back_bottom', function() {
            window.location.href = base_url + active_controller + '/approval_management';
        });
    });
</script>
