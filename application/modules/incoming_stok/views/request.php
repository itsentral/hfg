<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .card {
        border-radius: 0.75rem;
    }
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
</style>

<form id="data-form" method="post" autocomplete="off">
    <!-- Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row align-items-center g-3">
                <div class="col-md-7 col-12">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                            <i class="fa fa-dolly fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title fw-bold text-dark mb-0">Tambah Incoming Barang Stok</h5>
                            <small class="text-muted">Pilih supplier / kasbon, centang nomor PO yang akan diterima, dan sesuaikan kuantitas barang</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-12 text-md-end">
                    <a href="<?= base_url('incoming_stok') ?>" class="btn btn-outline-secondary shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Kolom Kiri: Informasi Dokumen & Pemilihan Sumber -->
                <div class="col-lg-6 col-12">
                    <div class="card border bg-light bg-opacity-50 h-100">
                        <div class="card-header bg-white py-2 border-bottom">
                            <span class="fw-bold text-dark"><i class="fa fa-truck-ramp-box text-primary me-2"></i>Sumber & Nomor Referensi</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">
                                    Pilih Sumber / Supplier <span class="text-danger">*</span>
                                </label>
                                <select class="form-select chosen-select pilih_supplier" name="pilih_supplier" id="pilih_supplier" required>
                                    <option value="">-- Pilih Supplier / Kasbon --</option>
                                    <option value="kasbon">KASBON</option>
                                    <?php if (!empty($listSupplier)) : ?>
                                        <?php foreach ($listSupplier as $item) : ?>
                                            <option value="<?= $item->kode_supplier; ?>">
                                                <?= strtoupper($item->nama); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <label class="form-label fw-bold text-dark">
                                Daftar Nomor PO / Kasbon <span class="text-danger">*</span>
                            </label>
                            <div class="table-responsive border rounded bg-white" style="max-height: 250px; overflow-y: auto;">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="text-center" width="45%">No Surat PO</th>
                                            <th class="text-center" width="40%">No PR</th>
                                            <th class="text-center" width="15%">Pilih</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list_no_po">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">
                                                <i class="fa fa-info-circle me-1"></i> Silakan pilih supplier / kasbon terlebih dahulu
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Pengaturan Penerimaan -->
                <div class="col-lg-6 col-12">
                    <div class="card border bg-light bg-opacity-50 h-100">
                        <div class="card-header bg-white py-2 border-bottom">
                            <span class="fw-bold text-dark"><i class="fa fa-warehouse text-primary me-2"></i>Parameter Penerimaan Gudang</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">
                                    Tanggal Penerimaan <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="tanggal" id="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required placeholder="Pilih tanggal">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">
                                    Warehouse Tujuan <span class="text-danger">*</span>
                                </label>
                                <select name="id_gudang" id="id_gudang" class="form-select chosen-select" required>
                                    <option value="0">-- Pilih Gudang Tujuan --</option>
                                    <?php if (!empty($listGudang)) : ?>
                                        <?php foreach ($listGudang as $val) : ?>
                                            <option value="<?= $val['id']; ?>">
                                                <?= strtoupper($val['nm_gudang']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">
                                    PIC Penerima <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa fa-user"></i></span>
                                    <input type="text" name="pic" id="pic" class="form-control" placeholder="Nama staf gudang / penerima barang..." required>
                                </div>
                            </div>

                            <div>
                                <label class="form-label fw-bold text-dark">Catatan / Keterangan</label>
                                <textarea name="keterangan" id="keterangan" class="form-control" rows="2" placeholder="Catatan opsional penerimaan..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Rincian Barang yang Diterima -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-boxes-stacked text-primary fs-5"></i>
                    <h6 class="card-title fw-bold text-dark mb-0">Rincian Barang yang Diterima</h6>
                </div>
                <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Input kuantitas barang yang diterima (maksimal sisa PO)</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="4%">#</th>
                            <th class="text-center" width="10%">ID Material</th>
                            <th class="text-start" width="14%">Kode Stok</th>
                            <th class="text-start">Nama Barang</th>
                            <th class="text-center" width="9%">Qty PO</th>
                            <th class="text-center" width="8%">Satuan</th>
                            <th class="text-center" width="9%">Sudah Masuk</th>
                            <th class="text-center" width="9%">Sisa Belum Masuk</th>
                            <th class="text-center" width="13%">Qty Masuk</th>
                            <th class="text-start" width="15%">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="body_req">
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fa fa-info-circle me-1"></i> Belum ada data barang. Silakan centang nomor PO dan pilih gudang tujuan di atas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Card Jurnal Incoming -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fa fa-book-bookmark text-primary fs-5"></i>
                <h6 class="card-title fw-bold text-dark mb-0">Estimasi Jurnal Otomatis Incoming</h6>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="12%">Tanggal Jurnal</th>
                            <th class="text-center" width="10%">Company</th>
                            <th class="text-center" width="10%">Divisi</th>
                            <th class="text-center" width="10%">No COA</th>
                            <th class="text-start" width="15%">Nama Company</th>
                            <th class="text-start" width="15%">Nama Account</th>
                            <th class="text-start">Deskripsi</th>
                            <th class="text-end" width="11%">Debit</th>
                            <th class="text-end" width="11%">Kredit</th>
                        </tr>
                    </thead>
                    <tbody class="tbody_list_jurnal">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">
                                <i>Jurnal akan terbentuk otomatis setelah nomor PO dipilih</i>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold border-top">
                        <tr>
                            <th class="text-end" colspan="7">TOTAL BALANCING :</th>
                            <th class="text-end text-primary ttl_debit_jurnal">0</th>
                            <th class="text-end text-success ttl_kredit_jurnal">0</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3 text-end">
            <button type="button" class="btn btn-primary px-4 shadow-sm" id="save">
                <i class="fa fa-paper-plane me-1"></i> Proses Incoming
            </button>
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

        // Inisialisasi safe autoNumeric
        const initAutoNumeric = () => {
            if (typeof $.fn.autoNumeric === 'function') {
                $('.autoNumeric4').autoNumeric('init', {
                    mDec: '4',
                    aPad: false
                });
            }
        };
        initAutoNumeric();

        // Fallback filter numeric jika plugin autoNumeric belum siap
        $(document).on('input', '.autoNumeric4, .qty_in', function() {
            if (typeof $.fn.autoNumeric !== 'function') {
                this.value = this.value.replace(/[^0-9.]/g, '');
            }
        });

        $(document).on('keyup', '.qty_in', function() {
            let qty_in_val = $(this).val() ? $(this).val().toString().replace(/,/g, '') : "0";
            let qty_in = parseFloat(qty_in_val) || 0;

            let qty_max_val = $(this).closest('tr').find('.qty_max').text().replace(/,/g, '');
            let qty_max = parseFloat(qty_max_val) || 0;

            if (qty_in > qty_max) {
                $(this).val(qty_max);
            }
        });

        // Trigger pemilihan supplier
        $(document).on('change', '.pilih_supplier', function() {
            var supplier = $(this).val();
            if (!supplier) {
                $('.list_no_po').html('<tr><td colspan="3" class="text-center text-muted py-3">Silakan pilih supplier / kasbon</td></tr>');
                $('#body_req').html('<tr><td colspan="10" class="text-center text-muted py-4">Belum ada data barang.</td></tr>');
                return;
            }

            $('.list_no_po').html('<tr><td colspan="3" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat daftar PO...</td></tr>');

            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'pilih_supplier',
                data: {
                    'kode_supplier': supplier
                },
                cache: false,
                success: function(result) {
                    if (result.trim() === '') {
                        $('.list_no_po').html('<tr><td colspan="3" class="text-center text-muted py-3">Tidak ada PO yang aktif atau belum incoming</td></tr>');
                    } else {
                        $('.list_no_po').html(result);
                    }
                },
                error: function() {
                    $('.list_no_po').html('<tr><td colspan="3" class="text-center text-danger py-3">Gagal memuat daftar PO dari server</td></tr>');
                }
            });
        });

        // Trigger centang PO dan pemilihan Gudang
        $(document).on('change', '.check_po, #id_gudang', function() {
            var no_po = [];
            $('.check_po:checked').each(function() {
                var val = $(this).val();
                if (val) {
                    no_po.push(val);
                }
            });
            let id_gudang = $('#id_gudang').val();

            if (no_po.length > 0 && id_gudang && id_gudang !== '0') {
                $('#body_req').html('<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Mengambil rincian barang dari PO...</td></tr>');

                $.ajax({
                    type: 'POST',
                    url: siteurl + active_controller + 'detail_purchasing_order',
                    data: {
                        'no_po': no_po,
                        'id_gudang': id_gudang,
                    },
                    dataType: 'json',
                    success: function(data) {
                        $('#body_req').html(data.header);
                        initAutoNumeric();
                    },
                    error: function() {
                        $('#body_req').html('<tr><td colspan="10" class="text-center text-danger py-4">Gagal memuat rincian barang dari server</td></tr>');
                    }
                });

                set_jurnal(no_po, id_gudang);
            } else if (no_po.length === 0) {
                $('#body_req').html('<tr><td colspan="10" class="text-center text-muted py-4"><i class="fa fa-info-circle me-1"></i> Silakan centang minimal satu nomor PO di atas.</td></tr>');
                $('.tbody_list_jurnal').html('<tr><td colspan="9" class="text-center text-muted py-3"><i>Jurnal akan terbentuk otomatis setelah nomor PO dipilih</i></td></tr>');
                $('.ttl_debit_jurnal').text('0');
                $('.ttl_kredit_jurnal').text('0');
            }
        });

        // Submit form handler
        $('#save').click(function(e) {
            e.preventDefault();

            var no_po = [];
            $('.check_po:checked').each(function() {
                var val = $(this).val();
                if (val) {
                    no_po.push(val);
                }
            });

            var id_gudang = $('#id_gudang').val();
            var pic = $('#pic').val();

            if (no_po.length < 1) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Nomor PO / Kasbon belum dipilih!' });
                } else {
                    alert('Nomor PO / Kasbon belum dipilih!');
                }
                return false;
            }

            if (!id_gudang || id_gudang == '0') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Warehouse tujuan belum dipilih!' });
                } else {
                    alert('Warehouse tujuan belum dipilih!');
                }
                $('#id_gudang').focus();
                return false;
            }

            if (!pic || pic.trim() === '') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Nama PIC penerima barang wajib diisi!' });
                } else {
                    alert('Nama PIC penerima barang wajib diisi!');
                }
                $('#pic').focus();
                return false;
            }

            var hasValidQty = false;
            $('.qty_in').each(function() {
                var val = $(this).val() ? parseFloat($(this).val().toString().replace(/,/g, '')) : 0;
                if (val > 0) {
                    hasValidQty = true;
                }
            });

            if (!hasValidQty) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Minimal satu barang harus memiliki Qty Masuk > 0!' });
                } else {
                    alert('Minimal satu barang harus memiliki Qty Masuk > 0!');
                }
                return false;
            }

            var doSubmit = function() {
                var formData = new FormData($('#data-form')[0]);
                var baseurl = siteurl + active_controller + 'request_stok';
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
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Proses Incoming');
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
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Proses Incoming');
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
                    title: "Konfirmasi Incoming",
                    text: "Apakah Anda yakin data penerimaan stok ini sudah benar?",
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
                if (confirm("Apakah Anda yakin ingin memproses transaksi incoming ini?")) {
                    doSubmit();
                }
            }
        });
    });

    function set_jurnal(no_po, id_gudang) {
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'set_jurnal',
            data: {
                'no_po': no_po,
                'id_gudang': id_gudang
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                if (result.hasil_jurnal && result.hasil_jurnal.trim() !== '') {
                    $('.tbody_list_jurnal').html(result.hasil_jurnal);
                    $('.ttl_debit_jurnal').text(number_format(result.ttl_debit));
                    $('.ttl_kredit_jurnal').text(number_format(result.ttl_kredit));
                } else {
                    $('.tbody_list_jurnal').html('<tr><td colspan="9" class="text-center text-muted py-3"><i>Tidak ada jurnal yang di-generate</i></td></tr>');
                    $('.ttl_debit_jurnal').text('0');
                    $('.ttl_kredit_jurnal').text('0');
                }
            },
            error: function() {
                $('.tbody_list_jurnal').html('<tr><td colspan="9" class="text-center text-muted py-3"><i>Informasi jurnal tidak dapat dimuat</i></td></tr>');
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
