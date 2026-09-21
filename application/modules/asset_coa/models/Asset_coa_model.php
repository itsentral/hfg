<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Asset_coa_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_json_asset_coa()
    {
        $requestData = $_REQUEST;
        $fetch       = $this->get_query_json_asset_coa(
            isset($requestData['label']) ? $requestData['label'] : (isset($requestData['search']['value']) ? $requestData['search']['value'] : NULL),
            isset($requestData['order'][0]['column']) ? $requestData['order'][0]['column'] : 1,
            isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'asc',
            isset($requestData['start']) ? $requestData['start'] : 0,
            isset($requestData['length']) ? $requestData['length'] : 10
        );
        $totalData       = $fetch['totalFiltered'];
        $totalFiltered   = $fetch['totalFiltered'];
        $query           = $fetch['query'];

        $data  = array();
        $urut1 = 1;
        $urut2 = 0;
        foreach ($query->result_array() as $row) {
            $total_data = $totalData;
            $start_dari = isset($requestData['start']) ? $requestData['start'] : 0;
            $asc_desc   = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'asc';
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            }
            if ($asc_desc == 'desc') {
                $nomor = ($total_data - $start_dari) - $urut2;
            }
            $status = ($row['status'] == 'Y' ? '' : 'bg-danger text-white');

            $nestedData   = array();
            $nestedData[] = "<div class='text-center " . $status . "'>" . $nomor . "</div>";
            $nestedData[] = "<div class='text-start " . $status . "'>" . strtoupper($row['keterangan']) . "</div>";
            $nestedData[] = "<div class='text-start " . $status . "'>" . $row['coa'] . "</div>";
            $nestedData[] = "<div class='text-start " . $status . "'>" . $row['coa_kredit'] . "</div>";

            $edit   = "<button type='button' class='btn btn-sm btn-primary edit' data-code='" . $row['id'] . "' title='Edit Data'><i class='fa fa-edit'></i> Edit</button>";
            $delete = "<button type='button' class='btn btn-sm btn-danger delete' data-code='" . $row['id'] . "' title='Delete Data'><i class='fa fa-trash'></i></button>";

            $nestedData[] = "<div class='text-center d-flex gap-1 justify-content-center'>" . $edit . " " . $delete . "</div>";
            $data[]       = $nestedData;
            $urut1++;
            $urut2++;
        }

        $json_data = array(
            'draw'            => intval(isset($requestData['draw']) ? $requestData['draw'] : 0),
            'recordsTotal'    => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data'            => $data
        );

        echo json_encode($json_data);
    }

    public function get_query_json_asset_coa($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "
            SELECT
                (@row:=@row+1) AS nomor,
                a.*
            FROM
                asset_coa a,
                (SELECT @row:=0) r 
            WHERE
                1=1 AND (
                a.coa LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.keterangan LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.coa_kredit LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
        ";

        $data['totalData']     = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'keterangan'
        );

        $order_col = isset($columns_order_by[$column_order]) ? $columns_order_by[$column_order] : 'keterangan';
        $sql .= " ORDER BY " . $order_col . " " . $column_dir;
        if ($limit_length != -1 && $limit_length !== NULL) {
            $sql .= " LIMIT " . $limit_start . " , " . $limit_length;
        }
        $data['query'] = $this->db->query($sql);

        return $data;
    }
}
