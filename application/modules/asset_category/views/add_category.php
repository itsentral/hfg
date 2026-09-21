<?php
$id          = (!empty($header[0]->id)) ? $header[0]->id : '';
$nm_category = (!empty($header[0]->nm_category)) ? $header[0]->nm_category : '';
$status      = (!empty($header[0]->status)) ? $header[0]->status : 'Y';
?>

<form id="form_ct" autocomplete="off">
    <input type="hidden" id="id" name="id" value="<?= $id ?>">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nm_category" name="nm_category" placeholder="Input Category Name" value="<?= $nm_category ?>">
        </div>
        <div class="col-12">
            <label class="form-label fw-bold">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="Y" <?= ($status == 'Y') ? 'selected' : '' ?>>Active</option>
                <option value="N" <?= ($status == 'N') ? 'selected' : '' ?>>Not Active</option>
            </select>
        </div>
        <div class="col-12 text-end mt-4">
            <button type="button" class="btn btn-primary btn-sm px-4" id="save">
                <i class="fa fa-save me-1"></i> Save Category
            </button>
        </div>
    </div>
</form>
