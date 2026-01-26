<?php
$ENABLE_ADD     = has_permission('Master_Unit.Add');
$ENABLE_MANAGE  = has_permission('Master_Unit.Manage');
$ENABLE_VIEW    = has_permission('Master_Unit.View');
$ENABLE_DELETE  = has_permission('Master_Unit.Delete');

$id   = (!empty($header[0]->id)) ? $header[0]->id : '';
$code = (!empty($header[0]->code)) ? $header[0]->code : '';
$nama = (!empty($header[0]->nama)) ? $header[0]->nama : '';
?>

<!-- Dipakai untuk isi #ModalView (form wrapper sudah ada di modal utama),
     jadi jangan pakai <form> lagi dan jangan ada tombol save di sini -->

<div class="form-group row mb-3">
	<div class="col-md-3">
		<label for="code" class="form-label mb-0">Code <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-9">
		<input type="text" class="form-control" id="code" name="code" required placeholder="Code" value="<?= $code; ?>">
		<input type="hidden" class="form-control" id="id" name="id" value="<?= $id; ?>">
	</div>
</div>

<div class="form-group row mb-3">
	<div class="col-md-3">
		<label for="nama" class="form-label mb-0">Unit Name <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-9">
		<input type="text" class="form-control" id="nama" name="nama" required placeholder="Unit Name" value="<?= $nama; ?>">
	</div>
</div>