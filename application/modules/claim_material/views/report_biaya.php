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
  .cl-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid var(--cl-border);
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  }
  .cl-table {
    width: 100%;
    font-size: 12.5px;
  }
  .cl-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    padding: 10px 10px;
    border-bottom: 1px solid var(--cl-border);
    white-space: nowrap;
  }
  .cl-table td {
    padding: 10px 10px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
  }
  .cl-table tfoot td {
    background: #f8fafc;
    font-weight: 700;
    border-top: 2px solid var(--cl-border);
  }
  .badge-dn { background: #dbeafe; color: #1e40af; }
  .badge-jual { background: #fef3c7; color: #92400e; }
</style>

<div class="container-fluid px-3 py-2">
  <div class="cl-page-header">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1 class="h5 fw-bold mb-1">Report Biaya Kompensasi vs Biaya Scrap</h1>
        <p class="text-muted small mb-0">Beban yang tetap ditanggung HFG setelah kompensasi (Debit Note) dan setelah Jual Material (Biaya Scrap yang diakui) atas material yang di-claim (FR-87).</p>
      </div>
      <div>
        <a href="<?= site_url('claim_material') ?>" class="btn btn-outline-primary btn-sm">
          <i class="fa fa-envelope-o me-1"></i> Ke Daftar Claim Material
        </a>
      </div>
    </div>
  </div>

  <div class="cl-card p-3">
    <div class="table-responsive">
      <table class="table cl-table">
        <thead>
          <tr>
            <th>Jenis</th>
            <th>No. Claim</th>
            <th>Nama Supplier</th>
            <th>Nama Material</th>
            <th>No. Coil Packing List</th>
            <th>Asal</th>
            <th class="text-end">Berat (Kg)</th>
            <th class="text-end">Biaya Material (Rp)</th>
            <th class="text-end">Nilai Debit Note (Rp)</th>
            <th class="text-end">Nilai Material KW (Rp)</th>
            <th class="text-end">Beban Ditanggung HFG (Rp)</th>
            <th>Akun Beban</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $tot_biaya = 0;
            $tot_dn = 0;
            $tot_kw = 0;
            $tot_beban = 0;

            if (!empty($report_data)):
              foreach ($report_data as $r):
                $tot_biaya += (float)$r['biaya_material'];
                $tot_dn    += (float)($r['nilai_kompensasi'] ?? 0);
                $tot_kw    += (float)($r['nilai_material_kw'] ?? 0);
                $tot_beban += (float)$r['beban_ditanggung_hfg'];
          ?>
            <tr>
              <td>
                <span class="badge <?= $r['jenis'] === 'Debit Note' ? 'badge-dn' : 'badge-jual' ?>">
                  <?= htmlspecialchars($r['jenis']) ?>
                </span>
              </td>
              <td><code><?= htmlspecialchars($r['no_claim']) ?></code></td>
              <td><strong><?= htmlspecialchars($r['nama_supplier']) ?></strong></td>
              <td><?= htmlspecialchars($r['nama_material']) ?></td>
              <td><code><?= htmlspecialchars($r['no_coil_packing_list']) ?></code></td>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($r['asal']) ?></span></td>
              <td class="text-end fw-semibold"><?= number_format($r['berat_kg'], 2, ',', '.') ?></td>
              <td class="text-end"><?= number_format($r['biaya_material'], 2, ',', '.') ?></td>
              <td class="text-end"><?= $r['nilai_kompensasi'] !== null ? number_format($r['nilai_kompensasi'], 2, ',', '.') : '-' ?></td>
              <td class="text-end"><?= $r['nilai_material_kw'] !== null ? number_format($r['nilai_material_kw'], 2, ',', '.') : '-' ?></td>
              <td class="text-end fw-bold text-danger"><?= number_format($r['beban_ditanggung_hfg'], 2, ',', '.') ?></td>
              <td><span class="badge bg-secondary"><?= htmlspecialchars($r['akun_beban']) ?></span></td>
            </tr>
          <?php
              endforeach;
            else:
          ?>
            <tr>
              <td colspan="12" class="text-center py-4 text-muted">
                Belum ada data claim berjalur Kompensasi (Debit Note atau Jual Material).
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
        <?php if (!empty($report_data)): ?>
        <tfoot>
          <tr>
            <td colspan="7" class="text-end">Total:</td>
            <td class="text-end">Rp <?= number_format($tot_biaya, 2, ',', '.') ?></td>
            <td class="text-end">Rp <?= number_format($tot_dn, 2, ',', '.') ?></td>
            <td class="text-end">Rp <?= number_format($tot_kw, 2, ',', '.') ?></td>
            <td class="text-end text-danger">Rp <?= number_format($tot_beban, 2, ',', '.') ?></td>
            <td></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>

    <p class="text-muted small mt-3 mb-0">
      * <b>Beban Ditanggung HFG</b>: untuk Debit Note = Biaya Material &minus; Nilai Kompensasi (tanpa PPN); untuk Jual Material = Biaya Scrap yang diakui (Biaya Material &minus; Material KW). Retur dan Lanjut Produksi tidak ditampilkan karena tidak mengakui kompensasi atau kerugian (keputusan user 2026-10-28, G5). Biaya material = Berat &times; Harga Beli/Kg.
    </p>
  </div>
</div>

