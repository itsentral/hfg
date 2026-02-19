<?php
$ENABLE_ADD     = has_permission('Incoming.Add');
$ENABLE_MANAGE  = has_permission('Incoming.Manage');
$ENABLE_VIEW    = has_permission('Incoming.View');
$ENABLE_DELETE  = has_permission('Incoming.Delete');
?>

<div class="card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <?php if ($ENABLE_ADD) : ?>
            <a href="<?= base_url('incoming/add') ?>" class="btn btn-success add">
                <i class="fa fa-plus me-1"></i> Incoming
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">

    </div>
</div>