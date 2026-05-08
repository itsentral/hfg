<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Incoming extends Admin_Controller
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
        $this->template->render('index');
    }

    // DATA TABLE — Tab OPEN
    public function data_side_incoming()
    {
        $ENABLE_ADD  = has_permission('Incoming.Add');

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
            4 => 'r.kurs_pib',
        ];
        $order_by = $col_map[$order_col] ?? 'r.id';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_ros_header r
                      LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
                      WHERE r.status = '0' AND (r.status_incoming = 'open' OR r.status_incoming IS NULL)
                      {$where_search}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
            SELECT r.*, s.nama AS nm_supplier,
                   GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list
            FROM tr_ros_header r
            LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
            LEFT JOIN tr_purchase_order p ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
            WHERE r.status = '0' AND (r.status_incoming = 'open' OR r.status_incoming IS NULL)
            {$where_search}
            GROUP BY r.id
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";

        $rows = $this->db->query($sql)->result_array();
        $data = [];
        $no   = $start + 1;

        foreach ($rows as $row) {
            $sts_badge    = '<span class="badge rounded-pill bg-warning text-dark">Open</span>';
            $btn_incoming = '';
            if ($ENABLE_ADD) {
                $btn_incoming = '<a href="' . base_url('incoming/add/' . $row['id']) . '" class="btn btn-sm btn-success" title="Proses Incoming">
                    <i class="fa fa-sign-in-alt"></i> Incoming
                </a>';
            }
            $no_po_display = !empty($row['no_surat_list']) ? $row['no_surat_list'] : $row['no_po'];

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'],
                $no_po_display,
                $row['nm_supplier'],
                "<div class='text-right'>" . number_format((float) $row['kurs_pib'], 0, ',', '.') . "</div>",
                "<div class='text-center'>{$sts_badge}</div>",
                "<div class='text-center'>{$btn_incoming}</div>",
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

    // DATA TABLE — Tab DRAFT
    public function data_side_draft()
    {
        $ENABLE_MANAGE = has_permission('Incoming.Manage');

        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [1 => 'r.id', 2 => 'r.no_po', 3 => 's.nama', 4 => 'r.draft_date'];
        $order_by = $col_map[$order_col] ?? 'r.id';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_ros_header r
                      LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
                      WHERE r.status = '0' AND r.status_incoming = 'draft'
                      {$where_search}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
            SELECT r.*, s.nama AS nm_supplier,
                   GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list,
                   u.nm_lengkap AS draft_by_name
            FROM tr_ros_header r
            LEFT JOIN new_supplier s   ON r.id_supplier = s.kode_supplier
            LEFT JOIN tr_purchase_order p ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
            LEFT JOIN users u          ON u.id_user = r.draft_by
            WHERE r.status = '0' AND r.status_incoming = 'draft'
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
            $coil_ids = $this->db->query(
                "SELECT GROUP_CONCAT(c.id SEPARATOR '-') AS ids
                 FROM tr_ros_material_coil c
                 JOIN tr_ros_material m ON m.id = c.id_ros_material
                 WHERE m.id_ros = ?",
                [$row['id']]
            )->row();
            $coil_ids_str = $coil_ids ? $coil_ids->ids : '';

            $btn_edit = '<a href="' . base_url('incoming/edit_draft/' . $row['id']) . '" class="btn btn-sm btn-warning" title="Edit Draft" style="width:100px">
                <i class="fa fa-edit"></i> Edit
            </a>';

            $btn_print = $coil_ids_str
                ? '<a href="' . base_url('incoming/print_qr/' . $coil_ids_str) . '" target="_blank" class="btn btn-sm btn-info" title="Print Label" style="width:100px">
                    <i class="fa fa-print"></i> Print QR
                  </a>'
                : '';

            $btn_print_pl = '<a href="' . base_url('incoming/print_pl_by_gudang/' . $row['id']) . '" target="_blank" class="btn btn-sm btn-secondary" title="Print Packing List per Gudang" style="width:100px">
                    <i class="fa fa-file-alt"></i> Print PL
                </a>';

            $btn_finalize = '';
            if ($ENABLE_MANAGE) {
                $btn_finalize = '<button class="btn btn-sm btn-success btn-finalize" data-id="' . $row['id'] . '" title="Finalize & Close" style="width:100px">
                    <i class="fa fa-check-circle"></i> Finalize
                </button>';
            }

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'],
                $no_po_display,
                $row['nm_supplier'],
                "<div class='text-center'>" . ($row['draft_date'] ? date('d/m/Y H:i', strtotime($row['draft_date'])) : '-') . "</div>",
                "<div class='text-center'>" . htmlspecialchars($row['draft_by_name'] ?? '-') . "</div>",
                "<span class='badge rounded-pill bg-info text-dark'>Draft</span>",
                "<div class='text-center d-flex flex-column align-items-center gap-1'>
                    <div class='d-flex gap-1'>
                        {$btn_edit} 
                        {$btn_print}
                    </div>
                    <div class='d-flex gap-1'>
                        {$btn_print_pl} 
                        {$btn_finalize}
                    </div>
                </div>"
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

    // FORM ADD
    public function add()
    {
        $this->auth->restrict($this->viewPermission);
        $no_ros_default = $this->uri->segment(3);

        $list_supplier = $this->db->query("
            SELECT DISTINCT(b.kode_supplier), b.nama
            FROM tr_purchase_order a
            LEFT JOIN new_supplier b ON a.id_suplier = b.kode_supplier
            WHERE a.status = '2' AND b.kode_supplier IS NOT NULL
            ORDER BY b.nama ASC
        ")->result();

        $list_gudang = $this->db->query("
            SELECT id, nm_gudang, kd_gudang FROM warehouse WHERE status = 'Y' ORDER BY urut ASC
        ")->result_array();

        $ros_data = null;
        if (!empty($no_ros_default)) {
            $ros_data = $this->db->get_where('tr_ros_header', [
                'id'             => $no_ros_default,
                'status'         => '0',
            ])->row();
            // Hanya boleh add jika masih open
            if ($ros_data && !in_array($ros_data->status_incoming, ['open', null, ''])) {
                redirect('incoming');
            }
        }

        $data = [
            'page_mode'      => 'add',
            'list_supplier'  => $list_supplier,
            'list_gudang'    => $list_gudang,
            'no_ros_default' => $no_ros_default,
            'ros_data'       => $ros_data,
            'draft_coils'    => [],
        ];

        $this->template->set($data);
        $this->template->title('Incoming Based on ROS');
        $this->template->render('form');
    }

    // FORM EDIT DRAFT
    public function edit_draft()
    {
        $this->auth->restrict($this->viewPermission);
        $no_ros = $this->uri->segment(3);

        $ros_data = $this->db->get_where('tr_ros_header', [
            'id'              => $no_ros,
            'status'          => '0',
            'status_incoming' => 'draft',
        ])->row();

        if (empty($ros_data)) {
            show_404();
        }

        $list_supplier = $this->db->query("
            SELECT DISTINCT(b.kode_supplier), b.nama
            FROM tr_purchase_order a
            LEFT JOIN new_supplier b ON a.id_suplier = b.kode_supplier
            WHERE a.status = '2' AND b.kode_supplier IS NOT NULL
            ORDER BY b.nama ASC
        ")->result();

        $list_gudang = $this->db->query("
            SELECT id, nm_gudang, kd_gudang FROM warehouse WHERE status = 'Y' ORDER BY urut ASC
        ")->result_array();

        // Ambil data coil beserta pilihan gudang & QC yang sudah disimpan
        $draft_coils = $this->db->query("
            SELECT c.id AS id_ros_coil_detail, c.no_coil, c.berat_kotor AS ros_kotor,
                   c.berat_bersih AS ros_bersih, c.id_gudang_ke, c.kd_gudang_ke, c.status_qc,
                   m.id AS id_ros_material, m.nm_erp AS nm_material, m.id_barang AS id_material,
                   m.unit_price_usd AS price_coil, m.total_value_rp AS price_coil_idr,
                   d.qty AS qty_po, d.qty_in, d.id AS id_po_detail,
                   h.id AS no_ros
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m      ON m.id = c.id_ros_material
            JOIN tr_ros_header h        ON h.id = m.id_ros
            LEFT JOIN dt_trans_po d     ON d.id = m.id_po_detail
            WHERE h.id = ?
            ORDER BY m.id_barang, c.no_coil ASC
        ", [$no_ros])->result_array();

        // Index by coil id untuk prefill di JS
        $draft_coils_map = [];
        foreach ($draft_coils as $c) {
            $draft_coils_map[$c['id_ros_coil_detail']] = [
                'id_gudang_ke' => $c['id_gudang_ke'],
                'kd_gudang_ke' => $c['kd_gudang_ke'],
                'status_qc'    => $c['status_qc'] ?? 'OK',
            ];
        }

        $data = [
            'page_mode'       => 'edit_draft',
            'list_supplier'   => $list_supplier,
            'list_gudang'     => $list_gudang,
            'no_ros_default'  => $no_ros,
            'ros_data'        => $ros_data,
            'draft_coils'     => $draft_coils,
            'draft_coils_map' => $draft_coils_map,
        ];

        $this->template->set($data);
        $this->template->title('Edit Draft Incoming - ROS ' . $no_ros);
        $this->template->render('form');
    }

    // FORM VIEW
    public function view()
    {
        $kode_trans = $this->uri->segment(3);

        $get_incoming = $this->db->get_where('tr_incoming_check', ['kode_trans' => $kode_trans])->row();

        $sql = "
        SELECT inc.kode_trans, inc.tanggal, inc.no_ros, det.id_material, det.nm_material,
            det.qty_order, det.keterangan, det.harga,
            coil.no_coil, coil.berat_kotor, coil.berat_bersih, coil.panjang AS length,
            mat.nm_erp AS nm_barang, mat.id_barang,
            po.no_po, wh.nm_gudang, wh.kd_gudang, qc.status_qc, qc.id_gudang_ke,
            sup.nama AS nm_supplier
        FROM tr_incoming_check inc
        JOIN tr_incoming_check_detail det   ON det.kode_trans    = inc.kode_trans
        LEFT JOIN tr_ros_header ros          ON ros.id            = inc.no_ros
        LEFT JOIN new_supplier sup           ON sup.kode_supplier = ros.id_supplier
        LEFT JOIN tr_ros_material_coil coil ON coil.no_coil      = SUBSTRING_INDEX(det.keterangan, ': ', -1)
        LEFT JOIN tr_ros_material mat        ON mat.id            = coil.id_ros_material
        LEFT JOIN dt_trans_po po             ON po.id             = det.id_po_detail
        LEFT JOIN warehouse wh               ON wh.kd_gudang      = inc.kd_gudang_ke
        LEFT JOIN tr_checked_incoming_detail qc ON qc.kode_trans  = det.kode_trans
                                                AND qc.id_material = det.id_material
        WHERE inc.kode_trans = ?
        ORDER BY det.id_material, coil.no_coil ASC
    ";
        $detail_ros = $this->db->query($sql, [$kode_trans])->result_array();

        $no_surat_list = [];
        if ($get_incoming) {
            $po_list = array_map('trim', explode(',', $get_incoming->no_ipp ?? ''));
            if (!empty($po_list)) {
                $get_no_surat = $this->db->select('no_surat')->from('tr_purchase_order')
                    ->where_in('no_po', $po_list)->get()->result();
                foreach ($get_no_surat as $item) {
                    $no_surat_list[] = $item->no_surat;
                }
            }
        }

        $data = [
            'page_mode'              => 'view',           // ← tambah ini
            'detail_ros'             => $detail_ros,
            'no_surat'               => implode(', ', $no_surat_list),
            'tanggal'                => $get_incoming ? date('d F Y', strtotime($get_incoming->tanggal)) : '-',
            'file_incoming_material' => $get_incoming->file_incoming_material ?? '',
            'ros_data'               => null,
            'draft_coils'            => [],
            'draft_coils_map'        => [],
            // Tambah ini agar view punya data supplier & no_ros untuk header
            'nm_supplier_view'       => !empty($detail_ros) ? ($detail_ros[0]['nm_supplier'] ?? '-') : '-',
            'no_ros_view'            => $get_incoming->no_ros ?? '-',
            'list_supplier'          => [],
            'list_gudang'            => [],
            'no_ros_default'         => '',
        ];

        $this->template->set($data);
        $this->template->title('Detail Incoming');
        $this->template->render('form');   // ← arahkan ke form, bukan view terpisah
    }

    // SAVE DRAFT
    public function save_draft()
    {
        $post   = $this->input->post();
        $no_ros = $post['no_ros'] ?? '';

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'No ROS tidak boleh kosong!']);
            return;
        }

        if (!empty($post['detail'])) {
            foreach ($post['detail'] as $val) {
                if (empty($val['id_gudang_ke'])) {
                    echo json_encode(['status' => 0, 'pesan' => 'Semua coil harus dipilih gudang tujuannya!']);
                    return;
                }
            }
        }

        // ── Handle file upload ──────────────────────────────────
        // Ambil nama asli sebelum upload (hanya file pertama)
        $file_original = $_FILES['file_incoming_material']['name'][0] ?? '';
        $file_hash     = '';

        if (!empty($file_original)) {
            // Ada file baru → upload
            $file_hash = $this->_upload_incoming_files('file_incoming_material');
        } else {
            // Tidak ada upload baru → pakai nilai lama dari hidden input
            $file_original = $post['existing_file_original'] ?? '';
            $file_hash     = $post['existing_file_hash']     ?? '';
        }
        // ────────────────────────────────────────────────────────

        $this->db->trans_begin();

        foreach ($post['detail'] as $val) {
            $gd        = $this->db->get_where('warehouse', ['id' => (int) $val['id_gudang_ke']])->row();
            $kd_gudang = $gd ? $gd->kd_gudang : '';

            $this->db->update('tr_ros_material_coil', [
                'id_gudang_ke' => (int) $val['id_gudang_ke'],
                'kd_gudang_ke' => $kd_gudang,
                'status_qc'    => $val['status_qc'] ?? 'OK',
            ], ['id' => (int) $val['id_ros_coil']]);
        }

        $this->db->update('tr_ros_header', [
            'status_incoming' => 'draft',
            'draft_by'        => $this->auth->user_id(),
            'draft_date'      => date('Y-m-d H:i:s'),
            'file_original'   => $file_original, // ← tambah
            'file_hash'       => $file_hash,     // ← tambah
        ], ['id' => $no_ros]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menyimpan draft!']);
            return;
        }

        $this->db->trans_commit();

        $coil_ids = $this->db->query(
            "SELECT GROUP_CONCAT(c.id SEPARATOR '-') AS ids
         FROM tr_ros_material_coil c
         JOIN tr_ros_material m ON m.id = c.id_ros_material
         WHERE m.id_ros = ?",
            [$no_ros]
        )->row();

        echo json_encode([
            'status'    => 1,
            'pesan'     => 'Draft berhasil disimpan!',
            'no_ros'    => $no_ros,
            'print_url' => base_url('incoming/print_qr/' . ($coil_ids->ids ?? '')),
        ]);
    }

    // UPDATE DRAFT (dari edit_draft)
    public function update_draft()
    {
        // Sama logikanya dengan save_draft — reuse
        $this->save_draft();
    }

    // FINALIZE (proses stok + jurnal + close)
    public function finalize()
    {
        $this->auth->restrict($this->managePermission);
        $no_ros = $this->input->post('no_ros');

        if (empty($no_ros)) {
            echo json_encode(['status' => 0, 'pesan' => 'No ROS tidak boleh kosong!']);
            return;
        }

        $ros_header = $this->db->get_where('tr_ros_header', [
            'id'              => $no_ros,
            'status'          => '0',
            'status_incoming' => 'draft',
        ])->row();

        if (empty($ros_header)) {
            echo json_encode(['status' => 0, 'pesan' => 'Data draft tidak ditemukan atau sudah difinalize!']);
            return;
        }

        // Ambil semua coil dari draft (yang sudah ada gudang & QC)
        $coils = $this->db->query("
            SELECT c.*, m.id AS id_ros_material, m.nm_erp AS nm_material,
                   m.id_barang AS id_material, m.unit_price_usd, m.total_value_rp,
                   m.bm_rp, m.forwarding_cost, m.total_nilai_inventory, m.cost_book,
                   m.id_po_detail, d.qty AS qty_po, d.qty_in, d.id AS id_po_detail_id,
                   h.no_po
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m  ON m.id = c.id_ros_material
            JOIN tr_ros_header h    ON h.id = m.id_ros
            LEFT JOIN dt_trans_po d ON d.id = m.id_po_detail
            WHERE m.id_ros = ?
            ORDER BY m.id_barang, c.no_coil ASC
        ", [$no_ros])->result_array();

        if (empty($coils)) {
            echo json_encode(['status' => 0, 'pesan' => 'Tidak ada data coil untuk diproses!']);
            return;
        }

        // Validasi semua coil sudah ada gudang
        foreach ($coils as $c) {
            if (empty($c['id_gudang_ke'])) {
                echo json_encode(['status' => 0, 'pesan' => 'Masih ada coil yang belum dipilih gudang. Silakan edit draft terlebih dahulu.']);
                return;
            }
        }

        // Ambil data pendukung dari POST (tanggal, file, dll dari form finalize)
        $tanggal        = $this->input->post('tanggal') ?: date('Y-m-d');
        $uang_muka_idr  = (float) str_replace(',', '', $this->input->post('uang_muka_idr') ?? 0);
        $uang_muka_usd  = (float) str_replace(',', '', $this->input->post('uang_muka') ?? 0);
        if (!empty($_FILES['file_incoming_material']['name'][0])) {
            $link = $this->_upload_incoming_files('file_incoming_material');
            $file_original_final = $_FILES['file_incoming_material']['name'][0] ?? '';
        } else {
            $link                = $ros_header->file_hash     ?? '';
            $file_original_final = $ros_header->file_original ?? '';
        }

        $this->db->trans_begin();

        $kode_incoming      = $this->Incoming_model->generate_id_incoming();
        $total_harga_check  = 0;
        $total_berat_check  = 0;
        $materials_incoming = [];
        $no_po              = $ros_header->no_po;
        $id_supplier        = $ros_header->id_supplier;
        $id_gudang_ke_last  = null;

        foreach ($coils as $val) {
            $aktual_bersih   = (float) ($val['berat_bersih'] ?? 0);
            $id_material     = $val['id_material'];
            $id_po_detail    = $val['id_po_detail_id'];
            $id_ros_material = $val['id_ros_material'];
            $no_coil         = $val['no_coil'];
            $status_qc       = $val['status_qc'] ?? 'OK';
            $id_gudang_ke    = (int) $val['id_gudang_ke'];
            $kd_gudang_ke    = $val['kd_gudang_ke'];

            if ($aktual_bersih <= 0) continue;

            $get_mat = $this->db->select('a.*, d.nama as unit, e.nama as packing')
                ->from('new_inventory_4 a')
                ->join('ms_satuan d', 'a.id_unit = d.id', 'left')
                ->join('ms_satuan e', 'a.id_unit_packing = e.id', 'left')
                ->where('a.code_lv4', $id_material)
                ->get()->row();

            $get_po = $this->db->get_where('dt_trans_po', ['id' => $id_po_detail])->row();

            if (empty($get_mat) || empty($get_po)) continue;

            $price_per_kg = !empty($val['cost_book'])
                ? (float) $val['cost_book']
                : (($val['berat_bersih'] > 0)
                    ? ((float) $val['total_nilai_inventory'] / (float) $val['berat_bersih'])
                    : 0);

            // Insert detail incoming
            $this->db->insert('tr_incoming_check_detail', [
                'kode_trans'      => $kode_incoming,
                'id_po_detail'    => $id_po_detail,
                'no_ipp'          => $no_po,
                'id_material_req' => $id_material,
                'id_material'     => $id_material,
                'nm_material'     => $get_mat->nama,
                'qty_order'       => $get_po->qty,
                'harga'           => $price_per_kg,
                'keterangan'      => "Coil Nomor: " . $no_coil,
            ]);
            $id_detail_inc = $this->db->insert_id();

            // Insert detail QC
            $this->db->insert('tr_checked_incoming_detail', [
                'kode_trans'   => $kode_incoming,
                'id_detail'    => $id_detail_inc,
                'id_material'  => $id_material,
                'nm_material'  => $get_mat->nama,
                'unit'         => $get_mat->unit,
                'packing'      => $get_mat->packing,
                'no_ipp'       => $no_po,
                'qty_order'    => $get_po->qty,
                'qty_incoming' => ($status_qc == 'OK') ? $aktual_bersih : 0,
                'qty_oke'      => ($status_qc == 'OK') ? $aktual_bersih : 0,
                'qty_ng'       => ($status_qc == 'REJECT') ? $aktual_bersih : 0,
                'sts'          => '1',
                'harga'        => $price_per_kg,
                'total_harga'  => $aktual_bersih * $price_per_kg,
                'status_qc'    => $status_qc,
                'id_gudang_ke' => $id_gudang_ke,
            ]);

            if ($status_qc == 'OK') {
                $nilai_coil = (int) round($aktual_bersih * $price_per_kg, 0);

                $this->_update_stock_and_history(
                    $id_material,
                    $get_mat->nama,
                    $aktual_bersih,
                    $price_per_kg,
                    $kode_incoming,
                    $no_po,
                    $no_coil,
                    $id_gudang_ke,
                    $kd_gudang_ke,
                    $no_ros
                );

                $this->db->set('qty_in', 'qty_in + ' . (float) $aktual_bersih, FALSE);
                $this->db->where('id', $id_po_detail);
                $this->db->update('dt_trans_po');

                $total_harga_check += $nilai_coil;
                $total_berat_check += $aktual_bersih;
                $id_gudang_ke_last  = $id_gudang_ke;

                $materials_incoming[] = [
                    'id_material'      => $id_material,
                    'nm_material'      => $get_mat->nama,
                    'qty'              => $aktual_bersih,
                    'harga'            => $price_per_kg,
                    'total_persediaan' => $nilai_coil,
                    'biaya_masuk'      => (float) ($val['bm_rp'] ?? 0),
                    'forwarding'       => (float) ($val['forwarding_cost'] ?? 0),
                    'price_coil_usd'   => (float) ($val['unit_price_usd'] ?? 0),
                    'price_coil_idr'   => (float) ($val['total_value_rp'] ?? 0),
                    'no_coil'          => $no_coil,
                    'id_gudang_ke'     => $id_gudang_ke,
                    'kd_gudang_ke'     => $kd_gudang_ke,
                ];
            }
        }

        // Insert header incoming
        $this->db->insert('tr_incoming_check', [
            'kode_trans'             => $kode_incoming,
            'tanggal'                => $tanggal,
            'no_ipp'                 => $no_po,
            'no_ros'                 => $no_ros,
            'category'               => 'incoming material',
            'jumlah_mat'             => $total_berat_check,
            'id_gudang_dari'         => 1,
            'kd_gudang_dari'         => 'PUS',
            'id_gudang_ke'           => null,
            'kd_gudang_ke'           => 'MULTI',
            'checked'                => 'Y',
            'file_incoming_material' => $link,
            'file_original'          => $file_original_final,
            'created_by'             => $this->auth->user_id(),
            'created_date'           => date('Y-m-d H:i:s'),
        ]);

        // Close ROS
        $this->db->update('tr_ros_header', [
            'status'          => '1',
            'status_incoming' => 'closed',
        ], ['id' => $no_ros]);

        $jurnal_params = null;
        if ($total_harga_check > 0) {
            $jurnal_params = [
                'kode_incoming' => $kode_incoming,
                'no_po'         => $no_po,
                'total'         => $total_harga_check,
                'id_supplier'   => $id_supplier,
                'materials'     => $materials_incoming,
                'uang_muka_idr' => $uang_muka_idr,
                'uang_muka_usd' => $uang_muka_usd,
                'no_ros'        => $no_ros,
                'id_gudang_ke'  => $id_gudang_ke_last,
            ];
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal memproses finalize!']);
            return;
        }
        $this->db->trans_commit();

        $jurnal_error = null;
        if ($jurnal_params !== null) {
            try {
                $this->_generate_jurnal_and_debt(
                    $jurnal_params['kode_incoming'],
                    $jurnal_params['no_po'],
                    $jurnal_params['total'],
                    $jurnal_params['id_supplier'],
                    $jurnal_params['materials'],
                    $jurnal_params['uang_muka_idr'],
                    $jurnal_params['uang_muka_usd'],
                    $jurnal_params['no_ros'],
                    $jurnal_params['id_gudang_ke']
                );
            } catch (Exception $e) {
                $jurnal_error = $e->getMessage();
                log_message('error', 'Jurnal error finalize ' . $kode_incoming . ': ' . $jurnal_error);
            }
        }

        if ($jurnal_error) {
            echo json_encode(['status' => 2, 'pesan' => 'Finalize berhasil, namun jurnal akuntansi gagal. Silakan repost via GL Interface.']);
        } else {
            echo json_encode(['status' => 1, 'pesan' => 'Finalize berhasil! Stok, Hutang, dan Jurnal telah diproses.']);
        }
    }

    // PRINT QR — include info gudang per coil
    public function print_qr($ids)
    {
        $array_id = explode('-', $ids);

        $data_coil = $this->db->query("
            SELECT c.*, m.nm_barang, m.nm_alias, m.nm_erp,
                   h.id as no_ros, h.nm_supplier,
                   w.nm_gudang AS nm_gudang_tujuan,
                   c.kd_gudang_ke
            FROM tr_ros_material_coil c
            JOIN tr_ros_material m  ON m.id = c.id_ros_material
            JOIN tr_ros_header h    ON h.id = m.id_ros
            LEFT JOIN warehouse w   ON w.id  = c.id_gudang_ke
            WHERE c.id IN (" . implode(',', array_map('intval', $array_id)) . ")
        ")->result_array();

        if (empty($data_coil)) {
            die("Data tidak ditemukan.");
        }

        $data = ['results' => $data_coil];
        $this->load->view('print_qr_label', $data);
    }

    // AJAX helpers (tidak berubah)
    public function get_po_by_supplier()
    {
        $id_supplier = $this->input->post('id_supplier');
        $data = $this->db->query("
            SELECT no_po, no_surat, uang_muka, uang_muka_idr
            FROM tr_purchase_order
            WHERE id_suplier = ? AND status = '2'
            ORDER BY no_po DESC
        ", [$id_supplier])->result();
        echo json_encode($data);
    }

    public function get_ros_by_po_select()
    {
        $no_po = $this->input->post('no_po');
        $data = $this->db->query("
            SELECT DISTINCT a.id AS no_ros
            FROM tr_ros_header a
            JOIN tr_ros_material b ON b.id_ros = a.id
            JOIN dt_trans_po c     ON c.id = b.id_po_detail
            WHERE c.no_po = ?
              AND a.status = '0'
              AND (a.status_incoming = 'open' OR a.status_incoming IS NULL)
            ORDER BY a.id ASC
        ", [$no_po])->result();
        echo json_encode($data);
    }

    public function get_ros_detail_to_table()
    {
        $no_ros = $this->input->post('no_ros');
        $query = "
            SELECT c.id AS id_ros_coil_detail, c.no_coil,
                   c.berat_kotor AS ros_kotor, c.berat_bersih AS ros_bersih,
                   c.id_gudang_ke, c.kd_gudang_ke, c.status_qc,
                   b.id AS id_ros_material, b.nm_erp AS nm_material,
                   b.id_barang AS id_material, b.unit_price_usd AS price_coil,
                   b.total_value_rp AS price_coil_idr, b.bm_rp, b.forwarding_cost,
                   d.qty AS qty_po, d.qty_in, d.id AS id_po_detail, a.id AS no_ros
            FROM tr_ros_header a
            JOIN tr_ros_material b      ON a.id = b.id_ros
            JOIN tr_ros_material_coil c ON b.id = c.id_ros_material
            LEFT JOIN dt_trans_po d     ON b.id_po_detail = d.id
            WHERE a.id = ?
            ORDER BY b.id_barang, c.no_coil ASC
        ";
        $data = $this->db->query($query, [$no_ros])->result();
        echo json_encode($data);
    }

    // PROSES INCOMING COIL (SAVE)
    public function process_incoming_coil()
    {
        $post     = $this->input->post();
        $dateTime = date('Y-m-d H:i:s');

        // Validasi gudang per baris
        if (!empty($post['detail'])) {
            foreach ($post['detail'] as $val) {
                if (empty($val['id_gudang_ke'])) {
                    echo json_encode(['status' => 0, 'pesan' => 'Semua coil harus dipilih gudang tujuannya!']);
                    return;
                }
            }
        }

        $this->db->trans_begin();

        $kode_incoming      = $this->Incoming_model->generate_id_incoming();
        $link               = $this->_upload_incoming_files('file_incoming_material');
        $total_harga_check  = 0;
        $total_berat_check  = 0;
        $list_ros           = [];
        $materials_incoming = [];
        $id_gudang_ke       = null;

        foreach ($post['detail'] as $val) {
            $aktual_bersih = (float) str_replace(',', '', $val['aktual_bersih'] ?? 0);
            if ($aktual_bersih <= 0) continue;

            $id_material     = $val['id_material'];
            $id_po_detail    = $val['id_po_detail'];
            $id_ros_material = $val['id_ros_material'];
            $id_ros_coil     = $val['id_ros_coil'];

            // Data material dari inventory
            $get_mat = $this->db
                ->select('a.*, d.nama as unit, e.nama as packing')
                ->from('new_inventory_4 a')
                ->join('ms_satuan d', 'a.id_unit = d.id', 'left')
                ->join('ms_satuan e', 'a.id_unit_packing = e.id', 'left')
                ->where('a.code_lv4', $id_material)
                ->get()->row();

            $get_po       = $this->db->get_where('dt_trans_po',          ['id' => $id_po_detail])->row();
            $get_ros_mat  = $this->db->get_where('tr_ros_material',       ['id' => $id_ros_material])->row();
            $get_ros_coil = $this->db->get_where('tr_ros_material_coil',  ['id' => $id_ros_coil])->row();

            if (empty($get_mat) || empty($get_po) || empty($get_ros_mat) || empty($get_ros_coil)) continue;

            // Harga per kg: gunakan cost_book jika ada
            $price_per_kg = !empty($get_ros_mat->cost_book)
                ? (float) $get_ros_mat->cost_book
                : (($get_ros_coil->berat_bersih > 0)
                    ? ((float) $get_ros_mat->total_nilai_inventory / (float) $get_ros_coil->berat_bersih)
                    : 0);

            // Insert detail incoming
            $this->db->insert('tr_incoming_check_detail', [
                'kode_trans'      => $kode_incoming,
                'id_po_detail'    => $id_po_detail,
                'no_ipp'          => $post['no_po'],
                'id_material_req' => $id_material,
                'id_material'     => $id_material,
                'nm_material'     => $get_mat->nama,
                'qty_order'       => $get_po->qty,
                'harga'           => $price_per_kg,
                'keterangan'      => "Coil Nomor: " . $val['no_coil'],
            ]);
            $id_detail_inc = $this->db->insert_id();

            // Insert detail QC
            $this->db->insert('tr_checked_incoming_detail', [
                'kode_trans'   => $kode_incoming,
                'id_detail'    => $id_detail_inc,
                'id_material'  => $id_material,
                'nm_material'  => $get_mat->nama,
                'unit'         => $get_mat->unit,
                'packing'      => $get_mat->packing,
                'no_ipp'       => $post['no_po'],
                'qty_order'    => $get_po->qty,
                'qty_incoming' => ($val['status_qc'] == 'OK') ? $aktual_bersih : 0,
                'qty_oke'      => ($val['status_qc'] == 'OK') ? $aktual_bersih : 0,
                'qty_ng'       => ($val['status_qc'] == 'REJECT') ? $aktual_bersih : 0,
                'sts'          => '1',
                'harga'        => $price_per_kg,
                'total_harga'  => $aktual_bersih * $price_per_kg,
                'status_qc'    => $val['status_qc'],
            ]);

            if ($val['status_qc'] == 'OK') {
                $id_gudang_ke = (int) $val['id_gudang_ke'];
                $gd           = $this->db->get_where('warehouse', ['id' => $id_gudang_ke])->row();
                $kd_gudang_ke = $gd ? $gd->kd_gudang : 'PUS';

                $nilai_coil = (int) round($aktual_bersih * $price_per_kg, 0);

                $this->_update_stock_and_history(
                    $id_material,
                    $get_mat->nama,
                    $aktual_bersih,
                    $price_per_kg,
                    $kode_incoming,
                    $post['no_po'],
                    $val['no_coil'],
                    $id_gudang_ke,
                    $kd_gudang_ke,
                    $post['no_ros']
                );

                // Update qty_in di dt_trans_po
                $this->db->set('qty_in', 'qty_in + ' . (float) $aktual_bersih, FALSE);
                $this->db->where('id', $id_po_detail);
                $this->db->update('dt_trans_po');

                $total_harga_check += $nilai_coil;
                $total_berat_check += $aktual_bersih;

                $materials_incoming[] = [
                    'id_material'      => $id_material,
                    'nm_material'      => $get_mat->nama,
                    'qty'              => $aktual_bersih,
                    'harga'            => $price_per_kg,
                    'total_persediaan' => $nilai_coil,
                    'biaya_masuk'      => (float) ($get_ros_mat->bm_rp ?? 0),
                    'forwarding'       => (float) ($get_ros_mat->forwarding_cost ?? 0),
                    'price_coil_usd'   => (float) ($get_ros_mat->unit_price_usd ?? 0),
                    'price_coil_idr'   => (float) ($get_ros_mat->total_value_rp ?? 0),
                    'no_coil'          => $val['no_coil'],
                    'id_gudang_ke'     => $id_gudang_ke,
                    'kd_gudang_ke'     => $kd_gudang_ke,
                ];
            }

            if (!empty($val['id_ros_header'])) {
                $list_ros[] = $val['id_ros_header'];
            }
        }

        // Insert header incoming
        $this->db->insert('tr_incoming_check', [
            'kode_trans'             => $kode_incoming,
            'tanggal'                => $post['tanggal'],
            'no_ipp'                 => $post['no_po'],
            'no_ros'                 => $post['no_ros'],
            'category'               => 'incoming material',
            'jumlah_mat'             => $total_berat_check,
            'id_gudang_dari'         => 1,
            'kd_gudang_dari'         => 'PUS',
            'id_gudang_ke'           => null,
            'kd_gudang_ke'           => 'MULTI',
            'checked'                => 'Y',
            'file_incoming_material' => $link,
            'created_by'             => $this->auth->user_id(),
            'created_date'           => $dateTime,
        ]);

        // Update status ROS → '1' (Closed) di tabel baru
        if (!empty($list_ros)) {
            $this->db->where_in('id', array_unique($list_ros));
            $this->db->update('tr_ros_header', ['status' => '1']);
        }

        // Siapkan parameter jurnal
        $jurnal_params = null;
        if ($total_harga_check > 0) {
            $jurnal_params = [
                'kode_incoming' => $kode_incoming,
                'no_po'         => $post['no_po'],
                'total'         => $total_harga_check,
                'id_supplier'   => $post['id_supplier'],
                'materials'     => $materials_incoming,
                'uang_muka_idr' => (float) str_replace(',', '', $post['uang_muka_idr']),
                'uang_muka_usd' => (float) str_replace(',', '', $post['uang_muka']),
                'no_ros'        => $post['no_ros'],
                'id_gudang_ke'  => $id_gudang_ke,
            ];
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal simpan data transaksi!']);
            return;
        }
        $this->db->trans_commit();

        // Jurnal dijalankan setelah commit (error jurnal tidak rollback stok)
        $jurnal_error = null;
        if ($jurnal_params !== null) {
            try {
                $this->_generate_jurnal_and_debt(
                    $jurnal_params['kode_incoming'],
                    $jurnal_params['no_po'],
                    $jurnal_params['total'],
                    $jurnal_params['id_supplier'],
                    $jurnal_params['materials'],
                    $jurnal_params['uang_muka_idr'],
                    $jurnal_params['uang_muka_usd'],
                    $jurnal_params['no_ros'],
                    $jurnal_params['id_gudang_ke']
                );
            } catch (Exception $e) {
                $jurnal_error = $e->getMessage();
                log_message('error', 'Jurnal error untuk ' . $jurnal_params['kode_incoming'] . ': ' . $jurnal_error);
            }
        }

        if ($jurnal_error) {
            echo json_encode(['status' => 2, 'pesan' => 'Data transaksi berhasil disimpan, namun jurnal akuntansi gagal. Silakan repost via menu GL Interface.']);
        } else {
            echo json_encode(['status' => 1, 'pesan' => 'Sukses! Stok, Hutang, dan Jurnal telah diproses.']);
        }
    }

    // UPDATE STOK & HISTORY
    private function _update_stock_and_history($id_material, $nm_material, $qty_in, $price_unit_idr, $kode_trans, $no_po, $no_coil, $id_gudang = 1, $kd_gudang = 'PUS', $no_ros = '')
    {
        $get_stock = $this->db->query(
            "SELECT * FROM warehouse_stock WHERE id_material = ? AND id_gudang = ? LIMIT 1 FOR UPDATE",
            [$id_material, $id_gudang]
        )->row();

        $qty_awal      = !empty($get_stock) ? (float) $get_stock->qty_stock   : 0;
        $harga_lama    = !empty($get_stock) ? (float) $get_stock->harga_beli  : 0;
        $qty_book_awal = !empty($get_stock) ? (float) $get_stock->qty_booking : 0;
        $qty_free_awal = !empty($get_stock) ? (float) $get_stock->qty_free    : 0;
        $saldo_awal    = !empty($get_stock) ? (int) round($get_stock->total_nilai) : 0;

        $nilai_baru        = (int) round($qty_in * $price_unit_idr);
        $qty_akhir         = $qty_awal + $qty_in;
        $total_nilai_akhir = (int) round($saldo_awal + $nilai_baru);
        $costbook          = ($qty_akhir > 0) ? $total_nilai_akhir / $qty_akhir : $price_unit_idr;

        if (empty($get_stock)) {
            $this->db->insert('warehouse_stock', [
                'id_material' => $id_material,
                'code_lv4'    => $id_material,
                'nm_material' => $nm_material,
                'id_gudang'   => $id_gudang,
                'kd_gudang'   => $kd_gudang,
                'incoming'    => $qty_in,
                'qty_booking' => 0,
                'qty_stock'   => $qty_akhir,
                'qty_free'    => $qty_akhir,
                'harga_beli'  => $costbook,
                'total_nilai' => $total_nilai_akhir,
                'update_by'   => $this->auth->user_id(),
                'update_date' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->update('warehouse_stock', [
                'incoming'    => $qty_in,
                'qty_stock'   => $qty_akhir,
                'qty_booking' => $qty_book_awal,
                'qty_free'    => $qty_free_awal + $qty_in,
                'harga_beli'  => $costbook,
                'total_nilai' => $total_nilai_akhir,
                'update_by'   => $this->auth->user_id(),
                'update_date' => date('Y-m-d H:i:s'),
            ], ['id' => $get_stock->id]);
        }

        $this->db->insert('warehouse_history', [
            'id_material'     => $id_material,
            'nm_material'     => $nm_material,
            'id_gudang'       => $id_gudang,
            'kd_gudang'       => $kd_gudang,
            'id_gudang_dari'  => $id_gudang,
            'kd_gudang_dari'  => $kd_gudang,
            'id_gudang_ke'    => $id_gudang,
            'kd_gudang_ke'    => $kd_gudang,
            'qty_stock_awal'  => $qty_awal,
            'qty_stock_akhir' => $qty_akhir,
            'no_ipp'          => $kode_trans,
            'jumlah_mat'      => $qty_in,
            'ket'             => 'QC Incoming Coil Check (Coil NO: ' . $no_coil . ' , PO: ' . $no_po . ')',
            'no_coil'         => $no_coil,
            'harga_beli'      => (int) round($price_unit_idr),
            'total_harga'     => $nilai_baru,
            'saldo_awal'      => $saldo_awal,
            'saldo_akhir'     => $total_nilai_akhir,
            'harga_baru'      => $costbook,
            'harga_lama'      => $harga_lama,
            'update_by'       => $this->auth->user_id(),
            'update_date'     => date('Y-m-d H:i:s'),
        ]);

        $this->db->insert('kartu_stok', [
            'no_transaksi'     => $kode_trans,
            'id_gudang'        => $id_gudang,
            'transaksi'        => 'Incoming Material',
            'tgl_transaksi'    => date('Y-m-d H:i:s'),
            'code_lv4'         => $id_material,
            'code_material'    => $id_material,
            'nm_material'      => $nm_material,
            'qty'              => $qty_awal,
            'qty_book'         => $qty_book_awal,
            'qty_free'         => $qty_free_awal,
            'qty_akhir'        => $qty_akhir,
            'qty_transaksi'    => $qty_in,
            'qty_book_akhir'   => $qty_book_awal,
            'qty_free_akhir'   => $qty_free_awal + $qty_in,
            'harga_stok'       => $costbook,
            'status_transaksi' => 'in',
            'created_by'       => $this->auth->user_id(),
            'created_on'       => date('Y-m-d H:i:s'),
        ]);

        $this->db->insert('warehouse_stock_coil', [
            'id_material' => $id_material,
            'nm_material' => $nm_material,
            'id_gudang'   => $id_gudang,
            'kd_gudang'   => $kd_gudang,
            'no_coil'     => $no_coil,
            'no_ipp'      => $kode_trans,
            'no_po'       => $no_po,
            'no_ros'      => $no_ros,
            'qty'         => $qty_in,
            'harga_beli'  => (int) round($price_unit_idr),
            'total_nilai' => $nilai_baru,
            'status'      => 1,
            'created_by'  => $this->auth->user_id(),
            'created_on'  => date('Y-m-d H:i:s'),
        ]);
    }

    // GENERATE JURNAL & HUTANG (GL Interface)
    private function _generate_jurnal_and_debt($kode_trans, $no_po, $total_rp, $id_supplier, $materials = [], $uang_muka_idr = 0, $uang_muka_usd = 0, $no_ros = '', $id_gudang_ke = null)
    {
        $tgl_inv       = date('Y-m-d');
        $supplier_name = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row()->nama;

        $po_data  = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();
        $currency = $po_data ? strtoupper(trim($po_data->matauang)) : 'IDR';

        $coa_dp      = ($currency === 'IDR') ? '1104-01-01' : '1104-01-02';
        $coa_unbill  = '2101-01-06';
        $coa_bm      = '1108-01-09';
        $coa_forward = '2104-01-13';
        $coa_kurs    = '7201-01-07';

        $coa_persediaan_map     = ['PUS' => '1105-01-01', 'PEN' => '1105-01-02'];
        $coa_persediaan_default = '1105-01-01';

        $keterangan = "Incoming Coil PO: " . $no_po;
        $user_id    = $this->auth->user_id();
        $created_on = date('Y-m-d H:i:s');

        // Ambil kurs_pib dari tr_ros_header (tabel baru)
        $kurs_pib = 0;
        if (!empty($no_ros)) {
            $ros_header = $this->db->get_where('tr_ros_header', ['id' => $no_ros])->row();
            $kurs_pib   = !empty($ros_header) ? (float) $ros_header->kurs_pib : 0;
        }

        $total_biaya_masuk = array_sum(array_column($materials, 'biaya_masuk'));
        $total_forwarding  = array_sum(array_column($materials, 'forwarding'));
        $selisih_kurs      = $uang_muka_idr - ($kurs_pib * $uang_muka_usd);
        $selisih_kurs_abs  = abs($selisih_kurs);
        $total_unbill      = $total_rp - $uang_muka_idr - $total_biaya_masuk - $total_forwarding - $selisih_kurs;
        $nomor_jv = $this->_generate_nomor_jv();

        // Insert header GL Interface
        $this->db->insert('gl_interface', [
            'nomor'           => $nomor_jv,
            'tgl'             => $tgl_inv,
            'bulan'           => date('m'),
            'tahun'           => date('Y'),
            'kdcab'           => '101',
            'jenis'           => 'JV',
            'keterangan'      => $keterangan,
            'jenis_transaksi' => 'incoming',
            'status'          => 'pending',
            'user_id'         => $user_id,
            'memo'            => json_encode([
                'id_supplier'   => $id_supplier,
                'nama_supplier' => $supplier_name,
                'no_reff'       => $no_po,
                'no_request'    => $kode_trans,
            ]),
        ]);
        $id_gl = $this->db->insert_id();

        // DEBET persediaan per coil
        foreach ($materials as $mat) {
            $kd_gd          = $mat['kd_gudang_ke'] ?? '';
            $coa_persediaan = $coa_persediaan_map[$kd_gd] ?? $coa_persediaan_default;
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch'        => null,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_persediaan,
                'id_material'     => $mat['id_material'],
                'nm_material'     => $mat['nm_material'],
                'id_gudang'       => $mat['id_gudang_ke'] ?? null,
                'no_coil'         => $mat['no_coil'],
                'keterangan'      => "Incoming Coil PO: {$no_po} | {$mat['nm_material']} (Coil: {$mat['no_coil']})",
                'no_reff'         => $no_po,
                'no_request'      => $kode_trans,
                'debet'           => $mat['total_persediaan'],
                'kredit'          => 0,
                'created_at'      => $created_on,
            ]);
        }

        // KREDIT Down Payment
        if ($uang_muka_idr > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch' => null,
                'tipe' => 'JV',
                'tanggal' => $tgl_inv,
                'no_perkiraan' => $coa_dp,
                'id_material' => null,
                'nm_material' => null,
                'id_gudang' => $id_gudang_ke,
                'no_coil' => null,
                'keterangan' => $keterangan,
                'no_reff' => $no_po,
                'no_request' => $kode_trans,
                'debet' => 0,
                'kredit' => $uang_muka_idr,
                'created_at' => $created_on,
            ]);
        }

        // KREDIT Unbill
        if ($total_unbill != 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch' => null,
                'tipe' => 'JV',
                'tanggal' => $tgl_inv,
                'no_perkiraan' => $coa_unbill,
                'id_material' => null,
                'nm_material' => null,
                'id_gudang' => $id_gudang_ke,
                'no_coil' => null,
                'keterangan' => $keterangan,
                'no_reff' => $no_po,
                'no_request' => $kode_trans,
                'debet'  => ($total_unbill < 0) ? abs($total_unbill) : 0,
                'kredit' => ($total_unbill > 0) ? $total_unbill : 0,
                'created_at' => $created_on,
            ]);
        }

        // KREDIT Prepaid BM
        if ($total_biaya_masuk > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch' => null,
                'tipe' => 'JV',
                'tanggal' => $tgl_inv,
                'no_perkiraan' => $coa_bm,
                'id_material' => null,
                'nm_material' => null,
                'id_gudang' => $id_gudang_ke,
                'no_coil' => null,
                'keterangan' => $keterangan,
                'no_reff' => $no_po,
                'no_request' => $kode_trans,
                'debet' => 0,
                'kredit' => $total_biaya_masuk,
                'created_at' => $created_on,
            ]);
        }

        // KREDIT Hutang Forwarder
        if ($total_forwarding > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch' => null,
                'tipe' => 'JV',
                'tanggal' => $tgl_inv,
                'no_perkiraan' => $coa_forward,
                'id_material' => null,
                'nm_material' => null,
                'id_gudang' => $id_gudang_ke,
                'no_coil' => null,
                'keterangan' => $keterangan,
                'no_reff' => $no_po,
                'no_request' => $kode_trans,
                'debet' => 0,
                'kredit' => $total_forwarding,
                'created_at' => $created_on,
            ]);
        }

        // DEBET/KREDIT Selisih Kurs
        if ($selisih_kurs_abs > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'no_batch' => null,
                'tipe' => 'JV',
                'tanggal' => $tgl_inv,
                'no_perkiraan' => $coa_kurs,
                'id_material' => null,
                'nm_material' => null,
                'id_gudang' => $id_gudang_ke,
                'no_coil' => null,
                'keterangan' => $keterangan . " | Selisih Kurs (Kurs PIB: " . number_format($kurs_pib, 2) . ")",
                'no_reff' => $no_po,
                'no_request' => $kode_trans,
                'debet'  => ($selisih_kurs < 0) ? $selisih_kurs_abs : 0,
                'kredit' => ($selisih_kurs > 0) ? $selisih_kurs_abs : 0,
                'created_at' => $created_on,
            ]);
        }
    }

    // POST GL INTERFACE (step 2 - posting ke accounting)
    private function _post_gl_interface($nomor_jv)
    {
        $header = $this->db->get_where('gl_interface', ['nomor' => $nomor_jv, 'status' => 'pending'])->row_array();
        if (empty($header)) return;

        $memo          = !empty($header['memo']) ? json_decode($header['memo'], true) : [];
        $id_supplier   = $memo['id_supplier']   ?? null;
        $nama_supplier = $memo['nama_supplier'] ?? null;

        $details = $this->db->get_where('gl_interface_detail', ['id_gl_interface' => $header['id']])->result_array();
        if (empty($details)) return;

        // Balance debet vs kredit, selisih diadjust ke unbill
        $total_debet  = array_sum(array_column($details, 'debet'));
        $total_kredit = array_sum(array_column($details, 'kredit'));
        $selisih      = round($total_debet) - round($total_kredit);
        if ($selisih != 0) {
            foreach ($details as &$line) {
                if ($line['no_perkiraan'] === '2101-01-06') {
                    $line['kredit'] = round($line['kredit'] + $selisih);
                    $this->db->update('gl_interface_detail', ['kredit' => $line['kredit']], ['id' => $line['id']]);
                    break;
                }
            }
            unset($line);
        }

        $this->db->insert(DBACC . '.javh', [
            'nomor' => $nomor_jv,
            'tgl' => $header['tgl'],
            'jml' => $header['jml'],
            'kdcab' => $header['kdcab'],
            'jenis' => $header['jenis'],
            'keterangan' => $header['keterangan'],
            'bulan' => $header['bulan'],
            'tahun' => $header['tahun'],
            'user_id' => $header['user_id'],
        ]);

        foreach ($details as $line) {
            $this->db->insert(DBACC . '.jurnal', [
                'tipe' => $line['tipe'],
                'nomor' => $nomor_jv,
                'tanggal' => $line['tanggal'],
                'no_perkiraan' => $line['no_perkiraan'],
                'keterangan' => $line['keterangan'],
                'no_reff' => $line['no_reff'],
                'debet' => $line['debet'],
                'kredit' => $line['kredit'],
                'id_material' => $line['id_material'],
                'nm_material' => $line['nm_material'],
                'id_gudang' => $line['id_gudang'],
                'no_coil' => $line['no_coil'],
                'created_by' => $header['user_id'],
                'created_on' => date('Y-m-d H:i:s'),
            ]);

            if ($line['kredit'] > 0) {
                $this->db->insert('tr_kartu_hutang', [
                    'tipe' => $line['tipe'],
                    'nomor' => $nomor_jv,
                    'tanggal' => $line['tanggal'],
                    'no_perkiraan' => $line['no_perkiraan'],
                    'keterangan' => $line['keterangan'],
                    'no_reff' => $line['no_reff'],
                    'debet' => 0,
                    'kredit' => $line['kredit'],
                    'id_supplier' => $id_supplier,
                    'nama_supplier' => $nama_supplier,
                    'no_request' => $line['no_request'],
                ]);
            }
        }

        // $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'");
        $this->db->update('gl_interface', ['status' => 'posted', 'posted_at' => date('Y-m-d H:i:s')], ['id' => $header['id']]);
    }

    // REPOST GL INTERFACE
    public function repost_gl_interface()
    {
        $nomor_jv = $this->input->post('nomor_jv');
        if (empty($nomor_jv)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'nomor_jv tidak boleh kosong']));
        }

        $already = $this->db->get_where(DBACC . '.javh', ['nomor' => $nomor_jv])->num_rows();
        if ($already > 0) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'Nomor JV ' . $nomor_jv . ' sudah diposting']));
        }

        $gl = $this->db->get_where('gl_interface', ['nomor' => $nomor_jv, 'status' => 'pending'])->row();
        if (empty($gl)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'Data tidak ditemukan atau sudah diposting']));
        }

        $this->db->trans_start();
        $this->_post_gl_interface($nomor_jv);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'Posting ulang gagal']));
        }

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['status' => 1, 'pesan' => 'Posting ulang berhasil untuk JV ' . $nomor_jv]));
    }

    // UPLOAD FILE
    private function _upload_incoming_files($input_name)
    {
        if (empty($_FILES[$input_name]['name'][0])) return '';

        $config = [
            'upload_path'   => './uploads/incoming_material',
            'allowed_types' => 'gif|jpg|jpeg|png|pdf|zip|rar',
            'max_size'      => 102400,
            'encrypt_name'  => TRUE,
            'remove_spaces' => TRUE,
        ];

        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, TRUE);

        $this->load->library('upload', $config);

        $uploaded_paths = [];
        $files = $_FILES[$input_name];

        foreach ($files['name'] as $key => $image) {
            $_FILES['temp_upload'] = [
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error'    => $files['error'][$key],
                'size'     => $files['size'][$key],
            ];
            $this->upload->initialize($config);
            if ($this->upload->do_upload('temp_upload')) {
                $d = $this->upload->data();
                $uploaded_paths[] = 'uploads/incoming_material/' . $d['file_name'];
            }
        }

        return !empty($uploaded_paths) ? implode('|', $uploaded_paths) : '';
    }

    private function _generate_nomor_jv()
    {
        $cabang = $this->db->query(
            "SELECT nomorJC FROM " . DBACC . ".pastibisa_tb_cabang WHERE nocab = '101' LIMIT 1 FOR UPDATE"
        )->row();

        if (empty($cabang)) {
            throw new Exception('Data cabang tidak ditemukan untuk generate nomor JV!');
        }

        $nomor_urut = (int) $cabang->nomorJC + 1;

        // Hasil: 101-AJV2606779
        $nomor_jv = '101-AJV' . date('ym') . $nomor_urut;

        $this->db->query(
            "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'"
        );

        return $nomor_jv;
    }

    // DATA TABLE — Tab CLOSE
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
            4 => 'inc.created_date',
            5 => 'inc.kode_trans',
        ];
        $order_by = $col_map[$order_col] ?? 'inc.created_date';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR s.nama LIKE '%{$s}%' OR inc.kode_trans LIKE '%{$s}%')";
        }

        $base_sql = " FROM tr_ros_header r
                  LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
                  LEFT JOIN tr_incoming_check inc ON inc.no_ros = r.id
                  WHERE r.status = '1' AND r.status_incoming = 'closed'
                  {$where_search}";

        $total_q   = $this->db->query("SELECT COUNT(*) as cnt {$base_sql}")->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        $sql = "
        SELECT r.*, s.nama AS nm_supplier,
               inc.kode_trans AS kode_incoming,
               inc.created_date AS tgl_finalize,
               GROUP_CONCAT(DISTINCT p.no_surat ORDER BY p.no_surat SEPARATOR ', ') AS no_surat_list
        FROM tr_ros_header r
        LEFT JOIN new_supplier s ON r.id_supplier = s.kode_supplier
        LEFT JOIN tr_incoming_check inc ON inc.no_ros = r.id
        LEFT JOIN tr_purchase_order p ON FIND_IN_SET(p.no_po, REPLACE(r.no_po, ' ', ''))
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

            // Tombol View Detail
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

    public function print_pl_by_gudang($no_ros)
    {
        $this->auth->restrict($this->viewPermission);

        // ── 1. Header ROS ────────────────────────────────────────────────────
        $header_ros = $this->db->query("
        SELECT a.*,
               b.nama AS nm_supplier
        FROM tr_ros_header a
        LEFT JOIN new_supplier b ON a.id_supplier = b.kode_supplier
        WHERE a.id = ?
        LIMIT 1
    ", [$no_ros])->row_array();

        if (empty($header_ros)) {
            show_404();
        }

        // ── 2. Detail coil lengkap dengan info gudang ─────────────────────────
        //    Diambil semua coil yang sudah di-assign ke gudang (kd_gudang_ke IS NOT NULL)
        $detail_coils = $this->db->query("
        SELECT
            c.id                AS id_coil,
            c.no_coil,
            c.berat_kotor,
            c.berat_bersih,
            c.panjang           AS length,
            c.kode_internal,
            c.status_qc,
            c.id_gudang_ke,
            c.kd_gudang_ke,
            w.nm_gudang,
            b.nm_erp            AS nm_material,
            b.id_barang         AS id_material,
            b.nm_alias          AS trade_name,
            IFNULL(ms.code, 'Kg') AS unit_satuan
        FROM tr_ros_material_coil c
        JOIN tr_ros_material b      ON c.id_ros_material = b.id
        JOIN tr_ros_header a        ON b.id_ros = a.id
        LEFT JOIN warehouse w       ON c.id_gudang_ke = w.id
        LEFT JOIN dt_trans_po f     ON b.id_po_detail = f.id AND f.tipe IS NOT NULL
        LEFT JOIN ms_satuan ms      ON ms.id = (
            /* Prioritas satuan: dari PO > dari inventory */
            COALESCE(
                (SELECT id_unit FROM new_inventory_4 WHERE code_lv4 = b.id_barang LIMIT 1),
                (SELECT id_unit_gudang FROM accessories WHERE id = b.id_barang LIMIT 1)
            )
        )
        WHERE a.id = ?
          AND c.id_gudang_ke IS NOT NULL
        ORDER BY w.nm_gudang ASC, b.id_barang ASC, c.no_coil ASC
    ", [$no_ros])->result_array();

        // ── 3. Kelompokkan coil berdasarkan gudang ────────────────────────────
        $grouped_by_gudang = [];   // [ id_gudang => ['info' => [...], 'items' => [...]] ]

        foreach ($detail_coils as $row) {
            $gid = $row['id_gudang_ke'] ?? 'unset';

            if (!isset($grouped_by_gudang[$gid])) {
                $grouped_by_gudang[$gid] = [
                    'id_gudang'  => $row['id_gudang_ke'],
                    'kd_gudang'  => $row['kd_gudang_ke'],
                    'nm_gudang'  => $row['nm_gudang'] ?? '-',
                    'items'      => [],
                ];
            }

            $grouped_by_gudang[$gid]['items'][] = $row;
        }

        // ── 4. Kirim ke view ──────────────────────────────────────────────────
        $data = [
            'header_ros'        => $header_ros,
            'grouped_by_gudang' => $grouped_by_gudang,
            'no_ros'            => $no_ros,
        ];

        $this->load->view('incoming/print_pl_by_gudang', $data);
    }
}
