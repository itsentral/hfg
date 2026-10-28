<style>
.select2-container--default .select2-selection--single {
    height: 38px !important;
    padding: 5px 12px !important;
    border: 1px solid #ced4da !important;
    border-radius: 6px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px !important;
    padding-left: 0 !important;
    color: #212529 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 8px !important;
}
.select2-dropdown {
    border: 1px solid #ced4da !important;
    border-radius: 6px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    z-index: 1065 !important;
}
#tbl_proses .select2-container--default .select2-selection--single {
    height: 31px !important;
    padding: 2px 8px !important;
    border-radius: 4px !important;
}
#tbl_proses .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 25px !important;
    font-size: 0.875rem !important;
}
#tbl_proses .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 29px !important;
    right: 4px !important;
}
</style>
<?php
// Isi modal Flow Process & Cycletime (parsial; dimuat lewat AJAX dari Flow_process::form).
// $mode: 'add' | 'edit' | 'view'
$view_only  = ($mode === 'view');
$is_add     = ($mode === 'add');
$id         = (!empty($header['id'])) ? $header['id'] : '';
$code_lv2   = (!empty($header['code_lv2'])) ? $header['code_lv2'] : '';
$nama_kat   = (isset($categories[$code_lv2])) ? $categories[$code_lv2] : $code_lv2;
$readonly   = ($view_only) ? 'readonly' : '';
$disabled   = ($view_only) ? 'disabled' : '';
$machines   = (!empty($machines)) ? $machines : [];

// Tampilan angka: titik desimal untuk input; tanpa nol berlebih
$plain = function ($v, $dec = 6) {
	$s = rtrim(rtrim(number_format((float) $v, $dec, '.', ''), '0'), '.');
	return ($s === '' || $s === '-0') ? '0' : $s;
};

// Baris awal: baris tersimpan, atau satu baris kosong saat tambah
$rows = $details;
if (empty($rows)) {
	$rows = [['costcentre_area' => '', 'process_name' => '', 'machine' => '', 'cycletime' => '', 'man_power' => '', 'total_man_hour' => 0]];
}

$baris = function ($r, $no) use ($plain, $readonly, $disabled, $view_only, $machines) {
	ob_start(); ?>
	<tr class="fp-row">
		<td class="text-center fp-no"><?= $no; ?></td>
		<td><input type="text" name="costcentre_area[]" list="list_costcenter" class="form-control form-control-sm" maxlength="150" placeholder="Pilih / ketik area" value="<?= htmlspecialchars((string) $r['costcentre_area']); ?>" <?= $readonly; ?>></td>
		<td><input type="text" name="process_name[]" class="form-control form-control-sm" maxlength="150" placeholder="Nama proses" value="<?= htmlspecialchars((string) $r['process_name']); ?>" <?= $readonly; ?> required></td>
		<td>
			<select name="machine[]" class="form-select form-select-sm select-machine" style="width: 100%;" <?= $disabled; ?>>
				<option value="">-- Pilih Mesin --</option>
				<?php 
				$current_val = (string) $r['machine'];
				$found = false;
				if (!empty($machines)):
					foreach ($machines as $m):
						$is_sel = ($current_val !== '' && ($current_val === $m['kd_asset'] || $current_val === $m['nm_asset'] || $current_val === (string)$m['id']));
						if ($is_sel) $found = true;
				?>
					<option value="<?= htmlspecialchars($m['kd_asset']); ?>" <?= $is_sel ? 'selected' : ''; ?>>
						<?= htmlspecialchars($m['nm_asset']); ?> (<?= htmlspecialchars($m['kd_asset']); ?>)
					</option>
				<?php 
					endforeach;
				endif;
				if (!$found && $current_val !== ''):
				?>
					<option value="<?= htmlspecialchars($current_val); ?>" selected>
						<?= htmlspecialchars($current_val); ?>
					</option>
				<?php endif; ?>
			</select>
		</td>
		<td><input type="text" name="cycletime[]" class="form-control form-control-sm text-end fp-ct" inputmode="decimal" placeholder="0" value="<?= ($r['cycletime'] === '' ? '' : $plain($r['cycletime'])); ?>" <?= $readonly; ?>></td>
		<td><input type="text" name="man_power[]" class="form-control form-control-sm text-end fp-mp" inputmode="numeric" placeholder="0" value="<?= ($r['man_power'] === '' ? '' : (int) $r['man_power']); ?>" <?= $readonly; ?>></td>
		<td class="text-end fp-mh"><?= $plain($r['total_man_hour'], 8); ?></td>
		<?php if (!$view_only) : ?>
			<td class="text-center"><a href="javascript:void(0)" class="btn-icon btn-icon-delete fp-del" title="Hapus proses"><i class="ti ti-trash"></i></a></td>
		<?php endif; ?>
	</tr>
	<?php
	return ob_get_clean();
};
?>

<input type="hidden" id="fp_mode" value="<?= $mode; ?>">
<input type="hidden" name="id" value="<?= $id; ?>">

<!-- Datalist Cost Centre / Area -->
<datalist id="list_costcenter">
	<?php if (!empty($costcentres)): ?>
		<?php foreach ($costcentres as $c): ?>
			<option value="<?= htmlspecialchars($c); ?>">
		<?php endforeach; ?>
	<?php endif; ?>
</datalist>

<div class="row mb-3">
	<div class="col-md-3">
		<label class="col-form-label fw-bold">Product Category <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-6">
		<?php if ($is_add) : ?>
			<select name="code_lv2" id="code_lv2" class="form-select" style="width: 100%;">
				<option value="0">Select Product Category</option>
				<?php foreach ($tersedia as $kode => $nama) : ?>
					<option value="<?= htmlspecialchars($kode, ENT_QUOTES); ?>"><?= strtoupper(htmlspecialchars($nama)); ?></option>
				<?php endforeach; ?>
			</select>
			<?php if (empty($tersedia)) : ?>
				<small class="text-muted">Semua Product Category sudah memiliki Flow Process.</small>
			<?php endif; ?>
		<?php else : ?>
			<input type="text" class="form-control bg-light" value="<?= strtoupper(htmlspecialchars($nama_kat)); ?>" readonly>
			<input type="hidden" name="code_lv2" value="<?= htmlspecialchars($code_lv2, ENT_QUOTES); ?>">
		<?php endif; ?>
	</div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
	<h6 class="mb-0 fw-bold text-dark"><i class="ti ti-list-details me-1"></i> Rincian Urutan Proses &amp; Cycletime</h6>
	<?php if (!$view_only) : ?>
		<button type="button" class="btn btn-sm btn-primary shadow-xs" id="fp_add_row">
			<i class="ti ti-plus me-1"></i> Tambah Proses
		</button>
	<?php endif; ?>
</div>

<div class="table-responsive">
	<table class="table table-bordered table-sm align-middle" id="tbl_proses">
		<thead class="table-light">
			<tr>
				<th class="text-center" style="width:40px">#</th>
				<th style="min-width:150px">Costcentre / Area</th>
				<th style="min-width:150px">Process Name <span class="text-danger">*</span></th>
				<th style="min-width:240px">Machine</th>
				<th class="text-end" style="min-width:130px">Cycletime (menit/meter)</th>
				<th class="text-end" style="width:100px">Man Power</th>
				<th class="text-end" style="min-width:130px">Man Hour (jam/meter)</th>
				<?php if (!$view_only) : ?><th class="text-center" style="width:50px">Aksi</th><?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($rows as $i => $r) { echo $baris($r, $i + 1); } ?>
		</tbody>
		<tfoot>
			<tr class="table-light fw-bold">
				<td colspan="4" class="text-end">Total</td>
				<td class="text-end" id="fp_total_ct">-</td>
				<td></td>
				<td class="text-end" id="fp_total_mh">-</td>
				<?php if (!$view_only) : ?><td></td><?php endif; ?>
			</tr>
		</tfoot>
	</table>
</div>

<div class="alert alert-light border py-2 mt-3 mb-0 text-muted small">
	<i class="fa fa-info-circle me-1"></i> <strong>Rumus:</strong> <code>Man Hour = Cycletime &times; Man Power &divide; 60</code>. Total Man Hour = jumlah Man Hour seluruh proses. Contoh: Cycletime <code>0.041667</code> menit/m dengan 1 orang Man Power &rarr; <code>0.000694</code> jam/meter.
</div>

<!-- Templat baris baru -->
<script type="text/html" id="fp_tpl_row">
	<?= $baris(['costcentre_area' => '', 'process_name' => '', 'machine' => '', 'cycletime' => '', 'man_power' => '', 'total_man_hour' => 0], '__NO__'); ?>
</script>
