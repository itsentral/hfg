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
        <label class="form-label d-flex justify-content-between align-items-center mb-1">
          <span>Order <span class="text-danger">*</span></span>
          <span id="order_suggest_badge" class="badge bg-light-primary text-primary" style="cursor: pointer; display: none;" title="Klik untuk menerapkan angka rekomendasi">
            <i class="ti ti-sparkles me-1"></i> Rekomendasi: <b id="order_suggest_val">1</b>
          </span>
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
          <button type="button" class="btn btn-outline-primary" id="btn_apply_recommendation" style="display: none;" title="Terapkan nomor rekomendasi">
            <i class="ti ti-check me-1"></i> Gunakan
          </button>
        </div>
        <!-- Helper Info Container -->
        <div id="order_info_container" class="mt-2"></div>
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
  var currentSiblingsData = [];
  var currentRecommendedOrder = null;
  var isOrderCollision = false;
  var collisionMenuTitle = '';

  function fetchOrderRecommendation(parentId, isInitialLoad) {
    var menuId = $('#id_hidden').val() || '';
    var formType = $('#type').val() || '';
    var isEdit = (menuId !== '' || formType === 'edit');

    if (parentId === '' || parentId === undefined || parentId === null) {
      parentId = 0;
    }

    $('#order_info_container').html('<div class="text-muted small py-1"><i class="fa fa-spinner fa-spin me-1 text-primary"></i> Memuat rekomendasi urutan...</div>');

    $.ajax({
      url: siteurl + "menus/get_order_recommendation",
      type: "GET",
      dataType: "json",
      data: {
        parent_id: parentId,
        menu_id: menuId
      },
      success: function(res) {
        if (res && res.status === 1) {
          currentSiblingsData = res.siblings || [];
          currentRecommendedOrder = res.next_order;

          // Update badge rekomendasi
          $('#order_suggest_val').text(res.next_order);
          $('#order_suggest_badge').fadeIn(200);

          // Jika mode Create (tambah baru) ATAU input order masih kosong: otomatis isi input order
          var existingVal = $('#order').val();
          if (!isEdit || existingVal === '' || !isInitialLoad) {
            if (!isEdit || existingVal === '') {
              $('#order').val(res.next_order);
            }
          }

          // Update status tombol gunakan rekomendasi & cek tabrakan
          updateApplyButtonState();

          // Render info box
          renderOrderInfoBox(res, isEdit);
        }
      },
      error: function() {
        $('#order_info_container').html('');
        $('#order_suggest_badge').hide();
        $('#btn_apply_recommendation').hide();
      }
    });
  }

  function updateApplyButtonState() {
    var inputVal = parseInt($('#order').val(), 10);
    if (currentRecommendedOrder !== null && !isNaN(inputVal) && inputVal !== currentRecommendedOrder) {
      $('#btn_apply_recommendation').show();
    } else {
      $('#btn_apply_recommendation').hide();
    }
    checkOrderCollision();
  }

  function checkOrderCollision() {
    var inputVal = parseInt($('#order').val(), 10);
    $('#order_collision_warn').remove();

    if (isNaN(inputVal) || currentSiblingsData.length === 0) {
      isOrderCollision = false;
      collisionMenuTitle = '';
      $('#order').removeClass('is-invalid');
      $('#submit').prop('disabled', false).removeClass('disabled');
      return;
    }

    var usedBy = null;
    for (var i = 0; i < currentSiblingsData.length; i++) {
      var s = currentSiblingsData[i];
      if (!s.is_current && parseInt(s.order, 10) === inputVal) {
        usedBy = s;
        break;
      }
    }

    if (usedBy) {
      isOrderCollision = true;
      collisionMenuTitle = usedBy.title;
      $('#order').addClass('is-invalid');
      $('#submit').prop('disabled', true).addClass('disabled');
      var warnHtml = '<div id="order_collision_warn" class="alert alert-danger py-1 px-2 mt-2 mb-0 small rounded">' +
        '<i class="ti ti-circle-x me-1"></i> <b>Tidak dapat disimpan:</b> Nomor urut <b>' + inputVal + '</b> sudah digunakan oleh menu <b>\'' + usedBy.title + '\'</b>. Silakan gunakan nomor urut yang berbeda.' +
        '</div>';
      $('#order_info_container').append(warnHtml);
    } else {
      isOrderCollision = false;
      collisionMenuTitle = '';
      $('#order').removeClass('is-invalid');
      $('#submit').prop('disabled', false).removeClass('disabled');
    }
  }

  function renderOrderInfoBox(data, isEdit) {
    var html = '';
    if (data.total_siblings === 0) {
      html = '<div class="alert alert-light-primary border-primary border-opacity-25 py-2 px-3 mb-0 small rounded">' +
        '<i class="ti ti-info-circle me-1 text-primary"></i> Belum ada menu di bawah parent ini. Rekomendasi urutan pertama: <b>1</b>.' +
        '</div>';
    } else {
      html = '<div class="p-2 border rounded bg-light small">';
      html += '<div class="d-flex justify-content-between align-items-center">';
      html += '<span><i class="ti ti-list-numbers me-1 text-primary"></i> <b>' + data.total_siblings + '</b> menu ada di parent ini</span>';
      html += '<a href="javascript:void(0)" id="btn_toggle_siblings" class="text-primary text-decoration-none fw-semibold"><i class="ti ti-list-details me-1"></i>Lihat Urutan</a>';
      html += '</div>';

      // Daftar siblings
      html += '<div id="siblings_list_box" class="mt-2 pt-2 border-top" style="display: none; max-height: 140px; overflow-y: auto;">';
      html += '<div class="d-flex flex-wrap gap-1">';
      for (var i = 0; i < data.siblings.length; i++) {
        var s = data.siblings[i];
        var badgeClass = s.is_current ? 'bg-primary text-white shadow-sm' : 'bg-white border text-dark';
        var currentBadge = s.is_current ? ' <span class="badge bg-light text-primary ms-1">Menu Ini</span>' : '';
        html += '<span class="badge ' + badgeClass + ' py-1 px-2" title="' + s.title + '">' +
          '#' + s.order + ' ' + s.title + currentBadge +
          '</span>';
      }
      html += '</div>';
      html += '</div>';

      html += '<div class="mt-2 pt-1 border-top d-flex justify-content-between align-items-center">';
      html += '<span class="text-muted">Urutan tertinggi: <b>' + data.max_order + '</b></span>';
      html += '<span>Rekomendasi berikutnya: <span class="badge bg-success text-white">#' + data.next_order + '</span></span>';
      html += '</div>';
      html += '</div>';
    }

    $('#order_info_container').html(html);

    // Toggle list siblings
    $('#btn_toggle_siblings').on('click', function(e) {
      e.preventDefault();
      var $box = $('#siblings_list_box');
      if ($box.is(':visible')) {
        $box.slideUp(150);
        $(this).html('<i class="ti ti-list-details me-1"></i>Lihat Urutan');
      } else {
        $box.slideDown(150);
        $(this).html('<i class="ti ti-chevron-up me-1"></i>Sembunyikan');
      }
    });

    checkOrderCollision();
  }

  $(document).ready(function() {

    // Select2
    $(".parent_id").select2({
      width: '100%'
    });
    $(".group_menu").select2({
      width: '100%'
    });
    $(".permission_id").select2({
      width: '100%'
    });

    // Event listener Parent ID change
    $('#parent_id').on('change', function() {
      fetchOrderRecommendation($(this).val(), false);
    });

    // Event listener klik tombol gunakan rekomendasi / badge rekomendasi
    $('#order_suggest_badge, #btn_apply_recommendation').on('click', function() {
      if (currentRecommendedOrder !== null) {
        $('#order').val(currentRecommendedOrder).trigger('input');
        updateApplyButtonState();
      }
    });

    // Event listener saat user ketik order secara manual
    $('#order').on('input change', function() {
      updateApplyButtonState();
    });

    // Initial load
    fetchOrderRecommendation($('#parent_id').val(), true);

  });

  // Submit AJAX
  $('#frm_menus').on('submit', function(e) {
    e.preventDefault();

    // Validasi tabrakan nomor urut di frontend
    if (isOrderCollision) {
      swal({
        title: "Nomor Urut Sama!",
        text: "Nomor urut " + $('#order').val() + " sudah digunakan oleh menu '" + collisionMenuTitle + "'. Silakan gunakan nomor urut yang berbeda sebelum menyimpan.",
        type: "error"
      });
      $('#order').focus();
      return false;
    }

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
            title: "Gagal Menyimpan!",
            text: msg['msg'] || "Data Gagal Di Simpan",
            type: "error"
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