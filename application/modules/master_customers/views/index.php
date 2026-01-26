<?php
$ENABLE_ADD     = has_permission('Master_customer.Add');
$ENABLE_MANAGE  = has_permission('Master_customer.Manage');
$ENABLE_VIEW    = has_permission('Master_customer.View');
$ENABLE_DELETE  = has_permission('Master_customer.Delete');
?>

<!-- Alert -->
<div id="alert_edit" class="alert alert-success alert-dismissible fade show" style="display:none;" role="alert">
	<span id="alert_edit_text"></span>
	<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- ✅ DataTables Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<style>
	/* optional: kalau kamu mau filter column header */
	thead input {
		width: 100%;
	}
</style>

<!-- ✅ Tabs Bootstrap 5 -->
<ul class="nav nav-tabs mb-3" id="customerTabs" role="tablist">
	<li class="nav-item" role="presentation">
		<button class="nav-link active" id="tab-local" data-bs-toggle="tab" data-bs-target="#customer" type="button" role="tab">
			Local Customer
		</button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="tab-category" data-bs-toggle="tab" data-bs-target="#customer_category" type="button" role="tab">
			Master Customer Category
		</button>
	</li>
</ul>

<div class="tab-content">
	<!-- ==========================
		 TAB 1: LOCAL CUSTOMER
	=========================== -->
	<div class="tab-pane fade show active" id="customer" role="tabpanel" aria-labelledby="tab-local">

		<div class="card">
			<div class="card-header">
				<div class="d-flex flex-column flex-md-row align-items-md-center gap-2">

					<div class="d-flex gap-2 flex-wrap">
						<?php if ($ENABLE_VIEW) : ?>
							<a class="btn btn-success btn-md" href="<?= base_url('master_customers/add') ?>">
								<i class="fa fa-plus me-1"></i> Tambah Data
							</a>
						<?php endif; ?>

						<a class="btn btn-warning btn-md" href="<?= base_url('master_customers/excel_report_all') ?>" target="_blank" title="Download Excel">
							<i class="fas fa-file-excel"></i> Download Excel
						</a>
					</div>

					<div class="ms-md-auto">
						<!-- slot tombol tambahan jika perlu -->
					</div>

				</div>
			</div>

			<div class="card-body">
				<div class="table-responsive">
					<table id="tblCustomer" class="table table-striped table-hover align-middle w-100">
						<thead class="table-light">
							<tr>
								<th style="width:60px;">#</th>
								<th style="width:160px;">Id Customer</th>
								<th>Nama Customer</th>
								<th>Sales</th>
								<th hidden>Kategori Customer</th>
								<th style="width:120px;">Status</th>

								<?php if ($ENABLE_MANAGE) : ?>
									<th style="width:140px;" class="text-end">Action</th>
								<?php endif; ?>
							</tr>
						</thead>

						<tbody>
							<?php if (!empty($results['customer'])) : ?>
								<?php $numb = 0;
								foreach ($results['customer'] as $customer) : $numb++; ?>
									<tr>
										<td><?= $numb; ?></td>
										<td><?= $customer->id_customer ?></td>
										<td class="fw-semibold"><?= $customer->name_customer ?></td>
										<td><?= ucfirst($customer->nama_karyawan) ?></td>

										<td hidden>
											<?php
											$id = $customer->id_customer;
											$cate = $this->db->get_where('child_category_customer', ['id_customer' => $id])->result();
											foreach ($cate as $vp) {
												echo $vp->name_category_customer . "<br>";
											}
											?>
										</td>

										<td class="text-center">
											<?php if ($customer->activation == 'aktif') : ?>
												<span class="badge-status active">Aktif</span>
											<?php else : ?>
												<span class="badge-status inactive">Non Aktif</span>
											<?php endif; ?>
										</td>

										<?php if ($ENABLE_MANAGE) : ?>
											<td class="text-end">
												<?php if ($ENABLE_VIEW) : ?>
													<a class="btn-icon btn-icon-view view_local" href="javascript:void(0)"
														title="View" data-id_customer="<?= $customer->id_customer ?>">
														<i class="ti ti-eye"></i>
													</a>
												<?php endif; ?>

												<?php if ($ENABLE_MANAGE) : ?>
													<a class="btn-icon btn-icon-edit edit_local" href="javascript:void(0)"
														title="Edit" data-id_customer="<?= $customer->id_customer ?>">
														<i class="ti ti-edit"></i>
													</a>
												<?php endif; ?>

												<?php if ($ENABLE_DELETE) : ?>
													<a class="btn-icon btn-icon-delete delete_local" href="javascript:void(0)"
														title="Delete" data-id_customer="<?= $customer->id_customer ?>">
														<i class="ti ti-trash"></i>
													</a>
												<?php endif; ?>
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>

		</div>
	</div>

	<!-- ==========================
		 TAB 2: CUSTOMER CATEGORY
	=========================== -->
	<div class="tab-pane fade" id="customer_category" role="tabpanel" aria-labelledby="tab-category">

		<div class="card">
			<div class="card-header">
				<div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
					<div>
						<?php if ($ENABLE_VIEW) : ?>
							<a class="btn btn-success btn-md add_category" href="javascript:void(0)">
								<i class="fa fa-plus me-1"></i> Tambah Data
							</a>
						<?php endif; ?>
					</div>
					<div class="ms-md-auto"></div>
				</div>
			</div>

			<div class="card-body">
				<div class="table-responsive">
					<table id="tblCategory" class="table table-striped table-hover align-middle w-100">
						<thead class="table-light">
							<tr>
								<th style="width:60px;">#</th>
								<th hidden>Id Category</th>
								<th hidden>Kode Kategori</th>
								<th>Nama Kategori</th>
								<th style="width:120px;">Status</th>

								<?php if ($ENABLE_MANAGE) : ?>
									<th style="width:140px;" class="text-end">Aksi</th>
								<?php endif; ?>
							</tr>
						</thead>

						<tbody>
							<?php if (!empty($results['category'])) : ?>
								<?php $numb3 = 0;
								foreach ($results['category'] as $category) : $numb3++; ?>
									<tr>
										<td><?= $numb3; ?></td>
										<td hidden><?= $category->id_category_customer ?></td>
										<td hidden><?= $category->customer_code ?></td>
										<td class="fw-semibold"><?= $category->name_category_customer ?></td>

										<td class="text-center">
											<?php if ($category->activation == 'active') : ?>
												<span class="badge-status active">Aktif</span>
											<?php else : ?>
												<span class="badge-status inactive">Non Aktif</span>
											<?php endif; ?>
										</td>

										<?php if ($ENABLE_MANAGE) : ?>
											<td class="text-end">
												<?php if ($ENABLE_VIEW) : ?>
													<a class="btn-icon btn-icon-view view_category" href="javascript:void(0)"
														title="View" data-id_category_customer="<?= $category->id_category_customer ?>">
														<i class="ti ti-eye"></i>
													</a>
												<?php endif; ?>

												<?php if ($ENABLE_MANAGE) : ?>
													<a class="btn-icon btn-icon-edit edit_category" href="javascript:void(0)"
														title="Edit" data-id_category_customer="<?= $category->id_category_customer ?>">
														<i class="ti ti-edit"></i>
													</a>
												<?php endif; ?>

												<?php if ($ENABLE_DELETE) : ?>
													<a class="btn-icon btn-icon-delete delete_category" href="javascript:void(0)"
														title="Delete" data-id_category_customer="<?= $category->id_category_customer ?>">
														<i class="ti ti-trash"></i>
													</a>
												<?php endif; ?>
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>

					</table>
				</div>
			</div>

		</div>
	</div>
</div>

<!-- ✅ Modal Bootstrap 5 (Popup) -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			<div class="modal-header">
				<h5 class="modal-title" id="head_title">
					<i class="fa fa-users me-2"></i> Data Customer
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body" id="ModalView">
				...
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					<i class="fa fa-times me-1"></i> Close
				</button>
			</div>

		</div>
	</div>
</div>

<!-- ✅ Modal Bootstrap 5 (Rekap) -->
<div class="modal fade" id="dialog-rekap" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			<div class="modal-header">
				<h5 class="modal-title">
					<i class="fa fa-file-pdf-o me-2"></i> Rekap Data Customer
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body" id="MyModalBody">
				...
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					<i class="fa fa-times me-1"></i> Close
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
		// init datatables: customer
		$('#tblCustomer').DataTable({
			pageLength: 10,
			autoWidth: false
		});

		// init datatables: category
		$('#tblCategory').DataTable({
			pageLength: 10,
			autoWidth: false
		});
	});

	/* ==========================
		MODAL HANDLER (BS5)
	========================== */
	function openModal(titleHtml, contentHtml) {
		$("#head_title").html(titleHtml);
		$("#ModalView").html(contentHtml);

		const modalEl = document.getElementById('dialog-popup');
		const modal = new bootstrap.Modal(modalEl);
		modal.show();
	}

	/* ==========================
		AJAX ACTIONS
	========================== */
	$(document).on('click', '.edit_category', function(e) {
		var id = $(this).data('id_category_customer');
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_customers/EditCategory/' + id,
			success: function(data) {
				openModal("<i class='fa fa-list-alt me-2'></i><b>Edit Data</b>", data);
			}
		});
	});

	$(document).on('click', '.view_category', function() {
		var id = $(this).data('id_category_customer');
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_customers/viewCategory/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				openModal("<i class='fa fa-list-alt me-2'></i><b>Detail Data</b>", data);
			}
		});
	});

	$(document).on('click', '.edit_local', function(e) {
		var id = $(this).data('id_customer');
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_customers/editCustomer/' + id,
			success: function(data) {
				openModal("<i class='fa fa-list-alt me-2'></i><b>Edit Data</b>", data);
			}
		});
	});

	$(document).on('click', '.view_local', function() {
		var id = $(this).data('id_customer');
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_customers/viewCustomer/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				openModal("<i class='fa fa-list-alt me-2'></i><b>Detail Data</b>", data);
			}
		});
	});

	$(document).on('click', '.add_category', function() {
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_customers/addCategory',
			success: function(data) {
				openModal("<i class='fa fa-list-alt me-2'></i><b>Tambah Data</b>", data);
			}
		});
	});

	/* ==========================
		DELETE ACTIONS
	========================== */
	$(document).on('click', '.delete_category', function(e) {
		e.preventDefault();
		var id = $(this).data('id_category_customer');

		swal({
			title: "Anda Yakin?",
			text: "Data akan di hapus.",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-info",
			confirmButtonText: "Ya, Hapus!",
			cancelButtonText: "Batal",
			closeOnConfirm: false
		}, function() {
			$.ajax({
				type: 'POST',
				url: siteurl + 'master_customers/deleteCategory',
				dataType: "json",
				data: {
					'id': id
				},
				success: function(result) {
					if (result.status == '1') {
						swal({
							title: "Sukses",
							text: "Data berhasil dihapus.",
							type: "success"
						}, function() {
							window.location.reload(true);
						});
					} else {
						swal({
							title: "Error",
							text: "Data error. Gagal hapus data",
							type: "error"
						});
					}
				},
				error: function() {
					swal({
						title: "Error",
						text: "Data error. Gagal request Ajax",
						type: "error"
					});
				}
			});
		});
	});

	$(document).on('click', '.delete_local', function(e) {
		e.preventDefault();
		var id = $(this).data('id_customer');

		swal({
			title: "Anda Yakin?",
			text: "Data akan di hapus.",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-info",
			confirmButtonText: "Ya, Hapus!",
			cancelButtonText: "Batal",
			closeOnConfirm: false
		}, function() {
			$.ajax({
				type: 'POST',
				url: siteurl + 'master_customers/deletelokal',
				dataType: "json",
				data: {
					'id': id
				},
				success: function(result) {
					if (result.status == '1') {
						swal({
							title: "Sukses",
							text: "Data berhasil dihapus.",
							type: "success"
						}, function() {
							window.location.reload(true);
						});
					} else {
						swal({
							title: "Error",
							text: "Data error. Gagal hapus data",
							type: "error"
						});
					}
				},
				error: function() {
					swal({
						title: "Error",
						text: "Data error. Gagal request Ajax",
						type: "error"
					});
				}
			});
		});
	});

	/* ==========================
		PREVIEW PDF (BS5)
	========================== */
	function PreviewPdf(id) {
		let tujuan = 'customer/print_request/' + id;
		$("#MyModalBody").html('<iframe src="' + tujuan + '" frameborder="0" width="100%" height="450"></iframe>');

		const modalEl = document.getElementById('dialog-rekap');
		const modal = new bootstrap.Modal(modalEl);
		modal.show();
	}

	function PreviewRekap() {
		let tujuan = 'customer/rekap_pdf';
		$("#MyModalBody").html('<iframe src="' + tujuan + '" frameborder="0" width="100%" height="450"></iframe>');

		const modalEl = document.getElementById('dialog-rekap');
		const modal = new bootstrap.Modal(modalEl);
		modal.show();
	}
</script>