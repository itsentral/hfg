<?php
$header = isset($pelaporan->header) ? $pelaporan->header : $pelaporan;
$pencatatan_list = isset($pelaporan->pencatatan_list) ? $pelaporan->pencatatan_list : [];
$jumlah_item = count($pencatatan_list);

$periode_start_fmt = date('d/m/Y', strtotime($header->periode_start));
$periode_end_fmt   = date('d/m/Y', strtotime($header->periode_end));

$sisa_budget = isset($budget_info->sisa_budget) ? (float)$budget_info->sisa_budget : 0;

$coa_map = [];
if (!empty($coa_list)) {
    foreach ($coa_list as $coa) {
        $coa_map[$coa->coa_code] = $coa->coa_code . ' - ' . (isset($coa->coa_nama) ? $coa->coa_nama : '');
    }
}
?>

<div class="card border-0 shadow-sm rounded-3 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-check-circle text-primary me-2"></i>Review Approval Pelaporan Petty Cash
            </h5>
            <small class="text-muted">Nomor Dokumen: <span class="fw-bold text-dark"><?= htmlspecialchars($header->no_pelaporan) ?></span></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= site_url('expense_petty_cash/approval') ?>" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Antrian
            </a>
            <button type="button" class="btn btn-danger btn-sm px-3 shadow-sm" id="btn-reject-top">
                <i class="fa fa-ban me-1"></i> Tolak (Reject)
            </button>
            <button type="button" class="btn btn-success btn-sm px-3 shadow-sm" id="btn-approve-top">
                <i class="fa fa-check me-1"></i> Setujui (Approve)
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Summary Cards Grid -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small fw-semibold text-uppercase">No Pelaporan</span>
                    <h5 class="fw-bold text-primary mb-0 mt-1"><?= htmlspecialchars($header->no_pelaporan) ?></h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small fw-semibold text-uppercase">Periode Transaksi</span>
                    <h6 class="fw-bold text-dark mb-0 mt-1"><i class="fa fa-calendar me-1 text-muted"></i><?= $periode_start_fmt ?> - <?= $periode_end_fmt ?></h6>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small fw-semibold text-uppercase">Company</span>
                    <div class="mt-1">
                        <?php
                        $comp = $header->company;
                        $bCls = ($comp === 'STM') ? 'bg-primary' : (($comp === 'VUCA') ? 'bg-info text-dark' : 'bg-success');
                        ?>
                        <span class="badge <?= $bCls ?> px-2 py-1 fs-6"><?= htmlspecialchars($comp) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small fw-semibold text-uppercase">Pencatatan</span>
                    <h5 class="fw-bold text-dark mb-0 mt-1"><?= $jumlah_item ?> Item</h5>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small fw-semibold text-uppercase">Grand Total</span>
                    <h5 class="fw-bold text-success mb-0 mt-1">Rp <?= number_format($header->grand_total, 0, ',', '.') ?></h5>
                </div>
            </div>
        </div>

        <hr class="my-4 text-muted opacity-25">

        <!-- Accordion per Pencatatan -->
        <h6 class="fw-bold mb-3 text-dark">
            <i class="fa fa-list-alt text-primary me-2"></i>Daftar Pencatatan yang Diajukan
        </h6>

        <div class="accordion mb-4" id="accordionApproval">
            <?php foreach ($pencatatan_list as $pIdx => $pItem) : ?>
                <?php
                $pDetailItems = isset($pencatatan_details[$pItem->id]) ? $pencatatan_details[$pItem->id] : [];
                $items = is_array($pDetailItems) ? $pDetailItems : (isset($pDetailItems->details) ? $pDetailItems->details : []);
                ?>
                <div class="accordion-item border rounded-3 mb-2 overflow-hidden shadow-none">
                    <h2 class="accordion-header" id="heading_app_<?= $pItem->id ?>">
                        <button class="accordion-button <?= ($pIdx === 0) ? '' : 'collapsed' ?> bg-light py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_app_<?= $pItem->id ?>" aria-expanded="<?= ($pIdx === 0) ? 'true' : 'false' ?>">
                            <div class="d-flex flex-wrap align-items-center justify-content-between w-100 me-3">
                                <div>
                                    <span class="fw-bold text-primary me-2"><i class="fa fa-file-text-o me-1"></i><?= htmlspecialchars($pItem->no_pencatatan) ?></span>
                                    <span class="text-muted small me-2"><i class="fa fa-calendar me-1"></i><?= date('d/m/Y', strtotime($pItem->tanggal)) ?></span>
                                    <span class="text-muted small"><i class="fa fa-user me-1"></i><?= htmlspecialchars($pItem->request_by) ?></span>
                                </div>
                                <div>
                                    <span class="badge bg-white text-dark border me-2"><?= count($items) ?> item</span>
                                    <span class="fw-bold text-success">Rp <?= number_format($pItem->grand_total, 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse_app_<?= $pItem->id ?>" class="accordion-collapse collapse <?= ($pIdx === 0) ? 'show' : '' ?>" data-bs-parent="#accordionApproval">
                        <div class="accordion-body p-3 bg-white">
                            <?php if (!empty($pItem->keterangan)) : ?>
                                <p class="text-muted small mb-2"><i class="fa fa-info-circle me-1"></i><strong>Keterangan:</strong> <?= htmlspecialchars($pItem->keterangan) ?></p>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover align-middle mb-0" style="font-size: 12px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="35" class="text-center">#</th>
                                            <th width="220">COA</th>
                                            <th>Pengeluaran</th>
                                            <th>Spesifikasi</th>
                                            <th width="60" class="text-center">Qty</th>
                                            <th width="110" class="text-end">Nominal</th>
                                            <th width="120" class="text-end">Total</th>
                                            <th width="160">Evidence</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($items)) : ?>
                                            <?php $iNo = 1; foreach ($items as $item) : ?>
                                                <tr>
                                                    <td class="text-center text-muted"><?= $iNo++ ?></td>
                                                    <td><?= isset($coa_map[$item->coa_code]) ? htmlspecialchars($coa_map[$item->coa_code]) : htmlspecialchars($item->coa_code) ?></td>
                                                    <td><?= htmlspecialchars($item->pengeluaran) ?></td>
                                                    <td><?= htmlspecialchars($item->spesifikasi) ?: '-' ?></td>
                                                    <td class="text-center"><?= $item->jumlah ?></td>
                                                    <td class="text-end">Rp <?= number_format($item->nominal, 0, ',', '.') ?></td>
                                                    <td class="text-end fw-semibold">Rp <?= number_format($item->total, 0, ',', '.') ?></td>
                                                    <td>
                                                        <?php
                                                        $evs = isset($item->evidences) ? $item->evidences : [];
                                                        if (!empty($evs)) : ?>
                                                            <?php foreach ($evs as $ev) : ?>
                                                                <a href="<?= base_url('assets/expense_petty_cash/' . $ev->encrypted_name) ?>" target="_blank" class="badge bg-light text-dark border text-decoration-none p-1 me-1 mb-1">
                                                                    <i class="fa fa-paperclip text-primary me-1"></i><?= htmlspecialchars(strlen($ev->original_name) > 12 ? substr($ev->original_name, 0, 12) . '...' : $ev->original_name) ?>
                                                                </a>
                                                            <?php endforeach; ?>
                                                        <?php else : ?>
                                                            <span class="text-muted small">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr><td colspan="8" class="text-center text-muted py-2">Tidak ada detail</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <hr class="my-4 text-muted opacity-25">

        <!-- Rekap Jurnal Section -->
        <?php
        $coa_summary = [];
        foreach ($pencatatan_list as $pItem) {
            $pDetailItems = isset($pencatatan_details[$pItem->id]) ? $pencatatan_details[$pItem->id] : [];
            $items = is_array($pDetailItems) ? $pDetailItems : (isset($pDetailItems->details) ? $pDetailItems->details : []);
            foreach ($items as $it) {
                if (!isset($coa_summary[$it->coa_code])) {
                    $coa_summary[$it->coa_code] = 0;
                }
                $coa_summary[$it->coa_code] += (float)$it->total;
            }
        }
        $grand_total = (float)$header->grand_total;
        ?>
        <div class="card border border-light-subtle rounded-3 bg-light shadow-none mb-4">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-2 text-dark">
                    <i class="fa fa-book text-primary me-2"></i>Rekap Jurnal Transaksi yang Disetujui
                </h6>
                <div class="table-responsive bg-white rounded border">
                    <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                        <thead class="table-light">
                            <tr>
                                <th width="120">COA</th>
                                <th>Nama Account</th>
                                <th width="80" class="text-center">Company</th>
                                <th width="140" class="text-end">Total Debit</th>
                                <th width="140" class="text-end">Total Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coa_summary as $code => $tot) :
                                $nama = isset($coa_map[$code]) ? $coa_map[$code] : $code;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($code) ?></td>
                                    <td><?= htmlspecialchars($nama) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($header->company) ?></td>
                                    <td class="text-end">Rp <?= number_format($tot, 0, ',', '.') ?></td>
                                    <td class="text-end text-muted">-</td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-warning">
                                <td>1101-01-02</td>
                                <td>Kas Kecil</td>
                                <td class="text-center">STM</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end">Rp <?= number_format($grand_total, 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">Balancing:</td>
                                <td class="text-end">Rp <?= number_format($grand_total, 0, ',', '.') ?></td>
                                <td class="text-end">Rp <?= number_format($grand_total, 0, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Action Card -->
        <div class="card border border-primary-subtle rounded-3 p-3 bg-white shadow-sm">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Keputusan Approval</h6>
                    <small class="text-muted">Setujui pelaporan untuk memproses reimbursement kas kecil, atau tolak dengan menyertakan alasan.</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-danger px-4" id="btn-reject">
                        <i class="fa fa-ban me-1"></i> Tolak Pelaporan
                    </button>
                    <button type="button" class="btn btn-success px-4 shadow-sm" id="btn-approve">
                        <i class="fa fa-check me-1"></i> Setujui Pelaporan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var PELAPORAN_ID = <?= json_encode($header->id) ?>;
    var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';

    $(document).ready(function() {
        // Approve
        $('#btn-approve, #btn-approve-top').on('click', function() {
            Swal.fire({
                title: 'Setujui Pelaporan Petty Cash?',
                text: 'Pelaporan akan disetujui dan pengajuan Request Payment untuk reimbursement akan otomatis dibuat.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-check me-1"></i> Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menyetujui pelaporan',
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: BASE_URL + 'approve/' + PELAPORAN_ID,
                        type: 'POST',
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.status) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: resp.message || 'Pelaporan berhasil disetujui.',
                                    icon: 'success'
                                }).then(function() {
                                    window.location.href = BASE_URL + 'approval';
                                });
                            } else {
                                Swal.fire('Gagal!', resp.message || 'Gagal melakukan approval.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem saat memproses approval.', 'error');
                        }
                    });
                }
            });
        });

        // Reject
        $('#btn-reject, #btn-reject-top').on('click', function() {
            Swal.fire({
                title: 'Tolak Pelaporan Petty Cash',
                text: 'Tuliskan alasan penolakan secara jelas (minimal 10 karakter):',
                input: 'textarea',
                inputPlaceholder: 'Tuliskan alasan penolakan di sini...',
                inputAttributes: {
                    'aria-label': 'Alasan penolakan',
                    'rows': 4
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-ban me-1"></i> Tolak Pelaporan',
                cancelButtonText: 'Batal',
                preConfirm: function(alasan) {
                    if (!alasan || alasan.trim().length < 10) {
                        Swal.showValidationMessage('Alasan penolakan wajib diisi minimal 10 karakter.');
                        return false;
                    }
                    return alasan.trim();
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menolak pelaporan',
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: BASE_URL + 'reject/' + PELAPORAN_ID,
                        type: 'POST',
                        data: { alasan: result.value },
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.status) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: resp.message || 'Pelaporan berhasil ditolak.',
                                    icon: 'success'
                                }).then(function() {
                                    window.location.href = BASE_URL + 'approval';
                                });
                            } else {
                                Swal.fire('Gagal!', resp.message || 'Gagal menolak pelaporan.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem saat memproses reject.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
