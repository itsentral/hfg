<div class="row g-3">
    <input type="hidden" name="no_pengajuan" value="<?= $no_pengajuan; ?>">
    <div class="col-12">
        <label for="reject_reason" class="form-label fw-bold">Reject Reason <span class="text-danger">*</span></label>
        <textarea name="reject_reason" id="reject_reason" class="form-control" rows="4" placeholder="Enter reason for closing this PR..."></textarea>
    </div>
</div>
