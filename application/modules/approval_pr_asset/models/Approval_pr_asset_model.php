<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Approval_pr_asset_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_data_json_approval()
    {
        $requestData = $_REQUEST;
        $fetch       = $this->query_data_json_approval(
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

            $nestedData[] = "<div align='center'><span class='badge bg-warning'>WAITING APPROVAL</span></div>";

            $view = "<button type='button' class='btn btn-sm btn-primary look_hide' title='Approve / Reject Action' data-id='" . $nomor . "'><i class='fa fa-check-square'></i> Process</button>";

            $nestedData[] = "<div align='center'>" . $view . "</div>";
            $data[]       = $nestedData;

            // Detail approval row
            $nestedData2   = array();
            $nestedData2[] = "<div class='prtCh_" . $nomor . "' align='center'></div><script type='text/javascript'>$('.prtCh_" . $nomor . "').parent().parent().attr('class','child-" . $nomor . "');$('.child-" . $nomor . "').hide()</script>";
            $nestedData2[] = "<div align='left'></div>";
            $nestedData2[] = "<div align='right'><b>QTY BARANG</b><br>" . number_format($row['qty']) . "</div>";
            $nestedData2[] = "<div align='right'><b>NILAI PR</b><br>" . number_format($row['nilai_pr']) . "</div>";
            $nestedData2[] = "<div align='right'><b>TGL DIBUTUHKAN</b><br>" . date('d F Y', strtotime($row['tgl_dibutuhkan'])) . "</div>";

            $nestedData2[] = "<div align='right'><b>ACTION</b><br>
                                <select id='action_" . $nomor . "' class='form-select form-select-sm'>
                                    <option value='Y'>APPROVE</option>
                                    <option value='D'>REJECT</option>
                                </select>
                                </div>";
            $nestedData2[] = "<div align='right'><b>REASON REJECT</b><br>
                                <input type='text' id='reason_" . $nomor . "' class='form-control form-control-sm' placeholder='Reason if reject'>
                                <input type='hidden' id='no_pr_" . $nomor . "' value='" . $row['no_pr'] . "'>
                                </div>";
            $approve_btn   = "<button type='button' class='btn btn-sm btn-success approve' title='Submit Approval' data-id='" . $nomor . "'><i class='fa fa-save'></i> Submit</button>";
            $nestedData2[] = "<div align='center'><br>" . $approve_btn . "</div>";

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

    public function query_data_json_approval($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "
            SELECT
                a.*,
                b.tgl_pr
            FROM
                tran_pr_detail a
                LEFT JOIN tran_pr_header b ON a.no_pr = b.no_pr
            WHERE a.category='asset' AND (a.app_status = 'N' OR a.app_status IS NULL OR a.app_status = '') AND (
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

    public function approve_pr()
    {
        $data   = $this->input->post();
        $no_pr  = $data['no_pr'];
        $action = $data['action'];
        $reason = strtolower($data['reason']);

        $ArrUpdate = array(
            'app_status' => $action,
            'app_reason' => $reason,
            'app_by'     => (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin'))),
            'app_date'   => date('Y-m-d H:i:s')
        );

        $this->db->trans_start();
        $this->db->where('no_pr', $no_pr);
        $this->db->update('tran_pr_header', $ArrUpdate);

        $this->db->where('no_pr', $no_pr);
        $this->db->update('tran_pr_detail', $ArrUpdate);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data = array('pesan' => 'Proses approval gagal...', 'status' => 0);
        } else {
            $this->db->trans_commit();
            $status_lbl = ($action == 'Y') ? 'Approve' : 'Reject';
            $Arr_Data   = array('pesan' => 'Berhasil ' . $status_lbl . ' PR Asset No. ' . $no_pr, 'status' => 1);
            history($status_lbl . ' PR asset ' . $no_pr);
        }

        echo json_encode($Arr_Data);
    }
}
