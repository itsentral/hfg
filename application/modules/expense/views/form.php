<?php
$datauser = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
$dept = !empty($datauser) ? $datauser->department_id : '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!empty($datauser) && !empty($datauser->employee_id)) {
	$datadept = $this->db->get_where('employee', ['id' => $datauser->employee_id])->row();
	if (!empty($datadept)) {
		$bank_id = $datadept->bank_id;
		$accnumber = $datadept->accnumber;
		$accname = $datadept->accname;
	}
}

$id = (isset($data->id)) ? $data->id : '';
$pre_id = (isset($pre_id)) ? $pre_id : '';
$no_doc_val = (isset($data->no_doc) && !empty($data->no_doc)) ? $data->no_doc : $pre_id;
$tgl_doc = (isset($data->tgl_doc)) ? $data->tgl_doc : date('Y-m-d');
$keterangan = (isset($data->informasi)) ? $data->informasi : '';
$bank_id = (isset($data->bank_id) && $data->bank_id !== '') ? $data->bank_id : $bank_id;
$accnumber = (isset($data->accnumber) && $data->accnumber !== '') ? $data->accnumber : $accnumber;
$accname = (isset($data->accname) && $data->accname !== '') ? $data->accname : $accname;
$pettycash = (isset($data->pettycash)) ? $data->pettycash : '';
$stsview = (isset($stsview)) ? $stsview : '';
$option_coa = (isset($option_coa) && is_array($option_coa)) ? $option_coa : [];
$datacoa = '';
foreach ($option_coa as $keys => $val) {
	$datacoa .= '<option value="' . $keys . '">' . $val . '</option>';
}

$is_view = ($stsview == 'view' || $stsview == 'approval');

// Cek apakah mode report / tarikan kasbon
$kasbon_obj = isset($kasbon) && !empty($kasbon) ? $kasbon : null;
$no_doc_kasbon_val = !empty($kasbon_obj) ? $kasbon_obj->no_doc : (isset($data->id_kasbon) && !empty($data->id_kasbon) ? $data->id_kasbon : (isset($no_doc_kasbon) ? $no_doc_kasbon : ''));
$is_report = !empty($no_doc_kasbon_val);

$total_kasbon_val = 0;
if (!empty($kasbon_obj)) {
	$total_kasbon_val = (float)$kasbon_obj->jumlah_kasbon;
	$bank_id = !empty($kasbon_obj->bank_id) ? $kasbon_obj->bank_id : $bank_id;
	$accnumber = !empty($kasbon_obj->accnumber) ? $kasbon_obj->accnumber : $accnumber;
	$accname = !empty($kasbon_obj->accname) ? $kasbon_obj->accname : $accname;
	$keterangan = empty($keterangan) && !empty($kasbon_obj->keperluan) ? $kasbon_obj->keperluan : $keterangan;
	$dept = !empty($kasbon_obj->departement) ? $kasbon_obj->departement : $dept;
} elseif (isset($data->id_kasbon) && !empty($data->id_kasbon)) {
	// Ambil dari data kasbon di database jika sedang edit/view
	$kb_row = $this->db->get_where('tr_kasbon', ['no_doc' => $data->id_kasbon])->row();
	if (!empty($kb_row)) {
		$kasbon_obj = $kb_row;
		$total_kasbon_val = (float)$kb_row->jumlah_kasbon;
	}
}

$nama_pemohon = isset($data->nama) ? $data->nama : (!empty($kasbon_obj) ? $kasbon_obj->nama : $this->auth->user_name());

// Status Label & Badge
$status_label = 'Draft / Baru';
$status_badge = 'bg-warning text-dark';
if (isset($data->status)) {
	if ($data->status == '1') {
		$status_label = 'Disetujui Finance';
		$status_badge = 'bg-info text-dark';
	} elseif ($data->status == '2') {
		$status_label = 'Disetujui Management';
		$status_badge = 'bg-primary text-light';
	} elseif ($data->status == '3') {
		$status_label = 'Selesai';
		$status_badge = 'bg-success text-light';
	} elseif ($data->status == '9') {
		$status_label = 'Ditolak';
		$status_badge = 'bg-danger text-light';
	}
}
?>

<div class="container-fluid p-0">
	<!-- Top Bar / Header Card -->
	<div class="card border-0 shadow-sm rounded-3 mb-4">
		<div class="card-body p-3 p-md-4">
			<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
				<div>
					<div class="d-flex align-items-center gap-2 mb-1">
						<span class="badge <?= $status_badge ?> px-3 py-2 rounded-pill fw-medium">
							<i class="fa fa-info-circle me-1"></i><?= $status_label ?>
						</span>
						<?php if ($stsview == 'approval') : ?>
							<span class="badge bg-light text-warning border px-3 py-2 rounded-pill fw-medium">
								<i class="fa fa-check-square-o me-1"></i>Mode Approval
							</span>
						<?php elseif ($stsview == 'view') : ?>
							<span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-medium">
								<i class="fa fa-eye me-1"></i>Mode Detail (View)
							</span>
						<?php elseif ($is_report) : ?>
							<span class="badge bg-light text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-medium">
								<i class="fa fa-ticket me-1"></i>Mode Expense Report (Kasbon)
							</span>
						<?php else : ?>
							<span class="badge bg-light text-success border border-success-subtle px-3 py-2 rounded-pill fw-medium">
								<i class="fa fa-pencil me-1"></i><?= !empty($id) ? 'Mode Edit Expense' : 'Mode Expense Langsung' ?>
							</span>
						<?php endif; ?>
					</div>
					<h4 class="fw-bold mb-1 text-dark">
						<i class="fa <?= $is_report ? 'fa-ticket text-primary' : 'fa-cubes text-success' ?> me-2"></i>
						<?= ($stsview == 'approval') ? 'Approval Expense Report' : (($stsview == 'view') ? 'Detail Expense Report' : ($is_report ? 'Form Pertanggungjawaban Expense Report (Kasbon)' : 'Form Pengajuan Expense Langsung (Direct Expense)')) ?>
						<?php if (!empty($no_doc_val)) : ?>
							<span class="text-primary fs-5">#<?= $no_doc_val ?></span>
						<?php endif; ?>
					</h4>
					<small class="text-muted">
						Pelapor: <span class="fw-semibold text-dark"><?= $nama_pemohon ?></span>
					</small>
				</div>

				<div class="d-flex flex-wrap align-items-center gap-2">
					<a class="btn btn-outline-secondary btn-sm px-3" onclick="if (typeof list_view === 'function') { list_view(); } else { $('#form-data').hide().empty(); $('#card-list, .box').show(); } return false;">
						<i class="fa fa-arrow-left me-1"></i> Kembali ke List
					</a>

					<?php if (isset($data->status) && $data->status == 0 && $stsview == 'approval') : ?>
						<button type="button" class="btn btn-success btn-sm px-3" onclick="data_approve()">
							<i class="fa fa-check me-1"></i> Setujui (Approve)
						</button>
						<button type="button" class="btn btn-danger btn-sm px-3" onclick="data_reject()">
							<i class="fa fa-ban me-1"></i> Tolak (Reject)
						</button>
					<?php endif; ?>

					<?php if (!$is_view) : ?>
						<button type="button" class="btn btn-primary btn-sm px-4 shadow-sm stsview" onclick="$('#frm_data').submit();">
							<i class="fa fa-save me-1"></i> Simpan Expense
						</button>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php include __DIR__ . '/reject_card.php'; ?>

	<?= form_open_multipart($this->uri->segment(1) . '/save', array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'needs-validation')) ?>
	<input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
	<input type="hidden" id="pettycash" name="pettycash" value="<?php echo $pettycash; ?>">
	<input type="hidden" id="is_direct_expense" name="is_direct_expense" value="<?= $is_report ? '0' : '1' ?>">
	<input type="hidden" id="nama" name="nama" value="<?php echo $nama_pemohon; ?>">
	<input type="hidden" id="departement" name="departement" value="<?php echo (isset($data->departement) ? $data->departement : $dept); ?>">
	<input type="hidden" id="no_doc_kasbon" name="no_doc_kasbon" value="<?php echo $no_doc_kasbon_val; ?>">
	<input type="hidden" id="id_kasbon" name="id_kasbon" value="<?php echo $no_doc_kasbon_val; ?>">
	<input type="hidden" id="idKasbon" name="idKasbon" value="<?= !empty($kasbon_obj) ? $kasbon_obj->id : '' ?>">

	<!-- Banner Kasbon Terpilih (Jika ditarik dari Kasbon) -->
	<div id="card_kasbon_info" class="card border border-primary-subtle bg-primary-subtle bg-opacity-10 shadow-sm rounded-3 mb-4" style="<?= $is_report ? '' : 'display:none;' ?>">
		<div class="card-body p-3">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
				<div class="d-flex align-items-center gap-3">
					<div class="rounded-circle bg-primary text-white p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
						<i class="fa fa-ticket fs-5"></i>
					</div>
					<div>
						<span class="badge bg-primary px-2 py-1 mb-1">Kasbon Terkait</span>
						<h6 class="fw-bold mb-0 text-dark">
							No. Kasbon: <span id="label_no_kasbon" class="text-primary font-monospace"><?= $no_doc_kasbon_val ?: '-' ?></span> &bull; 
							Nominal Kasbon Diterima: <span class="text-primary font-monospace" id="label_jumlah_kasbon">Rp <?= number_format($total_kasbon_val, 0, ',', '.') ?></span>
						</h6>
						<small class="text-muted" id="label_keperluan_kasbon"><?= !empty($kasbon_obj) ? htmlspecialchars($kasbon_obj->keperluan) : '' ?></small>
					</div>
				</div>
				<?php if (!$is_view): ?>
					<div>
						<button type="button" class="btn btn-outline-danger btn-sm" onclick="clear_kasbon_selection()">
							<i class="fa fa-times me-1"></i> Lepas Kasbon
						</button>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Card Informasi Dokumen & Rekening -->
	<div class="row g-4 mb-4">
		<!-- Informasi Dokumen -->
		<div class="col-lg-6">
			<div class="card border-0 shadow-sm rounded-3 h-100">
				<div class="card-header bg-white border-bottom py-3">
					<h6 class="card-title fw-bold mb-0 text-dark">
						<i class="fa fa-file-text-o text-primary me-2"></i>Informasi Dokumen Expense
					</h6>
				</div>
				<div class="card-body p-4">
					<div class="row g-3">
						<div class="col-md-6">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">No. Dokumen</label>
							<input type="text" class="form-control form-control-sm bg-light" id="no_doc" name="no_doc" value="<?php echo $no_doc_val; ?>" placeholder="Otomatis (System)" readonly>
						</div>

						<div class="col-md-6">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">
								Tanggal Dokumen <span class="text-danger">*</span>
							</label>
							<div class="input-group input-group-sm">
								<span class="input-group-text bg-light"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control tanggal" id="tgl_doc" name="tgl_doc" value="<?php echo $tgl_doc; ?>" required placeholder="YYYY-MM-DD" <?= $is_view ? 'readonly' : '' ?>>
							</div>
						</div>

						<div class="col-12">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">
								Keterangan / Keperluan <span class="text-danger">*</span>
							</label>
							<textarea class="form-control form-control-sm" id="informasi" name="informasi" rows="3" required placeholder="Tuliskan keterangan keperluan pengeluaran expense..." <?= $is_view ? 'readonly' : '' ?>><?php echo $keterangan; ?></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Rekening Penerima -->
		<div class="col-lg-6">
			<div class="card border-0 shadow-sm rounded-3 h-100">
				<div class="card-header bg-white border-bottom py-3">
					<h6 class="card-title fw-bold mb-0 text-dark">
						<i class="fa fa-credit-card text-primary me-2"></i>Rekening Penerima / Transfer
					</h6>
				</div>
				<div class="card-body p-4">
					<div class="row g-3">
						<div class="col-12">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">Nama Bank Penerima</label>
							<div class="input-group input-group-sm">
								<span class="input-group-text bg-light"><i class="fa fa-university"></i></span>
								<input type="text" class="form-control" id="bank_id" name="bank_id" value="<?php echo $bank_id; ?>" placeholder="Contoh: BCA, Mandiri, BRI" <?= $is_view ? 'readonly' : '' ?>>
							</div>
						</div>

						<div class="col-md-6">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">Nomor Rekening</label>
							<div class="input-group input-group-sm">
								<span class="input-group-text bg-light"><i class="fa fa-hashtag"></i></span>
								<input type="text" class="form-control font-monospace" id="accnumber" name="accnumber" value="<?php echo $accnumber; ?>" placeholder="Nomor Rekening" <?= $is_view ? 'readonly' : '' ?>>
							</div>
						</div>

						<div class="col-md-6">
							<label class="form-label text-muted small fw-semibold text-uppercase mb-1">Atas Nama Rekening</label>
							<div class="input-group input-group-sm">
								<span class="input-group-text bg-light"><i class="fa fa-user"></i></span>
								<input type="text" class="form-control" id="accname" name="accname" value="<?php echo $accname; ?>" placeholder="Nama Pemilik Rekening" <?= $is_view ? 'readonly' : '' ?>>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Card Rincian Pengeluaran Barang & Jasa -->
	<div class="card border-0 shadow-sm rounded-3 mb-4">
		<div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
			<div>
				<h6 class="card-title fw-bold mb-0 text-dark">
					<i class="fa fa-list-alt text-primary me-2"></i>Rincian Pengeluaran Barang & Jasa
				</h6>
				<small class="text-muted">Item pengeluaran realisasi langsung beserta bukti bon/struk</small>
			</div>
			<?php if (!$is_view) : ?>
				<div class="stsview d-flex align-items-center gap-2">
					<button type="button" class="btn btn-success btn-sm shadow-sm px-3" onclick="add_detail()" id="add-material" title="Tambah Baris Pengeluaran Baru">
						<i class="fa fa-plus me-1"></i> Tambah Baris Pengeluaran
					</button>
				</div>
			<?php endif; ?>
		</div>

		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-bordered table-hover align-middle mb-0" id="tableDetailExpense" style="min-width: 1100px;">
					<thead class="table-light text-center">
						<tr>
							<th width="35" class="text-center">#</th>
							<th width="85" class="text-center">Tipe</th>
							<th width="220">Akun COA / Jenis</th>
							<th width="110" class="text-center">Tanggal</th>
							<th width="200">Barang / Jasa</th>
							<th width="180">Spesifikasi</th>
							<th width="70" class="text-center">Qty</th>
							<th width="120" class="text-end">Harga Satuan</th>
							<th width="140" class="text-end">Total Nominal</th>
							<th width="170" class="text-center">Bon / Bukti</th>
							<?php if (!$is_view) : ?>
								<th width="50" class="text-center">Aksi</th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody id="detail_body">
						<?php
						$idd = 1;
						$total_expense = 0;
						if (!empty($data_detail)) {
							foreach ($data_detail as $record) {
								$row_expense_val = floatval($record->expense > 0 ? $record->expense : $record->total_harga);
								$total_expense += $row_expense_val;
								$row_files = isset($detail_files[$record->id]) ? $detail_files[$record->id] : [];
						?>
								<tr id='tr1_<?= $idd ?>' class='delAll'>
									<td class="text-center text-muted fw-medium">
										<input type='hidden' name='id_kasbon[]' id='id_kasbon_<?= $idd ?>' value='<?= $record->id_kasbon ?>'>
										<input type="hidden" name="filename[]" id="filename_<?= $idd ?>" value="<?= $record->doc_file; ?>">
										<input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $idd; ?>" class="dtlloop">
										<input type="hidden" name="id_detail[]" id="id_detail_<?= $idd ?>" value="<?= $record->id; ?>" class="dtlloop">
										<?= $idd ?>
									</td>
									<td class="text-center">
										<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
											<i class="fa fa-money me-1"></i>Realisasi
										</span>
									</td>
									<td>
										<?php if ($is_view) : ?>
											<input type="hidden" name="coa[]" id="coa<?= $idd ?>" value="<?= $record->coa ?>">
											<span class="small fw-semibold"><?= isset($option_coa[$record->coa]) ? $option_coa[$record->coa] : $record->coa ?></span>
										<?php else : ?>
											<?= form_dropdown('coa[]', $option_coa, (isset($record->coa) ? $record->coa : ''), array('id' => 'coa' . $idd, 'required' => 'required', 'class' => 'form-select form-select-sm select2')); ?>
										<?php endif; ?>
									</td>
									<td>
										<input type="text" class="form-control form-control-sm tanggal" name="tanggal[]" id="tanggal<?= $idd; ?>" value="<?= $record->tanggal; ?>" <?= $is_view ? 'readonly' : '' ?>>
									</td>
									<td>
										<textarea class="form-control form-control-sm" name="deskripsi[]" id="deskripsi_<?= $idd; ?>" rows="2" <?= $is_view ? 'readonly' : '' ?>><?= $record->deskripsi; ?></textarea>
									</td>
									<td>
										<textarea class="form-control form-control-sm" name="keterangan[]" id="keterangan_<?= $idd; ?>" rows="2" <?= $is_view ? 'readonly' : '' ?>><?= $record->keterangan; ?></textarea>
									</td>
									<td>
										<input type="text" class="form-control form-control-sm divide text-center" name="qty[]" id="qty_<?= $idd; ?>" value="<?= $record->qty; ?>" onblur="cektotal(<?= $idd; ?>)" <?= $is_view ? 'readonly' : '' ?>>
									</td>
									<td>
										<input type="text" class="form-control form-control-sm divide text-end" name="harga[]" id="harga_<?= $idd; ?>" value="<?= $record->harga; ?>" onblur="cektotal(<?= $idd; ?>)" <?= $is_view ? 'readonly' : '' ?>>
									</td>
									<td>
										<input type="text" class="form-control form-control-sm divide subtotal text-end fw-bold text-success bg-light" name="expense[]" id="expense_<?= $idd; ?>" value="<?= $row_expense_val ?>" tabindex="-1" readonly>
										<input type="hidden" class="subkasbon" name="kasbon[]" id="kasbon_<?= $idd; ?>" value="0">
									</td>
									<td class="text-center">
										<div class="file-upload-cell-<?= $idd ?>">
											<?php if (!$is_view) : ?>
												<label class="btn btn-outline-primary btn-sm stsview py-1 px-2" style="cursor:pointer;" title="Pilih File Bon/Bukti">
													<i class="fa fa-folder-open me-1"></i> Pilih File
													<input type="file" id="temp_file_<?= $idd ?>" class="temp-detail-file-picker" data-row="<?= $idd ?>" style="display:none;" accept=".jpg,.jpeg,.png,.pdf" multiple>
												</label>
												<input type="file" name="doc_files_<?= $idd ?>[]" id="doc_files_<?= $idd ?>" multiple style="display:none;">
											<?php endif; ?>
											<div id="new_files_list_<?= $idd ?>" class="d-flex flex-column gap-1 mt-1"></div>

											<?php if (!empty($row_files)): ?>
												<div class="existing-files-row-<?= $idd ?> d-flex flex-column gap-1 mt-1">
													<input type="hidden" name="has_existing_files_<?= $idd ?>" value="1">
													<?php foreach ($row_files as $rf):
														$furl = base_url('assets/expense/' . $rf->doc_file);
														$is_fpdf = (stripos($rf->doc_file, '.pdf') !== false);
													?>
														<span class="badge bg-light text-dark border file-badge-item d-inline-flex align-items-center justify-content-between p-1 px-2">
															<a href="<?= $furl ?>" target="_blank" class="text-decoration-none text-truncate" style="max-width:110px;" title="<?= htmlspecialchars($rf->doc_file) ?>">
																<i class="fa <?= $is_fpdf ? 'fa-file-pdf-o text-danger' : 'fa-file-image-o text-info' ?> me-1"></i> <?= htmlspecialchars($rf->doc_file) ?>
															</a>
															<input type="hidden" name="existing_files_<?= $idd ?>[]" value="<?= htmlspecialchars($rf->id) ?>">
															<?php if (!$is_view): ?>
																<button type="button" class="btn btn-link btn-sm text-danger p-0 ms-1 remove-existing-detail-file" title="Hapus file ini">
																	<i class="fa fa-times"></i>
																</button>
															<?php endif; ?>
														</span>
													<?php endforeach; ?>
												</div>
											<?php endif; ?>
										</div>
									</td>
									<?php if (!$is_view) : ?>
										<td class="text-center">
											<button type='button' class='btn btn-outline-danger btn-sm' onClick='delDetail(<?= $idd ?>)' title='Hapus'><i class='fa fa-trash'></i></button>
										</td>
									<?php endif; ?>
								</tr>
						<?php
								$idd++;
							}
						} elseif (!empty($pr_detail)) {
							// Otomatis tarik item-item dari PR Non PO Kasbon
							foreach ($pr_detail as $record_pr) {
								$row_pr_total = floatval($record_pr['total_harga']);
								$total_expense += $row_pr_total;
								$pr_deskripsi = (!empty($record_pr['no_pr']) ? '[' . $record_pr['no_pr'] . '] ' : '') . $record_pr['nm_material'];
						?>
								<tr id='tr1_<?= $idd ?>' class='delAll'>
									<td class="text-center text-muted fw-medium">
										<input type='hidden' name='id_kasbon[]' id='id_kasbon_<?= $idd ?>' value='<?= $no_doc_kasbon_val ?>'>
										<input type="hidden" name="filename[]" id="filename_<?= $idd ?>" value="">
										<input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $idd; ?>" class="dtlloop">
										<input type="hidden" name="id_detail[]" id="id_detail_<?= $idd ?>" value="<?= $idd; ?>" class="dtlloop">
										<?= $idd ?>
									</td>
									<td class="text-center">
										<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
											<i class="fa fa-money me-1"></i>Realisasi
										</span>
									</td>
									<td>
										<?= form_dropdown('coa[]', $option_coa, (!empty($kasbon_obj->no_coa) ? $kasbon_obj->no_coa : ''), array('id' => 'coa' . $idd, 'required' => 'required', 'class' => 'form-select form-select-sm select2')); ?>
									</td>
									<td>
										<input type="text" class="form-control form-control-sm tanggal" name="tanggal[]" id="tanggal<?= $idd; ?>" value="<?= date('Y-m-d') ?>">
									</td>
									<td>
										<textarea class="form-control form-control-sm" name="deskripsi[]" id="deskripsi_<?= $idd; ?>" rows="2"><?= htmlspecialchars($pr_deskripsi) ?></textarea>
										<input type='hidden' name='id_expense_detail[]' id='id_expense_detail_<?= $idd ?>' value='<?= $record_pr['id_detail'] ?>' />
									</td>
									<td>
										<textarea class="form-control form-control-sm" name="keterangan[]" id="keterangan_<?= $idd; ?>" rows="2" placeholder="Spesifikasi..."><?= htmlspecialchars($record_pr['satuan'] ?: 'Pcs') ?></textarea>
									</td>
									<td>
										<input type="text" class="form-control form-control-sm divide text-center" name="qty[]" id="qty_<?= $idd; ?>" value="<?= (float)$record_pr['qty'] ?>" onblur="cektotal(<?= $idd; ?>)">
									</td>
									<td>
										<input type="text" class="form-control form-control-sm divide text-end" name="harga[]" id="harga_<?= $idd; ?>" value="<?= (float)$record_pr['harga'] ?>" onblur="cektotal(<?= $idd; ?>)">
									</td>
									<td>
										<input type="text" class="form-control form-control-sm divide subtotal text-end fw-bold text-success bg-light" name="expense[]" id="expense_<?= $idd; ?>" value="<?= $row_pr_total ?>" tabindex="-1" readonly>
										<input type="hidden" class="subkasbon" name="kasbon[]" id="kasbon_<?= $idd; ?>" value="0">
									</td>
									<td class="text-center">
										<div class="file-upload-cell-<?= $idd ?>">
											<label class="btn btn-outline-primary btn-sm stsview py-1 px-2" style="cursor:pointer;" title="Pilih File Bon/Bukti">
												<i class="fa fa-folder-open me-1"></i> Pilih File
												<input type="file" id="temp_file_<?= $idd ?>" class="temp-detail-file-picker" data-row="<?= $idd ?>" style="display:none;" accept=".jpg,.jpeg,.png,.pdf" multiple>
											</label>
											<input type="file" name="doc_files_<?= $idd ?>[]" id="doc_files_<?= $idd ?>" multiple style="display:none;">
											<div id="new_files_list_<?= $idd ?>" class="d-flex flex-column gap-1 mt-1"></div>
										</div>
									</td>
									<td class="text-center">
										<button type='button' class='btn btn-outline-danger btn-sm' onClick='delDetail(<?= $idd ?>)' title='Hapus'><i class='fa fa-trash'></i></button>
									</td>
								</tr>
						<?php
								$idd++;
							}
						} elseif (!empty($kasbon_obj)) {
							// Kasbon tanpa PR (Manual cash advance) -> Buat 1 baris awal
							$row_kb_total = floatval($kasbon_obj->jumlah_kasbon);
							$total_expense = $row_kb_total;
						?>
							<tr id='tr1_1' class='delAll'>
								<td class="text-center text-muted fw-medium">
									<input type='hidden' name='id_kasbon[]' id='id_kasbon_1' value='<?= $no_doc_kasbon_val ?>'>
									<input type="hidden" name="filename[]" id="filename_1" value="">
									<input type="hidden" name="detail_id[]" id="raw_id_1" value="1" class="dtlloop">
									<input type="hidden" name="id_detail[]" id="id_detail_1" value="1" class="dtlloop">
									1
								</td>
								<td class="text-center">
									<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
										<i class="fa fa-money me-1"></i>Realisasi
									</span>
								</td>
								<td>
									<?= form_dropdown('coa[]', $option_coa, (!empty($kasbon_obj->no_coa) ? $kasbon_obj->no_coa : ''), array('id' => 'coa1', 'required' => 'required', 'class' => 'form-select form-select-sm select2')); ?>
								</td>
								<td>
									<input type="text" class="form-control form-control-sm tanggal" name="tanggal[]" id="tanggal1" value="<?= date('Y-m-d') ?>">
								</td>
								<td>
									<textarea class="form-control form-control-sm" name="deskripsi[]" id="deskripsi_1" rows="2"><?= htmlspecialchars($kasbon_obj->keperluan) ?></textarea>
									<input type='hidden' name='id_expense_detail[]' id='id_expense_detail_1' value='' />
								</td>
								<td>
									<textarea class="form-control form-control-sm" name="keterangan[]" id="keterangan_1" rows="2" placeholder="Spesifikasi / catatan..."></textarea>
								</td>
								<td>
									<input type="text" class="form-control form-control-sm divide text-center" name="qty[]" id="qty_1" value="1" onblur="cektotal(1)">
								</td>
								<td>
									<input type="text" class="form-control form-control-sm divide text-end" name="harga[]" id="harga_1" value="<?= $row_kb_total ?>" onblur="cektotal(1)">
								</td>
								<td>
									<input type="text" class="form-control form-control-sm divide subtotal text-end fw-bold text-success bg-light" name="expense[]" id="expense_1" value="<?= $row_kb_total ?>" tabindex="-1" readonly>
									<input type="hidden" class="subkasbon" name="kasbon[]" id="kasbon_1" value="0">
								</td>
								<td class="text-center">
									<div class="file-upload-cell-1">
										<label class="btn btn-outline-primary btn-sm stsview py-1 px-2" style="cursor:pointer;" title="Pilih File Bon/Bukti">
											<i class="fa fa-folder-open me-1"></i> Pilih File
											<input type="file" id="temp_file_1" class="temp-detail-file-picker" data-row="1" style="display:none;" accept=".jpg,.jpeg,.png,.pdf" multiple>
										</label>
										<input type="file" name="doc_files_1[]" id="doc_files_1" multiple style="display:none;">
										<div id="new_files_list_1" class="d-flex flex-column gap-1 mt-1"></div>
									</div>
								</td>
								<td class="text-center">
									<button type='button' class='btn btn-outline-danger btn-sm' onClick='delDetail(1)' title='Hapus'><i class='fa fa-trash'></i></button>
								</td>
							</tr>
						<?php
							$idd = 2;
						}
						?>
					</tbody>
					<tfoot class="table-light">
						<!-- Baris Total Pengeluaran -->
						<tr>
							<td colspan="8" class="text-end fw-bold py-2 text-uppercase text-secondary">TOTAL PENGELUARAN (REALISASI) :</td>
							<td class="text-end py-2">
								<input type="text" class="form-control form-control-sm divide text-end fw-bold font-monospace text-success fs-6" id="total_expense" name="total_expense" value="<?= $total_expense ?>" placeholder="0" tabindex="-1" readonly>
							</td>
							<td colspan="<?= !$is_view ? '2' : '1' ?>"></td>
						</tr>

						<!-- Baris Kasbon Diterima (Hanya jika mode report / ada kasbon) -->
						<tr id="row_total_kasbon" style="<?= ($is_report || $total_kasbon_val > 0) ? '' : 'display:none;' ?>">
							<td colspan="8" class="text-end fw-bold py-2 text-uppercase text-primary">KASBON YANG DITERIMA :</td>
							<td class="text-end py-2">
								<input type="text" class="form-control form-control-sm divide text-end fw-bold font-monospace text-primary fs-6" id="total_kasbon" name="total_kasbon" value="<?= $total_kasbon_val ?>" placeholder="0" tabindex="-1" readonly>
							</td>
							<td colspan="<?= !$is_view ? '2' : '1' ?>"></td>
						</tr>

						<!-- Baris Selisih Kurang / Lebih Bayar -->
						<tr id="row_grand_total" style="<?= ($is_report || $total_kasbon_val > 0) ? '' : 'display:none;' ?>">
							<td colspan="8" class="text-end fw-bolder py-2 text-uppercase text-dark fs-6">
								<span id="label_selisih">SELISIH (KURANG / LEBIH) :</span>
							</td>
							<td class="text-end py-2">
								<input type="text" class="form-control form-control-sm divide text-end fw-bolder font-monospace fs-6" id="grand_total" name="grand_total" value="<?= $total_expense - $total_kasbon_val ?>" placeholder="0" tabindex="-1" readonly>
							</td>
							<td colspan="<?= !$is_view ? '2' : '1' ?>"></td>
						</tr>
					</tfoot>
				</table>
			</div>

			<!-- Box Pengembalian Sisa Kasbon (Jika Lebih Bayar / Uang kasbon bersisa) -->
			<div class="p-3 bg-light border-top" id="box_pengembalian" style="display:none;">
				<div class="card border border-warning-subtle rounded-3 p-3 bg-white">
					<h6 class="fw-bold text-warning-emphasis mb-2">
						<i class="fa fa-info-circle me-1"></i> Pengembalian Sisa Kasbon ke Perusahaan
					</h6>
					<p class="text-muted small mb-3">Nominal realisasi pengeluaran lebih kecil dari kasbon yang diterima. Mohon pilih metode pengembalian sisa uang ke kantor:</p>
					<div class="row g-3">
						<div class="col-md-4">
							<label class="form-label text-muted small fw-semibold text-uppercase">Metode Pengembalian</label>
							<div class="d-flex gap-3 mt-1">
								<div class="form-check">
									<input class="form-check-input" type="radio" name="pengembalian" id="pengembalian_cash" value="1" <?= (isset($data->tipe_pengembalian) && $data->tipe_pengembalian == 1) ? 'checked' : 'checked' ?> <?= $is_view ? 'disabled' : '' ?>>
									<label class="form-check-label" for="pengembalian_cash">Cash (Tunai)</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="pengembalian" id="pengembalian_transfer" value="2" <?= (isset($data->tipe_pengembalian) && $data->tipe_pengembalian == 2) ? 'checked' : '' ?> <?= $is_view ? 'disabled' : '' ?>>
									<label class="form-check-label" for="pengembalian_transfer">Transfer Bank</label>
								</div>
							</div>
						</div>

						<div class="col-md-8">
							<label class="form-label text-muted small fw-semibold text-uppercase">Bukti Transfer Pengembalian</label>
							<?php if (!$is_view) : ?>
								<input type="file" name="bukti_pengembalian[]" class="form-control form-control-sm" multiple>
								<small class="text-muted fst-italic">Upload bukti transfer pengembalian sisa uang (jika via transfer)</small>
							<?php endif; ?>

							<?php if (isset($data->bukti_pengembalian) && !empty($data->bukti_pengembalian)) : ?>
								<div class="mt-2">
									<a href="<?= base_url($data->bukti_pengembalian) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
										<i class="fa fa-paperclip me-1"></i> Lihat Bukti Pengembalian
									</a>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- SECTION TABLE ALOKASI AKUN PEMBEBANAN (COA) -->
	<div id="section_jurnal" class="card border-0 shadow-sm rounded-3 mb-4">
		<div class="card-header bg-white border-bottom py-3">
			<h6 class="card-title fw-bold mb-0 text-dark">
				<i class="fa fa-list-alt text-primary me-2"></i>Daftar Alokasi Akun Pembebanan (COA)
			</h6>
		</div>
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-bordered table-striped align-middle mb-0" width="100%">
					<thead class="table-light text-center">
						<tr>
							<th width="120" class="text-center">Tanggal</th>
							<th width="120" class="text-center">COA</th>
							<th width="150" class="text-center">Nama Company</th>
							<th width="200">Nama Account</th>
							<th>Deskripsi / Keterangan</th>
							<th width="150" class="text-end">Nominal Alokasi (Rp)</th>
						</tr>
					</thead>
					<tbody class="tbody_jurnal">
					</tbody>
					<tfoot class="table-light fw-bold">
						<tr>
							<td colspan="5" class="text-end py-2 text-uppercase text-secondary">TOTAL ALOKASI BIAYA :</td>
							<td class="text-end py-2 ttl_debit text-success font-monospace fs-6">0</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>

	<!-- FOOTER ACTION BAR -->
	<div class="card border-0 shadow-sm rounded-3 mb-4">
		<div class="card-body p-3 d-flex flex-wrap justify-content-end align-items-center gap-2">
			<a class="btn btn-outline-secondary btn-sm px-3" onclick="if (typeof list_view === 'function') { list_view(); } else { $('#form-data').hide().empty(); $('#card-list, .box').show(); } return false;">
				<i class="fa fa-reply me-1"></i> Kembali / Batal
			</a>

			<?php if (isset($data->status) && $data->status == 0 && $stsview == 'approval') : ?>
				<button type="button" class="btn btn-success btn-sm px-3" onclick="data_approve()">
					<i class="fa fa-check me-1"></i> Setujui (Approve)
				</button>
				<button type="button" class="btn btn-danger btn-sm px-3" onclick="data_reject()">
					<i class="fa fa-ban me-1"></i> Tolak (Reject)
				</button>
			<?php endif; ?>

			<?php if (!$is_view) : ?>
				<button type="submit" name="save" class="btn btn-primary btn-sm px-4 shadow-sm stsview" id="submit">
					<i class="fa fa-save me-1"></i> Simpan Expense
				</button>
			<?php endif; ?>
		</div>
	</div>

	<?= form_close() ?>
</div>


</div>

<!-- Plugins JS -->
<script src="<?= base_url('assets/plugins/datepicker/bootstrap-datepicker.js') ?>"></script>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>

<script type="text/javascript">
	var stsview = "<?= $stsview ?>";
	var url_set_jurnal = siteurl + 'expense/set_jurnal_expense';
	var datacoa_global = <?= json_encode($datacoa) ?>;

	$(document).ready(function() {
		var companyName = (document.title.indexOf('|') !== -1) ? document.title.split('|')[0].trim() : 'HFG';
		document.title = companyName + ' | Expense';
		$('.content-header h1').html('<i class="fa fa-cubes"></i> Expense');

		$('.select2').select2({ width: '100%' });
		$(".divide").divide();
		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		}).on('changeDate change', function() {
			set_jurnal();
		});

		$(document).on('change changeDate', '#tgl_doc, input[name="tanggal[]"]', function() {
			set_jurnal();
		});

		$(document).on('change', 'select[name="coa[]"]', function() {
			set_jurnal();
		});

		$(document).on('blur change', 'textarea[name="deskripsi[]"]', function() {
			set_jurnal();
		});

		if (stsview == 'view' || stsview == 'approval') {
			$(".stsview").hide();
			$("#frm_data input:not([type=hidden]), #frm_data textarea, #frm_data select").prop("disabled", true);
		}

		if ($("#detail_body tr").length === 0) {
			add_detail();
		} else {
			cektotal();
		}
	});

	function add_detail() {
		var nomor = $("#detail_body tr").length + 1;
		var kasbon_doc = $("#no_doc_kasbon").val() || '';
		var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
		Rows += "<td class='text-center text-muted fw-medium'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value='" + kasbon_doc + "'>";
		Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
		Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
		Rows += nomor + "</td>";
		Rows += "<td class='text-center'><span class='badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill'><i class='fa fa-money me-1'></i>Realisasi</span></td>";
		Rows += "<td>";
		Rows += "<select name='coa[]' id='coa_" + nomor + "' required='required' class='form-select form-select-sm select2'>" + datacoa_global + "</select>";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control form-control-sm tanggal' placeholder='YYYY-MM-DD' name='tanggal[]' id='tanggal_" + nomor + "' value='<?= date("Y-m-d") ?>' />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<textarea class='form-control form-control-sm' placeholder='Nama barang/jasa...' name='deskripsi[]' id='deskripsi_" + nomor + "' rows='2'></textarea>";
		Rows += "<input type='hidden' name='id_expense_detail[]' id='id_expense_detail_" + nomor + "' value='' />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<textarea class='form-control form-control-sm' placeholder='Rincian / spesifikasi...' name='keterangan[]' id='keterangan_" + nomor + "' rows='2'></textarea>";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control form-control-sm divide text-center' name='qty[]' id='qty_" + nomor + "' value='1' onblur='cektotal(" + nomor + ")'/>";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control form-control-sm divide text-end' name='harga[]' id='harga_" + nomor + "' value='0' onblur='cektotal(" + nomor + ")' />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control form-control-sm divide subtotal text-end fw-bold text-success bg-light' name='expense[]' id='expense_" + nomor + "' value='0' tabindex='-1' readonly />";
		Rows += "<input type='hidden' class='subkasbon' name='kasbon[]' id='kasbon_" + nomor + "' value='0' />";
		Rows += "</td>";
		Rows += "<td class='text-center'>";
		Rows += "<div class='file-upload-cell-" + nomor + "'>";
		Rows += "<label class='btn btn-outline-primary btn-sm stsview py-1 px-2' style='cursor:pointer;' title='Pilih File Bon/Bukti'>";
		Rows += "<i class='fa fa-folder-open me-1'></i> Pilih File";
		Rows += "<input type='file' id='temp_file_" + nomor + "' class='temp-detail-file-picker' data-row='" + nomor + "' style='display:none;' accept='.jpg,.jpeg,.png,.pdf' multiple>";
		Rows += "</label>";
		Rows += "<input type='file' name='doc_files_" + nomor + "[]' id='doc_files_" + nomor + "' multiple style='display:none;'>";
		Rows += "<div id='new_files_list_" + nomor + "' class='d-flex flex-column gap-1 mt-1'></div>";
		Rows += "</div>";
		Rows += "</td>";
		Rows += "<td class='text-center'>";
		Rows += "<button type='button' class='btn btn-outline-danger btn-sm' onClick='delDetail(" + nomor + ")' title='Hapus'><i class='fa fa-trash'></i></button>";
		Rows += "</td>";
		Rows += "</tr>";

		$('#detail_body').append(Rows);
		$("#tanggal_" + nomor).focus();
		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		}).on('changeDate change', function() {
			set_jurnal();
		});
		$('.select2').select2({ width: '100%' });
		$(".divide").divide();
		cektotal();
	}

	function delDetail(row) {
		$('#tr1_' + row).remove();
		$('#detail_body tr').each(function(index) {
			$(this).find('td:first').contents().filter(function() {
				return this.nodeType === 3;
			}).remove();
			$(this).find('td:first').append((index + 1));
		});
		cektotal();
	}

	function cektotal(id) {
		if (id !== undefined) {
			var qty = parseFloat(String($("#qty_" + id).val() || 0).replace(/,/g, '')) || 0;
			var harga = parseFloat(String($("#harga_" + id).val() || 0).replace(/,/g, '')) || 0;
			var total = qty * harga;
			$("#expense_" + id).val(total);
		}

		var total_expense = 0;
		$(".subtotal").each(function() {
			total_expense += parseFloat(String($(this).val() || 0).replace(/,/g, '')) || 0;
		});
		$("#total_expense").val(total_expense);

		var total_kasbon = parseFloat(String($("#total_kasbon").val() || 0).replace(/,/g, '')) || 0;
		var selisih = total_expense - total_kasbon;
		$("#grand_total").val(selisih);

		// Atur warna dan label selisih
		if (total_kasbon > 0) {
			$("#row_total_kasbon").show();
			$("#row_grand_total").show();
			if (selisih > 0) {
				$("#label_selisih").html("SELISIH (KURANG BAYAR / KLAIM) :");
				$("#grand_total").removeClass('text-success text-warning').addClass('text-danger');
				$("#box_pengembalian").hide();
			} else if (selisih < 0) {
				$("#label_selisih").html("SELISIH (LEBIH BAYAR / SISA KASBON) :");
				$("#grand_total").removeClass('text-danger text-success').addClass('text-warning');
				$("#box_pengembalian").show();
			} else {
				$("#label_selisih").html("SELISIH (PAS / BALANCE) :");
				$("#grand_total").removeClass('text-danger text-warning').addClass('text-success');
				$("#box_pengembalian").hide();
			}
		} else {
			$("#box_pengembalian").hide();
		}

		$(".divide").divide();
		set_jurnal();
	}

	function set_jurnal() {
		var $form = $('#frm_data');
		if ($form.length === 0) return;

		var $disabled = $form.find(':disabled');
		$disabled.prop('disabled', false);
		var formdata = new FormData($form[0]);
		$disabled.prop('disabled', true);

		$.ajax({
			url: url_set_jurnal,
			dataType: "json",
			type: 'POST',
			data: formdata,
			processData: false,
			contentType: false,
			success: function(res) {
				if (res && res.status === 1) {
					$('.tbody_jurnal').html(res.hasil);
					$('.ttl_debit').text(res.ttl_debit);
				}
			},
			error: function(xhr, status, error) {
				console.error("Gagal generate preview jurnal: " + error);
			}
		});
	}

	// ==========================================
	// MODAL DAN PENARIKAN DATA KASBON DI FORM
	// ==========================================
	

	function select_kasbon_direct(no_doc_kasbon, id_kasbon) {
		if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
			var modalEl = document.getElementById('modalKasbonForm');
			var modalInst = bootstrap.Modal.getInstance(modalEl);
			if (modalInst) modalInst.hide();
		}
		$('#modalKasbonForm').modal('hide');

		// Ambil data detail kasbon + PR Non PO
		$.ajax({
			url: siteurl + 'expense/get_kasbon_detail/' + encodeURIComponent(no_doc_kasbon) + '/' + (id_kasbon || ''),
			type: "GET",
			dataType: "json",
			success: function(res) {
				if (res && res.status === 1) {
					var kb = res.kasbon;
					var prs = res.pr_detail || [];

					// 1. Update Header Form
					$("#no_doc_kasbon").val(kb.no_doc);
					$("#id_kasbon").val(kb.no_doc);
					$("#idKasbon").val(kb.id);
					$("#is_direct_expense").val("0");
					if (kb.bank_id) $("#bank_id").val(kb.bank_id);
					if (kb.accnumber) $("#accnumber").val(kb.accnumber);
					if (kb.accname) $("#accname").val(kb.accname);
					if (kb.nama) $("#nama").val(kb.nama);
					if (kb.departement) $("#departement").val(kb.departement);
					if (kb.keperluan && $("#informasi").val() === "") {
						$("#informasi").val(kb.keperluan);
					}

					// 2. Tampilkan Banner Kasbon
					$("#label_no_kasbon").text(kb.no_doc);
					$("#label_jumlah_kasbon").text("Rp " + Number(kb.jumlah_kasbon).toLocaleString('id-ID'));
					$("#label_keperluan_kasbon").text(kb.keperluan || '');
					$("#card_kasbon_info").slideDown();

					// 3. Set Total Kasbon
					$("#total_kasbon").val(kb.jumlah_kasbon);

					// 4. Tarik Rincian Barang
					$('#detail_body').empty();
					var nomor = 1;
					if (prs.length > 0) {
						for (var j = 0; j < prs.length; j++) {
							var p = prs[j];
							var prTotal = parseFloat(p.total_harga) || 0;
							var prDeskripsi = (p.no_pr ? '[' + p.no_pr + '] ' : '') + p.nm_material;

							var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
							Rows += "<td class='text-center text-muted fw-medium'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value='" + kb.no_doc + "'>";
							Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
							Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
							Rows += nomor + "</td>";
							Rows += "<td class='text-center'><span class='badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill'><i class='fa fa-money me-1'></i>Realisasi</span></td>";
							Rows += "<td>";
							Rows += "<select name='coa[]' id='coa_" + nomor + "' required='required' class='form-select form-select-sm select2'>" + datacoa_global + "</select>";
							Rows += "</td>";
							Rows += "<td>";
							Rows += "<input type='text' class='form-control form-control-sm tanggal' placeholder='YYYY-MM-DD' name='tanggal[]' id='tanggal_" + nomor + "' value='<?= date("Y-m-d") ?>' />";
							Rows += "</td>";
							Rows += "<td>";
							Rows += "<textarea class='form-control form-control-sm' name='deskripsi[]' id='deskripsi_" + nomor + "' rows='2'>" + prDeskripsi + "</textarea>";
							Rows += "<input type='hidden' name='id_expense_detail[]' id='id_expense_detail_" + nomor + "' value='" + p.id_detail + "' />";
							Rows += "</td>";
							Rows += "<td>";
							Rows += "<textarea class='form-control form-control-sm' name='keterangan[]' id='keterangan_" + nomor + "' rows='2' placeholder='Spesifikasi...'>" + (p.satuan || 'Pcs') + "</textarea>";
							Rows += "</td>";
							Rows += "<td>";
							Rows += "<input type='text' class='form-control form-control-sm divide text-center' name='qty[]' id='qty_" + nomor + "' value='" + p.qty + "' onblur='cektotal(" + nomor + ")'/>";
							Rows += "</td>";
							Rows += "<td>";
							Rows += "<input type='text' class='form-control form-control-sm divide text-end' name='harga[]' id='harga_" + nomor + "' value='" + p.harga + "' onblur='cektotal(" + nomor + ")' />";
							Rows += "</td>";
							Rows += "<td>";
							Rows += "<input type='text' class='form-control form-control-sm divide subtotal text-end fw-bold text-success bg-light' name='expense[]' id='expense_" + nomor + "' value='" + prTotal + "' tabindex='-1' readonly />";
							Rows += "<input type='hidden' class='subkasbon' name='kasbon[]' id='kasbon_" + nomor + "' value='0' />";
							Rows += "</td>";
							Rows += "<td class='text-center'>";
							Rows += "<div class='file-upload-cell-" + nomor + "'>";
							Rows += "<label class='btn btn-outline-primary btn-sm stsview py-1 px-2' style='cursor:pointer;' title='Pilih File Bon/Bukti'>";
							Rows += "<i class='fa fa-folder-open me-1'></i> Pilih File";
							Rows += "<input type='file' id='temp_file_" + nomor + "' class='temp-detail-file-picker' data-row='" + nomor + "' style='display:none;' accept='.jpg,.jpeg,.png,.pdf' multiple>";
							Rows += "</label>";
							Rows += "<input type='file' name='doc_files_" + nomor + "[]' id='doc_files_" + nomor + "' multiple style='display:none;'>";
							Rows += "<div id='new_files_list_" + nomor + "' class='d-flex flex-column gap-1 mt-1'></div>";
							Rows += "</div>";
							Rows += "</td>";
							Rows += "<td class='text-center'>";
							Rows += "<button type='button' class='btn btn-outline-danger btn-sm' onClick='delDetail(" + nomor + ")' title='Hapus'><i class='fa fa-trash'></i></button>";
							Rows += "</td>";
							Rows += "</tr>";

							$('#detail_body').append(Rows);
							if (kb.no_coa) {
								$("#coa_" + nomor).val(kb.no_coa);
							}
							nomor++;
						}
					} else {
						// Kasbon manual tanpa PR -> Buat 1 baris awal
						var Rows = "<tr id='tr1_1' class='delAll'>";
						Rows += "<td class='text-center text-muted fw-medium'><input type='hidden' name='id_kasbon[]' id='id_kasbon_1' value='" + kb.no_doc + "'>";
						Rows += "<input type='hidden' name='detail_id[]' id='raw_id_1' value='1' class='dtlloop'>";
						Rows += "<input type='hidden' name='id_detail[]' id='id_detail_1' value='1' class='dtlloop'>";
						Rows += "1</td>";
						Rows += "<td class='text-center'><span class='badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill'><i class='fa fa-money me-1'></i>Realisasi</span></td>";
						Rows += "<td>";
						Rows += "<select name='coa[]' id='coa_1' required='required' class='form-select form-select-sm select2'>" + datacoa_global + "</select>";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control form-control-sm tanggal' placeholder='YYYY-MM-DD' name='tanggal[]' id='tanggal_1' value='<?= date("Y-m-d") ?>' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<textarea class='form-control form-control-sm' name='deskripsi[]' id='deskripsi_1' rows='2'>" + (kb.keperluan || '') + "</textarea>";
						Rows += "<input type='hidden' name='id_expense_detail[]' id='id_expense_detail_1' value='' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<textarea class='form-control form-control-sm' name='keterangan[]' id='keterangan_1' rows='2' placeholder='Spesifikasi...'>Pengeluaran Kasbon</textarea>";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control form-control-sm divide text-center' name='qty[]' id='qty_1' value='1' onblur='cektotal(1)'/>";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control form-control-sm divide text-end' name='harga[]' id='harga_1' value='" + kb.jumlah_kasbon + "' onblur='cektotal(1)' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control form-control-sm divide subtotal text-end fw-bold text-success bg-light' name='expense[]' id='expense_1' value='" + kb.jumlah_kasbon + "' tabindex='-1' readonly />";
						Rows += "<input type='hidden' class='subkasbon' name='kasbon[]' id='kasbon_1' value='0' />";
						Rows += "</td>";
						Rows += "<td class='text-center'>";
						Rows += "<div class='file-upload-cell-1'>";
						Rows += "<label class='btn btn-outline-primary btn-sm stsview py-1 px-2' style='cursor:pointer;' title='Pilih File Bon/Bukti'>";
						Rows += "<i class='fa fa-folder-open me-1'></i> Pilih File";
						Rows += "<input type='file' id='temp_file_1' class='temp-detail-file-picker' data-row='1' style='display:none;' accept='.jpg,.jpeg,.png,.pdf' multiple>";
						Rows += "</label>";
						Rows += "<input type='file' name='doc_files_1[]' id='doc_files_1' multiple style='display:none;'>";
						Rows += "<div id='new_files_list_1' class='d-flex flex-column gap-1 mt-1'></div>";
						Rows += "</div>";
						Rows += "</td>";
						Rows += "<td class='text-center'>";
						Rows += "<button type='button' class='btn btn-outline-danger btn-sm' onClick='delDetail(1)' title='Hapus'><i class='fa fa-trash'></i></button>";
						Rows += "</td>";
						Rows += "</tr>";

						$('#detail_body').append(Rows);
						if (kb.no_coa) {
							$("#coa_1").val(kb.no_coa);
						}
					}

					$(".tanggal").datepicker({
						todayHighlight: true,
						format: "yyyy-mm-dd",
						showInputs: true,
						autoclose: true
					}).on('changeDate change', function() {
						set_jurnal();
					});
					$('.select2').select2({ width: '100%' });
					$(".divide").divide();
					cektotal();

					if (typeof Swal !== 'undefined') {
						Swal.fire({
							title: 'Berhasil!',
							text: 'Data kasbon ' + kb.no_doc + ' berhasil ditarik ke form.',
							icon: 'success',
							timer: 1500,
							showConfirmButton: false
						});
					}
				} else {
					if (typeof Swal !== 'undefined') {
						Swal.fire('Gagal!', 'Data detail kasbon tidak ditemukan.', 'error');
					} else {
						alert('Data detail kasbon tidak ditemukan.');
					}
				}
			},
			error: function() {
				alert('Terjadi kesalahan saat menarik data detail kasbon.');
			}
		});
	}

	function clear_kasbon_selection() {
		$("#no_doc_kasbon").val("");
		$("#id_kasbon").val("");
		$("#is_direct_expense").val("1");
		$("#total_kasbon").val("0");
		$("#card_kasbon_info").slideUp();
		$("input[name='id_kasbon[]']").val("");
		cektotal();
	}

	// ==========================================
	// DETAIL MULTI-FILE ACCUMULATOR (DataTransfer)
	// ==========================================
	var dtDetailMap = {};

	$(document).on('change', '.temp-detail-file-picker', function() {
		var row = $(this).data('row');
		if (!dtDetailMap[row]) {
			dtDetailMap[row] = new DataTransfer();
		}
		var dt = dtDetailMap[row];
		var newFiles = this.files;
		if (newFiles.length > 0) {
			for (var i = 0; i < newFiles.length; i++) {
				var file = newFiles[i];
				var exists = false;
				for (var j = 0; j < dt.items.length; j++) {
					var existing = dt.items[j].getAsFile();
					if (existing && existing.name === file.name && existing.size === file.size) {
						exists = true;
						break;
					}
				}
				if (!exists) {
					dt.items.add(file);
				}
			}
			var hiddenInput = document.getElementById('doc_files_' + row);
			if (hiddenInput) {
				hiddenInput.files = dt.files;
			}
			$(this).val('');
			render_detail_files(row);
		}
	});

	function render_detail_files(row) {
		var container = $('#new_files_list_' + row);
		container.empty();
		if (!dtDetailMap[row]) return;
		var dt = dtDetailMap[row];
		for (var i = 0; i < dt.files.length; i++) {
			var file = dt.files[i];
			var isPdf = file.name.toLowerCase().endsWith('.pdf');
			var sizeKb = Math.round(file.size / 1024);
			var badge = '<span class="badge bg-light text-dark border p-1 px-2 d-inline-flex align-items-center justify-content-between">' +
				'<span class="text-truncate me-1" style="max-width:110px;" title="' + file.name + ' (' + sizeKb + ' KB)">' +
				'<i class="fa ' + (isPdf ? 'fa-file-pdf-o text-danger' : 'fa-file-image-o text-info') + ' me-1"></i>' + file.name +
				'</span>' +
				'<button type="button" class="btn btn-link btn-sm text-danger p-0 remove-new-detail-file" data-row="' + row + '" data-index="' + i + '" title="Hapus file ini">' +
				'<i class="fa fa-times"></i>' +
				'</button>' +
				'</span>';
			container.append(badge);
		}
	}

	$(document).on('click', '.remove-new-detail-file', function(e) {
		e.preventDefault();
		var row = $(this).data('row');
		var idx = parseInt($(this).data('index'));
		if (dtDetailMap[row]) {
			var dt = dtDetailMap[row];
			var newDt = new DataTransfer();
			for (var i = 0; i < dt.files.length; i++) {
				if (i !== idx) {
					newDt.items.add(dt.files[i]);
				}
			}
			dtDetailMap[row] = newDt;
			var hiddenInput = document.getElementById('doc_files_' + row);
			if (hiddenInput) {
				hiddenInput.files = newDt.files;
			}
			render_detail_files(row);
		}
	});

	$(document).on('click', '.remove-existing-detail-file', function(e) {
		e.preventDefault();
		$(this).closest('.file-badge-item').remove();
	});

	// SUBMIT FORM
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var errors = "";
		if ($("#informasi").val() == "") errors = "Keterangan tidak boleh kosong";
		if ($("#tgl_doc").val() == "") errors = "Tanggal Dokumen tidak boleh kosong";
		if ($("#detail_body tr").length == 0) errors = "Rincian barang/jasa pengeluaran belum diisi";

		if (errors != "") {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					title: "Perhatian!",
					text: errors,
					icon: "warning"
				});
			} else {
				alert(errors);
			}
			return false;
		}

		var doSubmit = function() {
			var formdata = new FormData($('#frm_data')[0]);
			$.ajax({
				url: siteurl + 'expense/save',
				dataType: "json",
				type: 'POST',
				data: formdata,
				processData: false,
				contentType: false,
				success: function(msg) {
					if (msg.save == '1') {
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								title: "Berhasil!",
								text: "Dokumen Expense berhasil disimpan.",
								icon: "success"
							}).then(() => {
								window.location.reload();
							});
						} else {
							alert("Dokumen Expense berhasil disimpan.");
							window.location.reload();
						}
					} else {
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								title: "Gagal!",
								text: msg.message || "Gagal menyimpan dokumen.",
								icon: "error"
							});
						} else {
							alert(msg.message || "Gagal menyimpan dokumen.");
						}
					}
				},
				error: function() {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							title: "Gagal!",
							text: "Terjadi kesalahan pada server saat menyimpan.",
							icon: "error"
						});
					} else {
						alert("Terjadi kesalahan pada server saat menyimpan.");
					}
				}
			});
		};

		if (typeof Swal !== 'undefined') {
			Swal.fire({
				title: "Simpan Expense?",
				text: "Pastikan seluruh data dan bukti pengeluaran sudah benar!",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Ya, Simpan!",
				cancelButtonText: "Batal"
			}).then((result) => {
				if (result.isConfirmed) {
					doSubmit();
				}
			});
		} else {
			if (confirm("Simpan Expense? Pastikan seluruh data sudah benar!")) {
				doSubmit();
			}
		}
	});

	function data_approve() {
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				title: "Setujui Expense?",
				text: "Dokumen akan disetujui dan diteruskan ke tahap berikutnya!",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Ya, Setujui!",
				cancelButtonText: "Batal",
				confirmButtonColor: "#28a745"
			}).then((res) => {
				if (res.isConfirmed) {
					var id = $("#id").val();
					$.post(siteurl + 'expense/approve/' + id, function(result) {
						if (result.save) {
							Swal.fire({
								icon: "success",
								title: "Sukses!",
								text: "Expense berhasil disetujui.",
								timer: 1500,
								showConfirmButton: false
							}).then(() => {
								if (typeof list_view === 'function') {
									list_view();
								} else {
									window.location.reload();
								}
							});
						} else {
							Swal.fire("Gagal!", "Gagal memproses approval.", "error");
						}
					}, "json");
				}
			});
		} else {
			if (confirm("Setujui Expense?")) {
				var id = $("#id").val();
				$.post(siteurl + 'expense/approve/' + id, function(result) {
					if (result.save) {
						alert("Expense berhasil disetujui.");
						if (typeof list_view === 'function') {
							list_view();
						} else {
							window.location.reload();
						}
					} else {
						alert("Gagal memproses approval.");
					}
				}, "json");
			}
		}
	}

	function data_reject() {
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				title: "Tolak Dokumen Expense",
				input: "textarea",
				inputLabel: "Alasan Penolakan (Reject Reason)",
				inputPlaceholder: "Tuliskan alasan penolakan di sini...",
				showCancelButton: true,
				confirmButtonText: "Tolak Dokumen",
				cancelButtonText: "Batal",
				confirmButtonColor: "#dc3545",
				inputValidator: (value) => {
					if (!value || !value.trim()) {
						return "Alasan penolakan wajib diisi!";
					}
				}
			}).then((res) => {
				if (res.isConfirmed) {
					var id = $("#id").val();
					$.post(siteurl + 'expense/reject', {
						id: id,
						reason: res.value,
						table: 'tr_expense'
					}, function(result) {
						Swal.fire({
							icon: "info",
							title: "Ditolak!",
							text: "Dokumen telah ditolak.",
							timer: 1500,
							showConfirmButton: false
						}).then(() => {
							if (typeof list_view === 'function') {
								list_view();
							} else {
								window.location.reload();
							}
						});
					}, "json");
				}
			});
		} else {
			var reason = prompt("Alasan penolakan:");
			if (reason) {
				var id = $("#id").val();
				$.post(siteurl + 'expense/reject', {
					id: id,
					reason: reason,
					table: 'tr_expense'
				}, function(result) {
					alert("Dokumen telah ditolak.");
					if (typeof list_view === 'function') {
						list_view();
					} else {
						window.location.reload();
					}
				}, "json");
			}
		}
	}
</script>
