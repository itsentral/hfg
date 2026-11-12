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
</style>

<div class="container-fluid px-3 py-2">
  <div class="cl-page-header">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="h5 fw-bold mb-1">Material Dijual &mdash; Gudang Slitting</h1>
        <p class="text-muted small mb-0">Daftar baca material hasil claim yang diputuskan <b>Jual Material</b> (Inventory Material Khusus Dijual, FR-81, FR-96).</p>
      </div>
      <div>
        <a href="<?= site_url('claim_material') ?>" class="btn btn-outline-primary btn-sm">
          <i class="fa fa-envelope-o me-1"></i> Ke Daftar Claim Material
        </a>
      </div>
    </div>
  </div>

  <!-- KPI SUMMARY -->
  <div class="cl-summary-cards">
    <div class="cl-stat-card">
      <div class="val text-primary" id="statSlitRows">0</div>
      <div class="lbl">Baris Material Khusus Dijual</div>
    </div>
    <div class="cl-stat-card">
      <div class="val text-dark" id="statSlitKg">0.00 Kg</div>
      <div class="lbl">Total Berat Material</div>
    </div>
    <div class="cl-stat-card">
      <div class="val text-success" id="statSlitNilai">Rp 0</div>
      <div class="lbl">Total Nilai Material</div>
    </div>
  </div>

  <!-- CARD TABLE -->
  <div class="cl-card p-3">
    <div class="table-responsive">
      <table class="table cl-table">
        <thead>
          <tr>
            <th width="40">No</th>
            <th>Tgl Masuk</th>
            <th>No. Claim</th>
            <th>Nama Supplier</th>
            <th>Nama Material</th>
            <th>No. Coil Packing List</th>
            <th class="text-end">Berat (Kg)</th>
            <th class="text-end">Nilai Material (Rp)</th>
            <th class="text-end">Nilai Material KW (Rp)</th>
            <th class="text-end">Nilai Biaya Scrap (Rp)</th>
          </tr>
        </thead>
        <tbody id="slitTableBody">
          <tr>
            <td colspan="10" class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin me-1"></i> Memuat data gudang slitting...</td>
          </tr>
        </tbody>
      </table>
    </div>
    <p class="text-muted small mt-3 mb-0">
      * Catatan: Halaman baca saja. Nilai Material = Berat &times; Harga Beli/Kg; Nilai Material KW diakui sebagai inventory, selisihnya diakui sebagai kerugian / Biaya Scrap (FR-81).
    </p>
  </div>
</div>

<script>
  function formatNum(n, d = 2) {
    return Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
  }

  function loadSlittingMaterials() {
    fetch('<?= site_url('claim_material/ajax_slitting_list') ?>')
      .then(r => r.json())
      .then(res => {
        const tbody = document.getElementById('slitTableBody');
        if (!res.status || !res.data || !res.data.length) {
          tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">Belum ada material dijual. Baris muncul otomatis saat Process Decision memilih Kompensasi &rarr; Jual Material.</td></tr>';
          document.getElementById('statSlitRows').textContent = '0';
          document.getElementById('statSlitKg').textContent = '0.00 Kg';
          document.getElementById('statSlitNilai').textContent = 'Rp 0';
          return;
        }

        const data = res.data;
        let totKg = 0;
        let totNilai = 0;
        let html = '';

        data.forEach((r, idx) => {
          const kg = parseFloat(r.berat_kg) || 0;
          const val = parseFloat(r.nilai_material) || 0;
          totKg += kg;
          totNilai += val;

          html += `
            <tr>
              <td>${idx + 1}</td>
              <td>${r.tgl_masuk || '-'}</td>
              <td><code>${r.no_claim || '-'}</code></td>
              <td><strong>${r.nama_supplier || '-'}</strong></td>
              <td>${r.nama_material || '-'}</td>
              <td><code>${r.no_coil_packing_list || '-'}</code></td>
              <td class="text-end fw-bold">${formatNum(kg, 2)}</td>
              <td class="text-end">${formatNum(val, 2)}</td>
              <td class="text-end text-success">${formatNum(r.nilai_material_kw, 2)}</td>
              <td class="text-end text-danger">${formatNum(r.nilai_biaya_scrap, 2)}</td>
            </tr>
          `;
        });

        tbody.innerHTML = html;
        document.getElementById('statSlitRows').textContent = data.length;
        document.getElementById('statSlitKg').textContent = `${formatNum(totKg, 2)} Kg`;
        document.getElementById('statSlitNilai').textContent = `Rp ${formatNum(totNilai, 2)}`;
      })
      .catch(err => {
        document.getElementById('slitTableBody').innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Gagal memuat data</td></tr>';
      });
  }

  document.addEventListener('DOMContentLoaded', loadSlittingMaterials);
</script>

