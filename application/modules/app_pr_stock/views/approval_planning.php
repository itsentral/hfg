<?php
$pembeda = substr($header[0]['so_number'], 0, 1);
$due_date = (!empty($header[0]['due_date'])) ? date('d F Y', strtotime($header[0]['due_date'])) : '-';
$tgl_dibutuhkan = (!empty($header[0]['tgl_dibutuhkan'])) ? date('d F Y', strtotime($header[0]['tgl_dibutuhkan'])) : '-';

// Detail Approval
$alasan_reject1 = (!empty($header)) ? $header[0]['reject_reason1'] : '';
$alasan_reject2 = (!empty($header)) ? $header[0]['reject_reason2'] : '';
$alasan_reject3 = (!empty($header)) ? $header[0]['reject_reason3'] : '';

$keterangan_1 = (!empty($header)) ? $header[0]['keterangan_1'] : '';
$keterangan_2 = (!empty($header)) ? $header[0]['keterangan_2'] : '';
$keterangan_3 = (!empty($header)) ? $header[0]['keterangan_3'] : '';

$status1 = '';
$tgl_appre_1 = '';
$status2 = '';
$tgl_appre_2 = '';
$status3 = '<span class="badge bg-secondary px-2 py-1">Menunggu</span>';
$tgl_appre_3 = '-';
if (!empty($header)) {
    if ($header[0]['app_3'] == '1') {
        $status3 = '<span class="badge bg-success px-2 py-1">Approved</span>';
        $tgl_appre_3 = date('d F Y', strtotime($header[0]['app_3_date']));
    } else {
        if ($header[0]['sts_reject3'] == '1') {
            $status3 = '<span class="badge bg-danger px-2 py-1">Rejected</span>';
            $tgl_appre_3 = date('d F Y', strtotime($header[0]['sts_reject3_date']));
        }
    }
}
?>

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-check-square-o me-2"></i>Form Approval PR Stock - Management
            </h5>
            <small class="text-muted">Nomor Request / SO: <span class="fw-bold text-dark"><?= htmlspecialchars($header[0]['so_number']) ?></span></small>
        </div>
        <div>
            <a href="<?= site_url('app_pr_stock/approval_management') ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <form id="data-form" method="post" autocomplete="off">
            <input type="hidden" name="so_number" id="so_number" value="<?= htmlspecialchars($header[0]['so_number']) ?>">
            <input type="hidden" name="tingkat_approval" id="tingkat_approval" value="<?= htmlspecialchars($tingkat_approval) ?>">

            <!-- Summary Information Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <span class="text-muted small fw-semibold text-uppercase">No. Request / SO</span>
                        <h6 class="fw-bold text-primary mb-0 mt-1"><?= htmlspecialchars($header[0]['so_number']) ?></h6>
                        <small class="text-muted">Due Date: <?= $due_date ?></small>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <span class="text-muted small fw-semibold text-uppercase">No. PR</span>
                        <h6 class="fw-bold text-dark mb-0 mt-1"><?= htmlspecialchars($header[0]['no_pr']) ?></h6>
                        <small class="text-muted">Dibutuhkan: <?= $tgl_dibutuhkan ?></small>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <span class="text-muted small fw-semibold text-uppercase">Customer</span>
                        <h6 class="fw-bold text-dark mb-0 mt-1"><?= htmlspecialchars($header[0]['nm_customer'] ?: '-') ?></h6>
                        <small class="text-muted">Tingkat: <span class="badge <?= ($header[0]['tingkat_pr'] == 2) ? 'bg-danger' : 'bg-info' ?>"><?= ($header[0]['tingkat_pr'] == 2) ? 'Urgent' : 'Normal' ?></span></small>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <span class="text-muted small fw-semibold text-uppercase">Perbandingan Nilai</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted">Budget:</small>
                            <span class="fw-bold text-dark">Rp <?= number_format($header[0]['nilai_budget']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Pengajuan:</small>
                            <span class="fw-bold text-primary">Rp <?= number_format($ttl_pengajuan_pr) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Status Info -->
            <div class="card border mb-4">
                <div class="card-header bg-light py-2">
                    <span class="fw-bold text-dark small"><i class="fa fa-history me-1"></i>Status Approval Management</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle">
                            <thead class="table-light small text-center">
                                <tr>
                                    <th width="15%">Level Approval</th>
                                    <th width="12%">Status</th>
                                    <th width="18%">Tgl Approve / Reject</th>
                                    <th width="25%">Alasan Reject</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center fw-semibold">Management</td>
                                    <td class="text-center"><?= $status3 ?></td>
                                    <td class="text-center text-muted"><?= $tgl_appre_3 ?></td>
                                    <td>
                                        <input type="text" name="reject_reason3" class="form-control form-control-sm" placeholder="Alasan reject..." value="<?= htmlspecialchars($alasan_reject3) ?>" <?= ($header[0]['app_3'] == '1') ? 'readonly' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="keterangan_3" class="form-control form-control-sm" placeholder="Keterangan..." value="<?= htmlspecialchars($keterangan_3) ?>" <?= ($header[0]['app_3'] == '1') ? 'readonly' : '' ?>>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detail Barang Table -->
            <h6 class="fw-bold text-primary mb-2"><i class="fa fa-list me-1"></i>Rincian Barang yang Diajukan</h6>
            <div class="table-responsive mb-4">
                <table class="table table-striped table-bordered table-hover align-middle w-100">
                    <thead class="table-light text-center small">
                        <tr>
                            <th width="4%" class="text-center">
                                <input type="checkbox" class="form-check-input" name="chk_all" id="chk_all">
                            </th>
                            <th>Barang</th>
                            <th width="11%" class="text-end">Kebutuhan / Bln</th>
                            <th width="10%" class="text-end">Max</th>
                            <th width="10%" class="text-end">Stock</th>
                            <th width="10%" class="text-end">Propose</th>
                            <th width="12%" class="text-end">Price Reference</th>
                            <th width="14%" class="text-end">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grand_total_price = 0;
                        if (!empty($detail)) :
                            foreach ($detail as $key => $value) :
                                $key++;
                                $nm_material = htmlspecialchars($value['nm_material']) . ' <br><small class="text-muted fw-semibold">(' . htmlspecialchars($value['no_coa'] . ' - ' . $value['nm_coa']) . ')</small>';
                                $propose = $value['propose_purchase'];

                                $get_kebutuhan = $this->db->get_where('budget_rutin_detail', ['id_barang' => $value['id_material']])->row();
                                $get_stock = $this->db->get_where('warehouse_stock_non_material', ['id_material' => $value['id_material']])->row();

                                $kebutuhan = (!empty($get_kebutuhan)) ? $get_kebutuhan->kebutuhan_month : 0;
                                $stock = (!empty($get_stock)) ? $get_stock->qty_stock : 0;
                                $total_row = ($propose) * $value['price_ref'];
                                $grand_total_price += $total_row;
                        ?>
                            <tr>
                                <td class="text-center">
                                    <?php if ($value['status_app'] == 'N') : ?>
                                        <input type="checkbox" class="form-check-input chk_personal" name="check[<?= $value['id'] ?>]" value="<?= $value['id'] ?>">
                                    <?php else : ?>
                                        <i class="fa fa-check text-success"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $nm_material ?>
                                    <input type="hidden" name="detail[<?= $key ?>][id]" value="<?= $value['id'] ?>">
                                </td>
                                <td class="text-end"><?= number_format($kebutuhan) ?></td>
                                <td class="text-end"><?= number_format($kebutuhan * 1.5) ?></td>
                                <td class="text-end fw-semibold"><?= number_format($stock, 2) ?></td>
                                <td class="text-end fw-bold text-primary"><?= number_format($propose, 2) ?></td>
                                <td class="text-end">Rp <?= number_format($value['price_ref']) ?></td>
                                <td class="text-end fw-bold">Rp <?= number_format($total_row) ?></td>
                            </tr>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="7" class="text-end fw-bold">Total Price Pengajuan:</th>
                            <th class="text-end fw-bold text-primary fs-6">Rp <?= number_format($grand_total_price) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Form Reject Reason -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="reject_reason" class="form-label fw-semibold text-muted small">Alasan Reject (Diisi bila menolak pengajuan)</label>
                    <textarea name="reject_reason" id="reject_reason" class="form-control" rows="3" placeholder="Tuliskan catatan / alasan bila pengajuan di-reject..."></textarea>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <!-- Action Buttons -->
            <div class="d-flex align-items-center justify-content-end gap-2">
                <button type="button" class="btn btn-secondary px-3 shadow-sm" name="back" id="back">
                    <i class="fa fa-arrow-left me-1"></i> Kembali
                </button>
                <button type="button" class="btn btn-danger px-4 shadow-sm" name="reject" id="reject">
                    <i class="fa fa-times me-1"></i> Reject PR
                </button>
                <button type="button" class="btn btn-success px-4 shadow-sm" name="save" id="save">
                    <i class="fa fa-check me-1"></i> Approve PR
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var base_url = '<?= base_url(); ?>';
    var active_controller = '<?= ($this->uri->segment(1)); ?>';
    var tingkat_approval = $("#tingkat_approval").val();

    $(document).ready(function() {
        $("#chk_all").on("change", function() {
            $('.chk_personal').prop('checked', this.checked);
        });

        $(document).on('click', '#back', function() {
            if (tingkat_approval == "1") {
                window.location.href = base_url + active_controller + '/approval_head';
            } else if (tingkat_approval == "2") {
                window.location.href = base_url + active_controller + '/approval_cost_control';
            } else {
                window.location.href = base_url + active_controller + '/approval_management';
            }
        });

        // APPROVE ACTION
        $('#save').click(function(e) {
            e.preventDefault();

            if ($('.chk_personal:checked').length == 0) {
                Swal.fire({
                    title: "Peringatan!",
                    text: 'Silakan checklist minimal satu item barang untuk diapprove!',
                    icon: "warning"
                });
                return false;
            }

            Swal.fire({
                title: "Konfirmasi Approval",
                text: "Apakah Anda yakin ingin menyetujui item PR yang dipilih?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#198754",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-check me-1'></i> Ya, Approve!",
                cancelButtonText: "Batal"
            }).then((next) => {
                if (next.isConfirmed) {
                    Swal.fire({
                        title: "Memproses...",
                        text: "Sedang menyimpan persetujuan PR",
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    var formData = new FormData($('#data-form')[0]);
                    var baseurl = siteurl + active_controller + '/process_approval_all';

                    $.ajax({
                        url: baseurl,
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.status == 1) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: data.pesan || "Data berhasil diapprove.",
                                    icon: "success"
                                }).then(() => {
                                    if (tingkat_approval == "1") {
                                        window.location.href = base_url + active_controller + '/approval_head';
                                    } else if (tingkat_approval == "2") {
                                        window.location.href = base_url + active_controller + '/approval_cost_control';
                                    } else {
                                        window.location.href = base_url + active_controller + '/approval_management';
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "Gagal!",
                                    text: data.pesan || "Gagal memproses approval.",
                                    icon: "warning"
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: "Error!",
                                text: 'Terjadi kesalahan sistem saat menghubungi server.',
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });

        // REJECT ACTION
        $("#reject").click(function(e) {
            e.preventDefault();

            var reject_val = $("#reject_reason").val().trim();
            if (reject_val === "") {
                Swal.fire({
                    title: "Alasan Reject Wajib Diisi!",
                    text: "Silakan isi alasan penolakan pada kolom yang tersedia sebelum menolak.",
                    icon: "warning"
                });
                $("#reject_reason").focus();
                return false;
            }

            Swal.fire({
                title: "Konfirmasi Reject PR",
                text: "Apakah Anda yakin ingin menolak pengajuan PR ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "<i class='fa fa-times me-1'></i> Ya, Reject!",
                cancelButtonText: "Batal"
            }).then((next) => {
                if (next.isConfirmed) {
                    Swal.fire({
                        title: "Memproses...",
                        text: "Sedang memproses penolakan PR",
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    var formData = new FormData($('#data-form')[0]);
                    var baseurl = siteurl + active_controller + '/process_reject_all';

                    $.ajax({
                        url: baseurl,
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.status == 1) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: data.pesan || "Data berhasil direject.",
                                    icon: "success"
                                }).then(() => {
                                    if (tingkat_approval == "1") {
                                        window.location.href = base_url + active_controller + '/approval_head';
                                    } else if (tingkat_approval == "2") {
                                        window.location.href = base_url + active_controller + '/approval_cost_control';
                                    } else {
                                        window.location.href = base_url + active_controller + '/approval_management';
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "Gagal!",
                                    text: data.pesan || "Gagal memproses reject.",
                                    icon: "warning"
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: "Error!",
                                text: 'Terjadi kesalahan sistem saat memproses reject.',
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });
    });
</script>
