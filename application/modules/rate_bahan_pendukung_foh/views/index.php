<?php
$ENABLE_ADD     = has_permission('Rate_Bahan_Pendukung_FOH.Add');
$ENABLE_MANAGE  = has_permission('Rate_Bahan_Pendukung_FOH.Manage');
$ENABLE_DELETE  = has_permission('Rate_Bahan_Pendukung_FOH.Delete');
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1 text-primary fw-bold">
                <i class="fa fa-cubes me-2"></i>Rate Bahan Pendukung Khusus &amp; FOH
            </h5>
            <small class="text-muted">Rate per Product Category (Rp/Kg). Digunakan dalam perhitungan HPP Simulasi Costing.</small>
        </div>
        <div>
            <span class="badge bg-light text-dark border px-3 py-2 me-2">
                Total: <strong><?= count($list) ?></strong> Kategori
            </span>
            <?php if (($ENABLE_ADD || $ENABLE_MANAGE) && !empty($available)): ?>
                <button type="button" class="btn btn-primary btn-sm btn-add-rate shadow-sm">
                    <i class="fa fa-plus-circle me-1"></i> Tambah Category Rate
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-3">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle me-1"></i> <?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tbl-bpkfoh" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>Product Category</th>
                        <th class="text-end" width="220">Rate Bahan Pendukung Khusus (Rp/Kg)</th>
                        <th class="text-end" width="220">Rate FOH (Rp/Kg)</th>
                        <th class="text-center" width="160">Last Update</th>
                        <?php if ($ENABLE_MANAGE): ?>
                            <th class="text-center no-sort" width="120">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($list)): ?>
                        <?php foreach ($list as $idx => $r): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td class="fw-bold"><?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?></td>
                                <td class="text-end fw-bold text-success fs-6">
                                    Rp <?= number_format($r->rate_pendukung_khusus, 2, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold text-primary fs-6">
                                    Rp <?= number_format($r->rate_foh, 2, ',', '.') ?>
                                </td>
                                <td class="text-center text-muted">
                                    <?= !empty($r->latest_update) ? date('d/m/Y H:i', strtotime($r->latest_update)) : '-' ?>
                                </td>
                                <?php if ($ENABLE_MANAGE): ?>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning btn-edit-rate"
                                            data-id="<?= $r->id ?>"
                                            data-code="<?= $r->code_lv2 ?>"
                                            data-nama="<?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?>"
                                            data-bpk="<?= (float)$r->rate_pendukung_khusus ?>"
                                            data-foh="<?= (float)$r->rate_foh ?>"
                                            title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-del-rate ms-1"
                                            data-id="<?= $r->id ?>"
                                            data-nama="<?= htmlspecialchars(strtoupper($r->nm_category ?: $r->code_lv2)) ?>"
                                            title="Hapus">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Rate BPK & FOH -->
<div class="modal fade" id="modal-rate" tabindex="-1" aria-labelledby="modalRateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="<?= base_url('rate_bahan_pendukung_foh/save') ?>" method="POST" autocomplete="off">
                <input type="hidden" name="id" id="rate_id" value="">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalRateLabel">Rate Bahan Pendukung &amp; FOH</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3" id="group-select-cat">
                        <label class="form-label fw-bold">Product Category <span class="text-danger">*</span></label>
                        <select name="code_lv2" id="rate_code_lv2" class="form-select">
                            <option value="">-- Pilih Product Category --</option>
                            <?php foreach ($available as $cat): ?>
                                <option value="<?= htmlspecialchars($cat->code_lv2) ?>"><?= htmlspecialchars(strtoupper($cat->nama)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="group-ro-cat">
                        <label class="form-label fw-bold">Product Category</label>
                        <input type="text" id="ro_cat_name" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rate Bahan Pendukung Khusus (Rp/Kg) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="0.0001" min="0" name="rate_pendukung_khusus" id="rate_bpk" class="form-control text-end" required placeholder="0.00">
                            <span class="input-group-text">/ Kg</span>
                        </div>
                        <div class="form-text text-muted">Tarif bahan pendukung khusus per kilogram.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rate FOH (Rp/Kg) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="0.0001" min="0" name="rate_foh" id="rate_foh" class="form-control text-end" required placeholder="0.00">
                            <span class="input-group-text">/ Kg</span>
                        </div>
                        <div class="form-text text-muted">Tarif Factory Overhead per kilogram.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function() {
    $('#tbl-bpkfoh').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [{ targets: 'no-sort', orderable: false }]
    });

    // Add Rate
    $('.btn-add-rate').on('click', function() {
        $('#rate_id').val('');
        $('#group-select-cat').removeClass('d-none');
        $('#group-ro-cat').addClass('d-none');
        $('#rate_code_lv2').val('').prop('required', true);
        $('#rate_bpk').val('');
        $('#rate_foh').val('');
        $('#modalRateLabel').text('Tambah Rate Bahan Pendukung & FOH');
        $('#modal-rate').modal('show');
    });

    // Edit Rate
    $(document).on('click', '.btn-edit-rate', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var bpk = $(this).data('bpk');
        var foh = $(this).data('foh');

        $('#rate_id').val(id);
        $('#group-select-cat').addClass('d-none');
        $('#group-ro-cat').removeClass('d-none');
        $('#rate_code_lv2').prop('required', false);
        $('#ro_cat_name').val(nama);
        $('#rate_bpk').val(bpk);
        $('#rate_foh').val(foh);
        $('#modalRateLabel').text('Edit Rate — ' + nama);
        $('#modal-rate').modal('show');
    });

    // Delete Rate
    $(document).on('click', '.btn-del-rate', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        Swal.fire({
            title: 'Hapus Rate?',
            text: 'Rate Bahan Pendukung Khusus & FOH untuk "' + nama + '" akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(res) {
            if (!res.isConfirmed) return;
            $.ajax({
                url: '<?= base_url('rate_bahan_pendukung_foh/delete') ?>',
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(resp) {
                    if (resp.status == 1) {
                        Swal.fire({ title: 'Berhasil!', text: resp.pesan, icon: 'success', timer: 1500, showConfirmButton: false })
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
