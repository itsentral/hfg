<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
$mod = isset($mod) ? $mod : '';
$stsview = isset($stsview) ? $stsview : 'view';

// Department
$dept_name = isset($nm_dept) && !empty($nm_dept) ? $nm_dept : '';
if (empty($dept_name) && !empty($data->departement)) {
	$get_dept = $this->db->get_where('ms_department', ['id' => $data->departement])->row();
	if (!empty($get_dept)) {
		$dept_name = $get_dept->nama;
	}
}

// User / Pemohon
$nama_pemohon = !empty($data->nama) ? $data->nama : $data->created_by;

// Status styling
$status_label = 'Draft / Waiting Approval';
$status_badge = 'bg-primary text-light';

if ($data->status == '0') {
	if (isset($data->sts_finance) && $data->sts_finance == '1') {
		$status_label = 'Waiting Approval Management';
		$status_badge = 'bg-info text-dark';
	} else {
		$status_label = 'Waiting Approval Finance';
		$status_badge = 'bg-primary text-light';
	}
} elseif (in_array($data->status, ['1', '2'])) {
	$status_label = 'Approved';
	$status_badge = 'bg-success text-light';
} elseif ($data->status == '3') {
	$check_exp = $this->db->get_where('tr_expense_detail', ['id_kasbon' => $data->no_doc, 'status' => 2])->row();
	if (!empty($check_exp)) {
		$status_label = 'Closed';
		$status_badge = 'bg-dark text-light';
	} else {
		$status_label = 'Paid';
		$status_badge = 'bg-success text-light';
	}
} elseif ($data->status == '9') {
	$status_label = 'Rejected';
	$status_badge = 'bg-danger text-light';
} elseif ($data->status == '4') {
	$status_label = 'Kurang';
	$status_badge = 'bg-warning text-dark';
}

// COA String
$val_coa = '-';
if (!empty($data->no_coa) || !empty($data->nm_coa)) {
	$val_coa = trim(($data->no_coa ?? '') . ' - ' . ($data->nm_coa ?? ''));
} elseif (!empty($data->coa)) {
	$val_coa = $data->coa;
}

// Alasan reject jika ada
$reject_msg = '';
if (!empty($data->st_reject)) {
	$reject_msg = $data->st_reject;
} elseif (!empty($data->reject_reason)) {
	$reject_msg = $data->reject_reason;
} elseif (!empty($data->reject_reason_finance)) {
	$reject_msg = $data->reject_reason_finance;
}

// Terbilang
$terbilang_nominal = function_exists('ynz_terbilang_format') ? ucwords(trim(ynz_terbilang_format((float)$data->jumlah_kasbon))) . ' Rupiah' : '';

// Siapkan list attachments dari tabel tr_kasbon_files dan legacy doc_file/doc_file_2
$all_attachments = [];
if (!empty($list_files)) {
	foreach ($list_files as $f) {
		$all_attachments[] = [
			'file_name' => $f['file_name'],
			'file_path' => base_url('assets/expense/' . $f['file_name']),
			'file_size' => isset($f['file_size']) ? $f['file_size'] : 0,
			'uploaded_at' => isset($f['created_on']) ? $f['created_on'] : null,
		];
	}
}
if (empty($all_attachments)) {
	if (!empty($data->doc_file)) {
		$all_attachments[] = [
			'file_name' => $data->doc_file,
			'file_path' => base_url('assets/expense/' . $data->doc_file),
			'file_size' => 0,
			'uploaded_at' => $data->created_on ?? null,
		];
	}
	if (!empty($data->doc_file_2)) {
		$all_attachments[] = [
			'file_name' => $data->doc_file_2,
			'file_path' => base_url('assets/expense/' . $data->doc_file_2),
			'file_size' => 0,
			'uploaded_at' => $data->created_on ?? null,
		];
	}
}
?>

<div class="container-fluid p-0">
	<!-- Top Navigation / Action Bar -->
	<div class="card border-0 shadow-sm rounded-3 mb-4">
		<div class="card-body p-3 p-md-4">
			<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
				<div>
					<div class="d-flex align-items-center gap-2 mb-1">
						<span class="badge <?= $status_badge ?> px-3 py-2 rounded-pill fw-medium fs-7">
							<i class="fa fa-info-circle me-1"></i><?= $status_label ?>
						</span>
						<?php if (!empty($data->id_pr)) : ?>
							<span class="badge bg-light text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-medium fs-7">
								<i class="fa fa-link me-1"></i>Tarik dari <?= ucwords($data->tipe_pr ?: 'PR Non PO') ?>
							</span>
						<?php else : ?>
							<span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-medium fs-7">
								<i class="fa fa-pencil me-1"></i>Input Manual
							</span>
						<?php endif; ?>
					</div>
					<h3 class="fw-bold mb-1 text-dark">
						Detail Pengajuan Kasbon <span class="text-primary">#<?= $data->no_doc ?></span>
					</h3>
					<p class="text-muted small mb-0">
						Dibuat pada <?= date('d F Y', strtotime($data->tgl_doc)) ?> &bull; Pemohon: <span class="fw-semibold text-dark"><?= $nama_pemohon ?></span> <?= !empty($dept_name) ? "({$dept_name})" : "" ?>
					</p>
				</div>

				<div class="d-flex flex-wrap align-items-center gap-2">
					<button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="list_view(); return false;">
						<i class="fa fa-arrow-left me-1"></i> Kembali
					</button>
					<a href="<?= base_url('expense/kasbon_print/' . $data->id) ?>" target="_blank" class="btn btn-outline-primary btn-sm px-3">
						<i class="fa fa-print me-1"></i> Cetak Kasbon
					</a>

					<!-- Approval Action Buttons (Khusus Finance/Management jika di mode approval dan BUKAN view) -->
					<?php 
					$stsview_mode = isset($stsview) ? $stsview : '';
					if ($stsview_mode !== 'view' && $mod == '_fin' && $data->status == 0 && (isset($data->sts_finance) ? $data->sts_finance == 0 : true)) : ?>
						<button type="button" class="btn btn-success btn-sm px-3" onclick="data_approve(<?= $data->id ?>, 1)">
							<i class="fa fa-check me-1"></i> Setujui (Finance)
						</button>
						<button type="button" class="btn btn-danger btn-sm px-3" onclick="data_reject()">
							<i class="fa fa-times me-1"></i> Tolak
						</button>
					<?php elseif ($stsview_mode !== 'view' && $mod == '_mgt' && $data->status == 0 && (isset($data->sts_finance) ? $data->sts_finance == 1 : false)) : ?>
						<button type="button" class="btn btn-success btn-sm px-3" onclick="data_approve(<?= $data->id ?>, 1)">
							<i class="fa fa-check me-1"></i> Setujui (Management)
						</button>
						<button type="button" class="btn btn-danger btn-sm px-3" onclick="data_reject()">
							<i class="fa fa-times me-1"></i> Tolak
						</button>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Alert Alasan Penolakan jika Status Reject -->
	<?php if (!empty($reject_msg)) : ?>
		<div class="alert alert-danger border-0 shadow-sm rounded-3 p-3 mb-4 d-flex align-items-start gap-3">
			<div class="fs-3 text-danger"><i class="fa fa-ban"></i></div>
			<div>
				<h5 class="fw-bold mb-1 text-danger">Pengajuan Kasbon Ditolak!</h5>
				<p class="mb-0 text-dark"><?= nl2br(htmlspecialchars($reject_msg)) ?></p>
			</div>
		</div>
	<?php endif; ?>

	<!-- Grid Info Utama & Nominal -->
	<div class="row g-4 mb-4">
		<!-- Kolom Kiri: Informasi Pengajuan & COA -->
		<div class="col-lg-7">
			<div class="card border-0 shadow-sm rounded-3 h-100">
				<div class="card-header bg-transparent border-bottom py-3">
					<h5 class="card-title fw-bold mb-0 text-dark">
						<i class="fa fa-file-text-o text-primary me-2"></i>Informasi Kasbon
					</h5>
				</div>
				<div class="card-body p-4">
					<div class="row g-3">
						<div class="col-sm-6">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">No. Dokumen</label>
							<div class="fw-bold text-dark fs-6"><?= $data->no_doc ?></div>
						</div>
						<div class="col-sm-6">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">Tanggal Dokumen</label>
							<div class="fw-bold text-dark fs-6"><?= date('d F Y', strtotime($data->tgl_doc)) ?></div>
						</div>

						<div class="col-sm-6">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">Pemohon</label>
							<div class="fw-semibold text-dark fs-6"><?= $nama_pemohon ?></div>
						</div>
						<div class="col-sm-6">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">Departemen</label>
							<div class="fw-semibold text-dark fs-6"><?= !empty($dept_name) ? $dept_name : '-' ?></div>
						</div>

						<div class="col-12">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">Keperluan</label>
							<div class="p-3 bg-light rounded-3 text-dark fw-medium border border-light-subtle">
								<?= nl2br(htmlspecialchars($data->keperluan ?: '-')) ?>
							</div>
						</div>

						<div class="col-12">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">Keterangan Tambahan</label>
							<div class="p-3 bg-light rounded-3 text-dark border border-light-subtle">
								<?= nl2br(htmlspecialchars($data->keterangan ?: '-')) ?>
							</div>
						</div>

						<div class="col-12">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">Akun COA</label>
							<div class="d-flex align-items-center gap-2">
								<span class="badge bg-secondary-subtle text-secondary-emphasis px-3 py-2 rounded-2 fs-7 fw-semibold border">
									<i class="fa fa-book me-1"></i><?= htmlspecialchars($val_coa) ?>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Kolom Kanan: Nominal Highlight & Rekening Transfer -->
		<div class="col-lg-5">
			<div class="d-flex flex-column gap-4 h-100">
				<!-- Nominal Box -->
				<div class="card border-0 shadow-sm rounded-3" style="background: linear-gradient(135deg, #f8faff 0%, #eef4fe 100%); border-left: 5px solid #0d6efd !important;">
					<div class="card-body p-4">
						<div class="d-flex justify-content-between align-items-center mb-2">
							<span class="text-uppercase fw-bold text-muted small letter-spacing-1">Nominal Kasbon</span>
							<span class="badge bg-primary text-light px-2 py-1 rounded">IDR</span>
						</div>
						<h2 class="fw-bolder text-primary mb-2">
							Rp <?= number_format($data->jumlah_kasbon, 0, ',', '.') ?>
						</h2>
						<?php if (!empty($terbilang_nominal)) : ?>
							<p class="text-muted small fst-italic mb-0 border-top pt-2 mt-2">
								<i class="fa fa-quote-left text-primary opacity-50 me-1"></i><?= $terbilang_nominal ?>
							</p>
						<?php endif; ?>
					</div>
				</div>

				<!-- Info Transfer Bank -->
				<div class="card border-0 shadow-sm rounded-3 flex-grow-1">
					<div class="card-header bg-transparent border-bottom py-3">
						<h5 class="card-title fw-bold mb-0 text-dark">
							<i class="fa fa-credit-card text-primary me-2"></i>Informasi Rekening Transfer
						</h5>
					</div>
					<div class="card-body p-4">
						<div class="d-flex flex-column gap-3">
							<div class="p-3 bg-light rounded-3 border border-light-subtle">
								<div class="row align-items-center">
									<div class="col-4 text-muted small fw-semibold text-uppercase">Nama Bank</div>
									<div class="col-8 fw-bold text-dark fs-6">
										<i class="fa fa-university text-secondary me-2"></i><?= $data->bank_id ?: '-' ?>
									</div>
								</div>
							</div>

							<div class="p-3 bg-light rounded-3 border border-light-subtle">
								<div class="row align-items-center">
									<div class="col-4 text-muted small fw-semibold text-uppercase">No. Rekening</div>
									<div class="col-8 fw-bold text-primary fs-6 font-monospace">
										<?= $data->accnumber ?: '-' ?>
									</div>
								</div>
							</div>

							<div class="p-3 bg-light rounded-3 border border-light-subtle">
								<div class="row align-items-center">
									<div class="col-4 text-muted small fw-semibold text-uppercase">Atas Nama</div>
									<div class="col-8 fw-semibold text-dark fs-6">
										<?= $data->accname ?: '-' ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Card Referensi PR Non PO (Hanya tampil jika ada data PR) -->
	<?php if (!empty($data->id_pr) || !empty($list_detail_pr_kasbon)) : ?>
		<div class="card border-0 shadow-sm rounded-3 mb-4">
			<div class="card-header bg-transparent border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
				<div class="d-flex align-items-center gap-2">
					<h5 class="card-title fw-bold mb-0 text-dark">
						<i class="fa fa-list-alt text-primary me-2"></i>Referensi PR Non PO
					</h5>
					<span class="badge bg-primary text-light px-3 py-1 rounded-pill">
						<?= $data->id_pr ?: 'PR' ?>
					</span>
				</div>
				<?php if (!empty($data->tipe_pr)) : ?>
					<span class="text-muted small">
						Kategori: <strong class="text-dark"><?= ucwords($data->tipe_pr) ?></strong>
					</span>
				<?php endif; ?>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead class="table-light text-secondary">
							<tr>
								<th class="text-center py-3" style="width: 50px;">No.</th>
								<th class="py-3">Nama Material / Barang</th>
								<th class="text-center py-3" style="width: 100px;">Qty</th>
								<th class="text-center py-3" style="width: 100px;">Satuan</th>
								<th class="text-end py-3" style="width: 180px;">Harga Satuan (Rp)</th>
								<th class="text-end py-3 pe-4" style="width: 180px;">Total (Rp)</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$grand_total_pr = 0;
							if (!empty($list_detail_pr_kasbon)) :
								$no = 1;
								foreach ($list_detail_pr_kasbon as $detail_pr) :
									$total_harga = (float)($detail_pr['total_harga'] ?? ($detail_pr['harga'] * $detail_pr['qty']));
									$grand_total_pr += $total_harga;
							?>
									<tr>
										<td class="text-center text-muted fw-medium"><?= $no++ ?></td>
										<td>
											<span class="fw-semibold text-dark"><?= htmlspecialchars($detail_pr['nm_material']) ?></span>
										</td>
										<td class="text-center fw-medium"><?= number_format($detail_pr['qty']) ?></td>
										<td class="text-center text-muted"><?= htmlspecialchars($detail_pr['satuan'] ?: '-') ?></td>
										<td class="text-end font-monospace"><?= number_format($detail_pr['harga'], 0, ',', '.') ?></td>
										<td class="text-end font-monospace fw-bold text-dark pe-4"><?= number_format($total_harga, 0, ',', '.') ?></td>
									</tr>
								<?php
								endforeach;
							else :
								?>
								<tr>
									<td colspan="6" class="text-center py-4 text-muted">
										<i class="fa fa-info-circle me-1"></i> Tidak ada rincian item PR tersimpan.
									</td>
								</tr>
							<?php endif; ?>
						</tbody>
						<tfoot class="table-light">
							<tr>
								<th colspan="5" class="text-end py-3 fw-bold text-dark">Grand Total Referensi PR :</th>
								<th class="text-end py-3 pe-4 font-monospace fw-bolder fs-6 text-primary">
									Rp <?= number_format($grand_total_pr, 0, ',', '.') ?>
								</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- Card Berkas Lampiran & History Approval -->
	<div class="row g-4 mb-4">
		<!-- Lampiran Berkas -->
		<div class="col-lg-7">
			<div class="card border-0 shadow-sm rounded-3 h-100">
				<div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
					<h5 class="card-title fw-bold mb-0 text-dark">
						<i class="fa fa-paperclip text-primary me-2"></i>Berkas Lampiran Pendukung
					</h5>
					<span class="badge bg-light text-dark border rounded-pill px-3">
						<?= count($all_attachments) ?> Berkas
					</span>
				</div>
				<div class="card-body p-4">
					<?php if (!empty($all_attachments)) : ?>
						<div class="table-responsive">
							<table class="table table-hover align-middle mb-0">
								<thead class="table-light text-secondary">
									<tr>
										<th style="width: 40px;">#</th>
										<th>Nama Berkas</th>
										<th class="text-center" style="width: 140px;">Aksi</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$idx = 1;
									foreach ($all_attachments as $att) :
										$ext = strtolower(pathinfo($att['file_name'], PATHINFO_EXTENSION));
										$is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
										$is_pdf = ($ext == 'pdf');

										$icon = 'fa-file-o text-secondary';
										if ($is_pdf) $icon = 'fa-file-pdf-o text-danger';
										elseif ($is_image) $icon = 'fa-file-image-o text-success';
									?>
										<tr>
											<td class="text-muted small"><?= $idx++ ?></td>
											<td>
												<div class="d-flex align-items-center gap-2">
													<i class="fa <?= $icon ?> fs-5"></i>
													<div>
														<div class="fw-semibold text-dark text-break small"><?= htmlspecialchars($att['file_name']) ?></div>
														<span class="text-muted" style="font-size: 0.75rem;">
															<?= strtoupper($ext) ?> &bull; <?= !empty($att['uploaded_at']) ? date('d M Y H:i', strtotime($att['uploaded_at'])) : 'Tersimpan' ?>
														</span>
													</div>
												</div>
											</td>
											<td class="text-center">
												<div class="btn-group btn-group-sm">
													<?php if ($is_image || $is_pdf) : ?>
														<button type="button" class="btn btn-outline-primary btn-sm" onclick="previewModalFile('<?= $att['file_path'] ?>', '<?= $is_pdf ? 'pdf' : 'image' ?>', '<?= htmlspecialchars($att['file_name']) ?>')" title="Lihat Preview">
															<i class="fa fa-eye"></i>
														</button>
													<?php endif; ?>
													<a href="<?= $att['file_path'] ?>" download class="btn btn-outline-secondary btn-sm" title="Download">
														<i class="fa fa-download"></i>
													</a>
												</div>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php else : ?>
						<div class="text-center py-4 text-muted">
							<i class="fa fa-folder-open-o fs-1 opacity-50 mb-2"></i>
							<p class="mb-0 small">Tidak ada berkas lampiran yang diunggah.</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- History & Log Verifikasi -->
		<div class="col-lg-5">
			<div class="card border-0 shadow-sm rounded-3 h-100">
				<div class="card-header bg-transparent border-bottom py-3">
					<h5 class="card-title fw-bold mb-0 text-dark">
						<i class="fa fa-check-circle-o text-primary me-2"></i>Riwayat Persetujuan
					</h5>
				</div>
				<div class="card-body p-4">
					<div class="position-relative ps-4 border-start border-2 border-primary-subtle d-flex flex-column gap-4 ms-2">
						<!-- Pemohon -->
						<div class="position-relative">
							<span class="position-absolute top-0 start-0 translate-middle p-2 bg-primary rounded-circle" style="left: -17px !important;"></span>
							<div class="fw-bold text-dark small text-uppercase">1. Pengajuan Dibuat</div>
							<div class="fw-semibold text-dark"><?= $nama_pemohon ?></div>
							<div class="text-muted small">
								<?= !empty($data->created_on) ? date('d F Y, H:i', strtotime($data->created_on)) : '-' ?>
							</div>
						</div>

						<!-- Approval Finance -->
						<div class="position-relative">
							<?php
							$fin_approved = (isset($data->sts_finance) && $data->sts_finance == '1');
							$dot_fin = $fin_approved ? 'bg-success' : ($data->status == '9' ? 'bg-danger' : 'bg-warning');
							?>
							<span class="position-absolute top-0 start-0 translate-middle p-2 <?= $dot_fin ?> rounded-circle" style="left: -17px !important;"></span>
							<div class="fw-bold text-dark small text-uppercase">2. Verifikasi Finance</div>
							<?php if ($fin_approved) : ?>
								<div class="fw-semibold text-success"><i class="fa fa-check me-1"></i>Telah Diverifikasi</div>
								<div class="text-muted small">
									<?= !empty($data->app_finance_date) ? date('d F Y, H:i', strtotime($data->app_finance_date)) : 'Selesai' ?>
								</div>
							<?php elseif ($data->status == '9') : ?>
								<div class="fw-semibold text-danger"><i class="fa fa-times me-1"></i>Ditolak</div>
							<?php else : ?>
								<div class="fw-semibold text-warning"><i class="fa fa-clock-o me-1"></i>Menunggu Verifikasi Finance</div>
							<?php endif; ?>
						</div>

						<!-- Approval Management -->
						<div class="position-relative">
							<?php
							$mgt_approved = in_array($data->status, ['1', '2', '3']);
							$dot_mgt = $mgt_approved ? 'bg-success' : ($data->status == '9' ? 'bg-danger' : 'bg-secondary');
							?>
							<span class="position-absolute top-0 start-0 translate-middle p-2 <?= $dot_mgt ?> rounded-circle" style="left: -17px !important;"></span>
							<div class="fw-bold text-dark small text-uppercase">3. Persetujuan Management</div>
							<?php if ($mgt_approved) : ?>
								<div class="fw-semibold text-success">
									<i class="fa fa-check me-1"></i>Disetujui oleh <?= $data->approved_by ?: 'Management' ?>
								</div>
								<div class="text-muted small">
									<?= !empty($data->approved_on) ? date('d F Y, H:i', strtotime($data->approved_on)) : '-' ?>
								</div>
							<?php elseif ($data->status == '9') : ?>
								<div class="fw-semibold text-danger"><i class="fa fa-times me-1"></i>Ditolak</div>
							<?php else : ?>
								<div class="fw-semibold text-muted"><i class="fa fa-clock-o me-1"></i>Menunggu Approval Management</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal Preview Berkas -->
<div class="modal fade" id="modalPreviewFile" tabindex="-1" aria-labelledby="modalPreviewFileTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content border-0 shadow rounded-3">
			<div class="modal-header border-bottom py-3">
				<h5 class="modal-title fw-bold text-dark fs-6" id="modalPreviewFileTitle">Preview Berkas</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-0 text-center bg-light" id="modalPreviewFileBody" style="min-height: 400px; max-height: 80vh; overflow-y: auto;">
				<!-- Content will be injected by JavaScript -->
			</div>
			<div class="modal-footer border-top py-2 d-flex justify-content-between">
				<a href="#" id="modalPreviewFileDownload" download class="btn btn-outline-primary btn-sm">
					<i class="fa fa-download me-1"></i> Download Berkas
				</a>
				<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<script>
	function previewModalFile(fileUrl, fileType, fileName) {
		$('#modalPreviewFileTitle').text(fileName);
		$('#modalPreviewFileDownload').attr('href', fileUrl);

		var body = $('#modalPreviewFileBody');
		body.empty();

		if (fileType === 'pdf') {
			body.html('<iframe src="' + fileUrl + '#toolbar=0" style="width: 100%; height: 75vh; border: none;"></iframe>');
		} else if (fileType === 'image') {
			body.html('<div class="p-3"><img src="' + fileUrl + '" class="img-fluid rounded shadow-sm" style="max-height: 70vh;"></div>');
		} else {
			body.html('<div class="p-5 text-muted"><i class="fa fa-file-o fs-1 mb-3"></i><p>Preview tidak didukung untuk tipe file ini. Silakan unduh berkas untuk membukanya.</p></div>');
		}

		$('#modalPreviewFile').modal('show');
	}

	function list_view() {
		if ($('#card-list').length && $('#form-data').length) {
			$('#form-data').hide().empty();
			$('#card-list, .box').fadeIn(200);
			if (typeof datatables !== 'undefined' && datatables) {
				datatables.ajax.reload(null, false);
			}
		} else {
			window.location.href = siteurl + 'expense/kasbon' + '<?= $mod ?>';
		}
	}

	function data_approve(id, next_status) {
		var mod = '<?= $mod ?>';
		var url = siteurl + (mod === '_mgt' ? 'expense/kasbon_approve_manage/' : 'expense/kasbon_approve/') + id;

		Swal.fire({
			title: "Konfirmasi Approval",
			text: "Apakah Anda yakin ingin menyetujui pengajuan kasbon ini?",
			icon: "question",
			showCancelButton: true,
			confirmButtonColor: "#198754",
			cancelButtonColor: "#6c757d",
			confirmButtonText: '<i class="fa fa-check me-1"></i> Ya, Setujui!',
			cancelButtonText: "Batal"
		}).then((result) => {
			if (result.isConfirmed) {
				Swal.fire({
					title: "Memproses...",
					text: "Sedang memproses persetujuan kasbon...",
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: url,
					dataType: "json",
					type: 'POST',
					success: function(msg) {
						if (msg.save == '1' || msg.save == 1 || msg.status == '1' || msg.status == 1) {
							Swal.fire({
								icon: "success",
								title: "Berhasil Disetujui!",
								text: msg.msg || "Kasbon berhasil disetujui",
								timer: 2000,
								showConfirmButton: false
							});
							list_view();
						} else {
							Swal.fire({
								icon: "error",
								title: "Gagal!",
								text: msg.msg || "Gagal menyetujui pengajuan kasbon"
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: "error",
							title: "Error!",
							text: "Terjadi kesalahan saat memproses approval"
						});
					}
				});
			}
		});
	}

	function data_reject() {
		var id = '<?= $data->id ?>';
		var mod = '<?= $mod ?>';

		Swal.fire({
			title: "Alasan Penolakan",
			text: "Silakan masukkan alasan penolakan pengajuan kasbon ini:",
			input: "textarea",
			inputPlaceholder: "Tuliskan alasan penolakan di sini...",
			inputAttributes: {
				"aria-label": "Alasan penolakan",
				"rows": 4
			},
			showCancelButton: true,
			confirmButtonColor: "#dc3545",
			cancelButtonColor: "#6c757d",
			confirmButtonText: '<i class="fa fa-ban me-1"></i> Tolak Pengajuan',
			cancelButtonText: "Batal",
			inputValidator: (value) => {
				if (!value || !value.trim()) {
					return "Alasan penolakan wajib diisi!";
				}
			}
		}).then((result) => {
			if (result.isConfirmed) {
				var reason = result.value.trim();
				Swal.fire({
					title: "Memproses...",
					text: "Sedang memproses penolakan kasbon...",
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				$.ajax({
					url: siteurl + 'expense/reject/',
					data: {
						'id': id,
						'reason': reason,
						'table': 'tr_kasbon'
					},
					dataType: "json",
					type: 'POST',
					success: function(msg) {
						if (msg.save == '1' || msg.save == 1 || msg.status == '1' || msg.status == 1) {
							Swal.fire({
								icon: "success",
								title: "Berhasil Ditolak!",
								text: msg.message || msg.msg || "Pengajuan kasbon berhasil ditolak",
								timer: 2000,
								showConfirmButton: false
							});
							list_view();
						} else {
							Swal.fire({
								icon: "error",
								title: "Gagal!",
								text: msg.message || msg.msg || "Gagal menolak pengajuan kasbon"
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: "error",
							title: "Error!",
							text: "Terjadi kesalahan saat menolak pengajuan"
						});
					}
				});
			}
		});
	}
</script>
