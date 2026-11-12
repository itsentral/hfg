<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Accessories_model extends BF_Model
{

	protected $ENABLE_ADD;
	protected $ENABLE_MANAGE;
	protected $ENABLE_VIEW;
	protected $ENABLE_DELETE;

	protected $accounting;

	public function __construct()
	{
		parent::__construct();

		$this->ENABLE_ADD     = has_permission('Master_Indirect.Add');
		$this->ENABLE_MANAGE  = has_permission('Master_Indirect.Manage');
		$this->ENABLE_VIEW    = has_permission('Master_Indirect.View');
		$this->ENABLE_DELETE  = has_permission('Master_Indirect.Delete');

		$this->accounting = $this->load->database('accounting', true);
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

	public function get_data_group($table, $where_field = '', $where_value = '', $where_group = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->group_by($where_group)->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function get_json_accessories()
	{
		$requestData   = $_REQUEST;
		$id_category   = isset($requestData['id_category']) ? $requestData['id_category'] : 0;
		$search_val    = isset($requestData['search']['value']) ? $requestData['search']['value'] : '';
		$col_order     = isset($requestData['order'][0]['column']) ? intval($requestData['order'][0]['column']) : 1;
		$col_dir       = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'asc';
		$limit_start   = isset($requestData['start']) ? intval($requestData['start']) : 0;
		$limit_length  = isset($requestData['length']) ? intval($requestData['length']) : 10;
		$draw          = isset($requestData['draw']) ? intval($requestData['draw']) : 1;

		$fetch = $this->get_query_json_accessories(
			$id_category,
			$search_val,
			$col_order,
			$col_dir,
			$limit_start,
			$limit_length
		);

		$totalData     = $fetch['totalData'];
		$totalFiltered = $fetch['totalFiltered'];
		$query         = $fetch['query'];

		$data  = array();
		$urut1 = 1;
		foreach ($query->result_array() as $row) {
			$nomor = $urut1 + $limit_start;

			$nestedData = array();
			$nestedData[] = "<div class='text-center'>" . $nomor . "</div>";
			$nestedData[] = "<div>" . htmlspecialchars((string)$row['id_stock']) . "</div>";
			$nestedData[] = "<div>" . htmlspecialchars(strtoupper((string)$row['stock_name'])) . "</div>";
			$nestedData[] = "<div>" . htmlspecialchars(strtoupper((string)$row['nm_category'])) . "</div>";
			$nestedData[] = "<div>" . htmlspecialchars(strtoupper((string)$row['trade_name'])) . "</div>";
			$nestedData[] = "<div>" . htmlspecialchars(strtoupper((string)$row['brand'])) . "</div>";
			$nestedData[] = "<div>" . htmlspecialchars(strtoupper((string)$row['spec'])) . "</div>";

			$status = ($row['status'] == '1') ? 'Active' : 'Non-Active';
			$warna  = ($row['status'] == '1') ? 'success' : 'danger';
			$nestedData[] = "<div class='text-center'><span class='badge bg-" . $warna . "'>" . $status . "</span></div>";

			$last_create = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
			$nm_user = !empty($last_create) ? get_name('users', 'username', 'id_user', $last_create) : '-';
			$nestedData[] = "<div>" . htmlspecialchars(strtolower((string)$nm_user)) . "</div>";

			$last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];
			$formatted_date = !empty($last_date) ? date('d-M-Y H:i', strtotime($last_date)) : '-';
			$nestedData[] = "<div class='text-center'>" . $formatted_date . "</div>";

			$view = "<a href='" . site_url('accessories/add/' . $row['id'] . '/view') . "' class='btn btn-sm btn-info text-white' title='Detail Data'><i class='fa fa-eye'></i></a>";
			$edit = "";
			$delete = "";
			if ($this->ENABLE_MANAGE) {
				$edit = "<a href='" . site_url('accessories/add/' . $row['id']) . "' class='btn btn-sm btn-primary' title='Edit Data'><i class='fa fa-edit'></i></a>";
			}
			if ($this->ENABLE_DELETE) {
				$delete = "<button type='button' class='btn btn-sm btn-danger delete' title='Delete data' data-id='" . $row['id'] . "'><i class='fa fa-trash'></i></button>";
			}
			$nestedData[] = "<div class='d-flex justify-content-center gap-1'>" . $view . $edit . $delete . "</div>";

			$data[] = $nestedData;
			$urut1++;
		}

		$json_data = array(
			"draw"            => $draw,
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);

		$json_str = json_encode($json_data);
		header('Content-Type: application/json; charset=utf-8');
		echo $json_str;
	}

	public function get_query_json_accessories($id_category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{
		$WHERE = '';
		if ($id_category > 0) {
			$WHERE = "AND a.id_category='" . $this->db->escape_str($id_category) . "'";
		}

		$sql_search = "";
		if (!empty($like_value)) {
			$escaped_like = $this->db->escape_like_str($like_value);
			$sql_search = " AND (
				a.stock_name LIKE '%" . $escaped_like . "%'
				OR a.id_stock LIKE '%" . $escaped_like . "%'
				OR a.trade_name LIKE '%" . $escaped_like . "%'
				OR a.brand LIKE '%" . $escaped_like . "%'
				OR a.spec LIKE '%" . $escaped_like . "%'
				OR b.code LIKE '%" . $escaped_like . "%'
				OR c.code LIKE '%" . $escaped_like . "%'
				OR d.nm_category LIKE '%" . $escaped_like . "%'
			)";
		}

		$sql_base = "FROM
					accessories a
					LEFT JOIN ms_satuan b ON a.id_unit_gudang=b.id
					LEFT JOIN ms_satuan c ON a.id_unit=c.id
					LEFT JOIN accessories_category d ON a.id_category = d.id
				WHERE a.deleted_date IS NULL " . $WHERE . $sql_search;

		// Hitung total data
		$data['totalData'] = $this->db->query("SELECT count(a.id) as total " . $sql_base)->row()->total;
		$data['totalFiltered'] = $data['totalData'];

		$columns_order_by = array(
			0 => 'a.id',
			1 => 'a.id_stock',
			2 => 'a.stock_name',
			3 => 'd.nm_category',
			4 => 'a.trade_name',
			5 => 'a.brand',
			6 => 'a.spec',
			7 => 'a.status',
			8 => 'a.updated_by',
			9 => 'a.updated_date',
			10 => 'a.id'
		);

		$order_col = isset($columns_order_by[$column_order]) ? $columns_order_by[$column_order] : 'a.id';
		$order_dir = (strtolower($column_dir) === 'desc') ? 'DESC' : 'ASC';

		$sql = "SELECT
					a.*,
					b.code AS sat_packing,
					c.code AS sat_unit,
					d.nm_category AS nm_category
				" . $sql_base . "
				ORDER BY " . $order_col . " " . $order_dir . "
				LIMIT " . intval($limit_start) . ", " . intval($limit_length);

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function get_json_unit()
	{
		$requestData   = $_REQUEST;
		$search_val    = isset($requestData['search']['value']) ? $requestData['search']['value'] : '';
		$col_order     = isset($requestData['order'][0]['column']) ? intval($requestData['order'][0]['column']) : 1;
		$col_dir       = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'asc';
		$limit_start   = isset($requestData['start']) ? intval($requestData['start']) : 0;
		$limit_length  = isset($requestData['length']) ? intval($requestData['length']) : 10;
		$draw          = isset($requestData['draw']) ? intval($requestData['draw']) : 1;

		$fetch = $this->get_query_json_unit(
			$search_val,
			$col_order,
			$col_dir,
			$limit_start,
			$limit_length
		);

		$totalData     = $fetch['totalData'];
		$totalFiltered = $fetch['totalFiltered'];
		$query         = $fetch['query'];

		$data  = array();
		$urut1 = 1;
		foreach ($query->result_array() as $row) {
			$nomor = $urut1 + $limit_start;

			$nestedData = array();
			$nestedData[] = "<div class='text-center'>" . $nomor . "</div>";
			$nestedData[] = "<div>" . htmlspecialchars(strtoupper((string)$row['code'])) . "</div>";

			$last_create = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
			$nm_user = !empty($last_create) ? get_name('users', 'username', 'id_user', $last_create) : '-';
			$nestedData[] = "<div>" . htmlspecialchars(strtolower((string)$nm_user)) . "</div>";

			$last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];
			$formatted_date = !empty($last_date) ? date('d-M-Y H:i', strtotime($last_date)) : '-';
			$nestedData[] = "<div class='text-center'>" . $formatted_date . "</div>";

			$edit   = "<button type='button' data-id='" . $row['id'] . "' class='btn btn-sm btn-primary edit' title='Edit Data'><i class='fa fa-edit'></i></button>";
			$delete = "<button type='button' class='btn btn-sm btn-danger delete' title='Delete data' data-id='" . $row['id'] . "'><i class='fa fa-trash'></i></button>";

			$nestedData[] = "<div class='d-flex justify-content-center gap-1'>" . $edit . $delete . "</div>";
			$data[] = $nestedData;
			$urut1++;
		}

		$json_data = array(
			"draw"            => $draw,
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);

		$json_str = json_encode($json_data);
		header('Content-Type: application/json; charset=utf-8');
		echo $json_str;
	}

	public function get_query_json_unit($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{
		$sql_search = "";
		if (!empty($like_value)) {
			$escaped_like = $this->db->escape_like_str($like_value);
			$sql_search = " AND a.code LIKE '%" . $escaped_like . "%' ";
		}

		$sql_base = "FROM ms_satuan a WHERE a.deleted='N' " . $sql_search;

		$data['totalData'] = $this->db->query("SELECT count(a.id) as total " . $sql_base)->row()->total;
		$data['totalFiltered'] = $data['totalData'];

		$columns_order_by = array(
			0 => 'a.id',
			1 => 'a.code',
			2 => 'a.updated_by',
			3 => 'a.updated_date',
			4 => 'a.id'
		);

		$order_col = isset($columns_order_by[$column_order]) ? $columns_order_by[$column_order] : 'a.id';
		$order_dir = (strtolower($column_dir) === 'desc') ? 'DESC' : 'ASC';

		$sql = "SELECT a.* " . $sql_base . "
				ORDER BY " . $order_col . " " . $order_dir . "
				LIMIT " . intval($limit_start) . ", " . intval($limit_length);

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function all_coa()
	{
		$this->accounting->select('a.no_perkiraan, a.nama');
		$this->accounting->from('coa_master a');
		$this->accounting->order_by('a.id', 'desc');
		$coa = $this->accounting->get()->result();

		return $coa;
	}

	public function coa_detail($no_coa)
	{
		$this->accounting->select('a.*');
		$this->accounting->from('coa_master a');
		$this->accounting->where('a.no_perkiraan', $no_coa);
		$get_coa = $this->accounting->get()->row();

		return $get_coa;
	}
}