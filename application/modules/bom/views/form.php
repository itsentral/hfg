<?php defined('BASEPATH') || exit('No direct script access allowed');
$is_edit    = isset($bom) && $bom;
$page_title = $is_edit ? 'Edit BOM: ' . $bom->nm_produk : 'Buat BOM Baru';
?>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold"><i class="fa fa-sitemap text-primary me-2"></i> <?= $page_title ?></h5>
        <a href="<?= base_url('bom') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="form-bom" action="<?= base_url('bom/save_bom') ?>" method="POST">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id_bom" id="id_bom" value="<?= $bom->id ?>">
            <?php endif; ?>

            <!-- Header Form: Produk FG & Trade Name Produk Sesuai Mockup -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label fw-bold">Produk FG <span class="text-danger">*</span></label>
                    <select name="id_produk" id="select-produk" class="form-select select2-produk" required <?= $is_edit ? 'disabled' : '' ?>>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($produk_list as $idx => $p): ?>
                            <option value="<?= htmlspecialchars($p->code_lv4) ?>"
                                data-idx="<?= str_pad($idx + 1, 3, '0', STR_PAD_LEFT) ?>"
                                data-nama="<?= htmlspecialchars($p->nama) ?>"
                                data-trade="<?= htmlspecialchars(!empty($p->trade_name) ? $p->trade_name : $p->nama) ?>"
                                <?= ($is_edit && $bom->id_produk == $p->code_lv4) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p->code_lv4) ?> — <?= htmlspecialchars($p->nama) ?>
                                <?php if ($p->nm_lv1 || $p->nm_lv2): ?>
                                    (<?= htmlspecialchars(implode(' / ', array_filter([$p->nm_lv1, $p->nm_lv2, $p->nm_lv3]))) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="id_produk" value="<?= htmlspecialchars($bom->id_produk) ?>">
                    <?php endif; ?>
                    <input type="hidden" name="nm_produk" id="nm_produk" value="<?= $is_edit ? htmlspecialchars($bom->nm_produk) : '' ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Trade Name Produk</label>
                    <input type="text" id="trade-name-produk" class="form-control bg-light" readonly
                        value="<?= $is_edit ? htmlspecialchars(!empty($bom->trade_name) ? $bom->trade_name : $bom->nm_produk) : '' ?>"
                        placeholder="Otomatis dari master">
                </div>
            </div>

            <!-- Card Tabel: Daftar Varian Tebal (Material) Sesuai Mockup BA -->
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3">
                    <h6 class="mb-0 text-white fw-bold"><i class="fa fa-list me-1"></i> Daftar Varian Tebal (Material)</h6>
                    <button type="button" class="btn btn-light btn-sm fw-bold" id="btn-add-variant">
                        <i class="fa fa-plus me-1"></i> Tambah Varian
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0" id="tbl-variant">
                            <thead class="table-light">
                                <tr>
                                    <th width="45" class="text-center">No</th>
                                    <th width="180">No. Uniq ID <span class="text-danger">*</span></th>
                                    <th width="38%">Material <span class="text-danger">*</span></th>
                                    <th width="120" class="text-end">Varian Tebal</th>
                                    <th width="120" class="text-end">Lebar Coil</th>
                                    <th width="160" class="text-end">Berat/Meter (kg/m)</th>
                                    <th width="60" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-variant">
                                <?php if ($is_edit && !empty($details)): ?>
                                    <?php foreach ($details as $i => $d): ?>
                                    <tr data-index="<?= $i ?>">
                                        <td class="text-center row-no"><?= $i + 1 ?></td>
                                        <td>
                                            <input type="text" name="detail[<?= $i ?>][no_uniq_id]"
                                                class="form-control form-control-sm variant-uniq" required
                                                value="<?= htmlspecialchars(!empty($d->no_uniq_id) ? $d->no_uniq_id : 'Type 001.' . ($i + 1)) ?>">
                                        </td>
                                        <td>
                                            <select name="detail[<?= $i ?>][id_material]"
                                                class="form-select form-select-sm select2-material" required>
                                                <option value="">-- Pilih Material --</option>
                                                <?php foreach ($material_list as $m): ?>
                                                    <option value="<?= htmlspecialchars($m->code_lv4) ?>"
                                                        data-nama="<?= htmlspecialchars($m->nama) ?>"
                                                        data-thick="<?= (float) $m->thickness ?>"
                                                        data-width="<?= (float) $m->width ?>"
                                                        data-unit="<?= htmlspecialchars($m->id_unit ?: '') ?>"
                                                        data-nm-unit="<?= htmlspecialchars($m->nm_unit ?: '') ?>"
                                                        <?= ($d->id_material == $m->code_lv4) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($m->code_lv4) ?> — <?= htmlspecialchars($m->nama) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="detail[<?= $i ?>][nm_material]" class="inp-nm-material" value="<?= htmlspecialchars($d->nm_material ?: '') ?>">
                                            <input type="hidden" name="detail[<?= $i ?>][id_unit]" class="inp-id-unit" value="<?= htmlspecialchars($d->id_unit ?: '') ?>">
                                            <input type="hidden" name="detail[<?= $i ?>][nm_unit]" class="inp-nm-unit" value="<?= htmlspecialchars($d->nm_unit ?: '') ?>">
                                            <input type="hidden" name="detail[<?= $i ?>][qty]" value="1">
                                            <input type="hidden" name="detail[<?= $i ?>][berat_per_meter]" class="inp-bpm" value="<?= number_format($d->berat_per_meter, 6, '.', '') ?>">
                                        </td>
                                        <td class="text-end col-thick">
                                            <?= number_format(isset($d->thickness) ? $d->thickness : 0, 3, '.', '') ?>
                                        </td>
                                        <td class="text-end col-width">
                                            <?= number_format(isset($d->width) ? $d->width : 0, 2, '.', '') ?>
                                        </td>
                                        <td class="text-end fw-bold text-success col-bpm">
                                            <?= number_format($d->berat_per_meter, 4, '.', '') ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm btn-del-row" title="Hapus">
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

            <!-- Keterangan Formula Sesuai Mockup -->
            <div class="alert alert-light border py-2 mb-4 text-muted small">
                <i class="fa fa-info-circle me-1"></i> <strong>Berat/Meter (otomatis, read-only)</strong> = <code>((Tebal Material &minus; Standar Toleransi) &times; Lebar Coil &times; Standar Berat Jenis &divide; 1000) + Coating</code> &middot; nilai standar dari Master Standard Material (Toleransi <?= number_format($standard_mat->standar_toleransi, 2) ?>, Berat Jenis <?= number_format($standard_mat->standar_berat_jenis, 2) ?>, Coating <?= number_format($standard_mat->coating, 2) ?>). Material yang sudah dipakai varian lain pada produk ini tidak muncul di pilihan (tidak boleh dobel).
            </div>

            <!-- Tombol Action Simpan & Batal -->
            <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa fa-save me-1"></i> Simpan BOM
                </button>
                <a href="<?= base_url('bom') ?>" class="btn btn-secondary px-4">Batal</a>
            </div>
        </form>
    </div>
</div>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    var std = {
        toleransi: <?= (float) $standard_mat->standar_toleransi ?>,
        bj:        <?= (float) $standard_mat->standar_berat_jenis ?>,
        coating:   <?= (float) $standard_mat->coating ?>
    };

    var materialList = <?= json_encode($material_list) ?>;
    var rowIndex = <?= ($is_edit && !empty($details)) ? count($details) : 0 ?>;

    // Inisialisasi Select2 Produk
    $('#select-produk').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Produk --',
        width: '100%'
    });

    function initSelect2Material(elem) {
        elem.select2({
            theme: 'bootstrap-5',
            placeholder: '-- Pilih Material --',
            width: '100%'
        });
    }

    $('.select2-material').each(function() {
        initSelect2Material($(this));
    });

    // Event saat Produk FG dipilih
    $('#select-produk').on('change', function() {
        var opt = $(this).find(':selected');
        var nama = opt.data('nama') || '';
        var trade = opt.data('trade') || nama;
        var pIdx = opt.data('idx') || '001';

        $('#nm_produk').val(nama);
        $('#trade-name-produk').val(trade);

        // Update Uniq ID pada baris-baris varian jika belum diisi custom
        $('#tbody-variant tr').each(function(i) {
            var rowNo = i + 1;
            $(this).find('.variant-uniq').val('Type ' + pIdx + '.' + rowNo);
        });
    });

    // Fungsi hitung Berat/Meter
    function calcBPM(thick, width) {
        if (!thick || !width || thick <= 0 || width <= 0) return 0;
        var bpm = (((thick - std.toleransi) * width * std.bj) / 1000) + std.coating;
        return bpm > 0 ? bpm : 0;
    }

    // Event saat Material di baris varian dipilih
    $(document).on('change', '.select2-material', function() {
        var tr = $(this).closest('tr');
        var opt = $(this).find(':selected');
        var thick = parseFloat(opt.data('thick')) || 0;
        var width = parseFloat(opt.data('width')) || 0;
        var nama  = opt.data('nama') || '';
        var unit  = opt.data('unit') || '';
        var nmUnit = opt.data('nm-unit') || '';

        tr.find('.inp-nm-material').val(nama);
        tr.find('.inp-id-unit').val(unit);
        tr.find('.inp-nm-unit').val(nmUnit);

        tr.find('.col-thick').text(thick > 0 ? thick.toFixed(3) : '—');
        tr.find('.col-width').text(width > 0 ? width.toFixed(2) : '—');

        var bpm = calcBPM(thick, width);
        tr.find('.col-bpm').text(bpm > 0 ? bpm.toFixed(4) : '—');
        tr.find('.inp-bpm').val(bpm.toFixed(6));
    });

    // Tambah Varian Baru
    $('#btn-add-variant').on('click', function() {
        var pOpt = $('#select-produk').find(':selected');
        var pIdx = pOpt.data('idx') || '001';
        var currentRows = $('#tbody-variant tr').length;
        var nextRow = currentRows + 1;
        var uniqId = 'Type ' + pIdx + '.' + nextRow;

        var matOptions = '<option value="">-- Pilih Material --</option>';
        materialList.forEach(function(m) {
            matOptions += '<option value="' + m.code_lv4 + '"'
                + ' data-nama="' + (m.nama || '') + '"'
                + ' data-thick="' + (parseFloat(m.thickness) || 0) + '"'
                + ' data-width="' + (parseFloat(m.width) || 0) + '"'
                + ' data-unit="' + (m.id_unit || '') + '"'
                + ' data-nm-unit="' + (m.nm_unit || '') + '">'
                + m.code_lv4 + ' — ' + m.nama + '</option>';
        });

        var html = '<tr data-index="' + rowIndex + '">'
            + '<td class="text-center row-no">' + nextRow + '</td>'
            + '<td><input type="text" name="detail[' + rowIndex + '][no_uniq_id]" class="form-control form-control-sm variant-uniq" required value="' + uniqId + '"></td>'
            + '<td>'
            +   '<select name="detail[' + rowIndex + '][id_material]" class="form-select form-select-sm select2-material" required>'
            +     matOptions
            +   '</select>'
            +   '<input type="hidden" name="detail[' + rowIndex + '][nm_material]" class="inp-nm-material">'
            +   '<input type="hidden" name="detail[' + rowIndex + '][id_unit]" class="inp-id-unit">'
            +   '<input type="hidden" name="detail[' + rowIndex + '][nm_unit]" class="inp-nm-unit">'
            +   '<input type="hidden" name="detail[' + rowIndex + '][qty]" value="1">'
            +   '<input type="hidden" name="detail[' + rowIndex + '][berat_per_meter]" class="inp-bpm" value="0.000000">'
            + '</td>'
            + '<td class="text-end col-thick">—</td>'
            + '<td class="text-end col-width">—</td>'
            + '<td class="text-end fw-bold text-success col-bpm">—</td>'
            + '<td class="text-center">'
            +   '<button type="button" class="btn btn-danger btn-sm btn-del-row" title="Hapus"><i class="fa fa-trash"></i></button>'
            + '</td>'
            + '</tr>';

        $('#tbody-variant').append(html);
        initSelect2Material($('#tbody-variant tr:last .select2-material'));
        rowIndex++;
    });

    // Hapus Baris Varian
    $(document).on('click', '.btn-del-row', function() {
        if ($('#tbody-variant tr').length <= 1) {
            alert('Minimal harus ada 1 varian material.');
            return;
        }
        $(this).closest('tr').remove();
        // Re-index nomor urut
        $('#tbody-variant tr').each(function(i) {
            $(this).find('.row-no').text(i + 1);
        });
    });

    // Jika form add baru dan tabel masih kosong, tambahkan 1 baris otomatis
    if ($('#tbody-variant tr').length === 0) {
        $('#btn-add-variant').trigger('click');
    }

    // Validasi form submit
    $('#form-bom').on('submit', function(e) {
        if ($('#tbody-variant tr').length === 0) {
            e.preventDefault();
            alert('Tambahkan minimal 1 varian material.');
            return false;
        }

        // Cek duplikasi material pada produk yang sama
        var selectedMats = [];
        var hasDup = false;
        $('.select2-material').each(function() {
            var val = $(this).val();
            if (val) {
                if (selectedMats.indexOf(val) !== -1) {
                    hasDup = true;
                }
                selectedMats.push(val);
            }
        });

        if (hasDup) {
            e.preventDefault();
            alert('Material yang sama tidak boleh dipakai dua kali pada produk ini.');
            return false;
        }

        // Cek duplikasi no_uniq_id pada produk yang sama
        var selectedUniqs = [];
        var hasDupUniq = false;
        $('.variant-uniq').each(function() {
            var val = $(this).val().trim();
            if (val) {
                if (selectedUniqs.indexOf(val) !== -1) {
                    hasDupUniq = true;
                }
                selectedUniqs.push(val);
            }
        });

        if (hasDupUniq) {
            e.preventDefault();
            alert('No. Uniq ID (Varian BOM) tidak boleh duplikat pada produk yang sama.');
            return false;
        }
    });
});
</script>
