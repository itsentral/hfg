<?php defined('BASEPATH') || exit('No direct script access allowed');
$is_edit    = isset($plan) && $plan;
$page_title = $is_edit ? 'Edit Production Plan' : 'Buat Production Plan';
?>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<!-- Flatpickr -->
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= $page_title ?></h5>
        <a href="<?= base_url('production_planning') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="form-plan" action="<?= base_url('production_planning/save_plan') ?>" method="POST">
            <?php if ($is_edit): ?>
                <input type="hidden" name="plan_no" value="<?= $plan->plan_no ?>">
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Plan <span class="text-danger">*</span></label>
                    <input type="text" name="tgl_plan" id="tgl_plan" class="form-control flatpickr-date"
                        value="<?= $is_edit ? $plan->tgl_plan : date('Y-m-d') ?>" required autocomplete="off">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Due Date</label>
                    <input type="text" name="due_date" id="due_date" class="form-control flatpickr-date"
                        value="<?= $is_edit ? $plan->due_date : '' ?>" autocomplete="off">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Target Qty <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="target_qty" class="form-control"
                        value="<?= $is_edit ? $plan->target_qty : '' ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Target Berat (kg)</label>
                    <input type="number" step="0.001" name="target_berat" class="form-control"
                        value="<?= $is_edit ? $plan->target_berat : '' ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Produk FG <span class="text-danger">*</span></label>
                    <select name="id_produk_fg" id="select-produk-fg" class="form-select select2-produk" required>
                        <option value="">-- Pilih Produk FG --</option>
                        <?php if (!empty($produk_list)): ?>
                            <?php foreach ($produk_list as $p): ?>
                                <option value="<?= htmlspecialchars($p->code_lv4) ?>"
                                    data-nama="<?= htmlspecialchars($p->nama) ?>"
                                    <?= ($is_edit && $plan->id_produk_fg == $p->code_lv4) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p->code_lv4) ?> — <?= htmlspecialchars($p->nama) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" name="nm_produk_fg" id="nm_produk_fg"
                        value="<?= $is_edit ? htmlspecialchars($plan->nm_produk_fg) : '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="1"><?= $is_edit ? htmlspecialchars($plan->catatan) : '' ?></textarea>
                </div>
            </div>

            <!-- Tabel Coil -->
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Alokasi Coil</h6>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-load-coil">
                        <i class="fa fa-search"></i> Cari Coil Tersedia
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" id="tbl-coil-selected">
                            <thead class="table-light">
                                <tr>
                                    <th width="40" class="text-center">No</th>
                                    <th>No Coil</th>
                                    <th>No ROS</th>
                                    <th>Material</th>
                                    <th class="text-end">Berat Bersih (kg)</th>
                                    <th class="text-end">Estimasi FG (kg)</th>
                                    <th width="60" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-coil">
                                <?php if ($is_edit && !empty($details)): ?>
                                    <?php foreach ($details as $i => $d): ?>
                                        <tr>
                                            <td class="text-center row-no"><?= $i + 1 ?></td>
                                            <td>
                                                <?= htmlspecialchars($d->no_coil) ?>
                                                <input type="hidden" name="detail[<?= $i ?>][no_coil]" value="<?= htmlspecialchars($d->no_coil) ?>">
                                                <input type="hidden" name="detail[<?= $i ?>][id_material]" value="<?= htmlspecialchars($d->id_material) ?>">
                                                <input type="hidden" name="detail[<?= $i ?>][nm_material]" value="<?= htmlspecialchars($d->nm_material) ?>">
                                                <input type="hidden" name="detail[<?= $i ?>][no_ros]" value="<?= htmlspecialchars($d->no_ros) ?>">
                                                <input type="hidden" name="detail[<?= $i ?>][net_weight_coil]" value="<?= $d->net_weight_coil ?>">
                                                <input type="hidden" name="detail[<?= $i ?>][estimasi_fg]" value="<?= $d->estimasi_fg ?>">
                                            </td>
                                            <td><?= htmlspecialchars($d->no_ros) ?></td>
                                            <td><?= htmlspecialchars($d->nm_material) ?></td>
                                            <td class="text-end"><?= number_format($d->net_weight_coil, 3) ?></td>
                                            <td class="text-end"><?= number_format($d->estimasi_fg, 3) ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-xs btn-remove-coil"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="4" class="text-end">Total:</td>
                                    <td class="text-end" id="total-net-weight">0.000</td>
                                    <td class="text-end" id="total-estimasi-fg">0.000</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan Plan
                </button>
                <a href="<?= base_url('production_planning') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pilih Coil -->
<div class="modal fade" id="modal-coil" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Coil Tersedia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="coil-loading" class="text-center py-3">
                    <i class="fa fa-spinner fa-spin fa-2x"></i> Memuat data coil...
                </div>
                <div class="table-responsive" id="coil-table-wrap" style="display:none;">
                    <table class="table table-bordered table-sm table-hover" id="tbl-coil-available">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center"><input type="checkbox" id="check-all-coil"></th>
                                <th>No Coil</th>
                                <th>No ROS</th>
                                <th>Material</th>
                                <th class="text-end">Berat Kotor</th>
                                <th class="text-end">Berat Bersih</th>
                                <th>Gudang</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-coil-available"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-add-selected-coil">
                    <i class="fa fa-plus"></i> Tambahkan Coil Terpilih
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
var rowIndex = <?= $is_edit && !empty($details) ? count($details) : 0 ?>;

$(document).ready(function () {

    // ── Select2 untuk Produk FG ──────────────────────────────────────────────
    $('#select-produk-fg').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Produk FG --',
        allowClear: true,
        width: '100%'
    }).on('change', function () {
        var $opt = $(this).find(':selected');
        $('#nm_produk_fg').val($opt.data('nama') || '');
    });

    // ── Flatpickr untuk semua input tanggal ──────────────────────────────────
    flatpickr('.flatpickr-date', {
        dateFormat: 'Y-m-d',
        locale: 'id',
        allowInput: true
    });

    // ── Recalc totals ────────────────────────────────────────────────────────
    recalcTotals();

    // ── Load coil available ──────────────────────────────────────────────────
    $('#btn-load-coil').on('click', function () {
        var id_produk_fg = $('#select-produk-fg').val();
        $('#coil-loading').show();
        $('#coil-table-wrap').hide();
        $('#modal-coil').modal('show');

        $.get(siteurl + 'production_planning/get_coil_available', { id_produk_fg: id_produk_fg }, function (res) {
            $('#coil-loading').hide();
            var html = '';
            if (res.status === 'ok' && res.data.length > 0) {
                $.each(res.data, function (i, c) {
                    html += '<tr>'
                          + '<td class="text-center"><input type="checkbox" class="coil-check"'
                          + ' data-no_coil="' + c.no_coil + '"'
                          + ' data-id_material="' + (c.id_material || '') + '"'
                          + ' data-nm_material="' + (c.nm_material || '') + '"'
                          + ' data-no_ros="' + (c.no_ros || '') + '"'
                          + ' data-berat_bersih="' + (c.berat_bersih || 0) + '"></td>'
                          + '<td>' + c.no_coil + '</td>'
                          + '<td>' + (c.no_ros || '-') + '</td>'
                          + '<td>' + (c.nm_material || '-') + '</td>'
                          + '<td class="text-end">' + parseFloat(c.berat_kotor || 0).toFixed(3) + '</td>'
                          + '<td class="text-end">' + parseFloat(c.berat_bersih || 0).toFixed(3) + '</td>'
                          + '<td>' + (c.nm_gudang || '-') + '</td>'
                          + '</tr>';
                });
            } else {
                html = '<tr><td colspan="7" class="text-center text-muted">Tidak ada coil tersedia</td></tr>';
            }
            $('#tbody-coil-available').html(html);
            $('#coil-table-wrap').show();
        }, 'json');
    });

    // ── Check all coil ───────────────────────────────────────────────────────
    $('#check-all-coil').on('change', function () {
        $('.coil-check').prop('checked', this.checked);
    });

    // ── Tambahkan coil terpilih ──────────────────────────────────────────────
    $('#btn-add-selected-coil').on('click', function () {
        var existingCoils = [];
        $('#tbody-coil input[name*="[no_coil]"]').each(function () {
            existingCoils.push($(this).val());
        });

        $('.coil-check:checked').each(function () {
            var no_coil      = $(this).data('no_coil');
            if (existingCoils.indexOf(no_coil) !== -1) return;

            var id_material  = $(this).data('id_material');
            var nm_material  = $(this).data('nm_material');
            var no_ros       = $(this).data('no_ros');
            var berat_bersih = parseFloat($(this).data('berat_bersih')) || 0;

            var row = '<tr>'
                + '<td class="text-center row-no"></td>'
                + '<td>' + no_coil
                + '<input type="hidden" name="detail[' + rowIndex + '][no_coil]" value="' + no_coil + '">'
                + '<input type="hidden" name="detail[' + rowIndex + '][id_material]" value="' + id_material + '">'
                + '<input type="hidden" name="detail[' + rowIndex + '][nm_material]" value="' + nm_material + '">'
                + '<input type="hidden" name="detail[' + rowIndex + '][no_ros]" value="' + no_ros + '">'
                + '<input type="hidden" name="detail[' + rowIndex + '][net_weight_coil]" value="' + berat_bersih.toFixed(3) + '">'
                + '<input type="hidden" name="detail[' + rowIndex + '][estimasi_fg]" value="' + berat_bersih.toFixed(3) + '">'
                + '</td>'
                + '<td>' + (no_ros || '-') + '</td>'
                + '<td>' + (nm_material || '-') + '</td>'
                + '<td class="text-end">' + berat_bersih.toFixed(3) + '</td>'
                + '<td class="text-end">' + berat_bersih.toFixed(3) + '</td>'
                + '<td class="text-center"><button type="button" class="btn btn-danger btn-xs btn-remove-coil"><i class="fa fa-trash"></i></button></td>'
                + '</tr>';

            $('#tbody-coil').append(row);
            rowIndex++;
        });

        recalcTotals();
        $('#modal-coil').modal('hide');
    });

    // ── Hapus baris coil ─────────────────────────────────────────────────────
    $(document).on('click', '.btn-remove-coil', function () {
        $(this).closest('tr').remove();
        recalcTotals();
    });
});

function recalcTotals() {
    var totalNet = 0, totalFg = 0;
    $('#tbody-coil tr').each(function (i) {
        $(this).find('.row-no').text(i + 1);
        totalNet += parseFloat($(this).find('input[name*="net_weight_coil"]').val()) || 0;
        totalFg  += parseFloat($(this).find('input[name*="estimasi_fg"]').val()) || 0;
    });
    $('#total-net-weight').text(totalNet.toFixed(3));
    $('#total-estimasi-fg').text(totalFg.toFixed(3));
}
</script>
