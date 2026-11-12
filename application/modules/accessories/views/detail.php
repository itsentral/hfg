<?php
$is_accessories = (!empty($header) && isset($header[0]->stock_name));

$id             = (!empty($header)) ? $header[0]->id : '';
$id_stock       = (!empty($header) && isset($header[0]->id_stock)) ? $header[0]->id_stock : '';
$stock_name     = (!empty($header) && isset($header[0]->stock_name)) ? $header[0]->stock_name : '';
$trade_name     = (!empty($header) && isset($header[0]->trade_name)) ? $header[0]->trade_name : '';
$brand          = (!empty($header) && isset($header[0]->brand)) ? $header[0]->brand : '';
$spec           = (!empty($header) && isset($header[0]->spec)) ? $header[0]->spec : '';
$id_category    = (!empty($header) && isset($header[0]->id_category)) ? $header[0]->id_category : '';
$id_unit_gudang = (!empty($header) && isset($header[0]->id_unit_gudang)) ? $header[0]->id_unit_gudang : '';
$konversi       = (!empty($header) && isset($header[0]->konversi)) ? $header[0]->konversi : '';
$id_unit        = (!empty($header) && isset($header[0]->id_unit)) ? $header[0]->id_unit : '';
$min_order      = (!empty($header) && isset($header[0]->min_order)) ? $header[0]->min_order : 0;
$no_coa         = (!empty($header) && isset($header[0]->no_coa)) ? $header[0]->no_coa : '';
$nm_coa         = (!empty($header) && isset($header[0]->nm_coa)) ? $header[0]->nm_coa : '';
$status         = (!empty($header) && isset($header[0]->status)) ? ($header[0]->status == '1' ? 'Active' : 'Non-Active') : 'Active';
$badge_color    = ($status == 'Active') ? 'success' : 'danger';

// Material fallback fields
$code_material  = (!empty($header) && isset($header[0]->code_material)) ? $header[0]->code_material : '';
$code_company   = (!empty($header) && isset($header[0]->code_company)) ? $header[0]->code_company : '';
$nm_material    = (!empty($header) && isset($header[0]->nm_material)) ? strtoupper($header[0]->nm_material) : '';
$satuan_packing = (!empty($header) && isset($header[0]->satuan_packing)) ? $header[0]->satuan_packing : '';
$unit           = (!empty($header) && isset($header[0]->unit)) ? $header[0]->unit : '';
$begin_balance  = (!empty($header) && isset($header[0]->begin_balance)) ? $header[0]->begin_balance : '';
$incoming       = (!empty($header) && isset($header[0]->incoming)) ? $header[0]->incoming : '';
$outgoing       = (!empty($header) && isset($header[0]->outgoing)) ? $header[0]->outgoing : '';
$ending_balance = (!empty($header) && isset($header[0]->ending_balance)) ? $header[0]->ending_balance : '';
$unit_fisik     = (!empty($header) && isset($header[0]->unit_fisik)) ? $header[0]->unit_fisik : '';
?>

<div class="card border-0">
    <div class="card-body p-0">
        <?php if ($is_accessories): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <tbody>
                        <tr>
                            <th width="30%" class="bg-light">Item Code</th>
                            <td><?= !empty($id_stock) ? $id_stock : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Stok Name</th>
                            <td class="fw-bold"><?= !empty($stock_name) ? strtoupper($stock_name) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Trade Name</th>
                            <td><?= !empty($trade_name) ? strtoupper($trade_name) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Brand</th>
                            <td><?= !empty($brand) ? strtoupper($brand) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Spesification</th>
                            <td><?= !empty($spec) ? $spec : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Packing Unit / Konversi</th>
                            <td>
                                <?php
                                $nm_packing = '-';
                                if (!empty($results['satuan_packing'])) {
                                    foreach ($results['satuan_packing'] as $sat) {
                                        if ($sat->id == $id_unit_gudang) {
                                            $nm_packing = $sat->code;
                                            break;
                                        }
                                    }
                                }
                                echo $nm_packing . ' (Konversi: ' . number_format((float)$konversi, 2) . ')';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Unit Measurement</th>
                            <td>
                                <?php
                                $nm_satuan = '-';
                                if (!empty($results['satuan'])) {
                                    foreach ($results['satuan'] as $sat) {
                                        if ($sat->id == $id_unit) {
                                            $nm_satuan = $sat->code;
                                            break;
                                        }
                                    }
                                }
                                echo $nm_satuan;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Minimum Order</th>
                            <td><?= number_format((float)$min_order, 2) ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">COA</th>
                            <td><?= !empty($no_coa) ? ($no_coa . ' - ' . $nm_coa) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Status</th>
                            <td><span class="badge bg-<?= $badge_color ?>"><?= $status ?></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Code Material</label>
                    <input type="text" class="form-control" disabled value="<?= $code_company; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Material Name</label>
                    <input type="text" class="form-control" disabled value="<?= $nm_material; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Packing Unit</label>
                    <input type="text" class="form-control" disabled value="<?= $satuan_packing; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Conversion</label>
                    <input type="text" class="form-control" disabled value="<?= $konversi; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Unit</label>
                    <input type="text" class="form-control" disabled value="<?= $unit; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Begin Balance /kg</label>
                    <input type="text" class="form-control" disabled value="<?= $begin_balance; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Incoming /kg</label>
                    <input type="text" class="form-control" disabled value="<?= $incoming; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Outgoing /kg</label>
                    <input type="text" class="form-control" disabled value="<?= $outgoing; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Ending Balance /kg</label>
                    <input type="text" class="form-control" disabled value="<?= $ending_balance; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Unit Fisik</label>
                    <input type="text" class="form-control" disabled value="<?= $unit_fisik; ?>">
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>