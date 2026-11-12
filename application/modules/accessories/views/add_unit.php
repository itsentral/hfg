<?php
    $ENABLE_ADD     = has_permission('Inventory_1.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_1.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_1.View');
    $ENABLE_DELETE  = has_permission('Inventory_1.Delete');

    $id   = (!empty($header)) ? $header[0]->id : '';
    $code = (!empty($header)) ? $header[0]->code : '';
?>

<div class="card border-0">
    <div class="card-body p-0">
        <form id="data_form" autocomplete="off">
            <input type="hidden" id="id" name="id" value="<?= $id; ?>">
            <div class="mb-3">
                <label for="code" class="form-label fw-bold">Nama Unit <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code" required name="code" placeholder="Nama Unit (e.g. PCS, KG, ROLL)" value="<?= $code; ?>">
            </div>
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" name="save" id="save">
                    <i class="fa fa-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>