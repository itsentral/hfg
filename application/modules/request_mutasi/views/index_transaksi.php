<?php
$ENABLE_ADD     = has_permission('Request_Mutasi.Add');
$ENABLE_MANAGE  = has_permission('Request_Mutasi.Manage');
$ENABLE_DELETE  = has_permission('Request_Mutasi.Delete');
$ENABLE_VIEW    = has_permission('Request_Mutasi.View');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-exchange me-2"></i>Daftar Transaksi Kas / Bank Langsung
            </h5>
            <small class="text-muted">Manage direct bank receipt (BUM) and payment (BUK) transactions</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($ENABLE_ADD) : ?>
                <a class="btn btn-success btn-sm px-3 shadow-sm" href="<?= base_url('request_mutasi/create_transaksi') ?>" title="Add Bank Transaction">
                    <i class="fa fa-plus me-1"></i> Tambah Transaksi
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-light text-center">
                    <tr>
                        <th width="4%" class="text-center">No</th>
                        <th width="9%" class="text-center">Transaction Date</th>
                        <th width="13%">Transaction Code</th>
                        <th width="8%" class="text-center">Type</th>
                        <th>Description</th>
                        <th width="13%">COA Asal</th>
                        <th width="13%">Destination COA</th>
                        <th width="11%" class="text-end">Foreign Currency Value</th>
                        <th width="12%" class="text-end">Total IDR</th>
                        <th width="7%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)) : ?>
                        <?php $numb = 1; foreach ($data as $record) : ?>
                            <?php
                            $jenis_badge = ($record->jenis_transaksi == 'keluar')
                                ? '<span class="badge bg-danger px-2 py-1"><i class="fa fa-arrow-up me-1"></i>Out</span>'
                                : '<span class="badge bg-success px-2 py-1"><i class="fa fa-arrow-down me-1"></i>In</span>';
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-semibold"><?= $numb++; ?></td>
                                <td class="text-center text-nowrap"><i class="fa fa-calendar me-1 text-muted"></i><?= date('d/m/Y', strtotime((string) $record->tgl_request)) ?></td>
                                <td><span class="fw-bold text-primary"><?= htmlspecialchars((string) $record->kd_mutasi) ?></span></td>
                                <td class="text-center"><?= $jenis_badge ?></td>
                                <td><?= htmlspecialchars((string) $record->keterangan) ?></td>
                                <td><?= htmlspecialchars($record->nama_bank_asal ?: $record->bank_asal) ?></td>
                                <td><?= htmlspecialchars($record->nama_bank_tujuan ?: $record->bank_tujuan) ?></td>
                                <td class="text-end fw-semibold"><?= number_format((float) $record->nilai, 0, ',', '.') ?></td>
                                <td class="text-end fw-bold text-success">Rp <?= number_format((float) $record->transaksi, 0, ',', '.') ?></td>
                                <td class="text-center text-nowrap">
                                    <a class="btn btn-outline-success btn-sm" href="<?= base_url('request_mutasi/printout_transaksi/' . $record->kd_mutasi) ?>" target="_blank" title="Print Transaction Voucher">
                                        <i class="fa fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#mytabledata').DataTable({
            order: [[0, 'asc']],
            responsive: true
        });
    });
</script>
