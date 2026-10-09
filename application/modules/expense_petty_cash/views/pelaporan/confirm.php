<?php
$header = $pelaporan->header;
$pencatatan_list = isset($pelaporan->pencatatan_list) ? $pelaporan->pencatatan_list : [];
$jumlah_item = count($pencatatan_list);

$periode_start_fmt = date('d/m/Y', strtotime($header->periode_start));
$periode_end_fmt   = date('d/m/Y', strtotime($header->periode_end));

$sisa_budget = isset($budget_info->sisa_budget) ? (float)$budget_info->sisa_budget : 0;
$budget_total = isset($budget_info->budget) ? (float)$budget_info->budget : 0;

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
                <i class="fa fa-check-square-o me-2"></i>Konfirmasi & Pengajuan Pelaporan Petty Cash
            </h5>
            <small class="text-muted">Periksa kembali ringkasan pencatatan sebelum diajukan ke approver</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= site_url('expense_petty_cash/pelaporan') ?>" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
            <button type="button" class="btn btn-primary btn-sm px-4 shadow-sm" id="btn-submit-pelaporan">
                <i class="fa fa-send me-1"></i> Ajukan Pelaporan
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Summary Cards Row -->
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
            <i class="fa fa-list-alt text-primary me-2"></i>Daftar Pencatatan dalam Pelaporan
        </h6>

        <div class="accordion mb-4" id="accordionPencatatan">
            <?php foreach ($pencatatan_list as $pIdx => $pItem) : ?>
                <?php
                $pDetailObj = isset($pencatatan_details[$pItem->id]) ? $pencatatan_details[$pItem->id] : null;
                $pItems = [];
                $pEvidences = [];
                if ($pDetailObj) {
                    if (is_object($pDetailObj) && isset($pDetailObj->details)) {
                        $pItems = $pDetailObj->details;
                        $pEvidences = isset($pDetailObj->evidences) ? $pDetailObj->evidences : [];
                    } elseif (is_array($pDetailObj)) {
                        $pItems = $pDetailObj;
                    }
                }
                ?>
                <div class="accordion-item border rounded-3 mb-2 overflow-hidden shadow-none">
                    <h2 class="accordion-header" id="heading_<?= $pItem->id ?>">
                        <button class="accordion-button <?= ($pIdx === 0) ? '' : 'collapsed' ?> bg-light py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?= $pItem->id ?>" aria-expanded="<?= ($pIdx === 0) ? 'true' : 'false' ?>">
                            <div class="d-flex flex-wrap align-items-center justify-content-between w-100 me-3">
                                <div>
                                    <span class="fw-bold text-primary me-2"><i class="fa fa-file-text-o me-1"></i><?= htmlspecialchars($pItem->no_pencatatan) ?></span>
                                    <span class="text-muted small me-2"><i class="fa fa-calendar me-1"></i><?= date('d/m/Y', strtotime($pItem->tanggal)) ?></span>
                                    <span class="text-muted small"><i class="fa fa-user me-1"></i><?= htmlspecialchars($pItem->request_by) ?></span>
                                </div>
                                <div>
                                    <span class="badge bg-white text-dark border me-2"><?= count($pItems) ?> item</span>
                                    <span class="fw-bold text-success">Rp <?= number_format($pItem->grand_total, 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse_<?= $pItem->id ?>" class="accordion-collapse collapse <?= ($pIdx === 0) ? 'show' : '' ?>" data-bs-parent="#accordionPencatatan">
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
                                        <?php if (!empty($pItems)) : ?>
                                            <?php $iNo = 1; foreach ($pItems as $item) : ?>
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
                                                        $evs = [];
                                                        if (isset($item->evidences) && !empty($item->evidences)) {
                                                            $evs = $item->evidences;
                                                        } elseif (isset($pEvidences[$item->id])) {
                                                            $evs = $pEvidences[$item->id];
                                                        }
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

        <!-- Rekapitulasi Jurnal Periode -->
        <div class="card border border-light-subtle rounded-3 bg-light shadow-none mb-4">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-2 text-dark">
                    <i class="fa fa-book text-primary me-2"></i>Rekapitulasi Jurnal Periode Pelaporan
                </h6>
                <small class="text-muted d-block mb-3">Ringkasan total penjurnalan untuk seluruh pencatatan dalam pelaporan ini</small>

                <?php
                // Aggregasi COA
                $coa_summary = [];
                foreach ($pencatatan_list as $pItem) {
                    $pDetailObj = isset($pencatatan_details[$pItem->id]) ? $pencatatan_details[$pItem->id] : null;
                    $pItems = [];
                    if ($pDetailObj) {
                        if (is_object($pDetailObj) && isset($pDetailObj->details)) $pItems = $pDetailObj->details;
                        elseif (is_array($pDetailObj)) $pItems = $pDetailObj;
                    }
                    foreach ($pItems as $it) {
                        if (!isset($coa_summary[$it->coa_code])) {
                            $coa_summary[$it->coa_code] = 0;
                        }
                        $coa_summary[$it->coa_code] += (float)$it->total;
                    }
                }
                $grand_total = (float)$header->grand_total;
                ?>

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

        <div class="d-flex align-items-center justify-content-end gap-2">
            <a href="<?= site_url('expense_petty_cash/pelaporan') ?>" class="btn btn-secondary px-3">
                <i class="fa fa-times me-1"></i> Batal
            </a>
            <button type="button" class="btn btn-primary px-4 shadow-sm" id="btn-submit-bottom">
                <i class="fa fa-send me-1"></i> Ajukan Pelaporan Sekarang
            </button>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var PELAPORAN_ID = <?= json_encode($header->id) ?>;
    var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';

    $(document).ready(function() {
        $('#btn-submit-pelaporan, #btn-submit-bottom').on('click', function() {
            Swal.fire({
                title: 'Ajukan Pelaporan?',
                text: 'Pelaporan akan diajukan ke tahap approval. Pastikan seluruh rincian dan lampiran sudah benar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-send me-1"></i> Ya, Ajukan Pelaporan',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengajukan...',
                        text: 'Sedang memproses pengajuan pelaporan',
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: BASE_URL + 'submit_pelaporan/' + PELAPORAN_ID,
                        type: 'POST',
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.status) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: resp.message,
                                    icon: 'success'
                                }).then(function() {
                                    window.location.href = BASE_URL + 'pelaporan';
                                });
                            } else {
                                Swal.fire('Gagal!', resp.message || 'Gagal mengajukan pelaporan.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem saat mengajukan pelaporan.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
