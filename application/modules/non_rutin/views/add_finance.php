<?php
$id_dept 		= (!empty($header)) ? $header[0]->id_dept : '';
$id_costcenter 	= (!empty($header)) ? $header[0]->id_costcenter : '';
$budget 		= (!empty($header)) ? number_format($header[0]->budget) : '0';
$sisa_budget 	= (!empty($header)) ? number_format($header[0]->sisa_budget) : '0';
$coa 			= (!empty($header)) ? $header[0]->coa : '';
$upload_spk 	= (!empty($header)) ? $header[0]->document : '';
$no_so 			= (!empty($header)) ? $header[0]->no_so : '';
$project_name 	= (!empty($header)) ? $header[0]->project_name : '';
$pr_coa 	= (!empty($header)) ? $header[0]->coa : '';
$tingkat_pr = (!empty($header)) ? $header[0]->tingkat_pr : '';
if (!empty($header) && !empty($header[0]->nm_pembuat)) {
	$nm_pembuat = $header[0]->nm_pembuat;
} else {
	$nm_pembuat = !empty($this->auth->nama()) ? $this->auth->nama() : (!empty($this->session->userdata['app_session']['nm_lengkap']) ? $this->session->userdata['app_session']['nm_lengkap'] : $this->auth->user_name());
}

if (!empty($header) && !empty($header[0]->created_date)) {
	$tgl_dibuat = date('d-M-Y', strtotime($header[0]->created_date));
} else {
	$tgl_dibuat = date('d-M-Y');
}

// Detail Approval
$alasan_reject1 = (!empty($header)) ? $header[0]->reject_reason1 : '';
$alasan_reject2 = (!empty($header)) ? $header[0]->reject_reason2 : '';
$alasan_reject3 = (!empty($header)) ? $header[0]->reject_reason3 : '';

$keterangan_1 = (!empty($header)) ? $header[0]->keterangan_1 : '';
$keterangan_2 = (!empty($header)) ? $header[0]->keterangan_2 : '';
$keterangan_3 = (!empty($header)) ? $header[0]->keterangan_3 : '';

$status1 = '';
$tgl_appre_1 = '';
$status2 = '';
$tgl_appre_2 = '';
$status3 = '';
$tgl_appre_3 = '';
if (!empty($header)) {
	if ($header[0]->app_3 == '1') {
		$status3 = '<span class="badge bg-success">Approved</span>';
		$tgl_appre_3 = date('d F Y', strtotime($header[0]->app_3_date));
	} else {
		if ($header[0]->sts_reject3 == '1') {
			$status3 = '<span class="badge bg-danger">Rejected</span>';
			$tgl_appre_3 = date('d F Y', strtotime($header[0]->sts_reject3_date));
		}
	}
}
// End Detail Status

$tanda 			= (!empty($code)) ? 'Update' : 'Insert';
$is_locked		= (!empty($approve)); // true for both 'approve' and 'view'
$disabled		= ($is_locked) ? 'disabled' : '';
$readonly		= ($is_locked) ? 'readonly' : '';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="<?= base_url('assets/plugins/sweetalert/dist/sweetalert2.all.min.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
	if (typeof Swal === 'undefined' && typeof Sweetalert2 !== 'undefined') {
		window.Swal = Sweetalert2;
	}
	if (typeof window.Swal !== 'undefined' && typeof window.swal === 'undefined') {
		window.swal = window.Swal;
	}
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" integrity="sha512-0nkKORjFgcyxv3HbE4rzFUlENUMNqic/EzDIeYCgsKa/nwqr2B91Vu/tNAu4Q0cBuG4Xe/D1f/freEci/7GDRA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<form action="#" method="POST" id="form_ct" enctype="multipart/form-data" autocomplete="off">
	<input type="hidden" name="id" value="<?= $id; ?>">
	<input type="hidden" name="tanda" value="<?= $tanda; ?>">
	<input type="hidden" id="approve" name="approve" value="<?= $approve; ?>">
	<input type="hidden" name="tingkat_approval" id="tingkat_approval" value="<?= $tingkat_approval ?>">

	<div class="card shadow-sm border-0 mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0 fw-bold text-primary">
				<i class="fa fa-pen-to-square me-2"></i><?= $title; ?>
			</h5>
		</div>

		<div class="card-body">
			<!-- Header fields -->
			<div class="row g-3 mb-3">
				<div class="col-md-6">
					<div class="row align-items-center">
						<label class="col-sm-4 col-form-label fw-bold">Department <span class="text-danger">*</span></label>
						<div class="col-sm-8">
							<select name="id_dept" id="id_dept" class="form-control chosen_select" <?= $disabled; ?>>
								<option value="0">Select Department</option>
								<?php
								foreach ($list_departement as $departement) {
									$selected = '';
									if ($departement->id == $id_dept) {
										$selected = 'selected';
									}
									$nama_dept = $departement->nm_dept ?? ($departement->name ?? '');
									echo "<option value='" . $departement->id . "' " . $selected . ">" . strtoupper($nama_dept) . "</option>";
								}
								?>
							</select>
							<?php if ($is_locked) : ?>
								<input type="hidden" name="id_dept" value="<?= $id_dept; ?>">
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="row align-items-center">
						<label class="col-sm-4 col-form-label fw-bold">Project Name</label>
						<div class="col-sm-8">
							<input type="text" id="project_name" name="project_name" class="form-control" placeholder="Project Name" value="<?= $project_name; ?>" <?= $readonly; ?>>
						</div>
					</div>
				</div>
			</div>

			<div class="row g-3 mb-3">
				<div class="col-md-6">
					<div class="row align-items-center">
						<label class="col-sm-4 col-form-label fw-bold">Upload Document</label>
						<div class="col-sm-8">
							<div class="input-group">
								<?php if (!$is_locked) : ?>
									<input type="file" id="upload_spk" name="upload_spk" class="form-control">
								<?php endif; ?>
								<?php if (!empty($upload_spk)) : ?>
									<?php
									$doc_dl = (file_exists('uploads/pr/' . $upload_spk)) ? 'uploads/pr/' . $upload_spk : 'assets/pr/' . $upload_spk;
									?>
									<a href="<?= base_url($doc_dl); ?>" target="_blank" class="btn btn-outline-secondary" title="Download">
										<i class="fa fa-download me-1"></i> File (<?= $upload_spk; ?>)
									</a>
								<?php elseif ($is_locked) : ?>
									<span class="form-control text-muted bg-light">Tidak ada dokumen dilampirkan</span>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="row align-items-center">
						<label class="col-sm-4 col-form-label fw-bold">COA <span class="text-danger">*</span></label>
						<div class="col-sm-8">
							<select name="coa" id="coa" class="form-control chosen_select" <?= $disabled; ?> required>
								<option value="">- Select COA -</option>
								<?php
								foreach ($list_coa as $coa_item) :
									$selected = ($coa_item['no_perkiraan'] == $pr_coa) ? "selected" : "";
									echo '<option value="' . $coa_item['no_perkiraan'] . '" ' . $selected . '>' . $coa_item['no_perkiraan'] . ' - ' . $coa_item['nama'] . '</option>';
								endforeach;
								?>
							</select>
							<?php if ($is_locked) : ?>
								<input type="hidden" name="coa" value="<?= $pr_coa; ?>">
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="row g-3 mb-3">
				<div class="col-md-6">
					<div class="row align-items-center">
						<label class="col-sm-4 col-form-label fw-bold">Tingkat PR</label>
						<div class="col-sm-8">
							<select name="tingkat_pr" id="tingkat_pr" class="form-select" <?= $disabled; ?>>
								<option value="1" <?= ($tingkat_pr == '1') ? 'selected' : null ?>>Normal</option>
								<option value="2" <?= ($tingkat_pr == '2') ? 'selected' : null ?>>Urgent</option>
							</select>
							<?php if ($is_locked) : ?>
								<input type="hidden" name="tingkat_pr" value="<?= $tingkat_pr; ?>">
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="row g-3 mb-3">
				<div class="col-md-6">
					<div class="row align-items-center">
						<label class="col-sm-4 col-form-label fw-bold">Nama Pembuat PR</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?= ucwords(strtolower($nm_pembuat)) ?>" readonly>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="row align-items-center">
						<label class="col-sm-4 col-form-label fw-bold">Tgl Dibuat PR</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?= $tgl_dibuat ?>" readonly>
						</div>
					</div>
				</div>
			</div>

			<?php if (!empty($header) && isset($approve) && $approve === 'view') : ?>
				<!-- Approval Status Table (Hanya di Mode View) -->
				<div class="row mb-4">
					<div class="col-12 col-lg-8">
						<div class="table-responsive">
							<table class="table table-bordered table-sm align-middle">
								<thead class="table-light text-center">
									<tr>
										<th>Approval By</th>
										<th>Status</th>
										<th>Tgl Approve / Reject</th>
										<th>Alasan Reject</th>
										<th>Keterangan</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="text-center fw-semibold">Management</td>
										<td class="text-center"><?= $status3 ?></td>
										<td class="text-center"><?= $tgl_appre_3 ?></td>
										<td>
											<input type="text" name="reject_reason3" class="form-control form-control-sm" value="<?= $alasan_reject3 ?>" readonly>
										</td>
										<td>
											<input type="text" name="keterangan_3" class="form-control form-control-sm" value="<?= $keterangan_3 ?>" readonly>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($approve == 'approve') : ?>
				<div class="card bg-light border-0 p-3 mb-4">
					<div class="row g-3 align-items-center">
						<label class="col-sm-2 col-form-label fw-bold">Approve <span class="text-danger">*</span></label>
						<div class="col-sm-3">
							<select name="sts_app" id="sts_app" class="form-select">
								<option value="0">Select Approve</option>
								<option value="Y">Approve</option>
								<option value="D">Reject</option>
							</select>
						</div>
						<label class="col-sm-2 col-form-label fw-bold tnd_reason">Reason <span class="text-danger">*</span></label>
						<div class="col-sm-5 tnd_reason">
							<textarea id="reason" name="reason" class="form-control" rows="2" placeholder="Reason for rejection"></textarea>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Header Detail Barang / Jasa with Add Button on top -->
			<div class="d-flex justify-content-between align-items-center mb-2">
				<h6 class="fw-bold mb-0 text-primary"><i class="fa fa-list me-1"></i> Detail Barang / Jasa</h6>
				<?php if (empty($approve)) : ?>
					<button type="button" class="btn btn-sm btn-success addPart" title="Add Barang">
						<i class="fa fa-plus me-1"></i> Add Barang
					</button>
				<?php endif; ?>
			</div>
			<div class="table-responsive mb-4">
				<table class='table table-striped table-bordered table-hover align-middle' width='100%' id="table-detail-pr">
					<thead class="table-primary text-center">
						<tr>
							<th class='text-center' style='width: 3%;'>#</th>
							<th class='text-center'>Nama Barang/Jasa</th>
							<th class='text-center' style='width: 13%;'>Spec/ Requirement</th>
							<th class='text-center' style='width: 7%;'>Qty</th>
							<th class='text-center' style='width: 8%;'>Satuan</th>
							<th class='text-center' style='width: 9%;'>Est Harga</th>
							<th class='text-center' style='width: 9%;'>Est Total Harga</th>
							<th class='text-center' style='width: 9%;'>Tanggal Dibutuhkan</th>
							<th class='text-center' style='width: 15%;'>Keterangan</th>
							<?php if (empty($approve)) : ?>
								<th class='text-center' style='width: 8%;'>#</th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody id="detail_body">
						<?php
						$nomor = 0;
						if (!empty($detail)) {
							foreach ($detail as $val => $valx) {
								$nomor++;
								echo "<tr class='header_" . $nomor . "'>";
								echo "<td align='center'>" . $nomor . "<input type='hidden' name='detail[" . $nomor . "][id]' value='" . $valx['id'] . "'></td>";
								echo "<td align='left'>
									<textarea class='form-control input-md nm_barang_" . $nomor . "' name='detail[" . $nomor . "][nm_barang]' " . $readonly . ">" . strtoupper($valx['nm_barang']) . "</textarea>
								</td>";
								echo "<td align='left'>
									<textarea class='form-control input-md spec_" . $nomor . "' name='detail[" . $nomor . "][spec]' " . $readonly . ">" . strtoupper($valx['spec']) . "</textarea>
								</td>";
								echo "<td align='left'><input type='text' " . $readonly . " id='qty_" . $nomor . "' name='detail[" . $nomor . "][qty]' class='form-control input-md text-right autoNumeric2 sum_tot qty_" . $nomor . "' value='" . $valx['qty'] . "'></td>";
								echo "<td align='left'>";
								if ($is_locked) {
									echo "<select name='detail[" . $nomor . "][satuan]' class='form-control satuan_" . $nomor . "' style='pointer-events: none; background-color: #e9ecef;' tabindex='-1'>";
								} else {
									echo "<select name='detail[" . $nomor . "][satuan]' class='form-control wajib satuan_" . $nomor . "' required>";
								}
								echo "<option value=''>Pilih</option>";
								foreach ($satuan as $key => $value) {
									$selected = ($value['id'] == $valx['satuan']) ? 'selected' : '';
									echo "<option value='" . $value['id'] . "' " . $selected . ">" . $value['code'] . "</option>";
								}
								echo "	</select>";
								if ($is_locked) {
									echo "<input type='hidden' name='detail[" . $nomor . "][satuan]' value='" . $valx['satuan'] . "'>";
								}
								echo "</td>";
								echo "<td align='left'><input type='text' " . $readonly . " id='harga_" . $nomor . "' name='detail[" . $nomor . "][harga]' class='form-control input-md text-right maskM sum_tot harga_" . $nomor . "' value='" . $valx['harga'] . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
								echo "<td align='left'><input type='text' id='total_harga_" . $nomor . "' name='detail[" . $nomor . "][total_harga]' class='form-control input-md text-right maskM jumlah_all total_harga_" . $nomor . "' value='" . ($valx['qty'] * $valx['harga']) . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></td>";
								$datepicker_cls = ($is_locked) ? '' : 'datepicker';
								echo "<td align='left'><input type='text' name='detail[" . $nomor . "][tanggal]' class='form-control input-md text-center " . $datepicker_cls . " tgl_dibutuhkan tanggal_" . $nomor . "' readonly value='" . strtoupper($valx['tanggal']) . "'></td>";
								echo "<td align='left'>
									<textarea class='form-control input-md keterangan_" . $nomor . "' name='detail[" . $nomor . "][keterangan]' " . $readonly . ">" . strtoupper($valx['keterangan']) . "</textarea>
								</td>";
								if (empty($approve)) {
									echo "<td align='center'><button type='button' class='btn btn-sm btn-warning edit_detail edit_detail_" . $nomor . "' data-id='" . $valx['id'] . "' data-nomor='" . $nomor . "' style='margin-right: 0.5em;' title='Edit Detail'><i class='fa fa-pencil ti ti-pencil'></i>
									</button><button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-trash ti ti-trash'></i></button></td>";
								}
								echo "</tr>";
							}
						}
						?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="card-footer bg-white py-3 d-flex justify-content-end gap-2">
			<button type="button" class="btn btn-secondary" id="back">
				<i class="fa fa-arrow-left ti ti-arrow-left me-1"></i> Back
			</button>
			<?php if ($approve <> 'view') : ?>
				<button type="button" class="btn btn-primary" id="save">
					<i class="fa fa-save ti ti-device-floppy me-1"></i> Save
				</button>
			<?php endif; ?>
		</div>
	</div>
</form>

<style type="text/css">
	.chosen-container-active .chosen-single {
		border: 1px solid #86b7fe;
		box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
	}

	.chosen-container-single .chosen-single {
		height: 38px;
		border: 1px solid #ced4da;
		border-radius: 0.375rem;
		background: #fff;
		box-shadow: none;
		color: #212529;
		line-height: 36px;
	}

	.chosen-container-single .chosen-single div {
		top: 6px;
	}

	.chosen-container .chosen-results {
		max-height: 300px;
		overflow-y: auto;
	}

	.datepicker {
		cursor: pointer;
	}
</style>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
	$(document).ready(function() {
		$('.maskM').autoNumeric();
		$('.autoNumeric2').autoNumeric('init', {
			mDec: '2',
			aPad: false
		});
		$('.chosen_select').chosen({
			width: '100%'
		});
		$('.datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
		});
		$('.tnd_reason').hide();
	});

	$(document).on('change', '#sts_app', function(e) {
		var sts = $(this).val();
		if (sts == 'D') {
			$('.tnd_reason').show();
		} else {
			$('.tnd_reason').hide();
		}
	});

	$(document).on('click', '#back', function(e) {
		var tanda = "app_pr_dept_finance";
		window.location.href = base_url + active_controller + tanda;
	});

	var row_number = <?= $nomor; ?>;

	$(document).on('click', '.addPart', function() {
		row_number++;
		var id = row_number;

		$.ajax({
			url: base_url + active_controller + 'get_add/' + id,
			cache: false,
			type: "POST",
			dataType: "json",
			success: function(data) {
				$("#detail_body").append(data.header);
				$('.chosen_select').chosen({
					width: '100%'
				});
				$('.maskM').autoNumeric();
				$('.autoNumeric2').autoNumeric('init', {
					mDec: '2',
					aPad: false
				});
				$('.datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
				});
				if (typeof swal !== 'undefined' && typeof swal.close === 'function') {
					swal.close();
				} else if (typeof Swal !== 'undefined' && typeof Swal.close === 'function') {
					Swal.close();
				}
				renumber_detail_rows();
			},
			error: function() {
				Swal.fire({
					title: "Error Message !",
					text: 'Connection Time Out. Please try again..',
					icon: "warning",
					timer: 3000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	// Delete part
	$(document).on('click', '.delPart', function() {
		$(this).closest('tr').remove();
		sum_total(0);
		renumber_detail_rows();
	});

	function renumber_detail_rows() {
		var num = 1;
		$('#detail_body tr').each(function() {
			var firstTd = $(this).find('td').first();
			var hiddenInputs = firstTd.find('input');
			firstTd.text(num);
			if (hiddenInputs.length > 0) {
				firstTd.append(hiddenInputs);
			}
			num++;
		});
	}

	$(document).on('keyup', '.sum_tot', function() {
		var id = $(this).attr('id');
		var det_id = id.split('_');
		var a = det_id[1];
		sum_total(a);
	});

	// SAVE
	$(document).on('click', '#save', function(e) {
		e.preventDefault();
		$('#save').prop('disabled', true);

		var itemCount = $('#table-detail-pr tbody tr').not('#empty_row').length;
		if (itemCount === 0) {
			Swal.fire({
				title: "Error Message!",
				text: 'Silakan tambahkan minimal 1 data barang / jasa terlebih dahulu!',
				icon: "warning"
			});
			$('#save').prop('disabled', false);
			return false;
		}

		var tingkat_approval = $('#tingkat_approval').val();
		var id_dept = $('#id_dept').val();
		var coa = $('#coa').val();
		var sts_app = $('#sts_app').val();

		var app = $("#approve").val();
		if (app == 'approve') {
			if (sts_app == '0' || sts_app == '') {
				Swal.fire({
					title: "Warning!",
					text: 'Status Approve empty, select first ...',
					icon: "warning"
				});
				$('#save').prop('disabled', false);
				return false;
			}
			if (sts_app == 'D' && $.trim($('#reason').val()) == '') {
				Swal.fire({
					title: "Warning!",
					text: 'Reason for rejection is required!',
					icon: "warning"
				});
				$('#save').prop('disabled', false);
				return false;
			}
		} else {
			if (id_dept == '0' || id_dept == '') {
				Swal.fire({
					title: "Error Message!",
					text: 'Department name empty, select first ...',
					icon: "warning"
				});
				$('#save').prop('disabled', false);
				return false;
			}

			let FALIDASIwajib = true;
			$(".wajib").each(function() {
				var satuan = $(this).val();
				if (satuan == '' || satuan == '0') {
					FALIDASIwajib = false;
					return false;
				}
			});
			if (FALIDASIwajib === false) {
				Swal.fire({
					title: "Error Message!",
					text: 'Satuan wajib diisi !',
					icon: "warning",
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false,
					timer: 2000
				});
				$('#save').prop('disabled', false);
				return false;
			}

			let FALIDASI = true;
			$(".tgl_dibutuhkan").each(function() {
				var tgl_butuh = $(this).val();
				if (tgl_butuh == '' || tgl_butuh == '0000-00-00') {
					FALIDASI = false;
					return false;
				}
			});
			if (FALIDASI === false) {
				Swal.fire({
					title: "Error Message!",
					text: 'Tgl dibutuhkan wajib diisi !',
					icon: "warning",
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false,
					timer: 2000
				});
				$('#save').prop('disabled', false);
				return false;
			}
		}

		Swal.fire({
			title: "Are you sure?",
			text: "Save this data ?",
			icon: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-primary",
			confirmButtonText: "Yes, Process it!",
			cancelButtonText: "No, cancel process!",
		}).then((isConfirm) => {
			if (isConfirm.isConfirmed) {
				var formData = new FormData($('#form_ct')[0]);
				var baseurl = base_url + active_controller + 'add_finance';
				$.ajax({
					url: baseurl,
					type: "POST",
					data: formData,
					cache: false,
					dataType: 'json',
					processData: false,
					contentType: false,
					success: function(data) {
						if (data.status == 1) {
							Swal.fire({
								title: "Save Success!",
								text: data.pesan,
								icon: "success",
								timer: 3000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							}).then((next) => {
								var return_link = 'app_pr_dept_finance';
								window.location.href = base_url + active_controller + return_link;
							});
						} else if (data.status == 0) {
							Swal.fire({
								title: "Save Failed!",
								text: data.pesan,
								icon: "warning",
								timer: 3000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							}).then((next) => {
								$('#save').prop('disabled', false);
							});
						}
					},
					error: function() {
						Swal.fire({
							title: "Error Message !",
							text: 'An Error Occured During Process. Please try again..',
							icon: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						}).then(next => {
							$('#save').prop('disabled', false);
						});
					}
				});
			} else {
				$('#save').prop('disabled', false);
			}
		});
	});

	function sum_total(a) {
		var qty = getNum($('#qty_' + a).val().split(",").join(""));
		var harga = getNum($('#harga_' + a).val().split(",").join(""));

		var total = Number(qty) * Number(harga);
		$('#total_harga_' + a).val(number_format(total));

		var total_all = 0;
		$('.jumlah_all').each(function() {
			var val = getNum($(this).val().split(",").join(""));
			total_all += Number(val);
		});

		$('#budget').val(number_format(total_all));
	}

	function number_format(number, decimals, dec_point, thousands_sep) {
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
				var k = Math.pow(10, prec);
				return '' + (Math.round(n * k) / k).toFixed(prec);
			};
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}
</script>
