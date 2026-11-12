<?php
$ENABLE_ADD     = has_permission('Approval_PR_Department_Finance.Add');
$ENABLE_MANAGE  = has_permission('Approval_PR_Department_Finance.Manage');
$ENABLE_VIEW    = has_permission('Approval_PR_Department_Finance.View');
$ENABLE_DELETE  = has_permission('Approval_PR_Department_Finance.Delete');
?>

<div class="card shadow-sm border-0 mb-4">
	<div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
		<h5 class="card-title mb-0 fw-bold text-primary">
			<i class="fa fa-file-invoice-dollar me-2"></i><?= $title; ?>
		</h5>
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
						<th class="text-center no-sort">Keterangan</th>
						<th class="text-center no-sort">PIC</th>
						<th class="text-center no-sort">Created Date</th>
						<th class="text-center no-sort">Nama Pembuat PR</th>
						<th class="text-center no-sort">Tgl Dibuat PR</th>
						<th class="text-center no-sort" width="10%">Tingkat PR</th>
						<th class="text-center no-sort" width="12%">Status</th>
						<th class="text-center no-sort text-nowrap" width="13%">Option</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal View PR -->
<div class="modal fade" id="ModalView2" tabindex="-1" aria-labelledby="head_title2" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title fw-bold" id="head_title2"><?= $title; ?></h5>
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
		if ($.fn.DataTable.isDataTable('#my-grid')) {
			$('#my-grid').DataTable().destroy();
		}
		var dataTable = $('#my-grid').DataTable({
			processing: true,
			serverSide: true,
			stateSave: true,
			destroy: true,
			responsive: true,
			aaSorting: [
				[1, "asc"]
			],
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
				url: base_url + active_controller + 'server_side_non_rutin_approval_finance',
				type: "post",
				data: function(d) {
					d.tanda = tanda
				},
				cache: false,
				error: function(xhr, error, code) {
					console.error("DataTables AJAX error:", error, code);
					$(".my-grid-error").html("");
					$("#my-grid tbody").html('<tr><td colspan="11" class="text-center text-muted py-4">No data found in the server</td></tr>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
	}
</script>
