<!DOCTYPE html>
<html>

<head>
    <title>Print QR Label</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            .page-break {
                page-break-after: always;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }

        .label-container {
            width: 350px;
            /* Sesuaikan dengan ukuran kertas label Anda */
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 20px;
            display: inline-block;
            vertical-align: top;
            margin-right: 10px;
        }

        .header-label {
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }

        .qr-code {
            float: right;
            margin-left: 10px;
        }

        .info-table td {
            vertical-align: top;
            font-size: 11px;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body onload="window.print();">
    <div class="no-print" style="background: #fdfd96; padding: 10px; margin-bottom: 20px;">
        <button onclick="window.print()">Print Now</button>
        <p><i>Pastikan printer label sudah siap. Tekan Print untuk mencetak semua label yang dipilih.</i></p>
    </div>

    <?php foreach ($results as $row): ?>
        <div class="label-container">
            <div class="header-label">PACKING LIST / COIL INFO</div>

            <div class="qr-code">
                <?php
                // $qr_content = "ROS:" . $row['no_ros'] . "|COIL:" . $row['no_coil'] . "|W:" . $row['berat_bersih'];
                $qr_content = $row['kode_internal'];
                $url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_content) . "&choe=UTF-8";
                ?>
                <img src="<?= $url ?>" alt="QR Code">
            </div>

            <table class="info-table">
                <tr>
                    <td><b>No. ROS</b></td>
                    <td>: <?= $row['no_ros'] ?></td>
                </tr>
                <tr>
                    <td><b>Supplier</b></td>
                    <td>: <?= $row['nm_supplier'] ?></td>
                </tr>
                <tr>
                    <td><b>Material</b></td>
                    <td>: <?= $row['trade_name'] ?></td>
                </tr>
                <tr>
                    <td><b>Kode Internal</b></td>
                    <td>: <?= $row['kode_internal'] ?></td>
                </tr>
                <tr>
                    <td><b>Net Weight</b></td>
                    <td>: <?= number_format($row['berat_bersih'], 2) ?> Kg</td>
                </tr>
                <tr>
                    <td><b>AWB/BL</b></td>
                    <td>: <?= $row['awb_bl_number'] ?></td>
                </tr>
            </table>
            <div class="clear"></div>
        </div>
    <?php endforeach; ?>

</body>

</html>