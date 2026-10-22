<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Harga Resmi HFG - <?= date('d/m/Y') ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0 0 5px 0; color: #2c3e50; font-size: 18px; }
        .header p { margin: 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; font-size: 11px; }
        th { background-color: #f4f6f9; color: #2c3e50; font-weight: bold; text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .price-highlight { font-weight: bold; color: #27ae60; }
        .footer { margin-top: 25px; font-size: 10px; color: #888; border-top: 1px solid #eee; padding-top: 8px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; background: #e8f4fd; padding: 10px; border-radius: 4px;">
        <button onclick="window.print()" style="padding: 6px 14px; background: #3c8dbc; color: #fff; border: none; border-radius: 3px; cursor: pointer; font-weight: bold;">
            Cetak / Simpan PDF
        </button>
        <span style="margin-left: 10px; font-size: 12px; color: #555;">Gunakan tombol cetak ini untuk mencetak katalog atau menyimpannya sebagai PDF.</span>
    </div>

    <div class="header">
        <table style="border:none; width:100%; margin:0;">
            <tr style="border:none;">
                <td style="border:none; padding:0;">
                    <h2>PT. HARAPAN FORWARDING GRAHA (HFG)</h2>
                    <p><strong>DAFTAR HARGA RESMI PRODUK (PRODUCT CATALOG)</strong></p>
                    <p>Status: RESMI AKTIF | Tanggal Cetak: <?= html_escape($date) ?></p>
                </td>
                <td style="border:none; padding:0; text-align:right; vertical-align:top;">
                    <?php if ($catalog): ?>
                        <strong>No. Katalog: <?= html_escape($catalog->header->no_catalog) ?></strong><br>
                        <span style="font-size:10px;">Rilis: <?= date('d/m/Y', strtotime($catalog->header->released_at)) ?></span><br>
                        <span style="font-size:10px;">Ref Pricelist: <?= html_escape($catalog->header->no_pricelist ? $catalog->header->no_pricelist : '-') ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 25%;">Nama Produk & Kode</th>
                <th style="width: 12%;" class="text-center">Varian (Uniq ID)</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 8%;" class="text-center">Ukuran (m)</th>
                <th style="width: 8%;" class="text-right">Berat (Kg/m)</th>
                <th style="width: 14%;" class="text-right">Price List / Meter</th>
                <th style="width: 14%;" class="text-right">Price List / Kg</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): $no = 1; foreach ($items as $item): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td>
                        <strong><?= html_escape($item->nm_product ? $item->nm_product : $item->code_lv4) ?></strong>
                        <div style="font-size:9px; color:#777;"><?= html_escape($item->code_lv4) ?></div>
                    </td>
                    <td class="text-center"><strong><?= html_escape($item->no_uniq_id) ?></strong></td>
                    <td><?= html_escape($item->nm_category ? $item->nm_category : '-') ?></td>
                    <td class="text-center"><?= number_format((float)$item->ukuran_product, 2) ?></td>
                    <td class="text-right"><?= number_format((float)$item->berat_per_meter, 4) ?></td>
                    <td class="text-right price-highlight">Rp <?= number_format((float)$item->price_list_meter, 2) ?></td>
                    <td class="text-right">Rp <?= number_format((float)$item->price_list_kg, 2) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>* Harga di atas merupakan harga resmi katalog HFG dan belum termasuk diskon khusus atau penyesuaian negosiasi tambahan yang disetujui General Manager.</p>
    </div>
</body>
</html>
