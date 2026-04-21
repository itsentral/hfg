<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Fg_warehouse extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Fg_warehouse/Fg_warehouse_model');
        $this->template->title('FG Warehouse');
        $this->template->page_icon('fa fa-warehouse');
    }

    // =========================================================================
    // Pages
    // =========================================================================

    /**
     * List FG Receipt
     */
    public function index()
    {
        $this->template->render('index');
    }

    /**
     * Detail FG Receipt
     *
     * @param string $fg_receipt_no
     */
    public function view($fg_receipt_no)
    {
        $receipt = $this->Fg_warehouse_model->get_receipt($fg_receipt_no);
        if (!$receipt) {
            $this->session->set_flashdata('error', 'FG Receipt tidak ditemukan');
            redirect('fg_warehouse');
        }

        $this->template->set(['receipt' => $receipt]);
        $this->template->render('view_receipt');
    }

    /**
     * Halaman stok FG terkini per produk
     */
    public function stok_fg()
    {
        $stok_list = $this->Fg_warehouse_model->get_all_stok();
        $this->template->set(['stok_list' => $stok_list]);
        $this->template->render('stok_fg');
    }

    /**
     * Halaman kartu stok dengan filter tanggal
     *
     * @param string|null $produk_fg
     */
    public function kartu_stok($produk_fg = null)
    {
        $tgl_dari   = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        // Jika produk_fg dari GET param
        if (empty($produk_fg)) {
            $produk_fg = $this->input->get('produk_fg');
        }

        $ledger      = [];
        $stok_info   = null;
        $produk_list = $this->Fg_warehouse_model->get_all_stok();

        if (!empty($produk_fg)) {
            $stok_info = $this->Fg_warehouse_model->get_stok($produk_fg);
            $ledger    = $this->Fg_warehouse_model->get_kartu_stok($produk_fg, $tgl_dari, $tgl_sampai);
        }

        $this->template->set([
            'produk_fg'   => $produk_fg,
            'tgl_dari'    => $tgl_dari,
            'tgl_sampai'  => $tgl_sampai,
            'ledger'      => $ledger,
            'stok_info'   => $stok_info,
            'produk_list' => $produk_list,
        ]);
        $this->template->render('kartu_stok');
    }

    // =========================================================================
    // JSON Endpoints
    // =========================================================================

    /**
     * DataTables server-side endpoint untuk list FG Receipt
     */
    public function data_side_receipt()
    {
        $result = $this->Fg_warehouse_model->get_receipt_list_for_datatable($_REQUEST);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $status_badge = $this->_status_badge($row->status);
            $btn_view     = '<a href="' . base_url('fg_warehouse/view/' . $row->fg_receipt_no) . '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>';

            $data[] = [
                $i + 1 + (int) $_REQUEST['start'],
                $row->fg_receipt_no,
                $row->created_at,
                $row->report_no,
                $row->spk_no,
                $row->nm_produk_fg ? $row->nm_produk_fg : ($row->produk_fg ? $row->produk_fg : '-'),
                number_format((float) $row->fg_qty, 2) . ' pcs / ' . number_format((float) $row->fg_kg, 3) . ' kg',
                $status_badge,
                $btn_view,
            ];
        }

        echo json_encode([
            'draw'            => (int) $_REQUEST['draw'],
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }

    // =========================================================================
    // POST Actions
    // =========================================================================

    /**
     * POST: posting FG Receipt, return JSON
     *
     * @param string $fg_receipt_no
     */
    public function process_post_receipt($fg_receipt_no)
    {
        $user_id = $this->auth->user_id();
        $result  = $this->Fg_warehouse_model->post_receipt($fg_receipt_no, $user_id);
        echo json_encode($result);
    }

    /**
     * POST: cancel FG Receipt, return JSON
     *
     * @param string $fg_receipt_no
     */
    public function process_cancel_receipt($fg_receipt_no)
    {
        $user_id = $this->auth->user_id();
        $result  = $this->Fg_warehouse_model->cancel_receipt($fg_receipt_no, $user_id);
        echo json_encode($result);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function _status_badge($status)
    {
        $map = [
            'Draft'     => 'secondary',
            'Posted'    => 'success',
            'Cancelled' => 'danger',
        ];
        $color = isset($map[$status]) ? $map[$status] : 'secondary';
        return "<span class='badge bg-{$color}'>{$status}</span>";
    }
}
