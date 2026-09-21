<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Asset_depreciation_model extends BF_Model
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

    public function data_side_depreciation()
    {
        $requestData = $_REQUEST;
        $kdcab       = isset($requestData['kdcab']) ? $requestData['kdcab'] : '';
        $tgl         = isset($requestData['tgl']) ? $requestData['tgl'] : '';
        $kategori    = isset($requestData['kategori']) ? $requestData['kategori'] : '';
        $bulan       = isset($requestData['bulan']) ? $requestData['bulan'] : '';
        $tahun       = isset($requestData['tahun']) ? $requestData['tahun'] : '';

        $fetch = $this->query_depreciation(
            $kdcab,
            $tgl,
            $kategori,
            $bulan,
            $tahun,
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );

        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $totalAset     = 0;
        $totalSusut    = 0;
        $totalSusutnew = 0;
        $totalSisa     = 0;

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

            $SISA_NILAI = ($row['penyusutan'] == 'N') ? $row['nilai_asset'] : $row['sisa_nilai'];

            $TGL_PEROLEHAN  = date('Y-m-01', strtotime($row['tgl_perolehan']));
            $DEPRESIASI_BLN = $row['depresiasi'] * 12;
            $TGL_LAST_DEPT = date('Ym', strtotime('+' . $DEPRESIASI_BLN . ' month', strtotime($TGL_PEROLEHAN)));
            $TGL_NOW       = $tahun . $bulan;
            $TGL_NOW_DATE   = date('Y-m-01', strtotime($tahun . '-' . $bulan . '-01'));

            $DEPRESIASI = 0;
            if ($TGL_LAST_DEPT > $TGL_NOW && $TGL_PEROLEHAN <= $TGL_NOW_DATE) {
                $DEPRESIASI = $row['value'];
            }

            $totalAset     += $row['nilai_asset'];
            $totalSusut    += $DEPRESIASI;
            $totalSusutnew += $row['total_depresiasi'];
            $totalSisa     += $SISA_NILAI;

            $nestedData   = array();
            $nestedData[] = "<div align='center'>" . $nomor . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['kd_asset']) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_asset']) . "</div>";
            $nestedData[] = "<div align='center'>" . $row['tgl_perolehan'] . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['nm_category']) . "</div>";
            $nestedData[] = "<div align='left'>" . (!empty($row['no_perkiraan']) ? strtoupper($row['no_perkiraan'] . ' | ' . $row['ket_coa']) : '') . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['cost_center']) . "</div>";
            $nestedData[] = "<div align='center'>" . $row['depresiasi'] . " Thn</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['nilai_asset']) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($DEPRESIASI) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['total_depresiasi']) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($SISA_NILAI) . "</div>";

            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        $json_data = array(
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
            "recordsAset"     => intval($totalAset),
            "recordsSusut"    => intval($totalSusut),
            "recordsSusutAk"  => intval($totalSusutnew),
            "recordsSisa"     => intval($totalSisa)
        );

        echo json_encode($json_data);
    }

    public function query_depreciation($kdcab, $tgl, $kategori, $bulan, $tahun, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $where_kdcab = "";
        if (!empty($kdcab)) {
            $where_kdcab = " AND a.kdcab = '" . $this->db->escape_str($kdcab) . "' ";
        }

        $where_kategori = "";
        if ($kategori != '0' && !empty($kategori)) {
            $where_kategori = " AND a.category = '" . $this->db->escape_str($kategori) . "' ";
        }

        $WHERE_PERIODE  = "AND (b.flag='N' OR b.flag='X')";
        $WHERE_PERIODE2 = "AND (b.flag='N' OR b.flag='X')";

        if ($bulan != '0' && $tahun != '0' && !empty($bulan) && !empty($tahun)) {
            $WHERE_PERIODE2 = "AND CONCAT(b.tahun,'-',b.bulan,'-01') <= '" . $tahun . "-" . $bulan . "-01'";
        }

        $sql = "
            SELECT
                a.id,
                a.kd_asset,
                a.nm_asset,
                a.category,
                a.penyusutan,
                c.nm_category,
                a.nilai_asset,
                a.depresiasi,
                a.`value`,
                (SELECT SUM(b.nilai_susut) FROM asset_generate b WHERE a.kd_asset = b.kd_asset AND a.deleted = 'N' " . $WHERE_PERIODE . ") as sisa_nilai,
                (SELECT SUM(b.nilai_susut) FROM asset_generate b WHERE a.kd_asset = b.kd_asset AND a.deleted = 'N' AND b.flag='Y' " . $WHERE_PERIODE2 . ") as total_depresiasi,
                a.department,
                a.kdcab,
                a.cost_center,
                a.tgl_perolehan,
                d.coa AS no_perkiraan,
                d.keterangan AS ket_coa
            FROM
                asset a 
                LEFT JOIN asset_category c ON a.category = c.id
                LEFT JOIN asset_coa d ON a.id_coa = d.id
            WHERE 1=1
                AND a.deleted_date IS NULL
                " . $where_kdcab . "
                " . $where_kategori . "
                AND (
                    a.nm_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                    OR a.category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                    OR a.kd_asset LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                )
            GROUP BY a.kd_asset
        ";

        $data['totalData']     = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();

        $columns_order_by = array(
            0  => 'a.id',
            1  => 'a.kd_asset',
            2  => 'a.nm_asset',
            3  => 'a.tgl_perolehan',
            4  => 'c.nm_category',
            5  => 'd.coa',
            6  => 'a.cost_center',
            7  => 'a.depresiasi',
            8  => 'a.nilai_asset',
            9  => 'a.value',
            10 => 'a.nilai_asset'
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
