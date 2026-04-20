<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Incoming extends Admin_Controller
{
    //Permission
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

    public function data_side_incoming()
    {
        $ENABLE_ADD  = has_permission('Incoming.Add');
        $ENABLE_VIEW = has_permission('Incoming.View');

        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'] ?? '';
        $start       = (int) ($requestData['start'] ?? 0);
        $length      = (int) ($requestData['length'] ?? 10);
        $order_col   = $requestData['order'][0]['column'] ?? 1;
        $order_dir   = $requestData['order'][0]['dir']    ?? 'desc';

        $col_map = [
            1 => 'r.id',
            2 => 'r.no_po',
            3 => 'r.nm_supplier',
            4 => 'r.kurs_pib',
            5 => 'r.eta_warehouse',
        ];
        $order_by = isset($col_map[$order_col]) ? $col_map[$order_col] : 'r.id';

        $where_search = '';
        if (!empty($search)) {
            $s = $this->db->escape_like_str($search);
            $where_search = " AND (r.id LIKE '%{$s}%' OR r.no_po LIKE '%{$s}%' OR r.nm_supplier LIKE '%{$s}%')";
        }

        // Total data
        $total_q = $this->db->query("SELECT COUNT(*) as cnt FROM tr_ros r WHERE r.sts = '0'" . $where_search)->row();
        $totalData = $total_q ? (int) $total_q->cnt : 0;

        // Data query
        $sql = "
            SELECT r.id, r.no_po, r.nm_supplier, r.kurs_pib, r.eta_warehouse, r.sts
            FROM tr_ros r
            WHERE r.sts = '0'
            {$where_search}
            ORDER BY {$order_by} {$order_dir}
            LIMIT {$start}, {$length}
        ";
        $rows = $this->db->query($sql)->result_array();

        $data = [];
        $no   = $start + 1;
        foreach ($rows as $row) {
            $sts_badge = '<span class="badge rounded-pill bg-warning text-dark">Open</span>';

            $btn_incoming = '';
            if ($ENABLE_ADD) {
                $btn_incoming = '<a href="' . base_url('incoming/add/' . $row['id']) . '" class="btn btn-sm btn-success" title="Proses Incoming">
                    <i class="fa fa-sign-in-alt"></i> Incoming
                </a>';
            }

            $data[] = [
                "<div class='text-center'>{$no}</div>",
                $row['id'],
                $row['no_po'],
                $row['nm_supplier'],
                number_format((float) $row['kurs_pib'], 0, ',', '.'),
                $row['eta_warehouse'] ? date('d-M-Y', strtotime($row['eta_warehouse'])) : '-',
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

    public function add()
    {
        $this->auth->restrict($this->viewPermission);

        $no_ros_default = $this->uri->segment(3); // incoming/add/{no_ros}

        $list_supplier = $this->db->query("
        SELECT DISTINCT(b.kode_supplier), b.nama 
        FROM tr_purchase_order a 
        LEFT JOIN new_supplier b ON a.id_suplier = b.kode_supplier 
        WHERE a.status = '2' AND b.kode_supplier IS NOT NULL 
        ORDER BY b.nama ASC
        ")->result();

        $list_gudang = $this->db->query("SELECT id, nm_gudang, kd_gudang FROM warehouse WHERE status = 'Y' ORDER BY urut ASC")->result_array();

        // Jika no_ros diberikan dari URL, ambil data ROS untuk pre-fill form
        $ros_data = null;
        if (!empty($no_ros_default)) {
            $ros_data = $this->db->get_where('tr_ros', ['id' => $no_ros_default, 'sts' => '0'])->row();
        }

        $data = [
            'list_supplier'  => $list_supplier,
            'list_gudang'    => $list_gudang,
            'no_ros_default' => $no_ros_default,
            'ros_data'       => $ros_data,
        ];

        $this->template->set($data);
        $this->template->title('Incoming Based on ROS');
        $this->template->render('form');
    }

    public function view()
    {
        $no_po = $this->uri->segment(3);

        // 1. Ambil data utama dengan JOIN ke tr_ros_detail untuk mendapatkan data per COIL
        $this->db->select('
        a.*, 
        b.code as id_barang, b.konversi, 
        c.code as unit_measure, 
        d.code as unit_packing, 
        e.qty_order, e.keterangan as keterangan_check, 
        f.tanggal as tgl_incoming,
        g.no_coil, g.berat_kotor, g.berat_bersih, g.length, g.nm_barang, g.id_po_detail
    ');
        $this->db->from('dt_trans_po a');
        $this->db->join('new_inventory_4 b', 'b.code_lv4 = a.idmaterial', 'left');
        $this->db->join('ms_satuan c', 'c.id = b.id_unit', 'left');
        $this->db->join('ms_satuan d', 'd.id = b.id_unit_packing', 'left');
        $this->db->join('tr_incoming_check_detail e', 'e.id_po_detail = a.id');
        $this->db->join('tr_incoming_check f', 'f.kode_trans = e.kode_trans', 'left');

        // JOIN ke detail ROS berdasarkan id_po_detail agar bisa grouping per coil
        $this->db->join('tr_ros_detail g', 'g.id_po_detail = a.id', 'left');

        $this->db->where('e.kode_trans', $no_po);
        $result = $this->db->get()->result_array();

        // 2. Ambil informasi file dan header
        $get_file_incoming = $this->db->select('no_ipp, file_incoming_material')
            ->get_where('tr_incoming_check', ['kode_trans' => $no_po])
            ->row();

        // 3. Ambil No Surat (PO)
        $no_surat_list = [];
        if ($get_file_incoming) {
            $get_no_surat = $this->db
                ->select('no_surat')
                ->from('tr_purchase_order')
                ->where_in('no_po', explode(',', $get_file_incoming->no_ipp))
                ->get()
                ->result();

            foreach ($get_no_surat as $item_surat) {
                $no_surat_list[] = $item_surat->no_surat;
            }
        }
        $no_surat = implode(', ', $no_surat_list);

        // 4. Kirim data ke View
        // Kita kirim 'detail_ros' yang berisi hasil join tadi untuk di-loop dengan logika grouping
        $data = array(
            'detail_ros'             => $result, // Ini variabel yang akan di-foreach di view terbaru
            'no_surat'               => $no_surat,
            'tanggal'                => isset($result[0]['tgl_incoming']) ? date('d F Y', strtotime($result[0]['tgl_incoming'])) : '-',
            'file_incoming_material' => $get_file_incoming->file_incoming_material ?? ''
        );

        $this->template->set($data);
        $this->template->render('view');
    }

    public function get_po_by_supplier()
    {
        $id_supplier = $this->input->post('id_supplier');
        $data = $this->db->query("
        SELECT no_po, no_surat, uang_muka, uang_muka_idr
        FROM tr_purchase_order 
        WHERE id_suplier = '$id_supplier' AND status = '2'
        ORDER BY no_po DESC
    ")->result();
        echo json_encode($data);
    }


    public function get_ros_by_po_select()
    {
        $no_po = $this->input->post('no_po');
        // Ambil nomor ROS unik dari tabel detail yang terhubung ke PO tersebut
        $data = $this->db->query("
        SELECT DISTINCT a.no_ros 
        FROM tr_ros_detail a
        LEFT JOIN dt_trans_po b ON a.id_po_detail = b.id
        LEFT JOIN tr_ros c ON c.id = a.no_ros
        WHERE b.no_po = '$no_po'
        AND c.sts = 0
        ORDER BY a.no_ros ASC
    ")->result();
        echo json_encode($data);
    }

    // Update fungsi detail ini agar memfilter berdasarkan no_ros, bukan cuma no_po
    public function get_ros_detail_to_table()
    {
        $no_ros = $this->input->post('no_ros');
        $query = "SELECT
                a.id AS id_ros_detail,
                a.no_coil,
                a.no_ros,
                a.berat_kotor AS ros_kotor,
                a.berat_bersih AS ros_bersih, 
                a.nm_barang AS nm_material,
                a.id_barang AS id_material,
                a.price_coil AS price_coil,
                a.price_coil_idr AS price_coil_idr,
                a.biaya_masuk AS biaya_masuk,
                a.forwarding_cost AS forwarding_cost,
                b.qty AS qty_po,
                b.qty_in,
                b.id AS id_po_detail
                FROM tr_ros_detail a
                LEFT JOIN dt_trans_po b ON a.id_po_detail = b.id
                WHERE a.no_ros = '$no_ros' 
                ORDER BY a.id_barang, a.no_coil ASC";
        $data = $this->db->query($query)->result();
        echo json_encode($data);
    }

    public function process_incoming_coil()
    {
        $post = $this->input->post();
        $dateTime = date('Y-m-d H:i:s');

        // Validasi: pastikan semua coil sudah dipilih gudang tujuan
        if (!empty($post['detail'])) {
            foreach ($post['detail'] as $idx => $val) {
                if (empty($val['aktual_bersih']) || $val['aktual_bersih'] == 0) continue;
                if (empty($val['id_gudang_ke'])) {
                    echo json_encode(['status' => 0, 'pesan' => 'Semua coil harus dipilih gudang tujuannya!']);
                    return;
                }
            }
        }

        $this->db->trans_begin();

        $kode_incoming = $this->Incoming_model->generate_id_incoming();
        $link = $this->_upload_incoming_files('file_incoming_material');

        $total_harga_check = 0;
        $total_berat_check = 0;
        $list_ros = []; // Untuk menampung ID ROS yang terlibat
        $materials_incoming = []; // Untuk jurnal per material

        foreach ($post['detail'] as $val) {
            // Lewati jika tidak ada input berat (mencegah data sampah)
            if (empty($val['aktual_bersih']) || $val['aktual_bersih'] == 0) continue;

            $aktual_bersih = str_replace(',', '', $val['aktual_bersih']);
            $id_material  = $val['id_material'];
            $id_po_detail = $val['id_po_detail'];
            $id_ros_detail = $val['id_ros_detail'];

            $get_mat = $this->db
                ->select('a.*, d.nama as unit, e.nama as packing')
                ->from('new_inventory_4 a')
                ->join('ms_satuan d', 'a.id_unit = d.id', 'left')
                ->join('ms_satuan e', 'a.id_unit_packing = e.id', 'left')
                ->where('a.code_lv4', $id_material)
                ->get();
            $get_mat = ($get_mat !== false) ? $get_mat->row() : null;

            $q_po = $this->db->get_where('dt_trans_po', ['id' => $id_po_detail]);
            $get_po = ($q_po !== false) ? $q_po->row() : null;

            $q_ros = $this->db->get_where('tr_ros_detail', ['id' => $id_ros_detail]);
            $get_ros = ($q_ros !== false) ? $q_ros->row() : null;

            if (empty($get_mat) || empty($get_po) || empty($get_ros)) continue;

            // 1. Insert Detail Utama
            $this->db->insert('tr_incoming_check_detail', [
                'kode_trans'   => $kode_incoming,
                'id_po_detail' => $id_po_detail,
                'no_ipp'       => $post['no_po'],
                'id_material_req'  => $id_material,
                'id_material'  => $id_material,
                'nm_material'  => $get_mat->nama,
                'qty_order'    => $get_po->qty,
                'harga'        => $get_ros->price_unit_idr,
                'keterangan'   => "Coil Nomor: " . $val['no_coil']
            ]);

            $id_detail_inc = $this->db->insert_id();

            // 2. Insert Detail QC
            $this->db->insert('tr_checked_incoming_detail', [
                'kode_trans'    => $kode_incoming,
                'id_detail'     => $id_detail_inc,
                'id_material'   => $id_material,
                'nm_material'   => $get_mat->nama,
                'unit'          => $get_mat->unit,
                'packing'       => $get_mat->packing,
                'no_ipp'        => $post['no_po'],
                'qty_order'     => $get_po->qty,
                'qty_incoming'  => ($val['status_qc'] == 'OK') ? $aktual_bersih : 0,
                'qty_oke'       => ($val['status_qc'] == 'OK') ? $aktual_bersih : 0,
                'qty_ng'        => ($val['status_qc'] == 'REJECT') ? $aktual_bersih : 0,
                'sts'           => '1',
                'harga'         => $get_ros->price_unit_idr,
                'total_harga'   => $aktual_bersih * $get_ros->price_unit_idr
            ]);

        // 3. Proses jika OK
            if ($val['status_qc'] == 'OK') {
                $price_per_kg = ($get_ros->berat_bersih > 0)
                    ? ($get_ros->total_nilai / $get_ros->berat_bersih)
                    : 0;

                // Gudang tujuan per coil — ambil dari DB untuk keamanan
                $id_gudang_ke = (int) $val['id_gudang_ke'];
                $gd = $this->db->get_where('warehouse', ['id' => $id_gudang_ke])->row();
                $kd_gudang_ke = $gd ? $gd->kd_gudang : 'PUS';

                // Nilai masuk = qty × harga, dibulatkan ke rupiah penuh (tanpa desimal)
                // agar sama dengan nilai yang tercatat di warehouse_history
                $nilai_coil = (int) round($aktual_bersih * $price_per_kg, 0);

                $this->_update_stock_and_history($id_material, $get_mat->nama, $aktual_bersih, $price_per_kg, $kode_incoming, $post['no_po'], $val['no_coil'], $id_gudang_ke, $kd_gudang_ke, $post['no_ros']);

                // Update qty_in di detail PO
                $this->db->set('qty_in', 'qty_in + ' . (float)$aktual_bersih, FALSE);
                $this->db->where('id', $id_po_detail);
                $this->db->update('dt_trans_po');

                $biaya_masuk_coil   = (float) str_replace(',', '', $val['biaya_masuk']);
                $forwarding_coil    = (float) str_replace(',', '', $val['forwarding_cost']);
                $price_coil_usd     = (float) str_replace(',', '', $val['price_coil']);
                $price_coil_idr     = (float) str_replace(',', '', $val['price_coil_idr']);

                $total_harga_check += $nilai_coil;
                $total_berat_check += $aktual_bersih;

                // Kumpulkan per material untuk jurnal persediaan
                $materials_incoming[] = [
                    'id_material'      => $id_material,
                    'nm_material'      => $get_mat->nama,
                    'qty'              => $aktual_bersih,
                    'harga'            => $price_per_kg,
                    'total_persediaan' => $nilai_coil,          // Debet COA persediaan
                    'biaya_masuk'      => $biaya_masuk_coil,    // Kredit 1108-01-09
                    'forwarding'       => $forwarding_coil,     // Kredit 2104-01-13
                    'price_coil_usd'   => $price_coil_usd,      // Untuk hitung selisih kurs
                    'price_coil_idr'   => $price_coil_idr,      // Nilai IDR saat ini
                    'no_coil'          => $val['no_coil'],
                    'id_gudang_ke'     => $id_gudang_ke,        // Untuk mapping COA persediaan
                    'kd_gudang_ke'     => $kd_gudang_ke,
                ];
            }

            // Simpan ID ROS untuk diupdate statusnya nanti
            if (!empty($val['no_ros'])) {
                $list_ros[] = $val['no_ros'];
            }
        }

        // 4. Insert Header Incoming
        $this->db->insert('tr_incoming_check', [
            'kode_trans'   => $kode_incoming,
            'tanggal'      => $post['tanggal'],
            'no_ipp'       => $post['no_po'],
            'no_ros'       => $post['no_ros'],
            'category'     => 'incoming material',
            'jumlah_mat'   => $total_berat_check,
            'id_gudang_dari' => 1,
            'kd_gudang_dari' => 'PUS',
            'id_gudang_ke'   => null,
            'kd_gudang_ke'   => 'MULTI',
            'checked'      => 'Y',
            'file_incoming_material' => $link,
            'created_by'   => $this->auth->user_id(),
            'created_date' => $dateTime
        ]);

        // 5. Update status semua ROS yang terlibat menjadi '1' (Closed)
        if (!empty($list_ros)) {
            $this->db->where_in('id', array_unique($list_ros));
            $this->db->update('tr_ros', ['sts' => '1']);
        }

        //6. Generate Jurnal & Hutang (Hanya 1x panggil)
        // Jurnal dijalankan di luar transaction utama agar tidak rollback data transaksi jika jurnal gagal.
        // gl_interface sudah menjadi safety net — jika posting ke accounting gagal,
        // status gl_interface tetap 'pending' dan bisa direpost via repost_gl_interface().
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

        // Finalisasi Transaksi Utama (stok, incoming, ROS)
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal simpan data transaksi!']);
            return;
        }

        $this->db->trans_commit();

        // Jurnal dijalankan setelah transaksi utama commit
        // Error jurnal tidak mempengaruhi data transaksi yang sudah tersimpan
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
            echo json_encode([
                'status' => 2,
                'pesan'  => 'Data transaksi berhasil disimpan, namun jurnal akuntansi gagal diposting. Silakan lakukan repost dari menu GL Interface.',
            ]);
        } else {
            echo json_encode([
                'status' => 1,
                'pesan'  => 'Sukses! Stok, Hutang, dan Jurnal telah diproses.',
            ]);
        }
    }

    private function _update_stock_and_history($id_material, $nm_material, $qty_in, $price_unit_idr, $kode_trans, $no_po, $no_coil, $id_gudang = 1, $kd_gudang = 'PUS', $no_ros = '')
    {
        // Pakai SELECT ... FOR UPDATE agar baca nilai terbaru dalam transaction
        $get_stock = $this->db->query(
            "SELECT * FROM warehouse_stock WHERE id_material = ? AND id_gudang = ? LIMIT 1 FOR UPDATE",
            [$id_material, $id_gudang]
        )->row();

        $qty_awal      = !empty($get_stock) ? (float) $get_stock->qty_stock   : 0;
        $harga_lama    = !empty($get_stock) ? (float) $get_stock->harga_beli  : 0;
        $qty_book_awal = !empty($get_stock) ? (float) $get_stock->qty_booking : 0;
        $qty_free_awal = !empty($get_stock) ? (float) $get_stock->qty_free    : 0;

        // Saldo awal diambil langsung dari total_nilai yang tersimpan di stok
        // agar saldo_akhir baris sebelumnya = saldo_awal baris ini
        $saldo_awal = !empty($get_stock) ? (int) round($get_stock->total_nilai) : 0;

        $nilai_baru        = (int) round($qty_in * $price_unit_idr);
        $qty_akhir         = $qty_awal + $qty_in;
        $total_nilai_akhir = (int) round($saldo_awal + $nilai_baru);

        // Moving average cost (untuk harga_beli di warehouse_stock)
        $costbook = ($qty_akhir > 0)
            ? $total_nilai_akhir / $qty_akhir
            : $price_unit_idr;

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
                'update_date' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->db->update('warehouse_stock', [
                'incoming'    => $qty_in,
                'qty_stock'   => $qty_akhir,
                'qty_booking' => $qty_book_awal,
                'qty_free'    => $qty_free_awal + $qty_in,
                'harga_beli'  => $costbook,          // moving average terbaru
                'total_nilai' => $total_nilai_akhir, // qty_akhir * costbook
                'update_by'   => $this->auth->user_id(),
                'update_date' => date('Y-m-d H:i:s')
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
            'harga_beli'      => (int) round($price_unit_idr),  // harga beli per unit saat incoming
            'total_harga'     => $nilai_baru,                   // qty_in * harga_beli (bulat)
            'saldo_awal'      => $saldo_awal,                   // dari total_nilai stok sebelumnya
            'saldo_akhir'     => $total_nilai_akhir,            // saldo_awal + nilai_baru (bulat)
            'harga_baru'      => $costbook,                     // moving average terbaru
            'harga_lama'      => $harga_lama,                   // harga rata-rata sebelumnya
            'update_by'       => $this->auth->user_id(),
            'update_date'     => date('Y-m-d H:i:s')
        ]);

        $this->db->insert('kartu_stok', [
            'no_transaksi'  => $kode_trans,
            'id_gudang'     => $id_gudang,
            'transaksi'     => "Incoming Material",
            'tgl_transaksi' => date('Y-m-d H:i:s'),
            'code_lv4'      => $id_material,
            'code_material' => $id_material,
            'nm_material'   => $nm_material,
            'qty'           => $qty_awal,
            'qty_book'      => $qty_book_awal,
            'qty_free'      => $qty_free_awal,
            'qty_akhir'     => $qty_akhir,
            'qty_transaksi' => $qty_in,
            'qty_book_akhir'      => $qty_book_awal,
            'qty_free_akhir'      => $qty_free_awal + $qty_in,
            'harga_stok'    => $costbook,
            'status_transaksi' => 'in',
            'created_by' => $this->auth->user_id(),
            'created_on' => date('Y-m-d H:i:s'),
        ]);

        // Insert detail coil ke warehouse_stock_coil (child dari warehouse_stock)
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

    /**
     * Step 1 — Staging: insert ke gl_interface (header) + gl_interface_detail.
     *
     * Jurnal Incoming Coil:
     *   DEBET  1105-01-01  Persediaan Bahan Baku     → per material (total_nilai per coil)
     *   KREDIT 1110-01-02  Down Payment ($)           → uang_muka_idr (dari PO)
     *   KREDIT 2101-01-06  Unbill ($)                 → sisa (total_persediaan - uang_muka_idr - biaya_masuk - forwarding - selisih_kurs)
     *   KREDIT 1108-01-09  Prepaid BM                 → total biaya_masuk semua coil
     *   KREDIT 2104-01-13  Hutang Forwarder           → total forwarding semua coil
     *   DEBET/KREDIT 7201-01-07  Selisih Kurs         → uang_muka_idr - (kurs_pib * uang_muka_usd)
     *                                                    positif = KREDIT (rugi kurs), negatif = DEBET (untung kurs)
     *
     * @param string $kode_trans
     * @param string $no_po
     * @param float  $total_rp         Total nilai persediaan semua material
     * @param string $id_supplier
     * @param array  $materials        Per coil: id_material, nm_material, qty, harga,
     *                                 total_persediaan, biaya_masuk, forwarding,
     *                                 price_coil_usd, price_coil_idr, no_coil
     * @param float  $uang_muka_idr    Down Payment dalam IDR (dari PO)
     * @param float  $uang_muka_usd    Down Payment dalam USD (dari PO)
     * @param string $no_ros           Nomor ROS untuk ambil kurs_pib
     */
    private function _generate_jurnal_and_debt($kode_trans, $no_po, $total_rp, $id_supplier, $materials = [], $uang_muka_idr = 0, $uang_muka_usd = 0, $no_ros = '', $id_gudang_ke = null)
    {
        $tgl_inv       = date('Y-m-d');
        $supplier_name = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row()->nama;
        $Nomor_JV      = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_inv);

        // Ambil currency dari tr_purchase_order
        $po_data  = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();
        $currency = $po_data ? strtoupper(trim($po_data->matauang)) : 'IDR';

        // COA DP: IDR = 1104-01-01, USD/lainnya = 1104-01-02
        $coa_dp          = ($currency === 'IDR') ? '1104-01-01' : '1104-01-02';
        $coa_unbill      = '2101-01-06';
        $coa_bm          = '1108-01-09';
        $coa_forwarder   = '2104-01-13';
        $coa_kurs        = '7201-01-07';

        // Mapping COA persediaan berdasarkan kd_gudang
        $coa_persediaan_map = [
            'PUS' => '1105-01-01', // Gudang Pusat → Persediaan Bahan Baku
            'PEN' => '1105-01-02', // Gudang Penjualan → Persediaan Barang Dagangan
        ];
        $coa_persediaan_default = '1105-01-01'; // fallback jika gudang tidak ada di map

        $keterangan      = "Incoming Coil PO: " . $no_po;
        $user_id         = $this->auth->user_id();
        $created_on      = date('Y-m-d H:i:s');

        // Ambil kurs_pib dari tr_ros
        $kurs_pib = 0;
        if (!empty($no_ros)) {
            $ros_header = $this->db->get_where('tr_ros', ['id' => $no_ros])->row();
            $kurs_pib   = !empty($ros_header) ? (float) $ros_header->kurs_pib : 0;
        }

        // Hitung komponen jurnal
        $total_biaya_masuk = array_sum(array_column($materials, 'biaya_masuk'));
        $total_forwarding  = array_sum(array_column($materials, 'forwarding'));

        // Selisih kurs = uang_muka_idr - (kurs_pib * uang_muka_usd)
        // Positif → rugi kurs → KREDIT 7201-01-07
        // Negatif → untung kurs → DEBET 7201-01-07
        $selisih_kurs     = $uang_muka_idr - ($kurs_pib * $uang_muka_usd);
        $selisih_kurs_abs = abs($selisih_kurs);

        // Unbill = total persediaan - DP - BM - Forwarder - selisih kurs (signed)
        $total_unbill = $total_rp - $uang_muka_idr - $total_biaya_masuk - $total_forwarding - $selisih_kurs;

        // --- STEP 1a: Insert header ke gl_interface ---
        $this->db->insert('gl_interface', [
            'nomor'           => $Nomor_JV,
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
        $id_gl_interface = $this->db->insert_id();

        // --- STEP 1b: Insert detail ke gl_interface_detail ---

        // DEBET persediaan: 1 baris per material/coil, COA sesuai gudang tujuan
        foreach ($materials as $mat) {
            $kd_gudang_mat  = $mat['kd_gudang_ke'] ?? '';
            $coa_persediaan = $coa_persediaan_map[$kd_gudang_mat] ?? $coa_persediaan_default;
            $ket_mat = "Incoming Coil PO: {$no_po} | {$mat['nm_material']} (Coil: {$mat['no_coil']})";
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl_interface,
                'no_batch'        => $Nomor_JV,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_persediaan,
                'id_material'     => $mat['id_material'],
                'nm_material'     => $mat['nm_material'],
                'id_gudang'       => $mat['id_gudang_ke'] ?? null,
                'no_coil'         => $mat['no_coil'],
                'keterangan'      => $ket_mat,
                'no_reff'         => $no_po,
                'no_request'      => $kode_trans,
                'debet'           => $mat['total_persediaan'],
                'kredit'          => 0,
                'created_at'      => $created_on,
            ]);
        }

        // KREDIT 1110-01-02 Down Payment ($)
        if ($uang_muka_idr > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl_interface,
                'no_batch'        => $Nomor_JV,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_dp,
                'id_material'     => null,
                'nm_material'     => null,
                'id_gudang'       => $id_gudang_ke,
                'no_coil'         => null,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_po,
                'no_request'      => $kode_trans,
                'debet'           => 0,
                'kredit'          => $uang_muka_idr,
                'created_at'      => $created_on,
            ]);
        }

        // KREDIT 2101-01-06 Unbill ($)
        if ($total_unbill != 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl_interface,
                'no_batch'        => $Nomor_JV,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_unbill,
                'id_material'     => null,
                'nm_material'     => null,
                'id_gudang'       => $id_gudang_ke,
                'no_coil'         => null,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_po,
                'no_request'      => $kode_trans,
                'debet'           => ($total_unbill < 0) ? abs($total_unbill) : 0,
                'kredit'          => ($total_unbill > 0) ? $total_unbill : 0,
                'created_at'      => $created_on,
            ]);
        }

        // KREDIT 1108-01-09 Prepaid BM
        if ($total_biaya_masuk > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl_interface,
                'no_batch'        => $Nomor_JV,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_bm,
                'id_material'     => null,
                'nm_material'     => null,
                'id_gudang'       => $id_gudang_ke,
                'no_coil'         => null,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_po,
                'no_request'      => $kode_trans,
                'debet'           => 0,
                'kredit'          => $total_biaya_masuk,
                'created_at'      => $created_on,
            ]);
        }

        // KREDIT 2104-01-13 Hutang Forwarder
        if ($total_forwarding > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl_interface,
                'no_batch'        => $Nomor_JV,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_forwarder,
                'id_material'     => null,
                'nm_material'     => null,
                'id_gudang'       => $id_gudang_ke,
                'no_coil'         => null,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_po,
                'no_request'      => $kode_trans,
                'debet'           => 0,
                'kredit'          => $total_forwarding,
                'created_at'      => $created_on,
            ]);
        }

        // DEBET/KREDIT 7201-01-07 Selisih Kurs
        if ($selisih_kurs_abs > 0) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl_interface,
                'no_batch'        => $Nomor_JV,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_inv,
                'no_perkiraan'    => $coa_kurs,
                'id_material'     => null,
                'nm_material'     => null,
                'id_gudang'       => $id_gudang_ke,
                'no_coil'         => null,
                'keterangan'      => $keterangan . " | Selisih Kurs (Kurs PIB: " . number_format($kurs_pib, 2) . ")",
                'no_reff'         => $no_po,
                'no_request'      => $kode_trans,
                'debet'           => ($selisih_kurs < 0) ? $selisih_kurs_abs : 0,
                'kredit'          => ($selisih_kurs > 0) ? $selisih_kurs_abs : 0,
                'created_at'      => $created_on,
            ]);
        }

        // --- STEP 2: Posting dari gl_interface ke accounting ---
        try {
            $this->_post_gl_interface($Nomor_JV);
        } catch (Exception $e) {
            // Posting gagal — tandai error di gl_interface agar bisa direpost
            $this->db->update('gl_interface',
                ['status' => 'error', 'error_msg' => $e->getMessage()],
                ['id' => $id_gl_interface]
            );
            throw $e; // lempar ke atas agar process_incoming_coil bisa tangkap
        }
    }

    /**
     * Step 2 — Posting: baca gl_interface (header) + gl_interface_detail,
     * lalu insert ke javh + jurnal + tr_kartu_hutang.
     * Jika berhasil, update status gl_interface menjadi 'Y'.
     */
    private function _post_gl_interface($nomor_jv)
    {
        // Ambil header
        $header = $this->db
            ->get_where('gl_interface', ['nomor' => $nomor_jv, 'status' => 'pending'])
            ->row_array();

        if (empty($header)) {
            return;
        }

        // Decode memo untuk ambil id_supplier & nama_supplier
        $memo          = !empty($header['memo']) ? json_decode($header['memo'], true) : [];
        $id_supplier   = $memo['id_supplier']   ?? null;
        $nama_supplier = $memo['nama_supplier'] ?? null;

        // Ambil detail via id_gl_interface
        $details = $this->db
            ->get_where('gl_interface_detail', ['id_gl_interface' => $header['id']])
            ->result_array();

        if (empty($details)) {
            return;
        }

        // Hitung selisih debet vs kredit
        $total_debet  = array_sum(array_column($details, 'debet'));
        $total_kredit = array_sum(array_column($details, 'kredit'));
        $selisih      = round($total_debet) - round($total_kredit); // selisih dalam rupiah bulat

        // Selisih berapapun → tambahkan ke Unbill (2101-01-06) agar balance
        if ($selisih != 0) {
            foreach ($details as &$line) {
                if ($line['no_perkiraan'] === '2101-01-06') {
                    // selisih positif (debet > kredit) → tambah kredit unbill
                    // selisih negatif (kredit > debet) → kurangi kredit unbill
                    $line['kredit'] = round($line['kredit'] + $selisih);
                    $this->db->update('gl_interface_detail',
                        ['kredit' => $line['kredit']],
                        ['id'     => $line['id']]
                    );
                    break;
                }
            }
            unset($line);
        }

        // Insert header jurnal (javh)
        $this->db->insert(DBACC . '.javh', [
            'nomor'      => $nomor_jv,
            'tgl'        => $header['tgl'],
            'jml'        => $header['jml'],
            'kdcab'      => $header['kdcab'],
            'jenis'      => $header['jenis'],
            'keterangan' => $header['keterangan'],
            'bulan'      => $header['bulan'],
            'tahun'      => $header['tahun'],
            'user_id'    => $header['user_id'],
        ]);

        // Insert detail jurnal + kartu hutang per baris
        foreach ($details as $line) {
            $this->db->insert(DBACC . '.jurnal', [
                'tipe'         => $line['tipe'],
                'nomor'        => $nomor_jv,
                'tanggal'      => $line['tanggal'],
                'no_perkiraan' => $line['no_perkiraan'],
                'keterangan'   => $line['keterangan'],
                'no_reff'      => $line['no_reff'],
                'debet'        => $line['debet'],
                'kredit'       => $line['kredit'],
                'id_material'  => $line['id_material'],
                'nm_material'  => $line['nm_material'],
                'id_gudang'    => $line['id_gudang'],
                'no_coil'      => $line['no_coil'],
                'created_by'   => $header['user_id'],
                'created_on'   => date('Y-m-d H:i:s'),
            ]);

            // Kartu hutang hanya untuk baris kredit
            if ($line['kredit'] > 0) {
                $this->db->insert('tr_kartu_hutang', [
                    'tipe'          => $line['tipe'],
                    'nomor'         => $nomor_jv,
                    'tanggal'       => $line['tanggal'],
                    'no_perkiraan'  => $line['no_perkiraan'],
                    'keterangan'    => $line['keterangan'],
                    'no_reff'       => $line['no_reff'],
                    'debet'         => 0,
                    'kredit'        => $line['kredit'],
                    'id_supplier'   => $id_supplier,
                    'nama_supplier' => $nama_supplier,
                    'no_request'    => $line['no_request'],
                ]);
            }
        }

        // Update counter nomor jurnal
        $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'");

        // Tandai gl_interface sudah diposting
        $this->db->update('gl_interface',
            ['status' => 'posted', 'posted_at' => date('Y-m-d H:i:s')],
            ['id' => $header['id']]
        );
    }

    /**
     * Endpoint untuk posting ulang dari gl_interface.
     * Dipanggil jika ada jurnal yang tidak masuk ke accounting.
     * Hanya memproses header dengan status='N'.
     *
     * POST param: nomor_jv
     */
    public function repost_gl_interface()
    {
        $nomor_jv = $this->input->post('nomor_jv');

        if (empty($nomor_jv)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'nomor_jv tidak boleh kosong']));
        }

        // Cek apakah sudah pernah diposting ke javh (hindari duplikat)
        $already = $this->db->get_where(DBACC . '.javh', ['nomor' => $nomor_jv])->num_rows();
        if ($already > 0) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'Nomor JV ' . $nomor_jv . ' sudah diposting ke accounting']));
        }

        // Cek gl_interface masih pending
        $gl = $this->db->get_where('gl_interface', ['nomor' => $nomor_jv, 'status' => 'pending'])->row();
        if (empty($gl)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'Data gl_interface tidak ditemukan atau sudah diposting']));
        }

        $this->db->trans_start();
        $this->_post_gl_interface($nomor_jv);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 0, 'pesan' => 'Posting ulang gagal']));
        }

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['status' => 1, 'pesan' => 'Posting ulang berhasil untuk JV ' . $nomor_jv]));
    }

    private function _upload_incoming_files($input_name)
    {
        if (empty($_FILES[$input_name]['name'][0])) {
            return '';
        }

        $config['upload_path']   = './uploads/incoming_material';
        $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|zip|rar';
        $config['max_size']      = 102400;
        $config['encrypt_name']  = TRUE;
        $config['remove_spaces'] = TRUE;

        // Buat folder jika belum ada
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, TRUE);
        }

        $this->load->library('upload', $config);

        $uploaded_paths = [];
        $files = $_FILES[$input_name];

        foreach ($files['name'] as $key => $image) {
            $_FILES['temp_upload']['name']     = $files['name'][$key];
            $_FILES['temp_upload']['type']     = $files['type'][$key];
            $_FILES['temp_upload']['tmp_name'] = $files['tmp_name'][$key];
            $_FILES['temp_upload']['error']    = $files['error'][$key];
            $_FILES['temp_upload']['size']     = $files['size'][$key];

            $this->upload->initialize($config);

            if ($this->upload->do_upload('temp_upload')) {
                $data = $this->upload->data();
                $uploaded_paths[] = 'uploads/incoming_material/' . $data['file_name'];
            }
        }

        return (!empty($uploaded_paths)) ? implode('|', $uploaded_paths) : '';
    }
}
