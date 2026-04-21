<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan — <?= $do->do_no ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; background: #fff; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .company-info h2 { font-size: 18px; font-weight: bold; }
        .company-info p { font-size: 11px; color: #333; }
        .doc-title { text-align: right; }
        .doc-title h3 { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .doc-title .doc-no { font-size: 14px; font-weight: bold; color: #333; }

        /* Info Section */
        .info-section { display: flex; gap: 30px; margin-bottom: 20px; }
        .info-block { flex: 1; }
        .info-block table { width: 100%; border-collapse: collapse; }
        .info-block td { padding: 3px 5px; font-size: 12px; vertical-align: top; }
        .info-block td:first-child { width: 40%; font-weight: bold; }

        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #333; color: #fff; padding: 6px 8px; text-align: center; font-size: 11px; }
        .items-table td { padding: 5px 8px; border: 1px solid #ccc; font-size: 12px; }
        .items-table tr:nth-child(even) td { background: #f9f9f9; }
        .items-table tfoot td { font-weight: bold; background: #eee; border: 1px solid #ccc; padding: 5px 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Weight Summary */
        .weight-summary { border: 1px solid #333; padding: 10px 15px; margin-bottom: 20px; background: #f5f5f5; }
        .weight-summary table { width: 100%; }
        .weight-summary td { padding: 3px 8px; font-size: 12px; }
        .weight-summary td:first-child { font-weight: bold; width: 40%; }

        /* Signature Section */
        .signature-section { display: flex; justify-content: space-between; margin-top: 30px; }
        .signature-box { text-align: center; width: 22%; }
        .signature-box .sign-area { height: 60px; border-bottom: 1px solid #000; margin-bottom: 5px; }
        .signature-box p { font-size: 11px; }

        /* Status Badge */
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .status-shipped { background: #333; color: #fff; }
        .status-approved { background: #28a745; color: #fff; }

        /* Print */
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .container { padding: 10px; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Print Button (no-print) -->
    <div class="no-print" style="margin-bottom:15px;">
        <button onclick="window.print()" style="padding:8px 20px; background:#333; color:#fff; border:none; cursor:pointer; border-radius:4px;">
            &#128438; Cetak Surat Jalan
        </button>
        <a href="javascript:window.close()" style="margin-left:10px; padding:8px 20px; background:#666; color:#fff; text-decoration:none; border-radius:4px;">
            Tutup
        </a>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <h2>PT HFG</h2>
            <p>Jl. Industri No. 1, Kawasan Industri</p>
            <p>Telp: (021) 000-0000</p>
        </div>
        <div class="doc-title">
            <h3>Surat Jalan</h3>
            <div class="doc-no"><?= $do->do_no ?></div>
            <div style="margin-top:5px;">
                <span class="status-badge <?= $do->status === 'Shipped' ? 'status-shipped' : 'status-approved' ?>">
                    <?= $do->status ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-block">
            <table>
                <tr>
                    <td>No Surat Jalan</td>
                    <td>: <?= $do->do_no ?></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: <?= date('d/m/Y', strtotime($do->tgl_delivery)) ?></td>
                </tr>
                <tr>
                    <td>Tgl Cetak</td>
                    <td>: <?= date('d/m/Y H:i') ?></td>
                </tr>
            </table>
        </div>
        <div class="info-block">
            <table>
                <tr>
                    <td>Kepada</td>
                    <td>: <strong><?= htmlspecialchars($do->customer) ?></strong></td>
                </tr>
                <?php if ($do->keterangan): ?>
                <tr>
                    <td>Keterangan</td>
                    <td>: <?= htmlspecialchars($do->keterangan) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Kode Produk</th>
                <th width="30%">Nama Produk</th>
                <th width="15%">Qty</th>
                <th width="15%">Est. Berat (kg)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $i => $d): ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($d->produk_fg) ?></td>
                <td><?= htmlspecialchars($d->nm_produk_fg ?: '-') ?></td>
                <td class="text-right"><?= number_format($d->qty_kirim, 2) ?></td>
                <td class="text-right"><?= number_format($d->estimasi_berat, 3) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right"><?= number_format($total_qty, 2) ?></td>
                <td class="text-right"><?= number_format($total_estimasi, 3) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Weight Summary -->
    <div class="weight-summary">
        <table>
            <tr>
                <td>Total Estimasi Berat</td>
                <td>: <?= number_format($total_estimasi, 3) ?> kg</td>
                <td width="10%"></td>
                <?php if ($berat_aktual !== null): ?>
                <td style="font-weight:bold;">Berat Aktual Timbang</td>
                <td>: <?= number_format($berat_aktual, 3) ?> kg</td>
                <?php endif; ?>
            </tr>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="sign-area"></div>
            <p><strong>Dibuat Oleh</strong></p>
            <p><?= isset($do->nama_created_by) ? $do->nama_created_by : '_______________' ?></p>
        </div>
        <div class="signature-box">
            <div class="sign-area"></div>
            <p><strong>Pengirim</strong></p>
            <p>_______________</p>
        </div>
        <div class="signature-box">
            <div class="sign-area"></div>
            <p><strong>Disetujui Oleh</strong></p>
            <p><?= isset($do->nama_approved_by) && $do->nama_approved_by ? $do->nama_approved_by : '_______________' ?></p>
        </div>
        <div class="signature-box">
            <div class="sign-area"></div>
            <p><strong>Penerima</strong></p>
            <p>_______________</p>
        </div>
    </div>

    <div style="margin-top:20px; font-size:10px; color:#666; text-align:center; border-top:1px solid #ccc; padding-top:8px;">
        Dokumen ini dicetak secara otomatis oleh sistem. <?= $do->do_no ?> — <?= date('d/m/Y H:i:s') ?>
    </div>

</div>
</body>
</html>
