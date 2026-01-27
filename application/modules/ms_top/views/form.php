<?php
$id     = (!empty($data[0]->id)) ? $data[0]->id : '';
$name   = (!empty($data[0]->name)) ? $data[0]->name : '';
$data1  = (!empty($data[0]->data1)) ? $data[0]->data1 : '';
?>

<input type="hidden" id="id" name="id" value="<?= $id; ?>">

<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Nama" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div class="col-12">
        <label class="form-label">Nilai <span class="text-danger">*</span></label>
        <input type="text" class="form-control text-end" id="data1" name="data1" placeholder="Nilai" value="<?= htmlspecialchars($data1, ENT_QUOTES, 'UTF-8'); ?>" required>
        <small class="text-muted">Isi angka tanpa huruf (contoh: 30 atau 30 hari).</small>
    </div>
</div>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script>
    // kalau kamu memang membuka swal loading sebelum load form
    if (typeof swal !== "undefined") swal.close();

    // optional: kalau ingin nilai otomatis numeric formatting (kalau cocok)
    // new Divider('#data1', { delimiter: ',', divideThousand: true });

    // fokus ke input pertama saat modal kebuka
    setTimeout(() => {
        $('#name').trigger('focus');
    }, 150);
</script>