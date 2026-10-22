<?php defined('BASEPATH') or exit('No direct script access allowed');
$ENABLE_MANAGE = has_permission('Man_Power_Rate.Manage');
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

<div class="alert alert-info py-2 mb-4">
    <i class="fa fa-info-circle me-1"></i> <strong>Man Power Rate (Operator):</strong> Rate 1/3 Operator (Rp/jam) berasal dari rincian gaji, BPJS, dan biaya lain Operator. Digunakan menghitung Man Power Cost: <code>(Total Man Hour &times; Rate 1/3 Operator + Rate Borongan Helper Rp/Meter) &times; Faktor Indirect Labor</code>.
</div>

<?php
$groups = [
    'salary'    => ['no' => '1', 'title' => 'Salary Direct Man Power', 'sub' => $rate->salary_direct_man_power, 'items' => $components['salary'], 'desc' => 'Gaji pokok dan tunjangan langsung operator.'],
    'bpjs'      => ['no' => '2', 'title' => 'BPJS (Input Manual)', 'sub' => $rate->bpjs, 'items' => $components['bpjs'], 'desc' => 'Nilai rupiah tiap komponen diinput manual; persentase hanya keterangan.'],
    'lain_lain' => ['no' => '3', 'title' => 'Biaya Lain-Lain', 'sub' => $rate->biaya_lain_lain, 'items' => $components['lain_lain'], 'desc' => 'Biaya pendukung per bulan (seragam, pelatihan, dll.).'],
];
?>

<!-- 1, 2, 3: Group Komponen Biaya -->
<?php foreach ($groups as $gKey => $g): ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <div>
            <h5 class="mb-0 fw-bold text-dark"><?= $g['no'] ?>. <?= $g['title'] ?></h5>
            <small class="text-muted"><?= $g['desc'] ?></small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-primary fs-6 border px-3 py-2">
                Subtotal: <strong>Rp <?= number_format($g['sub'], 0, ',', '.') ?></strong>
            </span>
            <?php if ($ENABLE_MANAGE): ?>
                <button type="button" class="btn btn-primary btn-sm btn-add-comp" data-kelompok="<?= $gKey ?>" data-title="<?= $g['title'] ?>">
                    <i class="fa fa-plus me-1"></i> Add Komponen
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>Item Komponen</th>
                        <th class="text-end" width="220">Nilai (Rp)</th>
                        <th>Keterangan</th>
                        <?php if ($ENABLE_MANAGE): ?>
                            <th class="text-center" width="120">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($g['items'])): ?>
                        <tr><td colspan="<?= $ENABLE_MANAGE ? 5 : 4 ?>" class="text-center text-muted py-3">Belum ada komponen pada kelompok ini</td></tr>
                    <?php else: ?>
                        <?php foreach ($g['items'] as $idx => $item): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($item->nama_komponen) ?></td>
                                <td class="text-end fw-bold text-success">Rp <?= number_format($item->nilai, 0, ',', '.') ?></td>
                                <td class="text-muted"><?= htmlspecialchars($item->keterangan ?: '—') ?></td>
                                <?php if ($ENABLE_MANAGE): ?>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning btn-edit-comp"
                                            data-id="<?= $item->id ?>"
                                            data-kelompok="<?= $gKey ?>"
                                            data-nama="<?= htmlspecialchars($item->nama_komponen) ?>"
                                            data-nilai="<?= (float)$item->nilai ?>"
                                            data-ket="<?= htmlspecialchars($item->keterangan ?: '') ?>"
                                            title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-del-comp ms-1"
                                            data-id="<?= $item->id ?>"
                                            data-nama="<?= htmlspecialchars($item->nama_komponen) ?>"
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
<?php endforeach; ?>

<!-- 4: Parameter & Rate 1/3 Operator Sesuai Mockup -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <div>
            <h5 class="mb-0 fw-bold text-dark">4. Parameter &amp; Rate 1/3 Operator</h5>
            <small class="text-muted">Konstanta jam kerja, porsi operator, dan pembulatan ke atas ke Rupiah penuh (CEIL).</small>
        </div>
        <?php if ($ENABLE_MANAGE): ?>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-param">
                <i class="fa fa-edit me-1"></i> Edit Parameter
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="35%">Komponen</th>
                        <th width="25%" class="text-end">Nilai</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Jam kerja per bulan <span class="badge bg-light text-dark border">parameter</span></td>
                        <td class="text-end fw-bold fs-6"><?= number_format($rate->jam_kerja_per_bulan, 0) ?> jam</td>
                        <td class="text-muted">Pembagi Total Biaya Direct Labor Operator</td>
                    </tr>
                    <tr>
                        <td>Porsi Operator <span class="badge bg-light text-dark border">parameter</span></td>
                        <td class="text-end fw-bold fs-6">1 / <?= round(1 / ((float)$rate->porsi_operator ?: 0.3333)) ?></td>
                        <td class="text-muted">Operator dianggap hanya 1/<?= round(1 / ((float)$rate->porsi_operator ?: 0.3333)) ?> bekerja langsung pada produk</td>
                    </tr>
                    <tr>
                        <td><strong>Faktor Indirect Labor</strong> <span class="badge bg-info text-dark">master global</span></td>
                        <td class="text-end fw-bold fs-6 text-primary"><?= number_format($faktor, 2) ?></td>
                        <td class="text-muted">Master: menu <a href="<?= base_url('faktor_indirect_labor') ?>" class="fw-bold">Faktor Indirect Labor</a></td>
                    </tr>
                    <tr class="table-light">
                        <td><strong>Total Biaya Direct Labor Operator / Bulan</strong></td>
                        <td class="text-end fw-bold fs-6 text-dark">Rp <?= number_format($rate->total_direct_labor_per_bulan, 0, ',', '.') ?></td>
                        <td class="text-muted">Salary + BPJS + Biaya Lain-Lain</td>
                    </tr>
                    <tr>
                        <td>Rate Direct Labor Operator (Rp/jam)</td>
                        <td class="text-end fw-bold text-secondary">Rp <?= number_format($rate->rate_direct_labor, 2, ',', '.') ?></td>
                        <td class="text-muted">Total &divide; jam kerja per bulan (belum dibulatkan)</td>
                    </tr>
                    <tr class="table-primary">
                        <td class="fw-bold fs-6 text-primary"><strong>Rate 1/3 Operator (Rp/jam)</strong></td>
                        <td class="text-end fw-bold fs-5 text-primary">
                            Rp <?= number_format($rate->rate_1_3_operator, 0, ',', '.') ?>
                        </td>
                        <td class="small">
                            <strong>CEIL(Rate Direct Labor &times; 1/<?= round(1 / ((float)$rate->porsi_operator ?: 0.3333)) ?>)</strong> &mdash; dibulatkan ke atas ke Rupiah penuh sesudah dikali 1/3. Satu-satunya angka yang dibulatkan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 5: Contoh Dampak Man Power Cost per Product Category Sesuai Mockup -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark">5. Contoh: Man Power Cost per Product Category</h5>
        <small class="text-muted">
            Formula: <code>Man Power Cost (Rp/Meter) = (Total Man Hour &times; Rate 1/3 Operator + Rate Borongan Helper Rp/Meter) &times; Faktor Indirect Labor</code>
        </small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>Product Category</th>
                        <th class="text-end" width="170">Total Man Hour (jam/m)</th>
                        <th class="text-end" width="180">Bagian Operator (Rp/m)</th>
                        <th class="text-end" width="180">Borongan Helper (Rp/m)</th>
                        <th class="text-end" width="200">Jumlah sebelum Faktor (Rp/m)</th>
                        <th class="text-end" width="140">&times; Faktor Indirect</th>
                        <th class="text-end" width="200">Man Power Cost (Rp/m)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($simulation)): ?>
                        <?php foreach ($simulation as $idx => $sim): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td class="fw-bold"><?= htmlspecialchars(strtoupper($sim->nama)) ?></td>
                                <td class="text-end">
                                    <?= $sim->has_flow ? number_format($sim->tmh, 6, '.', '') : '<span class="badge bg-warning text-dark">Flow belum ada</span>' ?>
                                </td>
                                <td class="text-end">
                                    <?= $sim->has_flow ? 'Rp ' . number_format($sim->op_cost, 2, ',', '.') : '—' ?>
                                </td>
                                <td class="text-end">
                                    <?= $sim->has_bor ? 'Rp ' . number_format($sim->bor, 2, ',', '.') : '<span class="badge bg-warning text-dark">Rate belum ada</span>' ?>
                                </td>
                                <td class="text-end fw-semibold">
                                    <?= $sim->subtotal !== null ? 'Rp ' . number_format($sim->subtotal, 2, ',', '.') : '—' ?>
                                </td>
                                <td class="text-end text-primary fw-bold">
                                    <?= number_format($sim->faktor, 2) ?>
                                </td>
                                <td class="text-end fw-bold text-success fs-6">
                                    <?= $sim->total_mpc !== null ? 'Rp ' . number_format($sim->total_mpc, 2, ',', '.') : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Komponen -->
<div class="modal fade" id="modal-comp" tabindex="-1" aria-labelledby="modalCompLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="<?= base_url('man_power_rate/save_component') ?>" method="POST" autocomplete="off">
                <input type="hidden" name="id" id="comp_id" value="">
                <input type="hidden" name="kelompok" id="comp_kelompok" value="salary">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalCompLabel">Komponen Biaya</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Item Komponen <span class="text-danger">*</span></label>
                        <input type="text" name="nama_komponen" id="comp_nama" class="form-control" required placeholder="Nama item komponen">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nilai Rupiah (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="0" name="nilai" id="comp_nilai" class="form-control" required placeholder="0">
                        <div class="form-text text-muted">Nilai biaya per bulan dalam Rupiah penuh.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <input type="text" name="keterangan" id="comp_ket" class="form-control" placeholder="Contoh: Iuran bulanan, tunjangan makan, dll.">
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

<!-- Modal Edit Parameter -->
<div class="modal fade" id="modal-param" tabindex="-1" aria-labelledby="modalParamLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <form action="<?= base_url('man_power_rate/save_parameters') ?>" method="POST" autocomplete="off">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalParamLabel">Edit Parameter Man Power</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jam Kerja per Bulan <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" min="1" name="jam_kerja_per_bulan" class="form-control" required
                            value="<?= number_format($rate->jam_kerja_per_bulan, 2, '.', '') ?>">
                        <div class="form-text text-muted">Standar parameter jam kerja (default 173 jam).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Porsi Operator: 1 / n (n =) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" min="0.1" name="porsi_operator_den" class="form-control" required
                            value="<?= round(1 / ((float)$rate->porsi_operator ?: 0.3333)) ?>">
                        <div class="form-text text-muted">Operator dianggap hanya 1/n bekerja langsung pada produk (contoh n = 3 untuk porsi 1/3).</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i> Simpan Parameter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function() {
    // Tombol Add Komponen
    $('.btn-add-comp').on('click', function() {
        var kel = $(this).data('kelompok');
        var title = $(this).data('title');
        $('#comp_id').val('');
        $('#comp_kelompok').val(kel);
        $('#comp_nama').val('');
        $('#comp_nilai').val('');
        $('#comp_ket').val('');
        $('#modalCompLabel').text('Add Komponen — ' + title);
        $('#modal-comp').modal('show');
    });

    // Tombol Edit Komponen
    $('.btn-edit-comp').on('click', function() {
        var id = $(this).data('id');
        var kel = $(this).data('kelompok');
        var nama = $(this).data('nama');
        var nilai = $(this).data('nilai');
        var ket = $(this).data('ket');

        $('#comp_id').val(id);
        $('#comp_kelompok').val(kel);
        $('#comp_nama').val(nama);
        $('#comp_nilai').val(nilai);
        $('#comp_ket').val(ket);
        $('#modalCompLabel').text('Edit Komponen — ' + nama);
        $('#modal-comp').modal('show');
    });

    // Tombol Delete Komponen
    $('.btn-del-comp').on('click', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        Swal.fire({
            title: 'Hapus Komponen?',
            text: 'Komponen "' + nama + '" akan dihapus dan Rate 1/3 Operator akan dihitung ulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(res) {
            if (!res.isConfirmed) return;
            $.ajax({
                url: '<?= base_url('man_power_rate/delete_component') ?>',
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
