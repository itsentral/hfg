<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Budget Stock.xls");
?>
<table width="100%" border="1">
    <thead>
        <tr>
            <th>#</th>
            <th>Item Name</th>
            <th>Spesifikasi</th>
            <th>1 Month Requirement</th>
            <th>Product Unit</th>
            <th>Price Reference</th>
            <th>Total Price</th>
            <th>Price Reference After</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;
        foreach ($data_detail as $item) {
            $no++;

            echo '
                <tr>
                    <td>' . $no . '</td>
                    <td>' . $item->stock_name . '</td>
                    <td>' . $item->spec . '</td>
                    <td>' . $item->kebutuhan_month . '</td>
                    <td>' . strtoupper((string) $item->code) . '</td>
                    <td>' . $item->price_reference . '</td>
                    <td>' . $item->total_price . '</td>
                    <td></td>
                </tr>
            ';
        }
        ?>
    </tbody>
</table>