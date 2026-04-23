<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing List - <?= isset($header_ros['id']) ? $header_ros['id'] : '-' ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            padding: 30px;
            color: #000;
        }

        .header-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-title h4 {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .header-title p {
            font-size: 12px;
            color: #555;
            margin: 0;
        }

        .info-table td {
            padding: 4px 8px;
            font-size: 13px;
            vertical-align: top;
        }

        .info-table td:first-child {
            font-weight: 600;
            white-space: nowrap;
            width: 170px;
        }

        .info-table td:nth-child(2) {
            width: 10px;
        }

        .divider {
            border-top: 2px solid #000;
            margin: 15px 0;
        }

        .divider-thin {
            border-top: 1px solid #ccc;
            margin: 10px 0;
        }

        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        table.detail-table th {
            background-color: #2c3e50;
            color: #fff;
            text-align: center;
            padding: 7px 8px;
            border: 1px solid #000;
            font-size: 12px;
        }

        table.detail-table td {
            padding: 6px 8px;
            border: 1px solid #aaa;
            vertical-align: middle;
        }

        table.detail-table tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        table.detail-table tfoot td {
            border: 1px solid #000;
            background-color: #ecf0f1;
            font-weight: bold;
            padding: 7px 8px;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge-ros {
            display: inline-block;
            background: #2c3e50;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .no-print {
            margin-bottom: 20px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 15px;
            }

            table.detail-table th {
                background-color: #2c3e50 !important;
                -webkit-print-color-adjust: exact;
            }

            table.detail-table tfoot td {
                background-color: #ecf0f1 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <?php
    function fmt_date($val)
    {
        if (empty($val) || $val === '0000-00-00') return '-';
        return date('d-M-Y', strtotime($val));
    }

    $no_ros        = isset($header_ros['id'])            ? $header_ros['id']            : '-';
    $no_po         = isset($header_ros['no_po'])         ? str_replace(',', ', ', $header_ros['no_po']) : '-';
    $no_surat      = isset($header_ros['no_surat'])      ? $header_ros['no_surat']      : '-';
    $nm_supplier   = isset($header_ros['nm_supplier'])   ? $header_ros['nm_supplier']   : '-';
    $awb_number    = isset($header_ros['awb_bl_number']) && $header_ros['awb_bl_number'] !== '' ? $header_ros['awb_bl_number'] : '-';
    $awb_date      = fmt_date(isset($header_ros['awb_bl_date'])   ? $header_ros['awb_bl_date']   : '');
    $eta_warehouse = fmt_date(isset($header_ros['eta_warehouse']) ? $header_ros['eta_warehouse'] : '');
    $ata_pod       = fmt_date(isset($header_ros['ata_pod'])       ? $header_ros['ata_pod']       : '');
    $ttl_berat_kotor  = array_sum(array_column($detail_ros ?? [], 'berat_kotor'));
    $ttl_berat_bersih = array_sum(array_column($detail_ros ?? [], 'berat_bersih'));
    ?>

    <!-- Tombol Aksi -->
    <div class="no-print">
        <button class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="fa fa-print"></i> Print
        </button>
        <button class="btn btn-secondary btn-sm" onclick="window.close()">
            &times; Close
        </button>
    </div>

    <!-- logo dan judul -->
    <div style="position: relative; display: flex; align-items: center; margin-bottom: 20px; min-height: 120px;">
        <div style="flex: 0 0 auto;">
            <img src="<?= base_url('assets/images/logohfg.png') ?>"
                alt="Logo"
                style="height: 100px; width: auto;">
        </div>
        <div style="position: absolute; left: 0; right: 0; text-align: center; pointer-events: none;">
            <h4 style="font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 4px 0;">
                Packing List
            </h4>
            <p style="font-size: 14px; color: #555; margin: 0;">Report of Shipment</p>
        </div>
    </div>

    <!-- Info Header -->
    <div style="display: flex; justify-content: flex-start; gap: 80px; margin-bottom: 10px;">

        <!-- Kiri -->
        <div>
            <table style="border-collapse: collapse; font-size: 13px;">
                <tr>
                    <td style="white-space: nowrap; padding: 3px 6px 3px 0; font-weight: 600;">No. ROS</td>
                    <td style="padding: 3px 6px 3px 0;">:</td>
                    <td style="padding: 3px 0; white-space: nowrap;"><span class="badge-ros"><?= $no_ros ?></span></td>
                </tr>
                <tr>
                    <td style="white-space: nowrap; padding: 3px 6px 3px 0; font-weight: 600;">No. PO</td>
                    <td style="padding: 3px 6px 3px 0;">:</td>
                    <td style="padding: 3px 0; white-space: nowrap;"><?= $no_surat ?></td>
                </tr>
                <tr>
                    <td style="white-space: nowrap; padding: 3px 6px 3px 0; font-weight: 600;">Supplier</td>
                    <td style="padding: 3px 6px 3px 0;">:</td>
                    <td style="padding: 3px 0;"><?= $nm_supplier ?></td>
                </tr>
            </table>
        </div>

        <!-- Kanan -->
        <div>
            <table style="border-collapse: collapse; font-size: 13px;">
                <tr>
                    <td style="white-space: nowrap; padding: 3px 6px 3px 0; font-weight: 600;">AWB / BL Number</td>
                    <td style="padding: 3px 6px 3px 0;">:</td>
                    <td style="padding: 3px 0; white-space: nowrap;"><?= $awb_number ?></td>
                </tr>
                <tr>
                    <td style="white-space: nowrap; padding: 3px 6px 3px 0; font-weight: 600;">AWB / BL Date</td>
                    <td style="padding: 3px 6px 3px 0;">:</td>
                    <td style="padding: 3px 0; white-space: nowrap;"><?= $awb_date ?></td>
                </tr>
                <tr>
                    <td style="white-space: nowrap; padding: 3px 6px 3px 0; font-weight: 600;">ETA Warehouse</td>
                    <td style="padding: 3px 6px 3px 0;">:</td>
                    <td style="padding: 3px 0; white-space: nowrap;"><?= $eta_warehouse ?></td>
                </tr>
                <tr>
                    <td style="white-space: nowrap; padding: 3px 6px 3px 0; font-weight: 600;">ATA POD</td>
                    <td style="padding: 3px 6px 3px 0;">:</td>
                    <td style="padding: 3px 0; white-space: nowrap;"><?= $ata_pod ?></td>
                </tr>
            </table>
        </div>

    </div>

    <div class="divider"></div>

    <!-- Tabel Detail -->
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 20px;">No.</th>
                <th style="width: 200px;">Nama Barang</th>
                <th style="width: 30px;">Satuan</th>
                <th style="width: 80px;">Length</th>
                <th style="width: 100px;">No. Coil</th>
                <th style="width: 100px;">Kode Internal</th>
                <th style="width: 100px;">Berat Kotor (kg)</th>
                <th style="width: 100px;">Berat Bersih (kg)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($detail_ros)) : ?>
                <?php $no = 1;
                foreach ($detail_ros as $item) : ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($item['trade_name']) ?></td>
                        <td class="text-center"><?= ucfirst($item['unit_satuan']) ?></td>
                        <td class="text-end"><?= number_format($item['length'], 2) ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['no_coil']) ?></td>
                        <td class="text-center">
                            <?= !empty($item['kode_internal']) ? htmlspecialchars($item['kode_internal']) : '-' ?>
                        </td>
                        <td class="text-end"><?= number_format($item['berat_kotor'], 2) ?></td>
                        <td class="text-end"><?= number_format($item['berat_bersih'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="text-center" style="color: #999; padding: 20px;">
                        No data available
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end">Total</td>
                <td class="text-end"><?= number_format($ttl_berat_kotor, 2) ?></td>
                <td class="text-end"><?= number_format($ttl_berat_bersih, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer Cetak -->
    <div style="margin-top: 40px; display: flex; justify-content: flex-end; font-size: 12px; color: #888;">
        <span>Dicetak pada: <?= date('d-M-Y H:i') ?></span>
    </div>

    <script>
        window.print();
    </script>
</body>

</html>