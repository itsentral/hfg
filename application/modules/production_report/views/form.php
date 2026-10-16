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
</style>

<div class="container-fluid p-3">
  <!-- TOP HEADER -->
  <div class="pr-page-header">
    <div>
      <h1>Input Laporan Produksi</h1>
      <p>SPK: <strong><?= htmlspecialchars($spk['spk_no']); ?></strong> &bull; Tgl SPK: <?= date('d/m/Y', strtotime($spk['tgl_spk'])); ?></p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-secondary px-3 py-2 fs-6" id="docStatus">Draft</span>
      <a href="<?= site_url('production_report'); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    </div>
  </div>

  <form id="prodForm" novalidate>
    <input type="hidden" name="spk_no" value="<?= htmlspecialchars($spk['spk_no']); ?>">
    <input type="hidden" name="id_tr_spk_detail" value="<?= !empty($spk['primary_spk_detail_id']) ? $spk['primary_spk_detail_id'] : ''; ?>">
    <input type="hidden" name="report_id" id="reportId" value="<?= !empty($draft['header']['id']) ? $draft['header']['id'] : ''; ?>">

    <!-- 1. INFORMASI LAPORAN -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">1</span> Informasi Laporan</h2>
          <div class="pr-card-hint">Data SPK &amp; jadwal produksi (read-only info SPK). Lengkapi data mesin dan operator di bawah.</div>
        </div>
      </div>
      <div class="pr-card-body">
        <div class="meta-strip-spk">
          <div class="meta-item">
            <span class="meta-label">Nomor SPK</span>
            <span class="meta-value"><?= htmlspecialchars($spk['spk_no']); ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Tanggal SPK</span>
            <span class="meta-value"><?= date('d/m/Y', strtotime($spk['tgl_spk'])); ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Total Target Qty</span>
            <span class="meta-value text-primary"><?= number_format(!empty($spk['target_qty']) ? $spk['target_qty'] : (!empty($spk['total_target_qty']) ? $spk['total_target_qty'] : 0), 0, ',', '.'); ?> pcs</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Catatan SPK</span>
            <span class="meta-value text-muted"><?= !empty($spk['catatan']) ? htmlspecialchars($spk['catatan']) : '-'; ?></span>
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
                <option value="<?= $m['id']; ?>"><?= htmlspecialchars($m['nm_asset']); ?> (<?= htmlspecialchars($m['kd_asset']); ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold small">Helper Name <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm select2" id="employee_helper" name="employee_helper" required>
              <option value="">-- Pilih Helper --</option>
              <?php foreach ($employees as $e): ?>
                <option value="<?= htmlspecialchars($e['nm_karyawan']); ?>"><?= htmlspecialchars($e['nm_karyawan']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold small">Setter Name <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm select2" id="employee_setter" name="employee_setter" required>
              <option value="">-- Pilih Setter --</option>
              <?php foreach ($employees as $e): ?>
                <option value="<?= htmlspecialchars($e['nm_karyawan']); ?>"><?= htmlspecialchars($e['nm_karyawan']); ?></option>
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
          <div class="pr-card-hint">Pilih baby coil dari Gudang Unpack, WIP, atau Hold. Tarik data berat &amp; meter otomatis.</div>
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
                <th style="width:100px">Sumber</th>
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
                <td colspan="9" class="empty" id="coilEmpty">Belum ada coil ditambahkan. Klik salah satu tombol di atas untuk menambah baby coil.</td>
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
          <div class="pr-card-hint">KW 1 terisi dari SPK. Stok Bebas untuk sisa produksi di luar target SPK. Toleransi selisih aktual vs standar &plusmn; 0.7%.</div>
        </div>
        <div>
          <span class="text-muted small"><i class="fa fa-info-circle me-1"></i>Maksimal baris mengikuti jumlah material coil</span>
        </div>
      </div>
      <div class="pr-card-body">
        <div class="pr-subhead">
          <div class="d-flex align-items-center gap-2">
            <h3 class="mb-0">Produk KW 1 (dari SPK)</h3>
            <span class="badge bg-light text-secondary border small" id="kw1CountBadge">0 / 0 Baris</span>
          </div>
          <button type="button" class="btn btn-sm btn-outline-primary" data-add-fg="kw1"><i class="fa fa-plus me-1"></i> Add Produk KW 1</button>
        </div>
        <div id="fgList"></div>
        <div class="empty text-center text-muted py-2" id="fgEmpty" style="display:none">Belum ada baris Produk KW 1 ditambahkan. Tambahkan baris material coil terlebih dahulu.</div>

        <div class="pr-subhead mt-4">
          <div class="d-flex align-items-center gap-2">
            <h3 class="mb-0">Stok Bebas</h3>
            <span class="badge bg-light text-secondary border small" id="bebasCountBadge">0 / 0 Baris</span>
          </div>
          <button type="button" class="btn btn-sm btn-outline-primary" data-add-fg="bebas"><i class="fa fa-plus me-1"></i> Add Stok Bebas</button>
        </div>
        <div id="bebasList"></div>
        <div class="empty text-center text-muted py-2" id="bebasEmpty">Belum ada Stok Bebas ditambahkan. Tambahkan baris material coil terlebih dahulu.</div>
      </div>
    </section>

    <!-- 4. FG KW 2 -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">4</span> Finish Good KW 2</h2>
          <div class="pr-card-hint">Dipisah KW 2 Internal dan Supplier. Nama otomatis di-generate: {Produk}-KW2-{I/S}-01, ...</div>
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
                <th class="num" style="width:110px">Berat Total (kg)</th>
                <th class="num" style="width:110px">Berat/Pcs (kg)</th>
                <th>Nama KW 2</th>
                <th>Keterangan</th>
                <th style="width:40px"></th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              <tr>
                <td colspan="10" class="empty">Belum ada KW 2 Internal.</td>
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
                <th class="num" style="width:110px">Berat Total (kg)</th>
                <th class="num" style="width:110px">Berat/Pcs (kg)</th>
                <th>Nama KW 2</th>
                <th>Keterangan</th>
                <th style="width:40px"></th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              <tr>
                <td colspan="10" class="empty">Belum ada KW 2 Supplier.</td>
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
          <div class="pr-card-hint">Sisa Coil masuk Gudang WIP. Bila terdapat Hold Coil, tombol Save akan dikunci (harus melalui Hold &amp; Claim).</div>
        </div>
      </div>
      <div class="pr-card-body">
        <!-- Sisa Coil (Atas) -->
        <div class="mb-4">
          <div class="pr-subhead">
            <div class="d-flex align-items-center gap-2">
              <h3 class="mb-0">Sisa Coil</h3>
              <span class="badge bg-light text-secondary border small" id="sisaCountBadge">0 / 0 Baris</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="addSisa"><i class="fa fa-plus me-1"></i> Add Sisa</button>
          </div>
          <div class="table-responsive">
            <table class="tbl-custom" id="sisaTable">
              <thead>
                <tr>
                  <th>Baby Coil Terpakai</th>
                  <th class="num" style="width:180px">Berat Sisa (kg)</th>
                  <th style="width:50px"></th>
                </tr>
              </thead>
              <tbody></tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="empty">Tidak ada sisa coil.</td>
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
              <span class="badge bg-light text-secondary border small" id="holdCountBadge">0 / 0 Baris</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" id="addHold"><i class="fa fa-plus me-1"></i> Add Hold</button>
          </div>
          <div class="table-responsive">
            <table class="tbl-custom" id="holdTable">
              <thead>
                <tr>
                  <th>Baby Coil Terpakai</th>
                  <th class="num" style="width:180px">Berat Hold (kg)</th>
                  <th style="width:50px"></th>
                </tr>
              </thead>
              <tbody></tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="empty">Tidak ada hold coil.</td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="alert alert-warning py-2 mt-2 small" id="holdNote" style="display:none">
            <i class="fa fa-exclamation-triangle me-1"></i> Ada Hold Coil — tombol Save dikunci, gunakan tombol <strong>Hold &amp; Claim</strong>.
          </div>
        </div>
      </div>
    </section>

    <!-- 6. KOMPONEN SCRAP -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">6</span> Komponen Scrap</h2>
          <div class="pr-card-hint">Input berat scrap produksi (Tong, Wrapping, Potongan Pisau, serta Reject Internal &amp; Supplier).</div>
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
                <th>Keterangan Reject</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Internal</strong></td>
                <td class="num"><input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in text-end" id="rejProdInt" value="0"></td>
                <td class="num"><input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in text-end" id="rejMatInt" value="0"></td>
                <td><input type="text" class="form-control form-control-sm" id="rejKetInt" placeholder="Catatan reject internal..."></td>
              </tr>
              <tr>
                <td><strong>Supplier</strong></td>
                <td class="num"><input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in text-end" id="rejProdSup" value="0"></td>
                <td class="num"><input type="number" step="0.01" min="0" class="form-control form-control-sm scrap-in text-end" id="rejMatSup" value="0"></td>
                <td><input type="text" class="form-control form-control-sm" id="rejKetSup" placeholder="Catatan reject supplier..."></td>
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

    <!-- 7. SUMMARY PACKING LIST VS AKTUAL -->
    <section class="pr-card">
      <div class="pr-card-header">
        <div>
          <h2 class="pr-card-title"><span class="pr-card-num">7</span> Summary — Packing List vs Aktual</h2>
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
            <span class="sm-k">Total Sisa Coil</span>
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

    <!-- 8. AUDIT KONFIRMASI SELISIH -->
    <section class="pr-card" id="auditCard" style="display:none">
      <div class="pr-card-header bg-light">
        <div>
          <h2 class="pr-card-title text-danger"><span class="pr-card-num text-danger">8</span> Jejak Konfirmasi Selisih</h2>
          <div class="pr-card-hint">Daftar konfirmasi selisih di luar toleransi &plusmn; 0,7% beserta identitas dan waktu konfirmasi.</div>
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
              <th style="width:200px">Dikonfirmasi Oleh</th>
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
        <span id="gateMsg" class="text-muted small">Save aktif bila tidak ada Hold Coil.</span>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary px-3" id="btnDraft">
          <i class="fa fa-save me-1"></i> Save Draft
        </button>
        <button type="button" class="btn btn-warning px-3" id="btnHoldClaim" disabled>
          <i class="fa fa-pause-circle me-1"></i> Hold &amp; Claim
        </button>
        <button type="submit" class="btn btn-primary px-4" id="btnSave">
          <i class="fa fa-check-circle me-1"></i> Simpan Laporan
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
        <button type="button" class="btn btn-primary btn-sm" id="tolAck">Sudah Sesuai Aktual</button>
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
  var CURRENT_USER = {
    name: <?= json_encode($this->auth->user_name()); ?>,
    role: "User"
  };
  var SAVE_URL = "<?= site_url('production_report/save'); ?>";
  var COIL_URL = "<?= site_url('production_report/ajax_get_coils'); ?>";
  var BASE_MODULE_URL = "<?= site_url('production_report'); ?>";
  var TOLERANCE = 0.7; // ± 0.7%

  var SRC_LABEL = {
    unpack: "Gudang Unpack",
    wip: "Gudang WIP",
    hold: "Gudang Hold"
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
          return $(this).data('placeholder') || $(this).find('option:first').text() || '-- Pilih --';
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
    var opts = '<option value="">— Pilih Baby Coil Terpakai —</option>';
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
    var opts = '<option value="">— Pilih Baby Coil Terpakai —</option>';
    $.each(state.coils, function(i, c) {
      if (c.code && !usedCoils[c.code]) {
        opts += '<option value="' + c.code + '">' + c.code + ' · ' + c.material + '</option>';
      }
    });
    return opts;
  }

  function kw2CoilOptions(currentKw2Id, currentType) {
    var opts = '<option value="">— Pilih Baby Coil Terpakai —</option>';
    $.each(state.coils, function(i, c) {
      if (c.code) {
        opts += '<option value="' + c.code + '">' + c.code + ' · ' + c.material + '</option>';
      }
    });
    return opts;
  }

  function allProductOptions(selectedId) {
    var opts = '<option value="">— Pilih Master Produk —</option>';
    $.each(ALL_PRODUCTS, function(i, p) {
      var sel = (String(p.id) === String(selectedId)) ? ' selected' : '';
      var w = p.weight ? p.weight : 0;
      opts += '<option value="' + p.id + '"' + sel + '>' + p.nama + ' (' + w + ' kg/pcs)</option>';
    });
    return opts;
  }

  function spkProductOptions(selectedId) {
    var opts = '<option value="">— Pilih Produk dari SPK —</option>';
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

    var opts = '<option value="">— Pilih Baby Coil —</option>';
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
      html += '    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" data-del-coil="' + c.id + '" title="Hapus">✕</button>';
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
        Swal.fire('Material Sudah Dipilih', 'Baby Coil "' + code + '" sudah dipilih pada baris lain di Sumber Material.', 'warning');
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
      Swal.fire('Perhatian', 'Silakan tambahkan Baby Coil pada Sumber Material (Section 2) terlebih dahulu.', 'warning');
      return;
    }

    var currentKw1 = $.grep(state.fg, function(f) {
      return f.kind === 'kw1';
    }).length;
    if (currentKw1 >= maxCoils) {
      Swal.fire('Maksimal Tercapai', 'Jumlah baris Produk KW 1 (' + currentKw1 + ') tidak boleh melebihi jumlah material coil (' + maxCoils + ' baris).', 'warning');
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
      Swal.fire('Perhatian', 'Silakan tambahkan Baby Coil pada Sumber Material (Section 2) terlebih dahulu.', 'warning');
      return;
    }

    var currentBebas = $.grep(state.fg, function(f) {
      return f.kind === 'bebas';
    }).length;
    if (currentBebas >= maxCoils) {
      Swal.fire('Maksimal Tercapai', 'Jumlah baris Stok Bebas (' + currentBebas + ') tidak boleh melebihi jumlah material coil (' + maxCoils + ' baris).', 'warning');
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

    var delBtn = '<button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" data-del-fg="' + f.id + '" title="Hapus Baris">✕</button>';

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
    html += '    <div class="method-toggle">';
    html += '      <label><input type="radio" name="m-' + f.id + '" value="1" ' + (p1 ? 'checked' : '') + ' data-fg-method="' + f.id + '"> <span>Metode 1: Qty + Berat Total</span></label>';
    html += '      <label><input type="radio" name="m-' + f.id + '" value="2" ' + (!p1 ? 'checked' : '') + ' data-fg-method="' + f.id + '"> <span>Metode 2: Qty + Berat/Pcs</span></label>';
    html += '    </div>';
    html += '    <div class="row g-2 align-items-end">';
    html += '      <div class="col-md-3">';
    html += '        <label class="form-label small fw-semibold mb-1">Baby Coil Terpakai</label>';
    html += '        <select data-fg-coil="' + f.id + '" data-kind="' + f.kind + '" class="form-select form-select-sm select2" style="width:100%">' + coilOptions(f.id, f.kind) + '</select>';
    html += '      </div>';
    html += '      <div class="col-md-2">';
    html += '        <label class="form-label small fw-semibold mb-1">Qty (pcs)</label>';
    html += '        <input type="number" min="0" step="1" data-fg-qty="' + f.id + '" class="form-control form-control-sm text-end" value="' + (f.qty || '') + '">';
    html += '      </div>';
    html += '      <div class="col-md-2">';
    html += '        <label class="form-label small fw-semibold mb-1">' + (p1 ? 'Berat Total (kg)' : 'Berat/Pcs (kg)') + '</label>';
    html += '        <input type="number" min="0" step="0.01" data-fg-input="' + f.id + '" class="form-control form-control-sm text-end" value="' + (p1 ? (f.total || '') : (f.perPcs || '')) + '">';
    html += '      </div>';
    html += '      <div class="col-md-2">';
    html += '        <label class="form-label small fw-semibold mb-1">' + (p1 ? 'Berat/Pcs (kg)' : 'Berat Total (kg)') + '</label>';
    html += '        <input type="text" class="form-control form-control-sm bg-light text-end" readonly data-fg-derived="' + f.id + '" value="' + (p1 ? formatNum(f.perPcs) : formatNum(f.total)) + '">';
    html += '      </div>';
    html += '      <div class="col-md-1">';
    html += '        <label class="form-label small fw-semibold mb-1">Std/Pcs</label>';
    html += '        <input type="text" class="form-control form-control-sm bg-light text-end" readonly data-fg-std="' + f.id + '" value="' + (f.std ? formatNum(f.std) : '') + '">';
    html += '      </div>';
    html += '      <div class="col-md-2">';
    html += '        <label class="form-label small fw-semibold mb-1">% Selisih</label>';
    html += '        <div>';
    html += '          <span class="selisih-badge ' + (Math.abs(f.selisih) > TOLERANCE ? 'bad' : 'ok') + '" data-fg-selisih="' + f.id + '">';
    html += '            ' + formatNum(f.selisih) + ' %';
    html += '          </span>';
    html += '        </div>';
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
    $('#kw1CountBadge').text(kw1.length + ' / ' + maxCoils + ' Baris');
    $('#bebasCountBadge').text(bebas.length + ' / ' + maxCoils + ' Baris');

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
      }
    }
    if (f.method === 1) {
      f.perPcs = (f.qty > 0) ? f.total / f.qty : 0;
    } else {
      f.total = f.qty * f.perPcs;
    }
    f.selisih = (f.std > 0) ? ((f.perPcs - f.std) / f.std) * 100 : 0;
  }

  function updateFgDerivedView() {
    $.each(state.fg, function(i, f) {
      var $der = $('[data-fg-derived="' + f.id + '"]');
      var $std = $('[data-fg-std="' + f.id + '"]');
      var $sel = $('[data-fg-selisih="' + f.id + '"]');
      var $badge = $('[data-fg-confirm="' + f.id + '"]');

      if ($der.length) $der.val(f.method === 1 ? formatNum(f.perPcs) : formatNum(f.total));
      if ($std.length) $std.val(f.std ? formatNum(f.std) : '');
      if ($sel.length) {
        $sel.text(formatNum(f.selisih) + ' %');
        $sel.removeClass('ok bad').addClass(Math.abs(f.selisih) > TOLERANCE ? 'bad' : 'ok');
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
      html += '  <td><input type="text" data-kw2-ket="' + r.id + '" class="form-control form-control-sm" placeholder="Keterangan..." value="' + (r.ket || '') + '"></td>';
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
    var label = (kind === 'sisa') ? 'Sisa Coil' : 'Hold Coil';

    if (maxCoils === 0) {
      Swal.fire('Perhatian', 'Silakan tambahkan Baby Coil pada Sumber Material (Section 2) terlebih dahulu.', 'warning');
      return;
    }

    if (state[kind].length >= maxCoils) {
      Swal.fire('Maksimal Tercapai', 'Jumlah baris ' + label + ' (' + state[kind].length + ') tidak boleh melebihi jumlah material coil (' + maxCoils + ' baris).', 'warning');
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
    $(badgeId).text(rows.length + ' / ' + maxCoils + ' Baris');
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
      $('#tolNote').text('⚠ Selisih ' + formatNum(selisihPct) + '% melebihi toleransi ± ' + TOLERANCE + '%. Perlu konfirmasi audit.');
    } else {
      $('#sumSelisihBox').removeClass('bad');
      $('#tolNote').text('Toleransi ± ' + TOLERANCE + '% terhadap Net Weight Produksi.');
    }

    // Gate: Hold Coil check
    var hasHold = (state.hold.length > 0);
    $('#btnSave').prop('disabled', hasHold);
    $('#btnHoldClaim').prop('disabled', !hasHold);
    if (hasHold) {
      $('#holdNote').show();
      $('#gateMsg').text('Ada Hold Coil — Tombol Save dikunci. Gunakan Hold & Claim.');
    } else {
      $('#holdNote').hide();
      $('#gateMsg').text('Save aktif bila tidak ada Hold Coil.');
    }

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
      statement: ctx.statement || 'Sudah sesuai aktual',
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
  async function submitReport(isDraft, isHoldClaim) {
    isDraft = isDraft || 0;
    isHoldClaim = isHoldClaim || false;

    var calcRes = recalc();

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
        Swal.fire('Validasi Gagal', 'Tanggal Produksi wajib diisi.', 'warning');
        $('#tglProduksi').focus();
        return;
      }
      if (!idMachine) {
        Swal.fire('Validasi Gagal', 'Mesin produksi wajib dipilih.', 'warning');
        $('#id_asset_machine').select2('open');
        return;
      }
      if (!empHelper) {
        Swal.fire('Validasi Gagal', 'Helper Name wajib dipilih.', 'warning');
        $('#employee_helper').select2('open');
        return;
      }
      if (!empSetter) {
        Swal.fire('Validasi Gagal', 'Setter Name wajib dipilih.', 'warning');
        $('#employee_setter').select2('open');
        return;
      }
      if (!startTime) {
        Swal.fire('Validasi Gagal', 'Start Time wajib diisi.', 'warning');
        $('#startTime').focus();
        return;
      }
      if (!finishedTime) {
        Swal.fire('Validasi Gagal', 'Finished Time wajib diisi.', 'warning');
        $('#finishedTime').focus();
        return;
      }

      // 2. Validasi Add Coil (Sumber Material) - Semua Mandatory
      if (state.coils.length === 0) {
        Swal.fire('Validasi Gagal', 'Sumber Material (Add Coil) wajib ditambahkan minimal 1 baris.', 'warning');
        return;
      }
      for (var cIdx = 0; cIdx < state.coils.length; cIdx++) {
        var c = state.coils[cIdx];
        if (!c.code) {
          Swal.fire('Validasi Gagal', 'Baris ke-' + (cIdx + 1) + ' pada Sumber Material belum memilih Baby Coil.', 'warning');
          return;
        }
      }

      // 3. Validasi Finish Good KW 1 (Mandatory, Stok Bebas Optional)
      var kw1Rows = $.grep(state.fg, function(f) {
        return f.kind === 'kw1';
      });
      if (kw1Rows.length === 0) {
        Swal.fire('Validasi Gagal', 'Produk KW 1 (dari SPK) wajib diisi minimal 1 baris.', 'warning');
        return;
      }
      for (var kIdx = 0; kIdx < kw1Rows.length; kIdx++) {
        var kw1 = kw1Rows[kIdx];
        if (!kw1.productId) {
          Swal.fire('Validasi Gagal', 'Baris ke-' + (kIdx + 1) + ' Produk KW 1 belum memilih Produk.', 'warning');
          return;
        }
        if (!kw1.coil) {
          Swal.fire('Validasi Gagal', 'Baris ke-' + (kIdx + 1) + ' Produk KW 1 belum memilih Baby Coil Terpakai.', 'warning');
          return;
        }
        if (getNum(kw1.qty) <= 0) {
          Swal.fire('Validasi Gagal', 'Qty pada baris ke-' + (kIdx + 1) + ' Produk KW 1 harus lebih besar dari 0.', 'warning');
          return;
        }
        if (getNum(kw1.total) <= 0) {
          Swal.fire('Validasi Gagal', 'Berat Total pada baris ke-' + (kIdx + 1) + ' Produk KW 1 harus lebih besar dari 0.', 'warning');
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
              label: f.productName + ' (' + (f.kind === 'bebas' ? 'Stok Bebas' : 'KW 1') + ')',
              selisih: f.selisih,
              msg: 'Produk "' + f.productName + '" memiliki selisih aktual ' + formatNum(f.selisih) + '% terhadap berat standar (' + formatNum(f.std) + ' kg/pcs). Toleransi maksimum ± ' + TOLERANCE + '%.'
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
          Swal.fire('Perhatian', 'Penyimpanan dibatalkan. Silakan sesuaikan data aktual.', 'info');
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
      scraps: []
    };

    // Items: FG KW1 & Bebas
    $.each(state.fg, function(i, f) {
      payload.items.push({
        kategori: (f.kind === 'bebas') ? 'stok_bebas' : 'kw_1',
        id_product_lvl_4: f.productId,
        nama_produk: f.productName,
        kode_baby_coil: f.coil,
        metode_input: f.method,
        qty: f.qty,
        berat_total: f.total,
        berat_per_pcs: f.perPcs,
        berat_standard_pcs: f.std,
        selisih_persen: f.selisih,
        size_meter: 0,
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
      jenis_scrap: 'reject_internal',
      berat: getNum($('#rejProdInt').val()) + getNum($('#rejMatInt').val()),
      keterangan: $('#rejKetInt').val()
    });
    payload.scraps.push({
      jenis_scrap: 'reject_supplier',
      berat: getNum($('#rejProdSup').val()) + getNum($('#rejMatSup').val()),
      keterangan: $('#rejKetSup').val()
    });
    $.each(state.sisa, function(i, s) {
      payload.scraps.push({
        jenis_scrap: 'sisa_coil',
        kode_baby_coil: s.coil,
        berat: s.berat,
        keterangan: 'Masuk Gudang WIP'
      });
    });
    $.each(state.hold, function(i, h) {
      payload.scraps.push({
        jenis_scrap: 'hold_coil',
        kode_baby_coil: h.coil,
        berat: h.berat,
        keterangan: 'Masuk Gudang Hold'
      });
    });

    // Confirmation dialog
    Swal.fire({
      title: isDraft ? 'Simpan Draft?' : 'Simpan Laporan Produksi?',
      text: isDraft ? 'Data akan disimpan sebagai draft.' : 'Data produksi akan disimpan secara permanen.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Simpan',
      cancelButtonText: 'Batal'
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
              title: 'Berhasil',
              text: isDraft ? 'Draft laporan produksi berhasil disimpan!' : 'Laporan produksi berhasil disimpan!',
              timer: 1500,
              showConfirmButton: false
            }).then(function() {
              window.location.href = BASE_MODULE_URL;
            });
          } else {
            Swal.fire('Gagal', response.message || 'Terjadi kesalahan sistem.', 'error');
          }
        },
        error: function() {
          Swal.close();
          Swal.fire('Error', 'Terjadi kesalahan jaringan atau server saat menyimpan.', 'error');
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
          var labelKind = (currentKind === 'bebas') ? 'Stok Bebas' : 'Produk KW 1';
          Swal.fire('Material Sudah Dipilih', 'Baby Coil "' + val + '" sudah dipilih di baris ' + labelKind + ' lainnya.', 'warning');
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
          Swal.fire('Material Sudah Dipilih', 'Baby Coil "' + val + '" sudah dipilih di baris ' + (kind === 'sisa' ? 'Sisa Coil' : 'Hold Coil') + ' lainnya.', 'warning');
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

    $(document).on('click', '#btnHoldClaim', function(e) {
      e.preventDefault();
      submitReport(0, true);
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
    if (h.employee_helper) $('#employee_helper').val(h.employee_helper).trigger('change.select2');
    if (h.employee_setter) $('#employee_setter').val(h.employee_setter).trigger('change.select2');
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
        } else if (st === 'reject_internal') {
          $('#rejProdInt').val(b);
          if (sc.keterangan) $('#rejKetInt').val(sc.keterangan);
        } else if (st === 'reject_supplier') {
          $('#rejProdSup').val(b);
          if (sc.keterangan) $('#rejKetSup').val(sc.keterangan);
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

    // Ubah badge status di header
    $('#docStatus').text('Lanjutkan Draft').removeClass('bg-secondary').addClass('bg-warning text-dark');
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