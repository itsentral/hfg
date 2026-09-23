<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Budget extends Admin_Controller
{
    protected $viewPermission   = 'Budget.View';
    protected $addPermission    = 'Budget.Add';
    protected $managePermission = 'Budget.Manage';
    protected $deletePermission = 'Budget.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('budget/Budget_model', 'budget_model');
        $this->load->model('master_model');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data = array(
            'title'  => 'Index Of Budget Assets',
            'action' => 'index',
            'tanda'  => ''
        );

        // legacy history view removed
        $this->template->title('Index Of Budget Assets');
        $this->template->render('index', $data);
    }

    public function index_approve()
    {
        $this->auth->restrict($this->managePermission);

        $data = array(
            'title'  => 'Approval Of Budget Assets',
            'action' => 'index',
            'tanda'  => 'approve'
        );

        // legacy history view removed
        $this->template->title('Approval Of Budget Assets');
        $this->template->render('index_approve', $data);
    }

    public function server_side_asset()
    {
        $this->budget_model->get_data_json_asset();
    }

    public function add_asset()
    {
        $id = $this->uri->segment(3);

        if ($this->input->post()) {
            $data         = $this->input->post();
            $data_session = $this->session->userdata;
            $dateTime     = date('Y-m-d H:i:s');

            $code_planx    = isset($data['id']) ? $data['id'] : '';
            $id_dept       = (!empty($data['id_dept'])) ? $data['id_dept'] : NULL;
            $id_costcenter = (!empty($data['id_costcenter'])) ? $data['id_costcenter'] : NULL;
            $coa           = (!empty($data['coa'])) ? $data['coa'] : NULL;
            $coa_akum      = (!empty($data['coa_akum'])) ? $data['coa_akum'] : NULL;
            $nama_asset    = strtolower($data['nama_asset']);
            $tahun         = $data['tahun'];
            $bulan         = $data['bulan'];
            $budget        = str_replace(',', '', $data['budget']);
            $qty           = str_replace(',', '', $data['qty']);
            $keterangan    = strtolower($data['keterangan']);

            $ym = date('ym');

            $username = isset($data_session['ORI_User']['username']) ? $data_session['ORI_User']['username'] : (isset($data_session['app_session']['username']) ? $data_session['app_session']['username'] : (isset($data_session['username']) ? $data_session['username'] : 'admin'));

            if (empty($code_planx)) {
                $srcMtr     = "SELECT MAX(code_plan) as maxP FROM asset_planning WHERE code_plan LIKE 'PLA" . $ym . "%' ";
                $resultMtr  = $this->db->query($srcMtr)->result_array();
                $angkaUrut2 = $resultMtr[0]['maxP'];
                $urutan2    = (int)substr($angkaUrut2, 7, 3) + 1;
                $code_plan  = "PLA" . $ym . sprintf('%03s', $urutan2);

                $ArrHeader = array(
                    'id_dept'       => $id_dept,
                    'code_plan'     => $code_plan,
                    'id_costcenter' => $id_costcenter,
                    'coa'           => $coa,
                    'coa_akum'      => $coa_akum,
                    'nama_asset'    => $nama_asset,
                    'tahun'         => $tahun,
                    'bulan'         => $bulan,
                    'budget'        => $budget,
                    'budget_pr'     => $budget,
                    'budget_po'     => $budget,
                    'qty'           => $qty,
                    'keterangan'    => $keterangan,
                    'created_by'    => $username,
                    'created_date'  => $dateTime
                );
            } else {
                $code_plan = $code_planx;
                $ArrHeader = array(
                    'id_dept'       => $id_dept,
                    'id_costcenter' => $id_costcenter,
                    'coa'           => $coa,
                    'coa_akum'      => $coa_akum,
                    'nama_asset'    => $nama_asset,
                    'tahun'         => $tahun,
                    'bulan'         => $bulan,
                    'budget'        => $budget,
                    'qty'           => $qty,
                    'keterangan'    => $keterangan,
                    'updated_by'    => $username,
                    'updated_date'  => $dateTime
                );
            }

            $this->db->trans_start();
            if (empty($code_planx)) {
                $this->db->insert('asset_planning', $ArrHeader);
            } else {
                $this->db->where('code_plan', $code_planx);
                $this->db->update('asset_planning', $ArrHeader);
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $Arr_Kembali = array('pesan' => 'Process data failed. Please try again later...', 'status' => 0);
                write_log('Budget Asset', 'Save Pengajuan Budget Asset', 'Process data failed: ' . $code_plan, $data, null, 0);
            } else {
                $this->db->trans_commit();
                $Arr_Kembali = array('pesan' => 'Process data success. Thanks...', 'status' => 1);
                write_log('Budget Asset', 'Save Pengajuan Budget Asset', 'Process data success: ' . $code_plan, $data, null, 1);
            }
            echo json_encode($Arr_Kembali);
        } else {
            $header     = $this->db->query("SELECT * FROM asset_planning WHERE code_plan='" . $this->db->escape_str($id) . "'")->result();
            $db2 = $this->load->database('accounting', TRUE);
            $datacoa    = $db2->query("SELECT no_perkiraan, nama FROM coa_master WHERE level='5' ORDER BY no_perkiraan ASC")->result_array();
            $penyusutan = $db2->query("SELECT no_perkiraan as coa, nama as keterangan FROM coa_master WHERE level='5' AND (nama LIKE 'DEPRECIATION%') ORDER BY no_perkiraan ASC")->result_array();
            $list_dept  = $this->db->get('department')->result_array();
            $list_cost  = $this->db->get_where('costcenter', array('deleted' => 'N'))->result_array();

            $tanda = (!empty($header)) ? 'Edit' : 'Add';
            $data  = array(
                'title'       => $tanda . ' Budget Asset',
                'action'      => strtolower($tanda),
                'header'      => $header,
                'datacoa'     => $datacoa,
                'penyusutan'  => $penyusutan,
                'list_dept'   => $list_dept,
                'list_cost'   => $list_cost,
                'id'          => $id
            );

            $this->template->title($tanda . ' Budget Asset');
            $this->template->render('add', $data);
        }
    }

    public function view_asset()
    {
        $id     = $this->uri->segment(3);
        $db2    = $this->load->database('accounting', TRUE);
        $header = $this->db->query("
            SELECT a.*, b.nm_dept, c.nm_costcenter, d.nama as nama_coa
            FROM asset_planning a
            LEFT JOIN department b ON a.id_dept = b.id
            LEFT JOIN costcenter c ON a.id_costcenter = c.id_costcenter
            LEFT JOIN " . $db2->database . ".coa_master d ON a.coa = d.no_perkiraan
            WHERE a.code_plan='" . $this->db->escape_str($id) . "'
        ")->result();

        $data = array(
            'title'  => 'Detail Budget Asset',
            'action' => 'view',
            'header' => $header,
            'id'     => $id
        );

        $this->template->title('Detail Budget Asset');
        $this->template->render('view', $data);
    }

    public function approve_asset()
    {
        $id = $this->uri->segment(3);

        if ($this->input->post()) {
            $data         = $this->input->post();
            $data_session = $this->session->userdata;
            $dateTime     = date('Y-m-d H:i:s');

            $code_planx = isset($data['id']) ? $data['id'] : '';
            $nama_asset = strtolower($data['nama_asset']);
            $tahun      = $data['tahun'];
            $bulan      = $data['bulan'];
            $budget     = str_replace(',', '', $data['budget']);
            $budget_pr  = (!empty($data['budget_pr'])) ? str_replace(',', '', $data['budget_pr']) : $budget;
            $budget_po  = (!empty($data['budget_po'])) ? str_replace(',', '', $data['budget_po']) : $budget;
            $qty        = str_replace(',', '', $data['qty']);
            $keterangan = strtolower($data['keterangan']);
            $reason     = (!empty($data['reason'])) ? strtolower($data['reason']) : '';
            $status     = (!empty($data['status'])) ? $data['status'] : 'Y';

            $ArrHeader = array(
                'rev_nama_asset' => $nama_asset,
                'rev_tahun'      => $tahun,
                'rev_bulan'      => $bulan,
                'rev_budget'     => $budget,
                'rev_qty'        => $qty,
                'rev_keterangan' => $keterangan,
                'budget_pr'      => $budget_pr,
                'budget_po'      => $budget_po,
                'status'         => $status,
                'reason'         => $reason,
                'app_by'         => isset($data_session['ORI_User']['username']) ? $data_session['ORI_User']['username'] : (isset($data_session['app_session']['username']) ? $data_session['app_session']['username'] : (isset($data_session['username']) ? $data_session['username'] : 'admin')),
                'app_date'       => $dateTime
            );

            $this->db->trans_start();
            $this->db->where('code_plan', $code_planx);
            $this->db->update('asset_planning', $ArrHeader);
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $Arr_Kembali = array('pesan' => 'Approval failed. Please try again later...', 'status' => 0);
                write_log('Budget Asset', 'Approval Budget Asset', 'Approval failed: ' . $code_planx, $data, null, 0);
            } else {
                $this->db->trans_commit();
                $Arr_Kembali = array('pesan' => 'Approval processed successfully. Thanks...', 'status' => 1);
                write_log('Budget Asset', 'Approval Budget Asset', 'Approval processed successfully: ' . $code_planx, $data, null, 1);
            }
            echo json_encode($Arr_Kembali);
        } else {
            $header     = $this->db->query("SELECT * FROM asset_planning WHERE code_plan='" . $this->db->escape_str($id) . "'")->result();
            $db2 = $this->load->database('accounting', TRUE);
            $datacoa    = $db2->query("SELECT no_perkiraan, nama FROM coa_master WHERE level='5' ORDER BY no_perkiraan ASC")->result_array();
            $penyusutan = $db2->query("SELECT no_perkiraan as coa, nama as keterangan FROM coa_master WHERE level='5' AND (nama LIKE 'DEPRECIATION%') ORDER BY no_perkiraan ASC")->result_array();
            $list_dept  = $this->db->get('department')->result_array();
            $list_cost  = $this->db->get_where('costcenter', array('deleted' => 'N'))->result_array();

            $data = array(
                'title'      => 'Approve Budget Asset',
                'action'     => 'approve',
                'header'     => $header,
                'datacoa'    => $datacoa,
                'penyusutan' => $penyusutan,
                'list_dept'  => $list_dept,
                'list_cost'  => $list_cost,
                'id'         => $id
            );

            $this->template->title('Approve Budget Asset');
            $this->template->render('approve', $data);
        }
    }

    public function hapus_asset()
    {
        $id     = $this->uri->segment(3);
        $result = $this->budget_model->hapus_asset($id);
        echo json_encode($result);
    }
}
