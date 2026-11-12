<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
  .pr-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    margin-bottom: 22px;
  }
  .pr-card-header {
    background: #fafbfd;
    padding: 14px 20px;
    border-bottom: 1px solid #e2e8f0;
  }
  .pr-card-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
  }
  .tbl-custom {
    width: 100%;
    font-size: 13px;
  }
  .tbl-custom th {
    background: #f8fafc;
    color: #475569;
    padding: 8px 12px;
    border-bottom: 1px solid #e2e8f0;
  }
  .tbl-custom td {
    padding: 8px 12px;
    border-bottom: 1px solid #f1f5f9;
  }
</style>

<div class="container-fluid p-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h4 fw-bold mb-1">Detail Laporan Produksi</h1>
      <p class="text-muted small mb-0">Nomor SPK: <strong><?= htmlspecialchars($report['spk_header']['spk_no'] ?? '-'); ?></strong> &bull; Tgl Produksi: <?= !empty($report['header']['tgl_produksi']) ? date('d/m/Y', strtotime($report['header']['tgl_produksi'])) : '-'; ?></p>
    </div>
    <div class="d-flex gap-2">
      <span class="badge <?= ($report['header']['status_draft'] ?? 1) == 1 ? 'bg-warning text-dark' : 'bg-success'; ?> px-3 py-2 fs-6">
        <?= ($report['header']['status_draft'] ?? 1) == 1 ? 'Draft' : 'Submitted (Done)'; ?>
      </span>
      <?php if (($report['header']['status_draft'] ?? 1) == 0): ?>
        <a href="<?= site_url('production_report/hpp/' . ($report['header']['id'] ?? '')); ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
          <i class="fa fa-calculator me-1"></i> Laporan HPP &amp; Jurnal
        </a>
      <?php endif; ?>
      <a href="<?= site_url('production_report'); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    </div>
  </div>

  <!-- Header Info -->
  <div class="pr-card mb-3">
    <div class="pr-card-header">
      <h2 class="pr-card-title">Informasi Laporan &amp; Operator</h2>
    </div>
    <div class="p-3">
      <div class="row g-3">
        <div class="col-md-3">
          <small class="text-muted d-block">Mesin</small>
          <strong><?= htmlspecialchars($report['mesin']['nm_asset'] ?? $report['mesin']['nama_asset'] ?? '-'); ?> (<?= htmlspecialchars($report['mesin']['kd_asset'] ?? '-'); ?>)</strong>
        </div>
        <div class="col-md-3">
          <small class="text-muted d-block">Helper Name</small>
          <strong><?= htmlspecialchars($report['header']['employee_helper'] ?? '-'); ?></strong>
        </div>
        <div class="col-md-3">
          <small class="text-muted d-block">Setter Name</small>
          <strong><?= htmlspecialchars($report['header']['employee_setter'] ?? '-'); ?></strong>
        </div>
        <div class="col-md-3">
          <small class="text-muted d-block">Jam Kerja</small>
          <strong><?= htmlspecialchars($report['header']['start_time'] ?? '-'); ?> s/d <?= htmlspecialchars($report['header']['finished_time'] ?? '-'); ?></strong>
        </div>
      </div>
    </div>
  </div>

  <!-- Materials Used -->
  <div class="pr-card mb-3">
    <div class="pr-card-header">
      <h2 class="pr-card-title">Baby Coil Terpakai (Bahan Baku)</h2>
    </div>
    <div class="p-0 table-responsive">
      <table class="tbl-custom">
        <thead>
          <tr>
            <th>Sumber</th>
            <th>No Coil</th>
            <th>Nama Material</th>
            <th class="text-end">Nett Packing List (kg)</th>
            <th class="text-end">Gross (kg)</th>
            <th class="text-end">Total Meter</th>
            <th class="text-end">Kulit (kg)</th>
            <th class="text-end">Clamp (kg)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($report['materials'])): ?>
            <tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data material coil.</td></tr>
          <?php else: ?>
            <?php foreach ($report['materials'] as $m): ?>
              <?php
                $coil_code = $m['coil_code'] ?? $m['no_coil'] ?? '-';
                $nett = (float)($m['nett_weight_packing'] ?? $m['net_weight_packing_list'] ?? 0);
                $gross = (float)($m['gross_weight'] ?? $m['gross_weight_packing_list'] ?? 0);
                $meter = (float)($m['total_meter'] ?? 0);
                $kulit = (float)($m['berat_kulit'] ?? 0);
                $clamp = (float)($m['berat_clamp'] ?? 0);
              ?>
              <tr>
                <td><span class="badge bg-light text-dark border"><?= strtoupper($m['source_warehouse'] ?? '-'); ?></span></td>
                <td><strong><?= htmlspecialchars($coil_code); ?></strong></td>
                <td><?= htmlspecialchars($m['material_name'] ?? '-'); ?></td>
                <td class="text-end"><?= number_format($nett, 2, ',', '.'); ?></td>
                <td class="text-end"><?= number_format($gross, 2, ',', '.'); ?></td>
                <td class="text-end"><?= number_format($meter, 2, ',', '.'); ?></td>
                <td class="text-end"><?= number_format($kulit, 2, ',', '.'); ?></td>
                <td class="text-end"><?= number_format($clamp, 2, ',', '.'); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Items Produced -->
  <div class="pr-card mb-3">
    <div class="pr-card-header">
      <h2 class="pr-card-title">Hasil Produksi (KW 1, Stok Bebas, KW 2)</h2>
    </div>
    <div class="p-0 table-responsive">
      <table class="tbl-custom">
        <thead>
          <tr>
            <th>Kategori</th>
            <th>Nama Produk</th>
            <th>No Coil Asal</th>
            <th class="text-end">Qty</th>
            <th class="text-end">Berat Total (kg)</th>
            <th class="text-end">Berat/Pcs (kg)</th>
            <th class="text-end">Std/Pcs (kg)</th>
            <th class="text-end">% Selisih</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($report['items'])): ?>
            <tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data hasil produksi.</td></tr>
          <?php else: ?>
            <?php foreach ($report['items'] as $it): ?>
              <?php
                $cat = $it['category_type'] ?? $it['kategori'] ?? 'kw_1';
                $prod_name = !empty($it['nama_product_custom']) ? $it['nama_product_custom'] : (!empty($it['nama_produk']) ? $it['nama_produk'] : (!empty($it['nama_master_produk']) ? $it['nama_master_produk'] : '-'));
                $coil_origin = $it['source_material_coil'] ?? $it['kode_baby_coil'] ?? '-';
                $qty = (float)($it['qty'] ?? 0);
                $berat_tot = (float)($it['berat_total'] ?? 0);
                $berat_pcs = (float)($it['berat_per_pcs'] ?? 0);
                $berat_std = (float)($it['berat_standard'] ?? $it['berat_standard_pcs'] ?? 0);
                $selisih_pct = (float)($it['percentage_selisih'] ?? $it['selisih_persen'] ?? 0);
              ?>
              <tr>
                <td>
                  <span class="badge <?= in_array($cat, ['kw_1']) ? 'bg-primary' : (in_array($cat, ['stok_bebas']) ? 'bg-info text-dark' : 'bg-secondary'); ?>">
                    <?= strtoupper(str_replace('_', ' ', $cat)); ?>
                  </span>
                </td>
                <td><strong><?= htmlspecialchars($prod_name); ?></strong></td>
                <td><?= htmlspecialchars($coil_origin ?: '-'); ?></td>
                <td class="text-end"><?= number_format($qty, 0, ',', '.'); ?></td>
                <td class="text-end fw-bold"><?= number_format($berat_tot, 2, ',', '.'); ?></td>
                <td class="text-end"><?= number_format($berat_pcs, 2, ',', '.'); ?></td>
                <td class="text-end"><?= number_format($berat_std, 2, ',', '.'); ?></td>
                <td class="text-end <?= abs($selisih_pct) > 0.7 ? 'text-danger fw-bold' : 'text-success'; ?>">
                  <?= number_format($selisih_pct, 2, ',', '.'); ?> %
                </td>
                <td><?= htmlspecialchars($it['keterangan'] ?? '-'); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Scraps & Residuals -->
  <?php if (!empty($report['scraps'])): ?>
  <div class="pr-card mb-3">
    <div class="pr-card-header">
      <h2 class="pr-card-title">Komponen Scrap, Sisa, &amp; Hold Coil</h2>
    </div>
    <div class="p-0 table-responsive">
      <table class="tbl-custom">
        <thead>
          <tr>
            <th>Jenis Komponen</th>
            <th>No Coil Terkait</th>
            <th class="text-end">Berat Total (kg)</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($report['scraps'] as $sc): ?>
            <?php
              $sc_type = $sc['scrap_type'] ?? $sc['jenis_scrap'] ?? '-';
              $sc_coil = $sc['target_coil_code'] ?? $sc['kode_baby_coil'] ?? '-';
              $sc_berat = (float)($sc['berat_total'] ?? $sc['berat'] ?? 0);
            ?>
            <tr>
              <td><span class="badge bg-light text-secondary border"><?= strtoupper(str_replace('_', ' ', $sc_type)); ?></span></td>
              <td><?= htmlspecialchars($sc_coil ?: '-'); ?></td>
              <td class="text-end fw-bold"><?= number_format($sc_berat, 2, ',', '.'); ?></td>
              <td><?= htmlspecialchars($sc['keterangan'] ?? '-'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Summary Card -->
  <?php
    $h = $report['header'] ?? [];
    $net_prod = (float)($h['summary_nett_weight_produksi'] ?? $h['summary_net_produksi'] ?? 0);
    $net_pack = (float)($h['summary_nett_weight_packing_list'] ?? $h['summary_net_packing_list'] ?? 0);
    $sel_kg = (float)($h['summary_selisih'] ?? $h['selisih_kg'] ?? 0);
    $sel_pct = (float)($h['summary_selisih_percentage'] ?? $h['selisih_persen'] ?? 0);
  ?>
  <div class="pr-card">
    <div class="pr-card-header">
      <h2 class="pr-card-title">Ringkasan &amp; Rekonsiliasi Berat</h2>
    </div>
    <div class="p-3">
      <div class="row g-3 text-center">
        <div class="col-md-3">
          <div class="p-2 border rounded bg-light">
            <small class="text-muted d-block">Net Weight Produksi</small>
            <span class="fs-5 fw-bold text-primary"><?= number_format($net_prod, 2, ',', '.'); ?> kg</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-2 border rounded bg-light">
            <small class="text-muted d-block">Net Weight Packing List</small>
            <span class="fs-5 fw-bold"><?= number_format($net_pack, 2, ',', '.'); ?> kg</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-2 border rounded bg-light">
            <small class="text-muted d-block">Selisih (kg)</small>
            <span class="fs-5 fw-bold"><?= number_format($sel_kg, 2, ',', '.'); ?> kg</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-2 border rounded <?= abs($sel_pct) > 0.7 ? 'bg-danger-subtle border-danger' : 'bg-light'; ?>">
            <small class="text-muted d-block">Selisih (%)</small>
            <span class="fs-5 fw-bold <?= abs($sel_pct) > 0.7 ? 'text-danger' : 'text-success'; ?>">
              <?= number_format($sel_pct, 2, ',', '.'); ?> %
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
