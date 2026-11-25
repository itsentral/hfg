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
                <i class="fa fa-random me-2"></i>Daftar Request Mutasi
            </h5>
            <small class="text-muted">Manage balance transfer requests between cash / bank accounts</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($ENABLE_ADD) : ?>
                <a class="btn btn-success btn-sm px-3 shadow-sm" href="<?= base_url('request_mutasi/create') ?>" title="Add Mutation Request">
                    <i class="fa fa-plus me-1"></i> Tambah Request
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
                        <th width="9%" class="text-center">Tgl Request</th>
                        <th width="13%">Mutation Code</th>
                        <th>Description</th>
                        <th width="13%">Bank Asal</th>
                        <th width="13%">Destination Bank</th>
                        <th width="8%" class="text-center">Currency</th>
                        <th width="12%" class="text-end">Transfer Value</th>
                        <th width="10%" class="text-center">Status</th>
                        <th width="12%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)) : ?>
                        <?php $numb = 1; foreach ($data as $record) : ?>
                            <?php
                            $sts_app = (string) ($record->status_approve ?? '0');
                            if ($sts_app === '1') {
                                $badge_sts = '<span class="badge bg-success">Approved</span>';
                            } elseif ($sts_app === '2') {
                                $badge_sts = '<span class="badge bg-danger">Rejected</span>';
                            } else {
                                $badge_sts = '<span class="badge bg-warning text-dark">Waiting Approval</span>';
                            }
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-semibold"><?= $numb++; ?></td>
                                <td class="text-center text-nowrap"><i class="fa fa-calendar me-1 text-muted"></i><?= date('d/m/Y', strtotime((string) $record->tgl_request)) ?></td>
                                <td><span class="fw-bold text-primary"><?= htmlspecialchars((string) $record->kd_mutasi) ?></span></td>
                                <td><?= htmlspecialchars((string) $record->keterangan) ?></td>
                                <td><i class="fa fa-bank me-1 text-muted"></i><?= htmlspecialchars($record->nama_bank_asal ?: $record->bank_asal) ?></td>
                                <td><i class="fa fa-bank me-1 text-muted"></i><?= htmlspecialchars($record->nama_bank_tujuan ?: $record->bank_tujuan) ?></td>
                                <td class="text-center"><span class="badge bg-light text-dark border"><?= htmlspecialchars($record->mata_uang ?: 'IDR') ?></span></td>
                                <td class="text-end fw-semibold">Rp <?= number_format((float) $record->nilai_request, 0, ',', '.') ?></td>
                                <td class="text-center"><?= $badge_sts ?></td>
                                <td class="text-center text-nowrap">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if ($sts_app === '1' && ($ENABLE_ADD || $ENABLE_MANAGE)) : ?>
                                            <a class="btn btn-primary btn-sm" href="<?= base_url('request_mutasi/add_mutasi/' . $record->kd_mutasi) ?>" title="Realisasi Mutasi">
                                                <i class="fa fa-exchange me-1"></i> Add Mutasi
                                            </a>
                                        <?php endif; ?>
                                        <a class="btn btn-outline-success btn-sm" href="<?= base_url('request_mutasi/printout/' . $record->kd_mutasi) ?>" target="_blank" title="Print Request Form">
                                            <i class="fa fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables -->
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
