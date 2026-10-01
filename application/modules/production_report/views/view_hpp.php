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
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
  }
  .meta-strip {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    background: #f8fafc;
    padding: 12px 18px;
    border-radius: 8px;
    border: 1px solid var(--pr-border);
    margin-top: 14px;
  }
  .meta-item {
    display: flex;
    flex-direction: column;
    min-width: 130px;
  }
  .meta-item .lbl {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--pr-muted);
    font-weight: 600;
  }
  .meta-item .val {
    font-size: 14px;
    font-weight: 700;
    color: var(--pr-text);
  }
  .card-hpp {
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--pr-border);
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
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
  .tbl-hpp th, .tbl-hpp td {
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
  .tbl-journal thead th {
    background: #f8fafc;
  }
  .tbl-journal tr.jgroup td {
    background: #f1f5f9;
    font-weight: 700;
    color: var(--pr-primary);
    padding: 8px 12px;
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
      function rp_format($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
    }
    if (!function_exists('kg_format')) {
      function kg_format($n) { return number_format((float)$n, 2, ',', '.'); }
    }
  ?>

  <div class="hpp-header d-flex justify-content-between align-items-center">
    <div>
      <h1 class="h4 fw-bold mb-1">Laporan Harga Pokok Produksi (HPP)</h1>
      <p class="text-muted small mb-0">Rincian Komponen Biaya Produksi (Read-Only) &amp; Posting Jurnal GL Interface.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= site_url('production_report/view/' . $h['id']); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-file-text-o me-1"></i> Lihat Laporan Produksi
      </a>
      <a href="<?= site_url('production_report'); ?>" class="btn btn-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    </div>
  </div>

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
    <div class="table-responsive">
      <table class="tbl-hpp">
        <thead>
          <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Produk</th>
            <th rowspan="2">Coil</th>
            <th rowspan="2" class="num">Berat (kg)</th>
            <th rowspan="2" class="num">Berat/Pcs</th>
            <th rowspan="2" class="num">Qty</th>
            <th rowspan="2" class="num">Costbook</th>
            <th colspan="8" class="grp">Komponen Biaya / Pcs (Rp)</th>
            <th colspan="8" class="grp-total">Total Biaya Finish Good (Rp)</th>
          </tr>
          <tr>
            <th class="num">Material</th>
            <th class="num">Man Power</th>
            <th class="num">FOH</th>
            <th class="num">Bahan Pend.</th>
            <th class="num">Consumable</th>
            <th class="num">Koordinasi</th>
            <th class="num">Scrap</th>
            <th class="num fw-bold">Value/Pcs</th>
            <th class="num">Tot Material</th>
            <th class="num">Man Power</th>
            <th class="num">FOH</th>
            <th class="num">Bahan Pend.</th>
            <th class="num">Consumable</th>
            <th class="num">Koordinasi</th>
            <th class="num">Scrap</th>
            <th class="num grp-total">FG Value Total</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($kw1_items)): ?>
            <tr><td colspan="23" class="text-center text-muted py-3">Tidak ada data KW 1 / Stok Bebas.</td></tr>
          <?php else: ?>
            <?php 
              $no = 1; 
              $t_mat = 0; $t_mp = 0; $t_foh = 0; $t_bahan = 0; $t_cons = 0; $t_koor = 0; $t_scrap = 0; $t_fg = 0;
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
                <td><strong><?= htmlspecialchars($it['nama_product_custom'] ?? '-'); ?></strong> <?= ($it['category_type'] === 'stok_bebas') ? '<span class="badge bg-warning text-dark small ms-1">Bebas</span>' : ''; ?></td>
                <td><small><?= htmlspecialchars($it['source_material_coil'] ?? '-'); ?></small></td>
                <td class="num"><?= kg_format($it['berat_total']); ?></td>
                <td class="num"><?= kg_format($it['berat_per_pcs']); ?></td>
                <td class="num fw-bold"><?= number_format($it['qty']); ?></td>
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
              <td colspan="15" class="text-end fw-bold">Total Biaya Finish Good (Rp) &rarr;</td>
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
      <h6 class="fw-bold mb-2">KW 2 &mdash; Internal <span class="badge bg-info text-dark ms-2 small">Inventory 80% &bull; Biaya Loss 20%</span></h6>
      <div class="table-responsive mb-4">
        <table class="tbl-hpp">
          <thead>
            <tr>
              <th rowspan="2">No</th>
              <th rowspan="2">Produk KW 2</th>
              <th rowspan="2">Coil</th>
              <th rowspan="2" class="num">Berat (kg)</th>
              <th rowspan="2" class="num">Qty</th>
              <th rowspan="2" class="num">Costbook</th>
              <th colspan="7" class="grp">Total Biaya Komponen (Rp)</th>
              <th rowspan="2" class="num grp-total">Total Biaya FG</th>
              <th rowspan="2" class="num text-success bg-light">Inventory (80%)</th>
              <th rowspan="2" class="num text-danger bg-light">Biaya Loss (20%)</th>
            </tr>
            <tr>
              <th class="num">Material</th>
              <th class="num">Man Power</th>
              <th class="num">FOH</th>
              <th class="num">Bahan Pend.</th>
              <th class="num">Consumable</th>
              <th class="num">Koordinasi</th>
              <th class="num">Scrap</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($kw2_int_items)): ?>
              <tr><td colspan="17" class="text-center text-muted py-2">Tidak ada KW 2 Internal.</td></tr>
            <?php else: ?>
              <?php $no = 1; foreach ($kw2_int_items as $it): ?>
                <tr>
                  <td class="text-center"><?= $no++; ?></td>
                  <td><strong><?= htmlspecialchars($it['nama_product_custom'] ?? '-'); ?></strong></td>
                  <td><small><?= htmlspecialchars($it['source_material_coil'] ?? '-'); ?></small></td>
                  <td class="num"><?= kg_format($it['berat_total']); ?></td>
                  <td class="num"><?= number_format($it['qty']); ?></td>
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

      <h6 class="fw-bold mb-2">KW 2 &mdash; Supplier <span class="badge bg-info text-dark ms-2 small">Inventory 80% &bull; Biaya Loss 20%</span></h6>
      <div class="table-responsive">
        <table class="tbl-hpp">
          <thead>
            <tr>
              <th rowspan="2">No</th>
              <th rowspan="2">Produk KW 2</th>
              <th rowspan="2">Coil</th>
              <th rowspan="2" class="num">Berat (kg)</th>
              <th rowspan="2" class="num">Qty</th>
              <th rowspan="2" class="num">Costbook</th>
              <th colspan="7" class="grp">Total Biaya Komponen (Rp)</th>
              <th rowspan="2" class="num grp-total">Total Biaya FG</th>
              <th rowspan="2" class="num text-success bg-light">Inventory (80%)</th>
              <th rowspan="2" class="num text-danger bg-light">Biaya Loss (20%)</th>
            </tr>
            <tr>
              <th class="num">Material</th>
              <th class="num">Man Power</th>
              <th class="num">FOH</th>
              <th class="num">Bahan Pend.</th>
              <th class="num">Consumable</th>
              <th class="num">Koordinasi</th>
              <th class="num">Scrap</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($kw2_sup_items)): ?>
              <tr><td colspan="17" class="text-center text-muted py-2">Tidak ada KW 2 Supplier.</td></tr>
            <?php else: ?>
              <?php $no = 1; foreach ($kw2_sup_items as $it): ?>
                <tr>
                  <td class="text-center"><?= $no++; ?></td>
                  <td><strong><?= htmlspecialchars($it['nama_product_custom'] ?? '-'); ?></strong></td>
                  <td><small><?= htmlspecialchars($it['source_material_coil'] ?? '-'); ?></small></td>
                  <td class="num"><?= kg_format($it['berat_total']); ?></td>
                  <td class="num"><?= number_format($it['qty']); ?></td>
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
            <th>Jenis Scrap</th>
            <th>Coil Asal</th>
            <th class="num" style="width:140px">Total Berat (kg)</th>
            <th class="num" style="width:140px">Costbook /kg</th>
            <th class="num" style="width:160px">Nilai (Rp)</th>
            <th class="num text-success bg-light" style="width:170px">Inventory Value (40%)</th>
            <th class="num text-danger bg-light" style="width:170px">Beban FOH (60%)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($waste_scraps)): ?>
            <tr><td colspan="8" class="text-center text-muted py-2">Tidak ada komponen scrap.</td></tr>
          <?php else: ?>
            <?php $no = 1; foreach ($waste_scraps as $sc): ?>
              <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td><strong><?= strtoupper(str_replace('_', ' ', $sc['scrap_type'])); ?></strong></td>
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
            <td class="num"><?= rp_format(($h['total_inventory_scrap'] ?? 0) + ($h['total_beban_foh_scrap'] ?? 0)); ?></td>
            <td class="num text-success"><?= rp_format($h['total_inventory_scrap'] ?? 0); ?></td>
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
                <th class="num">Berat (kg)</th>
                <th class="num">Nilai (Rp)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($sisa_scraps)): ?>
                <tr><td colspan="3" class="text-center text-muted small py-2">— Tidak ada —</td></tr>
              <?php else: ?>
                <?php foreach ($sisa_scraps as $s): ?>
                  <tr>
                    <td><small><?= htmlspecialchars($s['target_coil_code']); ?></small></td>
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
                <th class="num">Berat (kg)</th>
                <th class="num">Nilai (Rp)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($hold_scraps)): ?>
                <tr><td colspan="3" class="text-center text-muted small py-2">— Tidak ada —</td></tr>
              <?php else: ?>
                <?php foreach ($hold_scraps as $s): ?>
                  <tr>
                    <td><small><?= htmlspecialchars($s['target_coil_code']); ?></small></td>
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
            <small class="text-muted d-block">Nilai Rupiah Selisih Material</small>
            <span class="fs-5 fw-bold <?= ((float)($h['selisih_weight_value'] ?? 0) < 0) ? 'text-success' : 'text-danger'; ?>">
              <?= rp_format($h['selisih_weight_value'] ?? 0); ?>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 5. JURNAL PRODUKSI (GL INTERFACE) -->
  <div class="card-hpp">
    <div class="card-hpp-header d-flex justify-content-between align-items-center">
      <h2><span class="card-num">7</span> Jurnal Produksi &mdash; GL Interface</h2>
      <button type="button" class="btn btn-primary btn-sm" id="btnPostGL" onclick="alert('Jurnal Produksi siap di-posting ke General Ledger!');">
        <i class="fa fa-share me-1"></i> Posting Jurnal ke GL
      </button>
    </div>
    <div class="table-responsive">
      <table class="tbl-hpp tbl-journal">
        <thead>
          <tr>
            <th style="width:130px">Account COA</th>
            <th>Nama Account</th>
            <th>Formula / Keterangan</th>
            <th class="num" style="width:180px">Debit (Rp)</th>
            <th class="num" style="width:180px">Kredit (Rp)</th>
          </tr>
        </thead>
        <tbody>
          <?php
            // Baris Jurnal
            $cogm_mat  = 0; $cogm_mp = 0; $cogm_foh = 0; $cogm_bahan = 0; $cogm_cons = 0; $cogm_koor = 0; $cogm_scrap = 0;
            foreach ($items as $it) {
              $cogm_mat   += (float)$it['material_cost_total'];
              $cogm_mp    += (float)$it['main_power_cost_total'];
              $cogm_foh   += (float)$it['foh_cost_total'];
              $cogm_bahan += (float)$it['bahan_pendukung_cost_total'];
              $cogm_cons  += (float)$it['consumables_cost_total'];
              $cogm_koor  += (float)$it['coordinator_cost_total'];
              $cogm_scrap += (float)$it['scrap_cost_total'];
            }
            $kw2_loss = (float)($h['total_loss_kw2'] ?? 0);
            $fg_inv   = (float)($h['total_inventory_fg'] ?? 0);
            $cogm_tot = (float)($h['total_cogm'] ?? 0);

            $inv_scrap = (float)($h['total_inventory_scrap'] ?? 0);
            $foh_scrap = (float)($h['total_beban_foh_scrap'] ?? 0);
            $inv_sisa  = (float)($h['total_inventory_sisa'] ?? 0);

            // Coil usage per gudang
            $mat_unpack_hold = 0;
            $mat_wip = 0;
            foreach ($materials as $m) {
              $cb = (float)($m['harga_beli'] ?? 0);
              if ($cb == 0 && isset($it['costbook_rate'])) $cb = (float)$it['costbook_rate'];
              $val = (float)$m['nett_weight_packing'] * $cb;
              if ($m['source_warehouse'] === 'wip') {
                $mat_wip += $val;
              } else {
                $mat_unpack_hold += $val;
              }
            }

            $sel_audit = (float)($h['selisih_weight_value'] ?? 0);
          ?>
          <tr class="jgroup"><td colspan="5">COGM (Cost of Goods Manufactured)</td></tr>
          <tr><td>5101-01-01</td><td>COGM MATERIAL</td><td>Material KW 1 &amp; KW 2</td><td class="num"><?= rp_format($cogm_mat); ?></td><td class="num">&mdash;</td></tr>
          <tr><td>5101-02-01</td><td>COGM LABOUR COST PC</td><td>Labour cost FG KW1 &amp; KW2</td><td class="num"><?= rp_format($cogm_mp); ?></td><td class="num">&mdash;</td></tr>
          <tr><td>5101-05-01</td><td>COGM FACTORY OVERHEAD PC</td><td>Factory Overhead FG KW1 &amp; KW2</td><td class="num"><?= rp_format($cogm_foh); ?></td><td class="num">&mdash;</td></tr>
          <tr><td>5101-03-01</td><td>COGM BAHAN PENDUKUNG KHUSUS PC</td><td>Bahan Pendukung Khusus FG KW1 &amp; KW2</td><td class="num"><?= rp_format($cogm_bahan); ?></td><td class="num">&mdash;</td></tr>
          <tr><td>5101-04-01</td><td>COGM CONSUMABLE PC</td><td>Consumables FG KW1 &amp; KW2</td><td class="num"><?= rp_format($cogm_cons); ?></td><td class="num">&mdash;</td></tr>
          <tr><td>5101-06-01</td><td>COGM BIAYA KOORDINASI PC</td><td>Biaya Koordinasi FG KW1 &amp; KW2</td><td class="num"><?= rp_format($cogm_koor); ?></td><td class="num">&mdash;</td></tr>
          <tr><td>5101-07-01</td><td>COGM BIAYA SCRAP PC</td><td>Biaya Scrap FG KW1 &amp; KW2</td><td class="num"><?= rp_format($cogm_scrap); ?></td><td class="num">&mdash;</td></tr>
          <tr><td>&mdash;</td><td>COGM Loss KW 2</td><td>Total Biaya Loss KW 2 (20%)</td><td class="num">&mdash;</td><td class="num"><?= rp_format($kw2_loss); ?></td></tr>

          <tr class="jgroup"><td colspan="5">Persediaan (Inventory)</td></tr>
          <tr><td>1105-03-01</td><td>PERSEDIAAN BARANG WASTE</td><td>40% dari Scrap Waste</td><td class="num"><?= rp_format($inv_scrap); ?></td><td class="num">&mdash;</td></tr>
          <tr><td>1105-05-02</td><td>WIP MANUFACTURING</td><td>Sisa Material dikembalikan ke WIP</td><td class="num"><?= rp_format($inv_sisa); ?></td><td class="num">&mdash;</td></tr>
          <tr><td>1105-05-02</td><td>WIP MANUFACTURING</td><td>Material yang dipakai produksi (keluar WIP)</td><td class="num">&mdash;</td><td class="num"><?= rp_format($mat_wip); ?></td></tr>
          <tr><td>1105-01-01</td><td>PERSEDIAAN BAHAN BAKU PRODUKSI</td><td>Nett Weight Material Unpack/Hold &times; Costbook</td><td class="num">&mdash;</td><td class="num"><?= rp_format($mat_unpack_hold); ?></td></tr>

          <tr class="jgroup"><td colspan="5">Biaya Aktual Based On Standard Costing</td></tr>
          <tr><td>5205-01-01</td><td>BIAYA SCRAP</td><td>60% dari Scrap Waste</td><td class="num"><?= rp_format($foh_scrap); ?></td><td class="num">&mdash;</td></tr>
          <tr><td>&mdash;</td><td>Biaya KW 2 (20%)</td><td>Beban Loss KW 2 (20%)</td><td class="num"><?= rp_format($kw2_loss); ?></td><td class="num">&mdash;</td></tr>

          <tr class="jgroup"><td colspan="5">Biaya Loss/Profit Akibat Selisih Material</td></tr>
          <tr>
            <td>7201-01-03</td><td>B. SELISIH STOCK AUDIT</td><td>Selisih timbang berat tercatat vs aktual <?= ($sel_audit < 0) ? '(Favorable)' : ''; ?></td>
            <td class="num"><?= ($sel_audit >= 0) ? rp_format($sel_audit) : '&mdash;'; ?></td>
            <td class="num"><?= ($sel_audit < 0) ? rp_format(abs($sel_audit)) : '&mdash;'; ?></td>
          </tr>

          <tr class="jgroup"><td colspan="5">Cek Balancing HPP vs COGM</td></tr>
          <tr class="table-light fw-bold">
            <td>1106-03-01</td><td>INVENTORY FINISHED GOODS</td><td>Total Nilai Finish Good KW 1 &amp; KW 2</td>
            <td class="num text-primary"><?= rp_format($fg_inv); ?></td>
            <td class="num">&mdash;</td>
          </tr>
          <tr class="table-light fw-bold">
            <td>5103-01-01</td><td>COST OF GOOD MANUFACTURING</td><td>Total Komponen COGM (Material s.d. Scrap)</td>
            <td class="num">&mdash;</td>
            <td class="num text-primary"><?= rp_format($cogm_tot); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
