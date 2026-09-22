<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Asset_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList($table)
    {
        if ($this->db->field_exists('status', $table)) {
            $this->db->where('status', 'Y');
        }
        if ($this->db->field_exists('deleted', $table)) {
            $this->db->where('deleted', 'N');
        }
        $query = $this->db->get($table);
        return $query->result_array();
    }

    public function getWhere($table, $field, $value)
    {
        $query = $this->db->get_where($table, array($field => $value));
        return $query->result_array();
    }

    public function getDataJSON()
    {
        $requestData = $_REQUEST;
        $kategori    = isset($requestData['kategori']) ? $requestData['kategori'] : (isset($requestData['kategory']) ? $requestData['kategory'] : '0');

        $fetch       = $this->get_query_json(
            $kategori,
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

            $tgl_perolehan = '-';
            if (!empty($row['tgl_perolehan']) && $row['tgl_perolehan'] != '0000-00-00') {
                $tgl_perolehan = date('d M Y', strtotime($row['tgl_perolehan']));
            }

            $nestedData   = array();
            $nestedData[] = "<div align='center'>" . $nomor . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['kd_asset']) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_asset']) . "</div>";
            $nestedData[] = "<div align='center'>" . $tgl_perolehan . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_category']) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_dept']) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['nilai_asset']) . "</div>";
            $nestedData[] = "<div align='center'>" . $row['depresiasi'] . " Tahun</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['value']) . "</div>";

            $view   = "<button type='button' class='btn btn-sm btn-warning detail' data-id='" . $row['id'] . "' title='View Data'><i class='fa fa-eye'></i></button>";
            $edit   = "";
            $delete = "";

            if ($this->auth->has_permission('Asset.Manage') || $this->auth->has_permission('Assets.Manage')) {
                $edit = "<a href='" . site_url('asset/add/' . $row['id']) . "' class='btn btn-sm btn-primary ms-1' title='Edit Data'><i class='fa fa-edit'></i></a>";
            }
            if ($this->auth->has_permission('Asset.Delete') || $this->auth->has_permission('Assets.Delete')) {
                $delete = "<button type='button' class='btn btn-sm btn-danger ms-1 delete' data-id='" . $row['kd_asset'] . "' title='Delete Data'><i class='fa fa-trash'></i></button>";
            }

            $nestedData[] = "<div align='center' class='btn-group'>" . $view . $edit . $delete . "</div>";
            $data[]       = $nestedData;
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

    public function get_query_json($kategori = '0', $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $where_kategori = "";
        if (!empty($kategori) && $kategori !== '0') {
            $where_kategori = " AND a.category = '" . $this->db->escape_str($kategori) . "' ";
        }

        $sql = "
            SELECT
                a.*,
                COALESCE(b.nm_category, '') as nm_category,
                COALESCE(c.nm_dept, '') as nm_dept
            FROM
                asset a
                LEFT JOIN asset_category b ON a.category = b.id
                LEFT JOIN department c ON a.id_dept = c.id
            WHERE 1=1
                AND a.deleted_date IS NULL
                " . $where_kategori . "
                AND (
                    a.kd_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                    OR a.nm_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                    OR b.nm_category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                    OR c.nm_dept LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                )
        ";

        $data['totalData']     = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();

        $columns_order_by = array(
            0 => 'a.id',
            1 => 'a.kd_asset',
            2 => 'a.nm_asset',
            3 => 'a.tgl_perolehan',
            4 => 'b.nm_category',
            5 => 'c.nm_dept',
            6 => 'a.nilai_asset',
            7 => 'a.depresiasi',
            8 => 'a.value'
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
}
