<?php
$ENABLE_ADD     = has_permission('Master_Karyawan.Add');
$ENABLE_MANAGE  = has_permission('Master_Karyawan.Manage');
$ENABLE_VIEW    = has_permission('Master_Karyawan.View');
$ENABLE_DELETE  = has_permission('Master_Karyawan.Delete');
?>

<div class="card shadow-sm border-0">
	<div class="card-header bg-white d-flex align-items-center justify-content-between">
		<div class="d-flex gap-2 flex-wrap">
			<?php if ($ENABLE_ADD) : ?>
				<a class="btn btn-success btn-md" href="<?= base_url('master_employee/add') ?>" title="Add">
					<i class="fa fa-plus me-1"></i> Add
				</a>
			<?php endif; ?>

			<?php /* contoh kalau nanti mau aktifkan
      <a class="btn btn-warning btn-md" href="<?= base_url('master_employee/excel_download') ?>" target="_blank" title="Download Excel">
        <i class="fa fa-file-excel-o me-1"></i> Download Excel
      </a>
      */ ?>
		</div>

		<div class="ms-auto">
			<!-- slot tombol tambahan -->
		</div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th style="width:60px;" class="text-center">#</th>
						<th>Employee Name</th>
						<th style="width:140px;">Gender</th>
						<th>Department</th>
						<th style="width:150px;">Telepon</th>
						<th>Email</th>
						<th style="width:120px;" class="text-center">Status</th>
						<th style="width:140px;" class="text-end">Option</th>
					</tr>
				</thead>

				<tbody>
					<?php if (!empty($result)) : ?>
						<?php
						$numb = 0;
						foreach ($result as $record) :
							$numb++;

							$status_text = 'Active';
							if ($record->status == 'N') $status_text = 'Non-Active';

							$gender = ($record->gender == 'P') ? 'Perempuan' : 'Laki-Laki';
							$dept = ucwords(strtolower(get_name('ms_department', 'nama', 'id', $record->department)));
						?>
							<tr>
								<td class="text-center"><?= $numb; ?></td>
								<td class="fw-semibold"><?= ucwords(strtolower($record->nm_karyawan)) ?></td>
								<td><?= $gender ?></td>
								<td><?= $dept ?></td>
								<td><?= strtoupper($record->no_ponsel) ?></td>
								<td><?= strtolower($record->email) ?></td>

								<td class="text-center">
									<?php if ($record->status == 'N') : ?>
										<span class="badge-status inactive">Non-Active</span>
									<?php else : ?>
										<span class="badge-status active">Active</span>
									<?php endif; ?>
								</td>

								<td class="text-end">
									<?php if ($ENABLE_VIEW) : ?>
										<a href="<?= base_url('master_employee/add/' . $record->id . '/view'); ?>"
											class="btn-icon btn-icon-view"
											title="Detail">
											<i class="ti ti-eye"></i>
										</a>
									<?php endif; ?>

									<?php if ($ENABLE_MANAGE) : ?>
										<a href="<?= base_url('master_employee/add/' . $record->id); ?>"
											class="btn-icon btn-icon-edit"
											title="Edit">
											<i class="ti ti-edit"></i>
										</a>
									<?php endif; ?>

									<?php if ($ENABLE_DELETE) : ?>
										<a href="javascript:void(0)"
											class="btn-icon btn-icon-delete delete"
											title="Delete"
											data-id="<?= $record->id; ?>">
											<i class="ti ti-trash"></i>
										</a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>

			</table>
		</div>
	</div>
</div>

<!-- DataTables JS (Bootstrap 5) -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$('#example1').DataTable({
			responsive: true,
			pageLength: 10,
			lengthMenu: [10, 25, 50, 100],
			autoWidth: false,
			language: {
				search: "Search:",
				lengthMenu: "Show _MENU_ entries",
				info: "Showing _START_ to _END_ of _TOTAL_ entries",
				paginate: {
					previous: "Prev",
					next: "Next"
				}
			}
		});
	});

	// Delete
	$(document).on('click', '.delete', function(e) {
		e.preventDefault();
		var id = $(this).data('id');

		swal({
			title: "Anda Yakin?",
			text: "Data akan di hapus!",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-info",
			confirmButtonText: "Yes",
			cancelButtonText: "No",
			closeOnConfirm: false
		}, function() {
			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + '/delete',
				dataType: "json",
				data: {
					id: id
				},
				success: function(res) {
					if (res.status == '1') {
						swal({
							title: "Sukses",
							text: res.pesan,
							type: "success"
						}, function() {
							window.location.reload(true);
						});
					} else {
						swal({
							title: "Error",
							text: res.pesan,
							type: "error"
						});
					}
				},
				error: function() {
					swal({
						title: "Error",
						text: "Error proccess !",
						type: "error"
					});
				}
			});
		});
	});
</script>