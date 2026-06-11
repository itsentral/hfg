<?php
$ENABLE_ADD    = has_permission('Pengajuan_mutasi.Add');
$ENABLE_MANAGE = has_permission('Pengajuan_mutasi.Manage');
$ENABLE_VIEW   = has_permission('Pengajuan_mutasi.View');
?>

<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle" id="tblOpen" style="width: 100%;">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>No. Mutasi</th>
                <th>Pengajuan Oleh</th>
                <th>Gudang Asal</th>
                <th>Gudang Tujuan</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th width="100">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($list)): ?>
                <?php foreach ($list as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= ($row['mutation_number']) ?></strong></td>
                        <td>
                            <?= htmlspecialchars($row['create_by']) ?><br>
                            <small><i><?= date('d/m/Y H:i', strtotime($row['create_date'])) ?></i></small>
                        </td>
                        <td><?= ($row['nm_gudang_from']) ?></td>
                        <td><?= ($row['nm_gudang_to']) ?></td>
                        <td><?= ($row['description'] ?? '-') ?></td>
                        <td>
                            <?php if ($row['status'] == 0) : ?>
                                <span class="badge bg-primary">Open</span>
                            <?php elseif ($row['status'] == 1) : ?>
                                <span class="badge bg-warning">Menunggu Approve</span>
                            <?php elseif ($row['status'] == 6) : ?>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-danger">Revisi</span>
                                    <a href="javascript:void(0)" class="text-info fs-6"
                                        onclick="showRevisionReason('<?= htmlspecialchars($row['mutation_number']) ?>', '<?= isset($row['reject_reason']) ? htmlspecialchars($row['reject_reason']) : '' ?>')"
                                        title="Lihat Catatan Revisi">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <?php if ($ENABLE_VIEW): ?>
                                    <a href="<?= site_url('pengajuan_mutasi/form/view/' . $row['id']) ?>"
                                        class="btn btn-sm btn-info text-white" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if ($ENABLE_MANAGE): ?>
                                    <?php if ($row['status'] == 0): ?>
                                        <a href="<?= site_url('pengajuan_mutasi/form/edit/' . $row['id']) ?>"
                                            class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success"
                                            onclick="confirmSubmit(<?= $row['id'] ?>)" title="Ajukan">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmCancel(<?= $row['id'] ?>)" title="Cancel">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($row['status'] == 6): ?>
                                        <a href="<?= site_url('pengajuan_mutasi/form/edit/' . $row['id']) ?>"
                                            class="btn btn-sm btn-warning" title="Perbaiki Data">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success"
                                            onclick="confirmSubmit(<?= $row['id'] ?>)" title="Ajukan Kembali">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    <?php endif; ?>

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

    // Fungsi untuk memunculkan Alasan/Catatan Revisi dari Approver
    function showRevisionReason(mutationNumber, reasonJson) {
        let reason = "Tidak ada catatan spesifik.";

        try {
            if (reasonJson) {
                // Unescape json string jika dibungkus json_encode sebelumnya
                reason = JSON.parse(reasonJson);
            }
        } catch (e) {
            if (reasonJson) reason = reasonJson;
        }

        if (!reason || reason.trim() === "null" || reason.trim() === "") {
            reason = "Tidak ada catatan spesifik.";
        }

        Swal.fire({
            title: 'Catatan Revisi',
            html: `<div class="text-start">
                <p class="mb-1"><strong>No. Mutasi:</strong> ${mutationNumber}</p>
                <hr class="my-2">
                <p class="mb-0 text-danger fw-semibold"><i class="fa-solid fa-comment-dots me-1"></i> Alasan/Poin Revisi:</p>
                <blockquote class="bg-light p-3 border-start border-info border-3 rounded mt-2 text-dark" style="font-style: italic;">
                    "${reason}"
                </blockquote>
               </div>`,
            icon: 'info',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#6c757d'
        });
    }
</script>