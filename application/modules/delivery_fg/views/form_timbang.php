<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Input Berat Timbang Aktual — <?= isset($do) ? $do->do_no : '' ?></h5>
    </div>
    <div class="card-body">
        <?php if (isset($do)): ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%">No DO</td>
                        <td><strong><?= $do->do_no ?></strong></td>
                    </tr>
                    <tr>
                        <td>Customer</td>
                        <td><?= htmlspecialchars($do->customer) ?></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><?= $do->status ?></td>
                    </tr>
                    <tr>
                        <td>Total Estimasi Berat</td>
                        <td><strong><?= number_format(isset($total_estimasi) ? $total_estimasi : 0, 3) ?> kg</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('delivery_fg/save_timbang/' . (isset($do) ? $do->do_no : '')) ?>">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Berat Aktual (kg) <span class="text-danger">*</span></label>
                    <input type="number" name="berat_aktual" class="form-control" id="berat_aktual"
                           min="0.001" step="0.001" placeholder="Masukkan berat aktual" required
                           oninput="hitungSelisih()">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Opsional">
                </div>
            </div>

            <?php if (isset($total_estimasi) && $total_estimasi > 0): ?>
            <div class="mt-3 p-3 bg-light rounded" id="preview-selisih" style="display:none;">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="fw-bold">Estimasi Berat</div>
                        <div class="fs-5"><?= number_format($total_estimasi, 3) ?> kg</div>
                    </div>
                    <div class="col-md-4">
                        <div class="fw-bold">Selisih (kg)</div>
                        <div class="fs-5" id="preview-selisih-kg">—</div>
                    </div>
                    <div class="col-md-4">
                        <div class="fw-bold">Selisih (%)</div>
                        <div class="fs-5" id="preview-selisih-pct">—</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan Timbang
                </button>
                <?php if (isset($do)): ?>
                <a href="<?= base_url('delivery_fg/view/' . $do->do_no) ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
var totalEstimasi = <?= isset($total_estimasi) ? (float) $total_estimasi : 0 ?>;

function hitungSelisih() {
    var aktual = parseFloat($('#berat_aktual').val()) || 0;
    if (aktual <= 0 || totalEstimasi <= 0) {
        $('#preview-selisih').hide();
        return;
    }

    var selisih_kg  = aktual - totalEstimasi;
    var selisih_pct = Math.abs(selisih_kg) / totalEstimasi * 100;

    $('#preview-selisih-kg').text((selisih_kg >= 0 ? '+' : '') + selisih_kg.toFixed(3));
    $('#preview-selisih-pct').text(selisih_pct.toFixed(2) + '%');

    var $pct = $('#preview-selisih-pct');
    if (selisih_pct > 3) {
        $pct.removeClass('text-success').addClass('text-danger fw-bold');
        $pct.append(' <span class="badge bg-warning text-dark">Melebihi Toleransi</span>');
    } else {
        $pct.removeClass('text-danger fw-bold').addClass('text-success');
    }

    $('#preview-selisih').show();
}
</script>
