<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pr_asset extends Admin_Controller
{
    protected $viewPermission   = 'PR_Asset.View';
    protected $addPermission    = 'PR_Asset.Add';
    protected $managePermission = 'PR_Asset.Manage';
    protected $deletePermission = 'PR_Asset.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('pr_asset/Pr_asset_model', 'Pr_asset_model');
        $this->load->model('master_model');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $data = array(
            'title' => 'PR Asset Management',
            'tanda' => ''
        );
        history("View index PR Asset");
        $this->template->title('PR Asset');
        $this->template->render('pr', $data);
    }

    public function pr()
    {
        $this->index();
    }

    public function server_side_pr_asset()
    {
        $this->Pr_asset_model->get_data_json_pr_asset();
    }

    public function add_pr()
    {
        if ($this->input->post()) {
            $this->Pr_asset_model->add_pr();
        } else {
            $this->auth->restrict($this->addPermission);
            $data = array(
                'title' => 'Add PR Asset'
            );
            $this->template->title('Add PR Asset');
            $this->template->render('add_pr', $data);
        }
    }

    public function server_side_add_pr_asset()
    {
        $this->Pr_asset_model->get_data_json_add_pr_asset();
    }

    public function print_pr_asset()
    {
        $no_pr = $this->uri->segment(3);
        $sql = "SELECT a.* FROM tran_pr_detail a LEFT JOIN tran_pr_header b ON a.no_pr = b.no_pr WHERE a.category='asset' AND a.no_pr = '" . $this->db->escape_str($no_pr) . "'";
        $result = $this->db->query($sql)->result_array();
        $result_header = $this->db->get_where('tran_pr_header', array('no_pr' => $no_pr))->row_array();

        $data = array(
            'no_pr'         => $no_pr,
            'result'        => $result,
            'result_header' => $result_header
        );
        $this->load->view('pr_asset/print_pr_asset', $data);
    }

    public function view_pr()
    {
        $no_pr = $this->uri->segment(3);
        $sql = "SELECT a.*, b.tgl_pr, b.created_by as pr_by, b.created_date as pr_date FROM tran_pr_detail a LEFT JOIN tran_pr_header b ON a.no_pr = b.no_pr WHERE a.category='asset' AND a.no_pr = '" . $this->db->escape_str($no_pr) . "'";
        $result = $this->db->query($sql)->result_array();
        $result_header = $this->db->get_where('tran_pr_header', array('no_pr' => $no_pr))->row_array();

        $data = array(
            'no_pr'         => $no_pr,
            'result'        => $result,
            'result_header' => $result_header
        );
        $this->load->view('pr_asset/view_pr', $data);
    }
}
