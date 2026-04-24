<?php defined('BASEPATH') || exit('No direct script access allowed');
$is_edit    = isset($plan) && $plan;
$page_title = $is_edit ? 'Edit Production Plan' : 'Buat Production Plan';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

<style>
.coil-row-selected { background-color: #d1e7dd !important; }
.estimate-badge { font-size: 1rem; font-weight: bold; }
.product-block { border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; margin-bottom: 16px; background: #f8f9fa; }
.product-block .block-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
</style>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-industry"></i> <?= $page_title ?></h5>
        <a href="<?= base_url('production_planning') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="form-plan" action="<?= base_url('production_planning/save_plan') ?>" method="POST">
            <?php if ($is_edit): ?>
                <input type="hidden" name="plan_no" value="<?= $plan->plan_no ?>">
            <?php endif; ?>

            <!-- Header Plan -->
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
                    <label class="form-label">Target Qty</label>
                    <input type="number" step="0.01" name="target_qty" class="form-control"
                        value="<?= $is_edit ? $plan->target_qty : '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="1"><?= $is_edit ? htmlspecialchars($plan->catatan) : '' ?></textarea>
                </div>
            </div>

            <!-- Container produk (bisa multi produk) -->
            <div id="product-blocks">
                <!-- Blok produk pertama -->
                <div class="product-block" id="block-0">
                    <div class="block-header">
                        <h6 class="mb-0 text-primary"><i class="fa fa-box"></i> Produk #<span class="block-num">1</span></h6>
                        <button type="button" class="btn btn-danger btn-sm btn-remove-block d-none">
                            <i class="fa fa-times"></i> Hapus Produk
                        </button>
                    </div>

                    <!-- Pilih Produk -->
                    <div class="row mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Produk FG <span class="text-danger">*</span></label>
                            <select name="id_produk_fg" class="form-select select2-produk" required>
                                <option value="">-- Pilih Produk --</option>
                                <?php if (!empty($produk_list)): ?>
                                    <?php foreach ($produk_list as $p): ?>
                                        <option value="<?= htmlspecialchars($p->code_lv4) ?>"
                                            data-nama="<?= htmlspecialchars($p->nama) ?>"
                                            data-trade="<?= htmlspecialchars($p->trade_name) ?>"
                                            <?= ($is_edit && $plan->id_produk_fg == $p->code_lv4) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p->code_lv4) ?> — <?= htmlspecialchars($p->nama) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <input type="hidden" name="nm_produk_fg" class="inp-nm-produk"
                                value="<?= $is_edit ? htmlspecialchars($plan->nm_produk_fg) : '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Berat / Unit (kg)</label>
                            <input type="text" class="form-control bg-light inp-berat-unit" readonly
                                placeholder="Otomatis" value="<?= $is_edit ? $plan->target_berat : '' ?>">
                            <input type="hidden" name="target_berat" class="inp-berat-unit-hidden"
                                value="<?= $is_edit ? $plan->target_berat : '' ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100 btn-cari-coil">
                                <i class="fa fa-search"></i> Cari Coil Tersedia
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Coil -->
                    <div class="coil-section" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold text-secondary">Material: <span class="lbl-material-name">—</span></span>
                            <span class="badge bg-success estimate-badge">
                                Estimated Total Qty: <span class="lbl-total-qty">0</span>
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th width="40" class="text-center">
                                            <input type="checkbox" class="check-all-coil">
                                        </th>
                                        <th>No Coil</th>
                                        <th>Nama Material</th>
                                        <th class="text-end">Net Weight (kg)</th>
                                        <th class="text-end">Estimate Qty</th>
                                        <th>Gudang</th>
                                    </tr>
                                </thead>
                                <tbody class="tbody-coil">
                                    <!-- diisi via AJAX -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Hidden inputs untuk coil yang dipilih -->
                        <div class="selected-coil-inputs"></div>
                    </div>

                    <!-- Loading -->
                    <div class="coil-loading text-center py-3" style="display:none;">
                        <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                        <div class="mt-2 text-muted">Memuat coil tersedia...</div>
                    </div>
                </div>
            </div>

            <!-- Tombol Tambah Produk -->
            <div class="mb-3">
                <button type="button" class="btn btn-outline-primary" id="btn-add-product">
                    <i class="fa fa-plus"></i> Add More Product
                </button>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success btn-lg" id="btn-create-spk">
                    <i class="fa fa-file-alt"></i> Create SPK
                </button>
                <a href="<?= base_url('production_planning') ?>" class="btn btn-secondary btn-lg">Batal</a>
            </div>
        </form>
    </div>
</div>

<!-- Template blok produk -->
<template id="product-block-template">
    <div class="product-block" id="block-__IDX__">
        <div class="block-header">
            <h6 class="mb-0 text-primary"><i class="fa fa-box"></i> Produk #<span class="block-num">__NUM__</span></h6>
            <button type="button" class="btn btn-danger btn-sm btn-remove-block">
                <i class="fa fa-times"></i> Hapus Produk
            </button>
        </div>
        <div class="row mb-3">
            <div class="col-md-7">
                <label class="form-label fw-semibold">Produk FG <span class="text-danger">*</span></label>
                <select name="id_produk_fg" class="form-select select2-produk" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($produk_list as $p): ?>
                        <option value="<?= htmlspecialchars($p->code_lv4) ?>"
                            data-nama="<?= htmlspecialchars($p->nama) ?>"
                            data-trade="<?= htmlspecialchars($p->trade_name) ?>">
                            <?= htmlspecialchars($p->code_lv4) ?> — <?= htmlspecialchars($p->nama) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="nm_produk_fg" class="inp-nm-produk" value="">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Berat / Unit (kg)</label>
                <input type="text" class="form-control bg-light inp-berat-unit" readonly placeholder="Otomatis">
                <input type="hidden" name="target_berat" class="inp-berat-unit-hidden" value="">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-primary w-100 btn-cari-coil">
                    <i class="fa fa-search"></i> Cari Coil Tersedia
                </button>
            </div>
        </div>
        <div class="coil-section" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold text-secondary">Material: <span class="lbl-material-name">—</span></span>
                <span class="badge bg-success estimate-badge">
                    Estimated Total Qty: <span class="lbl-total-qty">0</span>
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover">
                    <thead>
                        <tr>
                            <th width="40" class="text-center"><input type="checkbox" class="check-all-coil"></th>
                            <th>No Coil</th>
                            <th>Nama Material</th>
                            <th class="text-end">Net Weight (kg)</th>
                            <th class="text-end">Estimate Qty</th>
                            <th>Gudang</th>
                        </tr>
                    </thead>
                    <tbody class="tbody-coil"></tbody>
                </table>
            </div>
            <div class="selected-coil-inputs"></div>
        </div>
        <div class="coil-loading text-center py-3" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
            <div class="mt-2 text-muted">Memuat coil tersedia...</div>
        </div>
    </div>
</template>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
var blockIndex = 1;

// ── Flatpickr ────────────────────────────────────────────────────────────────
flatpickr('.flatpickr-date', { dateFormat: 'Y-m-d', locale: 'id', allowInput: true });

// ── Init Select2 pada satu blok ──────────────────────────────────────────────
function initBlockSelect2($block) {
    $block.find('.select2-produk').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Produk --',
        allowClear: true,
        width: '100%'
    }).on('change', function () {
        var $opt  = $(this).find(':selected');
        var nama  = $opt.data('nama') || '';
        $block.find('.inp-nm-produk').val(nama);
        // Reset coil section
        $block.find('.coil-section').hide();
        $block.find('.inp-berat-unit').val('');
        $block.find('.inp-berat-unit-hidden').val('');
        $block.find('.lbl-total-qty').text('0');
    });
}

// ── Cari coil untuk satu blok ────────────────────────────────────────────────
function loadCoilForBlock($block) {
    var id_produk = $block.find('.select2-produk').val();
    if (!id_produk) {
        alert('Pilih produk terlebih dahulu');
        return;
    }

    $block.find('.coil-section').hide();
    $block.find('.coil-loading').show();

    // Ambil berat per unit produk
    $.get(siteurl + 'production_planning/get_produk_info', { id_produk_fg: id_produk }, function (res) {
        if (res.status === 'ok') {
            var berat = parseFloat(res.data.weight) || 0;
            $block.find('.inp-berat-unit').val(berat > 0 ? berat.toFixed(4) : '—');
            $block.find('.inp-berat-unit-hidden').val(berat);
        }
    }, 'json');

    // Ambil coil tersedia (filter via BOM)
    $.get(siteurl + 'production_planning/get_coil_available', { id_produk_fg: id_produk }, function (res) {
        $block.find('.coil-loading').hide();

        if (res.status !== 'ok' || res.data.length === 0) {
            $block.find('.tbody-coil').html(
                '<tr><td colspan="6" class="text-center text-muted py-3">' +
                '<i class="fa fa-info-circle"></i> Tidak ada coil tersedia untuk produk ini' +
                '</td></tr>'
            );
            $block.find('.coil-section').show();
            return;
        }

        var html = '';
        var materialNames = [];
        $.each(res.data, function (i, c) {
            if (c.nm_material && materialNames.indexOf(c.nm_material) === -1) {
                materialNames.push(c.nm_material);
            }
            html += '<tr class="coil-row" data-no_coil="' + c.no_coil + '"'
                  + ' data-id_material="' + (c.id_material || '') + '"'
                  + ' data-nm_material="' + (c.nm_material || '') + '"'
                  + ' data-no_ros="' + (c.no_ros || '') + '"'
                  + ' data-net_weight="' + (c.net_weight || 0) + '"'
                  + ' data-estimate_qty="' + (c.estimate_qty || 0) + '">'
                  + '<td class="text-center">'
                  + '<input type="checkbox" class="coil-checkbox">'
                  + '</td>'
                  + '<td><code>' + c.no_coil + '</code></td>'
                  + '<td>' + (c.nm_material || '-') + '</td>'
                  + '<td class="text-end fw-bold">' + parseFloat(c.net_weight || 0).toFixed(3) + '</td>'
                  + '<td class="text-end">'
                  + '<span class="badge bg-primary">' + (c.estimate_qty || 0) + ' pcs</span>'
                  + '</td>'
                  + '<td>' + (c.nm_gudang || '-') + '</td>'
                  + '</tr>';
        });

        $block.find('.tbody-coil').html(html);
        $block.find('.lbl-material-name').text(materialNames.join(', ') || '—');
        $block.find('.coil-section').show();
        updateTotalQty($block);
    }, 'json');
}

// ── Hitung total estimate qty dari coil yang dicentang ───────────────────────
function updateTotalQty($block) {
    var total = 0;
    $block.find('.coil-row').each(function () {
        if ($(this).find('.coil-checkbox').is(':checked')) {
            total += parseInt($(this).data('estimate_qty')) || 0;
        }
    });
    $block.find('.lbl-total-qty').text(total);
    syncHiddenInputs($block);
}

// ── Sync hidden inputs untuk coil yang dipilih ───────────────────────────────
function syncHiddenInputs($block) {
    var $container = $block.find('.selected-coil-inputs');
    $container.empty();

    var blockId = $block.attr('id').replace('block-', '');

    $block.find('.coil-row').each(function (i) {
        if (!$(this).find('.coil-checkbox').is(':checked')) return;
        var no_coil     = $(this).data('no_coil');
        var id_material = $(this).data('id_material');
        var nm_material = $(this).data('nm_material');
        var no_ros      = $(this).data('no_ros');
        var net_weight  = $(this).data('net_weight');
        var est_qty     = $(this).data('estimate_qty');

        var prefix = 'detail[' + blockId + '_' + i + ']';
        $container.append(
            '<input type="hidden" name="' + prefix + '[no_coil]" value="' + no_coil + '">' +
            '<input type="hidden" name="' + prefix + '[id_material]" value="' + id_material + '">' +
            '<input type="hidden" name="' + prefix + '[nm_material]" value="' + nm_material + '">' +
            '<input type="hidden" name="' + prefix + '[no_ros]" value="' + no_ros + '">' +
            '<input type="hidden" name="' + prefix + '[net_weight_coil]" value="' + net_weight + '">' +
            '<input type="hidden" name="' + prefix + '[estimasi_fg]" value="' + est_qty + '">'
        );
    });
}

// ── Renumber blok produk ─────────────────────────────────────────────────────
function renumberBlocks() {
    $('#product-blocks .product-block').each(function (i) {
        $(this).find('.block-num').text(i + 1);
        if (i === 0) {
            $(this).find('.btn-remove-block').addClass('d-none');
        } else {
            $(this).find('.btn-remove-block').removeClass('d-none');
        }
    });
}

// ── Event delegation ─────────────────────────────────────────────────────────
$(document).ready(function () {

    // Init blok pertama
    initBlockSelect2($('#block-0'));

    // Cari coil
    $(document).on('click', '.btn-cari-coil', function () {
        loadCoilForBlock($(this).closest('.product-block'));
    });

    // Check all coil dalam satu blok
    $(document).on('change', '.check-all-coil', function () {
        var $block = $(this).closest('.product-block');
        $block.find('.coil-checkbox').prop('checked', this.checked);
        $block.find('.coil-row').toggleClass('coil-row-selected', this.checked);
        updateTotalQty($block);
    });

    // Centang individual coil
    $(document).on('change', '.coil-checkbox', function () {
        var $row   = $(this).closest('.coil-row');
        var $block = $(this).closest('.product-block');
        $row.toggleClass('coil-row-selected', this.checked);
        updateTotalQty($block);
    });

    // Klik baris untuk toggle checkbox
    $(document).on('click', '.coil-row td:not(:first-child)', function () {
        var $cb = $(this).closest('.coil-row').find('.coil-checkbox');
        $cb.prop('checked', !$cb.prop('checked')).trigger('change');
    });

    // Tambah blok produk
    $('#btn-add-product').on('click', function () {
        var tpl = document.getElementById('product-block-template').innerHTML;
        tpl = tpl.replace(/__IDX__/g, blockIndex).replace(/__NUM__/g, blockIndex + 1);
        var $newBlock = $(tpl);
        $('#product-blocks').append($newBlock);
        initBlockSelect2($newBlock);
        blockIndex++;
        renumberBlocks();
    });

    // Hapus blok produk
    $(document).on('click', '.btn-remove-block', function () {
        $(this).closest('.product-block').remove();
        renumberBlocks();
    });

    // Validasi sebelum submit
    $('#form-plan').on('submit', function () {
        var hasCoil = false;
        $('.coil-checkbox:checked').each(function () { hasCoil = true; });
        if (!hasCoil) {
            alert('Pilih minimal satu coil sebelum membuat SPK');
            return false;
        }
    });
});
</script>
