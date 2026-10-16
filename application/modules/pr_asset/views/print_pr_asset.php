<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PR Asset Print - <?= $no_pr; ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 15mm;
        }
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 15px; color: #000; }
        .header-wrap { display: flex; align-items: flex-start; margin-bottom: 8px; }
        .header-logo { min-width: 80px; }
        .header-company { flex: 1; padding-left: 10px; font-size: 11px; line-height: 1.4; }
        .header-company strong { font-size: 13px; }
        .header-title { text-align: right; min-width: 220px; }
        .header-title h2 { margin: 0; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header-title .doc-no { font-size: 11px; color: #333; margin-top: 4px; }
        hr { border: none; border-top: 2px solid #000; margin: 8px 0 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; }
        th { background-color: #f2f2f2; text-align: center; }
        .no-border td, .no-border th { border: none !important; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .no-print { margin-bottom: 15px; }
        .btn-print { display: inline-block; padding: 6px 16px; background: #1a5276; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">&#128438; Cetak</button>
        <button class="btn-print" style="background:#555;" onclick="window.close()">Tutup</button>
    </div>

    <!-- Header Perusahaan -->
    <div class="header-wrap">
        <div class="header-logo">
            <img src="<?= base_url('assets/images/iconhfg.png') ?>" alt="Logo" width="60" height="60">
        </div>
        <div class="header-company">
            <strong>PT HARAPAN FAJAR GEMILANG</strong><br>
            Jl. Daan Mogot Km. 11 No. 45 Kedaung Kali Angke<br>
            Cengkareng, Jakarta Barat 11710<br>
            Indonesia
        </div>
        <div class="header-title">
            <h2>Purchase Request (PR) Asset</h2>
            <div class="doc-no">No: <strong><?= $no_pr; ?></strong></div>
        </div>
    </div>
    <hr>
    
    <table class="no-border" style="margin-bottom: 12px;">
        <tr>
            <td style="width: 15%;"><strong>NO PR</strong></td>
            <td style="width: 35%;">: <?= $no_pr; ?></td>
            <td style="width: 15%;"><strong>Tanggal PR</strong></td>
            <td style="width: 35%;">: <?= isset($result_header['tgl_pr']) ? date('d F Y', strtotime($result_header['tgl_pr'])) : date('d F Y'); ?></td>
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
