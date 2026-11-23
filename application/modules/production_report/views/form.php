<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<!-- Bootstrap 5, FontAwesome, Select2, Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<style>
  :root {
    --pr-primary: #1e3a8a;
    --pr-primary-dark: #172554;
    --pr-primary-soft: #eff6ff;
    --pr-accent: #2563eb;
    --pr-text: #0f172a;
    --pr-muted: #64748b;
    --pr-border: #e2e8f0;
    --pr-ok: #15803d;
    --pr-ok-soft: #dcfce7;
    --pr-warn: #b45309;
    --pr-warn-soft: #fef3c7;
    --pr-danger: #b91c1c;
    --pr-danger-soft: #fee2e2;
  }

  /* Untuk Chrome, Safari, Edge, Opera */
  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  /* Untuk Firefox */
  input[type=number] {
    -moz-appearance: textfield;
  }

  .pr-page-header {
    background: #fff;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    border: 1px solid var(--pr-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .pr-page-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: var(--pr-text);
    margin-bottom: 4px;
  }

  .pr-page-header p {
    color: var(--pr-muted);
    font-size: 13.5px;
    margin-bottom: 0;
  }

  .pr-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--pr-border);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    margin-bottom: 22px;
    overflow: hidden;
  }

  .pr-card-header {
    background: #fafbfd;
    padding: 14px 20px;
    border-bottom: 1px solid var(--pr-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .pr-card-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--pr-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .pr-card-num {
    background: var(--pr-primary-soft);
    color: var(--pr-accent);
    width: 26px;
    height: 26px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
  }

  .pr-card-hint {
    font-size: 12.5px;
    color: var(--pr-muted);
    margin: 4px 0 0 34px;
  }

  .pr-card-body {
    padding: 20px;
  }

  .meta-strip-spk {
    background: #f8fafc;
    border: 1px solid var(--pr-border);
    border-radius: 8px;
    padding: 14px 20px;
    margin-bottom: 20px;
    width: 100%;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px 24px;
  }

  @media (max-width: 992px) {
    .meta-strip-spk {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 576px) {
    .meta-strip-spk {
      grid-template-columns: 1fr;
    }
  }

  .meta-item {
    display: flex;
    flex-direction: column;
    min-width: 0;
  }

  .meta-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--pr-muted);
    letter-spacing: 0.5px;
  }

  .meta-value {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--pr-text);
  }

  .pr-subhead {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 16px 0 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid #f1f5f9;
  }

  .pr-subhead h3 {
    font-size: 14px;
    font-weight: 700;
    margin: 0;
    color: #334155;
  }

  .tbl-custom {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 13px;
  }

  .tbl-custom th {
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    padding: 9px 12px;
    border-top: 1px solid var(--pr-border);
    border-bottom: 1px solid var(--pr-border);
    font-size: 12px;
    text-align: left;
  }

  .tbl-custom th.num,
  .tbl-custom td.num {
    text-align: right;
  }

  /* Min-width guard for table inputs and selects to prevent shrinkage */
  .tbl-custom td.num input[type="number"],
  .tbl-custom td.num input.form-control {
    min-width: 100px;
    text-align: right;
  }

  .tbl-custom td select.select2,
  .tbl-custom td .select2-container {
    min-width: 220px;
  }

  .tbl-custom input[type="text"] {
    min-width: 120px;
  }

  /* Clear styling for editable vs readonly/disabled inputs */
  .form-control,
  .form-select {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #0f172a !important;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }

  .form-control:focus,
  .form-select:focus {
    background-color: #ffffff !important;
    border-color: #2563eb !important;
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15) !important;
  }

  .form-control[readonly],
  .form-control:disabled,
  .form-select:disabled,
  .bg-light.form-control {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    color: #64748b !important;
    cursor: not-allowed !important;
  }

  .tbl-custom td {
    padding: 8px 12px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
  }

  .tbl-custom tfoot td.empty {
    text-align: center;
    color: var(--pr-muted);
    font-style: italic;
    padding: 16px;
  }

  .fg-card {
    background: #fff;
    border: 1px solid var(--pr-border);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 14px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
  }

  .fg-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dashed var(--pr-border);
  }

  .fg-prod-name {
    font-weight: 700;
    font-size: 14.5px;
    color: var(--pr-text);
  }

  .method-toggle {
    display: inline-flex;
    gap: 16px;
    margin-bottom: 12px;
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
  }

  .method-toggle label {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
  }

  .selisih-badge {
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 12px;
    display: inline-block;
  }

  .selisih-badge.ok {
    background: var(--pr-ok-soft);
    color: var(--pr-ok);
  }

  .selisih-badge.bad {
    background: var(--pr-danger-soft);
    color: var(--pr-danger);
  }

  .summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-top: 10px;
  }

  .summary-box {
    background: #f8fafc;
    border: 1px solid var(--pr-border);
    border-radius: 8px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
  }

  .summary-box.accent {
    background: #eff6ff;
    border-color: #bfdbfe;
  }

  .summary-box.result {
    background: #fafaf9;
    border-color: #e7e5e4;
  }

  .summary-box.bad {
    background: #fee2e2 !important;
    border-color: #fca5a5 !important;
  }

  .summary-box.bad .sm-v {
    color: #b91c1c !important;
  }

  .sm-k {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--pr-muted);
    margin-bottom: 4px;
  }

  .sm-v {
    font-size: 16px;
    font-weight: 700;
    color: var(--pr-text);
  }

  .actionbar {
    position: sticky;
    bottom: 0;
    background: #fff;
    border: 1px solid var(--pr-border);
    border-radius: 12px;
    padding: 14px 20px;
    box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.06);
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 100;
    margin-top: 24px;
  }

  .src-tag {
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
    text-transform: uppercase;
  }

  .src-tag-unpack {
    background: #e0f2fe;
    color: #0369a1;
  }

  .src-tag-wip {
    background: #fef3c7;
    color: #92400e;
  }

  .src-tag-hold {
    background: #fee2e2;
    color: #991b1b;
  }

  .confirm-badge {
    background: #dcfce7;
    color: #15803d;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
  }

  .cl-file-chip {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 11px;
    margin: 2px;
  }
  .cl-del-file {
    color: #ef4444;
    cursor: pointer;
    margin-left: 6px;
    font-weight: bold;
    text-decoration: none;
  }
  .cl-del-file:hover {
    color: #b91c1c;
  }
</style>

<div class="container-fluid p-3">
  <!-- TOP HEADER -->
  <div class="pr-page-header">
    <div>
      <h1>Input Production Report</h1>
      <p>SPK: <strong><?= htmlspecialchars((string) $spk['spk_no']); ?></strong> &bull; Tgl SPK: <?= date('d/m/Y', strtotime((string) $spk['tgl_spk'])); ?></p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-secondary px-3 py-2 fs-6" id="docStatus">Draft</span>
      <a href="<?= site_url('production_report'); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    </div>
  </div>

  <?php if (!empty($costing_check) && !$costing_check['is_all_available']): ?>
    <!-- BANNER PERINGATAN KETERSEDIAAN PRODUCT COSTING (FR-48, FR-74) -->
    <div class="alert alert-warning border-warning shadow-sm mb-3 d-flex align-items-center justify-content-between p-3" id="costingWarningBanner">
      <div class="d-flex align-items-start gap-3">
        <div class="fs-3 text-warning lh-1 mt-1">
          <i class="fa fa-exclamation-triangle"></i>
        </div>
        <div>
          <h6 class="fw-bold mb-1 text-dark">Product Costing Data Incomplete (FR-48, FR-74)</h6>
          <p class="mb-1 small text-dark">
            The following products do not have a cost calculation result in the <strong>Costing Simulation</strong> module yet:
            <strong class="text-danger">
              <?= htmlspecialchars(implode(', ', array_unique(array_column($costing_check['missing_products'] ?? [], 'nm_produk')))); ?>
            </strong>.
          </p>
          <div class="small text-muted">
            <i class="fa fa-info-circle me-1"></i> You can still save this report as a <strong>Draft</strong>. However, the <strong>Save &amp; Submit Report</strong> button to Accounting will be locked until <em>Update Costing</em> is run on the related variants.
          </div>
        </div>
      </div>
      <div class="ms-3 text-nowrap">
        <a href="<?= site_url('simulasi_costing'); ?>" target="_blank" class="btn btn-warning btn-sm fw-bold">
          <i class="fa fa-calculator me-1"></i> Open Costing Simulation <i class="fa fa-external-link ms-1"></i>
        </a>
      </div>
    </div>
  <?php endif; ?>

  <form id="prodForm" novalidate>
    <input type="hidden" name="spk_no" value="<?= htmlspecialchars((string) $spk['spk_no']); ?>">
    <input type="hidden" name="id_tr_spk_detail" value="<?= !empty($spk['primary_spk_detail_id']) ? $spk['primary_spk_detail_id'] : ''; ?>">
    <input type="hidden" name="report_id" id="reportId" value="<?= !empty($draft['header']['id']) ? $draft['header']['id'] : ''; ?>">

    <!-- 1. INFORMASI LAPORAN -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">1</span> Informasi Laporan</h2>
          <div class="pr-card-hint">SPK data &amp; production schedule (read-only SPK info). Complete the machine and operator data below.</div>
        </div>
      </div>
      <div class="pr-card-body">
        <div class="meta-strip-spk">
          <div class="meta-item">
            <span class="meta-label">SPK Number</span>
            <span class="meta-value"><?= htmlspecialchars((string) $spk['spk_no']); ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">SPK Date</span>
            <span class="meta-value"><?= date('d/m/Y', strtotime((string) $spk['tgl_spk'])); ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Total Target Qty</span>
            <span class="meta-value text-primary"><?= number_format(!empty($spk['target_qty']) ? $spk['target_qty'] : (!empty($spk['total_target_qty']) ? $spk['total_target_qty'] : 0), 0, ',', '.'); ?> pcs</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">SPK Notes</span>
            <span class="meta-value text-muted"><?= !empty($spk['catatan']) ? htmlspecialchars((string) $spk['catatan']) : '-'; ?></span>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label fw-semibold small">Tgl Produksi <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm flatpickr-date" id="tglProduksi" name="tgl_produksi" required placeholder="YYYY-MM-DD" value="<?= date('Y-m-d'); ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold small">Mesin <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm select2" id="id_asset_machine" name="id_asset_machine" required>
              <option value="">-- Pilih Mesin --</option>
              <?php foreach ($machines as $m): ?>
                <option value="<?= $m['id']; ?>"><?= htmlspecialchars((string) $m['nm_asset']); ?> (<?= htmlspecialchars((string) $m['kd_asset']); ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold small">Helper Name <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm select2" id="employee_helper" name="employee_helper[]" multiple="multiple" data-placeholder="-- Select 1 or More Helpers --" required>
              <?php foreach ($helpers as $e): ?>
                <option value="<?= htmlspecialchars((string) $e['nm_karyawan']); ?>"><?= htmlspecialchars((string) $e['nm_karyawan']); ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text small text-muted" style="font-size: 11px;">More than 1 helper can be selected</div>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold small">Setter Name <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm select2" id="employee_setter" name="employee_setter" required>
              <option value="">-- Pilih Setter --</option>
              <?php foreach ($setters as $e): ?>
                <option value="<?= htmlspecialchars((string) $e['nm_karyawan']); ?>"><?= htmlspecialchars((string) $e['nm_karyawan']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold small">Start Time <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm flatpickr-time" id="startTime" name="start_time" required placeholder="HH:mm">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold small">Finished Time <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm flatpickr-time" id="finishedTime" name="finished_time" required placeholder="HH:mm">
          </div>
        </div>
      </div>
    </section>

    <!-- 2. ADD COIL -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">2</span> Add Coil (Sumber Material)</h2>
          <div class="pr-card-hint">Select a baby coil from the Unpack, WIP, or Hold Warehouse. Weight &amp; meter data are pulled automatically.</div>
        </div>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-outline-primary" data-add-coil="unpack"><i class="fa fa-plus me-1"></i> Coil From Unpack</button>
          <button type="button" class="btn btn-outline-warning" data-add-coil="wip"><i class="fa fa-plus me-1"></i> Coil From WIP</button>
          <button type="button" class="btn btn-outline-danger" data-add-coil="hold"><i class="fa fa-plus me-1"></i> Coil From Hold</button>
        </div>
      </div>
      <div class="pr-card-body p-0">
        <div class="table-responsive">
          <table class="tbl-custom" id="coilTable">
            <thead>
              <tr>
                <th style="width:100px">Source</th>
                <th style="width:230px">Baby Coil</th>
                <th>Material Name</th>
                <th class="num" style="width:120px">Nett (kg)<br><small class="text-muted">Pack List</small></th>
                <th class="num" style="width:120px">Gross (kg)<br><small class="text-muted">Pack List</small></th>
                <th class="num" style="width:100px">Total Meter</th>
                <th class="num" style="width:100px">Kulit (kg)</th>
                <th class="num" style="width:110px">Clamp/Ring</th>
                <th style="width:50px"></th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              <tr>
                <td colspan="9" class="empty" id="coilEmpty">No coils added yet. Click one of the buttons above to add a baby coil.</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </section>

    <!-- 3. FG KW 1 & STOK BEBAS -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">3</span> Finish Good KW 1 &amp; Stok Bebas</h2>
          <div class="pr-card-hint">KW 1 is filled from the SPK. Free Stock is for remaining production outside the SPK target. Actual vs standard difference tolerance &plusmn; 0.7%.</div>
        </div>
        <div>
          <span class="text-muted small"><i class="fa fa-info-circle me-1"></i>The maximum number of rows follows the number of coil materials</span>
        </div>
      </div>
      <div class="pr-card-body">
        <div class="pr-subhead">
          <div class="d-flex align-items-center gap-2">
            <h3 class="mb-0">KW 1 Product (from SPK)</h3>
            <span class="badge bg-light text-secondary border small" id="kw1CountBadge">0 / 0 Rows</span>
          </div>
          <button type="button" class="btn btn-sm btn-outline-primary" data-add-fg="kw1"><i class="fa fa-plus me-1"></i> Add Produk KW 1</button>
        </div>
        <div id="fgList"></div>
        <div class="empty text-center text-muted py-2" id="fgEmpty" style="display:none">No KW 1 Product rows added yet. Add a coil material row first.</div>

        <div class="pr-subhead mt-4">
          <div class="d-flex align-items-center gap-2">
            <h3 class="mb-0">Free Stock</h3>
            <span class="badge bg-light text-secondary border small" id="bebasCountBadge">0 / 0 Rows</span>
          </div>
          <button type="button" class="btn btn-sm btn-outline-primary" data-add-fg="bebas"><i class="fa fa-plus me-1"></i> Add Stok Bebas</button>
        </div>
        <div id="bebasList"></div>
        <div class="empty text-center text-muted py-2" id="bebasEmpty">No Free Stock added yet. Add a coil material row first.</div>
      </div>
    </section>

    <!-- 4. FG KW 2 -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">4</span> Finish Good KW 2</h2>
          <div class="pr-card-hint">KW 2 Internal and Supplier are separated. Names are generated automatically: {Produk}-KW2-{I/S}-01, ...</div>
        </div>
      </div>
      <div class="pr-card-body">
        <div class="pr-subhead">
          <h3>KW 2 — Internal</h3>
          <button type="button" class="btn btn-sm btn-outline-secondary" data-add-kw2="internal"><i class="fa fa-plus me-1"></i> Add KW 2 Internal</button>
        </div>
        <div class="table-responsive mb-4">
          <table class="tbl-custom" id="kw2InternalTable">
            <thead>
              <tr>
                <th style="width:40px">No</th>
                <th style="width:240px">Produk Asal</th>
                <th style="width:250px">Baby Coil Terpakai</th>
                <th class="num" style="width:95px">Size (m)</th>
                <th class="num" style="width:95px">Qty</th>
                <th class="num" style="width:110px">Total Weight (kg)</th>
                <th class="num" style="width:110px">Weight/Pcs (kg)</th>
                <th>KW 2 Name</th>
                <th>Description</th>
                <th style="width:40px"></th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              <tr>
                <td colspan="10" class="empty">No KW 2 Internal yet.</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="pr-subhead">
          <h3>KW 2 — Supplier</h3>
          <button type="button" class="btn btn-sm btn-outline-secondary" data-add-kw2="supplier"><i class="fa fa-plus me-1"></i> Add KW 2 Supplier</button>
        </div>
        <div class="table-responsive">
          <table class="tbl-custom" id="kw2SupplierTable">
            <thead>
              <tr>
                <th style="width:40px">No</th>
                <th style="width:240px">Produk Asal</th>
                <th style="width:250px">Baby Coil Terpakai</th>
                <th class="num" style="width:95px">Size (m)</th>
                <th class="num" style="width:95px">Qty</th>
                <th class="num" style="width:110px">Total Weight (kg)</th>
                <th class="num" style="width:110px">Weight/Pcs (kg)</th>
                <th>KW 2 Name</th>
                <th>Description</th>
                <th style="width:40px"></th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              <tr>
                <td colspan="10" class="empty">No KW 2 Supplier yet.</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </section>

    <!-- 5. SISA & HOLD COIL -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">5</span> Sisa Coil &amp; Hold Coil</h2>
          <div class="pr-card-hint">Remaining Coil goes into the WIP Warehouse. Hold Coil rows go into the Material Claim section automatically (FR-98).</div>
        </div>
      </div>
      <div class="pr-card-body">
        <!-- Sisa Coil (Atas) -->
        <div class="mb-4">
          <div class="pr-subhead">
            <div class="d-flex align-items-center gap-2">
              <h3 class="mb-0">Remaining Coil</h3>
              <span class="badge bg-light text-secondary border small" id="sisaCountBadge">0 / 0 Rows</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="addSisa"><i class="fa fa-plus me-1"></i> Add Sisa</button>
          </div>
          <div class="table-responsive">
            <table class="tbl-custom" id="sisaTable">
              <thead>
                <tr>
                  <th>Baby Coil Terpakai</th>
                  <th class="num" style="width:180px">Remaining Weight (kg)</th>
                  <th style="width:50px"></th>
                </tr>
              </thead>
              <tbody></tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="empty">No remaining coils.</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Hold Coil (Bawah) -->
        <div>
          <div class="pr-subhead">
            <div class="d-flex align-items-center gap-2">
              <h3 class="mb-0">Hold Coil</h3>
              <span class="badge bg-light text-secondary border small" id="holdCountBadge">0 / 0 Rows</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" id="addHold"><i class="fa fa-plus me-1"></i> Add Hold</button>
          </div>
          <div class="table-responsive">
            <table class="tbl-custom" id="holdTable">
              <thead>
                <tr>
                  <th>Baby Coil Terpakai</th>
                  <th class="num" style="width:180px">Hold Weight (kg)</th>
                  <th style="width:50px"></th>
                </tr>
              </thead>
              <tbody></tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="empty">No hold coils.</td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="alert alert-warning py-2 mt-2 small" id="holdNote" style="display:none">
            <i class="fa fa-info-circle me-1"></i> There is a Hold Coil — claim rows are created automatically in <strong>Card 7. Claim Material</strong>. You can save the report as a <strong>Draft</strong>, but <strong>Submit</strong> is locked until Purchasing decides the claim (FR-18, FR-98).
          </div>
        </div>
      </div>
    </section>

    <!-- 6. KOMPONEN SCRAP -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">6</span> Komponen Scrap</h2>
          <div class="pr-card-hint">Input production scrap weights (Drum, Wrapping, Knife Trimmings, and Internal &amp; Supplier Rejects).</div>
        </div>
      </div>
      <div class="pr-card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold small">Tong Coil (kg)</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in" id="scrapTong" value="0">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold small">Wrapping (kg)</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in" id="scrapWrapping" value="0">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold small">Potongan Pisau (kg)</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in" id="scrapPisau" value="0">
          </div>
        </div>

        <div class="pr-subhead">
          <h3>Reject Produksi &amp; Material</h3>
        </div>
        <div class="table-responsive">
          <table class="tbl-custom">
            <thead>
              <tr>
                <th style="width:140px">Kelompok</th>
                <th class="num" style="width:180px">Reject Product (kg)</th>
                <th class="num" style="width:180px">Reject Material Plat (kg)</th>
                <th>Reject Description</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Internal</strong></td>
                <td class="num"><input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in text-end" id="rejProdInt" value="0"></td>
                <td class="num"><input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in text-end" id="rejMatInt" value="0"></td>
                <td><input type="text" class="form-control form-control-sm" id="rejKetInt" placeholder="Internal reject notes..."></td>
              </tr>
              <tr>
                <td><strong>Supplier</strong></td>
                <td class="num"><input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in text-end" id="rejProdSup" value="0"></td>
                <td class="num"><input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in text-end" id="rejMatSup" value="0"></td>
                <td><input type="text" class="form-control form-control-sm" id="rejKetSup" placeholder="Supplier reject notes..."></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end align-items-center mt-3 p-2 bg-light rounded border">
          <span class="me-3 fw-semibold text-muted">Total Inventory Scrap:</span>
          <span class="fs-5 fw-bold text-dark" id="scrapTotal">0,00 kg</span>
        </div>
      </div>
    </section>

    <!-- 7. CLAIM MATERIAL / AJUKAN CLAIM (FR-98, FR-94) -->
    <section class="pr-card" id="cardClaimMaterial">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">7</span> Claim Material / Ajukan Claim <span class="badge bg-light text-secondary border small ms-2" id="claimCountBadge">0 Rows</span></h2>
          <div class="pr-card-hint">Hold Coil, KW 2 (Supplier), and Reject (Supplier) rows are detected automatically and appear here. Complete the Problem, Production Reason, and upload evidence files (max. 5 MB/file).</div>
        </div>
      </div>
      <div class="pr-card-body">
        <div id="claimContainer">
          <div class="table-responsive">
            <table class="tbl-custom" id="tblClaimMaterial">
              <thead>
                <tr>
                  <th style="width:110px">Asal</th>
                  <th style="width:140px">Baby Coil</th>
                  <th>Material Name</th>
                  <th>Supplier</th>
                  <th>No. PO</th>
                  <th>No. Coil PL</th>
                  <th class="num" style="width:110px">Weight (kg)</th>
                  <th class="num" style="width:110px">Price/Kg</th>
                  <th class="num" style="width:110px">Costbook/Kg</th>
                  <th class="num" style="width:110px">Price/Unit PO</th>
                  <th style="width:150px">Problem <span class="text-danger">*</span></th>
                  <th style="width:180px">Alasan Produksi <span class="text-danger">*</span></th>
                  <th style="width:230px">Bukti Pendukung <span class="text-danger">*</span> (Maks 5MB)</th>
                  <th style="width:100px">Status Claim</th>
                </tr>
              </thead>
              <tbody id="claimTbody">
                <tr><td colspan="14" class="empty text-muted">There is no Hold Coil, KW 2 (Supplier), or Reject (Supplier) in this report.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="alert alert-info py-2 mt-2 small">
          <i class="fa fa-info-circle me-1"></i> <strong>Required Supporting Documents:</strong> Attach at least 1 file per claim row (photo of the coil defect, video of the problem, or QC official report; maximum 5 MB per file). The files will be saved with the claim ticket to Purchasing.
        </div>
      </div>
    </section>

    <!-- 8. SUMMARY PACKING LIST VS AKTUAL -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">8</span> Summary — Packing List vs Aktual</h2>
          <div class="pr-card-hint">Total Net Weight Produksi vs Net Weight Packing List. Toleransi &plusmn; 0.7%.</div>
        </div>
      </div>
      <div class="pr-card-body">
        <div class="summary-grid" id="summaryGrid">
          <div class="summary-box">
            <span class="sm-k">Finish Good (KW 1)</span>
            <span class="sm-v" id="sumFG">0,00 kg</span>
          </div>
          <div class="summary-box">
            <span class="sm-k">KW 2 Total</span>
            <span class="sm-v" id="sumKW2">0,00 kg</span>
          </div>
          <div class="summary-box">
            <span class="sm-k">Scrap Total</span>
            <span class="sm-v" id="sumScrap">0,00 kg</span>
          </div>
          <div class="summary-box">
            <span class="sm-k">Total Remaining Coil</span>
            <span class="sm-v" id="sumSisa">0,00 kg</span>
          </div>
          <div class="summary-box">
            <span class="sm-k">Hold Coil</span>
            <span class="sm-v" id="sumHold">0,00 kg</span>
          </div>
          <div class="summary-box accent">
            <span class="sm-k">Net Weight Produksi</span>
            <span class="sm-v text-primary" id="sumNetProd">0,00 kg</span>
          </div>
          <div class="summary-box">
            <span class="sm-k">Net Weight Packing List</span>
            <span class="sm-v" id="sumNetPack">0,00 kg</span>
          </div>
          <div class="summary-box">
            <span class="sm-k">Selisih (kg)</span>
            <span class="sm-v" id="sumSelisihKg">0,00 kg</span>
          </div>
          <div class="summary-box result" id="sumSelisihBox">
            <span class="sm-k">Selisih (%)</span>
            <span class="sm-v" id="sumSelisihPct">0,00 %</span>
          </div>
        </div>
        <div class="mt-3">
          <p class="small text-muted mb-0" id="tolNote">Toleransi &plusmn; 0,7% terhadap Net Weight Produksi.</p>
        </div>
      </div>
    </section>

    <!-- 9. AUDIT KONFIRMASI SELISIH -->
    <section class="pr-card" id="auditCard" style="display:none">
      <div class="pr-card-header bg-light">
        <div>
          <h2 class="pr-card-title text-danger"><span class="pr-card-num text-danger">9</span> Jejak Konfirmasi Selisih</h2>
          <div class="pr-card-hint">List of confirmed differences outside the &plusmn; 0.7% tolerance, with confirmer identity and time.</div>
        </div>
      </div>
      <div class="pr-card-body p-0">
        <table class="tbl-custom" id="auditTable">
          <thead>
            <tr>
              <th style="width:40px">No</th>
              <th>Objek</th>
              <th class="num" style="width:120px">% Selisih</th>
              <th>Pernyataan</th>
              <th style="width:200px">Confirmed By</th>
              <th style="width:160px">Waktu</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </section>

    <!-- ACTION BAR -->
    <div class="actionbar">
      <div>
        <span id="gateMsg" class="text-muted small">Save Draft is always active. Submit is locked if there is a Hold Coil awaiting a Purchasing decision.</span>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary px-3" id="btnDraft">
          <i class="fa fa-save me-1"></i> Save Draft
        </button>
        <button type="submit" class="btn btn-primary px-4" id="btnSave">
          <i class="fa fa-check-circle me-1"></i> Save &amp; Submit Report
        </button>
      </div>
    </div>
  </form>
</div>

<!-- MODAL TOLERANSI -->
<div class="modal fade" id="tolModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-dark py-3">
        <h5 class="modal-title fw-bold"><i class="fa fa-exclamation-triangle me-2"></i> Konfirmasi Selisih Di Luar Toleransi</h5>
      </div>
      <div class="modal-body p-4">
        <p class="fs-6" id="tolModalBody"></p>
        <div class="p-3 bg-light rounded border mt-3 small">
          Konfirmasi dicatat atas nama: <strong id="tolSigner"><?= htmlspecialchars($this->auth->user_name()); ?></strong>
        </div>
      </div>
      <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-secondary btn-sm" id="tolCancel" data-bs-dismiss="modal">Perbaiki Data</button>
        <button type="button" class="btn btn-primary btn-sm" id="tolAck">Matches Actual</button>
      </div>
    </div>
  </div>
</div>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
  // GLOBAL CONSTANTS & DATA FROM SERVER
  var SPK = <?= json_encode($spk); ?>;
  var SPK_PRODUCTS = <?= json_encode($spk_products); ?>;
  var ALL_PRODUCTS = <?= json_encode($all_products); ?>;
  var DRAFT_DATA = <?= !empty($draft) ? json_encode($draft) : 'null'; ?>;
  var COSTING_CHECK = <?= json_encode(!empty($costing_check) ? $costing_check : ['is_all_available' => true, 'missing_products' => []]); ?>;
  var SIMULASI_COSTING_URL = "<?= site_url('simulasi_costing'); ?>";
  var CURRENT_USER = {
    name: <?= json_encode($this->auth->user_name()); ?>,
    role: "User"
  };
  var SAVE_URL = "<?= site_url('production_report/save'); ?>";
  var COIL_URL = "<?= site_url('production_report/ajax_get_coils'); ?>";
  var BASE_MODULE_URL = "<?= site_url('production_report'); ?>";
  var CLAIM_INFO_URL = "<?= site_url('production_report/ajax_get_coil_claim_info'); ?>";
  var UPLOAD_CLAIM_URL = "<?= site_url('production_report/upload_claim_bukti'); ?>";
  var TOLERANCE = 0.7; // ± 0.7%

  var SRC_LABEL = {
    unpack: "Unpack Warehouse",
    wip: "WIP Warehouse",
    hold: "Hold Warehouse"
  };

  // GLOBAL STATE
  var state = {
    coils: [], // { id, source, id_warehouse_stock_coil, code, material, nett, gross, meter, kulit, clamp }
    fg: [], // { id, kind:'kw1'|'bebas', productId, productName, method, qty, total, perPcs, std, selisih, coil, confirmedAt }
    kw2: {
      internal: [],
      supplier: []
    },
    sisa: [], // { id, coil, berat }
    hold: [], // { id, coil, berat }
    confirmations: [],
    claimIn: {}, // { [key]: { problem, alasan, bukti: [], coilCode, status } }
    availableCoils: {
      unpack: [],
      wip: [],
      hold: []
    }
  };

  var seq = 0;

  function uid() {
    seq++;
    return 'r' + seq;
  }

  // Helper Inisialisasi Select2 Searchable
  function initSelect2Search(container) {
    if (!$.fn || !$.fn.select2) return;
    var $target = container ? $(container).find('.select2') : $('.select2');
    if (container && $(container).hasClass('select2')) {
      $target = $target.add($(container));
    }
    $target.each(function() {
      var $el = $(this);
      if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
      }
      $el.select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: function() {
          return $(this).data('placeholder') || $(this).find('option:first').text() || '-- Select --';
        },
        allowClear: true
      });
    });
  }

  // UTILITIES
  function formatNum(val, dec) {
    dec = (dec !== undefined) ? dec : 2;
    return (parseFloat(val) || 0).toLocaleString('en-US', {
      minimumFractionDigits: dec,
      maximumFractionDigits: dec
    });
  }

  function getNum(val) {
    if (typeof val === 'number') return isNaN(val) ? 0 : val;
    return parseFloat(String(val || '').replace(/,/g, '')) || 0;
  }

  function productById(id) {
    for (var i = 0; i < ALL_PRODUCTS.length; i++) {
      if (String(ALL_PRODUCTS[i].id) === String(id)) {
        return ALL_PRODUCTS[i];
      }
    }
    return null;
  }

  function coilOptions(currentFgId, currentKind) {
    var usedCoils = {};
    if (currentFgId && currentKind) {
      $.each(state.fg, function(i, f) {
        // Hanya cek keunikan dalam grup/kategori yang sama (kw1 dengan sesama kw1, bebas dengan sesama bebas)
        if (f.id !== currentFgId && f.kind === currentKind && f.coil) {
          usedCoils[f.coil] = true;
        }
      });
    }
    var opts = '<option value="">— Select Used Baby Coil —</option>';
    $.each(state.coils, function(i, c) {
      if (c.code && !usedCoils[c.code]) {
        opts += '<option value="' + c.code + '">' + c.code + ' · ' + c.material + '</option>';
      }
    });
    return opts;
  }

  function lineCoilOptions(currentLineId, currentKind) {
    var usedCoils = {};
    if (currentLineId && currentKind) {
      $.each(state[currentKind], function(i, r) {
        if (r.id !== currentLineId && r.coil) {
          usedCoils[r.coil] = true;
        }
      });
    }
    var opts = '<option value="">— Select Used Baby Coil —</option>';
    $.each(state.coils, function(i, c) {
      if (c.code && !usedCoils[c.code]) {
        opts += '<option value="' + c.code + '">' + c.code + ' · ' + c.material + '</option>';
      }
    });
    return opts;
  }

  function kw2CoilOptions(currentKw2Id, currentType) {
    var opts = '<option value="">— Select Used Baby Coil —</option>';
    $.each(state.coils, function(i, c) {
      if (c.code) {
        opts += '<option value="' + c.code + '">' + c.code + ' · ' + c.material + '</option>';
      }
    });
    return opts;
  }

  function allProductOptions(selectedId) {
    var opts = '<option value="">— Select Master Product —</option>';
    $.each(ALL_PRODUCTS, function(i, p) {
      var sel = (String(p.id) === String(selectedId)) ? ' selected' : '';
      var w = p.weight ? p.weight : 0;
      opts += '<option value="' + p.id + '"' + sel + '>' + p.nama + ' (' + w + ' kg/pcs)</option>';
    });
    return opts;
  }

  function spkProductOptions(selectedId) {
    var opts = '<option value="">— Select Product from SPK —</option>';
    $.each(SPK_PRODUCTS, function(i, sp) {
      var prodId = sp.id_product_lvl_4 || sp.product_lvl_4_id || sp.id_produk_fg;
      var sel = (String(prodId) === String(selectedId)) ? ' selected' : '';
      var target = sp.target_qty ? sp.target_qty : 0;
      opts += '<option value="' + prodId + '"' + sel + '>' + sp.nm_produk_fg + ' (Target: ' + target + ' pcs)</option>';
    });
    return opts;
  }

  function babyCoilSelectOptions(source, selectedCode, selectedMaterial) {
    var list = state.availableCoils[source] || [];
    var usedElsewhere = {};
    $.each(state.coils, function(i, c) {
      if (c.code && c.code !== selectedCode) {
        usedElsewhere[c.code] = true;
      }
    });

    var opts = '<option value="">— Select Baby Coil —</option>';
    var foundSelected = false;

    $.each(list, function(i, c) {
      if (!usedElsewhere[c.no_coil]) {
        var sel = '';
        if (c.no_coil === selectedCode) {
          sel = ' selected';
          foundSelected = true;
        }
        var mat = c.nm_material || c.lot_material || 'Coil';
        opts += '<option value="' + c.no_coil + '"' + sel + '>' + c.no_coil + ' · ' + mat + '</option>';
      }
    });

    // Fallback penting jika selectedCode ada tapi belum ada di list (misal saat buka draft / ajax belum selesai)
    if (selectedCode && !foundSelected) {
      var label = selectedCode + (selectedMaterial ? ' · ' + selectedMaterial : '');
      opts += '<option value="' + selectedCode + '" selected>' + label + '</option>';
    }

    return opts;
  }

  // ── Fetch Baby Coils via AJAX ──
  function loadCoilOptions(source, callback) {
    if (state.availableCoils[source] && state.availableCoils[source].length > 0) {
      if (callback) callback(state.availableCoils[source]);
      return;
    }

    $.ajax({
      url: COIL_URL,
      type: 'GET',
      data: {
        source: source
      },
      dataType: 'json',
      success: function(res) {
        if (res.status == 1 && res.data) {
          state.availableCoils[source] = res.data;
        } else {
          state.availableCoils[source] = [];
        }
        if (callback) callback(state.availableCoils[source]);
      },
      error: function() {
        state.availableCoils[source] = [];
        if (callback) callback([]);
      }
    });
  }

  // ── SECTION 2: COILS ──
  function addCoil(source) {
    loadCoilOptions(source, function() {
      state.coils.push({
        id: uid(),
        source: source,
        id_warehouse_stock_coil: null,
        code: '',
        material: '',
        nett: null,
        gross: null,
        meter: null,
        kulit: null,
        clamp: null
      });
      renderCoils();
      // Jika KW1 masih 0 dan ada produk SPK, tambahkan 1 baris KW 1 otomatis
      var currentKw1 = $.grep(state.fg, function(f) {
        return f.kind === 'kw1';
      }).length;
      if (currentKw1 === 0 && SPK_PRODUCTS && SPK_PRODUCTS.length > 0) {
        addKw1();
      } else {
        renderFg();
      }
      recalc();
      $('#coilTable tbody tr:last-child select').focus();
    });
  }

  function renderCoils() {
    var html = '';
    $.each(state.coils, function(i, c) {
      var chosen = !!c.code;
      html += '<tr data-id="' + c.id + '">';
      html += '  <td><span class="src-tag src-tag-' + c.source + '">' + (SRC_LABEL[c.source] || c.source) + '</span></td>';
      html += '  <td><select data-coil-pick="' + c.id + '" class="form-select form-select-sm select2 ' + (chosen ? '' : 'border-primary') + '">' +
        babyCoilSelectOptions(c.source, c.code, c.material) +
        '</select></td>';
      html += '  <td>' + (chosen ? c.material : '—') + '</td>';
      html += '  <td class="num">' + (c.nett != null ? formatNum(c.nett) : '—') + '</td>';
      html += '  <td class="num">' + (c.gross != null ? formatNum(c.gross) : '—') + '</td>';
      html += '  <td class="num">' + (c.meter != null ? c.meter : '—') + '</td>';
      html += '  <td class="num">' + (c.kulit != null ? formatNum(c.kulit) : '—') + '</td>';
      html += '  <td class="num">' + (c.clamp != null ? formatNum(c.clamp) : '—') + '</td>';
      html += '  <td class="text-center">';
      html += '    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" data-del-coil="' + c.id + '" title="Delete">✕</button>';
      html += '  </td>';
      html += '</tr>';
    });

    $('#coilTable tbody').html(html);
    if (state.coils.length > 0) {
      $('#coilEmpty').closest('tfoot').hide();
    } else {
      $('#coilEmpty').closest('tfoot').show();
    }

    // Set nilai pada select sebelum dan sesudah initSelect2
    $.each(state.coils, function(i, c) {
      if (c.code) {
        $('[data-coil-pick="' + c.id + '"]').val(c.code);
      }
    });

    initSelect2Search('#coilTable');

    $.each(state.coils, function(i, c) {
      if (c.code) {
        $('[data-coil-pick="' + c.id + '"]').val(c.code);
      }
    });
  }

  function pickCoil(id, code) {
    var row = null;
    $.each(state.coils, function(i, c) {
      if (c.id === id) {
        row = c;
        return false;
      }
    });
    if (!row) return;

    // Jika code sama dengan yang sudah ada dan detail sudah terisi (misal saat restore draft), tidak perlu proses ulang
    if (code && row.code === code && row.material) {
      return;
    }

    // Validasi: pastikan coil belum dipilih di baris Add Coil lainnya
    if (code) {
      var alreadyCoil = false;
      $.each(state.coils, function(i, c) {
        if (c.id !== id && c.code === code) {
          alreadyCoil = true;
          return false;
        }
      });
      if (alreadyCoil) {
        Swal.fire('Material Already Selected', 'Baby Coil "' + code + '" has already been selected in another row in Material Source.', 'warning');
        renderCoils();
        return;
      }
    }

    if (!code) {
      row.id_warehouse_stock_coil = null;
      row.code = '';
      row.material = '';
      row.nett = null;
      row.gross = null;
      row.meter = null;
      row.kulit = null;
      row.clamp = null;
    } else {
      var sourceList = state.availableCoils[row.source] || [];
      var coilData = null;
      $.each(sourceList, function(i, c) {
        if (c.no_coil === code) {
          coilData = c;
          return false;
        }
      });

      if (coilData) {
        row.id_warehouse_stock_coil = coilData.id;
        row.code = coilData.no_coil;
        row.material = coilData.nm_material || coilData.lot_material || 'Coil Material';
        row.nett = getNum(coilData.weight);
        row.gross = getNum(coilData.gross_weight || coilData.weight);
        row.meter = getNum(coilData.total_meter || coilData.panjang);
        row.kulit = getNum(coilData.berat_kulit);
        row.clamp = getNum(coilData.berat_clamp);
      }
    }

    renderCoils();
    refreshCoilSelectors();
    recalc();
  }

  function refreshCoilSelectors() {
    renderFg();
    renderKw2('internal');
    renderKw2('supplier');
    renderLines('sisa');
    renderLines('hold');
  }

  // ── SECTION 3: FINISH GOOD KW 1 & STOK BEBAS ──
  function initFgFromSpk() {
    // Initial setup: jika ada SPK_PRODUCTS dan coils belum ada, tunggu user input material
    renderFg();
  }

  function addKw1() {
    var maxCoils = state.coils.length;
    if (maxCoils === 0) {
      Swal.fire('Attention', 'Please add a Baby Coil in Material Source (Section 2) first.', 'warning');
      return;
    }

    var currentKw1 = $.grep(state.fg, function(f) {
      return f.kind === 'kw1';
    }).length;
    if (currentKw1 >= maxCoils) {
      Swal.fire('Maximum Reached', 'KW 1 Product row count (' + currentKw1 + ') cannot exceed the number of coil materials (' + maxCoils + ' rows).', 'warning');
      return;
    }

    // Default ke item SPK pertama bila ada
    var defaultSpk = (SPK_PRODUCTS && SPK_PRODUCTS.length > 0) ? SPK_PRODUCTS[0] : null;

    var defProdId = defaultSpk ? (defaultSpk.id_product_lvl_4 || defaultSpk.product_lvl_4_id || defaultSpk.id_produk_fg) : '';
    var defStdWeight = defaultSpk ? getNum(defaultSpk.weight_standard || defaultSpk.berat_standar || defaultSpk.berat_per_unit) : 0;

    state.fg.push({
      id: uid(),
      kind: 'kw1',
      productId: defProdId,
      productName: defaultSpk ? defaultSpk.nm_produk_fg : '',
      targetQty: defaultSpk ? getNum(defaultSpk.target_qty) : 0,
      method: 1, // 1: Qty + Total; 2: Qty + PerPcs
      qty: 0,
      total: 0,
      perPcs: 0,
      std: defStdWeight,
      selisih: 0,
      coil: ''
    });
    renderFg();
    recalc();
  }

  function addBebas() {
    var maxCoils = state.coils.length;
    if (maxCoils === 0) {
      Swal.fire('Attention', 'Please add a Baby Coil in Material Source (Section 2) first.', 'warning');
      return;
    }

    var currentBebas = $.grep(state.fg, function(f) {
      return f.kind === 'bebas';
    }).length;
    if (currentBebas >= maxCoils) {
      Swal.fire('Maximum Reached', 'Free Stock row count (' + currentBebas + ') cannot exceed the number of coil materials (' + maxCoils + ' rows).', 'warning');
      return;
    }

    state.fg.push({
      id: uid(),
      kind: 'bebas',
      productId: '',
      productName: '',
      targetQty: null,
      method: 1,
      qty: 0,
      total: 0,
      perPcs: 0,
      std: 0,
      selisih: 0,
      coil: ''
    });
    renderFg();
    recalc();
  }

  function fgCard(f) {
    var isBebas = (f.kind === 'bebas');
    var p1 = (f.method === 1);
    var confirmed = (f.confirmedAt != null && f.confirmedAt === f.selisih && Math.abs(f.selisih) > TOLERANCE);

    var productHeader = '';
    if (isBebas) {
      productHeader = '<div style="min-width:280px; max-width:360px;"><select data-fg-product="' + f.id + '" class="form-select form-select-sm select2" style="width:100%">' +
        allProductOptions(f.productId) +
        '</select></div>';
    } else {
      if (SPK_PRODUCTS && SPK_PRODUCTS.length > 1) {
        productHeader = '<div style="min-width:280px; max-width:360px;"><select data-fg-product="' + f.id + '" class="form-select form-select-sm select2" style="width:100%">' +
          spkProductOptions(f.productId) +
          '</select></div>';
      } else {
        productHeader = '<span class="fg-prod-name">' + (f.productName || 'Produk SPK') + '</span>';
      }
      if (f.targetQty != null) {
        productHeader += '<span class="badge bg-light text-primary border ms-2" data-fg-target-badge="' + f.id + '">Target SPK: ' + f.targetQty + ' pcs</span>';
      }
    }

    var delBtn = '<button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" data-del-fg="' + f.id + '" title="Delete Row">✕</button>';

    var html = '';
    html += '<div class="fg-card" data-id="' + f.id + '">';
    html += '  <div class="fg-card-head">';
    html += '    <div class="d-flex align-items-center gap-2">';
    html += '      ' + productHeader;
    html += '      <span class="confirm-badge ms-2" data-fg-confirm="' + f.id + '" ' + (confirmed ? '' : 'style="display:none"') + '>✓ Selisih Dikonfirmasi</span>';
    html += '    </div>';
    html += '    ' + delBtn;
    html += '  </div>';
    html += '  <div>';
    html += '    <div class="method-toggle mb-2">';
    html += '      <label><input type="radio" name="m-' + f.id + '" value="1" ' + (p1 ? 'checked' : '') + ' data-fg-method="' + f.id + '"> <span>Method 1: Qty + Total Weight</span></label>';
    html += '      <label><input type="radio" name="m-' + f.id + '" value="2" ' + (!p1 ? 'checked' : '') + ' data-fg-method="' + f.id + '"> <span>Method 2: Qty + Weight/Pcs</span></label>';
    html += '    </div>';

    // Baris 1: Input Pokok
    html += '    <div class="row g-2 align-items-end mb-2">';
    html += '      <div class="col-md-3">';
    html += '        <label class="form-label small fw-semibold mb-1">Baby Coil Terpakai</label>';
    html += '        <select data-fg-coil="' + f.id + '" data-kind="' + f.kind + '" class="form-select form-select-sm select2" style="width:100%">' + coilOptions(f.id, f.kind) + '</select>';
    html += '      </div>';
    html += '      <div class="col-md-3">';
    html += '        <label class="form-label small fw-semibold mb-1">Qty (pcs)</label>';
    html += '        <input type="number" min="0" step="1" data-fg-qty="' + f.id + '" class="form-control form-control-sm text-end" value="' + (f.qty || '') + '">';
    html += '      </div>';
    html += '      <div class="col-md-3">';
    html += '        <label class="form-label small fw-semibold mb-1">' + (p1 ? 'Total Weight (kg)' : 'Weight/Pcs (kg)') + '</label>';
    html += '        <input type="number" min="0" step="0.01" data-fg-input="' + f.id + '" class="form-control form-control-sm text-end" value="' + (p1 ? (f.total || '') : (f.perPcs || '')) + '">';
    html += '      </div>';
    html += '      <div class="col-md-3">';
    html += '        <label class="form-label small fw-semibold mb-1">' + (p1 ? 'Weight/Pcs (kg)' : 'Total Weight (kg)') + '</label>';
    html += '        <input type="text" class="form-control form-control-sm bg-light text-end" readonly data-fg-derived="' + f.id + '" value="' + (p1 ? formatNum(f.perPcs) : formatNum(f.total)) + '">';
    html += '      </div>';
    html += '    </div>';

    // Baris 2: Sub-Card Metrik Per Pcs & Per Meter (Aktual, Standar BOM, % Selisih)
    html += '    <div class="p-2 rounded bg-light border row g-2 align-items-center mt-1">';
    html += '      <div class="col-md-2">';
    html += '        <label class="form-label small text-muted mb-0 d-block">Std / Pcs</label>';
    html += '        <input type="text" class="form-control form-control-sm bg-white text-end" readonly data-fg-std="' + f.id + '" value="' + (f.std ? formatNum(f.std) : '-') + '">';
    html += '      </div>';
    html += '      <div class="col-md-2">';
    html += '        <label class="form-label small text-muted mb-0 d-block">% Selisih / Pcs</label>';
    html += '        <div><span class="selisih-badge ' + (Math.abs(f.selisih) > TOLERANCE ? 'bad' : 'ok') + '" data-fg-selisih="' + f.id + '">' + formatNum(f.selisih) + ' %</span></div>';
    html += '      </div>';
    html += '      <div class="col-md-2 border-start">';
    html += '        <label class="form-label small text-muted mb-0 d-block">Length (m)</label>';
    html += '        <input type="text" class="form-control form-control-sm bg-white text-end" readonly data-fg-panjang="' + f.id + '" value="' + (f.panjang ? formatNum(f.panjang) : '-') + '">';
    html += '      </div>';
    html += '      <div class="col-md-2">';
    html += '        <label class="form-label small text-muted mb-0 d-block">Actual Weight / m</label>';
    html += '        <input type="text" class="form-control form-control-sm bg-white text-end text-primary fw-bold" readonly data-fg-berat-meter="' + f.id + '" value="' + (f.beratPerMeterAktual ? formatNum(f.beratPerMeterAktual, 4) : '-') + '">';
    html += '      </div>';
    html += '      <div class="col-md-2">';
    html += '        <label class="form-label small text-muted mb-0 d-block">Std / Meter (BOM)</label>';
    html += '        <input type="text" class="form-control form-control-sm bg-white text-end" readonly data-fg-std-meter="' + f.id + '" value="' + (f.stdPerMeter ? formatNum(f.stdPerMeter, 4) : '-') + '">';
    html += '      </div>';
    html += '      <div class="col-md-2">';
    html += '        <label class="form-label small text-muted mb-0 d-block">% Selisih / Meter</label>';
    html += '        <div><span class="selisih-badge ' + (Math.abs(f.selisihMeter) > TOLERANCE ? 'bad' : 'ok') + '" data-fg-selisih-meter="' + f.id + '">' + formatNum(f.selisihMeter) + ' %</span></div>';
    html += '      </div>';
    html += '    </div>';

    html += '  </div>';
    html += '</div>';

    return html;
  }

  function renderFg() {
    var kw1 = [];
    var bebas = [];
    $.each(state.fg, function(i, f) {
      if (f.kind === 'kw1') kw1.push(f);
      else bebas.push(f);
    });

    var kw1Html = '';
    $.each(kw1, function(i, f) {
      kw1Html += fgCard(f);
    });
    $('#fgList').html(kw1Html);

    var bebasHtml = '';
    $.each(bebas, function(i, f) {
      bebasHtml += fgCard(f);
    });
    $('#bebasList').html(bebasHtml);

    var maxCoils = state.coils.length;
    $('#kw1CountBadge').text(kw1.length + ' / ' + maxCoils + ' Row');
    $('#bebasCountBadge').text(bebas.length + ' / ' + maxCoils + ' Row');

    if (kw1.length > 0) $('#fgEmpty').hide();
    else $('#fgEmpty').show();
    if (bebas.length > 0) $('#bebasEmpty').hide();
    else $('#bebasEmpty').show();

    // Disable add buttons if max reached or coils = 0
    $('[data-add-fg="kw1"]').prop('disabled', (maxCoils === 0 || kw1.length >= maxCoils));
    $('[data-add-fg="bebas"]').prop('disabled', (maxCoils === 0 || bebas.length >= maxCoils));

    // Restore values in select inputs
    $.each(state.fg, function(i, f) {
      if (f.productId) $('[data-fg-product="' + f.id + '"]').val(f.productId);
      if (f.coil) $('[data-fg-coil="' + f.id + '"]').val(f.coil);
    });
    initSelect2Search('#fgList, #bebasList');
  }

  function computeFg(f) {
    if (f.kind === 'bebas') {
      var p = productById(f.productId);
      f.std = p ? getNum(p.weight) : 0;
      f.panjang = p ? getNum(p.panjang || p.panjang_produk || p.length) : 0;
      f.stdPerMeter = p ? getNum(p.std_per_meter) : 0;
    } else {
      var sp = null;
      $.each(SPK_PRODUCTS, function(i, item) {
        var pId = item.id_product_lvl_4 || item.product_lvl_4_id || item.id_produk_fg;
        if (String(pId) === String(f.productId)) {
          sp = item;
          return false;
        }
      });
      if (sp) {
        f.std = getNum(sp.weight_standard || sp.berat_standar || sp.berat_per_unit);
        f.targetQty = getNum(sp.target_qty);
        f.productName = sp.nm_produk_fg;
        f.panjang = getNum(sp.panjang || sp.panjang_m || sp.length);
        f.stdPerMeter = getNum(sp.std_per_meter);
      }
    }
    if (f.method === 1) {
      f.perPcs = (f.qty > 0) ? f.total / f.qty : 0;
    } else {
      f.total = f.qty * f.perPcs;
    }
    f.selisih = (f.std > 0) ? ((f.perPcs - f.std) / f.std) * 100 : 0;

    // Perhitungan Metrik Weight per Meter (Aktual, Std BOM, % Selisih)
    f.beratPerMeterAktual = (f.panjang > 0) ? (f.perPcs / f.panjang) : 0;
    f.selisihMeter = (f.stdPerMeter > 0) ? ((f.beratPerMeterAktual - f.stdPerMeter) / f.stdPerMeter) * 100 : 0;
  }

  function updateFgDerivedView() {
    $.each(state.fg, function(i, f) {
      var $der = $('[data-fg-derived="' + f.id + '"]');
      var $std = $('[data-fg-std="' + f.id + '"]');
      var $sel = $('[data-fg-selisih="' + f.id + '"]');
      var $panjang = $('[data-fg-panjang="' + f.id + '"]');
      var $beratMeter = $('[data-fg-berat-meter="' + f.id + '"]');
      var $stdMeter = $('[data-fg-std-meter="' + f.id + '"]');
      var $selMeter = $('[data-fg-selisih-meter="' + f.id + '"]');
      var $badge = $('[data-fg-confirm="' + f.id + '"]');

      if ($der.length) $der.val(f.method === 1 ? formatNum(f.perPcs) : formatNum(f.total));
      if ($std.length) $std.val(f.std ? formatNum(f.std) : '-');
      if ($sel.length) {
        $sel.text(formatNum(f.selisih) + ' %');
        $sel.removeClass('ok bad').addClass(Math.abs(f.selisih) > TOLERANCE ? 'bad' : 'ok');
      }
      if ($panjang.length) $panjang.val(f.panjang ? formatNum(f.panjang) : '-');
      if ($beratMeter.length) $beratMeter.val(f.beratPerMeterAktual ? formatNum(f.beratPerMeterAktual, 4) : '-');
      if ($stdMeter.length) $stdMeter.val(f.stdPerMeter ? formatNum(f.stdPerMeter, 4) : '-');
      if ($selMeter.length) {
        $selMeter.text(formatNum(f.selisihMeter) + ' %');
        $selMeter.removeClass('ok bad').addClass(Math.abs(f.selisihMeter) > TOLERANCE ? 'bad' : 'ok');
      }
      if ($badge.length) {
        var isOk = (f.confirmedAt != null && f.confirmedAt === f.selisih && Math.abs(f.selisih) > TOLERANCE);
        if (isOk) $badge.show();
        else $badge.hide();
      }
    });
  }

  // ── SECTION 4: FG KW 2 ──
  function addKw2(type) {
    state.kw2[type].push({
      id: uid(),
      productId: '',
      coil: '',
      size: 0,
      qty: 0,
      total: 0,
      ket: ''
    });
    renderKw2(type);
    recalc();
  }

  function renderKw2(type) {
    var tableId = (type === 'internal') ? '#kw2InternalTable' : '#kw2SupplierTable';
    var rows = state.kw2[type];
    var inisial = (type === 'supplier') ? 'S' : 'I';

    var html = '';
    $.each(rows, function(i, r) {
      var p = productById(r.productId);
      var perPcs = (r.qty > 0) ? r.total / r.qty : 0;
      var numStr = (i + 1 < 10) ? '0' + (i + 1) : (i + 1);
      var nama = p ? (p.nama + '-KW2-' + inisial + '-' + numStr) : '—';

      html += '<tr data-id="' + r.id + '" data-type="' + type + '">';
      html += '  <td>' + (i + 1) + '</td>';
      html += '  <td><select data-kw2-product="' + r.id + '" class="form-select form-select-sm select2" style="width:100%">' + allProductOptions(r.productId) + '</select></td>';
      html += '  <td><select data-kw2-coil="' + r.id + '" data-type="' + type + '" class="form-select form-select-sm select2" style="width:100%">' + kw2CoilOptions(r.id, type) + '</select></td>';
      html += '  <td class="num"><input type="number" min="0" step="0.01" data-kw2-size="' + r.id + '" class="form-control form-control-sm text-end" value="' + (r.size || '') + '"></td>';
      html += '  <td class="num"><input type="number" min="0" step="1" data-kw2-qty="' + r.id + '" class="form-control form-control-sm text-end" value="' + (r.qty || '') + '"></td>';
      html += '  <td class="num"><input type="number" min="0" step="0.01" data-kw2-total="' + r.id + '" class="form-control form-control-sm text-end" value="' + (r.total || '') + '"></td>';
      html += '  <td class="num text-muted kw2-perpcs-col">' + formatNum(perPcs) + '</td>';
      html += '  <td><span class="badge bg-secondary">' + nama + '</span></td>';
      html += '  <td><input type="text" data-kw2-ket="' + r.id + '" class="form-control form-control-sm" placeholder="Description..." value="' + (r.ket || '') + '"></td>';
      html += '  <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" data-del-kw2="' + r.id + '" data-type="' + type + '">✕</button></td>';
      html += '</tr>';
    });

    $(tableId + ' tbody').html(html);
    if (rows.length > 0) {
      $(tableId + ' tfoot').hide();
    } else {
      $(tableId + ' tfoot').show();
    }

    // Restore select values
    $.each(rows, function(i, r) {
      if (r.productId) {
        $(tableId + ' [data-kw2-product="' + r.id + '"]').val(r.productId);
      }
      if (r.coil) {
        $(tableId + ' [data-kw2-coil="' + r.id + '"]').val(r.coil);
      }
    });
    initSelect2Search(tableId);
  }

  // ── SECTION 5: SISA & HOLD COIL ──
  function addLine(kind) {
    var maxCoils = state.coils.length;
    var label = (kind === 'sisa') ? 'Remaining Coil' : 'Hold Coil';

    if (maxCoils === 0) {
      Swal.fire('Attention', 'Please add a Baby Coil in Material Source (Section 2) first.', 'warning');
      return;
    }

    if (state[kind].length >= maxCoils) {
      Swal.fire('Maximum Reached', 'Row count ' + label + ' (' + state[kind].length + ') cannot exceed the number of coil materials (' + maxCoils + ' rows).', 'warning');
      return;
    }

    state[kind].push({
      id: uid(),
      coil: '',
      berat: 0
    });
    renderLines(kind);
    recalc();
  }

  function renderLines(kind) {
    var tableId = '#' + kind + 'Table';
    var rows = state[kind];
    var maxCoils = state.coils.length;

    var html = '';
    $.each(rows, function(i, r) {
      html += '<tr data-id="' + r.id + '" data-kind="' + kind + '">';
      html += '  <td><select data-line-coil="' + r.id + '" data-kind="' + kind + '" class="form-select form-select-sm select2" style="width:100%">' + lineCoilOptions(r.id, kind) + '</select></td>';
      html += '  <td class="num"><input type="number" min="0" step="0.01" data-line-berat="' + r.id + '" data-kind="' + kind + '" class="form-control form-control-sm text-end" value="' + (r.berat || '') + '"></td>';
      html += '  <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" data-del-line="' + r.id + '" data-kind="' + kind + '">✕</button></td>';
      html += '</tr>';
    });

    $(tableId + ' tbody').html(html);
    if (rows.length > 0) {
      $(tableId + ' tfoot').hide();
    } else {
      $(tableId + ' tfoot').show();
    }

    // Update badge and disable button if max reached
    var badgeId = (kind === 'sisa') ? '#sisaCountBadge' : '#holdCountBadge';
    var btnId = (kind === 'sisa') ? '#addSisa' : '#addHold';
    $(badgeId).text(rows.length + ' / ' + maxCoils + ' Row');
    $(btnId).prop('disabled', (maxCoils === 0 || rows.length >= maxCoils));

    $.each(rows, function(i, r) {
      if (r.coil) {
        $(tableId + ' [data-line-coil="' + r.id + '"]').val(r.coil);
      }
    });
    initSelect2Search(tableId);
  }

  // ── SECTION 6: SCRAP ──
  function scrapTotal() {
    var ids = ['#scrapTong', '#scrapWrapping', '#scrapPisau', '#rejProdInt', '#rejMatInt', '#rejProdSup', '#rejMatSup'];
    var sum = 0;
    $.each(ids, function(i, sel) {
      sum += getNum($(sel).val());
    });
    return sum;
  }

  // ── SECTION 7: RECALCULATE & SUMMARY ──
  function recalc() {
    $.each(state.fg, function(i, f) {
      computeFg(f);
    });

    var fgKw1 = 0;
    var fgBebas = 0;
    $.each(state.fg, function(i, f) {
      if (f.kind === 'kw1') fgKw1 += f.total;
      else fgBebas += f.total;
    });

    var kw2Internal = 0;
    $.each(state.kw2.internal, function(i, r) {
      kw2Internal += getNum(r.total);
    });
    var kw2Supplier = 0;
    $.each(state.kw2.supplier, function(i, r) {
      kw2Supplier += getNum(r.total);
    });
    var kw2Total = kw2Internal + kw2Supplier;

    var scrap = scrapTotal();

    var sisaTotal = 0;
    $.each(state.sisa, function(i, r) {
      sisaTotal += getNum(r.berat);
    });

    var holdTotal = 0;
    $.each(state.hold, function(i, r) {
      holdTotal += getNum(r.berat);
    });

    var netProd = (fgKw1 + fgBebas) + kw2Total + scrap + sisaTotal + holdTotal;

    var netPack = 0;
    $.each(state.coils, function(i, c) {
      netPack += getNum(c.nett);
    });

    var selisihKg = netProd - netPack;
    var selisihPct = (netProd > 0) ? (selisihKg / netProd) * 100 : 0;

    $('#sumFG').text(formatNum(fgKw1 + fgBebas) + ' kg');
    $('#sumKW2').text(formatNum(kw2Total) + ' kg');
    $('#sumScrap').text(formatNum(scrap) + ' kg');
    $('#sumSisa').text(formatNum(sisaTotal) + ' kg');
    $('#sumHold').text(formatNum(holdTotal) + ' kg');
    $('#sumNetProd').text(formatNum(netProd) + ' kg');
    $('#sumNetPack').text(formatNum(netPack) + ' kg');
    $('#sumSelisihKg').text(formatNum(selisihKg) + ' kg');
    $('#sumSelisihPct').text(formatNum(selisihPct) + ' %');
    $('#scrapTotal').text(formatNum(scrap) + ' kg');

    var over = (Math.abs(selisihPct) > TOLERANCE && netProd > 0);
    if (over) {
      $('#sumSelisihBox').addClass('bad');
      $('#tolNote').text('⚠ Selisih ' + formatNum(selisihPct) + '% melebihi toleransi ± ' + TOLERANCE + '%. Audit confirmation required.');
    } else {
      $('#sumSelisihBox').removeClass('bad');
      $('#tolNote').text('Toleransi ± ' + TOLERANCE + '% terhadap Net Weight Produksi.');
    }

    // Gate: Hold Coil check (FR-18, FR-98) & Costing check (FR-48, FR-74)
    var hasHold = (state.hold.length > 0);
    var missingCosting = (COSTING_CHECK && !COSTING_CHECK.is_all_available);

    if (hasHold) {
      $('#holdNote').show();
      $('#gateMsg').html('<span class="text-warning"><i class="fa fa-exclamation-triangle me-1"></i> There is a Hold Coil — Save as <strong>Draft</strong>. Submit is waiting for a Purchasing decision.</span>');
    } else if (missingCosting) {
      $('#holdNote').hide();
      $('#gateMsg').html('<span class="text-warning"><i class="fa fa-exclamation-triangle me-1"></i> Costing Incomplete — Save as <strong>Draft</strong> active. Submit is locked until Update Costing.</span>');
    } else {
      $('#holdNote').hide();
      $('#gateMsg').text('Save Draft or Submit Report.');
    }

    renderClaims();

    return {
      selisihPct: selisihPct,
      over: over,
      netProd: netProd,
      netPack: netPack,
      selisihKg: selisihKg,
      fgTotal: fgKw1 + fgBebas,
      kw2Total: kw2Total,
      scrap: scrap,
      sisaTotal: sisaTotal,
      holdTotal: holdTotal
    };
  }

  // ── SECTION 7: CLAIM MATERIAL / AJUKAN CLAIM (FR-98, FR-94) ──
  var coilMetaCache = {};

  function getCoilClaimMeta(coilCode) {
    if (!coilCode) return null;
    if (coilMetaCache[coilCode]) return coilMetaCache[coilCode];

    var found = null;
    $.each(state.coils, function(i, c) {
      if (c && c.code === coilCode) {
        found = c;
        return false;
      }
    });

    coilMetaCache[coilCode] = {
      id_baby_coil: found ? found.id_warehouse_stock_coil : null,
      id_material: found ? found.id_material : '',
      nm_material: found ? found.material : '',
      no_coil_packing_list: coilCode,
      no_po: found && found.no_po ? found.no_po : '-',
      id_suplier: '-',
      nm_supplier: '-',
      harga_beli_per_kg: found && found.harga_beli ? found.harga_beli : 0,
      costbook_per_kg: 0,
      price_per_unit_po: 0,
      id_gudang_asal: null,
      kd_gudang_asal: null
    };

    $.getJSON(CLAIM_INFO_URL + '?coil_code=' + encodeURIComponent(coilCode), function(res) {
      if (res && res.status === 1 && res.data) {
        coilMetaCache[coilCode] = res.data;
        renderClaims();
      }
    });

    return coilMetaCache[coilCode];
  }

  function claimRows() {
    var rows = [];

    // 1. Dari Hold Coil (Card 5)
    $.each(state.hold, function(i, h) {
      var b = getNum(h.berat);
      var cCode = h.coil ? String(h.coil).trim() : '';
      if (cCode && b > 0) {
        var key = 'Hold Coil:' + cCode;
        var existing = state.claimIn[key] || {};
        rows.push({
          key: key,
          asal: 'Hold Coil',
          coilCode: cCode,
          fixed: true,
          berat_kg: b,
          defaultProblem: 'Hold Coil',
          defaultAlasan: 'Material held on the production line',
          problem: existing.problem !== undefined ? existing.problem : '',
          alasan: existing.alasan !== undefined ? existing.alasan : '',
          bukti: existing.bukti || [],
          status: existing.status || 'Dibuat saat Save'
        });
      }
    });

    // 2. Dari KW 2 (Supplier) (Card 4)
    var kw2Group = {};
    $.each(state.kw2.supplier, function(i, r) {
      var t = getNum(r.total);
      var cCode = r.coil ? String(r.coil).trim() : '';
      if (cCode && t > 0) {
        if (!kw2Group[cCode]) {
          kw2Group[cCode] = { berat: 0, kets: [] };
        }
        kw2Group[cCode].berat += t;
        if (r.ket && String(r.ket).trim()) {
          kw2Group[cCode].kets.push(String(r.ket).trim());
        }
      }
    });
    $.each(kw2Group, function(cCode, g) {
      var key = 'KW 2 (Supplier):' + cCode;
      var existing = state.claimIn[key] || {};
      var defAlasan = g.kets.length ? g.kets.join('; ') : 'KW 2 output defective from supplier';
      rows.push({
        key: key,
        asal: 'KW 2 (Supplier)',
        coilCode: cCode,
        fixed: true,
        berat_kg: g.berat,
        defaultProblem: 'KW 2',
        defaultAlasan: defAlasan,
        problem: existing.problem !== undefined ? existing.problem : 'KW 2',
        alasan: existing.alasan !== undefined ? existing.alasan : defAlasan,
        bukti: existing.bukti || [],
        status: existing.status || 'Dibuat saat Save'
      });
    });

    // 3. Dari Reject (Supplier) (Card 6)
    var rejProd = getNum($('#rejProdSup').val());
    var rejMat  = getNum($('#rejMatSup').val());
    var rejKet  = $('#rejKetSup').val() ? $('#rejKetSup').val().trim() : '';

    var activeCoils = $.grep(state.coils, function(c) { return c && c.code; });
    var autoCoil = activeCoils.length === 1 ? activeCoils[0].code : '';

    if (rejProd > 0) {
      var key = 'Reject (Supplier):Reject Product';
      var existing = state.claimIn[key] || {};
      rows.push({
        key: key,
        asal: 'Reject (Supplier)',
        coilCode: existing.coilCode || autoCoil,
        fixed: false,
        berat_kg: rejProd,
        defaultProblem: 'Reject Product',
        defaultAlasan: rejKet || 'Reject defective product from supplier',
        problem: existing.problem !== undefined ? existing.problem : 'Reject Product',
        alasan: existing.alasan !== undefined ? existing.alasan : (rejKet || 'Reject defective product from supplier'),
        bukti: existing.bukti || [],
        status: existing.status || 'Dibuat saat Save'
      });
    }

    if (rejMat > 0) {
      var key = 'Reject (Supplier):Reject Material';
      var existing = state.claimIn[key] || {};
      rows.push({
        key: key,
        asal: 'Reject (Supplier)',
        coilCode: existing.coilCode || autoCoil,
        fixed: false,
        berat_kg: rejMat,
        defaultProblem: 'Reject Material',
        defaultAlasan: rejKet || 'Reject defective plate material from supplier',
        problem: existing.problem !== undefined ? existing.problem : 'Reject Material',
        alasan: existing.alasan !== undefined ? existing.alasan : (rejKet || 'Reject defective plate material from supplier'),
        bukti: existing.bukti || [],
        status: existing.status || 'Dibuat saat Save'
      });
    }

    return rows;
  }

  function renderClaims() {
    var rows = claimRows();
    $('#claimCountBadge').text(rows.length + ' Row');

    if (rows.length === 0) {
      $('#claimTbody').html('<tr><td colspan="14" class="empty text-muted">There is no Hold Coil, KW 2 (Supplier), or Reject (Supplier) in this report.</td></tr>');
      return;
    }

    var html = '';
    var activeCoils = $.grep(state.coils, function(c) { return c && c.code; });

    $.each(rows, function(idx, r) {
      var meta = getCoilClaimMeta(r.coilCode) || {};
      var lock = (r.status && r.status !== 'Claim Baru' && r.status !== 'Baru' && r.status !== 'Dibuat saat Save');

      html += '<tr data-claim-key="' + r.key + '">';
      html += '  <td><span class="badge ' + (r.asal === 'Hold Coil' ? 'bg-danger' : (r.asal === 'KW 2 (Supplier)' ? 'bg-warning text-dark' : 'bg-dark')) + '">' + r.asal + '</span></td>';

      // Baby coil selector jika tidak fixed (Reject Supplier)
      if (r.fixed) {
        html += '  <td><strong>' + (r.coilCode || '—') + '</strong></td>';
      } else {
        html += '  <td>';
        html += '    <select class="form-select form-select-sm cl-coil-select" data-key="' + r.key + '" ' + (lock ? 'disabled' : '') + '>';
        html += '      <option value="">-- Pilih Coil --</option>';
        $.each(activeCoils, function(ci, ac) {
          html += '    <option value="' + ac.code + '" ' + (ac.code === r.coilCode ? 'selected' : '') + '>' + ac.code + '</option>';
        });
        html += '    </select>';
        html += '  </td>';
      }

      html += '  <td>' + (meta.nm_material || '—') + '</td>';
      html += '  <td>' + (meta.nm_supplier || '—') + '</td>';
      html += '  <td>' + (meta.no_po || '—') + '</td>';
      html += '  <td>' + (meta.no_coil_packing_list || r.coilCode || '—') + '</td>';
      html += '  <td class="num font-monospace">' + formatNum(r.berat_kg) + '</td>';
      html += '  <td class="num font-monospace">' + formatNum(meta.harga_beli_per_kg || 0) + '</td>';
      html += '  <td class="num font-monospace">' + formatNum(meta.costbook_per_kg || 0) + '</td>';
      html += '  <td class="num font-monospace">' + formatNum(meta.price_per_unit_po || 0) + '</td>';

      html += '  <td><input type="text" class="form-control form-control-sm cl-in-problem" data-key="' + r.key + '" value="' + (r.problem ? escapeHtml(r.problem) : '') + '" placeholder="mis. Cacat / Karat" ' + (lock ? 'disabled' : '') + '></td>';
      html += '  <td><textarea class="form-control form-control-sm cl-in-alasan" rows="2" data-key="' + r.key + '" placeholder="Alasan dari produksi..." ' + (lock ? 'disabled' : '') + '>' + (r.alasan ? escapeHtml(r.alasan) : '') + '</textarea></td>';

      // Bukti upload & file chip list
      html += '  <td>';
      html += '    <div class="cl-file-list mb-1">';
      if (r.bukti && r.bukti.length > 0) {
        $.each(r.bukti, function(bi, b) {
          var fname = b.file_original_name || b.name || b.file_hash_name || 'File';
          var fsizeKb = b.file_size ? Math.round(b.file_size / 1024) + ' KB' : '';
          var furl = b.url ? b.url : (BASE_MODULE_URL + '/../../uploads/claim_material/' + (b.file_hash_name || ''));
          html += '<span class="cl-file-chip">';
          html += '  <a href="' + furl + '" target="_blank" class="text-decoration-none text-dark">📎 ' + escapeHtml(fname) + (fsizeKb ? ' <small class="text-muted">(' + fsizeKb + ')</small>' : '') + '</a>';
          if (!lock) {
            html += '  <span class="cl-del-file" data-key="' + r.key + '" data-file-idx="' + bi + '" title="Hapus berkas">&times;</span>';
          }
          html += '</span>';
        });
      } else {
        html += '      <span class="text-danger small fst-italic">Evidence must be attached</span>';
      }
      html += '    </div>';
      if (!lock) {
        html += '    <label class="btn btn-xs btn-outline-primary py-0 px-2 mt-1">';
        html += '      <i class="fa fa-upload me-1"></i> Upload Bukti';
        html += '      <input type="file" class="cl-file-input" data-key="' + r.key + '" multiple style="display:none;" accept="image/*,application/pdf,video/*">';
        html += '    </label>';
      }
      html += '  </td>';

      html += '  <td><span class="badge ' + (lock ? 'bg-danger' : 'bg-secondary') + '">' + (r.status || 'Draft') + '</span></td>';
      html += '</tr>';
    });

    $('#claimTbody').html(html);
  }

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Event handler input field claim (problem, alasan, coil)
  $(document).on('input', '.cl-in-problem', function() {
    var key = $(this).attr('data-key');
    if (!state.claimIn[key]) state.claimIn[key] = {};
    state.claimIn[key].problem = $(this).val();
  });

  $(document).on('input', '.cl-in-alasan', function() {
    var key = $(this).attr('data-key');
    if (!state.claimIn[key]) state.claimIn[key] = {};
    state.claimIn[key].alasan = $(this).val();
  });

  $(document).on('change', '.cl-coil-select', function() {
    var key = $(this).attr('data-key');
    var val = $(this).val();
    if (!state.claimIn[key]) state.claimIn[key] = {};
    state.claimIn[key].coilCode = val;
    renderClaims();
  });

  // Upload bukti file handler (maks 5MB per berkas sesuai instruksi user)
  $(document).on('change', '.cl-file-input', function() {
    var key = $(this).attr('data-key');
    var files = this.files;
    if (!files || files.length === 0) return;

    var maxBytes = 5 * 1024 * 1024; // 5 MB
    var inputElem = this;

    $.each(files, function(i, file) {
      if (file.size > maxBytes) {
        Swal.fire('File Too Large', 'File "' + file.name + '" exceeds the 5 MB limit and was not uploaded.', 'warning');
        return;
      }

      var fd = new FormData();
      fd.append('file', file);

      $.ajax({
        url: UPLOAD_CLAIM_URL,
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
          if (res.status === 1) {
            if (!state.claimIn[key]) state.claimIn[key] = {};
            if (!state.claimIn[key].bukti) state.claimIn[key].bukti = [];
            state.claimIn[key].bukti.push({
              file_original_name: res.file_original_name,
              file_hash_name: res.file_hash_name,
              file_size: res.file_size,
              file_type: res.file_type,
              url: res.url
            });
            renderClaims();
          } else {
            Swal.fire('Upload Failed', res.message || 'Failed to upload file', 'error');
          }
        },
        error: function(xhr, status, err) {
          Swal.fire('Error', 'An error occurred while uploading the file: ' + err, 'error');
        }
      });
    });

    $(inputElem).val('');
  });

  // Hapus bukti file
  $(document).on('click', '.cl-del-file', function(e) {
    e.preventDefault();
    var key = $(this).attr('data-key');
    var idx = parseInt($(this).attr('data-file-idx'), 10);
    if (state.claimIn[key] && state.claimIn[key].bukti) {
      state.claimIn[key].bukti.splice(idx, 1);
      renderClaims();
    }
  });

  // ── SECTION 8: AUDIT KONFIRMASI SELISIH ──
  var modalResolve = null;

  function openTolModal(msg, ctx) {
    $('#tolModalBody').text(msg);
    $('#tolSigner').text(CURRENT_USER.name + ' (' + CURRENT_USER.role + ')');
    $('#tolModal').data('ctx', ctx ? JSON.stringify(ctx) : '');
    $('#tolModal').modal('show');

    return new Promise(function(resolve) {
      modalResolve = resolve;
    });
  }

  function closeTolModal(ack) {
    var raw = $('#tolModal').data('ctx');
    $('#tolModal').modal('hide');

    if (ack && raw) {
      try {
        recordConfirmation(JSON.parse(raw));
      } catch (e) {
        console.error(e);
      }
    }
    if (modalResolve) {
      modalResolve(ack);
      modalResolve = null;
    }
  }

  function recordConfirmation(ctx) {
    var now = new Date();
    var entry = {
      id: uid(),
      scope: ctx.scope,
      ref: ctx.ref,
      label: ctx.label,
      selisih: ctx.selisih,
      statement: ctx.statement || 'Matches actual',
      user: CURRENT_USER.name,
      role: CURRENT_USER.role,
      ts: now.toISOString(),
      tsLabel: now.toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short'
      })
    };

    state.confirmations = $.grep(state.confirmations, function(c) {
      return c.ref !== ctx.ref;
    });
    state.confirmations.push(entry);

    if (ctx.scope === 'fg') {
      $.each(state.fg, function(i, f) {
        if (f.id === ctx.ref) {
          f.confirmedAt = f.selisih;
          return false;
        }
      });
      $('[data-fg-confirm="' + ctx.ref + '"]').show();
    }

    renderAudit();
  }

  function renderAudit() {
    if (!state.confirmations.length) {
      $('#auditCard').hide();
      $('#auditTable tbody').empty();
      return;
    }

    $('#auditCard').show();
    var html = '';
    $.each(state.confirmations, function(i, c) {
      html += '<tr>';
      html += '  <td>' + (i + 1) + '</td>';
      html += '  <td>' + c.label + '</td>';
      html += '  <td class="num text-danger fw-bold">' + formatNum(c.selisih) + ' %</td>';
      html += '  <td>' + c.statement + '</td>';
      html += '  <td><strong>' + c.user + '</strong> <span class="badge bg-light text-dark">' + c.role + '</span></td>';
      html += '  <td>' + c.tsLabel + '</td>';
      html += '</tr>';
    });
    $('#auditTable tbody').html(html);
  }

  // ── SAVE & SUBMIT REPORT ──
  async function submitReport(isDraft) {
    isDraft = isDraft || 0;

    var calcRes = recalc();

    // ── SUBMIT LOCK JIKA DATA PRODUCT COSTING BELUM LENGKAP (FR-48, FR-74) ──
    if (!isDraft && COSTING_CHECK && !COSTING_CHECK.is_all_available) {
      var missingList = (COSTING_CHECK.missing_products || []).map(function(p) {
        return '<li><strong>' + (p.nm_produk || 'Produk') + '</strong> (Varian: ' + (p.no_uniq_id || '-') + ')</li>';
      }).join('');

      Swal.fire({
        icon: 'warning',
        title: 'Report Submit Locked',
        html: '<div class="text-start">' +
              '<p>The Production Report cannot be submitted to Accounting because the <strong>Product Costing</strong> data for the following products has not been updated:</p>' +
              '<ul>' + missingList + '</ul>' +
              '<p class="mb-0 text-muted small"><i class="fa fa-info-circle me-1"></i> You can save the report as <strong>Draft</strong> first by pressing the <strong>Save Draft</strong>, button, or click the button below to open the Costing Simulation module.</p>' +
              '</div>',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fa fa-external-link me-1"></i> Open Costing Simulation',
        cancelButtonText: 'Close'
      }).then(function(res) {
        if (res.isConfirmed) {
          window.open(SIMULASI_COSTING_URL, '_blank');
        }
      });
      return;
    }

    // ── SUBMIT LOCK JIKA ADA HOLD COIL (FR-18, FR-98) ──
    if (!isDraft && state.hold.length > 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Report Submit Locked',
        html: 'The report contains <strong>Hold Coil</strong> material awaiting a Purchasing decision (FR-18, FR-98).<br><br>Please save this report as a <strong>Draft</strong> by pressing the <strong>Save Draft</strong>.',
        confirmButtonText: 'Close'
      });
      return;
    }

    // ── VALIDASI & PENYUSUNAN CLAIM MATERIAL (FR-94, FR-98) ──
    var claimList = claimRows();
    var claimErr = [];
    var payloadClaims = [];

    if (claimList.length > 0) {
      $.each(claimList, function(i, r) {
        var meta = getCoilClaimMeta(r.coilCode) || {};
        var n = 'Row ' + (i + 1) + ' (' + r.asal + ')';
        if (!r.coilCode) {
          claimErr.push(n + ': Source baby coil has not been selected.');
        }
        if (!r.problem || !String(r.problem).trim()) {
          claimErr.push(n + ': Problem is required.');
        }
        if (!r.alasan || !String(r.alasan).trim()) {
          claimErr.push(n + ': Production Reason is required.');
        }
        if (!r.bukti || r.bukti.length === 0) {
          claimErr.push(n + ': At least 1 supporting document must be uploaded.');
        }

        payloadClaims.push({
          asal: r.asal,
          id_baby_coil: meta.id_baby_coil || null,
          id_material: meta.id_material || '',
          id_suplier: meta.id_suplier || '-',
          no_po: meta.no_po || '-',
          no_coil_packing_list: meta.no_coil_packing_list || r.coilCode,
          berat_kg: r.berat_kg,
          harga_beli_per_kg: meta.harga_beli_per_kg || 0,
          costbook_per_kg: meta.costbook_per_kg || 0,
          price_per_unit_po: meta.price_per_unit_po || 0,
          id_gudang_asal: meta.id_gudang_asal || null,
          kd_gudang_asal: meta.kd_gudang_asal || null,
          problem: r.problem,
          alasan_produksi: r.alasan,
          bukti: r.bukti
        });
      });

      if (claimErr.length > 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Material Claim Entry Incomplete',
          html: '<div class="text-start small"><ul><li>' + claimErr.join('</li><li>') + '</li></ul></div>',
          confirmButtonText: 'Lengkapi Data'
        });
        $('html, body').animate({ scrollTop: $('#cardClaimMaterial').offset().top - 80 }, 300);
        return;
      }
    }

    // ── VALIDASI MANDATORY FORM (Jika Bukan Simpan Draft) ──
    if (!isDraft) {
      // 1. Validasi Card Informasi Laporan (Semua Mandatory)
      var tglProd = $('#tglProduksi').val();
      var idMachine = $('#id_asset_machine').val();
      var empHelper = $('#employee_helper').val();
      var empSetter = $('#employee_setter').val();
      var startTime = $('#startTime').val();
      var finishedTime = $('#finishedTime').val();

      if (!tglProd) {
        Swal.fire('Validation Failed', 'Production Date is required.', 'warning');
        $('#tglProduksi').focus();
        return;
      }
      if (!idMachine) {
        Swal.fire('Validation Failed', 'A production machine is required.', 'warning');
        $('#id_asset_machine').select2('open');
        return;
      }
      if (!empHelper || (Array.isArray(empHelper) && empHelper.length === 0)) {
        Swal.fire('Validation Failed', 'At least 1 Helper Name must be selected.', 'warning');
        $('#employee_helper').select2('open');
        return;
      }
      if (!empSetter) {
        Swal.fire('Validation Failed', 'Setter Name is required.', 'warning');
        $('#employee_setter').select2('open');
        return;
      }
      if (!startTime) {
        Swal.fire('Validation Failed', 'Start Time is required.', 'warning');
        $('#startTime').focus();
        return;
      }
      if (!finishedTime) {
        Swal.fire('Validation Failed', 'Finished Time is required.', 'warning');
        $('#finishedTime').focus();
        return;
      }

      // 2. Validasi Add Coil (Sumber Material) - Semua Mandatory
      if (state.coils.length === 0) {
        Swal.fire('Validation Failed', 'Material Source (Add Coil) requires at least 1 row.', 'warning');
        return;
      }
      for (var cIdx = 0; cIdx < state.coils.length; cIdx++) {
        var c = state.coils[cIdx];
        if (!c.code) {
          Swal.fire('Validation Failed', 'Row ' + (cIdx + 1) + ' in Material Source has no Baby Coil selected.', 'warning');
          return;
        }
      }

      // 3. Validasi Finish Good KW 1 (Mandatory, Stok Bebas Optional)
      var kw1Rows = $.grep(state.fg, function(f) {
        return f.kind === 'kw1';
      });
      if (kw1Rows.length === 0) {
        Swal.fire('Validation Failed', 'KW 1 Product (from SPK) requires at least 1 row.', 'warning');
        return;
      }
      for (var kIdx = 0; kIdx < kw1Rows.length; kIdx++) {
        var kw1 = kw1Rows[kIdx];
        if (!kw1.productId) {
          Swal.fire('Validation Failed', 'Row' + (kIdx + 1) + ' Produk KW 1 belum memilih Produk.', 'warning');
          return;
        }
        if (!kw1.coil) {
          Swal.fire('Validation Failed', 'Row' + (kIdx + 1) + ' Produk KW 1 belum memilih Baby Coil Terpakai.', 'warning');
          return;
        }
        if (getNum(kw1.qty) <= 0) {
          Swal.fire('Validation Failed', 'Qty on KW 1 Product row ' + (kIdx + 1) + ' must be greater than 0.', 'warning');
          return;
        }
        if (getNum(kw1.total) <= 0) {
          Swal.fire('Validation Failed', 'Total Weight on KW 1 Product row ' + (kIdx + 1) + ' must be greater than 0.', 'warning');
          return;
        }
      }
    }

    // Audit checking jika bukan draft
    if (!isDraft) {
      var auditQueue = [];
      $.each(state.fg, function(i, f) {
        if (f.std > 0 && Math.abs(f.selisih) > TOLERANCE) {
          var already = false;
          $.each(state.confirmations, function(j, c) {
            if (c.ref === f.id && c.selisih === f.selisih) {
              already = true;
              return false;
            }
          });
          if (!already) {
            auditQueue.push({
              scope: 'fg',
              ref: f.id,
              label: f.productName + ' (' + (f.kind === 'bebas' ? 'Free Stock' : 'KW 1') + ')',
              selisih: f.selisih,
              msg: 'Produk "' + f.productName + '" memiliki selisih aktual ' + formatNum(f.selisih) + '% against the standard weight (' + formatNum(f.std) + ' kg/pcs). Toleransi maksimum ± ' + TOLERANCE + '%.'
            });
          }
        }
      });

      if (calcRes.over) {
        var alreadySummary = false;
        $.each(state.confirmations, function(j, c) {
          if (c.ref === 'summary' && Math.abs(c.selisih - calcRes.selisihPct) < 0.01) {
            alreadySummary = true;
            return false;
          }
        });
        if (!alreadySummary) {
          auditQueue.push({
            scope: 'summary',
            ref: 'summary',
            label: 'Total Net Produksi vs Packing List',
            selisih: calcRes.selisihPct,
            msg: 'Total Net Weight Produksi (' + formatNum(calcRes.netProd) + ' kg) memiliki selisih ' + formatNum(calcRes.selisihPct) + '% terhadap Net Packing List (' + formatNum(calcRes.netPack) + ' kg). Melebihi toleransi ± ' + TOLERANCE + '%.'
          });
        }
      }

      for (var k = 0; k < auditQueue.length; k++) {
        var item = auditQueue[k];
        var ack = await openTolModal(item.msg, item);
        if (!ack) {
          Swal.fire('Attention', 'Save cancelled. Please adjust the actual data.', 'info');
          return;
        }
      }
    }

    // Build Payload
    var payload = {
      report_id: $('#reportId').val(),
      spk_no: $('input[name="spk_no"]').val(),
      id_tr_spk_detail: $('input[name="id_tr_spk_detail"]').val(),
      tgl_produksi: $('#tglProduksi').val(),
      id_asset_machine: $('#id_asset_machine').val(),
      employee_helper: $('#employee_helper').val(),
      employee_setter: $('#employee_setter').val(),
      start_time: $('#startTime').val(),
      finished_time: $('#finishedTime').val(),
      summary_finish_good: calcRes.fgTotal,
      summary_kw_2: calcRes.kw2Total,
      summary_scrap: calcRes.scrap,
      summary_sisa_coil: calcRes.sisaTotal,
      summary_hold_coil: calcRes.holdTotal,
      summary_net_produksi: calcRes.netProd,
      summary_net_packing_list: calcRes.netPack,
      selisih_kg: calcRes.selisihKg,
      selisih_persen: calcRes.selisihPct,
      status_draft: isDraft,
      confirmations: state.confirmations,
      materials: $.map(state.coils, function(c) {
        return {
          source_warehouse: c.source,
          id_warehouse_stock_coil: c.id_warehouse_stock_coil,
          no_coil: c.code,
          material_name: c.material,
          net_weight_packing_list: c.nett,
          gross_weight_packing_list: c.gross,
          total_meter: c.meter,
          berat_kulit: c.kulit,
          berat_clamp: c.clamp
        };
      }),
      items: [],
      scraps: [],
      claims: payloadClaims
    };

    // Helper untuk mencari Varian BOM SPK (FR-48, FR-74)
    function getSpkVariantFor(productId) {
      if (SPK_PRODUCTS && SPK_PRODUCTS.length > 0) {
        var sp = SPK_PRODUCTS.find(function(x) {
          return x.id_product_lvl_4 == productId || x.product_lvl_4_id == productId || x.id_produk_fg == productId;
        });
        if (sp && sp.no_uniq_id) return sp.no_uniq_id;
        if (SPK_PRODUCTS[0] && SPK_PRODUCTS[0].no_uniq_id) return SPK_PRODUCTS[0].no_uniq_id;
      }
      return 'Type 001.1';
    }

    // Items: FG KW1 & Bebas
    $.each(state.fg, function(i, f) {
      payload.items.push({
        kategori: (f.kind === 'bebas') ? 'stok_bebas' : 'kw_1',
        id_product_lvl_4: f.productId,
        no_uniq_id: f.no_uniq_id || getSpkVariantFor(f.productId),
        nama_produk: f.productName,
        kode_baby_coil: f.coil,
        metode_input: f.method,
        qty: f.qty,
        berat_total: f.total,
        berat_per_pcs: f.perPcs,
        berat_standard_pcs: f.std,
        selisih_persen: f.selisih,
        size_meter: 0,
        panjang_meter: f.panjang || 0,
        berat_per_meter_aktual: f.beratPerMeterAktual || 0,
        berat_per_meter_standard: f.stdPerMeter || 0,
        percentage_selisih_meter: f.selisihMeter || 0,
        keterangan: null
      });
    });

    // Items: KW 2 Internal
    $.each(state.kw2.internal, function(i, r) {
      var p = productById(r.productId);
      var numStr = (i + 1 < 10) ? '0' + (i + 1) : (i + 1);
      payload.items.push({
        kategori: 'kw_2_internal',
        id_product_lvl_4: r.productId,
        no_uniq_id: r.no_uniq_id || getSpkVariantFor(r.productId),
        nama_produk: p ? (p.nama + '-KW2-I-' + numStr) : 'KW2-I',
        kode_baby_coil: r.coil || null,
        metode_input: 1,
        qty: r.qty,
        berat_total: r.total,
        berat_per_pcs: (r.qty > 0) ? r.total / r.qty : 0,
        berat_standard_pcs: 0,
        selisih_persen: 0,
        size_meter: r.size,
        keterangan: r.ket
      });
    });

    // Items: KW 2 Supplier
    $.each(state.kw2.supplier, function(i, r) {
      var p = productById(r.productId);
      var numStr = (i + 1 < 10) ? '0' + (i + 1) : (i + 1);
      payload.items.push({
        kategori: 'kw_2_supplier',
        id_product_lvl_4: r.productId,
        no_uniq_id: r.no_uniq_id || getSpkVariantFor(r.productId),
        nama_produk: p ? (p.nama + '-KW2-S-' + numStr) : 'KW2-S',
        kode_baby_coil: r.coil || null,
        metode_input: 1,
        qty: r.qty,
        berat_total: r.total,
        berat_per_pcs: (r.qty > 0) ? r.total / r.qty : 0,
        berat_standard_pcs: 0,
        selisih_persen: 0,
        size_meter: r.size,
        keterangan: r.ket
      });
    });

    // Scraps
    payload.scraps.push({
      jenis_scrap: 'tong_coil',
      berat: getNum($('#scrapTong').val()),
      keterangan: null
    });
    payload.scraps.push({
      jenis_scrap: 'wrapping',
      berat: getNum($('#scrapWrapping').val()),
      keterangan: null
    });
    payload.scraps.push({
      jenis_scrap: 'potongan_pisau',
      berat: getNum($('#scrapPisau').val()),
      keterangan: null
    });
    payload.scraps.push({
      jenis_scrap: 'reject_product_internal',
      berat: getNum($('#rejProdInt').val()),
      keterangan: $('#rejKetInt').val()
    });
    payload.scraps.push({
      jenis_scrap: 'reject_material_internal',
      berat: getNum($('#rejMatInt').val()),
      keterangan: $('#rejKetInt').val()
    });
    payload.scraps.push({
      jenis_scrap: 'reject_product_supplier',
      berat: getNum($('#rejProdSup').val()),
      keterangan: $('#rejKetSup').val()
    });
    payload.scraps.push({
      jenis_scrap: 'reject_material_supplier',
      berat: getNum($('#rejMatSup').val()),
      keterangan: $('#rejKetSup').val()
    });
    $.each(state.sisa, function(i, s) {
      payload.scraps.push({
        jenis_scrap: 'sisa_coil',
        kode_baby_coil: s.coil,
        berat: s.berat,
        keterangan: 'Into WIP Warehouse'
      });
    });
    $.each(state.hold, function(i, h) {
      payload.scraps.push({
        jenis_scrap: 'hold_coil',
        kode_baby_coil: h.coil,
        berat: h.berat,
        keterangan: 'Into Hold Warehouse'
      });
    });

    // Confirmation dialog
    Swal.fire({
      title: isDraft ? 'Save Draft?' : 'Save Production Report?',
      text: isDraft ? 'The data will be saved as a draft.' : 'The production data will be saved permanently.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, Save',
      cancelButtonText: 'Cancel'
    }).then(function(res) {
      if (!res.isConfirmed) return;

      $.ajax({
        url: SAVE_URL,
        type: 'POST',
        data: JSON.stringify(payload),
        contentType: 'application/json',
        dataType: 'json',
        beforeSend: function() {
          Swal.fire({
            title: 'Menyimpan...',
            allowOutsideClick: false,
            didOpen: function() {
              Swal.showLoading();
            }
          });
        },
        success: function(response) {
          Swal.close();
          if (response.status == 1) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: isDraft ? 'Production report draft saved successfully!' : 'Production report saved successfully!',
              timer: 1500,
              showConfirmButton: false
            }).then(function() {
              window.location.href = BASE_MODULE_URL;
            });
          } else if (response.status == 2) {
            // Laporan tersimpan, tetapi jurnal GL Interface gagal dibuat
            Swal.fire({
              icon: 'warning',
              title: 'Saved with Notes',
              text: response.message,
              confirmButtonText: 'OK'
            }).then(function() {
              window.location.href = BASE_MODULE_URL;
            });
          } else if (response.costing_missing) {
            Swal.fire({
              icon: 'warning',
              title: 'Product Costing Incomplete',
              html: response.message,
              showCancelButton: true,
              confirmButtonColor: '#f59e0b',
              confirmButtonText: '<i class="fa fa-external-link me-1"></i> Open Costing Simulation',
              cancelButtonText: 'Close'
            }).then(function(res) {
              if (res.isConfirmed) {
                window.open(SIMULASI_COSTING_URL, '_blank');
              }
            });
          } else {
            Swal.fire('Failed', response.message || 'A system error occurred.', 'error');
          }
        },
        error: function() {
          Swal.close();
          Swal.fire('Error', 'A network or server error occurred while saving.', 'error');
        }
      });
    });
  }

  // ── DOCUMENT READY ──
  $(document).ready(function() {
    // Init Flatpickr
    if (typeof flatpickr !== 'undefined') {
      flatpickr('#tglProduksi', {
        dateFormat: 'Y-m-d',
        allowInput: true,
        defaultDate: $('#tglProduksi').val() || new Date()
      });

      flatpickr('#startTime, #finishedTime', {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: true,
        allowInput: true,
        minuteIncrement: 5
      });
    }

    // Init Select2 on static elements
    initSelect2Search(document);

    // Event: Add Coil
    $(document).on('click', '[data-add-coil]', function(e) {
      e.preventDefault();
      var source = $(this).attr('data-add-coil');
      if (source) addCoil(source);
    });

    // Event: Delete Coil
    $(document).on('click', '[data-del-coil]', function(e) {
      e.preventDefault();
      var delId = $(this).attr('data-del-coil');
      state.coils = $.grep(state.coils, function(c) {
        return c.id !== delId;
      });
      renderCoils();
      refreshCoilSelectors();
      recalc();
    });

    // Event: Add Produk KW 1
    $(document).on('click', '[data-add-fg="kw1"]', function(e) {
      e.preventDefault();
      addKw1();
    });

    // Event: Add Stok Bebas
    $(document).on('click', '[data-add-fg="bebas"]', function(e) {
      e.preventDefault();
      addBebas();
    });

    // Event: Delete Stok Bebas
    $(document).on('click', '[data-del-fg]', function(e) {
      e.preventDefault();
      var delId = $(this).attr('data-del-fg');
      state.fg = $.grep(state.fg, function(f) {
        return f.id !== delId;
      });
      renderFg();
      recalc();
    });

    // Event: Add KW 2
    $(document).on('click', '[data-add-kw2]', function(e) {
      e.preventDefault();
      var type = $(this).attr('data-add-kw2');
      if (type) addKw2(type);
    });

    // Event: Delete KW 2
    $(document).on('click', '[data-del-kw2]', function(e) {
      e.preventDefault();
      var ty = $(this).attr('data-type');
      var delId = $(this).attr('data-del-kw2');
      if (ty && state.kw2[ty]) {
        state.kw2[ty] = $.grep(state.kw2[ty], function(r) {
          return r.id !== delId;
        });
        renderKw2(ty);
        recalc();
      }
    });

    // Event: Add Sisa / Hold
    $(document).on('click', '#addSisa', function(e) {
      e.preventDefault();
      addLine('sisa');
    });
    $(document).on('click', '#addHold', function(e) {
      e.preventDefault();
      addLine('hold');
    });

    // Event: Delete Sisa / Hold
    $(document).on('click', '[data-del-line]', function(e) {
      e.preventDefault();
      var k = $(this).attr('data-kind');
      var delId = $(this).attr('data-del-line');
      if (k && state[k]) {
        state[k] = $.grep(state[k], function(r) {
          return r.id !== delId;
        });
        renderLines(k);
        recalc();
      }
    });

    // Event: Inputs handling (Delegated)
    $(document).on('change', '[data-coil-pick]', function() {
      var id = $(this).attr('data-coil-pick');
      var val = $(this).val();
      pickCoil(id, val);
    });

    $(document).on('change', '[data-fg-product]', function() {
      var id = $(this).attr('data-fg-product');
      var val = $(this).val();
      $.each(state.fg, function(i, f) {
        if (f.id === id) {
          f.productId = val;
          var p = productById(val);
          f.productName = p ? p.nama : '';
          return false;
        }
      });
      recalc();
      updateFgDerivedView();
    });

    $(document).on('change', '[data-fg-coil]', function() {
      var id = $(this).attr('data-fg-coil');
      var currentKind = $(this).attr('data-kind');
      var val = $(this).val();

      // Cari baris saat ini jika currentKind belum terbaca dari atribut
      if (!currentKind) {
        $.each(state.fg, function(i, f) {
          if (f.id === id) {
            currentKind = f.kind;
            return false;
          }
        });
      }

      // Validasi: hanya cek apakah sudah dipilih di sesama kategori (KW 1 vs KW 1, Stok Bebas vs Stok Bebas)
      if (val) {
        var already = false;
        $.each(state.fg, function(i, f) {
          if (f.id !== id && f.kind === currentKind && f.coil === val) {
            already = true;
            return false;
          }
        });
        if (already) {
          var labelKind = (currentKind === 'bebas') ? 'Free Stock' : 'KW 1 Product';
          Swal.fire('Material Already Selected', 'Baby Coil "' + val + '" has already been selected in another ' + labelKind + ' row.', 'warning');
          $(this).val('').trigger('change.select2');
          return;
        }
      }

      $.each(state.fg, function(i, f) {
        if (f.id === id) {
          f.coil = val;
          return false;
        }
      });

      // Re-render FG rows to update available coil options across other rows
      renderFg();
    });

    $(document).on('change', '[data-fg-method]', function() {
      var id = $(this).attr('data-fg-method');
      var val = parseInt($(this).val(), 10);
      $.each(state.fg, function(i, f) {
        if (f.id === id) {
          f.method = val;
          return false;
        }
      });
      renderFg();
      recalc();
    });

    $(document).on('input change', '[data-fg-qty]', function() {
      var id = $(this).attr('data-fg-qty');
      var val = getNum($(this).val());
      $.each(state.fg, function(i, f) {
        if (f.id === id) {
          f.qty = val;
          return false;
        }
      });
      recalc();
      updateFgDerivedView();
    });

    $(document).on('input change', '[data-fg-input]', function() {
      var id = $(this).attr('data-fg-input');
      var val = getNum($(this).val());
      $.each(state.fg, function(i, f) {
        if (f.id === id) {
          if (f.method === 1) f.total = val;
          else f.perPcs = val;
          return false;
        }
      });
      recalc();
      updateFgDerivedView();
    });

    // KW2 inputs
    $(document).on('change', '[data-kw2-product]', function() {
      var id = $(this).attr('data-kw2-product');
      var val = $(this).val();
      $.each(['internal', 'supplier'], function(idx, ty) {
        $.each(state.kw2[ty], function(i, r) {
          if (r.id === id) {
            r.productId = val;
            renderKw2(ty);
            return false;
          }
        });
      });
    });

    $(document).on('change', '[data-kw2-coil]', function() {
      var id = $(this).attr('data-kw2-coil');
      var ty = $(this).attr('data-type');
      var val = $(this).val();
      if (ty && state.kw2[ty]) {
        $.each(state.kw2[ty], function(i, r) {
          if (r.id === id) {
            r.coil = val;
            return false;
          }
        });
      }
    });

    function updateKw2RowCalc($tr, rowObj) {
      var perPcs = (rowObj.qty > 0) ? (rowObj.total / rowObj.qty) : 0;
      $tr.find('.kw2-perpcs-col').text(formatNum(perPcs));
    }

    $(document).on('input change', '[data-kw2-size]', function() {
      var id = $(this).attr('data-kw2-size');
      var val = getNum($(this).val());
      var $tr = $(this).closest('tr');
      $.each(['internal', 'supplier'], function(idx, ty) {
        $.each(state.kw2[ty], function(i, r) {
          if (r.id === id) {
            r.size = val;
            updateKw2RowCalc($tr, r);
            return false;
          }
        });
      });
    });

    $(document).on('input change', '[data-kw2-qty]', function() {
      var id = $(this).attr('data-kw2-qty');
      var val = getNum($(this).val());
      var $tr = $(this).closest('tr');
      $.each(['internal', 'supplier'], function(idx, ty) {
        $.each(state.kw2[ty], function(i, r) {
          if (r.id === id) {
            r.qty = val;
            updateKw2RowCalc($tr, r);
            return false;
          }
        });
      });
      recalc();
    });

    $(document).on('input change', '[data-kw2-total]', function() {
      var id = $(this).attr('data-kw2-total');
      var val = getNum($(this).val());
      var $tr = $(this).closest('tr');
      $.each(['internal', 'supplier'], function(idx, ty) {
        $.each(state.kw2[ty], function(i, r) {
          if (r.id === id) {
            r.total = val;
            updateKw2RowCalc($tr, r);
            return false;
          }
        });
      });
      recalc();
    });

    $(document).on('input change', '[data-kw2-ket]', function() {
      var id = $(this).attr('data-kw2-ket');
      var val = $(this).val();
      $.each(['internal', 'supplier'], function(idx, ty) {
        $.each(state.kw2[ty], function(i, r) {
          if (r.id === id) {
            r.ket = val;
            return false;
          }
        });
      });
    });

    // Sisa / Hold inputs
    $(document).on('change', '[data-line-coil]', function() {
      var id = $(this).attr('data-line-coil');
      var kind = $(this).attr('data-kind');
      var val = $(this).val();

      if (val) {
        var already = false;
        $.each(state[kind], function(i, r) {
          if (r.id !== id && r.coil === val) {
            already = true;
            return false;
          }
        });
        if (already) {
          Swal.fire('Material Already Selected', 'Baby Coil "' + val + '" has already been selected in another ' + (kind === 'sisa' ? 'Remaining Coil' : 'Hold Coil') + ' row.', 'warning');
          $(this).val('').trigger('change.select2');
          return;
        }
      }

      $.each(state[kind], function(i, r) {
        if (r.id === id) {
          r.coil = val;
          return false;
        }
      });

      renderLines(kind);
    });

    $(document).on('input change', '[data-line-berat]', function() {
      var id = $(this).attr('data-line-berat');
      var kind = $(this).attr('data-kind');
      var val = getNum($(this).val());
      $.each(state[kind], function(i, r) {
        if (r.id === id) {
          r.berat = val;
          return false;
        }
      });
      recalc();
    });

    // Scrap inputs
    $(document).on('input change', '.scrap-in', function() {
      recalc();
    });

    // Modal ack buttons
    $(document).on('click', '#tolAck', function() {
      closeTolModal(true);
    });
    $(document).on('click', '#tolCancel', function() {
      closeTolModal(false);
    });

    // Actionbar buttons
    $(document).on('click', '#btnDraft', function(e) {
      e.preventDefault();
      submitReport(1);
    });

    $('#prodForm').on('submit', function(e) {
      e.preventDefault();
      submitReport(0);
    });


  // RESTORE DRAFT DATA BILA SEDANG EDIT / LANJUTKAN DRAFT
  function restoreDraftData() {
    if (!DRAFT_DATA) return;

    var h = DRAFT_DATA.header || {};

    // 1. Restore Info Header (Card 1)
    if (h.tgl_produksi) $('#tglProduksi').val(h.tgl_produksi);
    if (h.id_asset_machine) $('#id_asset_machine').val(h.id_asset_machine).trigger('change.select2');
    if (h.employee_helper) {
      var helpers = Array.isArray(h.employee_helper) 
        ? h.employee_helper 
        : String(h.employee_helper).split(',').map(function(s) { return s.trim(); }).filter(Boolean);
      // Nama draft yang kini tidak ada di daftar role (role berubah / nonaktif) tetap ditampilkan agar tidak hilang diam-diam
      helpers.forEach(function(n) {
        if ($('#employee_helper option').filter(function() { return this.value === n; }).length === 0) {
          $('#employee_helper').append(new Option(n, n, false, false));
        }
      });
      $('#employee_helper').val(helpers).trigger('change.select2');
    }
    if (h.employee_setter) {
      if ($('#employee_setter option').filter(function() { return this.value === h.employee_setter; }).length === 0) {
        $('#employee_setter').append(new Option(h.employee_setter, h.employee_setter, false, false));
      }
      $('#employee_setter').val(h.employee_setter).trigger('change.select2');
    }
    if (h.start_time) $('#startTime').val(h.start_time.substring(0, 5));
    if (h.finished_time) $('#finishedTime').val(h.finished_time.substring(0, 5));

    // 2. Restore Materials / Coils (Card 2)
    if (DRAFT_DATA.materials && DRAFT_DATA.materials.length > 0) {
      state.coils = [];
      var sourcesToLoad = {};
      $.each(DRAFT_DATA.materials, function(i, m) {
        var src = m.source_warehouse || 'unpack';
        sourcesToLoad[src] = true;
        state.coils.push({
          id: uid(),
          source: src,
          id_warehouse_stock_coil: m.id_unpack_baby_coil,
          code: m.coil_code,
          material: m.material_name,
          nett: getNum(m.nett_weight_packing),
          gross: getNum(m.gross_weight),
          meter: getNum(m.total_meter),
          kulit: getNum(m.berat_kulit),
          clamp: getNum(m.berat_clamp)
        });
      });
      renderCoils();
      refreshCoilSelectors();

      // Preload pilihan coil dari warehouse di background agar jika user ingin mengganti coil dropdown tetap lengkap
      $.each(Object.keys(sourcesToLoad), function(idx, src) {
        loadCoilOptions(src, function() {
          $.each(state.coils, function(i, c) {
            if (c.source === src) {
              var $sel = $('[data-coil-pick="' + c.id + '"]');
              var currentCode = c.code;
              $sel.html(babyCoilSelectOptions(c.source, c.code, c.material));
              if (currentCode) {
                $sel.val(currentCode);
              }
            }
          });
          initSelect2Search('#coilTable');
        });
      });
    }

    // 3. Restore Items (Card 3: KW1 & Bebas, Card 4: KW2)
    if (DRAFT_DATA.items && DRAFT_DATA.items.length > 0) {
      state.fg = [];
      state.kw2.internal = [];
      state.kw2.supplier = [];

      $.each(DRAFT_DATA.items, function(i, it) {
        var cat = it.category_type;
        if (cat === 'kw_1' || cat === 'stok_bebas') {
          state.fg.push({
            id: uid(),
            kind: (cat === 'stok_bebas') ? 'bebas' : 'kw1',
            productId: it.product_lvl_4_id,
            productName: it.nama_product_custom || '',
            targetQty: 0,
            method: 1,
            qty: getNum(it.qty),
            total: getNum(it.berat_total),
            perPcs: getNum(it.berat_per_pcs),
            std: getNum(it.berat_standard),
            selisih: getNum(it.percentage_selisih),
            panjang: getNum(it.panjang_meter),
            beratPerMeterAktual: getNum(it.berat_per_meter_aktual),
            stdPerMeter: getNum(it.berat_per_meter_standard),
            selisihMeter: getNum(it.percentage_selisih_meter),
            coil: it.source_material_coil || ''
          });
        } else if (cat === 'kw_2_internal') {
          state.kw2.internal.push({
            id: uid(),
            productId: it.product_lvl_4_id,
            coil: it.source_material_coil || '',
            size: 0,
            qty: getNum(it.qty),
            total: getNum(it.berat_total),
            ket: it.keterangan || ''
          });
        } else if (cat === 'kw_2_supplier') {
          state.kw2.supplier.push({
            id: uid(),
            productId: it.product_lvl_4_id,
            coil: it.source_material_coil || '',
            size: 0,
            qty: getNum(it.qty),
            total: getNum(it.berat_total),
            ket: it.keterangan || ''
          });
        }
      });

      renderFg();
      renderKw2('internal');
      renderKw2('supplier');
    }

    // 4. Restore Scraps, Sisa, and Hold (Card 5 & Card 6)
    if (DRAFT_DATA.scraps && DRAFT_DATA.scraps.length > 0) {
      state.sisa = [];
      state.hold = [];

      $.each(DRAFT_DATA.scraps, function(i, sc) {
        var st = sc.scrap_type;
        var b = getNum(sc.berat_total);
        if (st === 'tong_coil') {
          $('#scrapTong').val(b);
        } else if (st === 'wrapping') {
          $('#scrapWrapping').val(b);
        } else if (st === 'potongan_pisau') {
          $('#scrapPisau').val(b);
        } else if (st === 'reject_product_internal' || st === 'reject_internal') {
          $('#rejProdInt').val(b);
          if (sc.keterangan) $('#rejKetInt').val(sc.keterangan);
        } else if (st === 'reject_material_internal') {
          $('#rejMatInt').val(b);
          if (sc.keterangan && !$('#rejKetInt').val()) $('#rejKetInt').val(sc.keterangan);
        } else if (st === 'reject_product_supplier' || st === 'reject_supplier') {
          $('#rejProdSup').val(b);
          if (sc.keterangan) $('#rejKetSup').val(sc.keterangan);
        } else if (st === 'reject_material_supplier') {
          $('#rejMatSup').val(b);
          if (sc.keterangan && !$('#rejKetSup').val()) $('#rejKetSup').val(sc.keterangan);
        } else if (st === 'sisa_coil') {
          state.sisa.push({
            id: uid(),
            coil: sc.target_coil_code || '',
            berat: b
          });
        } else if (st === 'hold_coil') {
          state.hold.push({
            id: uid(),
            coil: sc.target_coil_code || '',
            berat: b
          });
        }
      });

      renderLines('sisa');
      renderLines('hold');
    }

    // 5. Restore Confirmations
    if (h.override_confirm_json) {
      try {
        state.confirmations = JSON.parse(h.override_confirm_json);
      } catch (err) {}
    }

    // 6. Restore Claim Material (Card 7, FR-94, FR-98)
    if (DRAFT_DATA.claims && DRAFT_DATA.claims.length > 0) {
      $.each(DRAFT_DATA.claims, function(i, cl) {
        var key = cl.asal + ':' + (cl.no_coil_packing_list || '');
        state.claimIn[key] = {
          coilCode: cl.no_coil_packing_list || '',
          problem: cl.problem || '',
          alasan: cl.alasan_produksi || '',
          bukti: cl.bukti || [],
          status: cl.status_claim || 'Claim Baru'
        };
      });
    }

    // Ubah badge status di header
    var pendingHold = $.grep(DRAFT_DATA.claims || [], function(c) {
      return c.asal === 'Hold Coil' && (!c.decisions || c.decisions.length === 0);
    });
    if (pendingHold.length > 0) {
      $('#docStatus').text('SPK Is On Hold (' + pendingHold[0].no_claim + ')').removeClass('bg-secondary bg-warning').addClass('bg-danger text-white');
    } else {
      $('#docStatus').text('Continue Draft').removeClass('bg-secondary').addClass('bg-warning text-dark');
    }
  }

    // INIT DATA
    if (DRAFT_DATA) {
      restoreDraftData();
    } else {
      initFgFromSpk();
    }
    recalc();
  });
</script>