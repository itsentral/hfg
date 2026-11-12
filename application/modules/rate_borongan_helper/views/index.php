<?php defined('BASEPATH') or exit('No direct script access allowed');
$ENABLE_MANAGE = has_permission('Rate_Borongan_Helper.Manage');
?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-1"></i> <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <div>
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fa fa-handshake-o text-primary me-2"></i> Rate Borongan Helper per Product Category
            </h5>
            <small class="text-muted">Biaya upah borongan helper per meter (Rp/Meter) untuk setiap Product Category.</small>
        </div>
        <?php if ($ENABLE_MANAGE && !empty($available)): ?>
            <button type="button" class="btn btn-success btn-sm btn-add-rate">
                <i class="fa fa-plus me-1"></i> Add Rate Borongan
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="alert alert-info py-2 mb-4">
            <i class="fa fa-info-circle me-1"></i> <strong>Spesifikasi:</strong> Satuan rate adalah <strong>Rp/Meter</strong> langsung. Pada Man Power Cost, nilai ini dijumlahkan dengan bagian operator: <code>(Total Man Hour &times; Rate 1/3 Operator + Rate Borongan Helper Rp/Meter) &times; Faktor Indirect Labor</code>.
        </div>

        <div class="table-responsive">
            <table id="tbl-borongan" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>Product Category</th>
                        <th class="text-end" width="220">Rate Borongan (Rp/Meter)</th>
                        <th class="text-center" width="180">Last Update</th>
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
                                    Rp <?= number_format($r->rate_borongan_helper, 2, ',', '.') ?>
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
                                            data-rate="<?= (float)$r->rate_borongan_helper ?>"
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

<!-- Modal Add/Edit Rate Borongan -->
<div class="modal fade" id="modal-rate" tabindex="-1" aria-labelledby="modalRateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="<?= base_url('rate_borongan_helper/save') ?>" method="POST" autocomplete="off">
                <input type="hidden" name="id" id="rate_id" value="">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalRateLabel">Rate Borongan Helper</h5>
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
                        <label class="form-label fw-bold">Rate Borongan Helper (Rp/Meter) <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" min="0" name="rate_borongan_helper" id="rate_val" class="form-control" required placeholder="0.00">
                        <div class="form-text text-muted">Upah borongan helper per meter dalam Rupiah.</div>
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
    $('#tbl-borongan').DataTable({
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
        $('#rate_val').val('');
        $('#modalRateLabel').text('Add Rate Borongan Helper');
        $('#modal-rate').modal('show');
    });

    // Edit Rate
    $(document).on('click', '.btn-edit-rate', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var rate = $(this).data('rate');

        $('#rate_id').val(id);
        $('#group-select-cat').addClass('d-none');
        $('#group-ro-cat').removeClass('d-none');
        $('#rate_code_lv2').prop('required', false);
        $('#ro_cat_name').val(nama);
        $('#rate_val').val(rate);
        $('#modalRateLabel').text('Edit Rate Borongan — ' + nama);
        $('#modal-rate').modal('show');
    });

    // Delete Rate
    $(document).on('click', '.btn-del-rate', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        Swal.fire({
            title: 'Hapus Rate?',
            text: 'Rate Borongan Helper untuk "' + nama + '" akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(res) {
            if (!res.isConfirmed) return;
            $.ajax({
                url: '<?= base_url('rate_borongan_helper/delete') ?>',
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
