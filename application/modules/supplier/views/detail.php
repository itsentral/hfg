<?php
$id_product        = (!empty($header[0]->id_product)) ? $header[0]->id_product : '0';
$variant_product   = (!empty($header[0]->variant_product)) ? $header[0]->variant_product : '0';
$nm_product        = (!empty($GET_LEVEL4[$id_product]['nama'])) ? $GET_LEVEL4[$id_product]['nama'] : '';
?>

<div class="card shadow-sm border-0">
	<div class="card-body">

		<!-- =====================
		     HEADER INFO PRODUCT
		===================== -->
		<div class="row mb-3">
			<div class="col-md-6">
				<div class="mb-2">
					<span class="text-muted">Product Name</span>
					<div class="fw-semibold"><?= $nm_product; ?></div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="mb-2">
					<span class="text-muted">Variant Product</span>
					<div class="fw-semibold"><?= $variant_product; ?></div>
				</div>
			</div>
		</div>

		<hr>

		<!-- =====================
		     DETAIL MATERIAL TABLE
		===================== -->
		<div class="table-responsive">
			<table class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th style="width:60px;">#</th>
						<th>Material Type</th>
						<th>Material Category</th>
						<th>Material Jenis</th>
						<th>Material Name</th>
						<th class="text-end" style="width:140px;">Berat (Kg)</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$SUM = 0;
					foreach ($detail as $i => $valx) {
						$no = $i + 1;

						$nm_material = (!empty($GET_LEVEL4[$valx['code_material']]['nama']))
							? $GET_LEVEL4[$valx['code_material']]['nama']
							: '-';

						$code_lv1 = (!empty($GET_LEVEL4[$valx['code_material']]['code_lv1']))
							? $GET_LEVEL4[$valx['code_material']]['code_lv1']
							: '-';

						$code_lv2 = (!empty($GET_LEVEL4[$valx['code_material']]['code_lv2']))
							? $GET_LEVEL4[$valx['code_material']]['code_lv2']
							: '-';

						$code_lv3 = (!empty($GET_LEVEL4[$valx['code_material']]['code_lv3']))
							? $GET_LEVEL4[$valx['code_material']]['code_lv3']
							: '-';

						$nm_category = strtolower(get_name('new_inventory_2', 'nama', 'code_lv2', $code_lv2));

						$SUM += $valx['weight'];
					?>
						<tr>
							<td><?= $no; ?></td>
							<td><?= strtoupper(get_name('new_inventory_1', 'nama', 'code_lv1', $code_lv1)); ?></td>
							<td><?= strtoupper($nm_category); ?></td>
							<td><?= strtoupper(get_name('new_inventory_3', 'nama', 'code_lv3', $code_lv3)); ?></td>
							<td class="fw-semibold"><?= strtoupper($nm_material); ?></td>
							<td class="text-end"><?= number_format($valx['weight'], 4); ?></td>
						</tr>
					<?php } ?>

					<!-- TOTAL -->
					<tr class="table-light">
						<td></td>
						<td colspan="4" class="fw-semibold">Total Berat</td>
						<td class="text-end fw-bold"><?= number_format($SUM, 4); ?> Kg</td>
					</tr>

				</tbody>
			</table>
		</div>

	</div>
</div>