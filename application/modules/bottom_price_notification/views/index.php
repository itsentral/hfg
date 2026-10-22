<?php
$ENABLE_MANAGE = has_permission('Bottom_Price_Notification.Manage');
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1 text-primary fw-bold">
                    <i class="fa fa-bell me-2"></i>Notifikasi Perubahan Bottom Price
                </h5>
                <small class="text-muted">
                    Muncul saat <strong>Update Costing</strong> dijalankan dan Bottom Price (Rp/m) suatu varian BOM bergeser dari baseline sebelumnya.
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('simulasi_costing') ?>" class="btn btn-outline-primary btn-sm shadow-sm">
                    <i class="fa fa-calculator me-1"></i> Buka Simulasi Costing
                </a>
            </div>
        </div>

        <!-- Summary Badges & Filter Status -->
        <div class="row align-items-center g-3">
            <div class="col-md-4">
                <label class="form-label text-muted small mb-1 fw-bold">Filter Status Peninjauan</label>
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="" <?= empty($status_filter) ? 'selected' : '' ?>>-- SEMUA STATUS (<?= $counts['total'] ?>) --</option>
                    <option value="Belum Ditinjau" <?= ($status_filter === 'Belum Ditinjau') ? 'selected' : '' ?>>
                        Belum Ditinjau (<?= $counts['pending'] ?>)
                    </option>
                    <option value="Diajukan" <?= ($status_filter === 'Diajukan') ? 'selected' : '' ?>>
                        Diajukan ke GM (<?= $counts['requested'] ?>)
                    </option>
                    <option value="Tidak Perlu Diubah" <?= ($status_filter === 'Tidak Perlu Diubah') ? 'selected' : '' ?>>
                        Tidak Perlu Diubah / Keep (<?= $counts['kept'] ?>)
                    </option>
                </select>
            </div>
            <div class="col-md-8 text-md-end">
                <span class="badge bg-warning text-dark border border-warning px-3 py-2 me-1">
                    <i class="fa fa-clock-o me-1"></i> <strong><?= $counts['pending'] ?></strong> Belum Ditinjau
                </span>
                <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 me-1">
                    <i class="fa fa-paper-plane me-1"></i> <strong><?= $counts['requested'] ?></strong> Diajukan ke GM
                </span>
                <span class="badge bg-light text-muted border px-3 py-2">
                    <i class="fa fa-check me-1"></i> <strong><?= $counts['kept'] ?></strong> Keep Rate Aktif
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-notif" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="40">#</th>
                        <th width="140">Waktu Deteksi</th>
                        <th>Product Category</th>
                        <th>Produk / Varian</th>
                        <th class="text-end" width="130">Bottom Lama</th>
                        <th class="text-end" width="130">Bottom Baru</th>
                        <th class="text-end" width="120">Selisih</th>
                        <th class="text-center" width="140">Status</th>
                        <th width="150">Ditinjau Oleh</th>
                        <?php if ($ENABLE_MANAGE): ?>
                            <th class="text-center no-sort" width="180">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($list)): ?>
                        <?php foreach ($list as $idx => $r): ?>
                            <?php 
                                $is_pending = ($r->status === 'Belum Ditinjau');
                                $lama = (float)$r->bottom_price_lama_meter;
                                $baru = (float)$r->bottom_price_baru_meter;
                                $diff = $baru - $lama;
                                $pct_diff = ($lama > 0) ? (($diff / $lama) * 100) : 0;
                            ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td class="text-muted small">
                                    <?= date('d/m/Y H:i', strtotime($r->detected_at)) ?>
                                </td>
                                <td><?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?></td>
                                <td>
                                    <strong class="text-dark"><?= htmlspecialchars(strtoupper($r->nm_product ?: $r->code_lv4)) ?></strong>
                                    <div class="text-muted small">Uniq ID: <span class="badge bg-light text-dark border"><?= htmlspecialchars($r->no_uniq_id) ?></span></div>
                                </td>
                                <td class="text-end">
                                    Rp <?= number_format($lama, 2, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    Rp <?= number_format($baru, 2, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <span class="<?= $diff > 0 ? 'text-danger' : 'text-success' ?>">
                                        <?= ($diff > 0 ? '+' : '') . number_format($diff, 2, ',', '.') ?>
                                    </span>
                                    <div class="text-muted small">(<?= ($diff > 0 ? '+' : '') . number_format($pct_diff, 1) ?>%)</div>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_pending): ?>
                                        <span class="badge bg-warning text-dark border border-warning px-2 py-1">
                                            <i class="fa fa-clock-o me-1"></i> Belum Ditinjau
                                        </span>
                                    <?php elseif ($r->status === 'Diajukan'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">
                                            <i class="fa fa-paper-plane me-1"></i> Diajukan ke GM
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border px-2 py-1">
                                            <i class="fa fa-check me-1"></i> Keep Rate Aktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <?php if (!empty($r->reviewer_name)): ?>
                                        <strong><?= htmlspecialchars($r->reviewer_name) ?></strong>
                                        <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($r->reviewed_at)) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($ENABLE_MANAGE): ?>
                                    <td class="text-center">
                                        <?php if ($is_pending): ?>
                                            <button type="button" class="btn btn-sm btn-primary btn-nego-modal shadow-sm"
                                                data-id="<?= $r->id ?>"
                                                data-nama="<?= htmlspecialchars(strtoupper($r->nm_product ?: $r->code_lv4)) ?> (<?= $r->no_uniq_id ?>)"
                                                data-cat="<?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?>"
                                                data-active="<?= (float)($r->rate_negotiation_allowance * 100) ?>"
                                                data-bottom-lama="<?= number_format($lama, 2, ',', '.') ?>"
                                                data-bottom-baru="<?= number_format($baru, 2, ',', '.') ?>"
                                                title="Ajukan Revisi Extra Margin ke GM">
                                                <i class="fa fa-tag me-1"></i> Ajukan
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-keep-rate ms-1"
                                                data-id="<?= $r->id ?>"
                                                data-nama="<?= htmlspecialchars(strtoupper($r->nm_product ?: $r->code_lv4)) ?> (<?= $r->no_uniq_id ?>)"
                                                title="Tetap Gunakan Rate Extra Margin Aktif">
                                                Keep
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-light text-muted" disabled>
                                                <i class="fa fa-check me-1"></i> Selesai
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3 text-muted small">
            <i class="fa fa-info-circle me-1"></i>
            <strong>Keterangan:</strong> Notifikasi ini bersifat panduan bagi Sales Manager untuk mengevaluasi apakah pergeseran Bottom Price memerlukan penyesuaian Extra Margin sebelum rilis Pricelist.
        </div>
    </div>
</div>

<!-- Modal Ajukan Extra Margin dari Notifikasi -->
<div class="modal fade" id="modal-nego" tabindex="-1" aria-labelledby="modalNegoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form id="form-nego" autocomplete="off">
                <input type="hidden" name="id" id="notif_id">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalNegoLabel">
                        <i class="fa fa-tag me-2"></i>Ajukan Revisi Extra Margin
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <small class="text-muted d-block">Produk / Varian</small>
                            <strong class="fs-6 text-dark" id="txt_prod_name"></strong>
                            <div class="mt-2 row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Bottom Price Lama</small>
                                    <span class="text-muted" id="txt_bot_lama"></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Bottom Price Baru</small>
                                    <strong class="text-primary" id="txt_bot_baru"></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Rate Extra Margin Aktif Saat Ini</label>
                        <div class="input-group">
                            <input type="text" id="txt_active_rate" class="form-control bg-light text-end fw-bold" readonly>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Rate Baru Yang Diajukan (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" name="rate_pct" id="txt_new_rate" class="form-control text-end fs-6 fw-bold text-primary" required placeholder="0.00">
                            <span class="input-group-text fw-bold">%</span>
                        </div>
                        <div class="form-text text-muted">
                            Pengajuan akan otomatis diteruskan ke antrean <strong>Approval GM</strong>.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4" id="btn-submit-nego">
                        <i class="fa fa-paper-plane me-1"></i> Kirim ke GM
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function() {
    $('#tbl-notif').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [{ targets: 'no-sort', orderable: false }]
    });

    // Filter status
    $('#filter-status').on('change', function() {
        var s = $(this).val();
        window.location.href = '<?= base_url('bottom_price_notification') ?>' + (s ? '?status=' + encodeURIComponent(s) : '');
    });

    // Open Nego Modal
    $(document).on('click', '.btn-nego-modal', function() {
        var id       = $(this).data('id');
        var nama     = $(this).data('nama');
        var active   = $(this).data('active');
        var bot_lama = $(this).data('bottom-lama');
        var bot_baru = $(this).data('bottom-baru');

        $('#notif_id').val(id);
        $('#txt_prod_name').text(nama);
        $('#txt_bot_lama').text('Rp ' + bot_lama);
        $('#txt_bot_baru').text('Rp ' + bot_baru);
        $('#txt_active_rate').val(Number(active).toFixed(2));
        $('#txt_new_rate').val(Number(active).toFixed(2));

        $('#modal-nego').modal('show');
    });

    // Submit Nego Request
    $('#form-nego').on('submit', function(e) {
        e.preventDefault();
        var id   = $('#notif_id').val();
        var rate = $('#txt_new_rate').val();

        if (rate === '' || isNaN(rate) || Number(rate) < 0 || Number(rate) > 100) {
            Swal.fire({ title: 'Validasi', text: 'Rate Extra Margin harus antara 0% dan 100%', icon: 'warning' });
            return;
        }

        $('#btn-submit-nego').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Mengirim...');

        $.ajax({
            url: '<?= base_url('bottom_price_notification/submit_extra_margin') ?>',
            type: 'POST',
            dataType: 'json',
            data: { id: id, rate_pct: rate },
            success: function(resp) {
                $('#btn-submit-nego').prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Kirim ke GM');
                if (resp.status == 1) {
                    $('#modal-nego').modal('hide');
                    Swal.fire({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1800, showConfirmButton: false })
                        .then(function() { window.location.reload(); });
                } else {
                    Swal.fire({ title: 'Gagal!', text: resp.pesan, icon: 'error' });
                }
            },
            error: function(xhr) {
                $('#btn-submit-nego').prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Kirim ke GM');
                Swal.fire({ title: 'Error!', text: 'Request error: ' + xhr.status, icon: 'error' });
            }
        });
    });

    // Keep Active Rate Click
    $(document).on('click', '.btn-keep-rate', function() {
        var id   = $(this).data('id');
        var nama = $(this).data('nama');

        Swal.fire({
            title: 'Rate Extra Margin tidak perlu diubah?',
            text: 'Rate Extra Margin aktif tetap dipakai untuk "' + nama + '" dan baseline Bottom Price bergeser ke nilai baru.',
            icon: 'question',
            input: 'text',
            inputPlaceholder: 'Tuliskan catatan opsional (mis: Harga pasar masih sesuai)...',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Tetap Gunakan Rate Aktif',
            cancelButtonText: 'Batal'
        }).then(function(res) {
            if (!res.isConfirmed) return;

            $.ajax({
                url: '<?= base_url('bottom_price_notification/keep') ?>',
                type: 'POST',
                dataType: 'json',
                data: { id: id, catatan: res.value || '' },
                success: function(resp) {
                    if (resp.status == 1) {
                        Swal.fire({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1800, showConfirmButton: false })
                            .then(function() { window.location.reload(); });
                    } else {
                        Swal.fire({ title: 'Gagal!', text: resp.pesan, icon: 'error' });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ title: 'Error!', text: 'Request error: ' + xhr.status, icon: 'error' });
                }
            });
        });
    });
});
</script>
