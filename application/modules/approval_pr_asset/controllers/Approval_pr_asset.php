<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Approval_pr_asset extends Admin_Controller
{
    protected $viewPermission   = 'Approval_PR_Asset.View';
    protected $managePermission = 'Approval_PR_Asset.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('approval_pr_asset/Approval_pr_asset_model', 'approval_pr_asset_model');
        $this->load->model('master_model');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $data = array(
            'title' => 'Approval PR Asset'
        );
        // legacy history view removed
        $this->template->title('Approval PR Asset');
        $this->template->render('index', $data);
    }

    public function server_side_approval()
    {
        $this->approval_pr_asset_model->get_data_json_approval();
    }

    public function approve_pr()
    {
        $this->approval_pr_asset_model->approve_pr();
    }
}
