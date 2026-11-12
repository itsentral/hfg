<?php
$id             = (!empty($header)) ? $header[0]->id : '';
$id_stock       = (!empty($header)) ? $header[0]->id_stock : '';
$id_category    = (!empty($header)) ? $header[0]->id_category : '';
$stock_name     = (!empty($header)) ? $header[0]->stock_name : '';
$trade_name     = (!empty($header)) ? $header[0]->trade_name : '';
$brand          = (!empty($header)) ? $header[0]->brand : '';
$spec           = (!empty($header)) ? $header[0]->spec : '';
$id_unit_gudang = (!empty($header)) ? $header[0]->id_unit_gudang : '';
$konversi       = (!empty($header)) ? $header[0]->konversi : '';
$id_unit        = (!empty($header)) ? $header[0]->id_unit : '';
$min_stok       = (!empty($header)) ? $header[0]->min_stok : '';
$max_stok       = (!empty($header)) ? $header[0]->max_stok : '';
$min_order      = (!empty($header)) ? $header[0]->min_order : 0;
$coa            = (!empty($header)) ? $header[0]->no_coa : 0;

$is_view = (!empty($results['tanda']) && $results['tanda'] == 'view');
$disabled = $is_view ? 'disabled' : '';

$status1 = '';
$status2 = '';
if (!empty($id)) {
	$status1 = ($header[0]->status == '1') ? 'checked' : '';
	$status2 = ($header[0]->status == '0') ? 'checked' : '';
} else {
	$status1 = 'checked';
}
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fa <?= empty($id) ? 'fa-plus' : ($is_view ? 'fa-eye' : 'fa-edit') ?> me-2"></i><?= empty($id) ? 'Add Barang Stok' : ($is_view ? 'Detail Barang Stok' : 'Edit Barang Stok') ?>
        </h5>
        <a href="<?= base_url('accessories') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="data-form" method="post" autocomplete="off">
            <input type="hidden" id="id" name="id" value='<?= $id; ?>'>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Category Stok <span class="text-danger">*</span></label>
                    <select id="id_category" name="id_category" class="form-select chosen-select" required <?= $disabled; ?>>
                        <option value="0">Select An Option</option>
                        <?php foreach ($results['category'] as $kel) {
                            $sel = ($kel->id == $id_category) ? 'selected' : '';
                        ?>
                            <option value="<?= $kel->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($kel->nm_category)) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Stok Name <span class="text-danger">*</span></label>
                    <input type="text" id="stock_name" name="stock_name" class="form-control" required placeholder="Stok Name" value='<?= $stock_name; ?>' <?= $disabled; ?>>
                </div>
            </div>

            <hr class="my-4 text-muted">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Item Code</label>
                    <input type="text" id="id_stock" name="id_stock" class="form-control" placeholder="Item Code" value='<?= $id_stock; ?>' <?= $disabled; ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Trade Name</label>
                    <input type="text" id="trade_name" name="trade_name" class="form-control" placeholder="Trade Name" value='<?= $trade_name; ?>' <?= $disabled; ?>>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Brand</label>
                    <input type="text" id="brand" name="brand" class="form-control" placeholder="Brand" value='<?= $brand; ?>' <?= $disabled; ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Spesification</label>
                    <input type="text" id="spec" name="spec" class="form-control" placeholder="Spesification" value='<?= $spec; ?>' <?= $disabled; ?>>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Packing Unit</label>
                    <select id="id_unit_gudang" name="id_unit_gudang" class="form-select chosen-select" <?= $disabled; ?>>
                        <option value="0">Select An Option</option>
                        <?php foreach ($results['satuan_packing'] as $satuan) {
                            $sel = ($satuan->id == $id_unit_gudang) ? 'selected' : '';
                        ?>
                            <option value="<?= $satuan->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($satuan->code)) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Konversi</label>
                    <input type="text" id="konversi" name="konversi" class="form-control autoNumeric" placeholder="Konversi" value='<?= $konversi; ?>' <?= $disabled; ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Unit Measurement</label>
                    <select id="id_unit" name="id_unit" class="form-select chosen-select" <?= $disabled; ?>>
                        <option value="0">Select An Option</option>
                        <?php foreach ($results['satuan'] as $satuan) {
                            $sel = ($satuan->id == $id_unit) ? 'selected' : '';
                        ?>
                            <option value="<?= $satuan->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($satuan->code)) ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Minimum Order</label>
                    <input type="text" name="min_order" id="min_order" class="form-control autoNumeric" value="<?= $min_order; ?>" <?= $disabled; ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">COA</label>
                    <select class="form-select chosen-select" name="coa" id="coa" <?= $disabled; ?>>
                        <option value="">- Select COA -</option>
                        <?php
                        foreach ($list_coa as $item) {
                            $selected = '';
                            if ($item->no_perkiraan == $coa) {
                                $selected = 'selected';
                            }
                            echo '<option value="' . $item->no_perkiraan . '" ' . $selected . '>' . $item->no_perkiraan . ' - ' . $item->nama . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <?php if (!empty($id)) { ?>
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold d-block">Status</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="status1" value="1" <?= $status1; ?> <?= $disabled; ?>>
                            <label class="form-check-label" for="status1">Aktif</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="status0" value="0" <?= $status2; ?> <?= $disabled; ?>>
                            <label class="form-check-label" for="status0">Non-Aktif</label>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-secondary" name="back" id="back">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </button>
                <?php if (!$is_view) { ?>
                    <button type="submit" class="btn btn-primary" name="save" id="save">
                        <i class="fa fa-save me-1"></i> Save
                    </button>
                <?php } ?>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

	$(document).ready(function() {
		$('.chosen-select').select2({width: '100%'});
		$('.autoNumeric').autoNumeric();

		$(document).on('click', '#back', function() {
			window.location.href = base_url + active_controller;
		});

		$(document).on('click', '#save', function(e) {
			e.preventDefault();

			var id_category = $('#id_category').val();
			var stock_name = $('#stock_name').val();

			if (id_category == '0') {
				swal({
					title: "Error Message!",
					text: 'Category stok empty, select first ...',
					type: "warning"
				});
				$('#save').prop('disabled', false);
				return false;
			}
			if (stock_name == '') {
				swal({
					title: "Error Message!",
					text: 'Stok name empty, select first ...',
					type: "warning"
				});
				$('#save').prop('disabled', false);
				return false;
			}

			swal({
					title: "Are you sure?",
					text: "You will not be able to process again this data!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes, Process it!",
					cancelButtonText: "No, cancel process!",
					closeOnConfirm: true,
					closeOnCancel: false
				},
				function(isConfirm) {
					if (isConfirm) {
						var formData = $('#data-form').serialize();
						var baseurl = siteurl + active_controller + '/add';
						$.ajax({
							url: baseurl,
							type: "POST",
							data: formData,
							cache: false,
							dataType: 'json',
							success: function(data) {
								if (data.status == 1) {
									swal({
										title: "Save Success!",
										text: data.pesan,
										type: "success",
										timer: 3000
									});
									window.location.href = base_url + active_controller;
								} else {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 3000
									});
								}
							},
							error: function() {
								swal({
									title: "Error Message !",
									text: 'An Error Occurred During Process. Please try again..',
									type: "warning",
									timer: 3000
								});
							}
						});
					} else {
						swal("Cancelled", "Data can be processed again :)", "error");
						return false;
					}
				});
		});
	});
</script>