<?php
// Helper kecil untuk format angka & tanggal
if (!function_exists('fmt')) {
    function fmt($n)
    {
        return number_format((float)$n, 0, ',', '.');
    }
}
if (!function_exists('fmt2')) {
    function fmt2($n)
    {
        return number_format((float)$n, 2, ',', '.');
    }
}
if (!function_exists('idDate')) {
    function idDate($d)
    {
        return $d ? date('d M Y', strtotime($d)) : '';
    }
}

// Ambil variabel dari controller
$header        = isset($header) ? $header : (object)[];
$rows          = isset($detail) ? $detail : [];
$data_supplier = isset($data_supplier) ? $data_supplier : (object)[];
$nm_department = isset($nm_department) ? $nm_department : '';
$no_pr         = isset($no_pr) ? $no_pr : '';

$poNumber  = $header->no_surat ?? '';
$poDate    = $header->tanggal ?? ($header->tanggal ?? null);
$terms     = $header->top ?? ($header->top ?? ''); // kalau ada nama TOP
$rate      = $header->rate ?? ''; // opsional
$vendorTax = isset($header->vendor_is_taxable) && $header->vendor_is_taxable ? 'Yes' : '';

// alamat & info supplier
$vendorName    = $data_supplier->nama   ?? ($header->nm_supp ?? '');
$vendorAddress = trim(($data_supplier->address ?? ($header->alamat ?? '')));

// Ship to (alamat perusahaan)
$shipTo = $header->delivery_address;

// Hitung subtotal/diskon/ppn/total
$subtotal = 0;
$totalDiskon = 0;
$ppnPersen = isset($header->ppn_persen) && $header->ppn_persen !== '' ? (float)$header->ppn_persen : 11; // default 11%
foreach ($rows as $it) {
    $qty   = (float)($it->qty ?? 0);
    $price = (float)($it->hargasatuan ?? 0);
    $discP = (float)($it->diskon_persen ?? 0);
    $line  = $qty * $price;
    $discA = $line * $discP / 100;
    $subtotal   += $line - $discA;
    $totalDiskon += $discA;
}
$ppn = round($subtotal * $ppnPersen / 100);
$biayaLain = (float)($header->biaya_lain ?? 0);
$totalOrder = $subtotal + $ppn + $biayaLain;

// terbilang (opsional)
$terbilang = function_exists('rupiah_to_words')
    ? ucfirst(rupiah_to_words($header->total_include_ppn)) . ' Rupiah'
    : '';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Purchase Order</title>
    <style>
        @page {
            size: A4;
            margin: 5mm;
        }

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 5mm;
                font-family: Arial, sans-serif;
                font-size: 16px;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 4px;
            }

            .no-border td {
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

            table,
            tr,
            td,
            th {
                page-break-inside: avoid;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 15px;
            padding: 5mm;
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
        }

        .no-border td {
            border: none;
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

        .h40 {
            height: 40px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div style="display: flex;">
        <div class="text-left" style="width: 140px;">
            <img src="<?= base_url('assets/images/logo_sbf.png') ?>" alt="" width="100" height="60">
        </div>
        <div style="width: 60%;">
            <strong>PT Surya Bangun Fajar</strong><br>
            Jl. Kalijaga No.35 Kel. Pegambiran Kec. Lemahwungkuk<br>
            Kota Cirebon Jawa Barat 45113<br>
            Indonesia
        </div>
        <div class="text-right" style="width: 40%;">
            <h2>PURCHASE ORDER</h2>
        </div>
    </div>
    <hr>

    <!-- Info Vendor & Shipping -->
    <div style="display: flex;">
        <div style="width: 50%;">
            <table class="no-border">
                <tr>
                    <td style="width: 60px; vertical-align: top;"><strong>Vendor</strong></td>
                    <td>
                        <?= strtoupper($vendorName)  ?> <br>
                        <?= nl2br($vendorAddress) ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><strong>Ship To</strong></td>
                    <td>
                        <?= nl2br($shipTo) ?>
                    </td>
                </tr>
            </table>
        </div>
        <div style="width: 50%;">
            <table class="no-border">
                <tr>
                    <td>PO Date :<br>
                        <?= idDate($poDate) ?></td>
                    <td>PO Number :<br>
                        <?= htmlspecialchars($poNumber) ?></td>
                </tr>
                <tr>
                    <td>Terms : <br>
                        <?= $terms ?></td>

                    <td>FOB : <br>
                    </td>
                </tr>
                <tr>
                    <td>Vendor is Taxable :<br>
                        <?= $vendorTax ?></td>

                    <td>Rate : <br>
                        <?= $rate ?></td>
                </tr>
            </table>
        </div>
    </div>
    <br>

    <!-- Items -->
    <table class="mt-8">
        <thead>
            <tr class="text-center">
                <th style="width:10%;">Item</th>
                <th>Description</th>
                <th style="width:10%;">Qty</th>
                <th style="width:15%;">Unit Price</th>
                <th style="width:8%;">Disc</th>
                <th style="width:18%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach ($rows as $r):
                $code  = $r->code ?? '';
                $desc  = trim(($r->nama ?? $r->namamaterial ?? '') . ($r->description ? ' ' . $r->description : ''));
                $qty   = (float)($r->qty ?? 0);
                $price = (float)($r->hargasatuan ?? 0);
                $discP = (float)($r->diskon_persen ?? 0);
                $line  = $qty * $price;
                $discA = $line * $discP / 100;
                $amount = $line - $discA;
            ?>
                <tr>
                    <td class="text-center"><?= htmlspecialchars($code) ?></td>
                    <td><?= nl2br(htmlspecialchars($desc)) ?></td>
                    <td class="text-center"><?= fmt($qty) ?></td>
                    <td class="text-right"><?= fmt2($price) ?></td>
                    <td class="text-center"><?= $discP ? fmt2($discP) . '%' : '0' ?></td>
                    <td class="text-right"><?= fmt2($amount) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data item</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Terbilang & Summary -->
    <table class="mt-8">
        <tr>
            <td style="width:65%;">
                <div>Terbilang :</div>
                <div class="box" style="min-height:26px;"><em><strong><?= htmlspecialchars($terbilang) ?></strong></em></div>

                <div class="box mt-8" style="white-space:pre-line; min-height:80px;">
                    <strong>Keterangan :</strong>
                    <?= $header->note ?>
                </div>
            </td>
            <td style="width:35%;">
                <table>
                    <tr>
                        <td class="text-left" style="width:50%;">Sub Total</td>
                        <td class="text-right" style="width:50%;"><?= fmt2($subtotal) ?></td>
                    </tr>
                    <tr>
                        <td class="text-left">Diskon</td>
                        <td class="text-right"><?= $totalDiskon ? fmt2($totalDiskon) : '0' ?></td>
                    </tr>
                    <tr>
                        <td class="text-left">PPN (<?= rtrim(rtrim(number_format($ppnPersen, 2), '0'), ',') ?>%)</td>
                        <td class="text-right"><?= fmt2($header->total_ppn) ?></td>
                    </tr>
                    <tr>
                        <td class="text-left">Biaya Lain-lain</td>
                        <td class="text-right"><?= $biayaLain ? fmt2($biayaLain) : '0' ?></td>
                    </tr>
                    <tr>
                        <td class="text-left"><strong>Total Order</strong></td>
                        <td class="text-right"><strong><?= fmt2($header->total_include_ppn) ?></strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
    <!-- Signature -->
    <table class="mt-8 no-border">
        <tr class="text-center">
            <td>Prepared By,</td>
            <td>Preview By,</td>
            <td>Approved By,</td>
        </tr>
        <tr>
            <td class="h40"></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="text-center">
            <td><?= strtoupper($this->auth->user_name()) ?></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr class="text-center">
            <td>Date :</td>
            <td>Date :</td>
            <td>Date :</td>
        </tr>
    </table>

    <div class="text-right small" style="margin-top:6px;"><b>Doc. No: FR-PUR-PO</b></div>

</body>

</html>