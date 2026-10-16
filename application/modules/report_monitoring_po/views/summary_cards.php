<!-- Counter Cards Paling Atas -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="ti ti-file-text fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small fw-medium">Total PO Terdaftar</span>
                    <h4 class="mb-0 fw-bold text-dark"><?= number_format($summary['total_po'] ?? 0); ?></h4>
                    <span class="text-xs text-muted">Purchase Order</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-circle bg-light-info text-info d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="ti ti-package fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small fw-medium">Total Qty PO (Kg)</span>
                    <h4 class="mb-0 fw-bold text-dark"><?= number_format($summary['total_qty_po'] ?? 0, 2); ?></h4>
                    <span class="text-xs text-muted">Total Dipesan</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-circle bg-light-success text-success d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="ti ti-truck-delivery fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small fw-medium">Total Diterima (Kg)</span>
                    <h4 class="mb-0 fw-bold text-success"><?= number_format($summary['total_diterima'] ?? 0, 2); ?></h4>
                    <span class="text-xs text-muted">Sudah Tiba di Gudang</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-circle bg-light-danger text-danger d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="ti ti-clock-pause fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small fw-medium">Sisa Belum Dikirim (Kg)</span>
                    <h4 class="mb-0 fw-bold text-danger"><?= number_format($summary['total_sisa'] ?? 0, 2); ?></h4>
                    <span class="text-xs text-muted">Outstanding PO</span>
                </div>
            </div>
        </div>
    </div>
</div>
