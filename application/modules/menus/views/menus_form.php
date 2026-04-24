<div class="card">
  <div class="card-body">

    <div id="alert_edit" class="alert alert-success alert-dismissible fade show d-none" role="alert">
      <span id="alert_edit_text"></span>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <?php if (isset($data->id)) {
      $type = 'edit';
    } ?>

    <?= form_open(
      $this->uri->uri_string(),
      [
        'id'   => 'frm_menus',
        'name' => 'frm_menus',
        'role' => 'form',
        'class' => 'needs-validation',
        'autocomplete' => 'off'
      ]
    ) ?>

    <!-- hidden -->
    <input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
    <input type="hidden" id="id_hidden" name="id" value="<?= set_value('id', isset($data->id) ? $data->id : '') ?>">

    <div class="row g-3">

      <!-- ID Menu -->
      <div class="col-md-6">
        <label class="form-label">
          ID Menu <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="ti ti-hash"></i>
          </span>
          <input
            type="text"
            class="form-control"
            id="id"
            name="id"
            maxlength="4"
            value="<?= set_value('id', isset($data->id) ? $data->id : '') ?>"
            placeholder="ID menu"
            readonly />
        </div>
      </div>

      <!-- Menu Name -->
      <div class="col-md-6">
        <label class="form-label">
          Menu's Name <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="ti ti-menu-2"></i>
          </span>
          <input
            type="text"
            class="form-control"
            id="title"
            name="title"
            maxlength="45"
            value="<?= set_value('title', isset($data->title) ? $data->title : '') ?>"
            placeholder="Menu's Name"
            required />
        </div>
      </div>

      <!-- Link -->
      <div class="col-md-6">
        <label class="form-label">
          Path Menu <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="ti ti-link"></i>
          </span>
          <input
            type="text"
            class="form-control"
            id="link"
            name="link"
            value="<?= set_value('link', isset($data->link) ? $data->link : '') ?>"
            placeholder="contoh: menus / users/setting"
            required />
        </div>
      </div>

      <!-- Parent Menu -->
      <div class="col-md-6">
        <label class="form-label">
          Parent Menu <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="ti ti-sitemap"></i>
          </span>
          <?php
          $parent[0] = 'Select An Option(NONE)';
          echo form_dropdown(
            'parent_id',
            $parent,
            set_value('parent_id', isset($data->parent_id) ? $data->parent_id : 'selected'),
            ['id' => 'parent_id', 'class' => 'form-select parent_id']
          );
          ?>
        </div>
      </div>

      <!-- Show Status -->
      <div class="col-md-6">
        <label class="form-label">
          Show Status <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="ti ti-eye"></i>
          </span>
          <select class="form-select group_menu" name="group_menu" id="group_menu" required>
            <option value="">Select An Option(NONE)</option>
            <?php for ($i = 1; $i <= count($datgroupmenu); $i++) { ?>
              <option value="<?= $i ?>" <?= (isset($data->group_menu) && $i == $data->group_menu) ? "selected" : "" ?>>
                <?= ($datgroupmenu[$i] == "Back End") ? "Show" : "Hidden" ?>
              </option>
            <?php } ?>
          </select>
        </div>
      </div>

      <!-- Icon -->
      <div class="col-md-6">
        <label class="form-label">
          Icon <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="ti ti-plant-2"></i>
          </span>
          <input
            type="text"
            class="form-control"
            id="icon"
            name="icon"
            value="<?= set_value('icon', isset($data->icon) ? $data->icon : '') ?>"
            placeholder="contoh: ti ti-settings / ti ti-users"
            required />
        </div>
        <div class="form-text">
          Gunakan Tabler Icon class (Berry): <code>ti ti-xxx</code>
        </div>
      </div>

      <!-- Target -->
      <div class="col-md-6">
        <label class="form-label">Target</label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="ti ti-window"></i>
          </span>
          <select id="target" name="target" class="form-select">
            <option value="_blank" <?= set_select('target', '_blank', isset($data->target) && $data->target == '_blank'); ?>>Blank</option>
            <option value="sametab" <?= set_select('target', 'sametab', isset($data->target) && $data->target == 'sametab'); ?>>Same Tab</option>
          </select>
        </div>
      </div>

      <!-- Status -->
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="ti ti-toggle-right"></i>
          </span>
          <select id="status" name="status" class="form-select">
            <option value="1" <?= set_select('status', '1', isset($data->status) && $data->status == '1'); ?>>Active</option>
            <option value="0" <?= set_select('status', '0', isset($data->status) && $data->status == '0'); ?>>Inactive</option>
          </select>
        </div>
      </div>

      <!-- Order -->
      <div class="col-md-6">
        <label class="form-label">
          Order <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="ti ti-sort-ascending"></i>
          </span>
          <input
            type="number"
            class="form-control"
            id="order"
            name="order"
            value="<?= set_value('order', isset($data->order) ? $data->order : '') ?>"
            placeholder="order menu"
            required />
        </div>
      </div>

      <!-- Permission ID -->
      <?php if (isset($data->id)): ?>
        <div class="col-md-6">
          <label class="form-label">
            Permission ID <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="ti ti-key"></i>
            </span>
            <?php
            $permission[0] = 'Select An Option';
            echo form_dropdown(
              'permission_id',
              $permission,
              set_value('permission_id', isset($data->permission_id) ? $data->permission_id : 'selected'),
              ['id' => 'permission_id', 'class' => 'form-select permission_id']
            );
            ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Buttons -->
      <div class="col-12">
        <div class="d-flex gap-2 justify-content-end pt-3 border-top">
          <button type="submit" name="save" class="btn btn-primary" id="submit">
            <i class="ti ti-device-floppy me-1"></i> Save
          </button>
          <button type="button" class="btn btn-dark" onclick="cancel()">
            <i class="ti ti-x me-1"></i> Cancel
          </button>
        </div>
      </div>

    </div>

    <?= form_close() ?>

  </div>
</div>


<script type="text/javascript">
  $(document).ready(function() {

    // Select2 tetap jalan
    $(".parent_id").select2({
      width: '100%'
    });
    $(".group_menu").select2({
      width: '100%'
    });
    $(".permission_id").select2({
      width: '100%'
    });

  });

  // Submit AJAX
  $('#frm_menus').on('submit', function(e) {
    e.preventDefault();
    var formdata = $("#frm_menus").serialize();

    $.ajax({
      url: siteurl + "menus/save_data_menus",
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