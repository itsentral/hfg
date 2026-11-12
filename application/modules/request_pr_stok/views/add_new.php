<div class="row">
    <div class="col-12">
        <form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6 col-12">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                    <i class="fa fa-cart-plus fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="card-title fw-bold text-dark mb-0">Pengajuan Re-Order Point / PR Stok</h5>
                                    <small class="text-muted">Hitung otomatis usulan pembelian stok barang berdasarkan konsumsi rutin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12 text-md-end d-flex flex-wrap gap-2 justify-content-md-end">
                            <a href="<?= base_url('request_pr_stok') ?>" class="btn btn-secondary shadow-sm" id="back">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="button" class="btn btn-outline-danger shadow-sm" id="autoDelete">
                                <i class="fa fa-trash me-1"></i> Clear Propose
                            </button>
                            <button type="button" class="btn btn-outline-primary shadow-sm" id="autoUpdate">
                                <i class="fa fa-refresh me-1"></i> Update Otomatis
                            </button>
                            <button type="button" class="btn btn-success shadow-sm" id="saveRequest">
                                <i class="fa fa-paper-plane me-1"></i> Submit PR Stok
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Form Filter / Header Parameters -->
                    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="fa fa-tags text-primary me-1"></i> Kategori Barang
                            </label>
                            <select name="category" id="category" class="form-select select2">
                                <?php
                                foreach ($category as $valx) {
                                    $selected = ($valx['id'] == '8') ? 'selected' : '';
                                    echo "<option value='" . $valx['id'] . "' " . $selected . " data-nm_category='" . htmlspecialchars(strtoupper($valx['nm_category'])) . "'>" . strtoupper($valx['nm_category']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="fa fa-exclamation-circle text-primary me-1"></i> Tingkat Urgensi PR
                            </label>
                            <select name="tingkat_pr" class="form-select select2 tingkat_pr">
                                <option value="1">Normal</option>
                                <option value="2">Urgent</option>
                            </select>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="fa fa-calendar text-primary me-1"></i> Tanggal Dibutuhkan
                            </label>
                            <?php
                            $tgl_now = date('Y-m-d');
                            $tgl_next_month = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
                            ?>
                            <div class="input-group">
                                <span class="input-group-text bg-white"> 📅 </span>
                                <input type="text" id="tgl_butuh" name="tgl_butuh" class="form-control flatpickr-date changeSaveDate bg-white" readonly value="<?= $tgl_next_month ?>" placeholder="Pilih tanggal">
                            </div>
                        </div>
                    </div>

                    <!-- Summary Stat Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-12">
                            <div class="card border-0 bg-info bg-opacity-10 rounded-3 p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase">Nilai Budget Bulanan</div>
                                        <div class="input-group mt-1">
                                            <span class="input-group-text bg-white border-0 fw-bold text-info">Rp</span>
                                            <input type="text" class="form-control border-0 bg-white fw-bold text-end fs-5 nilai_budget" value="0" readonly>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-white bg-opacity-75 rounded-circle text-info">
                                        <i class="fas fa-money-bill-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="card border-0 bg-success bg-opacity-10 rounded-3 p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase">Total Nilai Pengajuan</div>
                                        <div class="input-group mt-1">
                                            <span class="input-group-text bg-white border-0 fw-bold text-success">Rp</span>
                                            <input type="text" class="form-control border-0 bg-white fw-bold text-end fs-5 nilai_pengajuan" value="0" readonly>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-white bg-opacity-75 rounded-circle text-success">
                                        <i class="fa fa-calculator fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-striped border-top mb-0" id="example1" width="100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="4%">#</th>
                                    <th class="text-start">Nama Barang</th>
                                    <th class="text-end" width="11%">Kebutuhan (1 Bln)</th>
                                    <th class="text-end" width="10%">Stock Saat Ini</th>
                                    <th class="text-end" width="10%">Max Stock</th>
                                    <th class="text-center" width="12%">Propose Purchase</th>
                                    <th class="text-center" width="9%">Satuan</th>
                                    <th class="text-start" width="16%">Keterangan</th>
                                    <th class="text-end" width="11%">Price Ref</th>
                                    <th class="text-end" width="12%">Total Price</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="9" class="text-end fw-bold py-3 fs-6">Total Nilai Pengajuan (Item):</th>
                                    <th class="text-end fw-bold py-3 fs-6 text-success total-price">0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="<?= base_url('request_pr_stok') ?>" class="btn btn-secondary shadow-sm">
                            <i class="fa fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-success shadow-sm px-4" id="saveRequestBottom">
                            <i class="fa fa-paper-plane me-1"></i> Submit PR Stok
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

<script>
    $(document).ready(function() {
        // Inisialisasi select2
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%'
            });
        }

        // Inisialisasi Flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr('.flatpickr-date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                defaultDate: '<?= $tgl_next_month ?>',
                onChange: function(selectedDates, dateStr, instance) {
                    $('#tgl_butuh').trigger('change');
                }
            });
        }

        var category = $("#category").val();
        DataTables(category);

        $(document).on('change', '#category', function() {
            var cat = $("#category").val();
            DataTables(cat);
            hitungBudget();
            hitungPengajuan();
        });

        hitungBudget();
        hitungPengajuan();

        $(document).on('click', '#saveRequestBottom', function() {
            $('#saveRequest').trigger('click');
        });
    });

    $(document).on('click', '#autoUpdate', function() {
        var inventory = $('#category').val();
        var nm_category = $('#category').find(':selected').data('nm_category');

        if (!inventory || inventory == '0') {
            swal({
                title: "Peringatan",
                text: 'Pilih kategori barang terlebih dahulu.',
                type: "warning"
            });
            return false;
        }

        swal({
            title: "Konfirmasi Update",
            text: "Lakukan update otomatis perhitungan untuk kategori " + nm_category + " ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-primary",
            confirmButtonText: "Ya, Proses",
            cancelButtonText: "Batal",
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: base_url + active_controller + '/auto_update_rutin/' + inventory,
                    type: "POST",
                    cache: false,
                    dataType: 'json',
                    success: function(data) {
                        if (data.status == 1) {
                            swal({
                                title: "Berhasil!",
                                text: data.pesan,
                                type: "success",
                                timer: 3000
                            });
                            DataTables(inventory);
                            hitungPengajuan();
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
                            text: 'Terjadi kesalahan sistem saat proses update.',
                            type: "error"
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '#autoDelete', function() {
        var id_category = $('#category').val();
        var nm_category = $('#category').find(':selected').data('nm_category');

        if (!id_category || id_category == '0') {
            swal({
                title: "Peringatan",
                text: 'Pilih kategori barang terlebih dahulu.',
                type: "warning"
            });
            return false;
        }

        swal({
            title: "Konfirmasi Clear",
            text: "Bersihkan usulan propose request untuk kategori " + nm_category + " ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya, Bersihkan",
            cancelButtonText: "Batal",
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: base_url + active_controller + '/clear_update_reorder/' + id_category,
                    type: "POST",
                    cache: false,
                    dataType: 'json',
                    success: function(data) {
                        if (data.status == 1) {
                            swal({
                                title: "Berhasil!",
                                text: data.pesan,
                                type: "success",
                                timer: 3000
                            });
                            DataTables(id_category);
                            hitungBudget();
                            hitungPengajuan();
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
                            text: 'Terjadi kesalahan sistem.',
                            type: "error"
                        });
                    }
                });
            }
        });
    });

    $(document).on('change', '.changeSave', function() {
        var id = $(this).data('id');
        var inventory = $('#category').val();
        var max_propose = $(this).data('max_propose');
        var nomor = $(this).data('no');
        var id_material = $(this).data('id');
        var purchase = $('#purchase_' + nomor).val().split(",").join("");

        if (parseFloat(purchase) > parseFloat(max_propose)) {
            swal({
                type: 'warning',
                title: 'Peringatan !',
                text: 'Nilai propose tidak boleh lebih dari Max Stock (' + max_propose + ') !'
            });
            purchase = max_propose;
            $('#purchase_' + nomor).val(max_propose);
        }

        var tanggal = $('#tgl_butuh').val();
        var satuan = $('#satuan_' + nomor).val();
        var info = $('#info_' + nomor).val();

        $.ajax({
            url: base_url + active_controller + '/save_reorder_change',
            type: "POST",
            data: {
                "id_material": id_material,
                "purchase": purchase,
                "tanggal": tanggal,
                "info": info,
                "satuan": satuan
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                DataTables(inventory);
                hitungPengajuan();
            },
            error: function() {
                console.log('Error saving reorder change');
            }
        });
    });

    function check_inputed_qty_stock() {
        var deferred = $.Deferred();
        $.ajax({
            url: siteurl + active_controller + '/check_inputed_qty_stock',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                deferred.resolve(response.jumlah_data);
            },
            error: function(error) {
                deferred.reject(error);
            }
        });
        return deferred.promise();
    }

    $(document).on('click', '#saveRequest', function() {
        var category = $('#category').val();
        var tingkat_pr = $('.tingkat_pr').val();

        var nilai_budget = $('.nilai_budget').val();
        nilai_budget = nilai_budget ? parseFloat(nilai_budget.split(',').join('')) : 0;

        var nilai_pengajuan = $('.nilai_pengajuan').val();
        nilai_pengajuan = nilai_pengajuan ? parseFloat(nilai_pengajuan.split(',').join('')) : 0;

        if (!category || category == '0') {
            swal({
                title: "Peringatan",
                text: 'Pilih kategori barang terlebih dahulu.',
                type: "warning"
            });
            return false;
        }

        check_inputed_qty_stock().then(function(data) {
            if (data > 0) {
                swal({
                    title: "Konfirmasi Pengajuan",
                    text: "Ajukan Purchase Request (PR) berdasarkan data kategori terpilih?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Ya, Ajukan",
                    cancelButtonText: "Batal",
                    closeOnConfirm: false
                }, function(isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            url: base_url + active_controller + '/save_reorder_all',
                            type: "POST",
                            data: {
                                'category': category,
                                'tingkat_pr': tingkat_pr,
                                'nilai_budget': nilai_budget,
                                'nilai_pengajuan': nilai_pengajuan
                            },
                            cache: false,
                            dataType: 'json',
                            success: function(res) {
                                if (res.status == 1) {
                                    swal({
                                        title: "Berhasil!",
                                        text: res.pesan,
                                        type: "success"
                                    }, function() {
                                        window.location.href = base_url + active_controller;
                                    });
                                } else {
                                    swal({
                                        title: "Gagal!",
                                        text: res.pesan,
                                        type: "warning"
                                    });
                                }
                            },
                            error: function() {
                                swal({
                                    title: "Error!",
                                    text: 'Terjadi kesalahan sistem saat menyimpan PR.',
                                    type: "error"
                                });
                            }
                        });
                    }
                });
            } else {
                swal({
                    title: 'Peringatan !',
                    text: 'Minimal harus ada 1 barang dengan kuantitas propose purchase > 0.',
                    type: 'warning'
                });
            }
        }).fail(function() {
            swal({
                title: 'Error !',
                text: 'Gagal mengecek kuantitas usulan stok.',
                type: 'error'
            });
        });
    });

    $(document).on('change', '.changeSaveDate', function() {
        var tanggal = $('#tgl_butuh').val();
        var id_category = $('#category').val();

        if (!id_category || id_category == '0') return false;

        $.ajax({
            url: base_url + active_controller + '/save_reorder_change_date',
            type: "POST",
            data: {
                "tanggal": tanggal,
                "id_category": id_category
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                console.log(data.pesan);
            }
        });
    });

    function hitungBudget() {
        var category = $('#category').val();
        $.ajax({
            type: 'post',
            url: siteurl + active_controller + '/hitung_budget',
            data: {
                'category': category
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                $('.nilai_budget').val(number_format(result.nilai_budget));
            }
        });
    }

    function hitungPengajuan() {
        var category = $('#category').val();
        $.ajax({
            type: 'post',
            url: siteurl + active_controller + '/hitung_pengajuan',
            data: {
                'category': category
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                $('.nilai_pengajuan').val(number_format(result.nilai_pengajuan));
            }
        });
    }

    function DataTables(category = null) {
        $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "responsive": true,
            "aaSorting": [
                [1, "asc"]
            ],
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false
            }],
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100],
                [10, 20, 50, 100]
            ],
            "ajax": {
                url: base_url + active_controller + '/server_side_reorder_point_new',
                type: "post",
                data: function(d) {
                    d.category = category;
                },
                cache: false,
                dataSrc: function(json) {
                    var total_price = json.total_price || 0;
                    $('#example1 tfoot .total-price').text(number_format(total_price));
                    return json.data;
                }
            },
            "language": {
                "search": "Cari Barang:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data barang",
                "zeroRecords": "Tidak ada barang yang cocok",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Lanjut",
                    "previous": "Sebelum"
                }
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
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>