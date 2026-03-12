<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Warehouse"
 */

class Warehouse_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Warehouse.Add');
        $this->ENABLE_MANAGE  = has_permission('Warehouse.Manage');
        $this->ENABLE_VIEW    = has_permission('Warehouse.View');
        $this->ENABLE_DELETE  = has_permission('Warehouse.Delete');
    }

    // list data
    public function GetListWarehouse()
    {
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->order_by('a.id', 'asc');
        $query = $this->db->get();
        if ($query->num_rows() != 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    // get data
    public function GetDataWarehouse($id)
    {
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->where('a.id', $id);
        $query = $this->db->get();
        if ($query->num_rows() != 0) {
            return $query->row();
        } else {
            return false;
        }
    }

    //server side
    public function get_json_warehouse_stock()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_warehouse_stock(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );

        $totalData = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query = $fetch['query'];

        $data = [];
        $urut1 = 1;

        foreach ($query->result_array() as $row) {
            $nomor = $urut1 + $requestData['start'];

            $nestedData = [];
            $nestedData[] = "<div align='center'>{$nomor}</div>";
            $nestedData[] = "<div align='left'>{$row['nm_barang']}</div>";
            $nestedData[] = "<div align='center'>{$row['no_coil']}</div>";
            $nestedData[] = "<div align='center'>1</div>"; // Jumlah Coil selalu 1 per baris sesuai konsep
            $nestedData[] = "<div align='right'>" . number_format($row['berat_bersih'], 3) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['berat_kotor'], 3) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['length'], 3) . "</div>";

            $data[] = $nestedData;
            $urut1++;
        }

        $json_data = [
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ];

        echo json_encode($json_data);
    }
    public function get_query_json_warehouse_stock($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
    {
        $columns_order_by = [
            0 => 'rd.id',
            1 => 'rd.nm_barang',
            2 => 'rd.no_coil',
            4 => 'rd.berat_bersih',
            5 => 'rd.berat_kotor',
            6 => 'rd.length'
        ];

        // 1. Hitung Total Data (Tanpa Filter Search)
        $this->db->from('tr_ros_detail rd');
        $this->db->join('tr_ros r', 'rd.no_ros = r.id', 'inner');
        $this->db->where('r.sts', '1');
        $totalData = $this->db->count_all_results(); // count_all_results otomatis mereset query CI

        // 2. Hitung Total Filtered (Dengan Search)
        // Kita panggil manual builder-nya di sini
        $this->_build_query_stock($like_value);
        $totalFiltered = $this->db->count_all_results();

        // 3. Ambil Data Utama
        $this->db->select('rd.*');
        $this->_build_query_stock($like_value); // Panggil lagi builder-nya

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('rd.id', 'desc');
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $query = $this->db->get();

        return [
            'totalData' => $totalData,
            'totalFiltered' => $totalFiltered,
            'query' => $query
        ];
    }

    // Tambahkan r.sts = 1 di sini agar konsisten di semua hitungan
    private function _build_query_stock($like_value)
    {
        $this->db->from('tr_ros_detail rd');
        $this->db->join('tr_ros r', 'rd.no_ros = r.id', 'inner');
        $this->db->where('r.sts', '1');

        if ($like_value) {
            $this->db->group_start();
            $this->db->like('rd.nm_barang', $like_value);
            $this->db->or_like('rd.no_coil', $like_value);
            $this->db->group_end();
        }
    }

    public function get_json_kartu_stok()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_kartu_stok(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );

        $totalData = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query = $fetch['query'];

        $data = [];
        $urut1 = 1;

        foreach ($query->result_array() as $row) {
            $nomor = $urut1 + $requestData['start'];
            $nestedData = [];

            $nestedData[] = "<div align='center'>{$nomor}</div>";
            $nestedData[] = date('d/M/Y', strtotime($row['tgl_transaksi']));
            $nestedData[] = $row['no_transaksi'];
            $nestedData[] = $row['transaksi'];
            $nestedData[] = $row['code_lv4'];
            $nestedData[] = $row['nm_material'];
            $nestedData[] = number_format($row['qty']);                  // AWAL: stock
            $nestedData[] = number_format($row['qty_book']);             // AWAL: booking
            $nestedData[] = number_format($row['qty_free']);             // AWAL: free stock
            $nestedData[] = number_format($row['qty_transaksi']);        // TRANSAKSI: in/out
            $nestedData[] = number_format($row['qty_book_akhir']);       // TRANSAKSI: booking
            $nestedData[] = number_format($row['qty_akhir']);            // AKHIR: stock
            $nestedData[] = number_format($row['qty_book_akhir']);       // AKHIR: booking
            $nestedData[] = number_format($row['qty_free_akhir']);       // AKHIR: free stock

            $data[] = $nestedData;
            $urut1++;
        }

        $json_data = [
            "draw" => intval($requestData['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ];

        echo json_encode($json_data);
    }


    public function get_query_json_kartu_stok($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
    {
        $columns_order_by = [
            0 => 'ks.id',
            1 => 'ks.tgl_transaksi',
            2 => 'ks.no_transaksi',
            3 => 'ks.transaksi',
            4 => 'ks.code_lv4',
            5 => 'ks.nm_material',
            6 => 'ks.qty',
            7 => 'ks.qty_book',
            8 => 'ks.qty_free',
            9 => 'ks.qty_transaksi',
            10 => 'ks.qty_book_akhir',
            11 => 'ks.qty_akhir',
            12 => 'ks.qty_book_akhir',
            13 => 'ks.qty_free_akhir'
        ];

        $this->db->select('ks.id');
        $this->db->from('kartu_stok ks');
        $this->db->where('ks.deleted', null);
        $this->db->order_by('ks.tgl_transaksi', 'desc');
        $totalData = $this->db->count_all_results();

        $this->db->select('ks.id');
        $this->db->from('kartu_stok ks');
        $this->db->where('ks.deleted', null);
        $this->db->order_by('ks.tgl_transaksi', 'desc');

        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('ks.code_lv4', $like_value);
            $this->db->or_like('ks.nm_material', $like_value);
            $this->db->or_like('ks.no_transaksi', $like_value);
            $this->db->or_like('ks.transaksi', $like_value);
            $this->db->group_end();
        }

        $totalFiltered = $this->db->count_all_results();

        $this->db->select('
        ks.*
    ');
        $this->db->from('kartu_stok ks');
        $this->db->where('ks.deleted', null);
        $this->db->order_by('ks.created_on', 'desc');

        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('ks.code_lv4', $like_value);
            $this->db->or_like('ks.nm_material', $like_value);
            $this->db->or_like('ks.no_transaksi', $like_value);
            $this->db->or_like('ks.transaksi', $like_value);
            $this->db->group_end();
        }

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('ks.tgl_transaksi', 'desc');
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $query = $this->db->get();

        return [
            'totalData' => $totalData,
            'totalFiltered' => $totalFiltered,
            'query' => $query
        ];
    }
}
