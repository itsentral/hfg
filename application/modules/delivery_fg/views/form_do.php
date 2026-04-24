<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
$is_edit = isset($do) && $do;
$form_title = $is_edit ? 'Edit Delivery Order: ' . $do->do_no : 'Buat Delivery Order Baru';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?= $form_title ?></h5>
    </div>
    <div class="card-body">
        <form method="post" action="<?= base_url('delivery_fg/save_do') ?>" id="form-do">
            <?php if ($is_edit): ?>
                <input type="hidden" name="do_no" value="<?= $do->do_no ?>">
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <input type="text" name="customer" class="form-control"
                           value="<?= $is_edit ? htmlspecialchars($do->customer) : '' ?>"
                           placeholder="Nama customer" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Delivery <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_delivery" class="form-control"
                           value="<?= $is_edit ? $do->tgl_delivery : date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control"
                           value="<?= $is_edit && $do->keterangan ? htmlspecialchars($do->keterangan) : '' ?>"
                           placeholder="Opsional">
                </div>
            </div>

            <!-- Tabel Item -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Item Produk FG</h6>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addRow()">
                        <i class="fa fa-plus"></i> Tambah Item
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="tbl-items">
                            <thead class="table-light">
                                <tr>
                                    <th width="30%">Produk FG</th>
                                    <th width="20%">Qty Kirim</th>
                                    <th width="20%">Berat Referensi (kg)</th>
                                    <th width="20%">Estimasi Berat (kg)</th>
                                    <th width="10%" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="item-rows">
                                <?php if ($is_edit && !empty($details)): ?>
                                    <?php foreach ($details as $d): ?>
                                    <tr>
                                        <td>
                                            <select name="produk_fg[]" class="form-select produk-select" required onchange="onProdukChange(this)">
                                                <option value="">-- Pilih Produk --</option>
                                            </select>
                                            <input type="hidden" name="nm_produk_fg[]" class="nm-produk" value="<?= htmlspecialchars($d->nm_produk_fg) ?>">
                                            <input type="hidden" class="selected-produk" value="<?= $d->produk_fg ?>">
                                        </td>
                                        <td>
                                            <input type="number" name="qty_kirim[]" class="form-control qty-input"
                                                   value="<?= $d->qty_kirim ?>" min="0.01" step="0.01" required
                                                   onchange="hitungEstimasi(this)">
                                        </td>
                                        <td>
                                            <input type="number" name="berat_referensi[]" class="form-control berat-ref-input"
                                                   value="<?= $d->berat_referensi ?>" min="0" step="0.0001" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control estimasi-input" readonly
                                                   value="<?= number_format($d->estimasi_berat, 3) ?>">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Row kosong awal -->
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="3" class="text-end fw-bold">Total Estimasi Berat:</td>
                                    <td><span id="total-estimasi">0.000</span> kg</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan DO
                </button>
                <a href="<?= base_url('delivery_fg') ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
var stokFG = [];

// Load stok FG saat halaman dibuka
$(document).ready(function () {
    $.get(siteurl + 'delivery_fg/get_stok_fg', function (res) {
        if (res.success) {
            stokFG = res.data;
            // Isi semua select yang sudah ada
            $('.produk-select').each(function () {
                var selected = $(this).closest('tr').find('.selected-produk').val();
                populateSelect(this, selected);
            });
            updateNmProduk();
        }
    }, 'json');

    // Jika tidak ada row, tambah satu
    if ($('#item-rows tr').length === 0) {
        addRow();
    }

    hitungTotalEstimasi();
});

function populateSelect(selectEl, selectedVal) {
    var $sel = $(selectEl);
    $sel.empty().append('<option value="">-- Pilih Produk --</option>');
    stokFG.forEach(function (s) {
        var opt = $('<option>')
            .val(s.produk_fg)
            .text(s.produk_fg + ' — Stok: ' + parseFloat(s.qty_stok).toFixed(2) + ' | Ref: ' + parseFloat(s.berat_referensi || 0).toFixed(4) + ' kg')
            .data('berat_referensi', s.berat_referensi || 0)
            .data('nm_produk_fg', s.nm_produk_fg || s.produk_fg);
        if (s.produk_fg === selectedVal) opt.prop('selected', true);
        $sel.append(opt);
    });
    if (selectedVal) {
        onProdukChange(selectEl);
    }
}

function updateNmProduk() {
    $('.produk-select').each(function () {
        var selected = $(this).closest('tr').find('.selected-produk').val();
        if (selected) {
            $(this).val(selected);
            onProdukChange(this);
        }
    });
}

function addRow() {
    var row = '<tr>' +
        '<td>' +
            '<select name="produk_fg[]" class="form-select produk-select" required onchange="onProdukChange(this)">' +
                '<option value="">-- Pilih Produk --</option>' +
            '</select>' +
            '<input type="hidden" name="nm_produk_fg[]" class="nm-produk" value="">' +
            '<input type="hidden" class="selected-produk" value="">' +
        '</td>' +
        '<td><input type="number" name="qty_kirim[]" class="form-control qty-input" min="0.01" step="0.01" required onchange="hitungEstimasi(this)"></td>' +
        '<td><input type="number" name="berat_referensi[]" class="form-control berat-ref-input" min="0" step="0.0001" readonly></td>' +
        '<td><input type="text" class="form-control estimasi-input" readonly value="0.000"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    $('#item-rows').append(row);

    // Populate select baru
    var newSelect = $('#item-rows tr:last .produk-select')[0];
    populateSelect(newSelect, '');
}

function removeRow(btn) {
    if ($('#item-rows tr').length <= 1) {
        Swal.fire({ title: 'Perhatian', text: 'Minimal satu item harus ada', icon: 'warning', confirmButtonText: 'OK' });
        return;
    }
    $(btn).closest('tr').remove();
    hitungTotalEstimasi();
}

function onProdukChange(selectEl) {
    var $row = $(selectEl).closest('tr');
    var $opt = $(selectEl).find(':selected');
    var berat_ref = $opt.data('berat_referensi') || 0;
    var nm = $opt.data('nm_produk_fg') || '';

    $row.find('.berat-ref-input').val(parseFloat(berat_ref).toFixed(4));
    $row.find('.nm-produk').val(nm);
    hitungEstimasi($row.find('.qty-input')[0]);
}

function hitungEstimasi(qtyInput) {
    var $row = $(qtyInput).closest('tr');
    var qty = parseFloat($row.find('.qty-input').val()) || 0;
    var berat_ref = parseFloat($row.find('.berat-ref-input').val()) || 0;
    var estimasi = qty * berat_ref;
    $row.find('.estimasi-input').val(estimasi.toFixed(3));
    hitungTotalEstimasi();
}

function hitungTotalEstimasi() {
    var total = 0;
    $('.estimasi-input').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    $('#total-estimasi').text(total.toFixed(3));
}
</script>
