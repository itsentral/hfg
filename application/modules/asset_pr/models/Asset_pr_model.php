<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'modules/pr_asset/models/Pr_asset_model.php';

class Asset_pr_model extends Pr_asset_model
{
    public function __construct()
    {
        parent::__construct();
    }
}
