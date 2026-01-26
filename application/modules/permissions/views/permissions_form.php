<div class="card">
    <div class="card-body">

        <div id="alert_edit" class="alert alert-success d-none" style="padding: 15px;"></div>

        <?= form_open($this->uri->uri_string(), array(
            'id' => 'frm_permissions',
            'name' => 'frm_permissions',
            'role' => 'form'
        )) ?>

        <?php if (isset($data->id_permission)) {
            $type = 'edit';
        } ?>
        <input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
        <input type="hidden" id="id" name="id" value="<?= set_value('id', isset($data->id) ? $data->id : '') ?>">

        <div class="row g-3">

            <!-- ID Permission -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    ID Permission <span class="text-danger">*</span>
                </label>

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="ti ti-hash"></i>
                    </span>
                    <input type="text"
                        class="form-control"
                        id="id_permission"
                        name="id_permission"
                        maxlength="4"
                        onkeyup="this.value=this.value.replace(/[^0-9]/g,'');"
                        value="<?= set_value('id_permission', isset($data->id_permission) ? $data->id_permission : '') ?>"
                        placeholder="ID Permission"
                        readonly>
                </div>
            </div>

            <!-- Permission Menu -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Permission Menu <span class="text-danger">*</span>
                </label>

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="ti ti-menu-2"></i>
                    </span>

                    <?php
                    $datmenu[0] = 'Select An Option';
                    echo form_dropdown(
                        'nm_permission',
                        $datmenu,
                        set_value('nm_permission', isset($data->nm_menu) ? $data->nm_menu : 'selected'),
                        array('id' => 'nm_permission', 'class' => 'form-control nm_permission')
                    );
                    ?>
                </div>
            </div>

            <!-- Jenis Permission -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Jenis Permission <span class="text-danger">*</span>
                </label>

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="ti ti-shield"></i>
                    </span>

                    <select id="ket" name="ket" class="form-select">
                        <option value="View" <?= set_select('ket', 'View', isset($data->ket) && $data->ket == 'View'); ?>>View</option>
                        <option value="Add" <?= set_select('ket', 'Add', isset($data->ket) && $data->ket == 'Add'); ?>>Add</option>
                        <option value="Manage" <?= set_select('ket', 'Manage', isset($data->ket) && $data->ket == 'Manage'); ?>>Manage</option>
                        <option value="Delete" <?= set_select('ket', 'Delete', isset($data->ket) && $data->ket == 'Delete'); ?>>Delete</option>
                        <option value="Marketing" <?= set_select('ket', 'Marketing', isset($data->ket) && $data->ket == 'Marketing'); ?>>Marketing</option>
                    </select>
                </div>

                <small class="text-muted d-block mt-1">
                    Pilih tipe akses yang akan dibuat.
                </small>
            </div>

        </div>

        <hr class="my-4">

        <!-- Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <button type="submit" name="save" class="btn btn-primary" id="submit">
                <i class="ti ti-device-floppy"></i> Save
            </button>

            <button type="button" class="btn btn-dark" onclick="cancel()">
                <i class="ti ti-x"></i> Cancel
            </button>
        </div>

        <?= form_close() ?>

    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        $(".nm_permission").select2({
            width: '100%'
        });
    });

    // Submit AJAX
    $('#frm_permissions').on('submit', function(e) {
        e.preventDefault();
        var formdata = $("#frm_permissions").serialize();

        $.ajax({
            url: siteurl + "permissions/save_data_permissions",
            dataType: "json",
            type: 'POST',
            data: formdata,
            success: function(msg) {
                if (msg['save'] == '1') {
                    swal({
                        title: "Sukses!",
                        text: "Data Berhasil Di Simpan",
                        type: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });
                    cancel();
                    window.location.reload();
                } else {
                    swal({
                        title: "Gagal!",
                        text: "Data Gagal Di Simpan",
                        type: "error",
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function() {
                swal({
                    title: "Gagal!",
                    text: "Ajax Data Gagal Di Proses",
                    type: "error",
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });

    function cancel() {
        window.location.reload();
    }
</script>