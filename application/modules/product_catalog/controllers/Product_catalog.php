<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_catalog extends Admin_Controller
{
    protected $viewPermission   = 'Product_Catalog.View';
    protected $managePermission = 'Product_Catalog.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('product_catalog/Product_catalog_model', 'pc_model');
        $this->template->title('Product Catalog');
        $this->template->page_icon('fa fa-book');
    }

    /**
     * Tampilan Utama Katalog Produk HFG
     * Nama produk di depan (FR-73)
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $category   = $this->input->get('category');
        $catalog_id = $this->input->get('catalog_id');
        $status     = $this->input->get('status') !== null ? $this->input->get('status') : 'Active';
        $date_from  = $this->input->get('date_from');
        $date_to    = $this->input->get('date_to');

        $items        = $this->pc_model->get_catalog_items($category, $catalog_id, $status, $date_from, $date_to);
        $headers      = $this->pc_model->get_catalog_headers();
        $categories   = $this->pc_model->get_categories();
        $approved_pls = $this->pc_model->get_approved_pricelists();
        $stats        = $this->pc_model->get_summary_stats();

        $this->template->set([
            'items'            => $items,
            'headers'          => $headers,
            'categories'       => $categories,
            'approved_pls'     => $approved_pls,
            'stats'            => $stats,
            'filter_cat'       => $category,
            'filter_cid'       => $catalog_id,
            'filter_stat'      => $status,
            'filter_date_from' => $date_from,
            'filter_date_to'   => $date_to,
        ]);
        $this->template->render('index');
    }

    /**
     * Ambil Detail Rilis Catalog (AJAX)
     */
    public function detail($id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$id) {
            $id = (int)$this->input->get('id');
        }

        $catalog = $this->pc_model->get_catalog_by_id($id);
        if (!$catalog) {
            echo json_encode(['status' => 0, 'pesan' => 'Catalog not found']);
            return;
        }

        echo json_encode(['status' => 1, 'data' => $catalog]);
    }

    /**
     * Rilis Product Catalog dari Pricelist Approved
     */
    public function release()
    {
        $this->auth->restrict($this->managePermission);

        $id_pricelist = (int)$this->input->post('id_pricelist');
        if (!$id_pricelist) {
            echo json_encode(['status' => 0, 'pesan' => 'Select an approved Pricelist document']);
            return;
        }

        $no_cat = $this->pc_model->release_catalog_from_pricelist($id_pricelist, $this->auth->user_id());
        if ($no_cat) {
            write_log('Product Catalog', 'Release', "Merilis Product Catalog baru {$no_cat} dari Pricelist ID {$id_pricelist}", null, null, 1);
            echo json_encode([
                'status' => 1,
                'pesan'  => "Product Catalog {$no_cat} released successfully as the active official catalog snapshot.",
            ]);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Failed to release Product Catalog']);
        }
    }

    /**
     * Ekspor Daftar Katalog ke Excel / CSV
     * Memerlukan hak Product_Catalog.Manage (NFR-17)
     */
    public function export_excel()
    {
        $this->auth->restrict($this->managePermission);

        $category   = $this->input->get('category');
        $catalog_id = $this->input->get('catalog_id');
        $status     = $this->input->get('status') !== null ? $this->input->get('status') : 'Active';
        $date_from  = $this->input->get('date_from');
        $date_to    = $this->input->get('date_to');

        $items = $this->pc_model->get_catalog_items($category, $catalog_id, $status, $date_from, $date_to);

        $company_code = preg_replace('/[^A-Za-z0-9_-]/', '_', get_company_code());
        $filename = 'Product_Catalog_' . $company_code . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        // BOM UTF-8 for Excel Indonesian character support
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header CSV
        fputcsv($output, [
            'No',
            'Product Name',
            'Product Code (Lv4)',
            'No Uniq ID (Varian)',
            'Product Category',
            'Size (m)',
            'Weight / Meter (Kg/m)',
            'Price List / Meter (Rp)',
            'Price List / Kg (Rp)',
            'Price List / Pcs (Rp)',
            'No Catalog',
            'Status Catalog',
            'Release Date',
            'Pricelist Asal',
        ]);

        $no = 1;
        foreach ($items as $item) {
            fputcsv($output, [
                $no++,
                $item->nm_product,
                $item->code_lv4,
                $item->no_uniq_id,
                $item->nm_category,
                number_format((float)$item->ukuran_product, 2),
                number_format((float)$item->berat_per_meter, 4),
                number_format((float)$item->price_list_meter, 2, '.', ''),
                number_format((float)$item->price_list_kg, 2, '.', ''),
                number_format((float)$item->price_list_pcs, 2, '.', ''),
                $item->no_catalog,
                $item->catalog_status,
                $item->released_at,
                $item->no_pricelist ? $item->no_pricelist : '-',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Cetak Tampilan Resmi Katalog untuk Sales / Dokumen Fisik
     */
    public function print_catalog($catalog_id = null)
    {
        $this->auth->restrict($this->viewPermission);

        $category  = $this->input->get('category');
        $status    = $this->input->get('status') !== null ? $this->input->get('status') : 'Active';
        $date_from = $this->input->get('date_from');
        $date_to   = $this->input->get('date_to');

        $items   = $this->pc_model->get_catalog_items($category, $catalog_id, $status, $date_from, $date_to);
        $catalog = $catalog_id ? $this->pc_model->get_catalog_by_id($catalog_id) : null;

        $data = [
            'items'    => $items,
            'catalog'  => $catalog,
            'category' => $category,
            'date'     => date('d F Y'),
        ];

        $this->load->view('product_catalog/print', $data);
    }
}
