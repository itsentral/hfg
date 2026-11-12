<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="card shadow-sm border-0 mb-4 box">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fa fa-money me-2"></i>Daftar Pencatatan Petty Cash
            </h5>
            <small class="text-muted">Catat pengeluaran harian kas kecil dan kumpulkan untuk pelaporan mingguan</small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <?php if ($has_add) : ?>
                <button class="btn btn-success btn-sm shadow-sm px-3" type="button" onclick="showPilihPettyCash()">
                    <i class="fa fa-plus me-1"></i> Tambah Pencatatan
                </button>
            <?php endif; ?>
            <button class="btn btn-primary btn-sm shadow-sm px-3" type="button" id="btn-buat-pelaporan" disabled>
                <i class="fa fa-file-text-o me-1"></i> <span id="btn-buat-pelaporan-text">Buat Pelaporan</span>
            </button>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <div class="table-responsive">
            <table id="table-pencatatan" class="table table-bordered table-striped table-hover align-middle w-100">
                <thead class="table-light text-center">
                    <tr>
                        <th width="35" class="text-center"><input type="checkbox" id="check-all" class="form-check-input"></th>
                        <th width="40" class="text-center">No</th>
                        <th width="140">No Pencatatan</th>
                        <th width="95" class="text-center">Tanggal</th>
                        <th width="85" class="text-center">Company</th>
                        <th width="130">Request By</th>
                        <th>Keterangan</th>
                        <th width="120" class="text-end">Nominal</th>
                        <th width="130" class="text-center">Status</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.card-body -->
</div>

<!-- Modal Pilih Petty Cash -->
<div class="modal fade" id="modal-pilih-petty-cash" tabindex="-1" aria-labelledby="modal-pilih-petty-cash-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold fs-6" id="modal-pilih-petty-cash-label">
                    <i class="fa fa-wallet me-2"></i>Pilih Master Petty Cash
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3 small">
                    <i class="fa fa-info-circle me-1"></i>Pilih petty cash yang akan digunakan untuk pencatatan pengeluaran:
                </p>
                <div id="petty-cash-list-container">
                    <div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" data-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Batal
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .petty-cash-card {
        display: flex;
        align-items: center;
        padding: 14px 18px;
        margin-bottom: 12px;
        border: 1px solid #e9ecef;
        border-left: 4px solid #198754;
        border-radius: 8px;
        background: #ffffff;
        text-decoration: none !important;
        color: #212529 !important;
        transition: all 0.2s ease-in-out;
    }

    .petty-cash-card:hover {
        border-left-color: #0d6efd;
        background: #f8fbff;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.12);
        transform: translateY(-2px);
    }

    .petty-cash-card .pc-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #198754, #0d6efd);
        color: #fff;
        border-radius: 50%;
        font-size: 18px;
        margin-right: 14px;
    }

    .petty-cash-card .pc-info {
        flex: 1;
    }

    .petty-cash-card .pc-name {
        font-size: 15px;
        font-weight: 600;
        margin: 0 0 2px 0;
        color: #212529;
    }

    .petty-cash-card .pc-desc {
        font-size: 12px;
        color: #6c757d;
        margin: 0 0 4px 0;
    }

    .petty-cash-card .pc-budget {
        display: inline-block;
        padding: 2px 8px;
        background: #e8f5e9;
        color: #198754;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .petty-cash-card:hover .pc-budget {
        background: #c8e6c9;
    }

    .petty-cash-card .pc-arrow {
        color: #adb5bd;
        font-size: 16px;
        margin-left: 10px;
        transition: transform 0.2s ease;
    }

    .petty-cash-card:hover .pc-arrow {
        color: #0d6efd;
        transform: translateX(4px);
    }

    .row-company-locked {
        background-color: #f8f9fa !important;
        opacity: 0.55;
    }

    .row-company-locked input[type="checkbox"] {
        cursor: not-allowed;
    }
</style>

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
        has_manage: <?= json_encode((bool)$has_manage) ?>,
        has_delete: <?= json_encode((bool)$has_delete) ?>
    };
    var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';

    function showPilihPettyCash() {
        $('#petty-cash-list-container').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i></div>');
        $('#modal-pilih-petty-cash').modal('show');

        $.ajax({
            type: 'GET',
            url: BASE_URL + 'get_petty_cash_list',
            dataType: 'json',
            success: function(response) {
                if (response.status == 1 && response.data && response.data.length > 0) {
                    var html = '';
                    $.each(response.data, function(i, item) {
                        html += '<a href="' + BASE_URL + 'create/' + item.id + '" class="petty-cash-card">';
                        html += '  <div class="pc-icon"><i class="fa fa-wallet"></i></div>';
                        html += '  <div class="pc-info">';
                        html += '    <p class="pc-name">' + $('<div>').text(item.nama).html() + '</p>';
                        html += '    <p class="pc-desc">' + $('<div>').text(item.keterangan || 'Tidak ada keterangan').html() + '</p>';
                        html += '    <span class="pc-budget"><i class="fa fa-money me-1"></i> Budget: Rp ' + item.total_budget + '</span>';
                        html += '  </div>';
                        html += '  <span class="pc-arrow"><i class="fa fa-chevron-right"></i></span>';
                        html += '</a>';
                    });
                    $('#petty-cash-list-container').html(html);
                } else {
                    $('#petty-cash-list-container').html('<div class="alert alert-warning mb-0"><i class="fa fa-exclamation-triangle me-1"></i> Tidak ada data Master Petty Cash aktif. Silakan hubungi admin.</div>');
                }
            },
            error: function() {
                $('#petty-cash-list-container').html('<div class="alert alert-danger mb-0"><i class="fa fa-times me-1"></i> Gagal memuat data dari server.</div>');
            }
        });
    }

    $(document).ready(function() {
        var selectedCompany = null;
        var selectedIds = [];

        var table = $('#table-pencatatan').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + 'get_data_pencatatan',
                type: 'POST'
            },
            order: [
                [3, 'desc']
            ],
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        var isDraft = (row.status === 'draft');
                        var inPelaporan = row.in_pelaporan;
                        if (!isDraft || inPelaporan) {
                            return '<input type="checkbox" class="form-check-input" disabled title="' + (inPelaporan ? 'Sudah masuk pelaporan' : 'Bukan status draft') + '">';
                        }
                        var isChecked = selectedIds.indexOf(row.id) !== -1;
                        var isLocked = selectedCompany && selectedCompany !== row.company;
                        return '<input type="checkbox" class="form-check-input check-item" value="' + row.id + '" data-company="' + row.company + '" ' + (isChecked ? 'checked' : '') + ' ' + (isLocked ? 'disabled' : '') + '>';
                    }
                },
                {
                    data: 'no',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'no_pencatatan',
                    render: function(data) {
                        return '<span class="fw-bold text-primary">' + $('<div>').text(data).html() + '</span>';
                    }
                },
                {
                    data: 'tanggal',
                    className: 'text-center',
                    render: function(data) {
                        if (!data) return '-';
                        var parts = data.split('-');
                        return (parts.length === 3) ? parts[2] + '/' + parts[1] + '/' + parts[0] : data;
                    }
                },
                {
                    data: 'company',
                    className: 'text-center',
                    render: function(data) {
                        var badgeClass = 'bg-secondary';
                        if (data === 'STM') badgeClass = 'bg-primary';
                        else if (data === 'VUCA') badgeClass = 'bg-info text-dark';
                        else if (data === 'SUSTAIN') badgeClass = 'bg-success';
                        return '<span class="badge ' + badgeClass + ' px-2 py-1">' + $('<div>').text(data).html() + '</span>';
                    }
                },
                {
                    data: 'request_by'
                },
                {
                    data: 'keterangan',
                    render: function(data) {
                        return data ? $('<div>').text(data).html() : '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'grand_total',
                    className: 'text-end fw-semibold',
                    render: function(data) {
                        return 'Rp ' + data;
                    }
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(data, type, row) {
                        var statusBadge = '';
                        switch (data) {
                            case 'draft':
                                statusBadge = '<span class="badge bg-warning text-dark px-2 py-1">Draft</span>';
                                break;
                            case 'waiting approval':
                                statusBadge = '<span class="badge bg-info text-dark px-2 py-1">Waiting Approval</span>';
                                break;
                            case 'approved':
                                statusBadge = '<span class="badge bg-success px-2 py-1">Approved</span>';
                                break;
                            case 'reject':
                                statusBadge = '<span class="badge bg-danger px-2 py-1">Reject</span>';
                                break;
                            default:
                                statusBadge = '<span class="badge bg-secondary px-2 py-1">' + data + '</span>';
                        }

                        var journalBadge = '';
                        if (row.journal_status === 'failed') {
                            journalBadge = '<br><span class="badge bg-danger mt-1" title="Jurnal Gagal"><i class="fa fa-exclamation-circle me-1"></i>Jurnal Gagal</span>';
                        }

                        return statusBadge + journalBadge;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center text-nowrap',
                    render: function(data, type, row) {
                        var btn = '<div class="btn-group btn-group-sm" role="group">';
                        btn += '<a href="' + BASE_URL + 'view/' + row.id + '" class="btn btn-outline-info btn-sm" title="Detail"><i class="fa fa-eye"></i></a>';

                        if (row.status === 'draft' && !row.in_pelaporan && PERMISSIONS.has_manage) {
                            btn += '<a href="' + BASE_URL + 'edit/' + row.id + '" class="btn btn-outline-warning btn-sm" title="Edit"><i class="fa fa-pen"></i></a>';
                        }

                        if (row.status === 'draft' && !row.in_pelaporan && PERMISSIONS.has_delete) {
                            btn += '<button type="button" class="btn btn-outline-danger btn-sm btn-delete" data-id="' + row.id + '" title="Hapus"><i class="fa fa-trash"></i></button>';
                        }

                        if (row.journal_status === 'failed' && PERMISSIONS.has_manage) {
                            btn += '<button type="button" class="btn btn-outline-secondary btn-sm btn-retry-journal" data-id="' + row.id + '" title="Retry Jurnal"><i class="fa fa-refresh"></i></button>';
                        }

                        btn += '</div>';
                        return btn;
                    }
                }
            ],
            drawCallback: function() {
                updateCheckboxesState();
            }
        });

        // Checkbox events
        $('#table-pencatatan').on('change', '.check-item', function() {
            var id = $(this).val();
            var company = $(this).data('company');

            if ($(this).is(':checked')) {
                if (selectedIds.length === 0) {
                    selectedCompany = company;
                }
                if (selectedIds.indexOf(id) === -1) {
                    selectedIds.push(id);
                }
            } else {
                var idx = selectedIds.indexOf(id);
                if (idx !== -1) selectedIds.splice(idx, 1);
                if (selectedIds.length === 0) {
                    selectedCompany = null;
                }
            }

            updateCheckboxesState();
        });

        $('#check-all').on('change', function() {
            var checked = $(this).is(':checked');
            $('.check-item:not(:disabled)').each(function() {
                var id = $(this).val();
                var comp = $(this).data('company');

                if (checked) {
                    if (!selectedCompany) selectedCompany = comp;
                    if (comp === selectedCompany && selectedIds.indexOf(id) === -1) {
                        selectedIds.push(id);
                        $(this).prop('checked', true);
                    }
                } else {
                    $(this).prop('checked', false);
                    var idx = selectedIds.indexOf(id);
                    if (idx !== -1) selectedIds.splice(idx, 1);
                }
            });

            if (selectedIds.length === 0) selectedCompany = null;
            updateCheckboxesState();
        });

        function updateCheckboxesState() {
            if (selectedCompany) {
                $('.check-item').each(function() {
                    if ($(this).data('company') !== selectedCompany) {
                        $(this).prop('disabled', true);
                        $(this).closest('tr').addClass('row-company-locked');
                    } else {
                        $(this).prop('disabled', false);
                        $(this).closest('tr').removeClass('row-company-locked');
                    }
                });
            } else {
                $('.check-item').prop('disabled', false).closest('tr').removeClass('row-company-locked');
            }

            if (selectedIds.length > 0) {
                $('#btn-buat-pelaporan').prop('disabled', false);
                $('#btn-buat-pelaporan-text').text('Buat Pelaporan (' + selectedIds.length + ')');
            } else {
                $('#btn-buat-pelaporan').prop('disabled', true);
                $('#btn-buat-pelaporan-text').text('Buat Pelaporan');
                $('#check-all').prop('checked', false);
            }
        }

        // Buat Pelaporan Click
        $('#btn-buat-pelaporan').on('click', function() {
            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'Konfirmasi Pelaporan',
                text: 'Buat pelaporan mingguan untuk ' + selectedIds.length + ' pencatatan terpilih (' + selectedCompany + ')?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-check me-1"></i> Ya, Buat Pelaporan',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang membuat pelaporan petty cash',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: BASE_URL + 'create_pelaporan',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            pencatatan_ids: selectedIds
                        },
                        success: function(resp) {
                            if (resp.status) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: resp.message,
                                    icon: 'success'
                                }).then(function() {
                                    if (resp.redirect_url) {
                                        window.location.href = resp.redirect_url;
                                    } else {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire('Gagal!', resp.message || 'Terjadi kesalahan.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        });

        // Delete Row
        $('#table-pencatatan').on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Pencatatan?',
                text: 'Data pencatatan yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-trash me-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: BASE_URL + 'delete/' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.status == 1 || resp.status === true) {
                                Swal.fire('Berhasil!', resp.msg || 'Data berhasil dihapus.', 'success');
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Gagal!', resp.msg || 'Gagal menghapus data.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    });
                }
            });
        });

        // Retry Journal
        $('#table-pencatatan').on('click', '.btn-retry-journal', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Retry Sinkronisasi Jurnal?',
                text: 'Sistem akan mencoba membuat ulang jurnal untuk pencatatan ini.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Retry',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: BASE_URL + 'retry_journal/' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.status == '1' || resp.status === 1 || resp.status === true) {
                                Swal.fire('Berhasil!', resp.message || 'Jurnal berhasil disinkronkan.', 'success');
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire('Gagal!', resp.message || 'Gagal menyinkronkan jurnal.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>