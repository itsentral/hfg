<?php
$id = isset($data->id) ? $data->id : '';
$no_doc = isset($data->no_doc) ? $data->no_doc : '';
$tanggal_doc = isset($data->tanggal_doc) ? $data->tanggal_doc : date('Y-m-d');
$departement_id = isset($data->departement) ? $data->departement : '';
$dept_name = isset($datdept[$departement_id]) ? $datdept[$departement_id] : $departement_id;
$is_view_mode = (isset($type) && $type == 'view');
$is_approve_mode = (isset($app) && $app == 'app');

$status_label = 'Menunggu Approval';
$status_badge = 'bg-warning text-dark';
if (isset($data->status)) {
    if ($data->status == 1) {
        $status_label = 'Approved';
        $status_badge = 'bg-success text-white';
    } elseif ($data->status == 2) {
        $status_label = 'Diproses Finance';
        $status_badge = 'bg-info text-dark';
    } elseif ($data->sts_reject == 1) {
        $status_label = 'Ditolak';
        $status_badge = 'bg-danger text-white';
    }
}
?>

<?php if ($is_view_mode): ?>
<!-- ================= MODERN BOOTSTRAP 5 VIEW & APPROVE MODE ================= -->
<div class="container-fluid p-0">
    <!-- Header Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge <?= $status_badge ?> px-3 py-2 rounded-pill fw-medium">
                            <i class="fa fa-info-circle me-1"></i><?= $status_label ?>
                        </span>
                        <?php if ($is_approve_mode): ?>
                            <span class="badge bg-light text-primary border px-3 py-2 rounded-pill fw-medium">
                                <i class="fa fa-check-circle me-1"></i>Mode Verifikasi Approval
                            </span>
                        <?php else: ?>
                            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-medium">
                                <i class="fa fa-eye me-1"></i>Mode Detail (View)
                            </span>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="fa fa-calendar-check text-primary me-2"></i>
                        <?= $is_approve_mode ? 'Verifikasi Approval Pembayaran Periodik (Rutin)' : 'Detail Pengajuan Pembayaran Periodik (Rutin)' ?>
                        <?php if (!empty($no_doc)): ?>
                            <span class="text-primary fs-5">#<?= htmlspecialchars($no_doc) ?></span>
                        <?php endif; ?>
                    </h4>
                    <small class="text-muted">
                        Departemen: <span class="fw-semibold text-dark"><?= strtoupper(htmlspecialchars($dept_name)) ?></span>
                    </small>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a class="btn btn-outline-secondary btn-sm px-3" onclick="if(typeof list_view === 'function'){ list_view(); } else { location.reload(); } return false;">
                        <i class="fa fa-arrow-left me-1"></i> Kembali ke List
                    </a>

                    <?php if (isset($data->status) && $data->status == 0 && $is_approve_mode): ?>
                        <button type="button" class="btn btn-success btn-sm px-3" onclick="data_approve(<?= $id ?>)">
                            <i class="fa fa-check me-1"></i> Setujui (Approve)
                        </button>
                        <button type="button" class="btn btn-danger btn-sm px-3" onclick="data_reject(<?= $id ?>)">
                            <i class="fa fa-ban me-1"></i> Tolak (Reject)
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Meta Info Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">No. Dokumen</label>
                    <div class="fw-bold fs-6 text-primary"><?= htmlspecialchars($no_doc) ?></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Departemen</label>
                    <div class="fw-semibold fs-6 text-dark"><?= strtoupper(htmlspecialchars($dept_name)) ?></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Tanggal Dokumen</label>
                    <div class="fw-semibold fs-6 text-dark"><?= date('d F Y', strtotime($tanggal_doc)) ?></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Status Pengajuan</label>
                    <div><span class="badge <?= $status_badge ?>"><?= $status_label ?></span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Detail Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="fa fa-list-alt text-primary me-2"></i>Rincian Pembayaran Periodik
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">#</th>
                            <th>Deskripsi Pembayaran</th>
                            <th class="text-center">Jadwal</th>
                            <th class="text-end">Budget (Rp)</th>
                            <th class="text-end">Perkiraan Biaya (Rp)</th>
                            <th>Keterangan</th>
                            <th>Bank & Rekening Penerima</th>
                            <th class="text-center">Dokumen Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        $idd = 1;
                        $lampiran_list = [];
                        if (!empty($data_detail)) {
                            foreach ($data_detail as $record) {
                                $total += (float)$record->nilai;
                                if (!empty($record->doc_file)) {
                                    $lampiran_list[] = [
                                        'file' => $record->doc_file,
                                        'nama' => $record->nama,
                                        'no_doc' => $record->no_doc
                                    ];
                                }
                        ?>
                                <tr>
                                    <td class="text-center text-muted fw-medium"><?= $idd++ ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($record->nama) ?></div>
                                        <?php if (!empty($record->coa)): ?>
                                            <span class="badge bg-light text-secondary border small">COA: <?= htmlspecialchars($record->coa) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">
                                            <i class="fa fa-calendar me-1 text-primary"></i><?= $record->tanggal ?>
                                        </span>
                                    </td>
                                    <td class="text-end font-monospace text-secondary">
                                        Rp <?= number_format($record->budget, 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-success fs-6">
                                        Rp <?= number_format($record->nilai, 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <span class="small text-muted"><?= htmlspecialchars($record->keterangan ?: '-') ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($record->bank_id) || !empty($record->accnumber) || !empty($record->accname)): ?>
                                            <div class="small">
                                                <span class="badge bg-light-primary text-primary fw-semibold"><?= htmlspecialchars($record->bank_id ?: 'BANK') ?></span>
                                                <span class="fw-medium text-dark ms-1"><?= htmlspecialchars($record->accnumber ?: '-') ?></span>
                                                <div class="text-muted small">a/n <?= htmlspecialchars($record->accname ?: '-') ?></div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($record->doc_file)): ?>
                                            <a href="<?= base_url('assets/bayar_rutin/' . $record->doc_file) ?>" target="_blank" class="btn btn-outline-primary btn-sm py-1 px-2" title="Unduh / Lihat Dokumen">
                                                <i class="fa fa-file-text me-1"></i> Buka File
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Tidak ada rincian data pembayaran.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end py-3 text-uppercase text-secondary">TOTAL PERKIRAAN BIAYA :</td>
                            <td class="text-end py-3 text-success font-monospace fs-5">Rp <?= number_format($total, 0, ',', '.') ?></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Lampiran Preview Section -->
    <?php if (!empty($lampiran_list)): ?>
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fa fa-paperclip text-primary me-2"></i>Lampiran Dokumen Bukti (<?= count($lampiran_list) ?>)
                </h5>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <?php foreach ($lampiran_list as $lamp): ?>
                        <?php
                        $ext = strtolower(pathinfo($lamp['file'], PATHINFO_EXTENSION));
                        $is_pdf = ($ext == 'pdf');
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border rounded-3 p-3 bg-light">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fa <?= $is_pdf ? 'fa-file-pdf text-danger' : 'fa-file-image text-primary' ?> fs-4 me-2"></i>
                                    <div class="text-truncate">
                                        <div class="fw-semibold text-dark text-truncate" title="<?= htmlspecialchars($lamp['nama']) ?>">
                                            <?= htmlspecialchars($lamp['nama']) ?>
                                        </div>
                                        <small class="text-muted"><?= htmlspecialchars($lamp['file']) ?></small>
                                    </div>
                                </div>
                                <?php if (!$is_pdf): ?>
                                    <div class="text-center my-2">
                                        <a href="<?= base_url('assets/bayar_rutin/' . $lamp['file']) ?>" target="_blank">
                                            <img src="<?= base_url('assets/bayar_rutin/' . $lamp['file']) ?>" class="img-fluid rounded border" style="max-height: 180px; object-fit: cover;">
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <div class="mt-auto pt-2 text-end">
                                    <a href="<?= base_url('assets/bayar_rutin/' . $lamp['file']) ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="fa fa-external-link-alt me-1"></i> Buka / Unduh Lampiran
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer Action Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 d-flex flex-wrap justify-content-end align-items-center gap-2">
            <a class="btn btn-outline-secondary btn-sm px-3" onclick="if(typeof list_view === 'function'){ list_view(); } else { location.reload(); } return false;">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke List
            </a>

            <?php if (isset($data->status) && $data->status == 0 && $is_approve_mode): ?>
                <button type="button" class="btn btn-success btn-sm px-3" onclick="data_approve(<?= $id ?>)">
                    <i class="fa fa-check me-1"></i> Setujui (Approve)
                </button>
                <button type="button" class="btn btn-danger btn-sm px-3" onclick="data_reject(<?= $id ?>)">
                    <i class="fa fa-ban me-1"></i> Tolak (Reject)
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    var url_approve = siteurl + 'pengajuan_rutin/approve/';
    var url_reject  = siteurl + 'pengajuan_rutin/reject/';

    function data_approve(id_param) {
        var id = id_param || $("#id").val();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: "Setujui Pengajuan?",
                text: "Data pengajuan periodik ini akan disetujui!",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya, Setujui!",
                cancelButtonText: "Batal",
                confirmButtonColor: "#28a745"
            }).then((res) => {
                if (res.isConfirmed) {
                    $.post(url_approve + id, function(msg) {
                        if (msg.save == '1' || msg.save == 1 || msg.save === true) {
                            Swal.fire({
                                icon: "success",
                                title: "Sukses!",
                                text: "Data Berhasil Disetujui",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                if (typeof list_view === 'function') {
                                    list_view();
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Swal.fire("Gagal!", "Data Gagal Disetujui", "error");
                        }
                    }, "json");
                }
            });
        } else {
            if (confirm("Setujui pengajuan ini?")) {
                $.post(url_approve + id, function(msg) {
                    if (msg.save == '1' || msg.save == 1) {
                        alert("Data Berhasil Disetujui");
                        if (typeof list_view === 'function') {
                            list_view();
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert("Data Gagal Disetujui");
                    }
                }, "json");
            }
        }
    }

    function data_reject(id_param) {
        var id = id_param || $("#id").val();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: "Tolak Pengajuan Periodik",
                input: "textarea",
                inputLabel: "Alasan Penolakan (Reject Reason)",
                inputPlaceholder: "Tuliskan alasan penolakan di sini...",
                showCancelButton: true,
                confirmButtonText: "Tolak Dokumen",
                cancelButtonText: "Batal",
                confirmButtonColor: "#dc3545",
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return "Alasan penolakan wajib diisi!";
                    }
                }
            }).then((res) => {
                if (res.isConfirmed) {
                    $.post(url_reject + id, {
                        'reject_reason': res.value
                    }, function(msg) {
                        if (msg.save == '1' || msg.save == 1 || msg.save === true) {
                            Swal.fire({
                                icon: "info",
                                title: "Ditolak!",
                                text: "Data Berhasil Ditolak",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                if (typeof list_view === 'function') {
                                    list_view();
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Swal.fire("Gagal!", "Data Gagal Ditolak", "error");
                        }
                    }, "json");
                }
            });
        } else {
            var reason = prompt("Alasan penolakan:");
            if (reason) {
                $.post(url_reject + id, {
                    'reject_reason': reason
                }, function(msg) {
                    alert("Data Berhasil Ditolak");
                    if (typeof list_view === 'function') {
                        list_view();
                    } else {
                        window.location.reload();
                    }
                }, "json");
            }
        }
    }
</script>

<?php else: ?>
<!-- ================= MODERN BOOTSTRAP 5 EDIT & ADD MODE ================= -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'needs-validation', 'enctype' => 'multipart/form-data')); ?>
<input type="hidden" name="id" id="id" value="<?= (isset($data->id) ? $data->id : ''); ?>">

<div class="container-fluid p-0">
    <!-- Header Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="fa fa-calendar-plus fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge <?= ($type == 'edit') ? 'bg-warning text-dark' : 'bg-primary text-white'; ?> px-3 py-1 rounded-pill fw-medium">
                                <?= ($type == 'edit') ? '<i class="fa fa-edit me-1"></i>Mode Edit Pengajuan' : '<i class="fa fa-plus-circle me-1"></i>Mode Tambah Baru'; ?>
                            </span>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">
                            <?= ($type == 'edit') ? 'Edit Pengajuan Pembayaran Periodik' : 'Input Pengajuan Pembayaran Periodik'; ?>
                            <?php if (!empty($no_doc)): ?><span class="text-primary ms-1">#<?= htmlspecialchars($no_doc); ?></span><?php endif; ?>
                        </h4>
                        <small class="text-muted">Departemen: <span class="fw-semibold text-dark"><?= strtoupper(htmlspecialchars($dept_name)); ?></span></small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 shadow-sm" onclick="if(typeof list_view === 'function'){ list_view(); } else { location.reload(); } return false;">
                        <i class="fa fa-arrow-left me-1"></i> Kembali ke List
                    </button>
                    <button type="submit" name="save" class="btn btn-success btn-sm px-4 shadow-sm fw-bold" id="submit">
                        <i class="fa fa-save me-1"></i> Simpan Pengajuan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Meta Info Form Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="fa fa-circle-info me-1"></i> Informasi Dokumen Pengajuan
            </h6>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <div class="col-md-4 col-12">
                    <label class="form-label fw-bold small text-muted">Departemen <span class="text-danger">*</span></label>
                    <?= form_dropdown('departement', $datdept, set_value('departement', isset($data->departement) ? $data->departement : (isset($departement) ? $departement : '0')), array('id' => 'departement', 'class' => 'form-select form-select-sm', 'required' => 'required')); ?>
                </div>

                <div class="col-md-4 col-12">
                    <label class="form-label fw-bold small text-muted">No. Dokumen</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-semibold text-primary" id="no_doc" name="no_doc" value="<?= htmlspecialchars($no_doc); ?>" placeholder="Auto Generate" readonly>
                </div>

                <div class="col-md-4 col-12">
                    <label class="form-label fw-bold small text-muted">Tanggal Dokumen <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-sm flatpickr-date bg-white" id="tanggal_doc" name="tanggal_doc" value="<?= htmlspecialchars($tanggal_doc); ?>" placeholder="Pilih tanggal..." required readonly>
                        <span class="input-group-text bg-white"><i class="fa fa-calendar-alt text-primary"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Items Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-primary">
                    <i class="fa fa-list-check me-1"></i> Rincian Item Pembayaran Periodik
                </h6>
                <small class="text-muted">Masukkan nilai perkiraan biaya dan informasi rekening tujuan pembayaran</small>
            </div>
            <div>
                <button class="btn btn-outline-primary btn-sm px-3" onclick="add_detail()" id="add-material" type="button">
                    <i class="fa fa-arrows-rotate me-1"></i> Muat Ulang Detail Budget
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover align-middle mb-0" id="table-detail-biaya" style="min-width: 1050px;">
                    <thead class="table-light text-nowrap text-center">
                        <tr>
                            <th width="40">#</th>
                            <th style="min-width: 220px;">Deskripsi Biaya</th>
                            <th width="140">Jadwal Pembayaran</th>
                            <th width="140" class="text-end">Budget</th>
                            <th width="160" class="text-end">Perkiraan Biaya <span class="text-danger">*</span></th>
                            <th style="min-width: 180px;">Keterangan</th>
                            <th width="160">Dokumen Lampiran</th>
                            <th style="min-width: 240px;">Rekening Tujuan (Bank / No Rek / Nama)</th>
                        </tr>
                    </thead>
                    <tbody id="detail_body">
                        <?php
                        $total = 0;
                        $idd = 1;
                        $gambar = '';
                        if (!empty($data_detail)) :
                            foreach ($data_detail as $record) :
                                $total += (float)$record->nilai;
                        ?>
                                <tr id="tr1_<?= $idd; ?>" class="delAll">
                                    <td class="text-center">
                                        <input type="hidden" name="details[]" id="details_id_<?= $idd; ?>" value="<?= $idd; ?>">
                                        <input type="hidden" name="detail_id[]" id="raw_id_<?= $idd; ?>" value="<?= $record->id; ?>">
                                        <input type="hidden" name="id_budget[]" id="id_budget_<?= $idd; ?>" value="<?= $record->id_budget; ?>" class="budget">
                                        <input type="hidden" name="coa[]" id="coa_<?= $idd; ?>" value="<?= $record->coa; ?>">
                                        <span class="fw-semibold text-muted"><?= $idd; ?></span>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="nama[]" id="nama_<?= $idd; ?>" value="<?= htmlspecialchars($record->nama); ?>">
                                    </td>
                                    <td class="text-center">
                                        <input type="text" class="form-control form-control-sm bg-light text-center" name="tanggal[]" id="tanggal_<?= $idd; ?>" value="<?= $record->tanggal; ?>" readonly>
                                    </td>
                                    <td class="text-end">
                                        <input type="text" class="form-control form-control-sm divide bg-light text-end" name="budget[]" id="budget<?= $idd; ?>" value="<?= ($record->budget); ?>" readonly tabindex="-1">
                                    </td>
                                    <td class="text-end">
                                        <input type="text" class="form-control form-control-sm nilai divide text-end fw-bold text-primary" name="nilai[]" id="nilai_<?= $idd; ?>" value="<?= ($record->nilai); ?>" data-no="<?= $idd; ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="keterangan[]" id="keterangan_<?= $idd; ?>" value="<?= htmlspecialchars($record->keterangan); ?>" placeholder="Catatan...">
                                    </td>
                                    <td>
                                        <input type="file" class="form-control form-control-sm mb-1" name="doc_file_<?= $idd; ?>" id="doc_file<?= $idd; ?>">
                                        <?php if (!empty($record->doc_file)) : ?>
                                            <a href="<?= base_url('assets/bayar_rutin/' . $record->doc_file); ?>" download target="_blank" class="badge bg-light text-primary border text-decoration-none">
                                                <i class="fa fa-download me-1"></i>Unduh Lampiran
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control form-control-sm" name="bank_id[]" id="bank_id_<?= $idd; ?>" value="<?= htmlspecialchars($record->bank_id); ?>" placeholder="Nama Bank">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="list_bank(<?= $idd; ?>)" title="Pilih dari daftar bank">
                                                    <i class="fa fa-list"></i>
                                                </button>
                                            </div>
                                            <input type="text" class="form-control form-control-sm" name="accnumber[]" id="accnumber_<?= $idd; ?>" value="<?= htmlspecialchars($record->accnumber); ?>" placeholder="No. Rekening">
                                            <input type="text" class="form-control form-control-sm" name="accname[]" id="accname_<?= $idd; ?>" value="<?= htmlspecialchars($record->accname); ?>" placeholder="Atas Nama">
                                        </div>
                                    </td>
                                </tr>
                            <?php
                                $idd++;
                            endforeach;
                        endif;
                        ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end py-2">TOTAL PERKIRAAN BIAYA:</td>
                            <td class="text-end py-2">
                                <span id="total_nilai_display" class="fw-bold text-success fs-6">
                                    Rp <?= number_format($total); ?>
                                </span>
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="if(typeof list_view === 'function'){ list_view(); } else { location.reload(); } return false;">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke List
            </button>
            <button type="submit" name="save" class="btn btn-success btn-sm px-4 shadow-sm fw-bold" id="submit">
                <i class="fa fa-save me-1"></i> Simpan Pengajuan
            </button>
        </div>
    </div>
</div>

<!-- Modal Daftar Bank -->
<div class="modal fade" id="bankModal" tabindex="-1" aria-hidden="true" style="overflow-y: auto;">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fw-bold" id="myModalLabel"><i class="fa fa-building-columns me-1"></i> Daftar Rekening Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="bankModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Memuat data bank...</p>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= form_close(); ?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>

<script type="text/javascript">
    var url_save = siteurl + 'pengajuan_rutin/save_data/';
    var nomor = <?= $idd ?>;

    $(document).ready(function() {
        if ($('.divide').length) {
            $('.divide').divide();
        }

        // Init Flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#tanggal_doc', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd F Y',
                locale: 'id',
                allowInput: true
            });
        }

        hitung_total();
    });

    function hitung_total() {
        var total = 0;
        $(".nilai").each(function() {
            var valStr = $(this).val();
            if (valStr) {
                var cleanVal = parseFloat(valStr.toString().replace(/,/g, '')) || 0;
                total += cleanVal;
            }
        });
        $('#total_nilai_display').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
    }

    $(document).on("keyup change", ".nilai", function() {
        hitung_total();
    });

    function list_bank(id) {
        $("#bankModalBody").html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2 mb-0">Memuat data bank...</p></div>');
        $('#bankModal').modal('show');
        $.ajax({
            url: siteurl + 'all/list_bank/' + id,
            type: 'POST',
            success: function(msg) {
                $("#bankModalBody").html(msg);
                if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#mylistbank')) {
                    $("#mylistbank").DataTable();
                }
            },
            error: function() {
                $("#bankModalBody").html('<div class="alert alert-danger text-center">Gagal memuat daftar bank.</div>');
            }
        });
    }

    function pilihini(bank_id, accnumber, accname, id) {
        $("#bank_id_" + id).val(bank_id);
        $("#accnumber_" + id).val(accnumber);
        $("#accname_" + id).val(accname);
        $('#bankModal').modal('hide');
    }

    function add_detail() {
        var idbudget = [];
        var departement = $("#departement").val();
        var tanggal_doc = $("#tanggal_doc").val();
        $('.budget').each(function() {
            idbudget.push($(this).val());
        });

        $('#table-detail-biaya tbody').html('<tr><td colspan="8" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Memuat rincian budget periodik...</td></tr>');

        $.ajax({
            url: siteurl + "pengajuan_rutin/get_data",
            dataType: "json",
            type: 'POST',
            data: {
                allbudget: idbudget,
                dept: departement,
                tanggal: tanggal_doc
            },
            success: function(msg) {
                $('#table-detail-biaya tbody').empty();
                if (msg['save'] == '1') {
                    if (msg['data'] && msg['data'].length > 0) {
                        $.each(msg['data'], function(index, element) {
                            var jadwal = '';
                            if (element.tipe == 'tahun') jadwal = msg['tahun'] + '-' + element.tanggal;
                            if (element.tipe == 'bulan') jadwal = msg['tahun'] + '-' + msg['bulan'] + '-' + element.tanggal;

                            var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
                            Rows += "<td class='text-center'>";
                            Rows += "<input type='hidden' name='detail_id[]' id='details_id_" + nomor + "' value=''>";
                            Rows += "<input type='hidden' name='details[]' id='raw_id_" + nomor + "' value='" + nomor + "'>";
                            Rows += "<input type='hidden' name='id_budget[]' id='id_budget_" + nomor + "' value='" + element.id + "' class='budget'>";
                            Rows += "<input type='hidden' name='coa[]' id='coa_" + nomor + "' value='" + element.coa + "'>";
                            Rows += "<span class='fw-semibold text-muted'>" + nomor + "</span>";
                            Rows += "</td>";
                            Rows += "<td>";
                            Rows += "<input type='text' class='form-control form-control-sm' name='nama[]' id='nama_" + nomor + "' value='" + element.nama + "' />";
                            Rows += "</td>";
                            Rows += "<td class='text-center'>";
                            Rows += "<input type='text' class='form-control form-control-sm bg-light text-center' name='tanggal[]' id='tanggal_" + nomor + "' value='" + jadwal + "' readonly />";
                            Rows += "</td>";
                            Rows += "<td class='text-end'>";
                            Rows += "<input type='text' class='form-control form-control-sm divide bg-light text-end' name='budget[]' value='" + element.nilai + "' id='budget_" + nomor + "' readonly tabindex='-1'/>";
                            Rows += "</td>";
                            Rows += "<td class='text-end'>";
                            Rows += "<input type='text' class='form-control form-control-sm nilai divide text-end fw-bold text-primary' name='nilai[]' value='0' data-no='" + nomor + "' id='nilai_" + nomor + "' />";
                            Rows += "</td>";
                            Rows += "<td>";
                            Rows += "<input type='text' class='form-control form-control-sm' name='keterangan[]' id='keterangan_" + nomor + "' value='' placeholder='Catatan...' />";
                            Rows += "</td>";
                            Rows += "<td>";
                            Rows += "<input type='file' class='form-control form-control-sm' name='doc_file_" + nomor + "' id='doc_file" + nomor + "'>";
                            Rows += "</td>";
                            Rows += "<td>";
                            Rows += "<div class='d-flex flex-column gap-1'>";
                            Rows += "<div class='input-group input-group-sm'>";
                            Rows += "<input type='text' class='form-control form-control-sm' name='bank_id[]' id='bank_id_" + nomor + "' value='' placeholder='Nama Bank' />";
                            Rows += "<button type='button' class='btn btn-outline-secondary btn-sm' onclick='list_bank(" + nomor + ")'><i class='fa fa-list'></i></button>";
                            Rows += "</div>";
                            Rows += "<input type='text' class='form-control form-control-sm' name='accnumber[]' id='accnumber_" + nomor + "' value='' placeholder='No. Rekening' />";
                            Rows += "<input type='text' class='form-control form-control-sm' name='accname[]' id='accname_" + nomor + "' value='' placeholder='Atas Nama' />";
                            Rows += "</div>";
                            Rows += "</td>";
                            Rows += "</tr>";
                            nomor++;
                            $('#detail_body').append(Rows);
                        });
                        $(".divide").divide();
                        hitung_total();
                    } else {
                        $('#detail_body').html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fa fa-info-circle me-1"></i> Tidak ada rincian budget periodik yang ditemukan untuk periode ini.</td></tr>');
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'Perhatian!', text: 'Tidak ada data budget yang ditemukan.' });
                    } else {
                        alert('Tidak ada data budget yang ditemukan.');
                    }
                }
            },
            error: function() {
                $('#detail_body').html('<tr><td colspan="8" class="text-center py-4 text-danger"><i class="fa fa-exclamation-triangle me-1"></i> Gagal memuat data budget. Silakan coba lagi.</td></tr>');
            }
        });
    }

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();

        var inputed_nilai = 0;
        $(".nilai").each(function() {
            var valStr = $(this).val();
            if (valStr) {
                var cleanVal = parseFloat(valStr.toString().replace(/,/g, '')) || 0;
                inputed_nilai += cleanVal;
            }
        });

        if (inputed_nilai < 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Setidaknya masukkan 1 nilai perkiraan biaya yang lebih besar dari 0!'
                });
            } else {
                alert('Setidaknya masukkan 1 nilai perkiraan biaya yang lebih besar dari 0!');
            }
            return false;
        }

        var doSubmit = function() {
            var btn = $('#submit');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');
            var formdata = new FormData($('#frm_data')[0]);

            $.ajax({
                url: url_save,
                dataType: "json",
                type: 'POST',
                data: formdata,
                processData: false,
                contentType: false,
                success: function(msg) {
                    btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Pengajuan');
                    if (msg['save'] == '1') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses!',
                                text: 'Data pengajuan pembayaran periodik berhasil disimpan.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                if (typeof list_view === 'function') {
                                    list_view();
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else {
                            alert('Data pengajuan berhasil disimpan.');
                            if (typeof list_view === 'function') {
                                list_view();
                            } else {
                                window.location.reload();
                            }
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: msg['msg'] || 'Data gagal disimpan.'
                            });
                        } else {
                            alert('Data gagal disimpan.');
                        }
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Pengajuan');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan sistem saat memproses data.'
                        });
                    } else {
                        alert('Terjadi kesalahan sistem saat memproses data.');
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: "Simpan Pengajuan?",
                text: "Pastikan data rincian pembayaran periodik telah sesuai!",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    doSubmit();
                }
            });
        } else {
            if (confirm("Simpan pengajuan ini?")) {
                doSubmit();
            }
        }
    });

    <?php if ($type == 'add') : ?>
        add_detail();
    <?php endif; ?>
</script>
<?php endif; ?>
