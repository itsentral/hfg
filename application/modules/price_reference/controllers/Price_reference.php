<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Price_reference extends Admin_Controller
{
    protected $viewPermission   = 'Price_Reference.View';
    protected $managePermission = 'Price_Reference.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('price_reference/Price_reference_model', 'model_pr');
        $this->template->title('Price Reference');
        $this->template->page_icon('fa fa-tags');

        date_default_timezone_set('Asia/Bangkok');
        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    /**
     * Cek izin akses dengan fallback ke permission legacy Price_Ref_Raw_Material
     */
    private function check_perm($perm)
    {
        if (has_permission($perm)) {
            return true;
        }
        if ($perm === $this->viewPermission && has_permission('Price_Ref_Raw_Material.View')) {
            return true;
        }
        if ($perm === $this->managePermission && (has_permission('Price_Ref_Raw_Material.Manage') || has_permission('Price_Ref_Raw_Material.Add'))) {
            return true;
        }
        return false;
    }

    /**
     * Tampilan Depan: Daftar Material untuk Product Costing Price Reference (Read-Only)
     */
    public function index()
    {
        if (!$this->check_perm($this->viewPermission)) {
            $this->auth->restrict($this->viewPermission);
        }

        $materials = $this->model_pr->get_materials();
        $kurs_info = $this->model_pr->get_usd_kurs();

        $data = [
            'materials'     => $materials,
            'usd_kurs'      => $kurs_info['kurs'],
            'id_kurs'       => $kurs_info['id_kurs'],
            'ENABLE_VIEW'   => $this->check_perm($this->viewPermission),
        ];

        $this->template->set($data);
        $this->template->render('index');
    }

    /**
     * AJAX: Ambil detail data material, history_purchase, riwayat PO, dan kalkulasi untuk modal View
     */
    public function get_detail($id)
    {
        if (!$this->check_perm($this->viewPermission)) {
            echo json_encode(['status' => 0, 'pesan' => 'Akses ditolak']);
            return;
        }

        $material = $this->model_pr->get_material_by_id($id);
        if (!$material) {
            echo json_encode(['status' => 0, 'pesan' => 'Material tidak ditemukan']);
            return;
        }

        $kurs_info  = $this->model_pr->get_usd_kurs();
        $history_db = $this->model_pr->get_monthly_history($id, $material->code_lv4);
        $pr_detail  = $this->model_pr->get_price_reference_detail($id, $material->code_lv4);
        $po_history = $this->model_pr->get_po_history($id, $material->code_lv4);

        // Siapkan array 12 bulan (Jan-Des)
        $months = [];
        $numeric_vals = [];
        for ($m = 1; $m <= 12; $m++) {
            $val = isset($history_db[$m]) ? (float)$history_db[$m] : null;
            $months[$m] = $val;
            if ($val !== null && $val > 0) {
                $numeric_vals[] = $val;
            }
        }

        // Jika riwayat bulanan di history_purchase masih kosong, cek apakah material sudah punya price_ref_use_usd
        if (empty($numeric_vals)) {
            $existing_usd = 0;
            if ($pr_detail && (float)$pr_detail->max_price_reference > 0) {
                $existing_usd = (float)$pr_detail->max_price_reference;
            } elseif ((float)$material->price_ref_use_usd > 0) {
                $existing_usd = (float)$material->price_ref_use_usd;
            }

            if ($existing_usd > 0) {
                $current_month = min(12, (int)date('n'));
                $months[$current_month] = $existing_usd;
                $numeric_vals[] = $existing_usd;
            }
        }

        // Hitung Max, Latest, Avg
        $max_usd = !empty($numeric_vals) ? max($numeric_vals) : 0;
        $latest_usd = !empty($numeric_vals) ? end($numeric_vals) : 0;
        $avg_usd = !empty($numeric_vals) ? (array_sum($numeric_vals) / count($numeric_vals)) : 0;

        $calc_kurs = ($material->kurs && (float)$material->kurs > 0) ? (float)$material->kurs : $kurs_info['kurs'];
        $calc_id_kurs = ($material->id_kurs && (int)$material->id_kurs > 0) ? (int)$material->id_kurs : $kurs_info['id_kurs'];
        $price_ref_idr = $max_usd * $calc_kurs;

        // Hitung status tanpa tgl expired
        $status_label = ($max_usd > 0 || (float)$material->price_ref_use > 0) ? 'Oke' : 'Not Set';
        $status_badge = ($status_label === 'Oke') ? 'success' : 'warning';

        echo json_encode([
            'status'     => 1,
            'material'   => [
                'id'           => $material->id,
                'code_lv4'     => $material->code_lv4,
                'code'         => $material->code ?: $material->code_lv4,
                'nama'         => strtoupper($material->nama),
                'status_label' => $status_label,
                'status_badge' => $status_badge,
            ],
            'history'    => $months,
            'po_history' => $po_history,
            'calc'       => [
                'max_usd'       => $max_usd,
                'latest_usd'    => $latest_usd,
                'avg_usd'       => $avg_usd,
                'id_kurs'       => $calc_id_kurs,
                'kurs'          => $calc_kurs,
                'price_ref_idr' => $price_ref_idr,
            ]
        ]);
    }
}
