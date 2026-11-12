<?php
// Detail Approval
$alasan_reject1 = (!empty($header)) ? $header[0]['reject_reason1'] : '';
$alasan_reject2 = (!empty($header)) ? $header[0]['reject_reason2'] : '';
$alasan_reject3 = (!empty($header)) ? $header[0]['reject_reason3'] : '';

$keterangan_1 = (!empty($header)) ? $header[0]['keterangan_1'] : '';
$keterangan_2 = (!empty($header)) ? $header[0]['keterangan_2'] : '';
$keterangan_3 = (!empty($header)) ? $header[0]['keterangan_3'] : '';

$status1 = '<span class="badge bg-secondary">Menunggu</span>';
$tgl_appre_1 = '-';
$status2 = '<span class="badge bg-secondary">Menunggu</span>';
$tgl_appre_2 = '-';
$status3 = '<span class="badge bg-secondary">Menunggu</span>';
$tgl_appre_3 = '-';

if (!empty($header)) {
    if ($header[0]['app_1'] == '1') {
        $status1 = '<span class="badge bg-success">Approved</span>';
        $tgl_appre_1 = date('d F Y', strtotime($header[0]['app_1_date']));
    } else {
        if ($header[0]['sts_reject1'] == '1') {
            $status1 = '<span class="badge bg-danger">Rejected</span>';
            $tgl_appre_1 = date('d F Y', strtotime($header[0]['sts_reject1_date']));
        }
    }

    if ($header[0]['app_2'] == '1') {
        $status2 = '<span class="badge bg-success">Approved</span>';
        $tgl_appre_2 = date('d F Y', strtotime($header[0]['app_2_date']));
    } else {
        if ($header[0]['sts_reject2'] == '1') {
            $status2 = '<span class="badge bg-danger">Rejected</span>';
            $tgl_appre_2 = date('d F Y', strtotime($header[0]['sts_reject2_date']));
        }
    }

    if ($header[0]['app_3'] == '1') {
        $status3 = '<span class="badge bg-success">Approved</span>';
        $tgl_appre_3 = date('d F Y', strtotime($header[0]['app_3_date']));
    } else {
        if ($header[0]['sts_reject3'] == '1') {
            $status3 = '<span class="badge bg-danger">Rejected</span>';
            $tgl_appre_3 = date('d F Y', strtotime($header[0]['sts_reject3_date']));
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <form id="data-form" method="post" autocomplete="off">
            <input type="hidden" name="so_number" id="so_number" value="<?= htmlspecialchars($header[0]['so_number'] ?? ''); ?>">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7 col-12">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                    <i class="fa fa-pencil-square-o fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="card-title fw-bold text-dark mb-0">Edit Pengajuan PR Stok</h5>
                                    <small class="text-muted">No. PR: <span class="badge bg-primary bg-opacity-10 text-primary fw-bold"><?= htmlspecialchars($header[0]['no_pr'] ?? '-') ?></span> &bull; No. SO: <?= htmlspecialchars($header[0]['so_number'] ?? '-') ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 col-12 text-md-end d-flex gap-2 justify-content-md-end">
                            <a href="<?= base_url('request_pr_stok') ?>" class="btn btn-secondary shadow-sm" id="back">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="button" class="btn btn-primary shadow-sm" name="save" id="save">
                                <i class="fa fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Form Metadata Grid -->
                    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold text-dark mb-1">Customer / Keperluan</label>
                            <input type="text" class="form-control bg-white" value="<?= htmlspecialchars($header[0]['nm_customer'] ?? 'Pengisian Stok Internal'); ?>" readonly>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold text-dark mb-1">Tingkat Urgensi PR</label>
                            <select name="tingkat_pr" id="tingkat_pr" class="form-select select2">
                                <option value="1" <?= (($header[0]['tingkat_pr'] ?? 1) == 1) ? 'selected' : null ?>>Normal</option>
                                <option value="2" <?= (($header[0]['tingkat_pr'] ?? 1) == 2) ? 'selected' : null ?>>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold text-dark mb-1">Tanggal Dibutuhkan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa fa-calendar-check-o text-muted"></i></span>
                                <input type="text" name="tgl_dibutuhkan" id="tgl_dibutuhkan" class="form-control flatpickr-date bg-white" value="<?= htmlspecialchars($header[0]['tgl_dibutuhkan'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Approval Log Table -->
                    <div class="card border mb-4">
                        <div class="card-header bg-white py-2">
                            <span class="fw-bold text-dark"><i class="fa fa-shield text-primary me-1"></i> Status Review & Keterangan Tambahan</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start" style="width: 200px;">Tahap Review</th>
                                            <th class="text-center" style="width: 130px;">Status</th>
                                            <th class="text-center" style="width: 160px;">Tgl Review</th>
                                            <th class="text-start" style="width: 250px;">Alasan Reject</th>
                                            <th class="text-start">Keterangan / Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold">Department Head</td>
                                            <td class="text-center"><?= $status1 ?></td>
                                            <td class="text-center text-muted small"><?= $tgl_appre_1 ?></td>
                                            <td><input type="text" name="reject_reason1" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($alasan_reject1) ?>" readonly></td>
                                            <td><input type="text" name="keterangan_1" class="form-control form-control-sm" value="<?= htmlspecialchars($keterangan_1) ?>" placeholder="Catatan Dept Head"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Cost Control</td>
                                            <td class="text-center"><?= $status2 ?></td>
                                            <td class="text-center text-muted small"><?= $tgl_appre_2 ?></td>
                                            <td><input type="text" name="reject_reason2" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($alasan_reject2) ?>" readonly></td>
                                            <td><input type="text" name="keterangan_2" class="form-control form-control-sm" value="<?= htmlspecialchars($keterangan_2) ?>" placeholder="Catatan Cost Control"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Management / Direktur</td>
                                            <td class="text-center"><?= $status3 ?></td>
                                            <td class="text-center text-muted small"><?= $tgl_appre_3 ?></td>
                                            <td><input type="text" name="reject_reason3" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($alasan_reject3) ?>" readonly></td>
                                            <td><input type="text" name="keterangan_3" class="form-control form-control-sm" value="<?= htmlspecialchars($keterangan_3) ?>" placeholder="Catatan Management"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Items Currently in PR -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0"><i class="fa fa-list-ul text-primary me-1"></i> Rincian Item PR Berjalan</h6>
                            <small class="text-muted">Edit kuantitas atau catatan item, lalu klik tombol Update</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-striped border-top mb-0" width="100%">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">#</th>
                                        <th class="text-start">Nama Barang</th>
                                        <th class="text-end" style="width: 100px;">Min Stock</th>
                                        <th class="text-end" style="width: 100px;">Max Stock</th>
                                        <th class="text-end" style="width: 100px;">Min Order</th>
                                        <th class="text-center" style="width: 130px;">Qty PR</th>
                                        <th class="text-center" style="width: 130px;">Status</th>
                                        <th class="text-start">Catatan Item</th>
                                        <th class="text-center" style="width: 110px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($detail as $key => $value) {
                                        $key++;
                                        $propose = $value['propose_purchase'] ?? 0;
                                        if ($propose > 0) {
                                            echo "<tr>";
                                            echo "<td class='text-center'>" . $key . "</td>";
                                            echo "<td class='text-start fw-semibold'>" . htmlspecialchars($value['stock_name'] ?? '-') . "</td>";
                                            echo "<td class='text-end'>" . number_format($value['min_stok'] ?? 0, 2) . "</td>";
                                            echo "<td class='text-end'>" . number_format($value['max_stok'] ?? 0, 2) . "</td>";
                                            echo "<td class='text-end'>" . number_format(0, 2) . "</td>";

                                            if (($value['status_app'] ?? 'N') == 'N') {
                                                echo "<td class='text-center'>";
                                                echo "<input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>";
                                                echo "<input type='text' name='detail[" . $key . "][qty]' class='form-control form-control-sm text-center qty_" . $value['id'] . " autoNumeric2' style='min-width: 90px;' value='" . $propose . "'>";
                                                echo "</td>";
                                                echo "<td class='text-center'><span class='badge bg-warning text-dark'>Waiting Process</span></td>";
                                            } elseif (($value['status_app'] ?? '') == 'Y') {
                                                echo "<td class='text-center fw-bold'>" . number_format($propose, 2) . "</td>";
                                                echo "<td class='text-center'><span class='badge bg-success'>Approved</span></td>";
                                            } elseif (($value['status_app'] ?? '') == 'D') {
                                                echo "<td class='text-center fw-bold'>" . number_format($propose, 2) . "</td>";
                                                echo "<td class='text-center'><span class='badge bg-danger'>Rejected</span></td>";
                                            }

                                            echo "<td>";
                                            echo "<input type='text' class='form-control form-control-sm notes_" . $value['id'] . "' name='detail[" . $key . "][notes]' value='" . htmlspecialchars($value['note'] ?? '') . "' placeholder='Catatan...'>";
                                            echo "</td>";
                                            echo "<td class='text-center'>";
                                            echo "<div class='btn-group btn-group-sm'>";
                                            echo "<button type='button' class='btn btn-warning edit_detail edit_detail_" . $value['id'] . "' data-key='" . $key . "' data-id='" . $value['id'] . "' title='Simpan Item Ini'><i class='fa fa-pencil'></i></button>";
                                            echo "<button type='button' class='btn btn-danger del_detail del_detail_" . $value['id'] . "' data-key='" . $key . "' data-id='" . $value['id'] . "' title='Hapus Item Ini'><i class='fa fa-trash'></i></button>";
                                            echo "</div>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Items Not in PR (Add New Item Section) -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <span class="fw-bold text-dark"><i class="fa fa-plus-circle text-success me-1"></i> Tambah Barang Lain ke PR Ini</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-striped border-top table_data mb-0" width="100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 40px;">#</th>
                                            <th class="text-start">Nama Barang</th>
                                            <th class="text-end" style="width: 100px;">Min Stock</th>
                                            <th class="text-end" style="width: 100px;">Max Stock</th>
                                            <th class="text-end" style="width: 100px;">Min Order</th>
                                            <th class="text-center" style="width: 130px;">Qty Usulan</th>
                                            <th class="text-start">Catatan</th>
                                            <th class="text-center" style="width: 70px;">Tambah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        if (!empty($list_stok_non_pr)) {
                                            foreach ($list_stok_non_pr as $item) {
                                                echo '<tr>';
                                                echo '<td class="text-center">' . $no . '</td>';
                                                echo '<td class="text-start fw-semibold">' . htmlspecialchars($item['stock_name']) . '</td>';
                                                echo '<td class="text-end">' . number_format($item['min_stok'], 2) . '</td>';
                                                echo '<td class="text-end">' . number_format($item['max_stok'], 2) . '</td>';
                                                echo '<td class="text-end">' . number_format(0, 2) . '</td>';
                                                echo '<td>';
                                                echo '<input type="text" class="form-control form-control-sm text-center autoNumeric2 nmat_qty_' . $item['id'] . '" placeholder="Qty">';
                                                echo '</td>';
                                                echo '<td>';
                                                echo '<input type="text" class="form-control form-control-sm nmat_notes_' . $item['id'] . '" placeholder="Catatan...">';
                                                echo '</td>';
                                                echo '<td class="text-center">';
                                                echo '<button type="button" class="btn btn-sm btn-success add_stock add_stock_' . $item['id'] . '" data-id="' . $item['id'] . '" title="Tambahkan ke PR"><i class="fa fa-plus"></i></button>';
                                                echo '</td>';
                                                echo '</tr>';
                                                $no++;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="<?= base_url('request_pr_stok') ?>" class="btn btn-secondary shadow-sm">
                            <i class="fa fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-primary shadow-sm px-4" id="saveBottom">
                            <i class="fa fa-save me-1"></i> Simpan Seluruh Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';
    var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
    var so_number = $('#so_number').val();

    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({ width: '100%' });
        }

        if (typeof flatpickr !== 'undefined') {
            flatpickr('.flatpickr-date', {
                dateFormat: 'Y-m-d',
                allowInput: false
            });
        }

        if ($.fn.dataTable) {
            $('.table_data').DataTable({
                "pageLength": 10,
                "language": {
                    "search": "Cari Barang:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "paginate": { "next": "Lanjut", "previous": "Sebelum" }
                }
            });
        }

        $('.autoNumeric2').autoNumeric('init', {
            mDec: '2',
            aPad: false
        });

        $(document).on('click', '#saveBottom', function() {
            $('#save').trigger('click');
        });

        $('#save').click(function(e) {
            e.preventDefault();

            swal({
                title: "Konfirmasi Update",
                text: "Simpan pembaruan untuk dokumen PR ini?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-primary",
                confirmButtonText: "Ya, Simpan",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    var formData = new FormData($('#data-form')[0]);
                    $.ajax({
                        url: siteurl + active_controller + '/process_update_all',
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Berhasil!",
                                    text: data.pesan,
                                    type: "success"
                                }, function() {
                                    window.location.href = base_url + active_controller;
                                });
                            } else {
                                swal({
                                    title: "Gagal!",
                                    text: data.pesan,
                                    type: "warning"
                                });
                            }
                        },
                        error: function() {
                            swal({
                                title: "Error!",
                                text: 'Terjadi kesalahan sistem saat menyimpan data.',
                                type: "error"
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.edit_detail', function() {
            var key = $(this).data('key');
            var id = $(this).data('id');
            var qty = $('.qty_' + id).val();
            qty = qty ? parseFloat(qty.split(',').join('')) : 0;
            var notes = $('.notes_' + id).val();

            $.ajax({
                type: "POST",
                url: siteurl + active_controller + '/edit_detail',
                data: {
                    'key': key,
                    'id': id,
                    'qty': qty,
                    'notes': notes
                },
                cache: false,
                dataType: 'json',
                beforeSend: function() {
                    $('.edit_detail_' + id).html('<i class="fa fa-spin fa-spinner"></i>');
                },
                success: function(result) {
                    if (result.status == 1) {
                        swal({
                            type: 'success',
                            title: 'Berhasil!',
                            text: 'Rincian item berhasil diperbarui!',
                            timer: 2000
                        });
                    } else {
                        swal({
                            type: 'error',
                            title: 'Gagal!',
                            text: 'Item gagal diperbarui.'
                        });
                    }
                    $('.edit_detail_' + id).html('<i class="fa fa-pencil"></i>');
                },
                error: function() {
                    swal({
                        title: 'Error!',
                        text: 'Terjadi kesalahan jaringan.',
                        type: 'error'
                    });
                    $('.edit_detail_' + id).html('<i class="fa fa-pencil"></i>');
                }
            });
        });

        $(document).on('click', '.del_detail', function() {
            var id = $(this).data('id');

            swal({
                title: "Konfirmasi Hapus",
                text: "Item ini akan dihapus dari pengajuan PR!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Ya, Hapus",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: "POST",
                        url: siteurl + active_controller + '/del_detail',
                        data: { 'id': id },
                        cache: false,
                        dataType: 'json',
                        success: function(result) {
                            if (result.status == '1') {
                                swal({
                                    title: 'Berhasil!',
                                    text: 'Item berhasil dihapus!',
                                    type: 'success'
                                }, function() {
                                    location.reload();
                                });
                            } else {
                                swal({
                                    title: 'Gagal!',
                                    text: 'Item gagal dihapus.',
                                    type: 'error'
                                });
                            }
                        },
                        error: function() {
                            swal({
                                type: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan sistem.'
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.add_stock', function() {
            var id = $(this).data('id');
            var qty = $('.nmat_qty_' + id).val();
            qty = qty ? parseFloat(qty.split(',').join('')) : 0;
            var notes = $('.nmat_notes_' + id).val();

            if (qty <= 0) {
                swal({
                    title: "Peringatan",
                    text: "Isikan kuantitas propose purchase lebih dari 0.",
                    type: "warning"
                });
                return false;
            }

            $.ajax({
                type: "POST",
                url: siteurl + active_controller + '/add_stok',
                data: {
                    'so_number': so_number,
                    'id': id,
                    'qty': qty,
                    'notes': notes
                },
                cache: false,
                dataType: 'json',
                beforeSend: function() {
                    $('.add_stock_' + id).html('<i class="fa fa-spin fa-spinner"></i>');
                    $('.add_stock_' + id).prop('disabled', true);
                },
                success: function(result) {
                    if (result.status == '1') {
                        swal({
                            type: 'success',
                            title: 'Berhasil!',
                            text: result.msg
                        }, function() {
                            location.reload();
                        });
                    } else {
                        swal({
                            type: 'warning',
                            title: 'Gagal!',
                            text: result.msg
                        });
                        $('.add_stock_' + id).html('<i class="fa fa-plus"></i>');
                        $('.add_stock_' + id).prop('disabled', false);
                    }
                },
                error: function() {
                    swal({
                        type: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem.'
                    });
                    $('.add_stock_' + id).html('<i class="fa fa-plus"></i>');
                    $('.add_stock_' + id).prop('disabled', false);
                }
            });
        });
    });
</script>
