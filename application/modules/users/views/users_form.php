<div class="card">
	<div class="card-body">
		<?= form_open($this->uri->uri_string(), array(
			'id' => 'frm_users',
			'name' => 'frm_users',
			'role' => 'form'
		)) ?>

		<div class="row g-3">

			<!-- Username -->
			<div class="col-md-6">
				<label class="form-label fw-semibold"><?= lang('users_username') ?></label>
				<div class="input-group <?= form_error('username') ? 'is-invalid' : ''; ?>">
					<span class="input-group-text"><i class="ti ti-user"></i></span>
					<input type="text"
						class="form-control <?= form_error('username') ? 'is-invalid' : ''; ?>"
						id="username" name="username"
						maxlength="45"
						value="<?= set_value('username', isset($data->username) ? $data->username : ''); ?>"
						autofocus />
				</div>
				<?= form_error('username', '<div class="invalid-feedback d-block">', '</div>'); ?>
			</div>

			<!-- Email -->
			<div class="col-md-6">
				<label class="form-label fw-semibold"><?= lang('users_email') ?></label>
				<div class="input-group">
					<span class="input-group-text"><i class="ti ti-mail"></i></span>
					<input type="email"
						class="form-control <?= form_error('email') ? 'is-invalid' : ''; ?>"
						id="email" name="email"
						maxlength="100"
						value="<?= set_value('email', isset($data->email) ? $data->email : ''); ?>" />
				</div>
				<?= form_error('email', '<div class="invalid-feedback d-block">', '</div>'); ?>
			</div>

			<!-- Password -->
			<div class="col-md-6">
				<label class="form-label fw-semibold"><?= lang('users_password') ?></label>
				<div class="input-group">
					<span class="input-group-text"><i class="ti ti-lock"></i></span>
					<input type="password"
						class="form-control <?= form_error('password') ? 'is-invalid' : ''; ?>"
						id="password" name="password"
						maxlength="100"
						value="<?= set_value('password'); ?>" />
				</div>
				<?= form_error('password', '<div class="invalid-feedback d-block">', '</div>'); ?>
				<small class="text-muted d-block mt-1">Kosongkan jika tidak ingin mengubah password (saat edit).</small>
			</div>

			<!-- Re Password -->
			<div class="col-md-6">
				<label class="form-label fw-semibold"><?= lang('users_repassword') ?></label>
				<div class="input-group">
					<span class="input-group-text"><i class="ti ti-shield-lock"></i></span>
					<input type="password"
						class="form-control <?= form_error('re-password') ? 'is-invalid' : ''; ?>"
						id="re-password" name="re-password"
						maxlength="100"
						value="<?= set_value('re-password'); ?>" />
				</div>
				<?= form_error('re-password', '<div class="invalid-feedback d-block">', '</div>'); ?>
			</div>

			<!-- Nama Lengkap -->
			<div class="col-md-6">
				<label class="form-label fw-semibold"><?= lang('users_nm_lengkap') ?></label>
				<div class="input-group">
					<span class="input-group-text"><i class="ti ti-id"></i></span>
					<input type="text"
						class="form-control <?= form_error('nm_lengkap') ? 'is-invalid' : ''; ?>"
						id="nm_lengkap" name="nm_lengkap"
						maxlength="100"
						value="<?= set_value('nm_lengkap', isset($data->nm_lengkap) ? $data->nm_lengkap : ''); ?>" />
				</div>
				<?= form_error('nm_lengkap', '<div class="invalid-feedback d-block">', '</div>'); ?>
			</div>

			<!-- Kota -->
			<div class="col-md-3">
				<label class="form-label fw-semibold"><?= lang('users_kota') ?></label>
				<div class="input-group">
					<span class="input-group-text"><i class="ti ti-map-pin"></i></span>
					<input type="text"
						class="form-control <?= form_error('kota') ? 'is-invalid' : ''; ?>"
						id="kota" name="kota"
						maxlength="20"
						value="<?= set_value('kota', isset($data->kota) ? $data->kota : ''); ?>" />
				</div>
				<?= form_error('kota', '<div class="invalid-feedback d-block">', '</div>'); ?>
			</div>

			<!-- HP -->
			<div class="col-md-3">
				<label class="form-label fw-semibold"><?= lang('users_hp') ?></label>
				<div class="input-group">
					<span class="input-group-text"><i class="ti ti-phone"></i></span>
					<input type="text"
						class="form-control <?= form_error('hp') ? 'is-invalid' : ''; ?>"
						id="hp" name="hp"
						maxlength="20"
						value="<?= set_value('hp', isset($data->hp) ? $data->hp : ''); ?>" />
				</div>
				<?= form_error('hp', '<div class="invalid-feedback d-block">', '</div>'); ?>
			</div>

			<!-- Status Aktif -->
			<div class="col-md-6">
				<label class="form-label fw-semibold"><?= lang('users_st_aktif') ?></label>
				<div class="input-group">
					<span class="input-group-text"><i class="ti ti-toggle-left"></i></span>
					<select name="st_aktif" id="st_aktif" class="form-select <?= form_error('st_aktif') ? 'is-invalid' : ''; ?>">
						<option value="1" <?= set_select('st_aktif', 1, isset($data->st_aktif) && $data->st_aktif == 1) ?>>
							<?= lang('users_aktif') ?>
						</option>
						<option value="0" <?= set_select('st_aktif', 0, isset($data->st_aktif) && $data->st_aktif == 0) ?>>
							<?= lang('users_td_aktif') ?>
						</option>
					</select>
				</div>
				<?= form_error('st_aktif', '<div class="invalid-feedback d-block">', '</div>'); ?>
			</div>

			<!-- Department -->
			<div class="col-md-6">
				<label class="form-label fw-semibold"><b>Department</b></label>

				<?php
				$deptid = (!empty($data->department_id)) ? $data->department_id : 0;
				$departmentx[0] = 'Select An Department';
				foreach ($department as $key => $value) {
					$departmentx[$value['id']] = strtoupper($value['nama']);
				}
				?>

				<div class="input-group">
					<span class="input-group-text"><i class="ti ti-building"></i></span>
					<?= form_dropdown(
						'department_id',
						$departmentx,
						$deptid,
						array('id' => 'department_id', 'class' => 'form-select', 'required' => 'required')
					); ?>
				</div>
			</div>

			<!-- Alamat -->
			<div class="col-12">
				<label class="form-label fw-semibold"><?= lang('users_alamat') ?></label>
				<div class="input-group">
					<span class="input-group-text"><i class="ti ti-home"></i></span>
					<textarea class="form-control <?= form_error('alamat') ? 'is-invalid' : ''; ?>"
						id="alamat" name="alamat"
						maxlength="255"
						rows="3"><?= set_value('alamat', isset($data->alamat) ? $data->alamat : ''); ?></textarea>
				</div>
				<?= form_error('alamat', '<div class="invalid-feedback d-block">', '</div>'); ?>
			</div>

			<!-- Cabang (Hidden as original) -->
			<div class="col-md-6 d-none">
				<label class="form-label fw-semibold">Cabang</label>
				<div class="input-group">
					<span class="input-group-text"><i class="ti ti-briefcase"></i></span>
					<select name="kdcab" id="kdcab" class="form-select">
						<option value="">Pilih Cabang</option>
						<?php
						foreach ($cabang as $k => $v) {
							$selected = '';
							if (isset($data->kdcab) && $v->kdcab == $data->kdcab) {
								$selected = 'selected="selected"';
							}
						?>
							<option value="<?= $v->kdcab ?>" <?= $selected ?>><?= $v->namacabang ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

		</div>

		<hr class="my-4">

		<!-- Buttons -->
		<div class="d-flex justify-content-end gap-2">
			<button type="submit" name="save" class="btn btn-primary">
				<i class="ti ti-device-floppy"></i> <?= lang('users_btn_save') ?>
			</button>

			<?= anchor('users/setting', '<i class="ti ti-x"></i> ' . lang('users_btn_cancel'), array('class' => 'btn btn-dark')); ?>
		</div>

		<?= form_close() ?>
	</div>
</div>