<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Delivery_fg extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Delivery_fg/Delivery_fg_model');
        $this->load->helper('config_param');
        $this->template->title('Delivery FG');
        $this->template->page_icon('fa fa-truck');
    }

    // =========================================================================
    // Pages
    // =========================================================================

    /**
     * List Delivery Order
     */
    public function index()
    {
        $this->template->render('index');
    }

    /**
     * Form buat DO baru
     */
    public function add()
    {
        $this->template->render('form_do');
    }

    /**
     * Form edit DO (hanya status Draft)
     *
     * @param string $do_no
     */
    public function edit($do_no)
    {
        $do = $this->Delivery_fg_model->get_do($do_no);
        if (!$do) {
            $this->session->set_flashdata('error', 'DO tidak ditemukan');
            redirect('delivery_fg');
        }
        if ($do->status !== 'Draft') {
            $this->session->set_flashdata('error', 'DO hanya bisa diedit dalam status Draft');
            redirect('delivery_fg/view/' . $do_no);
        }

        $details = $this->Delivery_fg_model->get_do_details($do_no);
        $this->template->set(['do' => $do, 'details' => $details]);
        $this->template->render('form_do');
    }

    /**
     * Detail DO + log timbang + riwayat approval
     *
     * @param string $do_no
     */
    public function view($do_no)
    {
        $do = $this->Delivery_fg_model->get_do($do_no);
        if (!$do) {
            $this->session->set_flashdata('error', 'DO tidak ditemukan');
            redirect('delivery_fg');
        }

        $details      = $this->Delivery_fg_model->get_do_details($do_no);
        $weight_log   = $this->Delivery_fg_model->get_do_weight_log($do_no);
        $approval_log = $this->Delivery_fg_model->get_do_approval_log($do_no);

        // Hitung total estimasi berat
        $total_estimasi = 0;
        foreach ($details as $d) {
            $total_estimasi += (float) $d->estimasi_berat;
        }

        $this->template->set([
            'do'             => $do,
            'details'        => $details,
            'weight_log'     => $weight_log,
            'approval_log'   => $approval_log,
            'total_estimasi' => $total_estimasi,
        ]);
        $this->template->render('view_do');
    }

    // =========================================================================
    // JSON Endpoints
    // =========================================================================

    /**
     * DataTables server-side endpoint
     */
    public function data_side_do()
    {
        $result = $this->Delivery_fg_model->get_list_for_datatable($_REQUEST);
        $data   = [];

        foreach ($result['data'] as $i => $row) {
            $status_badge = $this->_status_badge($row->status);
            $btn_view     = '<a href="' . base_url('delivery_fg/view/' . $row->do_no) . '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>';
            $btn_edit     = $row->status === 'Draft'
                ? ' <a href="' . base_url('delivery_fg/edit/' . $row->do_no) . '" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>'
                : '';

            $data[] = [
                $i + 1 + (int) $_REQUEST['start'],
                $row->do_no,
                $row->customer,
                $row->tgl_delivery,
                $status_badge,
                $row->created_at,
                $btn_view . $btn_edit,
            ];
        }

        echo json_encode([
            'draw'            => (int) $_REQUEST['draw'],
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }

    /**
     * AJAX: ambil daftar stok FG available
     */
    public function get_stok_fg()
    {
        $stok = $this->db
            ->where('qty_stok >', 0)
            ->order_by('produk_fg', 'ASC')
            ->get('fg_stock')
            ->result();

        echo json_encode(['success' => true, 'data' => $stok]);
    }

    // =========================================================================
    // POST Actions
    // =========================================================================

    /**
     * POST: validasi stok, hitung estimasi berat, simpan DO
     */
    public function save_do()
    {
        $customer     = $this->input->post('customer');
        $tgl_delivery = $this->input->post('tgl_delivery');
        $keterangan   = $this->input->post('keterangan');
        $do_no_edit   = $this->input->post('do_no'); // jika edit

        if (empty($customer) || empty($tgl_delivery)) {
            $this->session->set_flashdata('error', 'Customer dan tanggal delivery wajib diisi');
            redirect($do_no_edit ? 'delivery_fg/edit/' . $do_no_edit : 'delivery_fg/add');
        }

        $produk_fg_arr      = $this->input->post('produk_fg');
        $nm_produk_fg_arr   = $this->input->post('nm_produk_fg');
        $qty_kirim_arr      = $this->input->post('qty_kirim');
        $berat_referensi_arr = $this->input->post('berat_referensi');

        if (empty($produk_fg_arr)) {
            $this->session->set_flashdata('error', 'Minimal satu item produk FG harus diisi');
            redirect($do_no_edit ? 'delivery_fg/edit/' . $do_no_edit : 'delivery_fg/add');
        }

        $details = [];
        foreach ($produk_fg_arr as $idx => $produk_fg) {
            if (empty($produk_fg)) continue;
            $details[] = [
                'produk_fg'       => $produk_fg,
                'nm_produk_fg'    => isset($nm_produk_fg_arr[$idx]) ? $nm_produk_fg_arr[$idx] : '',
                'qty_kirim'       => (float) $qty_kirim_arr[$idx],
                'berat_referensi' => (float) $berat_referensi_arr[$idx],
            ];
        }

        $header = [
            'customer'     => $customer,
            'tgl_delivery' => $tgl_delivery,
            'keterangan'   => $keterangan,
            'created_by'   => $this->auth->user_id(),
        ];

        if ($do_no_edit) {
            $result = $this->Delivery_fg_model->update_do($do_no_edit, $header, $details);
        } else {
            $result = $this->Delivery_fg_model->save_do($header, $details);
        }

        if (!$result['success']) {
            $this->session->set_flashdata('error', $result['message']);
            redirect($do_no_edit ? 'delivery_fg/edit/' . $do_no_edit : 'delivery_fg/add');
        }

        $this->session->set_flashdata('success', $result['message']);
        redirect('delivery_fg/view/' . $result['do_no']);
    }

    /**
     * POST: simpan berat aktual, hitung selisih, cek toleransi
     *
     * @param string $do_no
     */
    public function save_timbang($do_no)
    {
        $berat_aktual = (float) $this->input->post('berat_aktual');
        $keterangan   = $this->input->post('keterangan');
        $user_id      = $this->auth->user_id();

        if ($berat_aktual <= 0) {
            $this->session->set_flashdata('error', 'Berat aktual harus lebih dari 0');
            redirect('delivery_fg/view/' . $do_no);
        }

        $result = $this->Delivery_fg_model->save_timbang($do_no, $berat_aktual, $user_id, $keterangan);

        if (!$result['success']) {
            $this->session->set_flashdata('error', $result['message']);
        } else {
            $pct = round($result['selisih_pct'] * 100, 2);
            if ($result['new_status'] === 'Waiting Approval') {
                $this->session->set_flashdata('warning',
                    'Timbang disimpan. Selisih ' . $pct . '% melebihi toleransi. DO menunggu approval manager.'
                );
            } else {
                $this->session->set_flashdata('success',
                    'Timbang disimpan. Selisih ' . $pct . '% dalam toleransi. DO siap dikirim.'
                );
            }
        }

        redirect('delivery_fg/view/' . $do_no);
    }

    /**
     * POST: cek self-approval, approve DO, return JSON
     *
     * @param string $do_no
     */
    public function process_approve($do_no)
    {
        $approver_id = $this->auth->user_id();
        $alasan      = $this->input->post('alasan');
        $result      = $this->Delivery_fg_model->approve_do($do_no, $approver_id, $alasan);
        echo json_encode($result);
    }

    /**
     * POST: reject DO + alasan, return JSON
     *
     * @param string $do_no
     */
    public function process_reject($do_no)
    {
        $approver_id = $this->auth->user_id();
        $alasan      = $this->input->post('alasan');

        if (empty($alasan)) {
            echo json_encode(['success' => false, 'message' => 'Alasan reject wajib diisi']);
            return;
        }

        $result = $this->Delivery_fg_model->reject_do($do_no, $approver_id, $alasan);
        echo json_encode($result);
    }

    /**
     * POST: konfirmasi shipped, kurangi stok, return JSON
     *
     * @param string $do_no
     */
    public function process_ship($do_no)
    {
        $user_id = $this->auth->user_id();
        $result  = $this->Delivery_fg_model->ship_do($do_no, $user_id);
        echo json_encode($result);
    }

    /**
     * POST: cancel DO, return JSON
     *
     * @param string $do_no
     */
    public function process_cancel($do_no)
    {
        $user_id = $this->auth->user_id();
        $result  = $this->Delivery_fg_model->cancel_do($do_no, $user_id);
        echo json_encode($result);
    }

    /**
     * GET: render template surat jalan (hanya jika status Approved Exception atau Shipped)
     *
     * @param string $do_no
     */
    public function cetak_surat_jalan($do_no)
    {
        $do = $this->Delivery_fg_model->get_do($do_no);
        if (!$do) {
            show_error('DO tidak ditemukan', 404);
        }

        if (!in_array($do->status, ['Approved Exception', 'Shipped'])) {
            $this->session->set_flashdata('error', 'Surat jalan hanya bisa dicetak jika status Approved Exception atau Shipped');
            redirect('delivery_fg/view/' . $do_no);
        }

        $details = $this->Delivery_fg_model->get_do_details($do_no);

        $total_estimasi = 0;
        $total_qty      = 0;
        foreach ($details as $d) {
            $total_estimasi += (float) $d->estimasi_berat;
            $total_qty      += (float) $d->qty_kirim;
        }

        // Ambil berat aktual terakhir
        $weight_log  = $this->Delivery_fg_model->get_do_weight_log($do_no);
        $berat_aktual = !empty($weight_log) ? $weight_log[0]->berat_aktual : null;

        $this->load->view('delivery_fg/surat_jalan', [
            'do'             => $do,
            'details'        => $details,
            'total_estimasi' => $total_estimasi,
            'total_qty'      => $total_qty,
            'berat_aktual'   => $berat_aktual,
        ]);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function _status_badge($status)
    {
        $map = [
            'Draft'              => 'secondary',
            'Waiting Approval'   => 'warning',
            'Approved Exception' => 'success',
            'Shipped'            => 'dark',
            'Cancelled'          => 'danger',
        ];
        $color = isset($map[$status]) ? $map[$status] : 'secondary';
        return "<span class='badge bg-{$color}'>{$status}</span>";
    }
}
