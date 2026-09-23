<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Asset_" . date('Ymd_His') . ".xls");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Asset Export</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h3 style="text-align: center;">DATA ASSET</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Kode Asset</th>
                <th>Nama Asset</th>
                <th>Tanggal Perolehan</th>
                <th>Category</th>
                <th>Department</th>
                <th>Cost Center</th>
                <th>Penyusutan</th>
                <th>Nilai Asset (Acquisition)</th>
                <th>Depresiasi (Tahun)</th>
                <th>Nilai Susut/Bulan (Value)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($data)) {
                $no = 1;
                foreach ($data as $row) {
                    echo "<tr>";
                    echo "<td class='text-center'>" . $no++ . "</td>";
                    echo "<td>" . strtoupper($row['kd_asset']) . "</td>";
                    echo "<td>" . strtoupper($row['nm_asset']) . "</td>";
                    echo "<td class='text-center'>" . $row['tgl_perolehan'] . "</td>";
                    echo "<td>" . strtoupper($row['nm_category']) . "</td>";
                    echo "<td>" . strtoupper($row['nm_dept']) . "</td>";
                    echo "<td>" . strtoupper($row['nm_costcenter']) . "</td>";
                    echo "<td class='text-center'>" . $row['penyusutan'] . "</td>";
                    echo "<td class='text-right'>" . number_format($row['nilai_asset'], 2) . "</td>";
                    echo "<td class='text-center'>" . $row['depresiasi'] . " Tahun</td>";
                    echo "<td class='text-right'>" . number_format($row['value'], 2) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='11' class='text-center'>Data tidak ditemukan</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
