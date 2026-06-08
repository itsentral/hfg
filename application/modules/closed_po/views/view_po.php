<?php
$ENABLE_VIEW = has_permission('Purchase_Request.View');
?>
<div class="card">
	<div class="card-body">
		<form id="data-form" method="post">
			<input type="hidden" name="no_po" value="<?= $results['get_po']->no_po ?>">
			<input type="hidden" name="no_pr" value="<?= $results['get_po']->no_pr ?>">
			<div class="col-sm-12">
				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Local / Import</label>
							</div>
							<div class="col-md-8">
								<select id="loi" name="loi" class="form-control select2" required>
									<option value="">--Pilih--</option>
									<option value="Import" <?= (isset($results['get_po']) && $results['get_po']->loi == 'Import') ? 'selected' : null ?>>Import</option>
									<option value="Lokal" <?= (isset($results['get_po']) && $results['get_po']->loi == 'Lokal') ? 'selected' : null ?>>Lokal</option>
								</select>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Department</label>
							</div>
							<div class="col-md-8">
								<input type="text" class="form-control" value="<?= $results['nm_depart'] ?>" readonly>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">NO.PO</label>
							</div>
							<div class="col-md-8">
								<input type="hidden" class="form-control" id="no_po" required name="no_po" readonly placeholder="ID PO" value="<?= $results['get_po']->no_po ?>">
								<input type="text" class="form-control" id="no_surat" required name="no_surat" readonly placeholder="No.PO" value="<?= $results['get_po']->no_surat ?>">
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">Delivery Date</label>
							</div>
							<div class="col-md-8">
								<input type="text" name="delivery_date" id="delivery_date" class="form-control" value="<?= $results['get_po']->delivery_date ?>" required>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">Tanggal PO</label>
							</div>
							<div class="col-md-8">
								<input type="text" class="form-control" id="tanggal" value="<?= $results['get_po']->tanggal ?>" onkeyup required name="tanggal">
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Currency</label>
							</div>
							<div class="col-md-8">
								<select id="select_curr" name="matauang" class='form-control select2' required>
									<option value="">- Currency -</option>
									<?php foreach ($results['mata_uang'] as $mata_uang) {
										$selected = ($results['get_po']->matauang == $mata_uang->kode) ? 'selected' : '';
									?>
										<option value="<?= $mata_uang->kode ?>" <?= $selected; ?>><?= strtoupper(strtolower($mata_uang->kode)) ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="customer">Payment Term</label>
							</div>
							<div class="col-md-8">
								<select id="term" name="term" class="form-control select2" required>
									<option value="">-- Pilih --</option>
									<?php foreach ($results['term'] as $term): ?>
										<option value="<?= htmlspecialchars($term->id) ?>" <?= ($results['get_po']->term == ($term->id)) ? 'selected' : '' ?>>
											<?= htmlspecialchars($term->name) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Supplier</label>
							</div>
							<div class="col-md-8">
								<select id="supplier" name="supplier" class='form-control select2' required>
									<option value="">- Supplier -</option>
									<?php foreach ($results['list_supplier'] as $supplier) {
										$selected = ($supplier->kode_supplier == $results['get_po']->id_suplier) ? 'selected' : '';
									?>
										<option value="<?= $supplier->kode_supplier ?>" data-address="<?= $supplier->address ?>" <?= $selected; ?>><?= strtoupper(strtolower($supplier->nama)) ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group row mb-3">
					<div class="col-sm-6">
						<div class="form-group row">
							<div class="col-md-4">
								<label for="id_customer">Keterangan</label>
							</div>
							<div class="col-md-8">
								<textarea name="keterangan" id="" class="form-control"><?= $results['get_po']->note ?></textarea>
							</div>
						</div>
					</div>
				</div>

				<div class="table-responsive mb-3">
					<div class="form-check form-switch mb-3">
						<input class="form-check-input text-end form-control" type="checkbox" id="show_tax" name="show_tax" value="Y" <?= ($results['get_po']->show_tax == 'Y') ? 'checked' : ''; ?>>
						<label class="form-check-label" for="show_tax"><b>Gunakan Pajak (PPn)</b></label>
					</div>

					<table class='table table-bordered table-striped'>
						<thead>
							<tr class='bg-blue'>
								<th style="min-width: 200px;">Item</th>
								<th style="min-width: 200px;">Nama Lain</th>
								<th style="min-width: 150px;">HS Code</th>
								<th style="min-width: 150px;">Kuota Internal</th>
								<th style="min-width: 100px;">Qty PR</th>
								<th style="min-width: 100px;">PO Qty</th>
								<th style="min-width: 100px;">Unit Measurement</th>
								<th style="min-width: 75px;">Unit Packing</th>
								<th style="min-width: 150px;">Harga Satuan</th>
								<th style="min-width: 150px;">Total Harga</th>
								<th style="min-width: 150px;">Nilai Discount</th>
								<th style="min-width: 150px;">Sub Total</th>
								<th style="min-width: 150px;">Deskripsi Item</th>
								<th style="min-width: 150px;">Sisa Kuota</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$n = 1;
							$key = 0;
							foreach ($results['getitemso'] as $value) {
								$no = $n++;
								$key++;
								$po    = $value->qty;
								$total = $value->hargasatuan * $value->qty;
								$description = (!empty($value->description)) ? $value->description : '';

								echo "
                                <tr>
                                    <td>  " . $value->nm_material . $value->nm_material1 . "</td>
                                    <td>" . $value->nm_lain . "</td>
                                    <td><input type='text' class='form-control input-sm' value='" . $value->local_code . "' readonly></td>
                                    <td><input type='text' class='form-control input-sm autoNumeric3' value='" . $value->kuota_internal . "' readonly></td>
                                    <td><input type='text' class='form-control text-center input-sm' value='" . $value->propose_purchase  . "' readonly ></td>
                                    <td><input type='text' class='form-control text-center input-sm' value='" . $value->qty . "' readonly></td>
                                    <td class='text-center'>" . ucfirst($value->unit_measure) . "</td>
                                    <td class='text-center'>" . ucfirst($value->packing_unit) . ucfirst($value->packing_unit2) . "</td>
                                    <td><input type='text' class='form-control text-right input-sm auto_num_3dec' value='" . $value->hargasatuan . "' readonly></td>
                                    <td><input type='text' class='form-control input-sm text-right auto_num' value='" . number_format($total, 4, '.', ',') . "' readonly></td>
                                    <td>
                                        <div class='input-group input-group-sm mb-2'>
                                            <input type='text' class='form-control input-sm auto_num' value='" . $value->persen_disc . "' readonly>
                                            <span class='input-group-text'>%</span>
                                        </div>
                                        <div class='input-group input-group-sm'>
                                            <input type='text' class='form-control input-sm auto_num' value='" . $value->nilai_disc . "' readonly>
                                            <span class='input-group-text'>Rp</span>
                                        </div>
                                    </td>
                                    <td><input type='text' class='form-control input-sm text-right auto_num' value='" . number_format($total - $value->nilai_disc + $value->ppn, 4, '.', ',') . "' readonly></td>
                                    <td><input type='text' class='form-control input-sm' value='" . $description . "' readonly></td>                                                                
                                    <td><input type='text' class='form-control input-sm text-end autoNumeric3' value='" . ($value->kuota_internal - $value->qty) . "' readonly></td>
                                </tr>";
							}
							?>
						</tbody>
						<tfoot>
							<tr>
								<td class="text-end" colspan="11"><b>Total</b></th>
								<td colspan="3">
									<input readonly type="text" class="form-control auto_num_4dec text-end" id="totalinppn" value="<?= number_format($results['get_po']->total_include_ppn, 4, '.', ',') ?>">
								</td>
							</tr>
							<tr class="row-pajak" style="display: <?= ($results['get_po']->show_tax == 'Y') ? 'table-row' : 'none'; ?>;">
								<td class="text-end" colspan="11"><b>DPP</b></td>
								<td colspan="3">
									<input readonly type="text" class="form-control text-end autoNumeric" id="dpp" value="<?= number_format($results['get_po']->total_include_ppn * (11 / 12), 2) ?>">
								</td>
							</tr>
							<tr class="row-pajak" style="display: <?= ($results['get_po']->show_tax == 'Y') ? 'table-row' : 'none'; ?>;">
								<td class="text-end" colspan="11"><b>PPn</b></td>
								<td colspan="3">
									<input readonly type="text" class="form-control text-end autoNumeric" id="ppn" value="<?= number_format(($results['get_po']->total_include_ppn * (11 / 12)) * 0.12, 2) ?>">
								</td>
							</tr>
							<tr>
								<td class="text-end" colspan="11"><b>Biaya Kirim</b></td>
								<td colspan="3">
									<input type="text" class="form-control auto_num text-end" id="kirim" value="<?= $results['get_po']->taxtotal ?>" readonly>
								</td>
							</tr>
							<tr>
								<td class="text-end" colspan="11"><b>Total Order</b></td>
								<td colspan="3">
									<input readonly type="text" class="form-control text-end auto_num_4dec" id="subtotal" value="<?= number_format($results['get_po']->subtotal, 4, '.', ',') ?>">
								</td>
							</tr>
						</tfoot>
					</table>
				</div>

				<hr>
				<div class="form-group row mb-3">
					<div class="col-sm-12">
						<h5>TOP Detail</h5>
						<table class="table table-bordered">
							<thead class="bg-blue">
								<tr>
									<th class="text-center">Group TOP</th>
									<th class="text-center">Progress (%)</th>
									<th class="text-center">Value</th>
									<th class="text-center">Keterangan</th>
									<th class="text-center">Tipe Pembayaran</th>
									<th class="text-center">Jatuh Tempo</th>
								</tr>
							</thead>
							<tbody class="list_tbody_top">
								<?php
								$no = 1;
								foreach ($results['list_top'] as $item_top) {
									$checked_lc = ($item_top->tipe_bayar == 'lc') ? 'checked' : '';
									$checked_tt = ($item_top->tipe_bayar == 'tt') ? 'checked' : '';
									$display_btn_lc = ($item_top->tipe_bayar == 'lc') ? '' : 'display:none;';

									echo '<tr>';
									echo '<td>';
									echo '<select name="group_top_' . $no . '" class="form-control form-control-sm" disabled>';
									foreach ($results['list_group_top'] as $item_group_top) {
										$selected = ($item_group_top->id == $item_top->group_top) ? 'selected' : '';
										echo '<option value="' . $item_group_top->id . '" ' . $selected . '>' . strtoupper($item_group_top->name) . '</option>';
									}
									echo '</select>';
									echo '</td>';

									echo '<td><input type="text" class="form-control form-control-sm auto_num" value="' . number_format($item_top->progress, 2) . '" readonly></td>';
									echo '<td class="text-right"><input type="text" class="form-control form-control-sm auto_num_4dec" value="' . number_format($item_top->nilai, 4, '.', ',') . '" readonly></td>';
									echo '<td><textarea class="form-control form-control-sm" readonly>' . $item_top->keterangan . '</textarea></td>';

									echo '<td>';
									echo '<div class="form-check"><input class="form-check-input check_bayar" type="radio" value="lc" ' . $checked_lc . ' disabled><label class="form-check-label">LC</label></div>';
									echo '<div class="form-check"><input class="form-check-input check_bayar" type="radio" value="tt" ' . $checked_tt . ' disabled><label class="form-check-label">TT</label></div>';
									echo '<button type="button" class="btn btn-sm btn-outline-primary btn_view_lc" id="btn_lc_' . $no . '" style="' . $display_btn_lc . '" data-no="' . $no . '"><i class="fas fa-eye"></i> Detail LC</button>';

									// Input Hidden data LC agar tetap bisa dilihat via JS Modal
									echo '<input type="hidden" name="no_credit_' . $no . '" value="' . $item_top->no_credit . '">';
									echo '<input type="hidden" name="issue_date_' . $no . '" value="' . $item_top->issue_date . '">';
									echo '<input type="hidden" name="expiry_date_' . $no . '" value="' . $item_top->expiry_date . '">';
									echo '<input type="hidden" name="value_contract_' . $no . '" value="' . $item_top->value_contract . '">';
									echo '<input type="hidden" name="tolerance_plus_' . $no . '" value="' . $item_top->tolerance_plus . '">';
									echo '<input type="hidden" name="tolerance_minus_' . $no . '" value="' . $item_top->tolerance_minus . '">';
									echo '<input type="hidden" name="type_of_lc_' . $no . '" value="' . $item_top->type_of_lc . '">';
									echo '<input type="hidden" name="valid_usen_until_' . $no . '" value="' . $item_top->valid_usen_until . '">';
									echo '<input type="hidden" name="bank_sender_' . $no . '" value="' . $item_top->bank_sender . '">';
									echo '<input type="hidden" name="bank_receiver_' . $no . '" value="' . $item_top->bank_receiver . '">';
									echo '<input type="hidden" name="latest_shipment_' . $no . '" value="' . $item_top->latest_shipment . '">';
									echo '<input type="hidden" name="no_sales_contract_' . $no . '" value="' . $item_top->no_sales_contract . '">';
									echo '</td>';

									echo '<td class=""><input type="date" class="form-control form-control-sm" value="' . $item_top->jatuh_tempo . '" readonly></td>';
									echo '</tr>';
									$no++;
								}
								?>
							</tbody>
						</table>
					</div>
				</div>

				<div class="text-center">
					<a href="<?= base_url('closed_po') ?>" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Kembali</a>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Modal Dialogs LC Tetap Dibiarkan agar bisa diklik lihat -->
<div class="modal fade" id="modal_lc" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
	<!-- ... (Bagian modal ini sama persis dengan kode aslimu, paste kode modal_lc kamu di sini jika diperlukan) ... -->
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-info">
				<h5 class="modal-title text-white">Detail Letter of Credit (LC)</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<!-- Semua input di modal ini dibuat readonly lewat javascript nanti -->
				<div class="row">
					<div class="col-md-6">
						<div class="form-group mb-2">
							<label>No. Credit</label>
							<input type="text" id="no_credit" class="form-control">
						</div>
						<div class="form-group mb-2">
							<label>Issue Date</label>
							<input type="date" id="issue_date" class="form-control">
						</div>
						<div class="form-group mb-2">
							<label>Expiry Date</label>
							<input type="date" id="expiry_date" class="form-control">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group mb-2">
							<label>Value Contract</label>
							<input type="text" id="value_contract" class="form-control">
						</div>
						<div class="form-group mb-2">
							<label>Bank Sender / Receiver</label>
							<div class="row">
								<div class="col-6"><input type="text" id="bank_sender" class="form-control" placeholder="Sender"></div>
								<div class="col-6"><input type="text" id="bank_receiver" class="form-control" placeholder="Receiver"></div>
							</div>
						</div>
						<div class="form-group mb-2">
							<label>No. Sales Contract</label>
							<input type="text" id="no_sales_contract" class="form-control">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Close</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';
	$(document).ready(function() {

		// Mematikan semua input karena ini hanya View
		$('.form-control').prop('readonly', true);
		$('select').prop('disabled', true);
		$('.form-check-input').prop('disabled', true);

		// Jika user klik tombol detail LC di tabel TOP
		$(document).on('click', '.btn_view_lc', function() {
			let no = $(this).data('no');
			let row = $(this).closest('tr');

			$('#no_credit').val(row.find('input[name="no_credit_' + no + '"]').val());
			$('#issue_date').val(row.find('input[name="issue_date_' + no + '"]').val());
			$('#expiry_date').val(row.find('input[name="expiry_date_' + no + '"]').val());
			$('#value_contract').val(row.find('input[name="value_contract_' + no + '"]').val());
			$('#bank_sender').val(row.find('input[name="bank_sender_' + no + '"]').val());
			$('#bank_receiver').val(row.find('input[name="bank_receiver_' + no + '"]').val());
			$('#no_sales_contract').val(row.find('input[name="no_sales_contract_' + no + '"]').val());

			$('#modal_lc').modal('show');
		});
	});
</script>