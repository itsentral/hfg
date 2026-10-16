<?php
$ENABLE_ADD     = has_permission('Kasbon.Add');
$ENABLE_MANAGE  = has_permission('Kasbon.Manage');
$ENABLE_VIEW    = has_permission('Kasbon.View');
$ENABLE_DELETE  = has_permission('Kasbon.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<div class="card shadow-sm border-0 mb-4 box">
	<div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
		<h5 class="card-title mb-0 fw-bold text-primary">
			<i class="fa fa-money-bill-wave me-2"></i>Daftar Kasbon
		</h5>
		<?php if ($ENABLE_ADD) : ?>
			<div>
				<button class="btn btn-success btn-sm shadow-sm" type="button" onclick="data_add()">
					<i class="fa fa-plus me-1"></i> Tambah Kasbon
				</button>
			</div>
		<?php endif; ?>
	</div>
	<!-- /.card-header -->
	<div class="card-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped table-hover align-middle w-100">
				<thead class="table-light text-center">
					<tr>
						<th width="4%" class="text-center">#</th>
						<th class="text-center">No Kasbon</th>
						<th class="text-center">Tanggal</th>
						<th class="text-center">Nama</th>
						<th class="text-center">Keperluan</th>
						<th class="text-center" width="16%">Status</th>
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

<!-- DataTables & Scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script type="text/javascript">
	var url_add = siteurl + 'expense/kasbon_create/';
	var url_edit = siteurl + 'expense/kasbon_edit/';
	var url_delete = siteurl + 'expense/kasbon_delete/';
	var url_view = siteurl + 'expense/kasbon_view/';

	$(document).ready(function() {
		datatables();

		if ($.fn.chosen) {
			$('.chosen_select').chosen({
				width: '100%'
			});
		}
	});

	function datatables() {
		$('#mytabledata').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			stateSave: false,
			ajax: {
				type: 'POST',
				url: siteurl + active_controller + 'get_dat_list_kasbon',
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
