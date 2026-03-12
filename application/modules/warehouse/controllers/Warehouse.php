<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Master Warehouse
 */
$status = array();
class Warehouse extends Admin_Controller
{
    //Permission
    protected $viewPermission       = 'Warehouse.View';
    protected $addPermission        = 'Warehouse.Add';
    protected $managePermission     = 'Warehouse.Manage';
    protected $deletePermission     = 'Warehouse.Delete';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Warehouse/Warehouse_model', 'All/All_model'));
        $this->template->title('Gudang');
        $this->template->page_icon('fa fa-dollar');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Material Stock');
        $this->template->page_icon('fa fa-cubes');
        $this->template->render('index');
    }

    public function kartu_stok()
    {
        $this->template->title('Kartu Stok');
        $this->template->page_icon('fa fa-file');
        $this->template->render('kartu_stok');
    }

    // SERVER SIDE
    public function data_side_warehouse_stock()
    {
        $this->Warehouse_model->get_json_warehouse_stock();
    }

    public function data_side_kartu_stok()
    {
        $this->Warehouse_model->get_json_kartu_stok();
    }
}
