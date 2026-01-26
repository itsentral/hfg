<?php
$ENABLE_ADD     = has_permission('menus.Add');
$ENABLE_MANAGE  = has_permission('menus.Manage');
$ENABLE_VIEW    = has_permission('menus.View');
$ENABLE_DELETE  = has_permission('menus.Delete');
?>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" style="display:none;" role="alert">
	<span id="alert_edit_text"></span>
	<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- ✅ DataTables Bootstrap 5 CSS (lebih cocok dengan Berry) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<div class="card">
	<div class="card-header">
		<div class="d-flex flex-column flex-md-row align-items-md-center gap-3">

			<div>
				<?php if ($ENABLE_ADD) : ?>
					<a class="btn btn-success btn-md" href="javascript:void(0)" onclick="add_data()">
						<i class="fa fa-plus me-1"></i> Add Menu
					</a>
				<?php endif; ?>
			</div>

			<div class="ms-md-auto">
				<!-- slot tombol lain kalau nanti mau export -->
			</div>

		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th style="width:60px;">#</th>
						<th>MenusID</th>
						<th>Nama Menu</th>
						<th>Link</th>
						<th>Target</th>
						<th>Group</th>
						<th>Parent</th>
						<th>Permission</th>
						<th style="width:110px;">Status</th>
						<?php if ($ENABLE_MANAGE) : ?>
							<th style="width:110px;" class="text-end">Action</th>
						<?php endif; ?>
					</tr>
				</thead>

				<tbody>
					<?php if (!empty($results)) : ?>
						<?php $numb = 0;
						foreach ($results as $record) : $numb++; ?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= $record->id ?></td>
								<td class="fw-semibold"><?= $record->title ?></td>
								<td><?= $record->link ?></td>
								<td><?= $record->target ?></td>
								<td><?= $record->group_menu ?></td>
								<td><?= $record->parent_id ?></td>
								<td><?= $record->permission_id ?></td>
								<td class="text-center">
									<?php if ($record->status == 1): ?>
										<span class="badge-status active">Active</span>
									<?php else: ?>
										<span class="badge-status inactive">Inactive</span>
									<?php endif; ?>
								</td>

								<?php if ($ENABLE_MANAGE) : ?>
									<td class="text-end">

										<?php if ($ENABLE_MANAGE) : ?>
											<a href="javascript:void(0)" class="btn-icon btn-icon-edit" title="Edit" onclick="edit_data('<?= $record->id ?>')">
												<i class="ti ti-edit"></i>
											</a>
										<?php endif; ?>

										<?php if ($ENABLE_DELETE) : ?>
											<a href="javascript:void(0)" class="btn-icon btn-icon-delete" title="Delete" onclick="delete_data('<?= $record->id ?>')">
												<i class="ti ti-trash"></i>
											</a>
										<?php endif; ?>

									</td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>

				<tfoot class="table-light">
					<tr>
						<th>#</th>
						<th>MenusID</th>
						<th>Nama Menu</th>
						<th>Link</th>
						<th>Target</th>
						<th>Group</th>
						<th>Parent</th>
						<th>Permission</th>
						<th>Status</th>
						<?php if ($ENABLE_MANAGE) : ?>
							<th class="text-end">Action</th>
						<?php endif; ?>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<!-- Form area -->
<div id="form-area" class="mt-3">
	<?php $this->load->view('menus/menus_form') ?>
</div>

<!-- ✅ Modal Bootstrap 5 -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			<div class="modal-header">
				<h5 class="modal-title">
					<i class="fa fa-file-pdf-o me-2"></i> Data Customer
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body" id="MyModalBody">
				...
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					<i class="fa fa-times me-1"></i> Tutup
				</button>
			</div>

		</div>
	</div>
</div>

<!-- ✅ DataTables (Bootstrap 5) -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script type="text/javascript">
	$(function() {
		$("#example1").DataTable({
			pageLength: 10,
			responsive: false,
			autoWidth: false
		});

		$("#form-area").hide();
	});

	function add_data() {
		var url = 'menus/create/';
		$(".card").hide(); // ✅ ganti .box jadi .card
		$("#form-area").show();
		$("#form-area").load(siteurl + url);
		$("#title").focus();
	}

	function edit_data(id) {
		if (id != "") {
			var url = 'menus/edit/' + id;
			$(".card").hide();
			$("#form-area").show();
			$("#form-area").load(siteurl + url);
			$("#title").focus();
		}
	}

	// Delete
	function delete_data(id) {
		swal({
				title: "Anda Yakin?",
				text: "Data Akan Terhapus secara Permanen!",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Yes, delete!",
				cancelButtonText: "No,cancel!",
				closeOnConfirm: false,
				closeOnCancel: true
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: siteurl + 'menus/hapus_menus/' + id,
						dataType: "json",
						type: 'POST',
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
		param = id;
		tujuan = 'customer/print_request/' + param;
		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="100%" height="450"></iframe>');

		// ✅ bootstrap 5 open modal
		var myModal = new bootstrap.Modal(document.getElementById('dialog-popup'));
		myModal.show();
	}
</script>