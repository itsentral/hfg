<?php
$stsview_val = isset($stsview) ? $stsview : '';
$is_view = ($stsview_val == 'view' || $stsview_val == 'approval' || $mod == '_fin' || $mod == '_mgt');
$is_approval = ($stsview_val === 'approval');
$default_date = isset($data->tgl_doc) ? $data->tgl_doc : date('Y-m-d');
$default_date1 = isset($data->date1) ? $data->date1 : date('Y-m-01');
$default_date2 = isset($data->date2) ? $data->date2 : date('Y-m-d');

$selected_nama = isset($data->nama) ? $data->nama : (!empty($current_employee) ? $current_employee->nm_karyawan : (!empty($current_user->nm_lengkap) ? $current_user->nm_lengkap : $this->auth->user_name()));
$selected_dept = isset($data->departement) ? $data->departement : (!empty($current_user->department_id) ? $current_user->department_id : (!empty($current_employee->department) ? $current_employee->department : ''));

$dept_map = [];
if (!empty($data_departement)) {
	foreach ($data_departement as $d) {
		$dept_map[$d->id] = $d->nm_dept;
	}
}
$selected_dept_name = isset($dept_map[$selected_dept]) ? $dept_map[$selected_dept] : $selected_dept;

$bank_id   = isset($data->bank_id) ? $data->bank_id : (!empty($current_employee->bank_account) ? $current_employee->bank_account : '');
$accnumber = isset($data->accnumber) ? $data->accnumber : (!empty($current_employee->rek_number) ? $current_employee->rek_number : '');
$accname   = isset($data->accname) ? $data->accname : (!empty($current_employee->nm_karyawan) ? $current_employee->nm_karyawan : '');
$mod = isset($mod) ? $mod : '';
?>

<!-- Flatpickr CSS fallback -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card shadow-sm border-0 mb-4">
	<div class="card-header bg-transparent py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
		<div class="d-flex align-items-center">
			<div class="avatar avatar-sm bg-light-primary text-primary me-2 rounded-2 d-flex align-items-center justify-content-center p-2">
				<i class="fa <?= $is_view ? 'fa-eye' : (isset($data->id) ? 'fa-edit' : 'fa-plus') ?> fs-5"></i>
			</div>
			<div>
				<h5 class="card-title mb-0 fw-bold text-dark">
					<?php 
						if (isset($data->id) && $data->id != '') {
							if ($stsview_val == 'view') {
								echo 'Detail Pengajuan Transportasi';
							} elseif ($mod == '_fin' || $stsview_val == 'approval') {
								echo 'Verifikasi / Approval Finance Penggantian Transport';
							} elseif ($mod == '_mgt') {
								echo 'Persetujuan Management Penggantian Transport';
							} else {
								echo 'Edit Pengajuan Transportasi';
							}
						} else {
							echo 'Form Pengajuan Penggantian Transport';
						}
					?>
				</h5>
				<small class="text-muted"><?php
					if ($stsview_val == 'view') {
						echo 'Lihat rincian pengajuan penggantian transport (Readonly)';
					} elseif ($is_approval) {
						echo 'Periksa rincian pengajuan transport karyawan sebelum menyetujui atau menolak';
					} elseif ($is_view) {
						echo 'Lihat rincian rekapitulasi transport';
					} else {
						echo 'Pilih karyawan dan periode untuk menarik data rekapitulasi perjalanan';
					}
				?></small>
			</div>
		</div>
		<div>
			<button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1" onclick="list_view()">
				<i class="fa fa-arrow-left"></i>
				<span>Kembali</span>
			</button>
		</div>
	</div>

	<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'class' => 'needs-validation')); ?>
	<input type="hidden" id="id" name="id" value="<?= set_value('id', isset($data->id) ? $data->id : ''); ?>">

	<div class="card-body p-4">
		<!-- Section 1: Informasi Header Pengajuan -->
		<div class="p-3 mb-4 rounded-3 border bg-light">
			<h6 class="fw-bold text-dark mb-3"><i class="fa fa-info-circle text-primary me-1"></i> Informasi Pengajuan</h6>
			<div class="row g-3">
				<div class="col-md-3">
					<label for="no_doc" class="form-label fw-semibold text-secondary small">No. Dokumen</label>
					<input type="text" class="form-control form-control-sm bg-white" id="no_doc" name="no_doc" value="<?= (isset($data->no_doc) ? $data->no_doc : ''); ?>" placeholder="Otomatis" readonly>
				</div>
				<div class="col-md-3">
					<label for="tgl_doc" class="form-label fw-semibold text-secondary small">Tanggal Pengajuan <span class="text-danger">*</span></label>
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
								<option value="<?= htmlspecialchars($emp->nm_karyawan); ?>" 
										data-department="<?= htmlspecialchars($emp_dept_id); ?>" 
										data-department-name="<?= htmlspecialchars($emp_dept_name); ?>"
										data-bank="<?= htmlspecialchars(!empty($emp->bank_account) ? $emp->bank_account : ''); ?>"
										data-rekening="<?= htmlspecialchars(!empty($emp->rek_number) ? $emp->rek_number : ''); ?>"
										data-accname="<?= htmlspecialchars($emp->nm_karyawan); ?>"
										<?= $selected; ?>>
									<?= htmlspecialchars($emp->nm_karyawan); ?> (<?= htmlspecialchars($emp->nik); ?>)
								</option>
							<?php endforeach; ?>
						<?php else: ?>
							<option value="<?= htmlspecialchars($selected_nama); ?>" selected><?= htmlspecialchars($selected_nama); ?></option>
						<?php endif; ?>
					</select>
				</div>
				<div class="col-md-3">
					<label for="nama_departement" class="form-label fw-semibold text-secondary small">Departemen</label>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-light text-muted"><i class="fa fa-building"></i></span>
						<input type="text" class="form-control bg-light fw-medium" id="nama_departement" value="<?= htmlspecialchars($selected_dept_name); ?>" placeholder="Otomatis mengikuti karyawan" readonly>
					</div>
					<input type="hidden" id="departement" name="departement" value="<?= htmlspecialchars($selected_dept); ?>">
				</div>

				<div class="col-md-3">
					<label for="date1" class="form-label fw-semibold text-secondary small">Periode Awal <span class="text-danger">*</span></label>
					<div class="input-group input-group-sm">
						<input type="text" class="form-control flatpickr-input bg-white" id="date1" name="date1" value="<?= $default_date1; ?>" placeholder="YYYY-MM-DD" required <?= $is_view ? 'disabled' : ''; ?>>
						<span class="input-group-text bg-white" onclick="$('#date1').focus()"><i class="fa fa-calendar text-muted"></i></span>
					</div>
				</div>
				<div class="col-md-3">
					<label for="date2" class="form-label fw-semibold text-secondary small">Periode Akhir <span class="text-danger">*</span></label>
					<div class="input-group input-group-sm">
						<input type="text" class="form-control flatpickr-input bg-white" id="date2" name="date2" value="<?= $default_date2; ?>" placeholder="YYYY-MM-DD" required <?= $is_view ? 'disabled' : ''; ?>>
						<span class="input-group-text bg-white" onclick="$('#date2').focus()"><i class="fa fa-calendar text-muted"></i></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Rejection Alert if Any -->
		<?php if (!empty($data->st_reject)): ?>
			<div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
				<i class="fa fa-exclamation-triangle fs-4 me-3"></i>
				<div>
					<h6 class="alert-heading fw-bold mb-1">Pengajuan Ditolak!</h6>
					<div>Alasan: <?= htmlspecialchars($data->st_reject); ?></div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Section 2: Rekening Pembayaran -->
		<div class="p-3 mb-4 rounded-3 border bg-light">
			<h6 class="fw-bold text-dark mb-3"><i class="fa fa-credit-card text-success me-1"></i> Informasi Rekening Transfer</h6>
			<div class="row g-3">
				<div class="col-md-4">
					<label for="bank_id" class="form-label fw-semibold text-secondary small">Nama Bank</label>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-white text-muted"><i class="fa fa-university"></i></span>
						<input type="text" class="form-control bg-white" id="bank_id" name="bank_id" value="<?= htmlspecialchars($bank_id); ?>" placeholder="Contoh: BCA / Mandiri / BNI" <?= $is_view ? 'readonly' : ''; ?>>
					</div>
				</div>
				<div class="col-md-4">
					<label for="accnumber" class="form-label fw-semibold text-secondary small">Nomor Rekening</label>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-white text-muted"><i class="fa fa-hashtag"></i></span>
						<input type="text" class="form-control bg-white" id="accnumber" name="accnumber" value="<?= htmlspecialchars($accnumber); ?>" placeholder="Nomor rekening transfer" <?= $is_view ? 'readonly' : ''; ?>>
					</div>
				</div>
				<div class="col-md-4">
					<label for="accname" class="form-label fw-semibold text-secondary small">Atas Nama Rekening</label>
					<div class="input-group input-group-sm">
						<span class="input-group-text bg-white text-muted"><i class="fa fa-user"></i></span>
						<input type="text" class="form-control bg-white" id="accname" name="accname" value="<?= htmlspecialchars($accname); ?>" placeholder="Nama pemilik rekening" <?= $is_view ? 'readonly' : ''; ?>>
					</div>
				</div>
			</div>
		</div>

		<!-- Section 3: Tabel Rincian Transportasi -->
		<div class="p-3 mb-4 rounded-3 border bg-white">
			<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
				<div>
					<h6 class="fw-bold text-dark mb-0"><i class="fa fa-list-alt text-primary me-1"></i> Rincian Dokumen Transportasi</h6>
					<small class="text-muted">Item perjalanan dinas yang akan dimasukkan ke pengajuan ini</small>
				</div>
				<?php if (!$is_view): ?>
					<div>
						<button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" id="btn_generate" onclick="add_detail()">
							<i class="fa fa-sync-alt"></i>
							<span>Tampilkan Data Transport</span>
						</button>
					</div>
				<?php endif; ?>
			</div>

			<div class="table-responsive">
				<table class="table table-bordered table-hover align-middle table-sm" id="table_detail">
					<thead class="table-light">
						<tr>
							<th width="40" class="text-center">#</th>
							<th>No. Dokumen</th>
							<th>Tanggal</th>
							<th>COA</th>
							<th>Keperluan</th>
							<th>Rute</th>
							<th class="text-end">Bensin</th>
							<th class="text-end">Tol</th>
							<th class="text-end">Parkir</th>
							<th class="text-end">Lain-lain</th>
							<th class="text-center">Total KM</th>
							<th width="60" class="text-center">Bukti</th>
							<?php if (!$is_view): ?>
								<th width="40" class="text-center stsview">Aksi</th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody id="detail_body">
						<?php 
						$total_bensin = 0;
						$total_tol = 0;
						$total_parkir = 0;
						$total_lainnya = 0;
						$total_km = 0;
						$grand_total = 0;
						$idd = 1;
						$gambar = '';

						if (!empty($data_detail)):
							foreach ($data_detail as $record):
								$km_diff = ((float)$record->km_akhir - (float)$record->km_awal);
								if ($km_diff < 0) $km_diff = 0;

								$total_bensin  += (float)$record->bensin;
								$total_tol     += (float)$record->tol;
								$total_parkir  += (float)$record->parkir;
								$total_lainnya += (float)$record->lainnya;
								$total_km      += $km_diff;

								$file_url = '';
								if (!empty($record->doc_file)) {
									if (file_exists('./uploads/expense/' . $record->doc_file)) {
										$file_url = base_url('uploads/expense/' . $record->doc_file);
									} else {
										$file_url = base_url('assets/expense/' . $record->doc_file);
									}
								}
						?>
							<tr id="row_<?= $idd ?>" class="delAll kasbonrow align-middle">
								<td class="text-center"><?= $idd ?></td>
								<td>
									<input type="hidden" name="id_transport[]" value="<?= $record->id; ?>">
									<input type="hidden" class="fben" value="<?= $record->bensin; ?>">
									<input type="hidden" class="ftol" value="<?= $record->tol; ?>">
									<input type="hidden" class="fpark" value="<?= $record->parkir; ?>">
									<input type="hidden" class="flainnya" value="<?= $record->lainnya; ?>">
									<input type="hidden" class="fkm" value="<?= $km_diff; ?>">
									<span class="fw-semibold text-primary"><?= htmlspecialchars($record->no_doc); ?></span>
								</td>
								<td><?= htmlspecialchars($record->tgl_doc); ?></td>
								<td class="small fw-semibold text-secondary">
									<?= htmlspecialchars(!empty($record->no_coa) ? ($record->no_coa . ' - ' . $record->nm_coa) : '-'); ?>
								</td>
								<td class="small"><?= htmlspecialchars($record->keperluan); ?></td>
								<td class="small"><?= htmlspecialchars($record->rute); ?></td>
								<td class="text-end fw-medium"><?= number_format($record->bensin, 0, ',', '.'); ?></td>
								<td class="text-end fw-medium"><?= number_format($record->tol, 0, ',', '.'); ?></td>
								<td class="text-end fw-medium"><?= number_format($record->parkir, 0, ',', '.'); ?></td>
								<td class="text-end fw-medium"><?= number_format($record->lainnya, 0, ',', '.'); ?></td>
								<td class="text-center small"><?= number_format($km_diff, 0, ',', '.'); ?> KM</td>
								<td class="text-center">
									<?php if (!empty($file_url)): ?>
										<a href="<?= $file_url ?>" target="_blank" class="btn btn-outline-primary btn-xs" title="Lihat Dokumen">
											<i class="fa fa-file-download"></i>
										</a>
									<?php else: ?>
										<span class="text-muted">-</span>
									<?php endif; ?>
								</td>
								<?php if (!$is_view): ?>
									<td class="text-center stsview">
										<button type="button" class="btn btn-outline-danger btn-xs" onclick="removeRow('row_<?= $idd ?>')" title="Hapus"><i class="fa fa-trash"></i></button>
									</td>
								<?php endif; ?>
							</tr>
						<?php 
								$idd++;
							endforeach;
						endif;
						$grand_total = ($total_bensin + $total_tol + $total_parkir + $total_lainnya);
						?>
					</tbody>
					<tfoot class="table-light">
						<tr>
							<th colspan="6" class="text-end fw-bold">SUB TOTAL:</th>
							<th class="text-end fw-bold" id="lbl_sub_bensin">Rp <?= number_format($total_bensin, 0, ',', '.'); ?></th>
							<th class="text-end fw-bold" id="lbl_sub_tol">Rp <?= number_format($total_tol, 0, ',', '.'); ?></th>
							<th class="text-end fw-bold" id="lbl_sub_parkir">Rp <?= number_format($total_parkir, 0, ',', '.'); ?></th>
							<th class="text-end fw-bold" id="lbl_sub_lainnya">Rp <?= number_format($total_lainnya, 0, ',', '.'); ?></th>
							<th class="text-center fw-bold" id="lbl_sub_km"><?= number_format($total_km, 0, ',', '.'); ?> KM</th>
							<th colspan="<?= $is_view ? '1' : '2' ?>"></th>
						</tr>
						<tr class="table-primary">
							<th colspan="6" class="text-end fw-bolder fs-6">TOTAL PENGAJUAN:</th>
							<th colspan="4" class="text-end fw-bolder fs-6 text-primary" id="lbl_grand_total">
								Rp <?= number_format($grand_total, 0, ',', '.'); ?>
							</th>
							<th colspan="<?= $is_view ? '2' : '3' ?>">
								<input type="hidden" id="jumlah_expense" name="jumlah_expense" value="<?= $grand_total; ?>">
							</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

		<!-- Footer Actions -->
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
			<div>
				<button type="button" class="btn btn-outline-secondary btn-sm" onclick="list_view()">
					<i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
				</button>
			</div>
			<div class="d-flex align-items-center gap-2">
				<?php if (isset($data) && !empty($data->id) && $stsview_val !== 'view'): ?>
					<?php if ($is_approval && ($data->status == 0 || $data->status == 1)): ?>
						<button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1" onclick="data_approve(<?= $data->id; ?>, <?= ($data->status + 1); ?>)">
							<i class="fa fa-check-circle"></i> Setujui Pengajuan
						</button>
						<button type="button" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1" onclick="data_reject()">
							<i class="fa fa-ban"></i> Tolak Pengajuan
						</button>
					<?php endif; ?>
				<?php endif; ?>

				<?php if (!$is_view): ?>
					<button type="submit" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" id="btn_save">
						<i class="fa fa-save"></i> Simpan Pengajuan
					</button>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?= form_close(); ?>
</div>

<script type="text/javascript">
	var url_save = siteurl + 'expense/transport_req_save/';
	var url_approve = siteurl + 'expense/transport_req_approve/';
	var is_view = <?= $is_view ? 'true' : 'false'; ?>;

	function initTransportReqForm() {
		// Initialize Flatpickr for dates
		if (typeof flatpickr !== 'undefined') {
			var dateConfig = {
				dateFormat: "Y-m-d",
				allowInput: true,
				locale: "id",
				static: true
			};
			if (!is_view) {
				flatpickr("#tgl_doc", dateConfig);
				flatpickr("#date1", dateConfig);
				flatpickr("#date2", dateConfig);
			}
		}

		// Initialize Select2 for employee selection
		if ($.fn.select2 && !is_view) {
			$('#nama').select2({
				placeholder: "-- Pilih Karyawan --",
				allowClear: true,
				width: '100%',
				dropdownParent: $('#frm_data')
			});
		}

		// Handle employee change -> auto-populate department and bank account
		$('#nama').on('change', function() {
			var selected = $(this).find('option:selected');
			if (selected.length && selected.val() !== "") {
				var deptId   = selected.data('department') || '';
				var deptName = selected.data('department-name') || '';
				var bank     = selected.data('bank') || '';
				var rekening = selected.data('rekening') || '';
				var accname  = selected.data('accname') || '';

				$('#departement').val(deptId);
				$('#nama_departement').val(deptName ? deptName : deptId);

				if (bank) $('#bank_id').val(bank);
				if (rekening) $('#accnumber').val(rekening);
				if (accname) $('#accname').val(accname);
			}
		});

		calculateAllTotals();
	}

	function removeRow(rowId) {
		$('#' + rowId).remove();
		calculateAllTotals();
	}

	function cleanNumber(val) {
		if (!val) return 0;
		var clean = String(val).replace(/[^0-9.-]/g, '');
		var num = parseFloat(clean);
		return isNaN(num) ? 0 : num;
	}

	function formatRupiahSimple(val) {
		var n = cleanNumber(val);
		return 'Rp ' + n.toLocaleString('id-ID');
	}

	function calculateAllTotals() {
		var total_bensin = 0;
		var total_tol = 0;
		var total_parkir = 0;
		var total_lainnya = 0;
		var total_km = 0;

		$('.fben').each(function() { total_bensin += cleanNumber($(this).val()); });
		$('.ftol').each(function() { total_tol += cleanNumber($(this).val()); });
		$('.fpark').each(function() { total_parkir += cleanNumber($(this).val()); });
		$('.flainnya').each(function() { total_lainnya += cleanNumber($(this).val()); });
		$('.fkm').each(function() { total_km += cleanNumber($(this).val()); });

		var grand_total = total_bensin + total_tol + total_parkir + total_lainnya;

		$('#lbl_sub_bensin').text(formatRupiahSimple(total_bensin));
		$('#lbl_sub_tol').text(formatRupiahSimple(total_tol));
		$('#lbl_sub_parkir').text(formatRupiahSimple(total_parkir));
		$('#lbl_sub_lainnya').text(formatRupiahSimple(total_lainnya));
		$('#lbl_sub_km').text(total_km.toLocaleString('id-ID') + ' KM');

		$('#lbl_grand_total').text(formatRupiahSimple(grand_total));
		$('#jumlah_expense').val(grand_total);
	}

	function add_detail() {
		var nama = $('#nama').val();
		var departement = $('#departement').val();
		var date1 = $('#date1').val();
		var date2 = $('#date2').val();

		if (!nama) {
			swal({
				title: "Perhatian",
				text: "Silakan pilih karyawan terlebih dahulu.",
				type: "warning"
			});
			return;
		}
		if (!date1 || !date2) {
			swal({
				title: "Perhatian",
				text: "Silakan lengkapi Periode Awal dan Periode Akhir.",
				type: "warning"
			});
			return;
		}

		var btn = $('#btn_generate');
		btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Mengambil data...');

		$.ajax({
			url: siteurl + 'expense/get_list_req_transport',
			type: 'POST',
			data: {
				nama: nama,
				departement: departement,
				date1: date1,
				date2: date2
			},
			dataType: 'json',
			success: function(data) {
				btn.prop('disabled', false).html('<i class="fa fa-sync-alt me-1"></i> Tampilkan Data Transport');

				// Clear existing items that were dynamically pulled
				$('.kasbonrow').remove();

				if (!data || data.length === 0) {
					swal({
						title: "Info",
						text: "Tidak ada data transport yang belum diajukan untuk karyawan ini pada periode " + date1 + " s/d " + date2,
						type: "info"
					});
					calculateAllTotals();
					return;
				}

				for (var i = 0; i < data.length; i++) {
					var item = data[i];
					var rowId = 'row_dyn_' + item.id;

					var km_diff = (parseFloat(item.km_akhir || 0) - parseFloat(item.km_awal || 0));
					if (km_diff < 0) km_diff = 0;

					var file_link = '<span class="text-muted">-</span>';
					if (item.doc_file && item.doc_file !== '') {
						var file_url = '<?= base_url('uploads/expense/') ?>' + item.doc_file;
						file_link = '<a href="' + file_url + '" target="_blank" class="btn btn-outline-primary btn-xs" title="Lihat Bukti"><i class="fa fa-file-download"></i></a>';
					}

					var coa_display = item.no_coa ? (item.no_coa + ' - ' + (item.nm_coa || '')) : '-';

					var row = '<tr id="' + rowId + '" class="delAll kasbonrow align-middle">';
					row += '<td class="text-center">' + (i + 1) + '</td>';
					row += '<td>';
					row += '<input type="hidden" name="id_transport[]" value="' + item.id + '">';
					row += '<input type="hidden" class="fben" value="' + (item.bensin || 0) + '">';
					row += '<input type="hidden" class="ftol" value="' + (item.tol || 0) + '">';
					row += '<input type="hidden" class="fpark" value="' + (item.parkir || 0) + '">';
					row += '<input type="hidden" class="flainnya" value="' + (item.lainnya || 0) + '">';
					row += '<input type="hidden" class="fkm" value="' + km_diff + '">';
					row += '<span class="fw-semibold text-primary">' + item.no_doc + '</span>';
					row += '</td>';
					row += '<td>' + item.tgl_doc + '</td>';
					row += '<td class="small fw-semibold text-secondary">' + coa_display + '</td>';
					row += '<td class="small">' + (item.keperluan || '-') + '</td>';
					row += '<td class="small">' + (item.rute || '-') + '</td>';
					row += '<td class="text-end fw-medium">' + cleanNumber(item.bensin).toLocaleString('id-ID') + '</td>';
					row += '<td class="text-end fw-medium">' + cleanNumber(item.tol).toLocaleString('id-ID') + '</td>';
					row += '<td class="text-end fw-medium">' + cleanNumber(item.parkir).toLocaleString('id-ID') + '</td>';
					row += '<td class="text-end fw-medium">' + cleanNumber(item.lainnya).toLocaleString('id-ID') + '</td>';
					row += '<td class="text-center small">' + km_diff.toLocaleString('id-ID') + ' KM</td>';
					row += '<td class="text-center">' + file_link + '</td>';
					row += '<td class="text-center stsview">';
					row += '<button type="button" class="btn btn-outline-danger btn-xs" onclick="removeRow(\'' + rowId + '\')" title="Hapus"><i class="fa fa-trash"></i></button>';
					row += '</td>';
					row += '</tr>';

					$('#detail_body').append(row);
				}

				calculateAllTotals();
			},
			error: function(xhr, status, err) {
				btn.prop('disabled', false).html('<i class="fa fa-sync-alt me-1"></i> Tampilkan Data Transport');
				swal({
					title: "Error!",
					text: "Gagal mengambil data transport (" + err + ")",
					type: "error"
				});
			}
		});
	}

	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var countTransport = $('input[name="id_transport[]"]').length;
		if (countTransport === 0) {
			swal({
				title: "Peringatan",
				text: "Belum ada item transport yang dimasukkan ke dalam pengajuan ini. Silakan klik tombol 'Tampilkan Data Transport'.",
				type: "warning"
			});
			return false;
		}

		if ($('#jumlah_expense').val() == "0" || $('#jumlah_expense').val() == "") {
			swal({
				title: "Peringatan",
				text: "Total pengajuan tidak boleh 0.",
				type: "warning"
			});
			return false;
		}

		swal({
			title: "Konfirmasi Simpan",
			text: "Apakah Anda yakin ingin menyimpan pengajuan penggantian transport ini?",
			type: "info",
			showCancelButton: true,
			confirmButtonText: "Ya, Simpan!",
			cancelButtonText: "Batal",
			closeOnConfirm: false
		}, function(isConfirm) {
			if (isConfirm) {
				var btnSave = $('#btn_save');
				btnSave.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...');
				var formdata = new FormData($('#frm_data')[0]);

				$.ajax({
					url: url_save,
					dataType: "json",
					type: 'POST',
					data: formdata,
					processData: false,
					contentType: false,
					success: function(msg) {
						btnSave.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Pengajuan');
						if (msg.save == 1 || msg.save == true) {
							swal({
								title: "Sukses!",
								text: msg.msg || "Pengajuan transport berhasil disimpan.",
								type: "success",
								timer: 1500,
								showConfirmButton: false
							});
							list_view();
						} else {
							swal({
								title: "Gagal!",
								text: msg.msg || "Data gagal disimpan.",
								type: "error"
							});
						}
					},
					error: function(xhr, status, err) {
						btnSave.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Pengajuan');
						swal({
							title: "Error!",
							text: "Terjadi kesalahan saat memproses data (" + err + ")",
							type: "error"
						});
					}
				});
			}
		});
	});

	function data_approve(id, status) {
		Swal.fire({
			title: "Konfirmasi Persetujuan",
			text: "Apakah Anda yakin ingin menyetujui pengajuan penggantian transport ini?",
			icon: "question",
			showCancelButton: true,
			confirmButtonColor: "#198754",
			cancelButtonColor: "#6c757d",
			confirmButtonText: '<i class="fa fa-check me-1"></i> Ya, Setujui!',
			cancelButtonText: "Batal"
		}).then((result) => {
			if (result.isConfirmed) {
				Swal.fire({
					title: "Memproses...",
					text: "Sedang menyimpan persetujuan...",
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: url_approve + id + '/' + status,
					dataType: "json",
					type: 'POST',
					success: function(msg) {
						if (msg.save == '1' || msg.save == 1 || msg.save == true) {
							Swal.fire({
								icon: "success",
								title: "Berhasil Disetujui!",
								text: msg.msg || "Data pengajuan transport berhasil disetujui.",
								timer: 2000,
								showConfirmButton: false
							});
							list_view();
						} else {
							Swal.fire({
								icon: "error",
								title: "Gagal!",
								text: msg.msg || "Data gagal disetujui."
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: "error",
							title: "Error!",
							text: "Terjadi kesalahan koneksi saat memproses persetujuan."
						});
					}
				});
			}
		});
	}

	function data_reject() {
		var id = $("#id").val();
		if (!id) {
			Swal.fire({
				icon: "warning",
				title: "Peringatan",
				text: "ID pengajuan tidak ditemukan."
			});
			return;
		}

		Swal.fire({
			title: "Alasan Penolakan",
			text: "Silakan masukkan alasan penolakan pengajuan transport ini:",
			input: "textarea",
			inputPlaceholder: "Tuliskan alasan penolakan di sini...",
			inputAttributes: {
				"aria-label": "Alasan penolakan",
				"rows": 4
			},
			showCancelButton: true,
			confirmButtonColor: "#dc3545",
			cancelButtonColor: "#6c757d",
			confirmButtonText: '<i class="fa fa-ban me-1"></i> Tolak Pengajuan',
			cancelButtonText: "Batal",
			inputValidator: (value) => {
				if (!value || !value.trim()) {
					return "Alasan penolakan wajib diisi!";
				}
			}
		}).then((result) => {
			if (result.isConfirmed) {
				var reason = result.value.trim();
				Swal.fire({
					title: "Memproses...",
					text: "Sedang memproses penolakan...",
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: siteurl + 'expense/reject/',
					data: {
						'id': id,
						'reason': reason,
						'table': 'tr_transport_req'
					},
					dataType: "json",
					type: 'POST',
					success: function(msg) {
						if (msg.save == 1 || msg.save == true || msg.status == 1 || msg.status == true) {
							Swal.fire({
								icon: "success",
								title: "Berhasil Ditolak!",
								text: msg.msg || "Data pengajuan transport berhasil ditolak.",
								timer: 2000,
								showConfirmButton: false
							});
							list_view();
						} else {
							Swal.fire({
								icon: "error",
								title: "Gagal!",
								text: msg.msg || "Data gagal ditolak."
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: "error",
							title: "Error!",
							text: "Terjadi kesalahan sistem saat memproses penolakan."
						});
					}
				});
			}
		});
	}

		initTransportReqForm();
	setTimeout(initTransportReqForm, 100);
</script>
