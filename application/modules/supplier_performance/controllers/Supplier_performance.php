<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Supplier_performance extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Supplier_performance/Supplier_performance_model');
        $this->template->title('Kinerja Supplier');
        $this->template->page_icon('fa fa-chart-bar');
    }

    // =========================================================================
    // Pages
    // =========================================================================

    /**
     * Halaman summary periodik dengan filter supplier & periode
     */
    public function index()
    {
        $supplier_list = $this->Supplier_performance_model->get_supplier_list();
        $this->template->set(['supplier_list' => $supplier_list]);
        $this->template->render('index');
    }

    /**
     * Halaman feed per coil
     */
    public function feed_coil()
    {
        $supplier_list = $this->Supplier_performance_model->get_supplier_list();
        $this->template->set(['supplier_list' => $supplier_list]);
        $this->template->render('feed_coil');
    }

    /**
     * Halaman dashboard perbandingan antar supplier
     */
    public function dashboard()
    {
        $dashboard_data = $this->Supplier_performance_model->get_dashboard_data();
        $this->template->set(['dashboard_data' => $dashboard_data]);
        $this->template->render('dashboard');
    }

    // =========================================================================
    // JSON Endpoints
    // =========================================================================

    /**
     * DataTables server-side endpoint untuk feed per coil
     */
    public function data_side_feed()
    {
        $params = $_REQUEST;
        $result = $this->Supplier_performance_model->get_feed_datatable($params);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $data[] = [
                $i + 1 + (int) $params['start'],
                $row->tgl_feed,
                $row->report_no,
                $row->no_coil,
                $row->nm_supplier ?: '-',
                number_format((float) $row->selisih_gross, 3),
                number_format((float) $row->selisih_net, 3),
                number_format((float) $row->reject_supplier_kg, 3),
                number_format((float) $row->ng_supplier_kg, 3),
                number_format((float) $row->kw2_supplier_kg, 3),
            ];
        }

        echo json_encode([
            'draw'            => (int) $params['draw'],
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }

    /**
     * DataTables server-side endpoint untuk summary periodik
     */
    public function data_side_summary()
    {
        $params = $_REQUEST;
        $result = $this->Supplier_performance_model->get_summary_datatable($params);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $data[] = [
                $i + 1 + (int) $params['start'],
                $row->nm_supplier ?: '-',
                (int) $row->jumlah_coil,
                number_format((float) $row->total_reject_kg, 3),
                number_format((float) $row->total_ng_kg, 3),
                number_format((float) $row->total_kw2_kg, 3),
                number_format((float) $row->total_defect_kg, 3),
                number_format((float) $row->avg_selisih_net, 3),
            ];
        }

        echo json_encode([
            'draw'            => (int) $params['draw'],
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }
}
