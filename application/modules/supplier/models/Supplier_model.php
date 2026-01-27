<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Supplier_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();

		$this->ENABLE_ADD     = has_permission('Master_Supplier.Add');
		$this->ENABLE_MANAGE  = has_permission('Master_Supplier.Manage');
		$this->ENABLE_VIEW    = has_permission('Master_Supplier.View');
		$this->ENABLE_DELETE  = has_permission('Master_Supplier.Delete');
	}

	public function get_data($table, $where_field = '', $where_value = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function get_data_where_array($table, $where)
	{
		if (!empty($where)) {
			$query = $this->db->get_where($table, $where);
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function get_data_group($table, $where_field = '', $where_value = '', $where_group = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->group_by($where_group)->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function get_json_supplier()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_supplier(
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		$data	= array();

		$urut1  = 1;
		$urut2  = 0;
		foreach ($query->result_array() as $row) {

			$total_data = $totalData;
			$start_dari = $requestData['start'];
			$asc_desc   = $requestData['order'][0]['dir'];

			if ($asc_desc == 'asc') {
				$nomor = $urut1 + $start_dari;
			} else {
				$nomor = ($total_data - $start_dari) - $urut2;
			}

			$nestedData = [];

			// # (center)
			$nestedData[] = "<div class='text-center'>{$nomor}</div>";

			// Supplier Name
			$nestedData[] = "<div class='fw-semibold'>" . strtoupper(strtolower($row['nama'])) . "</div>";

			// Country
			$nestedData[] = "<div>" . strtoupper(strtolower($row['nm_country'])) . "</div>";

			// Telp
			$nestedData[] = "<div class='text-nowrap'>" . strtoupper(strtolower($row['telp'])) . "</div>";

			// Fax
			$nestedData[] = "<div class='text-nowrap'>" . strtoupper(strtolower($row['fax'])) . "</div>";

			// Email
			$nestedData[] = "<div class='text-lowercase text-nowrap'>" . strtolower($row['email']) . "</div>";

			// Last By
			$last_create = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
			$nestedData[] = "<div class='text-nowrap'>" . strtoupper(get_name('users', 'nm_lengkap', 'id_user', $last_create)) . "</div>";

			// Last Date
			$last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];
			$nestedData[] = "<div class='text-center text-nowrap'>" . date('d-M-Y H:i', strtotime($last_date)) . "</div>";

			// Actions (right aligned)
			$edit = "";
			$delete = "";

			if ($this->ENABLE_MANAGE) {
				$edit = "<a href='javascript:void(0)'
                    data-id='{$row['id']}'
                    class='btn-icon btn-icon-edit edit_supplier'
                    title='Edit'>
                    <i class='ti ti-edit'></i>
                </a>";
			}

			if ($this->ENABLE_DELETE) {
				$delete = "<a href='javascript:void(0)'
                    data-id='{$row['id']}'
                    class='btn-icon btn-icon-delete delete'
                    title='Delete'>
                    <i class='ti ti-trash'></i>
                </a>";
			}

			$nestedData[] = "<div class='text-end d-inline-flex gap-1'>
                        {$edit}
                        {$delete}
                    </div>";

			$data[] = $nestedData;

			$urut1++;
			$urut2++;
		}


		$json_data = array(
			"draw"            	=> intval($requestData['draw']),
			"recordsTotal"    	=> intval($totalData),
			"recordsFiltered" 	=> intval($totalFiltered),
			"data"            	=> $data
		);

		echo json_encode($json_data);
	}

	public function get_query_json_supplier($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$sql = "SELECT
					(@row:=@row+1) AS nomor,
					a.*,
					b.name AS nm_country
				FROM
					new_supplier a
					LEFT JOIN country_all b ON a.id_country=b.iso3,
					(SELECT @row:=0) r
				WHERE 1=1 AND a.deleted_date IS NULL AND 
					(
						a.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.telp LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.email LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR b.name LIKE '%" . $this->db->escape_like_str($like_value) . "%'
					)
		";
		// echo "<pre>$sql</pre>";
		// exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'nama',
			2 => 'b.name',
			3 => 'telp',
			4 => 'fax',
			5 => 'email',
		);

		$sql .= " ORDER BY  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	function carikota($id_prov)
	{
		$this->db->where('id_prov', $id_prov);
		return $this->db->from('kota')
			->get()
			->result();
	}
}
