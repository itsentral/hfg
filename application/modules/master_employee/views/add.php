<!-- CDN Flatpickr & ViewerJS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>

<?php
$id                    = (!empty($header[0]->id)) ? $header[0]->id : '';
$nik                = (!empty($header[0]->id)) ? $header[0]->nik : '';
$nm_karyawan        = (!empty($header[0]->id)) ? $header[0]->nm_karyawan : '';
$no_ktp                = (!empty($header[0]->id)) ? $header[0]->no_ktp : '';
$tmp_lahir            = (!empty($header[0]->id)) ? $header[0]->tmp_lahir : '';
$tgl_lahir            = (!empty($header[0]->id)) ? $header[0]->tgl_lahir : '';
$gender                = (!empty($header[0]->id)) ? $header[0]->gender : '';
$agama                = (!empty($header[0]->id)) ? $header[0]->agama : '';
$department            = (!empty($header[0]->id)) ? $header[0]->department : '';
$sub_department        = (!empty($header[0]->id)) ? $header[0]->sub_department : '';
$cost_center        = (!empty($header[0]->id)) ? $header[0]->cost_center : '';
$no_ponsel            = (!empty($header[0]->id)) ? $header[0]->no_ponsel : '';
$email                = (!empty($header[0]->id)) ? $header[0]->email : '';
$pendidikan            = (!empty($header[0]->id)) ? $header[0]->pendidikan : '';
$position            = (!empty($header[0]->id)) ? $header[0]->position : '';
$ktp_provinsi        = (!empty($header[0]->id)) ? $header[0]->ktp_provinsi : '';
$domisili_provinsi    = (!empty($header[0]->id)) ? $header[0]->domisili_provinsi : '';
$ktp_kota            = (!empty($header[0]->id)) ? $header[0]->ktp_kota : '';
$domisili_kota        = (!empty($header[0]->id)) ? $header[0]->domisili_kota : '';
$ktp_kecamatan        = (!empty($header[0]->id)) ? $header[0]->ktp_kecamatan : '';
$domisili_kecamatan    = (!empty($header[0]->id)) ? $header[0]->domisili_kecamatan : '';
$ktp_kelurahan        = (!empty($header[0]->id)) ? $header[0]->ktp_kelurahan : '';
$domisili_kelurahan    = (!empty($header[0]->id)) ? $header[0]->domisili_kelurahan : '';
$ktp_kode_pos        = (!empty($header[0]->id)) ? $header[0]->ktp_kode_pos : '';
$domisili_kode_pos    = (!empty($header[0]->id)) ? $header[0]->domisili_kode_pos : '';
$ktp_alamat            = (!empty($header[0]->id)) ? $header[0]->ktp_alamat : '';
$domisili_alamat    = (!empty($header[0]->id)) ? $header[0]->domisili_alamat : '';
$npwp                = (!empty($header[0]->id)) ? $header[0]->npwp : '';
$bpjs                = (!empty($header[0]->id)) ? $header[0]->bpjs : '';
$tgl_join            = (!empty($header[0]->id)) ? $header[0]->tgl_join : '';
$tgl_end            = (!empty($header[0]->id)) ? $header[0]->tgl_end : '';
$rek_number            = (!empty($header[0]->id)) ? $header[0]->rek_number : '';
$bank_account        = (!empty($header[0]->id)) ? $header[0]->bank_account : '';
$sts_karyawan        = (!empty($header[0]->id)) ? $header[0]->sts_karyawan : '';
$status                = (!empty($header[0]->id)) ? $header[0]->status : '';
$tanda_tangan        = (!empty($header[0]->id)) ? $header[0]->tanda_tangan : '';
$tanda               = (!empty($tanda)) ? $tanda : '';

?>
<form method="POST" id="form_employee" autocomplete="off" enctype="multipart/form-data">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold">Personal Information</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <input type="hidden" name="nik" value="<?= html_escape($nik) ?>">

                <div class="col-md-6">
                    <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nm_karyawan" name="nm_karyawan" maxlength="40" value="<?= html_escape($nm_karyawan) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">ID Number <small class="text-muted">(No. KTP, 16 digit)</small></label>
                    <input type="text" class="form-control numberOnly" id="no_ktp" name="no_ktp" maxlength="16" inputmode="numeric" value="<?= html_escape($no_ktp) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Place of Birth</label>
                    <input type="text" class="form-control" id="tmp_lahir" name="tmp_lahir" maxlength="30" value="<?= html_escape($tmp_lahir) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="text" class="form-control flatpickr-date" id="tgl_lahir" name="tgl_lahir" value="<?= html_escape($tgl_lahir) ?>" placeholder="YYYY-MM-DD">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Religion</label>
                    <select name="agama" id="agama" class="form-select">
                        <option value="">Select Religion</option>
                        <?php foreach ($agamax as $v): ?>
                            <option value="<?= $v['name'] ?>" <?= ($v['name'] == $agama ? 'selected' : '') ?>>
                                <?= strtoupper((string) $v['data1']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <select name="gender" id="gender" class="form-select">
                        <option value="">Select Gender</option>
                        <?php foreach ($genderx as $v): ?>
                            <option value="<?= $v['name'] ?>" <?= ($v['name'] == $gender ? 'selected' : '') ?>>
                                <?= strtoupper((string) $v['data1']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class='col-sm-6'>
                    <label class='form-label'>Department</label>
                    <select name='department' id='department' class='form-select'>
                        <option value=''>Select An Department</option>
                        <?php
                        foreach ($departmentx as $val => $valx) {
                            $selected = ($valx['id'] == $department) ? 'selected' : '';
                            echo "<option value='" . $valx['id'] . "' " . $selected . ">" . strtoupper((string) $valx['nama']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class='col-sm-6' id="container_sub_department" style="display: none;">
                    <label class='form-label'>Sub Department / Role</label>
                    <select name='sub_department' id='sub_department' class='form-select'>
                        <option value=''>Select Role</option>
                        <option value='Setter' <?= ($sub_department == 'Setter' ? 'selected' : '') ?>>Setter</option>
                        <option value='Helper' <?= ($sub_department == 'Helper' ? 'selected' : '') ?>>Helper</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Last Education</label>
                    <select name='pendidikan' id='pendidikan' class='form-control input-md'>
                        <option value=''>Select An Last Education</option>
                        <?php
                        foreach ($pendidikanx as $val => $valx) {
                            $selected = ($valx['name'] == $pendidikan) ? 'selected' : '';
                            echo "<option value='" . $valx['name'] . "' " . $selected . ">" . $valx['data1'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold">Address</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">ID Card Address</label>
                    <textarea class="form-control" rows="3" id="ktp_alamat" name="ktp_alamat" maxlength="255"><?= html_escape($ktp_alamat) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Domicile Address</label>
                    <textarea class="form-control" rows="3" id="domisili_alamat" name="domisili_alamat" maxlength="255"><?= html_escape($domisili_alamat) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">ID Card Postcode</label>
                    <input type="text" class="form-control numberOnly" id="ktp_kode_pos" name="ktp_kode_pos" maxlength="5" inputmode="numeric" value="<?= html_escape($ktp_kode_pos) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Domicile Postcode</label>
                    <input type="text" class="form-control numberOnly" id="domisili_kode_pos" name="domisili_kode_pos" maxlength="5" inputmode="numeric" value="<?= html_escape($domisili_kode_pos) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contact Number</label>
                    <?php
                    echo form_input(array('id' => 'no_ponsel', 'name' => 'no_ponsel', 'class' => 'form-control numberOnly', 'placeholder' => 'Contoh: 081234567890', 'maxlength' => '16', 'inputmode' => 'tel'), $no_ponsel);
                    ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <?php
                    echo form_input(array('id' => 'email', 'name' => 'email', 'type' => 'email', 'class' => 'form-control', 'placeholder' => 'nama@contoh.com', 'maxlength' => '45'), $email);
                    ?>
                </div>

            </div>
        </div>
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold">Additional Information</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">NPWP</label>
                    <input type="text" class="form-control numberOnly" id="npwp" name="npwp" maxlength="16" inputmode="numeric" value="<?= html_escape($npwp) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">BPJS</label>
                    <input type="text" class="form-control numberOnly" id="bpjs" name="bpjs" maxlength="20" inputmode="numeric" value="<?= html_escape($bpjs) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Account Bank</label>
                    <select name="bank_account" id="bank_account" class="form-control select">
                        <option value=''>Select An Account Bank</option>
                        <?php
                        foreach ($bankx as $val => $valx) {
                            $selected = ($valx['code'] == $bank_account) ? 'selected' : '';
                            echo "<option value='" . $valx['code'] . "' " . $selected . ">" . strtoupper((string) $valx['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Account Number</label>
                    <?php
                    echo form_input(array('id' => 'rek_number', 'name' => 'rek_number', 'class' => 'form-control numberOnly', 'placeholder' => 'Account Number', 'maxlength' => '30', 'inputmode' => 'numeric'), $rek_number);
                    ?>
                </div>

                <div class="col-md-6">
                    <label class='form-label'>Employee Status</label>
                    <select name='sts_karyawan' id='sts_karyawan' class='form-control select'>
                        <option value=''>Select An Employee Status</option>
                        <?php
                        foreach ($sts_karyawanx as $val => $valx) {
                            $selected = ($valx['name'] == $sts_karyawan) ? 'selected' : '';
                            echo "<option value='" . $valx['name'] . "' " . $selected . ">" . strtoupper((string) $valx['data1']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class='form-label'>Status</label>
                    <select name='status' id='status' class='form-control select'>
                        <option value=''>Select An Status</option>
                        <?php
                        foreach ($statusx as $val => $valx) {
                            $selected = ($valx['name'] == $status) ? 'selected' : '';
                            echo "<option value='" . $valx['name'] . "' " . $selected . ">" . strtoupper((string) $valx['data1']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Join Date</label>
                    <input type="text" class="form-control flatpickr-date" id="tgl_join" name="tgl_join" value="<?= html_escape($tgl_join) ?>" placeholder="YYYY-MM-DD">
                </div>

                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="text" class="form-control flatpickr-date" id="tgl_end" name="tgl_end" value="<?= html_escape($tgl_end) ?>" placeholder="YYYY-MM-DD">
                </div>

                <div class="col-md-12">
                    <label for="tanda_tangan" class="form-label">Upload Tanda Tangan (.jpeg/.jpg/.png)</label>

                    <div class="d-flex flex-column gap-2">

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <!-- input asli disembunyikan -->
                            <input type="file"
                                name="tanda_tangan"
                                id="tanda_tangan"
                                class="d-none"
                                accept=".jpg,.jpeg,.png,image/jpeg,image/png">

                            <!-- tombol pilih file -->
                            <button type="button" class="btn btn-outline-warning" id="btnPickTtd">
                                <i class="ti ti-upload me-1"></i> Choose File
                            </button>

                            <!-- nama file -->
                            <span class="text-muted" id="ttdFileName">No file chosen</span>

                            <!-- tombol clear -->
                            <button type="button" class="btn btn-light border" id="btnClearTtd" style="display:none;">
                                <i class="ti ti-x me-1"></i> Clear
                            </button>
                        </div>

                        <!-- hint -->
                        <small class="text-muted">
                            Allowed: JPG/JPEG/PNG, maksimal 2 MB.
                        </small>

                        <!-- warning jika belum ada file existing -->
                        <?php if (empty($tanda_tangan)) : ?>
                            <small class="text-danger">Belum diupload !!!</small>
                        <?php endif; ?>

                        <!-- existing file -->
                        <?php if (!empty($tanda_tangan)) : ?>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-light text-dark border">
                                    <i class="ti ti-file-description me-1"></i> Existing Tanda Tangan
                                </span>

                                <a href="<?= base_url() . $tanda_tangan; ?>" download class="btn btn-sm btn-success">
                                    <i class="ti ti-download me-1"></i> Download
                                </a>

                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPreviewTtd">
                                    <i class="ti ti-eye me-1"></i> Preview
                                </button>
                            </div>

                            <!-- thumbnail preview -->
                            <div id="ttdImageContainer">
                                <img id="ttdImage" src="<?= base_url() . $tanda_tangan; ?>"
                                    alt="Ttd"
                                    style="width:160px; height:100px; object-fit:contain; border:1px solid #ddd; border-radius:6px; padding:4px; background:#fff; cursor:pointer;">
                            </div>
                        <?php endif; ?>

                    </div>

                    <script>
                        (function() {
                            const input = document.getElementById('tanda_tangan');
                            const btnPick = document.getElementById('btnPickTtd');
                            const btnClear = document.getElementById('btnClearTtd');
                            const fileName = document.getElementById('ttdFileName');

                            if (!input || !btnPick || !fileName) return;

                            btnPick.addEventListener('click', function() {
                                input.click();
                            });

                            input.addEventListener('change', function() {
                                const name = (input.files && input.files.length) ? input.files[0].name : 'No file chosen';
                                fileName.textContent = name;

                                if (btnClear) {
                                    btnClear.style.display = (input.files && input.files.length) ? 'inline-flex' : 'none';
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
        <div class="card-body text-center">
            <button type="button" id="back" class="btn btn-dark">
                <i class="ti ti-arrow-left"></i> Back
            </button>

            <button type="button" id="saved" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Save
            </button>
        </div>
    </div>
</form>


<script>
    $(document).ready(function() {
        $('select').select2({
            width: '100%'
        });

        // Inisialisasi Flatpickr
        flatpickr('.flatpickr-date', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });

        // Inisialisasi ViewerJS jika ada gambar ttd
        var ttdImg = document.getElementById('ttdImage');
        if (ttdImg) {
            var viewer = new Viewer(ttdImg, {
                inline: false,
                viewed: function() {}
            });

            $(document).on('click', '#btnPreviewTtd', function(e) {
                e.preventDefault();
                viewer.show();
            });
        }

        // Mode View Read-Only
        var modeTanda = '<?= $tanda ?>';
        if (modeTanda === 'view') {
            $('#form_employee input, #form_employee select, #form_employee textarea, #btnPickTtd, #btnClearTtd').prop('disabled', true);
            $('#saved').hide();
        }

        function checkDepartment() {
            var deptId = $('#department').val();
            // Produksi ID = 8
            if (deptId == '8') {
                $('#container_sub_department').show();
            } else {
                $('#container_sub_department').hide();
                $('#sub_department').val('').trigger('change');
            }
        }

        checkDepartment();

        $(document).on('change', '#department', function() {
            checkDepartment();
        });
    })

    $(document).on('click', '#back', function() {
        window.location.href = base_url + active_controller
    });

    // ---------------------------------------------------------------
    // VALIDASI & SIMPAN
    // ---------------------------------------------------------------
    // Hanya angka (dan tombol kontrol) untuk isian nomor
    $(document).on('input', '.numberOnly', function() {
        var allowPlus = $(this).is('#no_ponsel');
        var v = this.value;
        var clean = allowPlus ? v.replace(/(?!^\+)[^\d]/g, '') : v.replace(/\D/g, '');
        if (v !== clean) this.value = clean;
    });

    function fieldOf(name) {
        return $('#form_employee [name="' + name + '"]');
    }

    function clearFieldError(name) {
        var $f = fieldOf(name);
        $f.removeClass('is-invalid');
        $f.closest('.col-md-6, .col-sm-6, .col-md-12').find('.invalid-feedback.js-err').remove();
    }

    function setFieldError(name, msg) {
        var $f = fieldOf(name);
        if (!$f.length) return;
        clearFieldError(name);
        $f.addClass('is-invalid');
        var $anchor = $f.hasClass('select2-hidden-accessible') ? $f.next('.select2') : $f;
        if (name === 'tanda_tangan') $anchor = $('#ttdFileName');
        $('<div class="invalid-feedback js-err d-block"></div>').text(msg).insertAfter($anchor);
    }

    function clearAllErrors() {
        $('#form_employee .is-invalid').removeClass('is-invalid');
        $('#form_employee .invalid-feedback.js-err').remove();
    }

    $(document).on('input change', '#form_employee input, #form_employee select, #form_employee textarea', function() {
        if (this.name) clearFieldError(this.name);
    });

    // Aturan sama dengan validasi server (Master_employee::_validate_employee)
    function validateEmployeeForm() {
        var err = {};
        var val = function(n) {
            return $.trim(fieldOf(n).val() || '');
        };
        var digits = function(n) {
            return val(n).replace(/[\s.\-]/g, '');
        };
        var isDate = function(s) {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) return false;
            var d = new Date(s + 'T00:00:00');
            return !isNaN(d) && d.toISOString().slice(0, 10) === s;
        };

        if (val('nm_karyawan') === '') err.nm_karyawan = 'Nama karyawan wajib diisi.';
        if (digits('no_ktp') !== '' && !/^\d{16}$/.test(digits('no_ktp'))) err.no_ktp = 'No. KTP harus 16 digit angka.';
        if (digits('npwp') !== '' && !/^\d{15,16}$/.test(digits('npwp'))) err.npwp = 'NPWP harus 15 atau 16 digit angka.';
        if (digits('bpjs') !== '' && !/^\d{8,20}$/.test(digits('bpjs'))) err.bpjs = 'No. BPJS harus 8-20 digit angka.';
        if (digits('rek_number') !== '' && !/^\d{5,30}$/.test(digits('rek_number'))) err.rek_number = 'No. rekening harus 5-30 digit angka.';
        ['ktp_kode_pos', 'domisili_kode_pos'].forEach(function(n) {
            if (val(n) !== '' && !/^\d{5}$/.test(val(n))) err[n] = 'Kode pos harus 5 digit angka.';
        });
        var hp = val('no_ponsel').replace(/[\s.\-()]/g, '');
        if (hp !== '' && !/^\+?\d{8,15}$/.test(hp)) err.no_ponsel = 'No. HP harus 8-15 digit angka (boleh diawali +).';
        if (val('email') !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val('email'))) err.email = 'Format email tidak valid.';

        var today = new Date().toISOString().slice(0, 10);
        ['tgl_lahir', 'tgl_join', 'tgl_end'].forEach(function(n) {
            if (val(n) !== '' && !isDate(val(n))) err[n] = 'Tanggal tidak valid (format YYYY-MM-DD).';
        });
        if (!err.tgl_lahir && val('tgl_lahir') !== '' && val('tgl_lahir') > today) err.tgl_lahir = 'Tanggal lahir tidak boleh melebihi hari ini.';
        if (!err.tgl_join && !err.tgl_lahir && val('tgl_join') !== '' && val('tgl_lahir') !== '' && val('tgl_join') <= val('tgl_lahir')) err.tgl_join = 'Tanggal join harus setelah tanggal lahir.';
        if (!err.tgl_end && !err.tgl_join && val('tgl_end') !== '' && val('tgl_join') !== '' && val('tgl_end') < val('tgl_join')) err.tgl_end = 'Tanggal berakhir tidak boleh sebelum tanggal join.';

        var file = $('#tanda_tangan')[0];
        if (file && file.files && file.files.length) {
            var f = file.files[0];
            if (!/\.(jpe?g|png)$/i.test(f.name)) err.tanda_tangan = 'File harus berupa gambar JPG/JPEG/PNG.';
            else if (f.size > 2097152) err.tanda_tangan = 'Ukuran file terlalu besar (maksimal 2 MB).';
        }
        return err;
    }

    function showErrors(errors) {
        var names = Object.keys(errors || {});
        names.forEach(function(n) {
            setFieldError(n, errors[n]);
        });
        if (names.length) {
            var $first = fieldOf(names[0]);
            var $target = $first.hasClass('select2-hidden-accessible') ? $first.next('.select2') : $first;
            if (names[0] === 'tanda_tangan') $target = $('#ttdFileName');
            if ($target.length) $('html, body').animate({
                scrollTop: $target.offset().top - 120
            }, 200);
        }
        return names.length;
    }

    $('#saved').click(function(e) {
        e.preventDefault();
        clearAllErrors();
        var nErr = showErrors(validateEmployeeForm());
        if (nErr) {
            Swal.fire({
                icon: 'warning',
                title: 'Data belum lengkap',
                text: 'Ada ' + nErr + ' isian yang perlu diperbaiki (ditandai merah).'
            });
            return false;
        }

        Swal.fire({
            icon: 'question',
            title: 'Simpan data karyawan?',
            showCancelButton: true,
            confirmButtonText: 'Ya, simpan',
            cancelButtonText: 'Batal'
        }).then(function(r) {
            if (!r.isConfirmed) return;
            var $btn = $('#saved').prop('disabled', true);
            $.ajax({
                url: base_url + active_controller + '/add',
                type: 'POST',
                data: new FormData($('#form_employee')[0]),
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.pesan,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.href = base_url + active_controller;
                        });
                        return;
                    }
                    showErrors(data.errors);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gagal menyimpan',
                        text: data.pesan || 'Periksa kembali isian Anda.'
                    });
                    $btn.prop('disabled', false);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memproses data. Silakan coba lagi.'
                    });
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>