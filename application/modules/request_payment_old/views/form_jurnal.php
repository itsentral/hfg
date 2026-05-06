<?php
$ENABLE_ADD     = has_permission('Payment_Jurnal.Add');
$ENABLE_MANAGE  = has_permission('Payment_Jurnal.Manage');
$ENABLE_VIEW    = has_permission('Payment_Jurnal.View');
$ENABLE_DELETE  = has_permission('Payment_Jurnal.Delete');
?>
<?= form_open('request_payment/jurnal_save', array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal',)); ?>
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<input type="hidden" name="no_transaksi" value="<?= $no_transaksi ?>">
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>No Jurnal</th>
								<th>Tipe</th>
								<th>Tanggal</th>
								<th>COA</th>
								<th style="min-width: 300px;">Keterangan</th>
								<th>Debet</th>
								<th>Kredit</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$numb = 0;
							$ttl_debit = 0;
							$ttl_kredit = 0;
							foreach ($data as $record) {
								$numb++;
							?>
								<tr>
									<td><input type="text" class="form-control" name="no_jurnal[]" id="no_jurnal<?= $numb ?>" value="<?= $record->no_jurnal ?>" readonly></td>
									<td><input type="text" class="form-control" name="tipe[]" id="tipe<?= $numb ?>" value="<?= $record->tipe ?>" readonly></td>
									<td><input type="date" class="form-control" name="tgl_jurnal[]" id="tgl_jurnal<?= $numb ?>" value="<?= $record->tgl_jurnal ?>" readonly></td>
									<td>
										<?php
										echo form_dropdown('no_perkiraan[]', $datacoa, $record->coa, array('id' => 'no_perkiraan' . $numb, 'class' => 'form-control select2', 'required' => 'required', 'style' => 'width:100%'));
										?>
									</td>
									<td>
										<textarea name="keterangan[]" class="form-control" id="keterangan<?= $numb ?>"><?= $record->keterangan; ?></textarea>
									</td>
									<td><input type="text" class="form-control divide text-right" id="debit<?= $numb ?>" name="debit[]" value="<?= $record->debit; ?>" required></td>
									<td><input type="text" class="form-control divide text-right" id="kredit<?= $numb ?>" name="kredit[]" value="<?= $record->kredit; ?>" required></td>
									<input type="hidden" name="id[]" id="id<?= $numb ?>" value="<?= $record->id; ?>" />
								</tr>
							<?php
								$ttl_debit += $record->debit;
								$ttl_kredit += $record->kredit;
							}
							?>
						</tbody>
						<tfoot>
							<tr class="bg-gray">
								<td class="text-right" colspan="5"><b>Total</b></td>
								<td class="text-right"><?= number_format($ttl_debit) ?></td>
								<td class="text-right"><?= number_format($ttl_kredit) ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="box-footer">
				<div class="form-group">
					<div class="text-center">
						<button type="submit" name="simpan-com" class="btn btn-success btn-sm stsview" id="simpan-com"><i class="fa fa-save">&nbsp;</i>Submit</button>
						<a href="<?= base_url("request_payment/payment_jurnal_list") ?>" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
	$(".divide").divide();
	$('#simpan-com').click(function(e) {
		$("#simpan-com").addClass("hidden");
		d_error = '';
		e.preventDefault();
		if ($("#date").val() == "") {
			d_error = 'Date Error';
			alert(d_error);
		}
		if (d_error == '') {
			swal({
					title: "Save Data?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes",
					cancelButtonText: "No",
					closeOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						var formData = new FormData($('#frm_data')[0]);
						$.ajax({
							url: base_url + active_controller + "/jurnal_save",
							type: "POST",
							data: formData,
							cache: false,
							dataType: 'json',
							processData: false,
							contentType: false,
							success: function(msg) {
								if (msg['save'] == '1') {
									swal({
										title: "Success!",
										text: "Data saved",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									});
									window.location.href = base_url + active_controller + "/payment_jurnal_list";
								} else {
									swal({
										title: "Failed!",
										text: "Save Error",
										type: "error",
										timer: 1500,
										showConfirmButton: false
									});
								};
								console.log(msg);
							},
							error: function(msg) {
								$("#simpan-com").removeClass("hidden");
								swal({
									title: "Error!",
									text: "Ajax Error",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
								console.log(msg.responseText);
							}
						});
					} else {
						$("#simpan-com").removeClass("hidden");
					}
				});
		} else {
			$("#simpan-com").removeClass("hidden");
		}
	});


	<?php
	if (isset($status)) {
		if ($status == 'view') {
			echo '$("#frm_data :input").prop("disabled", true);
		$(".stsview").addClass("hidden");';
		}
	}
	?>
</script>