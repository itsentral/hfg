<?php
$ENABLE_ADD     = has_permission('Price_Supplier_Raw_Material.Add');
$ENABLE_MANAGE  = has_permission('Price_Supplier_Raw_Material.Manage');
$ENABLE_VIEW    = has_permission('Price_Supplier_Raw_Material.View');
$ENABLE_DELETE  = has_permission('Price_Supplier_Raw_Material.Delete');
?>


<div class="card shadow-sm border-0">
	<div class="card-header bg-white d-flex align-items-center justify-content-between">
		<div><a class="btn btn-success" href="<?= base_url($this->uri->segment(1) . '/excel_report') ?>" target="_blank" title="Download Excel">
				<i class="fa fa-file-excel-o me-1"></i> Download Excel
			</a></div>
		<div>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th style="width:60px;">#</th>
						<th>Material Code</th>
						<th>Material Master</th>
						<th class="text-end">Lower Price<br>Before</th>
						<th class="text-end">Lower Price<br>After</th>
						<th class="text-end">Higher Price<br>Before</th>
						<th class="text-end">Higher Price<br>After</th>
						<th class="text-center">Expired<br>Before</th>
						<th class="text-center">Expired<br>After</th>
						<th>Status</th>
						<th>Alasan Reject</th>
						<th style="width:140px;" class="text-end">Action</th>
					</tr>
				</thead>

				<tbody>
					<?php if (!empty($result)) {
						$numb = 0;
						foreach ($result as $record) {
							$numb++;

							$tgl_create 	= $record->price_ref_new_date;
							$max_exp 		= $record->price_ref_new_expired;
							$tgl_expired 	= date('Y-m-d', strtotime('+' . $max_exp . ' month', strtotime($tgl_create)));
							$date_now		= date('Y-m-d');

							$status = 'Not Set';
							$status_ = 'warning';
							$status2 = '';
							$status2_ = '';

							$expired = '-';
							$expired_new = '-';

							if (!empty($record->price_ref_date)) {
								$price_ref_date 	= date('Y-m-d', strtotime('+' . $record->price_ref_expired . ' month', strtotime($record->price_ref_date)));
								$expired = date('d-M-Y', strtotime($price_ref_date));

								if ($date_now > $price_ref_date) {
									$status = 'Expired';
									$status_ = 'danger';
								} else {
									$status = 'Oke';
									$status_ = 'success';
								}
							}

							if ($record->status_app == 'Y') {
								$expired_new = date('d-M-Y', strtotime($tgl_expired));
								$status2 = 'Waiting Approve';
								$status2_ = 'secondary';
							}
					?>
							<tr>
								<td class="text-center"><?= $numb; ?></td>
								<td><?= strtoupper($record->code) ?></td>
								<td><?= strtoupper($record->nama) ?></td>

								<td class="text-end"><?= number_format($record->price_ref, 2) ?></td>
								<td class="text-end"><?= number_format($record->price_ref_new, 2) ?></td>
								<td class="text-end"><?= number_format($record->price_ref_high, 2) ?></td>
								<td class="text-end"><?= number_format($record->price_ref_high_new, 2) ?></td>

								<td class="text-center"><?= $expired; ?></td>
								<td class="text-center"><?= $expired_new; ?></td>

								<td>
									<span class="badge bg-<?= $status_; ?>"><?= $status; ?></span>
									<?php if (!empty($status2)) { ?>
										<br><span class="badge bg-<?= $status2_; ?> mt-1"><?= $status2; ?></span>
									<?php } ?>
								</td>

								<td><?= strtoupper($record->status_reject) ?></td>

								<td class="text-end">
									<?php if ($ENABLE_MANAGE) : ?>
										<a class="btn-icon btn-icon-edit edit" href="javascript:void(0)" data-id="<?= $record->id ?>" title="Edit">
											<i class="ti ti-edit"></i>
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
	$(document).ready(function() {
		$('#example1').DataTable({
			orderCellsTop: true,
			fixedHeader: true
		});

		// ✅ Open Modal Edit
		$(document).on('click', '.edit', function() {
			const id = $(this).data('id');
			$('#head_title').text('Material Master');

			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + '/add/' + id,
				success: function(html) {
					$('#ModalView').html(html);

					// Show modal BS5
					const modalEl = document.getElementById('dialog-popup');
					const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
					modal.show();

					// re-init select2 inside modal (important)
					$('#dialog-popup .chosen-select').select2({
						width: '100%',
						dropdownParent: $('#dialog-popup')
					});

					// re-init autonumeric
					$('#dialog-popup .autoNumeric').autoNumeric('init', {
						mDec: '0',
						aPad: false
					});
					$('#dialog-popup .autoNumeric6').autoNumeric('init', {
						mDec: '6',
						aPad: false
					});
				}
			});
		});

		// ✅ Submit moved to INDEX
		$(document).on('submit', '#data_form', function(e) {
			e.preventDefault();

			const price_ref_expired = $('#price_ref_expired').val();
			if (price_ref_expired === '0') {
				swal({
					title: "Error Message!",
					text: "Expired not selected...",
					type: "warning"
				});
				return;
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

		//input idr
		$(document).on('keyup', '#price_ref_new', function() {
			var price_ref = getNum($('#price_ref_new').val().split(",").join(""));
			var kurs = getNum($('#kurs').val().split(",").join(""));
			var price = price_ref / kurs
			$('#price_ref_new_usd').val(price)
		})

		$(document).on('keyup', '#price_ref_high_new', function() {
			var price_ref = getNum($('#price_ref_high_new').val().split(",").join(""));
			var kurs = getNum($('#kurs').val().split(",").join(""));
			var price = price_ref / kurs
			$('#price_ref_high_new_usd').val(price)
		})
		//input usd
		$(document).on('keyup', '#price_ref_new_usd, #kurs', function() {
			var price_ref = getNum($('#price_ref_new_usd').val().split(",").join(""));
			var kurs = getNum($('#kurs').val().split(",").join(""));
			var price = price_ref * kurs
			$('#price_ref_new').val(price)
		})

		$(document).on('keyup', '#price_ref_high_new_usd, #kurs', function() {
			var price_ref = getNum($('#price_ref_high_new_usd').val().split(",").join(""));
			var kurs = getNum($('#kurs').val().split(",").join(""));
			var price = price_ref * kurs
			$('#price_ref_high_new').val(price)
		})
	});
</script>