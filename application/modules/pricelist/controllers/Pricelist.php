<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pricelist extends Admin_Controller
{
    protected $viewPermission       = 'Pricelist.View';
    protected $managePermission     = 'Pricelist.Manage';
    protected $deletePermission     = 'Pricelist.Delete';
    protected $viewAppPermission    = 'Approval_Pricelist.View';
    protected $manageAppPermission  = 'Approval_Pricelist.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('pricelist/Pricelist_model', 'pl_model');
        $this->template->title('Pricelist');
        $this->template->page_icon('fa fa-list-alt');
    }

        /** Daftar Dokumen Pricelist (Sales) - Hanya render Wadah Tab */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $status_filter = $this->input->get('status');
        $counts        = $this->pl_model->get_counts();

        $this->template->set([
            'counts'        => $counts,
            'status_filter' => $status_filter,
        ]);
        $this->template->render('index');
    }

    /** Menu Approval Pricelist (Management / GM) - Hanya render Wadah Tab */
    public function approval()
    {
        $this->auth->restrict($this->viewAppPermission);

        $status_filter = $this->input->get('status');
        $counts        = $this->pl_model->get_counts();

        $this->template->title('Approval Pricelist');
        $this->template->page_icon('fa fa-check-circle');
        $this->template->set([
            'counts'        => $counts,
            'status_filter' => $status_filter,
        ]);
        $this->template->render('approval');
    }

    // -------------------------------------------------------------------------
    // RENDER PARTIAL TABLE PER TAB VIA AJAX (SALES INDEX)
    // -------------------------------------------------------------------------

    public function render_tab($tab = 'all')
    {
        $this->auth->restrict($this->viewPermission);

        $status_map = [
            'all'      => null,
            'draft'    => 'Draft',
            'pending'  => 'Menunggu Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];

        $status           = isset($status_map[$tab]) ? $status_map[$tab] : null;
        $data['list']     = $this->pl_model->get_pricelists($status);
        $data['tab']      = $tab;
        $data['table_id'] = 'tbl-pl-' . $tab;

        $view = in_array($tab, ['all', 'draft', 'pending', 'approved', 'rejected']) ? $tab : 'all';
        $this->load->view('table/' . $view, $data);
    }

    public function render_all()      { $this->render_tab('all'); }
    public function render_draft()    { $this->render_tab('draft'); }
    public function render_pending()  { $this->render_tab('pending'); }
    public function render_approved() { $this->render_tab('approved'); }
    public function render_rejected() { $this->render_tab('rejected'); }

    // -------------------------------------------------------------------------
    // RENDER PARTIAL TABLE PER TAB VIA AJAX (APPROVAL MANAGEMENT)
    // -------------------------------------------------------------------------

    public function render_app_tab($tab = 'pending')
    {
        $this->auth->restrict($this->viewAppPermission);

        $status_map = [
            'pending'  => 'Menunggu Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'all'      => null,
        ];

        $status = isset($status_map[$tab]) ? $status_map[$tab] : null;
        $list   = $this->pl_model->get_pricelists($status);
        if ($tab === 'all') {
            $list = array_values(array_filter($list, function ($r) {
                return $r->status !== 'Draft';
            }));
        }

        $data['list']     = $list;
        $data['tab']      = $tab;
        $data['table_id'] = 'tbl-app-' . $tab;

        $view = in_array($tab, ['pending', 'approved', 'rejected', 'all']) ? 'approval_' . $tab : 'approval_pending';
        $this->load->view('table/' . $view, $data);
    }

    public function render_app_pending()  { $this->render_app_tab('pending'); }
    public function render_app_approved() { $this->render_app_tab('approved'); }
    public function render_app_rejected() { $this->render_app_tab('rejected'); }
    public function render_app_all()      { $this->render_app_tab('all'); }


    // -------------------------------------------------------------------------
    // AKSI SALES: LIHAT DOKUMEN, AJUKAN, HAPUS
    // -------------------------------------------------------------------------

    /** Ambil header + detail Pricelist (AJAX, dipakai halaman Sales & Approval) */
    public function view_doc()
    {
        if (has_permission($this->viewAppPermission) && !has_permission($this->viewPermission)) {
            $this->auth->restrict($this->viewAppPermission);
        } else {
            $this->auth->restrict($this->viewPermission);
        }

        $id = (int)$this->input->get('id');
        $pl = $this->pl_model->get_pricelist_by_id($id);

        if (!$pl) {
            echo json_encode(['status' => 0, 'pesan' => 'Dokumen Pricelist tidak ditemukan']);
            return;
        }

        echo json_encode([
            'status' => 1,
            'data'   => [
                'header'  => $pl->header,
                'details' => $pl->details,
            ],
        ]);
    }

    /** Sales Mengajukan Draft Pricelist ke Management */
    public function submit()
    {
        $this->auth->restrict($this->managePermission);

        $id = (int)$this->input->post('id');
        $pl = $this->pl_model->get_pricelist_by_id($id);

        if (!$pl || $pl->header->status !== 'Draft') {
            echo json_encode(['status' => 0, 'pesan' => 'Hanya dokumen berstatus Draft yang dapat diajukan']);
            return;
        }

        if (empty($pl->details)) {
            echo json_encode(['status' => 0, 'pesan' => 'Dokumen belum memiliki baris varian, tidak dapat diajukan']);
            return;
        }

        $ok = $this->pl_model->submit_to_management($id, $this->auth->user_id());
        if ($ok) {
            write_log('Pricelist', 'Submit', "Sales mengajukan Pricelist {$pl->header->no_pricelist} ke Management", (array)$pl->header, null, 1);
            echo json_encode([
                'status' => 1,
                'pesan'  => "Pricelist {$pl->header->no_pricelist} berhasil diajukan ke Management.",
            ]);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal mengajukan pricelist']);
        }
    }

    /** Hapus satu baris varian dari dokumen Draft */
    public function delete_item()
    {
        $this->auth->restrict($this->deletePermission);

        $detail_id = (int)$this->input->post('detail_id');
        $detail    = $this->db->get_where('pricelist_detail', ['id' => $detail_id])->row();

        if (!$detail) {
            echo json_encode(['status' => 0, 'pesan' => 'Baris varian tidak ditemukan']);
            return;
        }

        $pl = $this->pl_model->get_pricelist_by_id($detail->id_pricelist_header);
        if (!$pl || $pl->header->status !== 'Draft') {
            echo json_encode(['status' => 0, 'pesan' => 'Baris hanya dapat dihapus dari dokumen berstatus Draft']);
            return;
        }

        if ($this->pl_model->delete_detail($detail_id)) {
            write_log('Pricelist', 'Delete Item', "Menghapus baris varian {$detail->no_uniq_id} dari Draft {$pl->header->no_pricelist}", (array)$detail, null, 1);
            echo json_encode(['status' => 1, 'pesan' => 'Baris varian berhasil dihapus']);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menghapus baris varian']);
        }
    }

    /** Hapus seluruh dokumen Draft */
    public function delete()
    {
        $this->auth->restrict($this->deletePermission);

        $id = (int)$this->input->post('id');
        $pl = $this->pl_model->get_pricelist_by_id($id);

        if (!$pl || $pl->header->status !== 'Draft') {
            echo json_encode(['status' => 0, 'pesan' => 'Hanya dokumen berstatus Draft yang dapat dihapus']);
            return;
        }

        if ($this->pl_model->delete_draft($id)) {
            write_log('Pricelist', 'Delete', "Menghapus Draft Pricelist {$pl->header->no_pricelist}", (array)$pl->header, null, 1);
            echo json_encode(['status' => 1, 'pesan' => "Draft Pricelist {$pl->header->no_pricelist} berhasil dihapus."]);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menghapus draft pricelist']);
        }
    }

    /** Management Menyetujui Pricelist (Approved) */
    public function approve()
    {
        $this->auth->restrict($this->manageAppPermission);

        $id = (int)$this->input->post('id');
        $pl = $this->pl_model->get_pricelist_by_id($id);

        if (!$pl || $pl->header->status !== 'Menunggu Approval') {
            echo json_encode(['status' => 0, 'pesan' => 'Pengajuan sudah tidak dalam status menunggu approval']);
            return;
        }

        $ok = $this->pl_model->approve_pricelist($id, $this->auth->user_id());
        if ($ok) {
            write_log('Approval Pricelist', 'Approve', "Management menyetujui Pricelist {$pl->header->no_pricelist}", (array)$pl->header, null, 1);
            echo json_encode([
                'status' => 1,
                'pesan'  => "Pricelist {$pl->header->no_pricelist} berhasil disetujui (Approved). Riwayat harga dan katalog aktif telah diperbarui.",
            ]);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menyetujui pricelist']);
        }
    }

    /** Management Menolak Pricelist (Rejected) */
    public function reject()
    {
        $this->auth->restrict($this->manageAppPermission);

        $id     = (int)$this->input->post('id');
        $reason = trim($this->input->post('reject_reason'));

        if (empty($reason)) {
            echo json_encode(['status' => 0, 'pesan' => 'Alasan penolakan (Reject) wajib diisi']);
            return;
        }

        $pl = $this->pl_model->get_pricelist_by_id($id);
        if (!$pl || $pl->header->status !== 'Menunggu Approval') {
            echo json_encode(['status' => 0, 'pesan' => 'Pengajuan sudah tidak dalam status menunggu approval']);
            return;
        }

        $ok = $this->pl_model->reject_pricelist($id, $reason, $this->auth->user_id());
        if ($ok) {
            write_log('Approval Pricelist', 'Reject', "Management menolak Pricelist {$pl->header->no_pricelist} (Alasan: {$reason})", (array)$pl->header, null, 1);
            echo json_encode([
                'status' => 1,
                'pesan'  => "Pricelist {$pl->header->no_pricelist} berhasil ditolak (Rejected). Alasan tersimpan untuk Sales.",
            ]);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menolak pricelist']);
        }
    }
}
