<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bottom_price_notification extends Admin_Controller
{
    protected $viewPermission   = 'Bottom_Price_Notification.View';
    protected $managePermission = 'Bottom_Price_Notification.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('bottom_price_notification/Bottom_price_notification_model', 'bpn_model');
        $this->template->title('Notifikasi Perubahan Bottom Price');
        $this->template->page_icon('fa fa-bell');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $status_filter = $this->input->get('status');
        $list          = $this->bpn_model->get_list($status_filter);
        $counts        = $this->bpn_model->get_counts();

        $this->template->set([
            'list'          => $list,
            'counts'        => $counts,
            'status_filter' => $status_filter,
        ]);
        $this->template->render('index');
    }

    /** Keputusan: Tetap Pakai Rate Aktif (Tidak Perlu Diubah) */
    public function keep()
    {
        $this->auth->restrict($this->managePermission);

        $id      = (int) $this->input->post('id');
        $catatan = trim($this->input->post('catatan'));

        if (empty($id)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID notifikasi tidak valid']);
            return;
        }

        $ok = $this->bpn_model->keep_rate($id, $catatan, $this->auth->user_id());
        if ($ok) {
            write_log('Notifikasi Bottom Price', 'Keep', "Keputusan: Rate Extra Margin tetap dipakai untuk notifikasi #{$id}", ['catatan' => $catatan], null, 1);
            echo json_encode([
                'status' => 1,
                'pesan'  => 'Keputusan tersimpan. Rate Extra Margin aktif tetap dipakai.',
            ]);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menyimpan keputusan.']);
        }
    }

    /** Ajukan Perubahan Extra Margin Baru dari Notifikasi */
    public function submit_extra_margin()
    {
        $this->auth->restrict($this->managePermission);

        $id       = (int) $this->input->post('id');
        $rate_pct = (float) $this->input->post('rate_pct');

        if (empty($id)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID notifikasi tidak valid']);
            return;
        }

        if ($rate_pct < 0 || $rate_pct > 100) {
            echo json_encode(['status' => 0, 'pesan' => 'Rate Extra Margin harus antara 0% dan 100%']);
            return;
        }

        $notif = $this->db->where('id', $id)->get('bottom_price_notification')->row();
        if (!$notif) {
            echo json_encode(['status' => 0, 'pesan' => 'Data notifikasi tidak ditemukan']);
            return;
        }

        // Simpan request ke rate_negotiation_allowance_request
        $this->db->insert('rate_negotiation_allowance_request', [
            'code_lv2'                   => $notif->code_lv2,
            'rate_negotiation_allowance' => $rate_pct / 100,
            'status'                     => 'Menunggu Approval GM',
            'submitted_by'               => $this->auth->user_id(),
            'submitted_at'               => date('Y-m-d H:i:s'),
        ]);
        $req_id = $this->db->insert_id();

        // Tautkan notifikasi
        $this->bpn_model->link_request($id, $req_id, $this->auth->user_id());

        write_log('Notifikasi Bottom Price', 'Ajukan Nego', "Pengajuan Extra Margin baru ({$rate_pct}%) dari notifikasi #{$id}", [
            'id_notif' => $id,
            'req_id'   => $req_id,
            'rate_pct' => $rate_pct,
        ], null, 1);

        echo json_encode([
            'status' => 1,
            'pesan'  => 'Pengajuan perubahan Extra Margin berhasil dikirim ke antrean Approval GM.',
        ]);
    }
}
