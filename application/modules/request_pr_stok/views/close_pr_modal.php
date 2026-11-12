<div class="row g-3">
    <input type="hidden" name="so_number" value="<?= htmlspecialchars($so_number ?? '') ?>">
    <div class="col-12">
        <label class="form-label fw-bold text-dark mb-1">No. Dokumen PR</label>
        <input type="text" class="form-control bg-light fw-bold text-danger" value="<?= htmlspecialchars($no_pr ?? '') ?>" readonly>
    </div>
    <div class="col-12">
        <label class="form-label fw-bold text-dark mb-1">Alasan Penutupan (Closing Reason) <span class="text-danger">*</span></label>
        <textarea name="close_pr_reason" class="form-control" rows="3" placeholder="Masukkan alasan mengapa dokumen PR ini ditutup..." required></textarea>
    </div>
</div>
