<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Asset_coa extends Admin_Controller
{
    protected $viewPermission   = 'Asset_coa.View';
    protected $addPermission    = 'Asset_coa.Add';
    protected $managePermission = 'Asset_coa.Manage';
    protected $deletePermission = 'Asset_coa.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('asset_coa/Asset_coa_model', 'asset_coa_model');
        $this->load->model('master_model');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data_Group = $this->master_model->getArray('groups', array(), 'id', 'name');
        $data = array(
            'title'     => 'Indeks Of Asset COA',
            'action'    => 'category',
            'row_group' => $data_Group
        );

        history('View Data Asset COA');
        $this->template->title('Indeks Of Asset COA');
        $this->template->render('index', $data);
    }

    public function data_side_asset_coa()
    {
        $this->asset_coa_model->get_json_asset_coa();
    }

    public function add_asset_coa()
    {
        if ($this->input->post()) {
            $Arr_Kembali = array();
            $data        = $this->input->post();

            $id          = isset($data['id']) ? $data['id'] : '';
            $keterangan  = strtoupper($data['keterangan']);
            $coa         = isset($data['coa']) ? $data['coa'] : '';
            $coa_kredit  = isset($data['coa_kredit']) ? $data['coa_kredit'] : '';
            $status      = isset($data['status']) ? $data['status'] : 'Y';
            if (empty($status)) $status = 'Y';

            $ArrHeader = array(
                'keterangan' => $keterangan,
                'coa'        => $coa,
                'coa_kredit' => $coa_kredit,
                'status'     => $status,
            );

            $TandaI = empty($id) ? "Insert" : "Update";

            $this->db->trans_start();
            if (empty($id)) {
                $this->db->insert('asset_coa', $ArrHeader);
            } else {
                $this->db->where('id', $id);
                $this->db->update('asset_coa', $ArrHeader);
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $Arr_Kembali = array(
                    'pesan'  => $TandaI . ' data failed. Please try again later ...',
                    'status' => 0
                );
            } else {
                $this->db->trans_commit();
                $Arr_Kembali = array(
                    'pesan'  => $TandaI . ' data success. Thanks ...',
                    'status' => 1
                );
                history($TandaI . ' Asset COA ' . $id . ' / ' . $keterangan);
            }
            echo json_encode($Arr_Kembali);
        } else {
            $id = $this->uri->segment(3);
            $header = $this->db->get_where('asset_coa', array('id' => $id))->result();

            // Connect to accounting DB for COA table
            $acc_db  = $this->load->database('accounting', TRUE);
            $datacoa = $acc_db->query("SELECT a.no_perkiraan, a.nama FROM coa a WHERE a.level='5' ORDER BY a.no_perkiraan ASC")->result_array();

            $coalist = array('' => '-- Select COA --');
            if (!empty($datacoa)) {
                foreach ($datacoa as $val) {
                    $coalist[$val['no_perkiraan']] = $val['no_perkiraan'] . ' - ' . $val['nama'];
                }
            }

            $data = array(
                'header'  => $header,
                'data'    => $header,
                'datacoa' => $datacoa,
                'coalist' => $coalist
            );

            // Load view directly for AJAX modal
            $this->load->view('asset_coa/add_asset_coa', $data);
        }
    }

    public function hapus_asset_coa()
    {
        $id = $this->uri->segment(3);

        $ArrPlant = array(
            'status' => 'N',
        );

        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->update('asset_coa', $ArrPlant);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data = array(
                'pesan'  => 'Delete data failed. Please try again later ...',
                'status' => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data = array(
                'pesan'  => 'Delete data success. Thanks ...',
                'status' => 1
            );
            history('Delete Asset COA ' . $id);
        }
        echo json_encode($Arr_Data);
    }
}
