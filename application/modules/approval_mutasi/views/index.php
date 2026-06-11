<?php
$ENABLE_ADD    = has_permission('Approval_mutasi.Add');
$ENABLE_MANAGE = has_permission('Approval_mutasi.Manage');
$ENABLE_VIEW   = has_permission('Approval_mutasi.View');
$ENABLE_DELETE = has_permission('Approval_mutasi.Delete');
?>

<style>
    .skeleton {
        border-radius: 4px;
        animation: shimmer 1.5s infinite linear;
        background: linear-gradient(90deg, #f2f2f2 25%, #e0e0e0 50%, #f2f2f2 75%);
        background-size: 200% 100%;
    }

    .skeleton-line {
        height: 20px;
        margin: 8px 0;
        border-radius: 4px;
    }

    .skeleton-line.short {
        width: 60%;
    }

    .skeleton-line.medium {
        width: 80%;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }
</style>

<div class="card">
    <div class="card-body">
        <div id="skeleton-approval">
            <div class="skeleton skeleton-line medium"></div>
            <div class="skeleton skeleton-line"></div>
            <div class="skeleton skeleton-line short"></div>
        </div>

        <div id="approval-content" style="display:none;"></div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>

<script>
    const BASE_URL = siteurl + active_controller;

    function loadApprovalList() {
        const skeleton = $('#skeleton-approval');
        const content = $('#approval-content');

        skeleton.show();
        content.hide();

        $.get(BASE_URL + '/render_open', function(html) {
            skeleton.hide();
            content.html(html).show();
        });
    }
    $(document).ready(function() {
        loadApprovalList();
    });

    function reloadTable() {
        loadApprovalList();
    }
</script>