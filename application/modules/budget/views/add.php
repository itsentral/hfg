<?php
$id_dept        = (!empty($header)) ? $header[0]->id_dept : '';
$id_costcenter  = (!empty($header)) ? $header[0]->id_costcenter : '';
$coa            = (!empty($header)) ? $header[0]->coa : '';
$coa_akum        = (!empty($header)) ? $header[0]->coa_akum : '';
$nama_asset     = (!empty($header)) ? strtoupper($header[0]->nama_asset) : '';
$tahun          = (!empty($header)) ? $header[0]->tahun : date('Y');
$bulan          = (!empty($header)) ? $header[0]->bulan : date('m');
$budget         = (!empty($header)) ? number_format($header[0]->budget) : '';
$qty            = (!empty($header)) ? number_format($header[0]->qty) : '';
$keterangan     = (!empty($header)) ? strtoupper($header[0]->keterangan) : '';
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fa fa-plus-circle me-2"></i><?= $title ?>
        </h5>
        <a href="<?= site_url('budget') ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Budget List
        </a>
    </div>
    <div class="card-body">
        <form action="#" method="POST" id="form_proses_bro" autocomplete="off">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Department <span class="text-danger">*</span></label>
                    <select name="id_dept" id="id_dept" class="form-select select2">
                        <option value="0">Select Department</option>
                        <?php foreach ($list_dept as $valx): ?>
                            <?php $selected = ($valx['id'] == $id_dept) ? 'selected' : ''; ?>
                            <option value="<?= $valx['id'] ?>" <?= $selected ?>><?= strtoupper($valx['nm_dept']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Cost Center</label>
                    <select name="id_costcenter" id="id_costcenter" class="form-select select2">
                        <option value="0">Select Cost Center</option>
                        <?php foreach ($list_cost as $valx): ?>
                            <?php $selected = ($valx['id_costcenter'] == $id_costcenter) ? 'selected' : ''; ?>
                            <option value="<?= $valx['id_costcenter'] ?>" <?= $selected ?>><?= strtoupper($valx['nm_costcenter']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Post Anggaran (COA) <span class="text-danger">*</span></label>
                    <select name="coa" id="coa" class="form-select select2">
                        <option value="0">Select Post Anggaran</option>
                        <?php foreach ($datacoa as $valx): ?>
                            <?php $selected = ($valx['no_perkiraan'] == $coa) ? 'selected' : ''; ?>
                            <option value="<?= $valx['no_perkiraan'] ?>" <?= $selected ?>><?= strtoupper($valx['no_perkiraan']) ?> - <?= strtoupper($valx['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Post Penyusutan <span class="text-danger">*</span></label>
                    <select name="coa_akum" id="coa_akum" class="form-select select2">
                        <option value="0">Select Post Penyusutan</option>
                        <?php foreach ($penyusutan as $valx): ?>
                            <?php $selected = (isset($valx['coa']) && $valx['coa'] == $coa_akum) ? 'selected' : ''; ?>
                            <option value="<?= isset($valx['coa']) ? $valx['coa'] : $valx['id'] ?>" <?= $selected ?>><?= strtoupper(isset($valx['coa']) ? $valx['coa'] . ' - ' . $valx['keterangan'] : $valx['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Asset <span class="text-danger">*</span></label>
                    <input type="text" id="nama_asset" name="nama_asset" class="form-control" placeholder="Nama Assets" value="<?= $nama_asset ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Bulan <span class="text-danger">*</span></label>
                    <select name="bulan" id="bulan" class="form-select">
                        <?php
                        $months = array(1=>"January", 2=>"February", 3=>"March", 4=>"April", 5=>"May", 6=>"June", 7=>"July", 8=>"August", 9=>"September", 10=>"October", 11=>"November", 12=>"December");
                        foreach ($months as $num => $name) {
                            $selected = ($num == $bulan) ? 'selected' : '';
                            echo "<option value='{$num}' {$selected}>{$name}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Tahun <span class="text-danger">*</span></label>
                    <select name="tahun" id="tahun" class="form-select">
                        <?php
                        $current_year = date("Y") + 1;
                        for ($i = $current_year; $i >= 2019; $i--) {
                            $selected = ($i == $tahun) ? 'selected' : '';
                            echo "<option value='{$i}' {$selected}>{$i}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Budget <span class="text-danger">*</span></label>
                    <input type="text" id="budget" name="budget" class="form-control text-end autoNumeric" placeholder="0" value="<?= $budget ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Qty <span class="text-danger">*</span></label>
                    <input type="number" id="qty" name="qty" class="form-control text-center" placeholder="1" value="<?= $qty ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" class="form-control" rows="2" placeholder="Keterangan"><?= $keterangan ?></textarea>
                </div>

                <div class="col-12 text-end mt-4">
                    <button type="button" class="btn btn-primary px-4" id="save">
                        <i class="fa fa-save me-1"></i> Save Planning
                    </button>
                    <a href="<?= site_url('budget') ?>" class="btn btn-secondary px-4">Cancel</a>
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
    });

    $(document).on('click', '#save', function(e) {
        e.preventDefault();
        var id_dept = $('#id_dept').val();
        var nama_asset = $('#nama_asset').val();

        if (id_dept === '0' || id_dept === '') {
            swal({ title: "Warning!", text: "Please select department!", type: "warning" });
            return false;
        }
        if (nama_asset === '') {
            swal({ title: "Warning!", text: "Nama asset cannot be empty!", type: "warning" });
            return false;
        }

        swal({
            title: "Are you sure?",
            text: "Save this budget asset planning?",
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
                    url: base_url + active_controller + '/add_asset',
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
