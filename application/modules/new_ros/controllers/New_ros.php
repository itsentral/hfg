<?php
defined('BASEPATH') or exit('No direct script access allowed');

class New_ros extends Admin_Controller
{
    protected $viewPermission   = 'New_ROS.View';
    protected $addPermission    = 'New_ROS.Add';
    protected $managePermission = 'New_ROS.Manage';
    protected $deletePermission = 'New_ROS.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('New_ros/New_ros_model'));
        date_default_timezone_set('Asia/Bangkok');
    }

    // ─── LIST ────────────────────────────────────────────────────────
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('New ROS');
        $this->template->render('index');
    }

    public function data_side()
    {
        $ENABLE_MANAGE = has_permission('New_ROS.Manage');
        $ENABLE_DELETE = has_permission('New_ROS.Delete');

        $fetch         = $this->New_ros_model->get_datatables();
        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $requestData = $_REQUEST;
        $data  = [];
        $urut1 = 1;
        $urut2 = 0;

        foreach ($query->result_array() as $row) {
            $start_dari = $requestData['start'];
            $asc_desc   = $requestData['order'][0]['dir'];
            $nomor = ($asc_desc == 'asc') ? ($totalData - $start_dari) - $urut2 : $urut1 + $start_dari;

            $edit_btn = '';
            if ($ENABLE_MANAGE && $row['status'] == '0') {
                $edit_btn = '<a href="' . base_url('new_ros/edit/' . $row['id']) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
            }

            $del_btn = '';
            if ($ENABLE_DELETE && $row['status'] == '0') {
                $del_btn = '<a href="javascript:void(0)" class="btn btn-sm btn-danger del_ros" style="margin-left:0.5rem;" title="Delete" data-id="' . $row['id'] . '"><i class="fa fa-trash"></i></a>';
            }

            $view_btn = '<a href="' . base_url('new_ros/view/' . $row['id']) . '" class="btn btn-sm btn-info" style="margin-left:0.5rem;" title="View"><i class="fa fa-eye"></i></a>';

            $sts = '<span class="badge rounded-pill bg-warning">Draft</span>';
            if ($row['status'] == '1') {
                $sts = '<span class="badge rounded-pill bg-success">Final</span>';
            }

            $nestedData   = [];
            $nestedData[] = "<div class='text-center'>{$nomor}</div>";
            $nestedData[] = "<div class='text-left'>{$row['id']}</div>";
            $nestedData[] = "<div class='text-left'>" . ($row['no_surat'] ?: $row['no_po']) . "</div>";
            $nestedData[] = "<div class='text-left'>{$row['nm_supplier']}</div>";
            $nestedData[] = "<div class='text-end'>" . number_format($row['nilai_po_pib_rp'], 2) . "</div>";
            $nestedData[] = "<div class='text-center'>{$sts}</div>";
            $nestedData[] = "<div class='text-center'>{$view_btn} {$edit_btn} {$del_btn}</div>";

            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        echo json_encode([
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    // ─── ADD ─────────────────────────────────────────────────────────
    public function add()
    {
        $this->auth->restrict($this->addPermission);

        $list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result_array();

        $this->db->where('is_delete', '0');
        $master_forwarding = $this->db->get('master_forwarding_cost')->row();
        $forwarding_rate = ($master_forwarding) ? $master_forwarding->value_cost : 0;

        $this->template->set('list_supplier', $list_supplier);
        $this->template->set('forwarding_rate', $forwarding_rate);
        $this->template->set('mode', 'add');
        $this->template->title('Add New ROS');
        $this->template->render('add');
    }

    // ─── AJAX: Get PO by Supplier ────────────────────────────────────
    public function get_po_by_supplier()
    {
        $id_supplier = $this->input->post('id_supplier');
        $exclude_ros = $this->input->post('exclude_ros'); // untuk edit mode

        $this->db->select('a.no_po, a.no_surat');
        $this->db->from('tr_purchase_order a');
        $this->db->join('tr_ros_header c', 'c.no_po = a.no_po' . ($exclude_ros ? " AND c.id != " . $this->db->escape($exclude_ros) : ''), 'left');
        $this->db->where('a.id_suplier', $id_supplier);
        $this->db->where('a.status', 2);
        $this->db->where('c.id IS NULL');
        $this->db->order_by('a.no_po', 'DESC');
        $list = $this->db->get()->result_array();

        echo json_encode(['status' => 1, 'data' => $list]);
    }

    // ─── EDIT ────────────────────────────────────────────────────────
    public function edit($id_ros)
    {
        $this->auth->restrict($this->managePermission);

        $header    = $this->New_ros_model->get_header($id_ros);
        if (!$header) {
            show_404();
            return;
        }

        $materials = $this->New_ros_model->get_materials($id_ros);
        foreach ($materials as &$mat) {
            $mat['coils'] = $this->New_ros_model->get_coils($mat['id']);
        }
        $others        = $this->New_ros_model->get_others($id_ros);
        $list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result_array();

        // Ambil list PO untuk supplier ini (termasuk yang sudah dipilih)
        $this->db->select('a.no_po, a.no_surat');
        $this->db->from('tr_purchase_order a');
        $this->db->join('tr_ros_header c', "c.no_po = a.no_po AND c.id != " . $this->db->escape($id_ros), 'left');
        $this->db->where('a.id_suplier', $header['id_supplier']);
        $this->db->where('a.status', 2);
        $this->db->where('c.id IS NULL');
        $this->db->order_by('a.no_po', 'DESC');
        $list_po = $this->db->get()->result_array();

        $this->db->where('is_delete', '0');
        $master_forwarding = $this->db->get('master_forwarding_cost')->row();
        $forwarding_rate = ($master_forwarding) ? $master_forwarding->value_cost : 0;

        $this->template->set('header', $header);
        $this->template->set('materials', $materials);
        $this->template->set('others', $others);
        $this->template->set('list_po', $list_po);
        $this->template->set('list_supplier', $list_supplier);
        $this->template->set('forwarding_rate', $forwarding_rate);
        $this->template->set('mode', 'edit');
        $this->template->title('Edit New ROS');
        $this->template->render('add');
    }

    // ─── VIEW ────────────────────────────────────────────────────────
    public function view($id_ros)
    {
        $this->auth->restrict($this->viewPermission);

        $header = $this->New_ros_model->get_header($id_ros);
        if (!$header) {
            show_404();
            return;
        }

        $materials = $this->New_ros_model->get_materials($id_ros);
        foreach ($materials as &$mat) {
            $mat['coils'] = $this->New_ros_model->get_coils($mat['id']);
        }
        $others = $this->New_ros_model->get_others($id_ros);

        $this->template->set('header', $header);
        $this->template->set('materials', $materials);
        $this->template->set('others', $others);
        $this->template->set('mode', 'view');
        $this->template->title('View New ROS');
        $this->template->render('view');
    }

    // ─── AJAX: Get PO Materials ──────────────────────────────────────
    public function get_po_materials()
    {
        $no_po      = $this->input->post('no_po');
        $kurs_pib   = (float) str_replace(',', '', $this->input->post('kurs_pib'));
        $materials  = $this->New_ros_model->get_po_materials($no_po);

        // Ambil supplier dari PO
        $po = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();
        $id_supplier = $po ? $po->id_suplier : '';

        $result = [];
        foreach ($materials as $mat) {
            $bm_persen = $this->New_ros_model->get_bm_persen($mat['idmaterial'], $id_supplier);
            $total_value_usd = (float) $mat['total_value_usd'];
            $total_value_rp  = $total_value_usd * $kurs_pib;
            $bm_rp           = $total_value_rp * ($bm_persen / 100);

            $result[] = [
                'id_po_detail'    => $mat['id_po_detail'],
                'idmaterial'      => $mat['idmaterial'],
                'nm_barang'       => $mat['nm_barang'],
                'nm_erp'          => $mat['nm_erp'] ?: $mat['nm_barang'],
                'nm_alias'        => $mat['nm_alias'] ?: $mat['nm_barang'],
                'kg_unit'         => (float) $mat['kg_unit'],
                'unit_price_usd'  => (float) $mat['unit_price_usd'],
                'total_value_usd' => $total_value_usd,
                'total_value_rp'  => $total_value_rp,
                'bm_persen'       => $bm_persen,
                'bm_rp'           => $bm_rp,
                'currency'        => $mat['currency'],
            ];
        }

        echo json_encode(['status' => 1, 'data' => $result]);
    }

    // ─── AJAX: Save Others Cost ──────────────────────────────────────
    public function save_others()
    {
        $post   = $this->input->post();
        $id_ros = $post['id_ros'];

        $this->db->trans_begin();
        $this->db->insert('tr_ros_others', [
            'id_ros'     => $id_ros,
            'keterangan' => $post['keterangan'],
            'nilai'      => str_replace(',', '', $post['nilai']),
            'created_by' => $this->auth->user_id(),
            'created_on' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0]);
        } else {
            $this->db->trans_commit();
            $total = $this->New_ros_model->get_total_others($id_ros);
            $others = $this->New_ros_model->get_others($id_ros);
            echo json_encode(['status' => 1, 'total' => $total, 'others' => $others]);
        }
    }

    // ─── AJAX: Delete Others Cost ────────────────────────────────────
    public function delete_others()
    {
        $id     = $this->input->post('id');
        $id_ros = $this->input->post('id_ros');

        $this->db->trans_begin();
        $this->db->delete('tr_ros_others', ['id' => $id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0]);
        } else {
            $this->db->trans_commit();
            $total = $this->New_ros_model->get_total_others($id_ros);
            echo json_encode(['status' => 1, 'total' => $total]);
        }
    }

    // ─── SAVE ROS ────────────────────────────────────────────────────
    public function save()
    {
        $post = $this->input->post();
        $this->db->trans_begin();

        $kurs_pib          = (float) str_replace(',', '', $post['kurs_pib']);
        $total_kg_bersih   = (float) str_replace(',', '', $post['total_kg_bersih_pib']);
        $insurance         = (float) str_replace(',', '', $post['insurance']);
        $biaya_ls          = (float) str_replace(',', '', $post['biaya_ls']);

        // Hitung total others
        $total_others = 0;
        if (isset($post['others_nilai']) && is_array($post['others_nilai'])) {
            foreach ($post['others_nilai'] as $val) {
                $total_others += (float) str_replace(',', '', $val);
            }
        }

        $is_new = ($post['id_ros'] == 'New');

        if ($is_new) {
            $id_ros = $this->New_ros_model->generate_no_ros();
        } else {
            $id_ros = $post['id_ros'];
        }

        // Ambil supplier info
        $get_supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $post['id_supplier']])->row_array();

        // ── Header ──
        $header_data = [
            'id_supplier'        => $post['id_supplier'],
            'nm_supplier'        => $get_supplier ? $get_supplier['nama'] : '',
            'no_po'              => $post['no_po'],
            'no_surat'           => $post['no_surat'],
            'nilai_po_usd'       => (float) str_replace(',', '', $post['nilai_po_usd']),
            'kurs_pib'           => $kurs_pib,
            'nilai_po_pib_rp'    => (float) str_replace(',', '', $post['nilai_po_pib_rp']),
            'total_kg_kotor_pib' => (float) str_replace(',', '', $post['total_kg_kotor_pib']),
            'total_kg_bersih_pib' => $total_kg_bersih,
            'cost_bm'            => (float) str_replace(',', '', $post['cost_bm']),
            'cost_bm_kite'       => (float) str_replace(',', '', $post['cost_bm_kite']),
            'cost_bmt'           => (float) str_replace(',', '', $post['cost_bmt']),
            'cost_cukai'         => (float) str_replace(',', '', $post['cost_cukai']),
            'cost_ppn'           => (float) str_replace(',', '', $post['cost_ppn']),
            'cost_ppnbm'         => (float) str_replace(',', '', $post['cost_ppnbm']),
            'cost_pph_import'    => (float) str_replace(',', '', $post['cost_pph_import']),
            'biaya_ls'           => $biaya_ls,
            'ppn_ls'             => (float) str_replace(',', '', $post['ppn_ls']),
            'pph_ls'             => (float) str_replace(',', '', $post['pph_ls']),
            'insurance'          => $insurance,
        ];

        if ($is_new) {
            $header_data['id']         = $id_ros;
            $header_data['status']     = 0;
            $header_data['created_by'] = $this->auth->user_id();
            $header_data['created_on'] = date('Y-m-d H:i:s');
            $this->db->insert('tr_ros_header', $header_data);
        } else {
            $header_data['modified_by'] = $this->auth->user_id();
            $header_data['modified_on'] = date('Y-m-d H:i:s');
            $this->db->update('tr_ros_header', $header_data, ['id' => $id_ros]);

            // Hapus material & coil lama
            $old_materials = $this->db->select('id')->get_where('tr_ros_material', ['id_ros' => $id_ros])->result_array();
            foreach ($old_materials as $om) {
                $this->db->delete('tr_ros_material_coil', ['id_ros_material' => $om['id']]);
            }
            $this->db->delete('tr_ros_material', ['id_ros' => $id_ros]);

            // Hapus others lama dan insert ulang
            $this->db->delete('tr_ros_others', ['id_ros' => $id_ros]);
        }

        // ── Others Cost ──
        // Baris 349 (Estimasi)
        if (isset($post['others_keterangan']) && is_array($post['others_keterangan'])) {
            foreach ($post['others_keterangan'] as $idx => $ket) {
                // Tambahkan pengecekan isset untuk index $idx
                $nilai_raw = isset($post['others_nilai'][$idx]) ? $post['others_nilai'][$idx] : 0;
                $nilai_other = (float) str_replace(',', '', $nilai_raw);

                if (!empty($ket) || $nilai_other > 0) {
                    $this->db->insert('tr_ros_others', [
                        'id_ros'     => $id_ros,
                        'keterangan' => $ket,
                        'nilai'      => $nilai_other,
                        'created_by' => $this->auth->user_id(),
                        'created_on' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        // ── Hitung total KG LS untuk prorate ──
        $total_kg_ls = 0;
        if (isset($post['mat']) && is_array($post['mat'])) {
            foreach ($post['mat'] as $mat) {
                $ls_flag = isset($mat['ls_flag']) ? $mat['ls_flag'] : 'TIDAK';
                if ($ls_flag == 'YA') {
                    $total_kg_ls += (float) str_replace(',', '', $mat['kg_unit']);
                }
            }
        }

        // ── Materials ──
        if (isset($post['mat']) && is_array($post['mat'])) {
            foreach ($post['mat'] as $mat) {
                $kg_unit         = (float) str_replace(',', '', $mat['kg_unit']);
                $unit_price_usd  = (float) str_replace(',', '', $mat['unit_price_usd']);
                $total_value_usd = (float) str_replace(',', '', $mat['total_value_usd']);
                $total_value_rp  = $total_value_usd * $kurs_pib;
                $bm_persen       = (float) $mat['bm_persen'];
                $bm_rp           = $total_value_rp * ($bm_persen / 100);
                $ls_flag         = isset($mat['ls_flag']) ? $mat['ls_flag'] : 'TIDAK';

                // Prorate LS
                $prorate_ls = 0;
                if ($ls_flag == 'YA' && $total_kg_ls > 0) {
                    $prorate_ls = $biaya_ls * ($kg_unit / $total_kg_ls);
                }

                // Forwarding cost = Rate dari master_forwarding_cost * kg_unit
                $this->db->where('is_delete', '0');
                $master_fwd = $this->db->get('master_forwarding_cost')->row();
                $fwd_rate = ($master_fwd) ? (float) $master_fwd->value_cost : 0;
                $forwarding_cost = $fwd_rate * $kg_unit;

                // Prorate Insurance
                $prorate_insurance = 0;
                if ($total_kg_bersih > 0) {
                    $prorate_insurance = $insurance * ($kg_unit / $total_kg_bersih);
                }

                // Prorate Others
                $prorate_others = 0;
                if ($total_kg_bersih > 0) {
                    $prorate_others = $total_others * ($kg_unit / $total_kg_bersih);
                }

                // Total Nilai Inventory
                $total_nilai_inventory = $total_value_rp + $bm_rp + $prorate_ls + $forwarding_cost + $prorate_insurance + $prorate_others;

                // Cost Book
                $cost_book = ($kg_unit > 0) ? $total_nilai_inventory / $kg_unit : 0;

                $this->db->insert('tr_ros_material', [
                    'id_ros'               => $id_ros,
                    'id_po_detail'         => $mat['id_po_detail'],
                    'id_barang'            => $mat['id_barang'],
                    'nm_barang'            => $mat['nm_barang'],
                    'nm_erp'               => $mat['nm_erp'],
                    'nm_alias'             => $mat['nm_alias'],
                    'kg_unit'              => $kg_unit,
                    'unit_price_usd'       => $unit_price_usd,
                    'total_value_usd'      => $total_value_usd,
                    'total_value_rp'       => $total_value_rp,
                    'bm_persen'            => $bm_persen,
                    'bm_rp'                => $bm_rp,
                    'prorate_ls'           => $prorate_ls,
                    'forwarding_cost'      => $forwarding_cost,
                    'prorate_insurance'    => $prorate_insurance,
                    'prorate_others'       => $prorate_others,
                    'total_nilai_inventory' => $total_nilai_inventory,
                    'cost_book'            => $cost_book,
                    'ls_flag'              => $ls_flag,
                    'created_by'           => $this->auth->user_id(),
                    'created_on'           => date('Y-m-d H:i:s')
                ]);

                $id_ros_material = $this->db->insert_id();

                // ── Coils ──
                if (isset($mat['coil']) && is_array($mat['coil'])) {
                    foreach ($mat['coil'] as $coil) {
                        $berat_kotor  = (float) str_replace(',', '', $coil['berat_kotor']);
                        $berat_bersih = (float) str_replace(',', '', $coil['berat_bersih']);
                        if ($berat_kotor > 0 || !empty($coil['no_coil'])) {
                            $this->db->insert('tr_ros_material_coil', [
                                'id_ros_material' => $id_ros_material,
                                'no_coil'         => $coil['no_coil'],
                                'berat_kotor'     => $berat_kotor,
                                'berat_bersih'    => $berat_bersih,
                                'panjang'         => (float) str_replace(',', '', $coil['panjang']),
                                'kode_internal'   => isset($coil['kode_internal']) ? $coil['kode_internal'] : '',
                                'created_by'      => $this->auth->user_id(),
                                'created_on'      => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'msg' => 'Gagal menyimpan data ROS.']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => 1, 'msg' => 'Data ROS berhasil disimpan.', 'id' => $id_ros]);
        }
    }

    // ─── DELETE ───────────────────────────────────────────────────────
    public function delete()
    {
        $id_ros = $this->input->post('id');

        $this->db->trans_begin();

        // Hapus coils
        $materials = $this->db->select('id')->get_where('tr_ros_material', ['id_ros' => $id_ros])->result_array();
        foreach ($materials as $m) {
            $this->db->delete('tr_ros_material_coil', ['id_ros_material' => $m['id']]);
        }
        $this->db->delete('tr_ros_material', ['id_ros' => $id_ros]);
        $this->db->delete('tr_ros_others', ['id_ros' => $id_ros]);
        $this->db->delete('tr_ros_header', ['id' => $id_ros]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0]);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => 1]);
        }
    }

    // ─── DOWNLOAD TEMPLATE EXCEL ─────────────────────────────────────
    public function download_template()
    {
        $materials_json = $this->input->post('materials');
        $materials = json_decode($materials_json, true);

        if (empty($materials)) {
            show_error('Tidak ada data material.');
            return;
        }

        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Packing List');

        // Header style
        $headerStyle = array(
            'font' => array('bold' => true, 'size' => 10),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            ),
            'borders' => array(
                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
            ),
            'fill' => array(
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'D9E1F2')
            )
        );

        $dataStyle = array(
            'borders' => array(
                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
            ),
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(10);

        // Header row
        $sheet->setCellValue('A1', 'COIL NO.');
        $sheet->setCellValue('B1', 'NAMA Sesuai PO');
        $sheet->setCellValue('C1', "COIL\nNUMBER");
        $sheet->setCellValue('D1', "N.W.\n(KGS)");
        $sheet->setCellValue('E1', "G.W.\n(KGS)");
        $sheet->setCellValue('F1', "LENGTH\n(M)");
        $sheet->setCellValue('G1', 'BPM');

        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Data rows — 1 baris per material (nama lain sudah terisi)
        $row = 2;
        foreach ($materials as $nm_alias) {
            $sheet->setCellValue('A' . $row, ''); // COIL NO — user isi
            $sheet->setCellValue('B' . $row, $nm_alias); // Nama Sesuai PO — sudah terisi
            $sheet->setCellValue('C' . $row, 1); // COIL NUMBER default 1
            $sheet->setCellValue('D' . $row, ''); // N.W.
            $sheet->setCellValue('E' . $row, ''); // G.W.
            $sheet->setCellValue('F' . $row, ''); // LENGTH
            $sheet->setCellValue('G' . $row, ''); // BPM

            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($dataStyle);
            $row++;
        }

        // Output
        $filename = 'Template_Packing_List_' . date('Ymd_His') . '.xlsx';

        ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    // ─── PARSE PACKING LIST (Excel) → Return JSON tanpa save ────────
    // Bisa dipakai untuk ROS baru (belum disimpan) maupun edit
    public function parse_packing_list()
    {
        // Upload file
        $config['upload_path']   = FCPATH . 'assets/uploads/new_ros/';
        $config['allowed_types'] = 'xlsx|xls';
        $config['max_size']      = 10240;
        $config['encrypt_name']  = TRUE;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_packing_list')) {
            echo json_encode(['status' => 0, 'msg' => strip_tags($this->upload->display_errors())]);
            return;
        }

        $file_data     = $this->upload->data();
        $file_path     = $file_data['full_path'];
        $original_name = $file_data['client_name'];
        $hash_name     = $file_data['file_name'];

        // Ambil inisial supplier
        $id_supplier = $this->input->post('id_supplier');
        $supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $id_supplier])->row_array();
        $inisial  = isset($supplier['inisial']) ? $supplier['inisial'] : '';

        // Existing coil count (untuk counter kode internal)
        $existing_count = (int) $this->input->post('existing_coil_count');

        // Parse Excel
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');

        try {
            $objReader = PHPExcel_IOFactory::createReaderForFile($file_path);
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($file_path);
        } catch (Exception $e) {
            echo json_encode(['status' => 0, 'msg' => 'Gagal membaca file Excel: ' . $e->getMessage()]);
            return;
        }

        $sheet   = $objPHPExcel->getActiveSheet();
        $highRow = $sheet->getHighestRow();

        // Mapping kolom
        $start_row = 2;
        $col_coil_no = 'B';
        $col_nama_po = 'C';
        $col_number  = 'D';
        $col_nw = 'E';
        $col_gw = 'F';
        $col_length  = 'G';
        $col_bpm = 'H';

        for ($r = 1; $r <= min($highRow, 10); $r++) {
            $cellA = strtolower(trim((string) $sheet->getCell('A' . $r)->getValue()));
            $cellB = strtolower(trim((string) $sheet->getCell('B' . $r)->getValue()));

            if (strpos($cellA, 'coil no') !== false) {
                $col_coil_no = 'A';
                $col_nama_po = 'B';
                $col_number = 'C';
                $col_nw = 'D';
                $col_gw = 'E';
                $col_length = 'F';
                $col_bpm = 'G';
                $start_row = $r + 1;
                break;
            } elseif (strpos($cellB, 'coil no') !== false) {
                $col_coil_no = 'B';
                $col_nama_po = 'C';
                $col_number = 'D';
                $col_nw = 'E';
                $col_gw = 'F';
                $col_length = 'G';
                $col_bpm = 'H';
                $start_row = $r + 1;
                break;
            }
        }

        $counter = $existing_count + 1;
        $coils = [];

        for ($row = $start_row; $row <= $highRow; $row++) {
            $getCellValue = function ($col) use ($sheet, $row) {
                $cell = $sheet->getCell($col . $row);
                if ($cell->getDataType() == PHPExcel_Cell_DataType::TYPE_FORMULA) {
                    return $cell->getOldCalculatedValue();
                }
                return $cell->getValue();
            };

            $coil_no     = trim((string) $getCellValue($col_coil_no));
            $nm_po       = trim((string) $getCellValue($col_nama_po));
            $coil_number = $getCellValue($col_number);
            $nw          = $getCellValue($col_nw);
            $gw          = $getCellValue($col_gw);
            $length      = $getCellValue($col_length);
            $bpm         = $getCellValue($col_bpm);

            if (empty($coil_no) || strtolower($coil_no) == 'total') continue;
            if (strpos(strtolower($coil_no), 'error') !== false) continue;

            $nw_val     = (float) str_replace(',', '', (string) $nw);
            $gw_val     = (float) str_replace(',', '', (string) $gw);
            $length_val = (float) str_replace(',', '', (string) $length);
            $bpm_val    = (float) str_replace(',', '', (string) $bpm);

            $kode_internal = $inisial . '-' . $coil_no . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

            $coils[] = [
                'no_coil'        => $coil_no,
                'nama_sesuai_po' => $nm_po,
                'coil_number'    => (int) $coil_number ?: 1,
                'berat_bersih'   => $nw_val,
                'berat_kotor'    => $gw_val,
                'panjang'        => $length_val,
                'bpm'            => $bpm_val,
                'kode_internal'  => $kode_internal,
            ];
            $counter++;
        }

        echo json_encode([
            'status'        => 1,
            'msg'           => 'Berhasil membaca ' . count($coils) . ' baris coil.',
            'coils'         => $coils,
            'total'         => count($coils),
            'file_original' => $original_name,
            'file_hash'     => $hash_name,
        ]);
    }

    // ─── UPLOAD PACKING LIST (Excel) → Simpan ke tabel sementara ────
    public function upload_packing_list()
    {
        $id_ros = $this->input->post('id_ros');
        if (!$id_ros || $id_ros == 'New') {
            echo json_encode(['status' => 0, 'msg' => 'Simpan ROS terlebih dahulu sebelum upload packing list.']);
            return;
        }

        // Cek ROS exists
        $header = $this->New_ros_model->get_header($id_ros);
        if (!$header) {
            echo json_encode(['status' => 0, 'msg' => 'Data ROS tidak ditemukan.']);
            return;
        }

        // Upload file
        $config['upload_path']   = FCPATH . 'assets/uploads/new_ros/';
        $config['allowed_types'] = 'xlsx|xls';
        $config['max_size']      = 10240; // 10MB
        $config['encrypt_name']  = TRUE;

        // Buat folder jika belum ada
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_packing_list')) {
            echo json_encode(['status' => 0, 'msg' => strip_tags($this->upload->display_errors())]);
            return;
        }

        $file_data     = $this->upload->data();
        $file_path     = $file_data['full_path'];
        $original_name = $file_data['client_name'];
        $hash_name     = $file_data['file_name'];

        // Parse Excel
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $this->load->library('PHPExcel');

        try {
            $objReader = PHPExcel_IOFactory::createReaderForFile($file_path);
            $objReader->setReadDataOnly(true); // baca cached value saja, tidak kalkulasi ulang
            $objPHPExcel = $objReader->load($file_path);
        } catch (Exception $e) {
            echo json_encode(['status' => 0, 'msg' => 'Gagal membaca file Excel: ' . $e->getMessage()]);
            return;
        }

        $sheet    = $objPHPExcel->getActiveSheet();
        $highRow  = $sheet->getHighestRow();

        // Ambil materials untuk ROS ini
        $materials = $this->New_ros_model->get_materials($id_ros);
        if (empty($materials)) {
            echo json_encode(['status' => 0, 'msg' => 'Tidak ada material di ROS ini. Load data PO dan simpan terlebih dahulu.']);
            return;
        }

        // Build lookup: nm_alias / nm_barang (lowercase) → material id
        $mat_lookup = [];
        foreach ($materials as $mat) {
            $key = strtolower(trim($mat['nm_alias']));
            if ($key) $mat_lookup[$key] = $mat['id'];
            $key2 = strtolower(trim($mat['nm_barang']));
            if ($key2 && !isset($mat_lookup[$key2])) $mat_lookup[$key2] = $mat['id'];
            // Juga nm_erp
            $key3 = strtolower(trim($mat['nm_erp']));
            if ($key3 && !isset($mat_lookup[$key3])) $mat_lookup[$key3] = $mat['id'];
        }

        // Ambil inisial supplier
        $supplier = $this->db->get_where('new_supplier', ['kode_supplier' => $header['id_supplier']])->row_array();
        $inisial  = isset($supplier['inisial']) ? $supplier['inisial'] : '';

        // Hapus data temp lama untuk ROS + session ini
        $session_id = session_id();
        $this->db->delete('tr_ros_upload_temp', ['id_ros' => $id_ros, 'session_id' => $session_id]);

        // Cari header row dan tentukan mapping kolom
        // Format Excel: (A kosong) | B: COIL NO. | C: NAMA Sesuai PO | D: COIL NUMBER | E: N.W. | F: G.W. | G: LENGTH | H: BPM
        $start_row = 2;
        $col_coil_no = 'B';
        $col_nama_po = 'C';
        $col_number  = 'D';
        $col_nw = 'E';
        $col_gw = 'F';
        $col_length  = 'G';
        $col_bpm = 'H';

        for ($r = 1; $r <= min($highRow, 10); $r++) {
            $cellA = strtolower(trim((string) $sheet->getCell('A' . $r)->getValue()));
            $cellB = strtolower(trim((string) $sheet->getCell('B' . $r)->getValue()));

            if (strpos($cellA, 'coil no') !== false) {
                // Data mulai dari kolom A
                $col_coil_no = 'A';
                $col_nama_po = 'B';
                $col_number = 'C';
                $col_nw = 'D';
                $col_gw = 'E';
                $col_length = 'F';
                $col_bpm = 'G';
                $start_row = $r + 1;
                break;
            } elseif (strpos($cellB, 'coil no') !== false) {
                // Data mulai dari kolom B (kolom A kosong)
                $col_coil_no = 'B';
                $col_nama_po = 'C';
                $col_number = 'D';
                $col_nw = 'E';
                $col_gw = 'F';
                $col_length = 'G';
                $col_bpm = 'H';
                $start_row = $r + 1;
                break;
            }
        }

        // Hitung existing coil count untuk counter kode internal
        $this->db->select('c.id');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material');
        $this->db->where('m.id_ros', $id_ros);
        $existing_count = $this->db->get()->num_rows();
        $counter = $existing_count + 1;

        $rows_parsed = 0;
        $rows_matched = 0;

        for ($row = $start_row; $row <= $highRow; $row++) {
            $getCellValue = function ($col) use ($sheet, $row) {
                $cell = $sheet->getCell($col . $row);
                if ($cell->getDataType() == PHPExcel_Cell_DataType::TYPE_FORMULA) {
                    return $cell->getOldCalculatedValue();
                }
                return $cell->getValue();
            };

            $coil_no     = trim((string) $getCellValue($col_coil_no));
            $nm_po       = trim((string) $getCellValue($col_nama_po));
            $coil_number = $getCellValue($col_number);
            $nw          = $getCellValue($col_nw);
            $gw          = $getCellValue($col_gw);
            $length      = $getCellValue($col_length);
            $bpm         = $getCellValue($col_bpm);

            // Skip row kosong atau row TOTAL/ERROR
            if (empty($coil_no) || strtolower($coil_no) == 'total') continue;
            if (strpos(strtolower($coil_no), 'error') !== false) continue;

            $nw_val     = (float) str_replace(',', '', (string) $nw);
            $gw_val     = (float) str_replace(',', '', (string) $gw);
            $length_val = (float) str_replace(',', '', (string) $length);
            $bpm_val    = (float) str_replace(',', '', (string) $bpm);

            // Match material
            $nm_po_lower = strtolower(trim($nm_po));
            $id_ros_material = isset($mat_lookup[$nm_po_lower]) ? $mat_lookup[$nm_po_lower] : null;
            $is_matched = $id_ros_material ? 1 : 0;

            // Generate kode internal
            $kode_internal = $inisial . '-' . $coil_no . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

            $this->db->insert('tr_ros_upload_temp', [
                'id_ros'          => $id_ros,
                'session_id'      => $session_id,
                'no_coil'         => $coil_no,
                'nama_sesuai_po'  => $nm_po,
                'coil_number'     => (int) $coil_number ?: 1,
                'berat_bersih'    => $nw_val,
                'berat_kotor'     => $gw_val,
                'panjang'         => $length_val,
                'bpm'             => $bpm_val,
                'id_ros_material' => $id_ros_material,
                'kode_internal'   => $kode_internal,
                'is_matched'      => $is_matched,
                'created_on'      => date('Y-m-d H:i:s')
            ]);

            $rows_parsed++;
            if ($is_matched) $rows_matched++;
            $counter++;
        }

        // Simpan info file ke header
        $this->db->update('tr_ros_header', [
            'file_original_name' => $original_name,
            'file_hash_name'     => $hash_name,
            'modified_by'        => $this->auth->user_id(),
            'modified_on'        => date('Y-m-d H:i:s')
        ], ['id' => $id_ros]);

        echo json_encode([
            'status'       => 1,
            'msg'          => "Berhasil membaca {$rows_parsed} baris. {$rows_matched} matched, " . ($rows_parsed - $rows_matched) . " tidak match.",
            'total_parsed' => $rows_parsed,
            'total_matched' => $rows_matched,
            'file'         => $original_name
        ]);
    }

    // ─── AJAX: Get data temp untuk review di modal ───────────────────
    public function get_upload_review()
    {
        $id_ros     = $this->input->post('id_ros');
        $session_id = session_id();

        $this->db->select('t.*, m.nm_alias, m.nm_barang');
        $this->db->from('tr_ros_upload_temp t');
        $this->db->join('tr_ros_material m', 'm.id = t.id_ros_material', 'left');
        $this->db->where('t.id_ros', $id_ros);
        $this->db->where('t.session_id', $session_id);
        $this->db->order_by('t.id', 'ASC');
        $data = $this->db->get()->result_array();

        echo json_encode(['status' => 1, 'data' => $data, 'total' => count($data)]);
    }

    // ─── AJAX: Konfirmasi upload → pindah dari temp ke tabel asli ────
    public function confirm_upload()
    {
        $id_ros     = $this->input->post('id_ros');
        $session_id = session_id();

        // Ambil data temp
        $temp_data = $this->db->get_where('tr_ros_upload_temp', [
            'id_ros'     => $id_ros,
            'session_id' => $session_id,
            'is_matched' => 1
        ])->result_array();

        if (empty($temp_data)) {
            echo json_encode(['status' => 0, 'msg' => 'Tidak ada data yang bisa dikonfirmasi (tidak ada yang match).']);
            return;
        }

        $this->db->trans_begin();

        $inserted = 0;
        foreach ($temp_data as $row) {
            $this->db->insert('tr_ros_material_coil', [
                'id_ros_material' => $row['id_ros_material'],
                'no_coil'         => $row['no_coil'],
                'berat_kotor'     => $row['berat_kotor'],
                'berat_bersih'    => $row['berat_bersih'],
                'panjang'         => $row['panjang'],
                'kode_internal'   => $row['kode_internal'],
                'created_by'      => $this->auth->user_id(),
                'created_on'      => date('Y-m-d H:i:s')
            ]);
            $inserted++;
        }

        // Hapus semua data temp untuk session ini
        $this->db->delete('tr_ros_upload_temp', ['id_ros' => $id_ros, 'session_id' => $session_id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'msg' => 'Gagal menyimpan data coil.']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => 1, 'msg' => "Berhasil menyimpan {$inserted} coil.", 'total' => $inserted]);
        }
    }

    // ─── AJAX: Batal upload → hapus data temp ────────────────────────
    public function cancel_upload()
    {
        $id_ros     = $this->input->post('id_ros');
        $session_id = session_id();

        $this->db->delete('tr_ros_upload_temp', ['id_ros' => $id_ros, 'session_id' => $session_id]);
        echo json_encode(['status' => 1]);
    }

    // ─── AJAX: Get Coil List (untuk modal print QR) ──────────────────
    public function get_coil_list()
    {
        $id_ros = $this->input->post('id_ros');

        $this->db->select('c.*, m.nm_barang, m.nm_alias, m.nm_erp');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material', 'left');
        $this->db->where('m.id_ros', $id_ros);
        $this->db->order_by('m.id', 'ASC');
        $this->db->order_by('c.id', 'ASC');
        $coils = $this->db->get()->result_array();

        // Group by material
        $groups = [];
        foreach ($coils as $c) {
            $groups[$c['id_ros_material']][] = $c;
        }

        $html = '<div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Material</th>
                    <th class="text-center">Nama Alias</th>
                    <th class="text-center">No. Coil</th>
                    <th class="text-center">Kode Internal</th>
                    <th class="text-center">N.W. (Kg)</th>
                    <th class="text-center">G.W. (Kg)</th>
                    <th class="text-center">Length (M)</th>
                    <th class="text-center"><input type="checkbox" id="check_all_modal"></th>
                </tr>
            </thead>
            <tbody>';

        if (!empty($groups)) {
            $no = 1;
            foreach ($groups as $id_mat => $rows) {
                $rowspan = count($rows);
                foreach ($rows as $idx => $coil) {
                    $html .= '<tr>';
                    if ($idx === 0) {
                        $html .= '<td class="text-center" rowspan="' . $rowspan . '" style="vertical-align:middle">' . $no . '</td>';
                        $html .= '<td rowspan="' . $rowspan . '" style="vertical-align:middle">' . $coil['nm_erp'] . '</td>';
                        $html .= '<td rowspan="' . $rowspan . '" style="vertical-align:middle">' . $coil['nm_alias'] . '</td>';
                    }
                    $html .= '<td class="text-center">' . $coil['no_coil'] . '</td>';
                    $html .= '<td class="text-center">' . $coil['kode_internal'] . '</td>';
                    $html .= '<td class="text-end">' . number_format($coil['berat_bersih'], 2) . '</td>';
                    $html .= '<td class="text-end">' . number_format($coil['berat_kotor'], 2) . '</td>';
                    $html .= '<td class="text-end">' . number_format($coil['panjang'], 2) . '</td>';
                    $html .= '<td class="text-center"><input type="checkbox" class="check_item_modal" value="' . $coil['id'] . '"></td>';
                    $html .= '</tr>';
                }
                $no++;
            }
        } else {
            $html .= '<tr><td colspan="9" class="text-center">Belum ada data coil. Upload packing list terlebih dahulu.</td></tr>';
        }

        $html .= '</tbody></table></div>';
        echo $html;
    }

    // ─── PRINT QR CODE ───────────────────────────────────────────────
    public function print_qr($ids)
    {
        $array_id = explode('-', $ids);

        $this->db->select('c.*, m.nm_barang, m.nm_alias, m.nm_erp, h.id as no_ros, h.nm_supplier');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material', 'left');
        $this->db->join('tr_ros_header h', 'h.id = m.id_ros', 'left');
        $this->db->where_in('c.id', $array_id);
        $data_coil = $this->db->get()->result_array();

        if (empty($data_coil)) {
            die("Data tidak ditemukan.");
        }

        $data = ['results' => $data_coil];
        $this->load->view('print_qr_label', $data);
    }

    // ─── FINALIZE ROS → Pindah ke Incoming ───────────────────────────
    public function finalize()
    {
        $id_ros = $this->input->post('id_ros');

        // Cek apakah sudah ada coil
        $this->db->select('c.id');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material');
        $this->db->where('m.id_ros', $id_ros);
        $coil_count = $this->db->get()->num_rows();

        if ($coil_count == 0) {
            echo json_encode(['status' => 0, 'msg' => 'Upload packing list terlebih dahulu sebelum finalize.']);
            return;
        }

        $this->db->trans_begin();
        $this->db->update('tr_ros_header', [
            'status'      => 1,
            'modified_by' => $this->auth->user_id(),
            'modified_on' => date('Y-m-d H:i:s')
        ], ['id' => $id_ros]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'msg' => 'Gagal finalize ROS.']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => 1, 'msg' => 'ROS berhasil di-finalize. Silakan proses di menu Incoming.']);
        }
    }

    // ─── AJAX: Get Coils data for view after upload ──────────────────
    public function get_coils_data()
    {
        $id_ros = $this->input->post('id_ros');

        $this->db->select('c.*, m.nm_barang, m.nm_alias');
        $this->db->from('tr_ros_material_coil c');
        $this->db->join('tr_ros_material m', 'm.id = c.id_ros_material', 'left');
        $this->db->where('m.id_ros', $id_ros);
        $this->db->order_by('m.id', 'ASC');
        $this->db->order_by('c.id', 'ASC');
        $coils = $this->db->get()->result_array();

        echo json_encode(['status' => 1, 'data' => $coils, 'total' => count($coils)]);
    }
}
