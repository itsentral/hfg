<?php
$header = isset($dataD[0]) ? $dataD[0] : array();
?>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Kode Asset</label>
        <p class="fw-bold mb-2"><?= isset($header['kd_asset']) ? strtoupper($header['kd_asset']) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Nama Asset</label>
        <p class="fw-bold mb-2"><?= isset($header['nm_asset']) ? strtoupper($header['nm_asset']) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Tanggal Perolehan</label>
        <p class="fw-bold mb-2"><?= isset($header['tgl_perolehan']) ? $header['tgl_perolehan'] : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Nilai Asset</label>
        <p class="fw-bold mb-2"><?= isset($header['nilai_asset']) ? number_format($header['nilai_asset']) : '0' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Depresiasi (Tahun)</label>
        <p class="fw-bold mb-2"><?= isset($header['depresiasi']) ? $header['depresiasi'] . ' Tahun' : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Nilai Depresiasi / Bulan</label>
        <p class="fw-bold mb-2"><?= isset($header['value']) ? number_format($header['value']) : '0' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Department</label>
        <p class="fw-bold mb-2"><?= isset($header['department']) ? strtoupper($header['department']) : '-' ?></p>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-0">Cost Center</label>
        <p class="fw-bold mb-2"><?= isset($header['nm_costcenter']) ? strtoupper($header['nm_costcenter']) : '-' ?></p>
    </div>
</div>
