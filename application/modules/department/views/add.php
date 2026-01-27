<?php
$id   = (!empty($listData[0]->id)) ? $listData[0]->id : '';
$nama = (!empty($listData[0]->nama)) ? $listData[0]->nama : '';

$status1 = (!empty($listData[0]->status) && $listData[0]->status == '1') ? 'checked' : '';
$status2 = (!empty($listData[0]->status) && $listData[0]->status == '0') ? 'checked' : '';
?>

<div class="form-group row">
	<div class="col-md-3">
		<label for="nama">Department Name <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-9">
		<input type="hidden" class="form-control" id="id" name="id" value="<?= $id; ?>">
		<input type="text" class="form-control" id="nama" name="nama" required placeholder="Department Name" value="<?= $nama; ?>">
	</div>
</div>