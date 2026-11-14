<?php
$ENABLE_ADD    = has_permission('Spk_Material.Add');
$ENABLE_MANAGE = has_permission('Spk_Material.Manage');
$ENABLE_VIEW   = has_permission('Spk_Material.View');
?>

<style>
    .swal2-container {
        z-index: 999999 !important;
    }

    .swal2-popup {
        z-index: 1000000 !important;
    }

    .skeleton {
        border-radius: 4px;
        animation: shimmer 1.5s infinite linear;
        background: linear-gradient(90deg, #f2f2f2 25%, #e0e0e0 50%, #f2f2f2 75%);
        background-size: 200% 100%;
    }

    .skeleton-line {
        height: 20px;
        margin: 8px 0;
        border-radius: 4px;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    #spk-status-tabs .nav-link:not(.active) {
        color: #495057;
    }

    #spk-status-tabs .nav-link:not(.active):hover {
        background-color: #f1f3f5;
    }
</style>

<!-- Skeleton loading -->
<div id="skeleton-spk">
    <div class="skeleton">
        <div class="skeleton-line" style="width:60%"></div>
        <div class="skeleton-line" style="width:100%;height:200px"></div>
    </div>
</div>

<!-- Actual content (hidden until loaded) -->
<div id="content-spk" style="display:none">
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <!-- Nav Tabs Status SPK -->
            <ul class="nav nav-pills mb-0 flex-wrap gap-1" id="spk-status-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-1 px-2 px-md-3 small fw-semibold" id="tab-waiting" data-status="waiting" type="button" role="tab">
                        Menunggu Rilis <span class="badge bg-warning text-dark ms-1 rounded-pill" id="badge-count-waiting">0</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-2 px-md-3 small fw-semibold" id="tab-released" data-status="released" type="button" role="tab">
                        Released <span class="badge bg-success ms-1 rounded-pill" id="badge-count-released">0</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-2 px-md-3 small fw-semibold" id="tab-closed" data-status="closed" type="button" role="tab">
                        Closed Produksi <span class="badge bg-dark ms-1 rounded-pill" id="badge-count-closed">0</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-2 px-md-3 small fw-semibold" id="tab-all" data-status="all" type="button" role="tab">
                        Semua SPK <span class="badge bg-secondary ms-1 rounded-pill" id="badge-count-all">0</span>
                    </button>
                </li>
            </ul>

            <?php if ($ENABLE_ADD): ?>
                <a href="<?= site_url('spk_material/add') ?>" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus me-1"></i> Create SPK
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-spk" class="table table-bordered table-striped align-middle" width="100%">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>SPK No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Jumlah Produk</th>
                            <th width="20%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        const BASE_URL = siteurl + active_controller;
        var currentStatusFilter = 'waiting';

        // Initialize DataTables
        var tableSpk = $('#table-spk').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + '/data_side',
                type: 'GET',
                data: function(d) {
                    d.status_filter = currentStatusFilter;
                },
                dataSrc: function(json) {
                    if (json.counts) {
                        $('#badge-count-waiting').text(json.counts.waiting || 0);
                        $('#badge-count-released').text(json.counts.released || 0);
                        $('#badge-count-closed').text(json.counts.closed || 0);
                        $('#badge-count-all').text(json.counts.all || 0);
                    }
                    return json.data;
                }
            },
            columns: [{
                    data: 0,
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 1,
                    className: 'text-nowrap'
                },
                {
                    data: 2
                },
                {
                    data: 3
                },
                {
                    data: 4,
                    className: 'text-center'
                },
                {
                    data: 5,
                    className: 'text-center'
                },
                {
                    data: 6,
                    orderable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [1, 'desc']
            ],
            initComplete: function() {
                $('#skeleton-spk').hide();
                $('#content-spk').fadeIn();
            }
        });

        // Tab click event
        $('#spk-status-tabs button').on('click', function(e) {
            e.preventDefault();
            $('#spk-status-tabs button').removeClass('active');
            $(this).addClass('active');
            currentStatusFilter = $(this).data('status');
            tableSpk.ajax.reload();
        });

        // ---------------------------------------------------------------
        // RELEASE SPK BUTTON
        // ---------------------------------------------------------------
        $(document).on('click', '.btn-release-spk', function() {
            var spkNo = $(this).data('spk');
            if (!spkNo) return;

            Swal.fire({
                title: 'Release SPK ' + spkNo + '?',
                text: 'Setelah berstatus Released, SPK ini akan diteruskan ke Warehouse (Request List) dan dapat dibuat Laporan Produksi.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'Ya, Release SPK',
                cancelButtonText: 'Batal'
            }).then(function(r) {
                if (!r.isConfirmed) return;

                Swal.fire({
                    title: 'Merilis SPK...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: BASE_URL + '/release_spk',
                    type: 'POST',
                    data: {
                        spk_no: spkNo
                    },
                    dataType: 'json',
                    success: function(res) {
                        Swal.close();
                        if (res.status == 1) {
                            if (res.counts) {
                                $('#badge-count-waiting').text(res.counts.waiting || 0);
                                $('#badge-count-released').text(res.counts.released || 0);
                                $('#badge-count-closed').text(res.counts.closed || 0);
                                $('#badge-count-all').text(res.counts.all || 0);
                            }
                            tableSpk.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'SPK Berhasil Dirilis!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal merilis SPK.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                    }
                });
            });
        });

    });
</script>
