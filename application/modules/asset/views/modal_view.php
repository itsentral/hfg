<?php
$header = isset($dataD[0]) ? $dataD[0] : array();
$foto_path = '';
if (!empty($header['foto'])) {
    $foto_path = base_url('assets/foto/' . $header['foto']);
}
?>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Kode Asset</label>
        <p class="fw-bold fs-6 mb-2 text-primary"><?= isset($header['kd_asset']) ? strtoupper($header['kd_asset']) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Nama Asset</label>
        <p class="fw-bold fs-6 mb-2"><?= isset($header['nm_asset']) ? strtoupper($header['nm_asset']) : '-' ?></p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Owned Asset (Cabang)</label>
        <p class="fw-bold mb-2"><?= isset($header['kdcab']) ? strtoupper($header['kdcab']) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Department</label>
        <p class="fw-bold mb-2"><?= isset($header['department']) ? strtoupper($header['department']) : '-' ?></p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Cost Center</label>
        <p class="fw-bold mb-2"><?= isset($header['nm_costcenter']) ? strtoupper($header['nm_costcenter']) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Nama User</label>
        <p class="fw-bold mb-2"><?= isset($header['nama_user']) && !empty($header['nama_user']) ? strtoupper($header['nama_user']) : '-' ?></p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Tanggal Perolehan</label>
        <p class="fw-bold mb-2"><?= isset($header['tgl_perolehan']) ? date('d F Y', strtotime($header['tgl_perolehan'])) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Tanggal Mulai Depresiasi</label>
        <p class="fw-bold mb-2"><?= isset($header['tgl_depresiasi']) && !empty($header['tgl_depresiasi']) ? date('d F Y', strtotime($header['tgl_depresiasi'])) : '-' ?></p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Nilai Perolehan (Value Asset)</label>
        <p class="fw-bold mb-2 text-success fs-6">Rp <?= isset($header['nilai_asset']) ? number_format($header['nilai_asset']) : '0' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Penyusutan Status</label>
        <p class="mb-2">
            <?php if (isset($header['penyusutan']) && $header['penyusutan'] == 'Y'): ?>
                <span class="badge bg-success p-2">DAPAT DISUSUTKAN</span>
            <?php else: ?>
                <span class="badge bg-secondary p-2">TIDAK DISUSUTKAN</span>
            <?php endif; ?>
        </p>
    </div>

    <?php if (isset($header['penyusutan']) && $header['penyusutan'] == 'Y'): ?>
        <div class="col-md-6">
            <label class="form-label text-muted mb-0 small fw-semibold">Jangka Waktu Depresiasi</label>
            <p class="fw-bold mb-2"><?= isset($header['depresiasi']) ? $header['depresiasi'] . ' Tahun' : '-' ?></p>
        </div>
        <div class="col-md-6">
            <label class="form-label text-muted mb-0 small fw-semibold">Depresiasi / Bulan</label>
            <p class="fw-bold mb-2 text-warning fs-6">Rp <?= isset($header['value']) ? number_format($header['value']) : '0' ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($foto_path)): ?>
        <div class="col-12 mt-3">
            <label class="form-label text-muted mb-2 small fw-semibold">Foto Asset</label>
            <div>
                <img src="<?= $foto_path ?>" class="img-fluid rounded border shadow-sm" style="max-height: 300px; object-fit: contain;" alt="Foto Asset">
            </div>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    if (typeof swal !== 'undefined' && typeof swal.close === 'function') {
        swal.close();
    }
</script>
