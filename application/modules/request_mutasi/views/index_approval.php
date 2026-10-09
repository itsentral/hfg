<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-check-circle me-2"></i>Daftar Approval Request Mutasi
            </h5>
            <small class="text-muted">Antrian pengajuan mutasi bank yang memerlukan persetujuan</small>
        </div>
        <div>
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                <i class="fa fa-clock-o me-1"></i>Waiting Approval
            </span>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle w-100" id="mytabledata">
                <thead class="table-light text-center">
                    <tr>
                        <th width="4%" class="text-center">No</th>
                        <th width="9%" class="text-center">Tgl Request</th>
                        <th width="13%">Kode Mutasi</th>
                        <th>Keterangan</th>
                        <th width="12%">Bank Asal</th>
                        <th width="12%">Bank Tujuan</th>
                        <th width="7%" class="text-center">Mata Uang</th>
                        <th width="12%" class="text-end">Nilai Transfer</th>
                        <th width="10%">Dibuat Oleh</th>
                        <th width="8%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)) : ?>
                        <?php $numb = 1; foreach ($data as $record) : ?>
                            <tr>
                                <td class="text-center text-muted fw-semibold"><?= $numb++; ?></td>
                                <td class="text-center text-nowrap"><i class="fa fa-calendar me-1 text-muted"></i><?= date('d/m/Y', strtotime($record->tgl_request)) ?></td>
                                <td><span class="fw-bold text-primary"><?= htmlspecialchars($record->kd_mutasi) ?></span></td>
                                <td><?= htmlspecialchars($record->keterangan) ?></td>
                                <td><i class="fa fa-bank me-1 text-muted"></i><?= htmlspecialchars($record->nama_bank_asal ?: $record->bank_asal) ?></td>
                                <td><i class="fa fa-bank me-1 text-muted"></i><?= htmlspecialchars($record->nama_bank_tujuan ?: $record->bank_tujuan) ?></td>
                                <td class="text-center"><span class="badge bg-light text-dark border"><?= htmlspecialchars($record->mata_uang ?: 'IDR') ?></span></td>
                                <td class="text-end fw-semibold">Rp <?= number_format($record->nilai_request, 0, ',', '.') ?></td>
                                <td><i class="fa fa-user me-1 text-muted"></i><?= htmlspecialchars($record->created_by) ?></td>
                                <td class="text-center text-nowrap">
                                    <a class="btn btn-primary btn-sm px-3 shadow-sm" href="<?= base_url('request_mutasi/approval/' . $record->kd_mutasi) ?>" title="Proses Approval">
                                        <i class="fa fa-check-square-o me-1"></i> Review
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
