<?php
$ENABLE_ADD     = has_permission('PR_Departemen.Add');
$ENABLE_MANAGE  = has_permission('PR_Departemen.Manage');
$ENABLE_VIEW    = has_permission('PR_Departemen.View');
$ENABLE_DELETE  = has_permission('PR_Departemen.Delete');
?>

<div class="card shadow-sm border-0 mb-4">
	<div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
		<h5 class="card-title mb-0 fw-bold text-primary">
			<i class="fa fa-clipboard-list me-2"></i><?= $title; ?>
		</h5>
		<div class="d-flex align-items-center gap-2">
			<?php if ($ENABLE_ADD) : ?>
				<a href="<?= site_url('non_rutin/add') ?>" class="btn btn-sm btn-success" id="btn-add">
					<i class="fa fa-plus me-1"></i> Add
				</a>
			<?php endif; ?>
		</div>
	</div>
	<div class="card-body">
		<input type="hidden" id="tanda" value="<?= $tanda; ?>">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover align-middle w-100" id="my-grid">
				<thead class="table-primary text-center">
					<tr>
						<th class="text-center" width="4%">#</th>
						<th class="text-center">No PR</th>
						<th class="text-center">Departemen</th>
						<th class="text-center no-sort">Keterangan Project</th>
						<th class="text-center no-sort" width="10%">Tingkat PR</th>
						<th class="text-center no-sort">PIC</th>
						<th class="text-center no-sort">Created Date</th>
						<th class="text-center no-sort" width="12%">Status</th>
						<th class="text-center no-sort text-nowrap" width="15%">Option</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal View Detail -->
<div class="modal fade" id="ModalView2" tabindex="-1" aria-labelledby="head_title2" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title fw-bold" id="head_title2">Detail Non Rutin</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="view2"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal Closing PR -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title fw-bold text-danger" id="myModalLabel"><i class="fa fa-times-circle me-1"></i> Closing PR</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="" method="post" id="frm-data">
				<div class="modal-body" id="ModalView">...</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-sm btn-danger">Close PR</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
	$(document).ready(function() {
		$('.maskM').autoNumeric();
		DataTables();
	});

	$(document).on('click', '.close_pr_modal', function() {
		var no_pengajuan = $(this).data('no_pengajuan');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'close_pr_modal',
			data: {
				'no_pengajuan': no_pengajuan
			},
			cache: false,
			success: function(result) {
				$('#ModalView').html(result);
				if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
					var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('dialog-popup'));
					myModal.show();
				} else {
					$('#dialog-popup').modal('show');
				}
			},
			error: function(result) {
				Swal.fire({
					title: 'Error !',
					text: 'Please try again later !',
					icon: 'error'
				});
			}
		});
	});

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		var reject_reason = $('#reject_reason').val();
		if (reject_reason == '') {
			Swal.fire({
				title: 'Warning !',
				text: 'Please make sure the reject reason is not empty !',
				icon: 'warning'
			});
			return false;
		}

		Swal.fire({
			title: 'Are you sure ?',
			text: 'This PR will be closed !',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes',
			cancelButtonText: 'No'
		}).then((result) => {
			if (result.isConfirmed) {
				var data = new FormData($('#frm-data')[0]);
				$.ajax({
					type: 'POST',
					url: siteurl + active_controller + 'close_pr',
					data: data,
					cache: false,
					dataType: 'json',
					processData: false,
					contentType: false,
					success: function(result) {
						if (result.status == 1) {
							Swal.fire({
								title: 'Success !',
								text: result.msg,
								icon: 'success'
							}).then((result) => {
								if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
									var myModal = bootstrap.Modal.getInstance(document.getElementById('dialog-popup'));
									if (myModal) myModal.hide();
								} else {
									$('#dialog-popup').modal('hide');
								}
								DataTables();
							});
						} else {
							Swal.fire({
								title: 'Failed !',
								text: result.msg,
								icon: 'error'
							});
						}
					},
					error: function(result) {
						Swal.fire({
							title: 'Error !',
							text: 'Please try again later !',
							icon: 'error'
						});
					}
				});
			}
		});
	});

	$(document).on('click', '.detail', function() {
		var no_pengajuan = $(this).data('no_pengajuan');
		var pr_rev = $(this).data('pr_rev');
		loading_spinner();
		$('#head_title2').html("Detail Non Rutin");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + 'detail',
			data: {
				'no_pengajuan': no_pengajuan,
				'pr_rev': pr_rev
			},
			success: function(data) {
				if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
					var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('ModalView2'));
					modal.show();
				} else {
					$("#ModalView2").modal('show');
				}
				$("#view2").html(data);
				swal.close();
			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Timed Out ...',
					type: "warning",
					timer: 5000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	$(document).on('click', '.hapus', function() {
		var no_pengajuan = $(this).data('no_pengajuan');
		Swal.fire({
			title: "Are you sure?",
			text: "Delete this data ?",
			icon: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Yes, Process it!",
			cancelButtonText: "No, cancel process!",
			closeOnConfirm: true,
			closeOnCancel: false
		}).then((isConfirm) => {
			if (isConfirm.isConfirmed) {
				loading_spinner();
				$.ajax({
					url: base_url + active_controller + 'hapus/' + no_pengajuan,
					type: "POST",
					cache: false,
					dataType: 'json',
					processData: false,
					contentType: false,
					success: function(data) {
						if (data.status == 1) {
							Swal.fire({
								title: "Save Success!",
								text: data.pesan,
								icon: "success",
								timer: 7000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
							DataTables();
						} else if (data.status == 0) {
							Swal.fire({
								title: "Save Failed!",
								text: data.pesan,
								icon: "warning",
								timer: 7000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
						}
					},
					error: function() {
						Swal.fire({
							title: "Error Message !",
							text: 'An Error Occured During Process. Please try again..',
							icon: "warning",
							timer: 7000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			} else {
				Swal.fire("Cancelled", "Data can be process again :)", "error");
				return false;
			}
		});
	});

	function DataTables(tanda = null) {
		if ($.fn.DataTable.isDataTable('#my-grid')) {
			$('#my-grid').DataTable().destroy();
		}
		var dataTable = $('#my-grid').DataTable({
			processing: true,
			serverSide: true,
			stateSave: true,
			destroy: true,
			responsive: true,
			aaSorting: [[1, "asc"]],
			columnDefs: [{
				targets: 'no-sort',
				orderable: false,
			}],
			iDisplayLength: 10,
			aLengthMenu: [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			ajax: {
				url: siteurl + active_controller + 'get_data_non_rutin',
				type: "get",
				cache: false,
				error: function(xhr, error, code) {
					console.error("DataTables AJAX error:", error, code);
					$(".my-grid-error").html("");
					$("#my-grid tbody").html('<tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data ditemukan di server</td></tr>');
					$("#my-grid_processing").css("display", "none");
				}
			},
			columns: [
				{ data: 'no', className: 'text-center' },
				{ data: 'no_pr' },
				{ data: 'departemen' },
				{ data: 'keterangan' },
				{ data: 'tingkat_pr', className: 'text-center' },
				{ data: 'pic' },
				{ data: 'created_date' },
				{ data: 'status', className: 'text-center' },
				{ data: 'option', className: 'text-center text-nowrap', orderable: false, searchable: false }
			]
		});
	}
</script>
