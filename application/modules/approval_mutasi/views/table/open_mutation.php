<?php
$ENABLE_MANAGE = has_permission('Approval_mutasi.Manage');
$ENABLE_VIEW   = has_permission('Approval_mutasi.View');
?>

<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle" id="tblOpen" style="width: 100%;">
        <thead class="table-light">
            <tr>
                <th width="50">#</th>
                <th>No. Mutasi</th>
                <th>Pengajuan Oleh</th>
                <th>Gudang Asal</th>
                <th>Gudang Tujuan</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th width="80" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($list)): ?>
                <?php foreach ($list as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= $row['mutation_number'] ?></strong></td>
                        <td>
                            <?= htmlspecialchars($row['create_by']) ?><br>
                            <small class="text-muted"><i><?= date('d/m/Y H:i', strtotime($row['create_date'])) ?></i></small>
                        </td>
                        <td><?= $row['nm_gudang_from'] ?></td>
                        <td><?= $row['nm_gudang_to'] ?></td>
                        <td><?= $row['description'] ?? '-' ?></td>
                        <td>
                            <?php if ($row['status'] == 1) : ?>
                                <span class="badge bg-warning text-dark">Menunggu Approve</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Status <?= $row['status'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <?php if ($ENABLE_MANAGE): ?>
                                    <a href="<?= site_url('approval_mutasi/form/approval/' . $row['id']) ?>"
                                       class="btn btn-sm btn-success" title="Proses Approval">
                                        <i class="fa-solid fa-square-check"></i>
                                    </a>
                                <?php elseif ($ENABLE_VIEW): ?>
                                    <a href="<?= site_url('approval_mutasi/form/view/' . $row['id']) ?>"
                                       class="btn btn-sm btn-info text-white" title="View Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#tblOpen')) {
            $('#tblOpen').DataTable({
                "destroy": true,
                "responsive": true,
                "order": []
            });
        }
    });
</script>