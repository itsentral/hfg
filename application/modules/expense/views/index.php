<?php
$ENABLE_ADD     = has_permission('Expense.Add');
$ENABLE_MANAGE  = has_permission('Expense.Manage');
$ENABLE_VIEW    = has_permission('Expense.View');
$ENABLE_DELETE  = has_permission('Expense.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<div class="card shadow-sm border-0 mb-4 box">
	<div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
		<div>
			<h5 class="card-title mb-0 fw-bold text-primary">
				<i class="fa fa-cubes me-2"></i>Daftar Laporan & Pertanggungjawaban Expense
			</h5>
			<small class="text-muted">Kelola pengajuan biaya operasional langsung dan pertanggungjawaban kasbon</small>
		</div>
		<?php if ($ENABLE_ADD) : ?>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<button class="btn btn-success btn-sm shadow-sm px-3" type="button" onclick="data_add()" title="Buat Pengeluaran Langsung / Manual">
					<i class="fa fa-plus me-1"></i> Tambah Expense
				</button>
				<button class="btn btn-primary btn-sm shadow-sm px-3" type="button" onclick="data_add_report()" title="Pertanggungjawaban Kasbon">
					<i class="fa fa-ticket me-1"></i> Tambah Expense Report
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
						<th class="text-center" width="13%">No Dokumen</th>
						<th class="text-center" width="9%">Tanggal</th>
						<th class="text-center" width="12%">Nama Pemohon</th>
						<th class="text-end" width="12%">Total Realisasi</th>
						<th class="text-center" width="10%">Approval</th>
						<th class="text-center" width="10%">Approval Date</th>
						<th class="text-center">Keterangan</th>
						<th class="text-center" width="11%">Status</th>
						<th class="text-center text-nowrap" width="9%">Aksi</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div id="form-data"></div>

<!-- MODAL PILIH KASBON UNTUK EXPENSE REPORT -->
<div class="modal fade" id="modalKasbon" tabindex="-1" aria-labelledby="modalKasbonLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered">
		<div class="modal-content border-0 shadow rounded-3">
			<div class="modal-header border-bottom py-3">
				<h5 class="modal-title fw-bold text-dark fs-6" id="modalKasbonLabel">
					<i class="fa fa-ticket text-primary me-2"></i>Pilih Kasbon untuk Dibuatkan Expense Report
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-3">
				<div class="table-responsive">
					<table class="table table-bordered table-striped table-hover align-middle w-100" id="tableKasbon">
						<thead class="table-light text-center">
							<tr>
								<th width="5%" class="text-center">#</th>
								<th width="16%">No. Kasbon</th>
								<th width="11%">Tanggal</th>
								<th>Keperluan</th>
								<th>Keterangan</th>
								<th width="14%" class="text-end">Jumlah (Rp)</th>
								<th width="14%" class="text-center">Aksi</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer border-top py-2">
				<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<!-- Scripts -->
<script type="text/javascript">
	var url_add        = siteurl + 'expense/create/';
	var url_add_report = siteurl + 'expense/create_report/';
	var url_edit       = siteurl + 'expense/edit/';
	var url_delete     = siteurl + 'expense/delete/';
	var url_view       = siteurl + 'expense/view/';

	var all = "<?= (isset($all)) ? $all : '' ?>";

	$(document).ready(function() {
		datatables();
	});

	function data_add() {
		var companyName = (document.title.indexOf('|') !== -1) ? document.title.split('|')[0].trim() : 'HFG';
		document.title = companyName + ' | Expense';
		$('.content-header h1').html('<i class="fa fa-cubes"></i> Expense');
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(url_add);
	}

	function data_add_report() {
		$.ajax({
			url: siteurl + 'expense/get_kasbon',
			type: "POST",
			dataType: "json",
			success: function(data) {
				if ($.fn.DataTable.isDataTable('#tableKasbon')) {
					$('#tableKasbon').DataTable().destroy();
				}

				var tbody = '';
				if (data && data.length > 0) {
					for (var i = 0; i < data.length; i++) {
						tbody += '<tr>';
						tbody += '<td class="text-center">' + (i + 1) + '</td>';
						tbody += '<td class="text-center fw-bold text-primary">' + data[i].no_doc + '</td>';
						tbody += '<td class="text-center">' + data[i].tgl_doc + '</td>';
						tbody += '<td>' + (data[i].keperluan || '-') + '</td>';
						tbody += '<td>' + (data[i].keterangan || '-') + '</td>';
						tbody += '<td class="text-end fw-bold font-monospace text-dark">' + Number(data[i].jumlah_kasbon).toLocaleString('id-ID') + '</td>';
						tbody += '<td class="text-center"><button type="button" class="btn btn-primary btn-sm px-3 btn-pilih-kasbon-report" data-doc="' + data[i].no_doc + '" data-id="' + data[i].id + '"><i class="fa fa-arrow-right me-1"></i> Proses Report</button></td>';
						tbody += '</tr>';
					}
				} else {
					tbody = '<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada data kasbon yang tersedia untuk dilaporkan</td></tr>';
				}
				$('#tableKasbon tbody').html(tbody);
				$('#tableKasbon').DataTable({
					paging: true,
					pageLength: 10,
					lengthMenu: [10, 25, 50, 100],
					ordering: true,
					searching: true
				});

				if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
					var modalEl = document.getElementById('modalKasbon');
					var modalInst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
					modalInst.show();
				} else {
					$('#modalKasbon').modal('show');
				}
			},
			error: function() {
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						title: "Gagal!",
						text: 'Gagal mengambil data kasbon dari server.',
						icon: "warning"
					});
				} else {
					alert('Gagal mengambil data kasbon dari server.');
				}
			}
		});
	}

	$(document).on('click', '.btn-pilih-kasbon-report', function() {
		var no_doc_kasbon = $(this).data('doc');
		var id_kasbon = $(this).data('id');
		select_kasbon_report(no_doc_kasbon, id_kasbon);
	});

	function select_kasbon_report(no_doc_kasbon, id_kasbon) {
		if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
			var modalEl = document.getElementById('modalKasbon');
			var modalInst = bootstrap.Modal.getInstance(modalEl);
			if (modalInst) modalInst.hide();
		}
		$('#modalKasbon').modal('hide');

		var companyName = (document.title.indexOf('|') !== -1) ? document.title.split('|')[0].trim() : 'HFG';
		document.title = companyName + ' | Expense Report';
		$('.content-header h1').html('<i class="fa fa-ticket"></i> Expense Report');
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(url_add_report + encodeURIComponent(no_doc_kasbon) + '/' + (id_kasbon || ''));
	}

	function data_edit(id) {
		if (id != "") {
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_edit + id);
		}
	}

	function data_view(id) {
		if (id != "") {
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_view + id);
		}
	}

	function data_delete(id) {
		var deleteAction = function() {
			$.post(url_delete + id, function(res) {
				if (res.delete === true || res.delete == '1' || res.status == '1') {
					if (typeof Swal !== 'undefined') {
						Swal.fire('Terhapus!', 'Dokumen expense telah berhasil dihapus.', 'success');
					} else {
						alert('Dokumen expense telah berhasil dihapus.');
					}
					datatables();
				} else {
					if (typeof Swal !== 'undefined') {
						Swal.fire('Gagal!', res.msg || 'Gagal menghapus dokumen expense.', 'error');
					} else {
						alert(res.msg || 'Gagal menghapus dokumen expense.');
					}
				}
			}, 'json');
		};

		if (typeof Swal !== 'undefined') {
			Swal.fire({
				title: 'Hapus Dokumen Expense?',
				text: 'Data yang dihapus tidak dapat dikembalikan!',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Ya, Hapus!',
				cancelButtonText: 'Batal',
				confirmButtonColor: '#dc3545'
			}).then((result) => {
				if (result.isConfirmed) {
					deleteAction();
				}
			});
		} else {
			if (confirm('Apakah Anda yakin ingin menghapus dokumen Expense ini?')) {
				deleteAction();
			}
		}
	}

	function datatables() {
		$('#mytabledata').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			stateSave: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_dat_expense_data',
				cache: false,
				dataType: 'json',
				data: function(d) {
					d.all = all;
				},
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
					data: 'no_doc',
					className: 'text-center fw-bold text-dark'
				},
				{
					data: 'tgl_doc',
					className: 'text-center'
				},
				{
					data: 'nama'
				},
				{
					data: 'total_realisasi',
					className: 'text-end fw-bold font-monospace text-dark'
				},
				{
					data: 'approval'
				},
				{
					data: 'approval_date',
					className: 'text-center'
				},
				{
					data: 'keterangan'
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
