<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa fa-bar-chart"></i>
            Perbandingan Timbang Awal — SPK: <strong><?= htmlspecialchars($spk_no) ?></strong>
        </h5>
        <a href="<?= base_url('production_weighing') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">

        <?php if (empty($rows)): ?>
            <div class="alert alert-info">
                Belum ada data timbang awal untuk SPK ini.
            </div>
        <?php else: ?>

            <!-- Ringkasan -->
            <?php
            $total_coil     = count($rows);
            $total_exception = 0;
            $total_net_pl   = 0;
            $total_net_timbang = 0;
            foreach ($rows as $r) {
                if ($r->status === 'Exception') $total_exception++;
                $total_net_pl      += $r->net_pl;
                $total_net_timbang += (float) $r->net_timbang_awal;
            }
            $total_selisih_net = $total_net_timbang - $total_net_pl;
            $total_selisih_pct = ($total_net_pl > 0) ? abs($total_selisih_net) / $total_net_pl * 100 : 0;
            ?>

            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card text-center border-primary">
                        <div class="card-body py-2">
                            <div class="text-muted small">Total Coil</div>
                            <div class="fs-4 fw-bold text-primary"><?= $total_coil ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-danger">
                        <div class="card-body py-2">
                            <div class="text-muted small">Exception</div>
                            <div class="fs-4 fw-bold text-danger"><?= $total_exception ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-secondary">
                        <div class="card-body py-2">
                            <div class="text-muted small">Total Selisih Net</div>
                            <div class="fs-4 fw-bold <?= $total_selisih_net < 0 ? 'text-danger' : 'text-success' ?>">
                                <?= number_format($total_selisih_net, 3) ?> kg
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-secondary">
                        <div class="card-body py-2">
                            <div class="text-muted small">Rata-rata Deviasi</div>
                            <div class="fs-4 fw-bold"><?= number_format($total_selisih_pct, 2) ?>%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Detail -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>No Timbang</th>
                            <th>No Coil</th>
                            <th class="text-end">Gross PL (kg)</th>
                            <th class="text-end">Gross Aktual (kg)</th>
                            <th class="text-end">Selisih Gross (kg)</th>
                            <th class="text-end">Net PL (kg)</th>
                            <th class="text-end">Net Timbang (kg)</th>
                            <th class="text-end">Selisih Net (kg)</th>
                            <th class="text-end">Deviasi %</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Operator</th>
                            <th class="text-center">Tgl Timbang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sum_gross_pl      = 0;
                        $sum_gross_actual  = 0;
                        $sum_selisih_gross = 0;
                        $sum_net_pl        = 0;
                        $sum_net_timbang   = 0;
                        $sum_selisih_net   = 0;
                        ?>
                        <?php foreach ($rows as $i => $r): ?>
                            <?php
                            $status_map   = ['Draft' => 'secondary', 'Confirmed' => 'success', 'Exception' => 'danger'];
                            $status_color = isset($status_map[$r->status]) ? $status_map[$r->status] : 'secondary';
                            $sn_class     = $r->status === 'Exception' ? 'text-danger fw-bold' : '';
                            $sum_gross_pl      += $r->gross_pl;
                            $sum_gross_actual  += $r->gross_actual;
                            $sum_selisih_gross += $r->selisih_gross;
                            $sum_net_pl        += $r->net_pl;
                            $sum_net_timbang   += (float) $r->net_timbang_awal;
                            $sum_selisih_net   += (float) $r->selisih_net;
                            ?>
                            <tr class="<?= $r->status === 'Exception' ? 'table-danger' : '' ?>">
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= base_url('production_weighing/view/' . $r->preweigh_no) ?>">
                                        <?= htmlspecialchars($r->preweigh_no) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($r->no_coil) ?></td>
                                <td class="text-end"><?= number_format($r->gross_pl, 3) ?></td>
                                <td class="text-end"><?= number_format($r->gross_actual, 3) ?></td>
                                <td class="text-end <?= $r->selisih_gross < 0 ? 'text-danger' : '' ?>">
                                    <?= number_format($r->selisih_gross, 3) ?>
                                </td>
                                <td class="text-end"><?= number_format($r->net_pl, 3) ?></td>
                                <td class="text-end"><?= number_format($r->net_timbang_awal, 3) ?></td>
                                <td class="text-end <?= $sn_class ?>"><?= number_format($r->selisih_net, 3) ?></td>
                                <td class="text-end <?= $sn_class ?>"><?= number_format($r->selisih_net_pct * 100, 2) ?>%</td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $status_color ?>"><?= $r->status ?></span>
                                </td>
                                <td class="text-center"><?= htmlspecialchars($r->nama_user ?: '-') ?></td>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($r->created_at)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">Total:</td>
                            <td class="text-end"><?= number_format($sum_gross_pl, 3) ?></td>
                            <td class="text-end"><?= number_format($sum_gross_actual, 3) ?></td>
                            <td class="text-end <?= $sum_selisih_gross < 0 ? 'text-danger' : '' ?>">
                                <?= number_format($sum_selisih_gross, 3) ?>
                            </td>
                            <td class="text-end"><?= number_format($sum_net_pl, 3) ?></td>
                            <td class="text-end"><?= number_format($sum_net_timbang, 3) ?></td>
                            <td class="text-end <?= $sum_selisih_net < 0 ? 'text-danger' : '' ?>">
                                <?= number_format($sum_selisih_net, 3) ?>
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        <?php endif; ?>
    </div>
</div>
