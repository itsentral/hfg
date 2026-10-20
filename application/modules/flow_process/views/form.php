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

// Opsi Costcentre/Area; nilai tersimpan yang tidak ada di master tetap dipertahankan
$opsi_area = function ($pilih) use ($costcentres) {
	$html = '<option value="">- Pilih -</option>';
	$daftar = $costcentres;
	if ($pilih !== '' && $pilih !== null && !in_array($pilih, $daftar)) {
		$daftar[] = $pilih;
	}
	foreach ($daftar as $nama) {
		$sel   = ((string) $nama === (string) $pilih) ? ' selected' : '';
		$html .= '<option value="' . htmlspecialchars($nama, ENT_QUOTES) . '"' . $sel . '>' . htmlspecialchars($nama) . '</option>';
	}
	return $html;
};

$baris = function ($r, $no) use ($plain, $readonly, $disabled, $opsi_area, $view_only) {
	ob_start(); ?>
	<tr class="fp-row">
		<td class="text-center fp-no"><?= $no; ?></td>
		<td><select name="costcentre_area[]" class="form-select form-select-sm" <?= $disabled; ?>><?= $opsi_area($r['costcentre_area']); ?></select></td>
		<td><input type="text" name="process_name[]" class="form-control form-control-sm" maxlength="150" value="<?= htmlspecialchars((string) $r['process_name']); ?>" <?= $readonly; ?>></td>
		<td><input type="text" name="machine[]" class="form-control form-control-sm" maxlength="150" value="<?= htmlspecialchars((string) $r['machine']); ?>" <?= $readonly; ?>></td>
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

<div class="row mb-3">
	<div class="col-md-3">
		<label class="col-form-label">Product Category <span class="text-danger">*</span></label>
	</div>
	<div class="col-md-6">
		<?php if ($is_add) : ?>
			<select name="code_lv2" id="code_lv2" class="form-control chosen-select">
				<option value="0">Select Product Category</option>
				<?php foreach ($tersedia as $kode => $nama) : ?>
					<option value="<?= htmlspecialchars($kode, ENT_QUOTES); ?>"><?= strtoupper(htmlspecialchars($nama)); ?></option>
				<?php endforeach; ?>
			</select>
			<?php if (empty($tersedia)) : ?>
				<small class="text-muted">Semua Product Category sudah memiliki Flow Process.</small>
			<?php endif; ?>
		<?php else : ?>
			<input type="text" class="form-control" value="<?= strtoupper(htmlspecialchars($nama_kat)); ?>" readonly>
		<?php endif; ?>
	</div>
</div>

<div class="table-responsive">
	<table class="table table-bordered table-sm align-middle" id="tbl_proses">
		<thead class="table-light">
			<tr>
				<th class="text-center" style="width:40px">#</th>
				<th style="min-width:160px">Costcentre / Area</th>
				<th style="min-width:160px">Process Name <span class="text-danger">*</span></th>
				<th style="min-width:130px">Machine</th>
				<th class="text-end" style="min-width:120px">Cycletime (menit/meter)</th>
				<th class="text-end" style="width:90px">Man Power</th>
				<th class="text-end" style="min-width:120px">Man Hour (jam/meter)</th>
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

<?php if (!$view_only) : ?>
	<button type="button" class="btn btn-sm btn-outline-primary" id="fp_add_row"><i class="ti ti-plus me-1"></i>Tambah Proses</button>
<?php endif; ?>
<small class="text-muted d-block mt-2">
	Man Hour = Cycletime &times; Man Power &divide; 60. Total Man Hour = jumlah Man Hour seluruh proses. Nilai dihitung ulang oleh sistem saat disimpan.
	Gunakan titik untuk desimal.
</small>

<!-- Templat baris baru (tidak ikut terkirim karena berada di dalam script bertipe text/html) -->
<script type="text/html" id="fp_tpl_row">
	<?= $baris(['costcentre_area' => '', 'process_name' => '', 'machine' => '', 'cycletime' => '', 'man_power' => '', 'total_man_hour' => 0], '__NO__'); ?>
</script>
