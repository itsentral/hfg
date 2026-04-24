<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Filter Periode Dashboard</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal Dari</label>
                <input type="date" id="filter-tgl-dari" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Sampai</label>
                <input type="date" id="filter-tgl-sampai" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button id="btn-filter" class="btn btn-primary w-100">
                    <i class="fa fa-search"></i> Tampilkan
                </button>
            </div>
            <div class="col-md-4 d-flex align-items-end justify-content-end">
                <a href="<?= base_url('supplier_performance') ?>" class="btn btn-sm btn-outline-secondary me-1">
                    <i class="fa fa-table"></i> Summary
                </a>
                <a href="<?= base_url('supplier_performance/feed_coil') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-list"></i> Detail per Coil
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Chart Perbandingan Defect per Supplier -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-chart-bar"></i> Perbandingan Total Defect per Supplier (Top 10)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartDefect" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Perbandingan Kinerja Antar Supplier -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-table"></i> Tabel Perbandingan Kinerja Supplier</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbl-dashboard" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Nama Supplier</th>
                        <th class="text-center">Jml Coil</th>
                        <th class="text-end">Total Reject (kg)</th>
                        <th class="text-end">Total NG (kg)</th>
                        <th class="text-end">Total KW2 (kg)</th>
                        <th class="text-end">Total Defect (kg)</th>
                        <th class="text-end">Avg Selisih Net (kg)</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="tbody-dashboard"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
var chartDefect = null;

function loadDashboard() {
    var tglDari   = $('#filter-tgl-dari').val();
    var tglSampai = $('#filter-tgl-sampai').val();

    $.ajax({
        url: siteurl + 'supplier_performance/data_side_summary',
        type: 'POST',
        data: {
            draw: 1,
            start: 0,
            length: 10,
            'order[0][column]': 6,
            'order[0][dir]': 'desc',
            'search[value]': '',
            tgl_dari: tglDari,
            tgl_sampai: tglSampai
        },
        success: function (res) {
            var labels      = [];
            var rejectData  = [];
            var ngData      = [];
            var kw2Data     = [];
            var tbody       = '';
            var maxDefect   = 0;

            if (res.data && res.data.length > 0) {
                $.each(res.data, function (i, row) {
                    var defect = parseFloat(row[6].replace(/,/g, '')) || 0;
                    if (defect > maxDefect) maxDefect = defect;
                });

                $.each(res.data, function (i, row) {
                    labels.push(row[1]);
                    rejectData.push(parseFloat(row[3].replace(/,/g, '')) || 0);
                    ngData.push(parseFloat(row[4].replace(/,/g, '')) || 0);
                    kw2Data.push(parseFloat(row[5].replace(/,/g, '')) || 0);

                    var defect    = parseFloat(row[6].replace(/,/g, '')) || 0;
                    var isWorst   = (i === 0 && defect > 0);
                    var rowClass  = isWorst ? 'table-danger fw-bold' : '';
                    var badge     = isWorst ? '<span class="badge bg-danger">Worst</span>' : '';

                    tbody += '<tr class="' + rowClass + '">'
                           + '<td class="text-center">' + row[0] + '</td>'
                           + '<td>' + row[1] + '</td>'
                           + '<td class="text-center">' + row[2] + '</td>'
                           + '<td class="text-end">' + row[3] + '</td>'
                           + '<td class="text-end">' + row[4] + '</td>'
                           + '<td class="text-end">' + row[5] + '</td>'
                           + '<td class="text-end">' + row[6] + '</td>'
                           + '<td class="text-end">' + row[7] + '</td>'
                           + '<td class="text-center">' + badge + '</td>'
                           + '</tr>';
                });
            } else {
                tbody = '<tr><td colspan="9" class="text-center text-muted">Tidak ada data</td></tr>';
            }

            $('#tbody-dashboard').html(tbody);

            // Render chart
            if (chartDefect) {
                chartDefect.destroy();
            }
            var ctx = document.getElementById('chartDefect').getContext('2d');
            chartDefect = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Reject (kg)',
                            data: rejectData,
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        },
                        {
                            label: 'NG (kg)',
                            data: ngData,
                            backgroundColor: 'rgba(255, 193, 7, 0.7)',
                        },
                        {
                            label: 'KW2 (kg)',
                            data: kw2Data,
                            backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        },
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: false }
                    },
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true }
                    }
                }
            });
        }
    });
}

$(document).ready(function () {
    loadDashboard();

    $('#btn-filter').on('click', function () {
        loadDashboard();
    });
});
</script>
