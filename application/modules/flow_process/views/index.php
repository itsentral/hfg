<?php
$ENABLE_ADD    = has_permission('Flow_Process.Add');
$ENABLE_MANAGE = has_permission('Flow_Process.Manage');
$ENABLE_VIEW   = has_permission('Flow_Process.View');
$ENABLE_DELETE = has_permission('Flow_Process.Delete');

// Tampilan angka daftar: koma desimal, tanpa nol berlebih
$fmt = function ($v, $dec = 6) {
	$s = rtrim(rtrim(number_format((float) $v, $dec, ',', '.'), '0'), ',');
	return ($s === '') ? '0' : $s;
};
?>

<div class="card shadow-sm border-0">
	<div class="card-header bg-white d-flex align-items-center justify-content-between">
		<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success fp-add" href="javascript:void(0)" title="Add">
				<i class="fa fa-plus me-1"></i>Add
			</a>
		<?php endif; ?>
		<small class="text-muted ms-3">
			Urutan proses produksi dan cycletime per Product Category. Man Hour = Cycletime &times; Man Power &divide; 60.
		</small>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-striped table-hover align-middle w-100">
				<thead class="table-light">
					<tr>
						<th>#</th>
						<th>Product Category</th>
						<th class="text-end">Jumlah Proses</th>
						<th class="text-end">Cycletime Total (menit/meter)</th>
						<th class="text-end">Total Man Hour (jam/meter)</th>
						<th>Last Update</th>
						<th>Updated By</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($list)) : ?>
						<?php $numb = 0;
						foreach ($list as $row) : $numb++;
							$nama = (isset($categories[$row['code_lv2']])) ? $categories[$row['code_lv2']] : $row['code_lv2'];
							$last = (!empty($row['latest_update'])) ? date('d-m-Y H:i', strtotime($row['latest_update'])) : '-';
							$oleh = (isset($users[$row['updated_by']])) ? $users[$row['updated_by']] : '-';
						?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= strtoupper(htmlspecialchars($nama)); ?></td>
								<td class="text-end"><?= (int) $row['jumlah_proses']; ?></td>
								<td class="text-end"><?= $fmt($row['cycletime'], 6); ?></td>
								<td class="text-end"><?= $fmt($row['total_man_hour'], 8); ?></td>
								<td><?= $last; ?></td>
								<td><?= htmlspecialchars($oleh); ?></td>
								<td>
									<div class="d-flex justify-content-center gap-1">
										<?php if ($ENABLE_VIEW) : ?>
											<a class="btn-icon btn-icon-view fp-view" href="javascript:void(0)" title="View" data-id="<?= $row['id']; ?>">
												<i class="ti ti-eye"></i>
											</a>
										<?php endif; ?>
										<?php if ($ENABLE_MANAGE) : ?>
											<a class="btn-icon btn-icon-edit fp-edit" href="javascript:void(0)" title="Edit" data-id="<?= $row['id']; ?>">
												<i class="ti ti-edit"></i>
											</a>
										<?php endif; ?>
										<?php if ($ENABLE_DELETE) : ?>
											<a class="btn-icon btn-icon-delete fp-delete" href="javascript:void(0)" title="Delete" data-id="<?= $row['id']; ?>">
												<i class="ti ti-trash"></i>
											</a>
										<?php endif; ?>
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

<!-- Modal -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<form id="data_form" autocomplete="off">
				<div class="modal-header">
					<h4 class="modal-title" id="head_title">Flow Process &amp; Cycletime</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" id="ModalView">
					<!-- ajax content -->
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" id="btn_save">
						<i class="ti ti-device-floppy me-1"></i> Save
					</button>
					<button type="button" class="btn btn-dark" data-bs-dismiss="modal">
						<i class="ti ti-x me-1"></i> Cancel
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {

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

		// Format angka dengan titik desimal tanpa nol berlebih (hanya tampilan; server menghitung ulang)
		function plain(n, dec) {
			if (!isFinite(n)) return '0';
			var s = n.toFixed(dec);
			s = s.replace(/\.?0+$/, '');
			return (s === '' || s === '-0') ? '0' : s;
		}

		// Hitung Man Hour per baris dan total
		function hitung() {
			var totalCt = 0,
				totalMh = 0,
				ada = false;
			$('#tbl_proses tbody tr.fp-row').each(function(i) {
				$(this).find('.fp-no').text(i + 1);
				var ct = parseFloat($(this).find('.fp-ct').val());
				var mp = parseInt($(this).find('.fp-mp').val(), 10);
				ct = isNaN(ct) ? 0 : ct;
				mp = isNaN(mp) ? 0 : mp;
				var mh = ct * mp / 60;
				$(this).find('.fp-mh').text(plain(mh, 8));
				totalCt += ct;
				totalMh += mh;
				ada = true;
			});
			$('#fp_total_ct').text(ada ? plain(totalCt, 6) : '-');
			$('#fp_total_mh').text(ada ? plain(totalMh, 8) : '-');
		}

		function initSelect2Category() {
			if ($.fn.select2) {
				$('#code_lv2, #ModalView .select2, #ModalView .chosen-select').select2({
					width: '100%',
					dropdownParent: $('#dialog-popup'),
					placeholder: 'Select Product Category'
				});
			}
		}

		function bukaModal(url, judul) {
			$("#head_title").html(judul);
			$.ajax({
				type: 'POST',
				url: url,
				success: function(data) {
					$("#ModalView").html(data);
					$("#btn_save").toggle($('#fp_mode').val() !== 'view');
					$("#dialog-popup").modal('show');
					initSelect2Category();
					hitung();
				},
				error: function() {
					swal({
						title: "Error",
						text: "Gagal memuat data!",
						type: "error"
					});
				}
			});
		}

		$('#dialog-popup').on('shown.bs.modal', function() {
			initSelect2Category();
		});
		$('#dialog-popup').on('hidden.bs.modal', function() {
			if ($('#code_lv2').hasClass('select2-hidden-accessible')) {
				$('#code_lv2').select2('destroy');
			}
			$('#ModalView').html('');
		});

		$(document).on('click', '.fp-add', function() {
			bukaModal(base_url + active_controller + 'form', 'Add Flow Process');
		});
		$(document).on('click', '.fp-edit', function() {
			bukaModal(base_url + active_controller + 'form/' + $(this).data('id'), 'Edit Flow Process');
		});
		$(document).on('click', '.fp-view', function() {
			bukaModal(base_url + active_controller + 'form/' + $(this).data('id') + '/view', 'View Flow Process');
		});

		// Tambah / hapus baris proses, hitung ulang saat angka berubah
		$(document).on('click', '#fp_add_row', function() {
			var html = $('#fp_tpl_row').html().replace('__NO__', $('#tbl_proses tbody tr.fp-row').length + 1);
			$('#tbl_proses tbody').append(html);
			hitung();
		});
		$(document).on('click', '.fp-del', function() {
			$(this).closest('tr').remove();
			hitung();
		});
		$(document).on('input', '.fp-ct, .fp-mp', hitung);

		// SUBMIT (save)
		$(document).on('submit', '#data_form', function(e) {
			e.preventDefault();

			// Validasi cepat di sisi klien (validasi utama tetap di server)
			if ($('#code_lv2').length && $('#code_lv2').val() == '0') {
				swal({
					title: "Error Message!",
					text: 'Product Category belum dipilih.',
					type: "warning"
				});
				return false;
			}
			var valid = true,
				adaProses = false;
			$('#tbl_proses tbody tr.fp-row').each(function() {
				var nama = $.trim($(this).find('[name="process_name[]"]').val());
				var ct = $.trim($(this).find('.fp-ct').val());
				var mp = $.trim($(this).find('.fp-mp').val());
				if (nama === '' && ct === '' && mp === '') return; // baris kosong diabaikan server
				adaProses = true;
				if (nama === '' || !/^\d{1,8}(\.\d{1,6})?$/.test(ct) || !/^\d{1,4}$/.test(mp)) valid = false;
			});
			if (!adaProses || !valid) {
				swal({
					title: "Error Message!",
					text: 'Isi minimal satu proses dengan Process Name, Cycletime (angka, titik untuk desimal), dan Man Power (bilangan bulat).',
					type: "warning"
				});
				return false;
			}

			swal({
				title: "Are you sure?",
				text: "Process this data",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Simpan!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			}, function() {
				$.ajax({
					type: 'POST',
					url: base_url + active_controller + 'save',
					dataType: "json",
					data: $('#data_form').serialize(),
					success: function(res) {
						if (res.status == '1') {
							swal({
								title: "Success",
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
							text: "Error Process!",
							type: "error"
						});
					}
				});
			});
		});

		// DELETE
		$(document).on('click', '.fp-delete', function() {
			var id = $(this).data('id');
			swal({
				title: "Are you sure?",
				text: "Flow Process beserta seluruh prosesnya akan dihapus.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Ya, Hapus!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			}, function() {
				$.ajax({
					type: 'POST',
					url: base_url + active_controller + 'delete',
					dataType: "json",
					data: {
						id: id
					},
					success: function(res) {
						if (res.status == '1') {
							swal({
								title: "Success",
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
							text: "Error Process!",
							type: "error"
						});
					}
				});
			});
		});
	});
</script>
