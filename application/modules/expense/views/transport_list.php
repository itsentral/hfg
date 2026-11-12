<?php
$ENABLE_ADD     = has_permission('Transportasi.Add');
$ENABLE_MANAGE  = has_permission('Transportasi.Manage');
$ENABLE_VIEW    = has_permission('Transportasi.View');
$ENABLE_DELETE  = has_permission('Transportasi.Delete');
?>

<!-- Flatpickr CSS & JS (Preloaded so dynamic AJAX forms have instant access) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<!-- Main Card List -->
<div class="card shadow-sm border-0 mb-4" id="card-list">
	<div class="card-header bg-transparent py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
		<div class="d-flex align-items-center">
			<div class="avatar avatar-sm bg-light-primary text-primary me-2 rounded-2 d-flex align-items-center justify-content-center p-2">
				<i class="fa fa-car fs-5"></i>
			</div>
			<div>
				<h5 class="card-title mb-0 fw-bold text-dark">Data Pengajuan Transportasi</h5>
				<small class="text-muted">Kelola pengajuan biaya perjalanan dan transportasi internal</small>
			</div>
		</div>
		<div>
			<?php if ($ENABLE_ADD) : ?>
				<button class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm" type="button" onclick="data_add()">
					<i class="fa fa-plus"></i>
					<span>Tambah Pengajuan</span>
				</button>
			<?php endif; ?>
		</div>
	</div>
	<!-- /.card-header -->
	<div class="card-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-hover table-striped align-middle dt-responsive w-100">
				<thead class="table-light">
					<tr>
						<th width="40" class="text-center">#</th>
						<th>No Dokumen</th>
						<th>Tanggal</th>
						<th>Nama Karyawan</th>
						<th>Departemen</th>
						<th>Keperluan</th>
						<th>No. Polisi</th>
						<th class="text-end">Total Transport</th>
						<th class="text-center">Status</th>
						<th width="120" class="text-center">Aksi</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /.card-body -->
</div>

<!-- Dynamic Form Container -->
<div id="form-data" style="display: none;"></div>

<script type="text/javascript">
	var url_add    = siteurl + 'expense/transport_create/';
	var url_edit   = siteurl + 'expense/transport_edit/';
	var url_delete = siteurl + 'expense/transport_delete/';
	var url_view   = siteurl + 'expense/transport_view/';
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
			language: {
				search: "_INPUT_",
				searchPlaceholder: "Cari no doc, nama, keperluan...",
				lengthMenu: "_MENU_ data per halaman",
				info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
				infoEmpty: "Menampilkan 0 data",
				emptyTable: "Tidak ada data transportasi yang ditemukan",
				zeroRecords: "Tidak ada data yang cocok",
				processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Sedang memuat data...'
			},
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_transport_input',
				cache: false,
				dataType: 'json'
			},
			columns: [
				{ data: 'no', className: 'text-center' },
				{ data: 'id_pengajuan', className: 'fw-semibold text-primary' },
				{ data: 'tanggal' },
				{ data: 'nama', className: 'fw-medium' },
				{ data: 'departement' },
				{ data: 'keperluan' },
				{ data: 'no_polisi' },
				{ data: 'total', className: 'fw-bold text-end' },
				{ data: 'status', className: 'text-center' },
				{ data: 'action', className: 'text-center', orderable: false, searchable: false }
			],
			order: [[1, 'desc']]
		});
	}

	function list_view() {
		$('#form-data').hide().empty();
		$('#card-list, .box').fadeIn(200);
		if (datatables) {
			datatables.ajax.reload(null, false);
		}
	}

	function data_add() {
		$('#card-list, .box').hide();
		$('#form-data').show().html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Memuat formulir...</div></div>');
		$('#form-data').load(url_add, function(response, status, xhr) {
			if (status == "error") {
				swal({
					title: "Error!",
					text: "Gagal memuat formulir: " + xhr.status + " " + xhr.statusText,
					type: "error"
				});
				list_view();
			} else {
				if (typeof initTransportForm === 'function') {
					initTransportForm();
				}
			}
		});
	}

	function data_edit(id) {
		if (id != "") {
			$('#card-list, .box').hide();
			$('#form-data').show().html('<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div><div class="mt-2 text-muted">Memuat data edit...</div></div>');
			$('#form-data').load(url_edit + id, function(response, status, xhr) {
				if (status == "error") {
					swal({
						title: "Error!",
						text: "Gagal memuat formulir edit: " + xhr.status + " " + xhr.statusText,
						type: "error"
					});
					list_view();
				} else {
					if (typeof initTransportForm === 'function') {
						initTransportForm();
					}
				}
			});
		}
	}

	function data_view(id) {
		if (id != "") {
			$('#card-list, .box').hide();
			$('#form-data').show().html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div><div class="mt-2 text-muted">Memuat detail data...</div></div>');
			$('#form-data').load(url_view + id, function(response, status, xhr) {
				if (status == "error") {
					swal({
						title: "Error!",
						text: "Gagal memuat detail data: " + xhr.status + " " + xhr.statusText,
						type: "error"
					});
					list_view();
				} else {
					if (typeof initTransportForm === 'function') {
						initTransportForm();
					}
				}
			});
		}
	}

	function data_delete(id) {
		swal({
			title: "Konfirmasi Hapus",
			text: "Apakah Anda yakin ingin menghapus data pengajuan ini?",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#d33",
			cancelButtonColor: "#6c757d",
			confirmButtonText: "Ya, Hapus!",
			cancelButtonText: "Batal",
			closeOnConfirm: false
		}, function(isConfirm) {
			if (isConfirm) {
				$.ajax({
					url: url_delete + id,
					dataType: "json",
					type: 'POST',
					success: function(msg) {
						if (msg.delete == true || msg.delete == 1) {
							swal({
								title: "Terhapus!",
								text: "Data berhasil dihapus.",
								type: "success",
								timer: 1500,
								showConfirmButton: false
							});
							if (datatables) {
								datatables.ajax.reload(null, false);
							}
						} else {
							swal({
								title: "Gagal!",
								text: "Data gagal dihapus atau sudah diproses.",
								type: "error"
							});
						}
					},
					error: function(xhr) {
						swal({
							title: "Error!",
							text: "Terjadi kesalahan saat menghapus data.",
							type: "error"
						});
					}
				});
			}
		});
	}
</script>
