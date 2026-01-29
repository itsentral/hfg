<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Incoming_material extends Admin_Controller
{

    protected $viewPermission     = 'Incoming.View';
    protected $addPermission      = 'Incoming.Add';
    protected $managePermission = 'Incoming.Manage';
    protected $deletePermission = 'Incoming.Delete';


    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Incoming_material/Incoming_material_model',
            'Incoming_material/Master_model',
        ));

        $this->template->title('Incoming Material');
        $this->template->page_icon('fa fa-cubes');

        date_default_timezone_set('Asia/Bangkok');
    }


    //MATERIAL ADJUSTMENT
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
        $this->template->page_icon('fa fa-users');
        $data_Group = $this->db->query('SELECT * FROM `groups`')->result_array();
        $pusat      = $this->db->query("SELECT * FROM `warehouse` WHERE `desc`='pusat' ORDER BY `urut` ASC")->result_array();
        $no_po        = $this->db->query("
										SELECT a.no_po, a.no_surat, a.status, 'PO' as ket_,b.nama AS nm_supplier FROM tr_purchase_order a LEFT JOIN new_supplier b ON b.kode_supplier = a.id_suplier WHERE a.status = '2' AND a.tipe IS NULL AND (SELECT IF(SUM(aa.qty_oke + aa.qty_ng) IS NULL, 0, SUM(aa.qty_oke + aa.qty_ng)) FROM tr_checked_incoming_detail aa WHERE aa.no_ipp = a.no_po) < (SELECT SUM(ab.qty) FROM dt_trans_po ab WHERE ab.no_po = a.no_po) AND (SELECT COUNT(ac.id) FROM dt_trans_po ac JOIN new_inventory_4 ca ON ca.code_lv4 = ac.idmaterial WHERE ac.no_po = a.no_po AND ac.idmaterial <> '') > 0 ORDER BY a.no_po ASC
										")->result_array();
        $list_po    = $this->db->group_by('no_ipp')->get_where('warehouse_adjustment', array('category' => 'incoming material'))->result_array();
        $data_gudang = $this->db->group_by('id_gudang_ke')->get_where('warehouse_adjustment', array('category' => 'incoming material'))->result_array();

        $list_supplier = $this->db->select('kode_supplier, nama')->get_where('new_supplier', ['deleted_by' => null])->result();

        $data = array(
            'action'            => 'index',
            'row_group'         => $data_Group,
            'list_po'           => $list_po,
            'data_gudang'       => $data_gudang,
            'pusat'             => $pusat,
            'no_po'             => $no_po,
            'list_supplier'     => $list_supplier
        );
        // history('View Incoming Material');
        $this->template->set($data);
        $this->template->page_icon('fa fa-sign-in');
        $this->template->title('Incoming Material');
        $this->template->render('index');
    }

    public function server_side_incoming_material()
    {
        $this->Incoming_material_model->get_data_json_incoming_material();
    }

    public function modal_detail_adjustment()
    {
        $this->Incoming_material_model->modal_detail_adjustment();
    }

    public function modal_incoming_material()
    {
        $this->Incoming_material_model->modal_incoming_material();
    }

    public function process_in_material()
    {
        $data            = $this->input->post();

        $data_session    = $this->session->userdata;
        $dateTime        = date('Y-m-d H:i:s');
        $no_po           = $data['no_po'];
        $incoming_date   = $data['incoming_date'];

        $addInMat        = $data['addInMat'];

        $Ym              = date('ym');

        $table = 'dt_trans_po';


        $ArrUpdate         = array();
        $ArrInList         = array();
        $ArrDeatil         = array();
        $ArrDeatilAdj     = array();
        $ArrHist         = array();
        $SumMat = 0;
        $SumRisk = 0;


        $ArrInsertH = array(
            'no_ipp'             => $no_po,
            'category'           => 'incoming material',
            'jumlah_mat'         => $SumMat + $SumRisk,
            'kd_gudang_dari'     => 'PURCHASE',
            // 'note' => $note,
            'created_by'         => $this->auth->user_id(),
            'created_date'       => $dateTime
        );

        $ArrHeader2 = array(
            'status' => 'COMPLETE',
        );

        $ArrHeader2x = array(
            'status' => 'COMPLETE',
            'total_material_in' => $SumMat + $SumRisk
        );

        $ArrHeader3 = array(
            'status' => 'IN PARSIAL',
        );

        $this->db->trans_begin();

        $generate_id = $this->db->query("SELECT MAX(kode_trans) AS max_id FROM tr_incoming_check WHERE kode_trans LIKE '%TRS1-" . date('m-y') . "%'")->row();
        $kodeBarang = $generate_id->max_id;
        $urutan = (int) substr($kodeBarang, 11, 5);
        $urutan++;
        $tahun = date('m-y');
        $huruf = "TRS1-";
        $kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

        $jumlah_mat = 0;
        $valid = 1;
        foreach ($addInMat as $val => $valx) {
            $qtyIN         = str_replace(',', '', $valx['qty_in']);
            $qty_sisa = $valx['qty_sisa'];

            if ($qtyIN > $qty_sisa) {
                $valid = 2;
            } else {
                $get_trans_po = $this->db->get_where('dt_trans_po', ['id' => $valx['id']])->row();
                if ($qtyIN > 0) {
                    $this->db->insert('tr_incoming_check_detail', [
                        'kode_trans'        => $kodecollect,
                        'no_ipp'            => $no_po,
                        'id_po_detail'      => $valx['id'],
                        'id_material_req'   => $get_trans_po->idmaterial,
                        'id_material'       => $get_trans_po->idmaterial,
                        'nm_material'       => $get_trans_po->namamaterial,
                        'harga'             => $get_trans_po->hargasatuan,
                        'qty_order'         => $qtyIN,
                        'keterangan'        => $valx['keterangan']
                    ]);
                }

                $update_qty_in = $this->db->update('dt_trans_po', [
                    'qty_in' => ($get_trans_po->qty_in + $qtyIN),
                    'keterangan' => $valx['keterangan']
                ], [
                    'id' => $valx['id']
                ]);

                $jumlah_mat += $qtyIN;
            }
        }

        $config['upload_path'] = './uploads/incoming_material'; //path folder
        $config['allowed_types'] = '*'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = TRUE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $upload_incoming = '';

        $files = $_FILES['file_incoming_material'];
        $file_count = count($files['name']);
        for ($i = 0; $i < $file_count; $i++) {
            $_FILES['file_incoming_material']['name'] = $files['name'][$i];
            $_FILES['file_incoming_material']['type'] = $files['type'][$i];
            $_FILES['file_incoming_material']['tmp_name'] = $files['tmp_name'][$i];
            $_FILES['file_incoming_material']['error'] = $files['error'][$i];
            $_FILES['file_incoming_material']['size'] = $files['size'][$i];

            if (!$this->upload->do_upload('file_incoming_material')) {
                // If upload fails, display error
                $error = array('error' => $this->upload->display_errors());
                // print_r($error);
            } else {
                $data_upload_incoming = $this->upload->data();
                $upload_incoming = $upload_incoming . '|' . 'uploads/incoming_material/' . $data_upload_incoming['file_name'];
            }
        }

        $row = $this->db->select('subtotal')
            ->from('tr_purchase_order')
            ->where('no_po', $no_po)
            ->limit(1)
            ->get()
            ->row();

        $subtotal = $row ? $row->subtotal : null;

        $this->db->insert('tr_incoming_check', [
            'kode_trans'                => $kodecollect,
            'tanggal'                   => $incoming_date,
            'no_ipp'                    => $no_po,
            'category'                  => 'incoming material',
            'jumlah_mat'                => $jumlah_mat,
            'id_gudang_dari'            => 1,
            'kd_gudang_dari'            => 'PUS',
            'id_gudang_ke'              => 1,
            'kd_gudang_ke'              => 'PUS',
            'file_incoming_material'    => $upload_incoming,
            'total_harga_material'       => $subtotal,
            'created_by'                => $this->auth->user_id(),
            'created_date'              => date('Y-m-d H:i:s')
        ]);

        $checkSumQty = $this->db->query("SELECT SUM(qty) as total_qty, SUM(qty_in) AS qty_terkirim FROM dt_trans_po WHERE no_po = '" . $no_po . "'")->row();

        if ($this->db->trans_status() === FALSE || $valid > 1) {
            $this->db->trans_rollback();

            $msg = 'Save process failed. Please try again later ...';
            if ($valid == '2') {
                $msg = 'Maaf, qty pengiriman melebihi qty yang belum dikirim !';
            }
            $Arr_Data    = array(
                'pesan'        => $msg,
                'status'    => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data    = array(
                'pesan'        => 'Save process success. Thanks ...',
                'status'    => 1
            );
            // history($histHlp);
        }
        // echo json_encode($Arr_Data);
    }

    public function incoming_list_po()
    {
        $kode_supplier = $this->input->post('kode_supplier');

        $no_po = $this->db->query("
            SELECT 
                a.no_po, a.no_surat, a.status, 'PO' as ket_,b.nama AS nm_supplier 
            FROM 
                tr_purchase_order a 
                LEFT JOIN new_supplier b ON b.kode_supplier = a.id_suplier 
            WHERE a.status = '2'  AND a.id_suplier = '" . $kode_supplier . "' AND (SELECT IF(SUM(aa.qty_oke + aa.qty_ng) IS NULL, 0, SUM(aa.qty_oke + aa.qty_ng)) 
            FROM 
                tr_checked_incoming_detail aa WHERE aa.no_ipp = a.no_po) < (SELECT SUM(ab.qty) FROM dt_trans_po ab WHERE ab.no_po = a.no_po) AND (SELECT COUNT(ac.id) 
            FROM 
                dt_trans_po ac 
                JOIN new_inventory_4 ca ON ca.code_lv4 = ac.idmaterial 
            WHERE 
                ac.no_po = a.no_po AND ac.idmaterial <> '') > 0 AND a.close_po IS NULL ORDER BY a.no_po ASC
            ")
            ->result();

        $hasil = '';
        foreach ($no_po as $item) {

            $no_pr = [];
            $get_no_pr = $this->db->query("
                SELECT
                    d.no_pr as no_pr
                FROM
                    dt_trans_po a
                    JOIN tr_purchase_order b ON b.no_po = a.no_po
                    JOIN material_planning_base_on_produksi_detail c ON c.id = a.idpr
                    JOIN material_planning_base_on_produksi d ON d.so_number = c.so_number
                WHERE
                    b.no_surat = '" . $item->no_surat . "' AND
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
                    b.no_surat = '" . $item->no_surat . "' AND
                    a.tipe = 'pr depart'
                GROUP BY c.no_pr

            ")->result();
            foreach ($get_no_pr as $item_no_pr) {
                $no_pr[] = $item_no_pr->no_pr;
            }

            if (!empty($no_pr)) {
                $no_pr = implode(', ', $no_pr);
            } else {
                $no_pr = '';
            }

            $hasil .= '<tr>';
            $hasil .= '<td class="text-center">' . $item->no_surat . '</td>';
            $hasil .= '<td class="text-center">' . $no_pr . '</td>';
            $hasil .= '<td class="text-center"><input type="checkbox" name="check_po[]" class="check_po" value="' . $item->no_po . '"></td>';
            $hasil .= '</tr>';
        }

        echo $hasil;
    }
}
