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

    public function add()
    {
        $this->auth->restrict($this->viewPermission);

        $list_supplier = $this->db->query("
        SELECT DISTINCT(b.kode_supplier), b.nama 
        FROM tr_purchase_order a 
        LEFT JOIN new_supplier b ON a.id_suplier = b.kode_supplier 
        WHERE a.status = '2' AND b.kode_supplier IS NOT NULL 
        ORDER BY b.nama ASC
        ")->result();

        $pusat = $this->db->query("SELECT * FROM warehouse WHERE `desc`='pusat' ORDER BY urut ASC")->result_array();

        $data = array(
            'list_supplier' => $list_supplier,
            'pusat'         => $pusat,
        );

        $this->template->set($data);
        $this->template->title('Incoming Based on ROS');
        $this->template->render('form');
    }

    public function get_po_by_supplier()
    {
        $id_supplier = $this->input->post('id_supplier');
        $data = $this->db->query("
        SELECT no_po, no_surat 
        FROM tr_purchase_order 
        WHERE id_suplier = '$id_supplier' AND status = '2'
        ORDER BY no_po DESC
    ")->result();
        echo json_encode($data);
    }


    public function get_ros_by_po()
    {
        $no_po = $this->input->post('no_po');

        $query = "SELECT 
                a.id as id_ros_detail, 
                a.no_coil, 
                a.no_ros,
                a.berat_kotor as ros_kotor, 
                a.berat_bersih as ros_bersih,
                a.nm_barang as nm_material,
                a.id_barang as id_material,
                b.qty as qty_po,
                b.qty_in,
                b.id as id_po_detail
              FROM tr_ros_detail a
              LEFT JOIN dt_trans_po b ON a.id_po_detail = b.id
              WHERE a.no_ros IN (
                  SELECT no_ros 
                  FROM tr_ros 
                  WHERE no_po = '$no_po' 
                  AND sts = '0'
              )
              ORDER BY a.id_barang, a.no_coil ASC";

        $data = $this->db->query($query)->result();
        echo json_encode($data);
    }

    public function process_incoming_coil()
    {
        $post = $this->input->post();
        $dateTime = date('Y-m-d H:i:s');

        $this->db->trans_begin();

        $kode_incoming = $this->Incoming_model->generate_id_incoming();
        $link = $this->_upload_incoming_files('file_incoming_material');

        $total_harga_check = 0;
        $total_berat_check = 0;
        $list_ros = []; // Untuk menampung ID ROS yang terlibat

        foreach ($post['detail'] as $val) {
            // Lewati jika tidak ada input berat (mencegah data sampah)
            if (empty($val['aktual_kotor']) || $val['aktual_kotor'] == 0) continue;

            $aktual_kotor = str_replace(',', '', $val['aktual_kotor']);
            $id_material  = $val['id_material'];
            $id_po_detail = $val['id_po_detail'];

            $get_mat = $this->db->get_where('new_inventory_4', ['code_lv4' => $id_material])->row();
            $get_po  = $this->db->get_where('dt_trans_po', ['id' => $id_po_detail])->row();

            // 1. Insert Detail Utama
            $this->db->insert('tr_incoming_check_detail', [
                'kode_trans'   => $kode_incoming,
                'id_po_detail' => $id_po_detail,
                'no_ipp'       => $post['no_po'],
                'id_material'  => $id_material,
                'nm_material'  => $get_mat->nama,
                'qty_order'    => $aktual_kotor,
                'harga'        => $get_po->hargasatuan,
                'keterangan'   => "Coil Nomor: " . $val['no_coil']
            ]);

            $id_detail_inc = $this->db->insert_id();

            // 2. Insert Detail QC
            $this->db->insert('tr_checked_incoming_detail', [
                'kode_trans'  => $kode_incoming,
                'id_detail'   => $id_detail_inc,
                'id_material' => $id_material,
                'qty_oke'     => ($val['status_qc'] == 'OK') ? $aktual_kotor : 0,
                'qty_ng'      => ($val['status_qc'] == 'REJECT') ? $aktual_kotor : 0,
                'sts'         => '1',
                'harga'       => $get_po->hargasatuan,
                'total_harga' => $aktual_kotor * $get_po->hargasatuan
            ]);

            // 3. Proses jika OK
            if ($val['status_qc'] == 'OK') {
                $this->_update_stock_and_history($id_material, $get_mat->nama, $aktual_kotor, $get_po->hargasatuan, $kode_incoming, $post['no_po'], $val['no_coil']);

                // Update qty_in di detail PO
                $this->db->set('qty_in', 'qty_in + ' . (float)$aktual_kotor, FALSE);
                $this->db->where('id', $id_po_detail);
                $this->db->update('dt_trans_po');

                $total_harga_check += ($aktual_kotor * $get_po->hargasatuan);
                $total_berat_check += $aktual_kotor;
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
            'category'     => 'incoming material',
            'jumlah_mat'   => $total_berat_check,
            'id_gudang_dari' => 1,
            'kd_gudang_dari' => 'PUS',
            'id_gudang_ke'   => 1,
            'kd_gudang_ke'   => 'PUS',
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

        // 6. Generate Jurnal & Hutang (Hanya 1x panggil)
        if ($total_harga_check > 0) {
            $this->_generate_jurnal_and_debt($kode_incoming, $post['no_po'], $total_harga_check, $post['id_supplier']);
        }

        // Finalisasi Transaksi
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal simpan data!']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => 1, 'pesan' => 'Sukses! Stok, Hutang, dan Jurnal telah diproses.']);
        }
    }

    private function _update_stock_and_history($id_material, $nm_material, $qty_in, $harga_po, $kode_trans, $no_po, $no_coil)
    {
        $get_stock = $this->db->get_where('warehouse_stock', [
            'id_material' => $id_material,
            'id_gudang' => 1
        ])->row();

        $qty_awal = (!empty($get_stock)) ? $get_stock->qty_stock : 0;
        $harga_lama = (!empty($get_stock)) ? $get_stock->harga_beli : 0;
        $qty_book_awal = (!empty($get_stock)) ? $get_stock->qty_booking : 0;
        $qty_free_awal = (!empty($get_stock)) ? $get_stock->qty_free : 0;

        $nilai_lama = $qty_awal * $harga_lama;
        $nilai_baru = $qty_in * $harga_po;
        $qty_akhir  = $qty_awal + $qty_in;
        $costbook   = ($qty_akhir > 0) ? ($nilai_lama + $nilai_baru) / $qty_akhir : $harga_po;

        if (empty($get_stock)) {
            $this->db->insert('warehouse_stock', [
                'id_material' => $id_material,
                'code_lv4'    => $id_material,
                'nm_product'  => $nm_material,
                'id_gudang'   => 1,
                'kd_gudang'   => 'PUS',
                'incoming'    => $qty_in,
                'qty_book'    => $qty_book_awal,
                'qty_stock'   => $qty_in,
                'qty_free'    => $qty_in,
                'harga_beli'  => $costbook,
                'total_nilai' => $qty_in * $costbook,
                'update_by'   => $this->auth->user_id(),
                'update_date' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->db->update('warehouse_stock', [
                'incoming'    => $qty_in,
                'qty_stock'   => $qty_akhir,
                'qty_book'    => $qty_book_awal,
                'qty_free'    => $qty_free_awal + $qty_in,
                'harga_beli'  => $costbook,
                'total_nilai' => $qty_akhir * $costbook,
                'update_by'   => $this->auth->user_id(),
                'update_date' => date('Y-m-d H:i:s')
            ], ['id' => $get_stock->id]);
        }

        $this->db->insert('warehouse_history', [
            'id_material'     => $id_material,
            'nm_material'     => $nm_material,
            'id_gudang'       => 1,
            'kd_gudang'       => 'PUS',
            'id_gudang_dari'  => 1,
            'kd_gudang_dari'  => 'PUS',
            'id_gudang_ke'    => 1,
            'kd_gudang_ke'    => 'PUS',
            'qty_stock_awal'  => $qty_awal,
            'qty_stock_akhir' => $qty_akhir,
            'no_ipp'          => $kode_trans,
            'jumlah_mat'      => $qty_in,
            'ket'             => 'QC Incoming Coil Check (Coil NO: ' . $no_coil . ' , PO: ' . $no_po . ')',
            'update_by'       => $this->auth->user_id(),
            'update_date'     => date('Y-m-d H:i:s')
        ]);

        $this->db->insert('kartu_stok', [
            'no_transaksi'  => $kode_trans,
            'transaksi'     => "Incoming Material",
            'tgl_transaksi' => date('Y-m-d H:i:s'),
            'code_lv4'      => $id_material,
            'nm_product'    => $nm_material,
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
    }

    private function _generate_jurnal_and_debt($kode_trans, $no_po, $total_rp, $id_supplier)
    {
        $tgl_inv = date('Y-m-d');
        $supplier_name = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row()->nama;

        $Nomor_JV = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_inv);


        $this->db->insert(DBACC . '.javh', [
            'nomor'      => $Nomor_JV,
            'tgl'        => $tgl_inv,
            'jml'        => $total_rp,
            'kdcab'      => '101',
            'jenis'      => 'JV',
            'keterangan' => "Incoming Coil PO: " . $no_po,
            'bulan'      => date('m'),
            'tahun'      => date('Y'),
            'user_id'    => $this->auth->user_id()
        ]);

        $coa_persediaan = '1103-01-01';
        $coa_hutang     = '2101-01-01';

        $jurnal_detail = [
            ['coa' => $coa_persediaan, 'debet' => $total_rp, 'kredit' => 0],
            ['coa' => $coa_hutang, 'debet' => 0, 'kredit' => $total_rp]
        ];

        foreach ($jurnal_detail as $jd) {
            $this->db->insert(DBACC . '.jurnal', [
                'tipe'         => 'JV',
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl_inv,
                'no_perkiraan' => $jd['coa'],
                'keterangan'   => "Incoming Coil PO: " . $no_po,
                'no_reff'      => $no_po,
                'debet'        => $jd['debet'],
                'kredit'       => $jd['kredit'],
                'created_by'   => $this->auth->user_id(),
                'created_on'   => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->insert('tr_kartu_hutang', [
            'tipe'          => 'JV',
            'nomor'         => $Nomor_JV,
            'tanggal'       => $tgl_inv,
            'no_perkiraan'  => $coa_hutang,
            'keterangan'    => "Incoming Coil PO: " . $no_po,
            'no_reff'       => $no_po,
            'debet'         => 0,
            'kredit'        => $total_rp,
            'id_supplier'   => $id_supplier,
            'nama_supplier' => $supplier_name,
            'no_request'    => $kode_trans
        ]);

        $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab = '101'");
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
