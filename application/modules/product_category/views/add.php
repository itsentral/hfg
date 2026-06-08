<?php
$ENABLE_ADD     = has_permission('Product_Category.Add');
$ENABLE_MANAGE  = has_permission('Product_Category.Manage');
$ENABLE_VIEW    = has_permission('Product_Category.View');
$ENABLE_DELETE  = has_permission('Product_Category.Delete');

$id = (!empty($listData[0]->id)) ? $listData[0]->id : '';
$code_lv1 = (!empty($listData[0]->code_lv1)) ? $listData[0]->code_lv1 : '';
$code = (!empty($listData[0]->code_lv2)) ? $listData[0]->code_lv2 : '';
$nama = (!empty($listData[0]->nama)) ? $listData[0]->nama : '';
$code_manual = (!empty($listData[0]->code)) ? $listData[0]->code : '';

$status1 = (!empty($listData[0]->status) and $listData[0]->status == '1') ? 'checked' : '';
$status2 = (!empty($listData[0]->status) and $listData[0]->status == '0') ? 'checked' : ''; // Saya ubah jadi '0' asumsi Non-Aktif
?>
<div class="box box-primary">
    <div class="box-body">
        <form id="data_form" autocomplete="off">
            
            <!-- Tambahkan mb-3 di sini -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="col-form-label">Product Type <span class='text-danger'>*</span></label>
                </div>
                <div class="col-md-9">
                    <select name="code_lv1" id="code_lv1" class='form-select chosen-select'>
                        <option value="0">Select Product Type</option>
                        <?php
                        foreach ($listLevel1 as $key => $value) {
                            $selected = ($code_lv1 == $value['code_lv1']) ? 'selected' : '';
                            echo "<option value='" . $value['code_lv1'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Tambahkan mb-3 di sini -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="col-form-label">Product Category <span class='text-danger'>*</span></label>
                </div>
                <div class="col-md-9">
                    <input type="hidden" id="id" name="id" value='<?= $id; ?>'>
                    <input type="hidden" id="code" name="code" value='<?= $code; ?>'>
                    <input type="text" class="form-control" id="nama" required name="nama" placeholder="Product Category Name" value='<?= $nama; ?>'>
                </div>
            </div>

            <!-- Tambahkan mb-3 di sini -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="col-form-label">Category Code <span class='text-danger'>*</span></label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="code_manual" required name="code_manual" placeholder="Category Code" value='<?= $code_manual; ?>'>
                </div>
            </div>

            <?php if (!empty($id)) { ?>
                <!-- Tambahkan mb-3 di sini -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="col-form-label">Status</label>
                    </div>
                    <div class="col-md-9">
                        <div class="d-flex gap-4 pt-2">
                            <label>
                                <input type="radio" class="form-check-input" name="status" value="1" <?= $status1; ?>> Aktif
                            </label>
                            <label>
                                <input type="radio" class="form-check-input" name="status" value="0" <?= $status2; ?>> Non-Aktif
                            </label>
                        </div>
                    </div>
                </div>
            <?php } ?>
            
        </form>
    </div>
</div>