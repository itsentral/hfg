<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-check-circle me-2"></i>Daftar Approval Pelaporan Petty Cash
            </h5>
            <small class="text-muted">Daftar pengajuan pelaporan mingguan yang membutuhkan persetujuan Anda</small>
        </div>
        <div>
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                <i class="fa fa-clock-o me-1"></i>Antrian Waiting Approval
            </span>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="table-approval" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-light text-center">
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="160">No Pelaporan</th>
                        <th width="170" class="text-center">Periode</th>
                        <th width="90" class="text-center">Company</th>
                        <th width="120" class="text-center">Pencatatan</th>
                        <th width="140" class="text-end">Grand Total</th>
                        <th width="100" class="text-center">Aksi</th>
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

<script type="text/javascript">
    var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';

    $(document).ready(function() {
        $('#table-approval').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + 'get_data_approval',
                type: 'POST'
            },
            order: [[1, 'asc']],
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
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
                        var num = parseFloat(data) || 0;
                        return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return '<a href="' + BASE_URL + 'view_approval/' + row.id + '" class="btn btn-primary btn-sm px-3 shadow-sm" title="Review & Approval"><i class="fa fa-check-square-o me-1"></i> Review</a>';
                    }
                }
            ]
        });
    });
</script>
