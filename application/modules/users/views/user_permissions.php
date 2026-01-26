<div class="card shadow-sm">
	<?= form_open($this->uri->uri_string(), array(
		'id' => 'frm_users',
		'name' => 'frm_users',
		'role' => 'form'
	)) ?>

	<!-- CARD BODY -->
	<div class="card-body">

		<!-- USER INFO -->
		<div class="row g-3 mb-4">
			<div class="col-md-4">
				<label for="username" class="form-label"><?= lang('users_username') ?></label>
				<input type="text" class="form-control" id="username" name="username"
					value="<?= set_value('username', isset($data->username) ? $data->username : ''); ?>"
					readonly>
			</div>

			<div class="col-md-4">
				<label for="nm_lengkap" class="form-label"><?= lang('users_nm_lengkap') ?></label>
				<input type="text" class="form-control" id="nm_lengkap" name="nm_lengkap"
					value="<?= set_value('nm_lengkap', isset($data->nm_lengkap) ? $data->nm_lengkap : ''); ?>"
					readonly>
			</div>
		</div>

		<!-- TABLE -->
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0" id="permissionTable">
				<thead class="table-light">
					<tr>
						<th style="min-width:250px;">Menu</th>

						<th width="12%" class="text-center">
							Check All
							<div class="form-check d-flex justify-content-center mt-2">
								<input class="form-check-input" type="checkbox" id="chk_all">
							</div>
						</th>

						<th width="12%" class="text-center">
							View
							<div class="form-check d-flex justify-content-center mt-2">
								<input class="form-check-input" type="checkbox" id="chk_view">
							</div>
						</th>

						<th width="12%" class="text-center">
							Add
							<div class="form-check d-flex justify-content-center mt-2">
								<input class="form-check-input" type="checkbox" id="chk_add">
							</div>
						</th>

						<th width="12%" class="text-center">
							Manage
							<div class="form-check d-flex justify-content-center mt-2">
								<input class="form-check-input" type="checkbox" id="chk_manage">
							</div>
						</th>

						<th width="12%" class="text-center">
							Delete
							<div class="form-check d-flex justify-content-center mt-2">
								<input class="form-check-input" type="checkbox" id="chk_delete">
							</div>
						</th>
					</tr>
				</thead>

				<tbody id="listDetail">
					<?php foreach ($permissions as $key => $pr) : ?>
						<tr class="table-group">
							<td class="fw-semibold text-primary">
								<i class="ti ti-folder me-2"></i><?= $pr['nama_menu'] ?>
							</td>

							<td class="text-center">
								<div class="form-check d-flex justify-content-center">
									<input type="checkbox" class="form-check-input all_baris" data-id="<?= $pr['id']; ?>">
								</div>
							</td>

							<?php
							if (!empty($ArrActionPers[$pr['id']])) {
								foreach ($ArrActionPers[$pr['id']] as $value6) {
									$id_permission = $value6['id_permission'];
									$x = explode(".", $value6['nm_permission']);
									$id_roll_permission = (isset($auth_permissions[$id_permission]->is_role_permission) && $auth_permissions[$id_permission]->is_role_permission == 1) ? 1 : '';
									$action_value = isset($auth_permissions[$id_permission]) ? 1 : 0;
							?>
									<td class="text-center">
										<div class="form-check d-flex justify-content-center">
											<input
												type="checkbox"
												name="id_permissions[]"
												class="form-check-input det<?= $x[1]; ?> menuID<?= $pr['id']; ?>"
												value="<?= $id_permission ?>"
												title="<?= $x[0] ?>"
												<?= ($action_value == 1) ? "checked='checked'" : '' ?>
												<?= ($id_roll_permission == 1) ? "disabled='disabled'" : '' ?> />
										</div>
									</td>
							<?php
								}
							}
							?>
						</tr>

						<?php
						if (!empty($ArrPermissionDetail[$pr['id']])) {
							foreach ($ArrPermissionDetail[$pr['id']] as $value) {
						?>
								<tr>
									<td class="ps-4">
										<i class="ti ti-subtask me-2 text-muted"></i><?= $value['nama_menu'] ?>
									</td>

									<td class="text-center">
										<div class="form-check d-flex justify-content-center">
											<input type="checkbox" class="form-check-input all_baris" data-id="<?= $value['id']; ?>">
										</div>
									</td>

									<?php
									if (!empty($ArrActionPers[$value['id']])) {
										foreach ($ArrActionPers[$value['id']] as $value3) {
											$x = explode(".", $value3['nm_permission']);
											$id_roll_permission = (isset($auth_permissions[$value3['id_permission']]->is_role_permission) && $auth_permissions[$value3['id_permission']]->is_role_permission == 1) ? 1 : '';
											$action_value = isset($auth_permissions[$value3['id_permission']]) ? 1 : 0;
									?>
											<td class="text-center">
												<div class="form-check d-flex justify-content-center">
													<input
														type="checkbox"
														name="id_permissions[]"
														class="form-check-input det<?= $x[1]; ?> menuID<?= $value['id']; ?>"
														value="<?= $value3['id_permission'] ?>"
														title="<?= $x[0] ?>"
														<?= ($action_value == 1) ? "checked='checked'" : '' ?>
														<?= ($id_roll_permission == 1) ? "disabled='disabled'" : '' ?> />
												</div>
											</td>
									<?php
										}
									}
									?>
								</tr>
						<?php
							}
						}
						?>

					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

	</div>

	<!-- CARD FOOTER -->
	<div class="card-footer d-flex justify-content-end gap-2">
		<button type="submit" name="save" class="btn btn-primary">
			<i class="ti ti-device-floppy me-1"></i><?= lang('users_btn_save') ?>
		</button>
		<a href="<?= site_url('users/setting'); ?>" class="btn btn-light">
			<i class="ti ti-x me-1"></i><?= lang('users_btn_cancel') ?>
		</a>
	</div>

	<?= form_close() ?>
</div>

<style>
	/* Biar tabel permission enak dibaca */
	#permissionTable th,
	#permissionTable td {
		vertical-align: middle;
	}

	#permissionTable tbody tr:hover {
		cursor: pointer;
	}

	#permissionTable .table-group {
		background: #f8fafc;
	}

	#permissionTable .ps-4 {
		padding-left: 2rem !important;
	}
</style>

<script>
	$(document).ready(function() {

		// klik di row -> toggle checkbox (tapi jangan ganggu input checkbox asli)
		$("#permissionTable tbody td").on("click", function(e) {
			var chk = $(this).find("input:checkbox").get(0);
			if (!chk) return;
			if (e.target !== chk) chk.checked = !chk.checked;
		});

		$("#chk_view").click(function() {
			$('.detView').not(this).prop('checked', this.checked);
		});
		$("#chk_add").click(function() {
			$('.detAdd').not(this).prop('checked', this.checked);
		});
		$("#chk_delete").click(function() {
			$('.detDelete').not(this).prop('checked', this.checked);
		});
		$("#chk_manage").click(function() {
			$('.detManage').not(this).prop('checked', this.checked);
		});

		$(".all_baris").click(function() {
			var id = $(this).data('id');
			$('.menuID' + id).not(this).prop('checked', this.checked);
		});

		$("#chk_all").click(function() {
			$('input:checkbox').not(this).prop('checked', this.checked);
		});

	});
</script>