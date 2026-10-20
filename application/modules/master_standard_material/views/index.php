<?php
$ENABLE_ADD    = has_permission('Master_Standard_Material.Add');
$ENABLE_MANAGE = has_permission('Master_Standard_Material.Manage');

$standar_toleransi   = (isset($standard['standar_toleransi']))   ? $standard['standar_toleransi']   : '0';
$standar_berat_jenis = (isset($standard['standar_berat_jenis'])) ? $standard['standar_berat_jenis'] : '0';
$coating             = (isset($standard['coating']))             ? $standard['coating']             : '0';
$latest_update       = (!empty($standard['latest_update']))      ? date('d-m-Y H:i', strtotime($standard['latest_update'])) : '-';

// Baris global belum ada -> perlu hak Add; sudah ada -> perlu hak Manage
$boleh_simpan = (empty($standard)) ? $ENABLE_ADD : $ENABLE_MANAGE;
$readonly     = ($boleh_simpan) ? '' : 'readonly';
?>

<div class="card shadow-sm border-0">
	<div class="card-header bg-white">
		<h5 class="mb-0">Master Standard Material</h5>
		<small class="text-muted">
			Satu set nilai berlaku untuk semua material. Dipakai menghitung Berat/Meter pada BOM:
			((Tebal Material &minus; Standar Toleransi) &times; Lebar Coil &times; Standar Berat Jenis) + Coating.
		</small>
	</div>

	<div class="card-body">
		<form id="data_form" autocomplete="off">
			<div class="row mb-3">
				<div class="col-md-3">
					<label for="standar_toleransi" class="col-form-label">Standar Toleransi <span class="text-danger">*</span></label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control" id="standar_toleransi" name="standar_toleransi"
						inputmode="decimal" required placeholder="0" value="<?= $standar_toleransi; ?>" <?= $readonly; ?>>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-3">
					<label for="standar_berat_jenis" class="col-form-label">Standar Berat Jenis <span class="text-danger">*</span></label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control" id="standar_berat_jenis" name="standar_berat_jenis"
						inputmode="decimal" required placeholder="0" value="<?= $standar_berat_jenis; ?>" <?= $readonly; ?>>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-3">
					<label for="coating" class="col-form-label">Coating <span class="text-danger">*</span></label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control" id="coating" name="coating"
						inputmode="decimal" required placeholder="0" value="<?= $coating; ?>" <?= $readonly; ?>>
				</div>
			</div>

			<hr>

			<div class="row mb-3">
				<div class="col-md-3">
					<label class="col-form-label">Last Update</label>
				</div>
				<div class="col-md-4 pt-2"><?= $latest_update; ?></div>
			</div>

			<div class="row mb-3">
				<div class="col-md-3">
					<label class="col-form-label">Updated By</label>
				</div>
				<div class="col-md-4 pt-2"><?= $updated_by; ?></div>
			</div>

			<?php if ($boleh_simpan) : ?>
				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-9">
						<button type="submit" class="btn btn-primary">
							<i class="ti ti-device-floppy me-1"></i> Save
						</button>
					</div>
				</div>
			<?php endif; ?>
		</form>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {

		// SUBMIT (save)
		$(document).on('submit', '#data_form', function(e) {
			e.preventDefault();

			// Validasi cepat di sisi klien (validasi utama tetap di server): angka >= 0, titik sebagai desimal
			var valid = true;
			$('#standar_toleransi, #standar_berat_jenis, #coating').each(function() {
				if (!/^\d{1,8}(\.\d{1,6})?$/.test($(this).val().trim())) {
					valid = false;
				}
			});
			if (!valid) {
				swal({
					title: "Error Message!",
					text: 'Isi semua nilai dengan angka 0 atau lebih. Gunakan titik untuk desimal (maksimal 8 digit bulat dan 6 digit desimal).',
					type: "warning"
				});
				return false;
			}

			swal({
				title: "Are you sure?",
				text: "Process this data",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Simpan!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			}, function() {
				$.ajax({
					type: 'POST',
					url: base_url + active_controller + 'save',
					dataType: "json",
					data: $('#data_form').serialize(),
					success: function(res) {
						if (res.status == '1') {
							swal({
									title: "Success",
									text: res.pesan,
									type: "success"
								},
								function() {
									window.location.reload(true);
								});
						} else {
							swal({
								title: "Error",
								text: res.pesan,
								type: "error"
							});
						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Error Process!",
							type: "error"
						});
					}
				});
			});
		});
	});
</script>
