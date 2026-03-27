<?php
$id            = (!empty($header[0]->id)) ? $header[0]->id : '';
$kode_supplier = (!empty($header[0]->kode_supplier)) ? $header[0]->kode_supplier : '';
$nama          = (!empty($header[0]->nama)) ? $header[0]->nama : '';
$id_country    = (!empty($header[0]->id_country)) ? $header[0]->id_country : 'IDN';
$id_currency   = (!empty($header[0]->id_currency)) ? $header[0]->id_currency : 'IDR';
$telp          = (!empty($header[0]->telp)) ? $header[0]->telp : '';
$telp2         = (!empty($header[0]->telp2)) ? $header[0]->telp2 : '';
$fax           = (!empty($header[0]->fax)) ? $header[0]->fax : '';
$email         = (!empty($header[0]->email)) ? $header[0]->email : '';
$email2        = (!empty($header[0]->email2)) ? $header[0]->email2 : '';
$inisial       = (!empty($header[0]->inisial)) ? $header[0]->inisial : '';
$contact       = (!empty($header[0]->contact)) ? $header[0]->contact : '';
$contact_person = (!empty($header[0]->contact_person)) ? $header[0]->contact_person : '';
$tax_number    = (!empty($header[0]->tax_number)) ? $header[0]->tax_number : '';
$id_prov       = (!empty($header[0]->id_prov)) ? $header[0]->id_prov : '';
$id_kabkot     = (!empty($header[0]->id_kabkot)) ? $header[0]->id_kabkot : '';
$id_kec        = (!empty($header[0]->id_kec)) ? $header[0]->id_kec : '';
$address       = (!empty($header[0]->address)) ? $header[0]->address : '';
$tax_address   = (!empty($header[0]->tax_address)) ? $header[0]->tax_address : '';
$note          = (!empty($header[0]->note)) ? $header[0]->note : '';
$bank_account  = (!empty($header[0]->bank_account)) ? $header[0]->bank_account : '';
?>

<!-- hidden -->
<input type="hidden" name="id" id="id" value="<?= $id; ?>">
<input type="hidden" name="kode_supplier" id="kode_supplier" value="<?= $kode_supplier; ?>">

<div class="row g-3">

	<div class="col-md-6">
		<label class="form-label">Supplier Name <span class="text-danger">*</span></label>
		<input type="text" name="nama" id="nama" class="form-control" placeholder="Supplier Name" value="<?= $nama; ?>">
	</div>

	<div class="col-md-6">
		<label class="form-label">Country</label>
		<select id="id_country" name="id_country" class="form-select select2" onchange="checkCountry()">
			<?php foreach ($country as $value): $sel = ($value['iso3'] == $id_country) ? 'selected' : ''; ?>
				<option value="<?= $value['iso3']; ?>" <?= $sel; ?>><?= strtoupper($value['name']) ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<div id="indonesia-area" class="row g-3">
		<div class="col-md-4">
			<label class="form-label">Provinsi <span class="text-danger">*</span></label>
			<select id="id_prov" name="id_prov" class="form-select select2" onchange="get_kota()" required <?= $disabled ?? '' ?>>
				<option value="">--Pilih--</option>
				<?php foreach ($prov as $p): $selected = ($id_prov == $p->id_prov) ? 'selected' : ''; ?>
					<option value="<?= $p->id_prov ?>" <?= $selected ?>><?= $p->provinsi ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="col-md-4">
			<label class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
			<select id="id_kabkot" name="id_kabkot" class="form-select select2" onchange="get_kec()" required <?= $disabled ?? '' ?>>
				<option value="">--Pilih--</option>
				<?php foreach ($kabkot as $k): $selected = ($id_kabkot == $k->id_kabkot) ? 'selected' : ''; ?>
					<option value="<?= $k->id_kabkot ?>" <?= $selected ?>><?= $k->kabkot ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="col-md-4">
			<label class="form-label">Kecamatan <span class="text-danger">*</span></label>
			<select id="id_kec" name="id_kec" class="form-select select2" required <?= $disabled ?? '' ?>>
				<option value="">--Pilih--</option>
				<?php foreach ($kec as $kc): $selected = ($id_kec == $kc->id_kec) ? 'selected' : ''; ?>
					<option value="<?= $kc->id_kec ?>" <?= $selected ?>><?= $kc->kecamatan ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="col-md-4">
		<label class="form-label">Telephone</label>
		<input type="text" name="telp" id="telp" class="form-control" placeholder="Telephone" value="<?= $telp; ?>">
	</div>

	<div class="col-md-4">
		<label class="form-label">Telephone 2</label>
		<input type="text" name="telp2" id="telp2" class="form-control" placeholder="Telephone 2" value="<?= $telp2; ?>">
	</div>

	<div class="col-md-4">
		<label class="form-label">Fax</label>
		<input type="text" name="fax" id="fax" class="form-control" placeholder="Fax" value="<?= $fax; ?>">
	</div>

	<div class="col-md-6">
		<label class="form-label">Email</label>
		<input type="text" name="email" id="email" class="form-control" placeholder="Email" value="<?= $email; ?>">
	</div>

	<div class="col-md-6">
		<label class="form-label">Contact Person</label>
		<input type="text" name="contact_person" id="contact_person" class="form-control" placeholder="Contact Person" value="<?= $contact_person; ?>">
	</div>

	<div class="col-md-6">
		<label class="form-label">Email 2</label>
		<input type="text" name="email2" id="email2" class="form-control" placeholder="Email 2" value="<?= $email2; ?>">
	</div>

	<div class="col-md-6">
		<label class="form-label">Inisial</label>
		<input type="text" name="inisial" id="inisial" class="form-control" placeholder="Inisial" value="<?= $inisial; ?>">
	</div>

	<div class="col-md-6">
		<label class="form-label">Contact</label>
		<input type="text" name="contact" id="contact" class="form-control" placeholder="Contact" value="<?= $contact; ?>">
	</div>

	<div class="col-md-6">
		<label class="form-label">Tax Number</label>
		<input type="text" name="tax_number" id="tax_number" class="form-control" placeholder="Tax Number" value="<?= $tax_number; ?>">
	</div>

	<div class="col-md-6">
		<label class="form-label">Currency</label>
		<select id="id_currency" name="id_currency" class="form-select select2">
			<?php foreach ($currency as $value): $sel = ($value['kode'] == $id_currency) ? 'selected' : ''; ?>
				<option value="<?= $value['kode']; ?>" <?= $sel; ?>><?= strtoupper($value['kode'] . ' - ' . $value['mata_uang']) ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="col-md-6">
		<label class="form-label">Bank Account</label>
		<input type="text" name="bank_account" id="bank_account" class="form-control" placeholder="Bank Account" value="<?= $bank_account; ?>">
	</div>

	<div class="col-md-6">
		<label class="form-label">Address</label>
		<textarea name="address" id="address" class="form-control" rows="3" placeholder="Address"><?= $address; ?></textarea>
	</div>

	<div class="col-md-6">
		<label class="form-label">Tax Address</label>
		<textarea name="tax_address" id="tax_address" class="form-control" rows="3" placeholder="Tax Address"><?= $tax_address; ?></textarea>
	</div>

	<div class="col-md-12">
		<label class="form-label">Note</label>
		<textarea name="note" id="note" class="form-control" rows="3" placeholder="Note"><?= $note; ?></textarea>
	</div>

</div>

<script>
	// init select2 untuk konten yang baru di-load (modal)
	// (dropdownParent akan di-set dari index setelah load, ini fallback)
	$(function() {
		if ($.fn.select2) {
			$('.select2').select2({
				width: '100%'
			});
		}
	});
</script>