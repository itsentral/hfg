<?php
$terbilang_text = function_exists('ynz_terbilang_format') ? ucwords(trim(ynz_terbilang_format((float)$data->jumlah_kasbon))) . ' Rupiah' : '';

// Attachments
$all_files = [];
if (!empty($list_files)) {
	foreach ($list_files as $f) {
		$all_files[] = [
			'name' => $f['file_name'],
			'path' => base_url('assets/expense/' . $f['file_name']),
			'ext'  => strtolower(pathinfo($f['file_name'], PATHINFO_EXTENSION))
		];
	}
}
if (empty($all_files)) {
	if (!empty($data->doc_file)) {
		$all_files[] = [
			'name' => $data->doc_file,
			'path' => base_url('assets/expense/' . $data->doc_file),
			'ext'  => strtolower(pathinfo($data->doc_file, PATHINFO_EXTENSION))
		];
	}
	if (!empty($data->doc_file_2)) {
		$all_files[] = [
			'name' => $data->doc_file_2,
			'path' => base_url('assets/expense/' . $data->doc_file_2),
			'ext'  => strtolower(pathinfo($data->doc_file_2, PATHINFO_EXTENSION))
		];
	}
}

// COA string
$coa_display = '-';
if (!empty($data->no_coa) || !empty($data->nm_coa)) {
	$coa_display = trim(($data->no_coa ?? '') . ' - ' . ($data->nm_coa ?? ''));
} elseif (!empty($data->coa)) {
	$coa_display = $data->coa;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Formulir Pengajuan Kasbon - <?= $data->no_doc ?></title>
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
			font-size: 11pt;
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
			margin-bottom: 15px;
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
			font-size: 16pt;
			font-weight: bold;
			color: #1a1a1a;
			letter-spacing: 0.5px;
		}

		.company-info p {
			margin: 0;
			font-size: 9pt;
			color: #444;
			line-height: 1.4;
		}

		.doc-title-box {
			text-align: right;
			vertical-align: top !important;
		}

		.doc-title-box h3 {
			margin: 0 0 6px 0;
			font-size: 15pt;
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
			font-size: 10pt;
		}

		.info-table td {
			padding: 5px 8px;
			vertical-align: top;
		}

		.info-label {
			width: 18%;
			color: #555;
			font-weight: 600;
		}

		.info-separator {
			width: 2%;
			text-align: center;
		}

		.info-value {
			width: 30%;
			color: #111;
			font-weight: 500;
		}

		/* Box Nominal */
		.nominal-card {
			border: 1.5px solid #0d6efd;
			background: #f8faff;
			border-radius: 6px;
			padding: 12px 16px;
			margin-bottom: 20px;
		}

		.nominal-card-header {
			font-size: 9pt;
			font-weight: bold;
			color: #555;
			text-transform: uppercase;
			margin-bottom: 4px;
		}

		.nominal-card-amount {
			font-size: 18pt;
			font-weight: bold;
			color: #0d6efd;
			margin-bottom: 4px;
		}

		.nominal-card-terbilang {
			font-size: 9.5pt;
			font-style: italic;
			color: #333;
		}

		/* Table Items */
		.items-table {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 20px;
			font-size: 9.5pt;
		}

		.items-table th,
		.items-table td {
			border: 1px solid #ccc;
			padding: 6px 8px;
		}

		.items-table th {
			background-color: #f2f2f2;
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

		/* Signature Section */
		.signature-table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 25px;
			margin-bottom: 20px;
			page-break-inside: avoid;
		}

		.signature-table td {
			width: 25%;
			border: 1px solid #bbb;
			padding: 6px 8px;
			text-align: center;
			vertical-align: top;
		}

		.signature-header {
			font-size: 9.5pt;
			font-weight: bold;
			color: #333;
			background-color: #f8f9fa;
			padding: 6px !important;
		}

		.signature-space {
			height: 65px;
		}

		.signature-name {
			font-size: 9.5pt;
			font-weight: bold;
			border-top: 1px dotted #888;
			padding-top: 4px;
			margin: 0 10px;
		}

		.signature-date {
			font-size: 8pt;
			color: #666;
			margin-top: 2px;
		}

		/* Lampiran Info */
		.attachments-box {
			border: 1px dashed #bbb;
			border-radius: 4px;
			padding: 10px 14px;
			font-size: 9pt;
			color: #444;
			background: #fafafa;
			margin-top: 15px;
			page-break-inside: avoid;
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

			.items-table th {
				background-color: #e9ecef !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}

			.signature-header {
				background-color: #f1f1f1 !important;
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
			<strong>Formulir Kasbon:</strong> <?= $data->no_doc ?>
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
					<h3>PENGAJUAN KASBON</h3>
					<div class="doc-badge">No. <?= $data->no_doc ?></div>
				</td>
			</tr>
		</table>

		<hr class="divider-line">

		<!-- Info Pengajuan & Transfer -->
		<table class="info-table">
			<tr>
				<td class="info-label">Tanggal Dokumen</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= date('d F Y', strtotime($data->tgl_doc)) ?></td>

				<td class="info-label">Bank Penerima</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= $data->bank_id ?: '-' ?></td>
			</tr>
			<tr>
				<td class="info-label">Nama Pemohon</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= $nmuser ?></td>

				<td class="info-label">No. Rekening</td>
				<td class="info-separator">:</td>
				<td class="info-value font-monospace"><strong><?= $data->accnumber ?: '-' ?></strong></td>
			</tr>
			<tr>
				<td class="info-label">Departemen</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= !empty($nm_dept) ? $nm_dept : '-' ?></td>

				<td class="info-label">Atas Nama Rekening</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= $data->accname ?: '-' ?></td>
			</tr>
			<tr>
				<td class="info-label">Akun COA</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= htmlspecialchars($coa_display) ?></td>

				<td class="info-label">Referensi PR</td>
				<td class="info-separator">:</td>
				<td class="info-value"><?= !empty($data->id_pr) ? $data->id_pr . ' (' . ucwords($data->tipe_pr) . ')' : 'Input Manual' ?></td>
			</tr>
			<tr>
				<td class="info-label">Keperluan</td>
				<td class="info-separator">:</td>
				<td class="info-value" colspan="4"><strong><?= nl2br(htmlspecialchars($data->keperluan)) ?></strong></td>
			</tr>
			<?php if (!empty($data->keterangan)) : ?>
				<tr>
					<td class="info-label">Keterangan</td>
					<td class="info-separator">:</td>
					<td class="info-value" colspan="4"><?= nl2br(htmlspecialchars($data->keterangan)) ?></td>
				</tr>
			<?php endif; ?>
		</table>

		<!-- Box Nominal Kasbon -->
		<div class="nominal-card">
			<div class="nominal-card-header">Jumlah Kasbon yang Diajukan :</div>
			<div class="nominal-card-amount">
				Rp <?= number_format($data->jumlah_kasbon, 0, ',', '.') ?>
			</div>
			<?php if (!empty($terbilang_text)) : ?>
				<div class="nominal-card-terbilang">
					Terbilang: # <?= $terbilang_text ?> #
				</div>
			<?php endif; ?>
		</div>

		<!-- Tabel Rincian PR Non PO (Jika Ada) -->
		<?php if (!empty($list_detail_pr_kasbon)) : ?>
			<div style="font-size: 10pt; font-weight: bold; margin-bottom: 6px; color: #333;">
				Rincian Kebutuhan Barang / Material (Referensi: <?= $data->id_pr ?>)
			</div>
			<table class="items-table">
				<thead>
					<tr>
						<th style="width: 35px;">No</th>
						<th>Nama Barang / Material</th>
						<th style="width: 70px;">Qty</th>
						<th style="width: 70px;">Satuan</th>
						<th style="width: 130px;">Estimasi Harga (Rp)</th>
						<th style="width: 140px;">Total (Rp)</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					$grand_tot = 0;
					foreach ($list_detail_pr_kasbon as $item) :
						$tot = (float)($item['total_harga'] ?? ($item['harga'] * $item['qty']));
						$grand_tot += $tot;
					?>
						<tr>
							<td class="text-center"><?= $no++ ?></td>
							<td><?= htmlspecialchars($item['nm_material']) ?></td>
							<td class="text-center"><?= number_format($item['qty']) ?></td>
							<td class="text-center"><?= htmlspecialchars($item['satuan'] ?: '-') ?></td>
							<td class="text-right"><?= number_format($item['harga'], 0, ',', '.') ?></td>
							<td class="text-right"><strong><?= number_format($tot, 0, ',', '.') ?></strong></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="5" class="text-right">Total Referensi PR</th>
						<th class="text-right">Rp <?= number_format($grand_tot, 0, ',', '.') ?></th>
					</tr>
				</tfoot>
			</table>
		<?php endif; ?>

		<!-- Kolom Pengesahan / Tanda Tangan -->
		<table class="signature-table">
			<tr>
				<td class="signature-header">Diajukan Oleh,</td>
				<td class="signature-header">Mengetahui,</td>
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
					<div class="signature-name"><?= $nmuser ?></div>
					<div class="signature-date">Tgl: <?= !empty($data->created_on) ? date('d/m/Y', strtotime($data->created_on)) : date('d/m/Y') ?></div>
				</td>
				<td>
					<div class="signature-name">Atasan Langsung</div>
					<div class="signature-date">Tgl: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
				</td>
				<td>
					<div class="signature-name"><?= (isset($data->sts_finance) && $data->sts_finance == 1) ? 'Finance Department' : 'Finance' ?></div>
					<div class="signature-date">
						Tgl: <?= !empty($data->app_finance_date) ? date('d/m/Y', strtotime($data->app_finance_date)) : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ?>
					</div>
				</td>
				<td>
					<div class="signature-name"><?= !empty($data->approved_by) ? $data->approved_by : 'Direksi / Management' ?></div>
					<div class="signature-date">
						Tgl: <?= !empty($data->approved_on) ? date('d/m/Y', strtotime($data->approved_on)) : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ?>
					</div>
				</td>
			</tr>
		</table>

		<!-- Lampiran Berkas Information -->
		<?php if (!empty($all_files)) : ?>
			<div class="attachments-box">
				<strong>Dokumen / Berkas Lampiran Pendukung:</strong>
				<ol style="margin: 4px 0 0 16px; padding: 0;">
					<?php foreach ($all_files as $af) : ?>
						<li><?= htmlspecialchars($af['name']) ?> (<?= strtoupper($af['ext']) ?>)</li>
					<?php endforeach; ?>
				</ol>
			</div>
		<?php endif; ?>
	</div>

	<!-- Preview Gambar Lampiran di Lembar Tambahan jika dicetak -->
	<?php
	$image_attachments = array_filter($all_files, function ($af) {
		return in_array($af['ext'], ['jpg', 'jpeg', 'png', 'webp', 'gif']);
	});
	if (!empty($image_attachments)) :
		foreach ($image_attachments as $img_att) :
	?>
			<div class="pagebreak"></div>
			<div class="print-container attachment-img-page">
				<div style="text-align: left; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 8px;">
					<strong>Lampiran Berkas:</strong> <?= htmlspecialchars($img_att['name']) ?> &bull; No Dokumen: <?= $data->no_doc ?>
				</div>
				<img src="<?= $img_att['path'] ?>" alt="Lampiran Kasbon">
			</div>
	<?php
		endforeach;
	endif;
	?>
</body>

</html>
