<?php
// Hitung total expense dan kasbon
$total_expense = 0;
$total_kasbon  = 0;
if (!empty($data_detail)) {
	foreach ($data_detail as $record) {
		$total_expense += (float)$record->expense;
		$total_kasbon  += (float)$record->kasbon;
	}
}

$selisih = $total_expense - $total_kasbon;
$terbilang_nominal = function_exists('ynz_terbilang_format') ? ucwords(trim(ynz_terbilang_format((float)$total_expense))) . ' Rupiah' : '';

// Data pemohon / pelapor
$created_by_user = isset($data->created_by) ? $data->created_by : (isset($data->nama) ? $data->nama : '');
$pelapor_name = !empty($nmuser) ? $nmuser : (!empty($data->nama) ? $data->nama : $created_by_user);
if (!empty($created_by_user)) {
	$pelapor_row = $this->db->query("SELECT b.nm_karyawan as name FROM users a LEFT JOIN employee b ON a.employee_id=b.id WHERE a.username='" . $created_by_user . "'")->row();
	if (!empty($pelapor_row) && !empty($pelapor_row->name)) {
		$pelapor_name = $pelapor_row->name;
	}
}

// Mengetahui
$approval_user = isset($data->approval) ? $data->approval : '';
$mengetahui_name = !empty($approval_user) ? $approval_user : '';
if (!empty($approval_user)) {
	$mengetahui_row = $this->db->query("SELECT b.nm_karyawan as name FROM users a LEFT JOIN employee b ON a.employee_id=b.id WHERE a.username='" . $approval_user . "'")->row();
	if (!empty($mengetahui_row) && !empty($mengetahui_row->name)) {
		$mengetahui_name = $mengetahui_row->name;
	}
}

// Approved by (Management/Finance)
$approved_by_name = isset($data->approved_by) && !empty($data->approved_by) ? $data->approved_by : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Expense Report - <?= $data->no_doc ?></title>
	<style>
		@page {
			size: A4 portrait;
			margin: 12mm 15mm;
		}

		* {
			box-sizing: border-box;
		}

		body {
			font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
			font-size: 10pt;
			color: #111;
			background-color: #f5f5f5;
			margin: 0;
			padding: 20px 0;
		}

		.print-container {
			max-width: 210mm;
			margin: 0 auto;
			background: #fff;
			padding: 20mm;
			box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
		}

		/* No-print toolbar */
		.no-print-toolbar {
			max-width: 210mm;
			margin: 0 auto 15px auto;
			display: flex;
			justify-content: space-between;
			align-items: center;
			background: #fff;
			padding: 12px 20px;
			border-radius: 6px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
		}

		.btn {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 8px 16px;
			font-size: 13px;
			font-weight: 600;
			border-radius: 4px;
			cursor: pointer;
			text-decoration: none;
			border: 1px solid transparent;
		}

		.btn-primary {
			background: #0d6efd;
			color: #fff;
			border-color: #0d6efd;
		}

		.btn-primary:hover {
			background: #0b5ed7;
		}

		.btn-secondary {
			background: #6c757d;
			color: #fff;
			border-color: #6c757d;
		}

		.btn-secondary:hover {
			background: #5c636a;
		}

		/* Header Section */
		.header-table {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 12px;
		}

		.header-table td {
			vertical-align: middle;
			padding: 0;
		}

		.company-logo {
			width: 110px;
		}

		.company-logo img {
			max-width: 100px;
			height: auto;
		}

		.company-info h2 {
			margin: 0 0 4px 0;
			font-size: 15pt;
			font-weight: bold;
			color: #1a1a1a;
			letter-spacing: 0.5px;
		}

		.company-info p {
			margin: 0;
			font-size: 8.5pt;
			color: #444;
			line-height: 1.4;
		}

		.doc-title-box {
			text-align: right;
			vertical-align: top !important;
		}

		.doc-title-box h3 {
			margin: 0 0 6px 0;
			font-size: 14pt;
			font-weight: bold;
			color: #0d6efd;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.doc-badge {
			display: inline-block;
			padding: 4px 10px;
			font-size: 9.5pt;
			font-weight: bold;
			background: #eef4fe;
			color: #0d6efd;
			border: 1px solid #b6d4fe;
			border-radius: 4px;
		}

		.divider-line {
			border: none;
			border-top: 2px solid #222;
			margin: 10px 0 15px 0;
		}

		/* Metadata Grid */
		.info-table {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 15px;
			font-size: 9.5pt;
		}

		.info-table td {
			padding: 4px 6px;
			vertical-align: top;
		}

		.info-label {
			width: 16%;
			color: #555;
			font-weight: 600;
		}

		.info-separator {
			width: 2%;
			text-align: center;
		}

		.info-value {
			width: 32%;
			color: #111;
			font-weight: 500;
		}

		/* Table Items */
		.items-table {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 15px;
			font-size: 9pt;
		}

		.items-table th,
		.items-table td {
			border: 1px solid #bbb;
			padding: 6px 8px;
		}

		.items-table th {
			background-color: #f1f3f5;
			font-weight: bold;
			text-align: center;
			color: #222;
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

		/* Box Calculation Summary */
		.summary-box {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 15px;
		}

		.summary-box td {
			padding: 4px 8px;
			font-size: 9.5pt;
		}

		.nominal-card {
			border: 1.5px solid #0d6efd;
			background: #f8faff;
			border-radius: 6px;
			padding: 10px 14px;
			margin-bottom: 15px;
		}

		.nominal-card-amount {
			font-size: 15pt;
			font-weight: bold;
			color: #0d6efd;
			margin-bottom: 3px;
		}

		.nominal-card-terbilang {
			font-size: 9pt;
			font-style: italic;
			color: #333;
		}

		/* Signature Section */
		.signature-table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 20px;
			margin-bottom: 15px;
			page-break-inside: avoid;
		}

		.signature-table td {
			width: 25%;
			border: 1px solid #bbb;
			padding: 6px;
			text-align: center;
			vertical-align: top;
		}

		.signature-header {
			font-size: 9pt;
			font-weight: bold;
			color: #333;
			background-color: #f8f9fa;
			padding: 6px !important;
		}

		.signature-space {
			height: 60px;
		}

		.signature-name {
			font-size: 9pt;
			font-weight: bold;
			border-top: 1px dotted #888;
			padding-top: 4px;
			margin: 0 8px;
		}

		.signature-date {
			font-size: 8pt;
			color: #666;
			margin-top: 2px;
		}

		.pagebreak {
			page-break-before: always;
		}

		.attachment-img-page {
			text-align: center;
			margin-top: 20px;
		}

		.attachment-img-page img {
			max-width: 95%;
			max-height: 800px;
			border: 1px solid #ddd;
			border-radius: 4px;
		}

		/* Print Media Query */
		@media print {
			body {
				background-color: #fff;
				padding: 0;
			}

			.no-print,
			.no-print-toolbar {
				display: none !important;
			}

			.print-container {
				padding: 0;
				margin: 0;
				box-shadow: none;
				max-width: 100%;
			}

			.nominal-card {
				background: #fff !important;
				border: 1.5px solid #000 !important;
			}

			.nominal-card-amount {
				color: #000 !important;
			}

			.doc-title-box h3 {
				color: #000 !important;
			}

			.doc-badge {
				background: none !important;
				border: 1px solid #000 !important;
				color: #000 !important;
			}

			.items-table th,
			.signature-header {
				background-color: #e9ecef !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}
		}
	</style>
</head>

<body>
	<!-- Toolbar Aksi di Atas (Hanya tampil di browser) -->
	<div class="no-print-toolbar">
		<div>
			<strong>Laporan Expense:</strong> <?= $data->no_doc ?>
		</div>
		<div style="display: flex; gap: 10px;">
			<button type="button" class="btn btn-primary" onclick="window.print()">
				<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
					<path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
					<path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
				</svg>
				Cetak Dokumen
			</button>
			<button type="button" class="btn btn-secondary" onclick="window.close()">
				Tutup
			</button>
		</div>
	</div>

	<div class="print-container">
		<!-- Header Perusahaan -->
		<table class="header-table">
			<tr>
				<td class="company-logo">
					<img src="<?= base_url('assets/images/iconhfg.png') ?>" alt="Logo Perusahaan" style="width: 60px; height: 60px; object-fit: contain;">
				</td>
				<td class="company-info" style="padding-left: 12px;">
					<h2>PT HARAPAN FAJAR GEMILANG</h2>
					<p>
						Jl. Daan Mogot Km. 11 No. 45 Kedaung Kali Angke<br>
						Cengkareng, Jakarta Barat 11710<br>
						Indonesia
					</p>
				</td>
				<td class="doc-title-box">
					<h3>EXPENSE REPORT</h3>
					<div class="doc-badge">No. <?= $data->no_doc ?></div>
				</td>
			</tr>
		</table>

		<hr class="divider-line">

		<!-- Info Pengajuan -->
		<table class="info-table">
			<tr>
				<td class="info-label">Tanggal Dokumen</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= date('d F Y', strtotime($data->tgl_doc)) ?></td>

				<td class="info-label">Bank Penerima</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= !empty($data->bank_id) ? $data->bank_id : '-' ?></td>
			</tr>
			<tr>
				<td class="info-label">Nama Pelapor</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= $pelapor_name ?></td>

				<td class="info-label">No. Rekening</td>
				<td class="info-separator">:</td>
				<td class="info-value font-monospace"><strong><?= !empty($data->accnumber) ? $data->accnumber : '-' ?></strong></td>
			</tr>
			<tr>
				<td class="info-label">Departemen</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= !empty($nm_dept) ? $nm_dept : '-' ?></td>

				<td class="info-label">Atas Nama Rekening</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= !empty($data->accname) ? $data->accname : '-' ?></td>
			</tr>
			<tr>
				<td class="info-label">Keterangan</td>
				<td class="info-separator">:</td>
				<td class="info-value" colspan="4"><strong><?= nl2br(htmlspecialchars($data->informasi ?: '-')) ?></strong></td>
			</tr>
		</table>

		<!-- Tabel Rincian Biaya -->
		<table class="items-table">
			<thead>
				<tr>
					<th style="width: 30px;">No</th>
					<th style="width: 85px;">Tanggal</th>
					<th style="width: 140px;">Akun COA</th>
					<th>Barang / Jasa</th>
					<th>Spesifikasi</th>
					<th style="width: 50px;">Qty</th>
					<th style="width: 105px;">Harga Satuan</th>
					<th style="width: 110px;">Total (Rp)</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$no = 1;
				if (!empty($data_detail)) :
					foreach ($data_detail as $record) :
						$is_kasbon = ($record->id_kasbon != '');
						$row_total = ($is_kasbon) ? $record->kasbon : $record->expense;
				?>
						<tr>
							<td class="text-center"><?= $no++ ?></td>
							<td class="text-center"><?= date('d/m/Y', strtotime($record->tanggal)) ?></td>
							<td><?= htmlspecialchars($record->coa ?: ($is_kasbon ? 'Kasbon' : '-')) ?></td>
							<td>
								<?= htmlspecialchars($record->deskripsi) ?>
								<?php if ($is_kasbon) : ?>
									<br><small style="color:#0d6efd;">(Kasbon: <?= $record->id_kasbon ?>)</small>
								<?php endif; ?>
							</td>
							<td><?= htmlspecialchars($record->keterangan ?: '-') ?></td>
							<td class="text-center"><?= number_format($record->qty) ?></td>
							<td class="text-right"><?= number_format($record->harga, 0, ',', '.') ?></td>
							<td class="text-right fw-bold"><strong><?= number_format($row_total, 0, ',', '.') ?></strong></td>
						</tr>
					<?php
					endforeach;
				else :
					?>
					<tr>
						<td colspan="8" class="text-center">Tidak ada rincian data expense.</td>
					</tr>
				<?php endif; ?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="7" class="text-right">TOTAL EXPENSE</th>
					<th class="text-right">Rp <?= number_format($total_expense, 0, ',', '.') ?></th>
				</tr>
				<?php if ($total_kasbon > 0) : ?>
					<tr>
						<th colspan="7" class="text-right">TOTAL KASBON DITERIMA</th>
						<th class="text-right">Rp <?= number_format($total_kasbon, 0, ',', '.') ?></th>
					</tr>
					<tr>
						<th colspan="7" class="text-right">SELISIH (<?= ($selisih >= 0) ? 'KURANG BAYAR' : 'LEBIH BAYAR' ?>)</th>
						<th class="text-right">Rp <?= number_format(abs($selisih), 0, ',', '.') ?></th>
					</tr>
				<?php endif; ?>
			</tfoot>
		</table>

		<!-- Box Nominal Total -->
		<div class="nominal-card">
			<div style="font-size: 8.5pt; font-weight: bold; color: #555; text-transform: uppercase;">
				Total Biaya yang Dipertanggungjawabkan :
			</div>
			<div class="nominal-card-amount">
				Rp <?= number_format($total_expense, 0, ',', '.') ?>
			</div>
			<?php if (!empty($terbilang_nominal)) : ?>
				<div class="nominal-card-terbilang">
					Terbilang: # <?= $terbilang_nominal ?> #
				</div>
			<?php endif; ?>
		</div>

		<!-- Kolom Tanda Tangan -->
		<table class="signature-table">
			<tr>
				<td class="signature-header">Diajukan Oleh (Pelapor),</td>
				<td class="signature-header">Mengetahui (Atasan),</td>
				<td class="signature-header">Diperiksa (Finance),</td>
				<td class="signature-header">Disetujui (Management),</td>
			</tr>
			<tr>
				<td class="signature-space"></td>
				<td class="signature-space"></td>
				<td class="signature-space"></td>
				<td class="signature-space"></td>
			</tr>
			<tr>
				<td>
					<div class="signature-name"><?= $pelapor_name ?></div>
					<div class="signature-date">Tgl: <?= !empty($data->created_on) ? date('d/m/Y', strtotime($data->created_on)) : date('d/m/Y') ?></div>
				</td>
				<td>
					<div class="signature-name"><?= !empty($mengetahui_name) ? $mengetahui_name : 'Atasan Langsung' ?></div>
					<div class="signature-date">Tgl: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
				</td>
				<td>
					<div class="signature-name">Finance Department</div>
					<div class="signature-date">Tgl: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
				</td>
				<td>
					<div class="signature-name"><?= !empty($approved_by_name) ? $approved_by_name : 'Direksi / Management' ?></div>
					<div class="signature-date">
						Tgl: <?= !empty($data->approved_on) ? date('d/m/Y', strtotime($data->approved_on)) : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ?>
					</div>
				</td>
			</tr>
		</table>
	</div>

	<!-- Preview Bukti Dokumen (Gambar) pada Halaman Berikutnya -->
	<?php
	$all_print_images = [];
	if (!empty($data->bon_bukti)) {
		$ext = strtolower(pathinfo($data->bon_bukti, PATHINFO_EXTENSION));
		if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
			$all_print_images[] = base_url($data->bon_bukti);
		}
	}
	if (!empty($data_detail)) {
		foreach ($data_detail as $item_detail) {
			if (!empty($item_detail->doc_file)) {
				$ext = strtolower(pathinfo($item_detail->doc_file, PATHINFO_EXTENSION));
				if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
					$all_print_images[] = base_url('assets/expense/' . $item_detail->doc_file);
				}
			}
		}
	}

	if (!empty($all_print_images)) :
		foreach ($all_print_images as $img_url) :
	?>
			<div class="pagebreak"></div>
			<div class="print-container attachment-img-page">
				<div style="text-align: left; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 8px;">
					<strong>Bukti Pengeluaran / Bon Dokumen</strong> &bull; No Dokumen: <?= $data->no_doc ?>
				</div>
				<img src="<?= $img_url ?>" alt="Bukti Bon Expense">
			</div>
	<?php
		endforeach;
	endif;
	?>
</body>

</html>
