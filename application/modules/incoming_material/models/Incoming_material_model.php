<?php
class Incoming_material_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Incoming_material/Master_model'
        ));
    }

    public function get_data_json_incoming_material()
    {
        // $controller			= ucfirst(strtolower($this->uri->segment(1)));
        // $Arr_Akses			= getAcccesmenu($controller);

        $requestData    = $_REQUEST;
        $fetch            = $this->query_data_json_incoming_material(
            $requestData['no_po'],
            $requestData['gudang'],
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData        = $fetch['totalData'];
        $totalFiltered    = $fetch['totalFiltered'];
        $query            = $fetch['query'];

        $data    = array();
        $urut1  = 1;
        $urut2  = 0;
        foreach ($query->result_array() as $row) {
            $total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            }
            if ($asc_desc == 'desc') {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            $no_surat = [];
            $get_no_surat = $this->db->query("SELECT no_surat FROM tr_purchase_order WHERE no_po IN ('" . str_replace(",", "','", $row['no_ipp']) . "')")->result();
            foreach ($get_no_surat as $item) {
                $no_surat[] = $item->no_surat;
            }

            $no_surat = implode(', ', $no_surat);

            $no_pr = [];
            if (!empty($no_surat)) {
                $get_no_pr = $this->db->query("
                SELECT
                    d.no_pr as no_pr
                FROM
                    dt_trans_po a
                    JOIN tr_purchase_order b ON b.no_po = a.no_po
                    JOIN material_planning_base_on_produksi_detail c ON c.id = a.idpr
                    JOIN material_planning_base_on_produksi d ON d.so_number = c.so_number
                WHERE
                    b.no_surat IN ('" . str_replace(",", "','", str_replace(', ', ',', $no_surat)) . "') AND
                    (a.tipe IS NULL OR a.tipe = '')
                GROUP BY d.no_pr

                UNION ALL

                SELECT
                    c.no_pr as no_pr
                FROM
                    dt_trans_po a
                    JOIN tr_purchase_order b ON b.no_po = a.no_po
                    JOIN rutin_non_planning_detail c ON c.id = a.idpr
                WHERE
                    b.no_surat IN ('" . str_replace(",", "','", str_replace(', ', ',', $no_surat)) . "') AND
                    a.tipe = 'pr depart'
                GROUP BY c.no_pr

            ")->result();
                // print_r($this->db->last_query());
                // exit;
            }
            foreach ($get_no_pr as $item_no_pr) {
                $no_pr[] = $item_no_pr->no_pr;
            }

            if (!empty($no_pr)) {
                $no_pr = implode(', ', $no_pr);
            } else {
                $no_pr = '';
            }


            $nestedData     = array();
            $nestedData[]    = "<div align='center'>" . $nomor . "</div>";
            $nestedData[]    = "<div>" . $no_surat . " | " . $row['kode_trans'] . "</div>";
            $nestedData[]    = "<div>" . $no_pr . "</div>";
            $nestedData[]    = "<div>" . $row['nama_supplier'] . "</div>";
            $nestedData[]    = "<div align='right'>" . number_format($row['sum_material'], 2) . "</div>";
            $nestedData[]    = "<div align='left'>" . $row['nm_lengkap'] . "</div>";
            $nestedData[]    = "<div align='left'>" . date('d-M-Y', strtotime($row['incoming_date'])) . "</div>";
            $plus    = "";

            $print    = "&nbsp;<a href='" . base_url('warehouse/print_incoming/' . $row['no_po']) . "' target='_blank' class='btn btn-sm btn-warning' title='Print Incoming'><i class='fa fa-print'></i></a>";
            // if($row['checked'] == 'N'){
            // $plus	= "&nbsp;<button type='button' class='btn btn-sm btn-info check' title='Check Incoming' data-no_ipp='".$row['no_ipp']."' data-users='".str_replace(' ','sp4si', $row['created_by'])."' data-tanggal='".str_replace(' ','sp4si', $row['created_date'])."'><i class='fa fa-check'></i></button>";
            // }

            $nestedData[]    = "<div align='center'>
									<button type='button' class='btn btn-sm btn-primary detailAjust' title='View Incoming' data-kode_trans='" . $row['kode_trans'] . "' ><i class='fa fa-eye'></i></button>
									</div>";
            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        $json_data = array(
            "draw"                => intval($requestData['draw']),
            "recordsTotal"        => intval($totalData),
            "recordsFiltered"     => intval($totalFiltered),
            "data"                => $data
        );

        echo json_encode($json_data);
    }

    public function query_data_json_incoming_material($no_po, $gudang, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {

        $where_no_po = '';
        // if (!empty($no_po)) {
        //     $where_no_po = " AND a.no_ipp = '" . $no_po . "' ";
        // }

        $where_gudang = '';
        // if (!empty($gudang)) {
        //     $where_gudang = " AND a.id_gudang_ke = '" . $gudang . "' ";
        // }

        // $sql = "
        // 	SELECT
        // 		a.*,b.nm_supplier
        // 	FROM
        // 		warehouse_adjustment a
        // 		left join 
        // 			(SELECT no_po,nm_supplier FROM tran_material_po_header)
        // 		b on a.no_ipp=b.no_po
        //     WHERE 1=1 AND a.category = 'incoming material' AND a.status_id = 1
        // 		".$where_no_po."
        // 		".$where_gudang."
        // 	AND(
        // 		a.no_ipp LIKE '%".$this->db->escape_like_str($like_value)."%'
        // 		OR a.kode_trans LIKE '%".$this->db->escape_like_str($like_value)."%'
        // 		OR a.kd_gudang_ke LIKE '%".$this->db->escape_like_str($like_value)."%'
        // 		OR b.nm_supplier LIKE '%".$this->db->escape_like_str($like_value)."%'
        //     )
        // ";

        $sql = "
            SELECT
                a.*, e.tanggal as incoming_date, b.nama as nama_supplier, c.nm_lengkap, IF(SUM(d.qty_order) IS NULL, 0, SUM(d.qty_order)) as sum_material, e.kode_trans, e.no_ipp
            FROM
                tr_purchase_order a
                LEFT JOIN new_supplier b ON b.kode_supplier = a.id_suplier
                JOIN tr_incoming_check e ON e.no_ipp LIKE CONCAT('%',a.no_po,'%')
                LEFT JOIN users c ON c.id_user = e.created_by
                LEFT JOIN tr_incoming_check_detail d ON d.kode_trans = e.kode_trans
            WHERE
                1=1 AND (SELECT IF(SUM(aa.qty_order) IS NULL, 0, SUM(aa.qty_order)) FROM tr_incoming_check_detail aa WHERE aa.kode_trans LIKE CONCAT('%',e.kode_trans,'%')) > 0 AND (
                    a.no_surat LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
                    e.kode_trans LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
                    b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
                    c.nm_lengkap LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
                    a.tanggal LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                )
            GROUP BY e.kode_trans
            ORDER BY e.created_date DESC
        ";
        // echo $sql; exit;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'no_po'
        );

        // $sql .= " ORDER BY created_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

        $data['query'] = $this->db->query($sql);
        return $data;
    }

    public function modal_detail_adjustment()
    {
        $no_po = $this->uri->segment(3);
        // $tanda     	= $this->uri->segment(4);

        // $result            = $this->db->get_where('dt_trans_po', array('no_po' => $no_po))->result_array();
        $this->db->select('a.*, b.code as code_material, b.konversi, c.code as unit_measure, d.code as unit_packing, e.qty_order, e.keterangan as keterangan_check, f.tanggal as tgl_incoming');
        $this->db->from('dt_trans_po a');
        $this->db->join('new_inventory_4 b', 'b.code_lv4 = a.idmaterial', 'left');
        $this->db->join('ms_satuan c', 'c.id = b.id_unit', 'left');
        $this->db->join('ms_satuan d', 'd.id = b.id_unit_packing', 'left');
        $this->db->join('tr_incoming_check_detail e', 'e.id_po_detail = a.id');
        $this->db->join('tr_incoming_check f', 'f.kode_trans = e.kode_trans', 'left');
        $this->db->where('e.kode_trans', $no_po);
        $result = $this->db->get()->result_array();
        $result_header    = $this->db->get_where('tr_purchase_order', array('no_po' => $result[0]['no_po']))->result();

        $get_file_incoming = $this->db->select('no_ipp,file_incoming_material')->get_where('tr_incoming_check', ['kode_trans' => $no_po])->row();

        $no_surat = [];
        $get_no_surat = $this->db
            ->select('no_surat')
            ->from('tr_purchase_order')
            ->where_in('no_po', explode(',', $get_file_incoming->no_ipp))
            ->get()
            ->result();

        foreach ($get_no_surat as $item_surat) {
            $no_surat[] = $item_surat->no_surat;
        }
        $no_surat = implode(', ', $no_surat);



        $data = array(
            'result'     => $result,
            // 'checked'     => $result_header[0]->checked,
            // 'no_po' => $result_header[0]->no_po,
            'no_surat' => $no_surat,
            // 'qty_spk'     => $result_header[0]->qty_spk,
            // 'no_ros'     => $result_header[0]->no_ros,
            'tanggal'     => date('d F Y', strtotime($result[0]['tgl_incoming'])),
            // 'id_milik'     => get_name('materialion_detail', 'id_milik', 'no_spk', $result_header[0]->no_spk),
            // 'dated'     => date('ymdhis', strtotime($result_header[0]->created_date)),
            // 'resv'         => date('d F Y', strtotime($result_header[0]->created_date)),
            'file_incoming_material' => $get_file_incoming->file_incoming_material

        );

        $this->template->set($data);
        $this->template->render('modal_detail_adjustment');
    }

    public function modal_incoming_material()
    {
        $data = $this->input->post();
        $no_po      = $data['no_ipp'] ?? null;
        $kode_trans = $data['kode_trans'] ?? null;
        $arrNoPO    = $data['list_no_po'] ?? [];

        $this->db->select('a.*, b.code as code_material, b.konversi, c.code as unit_measure, d.code as unit_packing, e.qty_order');
        $this->db->from('dt_trans_po a');
        $this->db->join('new_inventory_4 b', 'b.code_lv4 = a.idmaterial', 'left');
        $this->db->join('ms_satuan c', 'c.id = b.id_unit', 'left');
        $this->db->join('ms_satuan d', 'd.id = b.id_unit_packing', 'left');
        $this->db->join('tr_incoming_check_detail e', 'e.id_po_detail = a.id AND e.kode_trans = "' . $kode_trans . '"', 'left');
        $this->db->where_in('a.no_po', $arrNoPO);
        $this->db->group_by('b.code_lv4', 'e.kode_trans');
        $result = $this->db->get()->result_array();

        $result_header = $this->db->select('*')->from('tr_purchase_order')->where_in('no_po', $arrNoPO)->get()->result();

        $list_no_surat = [];
        foreach ($result_header as $item) {
            $list_no_surat[] = $item->no_surat;
        }

        $this->db->select('c.no_pr');
        $this->db->from('dt_trans_po a');
        $this->db->join('material_planning_base_on_produksi_detail b', 'b.id = a.idpr', 'left');
        $this->db->join('material_planning_base_on_produksi c', 'c.so_number = b.so_number', 'left');
        $this->db->where_in('a.no_po', $arrNoPO);
        $this->db->group_by('c.no_pr');
        $get_no_pr = $this->db->get()->result_array();

        $arr_no_pr = array();
        foreach ($get_no_pr as $pr) :
            $arr_no_pr[] = $pr['no_pr'];
        endforeach;

        $no_pr = implode(', ', $arr_no_pr);

        $data = array(
            'no_po' => implode(',', $arrNoPO),
            'no_pr' => $no_pr,
            'no_surat' => implode(',', $list_no_surat),
            'result_header' => $result_header
        );

        $this->template->set($data);
        $this->template->render('modal_incoming_material');
    }
}
