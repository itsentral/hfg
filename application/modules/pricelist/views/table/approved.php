<?php
$ENABLE_ADD     = has_permission('Pricelist.Add');
$ENABLE_MANAGE  = has_permission('Pricelist.Manage');
$ENABLE_DELETE  = has_permission('Pricelist.Delete');
?>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle mb-0" id="<?= $table_id ?>" style="width:100%">
        <thead class="table-light">
            <tr>
                <th class="text-center" width="40">#</th>
                <th>Produk (Varian Utama)</th>
                <th width="140">No. Pricelist</th>
                <th class="text-center" width="90">Jumlah Baris</th>
                <th width="150">Dibuat Pada</th>
                <th class="text-center" width="160">Status</th>
                <th width="180">Keputusan Management</th>
                <th class="text-center no-sort" width="160">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($list)): ?>
                <?php $no = 1; foreach ($list as $r): ?>
                    <?php
                    $is_draft    = ($r->status === 'Draft');
                    $is_pending  = ($r->status === 'Menunggu Approval');
                    $is_approved = ($r->status === 'Approved');
                    $is_rejected = ($r->status === 'Rejected');

                    $prods = array_filter(array_map('trim', explode(',', $r->product_names)));
                    $first_prod = !empty($prods) ? $prods[0] : '(Belum ada produk)';
                    $more_count = count($prods) - 1;
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <strong class="text-dark fs-6"><?= htmlspecialchars(strtoupper($first_prod)) ?></strong>
                            <?php if ($more_count > 0): ?>
                                <span class="badge bg-secondary ms-1">+<?= $more_count ?> produk</span>
                            <?php endif; ?>
                            <div class="text-muted small mt-1">
                                Varian: <?= htmlspecialchars($r->uniq_ids ?: '-') ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fw-bold"><?= htmlspecialchars($r->no_pricelist) ?></span>
                        </td>
                        <td class="text-center fw-bold">
                            <?= $r->total_items ?> varian
                        </td>
                        <td class="text-muted small">
                            <?= date('d/m/Y H:i', strtotime($r->created_at)) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($is_draft): ?>
                                <span class="badge bg-secondary px-2 py-1">Draft</span>
                            <?php elseif ($is_pending): ?>
                                <span class="badge bg-warning text-dark border border-warning px-2 py-1">
                                    <i class="fa fa-clock-o me-1"></i> Menunggu Approval
                                </span>
                            <?php elseif ($is_approved): ?>
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                    <i class="fa fa-check-circle me-1"></i> Approved
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1" data-bs-toggle="tooltip" title="<?= htmlspecialchars($r->reject_reason ?: '') ?>">
                                    <i class="fa fa-times-circle me-1"></i> Rejected
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($is_approved): ?>
                                <div class="text-success small fw-bold"><i class="fa fa-check me-1"></i> Disetujui</div>
                                <div class="text-muted small"><?= htmlspecialchars($r->decided_by_name ?: 'GM') ?> &middot; <?= date('d/m/Y H:i', strtotime($r->decided_at)) ?></div>
                            <?php elseif ($is_rejected): ?>
                                <div class="text-danger small fw-bold"><i class="fa fa-times me-1"></i> Ditolak</div>
                                <div class="text-danger small"><?= htmlspecialchars($r->reject_reason ?: '-') ?></div>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center gap-1">
                                <button type="button"
                                    class="btn btn-sm btn-info text-white btn-view-pl shadow-sm"
                                    style="width: 80px;"
                                    data-id="<?= $r->id ?>"
                                    title="View Dokumen Pricelist">
                                    <i class="fa fa-eye"></i> View
                                </button>

                                <?php if ($is_draft && $ENABLE_MANAGE): ?>
                                    <button type="button"
                                        class="btn btn-sm btn-success btn-submit-pl shadow-sm"
                                        style="width: 80px;"
                                        data-id="<?= $r->id ?>"
                                        data-no="<?= $r->no_pricelist ?>"
                                        title="Ajukan ke Management">
                                        <i class="fa fa-paper-plane"></i> Ajukan
                                    </button>
                                <?php endif; ?>

                                <?php if ($is_draft && $ENABLE_DELETE): ?>
                                    <button type="button"
                                        class="btn btn-sm btn-danger btn-del-pl shadow-sm"
                                        style="width: 80px;"
                                        data-id="<?= $r->id ?>"
                                        data-no="<?= $r->no_pricelist ?>"
                                        title="Hapus Draft">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
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
        var tid = '#<?= $table_id ?>';
        if (!$.fn.DataTable.isDataTable(tid)) {
            $(tid).DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }]
            });
        }
    });
</script>
