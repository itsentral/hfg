<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Material_master_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'new_inventory_4';
        $this->key        = 'id';
        $this->code       = 'code_lv4';
    }

    function generate_id()
    {
        $kode             = 'M4' . date('y');
        $Query            = "SELECT MAX(" . $this->code . ") as maxP FROM " . $this->table_name . " WHERE " . $this->code . " LIKE '" . $kode . "%' ";
        $resultIPP        = $this->db->query($Query)->result_array();
        $angkaUrut2        = $resultIPP[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 4, 6);
        $urutan2++;
        $urut2            = sprintf('%06s', $urutan2);
        $kode_id        = $kode . $urut2;
        return $kode_id;
    }

    public function get_data($array_where)
    {
        if (!empty($array_where)) {
            $query = $this->db->get_where($this->table_name, $array_where);
        } else {
            $query = $this->db->get($this->table_name);
        }

        return $query->result();
    }

    function getById($id)
    {
        return $this->db->get_where($this->table_name, array($code => $id))->row_array();
    }

    public function get_json_material_master()
    {
        $requestData    = $_REQUEST;
        $fetch          = $this->get_query_json_material_master(
            $requestData['level1'],
            $requestData['level2'],
            $requestData['level3'],
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData      = $fetch['totalData'];
        $totalFiltered  = $fetch['totalFiltered'];
        $query          = $fetch['query'];

        $ENABLE_ADD     = has_permission('Material_master.Add');
        $ENABLE_MANAGE  = has_permission('Material_master.Manage');
        $ENABLE_VIEW    = has_permission('Material_master.View');
        $ENABLE_DELETE  = has_permission('Material_master.Delete');

        $get_level_1 = get_list_inventory_lv1('material');
        $get_level_2 = get_list_inventory_lv2('material');
        $get_level_3 = get_list_inventory_lv3('material');

        $data  = array();
        $urut1  = 1;
        $urut2  = 0;
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

            $material_type     = (!empty($get_level_1[$row['code_lv1']]['nama'])) ? $get_level_1[$row['code_lv1']]['nama'] : '';
            $material_category = (!empty($get_level_2[$row['code_lv1']][$row['code_lv2']]['nama'])) ? $get_level_2[$row['code_lv1']][$row['code_lv2']]['nama'] : '';
            $material_jenis    = (!empty($get_level_3[$row['code_lv1']][$row['code_lv2']][$row['code_lv3']]['nama'])) ? $get_level_3[$row['code_lv1']][$row['code_lv2']][$row['code_lv3']]['nama'] : '';

            $nestedData = array();
            $nestedData[] = $nomor;
            $nestedData[] = strtoupper($material_type);
            $nestedData[] = strtoupper($material_category);
            $nestedData[] = strtoupper($material_jenis);
            $nestedData[] = strtoupper($row['nama']);

            // ===== STATUS (pakai toggle switch seperti index baru) =====
            $checked = ($row['status'] == '1') ? 'checked' : '';
            $nestedData[] = "
                <label class='toggle-switch'>
                    <input type='checkbox'
                        class='toggle-status-checkbox'
                        data-id='{$row['id']}'
                        data-status='{$row['status']}'
                        {$checked}>
                    <span class='toggle-slider'></span>
                </label>
            ";

            // ===== ACTION (pakai btn-icon seperti index baru) =====
            $edit = "";
            $delete = "";

            if ($ENABLE_MANAGE) {
                $edit = "
                    <a class='btn-icon btn-icon-edit edit'
                    href='javascript:void(0)'
                    title='Edit'
                    data-id='{$row['id']}'>
                        <i class='ti ti-edit'></i>
                    </a>
                ";
            }

            if ($ENABLE_DELETE) {
                $delete = "
                    <a class='btn-icon btn-icon-delete delete'
                    href='javascript:void(0)'
                    title='Delete'
                    data-id='{$row['id']}'>
                        <i class='ti ti-trash'></i>
                    </a>
                ";
            }

            $nestedData[] = "<div class='d-flex justify-content-center gap-1'>{$edit}{$delete}</div>";

            $data[] = $nestedData;

            $urut1++;
            $urut2++;
        }


        $json_data = array(
            "draw"              => intval($requestData['draw']),
            "recordsTotal"      => intval($totalData),
            "recordsFiltered"   => intval($totalFiltered),
            "data"              => $data
        );

        echo json_encode($json_data);
    }

    public function get_query_json_material_master($level1, $level2, $level3, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {

        $WHERE_1 = "";
        if ($level1 != '0') {
            $WHERE_1 = " AND a.code_lv1 = '" . $level1 . "'";
        }

        $WHERE_2 = "";
        if ($level2 != '0') {
            $WHERE_2 = " AND a.code_lv2 = '" . $level2 . "'";
        }

        $WHERE_3 = "";
        if ($level3 != '0') {
            $WHERE_3 = " AND a.code_lv3 = '" . $level3 . "'";
        }

        $sql = "SELECT
                (@row:=@row+1) AS nomor,
                a.*
              FROM
                new_inventory_4 a,
                (SELECT @row:=0) r
              WHERE 
                a.deleted_date IS NULL 
                AND a.category='material' " . $WHERE_1 . $WHERE_2 . $WHERE_3 . "
                AND (
                  a.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                  OR a.trade_name LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                  OR a.code LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                )
      ";
        // echo $sql; exit;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'code_lv1',
            2 => 'code_lv2',
            3 => 'code_lv3',
            4 => 'nama'
        );

        $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

        $data['query'] = $this->db->query($sql);
        return $data;
    }
}
