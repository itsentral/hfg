<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Incoming_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $ENABLE_ADD     = has_permission('Incoming.Add');
        $ENABLE_MANAGE  = has_permission('Incoming.Manage');
        $ENABLE_VIEW    = has_permission('Incoming.View');
        $ENABLE_DELETE  = has_permission('Incoming.Delete');
    }

    public function generate_id_incoming()
    {
        $tahun  = date('ym');
        $huruf  = "INC";
        $prefix = $huruf . "-" . $tahun . "-";

        $query = $this->db->query("
        SELECT MAX(kode_trans) AS max_id 
        FROM tr_incoming_header 
        WHERE kode_trans LIKE ?
        FOR UPDATE
        ", [$prefix . '%']);

        $row = $query->row();

        if ($row && $row->max_id != null) {
            $urutan = (int) substr($row->max_id, -6);
        } else {
            $urutan = 0;
        }

        $urutan++;

        return $prefix . sprintf("%06s", $urutan);
    }

    public function get_data_json_incoming()
    {
        $requestData    = $_REQUEST;
        $fetch            = $this->query_data_json_incoming(
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
                    (a.tipe IS NULL OR a.tipe = 'pr material')
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

            $nestedData[]    = "<div align='center'>
									<button type='button' class='btn btn-sm btn-primary detailIncoming' title='View Incoming' data-kode_trans='" . $row['kode_trans'] . "' ><i class='fa fa-eye'></i></button>
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

    public function query_data_json_incoming($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        // 1. Mapping kolom untuk Datatables (pastikan index sesuai dengan jumlah kolom di view)
        $columns_order_by = array(
            0 => 'e.kode_trans',
            1 => 'e.kode_trans',
            2 => 'e.no_ipp',
            3 => 'b.nama',
            4 => 'sum_material',
            5 => 'c.nm_lengkap',
            6 => 'e.tanggal'
        );

        // 2. Query Utama (Hapus ORDER BY di sini agar tidak double)
        $sql = "
        SELECT
            a.*, 
            e.tanggal as incoming_date, 
            b.nama as nama_supplier, 
            c.nm_lengkap, 
            IF(SUM(d.qty_order) IS NULL, 0, SUM(d.qty_order)) as sum_material, 
            e.kode_trans, 
            e.no_ipp
        FROM
            tr_purchase_order a
            LEFT JOIN new_supplier b ON b.kode_supplier = a.id_suplier
            JOIN tr_incoming_check e ON e.no_ipp LIKE CONCAT('%',a.no_po,'%')
            LEFT JOIN users c ON c.id_user = e.created_by
            LEFT JOIN tr_incoming_check_detail d ON d.kode_trans = e.kode_trans
        WHERE
            1=1 
            AND (
                a.no_surat LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
                e.kode_trans LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
                b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
                c.nm_lengkap LIKE '%" . $this->db->escape_like_str($like_value) . "%' OR
                a.tanggal LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
        GROUP BY e.kode_trans
        HAVING sum_material > 0
    ";

        // 3. Hitung Total Data (sebelum LIMIT)
        $all_data = $this->db->query($sql);
        $data['totalData'] = $all_data->num_rows();
        $data['totalFiltered'] = $all_data->num_rows();

        // 4. Logika ORDER BY yang Dinamis
        if (!empty($column_order) && isset($columns_order_by[$column_order])) {
            $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir;
        } else {
            $sql .= " ORDER BY e.created_date DESC"; // Default terbaru di atas
        }

        // 5. Tambahkan LIMIT
        $sql .= " LIMIT " . (int)$limit_start . ", " . (int)$limit_length;

        $data['query'] = $this->db->query($sql);
        return $data;
    }
}
