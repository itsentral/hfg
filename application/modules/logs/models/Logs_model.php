<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logs_model extends CI_Model
{
    protected $table = 'logs';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get distinct list of all modules (Master list + any dynamic modules in DB)
     */
    public function get_distinct_modules()
    {
        $system_modules = [
            'approval_mutasi', 'approval_po', 'approval_pr_asset', 'approval_pr_material',
            'asset', 'asset_category', 'asset_coa', 'asset_depreciation',
            'auth', 'bom', 'budget', 'department', 'finalize_incoming',
            'gl_interface', 'hscode', 'incoming', 'master_coating', 'master_customers',
            'master_definisi', 'master_employee', 'master_forwarding_cost', 'master_jurnal_mapping',
            'master_rate_labor', 'master_sound', 'master_tensile', 'master_top',
            'material_category', 'material_jenis', 'material_master', 'material_type',
            'metode_pembelian', 'new_ros', 'packing', 'pembayaran_material',
            'pengajuan_mutasi', 'pr_asset', 'pr_material', 'price_ref_raw_material',
            'price_sup_raw_material', 'product_category', 'product_jenis', 'product_master',
            'product_type', 'purchase_order', 'purchase_order_payment', 'report_payment_po',
            'request_list', 'request_payment', 'spk_material', 'stock_from_warehouse',
            'supplier', 'unit', 'warehouse'
        ];

        $db_modules = [];
        $query = $this->db->select('module')
                          ->distinct()
                          ->where('module IS NOT NULL')
                          ->where('module !=', '')
                          ->get($this->table);
        if ($query) {
            foreach ($query->result() as $row) {
                if (!empty($row->module)) {
                    $db_modules[] = strtolower(trim($row->module));
                }
            }
        }

        $merged = array_unique(array_merge($system_modules, $db_modules));
        sort($merged, SORT_STRING);

        return $merged;
    }

    /**
     * Build base query with filters
     */
    private function _build_query($filters = [])
    {
        $this->db->from($this->table);

        if (!empty($filters['start_date'])) {
            $this->db->where('created_at >=', $filters['start_date'] . ' 00:00:00');
        }

        if (!empty($filters['end_date'])) {
            $this->db->where('created_at <=', $filters['end_date'] . ' 23:59:59');
        }

        if (!empty($filters['module'])) {
            $this->db->where('LOWER(module)', strtolower(trim($filters['module'])));
        }

        if (!empty($filters['status'])) {
            $st = strtoupper(trim($filters['status']));
            if ($st === 'SUCCESS') {
                $this->db->where('status', 1);
            } elseif ($st === 'FAILED') {
                $this->db->where('status', 0);
            }
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $this->db->group_start();
            $this->db->like('message', $search);
            $this->db->or_like('username', $search);
            $this->db->or_like('methode', $search);
            $this->db->or_like('module', $search);
            $this->db->or_like('ip_address', $search);
            $this->db->or_like('sql', $search);
            $this->db->group_end();
        }
    }

    /**
     * Get paginated logs
     */
    public function get_logs($filters = [], $limit = 50, $offset = 0, $sort = null)
    {
        $this->_build_query($filters);

        if (!empty($sort['colId']) && !empty($sort['sort'])) {
            $sortColMap = [
                'id'         => 'id',
                'created_at' => 'created_at',
                'user_name'  => 'username',
                'username'   => 'username',
                'module'     => 'module',
                'action'     => 'methode',
                'status'     => 'status'
            ];
            $col = isset($sortColMap[$sort['colId']]) ? $sortColMap[$sort['colId']] : 'id';
            $dir = strtolower($sort['sort']) === 'asc' ? 'asc' : 'desc';
            $this->db->order_by($col, $dir);
        } else {
            $this->db->order_by('id', 'desc');
        }

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    /**
     * Count total matching logs
     */
    public function count_logs($filters = [])
    {
        $this->_build_query($filters);
        return $this->db->count_all_results();
    }

    /**
     * Get single log by ID
     */
    public function get_log_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }
}
