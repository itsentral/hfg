<?php
$ENABLE_ADD     = has_permission('Master_forwarding_cost.Add');
$ENABLE_MANAGE  = has_permission('Master_forwarding_cost.Manage');
$ENABLE_VIEW    = has_permission('Master_forwarding_cost.View');
$ENABLE_DELETE  = has_permission('Master_forwarding_cost.Delete');
?>


<style>
	.swal2-container.swal2-center {
		z-index: 9999 !important;
	}

	.swal2-popup {
		z-index: 10000 !important;
	}
</style>
<div class="container-fluid mt-3">
	<div class="card shadow-sm">
		<div class="card-header bg-primary text-white">
			<h5 class="mb-0">
				<i class="fa fa-dollar-sign me-2"></i> Master Forwarding Cost
			</h5>
		</div>
		<div class="card-body">
			<div id="btnAddWrapper" class="mb-3 text-end">
				<?php if (empty($forwarding_cost)): ?>
					<button type="button" class="btn btn-success" id="btnTambah">
						<i class="fa fa-plus"></i> Tambah Forwarding Cost
					</button>
				<?php endif; ?>
			</div>

			<!-- Tabel Data -->
			<div class="table-responsive">
				<table class="table table-bordered table-hover" id="tabelForwardingCost">
					<thead class="table-light">
						<tr>
							<th width="5%">#</th>
							<th>Value Cost (Rp)</th>
							<th>Remark</th>
							<th>Last Update</th>
							<th width="15%">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($forwarding_cost)): ?>
							<?php $no = 1;
							foreach ($forwarding_cost as $row): ?>
								<tr>
									<td><?= $no++ ?></td>
									<td class="value-cost"><?= number_format($row->value_cost, 0, ',', '.') ?></td>
									<td><?= htmlspecialchars(!empty($row->remark) ? $row->remark : '-') ?></td>
									<td>
										<?= htmlspecialchars($row->create_by) ?><br>
										<small><i><?= date('d-m-Y H:i:s', strtotime($row->update_date)) ?></i></small>
									</td>
									<td>
										<button type="button" class="btn btn-warning btn-sm btn-edit"
											data-id="<?= $row->id ?>"
											data-value="<?= $row->value_cost ?>"
											data-remark="<?= htmlspecialchars($row->remark) ?>">
											<i class="fa fa-edit"></i> Edit
										</button>
										<button type="button" class="btn btn-danger btn-sm btn-delete"
											data-id="<?= $row->id ?>">
											<i class="fa fa-trash"></i> Hapus
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr id="rowEmpty">
								<td colspan="5" class="text-center text-muted py-4">
									<i class="fa fa-info-circle"></i> Belum ada data Forwarding Cost. Silakan tambah data.
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Modal Form Tambah/Edit -->
<div class="modal fade" id="modalForm" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title" id="modalTitle">
					<i class="fa fa-plus-circle"></i> Tambah Forwarding Cost
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
			</div>
			<form id="formForwardingCost">
				<div class="modal-body">
					<input type="hidden" name="id" id="id">

					<div class="mb-3">
						<label for="value_cost" class="form-label fw-bold">
							Value Cost <span class="text-danger">*</span>
						</label>
						<input type="text" name="value_cost" id="value_cost"
							class="form-control" placeholder="Masukkan nilai forwarding cost"
							autocomplete="off" required>
						<div class="invalid-feedback">Nilai harus diisi dengan angka</div>
					</div>

					<div class="mb-3">
						<label for="remark" class="form-label fw-bold">Remark / Keterangan</label>
						<textarea name="remark" id="remark" class="form-control" rows="3" placeholder="masukkan remark jika ada"></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						<i class="fa fa-times"></i> Batal
					</button>
					<button type="submit" class="btn btn-primary" id="btnSave">
						<i class="fa fa-save"></i> Simpan
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
	$(document).ready(function() {
		function formatNumber(num) {
			return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
		}

		function unformatNumber(str) {
			return parseFloat(str.replace(/\./g, '')) || 0;
		}

		$('#value_cost').on('keyup', function() {
			let value = $(this).val().replace(/\./g, '');
			if (!isNaN(value) && value !== '') {
				$(this).val(formatNumber(value));
			}
		});

		$('#btnTambah').on('click', function() {
			$('#modalTitle').html('<i class="fa fa-plus-circle"></i> Tambah Forwarding Cost');
			$('#formForwardingCost')[0].reset();
			$('#id').val('');
			$('#value_cost').val('');
			$('#remark').val('');
			$('.is-invalid').removeClass('is-invalid');
			$('#modalForm').modal('show');
		});

		$('.btn-edit').on('click', function() {
			let id = $(this).data('id');
			let value = $(this).data('value');
			let remark = $(this).data('remark');

			$('#modalTitle').html('<i class="fa fa-edit"></i> Edit Forwarding Cost');
			$('#id').val(id);
			$('#value_cost').val(formatNumber(value.toString()));
			$('#remark').val(remark);
			$('.is-invalid').removeClass('is-invalid');
			$('#modalForm').modal('show');
		});

		$('#formForwardingCost').on('submit', function(e) {
			e.preventDefault();

			let id = $('#id').val();
			let value_cost_raw = $('#value_cost').val();
			let value_cost = unformatNumber(value_cost_raw);
			let remark = $('#remark').val();

			if (!value_cost || value_cost <= 0) {
				Swal.fire({
					icon: 'error',
					title: 'Validasi Gagal',
					text: 'Value Cost harus diisi dengan angka yang valid (lebih dari 0)!',
					confirmButtonColor: '#d33'
				});
				$('#value_cost').addClass('is-invalid');
				return;
			}
			$('#value_cost').removeClass('is-invalid');

			$('#btnSave').prop('disabled', true);
			$('#btnSave').html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

			$.ajax({
				url: '<?= base_url("master_forwarding_cost/save") ?>',
				type: 'POST',
				data: {
					id: id,
					value_cost: value_cost,
					remark: remark
				},
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil!',
							text: response.message,
							timer: 1500,
							showConfirmButton: false
						}).then(() => {
							location.reload();
						});
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Gagal!',
							text: response.message
						});
					}
				},
				error: function() {
					Swal.fire({
						icon: 'error',
						title: 'Error!',
						text: 'Terjadi kesalahan pada server. Silakan coba lagi.'
					});
				},
				complete: function() {
					$('#btnSave').prop('disabled', false);
					$('#btnSave').html('<i class="fa fa-save"></i> Simpan');
				}
			});
		});

		$('.btn-delete').on('click', function() {
			let id = $(this).data('id');

			Swal.fire({
				title: 'Apakah Anda yakin?',
				text: "Data yang dihapus tidak dapat dikembalikan!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					Swal.fire({
						title: 'Menghapus...',
						text: 'Silakan tunggu',
						allowOutsideClick: false,
						didOpen: () => {
							Swal.showLoading();
						}
					});

					$.ajax({
						url: '<?= base_url("master_forwarding_cost/delete/") ?>' + id,
						type: 'POST',
						dataType: 'json',
						success: function(response) {
							if (response.status === 'success') {
								Swal.fire({
									icon: 'success',
									title: 'Terhapus!',
									text: response.message,
									timer: 1500,
									showConfirmButton: false
								}).then(() => {
									location.reload();
								});
							} else {
								Swal.fire({
									icon: 'error',
									title: 'Gagal!',
									text: response.message
								});
							}
						},
						error: function() {
							Swal.fire({
								icon: 'error',
								title: 'Error!',
								text: 'Terjadi kesalahan pada server. Silakan coba lagi.'
							});
						}
					});
				}
			});
		});
	});
</script>