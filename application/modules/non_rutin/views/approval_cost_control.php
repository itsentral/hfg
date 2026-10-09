<?php
$ENABLE_ADD     = has_permission('PR_Departemen.Add');
$ENABLE_MANAGE  = has_permission('PR_Departemen.Manage');
$ENABLE_VIEW    = has_permission('PR_Departemen.View');
$ENABLE_DELETE  = has_permission('PR_Departemen.Delete');
?>

<div class="card shadow-sm border-0 mb-4">
	<div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
		<h5 class="card-title mb-0 fw-bold text-primary">
			<i class="fa fa-coins me-2"></i><?= $title; ?>
		</h5>
		<div class="d-flex align-items-center gap-2">
			
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
						<th class="text-center no-sort" width="14%">Option</th>
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

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
	$(document).ready(function() {
		$('.maskM').autoNumeric();
		var tanda = $('#tanda').val();
		DataTables(tanda);
	});

	function DataTables(tanda = null) {
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
				url: base_url + active_controller + '/data_side_approval_cost_control',
				type: "post",
				data: function(d) {
					d.tanda = tanda;
				},
				cache: false,
				error: function() {
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="9">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
	}

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
</script>
