<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<style>
  :root {
    --cl-primary: #0d6efd;
    --cl-primary-dark: #0a58ca;
    --cl-text: #1e293b;
    --cl-muted: #64748b;
    --cl-border: #e2e8f0;
    --cl-bg-light: #f8fafc;
    --cl-success: #198754;
    --cl-warning: #b45309;
    --cl-danger: #dc3545;
  }

  .cl-page-header {
    background: #fff;
    border-radius: 10px;
    padding: 18px 24px;
    margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    border: 1px solid var(--cl-border);
  }
  .cl-page-header h1 {
    font-size: 20px;
    font-weight: 700;
    color: var(--cl-text);
    margin: 0 0 4px;
  }
  .cl-page-header p {
    font-size: 13px;
    color: var(--cl-muted);
    margin: 0;
  }

  /* Stat summary cards */
  .cl-summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
  }
  .cl-stat-card {
    background: #fff;
    border-radius: 10px;
    padding: 16px 20px;
    border: 1px solid var(--cl-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .cl-stat-card .val {
    font-size: 24px;
    font-weight: 700;
    color: var(--cl-text);
    line-height: 1.2;
  }
  .cl-stat-card .lbl {
    font-size: 12.5px;
    font-weight: 500;
    color: var(--cl-muted);
    margin-top: 4px;
  }

  /* Card and table */
  .cl-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid var(--cl-border);
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    overflow: hidden;
  }
  .cl-card-body {
    padding: 20px 24px;
  }

  /* Filters toolbar */
  .cl-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    margin-bottom: 16px;
  }
  .cl-toolbar select, .cl-toolbar input {
    height: 38px;
    font-size: 13px;
    border-radius: 6px;
  }
  .cl-toolbar .form-label-inline {
    font-size: 13px;
    font-weight: 600;
    margin: 0;
    color: var(--cl-text);
  }

  /* Badges & Labels */
  .cl-badge {
    display: inline-block;
    padding: 3px 8px;
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 4px;
    white-space: nowrap;
  }
  .cl-badge-baru { background: #fef3c7; color: #92400e; }
  .cl-badge-email { background: #e0f2fe; color: #0369a1; }
  .cl-badge-kompensasi { background: #dbeafe; color: #1d4ed8; }
  .cl-badge-retur { background: #fef9c3; color: #854d0e; }
  .cl-badge-komp-retur { background: #ede9fe; color: #6d28d9; }
  .cl-badge-hold { background: #fee2e2; color: #991b1b; }
  .cl-badge-release { background: #dcfce7; color: #166534; }
  .cl-badge-slitting { background: #f1f5f9; color: #334155; }

  /* Table styling */
  .cl-table {
    width: 100%;
    margin-bottom: 0;
    font-size: 12.5px;
  }
  .cl-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    padding: 10px 12px;
    border-bottom: 1px solid var(--cl-border);
    vertical-align: middle;
  }
  .cl-table td {
    padding: 10px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
  }
  .cl-table tbody tr:hover {
    background-color: #f8fafc;
  }

  /* Progress indicator inside table */
  .cl-prog-step {
    font-size: 11px;
    line-height: 1.3;
    color: #94a3b8;
  }
  .cl-prog-step.is-done {
    color: var(--cl-success);
    font-weight: 600;
  }

  /* Modal forms */
  .cl-modal-kv {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 6px 14px;
    font-size: 13px;
    background: #f8fafc;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    border: 1px solid var(--cl-border);
  }
  .cl-modal-kv dt { color: var(--cl-muted); font-weight: 500; margin: 0; }
  .cl-modal-kv dd { font-weight: 600; color: var(--cl-text); margin: 0; }

  .cl-dec-box {
    background: #fcfcfd;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px;
    margin-bottom: 14px;
  }
  .cl-dec-box h6 {
    font-size: 13.5px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #1e293b;
  }

  .cl-bk-item {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
    margin-bottom: 8px;
    font-size: 12.5px;
  }
  .cl-bk-item img {
    max-width: 100%;
    max-height: 260px;
    border-radius: 6px;
    margin-top: 8px;
    display: block;
    border: 1px solid #cbd5e1;
  }
  .cl-bk-item iframe {
    width: 100%;
    height: 350px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    margin-top: 8px;
  }

  .hint-box {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 10px 14px;
    font-size: 12.5px;
    color: #1e40af;
    border-radius: 0 6px 6px 0;
    margin-bottom: 16px;
  }
</style>

<div class="container-fluid px-3 py-2">
  <!-- PAGE HEADER -->
  <div class="cl-page-header">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1>Daftar Claim Material</h1>
        <p>Kelola claim material ke supplier (Hold Coil, KW 2, Reject) dari Laporan Produksi, kirim email keluhan, dan tetapkan Process Decision (Kompensasi / Retur).</p>
      </div>
      <div>
        <a href="<?= site_url('claim_material/report_biaya') ?>" class="btn btn-outline-primary btn-sm me-1">
          <i class="fa fa-bar-chart me-1"></i> Report Biaya Kompensasi
        </a>
        <a href="<?= site_url('stock_from_warehouse#tab-on-hold') ?>" class="btn btn-outline-secondary btn-sm">
          <i class="fa fa-pause-circle me-1"></i> Stok Material On Hold (Gudang)
        </a>
      </div>
    </div>
  </div>

  <!-- KPI SUMMARY CARDS -->
  <div class="cl-summary-cards">
    <div class="cl-stat-card">
      <div class="val" id="statBelumEmail"><?= (int)($stats['belum_email'] ?? 0) ?></div>
      <div class="lbl">Belum diemail ke Supplier</div>
    </div>
    <div class="cl-stat-card">
      <div class="val text-primary" id="statMenunggu"><?= (int)($stats['menunggu_decision'] ?? 0) ?></div>
      <div class="lbl">Menunggu Process Decision</div>
    </div>
    <div class="cl-stat-card">
      <div class="val text-success" id="statPutus"><?= (int)($stats['sudah_diputuskan'] ?? 0) ?></div>
      <div class="lbl">Sudah Diputuskan (Claim)</div>
    </div>
  </div>

  <!-- MAIN CARD -->
  <div class="cl-card">
    <div class="cl-card-body">
      <!-- TOOLBAR FILTER -->
      <div class="cl-toolbar">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label-inline">Supplier:</label>
          <select class="form-select form-select-sm" id="filterSupplier" style="width: 220px;">
            <option value="">(Semua Supplier)</option>
            <?php if (!empty($suppliers)): foreach ($suppliers as $sup): ?>
              <option value="<?= htmlspecialchars($sup['id_suplier']) ?>"><?= htmlspecialchars($sup['nama_supplier']) ?></option>
            <?php endforeach; endif; ?>
          </select>
        </div>

        <div class="d-flex align-items-center gap-2">
          <label class="form-label-inline">Asal:</label>
          <select class="form-select form-select-sm" id="filterAsal" style="width: 170px;">
            <option value="">(Semua Asal)</option>
            <option value="Hold Coil">Hold Coil</option>
            <option value="KW 2 (Supplier)">KW 2 (Supplier)</option>
            <option value="Reject (Supplier)">Reject (Supplier)</option>
          </select>
        </div>

        <div class="d-flex align-items-center gap-2 flex-grow-1">
          <input type="text" class="form-control form-control-sm" id="searchKeyword" placeholder="Cari No. Claim / No. Coil / PO / Problem...">
        </div>

        <div>
          <button type="button" class="btn btn-primary btn-sm" id="btnProcessSelected" disabled>
            <i class="fa fa-gavel me-1"></i> Process Decision (Terpilih: <span id="selCount">0</span>)
          </button>
        </div>
      </div>

      <div class="hint-box">
        <i class="fa fa-info-circle me-1"></i> Centang beberapa claim dari <b>satu supplier yang sama</b> (misal: baris KW 2 dan Hold Coil dari satu SPK), lalu klik tombol <b>Process Decision (Terpilih)</b> untuk menetapkan keputusan bersamaan dengan satu kesepakatan.
      </div>

      <!-- TABLE -->
      <div class="table-responsive">
        <table class="table cl-table" id="tableClaims">
          <thead>
            <tr>
              <th width="35" class="text-center">
                <input type="checkbox" id="checkAllClaims" title="Centang semua yang belum diputuskan">
              </th>
              <th width="40">No</th>
              <th>Nama Supplier</th>
              <th>Nama Material</th>
              <th>No. Coil Packing List</th>
              <th>No. PO</th>
              <th class="text-end">Berat (Kg)</th>
              <th>Problem</th>
              <th>Asal</th>
              <th>Status Claim</th>
              <th>Progres</th>
              <th width="150" class="text-center">Action</th>
            </tr>
          </thead>
          <tbody id="claimTableBody">
            <tr>
              <td colspan="12" class="text-center py-4 text-muted">Memuat data claim...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 1: EMAIL KELUHAN KE SUPPLIER (FR-77)                     -->
<!-- ============================================================== -->
<div class="modal fade" id="modalEmail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white py-2">
        <h5 class="modal-title fs-6" id="emailModalTitle">Kirim Email Keluhan ke Supplier</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="emailClaimId">
        <dl class="cl-modal-kv mb-3">
          <dt>No. Claim</dt><dd id="emNoClaim">-</dd>
          <dt>Nama Supplier</dt><dd id="emSupplier">-</dd>
          <dt>Material & Coil</dt><dd id="emMaterial">-</dd>
          <dt>No. PO / Berat</dt><dd id="emPoKg">-</dd>
          <dt>Problem</dt><dd id="emProblem">-</dd>
        </dl>

        <div class="mb-3">
          <label class="form-label fw-bold">Email Tujuan Supplier <span class="text-danger">*</span></label>
          <input type="email" class="form-control form-control-sm" id="emailTo" required placeholder="misal: claim@supplier.com">
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Subjek Email <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" id="emailSubject" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Isi Pesan Keluhan <span class="text-danger">*</span></label>
          <textarea class="form-control form-control-sm" id="emailBody" rows="5" required></textarea>
        </div>

        <div class="mb-2">
          <label class="form-label fw-bold">Dokumen & Bukti Pendukung Terlampir (FR-94)</label>
          <div id="emailBuktiList" class="p-2 border rounded bg-light" style="max-height: 120px; overflow-y: auto;">
            <span class="text-muted small">Tidak ada berkas bukti terlampir</span>
          </div>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm" id="btnSubmitEmail">
          <i class="fa fa-paper-plane me-1"></i> Kirim Email Keluhan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: PROCESS DECISION (FR-78)                              -->
<!-- ============================================================== -->
<div class="modal fade" id="modalDecision" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white py-2">
        <h5 class="modal-title fs-6" id="decModalTitle">Process Decision Claim Material</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
        <!-- KESEPAKATAN DENGAN SUPPLIER -->
        <div class="mb-3 p-3 bg-light border rounded">
          <div class="row align-items-center">
            <div class="col-md-4">
              <label class="form-label fw-bold mb-0">Kesepakatan dengan Supplier <span class="text-danger">*</span></label>
              <div class="small text-muted">Hasil negosiasi luar sistem (BPMN D003)</div>
            </div>
            <div class="col-md-8">
              <select class="form-select form-select-sm" id="decKesepakatan" required>
                <option value="">-- Pilih Kesepakatan --</option>
                <option value="Kompensasi">Kompensasi</option>
                <option value="Retur">Retur</option>
                <option value="Kompensasi + Retur">Kompensasi + Retur (Dua Jalur)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- CONTAINER DECISION PER CLAIM -->
        <div id="decisionCardsContainer">
          <!-- Di-render dinamis oleh JavaScript -->
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success btn-sm" id="btnSubmitDecision">
          <i class="fa fa-check me-1"></i> Simpan Keputusan Claim
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 3: VIEW ALASAN & BUKTI FOTO DARI PRODUKSI (FR-94)        -->
<!-- ============================================================== -->
<div class="modal fade" id="modalAlasan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light py-2">
        <h5 class="modal-title fs-6">Alasan Hold & Bukti Pendukung dari Produksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <dl class="cl-modal-kv mb-3">
          <dt>No. Claim</dt><dd id="viewNoClaim">-</dd>
          <dt>Nama Supplier</dt><dd id="viewSupplier">-</dd>
          <dt>Nama Material</dt><dd id="viewMaterial">-</dd>
          <dt>No. Coil</dt><dd id="viewCoil">-</dd>
          <dt>Berat</dt><dd id="viewBerat">-</dd>
          <dt>Problem</dt><dd class="text-danger fw-bold" id="viewProblem">-</dd>
          <dt>Alasan Produksi</dt><dd id="viewAlasanText">-</dd>
        </dl>

        <h6 class="fw-bold mb-2">Berkas Bukti Foto / Video / QC Report:</h6>
        <div id="viewBuktiContainer">
          <div class="text-muted small">Tidak ada lampiran berkas bukti.</div>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 4: LIHAT JURNAL GL CLAIM (FR-93)                         -->
<!-- ============================================================== -->
<div class="modal fade" id="modalJurnal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white py-2">
        <h5 class="modal-title fs-6">Jurnal Transaksi GL — Claim Material</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <dl class="cl-modal-kv mb-3">
          <dt>No. Claim</dt><dd id="jrNoClaim">-</dd>
          <dt>Nama Supplier</dt><dd id="jrSupplier">-</dd>
          <dt>Material</dt><dd id="jrMaterial">-</dd>
          <dt>Status Keputusan</dt><dd id="jrStatus">-</dd>
        </dl>

        <div id="jrEventsContainer">
          <!-- Di-render dinamis -->
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap 5 JS & SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  let claimsData = [];
  let selectedClaimIds = new Set();
  const modalEmailEl = new bootstrap.Modal(document.getElementById('modalEmail'));
  const modalDecisionEl = new bootstrap.Modal(document.getElementById('modalDecision'));
  const modalAlasanEl = new bootstrap.Modal(document.getElementById('modalAlasan'));
  const modalJurnalEl = new bootstrap.Modal(document.getElementById('modalJurnal'));

  function formatNum(n, d = 2) {
    return Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
  }

  function getBadgeClass(status) {
    if (status === 'Claim Baru') return 'cl-badge-baru';
    if (status === 'Email Terkirim') return 'cl-badge-email';
    if (status === 'Kompensasi') return 'cl-badge-kompensasi';
    if (status === 'Retur') return 'cl-badge-retur';
    if (status === 'Kompensasi + Retur') return 'cl-badge-komp-retur';
    return 'bg-secondary text-white';
  }

  function renderProgress(c) {
    const hasDec = (c.decisions && c.decisions.length > 0);
    const hasEmail = (c.status_claim !== 'Claim Baru');
    const isDone = hasDec;

    return `
      <div class="cl-prog-step is-done">&#10003; 1. Laporan Produksi</div>
      <div class="cl-prog-step ${hasEmail ? 'is-done' : ''}">${hasEmail ? '&#10003;' : '&#9675;'} 2. Email Keluhan</div>
      <div class="cl-prog-step ${hasDec ? 'is-done' : ''}">${hasDec ? '&#10003;' : '&#9675;'} 3. Process Decision</div>
    `;
  }

  function loadClaims() {
    const supplier = document.getElementById('filterSupplier').value;
    const asal = document.getElementById('filterAsal').value;
    const q = document.getElementById('searchKeyword').value;

    const tbody = document.getElementById('claimTableBody');
    tbody.innerHTML = '<tr><td colspan="12" class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin me-1"></i> Memuat data claim...</td></tr>';

    fetch(`<?= site_url('claim_material/ajax_claim_list') ?>?supplier=${encodeURIComponent(supplier)}&asal=${encodeURIComponent(asal)}&q=${encodeURIComponent(q)}`)
      .then(res => res.json())
      .then(res => {
        if (!res.status) {
          tbody.innerHTML = '<tr><td colspan="12" class="text-center py-4 text-danger">Gagal memuat data</td></tr>';
          return;
        }

        claimsData = res.data || [];
        updateStats(res.stats);
        renderTable();
      })
      .catch(err => {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="12" class="text-center py-4 text-danger">Terjadi kesalahan koneksi</td></tr>';
      });
  }

  function updateStats(stats) {
    if (!stats) return;
    document.getElementById('statBelumEmail').textContent = stats.belum_email || 0;
    document.getElementById('statMenunggu').textContent = stats.menunggu_decision || 0;
    document.getElementById('statPutus').textContent = stats.sudah_diputuskan || 0;
  }

  function renderTable() {
    const tbody = document.getElementById('claimTableBody');
    if (!claimsData.length) {
      tbody.innerHTML = '<tr><td colspan="12" class="text-center py-4 text-muted">Belum ada data claim material.</td></tr>';
      document.getElementById('btnProcessSelected').disabled = true;
      document.getElementById('selCount').textContent = '0';
      return;
    }

    let html = '';
    claimsData.forEach((c, idx) => {
      const hasDec = (c.decisions && c.decisions.length > 0);
      const isChecked = selectedClaimIds.has(String(c.id));
      const checkboxHtml = hasDec ? '' : `<input type="checkbox" class="claim-check" data-id="${c.id}" data-sup="${encodeURIComponent(c.id_suplier)}" ${isChecked ? 'checked' : ''}>`;

      html += `
        <tr>
          <td class="text-center">${checkboxHtml}</td>
          <td>${idx + 1}</td>
          <td><strong>${c.nama_supplier || '-'}</strong></td>
          <td>${c.nama_material || '-'}</td>
          <td><code>${c.no_coil_packing_list || '-'}</code></td>
          <td>${c.no_po || '-'}</td>
          <td class="text-end fw-bold">${formatNum(c.berat_kg, 2)}</td>
          <td><span class="text-danger fw-semibold">${c.problem || '-'}</span></td>
          <td><span class="badge bg-light text-dark border">${c.asal || '-'}</span></td>
          <td><span class="cl-badge ${getBadgeClass(c.status_claim)}">${c.status_claim}</span></td>
          <td>${renderProgress(c)}</td>
          <td class="text-center">
            <div class="d-flex flex-column gap-1">
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-info py-0 px-2" onclick="openEmailModal(${c.id})" title="Kirim Email Keluhan">
                  <i class="fa fa-envelope"></i> Email
                </button>
                <button type="button" class="btn ${hasDec ? 'btn-outline-secondary' : 'btn-primary'} py-0 px-2" onclick="openDecisionModal([${c.id}])">
                  <i class="fa fa-gavel"></i> ${hasDec ? 'View Dec' : 'Decide'}
                </button>
              </div>
              <a href="javascript:void(0)" class="small text-decoration-none" onclick="openAlasanModal(${c.id})">
                <i class="fa fa-paperclip me-1"></i> Bukti (${c.total_bukti || 0})
              </a>
              ${hasDec ? `<a href="javascript:void(0)" class="small text-success text-decoration-none" onclick="openJurnalModal(${c.id})"><i class="fa fa-book me-1"></i> Jurnal</a>` : ''}
            </div>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
    attachCheckboxEvents();
  }

  function attachCheckboxEvents() {
    const checks = document.querySelectorAll('.claim-check');
    checks.forEach(chk => {
      chk.addEventListener('change', function() {
        const id = this.dataset.id;
        if (this.checked) {
          // Validasi: hanya boleh memilih claim dari satu supplier yang sama (FR-78)
          if (selectedClaimIds.size > 0) {
            const firstId = [...selectedClaimIds][0];
            const firstClaim = claimsData.find(x => String(x.id) === String(firstId));
            const currentClaim = claimsData.find(x => String(x.id) === String(id));
            if (firstClaim && currentClaim && firstClaim.id_suplier !== currentClaim.id_suplier) {
              Swal.fire({
                icon: 'warning',
                title: 'Supplier Berbeda',
                text: 'Process Decision bersamaan hanya dapat dilakukan untuk claim dari satu supplier yang sama (' + firstClaim.nama_supplier + ').'
              });
              this.checked = false;
              return;
            }
          }
          selectedClaimIds.add(id);
        } else {
          selectedClaimIds.delete(id);
        }
        updateSelectedButton();
      });
    });

    document.getElementById('checkAllClaims').addEventListener('change', function() {
      const isChecked = this.checked;
      let firstSup = null;

      checks.forEach(chk => {
        const id = chk.dataset.id;
        const currentClaim = claimsData.find(x => String(x.id) === String(id));
        if (isChecked) {
          if (!firstSup) {
            firstSup = currentClaim.id_suplier;
            chk.checked = true;
            selectedClaimIds.add(id);
          } else if (currentClaim.id_suplier === firstSup) {
            chk.checked = true;
            selectedClaimIds.add(id);
          } else {
            chk.checked = false;
          }
        } else {
          chk.checked = false;
          selectedClaimIds.delete(id);
        }
      });
      updateSelectedButton();
    });
  }

  function updateSelectedButton() {
    const count = selectedClaimIds.size;
    document.getElementById('selCount').textContent = count;
    document.getElementById('btnProcessSelected').disabled = (count === 0);
  }

  document.getElementById('btnProcessSelected').addEventListener('click', function() {
    if (selectedClaimIds.size === 0) return;
    openDecisionModal([...selectedClaimIds]);
  });

  // Filter events
  document.getElementById('filterSupplier').addEventListener('change', loadClaims);
  document.getElementById('filterAsal').addEventListener('change', loadClaims);
  document.getElementById('searchKeyword').addEventListener('input', debounce(loadClaims, 400));

  function debounce(fn, delay) {
    let timer = null;
    return function(...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  // ==============================================================
  // LOGIC MODAL EMAIL
  // ==============================================================
  function openEmailModal(claimId) {
    const c = claimsData.find(x => String(x.id) === String(claimId));
    if (!c) return;

    document.getElementById('emailClaimId').value = c.id;
    document.getElementById('emNoClaim').textContent = c.no_claim;
    document.getElementById('emSupplier').textContent = c.nama_supplier;
    document.getElementById('emMaterial').textContent = `${c.nama_material} (${c.no_coil_packing_list})`;
    document.getElementById('emPoKg').textContent = `PO: ${c.no_po} | ${formatNum(c.berat_kg, 2)} Kg`;
    document.getElementById('emProblem').textContent = c.problem || '-';

    document.getElementById('emailTo').value = c.supplier_email || `claim@${(c.nama_supplier || 'supplier').toLowerCase().replace(/[^a-z0-9]/g, '')}.example`;
    const companyCode = <?= json_encode(get_company_code()) ?>;
    const companyNamePt = <?= json_encode(get_company_name(true, false)) ?>;
    document.getElementById('emailSubject').value = `[CLAIM MATERIAL] Ketidaksesuaian Coil ${c.no_coil_packing_list} - ${c.no_claim} - PT ${companyCode}`;
    document.getElementById('emailBody').value = `Kepada Yth. Rekan Supplier ${c.nama_supplier},\n\n`
      + `Melalui surat ini kami sampaikan klaim ketidaksesuaian material coil dengan rincian sbb:\n`
      + `- No. Claim : ${c.no_claim}\n`
      + `- No. PO : ${c.no_po}\n`
      + `- No. Coil : ${c.no_coil_packing_list}\n`
      + `- Material : ${c.nama_material}\n`
      + `- Berat : ${formatNum(c.berat_kg, 2)} Kg\n`
      + `- Problem : ${c.problem}\n`
      + `- Alasan Produksi : ${c.alasan_produksi || '-'}\n\n`
      + `Bersama ini kami lampirkan foto dan bukti pendukung dari tim produksi untuk ditindaklanjuti. Mohon kesepakatan penanganan kompensasi atau retur.\n\n`
      + `Hormat Kami,\n${companyNamePt} (Purchasing)`;

    const buktiBox = document.getElementById('emailBuktiList');
    if (c.bukti && c.bukti.length > 0) {
      buktiBox.innerHTML = c.bukti.map(b => `<div>📎 <b>${b.file_original_name}</b> <span class="text-muted">(${formatBytes(b.file_size)})</span></div>`).join('');
    } else {
      buktiBox.innerHTML = '<span class="text-muted small">Tidak ada berkas bukti terlampir</span>';
    }

    modalEmailEl.show();
  }

  document.getElementById('btnSubmitEmail').addEventListener('click', function() {
    const claimId = document.getElementById('emailClaimId').value;
    const to = document.getElementById('emailTo').value.trim();
    const subject = document.getElementById('emailSubject').value.trim();
    const body = document.getElementById('emailBody').value.trim();

    if (!to || !subject || !body) {
      Swal.fire('Peringatan', 'Harap lengkapi email tujuan, subjek, dan isi pesan', 'warning');
      return;
    }

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Mengirim...';

    const formData = new FormData();
    formData.append('claim_id', claimId);
    formData.append('email_to', to);
    formData.append('email_subject', subject);
    formData.append('email_body', body);

    fetch('<?= site_url('claim_material/send_email') ?>', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(res => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Kirim Email Keluhan';
      if (res.status) {
        modalEmailEl.hide();
        Swal.fire('Berhasil', res.message, 'success');
        loadClaims();
      } else {
        Swal.fire('Gagal', res.message, 'error');
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Kirim Email Keluhan';
      Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    });
  });

  // ==============================================================
  // LOGIC MODAL PROCESS DECISION (FR-78)
  // ==============================================================
  let currentDecClaimIds = [];

  function openDecisionModal(ids) {
    currentDecClaimIds = ids;
    const targetClaims = claimsData.filter(x => ids.map(String).includes(String(x.id)));
    if (!targetClaims.length) return;

    document.getElementById('decKesepakatan').value = targetClaims[0].kesepakatan_supplier || '';

    const container = document.getElementById('decisionCardsContainer');
    let html = '';

    targetClaims.forEach((c, idx) => {
      const isValas = (c.mata_uang_po && c.mata_uang_po !== 'IDR');
      const cur = c.mata_uang_po || 'IDR';
      const existingDec = c.decisions || [];

      html += `
        <div class="cl-dec-box" data-cid="${c.id}">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6>Baris Claim #${idx + 1}: ${c.no_claim} &mdash; ${c.nama_material} (${c.no_coil_packing_list})</h6>
            <span class="badge bg-primary">Total Berat: ${formatNum(c.berat_kg, 2)} Kg</span>
          </div>

          <div class="row g-2 mb-3 small text-muted">
            <div class="col-md-3">Supplier: <b>${c.nama_supplier}</b></div>
            <div class="col-md-3">No. PO: <b>${c.no_po}</b></div>
            <div class="col-md-3">Harga Beli/Kg: <b>Rp ${formatNum(c.harga_beli_per_kg)}</b></div>
            <div class="col-md-3">Mata Uang PO: <b>${cur}</b></div>
          </div>

          ${isValas ? `
            <div class="row g-2 mb-3 p-2 bg-warning bg-opacity-10 border border-warning rounded align-items-center">
              <div class="col-md-4">
                <label class="form-label fw-bold mb-0">Kurs Saat Claim (${cur} ke IDR) <span class="text-danger">*</span></label>
              </div>
              <div class="col-md-4">
                <input type="number" step="any" class="form-control form-control-sm claim-kurs" data-cid="${c.id}" value="${c.kurs_default || 16000}" required>
              </div>
              <div class="col-md-4 small text-muted">Wajib diisi untuk PO valas non-IDR (FR-78).</div>
            </div>
          ` : `<input type="hidden" class="claim-kurs" data-cid="${c.id}" value="1">`}

          <!-- LINES JALUR KEPUTUSAN -->
          <div class="dec-lines-container" id="linesContainer_${c.id}">
            <!-- Baris jalur dimasukkan di sini -->
          </div>

          <div class="d-flex justify-content-between align-items-center mt-2">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addDecisionLine(${c.id}, ${c.berat_kg}, ${c.harga_beli_per_kg})">
              <i class="fa fa-plus me-1"></i> Tambah Jalur Keputusan
            </button>
            <div class="small fw-bold">
              Total Alokasi Berat: <span id="totDecKg_${c.id}">0.00</span> / ${formatNum(c.berat_kg, 2)} Kg
              <span id="kgWarning_${c.id}" class="text-danger ms-2 d-none"><i class="fa fa-exclamation-triangle"></i> Melebihi berat claim!</span>
            </div>
          </div>
        </div>
      `;
    });

    container.innerHTML = html;

    // Masukkan baris jalur awal
    targetClaims.forEach(c => {
      if (c.decisions && c.decisions.length > 0) {
        c.decisions.forEach(d => {
          addDecisionLine(c.id, c.berat_kg, c.harga_beli_per_kg, d);
        });
      } else {
        addDecisionLine(c.id, c.berat_kg, c.harga_beli_per_kg, {
          jalur: 'Kompensasi',
          aksi: 'Debit Note',
          berat_kg: c.berat_kg,
          nilai_debit_note: (c.berat_kg * (c.price_per_unit_po || c.harga_beli_per_kg)),
        });
      }
    });

    modalDecisionEl.show();
  }

  function addDecisionLine(cid, maxKg, hargaBeli, initialData = null) {
    const container = document.getElementById(`linesContainer_${cid}`);
    const lineIdx = container.querySelectorAll('.dec-line-row').length;

    const jalur = initialData ? initialData.jalur : 'Kompensasi';
    const aksi = initialData ? initialData.aksi : 'Debit Note';
    const kg = initialData ? initialData.berat_kg : maxKg;
    const nilaiDn = (initialData && initialData.nilai_debit_note != null) ? initialData.nilai_debit_note : (kg * hargaBeli);
    const nilaiKw = (initialData && initialData.nilai_material_kw != null) ? initialData.nilai_material_kw : (kg * hargaBeli * 0.7);
    const ket = initialData ? (initialData.keterangan || '') : '';

    const row = document.createElement('div');
    row.className = 'dec-line-row border rounded p-2 mb-2 bg-white';
    row.innerHTML = `
      <div class="row g-2 align-items-center">
        <div class="col-md-2">
          <label class="form-label small fw-bold mb-1">Jalur</label>
          <select class="form-select form-select-sm sel-jalur" onchange="onJalurChange(this, ${cid}, ${hargaBeli})">
            <option value="Kompensasi" ${jalur === 'Kompensasi' ? 'selected' : ''}>Kompensasi</option>
            <option value="Retur" ${jalur === 'Retur' ? 'selected' : ''}>Retur</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold mb-1">Aksi</label>
          <select class="form-select form-select-sm sel-aksi" onchange="onAksiChange(this, ${cid}, ${hargaBeli})">
            <!-- options diatur dinamis -->
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold mb-1">Berat (Kg)</label>
          <input type="number" step="0.01" class="form-control form-control-sm inp-kg" value="${kg}" oninput="calcDecTotal(${cid}, ${hargaBeli})" required>
        </div>
        <div class="col-md-3 val-box">
          <!-- Nilai DN atau Nilai KW -->
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold mb-1">Keterangan</label>
          <input type="text" class="form-control form-control-sm inp-ket" value="${ket}" placeholder="Catatan...">
        </div>
        <div class="col-md-1 text-end">
          <label class="form-label small mb-1">&nbsp;</label>
          <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeDecisionLine(this, ${cid}, ${hargaBeli})">
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </div>
    `;

    container.appendChild(row);
    setupAksiOptions(row.querySelector('.sel-jalur'), aksi, nilaiDn, nilaiKw, kg, hargaBeli);
    calcDecTotal(cid, hargaBeli);
  }

  function setupAksiOptions(jalurEl, defaultAksi, nilaiDn, nilaiKw, kg, hargaBeli) {
    const row = jalurEl.closest('.dec-line-row');
    const aksiEl = row.querySelector('.sel-aksi');
    const valBox = row.querySelector('.val-box');
    const jalur = jalurEl.value;

    if (jalur === 'Kompensasi') {
      aksiEl.innerHTML = `
        <option value="Debit Note" ${defaultAksi === 'Debit Note' ? 'selected' : ''}>Debit Note</option>
        <option value="Lanjut Produksi" ${defaultAksi === 'Lanjut Produksi' ? 'selected' : ''}>Lanjut Produksi</option>
        <option value="Jual Material" ${defaultAksi === 'Jual Material' ? 'selected' : ''}>Jual Material</option>
      `;
    } else {
      aksiEl.innerHTML = `
        <option value="Retur" ${defaultAksi === 'Retur' ? 'selected' : ''}>Retur ke Supplier</option>
      `;
    }

    renderValBox(row, aksiEl.value, nilaiDn, nilaiKw, kg, hargaBeli);
  }

  function onJalurChange(el, cid, hargaBeli) {
    setupAksiOptions(el, null, null, null, 0, hargaBeli);
    calcDecTotal(cid, hargaBeli);
  }

  function onAksiChange(el, cid, hargaBeli) {
    const row = el.closest('.dec-line-row');
    const kg = parseFloat(row.querySelector('.inp-kg').value) || 0;
    renderValBox(row, el.value, kg * hargaBeli, kg * hargaBeli * 0.7, kg, hargaBeli);
    calcDecTotal(cid, hargaBeli);
  }

  function renderValBox(row, aksi, nilaiDn, nilaiKw, kg, hargaBeli) {
    const valBox = row.querySelector('.val-box');
    if (aksi === 'Debit Note') {
      valBox.innerHTML = `
        <label class="form-label small fw-bold mb-1">Nilai Debit Note (tanpa PPN)</label>
        <input type="number" step="0.01" class="form-control form-control-sm inp-nilai-dn" value="${nilaiDn != null ? nilaiDn : (kg * hargaBeli)}" required>
      `;
    } else if (aksi === 'Jual Material') {
      const totMaterial = kg * hargaBeli;
      valBox.innerHTML = `
        <label class="form-label small fw-bold mb-1">Nilai Material KW (Rp)</label>
        <input type="number" step="0.01" class="form-control form-control-sm inp-nilai-kw" value="${nilaiKw != null ? nilaiKw : (totMaterial * 0.7)}" required>
        <div class="small text-muted mt-1">Sisa diakui Biaya Scrap</div>
      `;
    } else if (aksi === 'Lanjut Produksi') {
      valBox.innerHTML = `
        <label class="form-label small fw-bold mb-1">Status Coil</label>
        <div class="badge bg-success mt-1 py-1 px-2">Release ke Produksi (tanpa jurnal)</div>
      `;
    } else {
      valBox.innerHTML = `
        <label class="form-label small fw-bold mb-1">Tindak Lanjut Retur</label>
        <div class="small text-muted">Akan masuk antrian SPK Re-Pack & Surat Jalan Retur</div>
      `;
    }
  }

  function removeDecisionLine(btn, cid, hargaBeli) {
    const container = document.getElementById(`linesContainer_${cid}`);
    if (container.querySelectorAll('.dec-line-row').length <= 1) {
      Swal.fire('Perhatian', 'Minimal harus ada 1 jalur keputusan per claim', 'warning');
      return;
    }
    btn.closest('.dec-line-row').remove();
    calcDecTotal(cid, hargaBeli);
  }

  function calcDecTotal(cid, hargaBeli) {
    const container = document.getElementById(`linesContainer_${cid}`);
    let totKg = 0;
    container.querySelectorAll('.inp-kg').forEach(el => {
      totKg += parseFloat(el.value) || 0;
    });

    const totSpan = document.getElementById(`totDecKg_${cid}`);
    const warnSpan = document.getElementById(`kgWarning_${cid}`);
    totSpan.textContent = formatNum(totKg, 2);

    const c = claimsData.find(x => String(x.id) === String(cid));
    const maxKg = c ? (parseFloat(c.berat_kg) || 0) : 0;

    if (totKg > maxKg + 0.001) {
      warnSpan.classList.remove('d-none');
    } else {
      warnSpan.classList.add('d-none');
    }
  }

  // Submit Decision
  document.getElementById('btnSubmitDecision').addEventListener('click', function() {
    const kesepakatan = document.getElementById('decKesepakatan').value;
    if (!kesepakatan) {
      Swal.fire('Peringatan', 'Harap pilih Kesepakatan dengan Supplier', 'warning');
      return;
    }

    const payloadDecisions = {};
    const payloadKurs = {};
    let hasError = false;
    let errorMsg = '';

    currentDecClaimIds.forEach(cid => {
      const c = claimsData.find(x => String(x.id) === String(cid));
      const box = document.querySelector(`.cl-dec-box[data-cid="${cid}"]`);
      if (!box) return;

      const kursEl = box.querySelector('.claim-kurs');
      payloadKurs[cid] = kursEl ? (parseFloat(kursEl.value) || 1.0) : 1.0;

      const lines = [];
      let totKg = 0;

      box.querySelectorAll('.dec-line-row').forEach(row => {
        const jalur = row.querySelector('.sel-jalur').value;
        const aksi = row.querySelector('.sel-aksi').value;
        const kg = parseFloat(row.querySelector('.inp-kg').value) || 0;
        const ket = row.querySelector('.inp-ket').value;

        if (kg <= 0) {
          hasError = true;
          errorMsg = `Berat (Kg) pada claim ${c ? c.no_claim : cid} harus lebih dari 0`;
          return;
        }

        totKg += kg;

        let nilaiDn = null;
        let nilaiKw = null;
        let nilaiScrap = null;

        if (aksi === 'Debit Note') {
          const dnEl = row.querySelector('.inp-nilai-dn');
          nilaiDn = dnEl ? parseFloat(dnEl.value) : 0;
        } else if (aksi === 'Jual Material') {
          const kwEl = row.querySelector('.inp-nilai-kw');
          nilaiKw = kwEl ? parseFloat(kwEl.value) : 0;
          const hargaBeli = c ? (parseFloat(c.harga_beli_per_kg) || 0) : 0;
          nilaiScrap = Math.max(0, (kg * hargaBeli) - nilaiKw);
        }

        lines.push({
          jalur: jalur,
          aksi: aksi,
          berat_kg: kg,
          nilai_debit_note: nilaiDn,
          nilai_material_kw: nilaiKw,
          nilai_biaya_scrap: nilaiScrap,
          keterangan: ket
        });
      });

      const maxKg = c ? (parseFloat(c.berat_kg) || 0) : 0;
      if (totKg > maxKg + 0.001) {
        hasError = true;
        errorMsg = `Total berat alokasi (${formatNum(totKg, 2)} Kg) melebihi berat claim ${c ? c.no_claim : cid} (${formatNum(maxKg, 2)} Kg)`;
        return;
      }

      payloadDecisions[cid] = lines;
    });

    if (hasError) {
      Swal.fire('Validasi Gagal', errorMsg, 'warning');
      return;
    }

    Swal.fire({
      title: 'Konfirmasi Keputusan',
      text: `Simpan keputusan untuk ${currentDecClaimIds.length} claim dengan kesepakatan "${kesepakatan}"?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Simpan',
      cancelButtonText: 'Batal'
    }).then(res => {
      if (!res.isConfirmed) return;

      const btn = document.getElementById('btnSubmitDecision');
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...';

      const dataPost = {
        claim_ids: currentDecClaimIds,
        kesepakatan: kesepakatan,
        decisions: payloadDecisions,
        kurs: payloadKurs
      };

      fetch('<?= site_url('claim_material/save_decision') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          'claim_ids[]': currentDecClaimIds,
          kesepakatan: kesepakatan,
          decisions: JSON.stringify(payloadDecisions),
          kurs: JSON.stringify(payloadKurs)
        })
      })
      .then(r => r.json())
      .then(resp => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check me-1"></i> Simpan Keputusan Claim';
        if (resp.status) {
          modalDecisionEl.hide();
          selectedClaimIds.clear();
          updateSelectedButton();
          Swal.fire('Berhasil', resp.message, 'success');
          loadClaims();
        } else {
          Swal.fire('Gagal', resp.message, 'error');
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check me-1"></i> Simpan Keputusan Claim';
        Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan keputusan', 'error');
      });
    });
  });

  // ==============================================================
  // LOGIC MODAL VIEW ALASAN & BUKTI (FR-94)
  // ==============================================================
  function openAlasanModal(claimId) {
    const c = claimsData.find(x => String(x.id) === String(claimId));
    if (!c) return;

    document.getElementById('viewNoClaim').textContent = c.no_claim;
    document.getElementById('viewSupplier').textContent = c.nama_supplier;
    document.getElementById('viewMaterial').textContent = c.nama_material;
    document.getElementById('viewCoil').textContent = c.no_coil_packing_list;
    document.getElementById('viewBerat').textContent = `${formatNum(c.berat_kg, 2)} Kg`;
    document.getElementById('viewProblem').textContent = c.problem || '-';
    document.getElementById('viewAlasanText').textContent = c.alasan_produksi || '-';

    const bBox = document.getElementById('viewBuktiContainer');
    if (!c.bukti || c.bukti.length === 0) {
      bBox.innerHTML = '<div class="text-muted small">Tidak ada lampiran berkas bukti dari tim produksi.</div>';
    } else {
      let bHtml = '';
      c.bukti.forEach(b => {
        const url = `<?= base_url('uploads/claim_material/') ?>${b.file_hash_name}`;
        const isImg = /\.(jpe?g|png|gif|webp)$/i.test(b.file_original_name);
        const isPdf = /\.pdf$/i.test(b.file_original_name);
        const isVid = /\.(mp4|webm|mov)$/i.test(b.file_original_name);

        bHtml += `
          <div class="cl-bk-item">
            <div class="d-flex justify-content-between align-items-center">
              <span>📎 <strong>${b.file_original_name}</strong> <span class="text-muted">(${formatBytes(b.file_size)})</span></span>
              <div>
                <a href="${url}" class="btn btn-outline-primary btn-sm py-0 px-2" target="_blank" rel="noopener">Buka di Tab Baru</a>
                <a href="${url}" class="btn btn-outline-secondary btn-sm py-0 px-2" download="${b.file_original_name}">Unduh</a>
              </div>
            </div>
            ${isImg ? `<img src="${url}" alt="${b.file_original_name}">` : ''}
            ${isPdf ? `<iframe src="${url}"></iframe>` : ''}
            ${isVid ? `<video src="${url}" controls class="w-100 mt-2" style="max-height: 260px;"></video>` : ''}
          </div>
        `;
      });
      bBox.innerHTML = bHtml;
    }

    modalAlasanEl.show();
  }

  // ==============================================================
  // LOGIC MODAL JURNAL (FR-93)
  // ==============================================================
  function openJurnalModal(claimId) {
    const c = claimsData.find(x => String(x.id) === String(claimId));
    if (!c) return;

    document.getElementById('jrNoClaim').textContent = c.no_claim;
    document.getElementById('jrSupplier').textContent = c.nama_supplier;
    document.getElementById('jrMaterial').textContent = `${c.nama_material} (${c.no_coil_packing_list})`;
    document.getElementById('jrStatus').textContent = c.status_claim;

    const jBox = document.getElementById('jrEventsContainer');
    jBox.innerHTML = '<div class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin"></i> Memuat jurnal...</div>';

    fetch(`<?= site_url('claim_material/ajax_jurnal') ?>/${claimId}`)
      .then(r => r.json())
      .then(res => {
        if (!res.status || !res.journals || !res.journals.length) {
          jBox.innerHTML = '<div class="text-muted small">Belum ada peristiwa jurnal yang terbentuk untuk claim ini.</div>';
          return;
        }

        let jHtml = '';
        res.journals.forEach(j => {
          let dbTotal = 0;
          let crTotal = 0;
          let linesHtml = '';

          j.lines.forEach(l => {
            dbTotal += (l[1] || 0);
            crTotal += (l[2] || 0);
            linesHtml += `
              <tr>
                <td>${l[0]}</td>
                <td class="text-end">${l[1] ? formatNum(l[1]) : '-'}</td>
                <td class="text-end">${l[2] ? formatNum(l[2]) : '-'}</td>
              </tr>
            `;
          });

          const isBalanced = Math.abs(dbTotal - crTotal) < 0.01;

          jHtml += `
            <div class="card mb-3 border">
              <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold">${j.title}</span>
                <span class="badge ${j.posted ? 'bg-success' : 'bg-warning text-dark'}">${j.posted ? 'Terposting ke GL' : 'Proyeksi (Belum Terbit)'}</span>
              </div>
              <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                  <thead class="table-light">
                    <tr>
                      <th>Akun (COA)</th>
                      <th class="text-end" width="130">Debit (Rp)</th>
                      <th class="text-end" width="130">Kredit (Rp)</th>
                    </tr>
                  </thead>
                  <tbody>${linesHtml}</tbody>
                  <tfoot class="table-light fw-bold">
                    <tr>
                      <td>Total ${isBalanced ? '<span class="text-success ms-2">&#10003; Seimbang</span>' : '<span class="text-danger ms-2">Tidak Seimbang</span>'}</td>
                      <td class="text-end">${formatNum(dbTotal)}</td>
                      <td class="text-end">${formatNum(crTotal)}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          `;
        });

        jBox.innerHTML = jHtml;
      })
      .catch(err => {
        jBox.innerHTML = '<div class="text-danger small">Gagal memuat data jurnal.</div>';
      });

    modalJurnalEl.show();
  }

  function formatBytes(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
  }

  // Load awal & auto-filter dari parameter URL (?q=...)
  document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const qParam = urlParams.get('q');
    if (qParam) {
      const searchEl = document.getElementById('searchKeyword');
      if (searchEl) searchEl.value = qParam;
    }
    loadClaims();
  });
</script>

