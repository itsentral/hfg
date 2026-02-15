<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class App_pr_material_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Approval_PR_Material.Add');
        $this->ENABLE_MANAGE  = has_permission('Approval_PR_Material.Manage');
        $this->ENABLE_VIEW    = has_permission('Approval_PR_Material.View');
        $this->ENABLE_DELETE  = has_permission('Approval_PR_Material.Delete');
    }

    public function get_json_data_approval()
    {
        $req = $_REQUEST;
        $fetch = $this->get_query_data_approval(
            // $req['material'],
            $req['costcenter'],
            $req['search']['value'],
            $req['order'][0]['column'],
            $req['order'][0]['dir'],
            $req['start'],
            $req['length']
        );

        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];
        $data          = [];
        $GET_USER      = get_list_user();

        $urut = $req['start'] + 1;

        foreach ($query->result_array() as $row) {
            $status = $this->_get_approval_status($row);

            $nama_user = $GET_USER[$row['booking_by']]['nama'] ?? '-';

            // tombol view => trigger modal
            $view_btn = "<button type='button' class='btn-icon btn-icon-view detail'
                    data-so_number='{$row['so_number']}' title='Detail'>
                    <i class='fa fa-eye'></i>
                </button>";

            $approve_btn = '';
            $getCheck = $this->db->get_where('material_planning_base_on_produksi_detail', [
                'so_number'   => $row['so_number'],
                'status_app'  => 'N'
            ])->num_rows();

            if ($this->ENABLE_MANAGE && $getCheck > 0 && $row['reject_status'] !== '1') {
                $approve_btn = "<a class='btn-icon btn-icon-approve' title='Approve'
                            href='" . site_url($this->uri->segment(1)) . "/approval_planning/{$row['so_number']}/3'>
                            <i class='fa fa-check'></i>
                        </a>";
            }

            $nestedData = [
                "<div class='text-center'>{$urut}</div>",
                "<div class='text-start'>MATERIAL PLANNING " . strtoupper($row['so_number']) . "</div>",
                "<div class='text-start'>" . strtoupper($row['so_number']) . "</div>",
                "<div class='text-center'>" . strtoupper($row['no_pr']) . "</div>",
                "<div class='text-start'>" . strtoupper($row['project']) . "</div>",
                "<div class='text-start'>" . ucwords(strtolower($nama_user)) . "</div>",
                "<div class='text-start'>" . date('d-M-Y', strtotime($row['booking_date'])) . "</div>",
                $status,
                "<div class='text-center'>{$view_btn} {$approve_btn}</div>"
            ];

            $data[] = $nestedData;
            $urut++;
        }


        echo json_encode([
            "draw"            => intval($req['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }


    public function get_query_data_approval($costcenter, $like = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $columns_order_by = [
            0 => 'a.so_number',
            1 => 'a.so_number',
            2 => 'a.no_pr',
            3 => 'a.project',
            4 => 'a.booking_date'
        ];

        $this->db->start_cache();

        $this->db->select("a.*, b.name_customer", false)
            ->from('material_planning_base_on_produksi a')
            ->join('material_planning_base_on_produksi_detail z', 'a.so_number = z.so_number')
            ->join('master_customers b', 'a.id_customer = b.id_customer', 'left')
            ->where_in('a.category', ['pr material', 'base on production'])
            ->where('a.reject_status', '0')
            ->where('a.app_post', '3')
            ->where('z.status_app', 'N')
            ->where('a.close_pr IS NULL', null, false)
            ->where('a.booking_date IS NOT NULL', null, false)
            ->where('a.app_3 IS NULL', null, false)
            ->where('a.sts_reject3 IS NULL', null, false);

        if ($costcenter !== '0') {
            $this->db->where('a.costcenter', $costcenter);
        }

        // if ($material !== '0') {
        //     $this->db->where('z.code_lv1', $material);
        // }

        if (!empty($like)) {
            $this->db->group_start()
                ->like('b.name_customer', $like)
                ->or_like('a.so_number', $like)
                ->or_like('a.project', $like)
                ->or_like('a.no_pr', $like)
                ->group_end();
        }

        $this->db->group_by('a.so_number');

        $this->db->stop_cache();

        $totalFiltered = $this->db->count_all_results();

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('a.booking_date', 'DESC');
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $query = $this->db->get();
        $this->db->flush_cache();

        return [
            'totalData'     => $totalFiltered,
            'totalFiltered' => $totalFiltered,
            'query'         => $query
        ];
    }


    private function _get_approval_status($row)
    {
        if ($row['sts_reject3'] === '1') {
            return "<div class='text-start'><span class='badge bg-danger'>Rejected by Management</span></div>";
        }

        if ($row['app_3'] === '1') {
            return "<div class='text-start'><span class='badge bg-success'>Approved</span></div>";
        }

        return "<div class='text-start'><span class='badge bg-primary'>Waiting Approval Management</span></div>";
    }
}
