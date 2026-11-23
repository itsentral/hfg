<div class="table-responsive">
    <table id="example1" class="table table-hover table-striped align-middle border rounded" style="width:100%">
        <thead class="table-light">
            <tr>
                <th class="text-center" width="4%">#</th>
                <th class="text-center" width="12%">Product Code</th>
                <th class="text-start">Item Name</th>
                <th class="text-center" width="15%">Warehouse</th>
                <th class="text-end" width="10%">Stock Qty</th>
                <th class="text-end" width="12%">Costbook Value</th>
                <th class="text-end" width="13%">Value Neraca</th>
                <th class="text-center" width="14%">Update Terakhir</th>
                <th class="text-center" width="8%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($results['list_data'] as $item) :
                $nilai_costbook = 0;
                $value_neraca = 0;
                $last_update = '-';
                $get_nilai_costbook = $this->db->query("SELECT a.costbook as nilai_costbook, a.created_on, a.value_neraca FROM tr_cost_book a WHERE a.id_material = '" . $item->id_material . "' AND (a.id_gudang_dari = '" . $item->id_gudang . "' OR a.id_gudang_ke = '" . $item->id_gudang . "') ORDER BY a.created_on DESC LIMIT 1")->row();
                if (!empty($get_nilai_costbook)) {
                    $nilai_costbook = $get_nilai_costbook->nilai_costbook;
                    $value_neraca = $get_nilai_costbook->value_neraca;
                    $last_update = date('d-M-Y H:i', strtotime((string) $get_nilai_costbook->created_on));
                }
            ?>
                <tr>
                    <td class="text-center"><?= $no ?></td>
                    <td class="text-center font-monospace fw-semibold text-primary"><?= $item->kode_produk ?></td>
                    <td class="text-start fw-bold text-dark"><?= strtoupper((string) $item->nm_material) ?></td>
                    <td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-1"><?= strtoupper((string) $item->nm_gudang) ?></span></td>
                    <td class="text-end fw-semibold"><?= number_format((float) $item->qty_stock, 2) ?></td>
                    <td class="text-end fw-bold text-success font-monospace">Rp <?= number_format((float) $nilai_costbook, 2) ?></td>
                    <td class="text-end fw-bold text-primary font-monospace">Rp <?= number_format((float) $value_neraca, 2) ?></td>
                    <td class="text-center text-muted font-monospace" style="font-size:0.85rem;"><?= $last_update ?></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-info check_costbook shadow-sm" data-id_material="<?= $item->id_material ?>" data-id_gudang="<?= $item->id_gudang ?>" title="View Costbook Transaction History">
                            <i class="fa fa-eye me-1"></i> Detail
                        </button>
                    </td>
                </tr>
            <?php
                $no++;
            endforeach;
            ?>
        </tbody>
    </table>
</div>
