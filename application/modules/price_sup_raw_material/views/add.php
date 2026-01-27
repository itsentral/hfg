<?php
$id 		= (!empty($listData[0]->id)) ? $listData[0]->id : '';
$code_lv4 	= (!empty($listData[0]->code_lv4)) ? $listData[0]->code_lv4 : '';
$nama 		= (!empty($listData[0]->nama)) ? $listData[0]->nama : '';
$status_app = (!empty($listData[0]->status_app)) ? $listData[0]->status_app : '';
$kurs 		= (!empty($listData[0]->kurs)) ? $listData[0]->kurs : '';

$price_ref 			= (!empty($listData[0]->price_ref)) ? $listData[0]->price_ref : '';
$price_ref_high 	= (!empty($listData[0]->price_ref_high)) ? $listData[0]->price_ref_high : '';
$price_ref_usd 		= (!empty($listData[0]->price_ref_usd)) ? $listData[0]->price_ref_usd : '';
$price_ref_high_usd = (!empty($listData[0]->price_ref_high_usd)) ? $listData[0]->price_ref_high_usd : '';

$price_ref_new 	= '';
$price_ref_high_new 	= '';
$price_ref_new_usd 	= '';
$price_ref_high_new_usd 	= '';
$note 			= '';
$upload_file 	= '';

$expired1 = $expired3 = $expired6 = $expired12 = '';

if ($status_app == 'Y') {
	$price_ref_new 		= (!empty($listData[0]->price_ref_new)) ? $listData[0]->price_ref_new : '';
	$price_ref_high_new = (!empty($listData[0]->price_ref_high_new)) ? $listData[0]->price_ref_high_new : '';
	$price_ref_new_usd 		= (!empty($listData[0]->price_ref_new_usd)) ? $listData[0]->price_ref_new_usd : '';
	$price_ref_high_new_usd = (!empty($listData[0]->price_ref_high_new_usd)) ? $listData[0]->price_ref_high_new_usd : '';
	$note 			= (!empty($listData[0]->note)) ? $listData[0]->note : '';
	$upload_file 	= (!empty($listData[0]->upload_file)) ? $listData[0]->upload_file : '';

	$expired1  = (!empty($listData[0]->price_ref_new_expired) && $listData[0]->price_ref_new_expired == '1')  ? 'selected' : '';
	$expired3  = (!empty($listData[0]->price_ref_new_expired) && $listData[0]->price_ref_new_expired == '3')  ? 'selected' : '';
	$expired6  = (!empty($listData[0]->price_ref_new_expired) && $listData[0]->price_ref_new_expired == '6')  ? 'selected' : '';
	$expired12 = (!empty($listData[0]->price_ref_new_expired) && $listData[0]->price_ref_new_expired == '12') ? 'selected' : '';
}
?>

<input type="hidden" id="id" name="id" value="<?= $id; ?>">
<input type="hidden" id="code_lv4" name="code_lv4" value="<?= $code_lv4; ?>">

<div class="row g-3">
	<div class="col-12">
		<label class="form-label">Material Master</label>
		<input type="text" class="form-control" id="nama" name="nama" value="<?= $nama; ?>" readonly>
	</div>

	<div class="col-12">
		<hr class="my-1">
	</div>

	<div class="col-12">
		<div class="row">
			<div class="col-md-6"><span class="text-danger fw-semibold">Lower Price</span></div>
			<div class="col-md-6"><span class="text-success fw-semibold">Higher Price</span></div>
		</div>
	</div>

	<div class="col-12">
		<label class="form-label">Before</label>
		<div class="row g-2">
			<div class="col-md-6">
				<div class="input-group">
					<span class="input-group-text">IDR</span>
					<input type="text" class="form-control text-center autoNumeric" id="price_ref" name="price_ref" value="<?= $price_ref; ?>" readonly>
					<span class="input-group-text">USD</span>
					<input type="text" class="form-control text-center autoNumeric6" id="price_ref_usd" name="price_ref_usd" value="<?= $price_ref_usd; ?>" readonly>
				</div>
			</div>
			<div class="col-md-6">
				<div class="input-group">
					<span class="input-group-text">IDR</span>
					<input type="text" class="form-control text-center autoNumeric" id="price_ref_high" name="price_ref_high" value="<?= $price_ref_high; ?>" readonly>
					<span class="input-group-text">USD</span>
					<input type="text" class="form-control text-center autoNumeric6" id="price_ref_high_usd" name="price_ref_high_usd" value="<?= $price_ref_high_usd; ?>" readonly>
				</div>
			</div>
		</div>
	</div>

	<div class="col-12">
		<label class="form-label">After <span class="text-danger">*</span></label>
		<div class="row g-2">
			<div class="col-md-6">
				<div class="input-group">
					<span class="input-group-text">IDR</span>
					<input type="text" class="form-control text-center autoNumeric" id="price_ref_new" name="price_ref_new" value="<?= $price_ref_new; ?>" required>
					<span class="input-group-text">USD</span>
					<input type="text" class="form-control text-center autoNumeric6" id="price_ref_new_usd" name="price_ref_new_usd" value="<?= $price_ref_new_usd; ?>">
				</div>
			</div>
			<div class="col-md-6">
				<div class="input-group">
					<span class="input-group-text">IDR</span>
					<input type="text" class="form-control text-center autoNumeric" id="price_ref_high_new" name="price_ref_high_new" value="<?= $price_ref_high_new; ?>" required>
					<span class="input-group-text">USD</span>
					<input type="text" class="form-control text-center autoNumeric6" id="price_ref_high_new_usd" name="price_ref_high_new_usd" value="<?= $price_ref_high_new_usd; ?>">
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<label class="form-label">Expired <span class="text-danger">*</span></label>
		<select id="price_ref_expired" name="price_ref_expired" class="form-control chosen-select" required>
			<option value="0">Select An Expired</option>
			<option value="1" <?= $expired1; ?>>1 Bulan</option>
			<option value="3" <?= $expired3; ?>>3 Bulan</option>
			<option value="6" <?= $expired6; ?>>Semester</option>
			<option value="12" <?= $expired12; ?>>Tahunan</option>
		</select>
	</div>

	<div class="col-md-6">
		<label class="form-label">Kurs</label>
		<input type="text" class="form-control text-center autoNumeric" id="kurs" name="kurs" value="<?= $kurs; ?>" required>
	</div>

	<div class="col-md-6">
		<label class="form-label">File Evidence <span class="text-danger">*</span></label>
		<input type="file" name="photo" id="photo" class="form-control">
		<?php if (!empty($upload_file)) { ?>
			<div class="form-text">
				<a href="<?= base_url() . $upload_file; ?>" target="_blank">Download File</a>
			</div>
		<?php } ?>
	</div>

	<div class="col-12">
		<label class="form-label">Note</label>
		<textarea class="form-control" id="note" name="note" rows="3" placeholder="Note"><?= $note; ?></textarea>
	</div>
</div>