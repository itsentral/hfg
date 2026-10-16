<?php
$ENABLE_ADD     = has_permission('Kasbon_List.Add');
$ENABLE_MANAGE  = has_permission('Kasbon_List.Manage');
$ENABLE_VIEW    = has_permission('Kasbon_List.View');
$ENABLE_DELETE  = has_permission('Kasbon_List.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<div class="card shadow-sm border-0 mb-4 box">
	<div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
		<h5 class="card-title mb-0 fw-bold text-primary">
			<i class="fa fa-list-alt me-2"></i>Daftar Kasbon (All)
		</h5>
	</div>
	<!-- /.card-header -->
	<div class="card-body">
		<div class="table-responsive">
			<table id="mytabledata2" class="table table-bordered table-striped table-hover align-middle w-100">
				<thead class="table-light text-center">
					<tr>
						<th width="4%" class="text-center">#</th>
						<th class="text-center">No Kasbon</th>
						<th class="text-center">Tanggal</th>
						<th class="text-center">Nama</th>
						<th class="text-center">Keperluan</th>
						<th class="text-center" width="12%">Approval Date</th>
						<th class="text-center" width="15%">Status</th>
						<th class="text-center text-nowrap" width="12%">Action</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /.card-body -->
</div>

<div id="form-data"></div>

<!-- Scripts -->
<script type="text/javascript">
	var url_view = siteurl + 'expense/kasbon_view/';

	$(document).ready(function() {
		datatables();
	});

	function datatables() {
		$('#mytabledata2').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_dat_kasbon_list',
				cache: false,
				dataType: 'json',
				error: function(xhr, status, error) {
					console.error("DataTable AJAX error: " + status + ": " + error);
				}
			},
			columns: [
				{
					data: 'no',
					className: 'text-center'
				},
				{
					data: 'no_kasbon',
					className: 'text-center fw-bold'
				},
				{
					data: 'tanggal',
					className: 'text-center'
				},
				{
					data: 'nama'
				},
				{
					data: 'keperluan'
				},
				{
					data: 'approval_date',
					className: 'text-center'
				},
				{
					data: 'status',
					className: 'text-center'
				},
				{
					data: 'action',
					className: 'text-center text-nowrap',
					orderable: false
				}
			]
		});
	}
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
