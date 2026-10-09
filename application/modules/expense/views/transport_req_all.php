<?php
$ENABLE_ADD     = has_permission('List_Transportasi.Add');
$ENABLE_MANAGE  = has_permission('List_Transportasi.Manage');
$ENABLE_VIEW    = has_permission('List_Transportasi.View') || has_permission('Pengajuan_Transportasi.View') || $this->auth->is_admin();
$ENABLE_DELETE  = has_permission('List_Transportasi.Delete');
?>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<!-- Main Card List -->
<div class="card shadow-sm border-0 mb-4" id="card-list">
	<div class="card-header bg-transparent py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
		<div class="d-flex align-items-center">
			<div class="avatar avatar-sm bg-light-primary text-primary me-2 rounded-2 d-flex align-items-center justify-content-center p-2">
				<i class="fa fa-list-check fs-5"></i>
			</div>
			<div>
				<h5 class="card-title mb-0 fw-bold text-dark">Monitoring Seluruh Pengajuan Transport</h5>
				<small class="text-muted">Rekapitulasi dan pemantauan status pengajuan penggantian biaya transport seluruh departemen</small>
			</div>
		</div>
		<div class="d-flex gap-2">
			<a href="<?= base_url('expense/transport_req'); ?>" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
				<i class="fa fa-folder-open"></i>
				<span>Pengajuan Saya</span>
			</a>
		</div>
	</div>
	<!-- /.card-header -->
	<div class="card-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-hover table-striped align-middle dt-responsive w-100">
				<thead class="table-light">
					<tr>
						<th width="40" class="text-center">#</th>
						<th>No. Dokumen</th>
						<th>Tanggal</th>
						<th>Nama Karyawan</th>
						<th>Departemen</th>
						<th>Tgl Disetujui</th>
						<th class="text-end">Total Biaya</th>
						<th class="text-center">Status</th>
						<th width="110" class="text-center">Aksi</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /.card-body -->
</div>

<!-- Dynamic Form / Detail Container -->
<div id="form-data" style="display: none;"></div>

<script type="text/javascript">
	var url_view = siteurl + 'expense/transport_req_view/';
	var datatables;

	$(document).ready(function() {
		DataTables();
	});

	function DataTables() {
		if ($.fn.DataTable.isDataTable('#mytabledata')) {
			$('#mytabledata').DataTable().destroy();
		}

		datatables = $('#mytabledata').DataTable({
			serverSide: true,
			processing: true,
			stateSave: true,
			paging: true,
			destroy: true,
			language: {
				search: "_INPUT_",
				searchPlaceholder: "Cari no dokumen, karyawan, departemen...",
				lengthMenu: "_MENU_ data per halaman",
				info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				infoEmpty: "Menampilkan 0 data",
				emptyTable: "Tidak ada data pengajuan transport yang ditemukan",
				zeroRecords: "Tidak ada data yang cocok",
				processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Sedang memuat data...'
			},
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_transport_req_all',
				cache: false,
				dataType: 'json'
			},
			columns: [
				{ data: 'no', className: 'text-center' },
				{ data: 'no_doc', className: 'fw-semibold' },
				{ data: 'tanggal' },
				{ data: 'nama', className: 'fw-medium' },
				{ data: 'department' },
				{ data: 'approval_date' },
				{ data: 'total_transport', className: 'text-end' },
				{ data: 'status', className: 'text-center' },
				{ data: 'action', className: 'text-center', orderable: false, searchable: false }
			],
			order: [[0, 'desc']]
		});
	}

	function list_view() {
		$('#form-data').hide().empty();
		$('#card-list, .box').fadeIn(200);
		if (datatables) {
			datatables.ajax.reload(null, false);
		}
	}

	function data_view(id) {
		if (id != "") {
			$('#card-list, .box').hide();
			$('#form-data').show().html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div><div class="mt-2 text-muted">Memuat detail pengajuan...</div></div>');
			$('#form-data').load(url_view + id + '/_all', function(response, status, xhr) {
				if (status == "error") {
					swal({
						title: "Error!",
						text: "Gagal memuat detail data: " + xhr.status + " " + xhr.statusText,
						type: "error"
					});
					list_view();
				} else {
					if (typeof initTransportReqForm === 'function') {
						initTransportReqForm();
					}
				}
			});
		}
	}
</script>
