<style>
    #table-coil th {
        vertical-align: middle !important;
        font-size: 12px;
    }

    #table-coil td {
        font-size: 12px;
        vertical-align: middle !important;
    }

    input.hitung-selisih {
        font-weight: bold;
        background-color: #fff9c4;
        /* Warna kuning muda untuk area input */
    }
</style>
<div class="card">
    <div class="card-body">
        <form action="" id="data-form">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row mb-3">
                            <div class="col-md-4"><label>Supplier</label></div>
                            <div class="col-md-8">
                                <select id="id_supplier" name="id_supplier" class="form-control select2">
                                    <option value="">Pilih Supplier</option>
                                    <?php foreach ($list_supplier as $sup): ?>
                                        <option value="<?= $sup->kode_supplier ?>"><?= $sup->nama ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <div class="col-md-4"><label>No. PO</label></div>
                            <div class="col-md-8">
                                <select id="no_po" name="no_po" class="form-control select2" disabled>
                                    <option value="">Pilih PO</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row mb-3">
                            <div class="col-md-4"><label>Tgl. Incoming</label></div>
                            <div class="col-md-8">
                                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Upload Document</label>
                            </div>

                            <div class="col-md-8">
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <input type="file" name="file_incoming_material[]" id="file_incoming_material" class="d-none" accept=".pdf,.jpg,.jpeg,.png" multiple>

                                        <button type="button" class="btn btn-outline-warning" id="btnPickFile">
                                            <i class="ti ti-upload me-1"></i> Choose File
                                        </button>

                                        <span class="text-muted" id="docFileName">No file chosen</span>

                                        <button type="button" class="btn btn-light border" id="btnClearFile" style="display:none;">
                                            <i class="ti ti-x me-1"></i> Clear
                                        </button>
                                    </div>

                                    <small class="text-muted">
                                        Allowed: PDF/JPG/PNG. Max size 2MB.
                                    </small>

                                    <!-- <?php if (!empty($file_msds)) : ?>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-light text-dark border">
                                                <i class="ti ti-file-description me-1"></i> Existing MSDS
                                            </span>
                                            <a href="<?= base_url() . $file_msds; ?>" target="_blank" class="btn btn-sm btn-success">
                                                <i class="ti ti-download me-1"></i> Download
                                            </a>
                                            <a href="<?= base_url() . $file_msds; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="ti ti-eye me-1"></i> Preview
                                            </a>
                                        </div>
                                    <?php endif; ?> -->
                                </div>
                            </div>

                            <script>
                                (function() {
                                    const input = document.getElementById('file_incoming_material');
                                    const btnPick = document.getElementById('btnPickFile');
                                    const btnClear = document.getElementById('btnClearFile');
                                    const fileName = document.getElementById('docFileName');

                                    if (!input || !btnPick || !fileName) return;

                                    btnPick.addEventListener('click', function() {
                                        input.click();
                                    });

                                    input.addEventListener('change', function() {
                                        const name = (input.files && input.files.length) ? input.files[0].name : 'No file chosen';
                                        fileName.textContent = name;

                                        if (input.files && input.files.length) {
                                            btnClear.style.display = 'inline-flex';
                                        } else {
                                            btnClear.style.display = 'none';
                                        }
                                    });

                                    if (btnClear) {
                                        btnClear.addEventListener('click', function() {
                                            input.value = '';
                                            fileName.textContent = 'No file chosen';
                                            btnClear.style.display = 'none';
                                        });
                                    }
                                })();
                            </script>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-coil">
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center" style="vertical-align: middle;" width="3%">No</th>
                                <th rowspan="2" class="text-center" style="vertical-align: middle;" width="15%">Material</th>
                                <th rowspan="2" class="text-center" style="vertical-align: middle;" width="8%">Qty Order</th>
                                <th rowspan="2" class="text-center" style="vertical-align: middle;" width="5%">Uom</th>
                                <th rowspan="2" class="text-center" style="vertical-align: middle;" width="8%">Qty Belum Kirim</th>
                                <th colspan="3" class="text-center" style="background-color: #d2d6de !important; color: #000;">Dari Data ROS (Packing List)</th>
                                <th colspan="2" class="text-center" style="background-color: #f3b44e !important;">Checklist Visual</th>
                                <th colspan="2" class="text-center" style="background-color: #69c79d !important;">Penimbangan</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="background-color: #d2d6de !important; color: #000;">No. Coil</th>
                                <th class="text-center" style="background-color: #d2d6de !important; color: #000;">Berat Kotor</th>
                                <th class="text-center" style="background-color: #d2d6de !important; color: #000;">Berat Bersih</th>
                                <th class="text-center" style="background-color: #f3b44e !important;">OK</th>
                                <th class="text-center" style="background-color: #f3b44e !important;">Reject</th>
                                <th class="text-center" style="background-color: #69c79d !important;">Aktual Berat Kotor</th>
                                <th class="text-center" style="background-color: #69c79d !important;">Selisih</th>
                            </tr>
                        </thead>
                        <tbody id="list-item-coil">
                            <tr>
                                <td colspan="12" class="text-center">Pilih Supplier dan Nomor PO untuk menampilkan data coil.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-center">
                    <button type="button" class="btn btn-success" id="save-incoming"><i class="fas fa-sign-in-alt"></i> Proses Incoming</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('.select2').select2();

        // 1. Saat Supplier Dipilih -> Ambil PO
        $(document).on('change', '#id_supplier', function() {
            let id_supplier = $(this).val();
            if (id_supplier) {
                $.ajax({
                    url: siteurl + active_controller + 'get_po_by_supplier',
                    type: 'POST',
                    data: {
                        id_supplier: id_supplier
                    },
                    dataType: 'json',
                    success: function(data) {
                        let html = '<option value="">Pilih PO</option>';
                        data.forEach(item => {
                            html += `<option value="${item.no_po}">${item.no_po} (${item.no_surat})</option>`;
                        });
                        $('#no_po').html(html).prop('disabled', false);
                    }
                });
            }
        });

        // 2. Saat PO Dipilih -> Ambil Data ROS/Coil
        $(document).on('change', '#no_po', function() {
            let no_po = $(this).val();
            if (no_po) {
                $.ajax({
                    url: siteurl + active_controller + 'get_ros_by_po',
                    type: 'POST',
                    data: {
                        no_po: no_po
                    },
                    dataType: 'json',
                    success: function(data) {
                        let html = '';
                        let currentMaterial = '';

                        if (data.length > 0) {
                            data.forEach((item, index) => {
                                let rowMaterial = '';

                                // Logika Grouping: Jika material sama, kolom kiri dikosongkan (seperti merge cell)
                                if (item.id_material !== currentMaterial) {
                                    let qty_belum_kirim = item.qty_po - item.qty_in;
                                    rowMaterial = `
                                <td class="text-center">${index + 1}</td>
                                <td><b>${item.nm_material}</b></td>
                                <td class="text-right">${Number(item.qty_po).toLocaleString()}</td>
                                <td class="text-center">Kg</td>
                                <td class="text-right">${Number(qty_belum_kirim).toLocaleString()}</td>
                            `;
                                    currentMaterial = item.id_material;
                                } else {
                                    // Baris kosong untuk kolom material jika coil masih dalam material yang sama
                                    rowMaterial = `<td colspan="5" style="border-top:none;"></td>`;
                                }

                                html += `
                                    <tr>
                                        ${rowMaterial}
                                        <td class="bg-gray text-center">${item.no_coil}</td>
                                        <td class="bg-gray text-right">${item.ros_kotor}</td>
                                        <td class="bg-gray text-right">${item.ros_bersih}</td>
                                        <td class="text-center">
                                            <input type="radio" name="detail[${index}][status_qc]" value="OK" checked>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio" name="detail[${index}][status_qc]" value="REJECT">
                                        </td>
                                        <td>
                                            <input type="number" name="detail[${index}][aktual_kotor]" 
                                                class="form-control input-sm hitung-selisih" 
                                                data-ros="${item.ros_kotor}" step="0.01">
                                            <input type="hidden" name="detail[${index}][id_ros_detail]" value="${item.id_ros_detail}">
                                            <input type="hidden" name="detail[${index}][id_po_detail]" value="${item.id_po_detail}">
                                            <input type="hidden" name="detail[${index}][id_material]" value="${item.id_material}">
                                            <input type="hidden" name="detail[${index}][nm_material]" value="${item.nm_material}">
                                            <input type="hidden" name="detail[${index}][no_coil]" value="${item.no_coil}">
                                            <input type="hidden" name="detail[${index}][no_ros]" value="${item.no_ros}">
                                        </td>
                                        <td class="bg-yellow">
                                            <input type="text" name="detail[${index}][selisih]" 
                                                class="form-control input-sm text-selisih" readonly>
                                        </td>
                                    </tr>`;
                            });
                        } else {
                            html = '<tr><td colspan="12" class="text-center">Data Coil tidak ditemukan</td></tr>';
                        }
                        $('#list-item-coil').html(html);
                    }
                });
            }
        });

        // 3. Hitung Selisih Real-time
        $(document).on('input', '.hitung-selisih', function() {
            let aktual = parseFloat($(this).val()) || 0;
            let ros = parseFloat($(this).data('ros')) || 0;
            let selisih = aktual - ros;

            $(this).closest('tr').find('.text-selisih').val(selisih.toFixed(2));
        });

        $(document).on('click', '#save-incoming', function(e) {
            e.preventDefault();

            let formData = new FormData($('#data-form')[0]);
            let no_po = $('#no_po').val();

            if (no_po == "") {
                swal("Peringatan", "Pilih Nomor PO terlebih dahulu!", "warning");
                return false;
            }

            // Validasi sederhana: pastikan ada berat aktual yang diisi
            let adaIsi = false;
            $('.hitung-selisih').each(function() {
                if ($(this).val() !== "" && $(this).val() !== "0") {
                    adaIsi = true;
                }
            });

            if (!adaIsi) {
                swal("Peringatan", "Minimal satu coil harus diisi berat aktualnya!", "warning");
                return false;
            }

            swal({
                title: "Apakah Anda Yakin?",
                text: "Data akan diproses ke stok dan jurnal akuntansi!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-primary",
                confirmButtonText: "Ya, Proses!",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function() {
                $.ajax({
                    url: siteurl + active_controller + 'process_incoming_coil',
                    type: "POST",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == 1) {
                            swal({
                                title: "Berhasil!",
                                text: result.pesan,
                                type: "success"
                            }, function() {
                                window.location.href = siteurl + active_controller;
                            });
                        } else {
                            swal("Gagal", result.pesan, "error");
                        }
                    },
                    error: function() {
                        swal("Error", "Terjadi kesalahan koneksi server.", "error");
                    }
                });
            });
        });
    });
</script>