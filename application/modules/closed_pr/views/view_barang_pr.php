<div class="mb-3">
    <table class="table table-sm table-borderless" style="width: auto;">
        <tr>
            <td class="fw-bold" style="width: 120px;">No. PR</td>
            <td class="text-center fw-bold" style="width: 15px;">:</td>
            <td class="fw-bold text-danger"><?= htmlspecialchars($no_pr ?? '-') ?></td>
        </tr>
        <?php if (!empty($kategori_pr)) : ?>
        <tr>
            <td class="fw-bold">Kategori PR</td>
            <td class="text-center fw-bold">:</td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary fw-bold"><?= htmlspecialchars($kategori_pr) ?></span></td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle table-striped table-bordered mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-center" style="width: 40px;">No</th>
                <th class="text-start">Nama Barang / Material</th>
                <th class="text-end" style="width: 110px;">Qty</th>
                <th class="text-end" style="width: 120px;">Qty Packing</th>
                <th class="text-center" style="width: 100px;">Unit</th>
                <th class="text-center" style="width: 110px;">Unit Packing</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if (!empty($list_barang)) {
                foreach ($list_barang as $item) {
                    $konversi = (!empty($item->nilai_konversi) && $item->nilai_konversi > 0) ? $item->nilai_konversi : 1;
                    $qty = $item->qty ?? 0;
                    $qty_packing = ($qty / $konversi);

                    echo '<tr>';
                    echo '<td class="text-center">' . $no . '</td>';
                    echo '<td class="text-start fw-semibold">' . htmlspecialchars($item->nm_barang ?? '-') . '</td>';
                    echo '<td class="text-end fw-bold">' . number_format($qty, 2) . '</td>';
                    echo '<td class="text-end fw-bold text-primary">' . number_format($qty_packing, 2) . '</td>';
                    echo '<td class="text-center"><span class="badge bg-light text-dark border">' . ucfirst($item->unit ?? '-') . '</span></td>';
                    echo '<td class="text-center"><span class="badge bg-light text-dark border">' . ucfirst($item->unit_packing ?? '-') . '</span></td>';
                    echo '</tr>';
                    $no++;
                }
            } else {
                echo '<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada rincian material pada PR ini</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
