<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Finalize_incoming extends Admin_Controller
{
    protected $viewPermission   = 'Incoming.View';
    protected $addPermission    = 'Incoming.Add';
    protected $managePermission = 'Incoming.Manage';
    protected $deletePermission = 'Incoming.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Incoming/Incoming_model', 'jurnal_nomor/Jurnal_model', 'all/All_model'));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Finalize Incoming');
        $this->template->render('index');
    }

    // ═══════════════════════════════════════════════════════════════
    // DATA TABLE — Tab DRAFT (submitted)
    // ═══════════════════════════════════════════════════════════════
    public function data_side_draft()
    {
        $ENABLE_MANAGE = has_permission('Incoming.Manage');

        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [1 => 'r.id', 2 => 'r.no_po', 3 => 's.nama', 4 => 'r.submitted_date'];
        $order_by = $col_map[$order_col] ?? 'r.submitted_date';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_ros_header r
              LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
              WHERE r.status = '1' AND r.status_incoming = 'submitted'
              {$where_search}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
            SELECT r.*, s.nama AS nm_supplier,
                   GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list,
                   u.nm_lengkap AS submitted_by_name
            FROM tr_ros_header r
            LEFT JOIN new_supplier s        ON r.id_supplier = s.kode_supplier
            LEFT JOIN tr_purchase_order p   ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
            LEFT JOIN users u               ON u.id_user = r.submitted_by
            WHERE r.status = '1' AND r.status_incoming = 'submitted'
            {$where_search}
            GROUP BY r.id
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $no_po_display = !empty($row['no_surat_list']) ? $row['no_surat_list'] : $row['no_po'];

            $btn_finalize = '';
            if ($ENABLE_MANAGE) {
                $btn_finalize = '<button class="btn btn-sm btn-success btn-finalize" data-id="' . $row['id'] . '" title="Finalize & Close" style="width:100px">
                    <i class="fa fa-check-circle"></i> Finalize
                </button>';
            }

            $btn_revisi = '<button class="btn btn-sm btn-danger btn-revisi" data-id="' . $row['id'] . '" title="Revisi / Kembalikan" style="width:100px">
                <i class="fa fa-undo"></i> Revisi
            </button>';

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'],
                $no_po_display,
                $row['nm_supplier'],
                "<div class='text-center'>" . ($row['submitted_date'] ? date('d/m/Y H:i', strtotime($row['submitted_date'])) : '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars($row['submitted_by_name'] ?? '-') . "</div>",
                "<span class='badge rounded-pill bg-info text-dark'>Submitted</span>",
                "<div class='text-center d-flex justify-content-center gap-1'>{$btn_finalize} {$btn_revisi}</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DATA TABLE — Tab CLOSE
    // ═══════════════════════════════════════════════════════════════
    public function data_side_close()
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [
            1 => 'r.id',
            2 => 'r.no_po',
            3 => 's.nama',
            4 => 'inc.created_at',
            5 => 'inc.kode_trans',
        ];
        $order_by = $col_map[$order_col] ?? 'inc.created_at';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%' OR inc.kode_trans LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_ros_header r
              LEFT JOIN new_supplier s       ON s.kode_supplier  = r.id_supplier
              LEFT JOIN tr_incoming_header inc ON inc.no_ros     = r.id
              WHERE r.status = '1' AND r.status_incoming = 'closed'
              {$where_search}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
            SELECT
                r.*,
                s.nama              AS nm_supplier,
                inc.kode_trans      AS kode_incoming,
                inc.created_at      AS tgl_finalize,
                GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list
            FROM tr_ros_header r
            LEFT JOIN new_supplier s         ON s.kode_supplier  = r.id_supplier
            LEFT JOIN tr_incoming_header inc ON inc.no_ros       = r.id
            LEFT JOIN tr_purchase_order p    ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
            WHERE r.status = '1' AND r.status_incoming = 'closed'
            {$where_search}
            GROUP BY r.id
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $no_po_display = !empty($row['no_surat_list']) ? $row['no_surat_list'] : $row['no_po'];

            $sts_badge = '<span class="badge rounded-pill bg-success">Closed</span>';

            $btn_view = '';
            if (!empty($row['kode_incoming'])) {
                $btn_view = '<a href="' . base_url('incoming/view/' . $row['kode_incoming']) . '"
                        class="btn btn-sm btn-primary" title="Lihat Detail">
                        <i class="fa fa-eye"></i> Detail
                     </a>';
            }

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'],
                $no_po_display,
                $row['nm_supplier'],
                "<div class='text-center'>" . ($row['tgl_finalize'] ? date('d/m/Y H:i', strtotime($row['tgl_finalize'])) : '-') . "</div>",
                "<div class='text-center'>" . ($row['kode_incoming'] ?? '-') . "</div>",
                "<div class='text-center'>{$sts_badge}</div>",
                "<div class='text-center'>{$btn_view}</div>",
            ];
            $no++;
        }

        echo json_encode([
            'draw'            => intval($requestData['draw'] ?? 1),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // AJAX — Preview data coil untuk modal finalize
    // ═══════════════════════════════════════════════════════════════
    public function get_draft_preview()
    {
        $no_ros = $this->input->post('no_ros');

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'No ROS tidak boleh kosong!']);
            return;
        }

        $header = $this->db->query("
            SELECT h.id, h.no_po, h.incoming_date, s.nama AS nm_supplier
            FROM tr_ros_header h
            LEFT JOIN new_supplier s ON s.kode_supplier = h.id_supplier
            WHERE h.id = ? LIMIT 1
        ", [$no_ros])->row_array();

        if (empty($header)) {
            echo json_encode(['status' => 0, 'pesan' => 'Data ROS tidak ditemukan!']);
            return;
        }

        $coils = $this->db->query("
            SELECT c.no_coil, c.berat_kotor, c.berat_bersih, c.panjang,
                c.status_qc, c.kd_gudang_ke,
                m.nm_erp AS nm_material, m.id_barang AS id_material,
                d.qty AS qty_po
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m  ON m.id = c.id_ros_material
            LEFT JOIN dt_trans_po d ON d.id = m.id_po_detail
            WHERE m.id_ros = ?
            ORDER BY m.id_barang, c.no_coil ASC
        ", [$no_ros])->result_array();

        echo json_encode([
            'status' => 1,
            'header' => $header,
            'coils'  => $coils,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // REVISI — Kembalikan ke incoming (status saved)
    // ═══════════════════════════════════════════════════════════════
    public function revisi()
    {
        $no_ros = $this->input->post('no_ros');
        $note   = $this->input->post('revision_note') ?: '';

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'No ROS tidak boleh kosong!']);
            return;
        }

        $ros_header = $this->db->get_where('tr_ros_header', [
            'id'              => $no_ros,
            'status'          => '1',
            'status_incoming' => 'submitted',
        ])->row();

        if (empty($ros_header)) {
            echo json_encode(['status' => 0, 'pesan' => 'Data tidak ditemukan atau sudah diproses!']);
            return;
        }

        $this->db->update('tr_ros_header', [
            'status_incoming' => 'saved',
            'revised_by'      => $this->auth->user_id(),
            'revised_date'    => date('Y-m-d H:i:s'),
            'revision_note'   => $note,
        ], ['id' => $no_ros]);

        echo json_encode(['status' => 1, 'pesan' => 'Data berhasil dikembalikan ke Incoming untuk direvisi.']);
    }

    // ═══════════════════════════════════════════════════════════════
    // FINALIZE — Proses stok + jurnal + close
    // ═══════════════════════════════════════════════════════════════
    public function finalize()
    {
        ob_start();
        $this->auth->restrict($this->managePermission);
        $no_ros  = $this->input->post('no_ros');
        $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');
        $qc_json = $this->input->post('qc_data') ?: '[]';

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'No ROS tidak boleh kosong!']);
            return;
        }

        // Decode QC data dari modal finalize
        $qc_data = json_decode($qc_json, true);
        $qc_map  = [];
        if (!empty($qc_data) && is_array($qc_data)) {
            foreach ($qc_data as $qc) {
                $qc_map[$qc['no_coil']] = $qc['status_qc'] ?? 'OK';
            }
        }

        $ros_header = $this->db->get_where('tr_ros_header', [
            'id'              => $no_ros,
            'status'          => '1',
            'status_incoming' => 'submitted',
        ])->row();

        if (empty($ros_header)) {
            echo json_encode(['status' => 0, 'pesan' => 'Data draft tidak ditemukan!']);
            return;
        }

        // Ambil semua coil
        $coils = $this->db->query("
            SELECT
                c.id                    AS id_ros_material_coil,
                c.no_coil,
                c.kode_internal,
                c.berat_kotor,
                c.berat_bersih,
                c.panjang,
                c.bpm,
                c.id_gudang_ke,
                c.kd_gudang_ke,
                c.status_qc,
                c.price_per_coil,
                m.id                    AS id_ros_material,
                m.id_barang             AS id_material,
                m.nm_erp                AS nm_material,
                m.nm_alias              AS trade_name,
                m.cost_book,
                m.total_nilai_inventory,
                m.id_po_detail,
                h.no_po,
                h.id_supplier,
                h.kurs_pib
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m ON m.id = c.id_ros_material
            JOIN tr_ros_header h   ON h.id = m.id_ros
            WHERE m.id_ros = ?
            ORDER BY m.id_barang, c.no_coil ASC
        ", [$no_ros])->result_array();

        if (empty($coils)) {
            echo json_encode(['status' => 0, 'pesan' => 'Tidak ada data coil!']);
            return;
        }

        foreach ($coils as $c) {
            if (empty($c['id_gudang_ke'])) {
                echo json_encode(['status' => 0, 'pesan' => 'Masih ada coil belum dipilih gudang!']);
                return;
            }
        }

        // Update status_qc dari modal finalize (hanya penanda)
        foreach ($coils as &$c) {
            if (isset($qc_map[$c['no_coil']])) {
                $c['status_qc'] = $qc_map[$c['no_coil']];
                $this->db->update('tr_ros_material_coil', [
                    'status_qc' => $c['status_qc'],
                ], ['id' => (int) $c['id_ros_material_coil']]);
            }
        }
        unset($c);

        // Validasi COA
        $coa_map = ['pusat' => '1105-01-01', 'penjualan' => '1105-01-02', 'intransit' => '1105-01-03'];
        $coa_check = $this->_validate_and_get_coa_names($coa_map);
        if (!$coa_check['valid']) {
            echo json_encode(['status' => 3, 'pesan' => 'COA belum terdaftar: ' . implode(', ', $coa_check['not_found'])]);
            return;
        }

        $this->db->trans_begin();

        $kode_incoming      = $this->Incoming_model->generate_id_incoming();
        $total_berat        = 0;
        $total_nilai_inc    = 0;
        $details_to_insert  = [];

        foreach ($coils as $c) {
            if ((float) $c['berat_bersih'] <= 0) continue;

            $berat_bersih    = (float) $c['berat_bersih'];
            $cost_book       = (float) $c['cost_book'];
            $price_per_coil  = (float) $c['price_per_coil'];
            $nilai_inventory = (float) $c['price_per_coil'];

            $details_to_insert[] = [
                'kode_trans'           => $kode_incoming,
                'id_ros_material_coil' => $c['id_ros_material_coil'],
                'id_ros_material'      => $c['id_ros_material'],
                'id_material'          => $c['id_material'],
                'nm_material'          => $c['nm_material'],
                'trade_name'           => $c['trade_name'],
                'no_coil'              => $c['no_coil'],
                'kode_internal'        => $c['kode_internal'],
                'berat_kotor'          => $c['berat_kotor'],
                'berat_bersih'         => $berat_bersih,
                'panjang'              => $c['panjang'],
                'bpm'                  => $c['bpm'],
                'id_gudang_ke'         => $c['id_gudang_ke'],
                'kd_gudang_ke'         => $c['kd_gudang_ke'],
                'status_qc'            => $c['status_qc'] ?? 'OK',
                'price_per_coil'       => $price_per_coil,
                'cost_book'            => $cost_book,
                'nilai_inventory'      => $nilai_inventory,
            ];

            // Semua coil masuk stok terlepas status QC
            $total_berat     += $berat_bersih;
            $total_nilai_inc += $price_per_coil;

            // Update warehouse stock
            $this->_update_stock_and_history(
                $c['id_material'],
                $c['nm_material'],
                $berat_bersih,
                $cost_book,
                $kode_incoming,
                $ros_header->no_po,
                $c['no_coil'],
                (int) $c['id_gudang_ke'],
                $c['kd_gudang_ke'],
                $no_ros
            );

            // Update qty_in di dt_trans_po
            $this->db->set('qty_in', 'qty_in + ' . $berat_bersih, FALSE);
            $this->db->where('id', $c['id_po_detail']);
            $this->db->update('dt_trans_po');
        }

        // Insert header incoming
        $supplier_row = $this->db->get_where('new_supplier', ['kode_supplier' => $ros_header->id_supplier])->row();
        $nm_supplier  = $supplier_row ? $supplier_row->nama : '';

        $this->db->insert('tr_incoming_header', [
            'kode_trans'         => $kode_incoming,
            'no_ros'             => $no_ros,
            'no_po'              => $ros_header->no_po,
            'id_supplier'        => $ros_header->id_supplier,
            'nm_supplier'        => $nm_supplier,
            'tanggal'            => $tanggal,
            'total_berat_bersih' => $total_berat,
            'total_nilai'        => $total_nilai_inc,
            'file_dokumen'       => $ros_header->file_hash   ?? '',
            'file_original'      => $ros_header->file_original ?? '',
            'status'             => 'finalized',
            'created_by'         => $this->auth->user_id(),
            'created_at'         => date('Y-m-d H:i:s'),
            'finalized_by'       => $this->auth->user_id(),
            'finalized_at'       => date('Y-m-d H:i:s'),
        ]);

        // Insert detail incoming
        foreach ($details_to_insert as $d) {
            $this->db->insert('tr_incoming_detail', $d);
        }

        // Close ROS
        $this->db->update('tr_ros_header', [
            'status_incoming' => 'closed',
        ], ['id' => $no_ros]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            ob_clean();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal memproses finalize!']);
            return;
        }
        $this->db->trans_commit();

        // Generate jurnal
        $jurnal_error = null;
        if ($total_nilai_inc > 0) {
            try {
                $this->_generate_jurnal_incoming(
                    $kode_incoming,
                    $ros_header->no_po,
                    $ros_header->no_surat ?? '',
                    $ros_header->id_supplier,
                    $no_ros,
                    $details_to_insert
                );
            } catch (Exception $e) {
                $jurnal_error = $e->getMessage();
                log_message('error', 'GL error finalize_incoming ' . $kode_incoming . ': ' . $jurnal_error);
            }
        }

        ob_clean();
        header('Content-Type: application/json');

        if ($jurnal_error) {
            echo json_encode(['status' => 2, 'pesan' => 'Finalize berhasil, namun GL Interface gagal: ' . $jurnal_error]);
        } else {
            echo json_encode(['status' => 1, 'pesan' => 'Finalize berhasil! Stok dan Jurnal telah diproses.']);
        }
        exit;
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS (delegasi ke Incoming controller)
    // ═══════════════════════════════════════════════════════════════
    private function _validate_and_get_coa_names(array $coa_list)
    {
        $CI =& get_instance();
        $CI->load->module('incoming');
        return $CI->incoming->_validate_and_get_coa_names($coa_list);
    }

    private function _update_stock_and_history($id_material, $nm_material, $berat_bersih, $cost_book, $kode_incoming, $no_po, $no_coil, $id_gudang_ke, $kd_gudang_ke, $no_ros)
    {
        $CI =& get_instance();
        $CI->load->module('incoming');
        $CI->incoming->_update_stock_and_history($id_material, $nm_material, $berat_bersih, $cost_book, $kode_incoming, $no_po, $no_coil, $id_gudang_ke, $kd_gudang_ke, $no_ros);
    }

    private function _generate_jurnal_incoming($kode_trans, $no_po, $no_surat, $id_supplier, $no_ros, array $details)
    {
        $CI =& get_instance();
        $CI->load->module('incoming');
        $CI->incoming->_generate_jurnal_incoming($kode_trans, $no_po, $no_surat, $id_supplier, $no_ros, $details);
    }
}
