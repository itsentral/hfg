<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<form id="data-form" method="post" autocomplete="off">
    <!-- Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row align-items-center g-3">
                <div class="col-md-7 col-12">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                            <i class="fa fa-boxes-packing fs-4"></i>
                        </div>
                        <div>
                            <h5 class="card-title fw-bold text-dark mb-0">Tambah Outgoing Stok ATK / Household</h5>
                            <small class="text-muted">Pilih departemen pemohon dan tambahkan barang dari katalog stok di bawah</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-12 text-md-end">
                    <a href="<?= base_url('outgoing_stok_atk') ?>" class="btn btn-sm btn-outline-secondary shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Informasi Permintaan Card -->
            <div class="card border shadow-none bg-light mb-4">
                <div class="card-header bg-white py-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fa fa-clipboard-list me-1"></i> 1. Informasi Permintaan
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold small text-muted">Departemen Pemohon <span class="text-danger">*</span></label>
                            <select name="id_dept" id="id_dept" class="form-select form-select-sm chosen-select w-100">
                                <option value="0">- Pilih Departemen -</option>
                                <?php if (!empty($listDepartment)) : ?>
                                    <?php foreach ($listDepartment as $key => $value) : ?>
                                        <option value="<?= $value['id'] ?>"><?= strtoupper($value['nama'] . ' - ' . $value['nm_comp']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold small text-muted">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="tanggal" id="tanggal" class="form-control form-control-sm flatpickr-date bg-white" placeholder="Pilih tanggal..." value="<?= date('Y-m-d') ?>" readonly>
                                <span class="input-group-text bg-white"><i class="fa fa-calendar-alt text-primary"></i></span>
                            </div>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold small text-muted">PIC Request <span class="text-danger">*</span></label>
                            <input type="text" name="pic" id="pic" class="form-control form-control-sm" placeholder="Nama PIC Pemohon">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Keterangan / Catatan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control form-control-sm" rows="2" placeholder="Tuliskan catatan keperluan permintaan barang..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Barang yang Diminta Card -->
            <div class="card border shadow-none bg-light mb-4">
                <div class="card-header bg-white py-2 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-success">
                        <i class="fa fa-cart-arrow-down me-1"></i> 2. Daftar Barang yang Akan Dikeluarkan
                    </h6>
                    <button type="button" class="btn btn-sm btn-success px-4 shadow-sm fw-bold" name="save" id="save">
                        <i class="fa fa-paper-plane me-1"></i> Proses Outgoing
                    </button>
                </div>
                <div class="card-body bg-white p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover align-middle mb-0" id="table-request-items">
                            <thead class="table-light text-nowrap text-center">
                                <tr>
                                    <th width="40">#</th>
                                    <th width="120">Kode</th>
                                    <th>Nama Barang Stok</th>
                                    <th width="150">Kategori</th>
                                    <th width="100">Stok Tersedia</th>
                                    <th width="80">Unit</th>
                                    <th width="130">Qty Request <span class="text-danger">*</span></th>
                                    <th>Keterangan</th>
                                    <th width="60">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="body_req">
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fa fa-info-circle me-1"></i> Belum ada barang yang dipilih. Silakan klik tombol <b>Pindahkan</b> pada katalog barang stok di bawah.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Katalog Barang Stok Card -->
            <div class="card border shadow-none bg-light">
                <div class="card-header bg-white py-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="fa fa-table-list me-1"></i> 3. Katalog Stok Barang ATK / Household
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="table-responsive">
                        <table id="my-grid2" class="table table-sm table-striped table-hover align-middle border mb-0" style="width: 100%;">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th class="text-center" width="40">#</th>
                                    <th width="120">Kode</th>
                                    <th>Nama Barang Stok</th>
                                    <th width="140">Kategori</th>
                                    <th class="text-center no-sort" width="80">Stok</th>
                                    <th class="text-center no-sort" width="70">Unit</th>
                                    <th class="text-center no-sort" width="120">Qty Request</th>
                                    <th class="text-center no-sort" width="180">Keterangan</th>
                                    <th class="text-center no-sort" width="70">Pilih</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        if ($('.chosen-select').length) {
            $('.chosen-select').select2({ width: '100%' });
        }

        if (typeof flatpickr !== 'undefined') {
            flatpickr('#tanggal', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd F Y',
                locale: 'id',
                defaultDate: '<?= date("Y-m-d"); ?>',
                allowInput: true
            });
        }

        DataTables2();

        // Fallback filter numeric jika plugin autoNumeric belum siap
        $(document).on('input', '.autoNumeric, .autoNumeric4, .sudah_request', function() {
            if (typeof $.fn.autoNumeric !== 'function') {
                this.value = this.value.replace(/[^0-9.]/g, '');
            }
        });


        let arrayRequest = [];
        let arrayDataCheck = [];

        $(document).on('click', '.pindahkan', function() {
            let tr = $(this).closest('tr');
            let id = tr.find('.id').val();
            let code_material = tr.find('.code_material').val();
            let nm_material = tr.find('.nm_material').val();
            let packing = tr.find('.packing').val();
            let satuan = tr.find('.satuan').val();
            let konversi = tr.find('.konversi').val();
            let nm_category = tr.find('.nm_category').val();
            let sudah_req_val = tr.find('.sudah_request').val();
            let sudah_request = sudah_req_val ? sudah_req_val.toString().replace(/,/g, '') : "0";
            let ket_request = tr.find('.ket_request').val() || '';
            let stock_qty = tr.find('.stock_qty').val() || '0';

            let reqNum = parseFloat(sudah_request) || 0;
            if (reqNum <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Perhatian!', text: 'Silakan isi Qty Request terlebih dahulu pada baris barang tersebut!' });
                } else {
                    alert('Silakan isi Qty Request terlebih dahulu!');
                }
                tr.find('.sudah_request').focus();
                return false;
            }

            let check = arrayDataCheck.includes(id);
            if (check === false) {
                let dataArr = {
                    'id': id,
                    'code_material': code_material,
                    'nm_material': nm_material,
                    'packing': packing,
                    'satuan': satuan,
                    'konversi': konversi,
                    'nm_category': nm_category,
                    'qty_unit': Number(konversi) * Number(sudah_request),
                    'sudah_request': sudah_request,
                    'ket_request': ket_request,
                    'stock_qty': stock_qty
                };
                arrayRequest.push(dataArr);
                arrayDataCheck.push(id);
                viewRequest();

                // reset input row
                tr.find('.sudah_request').val('');
                tr.find('.ket_request').val('');
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'info', title: 'Info', text: 'Barang stok ini sudah ada dalam daftar permintaan!' });
                } else {
                    alert('Barang stok sudah ada dalam daftar!');
                }
            }
        });

        $(document).on('click', '.hapus_req', function() {
            let id = $(this).data('id');
            delete arrayRequest[id];
            delete arrayDataCheck[id];
            viewRequest();
        });

        const viewRequest = () => {
            let DataAppend = "";
            let nomor = 0;
            let countItem = 0;

            arrayRequest.forEach((row, idx) => {
                if (row !== undefined) {
                    nomor++;
                    countItem++;
                    DataAppend += "<tr>";
                    DataAppend += "<td class='text-center'>" + nomor + "</td>";
                    DataAppend += "<td class='fw-semibold text-primary'>" + row.code_material + "</td>";
                    DataAppend += "<td class='fw-bold'>" + row.nm_material + "</td>";
                    DataAppend += "<td><span class='badge bg-light text-dark border'>" + row.nm_category + "</span></td>";
                    DataAppend += "<td class='text-center fw-bold'>" + number_format(row.stock_qty) + "</td>";
                    DataAppend += "<td class='text-center'>" + row.satuan + "</td>";
                    DataAppend += "<td>";
                    DataAppend += "<input type='hidden' name='detail[" + idx + "][id]' value='" + row.id + "'>";
                    DataAppend += "<input type='text' name='detail[" + idx + "][sudah_request]' class='form-control form-control-sm text-center autoNumeric qtyRequest' value='" + row.sudah_request + "'>";
                    DataAppend += "</td>";
                    DataAppend += "<td>";
                    DataAppend += "<input type='text' name='detail[" + idx + "][ket_request]' class='form-control form-control-sm' value='" + row.ket_request + "' placeholder='Catatan...'>";
                    DataAppend += "</td>";
                    DataAppend += "<td class='text-center'><button type='button' class='btn btn-danger btn-sm hapus_req' data-id='" + idx + "' title='Hapus'><i class='fa fa-trash'></i></button></td>";
                    DataAppend += "</tr>";
                }
            });

            if (countItem === 0) {
                DataAppend = '<tr><td colspan="9" class="text-center text-muted py-4"><i class="fa fa-info-circle me-1"></i> Belum ada barang yang dipilih. Silakan klik tombol <b>Pindahkan</b> pada katalog barang stok di bawah.</td></tr>';
            }

            $('#body_req').html(DataAppend);
            if (typeof $.fn.autoNumeric === 'function') {
                $('.autoNumeric').autoNumeric('init', {
                    mDec: '4',
                    aPad: false
                });
            }
        };

        $('#save').click(function(e) {
            e.preventDefault();

            var id_dept = $('#id_dept').val();
            var pic = $('#pic').val();

            if (id_dept == '0' || id_dept == '' || id_dept == null) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Departemen pemohon belum dipilih!' });
                } else {
                    alert('Departemen pemohon belum dipilih!');
                }
                return false;
            }

            if (!pic || pic.trim() === '') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Nama PIC Request wajib diisi!' });
                } else {
                    alert('Nama PIC Request wajib diisi!');
                }
                $('#pic').focus();
                return false;
            }

            var hasDetail = false;
            $('.qtyRequest').each(function() {
                var val = $(this).val();
                if (val && parseFloat(val.toString().replace(/,/g, '')) > 0) {
                    hasDetail = true;
                }
            });

            if (!hasDetail) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Minimal satu barang stok harus dimasukkan dengan Qty Request > 0!' });
                } else {
                    alert('Minimal satu barang stok harus dimasukkan!');
                }
                return false;
            }

            var doSubmit = function() {
                var formData = new FormData($('#data-form')[0]);
                var baseurl = siteurl + active_controller + 'request';
                var btn = $('#save');
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Memproses...');

                $.ajax({
                    url: baseurl,
                    type: "POST",
                    data: formData,
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Proses Outgoing');
                        if (data.status == 1) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.pesan, timer: 3000 }).then(() => {
                                    window.location.href = siteurl + active_controller;
                                });
                            } else {
                                alert(data.pesan);
                                window.location.href = siteurl + active_controller;
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'warning', title: 'Gagal!', text: data.pesan });
                            } else {
                                alert(data.pesan);
                            }
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Proses Outgoing');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan sistem saat memproses transaksi.' });
                        } else {
                            alert('Terjadi kesalahan sistem saat memproses transaksi.');
                        }
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: "Konfirmasi Outgoing",
                    text: "Apakah Anda yakin data pengeluaran stok ini sudah benar?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Ya, Proses!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        doSubmit();
                    }
                });
            } else {
                if (confirm("Apakah Anda yakin ingin memproses data outgoing ini?")) {
                    doSubmit();
                }
            }
        });
    });

    function DataTables2() {
        if ($.fn.DataTable.isDataTable('#my-grid2')) {
            $('#my-grid2').DataTable().destroy();
        }

        $('#my-grid2').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            autoWidth: false,
            responsive: true,
            order: [[1, "asc"]],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            drawCallback: function(settings) {
                if (typeof $.fn.autoNumeric === 'function') {
                    $('.autoNumeric4').autoNumeric('init', {
                        mDec: '4',
                        aPad: false
                    });
                }
            },
            ajax: {
                url: siteurl + active_controller + 'data_side_request_material',
                type: "POST",
                cache: false,
                error: function() {
                    $("#my-grid2 tbody").html('<tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data stok ditemukan</td></tr>');
                }
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari nama barang stok / kode...",
                lengthMenu: "_MENU_ data per halaman",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                emptyTable: "Katalog stok tidak tersedia",
                zeroRecords: "Tidak ada data yang cocok",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat katalog stok...'
            }
        });
    }

    function number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>
