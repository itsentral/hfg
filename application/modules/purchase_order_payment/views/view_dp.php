<div class="row g-3">

    <div class="col-md-6">
        <label class="form-label fw-semibold">PO Number</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['no_surat'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Supplier Name</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['nm_supplier'] ?? '-' ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Invoice Number</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['nomor_invoice'] ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Receive Invoice Date</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= date('d F Y', strtotime((string) $data['invoice_date'])) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Invoice Date</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= !empty($data['invoice_date_real']) ? date('d F Y', strtotime((string) $data['invoice_date_real'])) : '-' ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Persentase DP (%)</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format((float) $data['persen_dp'], 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">DPP</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format((float) $data['dpp'], 4) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Value DP</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format((float) $data['value_dp'], 4) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">VAT Value</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format((float) $data['nilai_ppn'], 2) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Discount Value</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format((float) $data['nilai_disc'], 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Currency</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['matauang'] ?? 'IDR' ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Kurs</label>
        <input type="text" class="form-control form-control-sm text-end"
            value="<?= number_format((float) $data['kurs'], 2) ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Tax Invoice Number</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['nomor_faktur_pajak'] ?? '-' ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Tax Invoice Date</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= !empty($data['tanggal_faktur_pajak']) ? date('d F Y', strtotime((string) $data['tanggal_faktur_pajak'])) : '-' ?>" readonly>
    </div>

    <?php if (!empty($data['file_invoice'])): ?>
    <div class="col-12">
        <label class="form-label fw-semibold">File Invoice</label><br>
        <a href="<?= base_url('uploads/invoice_dp/' . $data['file_invoice']) ?>"
            target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-file"></i> Lihat File
        </a>
    </div>
    <?php endif; ?>

    <div class="col-12"><hr class="my-1"><p class="fw-bold mb-2">Bank Information</p></div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Bank</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['bank'] ?>" readonly>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Account Number</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['no_bank'] ?>" readonly>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Owner Name</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['nm_acc_bank'] ?>" readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Status Payment</label><br>
        <?php
        if (empty($data['no_payment'])) {
            echo '<span class="badge bg-warning text-dark">Waiting for Payment</span>';
        } elseif ($data['status_payment'] == 'payment') {
            echo '<span class="badge bg-success">Lunas</span>';
        } else {
            echo '<span class="badge bg-info">In Process</span>';
        }
        ?>
    </div>
    <?php if (!empty($data['no_payment'])): ?>
    <div class="col-md-6">
        <label class="form-label fw-semibold">No. Payment</label>
        <input type="text" class="form-control form-control-sm"
            value="<?= $data['no_payment'] ?>" readonly>
    </div>
    <?php endif; ?>

</div>