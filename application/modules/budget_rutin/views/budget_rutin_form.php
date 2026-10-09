<?php
$is_view = isset($is_view) && $is_view;
if (isset($data->code_budget)) {
    $type = 'edit';
}
$type_val = isset($type) ? $type : 'add';
$code_id = isset($data->code_budget) ? $data->code_budget : '';
?>

<div class="card border-0 shadow-sm" id="card-form-budget">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                    <i class="fa fa-boxes-stacked fs-4"></i>
                </div>
                <div>
                    <h5 class="card-title fw-bold text-dark mb-0">
                        <?php
                        if ($is_view) {
                            echo 'Detail Budget Stock Rutin';
                        } elseif ($type_val == 'edit') {
                            echo 'Edit Budget Stock Rutin';
                        } else {
                            echo 'Tambah Budget Stock Rutin Baru';
                        }
                        ?>
                    </h5>
                    <small class="text-muted">
                        <?= ($is_view ? 'Lihat rincian perencanaan budget stock' : 'Tentukan alokasi kebutuhan bulanan dan referensi harga barang per warehouse') ?>
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancel()" title="Tutup Formulir">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form')) ?>
    <input type="hidden" id="type" name="type" value="<?= $type_val ?>">
    <input type="hidden" id="id" name="id" value="<?= set_value('id', $code_id) ?>">
    <input type="hidden" id="rev" name="rev" value="<?= (isset($data->rev) ? $data->rev : '0') ?>">

    <div class="card-body p-4">
        <!-- Warehouse & Top Actions -->
        <div class="row g-3 mb-4 align-items-center bg-light p-3 rounded-3 border">
            <div class="col-md-5 col-sm-12">
                <label class="form-label fw-bold small text-muted mb-1">Warehouse / Gudang <span class="text-danger">*</span></label>
                <?php
                $datdepartemen[0] = '- Pilih Warehouse -';
                echo form_dropdown('department', $datdepartemen, set_value('department', isset($data->department) ? $data->department : '0'), array('id' => 'department', 'class' => 'form-select select2', 'required' => 'required', ($is_view ? 'disabled' : '')));
                ?>
            </div>
            <div class="col-md-7 col-sm-12 text-md-end pt-md-3">
                <div class="d-inline-flex flex-wrap align-items-center gap-2">
                    <?php if (!$is_view && !empty($code_id)) : ?>
                        <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" id="update_price_ref">
                            <i class="fa fa-refresh me-1"></i> Update Price Reference
                        </button>
                    <?php endif; ?>
                    <?php if (!empty($code_id)) : ?>
                        <button type="button" class="btn btn-sm btn-outline-success shadow-sm" id="download_budget_stock" title="Download List Budget Stock" data-id="<?= $code_id ?>">
                            <i class="fa fa-file-excel me-1"></i> Download Excel
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Categories & Items Tables -->
        <div id="category_sections">
            <?php
            $totals = 0;
            if (isset($jenisrutin)) {
                foreach ($jenisrutin as $key) {
            ?>
                    <div class="card border mb-4 shadow-none">
                        <div class="card-header bg-light py-2 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white"><?= strtoupper($key->nm_category) ?></span>
                                <small class="text-muted">Daftar item barang kategori <?= $key->nm_category ?></small>
                            </div>
                            <?php if (!$is_view) : ?>
                                <button type="button" data-id_barang="<?= $key->id ?>" class="btn btn-sm btn-success addPart shadow-sm">
                                    <i class="fa fa-plus me-1"></i> Tambah Item
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle mb-0" id="tbl_<?= $key->id ?>">
                                    <thead class="table-light text-nowrap small text-uppercase fw-semibold">
                                        <tr>
                                            <th class="text-center" width="40">#</th>
                                            <th width="260">Nama Barang</th>
                                            <th width="200">Spesifikasi</th>
                                            <th width="140" class="text-center">Kebutuhan / Bln</th>
                                            <th width="120" class="text-center">Satuan</th>
                                            <th width="160" class="text-end">Price Reference</th>
                                            <th width="160" class="text-end bg-primary-subtle text-primary">Total Price</th>
                                            <?php if (!$is_view) : ?>
                                                <th class="text-center" width="50">#</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="tbody_<?= $key->id ?>"></tbody>
                                    <tfoot>
                                        <tr class="fw-bold bg-light">
                                            <th colspan="6" class="text-end">Total Subtotal (<?= $key->nm_category ?>):</th>
                                            <th class="text-end font-monospace text-primary bg-primary-subtle tfoot_<?= $key->id ?>">0</th>
                                            <?php if (!$is_view) : ?>
                                                <th></th>
                                            <?php endif; ?>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
                $nojenis = 0;
                foreach ($data_jenis as $item_jenis) {
                ?>
                    <div class="card border mb-4 shadow-none">
                        <div class="card-header bg-light py-2 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white"><?= strtoupper($item_jenis->nm_jenis) ?></span>
                                <small class="text-muted">Daftar item barang kategori <?= $item_jenis->nm_jenis ?></small>
                            </div>
                            <?php if (!$is_view) : ?>
                                <button type="button" data-id_barang="<?= $item_jenis->id_jenis ?>" class="btn btn-sm btn-success addPart shadow-sm">
                                    <i class="fa fa-plus me-1"></i> Tambah Item
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle mb-0" id="tbl_<?= $item_jenis->id_jenis ?>">
                                    <thead class="table-light text-nowrap small text-uppercase fw-semibold">
                                        <tr>
                                            <th class="text-center" width="40">#</th>
                                            <th width="260">Nama Barang</th>
                                            <th width="200">Spesifikasi</th>
                                            <th width="140" class="text-center">Kebutuhan / Bln</th>
                                            <th width="120" class="text-center">Satuan</th>
                                            <th width="160" class="text-end">Price Reference</th>
                                            <th width="160" class="text-end bg-primary-subtle text-primary">Total Price</th>
                                            <?php if (!$is_view) : ?>
                                                <th class="text-center" width="50">#</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="tbody_<?= $item_jenis->id_jenis ?>">
                                        <?php
                                        $total_budget_stock = 0;
                                        foreach ($data_detail as $key) {
                                            if ($key->id_type == $item_jenis->id_jenis) {
                                                $nojenis++;
                                                $total_budget_stock += $key->total_price;
                                        ?>
                                                <tr>
                                                    <td class="text-center text-muted small">
                                                        <?= $nojenis ?>
                                                        <input type="hidden" name="jenis_barang[]" value="<?= $key->jenis_barang ?>">
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="id_barang[]" value="<?= $key->id_barang ?>">
                                                        <div class="fw-bold font-monospace text-primary"><?= $key->id_barang ?></div>
                                                        <div class="small text-secondary"><?= $key->nama_barang ?></div>
                                                    </td>
                                                    <td class="small text-muted"><?= $key->spec1 ?></td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm text-center autoNumeric0 hitung_total_budget_stock" name="kebutuhan_month[]" id="kebutuhan_month_<?= $nojenis ?>" onchange="hitungPrice(<?= $nojenis ?>)" data-id_type="<?= $key->id_type ?>" value="<?= $key->kebutuhan_month ?>" <?= ($is_view ? 'readonly' : '') ?>>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="hidden" name="satuan[]" value="<?= $key->id_satuan ?>">
                                                        <span class="badge bg-light text-dark border"><?= $key->nm_satuan ?></span>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="price_reference[]" class="form-control form-control-sm text-end hitung_total_budget_stock autoNumeric0 font-monospace" id="price_reference_<?= $nojenis ?>" onchange="hitungPrice(<?= $nojenis ?>)" data-id_type="<?= $key->id_type ?>" value="<?= $key->price_reference ?>" <?= ($is_view ? 'readonly' : '') ?>>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="total_price[]" class="form-control form-control-sm text-end autoNumeric0 total_price_all total_price_<?= $key->id_type ?> font-monospace fw-bold text-primary bg-light" id="total_price_<?= $nojenis ?>" onchange="hitungPrice(<?= $nojenis ?>)" value="<?= $key->total_price ?>" readonly>
                                                    </td>
                                                    <?php if (!$is_view) : ?>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-outline-danger btn-sm delPart" title="Hapus Baris">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold bg-light">
                                            <th colspan="6" class="text-end">Total Subtotal (<?= $item_jenis->nm_jenis ?>):</th>
                                            <th class="text-end font-monospace text-primary bg-primary-subtle tfoot_<?= $item_jenis->id_jenis ?>">
                                                <?= number_format($total_budget_stock, 0, ',', '.') ?>
                                            </th>
                                            <?php if (!$is_view) : ?>
                                                <th></th>
                                            <?php endif; ?>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
            <?php
                }
            }
            ?>
        </div>

        <!-- Grand Total Summary Card -->
        <div class="card bg-success-subtle border-success-subtle p-3 mb-4 rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 text-success">
                    <i class="fa fa-calculator fs-4"></i>
                    <div>
                        <div class="fw-bold fs-6">Grand Total Estimasi Budget Stock</div>
                        <small class="text-muted">Akumulasi seluruh kategori barang di atas</small>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-success font-monospace" id="grand_total_display">
                    Rp 0
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button type="button" class="btn btn-outline-secondary px-3" onclick="cancel()">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
            </button>
            <?php if (!$is_view) : ?>
                <button type="submit" name="save" class="btn btn-primary px-4 shadow-sm" id="submit">
                    <i class="fa fa-save me-1"></i> Simpan Data Budget
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?= form_close() ?>
</div>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
    var row = <?= $totals ?>;
    var is_view = <?= ($is_view ? 'true' : 'false') ?>;

    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function recalcOverallTotal() {
        var overall = 0;
        $('.total_price_all').each(function() {
            var val = $(this).val();
            if (val !== '') {
                val = val.split(',').join('').split('.').join('');
                val = parseFloat(val);
                if (!isNaN(val)) overall += val;
            }
        });
        $('#grand_total_display').text(formatRupiah(overall));
    }

    $(document).ready(function() {
        $(".divide").divide();
        $('.select2').select2({
            width: '100%'
        });

        $(".autoNumeric0").autoNumeric('init', {
            mDec: '0',
            aPad: false
        });

        recalcOverallTotal();
    });

    $(document).on('click', '.addPart', function() {
        var jenis_barang = $(this).data('id_barang');
        $.ajax({
            url: siteurl + 'budget_rutin/get_material/' + jenis_barang,
            cache: false,
            type: "POST",
            dataType: "json",
            success: function(data) {
                var options = '<option value="">- Pilih Barang -</option>';
                for (var i = 0; i < data.length; i++) {
                    row++;
                    options += '<option value="' + data[i].id + '" data-id_spec="' + (data[i].spec ? data[i].spec : '') + '">' + data[i].stock_name + '</option>';
                }

                var rowHtml = '<tr>' +
                    '<td class="text-center text-muted small">#<input type="hidden" name="jenis_barang[]" value="' + jenis_barang + '"></td>' +
                    '<td><select id="id_barang' + row + '" name="id_barang[]" class="form-select form-select-sm select2" required onchange="getsatuan(' + row + ')">' + options + '</select></td>' +
                    '<td id="spek' + row + '" class="small text-muted"></td>' +
                    '<td><input type="text" class="form-control form-control-sm text-center hitung_total_budget_stock autoNumeric0" data-id_type="' + jenis_barang + '" name="kebutuhan_month[]" id="kebutuhan_month_' + row + '" onchange="hitungPrice(' + row + ')"></td>' +
                    '<td class="text-center"><select id="satuan' + row + '" name="satuan[]" class="form-select form-select-sm text-center" required></select></td>' +
                    '<td><input type="text" class="form-control form-control-sm text-end autoNumeric0 hitung_total_budget_stock font-monospace" data-id_type="' + jenis_barang + '" name="price_reference[]" id="price_reference_' + row + '" onchange="hitungPrice(' + row + ')"></td>' +
                    '<td><input type="text" class="form-control form-control-sm autoNumeric0 text-end total_price_all total_price_' + jenis_barang + ' font-monospace fw-bold text-primary bg-light" name="total_price[]" id="total_price_' + row + '" readonly></td>' +
                    '<td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm delPart" title="Hapus Baris"><i class="fa fa-times"></i></button></td>' +
                    '</tr>';

                $('.tbody_' + jenis_barang).append(rowHtml);
                $(".select2").select2({
                    width: '100%'
                });
                $(".autoNumeric0").autoNumeric('init', {
                    mDec: '0',
                    aPad: false
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Waktu Koneksi Habis',
                    text: 'Gagal mengambil data barang. Silakan coba lagi.'
                });
            }
        });
    });

    function getsatuan(id) {
        var idbarang = $("#id_barang" + id).val();
        var idspec = $("#id_barang" + id).find(':selected').attr('data-id_spec');
        $("#spek" + id).html(idspec);
        if (idbarang != '') {
            $.ajax({
                url: siteurl + 'budget_rutin/get_satuan/' + idbarang,
                method: "POST",
                dataType: 'json',
                success: function(data) {
                    var html = '';
                    for (var i = 0; i < data.length; i++) {
                        html += '<option value="' + data[i].id + '">' + data[i].code + '</option>';
                    }
                    $('#satuan' + id).html(html);
                }
            });

            $.ajax({
                type: 'post',
                url: siteurl + 'budget_rutin/getPriceRef',
                data: {
                    'id_barang': idbarang
                },
                cache: false,
                dataType: 'json',
                success: function(result) {
                    var price_ref = result.nilai_price_ref;
                    var kebutuhan_month = $('#kebutuhan_month_' + id).val();
                    if (kebutuhan_month !== '') {
                        kebutuhan_month = kebutuhan_month.split(',').join('').split('.').join('');
                        kebutuhan_month = parseFloat(kebutuhan_month);
                    } else {
                        kebutuhan_month = 0;
                    }

                    var total_price = (price_ref * kebutuhan_month);

                    $('#price_reference_' + id).autoNumeric('set', price_ref);
                    $('#total_price_' + id).autoNumeric('set', total_price);

                    hitungPrice(id);
                },
                error: function(result) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Gagal mengambil referensi harga.'
                    });
                }
            });
        } else {
            $('#satuan' + id).html('');
        }
    }

    $(document).on('click', '.delPart', function() {
        var $row = $(this).closest("tr");
        var id_type = $row.find('.hitung_total_budget_stock').data('id_type');
        $row.remove();

        if (id_type) {
            var totalPrice = 0;
            $('.total_price_' + id_type).each(function() {
                var total = $(this).val();
                if (total !== '') {
                    total = total.split(',').join('').split('.').join('');
                    total = parseFloat(total);
                } else {
                    total = 0;
                }
                totalPrice += total;
            });
            $('.tfoot_' + id_type).html(number_format(totalPrice));
        }
        recalcOverallTotal();
    });

    function hitungPrice(id) {
        var kebutuhan_month = $('#kebutuhan_month_' + id).val();
        if (kebutuhan_month !== '') {
            kebutuhan_month = kebutuhan_month.split(',').join('').split('.').join('');
            kebutuhan_month = parseFloat(kebutuhan_month);
        } else {
            kebutuhan_month = 0;
        }

        var price_reference = $('#price_reference_' + id).val();
        if (price_reference !== '') {
            price_reference = price_reference.split(',').join('').split('.').join('');
            price_reference = parseFloat(price_reference);
        } else {
            price_reference = 0;
        }

        var total_price = (kebutuhan_month * price_reference);
        $('#total_price_' + id).autoNumeric('set', total_price);

        var id_type = $('#kebutuhan_month_' + id).data('id_type');
        if (id_type) {
            var totalPrice = 0;
            $('.total_price_' + id_type).each(function() {
                var total = $(this).val();
                if (total !== '') {
                    total = total.split(',').join('').split('.').join('');
                    total = parseFloat(total);
                } else {
                    total = 0;
                }
                totalPrice += total;
            });
            $('.tfoot_' + id_type).html(number_format(totalPrice));
        }
        recalcOverallTotal();
    }

    $(document).on('change keyup', '.hitung_total_budget_stock', function() {
        var id_type = $(this).data('id_type');
        var totalPrice = 0;

        $('.total_price_' + id_type).each(function() {
            var total = $(this).val();
            if (total !== '') {
                total = total.split(',').join('').split('.').join('');
                total = parseFloat(total);
            } else {
                total = 0;
            }
            totalPrice += total;
        });

        $('.tfoot_' + id_type).html(number_format(totalPrice));
        recalcOverallTotal();
    });

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();

        var dept = $("#department").val();
        if (!dept || dept == '0') {
            Swal.fire({
                icon: "warning",
                title: "Perhatian",
                text: "Silakan pilih Warehouse / Gudang terlebih dahulu!",
                confirmButtonText: "OK"
            });
            return false;
        }

        Swal.fire({
            title: "Simpan Budget Stock?",
            text: "Pastikan seluruh data barang dan kebutuhan bulanan sudah sesuai.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#0d6efd",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "<i class='fa fa-save me-1'></i> Ya, Simpan",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                var $btn = $("#submit");
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menyimpan data budget stock...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                var formdata = $("#frm_data").serialize();
                $.ajax({
                    url: siteurl + "budget_rutin/save_data",
                    dataType: "json",
                    type: 'POST',
                    data: formdata,
                    success: function(msg) {
                        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Data Budget');
                        if (msg['save'] == true || msg['save'] == '1') {
                            Swal.fire({
                                icon: "success",
                                title: "Sukses!",
                                text: "Data budget stock berhasil disimpan.",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: "Data budget stock gagal disimpan.",
                                confirmButtonText: "Tutup"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Data Budget');
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: "Gagal memproses request AJAX: " + error,
                            confirmButtonText: "Tutup"
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '#update_price_ref', function() {
        Swal.fire({
            title: 'Update Price Reference?',
            text: "Harga referensi seluruh barang akan diperbarui sesuai master harga terbaru.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-refresh me-1"></i> Ya, Perbarui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang memperbarui referensi harga...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: siteurl + 'budget_rutin/update_price_reference',
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Price Reference berhasil diperbarui.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', 'Gagal memperbarui Price Reference.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan sistem saat memperbarui Price Reference.', 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '#download_budget_stock', function() {
        var id = $(this).data('id');
        window.open(siteurl + 'budget_rutin/download_budget_stock/' + id, '_blank');
    });

    function number_format(number) {
        return Math.round(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function cancel() {
        $("#form-data").hide().empty();
        $("#card-list, .box").show();
    }
</script>