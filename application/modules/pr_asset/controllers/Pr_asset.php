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
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $data = array(
            'title' => 'PR Asset Management',
            'tanda' => ''
        );
        // legacy history view removed
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
        $sql = "SELECT a.*, b.tgl_pr, b.created_by as pr_by, b.created_date as pr_date, b.app_status_3 as header_app_status_3, b.app_reason_3 as header_app_reason_3 FROM tran_pr_detail a LEFT JOIN tran_pr_header b ON a.no_pr = b.no_pr WHERE a.category='asset' AND a.no_pr = '" . $this->db->escape_str($no_pr) . "'";
        $result = $this->db->query($sql)->result_array();
        $result_header = $this->db->get_where('tran_pr_header', array('no_pr' => $no_pr))->row_array();

        $data = array(
            'no_pr'         => $no_pr,
            'result'        => $result,
            'result_header' => $result_header
        );
        $this->load->view('pr_asset/view_pr', $data);
    }

    public function close_pr_modal()
    {
        $no_pr = $this->input->post('no_pr');
        $this->template->set('no_pr', $no_pr);
        $this->template->render('close_pr_modal');
    }

    public function close_pr()
    {
        $this->auth->restrict($this->deletePermission);
        $no_pr = $this->input->post('no_pr');
        $close_pr_reason = $this->input->post('close_pr_reason');

        $old_pr_header = $this->db->get_where('tran_pr_header', ['no_pr' => $no_pr])->row_array();
        $update_data   = ['close_pr' => 1, 'close_pr_desc' => $close_pr_reason];

        $this->db->trans_start();
        $this->db->update('tran_pr_header', $update_data, ['no_pr' => $no_pr]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
            write_log('PR Asset', 'Close PR', 'Close PR asset failed: ' . $no_pr, $this->input->post(), null, 0);
        } else {
            $this->db->trans_commit();
            $valid = 1;
            $log_payload = $old_pr_header ? ['old' => $old_pr_header, 'new' => array_merge($old_pr_header, $update_data)] : $update_data;
            write_log('PR Asset', 'Close PR', 'Close PR asset success: ' . $no_pr, $log_payload, null, 1);
        }

        echo json_encode([
            'status' => $valid,
            'msg'    => ($valid == 1) ? 'PR Asset closed successfully!' : 'Failed to close PR Asset.'
        ]);
    }

    public function approval_management()
    {
        if (!has_permission('Approval_PR_Asset_Management.View') && !has_permission($this->managePermission)) {
            $this->auth->restrict('Approval_PR_Asset_Management.View');
        }
        $this->Pr_asset_model->approval_management();
    }

    public function approve_pr()
    {
        if (!has_permission('Approval_PR_Asset_Management.Manage') && !has_permission($this->managePermission) && !has_permission($this->addPermission)) {
            echo json_encode(['status' => 0, 'pesan' => 'Unauthorized']);
            return;
        }
        $this->Pr_asset_model->approve_pr();
    }
}

