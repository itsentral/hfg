<?php
$is_view = (isset($stsview) && $stsview == 'view');
$default_date = isset($data->tgl_doc) ? $data->tgl_doc : date('Y-m-d');
$selected_nama = isset($data->nama) ? $data->nama : (!empty($current_employee) ? $current_employee->nm_karyawan : (!empty($current_user->nm_lengkap) ? $current_user->nm_lengkap : $this->auth->user_name()));
$selected_dept = isset($data->departement) ? $data->departement : (!empty($current_user->department_id) ? $current_user->department_id : (!empty($current_employee->department) ? $current_employee->department : ''));

$dept_map = [];
if (!empty($data_departement)) {
	foreach ($data_departement as $d) {
		$dept_map[$d->id] = $d->nm_dept;
	}
}
$selected_dept_name = isset($dept_map[$selected_dept]) ? $dept_map[$selected_dept] : $selected_dept;
?>

<!-- Flatpickr CSS fallback -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="card shadow-sm border-0 mb-4">
	<div class="card-header bg-transparent py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
		<div class="d-flex align-items-center">
			<div class="avatar avatar-sm bg-light-primary text-primary me-2 rounded-2 d-flex align-items-center justify-content-center p-2">
				<i class="fa <?= $is_view ? 'fa-eye' : (isset($data->id) ? 'fa-edit' : 'fa-plus') ?> fs-5"></i>
			</div>
			<div>
				<h5 class="card-title mb-0 fw-bold text-dark">
					<?= (isset($data->id) && $data->id != '') ? ($is_view ? 'Detail Pengajuan Transportasi' : 'Edit Pengajuan Transportasi') : 'Form Pengajuan Transportasi'; ?>
				</h5>
				<small class="text-muted"><?= $is_view ? 'Lihat rincian pengajuan biaya transportasi' : 'Isi data formulir perjalanan dan rincian biaya di bawah ini'; ?></small>
			</div>
		</div>
		<div>
			<button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1" onclick="list_view()">
				<i class="fa fa-arrow-left"></i>
				<span>Kembali</span>
			</button>
		</div>
	</div>

	<?= form_open_multipart($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'class' => 'needs-validation')); ?>
	<input type="hidden" id="id" name="id" value="<?= set_value('id', isset($data->id) ? $data->id : ''); ?>">

	<div class="card-body p-4">
		<!-- Section 1: Informasi Header Dokumen -->
		<div class="p-3 mb-4 rounded-3 border bg-light">
			<h6 class="fw-bold text-dark mb-3"><i class="fa fa-info-circle text-primary me-1"></i> Informasi Dokumen</h6>
			<div class="row g-3">
				<div class="col-md-3">
					<label for="no_doc" class="form-label fw-semibold text-secondary small">No. Dokumen</label>
					<input type="text" class="form-control form-control-sm bg-white" id="no_doc" name="no_doc" value="<?= (isset($data->no_doc) ? $data->no_doc : ''); ?>" placeholder="Otomatis" readonly>
				</div>
				<div class="col-md-3">
					<label for="tgl_doc" class="form-label fw-semibold text-secondary small">Tanggal Dokumen <span class="text-danger">*</span></label>
					<div class="input-group input-group-sm">
						<input type="text" class="form-control flatpickr-input bg-white" id="tgl_doc" name="tgl_doc" value="<?= $default_date; ?>" placeholder="YYYY-MM-DD" required <?= $is_view ? 'disabled' : ''; ?>>
						<span class="input-group-text bg-white" onclick="$('#tgl_doc').focus()"><i class="fa fa-calendar text-muted"></i></span>
					</div>
				</div>
				<div class="col-md-3">
					<label for="nama" class="form-label fw-semibold text-secondary small">Nama Karyawan <span class="text-danger">*</span></label>
					<select class="form-select form-select-sm select2-transport" id="nama" name="nama" required <?= $is_view ? 'disabled' : ''; ?>>
						<option value="">-- Pilih Karyawan --</option>
						<?php if (!empty($data_employee)): ?>
							<?php foreach ($data_employee as $emp): ?>
								<?php
									$selected = '';
									if ($selected_nama == $emp->nm_karyawan || $selected_nama == $emp->nik) {
										$selected = 'selected';
									}
									$emp_dept_id = $emp->department;
									$emp_dept_name = isset($dept_map[$emp_dept_id]) ? $dept_map[$emp_dept_id] : $emp_dept_id;
								?>
								<option value="<?= htmlspecialchars($emp->nm_karyawan); ?>" data-department="<?= htmlspecialchars($emp_dept_id); ?>" data-department-name="<?= htmlspecialchars($emp_dept_name); ?>" <?= $selected; ?>>
									<?= htmlspecialchars($emp->nm_karyawan); ?> (<?= htmlspecialchars($emp->nik); ?>)
								</option>
							<?php endforeach; ?>
						<?php else: ?>
							<option value="<?= htmlspecialchars($selected_nama); ?>" selected><?= htmlspecialchars($selected_nama); ?></option>
						<?php endif; ?>
					</select>
				</div>
				<div class="col-md-3">
					<label for="nama_departement" class="form-label fw-semibold text-secondary small">Departemen <span class="text-danger">*</span></label>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-light text-muted"><i class="fa fa-building"></i></span>
						<input type="text" class="form-control bg-light fw-medium" id="nama_departement" value="<?= htmlspecialchars($selected_dept_name); ?>" placeholder="Otomatis mengikuti karyawan" readonly>
					</div>
					<input type="hidden" id="departement" name="departement" value="<?= htmlspecialchars($selected_dept); ?>">
				</div>
				<div class="col-md-6">
					<label for="coa" class="form-label fw-semibold text-secondary small">Akun COA (Accounting) <span class="text-danger">*</span></label>
					<select class="form-select form-select-sm select2-transport" id="coa" name="coa" required <?= $is_view ? 'disabled' : ''; ?>>
						<option value="">-- Pilih COA --</option>
						<?php if (!empty($list_coa)): ?>
							<?php foreach ($list_coa as $item_coa): ?>
								<?php
									$selected = (isset($data->no_coa) && $data->no_coa == $item_coa->no_coa) ? 'selected' : '';
								?>
								<option value="<?= $item_coa->no_coa; ?>" data-name="<?= htmlspecialchars($item_coa->nm_coa); ?>" <?= $selected; ?>>
									<?= $item_coa->no_coa; ?> - <?= htmlspecialchars($item_coa->nm_coa); ?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
					<input type="hidden" name="nm_coa" id="nm_coa" value="<?= (isset($data->nm_coa)) ? htmlspecialchars($data->nm_coa) : ''; ?>">
				</div>
				<div class="col-md-6">
					<label for="keperluan" class="form-label fw-semibold text-secondary small">Keperluan Perjalanan <span class="text-danger">*</span></label>
					<input type="text" class="form-control form-control-sm" id="keperluan" name="keperluan" value="<?= (isset($data->keperluan) ? htmlspecialchars($data->keperluan) : ''); ?>" placeholder="Contoh: Meeting dengan vendor, survey lapangan..." required <?= $is_view ? 'disabled' : ''; ?>>
				</div>
			</div>
		</div>

		<!-- Section 2: Keberangkatan & Rute -->
		<div class="row g-3 mb-4">
			<div class="col-md-6">
				<div class="card h-100 border border-success-subtle shadow-none">
					<div class="card-header bg-success-subtle py-2">
						<span class="fw-bold text-success"><i class="fa fa-arrow-circle-right me-1"></i> Data Keberangkatan</span>
					</div>
					<div class="card-body">
						<div class="mb-3">
							<label for="nopol" class="form-label fw-semibold text-secondary small">Nomor Polisi Kendaraan</label>
							<div class="input-group input-group-sm">
								<span class="input-group-text"><i class="fa fa-car text-muted"></i></span>
								<input type="text" class="form-control text-uppercase" id="nopol" name="nopol" value="<?= (isset($data->nopol) ? htmlspecialchars($data->nopol) : ''); ?>" placeholder="Contoh: B 1234 ABC" maxlength="15" <?= $is_view ? 'disabled' : ''; ?>>
							</div>
						</div>
						<div class="mb-3">
							<label for="rute" class="form-label fw-semibold text-secondary small">Rute Perjalanan</label>
							<textarea class="form-control form-control-sm" id="rute" name="rute" rows="2" placeholder="Contoh: Kantor - Cikarang - Kantor" <?= $is_view ? 'disabled' : ''; ?>><?= (isset($data->rute) ? htmlspecialchars($data->rute) : ''); ?></textarea>
						</div>
						<div class="row g-2">
							<div class="col-6">
								<label for="km_awal" class="form-label fw-semibold text-secondary small">KM Awal</label>
								<div class="input-group input-group-sm">
									<input type="text" class="form-control text-end auto-number" id="km_awal" name="km_awal" value="<?= (isset($data->km_awal) ? $data->km_awal : '0'); ?>" <?= $is_view ? 'disabled' : ''; ?>>
									<span class="input-group-text">km</span>
								</div>
							</div>
							<div class="col-6">
								<label for="km_akhir" class="form-label fw-semibold text-secondary small">KM Akhir</label>
								<div class="input-group input-group-sm">
									<input type="text" class="form-control text-end auto-number" id="km_akhir" name="km_akhir" value="<?= (isset($data->km_akhir) ? $data->km_akhir : '0'); ?>" <?= $is_view ? 'disabled' : ''; ?>>
									<span class="input-group-text">km</span>
								</div>
							</div>
						</div>
						<div class="mt-2 text-end">
							<small class="text-muted">Total Jarak: <strong id="lbl_total_km" class="text-dark">0</strong> km</small>
						</div>
					</div>
				</div>
			</div>

			<!-- Section 3: Rincian Biaya -->
			<div class="col-md-6">
				<div class="card h-100 border border-warning-subtle shadow-none">
					<div class="card-header bg-warning-subtle py-2">
						<span class="fw-bold text-dark"><i class="fa fa-money text-warning me-1"></i> Rincian Biaya Transportasi</span>
					</div>
					<div class="card-body">
						<div class="row g-2 mb-2">
							<div class="col-6">
								<label for="bensin" class="form-label fw-semibold text-secondary small">Bensin</label>
								<div class="input-group input-group-sm">
									<span class="input-group-text">Rp</span>
									<input type="text" class="form-control text-end auto-currency cost-calc" id="bensin" name="bensin" value="<?= (isset($data->bensin) ? $data->bensin : '0'); ?>" <?= $is_view ? 'disabled' : ''; ?>>
								</div>
							</div>
							<div class="col-6">
								<label for="tol" class="form-label fw-semibold text-secondary small">Tol</label>
								<div class="input-group input-group-sm">
									<span class="input-group-text">Rp</span>
									<input type="text" class="form-control text-end auto-currency cost-calc" id="tol" name="tol" value="<?= (isset($data->tol) ? $data->tol : '0'); ?>" <?= $is_view ? 'disabled' : ''; ?>>
								</div>
							</div>
							<div class="col-6">
								<label for="parkir" class="form-label fw-semibold text-secondary small">Parkir</label>
								<div class="input-group input-group-sm">
									<span class="input-group-text">Rp</span>
									<input type="text" class="form-control text-end auto-currency cost-calc" id="parkir" name="parkir" value="<?= (isset($data->parkir) ? $data->parkir : '0'); ?>" <?= $is_view ? 'disabled' : ''; ?>>
								</div>
							</div>
							<div class="col-6">
								<label for="lainnya" class="form-label fw-semibold text-secondary small">Lain-lain / Transport</label>
								<div class="input-group input-group-sm">
									<span class="input-group-text">Rp</span>
									<input type="text" class="form-control text-end auto-currency cost-calc" id="lainnya" name="lainnya" value="<?= (isset($data->lainnya) ? $data->lainnya : '0'); ?>" <?= $is_view ? 'disabled' : ''; ?>>
								</div>
							</div>
						</div>

						<div class="p-3 bg-light-primary rounded-2 border border-primary-subtle mt-3">
							<div class="d-flex justify-content-between align-items-center">
								<div>
									<small class="text-muted d-block">Total Biaya Transport:</small>
									<span class="badge bg-primary">Total Reimbursement</span>
								</div>
								<div class="text-end">
									<h4 class="mb-0 fw-bold text-primary" id="lbl_total_kasbon">Rp 0</h4>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Section 4: Bukti & Keterangan -->
		<div class="p-3 mb-3 rounded-3 border bg-light">
			<div class="row g-3">
				<div class="col-md-6">
					<label for="doc_file" class="form-label fw-semibold text-secondary small">
						Upload Bukti / Struk / Dokumen <?= !isset($data->doc_file) || $data->doc_file == '' ? '<span class="text-danger">*</span>' : ''; ?>
					</label>
					<input type="hidden" name="filename" id="filename" value="<?= (isset($data->doc_file) ? $data->doc_file : ''); ?>">
					<input type="file" class="form-control form-control-sm" name="doc_file" id="doc_file" accept="image/*,application/pdf" <?= $is_view ? 'disabled' : ''; ?>>
					<small class="text-muted d-block mt-1">Format file: JPG, PNG, atau PDF (maks. 5MB)</small>

					<?php if (isset($data->doc_file) && $data->doc_file != ''): ?>
						<div class="mt-3 p-2 bg-white rounded border d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<i class="fa <?= (strpos($data->doc_file, '.pdf') !== false) ? 'fa-file-pdf-o text-danger' : 'fa-file-image-o text-primary' ?> fs-4 me-2"></i>
								<div>
									<small class="fw-bold d-block text-truncate" style="max-width: 250px;"><?= $data->doc_file; ?></small>
									<span class="badge bg-success-subtle text-success">File terlampir</span>
								</div>
							</div>
							<div>
								<a href="<?= base_url('uploads/expense/' . $data->doc_file); ?>" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2" title="Buka Dokumen">
									<i class="fa fa-external-link"></i> Buka
								</a>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<div class="col-md-6">
					<label for="keterangan" class="form-label fw-semibold text-secondary small">Keterangan Tambahan</label>
					<textarea class="form-control form-control-sm" id="keterangan" name="keterangan" rows="3" placeholder="Catatan atau keterangan tambahan (opsional)" <?= $is_view ? 'disabled' : ''; ?>><?= (isset($data->keterangan) ? htmlspecialchars($data->keterangan) : ''); ?></textarea>
				</div>
			</div>
		</div>
	</div>

	<!-- Card Footer -->
	<div class="card-footer bg-transparent py-3 border-top d-flex justify-content-between align-items-center">
		<div>
			<button type="button" class="btn btn-outline-secondary px-4" onclick="list_view()">
				<i class="fa fa-times me-1"></i> Batal
			</button>
		</div>
		<div>
			<?php if (!$is_view): ?>
				<button type="submit" class="btn btn-primary px-4 shadow-sm" id="btn_submit">
					<i class="fa fa-save me-1"></i> Simpan Data
				</button>
			<?php endif; ?>
		</div>
	</div>
	<?= form_close(); ?>
</div>

<script type="text/javascript">
	window.initTransportForm = function() {
		// Initialize Flatpickr
		function runFlatpickr() {
			if (typeof flatpickr !== 'undefined') {
				flatpickr("#tgl_doc", {
					dateFormat: "Y-m-d",
					locale: "id",
					allowInput: true,
					defaultDate: "<?= $default_date; ?>"
				});
			} else {
				// Dynamic loader fallback
				$.getScript("https://cdn.jsdelivr.net/npm/flatpickr", function() {
					$.getScript("https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js", function() {
						flatpickr("#tgl_doc", {
							dateFormat: "Y-m-d",
							locale: "id",
							allowInput: true,
							defaultDate: "<?= $default_date; ?>"
						});
					});
				});
			}
		}

		runFlatpickr();

		// Initialize Select2 if available
		if ($.fn.select2) {
			$('.select2-transport').select2({
				width: '100%',
				dropdownParent: $('#form-data')
			});
		}

		// Initial auto-sync of department from selected employee
		updateDepartmentFromEmployee();

		// Initial calculation
		calculateTotalCost();
		calculateTotalKm();
	};

	function updateDepartmentFromEmployee() {
		var opt = $('#nama').find(':selected');
		var deptId = opt.data('department') || '';
		var deptName = opt.data('department-name') || '';

		if (deptId !== '' || deptName !== '') {
			$('#departement').val(deptId);
			$('#nama_departement').val(deptName);
		}
	}

	$(document).ready(function() {
		initTransportForm();

		// Auto select and fill department when employee is selected
		$(document).off('change', '#nama').on('change', '#nama', function() {
			updateDepartmentFromEmployee();
		});

		// Auto fill COA Name when COA is selected
		$(document).off('change', '#coa').on('change', '#coa', function() {
			var coaName = $(this).find(':selected').data('name');
			if (coaName) {
				$('#nm_coa').val(coaName);
			} else {
				var coa = $(this).val();
				if (coa) {
					$.ajax({
						type: 'post',
						url: siteurl + active_controller + 'get_coa_name',
						data: { 'no_coa': coa },
						dataType: 'json',
						success: function(result) {
							$('#nm_coa').val(result.nm_coa);
						}
					});
				}
			}
		});

		// Cost and KM calculation
		$(document).off('input keyup change', '.cost-calc, .auto-currency').on('input keyup change', '.cost-calc, .auto-currency', function() {
			calculateTotalCost();
		});

		$(document).off('input keyup change', '#km_awal, #km_akhir').on('input keyup change', '#km_awal, #km_akhir', function() {
			calculateTotalKm();
		});

		// Form Submission
		$(document).off('submit', '#frm_data').on('submit', '#frm_data', function(e) {
			e.preventDefault();

			var errors = "";
			var isNew = ($('#id').val() == "");

			if (isNew && $('#filename').val() == "" && $('#doc_file').get(0).files.length === 0) {
				errors = "Dokumen/Bukti harus diupload!";
			}
			if ($('#tgl_doc').val() == "") {
				errors = "Tanggal transaksi tidak boleh kosong!";
			}
			if ($('#nama').val() == "") {
				errors = "Karyawan harus dipilih!";
			}
			if ($('#departement').val() == "") {
				errors = "Departemen karyawan tidak boleh kosong! Pastikan karyawan memiliki data departemen.";
			}
			if ($('#coa').val() == "") {
				errors = "Akun COA harus dipilih!";
			}

			if (errors != "") {
				swal({
					title: "Perhatian!",
					text: errors,
					type: "warning"
				});
				return false;
			}

			swal({
				title: "Konfirmasi Simpan",
				text: "Apakah Anda yakin data pengajuan transportasi sudah benar?",
				type: "info",
				showCancelButton: true,
				confirmButtonText: "Ya, Simpan!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			}, function(isConfirm) {
				if (isConfirm) {
					var submitBtn = $('#btn_submit');
					submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

					var formdata = new FormData($('#frm_data')[0]);

					$.ajax({
						url: siteurl + 'expense/transport_save',
						dataType: "json",
						type: 'POST',
						data: formdata,
						processData: false,
						contentType: false,
						success: function(msg) {
							submitBtn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Data');
							if (msg.save == '1' || msg.save == 1 || msg.save == true) {
								swal({
									title: "Berhasil!",
									text: msg.msg || "Data transportasi berhasil disimpan!",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								setTimeout(function() {
									list_view();
								}, 1200);
							} else {
								swal({
									title: "Gagal!",
									text: msg.msg || "Terjadi kesalahan saat menyimpan data.",
									type: "error"
								});
							}
						},
						error: function(xhr, status, err) {
							submitBtn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Data');
							swal({
								title: "Error!",
								text: "Gagal memproses data melalui AJAX (" + err + ")",
								type: "error"
							});
						}
					});
				}
			});
		});
	});

	// Trigger immediately
	initTransportForm();
	setTimeout(initTransportForm, 100);

	function cleanNumber(val) {
		if (!val) return 0;
		var clean = String(val).replace(/[^0-9.-]/g, '');
		var num = parseFloat(clean);
		return isNaN(num) ? 0 : num;
	}

	function formatRupiah(num) {
		return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
	}

	function calculateTotalCost() {
		var bensin  = cleanNumber($('#bensin').val());
		var tol     = cleanNumber($('#tol').val());
		var parkir  = cleanNumber($('#parkir').val());
		var lainnya = cleanNumber($('#lainnya').val());

		var total = bensin + tol + parkir + lainnya;
		$('#lbl_total_kasbon').text(formatRupiah(total));
	}

	function calculateTotalKm() {
		var awal  = cleanNumber($('#km_awal').val());
		var akhir = cleanNumber($('#km_akhir').val());
		var diff  = akhir - awal;
		if (diff < 0) diff = 0;
		$('#lbl_total_km').text(diff.toLocaleString('id-ID'));
	}
</script>
