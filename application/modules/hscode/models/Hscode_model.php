<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hscode_model extends CI_Model
{
    protected $viewPermission   = 'Hscode.View';
    protected $addPermission    = 'Hscode.Add';
    protected $managePermission = 'Hscode.Manage';
    protected $deletePermission = 'Hscode.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'hscode';
        $this->key        = 'id';
        $this->code       = 'local_code';
    }

    function generate_id($code = '')
    {
        $y = date('y');
        $count = 1;
        $maxID = $this->db->select("MAX(RIGHT(id,5)) as id")->from('hscode')->where(['SUBSTR(id,4,2)' => $y])->get()->row()->id;
        if ($maxID) {
            $count = $maxID + 1;
        }
        $newID = "HSC$y-" . str_pad($count, 5, "0", STR_PAD_LEFT);
        return $newID;
    }

    function get_data($table)
    {
        return $this->db->get($table)->result();
    }

    public function get_json_hscode()
    {
        $requestData = $_REQUEST;
        $status = $requestData['status'];
        $search = $requestData['search']['value'];
        $column = $requestData['order'][0]['column'];
        $dir = $requestData['order'][0]['dir'];
        $start = $requestData['start'];
        $length = $requestData['length'];

        // Kondisi WHERE
        $where = '';
        $where = " AND vh.`status` = '$status'";

        // Escape string pencarian
        $string = $this->db->escape_like_str($search);

        // Query SQL dengan relasi
        $sql = "SELECT vh.*, (@row_number:=@row_number + 1) AS num, 
            GROUP_CONCAT(DISTINCT CONCAT(c.country_code, ' - ', c.name) SEPARATOR ', ') AS origins
            FROM view_hscode vh
            LEFT JOIN hscode_origin ho ON vh.id = ho.hscode_id
            LEFT JOIN countries c ON ho.origin_id = c.id, 
            (SELECT @row_number:=0) as temp
            WHERE 1=1 $where  
            AND ho.origin_id IS NOT NULL
            AND (vh.local_code LIKE '%$string%'
            OR vh.origin_code LIKE '%$string%'
            OR c.country_code LIKE '%$string%'
            OR c.name LIKE '%$string%'
            OR vh.brand LIKE '%$string%'
            OR vh.description LIKE '%$string%'
            OR vh.status LIKE '%$string%')
            GROUP BY vh.id";

        // Hitung total data
        $totalData = $this->db->query($sql)->num_rows();
        $totalFiltered = $this->db->query($sql)->num_rows();

        // Tambahkan sorting dan paginasi
        $columns_order_by = [
            0 => 'num',
            1 => 'local_code',
            2 => 'origin_code',
            3 => 'origins',
            4 => 'description',
            5 => 'brand',
            6 => 'status',
            7 => 'modified_at',
        ];

        $sql .= ' ORDER BY ' . $columns_order_by[$column] . ' ' . $dir . ' ';
        $sql .= ' LIMIT ' . $start . ' ,' . $length . ' ';
        $query = $this->db->query($sql);

        // Proses data
        $data = [];
        $urut1 = 1;
        $urut2 = 0;

        $statusLabels = [
            '0' => '<span class="bg-danger tx-white pd-5 tx-11 tx-bold rounded-5">Inactive</span>',
            '1' => '<span class="bg-info tx-white pd-5 tx-11 tx-bold rounded-5">Active</span>',
        ];

        foreach ($query->result_array() as $row) {
            $buttons = '';
            $total_data = $totalData;
            $start_dari = $start;
            $asc_desc = $dir;

            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            } else {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            $view = '<a href="' . base_url('hscode/view/') . $row['id'] . '" class="btn-icon btn-icon-view view" data-toggle="tooltip" title="View" data-id="' . $row['id'] . '"><i class="fa fa-eye"></i></a>';
            $edit = '<a href="' . base_url('hscode/edit/') . $row['id'] . '" class="btn-icon btn-icon-edit edit" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a>';
            $delete = '<a class="btn-icon btn-icon-delete delete" data-toggle="tooltip" title="Delete" data-id="' . $row['id'] . '"><i class="fa fa-trash"></i></a>';
            $buttons = $view;

            if (has_permission($this->managePermission)) {
                $buttons .= '&nbsp;' . $edit . '&nbsp;' . $delete;
            }

            $nestedData = [];
            $nestedData[] = $nomor;
            $nestedData[] = $row['local_code'];
            $nestedData[] = $row['description'];
            $nestedData[] = $row['origin_code'];
            $nestedData[] = $row['origins']; // Data dari relasi
            $nestedData[] = $row['brand'];
            // $nestedData[] = $statusLabels[$row['status']];
            // $nestedData[] = date("d M Y H:i:s", strtotime($row['modified_at']));
            $nestedData[] = $buttons;
            $data[] = $nestedData;
            ++$urut1;
            ++$urut2;
        }

        // Format data untuk DataTables
        $json_data = [
            'draw' => intval($requestData['draw']),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $data,
        ];

        echo json_encode($json_data);
    }
}
