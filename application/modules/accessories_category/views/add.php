<?php
	$id = (!empty($listData[0]->id)) ? $listData[0]->id : '';
	$nm_category = (!empty($listData[0]->nm_category)) ? $listData[0]->nm_category : '';
	$description = (!empty($listData[0]->description)) ? $listData[0]->description : '';
?>

<div class="card border-0">
    <div class="card-body p-0">
        <form id="data_form" autocomplete="off">
            <input type="hidden" id="id" name="id" value='<?= $id; ?>'>

            <div class="mb-3">
                <label for="nm_category" class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nm_category" required name="nm_category" placeholder="Nama Category" value='<?= $nm_category; ?>'>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-bold">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3" placeholder="Deskripsi Category"><?= $description; ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-primary" name="save" id="save">
                    <i class="fa fa-save me-1"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>