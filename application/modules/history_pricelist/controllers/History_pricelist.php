<?php
defined('BASEPATH') or exit('No direct script access allowed');

class History_pricelist extends Admin_Controller
{
    protected $viewPermission   = 'History_Pricelist.View';
    protected $managePermission = 'History_Pricelist.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('history_pricelist/History_pricelist_model', 'hp_model');
        $this->template->title('History Price List');
        $this->template->page_icon('fa fa-clock-o');
    }

    /**
     * Tampilan Riwayat Persetujuan Price List
     * Hanya Baca (Read-Only) sesuai NFR-17
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $filters = [
            'code_lv2'     => $this->input->get('category'),
            'no_pricelist' => $this->input->get('no_pricelist'),
            'start_date'   => $this->input->get('start_date'),
            'end_date'     => $this->input->get('end_date'),
        ];

        $list       = $this->hp_model->get_history($filters);
        $categories = $this->hp_model->get_categories();
        $pricelists = $this->hp_model->get_pricelists();

        $this->template->set([
            'list'       => $list,
            'categories' => $categories,
            'pricelists' => $pricelists,
            'filters'    => $filters,
        ]);
        $this->template->render('index');
    }

    /**
     * Ekspor Data Riwayat Pricelist ke Excel / CSV
     * Memerlukan hak History_Pricelist.Manage (NFR-17)
     */
    public function export_excel()
    {
        $this->auth->restrict($this->managePermission);

        $filters = [
            'code_lv2'     => $this->input->get('category'),
            'no_pricelist' => $this->input->get('no_pricelist'),
            'start_date'   => $this->input->get('start_date'),
            'end_date'     => $this->input->get('end_date'),
        ];

        $list = $this->hp_model->get_history($filters);

        $filename = 'History_Pricelist_HFG_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        // BOM UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'No',
            'Tanggal Disetujui',
            'No Dokumen Pricelist',
            'Nama Produk',
            'Kode Produk (Lv4)',
            'No Uniq ID (Varian)',
            'Kategori Produk',
            'Price List Lama / Meter (Rp)',
            'Price List Baru / Meter (Rp)',
            'Price List Baru / Kg (Rp)',
            'Selisih / Delta (Rp)',
            'Bottom Price / Meter (Rp)',
            'Extra Margin (%)',
            'Disetujui Oleh (Approver)',
            'Catatan',
        ]);

        $no = 1;
        foreach ($list as $r) {
            $delta = (float)$r->price_list_baru_meter - (float)$r->price_list_lama_meter;
            fputcsv($output, [
                $no++,
                $r->event_at,
                $r->no_pricelist,
                $r->nm_product ? $r->nm_product : $r->code_lv4,
                $r->code_lv4,
                $r->no_uniq_id,
                $r->nm_category ? $r->nm_category : '-',
                number_format((float)$r->price_list_lama_meter, 2, '.', ''),
                number_format((float)$r->price_list_baru_meter, 2, '.', ''),
                number_format((float)$r->price_list_baru_kg, 2, '.', ''),
                number_format($delta, 2, '.', ''),
                number_format((float)$r->bottom_price_meter, 2, '.', ''),
                number_format((float)$r->rate_negotiation_allowance * 100, 2, '.', ''),
                $r->event_by_name ? $r->event_by_name : '-',
                $r->catatan,
            ]);
        }

        fclose($output);
        exit;
    }
}
