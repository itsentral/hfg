<?php
$ENABLE_ADD     = has_permission('permissions.Add');
$ENABLE_MANAGE  = has_permission('permissions.Manage');
$ENABLE_VIEW    = has_permission('permissions.View');
$ENABLE_DELETE  = has_permission('permissions.Delete');
?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<!-- Alert -->
<div id="alert_edit" class="alert alert-success d-none" style="padding: 15px;"></div>

<!-- TABLE AREA -->
<div id="table-area">

	<div class="card">
		<div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
			<div class="d-flex gap-2">
				<?php if ($ENABLE_ADD) : ?>
					<button class="btn btn-success" type="button" onclick="add_data()">
						<i class="fa fa-plus me-1"></i> Add Permission
					</button>
				<?php endif; ?>
			</div>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table id="example1" class="table table-hover align-middle mb-0 w-100">
					<thead class="table-light">
						<tr>
							<th style="width:60px">#</th>
							<th>Permission ID</th>
							<th>Nama Permission</th>
							<th>Nama Menu</th>
							<th>Jenis</th>
							<?php if ($ENABLE_MANAGE) : ?>
								<th style="width:120px" class="text-end">Action</th>
							<?php endif; ?>
						</tr>
					</thead>

					<tbody>
						<?php if (!empty($results)) : ?>
							<?php $numb = 0;
							foreach ($results as $record) {
								$numb++; ?>
								<tr>
									<td class="text-muted"><?= $numb; ?></td>
									<td>
										<span class="fw-semibold"><?= $record->id_permission ?></span>
									</td>
									<td><?= $record->nm_permission ?></td>
									<td><?= $record->nm_menu ?></td>
									<td>
										<span class="badge rounded-pill bg-info">
											<?= $record->ket ?>
										</span>
									</td>

									<?php if ($ENABLE_MANAGE) : ?>
										<td class="text-end">

											<?php if ($ENABLE_MANAGE) : ?>
												<button type="button"
													class="btn-icon btn-icon-edit"
													title="Edit"
													onclick="edit_data('<?= $record->id_permission ?>')">
													<i class="ti ti-edit"></i>
												</button>
											<?php endif; ?>

											<?php if ($ENABLE_DELETE) : ?>
												<button type="button"
													class="btn-icon btn-icon-delete"
													title="Delete"
													onclick="delete_data('<?= $record->id_permission ?>')">
													<i class="ti ti-trash"></i>
												</button>
											<?php endif; ?>

										</td>
									<?php endif; ?>
								</tr>
							<?php } ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

</div>

<!-- FORM AREA -->
<div id="form-area" class="mt-3">
	<?php $this->load->view('permissions/permissions_form'); ?>
</div>

<!-- MODAL (optional) -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">
					<i class="ti ti-file-text"></i> Data Customer
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="MyModalBody">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">
					<i class="ti ti-x"></i> Tutup
				</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
	$(function() {

		// hide form first
		$("#form-area").hide();

		// Datatable init (Berry style friendly)
		$("#example1").DataTable({
			responsive: true,
			pageLength: 10,
			lengthChange: true,
			autoWidth: false
		});

	});

	function add_data() {
		var url = 'permissions/create/';
		$("#table-area").hide();
		$("#form-area").show();
		$("#form-area").load(siteurl + url);
	}

	function edit_data(id) {
		if (id) {
			var url = 'permissions/edit/' + id;
			$("#table-area").hide();
			$("#form-area").show();
			$("#form-area").load(siteurl + url);
		}
	}

	function delete_data(id) {
		swal({
			title: "Anda Yakin?",
			text: "Data Akan Terhapus secara Permanen!",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Ya, delete!",
			cancelButtonText: "Tidak!",
			closeOnConfirm: false,
			closeOnCancel: true
		}, function(isConfirm) {
			if (isConfirm) {
				$.ajax({
					url: siteurl + 'permissions/hapus_permissions/' + id,
					dataType: "json",
					type: "POST",
					success: function(msg) {
						if (msg['delete'] == '1') {
							swal({
								title: "Terhapus!",
								text: "Data berhasil dihapus",
								type: "success",
								timer: 1500,
								showConfirmButton: false
							});
							window.location.reload();
						} else {
							swal({
								title: "Gagal!",
								text: "Data gagal dihapus",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});
						}
					},
					error: function() {
						swal({
							title: "Gagal!",
							text: "Gagal Eksekusi Ajax",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					}
				});
			}
		});
	}

	function PreviewPdf(id) {
		var tujuan = 'customer/print_request/' + id;
		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="0" width="100%" height="450"></iframe>');
		// kalau pake bootstrap 5:
		// new bootstrap.Modal(document.getElementById('dialog-popup')).show();
	}
</script>