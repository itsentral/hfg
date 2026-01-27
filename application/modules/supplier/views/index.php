<?php
$ENABLE_ADD     = has_permission('Master_Supplier.Add');
$ENABLE_MANAGE  = has_permission('Master_Supplier.Manage');
$ENABLE_VIEW    = has_permission('Master_Supplier.View');
$ENABLE_DELETE  = has_permission('Master_Supplier.Delete');
?>

<div class="card shadow-sm border-0">
	<div class="card-header bg-white">
		<div class="d-flex flex-column flex-md-row gap-2 align-items-md-center">

			<div class="d-flex gap-2 flex-wrap">
				<?php if ($ENABLE_ADD) : ?>
					<a class="btn btn-success add_supplier" href="javascript:void(0)">
						<i class="fa fa-plus me-1"></i> Tambah Data
					</a>
				<?php endif; ?>

				<a class="btn btn-warning" href="<?= base_url('supplier/excel_report_all') ?>" target="_blank" title="Download Excel">
					<i class="fa fa-file-excel-o me-1"></i> Download Excel
				</a>
			</div>

			<div class="ms-md-auto"></div>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th style="width:60px;">#</th>
						<th>Supplier Name</th>
						<th>Country</th>
						<th>Telp</th>
						<th>Fax</th>
						<th>Email</th>
						<th>Last By</th>
						<th class="text-center">Last Date</th>
						<th style="width:140px;">Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<!-- ✅ Modal Bootstrap 5 -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">

			<form id="data-form" method="post" enctype="multipart/form-data" autocomplete="off">
				<div class="modal-header">
					<h5 class="modal-title" id="head_title">
						<i class="fa fa-users me-2"></i> Data Supplier
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body" id="ModalView">
					...
				</div>

				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" id="btnSaveSupplier">
						<i class="ti ti-device-floppy me-1"></i> Save
					</button>
					<button type="button" class="btn btn-dark" data-bs-dismiss="modal">
						<i class="ti ti-x me-1"></i> Cancel
					</button>
				</div>
			</form>

		</div>
	</div>
</div>


<script>
	var base_url = '<?= base_url(); ?>';
	var active_controller = '<?= $this->uri->segment(1); ?>';

	function initSelect2InModal() {
		$('#dialog-popup .select2').select2({
			width: '100%',
			dropdownParent: $('#dialog-popup')
		});
	}

	$(document).ready(function() {
		initTable();

		// ADD - load form into modal
		$(document).on('click', '.add_supplier', function() {
			$('#head_title').html("<i class='fa fa-plus me-2'></i> Tambah Supplier");

			$.ajax({
				type: 'POST',
				url: siteurl + 'supplier/add',
				success: function(html) {
					$('#ModalView').html(html);

					// init select2 inside modal
					$('#dialog-popup .select2').select2({
						width: '100%',
						dropdownParent: $('#dialog-popup')
					});

					// open modal (BS5)
					var myModal = new bootstrap.Modal(document.getElementById('dialog-popup'));
					myModal.show();
				}
			});
		});

		// EDIT - load form into modal
		$(document).on('click', '.edit_supplier', function() {
			const id = $(this).data('id');
			$('#head_title').html("<i class='fa fa-edit me-2'></i> Edit Supplier");

			$.ajax({
				type: 'POST',
				url: siteurl + 'supplier/edit',
				data: {
					id: id
				},
				success: function(html) {
					$('#ModalView').html(html);

					$('#dialog-popup .select2').select2({
						width: '100%',
						dropdownParent: $('#dialog-popup')
					});

					var myModal = new bootstrap.Modal(document.getElementById('dialog-popup'));
					myModal.show();
				}
			});
		});

		// ✅ SUBMIT DI INDEX (tombol Save ada di index)
		$(document).on('submit', '#data-form', function(e) {
			e.preventDefault();

			const nama = $('#nama').val();
			if (!nama) {
				swal({
					title: "Error Message!",
					text: "Supplier name empty, input dulu ...",
					type: "warning"
				});
				return;
			}

			swal({
				title: "Are you sure?",
				text: "You will not be able to process again this data!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: true,
				closeOnCancel: false
			}, function(isConfirm) {
				if (!isConfirm) {
					swal("Cancelled", "Data can be process again :)", "error");
					return;
				}

				var formData = new FormData($('#data-form')[0]);
				var url = base_url + active_controller + '/add';

				$.ajax({
					url: url,
					type: "POST",
					data: formData,
					cache: false,
					dataType: "json",
					processData: false,
					contentType: false,
					beforeSend: function() {
						$('#btnSaveSupplier').prop('disabled', true);
					},
					success: function(res) {
						if (res.status == 1) {
							swal({
								title: "Save Success!",
								text: res.pesan,
								type: "success",
								timer: 2500,
								showConfirmButton: false
							});
							window.location.href = base_url + active_controller;
						} else {
							swal({
								title: "Save Failed!",
								text: res.pesan,
								type: "warning"
							});
						}
					},
					error: function() {
						swal({
							title: "Error Message!",
							text: "An Error Occured During Process. Please try again..",
							type: "warning"
						});
					},
					complete: function() {
						$('#btnSaveSupplier').prop('disabled', false);
					}
				});

			});
		});

		// DELETE tetap
		$(document).on('click', '.delete', function(e) {
			e.preventDefault();
			var id = $(this).data('id');

			swal({
				title: "Anda Yakin?",
				text: "Data akan di hapus !",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Hapus!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			}, function() {
				$.ajax({
					type: 'POST',
					url: base_url + active_controller + '/hapus',
					dataType: "json",
					data: {
						id: id
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
								text: "Gagal hapus data",
								type: "error"
							});
						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Gagal request Ajax",
							type: "error"
						});
					}
				})
			});
		});

	});

	function initTable() {
		$('#example1').DataTable({
			// scrollY: "500px",
			scrollCollapse: true,
			processing: true,
			serverSide: true,
			stateSave: true,
			bAutoWidth: true,
			destroy: true,
			responsive: true,
			aaSorting: [
				[1, "asc"]
			],
			sPaginationType: "simple_numbers",
			iDisplayLength: 10,
			aLengthMenu: [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			ajax: {
				url: base_url + active_controller + '/get_json_supplier',
				type: "post",
				cache: false
			}
		});
	}

	function get_kota() {
		const id_prov = $("#id_prov").val();

		$.ajax({
			type: "GET",
			url: siteurl + 'supplier/getkota',
			data: {
				id_prov: id_prov
			},
			success: function(html) {
				$("#id_kabkot").html(html);
				$("#id_kec").html("<option value=''>--Pilih--</option>");
				initSelect2InModal();
			}
		});
	}


	function get_kec() {
		var id_kabkot = $("#id_kabkot").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'supplier/getkecamatan',
			data: {
				id_kabkot: id_kabkot
			},
			success: function(html) {
				$("#id_kec").html(html);
				initSelect2InModal();
			}
		});
	}
</script>