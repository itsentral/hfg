<?php

/**
 * form.php — Incoming Material
 * Mode ADD       : akses via incoming/add/{no_ros}       → $ros_data ada, $detail_ros kosong
 * Mode EDIT_DRAFT: akses via incoming/edit_draft/{no_ros} → $ros_data ada, $draft_coils ada
 * Mode VIEW      : akses via incoming/view/{kode}        → $detail_ros ada, $ros_data kosong
 */

$is_view_mode = !empty($detail_ros);
?>

<style>
    #table-coil th,
    #table-coil td {
        vertical-align: middle !important;
        font-size: 12px;
    }

    input.hitung-selisih {
        font-weight: bold;
        background-color: #fff9c4;
    }
</style>

<div class="card">
    <div class="card-body">

        <?php if ($is_view_mode): ?>
            <!-- ====================================================
                 MODE VIEW — Detail Incoming yang sudah diproses
            ===================================================== -->
            <?php
            // Ambil data baris pertama untuk info header
            $first_row      = !empty($detail_ros) ? $detail_ros[0] : [];
            $view_supplier  = $first_row['nm_supplier'] ?? '-';
            $view_no_ros    = $first_row['no_ros'] ?? '-';
            ?>
            <div class="row mb-3">
                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div class="form-group row mb-2">
                        <div class="col-md-4"><label class="col-form-label fw-bold">Supplier</label></div>
                        <div class="col-md-8">
                            <p class="form-control-plaintext"><?= htmlspecialchars($view_supplier) ?></p>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <div class="col-md-4"><label class="col-form-label fw-bold">No. PO</label></div>
                        <div class="col-md-8">
                            <p class="form-control-plaintext"><?= htmlspecialchars($no_surat ?? '-') ?></p>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <div class="col-md-4"><label class="col-form-label fw-bold">No. ROS</label></div>
                        <div class="col-md-8">
                            <p class="form-control-plaintext"><?= htmlspecialchars($view_no_ros) ?></p>
                        </div>
                    </div>
                </div>
                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <div class="form-group row mb-2">
                        <div class="col-md-4"><label class="col-form-label fw-bold">Tgl. Penerimaan</label></div>
                        <div class="col-md-8">
                            <p class="form-control-plaintext"><?= htmlspecialchars($tanggal ?? '-') ?></p>
                        </div>
                    </div>
                    <div class="form-group row mb-2">
                        <div class="col-md-4"><label class="col-form-label fw-bold">Dokumen</label></div>
                        <div class="col-md-8">
                            <?php if (!empty($file_incoming_material)): ?>
                                <div class="d-flex flex-column gap-1 pt-2">
                                    <?php foreach (explode('|', $file_incoming_material) as $f):
                                        if (file_exists($f)): ?>
                                            <a href="<?= base_url($f) ?>" target="_blank">
                                                <i class="fa fa-download"></i> <?= basename($f) ?>
                                            </a>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="form-control-plaintext text-muted">Tidak ada dokumen.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <hr>

            <table id="table-view" class="table table-bordered table-condensed" width="100%">
                <thead class="bg-blue">
                    <tr>
                        <th class="text-center" rowspan="2" style="vertical-align:middle;" width="3%">No</th>
                        <th class="text-center" rowspan="2" style="vertical-align:middle;" width="20%">Material</th>
                        <th class="text-center" rowspan="2" style="vertical-align:middle;" width="6%">Unit</th>
                        <th class="text-center" rowspan="2" style="vertical-align:middle;" width="8%">Qty PO</th>
                        <th class="text-center" colspan="4" style="background-color:#69c79d !important;">Data ROS (Packing List)</th>
                        <th class="text-center" rowspan="2" style="vertical-align:middle; background-color:#f3b44e !important;" width="8%">Status QC</th>
                        <th class="text-center" rowspan="2" style="vertical-align:middle; background-color:#c8e6c9 !important;" width="10%">Gudang Tujuan</th>
                    </tr>
                    <tr>
                        <th class="text-center" style="background-color:#69c79d !important;">No. Coil</th>
                        <th class="text-center" style="background-color:#69c79d !important;">Berat Kotor</th>
                        <th class="text-center" style="background-color:#69c79d !important;">Berat Bersih</th>
                        <th class="text-center" style="background-color:#69c79d !important;">Panjang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($detail_ros)):
                        $grouped = [];
                        foreach ($detail_ros as $item) {
                            // Gunakan id_material sebagai key grouping karena query view()
                            // tidak men-SELECT id_po_detail
                            $group_key = $item['id_material'] ?? $item['id_barang'] ?? uniqid();
                            $grouped[$group_key][] = $item;
                        }
                        $no = 1;
                        foreach ($grouped as $id_po => $rows):
                            foreach ($rows as $idx => $row): ?>
                                <tr>
                                    <?php if ($idx === 0):
                                        $rowspan = count($rows); ?>
                                        <td class="text-center" rowspan="<?= $rowspan ?>" style="vertical-align:middle;"><?= $no ?></td>
                                        <td rowspan="<?= $rowspan ?>" style="vertical-align:middle;">
                                            <b><?= htmlspecialchars($row['nm_material'] ?? $row['nm_barang'] ?? '-') ?></b><br>
                                            <small class="text-muted"><?= htmlspecialchars($row['id_material'] ?? $row['id_barang'] ?? '') ?></small>
                                        </td>
                                        <td class="text-center" rowspan="<?= $rowspan ?>" style="vertical-align:middle;">Kg</td>
                                        <td class="text-right" rowspan="<?= $rowspan ?>" style="vertical-align:middle;"><?= number_format((float)($row['qty_order'] ?? 0), 2) ?></td>
                                    <?php endif; ?>
                                    <td class="text-center bg-light"><?= htmlspecialchars($row['no_coil'] ?? '-') ?></td>
                                    <td class="text-right bg-light"><?= number_format((float)($row['berat_kotor'] ?? 0), 2) ?></td>
                                    <td class="text-right bg-light"><?= number_format((float)($row['berat_bersih'] ?? 0), 2) ?></td>
                                    <td class="text-right bg-light"><?= number_format((float)($row['length'] ?? 0), 2) ?></td>
                                    <td class="text-center">
                                        <?php
                                        $qc    = strtoupper($row['status_qc'] ?? 'OK');
                                        $badge = $qc === 'OK' ? 'success' : 'danger';
                                        echo "<span class='badge bg-{$badge}'>{$qc}</span>";
                                        ?>
                                    </td>
                                    <td class="text-center"><?= htmlspecialchars($row['kd_gudang'] ?? $row['kd_gudang_ke'] ?? '-') ?></td>
                                </tr>
                        <?php endforeach;
                            $no++;
                        endforeach;
                    else: ?>
                        <tr>
                            <td colspan="10" class="text-center">Data tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="text-center mt-3">
                <a href="<?= base_url('incoming') ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>

        <?php else: ?>
            <!-- ====================================================
                 MODE ADD / EDIT DRAFT — Form input proses incoming
            ===================================================== -->

            <?php
            // Ambil data supplier untuk ditampilkan sebagai teks
            $nm_supplier_display = '';
            if (!empty($ros_data)) {
                foreach ($list_supplier as $sup) {
                    if ($sup->kode_supplier == $ros_data->id_supplier) {
                        $nm_supplier_display = $sup->nama;
                        break;
                    }
                }
            }

            // Ambil no_surat PO untuk ditampilkan sebagai teks (dari DB sudah ada di ros_data->no_po)
            $no_po_display = $ros_data->no_surat ?? '-';
            $no_ros_display = $ros_data->id ?? '-';
            ?>

            <form action="" id="data-form" enctype="multipart/form-data">

                <!-- Hidden fields — nilai tetap dikirim ke server -->
                <input type="hidden" name="id_supplier" value="<?= htmlspecialchars($ros_data->id_supplier ?? '') ?>">
                <input type="hidden" name="no_po" value="<?= htmlspecialchars($ros_data->no_po ?? '') ?>">
                <input type="hidden" name="no_ros" value="<?= htmlspecialchars($ros_data->id ?? '') ?>">
                <input type="hidden" name="uang_muka" id="uang_muka" value="">
                <input type="hidden" name="uang_muka_idr" id="uang_muka_idr" value="">

                <div class="col-md-12">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-md-6">
                            <div class="form-group row mb-3">
                                <div class="col-md-4"><label class="col-form-label">Supplier</label></div>
                                <div class="col-md-8">
                                    <p class="form-control-plaintext fw-semibold">
                                        <?= htmlspecialchars($nm_supplier_display ?: '-') ?>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <div class="col-md-4"><label class="col-form-label">No. PO</label></div>
                                <div class="col-md-8">
                                    <p class="form-control-plaintext fw-semibold" id="no-po-display">
                                        <?= htmlspecialchars($no_po_display) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <div class="col-md-4"><label class="col-form-label">No. ROS</label></div>
                                <div class="col-md-8">
                                    <p class="form-control-plaintext fw-semibold">
                                        <?= htmlspecialchars($no_ros_display) ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-md-6">
                            <div class="form-group row mb-3">
                                <div class="col-md-4"><label class="col-form-label">Tgl. Incoming</label></div>
                                <div class="col-md-8">
                                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <div class="col-md-4"><label class="col-form-label">Upload Document</label></div>
                                <div class="col-md-8">
                                    <div class="d-flex flex-column gap-2">

                                        <?php
                                        // File lama hanya ada di mode edit_draft
                                        $existing_original = $ros_data->file_original ?? '';
                                        $existing_hash     = $ros_data->file_hash     ?? '';
                                        ?>

                                        <?php if (!empty($existing_original) && !empty($existing_hash) && file_exists($existing_hash)): ?>
                                            <!-- Tampilkan file yang sudah tersimpan -->
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="fa fa-file-alt text-primary"></i>
                                                <a href="<?= base_url($existing_hash) ?>" target="_blank" class="small">
                                                    <?= htmlspecialchars($existing_original) ?>
                                                </a>
                                                <span class="badge bg-secondary">Tersimpan</span>
                                            </div>
                                            <small class="text-warning">
                                                <i class="fa fa-info-circle"></i>
                                                Upload file baru untuk <b>mengganti</b> file lama.
                                            </small>
                                        <?php endif; ?>

                                        <!-- Input upload — selalu tampil -->
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <input type="file" name="file_incoming_material[]" id="file_incoming_material"
                                                class="d-none" accept=".pdf,.jpg,.jpeg,.png" multiple>
                                            <button type="button" class="btn btn-outline-warning" id="btnPickFile">
                                                <i class="ti ti-upload me-1"></i>
                                                <?= !empty($existing_original) ? 'Ganti File' : 'Choose File' ?>
                                            </button>
                                            <span class="text-muted" id="docFileName">No file chosen</span>
                                            <button type="button" class="btn btn-light border" id="btnClearFile" style="display:none;">
                                                <i class="ti ti-x me-1"></i> Clear
                                            </button>
                                        </div>

                                        <!-- Hidden: kirim nilai file lama ke server jika tidak ada upload baru -->
                                        <input type="hidden" name="existing_file_original" value="<?= htmlspecialchars($existing_original) ?>">
                                        <input type="hidden" name="existing_file_hash" value="<?= htmlspecialchars($existing_hash) ?>">

                                        <small class="text-muted">Allowed: PDF/JPG/PNG. Max 2MB.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /row -->

                    <hr>

                    <!-- Tabel Coil -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="table-coil">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;" width="3%">No</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;" width="15%">Material</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;" width="8%">Qty Order</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;" width="5%">Uom</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle;" width="8%">Qty Belum Kirim</th>
                                    <th colspan="3" class="text-center" style="background-color:#d2d6de !important; color:#000;">Dari Data ROS (Packing List)</th>
                                    <th colspan="2" class="text-center" style="background-color:#f3b44e !important;">Checklist Visual</th>
                                    <th rowspan="2" class="text-center" style="vertical-align:middle; background-color:#c8e6c9 !important; color:#000;" width="14%">Gudang Tujuan</th>
                                </tr>
                                <tr>
                                    <th class="text-center" style="background-color:#d2d6de !important; color:#000;">No. Coil</th>
                                    <th class="text-center" style="background-color:#d2d6de !important; color:#000;">Berat Kotor</th>
                                    <th class="text-center" style="background-color:#d2d6de !important; color:#000;">Berat Bersih</th>
                                    <th class="text-center" style="background-color:#f3b44e !important;">OK</th>
                                    <th class="text-center" style="background-color:#f3b44e !important;">Reject</th>
                                </tr>
                            </thead>
                            <tbody id="list-item-coil">
                                <tr>
                                    <td colspan="11" class="text-center">
                                        <i class="fa fa-spinner fa-spin"></i> Memuat data coil...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="text-center mt-3 d-flex gap-2 justify-content-center">
                        <a href="<?= base_url('incoming') ?>" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-primary" id="save-draft">
                            <i class="fa fa-save"></i>
                            <?= $page_mode === 'edit_draft' ? 'Update Draft' : 'Simpan Draft' ?>
                        </button>
                        <?php if ($page_mode === 'edit_draft' && !empty($no_ros_default)): ?>
                            <?php $coil_ids_print = implode('-', array_column($draft_coils, 'id_ros_coil_detail')); ?>
                            <a href="<?= base_url('incoming/print_qr/' . $coil_ids_print) ?>" target="_blank" class="btn btn-info">
                                <i class="fa fa-print"></i> Print Label
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </form>
        <?php endif; ?>

    </div><!-- /card-body -->
</div><!-- /card -->

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (!$is_view_mode): ?>
    <script>
        $(document).ready(function() {

            /* ── File upload handler ── */
            (function() {
                var input = document.getElementById('file_incoming_material');
                var btnPick = document.getElementById('btnPickFile');
                var btnClr = document.getElementById('btnClearFile');
                var label = document.getElementById('docFileName');
                if (!input || !btnPick) return;
                btnPick.addEventListener('click', function() {
                    input.click();
                });
                input.addEventListener('change', function() {
                    label.textContent = (input.files && input.files.length) ? input.files[0].name : 'No file chosen';
                    btnClr.style.display = (input.files && input.files.length) ? 'inline-flex' : 'none';
                });
                if (btnClr) btnClr.addEventListener('click', function() {
                    input.value = '';
                    label.textContent = 'No file chosen';
                    btnClr.style.display = 'none';
                });
            })();

            /* ── Prefill uang muka dari PO (ambil sekali via AJAX, simpan ke hidden) ── */
            (function() {
                var no_po = '<?= addslashes($ros_data->no_po ?? '') ?>';
                var id_supplier = '<?= addslashes($ros_data->id_supplier ?? '') ?>';
                if (!no_po || !id_supplier) return;

                $.ajax({
                    url: siteurl + active_controller + 'get_po_by_supplier',
                    type: 'POST',
                    data: {
                        id_supplier: id_supplier
                    },
                    dataType: 'json',
                    success: function(data) {
                        data.forEach(function(item) {
                            if (item.no_po === no_po) {
                                $('#uang_muka').val(item.uang_muka || '');
                                $('#uang_muka_idr').val(item.uang_muka_idr || '');
                            }
                        });
                    }
                });
            })();

            /* ── Load tabel coil langsung saat halaman dibuka ── */
            <?php if ($page_mode === 'edit_draft'): ?>
                var draftCoilsMap = <?= json_encode($draft_coils_map ?? []) ?>;
            <?php endif; ?>

            function loadCoilTable(no_ros) {
                $('#list-item-coil').html(
                    '<tr><td colspan="11" class="text-center">' +
                    '<i class="fa fa-spinner fa-spin"></i> Memuat data...</td></tr>'
                );

                var listGudang = <?= json_encode($list_gudang) ?>;

                $.ajax({
                    url: siteurl + active_controller + 'get_ros_detail_to_table',
                    type: 'POST',
                    data: {
                        no_ros: no_ros
                    },
                    dataType: 'json',
                    success: function(data) {
                        var html = '';
                        var currentMaterial = '';
                        var rowCounter = 0;

                        if (data && data.length > 0) {
                            data.forEach(function(item, index) {

                                <?php if ($page_mode === 'edit_draft'): ?>
                                    var saved = draftCoilsMap[item.id_ros_coil_detail] || {};
                                    var savedGudang = saved.id_gudang_ke || '';
                                    var savedQC = saved.status_qc || 'OK';
                                <?php else: ?>
                                    var savedGudang = '';
                                    var savedQC = 'OK';
                                <?php endif; ?>

                                var rowMaterial = '';
                                if (item.id_material !== currentMaterial) {
                                    var qty_belum = (parseFloat(item.qty_po) || 0) - (parseFloat(item.qty_in) || 0);
                                    rowCounter++;
                                    rowMaterial =
                                        '<td class="text-center" style="vertical-align:top;">' + rowCounter + '</td>' +
                                        '<td style="vertical-align:top;"><b>' + item.nm_material + '</b></td>' +
                                        '<td class="text-right" style="vertical-align:top;">' + (parseFloat(item.qty_po) || 0).toLocaleString('id-ID') + '</td>' +
                                        '<td class="text-center" style="vertical-align:top;">Kg</td>' +
                                        '<td class="text-right" style="vertical-align:top;">' + qty_belum.toLocaleString('id-ID') + '</td>';
                                    currentMaterial = item.id_material;
                                } else {
                                    rowMaterial = '<td colspan="5" style="border-top:none;border-bottom:none;"></td>';
                                }

                                var gudangOptsCoil = '<option value="">-- Pilih --</option>';
                                listGudang.forEach(function(g) {
                                    var isSelected = (String(g.id) === String(savedGudang)) ? ' selected' : '';
                                    gudangOptsCoil += '<option value="' + g.id + '" data-kd="' + g.kd_gudang + '"' + isSelected + '>' +
                                        g.nm_gudang + ' (' + g.kd_gudang + ')</option>';
                                });

                                <?php $savedKdGudang = $page_mode === 'edit_draft' ? 'saved.kd_gudang_ke || ""' : '""'; ?>

                                html +=
                                    '<tr>' +
                                    rowMaterial +
                                    '<td class="text-center bg-light">' + item.no_coil + '</td>' +
                                    '<td class="text-end bg-light">' + parseFloat(item.ros_kotor).toLocaleString('id-ID') + '</td>' +
                                    '<td class="text-end bg-light">' + parseFloat(item.ros_bersih).toLocaleString('id-ID') + '</td>' +
                                    '<td class="text-center">' +
                                    '<input type="radio" name="detail[' + index + '][status_qc]" value="OK"' + (savedQC === 'OK' ? ' checked' : '') + '>' +
                                    '</td>' +
                                    '<td class="text-center">' +
                                    '<input type="radio" name="detail[' + index + '][status_qc]" value="REJECT"' + (savedQC === 'REJECT' ? ' checked' : '') + '>' +
                                    '<input type="hidden" name="detail[' + index + '][id_ros_header]"   value="' + item.no_ros + '">' +
                                    '<input type="hidden" name="detail[' + index + '][id_ros_material]" value="' + item.id_ros_material + '">' +
                                    '<input type="hidden" name="detail[' + index + '][id_ros_coil]"     value="' + item.id_ros_coil_detail + '">' +
                                    '<input type="hidden" name="detail[' + index + '][id_po_detail]"    value="' + item.id_po_detail + '">' +
                                    '<input type="hidden" name="detail[' + index + '][id_material]"     value="' + item.id_material + '">' +
                                    '<input type="hidden" name="detail[' + index + '][no_coil]"         value="' + item.no_coil + '">' +
                                    '<input type="hidden" name="detail[' + index + '][aktual_bersih]"   value="' + item.ros_bersih + '">' +
                                    '</td>' +
                                    '<td class="text-center" style="background-color:#f1f8e9;">' +
                                    '<select name="detail[' + index + '][id_gudang_ke]" class="form-control form-control-sm select-gudang-coil" required>' +
                                    gudangOptsCoil +
                                    '</select>' +
                                    '<input type="hidden" name="detail[' + index + '][kd_gudang_ke]" class="kd-gudang-coil" value="' + (<?= $savedKdGudang ?>) + '">' +
                                    '</td>' +
                                    '</tr>';
                            });
                        } else {
                            html = '<tr><td colspan="11" class="text-center text-warning">Data Coil tidak ditemukan untuk ROS ini.</td></tr>';
                        }

                        $('#list-item-coil').html(html);
                        $('.select-gudang-coil').select2({
                            width: '100%'
                        });
                    },
                    error: function(xhr) {
                        console.error('Error load coil:', xhr.responseText);
                        $('#list-item-coil').html(
                            '<tr><td colspan="11" class="text-center text-danger">Gagal memuat data coil.</td></tr>'
                        );
                    }
                });
            }

            /* ── Auto-load coil saat halaman dibuka ── */
            <?php if (!empty($no_ros_default) && !empty($ros_data)): ?>
                loadCoilTable('<?= addslashes($ros_data->id) ?>');
            <?php endif; ?>

            /* ── Sync kd_gudang per baris ── */
            $(document).on('change', '.select-gudang-coil', function() {
                var kd = $(this).find('option:selected').data('kd') || '';
                $(this).siblings('.kd-gudang-coil').val(kd);
            });

            /* ── SAVE DRAFT ── */
            $(document).on('click', '#save-draft', function(e) {
                e.preventDefault();

                var gudangKosong = false;
                $('.select-gudang-coil').each(function() {
                    if (!$(this).val()) gudangKosong = true;
                });
                if (gudangKosong) {
                    Swal.fire({
                        title: 'Peringatan',
                        text: 'Semua coil harus dipilih gudang tujuannya!',
                        icon: 'warning'
                    });
                    return;
                }

                var endpoint = '<?= $page_mode === "edit_draft" ? "update_draft" : "save_draft" ?>';

                Swal.fire({
                    title: 'Simpan Draft?',
                    text: 'Data gudang dan QC per coil akan disimpan. Anda masih bisa mengubahnya nanti.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan Draft!',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: siteurl + active_controller + endpoint,
                        type: 'POST',
                        data: new FormData($('#data-form')[0]),
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                Swal.fire({
                                    title: 'Draft Tersimpan!',
                                    html: res.pesan + '<br><br><b>Apakah ingin print label sekarang?</b>',
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonText: '<i class="fa fa-print"></i> Print Label',
                                    cancelButtonText: 'Nanti Saja'
                                }).then(function(r2) {
                                    if (r2.isConfirmed && res.print_url) {
                                        window.open(res.print_url, '_blank');
                                    }
                                    window.location.href = siteurl + active_controller;
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: res.pesan,
                                    icon: 'error'
                                });
                            }
                        }
                    });
                });
            });

            /* ── SAVE FINALISASI (process_incoming_coil) ── */
            $(document).on('click', '#save-incoming', function(e) {
                e.preventDefault();

                var gudangKosong = false;
                $('.select-gudang-coil').each(function() {
                    if (!$(this).val()) gudangKosong = true;
                });
                if (gudangKosong) {
                    Swal.fire({
                        title: 'Peringatan',
                        text: 'Semua coil harus dipilih gudang tujuannya!',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Apakah Anda Yakin?',
                    text: 'Data akan diproses ke stok dan jurnal akuntansi!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Proses!',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: siteurl + active_controller + 'process_incoming_coil',
                        type: 'POST',
                        data: new FormData($('#data-form')[0]),
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                Swal.fire({
                                        title: 'Berhasil!',
                                        text: res.pesan,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    })
                                    .then(function() {
                                        window.location.href = siteurl + active_controller;
                                    });
                            } else if (res.status == 2) {
                                Swal.fire({
                                        title: 'Transaksi Tersimpan',
                                        text: res.pesan,
                                        icon: 'warning',
                                        confirmButtonText: 'OK'
                                    })
                                    .then(function() {
                                        window.location.href = siteurl + active_controller;
                                    });
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: res.pesan,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error',
                                text: 'Terjadi kesalahan koneksi server.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                });
            });

        });
    </script>
<?php endif; ?>