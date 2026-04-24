<?php defined('BASEPATH') || exit('No direct script access allowed');
$is_edit    = isset($bom) && $bom;
$page_title = $is_edit ? 'Edit BOM: ' . $bom->nm_produk : 'Buat BOM Baru';
?>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-sitemap"></i> <?= $page_title ?></h5>
        <a href="<?= base_url('bom') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="form-bom" action="<?= base_url('bom/save_bom') ?>" method="POST">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id_bom" value="<?= $bom->id ?>">
            <?php endif; ?>

            <!-- Header: Pilih Produk -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Produk FG <span class="text-danger">*</span></label>
                    <select name="id_produk" id="select-produk" class="form-select select2-produk" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($produk_list as $p): ?>
                            <option value="<?= htmlspecialchars($p->code_lv4) ?>"
                                data-nama="<?= htmlspecialchars($p->nama) ?>"
                                data-trade="<?= htmlspecialchars($p->trade_name) ?>"
                                <?= ($is_edit && $bom->id_produk == $p->code_lv4) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p->code_lv4) ?> — <?= htmlspecialchars($p->nama) ?>
                                <?php if ($p->nm_lv1 || $p->nm_lv2): ?>
                                    (<?= htmlspecialchars(implode(' / ', array_filter([$p->nm_lv1, $p->nm_lv2, $p->nm_lv3]))) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="nm_produk" id="nm_produk"
                        value="<?= $is_edit ? htmlspecialchars($bom->nm_produk) : '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Trade Name Produk</label>
                    <input type="text" id="trade-name-produk" class="form-control bg-light" readonly
                        value="<?= $is_edit ? htmlspecialchars($bom->nm_produk) : '' ?>"
                        placeholder="Otomatis dari master">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"
                        placeholder="Opsional"><?= $is_edit ? htmlspecialchars($bom->keterangan) : '' ?></textarea>
                </div>
            </div>

            <!-- Detail Material -->
            <div class="card border-primary">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fa fa-list"></i> Daftar Material</h6>
                    <button type="button" class="btn btn-light btn-sm" id="btn-add-row">
                        <i class="fa fa-plus"></i> Tambah Material
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" id="tbl-detail">
                            <thead class="table-light">
                                <tr>
                                    <th width="40" class="text-center">No</th>
                                    <th width="35%">Material <span class="text-danger">*</span></th>
                                    <th width="25%">Trade Name</th>
                                    <th width="10%">Qty</th>
                                    <!-- <th width="10%">Satuan</th> -->
                                    <th>Keterangan</th>
                                    <th width="50" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-detail">
                                <?php if ($is_edit && !empty($details)): ?>
                                    <?php foreach ($details as $i => $d): ?>
                                    <tr>
                                        <td class="text-center row-no"><?= $i + 1 ?></td>
                                        <td>
                                            <select name="detail[<?= $i ?>][id_material]"
                                                class="form-select form-select-sm select2-material" required>
                                                <option value="">-- Pilih Material --</option>
                                                <?php foreach ($material_list as $m): ?>
                                                    <option value="<?= htmlspecialchars($m->code_lv4) ?>"
                                                        data-nama="<?= htmlspecialchars($m->nama) ?>"
                                                        data-trade="<?= htmlspecialchars($m->trade_name) ?>"
                                                        data-unit="<?= htmlspecialchars($m->id_unit) ?>"
                                                        data-nm-unit="<?= htmlspecialchars($m->nm_unit) ?>"
                                                        <?= ($d->id_material == $m->code_lv4) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($m->code_lv4) ?> — <?= htmlspecialchars($m->nama) ?>
                                                        <?php if ($m->nm_lv1 || $m->nm_lv2): ?>
                                                            (<?= htmlspecialchars(implode(' / ', array_filter([$m->nm_lv1, $m->nm_lv2, $m->nm_lv3]))) ?>)
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="detail[<?= $i ?>][nm_material]" class="inp-nm-material" value="<?= htmlspecialchars($d->nm_material) ?>">
                                            <input type="hidden" name="detail[<?= $i ?>][trade_name]" class="inp-trade-name" value="<?= htmlspecialchars($d->trade_name) ?>">
                                            <input type="hidden" name="detail[<?= $i ?>][id_unit]" class="inp-id-unit" value="<?= htmlspecialchars($d->id_unit) ?>">
                                            <input type="hidden" name="detail[<?= $i ?>][nm_unit]" class="inp-nm-unit" value="<?= htmlspecialchars($d->nm_unit) ?>">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm inp-trade-display bg-light"
                                                readonly value="<?= htmlspecialchars($d->trade_name) ?>">
                                        </td>
                                        <td>
                                            <input type="number" name="detail[<?= $i ?>][qty]" step="0.0001"
                                                class="form-control form-control-sm" value="<?= $d->qty ?>" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm inp-unit-display bg-light"
                                                readonly value="<?= htmlspecialchars($d->nm_unit) ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="detail[<?= $i ?>][keterangan]"
                                                class="form-control form-control-sm"
                                                value="<?= htmlspecialchars($d->keterangan) ?>">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-xs btn-remove-row">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan BOM
                </button>
                <a href="<?= base_url('bom') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<!-- Template row (hidden) -->
<template id="row-template">
    <tr>
        <td class="text-center row-no"></td>
        <td>
            <select name="detail[__IDX__][id_material]"
                class="form-select form-select-sm select2-material" required>
                <option value="">-- Pilih Material --</option>
                <?php foreach ($material_list as $m): ?>
                    <option value="<?= htmlspecialchars($m->code_lv4) ?>"
                        data-nama="<?= htmlspecialchars($m->nama) ?>"
                        data-trade="<?= htmlspecialchars($m->trade_name) ?>"
                        data-unit="<?= htmlspecialchars($m->id_unit) ?>"
                        data-nm-unit="<?= htmlspecialchars($m->nm_unit) ?>">
                        <?= htmlspecialchars($m->code_lv4) ?> — <?= htmlspecialchars($m->nama) ?>
                        <?php if ($m->nm_lv1 || $m->nm_lv2): ?>
                            (<?= htmlspecialchars(implode(' / ', array_filter([$m->nm_lv1, $m->nm_lv2, $m->nm_lv3]))) ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="detail[__IDX__][nm_material]" class="inp-nm-material" value="">
            <input type="hidden" name="detail[__IDX__][trade_name]" class="inp-trade-name" value="">
            <input type="hidden" name="detail[__IDX__][id_unit]" class="inp-id-unit" value="">
            <input type="hidden" name="detail[__IDX__][nm_unit]" class="inp-nm-unit" value="">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm inp-trade-display bg-light" readonly placeholder="Otomatis">
        </td>
        <td>
            <input type="number" name="detail[__IDX__][qty]" step="0.0001"
                class="form-control form-control-sm" value="1" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm inp-unit-display bg-light" readonly placeholder="Otomatis">
        </td>
        <td>
            <input type="text" name="detail[__IDX__][keterangan]" class="form-control form-control-sm" placeholder="Opsional">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-xs btn-remove-row">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
var rowIndex = <?= $is_edit && !empty($details) ? count($details) : 0 ?>;

function initSelect2Row($row) {
    $row.find('.select2-material').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Material --',
        allowClear: true,
        width: '100%'
    }).on('change', function () {
        var $opt  = $(this).find(':selected');
        var trade = $opt.data('trade') || '';
        var nama  = $opt.data('nama') || '';
        var unit  = $opt.data('unit') || '';
        var nmUnit = $opt.data('nm-unit') || unit;
        var $row  = $(this).closest('tr');

        $row.find('.inp-nm-material').val(nama);
        $row.find('.inp-trade-name').val(trade);
        $row.find('.inp-id-unit').val(unit);
        $row.find('.inp-nm-unit').val(nmUnit);
        $row.find('.inp-trade-display').val(trade);
        $row.find('.inp-unit-display').val(nmUnit);
    });
}

function renumberRows() {
    $('#tbody-detail tr').each(function (i) {
        $(this).find('.row-no').text(i + 1);
    });
}

$(document).ready(function () {

    // Select2 untuk produk
    $('#select-produk').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Produk --',
        allowClear: true,
        width: '100%'
    }).on('change', function () {
        var $opt  = $(this).find(':selected');
        var nama  = $opt.data('nama') || '';
        var trade = $opt.data('trade') || '';
        $('#nm_produk').val(nama);
        $('#trade-name-produk').val(trade || nama);
    });

    // Init Select2 untuk baris yang sudah ada (mode edit)
    $('#tbody-detail tr').each(function () {
        initSelect2Row($(this));
    });

    // Tambah baris material
    $('#btn-add-row').on('click', function () {
        var tpl = document.getElementById('row-template').innerHTML;
        tpl = tpl.replace(/__IDX__/g, rowIndex);
        var $row = $(tpl);
        $('#tbody-detail').append($row);
        initSelect2Row($row);
        rowIndex++;
        renumberRows();
    });

    // Hapus baris
    $(document).on('click', '.btn-remove-row', function () {
        $(this).closest('tr').remove();
        renumberRows();
    });

    // Validasi sebelum submit
    $('#form-bom').on('submit', function () {
        if ($('#tbody-detail tr').length === 0) {
            alert('Minimal satu material harus ditambahkan');
            return false;
        }
    });
});
</script>
