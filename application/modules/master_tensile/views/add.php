<?php
$ENABLE_ADD     = has_permission('Master_tensile.Add');
$ENABLE_MANAGE  = has_permission('Master_tensile.Manage');
$ENABLE_VIEW    = has_permission('Master_tensile.View');
$ENABLE_DELETE  = has_permission('Master_tensile.Delete');

$id   = (!empty($header[0]->id)) ? $header[0]->id : '';
$nama = (!empty($header[0]->nama)) ? $header[0]->nama : '';
?>

<!-- Dipakai untuk isi #ModalView (form wrapper sudah ada di modal utama),
     jadi jangan pakai <form> lagi dan jangan ada tombol save di sini -->

<div class="form-group row mb-3">
	<div class="col-md-3">
		<label for="nama" class="form-label mb-0">Tensile Name <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-9">
		<input type="text" class="form-control" id="nama" name="nama" required placeholder="Unit Name" value="<?= $nama; ?>">
		<input type="hidden" class="form-control" id="id" name="id" value="<?= $id; ?>">
	</div>
</div>