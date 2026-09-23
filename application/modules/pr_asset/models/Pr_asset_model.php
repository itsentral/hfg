<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pr_asset_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_data_json_pr_asset()
    {
        $requestData   = $_REQUEST;
        $tanda         = isset($requestData['tanda']) ? $requestData['tanda'] : '';
        $status_filter = isset($requestData['status_filter']) ? $requestData['status_filter'] : (isset($requestData['status']) ? $requestData['status'] : '');

        $fetch = $this->query_data_json_pr_asset(
            $tanda,
            $status_filter,
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $data  = array();
        $urut1 = 1;
        $urut2 = 0;
        foreach ($query->result_array() as $row) {
            $total_data = $totalData;
            $start_dari = $requestData['start'];
            $asc_desc   = $requestData['order'][0]['dir'];
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            } else {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            $nestedData   = array();
            $nestedData[] = "<div class='prt_" . $nomor . "' align='center'>" . $nomor . "</div><script type='text/javascript'>$('.prt_" . $nomor . "').parent().parent().attr('id','" . $nomor . "');</script>";
            $nestedData[] = "<div align='center'>" . strtoupper($row['no_pr']) . "</div>";
            $nestedData[] = "<div align='center'>" . date('d M Y', strtotime($row['tgl_pr'])) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_barang']) . "</div>";
            $nestedData[] = "<div align='center'>" . $row['created_by'] . "</div>";
            $nestedData[] = "<div align='center'>" . date('d M Y', strtotime($row['created_date'])) . "</div>";

            $app_status = isset($row['app_status']) ? $row['app_status'] : 'N';
            if ($app_status == 'Y') {
                $status = 'APPROVED';
                $color  = 'success';
            } else if ($app_status == 'D') {
                $status = 'REJECTED';
                $color  = 'danger';
            } else {
                $status = 'WAITING APPROVAL';
                $color  = 'warning';
            }

            $nestedData[] = "<div align='center'><span class='badge bg-" . $color . "'>" . $status . "</span></div>";

            $view  = "<button type='button' class='btn btn-sm btn-warning look_hide me-1' title='View Detail' data-id='" . $nomor . "'><i class='fa fa-eye'></i> View</button>";
            $print = "<a href='" . site_url('pr_asset/print_pr_asset/' . $row['no_pr']) . "' target='_blank' class='btn btn-sm btn-info' title='Print PR'><i class='fa fa-print'></i> Print</a>";

            $nestedData[] = "<div align='center' class='btn-group'>" . $view . $print . "</div>";
            $data[]       = $nestedData;

            // Detail Row (Expandable underneath)
            $tgl_butuh = (!empty($row['tgl_butuh'])) ? date('d F Y', strtotime($row['tgl_butuh'])) : ((!empty($row['tgl_dibutuhkan'])) ? date('d F Y', strtotime($row['tgl_dibutuhkan'])) : '-');

            $reason_html = "<div align='center'></div>";
            if (($app_status == 'D' || $app_status == 'R') && !empty($row['app_reason'])) {
                $reason_html = "<div align='center'><b>REASON</b><br><span class='text-danger'>" . strtoupper($row['app_reason']) . "</span></div>";
            }

            $nestedData2   = array();
            $nestedData2[] = "<div class='prtCh_" . $nomor . "' align='center'></div><script type='text/javascript'>$('.prtCh_" . $nomor . "').parent().parent().attr('class','child-" . $nomor . "');$('.child-" . $nomor . "').hide()</script>";
            $nestedData2[] = "<div align='left'></div>";
            $nestedData2[] = "<div align='center'><b>QTY BARANG</b><br>" . number_format($row['qty']) . "</div>";
            $nestedData2[] = "<div align='left'><b>NILAI PR</b><br>" . number_format($row['nilai_pr']) . "</div>";
            $nestedData2[] = "<div align='center'><b>TGL DIBUTUHKAN</b><br>" . $tgl_butuh . "</div>";
            $nestedData2[] = $reason_html;
            $nestedData2[] = "<div align='center'></div>";
            $nestedData2[] = "<div align='center'></div>";

            $data[] = $nestedData2;

            $urut1++;
            $urut2++;
        }

        $json_data = array(
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        echo json_encode($json_data);
    }

    public function query_data_json_pr_asset($tanda = '', $status_filter = '', $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $where = '';
        if (!empty($tanda) && $tanda === 'approve') {
            $where .= " AND (a.app_status = 'N' OR a.app_status IS NULL OR a.app_status = '') ";
        }

        if (!empty($status_filter) && $status_filter !== 'ALL') {
            if ($status_filter === 'N') {
                $where .= " AND (a.app_status = 'N' OR a.app_status IS NULL OR a.app_status = '') ";
            } else if ($status_filter === 'D') {
                $where .= " AND (a.app_status = 'D' OR a.app_status = 'R') ";
            } else {
                $where .= " AND a.app_status = '" . $this->db->escape_str($status_filter) . "' ";
            }
        }

        $sql = "
            SELECT
                a.*,
                b.tgl_pr
            FROM
                tran_pr_detail a
                LEFT JOIN tran_pr_header b ON a.no_pr = b.no_pr
            WHERE a.category='asset' " . $where . " AND (
                a.id LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.nm_barang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
        ";

        $data['totalData']     = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by      = array(
            0 => 'a.id',
            1 => 'a.no_pr',
            2 => 'b.tgl_pr',
            3 => 'a.nm_barang'
        );

        if (isset($columns_order_by[$column_order])) {
            $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir;
        } else {
            $sql .= " ORDER BY a.id DESC";
        }

        $sql .= " LIMIT " . $limit_start . " ," . $limit_length;

        $data['query'] = $this->db->query($sql);
        return $data;
    }

    public function get_data_json_add_pr_asset()
    {
        $requestData = $_REQUEST;
        $fetch       = $this->query_data_json_add_pr_asset(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $data  = array();
        $urut1 = 1;
        $urut2 = 0;
        foreach ($query->result_array() as $row) {
            $total_data = $totalData;
            $start_dari = $requestData['start'];
            $asc_desc   = $requestData['order'][0]['dir'];
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            } else {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            $nestedData   = array();
            $nestedData[] = "<div class='prt_" . $nomor . "' align='center'>" . $nomor . "</div><script type='text/javascript'>$('.prt_" . $nomor . "').parent().parent().attr('id','" . $nomor . "');</script>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nama_asset']) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_dept']) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_costcenter']) . "</div>";
            $nestedData[] = "<div align='center'>" . $row['qty'] . "</div>";
            $nestedData[] = "<div align='center'>" . strtolower($row['created_by']) . "</div>";
            $nestedData[] = "<div align='center'>" . date('d M Y', strtotime($row['created_date'])) . "</div>";

            $view = "<button type='button' class='btn btn-sm btn-primary look_hide' title='Expand/Collapse' data-id='" . $nomor . "'><i class='fa fa-plus'></i></button>";

            $nestedData[] = "<div align='center'>" . $view . "</div>";
            $data[]       = $nestedData;

            // Child row detail
            $nestedData2   = array();
            $nestedData2[] = "<div class='prtCh_" . $nomor . "' align='center'></div><script type='text/javascript'>$('.prtCh_" . $nomor . "').parent().parent().attr('class','child-" . $nomor . "');$('.child-" . $nomor . "').hide()</script>";
            $nestedData2[] = "<div align='left'></div>";
            $nestedData2[] = "<div align='right'><b>BUDGET</b><br>" . number_format($row['budget']) . "<br><b>SISA BUDGET PR</b></br>" . number_format($row['budget_pr']) . "</div>";
            $nestedData2[] = "<div align='right'><b>RENCANA BELI</b><br>" . date('F Y', strtotime($row['tahun'] . '-' . sprintf('%02d', $row['bulan']) . '-01')) . "<br><b>KETERANGAN</b><br>" . strtoupper($row['keterangan']) . "</div>";
            $nestedData2[] = "<div align='right'><b>REVISI QTY</b><input type='text' id='qty_rev_" . $nomor . "' class='form-control form-control-sm text-center maskM' placeholder='Qty Rev' value='" . number_format($row['qty']) . "'></div>";
            $nestedData2[] = "<div align='right'><b>NILAI PR</b><input type='text' id='nil_pr_" . $nomor . "' class='form-control form-control-sm text-end maskM' placeholder='Nilai PR' value='" . number_format($row['budget']) . "'></div>";
            $nestedData2[] = "<div align='right'><b>TGL DIBUTUHKAN</b>
                                <input type='date' id='tgl_butuh_" . $nomor . "' class='form-control form-control-sm text-center'>
                                <input type='hidden' id='code_plan_" . $nomor . "' value='" . $row['code_plan'] . "'>
                                </div>";
            $app = "<button type='button' class='btn btn-sm btn-success add_pr' title='Tambahkan PR' data-id='" . $nomor . "'><i class='fa fa-check'></i> Add to PR</button>";

            $nestedData2[] = "<div align='center' style='vertical-align:middle;'><br>" . $app . "</div>";
            $data[]        = $nestedData2;

            $urut1++;
            $urut2++;
        }

        $json_data = array(
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        echo json_encode($json_data);
    }

    public function query_data_json_add_pr_asset($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "
            SELECT
                a.*,
                b.nm_dept,
                c.nm_costcenter
            FROM
                asset_planning a
                LEFT JOIN department b ON a.id_dept = b.id
                LEFT JOIN costcenter c ON a.id_costcenter = c.id_costcenter
            WHERE a.deleted='N' AND a.status='Y' AND (a.no_pr IS NULL OR a.no_pr = '') AND (
                a.id LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.nama_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR b.nm_dept LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR c.nm_costcenter LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
        ";

        $data['totalData']     = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by      = array(
            0 => 'a.id',
            1 => 'a.nama_asset',
            2 => 'b.nm_dept',
            3 => 'c.nm_costcenter'
        );

        if (isset($columns_order_by[$column_order])) {
            $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir;
        } else {
            $sql .= " ORDER BY a.id DESC";
        }

        $sql .= " LIMIT " . $limit_start . " ," . $limit_length;

        $data['query'] = $this->db->query($sql);
        return $data;
    }

    public function add_pr()
    {
        $data      = $this->input->post();
        $code_plan = $data['code_plan'];
        $qty_rev   = str_replace(',', '', $data['qty_rev']);
        $nil_pr    = str_replace(',', '', $data['nil_pr']);
        $tgl_butuh = $data['tgl_butuh'];

        $Ym          = date('y');
        $qIPP        = "SELECT MAX(no_pr) as maxP FROM tran_pr_header WHERE no_pr LIKE 'PRA" . $Ym . "%' ";
        $resultIPP   = $this->db->query($qIPP)->result_array();
        $angkaUrut2  = $resultIPP[0]['maxP'];
        $urutan2     = (int)substr($angkaUrut2, 5, 4);
        $urutan2++;
        $urut2       = sprintf('%04s', $urutan2);
        $no_pr       = "PRA" . $Ym . $urut2;

        $username = isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin'));
        $dateTime = date('Y-m-d H:i:s');

        $header     = $this->db->get_where('asset_planning', array('code_plan' => $code_plan))->row_array();
        $nama_asset = $header['nama_asset'];

        $ArrHeader = array(
            'no_pr'        => $no_pr,
            'tgl_pr'       => date('Y-m-d'),
            'category'     => 'asset',
            'created_by'   => $username,
            'created_date' => $dateTime
        );

        $ArrDetail = array(
            'no_pr'         => $no_pr,
            'code_plan'     => $code_plan,
            'category'      => 'asset',
            'nm_barang'     => $nama_asset,
            'qty'           => $qty_rev,
            'nilai_pr'      => $nil_pr,
            'tgl_butuh'     => $tgl_butuh,
            'app_status'    => 'N',
            'created_by'    => $username,
            'created_date'  => $dateTime
        );

        $ArrUpdatePlan = array(
            'no_pr' => $no_pr
        );

        $this->db->trans_start();
        $this->db->insert('tran_pr_header', $ArrHeader);
        $this->db->insert('tran_pr_detail', $ArrDetail);
        $this->db->where('code_plan', $code_plan);
        $this->db->update('asset_planning', $ArrUpdatePlan);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data = array('pesan' => 'Create PR asset failed. Please try again...', 'status' => 0);
            write_log('PR Asset', 'Create PR Asset', 'Create PR asset failed: ' . $no_pr . ' / ' . $code_plan, $data, null, 0);
        } else {
            $this->db->trans_commit();
            $Arr_Data = array('pesan' => 'Create PR asset success. Thanks...', 'status' => 1);
            write_log('PR Asset', 'Create PR Asset', 'Create PR asset success: ' . $no_pr . ' / ' . $code_plan, $data, null, 1);
        }

        echo json_encode($Arr_Data);
    }
}
