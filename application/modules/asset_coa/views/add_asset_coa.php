<?php
$ArrSelect['Y'] = 'Active';
$ArrSelect['N'] = 'Not Active';

$id          = (!empty($data[0]->id)) ? $data[0]->id : ((!empty($header[0]->id)) ? $header[0]->id : '');
$keterangan  = (!empty($data[0]->keterangan)) ? $data[0]->keterangan : ((!empty($header[0]->keterangan)) ? $header[0]->keterangan : '');
$coa         = (!empty($data[0]->coa)) ? $data[0]->coa : ((!empty($header[0]->coa)) ? $header[0]->coa : '');
$coa_kredit     = (!empty($data[0]->coa_kredit)) ? $data[0]->coa_kredit : ((!empty($header[0]->coa_kredit)) ? $header[0]->coa_kredit : '');
$status      = (!empty($data[0]->status)) ? $data[0]->status : ((!empty($header[0]->status)) ? $header[0]->status : 'Y');

if (!isset($coalist)) {
    $coalist = array('' => '-- Select COA --');
}
?>
<form id="form_ct" autocomplete="off">
    <div class="card border-0 shadow-none"><br>
        <div class="card-body p-2">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-3 fw-semibold">
                    <label>Keterangan</label>
                </div>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Keterangan" value='<?= $keterangan; ?>'>
                    <input type="hidden" class="form-control" id="id" name="id" value='<?= $id; ?>'>
                </div>
            </div>
            <div class="row mb-3 align-items-center">
                <div class="col-sm-3 fw-semibold">
                    <label>COA Debet</label>
                </div>
                <div class="col-sm-9">
                    <?php
                    echo form_dropdown('coa', $coalist, $coa, array('id' => 'coa', 'class' => 'form-select select2'));
                    ?>
                </div>
            </div>
            <div class="row mb-3 align-items-center">
                <div class="col-sm-3 fw-semibold">
                    <label>COA Kredit</label>
                </div>
                <div class="col-sm-9">
                    <?php
                    echo form_dropdown('coa_kredit', $coalist, $coa_kredit, array('id' => 'coa_kredit', 'class' => 'form-select select2'));
                    ?>
                </div>
            </div>
            <div class="row mb-3 align-items-center">
                <div class="col-sm-3 fw-semibold">
                    <label>Status</label>
                </div>
                <div class="col-sm-9">
                    <?php
                    echo form_dropdown('status', $ArrSelect, $status, array('id' => 'status', 'class' => 'form-select select2'));
                    ?>
                </div>
            </div>
            <div class="row mb-3 align-items-center">
                <div class="col-sm-3 fw-semibold"></div>
                <div class="col-sm-9">
                    <button type="button" class="btn btn-primary px-4" name="save" id="save"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    if (typeof swal !== 'undefined' && swal.close) {
        swal.close();
    }
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                dropdownParent: $('#ModalView'),
                width: '100%'
            });
        }
    });
</script>
