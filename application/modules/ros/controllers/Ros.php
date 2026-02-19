<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ros extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'ROS.View';
    protected $addPermission    = 'ROS.Add';
    protected $managePermission = 'ROS.Manage';
    protected $deletePermission = 'ROS.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Ros/ros_model', 'all/All_model'));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session  = $this->session->userdata('app_session');

        $this->template->title('ROS');
        $this->template->render('index');
    }

    public function add()
    {
        $this->auth->restrict($this->addPermission);
        $session  = $this->session->userdata('app_session');

        $list_ros_no_po = $this->ros_model->list_po_no_ros();
        $list_custom_pib = $this->ros_model->list_custom_pib($this->auth->user_id());
        $list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result_array();

        $this->template->set('list_ros_no_po', $list_ros_no_po);
        $this->template->set('list_custom_pib', $list_custom_pib);
        $this->template->set('list_supplier', $list_supplier);
        $this->template->title('Add ROS');
        $this->template->render('add');
    }

    public function edit($no_ros)
    {
        $this->auth->restrict($this->managePermission);
        $session  = $this->session->userdata('app_session');

        $list_ros_no_po = $this->ros_model->list_po_no_ros();
        $list_custom_pib = $this->ros_model->list_custom_pib($no_ros);
        $get_ros = $this->db->get_where('tr_ros', ['id' => $no_ros])->row_array();
        // $get_ros_detail = $this->db->get_where('tr_ros_detail', ['no_ros' => $no_ros])->result_array();
        $list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result_array();

        $this->db->select('a.*, IF(d.code IS NULL, IF(e.code IS NULL, IF(h.code IS NULL, "Pcs", h.code), e.code), d.code) as unit_satuan');
        $this->db->from('tr_ros_detail a');
        $this->db->join('new_inventory_4 b', 'b.code_lv4 = a.id_barang', 'left');
        $this->db->join('accessories c', 'c.id = a.id_barang', 'left');
        $this->db->join('dt_trans_po f', 'f.id = a.id_po_detail AND f.tipe IS NOT NULL', 'left');
        $this->db->join('rutin_non_planning_detail g', 'g.id = f.idpr', 'left');
        $this->db->join('ms_satuan d', 'b.id_unit = d.id', 'left');
        $this->db->join('ms_satuan e', 'c.id_unit_gudang = e.id', 'left');
        $this->db->join('ms_satuan h', 'g.satuan = h.id', 'left');
        $this->db->where('a.no_ros', $no_ros);
        $this->db->group_by('a.id');
        $get_ros_detail = $this->db->get()->result_array();

        $this->template->set('list_ros_no_po', $list_ros_no_po);
        $this->template->set('list_custom_pib', $list_custom_pib);
        $this->template->set('header_ros', $get_ros);
        $this->template->set('detail_ros', $get_ros_detail);
        $this->template->set('list_supplier', $list_supplier);
        $this->template->title('Edit ROS');
        $this->template->render('add');
    }

    public function view($no_ros)
    {
        $this->auth->restrict($this->managePermission);
        $session  = $this->session->userdata('app_session');

        $list_ros_no_po = $this->ros_model->list_po_no_ros();
        $list_custom_pib = $this->ros_model->list_custom_pib($no_ros);
        $get_ros = $this->db->get_where('tr_ros', ['id' => $no_ros])->row_array();
        $this->db->select('a.*, IF(d.code IS NULL, IF(e.code IS NULL, IF(h.code IS NULL, "Pcs", h.code), e.code), d.code) as unit_satuan');
        $this->db->from('tr_ros_detail a');
        $this->db->join('new_inventory_4 b', 'b.code_lv4 = a.id_barang', 'left');
        $this->db->join('accessories c', 'c.id = a.id_barang', 'left');
        $this->db->join('dt_trans_po f', 'f.id = a.id_po_detail AND f.tipe IS NOT NULL', 'left');
        $this->db->join('rutin_non_planning_detail g', 'g.id = f.idpr', 'left');
        $this->db->join('ms_satuan d', 'b.id_unit = d.id', 'left');
        $this->db->join('ms_satuan e', 'c.id_unit_gudang = e.id', 'left');
        $this->db->join('ms_satuan h', 'g.satuan = h.id', 'left');
        $this->db->where('a.no_ros', $no_ros);
        $this->db->group_by('a.id');
        $get_ros_detail = $this->db->get()->result_array();
        // print_r($this->db->error($get_ros_detail));
        // exit;

        $this->template->set('list_ros_no_po', $list_ros_no_po);
        $this->template->set('list_custom_pib', $list_custom_pib);
        $this->template->set('header_ros', $get_ros);
        $this->template->set('detail_ros', $get_ros_detail);
        $this->template->title('View ROS');
        $this->template->render('view');
    }

    public function data_side_ros()
    {
        $this->ros_model->data_side_ros();
    }

    public function get_no_po_detail()
    {
        $post = $this->input->post();

        $this->db->select('a.*');
        $this->db->from('tr_purchase_order a');
        $this->db->where_in('a.no_po', explode(',', $post['no_po']));
        $this->db->where('a.status', 2);
        $get_id_po = $this->db->get()->row_array();

        $hasil = '';

        $no = 1;
        $this->db->select('a.*, IF(f.code IS NULL, IF(g.code IS NULL, IF(h.code IS NULL, "Pcs", h.code), g.code), f.code) as unit_satuan');
        $this->db->from('dt_trans_po a');
        $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po');
        $this->db->join('new_inventory_4 c', 'c.code_lv4 = a.idmaterial', 'left');
        $this->db->join('accessories d', 'd.id = a.idmaterial', 'left');
        $this->db->join('rutin_non_planning_detail e', 'e.id = a.idpr', 'left');
        $this->db->join('ms_satuan f', 'f.id = c.id_unit', 'left');
        $this->db->join('ms_satuan g', 'g.id = d.id_unit_gudang', 'left');
        $this->db->join('ms_satuan h', 'h.id = e.satuan', 'left');
        $this->db->where_in('a.no_po', explode(',', $post['no_po']));
        $this->db->group_by('a.id');
        $get_detail_po = $this->db->get()->result_array();
        // print_r($this->db->error($get_detail_po));
        // exit;
        // $get_detail_po = $this->db->get_where('dt_trans_po', ['no_po' => $get_id_po['no_po']])->result_array();
        $ttl_price_detail = 0;
        foreach ($get_detail_po as $item_po) {

            $nilai_pengurang = 0;
            $this->db->select('IF(SUM(a.qty_packing_list) IS NULL, 0, SUM(a.qty_packing_list)) as nilai_pengurang');
            $this->db->from('tr_ros_detail a');
            $this->db->where('a.id_po_detail', $item_po['id']);
            $get_nilai_ros_used = $this->db->get()->row_array();
            if (!empty($get_nilai_ros_used)) {
                $nilai_pengurang += $get_nilai_ros_used['nilai_pengurang'];
            }

            if (($item_po['qty'] - $nilai_pengurang) > 0) {
                // Di dalam loop foreach ($get_detail_po as $item_po)
                $hasil .= '<tr class="row-material" data-id="' . $item_po['id'] . '">';
                $hasil .= '<td class="text-center no-urut">' . $no . '</td>';
                $hasil .= '<td class="text-center">' . $item_po['namamaterial'] . '</td>';
                $hasil .= '<td class="text-center">' . ucfirst($item_po['unit_satuan']) . '</td>';
                $hasil .= '<td class="text-center">' . $get_id_po['matauang'] . '</td>';
                $hasil .= '<td class="text-end">' . number_format($item_po['hargasatuan']) . '</td>';
                $hasil .= '<td class="text-end hargasatuan_rp">' . number_format($item_po['hargasatuan'] * $post['kurs_pib']) . '</td>';
                $hasil .= '<td class="text-center">' . number_format($item_po['qty']) . '</td>';

                // Kolom Input Pertama (Baris pertama untuk material ini)
                $hasil .= '<td><input type="text" name="dt[' . $item_po['id'] . '][berat_kotor][]" class="form-control auto_num text-end"></td>';
                $hasil .= '<td><input type="text" name="dt[' . $item_po['id'] . '][berat_bersih][]" class="form-control auto_num text-end"></td>';
                $hasil .= '<td><input type="text" name="dt[' . $item_po['id'] . '][length][]" class="form-control auto_num text-end"></td>';
                $hasil .= '<td><input type="text" name="dt[' . $item_po['id'] . '][biaya_masuk][]" class="form-control auto_num text-end calculate"></td>';
                $hasil .= '<td><input type="text" name="dt[' . $item_po['id'] . '][forwarding][]" class="form-control auto_num text-end calculate"></td>';
                $hasil .= '<td><input type="text" name="dt[' . $item_po['id'] . '][total_nilai][]" class="form-control auto_num text-end calculate"></td>';
                $hasil .= '<td><input type="text" name="dt[' . $item_po['id'] . '][no_coil][]" class="form-control"></td>';
                // Tombol Tambah Baris untuk material spesifik ini
                $hasil .= '<td class="text-center"><button type="button" class="btn btn-sm btn-primary add-row-child" data-id="' . $item_po['id'] . '"><i class="fa fa-plus"></i></button></td>';
                $hasil .= '</tr>';
                $no++;

                $ttl_price_detail += 0;
            }
        }

        echo json_encode([
            'list_detail_pr' => $hasil,
            'ttl_price_detail' => $ttl_price_detail
        ]);
    }

    public function add_custom_pembiayaan()
    {
        $post = $this->input->post();

        $no_ros = ($post['no_ros'] == 'new') ? $this->auth->user_id() : $post['no_ros'];

        $this->db->trans_begin();

        $this->db->insert('tr_ros_custom_pib', [
            'no_ros' => $no_ros,
            'nm_item_pembiayaan' => $post['biaya_name'],
            'nilai_cost' => $post['cost_biaya'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function hitung_custom_pib()
    {
        $no_ros = $this->input->post('no_ros');
        if ($no_ros == 'new') {
            $no_ros = $this->auth->user_id();
        }

        $nilai = 0;
        $get_custom_pib = $this->db->get_where('tr_ros_custom_pib', ['no_ros' => $no_ros])->result_array();
        foreach ($get_custom_pib as $item) {
            $nilai += $item['nilai_cost'];
        }

        echo json_encode(['ttl_custom_pib' => $nilai]);
    }

    public function refresh_list_pib()
    {
        $no_ros = $this->input->post('no_ros');
        if ($no_ros == 'new') {
            $no_ros = $this->auth->user_id();
        }

        $hasil = '';

        $no = 4;
        $get_custom_pib = $this->db->get_where('tr_ros_custom_pib', ['no_ros' => $no_ros])->result_array();
        foreach ($get_custom_pib as $item) {
            $hasil .= '<tr>';
            $hasil .= '<td class="text-center">' . $no . '</td>';
            $hasil .= '<td class="text-center">' . $item['nm_item_pembiayaan'] . '</td>';
            $hasil .= '<td class="text-center">
            <input type="text" name="cost_pib_' . $item['id'] . '" id="" class="form-control form-control-sm auto_num text-right cost_pib_custom cost_pib_custom_' . $item['id'] . '" data-id="' . $item['id'] . '" value="' . $item['nilai_cost'] . '">
            </td>';
            $hasil .= '<td class="text-center">
                <button type="button" class="btn btn-sm btn-danger del_custom_pib" data-id="' . $item['id'] . '"><i class="fa fa-trash"></i></button>
            </td>';
            $hasil .= '</tr>';

            $no++;
        }

        echo json_encode([
            'hasil' => $hasil
        ]);
    }

    public function del_custom_pib()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $this->db->delete('tr_ros_custom_pib', ['id' => $post['id']]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo $valid;
    }

    public function save_ros()
    {
        $post = $this->input->post();

        $config['upload_path'] = './assets/ros'; //path folder
        $config['allowed_types'] = '*'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = FALSE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $this->db->trans_begin();

        $valid = 1;
        if ($post['no_ros'] == 'New') {
            $no_ros = $this->ros_model->generate_no_ros();

            $upload_pib = '';

            if ($this->upload->do_upload('upload_pib')) {
                $data_upload_po = $this->upload->data();
                $upload_pib = 'assets/ros/' . $data_upload_po['file_name'];
            }

            $get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $post['supplier_name']])->row_array();

            $insert_ros = $this->db->insert('tr_ros', [
                'id' => $no_ros,
                'no_po' => implode(',', $post['no_po']),
                'id_supplier' => $post['supplier_name'],
                'nm_supplier' => $get_supplier['nama'],
                'link_doc' => $upload_pib,
                'kurs_pib' => str_replace(',', '', $post['kurs_pib']),
                'cost_bm' => str_replace(',', '', $post['cost_bm']),
                'cost_ppn' => str_replace(',', '', $post['cost_ppn']),
                'cost_pph' => str_replace(',', '', $post['cost_pph']),
                'freight_cost' => str_replace(',', '', $post['freight_cost_persen']),
                'awb_bl_date' => $post['awb_bl_date'],
                'awb_bl_number' => $post['awb_bl_number'],
                'eta_warehouse' => $post['eta_warehouse'],
                'ata_pod' => $post['ata_pod'],
                'no_pengajuan_pib' => $post['no_pengajuan_pib'],
                'no_biling' => $post['no_billing'],
                'keterangan' => $post['keterangan'],
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ]);

            // Ambil data detail PO untuk referensi (ID Material, Nama, Harga, dll)
            $this->db->select('a.*, b.matauang');
            $this->db->from('dt_trans_po a');
            $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
            $this->db->where_in('b.no_surat', $post['no_po']);
            $get_po_detail = $this->db->get()->result_array();

            foreach ($get_po_detail as $po_detail) {
                $id_po_detail = $po_detail['id'];

                // Cek apakah ada data input 'dt' untuk ID PO Detail ini
                if (isset($post['dt'][$id_po_detail])) {
                    $data_per_material = $post['dt'][$id_po_detail];

                    // Loop berdasarkan jumlah baris coil yang diinput (menggunakan indeks array berat_kotor sebagai acuan)
                    foreach ($data_per_material['berat_kotor'] as $key => $val) {

                        // Bersihkan format angka (hapus koma)
                        $berat_kotor  = str_replace(',', '', $data_per_material['berat_kotor'][$key]);
                        $berat_bersih = str_replace(',', '', $data_per_material['berat_bersih'][$key]);
                        $length       = str_replace(',', '', $data_per_material['length'][$key]);
                        $biaya_masuk  = str_replace(',', '', $data_per_material['biaya_masuk'][$key]);
                        $forwarding   = str_replace(',', '', $data_per_material['forwarding'][$key]);
                        $total_nilai  = str_replace(',', '', $data_per_material['total_nilai'][$key]);
                        $no_coil      = $data_per_material['no_coil'][$key];

                        // Validasi sederhana: Jika berat kotor atau no coil diisi, maka simpan
                        if ($berat_kotor > 0 || !empty($no_coil)) {
                            $this->db->insert('tr_ros_detail', [
                                'no_ros'          => $no_ros,
                                'id_po_detail'    => $id_po_detail,
                                'id_barang'       => $po_detail['idmaterial'],
                                'nm_barang'       => $po_detail['namamaterial'],
                                'currency'        => $po_detail['matauang'],
                                'price_unit'      => $po_detail['hargasatuan'],
                                'qty_po'          => $po_detail['qty'],
                                // Data Packing List (Coil)
                                'berat_kotor'     => $berat_kotor,
                                'berat_bersih'    => $berat_bersih,
                                'length'          => $length,
                                'biaya_masuk'     => $biaya_masuk,
                                'forwarding_cost' => $forwarding,
                                'total_nilai'     => $total_nilai,
                                'no_coil'         => $no_coil,
                                'created_by'      => $this->auth->user_id(),
                                'created_on'      => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }
            }

            $this->db->update('tr_ros_custom_pib', [
                'no_ros' => $no_ros
            ], [
                'no_ros' => $this->auth->user_id()
            ]);
        } else {
            $upload_pib = '';

            if ($this->upload->do_upload('upload_pib')) {
                $data_upload_po = $this->upload->data();
                $upload_pib = 'assets/ros/' . $data_upload_po['file_name'];
            } else {
                $get_ros = $this->db->get_where('tr_ros', ['id' => $post['no_ros']])->row_array();

                $upload_pib = $get_ros['link_doc'];
            }

            $get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $post['supplier_name']])->row_array();

            $this->db->update('tr_ros', [
                'no_po' => $post['no_po'],
                'id_supplier' => $post['supplier_name'],
                'nm_supplier' => $get_supplier['nama'],
                'link_doc' => $upload_pib,
                'kurs_pib' => str_replace(',', '', $post['kurs_pib']),
                'cost_bm' => str_replace(',', '', $post['cost_bm']),
                'cost_ppn' => str_replace(',', '', $post['cost_ppn']),
                'cost_pph' => str_replace(',', '', $post['cost_pph']),
                'freight_cost' => str_replace(',', '', $post['freight_cost_persen']),
                'awb_bl_date' => $post['awb_bl_date'],
                'awb_bl_number' => $post['awb_bl_number'],
                'eta_warehouse' => $post['eta_warehouse'],
                'ata_pod' => $post['ata_pod'],
                'no_pengajuan_pib' => $post['no_pengajuan_pib'],
                'no_biling' => $post['no_billing'],
                'keterangan' => $post['keterangan']
            ], [
                'id' => $post['no_ros']
            ]);

            $this->db->delete('tr_ros_detail', ['no_ros' => $post['no_ros']]);

            // Ambil data detail PO untuk referensi (ID Material, Nama, Harga, dll)
            $this->db->select('a.*, b.matauang');
            $this->db->from('dt_trans_po a');
            $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
            $this->db->where_in('b.no_surat', $post['no_po']);
            $get_po_detail = $this->db->get()->result_array();

            foreach ($get_po_detail as $po_detail) {
                $id_po_detail = $po_detail['id'];

                // Cek apakah ada data input 'dt' untuk ID PO Detail ini
                if (isset($post['dt'][$id_po_detail])) {
                    $data_per_material = $post['dt'][$id_po_detail];

                    // Loop berdasarkan jumlah baris coil yang diinput (menggunakan indeks array berat_kotor sebagai acuan)
                    foreach ($data_per_material['berat_kotor'] as $key => $val) {

                        // Bersihkan format angka (hapus koma)
                        $berat_kotor  = str_replace(',', '', $data_per_material['berat_kotor'][$key]);
                        $berat_bersih = str_replace(',', '', $data_per_material['berat_bersih'][$key]);
                        $length       = str_replace(',', '', $data_per_material['length'][$key]);
                        $biaya_masuk  = str_replace(',', '', $data_per_material['biaya_masuk'][$key]);
                        $forwarding   = str_replace(',', '', $data_per_material['forwarding'][$key]);
                        $total_nilai  = str_replace(',', '', $data_per_material['total_nilai'][$key]);
                        $no_coil      = $data_per_material['no_coil'][$key];

                        // Validasi sederhana: Jika berat kotor atau no coil diisi, maka simpan
                        if ($berat_kotor > 0 || !empty($no_coil)) {
                            $this->db->insert('tr_ros_detail', [
                                'no_ros'          => $post['no_ros'],
                                'id_po_detail'    => $id_po_detail,
                                'id_barang'       => $po_detail['idmaterial'],
                                'nm_barang'       => $po_detail['namamaterial'],
                                'currency'        => $po_detail['matauang'],
                                'price_unit'      => $po_detail['hargasatuan'],
                                'qty_po'          => $po_detail['qty'],
                                // Data Packing List (Coil)
                                'berat_kotor'     => $berat_kotor,
                                'berat_bersih'    => $berat_bersih,
                                'length'          => $length,
                                'biaya_masuk'     => $biaya_masuk,
                                'forwarding_cost' => $forwarding,
                                'total_nilai'     => $total_nilai,
                                'no_coil'         => $no_coil,
                                'created_by'      => $this->auth->user_id(),
                                'created_on'      => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }
            }
        }

        $msg = '';
        if ($this->db->trans_status() === false || $valid == 2) {
            $this->db->trans_rollback();
            $msg = 'Sorry, ROS not been saved !';
            if ($valid == 2) {
                $msg = 'Sorry, your qty packing list is greater than the remaining PO Qty !';
            }
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    public function del_ros()
    {
        $no_ros = $this->input->post('no_ros');

        $this->db->trans_begin();

        $this->db->delete('tr_ros', ['id' => $no_ros]);
        $this->db->delete('tr_ros_detail', ['no_ros' => $no_ros]);
        $this->db->delete('tr_ros_custom_pib', ['no_ros' => $no_ros]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function req_payment_ros()
    {
        $no_ros = $this->input->post('no_ros');

        $get_ros = $this->db->get_where('tr_ros', ['id' => $no_ros])->row_array();
        $get_ros_custom_pib = $this->db->select('SUM(nilai_cost) as ttl_custom_pib')->get_where('tr_ros_custom_pib', ['no_ros' => $no_ros])->row_array();

        $nilai_request = ($get_ros['cost_bm'] + $get_ros['cost_ppn'] + $get_ros['cost_pph'] + $get_ros_custom_pib['ttl_custom_pib']);

        $this->db->trans_begin();

        $get_nm_lengkap = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row_array();

        $no_doc = $this->All_model->GetAutoGenerate('format_expense');

        $insert_exp = $this->db->insert('tr_expense', [
            'no_doc' => $no_ros,
            'tgl_doc' => date('Y-m-d'),
            'departement' => '6',
            'nama' => $get_nm_lengkap['nm_lengkap'],
            'approval' => $get_nm_lengkap['nm_lengkap'],
            'status' => 1,
            'created_by' => $get_nm_lengkap['nm_lengkap'],
            'created_on' => date('Y-m-d H:i:s'),
            'approved_by' => $get_nm_lengkap['nm_lengkap'],
            'approved_on' => date('Y-m-d H:i:s'),
            'jumlah' => $nilai_request,
            'informasi' => 'Pembayaran PIB : ' . $get_ros['no_pengajuan_pib'] . '',
            'accnumber' => $get_ros['no_biling'],
            'exp_pib' => 1
        ]);
        if (!$insert_exp) {
            print_r($this->db->error($insert_exp));
            exit;
        }

        $arrData = array();

        $arrData[] = [
            'tanggal' => date('Y-m-d'),
            'no_doc' => $no_ros,
            'deskripsi' => 'BM',
            'qty' => 1,
            'harga' => $get_ros['cost_bm'],
            'total_harga' => $get_ros['cost_bm'],
            'keterangan' => 'Cost BM : ' . $get_ros['no_po'],
            'status' => 0,
            'expense' => $get_ros['cost_bm'],
            'created_by' => $get_nm_lengkap['nm_lengkap'],
            'created_on' => date('Y-m-d H:i:s')
        ];

        $arrData[] = [
            'tanggal' => date('Y-m-d'),
            'no_doc' => $no_ros,
            'deskripsi' => 'PPN',
            'qty' => 1,
            'harga' => $get_ros['cost_ppn'],
            'total_harga' => $get_ros['cost_ppn'],
            'keterangan' => 'Cost PPN : ' . $get_ros['no_po'],
            'status' => 0,
            'expense' => $get_ros['cost_ppn'],
            'created_by' => $get_nm_lengkap['nm_lengkap'],
            'created_on' => date('Y-m-d H:i:s')
        ];

        $arrData[] = [
            'tanggal' => date('Y-m-d'),
            'no_doc' => $no_ros,
            'deskripsi' => 'PPH',
            'qty' => 1,
            'harga' => $get_ros['cost_pph'],
            'total_harga' => $get_ros['cost_pph'],
            'keterangan' => 'Cost BM : ' . $get_ros['no_po'],
            'status' => 0,
            'expense' => $get_ros['cost_pph'],
            'created_by' => $get_nm_lengkap['nm_lengkap'],
            'created_on' => date('Y-m-d H:i:s')
        ];

        $list_ros_custom_pib = $this->db->get_where('tr_ros_custom_pib', ['no_ros' => $no_ros])->result_array();
        foreach ($list_ros_custom_pib as $item) {
            $arrData[] = [
                'tanggal' => date('Y-m-d'),
                'no_doc' => $no_ros,
                'deskripsi' => $item['nm_item_pembiayaan'],
                'qty' => 1,
                'harga' => $item['nilai_cost'],
                'total_harga' => $item['nilai_cost'],
                'keterangan' => 'Cost ' . $item['nm_item_pembiayaan'] . ' : ' . $get_ros['no_po'],
                'status' => 0,
                'expense' => $item['nilai_cost'],
                'created_by' => $get_nm_lengkap['nm_lengkap'],
                'created_on' => date('Y-m-d H:i:s')
            ];
        }

        $insert_exp_detail = $this->db->insert_batch('tr_expense_detail', $arrData);
        if (!$insert_exp_detail) {
            print_r($this->db->error($insert_exp_detail));
            exit;
        }

        $update_ros_sts = $this->db->update('tr_ros', ['sts' => 1], ['id' => $no_ros]);
        if (!$update_ros_sts) {
            print_r($this->db->error($update_ros_sts));
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function get_po_by_supplier()
    {
        $kode_supplier = $this->input->post('supplier');

        $this->db->select('a.no_surat, a.no_po');
        $this->db->from('tr_purchase_order a');
        $this->db->join('tr_ros b', 'b.no_po = a.no_surat', 'left');
        $this->db->where('a.status', 2);
        $this->db->where('a.id_suplier', $kode_supplier);
        $this->db->where('b.no_po', null);
        $list_po = $this->db->get()->result_array();

        $hasil = '';
        foreach ($list_po as $item) {

            $nilai_sisa = 0;
            $get_po_detail = $this->db->get_where('dt_trans_po', ['no_po' => $item['no_po']])->result_array();
            foreach ($get_po_detail as $item2) {
                $nilai_sisa += $item2['qty'];

                $get_used_ros_detail = $this->db->get_where('tr_ros_detail', ['id_po_detail' => $item2['id']])->result_array();
                if (!empty($get_used_ros_detail)) {
                    foreach ($get_used_ros_detail as $item3) {
                        $nilai_sisa -= $item3['qty_packing_list'];
                    }
                }
            }
            if ($nilai_sisa > 0) {
                $hasil .= '<tr>';
                $hasil .= '<td class="text-center">' . $item['no_surat'] . '</td>';
                $hasil .= '<td class="text-center"><input type="checkbox" name="no_po[]" class="no_po" value="' . $item['no_po'] . '"></td>';
                $hasil .= '</tr>';
            }
        }

        echo $hasil;
    }

    public function get_coil_list()
    {
        $id_ros = $this->input->post('id_ros');

        // Ambil data detail dengan join ke PO detail untuk info master material
        $this->db->select('a.*, b.qty as qty_order, m.nama as uom');
        $this->db->from('tr_ros_detail a');
        $this->db->join('dt_trans_po b', 'b.id = a.id_po_detail', 'left');
        $this->db->join('new_inventory_4 i', 'i.code_lv4 = b.idmaterial', 'left');
        $this->db->join('ms_satuan m', 'm.id = i.id_unit', 'left');
        $this->db->where('a.no_ros', $id_ros);
        $data = $this->db->get()->result_array();

        // Grouping data berdasarkan id_po_detail
        $groups = [];
        foreach ($data as $row) {
            $groups[$row['id_po_detail']][] = $row;
        }

        $html = '<div class="table-responsive">
                <table class="table table-bordered table-hover">
                <thead>
                    <tr class="bg-gray">
                        <th class="text-center" rowspan="2" style="vertical-align:middle">No</th>
                        <th class="text-center" rowspan="2" style="vertical-align:middle">Material</th>
                        <th class="text-center" rowspan="2" style="vertical-align:middle">Qty Order</th>
                        <th class="text-center" rowspan="2" style="vertical-align:middle">Uom</th>
                        <th colspan="3" class="text-center bg-info">Packing List</th>
                        <th class="text-center" rowspan="2" style="vertical-align:middle">
                             Check List<br>
                             <input type="checkbox" id="check_all_modal">
                        </th>
                    </tr>
                    <tr class="bg-navy">
                        <th class="text-center">No. Coil</th>
                        <th class="text-center">Berat Kotor</th>
                        <th class="text-center">Berat Bersih</th>
                    </tr>
                </thead>
                <tbody>';

        if (!empty($groups)) {
            $no = 1;
            foreach ($groups as $id_po => $coils) {
                $rowspan = count($coils);
                foreach ($coils as $index => $coil) {
                    $html .= '<tr>';

                    // Hanya munculkan kolom material di baris pertama group (Rowspan)
                    if ($index === 0) {
                        $html .= '<td class="text-center" rowspan="' . $rowspan . '" style="vertical-align:middle">' . $no . '</td>';
                        $html .= '<td rowspan="' . $rowspan . '" style="vertical-align:middle">' . $coil['nm_barang'] . '</td>';
                        $html .= '<td class="text-center" rowspan="' . $rowspan . '" style="vertical-align:middle">' . number_format($coil['qty_order'], 2) . '</td>';
                        $html .= '<td class="text-center" rowspan="' . $rowspan . '" style="vertical-align:middle">' . $coil['uom'] . '</td>';
                    }

                    // Detail Coil per baris
                    $html .= '<td class="text-center">' . $coil['no_coil'] . '</td>';
                    $html .= '<td class="text-right">' . number_format($coil['berat_kotor'], 2) . '</td>';
                    $html .= '<td class="text-right">' . number_format($coil['berat_bersih'], 2) . '</td>';
                    $html .= '<td class="text-center">
                            <input type="checkbox" class="check_item_modal" value="' . $coil['id'] . '">
                        </td>';

                    $html .= '</tr>';
                }
                $no++;
            }
        } else {
            $html .= '<tr><td colspan="8" class="text-center">No Data Available</td></tr>';
        }

        $html .= '</tbody></table></div>';

        echo $html;
    }

    public function print_qr_multi($ids)
    {
        // $ids berisi string seperti "12-13-14"
        $array_id = explode('-', $ids);

        $this->db->select('a.*, b.nm_supplier, b.awb_bl_number, c.qty as qty_order');
        $this->db->from('tr_ros_detail a');
        $this->db->join('tr_ros b', 'b.id = a.no_ros', 'left');
        $this->db->join('dt_trans_po c', 'c.id = a.id_po_detail', 'left');
        $this->db->where_in('a.id', $array_id);
        $data_coil = $this->db->get()->result_array();

        if (empty($data_coil)) {
            die("Data tidak ditemukan.");
        }

        $data = [
            'results' => $data_coil
        ];

        // Load view khusus untuk cetak
        $this->load->view('print_qr_label', $data);
    }
}
