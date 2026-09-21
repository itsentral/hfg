<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'modules/approval_pr_asset/controllers/Approval_pr_asset.php';

class Asset_pr_approve extends Approval_pr_asset
{
    public function __construct()
    {
        parent::__construct();
    }
}
