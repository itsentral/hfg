<?php
$header = isset($dataD[0]) ? $dataD[0] : array();
$foto_path = '';
if (!empty($header['foto'])) {
    $foto_path = base_url('assets/foto/' . $header['foto']);
}
?>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Asset Code</label>
        <p class="fw-bold fs-6 mb-2 text-primary"><?= isset($header['kd_asset']) ? strtoupper((string) $header['kd_asset']) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Asset Name</label>
        <p class="fw-bold fs-6 mb-2"><?= isset($header['nm_asset']) ? strtoupper((string) $header['nm_asset']) : '-' ?></p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Owned Asset (Branch)</label>
        <p class="fw-bold mb-2"><?= isset($header['kdcab']) ? strtoupper((string) $header['kdcab']) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Department</label>
        <p class="fw-bold mb-2"><?= isset($header['department']) ? strtoupper((string) $header['department']) : '-' ?></p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Cost Center</label>
        <p class="fw-bold mb-2"><?= isset($header['nm_costcenter']) ? strtoupper((string) $header['nm_costcenter']) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">User Name</label>
        <p class="fw-bold mb-2"><?= isset($header['nama_user']) && !empty($header['nama_user']) ? strtoupper((string) $header['nama_user']) : '-' ?></p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Acquisition Date</label>
        <p class="fw-bold mb-2"><?= isset($header['tgl_perolehan']) ? date('d F Y', strtotime((string) $header['tgl_perolehan'])) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Depreciation Start Date</label>
        <p class="fw-bold mb-2"><?= isset($header['tgl_depresiasi']) && !empty($header['tgl_depresiasi']) ? date('d F Y', strtotime((string) $header['tgl_depresiasi'])) : '-' ?></p>
    </div>

    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Acquisition Value (Asset Value)</label>
        <p class="fw-bold mb-2 text-success fs-6">Rp <?= isset($header['nilai_asset']) ? number_format((float) $header['nilai_asset']) : '0' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0 small fw-semibold">Depreciation Status</label>
        <p class="mb-2">
            <?php if (isset($header['penyusutan']) && $header['penyusutan'] == 'Y'): ?>
                <span class="badge bg-success p-2">DAPAT DISUSUTKAN</span>
            <?php else: ?>
                <span class="badge bg-secondary p-2">NOT DEPRECIATED</span>
            <?php endif; ?>
        </p>
    </div>

    <?php if (isset($header['penyusutan']) && $header['penyusutan'] == 'Y'): ?>
        <div class="col-md-6">
            <label class="form-label text-muted mb-0 small fw-semibold">Depreciation Period</label>
            <p class="fw-bold mb-2"><?= isset($header['depresiasi']) ? $header['depresiasi'] . ' Year' : '-' ?></p>
        </div>
        <div class="col-md-6">
            <label class="form-label text-muted mb-0 small fw-semibold">Depreciation / Month</label>
            <p class="fw-bold mb-2 text-warning fs-6">Rp <?= isset($header['value']) ? number_format((float) $header['value']) : '0' ?></p>
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
