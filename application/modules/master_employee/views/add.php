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

?>
<form method="POST" id="form_employee" autocomplete="off" enctype="multipart/form-data">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold">Personal Information</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="nik" value="<?= $nik ?>">

                <div class="col-md-6">
                    <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nm_karyawan" value="<?= $nm_karyawan ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">ID Number</label>
                    <input type="text" class="form-control" name="no_ktp" value="<?= $no_ktp ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Place of Birth</label>
                    <input type="text" class="form-control" name="tmp_lahir" value="<?= $tmp_lahir ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" name="tgl_lahir" value="<?= $tgl_lahir ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Religion</label>
                    <select name="agama" class="form-select">
                        <option value="">Select Religion</option>
                        <?php foreach ($agamax as $v): ?>
                            <option value="<?= $v['name'] ?>" <?= ($v['name'] == $agama ? 'selected' : '') ?>>
                                <?= strtoupper($v['data1']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select Gender</option>
                        <?php foreach ($genderx as $v): ?>
                            <option value="<?= $v['name'] ?>" <?= ($v['name'] == $gender ? 'selected' : '') ?>>
                                <?= strtoupper($v['data1']) ?>
                            </option>
                        <?php endforeach; ?>
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
                    <textarea class="form-control" rows="3" name="ktp_alamat"><?= $ktp_alamat ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Domicile Address</label>
                    <textarea class="form-control" rows="3" name="domisili_alamat"><?= $domisili_alamat ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">ID Card Postcode</label>
                    <input type="text" class="form-control numberOnly" name="ktp_kode_pos" value="<?= $ktp_kode_pos ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Domicile Postcode</label>
                    <input type="text" class="form-control numberOnly" name="domisili_kode_pos" value="<?= $domisili_kode_pos ?>">
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
                    <input type="text" class="form-control numberOnly" name="npwp" value="<?= $npwp ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">BPJS</label>
                    <input type="text" class="form-control" name="bpjs" value="<?= $bpjs ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Join Date</label>
                    <input type="text" class="form-control datepicker" readonly name="tgl_join" value="<?= $tgl_join ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="text" class="form-control datepicker" readonly name="tgl_end" value="<?= $tgl_end ?>">
                </div>

            </div>
        </div>
        <div class="card-body text-end">
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
    })

    $(document).on('click', '#back', function() {
        window.location.href = base_url + active_controller
    });

    $('#saved').click(function(e) {
        e.preventDefault();
        //Customer
        var nm_karyawan = $("#nm_karyawan").val();
        var tmp_lahir = $("#tmp_lahir").val();
        var tgl_lahir = $("#tgl_lahir").val();
        var department = $("#department").val();
        var gender = $("#gender").val();
        var agama = $("#agama").val();
        var pendidikan = $("#pendidikan").val();
        var ktp_kode_pos = $("#ktp_kode_pos").val();
        var ktp_alamat = $("#ktp_alamat").val();
        var domisili_kode_pos = $("#domisili_kode_pos").val();
        var domisili_alamat = $("#domisili_alamat").val();
        var no_ponsel = $("#no_ponsel").val();
        var email = $("#email").val();
        var npwp = $("#npwp").val();
        var bpjs = $("#bpjs").val();
        var no_ktp = $("#no_ktp").val();
        var tgl_join = $("#tgl_join").val();
        var tgl_end = $("#tgl_end").val();
        var sts_karyawan = $("#sts_karyawan").val();
        var rek_number = $("#rek_number").val();
        var bank_account = $("#bank_account").val();
        var status = $("#status").val();

        if (nm_karyawan == '') {
            swal({
                title: "Error Message!",
                text: 'Employee name is empty, please input first ...',
                type: "warning"
            });
            $('#simpan-bro').prop('disabled', false);
            return false;
        }

        // if(nm_customer=='' || nm_customer==null || nm_customer=='-' || nm_customer=='0'){
        //     swal({
        //         title	: "Error Message!",
        //         text	: 'Customer Name in master customer tab is empty, please input first ...',
        //         type	: "warning"
        //     });
        //     return false;
        // }
        // $('#saved').prop('disabled',false);

        swal({
                title: "Are you sure?",
                text: "You will not be able to process again this data!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    // loading_spinner();
                    var formData = new FormData($('#form_employee')[0]);
                    var baseurl = base_url + active_controller + '/add';
                    $.ajax({
                        url: baseurl,
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        beforeSend: function() {
                            $(this).prop('disabled', true);
                        },
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Save Success!",
                                    text: data.pesan,
                                    type: "success",
                                    timer: 7000
                                });
                                window.location.href = base_url + active_controller;
                            } else {
                                swal({
                                    title: "Save Failed!",
                                    text: data.pesan,
                                    type: "warning",
                                    timer: 7000
                                });
                            }
                            $('#saved').prop('disabled', false);
                        },
                        error: function() {

                            swal({
                                title: "Error Message !",
                                text: 'An Error Occured During Process. Please try again..',
                                type: "warning",
                                timer: 7000
                            });
                            $('#saved').prop('disabled', false);
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be process again :)", "error");
                    $('#saved').prop('disabled', false);
                    return false;
                }
            });
    });
</script>