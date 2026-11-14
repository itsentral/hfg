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

    /**
     * Validasi kelayakan hapus master material.
     * Mencegah data yatim jika material masih dipakai di:
     * 1. BOM Aktif (ms_bom_detail join ms_bom_header)
     * 2. Saldo Stok Gudang (warehouse_stock.qty_stock > 0 atau qty_booking > 0)
     * 3. Coil Fisik Aktif di Gudang (warehouse_stock_coil)
     * 4. Transaksi PO yang masih Open (dt_trans_po)
     * 5. Proses QC Incoming yang sedang berjalan (tr_incoming_check_detail)
     * 6. Permintaan Pengeluaran Coil yang aktif (tr_warehouse_request_coil_detail)
     *
     * @param int $id ID new_inventory_4
     * @return array ['valid' => bool, 'pesan' => string, 'details' => array]
     */
    public function validate_delete($id)
    {
        $material = $this->db->get_where($this->table_name, ['id' => $id])->row();
        if (!$material) {
            return [
                'valid'   => false,
                'pesan'   => 'Data material tidak ditemukan.',
                'details' => []
            ];
        }

        $code_lv4 = $material->code_lv4;

        // 1. Validasi BOM Aktif
        $dipakai_bom = $this->db->select('h.id_produk, h.nm_produk, d.no_uniq_id')
            ->from('ms_bom_detail d')
            ->join('ms_bom_header h', 'h.id = d.id_bom')
            ->where('d.id_material', $code_lv4)
            ->where('d.is_delete', 0)
            ->where('h.is_delete', 0)
            ->get()->result_array();
        if (!empty($dipakai_bom)) {
            $prods = array_unique(array_filter(array_map(function ($b) {
                return !empty($b['nm_produk']) ? $b['nm_produk'] : $b['id_produk'];
            }, $dipakai_bom)));
            $prodList = implode(', ', array_slice($prods, 0, 3));
            if (count($prods) > 3) {
                $prodList .= ' dan ' . (count($prods) - 3) . ' produk lainnya';
            }
            return [
                'valid'   => false,
                'pesan'   => 'Material ' . $code_lv4 . ' tidak dapat dihapus karena masih digunakan di BOM Produk (' . $prodList . ').',
                'details' => ['bom' => $prods]
            ];
        }

        // 2. Validasi Stok di Gudang
        $stock = $this->db->select('COALESCE(SUM(qty_stock), 0) AS total_stock, COALESCE(SUM(qty_booking), 0) AS total_booking')
            ->group_start()
                ->where('id_material', $code_lv4)
                ->or_where('code_lv4', $code_lv4)
            ->group_end()
            ->get('warehouse_stock')->row();
        if ($stock && ((float)$stock->total_stock > 0 || (float)$stock->total_booking > 0)) {
            return [
                'valid'   => false,
                'pesan'   => 'Material ' . $code_lv4 . ' tidak dapat dihapus karena masih memiliki saldo stok di gudang (Stok: ' . number_format((float)$stock->total_stock, 2) . ', Booking: ' . number_format((float)$stock->total_booking, 2) . ').',
                'details' => ['stock' => $stock]
            ];
        }

        // 3. Validasi Coil Fisik Aktif di Gudang
        $coil_aktif = $this->db->where('id_material', $code_lv4)
            ->where_in('status_proses', ['in_warehouse', 'in_transit', 'wip', 'booked', 'on_hold'])
            ->count_all_results('warehouse_stock_coil');
        if ($coil_aktif > 0) {
            return [
                'valid'   => false,
                'pesan'   => 'Material ' . $code_lv4 . ' tidak dapat dihapus karena masih terdapat ' . $coil_aktif . ' coil fisik aktif di gudang.',
                'details' => ['active_coils' => $coil_aktif]
            ];
        }

        // 4. Validasi Purchase Order (PO) yang masih Open
        $po_open = $this->db->select('p.no_po, p.namamaterial')
            ->from('dt_trans_po p')
            ->join('tr_purchase_order h', 'h.no_po = p.no_po', 'left')
            ->where('p.idmaterial', $code_lv4)
            ->where('p.close_po', 'N')
            ->where_not_in('COALESCE(h.status, "")', ['Cancel', 'Closed', 'Reject'])
            ->count_all_results();
        if ($po_open > 0) {
            return [
                'valid'   => false,
                'pesan'   => 'Material ' . $code_lv4 . ' tidak dapat dihapus karena masih terdapat ' . $po_open . ' item Purchase Order (PO) yang belum selesai (Open).',
                'details' => ['open_po_items' => $po_open]
            ];
        }

        // 5. Validasi QC Incoming
        $incoming_check = $this->db->from('tr_incoming_check_detail')
            ->where('id_material', $code_lv4)
            ->where('status_action', 'WAITING')
            ->count_all_results();
        if ($incoming_check > 0) {
            return [
                'valid'   => false,
                'pesan'   => 'Material ' . $code_lv4 . ' tidak dapat dihapus karena sedang dalam proses QC / Incoming penerimaan.',
                'details' => ['incoming_check' => $incoming_check]
            ];
        }

        // 6. Validasi Permintaan Pengeluaran Coil Aktif
        $spk_request = $this->db->from('tr_warehouse_request_coil_detail req')
            ->join('tr_warehouse_request_header h', 'h.id = req.request_id', 'left')
            ->where('req.id_material', $code_lv4)
            ->where('req.scan_status', 0)
            ->where_in('COALESCE(h.status, "")', ['Pending', 'Material On Load'])
            ->count_all_results();
        if ($spk_request > 0) {
            return [
                'valid'   => false,
                'pesan'   => 'Material ' . $code_lv4 . ' tidak dapat dihapus karena masih terdapat ' . $spk_request . ' permintaan pengeluaran material ke produksi yang belum selesai diproses.',
                'details' => ['spk_requests' => $spk_request]
            ];
        }

        return [
            'valid'    => true,
            'pesan'    => 'Material valid untuk dihapus.',
            'material' => $material
        ];
    }

    /**
     * Kaskade sinkronisasi perubahan material ke modul Warehouse dan BOM.
     *
     * @param string $code_lv4 Kode material (M4...)
     * @param array $dataProcess Data update dari form
     * @param array|null $oldData Data lama sebelum update
     * @param int|string $id_user User ID yang melakukan update
     * @return array Rincian baris terpengaruh per tabel
     */
    public function sync_material_cascade($code_lv4, $dataProcess, $oldData = null, $id_user = null)
    {
        $now = date('Y-m-d H:i:s');
        $affected = [
            'warehouse_stock'      => 0,
            'warehouse_stock_coil' => 0,
            'ms_bom_detail'        => 0
        ];

        $nama       = isset($dataProcess['nama']) ? $dataProcess['nama'] : null;
        $trade_name = isset($dataProcess['trade_name']) ? $dataProcess['trade_name'] : null;
        $code_lv1   = isset($dataProcess['code_lv1']) ? $dataProcess['code_lv1'] : null;
        $code_lv2   = isset($dataProcess['code_lv2']) ? $dataProcess['code_lv2'] : null;
        $code_lv3   = isset($dataProcess['code_lv3']) ? $dataProcess['code_lv3'] : null;
        $id_unit    = isset($dataProcess['id_unit']) ? $dataProcess['id_unit'] : null;
        $id_packing = isset($dataProcess['id_unit_packing']) ? $dataProcess['id_unit_packing'] : null;
        $thickness  = isset($dataProcess['thickness']) ? (float)$dataProcess['thickness'] : 0.0;
        $width      = isset($dataProcess['width']) ? (float)$dataProcess['width'] : 0.0;

        // 1. Kaskade ke warehouse_stock
        $stockExists = $this->db->group_start()
            ->where('id_material', $code_lv4)
            ->or_where('code_lv4', $code_lv4)
            ->group_end()
            ->get('warehouse_stock')->row();

        if ($stockExists) {
            $this->db->group_start()
                ->where('id_material', $code_lv4)
                ->or_where('code_lv4', $code_lv4)
                ->group_end();
            $this->db->update('warehouse_stock', [
                'nm_material'     => $nama,
                'trade_name'      => $trade_name,
                'code_lv1'        => $code_lv1,
                'code_lv2'        => $code_lv2,
                'code_lv3'        => $code_lv3,
                'id_unit'         => $id_unit,
                'id_unit_packing' => $id_packing,
                'update_by'       => $id_user,
                'update_date'     => $now
            ]);
            $affected['warehouse_stock'] = $this->db->affected_rows();
        } else {
            // Jika belum ada record warehouse_stock, buat record awal
            $this->db->insert('warehouse_stock', [
                'id_material'     => $code_lv4,
                'code_lv1'        => $code_lv1,
                'code_lv2'        => $code_lv2,
                'code_lv3'        => $code_lv3,
                'code_lv4'        => $code_lv4,
                'nm_material'     => $nama,
                'trade_name'      => $trade_name,
                'id_gudang'       => 1,
                'kd_gudang'       => 'PUS',
                'id_unit'         => $id_unit,
                'id_unit_packing' => $id_packing,
                'update_by'       => $id_user,
                'update_date'     => $now
            ]);
            $affected['warehouse_stock'] = 1;
        }

        // 2. Kaskade ke warehouse_stock_coil
        $this->db->where('id_material', $code_lv4);
        $this->db->update('warehouse_stock_coil', [
            'nm_material' => $nama,
            'trade_name'  => $trade_name
        ]);
        $affected['warehouse_stock_coil'] = $this->db->affected_rows();

        // 3. Kaskade ke ms_bom_detail
        $bomUpdate = [
            'nm_material' => $nama,
            'trade_name'  => $trade_name
        ];

        // Jika thickness atau width berubah, hitung ulang berat_per_meter di BOM
        $oldThick = isset($oldData['thickness']) ? (float)$oldData['thickness'] : 0.0;
        $oldWidth = isset($oldData['width']) ? (float)$oldData['width'] : 0.0;
        if ($oldThick !== $thickness || $oldWidth !== $width) {
            $std = $this->db->where('id', 1)->get('master_standard_material')->row();
            if ($std && $thickness > 0 && $width > 0) {
                $new_bpm = round(((($thickness - (float)$std->standar_toleransi) * $width * (float)$std->standar_berat_jenis / 1000) + (float)$std->coating), 6);
                $bomUpdate['berat_per_meter'] = $new_bpm;
            }
        }

        $this->db->where('id_material', $code_lv4);
        $this->db->where('is_delete', 0);
        $this->db->update('ms_bom_detail', $bomUpdate);
        $affected['ms_bom_detail'] = $this->db->affected_rows();

        return $affected;
    }
}
