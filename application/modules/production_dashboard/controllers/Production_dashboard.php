<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Production_dashboard extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Production_dashboard/Production_dashboard_model');
        $this->template->title('Dashboard Produksi');
        $this->template->page_icon('fa fa-tachometer-alt');
    }

    // =========================================================================
    // Pages
    // =========================================================================

    /**
     * Dashboard utama dengan ringkasan data
     */
    public function index()
    {
        $summary = $this->Production_dashboard_model->get_dashboard_summary();
        $this->template->set(['summary' => $summary]);
        $this->template->render('index');
    }

    /**
     * Laporan perbandingan timbang awal vs packing list
     */
    public function laporan_timbang_awal()
    {
        $spk_no    = $this->input->get('spk_no');
        $tgl_dari  = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        $data_laporan = $this->Production_dashboard_model->get_laporan_timbang_awal(
            $spk_no, $tgl_dari, $tgl_sampai
        );
        $spk_list = $this->Production_dashboard_model->get_spk_list();

        $this->template->set([
            'data_laporan' => $data_laporan,
            'spk_list'     => $spk_list,
            'filter'       => [
                'spk_no'    => $spk_no,
                'tgl_dari'  => $tgl_dari,
                'tgl_sampai' => $tgl_sampai,
            ],
        ]);
        $this->template->render('laporan_timbang_awal');
    }

    /**
     * Laporan hasil produksi per SPK dengan yield breakdown
     */
    public function laporan_hasil_produksi()
    {
        $spk_no    = $this->input->get('spk_no');
        $tgl_dari  = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        $data_laporan = $this->Production_dashboard_model->get_laporan_hasil_produksi(
            $spk_no, $tgl_dari, $tgl_sampai
        );
        $spk_list = $this->Production_dashboard_model->get_spk_list();

        $this->template->set([
            'data_laporan' => $data_laporan,
            'spk_list'     => $spk_list,
            'filter'       => [
                'spk_no'    => $spk_no,
                'tgl_dari'  => $tgl_dari,
                'tgl_sampai' => $tgl_sampai,
            ],
        ]);
        $this->template->render('laporan_hasil_produksi');
    }

    /**
     * Laporan selisih berat delivery (estimasi vs aktual per DO)
     */
    public function laporan_delivery_discrepancy()
    {
        $tgl_dari  = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        $data_laporan = $this->Production_dashboard_model->get_laporan_delivery_discrepancy(
            $tgl_dari, $tgl_sampai
        );

        $this->template->set([
            'data_laporan' => $data_laporan,
            'filter'       => [
                'tgl_dari'  => $tgl_dari,
                'tgl_sampai' => $tgl_sampai,
            ],
        ]);
        $this->template->render('laporan_delivery_discrepancy');
    }

    /**
     * Laporan berat standar vs aktual FG per periode
     */
    public function laporan_berat_fg()
    {
        $tgl_dari  = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        $data_laporan = $this->Production_dashboard_model->get_laporan_berat_fg(
            $tgl_dari, $tgl_sampai
        );

        $this->template->set([
            'data_laporan' => $data_laporan,
            'filter'       => [
                'tgl_dari'  => $tgl_dari,
                'tgl_sampai' => $tgl_sampai,
            ],
        ]);
        $this->template->render('laporan_berat_fg');
    }
}
