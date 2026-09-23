<!DOCTYPE html>
<html>
<head>
    <title>PR Asset Print - <?= $no_pr; ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header-title { text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body onload="window.print();">
    <div class="header-title">PURCHASE REQUEST (PR) ASSET</div>
    
    <table style="border: none; margin-bottom: 15px;">
        <tr style="border: none;">
            <td style="border: none; width: 15%;"><strong>NO PR</strong></td>
            <td style="border: none; width: 35%;">: <?= $no_pr; ?></td>
            <td style="border: none; width: 15%;"><strong>Tanggal PR</strong></td>
            <td style="border: none; width: 35%;">: <?= isset($result_header['tgl_pr']) ? date('d F Y', strtotime($result_header['tgl_pr'])) : date('d F Y'); ?></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Barang / Asset</th>
                <th width="10%">Qty</th>
                <th width="20%">Nilai PR (IDR)</th>
                <th width="15%">Tgl Dibutuhkan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($result)) {
                $no = 1;
                foreach ($result as $row) {
                    echo "<tr>";
                    echo "<td class='text-center'>" . $no++ . "</td>";
                    echo "<td>" . strtoupper($row['nm_barang']) . "</td>";
                    echo "<td class='text-center'>" . number_format($row['qty']) . "</td>";
                    echo "<td class='text-right'>" . number_format($row['nilai_pr']) . "</td>";
                    echo "<td class='text-center'>" . date('d M Y', strtotime($row['tgl_dibutuhkan'])) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>Data tidak ditemukan</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
