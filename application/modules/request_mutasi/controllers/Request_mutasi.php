<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2022
 *
 * This is controller for Request Mutasi
 */

class Request_mutasi extends Admin_Controller
{
    // Permission
    protected $viewPermission   = "Request_mutasi.View";
    protected $addPermission    = "Request_mutasi.Add";
    protected $managePermission = "Request_mutasi.Manage";
    protected $deletePermission = "Request_mutasi.Delete";

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Request_mutasi/Request_mutasi_model', 'All/All_model', 'Jurnal_nomor/Jurnal_model', 'Gl_interface/Acc_model'));
        $this->load->helper('app');
        $this->template->title('Manage Request Mutasi');
        $this->template->page_icon('fa fa-table');
        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        $data = $this->Request_mutasi_model->GetListDataRequest();
        $this->template->set('data', $data);
        $this->template->title('Request Mutasi');
        $this->template->render('index');
    }

    public function create()
    {
        $getInv             = $this->db->query("SELECT * FROM tr_invoice_header")->row();
        $Cust               = $this->db->query("SELECT a.id_customer,b.nm_customer FROM tr_invoice_header a
											INNER JOIN customer b on a.id_customer=b.id_customer GROUP BY a.id_customer")->result();
        $bank1              = $this->Jurnal_model->get_Coa_Bank_Aja('101');
        $pphpenjualan       = $this->Acc_model->combo_pph_penjualan();
        $datacoa            = $this->Acc_model->GetCoaCombo();
        $template           = $this->Acc_model->GetTemplate();
        $data_coa_bank      = $this->All_model->GetCoaCombo('5', " a.no_perkiraan like '1101%'");
        $matauang           = $this->All_model->GetKursCombo();
        $data = [
            'result'        => $getInv,
            'customer'      => $Cust,
            'bank1'         => $bank1,
            'pphpenjualan'  => $pphpenjualan,
            'datacoa'       => $datacoa,
            'template'      => $template,
            'datbank'       => $data_coa_bank,
            'matauang'      => $matauang,
        ];

        $this->template->title('Form Request Mutasi');
        $this->template->render('form', $data);
    }

    public function save()
    {
        $post   = $this->input->post();

        $tgl    = $post['tgl_request'];
        $dari   = $post['dari'];
        $ke     = $post['ke'];

        $kode_mutasi   = $this->Request_mutasi_model->generate_nopn($tgl);

        // Query COA validation langsung dari DBACC
        $q_bank_asal    = $this->db->query("SELECT * FROM " . DBACC . ".coa_master WHERE no_perkiraan=?", [$dari]);
        $q_bank_tujuan  = $this->db->query("SELECT * FROM " . DBACC . ".coa_master WHERE no_perkiraan=?", [$ke]);

        if (!$q_bank_asal || !$q_bank_tujuan) {
            echo json_encode([
                'status' => 2,
                'pesan'  => 'Database ' . DBACC . ' tidak dapat diakses.'
            ]);
            return;
        }

        $bank_asal    = $q_bank_asal->row();
        $bank_tujuan  = $q_bank_tujuan->row();

        if (!$bank_asal) {
            echo json_encode(['status' => 2, 'pesan' => "COA '$dari' tidak ditemukan di database " . DBACC . "."]);
            return;
        }
        if (!$bank_tujuan) {
            echo json_encode(['status' => 2, 'pesan' => "COA '$ke' tidak ditemukan di database " . DBACC . "."]);
            return;
        }

        $this->db->trans_begin();
        $data = array(
            'kd_mutasi'         => $kode_mutasi,
            'tgl_request'       => date('Y-m-d', strtotime($post['tgl_request'])),
            'bank_asal'         => $dari,
            'bank_tujuan'       => $ke,
            'mata_uang'         => $post['matauang'],
            'target_accounting' => 'default',
            'nilai_request'     => str_replace(",", "", $post['nilai']),
            'keterangan'        => $post['keterangan'],
            'terbilang'         => $post['terbilang'],
            'nama_bank_asal'    => $bank_asal->nama,
            'nama_bank_tujuan'  => $bank_tujuan->nama,
            'created_by'        => $this->auth->user_name(),
            'created_on'        => date('Y-m-d H:i:s'),
        );
        $this->db->insert('tr_request_mutasi', $data);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $return = array(
                'status' => 2,
                'pesan'  => 'Save Process Failed. Please Try Again...'
            );
        } else {
            $this->db->trans_commit();
            $return = array(
                'status' => 1,
                'nomor'  => $kode_mutasi,
                'pesan'  => 'Save Process Success.'
            );
        }
        echo json_encode($return);
    }

    public function mutasi()
    {
        $data = $this->Request_mutasi_model->GetListDataMutasi();
        $this->template->title('List Mutasi');
        $this->template->set('data', $data);
        $this->template->render('mutasi');
    }

    public function approval_mutasi()
    {
        $data = $this->Request_mutasi_model->GetListApproval();
        $this->template->set('data', $data);
        $this->template->title('Approval Mutasi');
        $this->template->render('index_approval');
    }

    public function add_mutasi($id)
    {
        $result             = $this->Request_mutasi_model->GetDataApprove($id);
        $bank1              = $this->Jurnal_model->get_Coa_Bank_Aja('101');
        $pphpenjualan       = $this->Acc_model->combo_pph_penjualan();
        $datacoa            = $this->Acc_model->GetCoaCombo();
        $template           = $this->Acc_model->GetTemplate();
        $data_coa_bank      = $this->All_model->GetCoaCombo('5', " a.no_perkiraan like '1101%'");
        $matauang           = $this->All_model->GetKursCombo();
        $data = [
            'result'        => $result,
            'bank1'         => $bank1,
            'pphpenjualan'  => $pphpenjualan,
            'datacoa'       => $datacoa,
            'template'      => $template,
            'datbank'       => $data_coa_bank,
            'matauang'      => $matauang,
        ];
        $this->template->page_icon('fa fa-plus');
        $this->template->title('Add Mutasi');
        $this->template->render('add', $data);
    }

    public function save_mutasi()
    {
        $post = $this->input->post();

        $no_request  = $post['no_request'];
        $dari        = $post['dari'];
        $ke          = $post['ke'];
        $keterangan  = $post['keterangan'];
        $kurs        = str_replace(',', '', $post['kurs'] ?? '1');
        $tgl         = $post['tgl'];
        $nilai_req   = str_replace(',', '', $post['nilai']);
        $rupiah      = str_replace(',', '', $post['rupiah']);

        $request = $this->db->get_where('tr_request_mutasi', ['kd_mutasi' => $no_request])->row();
        if (!$request) {
            echo json_encode(['status' => 2, 'pesan' => 'Data request mutasi tidak ditemukan.']);
            return;
        }

        $kode_aktual = $this->_generate_kode_aktual($tgl);

        $this->db->trans_begin();

        $data_aktual = [
            'kd_mutasi'         => $kode_aktual,
            'kd_mutasi_aktual'  => $kode_aktual,
            'kd_mutasi_request' => $no_request,
            'tgl_request'       => $request->tgl_request,
            'tgl_mutasi'        => date('Y-m-d', strtotime($tgl)),
            'bank_asal'         => $dari,
            'bank_tujuan'       => $ke,
            'nama_bank_asal'    => $request->nama_bank_asal,
            'nama_bank_tujuan'  => $request->nama_bank_tujuan,
            'mata_uang'         => $request->mata_uang,
            'target_accounting' => 'default',
            'kurs'              => $kurs,
            'nilai_request'     => $nilai_req,
            'nilai_aktual'      => $rupiah,
            'keterangan'        => $keterangan,
            'created_by'        => $this->auth->user_name(),
            'created_on'        => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tr_request_mutasi_aktual', $data_aktual);

        $this->db->update('tr_request_mutasi', ['status' => '1'], ['kd_mutasi' => $no_request]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 2, 'pesan' => 'Gagal menyimpan data mutasi.']);
            return;
        }

        $jurnal_ok = $this->_save_jurnal_mutasi_aktual($kode_aktual, $dari, $ke, $rupiah, $keterangan, $tgl, $request->mata_uang, $nilai_req);

        if (!$jurnal_ok) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 2, 'pesan' => 'Gagal membuat jurnal mutasi.']);
            return;
        }

        $this->db->trans_commit();
        echo json_encode([
            'status' => 1,
            'nomor'  => $kode_aktual,
            'pesan'  => 'Mutasi berhasil disimpan.'
        ]);
    }

    private function _generate_kode_aktual($tgl)
    {
        $prefix = 'MTR-' . date('ym', strtotime($tgl));

        $row = $this->db->query(
            "SELECT MAX(kd_mutasi_aktual) AS max_id FROM tr_request_mutasi_aktual WHERE kd_mutasi_aktual LIKE ?",
            [$prefix . '%']
        )->row_array();

        $next = $row && $row['max_id'] ? ((int) substr($row['max_id'], -5) + 1) : 1;
        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function get_coa_by_target()
    {
        $query = $this->db->query(
            "SELECT no_perkiraan, nama FROM " . DBACC . ".coa_master WHERE no_perkiraan LIKE '1101%' ORDER BY no_perkiraan ASC"
        );

        if (!$query) {
            echo json_encode([
                'status' => 2,
                'pesan'  => 'Database ' . DBACC . ' tidak dapat diakses.'
            ]);
            return;
        }

        $data = $query->result_array();

        echo json_encode([
            'status' => 1,
            'data'   => $data
        ]);
    }

    private function _save_jurnal_mutasi_aktual($kode_aktual, $bank_asal, $bank_tujuan, $nilai_idr, $keterangan, $tgl, $mata_uang, $nilai_valas)
    {
        $db_name    = DBACC;
        $tgl_jurnal = date('Y-m-d', strtotime($tgl));
        $Nomor_JV   = $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $tgl_jurnal, $db_name);

        // Header jurnal
        $this->db->insert($db_name . '.jarh', [
            'nomor'         => $Nomor_JV,
            'tgl'           => $tgl_jurnal,
            'jml'           => $nilai_idr,
            'kd_pembayaran' => $kode_aktual,
            'kdcab'         => '101',
            'jenis_reff'    => 'BUM',
            'no_reff'       => $kode_aktual,
            'terima_dari'   => $keterangan,
            'jenis_ar'      => 'BUM',
            'note'          => $keterangan,
            'batal'         => '0',
        ]);

        // Detail jurnal: debit bank tujuan, kredit bank asal
        $this->db->insert_batch($db_name . '.jurnal', [
            [
                'nomor'              => $Nomor_JV,
                'tanggal'            => $tgl_jurnal,
                'tipe'               => 'BUM',
                'no_perkiraan'       => $bank_tujuan,
                'keterangan'         => $keterangan,
                'no_reff'            => $kode_aktual,
                'debet'              => $nilai_idr,
                'kredit'             => 0,
                'nilai_valas_debet'  => $nilai_valas,
                'nilai_valas_kredit' => 0,
                'created_on'         => date('Y-m-d H:i:s'),
                'created_by'         => $this->auth->user_name(),
            ],
            [
                'nomor'              => $Nomor_JV,
                'tanggal'            => $tgl_jurnal,
                'tipe'               => 'BUM',
                'no_perkiraan'       => $bank_asal,
                'keterangan'         => $keterangan,
                'no_reff'            => $kode_aktual,
                'debet'              => 0,
                'kredit'             => $nilai_idr,
                'nilai_valas_debet'  => 0,
                'nilai_valas_kredit' => $nilai_valas,
                'created_on'         => date('Y-m-d H:i:s'),
                'created_by'         => $this->auth->user_name(),
            ],
        ]);

        // Update counter BUM di database dan simpan nomor jurnal ke aktual
        $this->db->query("UPDATE " . $db_name . ".pastibisa_tb_cabang SET nobum = nobum + 1 WHERE nocab = '101'");
        $this->db->update('tr_request_mutasi_aktual', ['jurnal' => $Nomor_JV, 'jurnal1' => $Nomor_JV], ['kd_mutasi_aktual' => $kode_aktual]);

        return ($this->db->affected_rows() >= 0);
    }

    public function approval($id)
    {
        $data = $this->Request_mutasi_model->GetDataApprove($id);
        $this->template->set('data', $data);
        $this->template->page_icon('fa fa-check');
        $this->template->title('Approval Mutasi');
        $this->template->render('approval');
    }

    public function save_approval()
    {
        $kd_mutasi = $this->input->post('no_request');

        $data = array(
            'status_approve'    => '1',
            'approved_by'       => $this->auth->user_name(),
            'approved_on'       => date('Y-m-d H:i:s'),
        );

        $this->db->trans_begin();
        $this->db->update('tr_request_mutasi', $data, ['kd_mutasi' => $kd_mutasi]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Return = array(
                'status'    => 2,
                'pesan'     => 'Approve Process Failed. Please Try Again...'
            );
        } else {
            $this->db->trans_commit();
            $Arr_Return = array(
                'status'    => 1,
                'nomor'     => $kd_mutasi,
                'pesan'     => 'Approve Process Success.'
            );
        }
        echo json_encode($Arr_Return);
    }

    public function reject_mutasi()
    {
        $kd_mutasi = $this->input->post('no_request');

        $data = array(
            'alasan'            => $this->input->post('alasan'),
            'status_approve'    => '2',
        );

        $this->db->trans_begin();
        $this->db->update('tr_request_mutasi', $data, ['kd_mutasi' => $kd_mutasi]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Return = array(
                'status'    => 2,
                'pesan'     => 'Reject Process Failed. Please Try Again...'
            );
        } else {
            $this->db->trans_commit();
            $Arr_Return = array(
                'status'    => 1,
                'nomor'     => $kd_mutasi,
                'pesan'     => 'Reject Process Success.'
            );
        }
        echo json_encode($Arr_Return);
    }

    public function index_transaksi()
    {
        $data = $this->Request_mutasi_model->GetListDataTransaksi();
        $this->template->set('data', $data);
        $this->template->title('Data Transaksi Bank');
        $this->template->render('index_transaksi');
    }

    public function create_transaksi()
    {
        $getInv             = $this->db->query("SELECT * FROM tr_invoice_header")->row();
        $Cust               = $this->db->query("SELECT a.id_customer,b.nm_customer FROM tr_invoice_header a
											INNER JOIN customer b on a.id_customer=b.id_customer GROUP BY a.id_customer")->result();
        $bank1              = $this->Jurnal_model->get_Coa_Bank_Aja('101');
        $pphpenjualan       = $this->Acc_model->combo_pph_penjualan();
        $datacoa            = $this->Acc_model->GetCoaCombo();
        $template           = $this->Acc_model->GetTemplate();
        $data_coa_bank      = $this->All_model->GetCoaCombo('5', " a.no_perkiraan like '1101%'");
        $matauang           = $this->All_model->GetKursCombo();
        $data = [
            'result'        => $getInv,
            'customer'      => $Cust,
            'bank1'         => $bank1,
            'pphpenjualan'  => $pphpenjualan,
            'datacoa'       => $datacoa,
            'template'      => $template,
            'datbank'       => $data_coa_bank,
            'matauang'      => $matauang,
        ];

        $this->template->title('Form Transaksi Bank');
        $this->template->render('form_transaksi', $data);
    }

    public function save_transaksi()
    {
        $post   = $this->input->post();

        $tgl    = $post['tgl_request'];
        $dari   = $post['dari'];
        $ke     = $post['ke'];
        $jenis  = $post['jenis_transaksi'];

        if ($jenis == 'keluar') {
            $kode_mutasi    = $this->Request_mutasi_model->generate_notr($tgl);
        } else {
            $kode_mutasi    = $this->Request_mutasi_model->generate_nokm($tgl);
        }

        // Query COA validation langsung dari DBACC
        $q_bank_asal2   = $this->db->query("SELECT * FROM " . DBACC . ".coa_master WHERE no_perkiraan=?", [$dari]);
        $q_bank_tujuan2 = $this->db->query("SELECT * FROM " . DBACC . ".coa_master WHERE no_perkiraan=?", [$ke]);

        if (!$q_bank_asal2 || !$q_bank_tujuan2) {
            echo json_encode([
                'status' => 2,
                'pesan'  => 'Database ' . DBACC . ' tidak dapat diakses.'
            ]);
            return;
        }

        $bank_asal   = $q_bank_asal2->row();
        $bank_tujuan = $q_bank_tujuan2->row();

        if (!$bank_asal) {
            echo json_encode(['status' => 2, 'pesan' => "COA '$dari' tidak ditemukan di database " . DBACC . "."]);
            return;
        }
        if (!$bank_tujuan) {
            echo json_encode(['status' => 2, 'pesan' => "COA '$ke' tidak ditemukan di database " . DBACC . "."]);
            return;
        }

        $nilai = str_replace(",", "", isset($post['nilai']) ? $post['nilai'] : '0');
        $kurs = (!empty($post['kurs'])) ? str_replace(",", "", $post['kurs']) : '1';
        $transaksi = (!empty($post['transaksi'])) ? str_replace(",", "", $post['transaksi']) : ((float)$nilai * (float)$kurs);

        $this->db->trans_begin();
        $data = array(
            'kd_mutasi'         => $kode_mutasi,
            'tgl_request'       => date('Y-m-d', strtotime($post['tgl_request'])),
            'bank_asal'         => $dari,
            'bank_tujuan'       => $ke,
            'mata_uang'         => $post['matauang'],
            'target_accounting' => 'default',
            'kurs'              => $kurs,
            'nilai'             => $nilai,
            'transaksi'         => $transaksi,
            'keterangan'        => $post['keterangan'],
            'terbilang'         => $post['terbilang'],
            'nama_bank_asal'    => $bank_asal->nama,
            'nama_bank_tujuan'  => $bank_tujuan->nama,
            'created_by'        => $this->auth->user_name(),
            'created_on'        => date('Y-m-d H:i:s'),
            'jenis_transaksi'   => $jenis,
        );
        $this->db->insert('tr_request_mutasi_admin', $data);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Return = array(
                'status'    => 2,
                'pesan'     => 'Save Process Failed. Please Try Again...'
            );
        } else {
            if ($jenis == 'keluar') {
                $jurnal_status = $this->save_jurnal_BUK($kode_mutasi);
            } elseif ($jenis == 'terima') {
                $jurnal_status = $this->save_jurnal_BUM($kode_mutasi);
            } else {
                $jurnal_status = true;
            }

            if (!$jurnal_status) {
                $this->db->trans_rollback();
                $Arr_Return = array(
                    'status'    => 2,
                    'pesan'     => 'Jurnal Save Failed. Transaction has been rolled back.'
                );
            } else {
                $this->db->trans_commit();
                $Arr_Return = array(
                    'status'    => 1,
                    'nomor'     => $kode_mutasi,
                    'pesan'     => 'Save Process Success.'
                );
            }
        }
        echo json_encode($Arr_Return);
    }

    public function save_jurnal_BUM($kode_mutasi)
    {
        $jurnal     = $this->db->query("SELECT * FROM tr_request_mutasi_admin WHERE kd_mutasi='$kode_mutasi'")->row();
        if (!$jurnal) return false;

        $db_name = DBACC;
        $Nomor_JV   = $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $jurnal->tgl_request, $db_name);

        $this->db->insert($db_name . '.jarh', [
            'nomor'         => $Nomor_JV,
            'tgl'           => $jurnal->tgl_request,
            'jml'           => $jurnal->transaksi,
            'kd_pembayaran' => $kode_mutasi,
            'kdcab'         => '101',
            'jenis_reff'    => 'BUM',
            'no_reff'       => $kode_mutasi,
            'terima_dari'   => $jurnal->keterangan,
            'jenis_ar'      => 'BUM',
            'note'          => $jurnal->keterangan,
            'batal'         => '0'
        ]);

        $det_Jurnaltes = [
            [
                'nomor'                 => $Nomor_JV,
                'tanggal'               => $jurnal->tgl_request,
                'tipe'                  => 'BUM',
                'no_perkiraan'          => $jurnal->bank_tujuan,
                'keterangan'            => $jurnal->keterangan,
                'no_reff'               => $kode_mutasi,
                'debet'                 => $jurnal->transaksi,
                'kredit'                => 0,
                'nilai_valas_debet'     => $jurnal->nilai,
                'nilai_valas_kredit'    => 0,
                'created_on'            => date('Y-m-d H:i:s'),
                'created_by'            => $this->auth->user_name(),
            ],
            [
                'nomor'                 => $Nomor_JV,
                'tanggal'               => $jurnal->tgl_request,
                'tipe'                  => 'BUM',
                'no_perkiraan'          => $jurnal->bank_asal,
                'keterangan'            => $jurnal->keterangan,
                'no_reff'               => $kode_mutasi,
                'debet'                 => 0,
                'kredit'                => $jurnal->transaksi,
                'nilai_valas_debet'     => 0,
                'nilai_valas_kredit'    => $jurnal->nilai,
                'created_on'            => date('Y-m-d H:i:s'),
                'created_by'            => $this->auth->user_name(),
            ]
        ];

        $this->db->insert_batch($db_name . '.jurnal', $det_Jurnaltes);
        $this->db->query("UPDATE " . $db_name . ".pastibisa_tb_cabang SET nobum = nobum + 1 WHERE nocab = '101'");
        $this->db->query("UPDATE tr_request_mutasi_admin SET jurnal1 = '$Nomor_JV' WHERE kd_mutasi = '$kode_mutasi'");

        return ($this->db->affected_rows() > 0);
    }

    public function save_jurnal_BUK($kode_mutasi)
    {
        $jurnal         = $this->db->query("SELECT * FROM tr_request_mutasi_admin WHERE kd_mutasi='$kode_mutasi'")->row();
        if (!$jurnal) return false;

        $db_name = DBACC;
        $Nomor_JV       = $this->Jurnal_model->get_Nomor_Jurnal_BUK2('101', $jurnal->tgl_request, $db_name);
        $nokir_debet    = ($jurnal->jenis_transaksi == 'bank') ? '1112-01-01' : $jurnal->bank_asal;

        $this->db->insert($db_name . '.japh', [
            'nomor'         => $Nomor_JV,
            'tgl'           => $jurnal->tgl_request,
            'jml'           => $jurnal->transaksi,
            'kdcab'         => '101',
            'jenis_reff'    => 'BUK',
            'no_reff'       => $kode_mutasi,
            'bayar_kepada'  => $jurnal->bank_tujuan,
            'jenis_ap'      => 'BUK',
            'note'          => $jurnal->keterangan,
            'batal'         => '0',
            'user_id'       => $this->auth->user_id(),
        ]);

        $det_Jurnaltes = [
            [
                'nomor'                 => $Nomor_JV,
                'tanggal'               => $jurnal->tgl_request,
                'tipe'                  => 'BUK',
                'no_perkiraan'          => $nokir_debet,
                'keterangan'            => $jurnal->keterangan,
                'no_reff'               => $kode_mutasi,
                'debet'                 => $jurnal->transaksi,
                'kredit'                => 0,
                'nilai_valas_debet'     => $jurnal->nilai,
                'nilai_valas_kredit'    => 0,
                'created_on'            => date('Y-m-d H:i:s'),
                'created_by'            => $this->auth->user_name(),
            ],
            [
                'nomor'                 => $Nomor_JV,
                'tanggal'               => $jurnal->tgl_request,
                'tipe'                  => 'BUK',
                'no_perkiraan'          => $jurnal->bank_tujuan,
                'keterangan'            => $jurnal->keterangan,
                'no_reff'               => $kode_mutasi,
                'debet'                 => 0,
                'kredit'                => $jurnal->transaksi,
                'nilai_valas_debet'     => 0,
                'nilai_valas_kredit'    => $jurnal->nilai,
                'created_on'            => date('Y-m-d H:i:s'),
                'created_by'            => $this->auth->user_name(),
            ]
        ];

        $this->db->insert_batch($db_name . '.jurnal', $det_Jurnaltes);
        $this->db->query("UPDATE " . $db_name . ".pastibisa_tb_cabang SET nobuk = nobuk + 1 WHERE nocab = '101'");
        $this->db->query("UPDATE tr_request_mutasi_admin SET jurnal1 = '$Nomor_JV' WHERE kd_mutasi = '$kode_mutasi'");

        return ($this->db->affected_rows() > 0);
    }

            public function printout($kd_mutasi = '')
    {
        $this->load->helper('app');
        $kd_mutasi = trim(urldecode($kd_mutasi));

        $this->db->select('a.*, COALESCE(b.mata_uang, a.mata_uang) AS mata_uang, b.mata_uang AS nama_mata_uang');
        $this->db->from('tr_request_mutasi a');
        $this->db->join('mata_uang b', 'a.mata_uang = b.kode', 'left');
        $this->db->where('a.kd_mutasi', $kd_mutasi);
        $data = $this->db->get()->row();

        if (!$data) {
            show_404();
            return;
        }

        $this->load->view('printout', ['data' => $data]);
    }

    public function printout_mutasi($kd_mutasi_aktual = '')
    {
        $this->load->helper('app');
        $kd_mutasi_aktual = trim(urldecode($kd_mutasi_aktual));

        $this->db->select('a.*, COALESCE(b.mata_uang, a.mata_uang) AS mata_uang, b.mata_uang AS nama_mata_uang');
        $this->db->from('tr_request_mutasi_aktual a');
        $this->db->join('mata_uang b', 'a.mata_uang = b.kode', 'left');
        $this->db->group_start();
        $this->db->where('a.kd_mutasi_aktual', $kd_mutasi_aktual);
        $this->db->or_where('a.kd_mutasi', $kd_mutasi_aktual);
        $this->db->group_end();
        $data = $this->db->get()->row();

        if (!$data) {
            show_404();
            return;
        }

        $request = null;
        if (!empty($data->kd_mutasi_request)) {
            $this->db->select('a.*, b.mata_uang AS nama_mata_uang');
            $this->db->from('tr_request_mutasi a');
            $this->db->join('mata_uang b', 'a.mata_uang = b.kode', 'left');
            $this->db->where('a.kd_mutasi', $data->kd_mutasi_request);
            $request = $this->db->get()->row();
        }

        $this->load->view('print_mutasi', [
            'data'    => $data,
            'request' => $request,
        ]);
    }

    public function printout_transaksi($kd_mutasi = '')
    {
        $this->load->helper('app');
        $kd_mutasi = trim(urldecode($kd_mutasi));

        $this->db->select('a.*, COALESCE(b.mata_uang, a.mata_uang) AS mata_uang, b.mata_uang AS nama_mata_uang');
        $this->db->from('tr_request_mutasi_admin a');
        $this->db->join('mata_uang b', 'a.mata_uang = b.kode', 'left');
        $this->db->where('a.kd_mutasi', $kd_mutasi);
        $data = $this->db->get()->row();

        if (!$data) {
            show_404();
            return;
        }

        $this->load->view('print_transaksi', ['data' => $data]);
    }

    public function terbilang()
    {
        $nilai = $this->input->get('nilai');
        if ($nilai === null) {
            $nilai = $this->input->post('nilai');
        }
        $nilai = str_replace(array(',', ' '), '', (string) $nilai);
        $nilai = (float) $nilai;

        $kode = $this->input->get('matauang');
        if ($kode === null) {
            $kode = $this->input->post('matauang');
        }
        if (empty($kode)) {
            $kode = 'IDR';
        }

        $terbilang = '';
        if ($nilai > 0 && function_exists('ynz_terbilang')) {
            $terbilang = ynz_terbilang($nilai);
            $terbilang = preg_replace('/\s+/', ' ', trim($terbilang));
        }

        $mata_uang = 'RUPIAH';
        if (!empty($kode)) {
            $nama_mu = $this->getMataUangByKode($kode);
            if (!empty($nama_mu)) {
                $mata_uang = strtoupper(trim($nama_mu));
            }
        }

        $hasil = trim($terbilang . ' ' . $mata_uang);
        echo $hasil;
    }

    public function getMataUangByKode($kode)
    {
        if (empty($kode)) {
            return "RUPIAH";
        }

        if (strtoupper($kode) === 'IDR') {
            return "RUPIAH";
        }

        $query = $this->db->select('mata_uang')
            ->from('mata_uang')
            ->where('kode', $kode)
            ->get();

        if ($query && $query->num_rows() > 0) {
            $row = $query->row();
            return !empty($row->mata_uang) ? $row->mata_uang : $kode;
        } else {
            return $kode;
        }
    }
}
