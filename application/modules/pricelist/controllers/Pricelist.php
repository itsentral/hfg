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

    /** Daftar Dokumen Pricelist (Sales) */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $status_filter = $this->input->get('status');
        $list          = $this->pl_model->get_pricelists($status_filter);

        $this->template->set([
            'list'          => $list,
            'status_filter' => $status_filter,
        ]);
        $this->template->render('index');
    }

    /** Ambil Detail Dokumen Pricelist (AJAX) */
    public function view_doc()
    {
        $this->auth->restrict($this->viewPermission);

        $id = (int)$this->input->get('id');
        $pl = $this->pl_model->get_pricelist_by_id($id);

        if (!$pl) {
            echo json_encode(['status' => 0, 'pesan' => 'Pricelist tidak ditemukan']);
            return;
        }

        echo json_encode(['status' => 1, 'data' => $pl]);
    }

    /** Ajukan Draft ke Management */
    public function submit()
    {
        $this->auth->restrict($this->managePermission);

        $id = (int)$this->input->post('id');
        $pl = $this->pl_model->get_pricelist_by_id($id);

        if (!$pl || $pl->header->status !== 'Draft') {
            echo json_encode(['status' => 0, 'pesan' => 'Hanya draft pricelist yang dapat diajukan']);
            return;
        }

        if (empty($pl->details)) {
            echo json_encode(['status' => 0, 'pesan' => 'Draft belum memiliki varian produk']);
            return;
        }

        $ok = $this->pl_model->submit_to_management($id, $this->auth->user_id());
        if ($ok) {
            write_log('Pricelist', 'Submit', "Pricelist {$pl->header->no_pricelist} diajukan ke Management", (array)$pl->header, null, 1);
            echo json_encode([
                'status' => 1,
                'pesan'  => "Pricelist {$pl->header->no_pricelist} berhasil diajukan ke Management.",
            ]);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal mengajukan pricelist']);
        }
    }

    /** Hapus Baris Detail Varian dari Draft */
    public function delete_item()
    {
        $this->auth->restrict($this->managePermission);

        $detail_id = (int)$this->input->post('detail_id');
        $ok = $this->pl_model->delete_detail($detail_id);

        if ($ok) {
            echo json_encode(['status' => 1, 'pesan' => 'Item varian berhasil dihapus dari draft']);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menghapus item']);
        }
    }

    /** Hapus Draft Dokumen */
    public function delete()
    {
        $this->auth->restrict($this->deletePermission);

        $id = (int)$this->input->post('id');
        $pl = $this->pl_model->get_pricelist_by_id($id);

        if (!$pl || $pl->header->status !== 'Draft') {
            echo json_encode(['status' => 0, 'pesan' => 'Hanya draft yang dapat dihapus']);
            return;
        }

        $ok = $this->pl_model->delete_draft($id);
        if ($ok) {
            write_log('Pricelist', 'Delete', "Draft Pricelist {$pl->header->no_pricelist} dihapus", (array)$pl->header, null, 1);
            echo json_encode(['status' => 1, 'pesan' => "Draft {$pl->header->no_pricelist} berhasil dihapus"]);
        } else {
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menghapus draft']);
        }
    }

    /** Menu Approval Pricelist (Management / GM) */
    public function approval()
    {
        $this->auth->restrict($this->viewAppPermission);

        $status_filter = $this->input->get('status');
        $list          = $this->pl_model->get_pricelists($status_filter);

        $this->template->title('Approval Pricelist');
        $this->template->page_icon('fa fa-check-circle');
        $this->template->set([
            'list'          => $list,
            'status_filter' => $status_filter,
        ]);
        $this->template->render('approval');
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
