<?php
$ENABLE_ADD     = has_permission('Costbook_Stock_Per_Item.Add');
$ENABLE_MANAGE  = has_permission('Costbook_Stock_Per_Item.Manage');
$ENABLE_VIEW    = has_permission('Costbook_Stock_Per_Item.View');
$ENABLE_DELETE  = has_permission('Costbook_Stock_Per_Item.Delete');
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    .flatpickr-input {
        background-color: #fff !important;
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
                                <i class="fa fa-book-bookmark fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-0">Costbook Stock Per Item</h5>
                                <small class="text-muted">Pantau nilai harga perolehan (costbook), saldo stok, dan nilai neraca per barang per gudang</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-12 text-md-end">
                        <button type="button" class="btn btn-outline-primary shadow-sm" id="btn_print_table" onclick="printTableList()">
                            <i class="fa fa-print me-1"></i> Print / Cetak Tabel
                        </button>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="row g-2 align-items-end mt-2 pt-3 border-top">
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-calendar-alt text-primary me-1"></i> Dari Tanggal</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control flatpickr tgl_from" placeholder="Pilih Dari Tanggal" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-calendar-check text-primary me-1"></i> Sampai Tanggal</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control flatpickr tgl_to" placeholder="Pilih Sampai Tanggal" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-8 col-12">
                        <label class="form-label fw-bold text-dark mb-1"><i class="fa fa-warehouse text-primary me-1"></i> Pilih Gudang</label>
                        <select class="form-select select2 select_warehouse">
                            <option value="">-- SEMUA GUDANG --</option>
                            <?php foreach ($results['list_warehouse'] as $item_warehouse) : ?>
                                <option value="<?= $item_warehouse->id ?>"><?= strtoupper($item_warehouse->nm_gudang) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-4 col-12 d-flex gap-2">
                        <button type="button" class="btn btn-primary w-100 change_warehouse shadow-sm">
                            <i class="fa fa-search me-1"></i> Cari
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-4" id="table_container">
                <div class="table-responsive">
                    <table id="example1" class="table table-hover table-striped align-middle border rounded" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="4%">#</th>
                                <th class="text-center" width="12%">Kode Produk</th>
                                <th class="text-start">Nama Barang</th>
                                <th class="text-center" width="15%">Gudang</th>
                                <th class="text-end" width="10%">Qty Stok</th>
                                <th class="text-end" width="12%">Nilai Costbook</th>
                                <th class="text-end" width="13%">Value Neraca</th>
                                <th class="text-center" width="14%">Update Terakhir</th>
                                <th class="text-center" width="8%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($results['list_data'] as $item) :
                                $nilai_costbook = 0;
                                $value_neraca = 0;
                                $last_update = '-';
                                $get_nilai_costbook = $this->db->query("SELECT a.costbook as nilai_costbook, a.created_on, a.value_neraca FROM tr_cost_book a WHERE a.id_material = '" . $item->id_material . "' AND (a.id_gudang_dari = '" . $item->id_gudang . "' OR a.id_gudang_ke = '" . $item->id_gudang . "') ORDER BY a.created_on DESC LIMIT 1")->row();
                                if (!empty($get_nilai_costbook)) {
                                    $nilai_costbook = $get_nilai_costbook->nilai_costbook;
                                    $value_neraca = $get_nilai_costbook->value_neraca;
                                    $last_update = date('d-M-Y H:i', strtotime($get_nilai_costbook->created_on));
                                }
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no ?></td>
                                    <td class="text-center font-monospace fw-semibold text-primary"><?= $item->kode_produk ?></td>
                                    <td class="text-start fw-bold text-dark"><?= strtoupper($item->nm_material) ?></td>
                                    <td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-1"><?= strtoupper($item->nm_gudang) ?></span></td>
                                    <td class="text-end fw-semibold"><?= number_format($item->qty_stock, 2) ?></td>
                                    <td class="text-end fw-bold text-success font-monospace">Rp <?= number_format($nilai_costbook, 2) ?></td>
                                    <td class="text-end fw-bold text-primary font-monospace">Rp <?= number_format($value_neraca, 2) ?></td>
                                    <td class="text-center text-muted font-monospace" style="font-size:0.85rem;"><?= $last_update ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-info check_costbook shadow-sm" data-id_material="<?= $item->id_material ?>" data-id_gudang="<?= $item->id_gudang ?>" title="Lihat Riwayat Transaksi Costbook">
                                            <i class="fa fa-eye me-1"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            <?php
                                $no++;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dialog Detail Costbook -->
<div class="modal fade" id="dialog-popup" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="myModalLabel"><i class="fa fa-list-check me-2"></i>Detail Transaksi Costbook Barang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="ModalView">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memuat rincian transaksi costbook...</p>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-primary" onclick="printModalContent()">
                    <i class="fa fa-print me-1"></i> Cetak / Print Rincian
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script type="text/javascript">
    $(document).ready(function() {
        if ($('.select2').length) {
            $('.select2').select2({ width: '100%' });
        }

        // Inisialisasi Flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr(".flatpickr", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-M-Y",
                allowInput: true
            });
        }

        initDataTable();
    });

    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().destroy();
        }
        $('#example1').DataTable({
            autoWidth: false,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari barang / kode...",
                lengthMenu: "_MENU_ data per halaman",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                emptyTable: "Belum ada data stok barang",
                zeroRecords: "Tidak ada data yang cocok"
            }
        });
    }

    $(document).on('click', '.change_warehouse', function() {
        var btn = $(this);
        var originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Memuat...');

        var warehouse = $('.select_warehouse').val();
        var tgl_from = $('.tgl_from').val();
        var tgl_to = $('.tgl_to').val();

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'change_warehouse',
            data: {
                'warehouse': warehouse,
                'tgl_from': tgl_from,
                'tgl_to': tgl_to
            },
            cache: false,
            success: function(result) {
                btn.prop('disabled', false).html(originalText);
                $('#table_container').html(result);
                initDataTable();
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan saat memfilter data gudang.' });
                } else {
                    alert('Terjadi kesalahan saat memfilter data gudang.');
                }
            }
        });
    });

    $(document).on('click', '.check_costbook', function() {
        var id_material = $(this).data('id_material');
        var id_gudang = $(this).data('id_gudang');

        $('#ModalView').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat rincian transaksi costbook...</p></div>');
        
        // Show modal (support Bootstrap 5 and Bootstrap 3/4)
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modalElem = document.getElementById('dialog-popup');
            var modalInstance = bootstrap.Modal.getInstance(modalElem) || new bootstrap.Modal(modalElem);
            modalInstance.show();
        } else {
            $('#dialog-popup').modal('show');
        }

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'view_per_item',
            data: {
                'id_material': id_material,
                'id_gudang': id_gudang
            },
            cache: false,
            success: function(result) {
                $('#ModalView').html(result);
            },
            error: function() {
                $('#ModalView').html('<div class="alert alert-danger my-3"><i class="fa fa-circle-exclamation me-1"></i> Gagal memuat data transaksi. Silakan coba kembali.</div>');
            }
        });
    });

    // Cetak HANYA tabel daftar per item dengan validasi data kosong
    function printTableList() {
        var tableElem = $('#example1');
        var validRows = tableElem.find('tbody tr').filter(function() {
            return !$(this).find('.dataTables_empty').length && $(this).find('td').length > 1;
        });

        if (validRows.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mencetak!',
                    text: 'Tidak ada data di dalam tabel untuk dicetak.'
                });
            } else if (typeof swal === 'function') {
                swal({
                    title: 'Gagal Mencetak!',
                    text: 'Tidak ada data di dalam tabel untuk dicetak.',
                    type: 'error'
                });
            } else {
                alert('Gagal Mencetak: Tidak ada data di dalam tabel untuk dicetak.');
            }
            return false;
        }

        // Clone tabel dan buang kolom Aksi (Action)
        var tableClone = tableElem.clone();
        tableClone.find('tr').each(function() {
            $(this).find('th:last-child, td:last-child').remove();
        });

        var warehouseName = $('.select_warehouse option:selected').text();
        var tglFrom = $('.tgl_from').val();
        var tglTo = $('.tgl_to').val();
        var periodeText = (tglFrom && tglTo) ? (tglFrom + ' s/d ' + tglTo) : 'Semua Periode';

        var printWindow = window.open('', '', 'height=750,width=1050');
        printWindow.document.write('<!DOCTYPE html><html><head><title>Cetak Costbook Stok Per Item</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('@page { size: landscape; margin: 10mm; }');
        printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 10px; }');
        printWindow.document.write('.header-cetak { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #333; padding-bottom: 8px; }');
        printWindow.document.write('.header-cetak h3 { margin: 0 0 4px 0; font-size: 15px; text-transform: uppercase; }');
        printWindow.document.write('.header-cetak p { margin: 2px 0; font-size: 10px; color: #555; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 8px; }');
        printWindow.document.write('th, td { border: 1px solid #777; padding: 5px 6px; font-size: 10.5px; }');
        printWindow.document.write('th { background-color: #f2f2f2 !important; text-align: center; font-weight: bold; }');
        printWindow.document.write('.text-center { text-align: center; }');
        printWindow.document.write('.text-start { text-align: left; }');
        printWindow.document.write('.text-end { text-align: right; }');
        printWindow.document.write('.badge { border: 1px solid #999; padding: 1px 4px; font-size: 10px; border-radius: 2px; }');
        printWindow.document.write('</style></head><body>');

        printWindow.document.write('<div class="header-cetak">');
        printWindow.document.write('<h3>LAPORAN COSTBOOK STOK PER ITEM</h3>');
        printWindow.document.write('<p>Gudang: <b>' + warehouseName + '</b> | Periode: <b>' + periodeText + '</b> | Dicetak: ' + new Date().toLocaleString('id-ID') + '</p>');
        printWindow.document.write('</div>');

        printWindow.document.write(tableClone[0].outerHTML);
        printWindow.document.write('</body></html>');

        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    // Cetak HANYA tabel rincian transaksi modal dengan validasi data kosong
    function printModalContent() {
        var tableElem = $('#table_detail_trans');
        if (tableElem.length === 0) {
            showAlertEmpty();
            return false;
        }

        var validRows = tableElem.find('tbody tr').filter(function() {
            return !$(this).find('td[colspan]').length && $(this).find('td').length > 1;
        });

        if (validRows.length === 0) {
            showAlertEmpty();
            return false;
        }

        var tableHtml = tableElem[0].outerHTML;
        var infoElem = document.querySelector('#ModalView .card');
        var infoText = infoElem ? infoElem.innerText.replace(/\s+/g, ' ').trim() : '';

        var printWindow = window.open('', '', 'height=750,width=1000');
        printWindow.document.write('<!DOCTYPE html><html><head><title>Cetak Rincian Costbook</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('@page { size: landscape; margin: 10mm; }');
        printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 10px; }');
        printWindow.document.write('.header-cetak { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #333; padding-bottom: 6px; }');
        printWindow.document.write('.header-cetak h3 { margin: 0 0 4px 0; font-size: 15px; text-transform: uppercase; }');
        printWindow.document.write('.header-cetak p { margin: 2px 0; font-size: 10px; color: #555; }');
        printWindow.document.write('.info-header { background: #f8f9fa; border: 1px solid #ddd; padding: 6px 10px; margin-bottom: 8px; font-weight: bold; font-size: 11px; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 5px; }');
        printWindow.document.write('th, td { border: 1px solid #777; padding: 5px 6px; font-size: 10.5px; }');
        printWindow.document.write('th { background-color: #f2f2f2 !important; text-align: center; font-weight: bold; }');
        printWindow.document.write('.text-center { text-align: center; }');
        printWindow.document.write('.text-start { text-align: left; }');
        printWindow.document.write('.text-end { text-align: right; }');
        printWindow.document.write('.badge { border: 1px solid #999; padding: 1px 4px; font-size: 10px; border-radius: 2px; }');
        printWindow.document.write('</style></head><body>');

        printWindow.document.write('<div class="header-cetak">');
        printWindow.document.write('<h3>RINCIAN TRANSAKSI COSTBOOK BARANG</h3>');
        printWindow.document.write('<p>Tanggal Cetak: ' + new Date().toLocaleString('id-ID') + '</p>');
        printWindow.document.write('</div>');

        if (infoText) {
            printWindow.document.write('<div class="info-header">' + infoText + '</div>');
        }

        printWindow.document.write(tableHtml);
        printWindow.document.write('</body></html>');

        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    function showAlertEmpty() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mencetak!',
                text: 'Tidak ada data transaksi di dalam tabel untuk dicetak.'
            });
        } else if (typeof swal === 'function') {
            swal({
                title: 'Gagal Mencetak!',
                text: 'Tidak ada data transaksi di dalam tabel untuk dicetak.',
                type: 'error'
            });
        } else {
            alert('Gagal Mencetak: Tidak ada data transaksi di dalam tabel untuk dicetak.');
        }
    }
</script>
