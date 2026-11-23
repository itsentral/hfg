<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<!-- Bootstrap 5, FontAwesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<style>
  :root {
    --pr-primary: #1e3a8a;
    --pr-primary-soft: #eff6ff;
    --pr-border: #e2e8f0;
    --pr-text: #0f172a;
    --pr-muted: #64748b;
  }

  .hpp-header {
    background: #fff;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 20px;
    border: 1px solid var(--pr-border);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
  }

  .meta-strip {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    /* desktop: 6 kolom sama lebar */
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid var(--pr-border);
    margin-top: 14px;
    overflow: hidden;
  }

  .meta-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 12px 10px;
    min-width: 0;
    border-right: 1px solid var(--pr-border);
  }

  .meta-item:last-child {
    border-right: 0;
  }

  .meta-item .lbl {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--pr-muted);
    font-weight: 600;
    margin-bottom: 2px;
  }

  .meta-item .val {
    font-size: 14px;
    font-weight: 700;
    color: var(--pr-text);
    word-break: break-word;
  }

  .card-hpp {
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--pr-border);
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
  }

  .card-hpp-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--pr-border);
    background: #fff;
    border-radius: 12px 12px 0 0;
  }

  .card-hpp-header h2 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: var(--pr-text);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .card-num {
    background: var(--pr-primary-soft);
    color: var(--pr-primary);
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 12px;
    font-weight: 700;
  }

  .tbl-hpp {
    width: 100%;
    font-size: 12.5px;
    border-collapse: collapse;
    margin-bottom: 0;
  }

  .tbl-hpp th,
  .tbl-hpp td {
    padding: 8px 10px;
    border: 1px solid var(--pr-border);
    vertical-align: middle;
  }

  .tbl-hpp thead th {
    background: #f1f5f9;
    font-weight: 600;
    text-align: center;
    color: #334155;
  }

  .tbl-hpp th.grp {
    background: #e2e8f0;
  }

  .tbl-hpp th.grp-total {
    background: #dbeafe;
    color: #1e40af;
  }

  .tbl-hpp td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
  }

  .tbl-hpp td.grp-total {
    background: #eff6ff;
    font-weight: 700;
  }

  .tbl-hpp tfoot td {
    font-weight: 700;
    background: #f8fafc;
  }

  /* Tablet: 3 x 2 */
  @media (max-width: 991.98px) {
    .meta-strip {
      grid-template-columns: repeat(3, 1fr);
    }

    .meta-item {
      border-bottom: 1px solid var(--pr-border);
    }

    .meta-item:nth-child(3n) {
      border-right: 0;
    }

    .meta-item:nth-child(n+4) {
      border-bottom: 0;
    }
  }

  /* Mobile: 2 x 3 */
  @media (max-width: 575.98px) {
    .meta-strip {
      grid-template-columns: repeat(2, 1fr);
    }

    .meta-item:nth-child(3n) {
      border-right: 1px solid var(--pr-border);
    }

    .meta-item:nth-child(2n) {
      border-right: 0;
    }

    .meta-item:nth-child(n+4) {
      border-bottom: 1px solid var(--pr-border);
    }

    .meta-item:nth-child(n+5) {
      border-bottom: 0;
    }
  }
</style>

<div class="container-fluid px-3 py-2">
  <?php
  $h = $report['header'] ?? [];
  $spk_h = $report['spk_header'] ?? [];
  $mesin = $report['mesin'] ?? [];
  $materials = $report['materials'] ?? [];
  $items = $report['items'] ?? [];
  $scraps = $report['scraps'] ?? [];

  // Pisahkan items
  $kw1_items = [];
  $kw2_int_items = [];
  $kw2_sup_items = [];

  foreach ($items as $it) {
    if ($it['category_type'] === 'kw_2_internal') {
      $kw2_int_items[] = $it;
    } elseif ($it['category_type'] === 'kw_2_supplier') {
      $kw2_sup_items[] = $it;
    } else {
      $kw1_items[] = $it;
    }
  }

  // Pisahkan scraps
  $waste_scraps = [];
  $sisa_scraps  = [];
  $hold_scraps  = [];
  foreach ($scraps as $sc) {
    if ($sc['scrap_type'] === 'sisa_coil') {
      $sisa_scraps[] = $sc;
    } elseif ($sc['scrap_type'] === 'hold_coil') {
      $hold_scraps[] = $sc;
    } else {
      $waste_scraps[] = $sc;
    }
  }

  // Helper formatter
  if (!function_exists('rp_format')) {
    function rp_format($n)
    {
      return 'Rp ' . number_format((float)$n, 0, ',', '.');
    }
  }
  if (!function_exists('kg_format')) {
    function kg_format($n)
    {
      return number_format((float)$n, 2, ',', '.');
    }
  }
  ?>

  <div class="hpp-header d-flex justify-content-between align-items-center">
    <div>
      <h1 class="h4 fw-bold mb-1">Cost of Goods Manufactured (HPP) Report</h1>
      <p class="text-muted small mb-0">Production Cost Component Details (Read-Only).</p>
    </div>
    <div class="d-flex gap-2">
      <?php if (!empty($h['status_draft'])): ?>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnRefreshCosting" data-id="<?= $h['id']; ?>">
          <i class="fa fa-refresh me-1"></i> Tarik Rate Costing Terkini
        </button>
      <?php endif; ?>
      <a href="<?= site_url('production_report/view/' . $h['id']); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-file-text-o me-1"></i> Lihat Laporan Produksi
      </a>
      <a href="<?= site_url('production_report'); ?>" class="btn btn-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    </div>
  </div>

  <?php if (!empty($h['status_draft']) && !empty($costing_check) && !$costing_check['is_all_available']): ?>
    <!-- BANNER PERINGATAN KETERSEDIAAN COSTING (DRAFT) -->
    <div class="alert alert-warning border-warning shadow-sm mb-4 d-flex align-items-center justify-content-between p-3">
      <div class="d-flex align-items-center gap-3">
        <i class="fa fa-exclamation-triangle fs-3 text-warning"></i>
        <div>
          <h6 class="fw-bold mb-1 text-dark">Product Costing Data Incomplete (FR-48, FR-74)</h6>
          <div class="small text-dark">
            This report still has status <strong>Draft</strong> and contains products that have not been updated in the <strong>Costing Simulation</strong> module (cost components are Rp 0).
            Run <em>Update Costing</em> in the Costing Simulation module, then click the <strong>"Pull Latest Costing Rate"</strong> button before submitting the report to Accounting.
          </div>
        </div>
      </div>
      <a href="<?= site_url('simulasi_costing'); ?>" target="_blank" class="btn btn-warning btn-sm text-nowrap fw-bold ms-3">
        <i class="fa fa-calculator me-1"></i> Open Costing Simulation <i class="fa fa-external-link ms-1"></i>
      </a>
    </div>
  <?php endif; ?>

  <!-- Meta Strip -->
  <div class="meta-strip mb-4">
    <div class="meta-item">
      <span class="lbl">No. SPK</span>
      <span class="val text-primary"><?= htmlspecialchars($spk_h['spk_no'] ?? '-'); ?></span>
    </div>
    <div class="meta-item">
      <span class="lbl">Tgl Produksi</span>
      <span class="val"><?= date('d/m/Y', strtotime($h['tgl_produksi'] ?? date('Y-m-d'))); ?></span>
    </div>
    <div class="meta-item">
      <span class="lbl">Mesin</span>
      <span class="val"><?= htmlspecialchars($mesin['nm_asset'] ?? '-'); ?></span>
    </div>
    <div class="meta-item">
      <span class="lbl">Total COGM</span>
      <span class="val text-success"><?= rp_format($h['total_cogm'] ?? 0); ?></span>
    </div>
    <div class="meta-item">
      <span class="lbl">Inventory FG Value</span>
      <span class="val text-primary"><?= rp_format($h['total_inventory_fg'] ?? 0); ?></span>
    </div>
    <div class="meta-item">
      <span class="lbl">Status</span>
      <span class="val"><span class="badge <?= (!empty($h['status_draft'])) ? 'bg-warning text-dark' : 'bg-success'; ?>"><?= (!empty($h['status_draft'])) ? 'Draft' : 'Final'; ?></span></span>
    </div>
  </div>

  <!-- 1. HPP FG KW 1 & STOK BEBAS -->
  <div class="card-hpp">
    <div class="card-hpp-header">
      <h2><span class="card-num">1</span> HPP — Finish Good KW 1 &amp; Stok Bebas</h2>
    </div>
    <div class="p-3 pb-0">
      <div class="alert alert-info py-2 px-3 mb-2 d-flex align-items-center gap-2 small">
        <i class="fa fa-info-circle fs-5 text-primary"></i>
        <div>
          <strong>HPP Rate from Costing Result:</strong> The cost composition (Man Power, FOH, Special Supporting Material, Consumables, Coordination Cost, and Scrap) is calculated using the <strong>Rp/Kg from BOM Variant Costing Result</strong> selected by the SPK, and is <strong>frozen</strong> when the report is saved (FR-48, FR-49).
        </div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="tbl-hpp">
        <thead>
          <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Produk <br><small class="text-muted fw-normal">/ Varian BOM (no_uniq_id)</small></th>
            <th rowspan="2">Coil</th>
            <th rowspan="2" class="num">Weight (kg)</th>
            <th rowspan="2" class="num">Weight/Pcs</th>
            <th rowspan="2" class="num">Qty</th>
            <th rowspan="2" class="num">Costbook</th>
            <th colspan="8" class="grp">Cost Component / Pcs (Rp)</th>
            <th colspan="8" class="grp-total">Total Finish Good Cost (Rp)</th>
          </tr>
          <tr>
            <th class="num">Material</th>
            <th class="num">Man Power</th>
            <th class="num">FOH</th>
            <th class="num">Supp. Material</th>
            <th class="num">Consumable</th>
            <th class="num">Koordinasi</th>
            <th class="num">Scrap</th>
            <th class="num fw-bold">Value/Pcs</th>
            <th class="num">Tot Material</th>
            <th class="num">Man Power</th>
            <th class="num">FOH</th>
            <th class="num">Supp. Material</th>
            <th class="num">Consumable</th>
            <th class="num">Koordinasi</th>
            <th class="num">Scrap</th>
            <th class="num grp-total">FG Value Total</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($kw1_items)): ?>
            <tr>
              <td colspan="23" class="text-center text-muted py-3">No KW 1 / Free Stock data.</td>
            </tr>
          <?php else: ?>
            <?php
            $no = 1;
            $t_mat = 0;
            $t_mp = 0;
            $t_foh = 0;
            $t_bahan = 0;
            $t_cons = 0;
            $t_koor = 0;
            $t_scrap = 0;
            $t_fg = 0;
            foreach ($kw1_items as $it):
              $val_pcs = (float)$it['material_cost_per_pcs'] + (float)$it['main_power_cost_per_pcs'] + (float)$it['foh_cost_per_pcs'] + (float)$it['bahan_pendukung_cost_per_pcs'] + (float)$it['consumables_cost_per_pcs'] + (float)$it['coordinator_cost_per_pcs'] + (float)$it['scrap_cost_per_pcs'];
              $t_mat += (float)$it['material_cost_total'];
              $t_mp += (float)$it['main_power_cost_total'];
              $t_foh += (float)$it['foh_cost_total'];
              $t_bahan += (float)$it['bahan_pendukung_cost_total'];
              $t_cons += (float)$it['consumables_cost_total'];
              $t_koor += (float)$it['coordinator_cost_total'];
              $t_scrap += (float)$it['scrap_cost_total'];
              $t_fg += (float)$it['total_hpp_item'];
            ?>
              <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td>
                  <strong><?= htmlspecialchars($it['nama_product_custom'] ?? '-'); ?></strong> <?= ($it['category_type'] === 'stok_bebas') ? '<span class="badge bg-warning text-dark small ms-1">Bebas</span>' : ''; ?>
                  <?php if (!empty($it['no_uniq_id'])): ?>
                    <br><span class="badge bg-light text-primary border mt-1"><i class="fa fa-tag me-1"></i><?= htmlspecialchars((string) $it['no_uniq_id']); ?></span>
                  <?php endif; ?>
                  <?php
                    $is_costing_zero = ((float)$it['main_power_cost_per_pcs'] == 0 && (float)$it['foh_cost_per_pcs'] == 0 && (float)$it['bahan_pendukung_cost_per_pcs'] == 0);
                  ?>
                  <?php if ($is_costing_zero): ?>
                    <br><span class="badge bg-danger-subtle text-danger border border-danger mt-1 small" title="Cost Component Rp 0 (Costing Not Updated)"><i class="fa fa-exclamation-triangle me-1"></i>Costing 0</span>
                  <?php else: ?>
                    <br><span class="badge bg-success-subtle text-success border border-success mt-1 small" title="Cost Components Filled from Costing Simulation"><i class="fa fa-check me-1"></i>Costing OK</span>
                  <?php endif; ?>
                </td>
                <td><small><?= htmlspecialchars($it['source_material_coil'] ?? '-'); ?></small></td>
                <td class="num"><?= kg_format($it['berat_total']); ?></td>
                <td class="num"><?= kg_format($it['berat_per_pcs']); ?></td>
                <td class="num fw-bold"><?= number_format((float) $it['qty']); ?></td>
                <td class="num"><?= rp_format($it['costbook_rate']); ?></td>
                <td class="num"><?= rp_format($it['material_cost_per_pcs']); ?></td>
                <td class="num"><?= rp_format($it['main_power_cost_per_pcs']); ?></td>
                <td class="num"><?= rp_format($it['foh_cost_per_pcs']); ?></td>
                <td class="num"><?= rp_format($it['bahan_pendukung_cost_per_pcs']); ?></td>
                <td class="num"><?= rp_format($it['consumables_cost_per_pcs']); ?></td>
                <td class="num"><?= rp_format($it['coordinator_cost_per_pcs']); ?></td>
                <td class="num"><?= rp_format($it['scrap_cost_per_pcs']); ?></td>
                <td class="num fw-bold"><?= rp_format($val_pcs); ?></td>
                <td class="num"><?= rp_format($it['material_cost_total']); ?></td>
                <td class="num"><?= rp_format($it['main_power_cost_total']); ?></td>
                <td class="num"><?= rp_format($it['foh_cost_total']); ?></td>
                <td class="num"><?= rp_format($it['bahan_pendukung_cost_total']); ?></td>
                <td class="num"><?= rp_format($it['consumables_cost_total']); ?></td>
                <td class="num"><?= rp_format($it['coordinator_cost_total']); ?></td>
                <td class="num"><?= rp_format($it['scrap_cost_total']); ?></td>
                <td class="num grp-total"><?= rp_format($it['total_hpp_item']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <?php if (!empty($kw1_items)): ?>
          <tfoot>
            <tr>
              <td colspan="15" class="text-end fw-bold">Total Finish Good Cost (Rp) &rarr;</td>
              <td class="num"><?= rp_format($t_mat); ?></td>
              <td class="num"><?= rp_format($t_mp); ?></td>
              <td class="num"><?= rp_format($t_foh); ?></td>
              <td class="num"><?= rp_format($t_bahan); ?></td>
              <td class="num"><?= rp_format($t_cons); ?></td>
              <td class="num"><?= rp_format($t_koor); ?></td>
              <td class="num"><?= rp_format($t_scrap); ?></td>
              <td class="num grp-total"><?= rp_format($t_fg); ?></td>
            </tr>
          </tfoot>
        <?php endif; ?>
      </table>
    </div>
  </div>

  <!-- 2. HPP FG KW 2 -->
  <div class="card-hpp">
    <div class="card-hpp-header">
      <h2><span class="card-num">2</span> HPP — Finish Good KW 2</h2>
    </div>
    <div class="p-3">
      <h6 class="fw-bold mb-2">KW 2 &mdash; Internal <span class="badge bg-info text-dark ms-2 small">Inventory 80% &bull; Loss Cost 20%</span></h6>
      <div class="table-responsive mb-4">
        <table class="tbl-hpp">
          <thead>
            <tr>
              <th rowspan="2">No</th>
              <th rowspan="2">Produk KW 2 <br><small class="text-muted fw-normal">/ Varian BOM (no_uniq_id)</small></th>
              <th rowspan="2">Coil</th>
              <th rowspan="2" class="num">Weight (kg)</th>
              <th rowspan="2" class="num">Qty</th>
              <th rowspan="2" class="num">Costbook</th>
              <th colspan="7" class="grp">Total Component Cost (Rp)</th>
              <th rowspan="2" class="num grp-total">Total FG Cost</th>
              <th rowspan="2" class="num text-success bg-light">Inventory (80%)</th>
              <th rowspan="2" class="num text-danger bg-light">Loss Cost (20%)</th>
            </tr>
            <tr>
              <th class="num">Material</th>
              <th class="num">Man Power</th>
              <th class="num">FOH</th>
              <th class="num">Supp. Material</th>
              <th class="num">Consumable</th>
              <th class="num">Koordinasi</th>
              <th class="num">Scrap</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($kw2_int_items)): ?>
              <tr>
                <td colspan="17" class="text-center text-muted py-2">No KW 2 Internal.</td>
              </tr>
            <?php else: ?>
              <?php $no = 1;
              foreach ($kw2_int_items as $it): ?>
                <tr>
                  <td class="text-center"><?= $no++; ?></td>
                  <td>
                    <strong><?= htmlspecialchars($it['nama_product_custom'] ?? '-'); ?></strong>
                    <?php if (!empty($it['no_uniq_id'])): ?>
                      <br><span class="badge bg-light text-primary border mt-1"><i class="fa fa-tag me-1"></i><?= htmlspecialchars((string) $it['no_uniq_id']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td><small><?= htmlspecialchars($it['source_material_coil'] ?? '-'); ?></small></td>
                  <td class="num"><?= kg_format($it['berat_total']); ?></td>
                  <td class="num"><?= number_format((float) $it['qty']); ?></td>
                  <td class="num"><?= rp_format($it['costbook_rate']); ?></td>
                  <td class="num"><?= rp_format($it['material_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['main_power_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['foh_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['bahan_pendukung_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['consumables_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['coordinator_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['scrap_cost_total']); ?></td>
                  <td class="num grp-total"><?= rp_format($it['total_hpp_item']); ?></td>
                  <td class="num text-success fw-bold"><?= rp_format($it['inventory_value_80']); ?></td>
                  <td class="num text-danger fw-bold"><?= rp_format($it['loss_cost_20']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <h6 class="fw-bold mb-2">KW 2 &mdash; Supplier <span class="badge bg-info text-dark ms-2 small">Inventory 80% &bull; Loss Cost 20%</span></h6>
      <div class="table-responsive">
        <table class="tbl-hpp">
          <thead>
            <tr>
              <th rowspan="2">No</th>
              <th rowspan="2">Produk KW 2 <br><small class="text-muted fw-normal">/ Varian BOM (no_uniq_id)</small></th>
              <th rowspan="2">Coil</th>
              <th rowspan="2" class="num">Weight (kg)</th>
              <th rowspan="2" class="num">Qty</th>
              <th rowspan="2" class="num">Costbook</th>
              <th colspan="7" class="grp">Total Component Cost (Rp)</th>
              <th rowspan="2" class="num grp-total">Total FG Cost</th>
              <th rowspan="2" class="num text-success bg-light">Inventory (80%)</th>
              <th rowspan="2" class="num text-danger bg-light">Loss Cost (20%)</th>
            </tr>
            <tr>
              <th class="num">Material</th>
              <th class="num">Man Power</th>
              <th class="num">FOH</th>
              <th class="num">Supp. Material</th>
              <th class="num">Consumable</th>
              <th class="num">Koordinasi</th>
              <th class="num">Scrap</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($kw2_sup_items)): ?>
              <tr>
                <td colspan="17" class="text-center text-muted py-2">No KW 2 Supplier.</td>
              </tr>
            <?php else: ?>
              <?php $no = 1;
              foreach ($kw2_sup_items as $it): ?>
                <tr>
                  <td class="text-center"><?= $no++; ?></td>
                  <td>
                    <strong><?= htmlspecialchars($it['nama_product_custom'] ?? '-'); ?></strong>
                    <?php if (!empty($it['no_uniq_id'])): ?>
                      <br><span class="badge bg-light text-primary border mt-1"><i class="fa fa-tag me-1"></i><?= htmlspecialchars((string) $it['no_uniq_id']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td><small><?= htmlspecialchars($it['source_material_coil'] ?? '-'); ?></small></td>
                  <td class="num"><?= kg_format($it['berat_total']); ?></td>
                  <td class="num"><?= number_format((float) $it['qty']); ?></td>
                  <td class="num"><?= rp_format($it['costbook_rate']); ?></td>
                  <td class="num"><?= rp_format($it['material_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['main_power_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['foh_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['bahan_pendukung_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['consumables_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['coordinator_cost_total']); ?></td>
                  <td class="num"><?= rp_format($it['scrap_cost_total']); ?></td>
                  <td class="num grp-total"><?= rp_format($it['total_hpp_item']); ?></td>
                  <td class="num text-success fw-bold"><?= rp_format($it['inventory_value_80']); ?></td>
                  <td class="num text-danger fw-bold"><?= rp_format($it['loss_cost_20']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- 3. KOMPONEN SCRAP -->
  <div class="card-hpp">
    <div class="card-hpp-header">
      <h2><span class="card-num">3</span> Komponen Scrap Waste &mdash; <small class="text-muted fs-6">Inventory Value (40%) &bull; Beban FOH (60%)</small></h2>
    </div>
    <div class="table-responsive">
      <table class="tbl-hpp">
        <thead>
          <tr>
            <th style="width:50px">No</th>
            <th>Scrap Type</th>
            <th>Coil Asal</th>
            <th class="num" style="width:140px">Total Weight (kg)</th>
            <th class="num" style="width:140px">Costbook /kg</th>
            <th class="num" style="width:160px">Value (Rp)</th>
            <th class="num text-success bg-light" style="width:170px">Inventory Value (40%)</th>
            <th class="num text-danger bg-light" style="width:170px">Beban FOH (60%)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($waste_scraps)): ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-2">No scrap components.</td>
            </tr>
          <?php else: ?>
            <?php $no = 1;
            foreach ($waste_scraps as $sc): ?>
              <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td><strong><?= strtoupper(str_replace('_', ' ', $sc['scrap_type'] ?? '')); ?></strong></td>
                <td><?= htmlspecialchars($sc['target_coil_code'] ?: '-'); ?></td>
                <td class="num"><?= kg_format($sc['berat_total']); ?></td>
                <td class="num"><?= rp_format($sc['costbook_rate']); ?></td>
                <td class="num fw-bold"><?= rp_format($sc['total_nilai']); ?></td>
                <td class="num text-success fw-bold"><?= rp_format($sc['inventory_value_40']); ?></td>
                <td class="num text-danger fw-bold"><?= rp_format($sc['foh_burden_60']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5" class="text-end fw-bold">Total Scrap &rarr;</td>
            <td class="num"><?= rp_format((($h['persediaan_waste'] ?? $h['total_inventory_scrap'] ?? 0)) + ($h['total_beban_foh_scrap'] ?? 0)); ?></td>
            <td class="num text-success"><?= rp_format($h['persediaan_waste'] ?? $h['total_inventory_scrap'] ?? 0); ?></td>
            <td class="num text-danger"><?= rp_format($h['total_beban_foh_scrap'] ?? 0); ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- 4. SISA COIL, HOLD COIL & SELISIH BERAT -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card-hpp h-100">
        <div class="card-hpp-header py-2">
          <h2 class="fs-6"><span class="card-num">4</span> Sisa Coil &rarr; WIP (100%)</h2>
        </div>
        <div class="p-2 table-responsive">
          <table class="tbl-hpp">
            <thead>
              <tr>
                <th>Coil</th>
                <th class="num">Weight (kg)</th>
                <th class="num">Value (Rp)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($sisa_scraps)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted small py-2">— None —</td>
                </tr>
              <?php else: ?>
                <?php foreach ($sisa_scraps as $s): ?>
                  <tr>
                    <td><small><?= htmlspecialchars((string) $s['target_coil_code']); ?></small></td>
                    <td class="num"><?= kg_format($s['berat_total']); ?></td>
                    <td class="num fw-bold text-success"><?= rp_format($s['total_nilai']); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2" class="text-end small">Total WIP:</td>
                <td class="num text-success"><?= rp_format($h['total_inventory_sisa'] ?? 0); ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card-hpp h-100">
        <div class="card-hpp-header py-2">
          <h2 class="fs-6"><span class="card-num">5</span> Hold Coil &rarr; Hold (100%)</h2>
        </div>
        <div class="p-2 table-responsive">
          <table class="tbl-hpp">
            <thead>
              <tr>
                <th>Coil</th>
                <th class="num">Weight (kg)</th>
                <th class="num">Value (Rp)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($hold_scraps)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted small py-2">— None —</td>
                </tr>
              <?php else: ?>
                <?php foreach ($hold_scraps as $s): ?>
                  <tr>
                    <td><small><?= htmlspecialchars((string) $s['target_coil_code']); ?></small></td>
                    <td class="num"><?= kg_format($s['berat_total']); ?></td>
                    <td class="num fw-bold text-primary"><?= rp_format($s['total_nilai']); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2" class="text-end small">Total Hold:</td>
                <td class="num text-primary"><?= rp_format($h['total_inventory_hold'] ?? 0); ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card-hpp h-100">
        <div class="card-hpp-header py-2">
          <h2 class="fs-6"><span class="card-num">6</span> Selisih Berat Audit Material</h2>
        </div>
        <div class="p-3">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small">Selisih Aktual vs Pack:</span>
            <span class="fw-bold"><?= kg_format($h['summary_selisih'] ?? 0); ?> kg (<?= number_format($h['summary_selisih_percentage'] ?? 0, 2); ?>%)</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small">Status Audit:</span>
            <span><?= ((float)($h['summary_selisih'] ?? 0) < 0) ? '<span class="badge bg-success-subtle text-success">Favorable</span>' : '<span class="badge bg-danger-subtle text-danger">Unfavorable</span>'; ?></span>
          </div>
          <div class="p-2 border rounded bg-light text-center mt-3">
            <small class="text-muted d-block">Material Difference Rupiah Value</small>
            <span class="fs-5 fw-bold <?= ((float)($h['selisih_weight_value'] ?? 0) < 0) ? 'text-success' : 'text-danger'; ?>">
              <?= rp_format($h['selisih_weight_value'] ?? 0); ?>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
  $('#btnRefreshCosting').on('click', function() {
    var reportId = $(this).data('id');
    Swal.fire({
      title: 'Tarik Rate Costing Terkini?',
      text: 'The system will re-read the Rp/Kg cost components from the latest Costing Simulation result and recalculate all HPP values of this draft.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, Pull Rate',
      cancelButtonText: 'Cancel'
    }).then(function(res) {
      if (!res.isConfirmed) return;

      Swal.fire({
        title: 'Recalculating HPP...',
        allowOutsideClick: false,
        didOpen: function() {
          Swal.showLoading();
        }
      });

      $.ajax({
        url: '<?= site_url("production_report/refresh_costing/"); ?>' + reportId,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
          Swal.close();
          if (response.status === 1) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: response.message || 'Costing rate updated successfully!',
              timer: 1500,
              showConfirmButton: false
            }).then(function() {
              window.location.reload();
            });
          } else {
            Swal.fire('Failed', response.message || 'Failed to update costing rate.', 'warning');
          }
        },
        error: function() {
          Swal.close();
          Swal.fire('Error', 'A system error occurred while contacting the server.', 'error');
        }
      });
    });
  });
});
</script>