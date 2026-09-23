<?php
$h = isset($header[0]) ? $header[0] : (object)array();
$nama_asset = isset($h->rev_nama_asset) && !empty($h->rev_nama_asset) ? strtoupper($h->rev_nama_asset) : (isset($h->nama_asset) ? strtoupper($h->nama_asset) : '');
$tahun      = isset($h->rev_tahun) && !empty($h->rev_tahun) ? $h->rev_tahun : (isset($h->tahun) ? $h->tahun : date('Y'));
$bulan      = isset($h->rev_bulan) && !empty($h->rev_bulan) ? $h->rev_bulan : (isset($h->bulan) ? $h->bulan : date('m'));
$budget     = isset($h->rev_budget) && !empty($h->rev_budget) ? number_format($h->rev_budget) : (isset($h->budget) ? number_format($h->budget) : '');
$budget_pr  = isset($h->budget_pr) ? number_format($h->budget_pr) : $budget;
$budget_po  = isset($h->budget_po) ? number_format($h->budget_po) : $budget;
$qty        = isset($h->rev_qty) && !empty($h->rev_qty) ? number_format($h->rev_qty) : (isset($h->qty) ? number_format($h->qty) : '');
$keterangan = isset($h->rev_keterangan) && !empty($h->rev_keterangan) ? strtoupper($h->rev_keterangan) : (isset($h->keterangan) ? strtoupper($h->keterangan) : '');
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-info">
            <i class="fa fa-check-square-o me-2"></i><?= $title ?>
        </h5>
        <a href="<?= site_url('budget/index_approve') ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Approval List
        </a>
    </div>
    <div class="card-body">
        <form action="#" method="POST" id="form_approve" autocomplete="off">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama Asset</label>
                    <input type="text" id="nama_asset" name="nama_asset" class="form-control" value="<?= $nama_asset ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Bulan</label>
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
                    <label class="form-label fw-bold">Tahun</label>
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
                    <label class="form-label fw-bold">Budget</label>
                    <input type="text" id="budget" name="budget" class="form-control text-end autoNumeric" value="<?= $budget ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Qty</label>
                    <input type="number" id="qty" name="qty" class="form-control text-center" value="<?= $qty ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Sisa Budget PR <span class="text-danger">*</span></label>
                    <input type="text" id="budget_pr" name="budget_pr" class="form-control text-end autoNumeric" value="<?= $budget_pr ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Sisa Budget PO <span class="text-danger">*</span></label>
                    <input type="text" id="budget_po" name="budget_po" class="form-control text-end autoNumeric" value="<?= $budget_po ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" class="form-control" rows="2"><?= $keterangan ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Approval Decision <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select">
                        <option value="Y">APPROVE</option>
                        <option value="D">REJECT</option>
                    </select>
                </div>

                <div class="col-12" id="box_reason" style="display: none;">
                    <label class="form-label fw-bold text-danger">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea id="reason" name="reason" class="form-control" rows="2" placeholder="Input rejection reason..."></textarea>
                </div>

                <div class="col-12 text-end mt-4">
                    <button type="button" class="btn btn-info text-white px-4" id="save_approve">
                        <i class="fa fa-check me-1"></i> Submit Decision
                    </button>
                    <a href="<?= site_url('budget/index_approve') ?>" class="btn btn-secondary px-4">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).on('change', '#status', function() {
        if ($(this).val() === 'D') {
            $('#box_reason').slideDown();
        } else {
            $('#box_reason').slideUp();
        }
    });

    $(document).on('click', '#save_approve', function(e) {
        e.preventDefault();
        var status = $('#status').val();
        var reason = $('#reason').val();

        if (status === 'D' && reason === '') {
            swal({ title: "Warning!", text: "Please enter rejection reason!", type: "warning" });
            return false;
        }

        swal({
            title: "Are you sure?",
            text: "Process approval decision?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-info",
            confirmButtonText: "Yes, Process!",
            closeOnConfirm: true
        }, function(isConfirm) {
            if (isConfirm) {
                if (typeof loading_spinner === 'function') loading_spinner();
                var formData = new FormData($('#form_approve')[0]);
                $.ajax({
                    url: base_url + active_controller + '/approve_asset',
                    type: "POST",
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.status == 1) {
                            swal("Processed!", data.pesan, "success");
                            window.location.href = base_url + active_controller + '/index_approve';
                        } else {
                            swal("Failed!", data.pesan, "warning");
                        }
                    },
                    error: function() {
                        swal("Error!", "An error occurred during process", "error");
                    }
                });
            }
        });
    });
</script>
