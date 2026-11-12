<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<style>
  :root {
    --cl-primary: #0d6efd;
    --cl-text: #1e293b;
    --cl-muted: #64748b;
    --cl-border: #e2e8f0;
  }
  .cl-page-header {
    background: #fff;
    border-radius: 10px;
    padding: 18px 24px;
    margin-bottom: 20px;
    border: 1px solid var(--cl-border);
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  }
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
  }
  .cl-stat-card .val {
    font-size: 24px;
    font-weight: 700;
    color: var(--cl-text);
  }
  .cl-stat-card .lbl {
    font-size: 12.5px;
    font-weight: 500;
    color: var(--cl-muted);
    margin-top: 4px;
  }
  .cl-nav-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 1px solid var(--cl-border);
    margin-bottom: 16px;
  }
  .cl-nav-tabs .tab-link {
    padding: 8px 18px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--cl-muted);
    text-decoration: none;
    border-bottom: 2px solid transparent;
  }
  .cl-nav-tabs .tab-link.active {
    color: var(--cl-primary);
    border-bottom: 2px solid var(--cl-primary);
    background: #fff;
  }
  .cl-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid var(--cl-border);
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  }
  .cl-table {
    width: 100%;
    font-size: 13px;
  }
  .cl-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    padding: 10px 12px;
    border-bottom: 1px solid var(--cl-border);
  }
  .cl-table td {
    padding: 10px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
  }
  .badge-hold { background: #fee2e2; color: #991b1b; }
  .badge-release { background: #dcfce7; color: #166534; }
  .badge-slitting { background: #e0f2fe; color: #075985; }
  .badge-retur { background: #fef9c3; color: #854d0e; }
  .badge-komp { background: #ede9fe; color: #6d28d9; }
</style>

<div class="container-fluid px-3 py-2">
  <div class="cl-page-header">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="h5 fw-bold mb-1">Gudang Coil &mdash; Material On Hold</h1>
        <p class="text-muted small mb-0">Tampungan material berstatus HOLD dari Laporan Produksi; tidak dijurnal sampai claim diputuskan Purchasing (FR-75, UC-N35).</p>
      </div>
      <div>
        <a href="<?= site_url('claim_material') ?>" class="btn btn-primary btn-sm">
          <i class="fa fa-envelope-o me-1"></i> Ke Daftar Claim Material
        </a>
      </div>
    </div>
  </div>

  <!-- KPI SUMMARY -->
  <div class="cl-summary-cards">
    <div class="cl-stat-card">
      <div class="val text-danger" id="statHoldCoils">0</div>
      <div class="lbl">Coil Berstatus Hold</div>
    </div>
    <div class="cl-stat-card">
      <div class="val text-primary" id="statHoldKg">0.00 Kg</div>
      <div class="lbl">Total Berat Hold</div>
    </div>
    <div class="cl-stat-card">
      <div class="val text-success" id="statDecided">0</div>
      <div class="lbl">Sudah Diputuskan (Claim)</div>
    </div>
  </div>

  <!-- NAVIGATION TABS -->
  <div class="cl-nav-tabs">
    <a href="<?= site_url('warehouse') ?>" class="tab-link">Stok Gudang Coil (Existing)</a>
    <a href="javascript:void(0)" class="tab-link active">Material On Hold (Tampungan)</a>
  </div>

  <!-- CARD TABLE -->
  <div class="cl-card p-3">
    <div class="table-responsive">
      <table class="table cl-table">
        <thead>
          <tr>
            <th width="40">No</th>
            <th>Nama Supplier</th>
            <th>Nama Material</th>
            <th>No. Coil Packing List</th>
            <th>No. PO</th>
            <th class="text-end">Berat (Kg)</th>
            <th>Tgl Hold</th>
            <th>Alasan Produksi</th>
            <th>Status Material</th>
            <th width="110" class="text-center">Action</th>
          </tr>
        </thead>
        <tbody id="holdTableBody">
          <tr>
            <td colspan="10" class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin me-1"></i> Memuat material on hold...</td>
          </tr>
        </tbody>
      </table>
    </div>
    <p class="text-muted small mt-3 mb-0">
      * Status berubah otomatis mengikuti keputusan yang ditetapkan di Daftar Claim Material (Lanjut Produksi &rarr; Release; Retur &rarr; Dalam Retur; Jual &rarr; Dijual Gudang Slitting).
    </p>
  </div>
</div>

<script>
  function formatNum(n, d = 2) {
    return Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
  }

  function getStatusBadge(st) {
    if (st === 'Dapat Dipakai (Release)') return '<span class="badge badge-release">Dapat Dipakai (Release)</span>';
    if (st === 'Dijual (Gudang Slitting)') return '<span class="badge badge-slitting">Dijual (Gudang Slitting)</span>';
    if (st === 'Dalam Retur') return '<span class="badge badge-retur">Dalam Retur</span>';
    if (st === 'Dikompensasi') return '<span class="badge badge-komp">Dikompensasi</span>';
    return '<span class="badge badge-hold">Hold</span>';
  }

  function loadHoldMaterials() {
    fetch('<?= site_url('claim_material/ajax_on_hold_list') ?>')
      .then(r => r.json())
      .then(res => {
        const tbody = document.getElementById('holdTableBody');
        if (!res.status || !res.data || !res.data.length) {
          tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada material coil berstatus On Hold.</td></tr>';
          document.getElementById('statHoldCoils').textContent = '0';
          document.getElementById('statHoldKg').textContent = '0.00 Kg';
          document.getElementById('statDecided').textContent = '0';
          return;
        }

        const data = res.data;
        let holdCount = 0;
        let holdKg = 0;
        let decidedCount = 0;

        let html = '';
        data.forEach((r, idx) => {
          const isHold = (r.status_material === 'Hold');
          if (isHold) {
            holdCount++;
            holdKg += (parseFloat(r.berat_kg) || 0);
          } else {
            decidedCount++;
          }

          html += `
            <tr>
              <td>${idx + 1}</td>
              <td><strong>${r.nama_supplier || '-'}</strong></td>
              <td>${r.nama_material || '-'}</td>
              <td><code>${r.no_coil_packing_list || '-'}</code></td>
              <td>${r.no_po || '-'}</td>
              <td class="text-end fw-bold">${formatNum(r.berat_kg, 2)}</td>
              <td>${r.tgl_produksi || '-'}</td>
              <td><span class="text-danger fw-semibold">${r.alasan_produksi || '-'}</span></td>
              <td>${getStatusBadge(r.status_material)}</td>
              <td class="text-center">
                <a href="<?= site_url('claim_material') ?>" class="btn btn-outline-info btn-sm py-0 px-2">Lihat Claim</a>
              </td>
            </tr>
          `;
        });

        tbody.innerHTML = html;
        document.getElementById('statHoldCoils').textContent = holdCount;
        document.getElementById('statHoldKg').textContent = `${formatNum(holdKg, 2)} Kg`;
        document.getElementById('statDecided').textContent = decidedCount;
      })
      .catch(err => {
        document.getElementById('holdTableBody').innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Gagal memuat data</td></tr>';
      });
  }

  document.addEventListener('DOMContentLoaded', loadHoldMaterials);
</script>

