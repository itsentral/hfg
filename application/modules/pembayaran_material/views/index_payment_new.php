<div class="card">
	<div class="card-header">
		<button type="button" class="btn btn-md btn-success choose_payment">Payment</button>
	</div>
	<div class="card-body">
		<!-- ✅ Tabs Bootstrap 5 -->
		<ul class="nav nav-tabs mb-3" id="paymentTab" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="tab-local" data-bs-toggle="tab" data-bs-target="#material" type="button" role="tab">
					PR
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="tab-category" data-bs-toggle="tab" data-bs-target="#non_material" type="button" role="tab">
					Non PR
				</button>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane fade show active" id="material" role="tabpanel" aria-labelledby="tab-local">
				<div class="box-body">
					<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center">No Payment</th>
								<th class="text-center">No Dokumen</th>
								<th class="text-center">Tgl Bayar</th>
								<th class="text-center">Requesto / Supplier</th>
								<th class="text-center">Nilai Bayar</th>
								<th class="text-center">Keterangan</th>
								<th class="text-center" width='110px'>Option</th>
							</tr>
						</thead>

						<tbody>
							<?php

							if (!empty($results)) {
								$no = 1;
								foreach ($results as $item) {

									$nm_supplier = $item->nm_supplier;

									echo '<tr>';
									echo '<td class="text-center">' . $item->id_payment . '</td>';
									echo '<td class="text-center">' . $item->no_doc . '</td>';
									echo '<td class="text-center">' . date('d F Y', strtotime($item->tgl_bayar)) . '</td>';
									echo '<td class="text-center">' . $nm_supplier . '</td>';
									echo '<td class="text-right">' . number_format($item->payment_bank, 2) . '</td>';
									echo '<td class="text-left">' . $item->keterangan_pembayaran . '</td>';
									echo '<td>';
									echo '<a href="' . base_url('pembayaran_material/view_payment_new/' . $item->id_payment) . '" target="_blank" class="btn btn-sm btn-info view" title="View Request Payment"><i class="fa fa-eye"></i></a>';
									if (file_exists('assets/expense/' . $item->link_doc) && $item->link_doc !== '') {
										echo '<a href="' . base_url('assets/expense/' . $item->link_doc) . '" class="btn btn-sm btn-primary" style="margin-left: 5px;"><i class="fa fa-download"></i></a>';
									}
									echo '</td>';
									echo '</tr>';

									$no++;
								}
							}

							?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="tab-pane fade" id="non_material" role="tabpanel" aria-labelledby="tab-category">
				<div class="box-body">
					<table class="table table-bordered table-striped" id="mytabledatanonmaterial" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center">No Payment</th>
								<th class="text-center">No Dokumen</th>
								<th class="text-center">Tgl Bayar</th>
								<th class="text-center">Requesto / Supplier</th>
								<th class="text-center">Nilai Bayar</th>
								<th class="text-center">Keterangan</th>
								<th class="text-center" width='110px'>Option</th>
							</tr>
						</thead>

						<tbody>
							<?php

							if (!empty($results2)) {
								$no = 1;
								foreach ($results2 as $item) {
									echo '<tr>';
									echo '<td class="text-center">' . $item->id_payment . '</td>';
									echo '<td class="text-center">' . $item->no_doc . '</td>';
									echo '<td class="text-center">' . date('d F Y', strtotime($item->tgl_bayar)) . '</td>';
									echo '<td class="text-center">' . $item->created_by . '</td>';
									echo '<td class="text-right">' . number_format($item->payment_bank, 2) . '</td>';
									echo '<td class="text-left">' . $item->keterangan_pembayaran . '</td>';
									echo '<td>';
									echo '<a href="' . base_url('pembayaran_material/view_payment_new/' . $item->id_payment) . '" target="_blank" class="btn btn-sm btn-info view" title="View Request Payment"><i class="fa fa-eye"></i></a>';
									if (file_exists('assets/expense/' . $item->link_doc) && $item->link_doc !== '') {
										echo '<a href="' . base_url('assets/expense/' . $item->link_doc) . '" class="btn btn-sm btn-primary" style="margin-left: 5px;"><i class="fa fa-download"></i></a>';
									}
									echo '</tr>';

									$no++;
								}
							}

							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel">Pilih Jenis Payment</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="MyModalBody">
				<div class="form-group">
					<label for="">Jenis Payment</label>
					<select name="jenis_payment" class="form-control jenis_payment">
						<option value="">- Jenis Payment -</option>
						<option value="1">Pembayaran PR</option>
						<option value="2">Pembayaran Non PR</option>
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success confirm_jenis_payment"><i class="fa fa-check"></i> Proses</button>
				<button type="button" class="btn btn-danger" data-bs-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Batal</button>
			</div>
		</div>
	</div>
</div>
<div id="form-data">
</div>

<script src="<?= base_url('assets/chosen_v1.8.7/chosen.jquery.min.js') ?>"></script>

<!-- page script -->
<script>
	$(document).ready(function() {
		$("#mytabledata").DataTable({
			"order": [
				[0, "asc"]
			]
		});
		$("#form-data").hide();

		$("#mytabledatanonmaterial").DataTable({
			"order": [
				[0, "asc"]
			]
		});

	});

	$(document).on('click', '.choose_payment', function() {
		$('#dialog-popup').modal('show');
	});

	$(document).on('click', '.confirm_jenis_payment', function() {
		var jenis_payment = $('.jenis_payment').val();

		if (jenis_payment == '' || jenis_payment == null) {
			swal({
				title: 'Warning !',
				text: 'Mohon pilih salah satu Jenis Payment !',
				type: 'warning'
			});
		} else {
			if (jenis_payment == 1 || jenis_payment == 2) {
				window.location.href = siteurl + active_controller + 'list_request_payment/' + jenis_payment
			} else {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		}
	});
</script>