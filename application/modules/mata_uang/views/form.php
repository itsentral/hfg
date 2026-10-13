<?php
$id_kurs   = (!empty($header->id_kurs)) ? $header->id_kurs : '';
$negara    = (!empty($header->negara)) ? $header->negara : '';
$kode      = (!empty($header->kode)) ? $header->kode : '';
$mata_uang = (!empty($header->mata_uang)) ? $header->mata_uang : '';
$activation= (!empty($header->activation)) ? $header->activation : 'active';
$is_edit   = !empty($id_kurs);
?>

<input type="hidden" id="id_kurs" name="id_kurs" value="<?= $id_kurs; ?>">

<?php if (!$is_edit) : ?>
<!-- Loading state saat mode tambah baru memuat data negara dari API -->
<div id="modal_loading" class="text-center py-5">
    <div class="spinner-border text-primary" style="width: 2.8rem; height: 2.8rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h6 class="mt-3 text-dark fw-bold mb-1">Memuat Form...</h6>
    <small class="text-muted">Sedang mengambil daftar negara dan mata uang via API</small>
</div>
<?php endif; ?>

<!-- Container Form (di-hide saat mode tambah baru sebelum API siap) -->
<div id="form_content" class="row g-3" style="<?= (!$is_edit) ? 'display: none;' : ''; ?>">
    <!-- NEGARA -->
    <div class="col-12">
        <label for="negara" class="form-label fw-semibold">
            Negara <span class="text-danger">*</span>
        </label>
        
        <?php if ($is_edit) : ?>
            <!-- Mode Edit: Langsung tampilkan negara dan di-disabled / readonly, tanpa panggil API -->
            <div class="input-group">
                <span class="input-group-text bg-light text-primary"><i class="ti ti-world"></i></span>
                <input type="text"
                       class="form-control bg-light text-dark fw-bold"
                       value="<?= htmlspecialchars($negara, ENT_QUOTES, 'UTF-8'); ?>"
                       readonly
                       disabled>
                <input type="hidden" id="negara" name="negara" value="<?= htmlspecialchars($negara, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-text mt-1 text-muted">
                <i class="ti ti-lock me-1 text-secondary"></i>
                Negara tidak dapat diubah pada mode edit.
            </div>
        <?php else : ?>
            <!-- Mode Tambah Baru: Select negara dengan data dari API -->
            <div class="input-group">
                <span class="input-group-text bg-light text-primary"><i class="ti ti-world"></i></span>
                <select id="negara" name="negara" class="form-select" required style="width: calc(100% - 45px);">
                    <option value="">-- Pilih Negara --</option>
                </select>
            </div>
            <div class="form-text mt-1 text-muted">
                <i class="ti ti-info-circle me-1 text-primary"></i>
                Daftar negara dimuat via API. Negara yang <strong>sudah terdaftar</strong> otomatis <strong>dinonaktifkan (disabled)</strong>.
            </div>
            <div id="negara_status_hint" class="mt-1"></div>
        <?php endif; ?>
    </div>

    <!-- KODE MATA UANG -->
    <div class="col-md-6">
        <label for="kode" class="form-label fw-semibold">
            Kode Mata Uang <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-light text-primary"><i class="ti ti-barcode"></i></span>
            <input type="text"
                   class="form-control text-uppercase fw-bold"
                   id="kode"
                   name="kode"
                   required
                   maxlength="10"
                   placeholder="Contoh: USD, IDR"
                   value="<?= htmlspecialchars($kode, ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <small class="text-muted">Kode ISO mata uang (biasanya 3 huruf)</small>
    </div>

    <!-- NAMA MATA UANG -->
    <div class="col-md-6">
        <label for="mata_uang" class="form-label fw-semibold">
            Nama Mata Uang <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-light text-primary"><i class="ti ti-coin"></i></span>
            <input type="text"
                   class="form-control"
                   id="mata_uang"
                   name="mata_uang"
                   required
                   placeholder="Contoh: Dollar, Rupiah"
                   value="<?= htmlspecialchars($mata_uang, ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <small class="text-muted">Nama resmi atau penyebutan mata uang</small>
    </div>

    <!-- STATUS AKTIVASI: IPHONE STYLE TOGGLE DENGAN TEKS DI DALAM -->
    <div class="col-md-12">
        <label class="form-label fw-semibold d-block mb-2">Status Mata Uang</label>
        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border">
            <div>
                <div class="fw-bold text-dark">Status Keaktifan</div>
                <div class="small text-muted mt-1">Status keaktifan mata uang untuk transaksi di sistem</div>
            </div>
            
            <div class="d-flex align-items-center">
                <label class="ios-toggle-labeled" title="Klik untuk beralih status">
                    <input type="checkbox" id="status_toggle" <?= ($activation === 'active') ? 'checked' : ''; ?>>
                    <span class="toggle-track">
                        <span class="toggle-text-active">Active</span>
                        <span class="toggle-text-inactive">Inactive</span>
                        <span class="toggle-handle"></span>
                    </span>
                </label>
                <input type="hidden" id="activation" name="activation" value="<?= ($activation === 'active') ? 'active' : 'inactive'; ?>">
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Toggle iPhone switch listener pada form
        $('#status_toggle').on('change', function() {
            var val = $(this).is(':checked') ? 'active' : 'inactive';
            $('#activation').val(val);
        });

        <?php if (!$is_edit) : ?>
            // Mode Tambah Baru: sembunyikan tombol simpan sampai form siap
            $('#btn_save').hide();

            var apiUrl = "<?= site_url('mata_uang/get_countries_api'); ?>";

            $.ajax({
                url: apiUrl,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1 && res.data) {
                        var $select = $('#negara');
                        $select.empty();
                        $select.append('<option value="">-- Cari / Pilih Negara --</option>');

                        $.each(res.data, function(idx, item) {
                            var opt = $('<option></option>');
                            opt.val(item.name);
                            opt.text(item.name + (item.is_disabled ? ' [Sudah Terdaftar (' + (item.currency_code || '') + ')]' : ''));
                            opt.attr('data-kode', item.currency_code || '');

                            if (item.is_disabled) {
                                opt.prop('disabled', true);
                                opt.addClass('text-muted');
                            }

                            $select.append(opt);
                        });

                        // Sembunyikan loading, lalu tampilkan form dan tombol simpan
                        $('#modal_loading').hide();
                        $('#form_content').fadeIn(200);
                        $('#btn_save').fadeIn(200);

                        // Inisialisasi Select2
                        $select.select2({
                            dropdownParent: $('#dialog-popup'),
                            placeholder: '-- Cari / Pilih Negara --',
                            allowClear: true,
                            width: 'resolve'
                        });
                    } else {
                        $('#modal_loading').html('<div class="alert alert-warning mb-0">Gagal memuat daftar negara via API. Silakan coba kembali.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error load countries API:', error);
                    $('#modal_loading').html('<div class="alert alert-danger mb-0">Gagal menghubungi server untuk memuat daftar negara.</div>');
                }
            });

            // Event change negara: otomatis isi kode mata uang jika belum diisi atau baru
            $('#negara').on('change', function() {
                var selectedOpt = $(this).find('option:selected');
                var currencyCode = selectedOpt.data('kode') || '';

                if (currencyCode) {
                    $('#kode').val(currencyCode.toUpperCase());
                }

                var val = selectedOpt.val();
                if (val) {
                    $('#negara_status_hint').html(
                        '<span class="badge bg-light-success text-success"><i class="ti ti-check me-1"></i>Negara dapat ditambahkan' + (currencyCode ? ' (Mata uang: ' + currencyCode + ')' : '') + '</span>'
                    );
                } else {
                    $('#negara_status_hint').html('');
                }
            });
        <?php else : ?>
            // Mode Edit: Pastikan tombol simpan langsung tampil
            $('#btn_save').show();
        <?php endif; ?>
    });
</script>
