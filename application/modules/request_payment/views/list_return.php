<?php
$total_dokumen = 0;
$total_pengembalian = 0;
$total_terbayar = 0;
$total_sisa = 0;
$processed_rows = [];

if (!empty($row)) {
	foreach ($row as $record) {
		$total_dokumen++;
		$sisa_expense = (float)$record->jumlah;
		$total_pengembalian += abs($sisa_expense);

		$get_pengembalian_expense = $this->db->select('IF(SUM(transfer_jumlah) IS NULL, 0, SUM(transfer_jumlah)) as ttl_kembali_expense')
			->get_where('tr_pengembalian_expense', ['no_doc' => $record->no_doc, 'status' => 1])->row();

		$ttl_kembali = !empty($get_pengembalian_expense) ? (float)$get_pengembalian_expense->ttl_kembali_expense : 0;
		$total_terbayar += $ttl_kembali;

		$sisa_kembali = ($sisa_expense - $ttl_kembali);
		if ($sisa_expense < 0) {
			$sisa_kembali = ($sisa_expense + $ttl_kembali);
		}
		$total_sisa += abs($sisa_kembali);

		$status_badge = '<span class="badge bg-danger px-2 py-1"><i class="fa fa-clock-o me-1"></i>Outstanding</span>';
		if ($ttl_kembali > 0) {
			$status_badge = '<span class="badge bg-warning text-dark px-2 py-1"><i class="fa fa-adjust me-1"></i>Partial</span>';
		}
		if ($sisa_kembali == 0) {
			$status_badge = '<span class="badge bg-success px-2 py-1"><i class="fa fa-check-circle me-1"></i>Paid</span>';
		}

		$processed_rows[] = [
			'record'       => $record,
			'ttl_kembali'  => $ttl_kembali,
			'sisa_kembali' => $sisa_kembali,
			'status_badge' => $status_badge
		];
	}
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">

<div id="alert_edit" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;"></div>

<!-- Quick KPI Stat Cards -->
<div class="row g-3 mb-4">
	<div class="col-6 col-md-3">
		<div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 5px solid #2563eb !important;">
			<div class="card-body p-3 d-flex align-items-center justify-content-between">
				<div>
					<span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Dokumen</span>
					<h4 class="fw-bold mb-0 text-primary mt-1"><?= number_format($total_dokumen) ?></h4>
					<small class="text-muted" style="font-size: 11px;">Pengembalian expense</small>
				</div>
				<div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-primary" style="width: 44px; height: 44px; font-size: 18px;">
					<i class="fas fa-copy"></i>
				</div>
			</div>
		</div>
	</div>
	<div class="col-6 col-md-3">
		<div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border-left: 5px solid #7c3aed !important;">
			<div class="card-body p-3 d-flex align-items-center justify-content-between">
				<div>
					<span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Nilai Pengembalian</span>
					<h4 class="fw-bold mb-0 text-dark mt-1" style="font-size: 1.15rem;">Rp <?= number_format($total_pengembalian, 0, ',', '.') ?></h4>
					<small class="text-muted" style="font-size: 11px;">Total nilai kembali</small>
				</div>
				<div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-purple" style="width: 44px; height: 44px; font-size: 18px; color: #7c3aed;">
					<i class="fas fa-money-bill-alt"></i>
				</div>
			</div>
		</div>
	</div>
	<div class="col-6 col-md-3">
		<div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 5px solid #16a34a !important;">
			<div class="card-body p-3 d-flex align-items-center justify-content-between">
				<div>
					<span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Total Terbayar</span>
					<h4 class="fw-bold mb-0 text-success mt-1" style="font-size: 1.15rem;">Rp <?= number_format($total_terbayar, 0, ',', '.') ?></h4>
					<small class="text-muted" style="font-size: 11px;">Realisasi transfer masuk</small>
				</div>
				<div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-success" style="width: 44px; height: 44px; font-size: 18px;">
					<i class="fa fa-check-circle"></i>
				</div>
			</div>
		</div>
	</div>
	<div class="col-6 col-md-3">
		<div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #d97706 !important;">
			<div class="card-body p-3 d-flex align-items-center justify-content-between">
				<div>
					<span class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">Sisa Belum Selesai</span>
					<h4 class="fw-bold mb-0 text-warning mt-1" style="font-size: 1.15rem;">Rp <?= number_format($total_sisa, 0, ',', '.') ?></h4>
					<small class="text-muted" style="font-size: 11px;">Outstanding pengembalian</small>
				</div>
				<div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center text-warning" style="width: 44px; height: 44px; font-size: 18px;">
					<i class="fa fa-hourglass-half"></i>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Main Table Card -->
<div class="card shadow-sm border-0 mb-4 box">
	<div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
		<div>
			<h5 class="card-title mb-0 fw-bold text-primary">
				<i class="fa fa-undo me-2"></i>Daftar Pengembalian Expense
			</h5>
			<small class="text-muted">Monitoring dan review status pengembalian dana / realisasi expense karyawan</small>
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped table-hover align-middle w-100">
				<thead class="table-light text-center">
					<tr>
						<th width="4%" class="text-center">#</th>
						<th width="14%">No Dokumen</th>
						<th width="13%">Request By</th>
						<th width="9%" class="text-center">Tanggal</th>
						<th>Keperluan</th>
						<th width="8%" class="text-center">Tipe</th>
						<th width="11%" class="text-end">Nilai Pengembalian</th>
						<th width="11%" class="text-end">Terbayar</th>
						<th width="11%" class="text-end">Sisa</th>
						<th width="10%" class="text-center">Status</th>
						<th width="8%" class="text-center">Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($processed_rows)) : ?>
						<?php $numb = 1;
						foreach ($processed_rows as $rowItem) : ?>
							<?php
							$rec = $rowItem['record'];
							$tgl_formatted = !empty($rec->tgl_doc) ? date('d/m/Y', strtotime($rec->tgl_doc)) : '-';
							?>
							<tr>
								<td class="text-center text-muted fw-semibold"><?= $numb++; ?></td>
								<td>
									<span class="fw-bold text-primary"><?= htmlspecialchars($rec->no_doc) ?></span>
								</td>
								<td class="fw-medium">
									<i class="fa fa-user me-1 text-muted"></i><?= htmlspecialchars($rec->created_by) ?>
								</td>
								<td class="text-center text-nowrap">
									<i class="fa fa-calendar me-1 text-muted"></i><?= $tgl_formatted ?>
								</td>
								<td>
									<span title="<?= htmlspecialchars($rec->keperluan) ?>"><?= htmlspecialchars($rec->keperluan) ?></span>
								</td>
								<td class="text-center">
									<span class="badge bg-light text-dark border text-uppercase px-2 py-1"><?= htmlspecialchars($rec->tipe) ?></span>
								</td>
								<td class="text-end fw-semibold">
									Rp <?= number_format($rec->jumlah, 0, ',', '.') ?>
								</td>
								<td class="text-end fw-semibold text-success">
									Rp <?= number_format($rowItem['ttl_kembali'], 0, ',', '.') ?>
								</td>
								<td class="text-end fw-bold <?= ($rowItem['sisa_kembali'] != 0) ? 'text-danger' : 'text-muted' ?>">
									Rp <?= number_format($rowItem['sisa_kembali'], 0, ',', '.') ?>
								</td>
								<td class="text-center text-nowrap">
									<?= $rowItem['status_badge'] ?>
								</td>
								<td class="text-center text-nowrap">
									<button type="button" class="btn btn-outline-primary btn-sm px-2 py-1 shadow-sm" onclick="edit(<?= $rec->ids ?>)" title="Review Pengembalian Expense">
										<i class="fa fa-eye me-1"></i> Review
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal View Expense (Bootstrap 5 Compatible) -->
<div class="modal fade" id="Mymodal" tabindex="-1" aria-labelledby="modalViewExpenseLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content border-0 shadow">
			<div class="modal-header bg-primary text-white py-3">
				<h5 class="modal-title fw-bold fs-6" id="modalViewExpenseLabel">
					<i class="fa fa-file-text-o me-2"></i>Review Pengembalian Expense
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-4" id="listexpense">
				<div class="text-center py-5">
					<i class="fa fa-spinner fa-spin fa-2x text-primary mb-2"></i>
					<p class="text-muted">Memuat data expense...</p>
				</div>
			</div>
			<div class="modal-footer bg-light py-2">
				<button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" data-dismiss="modal">
					<i class="fa fa-times me-1"></i> Tutup
				</button>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>

<script type="text/javascript">
	var base_url = '<?= base_url() ?>';

	$(document).ready(function() {
		$("#mytabledata").DataTable({
			order: [
				[0, 'asc']
			],
			pageLength: 10,
			responsive: true
		});

		if ($.fn.divide) {
			$(".divide").divide();
		}
	});

	function edit(id) {
		$("#listexpense").html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-primary mb-2"></i><p class="text-muted mb-0">Memuat data expense...</p></div>');

		// Compatible modal show (Bootstrap 5 or Bootstrap 3/4 jQuery fallback)
		var modalEl = document.getElementById('Mymodal');
		if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
			var modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
			modalInstance.show();
		} else {
			$("#Mymodal").modal('show');
		}

		$("#listexpense").load(base_url + 'expense/review/' + id, function(response, status, xhr) {
			if (status == "error") {
				$("#listexpense").html('<div class="alert alert-danger mb-0"><i class="fa fa-times me-1"></i> Gagal memuat data expense: ' + xhr.status + " " + xhr.statusText + '</div>');
			}
		});
	}
</script>