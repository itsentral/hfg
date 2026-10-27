<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Bom extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Bom/Bom_model');
        $this->template->title('BOM (Bill of Material)');
        $this->template->page_icon('fa fa-sitemap');
    }

    /** List semua BOM per Produk */
    public function index()
    {
        $this->template->render('index');
    }

    /** Form tambah BOM baru */
    public function add()
    {
        $produk_list   = $this->Bom_model->get_produk_list();
        $material_list = $this->Bom_model->get_material_list();
        $standard_mat  = $this->Bom_model->get_standard_material();
        $this->template->set([
            'produk_list'   => $produk_list,
            'material_list' => $material_list,
            'standard_mat'  => $standard_mat,
        ]);
        $this->template->render('form');
    }

    /** Form edit BOM */
    public function edit($id)
    {
        $bom = $this->Bom_model->get_bom($id);
        if (!$bom) {
            $this->session->set_flashdata('error', 'BOM tidak ditemukan');
            redirect('bom');
        }

        $details       = $this->Bom_model->get_bom_details($id);
        $produk_list   = $this->Bom_model->get_produk_list();
        $material_list = $this->Bom_model->get_material_list();
        $standard_mat  = $this->Bom_model->get_standard_material();

        $this->template->set([
            'bom'           => $bom,
            'details'       => $details,
            'produk_list'   => $produk_list,
            'material_list' => $material_list,
            'standard_mat'  => $standard_mat,
        ]);
        $this->template->render('form');
    }

    /** Detail / view BOM */
    public function view($id)
    {
        $bom = $this->Bom_model->get_bom($id);
        if (!$bom) {
            $this->session->set_flashdata('error', 'BOM tidak ditemukan');
            redirect('bom');
        }

        $details = $this->Bom_model->get_bom_details($id);
        $this->template->set(['bom' => $bom, 'details' => $details]);
        $this->template->render('view');
    }

    /** DataTables endpoint - List per Produk FG sesuai Mockup BA */
    public function data_side_bom()
    {
        $result = $this->Bom_model->get_list_for_datatable($_REQUEST);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $btn_view = '<a href="' . base_url('bom/view/' . $row->id) . '" class="btn btn-sm btn-info" title="Lihat"><i class="fa fa-eye"></i></a>';
            $btn_edit = '<a href="' . base_url('bom/edit/' . $row->id) . '" class="btn btn-sm btn-warning ms-1" title="Edit"><i class="fa fa-edit"></i></a>';
            $btn_del  = '<button class="btn btn-sm btn-danger ms-1 btn-delete-bom" data-id="' . $row->id . '" title="Hapus"><i class="fa fa-trash"></i></button>';

            $tgl = !empty($row->latest_act) ? date('d/m/Y H:i', strtotime($row->latest_act)) : '-';

            $data[] = [
                $i + 1 + (int) $_REQUEST['start'],
                htmlspecialchars($row->id_produk),
                htmlspecialchars($row->nm_produk ?: '-'),
                '<span class="badge bg-primary fs-6">' . (int) $row->jumlah_varian . ' varian</span>',
                $tgl,
                $btn_view . $btn_edit . $btn_del,
            ];
        }

        echo json_encode([
            'draw'            => (int) $_REQUEST['draw'],
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }

    /** AJAX: ambil info material (trade_name, thickness, width, unit) by code_lv4 */
    public function get_material_info()
    {
        $code = $this->input->get('code_lv4');
        $mat  = $this->Bom_model->get_material_by_code($code);
        $std  = $this->Bom_model->get_standard_material();
        if ($mat) {
            $bpm = 0.0;
            if ((float)$mat->thickness > 0 && (float)$mat->width > 0) {
                $bpm = round((((float)$mat->thickness - (float)$std->standar_toleransi) * (float)$mat->width * (float)$std->standar_berat_jenis / 1000) + (float)$std->coating, 6);
            }
            echo json_encode(['success' => true, 'data' => $mat, 'bpm' => $bpm, 'std' => $std]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    /** POST: simpan BOM dengan seluruh varian material */
    public function save_bom()
    {
        $id_bom    = $this->input->post('id_bom');
        $id_produk = $this->input->post('id_produk');
        $nm_produk = $this->input->post('nm_produk');

        if (empty($id_produk)) {
            $this->session->set_flashdata('error', 'Produk wajib dipilih');
            redirect('bom/add');
        }

        $header = [
            'id'         => $id_bom,
            'id_produk'  => $id_produk,
            'nm_produk'  => $nm_produk,
            'created_by' => $this->auth->user_id(),
        ];

        $details_raw = $this->input->post('detail') ?: [];
        $details = [];
        $material_set = [];

        $uniq_set = [];
        foreach ($details_raw as $d) {
            if (!empty($d['id_material'])) {
                if (in_array($d['id_material'], $material_set)) {
                    $this->session->set_flashdata('error', 'Material yang sama tidak boleh dipakai dua kali pada produk ini.');
                    redirect($id_bom ? 'bom/edit/' . $id_bom : 'bom/add');
                }
                $material_set[] = $d['id_material'];

                $uniq = !empty($d['no_uniq_id']) ? trim($d['no_uniq_id']) : '';
                if (!empty($uniq)) {
                    if (in_array($uniq, $uniq_set)) {
                        $this->session->set_flashdata('error', "No. Uniq ID (Varian BOM) '{$uniq}' tidak boleh duplikat pada produk yang sama.");
                        redirect($id_bom ? 'bom/edit/' . $id_bom : 'bom/add');
                    }
                    $uniq_set[] = $uniq;
                }

                $details[] = $d;
            }
        }

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal satu varian material harus ditambahkan');
            redirect('bom/add');
        }

        // Ambil data lama jika ada untuk audit trail
        $existing   = $this->Bom_model->get_bom_by_produk($id_produk);
        $oldDetails = $existing ? $this->Bom_model->get_bom_details($existing->id) : [];

        $result = $this->Bom_model->save_bom($header, $details);

        if ($result) {
            $action_label = $existing ? 'Edit' : 'Add';
            $logPayload = [
                'id_produk'     => $id_produk,
                'nm_produk'     => $nm_produk,
                'total_varian'  => count($details),
                'daftar_varian' => array_map(function($x) {
                    return [
                        'no_uniq_id'      => $x['no_uniq_id'] ?? '',
                        'id_material'     => $x['id_material'] ?? '',
                        'berat_per_meter' => $x['berat_per_meter'] ?? 0,
                    ];
                }, $details),
            ];
            write_log('BOM', $action_label, ($action_label == 'Edit' ? 'Update BOM ' : 'Tambah BOM ') . $id_produk, $logPayload, null, 1);
            $this->session->set_flashdata('success', 'BOM berhasil disimpan.');
            redirect('bom');
        } else {
            write_log('BOM', 'Save', 'Gagal simpan BOM ' . $id_produk, ['header' => $header], null, 0);
            $this->session->set_flashdata('error', 'Gagal menyimpan BOM');
            redirect('bom/add');
        }
    }

    /** POST: hapus BOM */
    public function delete_bom()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID BOM tidak valid']);
            return;
        }

        $bom = $this->Bom_model->get_bom($id);
        $user_id = $this->auth->user_id();
        $result  = $this->Bom_model->delete_bom($id, $user_id);

        if ($result) {
            write_log('BOM', 'Delete', 'Hapus BOM ID ' . $id, ['bom' => $bom], null, 1);
            echo json_encode(['success' => true]);
        } else {
            write_log('BOM', 'Delete', 'Gagal hapus BOM ID ' . $id, [], null, 0);
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus BOM']);
        }
    }
}
