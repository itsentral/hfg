<div class="box-body">
	<table width="100%" border="0">
		<thead>
			<tr>
				<td class="text-left" style='vertical-align:middle;' width='15%'>No PO</td>
				<td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= $no_surat; ?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;' width='15%'>No PR</td>
				<td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= $no_pr; ?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>No Transaksi</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= $no_surat . " | " . $kode_trans; ?></td>
				<td colspan="3"></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>Tanggal Penerimaan</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?= $resv; ?></td>
				<td colspan="3"></td>
			</tr>
		</thead>
	</table><br>
	<input type="hidden" name='kode_trans' id='kode_trans' value='<?= $kode_trans; ?>'>
	<input type="hidden" name='id_header' id='id_header' value='<?= $id_header; ?>'>
	<!-- <input type="hidden" name='gudang_tujuan' id='gudang_tujuan' value='<?= $gudang_tujuan; ?>'> -->
	<input type="hidden" name='id_tujuan' id='id_tujuan' value='<?= $id_tujuan; ?>'>
	<input type="hidden" name='no_pox' id='no_pox' value='<?= $no_po; ?>'>
	<input type="hidden" name='no_surat' id='no_surat' value='<?= $no_surat; ?>'>
	<!--<input type="text" name='no_rosx' id='no_rosx' value='<?= $id_ros; ?>'>
	<input type="hidden" name='total_freight' id='total_freight' value='<?= $total_freight; ?>'> -->
	<!-- <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center" style='vertical-align:middle;' width='3%'>#</th>
				<th class="text-center" style='vertical-align:middle;'>Nama Barang</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>Qty Order</th>
				<th class="text-center" style='vertical-align:middle;' width='5%'>UoM Order</th>
				<th class="text-center" style='vertical-align:middle;' width='10%'>Qty Diterima</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>Qty Kurang</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>Qty</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>Qty NG</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>Expired Date</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>Konversi (Kg)</th>
				<th class="text-center" style='vertical-align:middle;' width='10%'>Keterangan</th>
				<th class="text-center" style='vertical-align:middle;' width='3%'>#</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$Noo = 0;
			// print_r($result);
			// exit;

			?>
		</tbody>
	</table> -->

	<?php
	$exp_no_ipp = explode(',', $no_po);
	foreach ($exp_no_ipp as $no_ipp) {
		$sql = '
				SELECT 
					a.*, 
					b.konversi,
					c.code as satuan,
					e.code as packing,
					f.no_surat,
					g.hargasatuan,
					g.harga_total
				FROM 
					tr_incoming_check_detail a 
					LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.id_material 
					LEFT JOIN ms_satuan c ON c.id = b.id_unit 
					LEFT JOIN tr_incoming_check_detail d ON d.kode_trans = a.kode_trans AND d.id_material = a.id_material
					LEFT JOIN ms_satuan e ON e.id = b.id_unit_packing 
					LEFT JOIN tr_purchase_order f ON f.no_po LIKE CONCAT("%",a.no_ipp,"%")
					LEFT JOIN dt_trans_po g ON g.id = a.id_po_detail
				WHERE 	
					a.kode_trans = "' . $kode_trans . '" AND
					g.no_po = "' . $no_ipp . '"
				GROUP BY a.id_material, a.id
				';
		$result            = $this->db->query($sql)->result_array();

		$get_no_surat = $this->db->select('no_surat')->get_where('tr_purchase_order', ['no_po' => $no_ipp])->row();

	?>
		<b>No. PO : <?= $get_no_surat->no_surat ?></b>
		<div class="table-responsive">
			<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%" style="margin-top: 1.5vh;">
				<thead>
					<tr>
						<th class="text-center">No.</th>
						<th class="text-center">No. PO</th>
						<th class="text-center">Material</th>
						<th class="text-center">Incoming</th>
						<th class="text-center">Unit</th>
						<th class="text-center">Konversi</th>
						<th class="text-center">Qty Pack</th>
						<th class="text-center">Packing</th>
						<th class="text-center" style="min-width: 100px;">Qty KW 2</th>
						<th class="text-center" style="min-width: 100px;">Qty OK</th>
						<th class="text-center" style="min-width: 100px;">Qty Pack</th>
						<th class="text-center hidden" style="min-width: 200px;">Harga Satuan</th>
						<th class="text-center hidden" style="min-width: 200px;">Total Harga</th>
						<th class="text-center">Expired Date</th>
						<th class="text-center">Document</th>
						<th class="text-center">Lot Description</th>
						<th class="text-center">#</th>
					</tr>
				</thead>
				<tbody class="list_incoming_check_<?= $no_ipp ?>">
					<?php
					$no = 1;
					$harga_baru = 0;
					foreach ($result as $item) :
						if ($item['konversi'] > 0) {
							$konversi = $item['konversi'];
							$packing = ($item['qty_order'] / $item['konversi']);
						} else {
							$konversi = 1;
							$packing = $item['qty_order'];
						}

						$harga_baru = $item['harga_total'] / $item['qty_order'];

						echo '<tr>';
						echo '
						<input type="hidden" name="id" value="' . $item['id'] . '">
						<input type="hidden" name="kode_trans_' . $item['id'] . '" value="' . $item['kode_trans'] . '">
						<input type="hidden" name="id_material_' . $item['id'] . '" value="' . $item['id_material'] . '">
						<input type="hidden" name="harga_satuan' . $item['id'] . '" value="' . $item['hargasatuan'] . '">
						<input type="hidden" name="harga_total' . $item['id'] . '" value="' . $item['harga_total'] . '">
					';
						echo '<td class="text-center">' . $no . '</td>';
						echo '<td class="text-center">' . $get_no_surat->no_surat . '</td>';
						echo '<td class="">' . $item['nm_material'] . '</td>';
						echo '<td class="text-center">' . number_format($item['qty_order'], 2) . ' <input type="hidden" class="qty_order_' . $item['id'] . '" name="qty_order_' . $item['id'] . '" value="' . $item['qty_order'] . '"> </td>';
						echo '<td class="text-center">' . $item['satuan'] . '</td>';
						echo '<td class="text-center">' . $konversi . ' <input type="hidden" name="konversi_' . $konversi . '" class="konversi_' . $item['id'] . '" value="' . $konversi . '"></td>';
						echo '<td class="text-center">' . number_format(($item['qty_order'] / $konversi), 2) . '</td>';
						echo '<td class="text-center">' . $packing . '</td>';
						echo '<td class="">
								<input type="text" name="qty_ng_' . $item['id'] . '" id="" class="form-control form-control-sm input_hid maskM qty_ng qty_ng_' . $item['id'] . '" data-id="' . $item['id'] . '" data-incoming="' . $item['qty_order'] . '" data-konversi="' . $konversi . '" required>
							</td>';
						echo '<td class="">
								<input type="text" name="qty_oke_' . $item['id'] . '" id="" class="form-control form-control-sm maskM input_hid qty_oke qty_oke_' . $item['id'] . '" data-id="' . $item['id'] . '" data-id_material="' . $item['id_material'] . '">
							</td>';
						echo '<td class="">
								<input type="text" name="qty_pack_' . $item['id'] . '" id="" class="form-control form-control-sm maskM qty_pack_' . $item['id'] . '" readonly>
							</td>';
						echo '<td class="hidden">
								<input type="text" name="harga_baru_' . $item['id'] . '" id="" class="form-control form-control-sm harga_baru_' . $item['id'] . '" value="' . number_format(($harga_baru), 2) . '" readonly>
							</td>';
						echo '<td class="hidden">
								<input type="text" name="total_harga_' . $item['id'] . '" id="" class="form-control form-control-sm total_harga total_harga_' . $item['id'] . '" readonly>
							</td>';
						echo '<td class="">
								<input type="date" name="expired_date_' . $item['id'] . '" id="" class="form-control form-control-sm input_hid expired_date_' . $item['id'] . '" min="' . date('Y-m-d') . '" data-id="' . $item['id'] . '">
							</td>';
						echo '<td>
								<input type="file" name="upload_file_' . $item['id'] . '" id="" class="form-control input_hid upload_file_' . $item['id'] . '" data-id="' . $item['id'] . '">
							</td>';
						echo '<td>
								<input type="text" name="lot_info_' . $item['id'] . '" id="" class="form-control input_hid lot_info_' . $item['id'] . '" data-id="' . $item['id'] . '">
							</td>';
						echo '<td>
								<button type="button" class="btn btn-sm btn-primary add_lot add_lot_' . $item['id'] . '" data-id="' . $item['id'] . '" data-kode_trans="' . $item['kode_trans'] . '" data-id_material="' . $item['id_material'] . '" data-no_ipp="' . $no_ipp . '"><i class="fa fa-plus"></i></button>
							</td>';
						echo '</tr>';

						$get_checked = $this->db->get_where('tr_checked_incoming_detail', ['id_detail' => $item['id']])->result_array();
						foreach ($get_checked as $checked_item) :
							$hidden = '';
							if ($checked_item['sts'] == '1') {
								$hidden = 'disabled';
							}
							echo '<tr>';
							echo '<td colspan="8"></td>';
							echo '<td><input type="text" class="form-control" name="" id="" value="' . number_format($checked_item['qty_ng'], 2) . '" readonly></td>';
							echo '<td><input type="text" class="form-control" name="" id="" value="' . number_format($checked_item['qty_oke'], 2) . '" readonly></td>';
							echo '<td><input type="text" class="form-control" name="" id="" value="' . number_format($checked_item['qty_pack'], 2) . '" readonly></td>';
							echo '<td class="text-center" style="vertical-align: middle;">' . date('d F Y', strtotime($checked_item['expired_date'])) . '</td>';
							echo '<td class="text-center"><a href="' . base_url($checked_item['uploaded_file']) . '" class="btn btn-sm btn-primary" target="_blank">Download File</a></td>';
							echo '<td>' . $checked_item['lot_description'] . '</td>';
							echo '<td class="text-center">';
							if ($checked_item['sts'] == '0') {
								echo '<button type="button" class="btn btn-sm btn-danger del_checked" data-id="' . $checked_item['id'] . '" data-kode_trans="' . $checked_item['kode_trans'] . '" data-no_ipp="' . $no_ipp . '"><i class="fa fa-trash"></i></button>';
							}
							echo '</td>';
							echo '</tr>';
						endforeach;
						$no++;
						$Noo++;
					endforeach;
					?>
				</tbody>
				<tfoot hidden>
					<tr>
						<td class="text-right" colspan="12">Total Harga</td>
						<td colspan="2"><input type="text" id="grandTotal" class="form-control form-control-sm"></td>
					</tr>
				</tfoot>
			</table>
		</div>

	<?php
	}

	?>

	<div class="row align-items-center mt-2" id="lot-helper-bar">
		<div class="col-md-8">
			<medium class="text-muted text-danger">
				<span class="text-danger">*</span>
				Lengkapi <strong>Qty OK</strong>, <strong>Expired Date</strong>, dan <strong>Lot Description</strong>, lalu klik
				tombol <span class="badge bg-blue"><i class="fa fa-plus"></i></span>
			</medium>
		</div>
	</div>

	<div class="row">
		<div class="col-md-4" style="margin-top: 1vh;">
			<div class="table-responsive">
				<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
					<thead>
						<tr>
							<th class="text-center">No.</th>
							<th class="text-center">Material</th>
							<th class="text-center">Qty Incoming</th>
							<th class="text-center">Qty KW 2</th>
							<th class="text-center">Qty OK</th>
						</tr>
					</thead>
					<tbody class="list_summary_material">
						<?php
						$no = 1;
						$stok_tidak_masuk = 0;
						$stok_masuk = 0;
						$total_nilai = 0;
						foreach ($summary_incoming as $summ) :
							$id = $summ['id'];

							echo '<tr class="summary-row" data-id="' . $id . '">';
							echo '<td class="text-center">' . $no . '</td>';
							echo '<td class="text-center">' . htmlspecialchars($summ['nm_material']) . '</td>';

							// simpan angka murni di data-val, tampilkan number_format
							echo '<td class="text-center sum-order" data-val="' . $summ['qty_order'] . '">' . number_format($summ['qty_order']) . '</td>';
							echo '<td class="text-center sum-ng"    data-val="' . $summ['ttl_qty_ng'] . '">' . number_format($summ['ttl_qty_ng']) . '</td>';
							echo '<td class="text-center sum-oke"   data-val="' . $summ['ttl_qty_oke'] . '">' . number_format($summ['ttl_qty_oke']) . '</td>';

							echo '</tr>';
							$no++;

							$stok_tidak_masuk += $summ['ttl_qty_ng'];
							$stok_masuk += $summ['ttl_qty_oke'];
							$total_nilai += $summ['total_harga'];
						endforeach;
						?>
					</tbody>
					<tbody class="list_masuk_stock">
						<tr>
							<td colspan="3" class="text-right">Masuk ke Stock</td>
							<td class="text-center">
								<span style="color: red;"><?= number_format($stok_tidak_masuk) ?></span>
							</td>
							<td class="text-center">
								<span style="color: green;"><?= number_format($stok_masuk) ?></span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="list_form">
		<?php
		$sql = '
			SELECT 
				a.*, 
				b.konversi,
				c.code as satuan,
				e.code as packing,
				f.no_surat
			FROM 
				tr_incoming_check_detail a 
				LEFT JOIN new_inventory_4 b ON b.code_lv4 = a.id_material 
				LEFT JOIN ms_satuan c ON c.id = b.id_unit 
				LEFT JOIN tr_incoming_check_detail d ON d.kode_trans = a.kode_trans AND d.id_material = a.id_material
				LEFT JOIN ms_satuan e ON e.id = b.id_unit_packing 
				LEFT JOIN tr_purchase_order f ON f.no_po LIKE CONCAT("%",a.no_ipp,"%")
				LEFT JOIN dt_trans_po g ON g.id = a.id_po_detail
			WHERE 	
				a.kode_trans = "' . $kode_trans . '"
			GROUP BY a.id_material, a.id
		';
		$result            = $this->db->query($sql)->result_array();

		$no = 1;
		foreach ($result as $item) :
			echo '<form id="form_' . $item['id'] . '" method="post" enctype="multipart/form-data">';
			echo '<input type="hidden" name="id" value="' . $item['id'] . '">';
			echo '<input type="hidden" name="kode_trans" value="' . $item['kode_trans'] . '">';
			echo '<input type="hidden" name="qty_ng" class="qty_ng_hidden_' . $item['id'] . '">';
			echo '<input type="hidden" name="qty_oke" class="qty_oke_hidden_' . $item['id'] . '">';
			echo '<input type="hidden" name="qty_pack" class="qty_pack_hidden_' . $item['id'] . '">';
			echo '<input type="hidden" name="expired_date" class="expired_date_hidden_' . $item['id'] . '">';
			echo '<input type="hidden" name="lot_info" class="lot_info_hidden_' . $item['id'] . '">';
			echo '</form>';
		endforeach;
		?>

	</div>

	<div class="row">
		<div class="col-md-3" style="margin-top: 25px;">
			<!-- <div class="form-group">
				<input type="file" name="file_incoming_material[]" id="" class="form-control form-control-sm" multiple>
			</div> -->
		</div>
	</div>

	<?php
	// echo ($file_incoming_material);
	if (file_exists($file_incoming_material)) {
		echo '<a href="' . base_url($file_incoming_material) . '" class="btn btn-sm btn-primary" style="margin-top: 2vh;" download><i class="fa fa-downloads"></i> Download File Incoming</a>';
	}
	?>

	<div class="row">
		<div class="col-md-12">
			<b>Informasi Jurnal</b>
			<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
						<tr bgcolor='#9acfea'>
							<th>
								<center>Tanggal</center>
							</th>
							<th>
								<center>Tipe</center>
							</th>
							<th>
								<center>No. COA</center>
							</th>
							<th>
								<center>Nama. COA</center>
							</th>
							<th>
								<center>Debit</center>
							</th>
							<th>
								<center>Kredit</center>
							</th>
						</tr>
					</thead>
					<tbody class="list_jurnal">
						<tr bgcolor='#DCDCDC'>
							<td><input type="date" id="tgl_jurnal1" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
							<td><input type="text" id="type1" name="type[]" value="JV" class="form-control" readonly /></td>
							<td><input type="text" id="no_coa1" name="no_coa[]" value="1104-01-01" class="form-control" readonly /></td>
							<td><input type="text" id="nama_coa1" name="nama_coa[]" value="Persediaan Barang Warehouse" class="form-control" readonly /></td>
							<td>
								<input type="hidden" id="debet1" name="debet[]" value="<?= $total_nilai ?>" class="form-control" readonly />
								<input type="text" id="debet21" name="debet2[]" value="<?= $total_nilai ?>" class="form-control" readonly />
							</td>
							<td><input type="hidden" id="kredit1" name="kredit[]" value="0" class="form-control" readonly />
								<input type="text" id="kredit21" name="kredit2[]" value="0" class="form-control" readonly />
							</td>

						</tr>
						<tr bgcolor='#DCDCDC'>
							<td><input type="date" id="tgl_jurnal2" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
							<td><input type="text" id="type2" name="type[]" value="JV" class="form-control" readonly /></td>
							<td><input type="text" id="no_coa2" name="no_coa[]" value="2101-01-02" class="form-control" readonly /></td>
							<td><input type="text" id="nama_coa2" name="nama_coa[]" value="Unbill" class="form-control" readonly /></td>
							<td><input type="hidden" id="debet2" name="debet[]" value="0" class="form-control" readonly />
								<input type="text" id="debet22" name="debet2[]" value="0" class="form-control" readonly />
							</td>
							<td><input type="hidden" id="kredit2" name="kredit[]" value="0" class="form-control" readonly />
								<input type="text" id="kredit22" name="kredit2[]" value="0" class="form-control" readonly />
							</td>

						</tr>
						<tr bgcolor='#DCDCDC'>
							<td><input type="date" id="tgl_jurnal3" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
							<td><input type="text" id="type3" name="type[]" value="JV" class="form-control" readonly /></td>
							<td><input type="text" id="no_coa3" name="no_coa[]" value="1103-01-01" class="form-control" readonly /></td>
							<td><input type="text" id="nama_coa3" name="nama_coa[]" value="Uang Muka Pembelian" class="form-control" readonly /></td>
							<td><input type="hidden" id="debet3" name="debet[]" value="0" class="form-control" readonly />
								<input type="text" id="debet23" name="debet2[]" value="0" class="form-control" readonly />
							</td>
							<td><input type="hidden" id="kredit3" name="kredit[]" value="<?= $total_nilai ?>" class="form-control" readonly />
								<input type="text" id="kredit23" name="kredit2[]" value="<?= $total_nilai ?>" class="form-control" readonly />
							</td>

						</tr>
						<tr bgcolor='#DCDCDC'>
							<td colspan="4" align="right"><b>TOTAL</b></td>
							<td align="right"><input type="hidden" id="total" name="total" value="<?= $total_nilai ?>" class="form-control" readonly />
								<input type="text" id="total31" name="total3" value="<?= $total_nilai ?>" class="form-control" readonly />
							</td>
							<td align="right"><input type="hidden" id="total2" name="total2" value="" class="form-control" readonly />
								<input type="text" id="total41" name="total4" value="<?= $total_nilai ?>" class="form-control" readonly />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<style>
	.tanggal {
		cursor: pointer;
	}
</style>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
	$(document).ready(function() {
		swal.close();
		$('.maskM').autoNumeric('init', {
			mDec: '4',
			aPad: false
		});
		$('.tanggal').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true
		});

		$('.add_lot').prop('disabled', true);
		$('.add_lot').each(function() {
			updateAddLotStateById($(this).data('id'));
		});
	});

	$(document).on('change', '.qtyDiterima', function() {
		let idNomor = $(this).data('no')
		// let belumDiterima 	= getNum($(this).parent().parent().find('#belumDiterima_'+idNomor).text().split(',').join(''))
		let belumDiterima = getNum($('#belumDiterima_' + idNomor).text().split(',').join(''))
		// let qtyDiterima 	= getNum($(this).val().split(',').join(''))

		// if(qtyDiterima > belumDiterima){
		// 	$(this).val(belumDiterima)
		// }
		let inputQty
		let sisaDiterima
		let ID
		$('.qtyDiterima').each(function() {
			ID = $(this).data('no')
			if (ID == idNomor) {
				inputQty = getNum($(this).val().split(',').join(''))
				sisaDiterima = belumDiterima - inputQty

				console.log(inputQty)
				console.log(belumDiterima)
				console.log(sisaDiterima)

				if (sisaDiterima >= 0) {
					$(this).val(inputQty)
					// console.log('kurang')
				} else {
					// console.log('lebih')
					if (belumDiterima < 0) {
						$(this).val(0)
					} else {
						$(this).val(belumDiterima)

					}
				}

				belumDiterima = sisaDiterima
			}

		})
	})

	$(document).on('change', '.qty_oke', function() {
		var id = $(this).data('id');
		var id_material = $(this).data('id_material');
		var qty_oke = $(this).val();
		if (qty_oke == '' || qty_oke == null) {
			var qty_oke = 0;
		} else {
			qty_oke = qty_oke.split(',').join('');
			qty_oke = parseFloat(qty_oke);
		}
		console.log(qty_oke)
		var konversi = $('.konversi_' + id).val();
		var harga_baru = $('.harga_baru_' + id).val();
		harga_baru = harga_baru.split(',').join('');
		harga_baru = parseFloat(harga_baru);

		var total_harga = qty_oke * harga_baru;
		console.log(total_harga)

		$('.total_harga_' + id).val(total_harga);

		var qty_pack = parseFloat(qty_oke / konversi);
		$('.qty_pack_' + id).val(qty_pack.toFixed(2));

		let TTL = 0
		$('.total_harga').each(function() {
			TTL += Number($(this).val().split(',').join(''));
		})

		$('#grandTotal').val(TTL);

		// updateAddLotStateById(id)
	});

	$(document).on('change', '.qty_ng', function() {
		var id = $(this).data('id');
		var incoming = parseFloat($(this).data('incoming'));
		var konversi = $('.konversi_' + id).val();;

		var qty_ng = $(this).val();
		if (qty_ng == '' || qty_ng == null) {
			var qty_ng = 0;
		} else {
			var qty_ng = qty_ng.split(',').join('');
			qty_ng = parseFloat(qty_ng);
		}

		var qty_oke = incoming - qty_ng
		var qty_pack = (incoming - qty_ng) / konversi

		if (incoming < qty_ng) {
			$('.qty_oke_' + id).autoNumeric('set', 0);
			$('.qty_pack_' + id).autoNumeric('set', 0);
		} else {
			$('.qty_oke_' + id).autoNumeric('set', (incoming - qty_ng));
			$('.qty_pack_' + id).autoNumeric('set', ((incoming - qty_ng) / konversi));
		}

		// updateAddLotStateById(id)
	});

	$(document).on('click', '.add_lot', function(e) {
		$(this).attr('disabled', true);
		var id = $(this).data('id');
		var no_ipp = $(this).data('no_ipp');

		var formm = new FormData($('#form_' + id)[0]);
		formm.append('upload_file', $('.upload_file_' + id)[0].files[0])
		$.ajax({
			url: siteurl + active_controller + '/add_lot',
			type: "POST",
			data: formm,
			cache: true,
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function(result) {
				if (result.hasil == '1') {
					refresh_incoming_check(result.kode_trans, no_ipp);
				} else {
					swal({
						title: 'Error !',
						text: 'Please, try again !',
						type: 'error'
					});
				}
			},
			error: function(result) {
				swal({
					title: 'Error',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});

		$(this).attr('disabled', true);
	});

	$(document).on('change', '.input_hid', function() {
		var id = $(this).data('id');

		var qty_ng = $('.qty_ng_' + id).val();
		if (qty_ng == '' || qty_ng == null) {
			var qty_ng = 0;
		} else {
			qty_ng = qty_ng.split(',').join('');
			qty_ng = parseFloat(qty_ng);
		}

		var qty_oke = $('.qty_oke_' + id).val();
		if (qty_oke == '' || qty_oke == null) {
			var qty_oke = 0;
		} else {
			qty_oke = qty_oke.split(',').join('');
			qty_oke = parseFloat(qty_oke);
		}

		var qty_pack = $('.qty_pack_' + id).val();
		if (qty_pack == '' || qty_pack == null) {
			var qty_pack = 0;
		} else {
			qty_pack = qty_pack.split(',').join('');
			qty_pack = parseFloat(qty_pack);
		}

		var expired_date = $('.expired_date_' + id).val();
		var upload_file = $('.upload_file_' + id).val();
		var lot_info = $('.lot_info_' + id).val();

		$('.qty_ng_hidden_' + id).val(qty_ng);
		$('.qty_oke_hidden_' + id).val(qty_oke);
		$('.qty_pack_hidden_' + id).val(qty_pack);
		$('.expired_date_hidden_' + id).val(expired_date);
		$('.upload_file_hidden_' + id).val(upload_file);
		$('.lot_info_hidden_' + id).val(lot_info);
	});

	$(document).on('click', '.del_checked', function() {
		var id = $(this).data('id');
		var kode_trans = $(this).data('kode_trans');
		var no_ipp = $(this).data('no_ipp');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'del_checked_incoming',
			data: {
				'id': id,
				'kode_trans': kode_trans,
				'no_ipp': no_ipp
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				if (result.hasil == '1') {
					refresh_incoming_check(kode_trans, no_ipp);
				} else {
					swal({
						title: 'Error !',
						text: 'Please, try again !',
						type: 'error'
					});
				}
			}
		});


	});

	function refresh_incoming_check(kode_trans, no_ipp) {
		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'refresh_incoming_check',
			data: {
				'kode_trans': kode_trans,
				'no_ipp': no_ipp
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.list_incoming_check_' + no_ipp).html(result.hasil);
				$('.list_summary_material').html(result.hasil2);
				$('.list_masuk_stock').html(result.hasil3);
				$('.list_jurnal').html(result.hasil4);
				$('.maskM').autoNumeric('init', {
					mDec: '4',
					aPad: false
				});

				$('#ModalView2 .list_summary_material').html(result.hasil2);
				updateModalSaveStateIn('#ModalView2');

				$('.add_lot').each(function() {
					var id = $(this).data('id');
					updateAddLotStateById(id);
				});
			}
		});
	}

	function num(v) {
		v = (v == null ? '0' : String(v));
		return parseFloat(v.split(',').join('')) || 0;
	}

	function updateAddLotStateById(id) {
		const $btn = $('.add_lot_' + id);

		// baris ringkasan yang cocok
		const $sum = $('.summary-row[data-id="' + id + '"]');
		if ($sum.length === 0) {
			console.warn('Summary row not found for id', id);
			$btn.prop('disabled', false);
			return;
		}

		// ambil angka murni dari data-val (tidak terpengaruh pemisah ribuan)
		const qty_order = num($sum.find('.sum-order').data('val'));
		const qty_oke = num($sum.find('.sum-oke').data('val'));
		const qty_ng = num($sum.find('.sum-ng').data('val'));
		const done = (qty_oke + qty_ng) >= qty_order && qty_order > 0;
		$btn.prop('disabled', done);
	}
</script>