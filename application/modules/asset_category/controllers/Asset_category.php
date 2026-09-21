<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Asset_category extends Admin_Controller
{
    protected $viewPermission   = 'Asset_category.View';
    protected $addPermission    = 'Asset_category.Add';
    protected $managePermission = 'Asset_category.Manage';
    protected $deletePermission = 'Asset_category.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('asset_category/Asset_category_model', 'asset_category_model');
        $this->load->model('master_model');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data_Group = $this->master_model->getArray('groups', array(), 'id', 'name');
        $data = array(
            'title'     => 'Index Of Asset Category',
            'action'    => 'asset_category',
            'row_group' => $data_Group
        );

        history('View Data Master Asset category');
        $this->template->title('Index Of Asset Category');
        $this->template->render('index', $data);
    }

    public function data_side_category()
    {
        $this->asset_category_model->get_json_category();
    }

    public function add_category()
    {
        if ($this->input->post()) {
            $Arr_Kembali  = array();
            $data         = $this->input->post();
            $dateTime     = date('Y-m-d H:i:s');
            $username     = (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin')));

            $id          = isset($data['id']) ? $data['id'] : '';
            $nm_category = strtoupper($data['nm_category']);
            $status      = isset($data['status']) ? $data['status'] : 'Y';

            if (empty($id)) {
                $ArrHeader = array(
                    'nm_category'  => $nm_category,
                    'status'       => $status,
                    'created_by'   => $username,
                    'created_date' => $dateTime
                );
                $TandaI = "Insert";
            } else {
                $ArrHeader = array(
                    'nm_category'  => $nm_category,
                    'status'       => $status,
                    'updated_by'   => $username,
                    'updated_date' => $dateTime
                );
                $TandaI = "Update";
            }

            $this->db->trans_start();
            if (empty($id)) {
                $this->db->insert('asset_category', $ArrHeader);
            } else {
                $this->db->where('id', $id);
                $this->db->update('asset_category', $ArrHeader);
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
                history($TandaI . ' Category Asset ' . $id . ' / ' . $nm_category);
            }

            echo json_encode($Arr_Kembali);
        } else {
            $id     = $this->uri->segment(3);
            $header = $this->db->get_where('asset_category', array('id' => $id))->result();

            $data = array(
                'title'  => 'Add Category Asset',
                'action' => 'add',
                'header' => $header
            );
            $this->load->view('asset_category/add_category', $data);
        }
    }

    public function hapus_category()
    {
        $id       = $this->uri->segment(3);
        $username = (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin')));

        $ArrPlant = array(
            'deleted'      => 'Y',
            'deleted_by'   => $username,
            'deleted_date' => date('Y-m-d H:i:s')
        );

        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->update('asset_category', $ArrPlant);
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
            history('Delete Category Asset : ' . $id);
        }
        echo json_encode($Arr_Data);
    }

    public function modal_jurnal()
    {
        $this->load->view('asset_category/modal_jurnal');
    }
}
