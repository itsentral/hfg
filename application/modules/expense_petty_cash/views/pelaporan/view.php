<?php
$header = isset($pelaporan->header) ? $pelaporan->header : $pelaporan;
$pencatatan_list = isset($pelaporan->pencatatan_list) ? $pelaporan->pencatatan_list : [];
$jumlah_item = count($pencatatan_list);

$periode_start_fmt = date('d/m/Y', strtotime($header->periode_start));
$periode_end_fmt   = date('d/m/Y', strtotime($header->periode_end));

$coa_map = [];
if (!empty($coa_list)) {
    foreach ($coa_list as $coa) {
        $coa_map[$coa->coa_code] = $coa->coa_code . ' - ' . (isset($coa->coa_nama) ? $coa->coa_nama : '');
    }
}

function get_pelaporan_status_badge($status) {
    switch ($status) {
        case 'draft': return '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fa fa-pencil me-1"></i>Draft</span>';
        case 'waiting': return '<span class="badge bg-info text-dark px-3 py-2 rounded-pill"><i class="fa fa-clock-o me-1"></i>Waiting Approval</span>';
        case 'approved': return '<span class="badge bg-success px-3 py-2 rounded-pill"><i class="fa fa-check-circle me-1"></i>Approved</span>';
        case 'reject': return '<span class="badge bg-danger px-3 py-2 rounded-pill"><i class="fa fa-times-circle me-1"></i>Reject</span>';
        default: return '<span class="badge bg-secondary px-3 py-2 rounded-pill">' . htmlspecialchars($status) . '</span>';
    }
}
?>

<div class="card border-0 shadow-sm rounded-3 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-file-text-o me-2"></i>Detail Pelaporan Petty Cash
            </h5>
            <small class="text-muted">Nomor Dokumen: <span class="fw-bold text-dark"><?= htmlspecialchars($header->no_pelaporan) ?></span></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= site_url('expense_petty_cash/pelaporan') ?>" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke List
            </a>
            <?php if ($header->status === 'approved') : ?>
                <a href="<?= site_url('expense_petty_cash/print_pelaporan/' . $header->id) ?>" target="_blank" class="btn btn-outline-success btn-sm px-3 shadow-sm">
                    <i class="fa fa-print me-1"></i> Cetak PDF
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Summary Info Grid -->
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
                    <span class="text-muted small fw-semibold text-uppercase">Jumlah Pencatatan</span>
                    <h5 class="fw-bold text-dark mb-0 mt-1"><?= $jumlah_item ?> Item</h5>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small fw-semibold text-uppercase">Grand Total</span>
                    <h5 class="fw-bold text-success mb-0 mt-1">Rp <?= number_format($header->grand_total, 0, ',', '.') ?></h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small fw-semibold text-uppercase">Status Dokumen</span>
                    <div class="mt-1"><?= get_pelaporan_status_badge($header->status) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small fw-semibold text-uppercase">Dibuat Pada</span>
                    <div class="fw-semibold text-dark mt-1"><i class="fa fa-clock-o me-1 text-muted"></i><?= !empty($header->created_on) ? date('d/m/Y H:i', strtotime($header->created_on)) : '-' ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small fw-semibold text-uppercase">Disetujui / Ditolak Pada</span>
                    <div class="fw-semibold text-dark mt-1"><i class="fa fa-check me-1 text-muted"></i><?= !empty($header->approved_on) ? date('d/m/Y H:i', strtotime($header->approved_on)) : '-' ?></div>
                </div>
            </div>
            <?php if (!empty($header->reject_reason)) : ?>
                <div class="col-12">
                    <div class="alert alert-danger border-0 rounded-3 mb-0">
                        <h6 class="fw-bold mb-1"><i class="fa fa-ban me-1"></i>Alasan Reject:</h6>
                        <p class="mb-0 small"><?= nl2br(htmlspecialchars($header->reject_reason)) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <hr class="my-4 text-muted opacity-25">

        <!-- Accordion per Pencatatan -->
        <h6 class="fw-bold mb-3 text-dark">
            <i class="fa fa-list-alt text-primary me-2"></i>Rincian Pencatatan Terkait
        </h6>

        <div class="accordion mb-4" id="accordionPencatatanView">
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
                    <h2 class="accordion-header" id="heading_v_<?= $pItem->id ?>">
                        <button class="accordion-button <?= ($pIdx === 0) ? '' : 'collapsed' ?> bg-light py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_v_<?= $pItem->id ?>" aria-expanded="<?= ($pIdx === 0) ? 'true' : 'false' ?>">
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
                    <div id="collapse_v_<?= $pItem->id ?>" class="accordion-collapse collapse <?= ($pIdx === 0) ? 'show' : '' ?>" data-bs-parent="#accordionPencatatanView">
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
    </div>
</div>
