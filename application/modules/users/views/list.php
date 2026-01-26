<?php
$ENABLE_ADD     = has_permission('Users.Add');
$ENABLE_MANAGE  = has_permission('Users.Manage');
$ENABLE_DELETE  = has_permission('Users.Delete');
?>

<!-- DataTables Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<div class="card shadow-sm border-0">
	<div class="card-header bg-white d-flex align-items-center justify-content-between">
		<?php if ($ENABLE_ADD) : ?>
			<a href="<?= site_url('users/setting/create') ?>" class="btn btn-success">
				<i class="fa fa-plus me-1"></i>Add User
			</a>
		<?php endif; ?>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th style="width:60px;">#</th>
						<th><?= lang('users_username') ?></th>
						<th><?= lang('users_email') ?></th>
						<th><?= lang('users_nm_lengkap') ?></th>
						<th><?= lang('users_alamat') ?></th>
						<th><?= lang('users_kota') ?></th>
						<th><?= lang('users_hp') ?></th>
						<th style="width:120px;"><?= lang('users_st_aktif') ?></th>

						<?php if ($ENABLE_MANAGE) : ?>
							<th style="width:110px;" class="text-end">Action</th>
						<?php endif; ?>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($results as $record) : ?>
						<tr>
							<td><?= $numb; ?></td>
							<td class="fw-semibold"><?= $record->username ?></td>
							<td><?= $record->email ?></td>
							<td><?= $record->nm_lengkap ?></td>
							<td><?= $record->alamat ?></td>
							<td><?= $record->kota ?></td>
							<td><?= $record->hp ?></td>
							<td>
								<?php if ($record->st_aktif == 0): ?>
									<span class="badge-status inactive"><?= lang('users_td_aktif') ?></span>
								<?php else: ?>
									<span class="badge-status active"><?= lang('users_aktif') ?></span>
								<?php endif; ?>
							</td>

							<?php if ($ENABLE_MANAGE) : ?>
								<td class="text-end">
									<a class="btn-icon btn-icon-edit"
										href="<?= site_url('users/setting/edit/' . $record->id_user); ?>"
										data-bs-toggle="tooltip" data-bs-placement="top" title="Edit User">
										<i class="ti ti-pencil"></i>
									</a>

									<?php if ($record->id_user != 1) : ?>
										<a class="btn-icon btn-icon-view"
											href="<?= site_url('users/setting/permission/' . $record->id_user); ?>"
											data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Permission">
											<i class="ti ti-shield-lock"></i>
										</a>
									<?php endif; ?>
								</td>
							<?php endif; ?>
						</tr>
					<?php $numb++;
					endforeach; ?>
				</tbody>

			</table>
		</div>
	</div>
</div>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
	$(function() {
		$('#example1').DataTable({
			responsive: true,
			pageLength: 10,
			lengthMenu: [10, 25, 50, 100],
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

		// Tooltip bootstrap 5
		const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
		tooltipTriggerList.forEach((el) => new bootstrap.Tooltip(el));
	});
</script>