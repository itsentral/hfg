<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Closed_po extends Admin_Controller
{
    //Permission
    protected $viewPermission     = 'Closed_PO.View';
    protected $addPermission      = 'Closed_PO.Add';
    protected $managePermission = 'Closed_PO.Manage';
    protected $deletePermission = 'Closed_PO.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Closed_po/Closed_po_model',
            'Aktifitas/aktifitas_model',
        ));
        $this->template->title('Closed PO');
        $this->template->page_icon('fa fa-building-o');

        date_default_timezone_set('Asia/Bangkok');
    }

    // public function index()
    // {
    //     $this->auth->restrict($this->viewPermission);
    //     $session = $this->session->userdata('app_session');
    //     $this->template->page_icon('fa fa-users');

    //     $get_data = $this->db->query("
    //         SELECT 
    //             a.*, 
    //             b.nm_lengkap as nm_create, 
    //             d.so_number,
    //             f.no_pr as no_pr_material,
    //             e.no_pr as no_pr_depart,
    //             h.nama as nm_supplier,
    //             IF(SUM(j.jumlahharga) IS NULL, 0, SUM(j.jumlahharga)) as harga_po
    //         FROM 
    //             tr_purchase_order as a 
    //             LEFT JOIN users b ON b.id_user = a.created_by 
    //             LEFT JOIN dt_trans_po c ON c.no_po = a.no_po 
    //             LEFT JOIN material_planning_base_on_produksi_detail d ON d.id = c.idpr AND (c.tipe IS NULL OR c.tipe = '')
    //             LEFT JOIN material_planning_base_on_produksi f ON f.so_number = d.so_number AND (c.tipe IS NULL OR c.tipe = '')
    //             LEFT JOIN rutin_non_planning_detail e ON e.id = c.idpr AND c.tipe = 'pr depart'
    //             LEFT JOIN rutin_non_planning_header g ON g.no_pengajuan = e.no_pengajuan
    //             LEFT JOIN new_supplier h ON h.kode_supplier = a.id_suplier
    //             LEFT JOIN dt_trans_po j ON j.no_po = a.no_po
    //         WHERE
    //             a.close_po IS NOT NULL
    //         GROUP BY a.no_po
    //         ORDER BY a.no_po DESC
    //     ")->result();

    //     $this->template->set('results', $get_data);
    //     $this->template->render('index');
    // }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
        $this->template->page_icon('fa fa-users');

        $get_data = $this->db->query("
            SELECT 
                a.*, 
                b.nm_lengkap as nm_create, 
                d.so_number,
                f.no_pr as no_pr_material,
                e.no_pr as no_pr_depart,
                h.nama as nm_supplier,
                IF(SUM(j.jumlahharga) IS NULL, 0, SUM(j.jumlahharga)) as harga_po
            FROM 
                tr_purchase_order as a 
                LEFT JOIN users b ON b.id_user = a.created_by 
                LEFT JOIN dt_trans_po c ON c.no_po = a.no_po 
                LEFT JOIN material_planning_base_on_produksi_detail d ON d.id = c.idpr AND (c.tipe IS NULL OR c.tipe = '')
                LEFT JOIN material_planning_base_on_produksi f ON f.so_number = d.so_number AND (c.tipe IS NULL OR c.tipe = '')
                LEFT JOIN rutin_non_planning_detail e ON e.id = c.idpr AND c.tipe = 'pr depart'
                LEFT JOIN rutin_non_planning_header g ON g.no_pengajuan = e.no_pengajuan
                LEFT JOIN new_supplier h ON h.kode_supplier = a.id_suplier
                LEFT JOIN dt_trans_po j ON j.no_po = a.no_po
            WHERE
                a.close_po IS NOT NULL
            GROUP BY a.no_po
            ORDER BY a.no_po DESC
        ")->result();

        $list_no_incoming = [];

        // Mengambil data dari tabel ROS (Receipt of Shipment / Incoming)
        // GROUP_CONCAT digunakan jika 1 PO ternyata dikirim sebagian-sebagian (parsial)
        $get_incoming = $this->db->query("
        SELECT no_po, GROUP_CONCAT(id SEPARATOR ', ') as no_incoming 
        FROM tr_ros_header 
        GROUP BY no_po
        ")->result();

        foreach ($get_incoming as $inc) {
            $list_no_incoming[$inc->no_po] = $inc->no_incoming;
        }

        // Failsafe: Pastikan semua PO yang ditarik punya key di array ini
        // Supaya terhindar dari pesan error "Undefined offset/index" di View
        foreach ($get_data as $row) {
            if (!isset($list_no_incoming[$row->no_po])) {
                $list_no_incoming[$row->no_po] = '-';
            }
        }
        // =========================================================================

        $this->template->set('results', $get_data);
        $this->template->set('list_no_incoming', $list_no_incoming);
        $this->template->render('index');
    }

    public function view_po($no_po)
{
    $session = $this->session->userdata('app_session');

    $get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();

    // Query sudah disamakan dengan fungsi edit()
    $getitemso = $this->db->query("
        SELECT 
            a.id as id,
            a.idpr as idpr,
            a.no_po as no_po,
            a.idmaterial as idmaterial,
            a.qty as qty,
            a.hargasatuan as hargasatuan,
            a.jumlahharga as jumlahharga,
            a.kode_barang as kode_barang,
            a.ppn as ppn,
            a.ppn_persen as ppn_persen,
            a.harga_total as harga_total,
            a.tipe as tipe_pr,
            a.keterangan as keterangan,
            (b.qty_stock - b.qty_booking) AS avl_stock, 
            a.kode_barang as code, 
            '' as code1, 
            a.namamaterial as nm_material, 
            '' as nm_material1,
            a.persen_disc as persen_disc,
            a.nilai_disc as nilai_disc,
            e.propose_purchase as propose_purchase,
            g.code as packing_unit,
            h.code as packing_unit2,
            IF(i.code IS NOT NULL, i.code, j.code) as unit_measure,
            c.hscode,
            hs.kuota_internal,
            hs.local_code as local_code,
            c.trade_name as nm_lain
        FROM dt_trans_po a
        LEFT JOIN warehouse_stock b ON b.id_material = a.idmaterial
        LEFT JOIN new_inventory_4 c ON c.code_lv4 = a.idmaterial OR c.id = a.idmaterial
        LEFT JOIN material_planning_base_on_produksi_detail e ON e.id = a.idpr
        LEFT JOIN accessories f ON f.id = a.idmaterial
        LEFT JOIN ms_satuan g ON g.id = c.id_unit_packing
        LEFT JOIN ms_satuan h ON h.id = f.id_unit_gudang
        LEFT JOIN ms_satuan i ON i.id = c.id_unit
        LEFT JOIN ms_satuan j ON j.id = f.id_unit
        LEFT JOIN hscode hs  ON hs.id = c.hscode
        WHERE a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND (a.tipe IS NULL OR a.tipe = 'pr material')
        GROUP BY id

        UNION ALL

        SELECT 
            a.id as id, a.idpr as idpr, a.no_po as no_po, '' as idmaterial, a.qty as qty, a.hargasatuan as hargasatuan, a.jumlahharga as jumlahharga, a.kode_barang as kode_barang, a.ppn as ppn, a.ppn_persen as ppn_persen, a.harga_total as harga_total, a.tipe as tipe_pr, a.keterangan as keterangan, '0' AS avl_stock, a.kode_barang as code, '' as code1, a.namamaterial as nm_material, '' as nm_material1, a.persen_disc as persen_disc, a.nilai_disc as nilai_disc, a.qty as propose_purchase, IF(f.code IS NULL, 'Pcs', f.code) as packing_unit, '' as packing_unit2, IF(f.code IS NULL, 'Pcs', f.code) as unit_measure, '' as hscode, 0 as kuota_internal, '' as local_code, '' as nm_lain
        FROM dt_trans_po a
        LEFT JOIN rutin_non_planning_detail e ON e.id = a.idpr
        LEFT JOIN ms_satuan f ON f.id = e.satuan
        WHERE a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND a.tipe = 'pr depart'
        
        UNION ALL

        SELECT 
            a.id as id, a.idpr as idpr, a.no_po as no_po, '' as idmaterial, a.qty as qty, a.hargasatuan as hargasatuan, a.jumlahharga as jumlahharga, a.kode_barang as kode_barang, a.ppn as ppn, a.ppn_persen as ppn_persen, a.harga_total as harga_total, a.tipe as tipe_pr, a.keterangan as keterangan, '0' AS avl_stock, a.kode_barang as code, '' as code1, a.namamaterial as nm_material, '' as nm_material1, a.persen_disc as persen_disc, a.nilai_disc as nilai_disc, a.qty as propose_purchase, 'Pcs' as packing_unit, '' as packing_unit2, 'Pcs' as unit_measure, '' as hscode, 0 as kuota_internal, '' as local_code, '' as nm_lain
        FROM dt_trans_po a
        LEFT JOIN rutin_non_planning_detail e ON e.id = a.idpr
        WHERE a.no_po IN ('" . str_replace(",", "','", $no_po) . "') AND a.tipe = 'pr asset'
        GROUP BY id
    ")->result();

    $customers = $this->db->get_where('customer', ['deleted_by' => null])->result();
    $mata_uang = $this->db->get_where('mata_uang', ['deleted' => null])->result();
    $list_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result();
    $list_department = $this->db->select('id, nama')->get_where('ms_department', ['deleted_by' => null])->result();
    $list_group_top = $this->db->get_where('list_help', ['group_by' => 'top', 'sts' => 'Y'])->result();
    $term = $this->db->get_where('list_help', ['group_by' => 'top invoice', 'sts' => 'Y'])->result();

    $this->db->select('a.*, b.no_credit, b.issue_date, b.expiry_date, b.value_contract, b.tolerance_plus, b.tolerance_minus, b.type_of_lc, b.valid_usen_until, b.bank_sender, b.bank_receiver, b.latest_shipment, b.no_sales_contract');
    $this->db->from('tr_top_po a');
    $this->db->join('tr_po_detail_lc b', 'a.id = b.id_top', 'left');
    $this->db->where('a.no_po', $no_po);
    $list_top = $this->db->get()->result();
    $num_top = count($list_top);

    $nm_depart = [];
    $get_nm_depart = $this->db->query("SELECT nama FROM ms_department WHERE id IN ('" . str_replace(",", "','", $get_po->id_dept) . "')")->result();
    if (!empty($get_nm_depart)) {
        foreach ($get_nm_depart as $item_depart) {
            $nm_depart[] = strtoupper($item_depart->nama);
        }
    }
    
    $nm_depart = !empty($nm_depart) ? implode(', ', $nm_depart) : '';

    $data = [
        'customers'       => $customers,
        'mata_uang'       => $mata_uang,
        'get_po'          => $get_po,
        'getitemso'       => $getitemso,
        'list_supplier'   => $list_supplier,
        'list_department' => $list_department,
        'nm_depart'       => $nm_depart,
        'list_top'        => $list_top,
        'list_group_top'  => $list_group_top,
        'num_po'          => $num_top,
        'term'            => $term
    ];

    $this->template->set('results', $data);
    $this->template->title('Detail Purchase Order');
    $this->template->render('view_po');
}
}
