<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>Purchase Request</title>
	<style type="text/css">
		body {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 10px;
			color: #333333;
			margin: 0;
			padding: 0;
		}

		table {
			border-collapse: collapse;
		}

		table.gridtable {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 10px;
			color: #333333;
			width: 100%;
			border: 1px solid #999999;
			border-collapse: collapse;
		}

		table.gridtable th {
			padding: 6px;
			background-color: #2c3e50;
			color: #ffffff;
			border: 1px solid #999999;
			font-weight: bold;
		}

		table.gridtable td {
			padding: 5px 6px;
			border: 1px solid #cccccc;
		}

		.text-center {
			text-align: center;
		}

		.text-right {
			text-align: right;
		}

		.text-left {
			text-align: left;
		}
	</style>
</head>

<body>
	<?php
	if (!function_exists('html_escape')) {
		function html_escape($var, $double_encode = TRUE)
		{
			if (empty($var)) {
				return '';
			}
			if (is_array($var)) {
				return array_map('html_escape', $var);
			}
			return htmlspecialchars((string) $var, ENT_QUOTES, 'UTF-8', $double_encode);
		}
	}

	$hd = (is_array($header) && !empty($header)) ? $header[0] : (is_object($header) ? $header : new stdClass());

	// Nama User login / Pembuat PR
	$auth_user_name = '';
	if (isset($this) && isset($this->auth) && method_exists($this->auth, 'user_name')) {
		$auth_user_name = (string) $this->auth->user_name();
	} elseif (function_exists('get_instance')) {
		$ci = &get_instance();
		if (isset($ci->auth) && method_exists($ci->auth, 'user_name')) {
			$auth_user_name = (string) $ci->auth->user_name();
		}
	}
	if ($auth_user_name === '') {
		$auth_user_name = 'Purchasing';
	}

	// Penentuan Logo Perusahaan (FCPATH untuk performa mPDF langsung via filesystem)
	$logo_file = FCPATH . 'assets/images/logohsj.png';
	if (!file_exists($logo_file)) {
		$logo_file = FCPATH . 'assets/images/iconhsj.png';
	}

	$company_name = function_exists('get_company_name') ? get_company_name(true, true) : 'PT HARAPAN SUKSES JAYA';
	$company_address = function_exists('get_company_address') ? get_company_address() : '';

	// Penanganan aman mata uang (mencegah Undefined property $header->matauang)
	$matauang = !empty($hd->matauang) ? strtoupper(trim((string) $hd->matauang)) : 'IDR';
	$kode_curr = ($matauang === 'USD') ? '$' : 'Rp';
	?>

	<!-- Header Perusahaan & Logo -->
	<table style="width: 100%; border: none; margin-bottom: 5px;">
		<tr>
			<td style="width: 45%; vertical-align: top; border: none; padding: 0;">
				<img src="<?= $logo_file; ?>" alt="Logo" style="height: 48px; max-width: 220px;">
			</td>
			<td style="width: 55%; vertical-align: top; text-align: right; font-size: 10px; line-height: 1.35; color: #333; border: none; padding: 0;">
				<strong style="font-size: 12px; color: #000;"><?= html_escape($company_name); ?></strong><br>
				<?= function_exists('get_company_address_html') ? get_company_address_html() : nl2br(html_escape($company_address)); ?>
			</td>
		</tr>
	</table>

	<div style="border-top: 2px solid #2c3e50; margin: 4px 0 8px 0;"></div>

	<div style="text-align: center; background-color: #2c3e50; color: #ffffff; padding: 6px; font-weight: bold; font-size: 13px; letter-spacing: 1px;">
		PURCHASE REQUEST
	</div>

	<!-- Informasi Dokumen -->
	<table style="width: 100%; margin-top: 8px; font-size: 10px; border-collapse: collapse;">
		<tr>
			<!-- Info Customer / Vendor -->
			<td style="width: 49%; vertical-align: top; border: 1px solid #cccccc; padding: 6px; background-color: #fafafa;">
				<table style="width: 100%; font-size: 10px; border: none;">
					<tr>
						<td style="width: 70px; font-weight: bold; border: none; padding: 2px 0;">Customer</td>
						<td style="width: 8px; border: none; padding: 2px 0;">:</td>
						<td style="border: none; padding: 2px 0;"><?= !empty($hd->nm_customer) ? html_escape($hd->nm_customer) : '-' ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold; border: none; padding: 2px 0;">Address</td>
						<td style="border: none; padding: 2px 0;">:</td>
						<td style="border: none; padding: 2px 0;"><?= !empty($hd->alamat) ? html_escape($hd->alamat) : '-' ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold; border: none; padding: 2px 0;">Country</td>
						<td style="border: none; padding: 2px 0;">:</td>
						<td style="border: none; padding: 2px 0;"><?= !empty($hd->country_name) ? html_escape($hd->country_name) : 'Indonesia' ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold; border: none; padding: 2px 0;">PIC</td>
						<td style="border: none; padding: 2px 0;">:</td>
						<td style="border: none; padding: 2px 0;"><?= !empty($hd->nm_pic) ? html_escape($hd->nm_pic) : html_escape($auth_user_name) ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold; border: none; padding: 2px 0;">Phone / Fax</td>
						<td style="border: none; padding: 2px 0;">:</td>
						<td style="border: none; padding: 2px 0;">
							<?= !empty($hd->hp) ? html_escape($hd->hp) : '-' ?>
							<?= !empty($hd->fax) ? ' / ' . html_escape($hd->fax) : '' ?>
						</td>
					</tr>
				</table>
			</td>
			<td style="width: 2%;"></td>
			<!-- Info PR -->
			<td style="width: 49%; vertical-align: top; border: 1px solid #cccccc; padding: 6px; background-color: #fafafa;">
				<table style="width: 100%; font-size: 10px; border: none;">
					<tr>
						<td style="width: 85px; font-weight: bold; border: none; padding: 2px 0;">No. PR</td>
						<td style="width: 8px; border: none; padding: 2px 0;">:</td>
						<td style="border: none; padding: 2px 0; font-weight: bold; color: #1a5276;"><?= !empty($hd->no_pr) ? html_escape($hd->no_pr) : html_escape($hd->so_number ?? '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold; border: none; padding: 2px 0;">Date</td>
						<td style="border: none; padding: 2px 0;">:</td>
						<td style="border: none; padding: 2px 0;"><?= !empty($hd->created_date) ? date('d-M-Y', strtotime((string)$hd->created_date)) : (!empty($hd->tgl_so) ? date('d-M-Y', strtotime((string)$hd->tgl_so)) : '-') ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold; border: none; padding: 2px 0;">Needed Date</td>
						<td style="border: none; padding: 2px 0;">:</td>
						<td style="border: none; padding: 2px 0;"><?= !empty($hd->tgl_dibutuhkan) ? date('d-M-Y', strtotime((string)$hd->tgl_dibutuhkan)) : '-' ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold; border: none; padding: 2px 0;">Revision</td>
						<td style="border: none; padding: 2px 0;">:</td>
						<td style="border: none; padding: 2px 0;"><?= isset($hd->no_rev) ? html_escape($hd->no_rev) : '0' ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold; border: none; padding: 2px 0;">Project / Category</td>
						<td style="border: none; padding: 2px 0;">:</td>
						<td style="border: none; padding: 2px 0;"><?= !empty($hd->project) ? html_escape($hd->project) : (!empty($hd->category) ? strtoupper(html_escape($hd->category)) : '-') ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<!-- Tabel Item Material PR -->
	<table class="gridtable" style="margin-top: 12px;">
		<thead>
			<tr>
				<th style="width: 5%; text-align: center;">No.</th>
				<th style="width: 17%; text-align: center;">Item Code</th>
				<th style="width: 36%; text-align: left;">Description</th>
				<th style="width: 10%; text-align: right;">Qty (Pack)</th>
				<th style="width: 11%; text-align: center;">Unit Packing</th>
				<th style="width: 10%; text-align: right;">Qty</th>
				<th style="width: 11%; text-align: center;">Unit Measurement</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (!empty($detail) && is_array($detail)) :
				$no = 0;
				foreach ($detail as $row) :
					$no++;
					$item_code = !empty($row->code) ? $row->code : (!empty($row->id_material) ? $row->id_material : '-');
					$item_name = !empty($row->nama) ? $row->nama : (!empty($row->stock_name) ? $row->stock_name : '-');

					$qty = isset($row->propose_purchase) ? (float) $row->propose_purchase : 0;
					$konversi = (!empty($row->konversi) && (float) $row->konversi > 0) ? (float) $row->konversi : 1;
					$kategori_pr = isset($row->kategori_pr) ? strtolower(trim((string) $row->kategori_pr)) : (isset($hd->category) ? strtolower(trim((string) $hd->category)) : '');

					if ($kategori_pr === 'pr stok') {
						$qty_pack = $qty;
						$qty_total = $qty * $konversi;
					} else {
						$qty_total = $qty;
						$qty_pack = ($konversi > 0) ? ($qty / $konversi) : $qty;
					}

					$unit_packing = !empty($row->satuan_packing) ? strtoupper(trim((string) $row->satuan_packing)) : '-';
					$unit_meas    = !empty($row->satuan) ? strtoupper(trim((string) $row->satuan)) : '-';
			?>
					<tr>
						<td class="text-center"><?= $no ?></td>
						<td class="text-center" style="font-weight: bold;"><?= html_escape($item_code) ?></td>
						<td class="text-left"><?= html_escape($item_name) ?></td>
						<td class="text-right"><?= number_format($qty_pack, 2) ?></td>
						<td class="text-center"><?= html_escape($unit_packing) ?></td>
						<td class="text-right"><?= number_format($qty_total, 2) ?></td>
						<td class="text-center"><?= html_escape($unit_meas) ?></td>
					</tr>
				<?php
				endforeach;
			else :
				?>
				<tr>
					<td colspan="7" class="text-center" style="padding: 12px; color: #888;">Tidak ada data item material</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<!-- Blok Tanda Tangan Persetujuan -->
	<table style="width: 100%; margin-top: 25px; border-collapse: collapse; text-align: center; font-size: 10px;">
		<tr>
			<td style="width: 30%; border: 1px solid #999999; vertical-align: top;">
				<div style="background-color: #f2f2f2; padding: 5px; font-weight: bold; border-bottom: 1px solid #999999;">Dibuat Oleh:</div>
				<div style="height: 55px;"></div>
				<div style="padding: 5px; border-top: 1px dotted #cccccc;">
					( <?= html_escape($auth_user_name) ?> )
				</div>
			</td>
			<td style="width: 5%;"></td>
			<td style="width: 30%; border: 1px solid #999999; vertical-align: top;">
				<div style="background-color: #f2f2f2; padding: 5px; font-weight: bold; border-bottom: 1px solid #999999;">Diperiksa Oleh:</div>
				<div style="height: 55px;"></div>
				<div style="padding: 5px; border-top: 1px dotted #cccccc;">
					( Cost Control )
				</div>
			</td>
			<td style="width: 5%;"></td>
			<td style="width: 30%; border: 1px solid #999999; vertical-align: top;">
				<div style="background-color: #f2f2f2; padding: 5px; font-weight: bold; border-bottom: 1px solid #999999;">Disetujui Oleh:</div>
				<div style="height: 55px;"></div>
				<div style="padding: 5px; border-top: 1px dotted #cccccc;">
					( Dept Head )
				</div>
			</td>
		</tr>
	</table>

</body>

</html>
