<?php
if (!function_exists('render_pr_progress_cell')) {
    $this->load->helper('pr_progress');
}
$ENABLE_ADD     = has_permission('PR_Stok.Add');
$ENABLE_MANAGE  = has_permission('PR_Stok.Manage');
$ENABLE_VIEW    = has_permission('PR_Stok.View');
$ENABLE_DELETE  = has_permission('PR_Stok.Delete');
?>

<!-- Driver.js (Interactive Feature Tour) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css" />

<style type="text/css">
    .no-pr {
        color: #b3261e;
        font-weight: 700;
        font-size: 13px;
    }

    .kat {
        color: #4b5563;
        font-size: 12px;
        font-weight: 600;
    }

    .pic {
        color: #4b5563;
        font-size: 12px;
    }

    .item-line {
        line-height: 1.5;
        font-size: 12px;
        color: #1f2937;
    }

    .qty-line {
        line-height: 1.5;
        text-align: right;
        font-size: 12px;
        font-weight: 600;
        color: #2563eb;
    }

    .progress-cell {
        min-width: 280px;
    }

    .steps {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 6px;
    }

    .step-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        transition: transform 0.15s ease;
    }

    .step-dot.done {
        background: #1f7a45;
    }

    .step-dot.active {
        background: #a1670d;
        box-shadow: 0 0 0 3px rgba(161, 103, 13, 0.2);
    }

    .step-dot.pending {
        background: #cbd2d9;
    }

    .step-dot.reject {
        background: #b32b2b;
        box-shadow: 0 0 0 3px rgba(179, 43, 43, 0.2);
    }

    .step-line {
        flex: 1;
        height: 2px;
        background: #e2e6ea;
    }

    .step-line.done {
        background: #1f7a45;
    }

    .step-labels {
        display: flex;
        justify-content: space-between;
        font-size: 9.5px;
        color: #8c96a3;
        margin-bottom: 6px;
        line-height: 1.2;
        gap: 2px;
    }

    .step-lbl {
        flex: 1;
        text-align: center;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .step-lbl:first-child {
        text-align: left;
    }

    .step-lbl:last-child {
        text-align: right;
    }

    .step-lbl.cur {
        color: #1f2937;
        font-weight: 700;
    }

    .step-lbl.rej {
        color: #b32b2b;
        font-weight: 700;
    }

    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.3;
    }

    .st-wait {
        background: #fdf1d9;
        color: #a1670d;
    }

    .st-final {
        background: #e2f6ea;
        color: #1f7a45;
    }

    .st-reject {
        background: #fbe4e4;
        color: #b32b2b;
    }

    .doc-meta {
        font-size: 11px;
        color: #6b7280;
        margin-top: 3px;
    }

    .reject-reason {
        font-size: 11px;
        color: #b32b2b;
        background: #fff5f5;
        border-left: 3px solid #b32b2b;
        padding: 3px 6px;
        margin-top: 4px;
        border-radius: 2px;
        line-height: 1.35;
    }

    .opts {
        display: flex;
        gap: 6px;
        justify-content: center;
    }

    .opt-btn {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        border: none;
        color: #fff !important;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .opt-btn:hover {
        opacity: 0.85;
        transform: translateY(-1px);
    }

    .b-view {
        background: #3b82f6;
    }

    .b-edit {
        background: #f59e0b;
    }

    .b-print {
        background: #10b981;
    }

    .b-del {
        background: #ef4444;
    }

    .legend-box {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 12px;
        font-size: 12px;
        color: #64748b;
        background: #f8fafc;
        padding: 7px 14px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    /* Driver.js Custom Popover Styling */
    .driver-popover.pr-tour-popover {
        max-width: 390px;
        border-radius: 10px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
        border: 1px solid #e2e8f0;
    }

    .driver-popover.pr-tour-popover .driver-popover-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }

    .driver-popover.pr-tour-popover .driver-popover-description {
        font-size: 13px;
        line-height: 1.5;
        color: #475569;
    }

    .pr-tour-flow {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 3px solid #3b82f6;
        border-radius: 6px;
        padding: 8px 10px;
        margin-top: 8px;
        font-size: 12px;
    }

    .pr-tour-flow-item {
        margin-bottom: 6px;
    }

    .pr-tour-flow-item:last-child {
        margin-bottom: 0;
    }

    .legend-box span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .dot-pending { background: #cbd2d9; }
    .dot-active  { background: #a1670d; }
    .dot-done    { background: #1f7a45; }
    .dot-reject  { background: #b32b2b; }
</style>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row align-items-center g-3">
                    <div class="col-md-7 col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                <i class="fa fa-shopping-cart fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-0">Purchasing Request / PR Stock</h5>
                                <small class="text-muted">Manage re-order points for non-material / office supply items and monitor the document approval flow</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-12 text-md-end">
                        <div class="d-inline-flex flex-wrap gap-2 justify-content-md-end">
                            <button type="button" id="btn-pr-tour" class="btn btn-outline-primary shadow-sm" title="Panduan Alur PR Stock">
                                <i class="fa fa-question-circle me-1"></i> Panduan Alur PR
                            </button>
                            <?php if ($ENABLE_ADD) : ?>
                                <a id="btn-add-pr" class="btn btn-primary shadow-sm" href="<?= base_url('request_pr_stok/add_new') ?>" title="Create PR Stock">
                                    <i class="fa fa-plus me-1"></i> Tambah PR Stok
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- LEGEND -->
                <div class="legend-box" id="pr-legend-box">
                    <span><span class="dot dot-pending"></span> Not at this stage yet</span>
                    <span><span class="dot dot-active"></span> In progress / waiting</span>
                    <span><span class="dot dot-done"></span> This stage is complete</span>
                    <span><span class="dot dot-reject"></span> Rejected</span>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table id="example1" class="table table-hover align-middle table-striped border-top mb-0" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 35px;">#</th>
                                <th class="text-center" style="width: 120px;">No. PR</th>
                                <th class="text-center" style="width: 140px;">PR Category</th>
                                <th class="text-center">Item Name</th>
                                <th class="text-center" style="min-width: 90px;">Qty (Pack)</th>
                                <th class="text-center" style="width: 110px;">Dibutuhkan</th>
                                <th class="text-center" style="width: 110px;">Request By</th>
                                <th class="text-center" style="width: 110px;">Request Date</th>
                                <th class="text-center no-sort" id="th-progress-pr" style="min-width: 290px;">Progress PR</th>
                                <th class="text-center no-sort" id="th-aksi-pr" style="width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (!empty($result)) {
                                foreach ($result as $row) {
                                    $get_detail_pr = $this->db->get_where('material_planning_base_on_produksi_detail', ['so_number' => $row->so_number])->result();

                                    $nm_detail = '';
                                    $qty_detail = '';
                                    foreach ($get_detail_pr as $item) {
                                        $this->db->select('a.stock_name, b.code');
                                        $this->db->from('accessories a');
                                        $this->db->join('ms_satuan b', 'b.id = a.id_unit_gudang', 'left');
                                        $this->db->where('a.id', $item->id_material);
                                        $get_stok_data = $this->db->get()->row();

                                        if (!empty($get_stok_data)) {
                                            $nm_detail .= '<div class="item-line">' . htmlspecialchars((string) $get_stok_data->stock_name) . '</div>';
                                            $qty_detail .= '<div class="qty-line">' . number_format((float) $item->propose_purchase, 2) . ' ' . ucfirst((string) $get_stok_data->code) . '</div>';
                                        }
                                    }

                                    $kategori_pr = [];
                                    $this->db->select('c.nm_category as kategori');
                                    $this->db->from('material_planning_base_on_produksi_detail a');
                                    $this->db->join('accessories b', 'b.id = a.id_material', 'left');
                                    $this->db->join('accessories_category c', 'c.id = b.id_category', 'left');
                                    $this->db->where('a.so_number', $row->so_number);
                                    $this->db->group_by('c.id');
                                    $get_kategori_pr = $this->db->get()->result();
                                    foreach ($get_kategori_pr as $item_kategori_pr) {
                                        $kategori_pr[] = $item_kategori_pr->kategori;
                                    }

                                    $kategori_text = !empty($kategori_pr) ? implode(', ', $kategori_pr ?? []) : '-';

                                    echo '<tr>';
                                    echo '<td class="text-center">' . $no . '</td>';
                                    echo '<td><span class="no-pr">' . strtoupper((string) $row->no_pr) . '</span></td>';
                                    echo '<td><span class="kat">' . strtoupper((string) $kategori_text) . '</span></td>';
                                    echo '<td>' . (!empty($nm_detail) ? $nm_detail : '-') . '</td>';
                                    echo '<td>' . (!empty($qty_detail) ? $qty_detail : '-') . '</td>';
                                    echo '<td class="text-center">' . (!empty($row->tgl_dibutuhkan) ? date('d M Y', strtotime((string) $row->tgl_dibutuhkan)) : '-') . '</td>';
                                    echo '<td class="text-center pic">' . htmlspecialchars($row->request_by ?? '-') . '</td>';
                                    echo '<td class="text-center">' . htmlspecialchars($row->request_date ?? '-') . '</td>';
                                    echo '<td class="progress-cell">' . render_pr_progress_cell('stock', $row) . '</td>';

                                    $view  = "<a href='" . site_url($this->uri->segment(1)) . '/detail_planning/' . $row->so_number . "' class='opt-btn b-view' title='PR Detail'><i class='fa fa-eye'></i></a>";
                                    $print = '<a href="' . site_url($this->uri->segment(1)) . '/PrintH2/' . $row->so_number . '" class="opt-btn b-print" title="Print PR" target="_blank"><i class="fa fa-print"></i></a>';

                                    $close = '';
                                    if ($ENABLE_DELETE) {
                                        $close = '<button type="button" class="opt-btn b-del close_pr_modal" data-so_number="' . $row->so_number . '" title="Close PR"><i class="fa fa-times"></i></button>';
                                    }

                                    echo '<td><div class="opts">' . $view . ' ' . $print . ' ' . $close . '</div></td>';
                                    echo '</tr>';

                                    $no++;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Closing PR -->
<div class="modal fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="modalClosingLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold" id="modalClosingLabel"><i class="fa fa-times-circle me-1"></i> Closing PR</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post" id="frm-data">
                <div class="modal-body p-4" id="ModalView">
                    <div class="text-center text-muted py-3">Loading data...</div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger shadow-sm"><i class="fa fa-check me-1"></i> Close PR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Driver.js Script -->
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#example1').DataTable({
            "destroy": true,
            "order": [
                [7, "desc"]
            ],
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false,
            }],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No data to display",
                "zeroRecords": "No matching data",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Continue",
                    "previous": "Sebelum"
                }
            }
        });
    });

    function startPrStockTour() {
        if (!window.driver || !window.driver.js || typeof window.driver.js.driver !== 'function') {
            if (typeof swal === 'function') {
                swal('Info', 'Library panduan (Driver.js) gagal dimuat. Pastikan koneksi internet tersedia.', 'info');
            } else {
                alert('Library panduan (Driver.js) gagal dimuat.');
            }
            return;
        }

        var driverFactory = window.driver.js.driver;
        var steps = [{
            popover: {
                title: 'Alur PR Stock',
                description:
                    'Berikut skema proses persetujuan hingga pembayaran untuk <strong>PR Stock</strong>:' +
                    '<div class="pr-tour-flow">' +
                        '<div class="pr-tour-flow-item"><strong>1. Approval Awal:</strong><br>' +
                        '<span class="badge bg-secondary">PR — Direktur</span> <small class="text-muted">(1 level approval tanpa finance)</small> &rarr; <span class="badge bg-info text-dark">Metode Pembelian</span></div>' +
                        '<div class="pr-tour-flow-item"><strong>2a. Jalur Direct Payment:</strong><br>' +
                        '<span class="badge bg-warning text-dark">Direct Payment</span> &rarr; <span class="badge bg-success">Request Payment</span> <small class="text-muted">(final)</small></div>' +
                        '<div class="pr-tour-flow-item"><strong>2b. Jalur Cash Advance:</strong><br>' +
                        '<span class="badge bg-warning text-dark">Cash Advance</span> &rarr; <span class="badge bg-primary">CA Finance</span> &rarr; <span class="badge bg-primary">CA Director</span> &rarr; <span class="badge bg-success">Request Payment</span></div>' +
                    '</div>',
                side: 'bottom',
                align: 'center'
            }
        }];

        if ($('#btn-add-pr').length) {
            steps.push({
                element: '#btn-add-pr',
                popover: {
                    title: 'Buat Pengajuan PR Stok',
                    description: 'Klik tombol ini untuk membuat pengajuan <strong>PR Stock</strong> baru (non-material / perlengkapan) beserta item dan jumlah yang dibutuhkan.',
                    side: 'bottom',
                    align: 'end'
                }
            });
        }

        steps.push({
            element: '#pr-legend-box',
            popover: {
                title: 'Arti Kode Warna Status',
                description:
                    'Setiap titik (dot) pada pipeline memiliki arti status:<br>' +
                    '<ul class="mb-0 ps-3 mt-1">' +
                        '<li><strong>Abu-abu:</strong> Belum mencapai tahap ini.</li>' +
                        '<li><strong>Kuning/Amber:</strong> Sedang menunggu / dalam proses.</li>' +
                        '<li><strong>Hijau:</strong> Tahap telah disetujui / selesai.</li>' +
                        '<li><strong>Merah:</strong> Dokumen ditolak (<em>Rejected</em>).</li>' +
                    '</ul>',
                side: 'bottom',
                align: 'start'
            }
        });

        var progressTarget = $('#example1 tbody tr:first .progress-cell').length
            ? '#example1 tbody tr:first .progress-cell'
            : '#th-progress-pr';

        steps.push({
            element: progressTarget,
            popover: {
                title: 'Monitoring Progress PR',
                description: 'Kolom ini menampilkan posisi dokumen PR secara real-time, mulai dari approval Direktur, pemilihan metode pembelian, hingga Request Payment. Jika ditolak, alasan penolakan juga muncul di sini.',
                side: 'left',
                align: 'center'
            }
        });

        var aksiTarget = $('#example1 tbody tr:first .opts').length
            ? '#example1 tbody tr:first .opts'
            : '#th-aksi-pr';

        steps.push({
            element: aksiTarget,
            popover: {
                title: 'Aksi Dokumen PR',
                description:
                    'Gunakan tombol aksi di kolom ini:<br>' +
                    '<ul class="mb-0 ps-3 mt-1">' +
                        '<li><strong>Biru (Mata):</strong> Lihat detail PR.</li>' +
                        '<li><strong>Hijau (Print):</strong> Cetak dokumen PR.</li>' +
                        '<li><strong>Merah (Silang):</strong> Close PR jika sudah selesai atau dibatalkan.</li>' +
                    '</ul>',
                side: 'left',
                align: 'center'
            }
        });

        var driverObj = driverFactory({
            showProgress: true,
            animate: true,
            allowClose: true,
            popoverClass: 'pr-tour-popover',
            nextBtnText: 'Lanjut &rarr;',
            prevBtnText: '&larr; Kembali',
            doneBtnText: 'Selesai',
            steps: steps
        });

        driverObj.drive();
    }

    $(document).on('click', '#btn-pr-tour', function() {
        startPrStockTour();
    });

    $(document).on('click', '.close_pr_modal', function() {
        var so_number = $(this).data('so_number');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'close_pr_modal',
            data: {
                'so_number': so_number
            },
            cache: false,
            success: function(result) {
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Failed to load the PR closing form. Please try again.',
                    type: 'error'
                });
            }
        });
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        var data = new FormData($('#frm-data')[0]);
        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'close_pr',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(result) {
                if (result.status == '1') {
                    $('#dialog-popup').modal('hide');
                    swal({
                        title: 'Success !',
                        text: 'PR closed successfully.',
                        type: 'success'
                    }, function() {
                        location.reload(true);
                    });
                } else {
                    swal({
                        title: 'Failed !',
                        text: 'Failed to close PR.',
                        type: 'warning'
                    });
                }
            },
            error: function() {
                swal({
                    title: 'Error !',
                    text: 'A system error occurred.',
                    type: 'error'
                });
            }
        });
    });
</script>
