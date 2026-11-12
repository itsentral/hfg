<?php
defined('BASEPATH') or exit('No direct script access allowed');

class History_costing extends Admin_Controller
{
    protected $viewPermission   = 'History_Costing.View';
    protected $managePermission = 'History_Costing.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('history_costing/History_costing_model', 'hc_model');
        $this->template->title('History Costing');
        $this->template->page_icon('fa fa-history');
    }

    /**
     * Tampilan Riwayat Perubahan Costing & Bottom Price
     * Hanya Baca (Read-only) sesuai NFR-17
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $filters = [
            'code_lv2'   => $this->input->get('category'),
            'batch_id'   => $this->input->get('batch_id'),
            'start_date' => $this->input->get('start_date'),
            'end_date'   => $this->input->get('end_date'),
        ];

        $list       = $this->hc_model->get_history($filters);
        $categories = $this->hc_model->get_categories();
        $batches    = $this->hc_model->get_batches();

        $this->template->set([
            'list'       => $list,
            'categories' => $categories,
            'batches'    => $batches,
            'filters'    => $filters,
        ]);
        $this->template->render('index');
    }

    /**
     * Ambil Detail Snapshot Komponen Biaya (AJAX)
     * Membandingkan Komponen Lama vs Baru
     */
    public function detail_snapshot()
    {
        $this->auth->restrict($this->viewPermission);

        $id = (int)$this->input->get('id');
        $row = $this->hc_model->get_history_by_id($id);

        if (!$row) {
            echo json_encode(['status' => 0, 'pesan' => 'Riwayat costing tidak ditemukan']);
            return;
        }

        $snapshot = json_decode($row->detail_komponen, true);

        echo json_encode([
            'status'   => 1,
            'row'      => $row,
            'snapshot' => $snapshot,
        ]);
    }

    /**
     * Ekspor Data Riwayat Costing ke Excel / CSV
     * Memerlukan hak History_Costing.Manage (NFR-17)
     */
    public function export_excel()
    {
        $this->auth->restrict($this->managePermission);

        $filters = [
            'code_lv2'   => $this->input->get('category'),
            'batch_id'   => $this->input->get('batch_id'),
            'start_date' => $this->input->get('start_date'),
            'end_date'   => $this->input->get('end_date'),
        ];

        $list = $this->hc_model->get_history($filters);

        $filename = 'History_Costing_HFG_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        // BOM UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'No',
            'Batch ID',
            'Waktu Perubahan',
            'Nama Produk',
            'Kode Produk (Lv4)',
            'No Uniq ID',
            'Kategori Produk',
            'Sumber Perubahan',
            'Bottom Price Lama / Meter (Rp)',
            'Bottom Price Baru / Meter (Rp)',
            'Selisih / Delta (Rp)',
            'Price List Baru / Meter (Rp)',
            'Extra Margin (%)',
            'Diperbarui Oleh',
        ]);

        $no = 1;
        foreach ($list as $r) {
            $delta = (float)$r->bottom_price_baru_meter - (float)$r->bottom_price_lama_meter;
            fputcsv($output, [
                $no++,
                $r->batch_id,
                $r->changed_at,
                $r->nm_product ? $r->nm_product : $r->code_lv4,
                $r->code_lv4,
                $r->no_uniq_id,
                $r->nm_category ? $r->nm_category : '-',
                $r->change_source,
                number_format((float)$r->bottom_price_lama_meter, 2, '.', ''),
                number_format((float)$r->bottom_price_baru_meter, 2, '.', ''),
                number_format($delta, 2, '.', ''),
                number_format((float)$r->price_list_baru_meter, 2, '.', ''),
                number_format((float)$r->rate_negotiation_allowance * 100, 2, '.', ''),
                $r->changed_by_name ? $r->changed_by_name : '-',
            ]);
        }

        fclose($output);
        exit;
    }
}
