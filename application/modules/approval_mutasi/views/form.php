<?php
$ENABLE_MANAGE = has_permission('Approval_mutasi.Manage');

$is_approval = ($mode === 'approval');
$is_view     = ($mode === 'view');

$readonly = ($is_approval || $is_view) ? 'readonly' : '';
$disabled = ($is_approval || $is_view) ? 'disabled' : '';

$title_map  = ['approval' => 'Proses Approval Mutasi', 'view' => 'Detail Mutasi'];
$page_title = $title_map[$mode] ?? 'Form Approval Mutasi';

$m       = $mutation ?? [];
$details = $m['details'] ?? [];
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= $page_title ?></h5>
        <a href="<?= site_url('approval_mutasi') ?>" class="btn btn-sm btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card-body">

        <?php if (!empty($m)): ?>
            <div class="p-3 bg-light border rounded mb-4 w-100">
                <div class="row align-items-center g-3 m-0">
                    <div class="<?= empty($m['reject_reason']) ? 'col-12' : 'col-md-7 col-12' ?> p-0">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 w-100">
                            <div class="px-2 flex-fill">
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">No. Mutasi</small>
                                <span class="fs-6 fw-bold text-dark"><?= $m['mutation_number'] ?></span>
                            </div>
                            <div class="px-2 flex-fill border-start-custom">
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Tanggal</small>
                                <span class="text-dark fw-semibold"><?= date('d/m/Y', strtotime($m['mutation_date'])) ?></span>
                            </div>
                            <div class="px-2 flex-fill border-start-custom">
                                <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Status</small>
                                <?php
                                $status_map = [
                                    0 => ['Open', 'primary'],
                                    1 => ['Menunggu Approve', 'warning'],
                                    2 => ['Approved', 'success'],
                                    4 => ['Done', 'dark'],
                                    5 => ['Cancelled', 'secondary'],
                                    6 => ['Revisi', 'info'],
                                ];
                                $st = $status_map[$m['status']] ?? ['-', 'secondary'];
                                ?>
                                <span class="badge bg-<?= $st[1] ?> px-2 py-1"><?= $st[0] ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($m['reject_reason'])): ?>
                        <div class="col-md-5 col-12 border-start-md ps-md-4 py-1 text-start">
                            <small class="text-muted d-block text-uppercase font-size-xs fw-bold">Catatan Pengembalian / Revisi</small>
                            <span class="text-info fw-semibold">
                                <i class="fa-solid fa-circle-exclamation me-1"></i> <?= $m['reject_reason'] ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <form id="formMutasi" enctype="multipart/form-data">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">No. Berita Acara <span class="text-danger">*</span></label>
                    <input type="text" id="no_berita_acara" name="no_berita_acara" class="form-control" <?= $readonly ?> value="<?= $m['no_berita_acara'] ?? '' ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Gudang Asal <span class="text-danger">*</span></label>
                    <select id="id_gudang_from" class="form-select" <?= $disabled ?>>
                        <option value="">-- Pilih Gudang Asal --</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>" <?= (isset($m['id_gudang_from']) && $m['id_gudang_from'] == $wh['id']) ? 'selected' : '' ?>><?= $wh['nm_gudang'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Gudang Tujuan <span class="text-danger">*</span></label>
                    <select id="id_gudang_to" class="form-select" <?= $disabled ?>>
                        <option value="">-- Pilih Gudang Tujuan --</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh['id'] ?>" <?= (isset($m['id_gudang_to']) && $m['id_gudang_to'] == $wh['id']) ? 'selected' : '' ?>><?= $wh['nm_gudang'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                    <input type="text" id="description" name="description" class="form-control" <?= $readonly ?> value="<?= $m['description'] ?? '' ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Attach File</label>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <?php if (!empty($m['file_name_hash'])): ?>
                            <i class="fa-solid fa-paperclip text-primary"></i>
                            <a href="<?= base_url('uploads/berita_acara_mutasi/' . $m['file_name_hash']) ?>" target="_blank" class="text-truncate" style="max-width:200px;" title="<?= $m['file_name_original'] ?>">
                                <?= $m['file_name_original'] ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted small">Tidak ada file lampiran</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <h6 class="mb-2">Detail Material & Coil</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle" id="tblDetail">
                    <thead class="table-light">
                        <tr>
                            <th>Material</th>
                            <th>Kode Internal (Coil)</th>
                            <th>No. Coil</th>
                            <th width="140">Net Weight (kg)</th>
                            <th width="140">Length (m)</th>
                        </tr>
                    </thead>
                    <tbody id="detailBody"></tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">Total Keseluruhan</td>
                            <td id="totalNetWeight">0.00</td>
                            <td id="totalLength">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </form>

        <!-- PANEL KEPUTUSAN APPROVAL (HANYA APPROVE & REVISI) -->
        <?php if ($is_approval && $m['status'] == 1 && $ENABLE_MANAGE): ?>
            <div class="card border-warning mt-4">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fa-solid fa-gavel"></i> Form Keputusan Approval
                </div>
                <div class="card-body bg-light-50">
                    <div class="mb-3">
                        <label for="action_reason" class="form-label fw-semibold">Alasan / Catatan Revisi <small class="text-danger">(Wajib diisi jika meminta REVISI)</small></label>
                        <textarea id="action_reason" class="form-control" rows="3" placeholder="Masukkan poin-poin yang harus diperbaiki oleh pengaju..."></textarea>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-info text-white" id="btnRevisi" onclick="processApproval('revisi')">
                            <i class="fa-solid fa-rotate-left"></i> Kembalikan (Minta Revisi)
                        </button>
                        <button type="button" class="btn btn-success" id="btnApprove" onclick="processApproval('approve')">
                            <i class="fa-solid fa-check-double"></i> Approve Mutasi
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const BASE_URL = '<?= site_url('approval_mutasi') ?>';
    const RECORD_ID = '<?= $id ?? '' ?>';
    const EXISTING_DETAILS = <?= json_encode($details) ?>;

    let detailRows = [];

    $(document).ready(function() {
        if (EXISTING_DETAILS.length > 0) {
            EXISTING_DETAILS.forEach(function(d, index) {
                detailRows.push({
                    rowId: index + 1,
                    material: {
                        nm_material: d.nm_material,
                        trade_name: d.trade_name
                    },
                    coils: d.coils || []
                });
            });
            renderTable();
        }
    });

    function renderTable() {
        const tbody = $('#detailBody');
        tbody.empty();

        if (detailRows.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data detail material.</td></tr>');
            return;
        }

        detailRows.forEach(function(row) {
            const coilsCount = row.coils.length;
            row.coils.forEach(function(coil, index) {
                let rowHtml = '<tr>';
                if (index === 0) {
                    rowHtml += `
                        <td rowspan="${coilsCount}">
                            <div class="fw-bold text-dark">${escHtml(row.material.nm_material)}</div>
                            ${row.material.trade_name ? '<small class="text-muted d-block">' + escHtml(row.material.trade_name) + '</small>' : ''}
                        </td>
                    `;
                }
                rowHtml += `
                    <td><span class="badge bg-light text-dark border">${escHtml(coil.kode_internal || '-')}</span></td>
                    <td>${escHtml(coil.no_coil)}</td>
                    <td>${formatNum(coil.net_weight, 2)}</td>
                    <td>${formatNum(coil.length, 2)}</td>
                </tr>`;
                tbody.append(rowHtml);
            });
        });
        recalcTotals();
    }

    function recalcTotals() {
        let totalNet = 0,
            totalLen = 0;
        detailRows.forEach(function(r) {
            r.coils.forEach(function(c) {
                totalNet += parseFloat(c.net_weight || 0);
                totalLen += parseFloat(c.length || 0);
            });
        });
        $('#totalNetWeight').text(formatNum(totalNet, 2));
        $('#totalLength').text(formatNum(totalLen, 2));
    }

    function processApproval(actionType) {
        const reason = $('#action_reason').val().trim();

        // Validasi: Alasan wajib diisi jika Meminta Revisi
        if (actionType === 'revisi' && !reason) {
            Swal.fire('Perhatian', 'Harap isi alasan atau catatan bagian mana yang perlu direvisi.', 'warning');
            return;
        }

        let labelAction = 'Menyetujui';
        let confirmBtnColor = '#198754';
        if (actionType === 'revisi') {
            labelAction = 'Meminta Revisi';
            confirmBtnColor = '#0dcaf0';
        }

        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: `Anda akan memproses ${labelAction} untuk pengajuan mutasi ini.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmBtnColor,
            confirmButtonText: 'Ya, Proses',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#btnApprove, #btnRevisi').prop('disabled', true);

                $.post(BASE_URL + '/submit_approval', {
                    id: RECORD_ID,
                    action: actionType,
                    reason: reason
                }, function(res) {
                    if (res.status == 1) {
                        Swal.fire('Berhasil', res.message, 'success').then(() => {
                            window.location.href = BASE_URL;
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                        $('#btnApprove, #btnRevisi').prop('disabled', false);
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem di server.', 'error');
                    $('#btnApprove, #btnRevisi').prop('disabled', false);
                });
            }
        });
    }

    function escHtml(str) {
        return str ? String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') : '';
    }

    function formatNum(val, dec) {
        return parseFloat(val || 0).toLocaleString('id-ID', {
            minimumFractionDigits: dec,
            maximumFractionDigits: dec
        });
    }
</script>