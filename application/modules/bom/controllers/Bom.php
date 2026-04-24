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

    /** List semua BOM */
    public function index()
    {
        $this->template->render('index');
    }

    /** Form tambah BOM baru */
    public function add()
    {
        $produk_list   = $this->Bom_model->get_produk_list();
        $material_list = $this->Bom_model->get_material_list();
        $this->template->set(['produk_list' => $produk_list, 'material_list' => $material_list]);
        $this->template->render('form');
    }

    /** Form edit BOM */
    public function edit($id)
    {
        $bom     = $this->Bom_model->get_bom($id);
        if (!$bom) {
            $this->session->set_flashdata('error', 'BOM tidak ditemukan');
            redirect('bom');
        }

        $details       = $this->Bom_model->get_bom_details($id);
        $produk_list   = $this->Bom_model->get_produk_list();
        $material_list = $this->Bom_model->get_material_list();

        $this->template->set([
            'bom'           => $bom,
            'details'       => $details,
            'produk_list'   => $produk_list,
            'material_list' => $material_list,
        ]);
        $this->template->render('form');
    }

    /** Detail / view BOM */
    public function view($id)
    {
        $bom     = $this->Bom_model->get_bom($id);
        if (!$bom) {
            $this->session->set_flashdata('error', 'BOM tidak ditemukan');
            redirect('bom');
        }

        $details = $this->Bom_model->get_bom_details($id);
        $this->template->set(['bom' => $bom, 'details' => $details]);
        $this->template->render('view');
    }

    /** DataTables endpoint */
    public function data_side_bom()
    {
        $result = $this->Bom_model->get_list_for_datatable($_REQUEST);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $btn_view = '<a href="' . base_url('bom/view/' . $row->id) . '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>';
            $btn_edit = '<a href="' . base_url('bom/edit/' . $row->id) . '" class="btn btn-sm btn-warning ms-1"><i class="fa fa-edit"></i></a>';
            $btn_del  = '<button class="btn btn-sm btn-danger ms-1 btn-delete-bom" data-id="' . $row->id . '"><i class="fa fa-trash"></i></button>';

            $data[] = [
                $i + 1 + (int) $_REQUEST['start'],
                htmlspecialchars($row->id_produk),
                htmlspecialchars($row->nm_produk ?: '-'),
                (int) $row->jumlah_material,
                $row->created_at,
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

    /** AJAX: ambil info material (trade_name, unit) by code_lv4 */
    public function get_material_info()
    {
        $code = $this->input->get('code_lv4');
        $mat  = $this->Bom_model->get_material_by_code($code);
        if ($mat) {
            echo json_encode(['success' => true, 'data' => $mat]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    /** POST: simpan BOM */
    public function save_bom()
    {
        $id_produk  = $this->input->post('id_produk');
        $nm_produk  = $this->input->post('nm_produk');
        $keterangan = $this->input->post('keterangan');

        if (empty($id_produk)) {
            $this->session->set_flashdata('error', 'Produk wajib dipilih');
            redirect('bom/add');
        }

        $header = [
            'id_produk'  => $id_produk,
            'nm_produk'  => $nm_produk,
            'keterangan' => $keterangan,
            'created_by' => $this->auth->user_id(),
        ];

        $details_raw = $this->input->post('detail') ?: [];
        $details = [];
        foreach ($details_raw as $d) {
            if (!empty($d['id_material'])) {
                $details[] = $d;
            }
        }

        if (empty($details)) {
            $this->session->set_flashdata('error', 'Minimal satu material harus ditambahkan');
            redirect('bom/add');
        }

        $result = $this->Bom_model->save_bom($header, $details);

        if ($result) {
            $this->session->set_flashdata('success', 'BOM berhasil disimpan');
            redirect('bom/view/' . $result);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan BOM');
            redirect('bom/add');
        }
    }

    /** POST: hapus BOM (soft delete) */
    public function delete_bom()
    {
        $id     = $this->input->post('id');
        $result = $this->Bom_model->delete_bom($id, $this->auth->user_id());
        echo json_encode(['success' => $result]);
    }
}
