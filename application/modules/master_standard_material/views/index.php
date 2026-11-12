<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

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

<!-- Card 1: Master Standard Material (Global) - FULL WIDTH -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fa fa-cogs text-primary me-2"></i> Master Standard Material (Global)
        </h5>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-standard">
                <i class="fa fa-edit me-1"></i> Edit Standar Material
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info py-2 mb-4">
            <i class="fa fa-info-circle me-1"></i> Satu set nilai standar yang berlaku untuk seluruh material (global). Dipakai BOM untuk menghitung <strong>Berat/Meter</strong> otomatis.<br>
            <small class="text-muted">Formula: <code>Berat/Meter = ((Tebal Material &minus; Standar Toleransi) &times; Lebar Coil &times; Standar Berat Jenis &divide; 1000) + Coating</code></small>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 20%;">Standar Toleransi</th>
                        <th class="text-center" style="width: 25%;">Standar Berat Jenis Baja</th>
                        <th class="text-center" style="width: 20%;">Coating</th>
                        <th class="text-center" style="width: 20%;">Terakhir Diperbarui</th>
                        <th class="text-center" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center fw-bold fs-6 text-primary">
                            <?= number_format($standard->standar_toleransi, 6, '.', '') ?>
                        </td>
                        <td class="text-center fw-bold fs-6 text-success">
                            <?= number_format($standard->standar_berat_jenis, 6, '.', '') ?>
                        </td>
                        <td class="text-center fw-bold fs-6 text-info">
                            <?= number_format($standard->coating, 6, '.', '') ?>
                        </td>
                        <td class="text-center text-muted">
                            <?= !empty($standard->latest_update) ? date('d/m/Y H:i', strtotime($standard->latest_update)) : '-' ?>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modal-edit-standard" title="Edit">
                                <i class="fa fa-edit me-1"></i> Edit
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Card 2: Contoh Dampak Berat/Meter per Material (Hanya Baca) - FULL WIDTH -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-secondary">
            <i class="fa fa-table me-2"></i> Contoh Dampak: Berat/Meter per Material (Hanya Baca)
        </h6>
        <small class="text-muted">Nilai Berat/Meter di bawah otomatis dihitung dari Master Material menggunakan Standar di atas.</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Kode Material</th>
                        <th>Nama Material</th>
                        <th class="text-end" style="width: 130px;">Tebal (mm)</th>
                        <th class="text-end" style="width: 140px;">Lebar Coil (mm)</th>
                        <th class="text-end" style="width: 180px;">Berat/Meter (kg/m)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sample_materials)): ?>
                        <?php foreach ($sample_materials as $idx => $m): ?>
                            <?php
                                $th = (float) $m->thickness;
                                $wd = (float) $m->width;
                                $bpm = 0.0;
                                if ($th > 0 && $wd > 0) {
                                    $bpm = ((($th - (float)$standard->standar_toleransi) * $wd * (float)$standard->standar_berat_jenis) / 1000) + (float)$standard->coating;
                                }
                            ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><code><?= htmlspecialchars($m->code_lv4) ?></code></td>
                                <td><?= htmlspecialchars($m->nama) ?></td>
                                <td class="text-end"><?= number_format($th, 3, '.', '') ?></td>
                                <td class="text-end"><?= number_format($wd, 2, '.', '') ?></td>
                                <td class="text-end fw-bold text-success"><?= number_format($bpm, 6, '.', '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Master Standard Material -->
<div class="modal fade" id="modal-edit-standard" tabindex="-1" aria-labelledby="modalStandardLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="<?= base_url('master_standard_material/save') ?>" method="POST" autocomplete="off">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalStandardLabel">
                        <i class="fa fa-edit me-1"></i> Edit Master Standard Material
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="fa fa-exclamation-triangle me-1"></i> Perubahan nilai ini langsung mengubah perhitungan <strong>Berat/Meter</strong> semua varian BOM.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Standar Toleransi <span class="text-danger">*</span></label>
                        <input type="number" step="0.000001" min="0" name="standar_toleransi" class="form-control" required
                            value="<?= number_format($standard->standar_toleransi, 6, '.', '') ?>">
                        <div class="form-text text-muted">Standar toleransi ketebalan material (default: 0.000000).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Standar Berat Jenis Baja <span class="text-danger">*</span></label>
                        <input type="number" step="0.000001" min="0.000001" name="standar_berat_jenis" class="form-control" required
                            value="<?= number_format($standard->standar_berat_jenis, 6, '.', '') ?>">
                        <div class="form-text text-muted">Standar berat jenis baja (default: 7.850000).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Coating <span class="text-danger">*</span></label>
                        <input type="number" step="0.000001" min="0" name="coating" class="form-control" required
                            value="<?= number_format($standard->coating, 6, '.', '') ?>">
                        <div class="form-text text-muted">Nilai penambahan lapisan coating (default: 0.000000).</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
