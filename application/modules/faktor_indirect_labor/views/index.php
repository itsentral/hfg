<?php defined('BASEPATH') or exit('No direct script access allowed');
$ENABLE_MANAGE = has_permission('Faktor_Indirect_Labor.Manage');
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

<!-- Card 1: Nilai Faktor Indirect Labor (Global) - FULL WIDTH -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <div>
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fa fa-percent text-primary me-2"></i> Faktor Indirect Labor (Global)
            </h5>
            <small class="text-muted">Pengali Man Power Cost (satu nilai untuk seluruh produk).</small>
        </div>
        <?php if ($ENABLE_MANAGE): ?>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-edit-faktor">
                <i class="fa fa-edit me-1"></i> Edit Faktor
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="alert alert-info py-2 mb-4">
            <i class="fa fa-info-circle me-1"></i> Faktor mengalikan <code>(Total Man Hour &times; Rate 1/3 Operator + Rate Borongan Helper Rp/Meter)</code> pada Man Power Cost. Perubahan nilai tidak mengubah Pricelist snapshot yang sudah disetujui.
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-end" width="220">Faktor Indirect Labor</th>
                        <th>Keterangan Rumus</th>
                        <th class="text-center" width="200">Last Update</th>
                        <?php if ($ENABLE_MANAGE): ?>
                            <th class="text-center" width="120">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-end fw-bold fs-5 text-primary">
                            <?= number_format($faktor->faktor_indirect_labor, 4, '.', '') ?>
                        </td>
                        <td class="text-muted">
                            &times; (Total Man Hour &times; Rate 1/3 Operator + Rate Borongan Helper Rp/Meter)
                        </td>
                        <td class="text-center text-muted">
                            <?= !empty($faktor->latest_update) ? date('d/m/Y H:i', strtotime($faktor->latest_update)) : '-' ?>
                        </td>
                        <?php if ($ENABLE_MANAGE): ?>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modal-edit-faktor" title="Edit">
                                    <i class="fa fa-edit me-1"></i> Edit
                                </button>
                            </td>
                        <?php endif; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Card 2: Contoh Dampak Man Power Cost per Product Category - FULL WIDTH -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fa fa-table me-2"></i> Contoh Dampak: Man Power Cost per Product Category
        </h5>
        <small class="text-muted">
            Formula: <code>Man Power Cost (Rp/Meter) = Jumlah sebelum Faktor &times; Faktor Indirect Labor</code>. Hanya baca.
        </small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>Product Category</th>
                        <th class="text-end" width="250">Jumlah sebelum Faktor (Rp/Meter)</th>
                        <th class="text-end" width="180">&times; Faktor Indirect Labor</th>
                        <th class="text-end" width="250">Man Power Cost (Rp/Meter)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($simulation)): ?>
                        <?php foreach ($simulation as $idx => $s): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td class="fw-bold"><?= htmlspecialchars(strtoupper($s->nama)) ?></td>
                                <td class="text-end">
                                    <?= $s->has_data ? 'Rp ' . number_format($s->subtotal, 2, ',', '.') : '<span class="badge bg-warning text-dark">Data belum lengkap</span>' ?>
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    <?= number_format($s->faktor, 2) ?>
                                </td>
                                <td class="text-end fw-bold text-success fs-6">
                                    <?= $s->total_mpc !== null ? 'Rp ' . number_format($s->total_mpc, 2, ',', '.') : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Faktor Indirect Labor -->
<div class="modal fade" id="modal-edit-faktor" tabindex="-1" aria-labelledby="modalFaktorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="<?= base_url('faktor_indirect_labor/save') ?>" method="POST" autocomplete="off">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalFaktorLabel">
                        <i class="fa fa-edit me-1"></i> Edit Faktor Indirect Labor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="fa fa-exclamation-triangle me-1"></i> Perubahan nilai ini berlaku untuk semua produk dan mempengaruhi perhitungan Man Power Cost.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Faktor Indirect Labor <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" min="0.0001" name="faktor_indirect_labor" class="form-control form-control-lg" required
                            value="<?= number_format($faktor->faktor_indirect_labor, 4, '.', '') ?>">
                        <div class="form-text text-muted">Nilai pengali harus lebih besar dari 0 (default: 1.5000).</div>
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
