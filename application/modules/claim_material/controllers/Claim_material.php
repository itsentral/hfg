<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Claim_material extends Admin_Controller
{
    protected $viewPermission   = 'Claim_Material.View';
    protected $managePermission = 'Claim_Material.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            'claim_material/Claim_material_model' => 'cm_model'
        ]);
        $this->template->title('Claim Material');
        $this->template->page_icon('fa fa-clipboard-check');
    }

    /**
     * Halaman Utama: Daftar Claim Material (FR-76..FR-82)
     */
    public function index()
    {
        if (!has_permission($this->viewPermission) && !has_permission('Daftar_Claim_Material.View')) {
            $this->auth->restrict($this->viewPermission);
        }
        $suppliers = $this->cm_model->get_suppliers_list();
        $stats     = $this->cm_model->get_summary_stats();

        $this->template->set('suppliers', $suppliers);
        $this->template->set('stats', $stats);
        $this->template->render('index');
    }

    /**
     * Endpoint AJAX Daftar Claim Material dengan filter
     */
    public function ajax_claim_list()
    {
        $supplier = $this->input->get('supplier', true);
        $asal     = $this->input->get('asal', true);
        $q        = $this->input->get('q', true);

        $claims = $this->cm_model->get_claims($supplier, $asal, $q);
        $stats  = $this->cm_model->get_summary_stats();

        echo json_encode([
            'status' => true,
            'data'   => $claims,
            'stats'  => $stats,
        ]);
    }

    /**
     * Endpoint AJAX Detail Claim
     */
    public function ajax_claim_detail($id)
    {
        $claim = $this->cm_model->get_claim_detail($id);
        if (!$claim) {
            echo json_encode(['status' => false, 'message' => 'Claim tidak ditemukan']);
            return;
        }

        echo json_encode([
            'status' => true,
            'data'   => $claim
        ]);
    }

    /**
     * Endpoint AJAX Multiple Claims untuk Process Decision bulk
     */
    public function ajax_claims_multi()
    {
        $ids = $this->input->post('ids');
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['status' => false, 'message' => 'Tidak ada claim yang dipilih']);
            return;
        }

        $claims = $this->cm_model->get_claims_by_ids($ids);
        echo json_encode([
            'status' => true,
            'data'   => $claims
        ]);
    }

    /**
     * Endpoint AJAX Kirim Email Keluhan ke Supplier (FR-77)
     */
    public function send_email()
    {
        if (!has_permission($this->managePermission) && !has_permission('Daftar_Claim_Material.Manage')) {
            $this->auth->restrict($this->managePermission);
        }

        $claim_id      = (int)$this->input->post('claim_id');
        $email_to      = trim($this->input->post('email_to', true));
        $email_subject = trim($this->input->post('email_subject', true));
        $email_body    = trim($this->input->post('email_body'));

        if (empty($claim_id) || empty($email_to)) {
            echo json_encode(['status' => false, 'message' => 'ID Claim dan Email Supplier wajib diisi']);
            return;
        }

        $user_name = $this->auth->user_name();
        $result = $this->cm_model->send_email_complaint($claim_id, $email_to, $email_subject, $email_body, $user_name);
        echo json_encode($result);
    }

    /**
     * Endpoint AJAX Simpan Process Decision (FR-78)
     */
    public function save_decision()
    {
        if (!has_permission($this->managePermission) && !has_permission('Daftar_Claim_Material.Manage')) {
            $this->auth->restrict($this->managePermission);
        }

        $claim_ids   = $this->input->post('claim_ids');
        $kesepakatan = trim($this->input->post('kesepakatan', true));
        $decisions   = $this->input->post('decisions'); // array: [claim_id => [ [jalur, aksi, berat_kg, ...], ... ]]
        $kurs        = $this->input->post('kurs'); // array: [claim_id => float]

        if (empty($claim_ids) || !is_array($claim_ids)) {
            echo json_encode(['status' => false, 'message' => 'Pilih minimal satu claim']);
            return;
        }

        if (empty($kesepakatan)) {
            echo json_encode(['status' => false, 'message' => 'Kesepakatan dengan Supplier wajib dipilih']);
            return;
        }

        // Validasi berat per claim
        foreach ($claim_ids as $cid) {
            $claim = $this->cm_model->get_claim_detail($cid);
            if (!$claim) {
                echo json_encode(['status' => false, 'message' => 'Claim ID ' . $cid . ' tidak valid']);
                return;
            }

            $claim_dec = $decisions[$cid] ?? [];
            if (empty($claim_dec)) {
                echo json_encode(['status' => false, 'message' => 'Tentukan jalur keputusan untuk claim ' . $claim['no_claim']]);
                return;
            }

            $total_kg = 0;
            foreach ($claim_dec as $d) {
                $kg = (float)($d['berat_kg'] ?? 0);
                if ($kg <= 0) {
                    echo json_encode(['status' => false, 'message' => 'Berat (Kg) pada claim ' . $claim['no_claim'] . ' harus lebih dari 0']);
                    return;
                }
                $total_kg += $kg;
            }

            if ($total_kg > (float)$claim['berat_kg']) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Total berat keputusan (' . number_format($total_kg, 2) . ' Kg) melebihi berat claim ' . $claim['no_claim'] . ' (' . number_format((float)$claim['berat_kg'], 2) . ' Kg)'
                ]);
                return;
            }
        }

        $user_name = $this->auth->user_name();
        $result = $this->cm_model->save_process_decision($claim_ids, $kesepakatan, $decisions, $kurs, $user_name);
        echo json_encode($result);
    }

    /**
     * Halaman Material On Hold (FR-75, UC-N35)
     * Sekarang disatukan langsung ke tab On Hold pada modul stock_from_warehouse
     */
    public function on_hold()
    {
        redirect('stock_from_warehouse#tab-on-hold');
    }

    /**
     * Endpoint AJAX List Material On Hold
     */
    public function ajax_on_hold_list()
    {
        $data = $this->cm_model->get_on_hold_materials();
        echo json_encode([
            'status' => true,
            'data'   => $data,
        ]);
    }

    /**
     * Halaman Material Dijual - Gudang Slitting (FR-81, FR-96)
     */
    public function slitting()
    {
        if (!has_permission($this->viewPermission) && !has_permission('Material_Dijual_Slitting.View')) {
            $this->auth->restrict($this->viewPermission);
        }
        $this->template->title('Material Dijual — Gudang Slitting');
        $this->template->set('page_header', 'Material Dijual (Gudang Slitting)');
        $this->template->render('slitting');
    }

    /**
     * Endpoint AJAX List Material Dijual Slitting
     */
    public function ajax_slitting_list()
    {
        $data = $this->cm_model->get_slitting_materials();
        echo json_encode([
            'status' => true,
            'data'   => $data,
        ]);
    }

    /**
     * Halaman Report Biaya Kompensasi vs Biaya Scrap (FR-87)
     */
    public function report_biaya()
    {
        if (!has_permission($this->viewPermission) && !has_permission('Report_Biaya_Kompensasi.View')) {
            $this->auth->restrict($this->viewPermission);
        }
        $report_data = $this->cm_model->get_report_biaya();

        $this->template->title('Report Biaya Kompensasi vs Biaya Scrap');
        $this->template->set('report_data', $report_data);
        $this->template->render('report_biaya');
    }

    /**
     * Endpoint AJAX Jurnal Transaksi Claim (FR-93)
     */
    public function ajax_jurnal($id)
    {
        $claim = $this->cm_model->get_claim_detail($id);
        if (!$claim) {
            echo json_encode(['status' => false, 'message' => 'Claim tidak ditemukan']);
            return;
        }

        $journals = $this->cm_model->get_claim_journals($id);
        echo json_encode([
            'status'   => true,
            'claim'    => $claim,
            'journals' => $journals,
        ]);
    }
}

