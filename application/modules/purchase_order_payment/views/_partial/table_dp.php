<table class="table table-bordered table-hover table-sm table_req_pay_dp">
    <thead class="table-primary">
        <tr>
            <th class="text-center">No</th>
            <th class="text-center">No. PO</th>
            <th class="text-center">No. Invoice</th>
            <th class="text-center">No. Payment</th>
            <th class="text-center">Nama Supplier</th>
            <th class="text-center">Tanggal PO</th>
            <th class="text-center">Keterangan</th>
            <th class="text-center">Status</th>
            <th class="text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1;
        foreach ($list_po as $item): ?>
            <?php
            $sudah_receive = !empty($item['id_receive_dp']);
            $sudah_request = !empty($item['id_request_payment']);
            $sudah_bayar   = !empty($item['no_payment']) && $item['status_payment'] == 'payment';

            if (!$sudah_receive) {
                // Belum receive invoice
                $badge = '<span class="badge bg-secondary">Belum Receive</span>';
                $action_btn = '
                <button type="button" class="btn btn-sm btn-primary btn-req-dp"
                    data-id_top="' . $item['id_top'] . '"
                    data-no_po="' . $item['no_po'] . '"
                    title="Receive Invoice DP">
                    <i class="fa fa-plus"></i>
                </button>';
            } else {
                // Jika sudah receive, tampilkan status ENUM
                $status_enum = strtolower($item['status_receive_dp'] ?? '');
                $status_uc   = ucwords($status_enum);

                if ($status_enum === 'draft') {
                    $badge = '<span class="badge bg-info text-dark">Draft</span>';
                } elseif ($status_enum === 'payment') {
                    $badge = '<span class="badge bg-success">Payment</span>';
                } else {
                    $badge = '<span class="badge bg-warning text-dark">' . $status_uc . '</span>';
                }

                $action_btn = '
                <button type="button" class="btn btn-sm btn-info btn-view-dp"
                    data-id="' . $item['id_receive_dp'] . '"
                    title="Lihat Invoice">
                    <i class="fa fa-eye"></i>
                </button>';
            }
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center"><?= $item['no_surat'] ?></td>
                <td class="text-center"><?= $item['nomor_invoice'] ?? '-' ?></td>
                <td class="text-center"><?= $item['no_payment'] ?? '-' ?></td>
                <td><?= $item['nm_supplier'] ?></td>
                <td class="text-center"><?= date('d F Y', strtotime($item['tanggal'])) ?></td>
                <td><?= $item['keterangan_top'] ?? '-' ?></td>
                <td class="text-center"><?= $badge ?></td>
                <td class="text-center"><?= $action_btn ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
if ($.fn.DataTable.isDataTable('.table_req_pay_dp')) {
    $('.table_req_pay_dp').DataTable().destroy();
}
$('.table_req_pay_dp').DataTable();
</script>