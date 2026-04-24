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
                                        <option value="<?= $sup->kode_supplier ?>"
                                            <?= (!empty($ros_data) && $ros_data->id_supplier == $sup->kode_supplier) ? 'selected' : '' ?>>
                                            <?= $sup->nama ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <div class="col-md-4"><label>No. PO</label></div>
                            <div class="col-md-8">
                                <select id="no_po" name="no_po" class="form-control select2" <?= empty($ros_data) ? 'disabled' : '' ?>>
                                    <option value="">Pilih PO</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <div class="col-md-4"><label>No. ROS</label></div>
                            <div class="col-md-8">
                                <select id="no_ros" name="no_ros" class="form-control select2" <?= empty($ros_data) ? 'disabled' : '' ?>>
                                    <option value="">Pilih ROS</option>
                                    <?php if (!empty($ros_data)): ?>
                                        <option value="<?= $ros_data->id ?>" selected><?= $ros_data->id ?></option>
                                    <?php endif; ?>
                                </select>
                                <input type="hidden" name="uang_muka" id="uang_muka" class="form-control" readonly placeholder="Otomatis dari PO">
                                <input type="hidden" name="uang_muka_idr" id="uang_muka_idr" class="form-control" readonly placeholder="Otomatis dari PO">
                                <input type="hidden" name="tanggal" class="form-control">
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
                                <th rowspan="2" class="text-center" style="vertical-align: middle; background-color: #c8e6c9 !important; color: #000;" width="14%">Gudang Tujuan</th>
                                <th colspan="2" class="text-center" style="background-color: #69c79d !important;" hidden>Penimbangan</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="background-color: #d2d6de !important; color: #000;">No. Coil</th>
                                <th class="text-center" style="background-color: #d2d6de !important; color: #000;">Berat Kotor</th>
                                <th class="text-center" style="background-color: #d2d6de !important; color: #000;">Berat Bersih</th>
                                <th class="text-center" style="background-color: #f3b44e !important;">OK</th>
                                <th class="text-center" style="background-color: #f3b44e !important;">Reject</th>
                                <th class="text-center" style="background-color: #69c79d !important;" hidden>Aktual Berat Kotor</th>
                                <th class="text-center" style="background-color: #69c79d !important;" hidden>Selisih</th>
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

        <?php if (!empty($no_ros_default) && !empty($ros_data)): ?>
        // Auto-load: ROS sudah dipilih dari URL, langsung load PO dan tabel coil
        (function() {
            var id_supplier = '<?= $ros_data->id_supplier ?>';
            var no_ros      = '<?= $ros_data->id ?>';

            // Load PO list untuk supplier ini
            $.ajax({
                url: siteurl + active_controller + 'get_po_by_supplier',
                type: 'POST',
                data: { id_supplier: id_supplier },
                dataType: 'json',
                success: function(data) {
                    let opt = '<option value="">Pilih PO</option>';
                    data.forEach(item => {
                        opt += `<option value="${item.no_po}"
                            data-uang-muka="${item.uang_muka}"
                            data-uang-muka-idr="${item.uang_muka_idr}">
                            ${item.no_surat} (${item.no_po})
                        </option>`;
                    });
                    $('#no_po').html(opt).prop('disabled', false);

                    // Pilih PO pertama yang terkait ROS ini
                    var no_po_ros = '<?= $ros_data->no_po ?>';
                    // no_po di tr_ros bisa berisi multiple, ambil yang pertama
                    var first_po = no_po_ros.split(',')[0].trim();
                    $('#no_po option').each(function() {
                        if ($(this).val() === first_po) {
                            $(this).prop('selected', true);
                            var sel = $(this);
                            $('#uang_muka').val(sel.data('uang-muka') || '');
                            $('#uang_muka_idr').val(sel.data('uang-muka-idr') || '');
                        }
                    });
                    $('#no_po').trigger('change.select2');

                    // Load ROS list untuk PO ini
                    $.ajax({
                        url: siteurl + active_controller + 'get_ros_by_po_select',
                        type: 'POST',
                        data: { no_po: first_po },
                        dataType: 'json',
                        success: function(rosData) {
                            let rosOpt = '<option value="">Pilih ROS</option>';
                            rosData.forEach(item => {
                                rosOpt += `<option value="${item.no_ros}"
                                    ${item.no_ros === no_ros ? 'selected' : ''}>
                                    ${item.no_ros}
                                </option>`;
                            });
                            $('#no_ros').html(rosOpt).prop('disabled', false).trigger('change.select2');

                            // Trigger load tabel coil
                            loadCoilTable(no_ros);
                        }
                    });
                }
            });
        })();
        <?php endif; ?>

        // 1. SUPPLIER CHANGE -> GET PO
        $(document).on('change', '#id_supplier', function() {
            let id_supplier = $(this).val();
            $('#no_po').html('<option value="">Pilih PO</option>').prop('disabled', true);
            $('#no_ros').html('<option value="">Pilih ROS</option>').prop('disabled', true);
            $('#uang_muka').val('');
            $('#uang_muka_idr').val('');
            
            if (id_supplier) {
                $.ajax({
                    url: siteurl + active_controller + 'get_po_by_supplier',
                    type: 'POST',
                    data: {
                        id_supplier: id_supplier
                    },
                    dataType: 'json',
                    success: function(data) {
                        let opt = '<option value="">Pilih PO</option>';
                        data.forEach(item => {
                            opt += `<option value="${item.no_po}" data-uang-muka="${item.uang_muka}" data-uang-muka-idr="${item.uang_muka_idr}">${item.no_surat} (${item.no_po})</option>`;
                        });
                        $('#no_po').html(opt).prop('disabled', false);
                    }
                });
            }
        });

        // 2. PO CHANGE -> GET ROS + ISI UANG MUKA
        $(document).on('change', '#no_po', function() {
            let no_po = $(this).val();
            $('#no_ros').html('<option value="">Pilih ROS</option>').prop('disabled', true);

            // Isi uang muka dari data attribute option yang dipilih
            let selected = $(this).find('option:selected');
            $('#uang_muka').val(selected.data('uang-muka') || '');
            $('#uang_muka_idr').val(selected.data('uang-muka-idr') || '');

            if (no_po) {
                $.ajax({
                    url: siteurl + active_controller + 'get_ros_by_po_select',
                    type: 'POST',
                    data: {
                        no_po: no_po
                    },
                    dataType: 'json',
                    success: function(data) {
                        let opt = '<option value="">Pilih ROS</option>';
                        data.forEach(item => {
                            opt += `<option value="${item.no_ros}">${item.no_ros}</option>`;
                        });
                        $('#no_ros').html(opt).prop('disabled', false);
                    }
                });
            }
        });

        // 3. ROS CHANGE -> RENDER TABLE
        $(document).on('change', '#no_ros', function() {
            let no_ros = $(this).val();
            if (no_ros) {
                loadCoilTable(no_ros);
            } else {
                $('#list-item-coil').html('');
            }
        });

        function loadCoilTable(no_ros) {
            $.ajax({
                url: siteurl + active_controller + 'get_ros_detail_to_table',
                type: 'POST',
                data: { no_ros: no_ros },
                dataType: 'json',
                success: function(data) {
                        let html = '';
                        let currentMaterial = '';

                        // Build gudang options
                        let gudangOpts = '<option value="">-- Pilih --</option>';
                        <?php foreach ($list_gudang as $g): ?>
                        gudangOpts += `<option value="<?= $g['id'] ?>" data-kd="<?= $g['kd_gudang'] ?>"><?= $g['nm_gudang'] ?> (<?= $g['kd_gudang'] ?>)</option>`;
                        <?php endforeach; ?>

                        if (data.length > 0) {
                            data.forEach((item, index) => {
                                let rowMaterial = '';
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
                                    rowMaterial = `<td colspan="5" style="border-top:none;"></td>`;
                                }

                                html += `
                                <tr>
                                    ${rowMaterial}
                                    <td class="bg-gray text-center">${item.no_coil}</td>
                                    <td class="bg-gray text-end">${item.ros_kotor}</td>
                                    <td class="bg-gray text-end">${item.ros_bersih}</td>
                                    <td class="text-center">
                                        <input type="radio" name="detail[${index}][status_qc]" value="OK" checked>
                                    </td>
                                    <td class="text-center">
                                        <input type="radio" name="detail[${index}][status_qc]" value="REJECT">
                                        <input type="hidden" name="detail[${index}][id_ros_detail]" value="${item.id_ros_detail}">
                                        <input type="hidden" name="detail[${index}][id_po_detail]" value="${item.id_po_detail}">
                                        <input type="hidden" name="detail[${index}][id_material]" value="${item.id_material}">
                                        <input type="hidden" name="detail[${index}][no_coil]" value="${item.no_coil}">
                                        <input type="hidden" name="detail[${index}][no_ros]" value="${item.no_ros}">
                                        <input type="hidden" name="detail[${index}][aktual_bersih]" value="${item.ros_bersih}">
                                        <input type="hidden" name="detail[${index}][price_coil]" value="${item.price_coil}">
                                        <input type="hidden" name="detail[${index}][price_coil_idr]" value="${item.price_coil_idr}">
                                        <input type="hidden" name="detail[${index}][biaya_masuk]" value="${item.biaya_masuk}">
                                        <input type="hidden" name="detail[${index}][forwarding_cost]" value="${item.forwarding_cost}">
                                    </td>
                                    <td class="text-center" style="background-color: #f1f8e9;">
                                        <select name="detail[${index}][id_gudang_ke]" class="form-control form-control-sm select-gudang-coil" style="min-width:130px;" required>
                                            ${gudangOpts}
                                        </select>
                                        <input type="hidden" name="detail[${index}][kd_gudang_ke]" class="kd-gudang-coil" value="">
                                    </td>
                                </tr>`;
                            });
                        } else {
                            html = '<tr><td colspan="11" class="text-center">Data tidak ditemukan</td></tr>';
                        }
                        $('#list-item-coil').html(html);
                        $('.select-gudang-coil').select2({ width: '100%' });
                }
            });
        }

        // Sync kd_gudang_ke per baris saat pilih gudang
        $(document).on('change', '.select-gudang-coil', function() {
            let kd = $(this).find('option:selected').data('kd') || '';
            $(this).closest('td').find('.kd-gudang-coil').val(kd);
        });

        // 3. Hitung Selisih Real-time
        $(document).on('input', '.hitung-selisih', function() {
            let aktual = parseFloat($(this).val()) || 0;
            let ros = parseFloat($(this).data('ros')) || 0;
            let selisih = aktual - ros;

            $(this).closest('tr').find('.text-selisih').val(selisih.toFixed(2));
        });

        // Sync kd_gudang_ke saat pilih gudang
        $(document).on('change', '#id_gudang_ke', function() {
            let kd = $(this).find('option:selected').data('kd') || '';
            $('#kd_gudang_ke').val(kd);
        });

        $(document).on('click', '#save-incoming', function(e) {
            e.preventDefault();

            let formData = new FormData($('#data-form')[0]);
            let no_po = $('#no_po').val();

            if (no_po == "") {
                Swal.fire({ title: "Peringatan", text: "Pilih Nomor PO terlebih dahulu!", icon: "warning", confirmButtonText: "OK" });
                return false;
            }

            // Validasi gudang per baris
            let gudangKosong = false;
            $('.select-gudang-coil').each(function() {
                if ($(this).val() == '' || $(this).val() == null) {
                    gudangKosong = true;
                }
            });
            if (gudangKosong) {
                Swal.fire({ title: "Peringatan", text: "Semua coil harus dipilih gudang tujuannya!", icon: "warning", confirmButtonText: "OK" });
                return false;
            }

            Swal.fire({
                title: "Apakah Anda Yakin?",
                text: "Data akan diproses ke stok dan jurnal akuntansi!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Proses!",
                cancelButtonText: "Batal"
            }).then(function(result) {
                if (result.isConfirmed) {
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
                                Swal.fire({ title: "Berhasil!", text: result.pesan, icon: "success", timer: 1500, showConfirmButton: false })
                                    .then(function(){ window.location.href = siteurl + active_controller; });
                            } else if (result.status == 2) {
                                Swal.fire({ title: "Transaksi Tersimpan", text: result.pesan, icon: "warning", confirmButtonText: "OK" })
                                    .then(function(){ window.location.href = siteurl + active_controller; });
                            } else {
                                Swal.fire({ title: "Gagal", text: result.pesan, icon: "error", confirmButtonText: "OK" });
                            }
                        },
                        error: function() {
                            Swal.fire({ title: "Error", text: "Terjadi kesalahan koneksi server.", icon: "error", confirmButtonText: "OK" });
                        }
                    });
                }
            });
        });
    });
</script>