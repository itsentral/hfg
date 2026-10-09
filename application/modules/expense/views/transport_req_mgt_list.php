<?php
$ENABLE_ADD     = has_permission('Pengajuan_Transportasi_Approval.Add');
$ENABLE_MANAGE  = has_permission('Pengajuan_Transportasi_Approval.Manage');
$ENABLE_VIEW    = has_permission('Pengajuan_Transportasi_Approval.View');
$ENABLE_DELETE  = has_permission('Pengajuan_Transportasi_Approval.Delete');
?>

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<div class="card shadow-sm border-0 mb-4" id="card-list">
	<div class="card-header bg-transparent py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
		<div class="d-flex align-items-center">
			<div class="avatar avatar-sm bg-light-primary text-primary me-2 rounded-2 d-flex align-items-center justify-content-center p-2">
				<i class="fa fa-clipboard-check fs-5"></i>
			</div>
			<div>
				<h5 class="card-title mb-0 fw-bold text-dark">Persetujuan Management Penggantian Transport</h5>
				<small class="text-muted">Daftar pengajuan transport yang menunggu persetujuan manajemen</small>
			</div>
		</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="mytabledata_mgt" class="table table-hover table-striped align-middle dt-responsive w-100">
				<thead class="table-light">
					<tr>
						<th width="40" class="text-center">#</th>
						<th>No. Dokumen</th>
						<th>Tanggal</th>
						<th>Nama Karyawan</th>
						<th>Departemen</th>
						<th class="text-end">Total Pengajuan</th>
						<th class="text-center">Status</th>
						<th width="120" class="text-center">Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($results)): ?>
						<?php $no = 1; foreach ($results as $item): ?>
							<tr>
								<td class="text-center"><?= $no++; ?></td>
								<td class="fw-semibold text-primary"><?= htmlspecialchars($item->no_doc); ?></td>
								<td><?= date('d M Y', strtotime($item->tgl_doc)); ?></td>
								<td class="fw-medium"><?= htmlspecialchars(!empty($item->nama) ? $item->nama : $item->nmuser); ?></td>
								<td><?= htmlspecialchars($item->departement); ?></td>
								<td class="text-end fw-bold">Rp <?= number_format($item->jumlah_expense, 0, ',', '.'); ?></td>
								<td class="text-center">
									<?php 
										$st = isset($status[$item->status]) ? $status[$item->status] : 'Baru';
										$badge = 'bg-warning text-dark';
										if ($item->status == 1) $badge = 'bg-info text-white';
										if ($item->status == 2) $badge = 'bg-primary';
										if ($item->status == 3) $badge = 'bg-success';
										if ($item->status == 9) $badge = 'bg-danger';
									?>
									<span class="badge <?= $badge; ?>"><?= $st; ?></span>
								</td>
								<td class="text-center">
									<div class="btn-group btn-group-sm">
										<a class="btn btn-outline-info btn-sm" href="javascript:void(0)" onclick="data_view(<?= $item->id; ?>)" title="Review / Approve">
											<i class="fa fa-eye"></i>
										</a>
										<a class="btn btn-outline-secondary btn-sm" href="<?= base_url('expense/transport_req_print/' . $item->id); ?>" target="_blank" title="Print">
											<i class="fa fa-print"></i>
										</a>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div id="form-data" style="display: none;"></div>

<script type="text/javascript">
	var url_view = siteurl + 'expense/transport_req_view/';

	$(document).ready(function() {
		if ($.fn.DataTable.isDataTable('#mytabledata_mgt')) {
			$('#mytabledata_mgt').DataTable().destroy();
		}
		$('#mytabledata_mgt').DataTable({
			paging: true,
			responsive: true
		});
	});

	function list_view() {
		$('#form-data').hide().empty();
		$('#card-list, .box').fadeIn(200);
	}

	function data_view(id) {
		if (id != "") {
			$('#card-list, .box').hide();
			$('#form-data').show().html('<div class="text-center py-5"><div class="spinner-border text-info" role="status"></div><div class="mt-2 text-muted">Memuat detail data...</div></div>');
			$('#form-data').load(url_view + id + '/_mgt', function(response, status, xhr) {
				if (status == "error") {
					swal({
						title: "Error!",
						text: "Gagal memuat detail data: " + xhr.status + " " + xhr.statusText,
						type: "error"
					});
					list_view();
				}
			});
		}
	}
</script>
