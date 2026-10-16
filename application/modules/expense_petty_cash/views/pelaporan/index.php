<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-file-text-o me-2"></i>Daftar Pelaporan Petty Cash
            </h5>
            <small class="text-muted">Kelola rekapitulasi pelaporan mingguan petty cash dan pantau status persetujuannya</small>
        </div>
        <div>
            <a href="<?= site_url('expense_petty_cash') ?>" class="btn btn-outline-primary btn-sm px-3 shadow-sm">
                <i class="fa fa-list me-1"></i> Ke Pencatatan Harian
            </a>
        </div>
    </div>

    <!-- Filter Card Body -->
    <div class="card-body bg-light border-bottom py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filter-company" class="form-label small fw-semibold text-muted mb-1">Company</label>
                <select id="filter-company" class="form-select form-select-sm">
                    <option value="">-- Semua Company --</option>
                    <option value="STM">STM</option>
                    <option value="VUCA">VUCA</option>
                    <option value="SUSTAIN">SUSTAIN</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-status" class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="draft">Draft</option>
                    <option value="waiting">Waiting Approval</option>
                    <option value="approved">Approved</option>
                    <option value="reject">Reject</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Cari Tanggal / Periode (Flatpickr)</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fa fa-calendar"></i></span>
                    <input type="text" id="filter-date-search" class="form-control flatpickr-date" placeholder="Pilih tanggal...">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary flex-fill" id="btn-filter">
                    <i class="fa fa-filter me-1"></i> Filter
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reset-filter" title="Reset Filter">
                    <i class="fa fa-refresh"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="table-pelaporan" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-light text-center">
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="150">No Pelaporan</th>
                        <th width="170" class="text-center">Periode</th>
                        <th width="90" class="text-center">Company</th>
                        <th width="120" class="text-center">Pencatatan</th>
                        <th width="130" class="text-end">Grand Total</th>
                        <th width="130" class="text-center">Status</th>
                        <th width="140" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script type="text/javascript">
    var PERMISSIONS = {
        has_add: <?= json_encode((bool)$has_add) ?>,
        has_manage: <?= json_encode((bool)$has_manage) ?>
    };
    var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';

    $(document).ready(function() {
        flatpickr('#filter-date-search', {
            dateFormat: 'Y-m-d',
            locale: 'id',
            allowInput: true
        });

        var table = $('#table-pelaporan').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + 'get_data_pelaporan',
                type: 'POST',
                data: function(d) {
                    d.company = $('#filter-company').val();
                    d.status = $('#filter-status').val();
                    var dateVal = $('#filter-date-search').val();
                    if (dateVal && !d.search.value) {
                        d.search.value = dateVal;
                    }
                }
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'no', orderable: false, searchable: false, className: 'text-center' },
                {
                    data: 'no_pelaporan',
                    render: function(data) {
                        return '<span class="fw-bold text-primary">' + $('<div>').text(data).html() + '</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    render: function(data, type, row) {
                        function fmt(d) {
                            if (!d) return '-';
                            var p = d.split('-');
                            return (p.length === 3) ? p[2] + '/' + p[1] + '/' + p[0] : d;
                        }
                        return '<span class="small fw-semibold">' + fmt(row.periode_start) + ' - ' + fmt(row.periode_end) + '</span>';
                    }
                },
                {
                    data: 'company',
                    className: 'text-center',
                    render: function(data) {
                        var bCls = (data === 'STM') ? 'bg-primary' : ((data === 'VUCA') ? 'bg-info text-dark' : 'bg-success');
                        return '<span class="badge ' + bCls + ' px-2 py-1">' + data + '</span>';
                    }
                },
                {
                    data: 'jumlah_pencatatan',
                    className: 'text-center',
                    render: function(data) {
                        return '<span class="badge bg-light text-dark border">' + (data || 0) + ' item</span>';
                    }
                },
                {
                    data: 'grand_total',
                    className: 'text-end fw-semibold',
                    render: function(data) {
                        return 'Rp ' + (data || '0');
                    }
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(data) {
                        switch (data) {
                            case 'draft': return '<span class="badge bg-warning text-dark px-2 py-1">Draft</span>';
                            case 'waiting': return '<span class="badge bg-info text-dark px-2 py-1">Waiting Approval</span>';
                            case 'approved': return '<span class="badge bg-success px-2 py-1">Approved</span>';
                            case 'reject': return '<span class="badge bg-danger px-2 py-1">Reject</span>';
                            default: return '<span class="badge bg-secondary px-2 py-1">' + data + '</span>';
                        }
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center text-nowrap',
                    render: function(data, type, row) {
                        var btn = '<div class="btn-group btn-group-sm" role="group">';
                        btn += '<a href="' + BASE_URL + 'view_pelaporan/' + row.id + '" class="btn btn-outline-info btn-sm" title="Detail"><i class="fa fa-eye"></i></a>';

                        if (row.status === 'draft' && (PERMISSIONS.has_add || PERMISSIONS.has_manage)) {
                            btn += '<a href="' + BASE_URL + 'confirm_pelaporan/' + row.id + '" class="btn btn-outline-primary btn-sm" title="Review & Ajukan"><i class="fa fa-send"></i></a>';
                        }

                        if (row.status === 'approved') {
                            btn += '<a href="' + BASE_URL + 'print_pelaporan/' + row.id + '" target="_blank" class="btn btn-outline-success btn-sm" title="Cetak PDF"><i class="fa fa-print"></i></a>';
                        }

                        btn += '</div>';
                        return btn;
                    }
                }
            ]
        });

        $('#btn-filter').on('click', function() {
            table.ajax.reload();
        });

        $('#btn-reset-filter').on('click', function() {
            $('#filter-company').val('');
            $('#filter-status').val('');
            $('#filter-date-search').val('');
            table.search('').ajax.reload();
        });
    });
</script>
