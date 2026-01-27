<?php
$ENABLE_ADD     = has_permission('TOP.Add');
$ENABLE_MANAGE  = has_permission('TOP.Manage');
$ENABLE_VIEW    = has_permission('TOP.View');
$ENABLE_DELETE  = has_permission('TOP.Delete');
?>

<div class="card shadow-sm border-0">
	<div class="card-header bg-white d-flex align-items-center justify-content-between">
		<div>
			<?php if ($ENABLE_ADD) { ?>
				<button type="button" class="btn btn-info" id="add">
					<i class="fa fa-plus me-1"></i> Add TOP
				</button>
			<?php } ?>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped table-hover align-middle w-100" id="my-grid">
				<thead class="table-light">
					<tr>
						<th style="width:60px;" class="text-center">#</th>
						<th class="text-center">Nama</th>
						<th class="text-center">Nilai</th>
						<th style="width:160px;" class="text-end">Option</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<!-- ✅ Modal BS5 -->
<div class="modal fade" id="ModalView" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">

			<div class="modal-header">
				<h5 class="modal-title" id="head_title"></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<!-- form hanya di modal biar FormData bersih -->
				<form action="#" method="POST" id="form_ct" enctype="multipart/form-data" autocomplete="off">
					<div id="view"></div>
				</form>
			</div>

			<div class="modal-footer">
				<!-- tombol save akan muncul kalau form add/edit memang butuh save -->
				<button type="button" class="btn btn-primary" id="save">
					<i class="fa fa-save me-1"></i> Save
				</button>
				<button type="button" class="btn btn-dark" data-bs-dismiss="modal">
					<i class="fa fa-times me-1"></i> Close
				</button>
			</div>

		</div>
	</div>
</div>

<script src="https://cdn.datatables.net/2.0.2/js/dataTables.min.js"></script>
<script>
	let dtTop;

	$(document).ready(function() {
		initDataTables();
	});

	function showModal() {
		const el = document.getElementById('ModalView');
		const modal = bootstrap.Modal.getOrCreateInstance(el);
		modal.show();
	}

	function hideModal() {
		const el = document.getElementById('ModalView');
		const modal = bootstrap.Modal.getOrCreateInstance(el);
		modal.hide();
	}

	// ✅ Add
	$(document).on('click', '#add', function(e) {
		e.preventDefault();

		$("#head_title").html("<b>ADD TOP</b>");
		$("#view").load(base_url + 'index.php/' + active_controller + '/add_data', function() {
			showModal();
		});
	});

	// ✅ Edit
	$(document).on('click', '.edit', function(e) {
		e.preventDefault();

		const id = $(this).data('code');
		$("#head_title").html("<b>EDIT TOP</b>");
		$("#view").load(base_url + 'index.php/' + active_controller + '/add_data/' + id, function() {
			showModal();
		});
	});

	// ✅ Save
	$(document).on('click', '#save', function() {
		const name = $("#name").val();
		const data1 = $("#data1").val();

		if (!name) {
			swal({
				title: "Error Message!",
				text: "Name Empty",
				type: "warning"
			});
			return false;
		}
		if (!data1) {
			swal({
				title: "Error Message!",
				text: "Nilai Empty",
				type: "warning"
			});
			return false;
		}

		swal({
			title: "Are you sure?",
			text: "Save this data ?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Yes, Process it!",
			cancelButtonText: "No, cancel process!",
			closeOnConfirm: true,
			closeOnCancel: false
		}, function(isConfirm) {
			if (!isConfirm) {
				swal("Cancelled", "Data can be process again", "error");
				return false;
			}

			const formData = new FormData(document.getElementById('form_ct'));
			const url = base_url + active_controller + '/add_data';

			$.ajax({
				url: url,
				type: "POST",
				data: formData,
				cache: false,
				dataType: "json",
				processData: false,
				contentType: false,
				success: function(res) {
					if (res.status == 1) {
						swal({
							title: "Save Success!",
							text: res.pesan,
							type: "success",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
						hideModal();
						// reload datatable tanpa reload page
						if (dtTop) dtTop.ajax.reload(null, false);
					} else {
						swal({
							title: "Save Failed!",
							text: res.pesan,
							type: "warning",
							timer: 5000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				},
				error: function() {
					swal({
						title: "Error Message !",
						text: "An Error Occured During Process. Please try again..",
						type: "warning",
						timer: 5000,
						showCancelButton: false,
						showConfirmButton: false,
						allowOutsideClick: false
					});
				}
			});
		});
	});

	// ✅ Delete
	$(document).on('click', '.delete', function() {
		const code = $(this).data('code');

		swal({
			title: "Are you sure?",
			text: "Delete this data ?",
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
				return false;
			}

			$.ajax({
				url: base_url + 'index.php/' + active_controller + '/hapus_data/' + code,
				type: "POST",
				cache: false,
				dataType: "json",
				success: function(res) {
					if (res.status == 1) {
						swal({
							title: "Delete Success!",
							text: res.pesan,
							type: "success",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
						if (dtTop) dtTop.ajax.reload(null, false);
					} else {
						swal({
							title: "Delete Failed!",
							text: res.pesan,
							type: "warning",
							timer: 5000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				},
				error: function() {
					swal({
						title: "Error Message !",
						text: "An Error Occured During Process. Please try again..",
						type: "warning",
						timer: 5000,
						showCancelButton: false,
						showConfirmButton: false,
						allowOutsideClick: false
					});
				}
			});
		});
	});

	// ✅ DataTables serverSide
	function initDataTables() {
		dtTop = new DataTable('#my-grid', {
			processing: true,
			serverSide: true,
			stateSave: true,
			autoWidth: true,
			destroy: true,
			responsive: true,
			language: {
				search: "<b>Live Search : </b>",
				lengthMenu: "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
				info: "Showing _START_ to _END_ of _TOTAL_ entries",
				infoFiltered: "(filtered from _MAX_ total entries)",
				zeroRecords: "No matching records found",
				emptyTable: "No data available in table",
				loadingRecords: "Please wait - loading...",
				paginate: {
					previous: "Prev",
					next: "Next"
				}
			},
			order: [
				[2, "asc"]
			],
			pageLength: 50,
			lengthMenu: [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			ajax: {
				url: base_url + 'index.php/' + active_controller + '/data_side',
				type: "POST",
				cache: false,
				error: function() {
					$("#my-grid").append('<tbody class="my-grid-error"><tr><td colspan="4">No data found in the server</td></tr></tbody>');
				}
			}
		});
	}
</script>