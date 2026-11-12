<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Request Mutasi <?= isset($data->kd_mutasi) ? htmlspecialchars($data->kd_mutasi) : '' ?></title>
    <style>
        @page {
            size: A4;
            margin: 10mm 15mm;
        }

        @media print {

            html,
            body {
                width: 210mm;
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                font-size: 10px;
            }

            .no-print {
                display: none !important;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 5px 7px;
            }

            .no-border td,
            .no-border th {
                border: none !important;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .bold {
                font-weight: bold;
            }

            tr,
            td,
            th {
                page-break-inside: avoid;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 10mm 15mm;
            margin: 0;
            color: #000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px 7px;
        }

        .no-border td,
        .no-border th {
            border: none !important;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .header-wrap {
            display: flex;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .header-logo {
            min-width: 110px;
        }

        .header-company {
            flex: 1;
            padding-left: 10px;
            font-size: 11px;
            line-height: 1.4;
        }

        .header-title {
            text-align: right;
            min-width: 180px;
        }

        .header-title h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-title .doc-no {
            font-size: 11px;
            color: #333;
            margin-top: 4px;
        }

        hr {
            border: none;
            border-top: 2px solid #000;
            margin: 6px 0 10px;
        }

        .info-table td {
            padding: 3px 6px;
            font-size: 11px;
        }

        .info-table td:first-child {
            width: 160px;
            font-weight: bold;
        }

        .info-table td:nth-child(2) {
            width: 10px;
        }

        .detail-table thead th {
            background-color: #1a5276;
            color: #fff;
            text-align: center;
            font-size: 11px;
        }

        .detail-table tbody td {
            font-size: 11px;
        }

        .terbilang-box {
            border: 1px solid #000;
            padding: 6px 10px;
            margin-top: 10px;
            font-size: 11px;
            font-style: italic;
        }

        .sign-table {
            margin-top: 30px;
        }

        .sign-table td {
            border: none;
            text-align: center;
            width: 33%;
            padding: 0 10px;
            font-size: 11px;
        }

        .sign-line {
            height: 55px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-0 {
            background: #f39c12;
            color: #fff;
        }

        .status-1 {
            background: #27ae60;
            color: #fff;
        }

        .status-2 {
            background: #e74c3c;
            color: #fff;
        }

        .btn-print {
            display: inline-block;
            margin-bottom: 15px;
            padding: 6px 18px;
            background: #1a5276;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-print:hover {
            background: #154360;
        }
    </style>
</head>

<body>

    <?php if (!isset($data) || empty($data)): ?>
        <div class="no-print" style="padding:20px; color:#721c24; background-color:#f8d7da; border:1px solid #f5c6cb; border-radius:4px; margin-bottom:15px;">
            <strong>Error:</strong> Data request mutasi tidak ditemukan.
        </div>
        <button class="btn-print" style="background:#555;" onclick="history.back()">&#8592; Kembali</button>
</body>

</html>
<?php return;
    endif; ?>

<!-- Tombol Print -->
<div class="no-print" style="margin-bottom:12px;">
    <button class="btn-print" onclick="window.print()">&#128438; Cetak</button>
    <button class="btn-print" style="background:#555;" onclick="history.back()">&#8592; Kembali</button>
</div>

<!-- ===== HEADER ===== -->
<div class="header-wrap">
    <div class="header-logo">
        <img src="<?= base_url('assets/images/iconhfg.png') ?>" alt="Logo" width="60" height="60">
    </div>
    <div class="header-company">
        <strong style="font-size:13px;">PT HARAPAN FAJAR GEMILANG</strong><br>
        Jl. Daan Mogot Km. 11 No. 45 Kedaung Kali Angke<br>
        Cengkareng, Jakarta Barat 11710<br>
        Indonesia
    </div>
    <div class="header-title">
        <h2>Request Mutasi Bank</h2>
        <div class="doc-no">No: <strong><?= htmlspecialchars($data->kd_mutasi ?? '') ?></strong></div>
    </div>
</div>
<hr>

<!-- ===== INFO DOKUMEN ===== -->
<?php
$statusLabel = ['0' => 'Menunggu Approval', '1' => 'Approved', '2' => 'Ditolak'];
$statusClass = ['0' => 'status-0', '1' => 'status-1', '2' => 'status-2'];
$st = isset($data->status_approve) ? $data->status_approve : '0';
$tgl_req = !empty($data->tgl_request) ? date('d F Y', strtotime($data->tgl_request)) : '-';
$nilai_req = (float)($data->nilai_request ?? 0);

// Ambil nama mata uang dari tabel mata_uang yang berelasi dengan tr_request_mutasi.mata_uang = mata_uang.kode
$nama_mata_uang = '';
if (!empty($data->nama_mata_uang)) {
    $nama_mata_uang = $data->nama_mata_uang;
} elseif (!empty($data->mata_uang)) {
    $ci = &get_instance();
    $q_mu = $ci->db->select('mata_uang')->from('mata_uang')->where('kode', $data->mata_uang)->get()->row();
    $nama_mata_uang = ($q_mu && !empty($q_mu->mata_uang)) ? $q_mu->mata_uang : $data->mata_uang;
} else {
    $nama_mata_uang = 'Rupiah';
}
?>
<table class="no-border info-table" style="margin-bottom:12px;">
    <tr>
        <td>No. Request</td>
        <td>:</td>
        <td><strong><?= htmlspecialchars($data->kd_mutasi ?? '') ?></strong></td>
        <td style="width:30px;"></td>
        <td style="width:120px; font-weight:bold;">Status</td>
        <td style="width:10px;">:</td>
        <td>
            <span class="status-badge <?= $statusClass[$st] ?? 'status-0' ?>">
                <?= $statusLabel[$st] ?? '-' ?>
            </span>
        </td>
    </tr>
    <tr>
        <td>Tanggal Request</td>
        <td>:</td>
        <td><?= $tgl_req ?></td>
        <td></td>
        <td style="font-weight:bold;">Mata Uang</td>
        <td>:</td>
        <td><?= htmlspecialchars($data->mata_uang) ?></td>
    </tr>
    <tr>
        <td>Keterangan</td>
        <td>:</td>
        <td colspan="5"><?= htmlspecialchars($data->keterangan ?? '-') ?></td>
    </tr>
    <?php if ($st == '2' && !empty($data->alasan)): ?>
        <tr>
            <td>Alasan Ditolak</td>
            <td>:</td>
            <td colspan="5" style="color:#c0392b;"><?= htmlspecialchars($data->alasan) ?></td>
        </tr>
    <?php endif; ?>
</table>

<!-- ===== DETAIL REQUEST ===== -->
<table class="detail-table">
    <thead>
        <tr>
            <th class="text-center" width="5%">No</th>
            <th width="35%">Keterangan</th>
            <th width="25%">Bank Asal</th>
            <th width="25%">Bank Tujuan</th>
            <th class="text-right" width="10%">Nilai Request</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">1</td>
            <td><?= htmlspecialchars($data->keterangan ?? '-') ?></td>
            <td>
                <?= htmlspecialchars($data->nama_bank_asal ?? ($data->bank_asal ?? '-')) ?>
                <?php if (!empty($data->bank_asal)): ?>
                    <br><small style="color:#555;"><?= htmlspecialchars($data->bank_asal) ?></small>
                <?php endif; ?>
            </td>
            <td>
                <?= htmlspecialchars($data->nama_bank_tujuan ?? ($data->bank_tujuan ?? '-')) ?>
                <?php if (!empty($data->bank_tujuan)): ?>
                    <br><small style="color:#555;"><?= htmlspecialchars($data->bank_tujuan) ?></small>
                <?php endif; ?>
            </td>
            <td class="text-right"><strong><?= number_format($nilai_req, 2) ?></strong></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-right bold">Total Request</td>
            <td class="text-right bold"><?= number_format($nilai_req, 2) ?></td>
        </tr>
    </tfoot>
</table>

<!-- ===== TERBILANG ===== -->
<?php
$terbilang_text = '';
if (!empty($data->terbilang)) {
    $terbilang_text = ucfirst($data->terbilang);
} elseif (function_exists('ynz_terbilang') && $nilai_req > 0) {
    $terbilang_text = ynz_terbilang($nilai_req) . ' ' . strtoupper($nama_mata_uang);
}
?>
<div class="terbilang-box">
    Terbilang: <strong><?= htmlspecialchars($terbilang_text) ?></strong>
</div>

<!-- ===== APPROVAL INFO ===== -->
<?php if ($st == '1' && !empty($data->approved_by)): ?>
    <table class="no-border info-table" style="margin-top:8px;">
        <tr>
            <td>Disetujui Oleh</td>
            <td>:</td>
            <td><?= htmlspecialchars($data->approved_by) ?> &mdash; <?= !empty($data->approved_on) ? date('d/m/Y H:i', strtotime($data->approved_on)) : '-' ?></td>
        </tr>
    </table>
<?php endif; ?>

<!-- ===== TANDA TANGAN ===== -->
<table class="sign-table" style="margin-top:35px; width:100%;">
    <tr>
        <td class="text-center">Dibuat Oleh</td>
        <td class="text-center">Diperiksa Oleh</td>
        <td class="text-center">Disetujui Oleh</td>
    </tr>
    <tr>
        <td class="sign-line"></td>
        <td class="sign-line"></td>
        <td class="sign-line"></td>
    </tr>
    <tr>
        <td class="text-center">
            ( <strong><?= htmlspecialchars($data->created_by ?? '-') ?></strong> )<br>
            <small><?= !empty($data->created_on) ? date('d/m/Y', strtotime($data->created_on)) : '-' ?></small>
        </td>
        <td class="text-center">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
        <td class="text-center">
            <?php if ($st == '1' && !empty($data->approved_by)): ?>
                ( <strong><?= htmlspecialchars($data->approved_by) ?></strong> )
            <?php else: ?>
                ( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )
            <?php endif; ?>
        </td>
    </tr>
</table>

</body>

</html>