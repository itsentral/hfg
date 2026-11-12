<?php
if (!function_exists('render_pr_progress_cell')) {
    $this->load->helper('pr_progress');
}
$ENABLE_ADD     = has_permission('PR_Stok.Add');
$ENABLE_MANAGE  = has_permission('PR_Stok.Manage');
$ENABLE_VIEW    = has_permission('PR_Stok.View');
$ENABLE_DELETE  = has_permission('PR_Stok.Delete');
?>

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

    .flowmap-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #3b82f6;
        border-radius: 8px;
        padding: 14px 18px;
        margin-bottom: 16px;
        font-size: 12.5px;
        color: #475569;
    }

    .flow-trunk {
        font-size: 13px;
        color: #1e293b;
        margin-top: 4px;
        padding-bottom: 2px;
        font-weight: 500;
    }

    .flow-branches {
        margin-top: 8px;
        padding-left: 14px;
        border-left: 2px dashed #cbd5e1;
    }

    .branch-row {
        margin: 5px 0;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    .branch-tag {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        background: #fef3c7;
        color: #92400e;
    }

    .branch-note {
        color: #64748b;
        font-size: 11.5px;
        margin-left: 4px;
    }

    .legend-box {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        font-size: 12px;
        color: #64748b;
        background: #fff;
        padding: 8px 14px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
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
                                <h5 class="card-title fw-bold text-dark mb-0">Purchasing Request / PR Stok</h5>
                                <small class="text-muted">Kelola re-order point barang non-material / ATK dan pantau alur approval dokumen</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-12 text-md-end">
                        <?php if ($ENABLE_ADD) : ?>
                            <a class="btn btn-primary shadow-sm" href="<?= base_url('request_pr_stok/add_new') ?>" title="Buat PR Stok">
                                <i class="fa fa-plus me-1"></i> Tambah PR Stok
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- FLOWMAP CARD -->
                <div class="flowmap-box shadow-none">
                    <div class="fw-bold text-dark mb-1"><i class="fa fa-sitemap text-primary me-1"></i> Alur PR Stock:</div>
                    <div class="flow-trunk">
                        <span class="badge bg-secondary">PR — Direktur</span> <span class="branch-note">(1 level persetujuan, tanpa finance)</span> &rarr; <span class="badge bg-info text-dark">Metode Pembelian</span>
                    </div>
                    <div class="flow-branches">
                        <div class="branch-row">
                            <span class="branch-tag">Direct Payment</span> &rarr; <span class="badge bg-success">Request Payment</span>
                            <span class="branch-note">(langsung proses pembayaran — tahap final)</span>
                        </div>
                        <div class="branch-row">
                            <span class="branch-tag">Kasbon</span> &rarr; <span class="badge bg-primary">Kasbon Finance</span> &rarr; <span class="badge bg-primary">Kasbon Direktur</span> &rarr; <span class="badge bg-success">Request Payment</span>
                            <span class="branch-note">(setelah approval 2 level kasbon — tahap final)</span>
                        </div>
                    </div>
                </div>

                <!-- LEGEND -->
                <div class="legend-box">
                    <span><span class="dot dot-pending"></span> Belum sampai tahap ini</span>
                    <span><span class="dot dot-active"></span> Sedang berjalan / menunggu</span>
                    <span><span class="dot dot-done"></span> Selesai tahap ini</span>
                    <span><span class="dot dot-reject"></span> Ditolak</span>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table id="example1" class="table table-hover align-middle table-striped border-top mb-0" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 35px;">#</th>
                                <th class="text-center" style="width: 120px;">No. PR</th>
                                <th class="text-center" style="width: 140px;">Kategori PR</th>
                                <th class="text-center">Nama Barang</th>
                                <th class="text-center" style="min-width: 90px;">Qty (Pack)</th>
                                <th class="text-center" style="width: 110px;">Dibutuhkan</th>
                                <th class="text-center" style="width: 110px;">Request By</th>
                                <th class="text-center" style="width: 110px;">Request Date</th>
                                <th class="text-center no-sort" style="min-width: 290px;">Progress PR</th>
                                <th class="text-center no-sort" style="width: 110px;">Aksi</th>
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
                                            $nm_detail .= '<div class="item-line">' . htmlspecialchars($get_stok_data->stock_name) . '</div>';
                                            $qty_detail .= '<div class="qty-line">' . number_format($item->propose_purchase, 2) . ' ' . ucfirst($get_stok_data->code) . '</div>';
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

                                    $kategori_text = !empty($kategori_pr) ? implode(', ', $kategori_pr) : '-';

                                    echo '<tr>';
                                    echo '<td class="text-center">' . $no . '</td>';
                                    echo '<td><span class="no-pr">' . strtoupper($row->no_pr) . '</span></td>';
                                    echo '<td><span class="kat">' . strtoupper($kategori_text) . '</span></td>';
                                    echo '<td>' . (!empty($nm_detail) ? $nm_detail : '-') . '</td>';
                                    echo '<td>' . (!empty($qty_detail) ? $qty_detail : '-') . '</td>';
                                    echo '<td class="text-center">' . (!empty($row->tgl_dibutuhkan) ? date('d M Y', strtotime($row->tgl_dibutuhkan)) : '-') . '</td>';
                                    echo '<td class="text-center pic">' . htmlspecialchars($row->request_by ?? '-') . '</td>';
                                    echo '<td class="text-center">' . htmlspecialchars($row->request_date ?? '-') . '</td>';
                                    echo '<td class="progress-cell">' . render_pr_progress_cell('stock', $row) . '</td>';

                                    $view  = "<a href='" . site_url($this->uri->segment(1)) . '/detail_planning/' . $row->so_number . "' class='opt-btn b-view' title='Detail PR'><i class='fa fa-eye'></i></a>";
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
                    <div class="text-center text-muted py-3">Memuat data...</div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger shadow-sm"><i class="fa fa-check me-1"></i> Close PR</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data yang ditampilkan",
                "zeroRecords": "Tidak ada data yang cocok",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Lanjut",
                    "previous": "Sebelum"
                }
            }
        });
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
                    text: 'Gagal memuat form closing PR. Silakan coba lagi.',
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
                        text: 'PR berhasil di-close.',
                        type: 'success'
                    }, function() {
                        location.reload(true);
                    });
                } else {
                    swal({
                        title: 'Failed !',
                        text: 'PR gagal di-close.',
                        type: 'warning'
                    });
                }
            },
            error: function() {
                swal({
                    title: 'Error !',
                    text: 'Terjadi kesalahan sistem.',
                    type: 'error'
                });
            }
        });
    });
</script>
