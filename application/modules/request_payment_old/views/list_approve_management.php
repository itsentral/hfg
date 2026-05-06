<?php
$ENABLE_ADD     = has_permission('Approval_Request_Payment_Management.Add');
$ENABLE_MANAGE  = has_permission('Approval_Request_Payment_Management.Manage');
$ENABLE_VIEW    = has_permission('Approval_Request_Payment_Management.View');
$ENABLE_DELETE  = has_permission('Approval_Request_Payment_Management.Delete');

$count_transport = 0;
$count_kasbon = 0;
$count_expense = 0;
$count_periodik = 0;
$count_pembayaran_po = 0;
$count_direct_payment = 0;

foreach ($data as $item) :
    if ($item->tipe == 'transport') {
        $count_transport += 1;
    }
    if ($item->tipe == 'kasbon') {
        $count_kasbon += 1;
    }
    if ($item->tipe == 'expense') {
        if (strpos($item->no_doc, 'ER-') !== false || strpos($item->no_doc, 'ROS-') !== false) {
            $count_expense += 1;
        } else {
            $count_pembayaran_po += 1;
        }
    }
    if ($item->tipe == 'periodik') {
        $count_periodik += 1;
    }
    if ($item->tipe == 'direct_payment') {
        $count_direct_payment += 1;
    }
endforeach;
?>
<script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')); ?>
<div class="card">
    <div class="card-body">
        <div class="row col-md-12 mb-3">
            <div class="col-md-2 col-sm-6">
                <div class="panel panel-metric">
                    <div class="panel-heading bg-green">
                        <div class="heading-wrap">
                            <i class="fa fa-bus heading-icon"></i>
                            <span>Transportasi</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="metric-value"><?= $count_transport ?></div>
                    </div>
                    <div class="panel-footer">
                        <button type="button" class="btn btn-sm btn-icon-edit btn_view_req" data-val="transportasi">
                            <i class="fa fa-eye"></i> View
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6">
                <div class="panel panel-metric">
                    <div class="panel-heading bg-warning">
                        <div class="heading-wrap">
                            <i class="fa fa-credit-card heading-icon"></i>
                            <span>Kasbon</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="metric-value"><?= $count_kasbon ?></div>
                    </div>
                    <div class="panel-footer">
                        <button type="button" class="btn btn-sm btn-icon-edit btn_view_req" data-val="kasbon">
                            <i class="fa fa-eye"></i> View
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6">
                <div class="panel panel-metric">
                    <div class="panel-heading bg-blue">
                        <div class="heading-wrap">
                            <i class="fas fa-hand-holding-usd heading-icon"></i>
                            <span>Expense</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="metric-value"><?= $count_expense ?></div>
                    </div>
                    <div class="panel-footer">
                        <button type="button" class="btn btn-sm btn-icon-edit btn_view_req" data-val="expense">
                            <i class="fa fa-eye"></i> View
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6">
                <div class="panel panel-metric">
                    <div class="panel-heading bg-danger">
                        <div class="heading-wrap">
                            <i class="fas fa-business-time heading-icon"></i>
                            <span>Periodik</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="metric-value"><?= $count_periodik ?></div>
                    </div>
                    <div class="panel-footer">
                        <button type="button" class="btn btn-sm btn-icon-edit btn_view_req" data-val="periodik">
                            <i class="fa fa-eye"></i> View
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6">
                <div class="panel panel-metric">
                    <div class="panel-heading bg-light-blue">
                        <div class="heading-wrap">
                            <i class="fa fa-shopping-bag heading-icon"></i>
                            <span>Pembayaran PO</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="metric-value"><?= $count_pembayaran_po ?></div>
                    </div>
                    <div class="panel-footer">
                        <button type="button" class="btn btn-sm btn-icon-edit btn_view_req" data-val="pembayaran_po">
                            <i class="fa fa-eye"></i> View
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6">
                <div class="panel panel-metric">
                    <div class="panel-heading bg-grey">
                        <div class="heading-wrap">
                            <i class="fa fa-bolt heading-icon"></i>
                            <span>Direct Payment</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="metric-value"><?= $count_direct_payment ?></div>
                    </div>
                    <div class="panel-footer">
                        <button type="button" class="btn btn-sm btn-icon-edit btn_view_req" data-val="direct_payment">
                            <i class="fa fa-eye"></i> View
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12 list_transportasi" style="display: none;">
                    <hr class="mt-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3>Transportasi</h3>
                        <!-- <span class="text-muted small">(*) wajib diisi</span> -->
                    </div>
                    <table class="table table-bordered" id="table_transportasi" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center">No Dokument</th>
                                <th class="text-center">Request By</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Kepeluan</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-center">Nilai Pengajuan</th>
                                <th class="text-center">Tanggal Pembayaran</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($data as $item_transportasi) :
                                if ($item_transportasi->tipe == 'transport') {
                                    echo '<tr>';
                                    echo '<td>' . $item_transportasi->no_doc . '</td>';
                                    echo '<td>' . $item_transportasi->nama . '</td>';
                                    echo '<td>' . $item_transportasi->tgl_doc . '</td>';
                                    echo '<td>' . $item_transportasi->keperluan . '</td>';
                                    echo '<td>' . $item_transportasi->tipe . '</td>';
                                    echo '<td class="text-right">' . number_format($item_transportasi->jumlah) . '</td>';
                                    echo '<td>' . $item_transportasi->tanggal . '</td>';
                                    echo '<td>';
                                    $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_transportasi->no_doc, 'ids' => $item_transportasi->ids])->row_array();

                                    if ($item_transportasi->status == '0' || empty($get_sts_payment)) {
                                        if ($item_transportasi->status == '9') {
                                            echo '<span class="badge rounded-pill bg-danger">Rejected</label>';
                                        } else {
                                            echo '<span class="badge rounded-pill bg-info">Open</label>';
                                        }
                                    } elseif ($get_sts_payment['status'] == 1) {
                                        echo '<span class="badge rounded-pill bg-warning">Process</label>';
                                    } elseif ($get_sts_payment['status'] == 2) {
                                        echo '<span class="badge rounded-pill bg-secondary">Close</label>';
                                    } else {
                                        echo '<span class="badge rounded-pill bg-secondary"><span class="text-muted">Undefined</span></label>';
                                    }
                                    echo '</td>';
                                    echo '<td>';
                                    if ($ENABLE_MANAGE) : ?>
                                        <div class="text-center"><a href="<?= base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_transportasi->tipe . '&id=' . $item_transportasi->id . '&nilai=' . $item_transportasi->jumlah); ?>" name="save" class="btn btn-primary btn-sm"><i class="fas fa-check-double">&nbsp;</i>Approve</a></div>
                                        <!-- <input type="checkbox" name="status[]" id="status_<?= $numb ?>" value="<?= $item_transportasi->id ?>"> -->
                            <?php endif;
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12 list_kasbon" style="display: none;">
                    <hr class="mt-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3>Kasbon</h3>
                        <a href="<?= base_url('approval_request_payment/export_excel_kasbon_checker/?tingkat=1') ?>" class="btn btn-sm btn-success"><i class="fa fa-files"></i> Export Excel</a>
                    </div>
                    <table class="table table-bordered" id="table_kasbon" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center">No Dokument</th>
                                <th class="text-center">Request By</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Kepeluan</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-center">Nilai Pengajuan</th>
                                <th class="text-center">Tanggal Pembayaran</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($data as $item_kasbon) :
                                if ($item_kasbon->tipe == 'kasbon') {
                                    $get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $item_kasbon->no_doc))->row();
                                    $get_req_payment = $this->db->get_where('request_payment', ['no_doc' => $item_kasbon->no_doc])->result();
                                    // $get_kasbon_sendigs = $this->db->get_where('tr_kasbon', ['no_doc' => $item_kasbon->no_doc])->row();

                                    // $no_kasbon_consultant = (!empty($get_kasbon_sendigs)) ? $get_kasbon_sendigs->no_kasbon_consultant : '';

                                    // $get_kasbon_header = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', array('id' => $no_kasbon_consultant))->row();

                                    echo '<tr>';
                                    echo '<td>' . $item_kasbon->no_doc . '</td>';
                                    echo '<td>' . $item_kasbon->nama . '</td>';
                                    echo '<td>' . $item_kasbon->tgl_doc . '</td>';
                                    echo '<td>' . $item_kasbon->keperluan . '</td>';
                                    echo '<td>' . $item_kasbon->tipe . '</td>';
                                    echo '<td class="text-right">' . number_format($item_kasbon->jumlah) . '</td>';
                                    echo '<td>' . $item_kasbon->tanggal . '</td>';
                                    echo '<td>';
                                    $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_kasbon->no_doc, 'ids' => $item_kasbon->ids])->row_array();

                                    if ($item_kasbon->status == '0' || empty($get_sts_payment)) {
                                        if ($item_kasbon->status == '9') {
                                            echo '<span class="badge rounded-pill bg-danger">Rejected</label>';
                                        } else {
                                            echo '<span class="badge rounded-pill  bg-info">Open</label>';
                                        }
                                    } elseif ($get_sts_payment['status'] == 1) {
                                        echo '<span class="badge rounded-pill  bg-warning">Process</label>';
                                    } elseif ($get_sts_payment['status'] == 2) {
                                        echo '<span class="badge rounded-pill  bg-danger">Close</label>';
                                    } else {
                                        echo '<span class="badge rounded-pill  bg-secondary"><span class="text-muted">Undefined</span></label>';
                                    }
                                    echo '</td>';
                                    echo '<td>';
                                    echo '<div class="text-center">';
                                    if ($ENABLE_MANAGE) {

                                        $approve_query = http_build_query([
                                            'type'  => $item_kasbon->tipe,
                                            'id'    => $item_kasbon->id,
                                            'nilai' => $item_kasbon->jumlah,
                                        ]);
                                        $approve_url = base_url($this->uri->segment(1) . '/approval_payment?' . $approve_query);

                                        echo '<a href="' . $approve_url . '" name="save" class="btn btn-primary btn-sm">';
                                        echo '<i class="fas fa-check-double"></i>&nbsp;Approve';
                                        echo '</a>';
                                    }

                                    if (count($get_req_payment) > 0 && !is_null($get_req_payment[0]->app_checker)) {
                                        $no_doc_safe = urlencode(str_replace('/', '|', $item_kasbon->no_doc));

                                        $print_url = base_url('approval_request_payment/print_kasbon/' . $no_doc_safe);
                                        $view_url  = base_url('kasbon/view_kasbon/' . $no_doc_safe);

                                        echo '<a href="' . $print_url . '" class="btn btn-info btn-sm" target="_blank" title="Print PDF">';
                                        echo '<i class="fa fa-print"></i>&nbsp;Print';
                                        echo '</a> ';

                                        echo '<a href="' . $view_url . '" class="btn btn-secondary btn-sm" target="_blank" title="View Kasbon">';
                                        echo '<i class="fa fa-eye"></i>&nbsp;View';
                                        echo '</a>';
                                    }
                                    echo '</div>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12 list_expense" style="display: none;">
                    <hr class="mt-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3>Expense</h3>
                        <a href="<?= base_url('approval_request_payment/export_excel_expense_checker/?tingkat=1') ?>" class="btn btn-sm btn-success"><i class="fa fa-files"></i> Export Excel</a>
                    </div>
                    <table class="table table-bordered" id="table_expense" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center">No Dokument</th>
                                <th class="text-center">Request By</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Kepeluan</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-center">Nilai Pengajuan</th>
                                <th class="text-center">Tanggal Pembayaran</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($data as $item_expense) :
                                if ($item_expense->tipe == 'expense') {
                                    $tipe = ucfirst($item_expense->tipe);
                                    $get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_expense->no_doc])->row_array();
                                    if ($get_expense['exp_inv_po'] == '1') {
                                        $tipe = 'Pembayaran PO';
                                    }
                                    if (strpos($item_expense->no_doc, 'ROS') === true) {
                                        $tipe = 'Pembayaran PIB';
                                    }
                                    if (strpos($item_expense->no_doc, 'ER-') !== false || strpos($item_expense->no_doc, 'ROS-') !== false) {
                                        echo '<tr>';
                                        echo '<td>' . $item_expense->no_doc . '</td>';
                                        echo '<td>' . $item_expense->nama . '</td>';
                                        echo '<td>' . $item_expense->tgl_doc . '</td>';
                                        echo '<td>' . $item_expense->keperluan . '</td>';
                                        echo '<td>' . $tipe . '</td>';
                                        echo '<td class="text-right">' . number_format($item_expense->jumlah) . '</td>';
                                        echo '<td>' . $item_expense->tanggal . '</td>';
                                        echo '<td>';
                                        $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_expense->no_doc, 'ids' => $item_expense->ids])->row_array();

                                        if ($item_expense->status == '0' || empty($get_sts_payment)) {
                                            if ($item_expense->status == '9') {
                                                echo '<span class="badge rounded-pill  bg-danger">Rejected</label>';
                                            } else {
                                                echo '<span class="badge rounded-pill  bg-info">Open</label>';
                                            }
                                        } elseif ($get_sts_payment['status'] == 1) {
                                            echo '<span class="badge rounded-pill  bg-warning">Process</label>';
                                        } elseif ($get_sts_payment['status'] == 2) {
                                            echo '<span class="badge rounded-pill  bg-danger">Close</label>';
                                        } else {
                                            echo '<span class="badge rounded-pill  bg-secondary"><span class="text-muted">Undefined</span></label>';
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        echo '
                                                <div class="text-center">
                                                    <a href="' . base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_expense->tipe . '&id=' . $item_expense->id . '&nilai=' . $item_expense->jumlah) . '" class="btn btn-sm btn-primary"><i class="fas fa-check-double">&nbsp;</i>Approve</a>
                                                </div>
                                            ';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12 list_periodik" style="display: none;">
                    <hr class="mt-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3>Periodik</h3>
                    </div>
                    <table class="table table-bordered" id="table_periodik" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center">No Dokument</th>
                                <th class="text-center">Request By</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Kepeluan</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-center">Nilai Pengajuan</th>
                                <th class="text-center">Tanggal Pembayaran</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($data as $item_periodik) :
                                if ($item_periodik->tipe == 'periodik') {
                                    echo '<tr>';
                                    echo '<td>' . $item_periodik->no_doc . '</td>';
                                    echo '<td>' . $item_periodik->nama . '</td>';
                                    echo '<td>' . $item_periodik->tgl_doc . '</td>';
                                    echo '<td>' . $item_periodik->keperluan . '</td>';
                                    echo '<td>' . $item_periodik->tipe . '</td>';
                                    echo '<td class="text-right">' . number_format($item_periodik->jumlah) . '</td>';
                                    echo '<td>' . $item_periodik->tanggal . '</td>';
                                    echo '<td>';
                                    $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_periodik->no_doc, 'ids' => $item_periodik->ids])->row_array();

                                    if ($item_periodik->status == '0' || empty($get_sts_payment)) {
                                        if ($item_periodik->status == '9') {
                                            echo '<span class="badge rounded-pill  bg-danger">Rejected</label>';
                                        } else {
                                            echo '<span class="badge rounded-pill  bg-info">Open</label>';
                                        }
                                    } elseif ($get_sts_payment['status'] == 1) {
                                        echo '<span class="badge rounded-pill  bg-warning">Process</label>';
                                    } elseif ($get_sts_payment['status'] == 2) {
                                        echo '<span class="badge rounded-pill  bg-danger">Close</label>';
                                    } else {
                                        echo '<span class="badge rounded-pill  bg-secondary"><span class="text-muted">Undefined</span></label>';
                                    }
                                    echo '</td>';
                                    echo '<td>';
                                    if ($ENABLE_MANAGE) : ?>
                                        <div class="text-center"><a href="<?= base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_periodik->tipe . '&id=' . $item_periodik->id . '&nilai=' . $item_periodik->jumlah); ?>" name="save" class="btn btn-primary btn-sm"><i class="fas fa-check-double">&nbsp;</i>Approve</a></div>
                                        <!-- <input type="checkbox" name="status[]" id="status_<?= $numb ?>" value="<?= $item_periodik->id ?>"> -->
                            <?php endif;
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12 list_pembayaran_po" style="display: none;">
                    <hr class="mt-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3>Pembayaran PO</h3>
                    </div>
                    <table class="table table-bordered" id="table_pembayaran_po" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center">No Dokumen</th>
                                <th class="text-center">No Invoice</th>
                                <th class="text-center">Request By</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Kepeluan</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-center">Nilai Pengajuan</th>
                                <th class="text-center">Tanggal Pembayaran</th>
                                <th class="text-center">Keterangan PO</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($data as $item_expense) :
                                $no_invoice = (isset($list_no_invoice[$item_expense->no_doc])) ? $list_no_invoice[$item_expense->no_doc] : '';
                                if ($item_expense->tipe == 'expense') {
                                    $tipe = ucfirst($item_expense->tipe);
                                    $get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_expense->no_doc])->row_array();
                                    if ($get_expense['exp_inv_po'] == '1') {
                                        $tipe = 'Pembayaran PO';
                                    }
                                    if (strpos($item_expense->no_doc, 'ROS-') !== false) {
                                        $tipe = 'Pembayaran PIB';
                                    }

                                    $exp_id_po = explode(',', $get_expense['id_po']);

                                    $po_note = [];
                                    $this->db->select('note');
                                    $this->db->from('tr_purchase_order');
                                    $this->db->where_in('no_surat', $exp_id_po);
                                    $get_po_note = $this->db->get()->result();

                                    foreach ($get_po_note as $item_po_note) {
                                        $po_note[] = $item_po_note->note;
                                    }

                                    $po_note = implode(', ', $po_note);


                                    if ($get_expense['exp_inv_po'] == '1') {
                                        echo '<tr>';
                                        echo '<td>' . $item_expense->no_doc . '</td>';
                                        echo '<td>' . $no_invoice . '</td>';
                                        echo '<td>' . $item_expense->nama . '</td>';
                                        echo '<td>' . $item_expense->tgl_doc . '</td>';
                                        echo '<td>' . $item_expense->keperluan . '</td>';
                                        echo '<td>' . $tipe . '</td>';
                                        echo '<td class="text-right">' . number_format($item_expense->jumlah) . '</td>';
                                        echo '<td>' . $item_expense->tanggal . '</td>';
                                        echo '<td>' . $po_note . '</td>';
                                        echo '<td>';
                                        $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_expense->no_doc, 'ids' => $item_expense->ids])->row_array();

                                        if ($item_expense->status == '0' || empty($get_sts_payment)) {
                                            if ($item_expense->status == '9') {
                                                echo '<span class="badge rounded-pill  bg-danger">Rejected</label>';
                                            } else {
                                                echo '<span class="badge rounded-pill  bg-info">Open</label>';
                                            }
                                        } elseif ($get_sts_payment['status'] == 1) {
                                            echo '<span class="badge rounded-pill  bg-warning">Process</label>';
                                        } elseif ($get_sts_payment['status'] == 2) {
                                            echo '<span class="badge rounded-pill  bg-danger">Close</label>';
                                        } else {
                                            echo '<span class="badge rounded-pill  bg-secondary"><span class="text-muted">Undefined</span></label>';
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        if ($ENABLE_MANAGE) : ?>
                                            <a href="<?= base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_expense->tipe . '&id=' . $item_expense->id . '&nilai=' . $item_expense->jumlah); ?>" name="save" class="btn btn-primary btn-sm"><i class="fas fa-check-double"></i></a>

                                            <a href="javascript:void(0);" class="btn btn-sm btn-info view_receive_invoice" data-id_invoice="<?= $item_expense->no_doc ?>"><i class="fa fa-eye"></i></a>
                                            <!-- <input type="checkbox" name="status[]" id="status_<?= $numb ?>" value="<?= $item_expense->id ?>"> -->
                            <?php endif;
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12 list_direct_payment" style="display: none;">
                    <hr class="mt-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3>Direct Payment</h3>
                    </div>
                    <table class="table table-bordered" id="table_direct_payment" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center">No Dokument</th>
                                <th class="text-center">Request By</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Kepeluan</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-center">Nilai Pengajuan</th>
                                <th class="text-center">Tanggal Pembayaran</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="150px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($data as $item_dp) :
                                if ($item_dp->tipe == 'direct_payment') {

                                    echo '<tr>';
                                    echo '<td>' . $item_dp->no_doc . '</td>';
                                    echo '<td>' . $item_dp->nama . '</td>';
                                    echo '<td>' . $item_dp->tgl_doc . '</td>';
                                    echo '<td>' . $item_dp->keperluan . '</td>';
                                    echo '<td>' . $item_dp->tipe . '</td>';
                                    echo '<td class="text-right">' . number_format($item_dp->jumlah) . '</td>';
                                    echo '<td>' . $item_dp->tgl_doc . '</td>';
                                    echo '<td>';
                                    $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_dp->no_doc, 'ids' => $item_dp->ids])->row_array();

                                    if ($item_dp->status == '0' || empty($get_sts_payment)) {
                                        if ($item_dp->status == '9') {
                                            echo '<span class="badge rounded-pill  bg-danger">Rejected</label>';
                                        } else {
                                            echo '<span class="badge rounded-pill  bg-info">Open</label>';
                                        }
                                    } elseif ($get_sts_payment['status'] == 1) {
                                        echo '<span class="badge rounded-pill  bg-warning">Process</label>';
                                    } elseif ($get_sts_payment['status'] == 2) {
                                        echo '<span class="badge rounded-pill  bg-danger">Close</label>';
                                    } else {
                                        echo '<span class="badge rounded-pill  bg-secondary"><span class="text-muted">Undefined</span></label>';
                                    }
                                    echo '</td>';
                                    echo '<td>';
                                    // if ($ENABLE_MANAGE) 
                                    if ($ENABLE_MANAGE) : ?>
                                        <div class="text-center"><a href="<?= base_url($this->uri->segment(1) . '/approval_payment/?type=' . $item_dp->tipe . '&id=' . $item_dp->id . '&nilai=' . $item_dp->jumlah); ?>" name="save" class="btn btn-primary btn-sm"><i class="fas fa-check-double">&nbsp;</i>Approve</a></div>
                            <?php
                                    endif;
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- /.box-body -->
</div>
<?= form_close() ?>
<div class="modal modal-default fade" id="modal_view_receive_invoice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title title_modal" id="myModalLabel">View Receive Invoice</h4>
            </div>
            <div class="modal-body" id="ModalViewSPPLM">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Tutup</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script type="text/javascript">
    function trshowall() {
        $(".trows").removeClass("hidden");
    }

    function trshow(id) {
        $(".trows").addClass("hidden");
        $(".rowshow" + id).removeClass("hidden");
    }
    var url_save = siteurl + 'request_payment/save_approval/';
    //Save

    $(document).ready(function() {
        $('#table_transportasi').dataTable();
        $('#table_kasbon').dataTable();
        $('#table_expense').dataTable();
        $('#table_periodik').dataTable();
        $('#table_pembayaran_po').dataTable();
        $('#table_direct_payment').dataTable();
    })

    $(document).on("click", ".btn_view_req", function() {
        var val = $(this).data('val');
        // alert(val);

        $(".list_" + val).toggle();
        if (val == "transportasi") {
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
            $('.list_pembayaran_po').hide();
            $('.list_direct_payment').hide();
        }
        if (val == "kasbon") {
            $(".list_transportasi").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
            $('.list_pembayaran_po').hide();
            $('.list_direct_payment').hide();
        }
        if (val == "expense") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_periodik").hide();
            $('.list_pembayaran_po').hide();
            $('.list_direct_payment').hide();
        }
        if (val == "periodik") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $('.list_pembayaran_po').hide();
            $('.list_direct_payment').hide();
        }
        if (val == "pembayaran_po") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
            $('.list_direct_payment').hide();
        }
        if (val == "direct_payment") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
            $(".list_pembayaran_po").hide();
        }
    });

    $(document).on('click', '.view_receive_invoice', function() {
        var id_invoice = $(this).data('id_invoice');

        $.ajax({
            type: "POST",
            url: siteurl + active_controller + "view_receive_invoice",
            data: {
                "id_invoice": id_invoice
            },
            cache: false,
            success: function(result) {
                $('#ModalViewSPPLM').html(result);
                $('#modal_view_receive_invoice').modal('show');
            },
            error: function(result) {
                swal({
                    title: 'Error!',
                    text: 'Please try again later!',
                    type: 'error'
                });
            }
        });
    });

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();
        var errors = "";
        if (errors == "") {
            swal({
                    title: "Anda Yakin?",
                    text: "Data Akan Di Setujui!",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Setujui!",
                    cancelButtonText: "Tidak!",
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formdata = new FormData($('#frm_data')[0]);
                        $.ajax({
                            url: url_save,
                            dataType: "json",
                            type: 'POST',
                            data: formdata,
                            processData: false,
                            contentType: false,
                            success: function(msg) {
                                if (msg['save'] == '1') {
                                    swal({
                                        title: "Sukses!",
                                        text: "Data Berhasil Di Setujui",
                                        type: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    window.location.href = window.location.href;
                                } else {
                                    swal({
                                        title: "Gagal!",
                                        text: "Data Gagal Di Setujui",
                                        type: "error",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                };
                                console.log(msg);
                            },
                            error: function(msg) {
                                swal({
                                    title: "Gagal!",
                                    text: "Ajax Data Gagal Di Proses",
                                    type: "error",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                console.log(msg);
                            }
                        });
                    }
                });
        } else {
            swal(errors);
            return false;
        }
    });
    $("#btnxls").click(function() {
        $("#mytabledata").table2excel({
            exclude: ".exclass",
            name: "Request Payment Approval",
            filename: "RequestPaymentApproval.xls", // do include extension
            preserveColors: false // set to true if you want background colors and font colors preserved
        });
    });
</script>

<style>
    /* Card metric */
    .panel-metric {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0, 0, 0, .06);
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .panel-metric:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .10);
    }

    .panel-metric .panel-heading {
        color: #fff;
        font-weight: 600;
        letter-spacing: .2px;
        padding: 12px 18px;
    }

    .panel-metric .heading-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .panel-metric .heading-icon {
        opacity: .9;
        font-size: 18px;
    }

    .panel-metric .panel-body {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 90px;
        padding: 22px 18px;
        background: linear-gradient(180deg, #fff, #fafafa);
    }

    .panel-metric .metric-value {
        font-size: 46px;
        font-weight: 700;
        line-height: 1;
        color: #2b2b2b;
    }

    .panel-metric .panel-footer {
        background: #fff;
        border-top: 0;
        padding: 14px 16px;
    }

    .panel-metric .btn {
        width: 100%;
        border-radius: 10px;
        font-weight: 600;
    }

    /* Gradien warna header */
    .bg-green {
        background: linear-gradient(135deg, #0bb07b, #0aa36d);
    }

    .bg-warning {
        background: linear-gradient(135deg, #f9a10a, #f08c00);
    }

    .bg-blue {
        background: linear-gradient(135deg, #1e74d6, #125db5);
    }

    .bg-danger {
        background: linear-gradient(135deg, #e25555, #d13d3d);
    }

    .bg-light-blue {
        background: linear-gradient(135deg, #3aa3c8, #2a8fb2);
    }

    .bg-grey {
        background: linear-gradient(135deg, #bfc7d1, #aab3bf);
    }

    /* Responsive kecil */
    @media (max-width:992px) {
        .panel-metric .metric-value {
            font-size: 40px;
        }
    }
</style>