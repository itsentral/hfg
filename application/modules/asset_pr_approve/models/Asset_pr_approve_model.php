<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'modules/approval_pr_asset/models/Approval_pr_asset_model.php';

class Asset_pr_approve_model extends Approval_pr_asset_model
{
    public function __construct()
    {
        parent::__construct();
    }
}
