<?php
$ENABLE_ADD     = has_permission('Price_Ref_Raw_Material.Add');
$ENABLE_MANAGE  = has_permission('Price_Ref_Raw_Material.Manage');
$ENABLE_VIEW    = has_permission('Price_Ref_Raw_Material.View');
$ENABLE_DELETE  = has_permission('Price_Ref_Raw_Material.Delete');
?>

<div id="alert_edit" class="alert alert-success alert-dismissible" style="padding: 15px; display:none;"></div>

<div class="card shadow-sm border-0">
	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th style="width:60px;">#</th>
						<th>Material Code</th>
						<th>Material Master</th>
						<th class="text-end">Price Ref IDR</th>
						<th class="text-end">Price Ref USD</th>
						<th class="text-center">Expired</th>
						<th>Status</th>
						<th style="width:140px;" class="text-end">Action</th>
					</tr>
				</thead>

				<tbody>
					<?php if (!empty($result)) {
						$numb = 0;
						foreach ($result as $record) {
							$numb++;
							$date_now = date('Y-m-d');

							$status = 'Not Set';
							$status_ = 'warning';

							$status2 = '';
							$status2_ = '';

							$expired = '-';

							// status existing (use)
							if (!empty($record->price_ref_date_use)) {
								$price_ref_date = date('Y-m-d', strtotime('+' . $record->price_ref_expired_use . ' month', strtotime($record->price_ref_date_use)));
								$expired = date('d-M-Y', strtotime($price_ref_date));

								if ($date_now > $price_ref_date) {
									$status = 'Expired';
									$status_ = 'danger';
								} else {
									$status = 'Oke';
									$status_ = 'success';
								}
							}

							// waiting approve
							if ($record->status_app == 'Y') {
								$status2 = 'Waiting Approve';
								$status2_ = 'secondary';
							}
					?>
							<tr>
								<td class="text-center"><?= $numb; ?></td>
								<td><?= strtoupper($record->code) ?></td>
								<td><?= strtoupper($record->nama) ?></td>
								<td class="text-end"><?= number_format($record->price_ref_use) ?></td>
								<td class="text-end"><?= number_format($record->price_ref_use_usd, 2) ?></td>
								<td class="text-center"><?= $expired; ?></td>

								<td>
									<span class="badge bg-<?= $status_; ?>"><?= $status; ?></span>
									<?php if (!empty($status2)) { ?>
										<br><span class="badge bg-<?= $status2_; ?> mt-1"><?= $status2; ?></span>
									<?php } ?>
								</td>

								<td class="text-end">
									<?php if ($ENABLE_MANAGE && $record->status_app == 'Y') : ?>
										<a class="btn-icon btn-icon-view edit" href="javascript:void(0)" data-id="<?= $record->id ?>" title="Approve">
											<i class="fas fa-check-double"></i>
										</a>
									<?php endif; ?>

									<?php if (!empty($record->upload_file)) : ?>
										<a class="btn-icon btn-icon-view" href="<?= base_url($record->upload_file); ?>" target="_blank" title="Download">
											<i class="ti ti-download"></i>
										</a>
									<?php endif; ?>
								</td>
							</tr>
					<?php }
					} ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- ✅ Modal (BS5 style + scrollable) -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">
			<form id="data_form" method="post" autocomplete="off" enctype="multipart/form-data">
				<div class="modal-header">
					<h5 class="modal-title" id="head_title">Material Master</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body" id="ModalView"></div>

				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">
						<i class="ti ti-device-floppy me-1"></i> Save
					</button>
					<button type="button" class="btn btn-dark" data-bs-dismiss="modal">
						<i class="ti ti-x me-1"></i> Cancel
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
	$(function() {
		$('#example1').DataTable({
			orderCellsTop: true,
			fixedHeader: true
		});
	});

	// helper safe number
	function getNum(val) {
		val = (val || '').toString().replaceAll(',', '');
		const n = parseFloat(val);
		return isNaN(n) ? 0 : n;
	}

	// ✅ Open Modal Edit/Approve
	$(document).on('click', '.edit', function() {
		const id = $(this).data('id');
		$('#head_title').text('Material Master');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + '/add/' + id,
			success: function(html) {
				$('#ModalView').html(html);

				const modalEl = document.getElementById('dialog-popup');
				const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
				modal.show();

				// optional: re-init select2/chosen if used in modal
				if ($.fn.select2) {
					$('#dialog-popup .chosen-select').select2({
						width: '100%',
						dropdownParent: $('#dialog-popup')
					});
				}

				// optional: re-init autonumeric if needed
				if ($.fn.autoNumeric) {
					$('#dialog-popup .autoNumeric').autoNumeric('init', {
						mDec: '0',
						aPad: false
					});
					$('#dialog-popup .autoNumeric6').autoNumeric('init', {
						mDec: '6',
						aPad: false
					});
				}
			}
		});
	});

	// ✅ Open Modal Add
	$(document).on('click', '.add', function() {
		$('#head_title').text('Material Master');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + '/add/',
			success: function(html) {
				$('#ModalView').html(html);

				const modalEl = document.getElementById('dialog-popup');
				const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
				modal.show();

				if ($.fn.select2) {
					$('#dialog-popup .chosen-select').select2({
						width: '100%',
						dropdownParent: $('#dialog-popup')
					});
				}

				if ($.fn.autoNumeric) {
					$('#dialog-popup .autoNumeric').autoNumeric('init', {
						mDec: '0',
						aPad: false
					});
					$('#dialog-popup .autoNumeric6').autoNumeric('init', {
						mDec: '6',
						aPad: false
					});
				}
			}
		});
	});

	// ✅ Submit (logic tetap)
	$(document).on('submit', '#data_form', function(e) {
		e.preventDefault();

		const price_ref_expired_use_after = $('#price_ref_expired_use_after').val();
		const action_app = $('#action_app').val();

		if (price_ref_expired_use_after == '0' && action_app == '1') {
			swal({
				title: "Error Message!",
				text: "Expired not selected...",
				type: "warning"
			});
			return false;
		}

		swal({
			title: "Anda Yakin?",
			text: "Data akan diproses!",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-info",
			confirmButtonText: "Yes",
			cancelButtonText: "No",
			closeOnConfirm: false
		}, function() {

			const formData = new FormData(document.getElementById('data_form'));

			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + 'add',
				dataType: "json",
				data: formData,
				processData: false,
				contentType: false,
				success: function(res) {
					if (res.status == '1') {
						swal({
							title: "Sukses",
							text: res.pesan,
							type: "success"
						}, function() {
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
						text: "Error process!",
						type: "error"
					});
				}
			});
		});
	});

	// ✅ Kurs & conversion (keep logic from your original)
	$(document).on('keyup', '#price_ref_use_after', function() {
		const price_ref = getNum($('#price_ref_use_after').val());
		const kurs = getNum($('#kurs').val());
		const price = kurs ? (price_ref / kurs) : 0;
		$('#price_ref_use_after_usd').val(price);
	});

	$(document).on('keyup', '#price_ref_use_after_usd', function() {
		const price_ref = getNum($('#price_ref_use_after_usd').val());
		const kurs = getNum($('#kurs').val());
		$('#price_ref_use_after').val(price_ref * kurs);
	});
</script>