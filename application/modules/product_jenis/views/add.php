<?php
$id          = (!empty($listData[0]->id))       ? $listData[0]->id       : '';
$code_lv1    = (!empty($listData[0]->code_lv1)) ? $listData[0]->code_lv1 : '';
$code_lv2    = (!empty($listData[0]->code_lv2)) ? $listData[0]->code_lv2 : '';
$code        = (!empty($listData[0]->code_lv3)) ? $listData[0]->code_lv3 : '';
$nama        = (!empty($listData[0]->nama))      ? $listData[0]->nama      : '';
$code_manual = (!empty($listData[0]->code))      ? $listData[0]->code      : '';
$status1     = (!empty($listData[0]->status) && $listData[0]->status == '1') ? 'checked' : '';
$status2     = (!empty($listData[0]->status) && $listData[0]->status == '0') ? 'checked' : '';
?>

<input type="hidden" id="id" name="id" value="<?= $id; ?>">
<input type="hidden" id="code" name="code" value="<?= $code; ?>">

<div class="row mb-3">
	<div class="col-md-3">
		<label class="col-form-label">Product Type <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-9">
		<select name="code_lv1" id="code_lv1" class="form-select chosen-select">
			<option value="0">Select Product Type</option>
			<?php foreach ($listLevel1 as $key => $value) : ?>
				<option value="<?= $value['code_lv1']; ?>"
					<?= ($code_lv1 == $value['code_lv1']) ? 'selected' : ''; ?>>
					<?= strtoupper($value['nama']); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-3">
		<label class="col-form-label">Product Category <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-9">
		<select name="code_lv2" id="code_lv2" class="form-select chosen-select">
			<option value="0">Select Product Category</option>
			<?php if (!empty($id) && !empty($listLevel2)) : ?>
				<?php foreach ($listLevel2 as $key => $value) : ?>
					<option value="<?= $value['code_lv2']; ?>"
						<?= ($code_lv2 == $value['code_lv2']) ? 'selected' : ''; ?>>
						<?= strtoupper($value['nama']); ?>
					</option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-3">
		<label class="col-form-label">Product Jenis <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-9">
		<input type="text" class="form-control" id="nama" name="nama"
			required placeholder="Product Jenis" value="<?= $nama; ?>">
	</div>
</div>

<div class="row mb-3">
	<div class="col-md-3">
		<label class="col-form-label">Type Code <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-9">
		<input type="text" class="form-control" id="code_manual" name="code_manual"
			required placeholder="Type Code" value="<?= $code_manual; ?>">
	</div>
</div>

<?php if (!empty($id)) : ?>
	<div class="row mb-3">
		<div class="col-md-3">
			<label class="col-form-label">Status</label>
		</div>
		<div class="col-md-9">
			<div class="d-flex gap-4 pt-2">
				<label>
					<input type="radio" name="status" value="1" <?= $status1; ?>> Aktif
				</label>
				<label>
					<input type="radio" name="status" value="0" <?= $status2; ?>> Non-Aktif
				</label>
			</div>
		</div>
	</div>
<?php endif; ?>

<script>
	$(document).ready(function() {
		$('#dialog-popup').find('.chosen-select').select2({
			width: '100%',
			dropdownParent: $('#dialog-popup')
		});
	});
</script>