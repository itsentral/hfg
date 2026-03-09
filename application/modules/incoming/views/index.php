<?php
$ENABLE_ADD     = has_permission('Incoming.Add');
$ENABLE_MANAGE  = has_permission('Incoming.Manage');
$ENABLE_VIEW    = has_permission('Incoming.View');
$ENABLE_DELETE  = has_permission('Incoming.Delete');
?>

<div class="card">
    <div class="card-header">
        <?php if ($ENABLE_ADD) : ?>
            <a href="<?= base_url('incoming/add') ?>" class="btn btn-md btn-success add">
                <i class="fa fa-plus me-1"></i> Incoming
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="tblIncoming">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Trans</th>
                        <th>No PR</th>
                        <th>Supplier</th>
                        <th>Total Material</th>
                        <th>Incoming Date</th>
                        <th>Receiver</th>
                        <th>Option</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>