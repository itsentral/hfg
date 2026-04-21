<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_weighing_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // -------------------------------------------------------------------------
    // Pure Calculation Functions (no DB queries)
    // -------------------------------------------------------------------------

    /**
     * Hitung Net Weight timbang awal
     * Net_Weight = berat_coil_tong + berat_cover_wrapping
     *
     * @param float $berat_coil_tong
     * @param float $berat_cover_wrapping
     * @return float
     */
    public function calculate_net_weight($berat_coil_tong, $berat_cover_wrapping)
    {
        return (float) $berat_coil_tong + (float) $berat_cover_wrapping;
    }

    /**
     * Hitung selisih net weight vs packing list dan persentase deviasi
     *
     * @param float $net_timbang  Net weight hasil timbang awal
     * @param float $net_pl       Net weight dari packing list
     * @return array ['selisih_net' => float, 'selisih_net_pct' => float]
     */
    public function calculate_selisih($net_timbang, $net_pl)
    {
        $net_timbang    = (float) $net_timbang;
        $net_pl         = (float) $net_pl;
        $selisih_net    = $net_timbang - $net_pl;
        $selisih_net_pct = ($net_pl > 0) ? abs($selisih_net) / $net_pl : 0;

        return [
            'selisih_net'     => $selisih_net,
            'selisih_net_pct' => $selisih_net_pct,
        ];
    }

    /**
     * Cek apakah selisih pct melebihi toleransi
     *
     * @param float $selisih_pct
     * @return bool
     */
    public function check_exception($selisih_pct)
    {
        $toleransi = (float) get_param('toleransi_timbang_pct', 0.05);
        return (float) $selisih_pct > $toleransi;
    }

    // -------------------------------------------------------------------------
    // Numbering
    // -------------------------------------------------------------------------

    /**
     * Generate nomor preweigh: PW-YYYYMM-XXXX
     */
    public function generate_preweigh_no()
    {
        $prefix = 'PW-' . date('Ym') . '-';
        $row = $this->db->query(
            "SELECT MAX(preweigh_no) AS max_no FROM tr_coil_preweigh WHERE preweigh_no LIKE '{$prefix}%'"
        )->row();
        $last = $row ? (int) substr($row->max_no, -4) : 0;
        return $prefix . sprintf('%04d', $last + 1);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    /**
     * Validasi coil untuk timbang awal:
     * 1. Coil terdaftar dalam SPK berstatus In Process
     * 2. Coil sudah di-issue (ada di tr_stock_move_prod)
     *
     * @param string $no_coil
     * @return array ['valid' => bool, 'message' => string, 'coil_data' => object|null, 'spk_no' => string|null]
     */
    public function validate_coil_for_preweigh($no_coil)
    {
        $coil_data = $this->db
            ->select('d.*, s.spk_no, s.status AS status_spk, s.nm_produk_fg')
            ->from('tr_spk_material_detail d')
            ->join('tr_spk_production s', 's.spk_no = d.spk_no')
            ->where('d.no_coil', $no_coil)
            ->where('s.status', 'In Process')
            ->get()->row();

        if (!$coil_data) {
            return [
                'valid'     => false,
                'message'   => 'Coil ' . $no_coil . ' tidak ditemukan dalam SPK berstatus In Process',
                'coil_data' => null,
                'spk_no'    => null,
            ];
        }

        $issued = $this->db->where('no_coil', $no_coil)
            ->where('spk_no', $coil_data->spk_no)
            ->count_all_results('tr_stock_move_prod');

        if ($issued === 0) {
            return [
                'valid'     => false,
                'message'   => 'Coil ' . $no_coil . ' belum di-issue ke area produksi',
                'coil_data' => null,
                'spk_no'    => null,
            ];
        }

        return [
            'valid'     => true,
            'message'   => 'OK',
            'coil_data' => $coil_data,
            'spk_no'    => $coil_data->spk_no,
        ];
    }

    /**
     * Ambil data gross dan net weight dari packing list (tr_ros_detail)
     *
     * @param string $no_coil
     * @return object|null
     */
    public function get_packing_list_data($no_coil)
    {
        return $this->db->select('no_coil, berat_kotor AS gross_pl, berat_bersih AS net_pl, no_ros')
            ->from('tr_ros_detail')
            ->where('no_coil', $no_coil)
            ->get()->row();
    }

    // -------------------------------------------------------------------------
    // Save
    // -------------------------------------------------------------------------

    /**
     * Simpan timbang awal ke tr_coil_preweigh + tr_coil_preweigh_component (dalam transaksi)
     * Hitung net_timbang_awal, selisih, set status Exception jika perlu, kirim notifikasi
     *
     * @param array $data       Header: spk_no, no_coil, gross_actual, gross_pl, net_pl, created_by
     * @param array $components berat_kulit, berat_clamp_ring, berat_coil_tong, berat_cover_wrapping
     * @return array ['success' => bool, 'message' => string, 'preweigh_no' => string|null]
     */
    public function save_preweigh($data, $components)
    {
        $this->db->trans_start();

        $preweigh_no = $this->generate_preweigh_no();
        $now         = date('Y-m-d H:i:s');

        // Hitung net timbang awal
        $berat_coil_tong      = (float) (isset($components['berat_coil_tong']) ? $components['berat_coil_tong'] : 0);
        $berat_cover_wrapping = (float) (isset($components['berat_cover_wrapping']) ? $components['berat_cover_wrapping'] : 0);
        $net_timbang_awal     = $this->calculate_net_weight($berat_coil_tong, $berat_cover_wrapping);

        // Hitung selisih net
        $net_pl   = (float) (isset($data['net_pl']) ? $data['net_pl'] : 0);
        $selisih  = $this->calculate_selisih($net_timbang_awal, $net_pl);

        // Cek exception
        $is_exception = $this->check_exception($selisih['selisih_net_pct']);
        $status       = $is_exception ? 'Exception' : 'Confirmed';

        // Insert header (selisih_gross adalah generated column, tidak perlu di-insert)
        $this->db->insert('tr_coil_preweigh', [
            'preweigh_no'  => $preweigh_no,
            'spk_no'       => $data['spk_no'],
            'no_coil'      => $data['no_coil'],
            'gross_actual' => (float) (isset($data['gross_actual']) ? $data['gross_actual'] : 0),
            'gross_pl'     => (float) (isset($data['gross_pl']) ? $data['gross_pl'] : 0),
            'net_pl'       => $net_pl,
            'status'       => $status,
            'created_by'   => $data['created_by'],
            'created_at'   => $now,
        ]);

        // Insert komponen (net_timbang_awal adalah generated column, tidak perlu di-insert)
        $this->db->insert('tr_coil_preweigh_component', [
            'preweigh_no'          => $preweigh_no,
            'berat_kulit'          => (float) (isset($components['berat_kulit']) ? $components['berat_kulit'] : 0),
            'berat_clamp_ring'     => (float) (isset($components['berat_clamp_ring']) ? $components['berat_clamp_ring'] : 0),
            'berat_coil_tong'      => $berat_coil_tong,
            'berat_cover_wrapping' => $berat_cover_wrapping,
            'selisih_net'          => $selisih['selisih_net'],
            'selisih_net_pct'      => $selisih['selisih_net_pct'],
        ]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['success' => false, 'message' => 'Gagal menyimpan data timbang awal', 'preweigh_no' => null];
        }

        // Kirim notifikasi jika Exception
        if ($is_exception) {
            $this->send_notification_exception($preweigh_no, $data['spk_no'], $data['no_coil']);
        }

        return ['success' => true, 'message' => 'Data timbang awal berhasil disimpan', 'preweigh_no' => $preweigh_no];
    }

    // -------------------------------------------------------------------------
    // Notification
    // -------------------------------------------------------------------------

    /**
     * Kirim notifikasi exception ke supervisor/QC
     */
    public function send_notification_exception($preweigh_no, $spk_no, $no_coil)
    {
        $judul = 'Exception Timbang Awal: ' . $no_coil;
        $pesan = 'Selisih berat timbang awal coil ' . $no_coil . ' pada SPK ' . $spk_no
               . ' melebihi toleransi. No Timbang: ' . $preweigh_no;

        // Ambil user supervisor/QC dari config param
        $supervisor_ids_raw = get_param('supervisor_user_ids', '');
        $supervisor_ids     = array_filter(array_map('trim', explode(',', $supervisor_ids_raw)));

        if (!empty($supervisor_ids)) {
            foreach ($supervisor_ids as $uid) {
                if (is_numeric($uid)) {
                    send_notification((int) $uid, $judul, $pesan, $preweigh_no, 'production_weighing');
                }
            }
        } else {
            // Fallback: kirim ke semua user aktif
            $users = $this->db->select('id')->from('users')->where('status', 1)->get()->result();
            foreach ($users as $u) {
                send_notification($u->id, $judul, $pesan, $preweigh_no, 'production_weighing');
            }
        }
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    /**
     * Ambil satu record timbang awal beserta info user
     */
    public function get_preweigh($preweigh_no)
    {
        return $this->db
            ->select('p.*, u.name AS nama_user')
            ->from('tr_coil_preweigh p')
            ->join('users u', 'u.id = p.created_by', 'left')
            ->where('p.preweigh_no', $preweigh_no)
            ->get()->row();
    }

    /**
     * Ambil komponen berat untuk satu record timbang awal
     */
    public function get_preweigh_component($preweigh_no)
    {
        return $this->db->get_where('tr_coil_preweigh_component', ['preweigh_no' => $preweigh_no])->row();
    }

    /**
     * Data perbandingan timbang awal vs packing list per coil dalam satu SPK
     */
    public function get_perbandingan_spk($spk_no)
    {
        $sql = "
            SELECT
                p.preweigh_no,
                p.no_coil,
                p.gross_pl,
                p.gross_actual,
                p.selisih_gross,
                p.net_pl,
                c.net_timbang_awal,
                c.selisih_net,
                c.selisih_net_pct,
                p.status,
                p.created_at,
                u.name AS nama_user
            FROM tr_coil_preweigh p
            LEFT JOIN tr_coil_preweigh_component c ON c.preweigh_no = p.preweigh_no
            LEFT JOIN users u ON u.id = p.created_by
            WHERE p.spk_no = ?
            ORDER BY p.created_at ASC
        ";
        return $this->db->query($sql, [$spk_no])->result();
    }

    // -------------------------------------------------------------------------
    // DataTables
    // -------------------------------------------------------------------------

    /**
     * Query DataTables server-side untuk list timbang awal
     */
    public function get_list_for_datatable($params)
    {
        $search    = isset($params['search']['value']) ? $params['search']['value'] : '';
        $start     = isset($params['start']) ? (int) $params['start'] : 0;
        $length    = isset($params['length']) ? (int) $params['length'] : 10;
        $order_col = isset($params['order'][0]['column']) ? (int) $params['order'][0]['column'] : 0;
        $order_dir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';

        $cols     = ['p.preweigh_no', 'p.created_at', 'p.spk_no', 'p.no_coil', 'p.net_pl', 'c.selisih_net_pct', 'p.status'];
        $order_by = isset($cols[$order_col]) ? $cols[$order_col] : 'p.created_at';

        $base_sql = "FROM tr_coil_preweigh p
                     LEFT JOIN tr_coil_preweigh_component c ON c.preweigh_no = p.preweigh_no
                     LEFT JOIN users u ON u.id = p.created_by
                     WHERE 1=1";

        if (!empty($search)) {
            $esc = $this->db->escape_like_str($search);
            $base_sql .= " AND (p.preweigh_no LIKE '%{$esc}%' OR p.spk_no LIKE '%{$esc}%' OR p.no_coil LIKE '%{$esc}%' OR p.status LIKE '%{$esc}%')";
        }

        $total    = $this->db->query("SELECT COUNT(*) AS cnt {$base_sql}")->row()->cnt;
        $filtered = $total;
        $query    = $this->db->query(
            "SELECT p.*, c.net_timbang_awal, c.selisih_net, c.selisih_net_pct, u.name AS nama_user
             {$base_sql}
             ORDER BY {$order_by} {$order_dir}
             LIMIT {$start},{$length}"
        );

        return ['total' => $total, 'filtered' => $filtered, 'data' => $query->result()];
    }
}
