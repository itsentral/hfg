<?php
$dept = '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!isset($data->departement)) {
	$datauser = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
	$datadept = $this->db->get_where('employee', ['id' => $datauser->employee_id])->row();
	if (!empty($datadept)) {
		$dept = $datauser->department_id;
		$bank_id = $datadept->bank_id;
		$accnumber = $datadept->accnumber;
		$accname = $datadept->accname;
	}
}

$data_user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
$metode_pembayaran = (isset($data)) ? $data->metode_pembayaran : 1;
$mod = isset($mod) ? $mod : '';
$stsview = (isset($stsview)) ? $stsview : '';
$is_approval = ($stsview === 'approval');
$is_view = ($stsview === 'view' || $is_approval || $mod == '_fin' || $mod == '_mgt');

// Tentukan mode awal: apakah dari PR atau Manual
$is_mode_pr = (isset($data->id_pr) && !empty($data->id_pr));
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
	/* Tombol Pemilih Mode Aktif vs Non-aktif */
	.btn-mode-tipe.active,
	.btn-check-mode:checked + .btn-mode-tipe {
		background-color: #0d6efd !important;
		border-color: #0d6efd !important;
		color: #ffffff !important;
		box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3) !important;
		font-weight: 600 !important;
	}
	.btn-mode-tipe:not(.active),
	.btn-check-mode:not(:checked) + .btn-mode-tipe {
		background-color: #ffffff !important;
		border-color: #dee2e6 !important;
		color: #6c757d !important;
		font-weight: 500 !important;
	}
	.btn-mode-tipe:not(.active):hover {
		background-color: #f8f9fa !important;
		color: #0d6efd !important;
		border-color: #0d6efd !important;
	}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<form action="" id="frm_data" enctype="multipart/form-data">
	<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
	<input type="hidden" id="departement" name="departement" value="<?php echo ($data_user->department_id) ?>">
	<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
	<input type="hidden" name="stsview" class="stsview" value="<?= (isset($stsview)) ? $stsview : null ?>">

	<div class="card shadow-sm border-0 mb-4">
		<div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
			<h5 class="card-title mb-0 fw-bold text-primary">
				<i class="fa <?= $is_approval ? 'fa-check-circle text-success' : ($is_view ? 'fa-eye' : (isset($data->id) ? 'fa-edit' : 'fa-plus')) ?> me-2"></i>
				<?php 
				if ($is_approval) {
					echo ($mod == '_mgt') ? 'Persetujuan / Approval Management Kasbon' : 'Pengecekan / Approval Finance Kasbon';
				} elseif ($is_view) {
					echo 'Detail Pengajuan Kasbon';
				} elseif (isset($data->id) && $data->id != '') {
					echo 'Edit Kasbon';
				} else {
					echo 'Form Pengajuan Kasbon';
				}
				?>
			</h5>
			<button type="button" class="btn btn-outline-secondary btn-sm" onclick="list_view(); return false;">
				<i class="fa fa-arrow-left me-1"></i> Kembali ke List
			</button>
		</div>

		<div class="card-body p-4">

			<!-- 1. Opsi Pilihan Tipe Pengajuan Kasbon (Manual vs PR Non PO) -->
			<div class="card bg-light border-0 mb-4 p-3 rounded-3 shadow-none">
				<div class="row align-items-center g-3">
					<div class="col-md-4">
						<label class="form-label fw-bold text-dark mb-0">
							<i class="fa fa-sliders-h me-1 text-primary"></i> Sumber Pengajuan Kasbon <span class="text-danger">*</span>
						</label>
						<div class="text-muted small">Pilih metode input manual atau tarik dari PR</div>
					</div>
					<div class="col-md-8">
						<div class="btn-group w-100" role="group">
							<input type="radio" class="btn-check btn-check-mode opt_tipe_pengajuan" name="tipe_pengajuan" id="tipe_manual" value="manual" <?= (!$is_mode_pr) ? 'checked' : '' ?> autocomplete="off">
							<label class="btn btn-mode-tipe py-2 <?= (!$is_mode_pr) ? 'active' : '' ?>" for="tipe_manual" id="lbl_tipe_manual">
								<i class="fa fa-pen me-2"></i> Input Manual (Umum)
							</label>

							<input type="radio" class="btn-check btn-check-mode opt_tipe_pengajuan" name="tipe_pengajuan" id="tipe_pr" value="pr" <?= ($is_mode_pr) ? 'checked' : '' ?> autocomplete="off">
							<label class="btn btn-mode-tipe py-2 <?= ($is_mode_pr) ? 'active' : '' ?>" for="tipe_pr" id="lbl_tipe_pr">
								<i class="fa fa-shopping-cart me-2"></i> Tarik dari PR Non PO
							</label>
						</div>
					</div>
				</div>
			</div>

			<!-- 2. Card Bagian: Kasbon PR Non PO (POSISI DI PALING ATAS - Tampil HANYA jika mode "Tarik dari PR Non PO" dipilih) -->
			<div class="mb-4 p-3 bg-light rounded-3 border" id="card_pr_non_po" style="<?= (!$is_mode_pr) ? 'display: none;' : '' ?>">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<h6 class="fw-bold text-dark mb-0">
						<i class="fa fa-shopping-cart me-2 text-primary"></i>Referensi PR Non PO
					</h6>
					<span class="badge bg-primary">Integrasi PR Non PO</span>
				</div>
				<div class="row g-3 mb-3">
					<div class="col-md-6">
						<label class="form-label fw-semibold">Pilih No. PR Non PO <span class="text-danger">*</span></label>
						<?php if (isset($data->id_pr) && $data->id_pr != '') : ?>
							<input type="text" name="no_pr" id="search_pr_non_po" class="form-control" placeholder="- No PR -" value="<?= $data->id_pr ?>" readonly>
						<?php else : ?>
							<select name="no_pr" id="search_pr_non_po" class="form-select chosen_select">
								<option value="">- Pilih No PR -</option>
								<?php
								if (!empty($list_pr_non_po)) {
									foreach ($list_pr_non_po as $item_pr_non_po) {
										echo '<option value="' . $item_pr_non_po['no_pr'] . '">' . $item_pr_non_po['no_pr'] . ' - ' . $item_pr_non_po['keterangan'] . '</option>';
									}
								}
								?>
							</select>

							<input type="hidden" name="file_name" class="file_name">
							<input type="hidden" name="doc_pr" class="doc_pr">
							<input type="hidden" name="to_doc_pr" class="to_doc_pr">
						<?php endif; ?>

						<input type="hidden" name="tipe_pr" id="tipe_pr" value="<?= (isset($list_detail_pr_kasbon[0]['tipe_pr'])) ? $list_detail_pr_kasbon[0]['tipe_pr'] : null ?>">
						<small class="text-muted"><i class="fa fa-info-circle me-1"></i>Pilih PR Non PO untuk memuat daftar barang otomatis</small>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table table-bordered table-striped table-hover align-middle w-100">
						<thead class="table-light text-center">
							<tr>
								<th width="4%" class="text-center">No.</th>
								<th class="text-center">Material Name</th>
								<th width="10%" class="text-center">Qty</th>
								<th width="10%" class="text-center">Unit</th>
								<th width="18%" class="text-center">Harga Satuan</th>
								<th width="18%" class="text-center">Total</th>
								<th width="6%" class="text-center">Action</th>
							</tr>
						</thead>
						<tbody class="list_barang_pr">
							<?php
							$grand_total_non_pr = 0;
							if (isset($list_detail_pr_kasbon) && !empty($list_detail_pr_kasbon)) {
								$no = 1;
								foreach ($list_detail_pr_kasbon as $detail_pr) :
									$readonly = '';
									if (($mod == '_fin' || $mod == '_mgt')) {
										$readonly = 'readonly';
									}
									echo '<tr class="detail_pr_' . $detail_pr['id_detail'] . '">';
									echo '<td class="text-center">' . $no . '</td>';
									echo '<td>' . $detail_pr['nm_material'] . '</td>';
									echo '<td class="text-center">' . number_format($detail_pr['qty']) . ' <input type="hidden" class="qty_' . $detail_pr['id_detail'] . '" value="' . $detail_pr['qty'] . '"></td>';
									echo '<td class="text-center">' . $detail_pr['satuan'] . '</td>';
									echo '<td class="text-end"><input type="text" name="price_input_' . $detail_pr['id_detail'] . '" class="form-control form-control-sm text-end price_input price_input_' . $detail_pr['id_detail'] . ' autonum" data-no="' . $detail_pr['id_detail'] . '" value="' . $detail_pr['harga'] . '" ' . $readonly . '></td>';
									echo '<td class="text-end"><input type="text" name="grand_total_' . $detail_pr['id_detail'] . '" class="form-control form-control-sm text-end grand_total_' . $detail_pr['id_detail'] . ' autonum" value="' . $detail_pr['total_harga'] . '" ' . $readonly . '></td>';
									echo '<td class="text-center">';
									if (($mod == '_fin' || $mod == '_mgt')) {
									} else {
										if (!isset($stsview) || $stsview == '') {
											echo '<button type="button" class="btn btn-sm btn-danger del_detail" data-no="' . $detail_pr['id_detail'] . '" title="Hapus"><i class="fa fa-trash"></i></button>';
										}
									}
									echo '</td>';
									echo '</tr>';

									$grand_total_non_pr += $detail_pr['total_harga'];
									$no++;
								endforeach;
							}
							?>
						</tbody>
						<tfoot class="table-light">
							<tr>
								<th class="text-end fw-bold" colspan="5">Grand Total:</th>
								<th class="text-end fw-bold text-primary grand_total_non_pr"><?= number_format($grand_total_non_pr, 2) ?></th>
								<th></th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>

			<!-- 3. Baris 1: No Dokumen & Tanggal -->
			<div class="row g-3 mb-3">
				<div class="col-md-6">
					<label class="form-label fw-semibold">No Dokumen</label>
					<input type="text" class="form-control" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc : ""); ?>" placeholder="Automatic" readonly>
				</div>
				<div class="col-md-6">
					<label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
					<input type="text" class="form-control tanggal" id="tgl_doc" name="tgl_doc" value="<?php echo (isset($data->tgl_doc) ? $data->tgl_doc : date("Y-m-d")); ?>" placeholder="Tanggal Dokumen" required autocomplete="off">
				</div>
			</div>

			<!-- 4. Baris 2: Keperluan & Nominal Kasbon -->
			<div class="row g-3 mb-3">
				<div class="col-md-6">
					<label class="form-label fw-semibold">Keperluan <span class="text-danger">*</span></label>
					<input type="text" class="form-control" id="keperluan" name="keperluan" value="<?php echo (isset($data->keperluan) ? $data->keperluan : ''); ?>" placeholder="Keperluan Kasbon" required>
				</div>
				<div class="col-md-6">
					<label class="form-label fw-semibold">Nominal Kasbon <span class="text-danger">*</span></label>
					<input type="text" class="form-control divide" id="jumlah_kasbon" name="jumlah_kasbon" value="<?php echo (isset($data->jumlah_kasbon) ? $data->jumlah_kasbon : '0'); ?>" placeholder="0" <?= ($is_mode_pr) ? 'readonly style="background-color: #f8f9fa; cursor: not-allowed;"' : '' ?>>
					<small class="text-muted" id="note_nominal">
						<?php if ($is_mode_pr) : ?>
							<i class="fa fa-info-circle me-1"></i>Otomatis mengikuti grand total rincian barang PR Non PO di atas
						<?php else : ?>
							<i class="fa fa-pencil-alt me-1"></i>Masukkan nominal kasbon secara manual
						<?php endif; ?>
					</small>
				</div>
			</div>

			<!-- 5. Baris 3: Bagian Lampiran Dokumen (Multi-Upload, Max 5 File & Max 5 MB) -->
			<div class="mb-4 p-3 bg-light rounded-3 border" id="container_lampiran">
				<div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
					<div>
						<h6 class="fw-bold text-dark mb-1">
							<i class="fa fa-folder-open me-2 text-primary"></i>Daftar File Lampiran
						</h6>
						<small class="text-muted">
							Maksimal 5 file lampiran (maks. 5 MB per file). Format yang didukung: PDF, JPG, PNG, DOCX, XLSX.
						</small>
					</div>
					<div class="d-flex align-items-center gap-2">
						<span class="badge bg-secondary" id="badge_total_files">
							<?= (isset($list_files) && !empty($list_files)) ? count($list_files) : 0 ?> / 5 File
						</span>
						<?php if (!isset($stsview) || $stsview == '') : ?>
							<button type="button" class="btn btn-primary btn-sm" id="btn_add_files">
								<i class="fa fa-plus me-1"></i> Tambah File
							</button>
							<!-- Hidden file picker -->
							<input type="file" id="file_picker" multiple class="d-none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
						<?php endif; ?>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table table-sm table-bordered table-hover align-middle mb-0 bg-white" id="tbl_lampiran">
						<thead class="table-light text-center">
							<tr>
								<th width="5%">No.</th>
								<th>Nama File</th>
								<th width="15%">Ukuran</th>
								<th width="20%">Status / Sumber</th>
								<th width="15%">Aksi</th>
							</tr>
						</thead>
						<tbody id="tbody_lampiran">
							<?php
							$no_f = 1;
							if (isset($list_files) && !empty($list_files)) :
								foreach ($list_files as $f_item) :
									$f_name = $f_item['file_name'];
									$f_url = base_url('uploads/expense/' . $f_name);
							?>
									<tr id="row_file_<?= $f_item['id'] ?>" class="row_saved_file">
										<td class="text-center font-monospace"><?= $no_f++ ?></td>
										<td>
											<i class="fa fa-file-alt text-primary me-2"></i>
											<strong><?= $f_name ?></strong>
										</td>
										<td class="text-center text-muted">-</td>
										<td class="text-center">
											<span class="badge bg-success">Tersimpan</span>
										</td>
										<td class="text-center">
											<a href="<?= $f_url ?>" target="_blank" class="btn btn-xs btn-outline-primary me-1" title="Buka File">
												<i class="fa fa-external-link-alt"></i> Buka
											</a>
											<?php if (!isset($stsview) || $stsview == '') : ?>
												<button type="button" class="btn btn-xs btn-outline-danger btn_del_file" data-id="<?= $f_item['id'] ?>" data-name="<?= $f_name ?>" title="Hapus File">
													<i class="fa fa-trash"></i>
												</button>
											<?php endif; ?>
										</td>
									</tr>
							<?php
								endforeach;
							endif;
							?>
							<!-- Placeholder row jika ada dokumen PR Non PO -->
							<tr id="row_pr_doc_preview" style="display: none;">
								<td class="text-center font-monospace">#</td>
								<td id="lbl_pr_doc_name"></td>
								<td class="text-center text-muted">-</td>
								<td class="text-center"><span class="badge bg-info text-dark">Dari PR Non PO</span></td>
								<td class="text-center"><span class="badge bg-light text-dark">Otomatis Disertakan</span></td>
							</tr>
							<!-- Baris kosong jika belum ada file sama sekali -->
							<tr id="row_empty_files" style="<?= (isset($list_files) && !empty($list_files)) ? 'display: none;' : '' ?>">
								<td colspan="5" class="text-center text-muted py-3">
									<i class="fa fa-info-circle me-1"></i> Belum ada file lampiran yang dipilih. Klik tombol <strong>+ Tambah File</strong> di atas untuk mengunggah.
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- 6. Baris 4: Keterangan & COA -->
			<div class="row g-3 mb-4">
				<div class="col-md-6">
					<label class="form-label fw-semibold">Keterangan</label>
					<textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Keterangan"><?php echo (isset($data->keterangan) ? $data->keterangan : ''); ?></textarea>

					<?php if (isset($data->st_reject) && $data->st_reject !== '') : ?>
						<div class="alert alert-danger mt-2 py-2 mb-0">
							<h6 class="fw-bold mb-1"><i class="fa fa-ban me-1"></i> Alasan Penolakan:</h6>
							<div><?= $data->st_reject ?></div>
						</div>
					<?php endif; ?>
				</div>
				<div class="col-md-6">
					<label class="form-label fw-semibold">COA</label>
					<select name="coa" class="form-select coa_select chosen_select">
						<option value="">- Pilih COA -</option>
						<?php
						if (!empty($list_coa)) {
							foreach ($list_coa as $item_coa) {
								$selected = '';
								if (isset($data->no_coa) && $data->no_coa == $item_coa['no_perkiraan']) {
									$selected = 'selected';
								}
								echo '<option value="' . $item_coa['no_perkiraan'] . '" ' . $selected . '>' . $item_coa['no_perkiraan'] . ' - ' . $item_coa['nama'] . '</option>';
							}
						}
						?>
					</select>
					<input type="hidden" name="nm_coa" value="<?= (isset($data->nm_coa) ? $data->nm_coa : ''); ?>">
				</div>
			</div>

			<!-- 7. Card Bagian: Transfer ke -->
			<div class="transfer_ke_cont mb-4 p-3 bg-light rounded-3 border">
				<h6 class="fw-bold text-dark mb-3">
					<i class="fa fa-university me-2 text-primary"></i>Informasi Rekening Transfer
				</h6>
				<div class="row g-3">
					<div class="col-md-4">
						<label class="form-label fw-semibold">Bank</label>
						<input type="text" class="form-control" id="bank_id" name="bank_id" value="<?php echo (isset($data->bank_id) ? $data->bank_id : $bank_id); ?>" placeholder="Nama Bank">
					</div>
					<div class="col-md-4">
						<label class="form-label fw-semibold">Nomor Rekening</label>
						<input type="text" class="form-control" id="accnumber" name="accnumber" value="<?php echo (isset($data->accnumber) ? $data->accnumber : $accnumber); ?>" placeholder="Nomor Rekening">
					</div>
					<div class="col-md-4">
						<label class="form-label fw-semibold">Nama Pemilik Rekening</label>
						<input type="text" class="form-control" id="accname" name="accname" value="<?php echo (isset($data->accname) ? $data->accname : $accname); ?>" placeholder="Nama Pemilik Rekening">
					</div>
				</div>
			</div>

			<!-- 8. Lampiran Dokumen Preview Jika Ada -->
			<?php if (isset($list_files) && !empty($list_files)) : ?>
				<div class="card shadow-sm border-0 mb-4 p-3 bg-light rounded-3">
					<h6 class="fw-bold mb-3"><i class="fa fa-paperclip me-2 text-primary"></i>Preview Lampiran Dokumen</h6>
					<div class="row g-3">
						<?php
						$f_idx = 1;
						foreach ($list_files as $f_item) :
							$f_name = $f_item['file_name'];
							$f_ext  = strtolower(pathinfo($f_name, PATHINFO_EXTENSION));
							$f_url  = base_url('uploads/expense/' . $f_name);
						?>
							<div class="col-md-6">
								<div class="card h-100 border">
									<div class="card-header bg-white py-2 fw-semibold d-flex justify-content-between align-items-center">
										<span class="text-truncate" style="max-width: 250px;" title="<?= $f_name ?>">
											<i class="fa fa-file-alt me-1 text-primary"></i> Dokumen <?= $f_idx ?>: <?= $f_name ?>
										</span>
										<a href="<?= $f_url ?>" target="_blank" class="btn btn-sm btn-primary py-0 px-2">
											<i class="fa fa-external-link-alt me-1"></i> Buka File
										</a>
									</div>
									<div class="card-body text-center p-2">
										<?php if ($f_ext == 'pdf') : ?>
											<iframe src="<?= $f_url ?>#toolbar=0&navpanes=0" title="PDF <?= $f_idx ?>" style="width:100%; height:380px; border:none;"></iframe>
										<?php elseif (in_array($f_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) : ?>
											<img src="<?= $f_url ?>" class="img-fluid rounded" style="max-height: 380px;" alt="<?= $f_name ?>">
										<?php else : ?>
											<div class="py-5">
												<i class="fa fa-file-invoice fa-4x text-muted mb-2"></i>
												<p class="mb-2 fw-semibold"><?= $f_name ?></p>
												<a href="<?= $f_url ?>" target="_blank" class="btn btn-sm btn-outline-primary">
													<i class="fa fa-download me-1"></i> Unduh File
												</a>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php
							$f_idx++;
						endforeach;
						?>
					</div>
				</div>
			<?php endif; ?>

		</div>

		<!-- Card Footer Buttons -->
		<div class="card-footer bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
			<div>
				<button type="button" class="btn btn-outline-secondary btn-sm" onclick="list_view(); return false;">
					<i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
				</button>
			</div>
			<div class="d-flex align-items-center gap-2">
				<?php if (isset($data) && !empty($data->id) && $stsview !== 'view' && $is_approval && ($data->status == 0 || $data->status == 1)): ?>
					<button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm" id="approve" onclick="data_approve(<?= $data->id; ?>, <?= ($data->status + 1); ?>)">
						<i class="fa fa-check-circle"></i> Setujui Kasbon
					</button>
					<button type="button" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 shadow-sm" onclick="data_reject()">
						<i class="fa fa-ban"></i> Tolak Pengajuan
					</button>
				<?php endif; ?>

				<?php if (!$is_view): ?>
					<button type="submit" name="save" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" id="submit">
						<i class="fa fa-save"></i> Simpan Pengajuan
					</button>
				<?php endif; ?>
			</div>
		</div>
	</div>
</form>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
	var base_siteurl = (typeof siteurl !== 'undefined') ? siteurl : '<?= site_url() ?>';
	var url_save = base_siteurl + 'expense/kasbon_save/';
	var url_approve = (mod === '_mgt' ? base_siteurl + 'expense/kasbon_approve_manage/' : base_siteurl + 'expense/kasbon_approve/');

	var mod = '<?= $mod ?>';
	var stsview = '<?= $stsview ?>';
	var is_approval = <?= $is_approval ? 'true' : 'false'; ?>;
	var is_view = <?= $is_view ? 'true' : 'false'; ?>;

	if (is_view) {
		$('input, textarea').attr('readonly', true);
		$('select, input[type="file"], input[type="radio"], input[type="checkbox"]').prop('disabled', true);
		$('#btn_add_files').hide();
		$('.btn-mode-tipe').addClass('disabled').css('pointer-events', 'none');
		$('.del_detail, .btn_del_file').hide();
		$(".stsview").addClass("hidden");
	}

	$('.divide').divide();
	try {
		$('.autonum').autoNumeric('init');
	} catch(e) {}
	
	$('.chosen_select').select2({
		width: '100%'
	});

	// ========================================================
	// FUNGSI UPDATE NILAI NOMINAL KASBON SECARA HANDAL
	// ========================================================
	function update_nominal_kasbon_ui(nominal) {
		var n = parseFloat(nominal);
		if (isNaN(n)) n = 0;

		var formatted = number_format(n, 0, '.', ',');

		var $target = $('#jumlah_kasbon');
		$target.val(formatted);

		// Sinkronkan input hidden yang dibuat oleh plugin divide jika ada
		var $hiddenDivide = $target.parent().find('input[type="hidden"][name="jumlah_kasbon"]');
		if ($hiddenDivide.length) {
			$hiddenDivide.val(n);
		}

		// Update data divided jika ada
		var divided = $target.data('divided');
		if (divided) {
			divided.value = n.toString();
			$target.data('divided', divided);
		}

		// Trigger event change
		$target.trigger('input');
		$target.trigger('change');
	}

	// ========================================================
	// TOGGLE TIPE PENGAJUAN (MANUAL vs PR NON PO) DENGAN WARNA
	// ========================================================
	$(document).on('click', '#lbl_tipe_manual', function() {
		$('#tipe_manual').prop('checked', true).trigger('change');
	});
	$(document).on('click', '#lbl_tipe_pr', function() {
		$('#tipe_pr').prop('checked', true).trigger('change');
	});

	$(document).on('change', '.opt_tipe_pengajuan', function() {
		var tipe = $('input[name="tipe_pengajuan"]:checked').val();

		if (tipe === 'manual') {
			// Update tampilan warna tombol aktif
			$('#lbl_tipe_manual').addClass('active');
			$('#lbl_tipe_pr').removeClass('active');

			// Sembunyikan card PR Non PO
			$('#card_pr_non_po').slideUp(250);

			// Kosongkan referensi PR
			if ($('#search_pr_non_po').is('select')) {
				$('#search_pr_non_po').val('').trigger('change');
			}
			$('.list_barang_pr').html('');
			$('#tipe_pr').val('');
			$('.file_name').val('');
			$('.doc_pr').val('');
			$('.to_doc_pr').val('');
			$('#row_pr_doc_preview').hide();
			update_total_files_badge();

			// Buka kunci Nominal Kasbon agar bisa diinput bebas
			$('#jumlah_kasbon').prop('readonly', false).removeAttr('style');
			$('#note_nominal').html('<i class="fa fa-pencil-alt me-1"></i>Masukkan nominal kasbon secara manual');
		} else {
			// Update tampilan warna tombol aktif
			$('#lbl_tipe_pr').addClass('active');
			$('#lbl_tipe_manual').removeClass('active');

			// Tampilkan card PR Non PO
			$('#card_pr_non_po').slideDown(250);

			// Kunci Nominal Kasbon mengikuti PR
			$('#jumlah_kasbon').prop('readonly', true).css({
				'background-color': '#f8f9fa',
				'cursor': 'not-allowed'
			});
			$('#note_nominal').html('<i class="fa fa-info-circle me-1"></i>Otomatis mengikuti grand total rincian barang PR Non PO di atas');

			// Hitung ulang jika sudah ada barang di tabel
			hitung_grand_total_non_pr();
		}
	});

	$(document).on('change', '.metode_pembayaran', function() {
		var metode_pembayaran = $(this).val();

		if (metode_pembayaran == 1) {
			$('.transfer_ke_cont').show();
			$('#bank_id').prop('required', true);
			$('#accnumber').prop('required', true);
			$('#accname').prop('required', true);
		} else {
			$('.transfer_ke_cont').hide();
			$('#bank_id').prop('required', false);
			$('#accnumber').prop('required', false);
			$('#accname').prop('required', false);
		}
	});

	// ========================================================
	// SISTEM MULTI-FILE QUEUE (MAX 5 FILES & MAX 5 MB PER FILE)
	// ========================================================
	var MAX_TOTAL_FILES = 5;
	var MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB
	var selected_new_files = [];

	// Trigger file picker
	$(document).on('click', '#btn_add_files', function(e) {
		e.preventDefault();
		var current_count = get_current_total_files_count();
		if (current_count >= MAX_TOTAL_FILES) {
			swal({
				title: "Batas Maksimal!",
				text: "Maksimal hanya " + MAX_TOTAL_FILES + " file yang dapat dilampirkan.",
				type: "warning"
			});
			return;
		}

		$('#file_picker').val(''); // Reset value
		$('#file_picker').trigger('click');
	});

	function get_current_total_files_count() {
		var saved_count = $('#tbody_lampiran tr.row_saved_file').length;
		var new_count = selected_new_files.length;
		var pr_count = ($('#row_pr_doc_preview').is(':visible')) ? 1 : 0;
		return saved_count + new_count + pr_count;
	}

	// Tangani pemilihan file dari picker
	$(document).on('change', '#file_picker', function() {
		var files = this.files;
		if (files && files.length > 0) {
			var current_count = get_current_total_files_count();

			for (var i = 0; i < files.length; i++) {
				var f = files[i];

				// Validasi 1: Ukuran File Max 5 MB
				if (f.size > MAX_FILE_SIZE_BYTES) {
					var sizeMb = (f.size / (1024 * 1024)).toFixed(2);
					swal({
						title: "Ukuran File Terlalu Besar!",
						text: "File \"" + f.name + "\" berukuran " + sizeMb + " MB. Maksimal ukuran per file adalah 5 MB.",
						type: "error"
					});
					continue;
				}

				// Validasi 2: Kuota Maksimal 5 File
				if (current_count >= MAX_TOTAL_FILES) {
					swal({
						title: "Batas Maksimal Tercapai!",
						text: "Maksimal hanya " + MAX_TOTAL_FILES + " file lampiran yang diizinkan. Sebagian file tidak dapat ditambahkan.",
						type: "warning"
					});
					break;
				}

				// Validasi 3: Anti Duplikat
				var is_duplicate = false;
				for (var j = 0; j < selected_new_files.length; j++) {
					if (selected_new_files[j].name === f.name && selected_new_files[j].size === f.size) {
						is_duplicate = true;
						break;
					}
				}

				if (!is_duplicate) {
					selected_new_files.push(f);
					current_count++;
				}
			}

			render_new_files_rows();
		}
	});

	// Render antrian file baru ke tabel
	function render_new_files_rows() {
		$('.row_new_selected').remove();

		if (selected_new_files.length > 0) {
			$('#row_empty_files').hide();
			var saved_count = $('#tbody_lampiran tr.row_saved_file').length;

			for (var i = 0; i < selected_new_files.length; i++) {
				var f = selected_new_files[i];
				var fSize = (f.size < 1048576) ? (f.size / 1024).toFixed(1) + ' KB' : (f.size / 1048576).toFixed(2) + ' MB';
				var noUrut = saved_count + i + 1;

				var newRow = '<tr class="row_new_selected table-info">' +
					'<td class="text-center font-monospace">' + noUrut + '</td>' +
					'<td><i class="fa fa-file-upload text-primary me-2"></i><strong>' + f.name + '</strong></td>' +
					'<td class="text-center font-monospace text-muted">' + fSize + '</td>' +
					'<td class="text-center"><span class="badge bg-primary">Baru Dipilih</span></td>' +
					'<td class="text-center">' +
						'<button type="button" class="btn btn-xs btn-outline-danger btn_remove_new_file" data-index="' + i + '" title="Batalkan file ini">' +
							'<i class="fa fa-times me-1"></i> Batal' +
						'</button>' +
					'</td>' +
					'</tr>';
				$('#tbody_lampiran').append(newRow);
			}
		}

		update_total_files_badge();
	}

	// Batalkan salah satu file baru dari antrian
	$(document).on('click', '.btn_remove_new_file', function(e) {
		e.preventDefault();
		var idx = $(this).data('index');
		if (idx !== undefined && idx >= 0 && idx < selected_new_files.length) {
			selected_new_files.splice(idx, 1);
			render_new_files_rows();
		}
	});

	// Update counter badge total file
	function update_total_files_badge() {
		var total = get_current_total_files_count();
		$('#badge_total_files').text(total + ' / ' + MAX_TOTAL_FILES + ' File');

		// Toggle state tombol tambah file
		if (total >= MAX_TOTAL_FILES) {
			$('#btn_add_files').prop('disabled', true).addClass('disabled').attr('title', 'Batas maksimal 5 file telah tercapai');
		} else {
			$('#btn_add_files').prop('disabled', false).removeClass('disabled').removeAttr('title');
		}

		if (total === 0) {
			$('#row_empty_files').show();
		} else {
			$('#row_empty_files').hide();
		}
	}

	// Hapus file tersimpan di database via AJAX
	$(document).on('click', '.btn_del_file', function(e) {
		e.preventDefault();
		var f_id = $(this).data('id');
		var f_name = $(this).data('name');

		swal({
			title: "Hapus File Lampiran?",
			text: "File \"" + f_name + "\" akan dihapus permanen!",
			type: "warning",
			showCancelButton: true,
			confirmButtonText: "Ya, Hapus!",
			cancelButtonText: "Batal",
			closeOnConfirm: false,
			closeOnCancel: true
		}, function(isConfirm) {
			if (isConfirm) {
				$.ajax({
					url: base_siteurl + 'expense/delete_kasbon_file',
					type: 'POST',
					dataType: 'json',
					data: { id: f_id },
					success: function(resp) {
						if (resp.status == 1) {
							$('#row_file_' + f_id).fadeOut(300, function() {
								$(this).remove();
								update_total_files_badge();
								// Urutkan ulang nomor baris
								$('#tbody_lampiran tr.row_saved_file').each(function(idx) {
									$(this).find('td:first').text(idx + 1);
								});
								render_new_files_rows();
							});
							swal({
								title: "Sukses!",
								text: resp.pesan,
								type: "success",
								timer: 1500,
								showConfirmButton: false
							});
						} else {
							swal("Gagal!", resp.pesan, "error");
						}
					},
					error: function() {
						swal("Gagal!", "Terjadi kesalahan saat menghapus file.", "error");
					}
				});
			}
		});
	});

	// ========================================================
	// SUBMIT FORM
	// ========================================================
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var errors = "";

		var tipe_pengajuan = $('input[name="tipe_pengajuan"]:checked').val();
		var search_pr_non_po = $('#search_pr_non_po').val();
		var nominal_val = $("#jumlah_kasbon").val().toString().replace(/,/g, '');

		if (nominal_val == "0" || nominal_val == "" || parseFloat(nominal_val) <= 0) {
			errors = "Nominal Kasbon tidak boleh kosong atau 0!";
		}

		if (tipe_pengajuan === 'pr') {
			if (!search_pr_non_po || search_pr_non_po === '') {
				errors = "Silakan pilih No. PR Non PO terlebih dahulu!";
			}

			var price_no_input = 0;
			$('.price_input').each(function() {
				var val = parseFloat($(this).val().toString().replace(/,/g, ''));
				if (isNaN(val)) {
					price_no_input += 1;
				}
			});

			if (price_no_input > 0) {
				errors = "Pastikan semua harga material pada tabel PR sudah terisi!";
			}
		}

		if ($("#keperluan").val() == "") errors = "Keperluan tidak boleh kosong";
		if ($("#tgl_doc").val() == "") errors = "Tanggal Transaksi tidak boleh kosong";

		if (errors == "") {
			swal({
					title: "Anda Yakin?",
					text: "Data Akan Disimpan!",
					type: "info",
					showCancelButton: true,
					confirmButtonText: "Ya, simpan!",
					cancelButtonText: "Tidak!",
					closeOnConfirm: false,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						var formdata = new FormData($('#frm_data')[0]);

						// Masukkan SEMUA file baru dari antrian selected_new_files
						formdata.delete('doc_file[]');
						formdata.delete('doc_file');
						for (var i = 0; i < selected_new_files.length; i++) {
							formdata.append('doc_file[]', selected_new_files[i]);
						}

						$.ajax({
							url: url_save,
							dataType: "json",
							type: 'POST',
							data: formdata,
							processData: false,
							contentType: false,
							success: function(msg) {
								if (msg['save'] == '1') {
									swal({
										title: "Sukses!",
										text: "Data Berhasil Disimpan",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									});
									if (typeof list_view === 'function') {
										list_view();
										if (typeof datatables === 'function') datatables();
									} else {
										window.location = base_siteurl + 'expense/kasbon';
									}
								} else {
									swal({
										title: "Gagal!",
										text: "Data Gagal Disimpan",
										type: "error",
										timer: 1500,
										showConfirmButton: false
									});
								}
							},
							error: function(msg) {
								swal({
									title: "Gagal!",
									text: "Ajax Data Gagal Diproses",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
							}
						});
					}
				});
		} else {
			swal({
				title: 'Perhatian!',
				text: errors,
				type: 'error'
			});
		}
	});

	function number_format(number, decimals, dec_point, thousands_sep) {
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

	// ========================================================
	// EVENT SAAT USER MEMILIH NO PR NON PO
	// ========================================================
	$(document).on('change', '#search_pr_non_po', function(e) {
		const no_pr = $(this).val();
		if (!no_pr) return;

		$.ajax({
			type: "POST",
			url: base_siteurl + 'expense/get_pr_non_po',
			data: {
				'no_pr': no_pr
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				if (result.sts == '1') {
					$('.list_barang_pr').html(result.hasil);
					$('#tipe_pr').val(result.tipe_pr);

					// 1. Update nominal kasbon langsung dari result server (PENTING!)
					var total_pr = parseFloat(result.grand_total) || 0;
					$('.grand_total_non_pr').html(number_format(total_pr, 2));
					update_nominal_kasbon_ui(total_pr);

					// 2. Inisialisasi autoNumeric dengan aman
					try {
						$('.list_barang_pr .autonum').autoNumeric('init');
					} catch(err) {}

					// 3. Hitung ulang untuk memastikan
					hitung_grand_total_non_pr();

					if ($('#keperluan').val() == '') {
						$('#keperluan').val('Kasbon Non PO ' + no_pr);
					}

					// Otomatis tarik No COA dan Nama COA dari PR
					if (result.no_coa && result.no_coa !== '') {
						if ($('.coa_select option[value="' + result.no_coa + '"]').length === 0) {
							var coa_label = result.no_coa + (result.nm_coa ? ' - ' + result.nm_coa : '');
							var newOption = new Option(coa_label, result.no_coa, true, true);
							$('.coa_select').append(newOption);
						}
						$('.coa_select').val(result.no_coa).trigger('change');
						$('input[name="nm_coa"]').val(result.nm_coa);
					}
				} else {
					swal({
						title: 'Perhatian!',
						text: result.pesan,
						type: 'error'
					});
				}
			},
			error: function(result) {
				swal({
					title: 'Error!',
					text: 'Terjadi kendala saat memuat data PR, silakan coba beberapa saat lagi.',
					type: 'error'
				});
			}
		});

		$.ajax({
			type: 'post',
			url: base_siteurl + 'expense/copy_pr_doc',
			data: {
				'no_pr': no_pr
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.file_name').val(result.file_name);
				$('.doc_pr').val(result.doc_file);
				$('.to_doc_pr').val(result.to_doc_file);

				if (result.file_name && result.file_name !== '') {
					$('#lbl_pr_doc_name').html('<i class="fa fa-file-contract text-primary me-2"></i><strong>' + result.file_name + '</strong>');
					$('#row_pr_doc_preview').show();
				} else {
					$('#row_pr_doc_preview').hide();
				}
				update_total_files_badge();
			}
		});
	});

	// Hapus baris detail PR Non PO & otomatis hitung ulang grand total
	$(document).on('click', '.del_detail', function() {
		var no = $(this).data('no');
		$('.detail_pr_' + no).remove();
		hitung_grand_total_non_pr();
	});

	// Real-time adjustment: setiap input angka/perubahan harga satuan, subtotal baris dan grand total langsung update
	$(document).on('input keyup change', '.price_input', function() {
		hitung_grand_total_non_pr();
	});

	$(document).on('change', '.coa_select', function() {
		var no_coa = $(this).val();

		$.ajax({
			type: 'post',
			url: base_siteurl + 'expense/get_coa_name',
			data: {
				'no_coa': no_coa
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('input[name="nm_coa"]').val(result.nm_coa);
			}
		});
	});

	function getNum(val) {
		if (isNaN(val) || val == '') {
			return 0;
		}
		return parseFloat(val);
	}

	// Menghitung grand total PR Non PO & mengupdate input Nominal Kasbon (#jumlah_kasbon)
	function hitung_grand_total_non_pr() {
		var grand_total = 0;

		$('.list_barang_pr tr').each(function() {
			var $tr = $(this);
			var $priceEl = $tr.find('.price_input');
			if ($priceEl.length) {
				var valStr = $priceEl.val() ? $priceEl.val().toString().replace(/,/g, '') : '0';
				var harga = parseFloat(valStr);
				if (isNaN(harga)) harga = 0;

				var no = $priceEl.data('no');
				var $qtyEl = $tr.find('.qty_' + no);
				if (!$qtyEl.length) {
					$qtyEl = $tr.find('input[type="hidden"][class*="qty_"]');
				}
				var qtyStr = $qtyEl.length ? $qtyEl.val() : '0';
				var qty = parseFloat(qtyStr ? qtyStr.toString().replace(/,/g, '') : '0');
				if (isNaN(qty)) qty = 0;

				var subtotal = harga * qty;

				var $subtotalEl = $tr.find('.grand_total_' + no);
				if ($subtotalEl.length) {
					$subtotalEl.val(number_format(subtotal, 0, '.', ','));
				}

				grand_total += subtotal;
			}
		});

		$('.grand_total_non_pr').html(number_format(grand_total, 2));
		update_nominal_kasbon_ui(grand_total);
	}

	$(function() {
		if ($.fn.datepicker) {
			$(".tanggal").datepicker({
				todayHighlight: true,
				format: "yyyy-mm-dd",
				showInputs: true,
				autoclose: true
			});
		}

		// Initial update badge total files
		update_total_files_badge();
	});

	function list_view() {
		if ($('#card-list').length && $('#form-data').length) {
			$('#form-data').hide().empty();
			$('#card-list, .box').fadeIn(200);
			if (typeof datatables !== 'undefined' && datatables) {
				datatables.ajax.reload(null, false);
			}
		} else {
			var target_list = (mod === '_mgt') ? 'expense/kasbon_fin_manage' : ((mod === '_fin') ? 'expense/kasbon_fin' : 'expense/kasbon');
			window.location.href = base_siteurl + target_list;
		}
	}

	function data_approve(id, status) {
		Swal.fire({
			title: "Konfirmasi Persetujuan",
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
					text: "Sedang menyimpan persetujuan kasbon...",
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				var coa = $('select[name="coa"]').val();
				var nm_coa = $('input[name="nm_coa"]').val();

				$.ajax({
					url: url_approve + id,
					data: {
						'coa': coa,
						'nm_coa': nm_coa
					},
					dataType: "json",
					type: 'POST',
					success: function(msg) {
						if (msg['save'] == '1' || msg.save == 1 || msg.save == true || msg.status == '1' || msg.status == 1) {
							Swal.fire({
								icon: "success",
								title: "Berhasil Disetujui!",
								text: msg.msg || "Pengajuan kasbon berhasil disetujui.",
								timer: 2000,
								showConfirmButton: false
							});
							list_view();
						} else {
							Swal.fire({
								icon: "error",
								title: "Gagal!",
								text: msg.msg || "Data gagal disetujui."
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: "error",
							title: "Error!",
							text: "Terjadi kesalahan koneksi saat memproses persetujuan."
						});
					}
				});
			}
		});
	}

	function data_reject() {
		var id = $("#id").val();
		if (!id) {
			Swal.fire({
				icon: "warning",
				title: "Peringatan",
				text: "ID pengajuan tidak ditemukan."
			});
			return;
		}

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
					url: base_siteurl + 'expense/reject/',
					data: {
						'id': id,
						'reason': reason,
						'table': 'tr_kasbon'
					},
					dataType: "json",
					type: 'POST',
					success: function(msg) {
						if (msg.save == 1 || msg.save == true || msg.status == 1 || msg.status == true) {
							Swal.fire({
								icon: "success",
								title: "Berhasil Ditolak!",
								text: msg.message || msg.msg || "Data pengajuan kasbon berhasil ditolak.",
								timer: 2000,
								showConfirmButton: false
							});
							list_view();
						} else {
							Swal.fire({
								icon: "error",
								title: "Gagal!",
								text: msg.message || msg.msg || "Data gagal ditolak."
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: "error",
							title: "Error!",
							text: "Terjadi kesalahan sistem saat memproses penolakan."
						});
					}
				});
			}
		});
	}
</script>
