<?php
$ENABLE_ADD     = has_permission('Costbook_Stock_Neraca_Gudang.Add');
$ENABLE_MANAGE  = has_permission('Costbook_Stock_Neraca_Gudang.Manage');
$ENABLE_VIEW    = has_permission('Costbook_Stock_Neraca_Gudang.View');
$ENABLE_DELETE  = has_permission('Costbook_Stock_Neraca_Gudang.Delete');
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .card {
        border-radius: 0.75rem;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row align-items-center g-3">
                    <div class="col-md-7 col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="fa fa-scale-balanced fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-0">Costbook Stock Neraca Gudang</h5>
                                <small class="text-muted">Laporan neraca mutasi nilai stok gudang per periode bulan dan tahun</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-12 text-md-end">
                        <button type="button" class="btn btn-outline-primary shadow-sm" id="btn_print_neraca" onclick="printNeracaTable()">
                            <i class="fa fa-print me-1"></i> Print / Cetak Neraca
                        </button>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="row g-2 align-items-end mt-2 pt-3 border-top">
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-calendar-alt text-primary me-1"></i> Bulan</label>
                        <select name="bulan_input" class="form-select select2 periode_bulan">
                            <option value="">-- Pilih Bulan --</option>
                            <?php
                            $current_month = date('n');
                            $bulan_indo = array(
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            );
                            for ($i = 1; $i <= 12; $i++) {
                                $selected = ($i == $current_month) ? 'selected' : '';
                                echo '<option value="' . $i . '" ' . $selected . '>' . $bulan_indo[$i] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-calendar text-primary me-1"></i> Tahun</label>
                        <select name="tahun_input" class="form-select select2 periode_tahun">
                            <option value="">-- Pilih Tahun --</option>
                            <?php
                            $current_year = date('Y');
                            for ($y = $current_year + 1; $y >= 2019; $y--) {
                                $selected = ($y == $current_year) ? 'selected' : '';
                                echo '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-8 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-warehouse text-primary me-1"></i> Gudang</label>
                        <select class="form-select select2 select_warehouse">
                            <option value="">-- SEMUA GUDANG --</option>
                            <?php foreach ($results['list_warehouse'] as $item_warehouse) : ?>
                                <option value="<?= $item_warehouse->id ?>"><?= strtoupper($item_warehouse->nm_gudang) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-4 col-12 d-flex gap-2">
                        <button type="button" class="btn btn-primary w-100 get_costbook_data shadow-sm">
                            <i class="fa fa-search me-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-4" id="table_container_neraca">
                <div class="table-responsive">
                    <table id="example1" class="table table-hover table-striped align-middle border rounded" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="4%">#</th>
                                <th class="text-start" width="16%">Transaksi</th>
                                <th class="text-center" width="13%">No. Transaksi</th>
                                <th class="text-center" width="11%">Tgl Transaksi</th>
                                <th class="text-center" width="7%">Tipe</th>
                                <th class="text-center" width="12%">Dari Gudang</th>
                                <th class="text-center" width="12%">Ke Gudang</th>
                                <th class="text-end" width="11%">Value In</th>
                                <th class="text-end" width="11%">Value Out</th>
                                <th class="text-end" width="13%">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="list_data">
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted empty-state">
                                    <i class="fa fa-circle-info fs-3 d-block mb-2 text-primary"></i>
                                    Silakan tentukan filter <b>Bulan</b>, <b>Tahun</b>, dan <b>Gudang</b> lalu klik tombol <b>Tampilkan</b> untuk memuat neraca.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        if ($('.select2').length) {
            $('.select2').select2({ width: '100%' });
        }
    });

    $(document).on('click', '.get_costbook_data', function() {
        var btn = $(this);
        var originalText = btn.html();

        var periode_bulan = $('.periode_bulan').val();
        var periode_tahun = $('.periode_tahun').val();
        var warehouse = $('.select_warehouse').val();

        if (!periode_bulan || !periode_tahun) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih Periode Bulan dan Tahun terlebih dahulu!'
                });
            } else if (typeof swal === 'function') {
                swal({
                    title: 'Peringatan!',
                    text: 'Silakan pilih Periode Bulan dan Tahun terlebih dahulu!',
                    type: 'warning'
                });
            } else {
                alert('Silakan pilih Periode Bulan dan Tahun terlebih dahulu!');
            }
            return false;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Memuat...');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'get_costbook_data',
            data: {
                'periode_bulan': periode_bulan,
                'periode_tahun': periode_tahun,
                'warehouse': warehouse
            },
            cache: false,
            success: function(result) {
                btn.prop('disabled', false).html(originalText);
                $('#table_container_neraca').html(result);
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan sistem saat memuat data neraca gudang.'
                    });
                } else {
                    alert('Terjadi kesalahan sistem saat memuat data neraca gudang.');
                }
            }
        });
    });

    // Cetak HANYA tabel neraca gudang dengan validasi data kosong
    function printNeracaTable() {
        var tableElem = $('#example1');
        if (tableElem.length === 0) {
            showEmptyAlert();
            return false;
        }

        // Cek apakah tabel masih berstatus empty-state atau hanya berisi placeholder
        var validRows = tableElem.find('tbody tr').filter(function() {
            return !$(this).find('.empty-state').length && !$(this).find('.dataTables_empty').length && $(this).find('td').length > 1;
        });

        if (validRows.length === 0) {
            showEmptyAlert();
            return false;
        }

        var bulanText = $('.periode_bulan option:selected').text();
        var tahunText = $('.periode_tahun').val() || '<?= date("Y") ?>';
        var warehouseName = $('.select_warehouse option:selected').text();

        var printWindow = window.open('', '', 'height=750,width=1050');
        printWindow.document.write('<!DOCTYPE html><html><head><title>Cetak Neraca Gudang</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('@page { size: landscape; margin: 10mm; }');
        printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 10px; }');
        printWindow.document.write('.header-cetak { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #333; padding-bottom: 8px; }');
        printWindow.document.write('.header-cetak h3 { margin: 0 0 4px 0; font-size: 15px; text-transform: uppercase; }');
        printWindow.document.write('.header-cetak p { margin: 2px 0; font-size: 10.5px; color: #555; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 8px; }');
        printWindow.document.write('th, td { border: 1px solid #777; padding: 5px 7px; font-size: 10.5px; }');
        printWindow.document.write('th { background-color: #f2f2f2 !important; text-align: center; font-weight: bold; }');
        printWindow.document.write('.text-center { text-align: center; }');
        printWindow.document.write('.text-start { text-align: left; }');
        printWindow.document.write('.text-end { text-align: right; }');
        printWindow.document.write('.fw-bold { font-weight: bold; }');
        printWindow.document.write('.badge { border: 1px solid #999; padding: 1px 4px; font-size: 10px; border-radius: 2px; }');
        printWindow.document.write('</style></head><body>');

        printWindow.document.write('<div class="header-cetak">');
        printWindow.document.write('<h3>LAPORAN COSTBOOK STOK NERACA GUDANG</h3>');
        printWindow.document.write('<p>Periode: <b>' + bulanText + ' ' + tahunText + '</b> | Gudang: <b>' + warehouseName + '</b> | Dicetak: ' + new Date().toLocaleString('id-ID') + '</p>');
        printWindow.document.write('</div>');

        printWindow.document.write(tableElem[0].outerHTML);
        printWindow.document.write('</body></html>');

        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    function showEmptyAlert() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mencetak!',
                text: 'Tidak ada data neraca di dalam tabel untuk dicetak. Silakan tampilkan data terlebih dahulu.'
            });
        } else if (typeof swal === 'function') {
            swal({
                title: 'Gagal Mencetak!',
                text: 'Tidak ada data neraca di dalam tabel untuk dicetak. Silakan tampilkan data terlebih dahulu.',
                type: 'error'
            });
        } else {
            alert('Gagal Mencetak: Tidak ada data neraca di dalam tabel untuk dicetak.');
        }
    }
</script>
