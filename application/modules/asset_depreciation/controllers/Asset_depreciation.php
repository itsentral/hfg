<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Asset_depreciation extends Admin_Controller
{
    protected $viewPermission   = 'Asset_depreciation.View';
    protected $addPermission    = 'Asset_depreciation.Add';
    protected $managePermission = 'Asset_depreciation.Manage';
    protected $deletePermission = 'Asset_depreciation.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('asset_depreciation/Asset_depreciation_model', 'asset_depreciation_model');
        $this->load->model('master_model');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data = array(
            'title'    => 'Index Of Depreciation Assets',
            'action'   => 'asset',
            'bulan_'   => date('m'),
            'kategori' => $this->asset_depreciation_model->getList('asset_category')
        );

        history('View index asset depreciation');
        $this->template->title('Index Of Depreciation Assets');
        $this->template->render('index', $data);
    }

    public function data_side_depreciation()
    {
        $this->asset_depreciation_model->data_side_depreciation();
    }
}
