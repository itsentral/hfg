<?php
$is_view = (isset($mode) && $mode === 'view');
$mode_text = 'Tambah Data';
$mode_badge = 'bg-primary-subtle text-primary border-primary-subtle';
$mode_icon = 'fa-plus';

?>

<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form')); ?>
<div class="card shadow-sm border-0 mb-4" id="card-form-periodik">
    <!-- Card Header -->
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-sm text-primary me-2 rounded-2 d-flex align-items-center justify-content-center p-2" style="width: 40px; height: 40px; background-color: rgba(13, 110, 253, 0.1);">
                <i class="fa fa-calendar-check fs-5"></i>
            </div>
            <div>
                <h5 class="card-title mb-0 fw-bold text-dark">Master Pembayaran Periodik</h5>
                <small class="text-muted">Form pengisian post pengeluaran dan alokasi COA anggaran periodik</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-2" onclick="close_form()">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Departement Card -->
        <div class="card border-0 bg-light p-3 mb-4 rounded-3 shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label for="departement" class="form-label fw-bold text-dark">
                        <i class="fa fa-university text-primary me-1"></i> Departemen <span class="text-danger">*</span>
                    </label>
                    <?php if ($is_view) : ?>
                        <?php
                        $cur_dept = isset($departement) ? $departement : '0';
                        $dept_label = isset($datdept[$cur_dept]) ? $datdept[$cur_dept] : (isset($all_dept[$cur_dept]) ? $all_dept[$cur_dept] : 'Dept #' . $cur_dept);
                        ?>
                        <input type="text" class="form-control bg-white fw-bold text-dark" value="<?= strtoupper(htmlspecialchars($dept_label)) ?>" readonly>
                        <input type="hidden" name="departement" id="departement" value="<?= $cur_dept ?>">
                    <?php else : ?>
                        <?= form_dropdown('departement', $datdept, set_value('departement', isset($departement) ? $departement : '0'), array('id' => 'departement', 'class' => 'form-select select2', 'required' => 'required')); ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <small class="text-muted d-block">Penetapan anggaran rutin berlaku untuk seluruh periode tahun berjalan</small>
                </div>
            </div>
        </div>

        <!-- Detail Table Card -->
        <div class="card border mb-3 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <i class="fa fa-list text-primary me-2"></i>
                    <h6 class="mb-0 fw-bold text-dark">Rincian Post Pengeluaran & Anggaran</h6>
                </div>
                <?php if (!$is_view) : ?>
                    <button type="button" class="btn btn-primary btn-sm rounded-2 shadow-sm" onclick="add_detail(0)" id="add-material">
                        <i class="fa fa-plus me-1"></i> Tambah Baris Post
                    </button>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-detail-periodik">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">#</th>
                            <th width="260">Post Pengeluaran (COA) <span class="text-danger">*</span></th>
                            <th style="min-width: 320px;">Nama Barang / Jasa <span class="text-danger">*</span></th>
                            <th style="min-width: 125px;">Periode</th>
                            <th style="min-width: 125px;">Jadwal Bayar</th>
                            <th style="min-width: 240px;" class="text-end">Perkiraan Biaya <span class="text-danger">*</span></th>
                            <th width="180">Keterangan</th>
                            <?php if (!$is_view) : ?>
                                <th width="90" class="text-center">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="detail_body">
                        <?php
                        $total = 0;
                        $idd = 1;
                        $idalokasi = 1;
                        $all_coa = "";
                        if (!empty($callcoa)) {
                            foreach ($callcoa as $key => $val) {
                                $all_coa .= "<option value='" . $key . "'>" . htmlspecialchars($val) . "</option>";
                            }
                        }

                        $combowaktu = "<option value=''>Pilih Periode</option>";
                        $combocoa = "";
                        $combotgl = "<option value=''>Pilih Tanggal</option>";
                        if (!empty($datcoa)) {
                            foreach ($datcoa as $key => $val) {
                                $combocoa .= "<option value='" . $key . "'>" . htmlspecialchars($val) . "</option>";
                            }
                        }
                        if (!empty($waktu)) {
                            foreach ($waktu as $datawaktu) {
                                $combowaktu .= "<option value='" . $datawaktu . "'>" . ucfirst($datawaktu) . "</option>";
                            }
                        }
                        for ($tgl = 1; $tgl <= 31; $tgl++) {
                            $combotgl .= "<option value='" . $tgl . "'>Tgl " . $tgl . "</option>";
                        }

                        if (!empty($data_detail)) {
                            foreach ($data_detail as $record) {
                                $total += (float)$record->nilai;
                        ?>
                                <tr id='tr1_<?= $idd ?>' class='delAll'>
                                    <td class="text-center fw-semibold text-muted">
                                        <input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $record->id; ?>">
                                        <input type='hidden' name='kode_id[]' id='kode_id_<?= $idd ?>' value='<?= $record->kode_id; ?>'>
                                        <?= $idd; ?>
                                    </td>
                                    <td>
                                        <?php if ($is_view) : ?>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-secondary-subtle text-dark border px-2 py-1 font-monospace align-self-start mb-1"><?= htmlspecialchars($record->coa) ?></span>
                                                <span class="small fw-semibold text-dark"><?= isset($datcoa[$record->coa]) ? htmlspecialchars($datcoa[$record->coa]) : htmlspecialchars($record->nama_perkiraan) ?></span>
                                            </div>
                                        <?php else : ?>
                                            <select class="form-select select2" style="width:100%" name="coa[]" id="coa_<?= $idd; ?>" required>
                                                <?php
                                                if ($record->coa != '') {
                                                    echo str_ireplace("value='" . $record->coa . "'", "value='" . $record->coa . "' selected", $combocoa);
                                                } else {
                                                    echo $combocoa;
                                                }
                                                ?>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($is_view) : ?>
                                            <span class="text-dark fw-medium"><?= htmlspecialchars($record->nama); ?></span>
                                        <?php else : ?>
                                            <textarea class="form-control" name="nama[]" id="nama_<?= $idd; ?>" rows="2" style="min-height:55px;font-size:13px;resize:vertical;" placeholder="Nama barang atau jasa"><?= htmlspecialchars($record->nama); ?></textarea>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($is_view) : ?>
                                            <span class="badge bg-secondary-subtle text-dark border"><?= ucfirst($record->tipe) ?></span>
                                        <?php else : ?>
                                            <select class="form-select" name="tipe[]" id="tipe_<?= $idd; ?>" onchange="cektipe(<?= $idd; ?>)">
                                                <?php
                                                if ($record->tipe != '') {
                                                    echo str_ireplace("value='" . $record->tipe . "'", "value='" . $record->tipe . "' selected", $combowaktu);
                                                } else {
                                                    echo $combowaktu;
                                                }
                                                ?>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($is_view) : ?>
                                            <span class="text-secondary small fw-bold">
                                                <?= ($record->tipe == 'bulan') ? 'Tgl ' . $record->tanggal : 'MM-DD: ' . $record->tanggal ?>
                                            </span>
                                        <?php else : ?>
                                            <input type="text" class="form-control tanggal <?= ($record->tipe == 'bulan' ? 'd-none' : ''); ?>" name="thn[]" id="thn_<?= $idd; ?>" value="<?= $record->tanggal; ?>" readonly style="background:#fff;cursor: pointer;" placeholder="Bulan-Tgl">
                                            <select class="form-select <?= ($record->tipe == 'bulan' ? '' : 'd-none'); ?>" name="bln[]" id="bln_<?= $idd; ?>">
                                                <?php
                                                for ($tgl = 1; $tgl <= 31; $tgl++) {
                                                    $selected = ($record->tanggal == $tgl) ? 'selected' : '';
                                                    echo "<option value='" . $tgl . "' " . $selected . ">Tgl " . $tgl . "</option>";
                                                }
                                                ?>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($is_view) : ?>
                                            <span class="fw-bold text-dark font-monospace">Rp <?= number_format($record->nilai, 0, ',', '.') ?></span>
                                        <?php else : ?>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light small">Rp</span>
                                                <input type="text" class="form-control biaya-input divide text-end fw-semibold font-monospace" name="nilai[]" id="nilai_<?= $idd; ?>" value="<?= (int)$record->nilai; ?>" placeholder="0">
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($is_view) : ?>
                                            <span class="text-muted small"><?= !empty($record->keterangan) ? htmlspecialchars($record->keterangan) : '-' ?></span>
                                        <?php else : ?>
                                            <input type="text" class="form-control" name="keterangan[]" id="keterangan_<?= $idd; ?>" value="<?= htmlspecialchars($record->keterangan); ?>" placeholder="Keterangan">
                                        <?php endif; ?>
                                    </td>
                                    <?php if (!$is_view) : ?>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type='button' class='btn btn-outline-info btn-xs' onClick='add_alokasi(<?= $idd ?>)' title='Tambah alokasi COA'><i class='fa fa-cubes'></i></button>
                                                <button type='button' class='btn btn-outline-danger btn-xs' onClick='delDetail(this, <?= $idd ?>)' title='Hapus baris ini'><i class='fa fa-trash'></i></button>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php
                                if ($record->kode_id != '') {
                                    $dtalok = $this->db->query("select * from ms_budget_rutin_alokasi where kode='" . $record->kode_id . "'")->result();
                                    if (!empty($dtalok)) {
                                        foreach ($dtalok as $fields) { ?>
                                            <tr id='tr1_dtl<?= $idd . "_" . $idalokasi ?>' class='delAll rows<?= $record->kode_id ?> bg-light'>
                                                <td colspan="2" class="text-end text-muted small pe-3">
                                                    <i class="fa fa-level-up fa-rotate-90 text-primary me-1"></i> <span class="badge bg-secondary-subtle text-dark">Alokasi COA:</span>
                                                    <input type='hidden' name='kode_detail[]' id='kode_detail_<?= $idalokasi ?>' value='<?= $record->kode_id ?>'>
                                                    <input type='hidden' name='detail_alokasi[]' id='detail_alokasi_<?= $idalokasi ?>' value='<?= $fields->id ?>'>
                                                </td>
                                                <td colspan="3">
                                                    <?php if ($is_view) : ?>
                                                        <span class="badge bg-primary-subtle text-primary border font-monospace"><?= htmlspecialchars($fields->coa) ?></span>
                                                    <?php else : ?>
                                                        <select class='form-select select2' style="width:100%" name='coa_alokasi[]' id='coa_alokasi_<?= $idalokasi ?>'>
                                                            <?php
                                                            if ($fields->coa != '') {
                                                                echo str_ireplace("value='" . $fields->coa . "'", "value='" . $fields->coa . "' selected", $all_coa);
                                                            } else {
                                                                echo $all_coa;
                                                            }
                                                            ?>
                                                        </select>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type='text' class='form-control divide text-end fw-semibold font-monospace' name='nilai_alokasi[]' value='<?= $fields->nilai ?>' id='nilai_alokasi_<?= $idalokasi ?>' <?= ($is_view ? 'readonly' : '') ?> />
                                                        <span class="input-group-text bg-light small">%</span>
                                                    </div>
                                                </td>
                                                <td class="text-center" colspan="<?= ($is_view ? '1' : '2') ?>">
                                                    <?php if (!$is_view) : ?>
                                                        <button type='button' class='btn btn-outline-danger btn-xs' onClick="delDetailalokasi(this)" title='Hapus alokasi'><i class='fa fa-times me-1'></i> Hapus</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                        <?php
                                            $idalokasi++;
                                        }
                                    }
                                }
                                $idd++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Total Budget Summary Card -->
        <div class="card bg-success-subtle border-success-subtle p-3 mb-4 rounded-3 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 text-success">
                    <i class="fa fa-calculator fs-4"></i>
                    <div>
                        <div class="fw-bold fs-6">Total Estimasi Perkiraan Biaya</div>
                        <small class="text-muted">Akumulasi estimasi seluruh post pengeluaran periodik di atas</small>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-success font-monospace" id="total-perkiraan-display">
                    Rp <?= number_format($total, 0, ',', '.') ?>
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button type="button" class="btn btn-outline-secondary px-3" onclick="close_form()">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
            </button>
            <?php if (!$is_view) : ?>
                <button type="submit" name="save" class="btn btn-primary px-4 shadow-sm" id="submit">
                    <i class="fa fa-save me-1"></i> Simpan Data Budget
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= form_close() ?>

<!-- Hidden option templates for dynamic row creation (avoids inline JS string escape issues) -->
<select id="tpl-coa-options" class="d-none">
    <?= $combocoa ?>
</select>
<select id="tpl-all-coa-options" class="d-none">
    <?= $all_coa ?>
</select>
<select id="tpl-waktu-options" class="d-none">
    <?= $combowaktu ?>
</select>
<select id="tpl-tgl-options" class="d-none">
    <?= $combotgl ?>
</select>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>

<script type="text/javascript">
    var url_save = siteurl + 'budget_periodik/save_data/';
    var nomor = <?= $idd ?>;
    var nomor_detail = <?= $idalokasi ?>;

    $(function() {
        $(".select2").select2({
            width: '100%'
        });
        $(".tanggal").datepicker({
            todayHighlight: true,
            format: "mm-dd",
            showInputs: true,
            autoclose: true
        });
        $('.divide').divide();
        calculateTotal();
    });

    var is_view = <?= $is_view ? 'true' : 'false' ?>;

    function calculateTotal() {
        if (is_view) return;
        var tot = 0;
        $('.biaya-input').each(function() {
            var val = $(this).val();
            if (val === undefined || val === '') {
                val = $(this).parent().find('input[type="hidden"]').val();
            }
            if (val) {
                var cleaned = val.toString().replace(/[^0-9]/g, '');
                var num = parseInt(cleaned, 10);
                if (!isNaN(num) && num > 0) {
                    tot += num;
                }
            }
        });
        $('#total-perkiraan-display').text('Rp ' + tot.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
    }

    $(document).on('input keyup change paste', '.biaya-input', calculateTotal);

    function uniqId() {
        return Math.round(new Date().getTime() + (Math.random() * 100));
    }

    function add_alokasi(id) {
        nomor_detail++;
        var kode_id = $("#kode_id_" + id).val();
        if (kode_id == '') {
            kode_id = uniqId();
            $("#kode_id_" + id).val(kode_id);
        }
        var optAlok = $('#tpl-all-coa-options').html();

        var Rows = '<tr id="tr1_dtl' + nomor + '_' + nomor_detail + '" class="delAll rows' + kode_id + ' bg-light">' +
            '<td colspan="2" class="text-end text-muted small pe-3">' +
            '<i class="fa fa-level-up fa-rotate-90 text-primary me-1"></i> <span class="badge bg-secondary-subtle text-dark">Alokasi COA:</span>' +
            '<input type="hidden" name="kode_detail[]" id="kode_detail_' + nomor_detail + '" value="' + kode_id + '">' +
            '<input type="hidden" name="detail_alokasi[]" id="detail_alokasi_' + nomor_detail + '" value="">' +
            '</td>' +
            '<td colspan="3">' +
            '<select class="form-select select2" style="width:100%" name="coa_alokasi[]" id="coa_alokasi_' + nomor_detail + '">' +
            optAlok +
            '</select>' +
            '</td>' +
            '<td>' +
            '<div class="input-group">' +
            '<input type="text" class="form-control divide text-end fw-semibold font-monospace" name="nilai_alokasi[]" value="0" id="nilai_alokasi_' + nomor + '" />' +
            '<span class="input-group-text bg-light small">%</span>' +
            '</div>' +
            '</td>' +
            '<td align="center" colspan="2">' +
            '<button type="button" class="btn btn-outline-danger btn-xs" onClick="delDetailalokasi(this)" title="Hapus alokasi"><i class="fa fa-times me-1"></i> Hapus</button>' +
            '</td>' +
            '</tr>';

        $('#tr1_' + id).after(Rows);
        $(".divide").divide();
        $(".select2").select2({
            width: '100%'
        });
    }

    function add_detail(id) {
        var optCoa = $('#tpl-coa-options').html();
        var optWaktu = $('#tpl-waktu-options').html();
        var optTgl = $('#tpl-tgl-options').html();

        var Rows = '<tr id="tr1_' + nomor + '" class="delAll">' +
            '<td class="text-center fw-semibold text-muted">' +
            '<input type="hidden" name="detail_id[]" id="raw_id_' + nomor + '" value="">' +
            '<input type="hidden" name="kode_id[]" id="kode_id_' + nomor + '" value="">' +
            nomor +
            '</td>' +
            '<td>' +
            '<select class="form-select select2" style="width:100%" name="coa[]" id="coa_' + nomor + '" required>' +
            optCoa +
            '</select>' +
            '</td>' +
            '<td>' +
            '<textarea class="form-control" name="nama[]" id="nama_' + nomor + '" rows="2" style="min-height:55px;font-size:13px;resize:vertical;" placeholder="Nama barang atau jasa"></textarea>' +
            '</td>' +
            '<td>' +
            '<select class="form-select" name="tipe[]" id="tipe_' + nomor + '" onchange="cektipe(' + nomor + ')">' +
            optWaktu +
            '</select>' +
            '</td>' +
            '<td>' +
            '<input type="text" class="form-control tanggal d-none" name="thn[]" value="" id="thn_' + nomor + '" readonly style="background:#fff;cursor: pointer;" placeholder="Bulan-Tgl" />' +
            '<select class="form-select" name="bln[]" id="bln_' + nomor + '">' +
            optTgl +
            '</select>' +
            '</td>' +
            '<td class="text-end">' +
            '<div class="input-group">' +
            '<span class="input-group-text bg-light small">Rp</span>' +
            '<input type="text" class="form-control biaya-input divide text-end fw-semibold font-monospace" name="nilai[]" value="0" id="nilai_' + nomor + '" />' +
            '</div>' +
            '</td>' +
            '<td>' +
            '<input type="text" class="form-control" name="keterangan[]" id="keterangan_' + nomor + '" placeholder="Keterangan" />' +
            '</td>' +
            '<td class="text-center">' +
            '<div class="btn-group btn-group-sm" role="group">' +
            '<button type="button" class="btn btn-outline-info btn-xs" onClick="add_alokasi(' + nomor + ')" title="Tambah alokasi COA"><i class="fa fa-cubes"></i></button>' +
            '<button type="button" class="btn btn-outline-danger btn-xs" onClick="delDetail(this, ' + nomor + ')" title="Hapus baris"><i class="fa fa-trash"></i></button>' +
            '</div>' +
            '</td>' +
            '</tr>';

        if (id == 0) {
            $('#detail_body').append(Rows);
        } else {
            $('#tr1_' + id).after(Rows);
            var combodata = $("#coa_" + id).val();
            $("#coa_" + nomor).val(combodata);
        }
        nomor++;
        $(".tanggal").datepicker({
            todayHighlight: true,
            format: "mm-dd",
            showInputs: true,
            autoclose: true
        });
        $(".divide").divide();
        $(".select2").select2({
            width: '100%'
        });
        calculateTotal();
    }

    function cektipe(nomor) {
        var keys = $("#tipe_" + nomor).val();
        if (keys == "bulan") {
            $("#thn_" + nomor).addClass("d-none");
            $("#bln_" + nomor).removeClass("d-none");
        } else {
            $("#bln_" + nomor).addClass("d-none");
            $("#thn_" + nomor).removeClass("d-none");
        }
    }

    function delDetailalokasi(el) {
        if (typeof el === 'string') {
            $('#tr1_dtl' + el).remove();
        } else {
            $(el).closest('tr').remove();
        }
    }

    function delDetail(el, row) {
        var $tr = (typeof el === 'object') ? $(el).closest('tr') : $('#tr1_' + el);
        var kode_id = $tr.find('input[name="kode_id[]"]').val();
        if (kode_id && kode_id != '') {
            $('.rows' + kode_id).remove();
        }
        $tr.remove();
        calculateTotal();
    }

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();

        var dept = $('#departement').val();
        if (!dept || dept == '0') {
            Swal.fire({
                title: 'Perhatian',
                text: 'Silakan pilih departemen terlebih dahulu',
                icon: 'warning'
            });
            return false;
        }

        var rowCount = $('#detail_body tr.delAll:not([id*="dtl"])').length;
        if (rowCount < 1) {
            Swal.fire({
                title: 'Perhatian',
                text: 'Rincian anggaran minimal harus ada 1 baris',
                icon: 'warning'
            });
            return false;
        }

        Swal.fire({
            title: 'Simpan Anggaran?',
            text: 'Apakah Anda yakin ingin menyimpan rincian anggaran periodik ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-save me-1"></i> Ya, Simpan',
            cancelButtonText: '<i class="fa fa-times me-1"></i> Batal',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                var formdata = $("#frm_data").serialize();
                $.ajax({
                    url: url_save,
                    dataType: "json",
                    type: 'POST',
                    data: formdata,
                    success: function(msg) {
                        if (msg['save'] == 1 || msg['save'] == true) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: msg['msg'] || 'Data berhasil disimpan',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: msg['msg'] || 'Data gagal disimpan',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal menghubungi server',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
</script>