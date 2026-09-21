<?php
$id             = (!empty($data)) ? $data[0]['id'] : '';
$disabled       = (!empty($data)) ? 'disabled' : '';
$kdcab          = (!empty($data)) ? $data[0]['kdcab'] : '';
$kd_asset       = (!empty($data)) ? $data[0]['kd_asset'] : '';
$nm_asset       = (!empty($data)) ? $data[0]['nm_asset'] : '';
$category       = (!empty($data)) ? $data[0]['category'] : '';
$category_pajak = (!empty($data)) ? $data[0]['category_pajak'] : '';
$id_dept        = (!empty($data)) ? $data[0]['id_dept'] : '';
$nilai_asset    = (!empty($data)) ? $data[0]['nilai_asset'] : '';
$depresiasi     = (!empty($data)) ? $data[0]['depresiasi'] : '';
$value          = (!empty($data)) ? $data[0]['value'] : '';
$qty            = (!empty($data)) ? $data[0]['qty'] : '';
$id_costcenter  = (!empty($data)) ? $data[0]['id_costcenter'] : '';
$tgl_perolehan  = (!empty($data)) ? $data[0]['tgl_perolehan'] : '';
$id_coa         = (!empty($data)) ? $data[0]['id_coa'] : '';
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fa fa-edit me-2"></i><?= $title ?>
        </h5>
        <a href="<?= site_url('asset') ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Asset List
        </a>
    </div>
    <div class="card-body">
        <form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
            <input type="hidden" name="id" id="id" value="<?= $id ?>">
            <input type="hidden" name="kd_asset" value="<?= $kd_asset ?>">
            <input type="hidden" id="cs" value="<?= $id_costcenter ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Owned Assets <span class="text-danger">*</span></label>
                    <select name="branch" id="branch" class="form-select select2">
                        <option value="0">Select Owned Assets</option>
                        <?php foreach($list_cab as $valx): ?>
                            <?php $sexd = ($valx['id_branch'] == $kdcab) ? 'selected' : ''; ?>
                            <option value="<?= $valx['id_branch'] ?>" <?= $sexd ?>><?= strtoupper($valx['nm_alias']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Tax Category <span class="text-danger">*</span></label>
                    <select name="category_pajak" id="category_pajak" class="form-select select2" <?= $disabled ?>>
                        <option value="0">Select Tax Category</option>
                        <?php foreach($list_pajak as $valx): ?>
                            <?php $sexd = ($valx['id'] == $category_pajak) ? 'selected' : ''; ?>
                            <option value="<?= $valx['id'] ?>" <?= $sexd ?>><?= strtoupper($valx['nm_category']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                    <select name="category" id="category" class="form-select select2" <?= $disabled ?>>
                        <option value="0">Select Category</option>
                        <?php foreach($list_catg as $valx): ?>
                            <?php $sexd = ($valx['id'] == $category) ? 'selected' : ''; ?>
                            <option value="<?= $valx['id'] ?>" <?= $sexd ?>><?= strtoupper($valx['nm_category']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Asset Name <span class="text-danger">*</span></label>
                    <input type="text" id="nm_asset" name="nm_asset" class="form-control" autocomplete="off" placeholder="Asset Name" value="<?= $nm_asset ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Kelompok Penyusutan</label>
                    <select name="id_coa" id="id_coa" class="form-select select2" <?= $disabled ?>>
                        <option value="0">TIDAK ADA PENYUSUTAN</option>
                        <?php foreach($list_coa as $valx): ?>
                            <?php $sexd = ($valx['id'] == $id_coa) ? 'selected' : ''; ?>
                            <option value="<?= $valx['id'] ?>" <?= $sexd ?>><?= strtoupper($valx['coa'] . ' | ' . $valx['keterangan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Department <span class="text-danger">*</span></label>
                    <select name="lokasi_asset" id="lokasi_asset" class="form-select select2" <?= $disabled ?>>
                        <option value="0">Select Department</option>
                        <?php foreach($list_dept as $valx): ?>
                            <?php $sexd = ($valx['id'] == $id_dept) ? 'selected' : ''; ?>
                            <option value="<?= $valx['id'] ?>" <?= $sexd ?>><?= strtoupper($valx['nm_dept']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Cost Center</label>
                    <select name="cost_center" id="cost_center" class="form-select select2" <?= $disabled ?>>
                        <option value="0">Select Cost Center</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Tanggal Perolehan <span class="text-danger">*</span></label>
                    <input type="date" id="tanggal_oleh" name="tanggal_oleh" class="form-control" value="<?= $tgl_perolehan ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Nilai Perolehan <span class="text-danger">*</span></label>
                    <input type="text" id="nilai_asset" name="nilai_asset" class="form-control text-end autoNumeric" placeholder="0" value="<?= $nilai_asset ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Nilai Susut (Depresiasi / Bulan)</label>
                    <input type="text" id="value" name="value" class="form-control text-end autoNumeric" placeholder="0" readonly value="<?= $value ?>">
                </div>

                <div class="col-12 mt-4 text-end">
                    <button type="button" class="btn btn-primary px-4" id="simpan-bro">
                        <i class="fa fa-save me-1"></i> Save Data
                    </button>
                    <a href="<?= site_url('asset') ?>" class="btn btn-secondary px-4">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        if ($('.select2').length > 0) {
            $('.select2').select2({ width: '100%' });
        }

        $(document).on('change', '#lokasi_asset', function() {
            var id_dept = $(this).val();
            var cs = $('#cs').val();
            if (id_dept !== '0') {
                $.ajax({
                    url: base_url + active_controller + '/list_center/' + id_dept + '/' + cs,
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        $('#cost_center').html(data.option);
                    }
                });
            } else {
                $('#cost_center').html('<option value="0">Select Cost Center</option>');
            }
        });

        if ($('#lokasi_asset').val() !== '0') {
            $('#lokasi_asset').trigger('change');
        }
    });

    $(document).on('click', '#simpan-bro', function(e) {
        e.preventDefault();
        var nm_asset = $('#nm_asset').val();
        if (nm_asset === '') {
            swal({ title: "Warning!", text: "Nama Asset tidak boleh kosong!", type: "warning" });
            return false;
        }

        swal({
            title: "Are you sure?",
            text: "Save this asset data?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-primary",
            confirmButtonText: "Yes, Save!",
            closeOnConfirm: true
        }, function(isConfirm) {
            if (isConfirm) {
                if (typeof loading_spinner === 'function') loading_spinner();
                var formData = new FormData($('#form_proses_bro')[0]);
                $.ajax({
                    url: base_url + active_controller + '/add',
                    type: "POST",
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.status == 1) {
                            swal("Saved!", data.pesan, "success");
                            window.location.href = base_url + active_controller;
                        } else {
                            swal("Failed!", data.pesan, "warning");
                        }
                    },
                    error: function() {
                        swal("Error!", "An error occurred during save", "error");
                    }
                });
            }
        });
    });
</script>
