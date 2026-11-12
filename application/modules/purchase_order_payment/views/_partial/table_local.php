<?php
$tipe_btn = $tipe_btn ?? 'local';
?>
<table class="table table-bordered table-hover table-sm table_req_pay_local">
    <thead class="table-primary">
        <tr>
            <th class="text-center">No</th>
            <th class="text-center">No. PO</th>
            <th class="text-center">No. Incoming</th>
            <th class="text-center">No. Invoice</th>
            <th class="text-center">No. Payment</th>
            <th class="text-center">Nama Supplier</th>
            <th class="text-center">Tanggal Incoming</th>
            <th class="text-center">DP Sebelumnya (IDR)</th>
            <th class="text-center">Status</th>
            <th class="text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1;
        foreach ($list_po as $item): ?>
            <?php
            if (empty($item['id_receive_il'])) {
                $badge = '<span class="badge bg-secondary">Belum Receive</span>';
            } else {
                $status_enum = strtolower($item['status_receive_il'] ?? '');
                $status_uc   = ucwords($status_enum);

                if ($status_enum === 'draft') {
                    $badge = '<span class="badge bg-info text-dark">Draft</span>';
                } elseif ($status_enum === 'payment') {
                    $badge = '<span class="badge bg-success">Payment</span>';
                } else {
                    $badge = '<span class="badge bg-warning text-dark">' . $status_uc . '</span>';
                }
            }

            $dp_info = ((float)($item['total_dp_rupiah'] ?? 0) > 0)
                ? '<span class="badge bg-info text-dark">Rp ' . number_format($item['total_dp_rupiah'], 0) . '</span>'
                : '<span class="badge bg-light text-dark border">Tidak Ada DP</span>';

            $buttons = '';

            if (empty($item['id_receive_il'])) {
                $buttons .= '
        <button type="button"
            class="btn btn-sm btn-primary btn-req-il"
            data-id_top="' . ($item['id_top'] ?? '') . '"
            data-no_po="'  . $item['no_po']  . '"
            data-tipe="'   . $tipe_btn       . '"
            data-id_dp=""
            data-id_incoming="' . ($item['kode_trans'] ?? '') . '"
            title="Receive Invoice Local">
            <i class="fa fa-plus"></i>
        </button>';
            } else {
                $buttons .= '
        <button type="button"
            class="btn btn-sm btn-info btn-view-il text-white"
            data-id="'   . $item['id_receive_il'] . '"
            data-tipe="' . $tipe_btn              . '"
            title="Lihat Invoice">
            <i class="fa fa-eye"></i>
        </button>';
            }

            $action_btn = '<div class="d-inline-flex align-items-center justify-content-center gap-2">' . $buttons . '</div>';
            ?>
            <tr>
                <td class="text-center align-middle"><?= $no++ ?></td>
                <td class="text-center align-middle"><?= $item['no_surat'] ?></td>
                <td class="text-center align-middle"><?= $item['kode_trans'] ?? '-' ?></td>
                <td class="text-center align-middle"><?= $item['nomor_invoice'] ?? '-' ?></td>
                <td class="text-center align-middle"><?= $item['no_payment'] ?? '-' ?></td>
                <td class="align-middle"><?= $item['nm_supplier'] ?></td>
                <td class="text-center align-middle"><?= !empty($item['tanggal']) ? date('d F Y', strtotime($item['tanggal'])) : '-' ?></td>
                <td class="text-center align-middle"><?= $dp_info ?></td>
                <td class="text-center align-middle"><?= $badge ?></td>
                <td class="text-center align-middle"><?= $action_btn ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
if ($.fn.DataTable.isDataTable('.table_req_pay_local')) {
    $('.table_req_pay_local').DataTable().destroy();
}
$('.table_req_pay_local').DataTable();
</script>