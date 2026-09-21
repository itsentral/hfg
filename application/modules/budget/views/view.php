<?php
$h = isset($header[0]) ? $header[0] : (object)array();
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fa fa-eye me-2"></i><?= $title ?>
        </h5>
        <a href="<?= site_url('budget') ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Budget List
        </a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label text-muted mb-0">Code Plan</label>
                <p class="fw-bold mb-2"><?= isset($h->code_plan) ? strtoupper($h->code_plan) : '-' ?></p>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted mb-0">Department</label>
                <p class="fw-bold mb-2"><?= isset($h->nm_dept) ? strtoupper($h->nm_dept) : '-' ?></p>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted mb-0">Cost Center</label>
                <p class="fw-bold mb-2"><?= isset($h->nm_costcenter) ? strtoupper($h->nm_costcenter) : '-' ?></p>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted mb-0">Post Anggaran (COA)</label>
                <p class="fw-bold mb-2"><?= isset($h->coa) ? strtoupper($h->coa . ' - ' . $h->nama_coa) : '-' ?></p>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted mb-0">Nama Asset</label>
                <p class="fw-bold mb-2"><?= isset($h->nama_asset) ? strtoupper($h->nama_asset) : '-' ?></p>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted mb-0">Periode Planning</label>
                <p class="fw-bold mb-2"><?= isset($h->bulan) ? date('F Y', strtotime($h->tahun . '-' . sprintf('%02d', $h->bulan) . '-01')) : '-' ?></p>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted mb-0">Qty</label>
                <p class="fw-bold mb-2"><?= isset($h->qty) ? number_format($h->qty) : '0' ?></p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted mb-0">Total Budget</label>
                <p class="fw-bold mb-2 text-primary fs-5"><?= isset($h->budget) ? 'Rp ' . number_format($h->budget) : '0' ?></p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted mb-0">Sisa Budget PR</label>
                <p class="fw-bold mb-2"><?= isset($h->budget_pr) ? 'Rp ' . number_format($h->budget_pr) : '0' ?></p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted mb-0">Sisa Budget PO</label>
                <p class="fw-bold mb-2"><?= isset($h->budget_po) ? 'Rp ' . number_format($h->budget_po) : '0' ?></p>
            </div>
            <div class="col-md-12">
                <label class="form-label text-muted mb-0">Keterangan</label>
                <p class="fw-bold mb-2"><?= isset($h->keterangan) ? strtoupper($h->keterangan) : '-' ?></p>
            </div>

            <div class="col-12">
                <hr>
                <h6 class="fw-bold text-secondary">Status Approval</h6>
                <?php
                $status = isset($h->status) ? $h->status : 'N';
                if ($status == 'Y') {
                    echo "<span class='badge bg-success p-2'>APPROVED</span>";
                } else if ($status == 'D') {
                    echo "<span class='badge bg-danger p-2'>REJECTED</span>";
                } else {
                    echo "<span class='badge bg-warning text-dark p-2'>WAITING APPROVAL</span>";
                }
                ?>
                <?php if (!empty($h->reason)): ?>
                    <p class="mt-2 text-danger"><b>Reason:</b> <?= strtoupper($h->reason) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
