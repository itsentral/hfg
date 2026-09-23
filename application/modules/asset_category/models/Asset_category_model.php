<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Asset_category_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList($table)
    {
        $query = $this->db->get($table);
        return $query->result_array();
    }

    public function getWhere($table, $field, $value)
    {
        $query = $this->db->get_where($table, array($field => $value));
        return $query->result_array();
    }

    public function get_json_category()
    {
        $requestData = $_REQUEST;
        $fetch       = $this->get_query_json_category(
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

            $nestedData   = array();
            $nestedData[] = "<div align='center'>" . $nomor . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_category']) . "</div>";
            $nestedData[] = "<div align='center'>" . ($row['status'] == 'Y' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>') . "</div>";

            $edit   = "<button type='button' class='btn btn-sm btn-primary edit' data-code='" . $row['id'] . "' title='Edit Data'><i class='fa fa-edit'></i></button>";
            $delete = "<button type='button' class='btn btn-sm btn-danger delete' data-code='" . $row['id'] . "' title='Delete Data'><i class='fa fa-trash'></i></button>";

            $nestedData[] = "<div align='center' class='btn-group'>" . $edit . " " . $delete . "</div>";
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

    public function get_query_json_category($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "
            SELECT
                a.*
            FROM
                asset_category a
            WHERE 1=1
                AND a.deleted = 'N'
                AND (
                    a.nm_category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                )
        ";

        $data['totalData']     = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();

        $columns_order_by = array(
            0 => 'a.id',
            1 => 'a.nm_category',
            2 => 'a.status'
        );

        if (isset($columns_order_by[$column_order])) {
            $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir;
        } else {
            $sql .= " ORDER BY a.id ASC";
        }

        $sql .= " LIMIT " . $limit_start . " ," . $limit_length;

        $data['query'] = $this->db->query($sql);
        return $data;
    }
}
