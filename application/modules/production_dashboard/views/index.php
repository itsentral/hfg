<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-lg-2">
        <div class="card text-white bg-primary h-100">
            <div class="card-body text-center">
                <div class="fs-1 fw-bold"><?= $summary['plan_aktif'] ?></div>
                <div class="small mt-1"><i class="fa fa-clipboard-list"></i> Plan Aktif</div>
            </div>
            <div class="card-footer text-center p-1">
                <a href="<?= base_url('production_planning') ?>" class="text-white small">Lihat Detail &raquo;</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card text-white bg-warning h-100">
            <div class="card-body text-center">
                <div class="fs-1 fw-bold"><?= $summary['spk_in_process'] ?></div>
                <div class="small mt-1"><i class="fa fa-cogs"></i> SPK In Process</div>
            </div>
            <div class="card-footer text-center p-1">
                <a href="<?= base_url('production_issue') ?>" class="text-white small">Lihat Detail &raquo;</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body text-center">
                <div class="fs-4 fw-bold"><?= number_format($summary['total_stok_qty'], 0) ?> pcs</div>
                <div class="small"><?= number_format($summary['total_stok_berat'], 0) ?> kg</div>
                <div class="small mt-1"><i class="fa fa-boxes"></i> Total Stok FG</div>
            </div>
            <div class="card-footer text-center p-1">
                <a href="<?= base_url('fg_warehouse/stok_fg') ?>" class="text-white small">Lihat Detail &raquo;</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-2">
        <div class="card text-white <?= $summary['do_pending'] > 0 ? 'bg-danger' : 'bg-secondary' ?> h-100">
            <div class="card-body text-center">
                <div class="fs-1 fw-bold"><?= $summary['do_pending'] ?></div>
                <div class="small mt-1"><i class="fa fa-truck"></i> DO Pending Approval</div>
            </div>
            <div class="card-footer text-center p-1">
                <a href="<?= base_url('delivery_fg') ?>" class="text-white small">Lihat Detail &raquo;</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card text-white <?= $summary['laporan_pending'] > 0 ? 'bg-info' : 'bg-secondary' ?> h-100">
            <div class="card-body text-center">
                <div class="fs-1 fw-bold"><?= $summary['laporan_pending'] ?></div>
                <div class="small mt-1"><i class="fa fa-file-alt"></i> Laporan Menunggu Review</div>
            </div>
            <div class="card-footer text-center p-1">
                <a href="<?= base_url('production_report') ?>" class="text-white small">Lihat Detail &raquo;</a>
            </div>
        </div>
    </div>
</div>

<!-- Menu Laporan -->
<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body text-center py-4">
                <i class="fa fa-balance-scale fa-3x text-primary mb-3"></i>
                <h6>Laporan Timbang Awal</h6>
                <p class="text-muted small">Perbandingan net weight timbang awal vs packing list per coil</p>
                <a href="<?= base_url('production_dashboard/laporan_timbang_awal') ?>" class="btn btn-primary btn-sm">
                    Buka Laporan
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body text-center py-4">
                <i class="fa fa-chart-pie fa-3x text-success mb-3"></i>
                <h6>Laporan Hasil Produksi</h6>
                <p class="text-muted small">Breakdown output per kategori dan yield per SPK</p>
                <a href="<?= base_url('production_dashboard/laporan_hasil_produksi') ?>" class="btn btn-success btn-sm">
                    Buka Laporan
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body text-center py-4">
                <i class="fa fa-truck fa-3x text-warning mb-3"></i>
                <h6>Laporan Selisih Delivery</h6>
                <p class="text-muted small">Selisih estimasi vs aktual berat per Delivery Order</p>
                <a href="<?= base_url('production_dashboard/laporan_delivery_discrepancy') ?>" class="btn btn-warning btn-sm">
                    Buka Laporan
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body text-center py-4">
                <i class="fa fa-weight fa-3x text-info mb-3"></i>
                <h6>Laporan Berat FG</h6>
                <p class="text-muted small">Riwayat berat referensi FG per produk per periode</p>
                <a href="<?= base_url('production_dashboard/laporan_berat_fg') ?>" class="btn btn-info btn-sm">
                    Buka Laporan
                </a>
            </div>
        </div>
    </div>
</div>
