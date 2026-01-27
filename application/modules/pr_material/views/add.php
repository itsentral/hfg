<div class="card shadow-sm border-0">
    <form id="data-form" enctype="multipart/form-data">
        <div class="card-header bg-white">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="tgl_butuh" class="form-label mb-1"><b>Tanggal Dibutuhkan</b></label>
                    <?php
                    $tgl_now = date('Y-m-d');
                    $tgl_next_month = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
                    echo form_input([
                        'id' => 'tgl_butuh',
                        'name' => 'tgl_butuh',
                        'class' => 'form-control text-center tgl changeSaveDate',
                        'readonly' => 'readonly',
                        'placeholder' => 'Tanggal Dibutuhkan'
                    ], $tgl_next_month);
                    ?>
                </div>

                <div class="col-md-2">
                    <label class="form-label mb-1"><b>Tingkat PR</b></label>
                    <select name="tingkat_pr" class="form-control tingkat_pr">
                        <option value="1">Normal</option>
                        <option value="2">Urgent</option>
                    </select>
                </div>

                <div class="col-md-7 text-md-end">
                    <button type="button" class="btn btn-primary btn-sm" id="autoPropose">
                        <i class="fa fa-magic me-1"></i> Set Auto Propose
                    </button>
                    <button type="button" class="btn btn-danger btn-sm ms-1" id="autoDelete">
                        <i class="fa fa-trash me-1"></i> Clear Purpose Request
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="example1" class="table table-striped table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" rowspan="2" style="width:60px;">#</th>
                            <th class="text-center" rowspan="2">Id Material</th>
                            <th class="text-center" rowspan="2" style="min-width: 200px;">Material</th>
                            <th class="text-center" rowspan="2">Category</th>
                            <th class="text-center" colspan="4">Stock Free</th>
                            <th class="text-center" rowspan="2">Min Stock</th>
                            <th class="text-center" rowspan="2">Max Stock</th>
                            <th class="text-center" rowspan="2">PR On Progress</th>
                            <th class="text-center" colspan="2">Propose Request</th>
                            <th class="text-center" rowspan="2">Packing Unit</th>
                            <th class="text-center" rowspan="2">Keterangan</th>
                        </tr>
                        <tr>
                            <th class="text-center">Qty Pack</th>
                            <th class="text-center">Pack Unit</th>
                            <th class="text-center">Convertion</th>
                            <th class="text-center">Weight (Kg)</th>
                            <th class="text-center" style="width:110px;">Qty</th>
                            <th class="text-center" style="width:120px;">Qty Packing</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="text-center mt-3">
                <button type="button" class="btn btn-success" id="saveRequest">
                    <i class="fa fa-cart-plus me-1"></i> Purchase Request
                </button>
                <button type="button" class="btn btn-dark" onclick="window.history.back(); return false;">
                    <i class="fa fa-reply"></i> Batal
                </button>
            </div>
        </div>
    </form>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/jquery-inputmask/jquery.inputmask.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('.tgl').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
        });

        moneyFormat('.moneyFormat');
        DataTables();

        $(document).on('change', '.changeSave', function() {
            var nomor = $(this).data('no');
            var id_material = $(this).data('id_material');
            var purchase = ($('#purchase_' + nomor).val() || '').split(",").join("");
            var keterangan = ($('#keterangan_' + nomor).val() || '').split(",").join("");
            var tanggal = $('#tgl_butuh').val();

            var HTML = $(this).parents('tr');
            var konversi = getNum((HTML.find('.konversi').text() || '').split(",").join(""));
            var propose_pack = 0;

            if (konversi > 0 && purchase > 0) {
                propose_pack = purchase / konversi;
            }

            HTML.find('.propose_packing').text(number_format(propose_pack, 2));

            $.ajax({
                url: base_url + active_controller + '/save_reorder_change',
                type: "POST",
                data: {
                    "id_material": id_material,
                    "purchase": purchase,
                    "keterangan": keterangan,
                    "tanggal": tanggal
                },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    swal({
                        title: "Save Success!",
                        text: data.pesan,
                        type: "success",
                        timer: 3000
                    });
                },
                error: function(xhr, status, error) {
                    swal({
                        title: "Save Failed!",
                        text: "Terjadi kesalahan koneksi ke server.",
                        type: "warning",
                        timer: 4000
                    });
                    console.log('error connection server !', error);
                }
            });
        });

        $(document).on('click', '#saveRequest', function() {
            var tingkat_pr = $('.tingkat_pr').val();

            swal({
                title: "Are you sure?",
                text: "Membuat semua Propose Material !!!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: false,
                closeOnCancel: false
            }, function(isConfirm) {
                if (!isConfirm) {
                    swal("Cancelled", "Data can be process again :)", "error");
                    return false;
                }

                $.ajax({
                    url: base_url + active_controller + '/save_reorder_all',
                    type: "POST",
                    data: {
                        'tingkat_pr': tingkat_pr
                    },
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
                                timer: 4000
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error Message !",
                            text: "An Error Occured During Process. Please try again..",
                            type: "warning",
                            timer: 4000
                        });
                    }
                });
            });
        });

        $(document).on('change', '.changeSaveDate', function() {
            var tanggal = $('#tgl_butuh').val();

            $.ajax({
                url: base_url + active_controller + '/save_reorder_change_date',
                type: "POST",
                data: {
                    "tanggal": tanggal
                },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    console.log(data.pesan);
                },
                error: function() {
                    console.log('error connection serve !');
                }
            });
        });

        $(document).on('click', '#autoPropose', function() {
            swal({
                title: "Are you sure?",
                text: "Set Auto Propose !!!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: false,
                closeOnCancel: false
            }, function(isConfirm) {
                if (!isConfirm) {
                    swal("Cancelled", "Data can be process again :)", "error");
                    return false;
                }

                $.ajax({
                    url: base_url + active_controller + '/set_update_propose_reorder',
                    type: "POST",
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
                            window.location.href = base_url + active_controller + 'add';
                        } else {
                            swal({
                                title: "Save Failed!",
                                text: data.pesan,
                                type: "warning",
                                timer: 4000
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error Message !",
                            text: "An Error Occured During Process. Please try again..",
                            type: "warning",
                            timer: 4000
                        });
                    }
                });
            });
        });

        $(document).on('click', '#autoDelete', function() {
            swal({
                title: "Are you sure?",
                text: "Clear All Propose Request !!!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: false,
                closeOnCancel: false
            }, function(isConfirm) {
                if (!isConfirm) {
                    swal("Cancelled", "Data can be process again :)", "error");
                    return false;
                }

                $.ajax({
                    url: base_url + active_controller + '/clear_update_reorder',
                    type: "POST",
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
                            window.location.href = base_url + active_controller + 'add';
                        } else {
                            swal({
                                title: "Save Failed!",
                                text: data.pesan,
                                type: "warning",
                                timer: 4000
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error Message !",
                            text: "An Error Occured During Process. Please try again..",
                            type: "warning",
                            timer: 4000
                        });
                    }
                });
            });
        });
    });

    function DataTables() {
        var dataTable = $('#example1').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            autoWidth: true,
            destroy: true,
            responsive: true,
            aaSorting: [
                [2, "asc"]
            ],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            pagingType: "simple_numbers",
            pageLength: 10,
            lengthMenu: [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            ajax: {
                url: base_url + active_controller + '/server_side_reorder_point',
                type: "POST",
                cache: false,
                error: function() {
                    // ✅ FIX: gunakan table yang benar, bukan my-grid
                    $('#example1 tbody').remove();
                    $('#example1').append("<tbody class='my-grid-error'><tr><td colspan='15' class='text-center'>No data found in the server</td></tr></tbody>");
                }
            },
            drawCallback: function() {
                moneyFormat('.moneyFormat');
            }
        });
    }

    function moneyFormat(e) {
        $(e).inputmask({
            alias: "decimal",
            digits: 2,
            radixPoint: ".",
            autoGroup: true,
            placeholder: "0",
            rightAlign: false,
            allowMinus: false,
            integerDigits: 13,
            groupSeparator: ",",
            digitsOptional: false,
            showMaskOnHover: true,
        });
    }

    function getNum(val) {
        val = (val || '').toString().replaceAll(',', '');
        const n = parseFloat(val);
        return isNaN(n) ? 0 : n;
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
        if (s[0].length > 3) s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>