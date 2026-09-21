<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Budget_model extends BF_Model
{
    protected $db2;

    public function __construct()
    {
        parent::__construct();
        $this->db2 = $this->load->database('accounting', TRUE);
    }

    public function get_data_json_asset()
    {
        $requestData   = $_REQUEST;
        $tanda         = isset($requestData['tanda']) ? $requestData['tanda'] : '';
        $status_filter = isset($requestData['status_filter']) ? $requestData['status_filter'] : (isset($requestData['status']) ? $requestData['status'] : '');

        $fetch = $this->query_data_json_asset(
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
            }
            if ($asc_desc == 'desc') {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            $color  = 'secondary';
            $status = 'UNKNOWN';
            if ($row['status'] == 'N') {
                $status = 'WAITING APPROVAL';
                $color  = 'warning';
            } else if ($row['status'] == 'Y') {
                $status = 'APPROVED';
                $color  = 'success';
            } else if ($row['status'] == 'D' || $row['status'] == 'R') {
                $status = 'REJECTED';
                $color  = 'danger';
            }

            $badge = "<span class='badge bg-" . $color . "'>" . strtoupper($status) . "</span>";

            $view    = "<a href='" . site_url('budget/view_asset/' . $row['code_plan']) . "' class='btn btn-sm btn-warning' title='View Data'><i class='fa fa-eye'></i></a>";
            $edit    = "";
            $delete  = "";
            $approve = "";

            if ($row['status'] == 'N') {
                if (empty($tanda)) {
                    if ($this->auth->has_permission('Budget.Manage')) {
                        $edit = "<a href='" . site_url('budget/add_asset/' . $row['code_plan']) . "' class='btn btn-sm btn-primary ms-1' title='Edit Data'><i class='fa fa-edit'></i></a>";
                    }
                    if ($this->auth->has_permission('Budget.Delete')) {
                        $delete = "<button type='button' class='btn btn-sm btn-danger ms-1 hapus' data-id='" . $row['code_plan'] . "' title='Delete Data'><i class='fa fa-trash'></i></button>";
                    }
                }
                if (!empty($tanda) && $tanda === 'approve') {
                    if ($this->auth->has_permission('Budget.Manage')) {
                        $approve = "<a href='" . site_url('budget/approve_asset/' . $row['code_plan']) . "' class='btn btn-sm btn-info ms-1' title='Approve Data'><i class='fa fa-check'></i> Approve</a>";
                    }
                }
            } else if ($row['status'] == 'D' || $row['status'] == 'R') {
                if (empty($tanda)) {
                    if ($this->auth->has_permission('Budget.Manage')) {
                        $edit = "<a href='" . site_url('budget/add_asset/' . $row['code_plan']) . "' class='btn btn-sm btn-primary ms-1' title='Edit Data'><i class='fa fa-edit'></i></a>";
                    }
                    if ($this->auth->has_permission('Budget.Delete')) {
                        $delete = "<button type='button' class='btn btn-sm btn-danger ms-1 hapus' data-id='" . $row['code_plan'] . "' title='Delete Data'><i class='fa fa-trash'></i></button>";
                    }
                }
            }

            $nestedData   = array();
            $nestedData[] = "<div align='center'>" . $nomor . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['coa'] . ' | ' . $row['nama_coa']) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_dept']) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_costcenter']) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nama_asset']) . "</div>";
            $nestedData[] = "<div align='center'>" . $row['qty'] . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['budget']) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['budget_pr']) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['budget_po']) . "</div>";
            $nestedData[] = "<div align='center'>" . date('F Y', strtotime($row['tahun'] . '-' . sprintf('%02d', $row['bulan']) . '-01')) . "</div>";
            $nestedData[] = "<div align='center'>" . $badge . "</div>";
            $nestedData[] = "<div align='center' class='btn-group'>" . $view . $edit . $delete . $approve . "</div>";

            $data[] = $nestedData;
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

    public function query_data_json_asset($tanda = '', $status_filter = '', $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $where = '';
        if (!empty($tanda) && $tanda === 'approve') {
            $where .= " AND a.status = 'N' ";
        }

        if (!empty($status_filter) && $status_filter !== 'ALL') {
            if ($status_filter === 'D') {
                $where .= " AND (a.status = 'D' OR a.status = 'R') ";
            } else {
                $where .= " AND a.status = '" . $this->db->escape_str($status_filter) . "' ";
            }
        }

        $db_accounting = $this->db2->database;

        $sql = "
            SELECT
                a.*,
                COALESCE(b.nm_dept, '') as nm_dept,
                COALESCE(c.nm_costcenter, '') as nm_costcenter,
                COALESCE(d.nama, '') as nama_coa
            FROM
                asset_planning a
                LEFT JOIN department b ON a.id_dept = b.id
                LEFT JOIN costcenter c ON a.id_costcenter = c.id_costcenter
                LEFT JOIN " . $db_accounting . ".coa_master d ON a.coa = d.no_perkiraan
            WHERE a.deleted = 'N' " . $where . " AND (
                a.code_plan LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.nama_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR b.nm_dept LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR c.nm_costcenter LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
        ";

        $data['totalData']     = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();

        $columns_order_by = array(
            0  => 'a.id',
            1  => 'a.coa',
            2  => 'b.nm_dept',
            3  => 'c.nm_costcenter',
            4  => 'a.nama_asset',
            5  => 'a.qty',
            6  => 'a.budget',
            7  => 'a.budget_pr',
            8  => 'a.budget_po',
            9  => 'a.tahun',
            10 => 'a.status'
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

    public function hapus_asset($code_plan)
    {
        $data_session = $this->session->userdata;
        $dateTime     = date('Y-m-d H:i:s');
        $username     = isset($data_session['ORI_User']['username']) ? $data_session['ORI_User']['username'] : (isset($data_session['app_session']['username']) ? $data_session['app_session']['username'] : (isset($data_session['username']) ? $data_session['username'] : 'admin'));

        $ArrHeader = array(
            'deleted'      => 'Y',
            'deleted_by'   => $username,
            'deleted_date' => $dateTime
        );

        $this->db->trans_start();
        $this->db->where('code_plan', $code_plan);
        $this->db->update('asset_planning', $ArrHeader);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('pesan' => 'Process data failed. Please try again later ...', 'status' => 0);
        } else {
            $this->db->trans_commit();
            history('Hapus Pengajuan Budget Asset ' . $code_plan);
            return array('pesan' => 'Process data success. Thanks ...', 'status' => 1);
        }
    }
}
