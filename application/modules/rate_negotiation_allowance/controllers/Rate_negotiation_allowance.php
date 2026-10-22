<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rate_negotiation_allowance extends Admin_Controller
{
    protected $viewPermission       = 'Rate_Negotiation_Allowance.View';
    protected $managePermission     = 'Rate_Negotiation_Allowance.Manage';
    protected $viewAppPermission    = 'Approval_Extra_Margin.View';
    protected $manageAppPermission  = 'Approval_Extra_Margin.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('rate_negotiation_allowance/Rate_negotiation_allowance_model', 'rna_model');
        $this->template->title('Negotiation Allowance (Extra Margin)');
        $this->template->page_icon('fa fa-tag');
    }

    /** View Index: Negotiation Allowance per Product Category */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $list          = $this->rna_model->get_list_with_requests();
        $pending_count = $this->rna_model->count_pending_requests();

        $this->template->set([
            'list'          => $list,
            'pending_count' => $pending_count,
        ]);
        $this->template->render('index');
    }

    /** Ajukan Perubahan Rate Extra Margin (Sales Manager) */
    public function save_request()
    {
        $this->auth->restrict($this->managePermission);

        $code_lv2 = trim($this->input->post('code_lv2'));
        $rate_pct = (float) $this->input->post('rate_negotiation_allowance');

        if (empty($code_lv2)) {
            echo json_encode(['status' => 0, 'pesan' => 'Product Category wajib dipilih']);
            return;
        }

        if ($rate_pct < 0 || $rate_pct > 100) {
            echo json_encode(['status' => 0, 'pesan' => 'Rate Extra Margin harus antara 0% dan 100%']);
            return;
        }

        // Cek apakah sedang ada pengajuan berstatus 'Menunggu Approval GM'
        $existing = $this->db->where('code_lv2', $code_lv2)
            ->where('status', 'Menunggu Approval GM')
            ->get('rate_negotiation_allowance_request')
            ->row();

        if ($existing) {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Masih ada pengajuan perubahan untuk kategori ini yang menunggu keputusan GM.',
            ]);
            return;
        }

        $db_rate = $rate_pct / 100;
        $save = $this->rna_model->submit_request($code_lv2, $db_rate, $this->auth->user_id());

        if ($save) {
            $cat = $this->rna_model->get_by_code($code_lv2);
            $cat_name = $cat ? $cat->nm_category : $code_lv2;

            write_log('Negotiation Allowance', 'Request', 'Pengajuan perubahan Extra Margin: ' . $cat_name . ' (' . number_format($rate_pct, 2, ',', '.') . '%)', [
                'code_lv2' => $code_lv2,
                'rate_pct' => $rate_pct,
            ], null, 1);

            echo json_encode([
                'status' => 1,
                'pesan'  => 'Pengajuan perubahan Extra Margin untuk ' . $cat_name . ' berhasil dikirim ke GM.',
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Gagal mengirim pengajuan perubahan rate.',
            ]);
        }
    }

    /** View Approval GM */
    public function approval()
    {
        $this->auth->restrict($this->viewAppPermission);

        $status_filter = $this->input->get('status');
        $requests      = $this->rna_model->get_requests($status_filter);
        $pending_count = $this->rna_model->count_pending_requests();

        $this->template->title('Approval Extra Margin');
        $this->template->page_icon('fa fa-check-square-o');
        $this->template->set([
            'requests'      => $requests,
            'pending_count' => $pending_count,
            'status_filter' => $status_filter,
        ]);
        $this->template->render('approval');
    }

    /** GM Menyetujui Pengajuan */
    public function approve()
    {
        $this->auth->restrict($this->manageAppPermission);

        $id = (int) $this->input->post('id');
        if (empty($id)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID pengajuan tidak valid']);
            return;
        }

        $req = $this->rna_model->get_request_by_id($id);
        if (!$req || $req->status !== 'Menunggu Approval GM') {
            echo json_encode(['status' => 0, 'pesan' => 'Pengajuan sudah tidak dalam status menunggu']);
            return;
        }

        $ok = $this->rna_model->approve_request($id, $this->auth->user_id());
        if ($ok) {
            $rate_pct = number_format($req->rate_negotiation_allowance * 100, 2, ',', '.') . ' %';
            write_log('Approval Extra Margin', 'Approve', 'GM menyetujui pengajuan Extra Margin ' . $req->nm_category . ' (' . $rate_pct . ')', (array)$req, null, 1);

            echo json_encode([
                'status' => 1,
                'pesan'  => 'Pengajuan Extra Margin ' . $req->nm_category . ' berhasil disetujui (Approved). Rate aktif telah diperbarui.',
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Gagal memproses persetujuan pengajuan.',
            ]);
        }
    }

    /** GM Menolak Pengajuan */
    public function reject()
    {
        $this->auth->restrict($this->manageAppPermission);

        $id     = (int) $this->input->post('id');
        $reason = trim($this->input->post('reject_reason'));

        if (empty($id)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID pengajuan tidak valid']);
            return;
        }

        if (empty($reason)) {
            echo json_encode(['status' => 0, 'pesan' => 'Alasan Reject wajib diisi']);
            return;
        }

        $req = $this->rna_model->get_request_by_id($id);
        if (!$req || $req->status !== 'Menunggu Approval GM') {
            echo json_encode(['status' => 0, 'pesan' => 'Pengajuan sudah tidak dalam status menunggu']);
            return;
        }

        $ok = $this->rna_model->reject_request($id, $reason, $this->auth->user_id());
        if ($ok) {
            write_log('Approval Extra Margin', 'Reject', 'GM menolak pengajuan Extra Margin ' . $req->nm_category . ' dengan alasan: ' . $reason, (array)$req, null, 1);

            echo json_encode([
                'status' => 1,
                'pesan'  => 'Pengajuan Extra Margin ' . $req->nm_category . ' berhasil ditolak (Rejected). Alasan telah disimpan.',
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'pesan'  => 'Gagal memproses penolakan pengajuan.',
            ]);
        }
    }
}
